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
$descrid = optional_param('descrid', 0, PARAM_INT);

// error if example does not exist or was created by somebody else
if ($descrid > 0 && (!$descriptor = $DB->get_record('block_exacompdescriptors', array('id' => $descrid)))) {
	print_error('invalidexample', 'block_exacomp', $exampleid);
}

require_login($courseid);
$context = context_course::instance($courseid);
block_exacomp_require_teacher($context);

$PAGE->set_url('/blocks/exacomp/select_crosssubjects.php', array('courseid' => $courseid));
$PAGE->set_heading(block_exacomp_get_string('blocktitle'));
$PAGE->set_pagelayout('embedded');

// build breadcrumbs navigation
block_exacomp_build_breadcrum_navigation($courseid);

$output = block_exacomp_get_renderer();
echo $output->header($context, $courseid, '', false);

$subjects = block_exacomp_get_cross_subjects_grouped_by_subjects();

$assigned_crosssubjects = $DB->get_records_menu(BLOCK_EXACOMP_DB_DESCCROSS,array('descrid'=>$descrid),'','crosssubjid,descrid');

$content = "";
$crosssubjects_exist = false;
$content .= html_writer::start_tag('ul', array("class"=>"exa-tree exa-tree-open-all"));
		
foreach($subjects as $subject){
	$content .= html_writer::start_tag('li');
	$content .= $subject->title;

	$content .= html_writer::start_tag('ul');

	foreach($subject->crosssubjects as $crosssubject){

		$course = $DB->get_record('course', array('id'=>$crosssubject->courseid));

		$crosssubjects_exist = true;
		$content .= html_writer::start_tag('li');
		$content .= html_writer::checkbox('crosssubject', $crosssubject->id, isset($assigned_crosssubjects[$crosssubject->id]),
			$crosssubject->title." (".@$course->fullname.') ');
		$content .= html_writer::end_tag('li');
	}
	$content .= html_writer::end_tag('ul');
	$content .= html_writer::end_tag('li');
}
$content .= html_writer::end_tag('ul');

if(!$crosssubjects_exist) {
	echo block_exacomp_get_string('assign_descriptor_no_crosssubjects_available');
	echo $OUTPUT->footer();
	exit;
}
echo block_exacomp_get_string('assign_descriptor_to_crosssubject', null,$descriptor->title);
echo html_writer::empty_tag('br');

echo "<div>";
echo $content;

echo "</div>";

echo html_writer::div(html_writer::tag("input", '', array("type" => "button",
                                                        "value" => block_exacomp_get_string('add_descriptors_to_crosssub'),
                                                        "id" => "crosssubjects",
                                                        'class' => 'btn btn-default'
                                                        )
                    ), '', array('id'=>'exabis_save_button'));

echo $output->footer();
