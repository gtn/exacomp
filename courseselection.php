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

global $SESSION;

$courseid = required_param('courseid', PARAM_INT);
$action = optional_param('action', "", PARAM_ALPHAEXT);

$changeFilter = optional_param('filter_submit', "", PARAM_RAW);

block_exacomp_require_login($courseid);
block_exacomp_require_teacher();

/* PAGE IDENTIFIER - MUST BE CHANGED. Please use string identifier from lang file */
$page_identifier = 'tab_teacher_settings_selection';

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/courseselection.php', array('courseid' => $courseid));
$PAGE->set_heading(block_exacomp_get_string('blocktitle'));
$PAGE->set_title(block_exacomp_get_string($page_identifier));

// build breadcrumbs navigation
block_exacomp_build_breadcrum_navigation($courseid);

$headertext = "";

$img = new moodle_url('/blocks/exacomp/pix/two.png');

//unset($SESSION->courseselection_filter); // quick reset for testing
if ($changeFilter) {
    $types = optional_param('filter_schooltype', '', PARAM_RAW);
    $filter = array();
    if ($types) {
        $types = explode(',', $types);
        $filter['schooltype'] = $types;
    }
    $filter['only_selected'] = optional_param('only_selected', 0, PARAM_INT);
    $SESSION->courseselection_filter = $filter;
} else {
    // default filters
    if (file_exists($CFG->dirroot . '/blocks/eduvidual/block_eduvidual.php')) {
        require_once($CFG->dirroot . '/blocks/eduvidual/block_eduvidual.php');
        $org = block_eduvidual::get_org_by_courseid($courseid);
        $schulkennzahl = $org->orgid; // Die orgid ist die schulkennzahl
        $userSchoolType = intval(substr($schulkennzahl, -1));
        //$userSchoolType = 2; // for testing
        $eduvidalDefaults = block_exacomp_eduvidual_defaultSchooltypes();
        if (array_key_exists($userSchoolType, $eduvidalDefaults)) {
            if ($eduvidalDefaults[$userSchoolType]['realId']) {
                $filter = array('schooltype' => [$eduvidalDefaults[$userSchoolType]['realId']]);
                $SESSION->courseselection_filter = $filter;
            }
        }
    }
}

if ($action == 'save') {
    require_sesskey();
    $topics = block_exacomp\param::optional_array('topics', [PARAM_INT]);
    block_exacomp_set_coursetopics($courseid, $topics);

    if (empty($topics)) {
        $headertext = block_exacomp_get_string('tick_some');
    } else {
        $course_settings = block_exacomp_get_settings_by_course($courseid);
        if ($course_settings->uses_activities) {
            if (block_exacomp_is_activated($courseid)) {
                if (block_exacomp_use_old_activities_method()) {
                    $linkTo = html_writer::link(new moodle_url('edit_activities.php', array('courseid' => $courseid)), block_exacomp_get_string('next_step'));
                } else {
                    $linkTo = html_writer::link(new moodle_url('activities_to_descriptors.php', array('courseid' => $courseid)), block_exacomp_get_string('next_step'));
                }
                $headertext = html_writer::div(block_exacomp_get_string("save_success"), 'alert alert-success')
                    . html_writer::empty_tag('img', array('src' => $img, 'alt' => '', 'width' => '60px', 'height' => '60px'))
                    . $linkTo;
            }
        } else {
            $headertext = html_writer::div(block_exacomp_get_string("save_success"), 'alert alert-success')
                . html_writer::empty_tag('img', array('src' => $img, 'alt' => '', 'width' => '60px', 'height' => '60px')) . block_exacomp_get_string('completed_config');

            $students = block_exacomp_get_students_by_course($courseid);
            if (empty($students))
                //				$headertext .= html_writer::empty_tag('br')
                //					.html_writer::link(new moodle_url('/enrol/users.php', array('id'=>$courseid)), block_exacomp_get_string('optional_step'));
            {
                $headertext .= html_writer::empty_tag('br')
                    . html_writer::span(block_exacomp_get_string('enrol_users'));
            }
        }
    }
} else {
    $headertext = html_writer::empty_tag('img', array('src' => $img, 'alt' => '', 'width' => '60px', 'height' => '60px')) . block_exacomp_get_string('teacher_second_configuration_step');
}

// build tab navigation & print header
$output = block_exacomp_get_renderer();
echo $output->header_v2('tab_teacher_settings');
echo $OUTPUT->tabtree(block_exacomp_build_navigation_tabs_settings($courseid), $page_identifier);
/* CONTENT REGION */

// skillsmanagemenet has a per course configuration, excaomp has a per moodle configuration (courseid = 0)
$limit_courseid = block_exacomp_is_skillsmanagement() ? $courseid : 0;

$schooltypes = block_exacomp_build_schooltype_tree_for_courseselection($limit_courseid, true);

$active_topics = block_exacomp_get_topics_by_subject($courseid, 0, true);

// filtering by "only selected grids'
if (isset($SESSION->courseselection_filter)
    && array_key_exists('only_selected', $SESSION->courseselection_filter)
    && $SESSION->courseselection_filter['only_selected'] == 1) {
    $newSchooltypes = array();
    foreach ($schooltypes as $stid => $schooltype) {
        $addSchooltype = false;
        $newSubjects = array();
        foreach ($schooltype->subjects as $sid => $subject) {
            $addSubject = false;
            foreach ($subject->topics as $topic) {
                if (!empty($active_topics[$topic->id])) {
                    $addSubject = true;
                    break;
                }
            }
            if ($addSubject) {
                $addSchooltype = true;
                $newSubjects[$sid] = $subject;
            }
        }
        if ($addSchooltype) {
            $schooltype->subjects = $newSubjects;
            $newSchooltypes[$stid] = $schooltype;
        }
    }
    $schooltypes = $newSchooltypes;
}

echo $output->courseselection($schooltypes, $active_topics, $headertext);

/* END CONTENT REGION */
echo $output->footer();
