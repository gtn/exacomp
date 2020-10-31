<?php

require __DIR__.'/inc.php';
require_once($CFG->libdir . '/externallib.php');
require_once __DIR__.'/externallib.php';

// TODO: create a setting for this
$additional_allowed_redirect_uris = [
	'http://localhost:3000',
	'https://localhost:3000',
	'http://diggr-plus.at',
	'https://diggr-plus.at',
	'http://www.diggr-plus.at',
	'https://www.diggr-plus.at',
];

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

function block_exacomp_get_login_data() {

	$services = optional_param('services', '', PARAM_TEXT);
	$tokens = block_exacomp_get_service_tokens($services);

	// get login data
	$data = block_exacomp_external::login();
	// add tokens
	$data['tokens'] = $tokens;

	// clean output
	$data = external_api::clean_returnvalue(block_exacomp_external::login_returns(), $data);

	// $data = json_encode($data, JSON_PRETTY_PRINT);

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

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/blocks/exacomp/applogin_diggr_plus.php');
$PAGE->set_pagelayout('embedded');

// Allow CORS requests.
header('Access-Control-Allow-Origin: *');

// always delete old login data
$DB->execute("DELETE FROM {block_exacompapplogin} WHERE created_at<?", [time()-60*30]);

$action = optional_param('action', '', PARAM_TEXT);

if ($action == 'get_login_url') {
	required_param('app', PARAM_TEXT);
	required_param('app_version', PARAM_TEXT);

	$return_uri = required_param('return_uri', PARAM_TEXT);
	$allowed_redirect_uris = array_merge([$CFG->wwwroot], $additional_allowed_redirect_uris);

	$return_uri_allowed = false;
	foreach ($allowed_redirect_uris as $allowed_redirect_uri) {
		$allowed_redirect_uri = preg_replace('!/$!', '', $allowed_redirect_uri).'/';
		if (preg_match('!^'.preg_quote($allowed_redirect_uri, '!').'!', $return_uri)) {
			$return_uri_allowed = true;
			break;
		}
	}

	if (!$return_uri_allowed) {
		$data = [
			'error' => block_exacomp_trans(['de:Zugriff für diese DiggrPlus Installation ist nicht erlaubt', 'en:Access form this DiggrPlus is not allowed'])
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
if ($action == 'login_result') {
	required_param('app', PARAM_TEXT);
	required_param('app_version', PARAM_TEXT);

	$moodle_data_token = required_param('moodle_token', PARAM_TEXT);
	//$app_token = required_param('app_token', PARAM_TEXT);

	$applogin = $DB->get_record('block_exacompapplogin', ['moodle_data_token'=>$moodle_data_token]);
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

	redirect(str_replace('action=logout', '', $_SERVER['REQUEST_URI']));
}


$SESSION->wantsurl = $CFG->wwwroot.'/blocks/exacomp/applogin_diggr_plus.php?'.$_SERVER['QUERY_STRING'].'&from_login=1';

require_login(0,false,null,false,false);

if (isguestuser()) {
	// is guest user
	require_login();
	$SESSION->wantsurl = $CFG->wwwroot.'/blocks/exacomp/applogin_diggr_plus.php?'.$_SERVER['QUERY_STRING'].'&withlogout=1';
	redirect($CFG->wwwroot.'/login/index.php');
	exit;
}


////Here after the user is logged in
////Check if this user is a teacher from eeducation. If they are: add to course with id=700
//$email = $loginData["data"]["user"]["email"];
//if(strcmp(strstr($email,"@"),"@eeducation.at") == 0){
//    //They are from eeducation ==> enrol them
////    $course = get_course(700);
//    $course = $DB->get_record('course', array('id' => 700), '*');
//    if($course != null){ //only proceed with enrolment, if course exists
//        $context = context_course::instance($course->id);
//        $userid = $loginData["data"]["user"]["id"];
//        $user = $DB->get_record('user', array('id' => $userid, 'deleted' => 0), '*', MUST_EXIST);
//        if (!is_enrolled($context, $user)) {
//            $enrol = enrol_get_plugin("manual"); //enrolment = manual
//            if ($enrol === null) {
//                return false;
//            }
//            $instances = enrol_get_instances($course->id, true);
//            $manualinstance = null;
//            foreach ($instances as $instance) {
//                if ($instance->enrol == "manual") {
//                    $manualinstance = $instance;
//                    break;
//                }
//            }
////            if ($manualinstance !== null) {
////                $instanceid = $enrol->add_default_instance($course);
////                if ($instanceid === null) {
////                    $instanceid = $enrol->add_instance($course);
////                }
////                $instance = $DB->get_record('enrol', array('id' => $instanceid));
////            }
//
//            if($manualinstance != null){
//                $enrol->enrol_user($manualinstance, $userid, 3); //The roleid of "editingteacher" is 3 in mdl_role table
//            }
//        }
//    }
//}

$moodle_redirect_token = required_param('moodle_redirect_token', PARAM_TEXT);

$applogin = $DB->get_record('block_exacompapplogin', ['moodle_redirect_token'=>$moodle_redirect_token]);
if (!$applogin) {
	throw new Error('token not found');
}

$request_data = json_decode($applogin->request_data);

$DB->update_record('block_exacompapplogin', (object)[
	'id' => $applogin->id,
	'result_data' => json_encode([
		'tokens' => block_exacomp_get_service_tokens($request_data->services)
	]),
]);

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

/*
} elseif (optional_param('from_login', '', PARAM_BOOL)) {
	// hat sich gerade eingeloggt, sofort weiterleiten
	header("Location: ".$return_uri);
	exit;
} else {
	// was already logged in, show info and continue button... always comes here?
	echo $OUTPUT->header();

	?>
	<script>
      function app_login_now() {
        document.location.href = <?=json_encode($return_uri)?>;
      }

      function app_relogin() {
        document.location.href = document.location.href + '&action=logout';
      }
	</script>
	<?php

	echo '<div style="padding-top: 60px; text-align: center;">';
	echo block_exacomp_trans(['de:Du bist eingeloggt als:', 'en:You are logged in as:']).' ';
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
*/
