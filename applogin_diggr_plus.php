<?php

require __DIR__.'/inc.php';
require_once($CFG->libdir.'/externallib.php');
require_once __DIR__.'/externallib.php';

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

function block_exacomp_get_service_tokens($services) {
    $tokens = [];
    $services = array_keys(
        ['moodle_mobile_app' => 1, 'exacompservices' => 1] // default services
        + ($services ? array_flip(explode(',', $services)) : []));

    foreach ($services as $service) {
        $token = block_exacomp_load_service($service);
        $tokens[] = [
            'service' => $service,
            'token' => $token,
        ];
    }

    return $tokens;
}

function block_exacomp_logout() {
    $authsequence = get_enabled_auth_plugins(); // auths, in sequence
    foreach ($authsequence as $authname) {
        $authplugin = get_auth_plugin($authname);
        $authplugin->logoutpage_hook();
    }

    require_logout();
}

function block_exacomp_turn_notifications_on() {
    global $USER, $CFG;

    require_once($CFG->dirroot.'/user/editlib.php');

    // require __DIR__.'/db/messages.php';
    $providers = get_message_providers();
    foreach ($providers as $provider) {
        if ($provider->component != 'block_exacomp') {
            continue;
        }

        foreach (['loggedin', 'loggedoff'] as $type) {
            $preference_name = 'message_provider_'.$provider->component.'_'.$provider->name.'_'.$type;
            $value = @$USER->preference[$preference_name];
            if (strpos($value, 'popup') === false) {
                // only change, if popup isn't turned on
                if (!$value || $value == 'none') {
                    $newValue = 'popup';
                } else {
                    $newValue = 'popup,'.$value;
                }

                // echo $preference_name." ".$value." ".$newValue."\n";
                $userpref = ['id' => $USER->id];
                $userpref['preference_'.$preference_name] = $newValue;
                useredit_update_user_preference($userpref);
            }
        }
    }
}

function block_exacomp_login_successfull() {
    // actions after login:
    block_exacomp_turn_notifications_on();
    if (block_exacomp_is_diggrv_enabled()) { // && block_exacomp_is_teacher_in_any_course()
        block_exacomp_diggrv_create_first_course();
    }
}

function block_exacomp_is_return_uri_allowed($return_uri) {
    global $CFG;

    $allowed_redirect_uris = [$CFG->wwwroot, 'diggr-plus.at', 'www.diggr-plus.at'];
    $additional_allowed_redirect_uris = trim(get_config('exacomp', 'applogin_redirect_urls'));
    if ($additional_allowed_redirect_uris) {
        $additional_allowed_redirect_uris = preg_split('![\s\r\n]+!', $additional_allowed_redirect_uris);
        $allowed_redirect_uris = array_merge($allowed_redirect_uris, $additional_allowed_redirect_uris);
    }

    $return_uri_allowed = false;
    foreach ($allowed_redirect_uris as $allowed_redirect_uri) {
        // add protocol, if needed
        if (strpos($allowed_redirect_uri, '://') === false) {
            $allowed_redirect_uri = 'https://'.$allowed_redirect_uri;
        }
        // check url, also allow "www." prefix
        $regexp = '!^(www\\.)?'.preg_quote($allowed_redirect_uri, '!').'(/|$)!';
        // allow * as wildcard
        $regexp = str_replace('\\*', '.*', $regexp);
        if (preg_match($regexp, $return_uri)) {
            $return_uri_allowed = true;
            break;
        }
    }

    return $return_uri_allowed;
}

function block_exacomp_init_cors() {
    // from: https://stackoverflow.com/a/7454204
    if (@$_SERVER['HTTP_ORIGIN'] && block_exacomp_is_return_uri_allowed(@$_SERVER['HTTP_ORIGIN'])) {
        // set allowed origin only, if in allowed list
        header('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']);
    }

    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type,Authorization");

    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        echo 'option request';
        exit;
    }
}

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/blocks/exacomp/applogin_diggr_plus.php');
$PAGE->set_pagelayout('embedded');

