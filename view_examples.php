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

global $DB, $OUTPUT, $PAGE;

$courseid = required_param('courseid', PARAM_INT);
$courseid_for_tree = $courseid;
$sort = optional_param('sort', "desc", PARAM_ALPHA);
$show_all_examples = optional_param('showallexamples_check', '0', PARAM_INT);
$style = optional_param('style', 0, PARAM_INT);

if (!$course = $DB->get_record('course', array(
	'id' => $courseid,
))
) {
	print_error('invalidcourse', 'block_simplehtml', $courseid);
}

require_login($course);

$context = context_course::instance($courseid);

// CHECK TEACHER
$isTeacher = block_exacomp_is_teacher($context);

$studentid = block_exacomp_get_studentid();

/* PAGE IDENTIFIER - MUST BE CHANGED. Please use string identifier from lang file */
$page_identifier = 'tab_examples';

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/view_examples.php', array(
	'courseid' => $courseid,
));
$PAGE->set_heading(block_exacomp_get_string('blocktitle'));
$PAGE->set_title(block_exacomp_get_string($page_identifier));

// build breadcrumbs navigation
block_exacomp_build_breadcrum_navigation($courseid);

// build tab navigation & print header
$output = block_exacomp_get_renderer();
echo $output->header_v2($page_identifier);

if ($show_all_examples != 0) {
	$courseid_for_tree = 0;
}

/* CONTENT REGION */


echo $output->view_example_header();

if ($style == 0) {
	$tree = block_exacomp_build_example_association_tree($courseid, array(), 0, 0, true);
	echo $output->competence_based_list_tree($tree, $isTeacher, false);
}
if ($style == 1) {
	//could be optimized together with block_exacomp_build_example_tree
	//non critical - only 1 additional query for whole loading process
	$examples = \block_exacomp\example::get_objects_sql("
		SELECT DISTINCT e.*
		FROM {".BLOCK_EXACOMP_DB_COURSETOPICS."} ct
		JOIN {".BLOCK_EXACOMP_DB_DESCTOPICS."} dt ON ct.topicid = dt.topicid
		JOIN {".BLOCK_EXACOMP_DB_DESCEXAMP."} de ON dt.descrid = de.descrid
		JOIN {".BLOCK_EXACOMP_DB_EXAMPLES."} e ON e.id = de.exampid
		WHERE ct.courseid = ?
		ORDER BY e.title
	", [$courseid]);

	if (!$isTeacher) {
		$examples = array_filter($examples, function($example) use ($courseid, $studentid) {
			return block_exacomp_is_example_visible($courseid, $example, $studentid);
		});
	}

	echo $output->example_based_list_tree($examples);
}

/* END CONTENT REGION */
echo $output->footer();

