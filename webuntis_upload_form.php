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

class block_exacomp_webuntis_upload_form extends moodleform {

    function definition() {
        global $CFG, $USER, $DB;

        $mform = & $this->_form;

        $mform->addElement('filepicker', 'file', 'WebUntis-File', null, array('subdirs' => false, 'maxfiles' => 1));

        $this->add_action_buttons(true, block_exacomp_get_string('submit_example'));
    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $errors= array();

        if (empty($data['file'])) {
            $errors['file'] = block_exacomp_get_string('submissionmissing');
        }
        return $errors;
    }
}
