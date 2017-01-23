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
if($embedded)
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
	$item->delete();

	echo $output->popup_close_and_reload();
	exit;
}

require_once $CFG->libdir . '/formslib.php';

class block_exacomp_local_item_form extends moodleform {

	function definition() {
		global $COURSE;

		$mform = & $this->_form;

		$mform->addElement('text', 'title', block_exacomp_get_string('name'), 'maxlength="255" size="60"');
		$mform->setType('title', PARAM_TEXT);
		$mform->addRule('title', block_exacomp_get_string("titlenotemtpy"), 'required', null, 'client');

		$courseid_schooltype = block_exacomp_is_skillsmanagement() ? $COURSE->id : 0;
		$schooltypes = block_exacomp_get_schooltypes_by_course($courseid_schooltype);

		$schooltypes = array_map(function($st) { return $st->title; }, $schooltypes);

		$mform->addElement('select', 'stid', block_exacomp_get_string('tab_teacher_settings_selection_st'), $schooltypes);
		
		$this->add_action_buttons(false);
	}
}

$form = new block_exacomp_local_item_form($_SERVER['REQUEST_URI']);
if ($item) $form->set_data($item);

if($formdata = $form->get_data()) {
	
	$new = new stdClass();
	$new->title = $formdata->title;
	$new->stid = $formdata->stid;
	$new->titleshort = substr($formdata->title, 0, 1);
	
	if (!$item) {
		$new->source = BLOCK_EXACOMP_DATA_SOURCE_CUSTOM;
		$new->sourceid = 0;
	
		$new->id = $DB->insert_record(BLOCK_EXACOMP_DB_SUBJECTS, $new);
		
		// add one dummy topic
		$topicid = $DB->insert_record(BLOCK_EXACOMP_DB_TOPICS, array(
			'title' => block_exacomp_trans(['de:Neuer Raster', 'en:New competence grid']),
			'subjid' => $new->id,
			'numb' => 1,
			'source' => BLOCK_EXACOMP_DATA_SOURCE_CUSTOM,
			'sourceid' => 0
		));
	
		// add dummy topic to course
		$DB->insert_record(BLOCK_EXACOMP_DB_COURSETOPICS, array(
			'courseid' => $courseid,
			'topicid' => $topicid
		));
		$subjectid = $new->id;

		block_exacomp_set_topic_visibility($topicid, $courseid, 1, 0);

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
	echo '<a href="'.$_SERVER['REQUEST_URI'].'&action=delete" onclick="return confirm(\''.block_exacomp_trans('de:Wirklich löschen?').'\');">';
	echo block_exacomp_get_string('delete');
	echo '</a></div>';
}

$form->display();

echo $output->footer();
