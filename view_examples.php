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
require_once __DIR__."/inc.php";

global $DB, $OUTPUT, $PAGE;

$courseid = required_param ( 'courseid', PARAM_INT );
$courseid_for_tree = $courseid;
$sort = optional_param ( 'sort', "desc", PARAM_ALPHA );
$show_all_examples = optional_param ( 'showallexamples_check', '0', PARAM_INT );
$style = optional_param('style', 0, PARAM_INT);

if (! $course = $DB->get_record ( 'course', array (
		'id' => $courseid 
) )) {
	print_error ( 'invalidcourse', 'block_simplehtml', $courseid );
}

require_login ( $course );

$context = context_course::instance ( $courseid );

/* PAGE IDENTIFIER - MUST BE CHANGED. Please use string identifier from lang file */
$page_identifier = 'tab_examples';

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url ( '/blocks/exacomp/view_examples.php', array (
		'courseid' => $courseid 
) );
$PAGE->set_heading ( get_string ( 'pluginname', 'block_exacomp' ) );
$PAGE->set_title ( get_string ( $page_identifier, 'block_exacomp' ) );

$PAGE->requires->js ( "/blocks/exacomp/javascript/CollapsibleLists.js" );
$PAGE->requires->css ( "/blocks/exacomp/css/CollapsibleLists.css" );

// build breadcrumbs navigation
block_exacomp_build_breadcrum_navigation ( $courseid );

// build tab navigation & print header
$output = $PAGE->get_renderer ( 'block_exacomp' );
echo $output->header($context, $courseid , $page_identifier );

if ($show_all_examples != 0)
	$courseid_for_tree = 0;
	
	/* CONTENT REGION */

$tree = block_exacomp_build_example_association_tree($courseid, array(), 0, 0, true);

echo $output->print_view_example_header();

if($style==0)
	echo $output->print_competence_based_list_tree ( $tree , true, false);
if($style==1){
	$sql = 'SELECT DISTINCT e.*
		FROM {'.\block_exacomp\DB_COURSETOPICS.'} ct
		JOIN {'.\block_exacomp\DB_DESCTOPICS.'} dt ON ct.topicid = dt.topicid
		JOIN {'.\block_exacomp\DB_DESCEXAMP.'} de ON dt.descrid = de.descrid
		JOIN {'.\block_exacomp\DB_EXAMPLES.'} e ON e.id = de.exampid
		WHERE ct.courseid = ?';

	$comp_examples = $DB->get_records_sql($sql, array($courseid));
	
	$content = '';
	foreach($comp_examples as $example){
		$descexamp_mm = block_exacomp_get_descriptor_mms_by_example($example->id);
		$descriptors = array();
		foreach($descexamp_mm as $descexamp){
			if(!in_array($descexamp->descrid, $descriptors))
				$descriptors[$descexamp->descrid] = $descexamp->descrid;
		}
		$tree = block_exacomp_build_example_association_tree($courseid, $descriptors, $example->id, 0, false);
		$content .= $output->print_example_based_list_tree($example, $tree, true, false);
	}
	
	echo html_writer::div($content, '', array('id'=>'associated_div'));
	
}
echo '</div>';

/* END CONTENT REGION */
echo $output->footer ();
