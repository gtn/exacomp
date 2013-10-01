<?php

/* * *************************************************************
 *  Copyright notice
*
*  (c) 2011 exabis internet solutions <info@exabis.at>
*  All rights reserved
*
*  You can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  This module is based on the Collaborative Moodle Modules from
*  NCSA Education Division (http://www.ncsa.uiuc.edu)
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
* ************************************************************* */

require_once dirname(__FILE__) . '/inc.php';
require_once dirname(__FILE__) . '/lib/div.php';
require_once dirname(__FILE__) . '/example_upload_form.php';

$courseid = required_param('courseid', PARAM_INT);
$action = optional_param('action', 'add', PARAM_TEXT);
require_login($courseid);

$context = get_context_instance(CONTEXT_COURSE,$courseid);

require_login($courseid);

$url = '/blocks/exacomp/example_upload.php';

if($action == 'serve') {
	
	$contextid = required_param('c', PARAM_INT);
	$itempathnamehash = required_param('i', PARAM_TEXT);
	
	$fs = get_file_storage();
	send_stored_file($fs->get_file_by_hash($itempathnamehash));
	die;
}
require_capability('block/exacomp:teacher', $context);
$descrid = required_param('descrid', PARAM_INT);
$PAGE->set_url($url);
$PAGE->set_title(get_string("example_upload_header", "block_exacomp", $DB->get_field('block_exacompdescriptors','title',array("id"=>$descrid))));
$PAGE->set_context($context);

$form = new block_exacomp_example_upload_form($_SERVER['REQUEST_URI'], array("descrid" => $descrid));

if($formdata = $form->get_data()) {
	
	$newExample = new stdClass();
	$newExample->title = $formdata->name;
	$newExample->description = $formdata->intro;
	$newExample->taxid = 0;
	$newExample->creatorid = $USER->id;
	$newExample->source = CUSTOM_EXAMPLES_SOURCE;
	// save file
	$context = get_context_instance(CONTEXT_USER, $USER->id);
	$fs = get_file_storage();
	if($fs->file_exists($context->id, 'user', 'private', 0, '/', $form->get_new_filename('file'))) {
	}
	else {
		$form->save_stored_file('file', $context->id, 'user', 'private', 0, '/', $form->get_new_filename('file'), true);
		
	}
	$pathnamehash = $fs->get_pathname_hash($context->id, 'user', 'private', 0, '/', $form->get_new_filename('file'));
	
	/*
	file_save_draft_area_files($formdata->file, $context->id, 'user', 'private', 0, array('subdirs' => 1, 'maxbytes' => $CFG->userquota, 'maxfiles' => -1, 'accepted_types' => '*'));

	// find out the filename, so we can get the pathnamehash
	$filename = $DB->get_field("files", "filename", array("itemid"=>$formdata->file), IGNORE_MULTIPLE);
	$pathnamehash = $DB->get_field("files", "pathnamehash", array("filename"=>$filename,"component"=>"user","filearea"=>"private"));
	*/
	
	// insert example
	$task = new moodle_url($CFG->wwwroot.'/blocks/exacomp/example_upload.php',array("action"=>"serve","c"=>$context->id,"i"=>$pathnamehash,"courseid"=>$courseid));
	$newExample->task = $task->out(false);
	
	$newExample->id = $DB->insert_record('block_exacompexamples', $newExample);
	
	$DB->insert_record('block_exacompdescrexamp_mm', array('descrid' => $formdata->descrid, 'exampid' => $newExample->id));
	
	?>
	<script type="text/javascript">
		window.opener.Exacomp.newExampleAdded();
		window.close();
	</script>
	<?php 
}
// if form is submitted add data and close window
echo $OUTPUT->header();

$form->display();

echo $OUTPUT->footer();