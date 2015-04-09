<?php
// This file is NOT a part of Moodle - http://moodle.org/
//
// This client for Moodle 2 is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//

$token = 'dc4c9e77294d901f8247d1dccf63ce30';
$domainname = 'http://localhost/moodle271/';
$functionname = 'block_exacomp_get_courses';

$params = new stdClass();

require_once('./curl.php');
$curl = new curl;

$serverurl = 'http://localhost/moodle271/login/token.php?username=schueler&password=Schueler123!&service=exacompservices';
$resp = $curl->get($serverurl);
$resp = json_decode($resp)->token;
$token = $resp;
print_r($token);

/// REST CALL BLOCK_EXACOMP_GET_COURSES
header('Content-Type: text/plain');
$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->get($serverurl);
print_r($resp);


/// REST CALL BLOCK_EXACOMP_GET_SUBJECTS

$functionname = 'block_exacomp_get_subjects';

$params = new stdClass();
$params->courseid = 2;

$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$resp = $curl->post($serverurl, $params);
print_r($resp);

echo "



";
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
print_r($resp);