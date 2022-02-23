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

use block_exacomp\topic;

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
$item = $id ? topic::get($id) : null;

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/topic.php', array('courseid' => $courseid));
$PAGE->set_heading(block_exacomp_trans($item ? ['de:Kompetenzbereich bearbeiten', 'en:Edit competence area'] : ['de:Neuen Kompetenzbereich anlegen', 'en:Add competence area']));
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
    //$item->delete();
    block_exacomp_delete_tree($courseid, 'topic', $item->id);
    $forward = optional_param('forward', '', PARAM_URL);
    if ($forward) {
        echo $output->popup_close_and_forward($forward);
    } else {
        echo $output->popup_close_and_reload();
    }
    exit;
}

require_once $CFG->libdir . '/formslib.php';

class block_exacomp_local_item_form extends moodleform {

    function definition() {
        global $CFG, $USER, $DB, $PAGE;

        $output = block_exacomp_get_renderer();

        $mform = &$this->_form;

        $mform->addElement('text', 'title', block_exacomp_get_string('name'), 'maxlength="255" size="60"');
        $mform->setType('title', PARAM_TEXT);
        $mform->addRule('title', block_exacomp_get_string("titlenotemtpy"), 'required', null, 'client');

        $mform->addElement('text', 'numb', block_exacomp_trans('de:Nummer'), 'maxlength="4" size="4"');
        $mform->setType('numb', PARAM_INT);
        $mform->addRule('numb', block_exacomp_get_string('err_numeric', 'form'), 'required', null, 'client');

        $this->add_action_buttons(false);
    }
}

$form = new block_exacomp_local_item_form($_SERVER['REQUEST_URI']);
if ($item) {
    $form->set_data($item);
}

if ($formdata = $form->get_data()) {
    require_sesskey();
    $new = new stdClass();
    $new->title = $formdata->title;
    $new->numb = $formdata->numb;

    if (!$item) {
        $new->source = BLOCK_EXACOMP_DATA_SOURCE_CUSTOM;
        $new->sourceid = 0;
        $new->subjid = required_param('subjectid', PARAM_INT);
        $new->creatorid = $USER->id;

        $new->id = $DB->insert_record(BLOCK_EXACOMP_DB_TOPICS, $new);

        // add topic to course
        $DB->insert_record(BLOCK_EXACOMP_DB_COURSETOPICS, array(
            'courseid' => $courseid,
            'topicid' => $new->id,
        ));

        block_exacomp_set_topic_visibility($new->id, $courseid, 1, 0);

        $item = $new;
    } else {
        $subjectid = $item->subjid;
        $item->update($new);
    }

    echo $output->popup_close_and_forward($CFG->wwwroot . "/blocks/exacomp/assign_competencies.php?courseid=" . $courseid . "&editmode=1&subjectid={$item->subjid}&topicid={$item->id}");
    exit;
}

echo $output->header($context, $courseid, '', false);

if ($item) {
    // TODO: also check $item->can_delete
    echo '<div style="position: absolute; top: 40px; right: 20px;">';
    echo '<a href="' . $_SERVER['REQUEST_URI'] . '&action=delete" onclick="return confirm(\'' . block_exacomp_trans('de:Wirklich löschen?') . '\');">';
    echo block_exacomp_get_string('delete');
    echo '</a></div>';
}

$form->display();

echo $output->footer();
