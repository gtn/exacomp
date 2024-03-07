<?php

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;

require __DIR__ . '/inc.php';
require_once($CFG->libdir . '/externallib.php');

function block_exacomp_json_result_success($data) {
    header('Content-Type: application/json');
    echo json_encode(array_merge([
        'type' => 'success',
    ], $data));
    exit;
}

function block_exacomp_json_result_error($error) {
    header('Content-Type: application/json');
    echo json_encode([
        'type' => 'error',
        'error' => $error,
    ]);
    exit;
}

function block_exacomp_load_service($serviceshortname) {
    global $CFG, $DB;

    //check if the service exists and is enabled
    $service = $DB->get_record('external_services', array('shortname' => $serviceshortname, 'enabled' => 1));
    if (empty($service)) {
        // will throw exception if no token found
        throw new moodle_exception('servicenotavailable', 'webservice');
    }

    // Get an existing token or create a new one.
    $context = context_system::instance();
    // a part core code to prevent erorr message to own
    if (has_capability('moodle/webservice:createtoken', $context)) {
        if (in_array($serviceshortname, ['exacompservices', 'exaportservices'])) {
            // hack: allow exa-services also for admins, so admins also can login (mainly used by andreas, and in local dev!)
            $orig_siteadmins = $CFG->siteadmins;
            $CFG->siteadmins = '';
            $token = external_generate_token_for_current_user($service);
            $CFG->siteadmins = $orig_siteadmins;
        } else {
            $token = external_generate_token_for_current_user($service);
        }
    } else {
        throw new moodle_exception('diggrapp_cannotcreatetoken', 'block_exacomp');
    }


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

    require_once($CFG->dirroot . '/user/editlib.php');

    // require __DIR__.'/db/messages.php';
    $providers = get_message_providers();
    foreach ($providers as $provider) {
        if ($provider->component != 'block_exacomp') {
            continue;
        }

        foreach (['loggedin', 'loggedoff'] as $type) {
            $preference_name = 'message_provider_' . $provider->component . '_' . $provider->name . '_' . $type;
            $value = @$USER->preference[$preference_name];
            if (strpos($value, 'popup') === false) {
                // only change, if popup isn't turned on
                if (!$value || $value == 'none') {
                    $newValue = 'popup';
                } else {
                    $newValue = 'popup,' . $value;
                }

                // echo $preference_name." ".$value." ".$newValue."\n";
                $userpref = ['id' => $USER->id];
                $userpref['preference_' . $preference_name] = $newValue;
                useredit_update_user_preference($userpref);
            }
        }
    }
}

function block_exacomp_login_successfull($login_request_data) {
    // actions after login:
    block_exacomp_turn_notifications_on();

    if (@$login_request_data->app == 'setapp' && block_exacomp_is_setapp_enabled()) { // && block_exacomp_is_teacher_in_any_course()
        block_exacomp_diggrv_create_first_course();
    }
}

function block_exacomp_is_return_uri_allowed($return_uri) {
    global $CFG;

    $allowed_redirect_uris = [$CFG->wwwroot, 'diggr-plus.at', 'www.diggr-plus.at', 'dakoraplus.eu'];
    $additional_allowed_redirect_uris = trim(get_config('exacomp', 'applogin_redirect_urls'));
    if ($additional_allowed_redirect_uris) {
        $additional_allowed_redirect_uris = preg_split('![\s\r\n]+!', $additional_allowed_redirect_uris);
        $allowed_redirect_uris = array_merge($allowed_redirect_uris, $additional_allowed_redirect_uris);
    }

    $return_uri_allowed = false;
    foreach ($allowed_redirect_uris as $allowed_redirect_uri) {
        // add protocol, if needed
        if (!preg_match('!^[a-z]+://!i', $allowed_redirect_uri)) {
            $allowed_redirect_uri = 'https://' . $allowed_redirect_uri;
        }

        $regexp = preg_quote($allowed_redirect_uri, '!');

        // allow * as wildcard
        $regexp = str_replace('\\*', '.*', $regexp);

        // also allow "www." prefix
        $regexp = preg_replace('!^((http|https)[\\\\]?\://)!i', '$1(www\\.)?', $regexp);

        $regexp = "^" . rtrim($regexp, '/') . '(/.*)?$';
        if (preg_match("!{$regexp}!", $return_uri)) {
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
        header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
    }

    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type,Authorization");

    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        echo 'option request';
        exit;
    }
}

