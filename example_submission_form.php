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
		$isTeacher = $this->_customdata['isTeacher'];
		$studentid = $this->_customdata['studentid'];
        $visible_solution = $this->_customdata['visible_solution'];

		//$exampleTitle = $DB->get_field('block_exacompexamples', 'title', array("id" => $exampleid));
        $isTeacher = block_exacomp_is_teacher();
        // complex $exampleTitle
        $example = $DB->get_record('block_exacompexamples', ['id' => $exampleid]);
        $exampleObj = block_exacomp\example::get($exampleid);
        $output = block_exacomp_get_renderer();
        $fileLink = function($url, $img = null, $title = '') use ($output) {
            if (!$img) {
                $img = 'globesearch.png';
            }
            return html_writer::span($output->local_pix_icon($img, $title),
                    '',
                    array('onclick' => 'window.open("'.$url.'"); return false;',
                            'style' => 'cursor: pointer;',
                            'title' => $title)
            );
        };
        $exampleTitle = '';
        if ($example->ethema_parent > 0) {
            $parentExample = $DB->get_record('block_exacompexamples', ['id' => $example->ethema_parent]);
            $parentExampleObj = block_exacomp\example::get($example->ethema_parent);
            $exampleTitle .= $parentExample->title;
            if ($parentExample->description) {
                $exampleTitle .= '<br>'.$parentExample->description;
            }
            // external url
            if ($parentExample->externalurl) {
                $exampleTitle .= ' '.$fileLink($parentExample->externalurl, 'globesearch.png', block_exacomp_get_string('preview'));
            }
            // file task
            if ($taskurl = $parentExampleObj->get_task_file_url()) {
                $exampleTitle .= ' '.$fileLink($taskurl, 'filesearch.png', block_exacomp_get_string('preview'));
            }
            // file solution: TODO: check permissions (block_exacomp_renderer.php)
            $solutionurl = $parentExampleObj->get_solution_file_url();
            if (($isTeacher || $visible_solution) && $solutionurl) {
                $exampleTitle .= ' '.$fileLink($solutionurl, 'fullpage.png', block_exacomp_get_string('solution'));
            }
        }
        //echo "<pre>debug:<strong>example_submission_form.php:47</strong>\r\n"; print_r($exampleTitleArr); echo '</pre>'; exit; // !!!!!!!!!! delete it
        if ($exampleTitle) {
            $exampleTitle .= '<br>';
        }
        $exampleTitle .= $example->title;
        if ($example->description) {
            $exampleTitle .= '<br>'.$example->description;
        }
        // external url
        if ($example->externalurl) {
            $exampleTitle .= ' '.$fileLink($example->externalurl, 'globesearch.png', block_exacomp_get_string('preview'));
        }
        // file task
        if ($taskurl = $exampleObj->get_task_file_url()) {
            $exampleTitle .= ' '.$fileLink($taskurl, 'filesearch.png', block_exacomp_get_string('preview'));
        }
        // file solution: TODO: check permissions (block_exacomp_renderer.php)
        $solutionurl = $exampleObj->get_solution_file_url();
        if (($isTeacher || $visible_solution) && $solutionurl) {
            $exampleTitle .= ' '.$fileLink($solutionurl, 'fullpage.png', block_exacomp_get_string('solution'));
        }
		//$mform->addElement('header', 'general', block_exacomp_get_string("example_submission_header", null, $exampleTitle));
		$mform->addElement('header', 'general', $exampleTitle);

		$mform->addElement('static', 'info', block_exacomp_get_string('description'),
				block_exacomp_get_string("example_submission_info", null, $example->title));
		
		$mform->addElement('text', 'name', block_exacomp_get_string("name_example"), 'maxlength="255" size="60"');
		$mform->setType('name', PARAM_TEXT);
		$mform->setDefault('name', $example->title);
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
