<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

require __DIR__ . '/inc.php';
require_once __DIR__ . "/classes/data.php";

$courseid = required_param('courseid', PARAM_INT);

global $DB, $OUTPUT, $PAGE, $CFG;

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_simplehtml', $courseid);
}

block_exacomp_require_login($course);

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

function block_exacomp_source_delete_get_subjects($source, $subjects_preselection = -1) {
    $subjects = block_exacomp\db_layer_whole_moodle::get()->get_subjects_for_source($source, $subjects_preselection);
    return $subjects;
}

// Showing all the subjects of a source can become a huge task on large systems ==> Show only the subject, but not the topics etc in the first step
function block_exacomp_source_delete_get_subjects_for_preselection($source) {
    $subjects = block_exacomp\db_layer_whole_moodle::get()->get_subjects_preselection_for_source($source);
    return $subjects;
}

if ($action == 'delete_selected') {
    require_sesskey();
    $json_data = block_exacomp\param::required_json('json_data', (object)array(
        'subjects' => [PARAM_INT],
        'topics' => [PARAM_INT],
        'descriptors' => [PARAM_INT],
        'examples' => [PARAM_INT],
    ));

    $post_examples = array_combine($json_data->examples, $json_data->examples);
    $post_descriptors = array_combine($json_data->descriptors, $json_data->descriptors);
    $post_topics = array_combine($json_data->topics, $json_data->topics);
    $post_subjects = array_combine($json_data->subjects, $json_data->subjects);

    // 2022.01.20 RW this is not needed anymore. The warnings are displayed before anyways, but deletion is still allowed and will never be stopped here.

    // rechte hier nochmal pruefen!
    //    $delete_examples = array();
    //    $delete_descriptors = array();
    //    $delete_topics = array();
    //    $delete_subjects = array();
    //	foreach ($subjects as $subject) {
    //		if (!empty($post_subjects[$subject->id]) /*&& $subject->can_delete*/) {
    //			$delete_subjects[$subject->id] = $subject->id;
    //		}
    //
    //		foreach ($subject->topics as $topic) {
    //			if (!empty($post_topics[$topic->id]) /*&& $topic->can_delete*/) {
    //				$delete_topics[$topic->id] = $topic->id;
    //			}
    //
    //			foreach($topic->descriptors as $descriptor){
    //				if (!empty($post_descriptors[$descriptor->id]) /*&& $descriptor->can_delete*/) {
    //					$delete_descriptors[$descriptor->id] = $descriptor->id;
    //				}
    //
    //				foreach($descriptor->children as $child_descriptor){
    //					if (!empty($post_descriptors[$child_descriptor->id]) /*&& $child_descriptor->can_delete*/) {
    //						$delete_descriptors[$child_descriptor->id] = $child_descriptor->id;
    //					}
    //
    //					foreach ($child_descriptor->examples as $example){
    //						if (!empty($post_examples[$example->id]) /*&& $example->can_delete*/) {
    //							$delete_examples[$example->id] = $example->id;
    //						}
    //					}
    //				}
    //
    //				foreach ($descriptor->examples as $example){
    //					if (!empty($post_examples[$example->id]) /*&& $example->can_delete*/) {
    //						$delete_examples[$example->id] = $example->id;
    //					}
    //				}
    //			}
    //		}
    //	}

    if ($post_examples) {
        // TODO auch filestorage loeschen
        $DB->delete_records_list(BLOCK_EXACOMP_DB_EXAMPLES, 'id', $post_examples);
        // BLOCK_EXACOMP_DB_EXAMPTAX, BLOCK_EXACOMP_DB_ITEM_MM, BLOCK_EXACOMP_DB_EXAMPLEEVAL etc will/should be handles when normalizing the database
        // Loop probably a loop will be needed for clearing the filestorage
        //        $fs = get_file_storage();
        //        $fs->delete_area_files(\context_system::instance()->id, 'block_exacomp', 'example_task', $exampleid);
        //        $fs->delete_area_files(\context_system::instance()->id, 'block_exacomp', 'example_solution', $exampleid);
        //        $fs->delete_area_files(\context_system::instance()->id, 'block_exacomp', 'example_completefile', $exampleid);

    }
    if ($post_descriptors) {
        $DB->delete_records_list(BLOCK_EXACOMP_DB_DESCRIPTORS, 'id', $post_descriptors);
    }
    if ($post_topics) {
        $DB->delete_records_list(BLOCK_EXACOMP_DB_TOPICS, 'id', $post_topics);
    }
    if ($post_subjects) {
        $DB->delete_records_list(BLOCK_EXACOMP_DB_SUBJECTS, 'id', $post_subjects);
    }

    //block_exacomp\data::normalize_database(); // not needed here, since it is done in cronjobs
    //    $time_elapsed_secs = microtime(true) - $start;
    redirect($CFG->wwwroot . '/blocks/exacomp/import.php?courseid=' . $courseid);
    exit;
} else if ($action == 'select') {
    // deprecated... this shows ALL subjects at once... Would be useful for small sources. For sources with a huge amount of subjects, preselection is needed.
    $subjects = block_exacomp_source_delete_get_subjects($source);
    // build breadcrumbs navigation
    $coursenode = $PAGE->navigation->find($courseid, navigation_node::TYPE_COURSE);
    $blocknode = $coursenode->add(block_exacomp_get_string('blocktitle'));
    $pagenode = $blocknode->add(block_exacomp_get_string($page_identifier), $PAGE->url);
    $pagenode->make_active();

    echo $output->header($course_context, $courseid, 'tab_admin_settings');
    echo $OUTPUT->tabtree(block_exacomp_build_navigation_tabs_admin_settings($courseid), $page_identifier);

    echo $output->descriptor_selection_source_delete($source, $subjects);

    echo $output->footer();
} else if ($action == 'preselect_subjects') {
    // The user can select from which subject they want to delete. This can be only one subject, or several, or all.
    // If the user chooses all in a huge setting, this could lead to performance issues.
    $subjects = block_exacomp_source_delete_get_subjects_for_preselection($source);
    // build breadcrumbs navigation
    $coursenode = $PAGE->navigation->find($courseid, navigation_node::TYPE_COURSE);
    $blocknode = $coursenode->add(block_exacomp_get_string('blocktitle'));
    $pagenode = $blocknode->add(block_exacomp_get_string($page_identifier), $PAGE->url);
    $pagenode->make_active();

    echo $output->header($course_context, $courseid, 'tab_admin_settings');
    echo $OUTPUT->tabtree(block_exacomp_build_navigation_tabs_admin_settings($courseid), $page_identifier);

    echo $output->subject_preselection_source_delete($source, $subjects, $courseid);

    echo $output->footer();
} else if ($action == 'select_from_preselection') {
    // The user has selected 1 or several subjects from a source. Now the user can choose the details of what exactly they want to delete.
    $json_data = block_exacomp\param::required_json('json_data', (object)array(
        'subjects' => [PARAM_INT],
    ));
    $preselected_subjects = array_combine($json_data->subjects, $json_data->subjects);
    // Now we have the preselected subjects that the user wants to see in detail. The following code is very similar to the old 'select' code, where all subjects of a source have been shown in detail.
    $subjects = block_exacomp_source_delete_get_subjects($source, $preselected_subjects);
    // build breadcrumbs navigation
    $coursenode = $PAGE->navigation->find($courseid, navigation_node::TYPE_COURSE);
    $blocknode = $coursenode->add(block_exacomp_get_string('blocktitle'));
    $pagenode = $blocknode->add(block_exacomp_get_string($page_identifier), $PAGE->url);
    $pagenode->make_active();

    echo $output->header($course_context, $courseid, 'tab_admin_settings');
    echo $OUTPUT->tabtree(block_exacomp_build_navigation_tabs_admin_settings($courseid), $page_identifier);

    echo $output->descriptor_selection_source_delete($source, $subjects, $preselected_subjects);

    echo $output->footer();

} else {
    print_error("wrong action '$action'");
}
