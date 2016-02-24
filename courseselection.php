<?php

require_once __DIR__."/inc.php";

$courseid = required_param('courseid', PARAM_INT);
$action = optional_param('action', "", PARAM_ALPHAEXT);

require_login($courseid);
block_exacomp_require_teacher();

/* PAGE IDENTIFIER - MUST BE CHANGED. Please use string identifier from lang file */
$page_identifier = 'tab_teacher_settings_selection';

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/courseselection.php', array('courseid' => $courseid));
$PAGE->set_heading(get_string('pluginname', 'block_exacomp'));
$PAGE->set_title(get_string($page_identifier, 'block_exacomp'));

// build breadcrumbs navigation
block_exacomp_build_breadcrum_navigation($courseid);

$headertext = "";

$img = new moodle_url('/blocks/exacomp/pix/two.png');
	 	
if ($action == 'save') {
	$topics = block_exacomp\param::optional_array('topics', [PARAM_INT]);
	block_exacomp_set_coursetopics($courseid, $topics);
	
	if(empty($topics)) {
		$headertext = get_string('tick_some', 'block_exacomp');
	} else {
		$course_settings = block_exacomp_get_settings_by_course($courseid);
		if($course_settings->uses_activities){
			if (block_exacomp_is_activated($courseid))
			$headertext=get_string("save_success", "block_exacomp") .html_writer::empty_tag('br')
				.html_writer::empty_tag('img', array('src'=>$img, 'alt'=>'', 'width'=>'60px', 'height'=>'60px'))				
				. html_writer::link(new moodle_url('edit_activities.php', array('courseid'=>$courseid)), get_string('next_step', 'block_exacomp'));
		}else{
			 $headertext=get_string("save_success", "block_exacomp") .html_writer::empty_tag('br')
				.html_writer::empty_tag('img', array('src'=>$img, 'alt'=>'', 'width'=>'60px', 'height'=>'60px')).get_string('completed_config', 'block_exacomp');
	
	   		 $students = block_exacomp_get_students_by_course($courseid);
	   		 if(empty($students))
				$headertext .= html_writer::empty_tag('br')
					.html_writer::link(new moodle_url('/enrol/users.php', array('id'=>$courseid)), get_string('optional_step', 'block_exacomp'));
		}
	}
}else{
	$headertext = html_writer::empty_tag('img', array('src'=>$img, 'alt'=>'', 'width'=>'60px', 'height'=>'60px')).get_string('teacher_second_configuration_step', 'block_exacomp');
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

echo $output->print_courseselection($schooltypes, $active_topics, $headertext);

/* END CONTENT REGION */
echo $output->footer();
