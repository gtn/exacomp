<?php
/**
 * DATABSE TABLE NAMES
 */
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
define('DB_CROSSSUBJECTS', 'block_exacompcrosssubjects');
define('DB_DESCCROSS', 'block_exacompdescrcross_mm');
define('DB_CROSSSTUD', 'block_exacompcrossstud_mm');
define('DB_DESCVISIBILITY', 'block_exacompdescrvisibility');

/**
 * PLUGIN ROLES
 */
define('ROLE_TEACHER', 1);
define('ROLE_STUDENT', 0);

/**
 * COMPETENCE TYPES
 */
define('TYPE_DESCRIPTOR', 0);
define('TYPE_TOPIC', 1);
define('TYPE_CROSSSUB', 2);

define('SETTINGS_MAX_SCHEME', 10);
define('CUSTOM_EXAMPLE_SOURCE', 3);
define('ELOVE_EXAMPLE_SOURCE', 4);


define("IMPORT_SOURCE_SPECIFIC", 2);

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
$specificimport = get_config('exacomp','enableteacherimport');

define("SHOW_ALL_TOPICS",99999999);
define("SHOW_ALL_TAXONOMIES",100000000);
define("BLOCK_EXACOMP_SHOW_ALL_STUDENTS", -1);
define("BLOCK_EXACOMP_SHOW_STATISTIC", -2);

/**
 *
 * Includes all neccessary JavaScript files
 */
function block_exacomp_init_js_css(){
	global $PAGE, $CFG;
	$PAGE->requires->css('/blocks/exacomp/css/styles.css');
	$PAGE->requires->jquery();
	$PAGE->requires->js('/blocks/exacomp/javascript/exacomp.js', true);
	$PAGE->requires->js('/blocks/exacomp/javascript/ajax.js', true);

	// Strings can be used in JavaScript: M.util.get_string(identifier, component)
	$PAGE->requires->string_for_js('show', 'moodle');
	$PAGE->requires->string_for_js('hide', 'moodle');
	
	$scriptName = preg_replace('!\.[^\.]+$!', '', basename($_SERVER['PHP_SELF']));
	if (file_exists($CFG->dirroot.'/blocks/exacomp/css/'.$scriptName.'.css'))
		$PAGE->requires->css('/blocks/exacomp/css/'.$scriptName.'.css');
	if (file_exists($CFG->dirroot.'/blocks/exacomp/javascript/'.$scriptName.'.js'))
		$PAGE->requires->js('/blocks/exacomp/javascript/'.$scriptName.'.js', true);
		
	if(preg_match('/(?i)Trident|msie/',$_SERVER['HTTP_USER_AGENT']) && strcmp($scriptName, 'competence_profile')==0){
		$PAGE->requires->js('/blocks/exacomp/javascript/competence_profile_msie.js', true);
	}
}

function block_exacomp_is_teacher($context) {
    return has_capability('block/exacomp:teacher', $context);
}
function block_exacomp_is_student($context) {
    return has_capability('block/exacomp:student', $context);
}
function block_exacomp_is_admin($context) {
    return has_capability('block/exacomp:admin', $context);
}
function block_exacomp_require_teacher($context) {
    return require_capability('block/exacomp:teacher', $context);
}
function block_exacomp_require_admin($context) {
    return require_capability('block/exacomp:admin', $context);
}
 
/**
 * Gets one particular subject
 * 
 * @param int $subjectid
 * @return object $subject
 */
function block_exacomp_get_subject_by_id($subjectid) {
	global $DB;
	return $DB->get_record(DB_SUBJECTS,array("id" => $subjectid),'id, title, \'subject\' as tabletype');
}
/**
 * Gets all subjects that are in use in a particular course.
 * 
 * @param int $courseid
 * @param bool $showalldescriptors default false, show only comps with activities
 * @return array $subjects
 */
