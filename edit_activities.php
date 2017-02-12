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

// TODO: was macht das? wieso brauchen wir das?
if(strcmp("mysql",$CFG->dbtype)==0){
	$sql5="SET @@group_concat_max_len = 5012";

	$DB->execute($sql5);
}

$courseid = required_param('courseid', PARAM_INT);
$action = optional_param('action', "", PARAM_ALPHA);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_simplehtml', $courseid);
}

require_login($course);

$context = context_course::instance($courseid);

block_exacomp_require_teacher($context);

/* PAGE IDENTIFIER - MUST BE CHANGED. Please use string identifier from lang file */
$page_identifier = 'tab_teacher_settings_assignactivities';

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/edit_activities.php', array('courseid' => $courseid));
$PAGE->set_heading(block_exacomp_get_string('blocktitle'));
$PAGE->set_title(block_exacomp_get_string($page_identifier));

// build breadcrumbs navigation
block_exacomp_build_breadcrum_navigation($courseid);

$headertext = "";
$img = new moodle_url('/blocks/exacomp/pix/three.png');
	 
if (($action = optional_param("action", "", PARAM_TEXT) )== "save") {
	block_exacomp_delete_competences_activities();
	// DESCRIPTOR DATA
	block_exacomp_save_competences_activities(isset($_POST['data']) ? $_POST['data'] : array(), $courseid, 0);
	// TOPIC DATA
	block_exacomp_save_competences_activities(isset($_POST['topicdata']) ? $_POST['topicdata'] : array(), $courseid, 1);
	
	if(!isset($_POST['data']) && !isset($_POST['topicdata']))
		$headertext = block_exacomp_get_string('tick_some');
	else{
		$headertext=block_exacomp_get_string("save_success") .html_writer::empty_tag('br')
			.html_writer::empty_tag('img', array('src'=>$img, 'alt'=>'', 'width'=>'60px', 'height'=>'60px'))
			.block_exacomp_get_string('completed_config');
	
		$students = block_exacomp_get_students_by_course($courseid);
		if(empty($students))
			$headertext .= html_writer::empty_tag('br')
				.html_writer::link(new moodle_url('/enrol/users.php', array('id'=>$courseid)), block_exacomp_get_string('optional_step'));
	}
}else{
	$headertext = html_writer::empty_tag('img', array('src'=>$img, 'alt'=>'', 'width'=>'60px', 'height'=>'60px'))
		.block_exacomp_get_string('teacher_third_configuration_step')
		.html_writer::link(new moodle_url('/blocks/exacomp/edit_course.php', array('courseid'=>$courseid)), block_exacomp_get_string('teacher_third_configuration_step_link'));
}

// build tab navigation & print header
$output = block_exacomp_get_renderer();
echo $output->header($context,$courseid, 'tab_teacher_settings');
echo $OUTPUT->tabtree(block_exacomp_build_navigation_tabs_settings($courseid), $page_identifier);

$selected_niveaus = array();
$selected_modules = array();
/* CONTENT REGION */
if(($action = optional_param("action", "", PARAM_TEXT) ) == "filter"){
	if(isset($_POST['niveau_filter']))
		$selected_niveaus = $_POST['niveau_filter'];
		
	if(isset($_POST['module_filter']))
		$selected_modules = $_POST['module_filter'];
}


$subjects = block_exacomp_get_competence_tree($courseid, null, null, true, null, false, array(), false, true);

$modules = block_exacomp_get_allowed_course_modules_for_course($COURSE->id);
$visible_modules = [];
$modules_to_filter = [];

if($modules){
	foreach($modules as $module){
		$compsactiv = $DB->get_records('block_exacompcompactiv_mm', array('activityid'=>$module->id, 'eportfolioitem'=>0));
			
		$module->descriptors = array();
		$module->topics = array();
		
		foreach($compsactiv as $comp){
			if($comp->comptype == 0)
				$module->descriptors[$comp->compid] = $comp->compid;
			else 	
				$module->topics[$comp->compid] = $comp->compid;
		}
		
		if(empty($selected_modules) || in_array(0, $selected_modules) || in_array($module->id, $selected_modules))
			$visible_modules[] = $module;
		
		$modules_to_filter[] = $module;
	}
	
	$niveaus = block_exacomp_extract_niveaus($subjects);
	block_exacomp_filter_niveaus($subjects, $selected_niveaus);
	

	$topics_set = block_exacomp_get_topics_by_subject($courseid, null, true);

	if(!$topics_set){
		echo $output->activity_legend($headertext);
		echo $output->no_topics_warning();
	}else if(count($visible_modules)==0){
		echo $output->activity_legend($headertext);
		echo $output->no_course_activities_warning();
	}else{
		echo $output->activity_legend($headertext);
		echo $output->activity_content($subjects, $visible_modules);
		echo $output->activity_footer($niveaus, $modules_to_filter, $selected_niveaus, $selected_modules);
	}
}

/* END CONTENT REGION */
echo $output->footer();
