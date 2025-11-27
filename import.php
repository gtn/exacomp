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

if ($de) {
    define('DEMO_XML_PATH', 'https://raw.githubusercontent.com/gtn/edustandards/master/demo/deutsch/exacomp_data.xml');
} else {
    define('DEMO_XML_PATH', 'https://raw.githubusercontent.com/gtn/edustandards/master/demo/english/exacomp_data.xml');
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
$page_identifier = 'tab_admin_import';

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/import.php', array('courseid' => $courseid));
$PAGE->set_heading(block_exacomp_get_string('blocktitle'));
$PAGE->set_title(block_exacomp_get_string($page_identifier));

$isAdmin = has_capability('block/exacomp:admin', $context);
block_exacomp_require_teacher($context);

$importoption = optional_param('importoption', "", PARAM_ALPHA);
$importtype = optional_param('importtype', '', PARAM_TEXT);
$action = optional_param('action', '', PARAM_TEXT);

require_once $CFG->libdir . '/formslib.php';

class importtask_form extends moodleform {

    function definition() {
        $mform = &$this->_form;

        $this->_form->_attributes['action'] = $_SERVER['REQUEST_URI'];
        $this->_form->_attributes['class'] = "mform exacomp_importtask";

        $mform->addElement('text', 'title', block_exacomp_get_string('importtask_title'), array('size' => '50'));
        $mform->setType('title', PARAM_TEXT);
        $mform->addRule('title', null, 'required', null, 'client');
        $mform->addElement('text', 'link', block_exacomp_get_string('importtask_link'), array('size' => '50'));
        $mform->setType('link', PARAM_TEXT);
        //$mform->setType('link', PARAM_URL);
        //$mform->addRule('link', null, 'required', null, 'client');
        $mform->addElement('checkbox', 'disabled', block_exacomp_get_string('importtask_disabled'));

        $this->add_action_buttons(block_exacomp_get_string('cancel'), block_exacomp_get_string('save'));

    }
}

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
    if (($importtype == 'custom') && $data = $mform->get_file_content('file')) {
        $filecontent = $data;
        $importSuccess = block_exacomp\data_importer::do_import_string($data, $course_template, BLOCK_EXACOMP_IMPORT_SOURCE_SPECIFIC, $import_data->password, true);
    } else if ($isAdmin && ($importtype == 'normal') && $data = $mform->get_file_content('file')) {
        $filecontent = $data;
        $importSuccess = block_exacomp\data_importer::do_import_string($data, $course_template, BLOCK_EXACOMP_IMPORT_SOURCE_DEFAULT, $import_data->password, true);
    } else if ($isAdmin && ($importtype == 'demo')) {
        //do demo import

        // TODO: catch exception
        $file = optional_param('file', DEMO_XML_PATH, PARAM_TEXT);
        if ($importSuccess = block_exacomp\data_importer::do_import_url($file, $course_template, BLOCK_EXACOMP_IMPORT_SOURCE_DEFAULT, false, 0, true)) {
            block_exacomp_settstamp();
        }
    }

    if ($importSuccess) {
        import_completed::log(['objectid' => $courseid, 'courseid' => $courseid]);
    }

} catch (block_exacomp\import_exception $importException) {
}

// build breadcrumbs navigation
$coursenode = $PAGE->navigation->find($courseid, navigation_node::TYPE_COURSE);
$blocknode = $coursenode->add(block_exacomp_get_string('blocktitle'));
$pagenode = $blocknode->add(block_exacomp_get_string($page_identifier), $PAGE->url);
$pagenode->make_active();

$delete = false;
if (($isAdmin || block_exacomp_check_customupload()) && ($action == 'delete' && $importtype != 'scheduler')) {
    require_sesskey();
    block_exacomp\data::delete_source(required_param('source', PARAM_INT));
    $delete = true;
}

// build tab navigation & print header
$output = block_exacomp_get_renderer();
echo $output->header($context, $courseid, 'tab_admin_settings');
echo $OUTPUT->tabtree(block_exacomp_build_navigation_tabs_admin_settings($courseid), $page_identifier);
/* CONTENT REGION */

