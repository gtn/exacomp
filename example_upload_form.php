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

class block_exacomp_example_upload_form extends moodleform {

    private $hidetaxonomyselector = false;

    function definition() {
        global $CFG, $USER, $DB, $PAGE;

        $output = block_exacomp_get_renderer();

        $mform = &$this->_form;

        $descrid = @$this->_customdata['descrid'] ? $this->_customdata['descrid'] : null;
        $questionid = @$this->_customdata['questionid'] ? $this->_customdata['questionid'] : null;
        if (array_key_exists('crosssubjid', $this->_customdata)) {
            $crosssubjid = $this->_customdata['crosssubjid'];
        } else {
            $crosssubjid = null;
        }

        if ($descrid || $questionid) {
            if ($descrid) {
                $descrTitle = $DB->get_field('block_exacompdescriptors', 'title', array("id" => $descrid));
                $mform->addElement('header', 'general', block_exacomp_get_string("example_upload_header", null, $descrTitle));
            } else {
                $mform->addElement('header', 'general', block_exacomp_get_string("example_upload_header", null, null));
            }

            $mform->addElement('hidden', 'id');
            $mform->setType('id', PARAM_INT);
            $mform->setDefault('id', 0);

            //add html tree -> different treated in example_upload -> mform does not support a tree structure
            $treetitle = html_writer::start_div('fitem');
            $treetitle .= html_writer::start_div('fitemtitle') . html_writer::label(block_exacomp_get_string('descriptors'), 'tree') . html_writer::end_div();
            $treetitle .= html_writer::start_div('felement ftext');
            $tree = $this->_customdata['tree'];
            $html_tree = $output->competence_based_list_tree($tree, true, 1, false);
            $mform->addElement('html', $treetitle);
            $mform->addElement('html', $html_tree);

            $treetitle = html_writer::end_div() . html_writer::end_div();
            $mform->addElement('html', $treetitle);

            $mform->addElement('hidden', 'action');
            $mform->setType('action', PARAM_ACTION);
            $mform->setDefault('action', 'add');
        } else if ($crosssubjid) {
            $mform->addElement('header', 'general', block_exacomp_get_string("example_upload_header"));
        }
        if (!$questionid) {
            $mform->addElement('text', 'title', block_exacomp_get_string("name_example"), 'maxlength="255" size="60"');
            $mform->setType('title', PARAM_TEXT);
            $mform->addRule('title', block_exacomp_get_string("titlenotemtpy"), 'required', null, 'client');

            $mform->addElement('text', 'description', block_exacomp_get_string("description_example"), 'maxlength="255" size="60"');
            $mform->setType('description', PARAM_TEXT);

            $mform->addElement('text', 'timeframe', block_exacomp_get_string("timeframe_example"), 'maxlength="255" size="60"');
            $mform->setType('timeframe', PARAM_TEXT);

            if (!@$this->_customdata['taxonomies']) {
                $this->hidetaxonomyselector = true;
            }
            $tselect = $mform->addElement('select', 'taxid', block_exacomp_get_string('taxonomy'), @$this->_customdata['taxonomies']);
            $tselect->setMultiple(true);
            $tselect->setSelected(array_keys($DB->get_records(BLOCK_EXACOMP_DB_EXAMPTAX, array("exampleid" => @$this->_customdata['exampleid']), "", "taxid")));

            $mform->addElement('checkbox', 'isTeacherexample', block_exacomp_get_string('is_teacherexample'));
            $mform->setType('isTeacherexample', PARAM_INT);
            if ($this->_customdata['isTeacherexample']) {
                $mform->setDefault('isTeacherexample', true);
            }

            $mform->addElement('header', 'link', block_exacomp_get_string('link'));

            $mform->addElement('text', 'externalurl', block_exacomp_get_string("link"), 'maxlength="255" size="60"');
            $mform->setType('externalurl', PARAM_TEXT);

            $mform->addElement('header', 'filesheader', block_exacomp_get_string('files'));

            $mform->addElement('filemanager', 'files', block_exacomp_get_string('file'), null, array('subdirs' => false, 'maxfiles' => 2));
            $mform->addElement('filemanager', 'solution', block_exacomp_get_string('solution'), null, array('subdirs' => false, 'maxfiles' => 1));
            $mform->addElement('filemanager', 'completefile', block_exacomp_get_string('completefile'), null, array('subdirs' => false, 'maxfiles' => 1));

            if (@$this->_customdata['uses_activities']) {

                $mform->addElement('header', 'assignments', block_exacomp_get_string('assignments'));
                $mform->addElement('select', 'assignment', block_exacomp_get_string('assignments'), @$this->_customdata['activities']);
            }
            /* if(block_exacomp_is_altversion()) {
                $mform->addElement('checkbox', 'lisfilename', block_exacomp_get_string('lisfilename'));
                $mform->setDefault('lisfilename', 1);
            } */

            $mform->addElement('hidden', 'topicid');
            $mform->setType('topicid', PARAM_INT);
            $mform->setDefault('topicid', @$this->_customdata['topicid']);

            $mform->addElement('hidden', 'exampleid');
            $mform->setType('exampleid', PARAM_INT);
            $mform->setDefault('exampleid', @$this->_customdata['exampleid']);
        }
        $this->add_action_buttons(true);
    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $errors = array();

        if (!empty($data['link']) && filter_var($data['link'], FILTER_VALIDATE_URL) === false
            && filter_var("http://" . $data['link'], FILTER_VALIDATE_URL) === false) {
            $errors['link'] = block_exacomp_get_string('linkerr');
        }

        return $errors;
    }

