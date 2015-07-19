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

global $DB, $OUTPUT, $PAGE, $USER;

$courseid = required_param('courseid', PARAM_INT);
$exampleid = required_param('exampleid', PARAM_INT);
$editmode = optional_param('editmode', 0, PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_exacomp', $courseid);
}

// error if example does not exist or was created by somebody else
if (!$example = $DB->get_record('block_exacompexamples', array('id' => $exampleid))) {
    print_error('invalidexample', 'block_exacomp', $exampleid);
}

require_login($course);

$context = context_course::instance($courseid);

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/competence_associations.php', array('courseid' => $courseid));
$PAGE->set_heading(get_string('pluginname', 'block_exacomp'));

block_exacomp_init_js_css();
$PAGE->requires->js("/blocks/exacomp/javascript/CollapsibleLists.compressed.js");
$PAGE->requires->css("/blocks/exacomp/css/CollapsibleLists.css");

// build breadcrumbs navigation
$coursenode = $PAGE->navigation->find($courseid, navigation_node::TYPE_COURSE);
$blocknode = $coursenode->add(get_string('pluginname','block_exacomp'));
$blocknode->make_active();

// build tab navigation & print header
echo $OUTPUT->header();

// CHECK TEACHER
$isTeacher = (has_capability('block/exacomp:teacher', $context)) ? true : false;

echo '<div id="exacomp">';
/* CONTENT REGION */
if (($action = optional_param("action", "", PARAM_TEXT) ) == "save") {
	if(isset($_POST['descriptor']) && !empty($_POST['descriptor'])){
		$DB->delete_records(DB_DESCEXAMP,array('exampid' => $exampleid));
		foreach($_POST['descriptor'] as $descriptorid){
			//check if record already exists -> if not insert new 
			$record = $DB->get_records(DB_DESCEXAMP, array('descrid'=>$descriptorid, 'exampid'=>$exampleid));
			if(!$record){
				$insert = new stdClass();
				$insert->descrid = $descriptorid;
				$insert->exampid = $exampleid;
				$DB->insert_record(DB_DESCEXAMP, $insert);
			}	
		}
	}
	
	?>
<script type="text/javascript">
		window.close();
	</script>
<?php 
}

//get descriptors for the given example
$example_descriptors = $DB->get_records(DB_DESCEXAMP,array('exampid'=>$exampleid),'','descrid');

$tree = block_exacomp_build_example_association_tree($courseid, $example_descriptors, $exampleid);

$output = $PAGE->get_renderer('block_exacomp');

echo html_writer::tag("p",get_string("competence_associations_explaination","block_exacomp",$example->title));
$content = $output->print_competence_based_list_tree($tree, $isTeacher, $editmode);

if($editmode==1)
	$content.= html_writer::empty_tag('input', array('type'=>'submit', 'value'=>get_string('save_selection', 'block_exacomp')));

echo  html_writer::tag('form', $content, array('method'=>'post', 'action'=>$PAGE->url.'&exampleid='.$exampleid.'&editmode='.$editmode.'&action=save', 'name'=>'add_association'));
		
/* END CONTENT REGION */
echo '</div>';
echo $OUTPUT->footer();
?>