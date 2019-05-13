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
require_once __DIR__."/classes/data.php";

$courseid = required_param('courseid', PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_simplehtml', $courseid);
}

require_login($course);

$course_context = context_course::instance($courseid);

require_capability('block/exacomp:admin', context_system::instance());

$action = required_param('action', PARAM_ALPHANUMEXT);
$source = required_param('source', PARAM_INT);

$output = block_exacomp_get_renderer();

/* PAGE IDENTIFIER - MUST BE CHANGED. Please use string identifier from lang file */
$page_identifier = 'tab_admin_settings';

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/source_delete.php', array('courseid' => $courseid, 'source' => $source, 'action' => 'select'));
$PAGE->set_heading(block_exacomp_get_string('blocktitle'));
$PAGE->set_title(block_exacomp_get_string($page_identifier));

function block_exacomp_source_delete_get_subjects($source) {
	//$DB->set_debug(true);
	$subjects = block_exacomp\db_layer_whole_moodle::get()->get_subjects_for_source($source);
	
	return $subjects;
}

$subjects = block_exacomp_source_delete_get_subjects($source);

if ($action == 'delete_selected') {
	$json_data = block_exacomp\param::required_json('json_data', (object)array(
		'subjects' => [PARAM_INT],
		'topics' => [PARAM_INT],
		'descriptors' => [PARAM_INT],
		'examples' => [PARAM_INT]
	));
	
	$post_examples = array_combine($json_data->examples, $json_data->examples);
	$post_descriptors = array_combine($json_data->descriptors, $json_data->descriptors);
	$post_topics = array_combine($json_data->topics, $json_data->topics);
	$post_subjects = array_combine($json_data->subjects, $json_data->subjects);
	
	$delete_examples = array();
	$delete_descriptors = array();
	$delete_topics = array();
	$delete_subjects = array();
	
	// rechte hier nochmal pruefen!
	foreach ($subjects as $subject) {
		if (!empty($post_subjects[$subject->id]) /*&& $subject->can_delete*/) {
			$delete_subjects[$subject->id] = $subject->id;
		}
	
		foreach ($subject->topics as $topic) {
			if (!empty($post_topics[$topic->id]) /*&& $topic->can_delete*/) {
				$delete_topics[$topic->id] = $topic->id;
			}
			
			foreach($topic->descriptors as $descriptor){
				if (!empty($post_descriptors[$descriptor->id]) /*&& $descriptor->can_delete*/) {
					$delete_descriptors[$descriptor->id] = $descriptor->id;
				}
				
				foreach($descriptor->children as $child_descriptor){
					if (!empty($post_descriptors[$child_descriptor->id]) /*&& $child_descriptor->can_delete*/) {
						$delete_descriptors[$child_descriptor->id] = $child_descriptor->id;
					}
					
					foreach ($child_descriptor->examples as $example){
						if (!empty($post_examples[$example->id]) /*&& $example->can_delete*/) {
							$delete_examples[$example->id] = $example->id;
						}
					}
				}
	
				foreach ($descriptor->examples as $example){
					if (!empty($post_examples[$example->id]) /*&& $example->can_delete*/) {
						$delete_examples[$example->id] = $example->id;
					}
				}
			}
		}
	}
	if ($delete_examples) {
		// TODO auch filestorage loeschen
		$DB->delete_records_list(BLOCK_EXACOMP_DB_EXAMPLES, 'id', $delete_examples);
	}
	if ($delete_descriptors) {
		$DB->delete_records_list(BLOCK_EXACOMP_DB_DESCRIPTORS, 'id', $delete_descriptors);
	}
	if ($delete_topics) {
		$DB->delete_records_list(BLOCK_EXACOMP_DB_TOPICS, 'id', $delete_topics);
	}
	if ($delete_subjects) {
		$DB->delete_records_list(BLOCK_EXACOMP_DB_SUBJECTS, 'id', $delete_subjects);
	}
	
	block_exacomp\data::normalize_database();
	
	redirect($CFG->wwwroot.'/blocks/exacomp/import.php?courseid='.$courseid);
	exit;
} else if ($action == 'select') {
	
	// build breadcrumbs navigation
	$coursenode = $PAGE->navigation->find($courseid, navigation_node::TYPE_COURSE);
	$blocknode = $coursenode->add(block_exacomp_get_string('blocktitle'));
	$pagenode = $blocknode->add(block_exacomp_get_string($page_identifier), $PAGE->url);
	$pagenode->make_active();
	
	echo $output->header($course_context,$courseid, 'tab_admin_settings');
	echo $OUTPUT->tabtree(block_exacomp_build_navigation_tabs_admin_settings($courseid), $page_identifier);
	
	echo $output->descriptor_selection_source_delete($source, $subjects);
	
	echo $output->footer();
} else {
	print_error("wrong action '$action'");
}
