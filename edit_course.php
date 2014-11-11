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
require_once dirname(__FILE__) . '/lib/lib.php';
require_once($CFG->dirroot . "/lib/datalib.php");

global $DB, $OUTPUT, $PAGE;

$courseid = required_param('courseid', PARAM_INT);
$action = optional_param('action', "", PARAM_ALPHAEXT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_simplehtml', $courseid);
}

require_login($course);

$context = context_course::instance($courseid);

require_capability('block/exacomp:teacher', $context);

/* PAGE IDENTIFIER - MUST BE CHANGED. Please use string identifier from lang file */
$page_identifier = 'tab_teacher_settings_configuration';

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/edit_course.php', array('courseid' => $courseid));
$PAGE->set_heading(get_string('pluginname', 'block_exacomp'));
$PAGE->set_title(get_string($page_identifier, 'block_exacomp'));

block_exacomp_init_js_css();

// build breadcrumbs navigation
$coursenode = $PAGE->navigation->find($courseid, navigation_node::TYPE_COURSE);
$blocknode = $coursenode->add(get_string('pluginname','block_exacomp'));
$pagenode = $blocknode->add(get_string($page_identifier,'block_exacomp'), $PAGE->url);
$pagenode->make_active();
$headertext = '';

if ($action == 'save_coursesettings') {
	$settings = new stdClass;
	$settings->grading = optional_param('grading', 1, PARAM_INT);
	
	if($settings->grading == 0)
		$settings->grading = 1;
	
	$settings->uses_activities = optional_param('uses_activities', "", PARAM_INT);
	$settings->show_all_descriptors = optional_param('show_all_descriptors', "", PARAM_INT);
	$settings->show_all_examples = optional_param('show_all_examples', "", PARAM_INT);
	$settings->usedetailpage = optional_param('usedetailpage', "", PARAM_INT);
	$settings->profoundness = optional_param('profoundness', 0, PARAM_INT);
	
	block_exacomp_save_coursesettings($courseid, $settings);	
	
	if(!$version) $url = 'courseselection.php';
	else $url = 'edit_config.php';
	
	$headertext=get_string("save_success", "block_exacomp") .html_writer::empty_tag('br')
		.html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/one.png'), 'alt'=>'', 'width'=>'60px', 'height'=>'60px'))				
		. html_writer::link(new moodle_url($url, array('courseid'=>$courseid)), get_string('next_step', 'block_exacomp'));
}else{
	$headertext = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/one.png'), 'alt'=>'', 'width'=>'60px', 'height'=>'60px')).get_string('teacher_first_configuration_step', 'block_exacomp');
} 

// build tab navigation & print header
$output = $PAGE->get_renderer('block_exacomp');
echo $OUTPUT->header();
echo $output->print_wrapperdivstart();
echo $OUTPUT->tabtree(block_exacomp_build_navigation_tabs($context,$courseid), 'tab_teacher_settings');
echo $OUTPUT->tabtree(block_exacomp_build_navigation_tabs_settings($courseid), $page_identifier);
/* CONTENT REGION */
$courseSettings = block_exacomp_get_settings_by_course($courseid);


echo $output->print_edit_course($courseSettings, $courseid, $headertext);

/* END CONTENT REGION */
echo $output->print_wrapperdivend();
echo $OUTPUT->footer();
?>