function block_exacomp_send_login_result($user, $login_request_data) {
    // hack to get tokens
    global $USER, $DB;
    $origUSER = $USER;
    $USER = $user;

    try {
        $tokens = block_exacomp_get_service_tokens(optional_param('services', '', PARAM_TEXT));
        block_exacomp_login_successfull($login_request_data);
    } catch (Exception $e) {
        block_exacomp_logout();

        block_exacomp_json_result_error($e->getMessage());
    }

    $USER = $origUSER;

    $result_data = [
        'tokens' => $tokens,
    ];

    $moodle_redirect_token = '';
    $moodle_data_token = 'data-' . block_exacomp_random_password(24);
    $DB->insert_record('block_exacompapplogin', [
        'moodle_redirect_token' => $moodle_redirect_token,
        'moodle_data_token' => $moodle_data_token,
        'created_at' => time(),
        'request_data' => json_encode($login_request_data),
        'result_data' => json_encode($result_data),
    ]);

    block_exacomp_json_result_success([
        'moodle_token' => $moodle_data_token,
    ]);
}

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/blocks/exacomp/applogin_diggr_plus.php', $_GET);

$action = optional_param('action', '', PARAM_TEXT);

// Allow CORS requests.
header('Access-Control-Allow-Origin: *');


if (!get_config('exacomp', 'applogin_enabled')) {
    $error = block_exacomp_trans(['de:App Login ist deaktiviert!', 'en:App Login is disabled!']);

    if ($action == 'get_login_url' || $action == 'msteams_login') {
        block_exacomp_init_cors();
        block_exacomp_json_result_error($error);
    } else {
        echo $OUTPUT->header();

        echo '<div style="width: 100%; text-align: center; padding-top: 100px;">' . $error . '</div>';

        echo $OUTPUT->footer();
        exit;
    }
}


// always delete old login data
$DB->execute("DELETE FROM {block_exacompapplogin} WHERE created_at<?", [time() - 60 * 30]);

if ($action == 'get_login_url') {
    $app = required_param('app', PARAM_TEXT);
    $app_version = required_param('app_version', PARAM_TEXT);

    $return_uri = required_param('return_uri', PARAM_TEXT);

    if (!block_exacomp_is_return_uri_allowed($return_uri)) {
        block_exacomp_json_result_error(block_exacomp_trans(['de:Zugriff unter {$a->url} ist nicht erlaubt', 'en:Access from {$a->url} is not allowed'], ['url' => $return_uri]));
    }

    $moodle_redirect_token = 'redirect-' . block_exacomp_random_password(24);
    $moodle_data_token = 'data-' . block_exacomp_random_password(24);

    $DB->insert_record('block_exacompapplogin', [
        'app_token' => required_param('app_token', PARAM_TEXT),
        'moodle_redirect_token' => $moodle_redirect_token,
        'moodle_data_token' => $moodle_data_token,
        'created_at' => time(),
        'request_data' => json_encode([
            'app' => $app,
            'app_version' => $app_version,
            'return_uri' => $return_uri,
            'services' => optional_param('services', '', PARAM_TEXT),
        ]),
        'result_data' => '',
    ]);

    block_exacomp_json_result_success([
        'login_url' => $CFG->wwwroot . '/blocks/exacomp/applogin_diggr_plus.php?moodle_redirect_token=' . $moodle_redirect_token,
    ]);
    exit;
}

