<?php
/*
 * copyright exabis
 */

require __DIR__.'/inc.php';

$courseid = required_param('courseid', PARAM_INT);

require_login($courseid);

/* PAGE IDENTIFIER - MUST BE CHANGED. Please use string identifier from lang file */
$page_identifier = 'tab_cross_subjects_overview';

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/cross_subjects_overview.php', array('courseid' => $courseid));
$PAGE->set_heading(get_string('pluginname', 'block_exacomp'));
$PAGE->set_title(get_string($page_identifier, 'block_exacomp'));

// build breadcrumbs navigation
block_exacomp_build_breadcrum_navigation($courseid);

$output = block_exacomp_get_renderer();

// build tab navigation & print header
echo $output->header_v2('tab_cross_subjects');

if (block_exacomp_is_teacher() || block_exacomp_is_admin()) {
	$course_crosssubs = block_exacomp_get_cross_subjects_by_course($courseid);
	echo $output->cross_subjects_overview_teacher($course_crosssubs);
} else {
	$course_crosssubs = block_exacomp_get_cross_subjects_by_course($courseid, $USER->id);
	echo $output->cross_subjects_overview_student($course_crosssubs);
}

/* END CONTENT REGION */
echo $output->footer();
