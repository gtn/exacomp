<?php
// DB TABLE NAMES
define('DB_SKILLS', 'block_exacompskills');
define('DB_NIVEAUS', 'block_exacompniveaus');
define('DB_TAXONOMIES', 'block_exacomptaxonomies');
define('DB_EXAMPLES', 'block_exacompexamples');
define('DB_EXAMPLEEVAL', 'block_exacompexameval');
define('DB_DESCRIPTORS', 'block_exacompdescriptors');
define('DB_DESCEXAMP', 'block_exacompdescrexamp_mm');
define('DB_EDULEVELS', 'block_exacompedulevels');
define('DB_SCHOOLTYPES', 'block_exacompschooltypes');
define('DB_SUBJECTS', 'block_exacompsubjects');
define('DB_TOPICS', 'block_exacomptopics');
define('DB_COURSETOPICS', 'block_exacompcoutopi_mm');
define('DB_DESCTOPICS', 'block_exacompdescrtopic_mm');
define('DB_CATEGORIES', 'block_exacompcategories');
define('DB_COMPETENCE_ACTIVITY', 'block_exacompcompactiv_mm');
define('DB_COMPETENCIES', 'block_exacompcompuser');
define('DB_COMPETENCIES_USER_MM', 'block_exacompcompuser_mm');
define('DB_SETTINGS', 'block_exacompsettings');
define('DB_MDLTYPES', 'block_exacompmdltype_mm');
define('DB_DESCBADGE', 'block_exacompdescbadge_mm');
define('DB_PROFILESETTINGS', 'block_exacompprofilesettings');

// ROLE CONSTANTS
define('ROLE_TEACHER', 1);
define('ROLE_STUDENT', 0);

// DB COMPETENCE TYPE CONSTANTS
define('TYPE_DESCRIPTOR', 0);
define('TYPE_TOPIC', 1);

// SETTINGS
define('SETTINGS_MAX_SCHEME', 10);
define('CUSTOM_EXAMPLE_SOURCE', 3);

if (block_exacomp_moodle_badges_enabled()) {
	require_once($CFG->libdir . '/badgeslib.php');
	require_once($CFG->dirroot . '/badges/lib/awardlib.php');
}

$version = get_config('exacomp','alternativedatamodel');
$usebadges = get_config('exacomp', 'usebadges');
$skillmanagement = get_config('exacomp', 'skillmanagement');
$xmlserverurl = get_config('exacomp', 'xmlserverurl');
$autotest = get_config('exacomp', 'autotest');
$testlimit = get_config('exacomp', 'testlimit');

define("LIS_SHOW_ALL_TOPICS",99999999);

/**
 *
 * Include all JavaScript files needed
 */
function block_exacomp_init_js_css(){
	global $PAGE, $CFG;
	$PAGE->requires->css('/blocks/exacomp/styles.css');
	$PAGE->requires->css('/blocks/exacomp/css/jquery-ui.css');
	$PAGE->requires->js('/blocks/exacomp/javascript/jquery.js', true);
	$PAGE->requires->js('/blocks/exacomp/javascript/jquery-ui.js', true);
	$PAGE->requires->js('/blocks/exacomp/javascript/exacomp.js', true);

	$scriptName = preg_replace('!\.[^\.]+$!', '', basename($_SERVER['PHP_SELF']));
	if (file_exists($CFG->dirroot.'/blocks/exacomp/css/'.$scriptName.'.css'))
		$PAGE->requires->css('/blocks/exacomp/css/'.$scriptName.'.css');
	if (file_exists($CFG->dirroot.'/blocks/exacomp/javascript/'.$scriptName.'.js'))
		$PAGE->requires->js('/blocks/exacomp/javascript/'.$scriptName.'.js', true);

}


/**
 * Gets one particular subject
 * @param int $subjectid
 */
function block_exacomp_get_subject_by_id($subjectid) {
	global $DB;
	return $DB->get_records(DB_SUBJECTS,array("id" => $subjectid),'','id, title, numb, \'subject\' as tabletype');
}
/**
 * Gets all subjects that are in use in a particular course. The method also checks
 * @param unknown_type $courseid
 * @return multitype:
 */
function block_exacomp_get_subjects_by_course($courseid, $showalldescriptors = false) {
	global $DB;

	if(!$showalldescriptors)
		$showalldescriptors = block_exacomp_get_settings_by_course($courseid)->show_all_descriptors;

	$sql = '
	SELECT DISTINCT s.id, s.title, s.stid, s.numb, \'subject\' as tabletype
	FROM {'.DB_SUBJECTS.'} s
	JOIN {'.DB_TOPICS.'} t ON t.subjid = s.id
	JOIN {'.DB_COURSETOPICS.'} ct ON ct.topicid = t.id AND ct.courseid = ?
	'.($showalldescriptors ? '' : '
			-- only show active ones
			JOIN {'.DB_DESCTOPICS.'} topmm ON topmm.topicid=t.id
			JOIN {'.DB_DESCRIPTORS.'} d ON topmm.descrid=d.id
			JOIN {'.DB_COMPETENCE_ACTIVITY.'} ca ON (d.id=ca.compid AND ca.comptype = '.TYPE_DESCRIPTOR.') OR (t.id=ca.compid AND ca.comptype = '.TYPE_TOPIC.')
			JOIN {course_modules} a ON ca.activityid=a.id AND a.course=ct.courseid
			').'
			ORDER BY id, title
			';

	return $DB->get_records_sql($sql, array($courseid));
}
/**
 *
 * Gets all available subjects
 */
function block_exacomp_get_all_subjects() {
	global $DB;
	return $DB->get_records(DB_SUBJECTS,array(),'','id, title, numb, \'subject\' as tabletype');
}
/**
 * This method is only used in the LIS version
 * @param int $courseid
 */