if ($action == 'msteams_login') {
    $app = required_param('app', PARAM_TEXT);
    $app_version = required_param('app_version', PARAM_TEXT);
    $access_token = required_param('access_token', PARAM_TEXT);

    $login_request_data = (object)[
        'app' => $app,
        'app_version' => $app_version,
    ];

    block_exacomp_init_cors();

    // idea from https://github.com/catalyst/moodle-auth_userkey/blob/MOODLE_33PLUS/auth.php
    // test user
    // $user = get_complete_user_data('id', 3);

    $client_id = get_config("exacomp", 'msteams_client_id');
    if (!$client_id) {
        throw new moodle_exception('client_id not set');
    }

    // if (isloggedin()) {
    //     // login directly
    //     block_exacomp_send_login_result($USER);
    // }

    try {
        // check access_token
        $jwks = json_decode(file_get_contents('https://login.microsoftonline.com/common/discovery/v2.0/keys'), true);
        $decoded = JWT::decode($access_token, JWK::parseKeySet($jwks), array('RS256'));
    } catch (Exception $e) {
        block_exacomp_json_result_error('jwt error: ' . $e->getMessage());
    }

    // actually checking audience is not needed?
    if ($decoded->aud != 'api://diggr-plus.at/' . $client_id) {
        block_exacomp_json_result_error('audience not allowed: ' . $decoded->aud);
    }
    if (time() > $decoded->exp) {
        block_exacomp_json_result_error('access_token expired');
    }
    if ($decoded->scp != 'access_as_user') {
        block_exacomp_json_result_error('Wrong scp: ' . $decoded->scp);
    }
    // check config
    // demo tenantid = hak-steyr
    // if ($decoded->tid != '3171ff0c-9e10-4061-9afb-66b6b12b03a9') {
    //     block_exacomp_json_result_error('Wrong token: wrong tenantid '.$decoded->tid);
    // }
    if (!$decoded->tid) {
        block_exacomp_json_result_error(block_exacomp_trans(['de:Zugriff mit einem privaten Account ist nicht möglich', 'en:Access with a private account is not possible']));
    }

    $userPrincipalName = $decoded->upn;
    $tenantId = $decoded->tid;
    $familyName = $decoded->family_name;
    $givenName = $decoded->given_name;
    // $oid = $decoded->oid; // unique id for a user in one tenant

    if (!$userPrincipalName) {
        block_exacomp_json_result_error('decoded->userPrincipalName not set');
    }

    $email = $userPrincipalName;
    // uppercase email addresses on hak-steyr
    $email = strtolower($email);

    if (preg_match('!#!', $email)) {
        // eg. userPrincipalName starts with #EXT# for external users
        block_exacomp_json_result_error('External Users are not allowed to login');
    }

    if (get_config('exacomp', 'sso_create_users')) {
        $moodle_user = $DB->get_record('user', ['username' => $email]);
        if ($moodle_user) {
            block_exacomp_send_login_result($moodle_user, $login_request_data);
        }

        // old logic with creating the user if not existing (for diggrv)
        // create the user
        $moodle_user = array(
            'username' => $email,
            'password' => generate_password(20),
            'firstname' => $givenName,
            'lastname' => $familyName,
            'description' => 'diggr-plus: created by msteams login',
            'email' => $email,
            'suspended' => 0,
            'mnethostid' => $CFG->mnet_localhost_id,
            'confirmed' => 1,
        );

        require_once($CFG->dirroot . '/user/lib.php');
        $userid = user_create_user($moodle_user);

        $moodle_user = get_complete_user_data('id', $userid);

        block_exacomp_send_login_result($moodle_user, $login_request_data);
    } else {
        // new logic mit o365 user verknüpfen
        $usermap = $DB->get_record('block_exacomp_usermap', ['provider' => 'o365', 'tenant_id' => $tenantId, 'remoteuserid' => $email]);
        if (!$usermap) {
            $usermap = (object)[
                'provider' => 'o365',
                'tenant_id' => $tenantId,
                'remoteuserid' => $email,
                'timecreated' => time(),
            ];
            $usermap->id = $DB->insert_record('block_exacomp_usermap', $usermap);
        }

        $DB->update_record('block_exacomp_usermap', [
            'lastaccess' => time(),
            'firstname' => $givenName,
            'lastname' => $familyName,
            'email' => $email,
            'id' => $usermap->id,
        ]);

        $usermap = $DB->get_record('block_exacomp_usermap', ['id' => $usermap->id]);

        $moodle_user = null;
        if ($usermap->userid) {
            // already mapped -> login
            $moodle_user = $DB->get_record('user', ['id' => $usermap->userid]);
            if (!$moodle_user) {
                // remove the mapping
                $DB->update_record('block_exacomp_usermap', ['userid' => 0, 'id' => $usermap->id]);
            }
        }

        if ($moodle_user) {
            block_exacomp_send_login_result($moodle_user, $login_request_data);
        }

        $return_uri = required_param('return_uri', PARAM_TEXT);

        if (!block_exacomp_is_return_uri_allowed($return_uri)) {
            block_exacomp_json_result_error(block_exacomp_trans(['de:Zugriff unter {$a->url} ist nicht erlaubt', 'en:Access from {$a->url} is not allowed'], ['url' => $return_uri]));
        }

        $moodle_redirect_token = 'redirect-' . block_exacomp_random_password(24);
        $moodle_data_token = 'data-' . block_exacomp_random_password(24);

        $DB->insert_record('block_exacompapplogin', [
            'app_token' => required_param('app_token', PARAM_TEXT),
            'moodle_redirect_token' => $moodle_redirect_token,
            'moodle_data_token' => $moodle_data_token,
            'created_at' => time(),
            'request_data' => json_encode([
                'app' => $app,
                'app_version' => $app_version,
                'usermapid' => $usermap->id,
                'return_uri' => $return_uri,
                'services' => optional_param('services', '', PARAM_TEXT),
            ]),
            'result_data' => '',
        ]);

        block_exacomp_json_result_success([
            'login_url' => $CFG->wwwroot . '/blocks/exacomp/applogin_diggr_plus.php?moodle_redirect_token=' . $moodle_redirect_token,
        ]);
    }
}

