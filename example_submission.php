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

global $DB, $OUTPUT, $PAGE, $USER, $COURSE;

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


$item = $DB->get_record('block_exacompitemexample', array("exampleid"=>$exampleid),'*',IGNORE_MULTIPLE);
if($item != null)
{
	$url = new moodle_url("/blocks/exaport/item.php",array("courseid"=>$courseid,"action"=>"edit","sesskey"=>sesskey(),"id"=>$item->itemid));
	header("Location: " . str_replace('amp;','', $url->__toString()) );
	die();
}

$context = context_course::instance($courseid);

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/example_submission.php', array('courseid' => $courseid,'exampleid' => $exampleid));
$PAGE->set_heading(get_string('pluginname', 'block_exacomp'));

block_exacomp_init_js_css();

// build breadcrumbs navigation
$coursenode = $PAGE->navigation->find($courseid, navigation_node::TYPE_COURSE);
$blocknode = $coursenode->add(get_string('pluginname','block_exacomp'));
$blocknode->make_active();

// build tab navigation & print header
echo $OUTPUT->header();
echo '<div id="block_exacomp">';
/* CONTENT REGION */

$form = new block_exacomp_example_submission_form($_SERVER['REQUEST_URI'], array("exampleid"=>$exampleid));

if($formdata = $form->get_data()) {
	require_once $CFG->dirroot . '/blocks/exaport/lib/lib.php';
	
	if(isset($formdata->file))
		$type = 'file';
	else
		$type = 'url';
	
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
	$teachers = share_view_to_teachers($dbView->id, $courseid);
		
	//add item to view
	$DB->insert_record('block_exaportviewblock',array('viewid'=>$dbView->id,'positionx'=>1, 'positiony'=>1, 'type'=>'item', 'itemid'=>$itemid));
	
	
	if($type == "file") {
		
		$fs = get_file_storage();

		$filename = $form->get_new_filename('file');
		$pathnamehash = $fs->get_pathname_hash($context->id, 'user', 'private', 0, '/', $filename);
		$context = context_user::instance($USER->id);
		try {
			$form->save_stored_file('file', $context->id, 'block_exaport', 'item_file', $itemid, '/', $filename, true);
		} catch (Exception $e) {
			//some problem with the file occured
		}
	}
	
	$DB->insert_record('block_exacompitemexample',array('exampleid'=>$exampleid,'itemid'=>$itemid,'timecreated'=>time(),'status'=>0));

	if($teachers) {
		foreach($teachers as $teacher) {
			
 			$notification = new stdClass();
			$notification->component        = 'block_exacomp';
			$notification->name             = 'submission';
			$notification->userfrom         = $USER;
			$notification->userto           = $DB->get_record('user', array('id' => $teacher));
			$notification->subject          = get_string('example_submission_subject', 'block_exacomp');
			$notification->fullmessageformat 	= FORMAT_HTML;
			$notification->fullmessage  	= get_string('example_submission_message', 'block_exacomp', array('course' => $COURSE->fullname, 'student' => fullname($USER)));
			$notification->fullmessagehtml = $notification->fullmessage;
			$notification->smallmessage     = '';
			$notification->notification     = 1;
			
			$mailtext = get_string('example_submission_message', 'block_exacomp', array('course' => $COURSE->fullname, 'student' => fullname($USER)));
			email_to_user($DB->get_record('user', array('id' => $teacher)), $USER, get_string('example_submission_subject', 'block_exacomp'), strip_tags($mailtext), $mailtext);
		}
		
	}
?>
<script type="text/javascript">
		window.opener.Exacomp.newExampleAdded();
		window.close();
	</script>
<?php 
}

$form->display();

/* END CONTENT REGION */
echo '</div>';
echo $OUTPUT->footer();

?>