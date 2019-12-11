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

global $DB, $OUTPUT, $PAGE, $USER;

$courseid = required_param('courseid', PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_simplehtml', $courseid);
}

require_login($course);

$context = context_course::instance($courseid);

// CHECK TEACHER
$isTeacher = block_exacomp_is_teacher($context);

$studentid = block_exacomp_get_studentid() ;

if ($studentid < 0) {
    $studentid = 0;
}

/* PAGE IDENTIFIER - MUST BE CHANGED. Please use string identifier from lang file */
$page_identifier = 'tab_competence_profile_profile';

$output = block_exacomp_get_renderer();

if (optional_param('print', false, PARAM_BOOL)) {
    $output->print = true;
    if (!$isTeacher) {
        $studentid = $USER->id;

        $html_tables[] = $OUTPUT->tabtree(block_exacomp_build_navigation_tabs_profile($context, $courseid), $page_identifier);
    } else {
        $html_content = '';
        $html_header = '';
        $student = $DB->get_record('user',array('id' => $studentid));

        $possible_courses = block_exacomp_get_exacomp_courses($student);
        block_exacomp_init_profile($possible_courses, $student->id);
        $html_content .= $output->competence_profile_metadata($student);
        //$html_header .= $output->competence_profile_metadata($student); // TODO: ??
        $usebadges = get_config('exacomp', 'usebadges');
//         $profile_settings = block_exacomp_get_profile_settings($studentid);
        $items = array();
        $user_courses = array();
        foreach($possible_courses as $course){
                $user_courses[$course->id] = $course;
        }
        if(!empty($user_courses))
            $html_content .= html_writer::tag('h3', block_exacomp_get_string('my_comps'), array('class'=>'competence_profile_sectiontitle'));
        foreach($user_courses as $course) {
                $html_content .= $output->competence_profile_course($course, $student, true, block_exacomp_get_grading_scheme($course->id));
        }
        $html_tables[] = $html_content;

    }

    block_exacomp\printer::competenceprofile_overview($studentid, $html_header, $html_tables);
}


/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/competence_profile.php', array('courseid' => $courseid));
$PAGE->set_heading(block_exacomp_get_string('blocktitle'));
$PAGE->set_title(block_exacomp_get_string($page_identifier));

$PAGE->requires->js('/blocks/exacomp/javascript/Chart.js', true);
$PAGE->requires->js('/blocks/exacomp/javascript/d3.min.js', true);
$PAGE->requires->js('/blocks/exacomp/javascript/fullcalendar/moment.min.js', true);
$PAGE->requires->js('/blocks/exacomp/javascript/jquery.daterangepicker.min.js', true);
$PAGE->requires->css('/blocks/exacomp/css/daterangepicker.min.css', true);

// build breadcrumbs navigation
block_exacomp_build_breadcrum_navigation($courseid);

echo $output->header_v2($page_identifier);

/* CONTENT REGION */

if (!$isTeacher) {
	$studentid = $USER->id;
	echo $OUTPUT->tabtree(block_exacomp_build_navigation_tabs_profile($context, $courseid), $page_identifier);
} else {

	$coursestudents = block_exacomp_get_students_by_course($courseid);

	echo '<div style="padding-bottom: 15px;">';
	if ($studentid == 0 || $studentid == BLOCK_EXACOMP_SHOW_ALL_STUDENTS) {
		echo html_writer::tag("p", block_exacomp_get_string("select_student"));
		//print student selector
		echo block_exacomp_get_string("choosestudent");
		echo $output->studentselector($coursestudents,$studentid);
		echo $output->footer();

	} else {
		//check permission for viewing students profile
		if (!array_key_exists($studentid, $coursestudents))
			print_error("nopermissions","","","Show student profile");

		//print student selector
		echo block_exacomp_get_string("choosestudent");
		echo $output->studentselector($coursestudents,$studentid);

		//print date range picker
		echo block_exacomp_get_string("choosedaterange");
		if ($periods = block_exacomp_get_exastud_periods_current_and_past_periods()) {
			$options = [];
			foreach ($periods as $period) {
				$options[$period->starttime.'-'.$period->endtime] = $period->description;
			}
			echo html_writer::select($options, 'daterangeperiods', '', block_exacomp_get_string('periodselect'), []).' ';
		}
		echo $output->daterangepicker();
	}

	echo '</div>';
}
$student = $DB->get_record('user',array('id' => $studentid));

$printUrl = $PAGE->url->out(false, array('studentid' => $studentid, 'print' => 1));
echo html_writer::div(html_writer::link($printUrl,
        html_writer::empty_tag('img', array('src' => new moodle_url('/blocks/exacomp/pix/view_print.png'), 'alt' => 'print')),
        [
                //'target' => '_blank',
                'title' => block_exacomp_get_string('print'),
                'class' => 'print',
                //'onclick' => 'window.open(location.href+\'&print=1\'); return false;',
        ]), 'button-box');

$possible_courses = block_exacomp_get_exacomp_courses($student);

block_exacomp_init_profile($possible_courses, $student->id);

echo $output->competence_profile_metadata($student);

//echo html_writer::start_div('competence_profile_overview clearfix');

$usebadges = get_config('exacomp', 'usebadges');

// $profile_settings = block_exacomp_get_profile_settings($studentid);


$items = array();

$user_courses = array();

foreach($possible_courses as $course){
		$user_courses[$course->id] = $course;
}


    echo html_writer::tag('h3', block_exacomp_get_string('my_comps'), array('class' => 'competence_profile_sectiontitle'));


//Add crosssubjects
$crosssubjects = array();
foreach($user_courses as $course){
    $crosssubjects[] = block_exacomp_get_cross_subjects_by_course($course->id);
}

foreach($crosssubjects as $crosssubjectsOfCourse) {
    foreach($crosssubjectsOfCourse as $crosssubj){
        //if selected


        if(block_exacomp_student_crosssubj($crosssubj->id,$studentid)){
            //maybe find solution with better perfocrmance, without laoding course again
            $courseOfCrosssubj = $DB->get_record('course', array('id' => $crosssubj->courseid));
            echo $output->competence_profile_crosssubject($crosssubj,$student,true,block_exacomp_get_grading_scheme($crosssubj->id),$crosssubj);
        }
    }
}

foreach($user_courses as $course) {
	//if selected
    echo $output->competence_profile_course($course,$student,true,block_exacomp_get_grading_scheme($course->id)); //prints the actual content
}

/* END CONTENT REGION */
echo $output->footer();


