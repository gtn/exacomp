<?php
/*
 * copyright exabis
 */

require __DIR__.'/inc.php';

global $DB, $OUTPUT, $PAGE, $USER;

$courseid = required_param('courseid', PARAM_INT);
$creatorid = required_param('creatorid', PARAM_INT);
$action = optional_param('action', '', PARAM_TEXT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_exacomp', $courseid);
}

require_login($course);
$context = context_course::instance($courseid);
block_exacomp_require_teacher($context);

$PAGE->set_url('/blocks/exacomp/pre_planning_storage.php', array('courseid' => $courseid, 'creatorid'=>$creatorid));
$PAGE->set_heading(get_string('pluginname', 'block_exacomp'));
$PAGE->set_pagelayout('embedded');

// build breadcrumbs navigation
block_exacomp_build_breadcrum_navigation($courseid);
block_exacomp_init_js_weekly_schedule();

$output = block_exacomp_get_renderer();
echo $output->header($context, $courseid, '', false);

$students = block_exacomp_get_students_by_course($courseid);

if(!$students) {
	echo get_string('nostudents','block_exacomp');
		
	echo $output->footer();
	exit;
}

if(strcmp($action, 'empty')==0){
	block_exacomp_empty_pre_planning_storage($creatorid, $courseid);
}

$schedules = block_exacomp_get_pre_planning_storage($creatorid, $courseid);
//if(!$schedules) {
	//echo get_string('noschedules_pre_planning_storage','block_exacomp');
	//echo $output->footer();
	//exit;
//}

$students = block_exacomp_get_student_pool_examples($students, $courseid);

$examples = array();
foreach($schedules as $schedule){
	if(!in_array($schedule->exampleid, $examples))
		$examples[] = $schedule->exampleid;
}

/* CONTENT REGION */
echo html_writer::start_tag('form', array('action'=>$PAGE->url->out(false).'&action=empty', 'method'=>'post'));

echo $output->pre_planning_storage_pool();
echo $output->pre_planning_storage_students($students, $examples);
echo $output->example_trash(array(), false);
echo $output->create_blocking_event();

echo html_writer::div(html_writer::empty_tag('input', array('type'=>'button', 'id'=>'save_pre_planning_storage', 
	'value'=>get_string('save_pre_planning_selection', 'block_exacomp'))).
	html_writer::empty_tag('input', array('type'=>'submit', 'id'=>'empty_pre_planning_storage', 
	'value'=>get_string('empty_pre_planning_storage', 'block_exacomp'))),'', array('id'=>'save_button'));

echo html_writer::end_tag('form');
echo $output->footer();
