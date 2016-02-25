<?php
// This file is part of Exabis Competencies
//
// (c) 2016 exabis internet solutions <info@exabis.at>
//
// Exabis Comeptencies is free software: you can redistribute it and/or modify
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

$courseid = required_param('courseid', PARAM_INT);
$subjectid = optional_param('subjectid', 0, PARAM_INT);
$report = optional_param("report", BLOCK_EXACOMP_REPORT1, PARAM_INT);

require_login($courseid);

// CHECK TEACHER
$isTeacher = block_exacomp_is_teacher();

if($isTeacher && !isset($_SESSION['studentid-'.$COURSE->id]))
	$studentid = BLOCK_EXACOMP_SHOW_STATISTIC;
else {
	$studentid = block_exacomp_get_studentid($isTeacher);
	$coursestudents = block_exacomp_get_students_by_course($courseid);
	if (!isset($coursestudents[$studentid])) {
		$studentid = BLOCK_EXACOMP_SHOW_STATISTIC;
	}
}

/* PAGE IDENTIFIER - MUST BE CHANGED. Please use string identifier from lang file */
$page_identifier = 'tab_competence_grid';

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/competence_grid.php', [
	'courseid' => $courseid,
	'subjectid' => $subjectid,
	'studentid' => $studentid,
	'report' => $report
]);
$PAGE->set_heading(get_string('pluginname', 'block_exacomp'));
$PAGE->set_title(get_string($page_identifier,'block_exacomp'));

// build breadcrumbs navigation
block_exacomp_build_breadcrum_navigation($courseid);

$output = block_exacomp_get_renderer();

// build tab navigation & print header
echo $output->header_v2($page_identifier);

/* CONTENT REGION */

$course_settings = block_exacomp_get_settings_by_course($courseid);
$dropdown_subjects = block_exacomp_get_subjects_by_course($courseid, true);

if($dropdown_subjects && $subjectid == 0)
	$subjectid = key($dropdown_subjects);

list($niveaus, $skills, $subjects, $data, $selection) = block_exacomp_init_competence_grid_data($courseid, $subjectid, $studentid, (block_exacomp_get_settings_by_course($courseid)->show_all_examples != 0 || $isTeacher), block_exacomp_get_settings_by_course($courseid)->filteredtaxonomies);

echo $output->subject_dropdown(block_exacomp_get_schooltypetree_by_topics($dropdown_subjects,true), $subjectid);
if($data) {
	if ($isTeacher && !block_exacomp_get_settings_by_course($courseid)->nostudents) {
		echo ' '.get_string("choosestudent","block_exacomp").' ';
		echo $output->studentselector(block_exacomp_get_students_by_course($courseid),$studentid, $output::STUDENT_SELECTOR_OPTION_COMPETENCE_GRID_DROPDOWN);
	}
	
	if($course_settings->nostudents != 1)
		echo $output->competence_grid_reports_dropdown();

	echo html_writer::start_div();
	
	if(isset($dropdown_subjects[$subjectid]->infolink))
		echo html_writer::tag("p",get_string('infolink','block_exacomp') . html_writer::link($dropdown_subjects[$subjectid]->infolink, $dropdown_subjects[$subjectid]->infolink,array('target'=>'_blank')));

	echo $output->competence_grid($niveaus, $skills, $subjects, $data, $selection, $courseid,$studentid);

	echo html_writer::end_div();
}
else {
	echo html_writer::div(get_string('competencegrid_nodata', 'block_exacomp'));
}
/* END CONTENT REGION */
echo $output->footer();
