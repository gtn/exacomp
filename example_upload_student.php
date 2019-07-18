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

require __DIR__.'/inc.php';
require_once __DIR__.'/example_upload_student_form.php';

global $DB, $OUTPUT, $PAGE, $USER;

$courseid = required_param('courseid', PARAM_INT);
$exampleid = optional_param('exampleid', 0, PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_exacomp', $courseid);
}

// error if example does not exist or was created by somebody else
if ($exampleid && (!$example = $DB->get_record('block_exacompexamples', array('id' => $exampleid)))
		&& $example->creatorid != $USER->id) {
	print_error('invalidexample', 'block_exacomp', $exampleid);
}

require_login($course);

$item = $DB->get_record('block_exacompitemexample', array("exampleid"=>$exampleid),'*',IGNORE_MULTIPLE);
if($exampleid && $item) {
	$url = new moodle_url("/blocks/exaport/item.php",array("courseid"=>$courseid,"action"=>"edit","sesskey"=>sesskey(),"id"=>$item->itemid));
	redirect($url);
}


$context = context_course::instance($courseid);

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/example_upload_student.php', array('courseid' => $courseid));
$PAGE->set_heading(block_exacomp_get_string('blocktitle'));

// build breadcrumbs navigation
$coursenode = $PAGE->navigation->find($courseid, navigation_node::TYPE_COURSE);
$blocknode = $coursenode->add(block_exacomp_get_string('blocktitle'));
$blocknode->make_active();

$action = optional_param('action', 'add', PARAM_TEXT);

if($action == 'serve') {
	print_error('this function is not available anymore');
}
// build tab navigation & print header
$output= block_exacomp_get_renderer();
$output->header($context, $course, '', false);
/* CONTENT REGION */

$taxonomies = $DB->get_records_menu("block_exacomptaxonomies",null,"","id, title");
$taxonomies = array_merge(array("0" => ""),$taxonomies);

$example_descriptors = array();
if($exampleid>0)
	$example_descriptors = $DB->get_records(BLOCK_EXACOMP_DB_DESCEXAMP,array('exampid'=>$exampleid),'','descrid');

$tree = block_exacomp_build_example_association_tree($courseid, $example_descriptors, $exampleid, 0);

$form = new block_exacomp_example_upload_student_form($_SERVER['REQUEST_URI'], array("taxonomies"=>$taxonomies,"tree"=>$tree,"exampleid"=>$exampleid, "task"=>($task = block_exacomp_get_file_url($example, 'example_task')) ? $task : null,
		"solution"=>($solution = block_exacomp_get_file_url($example, 'example_solution')) ? $solution : null) );

if($formdata = $form->get_data()) {

	$newExample = new stdClass();
	$newExample->title = $formdata->title;
	$newExample->description = $formdata->description;
	$newExample->creatorid = $USER->id;
	$newExample->externalurl = $formdata->externalurl;
	$newExample->source = BLOCK_EXACOMP_EXAMPLE_SOURCE_USER;

	if($formdata->exampleid == 0)
		$newExample->id = $DB->insert_record('block_exacompexamples', $newExample);
	else {
		//update example
		$newExample->id = $formdata->exampleid;
		$DB->update_record('block_exacompexamples', $newExample);
		$DB->delete_records(BLOCK_EXACOMP_DB_DESCEXAMP,array('exampid' => $newExample->id));
	}

	//add descriptor association
	if(isset($_POST['descriptor'])){
		foreach($_POST['descriptor'] as $descriptorid){
			$record = $DB->get_record(BLOCK_EXACOMP_DB_DESCEXAMP, array('descrid'=>$descriptorid, 'exampid'=>$newExample->id));
			if(!$record)
				$DB->insert_record(BLOCK_EXACOMP_DB_DESCEXAMP, array('descrid'=>$descriptorid, 'exampid'=> $newExample->id));
		}
	}

	// save file
   require_once $CFG->dirroot . '/blocks/exaport/inc.php';

	if ($form->get_new_filename('file'))
		$type = 'file';
	else
		$type = 'url';

   //store item in the right portfolio category
	$course_category = block_exaport_get_user_category($course->fullname, $USER->id);
	if(!$course_category) {
		//$course_category = block_exaport_create_user_category($course->fullname, $USER->id);
	}

	$subjecttitle = block_exacomp_get_subjecttitle_by_example($newExample->id);
	$subject_category = block_exaport_get_user_category($subjecttitle, $USER->id);
	if(!$subject_category) {
		//$subject_category = block_exaport_create_user_category($subjecttitle, $USER->id, $course_category->id);
	}

	$itemid = $DB->insert_record("block_exaportitem", array('userid'=>$USER->id,'name'=>$formdata->title,'url'=>$formdata->externalurl,'intro'=>$formdata->description,'type'=>$type,'timemodified'=>time(),'categoryid'=>$subject_category->id));

	{
		// autogenerate a published view for the new item
		$dbView = new stdClass();
		$dbView->userid = $USER->id;
		$dbView->name =  $newExample->title;
		$dbView->timemodified = time();
		$dbView->layout = 1;
		// generate view hash
		do {
			$hash = substr(md5(microtime()), 3, 8);
		} while ($DB->record_exists("block_exaportview", array("hash"=>$hash)));
		$dbView->hash = $hash;

		$dbView->id = $DB->insert_record('block_exaportview', $dbView);
		//share the view with teachers
		$teachers = block_exaport_share_view_to_teachers($dbView->id, $courseid);

		//add item to view
		$DB->insert_record('block_exaportviewblock',array('viewid'=>$dbView->id,'positionx'=>1, 'positiony'=>1, 'type'=>'item', 'itemid'=>$itemid));
	}

	if ($filename = $form->get_new_filename('file')) {
		$context = context_user::instance($USER->id);
		try {
			$form->save_stored_file('file', $context->id, 'block_exaport', 'item_file', $itemid, '/', $filename, true);
		} catch (Exception $e) {
			//some problem with the file occured
		}
	}

	$DB->insert_record('block_exacompitemexample',array('exampleid'=>$newExample->id,'itemid'=>$itemid,'timecreated'=>time(),'status'=>0));

	// add to weekly schedule
	block_exacomp_add_example_to_schedule($USER->id, $newExample->id, $USER->id, $courseid,null,null,-1,-1,'S');
	block_exacomp_settstamp();
	echo $output->popup_close_and_reload();
	exit;
}else if($form->is_cancelled()){
    echo $output->popup_close_and_reload();
    exit;
}



if($exampleid > 0) {
    $example->descriptors = $DB->get_fieldset_select(BLOCK_EXACOMP_DB_DESCEXAMP, 'descrid', 'exampid = ?',array($exampleid));
	$form->set_data($example);
}

$form->display();

/* END CONTENT REGION */
echo $output->footer();
