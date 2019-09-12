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

$courseid = required_param('courseid', PARAM_INT);
$action = optional_param('action', "", PARAM_ALPHAEXT);

require_login($courseid);
block_exacomp_require_teacher();

/* PAGE IDENTIFIER - MUST BE CHANGED. Please use string identifier from lang file */
$page_identifier = 'tab_teacher_settings_selection';

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/courseselection.php', array('courseid' => $courseid));
$PAGE->set_heading(block_exacomp_get_string('blocktitle'));
$PAGE->set_title(block_exacomp_get_string($page_identifier));

// build breadcrumbs navigation
block_exacomp_build_breadcrum_navigation($courseid);

$headertext = "";

$img = new moodle_url('/blocks/exacomp/pix/two.png');

if ($action == 'save') {
	$topics = block_exacomp\param::optional_array('topics', [PARAM_INT]);
	block_exacomp_set_coursetopics($courseid, $topics);

	if(empty($topics)) {
		$headertext = block_exacomp_get_string('tick_some');
	} else {
		$course_settings = block_exacomp_get_settings_by_course($courseid);
		if($course_settings->uses_activities){
			if (block_exacomp_is_activated($courseid))
			$headertext=block_exacomp_get_string("save_success") .html_writer::empty_tag('br')
				.html_writer::empty_tag('img', array('src'=>$img, 'alt'=>'', 'width'=>'60px', 'height'=>'60px'))
				. html_writer::link(new moodle_url('edit_activities.php', array('courseid'=>$courseid)), block_exacomp_get_string('next_step'));
		}else{
			 $headertext=block_exacomp_get_string("save_success") .html_writer::empty_tag('br')
				.html_writer::empty_tag('img', array('src'=>$img, 'alt'=>'', 'width'=>'60px', 'height'=>'60px')).block_exacomp_get_string('completed_config');

	   		 $students = block_exacomp_get_students_by_course($courseid);
	   		 if(empty($students))
//				$headertext .= html_writer::empty_tag('br')
//					.html_writer::link(new moodle_url('/enrol/users.php', array('id'=>$courseid)), block_exacomp_get_string('optional_step'));
                $headertext .= html_writer::empty_tag('br')
                    .html_writer::span(block_exacomp_get_string('enrol_users'));
		}
	}
}else{
	$headertext = html_writer::empty_tag('img', array('src'=>$img, 'alt'=>'', 'width'=>'60px', 'height'=>'60px')).block_exacomp_get_string('teacher_second_configuration_step');
}

// build tab navigation & print header
$output = block_exacomp_get_renderer();
echo $output->header_v2('tab_teacher_settings');
echo $OUTPUT->tabtree(block_exacomp_build_navigation_tabs_settings($courseid), $page_identifier);
/* CONTENT REGION */

// skillsmanagemenet has a per course configuration, excaomp has a per moodle configuration (courseid = 0)
$limit_courseid = block_exacomp_is_skillsmanagement() ? $courseid : 0;

$schooltypes = block_exacomp_build_schooltype_tree_for_courseselection($limit_courseid);

$active_topics = block_exacomp_get_topics_by_subject($courseid, 0, true);

echo $output->courseselection($schooltypes, $active_topics, $headertext);

/* END CONTENT REGION */
echo $output->footer();
