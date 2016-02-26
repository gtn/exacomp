<?php
// This file is part of Exabis Competencies
//
// (c) 2016 GTN - Global Training Network GmbH <office@gtn-solutions.com>
//
// Exabis Competencies is free software: you can redistribute it and/or modify
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
$showevaluation = optional_param("showevaluation", true, PARAM_BOOL);
$group = optional_param('group', 0, PARAM_INT);

$editmode = optional_param('editmode', 0, PARAM_BOOL);
$subjectid = optional_param('subjectid', 0, PARAM_INT);

$topicid = optional_param('topicid', 0, PARAM_INT);
$niveauid = optional_param('niveauid', block_exacomp\SHOW_ALL_NIVEAUS, PARAM_INT);

require_login($courseid);

// CHECK TEACHER
$isTeacher = block_exacomp_is_teacher();
if(!$isTeacher) $editmode = 0;

$studentid = block_exacomp_get_studentid($isTeacher) ;
if($studentid == 0)
	$studentid = BLOCK_EXACOMP_SHOW_ALL_STUDENTS;

if($editmode) {
	$selectedStudentid = $studentid;
	$studentid = 0;
}

$page_identifier = 'tab_competence_overview';

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/assign_competencies.php', [
	'courseid' => $courseid,
	'showevaluation' => $showevaluation,
	'studentid' => $studentid,
	'editmode' => $editmode,
	'niveauid' => $niveauid,
	'subjectid' => $subjectid,
	'topicid' => $topicid,
]);
$PAGE->set_heading(get_string('pluginname', 'block_exacomp'));
$PAGE->set_title(get_string($page_identifier, 'block_exacomp'));

// build breadcrumbs navigation
block_exacomp_build_breadcrum_navigation($courseid);

/**
 * @var block_exacomp_renderer
 */
$output = block_exacomp_get_renderer();
$output->requires()->js('/blocks/exacomp/javascript/jquery.inputmask.bundle.js', true);
$output->requires()->js('/blocks/exacomp/javascript/competence_tree_common.js', true);
$output->requires()->css('/blocks/exacomp/css/competence_tree_common.css');
$output->editmode = $editmode;

$activities = block_exacomp_get_activities_by_course($courseid);
$course_settings = block_exacomp_get_settings_by_course($courseid);

if($course_settings->uses_activities && !$activities && !$course_settings->show_all_descriptors) {
	echo $output->header_v2($page_identifier);
	echo $output->no_activities_warning($isTeacher);
	echo $output->footer();
	exit;
}

$ret = block_exacomp_init_overview_data($courseid, $subjectid, $topicid, $niveauid, $editmode, $isTeacher, ($isTeacher?0:$USER->id));
if (!$ret) {
	print_error('not configured');
}
list($courseSubjects, $courseTopics, $niveaus, $selectedSubject, $selectedTopic, $selectedNiveau) = $ret;

// IF TEACHER SHOW ALL COURSE STUDENTS, IF NOT ONLY CURRENT USER
$students = $allCourseStudents = ($isTeacher) ? block_exacomp_get_students_by_course($courseid) : array($USER->id => $USER);
if($course_settings->nostudents) $allCourseStudents = array();

$competence_tree = block_exacomp_get_competence_tree($courseid,$selectedSubject?$selectedSubject->id:null,$selectedTopic?$selectedTopic->id:null,false,$selectedNiveau?$selectedNiveau->id:null,
		($course_settings->show_all_examples != 0 || $isTeacher),$course_settings->filteredtaxonomies, true);

$scheme = block_exacomp_get_grading_scheme($courseid);
$colselector="";
$statistic = false;
if($isTeacher){	//mind nostudents setting
	if($studentid == BLOCK_EXACOMP_SHOW_ALL_STUDENTS && $course_settings->nostudents != 1) {
		$colselector=$output->column_selector(count($allCourseStudents));
	} elseif (!$studentid || $course_settings->nostudents == 1) {
		$students = array();
	} elseif ($studentid == BLOCK_EXACOMP_SHOW_STATISTIC) {
		$statistic = true;
	} else {
		$students = !empty($students[$studentid]) ? array($students[$studentid]) : $students;
	}
}

foreach($students as $student) {
	block_exacomp_get_user_information_by_course($student, $courseid);
}

