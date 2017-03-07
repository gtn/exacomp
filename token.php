<?php

define('AJAX_SCRIPT', true);
define('REQUIRE_CORRECT_ACCESS', true);
define('NO_MOODLE_COOKIES', true);

require __DIR__.'/inc.php';

function block_exacomp_load_service($service) {
	extract($GLOBALS);

	ob_start();
	try {
		$_POST['service'] = $service;
		require __DIR__.'/../../login/token.php';
	} catch (moodle_exception $e) {
		if ($e->errorcode == 'servicenotavailable') {
			return null;
		}

		global $A2FA_ERROR;
		if ($A2FA_ERROR) {
			echo json_encode([
				'error' => $A2FA_ERROR,
				'errorcode' => 'a2farequired',
			], JSON_PRETTY_PRINT);
			exit;
		}

		throw $e;
	}
	$ret = ob_get_clean();

	$data = json_decode($ret);
	if ($data && $data->token) {
		return $data->token;
	} else {
		return null;
	}
}

// Allow CORS requests.
header('Access-Control-Allow-Origin: *');
echo $OUTPUT->header();

required_param('app', PARAM_TEXT);
required_param('app_version', PARAM_TEXT);

if (optional_param('testconnection', false, PARAM_BOOL)) {
	echo json_encode([
		'moodleName' => $DB->get_field('course', 'fullname', ['id' => 1]),
	], JSON_PRETTY_PRINT);
	exit;
}

$block_exacomp_tokens = [];

$services = optional_param('services', '', PARAM_TEXT);
$services = array_keys(
	['moodle_mobile_app' => 1, 'exacompservices' => 1] // default services
	+ ($services ? array_flip(explode(',', $services)) : []));

foreach ($services as $service) {
	$token = block_exacomp_load_service($service);
	$block_exacomp_tokens[$service] = $token;
}

require __DIR__.'/externallib.php';

$data = block_exacomp_external::login();
$data['tokens'] = $block_exacomp_tokens;

echo json_encode($data, JSON_PRETTY_PRINT);
