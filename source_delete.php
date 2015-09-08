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
require_once dirname(__FILE__)."/lib/xmllib.php";

$courseid = required_param('courseid', PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_simplehtml', $courseid);
}

require_login($course);

$course_context = context_course::instance($courseid);

require_capability('block/exacomp:admin', context_system::instance());

$action = required_param('action', PARAM_ALPHANUMEXT);
$source = required_param('source', PARAM_INT);

$output = $PAGE->get_renderer('block_exacomp');

/* PAGE IDENTIFIER - MUST BE CHANGED. Please use string identifier from lang file */
$page_identifier = 'tab_admin_import';

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/source_delete.php', array('courseid' => $courseid, 'source' => $source, 'action' => 'select'));
$PAGE->set_heading(get_string('pluginname', 'block_exacomp'));
$PAGE->set_title(get_string($page_identifier, 'block_exacomp'));

function block_exacomp_source_delete_get_subjects($source) {
    //$DB->set_debug(true);
    $subjects = block_exacomp_db_layer::get()->get_subjects();
    
    // $subjects = array_values($subjects);
    // $subjects = array($subjects[10]); // , $subjects[1]);
    
    // check delete
    foreach ($subjects as $subject) {
        $subject->can_delete = ($subject->source == $source);
    
        foreach ($subject->topics as $topic) {
            $topic->can_delete = ($topic->source == $source);
    
            foreach($topic->descriptors as $descriptor){
                $descriptor->can_delete = ($descriptor->source == $source);
    
                // child descriptors
                foreach($descriptor->children as $child_descriptor){
                    $child_descriptor->can_delete = ($child_descriptor->source == $source);
    
                    foreach ($child_descriptor->examples as $example){
                        $example->can_delete = ($example->source == $source);
    
                        if (!$example->can_delete)
                            $child_descriptor->can_delete = false;
                    }
    
                    if (!$child_descriptor->can_delete)
                        $descriptor->can_delete = false;
                }
    
                foreach ($descriptor->examples as $example){
                    $example->can_delete = ($example->source == $source);
    
                    if (!$example->can_delete)
                        $descriptor->can_delete = false;
                }
    
                if (!$descriptor->can_delete)
                    $topic->can_delete = false;
            }
    
            if (!$topic->can_delete)
                $subject->can_delete = false;
        }
    }
    
    return $subjects;
}

$subjects = block_exacomp_source_delete_get_subjects($source);

if ($action == 'delete_selected') {
    $examples = block_exacomp_clean_array(isset($_REQUEST['examples'])?$_REQUEST['examples']:array(), array(PARAM_INT=>PARAM_INT));
    $descriptors = block_exacomp_clean_array(isset($_REQUEST['descriptors'])?$_REQUEST['descriptors']:array(), array(PARAM_INT=>PARAM_INT));
    $topics = block_exacomp_clean_array(isset($_REQUEST['topics'])?$_REQUEST['topics']:array(), array(PARAM_INT=>PARAM_INT));
    $subjects = block_exacomp_clean_array(isset($_REQUEST['subjects'])?$_REQUEST['subjects']:array(), array(PARAM_INT=>PARAM_INT));
    
    $delete_examples = array();
    $delete_descriptors = array();
    $delete_topics = array();
    $delete_subjects = array();
    
    // rechte hier nochmal pruefen!
    foreach ($subjects as $subject) {
        if (!empty($subjects[$subject->id]) && $subject->can_delete) {
            $delete_subjects[$subject->id] = $subject->id;
        }
    
        foreach ($subject->topics as $topic) {
            if (!empty($topics[$topic->id]) && $topic->can_delete) {
                $delete_topics[$topic->id] = $topic->id;
            }
            
            foreach($topic->descriptors as $descriptor){
                if (!empty($descriptors[$descriptor->id]) && $descriptor->can_delete) {
                    $delete_descriptors[$descriptor->id] = $descriptor->id;
                }
                
                foreach($descriptor->children as $child_descriptor){
                    if (!empty($descriptors[$descriptor->id]) && $descriptor->can_delete) {
                        $delete_descriptors[$descriptor->id] = $descriptor->id;
                    }
                    
                    foreach ($child_descriptor->examples as $example){
                        if (!empty($examples[$example->id]) && $example->can_delete) {
                            $delete_examples[$example->id] = $example->id;
                        }
                    }
                }
    
                foreach ($descriptor->examples as $example){
                    if (!empty($examples[$example->id]) && $example->can_delete) {
                        $delete_examples[$example->id] = $example->id;
                    }
                }
            }
        }
    }
    
    if ($delete_examples) {
        // TODO auch filestorage loeschen
        $DB->delete_records_list(block_exacomp::DB_EXAMPLES, 'id', $delete_examples);
    }
    if ($delete_descriptors) {
        $DB->delete_records_list(block_exacomp::DB_DESCRIPTORS, 'id', $delete_descriptors);
    }
    if ($delete_topics) {
        $DB->delete_records_list(block_exacomp::DB_TOPICS, 'id', $delete_topics);
    }
    if ($delete_subjects) {
        $DB->delete_records_list(block_exacomp::DB_SUBJECTS, 'id', $delete_subjects);
    }
    
    block_exacomp_data::normalize_database();
    
    redirect($PAGE->url);
    exit;
} else if ($action == 'select') {
    
    // build breadcrumbs navigation
    $coursenode = $PAGE->navigation->find($courseid, navigation_node::TYPE_COURSE);
    $blocknode = $coursenode->add(get_string('pluginname','block_exacomp'));
    $pagenode = $blocknode->add(get_string($page_identifier,'block_exacomp'), $PAGE->url);
    $pagenode->make_active();
    
    echo $output->header();
    echo $OUTPUT->tabtree(block_exacomp_build_navigation_tabs($course_context,$courseid), $page_identifier);
    
    echo $output->print_descriptor_selection_source_delete($source, $subjects);
    
    echo $output->footer();
} else {
    print_error("wrong action '$action'");
}
