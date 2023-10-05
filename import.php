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

class generalxml_upload_form extends moodleform {

    protected $_confirmationData = null;

    public function setConfirmationData($data = null) {
        $this->_definition_finalized = false; // activate new form elements
        $this->_confirmationData = $data;
    }

    function definition() {
        $mform = &$this->_form;

        $importtype = optional_param('importtype', 'normal', PARAM_TEXT);

        $this->_form->_attributes['action'] = $_SERVER['REQUEST_URI'];
        $this->_form->_attributes['class'] = "mform exacomp_import";
        $check = data::has_data();

        if ($importtype == 'custom') {
            $mform->addElement('header', 'comment', block_exacomp_get_string("doimport_own"));
        } else if ($importtype == 'scheduler') {
            $mform->addElement('header', 'comment', block_exacomp_get_string("scheduler_import_settings"));
        } else if ($check) {
            $mform->addElement('header', 'comment', block_exacomp_get_string("doimport"));
        } else {
            $mform->addElement('header', 'comment', block_exacomp_get_string("doimport_again"));
        }

        if ($importtype != 'scheduler') {
            $mform->addElement('filepicker', 'file', block_exacomp_get_string("file"), null);
            $mform->addRule('file', null, 'required', null, 'client');
            $mform->addElement('static', 'destination_text', '', block_exacomp_get_string("dest_course"));
            $mform->addElement('select', 'template', block_exacomp_get_string("choosecoursetemplate"), ['' => ''] + block_exacomp_get_course_names());
        }

        $mform->addElement('text', 'password', block_exacomp_trans([
                'de:Passwort der Zip-Datei',
                'en:Zip-File Password',
            ]) . ':');
        $mform->setType('password', PARAM_TEXT);
    }

