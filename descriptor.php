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

use block_exacomp\descriptor;

require __DIR__ . '/inc.php';

global $DB, $OUTPUT, $PAGE, $USER;

$courseid = required_param('courseid', PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_exacomp', $courseid);
}

block_exacomp_require_login($course);

$context = context_course::instance($courseid);
$output = block_exacomp_get_renderer();

$id = optional_param('id', 0, PARAM_INT);
$item = $id ? descriptor::get($id) : null;

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/descriptor.php', array('courseid' => $courseid));
$PAGE->set_heading($item ? block_exacomp_trans('competence_edit', ['de:Kompetenz bearbeiten', 'en:Edit competence']) : block_exacomp_trans('competence_add', ['de:Neue Kompetenz anlegen', 'en:Create new competence']));
$PAGE->set_pagelayout('embedded');

// build tab navigation & print header

/* CONTENT REGION */

block_exacomp_require_teacher($context);
if ($item) {
    block_exacomp_require_item_capability(BLOCK_EXACOMP_CAP_MODIFY, $item);
}

// TODO: check permissions, check if item is BLOCK_EXACOMP_DATA_SOURCE_CUSTOM

if ($item && optional_param('action', '', PARAM_TEXT) == 'delete') {
    require_sesskey();
    block_exacomp_require_item_capability(BLOCK_EXACOMP_CAP_DELETE, $item);
    $item->delete();

    echo $output->popup_close_and_reload();
    exit;
}

require_once $CFG->libdir . '/formslib.php';

class block_exacomp_local_item_form extends moodleform {

    function definition() {
        global $CFG, $USER, $DB, $PAGE;

        $output = block_exacomp_get_renderer();

        $mform = &$this->_form;

        //$mform->addElement('text', 'title', block_exacomp_get_string('name'), 'maxlength="1000" size="100"');
        $mform->addElement('textarea', 'title', block_exacomp_get_string('name'), 'rows="6" cols="100" size="60"');
        $mform->setType('title', PARAM_TEXT);
        $mform->addRule('title', block_exacomp_get_string("titlenotemtpy"), 'required', null, 'client');

        if ($this->_customdata['hasNiveau']) {
            $mform->addElement('selectgroups', 'niveauid', block_exacomp_get_string('niveau'), block_exacomp_get_select_niveau_items(false));
        }

        $element = $mform->addElement('select', 'categories', block_exacomp_get_string('competence_grid_niveau'), $DB->get_records_menu(BLOCK_EXACOMP_DB_CATEGORIES, null, 'title', 'id, title'));
        $element->setMultiple(true);

        $this->add_action_buttons(false);
    }

    public function display() {
        ob_start();
        parent::display();
        $out = ob_get_contents();
        ob_end_clean();
        $doc = new DOMDocument();
        @$doc->loadHTML(mb_convert_encoding($out, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $selector = new DOMXPath($doc);
        // Add class to the form.
        foreach ($selector->query('//form') as $f) {
            $f->setAttribute("class", $f->getAttribute('class') . ' descriptor_form');
        }
        $output = $doc->saveHTML($doc->documentElement);
        print $output;
    }

}

$form = new block_exacomp_local_item_form($_SERVER['REQUEST_URI'], ['hasNiveau' => !$item->parentid]);

if ($item) {
    $data = $item->get_data();
    // also load category ids for form
    $data->categories = $item->category_ids;
    $form->set_data($data);
}

if ($formdata = $form->get_data()) {
    require_sesskey();
    $new = new stdClass();
    $new->title = $formdata->title;
    $new->niveauid = $formdata->niveauid;
    $new->author = fullname($USER);
    $new->editor = fullname($USER);

    if (!$item) {
        die('TODO');
        $new->source = BLOCK_EXACOMP_DATA_SOURCE_CUSTOM;
        $new->sourceid = 0;
        $new->subjid = required_param('subjectid', PARAM_INT);

        $new->id = $DB->insert_record(BLOCK_EXACOMP_DB_TOPICS, $new);

        // add topic to course
        $DB->insert_record(BLOCK_EXACOMP_DB_COURSETOPICS, array(
            'courseid' => $courseid,
            'topicid' => $new->id,
        ));
    } else {
        $item->update($new);
    }
    $item->store_categories($formdata->categories);

    echo $output->popup_close_and_reload();
    exit;
}

echo $output->header($context, $courseid, '', false);

/*
if ($item) {
	// TODO: also check $item->can_delete
	echo '<div style="position: absolute; top: 40px; right: 20px;">';
	echo '<a href="'.$_SERVER['REQUEST_URI'].'&action=delete" onclick="return confirm(\''.block_exacomp_trans('de:Wirklich löschen?').'\');">';
	echo block_exacomp_get_string('delete');
	echo '</a></div>';
}
*/

$form->display();

echo $output->footer();
