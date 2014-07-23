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
$page_identifier = 'tab_competence_overview';

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/assign_competencies.php', array('courseid' => $courseid));
$PAGE->set_heading(get_string('pluginname', 'block_exacomp'));

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
// IF DELETE > 0 DELTE CUSTOM EXAMPLE
if(($delete = optional_param("delete", 0, PARAM_INT)) > 0 && $isTeacher)
	block_exacomp_delete_custom_example($delete);

// SAVA DATA
if (($action = optional_param("action", "", PARAM_TEXT) )== "save") {
	// DESCRIPTOR DATA
	block_exacomp_save_competencies(isset($_POST['data']) ? $_POST['data'] : array(), $courseid, ($isTeacher) ? ROLE_TEACHER : ROLE_STUDENT, TYPE_DESCRIPTOR);
	// TOPIC DATA
	block_exacomp_save_competencies(isset($_POST['datatopics']) ? $_POST['datatopics'] : array(), $courseid, ($isTeacher) ? ROLE_TEACHER : ROLE_STUDENT, TYPE_TOPIC);
	// EXAMPLE DATA
	block_exacomp_save_example_evaluation(isset($_POST['dataexamples']) ? $_POST['dataexamples'] : array(), $courseid, ($isTeacher) ? ROLE_TEACHER : ROLE_STUDENT);
}

// IF TEACHER SHOW ALL COURSE STUDENTS, IF NOT ONLY CURRENT USER
$students = ($isTeacher) ? block_exacomp_get_students_by_course($courseid) : array($USER);
foreach($students as $student)
	block_exacomp_get_user_information_by_course($student, $courseid);

$subjects = block_exacomp_get_competence_tree($courseid);
$output = $PAGE->get_renderer('block_exacomp');
// PRINT LEGEND
$showevaluation = optional_param("showevaluation", false, PARAM_BOOL);
echo $output->print_student_evaluation($showevaluation);
echo $output->print_overview_legend($isTeacher);
echo $output->print_column_selector(count($students));
echo $output->print_competence_overview($subjects, $courseid, $students, $showevaluation, (has_capability('block/exacomp:teacher', $context)) ? ROLE_TEACHER : ROLE_STUDENT, block_exacomp_get_grading_scheme($courseid));
/* END CONTENT REGION */

echo $OUTPUT->footer();

?>