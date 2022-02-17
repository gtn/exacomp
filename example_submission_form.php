<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

require_once $CFG->libdir . '/formslib.php';

class block_exacomp_example_submission_form extends moodleform {

    function definition() {
        global $CFG, $USER, $DB;

        $mform = &$this->_form;

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
                array('onclick' => 'window.open("' . $url . '"); return false;',
                    'style' => 'cursor: pointer;',
                    'title' => $title)
            );
        };
        $exampleTitle = '';
        if ($example->ethema_parent > 0) {
            $parentExample = $DB->get_record('block_exacompexamples', ['id' => $example->ethema_parent]);
            $parentExampleObj = block_exacomp\example::get($example->ethema_parent);
            $exampleTitle .= '<h3 class="exacomp-submission-subcategory-title">' . $parentExample->title . '</h3>';
            if ($parentExample->description) {
                $exampleTitle .= '<span class="exacomp-submission-subcategory-desription">' . $parentExample->description . '</span>';
            }
            // files
            $subfiles = '';
            // completefile
            if ($parentExample->completefile) {
                $subfiles .= ' ' . $fileLink($parentExample->completefile, 'globesearch.png', block_exacomp_get_string('preview') . ': ' . $parentExample->completefile);
            }
            // externaltask
            if ($parentExample->externaltask) {
                $subfiles .= ' ' . $fileLink($parentExample->externaltask, 'globesearch.png', block_exacomp_get_string('preview') . ': ' . $parentExample->externaltask);
            }
            if ($subfiles) {
                $exampleTitle .= '<span class="exacomp-submission-example-files">' . block_exacomp_get_string('files') . ': ' . $subfiles . '</span>';
            }
            // links
            $sublinks = '';
            // external url
            if ($parentExample->externalurl) {
                $sublinks .= ' ' . $fileLink($parentExample->externalurl, 'globesearch.png', block_exacomp_get_string('preview') . ': ' . $parentExample->externalurl);
            }
            // file task
            /*if ($taskurl = $parentExampleObj->get_task_file_url()) {
                $sublinks .= ' '.$fileLink($taskurl, 'filesearch.png', block_exacomp_get_string('preview').': '.$taskurl);
            }
            // file solution: TODO: check permissions (block_exacomp_renderer.php)
            $solutionurl = $parentExampleObj->get_solution_file_url();
            if (($isTeacher || $visible_solution) && $solutionurl) {
                $sublinks .= ' '.$fileLink($solutionurl, 'fullpage.png', block_exacomp_get_string('solution').': '.$solutionurl);
            }*/
            if ($sublinks) {
                $exampleTitle .= '<span class="exacomp-submission-subcategory-links">' . block_exacomp_get_string('files') . ': ' . $sublinks . '</span>';
            }
        }
        //if ($exampleTitle) {
        //    $exampleTitle .= '<br>';
        //}
        $exampleTitle .= '<h3 class="exacomp-submission-example-title">' . $example->title . '</h3>';
        if ($example->description) {
            $exampleTitle .= '<span class="exacomp-submission-example-description">' . $example->description . '</span>';
        }
        $files = '';
        // completefile
        if ($example->completefile) {
            $files .= ' ' . $fileLink($example->completefile, 'globesearch.png', block_exacomp_get_string('preview') . ': ' . $example->completefile);
        }
        // externaltask
        if ($example->externaltask) {
            $files .= ' ' . $fileLink($example->externaltask, 'globesearch.png', block_exacomp_get_string('preview') . ': ' . $example->externaltask);
        }
        if ($files) {
            $exampleTitle .= '<span class="exacomp-submission-example-files">' . block_exacomp_get_string('files') . ': ' . $files . '</span>';
        }

        $links = '';
        // external url
        if ($example->externalurl) {
            $links .= ' ' . $fileLink($example->externalurl, 'globesearch.png', block_exacomp_get_string('preview') . ': ' . $example->externalurl);
        }
        /*// file task
        if ($taskurl = $exampleObj->get_task_file_url()) {
            $links .= ' '.$fileLink($taskurl, 'filesearch.png', block_exacomp_get_string('preview').': '.$taskurl);
        }
        // file solution: TODO: check permissions (block_exacomp_renderer.php)
        $solutionurl = $exampleObj->get_solution_file_url();
        if (($isTeacher || $visible_solution) && $solutionurl) {
            $links .= ' '.$fileLink($solutionurl, 'fullpage.png', block_exacomp_get_string('solution').': '.$solutionurl);
        }*/
        if ($links) {
            $exampleTitle .= '<span class="exacomp-submission-example-links">' . block_exacomp_get_string('links') . ': ' . $links . '</span>';
        }
        //$mform->addElement('header', 'general', block_exacomp_get_string("example_submission_header", null, $exampleTitle));
        //$mform->addElement('header', 'general', $exampleTitle);
        $mform->addElement('html', $exampleTitle);

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

        $mform->addElement('hidden', 'exampleid');
        $mform->setType('exampleid', PARAM_INT);
        $mform->setDefault('exampleid', $exampleid);

        $this->add_action_buttons(true, block_exacomp_get_string('submit_example'));
    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $errors = array();

        if (!empty($data['url']) && filter_var($data['url'], FILTER_VALIDATE_URL) === false &&
            filter_var("http://" . $data['url'], FILTER_VALIDATE_URL) === false) {
            $errors['url'] = block_exacomp_get_string('linkerr');
        }

        if (empty($data['url']) && empty($data['file'])) {
            $errors['url'] = block_exacomp_get_string('submissionmissing');
            $errors['file'] = block_exacomp_get_string('submissionmissing');
        }
        return $errors;
    }
}
