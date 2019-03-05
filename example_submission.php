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
require_once __DIR__.'/example_submission_form.php';

$courseid = required_param('courseid', PARAM_INT);
$exampleid = required_param('exampleid', PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_exacomp', $courseid);
}

// error if example does not exist or was created by somebody else
if (!$example = $DB->get_record('block_exacompexamples', array('id' => $exampleid))) {
	print_error('invalidexample', 'block_exacomp', $exampleid);
}

require_login($course);

$itemInformation = block_exacomp_get_current_item_for_example($USER->id, $exampleid);
if ($itemInformation && !optional_param('newsubmission', false, PARAM_BOOL)) {
	// edit url
	// $url = new moodle_url("/blocks/exaport/item.php",array("courseid"=>$courseid,"action"=>"edit","sesskey"=>sesskey(),"id"=>$itemInformation->id,"descriptorselection"=>false));
	// view url + comments
	$url = new moodle_url("/blocks/exaport/shared_item.php",array("courseid"=>$courseid,"access"=>"portfolio/id/".$itemInformation->userid,"itemid"=>$itemInformation->id));
	redirect($url);
}

$context = context_course::instance($courseid);

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/example_submission.php', array('courseid' => $courseid,'exampleid' => $exampleid));
$PAGE->set_heading(block_exacomp_get_string('blocktitle'));
$PAGE->set_pagelayout('embedded');

// build breadcrumbs navigation
$coursenode = $PAGE->navigation->find($courseid, navigation_node::TYPE_COURSE);
$blocknode = $coursenode->add(block_exacomp_get_string('blocktitle'));
$blocknode->make_active();

// build tab navigation & print header
$output = block_exacomp_get_renderer();
echo $output->header($context, $courseid, '', false);

/* CONTENT REGION */

$studentExamples = block_exacomp_get_user_examples_by_course($USER, $courseid);

require_once $CFG->dirroot . '/blocks/exaport/inc.php';
if(!empty($studentExamples->teacher[$exampleid])) {
	if(($itemInformation && !block_exaport_item_is_resubmitable($itemInformation->id)) || (!$itemInformation && !block_exaport_example_is_submitable($exampleid)))
		die(block_exacomp_get_string('isgraded'));
}

$isTeacher = block_exacomp_is_teacher();
$visible_solution = block_exacomp_is_example_solution_visible($courseid, $exampleid, $USER->id);
$form = new block_exacomp_example_submission_form($_SERVER['REQUEST_URI'],
                        array(  'exampleid' => $exampleid,
                                'isTeacher' => $isTeacher,
                                'studentid' => $USER->id,
                                'visible_solution' => $visible_solution));

if ($formdata = $form->get_data()) {
	
	$type = 'file';
	
	//store item in the right portfolio category
	$course_category = block_exaport_get_user_category($course->fullname, $USER->id);
		
	if(!$course_category) {
		$course_category = block_exaport_create_user_category($course->fullname, $USER->id,0, $course->id);
	}
	
	$exampletitle = $DB->get_field('block_exacompexamples', 'title', array('id'=>$exampleid));
	$subjecttitle = block_exacomp_get_subjecttitle_by_example($exampleid);
	$subject_category = block_exaport_get_user_category($subjecttitle, $USER->id);
	if(!$subject_category) {

		$subject_category = block_exaport_create_user_category($subjecttitle, $USER->id, $course_category->id);
	}
	
	if(!empty($formdata->url))
		$formdata->url = (filter_var($formdata->url, FILTER_VALIDATE_URL) == TRUE) ? $formdata->url : "http://" . $formdata->url;
	
	$itemid = $DB->insert_record("block_exaportitem", array('userid'=>$USER->id,'name'=>$formdata->name,'url'=>$formdata->url,'intro'=>$formdata->intro,'type'=>$type,'timemodified'=>time(),'categoryid'=>$subject_category->id, 'courseid' => $courseid));
	//autogenerate a published view for the new item
	$exampleTitle = $DB->get_field('block_exacompexamples','title',array("id"=>$exampleid));
	
	$dbView = new stdClass();
	$dbView->userid = $USER->id;
	$dbView->name = $exampleTitle;
	$dbView->timemodified = time();
	$dbView->layout = 1;
	// generate view hash
	do {
		$hash = substr(md5(microtime()), 3, 8);
	} while ($DB->record_exists("block_exaportview", array("hash"=>$hash)));
	$dbView->hash = $hash;
	
	$dbView->id = $DB->insert_record('block_exaportview', $dbView);
		
	//share the view with teachers
	block_exaport_share_view_to_teachers($dbView->id, $courseid);
		
	//add item to view
	$DB->insert_record('block_exaportviewblock',array('viewid'=>$dbView->id,'positionx'=>1, 'positiony'=>1, 'type'=>'item', 'itemid'=>$itemid));
	
	if(isset($formdata->file)) {
		$filename = $form->get_new_filename('file');
		$context = context_user::instance($USER->id);
		try {
			$form->save_stored_file('file', $context->id, 'block_exaport', 'item_file', $itemid, '/', $filename, true);
		} catch (Exception $e) {
			//some problem with the file occured
		}
	}
	$timecreated = time();
	$DB->insert_record('block_exacompitemexample',array('exampleid'=>$exampleid,'itemid'=>$itemid,'timecreated'=>$timecreated,'status'=>0));

	block_exacomp_notify_all_teachers_about_submission($courseid, $exampleid, $timecreated);
	
	\block_exacomp\event\example_submitted::log(['objectid' => $exampleid, 'courseid' => $courseid]);

	echo $output->popup_close_and_reload();
	exit;
}else if($form->is_cancelled()){
    echo $output->popup_close_and_reload();
    exit;
}

$form->display();

/* END CONTENT REGION */
echo $output->footer();
