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

global $DB, $OUTPUT, $PAGE;

$courseid = required_param ( 'courseid', PARAM_INT );
$courseid_for_tree = $courseid;
$sort = optional_param ( 'sort', "desc", PARAM_ALPHA );
$show_all_examples = optional_param ( 'showallexamples_check', '0', PARAM_INT );

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

block_exacomp_init_js_css ();
$PAGE->requires->js ( "/blocks/exacomp/javascript/CollapsibleLists.compressed.js" );
$PAGE->requires->css ( "/blocks/exacomp/css/CollapsibleLists.css" );

// build breadcrumbs navigation
block_exacomp_build_breadcrum_navigation ( $courseid );

// build tab navigation & print header
$output = $PAGE->get_renderer ( 'block_exacomp' );
echo $output->print_wrapperdivstart ();
echo $OUTPUT->header ();
echo $OUTPUT->tabtree ( block_exacomp_build_navigation_tabs ( $context, $courseid ), $page_identifier );

if ($show_all_examples != 0)
	$courseid_for_tree = 0;
	
	/* CONTENT REGION */
echo '<div id="block_exacomp">';

// get all subjects, topics, descriptors and examples
/*$tree = block_exacomp_get_competence_tree ( $courseid, null, false, SHOW_ALL_TOPICS, true, block_exacomp_get_settings_by_course ( $courseid )->filteredtaxonomies );

// unset all descriptors without any examples
foreach ( $tree as $skey => $subject ) {
	foreach ( $subject->subs as $tkey => $topic ) {
		if (isset ( $topic->descriptors )) {
			foreach ( $topic->descriptors as $dkey => $descriptor ) {
				$descriptor = block_exacomp_check_child_descriptors ( $descriptor );
				
				if (count ( $descriptor->children ) == 0)
					unset ( $topic->descriptors [$dkey] );
			}
		}
		if (!isset($topic->descriptors) || count ( $topic->descriptors ) == 0)
			unset ( $subject->subs [$tkey] );
	}
	if (count ( $subject->subs ) == 0)
		unset ( $tree [$skey] );
}
function block_exacomp_check_child_descriptors($descriptor) {
	foreach ( $descriptor->children as $ckey => $cvalue ) {
		$keepDescriptor = false;
		if (count ( $cvalue->examples ) == 0) {
			unset ( $descriptor->children [$ckey] );
			continue;
		}
	}
	
	return $descriptor;
}*/

$tree = block_exacomp_build_example_association_tree($courseid);

$output = $PAGE->get_renderer ( 'block_exacomp' );
echo $output->print_competence_based_list_tree ( $tree , true, true);
echo '</div>';

/*
 * echo $output->print_head_view_examples($sort, $show_all_examples, $PAGE->url, $context);
 *
 * $example_tree = '';
 * if($sort == 'desc')
 * $example_tree = block_exacomp_build_example_tree_desc($courseid_for_tree);
 * else
 * $example_tree = block_exacomp_build_example_tree_tax($courseid_for_tree);
 *
 * echo $output->print_tree_head();
 *
 * if($sort == 'desc')
 * echo $output->print_tree_view_examples_desc($example_tree);
 * else
 * echo $output->print_tree_view_examples_tax($example_tree);
 *
 * echo $output->print_foot_view_examples();
 */
/* END CONTENT REGION */
echo $output->print_wrapperdivend ();
echo $OUTPUT->footer ();

?>