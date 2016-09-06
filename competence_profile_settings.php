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
$PAGE->set_heading(get_string('blocktitle', 'block_exacomp'));
$PAGE->set_title(get_string($page_identifier, 'block_exacomp'));

// build breadcrumbs navigation
block_exacomp_build_breadcrum_navigation($courseid);

//SAVE DATA
if (($action = optional_param("action", "", PARAM_TEXT) ) == "save") {
	block_exacomp_set_profile_settings($USER->id, \block_exacomp\param::optional_array('profile_settings_course', PARAM_INT));
	
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

$user_courses = block_exacomp_get_exacomp_courses($student);

$profile_settings = block_exacomp_get_profile_settings();

echo $output->profile_settings($user_courses, $profile_settings);

/* END CONTENT REGION */
echo $output->footer();
