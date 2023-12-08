<?php

require __DIR__ . '/inc.php';
require_once($CFG->libdir . '/externallib.php');

function block_exacomp_load_service($serviceshortname) {
    global $DB;

    //check if the service exists and is enabled
    $service = $DB->get_record('external_services', array('shortname' => $serviceshortname, 'enabled' => 1));
    if (empty($service)) {
        // will throw exception if no token found
        throw new moodle_exception('servicenotavailable', 'webservice');
    }

    // Get an existing token or create a new one.
    $token = external_generate_token_for_current_user($service);
    //$privatetoken = $token->privatetoken;
    external_log_token_request($token);

    return $token->token;
}

function block_exacomp_get_login_data() {
    $exa_tokens = [];

    $services = optional_param('services', '', PARAM_TEXT);
    $services = array_keys(
        ['moodle_mobile_app' => 1, 'exacompservices' => 1, 'exaportservices' => 1] // default services
        + ($services ? array_flip(explode(',', $services)) : []));

    foreach ($services as $service) {
        $token = block_exacomp_load_service($service);
        $exa_tokens[] = [
            'service' => $service,
            'token' => $token,
        ];
    }

    // get login data
    $data = \block_exacomp\externallib\externallib::login();
    // add tokens
    $data['tokens'] = $exa_tokens;

    // clean output
    $data = external_api::clean_returnvalue(\block_exacomp\externallib\externallib::login_returns(), $data);

    return $data;
}

function block_exacomp_logout() {
    //mtrace("logout");
    // This is deprecated... logout is only done for diggrplus
    //require_sesskey();
    //$authsequence = get_enabled_auth_plugins(); // auths, in sequence
    //foreach ($authsequence as $authname) {
    //    $authplugin = get_auth_plugin($authname);
    //    $authplugin->logoutpage_hook();
    //}
    //require_logout();
}


$action = optional_param('action', '', PARAM_TEXT);

if ($action == 'dakora_sso') {
    require_sesskey();

    try {
        $data = block_exacomp_get_login_data();
    } catch (Exception $e) {
        echo $e->getMessage();
        exit;
    }

    $moodle_redirect_token = '';
    $moodle_data_token = 'data-' . block_exacomp_random_password(24);
    $DB->insert_record('block_exacompapplogin', [
        'moodle_redirect_token' => $moodle_redirect_token,
        'moodle_data_token' => $moodle_data_token,
        'created_at' => time(),
        'request_data' => '',
        'result_data' => json_encode($data),
    ]);

    $dakora_url = trim(get_config('exacomp', 'dakora_url'));
    $pos = strpos($dakora_url, "dakoraplus.eu");  //different redirects for dakora+ and dakora (old)
    if ($pos === false) {
        $redirect_url = $dakora_url . '/page/sso.html?moodle_url=' . $CFG->wwwroot . '&moodle_token=' . $moodle_data_token;
    } else {
        $redirect_url = $dakora_url . '/login?moodle_url=' . $CFG->wwwroot . '&do_login=true&recheck_login=1';
    }
    redirect($redirect_url);

    exit;
}


$PAGE->set_context(context_system::instance());
$PAGE->set_url('/blocks/exacomp/applogin.php', array('app' => required_param('app', PARAM_TEXT), 'app_version' => required_param('app_version', PARAM_TEXT)));
$PAGE->set_pagelayout('embedded');

if (!get_config('exacomp', 'applogin_enabled')) {
    echo $OUTPUT->header();

    echo '<div style="width: 100%; text-align: center; padding-top: 100px;">' . block_exacomp_trans(['de:App Login ist deaktiviert!', 'en:App Login is disabled!']) . '</div>';

    echo $OUTPUT->footer();
    exit;
}

// Allow CORS requests.
header('Access-Control-Allow-Origin: *');

required_param('app', PARAM_TEXT);
required_param('app_version', PARAM_TEXT);

$action = optional_param('action', '', PARAM_TEXT);

if ($action == 'info') {
    $info = core_plugin_manager::instance()->get_plugin_info('block_exacomp');

    $info = array(
        'version' => $info->versiondb,
        'release' => $info->release,
        'login_method' => get_config('exacomp', 'new_app_login') ? 'popup' : '',
    );

    header('Content-Type: application/json');
    echo json_encode($info);
    exit;
}

