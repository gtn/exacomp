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

require_once __DIR__."/inc.php";

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
$PAGE->set_heading(get_string('pluginname', 'block_exacomp'));
$PAGE->set_title(get_string($page_identifier, 'block_exacomp'));

$PAGE->requires->js('/blocks/exacomp/javascript/Chart.js', true);
$PAGE->requires->js('/blocks/exacomp/javascript/d3.min.js', true);

// build breadcrumbs navigation
block_exacomp_build_breadcrum_navigation($courseid);

$output = block_exacomp_get_renderer();
// build tab navigation & print header
echo $output->header($context, $courseid, $page_identifier);

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
		echo block_exacomp_studentselector($coursestudents,$studentid,$PAGE->url);
		echo $OUTPUT->footer();
		die;
	}else{
		//check permission for viewing students profile
		if(!array_key_exists($studentid, $coursestudents))
			print_error("nopermissions","","","Show student profile");
		
		//print student selector
		echo get_string("choosestudent","block_exacomp");
		echo block_exacomp_studentselector($coursestudents,$studentid,$PAGE->url);
	}
}
$student = $DB->get_record('user',array('id' => $studentid));


echo $output->print_profile_print_button();

$possible_courses = block_exacomp_get_exacomp_courses($student);

if(!block_exacomp_check_profile_config($student->id))
	block_exacomp_init_profile($possible_courses, $student->id);

echo $output->print_competence_profile_metadata($student);

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
foreach($possible_courses as $course){
	if(isset($profile_settings->exacomp[$course->id]))
		$user_courses[$course->id] = $course; 
}

//if(!block_exacomp_is_altversion())
//	echo $output->print_competene_profile_overview($student, $user_courses, $possible_courses, $badges, 
//		$profile_settings->useexaport, $items, $exastud_competence_profile_data, $profile_settings->onlygainedbadges);

if(!empty($profile_settings->exacomp) || $profile_settings->showallcomps == 1)
	echo html_writer::tag('h3', get_string('my_comps', 'block_exacomp'), array('class'=>'competence_profile_sectiontitle'));
	
echo $output->print_lm_graph_legend();
foreach($user_courses as $course) {
	//if selected
	if(isset($profile_settings->exacomp[$course->id]))
		echo $output->print_competence_profile_course($course,$student);
}

if(!block_exacomp_is_altversion()){
	if($profile_settings->showallcomps == 1){
		if(empty($user_courses))
			$overview_courses = $possible_courses;
		else 	
			$overview_courses = $user_courses;
			
		echo $output->print_competence_profile_course_all($overview_courses, $student);
	}
}
if($profile_settings->useexaport){
	echo $output->print_competence_profile_exaport($profile_settings, $student, $items);
}

if($profile_settings->useexastud){
	echo $output->print_competence_profile_exastud($profile_settings, $student);
}

/* END CONTENT REGION */
echo $output->footer();
