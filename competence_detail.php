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

global $DB, $OUTPUT, $PAGE, $version;

$courseid = required_param('courseid', PARAM_INT);
$showevaluation = optional_param("showevaluation", false, PARAM_BOOL);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_simplehtml', $courseid);
}

require_login($course);

$context = context_course::instance($courseid);

/* PAGE IDENTIFIER - MUST BE CHANGED. Please use string identifier from lang file */
$page_identifier = 'tab_competence_details';

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/competence_detail.php', array('courseid' => $courseid, 'showevaluation'=>$showevaluation));
$PAGE->set_heading(get_string('pluginname', 'block_exacomp'));
$PAGE->set_title(get_string($page_identifier, 'block_exacomp'));

block_exacomp_init_js_css();

// build breadcrumbs navigation
$coursenode = $PAGE->navigation->find($courseid, navigation_node::TYPE_COURSE);
$blocknode = $coursenode->add(get_string('pluginname','block_exacomp'));
$pagenode = $blocknode->add(get_string($page_identifier,'block_exacomp'), $PAGE->url);
$pagenode->make_active();

// build tab navigation & print header
echo $OUTPUT->header();
echo $OUTPUT->tabtree(block_exacomp_build_navigation_tabs($context,$courseid), $page_identifier);

// CHECK TEACHER
$isTeacher = (has_capability('block/exacomp:teacher', $context)) ? true : false;

if (($action = optional_param("action", "", PARAM_TEXT) ) == "save") {
	// DESCRIPTOR DATA
	block_exacomp_save_competencies_activities_detail(isset($_POST['data']) ? $_POST['data'] : array(), $courseid, ($isTeacher) ? ROLE_TEACHER : ROLE_STUDENT, TYPE_DESCRIPTOR);
	// TOPIC DATA
	block_exacomp_save_competencies_activities_detail(isset($_POST['datatopics']) ? $_POST['datatopics'] : array(), $courseid, ($isTeacher) ? ROLE_TEACHER : ROLE_STUDENT, TYPE_TOPIC);	
}

/* CONTENT REGION */
$output = $PAGE->get_renderer('block_exacomp');
$activities = block_exacomp_get_activities_by_course($courseid);
if(!$activities)
	echo $output->print_no_activities_warning();
else{
	echo $output->print_detail_legend($showevaluation, $isTeacher);
	
	$tree = block_exacomp_build_activity_tree($courseid);
	$students = ($isTeacher) ? block_exacomp_get_students_by_course($courseid) : array($USER);
	foreach($students as $student){
		block_exacomp_get_user_information_by_course($student, $courseid);
	}
	
	echo $output->print_detail_content($tree, $courseid, $students, $showevaluation, (has_capability('block/exacomp:teacher', $context)) ? ROLE_TEACHER : ROLE_STUDENT, block_exacomp_get_grading_scheme($courseid));
}


/* END CONTENT REGION */

echo $OUTPUT->footer();

?>