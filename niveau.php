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
$output = block_exacomp_get_renderer();

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/topic.php', array('courseid' => $courseid));
$PAGE->set_heading(\block_exacomp\trans('de:Lernfortschritt hinzufügen'));
$PAGE->set_pagelayout('embedded');

// build tab navigation & print header

/* CONTENT REGION */

block_exacomp_require_teacher($context);

// TODO: check permissions, check if item is block_exacomp::DATA_SOURCE_CUSTOM

require_once $CFG->libdir . '/formslib.php';

class block_exacomp_local_item_form extends moodleform {

    function definition() {
        global $CFG, $USER, $DB, $PAGE;

        $output = block_exacomp_get_renderer();

        $mform = & $this->_form;
        
        $radioarray=array();
        if ($this->_customdata['niveaus']) {
            // disable if no niveaus
            $radioarray[] =& $mform->createElement('radio', 'niveau_type', '', \block_exacomp\trans('de:vorhandener Lernfortschritt'), 'existing');
        }
        $radioarray[] =& $mform->createElement('radio', 'niveau_type', '', \block_exacomp\trans('de:neuer Lernfortschritt'), 'new');
        $mform->addGroup($radioarray, 'radioar', '', array(' '), false);
        
        $mform->addElement('text', 'niveau_title', \block_exacomp\trans('de:Name des Lernfortschritts'), 'maxlength="255" size="60"');
        $mform->setType('niveau_title', PARAM_TEXT);
        // $mform->addRule('niveau_title', \block_exacomp\get_string("titlenotemtpy"), 'required', null, 'client');
        
        $mform->addElement('select', 'niveau_id', \block_exacomp\get_string('niveau'), $this->_customdata['niveaus']);
        
        $mform->addElement('static', 'niveau_descriptor_description', \block_exacomp\trans('de:Bitte weisen sie diesem Lernfotschritt eine Kompetenz zu:'));
        
        $radioarray=array();
        if ($this->_customdata['descriptors']) {
            // disable if no descriptors
            $radioarray[] =& $mform->createElement('radio', 'descriptor_type', '', \block_exacomp\trans('de:vorhandene Kompetenz'), 'existing');
        }
        $radioarray[] =& $mform->createElement('radio', 'descriptor_type', '', \block_exacomp\trans('de:neue Kompetenz'), 'new');
        $mform->addGroup($radioarray, 'radioar', '', array(' '), false);

        $mform->addElement('text', 'descriptor_title', \block_exacomp\trans('de:Name der Kompetenz'), 'maxlength="255" size="60"');
        $mform->setType('descriptor_title', PARAM_TEXT);
        // $mform->addRule('descriptor_title', \block_exacomp\get_string("titlenotemtpy"), 'required', null, 'client');
        
        $mform->addElement('select', 'descriptor_id', \block_exacomp\get_string('descriptor'), $this->_customdata['descriptors']);
        
        $this->add_action_buttons(false);
    }
}

$topic = block_exacomp_topic::get(required_param('topicid', PARAM_INT));

$descriptors = array_map(function($d){ return $d->title; }, $topic->descriptors);
$niveaus = $DB->get_records_menu(block_exacomp::DB_NIVEAUS, null, 'sorting', 'id, title');
$form = new block_exacomp_local_item_form($_SERVER['REQUEST_URI'], array(
    'descriptors' => $descriptors,
    'niveaus' => $niveaus,
));

$data = new stdClass;
$data->descriptor_type = $descriptors ? 'existing' : 'new';
$data->niveau_type = $niveaus ? 'existing' : 'new';
$form->set_data($data);

if($formdata = $form->get_data()) {
    
    if ($formdata->niveau_type == 'new') {
        $niveau = new stdClass;
        $niveau->sorting = $DB->get_field(block_exacomp::DB_NIVEAUS, 'MAX(sorting)', array()) + 1;
        $niveau->title = $formdata->niveau_title;
        $niveau->id = $DB->insert_record(block_exacomp::DB_NIVEAUS, $niveau);
    } else {
        $niveau = $DB->get_record(block_exacomp::DB_NIVEAUS, array('id' => $formdata->niveau_id), '*', MUST_EXIST);
    }
    
    if ($formdata->descriptor_type == 'new') {
        block_exacomp_descriptor::insertInCourse($courseid, array(
            'title' => $formdata->descriptor_title,
            'topicid' => $topic->id,
            'niveauid' => $niveau->id
        ));
    } else {
        $descriptor = block_exacomp_descriptor::get($formdata->descriptor_id, MUST_EXIST);
        $descriptor->update(array('niveauid' => $niveau->id));
    }

    /*
    $mm = new stdClass();
    $mm->descrid = $formdata->descriptor_id;
    $mm->catid = $formdata->category;
    
    if (!$item) {
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
    */
    
    echo $output->popup_close_and_reload();
    exit;
}

echo $output->header($context, $courseid, '', false);

$form->display();

echo $output->footer();
