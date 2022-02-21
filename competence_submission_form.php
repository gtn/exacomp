// Not used for now
<?php
//// This file is part of Moodle - http://moodle.org/
////
//// Moodle is free software: you can redistribute it and/or modify
//// it under the terms of the GNU General Public License as published by
//// the Free Software Foundation, either version 3 of the License, or
//// (at your option) any later version.
////
//// Moodle is distributed in the hope that it will be useful,
//// but WITHOUT ANY WARRANTY; without even the implied warranty of
//// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//// GNU General Public License for more details.
////
//// You should have received a copy of the GNU General Public License
//// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
//
//require_once $CFG->libdir . '/formslib.php';
//
//class block_exacomp_competence_submission_form extends moodleform {
//
//    function definition() {
//        global $DB;
//
//        $mform = &$this->_form;
//        $compid = $this->_customdata['compid'];
//        $comptype = $this->_customdata['comptype'];
//        $competence = $DB->get_record('block_exacompdescriptors', ['id' => $compid]);
//
//        $competenceTitle = '';
//        $competenceTitle .= '<h3 class="exacomp-submission-example-title">' . $competence->title . '</h3>';
//        if ($competence->description) {
//            $competenceTitle .= '<span class="exacomp-submission-example-description">' . $competence->description . '</span>';
//        }
//
//        $mform->addElement('html', $competenceTitle);
//
//        switch ($comptype) {
//            case BLOCK_EXACOMP_TYPE_DESCRIPTOR:
//                $infotext = block_exacomp_get_string("descriptor_submission_info", null, $competence->title);
//                break;
//            case BLOCK_EXACOMP_TYPE_TOPIC:
//                $infotext = block_exacomp_get_string("topic_submission_info", null, $competence->title);
//                break;
//        }
//
//        $mform->addElement('static', 'info', block_exacomp_get_string('description'), $infotext);
//
//        $mform->addElement('text', 'name', block_exacomp_get_string("name_example"), 'maxlength="255" size="60"');
//        $mform->setType('name', PARAM_TEXT);
//        $mform->addRule('name', block_exacomp_get_string("titlenotemtpy"), 'required', null, 'client');
//
//        $mform->addElement('text', 'intro', block_exacomp_get_string("moduleintro"), 'maxlength="255" size="60"');
//        $mform->setType('intro', PARAM_TEXT);
//
//        $mform->addElement('filepicker', 'file', block_exacomp_get_string('file'), null, array('subdirs' => false, 'maxfiles' => 1));
//
//        $mform->addElement('text', 'url', block_exacomp_get_string("link"), 'maxlength="255" size="60"');
//        $mform->setType('url', PARAM_TEXT);
//
//        $mform->addElement('hidden', 'compid');
//        $mform->setType('compid', PARAM_INT);
//        $mform->setDefault('compid', $compid);
//
//        $this->add_action_buttons(true, block_exacomp_get_string('submit_example'));
//    }
//
//    function validation($data, $files) {
//        $errors = parent::validation($data, $files);
//
//        $errors = array();
//
//        if (!empty($data['url']) && filter_var($data['url'], FILTER_VALIDATE_URL) === false &&
//            filter_var("http://" . $data['url'], FILTER_VALIDATE_URL) === false) {
//            $errors['url'] = block_exacomp_get_string('linkerr');
//        }
//
//        if (empty($data['url']) && empty($data['file'])) {
//            $errors['url'] = block_exacomp_get_string('submissionmissing');
//            $errors['file'] = block_exacomp_get_string('submissionmissing');
//        }
//        return $errors;
//    }
//}
