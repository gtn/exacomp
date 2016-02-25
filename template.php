<?php

require __DIR__.'/inc.php';

$courseid = required_param('courseid', PARAM_INT);

require_login($courseid);

/* PAGE IDENTIFIER - MUST BE CHANGED. Please use string identifier from lang file */
$page_identifier = 'tab_competence_overview';

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/template.php', array('courseid' => $courseid));
$PAGE->set_heading(get_string('pluginname', 'block_exacomp'));
$PAGE->set_title(get_string($page_identifier, 'block_exacomp'));

// build breadcrumbs navigation
block_exacomp_build_breadcrum_navigation($courseid);

// build tab navigation & print header
$output = block_exacomp_get_renderer();

echo $output->header_v2($page_identifier);

/* CONTENT REGION */

echo "CONTENT";

/* END CONTENT REGION */

echo $output->footer();
