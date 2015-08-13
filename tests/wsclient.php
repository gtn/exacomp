<?php
// This file is NOT a part of Moodle - http://moodle.org/
//
// This client for Moodle 2 is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//

$domainname = 'http://gtn-solutions.com/moodle29';

$params = new stdClass();

require_once('./curl.php');
$curl = new curl;
$token_google = 762568;

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
$params->userid = 0;

$resp = $curl->get($serverurl, $params);
print_r($resp);
echo "

topics:

";

/// REST CALL BLOCK_EXACOMP_GET_TOPICS_BY_COURSE

$functionname = 'dakora_get_topics_by_course';

$params = new stdClass();
$params->courseid = 3;

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

$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);

echo "

descriptors children:

";

/// REST CALL dakora_get_descriptor_children
$functionname = 'dakora_get_descriptor_children';

$params = new stdClass();
$params->courseid = 3;
$params->descriptorid = 326;
$params->userid = 0;

$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);

echo "

examples

";

/// REST CALL dakora_get_descriptor_children
$functionname = 'dakora_get_examples_for_descriptor';

$params = new stdClass();
$params->courseid = 3;
$params->descriptorid = 327;

$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);

echo "

example overview

";

/// REST CALL dakora_get_descriptor_children
$functionname = 'dakora_get_example_overview';

$params = new stdClass();
$params->exampleid = 34;

$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);

echo "



";
/*
/// REST CALL BLOCK_EXACOMP_GET_TOPICS

$functionname = 'block_exacomp_get_topics';

$params = new stdClass();
$params->subjectid = 2;
$params->courseid = 2;

$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);

echo "



";

/// REST CALL BLOCK_EXACOMP_GET_SUBTOPICS

$functionname = 'block_exacomp_get_subtopics';

$params = new stdClass();

$params->courseid = 2;
$params->topicid = 14;

$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);

echo "



";

/// REST CALL BLOCK_EXACOMP_SET_SUBTOPIC

$functionname = 'block_exacomp_set_subtopic';
$params = new stdClass();
$params->courseid = 2;
$params->subtopicid = 70;
$params->value = 0;

$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);

echo "



";

/// REST CALL BLOCK_EXACOMP_GET_COMPETENCIES

$functionname = 'block_exacomp_get_competencies';

$params = new stdClass();

$params->courseid = 2;
$params->subtopicid = 70;

$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);

echo "



";
/// REST CALL BLOCK_EXACOMP_SET_COMPETENCE

$functionname = 'block_exacomp_set_competence';

$params = new stdClass();
$params->courseid = 2;
$params->descriptorid = 552;
$params->value = 1;


$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);

echo "



";
/// REST CALL BLOCK_EXACOMP_GET_ASSOCIATED_CONTENT

$functionname = 'block_exacomp_get_associated_content';

$params = new stdClass();
$params->courseid = 2;
$params->descriptorid = 552;

$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);


echo "



";

/// REST CALL BLOCK_EXACOMP_GET_ASSOCIATED_CONTENT

$functionname = 'block_exacomp_get_competence_by_id';

$params = new stdClass();
$params->competenceid = 552;

$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);


echo "



";

/// REST CALL BLOCK_EXACOMP_GET_ASSOCIATED_CONTENT

$functionname = 'block_exacomp_get_topic_by_id';

$params = new stdClass();
$params->topicid = 70;

$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);


echo "



";

/// REST CALL BLOCK_EXACOMP_GET_ASSIGN_INFORMATION
$functionname = 'block_exacomp_get_assign_information';

$params = new stdClass();
$params->assignid = 1;

$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);


/// REST CALL BLOCK_EXACOMP_UPDATE_ASSIGN_SUBMISSION
$functionname = 'block_exacomp_update_assign_submission';

$params = new stdClass();
$params->assignid = 1;
$params->onlinetext = "texteingabe";
$params->filename = "";

$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);

echo "



";
$functionname = 'block_exacomp_get_assign_information';

$params = new stdClass();
$params->assignid = 1;

$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);*/