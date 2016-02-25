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

require_once $CFG->libdir . '/formslib.php';

class block_exacomp_example_submission_form extends moodleform {

	function definition() {
		global $CFG, $USER, $DB;

		$mform = & $this->_form;

		$exampleid = $this->_customdata['exampleid'];
		
		$exampleTitle = $DB->get_field('block_exacompexamples','title',array("id"=>$exampleid));
		$mform->addElement('header', 'general', get_string("example_submission_header", "block_exacomp", $exampleTitle));

		$mform->addElement('static', 'info', get_string('description'),
				get_string("example_submission_info", "block_exacomp", $exampleTitle));
		
		$mform->addElement('text', 'name', get_string("name_example","block_exacomp"), 'maxlength="255" size="60"');
		$mform->setType('name', PARAM_TEXT);
		$mform->setDefault('name', $exampleTitle);
		$mform->addRule('name', get_string("titlenotemtpy", "block_exacomp"), 'required', null, 'client');

		$mform->addElement('text', 'intro', get_string("moduleintro"), 'maxlength="255" size="60"');
		$mform->setType('intro', PARAM_TEXT);
		
		$mform->addElement('filepicker', 'file', get_string('file'), null, array('subdirs' => false, 'maxfiles' => 1));
		
		$mform->addElement('text', 'url', get_string("link","block_exacomp"), 'maxlength="255" size="60"');
		$mform->setType('url', PARAM_TEXT);
		
		$mform->addElement('hidden','exampleid');
		$mform->setType('exampleid', PARAM_INT);
		$mform->setDefault('exampleid',$exampleid);
		
		$this->add_action_buttons(false);
	}

	function validation($data, $files) {
		$errors = parent::validation($data, $files);
	
		$errors= array();
	
		if (!empty($data['url']) && filter_var($data['url'], FILTER_VALIDATE_URL) === FALSE &&
				filter_var("http://" . $data['url'], FILTER_VALIDATE_URL) === FALSE) {
			$errors['url'] = get_string('linkerr','block_exacomp');
		}
	
		if (empty($data['url']) && empty($data['file'])) {
			$errors['url'] = get_string('submissionmissing','block_exacomp');
			$errors['file'] = get_string('submissionmissing','block_exacomp');
		}
		return $errors;
	}
}
