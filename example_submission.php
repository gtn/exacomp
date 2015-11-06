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
require_once dirname(__FILE__) . '/example_submission_form.php';

global $DB, $OUTPUT, $PAGE, $USER, $COURSE, $logging;

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
	$url = new moodle_url("/blocks/exaport/item.php",array("courseid"=>$courseid,"action"=>"edit","sesskey"=>sesskey(),"id"=>$itemInformation->id));
	redirect($url);
}

$context = context_course::instance($courseid);

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/example_submission.php', array('courseid' => $courseid,'exampleid' => $exampleid));
$PAGE->set_heading(get_string('pluginname', 'block_exacomp'));
$PAGE->set_pagelayout('popup');

// build breadcrumbs navigation
$coursenode = $PAGE->navigation->find($courseid, navigation_node::TYPE_COURSE);
$blocknode = $coursenode->add(get_string('pluginname','block_exacomp'));
$blocknode->make_active();

// build tab navigation & print header
$output = block_exacomp_get_renderer();
echo $output->header($context, $courseid, '', false);

/* CONTENT REGION */

$form = new block_exacomp_example_submission_form($_SERVER['REQUEST_URI'], array("exampleid"=>$exampleid));

if($formdata = $form->get_data()) {
	require_once $CFG->dirroot . '/blocks/exaport/lib/lib.php';
	
	$type = 'file';
	
	//store item in the right portfolio category
	$course_category = block_exaport_get_user_category($course->fullname, $USER->id);
		
	if(!$course_category) {
		$course_category = block_exaport_create_user_category($course->fullname, $USER->id);
	}
	
	$exampletitle = $DB->get_field('block_exacompexamples', 'title', array('id'=>$exampleid));
	$subjecttitle = block_exacomp_get_subjecttitle_by_example($exampleid);
	$subject_category = block_exaport_get_user_category($subjecttitle, $USER->id);
	if(!$subject_category) {
		$subject_category = block_exaport_create_user_category($subjecttitle, $USER->id, $course_category->id);
	}
	
	if(!empty($formdata->url))
		$formdata->url = (filter_var($formdata->url, FILTER_VALIDATE_URL) == TRUE) ? $formdata->url : "http://" . $formdata->url;
	
	$itemid = $DB->insert_record("block_exaportitem", array('userid'=>$USER->id,'name'=>$formdata->name,'url'=>$formdata->url,'intro'=>$formdata->intro,'type'=>$type,'timemodified'=>time(),'categoryid'=>$subject_category->id));
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
	share_view_to_teachers($dbView->id, $courseid);
		
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
	
	if($logging)
		$event = \block_exacomp\event\example_submitted::create(array('objectid' => $exampleid, 'contextid' => context_course::instance($courseid)->id))->trigger();

    echo $output->popup_close_and_reload();
    exit;
}

$form->display();

/* END CONTENT REGION */
echo $output->footer();

?>