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
require_once __DIR__."/inc.php";
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

$action = required_param('action', PARAM_TEXT);
switch($action){
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
	case ('crosssubj-share'):
		$crosssubjid = required_param('crosssubjid', PARAM_TEXT);
		$share_all = required_param('share_all', PARAM_BOOL);
		block_exacomp_share_crosssubject($crosssubjid, $share_all);
	
		if (!$share_all) {
			// save individual users
			$student_ids = block_exacomp\param::optional_array('students', array(PARAM_INT));
			$not_students_ids = block_exacomp\param::optional_array('not_students', array(PARAM_INT));
	
			foreach($student_ids as $studentid)
				block_exacomp_set_cross_subject_student($crosssubjid, $studentid);
			
			foreach($not_students_ids as $studentid)
				block_exacomp_unset_cross_subject_student($crosssubjid, $studentid);
		}
		
		\block_exacomp\event\crosssubject_added::log(['objectid' => $exampleid, 'courseid' => $courseid]);
		
		die('ok');
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
		
		if($studentid == BLOCK_EXACOMP_SHOW_ALL_STUDENTS){
			$course_students = block_exacomp_get_students_by_course($courseid);
			
			foreach($course_students as $student){
				block_exacomp_add_example_to_schedule($student->id, $exampleid, $creatorid, $courseid);
			}
			
			echo get_string('weekly_schedule_added_all', 'block_exacomp');
		}else if($studentid == 0){
			if(!block_exacomp_in_pre_planing_storage($exampleid, $creatorid, $courseid)){
				if(block_exacomp_add_example_to_schedule(0, $exampleid, $creatorid, $courseid))
					echo get_string('pre_planning_storage_added', 'block_exacomp');
			}else 
				echo get_string('pre_planning_storage_already_contains', 'block_exacomp');
		}else{
			if ( block_exacomp_add_example_to_schedule($studentid,$exampleid,$creatorid,$courseid) )
				echo get_string("weekly_schedule_added","block_exacomp");
		}
		
		break;
	case 'multi':
		$data = (object)block_exacomp\param::required_json('data');

		if (!empty($data->new_descriptors)) {
			$new_descriptors = block_exacomp\param::clean_array($data->new_descriptors, array((object)array(
				'parentid' => PARAM_INT,
				'topicid' => PARAM_INT,
				'niveauid' => PARAM_INT,
				'title' => PARAM_TEXT
			)));
			foreach ($new_descriptors as $descriptor) {
				block_exacomp_descriptor::insertInCourse($courseid, $descriptor);
			}
		}
		

		if (!empty($data->competencies_by_type)) {
			$competencies_by_type = block_exacomp\param::clean_array($data->competencies_by_type, array(PARAM_INT=>array((object)array(
				'compid' => PARAM_INT,
				'userid' => PARAM_INT,
				'value' => PARAM_INT
			))));
			foreach ($competencies_by_type as $comptype => $competencies) {
				foreach($competencies as $comp){
					block_exacomp_set_user_competence ( $comp->userid, $comp->compid, $comptype, $courseid, ($isTeacher) ? block_exacomp::ROLE_TEACHER : block_exacomp::ROLE_STUDENT, $comp->value );
				}
			}
		}

		if (!empty($data->update_crosssubj)) {
			$update_crosssubj = block_exacomp\param::clean_object($data->update_crosssubj, array(
				'id' => PARAM_INT,
				'subjectid' => PARAM_INT,
				'title' => PARAM_TEXT,
				'description' => PARAM_TEXT
			));
			
			if ($update_crosssubj) {
				// don't update title if empty
				if (empty($update_crosssubj->title)) unset($update_crosssubj->title);
				
				// TODO: pruefen ob mein crosssubj?
				$DB->update_record(block_exacomp::DB_CROSSSUBJECTS, $update_crosssubj);
			}
		}
		
		if (!empty($data->examples)) {
			$examples = block_exacomp\param::clean_array($data->examples, array((object)array(
				'userid' => PARAM_INT,
				'exampleid' => PARAM_INT,
				'value' => PARAM_INT
			)));
			foreach($examples as $example){
				block_exacomp_set_user_example($example->userid, $example->exampleid, $courseid, ($isTeacher) ? block_exacomp::ROLE_TEACHER : block_exacomp::ROLE_STUDENT, $example->value);
			}
		}
		
		if(!empty($data->competencies_additional_grading)){
			
			$additional_grading = block_exacomp\param::clean_array($data->competencies_additional_grading, 
				array(PARAM_INT=>
					array(PARAM_INT=>PARAM_TEXT)
				)
			);
			
			foreach($additional_grading as $descrid => $students){
				foreach($students as $studentid=>$value){
					block_exacomp_save_additional_grading_for_descriptor($courseid, $descrid, $studentid, $value);
				}
			}
		}
		if(!empty($data->examples_additional_grading)){
			
			$additional_grading = block_exacomp\param::clean_array($data->examples_additional_grading, 
				array(PARAM_INT=>
					array(PARAM_INT=>PARAM_INT)
				)
			);
			
			foreach($additional_grading as $exampleid => $students){
				foreach($students as $studentid=>$value){
					block_exacomp_save_additional_grading_for_example($courseid, $exampleid, $studentid, $value);
				}
			}
		}
		die('ok');
	case 'delete-crosssubject':
		$crosssubjectid = required_param('crosssubjid', PARAM_INT);
		
		// TODO: pruefen ob mein crosssubj?
		
		//delete student-crosssubject association
		$DB->delete_records(block_exacomp::DB_CROSSSTUD, array('crosssubjid'=>$crosssubjectid));
		
		//delete descriptor-crosssubject association
		$DB->delete_records(block_exacomp::DB_DESCCROSS, array('crosssubjid'=>$crosssubjectid));
		
		//delete crosssubject overall evaluations
		$DB->delete_records(block_exacomp::DB_COMPETENCIES, array('compid'=>$crosssubjectid, 'comptype'=>TYPE_CROSSSUB));
		
		//delete crosssubject
		$DB->delete_records(block_exacomp::DB_CROSSSUBJECTS, array('id'=>$crosssubjectid));
		break;
	case 'set-example-start-end':
		$scheduleid = required_param('scheduleid', PARAM_INT);
		$start = required_param('start', PARAM_INT);
		$end = required_param('end', PARAM_INT);
		$deleted = optional_param('deleted', 0, PARAM_INT);
		echo $start;
		
		block_exacomp_set_example_start_end($scheduleid, $start, $end, $deleted);
		break;
	case 'remove-example-from-schedule':
		$scheduleid = required_param('scheduleid', PARAM_INT);
		
		block_exacomp_remove_example_from_schedule($scheduleid);
		break;
	case 'get-examples-for-start-end':
		$studentid = required_param('studentid', PARAM_INT);
		if(!$studentid) $studentid = $USER->id;
		$start = required_param('start', PARAM_INT);
		$end = required_param('end', PARAM_INT);
		
		$examples = block_exacomp_get_examples_for_start_end_all_courses($studentid, $start, $end);
		foreach($examples as $example){
			$example->state = block_exacomp_get_dakora_state_for_example($example->courseid, $example->exampleid, $studentid);
		}
		$json_examples = block_exacomp_get_json_examples($examples);
		
		echo json_encode($json_examples);
		break;
	case 'get-weekly-schedule-configuration':
		$studentid = required_param('studentid', PARAM_INT);
		if(!$studentid) $studentid = $USER->id;
		
		$pool_course = required_param('pool_course', PARAM_INT);
		if(!$pool_course)$pool_course = $courseid;
		
		$examples_pool = block_exacomp_get_examples_for_pool($studentid, $pool_course);
		foreach($examples_pool as &$example_pool){
			$example_pool->state = block_exacomp_get_dakora_state_for_example($example_pool->courseid, $example_pool->exampleid, $studentid);
		}
		$json_examples_pool = block_exacomp_get_json_examples($examples_pool);
		
		$examples_trash = block_exacomp_get_examples_for_trash($studentid, $pool_course);
		$json_examples_trash = block_exacomp_get_json_examples($examples_trash);
		
		$json_time_slots = block_exacomp_build_json_time_slots();
		
		$configuration = array();
		$configuration['pool'] = $json_examples_pool; //for pool
		$configuration['trash'] = $json_examples_trash; //for trash
		$configuration['slots'] = $json_time_slots; //for calendar
		
		echo json_encode($configuration);
		
		break;
	case 'empty-trash':
		$studentid = required_param('studentid', PARAM_INT);
		if(!$studentid) $studentid = $USER->id;
		
		$schedules = block_exacomp_get_examples_for_trash($studentid, $courseid);
		foreach($schedules as $schedule){
			block_exacomp_remove_example_from_schedule($schedule->id);
		}
		break;
	case 'get-pre-planning-storage':
		$creatorid = required_param('creatorid', PARAM_INT);
		$examples = block_exacomp_get_pre_planning_storage($creatorid, $courseid);
		
		$json_examples = block_exacomp_get_json_examples($examples, false);
		
		echo json_encode($json_examples);
		break;
	case('example-up'):
		$exampleid = required_param('exampleid', PARAM_INT);
		$descrid = required_param('descrid', PARAM_INT);
		
		echo block_exacomp_example_up($exampleid, $descrid);
		break;
	case ('example-down') :
		$exampleid = required_param ( 'exampleid', PARAM_INT );
		$descrid = required_param ( 'descrid', PARAM_INT );
		
		echo block_exacomp_example_down ( $exampleid, $descrid );
		break;
	case 'delete-descriptor':
		if (!$isTeacher) {
			print_error('noteacher');
		}
		
		block_exacomp_delete_custom_descriptor(required_param('id', PARAM_INT));
		break;
	case 'allow-resubmission':
		if (!$isTeacher) {
			print_error('noteacher');
		}
		$studentid = required_param('studentid', PARAM_INT);
		$exampleid = required_param('exampleid', PARAM_INT);
		$courseid = required_param('courseid', PARAM_INT);
		
		echo block_exacomp_allow_resubmission($studentid, $exampleid, $courseid);
		break;
	case 'send-message-to-course':
			if (!$isTeacher) {
				print_error('noteacher');
			}
			$message = required_param('message', PARAM_TEXT);
			$courseid = required_param('courseid', PARAM_INT);
		
			echo block_exacomp_send_message_to_course($courseid, $message);
			break;
	default:
		print_error('wrong action: '.$action);
}
