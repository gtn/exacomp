<?php
// This file is part of Exabis Competencies
//
// (c) 2016 GTN - Global Training Network GmbH <office@gtn-solutions.com>
//
// Exabis Competencies is free software: you can redistribute it and/or modify
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

defined('MOODLE_INTERNAL') || die();

require __DIR__.'/inc.php';
require_once $CFG->libdir.'/externallib.php';
require_once $CFG->dirroot.'/mod/assign/locallib.php';
require_once $CFG->dirroot.'/mod/assign/submission/file/locallib.php';
require_once $CFG->dirroot.'/lib/filelib.php';

use block_exacomp\globals as g;

class block_exacomp_external extends external_api {

	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function get_courses_parameters() {
		return new external_function_parameters ( array (
				'userid' => new external_value ( PARAM_INT, 'id of user' )
		) );
	}

	/**
	 * get courses
	 *
	 * @return array of user courses
	 */
	public static function get_courses($userid) {
		global $CFG, $DB, $USER;
		require_once ("$CFG->dirroot/lib/enrollib.php");

		static::validate_parameters ( static::get_courses_parameters (), array (
				'userid' => $userid
		) );

		if (!$userid)
			$userid = $USER->id;

		static::require_can_access_user($userid);

		$mycourses = enrol_get_users_courses ( $userid, true );
		$courses = array ();

		foreach ( $mycourses as $mycourse ) {
			$context = context_course::instance ( $mycourse->id );
			if ($DB->record_exists ( "block_instances", array (
					"blockname" => "exacomp",
					"parentcontextid" => $context->id
			) )) {
				$course = array (
						"courseid" => $mycourse->id,
						"fullname" => $mycourse->fullname,
						"shortname" => $mycourse->shortname
				);
				$courses [] = $course;
			}
		}

		return $courses;
	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_multiple_structure
	 */
	public static function get_courses_returns() {
		return new external_multiple_structure ( new external_single_structure ( array (
				'courseid' => new external_value ( PARAM_INT, 'id of course' ),
				'fullname' => new external_value ( PARAM_TEXT, 'fullname of course' ),
				'shortname' => new external_value ( PARAM_RAW, 'shortname of course' )
		) ) );
	}

	/*
	 * Returns description of method parameters
	 * @return external_function_parameters
	 */
	public static function get_examples_for_subject_parameters() {
		return new external_function_parameters ( array (
				'subjectid' => new external_value ( PARAM_INT, 'id of subject' ),
				'courseid' => new external_value ( PARAM_INT, 'id of course' ),
				'userid' => new external_value ( PARAM_INT, 'id of user' )
		) );
	}
	/**
	 * Get examples
	 *
	 * @param
	 *			int subjectid
	 * @return array of examples
	 */
	public static function get_examples_for_subject($subjectid, $courseid, $userid) {
		global $DB, $USER;

		if (empty ( $subjectid ) || empty ( $courseid )) {
			throw new invalid_parameter_exception ( 'Parameter can not be empty' );
		}

		static::validate_parameters ( static::get_examples_for_subject_parameters (), array (
				'subjectid' => $subjectid,
				'courseid' => $courseid,
				'userid' => $userid
		) );

		if ($userid == 0)
			$userid = $USER->id;

		static::require_can_access_user($userid);

		$structure = array ();

		$topics = block_exacomp_get_topics_by_subject ( $courseid, $subjectid );
		foreach ( $topics as $topic ) {
			if (! array_key_exists ( $topic->id, $structure )) {
				$structure [$topic->id] = new stdClass ();
				$structure [$topic->id]->topicid = $topic->id;
				$structure [$topic->id]->title = $topic->title;
				$structure [$topic->id]->requireaction = false;
				$structure [$topic->id]->examples = array ();
			}
			$descriptors = block_exacomp_get_descriptors_by_topic ( $courseid, $topic->id, false, true );

			foreach ( $descriptors as $descriptor ) {
				$examples = $DB->get_records_sql ( "SELECT de.id as deid, e.id, e.title, e.externalurl,
						e.externalsolution, e.externaltask, e.completefile, e.description, e.creatorid
						FROM {" . \block_exacomp\DB_EXAMPLES . "} e
						JOIN {" . \block_exacomp\DB_DESCEXAMP . "} de ON e.id=de.exampid AND de.descrid=?
						", array (
						$descriptor->id
				) );

				foreach ( $examples as $example ) {
					$taxonomies = block_exacomp_get_taxonomies_by_example($example);
					if(!empty($taxonomies)){
						$taxonomy = reset($taxonomies);

						$example->taxid = $taxonomy->id;
						$example->tax = $taxonomy->title;
					}else{
						$example->taxid = null;
						$example->tax = "";
					}

					if (! array_key_exists ( $example->id, $structure [$topic->id]->examples )) {
						$structure [$topic->id]->examples [$example->id] = new stdClass ();
						$structure [$topic->id]->examples [$example->id]->exampleid = $example->id;
						$structure [$topic->id]->examples [$example->id]->example_title = $example->title;
						$structure [$topic->id]->examples [$example->id]->example_creatorid = $example->creatorid;
						$items_examp = $DB->get_records ( 'block_exacompitemexample', array (
								'exampleid' => $example->id
						) );
						$items = array ();
						foreach ( $items_examp as $item_examp ) {
							$item_db = $DB->get_record ( 'block_exaportitem', array (
									'id' => $item_examp->itemid
							) );
							if ($item_db->userid == $userid)
								$items [] = $item_examp;
						}
						if (! empty ( $items )) {
							// check for current
							$current_timestamp = 0;
							foreach ( $items as $item ) {
								if ($item->timecreated > $current_timestamp) {
									$structure [$topic->id]->examples [$example->id]->example_item = $item->itemid;
									$structure [$topic->id]->examples [$example->id]->example_status = $item->status;

									if($item->status == 0)
										$structure [$topic->id]->requireaction = true;
								}
							}
						} else {
							$structure [$topic->id]->examples [$example->id]->example_item = - 1;
							$structure [$topic->id]->examples [$example->id]->example_status = - 1;
						}
					}
				}
			}
		}

		return $structure;
	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_multiple_structure
	 */
	public static function get_examples_for_subject_returns() {
		return new external_multiple_structure ( new external_single_structure ( array (
				'topicid' => new external_value ( PARAM_INT, 'id of topic' ),
				'title' => new external_value ( PARAM_TEXT, 'title of topic' ),
				'requireaction' => new external_value( PARAM_BOOL, 'trainer action required or not'),
				'examples' => new external_multiple_structure ( new external_single_structure ( array (
						'exampleid' => new external_value ( PARAM_INT, 'id of example' ),
						'example_title' => new external_value ( PARAM_TEXT, 'title of example' ),
						'example_item' => new external_value ( PARAM_INT, 'current item id' ),
						'example_status' => new external_value ( PARAM_INT, 'status of current item' ),
						'example_creatorid' => new external_value ( PARAM_INT, 'creator of example' )
				) ) )
		) ) );
	}
	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function get_example_by_id_parameters() {
		return new external_function_parameters ( array (
				'exampleid' => new external_value ( PARAM_INT, 'id of example' )
		) );
	}

	/**
	 * Get example
	 *
	 * @param $exampleid
	 * @return example
	 * @throws invalid_parameter_exception
	 */
	public static function get_example_by_id($exampleid) {
		global $DB;

		if (empty ( $exampleid )) {
			throw new invalid_parameter_exception ( 'Parameter can not be empty' );
		}

		static::validate_parameters ( static::get_example_by_id_parameters (), array (
				'exampleid' => $exampleid
		) );

		$data = static::require_can_access_example($exampleid);

		$example = $DB->get_record (\block_exacomp\DB_EXAMPLES, array (
				'id' => $exampleid
		) );
		$example->description = htmlentities ( $example->description );
		$example->hassubmissions = ($DB->get_records('block_exacompitemexample',array('exampleid'=>$exampleid))) ? true : false;

		$task = block_exacomp_get_file_url($example, 'example_task', $data->courseid);
		if ($task)
			$example->task = (string)$task;
		elseif($example->externaltask)
			$example->task = (string)$example->externaltask;

		$solution = block_exacomp_get_file_url($example, 'example_solution', $data->courseid);
		if ($solution)
			$example->solution = (string)$solution;
		elseif($example->externalsolution)
			$example->solution = (string)$example->externalsolution;

		return $example;
	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_multiple_structure
	 */
	public static function get_example_by_id_returns() {
		return new external_single_structure ( array (
				'title' => new external_value ( PARAM_TEXT, 'title of example' ),
				'description' => new external_value ( PARAM_TEXT, 'description of example' ),
				'task' => new external_value ( PARAM_TEXT, 'task(url/description) of example' ),
				'solution' => new external_value ( PARAM_TEXT, 'solution(url/description) of example' ),
				'externalurl' => new external_value ( PARAM_TEXT, 'externalurl of example' ),
				'timeframe' => new external_value ( PARAM_INT, 'timeframe in minutes' ),
				'hassubmissions' => new external_value ( PARAM_BOOL, 'true if example has already submissions' )
		) );
	}
	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function get_descriptors_for_example_parameters() {
		return new external_function_parameters ( array (
				'exampleid' => new external_value ( PARAM_INT, 'id of example' ),
				'courseid' => new external_value ( PARAM_INT, 'id of course' ),
				'userid' => new external_value ( PARAM_INT, 'id of user' )
		) );
	}

	/**
	 * Get descriptors for example
	 *
	 * @param $exampleid
	 * @param $courseid
	 * @param $userid
	 * @return array list of descriptors
	 * @throws invalid_parameter_exception
	 */
	public static function get_descriptors_for_example($exampleid, $courseid, $userid) {
		global $DB, $USER;

		if ($userid == 0)
			$userid = $USER->id;

		static::validate_parameters ( static::get_descriptors_for_example_parameters (), array (
				'exampleid' => $exampleid,
				'courseid' => $courseid,
				'userid' => $userid
		) );

		static::require_can_access_user($userid);
		static::require_can_access_example($exampleid, $courseid);

		$descriptors_exam_mm = $DB->get_records (\block_exacomp\DB_DESCEXAMP, array (
				'exampid' => $exampleid
		) );

		$descriptors = array ();
		foreach ( $descriptors_exam_mm as $descriptor_mm ) {
			$descriptors [$descriptor_mm->descrid] = $DB->get_record (\block_exacomp\DB_DESCRIPTORS, array (
					'id' => $descriptor_mm->descrid
			) );

			$grading = \block_exacomp\ROLE_TEACHER;
			if(block_exacomp_is_elove_student_self_assessment_enabled() && !block_exacomp_is_teacher ( $course->id )) {
				$grading = \block_exacomp\ROLE_STUDENT;
			}
			$eval = block_exacomp\get_comp_eval($courseid, $grading, $userid, \block_exacomp\TYPE_DESCRIPTOR, $descriptor_mm->descrid);
			if ($eval && $eval->value !== null) {
				$descriptors [$descriptor_mm->descrid]->evaluation = $eval->value;
			} else {
				$descriptors [$descriptor_mm->descrid]->evaluation = 0;
			}

			$descriptors [$descriptor_mm->descrid]->descriptorid = $descriptor_mm->descrid;
		}
		return $descriptors;
	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_multiple_structure
	 */
	public static function get_descriptors_for_example_returns() {
		return new external_multiple_structure ( new external_single_structure ( array (
				'descriptorid' => new external_value ( PARAM_INT, 'id of descriptor' ),
				'title' => new external_value ( PARAM_TEXT, 'title of descriptor' ),
				'evaluation' => new external_value ( PARAM_INT, 'evaluation of descriptor' )
		) ) );
	}
	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function get_user_role_parameters() {
		return new external_function_parameters ( array () );
	}

	/**
	 * return 1 for trainer
	 * 2 for student
	 * 0 if false
	 *
	 * @param
	 *			int userid
	 * @return int
	 */
	public static function get_user_role() {
		global $DB, $USER;

		static::validate_parameters ( static::get_user_role_parameters (), array () );

		$trainer = $DB->get_records ( \block_exacomp\DB_EXTERNAL_TRAINERS, array (
				'trainerid' => $USER->id
		) );
		if ($trainer)
			return array (
					"role" => 1
			);

		$student = $DB->get_records ( \block_exacomp\DB_EXTERNAL_TRAINERS, array (
				'studentid' => $USER->id
		) );

		if ($student)
			return array (
					"role" => 2
			);

			// neither student nor trainer
		return array (
				"role" => 0
		);
	}

	/**
	 * Returns description of method return values
	 *
	 * @return external_multiple_structure
	 */
	public static function get_user_role_returns() {
		return new external_function_parameters ( array (
				'role' => new external_value ( PARAM_INT, '1=trainer, 2=student' )
		) );
	}

	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function get_external_trainer_students_parameters() {
		return new external_function_parameters ( array () );
	}

	/**
	 * Get all students for an external trainer
	 *
	 * @return array all items available
	 */
	public static function get_external_trainer_students() {
		global $DB, $USER;

		$students = $DB->get_records ( \block_exacomp\DB_EXTERNAL_TRAINERS, array (
				'trainerid' => $USER->id
		) );
		$returndata = array ();
		$cohorts = $DB->get_records('cohort');

		foreach ( $students as $student ) {
			$studentObject = $DB->get_record ( 'user', array (
					'id' => $student->studentid
			) );
			$returndataObject = new stdClass ();
			$returndataObject->name = fullname ( $studentObject );
			$returndataObject->userid = $student->studentid;
			$return_cohorts = array();

			$user_cohorts = $DB->get_records('cohort_members', array('userid' => $student->studentid));
			foreach ($user_cohorts as $user_cohort) {
				$currentCohort = new stdClass ();
				$currentCohort->cohortid = $user_cohort->cohortid;
				$currentCohort->name = $cohorts[$user_cohort->cohortid]->name;

				$return_cohorts [] = $currentCohort;
			}
			$returndataObject->cohorts = $return_cohorts;

			$require_actions = static::get_requireaction_subjects($student->studentid);
			$returndataObject->requireaction = !empty($require_actions);

			$returndata [] = $returndataObject;
		}
		return $returndata;
	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_multiple_structure
	 */
	public static function get_external_trainer_students_returns() {
		return new external_multiple_structure ( new external_single_structure ( array (
				'userid' => new external_value ( PARAM_INT, 'id of user' ),
				'name' => new external_value ( PARAM_TEXT, 'name of user' ),
				'cohorts' => new external_multiple_structure ( new external_single_structure ( array (
						'cohortid' => new external_value ( PARAM_INT, 'id of cohort' ),
						'name' => new external_value ( PARAM_TEXT, 'title of cohort' )
				) ) ),
				'requireaction' => new external_value( PARAM_BOOL, 'trainer action required or not')
		)
		 ) );
	}

	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function get_subjects_for_user_parameters() {
		return new external_function_parameters ( array (
				'userid' => new external_value ( PARAM_INT, 'id of user' )
		) );
	}

	/**
	 * get subjects from one user for all his courses
	 *
	 * @return array of user courses
	 */
	public static function get_subjects_for_user($userid) {
		global $CFG, $USER;
		require_once ("$CFG->dirroot/lib/enrollib.php");

		static::validate_parameters ( static::get_subjects_for_user_parameters (), array (
				'userid' => $userid
		) );

		if ($userid == 0)
			$userid = $USER->id;

		static::require_can_access_user($userid);

		$courses = static::get_courses ( $userid );

		$require_actions = static::get_requireaction_subjects($userid);

		$subjects_res = array ();
		foreach ( $courses as $course ) {
			$subjects = block_exacomp_get_subjects_by_course ( $course ["courseid"] );

			foreach ( $subjects as $subject ) {
				if (! array_key_exists ( $subject->id, $subjects_res )) {
					$elem = new stdClass ();
					$elem->subjectid = $subject->id;
					$elem->title = $subject->title;
					$elem->courseid = $course ["courseid"];
					$elem->requireaction = array_key_exists($subject->id, $require_actions);
					$subjects_res [] = $elem;
				}
			}
		}

		return $subjects_res;
	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_multiple_structure
	 */
	public static function get_subjects_for_user_returns() {
		return new external_multiple_structure ( new external_single_structure ( array (
				'subjectid' => new external_value ( PARAM_INT, 'id of subject' ),
				'title' => new external_value ( PARAM_TEXT, 'title of subject' ),
				'courseid' => new external_value ( PARAM_INT, 'id of course' ),
				'requireaction' => new external_value ( PARAM_BOOL, 'whether example in this subject has been edited or not by the selected student' )
		) ) );
	}

	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function delete_item_parameters() {
		return new external_function_parameters ( array (
				'itemid' => new external_value ( PARAM_INT, 'id of item' )
		) );
	}

	/**
	 * Deletes one user item if it is not graded already
	 *
	 * @param int $itemid
	 */
	public static function delete_item($itemid) {
		global $CFG,$DB,$USER;

		// TODO: check exaport available
		// TODO: check allowd to delete

		$item = $DB->get_record('block_exaportitem', array('id' => $itemid, 'userid' => $USER->id));
		if($item) {
			//check if the item is already graded
			$itemexample = $DB->get_record_sql("SELECT id, exampleid, itemid, status, MAX(timecreated) from {block_exacompitemexample} ie WHERE itemid = ?",array($itemid));
			if($itemexample->status == 0) {
				//delete item and all associated content
				$DB->delete_records('block_exacompitemexample',array('id' => $itemexample->id));
				$DB->delete_records('block_exaportitem',array('id' => $itemid));
				if($item->type == 'file') {
					require_once $CFG->dirroot . '/blocks/exaport/lib/lib.php';
					block_exaport_file_remove($item);
				}

				$DB->delete_records('block_exaportitemcomm',array('itemid' => $itemid));
				$DB->delete_records('block_exaportviewblock',array('itemid' => $itemid));
				return array("success"=>true);
			}
			throw new invalid_parameter_exception ( 'Not allowed; already graded' );
		}

		throw new invalid_parameter_exception ( 'Not allowed' );
	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_single_structure
	 */
	public static function delete_item_returns() {
		return new external_single_structure ( array (
				'success' => new external_value ( PARAM_BOOL, 'status' )
		) );
	}
	/**
	 * This method is used for eLove
	 *
	 * @return external_function_parameters
	 */
	public static function set_competence_parameters() {
		return new external_function_parameters ( array (
				'courseid' => new external_value ( PARAM_INT, 'id of course' ),
				'descriptorid' => new external_value ( PARAM_INT, 'id of descriptor' ),
				'value' => new external_value ( PARAM_INT, 'evaluation value' )
		) );
	}
	
	/**
	 * Set student evaluation
	 *
	 * @param
	 *			int courseid
	 * @param
	 *			int descriptorid
	 * @param
	 *			int value
	 * @return status
	 */
	public static function set_competence($courseid, $descriptorid, $value) {
		global $DB, $USER;
	
		if (empty ( $courseid ) || empty ( $descriptorid ) || ! isset ( $value )) {
			throw new invalid_parameter_exception ( 'Parameter can not be empty' );
		}
	
		static::validate_parameters ( static::set_competence_parameters (), array (
				'courseid' => $courseid,
				'descriptorid' => $descriptorid,
				'value' => $value
		) );
	
		static::require_can_access_course($courseid);
	
		$transaction = $DB->start_delegated_transaction (); // If an exception is thrown in the below code, all DB queries in this code will be rollback.
	
		$DB->delete_records ( 'block_exacompcompuser', array (
				"userid" => $USER->id,
				"role" => 0,
				"compid" => $descriptorid,
				"courseid" => $courseid,
				"comptype" => TYPE_DESCRIPTOR
		) );
		if ($value > 0) {
			$DB->insert_record ( 'block_exacompcompuser', array (
					"userid" => $USER->id,
					"role" => 0,
					"compid" => $descriptorid,
					"courseid" => $courseid,
					"comptype" => TYPE_DESCRIPTOR,
					"reviewerid" => $USER->id,
					"value" => $value
			) );
		}
	
		$transaction->allow_commit ();
	
		return array (
				"success" => true
		);
	}
	
	/**
	 * Returns desription of method return values
	 *
	 * @return external_multiple_structure
	 */
	public static function set_competence_returns() {
		return new external_single_structure ( array (
				'success' => new external_value ( PARAM_BOOL, 'status of success, either true (1) or false (0)' )
		) );
	}
	
	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function get_item_for_example_parameters() {
		return new external_function_parameters ( array (
				'userid' => new external_value ( PARAM_INT, 'id of user' ),
				'itemid' => new external_value ( PARAM_INT, 'id of item' )
		) );
	}

	/**
	 * get subjects from one user for all his courses
	 *
	 * @return array of user courses
	 */
	public static function get_item_for_example($userid, $itemid) {
		global $CFG, $DB, $USER;

		if ($userid == 0)
			$userid = $USER->id;

		static::validate_parameters ( static::get_item_for_example_parameters (), array (
				'userid' => $userid,
				'itemid' => $itemid
		) );

		static::require_can_access_user($userid);
		// TODO: can access item? can user access all items of that user

		$conditions = array (
				"id" => $itemid,
				"userid" => $userid
		);
		$item = $DB->get_record ( "block_exaportitem", $conditions, 'id,userid,type,name,intro,url', MUST_EXIST );
		$itemexample = $DB->get_record ( "block_exacompitemexample", array (
				"itemid" => $itemid
		) );

		if (!$itemexample) {
			throw new invalid_parameter_exception ( 'Item not found' );
		}
		static::require_can_access_example($itemexample->exampleid);

		$item->file = "";
		$item->isimage = false;
		$item->filename = "";
		$item->effort = strip_tags ( $item->intro );
		$item->teachervalue = isset ( $itemexample->teachervalue ) ? $itemexample->teachervalue : 0;
		$item->studentvalue = isset ( $itemexample->studentvalue ) ? $itemexample->studentvalue : 0;
		$item->status = isset ( $itemexample->status ) ? $itemexample->status : 0;

		if ($item->type == 'file') {
			// TODO: moved code into exaport\api
			require_once $CFG->dirroot . '/blocks/exaport/lib/lib.php';

			$item->userid = $userid;
			if ($file = block_exaport_get_item_file ( $item )) {
				$item->file = ("{$CFG->wwwroot}/blocks/exaport/portfoliofile.php?access=portfolio/id/" . $userid . "&itemid=" . $itemid);
				$item->isimage = $file->is_valid_image ();
				$item->filename = $file->get_filename ();
			}
		}

		$item->studentcomment = '';
		$item->teachercomment = '';
		// TODO: change to exaport\api::get_item_comments()
		$itemcomments = $DB->get_records ( 'block_exaportitemcomm', array (
				'itemid' => $itemid
		), 'timemodified ASC', 'entry, userid', 0, 2 );
		if ($itemcomments) {
			foreach ( $itemcomments as $itemcomment ) {
				if ($userid == $itemcomment->userid) {
					$item->studentcomment = $itemcomment->entry;
				} else {
					$item->teachercomment = $itemcomment->entry;
				}
			}
		}

		return ($item);
	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_multiple_structure
	 */
	public static function get_item_for_example_returns() {
		return new external_single_structure ( array (
				'id' => new external_value ( PARAM_INT, 'id of item' ),
				'name' => new external_value ( PARAM_TEXT, 'title of item' ),
				'type' => new external_value ( PARAM_TEXT, 'type of item (note,file,link)' ),
				'url' => new external_value ( PARAM_TEXT, 'url' ),
				'effort' => new external_value ( PARAM_RAW, 'description of the effort' ),
				'filename' => new external_value ( PARAM_TEXT, 'title of item' ),
				'file' => new external_value ( PARAM_URL, 'file url' ),
				'isimage' => new external_value ( PARAM_BOOL, 'true if file is image' ),
				'status' => new external_value ( PARAM_INT, 'status of the submission' ),
				'teachervalue' => new external_value ( PARAM_INT, 'teacher grading' ),
				'studentvalue' => new external_value ( PARAM_INT, 'student grading' ),
				'teachercomment' => new external_value ( PARAM_TEXT, 'teacher comment' ),
				'studentcomment' => new external_value ( PARAM_TEXT, 'student comment' )
		) );
	}
	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function get_competencies_for_upload_parameters() {
		return new external_function_parameters ( array (
				'userid' => new external_value ( PARAM_INT, 'id of user' )
		) );
	}
	/**
	 * Get all available competencies
	 *
	 * @param
	 *			int subjectid
	 * @return array of examples
	 */
	public static function get_competencies_for_upload($userid) {
		global $USER;

		static::validate_parameters ( static::get_competencies_for_upload_parameters (), array (
				'userid' => $userid
		) );

		if (!$userid) $userid = $USER->id;
		static::require_can_access_user($userid);

		$structure = array ();

		$courses = static::get_courses ( $userid );

		foreach ( $courses as $course ) {
			$tree = block_exacomp_get_competence_tree ( $course ["courseid"] );

			foreach ( $tree as $subject ) {
				$elem_sub = new stdClass ();
				$elem_sub->subjectid = $subject->id;
				$elem_sub->subjecttitle = $subject->title;
				$elem_sub->topics = array ();
				foreach ( $subject->topics as $topic ) {
					$elem_topic = new stdClass ();
					$elem_topic->topicid = $topic->id;
					$elem_topic->topictitle = $topic->title;
					$elem_topic->descriptors = array ();
					foreach ( $topic->descriptors as $descriptor ) {
						$elem_desc = new stdClass ();
						$elem_desc->descriptorid = $descriptor->id;
						$elem_desc->descriptortitle = $descriptor->title;
						$elem_topic->descriptors [] = $elem_desc;
					}
					$elem_sub->topics [] = $elem_topic;
				}
				$structure [] = $elem_sub;
			}
		}

		return $structure;
	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_multiple_structure
	 */
	public static function get_competencies_for_upload_returns() {
		return new external_multiple_structure ( new external_single_structure ( array (
				'subjectid' => new external_value ( PARAM_INT, 'id of topic' ),
				'subjecttitle' => new external_value ( PARAM_TEXT, 'title of topic' ),
				'topics' => new external_multiple_structure ( new external_single_structure ( array (
						'topicid' => new external_value ( PARAM_INT, 'id of example' ),
						'topictitle' => new external_value ( PARAM_TEXT, 'title of example' ),
						'descriptors' => new external_multiple_structure ( new external_single_structure ( array (
								'descriptorid' => new external_value ( PARAM_INT, 'id of example' ),
								'descriptortitle' => new external_value ( PARAM_TEXT, 'title of example' )
						) ) )
				) ) )
		) ) );
	}

	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function submit_example_parameters() {
		return new external_function_parameters ( array (
				'exampleid' => new external_value ( PARAM_INT, 'exampleid' ),
				'studentvalue' => new external_value ( PARAM_INT, 'studentvalue' ),
				'url' => new external_value ( PARAM_URL, 'url' ),
				'effort' => new external_value ( PARAM_TEXT, 'effort' ),
				'filename' => new external_value ( PARAM_TEXT, 'filename, used to look up file and create a new one in the exaport file area' ),
				'studentcomment' => new external_value ( PARAM_TEXT, 'studentcomment' ),
				'title' => new external_value ( PARAM_TEXT, 'title' ),
				'itemid' => new external_value ( PARAM_INT, 'itemid' ),
				'courseid' => new external_value ( PARAM_INT, 'courseid' )
		) );
	}

	/**
	 * Add item
	 * @param $exampleid
	 * @param $studentvalue
	 * @param $url
	 * @param $effort
	 * @param $filename
	 * @param $studentcomment
	 * @param $title
	 * @param int $itemid
	 * @param int $courseid
	 * @return array of course subjects
	 * @throws invalid_parameter_exception
	 */
	public static function submit_example($exampleid,$studentvalue,$url,$effort,$filename,$studentcomment,$title,$itemid=0,$courseid=0) {
		global $CFG,$DB,$USER;

		static::validate_parameters(static::submit_example_parameters(), array('title'=>$title,'exampleid'=>$exampleid,'url'=>$url,'effort'=>$effort,'filename'=>$filename,'studentcomment'=>$studentcomment,'studentvalue'=>$studentvalue,'itemid'=>$itemid,'courseid'=>$courseid));

		if ($CFG->block_exaport_app_externaleportfolio) {
			// export to Mahara
			// TODO: besser als function call, nicht als include!
			if ($filename != '') {
				if ((include $CFG->dirroot.'/blocks/exacomp/upload_externalportfolio.php') == true) {
					if ($maharaexport_success) {
						$url = $result_querystring; // link to Mahara from upload_externalportfolio.php
						// Type of item is 'url' if all OK;
						$type = 'url';
					}
				};
			};
		}
		if (!isset($type)) {
			$type = ($filename != '') ? 'file' : 'url';
		};

		//insert: if itemid == 0 OR status != 0
		$insert = true;
		if($itemid != 0) {
			$itemexample = $DB->get_record('block_exacompitemexample', array('itemid'=>$itemid));
			if ($itemexample->status == 0)
				$insert = false;
		}
		require_once $CFG->dirroot . '/blocks/exaport/lib/lib.php';

		if($insert) {
			//store item in eLOVE portfolio category
			$elove_category = block_exaport_get_user_category("eLOVE", $USER->id);

			if(!$elove_category) {
				$elove_category = block_exaport_create_user_category("eLOVE", $USER->id);
			}

			$exampletitle = $DB->get_field('block_exacompexamples', 'title', array('id'=>$exampleid));
			$subjecttitle = block_exacomp_get_subjecttitle_by_example($exampleid);
			$subject_category = block_exaport_get_user_category($subjecttitle, $USER->id);
			if(!$subject_category) {
				$subject_category = block_exaport_create_user_category($subjecttitle, $USER->id, $elove_category->id);
			}

			$itemid = $DB->insert_record("block_exaportitem", array('userid'=>$USER->id,'name'=>$exampletitle,'url'=>$url,'intro'=>$effort,'type'=>$type,'timemodified'=>time(),'categoryid'=>$subject_category->id));
			//autogenerate a published view for the new item
			$dbView = new stdClass();
			$dbView->userid = $USER->id;
			$dbView->name = $exampletitle;
			$dbView->timemodified = time();
			$dbView->layout = 1;
			// generate view hash
			do {
				$hash = substr(md5(microtime()), 3, 8);
			} while ($DB->record_exists("block_exaportview", array("hash"=>$hash)));
			$dbView->hash = $hash;

			$dbView->id = $DB->insert_record('block_exaportview', $dbView);

			//share the view with teachers
			block_exaport_share_view_to_teachers($dbView->id);

			//add item to view
			$DB->insert_record('block_exaportviewblock',array('viewid'=>$dbView->id,'positionx'=>1, 'positiony'=>1, 'type'=>'item', 'itemid'=>$itemid));

			//add the example competencies to the item, so that it is displayed in the exacomp moodle block
			$comps = $DB->get_records('block_exacompdescrexamp_mm',array('exampid'=>$exampleid));
			foreach($comps as $comp) {
				$DB->insert_record('block_exacompcompactiv_mm', array('compid'=>$comp->descrid,'comptype'=>0,'eportfolioitem'=>1,'activityid'=>$itemid));
			}
		} else {
			$item = $DB->get_record('block_exaportitem',array('id'=>$itemid));
			$item->name = $title;
			if($url != '')
				$item->url = $url;
			$item->intro = $effort;
			$item->timemodified = time();

			if($type == 'file')
				block_exaport_file_remove($DB->get_record("block_exaportitem",array("id"=>$itemid)));

			$DB->update_record('block_exaportitem', $item);
		}

		//if a file is added we need to copy the file from the user/private filearea to block_exaport/item_file with the itemid from above
		if($type == "file") {
			$context = context_user::instance($USER->id);
			$fs = get_file_storage();
			try {
				$old = $fs->get_file($context->id, "user", "private", 0, "/", $filename);

				if($old) {
					$file_record = array('contextid'=>$context->id, 'component'=>'block_exaport', 'filearea'=>'item_file',
							'itemid'=>$itemid, 'filepath'=>'/', 'filename'=>$old->get_filename(),
							'timecreated'=>time(), 'timemodified'=>time());
					$fs->create_file_from_storedfile($file_record, $old->get_id());

					$old->delete();
				}
			} catch (Exception $e) {
				//some problem with the file occured
			}
		}

		if($insert) {
			$DB->insert_record('block_exacompitemexample',array('exampleid'=>$exampleid,'itemid'=>$itemid,'timecreated'=>time(),'status'=>0,'studentvalue'=>$studentvalue));
			if($studentcomment != '')
				$DB->insert_record('block_exaportitemcomm',array('itemid'=>$itemid,'userid'=>$USER->id,'entry'=>$studentcomment,'timemodified'=>time()));
		} else {
			$itemexample->timemodified = time();
			$itemexample->studentvalue = $studentvalue;
			$DB->update_record('block_exacompitemexample', $itemexample);
			if($studentcomment != '') {
				$DB->delete_records('block_exaportitemcomm',array('itemid'=>$itemid,'userid'=>$USER->id));
				$DB->insert_record('block_exaportitemcomm',array('itemid'=>$itemid,'userid'=>$USER->id,'entry'=>$studentcomment,'timemodified'=>time()));
			}
		}
		// studentvalue has to be stored in exameval
		block_exacomp_set_user_example($USER->id, $exampleid, $courseid, \block_exacomp\ROLE_STUDENT, $studentvalue);

		return array("success"=>true,"itemid"=>$itemid);
	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_single_structure
	 */
	public static function submit_example_returns() {
		return new external_single_structure ( array (
				'success' => new external_value ( PARAM_BOOL, 'status' ),
				'itemid' => new external_value ( PARAM_INT, 'itemid' )
		) );
	}
	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function create_example_parameters() {
		return new external_function_parameters ( array (
				'name' => new external_value ( PARAM_TEXT, 'title of example' ),
				'description' => new external_value ( PARAM_TEXT, 'description of example' ),
				'task' => new external_value ( PARAM_TEXT, 'task of example' ),
				'comps' => new external_value ( PARAM_TEXT, 'list of competencies, seperated by comma' ),
				'filename' => new external_value ( PARAM_TEXT, 'filename, used to look up file and create a new one in the exaport file area' )
		) );
	}

	/**
	 * create example. DO NOT USE
	 *
	 * @param $name
	 * @param $description
	 * @param $task
	 * @param $comps
	 * @param $filename
	 * @return array
	 * @throws invalid_parameter_exception
	 */
	public static function create_example($name, $description, $task, $comps, $filename) {
		global $CFG, $DB, $USER;

		if (empty ( $name )) {
			throw new invalid_parameter_exception ( 'Parameter can not be empty' );
		}

		static::validate_parameters ( static::create_example_parameters (), array (
				'name' => $name,
				'description' => $description,
				'task' => $task,
				'comps' => $comps,
				'filename' => $filename
		) );

		if ($filename != '') {
			$context = context_user::instance ( $USER->id );
			$fs = get_file_storage ();

			if (! $fs->file_exists ( $context->id, 'user', 'private', 0, '/', $filename )) {
				// TODO: das geht so nicht
				throw new moodle_exception('TODO: $form not set?');
				// $form->save_stored_file ( 'file', $context->id, 'user', 'private', 0, '/', $filename, true );
			}

			$pathnamehash = $fs->get_pathname_hash ( $context->id, 'user', 'private', 0, '/', $filename );
			$temp_task = new moodle_url ( $CFG->wwwroot . '/blocks/exacomp/example_upload.php', array (
					"action" => "serve",
					"c" => $context->id,
					"i" => $pathnamehash,
					"courseid" => 1
			) );
			$example_task = $temp_task->out ( false );
		}

		// insert into examples and example_desc
		$example = new stdClass ();
		$example->title = $name;
		$example->description = $description;
		$example->task = $task;
		$example->externaltask = isset ( $example_task ) ? $example_task : null;
		$example->creatorid = $USER->id;
		$example->timestamp = time();
		$example->source = \block_exacomp\EXAMPLE_SOURCE_USER;

		$id = $DB->insert_record (\block_exacomp\DB_EXAMPLES, $example );

		$descriptors = explode ( ',', $comps );
		foreach ( $descriptors as $descriptor ) {
			$insert = new stdClass ();
			$insert->exampid = $id;
			$insert->descrid = $descriptor;
			$DB->insert_record (\block_exacomp\DB_DESCEXAMP, $insert );

			//visibility entries for this example in course where descriptors are associated
			$courses = block_exacomp_get_courseids_by_descriptor($descriptor);
			foreach($courses as $course){
				$insert = new stdClass();
				$insert->courseid = $course->courseid;
				$insert->exampleid = $id;
				$insert->studentid = 0;
				$insert->visible = 1;
				$DB->insert_record(\block_exacomp\DB_EXAMPVISIBILITY, $insert);
			}
		}


		return array (
				"exampleid" => $id
		);
	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_multiple_structure
	 */
	public static function create_example_returns() {
		return new external_single_structure ( array (
				'exampleid' => new external_value ( PARAM_INT, 'id of created example' )
		) );
	}
	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function grade_item_parameters() {
		return new external_function_parameters ( array (
				'userid' => new external_value ( PARAM_INT, 'id of user' ),
				'value' => new external_value ( PARAM_INT, 'value for grading' ),
				'status' => new external_value ( PARAM_INT, 'status' ),
				'comment' => new external_value ( PARAM_TEXT, 'comment of grading' ),
				'itemid' => new external_value ( PARAM_INT, 'id of item' ),
				'comps' => new external_value ( PARAM_TEXT, 'comps for example - positive grading' ),
				'courseid' => new external_value ( PARAM_INT, 'if of course' )
		) );
	}

	/**
	 * grade an item
	 *
	 * @param $userid
	 * @param $value
	 * @param $status
	 * @param $comment
	 * @param $itemid
	 * @param $comps
	 * @param $courseid
	 * @return array
	 * @throws invalid_parameter_exception
	 *
	 */
	public static function grade_item($userid, $value, $status, $comment, $itemid, $comps, $courseid) {
		global $DB, $USER;

		if (empty ( $userid ) || empty ( $value ) || empty ( $comment ) || empty ( $itemid ) || empty ( $courseid )) {
			throw new invalid_parameter_exception ( 'Parameter can not be empty' );
		}

		static::validate_parameters ( static::grade_item_parameters (), array (
				'userid' => $userid,
				'value' => $value,
				'status' => $status,
				'comment' => $comment,
				'itemid' => $itemid,
				'comps' => $comps,
				'courseid' => $courseid
		) );

		if (!$userid) $userid = $USER->id;
		static::require_can_access_user($userid);

		// insert into block_exacompitemexample
		$update = $DB->get_record ( 'block_exacompitemexample', array (
				'itemid' => $itemid
		) );

		$exampleid = $update->exampleid;

		$update->itemid = $itemid;
		$update->datemodified = time ();
		$update->teachervalue = $value;
		$update->status = $status;

		$DB->update_record ( 'block_exacompitemexample', $update );
		// if the grading is good, tick the example in exacomp
		$exameval = $DB->get_record ( 'block_exacompexameval', array (
				'exampleid' => $exampleid,
				'courseid' => $courseid,
				'studentid' => $userid
		) );
		if ($exameval) {
			$exameval->teacher_evaluation = 1;
			$DB->update_record ( 'block_exacompexameval', $exameval );
		} else {
			$DB->insert_record ( 'block_exacompexameval', array (
					'exampleid' => $exampleid,
					'courseid' => $courseid,
					'studentid' => $userid,
					'teacher_evaluation' => 1
			) );
		}

		$insert = new stdClass ();
		$insert->itemid = $itemid;
		$insert->userid = $USER->id;
		$insert->entry = $comment;
		$insert->timemodified = time ();

		$DB->delete_records ( 'block_exaportitemcomm', array (
				'itemid' => $itemid,
				'userid' => $USER->id
		) );
		$DB->insert_record ( 'block_exaportitemcomm', $insert );

		// get all available descriptors and unset them who are not received via web service
		$descriptors_exam_mm = $DB->get_records (\block_exacomp\DB_DESCEXAMP, array (
				'exampid' => $exampleid
		) );

		$descriptors = explode ( ',', $comps );

		$unset_descriptors = array ();
		foreach ( $descriptors_exam_mm as $descr_examp ) {
			if (! in_array ( $descr_examp->descrid, $descriptors )) {
				$unset_descriptors [] = $descr_examp->descrid;
			}
		}

		// set positive graded competencies
		foreach ( $descriptors as $descriptor ) {
			if ($descriptor != 0) {
				$entry = block_exacomp\get_comp_eval($courseid, \block_exacomp\ROLE_TEACHER, $userid, \block_exacomp\TYPE_DESCRIPTOR, $descriptor);

				if ($entry) {
					$entry->reviewerid = $USER->id;
					$entry->value = 1;
					$entry->timestamp = time ();
					$DB->update_record (\block_exacomp\DB_COMPETENCIES, $entry );
				} else {
					$insert = new stdClass ();
					$insert->userid = $userid;
					$insert->compid = $descriptor;
					$insert->reviewerid = $USER->id;
					$insert->role = \block_exacomp\ROLE_TEACHER;
					$insert->courseid = $courseid;
					$insert->value = 1;
					$insert->timestamp = time ();

					$DB->insert_record (\block_exacomp\DB_COMPETENCIES, $insert );
				}
			}
		}

		// set negative graded competencies
		foreach ( $unset_descriptors as $descriptor ) {
			$entry = block_exacomp\get_comp_eval($courseid, \block_exacomp\ROLE_TEACHER, $userid, \block_exacomp\TYPE_DESCRIPTOR, $descriptor);

			if ($entry) {
				$entry->reviewerid = $USER->id;
				$entry->value = 0;
				$entry->timestamp = time ();
				$DB->update_record (\block_exacomp\DB_COMPETENCIES, $entry );
			} else {
				$insert = new stdClass ();
				$insert->userid = $userid;
				$insert->compid = $descriptor;
				$insert->reviewerid = $USER->id;
				$insert->role = \block_exacomp\ROLE_TEACHER;
				$insert->courseid = $courseid;
				$insert->value = 0;
				$insert->timestamp = time ();

				$DB->insert_record (\block_exacomp\DB_COMPETENCIES, $insert );
			}
		}

		return array (
				"success" => true
		);
	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_multiple_structure
	 */
	public static function grade_item_returns() {
		return new external_single_structure ( array (
				'success' => new external_value ( PARAM_BOOL, 'true if grading was successful' )
		) );
	}

	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function get_user_examples_parameters() {
		return new external_function_parameters ( array () );
	}

	/**
	 * grade an item
	 *
	 * @return array
	 * @throws invalid_parameter_exception
	 */
	public static function get_user_examples() {
		global $DB, $USER;

		static::validate_parameters ( static::get_user_examples_parameters (), array () );

		$subjects = static::get_subjects_for_user ( $USER->id );

		$examples = array ();
		foreach ( $subjects as $subject ) {
			$topics = static::get_examples_for_subject ( $subject->subjectid, $subject->courseid, 0 );
			foreach ( $topics as $topic ) {
				foreach ( $topic->examples as $example ) {
					if ($example->example_creatorid == $USER->id) {
						$elem = new stdClass ();
						$elem->exampleid = $example->exampleid;
						$elem->exampletitle = $example->example_title;
						$elem->exampletopicid = $topic->topicid;
						$items_examp = $DB->get_records ( 'block_exacompitemexample', array (
								'exampleid' => $example->exampleid
						) );
						$items = array ();
						foreach ( $items_examp as $item_examp ) {
							$item_db = $DB->get_record ( 'block_exaportitem', array (
									'id' => $item_examp->itemid
							) );
							if ($item_db->userid == $USER->id)
								$items [] = $item_examp;
						}
						if (! empty ( $items )) {
							// check for current
							$current_timestamp = 0;
							foreach ( $items as $item ) {
								if ($item->timecreated > $current_timestamp) {
									$elem->example_status = $item->status;
								}
							}
						} else {
							$elem->example_status = - 1;
						}

						$examples [] = $elem;
					}
				}
			}
		}

		return $examples;
		// return array();
	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_multiple_structure
	 */
	public static function get_user_examples_returns() {
		return new external_multiple_structure ( new external_single_structure ( array (
				'exampleid' => new external_value ( PARAM_INT, 'id of example' ),
				'exampletitle' => new external_value ( PARAM_TEXT, 'title of example' ),
				'example_status' => new external_value ( PARAM_INT, 'status of example' ),
				'exampletopicid' => new external_value ( PARAM_INT, 'topic id where example belongs to' )
		) ) );
	}
	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function get_user_profile_parameters() {
		return new external_function_parameters ( array (
				'userid' => new external_value ( PARAM_INT, 'id of user' )
		) );
	}

	/**
	 *
	 * @return array of user courses
	 */
	public static function get_user_profile($userid) {
		global $CFG, $DB, $USER;
		require_once ("$CFG->dirroot/lib/enrollib.php");

		static::validate_parameters ( static::get_user_profile_parameters (), array (
				'userid' => $userid
		) );

		if (!$userid) $userid = $USER->id;
		static::require_can_access_user($userid);

		$user = $DB->get_record ( 'user', array (
				'id' => $userid
		) );

		$grading = (block_exacomp_is_elove_student_self_assessment_enabled()) ? "student" : "teacher";
		
		// total data
		$total_competencies = 0;
		$total_examples = array ();
		$total_user_competencies = 0;
		$total_user_examples = array ();

		$courses = static::get_courses ( $userid );

		$subjects_res = array ();
		foreach ( $courses as $course ) {

			$subjects = block_exacomp_get_subjects_by_course ( $course ["courseid"] );
			$coursesettings = block_exacomp_get_settings_by_course ( $course ['courseid'] );
			$user = block_exacomp_get_user_information_by_course ( $user, $course ['courseid'] );
			$cm_mm = block_exacomp_get_course_module_association ( $course ['courseid'] );

			foreach ( $subjects as $subject ) {
				$subject_total_competencies = 0;
				$subject_total_examples = 0;
				$subject_reached_competencies = 0;
				$subject_reached_examples = 0;
				$subject_topics = array();

				$topics = block_exacomp_get_topics_by_subject ( $course ['courseid'], $subject->id );
				foreach ( $topics as $topic ) {
					$topic_total_competencies = 0;
					$topic_total_examples = 0;
					$topic_reached_competencies = 0;
					$topic_reached_examples = 0;

					if ($coursesettings->show_all_descriptors || ($coursesettings->uses_activities && isset ( $cm_mm->topics [$topic->id] )))
						$topic_total_competencies ++;

					if (! empty ( $user->topics->$grading )) {
						if (isset ( $user->topics->$grading ) && isset ( $user->topics->$grading [$topic->id] )) {
							$topic_reached_competencies ++;
						}
					}

					$descriptors = block_exacomp_get_descriptors_by_topic ( $course ['courseid'], $topic->id, false, true );
					foreach ( $descriptors as $descriptor ) {
						if ($coursesettings->show_all_descriptors || ($coursesettings->uses_activities && isset ( $cm_mm->competencies [$descriptor->id] )))
							$topic_total_competencies ++;

						if (! empty ( $user->competencies->$grading )) {
							if (isset ( $user->competencies->$grading ) && isset ( $user->competencies->$grading [$descriptor->id] )) {
								$topic_reached_competencies ++;
							}
						}

						$examples = $DB->get_records_sql ( "SELECT de.id as deid, e.id, e.title, e.externalurl,
						e.externalsolution, e.externaltask, e.completefile, e.description, e.creatorid
						FROM {" . \block_exacomp\DB_EXAMPLES . "} e
						JOIN {" . \block_exacomp\DB_DESCEXAMP . "} de ON e.id=de.exampid AND de.descrid=? ", array (
								$descriptor->id
						) );

						foreach ( $examples as $example ) {
							$taxonomies = block_exacomp_get_taxonomies_by_example($example);
							if(!empty($taxonomies)){
								$taxonomy = reset($taxonomies);

								$example->taxid = $taxonomy->id;
								$example->tax = $taxonomy->title;
							}else{
								$example->taxid = null;
								$example->tax = "";
							}
							if (! in_array ( $example->id, $total_examples )) {
								$total_examples [] = $example->id;
								$topic_total_examples ++;

								// CHECK FOR USER EXAMPLES
								$sql = 'select * from {block_exacompitemexample} ie
										JOIN {block_exaportitem} i ON i.id = ie.itemid
										WHERE ie.exampleid = ? AND i.userid=? AND ie.status=2';
								if ($DB->get_records_sql ( $sql, array (
										$example->id,
										$userid
								) )) {
									$total_user_examples [] = $example->id;
									$topic_reached_examples ++;
								}
							}
						}
					}
					$subject_total_competencies += $topic_total_competencies;
					$subject_reached_competencies += $topic_reached_competencies;
					$subject_total_examples += $topic_total_examples;
					$subject_reached_examples += $topic_reached_examples;

					if (! array_key_exists ( $topic->id, $subject_topics )) {
						$elem = new stdClass ();
						$elem->title = $topic->title;
						$elem->competencies = array (
								"total" => $topic_total_competencies,
								"reached" => $topic_reached_competencies
						);
						$elem->examples = array (
								"total" => $topic_total_examples,
								"reached" => $topic_reached_examples
						);
						$subject_topics [] = $elem;
					}
				}

				if (! array_key_exists ( $subject->id, $subjects_res )) {
					$elem = new stdClass ();
					$elem->title = $subject->title;
					$elem->competencies = array (
							"total" => $subject_total_competencies,
							"reached" => $subject_reached_competencies
					);
					$elem->examples = array (
							"total" => $subject_total_examples,
							"reached" => $subject_reached_examples
					);
					$elem->topics = $subject_topics;

					$subjects_res [] = $elem;
				}

				$total_competencies += $subject_total_competencies;
				$total_user_competencies += $subject_reached_competencies;
			}
		}

		$defaultdata = array ();
		$defaultdata ['user'] = array (
				"competencies" => array (
						"total" => $total_competencies,
						"reached" => $total_user_competencies
				),
				"examples" => array (
						"total" => count ( $total_examples ),
						"reached" => count ( $total_user_examples )
				)
		);
		$defaultdata ['subjects'] = array ();

		foreach ( $subjects_res as $subject_res ) {
			$cursubject = array (
					"title" => $subject_res->title,
					"data" => array (
							"competencies" => $subject_res->competencies,
							"examples" => $subject_res->examples
					),
					"topics" => array()
			);

			foreach($subject_res->topics as $topic) {
				$cursubject["topics"][] = array (
									"title" => $topic->title,
									"data" => array (
											"competencies" => $topic->competencies,
											"examples" => $topic->examples
									)
							);
			}

			$defaultdata ['subjects'] [] = $cursubject;
		}

		return $defaultdata;
	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_multiple_structure
	 */
	public static function get_user_profile_returns() {
		return new external_single_structure ( array (
				'user' => new external_single_structure ( array (
						'competencies' => new external_single_structure ( array (
								'total' => new external_value ( PARAM_INT, 'amount of total competencies' ),
								'reached' => new external_value ( PARAM_INT, 'amount of reached competencies' )
						) ),
						'examples' => new external_single_structure ( array (
								'total' => new external_value ( PARAM_INT, 'amount of total competencies' ),
								'reached' => new external_value ( PARAM_INT, 'amount of reached competencies' )
						) )
				) ),
				'subjects' => new external_multiple_structure ( new external_single_structure ( array (
						'title' => new external_value ( PARAM_TEXT, 'subject title' ),
						'data' => new external_single_structure ( array (
								'competencies' => new external_single_structure ( array (
										'total' => new external_value ( PARAM_INT, 'amount of total competencies' ),
										'reached' => new external_value ( PARAM_INT, 'amount of reached competencies' )
								) ),
								'examples' => new external_single_structure ( array (
										'total' => new external_value ( PARAM_INT, 'amount of total competencies' ),
										'reached' => new external_value ( PARAM_INT, 'amount of reached competencies' )
								) )
						) ),
						'topics' => new external_multiple_structure ( new external_single_structure ( array (
								'title' => new external_value ( PARAM_TEXT, 'topic title' ),
								'data' => new external_single_structure ( array (
										'competencies' => new external_single_structure ( array (
												'total' => new external_value ( PARAM_INT, 'amount of total competencies' ),
												'reached' => new external_value ( PARAM_INT, 'amount of reached competencies' )
										) ),
										'examples' => new external_single_structure ( array (
												'total' => new external_value ( PARAM_INT, 'amount of total competencies' ),
												'reached' => new external_value ( PARAM_INT, 'amount of reached competencies' )
										) )
								) )
						) ) )
				) ) )
		) );
	}
	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function update_example_parameters() {
		return new external_function_parameters ( array (
				'exampleid' => new external_value ( PARAM_INT, 'id of example' ),
				'name' => new external_value ( PARAM_TEXT, 'title of example' ),
				'description' => new external_value ( PARAM_TEXT, 'description of example' ),
				'task' => new external_value ( PARAM_TEXT, 'task of example' ),
				'comps' => new external_value ( PARAM_TEXT, 'list of competencies, seperated by comma' ),
				'filename' => new external_value ( PARAM_TEXT, 'filename, used to look up file and create a new one in the exaport file area' )
		) );
	}
	/**
	 * create example
	 *
	 * @param
	 *
	 * @return
	 *
	 */
	public static function update_example($exampleid, $name, $description, $task, $comps, $filename) {
		global $CFG, $DB, $USER;

		if (empty ( $exampleid ) || empty ( $name )) {
			throw new invalid_parameter_exception ( 'Parameter can not be empty' );
		}

		static::validate_parameters ( static::update_example_parameters (), array (
				'exampleid' => $exampleid,
				'name' => $name,
				'description' => $description,
				'task' => $task,
				'comps' => $comps,
				'filename' => $filename
		) );

		block_exacomp\require_item_capability(block_exacomp\CAP_MODIFY, block_exacomp\example::get($exampleid));

		$example_task = "";
		$type = ($filename != '') ? 'file' : 'url';
		if ( $type == 'file') {
			$context = context_user::instance ( $USER->id );
			$fs = get_file_storage ();

			if (! $fs->file_exists ( $context->id, 'user', 'private', 0, '/', $filename )) {
				// TODO: das geht so nicht
				throw new moodle_exception('TODO: $form not set?');
				// $form->save_stored_file ( 'file', $context->id, 'user', 'private', 0, '/', $filename, true );
			}

			$pathnamehash = $fs->get_pathname_hash ( $context->id, 'user', 'private', 0, '/', $filename );
			$temp_task = new moodle_url ( $CFG->wwwroot . '/blocks/exacomp/example_upload.php', array (
					"action" => "serve",
					"c" => $context->id,
					"i" => $pathnamehash,
					"courseid" => 1
			) );
			$example_task = $temp_task->out ( false );
		}

		$example = $DB->get_record (\block_exacomp\DB_EXAMPLES, array (
				'id' => $exampleid
		) );

		// insert into examples and example_desc
		$example->title = $name;
		$example->description = $description;
		$example->task = $task;
		if($type == 'file')
			$example->externaltask = $example_task;

		$DB->update_record (\block_exacomp\DB_EXAMPLES, $example );

		if (! empty ( $comps )) {
			$DB->delete_records (\block_exacomp\DB_DESCEXAMP, array (
					'exampid' => $exampleid
			) );

			$descriptors = explode ( ',', $comps );
			foreach ( $descriptors as $descriptor ) {
				$insert = new stdClass ();
				$insert->exampid = $exampleid;
				$insert->descrid = $descriptor;
				$DB->insert_record (\block_exacomp\DB_DESCEXAMP, $insert );
			}
		}

		return array (
				"success" => true
		);
	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_multiple_structure
	 */
	public static function update_example_returns() {
		return new external_single_structure ( array (
				'success' => new external_value ( PARAM_BOOL, 'true if successful' )
		) );
	}

	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function delete_example_parameters() {
		return new external_function_parameters ( array (
				'exampleid' => new external_value ( PARAM_INT, 'id of example' )
		) );
	}
	/**
	 * delete example
	 *
	 * @param
	 *
	 * @return
	 *
	 */
	public static function delete_example($exampleid) {
		global $DB, $USER;

		static::validate_parameters ( static::delete_example_parameters (), array (
				'exampleid' => $exampleid
		) );

		$example = $DB->get_record('block_exacompexamples', array('id' => $exampleid, 'creatorid' => $USER->id));
		if(!$example)
			throw new invalid_parameter_exception ( 'Parameter can not be empty' );

		// also checks for permissions
		block_exacomp_delete_custom_example($exampleid);

		$items = $DB->get_records('block_exacompitemexample',array('exampleid' => $exampleid));
		foreach($items as $item) {
			$DB->delete_records('block_exacompitemexample', array('id'=>$item->id));
			static::delete_item($item->itemid);
		}
		return array (
				"success" => true
		);
	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_multiple_structure
	 */
	public static function delete_example_returns() {
		return new external_single_structure ( array (
				'success' => new external_value ( PARAM_BOOL, 'true if successful' )
		) );
	}

	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function get_competencies_by_topic_parameters() {
		return new external_function_parameters ( array (
				'userid' => new external_value ( PARAM_INT, 'id of user' ),
				'topicid' => new external_value ( PARAM_INT, 'id of topic' )
		) );
	}
	/**
	 * Get all available competencies
	 *
	 * @param
	 *			int subjectid
	 * @return array of examples
	 */
	public static function get_competencies_by_topic($userid, $topicid) {
		global $USER;

		static::validate_parameters ( static::get_competencies_by_topic_parameters (), array (
				'userid' => $userid,
				'topicid' => $topicid
		) );

		if (!$userid) $userid = $USER->id;
		static::require_can_access_user($userid);

		$structure = array ();

		$courses = static::get_courses ( $userid );

		foreach ( $courses as $course ) {
			$tree = block_exacomp_get_competence_tree ( $course ["courseid"] );

			foreach ( $tree as $subject ) {
				foreach ( $subject->topics as $topic ) {
					if ($topicid == 0 || ($topicid != 0 && $topic->id == $topicid)) {
						foreach ( $topic->descriptors as $descriptor ) {
							$elem_desc = new stdClass ();
							$elem_desc->descriptorid = $descriptor->id;
							$elem_desc->descriptortitle = $descriptor->title;
							$structure [] = $elem_desc;
						}
					}
				}
			}
		}

		return $structure;
	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_multiple_structure
	 */
	public static function get_competencies_by_topic_returns() {
		return new external_multiple_structure ( new external_single_structure ( array (
				'descriptorid' => new external_value ( PARAM_INT, 'id of example' ),
				'descriptortitle' => new external_value ( PARAM_TEXT, 'title of example' )
		) ) );
	}

	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 *
	 */
	public static function dakora_set_competence_parameters() {
		return new external_function_parameters ( array (
				'courseid' => new external_value ( PARAM_INT, 'id of course' ),
				'userid' => new external_value(PARAM_INT, 'id of user, if 0 current user'),
				'compid' => new external_value(PARAM_INT, 'competence id'),
				'role' => new external_value(PARAM_INT, 'user role (0 == student, 1 == teacher)'),
				'value' => new external_value(PARAM_INT, 'evaluation value (0, 1, 2 or 3)'),
				'additionalinfo' => new external_value(PARAM_TEXT, 'additional grading 3 letters')
		) );
	}

	/**
	 * Set a competence for a user
	 *
	 * @param
	 *			int courseid
	 *			int userid
	 *			int compid
	 *			int role
	 *			int value
	 * @return success
	 */
	public static function dakora_set_competence($courseid, $userid, $compid, $role, $value, $additional_info) {
		global $USER, $DB;
		static::validate_parameters ( static::dakora_set_competence_parameters (), array (
				'courseid'=>$courseid,
				'userid'=>$userid,
				'compid'=>$compid,
				'role'=>$role,
				'value'=>$value,
				'additionalinfo'=>$additional_info
		) );

		if($userid == 0 && $role == \block_exacomp\ROLE_STUDENT)
			$userid = $USER->id;
			else if($userid == 0)
				throw new invalid_parameter_exception ( 'Userid can not be 0 for teacher grading' );

				static::require_can_access_course_user($courseid, $userid);

				if(block_exacomp_set_user_competence($userid, $compid, \block_exacomp\TYPE_DESCRIPTOR, $courseid, $role, $value) == -1)
					throw new invalid_parameter_exception ( 'Not allowed' );

					if($role == \block_exacomp\ROLE_TEACHER){
						block_exacomp_save_additional_grading_for_descriptor($courseid, $compid, $userid, $additional_info);
					}

					return array (
							"success" => true
					);
	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_multiple_structure
	 */
	public static function dakora_set_competence_returns() {
		return new external_single_structure ( array (
				'success' => new external_value ( PARAM_BOOL, 'status of success, either true (1) or false (0)' )
		) );
	}

	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function dakora_get_courses_parameters() {
		return new external_function_parameters ( array (
				'userid' => new external_value ( PARAM_INT, 'id of user, 0 for current user' )
		) );
	}


	/**
	 * get courses
	 *
	 * @return array of user courses
	 */
	public static function dakora_get_courses($userid) {
		return static::get_courses ( $userid );
	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_multiple_structure
	 */
	public static function dakora_get_courses_returns() {
		return new external_multiple_structure ( new external_single_structure ( array (
				'courseid' => new external_value ( PARAM_INT, 'id of course' ),
				'fullname' => new external_value ( PARAM_TEXT, 'fullname of course' ),
				'shortname' => new external_value ( PARAM_RAW, 'shortname of course' )
		) ) );
	}

	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function dakora_get_topics_by_course_parameters() {
		return new external_function_parameters ( array (
				'courseid' => new external_value ( PARAM_INT, 'id of course' )
		) );
	}

	/**
	 * get courses
	 *
	 * @return array of user courses
	 */
	public static function dakora_get_topics_by_course($courseid) {

		static::validate_parameters ( static::dakora_get_topics_by_course_parameters (), array (
				'courseid' => $courseid
		) );

		return static::dakora_get_topics_by_course_common($courseid, true);
	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_multiple_structure
	 */
	public static function dakora_get_topics_by_course_returns() {
		return new external_multiple_structure ( new external_single_structure ( array (
				'topicid' => new external_value ( PARAM_INT, 'id of topic' ),
				'topictitle' => new external_value ( PARAM_TEXT, 'title of topic' ),
				'numbering' => new external_value ( PARAM_TEXT, 'numbering for topic'),
				'subjectid'=> new external_value (PARAM_INT, 'id of subject'),
				'subjecttitle'=> new external_value (PARAM_TEXT, 'title of subject')
		) ) );
	}

	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function dakora_get_all_topics_by_course_parameters() {
		return new external_function_parameters ( array (
				'courseid' => new external_value ( PARAM_INT, 'id of course' )
		) );
	}

	/**
	 * get courses
	 *
	 * @return array of user courses
	 */
	public static function dakora_get_all_topics_by_course($courseid) {

		static::validate_parameters ( static::dakora_get_all_topics_by_course_parameters (), array (
				'courseid' => $courseid
		) );

		return static::dakora_get_topics_by_course_common($courseid, false);
	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_multiple_structure
	 */
	public static function dakora_get_all_topics_by_course_returns() {
		return new external_multiple_structure ( new external_single_structure ( array (
				'topicid' => new external_value ( PARAM_INT, 'id of topic' ),
				'topictitle' => new external_value ( PARAM_TEXT, 'title of topic' ),
				'numbering' => new external_value ( PARAM_TEXT, 'numbering for topic'),
				'subjectid' => new external_value (PARAM_INT, 'id of subject'),
				'subjecttitle' => new external_value (PARAM_TEXT, 'title of subject')
		) ) );
	}

	/*
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function dakora_get_descriptors_parameters() {
		return new external_function_parameters ( array (
				'courseid' => new external_value ( PARAM_INT, 'id of course'),
				'topicid' => new external_value ( PARAM_INT, 'id of topic' ),
				'userid' => new external_value ( PARAM_INT, 'id of user, 0 for current user'),
				'forall' => new external_value (PARAM_BOOL, 'for all users = true, for one user = false')
		) );
	}

	/**
	 * get descriptors for one topic, considering the visibility
	 *
	 * @return array of user courses
	 */
	public static function dakora_get_descriptors($courseid, $topicid, $userid, $forall) {
		global $USER;
		static::validate_parameters ( static::dakora_get_descriptors_parameters (), array (
				'courseid' => $courseid,
				'topicid' => $topicid,
				'userid' => $userid,
				'forall'=> $forall
		) );

		if($userid == 0 && $forall == false)
			$userid = $USER->id;

		return static::dakora_get_descriptors_common($courseid, $topicid, $userid, $forall, true);
	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_multiple_structure
	 */
	public static function dakora_get_descriptors_returns() {
		return new external_multiple_structure ( new external_single_structure ( array (
				'descriptorid' => new external_value ( PARAM_INT, 'id of descriptor' ),
				'descriptortitle' => new external_value ( PARAM_TEXT, 'title of descriptor' ),
				'numbering' => new external_value ( PARAM_TEXT, 'numbering for descriptor'),
				'niveautitle' => new external_value ( PARAM_TEXT, 'title of niveau'),
				'niveauid' => new external_value ( PARAM_INT, 'id of niveau')
		) ) );
	}

	/*
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function dakora_get_all_descriptors_parameters() {
		return new external_function_parameters ( array (
				'courseid' => new external_value ( PARAM_INT, 'id of course'),
				'topicid' => new external_value ( PARAM_INT, 'id of topic' ),
				'userid' => new external_value ( PARAM_INT, 'id of user, 0 for current user'),
				'forall' => new external_value (PARAM_BOOL, 'for all users = true, for one user = false')
		) );
	}

	/**
	 * get courses
	 *
	 * @return array of user courses
	 */
	public static function dakora_get_all_descriptors($courseid, $topicid, $userid, $forall) {
		global $USER;
		static::validate_parameters ( static::dakora_get_all_descriptors_parameters (), array (
				'courseid' => $courseid,
				'topicid' => $topicid,
				'userid' => $userid,
				'forall'=> $forall
		) );

		if($userid == 0 && $forall == false)
			$userid = $USER->id;

		return static::dakora_get_descriptors_common($courseid, $topicid, $userid, $forall, false);
	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_multiple_structure
	 */
	public static function dakora_get_all_descriptors_returns() {
		return new external_multiple_structure ( new external_single_structure ( array (
				'descriptorid' => new external_value ( PARAM_INT, 'id of descriptor' ),
				'descriptortitle' => new external_value ( PARAM_TEXT, 'title of descriptor' ),
				'numbering' => new external_value ( PARAM_TEXT, 'numbering for descriptor'),
				'niveautitle' => new external_value ( PARAM_TEXT, 'title of niveau'),
				'niveauid' => new external_value ( PARAM_INT, 'id of niveau')
		) ) );
	}

	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function dakora_get_descriptor_children_parameters() {
		return new external_function_parameters ( array (
				'courseid' => new external_value( PARAM_INT, 'id of course' ),
				'descriptorid' => new external_value ( PARAM_INT, 'id of parent descriptor' ),
				'userid' => new external_value ( PARAM_INT, 'id of user, 0 for current user'),
				'forall' => new external_value (PARAM_BOOL, 'for all users = true, for one user = false')
		) );
	}

	/**
	 * get courses
	 *
	 * @return array of user courses
	 */
	public static function dakora_get_descriptor_children($courseid, $descriptorid, $userid, $forall) {
		global $USER;
		static::validate_parameters ( static::dakora_get_descriptor_children_parameters (), array (
				'courseid' => $courseid,
				'descriptorid' => $descriptorid,
				'userid' => $userid,
				'forall' => $forall
		) );

		if($userid == 0 && !$forall)
			$userid = $USER->id;

		return static::get_descriptor_children($courseid, $descriptorid, $userid, $forall);

	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_multiple_structure
	 */
	public static function dakora_get_descriptor_children_returns() {
		return new external_single_structure ( array (
			'children' => new external_multiple_structure ( new external_single_structure ( array (
					'childid' => new external_value ( PARAM_INT, 'id of child' ),
					'childtitle' => new external_value ( PARAM_TEXT, 'title of child' ),
					'numbering' => new external_value ( PARAM_TEXT, 'numbering for child'),
					'teacherevaluation' => new external_value ( PARAM_INT, 'grading of child'),
					'studentevaluation' => new external_value ( PARAM_INT, 'self evaluation of child'),
					'examplestotal' => new external_value (PARAM_INT, 'total number of material'),
					'examplesvisible' => new external_value (PARAM_INT, 'visible number of material'),
					'examplesinwork' => new external_value (PARAM_FLOAT, 'edited number of material')
			) ) ) ,
			'examples' => new external_multiple_structure ( new external_single_structure ( array (
					'exampleid' => new external_value ( PARAM_INT, 'id of example' ),
					'exampletitle' => new external_value ( PARAM_TEXT, 'title of example' ),
					'examplestate' => new external_value ( PARAM_INT, 'state of example, always 0 if for all students' )
			) ) ) ,
			'examplestotal' => new external_value (PARAM_INT, 'number of total examples'),
			'examplesvisible' => new external_value (PARAM_INT, 'number of visible examples'),
			'examplesinwork' => new external_value (PARAM_FLOAT, 'number of examples in work')
		) ) ;
	}

	public static function dakora_get_examples_for_descriptor_parameters(){
		return new external_function_parameters ( array (
				'courseid' => new external_value( PARAM_INT, 'id of course' ),
				'descriptorid' => new external_value ( PARAM_INT, 'id of parent descriptor' ),
				'userid' => new external_value (PARAM_INT, 'id of user, if 0 current user'),
				'forall' => new external_value (PARAM_BOOL, 'if all users = true, only one user = false')
		) );
	}

	public static function dakora_get_examples_for_descriptor($courseid, $descriptorid, $userid, $forall){
		global $USER;
		static::validate_parameters ( static::dakora_get_examples_for_descriptor_parameters (), array (
				'courseid' => $courseid,
				'descriptorid' => $descriptorid,
				'userid' => $userid,
				'forall' => $forall
		) );

		if($userid == 0 && !$forall)
			$userid = $USER->id;

		return static::dakora_get_examples_for_descriptor_common($courseid, $descriptorid, $userid, $forall, 0);
	}

	public static function dakora_get_examples_for_descriptor_returns(){
		return new external_multiple_structure ( new external_single_structure ( array (
				'exampleid' => new external_value ( PARAM_INT, 'id of descriptor' ),
				'exampletitle' => new external_value ( PARAM_TEXT, 'title of descriptor' ),
				'examplestate' => new external_value ( PARAM_INT, 'state of example, always 0 if for all students' )
		) ) );
	}

	public static function dakora_get_examples_for_descriptor_with_grading_parameters(){
		return new external_function_parameters ( array (
				'courseid' => new external_value( PARAM_INT, 'id of course' ),
				'descriptorid' => new external_value ( PARAM_INT, 'id of parent descriptor' ),
				'userid' => new external_value (PARAM_INT, 'id of user, if 0 current user'),
				'forall' => new external_value (PARAM_BOOL, 'if all users = true, only one user = false')
		) );
	}

	public static function dakora_get_examples_for_descriptor_with_grading($courseid, $descriptorid, $userid, $forall){
		global $USER;
		static::validate_parameters ( static::dakora_get_examples_for_descriptor_with_grading_parameters (), array (
				'courseid' => $courseid,
				'descriptorid' => $descriptorid,
				'userid' => $userid,
				'forall' => $forall
		) );

		if($userid == 0 && !$forall)
			$userid = $USER->id;

		return static::dakora_get_examples_for_descriptor_common($courseid, $descriptorid, $userid, $forall, 0);
	}

	public static function dakora_get_examples_for_descriptor_with_grading_returns(){
		return new external_multiple_structure ( new external_single_structure ( array (
				'exampleid' => new external_value ( PARAM_INT, 'id of descriptor' ),
				'exampletitle' => new external_value ( PARAM_TEXT, 'title of descriptor' ),
				'examplestate' => new external_value ( PARAM_INT, 'state of example, always 0 if for all students' ),
				'teacherevaluation' => new external_value ( PARAM_INT, 'example evaluation of teacher'),
				'studentevaluation' => new external_value ( PARAM_INT, 'example evaluation of student'),
				'teacheritemvalue' => new external_value ( PARAM_INT, 'item evaluation of teacher')
		) ) );
	}


	public static function dakora_get_examples_for_descriptor_for_crosssubject_parameters(){
		return new external_function_parameters ( array (
				'courseid' => new external_value( PARAM_INT, 'id of course' ),
				'descriptorid' => new external_value ( PARAM_INT, 'id of parent descriptor' ),
				'userid' => new external_value (PARAM_INT, 'id of user, if 0 current user'),
				'forall' => new external_value (PARAM_BOOL, 'if all users = true, only one user = false'),
				'crosssubjid' => new external_value (PARAM_INT, 'id of crosssubject')
		) );
	}

	public static function dakora_get_examples_for_descriptor_for_crosssubject($courseid, $descriptorid, $userid, $forall, $crosssubjid){
		global $USER;
		static::validate_parameters ( static::dakora_get_examples_for_descriptor_for_crosssubject_parameters (), array (
				'courseid' => $courseid,
				'descriptorid' => $descriptorid,
				'userid' => $userid,
				'forall' => $forall,
				'crosssubjid'=>$crosssubjid
		) );

		if($userid == 0 && !$forall)
			$userid = $USER->id;

		return static::dakora_get_examples_for_descriptor_common($courseid, $descriptorid, $userid, $forall, $crosssubjid);
	}

	public static function dakora_get_examples_for_descriptor_for_crosssubject_returns(){
		return new external_multiple_structure ( new external_single_structure ( array (
				'exampleid' => new external_value ( PARAM_INT, 'id of descriptor' ),
				'exampletitle' => new external_value ( PARAM_TEXT, 'title of descriptor' ),
				'examplestate' => new external_value ( PARAM_INT, 'state of example, always 0 if for all students' )
		) ) );
	}

	public static function dakora_get_examples_for_descriptor_for_crosssubject_with_grading_parameters(){
		return new external_function_parameters ( array (
				'courseid' => new external_value( PARAM_INT, 'id of course' ),
				'descriptorid' => new external_value ( PARAM_INT, 'id of parent descriptor' ),
				'userid' => new external_value (PARAM_INT, 'id of user, if 0 current user'),
				'forall' => new external_value (PARAM_BOOL, 'if all users = true, only one user = false'),
				'crosssubjid' => new external_value (PARAM_INT, 'id of crosssubject')
		) );
	}

	public static function dakora_get_examples_for_descriptor_for_crosssubject_with_grading($courseid, $descriptorid, $userid, $forall, $crosssubjid){
		global $USER;
		static::validate_parameters ( static::dakora_get_examples_for_descriptor_for_crosssubject_with_grading_parameters (), array (
				'courseid' => $courseid,
				'descriptorid' => $descriptorid,
				'userid' => $userid,
				'forall' => $forall,
				'crosssubjid'=>$crosssubjid
		) );

		if($userid == 0 && !$forall)
			$userid = $USER->id;

		return static::dakora_get_examples_for_descriptor_for_crosssubject($courseid, $descriptorid, $userid, $forall, $crosssubjid);
	}

	public static function dakora_get_examples_for_descriptor_for_crosssubject_with_grading_returns(){
		return new external_multiple_structure ( new external_single_structure ( array (
				'exampleid' => new external_value ( PARAM_INT, 'id of descriptor' ),
				'exampletitle' => new external_value ( PARAM_TEXT, 'title of descriptor' ),
				'examplestate' => new external_value ( PARAM_INT, 'state of example, always 0 if for all students' ),
				'teacherevaluation' => new external_value ( PARAM_INT, 'example evaluation of teacher'),
				'studentevaluation' => new external_value ( PARAM_INT, 'example evaluation of student'),
				'teacheritemvalue' => new external_value ( PARAM_INT, 'item evaluation of teacher')
		) ) );
	}


	public static function dakora_get_example_overview_parameters(){
		return new external_function_parameters ( array (
				'exampleid' => new external_value( PARAM_INT, 'id of example' )
		) );
	}

	public static function dakora_get_example_overview($exampleid){
		return static::get_example_by_id ( $exampleid );
	}

	public static function dakora_get_example_overview_returns(){
		return new external_single_structure ( array (
				'title' => new external_value ( PARAM_TEXT, 'title of example' ),
				'description' => new external_value ( PARAM_TEXT, 'description of example' ),
				'task' => new external_value ( PARAM_TEXT, 'task(url/description) of example' ),
				'solution' => new external_value ( PARAM_TEXT, 'task(url/description) of example' ),
				'externalurl' => new external_value ( PARAM_TEXT, 'externalurl of example' ),
				'timeframe' => new external_value ( PARAM_INT, 'timeframe in minutes' ),
				'hassubmissions' => new external_value ( PARAM_BOOL, 'true if example has already submissions' )
		) );
	}

	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function dakora_add_example_to_learning_calendar_parameters() {
		return new external_function_parameters ( array (
				'courseid' => new external_value ( PARAM_INT, 'id of course'),
				'exampleid' => new external_value ( PARAM_INT, 'id of example' ),
				'creatorid' => new external_value ( PARAM_INT, 'id of creator'),
				'userid' => new external_value ( PARAM_INT, 'id of user, if 0 current user'),
				'forall' => new external_value (PARAM_BOOL, 'for all users = true, for one user = false')
		) );
	}

	/**
	 * get courses
	 *
	 * @return array of user courses
	 */
	public static function dakora_add_example_to_learning_calendar($courseid, $exampleid, $creatorid, $userid, $forall) {
		global $DB, $USER;
		static::validate_parameters ( static::dakora_add_example_to_learning_calendar_parameters (), array (
				'courseid' => $courseid,
				'exampleid' => $exampleid,
				'creatorid' => $creatorid,
				'userid' => $userid,
				'forall' => $forall
		) );

		if($creatorid == 0)
			$creatorid = $USER->id;

		if($userid == 0 && !$forall)
			$userid = $USER->id;

		static::require_can_access_course_user($courseid, $creatorid);
		static::require_can_access_course_user($courseid, $userid);
		static::require_can_access_example($exampleid, $courseid);

		$example = $DB->get_record(\block_exacomp\DB_EXAMPLES, array('id'=>$exampleid));

		if($forall){
			$students = block_exacomp_get_students_by_course($courseid);

			foreach($students as $student){
				if(block_exacomp_is_example_visible($courseid, $example, $student->id))
					block_exacomp_add_example_to_schedule($student->id,$exampleid,$creatorid,$courseid);
			}
		}else{
			if(block_exacomp_is_example_visible($courseid, $example, $userid))
				block_exacomp_add_example_to_schedule($userid,$exampleid,$creatorid,$courseid);
		}

		return array (
				"success" => true
		);
	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_multiple_structure
	 */
	public static function dakora_add_example_to_learning_calendar_returns() {
		return new external_single_structure ( array (
				'success' => new external_value ( PARAM_BOOL, 'status of success, either true (1) or false (0)' )
		) );
	}

	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function dakora_get_descriptors_for_example_parameters() {
		return new external_function_parameters ( array (
				'exampleid' => new external_value ( PARAM_INT, 'id of example' ),
				'courseid' => new external_value ( PARAM_INT, 'id of course' ),
				'userid' => new external_value ( PARAM_INT, 'id of user' ),
				'forall' => new external_value ( PARAM_BOOL, 'for all users = true, for one user = false' )
		) );
	}

	/**
	 * Get descriptors for example
	 *
	 * @param
	 *			int exampleid
	 * @return list of descriptors
	 */
	public static function dakora_get_descriptors_for_example($exampleid, $courseid, $userid, $forall) {
		global $DB, $USER;

		static::validate_parameters ( static::dakora_get_descriptors_for_example_parameters (), array (
				'exampleid' => $exampleid,
				'courseid' => $courseid,
				'userid' => $userid,
				'forall' => $forall
		) );

		if($userid == 0 && !$forall)
			$userid = $USER->id;

		static::require_can_access_course_user($courseid, $userid);

		$non_visibilities = $DB->get_fieldset_select(\block_exacomp\DB_DESCVISIBILITY,'descrid', 'courseid=? AND studentid=? AND visible=0', array($courseid, 0));

		if(!$forall)
			$non_visibilities_student = $DB->get_fieldset_select(\block_exacomp\DB_DESCVISIBILITY,'descrid', 'courseid=? AND studentid=? AND visible=0', array($courseid, $userid));

		$descriptors = static::get_descriptors_for_example( $exampleid, $courseid, $userid);

		$final_descriptors = array();
		foreach($descriptors as $descriptor)
			if(!in_array($descriptor->descriptorid, $non_visibilities) && ((!$forall && !in_array($descriptor->descriptorid, $non_visibilities_student))||$forall))
				$final_descriptors[] = $descriptor;

		return $final_descriptors;
	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_multiple_structure
	 */
	public static function dakora_get_descriptors_for_example_returns() {
		return new external_multiple_structure ( new external_single_structure ( array (
				'descriptorid' => new external_value ( PARAM_INT, 'id of descriptor' ),
				'title' => new external_value ( PARAM_TEXT, 'title of descriptor' ),
				'evaluation' => new external_value ( PARAM_INT, 'evaluation of descriptor' )
		) ) );
	}

	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function dakora_get_example_grading_parameters() {
		return new external_function_parameters ( array (
				'exampleid' => new external_value ( PARAM_INT, 'id of example' ),
				'courseid' => new external_value ( PARAM_INT, 'id of course' ),
				'userid' => new external_value ( PARAM_INT, 'id of user, if 0 current user' )
		) );
	}

	/**
	 * Get example grading for user
	 *
	 * @param
	 *			int exampleid
	 *			int courseid
	 *			int userid
	 * @return list of descriptors
	 */
	public static function dakora_get_example_grading($exampleid, $courseid, $userid) {
		global $DB,$USER;

		static::validate_parameters ( static::dakora_get_example_grading_parameters (), array (
				'exampleid' => $exampleid,
				'courseid' => $courseid,
				'userid' => $userid
		) );

		if ($userid == 0)
			$userid = $USER->id;

		static::require_can_access_course_user($courseid, $userid);
		static::require_can_access_example($exampleid, $courseid);

		$student = $DB->get_record ( 'user', array (
				'id' => $userid
		) );

		$student->examples = block_exacomp_get_user_examples_by_course($student, $courseid);

		$teacherevaluation = -1;
		if(isset($student->examples->teacher[$exampleid])){
			$teacherevaluation = $student->examples->teacher[$exampleid];
		}

		$studentevaluation = -1;
		if(isset($student->examples->student[$exampleid])){
			$studentevaluation = $student->examples->student[$exampleid];
		}

		return array (
				'teacherevaluation' => $teacherevaluation,
				'studentevaluation' => $studentevaluation
		);
	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_multiple_structure
	 */
	public static function dakora_get_example_grading_returns() {
		return new external_single_structure ( array (
				'teacherevaluation' => new external_value ( PARAM_INT, 'teacher evaluation for student and example' ),
				'studentevaluation' => new external_value ( PARAM_INT, 'self evaluation for example' )
		) );
	}

	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function dakora_get_user_role_parameters() {
		return new external_function_parameters ( array (
		) );
	}

	/**
	 * return 1 for trainer
	 * 2 for student
	 * 0 if false
	 *
	 * @return array role
	 */
	public static function dakora_get_user_role() {
		global $USER;

		static::validate_parameters ( static::dakora_get_user_role_parameters (), array (
			) );

		$courses = static::get_courses($USER->id);

		foreach($courses as $course){
			$context = context_course::instance($course["courseid"]);

			$isTeacher = block_exacomp_is_teacher($context);

			if ($isTeacher) {
				return (object)["role" => \block_exacomp\WS_ROLE_TEACHER];
			}
		}

		return (object)["role" => \block_exacomp\WS_ROLE_STUDENT];
	}

	/**
	 * Returns description of method return values
	 *
	 * @return external_multiple_structure
	 */
	public static function dakora_get_user_role_returns() {
		return new external_function_parameters ( array (
				'role' => new external_value ( PARAM_INT, '1=trainer, 2=student' )
		) );
	}

		/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function dakora_get_students_for_course_parameters() {
		return new external_function_parameters ( array (
				'courseid' => new external_value ( PARAM_INT, 'id of course' )
		) );
	}

	public static function dakora_get_students_for_course($courseid) {
		global $PAGE;
		static::validate_parameters ( static::dakora_get_students_for_course_parameters (), array (
				'courseid'=>$courseid
			) );

		static::require_can_access_course($courseid);
		// TODO: only for teacher? fjungwirth: not necessary, students can also see other course participants in Moodle

		$students = block_exacomp_get_students_by_course($courseid);

		foreach($students as $student){
			$student->studentid = $student->id;
			$student->imagealt = '';
			$picture = new user_picture($student);
			$picture->size = 50;
			$student->profilepicture = $picture->get_url($PAGE)->out();
		}
		return $students;
	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_multiple_structure
	 */
	public static function dakora_get_students_for_course_returns() {
		return new external_multiple_structure ( new external_single_structure ( array (
				'studentid' => new external_value ( PARAM_INT, 'id of student' ),
				'firstname' => new external_value ( PARAM_TEXT, 'firstname of student' ),
				'lastname' => new external_value ( PARAM_TEXT, 'lastname of student' ),
				'profilepicture' => new external_value( PARAM_TEXT, 'link to  profile picture')
		) ) );
	}

	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function dakora_get_examples_pool_parameters() {
		return new external_function_parameters ( array (
				'courseid' => new external_value ( PARAM_INT, 'id of course'),
				'userid' => new external_value ( PARAM_INT, 'id of user, if 0 current user' )
		) );
	}

	/**
	 * Get examples for pool
	 *
	 * @param
	 * 			int courseid
	 *			int userid
	 * @return list of descriptors
	 */
	public static function dakora_get_examples_pool($courseid, $userid) {
		global $USER, $DB;

		static::validate_parameters ( static::dakora_get_examples_pool_parameters (), array (
				'courseid'=>$courseid,
				'userid'=>$userid
			) );

		if($userid == 0)
			$userid = $USER->id;

		static::require_can_access_course_user($courseid, $userid);

		$examples = block_exacomp_get_examples_for_pool($userid, $courseid);

		foreach($examples as $example){
			$example->state = block_exacomp_get_dakora_state_for_example($example->courseid, $example->exampleid, $userid);

			$example_course = $DB->get_record('course', array('id'=>$example->courseid));
			$example->courseshortname = $example_course->shortname;
			$example->coursefullname = $example_course->fullname;
		}

		return $examples;
	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_multiple_structure
	 */
	public static function dakora_get_examples_pool_returns() {
		return new external_multiple_structure ( new external_single_structure ( array (
				'exampleid' => new external_value ( PARAM_INT, 'id of example' ),
				'title' => new external_value ( PARAM_TEXT, 'title of example' ),
				'student_evaluation' => new external_value ( PARAM_INT, 'self evaluation of student' ),
				'teacher_evaluation' => new external_value( PARAM_INT, 'evaluation of teacher'),
				'courseid' => new external_value(PARAM_INT, 'example course'),
				'state' => new external_value (PARAM_INT, 'state of example'),
				'scheduleid' => new external_value (PARAM_INT, 'id in schedule context'),
				'courseshortname' => new external_value (PARAM_TEXT, 'shortname of example course'),
				'coursefullname' => new external_value (PARAM_TEXT, 'full name of example course')
		) ) );
	}

	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function dakora_get_examples_trash_parameters() {
		return new external_function_parameters ( array (
				'courseid' => new external_value ( PARAM_INT, 'id of course'),
				'userid' => new external_value ( PARAM_INT, 'id of user, if 0 current user' )
		) );
	}

	/**
	 * Get examples for trash
	 *
	 * @param
	 * 			int courseid
	 *			int userid
	 * @return list of descriptors
	 */
	public static function dakora_get_examples_trash($courseid, $userid) {
		global $USER, $DB;

		static::validate_parameters ( static::dakora_get_examples_trash_parameters (), array (
				'courseid'=>$courseid,
				'userid'=>$userid
			) );

		if($userid == 0)
			$userid = $USER->id;

		static::require_can_access_course_user($courseid, $userid);

		$examples = block_exacomp_get_examples_for_trash($userid, $courseid);

		foreach($examples as $example){
			$example->state = block_exacomp_get_dakora_state_for_example($example->courseid, $example->exampleid, $userid);

			$example_course = $DB->get_record('course', array('id'=>$example->courseid));
			$example->courseshortname = $example_course->shortname;
			$example->coursefullname = $example_course->fullname;
		}

		return $examples;
	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_multiple_structure
	 */
	public static function dakora_get_examples_trash_returns() {
		return new external_multiple_structure ( new external_single_structure ( array (
				'exampleid' => new external_value ( PARAM_INT, 'id of example' ),
				'title' => new external_value ( PARAM_TEXT, 'title of example' ),
				'student_evaluation' => new external_value ( PARAM_INT, 'self evaluation of student' ),
				'teacher_evaluation' => new external_value( PARAM_INT, 'evaluation of teacher'),
				'courseid' => new external_value(PARAM_INT, 'example course'),
				'state' => new external_value (PARAM_INT, 'state of example'),
				'scheduleid' => new external_value (PARAM_INT, 'id in schedule context'),
				'courseshortname' => new external_value (PARAM_TEXT, 'shortname of example course'),
				'coursefullname' => new external_value (PARAM_TEXT, 'full name of example course')
		) ) );
	}

	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function dakora_set_example_time_slot_parameters() {
		return new external_function_parameters ( array (
				'scheduleid' => new external_value (PARAM_INT, 'id in schedule context'),
				'start' => new external_value(PARAM_INT, 'start timestamp'),
				'end'=> new external_value(PARAM_INT, 'end timestamp'),
				'deleted' => new external_value(PARAM_INT, 'delete item')
		) );
	}

	/**
	 * set example time slot
	 *
	 * @param
	 *			int courseid
	 * 			int exampleid
	 *			int userid
	 *			int start
	 *			int end
	 * @return list of descriptors
	 */
	public static function dakora_set_example_time_slot($scheduleid, $start, $end, $deleted) {
		static::validate_parameters ( static::dakora_set_example_time_slot_parameters (), array (
				'scheduleid' => $scheduleid,
				'start'=>$start,
				'end'=>$end,
				'deleted'=>$deleted
			) );

		// TODO: check example

		block_exacomp_set_example_start_end($scheduleid, $start, $end, $deleted);

		return array (
				"success" => true
		);

	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_multiple_structure
	 */
	public static function dakora_set_example_time_slot_returns() {
		return new external_single_structure ( array (
				'success' => new external_value ( PARAM_BOOL, 'status of success, either true (1) or false (0)' )
		) );
	}


	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function dakora_remove_example_from_schedule_parameters() {
		return new external_function_parameters ( array (
				'scheduleid' => new external_value(PARAM_INT, 'id of schedule entry')
		) );
	}

	/**
	 * remove example from time slot
	 *
	 * @param
	 *
	 * @return list of descriptors
	 */
	public static function dakora_remove_example_from_schedule($scheduleid) {

		static::validate_parameters ( static::dakora_remove_example_from_schedule_parameters (), array (
				'scheduleid' => $scheduleid
			) );

		// fjungwirth: permissions are checked in lib.php
		block_exacomp_remove_example_from_schedule($scheduleid);

		return array (
				"success" => true
		);

	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_multiple_structure
	 */
	public static function dakora_remove_example_from_schedule_returns() {
		return new external_single_structure ( array (
				'success' => new external_value ( PARAM_BOOL, 'status of success, either true (1) or false (0)' )
		) );
	}

	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function dakora_get_examples_for_time_slot_parameters() {
		return new external_function_parameters ( array (
				'userid' => new external_value ( PARAM_INT, 'id of user, if 0 current user' ),
				'start' => new external_value(PARAM_INT, 'start timestamp'),
				'end' => new external_value(PARAM_INT, 'end timestamp')
		) );
	}

	/**
	 * Get examples for time slot
	 *
	 * @param
	 *			int userid
	 *			int start
	 *			int end
	 * @return list of descriptors
	 */
	public static function dakora_get_examples_for_time_slot($userid, $start, $end) {
		global $USER, $DB;
		static::validate_parameters ( static::dakora_get_examples_for_time_slot_parameters (), array (
				'userid'=>$userid,
				'start'=>$start,
				'end'=>$end
			) );

		if($userid == 0)
			$userid = $USER->id;

		static::require_can_access_user($userid);

		$examples = block_exacomp_get_examples_for_start_end_all_courses($userid, $start, $end);

		foreach($examples as $example){
			$example->state = block_exacomp_get_dakora_state_for_example($example->courseid, $example->exampleid, $userid);

			$example_course = $DB->get_record('course', array('id'=>$example->courseid));
			$example->courseshortname = $example_course->shortname;
			$example->coursefullname = $example_course->fullname;
			if(!isset($example->additionalinfo))
				$example->additionalinfo = -1;
		}

		return $examples;
	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_multiple_structure
	 */
	public static function dakora_get_examples_for_time_slot_returns() {
		return new external_multiple_structure ( new external_single_structure ( array (
				'exampleid' => new external_value ( PARAM_INT, 'id of example' ),
				'title' => new external_value ( PARAM_TEXT, 'title of example' ),
				'start' => new external_value (PARAM_INT, 'start of event'),
				'end' => new external_value (PARAM_INT, 'end of event'),
				'student_evaluation' => new external_value ( PARAM_INT, 'self evaluation of student' ),
				'teacher_evaluation' => new external_value( PARAM_INT, 'evaluation of teacher'),
				'additionalinfo' => new external_value( PARAM_INT, 'additional evaluation of teacher'),
				'courseid' => new external_value(PARAM_INT, 'example course'),
				'state' => new external_value (PARAM_INT, 'state of example'),
				'scheduleid' => new external_value (PARAM_INT, 'id in schedule context'),
				'courseshortname' => new external_value (PARAM_TEXT, 'shortname of example course'),
				'coursefullname' => new external_value (PARAM_TEXT, 'full name of example course')
		) ) );
	}

	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function dakora_get_cross_subjects_by_course_parameters() {
		return new external_function_parameters ( array (
				'courseid' => new external_value ( PARAM_INT, 'id of course' ),
				'userid' => new external_value(PARAM_INT, 'id of user, if 0 current user'),
				'forall' => new external_value(PARAM_BOOL, 'for all users = true, for one user = false')
		) );
	}

	/**
	 * Get cross subjects
	 *
	 * @param
	 *			int courseid
	 *			int userid
	 * @return array list of descriptors
	 */
	public static function dakora_get_cross_subjects_by_course($courseid, $userid, $forall) {
		global $USER, $DB;
		static::validate_parameters ( static::dakora_get_cross_subjects_by_course_parameters (), array (
				'courseid'=>$courseid,
				'userid'=>$userid,
				'forall'=>$forall
			) );

		if($userid == 0 && !$forall)
			$userid = $USER->id;

		if ($forall) {
			static::require_can_access_course($courseid);
		} else {
			static::require_can_access_course_user($courseid, $userid);
		}

		$cross_subjects = block_exacomp_get_cross_subjects_by_course($courseid, $userid);

		//if for all return only common cross subjects
		if($forall){
			$cross_subjects_return = array();
			foreach($cross_subjects as $cross_subject){
				if($cross_subject->shared == 1)
					$cross_subjects_return[] = $cross_subject;
				else{
					$shared_for_all = true;
					$cross_sub_students = $DB->get_fieldset_select(\block_exacomp\DB_CROSSSTUD,'studentid', 'crosssubjid=?', array($cross_subject->id));
					$students = block_exacomp_get_students_by_course($courseid);
					foreach($students as $student)
						if(!in_array($student->id, $cross_sub_students))
							$shared_for_all = false;

					if($shared_for_all)
						$cross_subjects_return[] = $cross_subject;
				}
			}

			return $cross_subjects_return;
		}

		return $cross_subjects;
	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_multiple_structure
	 */
	public static function dakora_get_cross_subjects_by_course_returns() {
		return new external_multiple_structure ( new external_single_structure ( array (
				'id' => new external_value ( PARAM_INT, 'id of cross subject' ),
				'title' => new external_value ( PARAM_TEXT, 'title of cross subject' ),
				'description' => new external_value ( PARAM_TEXT, 'description of cross subject'),
				'subjectid' => new external_value (PARAM_INT, 'subject id, cross subject is associated with')
		) ) );
	}

	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function dakora_get_descriptors_by_cross_subject_parameters() {
		return new external_function_parameters ( array (
				'courseid' => new external_value (PARAM_INT, 'id of course'),
				'crosssubjid' => new external_value ( PARAM_INT, 'id of cross subject' ),
				'userid' => new external_value(PARAM_INT, 'id of user, if 0 current user'),
				'forall' => new external_value(PARAM_BOOL, 'for all users = true, for one user = false')
		) );
	}

	/**
	 * Get cross subjects
	 *
	 * @param
	 *			int courseid
	 *			int crosssubjid
	 *			int userid
	 *			boolean forall
	 * @return list of descriptors
	 */
	public static function dakora_get_descriptors_by_cross_subject($courseid, $crosssubjid, $userid, $forall) {
		global $USER;
		static::validate_parameters ( static::dakora_get_descriptors_by_cross_subject_parameters (), array (
				'courseid' => $courseid,
				'crosssubjid'=>$crosssubjid,
				'userid'=>$userid,
				'forall' => $forall
			) );

		if($userid == 0 && $forall == false)
			$userid = $USER->id;

		static::require_can_access_course_user($courseid, $userid);

		return static::dakora_get_descriptors_by_cross_subject_common($courseid, $crosssubjid, $userid, $forall, true);
	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_multiple_structure
	 */
	public static function dakora_get_descriptors_by_cross_subject_returns() {
		return new external_multiple_structure ( new external_single_structure ( array (
				'descriptorid' => new external_value ( PARAM_INT, 'id of descriptor' ),
				'descriptortitle' => new external_value ( PARAM_TEXT, 'title of descriptor' ),
				'numbering' => new external_value ( PARAM_TEXT, 'numbering for descriptor'),
				'niveautitle' => new external_value ( PARAM_TEXT, 'title of nivaue'),
				'niveauid' => new external_value ( PARAM_INT, 'id of niveau')
		) ) );
	}

	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function dakora_get_all_descriptors_by_cross_subject_parameters() {
		return new external_function_parameters ( array (
				'courseid' => new external_value (PARAM_INT, 'id of course'),
				'crosssubjid' => new external_value ( PARAM_INT, 'id of cross subject' ),
				'userid' => new external_value(PARAM_INT, 'id of user, if 0 current user'),
				'forall' => new external_value(PARAM_BOOL, 'for all users = true, for one user = false')
		) );
	}

	/**
	 * Get cross subjects
	 *
	 * @param
	 *			int courseid
	 *			int crosssubjid
	 *			int userid
	 *			boolean forall
	 * @return list of descriptors
	 */
	public static function dakora_get_all_descriptors_by_cross_subject($courseid, $crosssubjid, $userid, $forall) {
		global $USER;
		static::validate_parameters ( static::dakora_get_all_descriptors_by_cross_subject_parameters (), array (
				'courseid' => $courseid,
				'crosssubjid'=>$crosssubjid,
				'userid'=>$userid,
				'forall' => $forall
			) );

		if($userid == 0 && $forall == false)
			$userid = $USER->id;

		static::require_can_access_course_user($courseid, $userid);

		return static::dakora_get_descriptors_by_cross_subject_common($courseid, $crosssubjid, $userid, $forall, false);
	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_multiple_structure
	 */
	public static function dakora_get_all_descriptors_by_cross_subject_returns() {
		return new external_multiple_structure ( new external_single_structure ( array (
				'descriptorid' => new external_value ( PARAM_INT, 'id of descriptor' ),
				'descriptortitle' => new external_value ( PARAM_TEXT, 'title of descriptor' ),
				'numbering' => new external_value ( PARAM_TEXT, 'numbering for descriptor'),
				'niveautitle' => new external_value ( PARAM_TEXT, 'title of nivaue'),
				'niveauid' => new external_value ( PARAM_INT, 'id of niveau')
		) ) );
	}

	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function dakora_get_descriptor_children_for_cross_subject_parameters() {
		return new external_function_parameters ( array (
				'courseid' => new external_value( PARAM_INT, 'id of course' ),
				'descriptorid' => new external_value ( PARAM_INT, 'id of parent descriptor' ),
				'userid' => new external_value ( PARAM_INT, 'id of user, 0 for current user'),
				'forall' => new external_value (PARAM_BOOL, 'for all users = true, for one user = false'),
				'crosssubjid' => new external_value (PARAM_INT, 'id of cross subject')
		) );
	}

	/**
	 * get children for descriptor in cross subject context
	 *
	 * @return array of user courses
	 */
	public static function dakora_get_descriptor_children_for_cross_subject($courseid, $descriptorid, $userid, $forall, $crosssubjid) {
		global $DB, $USER;
		static::validate_parameters ( static::dakora_get_descriptor_children_for_cross_subject_parameters (), array (
				'courseid' => $courseid,
				'descriptorid' => $descriptorid,
				'userid' => $userid,
				'forall' => $forall,
				'crosssubjid' => $crosssubjid
		) );

		if($userid == 0 && !$forall)
			$userid = $USER->id;

		static::require_can_access_course_user($courseid, $userid);

		return static::get_descriptor_children($courseid, $descriptorid, $userid, $forall, $crosssubjid);

	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_multiple_structure
	 */
	public static function dakora_get_descriptor_children_for_cross_subject_returns() {
		return new external_single_structure ( array (
			'children' => new external_multiple_structure ( new external_single_structure ( array (
					'childid' => new external_value ( PARAM_INT, 'id of child' ),
					'childtitle' => new external_value ( PARAM_TEXT, 'title of child' ),
					'numbering' => new external_value ( PARAM_TEXT, 'numbering for child'),
					'teacherevaluation' => new external_value ( PARAM_INT, 'grading of children'),
					'studentevaluation' => new external_value ( PARAM_INT, 'self evaluation of children'),
					'examplestotal' => new external_value (PARAM_INT, 'total number of material'),
					'examplesvisible' => new external_value (PARAM_INT, 'visible number of material'),
					'examplesinwork' => new external_value (PARAM_FLOAT, 'edited number of material')
			) ) ) ,
			'examples' => new external_multiple_structure ( new external_single_structure ( array (
					'exampleid' => new external_value ( PARAM_INT, 'id of example' ),
					'exampletitle' => new external_value ( PARAM_TEXT, 'title of example' ),
					'examplestate' => new external_value ( PARAM_INT, 'state of example, always 0 if for all students' )
			) ) ),
			'examplestotal' => new external_value (PARAM_INT, 'number of total examples'),
			'examplesvisible' => new external_value (PARAM_INT, 'number of visible examples'),
			'examplesinwork' => new external_value (PARAM_FLOAT, 'number of examples in work')
		) ) ;
	}
	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function dakora_get_all_descriptor_children_for_cross_subject_parameters() {
		return new external_function_parameters ( array (
				'courseid' => new external_value( PARAM_INT, 'id of course' ),
				'descriptorid' => new external_value ( PARAM_INT, 'id of parent descriptor' ),
				'userid' => new external_value ( PARAM_INT, 'id of user, 0 for current user'),
				'forall' => new external_value (PARAM_BOOL, 'for all users = true, for one user = false'),
				'crosssubjid' => new external_value (PARAM_INT, 'id of cross subject')
		) );
	}

	/**
	 * get children for descriptor in cross subject context
	 *
	 * @return array of user courses
	 */
	public static function dakora_get_all_descriptor_children_for_cross_subject($courseid, $descriptorid, $userid, $forall, $crosssubjid) {
		global $USER;
		static::validate_parameters ( static::dakora_get_all_descriptor_children_for_cross_subject_parameters (), array (
				'courseid' => $courseid,
				'descriptorid' => $descriptorid,
				'userid' => $userid,
				'forall' => $forall,
				'crosssubjid' => $crosssubjid
		) );

		if($userid == 0 && !$forall)
			$userid = $USER->id;

		static::require_can_access_course_user($courseid, $userid);
		return static::get_descriptor_children($courseid, $descriptorid, $userid, $forall, $crosssubjid, true);

	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_multiple_structure
	 */
	public static function dakora_get_all_descriptor_children_for_cross_subject_returns() {
		return new external_single_structure ( array (
			'children' => new external_multiple_structure ( new external_single_structure ( array (
					'childid' => new external_value ( PARAM_INT, 'id of child' ),
					'childtitle' => new external_value ( PARAM_TEXT, 'title of child' ),
					'numbering' => new external_value ( PARAM_TEXT, 'numbering for child'),
					'teacherevaluation' => new external_value ( PARAM_INT, 'grading of children'),
					'studentevaluation' => new external_value ( PARAM_INT, 'self evaluation of children'),
					'hasmaterial' => new external_value ( PARAM_BOOL, 'true or false if child has materials'),
					'examplestotal' => new external_value (PARAM_INT, 'total number of material'),
					'examplesvisible' => new external_value (PARAM_INT, 'visible number of material'),
					'examplesinwork' => new external_value (PARAM_FLOAT, 'edited number of material')
			) ) ) ,
			'examples' => new external_multiple_structure ( new external_single_structure ( array (
					'exampleid' => new external_value ( PARAM_INT, 'id of example' ),
					'exampletitle' => new external_value ( PARAM_TEXT, 'title of example' ),
					'examplestate' => new external_value ( PARAM_INT, 'state of example, always 0 if for all students' )
			) ) ),
			'examplestotal' => new external_value (PARAM_INT, 'number of total examples'),
			'examplesvisible' => new external_value (PARAM_INT, 'number of visible examples'),
			'examplesinwork' => new external_value (PARAM_FLOAT, 'number of examples in work')
		) ) ;
	}

	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function dakora_get_schedule_config_parameters() {
		return new external_function_parameters ( array (
		) );
	}

	/**
	 * get children for descriptor in cross subject context
	 *
	 * @return array of user courses
	 */
	public static function dakora_get_schedule_config() {
		$units = (get_config("exacomp","scheduleunits")) ? get_config("exacomp","scheduleunits") : 8;
		$interval = (get_config("exacomp","scheduleinterval")) ? get_config("exacomp","scheduleinterval") : 50;
		$time =  (get_config("exacomp","schedulebegin")) ? get_config("exacomp","schedulebegin") : "07:45";

		return array("units" => $units, "interval" => $interval, "begin" => $time);
	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_multiple_structure
	 */
	public static function dakora_get_schedule_config_returns() {
		return new external_single_structure ( array (
						'units' => new external_value ( PARAM_INT, 'number of units per day' ),
						'interval' => new external_value ( PARAM_TEXT, 'duration of unit in minutes' ),
						'begin' => new external_value ( PARAM_TEXT, 'begin time for the first unit, format hh:mm')
				));
	}

	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 *
	 */
	public static function dakora_get_pre_planning_storage_examples_parameters() {
		return new external_function_parameters ( array (
				'courseid' => new external_value ( PARAM_INT, 'id of course' )
		) );
	}

	/**
	 * get pre planning storage examples for current teacher
	 *
	 * @param
	 *			int courseid
	 * @return examples
	 */
	public static function dakora_get_pre_planning_storage_examples($courseid) {
		global $USER;
		static::validate_parameters ( static::dakora_get_pre_planning_storage_examples_parameters (), array (
				'courseid'=>$courseid
		) );

		$creatorid = $USER->id;
		static::require_can_access_course($courseid);

		$examples = block_exacomp_get_pre_planning_storage($creatorid, $courseid);

		foreach($examples as $example){
			$example->state = block_exacomp_get_dakora_state_for_example($example->courseid, $example->exampleid, $creatorid);
		}

		return $examples;
	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_multiple_structure
	 */
	public static function dakora_get_pre_planning_storage_examples_returns() {
		return new external_multiple_structure ( new external_single_structure ( array (
				'exampleid' => new external_value ( PARAM_INT, 'id of example' ),
				'title' => new external_value ( PARAM_TEXT, 'title of example' ),
				'courseid' => new external_value(PARAM_INT, 'example course'),
				'state' => new external_value (PARAM_INT, 'state of example'),
				'scheduleid' => new external_value (PARAM_INT, 'id in schedule context')
		) ) );
	}

	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 *
	 */
	public static function dakora_get_pre_planning_storage_students_parameters() {
		return new external_function_parameters ( array (
				'courseid' => new external_value ( PARAM_INT, 'id of course' )
		) );
	}

	/**
	 * get pre planning storage students for current teacher
	 *
	 * @param
	 *			int courseid
	 * @return examples
	 */
	public static function dakora_get_pre_planning_storage_students($courseid) {
		global $USER;
		static::validate_parameters ( static::dakora_get_pre_planning_storage_students_parameters (), array (
				'courseid'=>$courseid
		) );

		block_exacomp_require_teacher($courseid );

		$creatorid = $USER->id;

		$examples = array();
		$schedules = block_exacomp_get_pre_planning_storage($creatorid, $courseid);
		foreach($schedules as $schedule){
			if(!in_array($schedule->exampleid, $examples))
				$examples[] = $schedule->exampleid;
		}

		$students = block_exacomp_get_students_by_course($courseid);
		$students = block_exacomp_get_student_pool_examples($students, $courseid);

		foreach($students as $student){
			$student_has_examples = false;
			foreach($student->pool_examples as $example){
				if(in_array($example->exampleid, $examples))
					$student_has_examples = true;
			}
			$student->studentid = $student->id;
			$student->has_examples = $student_has_examples;
		}

		return $students;
	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_multiple_structure
	 */
	public static function dakora_get_pre_planning_storage_students_returns() {
		return new external_multiple_structure ( new external_single_structure ( array (
				'studentid' => new external_value ( PARAM_INT, 'id of student' ),
				'firstname' => new external_value ( PARAM_TEXT, 'firstname of student' ),
				'lastname' => new external_value ( PARAM_TEXT, 'lastname of student' ),
				'has_examples' => new external_value( PARAM_BOOL, 'already has examples from current pre planning storage')
		) ) );
	}

	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 *
	 */
	public static function dakora_has_items_in_pre_planning_storage_parameters() {
		return new external_function_parameters ( array (
				'courseid' => new external_value ( PARAM_INT, 'id of course' )
		) );
	}

	/**
	 * get pre planning storage students for current teacher
	 *
	 * @param
	 *			int courseid
	 * @return examples
	 */
	public static function dakora_has_items_in_pre_planning_storage($courseid) {
		global $USER;
		static::validate_parameters ( static::dakora_has_items_in_pre_planning_storage_parameters (), array (
				'courseid'=>$courseid
		) );

		$creatorid = $USER->id;

		static::require_can_access_course($courseid);

		$items = false;
		if(block_exacomp_has_items_pre_planning_storage($creatorid, $courseid))
			$items = true;

		return array (
				"success" => $items
		);
	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_multiple_structure
	 */
	public static function dakora_has_items_in_pre_planning_storage_returns() {
		return new external_single_structure ( array (
				'success' => new external_value ( PARAM_BOOL, 'status of success, either true (1) or false (0)' )
		) );
	}

	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 *
	 */
	public static function dakora_empty_pre_planning_storage_parameters() {
		return new external_function_parameters ( array (
				'courseid' => new external_value ( PARAM_INT, 'id of course' )
		) );
	}

	/**
	 * empty pre planning storage for current teacher
	 *
	 * @param
	 *			int courseid
	 * @return examples
	 */
	public static function dakora_empty_pre_planning_storage($courseid) {
		global $USER;
		static::validate_parameters ( static::dakora_empty_pre_planning_storage_parameters (), array (
				'courseid'=>$courseid
		) );

		$creatorid = $USER->id;
		static::require_can_access_course($courseid);

		block_exacomp_empty_pre_planning_storage($creatorid, $courseid);

		return array (
				"success" => true
		);
	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_multiple_structure
	 */
	public static function dakora_empty_pre_planning_storage_returns() {
		return new external_single_structure ( array (
				'success' => new external_value ( PARAM_BOOL, 'status of success, either true (1) or false (0)' )
		) );
	}

	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 *
	 */
	public static function dakora_add_example_to_pre_planning_storage_parameters() {
		return new external_function_parameters ( array (
				'courseid' => new external_value ( PARAM_INT, 'id of course' ),
				'exampleid' => new external_value (PARAM_INT, 'id of example')
		) );
	}

	/**
	 * add example to current pre planning storage
	 *
	 * @param
	 *			int courseid
	 * @return array
	 */
	public static function dakora_add_example_to_pre_planning_storage($courseid, $exampleid) {
		global $USER;
		static::validate_parameters ( static::dakora_add_example_to_pre_planning_storage_parameters (), array (
				'courseid'=>$courseid,
				'exampleid' => $exampleid
		) );

		$creatorid = $USER->id;

		static::require_can_access_course($courseid);
		static::require_can_access_example($exampleid);

		block_exacomp_add_example_to_schedule(0, $exampleid, $creatorid, $courseid);

		return array (
				"success" => true
		);
	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_multiple_structure
	 */
	public static function dakora_add_example_to_pre_planning_storage_returns() {
		return new external_single_structure ( array (
				'success' => new external_value ( PARAM_BOOL, 'status of success, either true (1) or false (0)' )
		) );
	}
	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 *
	 */
	public static function dakora_add_examples_to_students_schedule_parameters() {
		return new external_function_parameters ( array (
				'courseid' => new external_value ( PARAM_INT, 'id of course' ),
				'examples' => new external_value ( PARAM_TEXT, 'json array of examples'),
				'students' => new external_value ( PARAM_TEXT, 'json array of students')
		) );
	}

	/**
	 * add example to current pre planning storage
	 *
	 * @param
	 *			int courseid
	 * @return examples
	 */
	public static function dakora_add_examples_to_students_schedule($courseid, $examples, $students) {
		global $USER;
		static::validate_parameters ( static::dakora_add_examples_to_students_schedule_parameters (), array (
				'courseid'=>$courseid,
				'examples' => $examples,
				'students' => $students
		) );

		static::require_can_access_course_user($courseid, $USER->id);

		$creatorid = $USER->id;

		$examples = json_decode($examples);
		$students = json_decode($students);

		foreach($examples as $example){
			foreach($students as $student)
				block_exacomp_add_example_to_schedule($student, $example, $creatorid, $courseid);
		}

		return array (
				"success" => true
		);
	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_multiple_structure
	 */
	public static function dakora_add_examples_to_students_schedule_returns() {
		return new external_single_structure ( array (
				'success' => new external_value ( PARAM_BOOL, 'status of success, either true (1) or false (0)' )
		) );
	}


	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function dakora_submit_example_parameters() {
		return new external_function_parameters ( array (
				'exampleid' => new external_value ( PARAM_INT, 'exampleid' ),
				'studentvalue' => new external_value ( PARAM_INT, 'studentvalue' , VALUE_DEFAULT, -1),
				'url' => new external_value ( PARAM_URL, 'url' ),
				'filename' => new external_value ( PARAM_TEXT, 'filename, used to look up file and create a new one in the exaport file area' ),
				'studentcomment' => new external_value ( PARAM_TEXT, 'studentcomment' ),
				'itemid' => new external_value ( PARAM_INT, 'itemid (0 for insert, >0 for update)' ),
				'courseid' => new external_value ( PARAM_INT, 'courseid' )
		) );
	}

	/**
	 * Add student submission to example.
	 *
	 * @param int itemid (0 for new, >0 for existing)
	 * @return array of course subjects
	 */
	public static function dakora_submit_example($exampleid,$studentvalue = null,$url,$filename,$studentcomment,$itemid=0,$courseid=0) {
		global $CFG,$DB,$USER;

		static::validate_parameters(static::dakora_submit_example_parameters(), array('exampleid'=>$exampleid,'url'=>$url,'filename'=>$filename,'studentcomment'=>$studentcomment,'studentvalue'=>$studentvalue,'itemid'=>$itemid,'courseid'=>$courseid));

		if (!isset($type)) {
			$type = ($filename != '') ? 'file' : 'url';
		};

		static::require_can_access_course($courseid);

		//insert: if itemid == 0 OR status != 0
		$insert = true;
		if($itemid != 0) {
			$itemexample = $DB->get_record('block_exacompitemexample', array('itemid'=>$itemid));
			if($itemexample->teachervalue == null || $itemexample->status == 0)
				$insert = false;
		}
		require_once $CFG->dirroot . '/blocks/exaport/lib/lib.php';

		if($insert) {
			//store item in the right portfolio category
			$course = get_course($courseid);
			$course_category = block_exaport_get_user_category($course->fullname, $USER->id);

			if(!$course_category) {
				$course_category = block_exaport_create_user_category($course->fullname, $USER->id);
			}

			$exampletitle = $DB->get_field('block_exacompexamples', 'title', array('id'=>$exampleid));
			$subjecttitle = block_exacomp_get_subjecttitle_by_example($exampleid);
			$subject_category = block_exaport_get_user_category($subjecttitle, $USER->id);
			if(!$subject_category) {
				$subject_category = block_exaport_create_user_category($subjecttitle, $USER->id, $course_category->id);
			}

			$itemid = $DB->insert_record("block_exaportitem", array('userid'=>$USER->id,'name'=>$exampletitle,'intro' => '', 'url'=>$url, 'type'=>$type,'timemodified'=>time(),'categoryid'=>$subject_category->id,'teachervalue' => null, 'studentvalue' => null, 'courseid' => $courseid));
			//autogenerate a published view for the new item
			$dbView = new stdClass();
			$dbView->userid = $USER->id;
			$dbView->name = $exampletitle;
			$dbView->timemodified = time();
			$dbView->layout = 1;
			// generate view hash
			do {
				$hash = substr(md5(microtime()), 3, 8);
			} while ($DB->record_exists("block_exaportview", array("hash"=>$hash)));
			$dbView->hash = $hash;

			$dbView->id = $DB->insert_record('block_exaportview', $dbView);

			//share the view with teachers
			block_exaport_share_view_to_teachers($dbView->id);

			//add item to view
			$DB->insert_record('block_exaportviewblock',array('viewid'=>$dbView->id,'positionx'=>1, 'positiony'=>1, 'type'=>'item', 'itemid'=>$itemid));

		} else {
			$item = $DB->get_record('block_exaportitem',array('id'=>$itemid));
			if($url != '')
				$item->url = $url;
			$item->timemodified = time();

			if($type == 'file')
				block_exaport_file_remove($DB->get_record("block_exaportitem",array("id"=>$itemid)));

			$DB->update_record('block_exaportitem', $item);
		}

		//if a file is added we need to copy the file from the user/private filearea to block_exaport/item_file with the itemid from above
		if($type == "file") {

			$context = context_user::instance($USER->id);
			$fs = get_file_storage();
			try {
				$old = $fs->get_file($context->id, "user", "private", 0, "/", $filename);

				if($old) {
					$file_record = array('contextid'=>$context->id, 'component'=>'block_exaport', 'filearea'=>'item_file',
							'itemid'=>$itemid, 'filepath'=>'/', 'filename'=>$old->get_filename(),
							'timecreated'=>time(), 'timemodified'=>time());
					$fs->create_file_from_storedfile($file_record, $old->get_id());

					$old->delete();
				}
			} catch (Exception $e) {
				//some problem with the file occured
			}
		}

		if($insert) {
			$DB->insert_record('block_exacompitemexample',array('exampleid'=>$exampleid,'itemid'=>$itemid,'timecreated'=>time(),'status'=>0));
			if($studentcomment != '')
				$DB->insert_record('block_exaportitemcomm',array('itemid'=>$itemid,'userid'=>$USER->id,'entry'=>$studentcomment,'timemodified'=>time()));
		} else {
			$itemexample->timemodified = time();
			$itemexample->studentvalue = $studentvalue;
			$DB->update_record('block_exacompitemexample', $itemexample);

			if($studentcomment != '') {
				$DB->delete_records('block_exaportitemcomm',array('itemid'=>$itemid,'userid'=>$USER->id));
				$DB->insert_record('block_exaportitemcomm',array('itemid'=>$itemid,'userid'=>$USER->id,'entry'=>$studentcomment,'timemodified'=>time()));
			}
		}

		block_exacomp_set_user_example($USER->id, $exampleid, $courseid, \block_exacomp\ROLE_STUDENT, $studentvalue);

		block_exacomp_notify_all_teachers_about_submission($courseid, $exampleid, time());

		\block_exacomp\event\example_submitted::log(['objectid' => $exampleid, 'courseid' => $courseid]);

		return array("success"=>true,"itemid"=>$itemid);
	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_single_structure
	 */
	public static function dakora_submit_example_returns() {
		return new external_single_structure ( array (
				'success' => new external_value ( PARAM_BOOL, 'status' ),
				'itemid' => new external_value ( PARAM_INT, 'itemid' )
		) );
	}

	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function dakora_grade_example_parameters() {
		return new external_function_parameters ( array (
				'userid' => new external_value ( PARAM_INT, 'userid' ),
				'courseid' => new external_value ( PARAM_INT, 'courseid' ),
				'exampleid' => new external_value ( PARAM_INT, 'exampleid' ),
				'examplevalue' => new external_value ( PARAM_INT, 'examplevalue' ),
				'itemid' => new external_value ( PARAM_INT, 'itemid' , VALUE_DEFAULT, -1),
				'itemvalue' => new external_value ( PARAM_INT, 'itemvalue' , VALUE_DEFAULT, -1),
				'comment' => new external_value ( PARAM_TEXT, 'teachercomment' , VALUE_DEFAULT, "")
		) );
	}

	/**
	 * Add student submission to example.
	 *
	 * @param int $itemid (0 for new, >0 for existing)
	 * @return array of course subjects
	 */
	public static function dakora_grade_example($userid, $courseid, $exampleid, $examplevalue, $itemid, $itemvalue, $comment) {
		global $DB,$USER;

		static::validate_parameters(static::dakora_grade_example_parameters(), array('userid'=>$userid,'courseid'=>$courseid,'exampleid'=>$exampleid,'examplevalue'=>$examplevalue,'itemid'=>$itemid,'itemvalue'=>$itemvalue,'comment'=>$comment));

		if($userid == 0) {
			$role = \block_exacomp\ROLE_STUDENT;
			$userid = $USER->id;
		} else
			$role = \block_exacomp\ROLE_TEACHER;

		static::require_can_access_course_user($courseid, $userid);
		static::require_can_access_example($exampleid, $courseid);

		block_exacomp_set_user_example(($userid == 0) ? $USER->id : $userid, $exampleid, $courseid, $role, $examplevalue,0,0,'self',$itemvalue);

		if($itemid > 0 && $userid > 0) {

			$itemexample = $DB->get_record('block_exacompitemexample', array('exampleid' => $exampleid, 'itemid' => $itemid));
			if(!$itemexample)
				throw new invalid_parameter_exception("Wrong itemid given");

			if($itemvalue < 0 && $itemvalue > 100)
				throw new invalid_parameter_exception("Item value must be between 0 and 100");

			$itemexample->teachervalue = $itemvalue;
			$itemexample->datemodified = time();
			$itemexample->status = 1;

			$DB->update_record('block_exacompitemexample', $itemexample);

			if($comment) {
				$insert = new stdClass ();
				$insert->itemid = $itemid;
				$insert->userid = $USER->id;
				$insert->entry = $comment;
				$insert->timemodified = time ();

				$DB->delete_records ( 'block_exaportitemcomm', array (
						'itemid' => $itemid,
						'userid' => $USER->id
				) );
				$DB->insert_record ( 'block_exaportitemcomm', $insert );

				block_exacomp_send_example_comment_notification($USER, $DB->get_record('user', array('id' => $userid)), $courseid, $exampleid);

				\block_exacomp\event\example_commented::log(['objectid' => $exampleid, 'courseid' => $courseid]);
			}
		}

		return array("success"=>true,"exampleid"=>$exampleid);
	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_single_structure
	 */
	public static function dakora_grade_example_returns() {
		return new external_single_structure ( array (
				'success' => new external_value ( PARAM_BOOL, 'status' ),
				'exampleid' => new external_value ( PARAM_INT, 'exampleid' )
		) );
	}

	public static function dakora_get_descriptor_details_parameters(){
	return new external_function_parameters ( array (
				'courseid' => new external_value( PARAM_INT, 'courseid'),
				'descriptorid' => new external_value( PARAM_INT, 'descriptorid'),
				'userid' => new external_value ( PARAM_INT, 'userid' ),
				'forall' => new external_value ( PARAM_BOOL, 'forall'),
				'crosssubjid' => new external_value ( PARAM_INT, 'crosssubjid')
		) );
	}

	public static function dakora_get_descriptor_details($courseid, $descriptorid, $userid, $forall, $crosssubjid){
		global $DB, $USER;
		static::validate_parameters(static::dakora_get_descriptor_details_parameters(),
			array('courseid'=>$courseid, 'descriptorid'=>$descriptorid, 'userid'=>$userid,'forall'=>$forall, 'crosssubjid'=>$crosssubjid));

		if(!$forall && $userid == 0)
			$userid = $USER->id;

		static::require_can_access_course_user($courseid, $userid);

		$descriptor = $DB->get_record(\block_exacomp\DB_DESCRIPTORS, array('id'=>$descriptorid));
		$descriptor_topic_mm = $DB->get_record(\block_exacomp\DB_DESCTOPICS, array('descrid'=>$descriptor->id));
		$descriptor->topicid = $descriptor_topic_mm->topicid;

		$descriptor_return = new stdClass();
		$descriptor_return->descriptorid = $descriptorid;
		$descriptor_return->descriptortitle = $descriptor->title;
		$descriptor_return->teacherevaluation = -1;
		$descriptor_return->additionalinfo = null;
		if(!$forall){
			if ($grading = block_exacomp\get_comp_eval($courseid, \block_exacomp\ROLE_TEACHER, $userid, \block_exacomp\TYPE_DESCRIPTOR, $descriptorid)) {
				$descriptor_return->teacherevaluation = ($grading->value !== null) ? $grading->value : -1;
				$descriptor_return->additionalinfo = $grading->additionalinfo;
			}
		}
		$descriptor_return->studentevaluation = -1;
		if (!$forall) {
			if ($grading = block_exacomp\get_comp_eval($courseid, \block_exacomp\ROLE_STUDENT, $userid, \block_exacomp\TYPE_DESCRIPTOR, $descriptorid)) {
				$descriptor_return->studentevaluation = ($grading->value !== null) ? $grading->value : -1;
			}
		}

		$descriptor_return->numbering = block_exacomp_get_descriptor_numbering($descriptor);

		$descriptor_return->niveautitle = "";
		$descriptor_return->niveauid = 0;
		if($descriptor->niveauid){
			$niveau = $DB->get_record(\block_exacomp\DB_NIVEAUS, array('id'=>$descriptor->niveauid));
			$descriptor_return->niveautitle = $niveau->title;
			$descriptor_return->niveauid = $niveau->id;
		}

		$childsandexamples = static::get_descriptor_children($courseid, $descriptorid, $userid, $forall, $crosssubjid, true);

		$descriptor_return->children = $childsandexamples->children;

		// summary for children gradings
		$grading_scheme = block_exacomp_get_grading_scheme($courseid) + 1;
		$children_teacherevaluation = array_fill(0,$grading_scheme,0);
		$children_studentevaluation = array_fill(0,$grading_scheme,0);

		foreach($childsandexamples->children as $child) {
			if($child->teacherevaluation > -1)
				$children_teacherevaluation[$child->teacherevaluation]++;
			if($child->studentevaluation > -1)
				$children_studentevaluation[$child->studentevaluation]++;
		}
		$childrengradings = new stdClass();
		$childrengradings->teacher = array();
		$childrengradings->student = array();

		foreach($children_teacherevaluation as $key => $value) {
			$childrengradings->teacher[$key] = array('sum' => $value);
		}
		foreach($children_studentevaluation as $key => $value) {
			$childrengradings->student[$key] = array('sum' => $value);
		}
		$descriptor_return->childrengradings = $childrengradings;

		// summary for example gradings
		$descriptor_return->examples = $childsandexamples->examples;

		$examples_teacherevaluation = array_fill(0,$grading_scheme,0);
		$examples_studentevaluation = array_fill(0,$grading_scheme,0);

		foreach($childsandexamples->examples as $example) {
			if($example->teacherevaluation > -1)
				$examples_teacherevaluation[$example->teacherevaluation]++;
			if($example->studentevaluation > -1)
				$examples_studentevaluation[$example->studentevaluation]++;
		}
		$examplegradings = new stdClass();
		$examplegradings->teacher = array();
		$examplegradings->student = array();

		foreach($examples_teacherevaluation as $key => $value) {
			$examplegradings->teacher[$key] = array('sum' => $value);
		}
		foreach($examples_studentevaluation as $key => $value) {
			$examplegradings->student[$key] = array('sum' => $value);
		}
		$descriptor_return->examplegradings = $examplegradings;
		// example statistics
		$descriptor_return->examplestotal = $childsandexamples->examplestotal;
		$descriptor_return->examplesvisible = $childsandexamples->examplesvisible;
		$descriptor_return->examplesinwork = $childsandexamples->examplesinwork;
		$descriptor_return->examplesedited = $childsandexamples->examplesedited;

		$descriptor_return->hasmaterial = true;
		if(empty($childsandexamples->examples))
			$descriptor_return->hasmaterial = false;

		return $descriptor_return;
	}

	public static function dakora_get_descriptor_details_returns() {
		return new external_single_structure ( array (
				'descriptorid' => new external_value ( PARAM_INT, 'id of descriptor' ),
				'descriptortitle' => new external_value ( PARAM_TEXT, 'title of descriptor' ),
				'teacherevaluation' => new external_value ( PARAM_INT, 'teacher evaluation of descriptor' ),
				'studentevaluation' => new external_value ( PARAM_INT, 'student evaluation of descriptor' ),
				'additionalinfo' => new external_value ( PARAM_TEXT, 'additional grading for descriptor' ),
				'numbering' => new external_value ( PARAM_TEXT, 'numbering' ),
				'niveauid' => new external_value ( PARAM_INT, 'id of niveau' ),
				'niveautitle' => new external_value ( PARAM_TEXT, 'title of niveau' ),
				'hasmaterial' => new external_value ( PARAM_BOOL, 'true or false if descriptor has material' ),
				'children' => new external_multiple_structure ( new external_single_structure ( array (
						'descriptorid' => new external_value ( PARAM_INT, 'id of descriptor' ),
						'descriptortitle' => new external_value ( PARAM_TEXT, 'title of descriptor' ),
						'teacherevaluation' => new external_value ( PARAM_INT, 'teacher evaluation of descriptor' ),
						'studentevaluation' => new external_value ( PARAM_INT, 'student evaluation of descriptor' ),
						'numbering' => new external_value ( PARAM_TEXT, 'numbering' ),
						'hasmaterial' => new external_value ( PARAM_BOOL, 'true or false if descriptor has material' ),
						'examples' => new external_multiple_structure ( new external_single_structure ( array (
								'exampleid' => new external_value ( PARAM_INT, 'id of example' ),
								'exampletitle' => new external_value ( PARAM_TEXT, 'title of example' ),
								'examplestate' => new external_value ( PARAM_INT, 'state of example, always 0 if for all students' ),
								'teacherevaluation' => new external_value ( PARAM_INT, 'example evaluation of teacher' ),
								'studentevaluation' => new external_value ( PARAM_INT, 'example evaluation of student' ),
								'teacheritemvalue' => new external_value ( PARAM_INT, 'item evaluation of teacher' )
						) ) ),
						'examplestotal' => new external_value ( PARAM_INT, 'total number of material' ),
						'examplesvisible' => new external_value ( PARAM_FLOAT, 'visible number of material' ),
						'examplesinwork' => new external_value ( PARAM_FLOAT, 'number of material in work' ),
						'examplesedited' => new external_value ( PARAM_FLOAT, 'number of edited material' ),
						'examplegradings' => new external_single_structure ( array (
								'teacher' => new external_multiple_structure ( new external_single_structure ( array (
										'sum' => new external_value ( PARAM_INT, 'number of gradings' )
								) ) ),
								'student' => new external_multiple_structure ( new external_single_structure ( array (
										'sum' => new external_value ( PARAM_INT, 'number of gradings' )
								) ) )
						) )
				) ) ),
				'childrengradings' => new external_single_structure ( array (
						'teacher' => new external_multiple_structure ( new external_single_structure ( array (
								'sum' => new external_value ( PARAM_INT, 'number of gradings' )
						) ) ),
						'student' => new external_multiple_structure ( new external_single_structure ( array (
								'sum' => new external_value ( PARAM_INT, 'number of gradings' )
						) ) )
				) ),
				'examples' => new external_multiple_structure ( new external_single_structure ( array (
						'exampleid' => new external_value ( PARAM_INT, 'id of example' ),
						'exampletitle' => new external_value ( PARAM_TEXT, 'title of example' ),
						'examplestate' => new external_value ( PARAM_INT, 'state of example, always 0 if for all students' ),
						'teacherevaluation' => new external_value ( PARAM_INT, 'example evaluation of teacher' ),
						'studentevaluation' => new external_value ( PARAM_INT, 'example evaluation of student' ),
						'teacheritemvalue' => new external_value ( PARAM_INT, 'item evaluation of teacher' )
				) ) ),
				'examplestotal' => new external_value ( PARAM_INT, 'total number of material' ),
				'examplesvisible' => new external_value ( PARAM_FLOAT, 'visible number of material' ),
				'examplesinwork' => new external_value ( PARAM_FLOAT, 'number of material in work' ),
				'examplesedited' => new external_value ( PARAM_FLOAT, 'number of edited material' ),
				'examplegradings' => new external_single_structure ( array (
						'teacher' => new external_multiple_structure ( new external_single_structure ( array (
								'sum' => new external_value ( PARAM_INT, 'number of gradings' )
						) ) ),
						'student' => new external_multiple_structure ( new external_single_structure ( array (
								'sum' => new external_value ( PARAM_INT, 'number of gradings' )
						) ) )
				) )
		) );
	}


	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function dakora_get_example_information_parameters() {
		return new external_function_parameters ( array (
				'courseid' => new external_value ( PARAM_INT, 'id of course' ),
				'userid' => new external_value ( PARAM_INT, 'id of user' ),
				'exampleid' => new external_value ( PARAM_INT, 'id of example' )
		) );
	}

	/**
	 * get example with all submission details and gradings
	 *
	 * @return
	 */
	public static function dakora_get_example_information($courseid, $userid, $exampleid) {
		global $CFG, $DB, $USER;
		if ($userid == 0)
			$userid = $USER->id;

		static::validate_parameters ( static::dakora_get_example_information_parameters (), array (
				'courseid' => $courseid,
				'userid' => $userid,
				'exampleid' => $exampleid
		) );

		static::require_can_access_course_user($courseid, $userid);

		$example = $DB->get_record(\block_exacomp\DB_EXAMPLES, array('id'=>$exampleid));
		if(!$example)
			throw new invalid_parameter_exception ( 'Example does not exist' );

		$itemInformation = block_exacomp_get_current_item_for_example($userid, $exampleid);
		$exampleEvaluation = $DB->get_record(\block_exacomp\DB_EXAMPLEEVAL,array("studentid" => $userid, "courseid" => $courseid, "exampleid" => $exampleid));

		$data = array();

		if($itemInformation) {
			//item exists
			$data['itemid'] = $itemInformation->id;
			$data['file'] = "";
			$data['isimage'] = false;
			$data['filename'] = "";
			$data['mimetype'] = "";
			$data['teachervalue'] = isset ( $exampleEvaluation->teacher_evaluation ) ? $exampleEvaluation->teacher_evaluation : -1;
			$data['studentvalue'] = isset ( $exampleEvaluation->student_evaluation ) ? $exampleEvaluation->student_evaluation : -1;
			$data['status'] = isset ( $itemInformation->status ) ? $itemInformation->status : -1;
			$data['name'] = $itemInformation->name;
			$data['type'] = $itemInformation->type;
			$data['url'] = $itemInformation->url;
			//$data['teacheritemvalue'] = isset( $itemInformation->teachervalue ) ? $itemInformation->teachervalue : -1;
			$data['teacheritemvalue'] = isset ( $exampleEvaluation->additionalinfo ) ? $exampleEvaluation->additionalinfo : -1;

			if ($itemInformation->type == 'file') {
				require_once $CFG->dirroot . '/blocks/exaport/lib/lib.php';

				if ($file = block_exaport_get_item_file ( $itemInformation )) {
					$fileurl = (string)new moodle_url("/blocks/exaport/portfoliofile.php", [
						'userid' => $userid,
						'itemid' => $itemInformation->id,
						'wstoken' => static::wstoken(),
					]);
					$data['file'] = $fileurl;
					$data['mimetype'] = $file->get_mimetype();
					$data['filename'] = $file->get_filename();
				}
			}

			$data['studentcomment'] = '';
			$data['teachercomment'] = '';
			$data['teacherfile'] = '';

			$itemcomments = \block_exaport\api::get_item_comments($itemInformation->id);
			foreach ( $itemcomments as $itemcomment ) {
				if ($userid == $itemcomment->userid) {
					$data['studentcomment'] = $itemcomment->entry;
				} elseif (true) { // TODO: check if is teacher?
					$data['teachercomment'] = $itemcomment->entry;
					if ($itemcomment->file) {
						$fileurl = (string)new moodle_url("/blocks/exaport/portfoliofile.php", [
							'userid' => $userid,
							'itemid' => $itemInformation->id,
							'commentid' => $itemcomment->id,
							'wstoken' => static::wstoken(),
						]);
						$data['teacherfile'] = $fileurl;
					}
				}
			}
		} else {
			//no item and therefore no submission exists
			$data['itemid'] = 0;
			$data['status'] = -1;
			$data['name'] = "";
			$data['file'] = "";
			$data['filename'] = "";
			$data['url'] = "";
			$data['type'] = "";
			$data['mimetype'] = "";
			$data['teachercomment'] = "";
			$data['studentcomment'] = "";
			$data['teacherfile'] = '';
			$data['teachervalue'] = isset ( $exampleEvaluation->teacher_evaluation ) ? $exampleEvaluation->teacher_evaluation : -1;
			$data['studentvalue'] = isset ( $exampleEvaluation->student_evaluation ) ? $exampleEvaluation->student_evaluation : -1;
			$data['teacheritemvalue'] = isset ( $exampleEvaluation->additionalinfo ) ? $exampleEvaluation->additionalinfo : -1;
		}

		if(!$exampleEvaluation || $exampleEvaluation->resubmission)
			$data['resubmission'] = true;
		else
			$data['resubmission'] = false;

		return $data;
	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_multiple_structure
	 */
	public static function dakora_get_example_information_returns() {
		return new external_single_structure ( array (
				'itemid' => new external_value ( PARAM_INT, 'id of item' ),
				'status' => new external_value ( PARAM_INT, 'status of the submission (-1 == no submission; 0 == not graded; 1 == graded' ),
				'name' => new external_value ( PARAM_TEXT, 'title of item' ),
				'type' => new external_value ( PARAM_TEXT, 'type of item (note,file,link)' ),
				'url' => new external_value ( PARAM_TEXT, 'url' ),
				'filename' => new external_value ( PARAM_TEXT, 'title of item' ),
				'file' => new external_value ( PARAM_URL, 'file url' ),
				'mimetype' => new external_value ( PARAM_TEXT, 'mime type for file' ),
				'teachervalue' => new external_value ( PARAM_INT, 'teacher grading' ),
				'studentvalue' => new external_value ( PARAM_INT, 'student grading' ),
				'teachercomment' => new external_value ( PARAM_TEXT, 'teacher comment' ),
				'teacherfile' => new external_value ( PARAM_TEXT ),
				'studentcomment' => new external_value ( PARAM_TEXT, 'student comment' ),
				'teacheritemvalue' => new external_value ( PARAM_INT, 'item teacher grading' ),
				'resubmission' => new external_value ( PARAM_BOOL, 'resubmission is allowed/not allowed' )
		) );
	}

	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function dakora_get_user_information_parameters() {
		return new external_function_parameters ( array ( ) );
	}

	/**
	 * get example with all submission details and gradings
	 *
	 * @return
	 */
	public static function dakora_get_user_information() {
		global $CFG, $USER;
		require_once($CFG->dirroot . "/user/lib.php");

		return user_get_user_details_courses($USER);
	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_multiple_structure
	 */
	public static function dakora_get_user_information_returns() {
		return new external_single_structure ( array(
			'id'	=> new external_value(PARAM_INT, 'ID of the user'),
			'username'	=> new external_value(PARAM_RAW, 'The username', VALUE_OPTIONAL),
			'firstname'   => new external_value(PARAM_NOTAGS, 'The first name(s) of the user', VALUE_OPTIONAL),
			'lastname'	=> new external_value(PARAM_NOTAGS, 'The family name of the user', VALUE_OPTIONAL),
			'fullname'	=> new external_value(PARAM_NOTAGS, 'The fullname of the user'),
			'email'	   => new external_value(PARAM_TEXT, 'An email address - allow email as root@localhost', VALUE_OPTIONAL),
			'firstaccess' => new external_value(PARAM_INT, 'first access to the site (0 if never)', VALUE_OPTIONAL),
			'lastaccess'  => new external_value(PARAM_INT, 'last access to the site (0 if never)', VALUE_OPTIONAL),
			'auth'		=> new external_value(PARAM_PLUGIN, 'Auth plugins include manual, ldap, imap, etc', VALUE_OPTIONAL),
			'confirmed'   => new external_value(PARAM_INT, 'Active user: 1 if confirmed, 0 otherwise', VALUE_OPTIONAL),
			'lang'		=> new external_value(PARAM_SAFEDIR, 'Language code such as "en", must exist on server', VALUE_OPTIONAL),
			'url'		 => new external_value(PARAM_URL, 'URL of the user', VALUE_OPTIONAL),
			'profileimageurlsmall' => new external_value(PARAM_URL, 'User image profile URL - small version'),
			'profileimageurl' => new external_value(PARAM_URL, 'User image profile URL - big version')
		) );
	}


	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function dakora_get_competence_profile_for_topic_parameters() {
		return new external_function_parameters ( array (
				'courseid' => new external_value ( PARAM_INT, 'id of course' ),
				'userid' => new external_value ( PARAM_INT, 'id of user' ),
				'topicid' => new external_value ( PARAM_INT, 'id of topic' )
		) );
	}

	/**
	 * get example with all submission details and gradings
	 *
	 * @return
	 */
	public static function dakora_get_competence_profile_for_topic($courseid, $userid, $topicid) {
		global $DB, $USER;

		static::validate_parameters ( static::dakora_get_competence_profile_for_topic_parameters (), array (
				'courseid' => $courseid,
				'userid' => $userid,
				'topicid' => $topicid
		) );

		if ($userid == 0)
			$userid = $USER->id;

		static::require_can_access_course_user($courseid, $userid);

		$data = new stdClass();

		$topic = block_exacomp_get_topic_by_id($topicid);
		$user = $DB->get_record('user', array('id'=>$userid));
		$user = block_exacomp_get_user_information_by_course($user, $courseid);

		$data->topictitle = $topic->title;
		$data->topicnumbering = block_exacomp_get_topic_numbering($topic);
		$data->descriptordata = array();

		$descriptors = block_exacomp_get_descriptors_by_topic($courseid, $topicid, false, true, true);
		foreach($descriptors as $descriptor){
			$data_content = new stdClass();
			$data_content->descriptorid = $descriptor->id;
			$niveau = $DB->get_record(\block_exacomp\DB_NIVEAUS, array('id'=>$descriptor->niveauid));
			$data_content->lfstitle = $niveau->title;
			$lmdata = block_exacomp_calc_example_stat_for_profile($courseid, $descriptor, $user, 0, $niveau->title);
			$data_content->lfsgraphdata = $lmdata->dataobject;
			$data_content->totallmnumb = $lmdata->total;
			$data_content->inworklmnumb = $lmdata->inWork;
			$data_content->teacherevaluation = (isset($user->competencies->teacher[$descriptor->id]))?$user->competencies->teacher[$descriptor->id]:-1;
			$data_content->additionalinfo = (isset($user->competencies->teacher_additional_grading[$descriptor->id]))?$user->competencies->teacher_additional_grading[$descriptor->id]:'';
			$data_content->studentevaluation = (isset($user->competencies->student[$descriptor->id]))?$user->competencies->student[$descriptor->id]:-1;
			$data->descriptordata[] = $data_content;
		}

		return $data;
	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_multiple_structure
	 */
	public static function dakora_get_competence_profile_for_topic_returns() {
		return new external_single_structure ( array (
			'topictitle' => new external_value(PARAM_TEXT, 'title of topic'),
			'topicnumbering' => new external_value (PARAM_TEXT, 'numbering for topic'),
			'descriptordata' => new external_multiple_structure ( new external_single_structure ( array (
					'descriptorid' => new external_value( PARAM_INT, 'id of descriptor'),
					'lfstitle' => new external_value ( PARAM_TEXT, 'title of lfs' ),
					'lfsgraphdata' => new external_multiple_structure ( new external_single_structure( array (
						'data' => new external_single_structure ( array (
							'niveau' => new external_value ( PARAM_TEXT, 'title of niveau'),
							'count' => new external_value ( PARAM_TEXT, 'amount of lm in this category' )
							)),
						'name' => new external_value ( PARAM_TEXT, 'name of dataset' )
						) ) ),
					'totallmnumb' => new external_value ( PARAM_INT, 'number of learning material in total'),
					'inworklmnumb' => new external_value (PARAM_INT, 'number of learning material in work'),
					'teacherevaluation' => new external_value ( PARAM_INT, 'grading of descriptor'),
					'additionalinfo' => new external_value (PARAM_TEXT, 'additional grading of descriptor'),
					'studentevaluation' => new external_value ( PARAM_INT, 'self evaluation of descriptor')
			) ) )
		) );
	}

	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function dakora_get_admin_grading_scheme_parameters() {
		return new external_function_parameters ( array () );
	}

	/**
	 * get example with all submission details and gradings
	 *
	 * @return
	 */
	public static function dakora_get_admin_grading_scheme() {
		static::validate_parameters ( static::dakora_get_admin_grading_scheme_parameters (), array () );

		return \block_exacomp\global_config::get_scheme_id();
	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_multiple_structure
	 */
	public static function dakora_get_admin_grading_scheme_returns() {
		return new external_value ( PARAM_INT, 'identity of grading scheme' );
	}

	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function dakora_create_blocking_event_parameters() {
		return new external_function_parameters ( array (
				'courseid' => new external_value (PARAM_INT, 'id of course'),
				'title' => new external_value ( PARAM_TEXT, 'title of new blocking event' ),
				'userid'=> new external_value ( PARAM_INT, 'id of user'),
				'preplanningstorage'=> new external_value (PARAM_BOOL, 'in pre planning storage or for specific student')
		) );
	}

	/**
	 * Create a new blocking event
	 */
	public static function dakora_create_blocking_event($courseid, $title, $userid, $preplanningstorage) {
		global $USER;

		static::validate_parameters(static::dakora_create_blocking_event_parameters(), array('courseid'=>$courseid,'title'=>$title,
			'userid'=>$userid, 'preplanningstorage'=>$preplanningstorage));

		if($userid == 0 && !$preplanningstorage)
			$userid = $USER->id;

		static::require_can_access_course_user($courseid, $userid);

		block_exacomp_create_blocking_event($courseid, $title, $USER->id, $userid);

		return array("success"=>true);
	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_single_structure
	 */
	public static function dakora_create_blocking_event_returns() {
		return new external_single_structure ( array (
				'success' => new external_value ( PARAM_BOOL, 'status' )
		) );
	}

	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function dakora_get_examples_by_descriptor_and_grading_parameters() {
		return new external_function_parameters ( array (
				'courseid' => new external_value (PARAM_INT, 'id of course'),
				'userid' => new external_value (PARAM_INT, 'id of user'),
				'descriptorid' => new external_value ( PARAM_TEXT, 'id of descriptor' ),
				'grading' => new external_value (PARAM_INT, 'grading value')
		) );
	}

	/**
	 * Create a new blocking event
	 */
	public static function dakora_get_examples_by_descriptor_and_grading($courseid, $userid, $descriptorid, $grading) {
		global $USER;

		static::validate_parameters(static::dakora_get_examples_by_descriptor_and_grading_parameters(), array('courseid'=>$courseid,
		'userid'=>$userid, 'descriptorid'=>$descriptorid, 'grading'=>$grading));

		if($userid == 0)
			$userid = $USER->id;

		static::require_can_access_course_user($courseid, $userid);

		$grading = $grading -1;

		$childsandexamples = static::get_descriptor_children($courseid, $descriptorid, $userid, 0);

		$examples_return = array();

		//parent descriptor
		$examples = static::dakora_get_examples_for_descriptor_with_grading($courseid, $descriptorid, $userid, false);

		foreach($examples as $example){
			if($example->teacherevaluation == $grading){
				if(!array_key_exists($example->exampleid, $examples_return))
					$examples_return[$example->exampleid] = $example;
			}
		}

		foreach($childsandexamples->children as $child){
			$examples = static::dakora_get_examples_for_descriptor_with_grading($courseid, $child->childid, $userid, false);

			foreach($examples as $example){
				if($example->teacherevaluation == $grading){
					if(!array_key_exists($example->exampleid, $examples_return))
						$examples_return[$example->exampleid] = $example;
				}
			}
		}

		return $examples_return;
	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_single_structure
	 */
	public static function dakora_get_examples_by_descriptor_and_grading_returns() {
		return new external_multiple_structure ( new external_single_structure ( array (
			'exampleid' => new external_value(PARAM_INT, 'id of topic'),
			'exampletitle' => new external_value(PARAM_TEXT, 'title of topic')
		) ) );
	}

	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function dakora_allow_example_resubmission_parameters() {
		return new external_function_parameters ( array (
				'courseid' => new external_value (PARAM_INT, 'id of course'),
				'userid' => new external_value (PARAM_INT, 'id of user'),
				'exampleid' => new external_value ( PARAM_INT, 'id of example' )
		) );
	}

	/**
	 * Create a new blocking event
	 */
	public static function dakora_allow_example_resubmission($courseid, $userid, $exampleid) {
		global $USER;

		static::validate_parameters(static::dakora_allow_example_resubmission_parameters(), array('courseid'=>$courseid,
				'userid'=>$userid, 'exampleid'=>$exampleid));

		static::require_can_access_course_user($courseid, $userid);

		block_exacomp_allow_resubmission($userid, $exampleid, $courseid);

		return array('success' => true);

	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_single_structure
	 */
	public static function dakora_allow_example_resubmission_returns() {
		return new external_single_structure ( array (
				'success' => new external_value ( PARAM_BOOL, 'status of success, either true (1) or false (0)' )
		) );
	}

	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function dakora_get_competence_grid_for_profile_parameters() {
		return new external_function_parameters ( array (
				'courseid' => new external_value (PARAM_INT, 'id of course'),
				'userid' => new external_value (PARAM_INT, 'id of user')
		) );
	}

	/**
	 * Create a new blocking event
	 */
	public static function dakora_get_competence_grid_for_profile($courseid, $userid) {
		global $USER;

		static::validate_parameters(static::dakora_get_competence_grid_for_profile_parameters(), array('courseid'=>$courseid,
				'userid'=>$userid));

		if($userid == 0)
			$userid = $USER->id;

		static::require_can_access_course_user($courseid, $userid);
		$subjects = block_exacomp_get_subjects_by_course($courseid);
		
		$content = array();
		
		foreach($subjects as $subject) {
			$subjectinfo = block_exacomp_get_competence_profile_grid_for_ws($courseid, $userid, $subject->id);
			$subjectinfo->subjectid = $subject->id;
			$subjectinfo->subjecttitle = $subject->title;
			
			$content[] = $subjectinfo;
		}

		return $content;
	}

	/**
	 * Returns desription of method return values
	 *
	 * @return external_single_structure
	 */
	public static function dakora_get_competence_grid_for_profile_returns() {
		return new external_multiple_structure ( new external_single_structure ( array (
			'subjectid' => new external_value ( PARAM_INT, 'subjectid' ),
			'subjecttitle' => new external_value ( PARAM_TEXT, 'subjecttitle' ),
			'rows' => new external_multiple_structure ( new external_single_structure ( array (
					'columns' => new external_multiple_structure ( new external_single_structure( array (
						'text' => new external_value ( PARAM_TEXT, 'cell text'),
						'span' => new external_value ( PARAM_INT, 'colspan' )
					) ) )
			) ) )
		) ) );
	}

	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function is_elove_student_self_assessment_enabled_parameters() {
		return new external_function_parameters ( array () );
	}
	
	/**
	 * @return boolean
	 */
	public static function is_elove_student_self_assessment_enabled() {
		global $DB, $USER;
		static::validate_parameters ( static::is_elove_student_self_assessment_enabled_parameters (), array () );
	
		return array('enabled' => block_exacomp_is_elove_student_self_assessment_enabled());
	}
	
	/**
	 * Returns description of method return values
	 *
	 * @return external_multiple_structure
	 */
	public static function is_elove_student_self_assessment_enabled_returns() {
		return new external_function_parameters ( array (
				'enabled' => new external_value ( PARAM_BOOL, '' )
		) );
	}
	
	/**
	* helper function to use same code for 2 ws
	*/
private static function get_descriptor_children($courseid, $descriptorid, $userid, $forall, $crosssubjid = 0, $show_all = false) {
		global $DB;

		if ($forall) {
			static::require_can_access_course($courseid);
		} else {
			static::require_can_access_course_user($courseid, $userid);
		}

		$coursesettings = block_exacomp_get_settings_by_course($courseid);

		$isTeacher = (static::dakora_get_user_role()->role == \block_exacomp\WS_ROLE_TEACHER);
		$showexamples = ($isTeacher)?true:$coursesettings->show_all_examples;

		$parent_descriptor = $DB->get_record(\block_exacomp\DB_DESCRIPTORS, array('id'=>$descriptorid));
		$descriptor_topic_mm = $DB->get_record(\block_exacomp\DB_DESCTOPICS, array('descrid'=>$parent_descriptor->id));
		$parent_descriptor->topicid = $descriptor_topic_mm->topicid;

		$children = block_exacomp_get_child_descriptors($parent_descriptor, $courseid, false, array(SHOW_ALL_TAXONOMIES), true, true, true);

		$non_visibilities = $DB->get_fieldset_select(\block_exacomp\DB_DESCVISIBILITY,'descrid', 'courseid=? AND studentid=? AND visible=0', array($courseid, 0));

		if($crosssubjid > 0) {
			$crossdesc = $DB->get_fieldset_select(\block_exacomp\DB_DESCCROSS, 'descrid', 'crosssubjid=?', array($crosssubjid));
		} else {
			$crossdesc = [];
		}

		if(!$forall) {
			$non_visibilities_student = $DB->get_fieldset_select(\block_exacomp\DB_DESCVISIBILITY, 'descrid', 'courseid=? AND studentid=? AND visible=0', array($courseid, $userid));
		} else {
			$non_visibilities_student = [];
		}

		$children_return = array();
		foreach($children as $child){
			if($child->examples || $show_all){
				$child_return = static::dakora_get_descriptor_details($courseid, $child->id, $userid, $forall, $crosssubjid);

				if(!in_array($child->id, $non_visibilities) && ((!$forall && !in_array($child->id, $non_visibilities_student))||$forall)){
					if($crosssubjid == 0 || in_array($child->id, $crossdesc) || in_array($descriptorid, $crossdesc))
						$children_return[] = $child_return;
				}
			}
		}

		$examples_return = array();

		if($crosssubjid == 0 || in_array($parent_descriptor->id, $crossdesc)){
			$parent_descriptor = block_exacomp_get_examples_for_descriptor($parent_descriptor, array(SHOW_ALL_TAXONOMIES), $showexamples, $courseid);

			$example_non_visibilities = $DB->get_fieldset_select(\block_exacomp\DB_EXAMPVISIBILITY, 'exampleid', 'courseid=? AND studentid=? AND visible=0', array($courseid, 0));
			if(!$forall) {
				$example_non_visibilities_student = $DB->get_fieldset_select(\block_exacomp\DB_EXAMPVISIBILITY, 'exampleid', 'courseid=? AND studentid=? AND visible=0', array($courseid, $userid));
			} else {
				$example_non_visibilities_student = [];
			}

			foreach($parent_descriptor->examples as $example){

				$example_return = new stdClass();
				$example_return->exampleid = $example->id;
				$example_return->exampletitle = $example->title;
				$example_return->examplestate = ($forall)?0:block_exacomp_get_dakora_state_for_example($courseid, $example->id, $userid);

				if(!array_key_exists($example->id, $examples_return) && (!in_array($example->id, $example_non_visibilities)) && ((!$forall && !in_array($example->id, $example_non_visibilities_student))||$forall))
					$examples_return[$example->id] = $example_return;
			}
		}

		$return = new stdClass();
		$return->children = $children_return;
		//$return->examples = $examples_return;
		$return->examples = static::dakora_get_examples_for_descriptor_common($courseid, $descriptorid, $userid, $forall, 0);

		$descriptor_example_statistic = static::get_descriptor_example_statistic($courseid, $userid, $descriptorid, $forall, $crosssubjid);

		$return->examplestotal = $descriptor_example_statistic->total;
		$return->examplesvisible = $descriptor_example_statistic->visible;
		$return->examplesinwork = $descriptor_example_statistic->inwork;
		$return->examplesedited = $descriptor_example_statistic->edited;
		return $return;
	}

	private static function dakora_get_topics_by_course_common($courseid, $only_associated){

		static::require_can_access_course($courseid);

		//TODO if added for 1 student -> mind visibility for this student
		$tree = block_exacomp_build_example_association_tree($courseid, array(), 0, 0, true);

		$topics_return = array();
		foreach($tree as $subject){
			foreach($subject->topics as $topic){
				if(!$only_associated || ($only_associated && $topic->associated == 1)){
					$topic_return = new stdClass();
					$topic_return->topicid = $topic->id;
					$topic_return->topictitle = $topic->title;
					$topic_return->numbering = block_exacomp_get_topic_numbering($topic->id);
					$topic_return->subjectid = $subject->id;
					$topic_return->subjecttitle = $subject->title;
					$topics_return[] = $topic_return;
				}
			}
		}

		return $topics_return;
	}


	private static function dakora_get_descriptors_common($courseid, $topicid, $userid, $forall, $only_associated){
		global $DB;

		if ($forall) {
			static::require_can_access_course($courseid);
		} else {
			static::require_can_access_course_user($courseid, $userid);
		}

		$tree = block_exacomp_build_example_association_tree($courseid, array(), 0, 0, true);

		$non_visibilities = $DB->get_fieldset_select(\block_exacomp\DB_DESCVISIBILITY,'descrid', 'courseid=? AND studentid=? AND visible=0', array($courseid, 0));

		if(!$forall)
			$non_visibilities_student = $DB->get_fieldset_select(\block_exacomp\DB_DESCVISIBILITY,'descrid', 'courseid=? AND studentid=? AND visible=0', array($courseid, $userid));

		$descriptors_return = array();
		foreach($tree as $subject){
			foreach($subject->topics as $topic){
				if($topic->id == $topicid){
					foreach($topic->descriptors as $descriptor){
						if(!$only_associated || ($only_associated && $descriptor->associated == 1)){
							$descriptor_return = new stdClass();
							$descriptor_return->descriptorid = $descriptor->id;
							$descriptor_return->descriptortitle = $descriptor->title;
							$descriptor_return->numbering = block_exacomp_get_descriptor_numbering($descriptor);
							$descriptor_return->niveautitle = "";
							$descriptor_return->niveauid = 0;
							if($descriptor->niveauid){
								$niveau = $DB->get_record(\block_exacomp\DB_NIVEAUS, array('id'=>$descriptor->niveauid));
								$descriptor_return->niveautitle = $niveau->title;
								$descriptor_return->niveauid = $niveau->id;
							}
							if(!in_array($descriptor->id, $non_visibilities) && ((!$forall && !in_array($descriptor->id, $non_visibilities_student))||$forall))
								$descriptors_return[] = $descriptor_return;
						}
					}
				}
			}
		}

		return $descriptors_return;
	}

	private static function dakora_get_descriptors_by_cross_subject_common($courseid, $crosssubjid, $userid, $forall, $only_associated){
		global $DB;

		$descriptors = block_exacomp_get_descriptors_for_cross_subject($courseid, $crosssubjid, true);

		$non_visibilities = $DB->get_fieldset_select(\block_exacomp\DB_DESCVISIBILITY,'descrid', 'courseid=? AND studentid=? AND visible=0', array($courseid, 0));

		if(!$forall) {
			$non_visibilities_student = $DB->get_fieldset_select(\block_exacomp\DB_DESCVISIBILITY, 'descrid', 'courseid=? AND studentid=? AND visible=0', array($courseid, $userid));
		} else {
			$non_visibilities_student = [];
		}

		$descriptors_return = array();
		foreach($descriptors as $descriptor){
			if(!in_array($descriptor->id, $non_visibilities) && ((!$forall && !in_array($descriptor->id, $non_visibilities_student))||$forall)){ 	//descriptor is visibile
				if($only_associated){
					$has_visible_examples = false;
					$has_children_with_visible_examples = false;

					$example_non_visibilities = $DB->get_fieldset_select(\block_exacomp\DB_EXAMPVISIBILITY, 'exampleid', 'courseid=? AND studentid=? AND visible=0', array($courseid, 0));
						if(!$forall)
							$example_non_visibilities_student = $DB->get_fieldset_select(\block_exacomp\DB_EXAMPVISIBILITY, 'exampleid', 'courseid=? AND studentid=? AND visible=0', array($courseid, $userid));


					if(isset($descriptor->examples)){	//descriptor has examples
						foreach($descriptor->examples as $example){
							if(!in_array($example->id, $example_non_visibilities) && ((!$forall && !in_array($example->id, $example_non_visibilities_student))||$forall))
								$has_visible_examples = true;	//descriptor has visible examples

						}
					}

					if(isset($descriptor->children)){
						foreach($descriptor->children as $child){
							if((!in_array($child->id, $non_visibilities) && ((!$forall && !in_array($child->id, $non_visibilities_student))||$forall))){ //child is visible
								if(isset($child->examples)){	//descriptor has children
									foreach($child->examples as $example){
										if(!in_array($example->id, $example_non_visibilities) && ((!$forall && !in_array($example->id, $example_non_visibilities_student))||$forall))
											$has_children_with_visible_examples = true;	//descriptor has children with visible examples
									}
								}
							}
						}
					}

					if($has_visible_examples || $has_children_with_visible_examples){
							$descriptor_return = new stdClass();
							$descriptor_return->descriptorid = $descriptor->id;
							$descriptor_return->descriptortitle = $descriptor->title;
							$descriptor_return->numbering = block_exacomp_get_descriptor_numbering($descriptor);
							$descriptor_return->niveautitle = "";
							$descriptor_return->niveauid = 0;
							if($descriptor->niveauid){
								$niveau = $DB->get_record(\block_exacomp\DB_NIVEAUS, array('id'=>$descriptor->niveauid));
								$descriptor_return->niveautitle = substr(block_exacomp_get_descriptor_numbering($descriptor),0,1).": ".$niveau->title;
								$descriptor_return->niveauid = $niveau->id;
							}
							$descriptors_return[] = $descriptor_return;
					}
				}else{
					$descriptor_return = new stdClass();
					$descriptor_return->descriptorid = $descriptor->id;
					$descriptor_return->descriptortitle = $descriptor->title;
					$descriptor_return->numbering = block_exacomp_get_descriptor_numbering($descriptor);
					$descriptor_return->niveautitle = "";
					$descriptor_return->niveauid = 0;
					if($descriptor->niveauid){
						$niveau = $DB->get_record(\block_exacomp\DB_NIVEAUS, array('id'=>$descriptor->niveauid));
						$descriptor_return->niveautitle = substr(block_exacomp_get_descriptor_numbering($descriptor),0,1).": ".$niveau->title;
						$descriptor_return->niveauid = $niveau->id;
					}
					$descriptors_return[] = $descriptor_return;
				}
			}
		}

		return $descriptors_return;
	}

	private static function dakora_get_examples_for_descriptor_common($courseid, $descriptorid, $userid, $forall, $crosssubjid=0){
		global $DB;

		if ($forall) {
			static::require_can_access_course($courseid);
		} else {
			static::require_can_access_course_user($courseid, $userid);
		}

		$descriptor = $DB->get_record(\block_exacomp\DB_DESCRIPTORS, array('id'=>$descriptorid));
		$coursesettings = block_exacomp_get_settings_by_course($courseid);

		$isTeacher = (static::dakora_get_user_role()->role == \block_exacomp\WS_ROLE_TEACHER);
		$showexamples = ($isTeacher)?true:$coursesettings->show_all_examples;

		if($crosssubjid > 0){
			$cross_subject_descriptors = block_exacomp_get_cross_subject_descriptors($crosssubjid);
			if(!array_key_exists($descriptor->id, $cross_subject_descriptors))
				return array();
		}

		// get the topicid
		// note: parent and child descriptors are always associated with the topic
		$descriptor_topic_mm = $DB->get_record(\block_exacomp\DB_DESCTOPICS, array('descrid'=>$descriptor->id));
		$descriptor->topicid = $descriptor_topic_mm->topicid;
		$descriptor = block_exacomp_get_examples_for_descriptor($descriptor, array(SHOW_ALL_TAXONOMIES), $showexamples, $courseid);

		$example_non_visibilities = $DB->get_fieldset_select(\block_exacomp\DB_EXAMPVISIBILITY, 'exampleid', 'courseid=? AND studentid=? AND visible=0', array($courseid, 0));
		if(!$forall)
			$example_non_visibilities_student = $DB->get_fieldset_select(\block_exacomp\DB_EXAMPVISIBILITY, 'exampleid', 'courseid=? AND studentid=? AND visible=0', array($courseid, $userid));

		$examples_return = array();
		foreach($descriptor->examples as $example){
			$example_return = new stdClass();
			$example_return->exampleid = $example->id;
			$example_return->exampletitle = $example->title;
			$example_return->examplestate = ($forall)?0:block_exacomp_get_dakora_state_for_example($courseid, $example->id, $userid);

			if($forall){
				$example_return->teacherevaluation = -1;
				$example_return->studentevaluation = -1;
				$example_return->teacheritemvalue = -1;
			}else{
				$evaluation = (object) static::dakora_get_example_information($courseid, $userid, $example->id);
				$example_return->teacherevaluation = $evaluation->teachervalue;
				$example_return->studentevaluation = $evaluation->studentvalue;
				$example_return->teacheritemvalue = $evaluation->teacheritemvalue;
			}

			if(!array_key_exists($example->id, $examples_return) && (!in_array($example->id, $example_non_visibilities)) && ((!$forall && !in_array($example->id, $example_non_visibilities_student))||$forall))
				$examples_return[$example->id] = $example_return;
		}

		return $examples_return;
	}
	private static function get_descriptor_example_statistic($courseid, $userid, $descriptorid, $forall, $crosssubjid){
		$return = new stdClass();
		$return->total = 0;
		$return->visible = 0;
		$return->inwork = 0;
		$number_students = 1;

		if($forall) {
			static::require_can_access_course($courseid);
			$students = block_exacomp_get_students_by_course($courseid);
			$number_students = count($students);
		}
		else
			static::require_can_access_course_user($courseid, $userid);

		list($total, $gradings, $notEvaluated, $inWork,$totalGrade, $notInWork, $totalHidden, $edited) = block_exacomp_get_example_statistic_for_descriptor($courseid, $descriptorid, $userid, $crosssubjid);

		$return->total = $totalHidden/ $number_students;
		$return->visible = $total / $number_students;
		$return->inwork = ($inWork>0)?$inWork / $number_students:0;
		$return->edited = $edited / $number_students;
		return $return;
	}

	/**
	 * Returns all subjects for the given user where trainer action is required.
	 * Trainer action is required as soon as there are ungraded submissions.
	 * @param int $userid
	 */
	private function get_requireaction_subjects($userid) {
		global $DB;

		$require_actions = $DB->get_records_sql('SELECT s.id FROM {block_exacompsubjects} s
				JOIN {block_exacomptopics} t ON t.subjid = s.id
				JOIN {block_exacompdescrtopic_mm} td ON td.topicid = t.id
				JOIN {block_exacompdescriptors} d ON td.descrid = d.id
				JOIN {block_exacompdescrexamp_mm} de ON de.descrid = d.id
				JOIN {block_exacompexamples} e ON de.exampid = e.id
				JOIN {block_exacompitemexample} ie ON ie.exampleid = e.id
				JOIN {block_exaportitem} i ON i.id = ie.itemid
				WHERE ie.status = 0 AND i.userid = ?', array($userid));

		return $require_actions;
	}
	private static function require_can_access_course($courseid) {
		$course = g::$DB->get_record('course', ['id'=>$courseid]);
		if (!$course) {
			throw new invalid_parameter_exception ( 'Course not found' );
		}
		if (!can_access_course($course)) {
			throw new invalid_parameter_exception ( 'Not allowed to access this course' );
		}
	}

	private static function require_can_access_user($userid) {
		// can view myself
		if ($userid == g::$USER->id) {
			return;
		}

		// check external trainers
		$isTrainer = g::$DB->get_record ( \block_exacomp\DB_EXTERNAL_TRAINERS, array (
				'trainerid' => g::$USER->id,
				'studentid' => $userid
		) );
		if ($isTrainer) {
			return;
		}

		// check course teacher
		require_once g::$CFG->dirroot.'/lib/enrollib.php';
		$courses = enrol_get_users_courses(g::$USER->id, true);
		foreach ($courses as $course) {
			if (block_exacomp_is_teacher ( $course->id )) {
				$users = get_enrolled_users(block_exacomp_get_context_from_courseid($course->id));
				if (isset($users[$userid])) {
					// ok
					return;
				}
			}
		}

		throw new invalid_parameter_exception ( 'Not allowed to view other user' );
	}

	/**
	 * Used to check if current user is allowed to view the user(student) $userid
	 *
	 * @param int $courseid
	 * @param int|object $userid
	 * @throws invalid_parameter_exception
	 */
	private static function require_can_access_course_user($courseid, $userid) {
		if ($courseid) {
			// because in webservice block_exacomp_get_descriptors_for_example $courseid = 0

			$course = g::$DB->get_record('course', ['id'=>$courseid]);
			if (!$course) {
				throw new invalid_parameter_exception ( 'Course not found' );
			}

			if (!can_access_course($course)) {
				throw new invalid_parameter_exception ( 'Not allowed to access this course' );
			}
		}

		// can view myself
		if ($userid == g::$USER->id) {
			return;
		}

		// teacher can view other users
		if (block_exacomp_is_teacher ( $courseid )) {
			if ($userid == 0)
				return;
			$users = get_enrolled_users(block_exacomp_get_context_from_courseid($courseid));
			if (isset($users[$userid])) {
				return;
			}
		}

		throw new invalid_parameter_exception ( 'Not allowed to view other user' );
	}

	/**
	 * @param $exampleid
	 * @param int $courseid if courseid=0, then we don't know the course and have to search all
	 * 		                TODO: if courseid is set, then just search that course
	 * @return object the data of the found example
	 * @throws \block_exacomp\permission_exception
	 */
	private static function require_can_access_example($exampleid, $courseid=null) {
		// go through all courses
		// and all subjects
		// and all examples
		// and try to find it
		$courses_ws = static::get_courses(g::$USER->id);

		$courses = array();
		foreach($courses_ws as $course){
			$courses[$course['courseid']] = new stdClass();
			$courses[$course['courseid']]->id = $course['courseid'];
		}

		//check if user is external trainer, if he is add courses where external_student is enrolled
		// check external trainers
		$external_trainer_entries = g::$DB->get_records ( \block_exacomp\DB_EXTERNAL_TRAINERS, array (
				'trainerid' => g::$USER->id
		) );

		foreach($external_trainer_entries as $ext_tr_entry){
			$courses_user = static::get_courses($ext_tr_entry->studentid);

			foreach($courses_user as $course){
				if(!array_key_exists($course['courseid'], $courses)){
					$courses[$course['courseid']] = new stdClass();
					$courses[$course['courseid']]->id = $course['courseid'];
				}
			}
		}

		$exampleDataFound = null;
		foreach($courses as $course){
			// can be viewed by user, or by whole course
			if (block_exacomp_check_student_example_permission($course->id, $exampleid, g::$USER->id)
				|| block_exacomp_check_student_example_permission($course->id, $exampleid, 0)) {
				$exampleDataFound = (object)[ 'exampleid' => $exampleid, 'courseid' => $course->id ];
				break;
			}
		}

		if (!$exampleDataFound) {
			throw new \block_exacomp\permission_exception();
		}

		return $exampleDataFound;
	}

	private static function wstoken() {
		return optional_param('wstoken', null, PARAM_ALPHANUM);
	}
}
