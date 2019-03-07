<?php
// This file is part of Exabis Competence Grid
//
// (c) 2019 GTN - Global Training Network GmbH <office@gtn-solutions.com>
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

class block_exacomp_update_categories_form extends moodleform {

	function definition() {
	    global $CFG, $DB;
	    
	    $output = block_exacomp_get_renderer();
	    
	    $mform = $this->_form; // Don't forget the underscore!
	    
	    $descrid = $this->_customdata['descrid'];
	    
	    $descrTitle = $DB->get_field('block_exacompdescriptors','title',array("id"=>$descrid));
// 	    $mform->addElement('header', 'general', block_exacomp_get_string("example_upload_header", null, $descrTitle));

	    if ($this->_customdata['categories']) {
	        $cselect = $mform->addElement('select', 'taxid', "derText" ,$this->_customdata['categories']);
	        $cselect->setMultiple(true);
	        $cselect->setSelected(array_keys($DB->get_records(BLOCK_EXACOMP_DB_DESCCAT,array("descrid" => $this->_customdata['descrid']),"catid")));
	    }
	    

	    $skillsarray = array(
	        'val1' => 'Skill A',
	        'val2' => 'Skill B',
	        'val3' => 'Skill C'
	    );
	    $mform->addElement('select', 'md_skills', get_string('skills', 'metadata'), $skillsarray);
	    $mform->getElement('md_skills')->setMultiple(true);
	    // This will select the skills A and B.
	    $mform->getElement('md_skills')->setSelected(array('val1', 'val2'));

	}

	function validation($data, $files) {
		$errors = parent::validation($data, $files);
	
		$errors= array();
	
		if (!empty($data['link']) && filter_var($data['link'], FILTER_VALIDATE_URL) === FALSE
				&& filter_var("http://" . $data['link'], FILTER_VALIDATE_URL) === FALSE) {
			$errors['link'] = block_exacomp_get_string('linkerr');
		}
	
		return $errors;
	}
	public function print_competence_based_list_tree_for_form($tree, $mform) {
		global $PAGE;
		
		$mform->addElement('html', '<ul>');
		foreach($tree as $skey => $subject) {
			$mform->addElement('html', '<li>');
			$mform->addElement('static', 'subjecttitle', $subject->title);
			
			if(!empty($subject->topics))
				$mform->addElement('html', '<ul>');
			
			foreach ( $subject->topics as $tkey => $topic ) {
					$mform->addElement('html', '<li>');
					$mform->addElement('static', 'subjecttitle', $subject->title);
			
					if(!empty($topic->descriptors))
						$mform->addElement('html', '<ul>');
					
					foreach ( $topic->descriptors as $dkey => $descriptor ) {
						$mform = $this->print_competence_for_list_tree_for_form($descriptor, $mform);
					}
					
					if(!empty($topic->descriptors))
						$mform->addElement('html', '</ul>');
				
			}
			if(!empty($subject->topics))
				$mform->addElement('html', '</ul>');
			
			$mform->addElement('html', '</li>');
			
		}
		$mform->addElement('html', '</ul>');
		return $mform;
	}
	
	private function print_competence_for_list_tree_for_form($descriptor, $mform) {
		$mform->addElement('html', '<li>');
		
		if(isset($descriptor->direct_associated))
			$mform->addElement('advcheckbox', 'descriptor[]', 'Kompetenzen', $descriptor->title, array('group'=>'descriptor'));
			//$mform->setDefault('d');
			/*$html_tree .= html_writer::div(html_writer::div(
				html_writer::checkbox("descriptor[]", $descriptor->id, ($descriptor->direct_associated==1)?true:false, $descriptor->title),
				"felement fcheckbox"), "fitem fitem_fcheckbox ", array('id'=>'fitem_id_descriptor'));
	*/	else 
			$mform->addElement('static', 'descriptortitle', $descriptor->title);
			
		if(!empty($descriptor->examples))
			$mform->addElement('html', '<ul>');
			
		foreach($descriptor->examples as $example) {
			$mform->addElement('html', '<li>');
			$mform->addElement('static', 'exampletitle', $example->title);
		}
			
		if(!empty($descriptor->examples))
			$mform->addElement('html', '</ul>');
			
		if(!empty($descriptor->children)) {
			$mform->addElement('html', '<ul>');
			
			foreach($descriptor->children as $child)
				$mform = $this->print_competence_for_list_tree_for_form($child, $mform);
			
			$mform->addElement('html', '</ul>');
		}
		$mform->addElement('html', '</li>');
		
		return $mform;
	}


    public function display() {
        ob_start();
        parent::display();
        $out = ob_get_contents();
        ob_end_clean();
        $doc = new DOMDocument();
        @$doc->loadHTML(utf8_decode($out), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $selector = new DOMXPath($doc);
        $newInput = $doc->createDocumentFragment();
        $newInput->appendXML('<br /><span>'.block_exacomp_get_string('example_add_taxonomy').'</span> <input class="form-control" name="newtaxonomy" value="" size="10" />');
        foreach ($selector->query('//select[@name=\'taxid[]\']') as $e) {
            $e->setAttribute("class", $e->getAttribute('class').' exacomp_forpreconfig');
            $e->parentNode->appendChild($newInput);
        }
        $output = $doc->saveHTML($doc->documentElement);
        print $output;
    }

}
