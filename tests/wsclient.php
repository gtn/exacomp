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

$domainname = 'http://gtn-solutions.com/moodle29';

$params = new stdClass();

require_once('./curl.php');
$curl = new curl;
$token_google = 844659;

print_r($token_google);
echo "



";
$serverurl = 'http://gtn-solutions.com/moodle29/login/token.php?username=student2&password=Student2!&token='.$token_google.'&service=exacompservices';
$resp = $curl->get($serverurl);
$resp = json_decode($resp)->token;
$token = $resp;
print_r($token);
echo "



";

$serverurl_exaport = 'http://gtn-solutions.com/moodle29/login/token.php?username=student2&password=Student2!&token='.$token_google.'&service=exaportservices';
$resp_exaport = $curl->get($serverurl_exaport);
$resp_exaport = json_decode($resp_exaport)->token;
print_r($resp_exaport);
echo "



";

$serverurl_moodle = 'http://gtn-solutions.com/moodle29/login/token.php?username=student2&password=Student2!&token='.$token_google.'&service=moodle_mobile_app';
$resp_moodle = $curl->get($serverurl_exaport);
$resp_moodle = json_decode($resp_moodle)->token;
print_r($resp_moodle);
echo "

courses:

";

/// REST CALL BLOCK_EXACOMP_GET_COURSES
header('Content-Type: text/plain');

$functionname = 'dakora_get_courses';
$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;

$params = new stdClass();
$params->userid = 4;

$resp = $curl->get($serverurl, $params);
print_r($resp);
echo "

topics:

";

/// REST CALL BLOCK_EXACOMP_GET_TOPICS_BY_COURSE

$functionname = 'dakora_get_topics_by_course';

$params = new stdClass();
$params->courseid = 4;

$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);

echo "

descriptors:

";

/// REST CALL dakora_get_descriptors

$functionname = 'dakora_get_descriptors';

$params = new stdClass();
$params->courseid = 3;
$params->topicid = 13;
$params->userid = 0;
$params->forall = 1;

$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);

echo "

descriptors !for all:

";


/// REST CALL dakora_get_descriptors

$functionname = 'dakora_get_descriptors';

$params = new stdClass();
$params->courseid = 3;
$params->topicid = 13;
$params->userid = 0;
$params->forall = 0;

$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);

echo "

descriptors children for all:

";
/// REST CALL dakora_get_descriptor_children
$functionname = 'dakora_get_descriptor_children';

$params = new stdClass();
$params->courseid = 3;
$params->descriptorid = 326;
$params->userid = 0;
$params->forall = 1;

$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);

echo "

children !forall

";

/// REST CALL dakora_get_descriptor_children
$functionname = 'dakora_get_descriptor_children';

$params = new stdClass();
$params->courseid = 3;
$params->descriptorid = 326;
$params->userid = 0;
$params->forall = 0;

$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);

echo "

examples

";


/// REST CALL dakora_get_descriptor_children
$functionname = 'dakora_get_examples_for_descriptor';

$params = new stdClass();
$params->courseid = 4;
$params->descriptorid = 1184;
$params->userid = 0;
$params->forall = 0;

$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);

echo "

examples for all 

";

/// REST CALL dakora_get_descriptor_children
$functionname = 'dakora_get_examples_for_descriptor';

$params = new stdClass();
$params->courseid = 4;
$params->descriptorid = 1184;
$params->userid = 0;
$params->forall = 1;

$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);

echo "

example overview

";



/// REST CALL dakora_get_descriptor_children
$functionname = 'dakora_get_example_overview';

$params = new stdClass();
$params->exampleid = 33;

$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);

echo "

add example to schedule

";

/// REST CALL dakora_add_example_to_learning_calendar
$functionname = 'dakora_add_example_to_learning_calendar';

$params = new stdClass();
$params->courseid = 4;
$params->exampleid = 55;
$params->creatorid = 0;
$params->userid = 4;
$params->forall = 0;


$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);

echo "

add example to all
";

/// REST CALL dakora_add_example_to_learning_calendar
$functionname = 'dakora_add_example_to_learning_calendar';

$params = new stdClass();
$params->courseid = 4;
$params->exampleid = 55;
$params->creatorid = 0;
$params->userid = 0;
$params->forall = 1;


$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);


echo "

descriptors for example

";



// REST CALL dakora_get_descriptors_for_example
$functionname = 'dakora_get_descriptors_for_example';

$params = new stdClass();
$params->exampleid = 33;
$params->courseid = 3;
$params->userid = 4;
$params->forall = 0;


$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);


echo " get descriptors for examples for all 


";

// REST CALL dakora_get_descriptors_for_example
$functionname = 'dakora_get_descriptors_for_example';

$params = new stdClass();
$params->exampleid = 33;
$params->courseid = 3;
$params->userid = 0;
$params->forall = 1;


$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);


echo "

example grading:

";
// REST CALL dakora_get_example_grading
$functionname = 'dakora_get_example_grading';

$params = new stdClass();
$params->exampleid = 33;
$params->courseid = 3;
$params->userid = 4;


$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);

echo "

user role:

";

//REST CALL dakora_get_user_role
$functionname = 'dakora_get_user_role';

$params = new stdClass();

$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);

echo "


";

//REST CALL dakora_get_students_and_groups_for_course
$functionname = 'dakora_get_students_and_groups_for_course';

$params = new stdClass();
$params->courseid = 3;

$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);

echo "


";

//REST CALL dakora_get_examples_pool_for_week
$functionname = 'dakora_get_examples_pool';

$params = new stdClass();
$params->courseid = 4;
$params->userid =4;


$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);

echo "


";


/*//REST CALL dakora_set_example_time_slot
$functionname = 'dakora_set_example_time_slot';

$params = new stdClass();
$params->courseid = 3;
$params->exampleid = 31;
$params->studentid = 4;
$params->start = 1439794800;
$params->end = 1439796600;

$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);

echo "


";

//REST CALL dakora_remove_example_from_schedule
$functionname = 'dakora_remove_example_from_schedule';

$params = new stdClass();
$params->courseid = 3;
$params->exampleid = 3;
$params->studentid = 4;

$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);

echo "


";*/

//REST CALL dakora_get_examples_for_time_slot
$functionname = 'dakora_get_examples_for_time_slot';

$params = new stdClass();
$params->userid = 0;
$params->start = 1440542464;
$params->end = 1440628864;

$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);

echo "


";

//REST CALL dakora_get_cross_subjects
/*$functionname = 'dakora_get_cross_subjects';

$params = new stdClass();
$params->userid = 0;
$params->courseid = 4;

$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);

echo "


";
*/
