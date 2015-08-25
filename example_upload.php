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

global $DB, $OUTPUT, $PAGE, $USER;

$courseid = required_param('courseid', PARAM_INT);
$exampleid = optional_param('exampleid', 0, PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_exacomp', $courseid);
}

// error if example does not exist or was created by somebody else
if ($exampleid > 0 && (!$example = $DB->get_record('block_exacompexamples', array('id' => $exampleid)))
        && $example->creatorid != $USER->id) {
    print_error('invalidexample', 'block_exacomp', $exampleid);
}

require_login($course);

$context = context_course::instance($courseid);

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/example_upload.php', array('courseid' => $courseid));
$PAGE->set_heading(get_string('pluginname', 'block_exacomp'));

block_exacomp_init_js_css();
$PAGE->requires->js("/blocks/exacomp/javascript/CollapsibleLists.compressed.js");
$PAGE->requires->css("/blocks/exacomp/css/CollapsibleLists.css");

// build breadcrumbs navigation
$coursenode = $PAGE->navigation->find($courseid, navigation_node::TYPE_COURSE);
$blocknode = $coursenode->add(get_string('pluginname','block_exacomp'));
$blocknode->make_active();

$action = optional_param('action', 'add', PARAM_TEXT);

if($action == 'serve') {
    print_error('this function is not available anymore');
}
// build tab navigation & print header
echo $PAGE->get_renderer('block_exacomp')->header();
/* CONTENT REGION */

block_exacomp_require_teacher($context);
$descrid = required_param('descrid', PARAM_INT);
$topicid = required_param('topicid', PARAM_INT);

$taxonomies = $DB->get_records_menu("block_exacomptaxonomies",null,"","id, title");
$taxonomies = array_merge(array("0" => ""),$taxonomies);
$topicsub = $DB->get_record("block_exacomptopics", array("id"=>$topicid));
$topics = $DB->get_records("block_exacomptopics", array("subjid"=>$topicsub->subjid), null, 'title,id');

$example_descriptors = array();
if($exampleid>0)
	$example_descriptors = $DB->get_records(block_exacomp::DB_DESCEXAMP,array('exampid'=>$exampleid),'','descrid');

$tree = block_exacomp_build_example_association_tree($courseid, $example_descriptors, $exampleid, $descrid);

$form = new block_exacomp_example_upload_form($_SERVER['REQUEST_URI'],
            array("descrid" => $descrid,"taxonomies"=>$taxonomies,"tree"=>$tree,"topicid"=>$topicid, "exampleid"=>$exampleid));

if($formdata = $form->get_data()) {
	
    $newExample = new stdClass();
    $newExample->title = $formdata->title;
    $newExample->description = $formdata->description;
    $newExample->creatorid = $USER->id;
    $newExample->externalurl = $formdata->externalurl;
    $newExample->source = block_exacomp::EXAMPLE_SOURCE_TEACHER;
    
    if($formdata->exampleid == 0)
        $newExample->id = $DB->insert_record('block_exacompexamples', $newExample);
    else {
        //update example
        $newExample->id = $formdata->exampleid;
        $DB->update_record('block_exacompexamples', $newExample);
        $DB->delete_records('block_exacompdescrexamp_mm',array('exampid' => $newExample->id));
    }

    //TODO mehrfachauswahl
    //insert taxid in exampletax_mm
    $new_mm = new stdClass();
    $new_mm->exampleid = $newExample->id;
    $new_mm->taxid = $formdata->taxid;
    $DB->insert_record(block_exacomp::DB_EXAMPTAX, $new_mm);
    
    //add descriptor association
    if(isset($_POST['descriptor'])){
    	foreach($_POST['descriptor'] as $descriptorid){
    		$record = $DB->get_record(block_exacomp::DB_DESCEXAMP, array('descrid'=>$descriptorid, 'exampid'=>$newExample->id));
			if(!$record)
    			$DB->insert_record(block_exacomp::DB_DESCEXAMP, array('descrid'=>$descriptorid, 'exampid'=> $newExample->id));
    	}
    }

    //add visibility
	$DB->insert_record(block_exacomp::DB_EXAMPVISIBILITY, array('courseid'=>$courseid, 'exampleid'=>$newExample->id, 'studentid'=>0, 'visible'=>1));
    block_exacomp_settstamp();
    
    // save file
    file_save_draft_area_files($formdata->file, context_system::instance()->id, 'block_exacomp', 'example_task',
            $newExample->id, array('subdirs' => 0, 'maxfiles' => 1));
    file_save_draft_area_files($formdata->solution, context_system::instance()->id, 'block_exacomp', 'example_solution',
            $newExample->id, array('subdirs' => 0, 'maxfiles' => 1));
    
    // rename file according to LIS
    // TODO: lis renaming
    /*
    if($formdata->lisfilename) {
        $descr = reset($_POST['descriptor']);
        $descr = $DB->get_record(block_exacomp::DB_DESCRIPTORS,array('id' => $descr));
        $descr->topicid = $topicid;
        $newfilename = block_exacomp_get_descriptor_numbering($descr).' ';
        
        if ($file = block_exacomp_get_file($newExample, 'example_task')) {
            $filename_task = $newfilename . $formdata->title . "." . pathinfo($form->get_new_filename('file'), PATHINFO_EXTENSION);
            $file->rename('/', $filename_task);
        }
        
    
        if ($form->get_new_filename('file')) {
            $newExample->title = $filename_task;
        }
        if ($form->get_new_filename('solution')) {
            $filename_solution = $newfilename . $formdata->title . "_SOLUTION." . pathinfo($form->get_new_filename('solution'), PATHINFO_EXTENSION);
        }
    }
    if ($filename_solution && $file = block_exacomp_get_file($newExample, 'example_solution')) {
        $file->rename('/', $filename_solution);
    }
    */
    
    
    ?>
<script type="text/javascript">
		window.opener.block_exacomp.newExampleAdded();
		window.close();
	</script>
<?php 
	exit;
}

if($exampleid > 0) {
    $example->descriptors = $DB->get_fieldset_select('block_exacompdescrexamp_mm', 'descrid', 'exampid = ?',array($exampleid));
    
    $draftitemid = file_get_submitted_draft_itemid('file');
    file_prepare_draft_area($draftitemid, context_system::instance()->id, 'block_exacomp', 'example_task', $exampleid,
            array('subdirs' => 0, 'maxfiles' => 1));
    $example->file = $draftitemid;
    
    $draftitemid = file_get_submitted_draft_itemid('solution');
    file_prepare_draft_area($draftitemid, context_system::instance()->id, 'block_exacomp', 'example_solution', $exampleid,
            array('subdirs' => 0, 'maxfiles' => 1));
    $example->solution = $draftitemid;
    
    $form->set_data($example);
}

$form->display();

/* END CONTENT REGION */
echo $PAGE->get_renderer('block_exacomp')->footer();
