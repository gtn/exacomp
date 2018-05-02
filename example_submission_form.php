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

require_once $CFG->libdir . '/formslib.php';

class block_exacomp_example_submission_form extends moodleform {

	function definition() {
		global $CFG, $USER, $DB;

		$mform = & $this->_form;

		$exampleid = $this->_customdata['exampleid'];
		
		$exampleTitle = $DB->get_field('block_exacompexamples','title',array("id"=>$exampleid));
		$mform->addElement('header', 'general', block_exacomp_get_string("example_submission_header", null, $exampleTitle));

		$mform->addElement('static', 'info', block_exacomp_get_string('description'),
				block_exacomp_get_string("example_submission_info", null, $exampleTitle));
		
		$mform->addElement('text', 'name', block_exacomp_get_string("name_example"), 'maxlength="255" size="60"');
		$mform->setType('name', PARAM_TEXT);
		$mform->setDefault('name', $exampleTitle);
		$mform->addRule('name', block_exacomp_get_string("titlenotemtpy"), 'required', null, 'client');

		$mform->addElement('text', 'intro', block_exacomp_get_string("moduleintro"), 'maxlength="255" size="60"');
		$mform->setType('intro', PARAM_TEXT);
		
		$mform->addElement('filepicker', 'file', block_exacomp_get_string('file'), null, array('subdirs' => false, 'maxfiles' => 1));
		
		$mform->addElement('text', 'url', block_exacomp_get_string("link"), 'maxlength="255" size="60"');
		$mform->setType('url', PARAM_TEXT);
		
		$mform->addElement('hidden','exampleid');
		$mform->setType('exampleid', PARAM_INT);
		$mform->setDefault('exampleid',$exampleid);
		
		$this->add_action_buttons(true, block_exacomp_get_string('submit_example'));
	}

	function validation($data, $files) {
		$errors = parent::validation($data, $files);
	
		$errors= array();
	
		if (!empty($data['url']) && filter_var($data['url'], FILTER_VALIDATE_URL) === FALSE &&
				filter_var("http://" . $data['url'], FILTER_VALIDATE_URL) === FALSE) {
			$errors['url'] = block_exacomp_get_string('linkerr');
		}
	
		if (empty($data['url']) && empty($data['file'])) {
			$errors['url'] = block_exacomp_get_string('submissionmissing');
			$errors['file'] = block_exacomp_get_string('submissionmissing');
		}
		return $errors;
	}
}
