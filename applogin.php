<?php

require __DIR__.'/inc.php';
require_once($CFG->libdir . '/externallib.php');
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

function block_exacomp_get_login_data() {
	$exa_tokens = [];

	$services = optional_param('services', '', PARAM_TEXT);
	$services = array_keys(
		['moodle_mobile_app' => 1, 'exacompservices' => 1] // default services
		+ ($services ? array_flip(explode(',', $services)) : []));

	foreach ($services as $service) {
		$token = block_exacomp_load_service($service);
		$exa_tokens[] = [
			'service' => $service,
			'token' => $token,
		];
	}

	// get login data
	$data = block_exacomp_external::login();
	// add tokens
	$data['tokens'] = $exa_tokens;

	// clean output
	$data = external_api::clean_returnvalue(block_exacomp_external::login_returns(), $data);

	// $data = json_encode($data, JSON_PRETTY_PRINT);

	$data = [
		'type' => 'login_successful',
		'data' => $data,
	];

	return $data;
}

function block_exacomp_logout() {
	$authsequence = get_enabled_auth_plugins(); // auths, in sequence
	foreach($authsequence as $authname) {
	    $authplugin = get_auth_plugin($authname);
	    $authplugin->logoutpage_hook();
	}

	require_logout();
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
		'login_method' => get_config('exacomp', 'new_app_login') ? 'moodle_frame' : '',
	);

	header('Content-Type: application/json');
	echo json_encode($info);
	exit;
}

if ($action == 'logout') {
	block_exacomp_logout();

	redirect(str_replace('action=logout', '', $_SERVER['REQUEST_URI']));
}



$PAGE->set_context(context_system::instance());
require_login(0);



$PAGE->set_url('/blocks/exacomp/applogin.php');
$PAGE->set_pagelayout('embedded');

try {
	// no guest user allowed
	core_user::require_active_user($USER);
} catch (\Exception $e) {
	// is guest user
	redirect($_SERVER['REQUEST_URI'].'&action=logout');
	exit;
}

$loginData = block_exacomp_get_login_data();

if (preg_match('!'.preg_quote($CFG->wwwroot, '!').'/login!', @$_SERVER['HTTP_REFERER'])) {
	// came from login form

	echo $OUTPUT->header();

	?>
	<script>
		if (top !== window) {
			// for older browsers only string is allowed
			top.postMessage(<?php echo json_encode(json_encode($loginData)) ?>);
		}
	</script>
	<?php

	echo '<div style="width: 100%; text-align: center; padding-top: 100px;">Login erfolgreich</div>';

	echo $OUTPUT->footer();

	block_exacomp_logout();

	exit;
} else {
	// was already logged in, show info and continue button

	echo $OUTPUT->header();

	?>
	<script>
		function app_login_now() {
			if (top !== window) {
				// for older browsers only string is allowed
				top.postMessage(<?php echo json_encode(json_encode($loginData)) ?>);
			}
		}

		function app_relogin() {
			document.location.href = document.location.href + '&action=logout';
		}
	</script>
	<?php

	echo '<div style="padding-top: 60px; text-align: center;">';
	echo block_exacomp_trans(['de:Du bist eingeloggt als:', 'en:You are loggedin as:']).' ';
	echo fullname($USER);
	echo '<br/>';
	echo '<br/>';
	echo '<button type="button" class="btn btn-primary" onclick="app_login_now()">'.block_exacomp_trans(['de:Fortfahren', 'en:Continue']).'</button>';
	echo '<br/>';
	echo '<br/>';
	echo '<button type="button" class="btn btn-secondary" onclick="app_relogin()">'.block_exacomp_trans(['de:Als anderer Benutzer einloggen', 'en:Login as different user']).'</button>';
	echo '</div>';

	echo $OUTPUT->footer();

	exit;
}