    function definition_after_data() {
        global $CFG, $DB;
        //parent::definition_after_data();
        $forSchedulerTask = false;
        $mform =& $this->_form;

        if ($this->_confirmationData && is_array($this->_confirmationData)) {
            $data = $this->_confirmationData;
            if (isset($data['forSchedulerTask']) && $data['forSchedulerTask'] == true) {
                $forSchedulerTask = true;
            }
            switch ($data['result']) {
                case 'compareCategories':

                    $mform->addElement('html', '<input type="hidden" name="currentImportStep" value="compareCategories">');
                    $categoryMapping = data_importer::get_categorymapping_for_source($data['sourceId'], $forSchedulerTask);
                    // input form for comparing categories
                    $difflevels = block_exacomp_get_assessment_diffLevel_options_splitted();
                    $difflevels = array_combine($difflevels, $difflevels);
                    $difflevels = array(
                            '--as_is--' => block_exacomp_get_string('import_mapping_as_is'),
                            '--delete--' => block_exacomp_get_string('import_mapping_delete'),
                        ) + $difflevels;

                    $mform->addElement('html', '<table class="table">
                                                <thead><tr>
                                                    <td>' . block_exacomp_get_string("import_category_mapping_column_xml") . '</td>
                                                    <td></td>
                                                    <td>' . block_exacomp_get_string("import_category_mapping_column_exacomp") . '</td>
                                                    <td>' . block_exacomp_get_string("import_category_mapping_column_level") . '</td>
                                                </tr></thead><tbody>');
                    $mform->addElement('html', '');
                    $mform->addElement('html', '');
                    $mform->addElement('html', '');

                    foreach ($data['list'] as $catItem) {
                        $catId = intval($catItem->attributes()->id);
                        $mform->addElement('html', '<tr>');
                        $mform->addElement('html', '<td>');
                        $mform->addElement('html', $catItem->title);
                        $mform->addElement('html', '</td>');
                        $mform->addElement('html', '<td>');
                        $mform->addElement('html', '&nbsp;&rarr;&nbsp;');
                        $mform->addElement('html', '</td>');
                        $mform->addElement('html', '<td>');
                        $select = $mform->addElement('select', 'changeTo[' . $catId . ']', null, $difflevels);
                        if ($categoryMapping && array_key_exists($catId, $categoryMapping)) {
                            $select->setSelected($categoryMapping[$catId]);
                        }
                        $mform->addElement('html', '</td>');
                        $mform->addElement('html', '<td>');
                        $mform->addElement('html', ($catItem->lvl == 5 ? block_exacomp_get_string("import_category_mapping_column_level_descriptor") : block_exacomp_get_string("import_category_mapping_column_level_example")));
                        $mform->addElement('html', '</td>');
                        $mform->addElement('html', '</tr>');
                    }
                    $mform->addElement('html', '</tbody></table>');
                    break;
                case 'selectGrids':
                    require_once(__DIR__ . '/classes/import_selectgrid_checkbox.php');
                    MoodleQuickForm::registerElementType('import_selectgrid_checkbox', $CFG->dirroot . '/blocks/exacomp/classes/import_selectgrid_checkbox.php', 'block_exacomp_import_extraconfigcheckbox');
                    $currentAllGrids = block_exacomp\data_importer::get_selectedallgrids_for_source($data['sourceId'], $forSchedulerTask);
                    //$mform->addElement('hidden', 'currentImportStep');
                    //$mform->setType('currentImportStep', PARAM_TEXT);
                    //$mform->setDefault('currentImportStep', 'selectGrids');
                    $mform->addElement('html', '<input type="hidden" name="currentImportStep" value="selectGrids">');
                    // if used preselected values - message
                    $currentSelected = block_exacomp\data_importer::get_selectedgrids_for_source($data['sourceId'], $forSchedulerTask);
                    if ($currentSelected || $currentAllGrids == 1) {
                        // additional condition: at least one subject is from this source
                        $subjectExisting = $DB->get_records('block_exacompsubjects', ['source' => $data['sourceId']]);
                        if ($subjectExisting) {
                            $mform->addElement('html', '<div class="alert alert-info small">' .
                                block_exacomp_get_string('import_used_preselected_from_previous') . '</div>');
                        }
                    }
                    // import all subjects
                    $params = array('class' => 'import-all-subjects');
                    if ($currentAllGrids == 1) {
                        $params['checked'] = 'checked';
                    }
                    $mform->addElement('html', '<strong>');
                    $mform->addElement('import_selectgrid_checkbox', 'selectedGridAll', '', block_exacomp_get_string('importtask_all_subjects'), $params);
                    $mform->addElement('html', '</strong>');
                    $mform->addElement('html', '<div id="import-subjects-list">');
                    // Add select/deselect buttons
                    $buttons = '<div><small>' .
                        '<a class="exacomp_import_select_sublist" data-targetList="-1" data-selected="1">' . block_exacomp_get_string('select_all') . '</a>&nbsp;/&nbsp;' .
                        '<a class="exacomp_import_select_sublist" data-targetList="-1" data-selected="0">' . block_exacomp_get_string('deselect_all') . '</a>' .
                        '</small></div>';
                    $currPath = '';
                    $mform->addElement('html', $buttons . '<ul class="exacomp_import_grids_list">');
                    $pathIndex = 0;
                    foreach ($data['list'] as $subjid => $subject) {
                        $path = (string)$subject->pathname;
                        if ($currPath != $path) {
                            $pathIndex++;
                            $mform->addElement('html', '</ul>');
                            $buttons = '<small>' .
                                '<a class="exacomp_import_select_sublist" data-targetList="' . $pathIndex . '" data-selected="1">' . block_exacomp_get_string('select_all') . '</a>&nbsp;/&nbsp;' .
                                '<a class="exacomp_import_select_sublist" data-targetList="' . $pathIndex . '" data-selected="0">' . block_exacomp_get_string('deselect_all') . '</a>' .
                                '</small>';
                            $mform->addElement('html', '<h4>' . $path . '&nbsp;' . $buttons . '</h4>' . "\r\n");
                            $mform->addElement('html', '<ul class="exacomp_import_grids_list" data-pathIndex="' . $pathIndex . '">');
                            $currPath = $subject->pathname;
                        }
                        $mform->addElement('html', '<li>');
                        $params = array();
                        if ($subject->selected) {
                            $params['checked'] = 'checked';
                        }
                        $addText = '';
                        if ($subject->newForSelected) {
                            $addText = block_exacomp_get_string('new');
                        }
                        $mform->addElement('import_selectgrid_checkbox', 'selectedGrid[' . $subjid . ']', $addText, $subject->title, $params);
                        $mform->addElement('html', '</li>');
                    }
                    $mform->addElement('html', '</ul>');
                    $mform->addElement('html', '</div');
                    break;
            }

            // hide general fields if it is a step of importing
            if (@$data['result']) {
                // file
                $fileElement = $mform->getElement('file');
                $currFileValue = $fileElement->_attributes['value'];
                $mform->removeElement('file');
                $mform->addElement('hidden', 'file');
                $mform->setType('file', PARAM_INT);
                $mform->setDefault('file', $currFileValue);
                // destination_text
                $mform->removeElement('destination_text');
                // course template
                $templateElement = $mform->getElement('template');
                $values = @$templateElement->_values;
                if ($values) {
                    $currTemplateValue = reset($values); // only one template!
                } else {
                    $currTemplateValue = '';
                }
                $mform->removeElement('template');
                $mform->addElement('hidden', 'template');
                $mform->setType('template', PARAM_INT);
                $mform->setDefault('template', $currTemplateValue);
                // zip password
                $passwordElement = $mform->getElement('password');
                $currPasswordValue = $passwordElement->_attributes['value'];
                $mform->removeElement('password');
                $mform->addElement('hidden', 'password');
                $mform->setType('password', PARAM_TEXT);
                $mform->setDefault('password', $currPasswordValue);
            }
        }

        if ($forSchedulerTask) {
            $this->add_action_buttons(false, block_exacomp_get_string('save')); // settings of scheduler importing
        } else {
            $this->add_action_buttons(false, block_exacomp_get_string('add')); // we need buttons to bottom. so it is here. usual importing
        }

    }

    function is_validated() {
        $this->_definition_finalized = true; // disable start of  definition_after_data()
        $result = parent::is_validated();
        $this->_definition_finalized = false; // activate new form elements
        return $result;
    }

}

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

/* Admins are allowed to import data, or a special capability for custom imports */
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
            case 'scheduler':
                $taskform = new importtask_form();
                $taskslist = function() use ($OUTPUT, $DB, $courseid, $CFG, $isAdmin) {
                    // "add new" button
                    $buttons = html_writer::link(new moodle_url('/blocks/exacomp/import.php',
                        array('courseid' => $courseid, 'importtype' => 'scheduler', 'action' => 'add')),
                        block_exacomp_get_string('add_new_importtask'),
                        array('class' => 'btn btn-default'));
                    if ($CFG->version >= 2017022300 && $isAdmin) { // only from Moodle 3.3
                        $buttons .= '&nbsp;&nbsp;&nbsp;' . html_writer::link(
                                new moodle_url('/admin/tool/task/schedule_task.php',
                                    array('task' => 'block_exacomp\task\import_additional')),
                                html_writer::empty_tag('img',
                                    array('src' => new moodle_url('/blocks/exacomp/pix/pre-planning-storage-icon.png'),
                                        'height' => 16, 'width' => 16)) . '&nbsp;' . block_exacomp_get_string('import_activate_scheduled_tasks'),
                                array('target' => '_blank', 'class' => 'btn btn-default'));
                    }
                    echo $OUTPUT->box($buttons);
                    $tasks = $DB->get_records(BLOCK_EXACOMP_DB_IMPORTTASKS, null, 'title');
                    $table = new html_table();
                    $table->attributes['class'] = 'exacomp_importtasks';
                    $rows = array();
                    foreach ($tasks as $task) {
                        $row = new html_table_row();
                        // title
                        $cell = new html_table_cell();
                        $cell->attributes['class'] = 'exacomp_importtask_title';
                        $cell->text = $task->title . '&nbsp;&nbsp;&nbsp;';
                        $row->cells[] = $cell;
                        // link
                        $cell = new html_table_cell();
                        $cell->attributes['class'] = 'exacomp_importtask_link';
                        $cell->text = '<span>' . $task->link . '</span>&nbsp;&nbsp;&nbsp;';
                        $row->cells[] = $cell;
                        // buttons
                        $rows[] = $row;
                        $cell = new html_table_cell();
                        $cell->attributes['class'] = 'exacomp_importtask_buttons';
                        $edit = html_writer::link(new moodle_url('/blocks/exacomp/import.php',
                            array('courseid' => $courseid, 'importtype' => 'scheduler', 'action' => 'edit', 'id' => $task->id, 'sesskey' => sesskey())),
                            html_writer::empty_tag('img', array('src' => new moodle_url('/pix/i/edit.png'))),
                            array('class' => ''));
                        $settings = html_writer::link(new moodle_url('/blocks/exacomp/import.php',
                            array('courseid' => $courseid, 'importtype' => 'scheduler', 'action' => 'settings', 'id' => $task->id, 'sesskey' => sesskey())),
                            html_writer::empty_tag('img', array('src' => new moodle_url('/pix/e/document_properties.png'))),
                            array('class' => ''));
                        $button_pic = $task->disabled ? 'completion-auto-fail.png' : 'completion-auto-enabled.png';
                        $disable = html_writer::link(new moodle_url('/blocks/exacomp/import.php',
                            array('courseid' => $courseid, 'importtype' => 'scheduler', 'action' => 'disable', 'id' => $task->id, 'sesskey' => sesskey())),
                            html_writer::empty_tag('img', array('src' => new moodle_url('/pix/i/' . $button_pic))),
                            array('class' => ''));
                        $delete = html_writer::link(new moodle_url('/blocks/exacomp/import.php',
                            array('courseid' => $courseid, 'importtype' => 'scheduler', 'action' => 'delete', 'id' => $task->id, 'sesskey' => sesskey())),
                            html_writer::empty_tag('img', array('src' => new moodle_url('/pix/i/delete.png'))),
                            array('class' => ''));
                        $cell->text = $disable . '&nbsp;' . $edit . '&nbsp;' . $settings . '&nbsp;' . $delete . '&nbsp;';
                        $row->cells[] = $cell;

                        $row = new html_table_row();
                        $row->attributes['class'] = 'highlight';
                    }
                    $table->data = $rows;
                    echo html_writer::table($table);
                };

