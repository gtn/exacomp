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

use block_exacomp\data;
use block_exacomp\data_importer;
use block_exacomp\event\import_completed;

require __DIR__ . '/inc.php';

$de = false;
$lang = current_language();
if (isset($lang) && substr($lang, 0, 2) === 'de') {
    $de = true;
}

global $DB, $OUTPUT, $PAGE;

$courseid = required_param('courseid', PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_simplehtml', $courseid);
}

block_exacomp_require_login($course);

$context = context_system::instance();
$course_context = context_course::instance($courseid);

/* PAGE IDENTIFIER - MUST BE CHANGED. Please use string identifier from lang file */
$page_identifier = 'tab_teacher_settings_gridimport';

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/import_teacher.php', array('courseid' => $courseid));
$PAGE->set_heading(block_exacomp_get_string('blocktitle'));
$PAGE->set_title(block_exacomp_get_string($page_identifier));

if (!block_exacomp_is_teacher($courseid)) {
    throw new block_exacomp_permission_exception('User is no teacher');
}

// build breadcrumbs navigation
block_exacomp_build_breadcrum_navigation($courseid);

// build tab navigation & print header
$output = block_exacomp_get_renderer();
echo $output->header($context, $courseid, 'tab_teacher_settings');
echo $OUTPUT->tabtree(block_exacomp_build_navigation_tabs_settings($courseid), $page_identifier);
/* CONTENT REGION */
$courseSettings = block_exacomp_get_settings_by_course($courseid);


$importoption = optional_param('importoption', "", PARAM_ALPHA);
$importtype = 'normal'; // TODO: only normal or other types? - optional_param('importtype', '', PARAM_TEXT);
$action = optional_param('action', '', PARAM_TEXT);

//require_once $CFG->libdir . '/formslib.php';
require_once(__DIR__ . '/classes/import_generalxml_upload_form.php');

$mform = new generalxml_upload_form();

$importSuccess = false;
$importException = null;

data::prepare();

try {
    // check category renaming
    $import_data = $mform->get_data();
    if ($import_data) {
        require_sesskey();
        $course_template = intval($import_data->template);
    } else {
        $course_template = null;
    }
    $filecontent = ''; // Needed for next using, because $mform->get_file_content('file') can not work in the future
    if (block_exacomp_can_teacher_import_grid() && $importtype == 'normal' && $data = $mform->get_file_content('file')) {
        $filecontent = $data;
        $GLOBALS['teacher_imports_to_course'] = $courseid; // used in data_importer later;
        $importSuccess = block_exacomp\data_importer::do_import_string($data, $course_template, BLOCK_EXACOMP_IMPORT_SOURCE_DEFAULT, $import_data->password, true);
    }

    if ($importSuccess) {
        import_completed::log(['objectid' => $courseid, 'courseid' => $courseid]);
    }

} catch (block_exacomp\import_exception $importException) {
}

$coursenode = $PAGE->navigation->find($courseid, navigation_node::TYPE_COURSE);
$blocknode = $coursenode->add(block_exacomp_get_string('blocktitle'));
$pagenode = $blocknode->add(block_exacomp_get_string($page_identifier), $PAGE->url);
$pagenode->make_active();


if (block_exacomp_can_teacher_import_grid()) {

    if ($mform->is_cancelled()) {
        redirect($PAGE->url);
    } else {
        if ($filecontent) { // Instead of $data = $mform->get_file_content('file')
            if ($importSuccess) {
                if ($importSuccess === true) {
                    // "success" message
//                    $html = html_writer::div(block_exacomp_get_string("importsuccess"), 'alert alert-success');
                    // A link to the topic selection
                    $nextstepurl = new moodle_url('courseselection.php', array('courseid' => $courseid));
                    $nextstepdata = (object)[
                        'url' => $nextstepurl,
                        'title' => block_exacomp_get_string('next_step_first_teacher_step'),
                    ];
                    $html = html_writer::empty_tag('img', [
                            'src' => new moodle_url('/blocks/exacomp/pix/compprof_rating_teacher_grey_2_3.png'),
                            'alt' => '']
                        )
                        . '&nbsp;' . block_exacomp_get_string('import_teacher_next_step', 'block_exacomp', $nextstepdata);

                    echo $OUTPUT->box($html, 'alert alert-success');
                } else if (is_array($importSuccess) && $importSuccess['result'] != 'goRealImporting') {
                    // no errors for now, but the user needs to configure importing
                    $htmltext = '';
                    $step = 1;
                    switch ($importSuccess['result']) {
                        case 'compareCategories':
                            if ($htmltext == '') {
                                $step++;
//                                $img = 'compprof_rating_teacher_grey_' . $step . '_3.png'; // 3.img
                                $img = '';
                                $htmltext = block_exacomp_get_string("import_category_mapping_needed");
                            }
                        case 'selectGrids':
                            if ($htmltext == '') {
                                $step++;
//                                $img = 'compprof_rating_teacher_grey_' . $step . '_3.png'; // 2.img
                                $img = '';
                                $htmltext = block_exacomp_get_string("import_selectgrids_needed");
                            }
                        case 'selectSchooltype':
                            // Selects the schooltype for every imported grid
                            if ($htmltext == '') {
                                $step++;
//                                $img = 'compprof_rating_teacher_grey_' . $step . '_3.png'; // 2.img
                                $img = '';
                                $htmltext = block_exacomp_get_string("import_selectschooltypes_needed");
                            }
                    }
                    $html = html_writer::empty_tag('img', array(
                            'src' => new moodle_url ('/blocks/exacomp/pix/' . $img),
                            'alt' => '',
                        )) . '&nbsp;' . $htmltext;
                    echo $OUTPUT->box($html, 'alert alert-warning');
                    $mform->setConfirmationData($importSuccess);
                    $mform->display();
                }
            } else {
                echo block_exacomp_get_renderer()->box_error($importException);
                $mform->display();
            }

        } else {

            echo $OUTPUT->box(block_exacomp_get_string("importinfo"));
            @set_time_limit(0);
            $max_execution_time = (int)ini_get('max_execution_time');
            if ($max_execution_time && $max_execution_time < 60 * 5) {
                echo '<h3>' . block_exacomp_get_string("import_max_execution_time", null, $max_execution_time) . '</h3>';
            }
            $html = html_writer::empty_tag('img', array(
                    'src' => new moodle_url ('/blocks/exacomp/pix/compprof_rating_teacher_grey_1_3.png'),
                    'alt' => '',
                )) . '&nbsp;' . block_exacomp_get_string("import_select_file");
            echo $OUTPUT->box($html, 'alert alert-warning');
            $mform->display();

        }
    }

}

/* END CONTENT REGION */
echo $output->footer();

