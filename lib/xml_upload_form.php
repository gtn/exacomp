<?php
require_once $CFG->libdir . '/formslib.php';

class block_exacomp_xml_upload_form extends moodleform {

	function definition() {
		global $CFG, $USER, $DB;
		$mform = & $this->_form;

		$this->_form->_attributes['action'] = $_SERVER['REQUEST_URI'];
		$mform->addElement('header', 'comment', get_string("doimport_own", "block_exacomp"));

		$mform->addElement('filepicker', 'file', get_string("file"),null);
		$mform->addRule('file', get_string("commentshouldnotbeempty", "block_exaport"), 'required', null, 'client');

		$this->add_action_buttons(false, get_string('add'));

	}

}