if ($action == 'connected_users') {
    if (!isloggedin()) {
        die('not loggedin');
    }

    if ($disconnect_userid = optional_param('disconnect_userid', 0, PARAM_INT)) {
        $usermap = $DB->get_record('block_exacomp_usermap', ['id' => $disconnect_userid, 'userid' => $USER->id, 'candisconnect' => 1]);
        if ($usermap) {
            $DB->update_record('block_exacomp_usermap', ['userid' => 0, 'id' => $usermap->id]);
        }
    }

    $usermaps = $DB->get_records('block_exacomp_usermap', ['userid' => $USER->id]);

    // came from login form
    echo $OUTPUT->header();

    if (!$usermaps) {
        echo block_exacomp_trans(['de:Keine verbundenen Benutzer gefunden', 'en:No connected accounts found']);
    } else {
        echo '<table class="generaltable">';
        foreach ($usermaps as $usermap) {
            echo '<tr>';
            echo '<td>' . $usermap->provider . '</td>';
            echo '<td>' . $usermap->firstname . '</td>';
            echo '<td>' . $usermap->lastname . '</td>';
            echo '<td>' . $usermap->email . '</td>';
            echo '<td><form method="post">
            <input type="hidden" name="disconnect_userid" value="' . $usermap->id . '"/>
            <input type="submit" class="btn btn-secondary" value="' . block_exacomp_trans(['de:Trennen', 'en:Disconnect']) . '"/>
        </form></td>';
        }
        echo '</table>';
    }

    echo $OUTPUT->footer();

    exit;
}

if ($action == 'login_result') {
    required_param('app', PARAM_TEXT);
    required_param('app_version', PARAM_TEXT);

    $moodle_data_token = required_param('moodle_token', PARAM_TEXT);
    //$app_token = required_param('app_token', PARAM_TEXT);

    $applogin = $DB->get_record('block_exacompapplogin', ['moodle_data_token' => $moodle_data_token]);
    if (!$applogin) {
        block_exacomp_json_result_error('Wrong token');
    }

    // can only be used once
    $DB->delete_records('block_exacompapplogin', ['id' => $applogin->id]);

    $result_data = json_decode($applogin->result_data);
    block_exacomp_json_result_success([
        'type' => 'login_successful',
        'data' => $result_data,
    ]);
    exit;
}

if ($action == 'info') {
    required_param('app', PARAM_TEXT);
    required_param('app_version', PARAM_TEXT);

    $info = core_plugin_manager::instance()->get_plugin_info('block_exacomp');

    block_exacomp_json_result_success([
        'version' => $info->versiondb,
        'release' => $info->release,
        'login_method' => get_config('exacomp', 'new_app_login') ? 'popup' : '',
    ]);
    exit;
}

if ($action == 'logout') {
    try {
        // old diggr+ versions don't provide a sesskey
        // so wrap this in a try-catch-block to not fail on older versions
        require_sesskey();
        block_exacomp_logout();
    } catch (\moodle_exception $e) {
    }

    $return_uri = optional_param('return_uri', '', PARAM_TEXT);
    if ($return_uri) {
        if (!block_exacomp_is_return_uri_allowed($return_uri)) {
            header('Location: ' . $CFG->wwwroot);
            exit;
        } else {
            header('Location: ' . $return_uri);
            exit;
        }
    }

    redirect(str_replace('action=logout', '', $_SERVER['REQUEST_URI']));
    exit;
}

if ($action) {
    throw new Exception("action '$action' not found");
}

$PAGE->set_pagelayout('embedded');

$SESSION->wantsurl = $CFG->wwwroot . '/blocks/exacomp/applogin_diggr_plus.php?' . $_SERVER['QUERY_STRING'];

// für cors im iframe ist ein extra redirect mit manueller Benutzerbestätigung notwendig
// sonst werden die cookies nicht geladen bzw. können auch keine neuen cookies gesetzt werden
$extra_iframe_redirect = optional_param('extra_iframe_redirect', '', PARAM_TEXT);
if ($extra_iframe_redirect) {
    echo $OUTPUT->header();
    echo '<div style="margin: 30px;"><a href="' . str_replace('extra_iframe_redirect', 'extra_iframe_redirect_disabled', $_SERVER['REQUEST_URI']) . '">Bitte hier klicken!</a></div>';
    echo $OUTPUT->footer();
    exit;
}

