<?php
/*
 * *************************************************************
 * Copyright notice
 *
 * (c) 2014 exabis internet solutions <info@exabis.at>
 * All rights reserved
 *
 * You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This module is based on the Collaborative Moodle Modules from
 * NCSA Education Division (http://www.ncsa.uiuc.edu)
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 * *************************************************************
 */
require_once dirname ( __FILE__ ) . "/inc.php";
global $DB, $USER;

$courseid = required_param ( 'courseid', PARAM_INT );
if (! $course = $DB->get_record ( 'course', array (
		'id' => $courseid 
) )) {
	print_error ( 'invalidcourse', 'block_simplehtml', $courseid );
}

require_login ( $course );
$context = context_course::instance ( $courseid );
$isTeacher = (has_capability ( 'block/exacomp:teacher', $context )) ? true : false;

$action = optional_param ( 'action', 'competence', PARAM_TEXT );
if ($action == 'competence') {
	$userid = required_param ( 'userid', PARAM_INT );
	$compid = required_param ( 'compid', PARAM_INT );
	$comptype = required_param ( 'comptype', PARAM_INT );
	$value = required_param ( 'value', PARAM_INT );
	
	echo block_exacomp_set_user_competence ( $userid, $compid, $comptype, $courseid, ($isTeacher) ? ROLE_TEACHER : ROLE_STUDENT, $value );
} else {
	$userid = required_param ( 'userid', PARAM_INT );
	$exampleid = required_param ( 'exampleid', PARAM_INT );
	$value = optional_param ( 'value', null, PARAM_INT );
	$starttime = optional_param ( 'starttime', 0, PARAM_INT );
	$endtime = optional_param ( 'endtime', 0, PARAM_INT );
	$studypartner = optional_param ( 'studypartner', 'self', PARAM_TEXT );
	
	echo block_exacomp_set_user_example($userid, $exampleid, $courseid, ($isTeacher) ? ROLE_TEACHER : ROLE_STUDENT, $value, $starttime, $endtime, $studypartner);
}