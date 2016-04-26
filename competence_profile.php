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

global $DB, $OUTPUT, $PAGE, $USER;

$courseid = required_param('courseid', PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_simplehtml', $courseid);
}

require_login($course);

$context = context_course::instance($courseid);

// CHECK TEACHER
$isTeacher = block_exacomp_is_teacher($context);

$studentid = block_exacomp_get_studentid($isTeacher) ;
/* PAGE IDENTIFIER - MUST BE CHANGED. Please use string identifier from lang file */
$page_identifier = 'tab_competence_profile_profile';

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/competence_profile.php', array('courseid' => $courseid));
$PAGE->set_heading(get_string('blocktitle', 'block_exacomp'));
$PAGE->set_title(get_string($page_identifier, 'block_exacomp'));

$PAGE->requires->js('/blocks/exacomp/javascript/Chart.js', true);
$PAGE->requires->js('/blocks/exacomp/javascript/d3.min.js', true);

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
	
	if($studentid == 0 || $studentid == BLOCK_EXACOMP_SHOW_ALL_STUDENTS || $studentid == BLOCK_EXACOMP_SHOW_STATISTIC) {
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
	}
}
$student = $DB->get_record('user',array('id' => $studentid));

echo $output->button_box(true, '');

$possible_courses = block_exacomp_get_exacomp_courses($student);

if(!block_exacomp_check_profile_config($student->id))
	block_exacomp_init_profile($possible_courses, $student->id);

echo $output->competence_profile_metadata($student);

//echo html_writer::start_div('competence_profile_overview clearfix');

$usebadges = get_config('exacomp', 'usebadges');

$profile_settings = block_exacomp_get_profile_settings($studentid);

if (block_exacomp_moodle_badges_enabled() && $usebadges && $profile_settings->usebadges){
	block_exacomp_award_badges($courseid, $studentid);
	$badges = block_exacomp_get_user_badges($courseid, $studentid);
}else{
	$badges = array();
}

$items = array();
if($profile_settings->useexaport == 1){
	$items = block_exacomp_get_exaport_items($studentid);
	$items = block_exacomp_init_exaport_items($items);
}

$user_courses = array();
$max_scheme = 3;
foreach($possible_courses as $course){
	if(isset($profile_settings->exacomp[$course->id])){
		$user_courses[$course->id] = $course; 
		if(($grading = block_exacomp_get_grading_scheme($course->id)) > $max_scheme)
			$max_scheme = $grading;
	}
}

//if(!block_exacomp_is_altversion())
//	echo $output->competene_profile_overview($student, $user_courses, $possible_courses, $badges,
//		$profile_settings->useexaport, $items, $exastud_competence_profile_data, $profile_settings->onlygainedbadges);

if(!empty($profile_settings->exacomp) || $profile_settings->showallcomps == 1)
	echo html_writer::tag('h3', get_string('my_comps', 'block_exacomp'), array('class'=>'competence_profile_sectiontitle'));

foreach($user_courses as $course) {
	//if selected
	if(isset($profile_settings->exacomp[$course->id]))
		echo $output->competence_profile_course($course,$student,true,$max_scheme);
}
if ($profile_settings->showallcomps == 1) {
	if (empty ( $user_courses ))
		$overview_courses = $possible_courses;
	else
		$overview_courses = $user_courses;
	
	echo $output->competence_profile_course_all ( $overview_courses, $student );
}
if($profile_settings->useexaport){
	echo $output->competence_profile_exaport($profile_settings, $student, $items);
}

if($profile_settings->useexastud){
	echo $output->competence_profile_exastud($profile_settings, $student);
}

/* END CONTENT REGION */
echo $output->footer();