// Allow CORS requests.
header('Access-Control-Allow-Origin: *');

// always delete old login data
$DB->execute("DELETE FROM {block_exacompapplogin} WHERE created_at<?", [time() - 60 * 30]);

$action = optional_param('action', '', PARAM_TEXT);

if ($action == 'get_login_url') {
    required_param('app', PARAM_TEXT);
    required_param('app_version', PARAM_TEXT);

    $return_uri = required_param('return_uri', PARAM_TEXT);

    if (!block_exacomp_is_return_uri_allowed($return_uri)) {
        $data = [
            'error' => block_exacomp_trans(['de:Zugriff unter {$a->url} ist nicht erlaubt', 'en:Access from {$a->url} is not allowed'], ['url' => $return_uri]),
        ];

        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    $moodle_redirect_token = 'redirect-'.block_exacomp_random_password(24);
    $moodle_data_token = 'data-'.block_exacomp_random_password(24);

    $DB->insert_record('block_exacompapplogin', [
        'app_token' => required_param('app_token', PARAM_TEXT),
        'moodle_redirect_token' => $moodle_redirect_token,
        'moodle_data_token' => $moodle_data_token,
        'created_at' => time(),
        'request_data' => json_encode([
            'return_uri' => $return_uri,
            'services' => optional_param('services', '', PARAM_TEXT),
        ]),
        'result_data' => '',
    ]);

    $data = [
        'login_url' => $CFG->wwwroot.'/blocks/exacomp/applogin_diggr_plus.php?moodle_redirect_token='.$moodle_redirect_token,
    ];

    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

if ($action == 'msteams_login') {
    required_param('app', PARAM_TEXT);
    required_param('app_version', PARAM_TEXT);
    $access_token = required_param('access_token', PARAM_TEXT);

    $errorResult = function($error) {
        header('Content-Type: application/json');
        echo json_encode([
            'type' => 'error',
            'error' => $error,
        ]);
        exit;
    };

    block_exacomp_init_cors();

    // idea from https://github.com/catalyst/moodle-auth_userkey/blob/MOODLE_33PLUS/auth.php
    // test user
    // $user = get_complete_user_data('id', 3);

    $access_token_payload = explode('.', $access_token);
    $access_token_payload = json_decode(base64_decode($access_token_payload[1]));

    // check config
    // demo tenantid = hak-steyr
    if ($access_token_payload->tid != '3171ff0c-9e10-4061-9afb-66b6b12b03a9') {
        $errorResult('Wrong token: wrong tenantid '.$access_token_payload->tid);
    }

    $ch = curl_init('https://graph.microsoft.com/v1.0/me');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer '.$access_token,
    ]);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

    $result = curl_exec($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($status_code != 200) {
        $errorResult('Azure request failed, status_code: '.$status_code);
    }

    $teamsUser = json_decode($result);
    if (@!$teamsUser->userPrincipalName) {
        $errorResult('teamsUser->userPrincipalName not set');
    }

    $email = $teamsUser->userPrincipalName;
    // uppercase email addresses on hak-steyr
    $email = strtolower($email);

    if (preg_match('!#!', $email)) {
        // eg. userPrincipalName starts with #EXT# for external users
        $errorResult('External Users are not allowed to login');
    }


    $user = $DB->get_record('user', ['email' => $email]);
    if (!$user) {
        // create the user
        $user = array(
            'username' => $email,
            'password' => generate_password(20),
            'firstname' => $teamsUser->givenName,
            'lastname' => $teamsUser->surname,
            'description' => 'diggr-plus: imported with msteams login',
            'email' => $email,
            'suspended' => 0,
            'mnethostid' => $CFG->mnet_localhost_id,
            'confirmed' => 1,
        );

        require_once($CFG->dirroot.'/user/lib.php');
        $userid = user_create_user($user);
    } else {
        $userid = $user->id;
    }

    $user = get_complete_user_data('id', $userid);


    // hack to get tokens
    global $USER;
    $origUSER = $USER;
    $USER = $user;

    $tokens = block_exacomp_get_service_tokens(optional_param('services', '', PARAM_TEXT));
    block_exacomp_login_successfull();

    $USER = $origUSER;

    $result_data = [
        'tokens' => $tokens,
    ];

    $moodle_redirect_token = '';
    $moodle_data_token = 'data-'.block_exacomp_random_password(24);
    $DB->insert_record('block_exacompapplogin', [
        'moodle_redirect_token' => $moodle_redirect_token,
        'moodle_data_token' => $moodle_data_token,
        'created_at' => time(),
        'request_data' => '',
        'result_data' => json_encode($result_data),
    ]);

    $data = [
        'moodle_data_token' => $moodle_data_token,
    ];
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

if ($action == 'login_result') {
    required_param('app', PARAM_TEXT);
    required_param('app_version', PARAM_TEXT);

    $moodle_data_token = required_param('moodle_token', PARAM_TEXT);
    //$app_token = required_param('app_token', PARAM_TEXT);

    $applogin = $DB->get_record('block_exacompapplogin', ['moodle_data_token' => $moodle_data_token]);
    if (!$applogin) {
        header('Content-Type: application/json');
        echo json_encode([
            'type' => 'error',
            'error' => 'Wrong token',
        ]);
        exit;
    }

    // can only be used once
    $DB->delete_records('block_exacompapplogin', ['id' => $applogin->id]);

    $result_data = json_decode($applogin->result_data);
    header('Content-Type: application/json');
    echo json_encode([
        'type' => 'login_successful',
        'data' => $result_data,
    ]);
    exit;
}

if ($action == 'info') {
    required_param('app', PARAM_TEXT);
    required_param('app_version', PARAM_TEXT);

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

    $return_uri = optional_param('return_uri', '', PARAM_TEXT);
    if ($return_uri) {
        if (!block_exacomp_is_return_uri_allowed($return_uri)) {
            header('Location: '.$CFG->wwwroot);
            exit;
        } else {
            header('Location: '.$return_uri);
            exit;
        }
    }

    redirect(str_replace('action=logout', '', $_SERVER['REQUEST_URI']));
    exit;
}

if ($action) {
    throw new \Exception("action '$action' not found");
}


$SESSION->wantsurl = $CFG->wwwroot.'/blocks/exacomp/applogin_diggr_plus.php?'.$_SERVER['QUERY_STRING'].'&from_login=1';

require_login(0, false, null, false, false);

if (isguestuser()) {
    // is guest user
    require_login();
    $SESSION->wantsurl = $CFG->wwwroot.'/blocks/exacomp/applogin_diggr_plus.php?'.$_SERVER['QUERY_STRING'].'&withlogout=1';
    redirect($CFG->wwwroot.'/login/index.php');
    exit;
}


$moodle_redirect_token = required_param('moodle_redirect_token', PARAM_TEXT);

$applogin = $DB->get_record('block_exacompapplogin', ['moodle_redirect_token' => $moodle_redirect_token]);
if (!$applogin) {
    throw new Error('token not found');
}

$request_data = json_decode($applogin->request_data);

$DB->update_record('block_exacompapplogin', (object)[
    'id' => $applogin->id,
    'result_data' => json_encode([
        'tokens' => block_exacomp_get_service_tokens($request_data->services),
    ]),
]);

block_exacomp_login_successfull();

$return_uri = $request_data->return_uri.'?moodle_token='.$applogin->moodle_data_token;

if (optional_param('withlogout', '', PARAM_BOOL)) {

    // came from login form
    echo $OUTPUT->header();

    ?>
    <script>
        document.location.href = <?=json_encode($return_uri)?>;
    </script>
    <?php

    echo '<div style="width: 100%; text-align: center; padding-top: 100px;">Login erfolgreich</div>';

    echo $OUTPUT->footer();

    block_exacomp_logout();

    exit;
} else {
    header("Location: ".$return_uri);
    exit;
}
