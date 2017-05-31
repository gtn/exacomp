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
require_once $CFG->dirroot.'/lib/datalib.php';

$courseid = required_param ( 'courseid', PARAM_INT );
$action = optional_param ( 'action', "", PARAM_ALPHA );
$fromimport = optional_param ( 'fromimport', 0, PARAM_INT );

if (! $course = $DB->get_record ( 'course', array (
		'id' => $courseid 
) )) {
	print_error ( 'invalidcourse', 'block_simplehtml', $courseid );
}

require_login ( $course );

$context = context_system::instance ();
block_exacomp_require_admin($context);

$check = block_exacomp\data::has_data();
if (! $check) {
	redirect ( new moodle_url ( '/blocks/exacomp/import.php', array (
			'courseid' => $courseid 
	) ) );
}

/* PAGE IDENTIFIER - MUST BE CHANGED. Please use string identifier from lang file */
$page_identifier = 'tab_admin_configuration';

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url ( '/blocks/exacomp/edit_config.php', array (
		'courseid' => $courseid 
) );
$PAGE->set_heading ( block_exacomp_get_string('blocktitle') );
$PAGE->set_title ( block_exacomp_get_string($page_identifier) );

// build breadcrumbs navigation
block_exacomp_build_breadcrum_navigation ( $courseid );




if ($fromimport == 1) {
		$img = 'two_admin.png';
} else {
		$img = 'one_admin.png';
}

// Falls Formular abgesendet, speichern
if (isset ( $action ) && $action == 'save') {
	$values = isset ( $_POST['data'] ) ? $_POST['data'] : array ();
	
	block_exacomp_set_mdltype ( $values );
	
	if (! isset ( $_POST['data'] ))
		$headertext = block_exacomp_get_string('tick_some');
	else {
		$string = block_exacomp_get_string('next_step');
		
		$url = 'edit_course.php';
		
		$headertext = block_exacomp_get_string("save_success") . html_writer::empty_tag ( 'br' ) . html_writer::empty_tag ( 'img', array (
				'src' => new moodle_url ( '/blocks/exacomp/pix/' . $img ),
				'alt' => '',
				'width' => '60px',
				'height' => '60px' 
		) ) . html_writer::link ( new moodle_url ( $url, array (
				'courseid' => $courseid 
		) ), $string );
	}
} else {
	$headertext = html_writer::empty_tag ( 'img', array (
			'src' => new moodle_url ( '/blocks/exacomp/pix/' . $img ),
			'alt' => '',
			'width' => '60px',
			'height' => '60px' 
	) ) . block_exacomp_get_string('second_configuration_step') . html_writer::empty_tag ( 'br' ) . block_exacomp_get_string("explainconfig");
}

// build tab navigation & print header
$output = block_exacomp_get_renderer();
echo $output->header($context, $courseid, 'tab_admin_settings');
echo $OUTPUT->tabtree(block_exacomp_build_navigation_tabs_admin_settings($courseid), $page_identifier);

/* CONTENT REGION */

if (block_exacomp_is_skillsmanagement()) {
	echo $output->notification(block_exacomp_trans('en:This settings is not available in skillsmanagement mode!'));
	echo $output->footer();
	exit;
}

/* HTML CONTENT */

$data = new stdClass ();
$data->headertext = $headertext;
$data->levels = array ();

$levels = block_exacomp_get_edulevels ();
foreach ( $levels as $level ) {
	$data->levels[$level->id] = new stdClass ();
	$data->levels[$level->id]->level = $level;
	$data->levels[$level->id]->schooltypes = array ();
	
	$types = block_exacomp_get_schooltypes ( $level->id );
	
	foreach ( $types as $type ) {
		$ticked = block_exacomp_get_mdltypes ( $type->id );
		
		$data->levels[$level->id]->schooltypes[$type->id] = new stdClass ();
		$data->levels[$level->id]->schooltypes[$type->id]->schooltype = $type;
		$data->levels[$level->id]->schooltypes[$type->id]->ticked = $ticked;
	}
}

echo $output->edit_config ( $data, $courseid, $fromimport );

/* END CONTENT REGION */
echo $output->footer();
