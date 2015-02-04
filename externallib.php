<?php
require_once("$CFG->libdir/externallib.php");
require_once $CFG->dirroot . '/mod/assign/locallib.php';
require_once $CFG->dirroot . '/mod/assign/submission/file/locallib.php';
require_once $CFG->dirroot . '/lib/filelib.php';
require_once dirname(__FILE__)."/inc.php";

// DB COMPETENCE TYPE CONSTANTS
define('TYPE_DESCRIPTOR', 0);
define('TYPE_TOPIC', 1);

class block_exacomp_external extends external_api {

	/**
	 * Returns description of method parameters
	 * @return external_function_parameters
	 */
	public static function get_courses_parameters() {
		return new external_function_parameters(
				array(
						// no parameters
				)
		);
	}

	/**
	 * get courses
	 * @return array of user courses
	 */
	public static function get_courses() {
		global $CFG,$DB;
		require_once("$CFG->dirroot/lib/enrollib.php");

		$mycourses = enrol_get_my_courses();
		$courses = array();

		foreach($mycourses as $mycourse) {
			$context = context_course::instance($mycourse->id);
			//$context = get_context_instance(CONTEXT_COURSE, $mycourse->id);
			if($DB->record_exists("block_instances", array("blockname" => "exacomp", "parentcontextid" => $context->id))) {
				$course = array("courseid" => $mycourse->id,"fullname"=>$mycourse->fullname,"shortname"=>$mycourse->shortname);
				$courses[] = $course;
			}
		}

		return $courses;
	}

	/**
	 * Returns desription of method return values
	 * @return external_multiple_structure
	 */
	public static function get_courses_returns() {
		return new external_multiple_structure(
				new external_single_structure(
						array(
								'courseid' => new external_value(PARAM_INT, 'id of course'),
								'fullname' => new external_value(PARAM_TEXT, 'fullname of course'),
								'shortname' => new external_value(PARAM_RAW, 'shortname of course'),
						)
				)
		);
	}

	/**
	 * Returns description of method parameters
	 * @return external_function_parameters
	 */
	public static function get_subjects_parameters() {
		return new external_function_parameters(
				array('courseid' => new external_value(PARAM_INT, 'id of course'))
		);

	}

