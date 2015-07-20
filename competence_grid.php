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
block_exacomp_build_breadcrum_navigation($courseid);

$output = $PAGE->get_renderer('block_exacomp');

// build tab navigation & print header
echo $OUTPUT->header();
echo $output->print_wrapperdivstart();
echo $OUTPUT->tabtree(block_exacomp_build_navigation_tabs($context,$courseid), $page_identifier);

// CHECK TEACHER
$isTeacher = (has_capability('block/exacomp:teacher', $context)) ? true : false;
/* CONTENT REGION */

$subjectid = optional_param('subjectid', 0, PARAM_INT);
$studentid = optional_param("studentid", 0, PARAM_INT);

if(!$isTeacher) $studentid = $USER->id;

$dropdown_subjects = block_exacomp_get_subjects_by_course($courseid, true);

if($dropdown_subjects && $subjectid == 0)
	$subjectid = key($dropdown_subjects);
/* SAVE DATA */
if($version	&& $studentid > 0 && isset($_POST['btn_submit']) && $subjectid > 0)
	block_exacomp_save_competencies(isset($_POST['data']) ? $_POST['data'] : array(), $courseid, ($isTeacher) ? ROLE_TEACHER : ROLE_STUDENT, TYPE_TOPIC, null, $subjectid);

list($niveaus, $skills, $subjects, $data, $selection) = block_exacomp_init_competence_grid_data($courseid, $subjectid, $studentid, (block_exacomp_get_settings_by_course($courseid)->show_all_examples != 0 || $isTeacher), block_exacomp_get_settings_by_course($courseid)->filteredtaxonomies);

echo $output->print_subject_dropdown(block_exacomp_get_schooltypetree_by_subjects($dropdown_subjects,true),$subjectid, $studentid);
if($data) {
	if (has_capability('block/exacomp:teacher', $context) && !block_exacomp_get_settings_by_course($courseid)->nostudents) {
		echo ' '.get_string("choosestudent","block_exacomp").' ';
		echo block_exacomp_studentselector(block_exacomp_get_students_by_course($courseid),$studentid,$PAGE->url . ($subjectid > 0 ? "&subjectid=".$subjectid : ""), true);
	}
	echo html_writer::start_div();
	if(!$version && isset($dropdown_subjects[$subjectid]->infolink))
		echo html_writer::tag("p",get_string('infolink','block_exacomp') . html_writer::link($dropdown_subjects[$subjectid]->infolink, $dropdown_subjects[$subjectid]->infolink,array('target'=>'_blank')));
	echo html_writer::tag("a", get_string("textalign","block_exacomp"),array("class" => "switchtextalign"));
	echo html_writer::div($output->print_competence_grid_legend());
	echo html_writer::start_tag("form", array("method"=>"post", "id" => "assign-competencies"));
	echo $output->print_competence_grid($niveaus, $skills, $subjects, $data, $selection, $courseid,$studentid);
	if($version)
		echo html_writer::div(html_writer::empty_tag("input", array("type"=>"submit","name"=>"btn_submit","id"=>"btn_submit","value"=>get_string('save_selection','block_exacomp'))), '', array('id'=>'exabis_save_button'));
	echo html_writer::end_div();
}
else {
	echo html_writer::div(get_string('competencegrid_nodata', 'block_exacomp'));
}
/* END CONTENT REGION */
echo $output->print_wrapperdivend();
echo $OUTPUT->footer();

?>