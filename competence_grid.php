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

global $DB, $OUTPUT, $PAGE, $USER, $version;

$courseid = required_param('courseid', PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_simplehtml', $courseid);
}

require_login($course);

$context = context_course::instance($courseid);

/* PAGE IDENTIFIER - MUST BE CHANGED. Please use string identifier from lang file */
$page_identifier = 'tab_competence_grid';

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/competence_grid.php', array('courseid' => $courseid));
$PAGE->set_heading(get_string('pluginname', 'block_exacomp'));
$PAGE->set_title(get_string($page_identifier,'block_exacomp'));
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
/* CONTENT REGION */
$output = $PAGE->get_renderer('block_exacomp');
$subjectid = optional_param('subjectid', 0, PARAM_INT);
$studentid = optional_param("studentid", 0, PARAM_INT);

if(!$isTeacher) $studentid = $USER->id;

$dropdown_subjects = ($version) ? block_exacomp_get_schooltypes_by_course($courseid) : block_exacomp_get_subjects_by_course($courseid, true);
if($dropdown_subjects && $subjectid == 0)
	$subjectid = key($dropdown_subjects);
/* SAVE DATA */
if($version	&& $studentid > 0 && isset($_POST['btn_submit']) && $subjectid > 0)
	block_exacomp_save_competencies(isset($_POST['data']) ? $_POST['data'] : array(), $courseid, ($isTeacher) ? ROLE_TEACHER : ROLE_STUDENT, TYPE_TOPIC);

list($niveaus, $skills, $subjects, $data, $selection) = block_exacomp_init_competence_grid_data($courseid, $subjectid, $studentid, (block_exacomp_get_settings_by_course($courseid)->show_all_examples != 0 || $isTeacher));

echo $output->print_subject_dropdown($dropdown_subjects,$subjectid, $studentid);
if($data) {
	if (has_capability('block/exacomp:teacher', $context)) {
		echo ' '.get_string("choosestudent","block_exacomp").' ';
		echo block_exacomp_studentselector(block_exacomp_get_students_by_course($courseid),$studentid,$PAGE->url . ($subjectid > 0 ? "&subjectid=".$subjectid : ""));
	}
	echo html_writer::start_div();
	echo html_writer::tag("a", get_string("textalign","block_exacomp"),array("class" => "switchtextalign"));
	echo html_writer::div($output->print_competence_grid_legend());
	echo html_writer::start_tag("form", array("method"=>"post"));
	echo $output->print_competence_grid($niveaus, $skills, $subjects, $data, $selection, $courseid,$studentid);
	echo html_writer::div(html_writer::empty_tag("input", array("type"=>"submit","name"=>"btn_submit","value"=>get_string('save_selection','block_exacomp'))), '', array('id'=>'exabis_save_button'));
	echo html_writer::end_div();
}
else {
	echo html_writer::div(get_string('competencegrid_nodata', 'block_exacomp'));
}
/* END CONTENT REGION */

echo $OUTPUT->footer();

?>