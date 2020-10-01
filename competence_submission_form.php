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

class block_exacomp_competence_submission_form extends moodleform {

    function definition() {
        global $CFG, $USER, $DB;

        $mform = & $this->_form;

        $competenceid = $this->_customdata['competenceid'];
        $isTeacher = $this->_customdata['isTeacher'];
        $studentid = $this->_customdata['studentid'];

        $isTeacher = block_exacomp_is_teacher();
        $competence = $DB->get_record('block_exacompdescriptors', ['id' => $competenceid]);
        $competenceObj = block_exacomp\descriptor::get($competenceid);
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
        $competenceTitle = '';
        $competenceTitle .= '<h3 class="exacomp-submission-example-title">'.$competence->title.'</h3>';
        if ($competence->description) {
            $competenceTitle .= '<span class="exacomp-submission-example-description">'.$competence->description.'</span>';
        }
        $files = '';
        // completefile
        if ($competence->completefile) {
            $files .= ' '.$fileLink($competence->completefile, 'globesearch.png', block_exacomp_get_string('preview').': '.$competence->completefile);
        }
        // externaltask
        if ($competence->externaltask) {
            $files .= ' '.$fileLink($competence->externaltask, 'globesearch.png', block_exacomp_get_string('preview').': '.$competence->externaltask);
        }
        if ($files) {
            $competenceTitle .= '<span class="exacomp-submission-example-files">'.block_exacomp_get_string('files').': '.$files.'</span>';
        }

        $links = '';
        // external url
        if ($competence->externalurl) {
            $links .= ' '.$fileLink($competence->externalurl, 'globesearch.png', block_exacomp_get_string('preview').': '.$competence->externalurl);
        }
        /*// file task
        if ($taskurl = $competenceObj->get_task_file_url()) {
            $links .= ' '.$fileLink($taskurl, 'filesearch.png', block_exacomp_get_string('preview').': '.$taskurl);
        }
        // file solution: TODO: check permissions (block_exacomp_renderer.php)
        $solutionurl = $competenceObj->get_solution_file_url();
        if (($isTeacher || $visible_solution) && $solutionurl) {
            $links .= ' '.$fileLink($solutionurl, 'fullpage.png', block_exacomp_get_string('solution').': '.$solutionurl);
        }*/
        if ($links) {
            $competenceTitle .= '<span class="exacomp-submission-example-links">'.block_exacomp_get_string('links').': '.$links.'</span>';
        }
        //$mform->addElement('header', 'general', block_exacomp_get_string("example_submission_header", null, $competenceTitle));
        //$mform->addElement('header', 'general', $competenceTitle);
        $mform->addElement('html', $competenceTitle);

        $mform->addElement('static', 'info', block_exacomp_get_string('description'),
            block_exacomp_get_string("example_submission_info", null, $competence->title));

        $mform->addElement('text', 'name', block_exacomp_get_string("name_example"), 'maxlength="255" size="60"');
        $mform->setType('name', PARAM_TEXT);
        $mform->setDefault('name', $competence->title);
//        $mform->addRule('name', block_exacomp_get_string("titlenotemtpy"), 'required', null, 'client');

        $mform->addElement('text', 'intro', block_exacomp_get_string("moduleintro"), 'maxlength="255" size="60"');
        $mform->setType('intro', PARAM_TEXT);

        $mform->addElement('filepicker', 'file', block_exacomp_get_string('file'), null, array('subdirs' => false, 'maxfiles' => 1));

        $mform->addElement('text', 'url', block_exacomp_get_string("link"), 'maxlength="255" size="60"');
        $mform->setType('url', PARAM_TEXT);

        $mform->addElement('hidden','competenceid');
        $mform->setType('competenceid', PARAM_INT);
        $mform->setDefault('competenceid',$competenceid);

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
