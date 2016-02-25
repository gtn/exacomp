<?php
/*
 * copyright exabis
 */

require __DIR__.'/inc.php';
require_once __DIR__.'/example_upload_form.php';

global $DB, $OUTPUT, $PAGE, $USER;

$courseid = required_param('courseid', PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_exacomp', $courseid);
}

require_login($course);
block_exacomp_require_teacher($courseid);
/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/example_upload.php', array('courseid' => $courseid));
$PAGE->set_title(get_string('pluginname', 'block_exacomp'));
$PAGE->set_pagelayout('embedded');
$context = context_course::instance($courseid);
$output = block_exacomp_get_renderer();
echo $output->header($context, $courseid, '', false);

$context = context_course::instance($courseid);

echo html_writer::tag("textarea", "", array("id" => "message", "style" => "width:280px;height:180px"));
echo html_writer::tag("br", "");
echo html_writer::tag("input", "", array("type" => "submit", "value" => get_string("messagetocourse","block_exacomp"), "exa-type" => "send-message-to-course"));
