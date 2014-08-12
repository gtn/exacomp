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

global $DB, $OUTPUT, $PAGE, $USER;

$courseid = required_param('courseid', PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_simplehtml', $courseid);
}

require_login($course);

$context = context_course::instance($courseid);

/* PAGE IDENTIFIER - MUST BE CHANGED. Please use string identifier from lang file */
$page_identifier = 'tab_competence_profile_profile';

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/competence_profile.php', array('courseid' => $courseid));
$PAGE->set_heading(get_string('pluginname', 'block_exacomp'));
$PAGE->set_title(get_string($page_identifier, 'block_exacomp'));

block_exacomp_init_js_css();
$PAGE->requires->js('/blocks/exacomp/javascript/Chart.min.js', true);

// build breadcrumbs navigation
$coursenode = $PAGE->navigation->find($courseid, navigation_node::TYPE_COURSE);
$blocknode = $coursenode->add(get_string('pluginname','block_exacomp'));
$pagenode = $blocknode->add(get_string($page_identifier,'block_exacomp'), $PAGE->url);
$pagenode->make_active();

// build tab navigation & print header
echo $OUTPUT->header();
echo $OUTPUT->tabtree(block_exacomp_build_navigation_tabs($context,$courseid), $page_identifier);

/* CONTENT REGION */
$studentid = optional_param('studentid', $USER->id, PARAM_INT);
$isTeacher = (has_capability('block/exacomp:teacher', $context)) ? true : false;
if(!$isTeacher) $studentid = $USER->id;
$student = $DB->get_record('user',array('id' => $studentid));
$output = $PAGE->get_renderer('block_exacomp');

$user_courses = block_exacomp_get_exacomp_courses($student);

if(!block_exacomp_check_profile_config($student->id))
	block_exacomp_init_profile($user_courses, $student->id);

echo $output->print_competence_profile_metadata($student);

echo $output->print_competene_profile_overview($student, $user_courses);

foreach($user_courses as $course) {
	//if selected
	$profile_settings = block_exacomp_get_profile_settings();
	if(isset($profile_settings->exacomp[$course->id]))
		echo $output->print_competence_profile_course($course,$student);
}

$profile_settings = block_exacomp_get_profile_settings();

if($profile_settings->useexaport == 1){
	$items = block_exacomp_get_exaport_items();
	$items = block_exacomp_init_exaport_items($items);
	echo $output->print_competence_profile_exaport($profile_settings, $student, $items);
}
if($profile_settings->useexastud == 1){
	$periods = block_exacomp_get_exastud_periods();
	$reviews = block_exacomp_get_exastud_reviews($periods, $student);
	
	echo $output->print_competence_profile_exastud($profile_settings, $student, $periods, $reviews);
}
/* END CONTENT REGION */

echo $OUTPUT->footer();

?>