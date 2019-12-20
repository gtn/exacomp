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

global $DB, $OUTPUT, $PAGE, $USER;

$courseid = required_param('courseid', PARAM_INT);
$embedded = optional_param('embedded', true, PARAM_BOOL);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_exacomp', $courseid);
}

require_login($course);

$context = context_course::instance($courseid);
$output = block_exacomp_get_renderer();

$id = optional_param('id', 0, PARAM_INT);
$item = $id ? \block_exacomp\subject::get($id) : null;

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/subject.php', array('courseid' => $courseid));
$PAGE->set_heading($item ? block_exacomp_trans(['de:Kompetenzraster bearbeiten', 'en:Modify competence grid']) : block_exacomp_trans(['de:Neuen Kompetenzraster anlegen', 'en:Create new competence grid']));
if ($embedded)
	$PAGE->set_pagelayout('embedded');

// build tab navigation & print header

/* CONTENT REGION */

block_exacomp_require_teacher($context);
if ($item) {
	block_exacomp_require_item_capability(BLOCK_EXACOMP_CAP_MODIFY, $item);
}

// TODO: check permissions, check if item is BLOCK_EXACOMP_DATA_SOURCE_CUSTOM

if ($item && optional_param('action', '', PARAM_TEXT) == 'delete') {
	block_exacomp_require_item_capability(BLOCK_EXACOMP_CAP_DELETE, $item);
	//$item->delete();
    block_exacomp_delete_tree($courseid, 'subject', $item->id);
    $forward = optional_param('forward', '', PARAM_URL);
    if ($forward) {
        echo $output->popup_close_and_forward($forward);
    } else {
        echo $output->popup_close_and_reload();
    }
	exit;
}

require_once $CFG->libdir . '/formslib.php';

class block_exacomp_local_item_form extends moodleform {

	function definition() {
	    global $COURSE, $USER, $DB, $PAGE;
		

		$mform = & $this->_form;
		
        //Subject
		$mform->addElement('html', '<h2> '.block_exacomp_get_string("tab_competence_overview").' </h2>');
		$mform->addElement('text', 'title', block_exacomp_get_string('name'), 'maxlength="255" size="60"');
		$mform->setType('title', PARAM_TEXT);
		$mform->addRule('title', block_exacomp_get_string("titlenotemtpy"), 'required', null, 'client');

		$courseid_schooltype = block_exacomp_is_skillsmanagement() ? $COURSE->id : 0;
		$schooltypes = block_exacomp_get_schooltypes_by_course($courseid_schooltype);

		$schooltypes = array_map(function($st) { return $st->title; }, $schooltypes);

		//$mform->addElement('select', 'stid', block_exacomp_get_string('tab_teacher_settings_selection_st'), $schooltypes);
		//$mform->addElement('html', '<br/>');
		
		//Topic
		$mform->addElement('html', '<h2> '.block_exacomp_get_string("topic").' </h2>');
		$mform->addElement('html', '<p> '.block_exacomp_get_string("topic_description").'</p>');
		$mform->addElement('text', 'topicTitle', block_exacomp_get_string('name'), 'maxlength="255" size="60"');
		$mform->setType('topicTitle', PARAM_TEXT);
		$mform->addRule('topicTitle', block_exacomp_get_string("titlenotemtpy"), 'required', null, 'client');
		
		$mform->addElement('text', 'numb', block_exacomp_trans('de:Nummer'), 'maxlength="4" size="4"');
		$mform->setType('numb', PARAM_INT);
		$mform->addElement('html', '<br/>');
		
		//Niveau
		$mform->addElement('html', '<h2> '.block_exacomp_get_string("niveau").' </h2>');
		$mform->addElement('html', '<p> '.block_exacomp_get_string("niveau_description").' </p>');
		$mform->addElement('text', 'niveau_title', block_exacomp_get_string('name'), 'maxlength="255" size="60"');
		$mform->setType('niveau_title', PARAM_TEXT);
		$mform->addRule('niveau_title', block_exacomp_get_string("titlenotemtpy"), 'required', null, 'client');
		
		
		$mform->addElement('text', 'niveau_numb', block_exacomp_get_string('numb'), 'maxlength="255" size="60"');
		$mform->setType('niveau_numb', PARAM_TEXT);
		
		$mform->addElement('html', '<h2> '.block_exacomp_get_string("descriptors").' </h2>');
		$mform->addElement('text', 'descriptor_title', block_exacomp_get_string('descriptor_description'), 'maxlength="255" size="60"');
		$mform->setType('descriptor_title', PARAM_TEXT);
		$mform->addRule('descriptor_title', block_exacomp_get_string("titlenotemtpy"), 'required', null, 'client');
		
		$this->add_action_buttons(false);
	}
}

