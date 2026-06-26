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

/**
 * Admin page for managing assessment preconfiguration templates stored in the database.
 * The folder admin/ does not have a meaning for moodle, like e.g. putting the settings into plugin/settings.php.
 * It is just for organization reasons, that we put it here, instead of lib.
 *
 * Why this external page at all? https://moodledev.io/docs/5.0/apis/subsystems/admin?#when-to-use-an-admin_settings-vs-admin_externalpages
 * "when the settings you are changing are in a custom table and not in the config tables via set_config"
 *
 */

require_once __DIR__ . '/../../../config.php';
require_once $CFG->libdir . '/adminlib.php';
require_once __DIR__ . '/../inc.php';

$context = context_system::instance();
$pageurl = new moodle_url('/blocks/exacomp/admin/assessment_preconfig.php');

admin_externalpage_setup('block_exacomp_assessment_preconfigs');
require_capability('moodle/site:config', $context);

$PAGE->set_context($context);
$PAGE->set_url($pageurl);
$PAGE->set_heading(get_string('manage_assessment_configurations', 'block_exacomp'));
$PAGE->set_title(get_string('manage_assessment_configurations', 'block_exacomp'));

$tablename = 'block_exacomp_assessment_cfgs';
$integerfields = array(
    'assessment_example_scheme',
    'assessment_example_diff_level',
    'assessment_example_self_eval',
    'assessment_childcomp_scheme',
    'assessment_childcomp_diff_level',
    'assessment_childcomp_self_eval',
    'assessment_comp_scheme',
    'assessment_comp_diff_level',
    'assessment_comp_self_eval',
    'assessment_topic_scheme',
    'assessment_topic_diff_level',
    'assessment_topic_self_eval',
    'assessment_subject_scheme',
    'assessment_subject_diff_level',
    'assessment_subject_self_eval',
    'assessment_theme_scheme',
    'assessment_theme_diff_level',
    'assessment_theme_self_eval',
    'assessment_points_limit',
    'assessment_points_negativ',
    'assessment_grade_limit',
    'assessment_grade_negativ',
    'assessment_verbose_negative',
    'assessment_verbose_lowerisbetter',
);
$textfields = array(
    'assessment_grade_verbose',
    'assessment_diff_level_options',
    'assessment_verbose_options',
    'assessment_verbose_options_short',
);
// Keep the DB defaults close to the form so new rows start with the same values as seeded data.
$fielddefaults = array(
    'assessment_example_scheme' => 0,
    'assessment_example_diff_level' => 0,
    'assessment_example_self_eval' => 0,
    'assessment_childcomp_scheme' => 0,
    'assessment_childcomp_diff_level' => 0,
    'assessment_childcomp_self_eval' => 0,
    'assessment_comp_scheme' => 0,
    'assessment_comp_diff_level' => 0,
    'assessment_comp_self_eval' => 0,
    'assessment_topic_scheme' => 0,
    'assessment_topic_diff_level' => 0,
    'assessment_topic_self_eval' => 0,
    'assessment_subject_scheme' => 0,
    'assessment_subject_diff_level' => 0,
    'assessment_subject_self_eval' => 0,
    'assessment_theme_scheme' => 0,
    'assessment_theme_diff_level' => 0,
    'assessment_theme_self_eval' => 0,
    'assessment_points_limit' => 10,
    'assessment_points_negativ' => 4,
    'assessment_grade_limit' => 6,
    'assessment_grade_negativ' => 5,
    'assessment_grade_verbose' => '',
    'assessment_diff_level_options' => '',
    'assessment_verbose_options' => '',
    'assessment_verbose_negative' => 0,
    'assessment_verbose_options_short' => '',
    'assessment_verbose_lowerisbetter' => 0,
);

$action = optional_param('action', '', PARAM_ALPHA);
$id = optional_param('id', 0, PARAM_INT);
$editid = optional_param('editid', 0, PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_BOOL);

if ($action === 'save') {
    require_sesskey();

    $record = new stdClass();
    $record->id = optional_param('id', 0, PARAM_INT);
    $record->name = trim(required_param('name', PARAM_TEXT));

    foreach ($integerfields as $fieldname) {
        $record->{$fieldname} = optional_param($fieldname, $fielddefaults[$fieldname], PARAM_INT);
    }
    foreach ($textfields as $fieldname) {
        $record->{$fieldname} = trim(optional_param($fieldname, $fielddefaults[$fieldname], PARAM_TEXT));
    }

    if ($record->id) {
        $existing = $DB->get_record($tablename, array('id' => $record->id), '*', MUST_EXIST);
        // Preserve sortorder on edit so admins can reorder templates independently from field changes.
        $record->sortorder = $existing->sortorder;
        $DB->update_record($tablename, $record);
    } else {
        $maxsortorder = $DB->get_field_sql('SELECT MAX(sortorder) FROM {' . $tablename . '}');
        // New rows are appended so existing selections keep their relative order.
        $record->sortorder = (int)$maxsortorder + 1;
        $DB->insert_record($tablename, $record);
    }

    redirect($pageurl, get_string('changessaved'));
}

