<?php

define('AJAX_SCRIPT', true);
define('REQUIRE_CORRECT_ACCESS', true);
define('NO_MOODLE_COOKIES', true);

require __DIR__.'/inc.php';

$block_exacomp_tokens = [];

$services = @$_GET['services'];

function block_exacomp_load_service($service) {
	extract($GLOBALS);

	ob_start();
	try {
		$_POST['service'] = $service;
		require __DIR__.'/../../login/token.php';
	} catch (moodle_exception $e) {
		if ($e->errorcode == 'servicenotavailable') {
			return null;
		} else {
			throw $e;
		}
	}
	$ret = ob_get_clean();

	$data = json_decode($ret);
	if ($data && $data->token) {
		return $data->token;
	} else {
		return null;
	}
}

if ($services) {
	$services = explode(',', $services);

	foreach ($services as $service) {
		$token = block_exacomp_load_service($service);
		if ($token) {
			$block_exacomp_tokens[$service] = $token;
		}
	}
}

require __DIR__.'/externallib.php';

$data = block_exacomp_external::login();
$data['config']['tokens'] = $block_exacomp_tokens;

echo json_encode($data, JSON_PRETTY_PRINT);