function block_exacomp_get_schooltypes_by_course($courseid) {
	global $DB;
	return $DB->get_records_sql('
			SELECT s.id, s.title
			FROM {'.DB_SCHOOLTYPES.'} s
			JOIN {'.DB_MDLTYPES.'} m ON m.stid = s.id AND m.courseid = ?
			GROUP BY s.id, s.title
			ORDER BY s.title
			', array($courseid));
}
/**
 *
 * This function is used for courseselection.php
 * -only subject according to selected schooltypes are returned
 * @param unknown_type $courseid
 */
function block_exacomp_get_subjects_for_schooltype($courseid){
	global $DB;
	$sql = 'SELECT sub.id FROM {'.DB_SUBJECTS.'} sub
	JOIN {'.DB_MDLTYPES.'} type ON sub.stid = type.stid
	WHERE type.courseid=?';

	return $DB->get_records_sql($sql, array($courseid));
}
/**
 * Gets all subjects that are used in a particular course.
 *
 * @param int $courseid optional, when 0 all subjects from all courses are returned
 * @param int $subjectid this parameter is only used to check if a subject is in use in a course
 *
 */
function block_exacomp_get_subjects($courseid = 0, $subjectid = null) {
	global $DB;

	if($courseid == 0) {
		$sql = 'SELECT s.id, s.title, s.numb, \'subject\' as tabletype
		FROM {'.DB_SUBJECTS.'} s
		JOIN {'.DB_TOPICS.'} t ON t.subjid = s.id
		GROUP BY s.id, s.title, s.numb, s.stid
		ORDER BY s.stid, s.sorting, s.stid
		';

		return $DB->get_records_sql($sql);
	} else if($subjectid != null) {
		$sql = 'SELECT s.id, s.title, s.numb, \'subject\' as tabletype
		FROM {'.DB_SUBJECTS.'} s
		JOIN {'.DB_TOPICS.'} t ON t.subjid = s.id
		WHERE s.id = ?
		GROUP BY s.id, s.title, s.numb, s.stid
		ORDER BY s.stid, s.sorting, s.title';

		return $DB->get_records_sql($sql,$subjectid);
	}

	$subjects = $DB->get_records_sql('
			SELECT s.id, s.title, s.stid, s.numb, \'subject\' as tabletype
			FROM {'.DB_SUBJECTS.'} s
			JOIN {'.DB_TOPICS.'} t ON t.subjid = s.id
			JOIN {'.DB_COURSETOPICS.'} ct ON ct.topicid = t.id AND ct.courseid = ?
			'.(block_exacomp_get_settings_by_course($courseid)->show_all_descriptors ? '' : '
					-- only show active ones
					JOIN {'.DB_DESCTOPICS.'} topmm ON topmm.topicid=t.id
					JOIN {'.DB_DESCRIPTORS.'} d ON topmm.descrid=d.id
					JOIN {'.DB_COMPETENCE_ACTIVITY.'} ca ON (d.id=ca.compid AND ca.comptype = '.TYPE_DESCRIPTOR.') OR (t.id=ca.compid AND ca.comptype = '.TYPE_TOPIC.')
					JOIN {course_modules} a ON ca.activityid=a.id AND a.course=ct.courseid
					').'
			GROUP BY id
			ORDER BY s.sorting, s.id, s.title
			', array($courseid));

	return $subjects;
}
/**
 * returns all topics from a course
 * @param int $courseid
 */
function block_exacomp_get_topics_by_course($courseid,$showalldescriptors = false) {
	return block_exacomp_get_topics_by_subject($courseid,0,$showalldescriptors);
}
/**
 * Gets all topics from a particular subject
 * @param int $courseid
 * @param int $subjectid
 */
function block_exacomp_get_topics_by_subject($courseid, $subjectid = 0, $showalldescriptors = false) {
	global $DB;

	if(!$showalldescriptors)
		$showalldescriptors = block_exacomp_get_settings_by_course($courseid)->show_all_descriptors;

	$sql = 'SELECT DISTINCT t.id, t.title, t.catid, t.sorting, t.subjid, t.ataxonomie, t.btaxonomie, t.ctaxonomie, t.requirement, t.benefit, t.knowledgecheck,cat.title as cattitle
	FROM {'.DB_TOPICS.'} t
	JOIN {'.DB_COURSETOPICS.'} ct ON ct.topicid = t.id AND ct.courseid = ? '.(($subjectid > 0) ? 'AND t.subjid = ? ': '')
	.($showalldescriptors ? '' : '
			-- only show active ones
			JOIN {'.DB_DESCTOPICS.'} topmm ON topmm.topicid=t.id
			JOIN {'.DB_DESCRIPTORS.'} d ON topmm.descrid=d.id
			JOIN {'.DB_COMPETENCE_ACTIVITY.'} da ON (d.id=da.compid AND da.comptype = '.TYPE_DESCRIPTOR.') OR (t.id=da.compid AND da.comptype = '.TYPE_TOPIC.')
			JOIN {course_modules} a ON da.activityid=a.id AND a.course=ct.courseid
			').'
			LEFT JOIN {'.DB_CATEGORIES.'} cat ON t.catid = cat.id
			ORDER BY t.catid, t.sorting, t.subjid
			';
	//GROUP By funktioniert nur mit allen feldern im select, aber nicht mit strings
	return $DB->get_records_sql($sql, array($courseid, $subjectid));
}
/**
 * Gets all topics
 */
function block_exacomp_get_all_topics($subjectid = null) {
	global $DB;

	$topics = $DB->get_records_sql('
			SELECT t.id, t.title, t.parentid, t.subjid, \'topic\' as tabletype, t.catid, cat.title as cat
			FROM {'.DB_SUBJECTS.'} s
			JOIN {'.DB_TOPICS.'} t ON t.subjid = s.id
			LEFT JOIN {'.DB_CATEGORIES.'} cat ON t.catid = cat.id
			'.($subjectid == null ? '' : '
					-- only show active ones
					WHERE s.id = ?
					').'
			ORDER BY t.catid, t.sorting, t.subjid
			', array($subjectid));

	return $topics;
}
/**
 *
 * Gets topic with particular id
 * @param  $topicid
 */
function block_exacomp_get_topic_by_id($topicid) {
	global $DB;

	$topics = $DB->get_records_sql('
			SELECT t.id, t.title, t.parentid, t.subjid, \'topic\' as tabletype, t.catid, cat.title as cat
			FROM {'.DB_TOPICS.'} t
			LEFT JOIN {'.DB_CATEGORIES.'} cat ON t.catid = cat.id
			WHERE t.id = ?
			ORDER BY t.sorting
			', array($topicid));

	return $topics;
}

/**
 * Checks if a competence is associated to any activity in a particular course
 *
 * @param int $compid
 * @param int $comptype
 * @param int $courseid
 * @return boolean
 */
function block_exacomp_check_activity_association($compid, $comptype, $courseid) {
	global $DB;

	if(!block_exacomp_get_settings_by_course($courseid)->uses_activities)
		return true;

	$cms = get_course_mods($courseid);

	foreach($cms as $cm) {
		if($DB->record_exists(DB_COMPETENCE_ACTIVITY, array("compid"=>$compid,"comptype"=>$comptype,"activityid"=>$cm->id)))
			return true;
	}

	return false;
}

/**
 * Deletes an uploaded example and all it's data base entries and from the file system
 * @param int $delete exampleid
 */
function block_exacomp_delete_custom_example($delete) {
	global $DB,$USER;

	$example = $DB->get_record(DB_EXAMPLES, array('id'=>$delete));
	if($example && $example->creatorid == $USER->id) {
		$DB->delete_records(DB_EXAMPLES, array('id' => $delete));
		$DB->delete_records(DB_DESCEXAMP, array('exampid' => $delete));
		$DB->delete_records(DB_EXAMPLEEVAL, array('exampleid' => $delete));

		$fs = get_file_storage();
		$fileinstance = $DB->get_record('files',array("userid"=>$example->creatorid,"itemid"=>$example->id),'*',IGNORE_MULTIPLE);
		if($fileinstance) {
			$file = $fs->get_file_instance($fileinstance);
			$file->delete();
		}
	}
}
/**
 * Set one competence for one user in one course
 *
 * @param int $userid
 * @param int $compid
 * @param int $comptype
 * @param int $courseid
 * @param int $role
 * @param int $value
 */
function block_exacomp_set_user_competence($userid, $compid, $comptype, $courseid, $role, $value) {
	global $DB,$USER;

	if($record = $DB->get_record(DB_COMPETENCIES, array("userid" => $userid, "compid" => $compid, "comptype" => $comptype, "courseid" => $courseid, "role" => $role))) {
		$record->value = $value;
		$record->timestamp = time();
		$DB->update_record(DB_COMPETENCIES, $record);
	} else {
		$DB->insert_record(DB_COMPETENCIES, array("userid" => $userid, "compid" => $compid, "comptype" => $comptype, "courseid" => $courseid, "role" => $role, "value" => $value, "reviewerid" => $USER->id, "timestamp" => time()));
	}
}

/**
 * Set one competence for one user for one activity in one course
 *
 * @param int $userid
 * @param int $compid
 * @param int $comptype
 * @param int $activityid
 * @param int $role
 * @param int $value
 */
function block_exacomp_set_user_competence_activity($userid, $compid, $comptype, $activityid,$role, $value) {
	global $DB,$USER;

	if($record = $DB->get_record(DB_COMPETENCIES_USER_MM, array("userid" => $userid, "compid" => $compid, "comptype" => $comptype, 'activityid'=>$activityid, "role" => $role))) {
		$record->value = $value;
		$record->timestamp = time();
		$DB->update_record(DB_COMPETENCIES_USER_MM, $record);
	} else {
		$DB->insert_record(DB_COMPETENCIES_USER_MM, array("userid" => $userid, "compid" => $compid, "comptype" => $comptype, 'activityid'=>$activityid, "role" => $role, "value" => $value, "reviewerid" => $USER->id, "timestamp" => time()));
	}
}

/**
 * Saves competence data submitted by the assign competencies form
 *
 * @param array $data
 * @param int $courseid
 * @param int $role
 * @param int $comptype
 * @param int $topicid
 */
function block_exacomp_save_competencies($data, $courseid, $role, $comptype, $topicid = null) {
	global $USER;
	$values = array();
	foreach ($data as $compidKey => $students) {
		if (!empty($data[$compidKey])) {
			foreach ($data[$compidKey] as $studentidKey => $evaluations) {
				if(is_array($evaluations)) {
					if(isset($evaluations['teacher']))
						$value = intval($evaluations['teacher']);
					else
						$value = intval($evaluations['student']);
				}
				$values[] =  array('user' => intval($studentidKey), 'compid' => intval($compidKey), 'value' => $value);
			}
		}
	}
	block_exacomp_reset_comp_data($courseid, $role, $comptype, (($role == ROLE_STUDENT)) ? $USER->id : false, $topicid);

	foreach ($values as $value)
		block_exacomp_set_user_competence($value['user'], $value['compid'], $comptype, $courseid, $role, $value['value']);
}
/**
 * Saves competence data submitted by the competence detail form
 *
 * @param array $data
 * @param int $courseid
 * @param int $role
 * @param int $comptype
 */
function block_exacomp_save_competencies_activities_detail($data, $courseid, $role, $comptype) {
	global $USER;
	$activityid = null;
	$values = array();
	foreach ($data as $compidKey => $students) {
		if (!empty($data[$compidKey])) {
			foreach ($data[$compidKey] as $studentidKey => $evaluations) {
				if(is_array($evaluations)) {
					if(isset($evaluations['teacher']))
						$value = intval($evaluations['teacher']);
					else
						$value = intval($evaluations['student']);
				}
				foreach($data[$compidKey][$studentidKey] as $evaluation => $activities){
					foreach($data[$compidKey][$studentidKey][$evaluation] as $activityKey => $empty){
						$activityid = $activityKey;
						$values[] =  array('user' => intval($studentidKey), 'compid' => intval($compidKey), 'value' => $value, 'activityid'=>intval($activityKey));
					}
				}
			}
		}
	}
	block_exacomp_reset_comp_activity_data($courseid, $role, $comptype, (($role == ROLE_STUDENT)) ? $USER->id : false, $activityid);

	foreach ($values as $value)
		block_exacomp_set_user_competence_activity($value['user'], $value['compid'], $comptype, $value['activityid'], $role, $value['value']);
}


/**
 * Reset comp data for one comptype in one course
 *
 * @param int $courseid
 * @param int $role
 * @param int $comptype
 * @param int $userid
 * @param int $topicid
 */
function block_exacomp_reset_comp_data($courseid, $role, $comptype, $userid = false, $topicid = null) {
	global $DB;
	if(!$topicid) {
		if($role == ROLE_TEACHER)
			$DB->delete_records(DB_COMPETENCIES, array("courseid" => $courseid, "role" => $role, "comptype" => $comptype));
		else
			$DB->delete_records(DB_COMPETENCIES, array("courseid" => $courseid, "role" => $role,  "comptype" => $comptype, "userid"=>$userid));
	} else {
		$sql = "
		DELETE FROM {block_exacompcompuser} c WHERE c.compid = ? AND ((c.compid IN
		(SELECT d.id FROM {block_exacompdescriptors} d, {block_exacompdescrtopic_mm} dt
		WHERE d.id = dt.descrid AND dt.topicid = ?) AND c.comptype = 0) OR (c.comp = ? AND c.comptype = 1))
		";
		$select = " courseid = ? AND role = ? AND COMPTYPE = ? AND ((compid IN
		(SELECT d.id FROM {block_exacompdescriptors} d, {block_exacompdescrtopic_mm} dt
		WHERE d.id = dt.descrid AND dt.topicid = ?) AND comptype = 0) OR (compid = ? AND comptype = 1))";
		if($userid)
			$select .= " AND userid = ?";
		$DB->delete_records_select("block_exacompcompuser", $select ,array($courseid, $role, $comptype, $topicid,$topicid, $userid));
	}
}

/**
 * Reset activity comp data for one comptype in one course
 *
 * @param int $courseid
 * @param int $role
 * @param int $comptype
 * @param int $userid
 */
function block_exacomp_reset_comp_activity_data($courseid, $role, $comptype, $userid = false, $activityid = null) {
	global $DB;
	
	$activities = block_exacomp_get_activities_by_course($courseid);
	
	if($role == ROLE_TEACHER){
		foreach($activities as $activity)
			$DB->delete_records(DB_COMPETENCIES_USER_MM, array("activityid" => $activity->id, "role" => $role, "comptype" => $comptype));
	}else{
		foreach($activities as $activity)
			$DB->delete_records(DB_COMPETENCIES_USER_MM, array("activityid" => $activity->id, "role" => $role,  "comptype" => $comptype, "userid"=>$userid));
	}
}

/**
 * Delete timestamp for exampleid
 */
function block_exacomp_delete_timefield($exampleid, $deletestart, $deleteent){
	global $USER;

	$updateid = $DB->get_field(DB_EXAMPLEEVAL, 'id', array('exampleid'=>$exampleid, 'studentid'=>$USER->id));
	$update = new stdClass();
	$update->id = $updateid;
	if($deletestart==1)
		$update->starttime = null;
	elseif($deleteend==1)
	$update->endtime = null;

	$DB->update_record(DB_EXAMPLEEVAL, $update);
}
/**
 * Saves example date from competence overview form
 *
 * @param array $data
 * @param int $courseid
 * @param int $role
 */
function block_exacomp_save_example_evaluation($data, $courseid, $role, $topicid = null) {
	global $DB,$USER, $version;
	$values = array();

	foreach($data as $exampleidKey => $students) {
		foreach($students as $studentidKey => $values) {
			$updateEvaluation = new stdClass();

			if ($role == ROLE_TEACHER) {
				$updateEvaluation->teacher_evaluation = intval($values['teacher']);
				$updateEvaluation->teacher_reviewerid = $USER->id;
			} else {
				if ($studentidKey != $USER->id)
					// student can only assess himself
					continue;
					
				if (!empty($values['starttime'])) {
					$date = new DateTime(clean_param($values['starttime'], PARAM_SEQUENCE));
					$starttime = $date->getTimestamp();
				}else{
					$starttime = null;
				}
					
				if (!empty($values['endtime'])) {
					$date = new DateTime(clean_param($values['endtime'], PARAM_SEQUENCE));
					$endtime = $date->getTimestamp();
				}else{
					$endtime = null;
				}
					
				$updateEvaluation->student_evaluation = isset($values['student']) ? intval($values['student']) : 0;
				$updateEvaluation->starttime = $starttime;
				$updateEvaluation->endtime = $endtime;
				$updateEvaluation->studypartner = ($version) ? 'self' : $values['studypartner'];
			}
			if($record = $DB->get_record(DB_EXAMPLEEVAL,array("studentid" => $studentidKey, "courseid" => $courseid, "exampleid" => $exampleidKey))) {
				//if teacher keep studenteval
				if($role == ROLE_TEACHER) {
					$record->teacher_evaluation = $updateEvaluation->teacher_evaluation;
					$record->teacher_reviewerid = $updateEvaluation->teacher_reviewerid;
					$DB->update_record(DB_EXAMPLEEVAL,$record);
				} else {
					//if student keep teachereval
					$updateEvaluation->teacher_evaluation = $record->teacher_evaluation;
					$updateEvaluation->teacher_reviewerid = $record->teacher_reviewerid;
					$updateEvaluation->id = $record->id;
					$DB->update_record(DB_EXAMPLEEVAL,$updateEvaluation);
				}
			}
			else {
				$updateEvaluation->courseid = $courseid;
				$updateEvaluation->exampleid = $exampleidKey;
				$updateEvaluation->studentid = $studentidKey;

				$DB->insert_record(DB_EXAMPLEEVAL, $updateEvaluation);
			}

		}
	}
}
/**
 * Gets settings for the current course
 * @param int$courseid
 */
function block_exacomp_get_settings_by_course($courseid = 0) {
	global $DB, $COURSE, $version;

	if (!$courseid)
		$courseid = $COURSE->id;

	$settings = $DB->get_record(DB_SETTINGS, array("courseid" => $courseid));

	if (empty($settings)) $settings = new stdClass;
	if (empty($settings->grading)) $settings->grading = 1;
	if (!isset($settings->uses_activities)) $settings->uses_activities = ($version)? 0 : 1;
	if (!isset($settings->show_all_examples)) $settings->show_all_examples = 0;
	if (!isset($settings->usedetailpage)) $settings->usedetailpage = 0;
	if (!$settings->uses_activities) $settings->show_all_descriptors = 1;
	elseif (!isset($settings->show_all_descriptors)) $settings->show_all_descriptors = 0;
	
	return $settings;
}
/**
 * Returns a list of descriptors from a particular course
 *
 * @param $courseid
 * @param $onlywithactivitys - to select only descriptors assigned to activities
 */
function block_exacomp_get_descritors_list($courseid, $onlywithactivitys = 0) {
	global $DB;

	$query = 'SELECT t.id as topdescrid, d.id,d.title,tp.title as topic,tp.id as topicid, s.title as subject,s.id as
	subjectid,d.niveauid
	FROM {'.DB_DESCRIPTORS.'} d,
	{'.DB_COURSETOPICS.'} c,
	{'.DB_DESCTOPICS.'} t,
	{'.DB_TOPICS.'} tp,
	{'.DB_SUBJECTS.'} s
	WHERE d.id=t.descrid AND t.topicid = c.topicid AND t.topicid=tp.id AND tp.subjid = s.id AND c.courseid = ?';

	if ($onlywithactivitys==1){
		$descr=block_exacomp_get_descriptors($courseid, block_exacomp_get_settings_by_course($courseid)->show_all_descriptors);
		if ($descr=="") $descr=0;
		$query.=" AND d.id IN (".$descr.")";
	}
	$query.= " ORDER BY s.title,tp.title,d.sorting";
	$descriptors = $DB->get_records_sql($query, array($courseid));

	if (!$descriptors) {
		$descriptors = array();
	}

	return $descriptors;
}
/**
 *
 * returns all descriptors
 * @param $courseid if course id =0 all possible descriptors are returned
 */
function block_exacomp_get_descriptors($courseid = 0, $showalldescriptors = false, $subjectid = 0) {
	global $DB;
	
	if(!$showalldescriptors)
		$showalldescriptors = block_exacomp_get_settings_by_course($courseid)->show_all_descriptors;
	
	$sql = '(SELECT desctopmm.id as u_id, d.id as id, d.title, d.niveauid, t.id AS topicid, \'descriptor\' as tabletype '
	.'FROM {'.DB_TOPICS.'} t '
	.(($courseid>0)?'JOIN {'.DB_COURSETOPICS.'} topmm ON topmm.topicid=t.id AND topmm.courseid=? ' . (($subjectid > 0) ? ' AND t.subjid = '.$subjectid.' ' : '') :'')
	.'JOIN {'.DB_DESCTOPICS.'} desctopmm ON desctopmm.topicid=t.id '
	.'JOIN {'.DB_DESCRIPTORS.'} d ON desctopmm.descrid=d.id '
	.($showalldescriptors ? '' : '
			JOIN {'.DB_COMPETENCE_ACTIVITY.'} da ON d.id=da.compid AND da.comptype='.TYPE_DESCRIPTOR.'
			JOIN {course_modules} a ON da.activityid=a.id '.(($courseid>0)?'AND a.course=?':'')).')';

	$descriptors = $DB->get_records_sql($sql, array($courseid, $courseid, $courseid));

	return $descriptors;
}
function block_exacomp_get_descriptors_by_topic($courseid, $topicid, $showalldescriptors = false) {
	global $DB;
	
	if(!$showalldescriptors)
		$showalldescriptors = block_exacomp_get_settings_by_course($courseid)->show_all_descriptors;
	
	$sql = '(SELECT DISTINCT desctopmm.id as u_id, d.id as id, d.title, d.niveauid, t.id AS topicid, \'descriptor\' as tabletype '
	.'FROM {'.DB_TOPICS.'} t JOIN {'.DB_COURSETOPICS.'} topmm ON topmm.topicid=t.id AND topmm.courseid=? ' . (($topicid > 0) ? ' AND t.id = '.$topicid.' ' : '')
	.'JOIN {'.DB_DESCTOPICS.'} desctopmm ON desctopmm.topicid=t.id '
	.'JOIN {'.DB_DESCRIPTORS.'} d ON desctopmm.descrid=d.id '
	.($showalldescriptors ? '' : '
			JOIN {'.DB_COMPETENCE_ACTIVITY.'} da ON d.id=da.compid AND da.comptype='.TYPE_DESCRIPTOR.'
			JOIN {course_modules} a ON da.activityid=a.id '.(($courseid>0)?'AND a.course=?':'')).')';
	
	$descriptors = $DB->get_records_sql($sql, array($courseid, $courseid));
	
	return $descriptors;
}
function block_exacomp_get_descriptors_by_subject($subjectid,$niveaus = true) {
	global $DB;

	$sql = "SELECT d.*, dt.topicid, t.title as topic FROM {".DB_DESCRIPTORS."} d, {".DB_DESCTOPICS."} dt, {".DB_TOPICS."} t
	WHERE d.id=dt.descrid AND dt.topicid IN (SELECT id FROM {".DB_TOPICS."} WHERE subjid=?)";
	if($niveaus) $sql .= " AND d.niveauid > 0";
	$sql .= " AND dt.topicid = t.id order by d.skillid, dt.topicid, d.niveauid";

	return $DB->get_records_sql($sql,array($subjectid));
}

/**
 * Gets an associative array that is used to display the whole hierarchie of subjects, topics and competencies within a course
 *
 * @param int $courseid
 * @param int $subjectid
 * @return associative_array
 */
function block_exacomp_get_competence_tree($courseid = 0, $subjectid = null, $showalldescriptors = false, $topicid = null) {
	global $DB;

	if(!$showalldescriptors)
		$showalldescriptors = block_exacomp_get_settings_by_course($courseid)->show_all_descriptors;

	// 1. GET SUBJECTS
	if($courseid == 0)
		$allSubjects = block_exacomp_get_all_subjects();
	elseif($subjectid != null)
		$allSubjects = block_exacomp_get_subject_by_id($subjectid);
	else
		$allSubjects = block_exacomp_get_subjects_by_course($courseid, $showalldescriptors);
	
	// 2. GET TOPICS
	$allTopics = block_exacomp_get_all_topics($subjectid);
	if($courseid > 0) {
		if($topicid == LIS_SHOW_ALL_TOPICS)
			$courseTopics = block_exacomp_get_topics_by_subject($courseid, $subjectid);
		elseif($topicid == null)
			$courseTopics = block_exacomp_get_topics_by_course($courseid, $showalldescriptors);
		else
			$courseTopics = block_exacomp_get_topic_by_id($topicid);
	}
	// 3. GET DESCRIPTORS
	$allDescriptors = block_exacomp_get_descriptors($courseid, $showalldescriptors);
	
	foreach ($allDescriptors as $descriptor) {
	
		// get descriptor topic
		if (empty($allTopics[$descriptor->topicid])) continue;
		$topic = $allTopics[$descriptor->topicid];
		$topic->descriptors[$descriptor->id] = $descriptor;

		$examples = $DB->get_records_sql(
				"SELECT de.id as deid, e.id, e.title, tax.title as tax, e.task, e.externalurl,
				e.externalsolution, e.externaltask, e.solution, e.completefile, e.description, e.taxid, e.attachement, e.creatorid
				FROM {" . DB_EXAMPLES . "} e
				JOIN {" . DB_DESCEXAMP . "} de ON e.id=de.exampid AND de.descrid=?
				LEFT JOIN {" . DB_TAXONOMIES . "} tax ON e.taxid=tax.id
				", array($descriptor->id));
	
		$descriptor->examples = array();
		foreach($examples as $example){
			$descriptor->examples[$example->id] = $example;
		}
	}
	
	$subjects = array();

	foreach ($allTopics as $topic) {
		//topic must be coursetopic if courseid <> 0
		if($courseid > 0 && !array_key_exists($topic->id, $courseTopics))
			continue;

		//if($courseid==0 || $showalldescriptors || block_exacomp_check_activity_association($topic->id, TYPE_TOPIC, $courseid)) {
			// found: add it to the subject result, even if no descriptor from the topic is used
			// find all parent topics
			$found = true;
			for ($i = 0; $i < 10; $i++) {
				if ($topic->parentid) {
					// parent is topic, find it
					if (empty($allTopics[$topic->parentid])) {
						$found = false;
						break;
					}

					// found it
					$allTopics[$topic->parentid]->subs[$topic->id] = $topic;

					// go up
					$topic = $allTopics[$topic->parentid];
				} else {
					// parent is subject, find it
					if (empty($allSubjects[$topic->subjid])) {
						$found = false;
						break;
					}

					// found: add it to the subject result
					$subject = $allSubjects[$topic->subjid];
					$subject->subs[$topic->id] = $topic;
					$subjects[$topic->subjid] = $subject;

					// top found
					break;
				}
			}
	}

	return $subjects;
}
function block_exacomp_init_lis_data($courseid, $subjectid, $topicid, $student=false) {

	$subjects = block_exacomp_get_subjects_by_course($courseid);
	if (isset($subjects[$subjectid])) {
		$selectedSubject = $subjects[$subjectid];
	} elseif ($subjects) {
		$selectedSubject = reset($subjects);
	}

	$topics = block_exacomp_get_topics_by_subject($courseid,$selectedSubject->id);
	if (isset($topics[$topicid])) {
		$selectedTopic = $topics[$topicid];
	} elseif ($topics) {
		$selectedTopic = reset($topics);
	}

	if(!$student){
		$defaultTopic = new stdClass();
		$defaultTopic->id=LIS_SHOW_ALL_TOPICS;
		$defaultTopic->title= get_string('alltopics','block_exacomp');

		$topics = array_merge(array($defaultTopic),$topics);

		if($topicid == LIS_SHOW_ALL_TOPICS)
			$selectedTopic = $defaultTopic;
	}
	return array($subjects, $topics, $selectedSubject, $selectedTopic);
}
/**
 *
 * Returns all students enroled to a particular course
 * @param unknown_type $courseid
 */
function block_exacomp_get_students_by_course($courseid) {
	$context = context_course::instance($courseid);
	return get_role_users(5, $context);
}
/**
 *
 * Returns all teacher enroled to a course
 * @param unknown_type $courseid
 */
function block_exacomp_get_teachers_by_course($courseid) {
	$context = context_course::instance($courseid);
	return get_role_users(array(1,2,3,4), $context);
}

/**
 * Returns all the import information for a particular user in the given course about his competencies, topics and example evaluation values
 *
 * It returns user objects in the following format
 * 		$user
 * 			->competencies
 * 				->teacher[competenceid] = competence value
 * 				->student[competenceid] = competence value
 * 			->topics
 * 				->teacher
 * 				->student
 *
 * @param sdtClass $user
 * @param int $courseid
 * @return stdClass $ser
 */
function block_exacomp_get_user_information_by_course($user, $courseid) {
	// get student competencies
	$user = block_exacomp_get_user_competencies_by_course($user, $courseid);
	// get student topics
	$user = block_exacomp_get_user_topics_by_course($user, $courseid);
	// get student examples
	$user = block_exacomp_get_user_examples_by_course($user, $courseid);
	// get student activities topics
	$user = block_exacomp_get_user_activities_topics_by_course($user, $courseid);
	// get student activities competencies
	$user = block_exacomp_get_user_activities_competencies_by_course($user, $courseid);

	return $user;
}

/**
 * This method returns all user competencies for a particular user in the given course

 * @param stdClass $user
 * @param int $courseid
 * @return stdClass user
 */
function block_exacomp_get_user_competencies_by_course($user, $courseid) {
	global $DB;
	$user->competencies = new stdClass();
	$user->competencies->teacher = $DB->get_records_menu(DB_COMPETENCIES,array("courseid" => $courseid, "userid" => $user->id, "role" => ROLE_TEACHER, "comptype" => TYPE_DESCRIPTOR),'','compid as id, value');
	$user->competencies->student = $DB->get_records_menu(DB_COMPETENCIES,array("courseid" => $courseid, "userid" => $user->id, "role" => ROLE_STUDENT, "comptype" => TYPE_DESCRIPTOR),'','compid as id, value');

	return $user;
}

/**
 *  This method returns all user topics for a particular user in the given course
 *
 * @param stdClass $user
 * @param int $courseid
 * @return stdClass $user
 */
function block_exacomp_get_user_topics_by_course($user, $courseid) {
	global $DB;

	$user->topics = new stdClass();
	$user->topics->teacher = $DB->get_records_menu(DB_COMPETENCIES,array("courseid" => $courseid, "userid" => $user->id, "role" => ROLE_TEACHER, "comptype" => TYPE_TOPIC),'','compid as id, value');
	$user->topics->student = $DB->get_records_menu(DB_COMPETENCIES,array("courseid" => $courseid, "userid" => $user->id, "role" => ROLE_STUDENT, "comptype" => TYPE_TOPIC),'','compid as id, value');

	return $user;
}
/**
 *  This method returns all user examples for a particular user in the given course
 *
 * @param stdClass $user
 * @param int $courseid
 * @return stdClass $user
 */
function block_exacomp_get_user_examples_by_course($user, $courseid) {
	global $DB;

	$user->examples = new stdClass();
	$user->examples->teacher = $DB->get_records_menu(DB_EXAMPLEEVAL,array("courseid" => $courseid, "studentid" => $user->id),'','exampleid as id, teacher_evaluation as value');
	$user->examples->student = $DB->get_records_menu(DB_EXAMPLEEVAL,array("courseid" => $courseid, "studentid" => $user->id),'','exampleid as id, student_evaluation as value');

	return $user;
}
function block_exacomp_get_user_activities_topics_by_course($user, $courseid){
	global $DB;
	$activities = block_exacomp_get_activities_by_course($courseid);
	
	$user->activities_topics = new stdClass();
	$user->activities_topics->teacher = array();
	$user->activities_topics->student = array();
	
	foreach($activities as $activity){
		$user->activities_topics->teacher += $DB->get_records_menu(DB_COMPETENCIES_USER_MM,array("activityid" => $activity->id, "userid" => $user->id, "role" => ROLE_TEACHER, "comptype" => TYPE_TOPIC),'','compid as id, value');
		$user->activities_topics->student += $DB->get_records_menu(DB_COMPETENCIES_USER_MM,array("activityid" => $activity->id, "userid" => $user->id, "role" => ROLE_STUDENT, "comptype" => TYPE_TOPIC),'','compid as id, value');
	}
	
	return $user;
}
function block_exacomp_get_user_activities_competencies_by_course($user, $courseid){
	global $DB;
	$activities = block_exacomp_get_activities_by_course($courseid);
	
	$user->activities_competencies = new stdClass();
	$user->activities_competencies->teacher = array();
	$user->activities_competencies->student = array();
	
	foreach($activities as $activity){
		$user->activities_competencies->teacher += $DB->get_records_menu(DB_COMPETENCIES_USER_MM,array("activityid" => $activity->id, "userid" => $user->id, "role" => ROLE_TEACHER, "comptype" => TYPE_DESCRIPTOR),'','compid as id, value');
		$user->activities_competencies->student += $DB->get_records_menu(DB_COMPETENCIES_USER_MM,array("activityid" => $activity->id, "userid" => $user->id, "role" => ROLE_STUDENT, "comptype" => TYPE_DESCRIPTOR),'','compid as id, value');
	}
	
	return $user;
}
/**
 *
 * Build navigtion tabs, depending on role and version
 * @param unknown_type $context
 * @param unknown_type $courseid
 */
function block_exacomp_build_navigation_tabs($context,$courseid) {
	global $DB, $version, $usebadges, $skillmanagement;

	$courseSettings = block_exacomp_get_settings_by_course($courseid);
	$usedetailpage = $courseSettings->usedetailpage;
	$ready_for_use = block_exacomp_is_ready_for_use($courseid);
	$de = false;
		$lang = current_language();
		if(isset($lang) && substr( $lang, 0, 2) === 'de'){
			$de = true;
	}
	if($version)
		$checkConfig = block_exacomp_is_configured($courseid);
	else
		$checkConfig = block_exacomp_is_configured();

	$checkImport = $DB->get_records(DB_DESCRIPTORS);

	$rows = array();

	if (has_capability('block/exacomp:teacher', $context)) {
		if($checkImport){
			if($version){ //teacher tabs LIS
				if($checkConfig && $ready_for_use) {
					$rows[] = new tabobject('tab_competence_grid', new moodle_url('/blocks/exacomp/competence_grid.php',array("courseid"=>$courseid)),get_string('tab_competence_grid','block_exacomp'));
					$rows[] = new tabobject('tab_competence_overview', new moodle_url('/blocks/exacomp/assign_competencies.php',array("courseid"=>$courseid)),get_string('tab_competence_overview','block_exacomp'));
					if ($courseSettings->uses_activities && $usedetailpage)
						$rows[] = new tabobject('tab_competence_details', new moodle_url('/blocks/exacomp/competence_detail.php',array("courseid"=>$courseid)),get_string('tab_competence_details','block_exacomp'));
					$rows[] = new tabobject('tab_examples', new moodle_url('/blocks/exacomp/view_examples.php',array("courseid"=>$courseid)),get_string('tab_examples','block_exacomp'));
					$rows[] = new tabobject('tab_learning_agenda', new moodle_url('/blocks/exacomp/learningagenda.php',array("courseid"=>$courseid)),get_string('tab_learning_agenda','block_exacomp'));
					if (block_exacomp_moodle_badges_enabled() && $usebadges)
						$rows[] = new tabobject('tab_badges', new moodle_url('/blocks/exacomp/my_badges.php',array("courseid"=>$courseid)),get_string('tab_badges','block_exacomp'));
				}
				$settings = new tabobject('tab_teacher_settings', new moodle_url('/blocks/exacomp/edit_course.php',array("courseid"=>$courseid)),get_string('tab_teacher_settings','block_exacomp'));

				$settings->subtree = array();
				$settings->subtree[] = new tabobject('tab_teacher_settings_configuration', new moodle_url('/blocks/exacomp/edit_course.php', array('courseid'=>$courseid)), get_string("tab_teacher_settings_configuration", "block_exacomp"));
				$settings->subtree[] = new tabobject('tab_admin_configuration', new moodle_url('/blocks/exacomp/edit_config.php',array("courseid"=>$courseid)),get_string('tab_teacher_settings_selection_st','block_exacomp'));
				$settings->subtree[] = new tabobject('tab_teacher_settings_selection', new moodle_url('/blocks/exacomp/courseselection.php', array('courseid'=>$courseid)), get_string("tab_teacher_settings_selection", "block_exacomp"));

				if (block_exacomp_is_activated($courseid))
					if ($courseSettings->uses_activities)
					$settings->subtree[] = new tabobject('tab_teacher_settings_assignactivities', new moodle_url('/blocks/exacomp/edit_activities.php', array('courseid'=>$courseid)), get_string("tab_teacher_settings_assignactivities", "block_exacomp"));

				if (block_exacomp_moodle_badges_enabled() && $usebadges)
					$settings->subtree[] = new tabobject('tab_teacher_settings_badges', new moodle_url('/blocks/exacomp/edit_badges.php', array('courseid'=>$courseid)), get_string("tab_teacher_settings_badges", "block_exacomp"));

				$rows[] = $settings;

				if(!$skillmanagement && has_capability('block/exacomp:admin', $context))
					$rows[] = new tabobject('tab_admin_import', new moodle_url('/blocks/exacomp/import.php',array("courseid"=>$courseid)),get_string('tab_admin_import','block_exacomp'));

				if($de)
					$rows[] = new tabobject('tab_help', new moodle_url('/blocks/exacomp/help.php', array("courseid"=>$courseid)), get_string('tab_help', 'block_exacomp'));
			}else{	//teacher tabs !LIS
				if($checkConfig){
					if($ready_for_use){
						$rows[] = new tabobject('tab_competence_overview', new moodle_url('/blocks/exacomp/assign_competencies.php',array("courseid"=>$courseid)),get_string('tab_competence_overview','block_exacomp'));
						if ($courseSettings->uses_activities && $usedetailpage)
							$rows[] = new tabobject('tab_competence_details', new moodle_url('/blocks/exacomp/competence_detail.php',array("courseid"=>$courseid)),get_string('tab_competence_details','block_exacomp'));
						$rows[] = new tabobject('tab_competence_grid', new moodle_url('/blocks/exacomp/competence_grid.php',array("courseid"=>$courseid)),get_string('tab_competence_grid','block_exacomp'));
						$rows[] = new tabobject('tab_examples', new moodle_url('/blocks/exacomp/view_examples.php',array("courseid"=>$courseid)),get_string('tab_examples','block_exacomp'));
						$rows[] = new tabobject('tab_learning_agenda', new moodle_url('/blocks/exacomp/learningagenda.php',array("courseid"=>$courseid)),get_string('tab_learning_agenda','block_exacomp'));
						if(block_exacomp_moodle_badges_enabled() && $usebadges)
							$rows[] = new tabobject('tab_badges', new moodle_url('/blocks/exacomp/my_badges.php',array("courseid"=>$courseid)),get_string('tab_badges','block_exacomp'));
					}
					$settings = new tabobject('tab_teacher_settings', new moodle_url('/blocks/exacomp/edit_course.php',array("courseid"=>$courseid)),get_string('tab_teacher_settings','block_exacomp'));
					$settings->subtree = array();
					$settings->subtree[] = new tabobject('tab_teacher_settings_configuration', new moodle_url('/blocks/exacomp/edit_course.php', array('courseid'=>$courseid)), get_string("tab_teacher_settings_configuration", "block_exacomp"));
					$settings->subtree[] = new tabobject('tab_teacher_settings_selection', new moodle_url('/blocks/exacomp/courseselection.php', array('courseid'=>$courseid)), get_string("tab_teacher_settings_selection", "block_exacomp"));

					if (block_exacomp_is_activated($courseid))
						if ($courseSettings->uses_activities)
						$settings->subtree[] = new tabobject('tab_teacher_settings_assignactivities', new moodle_url('/blocks/exacomp/edit_activities.php', array('courseid'=>$courseid)), get_string("tab_teacher_settings_assignactivities", "block_exacomp"));

					if (block_exacomp_moodle_badges_enabled() && $usebadges) {
						$settings->subtree[] = new tabobject('tab_teacher_settings_badges', new moodle_url('/blocks/exacomp/edit_badges.php', array('courseid'=>$courseid)), get_string("tab_teacher_settings_badges", "block_exacomp"));
					}

					$rows[] = $settings;
				}
				if(has_capability('block/exacomp:admin', $context)){
					$rows[] = new tabobject('tab_admin_import', new moodle_url('/blocks/exacomp/import.php',array("courseid"=>$courseid)),get_string('tab_admin_import','block_exacomp'));
					if($checkImport)
						$rows[] = new tabobject('tab_admin_configuration', new moodle_url('/blocks/exacomp/edit_config.php',array("courseid"=>$courseid)),get_string('tab_admin_configuration','block_exacomp'));
				}

				if($de)
					$rows[] = new tabobject('tab_help', new moodle_url('/blocks/exacomp/help.php', array("courseid"=>$courseid)), get_string('tab_help', 'block_exacomp'));
			}
		}else{
			if(has_capability('block/exacomp:admin', $context)){
				$rows[] = new tabobject('tab_admin_import', new moodle_url('/blocks/exacomp/import.php',array("courseid"=>$courseid)),get_string('tab_admin_import','block_exacomp'));
				if($checkImport && !$version)
					$rows[] = new tabobject('tab_admin_configuration', new moodle_url('/blocks/exacomp/edit_config.php',array("courseid"=>$courseid)),get_string('tab_admin_configuration','block_exacomp'));
			}
		}
	}elseif (has_capability('block/exacomp:student', $context)) {
		if($checkConfig && $checkImport){
			if($version){ //student tabs LIS
				if($ready_for_use){
					$rows[] = new tabobject('tab_competence_grid', new moodle_url('/blocks/exacomp/competence_grid.php',array("courseid"=>$courseid)),get_string('tab_competence_grid','block_exacomp'));
					$rows[] = new tabobject('tab_competence_overview', new moodle_url('/blocks/exacomp/assign_competencies.php',array("courseid"=>$courseid)),get_string('tab_competence_overview','block_exacomp'));
					if ($courseSettings->uses_activities && $usedetailpage)
						$rows[] = new tabobject('tab_competence_details', new moodle_url('/blocks/exacomp/competence_detail.php',array("courseid"=>$courseid)),get_string('tab_competence_details','block_exacomp'));
					$profile = new tabobject('tab_competence_profile', new moodle_url('/blocks/exacomp/competence_profile.php', array("courseid"=>$courseid)), get_string('tab_competence_profile',  'block_exacomp'));
					$profile->subtree = array();
					$profile->subtree[] = new tabobject('tab_competence_profile_profile', new moodle_url('/blocks/exacomp/competence_profile.php', array("courseid"=>$courseid)), get_string('tab_competence_profile_profile', 'block_exacomp'));
					$profile->subtree[] = new tabobject('tab_competence_profile_settings', new moodle_url('/blocks/exacomp/competence_profile_settings.php', array("courseid"=>$courseid)), get_string('tab_competence_profile_settings', 'block_exacomp'));
					$rows[] = $profile;
					$rows[] = new tabobject('tab_learning_agenda', new moodle_url('/blocks/exacomp/learningagenda.php',array("courseid"=>$courseid)),get_string('tab_learning_agenda','block_exacomp'));
					if(block_exacomp_moodle_badges_enabled() && $usebadges)
						$rows[] = new tabobject('tab_badges', new moodle_url('/blocks/exacomp/my_badges.php',array("courseid"=>$courseid)),get_string('tab_badges','block_exacomp'));
				}
				if($de)
					$rows[] = new tabobject('tab_help', new moodle_url('/blocks/exacomp/help.php', array("courseid"=>$courseid)), get_string('tab_help', 'block_exacomp'));
			}else{	//student tabs !LIS
				if($ready_for_use){
					$rows[] = new tabobject('tab_competence_overview', new moodle_url('/blocks/exacomp/assign_competencies.php',array("courseid"=>$courseid)),get_string('tab_competence_overview','block_exacomp'));
					if ($courseSettings->uses_activities && $usedetailpage)
						$rows[] = new tabobject('tab_competence_details', new moodle_url('/blocks/exacomp/competence_detail.php',array("courseid"=>$courseid)),get_string('tab_competence_details','block_exacomp'));
					$profile = new tabobject('tab_competence_profile', new moodle_url('/blocks/exacomp/competence_profile.php', array("courseid"=>$courseid)), get_string('tab_competence_profile',  'block_exacomp'));
					$profile->subtree = array();
					$profile->subtree[] = new tabobject('tab_competence_profile_profile', new moodle_url('/blocks/exacomp/competence_profile.php', array("courseid"=>$courseid)), get_string('tab_competence_profile_profile', 'block_exacomp'));
					$profile->subtree[] = new tabobject('tab_competence_profile_settings', new moodle_url('/blocks/exacomp/competence_profile_settings.php', array("courseid"=>$courseid)), get_string('tab_competence_profile_settings', 'block_exacomp'));
					$rows[] = $profile;
					$rows[] = new tabobject('tab_competence_grid', new moodle_url('/blocks/exacomp/competence_grid.php',array("courseid"=>$courseid)),get_string('tab_competence_grid','block_exacomp'));
					$rows[] = new tabobject('tab_learning_agenda', new moodle_url('/blocks/exacomp/learningagenda.php',array("courseid"=>$courseid)),get_string('tab_learning_agenda','block_exacomp'));
					if(block_exacomp_moodle_badges_enabled() && $usebadges)
						$rows[] = new tabobject('tab_badges', new moodle_url('/blocks/exacomp/my_badges.php',array("courseid"=>$courseid)),get_string('tab_badges','block_exacomp'));
				}
				if($de)
					$rows[] = new tabobject('tab_help', new moodle_url('/blocks/exacomp/help.php', array("courseid"=>$courseid)), get_string('tab_help', 'block_exacomp'));
			}
		}
	}

	return $rows;
}
/**
 *
 * Gets html-selection with enroles students
 * @param unknown_type $students
 * @param unknown_type $selected
 * @param unknown_type $url
 */
function block_exacomp_studentselector($students,$selected,$url){
	global $CFG;

	$studentsAssociativeArray = array();
	$studentsAssociativeArray[0]=get_string('LA_no_student_selected', "block_exacomp");
	foreach($students as $student) {
		$studentsAssociativeArray[$student->id] = fullname($student);
	}
	return html_writer::select($studentsAssociativeArray, 'exacomp_competence_grid_select_student',$selected,true,
			array("onchange"=>"document.location.href='".$url."&studentid='+this.value;"));
}
/**
 *
 * Check if there is an custom xml-file uploaded
 */
function block_exacomp_check_customupload() {
	$context = context_system::instance();

	foreach (get_user_roles($context) as $role) {
		if($role->shortname == "exacompcustomupload")
			return true;
	}

	return false;
}

/**
 *
 * Get available education levels
 */
function block_exacomp_get_edulevels() {
	global $DB;
	return $DB->get_records(DB_EDULEVELS,null,'source');
}
/**
 *
 * Get schooltypes for particular education level
 * @param unknown_type $edulevel
 */
function block_exacomp_get_schooltypes($edulevel) {
	global $DB;

	return $DB->get_records(DB_SCHOOLTYPES, array("elid" => $edulevel));
}
function block_exacomp_get_schooltyp_by_subject($subject){
	global $DB;
	return $DB->get_field(DB_SCHOOLTYPES, "title", array("id"=>$subject->stid));
}
function block_exacomp_get_category($topic){
	global $DB;
	return $DB->get_record(DB_CATEGORIES,array("id"=>$topic->catid));
}
/**
 *
 * Gets assigned schooltypes for particular courseid
 * @param unknown_type $typeid
 * @param unknown_type $courseid
 */
function block_exacomp_get_mdltypes($typeid, $courseid = 0) {
	global $DB;

	return $DB->get_record(DB_MDLTYPES, array("stid" => $typeid, "courseid" => $courseid));
}
/**
 *
 * Assign a schooltype to a course
 * @param unknown_type $values
 * @param unknown_type $courseid
 */
function block_exacomp_set_mdltype($values, $courseid = 0) {
	global $DB;

	$DB->delete_records(DB_MDLTYPES,array("courseid"=>$courseid));
	foreach ($values as $value) {
		$DB->insert_record(DB_MDLTYPES, array("stid" => intval($value),"courseid" => $courseid));
	}
}
/**
 * check if configuration is already finished
 * configuration is finished if schooltype is selected for course(LIS)/moodle(normal)
 */
function block_exacomp_is_configured($courseid=0){
	global $DB;
	return $DB->get_records(DB_MDLTYPES, array("courseid"=>$courseid));
}
/**
 *
 * Check if moodle version is supporting badges
 */
function block_exacomp_moodle_badges_enabled() {
	global $CFG;

	// since moodle 2.5 it has badges functionality
	return (version_compare($CFG->release, '2.5') >= 0);
}
/**
 *
 * Set settings for course
 * @param unknown_type $courseid
 * @param unknown_type $settings
 */
function block_exacomp_save_coursesettings($courseid, $settings) {
	global $DB;

	$DB->delete_records(DB_SETTINGS, array("courseid" => $courseid));

	if ($settings->grading > SETTINGS_MAX_SCHEME) $settings->grading = SETTINGS_MAX_SCHEME;

	$settings->courseid = $courseid;
	$settings->tstamp = time();

	$DB->insert_record(DB_SETTINGS, $settings);
}
/**
 *
 * Check if there are already topics assigned to a course
 * @param unknown_type $courseid
 */
function block_exacomp_is_activated($courseid) {
	global $DB;

	return $DB->get_records(DB_COURSETOPICS, array("courseid" => $courseid));
}
/**
 *	Check is module is ready for use
 * 
 */
function block_exacomp_is_ready_for_use($courseid){
	global $DB;
	$course_settings = block_exacomp_get_settings_by_course($courseid);
	$is_activated = block_exacomp_is_activated($courseid);
	
	//no topics selected
	if(!$is_activated)
		return false;
	
	//topics selected
	//no activities->finish
	if(!$course_settings->uses_activities)
		return true;
	
	//work with activities
	$activities_assigned_to_any_course = $DB->get_records(DB_COMPETENCE_ACTIVITY, array('eportfolioitem'=>0));
	//no activites assigned
	if(!$activities_assigned_to_any_course)
		return false;
			
	//activity assigned in given course
	foreach($activities_assigned_to_any_course as $activity){
		$module = $DB->get_record('course_modules', array('id'=>$activity->activityid));
		if($module->course == $courseid)
			return true;
	}

	//no activity assigned in givel course
	return false;
}

/**
 *
 * Gets grading scheme for a course
 * @param unknown_type $courseid
 */
function block_exacomp_get_grading_scheme($courseid) {
	global $DB;
	$settings = block_exacomp_get_settings_by_course($courseid);
	return $settings->grading;
}
/**
 *
 * Builds topic title to print
 * @param unknown_type $topic
 */
function block_exacomp_get_output_fields($topic, $show_category=false) {
	global $version, $DB;

	if (preg_match('!^([^\s]*[0-9][^\s]*+)\s+(.*)$!iu', $topic->title, $matches)) {
		//$output_id = $matches[1];
		$output_id = '';
		$output_title = $matches[2];
	} else {
		$output_id = '';
		$output_title = $topic->title;
	}
	if($version && ($topic->id == LIS_SHOW_ALL_TOPICS)|| $show_category){
		$output_id = $DB->get_field(DB_CATEGORIES, 'title', array("id"=>$topic->catid));
		if($output_id)
			$output_id .= ': ';
	}
	return array($output_id, $output_title);
}
/**
 *
 * Awards badges to user
 * @param unknown_type $courseid
 * @param unknown_type $userid
 */
function block_exacomp_award_badges($courseid, $userid=null) {
	global $DB, $USER;

	// only award if badges are enabled
	if (!block_exacomp_moodle_badges_enabled()) return;

	$users = get_enrolled_users(context_course::instance($courseid));
	if ($userid) {
		if (!isset($users[$userid])) {
			return;
		}

		// only award for this user
		$users = array(
				$userid => $users[$userid]
		);
	}
	$badges = badges_get_badges(BADGE_TYPE_COURSE, $courseid);

	foreach ($badges as $badge) {

		// badges, which can be issued to user: status=active, type=manual
		if (!$badge->is_active() || !$badge->has_manual_award_criteria()) continue;

		$descriptors = $DB->get_records_sql('
				SELECT d.*
				FROM {'.DB_DESCRIPTORS.'} d
				JOIN {'.DB_DESCBADGE.'} db ON d.id=db.descid AND db.badgeid=?
				', array($badge->id));

		// no descriptors selected?
		if (empty($descriptors)) continue;

		foreach ($users as $user) {
			if ($badge->is_issued($user->id)) {
				// skip, already issued
				continue;
			}

			$usercompetences_all = block_exacomp_get_user_competencies_by_course($user, $courseid);
			$usercompetences = $usercompetences_all->competencies->teacher;
				
			$allFound = true;
			foreach ($descriptors as $descriptor) {
				if (isset($usercompetences[$descriptor->id])) {
					// found
				} else {
					// missing
					$allFound = false;
					break;
				}
			}

			// some are missing
			if (!$allFound) continue;

			// has all required competencies
			$acceptedroles = array_keys($badge->criteria[BADGE_CRITERIA_TYPE_MANUAL]->params);
			if (process_manual_award($user->id, $USER->id, $acceptedroles[0], $badge->id))  {
				// If badge was successfully awarded, review manual badge criteria.
				$data = new stdClass();
				$data->crit = $badge->criteria[BADGE_CRITERIA_TYPE_MANUAL];
				$data->userid = $user->id;
				badges_award_handle_manual_criteria_review($data);
			} else {
				echo 'error';
			}
		}
	}
}
/**
 *
 * Gets all badges for particular user
 * @param unknown_type $userid
 */
function block_exacomp_get_all_user_badges($userid = null) {
	global $USER;

	if ($userid == null) $userid = $USER->id;

	$records = badges_get_user_badges($userid);

	return $records;
}
/**
 *
 * Gets all badges for particular user in particular course
 * @param unknown_type $courseid
 * @param unknown_type $userid
 */
function block_exacomp_get_user_badges($courseid, $userid) {
	global $CFG, $DB;

	$badges = badges_get_badges(BADGE_TYPE_COURSE, $courseid);

	$result = (object)array(
			'issued' => array(),
			'pending' => array()
	);

	foreach ($badges as $badge) {

		// badges, which can be issued to user: status=active, type=manual
		if (!$badge->is_active() || !$badge->has_manual_award_criteria()) continue;

		$descriptors = $DB->get_records_sql('
				SELECT d.*
				FROM {'.DB_DESCRIPTORS.'} d
				JOIN {'.DB_DESCBADGE.'} db ON d.id=db.descid AND db.badgeid=?
				', array($badge->id));

		// no descriptors selected?
		if (empty($descriptors)) continue;

		$badge->descriptorStatus = array();

		$user = $DB->get_record('user', array('id'=>$userid));
		$usercompetences_all = block_exacomp_get_user_competencies_by_course($user, $courseid);
		$usercomptences = $usercompetences_all->competencies->teacher;
		//$usercompetences = block_exacomp_get_usercompetences($userid, $role=1, $courseid);

		foreach ($descriptors as $descriptor) {
			if (isset($usercompetences[$descriptor->id])) {
				$badge->descriptorStatus[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/accept.png'), 'style'=>'vertical-align:text-bottom;')).$descriptor->title;
			} else {
				$badge->descriptorStatus[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/cancel.png'), 'style'=>'vertical-align:text-bottom;')).$descriptor->title;
			}
		}
			
		if ($badge->is_issued($userid)) {
			$result->issued[$badge->id] = $badge;
		} else {
			$result->pending[$badge->id] = $badge;
		}
	}

	return $result;
}
/**
 *
 * Gets all desriptors assigned to a badge
 * @param unknown_type $badgeid
 */
function block_exacomp_get_badge_descriptors($badgeid){
	global $DB;
	return $DB->get_records_sql('
			SELECT d.*
			FROM {'.DB_DESCRIPTORS.'} d
			JOIN {'.DB_DESCBADGE.'} db ON d.id=db.descid AND db.badgeid=?
			', array($badgeid));
}
/**
 *
 * Build tree for learning materials with sort order "descriptors"
 * @param unknown_type $courseid
 */
function block_exacomp_build_example_tree_desc($courseid){
	global $DB;

	//get all subjects, topics, descriptors and examples
	$tree = block_exacomp_get_competence_tree($courseid);

	//go through tree and unset every subject, topic and descriptor where no example is appended
	foreach($tree as $subject){
		//traverse recursively, because of possible topic-children
		$subject_has_examples = block_exacomp_build_rec_topic_example_tree_desc($subject->subs);

		if(!$subject_has_examples)
			unset($tree[$subject->id]);
	}

	return $tree;
}
/**
 * helper function to traverse through tree recursively, because of endless topic children
 * and unset every node where leaf is no example
 */
function block_exacomp_build_rec_topic_example_tree_desc(&$subs){
	$sub_has_examples = false;
	$sub_topics_have_examples = false;
	foreach($subs as $topic){
		$topic_has_examples = false;
		if(isset($topic->descriptors)){
			foreach($topic->descriptors as $descriptor){
				if(empty($descriptor->examples)){
					unset($topic->descriptors[$descriptor->id]);
				}
				else{
					$sub_has_examples = true;
					$topic_has_examples = true;
				}
			}
		}
		if(isset($topic->subs)){
			$sub_topic_has_examples = block_exacomp_build_rec_topic_example_tree_desc($topic->subs);
			if($sub_topic_has_examples)
				$sub_topics_have_examples = true;
		}
		elseif((!isset($topic->subs) && !$topic_has_examples))
		unset($subs[$topic->id]);
			
		if(!$topic_has_examples && !$sub_topics_have_examples){
			unset($subs[$topic->id]);
		}
	}

	return $sub_has_examples;
}
/**
 * Build tree for learning materials with sort order "taxonomy"
 * Enter description here ...
 * @param unknown_type $courseid
 */
function block_exacomp_build_example_tree_tax($courseid){

	//get all subjects, topics, descriptor and examples
	$tree = block_exacomp_build_example_tree_desc($courseid);

	//extract all used taxonomies
	$taxonomies = block_exacomp_get_taxonomies($tree);

	//append the whole tree to every taxonomy
	foreach($taxonomies as $taxonomy){
		$tree = block_exacomp_build_example_tree_desc($courseid);
		$taxonomy->subs = $tree;
	}

	//unset every examples, descriptor, topic and subject where the taxonomy-id is not used
	foreach($taxonomies as $taxonomy){
		foreach($taxonomy->subs as $subject){
			$subject_has_examples = block_exacomp_build_rec_topic_example_tree_tax($subject->subs, $taxonomy->id);

			if(!$subject_has_examples)
				unset($taxonomy->subs[$subject->id]);
		}
	}
	return $taxonomies;
}
/**
 * helper function to traverse tree recursively because of endless topic structure
 */
function block_exacomp_build_rec_topic_example_tree_tax(&$subs, $taxid){
	$sub_has_examples = false;
	$sub_topics_have_examples = false;
	foreach($subs as $topic){
		$topic_has_examples = false;
		if(isset($topic->descriptors) && !empty($topic->descriptors)){
			foreach($topic->descriptors as $descriptor){
				$descriptor_has_examples = false;
				foreach($descriptor->examples as $example){
					if($example->taxid != $taxid){
						unset($descriptor->examples[$example->id]);
					}
					else{
						$descriptor_has_examples = true;
						$topic_has_examples = true;
						$sub_has_examples = true;
					}
				}
				if(!$descriptor_has_examples){
					unset($topic->descriptors[$descriptor->id]);
				}
			}
		}
		if(isset($topic->subs)){
			$sub_topic_has_examples = block_exacomp_build_rec_topic_example_tree_tax($topic->subs, $taxid);
			if($sub_topic_has_examples) 
				$sub_topics_have_examples = true;
		}
		elseif(!isset($topic->subs) && !$topic_has_examples){
			unset($subs[$topic->id]);
		}
		
		if(!$topic_has_examples && !$sub_topics_have_examples){
			unset($subs[$topic->id]);
		}
	}
	
	return $sub_has_examples;
}
/**
 *
 * Extract used taxonomies from given subject tree
 * @param unknown_type $tree
 */
function block_exacomp_get_taxonomies($tree){
	global $DB;

	$taxonomies = array();
	//extract all taxonomies from given structure, do it recursively because of topic structure
	foreach($tree as $subject){
		$taxonomies = block_exacomp_get_taxonomies_rek_topics($subject->subs, $taxonomies);
	}
	return $taxonomies;
}
/**
 * helper function for traversing through tree recursively
 */
function block_exacomp_get_taxonomies_rek_topics($subs, $taxonomies){
	global $DB;
	foreach($subs as $topic){
		if(isset($topic->descriptors)){
			foreach($topic->descriptors as $descriptor){
				foreach($descriptor->examples as $example){
					if($example->taxid > 0 && !in_array($example->taxid, $taxonomies)){
						$taxonomy = new stdClass();
						$taxonomy->id = $example->taxid;
						$taxonomy->title = $DB->get_record(DB_TAXONOMIES, array('id'=>$example->taxid), $fields='title');
						$taxonomies[$example->taxid]= $taxonomy;
					}
				}
			}
		}
		if(isset($topic->subs)){
			$taxonomies_sub = block_exacomp_get_taxonomies_rek_topics($topic->subs, $taxonomies);
			foreach($taxonomies_sub as $sub){
				if(!in_array($sub, $taxonomies))
					$taxonomies[$sub->id] = $sub;
			}
		}
	}
	return $taxonomies;
}
/**
 *
 * Gets supported modules for assigning activities
 */
function block_exacomp_get_supported_modules() {
	//TO DO: Settings for modules
	//assign, forum, glossary, quiz, wiki,url
	global $DB;
	
	$assign = $DB->get_record('modules', array('name'=>'assign'));
	$forum = $DB->get_record('modules', array('name'=>'forum'));
	$glossary = $DB->get_record('modules', array('name'=>'glossary'));
	$quiz = $DB->get_record('modules', array('name'=>'quiz'));
	$wiki = $DB->get_record('modules', array('name'=>'wiki'));
	$url = $DB->get_record('modules', array('name'=>'url'));
	
	return array($assign->id, $forum->id, $glossary->id, $quiz->id, $wiki->id, $url->id);
}
/**
 * Returns an associative array that gives information about which competence/topic is
 * associated with which course module
 *
 * $array->competencies[compid] = array(cmid, cmid, cmid)
 * $array->topics[topicid] = array(cmid, cmid, cmid)
 *
 * @param int $courseid
 * @return array
 */
function block_exacomp_get_course_module_association($courseid) {
	if(block_exacomp_get_settings_by_course($courseid)->uses_activities == 0)
		return null;

	global $DB;
	$records = $DB->get_records_sql('
			SELECT mm.id, compid, comptype, activityid
			FROM {'.DB_COMPETENCE_ACTIVITY.'} mm
			JOIN {course_modules} m ON m.id = mm.activityid
			WHERE m.course = ? AND mm.eportfolioitem = 0
			ORDER BY comptype, compid', array($courseid));

	$mm = new stdClass();
	$mm->competencies = array();
	$mm->topics = array();

	foreach($records as $record) {
		if($record->comptype == TYPE_DESCRIPTOR)
			$mm->competencies[$record->compid][$record->activityid] = $record->activityid;
		else
			$mm->topics[$record->compid][$record->activityid] = $record->activityid;
	}

	return $mm;
}
/**
 * Prepares an icon for a student for the given course modules, based on the grading.

 * @param array $coursemodules
 * @param stdClass $student
 *
 * @return stdClass $icon
 */
function block_exacomp_get_icon_for_user($coursemodules, $student) {
	global $CFG, $DB;
	require_once $CFG->libdir . '/gradelib.php';

	$found = false;
	$modules = $DB->get_records_menu("modules");

	$icon = new stdClass();
	$icon->text = fullname($student) . get_string('usersubmitted','block_exacomp') . ' <ul>';
	
	foreach ($coursemodules as $cm) {
		if(!in_array($cm->module, block_exacomp_get_supported_modules()))
			continue;

		$gradeinfo = grade_get_grades($cm->course,"mod",$modules[$cm->module],$cm->instance,$student->id);
		if(isset($gradeinfo->items[0]->grades[$student->id]->dategraded)) {
			$found = true;
			$icon->img = html_writer::empty_tag("img", array("src" => "pix/list_12x11.png","alt" => get_string("legend_activities","block_exacomp")));
			$icon->text .= '<li>' . $gradeinfo->items[0]->name . ((isset($gradeinfo->items[0]->grades[$student->id])) ? get_string('grading', "block_exacomp"). $gradeinfo->items[0]->grades[$student->id]->str_long_grade : '' ) . '</li>';
		}
	}
	if(!$found) {
		$icon->text = fullname($student) . get_string("usernosubmission","block_exacomp");
		$icon->img = html_writer::empty_tag("img", array("src" => "pix/x_11x11.png","alt" => fullname($student) . get_string("usernosubmission","block_exacomp")));
	} else
		$icon->text .= '</ul>';

	return $icon;
}
/**
 *
 * Assign topics to course
 * @param unknown_type $courseid
 * @param unknown_type $values
 */
function block_exacomp_set_coursetopics($courseid, $values) {
	global $DB;
	$DB->delete_records(DB_COURSETOPICS, array("courseid" => $courseid));
	if(isset($values)){
		foreach ($values as $value) {
			$DB->insert_record(DB_COURSETOPICS, array("courseid" => $courseid, "topicid" => intval($value)));
		}
	}
}
//TODO this can be done easier
function block_exacomp_get_active_topics($tree, $courseid){
	$topics = block_exacomp_get_topics_by_course($courseid);
	foreach($tree as $subject){
		block_exacomp_get_active_topics_rec($subject->subs, $topics);
	}
	return $tree;
}
//TODO this can be done easier
function block_exacomp_get_active_topics_rec($subs, $topics){
	foreach($subs as $topic){
		if(isset($topics[$topic->id])){
			$topic->checked = true;
		}else{
			$topic->checked = false;
		}
		if(!empty($topic->subs)){
			block_exacomp_get_active_topics_rec($topic->subs, $topics);
		}
	}
}
/**
 *
 * Returns quizes assigned to course
 * @param unknown_type $courseid
 */
function block_exacomp_get_active_tests_by_course($courseid){
	global $DB;

	$sql = "SELECT cm.instance as id, cm.id as activityid, q.grade FROM {block_exacompcompactiv_mm} activ "
	."JOIN {course_modules} cm ON cm.id = activ.activityid "
	."JOIN {modules} m ON m.id = cm.module "
	."JOIN {quiz} q ON cm.instance = q.id "
	."WHERE m.name='quiz' AND cm.course=?";

	$tests = $DB->get_records_sql($sql, array($courseid));

	foreach($tests as $test){
		$test->descriptors = $DB->get_records(DB_COMPETENCE_ACTIVITY, array('activityid'=>$test->activityid, 'comptype'=>TYPE_DESCRIPTOR), null, 'compid');
		$test->topics = $DB->get_records(DB_COMPETENCE_ACTIVITY, array('activityid'=>$test->activityid, 'comptype'=>TYPE_TOPIC), null, 'compid');
	}

	return $tests;
}
/**
 *
 * Returns all courses where an instance of Exabis Competences is installed
 */
function block_exacomp_get_courses(){
	global $DB;
	$courses = get_courses();

	$instances = $DB->get_records('block_instances', array('blockname'=>'exacomp'));

	$exabis_competences_courses = array();

	foreach($instances as $instance){
		$context = $DB->get_record('context', array('id'=>$instance->parentcontextid));
		$exabis_competences_courses[] = $context->instanceid;
	}

	return $exabis_competences_courses;
}
/**
 *
 * Gets URL for particular activity
 * @param unknown_type $activity
 * @param unknown_type $student
 */
function block_exacomp_get_activityurl($activity,$student=false) {
	global $DB;

	$mod = $DB->get_record('modules',array("id"=>$activity->module));

	if($mod->name == "assignment" && !$student)
		return new moodle_url('/mod/assignment/submissions.php', array('id'=>$activity->id));
	else return new moodle_url('mod/'.$mod->name.'/view.php', array('id'=>$activity->id));
}
/**
 *
 * Gets course module name for module
 * @param unknown_type $mod
 */
function block_exacomp_get_coursemodule($mod) {
	global $DB;
	$name = $DB->get_field('modules','name',array("id"=>$mod->module));
	return get_coursemodule_from_id($name,$mod->id);
}
/**
 *
 * Assign competencies to activites
 * @param unknown_type $data
 * @param unknown_type $courseid
 * @param unknown_type $comptype
 */
function block_exacomp_save_competencies_activities($data, $courseid, $comptype) {
	global $USER;
	if($data != null)
	foreach($data as $cmoduleKey => $comps){
		if(!empty($cmoduleKey)){
			foreach($comps as $compidKey=>$empty){
				//set activity
				block_exacomp_set_compactivity($cmoduleKey, $compidKey, $comptype);
			}
		}
	}
}
/**
 *
 * Assign one competence to one activity
 * @param unknown_type $activityid
 * @param unknown_type $compid
 * @param unknown_type $comptype
 */
function block_exacomp_set_compactivity($activityid, $compid, $comptype) {
	global $DB, $COURSE;

	$cmmod = $DB->get_record('course_modules',array("id"=>$activityid));
	$modulename = $DB->get_record('modules',array("id"=>$cmmod->module));
	$instance = get_coursemodule_from_id($modulename->name, $activityid);

	$DB->delete_records(DB_COMPETENCE_ACTIVITY, array("activityid" => $activityid, "compid" => $compid, "comptype"=>$comptype, "eportfolioitem"=>0));
	$DB->insert_record(DB_COMPETENCE_ACTIVITY, array("activityid" => $activityid, "compid" => $compid, "comptype"=>$comptype, "coursetitle"=>$COURSE->shortname, 'activitytitle'=>$instance->name));
}
/**
 *
 * Delete competence, activity associations
 */
function block_exacomp_delete_competencies_activities(){
	global $COURSE, $DB;

	$cmodules = $DB->get_records('course_modules', array('course'=>$COURSE->id));

	foreach($cmodules as $cm){
		$DB->delete_records(DB_COMPETENCE_ACTIVITY, array('activityid'=>$cm->id, 'eportfolioitem'=>0));
	}
}
/**
 * Get activity for particular competence
 * @param unknown_type $descid
 * @param unknown_type $courseid
 * @param unknown_type $descriptorassociation
 */
function block_exacomp_get_activities($compid, $courseid = null, $comptype = TYPE_DESCRIPTOR) { //alle assignments die einem bestimmten descriptor zugeordnet sind
	global $CFG, $DB;
	$query = 'SELECT mm.id as uniqueid,a.id,ass.grade,a.instance FROM {'.DB_DESCRIPTORS.'} descr
	INNER JOIN {'.DB_COMPETENCE_ACTIVITY.'} mm  ON descr.id=mm.compid
	INNER JOIN {course_modules} a ON a.id=mm.activityid
	LEFT JOIN {assign} ass ON ass.id=a.instance
	WHERE descr.id=? AND mm.comptype = '. $comptype;

	$condition = array($compid);
	if ($courseid){
		$query.=" AND a.course=?";
		$condition = array($compid, $courseid);
	}

	$activities = $DB->get_records_sql($query, $condition);
	if (!$activities) {
		$activities = array();
	}
	return $activities;
}
function block_exacomp_get_activities_by_course($courseid){
	global $DB;
	$query = 'SELECT DISTINCT mm.activityid as id, mm.activitytitle as title FROM {'.DB_COMPETENCE_ACTIVITY.'} mm 
		INNER JOIN {course_modules} a ON a.id=mm.activityid 
		WHERE a.course = ? AND mm.eportfolioitem=0';
	return $DB->get_records_sql($query, array($courseid));
}
function block_exacomp_init_competence_grid_data($courseid, $subjectid, $studentid) {
	global $version, $DB;

	if($studentid > 0) {
		$cm_mm = block_exacomp_get_course_module_association($courseid);
		$course_mods = get_fast_modinfo($courseid)->get_cms();
	}
	if($version) {
		$skills = array();
		$subjects = $DB->get_records_menu(DB_SUBJECTS,array("stid" => $subjectid),null,"id, title");
		$niveaus = $DB->get_records_menu(DB_CATEGORIES, array("lvl" => 4),"id,title","id,title");

		$data = array();
		if($studentid > 0)
			$competencies = array("studentcomps"=>$DB->get_records(DB_COMPETENCIES,array("role"=>ROLE_STUDENT,"courseid"=>$courseid,"userid"=>$studentid,"comptype"=>TYPE_TOPIC),"","compid,userid,reviewerid,value"),
					"teachercomps"=>$DB->get_records(DB_COMPETENCIES,array("role"=>ROLE_TEACHER,"courseid"=>$courseid,"userid"=>$studentid,"comptype"=>TYPE_TOPIC),"","compid,userid,reviewerid,value"));

		$selection = array();
		// Arrange data in associative array for easier use
		foreach($subjects as $subjid => $subject) {
			$topics = $DB->get_records('block_exacomptopics',array("subjid"=>$subjid),"catid");
			foreach($topics as $topic) {
				if($topic->catid == 0) continue;

				if($studentid > 0) {
					$topic->studentcomp = (array_key_exists($topic->id, $competencies['studentcomps'])) ? $competencies['studentcomps'][$topic->id]->value : false;
					$topic->teachercomp = (array_key_exists($topic->id, $competencies['teachercomps'])) ? $competencies['teachercomps'][$topic->id]->value : false;

					// ICONS
					if(isset($cm_mm->topics[$topic->id])) {
						//get CM instances
						$cm_temp = array();
						foreach($cm_mm->topics[$topic->id] as $cmid)
							$cm_temp[] = $course_mods[$cmid];
							
						$icon = block_exacomp_get_icon_for_user($cm_temp, $DB->get_record("user",array("id"=>$studentid)));
						$topic->icon = '<span title="'.$icon->text.'" class="exabis-tooltip">'.$icon->img.'</span>';
					}
				}
				$data[1][$subjid][$topic->catid][] = $topic;
			}
			$selection_temp = block_exacomp_get_topics_by_subject($courseid,$subjid);
			$selection = $selection + $selection_temp;
		}
		if(block_exacomp_get_settings_by_course($courseid)->show_all_descriptors)
			$selection = $DB->get_records(DB_COURSETOPICS,array('courseid'=>$courseid),'','topicid');

		return array($niveaus, $skills, $subjects, $data, $selection);
	}
	else {
		$niveaus = block_exacomp_get_niveaus_for_subject($subjectid);
		$skills = $DB->get_records_menu('block_exacompskills',null,null,"id, title");
		$descriptors = block_exacomp_get_descriptors_by_subject($subjectid);

		$data = array();
		if($studentid > 0)
			$competencies = array("studentcomps"=>$DB->get_records(DB_COMPETENCIES,array("role"=>ROLE_STUDENT,"courseid"=>$courseid,"userid"=>$studentid,"comptype"=>TYPE_DESCRIPTOR),"","compid,userid,reviewerid,value"),
					"teachercomps"=>$DB->get_records(DB_COMPETENCIES,array("role"=>ROLE_TEACHER,"courseid"=>$courseid,"userid"=>$studentid,"comptype"=>TYPE_DESCRIPTOR),"","compid,userid,reviewerid,value"));

		// Arrange data in associative array for easier use
		$topics = array();
		$data = array();
		foreach ($descriptors as $descriptor) {
			$examples = $DB->get_records_sql(
					"SELECT de.id as deid, e.id, e.title, tax.title as tax, e.task, e.externalurl,
					e.externalsolution, e.externaltask, e.solution, e.completefile, e.description, e.taxid, e.attachement, e.creatorid
					FROM {".DB_EXAMPLES."} e
					JOIN {".DB_DESCEXAMP."} de ON e.id=de.exampid AND de.descrid=?
					LEFT JOIN {".DB_TAXONOMIES."} tax ON e.taxid=tax.id
					ORDER BY tax.title", array($descriptor->id));
			$descriptor->examples = $examples;
				
			if($studentid > 0) {
				$descriptor->studentcomp = (array_key_exists($descriptor->id, $competencies['studentcomps'])) ? $competencies['studentcomps'][$descriptor->id]->value : false;
				$descriptor->teachercomp = (array_key_exists($descriptor->id, $competencies['teachercomps'])) ? $competencies['teachercomps'][$descriptor->id]->value : false;
				// ICONS
				if(isset($cm_mm->competencies[$descriptor->id])) {
					//get CM instances
					$cm_temp = array();
					foreach($cm_mm->competencies[$descriptor->id] as $cmid)
						$cm_temp[] = $course_mods[$cmid];
						
					$icon = block_exacomp_get_icon_for_user($cm_temp, $DB->get_record("user",array("id"=>$studentid)));
					$descriptor->icon = '<span title="'.$icon->text.'" class="exabis-tooltip">'.$icon->img.'</span>';
				}
			}
			$data[$descriptor->skillid][$descriptor->topicid][$descriptor->niveauid][] = $descriptor;
			$topics[$descriptor->topicid] = $descriptor->topic;
		}
		
		return array($niveaus, $skills, $topics, $data, array());
	}
}
function block_exacomp_get_niveaus_for_subject($subjectid) {
	global $DB;

	$niveaus = "SELECT DISTINCT n.id as id, n.title, n.sorting FROM {block_exacompdescriptors} d, {block_exacompdescrtopic_mm} dt, {block_exacompniveaus} n
	WHERE d.id=dt.descrid AND dt.topicid IN (SELECT id FROM {block_exacomptopics} WHERE subjid=?)
	AND d.niveauid > 0 AND d.niveauid = n.id order by n.sorting, n.id";
	
	/*$niveaus = "SELECT n.id, n.title, n.sorting, d.skillid, dt.topicid FROM {block_exacompdescriptors} d, {block_exacompdescrtopic_mm} dt, {block_exacompniveaus} n
	WHERE d.id=dt.descrid AND dt.topicid IN (SELECT id FROM {block_exacomptopics} WHERE subjid=?)
	AND d.niveauid > 0 AND d.niveauid = n.id GROUP BY n.id, n.title, n.sorting, d.skillid, dt.topicid
	ORDER BY n.id, n.sorting,d.skillid, dt.topicid";
*/
	return $DB->get_records_sql_menu($niveaus,array($subjectid));
}
/**
 *
 * Gets examples for LIS student view
 * @param unknown_type $subjects
 */
function block_exacomp_get_examples_LIS_student($subjects){
	$examples = array();
	foreach($subjects as $subject){
		block_exacomp_get_examples_LIS_student_topics($subject->subs, $examples);
	}
	return $examples;

}
/**
 *
 * Helper function to extract examples from subject tree for LIS student view
 * @param unknown_type $subs
 * @param unknown_type $examples
 */
function block_exacomp_get_examples_LIS_student_topics($subs, &$examples){
	foreach($subs as $topic){
		if(isset($topic->subs))
			block_exacomp_get_examples_LIS_student_topics($subs, $examples);
			
		if(isset($topic->descriptors)){
			foreach($topic->descriptors as $descriptor){
				foreach($descriptor->examples as $example){
					if(isset($examples[$example->id])){
						if(!isset($examples[$example->id]->descriptors[$descriptor->id]))
							$examples[$example->id]->descriptors[$descriptor->id] = $descriptor;
					}else{
						$examples[$example->id] = $example;
						$examples[$example->id]->descriptors = array();
						$examples[$example->id]->descriptors[$descriptor->id] = $descriptor;
					}

				}
			}
		}
	}
}
function block_exacomp_extract_niveaus($subject_tree){
	$niveaus = array();

	foreach($subject_tree as $subject){
		block_exacomp_extract_niveaus_topics($subject->subs, $niveaus);
	}
	return $niveaus;
}
function block_exacomp_extract_niveaus_topics($subs, &$niveaus){
	global $DB;
	foreach ($subs as $topic){
		if(isset($topic->subs))
			block_exacomp_extract_niveaus_topics($topic->subs, $niveaus);

		if(isset($topic->descriptors)){
			foreach($topic->descriptors as $descriptor){
				if($descriptor->niveauid > 0){
					if(!isset($niveaus[$descriptor->niveauid]))
						$niveaus[$descriptor->niveauid] = $DB->get_record(DB_NIVEAUS, array('id'=>$descriptor->niveauid));
				}
			}
		}
	}
}
/**
 *
 * Unsets every subject, topic, descriptor where descriptor niveauid is filtered
 * @param unknown_type $tree
 * @param unknown_type $niveaus
 */
function block_exacomp_filter_niveaus(&$tree, $niveaus){
	if(!empty($niveaus) && !in_array(0, $niveaus)){
		//go through tree and unset every subject, topic and descriptor where niveau is not in selected niveaus
		foreach($tree as $subject){
			//traverse recursively, because of possible topic-children
			$subject_has_niveaus = block_exacomp_filter_niveaus_topics($subject->subs, $niveaus);

			if(!$subject_has_niveaus)
				unset($tree[$subject->id]);
		}
	}
}
/**
 * helper function to traverse through tree recursively, because of endless topic children
 * and unset every node where descriptor doesn't fit to niveaus
 */
function block_exacomp_filter_niveaus_topics($subs, $niveaus){
	$sub_has_niveaus = false;
	$sub_topics_have_niveaus = false;
	foreach($subs as $topic){
		$topic_has_niveaus = false;
		if(isset($topic->descriptors)){
			foreach($topic->descriptors as $descriptor){
				if(!in_array($descriptor->niveauid, $niveaus)){
					unset($topic->descriptors[$descriptor->id]);
				}
				else{
					$sub_has_niveaus = true;
					$topic_has_niveaus = true;
				}
			}
		}
		if(isset($topic->subs)){
			$sub_topic_has_niveaus = block_exacomp_filter_niveaus_topics($topic->subs, $niveaus);
			if($sub_topic_has_niveaus)
				$sub_topics_have_niveaus = true;
		}
		elseif(!isset($topic->subs) && !$topic_has_niveaus)
			unset($subs[$topic->id]);
		
		if(!$topic_has_niveaus && !$sub_topics_have_niveaus){
			unset($subs[$topic->id]);
		}
	}
	return $sub_has_niveaus;
}
/**
 * 
 * Gets tree with activities on highest level
 * @param unknown_type $courseid
 */
function block_exacomp_build_activity_tree($courseid){
	$activities = block_exacomp_get_activities_by_course($courseid);
	
	//append the whole tree to every taxonomy
	foreach($activities as $activity){
		$tree = block_exacomp_get_competence_tree($courseid);
		$activity->subs = $tree;
	}
	$activity_association = block_exacomp_get_course_module_association($courseid);
	
	foreach($activities as $activity){
		foreach($activity->subs as $subject){
			$subject_has_examples = block_exacomp_build_activity_tree_topics($subject->subs, $activity->id, $activity_association);
			
			if(!$subject_has_examples)
				unset($activity->subs[$subject->id]);
		}
	}
	
	return $activities;
}

/**
 * helper function to traverse tree recursively because of endless topic structure
 */
function block_exacomp_build_activity_tree_topics(&$subs, $activityid, $activity_association){
	global $DB;
	
	$sub_has_activities = false;
	$sub_topics_have_activities = false;
	foreach($subs as $topic){
		$topic_has_activities = false;
		
		if(isset($activity_association->topics[$topic->id]) && array_key_exists($activityid, $activity_association->topics[$topic->id]))
			$topic_activity_association = true;//:
		else
			$topic_activity_association=false;
		
		if($topic_activity_association){
			$topic_has_activities = true;
			$sub_has_activities = true;
			$topic->used = true;
		}else{
			$topic->used = false;
		}
		
		if(isset($topic->descriptors) && !empty($topic->descriptors)){
			foreach($topic->descriptors as $descriptor){
				(array_key_exists($activityid, $activity_association->competencies[$descriptor->id]))?
					$descriptor_activity_association = true:$descriptor_activity_association=false;
		
				if(!$descriptor_activity_association){
					unset($topic->descriptors[$descriptor->id]);
				}else{
					$topic_has_activities = true;
					$sub_has_activities = true;
				}
			}
		}
		
		if(isset($topic->subs)){
			$sub_topic_has_activities = block_exacomp_build_activity_tree_topics($topic->subs, $activityid);
			if($sub_topic_has_activities) 
				$sub_topics_have_activities = true;
		}
		elseif(!isset($topic->subs) && !$topic_has_activities){
			unset($subs[$topic->id]);
		}
		
		if(!$topic_has_activities && !$sub_topics_have_activities){
			unset($subs[$topic->id]);
		}
	}
	
	return $sub_has_activities;
}

function block_exacomp_truncate_all_data() {
	global $DB;

	$sql = "TRUNCATE {block_exacompcategories}"; $DB->execute($sql);
	$sql = "TRUNCATE {block_exacompcompactiv_mm}"; $DB->execute($sql);
	$sql = "TRUNCATE {block_exacompcompuser}"; $DB->execute($sql);
	$sql = "TRUNCATE {block_exacompcompuser_mm}"; $DB->execute($sql);
	$sql = "TRUNCATE {block_exacompcoutopi_mm}"; $DB->execute($sql);
	$sql = "TRUNCATE {block_exacompdescbadge_mm}"; $DB->execute($sql);
	$sql = "TRUNCATE {block_exacompdescrexamp_mm}"; $DB->execute($sql);
	$sql = "TRUNCATE {block_exacompdescriptors}"; $DB->execute($sql);
	$sql = "TRUNCATE {block_exacompdescrtopic_mm}"; $DB->execute($sql);
	$sql = "TRUNCATE {block_exacompedulevels}"; $DB->execute($sql);
	$sql = "TRUNCATE {block_exacompexameval}"; $DB->execute($sql);
	$sql = "TRUNCATE {block_exacompexamples}"; $DB->execute($sql);
	$sql = "TRUNCATE {block_exacompmdltype_mm}"; $DB->execute($sql);
	$sql = "TRUNCATE {block_exacompniveaus}"; $DB->execute($sql);
	$sql = "TRUNCATE {block_exacompprofilesettings}"; $DB->execute($sql);
	$sql = "TRUNCATE {block_exacompschooltypes}"; $DB->execute($sql);
	$sql = "TRUNCATE {block_exacompsettings}"; $DB->execute($sql);
	$sql = "TRUNCATE {block_exacompskills}"; $DB->execute($sql);
	$sql = "TRUNCATE {block_exacompsubjects}"; $DB->execute($sql);
	$sql = "TRUNCATE {block_exacomptaxonomies}"; $DB->execute($sql);
	$sql = "TRUNCATE {block_exacomptopics}"; $DB->execute($sql);
}

/**
 *
 * This method returns all courses the user is entrolled to and exacomp is installed
 */
function block_exacomp_get_exacomp_courses($user) {
	global $DB;
	$user_courses = array();
	//get course id from all courses where exacomp is installed
	$all_exacomp_courses = block_exacomp_get_courses();
	
	foreach($all_exacomp_courses as $course){
		$context = context_course::instance($course);
		//only activte courses where user is enrolled
		if(is_enrolled($context, $user, '', true) && has_capability('block/exacomp:student', $context)){
			$user_courses[$course] = $DB->get_record('course', array('id'=>$course));
		}
	}
	
	return $user_courses;
}

/**
 *
 * This method is used to display course information in the profile overview
 *
 * @param int $courseid
 * @param int $studentid
 */
function block_exacomp_get_course_competence_statistics($courseid, $user, $scheme) {
	
	$coursesettings = block_exacomp_get_settings_by_course($courseid);
	
	$cm_mm = block_exacomp_get_course_module_association($courseid);
	
	$topics = block_exacomp_get_topics_by_course($courseid);
	$descriptors = block_exacomp_get_descriptors($courseid);
	
	$students = block_exacomp_get_students_by_course($courseid);
	$evaluation = block_exacomp_get_user_information_by_course($user, $courseid);
	
	$total = 0;
	$reached = 0;
	$average = 0;
	
	foreach($topics as $topic){
		if(!$coursesettings->uses_activities || ($coursesettings->uses_activities && isset($cm_mm->topics[$topic->id]))){
			$total ++;
		
			foreach ($students as $student){
				if($student->id == $user->id){
					if(isset($evaluation->topics->teacher) && isset($evaluation->topics->teacher[$topic->id])){
						if($scheme == 1 || $evaluation->topics->teacher[$topic->id] >= ceil($scheme/2))
							$reached ++;
					}
				}else{
					$student_evaluation = block_exacomp_get_user_information_by_course($student, $courseid);
					if(isset($student_evaluation->topics->teacher) && isset($student_evaluation->topics->teacher[$topic->id])){
						if($scheme == 1 || $student_evaluation->topics->teacher[$topic->id] >= ceil($scheme/2))
							$average ++;
					}
				}
			}
		}
	}
	foreach($descriptors as $descriptor){
		if(!$coursesettings->uses_activities || ($coursesettings->uses_activities && isset($cm_mm->competencies[$descriptor->id]))){
			$total ++;
				
			foreach($students as $student){
				if($student->id == $user->id){
					if(isset($evaluation->competencies->teacher) && isset($evaluation->competencies->teacher[$descriptor->id])){
						if($scheme == 1 || $evaluation->competencies->teacher[$descriptor->id] >= ceil($scheme/2))
							$reached ++;
					}
				}else{
					$student_evaluation = block_exacomp_get_user_information_by_course($student, $courseid);
					if(isset($student_evaluation->competencies->teacher) && isset($student_evaluation->competencies->teacher[$descriptor->id])){
						if($scheme == 1 || $student_evaluation->competencies->teacher[$descriptor->id] >= ceil($scheme/2))
							$average ++;
					}
				}
			}
		}	
	}
	
	$average = intval(ceil($average/(count($students)-1)));
	
	return array($total,$reached,$average);
}
/**
 * This method is used to get the necessary information to display a radar graph in
 * the profile overview
 *
 * $subjects['subjectid'] = $subject
 * $subject->student = 0-100 percentage
 * $subject->teacher = 0-100 percentage
 * @return array $subjects
 */
function block_exacomp_get_subjects_for_radar_graph($userid) {
	global $DB;
	// 1. get all used subjects
	$subjects = array();
	foreach(block_exacomp_get_exacomp_courses($userid) as $course) {
		$courseSubjects = block_exacomp_get_subjects_by_course($course->id);
		foreach($courseSubjects as $courseSubject) {
			if(!isset($subjects[$courseSubject->id]))
				$subjects[$courseSubject->id] = $courseSubject;
			
			$topics = block_exacomp_get_topics_by_subject($course->id,$courseSubject->id);
			foreach($topics as $topic) {
				if(!isset($subjects[$courseSubject->id]->topics[$topic->id]))
					$subjects[$courseSubject->id]->topics[$topic->id] = $topic;
			}
			
			$descriptors = block_exacomp_get_descriptors($course->id, false, $courseSubject->id);
			foreach($descriptors as $descriptor) {
				if(!isset($subjects[$courseSubject->id]->competencies[$descriptor->id]))
					$subjects[$courseSubject->id]->competencies[$descriptor->id] = $descriptor;
			}
		}
	}
	
	// 2. get competencies per subject
	foreach($subjects as $subject) {
		$total = count($subject->topics) + count($subject->competencies);
		$subject->total = $total;
		$sql = "SELECT DISTINCT c.id, c.userid, c.compid, c.role, c.courseid, c.value, c.comptype, c.timestamp FROM {".DB_COMPETENCIES."} c, {".DB_TOPICS."} t
			WHERE
			((c.comptype = 1 AND c.compid = t.id AND t.subjid = ?)
			OR
			(c.comptype = 0 AND c.compid IN
			 (
			    SELECT dt.descrid FROM {".DB_DESCTOPICS."} dt, {".DB_TOPICS."} t WHERE dt.topicid = t.id AND t.subjid = ?
			     )
			))
			AND c.role = ? AND c.userid = ?
			ORDER BY c.courseid";
		$competencies = $DB->get_records_sql($sql,array($subject->id,$subject->id,ROLE_TEACHER,$userid));
		$c_courseid = 0;
		$overall_competencies = array();
		foreach ($competencies as $competence) {
			if($competence->courseid != $c_courseid) {
				$c_courseid = $competence->courseid;
				$scheme = block_exacomp_get_grading_scheme($c_courseid);
			}
			if($competence->value >= ceil($scheme/2)) {
				$overall_competencies[$competence->id] = true;
			}
		}
		$subject->reached = count($overall_competencies);
		$subject->teacher = (count($overall_competencies) / $total) * 100;
		
		$competencies = $DB->get_records_sql($sql,array($subject->id,$subject->id,ROLE_STUDENT,$userid));
		$c_courseid = 0;
		$overall_competencies_student = array();
		foreach ($competencies as $competence) {
			if($competence->courseid != $c_courseid) {
				$c_courseid = $competence->courseid;
				$scheme = block_exacomp_get_grading_scheme($c_courseid);
			}
			if($competence->value >= ceil($scheme/2)) {
				$overall_competencies_student[$competence->id] = true;
			}
		}
		$subject->reached_student = count($overall_competencies_student);
		$subject->student = (count($overall_competencies_student) / $total) * 100;
	}
	return $subjects;
}
/**
 * $topics['topicid'] = $topic
 * $topic->student = 0-100 percentage
 * $topic->teacher = 0-100 percentage
 * @return array $topics
 */
function block_exacomp_get_topics_for_radar_graph($courseid,$studentid) {
	global $DB;
	$scheme = block_exacomp_get_grading_scheme($courseid);
	$topics = block_exacomp_get_topics_by_course($courseid);
	$user = $DB->get_record("user", array("id" => $studentid));

	foreach($topics as $topic) {
		$totalDescr = block_exacomp_get_descriptors_by_topic($courseid, $topic->id);
		$sql = "SELECT c.id, c.userid, c.compid, c.role, c.courseid, c.value, c.comptype, c.timestamp FROM {".DB_COMPETENCIES."} c, {".DB_DESCTOPICS."} dt
		WHERE c.compid = dt.descrid AND dt.topicid = ? AND c.comptype = 0 AND c.role=? AND c.userid = ? AND c.value >= ? AND c.courseid = ?";

		$competencies = $DB->get_records_sql($sql,array($topic->id,ROLE_TEACHER,$studentid, ceil($scheme / 2), $courseid));
		
		$topic->teacher = 0;
		if(count($totalDescr)>0)
			$topic->teacher = (count($competencies) / count($totalDescr)) * 100;
		
		$sql = "SELECT c.id, c.userid, c.compid, c.role, c.courseid, c.value, c.comptype, c.timestamp FROM {".DB_COMPETENCIES."} c, {".DB_DESCTOPICS."} dt
		WHERE c.compid = dt.descrid AND dt.topicid = ? AND c.comptype = 0 AND c.role=? AND c.userid = ? AND c.value >= ? AND c.courseid = ?";
		
		$competencies = $DB->get_records_sql($sql,array($topic->id,ROLE_STUDENT,$studentid, ceil($scheme/2),$courseid));
		
		$topic->student  =0;
		if(count($totalDescr)>0)
			$topic->student = (count($competencies) / count($totalDescr)) * 100;
	}
	
	return $topics;
}

/**
 * This method returns the total value of reached competencies ($teachercomp),
 * self-assessed competencies by the student ($studentcomp) and the pending
 * competencies ($pendingcomp) that is used to display a pie chart
 *
 * @param int $courseid
 * @return multitype:unknown
 */
function block_exacomp_get_competencies_for_pie_chart($courseid,$user, $scheme) {
	
	$coursesettings = block_exacomp_get_settings_by_course($courseid);
	
	$cm_mm = block_exacomp_get_course_module_association($courseid);
	
	$topics = block_exacomp_get_topics_by_course($courseid);
	$descriptors = block_exacomp_get_descriptors($courseid);
	
	$evaluation = block_exacomp_get_user_information_by_course($user, $courseid);
	
	$teachercomp = 0;
	$studentcomp = 0;
	$pendingcomp = 0;
	
	foreach($topics as $topic){
		$teacher_eval = false;
		$student_eval = false;
		if(!$coursesettings->uses_activities || ($coursesettings->uses_activities && isset($cm_mm->topics[$topic->id]))){
			if(isset($evaluation->topics->teacher) && isset($evaluation->topics->teacher[$topic->id])){
				if($scheme == 1 || $evaluation->topics->teacher[$topic->id] >= ceil($scheme/2)){
					$teachercomp ++;
					$teacher_eval = true;
				}
			}
			if(!$teacher_eval && isset($evaluation->topics->student) && isset($evaluation->topics->student[$topic->id])){
				if($scheme == 1 || $evaluation->topics->student[$topic->id] >= ceil($scheme/2)){
					$studentcomp ++;
					$student_eval = true;
				}
			}
			if(!$teacher_eval && !$student_eval)	
				$pendingcomp ++;
		}
	}
	foreach($descriptors as $descriptor){
		$teacher_eval = false;
		$student_eval = false;
		if(!$coursesettings->uses_activities || ($coursesettings->uses_activities && isset($cm_mm->competencies[$descriptor->id]))){
			if(isset($evaluation->competencies->teacher) && isset($evaluation->competencies->teacher[$descriptor->id])){
				if($scheme == 1 || $evaluation->competencies->teacher[$descriptor->id] >= ceil($scheme/2)){
					$teachercomp ++;
					$teacher_eval = true;
				}
			}
			if(!$teacher_eval && isset($evaluation->competencies->student) && isset($evaluation->competencies->student[$descriptor->id])){
				if($scheme == 1 || $evaluation->competencies->student[$descriptor->id] >= ceil($scheme/2)){
					$studentcomp ++;
					$student_eval = true;
				}
			}
			if(!$teacher_eval && !$student_eval) 	
				$pendingcomp ++;
		}
	}
	
	return array($teachercomp,$studentcomp,$pendingcomp);
}

function block_exacomp_exaportexists(){
	global $DB;
	return $DB->get_record('block',array('name'=>'exaport'));
}
function block_exacomp_exastudexists(){
	global $DB;
	return $DB->get_record('block',array('name'=>'exastud'));
}
function block_exacomp_get_exastud_periods(){
	global $USER, $DB;
	$sql = "SELECT p.id,p.description FROM {block_exastudreview} r, {block_exastudperiod} p WHERE r.student_id = ? AND r.periods_id = p.id GROUP BY p.id";
	return $DB->get_records_sql($sql,array("studentid"=>$USER->id));
}
function block_exacomp_get_exaport_items(){
	global $USER, $DB;
	return $DB->get_records('block_exaportitem',array("userid"=>$USER->id));
}
function block_exacomp_get_profile_settings(){
	global $USER, $DB;
	
	$profile_settings = new stdClass();
	
	$profile_settings->exacomp = array();
	$exacomp_settings = $DB->get_records(DB_PROFILESETTINGS, array('block'=>'exacomp', 'userid'=>$USER->id));
	foreach($exacomp_settings as $setting){
		$profile_settings->exacomp[$setting->itemid] = $setting;
	}
	
	/*$profile_settings->exaport = array();
	$exaport_settings = $DB->get_records(DB_PROFILESETTINGS, array('block'=>'exaport', 'userid'=>$USER->id));
	foreach($exaport_settings as $setting){
		$profile_settings->exaport[$setting->itemid] = $setting;
	}*/
	
	$profile_settings->exastud = array();
	$exastud_settings = $DB->get_records(DB_PROFILESETTINGS, array('block'=>'exastud', 'userid'=>$USER->id));
	foreach($exastud_settings as $setting){
		$profile_settings->exastud[$setting->itemid] = $setting;
	}
	
	$profile_settings->showonlyreached=0;
	$showonlyreached = $DB->get_field(DB_PROFILESETTINGS, 'itemid' ,array('block'=>'exacompdesc', 'userid'=>$USER->id));
	if($showonlyreached && $showonlyreached == 1)
		$profile_settings->showonlyreached = 1;
	
	$profile_settings->useexaport = 0;
	$useexaport = $DB->get_field(DB_PROFILESETTINGS, 'itemid', array('block'=>'useexaport', 'userid'=>$USER->id));
	if($useexaport && $useexaport == 1)
		$profile_settings->useexaport = 1;
		
	$profile_settings->useexastud = 0;	
 	$useexastud = $DB->get_field(DB_PROFILESETTINGS, 'itemid', array('block'=>'useexastud', 'userid'=>$USER->id));
	if($useexastud && $useexastud == 1)
		$profile_settings->useexastud = 1;
 	
	return $profile_settings;
}

function block_exacomp_reset_profile_settings($userid){
	global $DB;
	$DB->delete_records(DB_PROFILESETTINGS, array('userid'=>$userid));
}
	
function block_exacomp_set_profile_settings($userid, $showonlyreached, $useexaport, $useexastud, $courses, $periods){
	global $DB;
	//showonlyreached
	$insert = new stdClass();
	$insert->block = 'exacompdesc';
	$insert->itemid = intval($showonlyreached);
	$insert->feedback = '';
	$insert->userid = $userid;
	
	$DB->insert_record(DB_PROFILESETTINGS, $insert);
	
	//useexaport
	$insert = new stdClass();
	$insert->block = 'useexaport';
	$insert->itemid = intval($useexaport);
	$insert->feedback = '';
	$insert->userid = $userid;
	
	$DB->insert_record(DB_PROFILESETTINGS, $insert);
	
	//useexastud
	$insert = new stdClass();
	$insert->block = 'useexastud';
	$insert->itemid = intval($useexastud);
	$insert->feedback = '';
	$insert->userid = $userid;
	
	$DB->insert_record(DB_PROFILESETTINGS, $insert);
	
	//save courses
	foreach($courses as $course){
		$insert = new stdClass();
		$insert->block = 'exacomp';
		$insert->itemid = intval($course);
		$insert->feedback = '';
		$insert->userid = $userid;
		
		$DB->insert_record(DB_PROFILESETTINGS, $insert);
	}
	
	/*if($useexaport == 1){
		//save items
		foreach($items as $item){
			$insert = new stdClass();
			$insert->block = 'exaport';
			$insert->itemid = intval($item);
			$insert->feedback = '';
			$insert->userid = $userid;
			
			$DB->insert_record(DB_PROFILESETTINGS, $insert);
		}
	}*/
	if($useexastud == 1){
		//save periods
		foreach($periods as $period){
			$insert = new stdClass();
			$insert->block = 'exastud';
			$insert->itemid = intval($period);
			$insert->feedback = '';
			$insert->userid = $userid;
			
			$DB->insert_record(DB_PROFILESETTINGS, $insert);
		}
	}
}

function block_exacomp_init_profile($courses, $userid){
	global $DB;
	block_exacomp_reset_profile_settings($userid);
	foreach($courses as $course){
		$insert = new stdClass();
		$insert->block = 'exacomp';
		$insert->itemid = $course->id;
		$insert->feedback = '';
		$insert->userid = $userid;
		
		$DB->insert_record(DB_PROFILESETTINGS, $insert);
	}	
}
function block_exacomp_check_profile_config($userid){
	global $DB;
	
	return $DB->get_records(DB_PROFILESETTINGS, array('userid'=>$userid));
}
function block_exacomp_init_exaport_items($items){
	global $DB;
	$profile_settings = block_exacomp_get_profile_settings();
	
	foreach($items as $item){
		$item_comps = $DB->get_records(DB_COMPETENCE_ACTIVITY, array('activityid'=>$item->id, 'eportfolioitem'=>1));
		if($item_comps){
			$item->hascomps = true;
			$item->descriptors = array();
			$item->tabletype = 'item';
			foreach($item_comps as $item_comp){
				$item->descriptors[$item_comp->compid]  = $DB->get_record(DB_DESCRIPTORS, array('id'=>$item_comp->compid));
			}
		}
		else 
			$item->hascomps = false;
	}
	
	return $items;
}
function block_exacomp_get_exastud_reviews($periods, $student){
	global $DB;
	$reviews = array();
	foreach($periods as $period){
		$reviews[$period->id] = new stdClass();
		$reviews[$period->id]->id = $period->id;
		
		$db_review = $DB->get_record('block_exastudreview', array('student_id'=>$student->id, 'periods_id'=>$period->id));
		
		$reviews[$period->id]->feedback = $db_review->review;
		$reviews[$period->id]->reviewer = $DB->get_record('user', array('id'=>$db_review->teacher_id));
		$exastud_comps = $DB->get_records('block_exastudreviewpos', array('reviewid'=>$db_review->id, 'categorysource'=>'exastud'));
		$reviews[$period->id]->categories = array();
		foreach($exastud_comps as $cat){
			$reviews[$period->id]->categories[$cat->categoryid] = $DB->get_record('block_exastudcate', array('id'=>$cat->categoryid));
			$reviews[$period->id]->categories[$cat->categoryid]->evaluation = $cat->value;
		}
		
		$exacomp_comps = $DB->get_records('block_exastudreviewpos', array('reviewid'=>$db_review->id, 'categorysource'=>'exacomp'));
		$reviews[$period->id]->descriptors = array();
		foreach($exacomp_comps as $comp){
			$reviews[$period->id]->descriptors[$comp->categoryid] = $DB->get_record(DB_DESCRIPTORS, array('id'=>$comp->categoryid)); 
			$reviews[$period->id]->descriptors[$comp->categoryid]->evaluation = $comp->value;
		}
		
	}
	return $reviews;
}