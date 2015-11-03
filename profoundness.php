<?php

/* * *************************************************************
 *  Copyright notice
*
*  (c) 2014 exabis internet solutions <info@exabis.at>
*  All rights reserved
*
*  You can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  This module is based on the Collaborative Moodle Modules from
*  NCSA Education Division (http://www.ncsa.uiuc.edu)
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
* ************************************************************* */

require_once dirname(__FILE__)."/inc.php";

global $DB, $OUTPUT, $PAGE, $USER, $version;

$courseid = required_param('courseid', PARAM_INT);
$studentid = optional_param('studentid', 0, PARAM_INT);

$showevaluation = ($version) ? true : optional_param("showevaluation", false, PARAM_BOOL);
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_simplehtml', $courseid);
}

require_login($course);

$context = context_course::instance($courseid);

/* PAGE IDENTIFIER - MUST BE CHANGED. Please use string identifier from lang file */
$page_identifier = 'tab_profoundness';

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/profoundness.php', array('courseid' => $courseid));
$PAGE->set_heading(get_string('pluginname', 'block_exacomp'));
$PAGE->set_title(get_string($page_identifier, 'block_exacomp'));

// build breadcrumbs navigation
block_exacomp_build_breadcrum_navigation($courseid);

$output = block_exacomp_get_renderer();

// build tab navigation & print header
echo $output->header($context,$courseid, $page_identifier);

// CHECK TEACHER
$isTeacher = block_exacomp_is_teacher($context);

$activities = block_exacomp_get_activities_by_course($courseid);
$course_settings = block_exacomp_get_settings_by_course($courseid);

if($course_settings->uses_activities && !$activities && !$course_settings->show_all_descriptors)
	echo $output->print_no_activities_warning($isTeacher);
else{
	list($subjects, $topics, $selectedSubject, $selectedTopic) = block_exacomp_init_overview_data($courseid, null, optional_param('topicid', 0, PARAM_INT), optional_param('niveauid', 0, PARAM_INT));
	
	// SAVA DATA
	if (($action = optional_param("action", "", PARAM_TEXT) ) == "save") {
		// DESCRIPTOR DATA
		block_exacomp_save_competencies(isset($_POST['data']) ? $_POST['data'] : array(), $courseid, ($isTeacher) ? block_exacomp::ROLE_TEACHER : block_exacomp::ROLE_STUDENT, TYPE_DESCRIPTOR, $selectedTopic->id, $selectedSubject->id);
		
	}
	//Delete timestamp (end|start) from example
	if($example_del = optional_param('exampleid', 0, PARAM_INT)){
		block_exacomp_delete_timefield($example_del, optional_param('deletestart', 0, PARAM_INT), optional_param('deleteend', 0, PARAM_INT));
	}

	$coursestudents = block_exacomp_get_students_by_course($courseid);
	if($studentid == 0) $studentid = reset($coursestudents)->id;
	// IF TEACHER SHOW ALL COURSE STUDENTS, IF NOT ONLY CURRENT USER
	$students = ($isTeacher) ? array($DB->get_record('user', array('id' => $studentid))) : array($USER);
	foreach($students as $student)
		$student = block_exacomp_get_user_information_by_course($student, $courseid);

	echo $output->print_competence_overview_form_start((isset($selectedTopic))?$selectedTopic:null, (isset($selectedSubject))?$selectedSubject:null,$studentid);

	//dropdowns for subjects and topics
	echo $output->print_overview_dropdowns(block_exacomp_get_schooltypetree_by_topics($subjects), $selectedSubject->id, $selectedTopic->id, $students);
	
	if ($isTeacher) {
		echo ' '.get_string("choosestudent","block_exacomp").' ';
		echo block_exacomp_studentselector($coursestudents,$studentid,$PAGE->url . ($selectedSubject->id > 0 ? "&subjectid=".$selectedSubject->id : "")
				. ($selectedTopic->id > 0 ? "&topicid=".$selectedTopic->id : ""));
	}
	
	$schooltype = block_exacomp_get_schooltype_title_by_subject($selectedSubject);
	$cat = block_exacomp_get_category($selectedTopic);
		
	$scheme = block_exacomp_get_grading_scheme($courseid);
	if($selectedTopic->id != SHOW_ALL_TOPICS){
		echo $output->print_overview_metadata($schooltype, $selectedSubject, $selectedTopic, $cat);
	}
	
	$subjects = block_exacomp_get_competence_tree($courseid,(isset($selectedSubject))?$selectedSubject->id:null,false,(isset($selectedTopic))?$selectedTopic->id:null,
			false);

	echo $output->print_profoundness($subjects, $courseid, $students, $isTeacher ? block_exacomp::ROLE_TEACHER : block_exacomp::ROLE_STUDENT);
}
/* END CONTENT REGION */
echo $output->footer();

?>