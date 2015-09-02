<?php
// This file is NOT a part of Moodle - http://moodle.org/
//
// This client for Moodle 2 is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//

$domainname = 'http://gtn-solutions.com/moodle29test';

$params = new stdClass();

require_once('./curl.php');
$curl = new curl;
$token_google = 199809;

print_r($token_google);
echo "



";
$serverurl = 'http://gtn-solutions.com/moodle29test/login/token.php?username=student2&password=Student2!&token='.$token_google.'&service=exacompservices';
$resp = $curl->get($serverurl);
$resp = json_decode($resp)->token;
$token = $resp;
print_r($token);
echo "



";

$serverurl_exaport = 'http://gtn-solutions.com/moodle29test/login/token.php?username=student2&password=Student2!&token='.$token_google.'&service=exaportservices';
$resp_exaport = $curl->get($serverurl_exaport);
$resp_exaport = json_decode($resp_exaport)->token;
print_r($resp_exaport);
echo "



";

$serverurl_moodle = 'http://gtn-solutions.com/moodle29test/login/token.php?username=student2&password=Student2!&token='.$token_google.'&service=moodle_mobile_app';
$resp_moodle = $curl->get($serverurl_exaport);
$resp_moodle = json_decode($resp_moodle)->token;
print_r($resp_moodle);
echo "
cross subjects:

";
header('Content-Type: text/plain');
//REST CALL dakora_get_examples_pool_for_week
$functionname = 'dakora_get_cross_subjects_by_course';

$params = new stdClass();
$params->courseid = 4;
$params->userid =0;
$params->forall = 0;


$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);

echo "
cross subject descriptors:

";


//REST CALL dakora_get_examples_pool_for_week
$functionname = 'dakora_get_descriptors_by_cross_subject';

$params = new stdClass();
$params->courseid = 4;
$params->crosssubjid = 30;
$params->userid =20;
$params->forall = 0;

$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);