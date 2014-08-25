<?php

/* * *************************************************************
 *  Copyright notice
*
*  (c) 2014 exabis internet solutions <info@exabis.at>
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

require_once dirname(__FILE__)."/inc.php";
require_once dirname(__FILE__) . '/example_upload_form.php';

global $DB, $OUTPUT, $PAGE;

$courseid = required_param('courseid', PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_simplehtml', $courseid);
}

require_login($course);

$context = context_course::instance($courseid);

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/example_upload.php', array('courseid' => $courseid));
$PAGE->set_heading(get_string('pluginname', 'block_exacomp'));

block_exacomp_init_js_css();

// build breadcrumbs navigation
$coursenode = $PAGE->navigation->find($courseid, navigation_node::TYPE_COURSE);
$blocknode = $coursenode->add(get_string('pluginname','block_exacomp'));
$blocknode->make_active();

$action = optional_param('action', 'add', PARAM_TEXT);

if($action == 'serve') {

	$contextid = required_param('c', PARAM_INT);
	$itempathnamehash = required_param('i', PARAM_TEXT);

	$fs = get_file_storage();
	send_stored_file($fs->get_file_by_hash($itempathnamehash));
	die;
}
// build tab navigation & print header
echo $OUTPUT->header();

/* CONTENT REGION */

require_capability('block/exacomp:teacher', $context);
$descrid = required_param('descrid', PARAM_INT);
$topicid = required_param('topicid', PARAM_INT);

$taxonomies = $DB->get_records_menu("block_exacomptaxonomies",null,"","id, title");
$taxonomies = array_merge(array("0" => ""),$taxonomies);
$topicsub = $DB->get_record("block_exacomptopics", array("id"=>$topicid));
$topics = $DB->get_records("block_exacomptopics", array("subjid"=>$topicsub->subjid), null, 'title,id, catid');

foreach($topics as $topic){
	$topic->descriptors = $DB->get_records_sql_menu("SELECT d.id, d.title FROM {block_exacompdescriptors} d
			JOIN {block_exacompdescrtopic_mm} dt ON dt.descrid = d.id
			JOIN {block_exacomptopics} t ON dt.topicid = t.id
			WHERE t.id = ?",array($topic->id));

	if($topic->catid) {
		$topic->cattitle = $DB->get_record("block_exacompcategories", array("id"=>$topic->catid))->title;
		$topic->title = $topic->cattitle . ": " . $topic->title;
	}
}

$form = new block_exacomp_example_upload_form($_SERVER['REQUEST_URI'], array("descrid" => $descrid,"taxonomies"=>$taxonomies,"topics"=>$topics,"topicid"=>$topicid));

if($formdata = $form->get_data()) {

	$newExample = new stdClass();
	$newExample->title = $formdata->name;
	$newExample->description = $formdata->intro;
	$newExample->taxid = $formdata->tax;
	$newExample->creatorid = $USER->id;
	$newExample->externalurl = $formdata->link;
	$newExample->source = CUSTOM_EXAMPLE_SOURCE;
	// save file
	$context = context_user::instance($USER->id);
	$fs = get_file_storage();

	if($formdata->lisfilename == 1 && $form->get_new_filename('file')) {
		$filenameinfos = $DB->get_record_sql("SELECT s.numb, st.title as subjecttitle, cat.title as cattitle, cat.sourceid as catid FROM {block_exacompschooltypes} st
				JOIN {block_exacompsubjects} s ON s.stid = st.id
				JOIN {block_exacomptopics} t ON s.id = t.subjid
				JOIN {block_exacompcategories} cat ON t.catid = cat.id
				WHERE t.id = ?", array($topicid));
		//Fachkürzel
		$newfilename = substr($filenameinfos->subjecttitle,0,1);
		//$newfilename .= '_';
		//Nr Kompetenzbereich sprintf(%02d, $var);
		$newfilename .= sprintf("%02d", $filenameinfos->numb);
		//$newfilename .= '.';
		//Nr Lernfortschritt

		$newfilename .= sprintf("%02d", substr($filenameinfos->cattitle,4,1)) . ".";
		//Nr Lernwegeliste
		$newfilename .= sprintf("%02d", $filenameinfos->catid) . ".";
		$newfilename .= '_';
		//Taxonomie
		$taxname = $DB->get_field('block_exacomptaxonomies', 'title', array("id"=>$formdata->tax));
		if($taxname) {
			$newfilename .= $taxname;
			$newfilename .= '.';
		}
		//Dateiname
		$temp_filename = $newfilename;

		$newfilename .= $formdata->name . "." . pathinfo($form->get_new_filename('file'), PATHINFO_EXTENSION);
		$newsolutionname = $temp_filename . $formdata->name . "_SOLUTION." . pathinfo($form->get_new_filename('solution'), PATHINFO_EXTENSION);
		$newExample->title = $newfilename;
	}
	else {
		$newfilename = $form->get_new_filename('file');
		$newsolutionname = $form->get_new_filename('solution');
	}

	if(!$fs->file_exists($context->id, 'user', 'private', 0, '/', $newfilename))
		$form->save_stored_file('file', $context->id, 'user', 'private', 0, '/', $newfilename, true);

	$pathnamehash = $fs->get_pathname_hash($context->id, 'user', 'private', 0, '/', $newfilename);

	if(!$fs->file_exists($context->id, 'user', 'private', 0, '/', $newsolutionname))
		$form->save_stored_file('solution', $context->id, 'user', 'private', 0, '/', $newsolutionname, true);
	$solutionpathnamehash = $fs->get_pathname_hash($context->id, 'user', 'private', 0, '/', $newsolutionname);

	// insert example
	if($newfilename) {
		$task = new moodle_url($CFG->wwwroot.'/blocks/exacomp/example_upload.php',array("action"=>"serve","c"=>$context->id,"i"=>$pathnamehash,"courseid"=>$courseid));
		$newExample->task = $task->out(false);
	}
	if($newsolutionname) {
		$solution = new moodle_url($CFG->wwwroot.'/blocks/exacomp/example_upload.php',array("action"=>"serve","c"=>$context->id,"i"=>$solutionpathnamehash,"courseid"=>$courseid));
		$newExample->solution = $solution->out(false);
	}
	$newExample->id = $DB->insert_record('block_exacompexamples', $newExample);

	foreach($formdata->descriptors as $descr)
		$DB->insert_record('block_exacompdescrexamp_mm', array('descrid' => $descr, 'exampid' => $newExample->id));

	?>
<script type="text/javascript">
		window.opener.Exacomp.newExampleAdded();
		window.close();
	</script>
<?php 
}

$form->display();

/* END CONTENT REGION */

echo $OUTPUT->footer();

?>