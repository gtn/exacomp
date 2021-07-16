<?php
// This file is part of Exabis Competence Grid
//
// (c) 2016 GTN - Global Training Network GmbH <office@gtn-solutions.com>
//
// Exabis Competence Grid is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This script is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You can find the GNU General Public License at <http://www.gnu.org/licenses/>.
//
// This copyright notice MUST APPEAR in all copies of the script!

global $DB, $OUTPUT, $PAGE, $CFG;

require __DIR__.'/inc.php';
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
$page_identifier = 'tab_teacher_settings_course_grading';

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/edit_course_grading.php', array('courseid' => $courseid));
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
$choices = array('0' => block_exacomp_get_string('course_grading_use_global'));
$preconfigurations = block_exacomp_read_preconfigurations_xml();
if ($preconfigurations && is_array($preconfigurations)) {
    foreach ($preconfigurations as $key => $config) {
        $choices[$key] = $config['name'];
    }
}

if ($action == 'save') {
    $settings = block_exacomp_get_settings_by_course($courseid);
    $settings->course_grading_scheme = optional_param('selection_preconfig', 0, PARAM_INT);
    if($settings->course_grading_scheme == null || $settings->course_grading_scheme == 0){
        // use global settings
    }else{
        // use chosen settings --> Create course specific entries in a Table... to be decided
    }
}

/* CONTENT REGION */
echo $output->edit_course_grading($choices, $courseid);

/* END CONTENT REGION */
echo $output->footer();
