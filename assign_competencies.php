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

global $DB, $OUTPUT, $PAGE, $USER, $version;

$courseid = required_param('courseid', PARAM_INT);
$showevaluation = ($version) ? true : optional_param("showevaluation", false, PARAM_BOOL);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_simplehtml', $courseid);
}

require_login($course);

$context = context_course::instance($courseid);

/* PAGE IDENTIFIER - MUST BE CHANGED. Please use string identifier from lang file */
$page_identifier = 'tab_competence_overview';

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/assign_competencies.php', array('courseid' => $courseid, 'showevaluation'=>$showevaluation));
$PAGE->set_heading(get_string('pluginname', 'block_exacomp'));
$PAGE->set_title(get_string($page_identifier, 'block_exacomp'));

block_exacomp_init_js_css();

// build breadcrumbs navigation
$coursenode = $PAGE->navigation->find($courseid, navigation_node::TYPE_COURSE);
$blocknode = $coursenode->add(get_string('pluginname','block_exacomp'));
$pagenode = $blocknode->add(get_string($page_identifier,'block_exacomp'), $PAGE->url);
$pagenode->make_active();

// build tab navigation & print header
echo $OUTPUT->header();
echo $OUTPUT->tabtree(block_exacomp_build_navigation_tabs($context,$courseid), $page_identifier);

// CHECK TEACHER
$isTeacher = (has_capability('block/exacomp:teacher', $context)) ? true : false;
// IF DELETE > 0 DELTE CUSTOM EXAMPLE
if(($delete = optional_param("delete", 0, PARAM_INT)) > 0 && $isTeacher)
	block_exacomp_delete_custom_example($delete);

$output = $PAGE->get_renderer('block_exacomp');

$activities = block_exacomp_get_activities_by_course($courseid);

if(block_exacomp_get_settings_by_course($courseid)->uses_activities && !$activities && !block_exacomp_get_settings_by_course($courseid)->show_all_descriptors)
	echo $output->print_no_activities_warning();
else{
	
	if($isTeacher)
		list($subjects, $topics, $selectedSubject, $selectedTopic) = block_exacomp_init_overview_data($courseid, optional_param('subjectid', 0, PARAM_INT), optional_param('topicid', 0, PARAM_INT));
	else
		list($subjects, $topics, $selectedSubject, $selectedTopic) = block_exacomp_init_overview_data($courseid, optional_param('subjectid', 0, PARAM_INT), optional_param('topicid', 0, PARAM_INT), true);
		
	// SAVA DATA
	if (($action = optional_param("action", "", PARAM_TEXT) ) == "save") {
		// DESCRIPTOR DATA
		block_exacomp_save_competencies(isset($_POST['data']) ? $_POST['data'] : array(), $courseid, ($isTeacher) ? ROLE_TEACHER : ROLE_STUDENT, TYPE_DESCRIPTOR, $selectedTopic->id);
		// TOPIC DATA
		block_exacomp_save_competencies(isset($_POST['datatopics']) ? $_POST['datatopics'] : array(), $courseid, ($isTeacher) ? ROLE_TEACHER : ROLE_STUDENT, TYPE_TOPIC, $selectedTopic->id);
		// EXAMPLE DATA
		block_exacomp_save_example_evaluation(isset($_POST['dataexamples']) ? $_POST['dataexamples'] : array(), $courseid, ($isTeacher) ? ROLE_TEACHER : ROLE_STUDENT, $selectedTopic->id);

		//TOPIC LIS STUDENT
		if(isset($_POST['topiccomp'])){
			if(($topicid = optional_param('topicid', 0, PARAM_INT))!=0){
				block_exacomp_set_user_competence($USER->id, $topicid, TYPE_TOPIC, $courseid, ROLE_STUDENT, $_POST['topiccomp']);
			}
		}
	}
	//Delete timestamp (end|start) from example
	if($example_del = optional_param('exampleid', 0, PARAM_INT)){
		block_exacomp_delete_timefield($example_del, optional_param('deletestart', 0, PARAM_INT), optional_param('deleteend', 0, PARAM_INT));
	}

	// IF TEACHER SHOW ALL COURSE STUDENTS, IF NOT ONLY CURRENT USER
	$students = ($isTeacher) ? block_exacomp_get_students_by_course($courseid) : array($USER);
	foreach($students as $student)
		block_exacomp_get_user_information_by_course($student, $courseid);

	
	echo $output->print_competence_overview_form_start((isset($selectedTopic))?$selectedTopic:null, (isset($selectedSubject))?$selectedSubject:null);

	//dropdowns for subjects and topics
	echo $output->print_overview_dropdowns($subjects, $topics, $selectedSubject->id, $selectedTopic->id);
	
	$schooltype = block_exacomp_get_schooltyp_by_subject($selectedSubject);
	$cat = block_exacomp_get_category($selectedTopic);
		
	if($selectedTopic->id != SHOW_ALL_TOPICS){
		echo $output->print_overview_metadata($schooltype, $selectedSubject, $selectedTopic, $cat);
		
		if($isTeacher)
			echo $output->print_overview_metadata_teacher($selectedSubject,$selectedTopic);
		else{
			$user_evaluation = block_exacomp_get_user_information_by_course($USER, $courseid);
	
			$cm_mm = block_exacomp_get_course_module_association($courseid);
			$course_mods = get_fast_modinfo($courseid)->get_cms();
	
			$activities_student = array();
			if(isset($cm_mm->topics[$selectedTopic->id]))
				foreach($cm_mm->topics[$selectedTopic->id] as $cmid)
					$activities_student[] = $course_mods[$cmid];
			
			if($version)
				echo $output->print_overview_metadata_student($selectedSubject, $selectedTopic, $user_evaluation->topics, $showevaluation, block_exacomp_get_grading_scheme($courseid), block_exacomp_get_icon_for_user($activities_student, $USER));
		}
	}
	
	if(!$version) echo $output->print_student_evaluation($showevaluation, $isTeacher);
	
	echo $output->print_overview_legend($isTeacher);
	echo $output->print_column_selector(count($students));

	$subjects = block_exacomp_get_competence_tree($courseid,(isset($selectedSubject))?$selectedSubject->id:null,false,(isset($selectedTopic))?$selectedTopic->id:null,
			!(block_exacomp_get_settings_by_course($courseid)->show_all_examples == 0 && !$isTeacher));

	if($version && !$isTeacher){
		$examples = block_exacomp_get_examples_LIS_student($subjects);
		echo $output->print_competence_overview_LIS_student($subjects, $courseid, $showevaluation, block_exacomp_get_grading_scheme($courseid), $examples);
	}else
		echo $output->print_competence_overview($subjects, $courseid, $students, $showevaluation, (has_capability('block/exacomp:teacher', $context)) ? ROLE_TEACHER : ROLE_STUDENT, block_exacomp_get_grading_scheme($courseid));
}
/* END CONTENT REGION */

echo $OUTPUT->footer();

?>