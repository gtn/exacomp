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
$subjectid = optional_param('subjectid', 0, PARAM_INT);
$studentid = optional_param("studentid", 0, PARAM_INT);

if(!$isTeacher) $studentid = $USER->id;

$subjects = ($version) ? block_exacomp_get_schooltypes_by_course($courseid) : block_exacomp_get_subjects_by_course($courseid);
if($subjects && $subjectid == 0)
	$subjectid = key($subjects);

echo get_string("choosesubject","block_exacomp");
echo html_writer::select($subjects, 'exacomp_competence_grid_select_subject',array($subjectid),null,
		array("onchange"=>"document.location.href='".$CFG->wwwroot.$url."&subjectid='+this.value+'&studentid=".$studentid."';"));
if (has_capability('block/exacomp:teacher', $context)) {
	echo get_string("choosestudent","block_exacomp");
	echo block_exacomp_studentselector(get_role_users(5, $context),$studentid,$url);
}
/* END CONTENT REGION */

echo $OUTPUT->footer();

?>