	/**
	 * Get subjects
	 * @param int courseid
	 * @return array of course subjects
	 */
	public static function get_subjects($courseid) {
		global $CFG,$DB;

		if (empty($courseid)) {
			throw new invalid_parameter_exception('Parameter can not be empty');
		}
		$params = self::validate_parameters(self::get_subjects_parameters(), array('courseid'=>$courseid));

		$subjects = $DB->get_records_sql('
				SELECT s.id as subjectid, s.title
				FROM {block_exacompschooltypes} s
				JOIN {block_exacompmdltype_mm} m ON m.stid = s.id AND m.courseid = ?
				GROUP BY s.id
				ORDER BY s.title
				', array($courseid));

		return $subjects;
	}

	/**
	 * Returns desription of method return values
	 * @return external_multiple_structure
	 */
	public static function get_subjects_returns() {
		return new external_multiple_structure(
				new external_single_structure(
						array(
								'subjectid' => new external_value(PARAM_INT, 'id of subject'),
								'title' => new external_value(PARAM_TEXT, 'title of subject')
						)
				)
		);
	}

	/**
	 * Returns description of method parameters
	 * @return external_function_parameters
	 */
	public static function get_topics_parameters() {
		return new external_function_parameters(
				array('subjectid' => new external_value(PARAM_INT, 'id of subject'),
						'courseid' => new external_value(PARAM_INT, 'id of course'))
		);
	}

	/**
	 * Get subjects
	 * @param int courseid
	 * @return array of course subjects
	 */
	public static function get_topics($subjectid, $courseid) {
		global $CFG,$DB;

		if (empty($subjectid) || empty($courseid)) {
			throw new invalid_parameter_exception('Parameter can not be empty');
		}

		$params = self::validate_parameters(self::get_topics_parameters(), array('subjectid'=>$subjectid,'courseid'=>$courseid));
		
		/*$returnval = array();
		$returnval[92] = new stdClass();
		$returnval[92]->title = "title";
		$returnval[92]->topicid=12;
		return $returnval;*/
		
		$array = $DB->get_records_sql('
				SELECT s.id as topicid, s.title
				FROM {block_exacompsubjects} s
				JOIN {block_exacomptopics} t ON t.subjid = s.id
				JOIN {block_exacompcoutopi_mm} ct ON ct.topicid = t.id AND ct.courseid = ?
				'.(block_exacomp_get_settings_by_course($courseid)->show_all_descriptors ? '' : '
						-- only show active ones
						JOIN {block_exacompdescrtopic_mm} topmm ON topmm.topicid=t.id
						JOIN {block_exacompdescriptors} d ON topmm.descrid=d.id
						JOIN {block_exacompcompactiv_mm} ca ON (d.id=ca.compid AND ca.comptype = '.TYPE_DESCRIPTOR.') OR (t.id=ca.compid AND ca.comptype = '.TYPE_TOPIC.')
						JOIN {course_modules} a ON ca.activityid=a.id AND a.course=ct.courseid
						').'
				WHERE s.stid = ?
				GROUP BY s.id
				ORDER BY s.title
				', array($courseid,$subjectid));
			
		return $array;
	}

	/**
	 * Returns desription of method return values
	 * @return external_multiple_structure
	 */
	public static function get_topics_returns() {
		return new external_multiple_structure(
				new external_single_structure(
						array(
								'topicid' => new external_value(PARAM_INT, 'id of topic'),
								'title' => new external_value(PARAM_TEXT, 'title of topic')
						)
				)
		);
	}

	/**
	 * Returns description of method parameters
	 * @return external_function_parameters
	 */
	public static function get_subtopics_parameters() {
		return new external_function_parameters(
				array(
						'courseid' => new external_value(PARAM_INT, 'id of course'),
						'topicid' => new external_value(PARAM_INT, 'id of topic')
				)
		);
	}

	/**
	 * Get subjects
	 * @param int courseid
	 * @param int topicid
	 * @return array of course subjects
	 */
	public static function get_subtopics($courseid, $topicid) {
		global $CFG,$DB,$USER;

		if (empty($topicid) || empty($courseid)) {
			throw new invalid_parameter_exception('Parameter can not be empty');
		}

		$params = self::validate_parameters(self::get_subtopics_parameters(), array('courseid'=>$courseid,'topicid'=>$topicid));

		$cats = $DB->get_records_menu('block_exacompcategories', array("lvl" => 4),"id,title","id,title");

		$competencies = array(
				"studentcomps"=>$DB->get_records('block_exacompcompuser',array("role"=>0,"courseid"=>$courseid,"userid"=>$USER->id, "comptype"=>TYPE_TOPIC),"","compid,userid,reviewerid,value"),
				"teachercomps"=>$DB->get_records('block_exacompcompuser',array("role"=>1,"courseid"=>$courseid,"userid"=>$USER->id, "comptype"=>TYPE_TOPIC),"","compid,userid,reviewerid,value"));

		$subtopics = $DB->get_records('block_exacomptopics',array('subjid' => $topicid),'catid','id as subtopicid, title, catid');

		$subtopics = $DB->get_records_sql('
				SELECT t.id as subtopicid, t.title, t.catid
				FROM {block_exacomptopics} t
				JOIN {block_exacompcoutopi_mm} ct ON ct.topicid = t.id AND t.subjid = ? AND ct.courseid = ?
				'.(block_exacomp_get_settings_by_course($courseid)->show_all_descriptors ? '' : '
						-- only show active ones
						JOIN {block_exacompdescrtopic_mm} topmm ON topmm.topicid=t.id
						JOIN {block_exacompdescriptors} d ON topmm.descrid=d.id
						JOIN {block_exacompcompactiv_mm} ca ON (d.id=ca.compid AND ca.comptype = '.TYPE_DESCRIPTOR.') OR (t.id=ca.compid AND ca.comptype = '.TYPE_TOPIC.')
						JOIN {course_modules} a ON ca.activityid=a.id AND a.course=ct.courseid
						').'
				GROUP BY t.id
				ORDER BY t.catid, t.title
				', array($topicid, $courseid));

		foreach($subtopics as $subtopic) {
			$subtopic->studentcomp = (array_key_exists($subtopic->subtopicid, $competencies['studentcomps'])) ? true : false;
			$subtopic->teachercomp = (array_key_exists($subtopic->subtopicid, $competencies['teachercomps'])) ? true : false;
			$subtopic->catid = $cats[$subtopic->catid];
		}


		return $subtopics;
	}

	/**
	 * Returns desription of method return values
	 * @return external_multiple_structure
	 */
	public static function get_subtopics_returns() {
		return new external_multiple_structure(
				new external_single_structure(
						array(
								'subtopicid' => new external_value(PARAM_INT, 'id of subtopic'),
								'title' => new external_value(PARAM_TEXT, 'title of subtopic'),
								'catid' => new external_value(PARAM_TEXT, 'category of subtopic'),
								'studentcomp' => new external_value(PARAM_BOOL, 'student self evaluation'),
								'teachercomp' => new external_value(PARAM_BOOL, 'teacher evaluation'),

						)
				)
		);
	}

	/**
	 * Returns description of method parameters
	 * @return external_function_parameters
	 */
	public static function set_subtopic_parameters() {
		return new external_function_parameters(
				array(
						'courseid' => new external_value(PARAM_INT, 'id of course'),
						'subtopicid' => new external_value(PARAM_INT, 'id of subtopic'),
						'value' => new external_value(PARAM_INT, 'evaluation value')
				)
		);
	}

	/**
	 * Set subtopic student evaluation
	 * @param int courseid
	 * @param int subtopicid
	 * @return status
	 */
	public static function set_subtopic($courseid, $subtopicid, $value) {
		global $DB,$USER;

		if (empty($courseid) || empty($subtopicid) || !isset($value)) {
			throw new invalid_parameter_exception('Parameter can not be empty');
		}

		$params = self::validate_parameters(self::set_subtopic_parameters(), array('subtopicid'=>$subtopicid,'value'=>$value,'courseid'=>$courseid));

		$transaction = $DB->start_delegated_transaction(); //If an exception is thrown in the below code, all DB queries in this code will be rollback.

		$DB->delete_records('block_exacompcompuser',array("userid"=>$USER->id,"role"=>0,"compid"=>$subtopicid,"courseid"=>$courseid, 'comptype'=>TYPE_TOPIC));
		if ($value > 0) {
			$DB->insert_record('block_exacompcompuser',array("userid"=>$USER->id,"role"=>0,"compid"=>$subtopicid,"courseid"=>$courseid,'comptype'=>TYPE_TOPIC, "reviewerid"=>$USER->id,"value"=>$value));
		}

		$transaction->allow_commit();

		return array("success" => true);

	}

	/**
	 * Returns desription of method return values
	 * @return external_multiple_structure
	 */
	public static function set_subtopic_returns() {
		return new external_single_structure(
				array(
						'success' => new external_value(PARAM_BOOL, 'status of success, either true (1) or false (0)'),
				)
		);
	}

	/* Returns description of method parameters
	 * @return external_function_parameters
	*/
	public static function get_competencies_parameters() {
		return new external_function_parameters(
				array(
						'courseid' => new external_value(PARAM_INT, 'id of course'),
						'subtopicid' => new external_value(PARAM_INT, 'id of subtopic')
				)
		);
	}

	/**
	 * Get competencies
	 * @param int courseid
	 * @param int subtopicid
	 * @return external_multiple_structure
	 */
	public static function get_competencies($courseid, $subtopicid) {
		global $DB,$USER;

		if (empty($courseid) || empty($subtopicid) ) {
			throw new invalid_parameter_exception('Parameter can not be empty');
		}

		$params = self::validate_parameters(self::get_competencies_parameters(), array('subtopicid'=>$subtopicid,'courseid'=>$courseid));

		$courseSettings = block_exacomp_get_settings_by_course($courseid);

		$descriptors = $DB->get_records_sql('
				SELECT d.id as descriptorid, d.title, desctopmm.topicid AS topicid
				FROM {block_exacompdescriptors} d
				JOIN {block_exacompdescrtopic_mm} desctopmm ON desctopmm.descrid=d.id AND desctopmm.topicid = ?
				'.(block_exacomp_get_settings_by_course($courseid)->show_all_descriptors ? '' : '
						-- only show active ones
						JOIN {block_exacompcompactiv_mm} ca ON (d.id=ca.compid AND ca.comptype = '.TYPE_DESCRIPTOR.')
						JOIN {course_modules} a ON ca.activityid=a.id AND a.course=?
						').'
				GROUP BY descriptorid
				ORDER BY d.sorting
				', array($subtopicid, $courseid));

		$studentEvaluation = $DB->get_records('block_exacompcompuser',array("courseid"=>$courseid,"userid"=>$USER->id,"role"=>0, "comptype"=>TYPE_DESCRIPTOR),'','compid');
		$teacherEvaluation = $DB->get_records('block_exacompcompuser',array("courseid"=>$courseid,"userid"=>$USER->id,"role"=>1, "comptype"=>TYPE_DESCRIPTOR),'','compid');

		foreach($descriptors as $descriptor) {
			$descriptor->studentcomp = (array_key_exists($descriptor->descriptorid, $studentEvaluation)) ? true : false;
			$descriptor->teachercomp = (array_key_exists($descriptor->descriptorid, $teacherEvaluation)) ? true : false;

			$descriptor->isexpandable = false;
			// if there are examples for a descriptor
			if($DB->record_exists('block_exacompdescrexamp_mm', array("descrid"=>$descriptor->descriptorid)))
				$descriptor->isexpandable = true;
			else {
				$activities = $DB->get_records('block_exacompcompactiv_mm', array("compid"=>$descriptor->descriptorid, "comptype"=>TYPE_DESCRIPTOR));
				
				foreach($activities as $activity) {
					$module = $DB->get_record('course_modules', array('id'=>$activity->activityid));
					//only assignments
					if($courseSettings->uses_activities && $module && $module->module == 1) {
						$descriptor->isexpandable = true;
						continue;
					} else if ($activity->eportfolioitem == 1 ){
						$descriptor->isexpandable = true;
					}
				}
			}
		}
		return $descriptors;
	}

	/**
	 * Returns desription of method return values
	 * @return external_multiple_structure
	 */
	public static function get_competencies_returns() {
		return new external_multiple_structure(
				new external_single_structure(
						array(
								'descriptorid' => new external_value(PARAM_INT, 'id of descriptor'),
								'title' => new external_value(PARAM_TEXT, 'title of descriptor'),
								'isexpandable' => new external_value(PARAM_BOOL, 'is expandable if there are associated examples or eportfolio items'),
								'studentcomp' => new external_value(PARAM_BOOL, 'student self evaluation'),
								'teachercomp' => new external_value(PARAM_BOOL, 'teacher evaluation')
						)
				)
		);
	}

	/**
	 * Returns description of method parameters
	 * @return external_function_parameters
	 */
	public static function set_competence_parameters() {
		return new external_function_parameters(
				array(
						'courseid' => new external_value(PARAM_INT, 'id of course'),
						'descriptorid' => new external_value(PARAM_INT, 'id of descriptor'),
						'value' => new external_value(PARAM_INT, 'evaluation value')
				)
		);
	}

	/**
	 * Set student evaluation
	 * @param int courseid
	 * @param int descriptorid
	 * @param int value
	 * @return status
	 */
	public static function set_competence($courseid, $descriptorid, $value) {
		global $DB,$USER;

		if (empty($courseid) || empty($descriptorid) || !isset($value)) {
			throw new invalid_parameter_exception('Parameter can not be empty');
		}

		$params = self::validate_parameters(self::set_competence_parameters(), array('courseid'=>$courseid,'descriptorid'=>$descriptorid,'value'=>$value));

		$transaction = $DB->start_delegated_transaction(); //If an exception is thrown in the below code, all DB queries in this code will be rollback.

		$DB->delete_records('block_exacompcompuser',array("userid"=>$USER->id,"role"=>0,"compid"=>$descriptorid,"courseid"=>$courseid, "comptype"=>TYPE_DESCRIPTOR));
		if ($value > 0) {
			$DB->insert_record('block_exacompcompuser',array("userid"=>$USER->id,"role"=>0,"compid"=>$descriptorid,"courseid"=>$courseid, "comptype"=>TYPE_DESCRIPTOR, "reviewerid"=>$USER->id,"value"=>$value));
		}

		$transaction->allow_commit();

		return array("success" => true);

	}

	/**
	 * Returns desription of method return values
	 * @return external_multiple_structure
	 */
	public static function set_competence_returns() {
		return new external_single_structure(
				array(
						'success' => new external_value(PARAM_BOOL, 'status of success, either true (1) or false (0)'),
				)
		);
	}

	/* Returns description of method parameters
	 * @return external_function_parameters
	*/
	public static function get_associated_content_parameters() {
		return new external_function_parameters(
				array(
						'courseid' => new external_value(PARAM_INT, 'id of course'),
						'descriptorid' => new external_value(PARAM_INT, 'id of descriptor')
				)
		);
	}

	/**
	 * Get content
	 * @param int courseid
	 * @param int subtopicid
	 * @return external_multiple_structure
	 */
	public static function get_associated_content($courseid, $descriptorid) {
		global $DB,$USER;

		if (empty($courseid) || empty($descriptorid) ) {
			throw new invalid_parameter_exception('Parameter can not be empty');
		}

		$params = self::validate_parameters(self::get_associated_content_parameters(), array('descriptorid'=>$descriptorid,'courseid'=>$courseid));

		$courseSettings = block_exacomp_get_settings_by_course($courseid);
		$results = array();

		$examples = $DB->get_records_sql('
				SELECT e.id, e.title, e.task, e.externalurl
				FROM {block_exacompexamples} e
				JOIN {block_exacompdescrexamp_mm} mm ON mm.descrid=? AND mm.exampid = e.id
				', array($descriptorid));

		foreach($examples as $example) {
			$result = new stdClass();
			$result->type = "example";
			$result->title = $example->title;
			$result->link = ($example->task) ? $example->task : $example->externalurl;
			$result->contentid = $example->id;

			$results[] = $result;
		}

		$activities = $DB->get_records('block_exacompcompactiv_mm', array("compid"=>$descriptorid, "comptype"=>TYPE_DESCRIPTOR));

		foreach($activities as $activity) {
			$module = $DB->get_record('course_modules', array('id'=>$activity->activityid));
			if($courseSettings->uses_activities && $module->module==1) {

				$instance = $DB->get_field('course_modules', 'instance', array("id"=>$activity->activityid,"course"=>$courseid));
				$assign = $DB->get_record('assign',array('id'=>$instance));

				if($assign) {
					$result = new stdClass();
					$result->type = "assign";
					$result->title = $assign->name;
					$result->link = "";
					$result->contentid = $assign->id;

					$results[] = $result;
				}
			} else if ($activity->eportfolioitem == 1 ){
				$result = new stdClass();
				$result->type = "exaport";
				$result->title = $DB->get_field('block_exaportitem', 'name', array('id'=>$activity->activityid));
				$result->link = "";
				$result->contentid = $activity->activityid;
					
				$results[] = $result;
			}
		}
		return $results;
	}

	/**
	 * Returns desription of method return values
	 * @return external_multiple_structure
	 */
	public static function get_associated_content_returns() {
		return new external_multiple_structure(
				new external_single_structure(
						array(
								'type' => new external_value(PARAM_TEXT, 'type of content (exaport, assign, example)'),
								'title' => new external_value(PARAM_TEXT, 'title of content'),
								'link' => new external_value(PARAM_URL, 'link to external example'),
								'contentid' => new external_value(PARAM_INT, 'id of content')
						)
				)
		);
	}

	/** Returns description of method parameters
	 * @return external_function_parameters
	 */
	public static function get_assign_information_parameters() {
		return new external_function_parameters(
				array(
						'assignid' => new external_value(PARAM_INT, 'id of assign')
				)
		);
	}

	/**
	 * Get assign information
	 * @param int assignid
	 * @return external_multiple_structure
	 */
	public static function get_assign_information($assignid) {
		global $DB,$USER;

		if (empty($assignid)) {
			throw new invalid_parameter_exception('Parameter can not be empty');
		}

		$params = self::validate_parameters(self::get_assign_information_parameters(), array('assignid'=>$assignid));

		$cm = get_coursemodule_from_instance('assign', $assignid, 0, false, MUST_EXIST);
		$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

		$context = context_module::instance($cm->id);

		$assign = new assign($context, $cm, $course);
		$instance = $assign->get_instance();

		$submission = $assign->get_user_submission($USER->id,true);
		$onlinetextenabled = false;
		$onlinetext = "";

		$conditions = $DB->sql_compare_text("plugin")." = 'onlinetext' AND ".$DB->sql_compare_text("name"). " = 'enabled' AND value=1 AND assignment =".$assignid;
		if($DB->get_record_select("assign_plugin_config",$conditions)) {
			$onlinetextenabled = true;
			$onlinetext = $DB->get_field("assignsubmission_onlinetext", "onlinetext", array("assignment"=>$assignid,"submission"=>$submission->id));
		}

		$fileenabled = false;
		$file = "";
		$filename = "";

		$conditions = $DB->sql_compare_text("plugin")." = 'file' AND ".$DB->sql_compare_text("name"). " = 'enabled' AND value=1 AND assignment =".$assignid;
		if($DB->get_record_select("assign_plugin_config",$conditions)) {
			$fileenabled = true;

			$filesubmission = new assign_submission_file($assign, "submission");
			$files = $filesubmission->get_files($submission, $USER);

			if($files) {
				$file = reset($files);
				$filename = $file->get_filename();
				$url = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename())->out();
				$url = str_replace("pluginfile.php", "webservice/pluginfile.php", $url);
			}
		}

		return array(
				"title" => $instance->name,
				"intro" => strip_tags($instance->intro),
				"submissionstatus" => $submission->status,
				"deadline" => $instance->duedate,
				"onlinetextenabled" => $onlinetextenabled,
				"onlinetext" => $onlinetext,
				"fileenabled" => $fileenabled,
				"file" => $url,
				"filename" => $filename,
				"submissionenabled" => $assign->submissions_open(),
				"grade" => $assign->get_user_grade()->grade);
	}

	/**
	 * Returns desription of method return values
	 * @return external_multiple_structure
	 */
	public static function get_assign_information_returns() {
		return new external_single_structure(
				array(
						'title' => new external_value(PARAM_TEXT, 'title of assign'),
						'intro' => new external_value(PARAM_RAW, 'introduction of assign'),
						'submissionstatus' => new external_value(PARAM_TEXT, 'submissionstatus'),
						'deadline' => new external_value(PARAM_INT, 'submission deadline'),
						'onlinetextenabled' => new external_value(PARAM_BOOL, 'true if text submission enabled'),
						'onlinetext' => new external_value(PARAM_TEXT, 'online text submission'),
						'fileenabled' => new external_value(PARAM_BOOL, 'true if file submission enabled'),
						'file' => new external_value(PARAM_URL, 'link to file'),
						'filename' => new external_value(PARAM_TEXT, 'filename'),
						'submissionenabled' => new external_value(PARAM_BOOL, 'tells if a submission is allowed'),
						'grade' => new external_value(PARAM_FLOAT, 'grade')
				)
		);
	}

	/* Returns description of method parameters
	 * @return external_function_parameters
	*/
	public static function update_assign_submission_parameters() {
		return new external_function_parameters(
				array(
						'assignid' => new external_value(PARAM_INT, 'assignid'),
						'onlinetext' => new external_value(PARAM_TEXT, 'onlinetext submission'),
						'filename' => new external_value(PARAM_TEXT, 'onlinetext submission')
				)
		);
	}

	/**
	 * Update assign submission:
	 * When this function is called with a filename, the file itself is already
	 * stored in the private user file area. Then it has to be moved to the
	 * user draft area, and then be submitted to the given assign.
	 *
	 * @param int assignid
	 * @param string onlinetext
	 * @param string filename
	 * @return external_multiple_structure
	 */
	public static function update_assign_submission($assignid,$onlinetext,$filename) {
		global $DB,$USER;

		if (empty($assignid) || (empty($onlinetext) && empty($filename)) ) {
			throw new invalid_parameter_exception('Parameter can not be empty');
		}

		$params = self::validate_parameters(self::update_assign_submission_parameters(), array('assignid'=>$assignid,'onlinetext'=>$onlinetext,'filename'=>$filename));

		$context = context_user::instance($USER->id);

		if($filename) {
			$fs = get_file_storage();
			try {
				$old = $fs->get_file($context->id, "user", "private", 0, "/", $filename);
				$draftid = file_get_unused_draft_itemid();

				$file_record = array('contextid'=>$context->id, 'component'=>'user', 'filearea'=>'draft',
						'itemid'=>$draftid, 'filepath'=>'/', 'filename'=>$old->get_filename(),
						'timecreated'=>time(), 'timemodified'=>time());
				$fs->create_file_from_storedfile($file_record, $old->get_id());
			} catch (Exception $e) {
			}

		}

		$cm = get_coursemodule_from_instance('assign', $assignid, 0, false, MUST_EXIST);
		$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

		$context = context_module::instance($cm->id);

		$assign = new assign($context, $cm, $course);
		
		$data = new stdClass();
		if($filename)
			$data->files_filemanager = $draftid;

		$conditions = $DB->sql_compare_text("plugin")." = 'onlinetext' AND ".$DB->sql_compare_text("name"). " = 'enabled' AND value=1 AND assignment =".$assignid;
		if($onlinetext || $DB->get_record_select("assign_plugin_config",$conditions)) {
			$onlinetexteditor = array();
			$onlinetexteditor['text'] = $onlinetext;
			$onlinetexteditor['format'] = 1;
			$data->onlinetext_editor = $onlinetexteditor;
		}
		$data->id = $cm->id;
		$data->action = "savesubmission";
		$notices = array();

		global $CFG;
		require_once($CFG->dirroot . '/mod/assign/lib.php');

		return array("success" => $assign->save_submission($data, $notices));
	}

	/**
	 * Returns desription of method return values
	 * @return external_multiple_structure
	 */
	public static function update_assign_submission_returns() {
		return new external_single_structure(
				array(
						'success' => new external_value(PARAM_BOOL, 'status of success, either true (1) or false (0)'),
				)
		);
	}


	/** Returns description of method parameters
	 * @return external_function_parameters
	 */
	public static function get_competence_by_id_parameters() {
		return new external_function_parameters(
				array(
						'competenceid' => new external_value(PARAM_INT, 'id of competence')
				)
		);
	}

	/**
	 * Get competence information
	 * @param int assignid
	 * @return external_multiple_structure
	 */
	public static function get_competence_by_id($competenceid) {
		global $DB,$USER;

		if (empty($competenceid)) {
			throw new invalid_parameter_exception('Parameter can not be empty');
		}

		$params = self::validate_parameters(self::get_competence_by_id_parameters(), array('competenceid'=>$competenceid));

		return $DB->get_record("block_exacompdescriptors", array("id"=>$competenceid));
	}

	/**
	 * Returns desription of method return values
	 * @return external_multiple_structure
	 */
	public static function get_competence_by_id_returns() {
		return new external_single_structure(
				array(
						'title' => new external_value(PARAM_TEXT, 'title of assign')
				)
		);
	}


	/** Returns description of method parameters
	 * @return external_function_parameters
	 */
	public static function get_topic_by_id_parameters() {
		return new external_function_parameters(
				array(
						'topicid' => new external_value(PARAM_INT, 'id of competence')
				)
		);
	}

	/**
	 * Get competence information
	 * @param int assignid
	 * @return external_multiple_structure
	 */
	public static function get_topic_by_id($topicid) {
		global $DB,$USER;

		if (empty($topicid)) {
			throw new invalid_parameter_exception('Parameter can not be empty');
		}

		$params = self::validate_parameters(self::get_topic_by_id_parameters(), array('topicid'=>$topicid));

		return $DB->get_record("block_exacomptopics", array("id"=>$topicid));
	}

	/**
	 * Returns desription of method return values
	 * @return external_multiple_structure
	 */
	public static function get_topic_by_id_returns() {
		return new external_single_structure(
				array(
						'title' => new external_value(PARAM_TEXT, 'title of topic')
				)
		);
	}
	/**
	 * Returns description of method parameters
	 * @return external_function_parameters
	 */
	public static function get_subtopics_by_topic_parameters() {
		return new external_function_parameters(
				array('topicid' => new external_value(PARAM_INT, 'id of topic'))
		);
	}
	/**
	 * Get subtopics
	 * @param int topicid
	 * @return array of subtopics
	 */
	public static function get_subtopics_by_topic($topicid) {
		global $CFG,$DB;

		if (empty($topicid)) {
			throw new invalid_parameter_exception('Parameter can not be empty');
		}

		$params = self::validate_parameters(self::get_subtopics_by_topic_parameters(), array('topicid'=>$topicid));
        
	    $mycourses = enrol_get_my_courses();
		$courses = array();

		foreach($mycourses as $mycourse) {
			$context = context_course::instance($mycourse->id);
			//$context = get_context_instance(CONTEXT_COURSE, $mycourse->id);
			if($DB->record_exists("block_instances", array("blockname" => "exacomp", "parentcontextid" => $context->id))) {
				$course = array("courseid" => $mycourse->id,"fullname"=>$mycourse->fullname,"shortname"=>$mycourse->shortname);
				$courses[] = $course;
			}
		}
		
		//courses in $courses
		$topics = array();
		foreach($courses as $course){
			$tree = block_exacomp_build_example_tree_desc($course["courseid"]);
		    foreach($tree as $subject){
		        if($subject->id == $topicid){
		            foreach($subject->subs as $topic){
		                if(!array_key_exists($topic->id, $topics)){
		                    $topics[$topic->id] = new stdClass();
		                    $topics[$topic->id]->subtopicid = $topic->id;
		                    $topics[$topic->id]->title = $topic->title;
		                }
		            }
		        }
		    }
		}
		
		return $topics;
	}

	/**
	 * Returns desription of method return values
	 * @return external_multiple_structure
	 */
	public static function get_subtopics_by_topic_returns() {
		return new external_multiple_structure(
				new external_single_structure(
						array(
								'subtopicid' => new external_value(PARAM_INT, 'id of topic'),
								'title' => new external_value(PARAM_TEXT, 'title of topic')
						)
				)
		);
	}
	/**
	 * Returns description of method parameters
	 * @return external_function_parameters
	 */
	public static function get_examples_by_subtopic_parameters() {
		return new external_function_parameters(
				array('subtopicid' => new external_value(PARAM_INT, 'id of subtopic'))
		);
	}
	/**
	 * Get examples
	 * @param int subtopicid
	 * @return array of examples
	 */
	public static function get_examples_by_subtopic($subtopicid) {
		global $CFG,$DB;

		if (empty($subtopicid)) {
			throw new invalid_parameter_exception('Parameter can not be empty');
		}

		$params = self::validate_parameters(self::get_examples_by_subtopic_parameters(), array('subtopicid'=>$subtopicid));
        
	    $mycourses = enrol_get_my_courses();
		$courses = array();

		foreach($mycourses as $mycourse) {
			$context = context_course::instance($mycourse->id);
			//$context = get_context_instance(CONTEXT_COURSE, $mycourse->id);
			if($DB->record_exists("block_instances", array("blockname" => "exacomp", "parentcontextid" => $context->id))) {
				$course = array("courseid" => $mycourse->id,"fullname"=>$mycourse->fullname,"shortname"=>$mycourse->shortname);
				$courses[] = $course;
			}
		}
		
		$examples = array();

		foreach($courses as $course){
    		$descriptors = block_exacomp_get_descriptors_by_topic($course["courseid"], $subtopicid);
    
    		foreach($descriptors as $descriptor){
        	    $examples = $DB->get_records_sql(
        				"SELECT de.id as deid, e.id, e.title, tax.title as tax, e.task, e.externalurl,
        				e.externalsolution, e.externaltask, e.solution, e.completefile, e.description, e.taxid, e.attachement, e.creatorid
        				FROM {" . DB_EXAMPLES . "} e
        				JOIN {" . DB_DESCEXAMP . "} de ON e.id=de.exampid AND de.descrid=?
        				LEFT JOIN {" . DB_TAXONOMIES . "} tax ON e.taxid=tax.id"
        				. " WHERE " 
        				. ((true) ? " 1=1 " : " e.creatorid > 0")
        				. ((in_array(SHOW_ALL_TAXONOMIES, $filteredtaxonomies)) ? "" : " AND e.taxid IN (".implode(",", $filteredtaxonomies) .")" )
        				, array($descriptor->id));
        	
        		foreach($examples as $example){
        		    if(!array_key_exists($example->id, $examples)){
            			$examples[$example->id] = new stdClass();
            			$examples[$example->id]->exampleid = $example->id;
            			$examples[$example->id]->title = $example->title;
            		}
        		}
    		}
		}
		
	    return $examples;
	}

	/**
	 * Returns desription of method return values
	 * @return external_multiple_structure
	 */
	public static function get_examples_by_subtopic_returns() {
		return new external_multiple_structure(
				new external_single_structure(
						array(
								'exampleid' => new external_value(PARAM_INT, 'id of topic'),
								'title' => new external_value(PARAM_TEXT, 'title of topic')
						)
				)
		);
	}

}