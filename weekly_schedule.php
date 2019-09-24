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
$groupid = optional_param('groupid', 0, PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_simplehtml', $courseid);
}

require_login($course);

$context = context_course::instance($courseid);

// CHECK TEACHER
$isTeacher = block_exacomp_is_teacher($context);

$studentid = block_exacomp_get_studentid();
if($studentid == BLOCK_EXACOMP_SHOW_ALL_STUDENTS)
	$studentid = 0;

$selectedCourse = optional_param('pool_course', $courseid, PARAM_INT);

/* PAGE IDENTIFIER - MUST BE CHANGED. Please use string identifier from lang file */
$page_identifier = 'tab_weekly_schedule';

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/weekly_schedule.php', ['courseid' => $courseid, 'studentid'=>$studentid, 'pool_course'=>$selectedCourse]);
$PAGE->set_heading(block_exacomp_get_string('blocktitle'));
$PAGE->set_title(block_exacomp_get_string($page_identifier));

block_exacomp_init_js_weekly_schedule();

// build breadcrumbs navigation
block_exacomp_build_breadcrum_navigation($courseid);

/* CONTENT REGION */
if($isTeacher){
	$coursestudents = block_exacomp_get_students_by_course($courseid);
	if($studentid <= 0) {
		$student = null;
		if($studentid <= -1){
		    //MAYBE CHANGE WORDING   studentId is actually student or localgroup id.... if it is a localgroup, the value is negative
		    // use this negative value for the studentselector. the id in the selector is not the groupid because then it would overlap with the studentid
		    // so groupId = -groupidForSelector - 1
		    $groupidForSelector = $studentid;
		}
	}else{
		//check permission for viewing students profile
		if(!array_key_exists($studentid, $coursestudents))
			print_error("nopermissions","","","Show student profile");
		$student = $DB->get_record('user',array('id' => $studentid));
	}
} else {
	$student = $USER;
}

//Add the local groups
$groups = ($isTeacher) ? groups_get_all_groups($courseid) : array();


// print?
if ($student && optional_param('print', false, PARAM_BOOL)) {
	block_exacomp\printer::weekly_schedule($course, $student, optional_param('interval', 'week', PARAM_TEXT));
	exit;
}
// build tab navigation & print header
$output = block_exacomp_get_renderer();
echo $output->header($context, $courseid, $page_identifier);

/* CONTENT REGION */
if($isTeacher){
	echo block_exacomp_get_string("choosestudent");
	echo $output->studentselector($coursestudents, ($student) ? $student->id : @$groupidForSelector, 2, $groups);
} else {
// 	echo html_writer::tag("input", null, array("type" => "hidden", "value" => $student->id, "id" => "menuexacomp_competence_grid_select_student"));
}

echo $output->button_box('weekly_schedule_print();', '');
echo $output->course_dropdown($selectedCourse);

echo $OUTPUT->box(block_exacomp_get_string('weekly_schedule_link_to_grid'));

if($studentid == 0) {
	echo html_writer::div(block_exacomp_get_string('add_example_for_all_students_to_schedule') .
				html_writer::tag("input", "", array("id"=>"add-examples-to-schedule-for-all", "name" => "add-examples-to-schedule-for-all", "type" => "submit", "value" => block_exacomp_get_string("save_selection")))
			,"alert alert-warning");
}
if($studentid < -1) { //if studentid is smaller than -1 it is a locagroupid    change wording todo RW
    //((-1)*dropdownvalue)-1   the -1 is used for ALL_STUDENTS, this is why i calculate it like this    RW
    $groupid = (-1)*$studentid - 1;
    echo html_writer::div(block_exacomp_get_string('add_example_for_group_to_schedule') .
        html_writer::tag("input", "", array("id"=>"add-examples-to-schedule-for-group", "name" => "add-examples-to-schedule-for-group", "groupid" => $groupid, "type" => "submit", "value" => block_exacomp_get_string("save_selection")))
        ,"alert alert-warning");
}
echo $output->side_wrap_weekly_schedule();

/* END CONTENT REGION */

echo $output->footer();