if (optional_param('print', false, PARAM_BOOL)) {
	$output->print = true;
	$html_tables = [];
	
	if ($group == -1) {
		// all students, do nothing
	} else {
		// get the students on this group
		$students = array_slice($students, $group*\block_exacomp\STUDENTS_PER_COLUMN, \block_exacomp\STUDENTS_PER_COLUMN, true);
	}
	
	// TOOD: print column information for print
	
	// loop through all pages (eg. when all students should be printed)
	for ($group_i = 0; $group_i < count($students); $group_i+=\block_exacomp\STUDENTS_PER_COLUMN) {
		$students_to_print = array_slice($students, $group_i, \block_exacomp\STUDENTS_PER_COLUMN, true);
		
		$html_header = $output->overview_metadata($selectedSubject->title, $selectedTopic, null, $selectedNiveau);

		// $html .= "&nbsp;<br />";
		$html_tables[] = $output->competence_overview($competence_tree, $courseid, $students_to_print, $showevaluation, $isTeacher ? \block_exacomp\ROLE_TEACHER : \block_exacomp\ROLE_STUDENT, $scheme, $selectedNiveau->id != block_exacomp\SHOW_ALL_NIVEAUS, 0, $statistic);
	}

	block_exacomp\printer::competence_overview($selectedSubject, $selectedTopic, $selectedNiveau, null, $html_header, $html_tables);
}

echo $output->header_v2($page_identifier);
echo $colselector;
echo $output->competence_overview_form_start($selectedNiveau, $selectedTopic, $studentid, $editmode);

//dropdowns for subjects and topics and students -> if user is teacher and working with students
echo $output->overview_dropdowns('assign_competencies', $allCourseStudents, (!$editmode) ? $studentid : $selectedStudentid, $isTeacher);

echo '<div class="clearfix"></div>';

if($selectedNiveau->id != block_exacomp\SHOW_ALL_NIVEAUS){
	echo $output->overview_metadata($selectedSubject->title, $selectedTopic, null, $selectedNiveau);
			
	if($isTeacher)
		echo $output->overview_metadata_teacher($selectedTopic,$selectedNiveau);
	else{
		$cm_mm = block_exacomp_get_course_module_association($courseid);
		$course_mods = get_fast_modinfo($courseid)->get_cms();

		$activities_student = array();
		if(isset($cm_mm->topics[$selectedNiveau->id]))
			foreach($cm_mm->topics[$selectedNiveau->id] as $cmid)
				$activities_student[] = $course_mods[$cmid];
		
		// TODO: disabled for now
		// if(block_exacomp_is_altversion())
		//	echo $output->overview_metadata_student($selectedTopic, $selectedNiveau, $students[$USER->id]->topics, $showevaluation, $scheme, block_exacomp_get_icon_for_user($activities_student, $USER, block_exacomp_get_supported_modules()));
	}
}

echo html_writer::start_tag("div", array("id"=>"exabis_competences_block"));
echo html_writer::start_tag("div", array("class"=>"exabis_competencies_lis"));

echo html_writer::start_tag("div", array("class"=>"gridlayout"));

echo '<div class="gridlayout-left">';
echo $output->subjects_menu($courseSubjects, $selectedSubject, $selectedTopic);
echo '</div>';
echo '<div class="gridlayout-right">';
echo $output->niveaus_menu($niveaus,$selectedNiveau,$selectedTopic);

echo '<div class="clearfix"></div>';

if($course_settings->nostudents != 1)
	echo $output->overview_legend($isTeacher);
if($course_settings->nostudents != 1 && $studentid)
	echo $output->student_evaluation($showevaluation, $isTeacher,$selectedNiveau->id,$subjectid, $topicid, $studentid);

echo $output->competence_overview($competence_tree, $courseid, $students, $showevaluation,
		$isTeacher ? \block_exacomp\ROLE_TEACHER : \block_exacomp\ROLE_STUDENT, $scheme,
		($selectedNiveau->id != block_exacomp\SHOW_ALL_NIVEAUS), 0, $statistic);
echo '</div>';

echo html_writer::end_tag("div");
echo html_writer::end_tag("div");
echo html_writer::end_tag("div");

/* END CONTENT REGION */
echo $output->footer();
