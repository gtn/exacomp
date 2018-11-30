<?php
// This file is part of Exabis Competence Grid
//
// (c) 2016 GTN - Global Training Network GmbH <office@gtn-solutions.com>
//
// Exabis Competence Grid is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This script is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You can find the GNU General Public License at <http://www.gnu.org/licenses/>.
//
// This copyright notice MUST APPEAR in all copies of the script!

$domainname = 'https://gtn-solutions.com/moodle29test';

$params = new stdClass();

require_once('./curl.php');
$curl = new curl;
//$token_google = 548260;

print_r($token_google);
echo "



";
$serverurl = 'https://gtn-solutions.com/moodle29test/login/token.php?username=testws&password=pass&service=exacompservices';
$resp = $curl->get($serverurl);
$resp = json_decode($resp)->token;
$token = $resp;
print_r($token);
echo "



";

$serverurl_exaport = 'https://gtn-solutions.com/moodle29test/login/token.php?username=testws&password=pass&service=exaportservices';
$resp_exaport = $curl->get($serverurl_exaport);
$resp_exaport = json_decode($resp_exaport)->token;
print_r($resp_exaport);
echo "



";

$serverurl_moodle = 'http://gtn-solutions.com/moodle29test/login/token.php?username=testws&password=pass&token='.$token_google.'&service=moodle_mobile_app';
$resp_moodle = $curl->get($serverurl_exaport);
$resp_moodle = json_decode($resp_moodle)->token;
print_r($resp_moodle);

/*echo 'My local:';
$serverurl_moodle = 'http://localhost/Moodle32Dakora/login/token.php?username=teacher1&password=Teacher-111&service=moodle_mobile_app';
$resp_moodle = $curl->get($serverurl_exaport);
print_r($resp_moodle);
$resp_moodle = json_decode($resp_moodle)->token;
print_r($resp_moodle);
echo '<br>end local!<br>';*/


header('Content-Type: text/plain');



//REST CALL dakora_get_examples_pool_for_week
$functionname = 'dakora_get_competence_grid_for_profile';

$params = new stdClass();
$params->courseid = 4;
$params->userid = 4;
$params->subjectid = 3;


$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);

echo "
all topics:

";

//REST CALL dakora_get_examples_pool_for_week
$functionname = 'dakora_get_all_topics_by_course';

$params = new stdClass();
$params->courseid = 4;


$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);

echo "
all topics:

";
//REST CALL dakora_get_examples_pool_for_week
$functionname = 'dakora_get_admin_grading_scheme';

$params = new stdClass();


$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);

echo "
all topics:

";

//REST CALL dakora_get_examples_pool_for_week
$functionname = 'dakora_get_comp_grid_for_example';

$params = new stdClass();
$params->courseid = 2;
$params->exampleid = 175;

$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);

echo "
all topics:

";
/*
//REST CALL dakora_get_examples_pool_for_week
$functionname = 'dakora_get_descriptors_by_cross_subject';

$params = new stdClass();
$params->courseid = 4;
$params->crosssubjid = 30;
$params->userid=0;
$params->forall=0;


$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);

echo "
all topics:

";
header('Content-Type: text/plain');
//REST CALL dakora_get_examples_pool_for_week
$functionname = 'dakora_get_all_descriptors_by_cross_subject';

$params = new stdClass();
$params->courseid = 4;
$params->crosssubjid = 30;
$params->userid=0;
$params->forall=0;


$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);

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

echo "
descriptor children:

";

//REST CALL dakora_get_examples_pool_for_week ($courseid, $descriptorid, $userid, $forall)
$functionname = 'dakora_get_descriptor_children_for_cross_subject';

$params = new stdClass();
$params->courseid = 4;
$params->descriptorid = 9512;
$params->userid = 20;
$params->forall = 0;
$params->crosssubjid = 30;

$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);

echo "
descriptor children all

";

//REST CALL dakora_get_examples_pool_for_week ($courseid, $descriptorid, $userid, $forall)
$functionname = 'dakora_get_all_descriptor_children_for_cross_subject';

$params = new stdClass();
$params->courseid = 4;
$params->descriptorid = 9512;
$params->userid = 20;
$params->forall = 0;
$params->crosssubjid = 30;

$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);

echo "
descriptor example:

";

//REST CALL dakora_get_examples_pool_for_week ($courseid, $descriptorid, $userid, $forall)
$functionname = 'dakora_get_examples_for_descriptor';

$params = new stdClass();
$params->courseid = 4;
$params->descriptorid = 9512;
$params->userid = 0;
$params->forall = 0;

$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);

echo "
descriptor details:

";

//REST CALL dakora_get_examples_pool_for_week ($courseid, $descriptorid, $userid, $forall)
$functionname = 'dakora_get_examples_for_descriptor_for_crosssubject';

$params = new stdClass();
$params->courseid = 4;
$params->descriptorid = 9512;
$params->userid = 0;
$params->forall = 0;
$params->crosssubjid = 30;

$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);

echo "
descriptor details:

";

$functionname = 'dakora_get_descriptor_details';

$params = new stdClass();
$params->courseid = 4;
$params->descriptorid = 9480;
$params->userid = 0;
$params->forall = 0;
$params->crosssubjid = 30;

$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);

echo "
examples pool:

";*/


//REST CALL dakora_get_examples_pool_for_week ($courseid, $descriptorid, $userid, $forall)
/*$functionname = 'dakora_get_examples_pool';

$params = new stdClass();
$params->courseid = 4;
$params->userid = 0;

$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);

echo "
add example to time slot:

";

//REST CALL dakora_get_examples_pool_for_week ($courseid, $descriptorid, $userid, $forall)
/*$functionname = 'dakora_add_example_to_learning_calendar';

$params = new stdClass();
$params->courseid = 4;
$params->exampleid = 73;
$params->creatorid = 0;
$params->userid = 0;
$params->forall = 0;

$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);
*/
/*echo "
donnerstag:

";

$functionname = 'dakora_get_examples_for_time_slot';

$params = new stdClass();
$params->userid = 0;
$params->start = 1441234800;
$params->end = 1441299600;

$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);

echo "
freitag:

";

$functionname = 'dakora_get_examples_for_time_slot';

$params = new stdClass();
$params->userid = 0;
$params->start = 1441317600;
$params->end = 1441386000;

$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);
echo "

set time slot to 0

";




//REST CALL dakora_get_examples_pool_for_week ($courseid, $descriptorid, $userid, $forall)
/*$functionname = 'dakora_add_examples_to_students_schedule';

$examples = array();
$examples[] = 72;
$examples[] = 73;

$students = array();
$students[] = 20;
$students[] = 4;

$params = new stdClass();
$params->courseid = 4;
$params->examples = json_encode($examples);
$params->students = json_encode($students);
$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);

echo "
remove from schedule:


";*/
//REST CALL dakora_get_examples_pool_for_week ($courseid, $descriptorid, $userid, $forall)
/*$functionname = 'dakora_remove_example_from_schedule';

$params = new stdClass();
$params->scheduleid = 103;

$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);

echo "
add example:

";*/
