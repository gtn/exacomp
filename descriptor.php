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

require_once __DIR__."/inc.php";

global $DB, $OUTPUT, $PAGE, $USER;

$courseid = required_param('courseid', PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_exacomp', $courseid);
}

require_login($course);

$context = context_course::instance($courseid);
$output = $PAGE->get_renderer('block_exacomp');

$id = optional_param('id', 0, PARAM_INT);
$item = $id ? block_exacomp_descriptor::get($id) : null;

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/descriptor.php', array('courseid' => $courseid));
$PAGE->set_heading(block_exacomp::t($item ? 'de:Kompetenz bearbeiten' : 'de:Neue Kompetenz anlegen'));
$PAGE->set_pagelayout('popup');

// build tab navigation & print header

/* CONTENT REGION */

block_exacomp_require_teacher($context);

// TODO: check permissions, check if item is block_exacomp::DATA_SOURCE_CUSTOM

if ($item && optional_param('action', '', PARAM_TEXT) == 'delete') {
    $item->delete();

    echo $output->header();
    echo $output->popup_close_and_reload();
    echo $output->footer();

    exit;
}

require_once $CFG->libdir . '/formslib.php';

class block_exacomp_local_item_form extends moodleform {

    function definition() {
        global $CFG, $USER, $DB, $version, $PAGE;

        $output = $PAGE->get_renderer('block_exacomp');

        $mform = & $this->_form;

        $mform->addElement('text', 'title', block_exacomp::get_string('name'), 'maxlength="255" size="60"');
        $mform->setType('title', PARAM_TEXT);
        $mform->addRule('title', block_exacomp::get_string("titlenotemtpy"), 'required', null, 'client');

        $mform->addElement('select', 'niveauid', block_exacomp::get_string('niveau'), array(''=>'')+$DB->get_records_menu(block_exacomp::DB_NIVEAUS, null, 'sorting', 'id, title'));

        $element = $mform->addElement('select', 'categories', block_exacomp::get_string('categories'), $DB->get_records_menu(block_exacomp::DB_CATEGORIES, null, 'title', 'id, title'));
        $element->setMultiple(true);
        
        $this->add_action_buttons(false);
    }
}

$form = new block_exacomp_local_item_form($_SERVER['REQUEST_URI']);

if ($item) {
    $data = $item->getData();
    // also load category ids for form
    $data->categories = $item->category_ids;
    $form->set_data($data);
}

if($formdata = $form->get_data()) {
    
    $new = new stdClass();
    $new->title = $formdata->title;
    $new->niveauid = $formdata->niveauid;
    
    if (!$item) {
        die('TODO');
        $new->source = block_exacomp::DATA_SOURCE_CUSTOM;
        $new->sourceid = 0;
        $new->subjid = required_param('subjectid', PARAM_INT);
        
        $new->id = $DB->insert_record(block_exacomp::DB_TOPICS, $new);
        
        // add topic to course
        $DB->insert_record(block_exacomp::DB_COURSETOPICS, array(
            'courseid' => $courseid,
            'topicid' => $new->id
        ));
    } else {
        $item->update($new);
    }
    
    $item->set_categories($formdata->categories);
    
    echo $output->header();
    echo $output->popup_close_and_reload();
    echo $output->footer();
    
	exit;
}

echo $output->header($context, $courseid, '', false);

if ($item) {
    // TODO: also check $item->can_delete
    echo '<div style="position: absolute; top: 40px; right: 20px;">';
    echo '<a href="'.$_SERVER['REQUEST_URI'].'&action=delete" onclick="return confirm(\''.block_exacomp::t('de:Wirklich löschen?').'\');">';
    echo block_exacomp::get_string('delete');
    echo '</a></div>';
}

$form->display();

echo $output->footer();
