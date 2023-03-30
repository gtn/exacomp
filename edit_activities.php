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

use block_exacomp\data_importer;

require __DIR__ . '/inc.php';
// temporary disabled (there are errors)
if (11 == 22) {
    require_once __DIR__ . '/backup/test_backup.php';
    require_once __DIR__ . '/backup/test_restore.php';
}
require_once __DIR__ . '/classes/data.php';

global $DB, $OUTPUT, $PAGE, $CFG, $COURSE, $USER;

// TODO: was macht das? wieso brauchen wir das?
if (strcmp("mysql", $CFG->dbtype) == 0) {
    $sql5 = "SET @@group_concat_max_len = 10125012";

    $DB->execute($sql5);
}

$courseid = required_param('courseid', PARAM_INT);
$action = optional_param("action", "", PARAM_TEXT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_simplehtml', $courseid);
}

block_exacomp_require_login($course);

$slicemodulelist = false;
$columngroupnumber = null;
if (get_config('exacomp', 'disable_js_edit_activities')) {
    $columngroupnumber = optional_param('colgroupid', 0, PARAM_INT);
    if ($columngroupnumber > -1) { // -1 - show all!
        $slicemodulelist = true;
        $slicestartposition = $columngroupnumber * BLOCK_EXACOMP_MODULES_PER_COLUMN;
    }
}

$context = context_course::instance($courseid);

block_exacomp_require_teacher($context);

/* PAGE IDENTIFIER - MUST BE CHANGED. Please use string identifier from lang file */
$page_identifier = 'tab_teacher_settings_assignactivities';

$page_params = array('courseid' => $courseid);
if ($columngroupnumber !== null) {
    $page_params['colgroupid'] = $columngroupnumber;
}

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/edit_activities.php', $page_params);
$PAGE->set_heading(block_exacomp_get_string('blocktitle'));
$PAGE->set_title(block_exacomp_get_string($page_identifier));

// build breadcrumbs navigation
block_exacomp_build_breadcrum_navigation($courseid);

$headertext = "";
$img = new moodle_url('/blocks/exacomp/pix/three.png');

if ($action == "save") {
    require_sesskey();

    $activitiesData = array();
    if ($_POST['data']) {
        foreach ($_POST['data'] as $activityid => $activity) {
            $newActivity = array(); // to throw away all bad keys
            $activityid = clean_param($activityid, PARAM_INT);
            foreach ($activity as $descriptorid => $descriptor) {
                $descriptorid = clean_param($descriptorid, PARAM_INT);
                $newActivity[$descriptorid] = clean_param($descriptor, PARAM_INT);
            }
            $activitiesData[$activityid] = $newActivity;
        }
    }

    $activitiesTopicData = array();
    if ($_POST['topicdata']) {
        foreach ($_POST['topicdata'] as $activityid => $activity) {
            $newActivity = array(); // to throw away all bad keys
            $activityid = clean_param($activityid, PARAM_INT);
            foreach ($activity as $topicid => $topic) {
                $topicid = clean_param($topicid, PARAM_INT);
                $newActivity[$topicid] = clean_param($topic, PARAM_INT);
            }
            $activitiesTopicData[$activityid] = $newActivity;
        }
    }

    // delete old relations only from this page (some can be hidden)
    if (isset($activitiesData)) {
        foreach ($activitiesData as $cmoduleKey => $comps) {
            if (!empty($cmoduleKey)) {
                block_exacomp_delete_competences_activities($cmoduleKey, 0);
            }
        }
    }
    if (isset($activitiesTopicData)) {
        foreach ($activitiesTopicData as $cmoduleKey => $comps) {
            if (!empty($cmoduleKey)) {
                block_exacomp_delete_competences_activities($cmoduleKey, 1);
            }
        }
    }
    // delete all realtion for course
    //block_exacomp_delete_competences_activities();
    // DESCRIPTOR DATA
    block_exacomp_save_competences_activities($activitiesData, $courseid, 0);
    // TOPIC DATA
    block_exacomp_save_competences_activities($activitiesTopicData, $courseid, 1);

    if (!isset($_POST['data']) && !isset($_POST['topicdata'])) {
        $headertext = html_writer::div(block_exacomp_get_string("tick_some"), 'alert alert-danger');
    } else {
        $headertext = html_writer::div(block_exacomp_get_string("save_success"), 'alert alert-success')
            . html_writer::empty_tag('img', array('src' => $img, 'alt' => '', 'width' => '60px', 'height' => '60px'))
            . block_exacomp_get_string('completed_config');

        $students = block_exacomp_get_students_by_course($courseid);
        if (empty($students)) {
            $headertext .= html_writer::empty_tag('br')
                . html_writer::link(new moodle_url('/user/index.php', array('id' => $courseid)),
                    block_exacomp_get_string('optional_step'));
        }
    }
} else {
    $headertext = html_writer::empty_tag('img', array('src' => $img, 'alt' => '', 'width' => '60px', 'height' => '60px'))
        . block_exacomp_get_string('teacher_third_configuration_step')
        . html_writer::link(new moodle_url('/blocks/exacomp/edit_course.php', array('courseid' => $courseid)), block_exacomp_get_string('teacher_third_configuration_step_link'));
}

