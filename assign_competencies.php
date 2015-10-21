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

$editmode = optional_param('editmode', 0, PARAM_BOOL);
$ng_subjectid = optional_param('ng_subjectid', 0, PARAM_INT);
$subjectid = optional_param('subjectid', 0, PARAM_INT);
$topicid = optional_param('topicid', SHOW_ALL_TOPICS, PARAM_INT);

require_login($course);

$context = context_course::instance($courseid);

// CHECK TEACHER
$isTeacher = block_exacomp_is_teacher($context);

$studentid = block_exacomp_get_studentid($isTeacher) ;
if($studentid == 0)
	$studentid = BLOCK_EXACOMP_SHOW_ALL_STUDENTS;

if($editmode) {
	$selectedStudentid = $studentid;
	$studentid = 0;
}

$page_identifier = 'tab_competence_overview';

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/assign_competencies.php', array('courseid' => $courseid, 'showevaluation'=>$showevaluation));
$PAGE->set_heading(get_string('pluginname', 'block_exacomp'));
$PAGE->set_title(get_string($page_identifier, 'block_exacomp'));
$NG_PAGE = (object)[ 'url' => new block_exacomp\url('/blocks/exacomp/assign_competencies.php', array(
                'courseid' => $courseid,
                'showevaluation' => $showevaluation,
                'studentid' => $studentid,
                'editmode' => $editmode,
                'subjectid' => $subjectid,
                'ng_subjectid' => $ng_subjectid,
                'topicid' => $topicid,
            )) ];

// build breadcrumbs navigation
block_exacomp_build_breadcrum_navigation($courseid);

/**
 * @var block_exacomp_renderer
 */
$output = $PAGE->get_renderer('block_exacomp');
$output->editmode = $editmode;


// IF DELETE > 0 DELTE CUSTOM EXAMPLE
if(($delete = optional_param("delete", 0, PARAM_INT)) > 0 && $isTeacher){
	block_exacomp_delete_custom_example($delete);
}

$activities = block_exacomp_get_activities_by_course($courseid);
$course_settings = block_exacomp_get_settings_by_course($courseid);

if($course_settings->uses_activities && !$activities && !$course_settings->show_all_descriptors) {
    echo $output->header($context, $courseid, $page_identifier);
    echo $output->print_no_activities_warning($isTeacher);
	echo $output->footer();
	exit;
}

$ret = block_exacomp_init_overview_data($courseid, $ng_subjectid, $subjectid, $topicid, !$isTeacher, ($isTeacher?0:$USER->id));
if (!$ret) {
    print_error('not configured');
}
list($topics, $niveaus, $selectedSubject, $selectedNiveau) = $ret;

//Delete timestamp (end|start) from example
if($example_del = optional_param('exampleid', 0, PARAM_INT)){
	block_exacomp_delete_timefield($example_del, optional_param('deletestart', 0, PARAM_INT), optional_param('deleteend', 0, PARAM_INT));
}

// IF TEACHER SHOW ALL COURSE STUDENTS, IF NOT ONLY CURRENT USER
$students = ($isTeacher) ? block_exacomp_get_students_by_course($courseid) : array($USER->id => $USER);
if($course_settings->nostudents) $students = array();

$competence_tree = block_exacomp_get_competence_tree($courseid,(isset($selectedSubject))?$selectedSubject->id:null,false,(isset($selectedNiveau))?$selectedNiveau->id:null,
		!($course_settings->show_all_examples == 0 && !$isTeacher),$course_settings->filteredtaxonomies, true);

$scheme = block_exacomp_get_grading_scheme($courseid);

$statistic = false;
if($isTeacher){
    if($studentid == BLOCK_EXACOMP_SHOW_ALL_STUDENTS)
        echo $output->print_column_selector(count($students));
    elseif (!$studentid)
    $students = array();
    elseif($studentid == BLOCK_EXACOMP_SHOW_STATISTIC)
    $statistic = true;
    else
        $students = !empty($students[$studentid]) ? array($students[$studentid]) : $students;
}

