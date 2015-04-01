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
require_once dirname(__FILE__) . '/example_upload_form.php';

global $DB, $OUTPUT, $PAGE, $USER;

$courseid = required_param('courseid', PARAM_INT);
$descrid = optional_param('descrid', 0, PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_exacomp', $courseid);
}

// error if example does not exist or was created by somebody else
if ($descrid > 0 && (!$descriptor = $DB->get_record('block_exacompdescriptors', array('id' => $descrid))))
{
    print_error('invalidexample', 'block_exacomp', $exampleid);
}

require_login($course);
$context = context_course::instance($courseid);
require_capability('block/exacomp:teacher', $context);

$PAGE->set_url('/blocks/exacomp/select_crosssubjects.php', array('courseid' => $courseid));
$PAGE->set_heading(get_string('pluginname', 'block_exacomp'));

block_exacomp_init_js_css();
echo $OUTPUT->header();

$course_crosssubjects = block_exacomp_get_cross_subjects_by_course($courseid);
if(!$course_crosssubjects) {
	echo get_string('assign_descriptor_no_crosssubjects_available','block_exacomp');
	echo $OUTPUT->footer();
	exit;
}
$assigned_crosssubjects = $DB->get_records_menu(DB_DESCCROSS,array('descrid'=>$descrid),'','crosssubjid,descrid');
echo get_string('assign_descriptor_to_crosssubject','block_exacomp',$descriptor->title);

echo "<div>";
foreach($course_crosssubjects as $crosssubject)
	echo html_writer::checkbox('crosssubject',$crosssubject->id,isset($assigned_crosssubjects[$crosssubject->id]),$crosssubject->title);

echo "</div>";
echo html_writer::tag("input", '', array("type"=>"button","value"=>"Speichern","name"=>"crosssubjects"));

echo $OUTPUT->footer();
