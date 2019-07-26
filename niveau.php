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

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_exacomp', $courseid);
}

require_login($course);

$context = context_course::instance($courseid);
$output = block_exacomp_get_renderer();

$PAGE->set_url('/blocks/exacomp/niveau.php', array('courseid' => $courseid));
$PAGE->set_heading(block_exacomp_get_string('add_niveau'));
$PAGE->set_pagelayout('embedded');

// build tab navigation & print header

/* CONTENT REGION */

block_exacomp_require_teacher($context);

// TODO: check permissions, check if item is BLOCK_EXACOMP_DATA_SOURCE_CUSTOM

require_once $CFG->libdir . '/formslib.php';

class block_exacomp_local_item_form extends moodleform {

	function definition() {
		global $CFG, $USER, $DB, $PAGE;

		$output = block_exacomp_get_renderer();

		$mform = & $this->_form;

		//$niveaus = block_exacomp_get_select_niveau_items();
		$niveaus = null;

		/*$radioarray=array();
		if ($niveaus) {
			$radioarray[] =& $mform->createElement('radio', 'niveau_type', '', block_exacomp_trans(['de:vorhandener Lernfortschritt', 'en:Existing niveau']), 'existing');
		}
		$radioarray[] =& $mform->createElement('radio', 'niveau_type', '', block_exacomp_get_string('new_niveau'), 'new');
		$mform->addGroup($radioarray, 'radioar', '', array(' '), false);*/

		$mform->addElement('text', 'niveau_title', block_exacomp_get_string('name'), 'maxlength="255" size="60"');
		$mform->setType('niveau_title', PARAM_TEXT);
		// $mform->addRule('niveau_title', block_exacomp_get_string("titlenotemtpy"), 'required', null, 'client');

		$mform->addElement('text', 'niveau_numb', block_exacomp_get_string('numb'), 'maxlength="255" size="60"');
		$mform->setType('niveau_numb', PARAM_TEXT);

		//$mform->addElement('selectgroups', 'niveau_id', block_exacomp_get_string('niveau'), $niveaus);

		$mform->addElement('static', 'niveau_descriptor_description', block_exacomp_trans(['de:Bitte weisen Sie diesem Lernfortschritt eine Kompetenz zu', 'en:Please assign a competence to the new niveau']).':');

		$radioarray = array();
		if ($this->_customdata['descriptors']) {
			// disable if no descriptors
			$radioarray[] =& $mform->createElement('radio', 'descriptor_type', '', block_exacomp_trans(['de:vorhandene Kompetenz', 'en:Existing competence']), 'existing');
		}
		$radioarray[] =& $mform->createElement('radio', 'descriptor_type', '', block_exacomp_trans(['de:neue Kompetenz', 'en:New competence']), 'new');
		$mform->addGroup($radioarray, 'radioar', '', array(' '), false);

		$mform->addElement('text', 'descriptor_title', block_exacomp_get_string('name'), 'maxlength="255" size="60"');
		$mform->setType('descriptor_title', PARAM_TEXT);
		// $mform->addRule('descriptor_title', block_exacomp_get_string("titlenotemtpy"), 'required', null, 'client');

		$mform->addElement('select', 'descriptor_id', block_exacomp_get_string('descriptor'), $this->_customdata['descriptors']);

		$this->add_action_buttons(false);
	}
}

class block_exacomp_local_item_edit_form extends moodleform {

	function definition() {
		global $CFG, $USER, $DB, $PAGE, $COURSE;

		$output = block_exacomp_get_renderer();

		$mform = & $this->_form;
        $niveauid = optional_param('id', 0, PARAM_INT);

		$mform->addElement('text', 'title', block_exacomp_get_string('name'), 'maxlength="255" size="60"');
		$mform->setType('title', PARAM_TEXT);

		$mform->addElement('text', 'numb', block_exacomp_get_string('numb'), 'maxlength="255" size="60"');
		$mform->setType('numb', PARAM_TEXT);

        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('savechanges'));
        $deleteUrl = html_entity_decode(new block_exacomp\url('niveau.php', ['courseid' => $COURSE->id, 'id' => $niveauid, 'action' => 'delete', 'forward' => optional_param('backurl' ,'', PARAM_URL).'&editmode=1']));
        //$buttonarray[] = &$mform->createElement('button', 'delete', get_string('delete'));
        $buttonarray[] = &$mform->createElement('static', '', '', '<a href="#" onClick="if (confirm(\''.block_exacomp_get_string('really_delete').'\')) { window.location.href = \''.$deleteUrl.'\'; return false;} else {return false;};" class="btn btn-danger">'.get_string('delete').'</a>');
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
		//$this->add_action_buttons(false);
	}
}

