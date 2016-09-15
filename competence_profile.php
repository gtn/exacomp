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

global $DB, $OUTPUT, $PAGE, $USER;

$courseid = required_param('courseid', PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_simplehtml', $courseid);
}

require_login($course);

$context = context_course::instance($courseid);

// CHECK TEACHER
$isTeacher = block_exacomp_is_teacher($context);

$studentid = block_exacomp_get_studentid() ;
/* PAGE IDENTIFIER - MUST BE CHANGED. Please use string identifier from lang file */
$page_identifier = 'tab_competence_profile_profile';

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/competence_profile.php', array('courseid' => $courseid));
$PAGE->set_heading(get_string('blocktitle', 'block_exacomp'));
$PAGE->set_title(get_string($page_identifier, 'block_exacomp'));

$PAGE->requires->js('/blocks/exacomp/javascript/Chart.js', true);
$PAGE->requires->js('/blocks/exacomp/javascript/d3.min.js', true);
$PAGE->requires->js('/blocks/exacomp/javascript/fullcalendar/moment.min.js', true);
$PAGE->requires->js('/blocks/exacomp/javascript/jquery.daterangepicker.min.js', true);
$PAGE->requires->css('/blocks/exacomp/css/daterangepicker.min.css', true);

// build breadcrumbs navigation
block_exacomp_build_breadcrum_navigation($courseid);

$output = block_exacomp_get_renderer();
echo $output->header_v2($page_identifier);

/* CONTENT REGION */

if(!$isTeacher){
	$studentid = $USER->id;
	echo $OUTPUT->tabtree(block_exacomp_build_navigation_tabs_profile($context, $courseid), $page_identifier);
}else {
	
	$coursestudents = block_exacomp_get_students_by_course($courseid);
	
	if($studentid == 0 || $studentid == BLOCK_EXACOMP_SHOW_ALL_STUDENTS) {
		echo html_writer::tag("p", get_string("select_student","block_exacomp"));
		//print student selector
		echo get_string("choosestudent","block_exacomp");
		echo $output->studentselector($coursestudents,$studentid);
		echo $output->footer();
		die;
	}else{
		//check permission for viewing students profile
		if(!array_key_exists($studentid, $coursestudents))
			print_error("nopermissions","","","Show student profile");
		
		//print student selector
		echo get_string("choosestudent","block_exacomp");
		echo $output->studentselector($coursestudents,$studentid);
		
		//print date range picker
		echo get_string("choosedaterange","block_exacomp");
		echo $output->daterangepicker();
	}
}
$student = $DB->get_record('user',array('id' => $studentid));

echo $output->button_box(true, '');

$possible_courses = block_exacomp_get_exacomp_courses($student);

block_exacomp_init_profile($possible_courses, $student->id);

echo $output->competence_profile_metadata($student);

//echo html_writer::start_div('competence_profile_overview clearfix');

$usebadges = get_config('exacomp', 'usebadges');

$profile_settings = block_exacomp_get_profile_settings($studentid);


$items = array();

$user_courses = array();
$max_scheme = 3;
foreach($possible_courses as $course){
	if(isset($profile_settings->exacomp[$course->id])){
		$user_courses[$course->id] = $course; 
		if(($grading = block_exacomp_get_grading_scheme($course->id)) > $max_scheme)
			$max_scheme = $grading;
	}
}

if(!empty($profile_settings->exacomp) || $profile_settings->showallcomps == 1)
	echo html_writer::tag('h3', get_string('my_comps', 'block_exacomp'), array('class'=>'competence_profile_sectiontitle'));

foreach($user_courses as $course) {
	//if selected
	if(isset($profile_settings->exacomp[$course->id]))
		echo $output->competence_profile_course($course,$student,true,$max_scheme);
}

/* END CONTENT REGION */
echo $output->footer();

