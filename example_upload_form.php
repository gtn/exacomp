<?php
/* * *************************************************************
 *  Copyright notice
*
*  (c) 2011 exabis internet solutions <info@exabis.at>
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

require_once $CFG->libdir . '/formslib.php';

class block_exacomp_example_upload_form extends moodleform {

	function definition() {
		global $CFG, $USER, $DB, $version, $PAGE;

		$output = $PAGE->get_renderer('block_exacomp');
		
		$mform = & $this->_form;

		$descrid = $this->_customdata['descrid'];
		
		$descrTitle = $DB->get_field('block_exacompdescriptors','title',array("id"=>$descrid));
		$mform->addElement('header', 'general', get_string("example_upload_header", "block_exacomp", $descrTitle));

		$mform->addElement('hidden', 'id');
		$mform->setType('id', PARAM_INT);
		$mform->setDefault('id', 0);
		
		$tree = $this->_customdata['tree'];
		//$html_tree = $output->print_competence_based_list_tree($tree, true, 1);
		
		//$mform->addElement('list', 'descriptors', get_string('descriptors', 'block_exacomp'), $html_tree);
		
		$descriptorgroups = array();
		foreach($this->_customdata['topics'] as $topic){
			foreach($topic->descriptors as $id=>$descriptor) {
				$descriptor->title = $descriptor->niveautitle . ": " . $descriptor->title;
				$descriptorgroups[$descriptor->title] = array();
				foreach($descriptor->descriptors as $cid => $child)
					$descriptorgroups[$descriptor->title][$cid] = $child->title;
			}
		}
		$select = $mform->addElement('selectgroups', 'descriptors',  get_string('descriptors','block_exacomp'), $descriptorgroups);
		$select->setMultiple(true);
		$select->setSelected($descrid);
		$select->setSize(8);
		$mform->addHelpButton('descriptors', 'descriptors', 'block_exacomp');
		/*
		$mform->addElement('hidden', 'descrid');
		$mform->setType('descrid', PARAM_INT);
		$mform->setDefault('descrid', $descrid); */

		$mform->addElement('hidden', 'action');
		$mform->setType('action', PARAM_ACTION);
		$mform->setDefault('action', 'add');

		$mform->addElement('text', 'title', get_string("name_example","block_exacomp"), 'maxlength="255" size="60"');
		$mform->setType('title', PARAM_TEXT);
		$mform->addRule('title', get_string("titlenotemtpy", "block_exacomp"), 'required', null, 'client');

		$mform->addElement('text', 'description', get_string("moduleintro"), 'maxlength="255" size="60"');
		$mform->setType('description', PARAM_TEXT);
		
		$mform->addElement('text', 'externalurl', get_string("link","block_exacomp"), 'maxlength="255" size="60"');
		$mform->setType('externalurl', PARAM_TEXT);
		
		$mform->addElement('select', 'taxid', get_string('taxonomy', 'block_exacomp'),$this->_customdata['taxonomies']);
		
		$editexample = $this->_customdata['exampleid'] > 0;
		
		if(!$editexample || ($editexample && !$this->_customdata['task'])) {
    		$mform->addElement('filepicker', 'file', get_string('file'), null, array('subdirs' => false, 'maxfiles' => 1));
		} else {
		    $mform->addElement('static', 'filelabel', get_string('file'));
		    $mform->addElement('html', '<img width="50%" src="'.$this->_customdata['task'].'"/>',get_string('file'));
		}
		
		if(!$editexample || ($editexample && !$this->_customdata['solution'])) {
		    $mform->addElement('filepicker', 'solution', get_string('solution','block_exacomp'), null, array('subdirs' => false, 'maxfiles' => 1));
		
		    if($version) {
		        $mform->addElement('checkbox', 'lisfilename', get_string('lisfilename', 'block_exacomp'));
		        $mform->setDefault('lisfilename', 1);
		    }
		} else {
		    $mform->addElement('static', 'solutionlabel', get_string('solution','block_exacomp'));
		    $mform->addElement('html', '<img width="50%" src="'.$this->_customdata['solution'].'"/>',get_string('solution','block_exacomp'));
		}
		
		$mform->addElement('hidden','topicid');
		$mform->setType('topicid', PARAM_INT);
		$mform->setDefault('topicid',$this->_customdata['topicid']);
		
		$mform->addElement('hidden','exampleid');
		$mform->setType('exampleid', PARAM_INT);
		$mform->setDefault('exampleid',$this->_customdata['exampleid']);
		
		$this->add_action_buttons(false);
	}

	function validation($data, $files) {
		$errors = parent::validation($data, $files);
	
		$errors= array();
	
		if (!empty($data['link']) && filter_var($data['link'], FILTER_VALIDATE_URL) === FALSE) {
			$errors['link'] = get_string('linkerr','block_exacomp');
		}
	
		return $errors;
	}
}