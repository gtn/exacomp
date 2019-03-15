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
	    
	    $descrTitle = $DB->get_field(BLOCK_EXACOMP_DB_DESCRIPTORS,'title',array("id"=>$descrid));
// 	    $mform->addElement('header', 'general', block_exacomp_get_string("example_upload_header", null, $descrTitle));

	    if ($this->_customdata['categories']) {
	        $cselect = $mform->addElement('select', 'catid', block_exacomp_get_string('descriptor_categories') ,$this->_customdata['categories']);
	        $cselect->setMultiple(true);
	        $cselect->setSelected(array_keys($DB->get_records(BLOCK_EXACOMP_DB_DESCCAT,array("descrid" => $this->_customdata['descrid']),"","catid")));
	    }

	    $this->add_action_buttons(true);
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
        $newInput->appendXML('<br /><span>'.block_exacomp_get_string('descriptor_add_category').' <input class="form-control" name="newcategory" value="" size="10" /> </span>');
        foreach ($selector->query('//select[@name=\'catid[]\']') as $e) {
            $e->setAttribute("class", $e->getAttribute('class').' exacomp_forpreconfig');
            $e->parentNode->appendChild($newInput);
        }
        $output = $doc->saveHTML($doc->documentElement);
        print $output;
    }

}
