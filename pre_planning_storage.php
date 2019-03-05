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
$creatorid = required_param('creatorid', PARAM_INT);
$action = optional_param('action', '', PARAM_TEXT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_exacomp', $courseid);
}

require_login($course);
$context = context_course::instance($courseid);
block_exacomp_require_teacher($context);

$PAGE->set_url('/blocks/exacomp/pre_planning_storage.php', array('courseid' => $courseid, 'creatorid'=>$creatorid));
$PAGE->set_heading(block_exacomp_get_string('blocktitle'));
$PAGE->set_pagelayout('embedded');

// build breadcrumbs navigation
block_exacomp_build_breadcrum_navigation($courseid);
block_exacomp_init_js_weekly_schedule();

$output = block_exacomp_get_renderer();
echo $output->header($context, $courseid, '', false);

$students = block_exacomp_get_students_by_course($courseid);
$groups = groups_get_all_groups($courseid);

if(!$students) {
	echo block_exacomp_get_string('nostudents');
		
	echo $output->footer();
	exit;
}

if(strcmp($action, 'empty')==0){
	block_exacomp_empty_pre_planning_storage($courseid);
}

$schedules = block_exacomp_get_pre_planning_storage($creatorid, $courseid);
//if(!$schedules) {
	//echo block_exacomp_get_string('noschedules_pre_planning_storage');
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
echo $output->pre_planning_storage_students($students, $examples, $groups);
echo $output->example_trash(array(), false);
echo $output->create_blocking_event();



echo html_writer::div(html_writer::empty_tag('input', array('type' => 'button',
                                                        'id' => 'save_pre_planning_storage',
	                                                    'class' => 'btn btn-default',
	                                                    'value' => block_exacomp_get_string('save_pre_planning_selection'))).
                    html_writer::empty_tag('input', array('type'=>'submit',
                                                        'id' => 'empty_pre_planning_storage',
                                                        'value' => block_exacomp_get_string('empty_pre_planning_storage'),
                                                        'class' => 'btn btn-default',
                                                        'onclick' => "return confirm('".block_exacomp_get_string('empty_pre_planning_confirm')."')")),
                    '',
                    array('id'=>'save_button'));

echo html_writer::end_tag('form');

echo $output->footer();
