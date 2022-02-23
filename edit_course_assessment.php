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

global $DB, $OUTPUT, $PAGE, $CFG;

require __DIR__ . '/inc.php';
require_once($CFG->dirroot . "/lib/datalib.php");

$courseid = required_param('courseid', PARAM_INT);
$action = optional_param('action', "", PARAM_ALPHAEXT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_simplehtml', $courseid);
}

block_exacomp_require_login($course);

$context = context_course::instance($courseid);

block_exacomp_require_teacher($context);

/* PAGE IDENTIFIER - MUST BE CHANGED. Please use string identifier from lang file */
$page_identifier = 'tab_teacher_settings_course_assessment';

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/edit_course_assessment.php', array('courseid' => $courseid));
$PAGE->set_heading(block_exacomp_get_string('blocktitle'));
$PAGE->set_title(block_exacomp_get_string($page_identifier));

// build breadcrumbs navigation
block_exacomp_build_breadcrum_navigation($courseid);

// build tab navigation & print header
$output = block_exacomp_get_renderer();
echo $output->header_v2('tab_teacher_settings');
echo $OUTPUT->tabtree(block_exacomp_build_navigation_tabs_settings($courseid), $page_identifier);

// get the possible preconfigurations from the settings_preconfiguration.xml and create choices for the dropdown select menu
$preconfigurations = block_exacomp_read_preconfigurations_xml();
$choices = array('0' => block_exacomp_get_string('course_assessment_use_global'));
$preconfigurations = block_exacomp_read_preconfigurations_xml();
if ($preconfigurations && is_array($preconfigurations)) {
    foreach ($preconfigurations as $key => $config) {
        $choices[$key] = $config['name'];
    }
}

if ($action == 'save') {
    require_sesskey();
    //    $settings = $DB->get_record(BLOCK_EXACOMP_DB_SETTINGS, array("courseid" => $courseid));
    $settings = block_exacomp_get_settings_by_course($courseid);
    $settings->assessmentconfiguration = optional_param('selection_preconfig', 0, PARAM_INT);
    $settings->filteredtaxonomies = json_encode($settings->filteredtaxonomies); // TODO: why like this? Is this done at every location? Then why not in the function.. copied from edit_course.php
    block_exacomp_save_coursesettings($courseid, $settings);
}

/* CONTENT REGION */
$courseSettings = block_exacomp_get_settings_by_course($courseid);
echo $output->edit_course_assessment($choices, $courseid, $courseSettings->assessmentconfiguration);

/* END CONTENT REGION */
echo $output->footer();
