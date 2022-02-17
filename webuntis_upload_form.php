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

class block_exacomp_webuntis_upload_form extends moodleform {

    function definition() {
        global $CFG, $USER, $DB;

        $mform = &$this->_form;

        $mform->addElement('filepicker', 'file', 'WebUntis-File', null, array('subdirs' => false, 'maxfiles' => 1));

        $this->add_action_buttons(true, block_exacomp_get_string('submit_example'));
    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $errors = array();

        if (empty($data['file'])) {
            $errors['file'] = block_exacomp_get_string('submissionmissing');
        }
        return $errors;
    }
}
