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

require __DIR__ . '/inc.php';
require_once __DIR__ . '/webuntis_upload_form.php';

$courseid = required_param('courseid', PARAM_INT);
$studentid = required_param('studentid', PARAM_INT); //not always studentid actually, but id of selected weekly schedule

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_exacomp', $courseid);
}

block_exacomp_require_login($course);

$context = context_course::instance($courseid);

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/webuntis_upload.php', array('courseid' => $courseid));
$PAGE->set_heading(block_exacomp_get_string('blocktitle'));
$PAGE->set_pagelayout('embedded');

// build breadcrumbs navigation
$coursenode = $PAGE->navigation->find($courseid, navigation_node::TYPE_COURSE);
$blocknode = $coursenode->add(block_exacomp_get_string('blocktitle'));
$blocknode->make_active();

// build tab navigation & print header
$output = block_exacomp_get_renderer();
echo $output->header($context, $courseid, '', false);

/* CONTENT REGION */

$isTeacher = block_exacomp_is_teacher();
$visible_solution = block_exacomp_is_example_solution_visible($courseid, $exampleid, $USER->id);
$form = new block_exacomp_webuntis_upload_form($_SERVER['REQUEST_URI'],
    array());

if ($formdata = $form->get_data()) {
    require_sesskey();
    $type = 'file';
    if (isset($formdata->file)) {
        if (!$studentid) {
            $studentid = $USER->id;
        } else if ($studentid == BLOCK_EXACOMP_SHOW_ALL_STUDENTS) {
            $studentid = 0;
        }
        block_exacomp_import_ics_to_weekly_schedule($courseid, $studentid, null, $USER->id, $form->get_file_content('file'));
    }
    echo $output->popup_close_and_reload();
    exit;
} else if ($form->is_cancelled()) {
    echo $output->popup_close_and_reload();
    exit;
}

$form->display();

/* END CONTENT REGION */
echo $output->footer();