require_login(0, false, null, false, false);

if (isguestuser()) {
    // is guest user
    require_login();
    $SESSION->wantsurl = $CFG->wwwroot . '/blocks/exacomp/applogin_diggr_plus.php?' . $_SERVER['QUERY_STRING'] . '&withlogout=1';
    redirect($CFG->wwwroot . '/login/index.php');
    exit;
}

$moodle_redirect_token = required_param('moodle_redirect_token', PARAM_TEXT);

$applogin = $DB->get_record('block_exacompapplogin', ['moodle_redirect_token' => $moodle_redirect_token]);
if (!$applogin) {
    throw new Error('token not found');
}

$login_request_data = json_decode($applogin->request_data);

try {
    $tokens = block_exacomp_get_service_tokens(optional_param('services', '', PARAM_TEXT));
    block_exacomp_login_successfull($login_request_data);
} catch (Exception $e) {
    block_exacomp_logout();

    if (is_siteadmin($USER)) {
        // moodle does not allow a login with admin accounts
        // see external_generate_token_for_current_user()

        echo $OUTPUT->header();

        echo '<div class="login-container" style="max-width: 600px; padding: 60px 30px; text-align: center; margin: 35px auto;">';
        echo nl2br(block_exacomp_trans(["de:Ein Login mit einem Admin-Konto ist nicht möglich.", "en:A Login with admin-accounts is not possible."]));
        echo '</br><br/>';
        echo '<a href="' . $PAGE->url . '">' . block_exacomp_trans(["de:Mit anderem Benutzer einloggen", "en:Login with different User"]) . '</a>';
        echo '</div>';

        echo $OUTPUT->footer();
        exit;
    } else {
        echo $OUTPUT->header();

        echo '<div class="login-container" style="max-width: 600px; padding: 60px 30px; text-align: center; margin: 35px auto;">';
        echo nl2br(block_exacomp_trans(["de:Der Login für externe Apps wurde vom Administrator nicht konfiguriert.\n\nTechnische Info:", "en:The Login for external Apps is not configured.\n\nTechnical Details:"]));
        echo ' ' . $e->getMessage() . '<br/><br/>';
        echo 'An Administrator has to go to ' . new moodle_url('/blocks/exacomp/webservice_status.php?courseid=1') . ' and configure the Webservices';
        echo '</br><br/>';
        echo '<a href="' . $PAGE->url . '">' . block_exacomp_trans(["de:Login erneut versuchen", "en:Retry Login"]) . '</a>';
        echo '</div>';

        echo $OUTPUT->footer();
        exit;
    }
}

// hack add moodle sesskey too
$tokens[] = [
    'service' => 'sesskey',
    'token' => sesskey(),
];

$DB->update_record('block_exacompapplogin', (object)[
    'id' => $applogin->id,
    'result_data' => json_encode([
        'tokens' => $tokens,
    ]),
]);

if (@$login_request_data->usermapid) {
    $confirm = optional_param('confirm', false, PARAM_BOOL);

    if (!$confirm) {
        echo $OUTPUT->header();

        echo '<div style="text-align: center; padding: 60px 20px">';
        echo block_exacomp_trans(['de:Hiermit verknüpfen Sie Ihren MS Teams Benutzer mit dem Moodle Benutzer {$a}', 'en:You are connecting your MS Teams user with the Moodle user {$a}'], fullname($USER));
        echo '<br/><br/>';
        echo '<a href="' . $_SERVER['REQUEST_URI'] . '&action=logout&sesskey=' . sesskey() . '" class="btn btn-secondary">' . block_exacomp_trans(['de:Mit anderem Benutzer einloggen', 'en:Login with another user']) . '</a>';
        echo '&nbsp;&nbsp;&nbsp;';
        echo '<a href="' . $_SERVER['REQUEST_URI'] . '&confirm=1" class="btn btn-primary">' . block_exacomp_trans(['de:Weiter', 'en:Continue']) . '</a>';
        echo '</div>';

        echo $OUTPUT->footer();
        exit;
    }

    // came from o365 -> map the user
    $DB->update_record('block_exacomp_usermap', ['userid' => $USER->id, 'id' => $login_request_data->usermapid]);
}

$return_uri = $login_request_data->return_uri .
    (preg_match('!\\?!', $login_request_data->return_uri) ? '&' : '?') .
    'moodle_token=' . $applogin->moodle_data_token;

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
    header("Location: " . $return_uri);
    exit;
}