/* Admins are allowed to import data, or a special capability for custom imports*/
if ($isAdmin || block_exacomp_check_customupload()) {

    if ($importtype) {
        switch ($importtype) {
            case 'normal':
            case 'custom':
                if ($mform->is_cancelled()) {
                    redirect($PAGE->url);
                } else {
                    if ($filecontent) { // Instead of $data = $mform->get_file_content('file')
                        if ($importSuccess) {
                            if ($importSuccess === true) {
                                $string = block_exacomp_get_string('next_step');
                                $url = 'edit_config.php';

                                $html = html_writer::div(block_exacomp_get_string("importsuccess"), 'alert alert-success');
                                if ($isAdmin) {
                                    $html .= html_writer::empty_tag('img', array(
                                            'src' => new moodle_url('/blocks/exacomp/pix/one_admin.png'), 'alt' => '',
                                            'width' => '60px',
                                            'height' => '60px'))
                                        . html_writer::link(new moodle_url($url,
                                            array('courseid' => $courseid, 'fromimport' => 1)),
                                            $string);
                                }

                                echo $OUTPUT->box($html);
                            } else if (is_array($importSuccess) && $importSuccess['result'] != 'goRealImporting') {
                                // no errors for now, but the user needs to configure importing
                                $htmltext = '';
                                $step = 1;
                                switch ($importSuccess['result']) {
                                    case 'compareCategories':
                                        if ($htmltext == '') {
                                            $step++;
                                            $img = 'compprof_rating_teacher_grey_' . $step . '_3.png'; // 3.img
                                            $htmltext = block_exacomp_get_string("import_category_mapping_needed");
                                        }
                                    case 'selectGrids':
                                        if ($htmltext == '') {
                                            $step++;
                                            $img = 'compprof_rating_teacher_grey_' . $step . '_3.png'; // 2.img
                                            $htmltext = block_exacomp_get_string("import_selectgrids_needed");
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
                        if ($isAdmin) {
                            echo $OUTPUT->box(block_exacomp_get_string("importwebservice", null,
                                (string)new moodle_url("/admin/settings.php", array('section' => 'blocksettingexacomp'))));
                        }
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
                break;
            case 'demo':
                if ($importSuccess) {
                    $string = block_exacomp_get_string('next_step');

                    echo $OUTPUT->box(
                        html_writer::div(block_exacomp_get_string("importsuccess"), 'alert alert-success')
                        . html_writer::empty_tag('img', array('src' => new moodle_url('/blocks/exacomp/pix/one_admin.png'), 'alt' => '', 'width' => '60px', 'height' => '60px'))
                        . html_writer::link(new moodle_url('edit_config.php', array('courseid' => $courseid, 'fromimport' => 1)), $string));
                } else {
                    echo $OUTPUT->box(block_exacomp_get_string("importfail"));
                    echo block_exacomp_get_renderer()->box_error($importException);
                }
                break;
        } // switch
    } else {

        if (block_exacomp\data::has_old_data(BLOCK_EXACOMP_IMPORT_SOURCE_DEFAULT)) {
            if (!$isAdmin) {
                print_error('pls contact your admin');
            }

            echo $OUTPUT->box(html_writer::link(new moodle_url('/blocks/exacomp/import.php',
                array('courseid' => $courseid,
                    'importtype' => 'normal')),
                'For the latest exacomp version you need to reimport global educational standards'));
        } else if (block_exacomp\data::has_old_data(BLOCK_EXACOMP_IMPORT_SOURCE_SPECIFIC)) {
            if (!$isAdmin) {
                print_error('pls contact your admin');
            }

            echo $OUTPUT->box(html_writer::link(new moodle_url('/blocks/exacomp/import.php',
                array('courseid' => $courseid,
                    'importtype' => 'custom')),
                'For the latest exacomp version you need to reimport school/company specific standards'));
        } else {
            $hasData = block_exacomp\data::has_data();

            if ($delete) {
                echo $OUTPUT->box(block_exacomp_get_string("delete_success"));
            }
            if ($isAdmin) {
                if ($hasData) {
                    echo $OUTPUT->box(block_exacomp_get_string("importdone"));

                    echo $OUTPUT->box(html_writer::link(
                            new moodle_url('/blocks/exacomp/import.php',
                                array('courseid' => $courseid,
                                    'importtype' => 'normal')),
                            block_exacomp_get_string('doimport_again')));
                } else {
                    // no data yet, allow import or import demo data
                    echo $OUTPUT->box(html_writer::empty_tag('img',
                            array('src' => new moodle_url('/blocks/exacomp/pix/one_admin.png'),
                                'alt' => '',
                                'width' => '60px',
                                'height' => '60px')
                        ) . block_exacomp_get_string('first_configuration_step'));
                    echo $OUTPUT->box(block_exacomp_get_string("importpending"));
                    echo $OUTPUT->box(html_writer::link(new moodle_url('/blocks/exacomp/import.php',
                        array('courseid' => $courseid,
                            'importtype' => 'normal')),
                        block_exacomp_get_string('doimport')));
                    //echo $OUTPUT->box(html_writer::link(new moodle_url('/blocks/exacomp/import.php', array('courseid'=>$courseid, 'importtype'=>'demo')), block_exacomp_get_string('do_demo_import')));
                }
            }

            // export
            if ($hasData) {
                echo '<hr />';
                echo $OUTPUT->box(html_writer::link(new moodle_url('/blocks/exacomp/export.php', array('action' => 'export_all', 'courseid' => $courseid, 'sesskey' => sesskey())), block_exacomp_get_string("export_all_standards")));
                echo $OUTPUT->box(html_writer::link(new moodle_url('/blocks/exacomp/export.php', array('action' => 'select', 'courseid' => $courseid)), block_exacomp_get_string("export_selective")));
            }

            if ($isAdmin) {
                echo '<hr />';
                echo $output->sources();
            }
        }
    }
}

/* END CONTENT REGION */
echo $output->footer();

