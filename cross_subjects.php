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
$group = optional_param('group', 0, PARAM_INT);
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_simplehtml', $courseid);
}
$context = context_course::instance($courseid);

// CHECK TEACHER
require_login($course);
$isTeacher = block_exacomp_is_teacher($context);

$studentid = block_exacomp_get_studentid($isTeacher) ;

$edit = optional_param('editmode', 0, PARAM_BOOL);

if($edit) {
	$selectedStudentid = $studentid;
	$studentid = 0;
}


/* PAGE IDENTIFIER - MUST BE CHANGED. Please use string identifier from lang file */
$page_identifier = 'tab_cross_subjects_course';

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/cross_subjects.php', array('courseid' => $courseid, 'showevaluation'=>$showevaluation));
$PAGE->set_heading(get_string('pluginname', 'block_exacomp'));
$PAGE->set_title(get_string($page_identifier, 'block_exacomp'));

// build breadcrumbs navigation
block_exacomp_build_breadcrum_navigation($courseid);

$output = $PAGE->get_renderer('block_exacomp');

// build tab navigation & print header
echo $output->header($context, $courseid, 'tab_cross_subjects');


if($isTeacher){
    echo $OUTPUT->tabtree(block_exacomp_build_navigation_tabs_cross_subjects($context, $courseid), $page_identifier);
}
	
// IF DELETE > 0 DELTE CUSTOM EXAMPLE
if(($delete = optional_param("delete", 0, PARAM_INT)) > 0 && $isTeacher)
	block_exacomp_delete_custom_example($delete);

$activities = block_exacomp_get_activities_by_course($courseid);
$course_settings = block_exacomp_get_settings_by_course($courseid);

if($course_settings->uses_activities && !$activities && !$course_settings->show_all_descriptors)
	echo $output->print_no_activities_warning($isTeacher);
else{
	list($crosssubjects, $selectedCrosssubject) = block_exacomp_init_course_crosssubjects($courseid, optional_param('crosssubjid', 0, PARAM_INT), ($isTeacher)?0:$studentid);
	
	//no crosssubjects available -> end 
	if(empty($crosssubjects)){
		echo get_string('no_crosssubjs', 'block_exacomp');
		echo $output->print_wrapperdivend();
		echo $OUTPUT->footer();
		die;
	}
	
	//Delete timestamp (end|start) from example
	if($example_del = optional_param('exampleid', 0, PARAM_INT)){
		block_exacomp_delete_timefield($example_del, optional_param('deletestart', 0, PARAM_INT), optional_param('deleteend', 0, PARAM_INT));
	}

	// IF TEACHER SHOW ALL COURSE STUDENTS, IF NOT ONLY CURRENT USER
	$students = ($isTeacher) ? block_exacomp_get_students_for_crosssubject($courseid, $selectedCrosssubject) : array($USER);
	
	if(!$students){
		$edit = 1;
		$selectedStudentid = 0;
		$studentid = 0;
	}
	
	foreach($students as $student)
		$student = block_exacomp_get_user_information_by_course($student, $courseid);

	echo $output->print_cross_subjects_form_start((isset($selectedCrosssubject))?$selectedCrosssubject:null, $studentid);

	//dropdowns for crosssubjects
	echo $output->print_dropdowns_cross_subjects($crosssubjects, $selectedCrosssubject->id, $students, (!$edit) ? $studentid : $selectedStudentid, $isTeacher);
	
	//schooltypes
	/*$schooltypes = block_exacomp_get_schooltypes_by_course($courseid);
	    
	$schooltype_title = "";
	foreach($schooltypes as $schooltype){
	    $schooltype_title .= $schooltype->title . ", ";
	}
	$schooltype = substr($schooltype_title, 0, strlen($schooltype_title)-1);
	*/
	echo $output->print_overview_metadata_cross_subjects($selectedCrosssubject, $isTeacher, $studentid);
		
	$scheme = block_exacomp_get_grading_scheme($courseid);
	
	if(!$isTeacher){
	    $user_evaluation = block_exacomp_get_user_information_by_course($USER, $courseid);
	
    	$cm_mm = block_exacomp_get_course_module_association($courseid);
    	$course_mods = get_fast_modinfo($courseid)->get_cms();
    
    	//TODO: test with activities
    	/*$activities_student = array();
    	if(isset($cm_mm->topics[$selectedTopic->id]))
    		foreach($cm_mm->topics[$selectedTopic->id] as $cmid)
    			$activities_student[] = $course_mods[$cmid];*/
	}
	
	echo $output->print_overview_legend($isTeacher);
	
	$statistic = false;
	if($isTeacher){
    	if($studentid == BLOCK_EXACOMP_SHOW_ALL_STUDENTS){
    		$showevaluation = false;
    	    echo $output->print_column_selector(count($students));
    	}elseif ($studentid == 0)
    	    $students = array();
    	elseif($studentid == BLOCK_EXACOMP_SHOW_STATISTIC)
    		$statistic = true;
    	else{ 
    	    $students = array($students[$studentid]);
    	    $showevaluation = true;
		}
	}else{
		$showevaluation = true;
	}
	
	$subjects = block_exacomp_get_competence_tree_for_cross_subject($courseid,(isset($selectedCrosssubject))?$selectedCrosssubject->id:null,false, !($course_settings->show_all_examples == 0 && !$isTeacher),$course_settings->filteredtaxonomies);

	echo html_writer::start_tag("div", array("class"=>"exabis_competencies_lis"));
	echo $output->print_competence_overview($subjects, $courseid, $students, $showevaluation, $isTeacher ? block_exacomp::ROLE_TEACHER : block_exacomp::ROLE_STUDENT, $scheme, false, true, $selectedCrosssubject->id, $statistic);
	echo html_writer::end_tag("div");
}
/* END CONTENT REGION */
echo $output->footer();

?>