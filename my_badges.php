<?php
/*
 * copyright exabis
 */

require __DIR__.'/inc.php';

global $DB, $OUTPUT, $PAGE;

$courseid = required_param('courseid', PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_simplehtml', $courseid);
}

require_login($course);

$context = context_course::instance($courseid);

/* PAGE IDENTIFIER - MUST BE CHANGED. Please use string identifier from lang file */
$page_identifier = 'tab_badges';

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/my_badges.php', array('courseid' => $courseid));
$PAGE->set_heading(get_string('pluginname', 'block_exacomp'));
$PAGE->set_title(get_string($page_identifier, 'block_exacomp'));

// build breadcrumbs navigation
block_exacomp_build_breadcrum_navigation($courseid);

// build tab navigation & print header
$output = block_exacomp_get_renderer();
echo $output->header($context,$courseid, $page_identifier);

/* CONTENT REGION */
if (!block_exacomp_moodle_badges_enabled()) {
	error("Badges library not found, please upgrade your Moodle to 2.5");
	exit;
}

block_exacomp_award_badges($courseid, $USER->id);
$badges = block_exacomp_get_user_badges($courseid, $USER->id);


echo $output->my_badges($badges);

/* END CONTENT REGION */

echo $output->footer();