    public function print_competence_based_list_tree_for_form($tree, $mform) {
        global $PAGE;

        $mform->addElement('html', '<ul>');
        foreach ($tree as $skey => $subject) {
            $mform->addElement('html', '<li>');
            $mform->addElement('static', 'subjecttitle', $subject->title);

            if (!empty($subject->topics)) {
                $mform->addElement('html', '<ul>');
            }

            foreach ($subject->topics as $tkey => $topic) {
                $mform->addElement('html', '<li>');
                $mform->addElement('static', 'subjecttitle', $subject->title);

                if (!empty($topic->descriptors)) {
                    $mform->addElement('html', '<ul>');
                }

                foreach ($topic->descriptors as $dkey => $descriptor) {
                    $mform = $this->print_competence_for_list_tree_for_form($descriptor, $mform);
                }

                if (!empty($topic->descriptors)) {
                    $mform->addElement('html', '</ul>');
                }

            }
            if (!empty($subject->topics)) {
                $mform->addElement('html', '</ul>');
            }

            $mform->addElement('html', '</li>');

        }
        $mform->addElement('html', '</ul>');
        return $mform;
    }

    private function print_competence_for_list_tree_for_form($descriptor, $mform) {
        $mform->addElement('html', '<li>');

        if (isset($descriptor->direct_associated)) {
            $mform->addElement('advcheckbox', 'descriptor[]', 'Kompetenzen', $descriptor->title, array('group' => 'descriptor'));
        }
        //$mform->setDefault('d');
        /*$html_tree .= html_writer::div(html_writer::div(
            html_writer::checkbox("descriptor[]", $descriptor->id, ($descriptor->direct_associated==1)?true:false, $descriptor->title),
            "felement fcheckbox"), "fitem fitem_fcheckbox ", array('id'=>'fitem_id_descriptor'));
*/ else {
            $mform->addElement('static', 'descriptortitle', $descriptor->title);
        }

        if (!empty($descriptor->examples)) {
            $mform->addElement('html', '<ul>');
        }

        foreach ($descriptor->examples as $example) {
            $mform->addElement('html', '<li>');
            $mform->addElement('static', 'exampletitle', $example->title);
        }

        if (!empty($descriptor->examples)) {
            $mform->addElement('html', '</ul>');
        }

        if (!empty($descriptor->children)) {
            $mform->addElement('html', '<ul>');

            foreach ($descriptor->children as $child) {
                $mform = $this->print_competence_for_list_tree_for_form($child, $mform);
            }

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
        //        $out = utf8_decode($out); // needed for umlauts, but problems with cyrillic
        //        @$doc->loadHTML($out, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        @$doc->loadHTML(mb_convert_encoding($out, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $selector = new DOMXPath($doc);
        $newInput = $doc->createDocumentFragment();
        $newInput->appendXML('<br /><span class="example_add_taxonomy">' . block_exacomp_get_string('example_add_taxonomy') . '</span> <input type="text" class="form-control" name="newtaxonomy" value="" size="10" />');
        foreach ($selector->query('//select[@name=\'taxid[]\']') as $e) {
            $e->setAttribute("class", $e->getAttribute('class') . ' exacomp_forpreconfig taxonomy_selector');
            if ($this->hidetaxonomyselector) {
                $e->setAttribute('style', 'display: none;');
            }
            $e->parentNode->appendChild($newInput);
        }
        foreach ($selector->query('//form') as $f) {
            $f->setAttribute("class", $f->getAttribute('class') . ' example_upload_form');
        }
        $output = $doc->saveHTML($doc->documentElement);
        print $output;
    }


}
