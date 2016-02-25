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
$page_identifier = 'tab_help';

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/help.php', array('courseid' => $courseid));
$PAGE->set_heading(get_string('pluginname', 'block_exacomp'));
$PAGE->set_title(get_string($page_identifier, 'block_exacomp'));

// build breadcrumbs navigation
block_exacomp_build_breadcrum_navigation($courseid);

// build tab navigation & print header
$output = block_exacomp_get_renderer();
echo $output->header($context,$courseid, $page_identifier);

/* CONTENT REGION */

echo $OUTPUT->box('<div class="helpdiv">'.text_to_html(get_string("help_content", "block_exacomp")).'</div>');

/* END CONTENT REGION */
echo $output->footer();