if ($action == "import") {
    require_sesskey();
    $template = required_param('template', PARAM_INT);
    $backuprecords = $DB->get_records_sql('
            SELECT DISTINCT mm.activityid
			FROM {' . BLOCK_EXACOMP_DB_COMPETENCE_ACTIVITY . '} mm
			JOIN {course_modules} m ON m.id = mm.activityid
			WHERE ' . $template . ' = m.course AND m.deletioninprogress = 0');
    $records = $DB->get_records_sql('
            SELECT mm.compid, mm.comptype, mm.activityid, mm.activitytitle, m.module
			FROM {' . BLOCK_EXACOMP_DB_COMPETENCE_ACTIVITY . '} mm
			JOIN {course_modules} m ON m.id = mm.activityid
			WHERE ' . $template . ' = m.course AND m.deletioninprogress = 0');
    foreach ($backuprecords as $record) {
        $backupid = moodle_backup($record->activityid, $USER->id);
        moodle_restore($backupid, $COURSE->id, $USER->id);
    }
    foreach ($records as $record) {
        $activityid = data_importer::get_new_activity_id($record->activitytitle, $record->module, $COURSE->id);
        block_exacomp_set_compactivity($activityid, $record->compid, $record->comptype, $record->activitytitle);
    }

}

// build tab navigation & print header
$output = block_exacomp_get_renderer();
echo $output->header($context, $courseid, 'tab_teacher_settings');
echo $OUTPUT->tabtree(block_exacomp_build_navigation_tabs_settings($courseid), $page_identifier);

$subjects = block_exacomp_get_competence_tree($courseid, null, null, true, null, false, array(), false, true);

$modules = $allModules = block_exacomp_get_allowed_course_modules_for_course($COURSE->id);

$visible_modules = [];

$colselector = $output->students_column_selector(count($allModules), 'edit_activities');

if ($modules) {
    if ($slicemodulelist) {
        if (count($modules) < ($columngroupnumber * BLOCK_EXACOMP_MODULES_PER_COLUMN)) {
            $slicestartposition = 0;
        }
        $modules = array_slice($modules, $slicestartposition, BLOCK_EXACOMP_MODULES_PER_COLUMN);
    }

    foreach ($modules as $module) {
        $compsactiv = $DB->get_records('block_exacompcompactiv_mm', array('activityid' => $module->id, 'eportfolioitem' => 0));

        $module->descriptors = array();
        $module->topics = array();

        foreach ($compsactiv as $comp) {
            if ($comp->comptype == 0) {
                $module->descriptors[$comp->compid] = $comp->compid;
            } else {
                $module->topics[$comp->compid] = $comp->compid;
            }
        }

        $visible_modules[] = $module;
    }


    $topics_set = block_exacomp_get_topics_by_subject($courseid, null, true);

    if (!$topics_set) {
        echo $output->activity_legend($headertext);
        echo $output->transfer_activities();
        echo $output->no_topics_warning();
    } else if (count($visible_modules) == 0) {
        echo $output->activity_legend($headertext);
        echo $output->transfer_activities();
        echo $output->no_course_activities_warning();
    } else {
        echo $output->activity_legend($headertext);
        echo $output->transfer_activities();
        echo $colselector;
        echo $output->activity_content($subjects, $visible_modules, $courseid);
    }
} else {
    echo $output->transfer_activities();
}

/* END CONTENT REGION */
echo $output->footer();
