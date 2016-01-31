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

require_once __DIR__."/inc.php";

global $DB, $OUTPUT, $PAGE, $USER;

$courseid = required_param('courseid', PARAM_INT);
$crosssubjid = optional_param('crosssubjid', 0, PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_exacomp', $courseid);
}

// error if example does not exist or was created by somebody else
if ($crosssubjid > 0 && (!$crosssubject = $DB->get_record(\block_exacomp\DB_CROSSSUBJECTS, array('id' => $crosssubjid))))
{
	print_error('invalidexample', 'block_exacomp', $exampleid);
}

require_login($course);
$context = context_course::instance($courseid);
block_exacomp_require_teacher($context);

$PAGE->set_url('/blocks/exacomp/select_students.php', array('courseid' => $courseid));
$PAGE->set_heading(get_string('pluginname', 'block_exacomp'));
$PAGE->set_pagelayout('embedded');

// build breadcrumbs navigation
block_exacomp_build_breadcrum_navigation($courseid);

$output = block_exacomp_get_renderer();
echo $output->header($context, $courseid, '', false);

$students = block_exacomp_get_students_by_course($courseid);
if(!$students) {
	echo get_string('nostudents','block_exacomp');
	echo $OUTPUT->footer();
	exit;
}

$assigned_students = $DB->get_records_menu(\block_exacomp\DB_CROSSSTUD,array('crosssubjid'=>$crosssubjid),'','studentid,crosssubjid');
$shared = $crosssubject->shared;
echo "<div>";
echo get_string('share_crosssub_with_all', 'block_exacomp', $crosssubject->title);
echo html_writer::checkbox('share_all', 'share_all', ($shared==1), '').html_writer::empty_tag('br').html_writer::empty_tag('br');

echo get_string('share_crosssub_with_students','block_exacomp',$crosssubject->title).html_writer::empty_tag('br');

foreach($students as $student)
	echo html_writer::checkbox('student',$student->id,isset($assigned_students[$student->id]),$student->firstname." ".$student->lastname, ($shared==1)?array('disabled'=>true):array()).html_writer::empty_tag('br');

echo "</div>";
echo html_writer::empty_tag('br').html_writer::tag("input", '', array("type"=>"button","value"=>get_string('save_selection', 'block_exacomp'),"name"=>"share_crosssubj_students"));

echo $output->footer();
