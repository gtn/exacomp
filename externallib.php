<?php

defined('MOODLE_INTERNAL') || die();

require_once __DIR__.'/inc.php';
require_once $CFG->libdir.'/externallib.php';
require_once $CFG->dirroot.'/mod/assign/locallib.php';
require_once $CFG->dirroot.'/mod/assign/submission/file/locallib.php';
require_once $CFG->dirroot.'/lib/filelib.php';

use \block_exacomp\globals as g;

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
		
		self::validate_parameters ( self::get_courses_parameters (), array (
				'userid' => $userid 
		) );
		
		if (!$userid)
			$userid = $USER->id;

		static::check_can_access_user($userid);

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
	
	/**
	 * Returns description of method parameters
	 * 
	 * @return external_function_parameters
	 */
	public static function get_subjects_parameters() {
		return new external_function_parameters ( array (
				'courseid' => new external_value ( PARAM_INT, 'id of course' ) 
		) );
	}
	
	/**
	 * Get subjects
	 * 
	 * @param
	 *			int courseid
	 * @return array of course subjects
	 */
	public static function get_subjects($courseid) {
		global $DB;
		
		if (empty ( $courseid )) {
			throw new invalid_parameter_exception ( 'Parameter can not be empty' );
		}
		self::validate_parameters ( self::get_subjects_parameters (), array (
				'courseid' => $courseid 
		) );
		
		static::check_can_access_course($courseid);

		$subjects = $DB->get_records_sql ( '
				SELECT s.id as subjectid, s.title
				FROM {block_exacompschooltypes} s
				JOIN {block_exacompmdltype_mm} m ON m.stid = s.id AND m.courseid = ?
				GROUP BY s.id
				ORDER BY s.title
				', array (
				$courseid 
		) );
		
		return $subjects;
	}
	
	/**
	 * Returns desription of method return values
	 * 
	 * @return external_multiple_structure
	 */
	public static function get_subjects_returns() {
		return new external_multiple_structure ( new external_single_structure ( array (
				'subjectid' => new external_value ( PARAM_INT, 'id of subject' ),
				'title' => new external_value ( PARAM_TEXT, 'title of subject' ) 
		) ) );
	}
	
	/**
	 * Returns description of method parameters
	 * 
	 * @return external_function_parameters
	 */
	public static function get_topics_parameters() {
		return new external_function_parameters ( array (
				'subjectid' => new external_value ( PARAM_INT, 'id of subject' ),
				'courseid' => new external_value ( PARAM_INT, 'id of course' ) 
		) );
	}
	
	/**
	 * Get subjects
	 * 
	 * @param
	 *			int courseid
	 * @return array of course subjects
	 */
	public static function get_topics($subjectid, $courseid) {
		global $DB;
		
		if (empty ( $subjectid ) || empty ( $courseid )) {
			throw new invalid_parameter_exception ( 'Parameter can not be empty' );
		}
		
		self::validate_parameters ( self::get_topics_parameters (), array (
				'subjectid' => $subjectid,
				'courseid' => $courseid 
		) );
		
		static::check_can_access_course($courseid);

		/*
		 * $returnval = array();
		 * $returnval[92] = new stdClass();
		 * $returnval[92]->title = "title";
		 * $returnval[92]->topicid=12;
		 * return $returnval;
		 */
		
		$array = $DB->get_records_sql ( '
				SELECT s.id as topicid, s.title
				FROM {block_exacompsubjects} s
				JOIN {block_exacomptopics} t ON t.subjid = s.id
				JOIN {block_exacompcoutopi_mm} ct ON ct.topicid = t.id AND ct.courseid = ?
				' . (block_exacomp_get_settings_by_course ( $courseid )->show_all_descriptors ? '' : '
						-- only show active ones
						JOIN {block_exacompdescrtopic_mm} topmm ON topmm.topicid=t.id
						JOIN {block_exacompdescriptors} d ON topmm.descrid=d.id
						JOIN {block_exacompcompactiv_mm} ca ON (d.id=ca.compid AND ca.comptype = ' . TYPE_DESCRIPTOR . ') OR (t.id=ca.compid AND ca.comptype = ' . TYPE_TOPIC . ')
						JOIN {course_modules} a ON ca.activityid=a.id AND a.course=ct.courseid
						') . '
				WHERE s.stid = ?
				GROUP BY s.id
				ORDER BY s.title
				', array (
				$courseid,
				$subjectid 
		) );
		
		return $array;
	}
	
	/**
	 * Returns desription of method return values
	 * 
	 * @return external_multiple_structure
	 */
	public static function get_topics_returns() {
		return new external_multiple_structure ( new external_single_structure ( array (
				'topicid' => new external_value ( PARAM_INT, 'id of topic' ),
				'title' => new external_value ( PARAM_TEXT, 'title of topic' ) 
		) ) );
	}
	
	/**
	 * Returns description of method parameters
	 * 
	 * @return external_function_parameters
	 */
	public static function get_subtopics_parameters() {
		return new external_function_parameters ( array (
				'courseid' => new external_value ( PARAM_INT, 'id of course' ),
				'topicid' => new external_value ( PARAM_INT, 'id of topic' ) 
		) );
	}
	
	/**
	 * Get subjects
	 * 
	 * @param
	 *			int courseid
	 * @param
	 *			int topicid
	 * @return array of course subjects
	 */
	public static function get_subtopics($courseid, $topicid) {
		global $DB, $USER;
		
		if (empty ( $topicid ) || empty ( $courseid )) {
			throw new invalid_parameter_exception ( 'Parameter can not be empty' );
		}
		
		self::validate_parameters ( self::get_subtopics_parameters (), array (
				'courseid' => $courseid,
				'topicid' => $topicid 
		) );

		static::check_can_access_course($courseid);
		
		$cats = $DB->get_records_menu ( 'block_exacompcategories', array (
				"lvl" => 4 
		), "id,title", "id,title" );
		
		$competencies = array (
				"studentcomps" => $DB->get_records ( 'block_exacompcompuser', array (
						"role" => 0,
						"courseid" => $courseid,
						"userid" => $USER->id,
						"comptype" => TYPE_TOPIC 
				), "", "compid,userid,reviewerid,value" ),
				"teachercomps" => $DB->get_records ( 'block_exacompcompuser', array (
						"role" => 1,
						"courseid" => $courseid,
						"userid" => $USER->id,
						"comptype" => TYPE_TOPIC 
				), "", "compid,userid,reviewerid,value" ) 
		);
		
		$subtopics = $DB->get_records_sql ( '
				SELECT t.id as subtopicid, t.title, t.catid
				FROM {block_exacomptopics} t
				JOIN {block_exacompcoutopi_mm} ct ON ct.topicid = t.id AND t.subjid = ? AND ct.courseid = ?
				' . (block_exacomp_get_settings_by_course ( $courseid )->show_all_descriptors ? '' : '
						-- only show active ones
						JOIN {block_exacompdescrtopic_mm} topmm ON topmm.topicid=t.id
						JOIN {block_exacompdescriptors} d ON topmm.descrid=d.id
						JOIN {block_exacompcompactiv_mm} ca ON (d.id=ca.compid AND ca.comptype = ' . TYPE_DESCRIPTOR . ') OR (t.id=ca.compid AND ca.comptype = ' . TYPE_TOPIC . ')
						JOIN {course_modules} a ON ca.activityid=a.id AND a.course=ct.courseid
						') . '
				GROUP BY t.id
				ORDER BY t.catid, t.title
				', array (
				$topicid,
				$courseid 
		) );
		
		foreach ( $subtopics as $subtopic ) {
			$subtopic->studentcomp = (array_key_exists ( $subtopic->subtopicid, $competencies ['studentcomps'] )) ? true : false;
			$subtopic->teachercomp = (array_key_exists ( $subtopic->subtopicid, $competencies ['teachercomps'] )) ? true : false;
			$subtopic->catid = $cats [$subtopic->catid];
		}
		
		return $subtopics;
	}
	
	/**
	 * Returns desription of method return values
	 * 
	 * @return external_multiple_structure
	 */
	public static function get_subtopics_returns() {
		return new external_multiple_structure ( new external_single_structure ( array (
				'subtopicid' => new external_value ( PARAM_INT, 'id of subtopic' ),
				'title' => new external_value ( PARAM_TEXT, 'title of subtopic' ),
				'catid' => new external_value ( PARAM_TEXT, 'category of subtopic' ),
				'studentcomp' => new external_value ( PARAM_BOOL, 'student self evaluation' ),
				'teachercomp' => new external_value ( PARAM_BOOL, 'teacher evaluation' ) 
		)
		 ) );
	}
	
	/**
	 * Returns description of method parameters
	 * 
	 * @return external_function_parameters
	 */
	public static function set_subtopic_parameters() {
		return new external_function_parameters ( array (
				'courseid' => new external_value ( PARAM_INT, 'id of course' ),
				'subtopicid' => new external_value ( PARAM_INT, 'id of subtopic' ),
				'value' => new external_value ( PARAM_INT, 'evaluation value' ) 
		) );
	}
	
	/**
	 * Set subtopic student evaluation
	 * 
	 * @param
	 *			int courseid
	 * @param
	 *			int subtopicid
	 * @return status
	 */
	public static function set_subtopic($courseid, $subtopicid, $value) {
		global $DB, $USER;
		
		if (empty ( $courseid ) || empty ( $subtopicid ) || ! isset ( $value )) {
			throw new invalid_parameter_exception ( 'Parameter can not be empty' );
		}
		
		self::validate_parameters ( self::set_subtopic_parameters (), array (
				'subtopicid' => $subtopicid,
				'value' => $value,
				'courseid' => $courseid 
		) );
		
		static::check_can_access_course($courseid);

		$transaction = $DB->start_delegated_transaction (); // If an exception is thrown in the below code, all DB queries in this code will be rollback.
		
		$DB->delete_records ( 'block_exacompcompuser', array (
				"userid" => $USER->id,
				"role" => 0,
				"compid" => $subtopicid,
				"courseid" => $courseid,
				'comptype' => TYPE_TOPIC 
		) );
		if ($value > 0) {
			$DB->insert_record ( 'block_exacompcompuser', array (
					"userid" => $USER->id,
					"role" => 0,
					"compid" => $subtopicid,
					"courseid" => $courseid,
					'comptype' => TYPE_TOPIC,
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
	public static function set_subtopic_returns() {
		return new external_single_structure ( array (
				'success' => new external_value ( PARAM_BOOL, 'status of success, either true (1) or false (0)' ) 
		) );
	}
	
	/*
	 * Returns description of method parameters
	 * @return external_function_parameters
	 */
	public static function get_competencies_parameters() {
		return new external_function_parameters ( array (
				'courseid' => new external_value ( PARAM_INT, 'id of course' ),
				'subtopicid' => new external_value ( PARAM_INT, 'id of subtopic' ) 
		) );
	}
	
	/**
	 * Get competencies
	 * 
	 * @param
	 *			int courseid
	 * @param
	 *			int subtopicid
	 * @return external_multiple_structure
	 */
	public static function get_competencies($courseid, $subtopicid) {
		global $DB, $USER;
		
		if (empty ( $courseid ) || empty ( $subtopicid )) {
			throw new invalid_parameter_exception ( 'Parameter can not be empty' );
		}
		
		self::validate_parameters ( self::get_competencies_parameters (), array (
				'subtopicid' => $subtopicid,
				'courseid' => $courseid 
		) );
		
		static::check_can_access_course($courseid);
		
		$courseSettings = block_exacomp_get_settings_by_course ( $courseid );
		
		$descriptors = $DB->get_records_sql ( '
				SELECT d.id as descriptorid, d.title, desctopmm.topicid AS topicid
				FROM {block_exacompdescriptors} d
				JOIN {block_exacompdescrtopic_mm} desctopmm ON desctopmm.descrid=d.id AND desctopmm.topicid = ?
				' . (block_exacomp_get_settings_by_course ( $courseid )->show_all_descriptors ? '' : '
						-- only show active ones
						JOIN {block_exacompcompactiv_mm} ca ON (d.id=ca.compid AND ca.comptype = ' . TYPE_DESCRIPTOR . ')
						JOIN {course_modules} a ON ca.activityid=a.id AND a.course=?
						') . '
				GROUP BY descriptorid
				ORDER BY d.sorting
				', array (
				$subtopicid,
				$courseid 
		) );
		
		$studentEvaluation = $DB->get_records ( 'block_exacompcompuser', array (
				"courseid" => $courseid,
				"userid" => $USER->id,
				"role" => 0,
				"comptype" => TYPE_DESCRIPTOR 
		), '', 'compid' );
		$teacherEvaluation = $DB->get_records ( 'block_exacompcompuser', array (
				"courseid" => $courseid,
				"userid" => $USER->id,
				"role" => 1,
				"comptype" => TYPE_DESCRIPTOR 
		), '', 'compid' );
		
		foreach ( $descriptors as $descriptor ) {
			$descriptor->studentcomp = (array_key_exists ( $descriptor->descriptorid, $studentEvaluation )) ? true : false;
			$descriptor->teachercomp = (array_key_exists ( $descriptor->descriptorid, $teacherEvaluation )) ? true : false;
			
			$descriptor->isexpandable = false;
			// if there are examples for a descriptor
			if ($DB->record_exists ( 'block_exacompdescrexamp_mm', array (
					"descrid" => $descriptor->descriptorid 
			) ))
				$descriptor->isexpandable = true;
			else {
				$activities = $DB->get_records ( 'block_exacompcompactiv_mm', array (
						"compid" => $descriptor->descriptorid,
						"comptype" => TYPE_DESCRIPTOR 
				) );
				
				foreach ( $activities as $activity ) {
					$module = $DB->get_record ( 'course_modules', array (
							'id' => $activity->activityid 
					) );
					// only assignments
					if ($courseSettings->uses_activities && $module && $module->module == 1) {
						$descriptor->isexpandable = true;
						continue;
					} else if ($activity->eportfolioitem == 1) {
						$descriptor->isexpandable = true;
					}
				}
			}
		}
		return $descriptors;
	}
	
	/**
	 * Returns desription of method return values
	 * 
	 * @return external_multiple_structure
	 */
	public static function get_competencies_returns() {
		return new external_multiple_structure ( new external_single_structure ( array (
				'descriptorid' => new external_value ( PARAM_INT, 'id of descriptor' ),
				'title' => new external_value ( PARAM_TEXT, 'title of descriptor' ),
				'isexpandable' => new external_value ( PARAM_BOOL, 'is expandable if there are associated examples or eportfolio items' ),
				'studentcomp' => new external_value ( PARAM_BOOL, 'student self evaluation' ),
				'teachercomp' => new external_value ( PARAM_BOOL, 'teacher evaluation' ) 
		) ) );
	}
	
	/**
	 * Returns description of method parameters
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
		
		self::validate_parameters ( self::set_competence_parameters (), array (
				'courseid' => $courseid,
				'descriptorid' => $descriptorid,
				'value' => $value 
		) );
		
		static::check_can_access_course($courseid);
		
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
	
	/*
	 * Returns description of method parameters
	 * @return external_function_parameters
	 */
	public static function get_associated_content_parameters() {
		return new external_function_parameters ( array (
				'courseid' => new external_value ( PARAM_INT, 'id of course' ),
				'descriptorid' => new external_value ( PARAM_INT, 'id of descriptor' ) 
		) );
	}
	
	/**
	 * Get content
	 * 
	 * @param
	 *			int courseid
	 * @param
	 *			int subtopicid
	 * @return external_multiple_structure
	 */
	public static function get_associated_content($courseid, $descriptorid) {
		global $DB, $USER;
		
		if (empty ( $courseid ) || empty ( $descriptorid )) {
			throw new invalid_parameter_exception ( 'Parameter can not be empty' );
		}
		
		self::validate_parameters ( self::get_associated_content_parameters (), array (
				'descriptorid' => $descriptorid,
				'courseid' => $courseid 
		) );
		
		static::check_can_access_course($courseid);

		$courseSettings = block_exacomp_get_settings_by_course ( $courseid );
		$results = array ();
		
		$examples = $DB->get_records_sql ( '
				SELECT e.id, e.title, e.externalurl
				FROM {block_exacompexamples} e
				JOIN {block_exacompdescrexamp_mm} mm ON mm.descrid=? AND mm.exampid = e.id
				', array (
				$descriptorid 
		) );
		
		foreach ( $examples as $example ) {
			$result = new stdClass ();
			$result->type = "example";
			$result->title = $example->title;
			$result->link = ($url = block_exacomp_get_file_url($example, 'example_task')) ? $url : $example->externalurl;
			$result->contentid = $example->id;
			
			$results [] = $result;
		}
		
		$activities = $DB->get_records ( 'block_exacompcompactiv_mm', array (
				"compid" => $descriptorid,
				"comptype" => TYPE_DESCRIPTOR 
		) );
		
		foreach ( $activities as $activity ) {
			$module = $DB->get_record ( 'course_modules', array (
					'id' => $activity->activityid 
			) );
			if ($courseSettings->uses_activities && $module->module == 1) {
				
				$instance = $DB->get_field ( 'course_modules', 'instance', array (
						"id" => $activity->activityid,
						"course" => $courseid 
				) );
				$assign = $DB->get_record ( 'assign', array (
						'id' => $instance 
				) );
				
				if ($assign) {
					$result = new stdClass ();
					$result->type = "assign";
					$result->title = $assign->name;
					$result->link = "";
					$result->contentid = $assign->id;
					
					$results [] = $result;
				}
			} else if ($activity->eportfolioitem == 1) {
				$result = new stdClass ();
				$result->type = "exaport";
				$result->title = $DB->get_field ( 'block_exaportitem', 'name', array (
						'id' => $activity->activityid 
				) );
				$result->link = "";
				$result->contentid = $activity->activityid;
				
				$results [] = $result;
			}
		}
		return $results;
	}
	
	/**
	 * Returns desription of method return values
	 * 
	 * @return external_multiple_structure
	 */
	public static function get_associated_content_returns() {
		return new external_multiple_structure ( new external_single_structure ( array (
				'type' => new external_value ( PARAM_TEXT, 'type of content (exaport, assign, example)' ),
				'title' => new external_value ( PARAM_TEXT, 'title of content' ),
				'link' => new external_value ( PARAM_URL, 'link to external example' ),
				'contentid' => new external_value ( PARAM_INT, 'id of content' ) 
		) ) );
	}
	
	/**
	 * Returns description of method parameters
	 * 
	 * @return external_function_parameters
	 */
	public static function get_assign_information_parameters() {
		return new external_function_parameters ( array (
				'assignid' => new external_value ( PARAM_INT, 'id of assign' ) 
		) );
	}
	
	/**
	 * Get assign information
	 * 
	 * @param
	 *			int assignid
	 * @return external_multiple_structure
	 */
	public static function get_assign_information($assignid) {
		global $DB, $USER;
		
		if (empty ( $assignid )) {
			throw new invalid_parameter_exception ( 'Parameter can not be empty' );
		}
		
		self::validate_parameters ( self::get_assign_information_parameters (), array (
				'assignid' => $assignid 
		) );
		
		$cm = get_coursemodule_from_instance ( 'assign', $assignid, 0, false, MUST_EXIST );

		static::check_can_access_course($cm->course);
		
		$course = $DB->get_record ( 'course', array (
				'id' => $cm->course 
		), '*', MUST_EXIST );
		
		$context = context_module::instance ( $cm->id );
		
		$assign = new assign ( $context, $cm, $course );
		$instance = $assign->get_instance ();
		
		$submission = $assign->get_user_submission ( $USER->id, true );
		$onlinetextenabled = false;
		$onlinetext = "";
		
		$conditions = $DB->sql_compare_text ( "plugin" ) . " = 'onlinetext' AND " . $DB->sql_compare_text ( "name" ) . " = 'enabled' AND value=1 AND assignment =" . $assignid;
		if ($DB->get_record_select ( "assign_plugin_config", $conditions )) {
			$onlinetextenabled = true;
			$onlinetext = $DB->get_field ( "assignsubmission_onlinetext", "onlinetext", array (
					"assignment" => $assignid,
					"submission" => $submission->id 
			) );
		}
		
		$fileenabled = false;
		$file = "";
		$filename = "";
		
		$conditions = $DB->sql_compare_text ( "plugin" ) . " = 'file' AND " . $DB->sql_compare_text ( "name" ) . " = 'enabled' AND value=1 AND assignment =" . $assignid;
		$url = null;
		if ($DB->get_record_select ( "assign_plugin_config", $conditions )) {
			$fileenabled = true;
			
			$filesubmission = new assign_submission_file ( $assign, "submission" );
			$files = $filesubmission->get_files ( $submission, $USER );
			
			if ($files) {
				$file = reset ( $files );
				$filename = $file->get_filename ();
				$url = moodle_url::make_pluginfile_url ( $file->get_contextid (), $file->get_component (), $file->get_filearea (), $file->get_itemid (), $file->get_filepath (), $file->get_filename () )->out ();
				$url = str_replace ( "pluginfile.php", "webservice/pluginfile.php", $url );
			}
		}
		
		return array (
				"title" => $instance->name,
				"intro" => strip_tags ( $instance->intro ),
				"submissionstatus" => $submission->status,
				"deadline" => $instance->duedate,
				"onlinetextenabled" => $onlinetextenabled,
				"onlinetext" => $onlinetext,
				"fileenabled" => $fileenabled,
				"file" => $url,
				"filename" => $filename,
				"submissionenabled" => $assign->submissions_open (),
				"grade" => $assign->get_user_grade()->grade // TODO: parameter missing
		);
	}
	
	/**
	 * Returns desription of method return values
	 * 
	 * @return external_multiple_structure
	 */
	public static function get_assign_information_returns() {
		return new external_single_structure ( array (
				'title' => new external_value ( PARAM_TEXT, 'title of assign' ),
				'intro' => new external_value ( PARAM_RAW, 'introduction of assign' ),
				'submissionstatus' => new external_value ( PARAM_TEXT, 'submissionstatus' ),
				'deadline' => new external_value ( PARAM_INT, 'submission deadline' ),
				'onlinetextenabled' => new external_value ( PARAM_BOOL, 'true if text submission enabled' ),
				'onlinetext' => new external_value ( PARAM_TEXT, 'online text submission' ),
				'fileenabled' => new external_value ( PARAM_BOOL, 'true if file submission enabled' ),
				'file' => new external_value ( PARAM_URL, 'link to file' ),
				'filename' => new external_value ( PARAM_TEXT, 'filename' ),
				'submissionenabled' => new external_value ( PARAM_BOOL, 'tells if a submission is allowed' ),
				'grade' => new external_value ( PARAM_FLOAT, 'grade' ) 
		) );
	}
	
	/*
	 * Returns description of method parameters
	 * @return external_function_parameters
	 */
	public static function update_assign_submission_parameters() {
		return new external_function_parameters ( array (
				'assignid' => new external_value ( PARAM_INT, 'assignid' ),
				'onlinetext' => new external_value ( PARAM_TEXT, 'onlinetext submission' ),
				'filename' => new external_value ( PARAM_TEXT, 'onlinetext submission' ) 
		) );
	}
	
	/**
	 * Update assign submission:
	 * When this function is called with a filename, the file itself is already
	 * stored in the private user file area.
	 * Then it has to be moved to the
	 * user draft area, and then be submitted to the given assign.
	 *
	 * @param
	 *			int assignid
	 * @param
	 *			string onlinetext
	 * @param
	 *			string filename
	 * @return external_multiple_structure
	 */
	public static function update_assign_submission($assignid, $onlinetext, $filename) {
		global $DB, $USER;
		
		if (empty ( $assignid ) || (empty ( $onlinetext ) && empty ( $filename ))) {
			throw new invalid_parameter_exception ( 'Parameter can not be empty' );
		}
		
		self::validate_parameters ( self::update_assign_submission_parameters (), array (
				'assignid' => $assignid,
				'onlinetext' => $onlinetext,
				'filename' => $filename 
		) );
		
		$context = context_user::instance ( $USER->id );

		$draftid = null;
		if ($filename) {
			$fs = get_file_storage ();
			try {
				$old = $fs->get_file ( $context->id, "user", "private", 0, "/", $filename );
				$draftid = file_get_unused_draft_itemid ();
				
				$file_record = array (
						'contextid' => $context->id,
						'component' => 'user',
						'filearea' => 'draft',
						'itemid' => $draftid,
						'filepath' => '/',
						'filename' => $old->get_filename (),
						'timecreated' => time (),
						'timemodified' => time () 
				);
				$fs->create_file_from_storedfile ( $file_record, $old->get_id () );
			} catch ( Exception $e ) {
			}
		}
		
		$cm = get_coursemodule_from_instance ( 'assign', $assignid, 0, false, MUST_EXIST );
		static::check_can_access_course($cm->course);

		$course = $DB->get_record ( 'course', array (
				'id' => $cm->course 
		), '*', MUST_EXIST );
		
		$context = context_module::instance ( $cm->id );
		
		$assign = new assign ( $context, $cm, $course );
		
		$data = new stdClass ();
		if ($filename)
			$data->files_filemanager = $draftid;
		
		$conditions = $DB->sql_compare_text ( "plugin" ) . " = 'onlinetext' AND " . $DB->sql_compare_text ( "name" ) . " = 'enabled' AND value=1 AND assignment =" . $assignid;
		if ($onlinetext || $DB->get_record_select ( "assign_plugin_config", $conditions )) {
			$onlinetexteditor = array ();
			$onlinetexteditor ['text'] = $onlinetext;
			$onlinetexteditor ['format'] = 1;
			$data->onlinetext_editor = $onlinetexteditor;
		}
		$data->id = $cm->id;
		$data->action = "savesubmission";
		$notices = array ();
		
		global $CFG;
		require_once ($CFG->dirroot . '/mod/assign/lib.php');
		
		return array (
				"success" => $assign->save_submission ( $data, $notices ) 
		);
	}
	
	/**
	 * Returns desription of method return values
	 * 
	 * @return external_multiple_structure
	 */
	public static function update_assign_submission_returns() {
		return new external_single_structure ( array (
				'success' => new external_value ( PARAM_BOOL, 'status of success, either true (1) or false (0)' ) 
		) );
	}
	
	/**
	 * Returns description of method parameters
	 * 
	 * @return external_function_parameters
	 */
	public static function get_competence_by_id_parameters() {
		return new external_function_parameters ( array (
				'competenceid' => new external_value ( PARAM_INT, 'id of competence' ) 
		) );
	}
	
	/**
	 * Get competence information
	 * 
	 * @param
	 *			int assignid
	 * @return external_multiple_structure
	 */
	public static function get_competence_by_id($competenceid) {
		global $DB;
		
		if (empty ( $competenceid )) {
			throw new invalid_parameter_exception ( 'Parameter can not be empty' );
		}
		
		self::validate_parameters ( self::get_competence_by_id_parameters (), array (
				'competenceid' => $competenceid 
		) );
		
		return $DB->get_record ( "block_exacompdescriptors", array (
				"id" => $competenceid 
		) );
	}
	
	/**
	 * Returns desription of method return values
	 * 
	 * @return external_multiple_structure
	 */
	public static function get_competence_by_id_returns() {
		return new external_single_structure ( array (
				'title' => new external_value ( PARAM_TEXT, 'title of assign' ) 
		) );
	}
	
	/**
	 * Returns description of method parameters
	 * 
	 * @return external_function_parameters
	 */
	public static function get_topic_by_id_parameters() {
		return new external_function_parameters ( array (
				'topicid' => new external_value ( PARAM_INT, 'id of competence' ) 
		) );
	}
	
	/**
	 * Get competence information
	 * 
	 * @param
	 *			int assignid
	 * @return external_multiple_structure
	 */
	public static function get_topic_by_id($topicid) {
		global $DB, $USER;
		
		if (empty ( $topicid )) {
			throw new invalid_parameter_exception ( 'Parameter can not be empty' );
		}
		
		self::validate_parameters ( self::get_topic_by_id_parameters (), array (
				'topicid' => $topicid 
		) );
		
		return $DB->get_record ( "block_exacomptopics", array (
				"id" => $topicid 
		) );
	}
	
	/**
	 * Returns desription of method return values
	 * 
	 * @return external_multiple_structure
	 */
	public static function get_topic_by_id_returns() {
		return new external_single_structure ( array (
				'title' => new external_value ( PARAM_TEXT, 'title of topic' ) 
		) );
	}
	/**
	 * Returns description of method parameters
	 * 
	 * @return external_function_parameters
	 */
	public static function get_subtopics_by_topic_parameters() {
		return new external_function_parameters ( array (
				'topicid' => new external_value ( PARAM_INT, 'id of topic' ),
				'userid' => new external_value ( PARAM_INT, 'id of user' ) 
		) );
	}

	/**
	 * Get subtopics
	 *
	 * @param $topicid
	 * @param $userid
	 * @return array of subtopics
	 * @throws invalid_parameter_exception
	 */
	public static function get_subtopics_by_topic($topicid, $userid) {
		global $CFG, $DB, $USER;
		
		if (empty ( $topicid )) {
			throw new invalid_parameter_exception ( 'Parameter can not be empty' );
		}
		
		self::validate_parameters ( self::get_subtopics_by_topic_parameters (), array (
				'topicid' => $topicid,
				'userid' => $userid 
		) );
		
		if ($userid == 0)
			$userid = $USER->id;
		
		static::check_can_access_course_user($userid);

		$mycourses = enrol_get_users_courses ( $USER->id );
		// $mycourses = enrol_get_my_courses();
		$courses = array ();
		
		foreach ( $mycourses as $mycourse ) {
			$context = context_course::instance ( $mycourse->id );
			// $context = get_context_instance(CONTEXT_COURSE, $mycourse->id);
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
		
		// courses in $courses
		$topics = array ();
		foreach ( $courses as $course ) {
			$tree = block_exacomp_build_example_tree_desc ( $course ["courseid"] );
			foreach ( $tree as $subject ) {
				if ($subject->id == $topicid) {
					foreach ( $subject->subs as $topic ) {
						if (! array_key_exists ( $topic->id, $topics )) {
							$topics [$topic->id] = new stdClass ();
							$topics [$topic->id]->subtopicid = $topic->id;
							$topics [$topic->id]->title = $topic->title;
						}
					}
				}
			}
		}
		
		return $topics;
	}
	
	/**
	 * Returns desription of method return values
	 * 
	 * @return external_multiple_structure
	 */
	public static function get_subtopics_by_topic_returns() {
		return new external_multiple_structure ( new external_single_structure ( array (
				'subtopicid' => new external_value ( PARAM_INT, 'id of topic' ),
				'title' => new external_value ( PARAM_TEXT, 'title of topic' ) 
		) ) );
	}
	/**
	 * Returns description of method parameters
	 * 
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
		global $CFG, $DB, $USER;
		
		if (empty ( $subjectid ) || empty ( $courseid )) {
			throw new invalid_parameter_exception ( 'Parameter can not be empty' );
		}
		
		self::validate_parameters ( self::get_examples_for_subject_parameters (), array (
				'subjectid' => $subjectid,
				'courseid' => $courseid,
				'userid' => $userid 
		) );
		
		if ($userid == 0)
			$userid = $USER->id;
		
		static::check_can_access_course_user($courseid, $userid);
		
		$structure = array ();
		
		$topics = block_exacomp_get_topics_by_subject ( $courseid, $subjectid );
		foreach ( $topics as $topic ) {
			if (! array_key_exists ( $topic->id, $structure )) {
				$structure [$topic->id] = new stdClass ();
				$structure [$topic->id]->topicid = $topic->id;
				$structure [$topic->id]->title = $topic->title;
				$structure [$topic->id]->examples = array ();
			}
			$descriptors = block_exacomp_get_descriptors_by_topic ( $courseid, $topic->id, false, true );
			
			foreach ( $descriptors as $descriptor ) {
				$examples = $DB->get_records_sql ( "SELECT de.id as deid, e.id, e.title, e.externalurl,
						e.externalsolution, e.externaltask, e.completefile, e.description, e.creatorid
						FROM {" . block_exacomp::DB_EXAMPLES . "} e
						JOIN {" . block_exacomp::DB_DESCEXAMP . "} de ON e.id=de.exampid AND de.descrid=?
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
		
		self::validate_parameters ( self::get_example_by_id_parameters (), array (
				'exampleid' => $exampleid 
		) );

		// TODO: can access example?
		
		$example = $DB->get_record (block_exacomp::DB_EXAMPLES, array (
				'id' => $exampleid 
		) );
		$example->description = htmlentities ( $example->description );
		$example->hassubmissions = ($DB->get_records('block_exacompitemexample',array('exampleid'=>$exampleid))) ? true : false;
		
		$task = block_exacomp_get_file_url($example, 'example_task');
		if(isset($task))
			$example->task = $task->__toString();
		
		$solution = block_exacomp_get_file_url($example, 'example_solution');
		if(isset($solution))
			$example->solution = $solution->__toString();
		
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
				'externaltask' => new external_value ( PARAM_TEXT, 'externaltask(url/description) of example' ),
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
	 * @return list of descriptors
	 * @throws invalid_parameter_exception
	 */
	public static function get_descriptors_for_example($exampleid, $courseid, $userid) {
		global $DB, $USER;
		
		if ($userid == 0)
			$userid = $USER->id;
		
		self::validate_parameters ( self::get_descriptors_for_example_parameters (), array (
				'exampleid' => $exampleid,
				'courseid' => $courseid,
				'userid' => $userid 
		) );

		self::check_can_access_course_user($courseid, $userid);
		
		$descriptors_exam_mm = $DB->get_records (block_exacomp::DB_DESCEXAMP, array (
				'exampid' => $exampleid 
		) );
		
		$descriptors = array ();
		foreach ( $descriptors_exam_mm as $descriptor_mm ) {
			$descriptors [$descriptor_mm->descrid] = $DB->get_record (block_exacomp::DB_DESCRIPTORS, array (
					'id' => $descriptor_mm->descrid 
			) );
			
			$eval = $DB->get_record (block_exacomp::DB_COMPETENCIES, array (
					'userid' => $userid,
					'compid' => $descriptor_mm->descrid,
					'courseid' => $courseid,
					'role' => block_exacomp::ROLE_TEACHER 
			) );
			if ($eval) {
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
		
		self::validate_parameters ( self::get_user_role_parameters (), array () );
		
		$trainer = $DB->get_records ( 'block_exacompexternaltrainer', array (
				'trainerid' => $USER->id 
		) );
		if ($trainer)
			return array (
					"role" => 1 
			);
		
		$student = $DB->get_records ( 'block_exacompexternaltrainer', array (
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
		
		$students = $DB->get_records ( 'block_exacompexternaltrainer', array (
				'trainerid' => $USER->id 
		) );
		$returndata = array ();
		
		foreach ( $students as $student ) {
			$studentObject = $DB->get_record ( 'user', array (
					'id' => $student->studentid 
			) );
			$returndataObject = new stdClass ();
			$returndataObject->name = fullname ( $studentObject );
			$returndataObject->userid = $student->studentid;
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
				'name' => new external_value ( PARAM_TEXT, 'name of user' ) 
		)
		 ) );
	}
	/**
	 * Returns description of method parameters
	 * 
	 * @return external_function_parameters
	 */
	public static function get_item_example_status_parameters() {
		return new external_function_parameters ( array (
				'exampleid' => new external_value ( PARAM_INT, 'example id' ) 
		) );
	}
	
	/**
	 * Get status of example
	 * 
	 * @return array status
	 */
	public static function get_item_example_status($exampleid) {
		global $DB;
		
		self::validate_parameters ( self::get_item_example_status_parameters (), array (
				'exampleid' => $exampleid 
		) );

		// TODO: can access example?

		$entries = $DB->get_records ( 'block_exacompitemexample', array (
				'exampleid' => $exampleid 
		) );
		
		$current_timestamp = 0;
		$status = 0;
		foreach ( $entries as $entry ) {
			if ($current_timestamp < $entry->timecreated) {
				$current_timestamp = $entry->timecreated;
				$status = $entry->status;
			}
		}
		return array (
				"status" => $status 
		);
	}
	
	/**
	 * Returns desription of method return values
	 * 
	 * @return external_multiple_structure
	 */
	public static function get_item_example_status_returns() {
		return new external_single_structure ( array (
				'status' => new external_value ( PARAM_INT, 'status' ) 
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
		global $CFG, $DB, $USER;
		require_once ("$CFG->dirroot/lib/enrollib.php");
		
		self::validate_parameters ( self::get_subjects_for_user_parameters (), array (
				'userid' => $userid 
		) );
		
		if ($userid == 0)
			$userid = $USER->id;

		static::check_can_access_user($userid);
		
		$courses = static::get_courses ( $userid );
		
		$subjects_res = array ();
		foreach ( $courses as $course ) {
			$subjects = block_exacomp_get_subjects_by_course ( $course ["courseid"] );
			
			foreach ( $subjects as $subject ) {
				if (! array_key_exists ( $subject->id, $subjects_res )) {
					$elem = new stdClass ();
					$elem->subjectid = $subject->id;
					$elem->title = $subject->title;
					$elem->courseid = $course ["courseid"];
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
				'courseid' => new external_value ( PARAM_INT, 'id of course' ) 
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
		
		self::validate_parameters ( self::get_item_for_example_parameters (), array (
				'userid' => $userid,
				'itemid' => $itemid 
		) );
		
		static::check_can_access_user($userid);
		// TODO: can access item? can user access all items of that user

		$conditions = array (
				"id" => $itemid,
				"userid" => $userid 
		);
		$item = $DB->get_record ( "block_exaportitem", $conditions, 'id,userid,type,name,intro,url', MUST_EXIST );
		$itemexample = $DB->get_record ( "block_exacompitemexample", array (
				"itemid" => $itemid 
		) );
		
		$item->file = "";
		$item->isimage = false;
		$item->filename = "";
		$item->effort = strip_tags ( $item->intro );
		$item->teachervalue = isset ( $itemexample->teachervalue ) ? $itemexample->teachervalue : 0;
		$item->studentvalue = isset ( $itemexample->studentvalue ) ? $itemexample->studentvalue : 0;
		$item->status = isset ( $itemexample->status ) ? $itemexample->status : 0;
		
		if ($item->type == 'file') {
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
		global $DB, $USER;
		
		self::validate_parameters ( self::get_competencies_for_upload_parameters (), array (
				'userid' => $userid 
		) );

		if (!$userid) $userid = $USER->id;
		self::check_can_access_user($userid);

		$structure = array ();
		
		$courses = static::get_courses ( $userid );
		
		foreach ( $courses as $course ) {
			$tree = block_exacomp_get_competence_tree ( $course ["courseid"] );
			
			foreach ( $tree as $subject ) {
				$elem_sub = new stdClass ();
				$elem_sub->subjectid = $subject->id;
				$elem_sub->subjecttitle = $subject->title;
				$elem_sub->topics = array ();
				foreach ( $subject->subs as $topic ) {
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
	
		self::validate_parameters(self::submit_example_parameters(), array('title'=>$title,'exampleid'=>$exampleid,'url'=>$url,'effort'=>$effort,'filename'=>$filename,'studentcomment'=>$studentcomment,'studentvalue'=>$studentvalue,'itemid'=>$itemid,'courseid'=>$courseid));
	
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
		block_exacomp_set_user_example($USER->id, $exampleid, $courseid, block_exacomp::ROLE_STUDENT, $studentvalue);
		
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
		
		self::validate_parameters ( self::create_example_parameters (), array (
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
				$form->save_stored_file ( 'file', $context->id, 'user', 'private', 0, '/', $filename, true );
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
		$example->source = block_exacomp::EXAMPLE_SOURCE_USER;
		
		$id = $DB->insert_record (block_exacomp::DB_EXAMPLES, $example );
		
		$descriptors = explode ( ',', $comps );
		foreach ( $descriptors as $descriptor ) {
			$insert = new stdClass ();
			$insert->exampid = $id;
			$insert->descrid = $descriptor;
			$DB->insert_record (block_exacomp::DB_DESCEXAMP, $insert );
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
		
		self::validate_parameters ( self::grade_item_parameters (), array (
				'userid' => $userid,
				'value' => $value,
				'status' => $status,
				'comment' => $comment,
				'itemid' => $itemid,
				'comps' => $comps,
				'courseid' => $courseid 
		) );

		if (!$userid) $userid = $USER->id;
		self::check_can_access_user($userid);

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
		$descriptors_exam_mm = $DB->get_records (block_exacomp::DB_DESCEXAMP, array (
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
				$entry = $DB->get_record (block_exacomp::DB_COMPETENCIES, array (
						'userid' => $userid,
						'compid' => $descriptor,
						'courseid' => $courseid,
						'role' => block_exacomp::ROLE_TEACHER 
				) );
				
				if ($entry) {
					$entry->reviewerid = $USER->id;
					$entry->value = 1;
					$entry->timestamp = time ();
					$DB->update_record (block_exacomp::DB_COMPETENCIES, $entry );
				} else {
					$insert = new stdClass ();
					$insert->userid = $userid;
					$insert->compid = $descriptor;
					$insert->reviewerid = $USER->id;
					$insert->role = block_exacomp::ROLE_TEACHER;
					$insert->courseid = $courseid;
					$insert->value = 1;
					$insert->timestamp = time ();
					
					$DB->insert_record (block_exacomp::DB_COMPETENCIES, $insert );
				}
			}
		}
		
		// set negative graded competencies
		foreach ( $unset_descriptors as $descriptor ) {
			$entry = $DB->get_record (block_exacomp::DB_COMPETENCIES, array (
					'userid' => $userid,
					'compid' => $descriptor,
					'courseid' => $courseid,
					'role' => block_exacomp::ROLE_TEACHER 
			) );
			
			if ($entry) {
				$entry->reviewerid = $USER->id;
				$entry->value = 0;
				$entry->timestamp = time ();
				$DB->update_record (block_exacomp::DB_COMPETENCIES, $entry );
			} else {
				$insert = new stdClass ();
				$insert->userid = $userid;
				$insert->compid = $descriptor;
				$insert->reviewerid = $USER->id;
				$insert->role = block_exacomp::ROLE_TEACHER;
				$insert->courseid = $courseid;
				$insert->value = 0;
				$insert->timestamp = time ();
				
				$DB->insert_record (block_exacomp::DB_COMPETENCIES, $insert );
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
	public static function get_item_grading_parameters() {
		return new external_function_parameters ( array (
				'itemid' => new external_value ( PARAM_INT, 'id of item' ),
				'userid' => new external_value ( PARAM_INT, 'id of user' ) 
		) );
	}

	/**
	 * grade an item
	 *
	 * @param $itemid
	 * @param $userid
	 * @return mixed
	 * @throws invalid_parameter_exception
	 *
	 */
	public static function get_item_grading($itemid, $userid) {
		global $DB, $USER;
		
		if (empty ( $itemid )) {
			throw new invalid_parameter_exception ( 'Parameter can not be empty' );
		}
		
		self::validate_parameters ( self::get_item_grading_parameters (), array (
				'itemid' => $itemid,
				'userid' => $userid 
		) );
		
		if (!$userid) $userid = $USER->id;
		self::check_can_access_user($userid);

		$entry = $DB->get_record ( 'block_exacompitemexample', array (
				'itemid' => $itemid 
		) );
		$comments = $DB->get_records ( 'block_exaportitemcomm', array (
				'itemid' => $itemid 
		) );
		foreach ( $comments as $comment ) {
			// two comments per item, one from student and one from trainer
			if ($comment->userid != $userid) {
				$entry->comment = $comment;
			}
		}
		
		return $entry;
	}
	
	/**
	 * Returns desription of method return values
	 * 
	 * @return external_multiple_structure
	 */
	public static function get_item_grading_returns() {
		return new external_single_structure ( array (
				'teachervalue' => new external_value ( PARAM_INT, 'grading of teacher' ),
				'comment' => new external_value ( PARAM_TEXT, 'comment of teacher' ) 
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
		
		self::validate_parameters ( self::get_user_examples_parameters (), array () );
		
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

		self::validate_parameters ( self::get_user_profile_parameters (), array (
				'userid' => $userid 
		) );
		
		if (!$userid) $userid = $USER->id;
		self::check_can_access_user($userid);
		
		$user = $DB->get_record ( 'user', array (
				'id' => $userid 
		) );

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
					
					if (! empty ( $user->topics->teacher )) {
						if (isset ( $user->topics->teacher ) && isset ( $user->topics->teacher [$topic->id] )) {
							$topic_reached_competencies ++;
						}
					}
					
					$descriptors = block_exacomp_get_descriptors_by_topic ( $course ['courseid'], $topic->id, false, true );
					foreach ( $descriptors as $descriptor ) {
						if ($coursesettings->show_all_descriptors || ($coursesettings->uses_activities && isset ( $cm_mm->competencies [$descriptor->id] )))
							$topic_total_competencies ++;
						
						if (! empty ( $user->competencies->teacher )) {
							if (isset ( $user->competencies->teacher ) && isset ( $user->competencies->teacher [$descriptor->id] )) {
								$topic_reached_competencies ++;
							}
						}
						
						$examples = $DB->get_records_sql ( "SELECT de.id as deid, e.id, e.title, e.externalurl,
						e.externalsolution, e.externaltask, e.completefile, e.description, e.creatorid
						FROM {" . block_exacomp::DB_EXAMPLES . "} e
						JOIN {" . block_exacomp::DB_DESCEXAMP . "} de ON e.id=de.exampid AND de.descrid=? ", array (
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
		
		self::validate_parameters ( self::update_example_parameters (), array (
				'exampleid' => $exampleid,
				'name' => $name,
				'description' => $description,
				'task' => $task,
				'comps' => $comps,
				'filename' => $filename 
		) );

		// TODO: check can modify example

		$example_task = "";
		$type = ($filename != '') ? 'file' : 'url';
		if ( $type == 'file') {
			$context = context_user::instance ( $USER->id );
			$fs = get_file_storage ();
			
			if (! $fs->file_exists ( $context->id, 'user', 'private', 0, '/', $filename ))
				$form->save_stored_file ( 'file', $context->id, 'user', 'private', 0, '/', $filename, true );
			
			$pathnamehash = $fs->get_pathname_hash ( $context->id, 'user', 'private', 0, '/', $filename );
			$temp_task = new moodle_url ( $CFG->wwwroot . '/blocks/exacomp/example_upload.php', array (
					"action" => "serve",
					"c" => $context->id,
					"i" => $pathnamehash,
					"courseid" => 1 
			) );
			$example_task = $temp_task->out ( false );
		}
		
		$example = $DB->get_record (block_exacomp::DB_EXAMPLES, array (
				'id' => $exampleid 
		) );
		
		// insert into examples and example_desc
		$example->title = $name;
		$example->description = $description;
		$example->task = $task;
		if($type == 'file')
			$example->externaltask = $example_task;
		
		$DB->update_record (block_exacomp::DB_EXAMPLES, $example );
		
		if (! empty ( $comps )) {
			$DB->delete_records (block_exacomp::DB_DESCEXAMP, array (
					'exampid' => $exampleid 
			) );
			
			$descriptors = explode ( ',', $comps );
			foreach ( $descriptors as $descriptor ) {
				$insert = new stdClass ();
				$insert->exampid = $exampleid;
				$insert->descrid = $descriptor;
				$DB->insert_record (block_exacomp::DB_DESCEXAMP, $insert );
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
	
		self::validate_parameters ( self::delete_example_parameters (), array (
				'exampleid' => $exampleid
		) );
		
		$example = $DB->get_record('block_exacompexamples', array('id' => $exampleid, 'creatorid' => $USER->id));
		if(!$example)
			throw new invalid_parameter_exception ( 'Parameter can not be empty' );
				
		block_exacomp_delete_custom_example($exampleid);
		
		$items = $DB->get_records('block_exacompitemexample',array('exampleid' => $exampleid));
		foreach($items as $item) {
			$DB->delete_records('block_exacompitemexample', array('id'=>$item->id));
			self::delete_item($item->itemid);	
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
		
		self::validate_parameters ( self::get_competencies_by_topic_parameters (), array (
				'userid' => $userid,
				'topicid' => $topicid 
		) );

		if (!$userid) $userid = $USER->id;
		self::check_can_access_user($userid);

		$structure = array ();
		
		$courses = static::get_courses ( $userid );
		
		foreach ( $courses as $course ) {
			$tree = block_exacomp_get_competence_tree ( $course ["courseid"] );
			
			foreach ( $tree as $subject ) {
				foreach ( $subject->subs as $topic ) {
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
	
		self::validate_parameters ( self::dakora_get_topics_by_course_parameters (), array (
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
	
		self::validate_parameters ( self::dakora_get_all_topics_by_course_parameters (), array (
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
		self::validate_parameters ( self::dakora_get_descriptors_parameters (), array (
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
		self::validate_parameters ( self::dakora_get_all_descriptors_parameters (), array (
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
		global $DB, $USER;
		self::validate_parameters ( self::dakora_get_descriptor_children_parameters (), array (
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
					'examplesinwork' => new external_value (PARAM_INT, 'edited number of material')
			) ) ) ,
			'examples' => new external_multiple_structure ( new external_single_structure ( array (
					'exampleid' => new external_value ( PARAM_INT, 'id of example' ),
					'exampletitle' => new external_value ( PARAM_TEXT, 'title of example' ),
					'examplestate' => new external_value ( PARAM_INT, 'state of example, always 0 if for all students' )
			) ) ) ,
			'examplestotal' => new external_value (PARAM_INT, 'number of total examples'),
			'examplesvisible' => new external_value (PARAM_INT, 'number of visible examples'),
			'examplesinwork' => new external_value (PARAM_INT, 'number of examples in work')
		) ) ;
	}
	/**
	 * Returns description of method parameters
	 * 
	 * @return external_function_parameters
	 */
	public static function dakora_get_all_descriptor_children_parameters() {
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
	public static function dakora_get_all_descriptor_children($courseid, $descriptorid, $userid, $forall) {
		global $DB, $USER;
		self::validate_parameters ( self::dakora_get_all_descriptor_children_parameters (), array (
				'courseid' => $courseid,
				'descriptorid' => $descriptorid,
				'userid' => $userid,
				'forall' => $forall
		) );
		
		if($userid == 0 && !$forall)
			$userid = $USER->id;
			
		return static::get_descriptor_children($courseid, $descriptorid, $userid, $forall, 0, true);
		
	}
	
	/**
	 * Returns desription of method return values
	 * 
	 * @return external_multiple_structure
	 */
	public static function dakora_get_all_descriptor_children_returns() {
		return new external_single_structure ( array (
			'children' => new external_multiple_structure ( new external_single_structure ( array (
					'childid' => new external_value ( PARAM_INT, 'id of child' ),
					'childtitle' => new external_value ( PARAM_TEXT, 'title of child' ),
					'numbering' => new external_value ( PARAM_TEXT, 'numbering for child'),
					'teacherevaluation' => new external_value ( PARAM_INT, 'grading of child'),
					'studentevaluation' => new external_value ( PARAM_INT, 'self evaluation of child'),
					'hasmaterial' => new external_value (PARAM_BOOL, 'true or false if child has material'),
					'examplestotal' => new external_value (PARAM_INT, 'total number of material'),
					'examplesvisible' => new external_value (PARAM_INT, 'visible number of material'),
					'examplesinwork' => new external_value (PARAM_INT, 'edited number of material')
			) ) ) ,
			'examples' => new external_multiple_structure ( new external_single_structure ( array (
					'exampleid' => new external_value ( PARAM_INT, 'id of example' ),
					'exampletitle' => new external_value ( PARAM_TEXT, 'title of example' ),
					'examplestate' => new external_value ( PARAM_INT, 'state of example, always 0 if for all students' )
			) ) ),
			'examplestotal' => new external_value (PARAM_INT, 'number of total examples'),
			'examplesvisible' => new external_value (PARAM_INT, 'number of visible examples'),
			'examplesinwork' => new external_value (PARAM_INT, 'number of examples in work')
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
		self::validate_parameters ( self::dakora_get_examples_for_descriptor_parameters (), array (
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
		self::validate_parameters ( self::dakora_get_examples_for_descriptor_with_grading_parameters (), array (
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
		self::validate_parameters ( self::dakora_get_examples_for_descriptor_for_crosssubject_parameters (), array (
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
		self::validate_parameters ( self::dakora_get_examples_for_descriptor_for_crosssubject_with_grading_parameters (), array (
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
				'externalsolution' => new external_value ( PARAM_TEXT, 'solution(url/description) of example' ),
				'externaltask' => new external_value ( PARAM_TEXT, 'externaltask(url/description) of example' ),
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
		self::validate_parameters ( self::dakora_add_example_to_learning_calendar_parameters (), array (
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
			
		static::check_can_access_course_user($courseid, $creatorid);
		static::check_can_access_course_user($courseid, $userid);

		$example = $DB->get_record(block_exacomp::DB_EXAMPLES, array('id'=>$exampleid));
		
		// TODO: can access example
	
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
		
		self::validate_parameters ( self::dakora_get_descriptors_for_example_parameters (), array (
				'exampleid' => $exampleid,
				'courseid' => $courseid,
				'userid' => $userid,
				'forall' => $forall
		) );
		
		if($userid == 0 && !$forall)
			$userid = $USER->id;
		
		static::check_can_access_course_user($courseid, $userid);
			
		$non_visibilities = $DB->get_fieldset_select(block_exacomp::DB_DESCVISIBILITY,'descrid', 'courseid=? AND studentid=? AND visible=0', array($courseid, 0));
		
		if(!$forall)
			$non_visibilities_student = $DB->get_fieldset_select(block_exacomp::DB_DESCVISIBILITY,'descrid', 'courseid=? AND studentid=? AND visible=0', array($courseid, $userid));

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
		
		self::validate_parameters ( self::dakora_get_example_grading_parameters (), array (
				'exampleid' => $exampleid,
				'courseid' => $courseid,
				'userid' => $userid
		) );
		
		if ($userid == 0)
			$userid = $USER->id;
		
		static::check_can_access_course_user($courseid, $userid);
		// TODO: check example
				
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
		
		self::validate_parameters ( self::dakora_get_user_role_parameters (), array (
			) );

		$courses = static::get_courses($USER->id);
		
		foreach($courses as $course){
			$context = context_course::instance($course["courseid"]);
			
			$isTeacher = block_exacomp_is_teacher($context);
			
			if($isTeacher) {
				return array (
						"role" => 1 
				);
			}
		}
		
		return array (
				"role" => 2 
		);
			
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
		self::validate_parameters ( self::dakora_get_students_for_course_parameters (), array (
				'courseid'=>$courseid
			) );

		self::check_can_access_course($courseid);
		// TODO: only for teacher?
			
		$students = block_exacomp_get_students_by_course($courseid);
		
		foreach($students as $student){
			$student->studentid = $student->id;
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
		
		self::validate_parameters ( self::dakora_get_examples_pool_parameters (), array (
				'courseid'=>$courseid,
				'userid'=>$userid
			) );
			
		if($userid == 0)
			$userid = $USER->id;
			
		static::check_can_access_course_user($courseid, $userid);
				
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

		self::validate_parameters ( self::dakora_get_examples_trash_parameters (), array (
				'courseid'=>$courseid,
				'userid'=>$userid
			) );

		if($userid == 0)
			$userid = $USER->id;
		
		static::check_can_access_course_user($courseid, $userid);
				
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
		self::validate_parameters ( self::dakora_set_example_time_slot_parameters (), array (
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
	 * set example time slot
	 * 
	 * @param
	 *			
	 * @return list of descriptors
	 */
	public static function dakora_remove_example_from_schedule($scheduleid) {
		
		self::validate_parameters ( self::dakora_remove_example_from_schedule_parameters (), array (
				'scheduleid' => $scheduleid
			) );

		// TODO: check

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
		self::validate_parameters ( self::dakora_get_examples_for_time_slot_parameters (), array (
				'userid'=>$userid,
				'start'=>$start,
				'end'=>$end
			) );
			
		if($userid == 0)
			$userid = $USER->id;

		self::check_can_access_user($userid);
		
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
		self::validate_parameters ( self::dakora_get_cross_subjects_by_course_parameters (), array (
				'courseid'=>$courseid,
				'userid'=>$userid,
				'forall'=>$forall
			) );
			
		if($userid == 0 && !$forall)
			$userid = $USER->id;

		if ($forall) {
			self::check_can_access_course($courseid);
		} else {
			self::check_can_access_course_user($courseid, $userid);
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
					$cross_sub_students = $DB->get_fieldset_select(block_exacomp::DB_CROSSSTUD,'studentid', 'crosssubjid=?', array($cross_subject->id));
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
		self::validate_parameters ( self::dakora_get_descriptors_by_cross_subject_parameters (), array (
				'courseid' => $courseid,
				'crosssubjid'=>$crosssubjid,
				'userid'=>$userid,
				'forall' => $forall
			) );
			
		if($userid == 0 && $forall == false)
			$userid = $USER->id;
		
		static::check_can_access_course_user($courseid, $userid);
				
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
		self::validate_parameters ( self::dakora_get_all_descriptors_by_cross_subject_parameters (), array (
				'courseid' => $courseid,
				'crosssubjid'=>$crosssubjid,
				'userid'=>$userid,
				'forall' => $forall
			) );
			
		if($userid == 0 && $forall == false)
			$userid = $USER->id;
		
		static::check_can_access_course_user($courseid, $userid);
				
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
		self::validate_parameters ( self::dakora_get_descriptor_children_for_cross_subject_parameters (), array (
				'courseid' => $courseid,
				'descriptorid' => $descriptorid,
				'userid' => $userid,
				'forall' => $forall,
				'crosssubjid' => $crosssubjid
		) );
		
		if($userid == 0 && !$forall)
			$userid = $USER->id;
		
		static::check_can_access_course_user($courseid, $userid);
				
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
					'examplesinwork' => new external_value (PARAM_INT, 'edited number of material')
			) ) ) ,
			'examples' => new external_multiple_structure ( new external_single_structure ( array (
					'exampleid' => new external_value ( PARAM_INT, 'id of example' ),
					'exampletitle' => new external_value ( PARAM_TEXT, 'title of example' ),
					'examplestate' => new external_value ( PARAM_INT, 'state of example, always 0 if for all students' )
			) ) ),
			'examplestotal' => new external_value (PARAM_INT, 'number of total examples'),
			'examplesvisible' => new external_value (PARAM_INT, 'number of visible examples'),
			'examplesinwork' => new external_value (PARAM_INT, 'number of examples in work')
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
		global $DB, $USER;
		self::validate_parameters ( self::dakora_get_all_descriptor_children_for_cross_subject_parameters (), array (
				'courseid' => $courseid,
				'descriptorid' => $descriptorid,
				'userid' => $userid,
				'forall' => $forall,
				'crosssubjid' => $crosssubjid
		) );
		
		if($userid == 0 && !$forall)
			$userid = $USER->id;
			
		static::check_can_access_course_user($courseid, $userid);
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
					'examplesinwork' => new external_value (PARAM_INT, 'edited number of material')
			) ) ) ,
			'examples' => new external_multiple_structure ( new external_single_structure ( array (
					'exampleid' => new external_value ( PARAM_INT, 'id of example' ),
					'exampletitle' => new external_value ( PARAM_TEXT, 'title of example' ),
					'examplestate' => new external_value ( PARAM_INT, 'state of example, always 0 if for all students' )
			) ) ),
			'examplestotal' => new external_value (PARAM_INT, 'number of total examples'),
			'examplesvisible' => new external_value (PARAM_INT, 'number of visible examples'),
			'examplesinwork' => new external_value (PARAM_INT, 'number of examples in work')
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
	 */
	public static function dakora_get_user_fullname_parameters() {
		return new external_function_parameters ( array (
		) );
	}
	
	/**
	 * get children for descriptor in cross subject context
	 *
	 * @return array of user courses
	 */
	public static function dakora_get_user_fullname() {
		global $USER;
	
		return array("firstname" => $USER->firstname, "lastname" => $USER->lastname, "fullname" => fullname($USER));
	}
	
	/**
	 * Returns desription of method return values
	 *
	 * @return external_multiple_structure
	 */
	public static function dakora_get_user_fullname_returns() {
		return new external_single_structure ( array (
				'firstname' => new external_value ( PARAM_TEXT, 'User firstname' ),
				'lastname' => new external_value ( PARAM_TEXT, 'User lastname' ),
				'fullname' => new external_value ( PARAM_TEXT, 'User fullname')
		));
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
		self::validate_parameters ( self::dakora_set_competence_parameters (), array (
				'courseid'=>$courseid,
				'userid'=>$userid,
				'compid'=>$compid,
				'role'=>$role,
				'value'=>$value,
				'additionalinfo'=>$additional_info
		) );
		
		if($userid == 0 && $role == block_exacomp::ROLE_STUDENT)
			$userid = $USER->id;
		else if($userid == 0)
			throw new invalid_parameter_exception ( 'Userid can not be 0 for teacher grading' );
		
		static::check_can_access_course_user($courseid, $userid);
		
		if(block_exacomp_set_user_competence($userid, $compid, block_exacomp::TYPE_DESCRIPTOR, $courseid, $role, $value) == -1)
			throw new invalid_parameter_exception ( 'Not allowed' );
			
		if($role == block_exacomp::ROLE_TEACHER){
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
		self::validate_parameters ( self::dakora_get_pre_planning_storage_examples_parameters (), array (
				'courseid'=>$courseid
		) );
		
		$creatorid = $USER->id;
		self::check_can_access_course($courseid);
		
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
		self::validate_parameters ( self::dakora_get_pre_planning_storage_students_parameters (), array (
				'courseid'=>$courseid
		) );

		// TODO: check if is teacher?

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
		self::validate_parameters ( self::dakora_has_items_in_pre_planning_storage_parameters (), array (
				'courseid'=>$courseid
		) );
		
		$creatorid = $USER->id;

		self::check_can_access_course($courseid);
		
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
		self::validate_parameters ( self::dakora_empty_pre_planning_storage_parameters (), array (
				'courseid'=>$courseid
		) );
		
		$creatorid = $USER->id;
		self::check_can_access_course($courseid);
		
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
	 * @return examples
	 */
	public static function dakora_add_example_to_pre_planning_storage($courseid, $exampleid) {
		global $USER;
		self::validate_parameters ( self::dakora_add_example_to_pre_planning_storage_parameters (), array (
				'courseid'=>$courseid,
				'exampleid' => $exampleid
		) );
		
		$creatorid = $USER->id;

		self::check_can_access_course($courseid);
		// TODO: check example

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
		self::validate_parameters ( self::dakora_add_examples_to_students_schedule_parameters (), array (
				'courseid'=>$courseid,
				'examples' => $examples,
				'students' => $students
		) );
		
		static::check_can_access_course_user($courseid, $USER->id);
		
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
	
		self::validate_parameters(self::dakora_submit_example_parameters(), array('exampleid'=>$exampleid,'url'=>$url,'filename'=>$filename,'studentcomment'=>$studentcomment,'studentvalue'=>$studentvalue,'itemid'=>$itemid,'courseid'=>$courseid));
	
		if (!isset($type)) {
			$type = ($filename != '') ? 'file' : 'url';
		};

		// TODO: check courseid
	
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
	
			$itemid = $DB->insert_record("block_exaportitem", array('userid'=>$USER->id,'name'=>$exampletitle,'intro' => $exampletitle, 'url'=>$url, 'type'=>$type,'timemodified'=>time(),'categoryid'=>$subject_category->id,'teachervalue' => null, 'studentvalue' => null, 'courseid' => $courseid));
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
	
		block_exacomp_set_user_example($USER->id, $exampleid, $courseid, block_exacomp::ROLE_STUDENT, $studentvalue);
	
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
	 * @param int itemid (0 for new, >0 for existing)
	 * @return array of course subjects
	 */
	public static function dakora_grade_example($userid, $courseid, $exampleid, $examplevalue, $itemid, $itemvalue, $comment) {
		global $DB,$USER;
	
		self::validate_parameters(self::dakora_grade_example_parameters(), array('userid'=>$userid,'courseid'=>$courseid,'exampleid'=>$exampleid,'examplevalue'=>$examplevalue,'itemid'=>$itemid,'itemvalue'=>$itemvalue,'comment'=>$comment));
	
		static::check_can_access_course_user($courseid, $userid);
		// TODO: check example
		
		block_exacomp_set_user_example(($userid == 0) ? $USER->id : $userid, $exampleid, $courseid, ($userid == 0) ? block_exacomp::ROLE_STUDENT : block_exacomp::ROLE_TEACHER, $examplevalue,0,0,'self',$itemvalue);
	
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
		self::validate_parameters(self::dakora_get_descriptor_details_parameters(), 
			array('courseid'=>$courseid, 'descriptorid'=>$descriptorid, 'userid'=>$userid,'forall'=>$forall, 'crosssubjid'=>$crosssubjid));
			
		if(!$forall && $userid == 0)
			$userid = $USER->id;
			
		static::check_can_access_course_user($courseid, $userid);
			
		$descriptor = $DB->get_record(block_exacomp::DB_DESCRIPTORS, array('id'=>$descriptorid));
		$descriptor_topic_mm = $DB->get_record(block_exacomp::DB_DESCTOPICS, array('descrid'=>$descriptor->id));
		$descriptor->topicid = $descriptor_topic_mm->topicid;
		
		$descriptor_return = new stdClass();
		$descriptor_return->descriptorid = $descriptorid;
		$descriptor_return->descriptortitle = $descriptor->title;
		$descriptor_return->teacherevaluation = -1;
		$descriptor_return->additionalinfo = null;
		if(!$forall){
			$grading = $DB->get_record(block_exacomp::DB_COMPETENCIES, array('courseid'=>$courseid, 'userid'=>$userid, 'compid'=>$descriptorid, 'comptype'=>block_exacomp::TYPE_DESCRIPTOR, 'role'=>block_exacomp::ROLE_TEACHER));
			$descriptor_return->teacherevaluation = ($grading && $grading->value !== null) ? $grading->value : -1;
			$descriptor_return->additionalinfo = $grading->additionalinfo;
		}
		$descriptor_return->studentevaluation = -1;
		if(!$forall)
			$descriptor_return->studentevaluation = ($grading = $DB->get_record(block_exacomp::DB_COMPETENCIES, array('courseid'=>$courseid, 'userid'=>$userid, 'compid'=>$descriptorid, 'comptype'=>block_exacomp::TYPE_DESCRIPTOR, 'role'=>block_exacomp::ROLE_STUDENT)))? $grading->value:-1;
		
		$descriptor_return->numbering = block_exacomp_get_descriptor_numbering($descriptor);
		
		$descriptor_return->niveautitle = "";
		$descriptor_return->niveauid = 0;
		if($descriptor->niveauid){
			$niveau = $DB->get_record(block_exacomp::DB_NIVEAUS, array('id'=>$descriptor->niveauid));
			$descriptor_return->niveautitle = $niveau->title;
			$descriptor_return->niveauid = $niveau->id;
		}
		
		$childsandexamples = static::get_descriptor_children($courseid, $descriptorid, $userid, $forall, $crosssubjid, true);
		
		$descriptor_return->children = $childsandexamples->children;

		$descriptor_return->hasmaterial = true;
		if(empty($childsandexamples->examples))
			$descriptor_return->hasmaterial = false;
			
		$descriptor_example_statistic = static::get_descriptor_example_statistic($courseid, $userid, $descriptorid, $forall, $crosssubjid);
		$descriptor_return->examplestotal = $descriptor_example_statistic->total;
		$descriptor_return->examplesvisible = $descriptor_example_statistic->visible;
		$descriptor_return->examplesinwork = $descriptor_example_statistic->inwork;
		
		return $descriptor_return;
	}
	
	public static function dakora_get_descriptor_details_returns(){
		return new external_single_structure ( array (
			'descriptorid' => new external_value( PARAM_INT, 'id of descriptor'),
			'descriptortitle' => new external_value (PARAM_TEXT, 'title of descriptor'),
			'teacherevaluation'=> new external_value( PARAM_INT, 'teacher evaluation of descriptor'),
			'studentevaluation'=> new external_value( PARAM_INT, 'student evaluation of descriptor'),
			'additionalinfo'=> new external_value (PARAM_TEXT, 'additional grading for descriptor'),
			'numbering' => new external_value ( PARAM_TEXT, 'numbering'),
			'niveauid' => new external_value ( PARAM_INT, 'id of niveau'),
			'niveautitle' => new external_value ( PARAM_TEXT, 'title of niveau'),
			'hasmaterial' => new external_value (PARAM_BOOL, 'true or false if descriptor has material'),
			'children' => new external_multiple_structure ( new external_single_structure ( array (
					'childid' => new external_value ( PARAM_INT, 'id of child' ),
					'childtitle' => new external_value ( PARAM_TEXT, 'title of child' ),
					'numbering' => new external_value ( PARAM_TEXT, 'numbering for child'),
					'teacherevaluation' => new external_value ( PARAM_INT, 'grading of children'),
					'studentevaluation' => new external_value ( PARAM_INT, 'self evaluation of children'),
					'examplestotal' => new external_value (PARAM_INT, 'total number of material'),
					'examplesvisible' => new external_value (PARAM_INT, 'visible number of material'),
					'examplesinwork' => new external_value (PARAM_INT, 'edited number of material')
			) ) ),
			'examplestotal' => new external_value (PARAM_INT, 'total number of material'),
			'examplesvisible' => new external_value (PARAM_INT, 'visible number of material'),
			'examplesinwork' => new external_value (PARAM_INT, 'edited number of material')
		) ) ;
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
	
		self::validate_parameters ( self::dakora_get_example_information_parameters (), array (
				'courseid' => $courseid,
				'userid' => $userid,
				'exampleid' => $exampleid
		) );
	
		static::check_can_access_course_user($courseid, $userid);
		
		$example = $DB->get_record(block_exacomp::DB_EXAMPLES, array('id'=>$exampleid));
		if(!$example)
			throw new invalid_parameter_exception ( 'Example does not exist' );
	
		$itemInformation = block_exacomp_get_current_item_for_example($userid, $exampleid);
		$exampleEvaluation = $DB->get_record(block_exacomp::DB_EXAMPLEEVAL,array("studentid" => $userid, "courseid" => $courseid, "exampleid" => $exampleid));
	
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
					$data['file'] = ("{$CFG->wwwroot}/blocks/exaport/portfoliofile.php?access=view/id/" . $userid . "-" . $itemInformation->id. "&itemid=" . $itemInformation->id);
					$data['mimetype'] = $file->get_mimetype();
					$data['filename'] = $file->get_filename ();
				}
			}
	
			$data['studentcomment'] = '';
			$data['teachercomment'] = '';
			
			$itemcomments = $DB->get_records ( 'block_exaportitemcomm', array (
					'itemid' => $itemInformation->id
			), 'timemodified ASC', 'entry, userid', 0, 2 );
			if ($itemcomments) {
				foreach ( $itemcomments as $itemcomment ) {
					if ($userid == $itemcomment->userid) {
						$data['studentcomment'] = $itemcomment->entry;
					} else {
						$data['teachercomment'] = $itemcomment->entry;
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

		self::validate_parameters ( self::dakora_get_competence_profile_for_topic_parameters (), array (
				'courseid' => $courseid,
				'userid' => $userid,
				'topicid' => $topicid
		) );

		if ($userid == 0)
			$userid = $USER->id;

		static::check_can_access_course_user($courseid, $userid);

		$data = new stdClass();
	
		$topic = block_exacomp_get_topic_by_id($topicid);
		$user = $DB->get_record('user', array('id'=>$userid));
		$user = block_exacomp_get_user_information_by_course($user, $courseid);
		$scheme = block_exacomp_get_grading_scheme($courseid);
		
		$data->topictitle = $topic->title;
		$data->topicnumbering = block_exacomp_get_topic_numbering($topic);
		$data->descriptordata = array();
		
		$descriptors = block_exacomp_get_descriptors_by_topic($courseid, $topicid, false, true, true);
		foreach($descriptors as $descriptor){
			$data_content = new stdClass();
			$data_content->descriptorid = $descriptor->id;
			$niveau = $DB->get_record(block_exacomp::DB_NIVEAUS, array('id'=>$descriptor->niveauid));
			$data_content->lfstitle = $niveau->title;
			$lmdata = block_exacomp_calc_example_stat_for_profile($courseid, $descriptor, $user, $scheme, $niveau->title);
			$data_content->lfsgraphdata = $lmdata->dataobject;
			$data_content->totallmnumb = $lmdata->total;
			$data_content->inworklmnumb = $lmdata->inWork;
			$data_content->teacherevaluation = (isset($user->competencies->teacher[$descriptor->id]))?$user->competencies->teacher[$descriptor->id]:-1;
			$data_content->additionalinfo = $user->competencies->teacher_additional_grading[$descriptor->id];
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
		self::validate_parameters ( self::dakora_get_admin_grading_scheme_parameters (), array () );

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
	public static function dakora_get_comp_grid_for_example_parameters() {
		return new external_function_parameters ( array (
				'courseid' => new external_value ( PARAM_INT, 'id of course' ),
				'exampleid' => new external_value ( PARAM_INT, 'id of example' )
		) );
	}
	
	/**
	 * get example with all submission details and gradings
	 *
	 * @return
	 */
	public static function dakora_get_comp_grid_for_example($courseid, $exampleid) {
		global $DB;
		self::validate_parameters ( self::dakora_get_comp_grid_for_example_parameters (), array (
				'courseid' => $courseid,
				'exampleid' => $exampleid) 
			);

		static::check_can_access_course($courseid);
		// TODO: check example
			
		$data = new stdClass();
		$data->topics = array();
		
		$topics = block_exacomp_get_topics_by_course($courseid);
		foreach($topics as $topic){
			$topicdata = new stdClass();
			$topicdata->topicid = $topic->id;
			$topicdata->topictitle = $topic->title;
			$topicdata->topicnumbering = block_exacomp_get_topic_numbering($topic);
			$topicdata->niveaus = array();
			
			$descriptors = block_exacomp_get_descriptors_by_topic($courseid, $topic->id);
			$example_descriptors = block_exacomp_get_descriptor_mms_by_example($exampleid);
			
			foreach($descriptors as $descriptor){
				$niveau = $DB->get_record(block_exacomp::DB_NIVEAUS, array('id'=>$descriptor->niveauid));
				
				$niveaudata = new stdClass();
				$niveaudata->niveauid = $niveau->id;
				$niveaudata->niveautitle = $niveau->title;
				$niveaudata->association = 0;
				
				foreach($example_descriptors as $examp_desc){
					if($descriptor->id == $examp_desc->descrid){
						$niveaudata->association = 1;
						continue;
					}
					//check parent descriptor
					$example_descriptor = $DB->get_record(block_exacomp::DB_DESCRIPTORS, array('id'=>$examp_desc->descrid));
					if($example_descriptor->parentid == $descriptor->id){
						$niveaudata->association = 1;
						continue;
					}
				}
				
				$niveaudata->span = $niveau->span;
				$topicdata->span = $niveau->span;
				$topicdata->niveaus[$niveau->id] = $niveaudata;
			}
			
			$data->topics[$topic->id] = $topicdata;
		}
			
		return $data->topics;
	}
	
	/**
	 * Returns desription of method return values
	 *
	 * @return external_multiple_structure
	 */
	public static function dakora_get_comp_grid_for_example_returns() {
		return new external_multiple_structure ( new external_single_structure ( array (
			'topicid' => new external_value(PARAM_INT, 'id of topic'),
			'topictitle' => new external_value(PARAM_TEXT, 'title of topic'),
			'topicnumbering' => new external_value (PARAM_TEXT, 'numbering for topic'),
			'niveaus' => new external_multiple_structure ( new external_single_structure ( array (
					'niveauid' => new external_value( PARAM_INT, 'id of niveau'),
					'niveautitle' => new external_value ( PARAM_TEXT, 'title of niveau' ),
					'association' => new external_value ( PARAM_INT, 'association to example'),
					'span' => new external_value ( PARAM_INT, 'row spanning')
			) ) ),
			'span' => new external_value ( PARAM_INT, 'row spanning')
		) ) );
	}
	
	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
	public static function dakora_send_message_to_course_parameters() {
		return new external_function_parameters ( array (
				'message' => new external_value ( PARAM_TEXT, 'message' ),
				'courseid' => new external_value ( PARAM_INT, 'courseid' )
		) );
	}
	
	/**
	 * Add student submission to example.
	 *
	 * @param int itemid (0 for new, >0 for existing)
	 * @return array of course subjects
	 */
	public static function dakora_send_message_to_course($message, $courseid) {
		self::validate_parameters(self::dakora_send_message_to_course_parameters(), array('message'=>$message,'courseid'=>$courseid));

		static::check_can_access_course($courseid);

		block_exacomp_send_message_to_course($courseid, $message);
	
		return array("success"=>true);
	}
	
	/**
	 * Returns desription of method return values
	 *
	 * @return external_single_structure
	 */
	public static function dakora_send_message_to_course_returns() {
		return new external_single_structure ( array (
				'success' => new external_value ( PARAM_BOOL, 'status' )
		) );
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
		
		self::validate_parameters(self::dakora_create_blocking_event_parameters(), array('courseid'=>$courseid,'title'=>$title,
			'userid'=>$userid, 'preplanningstorage'=>$preplanningstorage));
	
		if($userid == 0 && !$preplanningstorage)
			$userid = $USER->id;
		
		static::check_can_access_course_user($courseid, $userid);
		
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
		
		self::validate_parameters(self::dakora_get_examples_by_descriptor_and_grading_parameters(), array('courseid'=>$courseid,
		'userid'=>$userid, 'descriptorid'=>$descriptorid, 'grading'=>$grading));
		
		if($userid == 0)
			$userid = $USER->id;
		
		static::check_can_access_course_user($courseid, $userid);
		
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
	public static function dakora_get_examples_by_descriptor_and_grading_for_crosssubject_parameters() {
		return new external_function_parameters ( array (
				'courseid' => new external_value (PARAM_INT, 'id of course'),
				'userid' => new external_value (PARAM_INT, 'id of user'),
				'descriptorid' => new external_value ( PARAM_TEXT, 'id of descriptor' ),
				'grading' => new external_value (PARAM_INT, 'grading value'),
				'crosssubjid'=> new external_value (PARAM_INT, 'id of crosssubjects')
		) );
	}
	
	/**
	 * Create a new blocking event
	 */
	public static function dakora_get_examples_by_descriptor_and_grading_for_crosssubject($courseid, $userid, $descriptorid, $grading, $crosssubjid) {
		global $USER;
		
		self::validate_parameters(self::dakora_get_examples_by_descriptor_and_grading_for_crosssubject_parameters(), array('courseid'=>$courseid,
		'userid'=>$userid, 'descriptorid'=>$descriptorid, 'grading'=>$grading, 'crosssubjid'=>$crosssubjid));
		
		if($userid == 0)
			$userid = $USER->id;
		
		static::check_can_access_course_user($courseid, $userid);
			
		$grading = $grading -1;
	
		$childsandexamples = static::get_descriptor_children($courseid, $descriptorid, $userid, 0, $crosssubjid);
		
		$examples_return = array();
		
		//parent descriptor
		$examples = static::dakora_get_examples_for_descriptor_for_crosssubject_with_grading($courseid, $descriptorid, $userid, false, $crosssubjid);
		
		foreach($examples as $example){
			if($example->teacherevaluation == $grading){
				if(!array_key_exists($example->exampleid, $examples_return))
					$examples_return[$example->exampleid] = $example;
			}
		}
		
		foreach($childsandexamples->children as $child){
			$examples = static::dakora_get_examples_for_descriptor_with_grading($courseid, $child->childid, $userid, false, $crosssubjid);
			
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
	public static function dakora_get_examples_by_descriptor_and_grading_for_crosssubject_returns() {
		return new external_multiple_structure ( new external_single_structure ( array (
			'exampleid' => new external_value(PARAM_INT, 'id of topic'),
			'exampletitle' => new external_value(PARAM_TEXT, 'title of topic')
		) ) );
	}
	
	/** 
	* helper function to use same code for 2 ws
	*/
	private static function get_descriptor_children($courseid, $descriptorid, $userid, $forall, $crosssubjid = 0, $show_all = false) {
		global $DB;
		
		if ($forall) {
			self::check_can_access_course($courseid);
		} else {
			self::check_can_access_course_user($courseid, $userid);
		}

		$coursesettings = block_exacomp_get_settings_by_course($courseid);
		
		$isTeacher = (static::dakora_get_user_role()->role == 1)? true : false;
		$showexamples = ($isTeacher)?true:$coursesettings->show_all_examples;
		
		$parent_descriptor = $DB->get_record(block_exacomp::DB_DESCRIPTORS, array('id'=>$descriptorid));
		$descriptor_topic_mm = $DB->get_record(block_exacomp::DB_DESCTOPICS, array('descrid'=>$parent_descriptor->id));
		$parent_descriptor->topicid = $descriptor_topic_mm->topicid;
		
		$children = block_exacomp_get_child_descriptors($parent_descriptor, $courseid, false, array(SHOW_ALL_TAXONOMIES), true, true, true);
		
		$non_visibilities = $DB->get_fieldset_select(block_exacomp::DB_DESCVISIBILITY,'descrid', 'courseid=? AND studentid=? AND visible=0', array($courseid, 0));
		
		if($crosssubjid > 0) {
			$crossdesc = $DB->get_fieldset_select(block_exacomp::DB_DESCCROSS, 'descrid', 'crosssubjid=?', array($crosssubjid));
		} else {
			$crossdesc = [];
		}
		
		if(!$forall) {
			$non_visibilities_student = $DB->get_fieldset_select(block_exacomp::DB_DESCVISIBILITY, 'descrid', 'courseid=? AND studentid=? AND visible=0', array($courseid, $userid));
		} else {
			$non_visibilities_student = [];
		}

		$children_return = array();
		foreach($children as $child){
			if($child->examples || $show_all){
				$child_return = new stdClass();
				$child_return->childid = $child->id;
				$child_return->childtitle = $child->title;
				$child_return->numbering = block_exacomp_get_descriptor_numbering($child);
				$child_return->teacherevaluation = -1;
				if(!$forall)
					$child_return->teacherevaluation = ($grading = $DB->get_record(block_exacomp::DB_COMPETENCIES, array('courseid'=>$courseid, 'userid'=>$userid, 'compid'=>$child->id, 'comptype'=>block_exacomp::TYPE_DESCRIPTOR, 'role'=>block_exacomp::ROLE_TEACHER)))? $grading->value:-1;
				$child_return->studentevaluation = -1;
				if(!$forall)
					$child_return->studentevaluation = ($grading = $DB->get_record(block_exacomp::DB_COMPETENCIES, array('courseid'=>$courseid, 'userid'=>$userid, 'compid'=>$child->id, 'comptype'=>block_exacomp::TYPE_DESCRIPTOR, 'role'=>block_exacomp::ROLE_STUDENT))) ? $grading->value:-1;
				
				if($show_all){
					$child_return->hasmaterial = ($child->examples)?true:false;
				}
				
				$result = static::get_descriptor_example_statistic($courseid, $userid, $child->id, $forall, $crosssubjid);
				$child_return->examplestotal = $result->total;
				$child_return->examplesvisible = $result->visible;
				$child_return->examplesinwork = $result->inwork;
				
				if(!in_array($child->id, $non_visibilities) && ((!$forall && !in_array($child->id, $non_visibilities_student))||$forall)){
					if($crosssubjid == 0 || in_array($child->id, $crossdesc) || in_array($descriptorid, $crossdesc))
						$children_return[] = $child_return;
				}
			}
		}
		
		$examples_return = array();

		if($crosssubjid == 0 || in_array($parent_descriptor->id, $crossdesc)){
			$parent_descriptor = block_exacomp_get_examples_for_descriptor($parent_descriptor, array(SHOW_ALL_TAXONOMIES), $showexamples, $courseid);
			
			$example_non_visibilities = $DB->get_fieldset_select(block_exacomp::DB_EXAMPVISIBILITY, 'exampleid', 'courseid=? AND studentid=? AND visible=0', array($courseid, 0));
			if(!$forall) {
				$example_non_visibilities_student = $DB->get_fieldset_select(block_exacomp::DB_EXAMPVISIBILITY, 'exampleid', 'courseid=? AND studentid=? AND visible=0', array($courseid, $userid));
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
		$return->examples = $examples_return;

		$descriptor_example_statistic = static::get_descriptor_example_statistic($courseid, $userid, $descriptorid, $forall, $crosssubjid);
		$return->examplestotal = $descriptor_example_statistic->total;
		$return->examplesvisible = $descriptor_example_statistic->visible;
		$return->examplesinwork = $descriptor_example_statistic->inwork;
		
		return $return;
	}
	
	private function dakora_get_topics_by_course_common($courseid, $only_associated){

		self::check_can_access_course($courseid);

		//TODO if added for 1 student -> mind visibility for this student
		$tree = block_exacomp_build_example_association_tree($courseid, array(), 0, 0, true);
		
		$topics_return = array();
		foreach($tree as $subject){
			foreach($subject->subs as $topic){
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
	
		
	private function dakora_get_descriptors_common($courseid, $topicid, $userid, $forall, $only_associated){
		global $DB;

		if ($forall) {
			self::check_can_access_course($courseid);
		} else {
			self::check_can_access_course_user($courseid, $userid);
		}

		$tree = block_exacomp_build_example_association_tree($courseid, array(), 0, 0, true);

		$non_visibilities = $DB->get_fieldset_select(block_exacomp::DB_DESCVISIBILITY,'descrid', 'courseid=? AND studentid=? AND visible=0', array($courseid, 0));
		
		if(!$forall)
			$non_visibilities_student = $DB->get_fieldset_select(block_exacomp::DB_DESCVISIBILITY,'descrid', 'courseid=? AND studentid=? AND visible=0', array($courseid, $userid));
		
		$descriptors_return = array();
		foreach($tree as $subject){
			foreach($subject->subs as $topic){
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
								$niveau = $DB->get_record(block_exacomp::DB_NIVEAUS, array('id'=>$descriptor->niveauid));
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
	
	private function dakora_get_descriptors_by_cross_subject_common($courseid, $crosssubjid, $userid, $forall, $only_associated){
		global $DB;
		
		$descriptors = block_exacomp_get_descriptors_for_cross_subject($courseid, $crosssubjid, true);
		
		$non_visibilities = $DB->get_fieldset_select(block_exacomp::DB_DESCVISIBILITY,'descrid', 'courseid=? AND studentid=? AND visible=0', array($courseid, 0));
		
		if(!$forall) {
			$non_visibilities_student = $DB->get_fieldset_select(block_exacomp::DB_DESCVISIBILITY, 'descrid', 'courseid=? AND studentid=? AND visible=0', array($courseid, $userid));
		} else {
			$non_visibilities_student = [];
		}
		
		$descriptors_return = array();
		foreach($descriptors as $descriptor){
			if(!in_array($descriptor->id, $non_visibilities) && ((!$forall && !in_array($descriptor->id, $non_visibilities_student))||$forall)){ 	//descriptor is visibile
				if($only_associated){
					$has_visible_examples = false;
					$has_children_with_visible_examples = false;
					
					$example_non_visibilities = $DB->get_fieldset_select(block_exacomp::DB_EXAMPVISIBILITY, 'exampleid', 'courseid=? AND studentid=? AND visible=0', array($courseid, 0));
						if(!$forall)
							$example_non_visibilities_student = $DB->get_fieldset_select(block_exacomp::DB_EXAMPVISIBILITY, 'exampleid', 'courseid=? AND studentid=? AND visible=0', array($courseid, $userid));
						
					
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
								$niveau = $DB->get_record(block_exacomp::DB_NIVEAUS, array('id'=>$descriptor->niveauid));
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
						$niveau = $DB->get_record(block_exacomp::DB_NIVEAUS, array('id'=>$descriptor->niveauid));
						$descriptor_return->niveautitle = substr(block_exacomp_get_descriptor_numbering($descriptor),0,1).": ".$niveau->title;
						$descriptor_return->niveauid = $niveau->id;
					}
					$descriptors_return[] = $descriptor_return;
				}
			}
		}
		
		return $descriptors_return;
	}
	
	private function dakora_get_examples_for_descriptor_common($courseid, $descriptorid, $userid, $forall, $crosssubjid=0){
		global $DB;
		
		if ($forall) {
			self::check_can_access_course($courseid);
		} else {
			self::check_can_access_course_user($courseid, $userid);
		}

		$descriptor = $DB->get_record(block_exacomp::DB_DESCRIPTORS, array('id'=>$descriptorid));
		$coursesettings = block_exacomp_get_settings_by_course($courseid);
		
		$isTeacher = (static::dakora_get_user_role()->role == 1)? true : false;
		$showexamples = ($isTeacher)?true:$coursesettings->show_all_examples;
		
		if($crosssubjid > 0){
			$cross_subject_descriptors = block_exacomp_get_cross_subject_descriptors($crosssubjid);
			if(!array_key_exists($descriptor->id, $cross_subject_descriptors))
				return array();
		}
		
		if($descriptor->parentid != 0){ //parent descriptor
			$descriptor_topic_mm = $DB->get_record(block_exacomp::DB_DESCTOPICS, array('descrid'=>$descriptor->id));
			$descriptor->topicid = $descriptor_topic_mm->topicid;
		
			$descriptor = block_exacomp_get_examples_for_descriptor($descriptor, array(SHOW_ALL_TAXONOMIES), $showexamples, $courseid);
		}else{ //child descriptor
			
			$parent_descriptor = $DB->get_record(block_exacomp::DB_DESCRIPTORS, array('id'=>$descriptor->parentid));
			$descriptor_topic_mm = $DB->get_record(block_exacomp::DB_DESCTOPICS, array('descrid'=>$parent_descriptor->id));
			$descriptor->topicid = $descriptor_topic_mm->topicid;
			$descriptor = block_exacomp_get_examples_for_descriptor($descriptor, array(SHOW_ALL_TAXONOMIES), $showexamples, $courseid);
			
		}
		
		$example_non_visibilities = $DB->get_fieldset_select(block_exacomp::DB_EXAMPVISIBILITY, 'exampleid', 'courseid=? AND studentid=? AND visible=0', array($courseid, 0));
		if(!$forall)
			$example_non_visibilities_student = $DB->get_fieldset_select(block_exacomp::DB_EXAMPVISIBILITY, 'exampleid', 'courseid=? AND studentid=? AND visible=0', array($courseid, $userid));
		
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
	private function get_descriptor_example_statistic($courseid, $userid, $descriptorid, $forall, $crosssubjid){
		$return = new stdClass();
		$return->total = 0;
		$return->visible = 0;
		$return->inwork = 0;
		
		if($forall) return $return;

		self::check_can_access_course_user($courseid, $userid);
		
		list($total, $gradings, $notEvaluated, $inWork,$totalGrade, $notInWork, $totalHidden) = block_exacomp_get_example_statistic_for_descriptor($courseid, $descriptorid, $userid, $crosssubjid);
		
		$return->total = $totalHidden;
		$return->visible = $total;
		$return->inwork = $inWork;
		return $return;
	}

	private static function check_can_access_course($courseid) {
		if (!can_access_course(g::$DB->get_record('course', ['id'=>$courseid]))) {
			throw new invalid_parameter_exception ( 'Not allowed to access this course' );
		}
	}

	private static function check_can_access_user($userid) {
		// can view myself
		if ($userid == g::$USER->id) {
			return;
		}

		// check external trainers
		$isTrainer = g::$DB->get_record ( 'block_exacompexternaltrainer', array (
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
	private static function check_can_access_course_user($courseid, $userid) {
		if (!can_access_course(g::$DB->get_record('course', ['id'=>$courseid]))) {
			throw new invalid_parameter_exception ( 'Not allowed to access this course' );
		}

		// can view myself
		if ($userid == g::$USER->id) {
			return;
		}

		// teacher can view other users
		// TODO: can he also view other teachers in that course?
		if (block_exacomp_is_teacher ( $courseid )) {
			$users = get_enrolled_users(block_exacomp_get_context_from_courseid($courseid));
			if (isset($users[$userid])) {
				return;
			}
		}

		throw new invalid_parameter_exception ( 'Not allowed to view other user' );
	}

	private static function check_can_access_example($exampleid) {

	}
}