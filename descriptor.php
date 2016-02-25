<?php
// This file is part of Exabis Competencies
//
// (c) 2016 exabis internet solutions <info@exabis.at>
//
// Exabis Comeptencies is free software: you can redistribute it and/or modify
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

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_exacomp', $courseid);
}

require_login($course);

$context = context_course::instance($courseid);
$output = block_exacomp_get_renderer();

$id = optional_param('id', 0, PARAM_INT);
$item = $id ? \block_exacomp\descriptor::get($id) : null;

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/descriptor.php', array('courseid' => $courseid));
$PAGE->set_heading($item ? \block_exacomp\trans('competency_edit', ['de:Kompetenz bearbeiten', 'en:Edit competency']) : \block_exacomp\trans('competency_add', ['de:Neue Kompetenz anlegen', 'en:Create new competency']));
$PAGE->set_pagelayout('embedded');

// build tab navigation & print header

/* CONTENT REGION */

block_exacomp_require_teacher($context);
if ($item) {
	block_exacomp\require_item_capability(block_exacomp\CAP_MODIFY, $item);
}

// TODO: check permissions, check if item is \block_exacomp\DATA_SOURCE_CUSTOM

if ($item && optional_param('action', '', PARAM_TEXT) == 'delete') {
	block_exacomp\require_item_capability(block_exacomp\CAP_DELETE, $item);
	$item->delete();

	echo $output->popup_close_and_reload();
	exit;
}

require_once $CFG->libdir . '/formslib.php';

class block_exacomp_local_item_form extends moodleform {

	function definition() {
		global $CFG, $USER, $DB, $PAGE;

		$output = block_exacomp_get_renderer();

		$mform = & $this->_form;

		$mform->addElement('text', 'title', \block_exacomp\get_string('name'), 'maxlength="255" size="60"');
		$mform->setType('title', PARAM_TEXT);
		$mform->addRule('title', \block_exacomp\get_string("titlenotemtpy"), 'required', null, 'client');

		$mform->addElement('selectgroups', 'niveauid', \block_exacomp\get_string('niveau'), block_exacomp\get_select_niveau_items());

		$element = $mform->addElement('select', 'categories', \block_exacomp\get_string('categories'), $DB->get_records_menu(\block_exacomp\DB_CATEGORIES, null, 'title', 'id, title'));
		$element->setMultiple(true);
		
		$this->add_action_buttons(false);
	}
}

$form = new block_exacomp_local_item_form($_SERVER['REQUEST_URI']);

if ($item) {
	$data = $item->get_data();
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
		$new->source = \block_exacomp\DATA_SOURCE_CUSTOM;
		$new->sourceid = 0;
		$new->subjid = required_param('subjectid', PARAM_INT);
		
		$new->id = $DB->insert_record(\block_exacomp\DB_TOPICS, $new);
		
		// add topic to course
		$DB->insert_record(\block_exacomp\DB_COURSETOPICS, array(
			'courseid' => $courseid,
			'topicid' => $new->id
		));
	} else {
		$item->update($new);
	}
	
	$item->store_categories(!empty($formdata->categories) ?: array());
	
	echo $output->popup_close_and_reload();
	exit;
}

echo $output->header($context, $courseid, '', false);

/*
if ($item) {
	// TODO: also check $item->can_delete
	echo '<div style="position: absolute; top: 40px; right: 20px;">';
	echo '<a href="'.$_SERVER['REQUEST_URI'].'&action=delete" onclick="return confirm(\''.\block_exacomp\trans('de:Wirklich löschen?').'\');">';
	echo \block_exacomp\get_string('delete');
	echo '</a></div>';
}
*/

$form->display();

echo $output->footer();
