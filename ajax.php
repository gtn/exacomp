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
switch($action){
	case ('competence') : 
		$userid = required_param ( 'userid', PARAM_INT );
		$compid = required_param ( 'compid', PARAM_INT );
		$comptype = required_param ( 'comptype', PARAM_INT );
		$value = required_param ( 'value', PARAM_INT );
		
		echo block_exacomp_set_user_competence ( $userid, $compid, $comptype, $courseid, ($isTeacher) ? ROLE_TEACHER : ROLE_STUDENT, $value );
	break;
	case ('example') : 
		$userid = required_param ( 'userid', PARAM_INT );
		$exampleid = required_param ( 'exampleid', PARAM_INT );
		$value = optional_param ( 'value', null, PARAM_INT );
		$starttime = optional_param ( 'starttime', 0, PARAM_INT );
		$endtime = optional_param ( 'endtime', 0, PARAM_INT );
		$studypartner = optional_param ( 'studypartner', 'self', PARAM_TEXT );
		
		echo block_exacomp_set_user_example($userid, $exampleid, $courseid, ($isTeacher) ? ROLE_TEACHER : ROLE_STUDENT, $value, $starttime, $endtime, $studypartner);
	break;
	case ('crosssubj-title') :
		var_dump("inhere-title");
		$crosssubjid = required_param('crosssubjid', PARAM_INT);
		$title = required_param('title', PARAM_TEXT);
		
		echo block_exacomp_save_cross_subject_title($crosssubjid, $title);
	break;
	case ('crosssubj-description') :
		var_dump("inhere-description");
		$crosssubjid = required_param('crosssubjid', PARAM_INT);
		$description = required_param('description', PARAM_TEXT);
		
		echo block_exacomp_save_cross_subject_description($crosssubjid, $description);
	break;
	case ('crosssubj-descriptors'):
		$descrid = required_param('descrid', PARAM_INT);
		$crosssubjects = required_param('crosssubjects', PARAM_TEXT);
		$subj_ids = json_decode($crosssubjects);
		
		$DB->delete_records(DB_DESCCROSS,array('descrid'=>$descrid));
		foreach($subj_ids as $subj_id)
			block_exacomp_set_cross_subject_descriptor($subj_id,$descrid);
			
		break;
}