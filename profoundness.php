<?php

require_once __DIR__."/inc.php";

$courseid = required_param('courseid', PARAM_INT);
$studentid = optional_param('studentid', 0, PARAM_INT);
$showevaluation = optional_param("showevaluation", true, PARAM_BOOL);

require_login($courseid);

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
echo $output->header_v2($page_identifier);

// CHECK TEACHER
$isTeacher = block_exacomp_is_teacher();

$activities = block_exacomp_get_activities_by_course($courseid);
$course_settings = block_exacomp_get_settings_by_course($courseid);

if($course_settings->uses_activities && !$activities && !$course_settings->show_all_descriptors) {
	echo $output->print_no_activities_warning($isTeacher);
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

$studentid = $student->id;
$students = array($student);

foreach($students as $student) {
	$student = block_exacomp_get_user_information_by_course($student, $courseid);
}



list($tmp2, $subjects, $topics, $tmp, $selectedSubject, $selectedTopic) = block_exacomp_init_overview_data($courseid, null, optional_param('topicid', 0, PARAM_INT), optional_param('niveauid', 0, PARAM_INT), false);

// SAVA DATA
if (($action = optional_param("action", "", PARAM_TEXT) ) == "save") {
	// DESCRIPTOR DATA
	block_exacomp_save_competencies(isset($_POST['data']) ? $_POST['data'] : array(), $courseid, ($isTeacher) ? \block_exacomp\ROLE_TEACHER : \block_exacomp\ROLE_STUDENT, TYPE_DESCRIPTOR, $selectedTopic->id, $selectedSubject->id);

}
//Delete timestamp (end|start) from example
/*
if($example_del = optional_param('exampleid', 0, PARAM_INT)){
	block_exacomp_delete_timefield($example_del, optional_param('deletestart', 0, PARAM_INT), optional_param('deleteend', 0, PARAM_INT));
}
*/

echo $output->print_competence_overview_form_start((isset($selectedTopic))?$selectedTopic:null, (isset($selectedSubject))?$selectedSubject:null,$studentid);

//dropdowns for subjects and topics
echo $output->print_overview_dropdowns('profoundness', $students);

if ($isTeacher) {
	echo ' '.get_string("choosestudent","block_exacomp").' ';
	echo $output->print_studentselector($coursestudents,$studentid);
}

$schooltype = block_exacomp_get_schooltype_title_by_subject($selectedSubject);
$cat = block_exacomp_get_category($selectedTopic);

$scheme = block_exacomp_get_grading_scheme($courseid);
if($selectedTopic->id != block_exacomp\SHOW_ALL_TOPICS){
	echo $output->print_overview_metadata($schooltype, $selectedSubject, $selectedTopic, $cat);
}

$subjects = block_exacomp_get_competence_tree($courseid, null, (isset($selectedSubject))?$selectedSubject->id:null,false,(isset($selectedTopic))?$selectedTopic->id:null,
		false);

echo $output->print_profoundness($subjects, $courseid, $students, $isTeacher ? \block_exacomp\ROLE_TEACHER : \block_exacomp\ROLE_STUDENT);

/* END CONTENT REGION */
echo $output->footer();
