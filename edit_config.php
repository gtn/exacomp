<?php

/*
 * *************************************************************
 * Copyright notice
 *
 * (c) 2014 exabis internet solutions <info@exabis.at>
 * All rights reserved
 *
 * You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This module is based on the Collaborative Moodle Modules from
 * NCSA Education Division (http://www.ncsa.uiuc.edu)
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 * *************************************************************
 */
require_once dirname ( __FILE__ ) . "/inc.php";
require_once dirname ( __FILE__ ) . '/lib/lib.php';
require_once ($CFG->dirroot . "/lib/datalib.php");
require_once dirname ( __FILE__ ) . '/lib/xmllib.php';

global $DB, $OUTPUT, $PAGE, $COURSE, $CFG;

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

$check = block_exacomp_data::has_data();
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
$PAGE->set_heading ( get_string ( 'pluginname', 'block_exacomp' ) );
$PAGE->set_title ( get_string ( $page_identifier, 'block_exacomp' ) );

// build breadcrumbs navigation
block_exacomp_build_breadcrum_navigation ( $courseid );

if ($fromimport == 1) {
		$img = 'two_admin.png';
} else {
		$img = 'one_admin.png';
}

// Falls Formular abgesendet, speichern
if (isset ( $action ) && $action == 'save') {
	$values = isset ( $_POST ['data'] ) ? $_POST ['data'] : array ();
	
	block_exacomp_set_mdltype ( $values );
	
	if (! isset ( $_POST ['data'] ))
		$headertext = get_string ( 'tick_some', 'block_exacomp' );
	else {
		$string = get_string ( 'next_step', 'block_exacomp' );
		
		$url = 'edit_course.php';
		
		$headertext = get_string ( "save_success", "block_exacomp" ) . html_writer::empty_tag ( 'br' ) . html_writer::empty_tag ( 'img', array (
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
	) ) . get_string ( 'second_configuration_step', 'block_exacomp' ) . html_writer::empty_tag ( 'br' ) . get_string ( "explainconfig", "block_exacomp" );
}

// build tab navigation & print header
$output = $PAGE->get_renderer ( 'block_exacomp' );
echo $output->header($context, $courseid, 'tab_admin_settings');
echo $OUTPUT->tabtree(block_exacomp_build_navigation_tabs_admin_settings($courseid), $page_identifier);

/* CONTENT REGION */

/* HTML CONTENT */

$data = new stdClass ();
$data->headertext = $headertext;
$data->levels = array ();

$levels = block_exacomp_get_edulevels ();
foreach ( $levels as $level ) {
	$data->levels [$level->id] = new stdClass ();
	$data->levels [$level->id]->level = $level;
	$data->levels [$level->id]->schooltypes = array ();
	
	$types = block_exacomp_get_schooltypes ( $level->id );
	
	foreach ( $types as $type ) {
		$ticked = block_exacomp_get_mdltypes ( $type->id );
		
		$data->levels [$level->id]->schooltypes [$type->id] = new stdClass ();
		$data->levels [$level->id]->schooltypes [$type->id]->schooltype = $type;
		$data->levels [$level->id]->schooltypes [$type->id]->ticked = $ticked;
	}
}

echo $output->print_edit_config ( $data, $courseid, $fromimport );

/* END CONTENT REGION */
echo $output->footer();

?>