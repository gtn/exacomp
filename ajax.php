<?php
/*
 * *************************************************************
 * Copyright notice
 *
 * (c) 2014 exabis internet solutions <info@exabis.at>
 * All rights reserved
 *
 * You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This module is based on the Collaborative Moodle Modules from
 * NCSA Education Division (http://www.ncsa.uiuc.edu)
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 * *************************************************************
 */
require_once dirname ( __FILE__ ) . "/inc.php";
global $DB, $USER;

$courseid = required_param ( 'courseid', PARAM_INT );
if (! $course = $DB->get_record ( 'course', array (
		'id' => $courseid 
) )) {
	print_error ( 'invalidcourse', 'block_simplehtml', $courseid );
}

require_login ( $course );
$context = context_course::instance ( $courseid );
$isTeacher = block_exacomp_is_teacher($context);

require_sesskey();

$action = optional_param ( 'action', 'competence', PARAM_TEXT );
switch($action){
	case ('crosssubj-title') :
		$crosssubjid = required_param('crosssubjid', PARAM_INT);
		$title = required_param('title', PARAM_TEXT);

		echo block_exacomp_save_cross_subject_title($crosssubjid, $title);
		break;
	case ('crosssubj-description') :
		$crosssubjid = required_param('crosssubjid', PARAM_INT);
		$description = required_param('description', PARAM_TEXT);

		echo block_exacomp_save_cross_subject_description($crosssubjid, $description);
		break;
	case ('crosssubj-descriptors'):
		$descrid = required_param('descrid', PARAM_INT);
		$crosssubjects = required_param('crosssubjects', PARAM_TEXT);
		$subj_ids = json_decode($crosssubjects);

		$not_crosssubjects = required_param('not_crosssubjects', PARAM_TEXT);
		$not_subj_ids = json_decode($not_crosssubjects);
		
		foreach($not_subj_ids as $not_subj_id)
			if(!is_numeric($not_subj_id))
				print_error('invalidparameter', 'block_exacomp', $not_subj_id);
		
		foreach($subj_ids as $subj_id)
			if(!is_numeric($subj_id))		
				print_error('invalidparameter', 'block_exacomp', $subj_id);
		
		foreach($not_subj_ids as $not_subj_id)
		block_exacomp_unset_cross_subject_descriptor($not_subj_id, $descrid);
			
		foreach($subj_ids as $subj_id)
		block_exacomp_set_cross_subject_descriptor($subj_id,$descrid);
			
		break;
	case ('crosssubj-descriptors-single'):
		$descrid = required_param('descrid', PARAM_INT);
		$crosssubjectid = required_param('crosssubjectid', PARAM_INT);
		
		block_exacomp_set_cross_subject_descriptor($crosssubjectid,$descrid);
		break;	
	case ('crosssubj-students'):
		$crosssubjid = required_param('crosssubjid', PARAM_INT);
		$students = required_param('students', PARAM_TEXT);
		$student_ids = json_decode($students);
		
		$not_students = required_param('not_students', PARAM_TEXT);
		$not_students_ids = json_decode($not_students);

        // TODO: kann man erstzen durch
        // $not_students_ids = block_exacomp_clean_array($not_students_ids, array(PARAM_INT=>PARAM_INT));
        // -- daniel
		foreach($not_students_ids as $studentid)
			if(!is_numeric($studentid))
				print_error('invalidparameter', 'block_exacomp', $studentid);
				
		foreach($student_ids as $studentid)
			if(!is_numeric($studentid))
				print_error('invalidparameter', 'block_exacomp', $studentid);
		
		foreach($student_ids as $studentid)
			block_exacomp_set_cross_subject_student($crosssubjid, $studentid);
		
		foreach($not_students_ids as $studentid)
			block_exacomp_unset_cross_subject_student($crosssubjid, $studentid);
		
		break;
	case ('crosssubj-share'):
		$crosssubjid = required_param('crosssubjid', PARAM_TEXT);
		$value = required_param('value', PARAM_INT);
		echo block_exacomp_share_crosssubject($crosssubjid, $value);
		break;
	case('competencies_array'):
		$competencies = required_param('competencies', PARAM_TEXT);
		$comptype = required_param ( 'comptype', PARAM_INT );

		$comps = json_decode($competencies);
		
		foreach($comps as $comp){
			if($comp)
				if(!is_numeric($comp->compid) || !is_numeric($comp->userid) || !is_numeric($comp->value))
					print_error('invalidparameter', 'block_exacomp', $comp);
		}
		
		$saved = "";
		foreach($comps as $comp){
			if($comp){
				$saved .= block_exacomp_set_user_competence ( $comp->userid, $comp->compid, $comptype, $courseid, ($isTeacher) ? block_exacomp::ROLE_TEACHER : block_exacomp::ROLE_STUDENT, $comp->value );
			}
		}
		echo $saved;
		break;
	case('examples_array'):
		$examples_json = required_param('examples', PARAM_TEXT);
		
		$examples = json_decode($examples_json);
		
		foreach($examples as $example){
			if($example)
				if(!is_numeric($example->exampleid) || !is_numeric($example->userid) || !is_numeric($example->value))
					print_error('invalidparameter', 'block_exacomp', $example);
		}
		
		$saved = "";
		foreach($examples as $example){
			if($example){
				$saved.="value: ".$example->value.$isTeacher." id: ";
				$saved .= block_exacomp_set_user_example($example->userid, $example->exampleid, $courseid, ($isTeacher) ? block_exacomp::ROLE_TEACHER : block_exacomp::ROLE_STUDENT, $example->value);
			}
		}
		echo $saved;
		break;
	case('save_as_draft'):
		$crosssubjid = required_param('crosssubjid', PARAM_INT);
		block_exacomp_save_drafts_to_course(array($crosssubjid), 0);
		break;
	case('hide-descriptor'):
		$descrid = required_param('descrid', PARAM_INT);
		$courseid = required_param('courseid', PARAM_INT);
		$visible = required_param('value', PARAM_INT);
		$studentid = required_param('studentid', PARAM_INT);
		
		block_exacomp_set_descriptor_visibility($descrid, $courseid, $visible, $studentid);
		break;
	case('hide-example'):
		$exampleid = required_param('exampleid', PARAM_INT);
		$courseid = required_param('courseid', PARAM_INT);
		$visible = required_param('value', PARAM_INT);
		$studentid = required_param('studentid', PARAM_INT);
		
		block_exacomp_set_example_visibility($exampleid, $courseid, $visible, $studentid);
		break;
	case('add-example-to-schedule'):
		$studentid = required_param('studentid', PARAM_INT);
		$exampleid = required_param('exampleid', PARAM_INT);
		$creatorid = $USER->id;
		
		if ( block_exacomp_add_example_to_schedule($studentid,$exampleid,$creatorid,$courseid) )
			echo get_string("weekly_schedule_added","block_exacomp");
		else
			echo get_string("weekly_schedule_already_exists","block_exacomp");
		
		break;
	case('new-comp'):
		$parentid = required_param('descriptorid', PARAM_INT);
		$title = required_param('title', PARAM_TEXT);
		
		//create sorting 
		$parent_descriptor = $DB->get_record(block_exacomp::DB_DESCRIPTORS, array('id'=>$parentid));
		$descriptor_topic_mm = $DB->get_record(block_exacomp::DB_DESCTOPICS, array('descrid'=>$parent_descriptor->id));
		$parent_descriptor->topicid = $descriptor_topic_mm->topicid;
		$siblings = block_exacomp_get_child_descriptors($parent_descriptor, $courseid);
		
		$max_sorting = 0;
		foreach($siblings as $sibling){
			if($sibling->sorting > $max_sorting) $max_sorting = $sibling->sorting;
		}
		
		$descriptor = new stdClass();
		$descriptor->title = $title;
		$descriptor->source = block_exacomp::CUSTOM_CREATED_DESCRIPTOR;
		$descriptor->parentid = $parentid;
		$descriptor->sorting = ++$max_sorting;
		
		$id = $DB->insert_record(block_exacomp::DB_DESCRIPTORS, $descriptor);
		
		$visibility = new stdClass();
		$visibility->courseid = $courseid;
		$visibility->descrid = $id;
		$visibility->studentid = 0;
		$visibility->visible = 1;
		
		$DB->insert_record(block_exacomp::DB_DESCVISIBILITY, $visibility);
		echo $id;
		break;
	case 'crosssubj-subject':
		$crosssubjectid = required_param('crosssubjid', PARAM_INT);
		$subjectid = required_param('subjectid', PARAM_INT);
		
		$crosssubject = $DB->get_record(block_exacomp::DB_CROSSSUBJECTS, array('id'=>$crosssubjectid));
		$crosssubject->subjectid = $subjectid;
		$DB->update_record(block_exacomp::DB_CROSSSUBJECTS, $crosssubject);
		
		echo $crosssubjectid.' subject:'.$subjectid;
		break;
	case 'delete-crosssubject':
		$crosssubjectid = required_param('crosssubjid', PARAM_INT);
		
		//delete student-crosssubject association
		$DB->delete_records(block_exacomp::DB_CROSSSTUD, array('crosssubjid'=>$crosssubjectid));
		
		//delete descriptor-crosssubject association
		$DB->delete_records(block_exacomp::DB_DESCCROSS, array('crosssubjid'=>$crosssubjectid));
		
		//delete crosssubject overall evaluations
		$DB->delete_records(block_exacomp::DB_COMPETENCIES, array('compid'=>$crosssubjectid, 'comptype'=>TYPE_CROSSSUB));
		
		//delete crosssubject
		$DB->delete_records(block_exacomp::DB_CROSSSUBJECTS, array('id'=>$crosssubjectid));
		break;
	case 'add-example-to-time-slot':
		$exampleid = required_param('exampleid', PARAM_INT);
		$studentid = required_param('studentid', PARAM_INT);
		if(!$studentid) $studentid = $USER->id;
		$event_course = required_param('event_course', PARAM_INT);
		
		$start = required_param('start', PARAM_INT);
		$end = required_param('end', PARAM_INT);
		echo $start;
		block_exacomp_set_example_time_slot($event_course, $exampleid, $studentid, $start, $end);
		break;
	case 'remove-example-from-schedule':
		$exampleid = required_param('exampleid', PARAM_INT);
		$studentid = required_param('studentid', PARAM_INT);
		if(!$studentid) $studentid = $USER->id;
		$event_course = required_param('event_course', PARAM_INT);
		
		block_exacomp_remove_example_from_schedule($event_course, $exampleid, $studentid);
		break;
	case 'get-examples-for-pool':
		$studentid = required_param('studentid', PARAM_INT);
		if(!$studentid) $studentid = $USER->id;
		
		$pool_course = required_param('pool_course', PARAM_INT);
		if(!$pool_course)$pool_course = $courseid;
		
		$week = optional_param('week', time(), PARAM_INT);
		$week = block_exacomp_add_days($week, 1 - date('N', $week));
		
		$examples = block_exacomp_get_examples_for_pool($studentid, $week, $pool_course);
		$json_examples = block_exacomp_get_json_examples($examples);
		
		echo json_encode($json_examples);
		break;
	case 'get-examples-for-time-slot':
		$studentid = required_param('studentid', PARAM_INT);
		if(!$studentid) $studentid = $USER->id;
		$start = required_param('start', PARAM_INT);
		$end = required_param('end', PARAM_INT);
		
		$examples = block_exacomp_get_examples_for_time_slot_all_courses($studentid, $start, $end);
		$json_examples = block_exacomp_get_json_examples($examples);
		
		echo json_encode($json_examples);
		break;
	case 'get-weekly-schedule-events':
		$studentid = required_param('studentid', PARAM_INT);
		if(!$studentid) $studentid = $USER->id;
		
		$pool_course = required_param('pool_course', PARAM_INT);
		if(!$pool_course)$pool_course = $courseid;
		
		$week = optional_param('week', time(), PARAM_INT);
		$week = block_exacomp_add_days($week, 1 - date('N', $week));
		
		$start = required_param('start', PARAM_INT);
		$end = required_param('end', PARAM_INT);
		
		$examples_pool = block_exacomp_get_examples_for_pool($studentid, $week, $pool_course);
		$json_examples_pool = block_exacomp_get_json_examples($examples);
		
		$examples_time_slots = block_exacomp_get_examples_for_time_slot_all_courses($studentid, $start, $end);
		$json_examples_time_slots = block_exacomp_get_json_examples($examples);
		
		$examples = array();
		$examples['pool'] = $json_examples_pool; //for pool
		$examples['time_slot'] = $json_examples_time_slots; //for calendar
		
		echo json_encode($examples);
		
		break;
}
