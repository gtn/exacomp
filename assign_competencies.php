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
$studentid = optional_param('studentid', 0, PARAM_INT);

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
block_exacomp_build_breadcrum_navigation($courseid);

$output = $PAGE->get_renderer('block_exacomp');

// build tab navigation & print header
echo $output->header();
echo $OUTPUT->tabtree(block_exacomp_build_navigation_tabs($context,$courseid), $page_identifier);

// CHECK TEACHER
$isTeacher = block_exacomp_is_teacher($context);
// IF DELETE > 0 DELTE CUSTOM EXAMPLE
if(($delete = optional_param("delete", 0, PARAM_INT)) > 0 && $isTeacher)
	block_exacomp_delete_custom_example($delete);

$activities = block_exacomp_get_activities_by_course($courseid);
$course_settings = block_exacomp_get_settings_by_course($courseid);

if($course_settings->uses_activities && !$activities && !$course_settings->show_all_descriptors)
	echo $output->print_no_activities_warning($isTeacher);
else{
	list($subjects, $topics, $selectedSubject, $selectedTopic) = block_exacomp_init_overview_data($courseid, optional_param('subjectid', 0, PARAM_INT), optional_param('topicid', SHOW_ALL_TOPICS, PARAM_INT), !$isTeacher,  ($isTeacher?0:$USER->id));
	
	//Delete timestamp (end|start) from example
	if($example_del = optional_param('exampleid', 0, PARAM_INT)){
		block_exacomp_delete_timefield($example_del, optional_param('deletestart', 0, PARAM_INT), optional_param('deleteend', 0, PARAM_INT));
	}

	// IF TEACHER SHOW ALL COURSE STUDENTS, IF NOT ONLY CURRENT USER
	$students_single = array();
	$students_single[$USER->id] = $USER;
	
	$students = ($isTeacher) ? block_exacomp_get_students_by_course($courseid) : $students_single;
	if($course_settings->nostudents) $students = array();
	
	foreach($students as $student)
		$student = block_exacomp_get_user_information_by_course($student, $courseid);

	echo $output->print_competence_overview_form_start((isset($selectedTopic))?$selectedTopic:null, (isset($selectedSubject))?$selectedSubject:null, $studentid);

	//dropdowns for subjects and topics and students -> if user is teacher
	echo $output->print_overview_dropdowns(block_exacomp_get_schooltypetree_by_subjects($subjects), $topics, $selectedSubject->id, $selectedTopic->id, $students, $studentid, $isTeacher);
	
	if($version)
		$metasubject = block_exacomp_get_subject_by_id($selectedSubject->subjid);
	else {
		$metasubject = new stdClass();
		$metasubject->title = block_exacomp_get_schooltype_title_by_subject($selectedSubject);
	}
	
		
	$scheme = block_exacomp_get_grading_scheme($courseid);
	
	if($selectedTopic->id != SHOW_ALL_TOPICS){
		$cat = block_exacomp_get_niveau($selectedTopic->niveauid);
		echo $output->print_overview_metadata($metasubject->title, $selectedSubject, $selectedTopic, $cat);
				
		if($isTeacher)
			echo $output->print_overview_metadata_teacher($selectedSubject,$selectedTopic);
		else{
			$cm_mm = block_exacomp_get_course_module_association($courseid);
			$course_mods = get_fast_modinfo($courseid)->get_cms();
	
			$activities_student = array();
			if(isset($cm_mm->topics[$selectedTopic->id]))
				foreach($cm_mm->topics[$selectedTopic->id] as $cmid)
					$activities_student[] = $course_mods[$cmid];
		    
			if($version)
				echo $output->print_overview_metadata_student($selectedSubject, $selectedTopic, $students[$USER->id]->topics, $showevaluation, $scheme, block_exacomp_get_icon_for_user($activities_student, $USER, block_exacomp_get_supported_modules()));
		}
	}
	
	if(!$version && $course_settings->nostudents != 1 && $studentid) echo $output->print_student_evaluation($showevaluation, $isTeacher,$selectedTopic->id,$selectedSubject->id, $studentid);
	
	if($course_settings->nostudents != 1)
	    echo $output->print_overview_legend($isTeacher);
	    
	$statistic = false;
    if($isTeacher){
    	if($studentid == BLOCK_EXACOMP_SHOW_ALL_STUDENTS)
    	    echo $output->print_column_selector(count($students));
    	elseif (!$studentid)
    	    $students = array();
    	elseif($studentid == BLOCK_EXACOMP_SHOW_STATISTIC)
    		$statistic = true;
    	else 
    	    $students = !empty($students[$studentid]) ? array($students[$studentid]) : array();
	}

	$subjects = block_exacomp_get_competence_tree($courseid,(isset($selectedSubject))?$selectedSubject->id:null,false,(isset($selectedTopic))?$selectedTopic->id:null,
			!($course_settings->show_all_examples == 0 && !$isTeacher),$course_settings->filteredtaxonomies, true);
	
	$firstvalue = reset($subjects);
	$firstvalue->title = $selectedSubject->title;
	
	echo $output->print_competence_overview($subjects, $courseid, $students, $showevaluation, $isTeacher ? ROLE_TEACHER : ROLE_STUDENT, $scheme, ($version && $selectedTopic->id != SHOW_ALL_TOPICS), false, 0, $statistic);

}

/* END CONTENT REGION */
echo $output->footer();
