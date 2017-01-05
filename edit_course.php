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
require_once($CFG->dirroot . "/lib/datalib.php");

global $DB, $OUTPUT, $PAGE;

$courseid = required_param('courseid', PARAM_INT);
$action = optional_param('action', "", PARAM_ALPHAEXT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_simplehtml', $courseid);
}

require_login($course);

$context = context_course::instance($courseid);

block_exacomp_require_teacher($context);

/* PAGE IDENTIFIER - MUST BE CHANGED. Please use string identifier from lang file */
$page_identifier = 'tab_teacher_settings_configuration';

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/edit_course.php', array('courseid' => $courseid));
$PAGE->set_heading(get_string('blocktitle', 'block_exacomp'));
$PAGE->set_title(get_string($page_identifier, 'block_exacomp'));

// build breadcrumbs navigation
block_exacomp_build_breadcrum_navigation($courseid);

$headertext = '';

if ($action == 'save_coursesettings') {
	$settings = new stdClass;
	$settings->grading = optional_param('grading', 3, PARAM_INT);
	
	if($settings->grading == 0)
		$settings->grading = 3;
	
	$settings->uses_activities = optional_param('uses_activities', "", PARAM_INT);
	$settings->show_all_descriptors = optional_param('show_all_descriptors', "", PARAM_INT);
	$settings->show_all_examples = optional_param('show_all_examples', "", PARAM_INT);
	$settings->nostudents = optional_param('nostudents', 0, PARAM_INT);
	$settings->filteredtaxonomies = json_encode((isset($_POST['filteredtaxonomies'])) ? array_values($_POST['filteredtaxonomies']) : SHOW_ALL_TAXONOMIES);
	
	block_exacomp_save_coursesettings($courseid, $settings);	
	
	$url = 'courseselection.php';
	
	$headertext = "";
	if($settings->uses_activities==1 && block_exacomp_check_user_evaluation_exists($courseid))
		$headertext .= get_string("warning_use_activities", "block_exacomp").html_writer::empty_tag('br'); 
	
	$headertext.=get_string("save_success", "block_exacomp") .html_writer::empty_tag('br')
		.html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/one.png'), 'alt'=>'', 'width'=>'60px', 'height'=>'60px'))				
		. html_writer::link(new moodle_url($url, array('courseid'=>$courseid)), get_string('next_step', 'block_exacomp'));
}else{
	$url = 'courseselection.php';
	
	$headertext = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/one.png'), 'alt'=>'', 'width'=>'60px', 'height'=>'60px')).get_string('teacher_first_configuration_step', 'block_exacomp')
		.' '.html_writer::link(new moodle_url($url, array('courseid'=>$courseid)), get_string('next_step_first_teacher_step', 'block_exacomp'));
} 

// build tab navigation & print header
$output = block_exacomp_get_renderer();
echo $output->header($context,$courseid, 'tab_teacher_settings');
echo $OUTPUT->tabtree(block_exacomp_build_navigation_tabs_settings($courseid), $page_identifier);
/* CONTENT REGION */
$courseSettings = block_exacomp_get_settings_by_course($courseid);


echo $output->edit_course($courseSettings, $courseid, $headertext);

/* END CONTENT REGION */
echo $output->footer();
