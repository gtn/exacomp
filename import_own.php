<?php
ini_set('max_execution_time', 3000);
require_once dirname(__FILE__) . '/inc.php';
require_once dirname(__FILE__) . '/lib/div.php';
require_once dirname(__FILE__) . '/lib/xmllib.php';

global $USER, $CFG, $OUTPUT;
$content = "";

$courseid = optional_param('courseid', 0, PARAM_INT);
require_login($courseid);

$context = get_context_instance(CONTEXT_SYSTEM);
$action = optional_param('action', "", PARAM_ALPHA);

require_capability('block/exacomp:admin', $context);

$url = '/blocks/exacomp/import_own.php';
$PAGE->set_url($url);
$url = $CFG->wwwroot.$url;
$returnurl = $CFG->wwwroot . '/blocks/exacomp/import.php?courseid=' . $courseid;
block_exacomp_print_header("admin", "admintabimport");

echo "<div class='block_excomp_center'>";

if($action == "xml") {
	$import = block_exacomp_xml_do_import();
}

require_once("{$CFG->dirroot}/blocks/exacomp/lib/xml_upload_form.php");

$mform = new block_exacomp_xml_upload_form();
if ($mform->is_cancelled()) {
	redirect($returnurl);
} else if ($data = $mform->get_file_content('file')) {
	if(block_exacomp_xml_do_import($data,$USER->id))
		echo $OUTPUT->box(text_to_html(get_string("importsuccess_own", "block_exacomp")));
	else {
		echo $OUTPUT->box(text_to_html(get_string("importfail", "block_exacomp")));
		$mform->display();
	}
		
} else
	$mform->display();

echo "</div>";
echo $OUTPUT->footer();