if ($action == 'logout') {
    block_exacomp_logout();

    $SESSION->wantsurl = $CFG->wwwroot . '/blocks/exacomp/applogin.php?' . $_SERVER['QUERY_STRING'] . '&withlogout=1';
    redirect(str_replace('action=logout', '', $_SERVER['REQUEST_URI']));
}

block_exacomp_require_login(0, false, null, true, false);

if (isguestuser()) {
    // is guest user
    block_exacomp_require_login();
    $SESSION->wantsurl = $CFG->wwwroot . '/blocks/exacomp/applogin.php?' . $_SERVER['QUERY_STRING'] . '&withlogout=1';
    redirect($CFG->wwwroot . '/login/index.php');
    exit;
}

$loginData = [
    'type' => 'login_successful',
    'data' => block_exacomp_get_login_data(),
];

//Here after the user is logged in
//Check if this user is a teacher from eeducation. If they are: add to course with id=700
$email = $loginData["data"]["user"]["email"];
if (strcmp(strstr($email, "@"), "@eeducation.at") == 0) {
    //They are from eeducation ==> enrol them
    //    $course = get_course(700);
    $course = $DB->get_record('course', array('id' => 700), '*');
    if ($course != null) { //only proceed with enrolment, if course exists
        $context = context_course::instance($course->id);
        $userid = $loginData["data"]["user"]["id"];
        $user = $DB->get_record('user', array('id' => $userid, 'deleted' => 0), '*', MUST_EXIST);
        if (!is_enrolled($context, $user)) {
            $enrol = enrol_get_plugin("manual"); //enrolment = manual
            if ($enrol === null) {
                return false;
            }
            $instances = enrol_get_instances($course->id, true);
            $manualinstance = null;
            foreach ($instances as $instance) {
                if ($instance->enrol == "manual") {
                    $manualinstance = $instance;
                    break;
                }
            }
            //            if ($manualinstance !== null) {
            //                $instanceid = $enrol->add_default_instance($course);
            //                if ($instanceid === null) {
            //                    $instanceid = $enrol->add_instance($course);
            //                }
            //                $instance = $DB->get_record('enrol', array('id' => $instanceid));
            //            }

            if ($manualinstance != null) {
                $enrol->enrol_user($manualinstance, $userid, 3); //The roleid of "editingteacher" is 3 in mdl_role table
            }
        }
    }
}

if (optional_param('withlogout', '', PARAM_BOOL)) {

    // came from login form
    echo $OUTPUT->header();

    ?>
    <script>
        if (top !== window) {
            // for older browsers only string is allowed
            top.postMessage(<?php echo json_encode(json_encode($loginData)) ?>, '*');
        } else if (window.opener) {
            // for older browsers only string is allowed
            window.opener.postMessage(<?php echo json_encode(json_encode($loginData)) ?>, '*');
            window.close();
        }
    </script>
    <?php

    echo '<div style="width: 100%; text-align: center; padding-top: 100px;">Login erfolgreich</div>';

    echo $OUTPUT->footer();

    block_exacomp_logout();

    exit;
} else {

    // was already logged in, show info and continue button... always comes here?
    echo $OUTPUT->header();

    ?>
    <script>
        function app_login_now() {
            if (top !== window) {
                // for older browsers only string is allowed
                top.postMessage(<?php echo json_encode(json_encode($loginData)) ?>, '*');
            } else if (window.opener) {
                // for older browsers only string is allowed
                window.opener.postMessage(<?php echo json_encode(json_encode($loginData)) ?>, '*');
                window.close();
            }
        }

        function app_relogin() {
            document.location.href = document.location.href + '&action=logout';
        }
    </script>
    <?php

    echo '<div style="padding-top: 60px; text-align: center;">';
    echo block_exacomp_trans(['de:Du bist eingeloggt als:', 'en:You are logged in as:']) . ' ';
    echo fullname($USER);
    echo '<br/>';
    echo '<br/>';
    echo '<button type="button" class="btn btn-primary" onclick="app_login_now()">' . block_exacomp_trans(['de:Fortfahren', 'en:Continue']) . '</button>';
    echo '<br/>';
    echo '<br/>';
    echo '<button type="button" class="btn btn-secondary" onclick="app_relogin()">' . block_exacomp_trans(['de:Als anderer Benutzer einloggen', 'en:Login as different user']) . '</button>';
    echo '</div>';

    echo $OUTPUT->footer();
    exit;
}
