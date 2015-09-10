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
$creatorid = required_param('creatorid', PARAM_INT);
$action = optional_param('action', '', PARAM_TEXT);
$crosssubj = optional_param('crosssubj', 0, PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_exacomp', $courseid);
}

require_login($course);
$context = context_course::instance($courseid);
block_exacomp_require_teacher($context);

$PAGE->set_url('/blocks/exacomp/pre_planning_storage.php', array('courseid' => $courseid, 'creatorid'=>$creatorid));
$PAGE->set_heading(get_string('pluginname', 'block_exacomp'));

block_exacomp_init_js_css();

block_exacomp_init_js_weekly_schedule();
echo $OUTPUT->header();

if($crosssubj == 0)
	$students = block_exacomp_get_students_by_course($courseid);
else
	$students = block_exacomp_get_students_for_crosssubject($courseid, $DB->get_record(block_exacomp::DB_CROSSSUBJECTS, array('id'=>$crosssubj)));
	
if(!$students) {
	if($crosssubj == 0)
		echo get_string('nostudents','block_exacomp');
	else
		echo get_string('no_students_crosssub', 'block_exacomp');
		
	echo $OUTPUT->footer();
	exit;
}

if(strcmp($action, 'empty')==0){
	block_exacomp_empty_pre_planning_storage($creatorid, $courseid);
}

$schedules = block_exacomp_get_pre_planning_storage($creatorid, $courseid);
if(!$schedules) {
	echo get_string('noschedules_pre_planning_storage','block_exacomp');
	echo $OUTPUT->footer();
	exit;
}

$students = block_exacomp_get_student_pool_examples($students, $courseid);

$examples = array();
foreach($schedules as $schedule){
	if(!in_array($schedule->exampleid, $examples))
		$examples[] = $schedule->exampleid;
}

/* CONTENT REGION */
$output = $PAGE->get_renderer('block_exacomp');
echo $output->print_wrapperdivstart();
echo html_writer::start_tag('form', array('action'=>$PAGE->url->out(false).'&action=empty', 'method'=>'post'));

echo $output->print_pre_planning_storage_pool();
echo $output->print_pre_planning_storage_students($students, $examples);
echo $output->print_example_trash(array(), false);

echo html_writer::div(html_writer::empty_tag('input', array('type'=>'button', 'id'=>'save_pre_planning_storage', 
	'value'=>get_string('save_pre_planning_selection', 'block_exacomp'))).
	html_writer::empty_tag('input', array('type'=>'submit', 'id'=>'empty_pre_planning_storage', 
	'value'=>get_string('empty_pre_planning_storage', 'block_exacomp'))),'', array('id'=>'save_button'));

echo html_writer::end_tag('form');
echo "</div>";

echo $OUTPUT->footer();
