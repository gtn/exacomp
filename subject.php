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

require_once __DIR__."/inc.php";

global $DB, $OUTPUT, $PAGE, $USER;

$courseid = required_param('courseid', PARAM_INT);
$action = optional_param('action', 'add', PARAM_TEXT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_exacomp', $courseid);
}

require_login($course);

$context = context_course::instance($courseid);

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/subject.php', array('courseid' => $courseid));
$PAGE->set_heading(block_exacomp\t('de:Neuen Kompetenzraster anlegen'));
$PAGE->set_pagelayout('popup');

// build tab navigation & print header
$output = $PAGE->get_renderer('block_exacomp');
/* CONTENT REGION */

block_exacomp_require_teacher($context);


require_once $CFG->libdir . '/formslib.php';

class block_exacomp_local_item_form extends moodleform {

    function definition() {
        global $CFG, $USER, $DB, $version, $PAGE;

        $output = $PAGE->get_renderer('block_exacomp');

        $mform = & $this->_form;

        // $mform->addElement('header', 'general', get_string("example_upload_header", "block_exacomp", $descrTitle));

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', 0);

        $mform->addElement('hidden', 'action');
        $mform->setType('action', PARAM_ACTION);
        $mform->setDefault('action', 'add');

        $mform->addElement('text', 'title', block_exacomp\get_string('name'), 'maxlength="255" size="60"');
        $mform->setType('title', PARAM_TEXT);
        $mform->addRule('title', block_exacomp\get_string("titlenotemtpy"), 'required', null, 'client');

        $tselect = $mform->addElement('select', 'stid', block_exacomp\get_string('tab_teacher_settings_selection_st'), $DB->get_records_menu(block_exacomp::DB_SCHOOLTYPES, null, null, 'id, title'));
        // $tselect->setSelected(array_keys($DB->get_records(block_exacomp::DB_EXAMPTAX,array("exampleid" => $this->_customdata['exampleid']),"","taxid")));

        $this->add_action_buttons(false);
    }
}

$form = new block_exacomp_local_item_form($_SERVER['REQUEST_URI'],
            array());

if($formdata = $form->get_data()) {
    
    $new = new stdClass();
    $new->title = $formdata->title;
    $new->source = block_exacomp::DATA_SOURCE_CUSTOM;
    $new->sourceid = block_exacomp::DATA_SOURCE_CUSTOM;
    
    // if($formdata->id == 0) {
        $new->id = $DB->insert_record(block_exacomp::DB_SUBJECTS, $new);
    /*
    }
    else {
        //update example
        $newExample->id = $formdata->exampleid;
        $DB->update_record('block_exacompexamples', $newExample);
        $DB->delete_records('block_exacompdescrexamp_mm',array('exampid' => $newExample->id));
    }

    //insert taxid in exampletax_mm
    if(isset($formdata->taxid)) {
	    foreach($formdata->taxid as $tax => $taxid)
		    block_exacomp\db::insert_or_update_record(block_exacomp::DB_EXAMPTAX, array(
		        'exampleid' => $newExample->id,
		        'taxid' => $taxid
		    ));
    }
    //add descriptor association
    if ($descriptors = block_exacomp\param::optional_array('descriptor', array(PARAM_INT=>PARAM_INT))) {
    	foreach($descriptors as $descriptorid){
    		$desc_examp = $DB->get_record(block_exacomp::DB_DESCEXAMP, array('descrid'=>$descriptorid, 'exampid'=>$newExample->id));
    		if(!$desc_examp){
    			$sql = "SELECT MAX(sorting) as sorting FROM {".block_exacomp::DB_DESCEXAMP."} WHERE descrid=?";
    			$max_sorting = $DB->get_record_sql($sql, array($descriptorid)); 
    			$sorting = intval($max_sorting->sorting)+1;
    			$insert = new stdClass();
    			$insert->descrid = $descriptorid;
    			$insert->exampid = $newExample->id;
    			$insert->sorting = $sorting;
    			
    			$DB->insert_record(block_exacomp::DB_DESCEXAMP, $insert);
    	}
            //block_exacomp_db::insert_or_update_record(block_exacomp::DB_DESCEXAMP, array('descrid'=>$descriptorid, 'exampid'=>$newExample->id));
    }
    }
    
    //add visibility if not exists
    if (!$DB->get_record(block_exacomp::DB_EXAMPVISIBILITY, array('courseid'=>$courseid, 'exampleid'=>$newExample->id, 'studentid'=>0))) {
        $DB->insert_record(block_exacomp::DB_EXAMPVISIBILITY, array('courseid'=>$courseid, 'exampleid'=>$newExample->id, 'studentid'=>0, 'visible'=>1));
    }
    block_exacomp_settstamp();
    
    // save file
    file_save_draft_area_files($formdata->file, context_system::instance()->id, 'block_exacomp', 'example_task',
            $newExample->id, array('subdirs' => 0, 'maxfiles' => 1));
    file_save_draft_area_files($formdata->solution, context_system::instance()->id, 'block_exacomp', 'example_solution',
            $newExample-s>id, array('subdirs' => 0, 'maxfiles' => 1));
    */

    echo $output->header($context, $courseid, '', false);
    
    echo $output->popup_close();
    ?>
    <script type="text/javascript">
    	top.location.href = <?php echo json_encode($CFG->wwwroot."/blocks/exacomp/assign_competencies.php?courseid=".$courseid."&editmode=1&ng_subjectid={$new->id}"); ?>;
    </script>
    <?php

    echo $output->footer();
    
	exit;
}

/*
if ($exampleid > 0) {
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
*/

echo $output->header($context, $courseid, '', false);

$form->display();

echo $output->footer();
