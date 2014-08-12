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

global $DB, $OUTPUT, $PAGE;

$courseid = required_param('courseid', PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_simplehtml', $courseid);
}

require_login($course);

$context = context_course::instance($courseid);

/* PAGE IDENTIFIER - MUST BE CHANGED. Please use string identifier from lang file */
$page_identifier = 'tab_competence_profile_settings';

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/competence_profile_settings.php', array('courseid' => $courseid));
$PAGE->set_heading(get_string('pluginname', 'block_exacomp'));
$PAGE->set_title(get_string($page_identifier, 'block_exacomp'));

block_exacomp_init_js_css();

// build breadcrumbs navigation
$coursenode = $PAGE->navigation->find($courseid, navigation_node::TYPE_COURSE);
$blocknode = $coursenode->add(get_string('pluginname','block_exacomp'));
$pagenode = $blocknode->add(get_string($page_identifier,'block_exacomp'), $PAGE->url);
$pagenode->make_active();

//SAVE DATA
if (($action = optional_param("action", "", PARAM_TEXT) ) == "save") {
	$showonlyreached = 0;
	if(isset($_POST['showonlyreached']))
		$showonlyreached = 1;
		
	$useexaport = 0;
	if(isset($_POST['useexaport']))
		$useexaport = 1;
		
	$useexastud = 0;
	if(isset($_POST['useexastud']))
		$useexastud = 1;
	
	block_exacomp_reset_profile_settings($USER->id);
	
	block_exacomp_set_profile_settings($USER->id, $showonlyreached, $useexaport, $useexastud, 
		(isset($_POST['profile_settings_course']))?$_POST['profile_settings_course']:array(),
		(isset($_POST['profile_settings_periods']))?$_POST['profile_settings_periods']:array());
	
}

// build tab navigation & print header
echo $OUTPUT->header();
echo $OUTPUT->tabtree(block_exacomp_build_navigation_tabs($context,$courseid), $page_identifier);

/* CONTENT REGION */
$studentid = optional_param('studentid', $USER->id, PARAM_INT);
$isTeacher = (has_capability('block/exacomp:teacher', $context)) ? true : false;
if(!$isTeacher) $studentid = $USER->id;
$student = $DB->get_record('user',array('id' => $studentid));
$output = $PAGE->get_renderer('block_exacomp');

$exaport = block_exacomp_exaportexists();
$exastud = block_exacomp_exastudexists();

$user_courses = block_exacomp_get_exacomp_courses($student);
//if(!block_exacomp_check_profile_config($student->id))
	//block_exacomp_init_profile($user_courses, $student->id);

if($exaport)
	$exaport_items = block_exacomp_get_exaport_items();
if($exastud)
	$exastud_periods = block_exacomp_get_exastud_periods();
		
$profile_settings = block_exacomp_get_profile_settings();

echo $output->print_profile_settings($user_courses, $profile_settings, $exaport, $exastud, (isset($exastud_periods))?$exastud_periods:array());

/* END CONTENT REGION */

echo $OUTPUT->footer();

?>