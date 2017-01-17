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

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error("That's an invalid course id");
}

require_login($course);

$context = context_system::instance();
$coursecontext = context_course::instance($courseid);

require_capability('block/exacomp:teacher', $coursecontext);
$url = '/blocks/exacomp/externaltrainers.php';
$PAGE->set_heading(block_exacomp_get_string('blocktitle'));
$PAGE->set_title(block_exacomp_get_string('blocktitle'));
$PAGE->set_url($url, ['courseid' => $courseid]);

$students = get_users_by_capability($coursecontext, 'block/exacomp:student');

$selectstudents = array();
$selectstudents[0] = block_exacomp_get_string('block_exacomp_external_trainer_allstudents');
foreach($students as $student) {
	$selectstudents[$student->id] = fullname($student); 
}
$noneditingteachers = get_users_by_capability($coursecontext, 'block/exacomp:teacher');
$selectteachers= array();
foreach($noneditingteachers as $noneditingteacher) {
	$selectteachers[$noneditingteacher->id] = fullname($noneditingteacher);
}

$trainerid = optional_param('trainerid', -1, PARAM_INT);
$studentid = optional_param('studentid', -1, PARAM_INT);


if($trainerid > 0 && $studentid > 0) {
    if(!$DB->record_exists('block_exacompexternaltrainer', array('trainerid'=>$trainerid,'studentid'=>$studentid)))  
        $DB->insert_record('block_exacompexternaltrainer', array('trainerid'=>$trainerid,'studentid'=>$studentid));  
} elseif($trainerid > 0 && $studentid == 0) {
	foreach($students as $student) {
		if(!$DB->record_exists('block_exacompexternaltrainer', array('trainerid'=>$trainerid,'studentid'=>$student->id)))
			$DB->insert_record('block_exacompexternaltrainer', array('trainerid'=>$trainerid,'studentid'=>$student->id));
	}
}

if(($delete = optional_param('delete',0,PARAM_INT)) > 0) {
	$DB->delete_records(BLOCK_EXACOMP_DB_EXTERNAL_TRAINERS,array('id'=>$delete));
}

$externaltrainers = $DB->get_records(BLOCK_EXACOMP_DB_EXTERNAL_TRAINERS);

$PAGE->set_title(block_exacomp_get_string('block_exacomp_external_trainer_assign'));

$output = block_exacomp_get_renderer();
echo $output->header($context, $courseid, 'tab_admin_settings');
echo $output->tabtree(block_exacomp_build_navigation_tabs_admin_settings($courseid), 'tab_external_trainer_assign');

echo $output->heading(block_exacomp_get_string('block_exacomp_external_trainer_assign'));
echo '<form method="post">';
echo block_exacomp_get_string('block_exacomp_external_trainer');
echo html_writer::select($selectteachers, 'trainerid');
echo '&nbsp;&nbsp;&nbsp;&nbsp;'.block_exacomp_get_string('block_exacomp_external_trainer_student');
echo html_writer::select($selectstudents, 'studentid');
echo '<input type="submit">';
echo '</form>';
if($externaltrainers){
	echo '<table id="user-table" class="generaltable externaltrainerstable">';
	echo '<tr><th>Trainer</th><th>Schüler</th><th></th></tr>';
	foreach($externaltrainers as $trainer) {
		echo '<tr>';
			echo '<td>' . fullname($DB->get_record('user', array('id'=>$trainer->trainerid))) . '</td>';
			echo '<td>' . fullname($DB->get_record('user', array('id'=>$trainer->studentid))) . '</td>';
			echo '<td> <a href="'.$CFG->wwwroot.'/blocks/exacomp/externaltrainers.php?delete='.$trainer->id.'&courseid='.$courseid.'">'.
			'<img src="pix/del.png" /></a></td>';

		echo '</tr>';
	}
	echo '</table>';
}
echo $output->footer();

