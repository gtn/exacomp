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

// build breadcrumbs navigation
block_exacomp_build_breadcrum_navigation($courseid);

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
		
	$profile_usebadges = 0;
	if(isset($_POST['usebadges']))
		$profile_usebadges = 1;
		
	$profile_onlygainedbadges = 0;
	if(isset($_POST['profile_settings_onlygainedbadges']))
		$profile_onlygainedbadges = 1;
		
	$profile_showallcomps = 0;
	if(isset($_POST['profile_settings_showallcomps']))
		$profile_showallcomps = 1;
	
	block_exacomp_reset_profile_settings($USER->id);
	
	block_exacomp_set_profile_settings($USER->id, $showonlyreached, $profile_usebadges, $profile_onlygainedbadges, $profile_showallcomps, $useexaport, $useexastud, 
		(isset($_POST['profile_settings_course']))?$_POST['profile_settings_course']:array(),
		(isset($_POST['profile_settings_periods']))?$_POST['profile_settings_periods']:array());
	
}
$output = block_exacomp_get_renderer();
// build tab navigation & print header
echo $output->header($context, $courseid, 'tab_competence_profile');

/* CONTENT REGION */
$studentid = optional_param('studentid', $USER->id, PARAM_INT);
$isTeacher = block_exacomp_is_teacher($context);
if(!$isTeacher) $studentid = $USER->id;
$student = $DB->get_record('user',array('id' => $studentid));

if(!$isTeacher)
	echo $OUTPUT->tabtree(block_exacomp_build_navigation_tabs_profile($context, $courseid), $page_identifier);


$exaport = block_exacomp_exaportexists();
$exastud = block_exacomp_exastudexists();

$user_courses = block_exacomp_get_exacomp_courses($student);

if($exaport)
	$exaport_items = block_exacomp_get_exaport_items();
//if($exastud)
	//$exastud_periods = block_exacomp_get_exastud_periods();

$usebadges = get_config('exacomp', 'usebadges');

$profile_usebadges = false;
if(block_exacomp_moodle_badges_enabled() && $usebadges)
	$profile_usebadges = true;

$profile_settings = block_exacomp_get_profile_settings();

echo $output->print_profile_settings($user_courses, $profile_settings, $profile_usebadges, $exaport, $exastud, (isset($exastud_periods))?$exastud_periods:array());

/* END CONTENT REGION */
echo $output->footer();

?>