$id = optional_param('id', 0, PARAM_INT);
$item = $id ? \block_exacomp\niveau::get($id) : null;

if ($item) {
	// TODO: check if is local niveau
	// block_exacomp_require_item_capability(BLOCK_EXACOMP_CAP_MODIFY, $item);
}

if ($item && optional_param('action', '', PARAM_TEXT) == 'delete') {
	block_exacomp_require_item_capability(BLOCK_EXACOMP_CAP_DELETE, $item);
    //$item->delete();
    block_exacomp_delete_tree($courseid, 'niveau', $item->id);
    $PAGE->set_heading(block_exacomp_get_string('delete_niveau'));
    $forward = optional_param('forward', '', PARAM_URL);
    if ($forward) {
        echo $output->popup_close_and_forward($forward);
    } else {
        echo $output->popup_close_and_reload();
    }
	exit;
}


if (!$item) {
	$topic = \block_exacomp\topic::get(required_param('topicid', PARAM_INT));

	$descriptors = array_map(function($d){ return $d->title; }, $topic->descriptors);
	$form = new block_exacomp_local_item_form($_SERVER['REQUEST_URI'], array(
		'descriptors' => $descriptors
	));

	$data = new stdClass;
	$data->descriptor_type = $descriptors ? 'existing' : 'new';
	//$data->niveau_type = 'new'; //'existing';
	$form->set_data($data);

	if ($formdata = $form->get_data()) {

		//if ($formdata->niveau_type == 'new') {
		if (empty($formdata->niveau_id)) {
			$niveau = new stdClass;
			$niveau->sorting = $DB->get_field(BLOCK_EXACOMP_DB_NIVEAUS, 'MAX(sorting)', array()) + 1;
			$niveau->source = BLOCK_EXACOMP_EXAMPLE_SOURCE_TEACHER;
			$niveau->title = $formdata->niveau_title;
			$niveau->numb = $formdata->niveau_numb;
			$niveau->id = $DB->insert_record(BLOCK_EXACOMP_DB_NIVEAUS, $niveau);
		} else {
			$niveau = $DB->get_record(BLOCK_EXACOMP_DB_NIVEAUS, array('id' => $formdata->niveau_id));
		}

		if ($niveau) {
			if ($formdata->descriptor_type == 'new') {
				\block_exacomp\descriptor::insertInCourse($courseid, array(
					'title' => $formdata->descriptor_title,
					'topicid' => $topic->id,
					'niveauid' => $niveau->id
				));
			} else {
				$descriptor = \block_exacomp\descriptor::get($formdata->descriptor_id, MUST_EXIST);
				$descriptor->update(array('niveauid' => $niveau->id));
			}

			echo $output->popup_close_and_reload();
			exit;
		}
	}

	echo $output->header($context, $courseid, '', false);

	$form->display();

	echo $output->footer();
} else {
	$form = new block_exacomp_local_item_edit_form($_SERVER['REQUEST_URI']);
	if ($item) {
        $form->set_data($item);
    }

	if ($formdata = $form->get_data()) {

		$new = new stdClass();
		$new->title = $formdata->title;
		$new->numb = $formdata->numb;
		$item->update($new);

		echo $output->popup_close_and_reload();
		exit;
	}

	echo $output->header($context, $courseid, '', false);

	/*
	if ($item) {
		// TODO: also check $item->can_delete
		echo '<div style="position: absolute; top: 40px; right: 20px;">';
		echo '<a href="'.$_SERVER['REQUEST_URI'].'&action=delete" onclick="return confirm(\''.block_exacomp_trans('de:Wirklich löschen?').'\');">';
		echo block_exacomp_get_string('delete');
		echo '</a></div>';
	}
	*/

	$form->display();

	echo $output->footer();
}