if (($action === 'moveup' || $action === 'movedown') && $id) {
    require_sesskey();

    $records = array_values($DB->get_records($tablename, null, 'sortorder ASC, id ASC'));
    $currentindex = null;
    foreach ($records as $index => $record) {
        if ((int)$record->id === $id) {
            $currentindex = $index;
            break;
        }
    }

    if ($currentindex !== null) {
        $swapindex = ($action === 'moveup') ? $currentindex - 1 : $currentindex + 1;
        if (isset($records[$swapindex])) {
            $current = $records[$currentindex];
            $swapwith = $records[$swapindex];
            $transaction = $DB->start_delegated_transaction();
            // Swap the explicit sort values so the admin list stays stable across databases.
            $currentsortorder = $current->sortorder;
            $current->sortorder = $swapwith->sortorder;
            $swapwith->sortorder = $currentsortorder;
            $DB->update_record($tablename, $current);
            $DB->update_record($tablename, $swapwith);
            $transaction->allow_commit();
        }
    }

    redirect($pageurl);
}

if ($action === 'delete' && $id && !$confirm) {
    $config = $DB->get_record($tablename, array('id' => $id), '*', MUST_EXIST);
    $deleteurl = new moodle_url($pageurl, array(
        'action' => 'delete',
        'id' => $id,
        'confirm' => 1,
        'sesskey' => sesskey(),
    ));

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('manage_assessment_configurations', 'block_exacomp'));
    // Ask for confirmation first so templates are not removed accidentally from admin settings.
    echo $OUTPUT->confirm(get_string('deletecheckfull', '', format_string($config->name)), $deleteurl, $pageurl);
    echo $OUTPUT->footer();
    exit;
}

if ($action === 'delete' && $id && $confirm) {
    require_sesskey();
    $DB->delete_records($tablename, array('id' => $id));
    redirect($pageurl, get_string('changessaved'));
}

$editrecord = (object)array_merge(array('id' => 0, 'name' => ''), $fielddefaults);
if ($editid) {
    $editrecord = $DB->get_record($tablename, array('id' => $editid), '*', MUST_EXIST);
}

$records = $DB->get_records($tablename, null, 'sortorder ASC, id ASC');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manage_assessment_configurations', 'block_exacomp'));

// Back button to the main Exabis Competencies settings page.
// The section parameter is the admin settings section name registered by the plugin.
$backurl = new moodle_url('/admin/settings.php', array(
    'section' => 'blocksettingexacomp',
));

echo html_writer::div(
    html_writer::link(
        $backurl,
        get_string('back'),
        array('class' => 'btn btn-secondary')
    ),
    'mb-3'
);

$table = new html_table();
$table->head = array('ID', get_string('name'), get_string('actions'));
$table->data = array();

$recordsindexed = array_values($records);
foreach ($recordsindexed as $index => $record) {
    $actions = array();

    $editurl = new moodle_url($pageurl, array('editid' => $record->id));
    $actions[] = html_writer::link($editurl, $OUTPUT->pix_icon('t/edit', get_string('edit')));

    $deleteurl = new moodle_url($pageurl, array('action' => 'delete', 'id' => $record->id));
    $actions[] = html_writer::link($deleteurl, $OUTPUT->pix_icon('t/delete', get_string('delete')));

    if ($index > 0) {
        $moveupurl = new moodle_url($pageurl, array(
            'action' => 'moveup',
            'id' => $record->id,
            'sesskey' => sesskey(),
        ));
        $actions[] = html_writer::link($moveupurl, $OUTPUT->pix_icon('t/up', get_string('up')));
    }

    if ($index < count($recordsindexed) - 1) {
        $movedownurl = new moodle_url($pageurl, array(
            'action' => 'movedown',
            'id' => $record->id,
            'sesskey' => sesskey(),
        ));
        $actions[] = html_writer::link($movedownurl, $OUTPUT->pix_icon('t/down', get_string('down')));
    }

    $table->data[] = array(
        $record->id,
        format_string($record->name),
        implode(' ', $actions),
    );
}

echo html_writer::table($table);

$headingtext = $editrecord->id ? get_string('edit') : get_string('new');
echo $OUTPUT->heading($headingtext, 3);

$formtable = new html_table();
$formtable->data = array();

$nameinput = html_writer::empty_tag('input', array(
    'type' => 'text',
    'name' => 'name',
    'value' => $editrecord->name,
    'size' => 80,
));
$formtable->data[] = array('name', $nameinput);

foreach ($integerfields as $fieldname) {
    $input = html_writer::empty_tag('input', array(
        'type' => 'number',
        'name' => $fieldname,
        'value' => $editrecord->{$fieldname},
        'size' => 10,
    ));
    $formtable->data[] = array($fieldname, $input);
}

foreach ($textfields as $fieldname) {
    $input = html_writer::empty_tag('input', array(
        'type' => 'text',
        'name' => $fieldname,
        'value' => $editrecord->{$fieldname},
        'size' => 120,
    ));
    $formtable->data[] = array($fieldname, $input);
}

echo html_writer::start_tag('form', array(
    'method' => 'post',
    'action' => $pageurl->out(false),
));
echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'action', 'value' => 'save'));
echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'id', 'value' => $editrecord->id));
echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()));
echo html_writer::table($formtable);
echo html_writer::empty_tag('input', array('type' => 'submit', 'class' => 'btn btn-primary', 'value' => get_string('savechanges')));
if ($editrecord->id) {
    echo ' ';
    echo html_writer::link($pageurl, get_string('cancel'), array('class' => 'btn btn-secondary'));
}
echo html_writer::end_tag('form');

echo $OUTPUT->footer();