foreach($students as $student)
    $student = block_exacomp_get_user_information_by_course($student, $courseid);

$firstvalue = reset($competence_tree);
$firstvalue->title = $selectedSubject->title;

if (optional_param('print', false, PARAM_BOOL)) {
    $output->print = true;
    $ret = $output->print_competence_overview($competence_tree, $courseid, $students, $showevaluation, $isTeacher ? block_exacomp::ROLE_TEACHER : block_exacomp::ROLE_STUDENT, $scheme, ($version && $selectedNiveau->id != SHOW_ALL_TOPICS), false, 0, $statistic);
    block_exacomp\printer::competence_overview($ret);
}

echo $output->header($context, $courseid, $page_identifier);

echo $output->print_competence_overview_form_start((isset($selectedNiveau))?$selectedNiveau:null, (isset($selectedSubject))?$selectedSubject:null, $studentid, $editmode);

//dropdowns for subjects and topics and students -> if user is teacher
echo $output->print_overview_dropdowns(block_exacomp_get_schooltypetree_by_topics($topics), $selectedSubject->id, $selectedNiveau->id, $students, (!$editmode) ? $studentid : $selectedStudentid, $isTeacher);
if($version)
	$metasubject = block_exacomp_get_subject_by_id($selectedSubject->subjid);
else {
	$metasubject = new stdClass();
	$metasubject->title = block_exacomp_get_schooltype_title_by_subject($selectedSubject);
}

if($selectedNiveau->id != SHOW_ALL_TOPICS){
	$cat = ($version)?block_exacomp_get_niveau($selectedNiveau->id):'';
	echo $output->print_overview_metadata($metasubject->title, $selectedSubject, $selectedNiveau, $cat);
			
	if($isTeacher)
		echo $output->print_overview_metadata_teacher($selectedSubject,$selectedNiveau);
	else{
		$cm_mm = block_exacomp_get_course_module_association($courseid);
		$course_mods = get_fast_modinfo($courseid)->get_cms();

		$activities_student = array();
		if(isset($cm_mm->topics[$selectedNiveau->id]))
			foreach($cm_mm->topics[$selectedNiveau->id] as $cmid)
				$activities_student[] = $course_mods[$cmid];
	    
		if($version)
			echo $output->print_overview_metadata_student($selectedSubject, $selectedNiveau, $students[$USER->id]->topics, $showevaluation, $scheme, block_exacomp_get_icon_for_user($activities_student, $USER, block_exacomp_get_supported_modules()));
	}
}

echo html_writer::start_tag("div", array("id"=>"exabis_competences_block"));
echo html_writer::start_tag("div", array("class"=>"exabis_competencies_lis"));
echo html_writer::start_tag("div", array("class"=>"gridlayout"));

echo $output->print_subjects_menu(block_exacomp_get_schooltypetree_by_topics($topics),$selectedSubject); 
echo $output->print_niveaus_menu($niveaus,$selectedNiveau,$selectedSubject);
if($course_settings->nostudents != 1)
	echo $output->print_overview_legend($isTeacher);
if(!$version && $course_settings->nostudents != 1 && $studentid) echo $output->print_student_evaluation($showevaluation, $isTeacher,$selectedNiveau->id,$selectedSubject->id, $studentid);

echo $output->print_competence_overview($competence_tree, $courseid, $students, $showevaluation, $isTeacher ? block_exacomp::ROLE_TEACHER : block_exacomp::ROLE_STUDENT, $scheme, ($version && $selectedNiveau->id != SHOW_ALL_TOPICS), false, 0, $statistic);

echo html_writer::end_tag("div");
echo html_writer::end_tag("div");
echo html_writer::end_tag("div");

/* END CONTENT REGION */
echo $output->footer();
