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
require_once dirname(__FILE__) . '/example_submission_form.php';

global $DB, $OUTPUT, $PAGE, $USER;

$courseid = required_param('courseid', PARAM_INT);
$exampleid = required_param('exampleid', PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_exacomp', $courseid);
}

// error if example does not exist or was created by somebody else
if (!$example = $DB->get_record('block_exacompexamples', array('id' => $exampleid))) {
    print_error('invalidexample', 'block_exacomp', $exampleid);
}

require_login($course);

$context = context_course::instance($courseid);

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/competence_association.php', array('courseid' => $courseid,'exampleid' => $exampleid));
$PAGE->set_heading(get_string('pluginname', 'block_exacomp'));

block_exacomp_init_js_css();
$PAGE->requires->js("/blocks/exacomp/javascript/CollapsibleLists.compressed.js");
$PAGE->requires->css("/blocks/exacomp/css/CollapsibleLists.css");

// build breadcrumbs navigation
$coursenode = $PAGE->navigation->find($courseid, navigation_node::TYPE_COURSE);
$blocknode = $coursenode->add(get_string('pluginname','block_exacomp'));
$blocknode->make_active();

// build tab navigation & print header
echo $OUTPUT->header();
echo '<div id="exacomp">';
/* CONTENT REGION */

//get descriptors for the given example
$example_descriptors = $DB->get_records(DB_DESCEXAMP,array('exampid'=>$exampleid),'','descrid');
//get all subjects, topics, descriptors and examples
$tree = block_exacomp_get_competence_tree($courseid, null, false, SHOW_ALL_TOPICS, true, block_exacomp_get_settings_by_course($courseid)->filteredtaxonomies);
// unset all descriptors, topics and subjects hat do not contain the example descriptors
foreach($tree as $skey => $subject) {
	foreach ( $subject->subs as $tkey => $topic ) {
		foreach ( $topic->descriptors as $dkey => $descriptor ) {
			$descriptor = block_exacomp_check_child_descriptors($descriptor, $example_descriptors, $exampleid);
			
			if(count($descriptor->children) == 0)
				unset($topic->descriptors[$dkey]);
		}
		if(count($topic->descriptors) == 0)
			unset($subject->subs[$tkey]);
	}
	if(count($subject->subs) == 0)
		unset($tree[$skey]);
}

function block_exacomp_check_child_descriptors($descriptor, $example_descriptors, $exampleid) {

	foreach($descriptor->children as $ckey => $cvalue) {
		$keepDescriptor = false;
		if (array_key_exists ( $cvalue->id, $example_descriptors )) {
			$keepDescriptor = true;
		}
		if (! $keepDescriptor) {
			unset ( $descriptor->children[$ckey] );
			continue;
		}
		foreach($cvalue->examples as $ekey => $example) {
			if($example->id != $exampleid)
				unset($cvalue->examples[$ekey]);
		}
	}
	
	return $descriptor;
}
$output = $PAGE->get_renderer('block_exacomp');
echo html_writer::tag("p",get_string("competence_associations_explaination","block_exacomp",$example->title));
echo $output->print_competence_based_list_tree($tree);

/* END CONTENT REGION */
echo '</div>';
echo $OUTPUT->footer();
?>