                switch ($action) {
                    case 'add':// add new task
                        if ($taskdata = $taskform->get_data()) {
                            require_sesskey();
                            $taskdata->disabled = 1;
                            // save data
                            $DB->insert_record(BLOCK_EXACOMP_DB_IMPORTTASKS, $taskdata);
                            $url = $PAGE->url;
                            $url->param('importtype', 'scheduler');
                            $taskslist();
                        } else {
                            $taskform->display();
                        }
                        break;
                    case 'edit': // edit task
                        $taskid = required_param('id', PARAM_INT);
                        if ($taskid) {
                            if ($taskdata = $taskform->get_data()) {
                                require_sesskey();
                                // save data
                                $taskdata->id = $taskid;
                                if (!isset($taskdata->disabled)) { // form does not send unchecked checkboxes
                                    $taskdata->disabled = 0;
                                }
                                $DB->update_record(BLOCK_EXACOMP_DB_IMPORTTASKS, $taskdata);
                                $url = $PAGE->url;
                                $url->param('importtype', 'scheduler');
                                $taskslist();
                            } else {
                                $taskdata = $DB->get_record(BLOCK_EXACOMP_DB_IMPORTTASKS, array('id' => $taskid));
                                $taskform->set_data($taskdata);
                                $taskform->display();
                            }
                        }
                        break;
                    case 'disable': // disable task
                        require_sesskey();
                        $taskid = required_param('id', PARAM_INT);
                        if ($taskid) {
                            $taskdata = $DB->get_record(BLOCK_EXACOMP_DB_IMPORTTASKS, array('id' => $taskid));
                            $taskdata->disabled = $taskdata->disabled ? 0 : 1;
                            $DB->update_record(BLOCK_EXACOMP_DB_IMPORTTASKS, $taskdata);
                        }
                        $taskslist();
                        break;
                    case 'delete': // delete task
                        require_sesskey();
                        $taskid = required_param('id', PARAM_INT);
                        if ($taskid) {
                            $DB->delete_records(BLOCK_EXACOMP_DB_IMPORTTASKS, array('id' => $taskid));
                        }
                        $taskslist();
                        break;
                    case 'settings': // edit settings: category_mapping, selected_grids
                        require_sesskey();
                        // it is simulating of importing:
                        // - download xml from link
                        // - try to import
                        // - get import settings (category_mapping, selected_grids) - like in manual importing
                        // - NOT do real importing
                        $taskid = required_param('id', PARAM_INT);
                        if ($taskid) {
                            $taskdata = $DB->get_record(BLOCK_EXACOMP_DB_IMPORTTASKS, array('id' => $taskid));
                            $url = $taskdata->link;
                            $importSuccess = block_exacomp\data_importer::do_import_url($url, $course_template, BLOCK_EXACOMP_IMPORT_SOURCE_DEFAULT, true, $taskid, false);
                            if (is_array($importSuccess)) {
                                // no errors for now, but the user needs to configure importing
                                switch ($importSuccess['result']) {
                                    case 'compareCategories':
                                        $html = block_exacomp_get_string("import_category_mapping_needed");
                                        break;
                                    case 'selectGrids':
                                        $html = block_exacomp_get_string("import_selectgrids_needed");
                                        break;
                                }
                                echo $OUTPUT->box($html, 'alert alert-warning');
                                $importSuccess['forSchedulerTask'] = true; // marker for simulating
                                //$importSuccess['allGrids'] = $taskdata->all_grids;
                                $mform->setConfirmationData($importSuccess);
                                $mform->display();
                            } else if ($importSuccess) {
                                $taskslist();
                            } else {
                                print_error('something wrong!');
                            }
                        }
                        break;
                    // list of scheduler import tasks
                    default: // list of tasks
                        $taskslist();
                        break;
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
                            block_exacomp_get_string('doimport_again'))) . '<hr>';
                    if ($CFG->version >= 2017022300) { // only from Moodle 3.3
                        $import_komet_html = html_writer::link(
                                new moodle_url('/admin/tool/task/schedule_task.php',
                                    array('task' => 'block_exacomp\task\import')),
                                block_exacomp_get_string('import_from_related_komet'),
                                array('target' => '_blank')) . '&nbsp;' . block_exacomp_help_icon(block_exacomp_get_string('import_from_related_komet'), block_exacomp_get_string('import_from_related_komet_help'));
                    } else if (!$CFG->cronclionly) { // for oldest versions
                        $import_komet_html = html_writer::link(
                            new moodle_url('/admin/cron.php'),
                            block_exacomp_get_string('import_from_related_komet'),
                            array('target' => '_blank'));
                    }
                    echo $OUTPUT->box($import_komet_html);
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
                echo $OUTPUT->box(html_writer::link(new moodle_url('/blocks/exacomp/import.php',
                    array('courseid' => $courseid, 'importtype' => 'scheduler')),
                    block_exacomp_get_string('schedulerimport')));
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

