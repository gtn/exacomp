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

require __DIR__.'/inc.php';

$courseid = required_param('courseid', PARAM_INT);
$studentid = optional_param('studentid', 0, PARAM_INT);
$showevaluation = optional_param("showevaluation", true, PARAM_BOOL);

require_login($courseid);

/* PAGE IDENTIFIER - MUST BE CHANGED. Please use string identifier from lang file */
$page_identifier = 'tab_profoundness';

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/profoundness.php', array('courseid' => $courseid));
$PAGE->set_heading(block_exacomp_get_string('blocktitle'));
$PAGE->set_title(block_exacomp_get_string($page_identifier));

// build breadcrumbs navigation
block_exacomp_build_breadcrum_navigation($courseid);

$output = block_exacomp_get_renderer();

// build tab navigation & print header
echo $output->header_v2($page_identifier);

// CHECK TEACHER
$isTeacher = block_exacomp_is_teacher();

$activities = block_exacomp_get_activities_by_course($courseid);
$course_settings = block_exacomp_get_settings_by_course($courseid);

if($course_settings->uses_activities && !$activities && !$course_settings->show_all_descriptors) {
	echo $output->no_activities_warning($isTeacher);
	$output->footer();
	exit;
}

if ($isTeacher){
	$coursestudents = block_exacomp_get_students_by_course($courseid);
	if (!empty($coursestudents[$studentid])) {
		$student = $coursestudents[$studentid];
	} else {
		$student = reset($coursestudents);
	}
} else {
	$student = $USER;
}

if (!$coursestudents) {
	echo block_exacomp_get_string('nostudents');
	$output->footer();
	exit;
}

$studentid = $student->id;
$students = array($student);

foreach ($students as $student) {
	$student = block_exacomp_get_user_information_by_course($student, $courseid);
}



list($tmp2, $subjects, $topics, $tmp, $selectedSubject, $selectedTopic) = block_exacomp_init_overview_data($courseid, null, optional_param('topicid', 0, PARAM_INT), optional_param('niveauid', 0, PARAM_INT), false);

// SAVA DATA
if (($action = optional_param("action", "", PARAM_TEXT) ) == "save") {
	// DESCRIPTOR DATA
	block_exacomp_save_competences(isset($_POST['data']) ? $_POST['data'] : array(), $courseid, ($isTeacher) ? BLOCK_EXACOMP_ROLE_TEACHER : BLOCK_EXACOMP_ROLE_STUDENT, BLOCK_EXACOMP_TYPE_DESCRIPTOR, $selectedTopic->id, $selectedSubject->id);

}
//Delete timestamp (end|start) from example
/*
if($example_del = optional_param('exampleid', 0, PARAM_INT)){
	block_exacomp_delete_timefield($example_del, optional_param('deletestart', 0, PARAM_INT), optional_param('deleteend', 0, PARAM_INT));
}
*/

echo $output->competence_overview_form_start((isset($selectedTopic))?$selectedTopic:null, (isset($selectedSubject))?$selectedSubject:null,$studentid);

//dropdowns for subjects and topics
echo $output->overview_dropdowns('profoundness', $students);

if ($isTeacher) {
	echo ' '.block_exacomp_get_string("choosestudent").' ';
	echo $output->studentselector($coursestudents,$studentid);
}

$schooltype = block_exacomp_get_schooltype_title_by_subject($selectedSubject);
$cat = block_exacomp_get_category($selectedTopic);

$scheme = block_exacomp_get_grading_scheme($courseid);
if($selectedTopic->id != BLOCK_EXACOMP_SHOW_ALL_TOPICS){
	echo $output->overview_metadata($schooltype, $selectedSubject, $selectedTopic, $cat);
}

$subjects = block_exacomp_get_competence_tree($courseid, null, (isset($selectedSubject))?$selectedSubject->id:null,false,(isset($selectedTopic))?$selectedTopic->id:null,
		false);

echo $output->profoundness($subjects, $courseid, $students, $isTeacher ? BLOCK_EXACOMP_ROLE_TEACHER : BLOCK_EXACOMP_ROLE_STUDENT);

/* END CONTENT REGION */
echo $output->footer();
