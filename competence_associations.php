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

use block_exacomp\param;

require __DIR__ . '/inc.php';
require_once __DIR__ . '/example_submission_form.php';

global $DB, $OUTPUT, $PAGE, $USER;

$courseid = required_param('courseid', PARAM_INT);
$exampleid = required_param('exampleid', PARAM_INT);
$editmode = optional_param('editmode', 0, PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_exacomp', $courseid);
}

// error if example does not exist or was created by somebody else
if (!$example = $DB->get_record('block_exacompexamples', array('id' => $exampleid))) {
    print_error('invalidexample', 'block_exacomp', $exampleid);
}

block_exacomp_require_login($course);

$context = context_course::instance($courseid);

// CHECK TEACHER
$isTeacher = block_exacomp_is_teacher($context);
if (!$isTeacher) {
    // don't allow editmode for students
    $editmode = 0;
}

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/competence_associations.php', array('courseid' => $courseid));
$PAGE->set_heading(block_exacomp_get_string('blocktitle'));
$PAGE->set_pagelayout('embedded');

// build breadcrumbs navigation
block_exacomp_build_breadcrum_navigation($courseid);

$output = block_exacomp_get_renderer();
// build tab navigation & print header
echo $output->header($context, $courseid, "", false);

if ($editmode && optional_param("action", "", PARAM_TEXT) == "save") {
    require_sesskey();
    $descriptorids = param::optional_array('descriptor', PARAM_INT);

    if ($descriptorids) {
        // at least one descriptor needed, else example would disappear

        foreach ($descriptorids as $descriptorid) {
            //check if record already exists -> if not insert new
            $record = $DB->get_records(BLOCK_EXACOMP_DB_DESCEXAMP, array('descrid' => $descriptorid, 'exampid' => $exampleid));
            if (!$record) {
                $sql = "SELECT MAX(sorting) as sorting FROM {" . BLOCK_EXACOMP_DB_DESCEXAMP . "} WHERE descrid=?";
                $max_sorting = $DB->get_record_sql($sql, array($descriptorid));
                $sorting = intval($max_sorting->sorting) + 1;

                $insert = new stdClass();
                $insert->descrid = $descriptorid;
                $insert->exampid = $exampleid;
                $insert->sorting = $sorting;
                $DB->insert_record(BLOCK_EXACOMP_DB_DESCEXAMP, $insert);
            }
        }

        $not_in = join(',', $descriptorids);
        $deleted = $DB->delete_records_select(BLOCK_EXACOMP_DB_DESCEXAMP, 'exampid = ? AND descrid NOT IN(' . $not_in . ')', array($exampleid));
    }

    echo $output->popup_close_and_reload();
    exit;
}
/* CONTENT REGION */
//get descriptors for the given example
$example_descriptors = $DB->get_records(BLOCK_EXACOMP_DB_DESCEXAMP, array('exampid' => $exampleid), '', 'descrid');

$tree = block_exacomp_build_example_association_tree($courseid, $example_descriptors, $exampleid);

echo html_writer::tag("p", block_exacomp_get_string("competence_associations_explaination", null, $example->title));
$content = $output->competence_based_list_tree($tree, $isTeacher, $editmode);

if ($editmode) {
    $content .= html_writer::empty_tag('input', array('type' => 'submit', 'value' => block_exacomp_get_string('save_selection')));
}


echo html_writer::tag('form', $content, array('method' => 'post', 'action' => $PAGE->url->out(false, array('sesskey' => sesskey())) . '&exampleid=' . $exampleid . '&editmode=' . $editmode . '&action=save', 'name' => 'add_association'));

/* END CONTENT REGION */
echo $output->footer();