function block_exacomp_get_subjects_by_course($courseid, $showalldescriptors = false) {
	global $DB;

	if(!$showalldescriptors)
		$showalldescriptors = block_exacomp_get_settings_by_course($courseid)->show_all_descriptors;

	$sql = '
	SELECT DISTINCT s.id, s.title, s.stid, s.infolink, s.description, \'subject\' as tabletype
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
 * Gets all available subjects
 */
function block_exacomp_get_all_subjects() {
	global $DB;
	return $DB->get_records(DB_SUBJECTS,array(),'','id, title, \'subject\' as tabletype');
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
 * @param int $courseid
 */
function block_exacomp_get_subjects_for_schooltype($courseid, $schooltypeid=0){
	global $DB;
	$sql = 'SELECT sub.id FROM {'.DB_SUBJECTS.'} sub
	JOIN {'.DB_MDLTYPES.'} type ON sub.stid = type.stid
	WHERE type.courseid=?';

	if($schooltypeid > 0)
		$sql .= ' AND type.stid = ?';
		
	return $DB->get_records_sql($sql, array($courseid, $schooltypeid));
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
		$sql = 'SELECT s.id, s.title, \'subject\' as tabletype
		FROM {'.DB_SUBJECTS.'} s
		JOIN {'.DB_TOPICS.'} t ON t.subjid = s.id
		GROUP BY s.id, s.title, s.stid
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
 * returns the subject an example belongs to
 * @param int $exampleid
 */
function block_exacomp_get_subjecttitle_by_example($exampleid) {
    global $DB;

    $descriptors = block_exacomp_get_descriptors_by_example($exampleid);

    foreach($descriptors as $descriptor) {
    	
    	$full = $DB->get_record(DB_DESCRIPTORS, array("id" => $descriptor->descrid));
        $sql = "select s.* FROM {block_exacompsubjects} s, {block_exacompdescrtopic_mm} dt, {block_exacomptopics} t
        WHERE dt.descrid = ? AND t.id = dt.topicid AND t.subjid = s.id";

        $subject = $DB->get_record_sql($sql,array($full->parentid),IGNORE_MULTIPLE);
        return $subject->title;
    }
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

	$sql = 'SELECT DISTINCT t.id, t.title, t.sorting, t.subjid, t.description, t.numb
	FROM {'.DB_TOPICS.'} t
	JOIN {'.DB_COURSETOPICS.'} ct ON ct.topicid = t.id AND ct.courseid = ? '.(($subjectid > 0) ? 'AND t.subjid = ? ': '')
	.($showalldescriptors ? '' : '
			-- only show active ones
			JOIN {'.DB_DESCTOPICS.'} topmm ON topmm.topicid=t.id
			JOIN {'.DB_DESCRIPTORS.'} d ON topmm.descrid=d.id
			JOIN {'.DB_COMPETENCE_ACTIVITY.'} da ON (d.id=da.compid AND da.comptype = '.TYPE_DESCRIPTOR.') OR (t.id=da.compid AND da.comptype = '.TYPE_TOPIC.')
			JOIN {course_modules} a ON da.activityid=a.id AND a.course=ct.courseid
			').'
			ORDER BY t.sorting, t.subjid
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
			SELECT t.id, t.title, t.parentid, t.subjid, \'topic\' as tabletype, t.numb
			FROM {'.DB_SUBJECTS.'} s
			JOIN {'.DB_TOPICS.'} t ON t.subjid = s.id
			'.($subjectid == null ? '' : '
					-- only show active ones
					WHERE s.id = ?
					').'
			ORDER BY t.sorting, t.subjid
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
			SELECT t.id, t.title, t.parentid, t.subjid, \'topic\' as tabletype, t.numb
			FROM {'.DB_TOPICS.'} t
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
 * Deletes an uploaded example and all it's database/filesystem entries
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

	if($role == ROLE_STUDENT && $userid != $USER->id)
		return -1;
	
	if($record = $DB->get_record(DB_COMPETENCIES, array("userid" => $userid, "compid" => $compid, "comptype" => $comptype, "courseid" => $courseid, "role" => $role))) {
		$record->value = $value;
		$record->timestamp = time();
		$DB->update_record(DB_COMPETENCIES, $record);
		return $record->id;
	} else {
		return $DB->insert_record(DB_COMPETENCIES, array("userid" => $userid, "compid" => $compid, "comptype" => $comptype, "courseid" => $courseid, "role" => $role, "value" => $value, "reviewerid" => $USER->id, "timestamp" => time()));
	}
}

function block_exacomp_set_user_example($userid, $exampleid, $courseid, $role, $value = null, $starttime = 0, $endtime = 0, $studypartner = 'self') {
	global $DB,$USER,$version;
	
	
	$updateEvaluation = new stdClass();
	
	if ($role == ROLE_TEACHER) {
		$updateEvaluation->teacher_evaluation = intval($value);
		$updateEvaluation->teacher_reviewerid = $USER->id;
	} else {
		if ($userid != $USER->id)
			// student can only assess himself
			continue;
			
		if (!empty($starttime)) {
			$date = new DateTime(clean_param($values['starttime'], PARAM_SEQUENCE));
			$starttime = $date->getTimestamp();
		}else{
			$starttime = null;
		}
			
		if (!empty($endtime)) {
			$date = new DateTime(clean_param($values['endtime'], PARAM_SEQUENCE));
			$endtime = $date->getTimestamp();
		}else{
			$endtime = null;
		}
			
		if($value != null)
		$updateEvaluation->student_evaluation = intval($value);
			
		$updateEvaluation->starttime = $starttime;
		$updateEvaluation->endtime = $endtime;
		$updateEvaluation->studypartner = ($version) ? 'self' : $studypartner;
	}
	if($record = $DB->get_record(DB_EXAMPLEEVAL,array("studentid" => $userid, "courseid" => $courseid, "exampleid" => $exampleid))) {
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
		return $record->id;
	}
	else {
		$updateEvaluation->courseid = $courseid;
		$updateEvaluation->exampleid = $exampleid;
		$updateEvaluation->studentid = $userid;
	
		return $DB->insert_record(DB_EXAMPLEEVAL, $updateEvaluation);
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
function block_exacomp_save_competencies($data, $courseid, $role, $comptype, $topicid = null, $subjectid = false) {
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
	if(!$subjectid)
		block_exacomp_reset_comp_data($courseid, $role, $comptype, (($role == ROLE_STUDENT)) ? $USER->id : false, $topicid);
	else {
		$studentid = ($role == ROLE_STUDENT) ? $USER->id : required_param('studentid', PARAM_INT);
		block_exacomp_reset_comp_data_for_subject($courseid, $role, $comptype, $studentid, $subjectid);
	}
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
			foreach ($data[$compidKey] as $studentidKey => $activities) {
				if (!empty($data[$compidKey][$studentidKey])) {
					foreach($data[$compidKey][$studentidKey] as $activityidKey => $evaluations){
						if(!empty($data[$compidKey][$studentidKey][$activityidKey])){
							foreach($data[$compidKey][$studentidKey][$activityidKey] as $evalKey => $evalvalue){
								$value = intval($evalvalue);
								$activityid = $activityidKey;
								//var_dump($activityid);
								$values[] =  array('user' => intval($studentidKey), 'compid' => intval($compidKey), 'value' => $value, 'activityid'=>intval($activityidKey));
							}
						}
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
	if(!$topicid || $topicid == SHOW_ALL_TOPICS) {
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
 * Is used to reset topics in the comp grid
 * 
 * @param int $courseid
 * @param int $role
 * @param int $comptype
 * @param int $userid
 * @param int $subjectid
 */
function block_exacomp_reset_comp_data_for_subject($courseid, $role, $comptype = 1, $userid, $subjectid) {
	global $DB;
	
	$select = " courseid = ? AND role = ? AND COMPTYPE = ? AND userid = ? AND 
		(compid IN
		(SELECT t.id FROM {block_exacomptopics} t, {block_exacompsubjects} s
		WHERE t.subjid = s.id AND s.stid = ?))";
	
	$DB->delete_records_select('block_exacompcompuser', $select,array($courseid, $role, $comptype, $userid, $subjectid));
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
 * @param int $courseid
 */
function block_exacomp_get_settings_by_course($courseid = 0) {
	global $DB, $COURSE, $version, $skillmanagement;

	if (!$courseid)
		$courseid = $COURSE->id;

	$settings = $DB->get_record(DB_SETTINGS, array("courseid" => $courseid));

	if (empty($settings)) $settings = new stdClass;
	if (empty($settings->grading)) $settings->grading = 1;
	if (empty($settings->nostudents)) $settings->nostudents = 0;
	if (!isset($settings->uses_activities)) $settings->uses_activities = ($version || $skillmanagement)? 0 : 1;
	if (!isset($settings->show_all_examples)) $settings->show_all_examples = ($skillmanagement) ? 1 : 0;
	if (!isset($settings->usedetailpage)) $settings->usedetailpage = 0;
	if (!$settings->uses_activities) $settings->show_all_descriptors = 1;
	elseif (!isset($settings->show_all_descriptors)) $settings->show_all_descriptors = 0;
	if (!isset($settings->profoundness)) $settings->profoundness = 0;
	if(isset($settings->filteredtaxonomies)) $settings->filteredtaxonomies = json_decode($settings->filteredtaxonomies,true);
	else $settings->filteredtaxonomies = array(SHOW_ALL_TAXONOMIES);

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
function block_exacomp_get_descriptors($courseid = 0, $showalldescriptors = false, $subjectid = 0, $showallexamples = true, $filteredtaxonomies = array(SHOW_ALL_TAXONOMIES), $showonlyvisible=false) {
	global $DB;
	
	if(!$showalldescriptors)
		$showalldescriptors = block_exacomp_get_settings_by_course($courseid)->show_all_descriptors;
	
	$sql = '(SELECT DISTINCT desctopmm.id as u_id, d.id as id, d.title, d.niveauid, t.id AS topicid, \'descriptor\' as tabletype, d.profoundness, d.catid, d.parentid, n.sorting niveau, dvis.visible as visible, d.sorting '
	.'FROM {'.DB_TOPICS.'} t '
	.(($courseid>0)?'JOIN {'.DB_COURSETOPICS.'} topmm ON topmm.topicid=t.id AND topmm.courseid=? ' . (($subjectid > 0) ? ' AND t.subjid = '.$subjectid.' ' : '') :'')
	.'JOIN {'.DB_DESCTOPICS.'} desctopmm ON desctopmm.topicid=t.id '
	.'JOIN {'.DB_DESCRIPTORS.'} d ON desctopmm.descrid=d.id '
	.'JOIN {'.DB_DESCVISIBILITY.'} dvis ON dvis.descrid=d.id AND dvis.studentid=0 AND dvis.courseid=? '
	.($showonlyvisible?'AND dvis.visible = 1 ':'') 
	.'LEFT JOIN {'.DB_NIVEAUS.'} n ON d.niveauid = n.id '		
	.($showalldescriptors ? '' : '
			JOIN {'.DB_COMPETENCE_ACTIVITY.'} da ON d.id=da.compid AND da.comptype='.TYPE_DESCRIPTOR.'
			JOIN {course_modules} a ON da.activityid=a.id '.(($courseid>0)?'AND a.course=?':'')).')';

	$descriptors = $DB->get_records_sql($sql, array($courseid, $courseid, $courseid, $courseid));

	foreach($descriptors as &$descriptor) {
		//get examples
		$descriptor = block_exacomp_get_examples_for_descriptor($descriptor, $filteredtaxonomies, $showallexamples);
		//check for child-descriptors
		$descriptor->children = block_exacomp_get_child_descriptors($descriptor,$courseid, $showalldescriptors, $filteredtaxonomies, $showallexamples, true, $showonlyvisible);
	}
	return $descriptors;
}

function block_exacomp_get_child_descriptors($parent, $courseid, $showalldescriptors = false, $filteredtaxonomies = array(SHOW_ALL_TAXONOMIES), $showallexamples = true, $mindvisibility = true, $showonlyvisible=false ) {
	global $DB;
	
	if(!$DB->record_exists(DB_DESCRIPTORS, array("parentid" => $parent->id))) {
		return array();
	}
	
	if(!$showalldescriptors)
		$showalldescriptors = block_exacomp_get_settings_by_course($courseid)->show_all_descriptors;
	
	$sql = 'SELECT d.id, d.title, d.niveauid, \'descriptor\' as tabletype, '.$parent->topicid.' as topicid, d.profoundness, d.parentid, '.
			($mindvisibility?'dvis.visible as visible,':'').' d.sorting
			FROM {'.DB_DESCRIPTORS.'} d '
			.($mindvisibility ? 'JOIN {'.DB_DESCVISIBILITY.'} dvis ON dvis.descrid=d.id AND dvis.courseid=? AND dvis.studentid=0 '
			.($showonlyvisible? 'AND dvis.visible=1 ':'') : '');
	
	/* activity association only for parent descriptors
			.($showalldescriptors ? '' : '
				JOIN {'.DB_COMPETENCE_ACTIVITY.'} da ON d.id=da.compid AND da.comptype='.TYPE_DESCRIPTOR.'
				JOIN {course_modules} a ON da.activityid=a.id '.(($courseid>0)?'AND a.course=?':''));
	*/
	$sql .= ' WHERE  d.parentid = ?';
	
	$params = array();
	if($mindvisibility)
		$params[] = $courseid;
		
	$params[]= $parent->id;
	//$descriptors = $DB->get_records_sql($sql, ($showalldescriptors) ? array($parent->id) : array($courseid,$parent->id));
	$descriptors = $DB->get_records_sql($sql,  $params);
	
	foreach($descriptors as &$descriptor) {
		$descriptor = block_exacomp_get_examples_for_descriptor($descriptor, $filteredtaxonomies, $showallexamples);
		$descriptor->children = block_exacomp_get_child_descriptors($descriptor, $courseid,$showalldescriptors,$filteredtaxonomies);
	}
	return $descriptors;
}

function block_exacomp_get_examples_for_descriptor($descriptor, $filteredtaxonomies = array(SHOW_ALL_TAXONOMIES),$showallexamples = true) {
	global $DB;
	
	$examples = $DB->get_records_sql(
			"SELECT de.id as deid, e.id, e.title, tax.title as tax, e.task, e.externalurl,
				e.externalsolution, e.externaltask, e.solution, e.completefile, e.description, e.taxid, e.attachement, e.creatorid, e.iseditable
				FROM {" . DB_EXAMPLES . "} e
				JOIN {" . DB_DESCEXAMP . "} de ON e.id=de.exampid AND de.descrid=?
				LEFT JOIN {" . DB_TAXONOMIES . "} tax ON e.taxid=tax.id"
			. " WHERE "
			. " e.source != " . ELOVE_EXAMPLE_SOURCE . " AND "
			. (($showallexamples) ? " 1=1 " : " e.creatorid > 0")
			. ((in_array(SHOW_ALL_TAXONOMIES, $filteredtaxonomies)) ? "" : " AND e.taxid IN (".implode(",", $filteredtaxonomies) .")" )
			, array($descriptor->id));
	
	$descriptor->examples = array();
	foreach($examples as $example){
		$descriptor->examples[$example->id] = $example;
	}
	
	return $descriptor;
}
/**
 * Returns descriptors for a given topic
 * 
 * @param int $courseid
 * @param int $topicid
 * @param bool $showalldescriptors
 */
function block_exacomp_get_descriptors_by_topic($courseid, $topicid, $showalldescriptors = false, $mind_visibility=false) {
	global $DB;
	
	if(!$showalldescriptors)
		$showalldescriptors = block_exacomp_get_settings_by_course($courseid)->show_all_descriptors;
	
	$sql = '(SELECT DISTINCT d.id, desctopmm.id as u_id, d.title, d.niveauid, t.id AS topicid, \'descriptor\' as tabletype, d.catid, d.requirement, d.knowledgecheck, d.benefit, n.title as cattitle '
	.'FROM {'.DB_TOPICS.'} t JOIN {'.DB_COURSETOPICS.'} topmm ON topmm.topicid=t.id AND topmm.courseid=? ' . (($topicid > 0) ? ' AND t.id = '.$topicid.' ' : '')
	.'JOIN {'.DB_DESCTOPICS.'} desctopmm ON desctopmm.topicid=t.id '
	.'JOIN {'.DB_DESCRIPTORS.'} d ON desctopmm.descrid=d.id '
	. 'LEFT JOIN {'.DB_NIVEAUS.'} n ON n.id = d.niveauid '
	.($mind_visibility ? 'JOIN {'.DB_DESCVISIBILITY.'} dvis ON dvis.descrid=d.id AND dvis.courseid=? AND dvis.studentid=0 AND dvis.visible=1 ' : '')
	.($showalldescriptors ? '' : '
			JOIN {'.DB_COMPETENCE_ACTIVITY.'} da ON d.id=da.compid AND da.comptype='.TYPE_DESCRIPTOR.'
			JOIN {course_modules} a ON da.activityid=a.id '.(($courseid>0)?'AND a.course=?':'')).')';
	
	$descriptors = $DB->get_records_sql($sql, array($courseid, $courseid, $courseid));
	
	return $descriptors;
}
/**
 * Returns descriptors for a given subject
 * @param int $subjectid
 * @param bool $niveaus default false, if true only descriptors with neveaus are returned
 * @return multitype:
 */
function block_exacomp_get_descriptors_by_subject($subjectid,$niveaus = true) {
	global $DB;

	$sql = "SELECT d.*, dt.topicid, t.title as topic FROM {".DB_DESCRIPTORS."} d, {".DB_DESCTOPICS."} dt, {".DB_TOPICS."} t
	WHERE d.id=dt.descrid AND dt.topicid IN (SELECT id FROM {".DB_TOPICS."} WHERE subjid=?)";
	if($niveaus) $sql .= " AND d.niveauid > 0";
	$sql .= " AND dt.topicid = t.id order by d.skillid, dt.topicid, d.niveauid";

	return $DB->get_records_sql($sql,array($subjectid));
}

function block_exacomp_get_descriptors_by_example($exampleid) {
    global $DB;

    return $DB->get_records('block_exacompdescrexamp_mm',array('exampid' => $exampleid));
}

/**
 * Gets an associative array that is used to display the whole hierarchie of subjects, topics and competencies within a course
 *
 * @param int $courseid
 * @param int $subjectid
 * @return associative_array
 */
function block_exacomp_get_competence_tree($courseid = 0, $subjectid = null, $showalldescriptors = false, $topicid = null, $showallexamples = true, $filteredtaxonomies = array(SHOW_ALL_TAXONOMIES), $calledfromoverview = false, $calledfromactivities = false, $showonlyvisible=false) {
	global $DB, $version;

	if(!$showalldescriptors)
		$showalldescriptors = block_exacomp_get_settings_by_course($courseid)->show_all_descriptors;

	if($version && $subjectid != null && $calledfromoverview) {
		$selectedTopic = $DB->get_record(DB_TOPICS,array('id'=>$subjectid));
		$subjectid = $selectedTopic->subjid;
		$selectedParent = $DB->get_record(DB_DESCRIPTORS,array('id'=>$topicid));
	}
	 
	// 1. GET SUBJECTS
	if($courseid == 0)
		$allSubjects = block_exacomp_get_all_subjects();
	elseif($subjectid != null) {
		$allSubjects = array($subjectid => block_exacomp_get_subject_by_id($subjectid));
	}
	else
		$allSubjects = block_exacomp_get_subjects_by_course($courseid, $showalldescriptors);
	
	// 2. GET TOPICS
	$allTopics = block_exacomp_get_all_topics($subjectid);
	if($courseid > 0) {
		if(($topicid == SHOW_ALL_TOPICS && !$version) || ($version && !$calledfromoverview && !$calledfromactivities))
			$courseTopics = block_exacomp_get_topics_by_subject($courseid, $subjectid);
		elseif($topicid == null)
			$courseTopics = block_exacomp_get_topics_by_course($courseid, $showalldescriptors);
		else if(!$version)
			$courseTopics = block_exacomp_get_topic_by_id($topicid);
		else 
			$courseTopics = block_exacomp_get_topic_by_id($selectedTopic->id);
	}
	
	// 3. GET DESCRIPTORS
	$allDescriptors = block_exacomp_get_descriptors($courseid, $showalldescriptors,0,$showallexamples, array(SHOW_ALL_TAXONOMIES), $showonlyvisible);
	
	foreach ($allDescriptors as $descriptor) {
	
		if($version && $topicid != SHOW_ALL_TOPICS && $calledfromoverview)
			if($descriptor->id != $selectedParent->id)
				continue;
			
		// get descriptor topic
		if (empty($allTopics[$descriptor->topicid])) continue;
		$topic = $allTopics[$descriptor->topicid];
		$topic->descriptors[$descriptor->id] = $descriptor;
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
function block_exacomp_init_overview_data($courseid, $subjectid, $topicid, $student=false) {
	global $version;
	
	if($version)
		$subjects = block_exacomp_get_topics_by_course($courseid);
	else
		$subjects = block_exacomp_get_subjects_by_course($courseid);
	
	if (isset($subjects[$subjectid])) {
		$selectedSubject = $subjects[$subjectid];
	} elseif ($subjects) {
		$selectedSubject = reset($subjects);
	}

	if($version)
		$topics = block_exacomp_get_descriptors_by_topic($courseid, $selectedSubject->id);
	else
		$topics = block_exacomp_get_topics_by_subject($courseid,$selectedSubject->id);
	
	if (isset($topics[$topicid])) {
		$selectedTopic = $topics[$topicid];
	} elseif ($topics) {
		$selectedTopic = reset($topics);
	}

	if(!$student){
		$defaultTopic = new stdClass();
		$defaultTopic->id=SHOW_ALL_TOPICS;
		$defaultTopic->title= get_string('alltopics','block_exacomp');

		$topics = array_merge(array($defaultTopic),$topics);

		if($topicid == SHOW_ALL_TOPICS)
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
    // TODO: wie werden studenten definiert, kann man den rollen capabilities zuweisen damit sie als studenten gelten? -- prieler
	return get_role_users(5, $context);
}
/**
 *
 * Returns all teacher enroled to a course
 * @param unknown_type $courseid
 */
function block_exacomp_get_teachers_by_course($courseid) {
	$context = context_course::instance($courseid);
    // TODO: wie werden lehrer definiert, kann man den rollen capabilities zuweisen damit sie als lehrer gelten? -- prieler
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
function block_exacomp_get_user_information_by_course($user, $courseid, $onlycomps=false) {
	// get student competencies
	$user = block_exacomp_get_user_competencies_by_course($user, $courseid);
	// get student topics
	$user = block_exacomp_get_user_topics_by_course($user, $courseid);
	// get student crosssubs
	$user = block_exacomp_get_user_crosssubs_by_course($user, $courseid);
	
	if(!$onlycomps){
		// get student examples
		$user = block_exacomp_get_user_examples_by_course($user, $courseid);
		$activities = block_exacomp_get_activities_by_course($courseid);
		// get student activities topics
		$user = block_exacomp_get_user_activities_topics_by_course($user, $courseid, $activities);
		// get student activities competencies
		$user = block_exacomp_get_user_activities_competencies_by_course($user, $courseid, $activities);
	}
	return $user;
}
/**
 * This method returns all user crosssubs for a particular user in the given course

 * @param stdClass $user
 * @param int $courseid
 * @return stdClass user
 */
function block_exacomp_get_user_crosssubs_by_course($user, $courseid) {
	global $DB;
	$user->crosssubs = new stdClass();
	$user->crosssubs->teacher = $DB->get_records_menu(DB_COMPETENCIES,array("courseid" => $courseid, "userid" => $user->id, "role" => ROLE_TEACHER, "comptype" => TYPE_CROSSSUB),'','compid as id, value');
	$user->crosssubs->student = $DB->get_records_menu(DB_COMPETENCIES,array("courseid" => $courseid, "userid" => $user->id, "role" => ROLE_STUDENT, "comptype" => TYPE_CROSSSUB),'','compid as id, value');
	$user->crosssubs->timestamp_teacher = $DB->get_records_menu(DB_COMPETENCIES,array("courseid" => $courseid, "userid" => $user->id, "role" => ROLE_TEACHER, "comptype" => TYPE_CROSSSUB),'','compid as id, timestamp');
	$user->crosssubs->timestamp_student = $DB->get_records_menu(DB_COMPETENCIES,array("courseid" => $courseid, "userid" => $user->id, "role" => ROLE_STUDENT, "comptype" => TYPE_CROSSSUB),'','compid as id, timestamp');
	
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
	$user->competencies->timestamp_teacher = $DB->get_records_menu(DB_COMPETENCIES,array("courseid" => $courseid, "userid" => $user->id, "role" => ROLE_TEACHER, "comptype" => TYPE_DESCRIPTOR),'','compid as id, timestamp');
	$user->competencies->timestamp_student = $DB->get_records_menu(DB_COMPETENCIES,array("courseid" => $courseid, "userid" => $user->id, "role" => ROLE_STUDENT, "comptype" => TYPE_DESCRIPTOR),'','compid as id, timestamp');
	
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
	$user->topics->timestamp_teacher = $DB->get_records_menu(DB_COMPETENCIES,array("courseid" => $courseid, "userid" => $user->id, "role" => ROLE_TEACHER, "comptype" => TYPE_TOPIC),'','compid as id, timestamp');
	$user->topics->timestamp_student = $DB->get_records_menu(DB_COMPETENCIES,array("courseid" => $courseid, "userid" => $user->id, "role" => ROLE_STUDENT, "comptype" => TYPE_TOPIC),'','compid as id, timestamp');
	
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
/**
 *  This method returns all topics for the detailed view for a given user
 *
 * @param object $user
 * @param int $courseid
 */
function block_exacomp_get_user_activities_topics_by_course($user, $courseid, $activities){
	global $DB;
	
	$user->activities_topics = new stdClass();
	$user->activities_topics->activities = array();
	
	foreach($activities as $activity){
		$user->activities_topics->activities[$activity->id] = new stdClass();
		
		$user->activities_topics->activities[$activity->id]->teacher = array();
		$user->activities_topics->activities[$activity->id]->student = array();
		
		$user->activities_topics->activities[$activity->id]->teacher += $DB->get_records_menu(DB_COMPETENCIES_USER_MM,array("activityid" => $activity->id, "userid" => $user->id, "role" => ROLE_TEACHER, "comptype" => TYPE_TOPIC),'','compid as id, value');
		$user->activities_topics->activities[$activity->id]->student += $DB->get_records_menu(DB_COMPETENCIES_USER_MM,array("activityid" => $activity->id, "userid" => $user->id, "role" => ROLE_STUDENT, "comptype" => TYPE_TOPIC),'','compid as id, value');
	}
	
	return $user;
}
/**
 *  This method returns all competencies for the detailed view for a given user
 *
 * @param object $user
 * @param int $courseid
 */
function block_exacomp_get_user_activities_competencies_by_course($user, $courseid, $activities){
	global $DB;
	
	$user->activities_competencies = new stdClass();
	$user->activities_competencies->activities = array();
	
	foreach($activities as $activity){
		$user->activities_competencies->activities[$activity->id] = new stdClass();
		
		$user->activities_competencies->activities[$activity->id]->teacher = array();
		$user->activities_competencies->activities[$activity->id]->student = array();
		$user->activities_competencies->activities[$activity->id]->teacher += $DB->get_records_menu(DB_COMPETENCIES_USER_MM,array("activityid" => $activity->id, "userid" => $user->id, "role" => ROLE_TEACHER, "comptype" => TYPE_DESCRIPTOR, "eportfolioitem"=>0),'','compid as id, value');
		$user->activities_competencies->activities[$activity->id]->student += $DB->get_records_menu(DB_COMPETENCIES_USER_MM,array("activityid" => $activity->id, "userid" => $user->id, "role" => ROLE_STUDENT, "comptype" => TYPE_DESCRIPTOR, "eportfolioitem"=>0),'','compid as id, value');
	}
	
	return $user;
}
function block_exacomp_build_navigation_tabs_settings($courseid){
	global $version, $usebadges, $skillmanagement;
	$courseSettings = block_exacomp_get_settings_by_course($courseid);
	$settings_subtree = array();

		$settings_subtree[] = new tabobject('tab_teacher_settings_configuration', new moodle_url('/blocks/exacomp/edit_course.php', array('courseid'=>$courseid)), get_string("tab_teacher_settings_configuration", "block_exacomp"));
		if(!$skillmanagement)
			$settings_subtree[] = new tabobject('tab_teacher_settings_selection', new moodle_url('/blocks/exacomp/courseselection.php', array('courseid'=>$courseid)), get_string("tab_teacher_settings_selection", "block_exacomp"));

		if (block_exacomp_is_activated($courseid))
			if ($courseSettings->uses_activities)
				$settings_subtree[] = new tabobject('tab_teacher_settings_assignactivities', new moodle_url('/blocks/exacomp/edit_activities.php', array('courseid'=>$courseid)), get_string("tab_teacher_settings_assignactivities", "block_exacomp"));

		if (block_exacomp_moodle_badges_enabled() && $usebadges) {
			$settings_subtree[] = new tabobject('tab_teacher_settings_badges', new moodle_url('/blocks/exacomp/edit_badges.php', array('courseid'=>$courseid)), get_string("tab_teacher_settings_badges", "block_exacomp"));
		}
	return $settings_subtree;
}
function block_exacomp_build_navigation_tabs_profile($context,$courseid){
	if (block_exacomp_is_teacher($context)) 
		return array();
	
	$profile_subtree = array();
	
	$profile_subtree[] = new tabobject('tab_competence_profile_profile', new moodle_url('/blocks/exacomp/competence_profile.php', array("courseid"=>$courseid)), get_string('tab_competence_profile_profile', 'block_exacomp'));
	$profile_subtree[] = new tabobject('tab_competence_profile_settings', new moodle_url('/blocks/exacomp/competence_profile_settings.php', array("courseid"=>$courseid)), get_string('tab_competence_profile_settings', 'block_exacomp'));
	return $profile_subtree;
}
function block_exacomp_build_navigation_tabs_cross_subjects($context,$courseid){
	if (!block_exacomp_is_teacher($context)) 
		return array();
	
	$crosssubs = block_exacomp_get_cross_subjects_by_course($courseid);
	
	$profile_subtree = array();
	
	$profile_subtree[] = new tabobject('tab_cross_subjects_overview', new moodle_url('/blocks/exacomp/cross_subjects_overview.php', array("courseid"=>$courseid)), get_string('tab_cross_subjects_overview', 'block_exacomp'));
	if($crosssubs)
	    $profile_subtree[] = new tabobject('tab_cross_subjects_course', new moodle_url('/blocks/exacomp/cross_subjects.php', array("courseid"=>$courseid)), get_string('tab_cross_subjects_course', 'block_exacomp'));
	return $profile_subtree;
}
/**
 * Build navigtion tabs, depending on role and version
 * 
 * @param object $context
 * @param int $courseid
 */
function block_exacomp_build_navigation_tabs($context,$courseid) {
	global $DB, $USER, $version, $usebadges, $skillmanagement,$specificimport;

	$global_context = context_system::instance();
	
	$courseSettings = block_exacomp_get_settings_by_course($courseid);
	$usedetailpage = $courseSettings->usedetailpage;
	$ready_for_use = block_exacomp_is_ready_for_use($courseid);
	
	$de = false;
		$lang = current_language();
		if(isset($lang) && substr( $lang, 0, 2) === 'de'){
			$de = true;
	}
	if($skillmanagement)
		$checkConfig = block_exacomp_is_configured($courseid);
	else
		$checkConfig = block_exacomp_is_configured();
	
	$checkImport = $DB->get_records(DB_DESCRIPTORS);

	$rows = array();

	if (block_exacomp_is_teacher($context)) {
		$crosssubs = block_exacomp_get_cross_subjects_by_course($courseid);
		if($checkImport){
			if($version){ //teacher tabs LIS
				if (block_exacomp_is_activated($courseid))
					$rows[] = new tabobject('tab_competence_grid', new moodle_url('/blocks/exacomp/competence_grid.php',array("courseid"=>$courseid)),get_string('tab_competence_grid','block_exacomp'));
					
				if($checkConfig && $ready_for_use) {
					$rows[] = new tabobject('tab_competence_overview', new moodle_url('/blocks/exacomp/assign_competencies.php',array("courseid"=>$courseid)),get_string('tab_competence_overview','block_exacomp'));
					if($crosssubs)
					    $rows[] = new tabobject('tab_cross_subjects', new moodle_url('/blocks/exacomp/cross_subjects.php', array("courseid"=>$courseid)), get_string('tab_cross_subjects', 'block_exacomp'));
					else
					    $rows[] = new tabobject('tab_cross_subjects', new moodle_url('/blocks/exacomp/cross_subjects_overview.php', array("courseid"=>$courseid)), get_string('tab_cross_subjects', 'block_exacomp'));
					
					if ($courseSettings->uses_activities && $usedetailpage)
						$rows[] = new tabobject('tab_competence_details', new moodle_url('/blocks/exacomp/competence_detail.php',array("courseid"=>$courseid)),get_string('tab_competence_details','block_exacomp'));
					
					if($courseSettings->nostudents != 1) {
					    $rows[] = new tabobject('tab_competence_profile_profile', new moodle_url('/blocks/exacomp/competence_profile.php', array("courseid"=>$courseid)), get_string('tab_competence_profile',  'block_exacomp'));
					    $rows[] = new tabobject('tab_examples', new moodle_url('/blocks/exacomp/view_examples.php',array("courseid"=>$courseid)),get_string('tab_examples','block_exacomp'));
					    $rows[] = new tabobject('tab_learning_agenda', new moodle_url('/blocks/exacomp/weekly_schedule.php',array("courseid"=>$courseid)),get_string('tab_learning_agenda','block_exacomp'));
					}//if (block_exacomp_moodle_badges_enabled() && $usebadges)
						//$rows[] = new tabobject('tab_badges', new moodle_url('/blocks/exacomp/my_badges.php',array("courseid"=>$courseid)),get_string('tab_badges','block_exacomp'));
				}
				$settings = new tabobject('tab_teacher_settings', new moodle_url('/blocks/exacomp/edit_course.php',array("courseid"=>$courseid)),get_string('tab_teacher_settings','block_exacomp'));

				$rows[] = $settings;

				if(!$skillmanagement && has_capability('block/exacomp:admin', $context))
					$rows[] = new tabobject('tab_admin_import', new moodle_url('/blocks/exacomp/import.php',array("courseid"=>$courseid)),get_string('tab_admin_import','block_exacomp'));

				if($de && !$skillmanagement)
					$rows[] = new tabobject('tab_help', new moodle_url('/blocks/exacomp/help.php', array("courseid"=>$courseid)), get_string('tab_help', 'block_exacomp'));
			}else{	//teacher tabs !LIS
				//if use skill management
				if($skillmanagement && block_exacomp_is_teacher($context)){
					$rows[] = new tabobject('tab_skillmanagement', new moodle_url('/blocks/exacomp/skillmanagement.php', array('courseid'=>$courseid)),get_string('tab_skillmanagement','block_exacomp'));
				}
				if($checkConfig){
					if($ready_for_use){
						$rows[] = new tabobject('tab_competence_overview', new moodle_url('/blocks/exacomp/assign_competencies.php',array("courseid"=>$courseid)),get_string('tab_competence_overview','block_exacomp'));
						
						if($crosssubs)
    					    $rows[] = new tabobject('tab_cross_subjects', new moodle_url('/blocks/exacomp/cross_subjects.php', array("courseid"=>$courseid)), get_string('tab_cross_subjects', 'block_exacomp'));
    					else
    					    $rows[] = new tabobject('tab_cross_subjects', new moodle_url('/blocks/exacomp/cross_subjects_overview.php', array("courseid"=>$courseid)), get_string('tab_cross_subjects', 'block_exacomp'));
				
					if ($courseSettings->uses_activities && $usedetailpage)
							$rows[] = new tabobject('tab_competence_details', new moodle_url('/blocks/exacomp/competence_detail.php',array("courseid"=>$courseid)),get_string('tab_competence_details','block_exacomp'));
					}	
					if (block_exacomp_is_activated($courseid))
						$rows[] = new tabobject('tab_competence_grid', new moodle_url('/blocks/exacomp/competence_grid.php',array("courseid"=>$courseid)),get_string('tab_competence_grid','block_exacomp'));
					if($ready_for_use && $courseSettings->nostudents != 1){
						$rows[] = new tabobject('tab_competence_profile_profile', new moodle_url('/blocks/exacomp/competence_profile.php', array("courseid"=>$courseid)), get_string('tab_competence_profile',  'block_exacomp'));
						$rows[] = new tabobject('tab_examples', new moodle_url('/blocks/exacomp/view_examples.php',array("courseid"=>$courseid)),get_string('tab_examples','block_exacomp'));
						$rows[] = new tabobject('tab_learning_agenda', new moodle_url('/blocks/exacomp/learningagenda.php',array("courseid"=>$courseid)),get_string('tab_learning_agenda','block_exacomp'));
						if($courseSettings->profoundness == 1)
							$rows[] = new tabobject('tab_profoundness', new moodle_url('/blocks/exacomp/profoundness.php',array("courseid"=>$courseid)),get_string('tab_profoundness','block_exacomp'));
					}
					$settings = new tabobject('tab_teacher_settings', new moodle_url('/blocks/exacomp/edit_course.php',array("courseid"=>$courseid)),get_string('tab_teacher_settings','block_exacomp'));
					
					$rows[] = $settings;
				}
				if((has_capability('block/exacomp:admin', $global_context) || $specificimport) && !$skillmanagement){
					$rows[] = new tabobject('tab_admin_import', new moodle_url('/blocks/exacomp/import.php',array("courseid"=>$courseid)),get_string('tab_admin_import','block_exacomp'));
					if($checkImport && has_capability('block/exacomp:admin', $global_context))
						$rows[] = new tabobject('tab_admin_configuration', new moodle_url('/blocks/exacomp/edit_config.php',array("courseid"=>$courseid)),get_string('tab_admin_configuration','block_exacomp'));
				}

				if($de && !$skillmanagement)
					$rows[] = new tabobject('tab_help', new moodle_url('/blocks/exacomp/help.php', array("courseid"=>$courseid)), get_string('tab_help', 'block_exacomp'));
			}
		}else{
			if(has_capability('block/exacomp:admin', $global_context) || $specificimport){
				$rows[] = new tabobject('tab_admin_import', new moodle_url('/blocks/exacomp/import.php',array("courseid"=>$courseid)),get_string('tab_admin_import','block_exacomp'));
				if($checkImport && !$version && has_capability('block/exacomp:admin', $global_context))
					$rows[] = new tabobject('tab_admin_configuration', new moodle_url('/blocks/exacomp/edit_config.php',array("courseid"=>$courseid)),get_string('tab_admin_configuration','block_exacomp'));
			}
		}
	}elseif (has_capability('block/exacomp:student', $context)) {
		$crosssubs = block_exacomp_get_cross_subjects_by_course($courseid, $USER->id);
		if($checkConfig && $checkImport){
			if($version){ //student tabs LIS
				if (block_exacomp_is_activated($courseid))
					$rows[] = new tabobject('tab_competence_grid', new moodle_url('/blocks/exacomp/competence_grid.php',array("courseid"=>$courseid)),get_string('tab_competence_grid','block_exacomp'));
					
				if($ready_for_use){
					$rows[] = new tabobject('tab_competence_overview', new moodle_url('/blocks/exacomp/assign_competencies.php',array("courseid"=>$courseid)),get_string('tab_competence_overview','block_exacomp'));
					if($crosssubs)
						$rows[] = new tabobject('tab_cross_subjects', new moodle_url('/blocks/exacomp/cross_subjects.php', array("courseid"=>$courseid)), get_string('tab_cross_subjects', 'block_exacomp'));
					if ($courseSettings->uses_activities && $usedetailpage)
						$rows[] = new tabobject('tab_competence_details', new moodle_url('/blocks/exacomp/competence_detail.php',array("courseid"=>$courseid)),get_string('tab_competence_details','block_exacomp'));
					
					$rows[] = new tabobject('tab_learning_agenda', new moodle_url('/blocks/exacomp/weekly_schedule.php',array("courseid"=>$courseid)),get_string('tab_learning_agenda','block_exacomp'));
					
					$profile = new tabobject('tab_competence_profile', new moodle_url('/blocks/exacomp/competence_profile.php', array("courseid"=>$courseid)), get_string('tab_competence_profile',  'block_exacomp'));
					$rows[] = $profile;
					//if(block_exacomp_moodle_badges_enabled() && $usebadges)
						//$rows[] = new tabobject('tab_badges', new moodle_url('/blocks/exacomp/my_badges.php',array("courseid"=>$courseid)),get_string('tab_badges','block_exacomp'));
				}
				if($de && !$skillmanagement)
					$rows[] = new tabobject('tab_help', new moodle_url('/blocks/exacomp/help.php', array("courseid"=>$courseid)), get_string('tab_help', 'block_exacomp'));
			}else{	//student tabs !LIS
				if($ready_for_use){
					$rows[] = new tabobject('tab_competence_overview', new moodle_url('/blocks/exacomp/assign_competencies.php',array("courseid"=>$courseid)),get_string('tab_competence_overview','block_exacomp'));
					if($crosssubs)
						$rows[] = new tabobject('tab_cross_subjects', new moodle_url('/blocks/exacomp/cross_subjects.php', array("courseid"=>$courseid)), get_string('tab_cross_subjects', 'block_exacomp'));
					if ($courseSettings->uses_activities && $usedetailpage)
						$rows[] = new tabobject('tab_competence_details', new moodle_url('/blocks/exacomp/competence_detail.php',array("courseid"=>$courseid)),get_string('tab_competence_details','block_exacomp'));
					if($courseSettings->nostudents != 1) {
					    $profile = new tabobject('tab_competence_profile', new moodle_url('/blocks/exacomp/competence_profile.php', array("courseid"=>$courseid)), get_string('tab_competence_profile',  'block_exacomp'));
					    $rows[] = $profile;
					}
				}
				if (block_exacomp_is_activated($courseid))
					$rows[] = new tabobject('tab_competence_grid', new moodle_url('/blocks/exacomp/competence_grid.php',array("courseid"=>$courseid)),get_string('tab_competence_grid','block_exacomp'));
					
				if($ready_for_use && $courseSettings->nostudents != 1){
					$rows[] = new tabobject('tab_learning_agenda', new moodle_url('/blocks/exacomp/learningagenda.php',array("courseid"=>$courseid)),get_string('tab_learning_agenda','block_exacomp'));
					//if(block_exacomp_moodle_badges_enabled() && $usebadges)
						//$rows[] = new tabobject('tab_badges', new moodle_url('/blocks/exacomp/my_badges.php',array("courseid"=>$courseid)),get_string('tab_badges','block_exacomp'));
				}
				if($de && !$skillmanagement)
					$rows[] = new tabobject('tab_help', new moodle_url('/blocks/exacomp/help.php', array("courseid"=>$courseid)), get_string('tab_help', 'block_exacomp'));
			}
		}
	}
	
	return $rows;
}

function block_exacomp_build_breadcrum_navigation($courseid) {
	global $PAGE;
	$coursenode = $PAGE->navigation->find($courseid, navigation_node::TYPE_COURSE);
	$blocknode = $coursenode->add(get_string('pluginname','block_exacomp'));
	$coursenode->add($blocknode);
	$blocknode->make_active();
}

class block_exacomp_url extends moodle_url {
}

/**
 * Generates html dropdown for students
 * 
 * @param array $students
 * @param object $selected
 * @param moodle_url $url
 */
define('BLOCK_EXACOMP_STUDENT_SELECTOR_OPTION_EDITMODE', 1);
define('BLOCK_EXACOMP_STUDENT_SELECTOR_OPTION_OVERVIEW_DROPDOWN', 2);
function block_exacomp_studentselector($students, $selected, $url, $option = null) {
	global $CFG;

	$studentsAssociativeArray = array();
    
    // make copy
    $url = new block_exacomp_url($url);
    $url->remove_params('studentid');
	
	if ($option == BLOCK_EXACOMP_STUDENT_SELECTOR_OPTION_EDITMODE || $option == BLOCK_EXACOMP_STUDENT_SELECTOR_OPTION_OVERVIEW_DROPDOWN)
		$studentsAssociativeArray[0]=get_string('no_student_edit', 'block_exacomp');
	else 
		$studentsAssociativeArray[0]=get_string('no_student', 'block_exacomp');
		
	foreach($students as $student) {
		$studentsAssociativeArray[$student->id] = fullname($student);
	}
	
	if ($option == BLOCK_EXACOMP_STUDENT_SELECTOR_OPTION_OVERVIEW_DROPDOWN) {
		$studentsAssociativeArray[BLOCK_EXACOMP_SHOW_ALL_STUDENTS] = get_string('allstudents', 'block_exacomp');
		$studentsAssociativeArray[BLOCK_EXACOMP_SHOW_STATISTIC] = get_string('statistic', 'block_exacomp');
    }
	
	return html_writer::select($studentsAssociativeArray, 'exacomp_competence_grid_select_student',$selected,true,
			array("data-url"=>$url));
}
/**
 *
 * Check if school specific import is enabled
 */
function block_exacomp_check_customupload() {
	/*
	$context = context_system::instance();

	foreach (get_user_roles($context) as $role) {
		if($role->shortname == "exacompcustomupload")
			return true;
	}

	return false;*/
	global $specificimport;
	
	return $specificimport;
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
/**
 * Gets a subject's schooltype title
 * 
 * @param object $subject
 * @return Ambigous <mixed, boolean>
 */
function block_exacomp_get_schooltype_title_by_subject($subject){
	global $DB;
	$subject = $DB->get_record(DB_SUBJECTS, array('id'=>$subject->id));
	return $DB->get_field(DB_SCHOOLTYPES, "title", array("id"=>$subject->stid));
}
/**
 * Get a schooltype by subject
 * 
 * @param unknown_type $subject
 */
function block_exacomp_get_schooltype_by_subject($subject){
    global $DB;
    return $DB->get_record(DB_SCHOOLTYPES, array("id"=>$subject->stid));
}
/**
 * Gets a topic's category
 * 
 * @param object $topic
 */
function block_exacomp_get_category($topic){
	global $DB;
	if(isset($topic->catid))
		return $DB->get_record(DB_CATEGORIES,array("id"=>$topic->catid));
}
/**
 * Gets a niveau
 *
 * @param object $niveau
 */
function block_exacomp_get_niveau($niveauid){
	global $DB;
	return $DB->get_record(DB_NIVEAUS,array("id"=>$niveauid));
}
/**
 * Gets assigned schooltypes for particular courseid
 * 
 * @param int $typeid
 * @param int $courseid
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
	
	block_exacomp_clean_course_topics($values, $courseid);
}

function block_exacomp_clean_course_topics($values, $courseid){
	global $DB;
	
	if($courseid == 0)
		$coutopics = $DB->get_records(DB_COURSETOPICS);
	else 
		$coutopics = $DB->get_records(DB_COURSETOPICS, array('courseid'=>$courseid));
		
	foreach($coutopics as $coutopic){
		$sql = 'SELECT s.stid FROM {'.DB_TOPICS.'} t 
			JOIN {'.DB_SUBJECTS.'} s ON t.subjid=s.id 
			WHERE t.id=?';
		
		$schooltype = $DB->get_record_sql($sql, array($coutopic->topicid));
		
		if(!array_key_exists($schooltype->stid, $values)){
			$DB->delete_records(DB_COURSETOPICS, array('id'=>$coutopic->id));
		}
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

	$old_course_settings = block_exacomp_get_settings_by_course($courseid);
	
	$DB->delete_records(DB_SETTINGS, array("courseid" => $courseid));

	if ($settings->grading > SETTINGS_MAX_SCHEME) $settings->grading = SETTINGS_MAX_SCHEME;

	//adapt old evaluation to new scheme
	//update compcompuser && compcompuser_mm && exameval
	if($old_course_settings->grading != $settings->grading){
		//block_exacompcompuser
		$records = $DB->get_records(DB_COMPETENCIES, array('courseid'=>$courseid));
		foreach($records as $record){
			//if value is set and greater than zero->adapt to new scheme
			if(isset($record->value) && $record->value > 0){
				//calculate old percentage and apply it to new scheme
				$percent_old = $record->value / $old_course_settings->grading;
				$value_new = round($settings->grading * $percent_old);
				
				$update = new stdClass();
				$update->id = $record->id;
				$update->value = $value_new;
				$DB->update_record(DB_COMPETENCIES, $update);
				
			}
		}
		
		//block_exacompcompuser_mm
		$records = $DB->get_records_sql('
			SELECT comp.id, comp.value 
			FROM {'.DB_COMPETENCIES_USER_MM.'} comp 
			JOIN {course_modules} cm ON comp.activityid=cm.id
			WHERE cm.course=?', array($courseid));
		
		foreach($records as $record){
			if(isset($record->value) && $record->value > 0){
				//calculate old percentage and apply it to new scheme
				$percent_old = $record->value / $old_course_settings->grading;
				$value_new = round($settings->grading * $percent_old);
				
				$update = new stdClass();
				$update->id = $record->id;
				$update->value = $value_new;
				$DB->update_record(DB_COMPETENCIES_USER_MM, $update);
			}
		}
		
		//block_exacompexampeval
		$records = $DB->get_records(DB_EXAMPLEEVAL, array('courseid'=>$courseid));
		foreach($records as $record){
			$update = new stdClass();
			$update->id = $record->id;
			
			$doteacherupdate = false;
			if(isset($record->teacher_evaluation) && $record->teacher_evaluation > 0){
				//calculate old percentage and apply it to new scheme
				$percent_old =  $record->teacher_evaluation / $old_course_settings->grading;
				$teachereval_new = round($settings->grading * $percent_old);	
				
				$update->teacher_evaluation = $teachereval_new;
				$doteacherupdate = true;
			}
			$dostudentupdate = false;
			if(isset($record->student_evaluation) && $record->student_evaluation > 0){
				//calculate old percentage and apply it to new scheme
				$percent_old =  $record->student_evaluation / $old_course_settings->grading;
				$studenteval_new = round($settings->grading * $percent_old);	
				
				$update->student_evaluation = $studenteval_new;
				$dostudentupdate = true;
			}
			
			if($dostudentupdate || $doteacherupdate)
				$DB->update_record(DB_EXAMPLEEVAL, $update);
		}
		
	}
	
	$settings->courseid = $courseid;
	$settings->tstamp = time();

	$DB->insert_record(DB_SETTINGS, $settings);
}
/**
 *
 * Check if there are already topics assigned to a course
 * @param int $courseid
 */
function block_exacomp_is_activated($courseid) {
	global $DB;

	return $DB->get_records(DB_COURSETOPICS, array("courseid" => $courseid));
}
/**
 *	Check is block is ready for use.
 *	It is ready if:
 *	1. block is activated and no activites are used in the course
 *	or
 *	2. block is activated, activities are used and associated
 * 
 */
function block_exacomp_is_ready_for_use($courseid){
	
	global $DB;
	$course_settings = block_exacomp_get_settings_by_course($courseid);
	$is_activated = block_exacomp_is_activated($courseid);
	
	//no topics selected
	if(!$is_activated)
		return false;
	
	return true;
	
	//topics selected
	//no activities->finish
	if(!$course_settings->uses_activities)
		return true;
	
	if($course_settings->show_all_descriptors)
		return true;
	
	//work with activities
	$activities_assigned_to_any_course = $DB->get_records(DB_COMPETENCE_ACTIVITY, array('eportfolioitem'=>0));
	//no activites assigned
	if(!$activities_assigned_to_any_course)
		return false;
			
	//activity assigned in given course
	foreach($activities_assigned_to_any_course as $activity){
		$module = $DB->get_record('course_modules', array('id'=>$activity->activityid));
		if(isset($module->course) && $module->course == $courseid)
			return true;
	}

	//no activity assigned in given course
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
function block_exacomp_get_output_fields($topic, $show_category=false, $isTopic = true) {
	global $version, $DB;

	if (preg_match('!^([^\s]*[0-9][^\s]*+)\s+(.*)$!iu', $topic->title, $matches)) {
		//$output_id = $matches[1];
		$output_id = '';
		$output_title = $matches[2];
	} else {
		$output_id = '';
		$output_title = $topic->title;
	}
	
	return array($output_id, $output_title);
}
/**
 *
 * Awards badges to user
 * @param int $courseid
 * @param int $userid
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

	//$badges = badges_get_badges(BADGE_TYPE_COURSE, $courseid);
	$badges = badges_get_badges(BADGE_TYPE_COURSE);

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
	$tree = block_exacomp_get_competence_tree($courseid, null, false, null, true, block_exacomp_get_settings_by_course($courseid)->filteredtaxonomies);

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
	global $DB;
	
	$assign = $DB->get_record('modules', array('name'=>'assign'));
	$forum = $DB->get_record('modules', array('name'=>'forum'));
	$glossary = $DB->get_record('modules', array('name'=>'glossary'));
	$quiz = $DB->get_record('modules', array('name'=>'quiz'));
	$wiki = $DB->get_record('modules', array('name'=>'wiki'));
	$url = $DB->get_record('modules', array('name'=>'url'));
	$lesson = $DB->get_record('modules', array('name'=>'lesson'));
	//do not change order, this affects visible modules
	return array($assign->id, $forum->id, $glossary->id, $quiz->id, $wiki->id, $url->id, $lesson->id);
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
 * Returns an associative array that gives information about which competence/topic is
 * associated with which eportfolioitem
 *
 * $array[$studentid->competencies[compid]->items[$itemid]->name = artefact name
 * 
 * $array[$studentid->competencies[compid]->items[$itemid]->shared = shared or not 
 *
 * @param int $courseid
 * @return array
 */

function block_exacomp_get_eportfolioitem_association($students){
	global $DB, $COURSE, $USER;
	//$teachers = block_exacomp_get_teachers_by_course($COURSE->id);
	$result = array();
	foreach($students as $student){
		$eportfolioitems = $DB->get_records_sql('
			SELECT mm.id, compid, activityid, i.shareall, i.externaccess, i.name
			FROM {'.DB_COMPETENCE_ACTIVITY.'} mm
			JOIN {block_exaportitem} i ON mm.activityid=i.id
			WHERE mm.eportfolioitem = 1 AND i.userid=?
			ORDER BY compid', array($student->id));
 		
		$result[$student->id] = new stdClass();
		$result[$student->id]->competencies = array();
		
		foreach($eportfolioitems as $item){
			$shared = false;
			$viewid = 0;
			$owner = 0;
			$useextern = false;
			$hash = 0;
			 
			$sql = '
				SELECT vs.userid, v.shareall, v.externaccess, v.id, v.hash, v.userid as owner FROM {block_exaportviewblock} vb 
				JOIN {block_exaportview} v ON vb.viewid=v.id 
				LEFT JOIN {block_exaportviewshar} vs ON vb.viewid=vs.viewid
				WHERE vb.itemid = ?';
			
			$shared_info = $DB->get_records_sql($sql, array($item->activityid));
			
			foreach($shared_info as $info){
				if((isset($info->shareall) && $info->shareall>0)){
					$shared = true;
					$useextern = false;
					$hash = $info->hash;
					$viewid = $info->id;
					$owner = $info->owner;
					continue;
				} 
				if(isset($info->externaccess)&& $info->externaccess>0){
					$shared= true;
					$useextern = true;
					$hash = $info->hash;
					$viewid = $info->id;
					$owner = $info->owner;
					continue;
				}
				
			}
			if(!$shared){
				//foreach($teachers as $teacher){
					foreach($shared_info as $info){
						if(isset($info->userid) && $USER->id == $info->userid){
							$shared=true;
							$useextern = false;
							$hash = $info->hash;
							$viewid = $info->id;
							$owner = $info->owner;
							continue;
						}
					}
				//}
			}
			if(!isset($result[$student->id]->competencies[$item->compid])){
				$result[$student->id]->competencies[$item->compid] = new stdClass();
				$result[$student->id]->competencies[$item->compid]->items = array();
			}
			$result[$student->id]->competencies[$item->compid]->items[$item->activityid] = new stdClass();
			$result[$student->id]->competencies[$item->compid]->items[$item->activityid]->shared = $shared;
			$result[$student->id]->competencies[$item->compid]->items[$item->activityid]->name = $item->name;
			$result[$student->id]->competencies[$item->compid]->items[$item->activityid]->viewid = $viewid;
			$result[$student->id]->competencies[$item->compid]->items[$item->activityid]->owner = $owner;
			$result[$student->id]->competencies[$item->compid]->items[$item->activityid]->useextern = $useextern;
			$result[$student->id]->competencies[$item->compid]->items[$item->activityid]->hash = $hash;	
		}
	}
	return $result;
}
/**
 * Prepares an icon for a student for the given course modules, based on the grading.

 * @param array $coursemodules
 * @param stdClass $student
 *
 * @return stdClass $icon
 */
function block_exacomp_get_icon_for_user($coursemodules, $student, $supported) {
	global $CFG, $DB;
	require_once $CFG->libdir . '/gradelib.php';
	
	$found = false;
	$modules = $DB->get_records_menu("modules");

	$icon = new stdClass();
	$icon->text = fullname($student) . get_string('usersubmitted','block_exacomp') . '&#013;';
	
	foreach ($coursemodules as $cm) {
		$hasSubmission = false;
		if(!in_array($cm->module, $supported))
			continue;
		
		$gradeinfo = grade_get_grades($cm->course,"mod",$modules[$cm->module],$cm->instance,$student->id);

		//check for assign
		if($cm->module == $supported[0]) {
			$hasSubmission = $DB->get_record('assign_submission', array('assignment' => $cm->instance, 'userid' => $student->id));
		}
		
		if(isset($gradeinfo->items[0]->grades[$student->id]->dategraded) || $hasSubmission) {
			$found = true;
			$icon->img = html_writer::empty_tag("img", array("src" => "pix/list_12x11.png","alt" => get_string("legend_activities","block_exacomp")));
			$icon->text .= '* ' . str_replace('"', '', $gradeinfo->items[0]->name) . ((isset($gradeinfo->items[0]->grades[$student->id])) ? get_string('grading', "block_exacomp"). $gradeinfo->items[0]->grades[$student->id]->str_long_grade : '' ) . '&#013;';
		}
	}
	if(!$found) {
		$icon->text = fullname($student) . get_string("usernosubmission","block_exacomp");
		$icon->img = html_writer::empty_tag("img", array("src" => "pix/x_11x11.png","alt" => fullname($student) . get_string("usernosubmission","block_exacomp")));
	}

	return $icon;
}

function block_exacomp_get_icon_data($courseid, $students) {
	global $DB,$CFG;
	require_once $CFG->libdir . '/gradelib.php';
	
	$mods = $DB->get_records_sql("
			SELECT activityid as cmid
			FROM mdl_block_exacompcompactiv_mm mm
			JOIN mdl_course_modules m ON m.id = mm.activityid
			WHERE m.course = ? AND mm.eportfolioitem = 0
            GROUP BY activityid", array($courseid));
	
	$icondata = array();
	
	foreach($mods as $mod) {
		$gradeinfo = grade_get_grades($courseid,"mod",$mod->cmid,$cm->instance,array_keys($students));
		$icondata[$mod->cmid] = $gradeinfo->grades;
	}
	
	return $icondata;
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
	
	$visibilities = $DB->get_fieldset_select(DB_DESCVISIBILITY,'descrid', 'courseid=? AND studentid=0', array($courseid));
	
	//get all cross subject descriptors - to support cross-course subjects descriptor visibility must be kept
	$cross_subjects = $DB->get_records(DB_CROSSSUBJECTS, array('courseid'=>$courseid));
	$cross_subjects_descriptors = array();
	foreach($cross_subjects as $crosssub){
		$cross_subject_descriptors = $DB->get_fieldset_select(DB_DESCCROSS, 'descrid', 'crosssubjid=?', array($crosssub->id));
		foreach($cross_subject_descriptors as $descriptor)
		if(!in_array($descriptor, $cross_subjects_descriptors)){
			$cross_subjects_descriptors[] = $descriptor;
		}
	}
	
	$descriptors = array();
	if(isset($values)){
		foreach ($values as $value) {
			$topicid = intval($value);
			
			$DB->insert_record(DB_COURSETOPICS, array("courseid" => $courseid, "topicid" => $topicid));
			
			//insert descriptors in block_exacompdescrvisibility
			$descriptors_topic = block_exacomp_get_descriptors_by_topic($courseid, $topicid);
			foreach($descriptors_topic as $descriptor){
				if(!array_key_exists($descriptor->id, $descriptors))
				$descriptors[$descriptor->id] = $descriptor;	
			}
		}
		
		$finaldescriptors=$descriptors;
		//manage visibility, do not delete user visibility, but delete unused entries
		foreach($descriptors as $descriptor){
			//new descriptors in table
			if(!in_array($descriptor->id, $visibilities))
				$DB->insert_record(DB_DESCVISIBILITY, array("courseid"=>$courseid, "descrid"=>$descriptor->id, "studentid"=>0, "visible"=>1));
		
			$descriptor->children = block_exacomp_get_child_descriptors($descriptor, $courseid, true, array(SHOW_ALL_TAXONOMIES), true, false);
			
			foreach($descriptor->children as $childdescriptor){
				if(!in_array($childdescriptor->id, $visibilities))
					$DB->insert_record(DB_DESCVISIBILITY, array("courseid"=>$courseid, "descrid"=>$childdescriptor->id, "studentid"=>0, "visible"=>1));
		
				if(!array_key_exists($childdescriptor->id, $finaldescriptors))
					$finaldescriptors[$childdescriptor->id] = $childdescriptor;
			}
		}
		
		foreach($visibilities as $visible){
			//delete ununsed descriptors for course and for special students
			if(!array_key_exists($visible, $finaldescriptors)){
				//check if used in cross-subjects --> then it must still be visible
				if(!in_array($visible, $cross_subjects_descriptors))
					$DB->delete_records(DB_DESCVISIBILITY, array("courseid"=>$courseid, "descrid"=>$visible));
			}
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

	$sql = "SELECT DISTINCT cm.instance as id, cm.id as activityid, q.grade FROM {block_exacompcompactiv_mm} activ "
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
		$context = $DB->get_record('context', array('id'=>$instance->parentcontextid, 'contextlevel'=>50));
		if($context)
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
	$query = 'SELECT mm.id as uniqueid,a.id,ass.grade,a.instance 
	FROM {'.DB_COMPETENCE_ACTIVITY.'} mm  
	INNER JOIN {course_modules} a ON a.id=mm.activityid
	LEFT JOIN {assign} ass ON ass.id=a.instance
	WHERE mm.compid=? AND mm.comptype = ?';

	$condition = array($compid, $comptype);
	
	if ($courseid){
		$query.=" AND a.course=?";
		$condition = array($compid, $comptype, $courseid);
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
function block_exacomp_init_competence_grid_data($courseid, $subjectid, $studentid, $showallexamples = false, $filteredtaxonomies = array(SHOW_ALL_TAXONOMIES)) {
	global $version, $DB;

	if($studentid) {
		$cm_mm = block_exacomp_get_course_module_association($courseid);
		$course_mods = get_fast_modinfo($courseid)->get_cms();
	}

	$selection = array();
	
	/* if($version) {
		$skills = array();
		$subjects = $DB->get_records_menu(DB_SUBJECTS,array("stid" => $subjectid),null,"id, title");
		$niveaus = $DB->get_records_menu(DB_CATEGORIES, array("lvl" => 4),"id,title","id,title");

		$data = array();
		if($studentid)
			$competencies = array("studentcomps"=>$DB->get_records(DB_COMPETENCIES,array("role"=>ROLE_STUDENT,"courseid"=>$courseid,"userid"=>$studentid,"comptype"=>TYPE_TOPIC),"","compid,userid,reviewerid,value"),
					"teachercomps"=>$DB->get_records(DB_COMPETENCIES,array("role"=>ROLE_TEACHER,"courseid"=>$courseid,"userid"=>$studentid,"comptype"=>TYPE_TOPIC),"","compid,userid,reviewerid,value"));

		$selection = array();
		// Arrange data in associative array for easier use
		foreach($subjects as $subjid => $subject) {
			$topics = $DB->get_records('block_exacomptopics',array("subjid"=>$subjid),"catid");
			foreach($topics as $topic) {
				if($topic->catid == 0) continue;

				if($studentid) {
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
	else { */
		$niveaus = block_exacomp_get_niveaus_for_subject($subjectid);
		$skills = $DB->get_records_menu('block_exacompskills',null,null,"id, title");
		$descriptors = block_exacomp_get_descriptors_by_subject($subjectid);
        
		$supported = block_exacomp_get_supported_modules();
		
		$data = array();
		if($studentid)
			$competencies = array("studentcomps"=>$DB->get_records(DB_COMPETENCIES,array("role"=>ROLE_STUDENT,"courseid"=>$courseid,"userid"=>$studentid,"comptype"=>TYPE_DESCRIPTOR),"","compid,userid,reviewerid,value"),
					"teachercomps"=>$DB->get_records(DB_COMPETENCIES,array("role"=>ROLE_TEACHER,"courseid"=>$courseid,"userid"=>$studentid,"comptype"=>TYPE_DESCRIPTOR),"","compid,userid,reviewerid,value"));

		// Arrange data in associative array for easier use
		$topics = array();
		$data = array();
		foreach ($descriptors as $descriptor) {
		    if($descriptor->parentid > 0) {
		        continue;
		    } 
		        
		    $descriptor->children = $DB->get_records('block_exacompdescriptors',array('parentid'=>$descriptor->id));
		    
			$examples = $DB->get_records_sql(
					"SELECT de.id as deid, e.id, e.title, tax.title as tax, e.task, e.externalurl,
					e.externalsolution, e.externaltask, e.solution, e.completefile, e.description, e.taxid, e.attachement, e.creatorid
					FROM {" . DB_EXAMPLES . "} e
					JOIN {" . DB_DESCEXAMP . "} de ON e.id=de.exampid AND de.descrid=?
					LEFT JOIN {" . DB_TAXONOMIES . "} tax ON e.taxid=tax.id"
					. ((!$showallexamples || !in_array(SHOW_ALL_TAXONOMIES, $filteredtaxonomies)) ? " WHERE " : "")
					. (($showallexamples) ? "" : " e.creatorid > 0")
					. ((in_array(SHOW_ALL_TAXONOMIES, $filteredtaxonomies)) ? "" : " e.taxid IN (".implode(",", $filteredtaxonomies) .")" )
					, array($descriptor->id));
			
			$descriptor->examples = $examples;
				
			if($studentid) {
				$descriptor->studentcomp = (array_key_exists($descriptor->id, $competencies['studentcomps'])) ? $competencies['studentcomps'][$descriptor->id]->value : false;
				$descriptor->teachercomp = (array_key_exists($descriptor->id, $competencies['teachercomps'])) ? $competencies['teachercomps'][$descriptor->id]->value : false;
				// ICONS
				if(isset($cm_mm->competencies[$descriptor->id])) {
					//get CM instances
					$cm_temp = array();
					foreach($cm_mm->competencies[$descriptor->id] as $cmid)
						$cm_temp[] = $course_mods[$cmid];
						
					$icon = block_exacomp_get_icon_for_user($cm_temp, $DB->get_record("user",array("id"=>$studentid)), $supported);
					$descriptor->icon = '<span title="'.$icon->text.'" class="exabis-tooltip">'.$icon->img.'</span>';
				}
			}
			$data[$descriptor->skillid][$descriptor->topicid][$descriptor->niveauid][] = $descriptor;
			$topics[$descriptor->topicid] = $descriptor->topic;
		}
		
		//if(block_exacomp_get_settings_by_course($courseid)->show_all_descriptors)
			$selection = $DB->get_records(DB_COURSETOPICS,array('courseid'=>$courseid),'','topicid');
		
		return array($niveaus, $skills, $topics, $data, $selection);
	//}
}
function block_exacomp_get_niveaus_for_subject($subjectid) {
	global $DB;

	$niveaus = "SELECT DISTINCT n.id as id, n.title, n.sorting FROM {block_exacompdescriptors} d, {block_exacompdescrtopic_mm} dt, {block_exacompniveaus} n
	WHERE d.id=dt.descrid AND dt.topicid IN (SELECT id FROM {block_exacomptopics} WHERE subjid=?)
	AND d.niveauid > 0 AND d.niveauid = n.id order by n.sorting, n.id";
	
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
				
				(isset($activity_association->competencies[$descriptor->id]) && array_key_exists($activityid, $activity_association->competencies[$descriptor->id]))?
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
		if(is_enrolled($context, $user, '', true) && has_capability('block/exacomp:student', $context, $user)){
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
	
	foreach ($students as $student){
		if($student->id == $user->id)
			$current_evaluation = $evaluation;
		else 
			$current_evaluation = block_exacomp_get_user_information_by_course($student, $courseid, true);
			
		foreach($topics as $topic){
			if($student->id == $user->id)
				if($coursesettings->show_all_descriptors || ($coursesettings->uses_activities && isset($cm_mm->topics[$topic->id])))
					$total ++;
			
			if(!empty($current_evaluation->topics->teacher)){
				if(isset($current_evaluation->topics->teacher) && isset($current_evaluation->topics->teacher[$topic->id])){
					if($scheme == 1 || $current_evaluation->topics->teacher[$topic->id] >= ceil($scheme/2)){
						if($student->id == $user->id)
							$reached ++;
						else
							$average ++;
					}
						
				}
			}
		}
		foreach($descriptors as $descriptor){
			if(block_exacomp_descriptor_visible($courseid, $descriptor, $student->id)){
				if($student->id == $user->id)
					if($coursesettings->show_all_descriptors || ($coursesettings->uses_activities && isset($cm_mm->competencies[$descriptor->id])))
						$total ++;
			
				if(!empty($current_evaluation->competencies->teacher)){ 
					if(isset($current_evaluation->competencies->teacher) && isset($current_evaluation->competencies->teacher[$descriptor->id])){
						if($scheme == 1 || $current_evaluation->competencies->teacher[$descriptor->id] >= ceil($scheme/2))
							if($student->id == $user->id)
								$reached ++;
							else
								$average ++;
					}
				}
			}
		}
	}
			
	if(count($students) > 0)
		$average = intval(ceil(($average+$reached)/count($students)));
	else
		$average = 0;
	
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
	$profile_settings = block_exacomp_get_profile_settings($userid);
	$user_courses = array();
	foreach(block_exacomp_get_exacomp_courses($userid) as $course){
		if(isset($profile_settings->exacomp[$course->id]))
			$user_courses[$course->id] = $course; 
	}
	foreach($user_courses as $course) {
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
		$total = (isset($subject->topics)? count($subject->topics):0) + (isset($subject->competencies)? count($subject->competencies) : 0);
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
		$totalDescr = block_exacomp_get_descriptors_by_topic($courseid, $topic->id, false, true);
		
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
function block_exacomp_get_competencies_for_pie_chart($courseid,$user, $scheme, $enddate=0, $exclude_student=false) {
	
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
					if($enddate>0){
						if($enddate >= $evaluation->topics->timestamp_teacher[$topic->id]){
							$teachercomp ++;
							$teacher_eval = true;
						}
					}else{
						$teachercomp ++;
						$teacher_eval = true;
					}
				}
			}
			if((!$teacher_eval||$exclude_student) && isset($evaluation->topics->student) && isset($evaluation->topics->student[$topic->id])){
				if($scheme == 1 || $evaluation->topics->student[$topic->id] >= ceil($scheme/2)){
					if($enddate>0){
						if($enddate >= $evaluation->topics->timestamp_student[$topic->id]){
							$studentcomp ++;
							$student_eval = true;
						}
					}else{
						$studentcomp ++;
						$student_eval = true;
					}
				}
			}
			if(!$teacher_eval && !$student_eval)	
				$pendingcomp ++;
		}
	}
	foreach($descriptors as $descriptor){
		if(block_exacomp_descriptor_visible($courseid, $descriptor, $user->id)){
			$teacher_eval = false;
			$student_eval = false;
			if(!$coursesettings->uses_activities || ($coursesettings->uses_activities && isset($cm_mm->competencies[$descriptor->id]))){
				if(isset($evaluation->competencies->teacher) && isset($evaluation->competencies->teacher[$descriptor->id])){
					if($scheme == 1 || $evaluation->competencies->teacher[$descriptor->id] >= ceil($scheme/2)){
						if($enddate>0){
							//compare only enddate->kumuliert
							if($enddate>=$evaluation->competencies->timestamp_teacher[$descriptor->id]){
								$teachercomp ++;
								$teacher_eval = true;
							}
						}else{
							$teachercomp ++;
							$teacher_eval = true;
						}
					}
				}
				if((!$teacher_eval||$exclude_student) && isset($evaluation->competencies->student) && isset($evaluation->competencies->student[$descriptor->id])){
					if($scheme == 1 || $evaluation->competencies->student[$descriptor->id] >= ceil($scheme/2)){
						if($enddate>0){
							if($enddate >= $evaluation->competencies->timestamp_student[$descriptor->id]){
								$studentcomp ++;
								$student_eval = true;
							}
						}else{
							$studentcomp ++;
							$student_eval = true;
						}
					}
				}
				if(!$teacher_eval && !$student_eval) 	
					$pendingcomp ++;
			}
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
function block_exacomp_get_exastud_periods($userid = 0){
	global $USER, $DB;
	if($userid == 0)
		$userid = $USER->id;
	
	$sql = "SELECT p.id,p.description FROM {block_exastudreview} r, {block_exastudperiod} p WHERE r.student_id = ? AND r.periods_id = p.id GROUP BY p.id";
	return $DB->get_records_sql($sql,array("studentid"=>$userid));
}
function block_exacomp_get_exaport_items($userid = 0){
	global $USER, $DB;
	if($userid == 0)
		$userid = $USER->id;
	
	$items = $DB->get_records('block_exaportitem',array("userid"=>$userid,"isoez" => 0));
	//if a teacher accesses a competence profile he should only see the views that are shared with him
	if($userid != $USER->id) {
		$teacherViews = $DB->get_fieldset_select('block_exaportviewshar', 'viewid', 'userid = ?',array($USER->id));
		if(!$teacherViews)
			return array();
		
		$teacherViews = implode(',',$teacherViews);
		
		foreach($items as $item) {
			//check if item is in one of the teacher views
			$sql = "SELECT * FROM {block_exaportviewblock} vb
				WHERE vb.type = 'item' AND vb.itemid = $item->id
				AND vb.viewid IN ($teacherViews)";
			$result = $DB->get_records_sql($sql);
			if(!$result)
				unset($items[$item->id]);
		}
	}
	
	return $items;
}
function block_exacomp_get_profile_settings($userid = 0){
	global $USER, $DB;
	
	if($userid == 0)
		$userid = $USER->id;
	
	$profile_settings = new stdClass();
	
	$profile_settings->exacomp = array();
	$exacomp_settings = $DB->get_records(DB_PROFILESETTINGS, array('block'=>'exacomp', 'userid'=>$userid));
	foreach($exacomp_settings as $setting){
		$profile_settings->exacomp[$setting->itemid] = $setting;
	}
	
	$profile_settings->exastud = array();
	$exastud_settings = $DB->get_records(DB_PROFILESETTINGS, array('block'=>'exastud', 'userid'=>$userid));
	foreach($exastud_settings as $setting){
		$profile_settings->exastud[$setting->itemid] = $setting;
	}
	
	$profile_settings->showonlyreached=0;
	$showonlyreached = $DB->get_field(DB_PROFILESETTINGS, 'itemid' ,array('block'=>'exacompdesc', 'userid'=>$userid));
	if($showonlyreached && $showonlyreached == 1)
		$profile_settings->showonlyreached = 1;
	
	$profile_settings->useexaport = 0;
	$useexaport = $DB->get_field(DB_PROFILESETTINGS, 'itemid', array('block'=>'useexaport', 'userid'=>$userid));
	if($useexaport && $useexaport == 1)
		$profile_settings->useexaport = 1;
		
	$profile_settings->useexastud = 0;	
 	$useexastud = $DB->get_field(DB_PROFILESETTINGS, 'itemid', array('block'=>'useexastud', 'userid'=>$userid));
	if($useexastud && $useexastud == 1)
		$profile_settings->useexastud = 1;
		
	$profile_settings->usebadges = 0;
	$usebadges = $DB->get_field(DB_PROFILESETTINGS, 'itemid', array('block'=>'usebadges', 'userid'=>$userid));
 	if($usebadges && $usebadges == 1)
		$profile_settings->usebadges = 1;
	
	$profile_settings->onlygainedbadges = 0;
	$onlygainedbadges = $DB->get_field(DB_PROFILESETTINGS, 'itemid', array('block'=>'badges', 'userid'=>$userid));
	if($onlygainedbadges && $onlygainedbadges == 1)
		$profile_settings->onlygainedbadges = 1;
		
	$profile_settings->showallcomps = 0;
	$showallcomps = $DB->get_field(DB_PROFILESETTINGS, 'itemid', array('block'=>'all', 'userid'=>$userid));
	if($showallcomps && $showallcomps == 1)
		$profile_settings->showallcomps = 1;	
		
	return $profile_settings;
}

function block_exacomp_reset_profile_settings($userid){
	global $DB;
	$DB->delete_records(DB_PROFILESETTINGS, array('userid'=>$userid));
}
	
function block_exacomp_set_profile_settings($userid, $showonlyreached, $usebadges, $onlygainedbadges, $showallcomps, $useexaport, $useexastud, $courses, $periods){
	global $DB;
	//showonlyreached
	$insert = new stdClass();
	$insert->block = 'exacompdesc';
	$insert->itemid = intval($showonlyreached);
	$insert->feedback = '';
	$insert->userid = $userid;
	
	$DB->insert_record(DB_PROFILESETTINGS, $insert);
	
	//usebadges
	$insert = new stdClass();
	$insert->block = 'usebadges';
	$insert->itemid = intval($usebadges);
	$insert->feedback = '';
	$insert->userid = $userid;
	
	$DB->insert_record(DB_PROFILESETTINGS, $insert);
	
	//onlygainedbadges
	$insert = new stdClass();
	$insert->block = 'badges';
	$insert->itemid = intval($onlygainedbadges);
	$insert->feedback = '';
	$insert->userid = $userid;
	
	$DB->insert_record(DB_PROFILESETTINGS, $insert);
	
	//showallcomps
	$insert = new stdClass();
	$insert->block = 'all';
	$insert->itemid = intval($showallcomps);
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
			$reviews[$period->id]->descriptors[$comp->categoryid]->evaluation = isset($comp->value) ? $comp->value : 0;
		}
		
	}
	return $reviews;
}
function block_exacomp_set_tipp($compid, $user, $type, $scheme){
	global $COURSE;
	//$user_information = block_exacomp_get_user_information_by_course($user, $COURSE->id);
	
	$show_tipp = false;
	foreach($user->{$type}->activities as $activity){
		if(isset($activity->teacher[$compid]) && $activity->teacher[$compid]>= ceil($scheme/2) )
			$show_tipp = true;
	}
	return $show_tipp;
}
function block_exacomp_get_tipp_string($compid, $user, $scheme, $type, $comptype){
	global $COURSE;
	$activities = block_exacomp_get_activities($compid, $COURSE->id, $comptype);
	$user_information = block_exacomp_get_user_information_by_course($user, $COURSE->id);
	
	$gained = 0;
	$total = count($activities);
	
	foreach($activities as $activity){
		if(isset($user_information->{$type}->activities[$activity->id]->teacher[$compid]) 
			&& $user_information->{$type}->activities[$activity->id]->teacher[$compid] >= ceil($scheme/2)){
				$gained++;
		}
	}
	
	return get_string('teacher_tipp_1', 'block_exacomp').$total.get_string('teacher_tipp_2', 'block_exacomp').$gained.get_string('teacher_tipp_3', 'block_exacomp');
}
/**
 * 
 * Gets tree with schooltype on highest level
 * @param unknown_type $courseid
 */
function block_exacomp_build_schooltype_tree($courseid=0){
	global $version,$skillmanagement;
	$schooltypes = block_exacomp_get_schooltypes_by_course($courseid);
	
	foreach($schooltypes as $schooltype){
		$subjects = block_exacomp_get_subjects_for_schooltype($courseid, $schooltype->id);
		
		$schooltype->subs = array();
		foreach($subjects as $subject){
			$param = $courseid;
			$tree = block_exacomp_get_competence_tree($param, $subject->id, true);
			$schooltype->subs += $tree;
		}
	}
	
	return $schooltypes;
}
/**
 * This function is used for ePop, to test for the latest db update.
 * It is used after every xml import and every example upload.
 */
function block_exacomp_settstamp(){

	global $DB;
	$sql="SELECT * FROM {block_exacompsettings} WHERE courseid=0 AND activities='importxml'";

	$modsetting = $DB->get_record('block_exacompsettings', array('courseid'=>0,'activities'=>'importxml'));
	if ($modsetting){
		$modsetting->tstamp = time();
		$DB->update_record('block_exacompsettings', $modsetting);
	}else{
		$DB->insert_record('block_exacompsettings',array("courseid" => 0,"grading"=>"0","activities"=>"importxml","tstamp"=>time()));
	}
}
/**
 * This function checkes for finished quizes that are associated with competencies and automatically gains them if the
 * coresponding setting is activated.
 */
function block_exacomp_perform_auto_test() {
	global $DB;
	
	$autotest = get_config('exacomp', 'autotest');
	$testlimit = get_config('exacomp', 'testlimit');
	
	if(!$autotest)
		return;
	
	//for all courses where exacomp is used
	$courses = block_exacomp_get_courses();
		
	foreach($courses as $courseid){
		//tests associated with competences
		//get all tests that are associated with competences
		$tests = block_exacomp_get_active_tests_by_course($courseid);
		$students = block_exacomp_get_students_by_course($courseid);
			
		$grading_scheme = block_exacomp_get_grading_scheme($courseid);
	
		//get student grading for each test
		foreach($students as $student){
			foreach($tests as $test){
				//get grading for each test and assign topics and descriptors
				$quiz = $DB->get_record('quiz_grades', array('quiz'=>$test->id, 'userid'=>$student->id));
				if(isset($quiz->grade) && (floatval($test->grade)*(floatval($testlimit)/100)) <= $quiz->grade){
					//assign competences to student
					if(isset($test->descriptors)){
						foreach($test->descriptors as $descriptor){
							block_exacomp_set_user_competence($student->id, $descriptor->compid,
									0, $courseid, ROLE_TEACHER, $grading_scheme);
							mtrace("set competence ".$descriptor->compid." for user ".$student->id.'<br>');
						}
					}
					if(isset($test->topics)){
						foreach($test->topics as $topic){
							block_exacomp_set_user_competence($student->id, $topic->compid,
									1, $courseid, ROLE_TEACHER, $grading_scheme);
							mtrace("set topic competence ".$topic->compid." for user ".$student->id.'<br>');
	
						}
					}
				}
			}
		}
	}
	return true;
}
function block_exacomp_get_timeline_data($courses, $student, $total){
	global $DB;
	$max_timestamp = 0;
	$min_timestamp = time();
	$no_data = true;
	foreach($courses as $course){
		
		$topics = block_exacomp_get_topics_by_course($course->id);
		$descriptors = block_exacomp_get_descriptors($course->id);
		
		$teacher_competencies = $DB->get_records(DB_COMPETENCIES, array('userid'=>$student->id, 'role'=>ROLE_TEACHER, 'value'=>1, 'courseid'=>$course->id));
		
		foreach($teacher_competencies as $competence){
			$no_data = false;
			if($competence->comptype == TYPE_DESCRIPTOR){
				foreach($descriptors as $descriptor){
					if(block_exacomp_descriptor_visible($course->id, $descriptor, $student->id)){
						if($descriptor->id == $competence->compid){
							if($competence->timestamp != null && $competence->timestamp<$min_timestamp)
								$min_timestamp = $competence->timestamp;
						}
					}
				}
			} 
			if($competence->comptype == TYPE_TOPIC) {
				foreach($topics as $topic){
					if($topic->id == $competence->compid){
						if($competence->timestamp != null && $competence->timestamp<$min_timestamp)
							$min_timestamp = $competence->timestamp;
					}
				}
			}
		}
		$student_competencies = $DB->get_records(DB_COMPETENCIES, array('userid'=>$student->id, 'role'=>ROLE_STUDENT, 'value'=>1, 'courseid'=>$course->id));
		
		foreach($student_competencies as $competence){
			$no_data = false;
			if($competence->comptype == TYPE_DESCRIPTOR){
				foreach($descriptors as $descriptor){
					if(block_exacomp_descriptor_visible($courseid, $descriptor, $student->id)){
						if($descriptor->id == $competence->compid){
							if($competence->timestamp != null && $competence->timestamp<$min_timestamp)
								$min_timestamp = $competence->timestamp;
						}
					}
					
				}
			} 
			if($competence->comptype == TYPE_TOPIC) {
				foreach($topics as $topic){
					if($topic->id == $competence->compid){
						if($competence->timestamp != null && $competence->timestamp<$min_timestamp)
							$min_timestamp = $competence->timestamp;
					}
				}
			}
		}
	}
	
	if(!$no_data){
		$max_timestamp = time();
		$time_diff = $max_timestamp - $min_timestamp;
		
		$x_values = array();
		$y_values_teacher = array();
		$y_values_student = array();
		$y_values_total = array();
		//Weeks
		if($time_diff < 10519200 && $time_diff >= 2419200){
			$weeks = array();
			
			$comp_timestamp = $min_timestamp - 604800;
			while($comp_timestamp <= $max_timestamp){
				$result = block_exacomp_calc_week_dates($comp_timestamp);
				$comp_timestamp = $result->dates[0][0]+604800;
				$weeks[] = $result;
			}
			foreach($weeks as $week){
				$teacher_comps = 0;
				$student_comps = 0;
				foreach($courses as $course){
					$scheme = block_exacomp_get_grading_scheme($course->id); 
					$comps = block_exacomp_get_competencies_for_pie_chart($course->id, $student, $scheme, $week->dates[6][0], true);
					$teacher_comps+=$comps[0];
					$student_comps+=$comps[1];
				}
				$x_values[] = $week->label;
				$y_values_teacher[] = $teacher_comps;
				$y_values_student[] = $student_comps;
				$y_values_total[] = $total;
			}
		}else if($time_diff<2419200){ //Days
			
			$min_date = getdate($min_timestamp);
			$max_date = getdate($max_timestamp);
			$min_timestamp = strtotime($min_date["mday"]."-".$min_date["mon"]."-".$min_date["year"]." 23:59");
			$max_timestamp = strtotime($max_date["mday"]."-".$max_date["mon"]."-".$max_date["year"]." 23:59");
			$act_time = $min_timestamp-86400;
			
			while($act_time<=$max_timestamp){
				
				$act_date = getdate($act_time);
				
				$teacher_comps = 0;
				$student_comps = 0;
				foreach($courses as $course){
					$scheme = block_exacomp_get_grading_scheme($course->id); 
					$comps = block_exacomp_get_competencies_for_pie_chart($course->id, $student, $scheme, $act_time, true);
					$teacher_comps+=$comps[0];
					$student_comps+=$comps[1];
				}
				$x_values[] = $act_date["mday"].".".$act_date["mon"];
				$y_values_teacher[] = $teacher_comps;
				$y_values_student[] = $student_comps;
				$y_values_total[] = $total;
				
				$act_time += 86400; 
			}
		}else if ($time_diff<63072000){	//month
			$month_end = strtotime('last day of this month', $min_timestamp);
			$min_date = getdate($month_end);
			$min_timestamp = strtotime($min_date["mday"]."-".$min_date["mon"]."-".$min_date["year"]." 23:59");
			$month_end = strtotime('last day of this month', $max_timestamp);
			$max_date = getdate($month_end);
			$max_timestamp = strtotime($max_date["mday"]."-".$max_date["mon"]."-".$max_date["year"]." 23:59");
			
			$act_time = strtotime('last day of this month', $min_timestamp-(86400*$max_date["mday"]));
			
			while($act_time<=$max_timestamp){
				
				$act_date = getdate($act_time);
			
				$teacher_comps = 0;
				$student_comps = 0;
				foreach($courses as $course){
					$scheme = block_exacomp_get_grading_scheme($course->id); 
					$comps = block_exacomp_get_competencies_for_pie_chart($course->id, $student, $scheme, $act_time, true);
					$teacher_comps+=$comps[0];
					$student_comps+=$comps[1];
				}
				//TODO sprache
				$x_values[] = get_string($act_date["month"], 'block_exacomp');
				$y_values_teacher[] = $teacher_comps;
				$y_values_student[] = $student_comps;
				$y_values_total[] = $total;
				
				$act_time += 86400; 
				$act_time = strtotime('last day of this month', $act_time);
				
			}
		}else{
			return false; //more than 2 years
		} 
		
		$result = new stdClass();
		$result->x_values = $x_values;
		$result->y_values_teacher = $y_values_teacher;
		$result->y_values_student = $y_values_student;
		$result->y_values_total = $y_values_total;
		return $result;
	}
	
	return false;
}
function block_exacomp_calc_week_dates($time){
	$actday = date('w', $time);
	if($actday == -1) $actday = 6;
	$temps = array($actday,-1+$actday,-2+$actday,-3+$actday,-4+$actday,-5+$actday, -6+$actday);

	$dates = array();
	foreach($temps as $temp){
		$dates[] = getdate( $time-$temp*86400);
	}
	if($dates[0]["mon"] == $dates[6]["mon"]){
		$string = $dates[0]["mday"]."-".$dates[6]["mday"].".".$dates[0]["mon"];	
	}else{
		$string = $dates[0]["mday"].".".$dates[0]["mon"]."-".$dates[6]["mday"].".".$dates[6]["mon"];
	}
	$result = new stdClass();
	$result->dates = $dates;
	$result->label = $string;
	
	return $result;
}
/**
 * 
 * check if there are already evaluations available
 * @param unknown_type $courseid
 */
function block_exacomp_check_user_evaluation_exists($courseid){
	$students = block_exacomp_get_students_by_course($courseid);
	foreach($students as $student){
		$info =  block_exacomp_get_user_competencies_by_course($student, $courseid);
		
		if(!empty($info->competencies->teacher) || !empty($info->comptencies->student))
			return true;
	}
	return false;
}
/**
 * build a schooltype -> subjects tree with given subjects
 * @param unknown_type $subjects
 * @return tree like:
 *  schooltype1
 *  	- subject 1
 *  	- subject 2
 *  schooltype2
 *  	- subject 3 
 */
function block_exacomp_get_schooltypetree_by_subjects($subjects, $competencegrid = false){
	global $version;
	
    $tree = array();
    foreach($subjects as $subject){
    	if($version && !$competencegrid) {
    		$schooltype = block_exacomp_get_subject_by_id($subject->subjid);
    	}
    	else
    		$schooltype = block_exacomp_get_schooltype_by_subject($subject);
       
        if(!array_key_exists($schooltype->id, $tree)){
            $tree[$schooltype->id] = new stdClass();
            $tree[$schooltype->id]->id = $schooltype->id;
            $tree[$schooltype->id]->title = $schooltype->title; 
            $tree[$schooltype->id]->subjects = array();
        }
        $tree[$schooltype->id]->subjects[$subject->id] = $subject; 
    }
    return $tree;
}

function block_exacomp_get_cross_subjects_drafts(){
    global $DB;
    return $DB->get_records(DB_CROSSSUBJECTS, array('courseid'=>0));
}
/**
 * 
 * save the given drafts to course
 * @param array $drafts_to_save
 * @param int $courseid
 */
function block_exacomp_save_drafts_to_course($drafts_to_save, $courseid){
    global $DB, $USER;
    $redirect_crosssubjid = 0;
    foreach($drafts_to_save as $draftid){
        $draft = $DB->get_record(DB_CROSSSUBJECTS, array('id'=>$draftid));
        $draft->courseid = $courseid;
        $draft->creatorid = $USER->id;
		$draft->sourceid = 0;
        $draft->source = IMPORT_SOURCE_SPECIFIC;
        $crosssubjid = $DB->insert_record(DB_CROSSSUBJECTS, $draft);
        
        if($redirect_crosssubjid == 0) $redirect_crosssubjid = $crosssubjid;
        
        //assign competencies
        $comps = $DB->get_records(DB_DESCCROSS, array('crosssubjid'=>$draftid));
        foreach($comps as $comp){
            $insert = new stdClass();
            $insert->descrid = $comp->descrid;
            $insert->crosssubjid = $crosssubjid;
            $DB->insert_record(DB_DESCCROSS, $insert);
            
            //cross course subjects -> insert in visibility table if not existing
            $visibility = $DB->get_record(DB_DESCVISIBILITY, array('courseid'=>$courseid, 'descrid'=>$comp->descrid, 'studentid'=>0));
        	if(!$visibility){
        		$insert = new stdClass();
        		$insert->courseid = $courseid;
        		$insert->descrid = $comp->descrid;
        		$insert->studentid=0;
        		$insert->visible = 1;
        		$DB->insert_record(DB_DESCVISIBILITY, $insert);
        	}
        }
    }
    return $redirect_crosssubjid;
}
function block_exacomp_create_new_crosssub($courseid){
	global $DB, $USER;
	
	$insert = new stdClass();
    $insert->title = get_string('empty_draft', 'block_exacomp');
    $insert->description = get_string('empty_draft_description', 'block_exacomp');
    $insert->courseid = $courseid;
    $insert->creatorid = $USER->id;
	$insert->sourceid = 0;
    $insert->source = IMPORT_SOURCE_SPECIFIC;
    return $DB->insert_record(DB_CROSSSUBJECTS, $insert);
}
function block_exacomp_delete_crosssubject_drafts($drafts_to_delete){
	global $DB;
	foreach($drafts_to_delete as $draftid){
		$DB->delete_records(DB_CROSSSUBJECTS, array('id'=>$draftid));
	}
}
function block_exacomp_get_cross_subjects_by_course($courseid, $studentid=0){
    global $DB;
    $crosssubs = $DB->get_records(DB_CROSSSUBJECTS, array('courseid'=>$courseid));
    if($studentid == 0)
    	return $crosssubs;
    
    $crosssubs_shared = array();
    foreach($crosssubs as $crosssubj){
    	if($crosssubj->shared == 1 || block_exacomp_student_crosssubj($crosssubj->id, $studentid))
    		$crosssubs_shared[$crosssubj->id] = $crosssubj;
    }
    return $crosssubs_shared;
}
function block_exacomp_student_crosssubj($crosssubjid, $studentid){
	global $DB;
	return $DB->get_records(DB_CROSSSTUD, array('crosssubjid'=>$crosssubjid, 'studentid'=>$studentid));
}

function block_exacomp_init_course_crosssubjects($courseid, $crosssubjid, $studentid = 0) {
    $crosssubjects = block_exacomp_get_cross_subjects_by_course($courseid, $studentid);
	
    $selectedCrosssubject = null;
    if(isset($crosssubjects[$crosssubjid])){
        $selectedCrosssubject = $crosssubjects[$crosssubjid];
    } elseif ($crosssubjects) {
        $selectedCrosssubject = reset($crosssubjects);
    }
	
	return array($crosssubjects, $selectedCrosssubject);
}
/**
 * Gets an associative array that is used to display the whole hierarchie of subjects, topics and competencies within a course
 *
 * @param int $courseid
 * @param int $subjectid
 * @return associative_array
 */
function block_exacomp_get_competence_tree_for_cross_subject($courseid, $crosssubjid, $showalldescriptors = false, $showallexamples = true, $filteredtaxonomies = array(SHOW_ALL_TAXONOMIES)) {
	global $DB;

	if(!$showalldescriptors)
		$showalldescriptors = block_exacomp_get_settings_by_course($courseid)->show_all_descriptors;
	
	$allTopics = block_exacomp_get_all_topics();
	$allSubjects = block_exacomp_get_subjects();
	
	$allDescriptors = block_exacomp_get_descriptors_for_cross_subject($courseid, $crosssubjid, $showalldescriptors);
	$courseTopics = block_exacomp_get_topics_for_cross_subject_by_descriptors($allDescriptors);
	
	foreach ($allDescriptors as $descriptor) {
	
		// get descriptor topic
		if (empty($allTopics[$descriptor->topicid])) continue;
		$topic = $allTopics[$descriptor->topicid];
		$topic->descriptors[$descriptor->id] = $descriptor;
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

function block_exacomp_get_descriptors_for_cross_subject($courseid, $crosssubjid, $showalldescriptors = false){
    global $DB;
    $comps = $DB->get_records(DB_DESCCROSS, array('crosssubjid'=>$crosssubjid),'','descrid,crosssubjid');
    
    if(!$comps) return array();
    
    $WHERE = "";
    foreach($comps as $comp){
    	$cross_descr = $DB->get_record(DB_DESCRIPTORS,array('id'=>$comp->descrid));
        $WHERE .=  $cross_descr->parentid.",";
    }
    $WHERE = substr($WHERE, 0, strlen($WHERE)-1);
    
    if(!$showalldescriptors)
		$showalldescriptors = block_exacomp_get_settings_by_course($courseid)->show_all_descriptors;
	
	$sql = '(SELECT DISTINCT desctopmm.id as u_id, d.id as id, d.title, d.niveauid, t.id AS topicid, \'descriptor\' as tabletype, d.profoundness, d.parentid, dvis.visible as visible, n.sorting as niveau, d.catid '
	.'FROM {'.DB_TOPICS.'} t '
	.'JOIN {'.DB_DESCTOPICS.'} desctopmm ON desctopmm.topicid=t.id '
	.'JOIN {'.DB_DESCRIPTORS.'} d ON desctopmm.descrid=d.id '
	.'JOIN {'.DB_DESCVISIBILITY.'} dvis ON dvis.descrid = d.id AND dvis.studentid=0 AND dvis.courseid=? '
	.'LEFT JOIN {'.DB_NIVEAUS.'} n ON n.id = d.niveauid '  
	.'WHERE d.id IN('.$WHERE.')'.')';

	$descriptors = $DB->get_records_sql($sql, array($courseid, $courseid, $courseid, $courseid));
   
	foreach($descriptors as &$descriptor) {
		//get examples
		$descriptor = block_exacomp_get_examples_for_descriptor($descriptor);
		//check for child-descriptors
		$descriptor->children = block_exacomp_get_child_descriptors($descriptor,$courseid, $showalldescriptors);
		foreach($descriptor->children as $cid => $cvalue) {
			if(!array_key_exists($cid, $comps))
				unset($descriptor->children[$cid]);
		}
	}
	
	return $descriptors;
    
}
function block_exacomp_get_topics_for_cross_subject_by_descriptors($descriptors){
    global $DB;
    $topics = array();
    foreach($descriptors as $descriptor){
        $topic = $DB->get_record(DB_TOPICS, array('id'=>$descriptor->topicid));
        if(!array_key_exists($topic->id, $topics))
            $topics[$topic->id] = $topic;
    }
   
    return $topics;
}
function block_exacomp_save_cross_subject_title($crosssubjid, $title){
    global $DB;
    
    if(isset($title)){
        $crosssub = $DB->get_record(DB_CROSSSUBJECTS, array('id'=>$crosssubjid));
        $crosssub->title = $title;
        $DB->update_record(DB_CROSSSUBJECTS, $crosssub);
    }
}
function block_exacomp_save_cross_subject_description($crosssubjid, $description){
    global $DB;
    
    if(isset($description)){
        $crosssub = $DB->get_record(DB_CROSSSUBJECTS, array('id'=>$crosssubjid));
        $crosssub->description = $description;
        $DB->update_record(DB_CROSSSUBJECTS, $crosssub);
    }
}
function block_exacomp_cross_subjects_exists(){
	global $DB;
	$dbman = $DB->get_manager();
	$table = new xmldb_table(DB_CROSSSUBJECTS);
	return $dbman->table_exists($table);
}
function block_exacomp_set_cross_subject_descriptor($crosssubjid,$descrid) {
	global $DB;
	$record = $DB->get_record(DB_DESCCROSS,array('crosssubjid'=>$crosssubjid,'descrid'=>$descrid));
	if(!$record)
		$DB->insert_record(DB_DESCCROSS,array('crosssubjid'=>$crosssubjid,'descrid'=>$descrid));
}
function block_exacomp_unset_cross_subject_descriptor($crosssubjid, $descrid){
	global $DB;
	$record = $DB->get_record(DB_DESCCROSS,array('crosssubjid'=>$crosssubjid,'descrid'=>$descrid));
	if($record)
		$DB->delete_records(DB_DESCCROSS,array('crosssubjid'=>$crosssubjid,'descrid'=>$descrid));	
}
function block_exacomp_set_cross_subject_student($crosssubjid, $studentid){
	global $DB;
	$record = $DB->get_record(DB_CROSSSTUD, array('crosssubjid'=>$crosssubjid, 'studentid'=>$studentid));
	if(!$record)
		$DB->insert_record(DB_CROSSSTUD, array('crosssubjid'=>$crosssubjid, 'studentid'=>$studentid));
}
function block_exacomp_unset_cross_subject_student($crosssubjid, $studentid){
	global $DB;
	$record = $DB->get_record(DB_CROSSSTUD, array('crosssubjid'=>$crosssubjid, 'studentid'=>$studentid));
	if($record)
		$DB->delete_records(DB_CROSSSTUD, array('crosssubjid'=>$crosssubjid, 'studentid'=>$studentid));
}
function 	block_exacomp_share_crosssubject($crosssubjid, $value = 0){
	global $DB;
	$update = $DB->get_record(DB_CROSSSUBJECTS, array('id'=>$crosssubjid));
	$update->shared = $value;
	return $DB->update_record(DB_CROSSSUBJECTS, $update);
}
function block_exacomp_get_descr_topic_sorting($topicid, $descid){
	global $DB;
	$record = $DB->get_record(DB_DESCTOPICS, array('descrid'=>$descid, 'topicid'=>$topicid));
	return ($record) ? $record->sorting : 0;
}
function block_exacomp_set_descriptor_visibility($descrid, $courseid, $value, $studentid){
	global $DB;
	$record = $DB->get_record(DB_DESCVISIBILITY, array('descrid'=>$descrid, 'courseid'=>$courseid, 'studentid'=>$studentid));
	if($record){
		$record->visible = $value;
		$DB->update_record(DB_DESCVISIBILITY, $record);
	}else{
		$insert->descrid = $descrid;
		$insert->courseid = $courseid;
		$insert->studentid = $studentid;
		$insert->visible = $value;
		$DB->insert_record(DB_DESCVISIBILITY, $insert);
	}
}
function block_exacomp_descriptor_visible($courseid, $descriptor, $studentid){
	global $DB;
	$record = $DB->get_record(DB_DESCVISIBILITY, array('courseid'=>$courseid, 'descrid'=>$descriptor->id, 'studentid'=>$studentid));
	
	if($record)
		return $record->visible;
	else 
		if(isset($descriptor->visible))
			return $descriptor->visible;
		else if($studentid > 0)
			return block_exacomp_descriptor_visible($courseid, $descriptor, 0);
		
}
function block_exacomp_descriptor_used($courseid, $descriptor, $studentid){
	global $DB;
	//if studentid == 0 used = true, if no evaluation (teacher OR student) for this descriptor for any student in this course
	//							     if no evaluation/submission for the examples of this descriptor
	 			
	//if studentid != 0 used = true, if any assignment (teacher OR student) for this descriptor for THIS student in this course
	//							     if no evaluation/submission for the examples of this descriptor
	
	if($studentid == 0){
		$records = $DB->get_records(DB_COMPETENCIES, array('courseid'=>$courseid, 'compid'=>$descriptor->id, 'comptype'=>TYPE_DESCRIPTOR, 'value'=>1));
		if($records) return true;
		
		if($descriptor->examples){
			foreach($descriptor->examples as $example){
				$records = $DB->get_records(DB_EXAMPLEEVAL, array('courseid'=>$courseid, 'exampleid'=>$example->id, 'teacher_evaluation'=>1));
				if($records) return true;
				
				$records = $DB->get_records(DB_EXAMPLEEVAL, array('courseid'=>$courseid, 'exampleid'=>$example->id, 'student_evaluation'=>1));
				if($records) return true;
			}
		}
		//TODO submission //activities
	}else{
	$records = $DB->get_records(DB_COMPETENCIES, array('courseid'=>$courseid, 'compid'=>$descriptor->id, 'comptype'=>TYPE_DESCRIPTOR, 'userid'=>$studentid, 'value'=>1));
		if($records) return true;
		
		if($descriptor->examples){
			foreach($descriptor->examples as $example){
				$records = $DB->get_records(DB_EXAMPLEEVAL, array('courseid'=>$courseid, 'exampleid'=>$example->id, 'studentid'=>$studentid, 'student_evaluation'=>1));
				if($records) return true;
				
				$records = $DB->get_records(DB_EXAMPLEEVAL, array('courseid'=>$courseid, 'exampleid'=>$example->id, 'studentid'=>$studentid, 'teacher_evaluation'=>1));
				if($records) return true;
			}
		}
		
		//TODO submissions & avtivities
	}
	
	return false;
}
function block_exacomp_get_students_for_crosssubject($courseid, $crosssub){
	global $DB;
	$course_students = block_exacomp_get_students_by_course($courseid);
	if($crosssub->shared)
		return $course_students;
		
	$students = array();
	$assigned_students = $DB->get_records_menu(DB_CROSSSTUD,array('crosssubjid'=>$crosssub->id),'','studentid,crosssubjid');
	foreach($course_students as $student){
		if(isset($assigned_students[$student->id]))
			$students[$student->id] = $student;
	}
	return $students;
}
function block_exacomp_get_viewurl_for_example($studentid,$exampleid) {
	global $DB;
	$sql = 'select *, max(timecreated) from {block_exacompitemexample} ie
	        JOIN {block_exaportitem} i ON i.id = ie.itemid
	        WHERE ie.exampleid = ? AND i.userid=?';
	
	$item = $DB->get_record_sql($sql, array($exampleid,$studentid));
	if(!$item)
		return false;
	
	$view = $DB->get_record('block_exaportviewblock', array("type"=>"item","itemid"=>$item->itemid));
	if(!$view)
		return false;
	
	$access = "view/id/".$studentid."-".$view->viewid."&itemid=".$item->itemid;
	
	return $access;
}
function block_exacomp_add_example_to_schedule($studentid,$exampleid,$creatorid,$courseid,$timecreated,$timemodified) {
	global $DB;
	
	if(!$DB->record_exists('block_exacompschedule', array('studentid' => $studentid, 'exampleid' => $exampleid, 'courseid' => $courseid))) {
		$DB->insert_record('block_exacompschedule', array('studentid' => $studentid, 'exampleid' => $exampleid, 'courseid' => $courseid,'creatorid' => $creatorid, 'timecreated' => $timecreated, 'timemodified' => $timemodified));
		return true;
	} else 
		return false;
}

function block_exacomp_add_days($date, $days) {
    return mktime(0,0,0,date('m', $date), date('d', $date)+$days, date('Y', $date));
}

function block_exacomp_optional_param_parse_key_type($type) {
    if (is_array($type)) return $type;
    if ($type === PARAM_INT || $type === PARAM_TEXT) return $type;
    return null;
}

function block_exacomp_clean_array($values, $definition) {

    if ((count($definition) == 1) && ($keyType = block_exacomp_optional_param_parse_key_type(key($definition))))  {
        // type => type
        $ret = array();
        
        $valueType = reset($definition);
        
        if (is_array($valueType)) {
            foreach ($values as $key=>$value) {
                $ret[clean_param($key, $keyType)] = block_exacomp_clean_array($value, $valueType);
            }
        } else {
            foreach ($values as $key=>$value) {
                $ret[clean_param($key, $keyType)] = clean_param($value, $valueType);
            }
        }
    } else {
        // some value => type
        $ret = new stdClass;
        
        foreach ($definition as $key => $valueType) {
            $value = isset($values[$key]) ? $values[$key] : null;
            if (is_array($valueType)) {
                $ret->$key = block_exacomp_clean_array($value, $valueType);
            } else {
                $ret->$key = clean_param($value, $valueType);
            }
        }
    }
    return $ret;
}

function block_exacomp_optional_param_array($parname, array $definition) {

    // POST has precedence.
    if (isset($_POST[$parname])) {
        $param = $_POST[$parname];
    } else if (isset($_GET[$parname])) {
        $param = $_GET[$parname];
    } else {
        return array();
    }

    return block_exacomp_clean_array($param, $definition);
}

function block_exacomp_build_example_association_tree($courseid, $example_descriptors = array(), $exampleid=0, $descriptorid = 0){
	//get all subjects, topics, descriptors and examples
	$tree = block_exacomp_get_competence_tree($courseid, null, false, SHOW_ALL_TOPICS, true, block_exacomp_get_settings_by_course($courseid)->filteredtaxonomies, false, false, true);
	
	// unset all descriptors, topics and subjects that do not contain the example descriptors
	foreach($tree as $skey => $subject) {
		$subject->associated = 0;
		foreach ( $subject->subs as $tkey => $topic ) {
			$topic->associated = 0;
			if(isset($topic->descriptors)) {
				foreach ( $topic->descriptors as $dkey => $descriptor ) {
					
					$descriptor = block_exacomp_check_child_descriptors($descriptor, $example_descriptors, $exampleid, $descriptorid);
					
					if($descriptor->associated == 1)
						$topic->associated = 1;
				}
			}
			
			if($topic->associated == 1)
				$subject->associated = 1;
		}
	}
	return $tree;
}
function block_exacomp_check_child_descriptors($descriptor, $example_descriptors, $exampleid, $descriptorid = 0) {

	$descriptor->associated = 0;
	foreach($descriptor->children as $ckey => $cvalue) {
		$keepDescriptor = false;
		
		if (array_key_exists ( $cvalue->id, $example_descriptors ) || $descriptorid == $ckey) {
			$keepDescriptor = true;
			$descriptor->associated = 1;
		}
		$descriptor->children[$ckey]->associated = 1;
		$descriptor->children[$ckey]->direct_associated = 1;
		if (! $keepDescriptor) {
			$descriptor->children[$ckey]->associated = 0;
			$descriptor->children[$ckey]->direct_associated = 0;
			continue;
		}
		foreach($cvalue->examples as $ekey => $example) {
			$cvalue->examples[$ekey]->associated = 1;
			if($example->id != $exampleid)
				$cvalue->examples[$ekey]->associated = 0;
		}
	}
	
	return $descriptor;
}
function block_exacomp_calculate_spanning_niveau_colspan($niveaus, $spanningNiveaus) {
	
	$colspan = count($niveaus);
	
	foreach($niveaus as $id => $niveau) {
		if(array_key_exists($id, $spanningNiveaus)) {
			$colspan--;
		}
	}
	
	return $colspan;
}

function block_exacomp_check_descriptor_visibility($courseid, $descriptor, $studentid, $one) {
	global $DB;
	
	$descriptor_used = block_exacomp_descriptor_used($courseid, $descriptor, $studentid);
	
	// if descriptor is used, hidding is impossible
	if($descriptor_used)
		return 1;
	
	// if we are in editmode, use global descriptor value
	if($studentid == 0) {
		if(isset($descriptor->visible))
			return $descriptor->visible;
	}
	
	// if we are in student mode, we use student value
	// or if we are in edit mode and do not have a global descriptor value, we get it here
	$record = $DB->get_record(DB_DESCVISIBILITY, array('courseid'=>$courseid, 'descrid'=>$descriptor->id, 'studentid'=>$studentid));
	if($record)
		return $record->visible;
	else
		if(isset($descriptor->visible))
			return $descriptor->visible;
	
}

function block_exacomp_get_descriptor_visible_css($visible, $role) {
	$visible_css = '';
	if(!$visible)
		($role == ROLE_TEACHER) ? $visible_css = ' hidden_temp' : $visible_css = ' hidden';
	
	return $visible_css;
}
function block_exacomp_init_cross_subjects(){
    global $DB;
    $emptydrafts = $DB->get_records(DB_CROSSSUBJECTS, array('sourceid'=>0, 'source'=>1, 'creatorid'=>0, 'courseid'=>0));
   
    foreach($emptydrafts as $emptydraft){
    	if(strcmp($emptydraft->title, 'Leere Vorlage')==0 || strcmp($emptydraft->title, 'new crosssubject')==0)
    		$DB->delete_records(DB_CROSSSUBJECTS, array('id'=>$emptydraft->id));
    } 
}
function block_exacomp_calculate_statistic_for_descriptor($courseid, $students, $descriptor){
	global $DB;
	$student_oB = 0; $student_iA = 0;
	$student_oB_title = ""; $student_iA_title = "";	
	$self_1 = 0; $self_2 = 0; $self_3 = 0;
	$self_1_title = ""; $self_2_title = ""; $self_3_title = "";
	$niv_class_G = 0; $niv_class_M = 0; $niv_class_E = 0; $niv_class_nE = 0; $niv_class_oB = 0; $niv_class_iA = 0;
	$niv_class_G_title = ""; $niv_class_M_title = ""; $niv_class_E_title = ""; $niv_class_nE_title = ""; $niv_class_oB_title = ""; $niv_class_iA_title = "";
		
	foreach($students as $student){
		if(isset($student->competencies->student[$descriptor->id])){
			//count different levels
			if($student->competencies->student[$descriptor->id]==1){
				$self_1++;
				$self_1_title .= $student->firstname." ".$student->lastname."\n";
			}elseif($student->competencies->student[$descriptor->id]==2){
				$self_2++;
				$self_2_title .= $student->firstname." ".$student->lastname."\n";
			}elseif($student->competencies->student[$descriptor->id]==3){
				$self_3++;
				$self_3_title .= $student->firstname." ".$student->lastname."\n";
			}
		}else{
			$student_oB++;
			$student_oB_title .= $student->firstname." ".$student->lastname."\n";
		}
		if(isset($student->competencies->teacher[$descriptor->id])){
			//count different levels
			if($student->competencies->teacher[$descriptor->id]==1){
				$niv_class_E++;
				$niv_class_E_title .= $student->firstname." ".$student->lastname."\n";
			}elseif($student->competencies->teacher[$descriptor->id]==2){
				$niv_class_M++;
				$niv_class_M_title .= $student->firstname." ".$student->lastname."\n";
			}elseif($student->competencies->teacher[$descriptor->id]==3){
				$niv_class_G++;
				$niv_class_G_title .= $student->firstname." ".$student->lastname."\n";
			}elseif($student->competencies->teacher[$descriptor->id]==0){
				$niv_class_nE++;
				$niv_class_nE_title .= $student->firstname." ".$student->lastname."\n";
			}
		}else{
			$niv_class_oB++;
			$niv_class_oB_title .= $student->firstname." ".$student->lastname."\n";
		}
		
		//TODO in arbeit
	}
	
	return array($self_1, $self_2, $self_3, $student_oB, $student_iA, $niv_class_G, $niv_class_M, 
		$niv_class_E, $niv_class_nE, $niv_class_oB, $niv_class_iA,
		$self_1_title, $self_2_title, $self_3_title, $student_oB_title, $student_iA_title, $niv_class_G_title, $niv_class_M_title, 
		$niv_class_E_title, $niv_class_nE_title, $niv_class_oB_title, $niv_class_iA_title);
}
function block_exacomp_calculate_statistic_for_example($courseid, $students, $example){
	global $DB;
	$student_oB = 0; $student_iA = 0;
	$student_oB_title = ""; $student_iA_title = "";	
	$self_1 = 0; $self_2 = 0; $self_3 = 0;
	$self_1_title = ""; $self_2_title = ""; $self_3_title = "";
	$niv_class_G = 0; $niv_class_M = 0; $niv_class_E = 0; $niv_class_nE = 0; $niv_class_oB = 0; $niv_class_iA = 0;
	$niv_class_G_title = ""; $niv_class_M_title = ""; $niv_class_E_title = ""; $niv_class_nE_title = ""; $niv_class_oB_title = ""; $niv_class_iA_title = "";
		
	foreach($students as $student){
		if(isset($student->examples->student[$example->id])){
			//count different levels
			if($student->examples->student[$example->id]==1){
				$self_1++;
				$self_1_title .= $student->firstname." ".$student->lastname."\n";
			}elseif($student->examples->student[$example->id]==2){
				$self_2++;
				$self_2_title .= $student->firstname." ".$student->lastname."\n";
			}elseif($student->examples->student[$example->id]==3){
				$self_3++;
				$self_3_title .= $student->firstname." ".$student->lastname."\n";
			}
		}else{
			$student_oB++;
			$student_oB_title .= $student->firstname." ".$student->lastname."\n";
		}
		if(isset($student->examples->teacher[$example->id])){
			//count different levels
			if($student->examples->teacher[$example->id]==1){
				$niv_class_E++;
				$niv_class_E_title .= $student->firstname." ".$student->lastname."\n";
			}elseif($student->examples->teacher[$example->id]==2){
				$niv_class_M++;
				$niv_class_M_title .= $student->firstname." ".$student->lastname."\n";
			}elseif($student->examples->teacher[$example->id]==3){
				$niv_class_G++;
				$niv_class_G_title .= $student->firstname." ".$student->lastname."\n";
			}elseif($student->examples->teacher[$example->id]==0){
				$niv_class_nE++;
				$niv_class_nE_title .= $student->firstname." ".$student->lastname."\n";
			}
		}else{
			$niv_class_oB++;
			$niv_class_oB_title .= $student->firstname." ".$student->lastname."\n";
		}
		
		//TODO in arbeit
	}
	
	return array($self_1, $self_2, $self_3, $student_oB, $student_iA, $niv_class_G, $niv_class_M, 
		$niv_class_E, $niv_class_nE, $niv_class_oB, $niv_class_iA,
		$self_1_title, $self_2_title, $self_3_title, $student_oB_title, $student_iA_title, $niv_class_G_title, $niv_class_M_title, 
		$niv_class_E_title, $niv_class_nE_title, $niv_class_oB_title, $niv_class_iA_title);
}