$form = new block_exacomp_local_item_form($_SERVER['REQUEST_URI']);
if ($item) $form->set_data($item);

if($formdata = $form->get_data()) {
	
	$new = new stdClass();
	$new->title = $formdata->title;
	$new->titleshort = substr($formdata->title, 0, 1);
	
	$newTopic = new stdClass();
	$newTopic->title = $formdata->topicTitle;
	$newTopic->numb = $formdata->numb;
	
	if (!$item) {
	    //Subject
		$new->source = BLOCK_EXACOMP_DATA_SOURCE_CUSTOM;
		$new->sourceid = 0;
		
		if(!$DB->record_exists(BLOCK_EXACOMP_DB_EDULEVELS, array('source' => BLOCK_EXACOMP_DATA_SOURCE_CUSTOM))){
		    $newEL = new stdClass();
		    $newEL->title = "Mein Edulevel";
		    $newEL->sourceid = 0;
		    $newEL->source = BLOCK_EXACOMP_DATA_SOURCE_CUSTOM;
		    
		    $id = $DB->insert_record(BLOCK_EXACOMP_DB_EDULEVELS, $newEL);
		    $new->stid = $id;

		    $newST = new stdClass();
		    $newST->elid = $id;
		    $newST->title = "Mein Schultyp";
		    $newST->sourceid = 0;
		    $newST->source = BLOCK_EXACOMP_DATA_SOURCE_CUSTOM;
		    
		    $DB->insert_record(BLOCK_EXACOMP_DB_SCHOOLTYPES, $newST);
		}
	
		$new->id = $DB->insert_record(BLOCK_EXACOMP_DB_SUBJECTS, $new);

		
		//Topic
		
		$newTopic->source = BLOCK_EXACOMP_DATA_SOURCE_CUSTOM;
		$newTopic->sourceid = 0;
		$newTopic->subjid = $new->id;
		    
		$topicid = $DB->insert_record(BLOCK_EXACOMP_DB_TOPICS, $newTopic);
		    
		    // add topic to course
		    $DB->insert_record(BLOCK_EXACOMP_DB_COURSETOPICS, array(
		        'courseid' => $courseid,
		        'topicid' => $topicid
		    ));
		    
		    block_exacomp_set_topic_visibility($topicid, $courseid, 1, 0);
		    $subjectid = $newTopic->subjid;
		    
		 //Niveau
		        $niveau = new stdClass;
		        $niveau->sorting = $DB->get_field(BLOCK_EXACOMP_DB_NIVEAUS, 'MAX(sorting)', array()) + 1;
		        $niveau->source = BLOCK_EXACOMP_EXAMPLE_SOURCE_TEACHER;
		        $niveau->title = $formdata->niveau_title;
		        $niveau->numb = $formdata->niveau_numb;
		        $niveau->id = $DB->insert_record(BLOCK_EXACOMP_DB_NIVEAUS, $niveau);
		    

		            \block_exacomp\descriptor::insertInCourse($courseid, array(
		                'title' => $formdata->descriptor_title,
		                'topicid' => $topicid,
		                'niveauid' => $niveau->id
		            ));
		    


	} else {
		$item->update($new);
		$subjectid = $item->id;
	}
	
	echo $output->popup_close_and_forward($CFG->wwwroot."/blocks/exacomp/assign_competencies.php?courseid=".$courseid."&editmode=1&subjectid={$subjectid}");

	exit;
}

echo $output->header($context, $courseid, '', false);

if ($item) {
	// TODO: also check $item->can_delete
	echo '<div style="position: absolute; top: 40px; right: 20px;">';
	echo '<a href="'.$_SERVER['REQUEST_URI'].'&action=delete" onclick="return confirm(\''.block_exacomp_get_string('really_delete').'\');">';
	echo block_exacomp_get_string('delete');
	echo '</a></div>';
}

$form->display();

echo $output->footer();
