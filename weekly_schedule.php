<?php
// This file is part of Exabis Competencies
//
// (c) 2016 GTN - Global Training Network GmbH <office@gtn-solutions.com>
//
// Exabis Competencies is free software: you can redistribute it and/or modify
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

// CHECK TEACHER
$isTeacher = block_exacomp_is_teacher($context);

$studentid = block_exacomp_get_studentid($isTeacher) ;
if($studentid == BLOCK_EXACOMP_SHOW_ALL_STUDENTS)
	$studentid = 0;

$selectedCourse = optional_param('pool_course', $courseid, PARAM_INT);

/* PAGE IDENTIFIER - MUST BE CHANGED. Please use string identifier from lang file */
$page_identifier = 'tab_weekly_schedule';

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/weekly_schedule.php', ['courseid' => $courseid, 'studentid'=>$studentid, 'pool_course'=>$selectedCourse]);
$PAGE->set_heading(get_string('pluginname', 'block_exacomp'));
$PAGE->set_title(get_string($page_identifier, 'block_exacomp'));

block_exacomp_init_js_weekly_schedule();

// build breadcrumbs navigation
block_exacomp_build_breadcrum_navigation($courseid);

/* CONTENT REGION */
if($isTeacher){
	$coursestudents = block_exacomp_get_students_by_course($courseid);
	if($studentid <= 0) {
		$student = null;
	}else{
		//check permission for viewing students profile
		if(!array_key_exists($studentid, $coursestudents))
			print_error("nopermissions","","","Show student profile");
		
		$student = $DB->get_record('user',array('id' => $studentid));
	}
} else {
	$student = $USER;
}

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
	if(!$student) {
		echo html_writer::tag("p", get_string("select_student_weekly_schedule","block_exacomp"));
		
		//print student selector
		echo get_string("choosestudent","block_exacomp");
		echo $output->studentselector($coursestudents, 0);
		echo $OUTPUT->footer();
		die;
	}else{
		//print student selector
		echo get_string("choosestudent","block_exacomp");
		echo $output->studentselector($coursestudents, $student->id);
	}
} else {
	echo html_writer::tag("input", null, array("type" => "hidden", "value" => $student->id, "id" => "menuexacomp_competence_grid_select_student"));
}

echo $output->button_box('weekly_schedule_print();', '');
echo $output->course_dropdown($selectedCourse);

echo $OUTPUT->box(get_string('weekly_schedule_link_to_grid','block_exacomp'));

echo $output->side_wrap_weekly_schedule();

/* END CONTENT REGION */

echo $output->footer();
