<?php
/***************************************************************
 *  Copyright notice
*
*  (c) 2006 exabis internet solutions <info@exabis.at>
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
***************************************************************/
global $DB;
require_once __DIR__.'/inc.php';

$courseid = required_param('courseid', PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error("That's an invalid course id");
}

require_login($course);

$context = context_system::instance();

require_capability('block/exacomp:assignstudents', $context);
$url = '/blocks/exacomp/externaltrainers.php';
$PAGE->set_url($url);

$coursecontext = context_course::instance($courseid);

$students = get_users_by_capability($coursecontext, 'block/exacomp:student');

$selectstudents = array();
foreach($students as $student) {
	$selectstudents[$student->id] = fullname($student); 
}
$noneditingteachers = get_users_by_capability($coursecontext, 'block/exacomp:teacher');
$selectteachers= array();
foreach($noneditingteachers as $noneditingteacher) {
	$selectteachers[$noneditingteacher->id] = fullname($noneditingteacher);
}

$trainerid = optional_param('trainerid', 0, PARAM_INT);
$studentid = optional_param('studentid', 0, PARAM_INT);

if($trainerid > 0 && $studentid) {
	if(!$DB->record_exists(\block_exacomp\DB_EXTERNAL_TRAINERS, array('trainerid'=>$trainerid,'studentid'=>$studentid)))
		$DB->insert_record(\block_exacomp\DB_EXTERNAL_TRAINERS, array('trainerid'=>$trainerid,'studentid'=>$studentid));
}
if(($delete = optional_param('delete',0,PARAM_INT)) > 0) {
	$DB->delete_records(\block_exacomp\DB_EXTERNAL_TRAINERS,array('id'=>$delete));
}

$externaltrainers = $DB->get_records(\block_exacomp\DB_EXTERNAL_TRAINERS);

$PAGE->set_title(get_string('block_exacomp_external_trainer_assign','block_exacomp'));

$output = block_exacomp_get_renderer();
echo $output->header($context, $courseid, 'tab_admin_settings');
echo $output->tabtree(block_exacomp_build_navigation_tabs_admin_settings($courseid), 'tab_external_trainer_assign');

echo $output->heading(get_string('block_exacomp_external_trainer_assign','block_exacomp'));
echo '<form method="post">';
echo get_string('block_exacomp_external_trainer','block_exacomp');
echo html_writer::select($selectteachers, 'trainerid');
echo '&nbsp;&nbsp;&nbsp;&nbsp;'.get_string('block_exacomp_external_trainer_student','block_exacomp');
echo html_writer::select($selectstudents, 'studentid');
echo '<input type="submit">';
echo '</form>';

echo '<table id="user-table">';
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

echo $output->footer();