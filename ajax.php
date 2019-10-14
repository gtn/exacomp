<?php
// This file is part of Exabis Competence Grid
//
// (c) 2016 GTN - Global Training Network GmbH <office@gtn-solutions.com>
//
// Exabis Competence Grid is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This script is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You can find the GNU General Public License at <http://www.gnu.org/licenses/>.
//
// This copyright notice MUST APPEAR in all copies of the script!

require __DIR__.'/inc.php';
require_once __DIR__.'/backup/test_backup.php';

$courseid = required_param ( 'courseid', PARAM_INT );
$action = required_param('action', PARAM_TEXT);

if (! $course = $DB->get_record ( 'course', array (
	'id' => $courseid,
))
) {
	print_error ( 'invalidcourse', 'block_simplehtml', $courseid );
}

require_login ( $course );
$isTeacher = block_exacomp_is_teacher($courseid);

require_sesskey();

switch($action){
    case ('dismiss_gradingisold_warning'):
        $descrid = required_param('descrid', PARAM_INT);
        $studentid = required_param('studentid', PARAM_INT);
        
        //block_exacomp_set_descriptor_grading_timestamp($courseid,$descrid,$studentid);
        block_exacomp_unset_descriptor_gradingisold($courseid,$descrid,$studentid);
      
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
	case('export-activity'):

	    $activityid = required_param('activityid', PARAM_INT);
	    \block_exacomp\data::prepare();
	    block_exacomp\data_exporter::do_activity_export($activityid);
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
	case('hide-solution'):
		$exampleid = required_param('exampleid', PARAM_INT);
		$courseid = required_param('courseid', PARAM_INT);
		$visible = required_param('value', PARAM_INT);
		$studentid = required_param('studentid', PARAM_INT);
		
		block_exacomp_set_example_solution_visibility($exampleid, $courseid, $visible, $studentid);
		break;
	case('hide-topic'):
		$topicid = required_param('topicid', PARAM_INT);
		$courseid = required_param('courseid', PARAM_INT);
		$visible = required_param('value', PARAM_INT);
		$studentid = required_param('studentid', PARAM_INT);
	
		block_exacomp_set_topic_visibility($topicid, $courseid, $visible, $studentid);
		break;
	case('add-example-to-schedule'):
	    $studentid = optional_param('studentid',null, PARAM_INT);
		$groupid = optional_param('groupid',null, PARAM_INT);
		$courseid = optional_param('courseid',null, PARAM_INT);
		$exampleid = required_param('exampleid', PARAM_INT);
		$creatorid = $USER->id;
		
		
		if($groupid!=null){ //add for group
		    $groupmembers = block_exacomp_groups_get_members($courseid,$groupid);
		    foreach($groupmembers as $member){
		        if (block_exacomp_add_example_to_schedule($member->id,$exampleid,$creatorid,$courseid) ) {
		            echo block_exacomp_get_string("weekly_schedule_added").": ".$member->firstname." ".$member->lastname."\n";
		        }
		    }
		}else{ //add for student
		    if($studentid == BLOCK_EXACOMP_SHOW_ALL_STUDENTS){
		        $course_students = block_exacomp_get_students_by_course($courseid);
		        foreach($course_students as $student){
		            block_exacomp_add_example_to_schedule($student->id, $exampleid, $creatorid, $courseid);
		        }
		        
		        echo block_exacomp_get_string('weekly_schedule_added_all');
		    } elseif ($studentid == 0){
		        if (!block_exacomp_in_pre_planing_storage($exampleid, $creatorid, $courseid)){
		            if (block_exacomp_add_example_to_schedule(0, $exampleid, $creatorid, $courseid))
		                echo block_exacomp_get_string('pre_planning_storage_added');
		        } else {
		            echo block_exacomp_get_string('pre_planning_storage_already_contains');
		        }
		    } else {
		        if (block_exacomp_add_example_to_schedule($studentid,$exampleid,$creatorid,$courseid) ) {
		            echo block_exacomp_get_string("weekly_schedule_added");
		        }
		    }
		}
		exit;
	case 'multi':
		$data = (object)block_exacomp\param::required_json('data');

		if (!empty($data->new_descriptors)) {
			$new_descriptors = block_exacomp\param::clean_array($data->new_descriptors, array((object)array(
				'parentid' => PARAM_INT,
				'topicid' => PARAM_INT,
				'niveauid' => PARAM_INT,
				'title' => PARAM_TEXT,
			)));
			foreach ($new_descriptors as $descriptor) {
				\block_exacomp\descriptor::insertInCourse($courseid, $descriptor);
			}
		}
		

		if (!empty($data->competencies_by_type)) {
			$competencies_by_type = block_exacomp\param::clean_array($data->competencies_by_type, array(PARAM_INT=>array((object)array(
				'compid' => PARAM_INT,
				'userid' => PARAM_INT,
				'value' => PARAM_INT,
				'niveauid' => PARAM_INT,
			))));
			
			foreach ($competencies_by_type as $comptype => $competencies) {
				foreach($competencies as $comp){
					block_exacomp_set_user_competence ( $comp->userid, $comp->compid, $comptype, $courseid, ($isTeacher) ? BLOCK_EXACOMP_ROLE_TEACHER : BLOCK_EXACOMP_ROLE_STUDENT, $comp->value, $comp->niveauid );
				}
			}
		}

		if (!empty($data->examples)) {
			$examples = block_exacomp\param::clean_array($data->examples, array((object)array(
				'userid' => PARAM_INT,
				'exampleid' => PARAM_INT,
				'value' => PARAM_RAW,// PARAM_INT,
				'niveauid' => PARAM_INT,
			)));

			foreach($examples as $example){
				block_exacomp_set_user_example($example->userid, $example->exampleid, $courseid, ($isTeacher) ? BLOCK_EXACOMP_ROLE_TEACHER : BLOCK_EXACOMP_ROLE_STUDENT, $example->value, $example->niveauid);
			}
            if ($isTeacher) {
                block_exacomp_etheme_autograde_examples_tree($courseid, $examples);
            }
		}

/*		if(!empty($data->examples_additional_grading)){
			$additional_grading = block_exacomp\param::clean_array($data->examples_additional_grading,
				array(PARAM_INT=>
					array(PARAM_INT => PARAM_TEXT),
				)
			);
			foreach($additional_grading as $exampleid => $students){
				foreach($students as $studentid=>$value){
					block_exacomp_save_additional_grading_for_comp($courseid, $exampleid, $studentid, $value, BLOCK_EXACOMP_TYPE_EXAMPLE);
				}
			}
		}*/
		if(!empty($data->competencies_additional_grading)){

			$additional_grading = block_exacomp\param::clean_array($data->competencies_additional_grading,
				array(PARAM_INT=>
					array(PARAM_INT => PARAM_TEXT),
				)
			);

			foreach($additional_grading as $descrid => $students){
				foreach($students as $studentid=>$value){
					block_exacomp_save_additional_grading_for_comp($courseid, $descrid, $studentid, $value, BLOCK_EXACOMP_TYPE_DESCRIPTOR);
				}
			}
		}
		// TODO: refactor, use generic comp array with type instead of 3 arrays for comp, topic and subject
		if(!empty($data->topics_additional_grading)){
			$additional_grading = block_exacomp\param::clean_array($data->topics_additional_grading,
					array(PARAM_INT=>
							array(PARAM_INT=>PARAM_TEXT)
					)
					);
				
			foreach($additional_grading as $descrid => $students){
				foreach($students as $studentid=>$value){
					block_exacomp_save_additional_grading_for_comp($courseid, $descrid, $studentid, $value, BLOCK_EXACOMP_TYPE_TOPIC);
				}
			}
		}
		if(!empty($data->crosssubs_additional_grading)){
			$additional_grading = block_exacomp\param::clean_array($data->crosssubs_additional_grading,
					array(PARAM_INT=>
							array(PARAM_INT=>PARAM_TEXT)
					)
			);
		
			foreach($additional_grading as $descrid => $students){
				foreach($students as $studentid=>$value){
					block_exacomp_save_additional_grading_for_comp($courseid, $descrid, $studentid, $value, BLOCK_EXACOMP_TYPE_CROSSSUB);
				}
			}
		}
		if(!empty($data->subjects_additional_grading)){
			$additional_grading = block_exacomp\param::clean_array($data->subjects_additional_grading,
					array(PARAM_INT=>
							array(PARAM_INT=>PARAM_TEXT)
					)
					);
		
			foreach($additional_grading as $descrid => $students){
				foreach($students as $studentid=>$value){
					block_exacomp_save_additional_grading_for_comp($courseid, $descrid, $studentid, $value, BLOCK_EXACOMP_TYPE_SUBJECT);
				}
			}
		}
		die('ok');
	case 'delete-crosssubject':
		$crosssubjectid = required_param('crosssubjid', PARAM_INT);
		
		// TODO: pruefen ob mein crosssubj?
		
		//delete student-crosssubject association
		$DB->delete_records(BLOCK_EXACOMP_DB_CROSSSTUD, array('crosssubjid'=>$crosssubjectid));
		
		//delete descriptor-crosssubject association
		$DB->delete_records(BLOCK_EXACOMP_DB_DESCCROSS, array('crosssubjid'=>$crosssubjectid));
		
		//delete crosssubject overall evaluations
		$DB->delete_records(BLOCK_EXACOMP_DB_COMPETENCES, array('compid'=>$crosssubjectid, 'comptype'=>BLOCK_EXACOMP_TYPE_CROSSSUB));
		
		//delete crosssubject
		$DB->delete_records(BLOCK_EXACOMP_DB_CROSSSUBJECTS, array('id'=>$crosssubjectid));
		break;
	case 'set-example-start-end':
		$scheduleid = required_param('scheduleid', PARAM_INT);
		$start = required_param('start', PARAM_INT);
		$end = required_param('end', PARAM_INT);
		$deleted = optional_param('deleted', 0, PARAM_INT);
		echo $start;
		
		block_exacomp_set_example_start_end($scheduleid, $start, $end, $deleted);
		exit;
	case 'remove-example-from-schedule':
		$scheduleid = required_param('scheduleid', PARAM_INT);
		
		block_exacomp_remove_example_from_schedule($scheduleid);
		break;
	case 'copy-example-from-schedule':
		$scheduleid = required_param('scheduleid', PARAM_INT);
		block_exacomp_copy_example_from_schedule($scheduleid);
		break;
	case 'get-examples-for-start-end':
		//$studentid = required_param('studentid', PARAM_INT);
		$studentid = optional_param('studentid', null, PARAM_INT);
		if (!$studentid) {
		    $studentid = $USER->id;
        }
		$start = required_param('start', PARAM_INT);
		$end = required_param('end', PARAM_INT);
		
		$examples = block_exacomp_get_examples_for_start_end_all_courses($studentid, $start, $end);
		foreach($examples as $example){
			$example->state = block_exacomp_get_dakora_state_for_example($example->courseid, $example->exampleid, $studentid);
		}
		$json_examples = block_exacomp_get_json_examples($examples);
		
		echo json_encode($json_examples);
		exit;
	case 'get-weekly-schedule-configuration':
		//$studentid = required_param('studentid', PARAM_INT);
		$studentid = optional_param('studentid', null, PARAM_INT);
		if (!$studentid){
		    $studentid = $USER->id;
		}
		
		// -1 => teacher wants to add examples for all students to their schedule
		if ($studentid == BLOCK_EXACOMP_SHOW_ALL_STUDENTS) {
			// check for teacher permissions
			block_exacomp_require_teacher();
			// now we want to display the examples from the pre-plan-storage, which are the examples in the schedule database table
			// with studentid 0
			$studentid = 0;
		}else if ($studentid < -1) { // smaller -1, teacher wants to add examples for a group
		    $studentid = 0;
		}
		
		//$pool_course = required_param('pool_course', PARAM_INT);
		$pool_course = optional_param('pool_course', null, PARAM_INT);
		if (!$pool_course) {
            $pool_course = $courseid;
        }

        $examples_pool = block_exacomp_get_examples_for_pool($studentid, $pool_course,0);

		foreach ($examples_pool as &$example_pool){
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
		
		exit;
	case 'empty-trash':
		//$studentid = required_param('studentid', PARAM_INT);
		$studentid = optional_param('studentid', null, PARAM_INT);
		if (!$studentid) {
		    $studentid = $USER->id;
        }
		
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
		exit;
	case 'add-examples-to-schedule-for-all':
		$courseid = required_param('courseid', PARAM_INT);
		block_exacomp_add_examples_to_schedule_for_all($courseid);
		break;
	case 'add-examples-to-schedule-for-group':
	    $courseid = required_param('courseid', PARAM_INT);
	    $groupid = required_param('groupid', PARAM_INT);
	    block_exacomp_add_examples_to_schedule_for_group($courseid,$groupid);
	    break;
	case('example-sorting'):
		$exampleid = required_param('exampleid', PARAM_INT);
		$descrid = required_param('descrid', PARAM_INT);
		$direction = required_param('direction', PARAM_TEXT);
		
		if ($direction == 'up') {
			block_exacomp_example_up($exampleid, $descrid);
		} else {
			block_exacomp_example_down($exampleid, $descrid);
		}
		die('ok');
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
		exit;
	case 'send-message-to-course':
			if (!$isTeacher) {
				print_error('noteacher');
			}
			$message = required_param('message', PARAM_TEXT);
			$courseid = required_param('courseid', PARAM_INT);
		
			echo block_exacomp_send_message_to_course($courseid, $message);
			exit;
	case 'create_blocking_event':
		$creatorid = required_param('creatorid', PARAM_INT);
		$title = required_param('title', PARAM_TEXT);
		
		block_exacomp_create_blocking_event($courseid, $title, $creatorid, 0);
		
		break;
	case 'get_statistics_for_profile' :
		if (block_exacomp_additional_grading (BLOCK_EXACOMP_TYPE_SUBJECT)) {
			$courseid = required_param ( 'courseid', PARAM_INT );
			$subjectid = required_param ( 'subjectid', PARAM_INT );
			$studentid = required_param ( 'studentid', PARAM_INT );
			$start = optional_param ( 'start', 0, PARAM_INT );
			$end = optional_param ( 'end', 0, PARAM_INT );
			
			$stat = block_exacomp_get_evaluation_statistic_for_subject ( $courseid, $subjectid, $studentid, $start, $end );
			$output = block_exacomp_get_renderer ();
			
			$tables = $output->subject_statistic_table ( $course->id, $stat['descriptor_evaluations'], 'Kompetenzen' );
			$tables .= $output->subject_statistic_table ( $course->id, $stat['child_evaluations'], 'Teilkompetenzen' );
			if(block_exacomp_course_has_examples($course->id))
				$tables .= $output->subject_statistic_table ( $course->id, $stat['example_evaluations'], 'Lernmaterialien' );
			echo html_writer::tag ( 'div', $tables, array (
					'class' => 'statistictables',
					'exa-subjectid' => $subjectid,
					'exa-courseid' => $courseid
			) );

			exit;
		}
		break;
	default:
		throw new moodle_exception('wrong action: '.$action);
}

echo 'ok';
