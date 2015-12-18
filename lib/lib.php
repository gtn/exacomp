<?php

namespace {
defined('MOODLE_INTERNAL') || die();

require_once __DIR__.'/common.php';
require_once __DIR__.'/classes.php';
require_once __DIR__.'/block_exacomp.class.php';

use \block_exacomp\globals as g;

/**
 * COMPETENCE TYPES
 */
define('TYPE_DESCRIPTOR', 0);
define('TYPE_TOPIC', 1);
define('TYPE_CROSSSUB', 2);

if (block_exacomp_moodle_badges_enabled()) {
	require_once($CFG->libdir . '/badgeslib.php');
	require_once($CFG->dirroot . '/badges/lib/awardlib.php');
}

$usebadges = get_config('exacomp', 'usebadges');
$xmlserverurl = get_config('exacomp', 'xmlserverurl');
$autotest = get_config('exacomp', 'autotest');
$testlimit = get_config('exacomp', 'testlimit');
$specificimport = get_config('exacomp','enableteacherimport');
$notifications = get_config('exacomp','notifications');

$additional_grading = get_config('exacomp', 'additional_grading');

define("SHOW_ALL_NIVEAUS",99999999);
define("SHOW_ALL_TAXONOMIES",100000000);
define("BLOCK_EXACOMP_SHOW_ALL", -1);
define("BLOCK_EXACOMP_SHOW_ALL_STUDENTS", -1);
define("BLOCK_EXACOMP_SHOW_STATISTIC", -2);
define("BLOCK_EXACOMP_DEFAULT_STUDENT", -5);

define("BLOCK_EXACOMP_REPORT1",1);
define("BLOCK_EXACOMP_REPORT2",2);
define("BLOCK_EXACOMP_REPORT3",3);

/**
 * wrote own function, so eclipse knows which type the output renderer is
 * @return block_exacomp_renderer
 */
function block_exacomp_get_renderer() {
	global $PAGE;
	return $PAGE->get_renderer('block_exacomp');
}

/**
 *
 * Includes all neccessary JavaScript files
 */
function block_exacomp_init_js_css(){
	global $PAGE, $CFG;
	
	// only allowed to be called once
	static $js_inited = false;
	if ($js_inited) return;
	$js_inited = true;
	
	// js/css for whole block
	$PAGE->requires->css('/blocks/exacomp/css/styles.css');
	$PAGE->requires->jquery();
	$PAGE->requires->jquery_plugin('ui');
	$PAGE->requires->jquery_plugin('ui-css');
	$PAGE->requires->js('/blocks/exacomp/javascript/exacomp.js', true);
	$PAGE->requires->js('/blocks/exacomp/javascript/ajax.js', true);

	// Strings can be used in JavaScript: M.util.get_string(identifier, component)
	$PAGE->requires->strings_for_js([
		'show', 'hide' //, 'selectall', 'deselectall'
	], 'moodle');
	$PAGE->requires->strings_for_js([
		'override_notice', 'unload_notice', 'example_sorting_notice', 'delete_unconnected_examples'
	], 'block_exacomp');
	
	// page specific js/css
	$scriptName = preg_replace('!\.[^\.]+$!', '', basename($_SERVER['PHP_SELF']));
	if (file_exists($CFG->dirroot.'/blocks/exacomp/css/'.$scriptName.'.css'))
		$PAGE->requires->css('/blocks/exacomp/css/'.$scriptName.'.css');
	if (file_exists($CFG->dirroot.'/blocks/exacomp/javascript/'.$scriptName.'.js'))
		$PAGE->requires->js('/blocks/exacomp/javascript/'.$scriptName.'.js', true);
		
	if(preg_match('/(?i)Trident|msie/',$_SERVER['HTTP_USER_AGENT']) && strcmp($scriptName, 'competence_profile')==0){
		$PAGE->requires->js('/blocks/exacomp/javascript/competence_profile_msie.js', true);
	}
}
function block_exacomp_init_js_weekly_schedule(){
	global $PAGE, $CFG;
	
	$PAGE->requires->css('/blocks/exacomp/fullcalendar/fullcalendar.css');
	
	$PAGE->requires->jquery_plugin('ui');
	$PAGE->requires->jquery_plugin('ui-css');
	
	$PAGE->requires->js('/blocks/exacomp/fullcalendar/moment.min.js', true);
	//$PAGE->requires->js('/blocks/exacomp/javascript/jquery.js');
	$PAGE->requires->js('/blocks/exacomp/fullcalendar/fullcalendar.js', true);
	$PAGE->requires->js('/blocks/exacomp/fullcalendar/lang-all.js', true);
	
	$PAGE->requires->js('/blocks/exacomp/fullcalendar/jquery.ui.touch.js');
}

function block_exacomp_get_context_from_courseid($courseid) {
	if ($courseid instanceof context) {
		// already context
		return $courseid;
	} else if (is_numeric($courseid)) { // don't use is_int, because eg. moodle $COURSE->id is a string!
		return context_course::instance($courseid);
	} else if ($courseid === null) {
		global $COURSE;
		return context_course::instance($COURSE->id);
	} else {
		print_error('wrong courseid type '.gettype($courseid));
	}
}
/**
 * 
 * @param courseid or context $context
 */
function block_exacomp_is_teacher($context = null) {
	$context = block_exacomp_get_context_from_courseid($context);
	return has_capability('block/exacomp:teacher', $context);
}
/**
 * 
 * @param courseid or context $context
 */
function block_exacomp_is_student($context = null) {
	$context = block_exacomp_get_context_from_courseid($context);
	// a teacher can not be a student in the same course
	return has_capability('block/exacomp:student', $context) && !has_capability('block/exacomp:teacher', $context);
}
/**
 * 
 * @param courseid or context $context
 */
function block_exacomp_is_admin($context = null) {
	$context = block_exacomp_get_context_from_courseid($context);
	return has_capability('block/exacomp:admin', $context);
}
/**
 * 
 * @param courseid or context $context
 */
function block_exacomp_require_teacher($context = null) {
	$context = block_exacomp_get_context_from_courseid($context);
	return require_capability('block/exacomp:teacher', $context);
}
/**
 * 
 * @param courseid or context $context
 */
function block_exacomp_require_admin($context = null) {
	$context = block_exacomp_get_context_from_courseid($context);
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
	return $DB->get_record(block_exacomp::DB_SUBJECTS,array("id" => $subjectid),'id, title, titleshort, \'subject\' as tabletype, source');
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
	SELECT DISTINCT s.id, s.title, s.stid, s.infolink, s.description, s.source, s.sorting, \'subject\' as tabletype
	FROM {'.block_exacomp::DB_SUBJECTS.'} s
	JOIN {'.block_exacomp::DB_TOPICS.'} t ON t.subjid = s.id
	JOIN {'.block_exacomp::DB_COURSETOPICS.'} ct ON ct.topicid = t.id AND ct.courseid = ?
	'.($showalldescriptors ? '' : '
			-- only show active ones
			JOIN {'.block_exacomp::DB_DESCTOPICS.'} topmm ON topmm.topicid=t.id
			JOIN {'.block_exacomp::DB_DESCRIPTORS.'} d ON topmm.descrid=d.id
			JOIN {'.block_exacomp::DB_COMPETENCE_ACTIVITY.'} ca ON (d.id=ca.compid AND ca.comptype = '.TYPE_DESCRIPTOR.') OR (t.id=ca.compid AND ca.comptype = '.TYPE_TOPIC.')
			JOIN {course_modules} a ON ca.activityid=a.id AND a.course=ct.courseid
			').'
			';
	$subjects = $DB->get_records_sql($sql, array($courseid));

	return block_exacomp_sort_items($subjects, block_exacomp::DB_SUBJECTS);
}
/**
 * Gets all available subjects
 */
function block_exacomp_get_all_subjects() {
	global $DB;
	return $DB->get_records(block_exacomp::DB_SUBJECTS,array(),'','id, title, \'subject\' as tabletype, source, sourceid');
}
/**
 * This method is only used in the LIS version
 * @param int $courseid
 */
function block_exacomp_get_schooltypes_by_course($courseid) {
	global $DB;
	return $DB->get_records_sql('
			SELECT DISTINCT s.id, s.title, s.source, s.sourceid
			FROM {'.block_exacomp::DB_SCHOOLTYPES.'} s
			JOIN {'.block_exacomp::DB_MDLTYPES.'} m ON m.stid = s.id AND m.courseid = ?
			ORDER BY s.sorting, s.title
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
	$sql = 'SELECT sub.id FROM {'.block_exacomp::DB_SUBJECTS.'} sub
	JOIN {'.block_exacomp::DB_MDLTYPES.'} type ON sub.stid = type.stid
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
		FROM {'.block_exacomp::DB_SUBJECTS.'} s
		JOIN {'.block_exacomp::DB_TOPICS.'} t ON t.subjid = s.id
		GROUP BY s.id, s.title, s.stid
		ORDER BY s.stid, s.sorting, s.title
		';

		return $DB->get_records_sql($sql);
	} else if($subjectid != null) {
		$sql = 'SELECT s.id, s.title, \'subject\' as tabletype
		FROM {'.block_exacomp::DB_SUBJECTS.'} s
		JOIN {'.block_exacomp::DB_TOPICS.'} t ON t.subjid = s.id
		WHERE s.id = ?
		GROUP BY s.id, s.title, s.stid
		ORDER BY s.stid, s.sorting, s.title';

		return $DB->get_records_sql($sql,$subjectid);
	}

	$subjects = $DB->get_records_sql('
			SELECT s.id, s.title, s.stid, \'subject\' as tabletype
			FROM {'.block_exacomp::DB_SUBJECTS.'} s
			JOIN {'.block_exacomp::DB_TOPICS.'} t ON t.subjid = s.id
			JOIN {'.block_exacomp::DB_COURSETOPICS.'} ct ON ct.topicid = t.id AND ct.courseid = ?
			'.(block_exacomp_get_settings_by_course($courseid)->show_all_descriptors ? '' : '
					-- only show active ones
					JOIN {'.block_exacomp::DB_DESCTOPICS.'} topmm ON topmm.topicid=t.id
					JOIN {'.block_exacomp::DB_DESCRIPTORS.'} d ON topmm.descrid=d.id
					JOIN {'.block_exacomp::DB_COMPETENCE_ACTIVITY.'} ca ON (d.id=ca.compid AND ca.comptype = '.TYPE_DESCRIPTOR.') OR (t.id=ca.compid AND ca.comptype = '.TYPE_TOPIC.')
					JOIN {course_modules} a ON ca.activityid=a.id AND a.course=ct.courseid
					').'
			GROUP BY id
			ORDER BY s.sorting, s.title
			', array($courseid));

	return $subjects;
}
/**
 * returns the subject an example belongs to
 * @param int $exampleid
 */
function block_exacomp_get_subjecttitle_by_example($exampleid) {
	global $DB;

	// TODO: refactor and use block_exacomp_get_descriptors_by_example()
	$descriptors = block_exacomp_get_descriptor_mms_by_example($exampleid);

	foreach($descriptors as $descriptor) {

		$full = $DB->get_record(block_exacomp::DB_DESCRIPTORS, array("id" => $descriptor->descrid));
		$sql = "select s.* FROM {block_exacompsubjects} s, {block_exacompdescrtopic_mm} dt, {block_exacomptopics} t
		WHERE dt.descrid = ? AND t.id = dt.topicid AND t.subjid = s.id";

		$subject = $DB->get_record_sql($sql,array($full->parentid),IGNORE_MULTIPLE);
		if ($subject) return $subject->title;
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

	$sql = 'SELECT DISTINCT t.id, t.title, t.sorting, t.subjid, t.description, t.numb, t.source, s.source AS subj_source, s.sorting AS subj_sorting, s.title AS subj_title
	FROM {'.block_exacomp::DB_TOPICS.'} t
	JOIN {'.block_exacomp::DB_COURSETOPICS.'} ct ON ct.topicid = t.id AND ct.courseid = ? '.(($subjectid > 0) ? 'AND t.subjid = ? ': '').'
	JOIN {'.block_exacomp::DB_SUBJECTS.'} s ON t.subjid=s.id -- join subject here, to make sure only topics with existing subject are loaded
	'.($showalldescriptors ? '' : '
			-- only show active ones
			JOIN {'.block_exacomp::DB_DESCTOPICS.'} topmm ON topmm.topicid=t.id
			JOIN {'.block_exacomp::DB_DESCRIPTORS.'} d ON topmm.descrid=d.id
			JOIN {'.block_exacomp::DB_COMPETENCE_ACTIVITY.'} da ON (d.id=da.compid AND da.comptype = '.TYPE_DESCRIPTOR.') OR (t.id=da.compid AND da.comptype = '.TYPE_TOPIC.')
			JOIN {course_modules} a ON da.activityid=a.id AND a.course=ct.courseid
			').'
			';
	//GROUP By funktioniert nur mit allen feldern im select, aber nicht mit strings
	$topics = $DB->get_records_sql($sql, array($courseid, $subjectid));

	return block_exacomp_sort_items($topics, ['subj_' => block_exacomp::DB_SUBJECTS, '' => block_exacomp::DB_TOPICS]);
}

function block_exacomp_sort_items($items, $sortings) {
	$sortings = (array)$sortings;
	// var_dump($sortings);

	uasort($items, function($a, $b) use ($sortings) {
		foreach ($sortings as $prefix => $sorting) {
			if (is_int($prefix)) $prefix = '';

			if ($sorting == block_exacomp::DB_SUBJECTS) {
				if (!array_key_exists($prefix."source", $a) || !array_key_exists($prefix."source", $b)) {
					throw new \block_exacomp\Exception('col not found: '.$prefix."source");
				}
				if (!array_key_exists($prefix."sorting", $a) || !array_key_exists($prefix."sorting", $b)) {
					throw new \block_exacomp\Exception('col not found: '.$prefix."sorting");
				}
				if (!array_key_exists($prefix."title", $a) || !array_key_exists($prefix."title", $b)) {
					throw new \block_exacomp\Exception('col not found: '.$prefix."title");
				}

				// sort subjects
				// first imported, then generated
				if ($a->{$prefix."source"} != block_exacomp::DATA_SOURCE_CUSTOM && $b->{$prefix."source"} == block_exacomp::DATA_SOURCE_CUSTOM)
					return -1;
				if ($a->{$prefix."source"} == block_exacomp::DATA_SOURCE_CUSTOM && $b->{$prefix."source"} != block_exacomp::DATA_SOURCE_CUSTOM)
					return 1;

				if ($a->{$prefix."sorting"} < $b->{$prefix."sorting"})
					return -1;
				if ($a->{$prefix."sorting"} > $b->{$prefix."sorting"})
					return 1;

				// last by title
				if ($a->{$prefix."title"} !== $b->{$prefix."title"}) {
					return strcmp($a->{$prefix."title"}, $b->{$prefix."title"});
				}
			} elseif ($sorting == block_exacomp::DB_TOPICS) {
				if (!array_key_exists($prefix."sorting", $a) || !array_key_exists($prefix."sorting", $b)) {
					throw new \block_exacomp\Exception('col not found: '.$prefix."sorting");
				}
				if (!array_key_exists($prefix."numb", $a) || !array_key_exists($prefix."numb", $b)) {
					throw new \block_exacomp\Exception('col not found: '.$prefix."numb");
				}
				if (!array_key_exists($prefix."title", $a) || !array_key_exists($prefix."title", $b)) {
					throw new \block_exacomp\Exception('col not found: '.$prefix."title");
				}

				if ($a->{$prefix."sorting"} < $b->{$prefix."sorting"})
					return -1;
				if ($a->{$prefix."sorting"} > $b->{$prefix."sorting"})
					return 1;

				if ($a->{$prefix."numb"} < $b->{$prefix."numb"})
					return -1;
				if ($a->{$prefix."numb"} > $b->{$prefix."numb"})
					return 1;

				// last by title
				if ($a->{$prefix."title"} !== $b->{$prefix."title"}) {
					return strcmp($a->{$prefix."title"}, $b->{$prefix."title"});
				}
			} elseif ($sorting == block_exacomp::DB_DESCRIPTORS) {
				if (!array_key_exists($prefix."sorting", $a) || !array_key_exists($prefix."sorting", $b)) {
					throw new \block_exacomp\Exception('col not found: '.$prefix."sorting");
				}
				if (!array_key_exists($prefix."title", $a) || !array_key_exists($prefix."title", $b)) {
					throw new \block_exacomp\Exception('col not found: '.$prefix."title");
				}

				if ($a->{$prefix."sorting"} < $b->{$prefix."sorting"})
					return -1;
				if ($a->{$prefix."sorting"} > $b->{$prefix."sorting"})
					return 1;

				// last by title
				if ($a->{$prefix."title"} !== $b->{$prefix."title"}) {
					return strcmp($a->{$prefix."title"}, $b->{$prefix."title"});
				}
			} else {
					throw new \block_exacomp\Exception('sorting type not found: '.$sorting);
			}
		}
	});

	return $items;
}


/**
 * Gets all topics
 */
function block_exacomp_get_all_topics($subjectid = null) {
	global $DB;

	$topics = $DB->get_records_sql('
			SELECT t.id, t.sorting, t.numb, t.title, t.parentid, t.subjid, \'topic\' as tabletype, s.source AS subj_source, s.sorting AS subj_sorting, s.title AS subj_title
			FROM {'.block_exacomp::DB_SUBJECTS.'} s
			JOIN {'.block_exacomp::DB_TOPICS.'} t ON t.subjid = s.id
			'.($subjectid == null ? '' : '
					-- only show active ones
					WHERE s.id = ?
					').'
			', array($subjectid));

	return block_exacomp_sort_items($topics, ['subj_' => block_exacomp::DB_SUBJECTS, '' => block_exacomp::DB_TOPICS]);
}
/**
 *
 * Gets topic with particular id
 * @param  $topicid
 */
function block_exacomp_get_topic_by_id($topicid) {
	global $DB;

	$topic = $DB->get_record_sql('
			SELECT t.id, t.title, t.parentid, t.subjid, \'topic\' as tabletype, t.numb
			FROM {'.block_exacomp::DB_TOPICS.'} t
			WHERE t.id = ?
			', array($topicid));

	return $topic;
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
		if($DB->record_exists(block_exacomp::DB_COMPETENCE_ACTIVITY, array("compid"=>$compid,"comptype"=>$comptype,"activityid"=>$cm->id)))
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

	$example = $DB->get_record(block_exacomp::DB_EXAMPLES, array('id'=>$delete));
	if($example && $example->creatorid == $USER->id) {
		$DB->delete_records(block_exacomp::DB_EXAMPLES, array('id' => $delete));
		$DB->delete_records(block_exacomp::DB_DESCEXAMP, array('exampid' => $delete));
		$DB->delete_records(block_exacomp::DB_EXAMPLEEVAL, array('exampleid' => $delete));

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
	global $DB, $USER;

	if($role == block_exacomp::ROLE_STUDENT && $userid != $USER->id)
		return -1;

	$id = -1;

	if($record = $DB->get_record(block_exacomp::DB_COMPETENCIES, array("userid" => $userid, "compid" => $compid, "comptype" => $comptype, "courseid" => $courseid, "role" => $role))) {
		$record->value = ($value != -1) ? $value : null;
		$record->timestamp = time();
		$record->reviewerid = $USER->id;
		$DB->update_record(block_exacomp::DB_COMPETENCIES, $record);
		$id = $record->id;
	} else {
		$id = $DB->insert_record(block_exacomp::DB_COMPETENCIES, array("userid" => $userid, "compid" => $compid, "comptype" => $comptype, "courseid" => $courseid, "role" => $role, "value" => $value, "reviewerid" => $USER->id, "timestamp" => time()));
	}

	if($role == block_exacomp::ROLE_TEACHER)
		block_exacomp_send_grading_notification($USER, $DB->get_record('user',array('id'=>$userid)), $courseid);
	else
		block_exacomp_notify_all_teachers_about_self_assessment($courseid);

	\block_exacomp\event\competence_assigned::log(['objecttable' => ($comptype == block_exacomp::TYPE_DESCRIPTOR) ? 'block_exacompdescriptors' : 'block_exacomptopics', 'objectid' => $compid, 'courseid' => $courseid, 'relateduserid' => $userid]);

	return $id;
}

function block_exacomp_set_user_example($userid, $exampleid, $courseid, $role, $value = null, $starttime = 0, $endtime = 0, $studypartner = 'self', $additionalinfo=null) {
	global $DB, $USER;

	$updateEvaluation = new stdClass();

	if ($role == block_exacomp::ROLE_TEACHER) {
		$updateEvaluation->teacher_evaluation = ($value != -1) ? $value : null;
		$updateEvaluation->teacher_reviewerid = $USER->id;
		if($additionalinfo !== null) $updateEvaluation->additionalinfo = $additionalinfo;
		$updateEvaluation->resubmission = ($value != -1) ? false : true;
	} else {
		if ($userid != $USER->id)
			// student can only assess himself
			continue;

			if($value !== null)
				$updateEvaluation->student_evaluation = ($value != -1) ? $value : null;

			$updateEvaluation->starttime = $starttime;
			$updateEvaluation->endtime = $endtime;
	}
	if($record = $DB->get_record(block_exacomp::DB_EXAMPLEEVAL,array("studentid" => $userid, "courseid" => $courseid, "exampleid" => $exampleid))) {
		//if teacher keep studenteval
		if($role == block_exacomp::ROLE_TEACHER) {
			$record->teacher_evaluation = $updateEvaluation->teacher_evaluation;
			$record->teacher_reviewerid = $updateEvaluation->teacher_reviewerid;
			if($additionalinfo !== null) $record->additionalinfo = $updateEvaluation->additionalinfo;
			$record->resubmission = $updateEvaluation->resubmission;

			$DB->update_record(block_exacomp::DB_EXAMPLEEVAL,$record);
		} else {
			//if student keep teachereval
			$updateEvaluation->teacher_evaluation = $record->teacher_evaluation;
			$updateEvaluation->teacher_reviewerid = $record->teacher_reviewerid;
			$updateEvaluation->id = $record->id;
			$DB->update_record(block_exacomp::DB_EXAMPLEEVAL,$updateEvaluation);
		}
		return $record->id;
	}
	else {
		$updateEvaluation->courseid = $courseid;
		$updateEvaluation->exampleid = $exampleid;
		$updateEvaluation->studentid = $userid;

		return $DB->insert_record(block_exacomp::DB_EXAMPLEEVAL, $updateEvaluation);
	}

	if($role == block_exacomp::ROLE_TEACHER)
		\block_exacomp\event\competence_assigned::log(['objectid' => $exampleid, 'courseid' => $courseid, 'relateduserid' => $userid]);
}
function block_exacomp_allow_resubmission($userid, $exampleid, $courseid) {
	global $DB,$USER;

	block_exacomp_require_teacher($courseid);

	$exameval = $DB->get_record(block_exacomp::DB_EXAMPLEEVAL, array('courseid'=>$courseid,'studentid'=>$userid,'exampleid'=>$exampleid));
	if($exameval) {
		$exameval->resubmission = 1;
		$DB->update_record(block_exacomp::DB_EXAMPLEEVAL, $exameval);
		return get_string('allow_resubmission_info','block_exacomp');
	}

	return false;
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

	if($record = $DB->get_record(block_exacomp::DB_COMPETENCIES_USER_MM, array("userid" => $userid, "compid" => $compid, "comptype" => $comptype, 'activityid'=>$activityid, "role" => $role))) {
		$record->value = $value;
		$record->timestamp = time();
		$DB->update_record(block_exacomp::DB_COMPETENCIES_USER_MM, $record);
	} else {
		$DB->insert_record(block_exacomp::DB_COMPETENCIES_USER_MM, array("userid" => $userid, "compid" => $compid, "comptype" => $comptype, 'activityid'=>$activityid, "role" => $role, "value" => $value, "reviewerid" => $USER->id, "timestamp" => time()));
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
		block_exacomp_reset_comp_data($courseid, $role, $comptype, (($role == block_exacomp::ROLE_STUDENT)) ? $USER->id : false, $topicid);
	else {
		$studentid = ($role == block_exacomp::ROLE_STUDENT) ? $USER->id : required_param('studentid', PARAM_INT);
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
	block_exacomp_reset_comp_activity_data($courseid, $role, $comptype, (($role == block_exacomp::ROLE_STUDENT)) ? $USER->id : false, $activityid);

	foreach ($values as $value)
		block_exacomp_set_user_competence_activity($value['user'], $value['compid'], $comptype, $value['activityid'], $role, $value['value']);
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

	if($role == block_exacomp::ROLE_TEACHER){
		foreach($activities as $activity)
			$DB->delete_records(block_exacomp::DB_COMPETENCIES_USER_MM, array("activityid" => $activity->id, "role" => $role, "comptype" => $comptype));
	}else{
		foreach($activities as $activity)
			$DB->delete_records(block_exacomp::DB_COMPETENCIES_USER_MM, array("activityid" => $activity->id, "role" => $role,  "comptype" => $comptype, "userid"=>$userid));
	}
}

/**
 * Delete timestamp for exampleid
 */
function block_exacomp_delete_timefield($exampleid, $deletestart, $deleteent){
	global $USER;

	$updateid = $DB->get_field(block_exacomp::DB_EXAMPLEEVAL, 'id', array('exampleid'=>$exampleid, 'studentid'=>$USER->id));
	$update = new stdClass();
	$update->id = $updateid;
	if($deletestart==1)
		$update->starttime = null;
	elseif($deleteend==1)
	$update->endtime = null;

	$DB->update_record(block_exacomp::DB_EXAMPLEEVAL, $update);
}

/**
 * Gets settings for the current course
 * @param int $courseid
 */
function block_exacomp_get_settings_by_course($courseid = 0) {
	global $DB, $COURSE;

	if (!$courseid)
		$courseid = $COURSE->id;

	$settings = $DB->get_record(block_exacomp::DB_SETTINGS, array("courseid" => $courseid));

	if (empty($settings)) $settings = new stdClass;
	if (empty($settings->grading)) $settings->grading = 1;
	if (empty($settings->nostudents)) $settings->nostudents = 0;
	if (!isset($settings->uses_activities)) $settings->uses_activities = (block_exacomp_is_altversion() || block_exacomp_is_skillsmanagement())? 0 : 1;
	if (!isset($settings->show_all_examples)) $settings->show_all_examples = (block_exacomp_is_skillsmanagement()) ? 1 : 0;
	if (!isset($settings->usedetailpage)) $settings->usedetailpage = 0;
	if (!$settings->uses_activities) $settings->show_all_descriptors = 1;
	elseif (!isset($settings->show_all_descriptors)) $settings->show_all_descriptors = 0;
	if(isset($settings->filteredtaxonomies)) $settings->filteredtaxonomies = json_decode($settings->filteredtaxonomies,true);
	else $settings->filteredtaxonomies = array(SHOW_ALL_TAXONOMIES);

	// actually this is a global setting now
	$settings->useprofoundness = get_config('exacomp', 'useprofoundness');

	return $settings;
}

function block_exacomp_is_skillsmanagement() {
	return get_config('exacomp', 'skillsmanagement');
}
function block_exacomp_is_altversion() {
	return get_config('exacomp', 'alternativedatamodel');
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
	FROM {'.block_exacomp::DB_DESCRIPTORS.'} d,
	{'.block_exacomp::DB_COURSETOPICS.'} c,
	{'.block_exacomp::DB_DESCTOPICS.'} t,
	{'.block_exacomp::DB_TOPICS.'} tp,
	{'.block_exacomp::DB_SUBJECTS.'} s
	WHERE d.id=t.descrid AND t.topicid = c.topicid AND t.topicid=tp.id AND tp.subjid = s.id AND c.courseid = ?';

	if ($onlywithactivitys==1){
		$descr=block_exacomp_get_descriptors($courseid, block_exacomp_get_settings_by_course($courseid)->show_all_descriptors);
		if ($descr=="") $descr=0;
		$query.=" AND d.id IN (".$descr.")";
	}
	$query.= " ORDER BY s.sorting, s.title, tp.title,d.sorting";
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

	if (!$courseid) {
		$showalldescriptors = true;
		$showonlyvisible = false;
		$mindvisibility = false;
	}
	if(!$showalldescriptors)
		$showalldescriptors = block_exacomp_get_settings_by_course($courseid)->show_all_descriptors;


	$sql = 'SELECT DISTINCT desctopmm.id as u_id, d.id as id, d.title, d.source, d.niveauid, t.id AS topicid, \'descriptor\' as tabletype, d.profoundness, d.parentid, n.sorting niveau, dvis.visible as visible, d.sorting '
	.' FROM {'.block_exacomp::DB_TOPICS.'} t '
	.(($courseid>0)?' JOIN {'.block_exacomp::DB_COURSETOPICS.'} topmm ON topmm.topicid=t.id AND topmm.courseid=? ' . (($subjectid > 0) ? ' AND t.subjid = '.$subjectid.' ' : '') :'')
	.' JOIN {'.block_exacomp::DB_DESCTOPICS.'} desctopmm ON desctopmm.topicid=t.id '
	.' JOIN {'.block_exacomp::DB_DESCRIPTORS.'} d ON desctopmm.descrid=d.id AND d.parentid=0 '
	.' -- left join, because courseid=0 has no descvisibility!
		LEFT JOIN {'.block_exacomp::DB_DESCVISIBILITY.'} dvis ON dvis.descrid=d.id AND dvis.studentid=0 AND dvis.courseid=?'
	.($showonlyvisible?' AND dvis.visible = 1 ':'')
	.' LEFT JOIN {'.block_exacomp::DB_NIVEAUS.'} n ON d.niveauid = n.id '
	.($showalldescriptors ? '' : '
			JOIN {'.block_exacomp::DB_COMPETENCE_ACTIVITY.'} da ON d.id=da.compid AND da.comptype='.TYPE_DESCRIPTOR.'
			JOIN {course_modules} a ON da.activityid=a.id '.(($courseid>0)?'AND a.course=?':''))
	.' ORDER BY topicid, niveau, d.sorting';

	$descriptors = $DB->get_records_sql($sql, array($courseid, $courseid, $courseid, $courseid));

	foreach($descriptors as &$descriptor) {
		//get examples
		$descriptor = block_exacomp_get_examples_for_descriptor($descriptor, $filteredtaxonomies, $showallexamples, $courseid);
		   //check for child-descriptors
		$descriptor->children = block_exacomp_get_child_descriptors($descriptor,$courseid, $showalldescriptors, $filteredtaxonomies, $showallexamples, true, $showonlyvisible);

		//get categories
		$descriptor->categories = block_exacomp_get_categories_for_descriptor($descriptor);
	}

	//TODO: use sort_items with niveau
	return $descriptors;
	// return block_exacomp_sort_items($descriptors, block_exacomp::DB_DESCRIPTORS);
}

function block_exacomp_get_categories_for_descriptor($descriptor){
	global $DB;
	//im upgrade skript zugriff auf diese funktion obwohl die tabelle erst sp�ter akutalisiert wird
	static $table_exists = false;
	if (!$table_exists) {
	   $dbman = $DB->get_manager();
	   $table = new xmldb_table('block_exacompdescrcat_mm');
	   if (!$table_exists = $dbman->table_exists($table)) {
		   return array();
	   }
	}

	$categories = $DB->get_records_sql("
		SELECT c.*
		FROM {".block_exacomp::DB_CATEGORIES."} c
		JOIN {".block_exacomp::DB_DESCCAT."} dc ON dc.catid=c.id
		WHERE dc.descrid=?
		ORDER BY c.sorting
	", array($descriptor->id));

	return $categories;
}
function block_exacomp_get_child_descriptors($parent, $courseid, $showalldescriptors = false, $filteredtaxonomies = array(SHOW_ALL_TAXONOMIES), $showallexamples = true, $mindvisibility = true, $showonlyvisible=false ) {
	global $DB;

	if(!$DB->record_exists(block_exacomp::DB_DESCRIPTORS, array("parentid" => $parent->id))) {
		return array();
	}

	if (!$courseid) {
		$showalldescriptors = true;
		$showonlyvisible = false;
		$mindvisibility = false;
	}
	if(!$showalldescriptors)
		$showalldescriptors = block_exacomp_get_settings_by_course($courseid)->show_all_descriptors;

	$sql = 'SELECT d.id, d.title, d.niveauid, d.source, \'descriptor\' as tabletype, '.$parent->topicid.' as topicid, d.profoundness, d.parentid, '.
			($mindvisibility?'dvis.visible as visible, ':'').' d.sorting
			FROM {'.block_exacomp::DB_DESCRIPTORS.'} d '
			.($mindvisibility ? 'JOIN {'.block_exacomp::DB_DESCVISIBILITY.'} dvis ON dvis.descrid=d.id AND dvis.courseid=? AND dvis.studentid=0 '
			.($showonlyvisible? 'AND dvis.visible=1 ':'') : '');

	/* activity association only for parent descriptors
			.($showalldescriptors ? '' : '
				JOIN {'.block_exacomp::DB_COMPETENCE_ACTIVITY.'} da ON d.id=da.compid AND da.comptype='.TYPE_DESCRIPTOR.'
				JOIN {course_modules} a ON da.activityid=a.id '.(($courseid>0)?'AND a.course=?':''));
	*/
	$sql .= ' WHERE d.parentid = ?';

	$params = array();
	if($mindvisibility)
		$params[] = $courseid;

	$params[]= $parent->id;
	//$descriptors = $DB->get_records_sql($sql, ($showalldescriptors) ? array($parent->id) : array($courseid,$parent->id));
	$descriptors = $DB->get_records_sql($sql,  $params);

	foreach($descriptors as $descriptor) {
		$descriptor = block_exacomp_get_examples_for_descriptor($descriptor, $filteredtaxonomies, $showallexamples, $courseid);
		$descriptor->children = block_exacomp_get_child_descriptors($descriptor, $courseid,$showalldescriptors,$filteredtaxonomies);
		$descriptor->categories = block_exacomp_get_categories_for_descriptor($descriptor);
	}

	return block_exacomp_sort_items($descriptors, block_exacomp::DB_DESCRIPTORS);
}

function block_exacomp_get_examples_for_descriptor($descriptor, $filteredtaxonomies = array(SHOW_ALL_TAXONOMIES),$showallexamples = true, $courseid = null, $mind_visibility=true, $showonlyvisible = false ) {
	global $DB, $COURSE;

	if($courseid == null)
		$courseid = $COURSE->id;

	$examples = $DB->get_records_sql(
			"SELECT de.id as deid, e.id, e.title, e.externalurl, e.source, ".
				($mind_visibility?"evis.visible,":"")."
				e.externalsolution, e.externaltask, e.completefile, e.description, e.creatorid, e.iseditable, e.tips, e.timeframe, e.author
				FROM {" . block_exacomp::DB_EXAMPLES . "} e
				JOIN {" . block_exacomp::DB_DESCEXAMP . "} de ON e.id=de.exampid AND de.descrid=?"
			.($mind_visibility?' JOIN {'.block_exacomp::DB_EXAMPVISIBILITY.'} evis ON evis.exampleid= e.id AND evis.studentid=0 AND evis.courseid=? '
			.($showonlyvisible?' AND evis.visible = 1 ':''):'')
			. " WHERE "
			. " e.source != " . block_exacomp::EXAMPLE_SOURCE_USER . " AND "
			. (($showallexamples) ? " 1=1 " : " e.creatorid > 0")
			. " ORDER BY de.sorting"
			, array($descriptor->id, $courseid));

	$examples = \block_exacomp\example::create_objects($examples);

	foreach($examples as $example){
		$example->taxonomies = block_exacomp_get_taxonomies_by_example($example);

		$taxtitle = "";
		foreach($example->taxonomies as $taxonomy){
			$taxtitle .= $taxonomy->title.", ";
		}

		$taxtitle = substr($taxtitle, 0, strlen($taxtitle)-1);
		$example->tax = $taxtitle;
	}
	$filtered_examples = array();
	if(!in_array(SHOW_ALL_TAXONOMIES, $filteredtaxonomies)){
		$filtered_taxonomies = implode(",", $filteredtaxonomies);

		foreach($examples as $example){
			foreach($examples->taxonomies as $taxonomy){
				if(in_array($taxonomy->id, $filtered_taxonomies)){
					if(!array_key_exists($example->id, $filtered_examples))
						$filtered_examples[$example->id] = $example;
					continue;
				}
			}
		}
	}else{
		$filtered_examples = $examples;
	}

	$descriptor->examples = array();
	foreach($filtered_examples as $example){
		$descriptor->examples[$example->id] = $example;
	}

	return $descriptor;
}

function block_exacomp_get_taxonomies_by_example($example){
	global $DB;

	return $DB->get_records_sql("
		SELECT tax.*
		FROM {".block_exacomp::DB_TAXONOMIES."} tax
		JOIN {".block_exacomp::DB_EXAMPTAX."} et ON tax.id = et.taxid
		WHERE et.exampleid = ?
		ORDER BY tax.sorting
	", array($example->id));
}
/**
 * Returns descriptors for a given topic
 *
 * @param int $courseid
 * @param int $topicid
 * @param bool $showalldescriptors
 */
function block_exacomp_get_descriptors_by_topic($courseid, $topicid, $showalldescriptors = false, $mind_visibility=false, $showonlyvisible=true) {
	global $DB;

	if(!$showalldescriptors)
		$showalldescriptors = block_exacomp_get_settings_by_course($courseid)->show_all_descriptors;

	$sql = '(SELECT DISTINCT d.id, desctopmm.id as u_id, d.title, d.niveauid, t.id AS topicid, \'descriptor\' as tabletype, d.requirement, d.knowledgecheck, d.benefit, d.sorting, n.title as cattitle '
	.'FROM {'.block_exacomp::DB_TOPICS.'} t JOIN {'.block_exacomp::DB_COURSETOPICS.'} topmm ON topmm.topicid=t.id AND topmm.courseid=? ' . (($topicid > 0) ? ' AND t.id = '.$topicid.' ' : '')
	.'JOIN {'.block_exacomp::DB_DESCTOPICS.'} desctopmm ON desctopmm.topicid=t.id '
	.'JOIN {'.block_exacomp::DB_DESCRIPTORS.'} d ON desctopmm.descrid=d.id AND d.parentid=0 '
	. 'LEFT JOIN {'.block_exacomp::DB_NIVEAUS.'} n ON n.id = d.niveauid '
	.($mind_visibility?'JOIN {'.block_exacomp::DB_DESCVISIBILITY.'} dvis ON dvis.descrid=d.id AND dvis.studentid=0 AND dvis.courseid=? '
	.($showonlyvisible?'AND dvis.visible = 1 ':''):'')
	.($showalldescriptors ? '' : '
			JOIN {'.block_exacomp::DB_COMPETENCE_ACTIVITY.'} da ON d.id=da.compid AND da.comptype='.TYPE_DESCRIPTOR.'
			JOIN {course_modules} a ON da.activityid=a.id '.(($courseid>0)?'AND a.course=?':'')).')';

	$sql .= ' ORDER BY d.sorting';
	$descriptors = $DB->get_records_sql($sql, array($courseid, $courseid, $courseid));


	foreach($descriptors as $descriptor){
		$descriptor->categories = block_exacomp_get_categories_for_descriptor($descriptor);
	}
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

	$sql = "SELECT d.*, dt.topicid, t.title as topic FROM {".block_exacomp::DB_DESCRIPTORS."} d, {".block_exacomp::DB_DESCTOPICS."} dt, {".block_exacomp::DB_TOPICS."} t
	WHERE d.id=dt.descrid AND d.parentid =0 AND dt.topicid IN (SELECT id FROM {".block_exacomp::DB_TOPICS."} WHERE subjid=?)";
	if($niveaus) $sql .= " AND d.niveauid > 0";
	$sql .= " AND dt.topicid = t.id order by d.skillid, dt.topicid, d.niveauid";

	return $DB->get_records_sql($sql,array($subjectid));
}

function block_exacomp_get_descriptor_mms_by_example($exampleid) {
	global $DB;

	return $DB->get_records('block_exacompdescrexamp_mm',array('exampid' => $exampleid));
}

function block_exacomp_get_descriptors_by_example($exampleid) {
	global $DB;

	return $DB->get_records_sql("
		SELECT d.*, de.id AS descexampid
		FROM {".block_exacomp::DB_DESCRIPTORS."} d
		JOIN {".block_exacomp::DB_DESCEXAMP."} de ON de.descrid=d.id
		WHERE de.exampid = ?
	", [$exampleid]);
}

/**
 * Gets an associative array that is used to display the whole hierarchie of subjects, topics and competencies within a course
 *
 * @param int $courseid
 * @param int $subjectid
 * @param int $topicid
 * @return associative_array
 */
function block_exacomp_get_competence_tree($courseid = 0, $subjectid = null, $topicid = null, $showalldescriptors = false, $niveauid = null, $showallexamples = true, $filteredtaxonomies = array(SHOW_ALL_TAXONOMIES), $calledfromoverview = false, $calledfromactivities = false, $showonlyvisible=false, $without_descriptors=false) {
	global $DB;

	if(!$showalldescriptors)
		$showalldescriptors = block_exacomp_get_settings_by_course($courseid)->show_all_descriptors;

	$selectedTopic = null;
	if($topicid && $calledfromoverview) {
		$selectedTopic = $DB->get_record(block_exacomp::DB_TOPICS,array('id'=>$topicid));
	}

	// 1. GET SUBJECTS
	if($courseid == 0)
		$allSubjects = block_exacomp_get_all_subjects();
	elseif($subjectid) {
		$allSubjects = array($subjectid => block_exacomp_get_subject_by_id($subjectid));
	}
	else
		$allSubjects = block_exacomp_get_subjects_by_course($courseid, $showalldescriptors);

	// 2. GET TOPICS
	$allTopics = block_exacomp_get_all_topics($subjectid);
	if($courseid > 0) {
		if((!$calledfromoverview && !$calledfromactivities) || !$selectedTopic) {
			$courseTopics = block_exacomp_get_topics_by_subject($courseid, $subjectid);
		}
		elseif(isset($selectedTopic))
			$courseTopics = block_exacomp_topic::get($selectedTopic->id);

		if (!$courseTopics) {
			$courseTopics = array();
		} elseif (is_object($courseTopics)) {
			// could be only one topic, see block_exacomp_get_topic_by_id above
			$courseTopics = array($courseTopics->id => $courseTopics);
		}
	}

	// 3. GET DESCRIPTORS
	if($without_descriptors)
		$allDescriptors = array();
	else
		$allDescriptors = block_exacomp_get_descriptors($courseid, $showalldescriptors,0,$showallexamples, array(SHOW_ALL_TAXONOMIES), $showonlyvisible);

	foreach ($allDescriptors as $descriptor) {

		if($niveauid != SHOW_ALL_NIVEAUS && $calledfromoverview)
			if($descriptor->niveauid != $niveauid)
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
/**
 *
 * @param unknown $courseid
 * @param unknown $subjectid the subjectid
 * @param unknown $topicid the descriptorid
 * @param unknown $editmode
 * @param string $isTeacher
 * @param number $studentid set if he is a student
 * @return multitype:unknown Ambigous <stdClass, unknown>
 */
function block_exacomp_init_overview_data($courseid, $subjectid, $topicid, $niveauid, $editmode, $isTeacher=true, $studentid=0) {
	$courseTopics = block_exacomp_get_topics_by_course($courseid);
	$courseSubjects = block_exacomp_get_subjects_by_course($courseid);

	$selectedSubject = null;
	$selectedTopic = null;

	if ($subjectid) {
		if (!empty($courseSubjects[$subjectid])) {
			$selectedSubject = $courseSubjects[$subjectid];

			$topics = block_exacomp_get_topics_by_subject($courseid, $selectedSubject->id);
			if ($topicid == BLOCK_EXACOMP_SHOW_ALL) {
				// no $selectedTopic
			} elseif ($topicid && isset($topics[$topicid])) {
				$selectedTopic = $topics[$topicid];
			} else {
				// select first
				$selectedTopic = reset($topics);
			}
		}
	}
	if (!$selectedSubject && $topicid) {
		if (isset($courseTopics[$topicid])) {
			$selectedTopic = $courseTopics[$topicid];
			$selectedSubject = $courseSubjects[$selectedTopic->subjid];
		}
	}
	if (!$selectedSubject) {
		// select the first subject
		$selectedSubject = reset($courseSubjects);
		$topics = block_exacomp_get_topics_by_subject($courseid, $selectedSubject->id);
		$selectedTopic = reset($topics);
	}

	// load all descriptors first (needed for teacher)
	if ($editmode) {
		$descriptors = block_exacomp_get_descriptors_by_topic ( $courseid, $selectedTopic ? $selectedTopic->id : null, true, false, false);
	} else {
		$descriptors = block_exacomp_get_descriptors_by_topic ( $courseid, $selectedTopic ? $selectedTopic->id : null, false, true, true);
	}

	if (!$isTeacher) {
		// for students check student visibility
		$descriptors = array_filter($descriptors,
			function($descriptor) use ($courseid, $studentid) {
				return block_exacomp_is_descriptor_visible($courseid, $descriptor, $studentid);
			});
	}

	// get niveau ids from descriptors
	$niveau_ids = array();
	foreach ($descriptors as $descriptor) {
		$niveau_ids[$descriptor->niveauid] = $descriptor->niveauid;
	}

	// load niveaus from db
	$niveaus = g::$DB->get_records_list(block_exacomp::DB_NIVEAUS, 'id', $niveau_ids, 'sorting');
	$niveaus = \block_exacomp\niveau::create_objects($niveaus);

	$defaultNiveau = \block_exacomp\niveau::create();
	$defaultNiveau->id = SHOW_ALL_NIVEAUS;
	$defaultNiveau->title = get_string ( 'alltopics', 'block_exacomp' );

	$niveaus = array($defaultNiveau->id => $defaultNiveau) + $niveaus;

	if (isset($niveaus[$niveauid])) {
		$selectedNiveau = $niveaus[$niveauid];
	} else {
		// default: show all
		$selectedNiveau = reset($niveaus);
	}

	// add topics to subjects
	foreach ($courseSubjects as $subject) $subject->topics = [];
	foreach ($courseTopics as $topic) $courseSubjects[$topic->subjid]->topics[$topic->id] = $topic;

	return array($courseSubjects, $courseTopics, $niveaus, $selectedSubject, $selectedTopic, $selectedNiveau);
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
	return get_enrolled_users($context,'block/exacomp:teacher');
}

/**
 * Returns all the import information for a particular user in the given course about his competencies, topics and example evaluation values
 *
 * It returns user objects in the following format
 *		 $user
 *			 ->competencies
 *				 ->teacher[competenceid] = competence value
 *				 ->student[competenceid] = competence value
 *			 ->topics
 *				 ->teacher
 *				 ->student
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
		$user->examples = block_exacomp_get_user_examples_by_course($user, $courseid);
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
	$user->crosssubs->teacher = $DB->get_records_menu(block_exacomp::DB_COMPETENCIES,array("courseid" => $courseid, "userid" => $user->id, "role" => block_exacomp::ROLE_TEACHER, "comptype" => TYPE_CROSSSUB),'','compid as id, value');
	$user->crosssubs->student = $DB->get_records_menu(block_exacomp::DB_COMPETENCIES,array("courseid" => $courseid, "userid" => $user->id, "role" => block_exacomp::ROLE_STUDENT, "comptype" => TYPE_CROSSSUB),'','compid as id, value');
	$user->crosssubs->timestamp_teacher = $DB->get_records_menu(block_exacomp::DB_COMPETENCIES,array("courseid" => $courseid, "userid" => $user->id, "role" => block_exacomp::ROLE_TEACHER, "comptype" => TYPE_CROSSSUB),'','compid as id, timestamp');
	$user->crosssubs->timestamp_student = $DB->get_records_menu(block_exacomp::DB_COMPETENCIES,array("courseid" => $courseid, "userid" => $user->id, "role" => block_exacomp::ROLE_STUDENT, "comptype" => TYPE_CROSSSUB),'','compid as id, timestamp');

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
	$user->competencies->teacher = $DB->get_records_menu(block_exacomp::DB_COMPETENCIES,array("courseid" => $courseid, "userid" => $user->id, "role" => block_exacomp::ROLE_TEACHER, "comptype" => TYPE_DESCRIPTOR),'','compid as id, value');
	$user->competencies->student = $DB->get_records_menu(block_exacomp::DB_COMPETENCIES,array("courseid" => $courseid, "userid" => $user->id, "role" => block_exacomp::ROLE_STUDENT, "comptype" => TYPE_DESCRIPTOR),'','compid as id, value');
	$user->competencies->timestamp_teacher = $DB->get_records_menu(block_exacomp::DB_COMPETENCIES,array("courseid" => $courseid, "userid" => $user->id, "role" => block_exacomp::ROLE_TEACHER, "comptype" => TYPE_DESCRIPTOR),'','compid as id, timestamp');
	$user->competencies->timestamp_student = $DB->get_records_menu(block_exacomp::DB_COMPETENCIES,array("courseid" => $courseid, "userid" => $user->id, "role" => block_exacomp::ROLE_STUDENT, "comptype" => TYPE_DESCRIPTOR),'','compid as id, timestamp');
	$user->competencies->teacher_additional_grading = $DB->get_records_menu(block_exacomp::DB_COMPETENCIES,array("courseid" => $courseid, "userid" => $user->id, "role" => block_exacomp::ROLE_TEACHER, "comptype" => TYPE_DESCRIPTOR),'','compid as id, additionalinfo');

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
	$user->topics->teacher = $DB->get_records_menu(block_exacomp::DB_COMPETENCIES,array("courseid" => $courseid, "userid" => $user->id, "role" => block_exacomp::ROLE_TEACHER, "comptype" => TYPE_TOPIC),'','compid as id, value');
	$user->topics->student = $DB->get_records_menu(block_exacomp::DB_COMPETENCIES,array("courseid" => $courseid, "userid" => $user->id, "role" => block_exacomp::ROLE_STUDENT, "comptype" => TYPE_TOPIC),'','compid as id, value');
	$user->topics->timestamp_teacher = $DB->get_records_menu(block_exacomp::DB_COMPETENCIES,array("courseid" => $courseid, "userid" => $user->id, "role" => block_exacomp::ROLE_TEACHER, "comptype" => TYPE_TOPIC),'','compid as id, timestamp');
	$user->topics->timestamp_student = $DB->get_records_menu(block_exacomp::DB_COMPETENCIES,array("courseid" => $courseid, "userid" => $user->id, "role" => block_exacomp::ROLE_STUDENT, "comptype" => TYPE_TOPIC),'','compid as id, timestamp');

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
	$examples = new stdClass();
	$examples->teacher = g::$DB->get_records_menu(block_exacomp::DB_EXAMPLEEVAL,array("courseid" => $courseid, "studentid" => $user->id),'','exampleid as id, teacher_evaluation as value');
	$examples->student = g::$DB->get_records_menu(block_exacomp::DB_EXAMPLEEVAL,array("courseid" => $courseid, "studentid" => $user->id),'','exampleid as id, student_evaluation as value');

	return $examples;
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

		$user->activities_topics->activities[$activity->id]->teacher += $DB->get_records_menu(block_exacomp::DB_COMPETENCIES_USER_MM,array("activityid" => $activity->id, "userid" => $user->id, "role" => block_exacomp::ROLE_TEACHER, "comptype" => TYPE_TOPIC),'','compid as id, value');
		$user->activities_topics->activities[$activity->id]->student += $DB->get_records_menu(block_exacomp::DB_COMPETENCIES_USER_MM,array("activityid" => $activity->id, "userid" => $user->id, "role" => block_exacomp::ROLE_STUDENT, "comptype" => TYPE_TOPIC),'','compid as id, value');
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
		$user->activities_competencies->activities[$activity->id]->teacher += $DB->get_records_menu(block_exacomp::DB_COMPETENCIES_USER_MM,array("activityid" => $activity->id, "userid" => $user->id, "role" => block_exacomp::ROLE_TEACHER, "comptype" => TYPE_DESCRIPTOR, "eportfolioitem"=>0),'','compid as id, value');
		$user->activities_competencies->activities[$activity->id]->student += $DB->get_records_menu(block_exacomp::DB_COMPETENCIES_USER_MM,array("activityid" => $activity->id, "userid" => $user->id, "role" => block_exacomp::ROLE_STUDENT, "comptype" => TYPE_DESCRIPTOR, "eportfolioitem"=>0),'','compid as id, value');
	}

	return $user;
}
function block_exacomp_build_navigation_tabs_settings($courseid){
	global $usebadges;
	$courseSettings = block_exacomp_get_settings_by_course($courseid);
	$settings_subtree = array();

	$settings_subtree[] = new tabobject('tab_teacher_settings_configuration', new moodle_url('/blocks/exacomp/edit_course.php', array('courseid'=>$courseid)), get_string("tab_teacher_settings_configuration", "block_exacomp"));
	if(!block_exacomp_is_skillsmanagement())
		$settings_subtree[] = new tabobject('tab_teacher_settings_selection', new moodle_url('/blocks/exacomp/courseselection.php', array('courseid'=>$courseid)), get_string("tab_teacher_settings_selection", "block_exacomp"));

	if (block_exacomp_is_activated($courseid))
		if ($courseSettings->uses_activities)
			$settings_subtree[] = new tabobject('tab_teacher_settings_assignactivities', new moodle_url('/blocks/exacomp/edit_activities.php', array('courseid'=>$courseid)), get_string("tab_teacher_settings_assignactivities", "block_exacomp"));

	if (block_exacomp_moodle_badges_enabled() && $usebadges) {
		$settings_subtree[] = new tabobject('tab_teacher_settings_badges', new moodle_url('/blocks/exacomp/edit_badges.php', array('courseid'=>$courseid)), get_string("tab_teacher_settings_badges", "block_exacomp"));
	}

	return $settings_subtree;
}
function block_exacomp_build_navigation_tabs_admin_settings($courseid){
	global $DB;

	$checkImport = block_exacomp_data::has_data();

	$settings_subtree = array();

	$settings_subtree[] = new tabobject('tab_admin_import', new moodle_url('/blocks/exacomp/import.php', array('courseid'=>$courseid)), get_string("tab_admin_import", "block_exacomp"));

	if ($checkImport && has_capability('block/exacomp:admin', context_system::instance()))
		$settings_subtree[] = new tabobject('tab_admin_configuration', new moodle_url('/blocks/exacomp/edit_config.php',array("courseid"=>$courseid)),get_string('tab_admin_configuration','block_exacomp'));

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
	global $DB, $USER, $usebadges, $specificimport;

	$globalcontext = context_system::instance();

	$courseSettings = block_exacomp_get_settings_by_course($courseid);
	$ready_for_use = block_exacomp_is_ready_for_use($courseid);

	$de = false;
	$lang = current_language();
	if(isset($lang) && substr( $lang, 0, 2) === 'de'){
			$de = true;
	}
	if(block_exacomp_is_skillsmanagement())
		$checkConfig = block_exacomp_is_configured($courseid);
	else
		$checkConfig = block_exacomp_is_configured();

	$has_data = \block_exacomp_data::has_data();

	$rows = array();

	$isTeacher = block_exacomp_is_teacher($context) && $courseid != 1;
	$isStudent = has_capability('block/exacomp:student', $context) && $courseid != 1 && !has_capability('block/exacomp:admin', $context);
	$isTeacherOrStudent = $isTeacher || $isStudent;

	if($checkConfig && $has_data){	//Modul wurde konfiguriert
		if ($isTeacherOrStudent && block_exacomp_is_activated($courseid)) {
			//Kompetenzraster
			$rows[] = new tabobject('tab_competence_grid', new moodle_url('/blocks/exacomp/competence_grid.php',array("courseid"=>$courseid)),get_string('tab_competence_grid','block_exacomp'));
		}
		if ($isTeacherOrStudent && $ready_for_use) {
			//Kompetenzüberblick
			$rows[] = new tabobject('tab_competence_overview', new moodle_url('/blocks/exacomp/assign_competencies.php',array("courseid"=>$courseid)),get_string('tab_competence_overview','block_exacomp'));

			if ($isTeacher || (block_exacomp_cross_subjects_exists() && block_exacomp_get_cross_subjects_by_course($courseid, $USER->id))) {
				// Cross subjects: always for teacher and for students if it there are cross subjects
				$rows[] = new tabobject('tab_cross_subjects', new moodle_url('/blocks/exacomp/cross_subjects_overview.php', array("courseid"=>$courseid)), get_string('tab_cross_subjects', 'block_exacomp'));
			}

			if (!$courseSettings->nostudents) {
				//Kompetenz-Detailansicht nur wenn mit Aktivitäten gearbeitet wird
				if ($courseSettings->uses_activities && $courseSettings->usedetailpage) {
					$rows[] = new tabobject('tab_competence_details', new moodle_url('/blocks/exacomp/competence_detail.php',array("courseid"=>$courseid)),get_string('tab_competence_details','block_exacomp'));
				}

				//Kompetenzprofil
				$rows[] = new tabobject('tab_competence_profile_profile', new moodle_url('/blocks/exacomp/competence_profile.php', array("courseid"=>$courseid)), get_string('tab_competence_profile',  'block_exacomp'));
			}

			if ($isTeacher && !$courseSettings->nostudents) {
				//Beispiel-Aufgaben
				$rows[] = new tabobject('tab_examples', new moodle_url('/blocks/exacomp/view_examples.php',array("courseid"=>$courseid)),get_string('tab_examples','block_exacomp'));

				//Lernagenda
				//$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/learningagenda.php', array('courseid'=>$courseid)), get_string('tab_learning_agenda', 'block_exacomp'), array('title'=>get_string('tab_learning_agenda', 'block_exacomp')));
				//$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/subject.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
			}

			if (!$courseSettings->nostudents) {
				//Wochenplan
				$rows[] = new tabobject('tab_weekly_schedule', new moodle_url('/blocks/exacomp/weekly_schedule.php',array("courseid"=>$courseid)),get_string('tab_weekly_schedule','block_exacomp'));
			}

			if ($isTeacher && !$courseSettings->nostudents) {
				if ($courseSettings->useprofoundness) {
					$rows[] = new tabobject('tab_profoundness', new moodle_url('/blocks/exacomp/profoundness.php',array("courseid"=>$courseid)),get_string('tab_profoundness','block_exacomp'));
				}

				//Meine Auszeichnungen
				//if (block_exacomp_moodle_badges_enabled() && $usebadges) {
				//$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/my_badges.php', array('courseid'=>$courseid)), get_string('tab_badges', 'block_exacomp'), array('title'=>get_string('tab_badges', 'block_exacomp')));
				//$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/pix/i/badge.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
				//}
			}
		}

		if ($isTeacher) {
			//Einstellungen
			$rows[] = new tabobject('tab_teacher_settings', new moodle_url('/blocks/exacomp/edit_course.php',array("courseid"=>$courseid)),get_string('tab_teacher_settings','block_exacomp'));
		}
	}

	//if has_data && checkSubjects -> Modul wurde konfiguriert
	//else nur admin sieht block und hat nur den link Modulkonfiguration
	if (has_capability('block/exacomp:admin', $globalcontext) && !block_exacomp_is_skillsmanagement()) {
		//Admin sieht immer Modulkonfiguration
		//Wenn Import schon erledigt, weiterleitung zu edit_config, ansonsten import.
		if($has_data){
			$rows[] = new tabobject('tab_admin_configuration', new moodle_url('/blocks/exacomp/edit_config.php',array("courseid"=>$courseid)),get_string('tab_admin_configuration','block_exacomp'));
		}
	}

	if ($de && !block_exacomp_is_skillsmanagement()) {
		//Hilfe
		$rows[] = new tabobject('tab_help', new moodle_url('/blocks/exacomp/help.php', array("courseid"=>$courseid)), get_string('tab_help', 'block_exacomp'));
	}

	return $rows;
}

function block_exacomp_build_breadcrum_navigation($courseid) {
	global $PAGE;
	$coursenode = $PAGE->navigation->find($courseid, navigation_node::TYPE_COURSE);
	$blocknode = $coursenode->add(get_string('pluginname','block_exacomp'));
	$blocknode->make_active();
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
define('BLOCK_EXACOMP_STUDENT_SELECTOR_OPTION_COMPETENCE_GRID_DROPDOWN', 3);
function block_exacomp_studentselector($students, $selected, $url, $option = null) {
	global $CFG;

	$studentsAssociativeArray = array();

	// make copy
	$url = new block_exacomp\url($url);
	$url->remove_params('studentid');

	if ($option == BLOCK_EXACOMP_STUDENT_SELECTOR_OPTION_EDITMODE)
		$studentsAssociativeArray[0]=get_string('no_student_edit', 'block_exacomp');
	 else if($option != BLOCK_EXACOMP_STUDENT_SELECTOR_OPTION_OVERVIEW_DROPDOWN)
		$studentsAssociativeArray[0]=get_string('no_student', 'block_exacomp');

	foreach($students as $student) {
		$studentsAssociativeArray[$student->id] = fullname($student);
	}

	if ($option == BLOCK_EXACOMP_STUDENT_SELECTOR_OPTION_OVERVIEW_DROPDOWN) {
		$studentsAssociativeArray[BLOCK_EXACOMP_SHOW_ALL_STUDENTS] = get_string('allstudents', 'block_exacomp');
	}
	if($option == BLOCK_EXACOMP_STUDENT_SELECTOR_OPTION_COMPETENCE_GRID_DROPDOWN) {
		$studentsAssociativeArray[BLOCK_EXACOMP_SHOW_STATISTIC] = get_string('statistic', 'block_exacomp');
	}

	$edit = optional_param('editmode', 0, PARAM_BOOL);

	return html_writer::select($studentsAssociativeArray, 'exacomp_competence_grid_select_student',$selected,true,
			array("data-url"=>$url,"disabled" => ($edit) ? "disabled" : ""));
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
	return $DB->get_records(block_exacomp::DB_EDULEVELS,null,'source');
}
/**
 *
 * Get schooltypes for particular education level
 * @param unknown_type $edulevel
 */
function block_exacomp_get_schooltypes($edulevel) {
	global $DB;

	return $DB->get_records(block_exacomp::DB_SCHOOLTYPES, array("elid" => $edulevel));
}
/**
 * Gets a subject's schooltype title
 *
 * @param object $subject
 * @return Ambigous <mixed, boolean>
 */
function block_exacomp_get_schooltype_title_by_subject($subject){
	global $DB;
	$subject = $DB->get_record(block_exacomp::DB_SUBJECTS, array('id'=>$subject->id));
	if ($subject) return $DB->get_field(block_exacomp::DB_SCHOOLTYPES, "title", array("id"=>$subject->stid));

}
/**
 * Get a schooltype by subject
 *
 * @param unknown_type $subject
 */
function block_exacomp_get_schooltype_by_subject($subject){
	global $DB;
	return $DB->get_record(block_exacomp::DB_SCHOOLTYPES, array("id"=>$subject->stid));
}
/**
 * Gets a topic's category
 *
 * @param object $topic
 */
function block_exacomp_get_category($topic){
	global $DB;
	if(isset($topic->catid))
		return $DB->get_record(block_exacomp::DB_CATEGORIES,array("id"=>$topic->catid));
}
/**
 * Gets assigned schooltypes for particular courseid
 *
 * @param int $typeid
 * @param int $courseid
 */
function block_exacomp_get_mdltypes($typeid, $courseid = 0) {
	global $DB;

	return $DB->get_record(block_exacomp::DB_MDLTYPES, array("stid" => $typeid, "courseid" => $courseid));
}
/**
 *
 * Assign a schooltype to a course
 * @param unknown_type $values
 * @param unknown_type $courseid
 */
function block_exacomp_set_mdltype($values, $courseid = 0) {
	global $DB;

	$DB->delete_records(block_exacomp::DB_MDLTYPES,array("courseid"=>$courseid));
	foreach ($values as $value) {
		$DB->insert_record(block_exacomp::DB_MDLTYPES, array("stid" => intval($value),"courseid" => $courseid));
	}

	block_exacomp_clean_course_topics($values, $courseid);
}

function block_exacomp_clean_course_topics($values, $courseid){
	global $DB;

	if($courseid == 0)
		// TODO: ist das korrekt so? sollte man nicht courseid=0 auslesen?
		$coutopics = $DB->get_records(block_exacomp::DB_COURSETOPICS);
	else
		$coutopics = $DB->get_records(block_exacomp::DB_COURSETOPICS, array('courseid'=>$courseid));

	foreach($coutopics as $coutopic){
		$sql = 'SELECT s.stid FROM {'.block_exacomp::DB_TOPICS.'} t
			JOIN {'.block_exacomp::DB_SUBJECTS.'} s ON t.subjid=s.id
			WHERE t.id=?';

		$schooltype = $DB->get_record_sql($sql, array($coutopic->topicid));

		if($schooltype && !array_key_exists($schooltype->stid, $values)){
			$DB->delete_records(block_exacomp::DB_COURSETOPICS, array('id'=>$coutopic->id));
		}
	}
}
/**
 * check if configuration is already finished
 * configuration is finished if schooltype is selected for course(LIS)/moodle(normal)
 */
function block_exacomp_is_configured($courseid=0){
	global $DB;
	return $DB->get_records(block_exacomp::DB_MDLTYPES, array("courseid"=>$courseid));
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

	$DB->delete_records(block_exacomp::DB_SETTINGS, array("courseid" => $courseid));

	if ($settings->grading > block_exacomp::SETTINGS_MAX_SCHEME) $settings->grading = block_exacomp::SETTINGS_MAX_SCHEME;

	//adapt old evaluation to new scheme
	//update compcompuser && compcompuser_mm && exameval
	if($old_course_settings->grading != $settings->grading){
		//block_exacompcompuser
		$records = $DB->get_records(block_exacomp::DB_COMPETENCIES, array('courseid'=>$courseid));
		foreach($records as $record){
			//if value is set and greater than zero->adapt to new scheme
			if(isset($record->value) && $record->value > 0){
				//calculate old percentage and apply it to new scheme
				$percent_old = $record->value / $old_course_settings->grading;
				$value_new = round($settings->grading * $percent_old);

				$update = new stdClass();
				$update->id = $record->id;
				$update->value = $value_new;
				$DB->update_record(block_exacomp::DB_COMPETENCIES, $update);

			}
		}

		//block_exacompcompuser_mm
		$records = $DB->get_records_sql('
			SELECT comp.id, comp.value
			FROM {'.block_exacomp::DB_COMPETENCIES_USER_MM.'} comp
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
				$DB->update_record(block_exacomp::DB_COMPETENCIES_USER_MM, $update);
			}
		}

		//block_exacompexampeval
		$records = $DB->get_records(block_exacomp::DB_EXAMPLEEVAL, array('courseid'=>$courseid));
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
				$DB->update_record(block_exacomp::DB_EXAMPLEEVAL, $update);
		}

	}

	$settings->courseid = $courseid;
	$settings->tstamp = time();

	$DB->insert_record(block_exacomp::DB_SETTINGS, $settings);
}
/**
 *
 * Check if there are already topics assigned to a course
 * @param int $courseid
 */
function block_exacomp_is_activated($courseid) {
	global $DB;

	return $DB->get_records(block_exacomp::DB_COURSETOPICS, array("courseid" => $courseid));
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
	$activities_assigned_to_any_course = $DB->get_records(block_exacomp::DB_COMPETENCE_ACTIVITY, array('eportfolioitem'=>0));
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
	global $DB;

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
				FROM {'.block_exacomp::DB_DESCRIPTORS.'} d
				JOIN {'.block_exacomp::DB_DESCBADGE.'} db ON d.id=db.descid AND db.badgeid=?
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
				FROM {'.block_exacomp::DB_DESCRIPTORS.'} d
				JOIN {'.block_exacomp::DB_DESCBADGE.'} db ON d.id=db.descid AND db.badgeid=?
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
			FROM {'.block_exacomp::DB_DESCRIPTORS.'} d
			JOIN {'.block_exacomp::DB_DESCBADGE.'} db ON d.id=db.descid AND db.badgeid=?
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
	$tree = block_exacomp_get_competence_tree($courseid, null, null, false, null, true, block_exacomp_get_settings_by_course($courseid)->filteredtaxonomies);

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
			FROM {'.block_exacomp::DB_COMPETENCE_ACTIVITY.'} mm
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
			FROM {'.block_exacomp::DB_COMPETENCE_ACTIVITY.'} mm
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
function block_exacomp_set_coursetopics($courseid, $topicids) {
	global $DB;

	$DB->delete_records(block_exacomp::DB_COURSETOPICS, array("courseid" => $courseid));

	$descriptors = array();
	$examples = array();
	foreach ($topicids as $topicid) {
		$DB->insert_record(block_exacomp::DB_COURSETOPICS, array("courseid" => $courseid, "topicid" => $topicid));

		//insert descriptors in block_exacompdescrvisibility
		$descriptors_topic = block_exacomp_get_descriptors_by_topic($courseid, $topicid);
		foreach($descriptors_topic as $descriptor){
			$descriptors[$descriptor->id] = $descriptor;
		}
	}

	block_exacomp_update_descriptor_visibilities($courseid, $descriptors);

	foreach($descriptors as $descriptor){
		 $descriptor = block_exacomp_get_examples_for_descriptor($descriptor, array(SHOW_ALL_TAXONOMIES), true, $courseid, false);
			foreach($descriptor->examples as $example){
				$examples[$example->id] = $example;
			}

			$descriptor->children = block_exacomp_get_child_descriptors($descriptor, $courseid);
			foreach($descriptor->children as $child){
				$child = block_exacomp_get_examples_for_descriptor($child, array(SHOW_ALL_TAXONOMIES), true, $courseid, false);
				foreach($child->examples as $example){
					$examples[$example->id] = $example;
				}
			}
	}

	block_exacomp_update_example_visibilities($courseid, $examples);

	// TODO: maybe move this whole part to block_exacomp_data::normalize_database() or better a new normalize_course($courseid);

	//delete unconnected examples
	//add blocking events to examples which are not deleted
	$blocking_events = $DB->get_records(block_exacomp::DB_EXAMPLES, array('blocking_event'=>1));

	foreach($blocking_events as $event){
		$examples[$event->id] = $event;
	}

	$where = $examples ? join(',', array_keys($examples)) : '-1';
	$DB->execute("DELETE FROM {".block_exacomp::DB_SCHEDULE."} WHERE courseid = ? AND exampleid NOT IN($where)", array($courseid));
}

/**
 *
 * given descriptor list is visible in cour
 * @param unknown_type $courseid
 * @param unknown_type $descriptors
 */
function block_exacomp_update_descriptor_visibilities($courseid, $descriptors){
	global $DB;

	$visibilities = $DB->get_fieldset_select(block_exacomp::DB_DESCVISIBILITY,'descrid', 'courseid=? AND studentid=0', array($courseid));

	//get all cross subject descriptors - to support cross-course subjects descriptor visibility must be kept
	$cross_subjects = $DB->get_records(block_exacomp::DB_CROSSSUBJECTS, array('courseid'=>$courseid));
	$cross_subjects_descriptors = array();

	foreach($cross_subjects as $crosssub){
		$cross_subject_descriptors = $DB->get_fieldset_select(block_exacomp::DB_DESCCROSS, 'descrid', 'crosssubjid=?', array($crosssub->id));
		foreach($cross_subject_descriptors as $descriptor)
			if(!in_array($descriptor, $cross_subjects_descriptors)){
				$cross_subjects_descriptors[] = $descriptor;
			}
	}

	$finaldescriptors=$descriptors;
	//manage visibility, do not delete user visibility, but delete unused entries
	foreach($descriptors as $descriptor){
		//new descriptors in table
		if(!in_array($descriptor->id, $visibilities)) {
			$visibilities[] = $descriptor->id;
			$DB->insert_record(block_exacomp::DB_DESCVISIBILITY, array("courseid"=>$courseid, "descrid"=>$descriptor->id, "studentid"=>0, "visible"=>1));
		}

		$descriptor->children = block_exacomp_get_child_descriptors($descriptor, $courseid, true, array(SHOW_ALL_TAXONOMIES), true, false);

		foreach($descriptor->children as $childdescriptor){
			if(!in_array($childdescriptor->id, $visibilities)) {
				$visibilities[] = $childdescriptor->id;
				$DB->insert_record(block_exacomp::DB_DESCVISIBILITY, array("courseid"=>$courseid, "descrid"=>$childdescriptor->id, "studentid"=>0, "visible"=>1));
			}

			if(!array_key_exists($childdescriptor->id, $finaldescriptors))
				$finaldescriptors[$childdescriptor->id] = $childdescriptor;
		}
	}

	foreach($visibilities as $visible){
		//delete ununsed descriptors for course and for special students
		if(!array_key_exists($visible, $finaldescriptors)){
			//check if used in cross-subjects --> then it must still be visible
			if(!in_array($visible, $cross_subjects_descriptors))
				$DB->delete_records(block_exacomp::DB_DESCVISIBILITY, array("courseid"=>$courseid, "descrid"=>$visible));
		}
	}
}

/**
 *
 * given example list is visible in cour
 * @param unknown_type $courseid
 * @param unknown_type $descriptors
 */
function block_exacomp_update_example_visibilities($courseid, $examples){
	global $DB;

	$visibilities = $DB->get_fieldset_select(block_exacomp::DB_EXAMPVISIBILITY,'exampleid', 'courseid=? AND studentid=0', array($courseid));

	//get all cross subject examples - to support cross-course subjects exampels visibility must be kept
	$cross_subjects = $DB->get_records(block_exacomp::DB_CROSSSUBJECTS, array('courseid'=>$courseid));
	$cross_subject_examples = array();

	foreach($cross_subjects as $crosssub){
		$cross_subject_descriptors = $DB->get_fieldset_select(block_exacomp::DB_DESCCROSS, 'descrid', 'crosssubjid=?', array($crosssub->id));
		foreach($cross_subject_descriptors as $descriptor){
			$descriptor = $DB->get_record(block_exacomp::DB_DESCRIPTORS, array('id'=>$descriptor));
			$descriptor = block_exacomp_get_examples_for_descriptor($descriptor, array(SHOW_ALL_TAXONOMIES), true, $courseid, false);
			foreach($descriptor->examples as $example)
				if(!in_array($example->id, $cross_subject_examples))
					$cross_subject_examples[] = $example->id;

			if($descriptor->parentid == 0){
				$descriptor_topic_mm = $DB->get_record(block_exacomp::DB_DESCTOPICS, array('descrid'=>$descriptor->id));

				$descriptor->topicid = $descriptor_topic_mm->topicid;
				$descriptor->children = block_exacomp_get_child_descriptors($descriptor, $courseid);
				foreach($descriptor->children as $child){
					$child = block_exacomp_get_examples_for_descriptor($child,  array(SHOW_ALL_TAXONOMIES), true, $courseid, false);
					foreach($child->examples as $example)
						if(!in_array($example->id, $cross_subject_examples))
							$cross_subject_examples[] = $example->id;
				}
			}
		}
	}

	$finalexamples = $examples;
	//manage visibility, do not delete user visibility, but delete unused entries
	foreach($examples as $example){
		//new example in table
		if(!in_array($example->id, $visibilities)) {
			$visibilities[] = $example->id;
			$DB->insert_record(block_exacomp::DB_EXAMPVISIBILITY, array("courseid"=>$courseid, "exampleid"=>$example->id, "studentid"=>0, "visible"=>1));
		}
	}

	foreach($visibilities as $visible){
		//delete ununsed descriptors for course and for special students
		if(!array_key_exists($visible, $finalexamples)){
			//check if used in cross-subjects --> then it must still be visible
			if(!in_array($visible, $cross_subject_examples))
				$DB->delete_records(block_exacomp::DB_EXAMPVISIBILITY, array("courseid"=>$courseid, "exampleid"=>$visible));
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
		$test->descriptors = $DB->get_records(block_exacomp::DB_COMPETENCE_ACTIVITY, array('activityid'=>$test->activityid, 'comptype'=>TYPE_DESCRIPTOR), null, 'compid');
		$test->topics = $DB->get_records(block_exacomp::DB_COMPETENCE_ACTIVITY, array('activityid'=>$test->activityid, 'comptype'=>TYPE_TOPIC), null, 'compid');
	}

	return $tests;
}
/**
 *
 * Returns all course ids where an instance of Exabis Competences is installed
 */
function block_exacomp_get_courseids(){
	$instances = g::$DB->get_records('block_instances', array('blockname'=>'exacomp'));

	$exabis_competences_courses = array();

	foreach($instances as $instance){
		$context = g::$DB->get_record('context', array('id'=>$instance->parentcontextid, 'contextlevel'=>CONTEXT_COURSE));
		if($context)
			$exabis_competences_courses[$context->instanceid] = $context->instanceid;
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
	else return new moodle_url('/mod/'.$mod->name.'/view.php', array('id'=>$activity->id));
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

	$DB->delete_records(block_exacomp::DB_COMPETENCE_ACTIVITY, array("activityid" => $activityid, "compid" => $compid, "comptype"=>$comptype, "eportfolioitem"=>0));
	$DB->insert_record(block_exacomp::DB_COMPETENCE_ACTIVITY, array("activityid" => $activityid, "compid" => $compid, "comptype"=>$comptype, "coursetitle"=>$COURSE->shortname, 'activitytitle'=>$instance->name));
}
/**
 *
 * Delete competence, activity associations
 */
function block_exacomp_delete_competencies_activities(){
	global $COURSE, $DB;

	$cmodules = $DB->get_records('course_modules', array('course'=>$COURSE->id));

	foreach($cmodules as $cm){
		$DB->delete_records(block_exacomp::DB_COMPETENCE_ACTIVITY, array('activityid'=>$cm->id, 'eportfolioitem'=>0));
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
	FROM {'.block_exacomp::DB_COMPETENCE_ACTIVITY.'} mm
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
	$query = 'SELECT DISTINCT mm.activityid as id, mm.activitytitle as title FROM {'.block_exacomp::DB_COMPETENCE_ACTIVITY.'} mm
		INNER JOIN {course_modules} a ON a.id=mm.activityid
		WHERE a.course = ? AND mm.eportfolioitem=0';
	return $DB->get_records_sql($query, array($courseid));
}
function block_exacomp_init_competence_grid_data($courseid, $subjectid, $studentid, $showallexamples = false, $filteredtaxonomies = array(SHOW_ALL_TAXONOMIES)) {
	global $DB;

	if($studentid) {
		$cm_mm = block_exacomp_get_course_module_association($courseid);
		$course_mods = get_fast_modinfo($courseid)->get_cms();
	}

	$selection = array();

		$niveaus = block_exacomp_get_niveaus_for_subject($subjectid);
		$skills = $DB->get_records_menu('block_exacompskills',null,null,"id, title");
		$descriptors = block_exacomp_get_descriptors_by_subject($subjectid);

		$supported = block_exacomp_get_supported_modules();

		$data = array();
		if($studentid)
			$competencies = array("studentcomps"=>$DB->get_records(block_exacomp::DB_COMPETENCIES,array("role"=>block_exacomp::ROLE_STUDENT,"courseid"=>$courseid,"userid"=>$studentid,"comptype"=>TYPE_DESCRIPTOR),"","compid,userid,reviewerid,value"),
					"teachercomps"=>$DB->get_records(block_exacomp::DB_COMPETENCIES,array("role"=>block_exacomp::ROLE_TEACHER,"courseid"=>$courseid,"userid"=>$studentid,"comptype"=>TYPE_DESCRIPTOR),"","compid,userid,reviewerid,value"));

		// Arrange data in associative array for easier use
		$topics = array();
		$data = array();
		foreach ($descriptors as $descriptor) {
			if($descriptor->parentid > 0) {
				continue;
			}

			$descriptor->children = $DB->get_records('block_exacompdescriptors',array('parentid'=>$descriptor->id));

			$examples = $DB->get_records_sql(
					"SELECT de.id as deid, e.id, e.title, e.externalurl,
					e.externalsolution, e.externaltask, e.completefile, e.description, e.creatorid
					FROM {" . block_exacomp::DB_EXAMPLES . "} e
					JOIN {" . block_exacomp::DB_DESCEXAMP . "} de ON e.id=de.exampid
					WHERE de.descrid=?"
					. ($showallexamples ? "" : " AND e.creatorid > 0")
					, array($descriptor->id));

			foreach($examples as $example){
				$example->taxonomies = block_exacomp_get_taxonomies_by_example($example);

				$taxtitle = "";
				foreach($example->taxonomies as $taxonomy){
					$taxtitle .= $taxonomy->title.", "; // TODO: beistrich am ende?
				}

				$taxtitle = substr($taxtitle, 0, strlen($taxtitle)-1);
				$example->tax = $taxtitle;
			}
			$filtered_examples = array();
			if(!in_array(SHOW_ALL_TAXONOMIES, $filteredtaxonomies)){
				$filtered_taxonomies = implode(",", $filteredtaxonomies);

				foreach($examples as $example){
					foreach($example->taxonomies as $taxonomy){
						if(in_array($taxonomy->id, $filtered_taxonomies)){
							if(!array_key_exists($example->id, $filtered_examples))
								$filtered_examples[$example->id] = $example;
							continue;
						}
					}
				}
			}else{
				$filtered_examples = $examples;
			}

			$descriptor->examples = array();
			foreach($filtered_examples as $example){
				$descriptor->examples[$example->id] = $example;
			}

			if($studentid && $studentid != BLOCK_EXACOMP_SHOW_STATISTIC) {
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

		$selection = $DB->get_records(block_exacomp::DB_COURSETOPICS,array('courseid'=>$courseid),'','topicid');

		return array($niveaus, $skills, $topics, $data, $selection);

}
function block_exacomp_get_niveaus_for_subject($subjectid) {
	global $DB;

	// TODO: besser formatieren und sql optimieren
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
						$niveaus[$descriptor->niveauid] = $DB->get_record(block_exacomp::DB_NIVEAUS, array('id'=>$descriptor->niveauid));
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
	$sql = "TRUNCATE {block_exacompdatasources}"; $DB->execute($sql);

	// TODO: tabellen block_exacompdescrvisibility, block_exacompitemexample, block_exacompschedule gehören auch gelöscht?
}

/**
 *
 * This method returns all courses the user is entrolled to and exacomp is installed
 */
function block_exacomp_get_exacomp_courses($user) {
	global $DB;
	$user_courses = array();
	//get course id from all courses where exacomp is installed
	$all_exacomp_courses = block_exacomp_get_courseids();

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
			if(block_exacomp_is_descriptor_visible($courseid, $descriptor, $student->id)){
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
		$sql = "SELECT DISTINCT c.id, c.userid, c.compid, c.role, c.courseid, c.value, c.comptype, c.timestamp FROM {".block_exacomp::DB_COMPETENCIES."} c, {".block_exacomp::DB_TOPICS."} t
			WHERE
			((c.comptype = 1 AND c.compid = t.id AND t.subjid = ?)
			OR
			(c.comptype = 0 AND c.compid IN
			 (
				SELECT dt.descrid FROM {".block_exacomp::DB_DESCTOPICS."} dt, {".block_exacomp::DB_TOPICS."} t WHERE dt.topicid = t.id AND t.subjid = ?
				 )
			))
			AND c.role = ? AND c.userid = ?
			ORDER BY c.courseid";
		$competencies = $DB->get_records_sql($sql,array($subject->id,$subject->id,block_exacomp::ROLE_TEACHER,$userid));
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

		$competencies = $DB->get_records_sql($sql,array($subject->id,$subject->id,block_exacomp::ROLE_STUDENT,$userid));
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

		$sql = "SELECT c.id, c.userid, c.compid, c.role, c.courseid, c.value, c.comptype, c.timestamp FROM {".block_exacomp::DB_COMPETENCIES."} c, {".block_exacomp::DB_DESCTOPICS."} dt
		WHERE c.compid = dt.descrid AND dt.topicid = ? AND c.comptype = 0 AND c.role=? AND c.userid = ? AND c.value >= ? AND c.courseid = ?";

		$competencies = $DB->get_records_sql($sql,array($topic->id,block_exacomp::ROLE_TEACHER,$studentid, ceil($scheme / 2), $courseid));

		$topic->teacher = 0;
		if(count($totalDescr)>0)
			$topic->teacher = (count($competencies) / count($totalDescr)) * 100;

		$sql = "SELECT c.id, c.userid, c.compid, c.role, c.courseid, c.value, c.comptype, c.timestamp FROM {".block_exacomp::DB_COMPETENCIES."} c, {".block_exacomp::DB_DESCTOPICS."} dt
		WHERE c.compid = dt.descrid AND dt.topicid = ? AND c.comptype = 0 AND c.role=? AND c.userid = ? AND c.value >= ? AND c.courseid = ?";

		$competencies = $DB->get_records_sql($sql,array($topic->id,block_exacomp::ROLE_STUDENT,$studentid, ceil($scheme/2),$courseid));

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
		if(block_exacomp_is_descriptor_visible($courseid, $descriptor, $user->id)){
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
	return class_exists('\block_exastud\api') && \block_exastud\api::active();
}
function block_exacomp_get_exastud_periods_with_review($userid){
	if (!block_exacomp_exastudexists()) {
		return [];
	} else {
		return \block_exastud\api::get_student_periods_with_review($userid);
	}
}
function block_exacomp_get_exaport_items($userid = 0){
	// TODO: change to \block_exastud\api::....

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
	$exacomp_settings = $DB->get_records(block_exacomp::DB_PROFILESETTINGS, array('block'=>'exacomp', 'userid'=>$userid));
	foreach($exacomp_settings as $setting){
		$profile_settings->exacomp[$setting->itemid] = $setting;
	}

	$profile_settings->exastud = array();
	$exastud_settings = $DB->get_records(block_exacomp::DB_PROFILESETTINGS, array('block'=>'exastud', 'userid'=>$userid));
	foreach($exastud_settings as $setting){
		$profile_settings->exastud[$setting->itemid] = $setting;
	}

	$profile_settings->showonlyreached=0;
	$showonlyreached = $DB->get_field(block_exacomp::DB_PROFILESETTINGS, 'itemid' ,array('block'=>'exacompdesc', 'userid'=>$userid));
	if($showonlyreached && $showonlyreached == 1)
		$profile_settings->showonlyreached = 1;

	$profile_settings->useexaport = 0;
	$useexaport = $DB->get_field(block_exacomp::DB_PROFILESETTINGS, 'itemid', array('block'=>'useexaport', 'userid'=>$userid));
	if($useexaport && $useexaport == 1)
		$profile_settings->useexaport = 1;

	$profile_settings->useexastud = 0;
	 $useexastud = $DB->get_field(block_exacomp::DB_PROFILESETTINGS, 'itemid', array('block'=>'useexastud', 'userid'=>$userid));
	if($useexastud && $useexastud == 1)
		$profile_settings->useexastud = 1;

	$profile_settings->usebadges = 0;
	$usebadges = $DB->get_field(block_exacomp::DB_PROFILESETTINGS, 'itemid', array('block'=>'usebadges', 'userid'=>$userid));
	 if($usebadges && $usebadges == 1)
		$profile_settings->usebadges = 1;

	$profile_settings->onlygainedbadges = 0;
	$onlygainedbadges = $DB->get_field(block_exacomp::DB_PROFILESETTINGS, 'itemid', array('block'=>'badges', 'userid'=>$userid));
	if($onlygainedbadges && $onlygainedbadges == 1)
		$profile_settings->onlygainedbadges = 1;

	$profile_settings->showallcomps = 0;
	$showallcomps = $DB->get_field(block_exacomp::DB_PROFILESETTINGS, 'itemid', array('block'=>'all', 'userid'=>$userid));
	if($showallcomps && $showallcomps == 1)
		$profile_settings->showallcomps = 1;

	return $profile_settings;
}

function block_exacomp_reset_profile_settings($userid){
	global $DB;
	$DB->delete_records(block_exacomp::DB_PROFILESETTINGS, array('userid'=>$userid));
}

function block_exacomp_set_profile_settings($userid, $showonlyreached, $usebadges, $onlygainedbadges, $showallcomps, $useexaport, $useexastud, $courses, $periods){
	global $DB;

	block_exacomp_reset_profile_settings($userid);

	//showonlyreached
	$insert = new stdClass();
	$insert->block = 'exacompdesc';
	$insert->itemid = intval($showonlyreached);
	$insert->feedback = '';
	$insert->userid = $userid;

	$DB->insert_record(block_exacomp::DB_PROFILESETTINGS, $insert);

	//usebadges
	$insert = new stdClass();
	$insert->block = 'usebadges';
	$insert->itemid = intval($usebadges);
	$insert->feedback = '';
	$insert->userid = $userid;

	$DB->insert_record(block_exacomp::DB_PROFILESETTINGS, $insert);

	//onlygainedbadges
	$insert = new stdClass();
	$insert->block = 'badges';
	$insert->itemid = intval($onlygainedbadges);
	$insert->feedback = '';
	$insert->userid = $userid;

	$DB->insert_record(block_exacomp::DB_PROFILESETTINGS, $insert);

	//showallcomps
	$insert = new stdClass();
	$insert->block = 'all';
	$insert->itemid = intval($showallcomps);
	$insert->feedback = '';
	$insert->userid = $userid;

	$DB->insert_record(block_exacomp::DB_PROFILESETTINGS, $insert);

	//useexaport
	$insert = new stdClass();
	$insert->block = 'useexaport';
	$insert->itemid = intval($useexaport);
	$insert->feedback = '';
	$insert->userid = $userid;

	$DB->insert_record(block_exacomp::DB_PROFILESETTINGS, $insert);

	//useexastud
	$insert = new stdClass();
	$insert->block = 'useexastud';
	$insert->itemid = intval($useexastud);
	$insert->feedback = '';
	$insert->userid = $userid;

	$DB->insert_record(block_exacomp::DB_PROFILESETTINGS, $insert);

	//save courses
	foreach($courses as $course){
		$insert = new stdClass();
		$insert->block = 'exacomp';
		$insert->itemid = $course;
		$insert->feedback = '';
		$insert->userid = $userid;

		$DB->insert_record(block_exacomp::DB_PROFILESETTINGS, $insert);
	}

	if($useexastud == 1){
		//save periods
		foreach($periods as $period){
			$insert = new stdClass();
			$insert->block = 'exastud';
			$insert->itemid = $period;
			$insert->feedback = '';
			$insert->userid = $userid;

			$DB->insert_record(block_exacomp::DB_PROFILESETTINGS, $insert);
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

		$DB->insert_record(block_exacomp::DB_PROFILESETTINGS, $insert);
	}
}
function block_exacomp_check_profile_config($userid){
	global $DB;

	return $DB->get_records(block_exacomp::DB_PROFILESETTINGS, array('userid'=>$userid));
}
function block_exacomp_init_exaport_items($items){
	global $DB;
	$profile_settings = block_exacomp_get_profile_settings();

	foreach($items as $item){
		$item_comps = $DB->get_records(block_exacomp::DB_COMPETENCE_ACTIVITY, array('activityid'=>$item->id, 'eportfolioitem'=>1));
		if($item_comps){
			$item->hascomps = true;
			$item->descriptors = array();
			$item->tabletype = 'item';
			foreach($item_comps as $item_comp){
				$item->descriptors[$item_comp->compid]  = $DB->get_record(block_exacomp::DB_DESCRIPTORS, array('id'=>$item_comp->compid));
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

		$db_review = $DB->get_record('block_exastudreview', array('studentid'=>$student->id, 'periodid'=>$period->id));

		$reviews[$period->id]->feedback = $db_review->review;
		$reviews[$period->id]->reviewer = $DB->get_record('user', array('id'=>$db_review->teacherid));
		$exastud_comps = $DB->get_records('block_exastudreviewpos', array('reviewid'=>$db_review->id, 'categorysource'=>'exastud'));
		$reviews[$period->id]->categories = array();
		foreach($exastud_comps as $cat){
			$reviews[$period->id]->categories[$cat->categoryid] = $DB->get_record('block_exastudcate', array('id'=>$cat->categoryid));
			$reviews[$period->id]->categories[$cat->categoryid]->evaluation = $cat->value;
		}

		$exacomp_comps = $DB->get_records('block_exastudreviewpos', array('reviewid'=>$db_review->id, 'categorysource'=>'exacomp'));
		$reviews[$period->id]->descriptors = array();
		foreach($exacomp_comps as $comp){
			$reviews[$period->id]->descriptors[$comp->categoryid] = $DB->get_record(block_exacomp::DB_DESCRIPTORS, array('id'=>$comp->categoryid));
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
function block_exacomp_build_schooltype_tree($courseid=0, $without_descriptors = false){
	$schooltypes = block_exacomp_get_schooltypes_by_course($courseid);

	foreach($schooltypes as $schooltype){
		$subjects = block_exacomp_get_subjects_for_schooltype($courseid, $schooltype->id);

		$schooltype->subs = array();
		foreach($subjects as $subject){
			$param = $courseid;
			$tree = block_exacomp_get_competence_tree($param, $subject->id, null, true, null, true, array(SHOW_ALL_TAXONOMIES), false, false, false, $without_descriptors);
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
	$courses = block_exacomp_get_courseids();

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
									0, $courseid, block_exacomp::ROLE_TEACHER, $grading_scheme);
							mtrace("set competence ".$descriptor->compid." for user ".$student->id.'<br>');
						}
					}
					if(isset($test->topics)){
						foreach($test->topics as $topic){
							block_exacomp_set_user_competence($student->id, $topic->compid,
									1, $courseid, block_exacomp::ROLE_TEACHER, $grading_scheme);
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

		$teacher_competencies = $DB->get_records(block_exacomp::DB_COMPETENCIES, array('userid'=>$student->id, 'role'=>block_exacomp::ROLE_TEACHER, 'value'=>1, 'courseid'=>$course->id));

		foreach($teacher_competencies as $competence){
			$no_data = false;
			if($competence->comptype == TYPE_DESCRIPTOR){
				foreach($descriptors as $descriptor){
					if(block_exacomp_is_descriptor_visible($course->id, $descriptor, $student->id)){
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
		$student_competencies = $DB->get_records(block_exacomp::DB_COMPETENCIES, array('userid'=>$student->id, 'role'=>block_exacomp::ROLE_STUDENT, 'value'=>1, 'courseid'=>$course->id));

		foreach($student_competencies as $competence){
			$no_data = false;
			if($competence->comptype == TYPE_DESCRIPTOR){
				foreach($descriptors as $descriptor){
					if(block_exacomp_is_descriptor_visible($course->id, $descriptor, $student->id)){
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
 *	  - subject 1
 *	  - subject 2
 *  schooltype2
 *	  - subject 3
 */
function block_exacomp_get_schooltypetree_by_topics($subjects, $competencegrid = false){
	$tree = array();
	foreach($subjects as $subject){
		if(!$competencegrid) {
			$schooltype = block_exacomp_get_subject_by_id($subject->subjid);
		}
		else
			$schooltype = block_exacomp_get_schooltype_by_subject($subject);

		if(!array_key_exists($schooltype->id, $tree)){
			$tree[$schooltype->id] = new stdClass();
			$tree[$schooltype->id]->id = $schooltype->id;
			$tree[$schooltype->id]->title = $schooltype->title;
			$tree[$schooltype->id]->source = !empty($schooltype->source) ? $schooltype->source : null;
			$tree[$schooltype->id]->subjects = array();
		}
		$tree[$schooltype->id]->subjects[$subject->id] = $subject;
	}

	return $tree;
}

function block_exacomp_get_cross_subjects_drafts(){
	global $DB;
	return $DB->get_records(block_exacomp::DB_CROSSSUBJECTS, array('courseid'=>0));
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
		$draft = $DB->get_record(block_exacomp::DB_CROSSSUBJECTS, array('id'=>$draftid));
		$draft->courseid = $courseid;
		$draft->creatorid = $USER->id;
		$draft->sourceid = 0;
		$draft->source = block_exacomp::IMPORT_SOURCE_SPECIFIC;
		$crosssubjid = $DB->insert_record(block_exacomp::DB_CROSSSUBJECTS, $draft);

		if($redirect_crosssubjid == 0) $redirect_crosssubjid = $crosssubjid;

		//assign competencies
		$comps = $DB->get_records(block_exacomp::DB_DESCCROSS, array('crosssubjid'=>$draftid));
		foreach($comps as $comp){
			$insert = new stdClass();
			$insert->descrid = $comp->descrid;
			$insert->crosssubjid = $crosssubjid;
			$DB->insert_record(block_exacomp::DB_DESCCROSS, $insert);

			//cross course subjects -> insert in visibility table if not existing
			$visibility = $DB->get_record(block_exacomp::DB_DESCVISIBILITY, array('courseid'=>$courseid, 'descrid'=>$comp->descrid, 'studentid'=>0));
			if(!$visibility){
				$insert = new stdClass();
				$insert->courseid = $courseid;
				$insert->descrid = $comp->descrid;
				$insert->studentid=0;
				$insert->visible = 1;
				$DB->insert_record(block_exacomp::DB_DESCVISIBILITY, $insert);
			}

			//check if descriptor has parent and if parent is visible in course
			$descriptor = $DB->get_record(block_exacomp::DB_DESCRIPTORS, array('id'=>$comp->descrid));
			if($descriptor->parentid != 0){ //has parent
					$parent_visibility = $DB->get_record(block_exacomp::DB_DESCVISIBILITY, array('courseid'=>$courseid, 'descrid'=>$descriptor->parentid, 'studentid'=>0));
					if(!$parent_visibility){ //not visible insert in table
						$insert = new stdClass();
						$insert->courseid = $courseid;
						$insert->descrid = $descriptor->parentid;
						$insert->studentid=0;
						$insert->visible = 1;
						$DB->insert_record(block_exacomp::DB_DESCVISIBILITY, $insert);
					}
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
	$insert->source = block_exacomp::IMPORT_SOURCE_SPECIFIC;
	return $DB->insert_record(block_exacomp::DB_CROSSSUBJECTS, $insert);
}
function block_exacomp_delete_crosssubject_drafts($drafts_to_delete){
	global $DB;
	foreach($drafts_to_delete as $draftid){
		$DB->delete_records(block_exacomp::DB_CROSSSUBJECTS, array('id'=>$draftid));
	}
}

function block_exacomp_get_crosssubjects(){
	global $DB;
	return $DB->get_records(block_exacomp::DB_CROSSSUBJECTS);
}
function block_exacomp_get_cross_subjects_by_course($courseid, $studentid=0){
	global $DB;
	$crosssubs = $DB->get_records(block_exacomp::DB_CROSSSUBJECTS, array('courseid'=>$courseid));
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
	return $DB->get_records(block_exacomp::DB_CROSSSTUD, array('crosssubjid'=>$crosssubjid, 'studentid'=>$studentid));
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
	$comps = $DB->get_records(block_exacomp::DB_DESCCROSS, array('crosssubjid'=>$crosssubjid),'','descrid,crosssubjid');

	if(!$comps) return array();

	$show_childs = array();
	$WHERE = "";
	foreach($comps as $comp){
		$cross_descr = $DB->get_record(block_exacomp::DB_DESCRIPTORS,array('id'=>$comp->descrid));

		$WHERE .= (($cross_descr->parentid == 0)?$cross_descr->id:$cross_descr->parentid).',';

		if($cross_descr->parentid == 0) //parent deskriptor -> show all childs
			$show_childs[$cross_descr->id] = true;
	}
	$WHERE = substr($WHERE, 0, strlen($WHERE)-1);

	if(!$showalldescriptors)
		$showalldescriptors = block_exacomp_get_settings_by_course($courseid)->show_all_descriptors;

	$sql = '(SELECT DISTINCT desctopmm.id as u_id, d.id as id, d.source, d.title, d.niveauid, t.id AS topicid, \'descriptor\' as tabletype, d.profoundness, d.sorting, d.parentid, dvis.visible as visible, n.sorting as niveau '
	.'FROM {'.block_exacomp::DB_TOPICS.'} t '
	.'JOIN {'.block_exacomp::DB_DESCTOPICS.'} desctopmm ON desctopmm.topicid=t.id '
	.'JOIN {'.block_exacomp::DB_DESCRIPTORS.'} d ON desctopmm.descrid=d.id AND d.parentid = 0 '
	.'JOIN {'.block_exacomp::DB_DESCVISIBILITY.'} dvis ON dvis.descrid = d.id AND dvis.studentid=0 AND dvis.courseid=? '
	.'LEFT JOIN {'.block_exacomp::DB_NIVEAUS.'} n ON n.id = d.niveauid '
	.'WHERE d.id IN('.$WHERE.')'.')';

	$descriptors = $DB->get_records_sql($sql, array($courseid, $courseid, $courseid, $courseid));

	foreach($descriptors as &$descriptor) {
		//get examples
		if(array_key_exists($descriptor->id, $comps) || (isset($show_childs[$descriptor->id]) && $show_childs[$descriptor->id]))
			$descriptor = block_exacomp_get_examples_for_descriptor($descriptor,array(SHOW_ALL_TAXONOMIES), true, $courseid);
		else $descriptor->examples = array();

		//check for child-descriptors
		$descriptor->children = block_exacomp_get_child_descriptors($descriptor,$courseid, $showalldescriptors);
		foreach($descriptor->children as $cid => $cvalue) {
			if(!array_key_exists($cid, $comps) && (!isset($show_childs[$descriptor->id])||!($show_childs[$descriptor->id])))
				unset($descriptor->children[$cid]);
		}
		$descriptor->categories = block_exacomp_get_categories_for_descriptor($descriptor);
	}

	return $descriptors;

}
function block_exacomp_get_topics_for_cross_subject_by_descriptors($descriptors){
	global $DB;
	$topics = array();
	foreach($descriptors as $descriptor){
		$topic = $DB->get_record(block_exacomp::DB_TOPICS, array('id'=>$descriptor->topicid));
		if(!array_key_exists($topic->id, $topics))
			$topics[$topic->id] = $topic;
	}

	return $topics;
}
function block_exacomp_cross_subjects_exists(){
	global $DB;
	$dbman = $DB->get_manager();
	$table = new xmldb_table(block_exacomp::DB_CROSSSUBJECTS);
	return $dbman->table_exists($table);
}
function block_exacomp_set_cross_subject_descriptor($crosssubjid,$descrid) {
	global $DB, $COURSE;
	$record = $DB->get_record(block_exacomp::DB_DESCCROSS,array('crosssubjid'=>$crosssubjid,'descrid'=>$descrid));
	if(!$record)
		$DB->insert_record(block_exacomp::DB_DESCCROSS,array('crosssubjid'=>$crosssubjid,'descrid'=>$descrid));

	//insert visibility if cross course
	$cross_subject = $DB->get_record(block_exacomp::DB_CROSSSUBJECTS, array('id'=>$crosssubjid));
	$visibility = $DB->get_record(block_exacomp::DB_DESCVISIBILITY, array('courseid'=>$cross_subject->courseid, 'descrid'=>$descrid, 'studentid'=>0));
	if(!$visibility){
		$insert = new stdClass();
		$insert->courseid = $cross_subject->courseid;
		$insert->descrid = $descrid;
		$insert->studentid = 0;
		$insert->visible = 1;
		$DB->insert_record(block_exacomp::DB_DESCVISIBILITY, $insert);
	}

	$descriptor = $DB->get_record(block_exacomp::DB_DESCRIPTORS, array('id'=>$descrid));

	if($descriptor->parentid == 0){	//insert children into visibility table
		//get topicid
		$descriptor_topic_mm = $DB->get_record(block_exacomp::DB_DESCTOPICS, array('descrid'=>$descriptor->id));
		$descriptor->topicid = $descriptor_topic_mm->topicid;

		$children = block_exacomp_get_child_descriptors($descriptor, $COURSE->id);

		foreach($children as $child){
			$visibility = $DB->get_record(block_exacomp::DB_DESCVISIBILITY, array('courseid'=>$cross_subject->courseid, 'descrid'=>$child->id, 'studentid'=>0));
			if(!$visibility){
				$insert = new stdClass();
				$insert->courseid = $cross_subject->courseid;
				$insert->descrid = $child->id;
				$insert->studentid = 0;
				$insert->visible = 1;
				$DB->insert_record(block_exacomp::DB_DESCVISIBILITY, $insert);

				//insert example visibility if not existent
				$child = block_exacomp_get_examples_for_descriptor($child, array(SHOW_ALL_TAXONOMIES), true, $COURSE->id);
				foreach($child->examples as $example){
					$record = $DB->get_records(block_exacomp::DB_EXAMPVISIBILITY, array('courseid'=>$cross_subject->courseid, 'exampleid'=>$example->id, 'studentid'=>0));
					if(!$record){
						$insert = new stdClass();
						$insert->courseid = $cross_subject->courseid;
						$insert->exampleid = $example->id;
						$insert->studentid = 0;
						$insert->visible = 1;
						$DB->insert_record(block_exacomp::DB_EXAMPVISIBILITY, $insert);
					}
				}
			}
		}
	}
	else{ //insert parent into visibility table
		$visibility = $DB->get_record(block_exacomp::DB_DESCVISIBILITY, array('courseid'=>$cross_subject->courseid, 'descrid'=>$descriptor->parentid, 'studentid'=>0));
		if(!$visibility){
			$insert = new stdClass();
			$insert->courseid = $cross_subject->courseid;
			$insert->descrid = $descriptor->parentid;
			$insert->studentid = 0;
			$insert->visible = 1;
			$DB->insert_record(block_exacomp::DB_DESCVISIBILITY, $insert);
		}
	}

	//example visibility
	$descriptor = block_exacomp_get_examples_for_descriptor($descriptor, array(SHOW_ALL_TAXONOMIES), true, $COURSE->id);

	foreach($descriptor->examples as $example){
		$record = $DB->get_records(block_exacomp::DB_EXAMPVISIBILITY, array('courseid'=>$cross_subject->courseid, 'exampleid'=>$example->id,'studentid'=>0));
		if(!$record){
			$insert = new stdClass();
			$insert->courseid = $cross_subject->courseid;
			$insert->exampleid = $example->id;
			$insert->studentid = 0;
			$insert->visible = 1;

			$DB->insert_record(block_exacomp::DB_EXAMPVISIBILITY, $insert);
		}
	}
}

function block_exacomp_unset_cross_subject_descriptor($crosssubjid, $descrid){
	global $DB, $COURSE;
	$record = $DB->get_record(block_exacomp::DB_DESCCROSS,array('crosssubjid'=>$crosssubjid,'descrid'=>$descrid));
	if($record)
		$DB->delete_records(block_exacomp::DB_DESCCROSS,array('crosssubjid'=>$crosssubjid,'descrid'=>$descrid));

	//delete visibility of non course descriptors, not connected to another course crosssubject
	$cross_subject = $DB->get_record(block_exacomp::DB_CROSSSUBJECTS, array('id'=>$crosssubjid));
	$cross_courseid = $cross_subject->courseid;

	if($cross_courseid != $COURSE->id){	//not current course
		$course_descriptors = block_exacomp_get_descriptors($cross_courseid);

		if(!array_key_exists($descrid, $course_descriptors)){	// no course descriptor -> cross course
			$descriptor_crosssubs_mm = $DB->get_records(block_exacomp::DB_DESCCROSS, array('descrid'=>$descrid));
			$course_cross_subjects = block_exacomp_get_cross_subjects_by_course($cross_courseid);

			$used_in_other_crosssub = false;
			foreach($descriptor_crosssubs_mm as $entry){
				if($entry->crosssubjid != $cross_subject->id){
					if(array_key_exists($entry->crosssubjid, $course_cross_subjects))
						$used_in_other_crosssub = true;
				}
			}

			if(!$used_in_other_crosssub){ // delete visibility if not used in other cross subject in this course
				$DB->delete_records(block_exacomp::DB_DESCVISIBILITY, array('descrid'=>$descrid, 'courseid'=>$cross_courseid, 'studentid'=>0));
			}
		}
	}
}
function block_exacomp_set_cross_subject_student($crosssubjid, $studentid){
	global $DB;
	$record = $DB->get_record(block_exacomp::DB_CROSSSTUD, array('crosssubjid'=>$crosssubjid, 'studentid'=>$studentid));
	if(!$record)
		$DB->insert_record(block_exacomp::DB_CROSSSTUD, array('crosssubjid'=>$crosssubjid, 'studentid'=>$studentid));
}
function block_exacomp_unset_cross_subject_student($crosssubjid, $studentid){
	global $DB;
	$record = $DB->get_record(block_exacomp::DB_CROSSSTUD, array('crosssubjid'=>$crosssubjid, 'studentid'=>$studentid));
	if($record)
		$DB->delete_records(block_exacomp::DB_CROSSSTUD, array('crosssubjid'=>$crosssubjid, 'studentid'=>$studentid));
}
function	 block_exacomp_share_crosssubject($crosssubjid, $value = 0){
	global $DB;

	// TODO: check if my crosssubj?

	$update = $DB->get_record(block_exacomp::DB_CROSSSUBJECTS, array('id'=>$crosssubjid));
	$update->shared = $value;
	return $DB->update_record(block_exacomp::DB_CROSSSUBJECTS, $update);
}
function block_exacomp_get_descr_topic_sorting($topicid, $descid){
	global $DB;
	$record = $DB->get_record(block_exacomp::DB_DESCTOPICS, array('descrid'=>$descid, 'topicid'=>$topicid));
	return ($record) ? $record->sorting : 0;
}
function block_exacomp_set_descriptor_visibility($descrid, $courseid, $visible, $studentid){
	global $DB;
	if($studentid == BLOCK_EXACOMP_SHOW_ALL_STUDENTS || $studentid ==0){//if visibility changed for all: delete individual settings
		$studentid = 0;
		$sql = "DELETE FROM {".block_exacomp::DB_DESCVISIBILITY."} WHERE descrid = ? AND courseid = ? and studentid <> 0";

		$DB->execute($sql, array($descrid, $courseid));
	}
	block_exacomp\db::insert_or_update_record(block_exacomp::DB_DESCVISIBILITY,
		['visible'=>$visible],
		['descrid'=>$descrid, 'courseid'=>$courseid, 'studentid'=>$studentid]
	);
}
function block_exacomp_set_example_visibility($exampleid, $courseid, $visible, $studentid){
	global $DB;

	if($studentid == BLOCK_EXACOMP_SHOW_ALL_STUDENTS || $studentid == 0){//if visibility changed for all: delete individual settings
		$studentid = 0;
		$sql = "DELETE FROM {".block_exacomp::DB_EXAMPVISIBILITY."} WHERE exampleid = ? AND courseid = ? and studentid <> 0";
		$DB->execute($sql, array($exampleid, $courseid));
	}

	block_exacomp\db::insert_or_update_record(block_exacomp::DB_EXAMPVISIBILITY,
		['visible'=>$visible],
		['exampleid'=>$exampleid, 'courseid'=>$courseid, 'studentid'=>$studentid]
	);
}
function block_exacomp_descriptor_used($courseid, $descriptor, $studentid){
	global $DB;
	//if studentid == 0 used = true, if no evaluation (teacher OR student) for this descriptor for any student in this course
	//								 if no evaluation/submission for the examples of this descriptor

	//if studentid != 0 used = true, if any assignment (teacher OR student) for this descriptor for THIS student in this course
	//								 if no evaluation/submission for the examples of this descriptor

	if($studentid == 0){
		$sql = "SELECT * FROM {".block_exacomp::DB_COMPETENCIES."} WHERE courseid = ? AND compid = ? AND comptype=? AND ( value>0 OR additionalinfo IS NOT NULL)";
		$records = $DB->get_records_sql($sql, array($courseid, $descriptor->id, TYPE_DESCRIPTOR));
		if($records) return true;

		if(isset($descriptor->examples) && $descriptor->examples){
			foreach($descriptor->examples as $example){
				$sql = "SELECT * FROM {".block_exacomp::DB_EXAMPLEEVAL."} WHERE courseid = ? AND exampleid = ? AND teacher_evaluation>0";
				$records = $DB->get_records_sql($sql, array($courseid, $example->id));
				if($records) return true;

				$sql = "SELECT * FROM {".block_exacomp::DB_EXAMPLEEVAL."} WHERE courseid = ? AND exampleid = ? AND student_evaluation>0";
				$records = $DB->get_records_sql($sql, array($courseid, $example->id));
				if($records) return true;
			}
		}
		//TODO submission //activities
	}else{
		$sql = "SELECT * FROM {".block_exacomp::DB_COMPETENCIES."} WHERE courseid = ? AND compid = ? AND comptype=? AND userid = ? AND ( value>0 OR additionalinfo IS NOT NULL)";
		$records = $DB->get_records_sql($sql, array($courseid, $descriptor->id, TYPE_DESCRIPTOR, $studentid));
		if($records) return true;

		if(isset($descriptor->examples) && $descriptor->examples){
			foreach($descriptor->examples as $example){
				$sql = "SELECT * FROM {".block_exacomp::DB_EXAMPLEEVAL."} WHERE courseid = ? AND exampleid = ? AND studentid=? AND teacher_evaluation>0";
				$records = $DB->get_records_sql($sql, array($courseid, $example->id, $studentid));
				if($records) return true;

				$sql = "SELECT * FROM {".block_exacomp::DB_EXAMPLEEVAL."} WHERE courseid = ? AND exampleid = ? AND studentid = ? AND student_evaluation>0";
				$records = $DB->get_records_sql($sql, array($courseid, $example->id, $studentid));
				if($records) return true;
			}
		}

		//TODO submissions & avtivities
	}

	return false;
}

function block_exacomp_example_used($courseid, $example, $studentid){
	global $DB;
	//if studentid == 0 used = true, if no evaluation/submission for this example

	//if studentid != 0 used = true, if no evaluation/submission for this examples for this student

	if($studentid == 0){
		$sql = "SELECT * FROM {".block_exacomp::DB_EXAMPLEEVAL."} WHERE courseid = ? AND exampleid = ? AND teacher_evaluation>0";
		$records = $DB->get_records_sql($sql, array($courseid, $example->id));
		if($records) return true;

		$sql = "SELECT * FROM {".block_exacomp::DB_EXAMPLEEVAL."} WHERE courseid = ? AND exampleid = ? AND student_evaluation>0";
		$records = $DB->get_records_sql($sql, array($courseid, $example->id));
		if($records) return true;

		//TODO submission //activities
	}else{
		$sql = "SELECT * FROM {".block_exacomp::DB_EXAMPLEEVAL."} WHERE courseid = ? AND exampleid = ? AND studentid=? AND teacher_evaluation>0";
		$records = $DB->get_records_sql($sql, array($courseid, $example->id, $studentid));
		if($records) return true;

		$sql = "SELECT * FROM {".block_exacomp::DB_EXAMPLEEVAL."} WHERE courseid = ? AND exampleid = ? AND studentid = ? AND student_evaluation>0";
		$records = $DB->get_records_sql($sql, array($courseid, $example->id, $studentid));
		if($records) return true;

		//TODO submissions & avtivities
	}


	$onSchedule = $DB->record_exists(block_exacomp::DB_SCHEDULE, array('exampleid' => $example->id));
	if($onSchedule)
		return true;

	return false;
}
function block_exacomp_get_students_for_crosssubject($courseid, $crosssub){
	global $DB;
	$course_students = block_exacomp_get_students_by_course($courseid);
	if($crosssub->shared)
		return $course_students;

	$students = array();
	$assigned_students = $DB->get_records_menu(block_exacomp::DB_CROSSSTUD,array('crosssubjid'=>$crosssub->id),'','studentid,crosssubjid');
	foreach($course_students as $student){
		if(isset($assigned_students[$student->id]))
			$students[$student->id] = $student;
	}
	return $students;
}
function block_exacomp_get_viewurl_for_example($studentid,$exampleid) {
	global $CFG, $DB;

	if (!block_exacomp_exaportexists()) {
		return null;
	}

	$sql = 'select *, max(timecreated) from {block_exacompitemexample} ie
			JOIN {block_exaportitem} i ON i.id = ie.itemid
			WHERE ie.exampleid = ? AND i.userid=?';

	$item = $DB->get_record_sql($sql, array($exampleid,$studentid));
	if(!$item)
		return null;

	$view = $DB->get_record('block_exaportviewblock', array("type"=>"item","itemid"=>$item->itemid));
	if(!$view)
		return null;

	$access = "view/id/".$studentid."-".$view->viewid."&itemid=".$item->itemid;

	return $CFG->wwwroot.'/blocks/exaport/shared_item.php?access='.$access;
}
function block_exacomp_add_example_to_schedule($studentid,$exampleid,$creatorid,$courseid) {
	global $USER, $DB;

	$timecreated = $timemodified = time();

	$DB->insert_record(block_exacomp::DB_SCHEDULE, array('studentid' => $studentid, 'exampleid' => $exampleid, 'courseid' => $courseid,'creatorid' => $creatorid, 'timecreated' => $timecreated, 'timemodified' => $timemodified));

	//only send a notification if a teacher adds an example for a student and not for pre planning storage
	if($creatorid != $studentid && $studentid >0)
		block_exacomp_send_weekly_schedule_notification($USER,$DB->get_record('user', array('id' => $studentid)), $courseid, $exampleid);

	\block_exacomp\event\example_added::log(['objectid' => $exampleid, 'courseid' => $courseid, 'relateduserid' => $studentid]);

	return true;
}

function block_exacomp_add_days($date, $days) {
	return mktime(0,0,0,date('m', $date), date('d', $date)+$days, date('Y', $date));
}

function block_exacomp_build_example_association_tree($courseid, $example_descriptors = array(), $exampleid=0, $descriptorid = 0, $showallexamples=false){
	//get all subjects, topics, descriptors and examples
	$tree = block_exacomp_get_competence_tree($courseid, null, null, false, SHOW_ALL_NIVEAUS, true, block_exacomp_get_settings_by_course($courseid)->filteredtaxonomies, false, false, true);

	// unset all descriptors, topics and subjects that do not contain the example descriptors
	foreach($tree as $skey => $subject) {
		$subject->associated = 0;
		foreach ( $subject->subs as $tkey => $topic ) {
			$topic->associated = 0;
			if(isset($topic->descriptors)) {
				foreach ( $topic->descriptors as $dkey => $descriptor ) {

					$descriptor = block_exacomp_check_child_descriptors($descriptor, $example_descriptors, $exampleid, $descriptorid, $showallexamples);

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
function block_exacomp_check_child_descriptors($descriptor, $example_descriptors, $exampleid, $descriptorid = 0, $showallexamples=false) {

	$descriptor->associated = 0;
	$descriptor->direct_associated = 0;

	if (array_key_exists ( $descriptor->id, $example_descriptors ) || $descriptorid == $descriptor->id || ($showallexamples && !empty($descriptor->examples))){
			$descriptor->associated = 1;
			$descriptor->direct_associated = 1;
	}

	//check descriptor examples
	foreach($descriptor->examples as $ekey => $example) {
		$descriptor->examples[$ekey]->associated = 1;
		if($example->id != $exampleid && !$showallexamples)
			$descriptor->examples[$ekey]->associated = 0;
	}

	//check children and their examples
	foreach($descriptor->children as $ckey => $cvalue) {
		$keepDescriptor_child = false;
		if (array_key_exists ( $cvalue->id, $example_descriptors ) || $descriptorid == $ckey || ($showallexamples && !empty($cvalue->examples))) {
			$keepDescriptor_child = true;
			$descriptor->associated = 1;
		}
		$descriptor->children[$ckey]->associated = 1;
		$descriptor->children[$ckey]->direct_associated = 1;
		if (! $keepDescriptor_child) {
			$descriptor->children[$ckey]->associated = 0;
			$descriptor->children[$ckey]->direct_associated = 0;
			continue;
		}
		foreach($cvalue->examples as $ekey => $example) {
			$cvalue->examples[$ekey]->associated = 1;
			if($example->id != $exampleid && !$showallexamples)
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

function block_exacomp_is_descriptor_visible($courseid, $descriptor, $studentid) {
	global $DB;

	// $studentid could be BLOCK_EXACOMP_SHOW_ALL_STUDENTS
	if ($studentid <= 0) {
		$studentid = 0;
	}

	// if used, hiding is impossible
	$descriptor_used = block_exacomp_descriptor_used($courseid, $descriptor, $studentid);
	if($descriptor_used)
		return true;

	// always use global value first (if set)
	if (isset($descriptor->visible) && !$descriptor->visible) {
		return false;
	}

	// check if it is hidden for whole course?
	$visible = $DB->get_field(block_exacomp::DB_DESCVISIBILITY, 'visible',
		['courseid'=>$courseid, 'descrid'=>$descriptor->id, 'studentid'=>0]);
	// $DB->get_field() returns false if not found
	if ($visible !== false && !$visible) {
		return false;
	}

	// then try for a student
	if ($studentid > 0) {
		$visible = $DB->get_field(block_exacomp::DB_DESCVISIBILITY, 'visible',
			['courseid'=>$courseid, 'descrid'=>$descriptor->id, 'studentid'=>$studentid]);
		// $DB->get_field() returns false if not found
		if ($visible !== false) {
			return $visible;
		}
	}

	// default is visible
	return true;
}
function block_exacomp_is_example_visible($courseid, $example, $studentid){
	global $DB;

	// $studentid could be BLOCK_EXACOMP_SHOW_ALL_STUDENTS
	if ($studentid <= 0) {
		$studentid = 0;
	}

	// if used, hiding is impossible
	$example_used = block_exacomp_example_used($courseid, $example, $studentid);
	if ($example_used) {
		return true;
	}

	// always use global value first (if set)
	if (isset($example->visible) && !$example->visible) {
		return false;
	}

	// check if it is hidden for whole course?
	$visible = $DB->get_field(block_exacomp::DB_EXAMPVISIBILITY, 'visible',
		['courseid'=>$courseid, 'exampleid'=>$example->id, 'studentid'=>0]);
	// $DB->get_field() returns false if not found
	if ($visible !== false && !$visible) {
		return false;
	}

	// then try for a student
	if ($studentid > 0) {
		$visible = $DB->get_field(block_exacomp::DB_EXAMPVISIBILITY, 'visible',
			['courseid'=>$courseid, 'exampleid'=>$example->id, 'studentid'=>$studentid]);
		// $DB->get_field() returns false if not found
		if ($visible !== false) {
			return $visible;
		}
	}

	// default is visible
	return true;
}

function block_exacomp_get_descriptor_visible_css($visible, $role) {
	$visible_css = '';
	if(!$visible)
		($role == block_exacomp::ROLE_TEACHER) ? $visible_css = ' hidden_temp' : $visible_css = ' hidden';

	return $visible_css;
}
function block_exacomp_get_example_visible_css($visible, $role) {
	$visible_css = '';
	if(!$visible)
		($role == block_exacomp::ROLE_TEACHER) ? $visible_css = ' hidden_temp' : $visible_css = ' hidden';

	return $visible_css;
}
// TODO: was macht die funktion?
function block_exacomp_init_cross_subjects(){
	global $DB;
	$emptydrafts = $DB->get_records(block_exacomp::DB_CROSSSUBJECTS, array('sourceid'=>0, 'source'=>1, 'creatorid'=>0, 'courseid'=>0));

	foreach($emptydrafts as $emptydraft){
		if(strcmp($emptydraft->title, 'Leere Vorlage')==0 || strcmp($emptydraft->title, 'new crosssubject')==0)
			$DB->delete_records(block_exacomp::DB_CROSSSUBJECTS, array('id'=>$emptydraft->id));
	}
}
/**
 *
 * Calculate number of students which have achieved a certain evaluation (depending on scheme)
 * and number of students working on this descriptor (working on one the examples)
 * Same for overview and crosssubject (only printed descriptors considered)
 *
 * @param unknown_type $courseid
 * @param unknown_type $students
 * @param unknown_type $descriptor
 * @param unknown_type $scheme
 */
function block_exacomp_calculate_statistic_for_descriptor($courseid, $students, $descriptor, $scheme){
	global $DB;
	$student_oB = 0; $student_iA = 0;
	$student_oB_title = ""; $student_iA_title = "";
	$self = array_fill(1, $scheme, 0);
	$self_title = array_fill(1, $scheme, "");
	$teacher = array_fill(0, $scheme+1, 0);
	$teacher_title = array_fill(0, $scheme+1, "");
	$teacher_oB = 0; $teacher_iA = 0;
	$teacher_oB_title = ""; $teacher_iA_title = "";

	foreach($students as $student){
		if(isset($student->competencies->student[$descriptor->id])){
			$counter = 1;
			while($counter < $scheme){
				if($student->competencies->student[$descriptor->id]==$counter){
					$self[$counter]++;
					$self_title[$counter] .= $student->firstname." ".$student->lastname."\n";
				}
				$counter++;
			}
		}else{
			$student_oB++;
			$student_oB_title .= $student->firstname." ".$student->lastname."\n";
		}
		if(isset($student->competencies->teacher[$descriptor->id])){
			$counter = 0;
			while($counter <= $scheme){
				if($student->competencies->teacher[$descriptor->id]==$counter){
					$teacher[$counter]++;
					$teacher_title[$counter] .= $student->firstname." ".$student->lastname."\n";
				}
				$counter++;
			}
		}else{
			$teacher_oB++;
			$teacher_oB_title .= $student->firstname." ".$student->lastname."\n";
		}

		$example_inwork = false;
		foreach($descriptor->examples as $example){
			if($DB->record_exists('block_exacompschedule', array('studentid' => $student->id, 'exampleid' => $example->id, 'courseid' => $courseid))) {
				$example_inwork = true;
			}
		}

		foreach($descriptor->children as $children){
			foreach($children->examples as $example){
				if($DB->record_exists('block_exacompschedule', array('studentid' => $student->id, 'exampleid' => $example->id, 'courseid' => $courseid))) {
					$example_inwork = true;
				}
			}
		}
		if($example_inwork){
			$student_iA++;
			$student_iA_title .= $student->firstname." ".$student->lastname."\n";
			$teacher_iA++;
			$teacher_iA_title .= $student->firstname." ".$student->lastname."\n";
		}
	}

	return array($self, $student_oB, $student_iA, $teacher, $teacher_oB, $teacher_iA,
		$self_title, $student_oB_title, $student_iA_title, $teacher_title, $teacher_oB_title, $teacher_iA_title);
}

function block_exacomp_calculate_statistic_for_example($courseid, $students, $example, $scheme){
	global $DB;
	global $DB;
	$student_oB = 0; $student_iA = 0;
	$student_oB_title = ""; $student_iA_title = "";
	$self = array_fill(1, $scheme, 0);
	$self_title = array_fill(1, $scheme, "");
	$teacher = array_fill(0, $scheme+1, 0);
	$teacher_title = array_fill(0, $scheme+1, "");
	$teacher_oB = 0; $teacher_iA = 0;
	$teacher_oB_title = ""; $teacher_iA_title = "";

	foreach($students as $student){
		if(isset($student->examples->student[$example->id])){
			$counter = 1;
			while($counter < $scheme){
				if($student->examples->student[$example->id]==$counter){
					$self[$counter]++;
					$self_title[$counter] .= $student->firstname." ".$student->lastname."\n";
				}
				$counter++;
			}
		}else{
			$student_oB++;
			$student_oB_title .= $student->firstname." ".$student->lastname."\n";
		}
		if(isset($student->examples->teacher[$example->id])){
			$counter = 0;
			while($counter <= $scheme){
				if($student->examples->teacher[$example->id]==$counter){
					$teacher[$counter]++;
					$teacher_title[$counter] .= $student->firstname." ".$student->lastname."\n";
				}
				$counter++;
			}
		}else{
			$teacher_oB++;
			$teacher_oB_title .= $student->firstname." ".$student->lastname."\n";

		}

		//TODO in arbeit
		if($DB->record_exists('block_exacompschedule', array('studentid' => $student->id, 'exampleid' => $example->id, 'courseid' => $courseid))) {
			$student_iA++;
			$student_iA_title .= $student->firstname." ".$student->lastname."\n";
			$teacher_iA++;
			$teacher_iA_title .= $student->firstname." ".$student->lastname."\n";
		}

	}

	return array($self, $student_oB, $student_iA, $teacher, $teacher_oB, $teacher_iA,
		$self_title, $student_oB_title, $student_iA_title, $teacher_title, $teacher_oB_title, $teacher_iA_title);
}

function block_exacomp_get_descriptor_numbering($descriptor){
	global $DB;

	if(block_exacomp_is_altversion()){
		$topicid = $descriptor->topicid;

		$numbering = block_exacomp_get_topic_numbering($topicid);

		if($descriptor->parentid == 0){
			$niveau = $DB->get_record(block_exacomp::DB_NIVEAUS, array('id'=>$descriptor->niveauid));
			if ($niveau)
				$numbering .= $niveau->numb;
		}
		if($descriptor->parentid != 0){
			$parent_descriptor = $DB->get_record(block_exacomp::DB_DESCRIPTORS, array('id'=>$descriptor->parentid));
			$niveau = $DB->get_record(block_exacomp::DB_NIVEAUS, array('id'=>$parent_descriptor->niveauid));
			if ($niveau)
				$numbering .= $niveau->numb.'.';
			$numbering .= $descriptor->sorting;
		}

		return $numbering;
	}
	return "";
}
/**
 *
 * @param $topic id or object
 * @return string
 */
function block_exacomp_get_topic_numbering($topic){
	if (is_object($topic)) {
		// ok
	} else {
	   $topic = block_exacomp_get_topic_by_id($topic);
	}
	if(block_exacomp_is_altversion()){
		$numbering = block_exacomp_get_subject_by_id($topic->subjid)->titleshort.'.';

		//topic
		$numbering .= $topic->numb.'.';

		return $numbering;
	}
	return "";
}
function block_exacomp_get_cross_subjects_drafts_sorted_by_subjects(){
	global $DB;
	$subjects = block_exacomp_get_subjects();

	$default_subject = new stdClass();
	$default_subject->id = 0;
	$default_subject->title = get_string('nocrosssubsub', 'block_exacomp');

	$subjects[0] = $default_subject;

	foreach($subjects as $subject){
		$drafts = $DB->get_records(block_exacomp::DB_CROSSSUBJECTS, array('subjectid'=>$subject->id, 'courseid'=>0));
		if($drafts)
			$subject->crosssub_drafts = $drafts;
	}

	return $subjects;
}

function block_exacomp_get_cross_subjects_sorted_by_subjects(){
	global $DB;

	$subjects = block_exacomp_get_subjects();

	$default_subject = new stdClass();
	$default_subject->id = 0;
	$default_subject->title = get_string('nocrosssubsub', 'block_exacomp');

	$subjects[0] = $default_subject;

	foreach($subjects as $subject){
		$all_crosssubjects = $DB->get_records(block_exacomp::DB_CROSSSUBJECTS, array('subjectid'=>$subject->id));
		$without_draft = array();
		if($all_crosssubjects){
			foreach($all_crosssubjects as $crosssubject)
				if($crosssubject->courseid > 0)
					$without_draft[$crosssubject->id] = $crosssubject;

			$subject->crosssubjects = $without_draft;
		}
	}

	return $subjects;
}

function block_exacomp_get_cross_subjects_for_descriptor($courseid, $descriptorid) {
	global $DB;

	$sql = "SELECT cs.id, cs.title FROM {block_exacompcrosssubjects} cs
			JOIN {block_exacompdescrcross_mm} dc ON dc.crosssubjid = cs.id
			WHERE dc.descrid = ? AND cs.courseid = ?";

	$crosssubjects = $DB->get_records_sql($sql,array("descrid" => $descriptorid, "courseid" => $courseid));

	$children = $DB->get_records(block_exacomp::DB_DESCRIPTORS,array("parentid" => $descriptorid));

	foreach($children as $child) {
		$child_crosssubjects = block_exacomp_get_cross_subjects_for_descriptor($courseid, $child->id);
		$crosssubjects += $child_crosssubjects;
	}

	return $crosssubjects;
}

function block_exacomp_get_cross_subject_descriptors($crosssubjid) {
	global $DB;
	$sql = "SELECT d.* from {".block_exacomp::DB_DESCRIPTORS."} d
			JOIN {".block_exacomp::DB_DESCCROSS."} dc ON dc.descrid = d.id
			WHERE dc.crosssubjid = ?";
	$descriptors = $DB->get_records_sql($sql, array("crosssubjid" => $crosssubjid));

	/*
	foreach($descriptors as $descriptor) {
		if($descriptor->parentid) {
			$parent = $DB->get_record(block_exacomp::DB_DESCRIPTORS, array('id' => $descriptor->parentid));
			$descriptors[$parent->id] = $parent;
		}
	}
	*/

	return $descriptors;
}
function block_exacomp_get_descriptor_statistic_for_crosssubject($courseid, $crosssubjid, $studentid) {
	global $DB;

	if($studentid == BLOCK_EXACOMP_SHOW_STATISTIC)
		$studentid = 0;

	// get total amount of descriptors for the given crosssubject

	$descriptors = block_exacomp_get_cross_subject_descriptors($crosssubjid);

	$descriptor_where_string = "";

	$inWork = 0;

	foreach($descriptors as $descriptor) {
		$descriptor->visible = $DB->get_field(block_exacomp::DB_DESCVISIBILITY, 'visible', array('courseid'=>$courseid, 'descrid'=>$descriptor->id, 'studentid'=>0));
		$visible = block_exacomp_is_descriptor_visible($courseid, $descriptor, $studentid);
		if(!$visible) {
			unset($descriptor);
			continue;
		}
		$descriptor_where_string .= $descriptor->id . ",";

		$sql = "SELECT count(e.id) FROM {".block_exacomp::DB_EXAMPLES."} e
				JOIN {".block_exacomp::DB_DESCEXAMP."} de ON de.exampid = e.id
				JOIN {".block_exacomp::DB_DESCRIPTORS."} d ON de.descrid = d.id
				JOIN {block_exacompschedule} s ON s.exampleid = e.id
				WHERE s.courseid = ? AND d.id = ?";
		if($studentid != 0) {
			$sql .= " AND s.studentid = ?";
			$conditions = array($courseid, $descriptor->id,$studentid);
		} else
			$conditions = array($courseid, $descriptor->id);

		// count the descriptors that are "in work", therefore one or more of their examples are on the weekly schedule
		$inWork += $DB->count_records_sql($sql,$conditions);
	}
	$descriptor_where_string = rtrim($descriptor_where_string, ",");
	$total = count($descriptors);

	$notEvaluated = $total;
	// if summary, multiply total amount with the number of students within the course
	if($studentid == 0)
		$notEvaluated *= count(block_exacomp_get_students_by_course($courseid));

	// iterative over grading scheme and get the amount for each grade
	$scheme = block_exacomp_get_grading_scheme($courseid);
	$gradings = array();
	for($i=0;$i<=$scheme;$i++) {
		$conditions = array();
		$conditions[] = $courseid;
		$conditions[] = TYPE_DESCRIPTOR;
		$conditions[] = block_exacomp::ROLE_TEACHER;
		$conditions[] = $i;

		if($studentid != 0)
			$conditions[] = $studentid;

		$sql = "SELECT count(c.id) as count FROM {".block_exacomp::DB_COMPETENCIES."} c
				WHERE courseid = ?
				AND comptype = ?
				AND role = ?
				AND value = ?
				AND compid IN (".$descriptor_where_string.")";
		if($studentid != 0)
			$sql .= ' AND userid = ?';

		$gradings[$i] = $DB->count_records_sql($sql, $conditions);

		$notEvaluated -= $gradings[$i];
	}


	//check for the crosssubj grade
	if($studentid != 0)
		$totalGrade = $DB->get_field(block_exacomp::DB_COMPETENCIES,'value',array('userid' => $studentid, 'comptype' => TYPE_CROSSSUB, 'courseid' => $courseid, 'compid' => $crosssubjid, 'role' => block_exacomp::ROLE_TEACHER));
	else
		$totalGrade = 0;

	return array($total, $gradings, $notEvaluated, $inWork,$totalGrade);
}
define('BLOCK_EXACOMP_DESCRIPTOR_STATISTIC', 0);
define('BLOCK_EXACOMP_EXAMPLE_STATISTIC', 1);

function block_exacomp_get_descriptor_statistic($courseid, $descrid, $studentid) {
	global $DB;

	if($studentid == BLOCK_EXACOMP_SHOW_STATISTIC)
		$studentid = 0;

	// get total amount of descriptors for the given crosssubject
	$descriptor = $DB->get_record(block_exacomp::DB_DESCRIPTORS,array("id" => $descrid));
	$children = $DB->get_records(block_exacomp::DB_DESCRIPTORS,array("parentid" => $descrid));

	$descriptor_where_string = "";

	$inWork = 0;

	foreach($children as $child) {
		$child->visible = $DB->get_field(block_exacomp::DB_DESCVISIBILITY, 'visible', array('courseid'=>$courseid, 'descrid'=>$child->id, 'studentid'=>0));
		$visible = block_exacomp_is_descriptor_visible($courseid, $child, $studentid);
		if(!$visible) {
			unset($child);
			continue;
		}
		$descriptor_where_string .= $child->id . ",";

		$sql = "SELECT count(e.id) FROM {".block_exacomp::DB_EXAMPLES."} e
				JOIN {".block_exacomp::DB_DESCEXAMP."} de ON de.exampid = e.id
				JOIN {".block_exacomp::DB_DESCRIPTORS."} d ON de.descrid = d.id
				JOIN {block_exacompschedule} s ON s.exampleid = e.id
				WHERE s.courseid = ? AND d.id = ?";
		if($studentid != 0) {
			$sql .= " AND s.studentid = ?";
			$conditions = array($courseid, $child->id,$studentid);
		} else
			$conditions = array($courseid, $child->id);

		// count the descriptors that are "in work", therefore one or more of their examples are on the weekly schedule
		$inWork += $DB->count_records_sql($sql,$conditions);
	}
	$descriptor_where_string = rtrim($descriptor_where_string, ",");
	$total = count($children);

	$notEvaluated = $total;
	// if summary, multiply total amount with the number of students within the course
	if($studentid == 0)
		$notEvaluated *= count(block_exacomp_get_students_by_course($courseid));

	// iterative over grading scheme and get the amount for each grade
	$scheme = block_exacomp_get_grading_scheme($courseid);
	$gradings = array();
	for($i=0;$i<=$scheme;$i++) {
		$conditions = array();
		$conditions[] = $courseid;
		$conditions[] = TYPE_DESCRIPTOR;
		$conditions[] = block_exacomp::ROLE_TEACHER;
		$conditions[] = $i;

		if($studentid != 0)
			$conditions[] = $studentid;

		$gradings[$i] = 0;
		if(!empty($descriptor_where_string)){
			$sql = "SELECT count(c.id) as count FROM {".block_exacomp::DB_COMPETENCIES."} c
					WHERE courseid = ?
					AND comptype = ?
					AND role = ?
					AND value = ?
					AND compid IN (".$descriptor_where_string.")";
			if($studentid != 0)
				$sql .= ' AND userid = ?';

			$gradings[$i] = $DB->count_records_sql($sql, $conditions);
		}
		$notEvaluated -= $gradings[$i];
	}

	$totalGrade = null;
	//check for the crosssubj grade
	if($studentid != 0)
		$totalGrade = $DB->get_field(block_exacomp::DB_COMPETENCIES,'value',array('userid' => $studentid, 'comptype' => TYPE_DESCRIPTOR, 'courseid' => $courseid, 'compid' => $descrid, 'role' => block_exacomp::ROLE_TEACHER));

	if($totalGrade == null)
		$totalGrade = 0;

	return array($total, $gradings, $notEvaluated, $inWork,$totalGrade);
}
function block_exacomp_delete_custom_descriptor($descriptorid){
	global $DB;

	//delete descriptor evaluation
	$DB->delete_records(block_exacomp::DB_COMPETENCIES, array('compid'=>$descriptorid, 'comptype'=>TYPE_DESCRIPTOR));

	//delete crosssubject association
	$DB->delete_records(block_exacomp::DB_DESCCROSS, array('descrid'=>$descriptorid));

	//delete descriptor
	$DB->delete_records(block_exacomp::DB_DESCRIPTORS, array('id'=>$descriptorid));

}
function block_exacomp_get_cross_subject_examples($crosssubjid) {
	global $DB;
	$sql = "SELECT e.* from {".block_exacomp::DB_EXAMPLES."} e
			JOIN {".block_exacomp::DB_DESCEXAMP."} de ON de.exampid = e.id
			JOIN {".block_exacomp::DB_DESCRIPTORS."} d ON de.descrid = d.id
			JOIN {".block_exacomp::DB_DESCCROSS."} dc ON dc.descrid = d.id
			WHERE dc.crosssubjid = ?";
	return $DB->get_records_sql($sql, array("crosssubjid" => $crosssubjid));
}
function block_exacomp_get_example_statistic_for_crosssubject($courseid, $crosssubjid, $studentid) {
	global $DB;

	if($studentid == BLOCK_EXACOMP_SHOW_STATISTIC)
		$studentid = 0;

	// get total amount of descriptors for the given crosssubject

	$examples = block_exacomp_get_cross_subject_examples($crosssubjid);

	$example_where_string = "";

	$inWork = 0;

	foreach($examples as $example) {

		// TODO check visibility

		$example_where_string .= $example->id . ",";

		$sql = "SELECT count(e.id) FROM {".block_exacomp::DB_EXAMPLES."} e
				JOIN {block_exacompschedule} s ON s.exampleid = e.id
				WHERE s.courseid = ? AND e.id = ?";

		if($studentid != 0) {
			$sql .= " AND s.studentid = ?";
			$conditions = array($courseid, $example->id,$studentid);
		} else
			$conditions = array($courseid, $example->id);

		// count the descriptors that are "in work", therefore one or more of their examples are on the weekly schedule
		$inWork += $DB->count_records_sql($sql,$conditions);
	}
	$example_where_string = rtrim($example_where_string, ",");
	$total = count($examples);

	$notEvaluated = $total;
	// if summary, multiply total amount with the number of students within the course
	if($studentid == 0)
		$notEvaluated *= count(block_exacomp_get_students_by_course($courseid));

	// iterative over grading scheme and get the amount for each grade
	$scheme = block_exacomp_get_grading_scheme($courseid);
	$gradings = array();
	for($i=0;$i<=$scheme;$i++) {
		$conditions = array();
		$conditions[] = $courseid;
		$conditions[] = $i;

		if($studentid != 0)
			$conditions[] = $studentid;

		if($total > 0) {
			$sql = "SELECT count(e.id) as count FROM {".block_exacomp::DB_EXAMPLEEVAL."} e
					WHERE courseid = ?
					AND teacher_evaluation = ?
					AND exampleid IN (".$example_where_string.")";
			if($studentid != 0)
				$sql .= ' AND studentid = ?';

			$gradings[$i] = $DB->count_records_sql($sql, $conditions);
		}
		else
			$gradings[$i] = 0;

		$notEvaluated -= $gradings[$i];
	}


	//check for the crosssubj grade
	if($studentid != 0)
		$totalGrade = $DB->get_field(block_exacomp::DB_COMPETENCIES,'value',array('userid' => $studentid, 'comptype' => TYPE_CROSSSUB, 'courseid' => $courseid, 'compid' => $crosssubjid, 'role' => block_exacomp::ROLE_TEACHER));
	else
		$totalGrade = 0;

	return array($total, $gradings, $notEvaluated, $inWork,$totalGrade);
}
function block_exacomp_get_example_statistic_for_descriptor($courseid, $descrid, $studentid, $crosssubjid = 0) {
	global $DB;

	if($studentid == BLOCK_EXACOMP_SHOW_STATISTIC)
		$studentid = 0;

	// get total amount of descriptors for the given crosssubject
	$descriptor = $DB->get_record(block_exacomp::DB_DESCRIPTORS,array("id" => $descrid));
	$children = $DB->get_records(block_exacomp::DB_DESCRIPTORS,array("parentid" => $descrid));

	$children[] = $descriptor;

	$crosssubjdescriptos = array();
	if($crosssubjid > 0)
		$crosssubjdescriptos = block_exacomp_get_descriptors_for_cross_subject($courseid, $crosssubjid);

	if($studentid == 0)
		$students =  block_exacomp_get_students_by_course($courseid);
	else
		$students = array($DB->get_record('user', array('id'=>$studentid)));

	foreach($children as $child){
		if($crosssubjid == 0 || array_key_exists($child->id, $crosssubjdescriptos)){
			$child->examples = $DB->get_records(block_exacomp::DB_DESCEXAMP,array('descrid' => $child->id));
			$child->visible =  $DB->get_field(block_exacomp::DB_DESCVISIBILITY, 'visible', array('courseid'=>$courseid, 'descrid'=>$child->id, 'studentid'=>0));
			foreach($child->examples as $example)
				$example->visible = $DB->get_field(block_exacomp::DB_EXAMPVISIBILITY, 'visible', array('courseid'=>$courseid, 'exampleid'=>$example->exampid, 'studentid'=>0));
		}
	}

	$total = 0;
	$totalHidden = 0;
	$inWork = 0;
	$notInWork = 0;
	$scheme = block_exacomp_get_grading_scheme($courseid);

	$gradings = array();
	for($i=0; $i<=$scheme; $i++)
		$gradings[$i] = 0;

	foreach($students as $student){
		$totalHiddenArray = array();
		$totalArray = array();
		$inWorkArray = array();
		$example_where_string = "";
		foreach($children as $child){
			$visible = block_exacomp_is_descriptor_visible($courseid, $child, $student->id);
			if(!$visible) {
				continue;
			}

			foreach($child->examples as $example){
				$visible_example = block_exacomp_is_example_visible($courseid, $example, $student->id);
				if($visible_example && !array_key_exists( $example->exampid, $totalArray)){
					$totalArray[$example->exampid] = $example;
					$example->hidden = false;
				}else{
					if (!array_key_exists( $example->exampid, $totalArray) && !array_key_exists( $example->exampid, $totalHiddenArray))
						$totalHiddenArray[$example->exampid] = $example;
					$example->hidden = true;
				}
			}

			$sql = "SELECT s.id, e.id as exampid FROM {".block_exacomp::DB_EXAMPLES."} e
					JOIN {".block_exacomp::DB_DESCEXAMP."} de ON de.exampid = e.id
					JOIN {".block_exacomp::DB_DESCRIPTORS."} d ON de.descrid = d.id
					JOIN {block_exacompschedule} s ON s.exampleid = e.id
					WHERE s.courseid = ?
					AND d.id = ? AND s.studentid = ? ";

			$schedule_examples = $DB->get_records_sql($sql, array($courseid, $child->id,$student->id));

			foreach($schedule_examples as $sched){
				$example = $totalArray[$sched->exampid];
				if(!$example->hidden && !array_key_exists($example->exampid, $inWorkArray))
					$inWorkArray[$example->exampid] = $example;
			}

		}

		foreach($totalArray as $example)
				$example_where_string .= $example->exampid.",";

		$example_where_string = substr($example_where_string, 0, strlen($example_where_string)-1);
		$total += count ($totalArray);
		$totalHidden = count($totalHiddenArray) + $total;
		$inWork += count ($inWorkArray);

		$notInWork = $total - $inWork;
		$notEvaluated = $total;

		if(!empty($totalArray)){
			$sql = "SELECT * FROM {".block_exacomp::DB_EXAMPLEEVAL."}
				WHERE courseid = ? AND studentid = ? AND exampleid IN (".$example_where_string.")";
			$examp_evals = $DB->get_records_sql($sql, array($courseid, $student->id));

			foreach($examp_evals as $examp_eval){
				if(isset($examp_eval->teacher_evaluation))
					$gradings[$examp_eval->teacher_evaluation]++;
				else
					unset($examp_eval);
			}
			$notEvaluated = $total - count($examp_evals);
		}

	}

	$totalGrade = null;

	if($studentid != 0)
		$totalGrade = $DB->get_field(block_exacomp::DB_COMPETENCIES,'value',array('userid' => $studentid, 'comptype' => TYPE_DESCRIPTOR, 'courseid' => $courseid, 'compid' => $descrid, 'role' => block_exacomp::ROLE_TEACHER));

	if($totalGrade == null)
		$totalGrade = 0;

	return array($total, $gradings, $notEvaluated, $inWork,$totalGrade, $notInWork, $totalHidden);
}


/**
 * @return stored_file
 * @param array|object $item database item
 * @param string $type
 */
function block_exacomp_get_file($item, $type) {
	// this function reads the associated file from the moodle file storage

	$fs = get_file_storage();
	$files = $fs->get_area_files(context_system::instance()->id, 'block_exacomp', $type, $item->id, null, false);

	// return first file
	return reset($files);
}

/**
 * @return moodle_url
 * @param array|object $item database item
 * @param string $type
 */
function block_exacomp_get_file_url($item, $type) {
	global $COURSE;

	// TODO: hacked here, delete fields and delete this code!
	/*
	if (($type == 'example_task') && $item->task) {
		return $item->task;
	}
	if (($type == 'example_solution') && $item->solution) {
		return $item->solution;
	}
	*/

	// get from filestorage
	$file = block_exacomp_get_file($item, $type);

	if (!$file) return null;

	return moodle_url::make_pluginfile_url(context_course::instance($COURSE->id)->id, $file->get_component(), $file->get_filearea(),
		$file->get_itemid(), $file->get_filepath(), $file->get_filename());
}

function block_exacomp_get_examples_for_pool($studentid, $courseid){
	global $DB;

	 if (date('w', time()) == 1)
		 $beginning_of_week = strtotime('Today',time());
	 else
		 $beginning_of_week = strtotime('last Monday',time());

	$sql = "select s.*,
				e.title, e.id as exampleid, e.source AS example_source, evis.visible,
				eval.student_evaluation, eval.teacher_evaluation, evis.courseid, s.id as scheduleid,
				e.externalurl, e.externaltask, e.description, eval.additionalinfo
			FROM {block_exacompschedule} s
			JOIN {block_exacompexamples} e ON e.id = s.exampleid
			JOIN {".block_exacomp::DB_EXAMPVISIBILITY."} evis ON evis.exampleid= e.id AND evis.studentid=0 AND evis.visible = 1 AND evis.courseid=?
			LEFT JOIN {block_exacompexameval} eval ON eval.exampleid = s.exampleid AND eval.studentid = s.studentid
			WHERE s.studentid = ? AND s.deleted = 0 AND (
				-- noch nicht auf einen tag geleg
				(s.start IS null OR s.start=0)
				-- oder auf einen tag der vorwoche gelegt und noch nicht evaluiert
				OR (s.start < ? AND (eval.teacher_evaluation IS NULL OR eval.teacher_evaluation=0))
			)
			ORDER BY s.id";

	return $DB->get_records_sql($sql,array($courseid, $studentid, $beginning_of_week));
}

function block_exacomp_get_examples_for_trash($studentid, $courseid){
	global $DB;

	$sql = "select s.*,
				e.title, e.id as exampleid, e.source AS example_source, evis.visible,
				eval.student_evaluation, eval.teacher_evaluation, evis.courseid, s.id as scheduleid
			FROM {block_exacompschedule} s
			JOIN {block_exacompexamples} e ON e.id = s.exampleid
			JOIN {".block_exacomp::DB_EXAMPVISIBILITY."} evis ON evis.exampleid= e.id AND evis.studentid=0 AND evis.visible = 1 AND evis.courseid=?
			LEFT JOIN {block_exacompexameval} eval ON eval.exampleid = s.exampleid AND eval.studentid = s.studentid
			WHERE s.studentid = ? AND s.deleted = 1 AND (
				-- noch nicht auf einen tag geleg
				(s.start IS null OR s.start=0)
			)
			ORDER BY s.id";

	return $DB->get_records_sql($sql,array($courseid, $studentid));
}
function block_exacomp_set_example_start_end($scheduleid, $start, $end, $deleted = 0){
	global $DB;

	$entry = $DB->get_record(block_exacomp::DB_SCHEDULE, array('id'=>$scheduleid));
	$entry->start = $start;
	$entry->end = $end;
	$entry->deleted = $deleted;

	$DB->update_record(block_exacomp::DB_SCHEDULE, $entry);
}

function block_exacomp_remove_example_from_schedule($scheduleid){
	global $DB;

	$DB->delete_records(block_exacomp::DB_SCHEDULE, array('id'=>$scheduleid));
}

function block_exacomp_get_examples_for_start_end($courseid, $studentid, $start, $end){
	global $DB;

	$sql = "select s.*,
				e.title, e.id as exampleid, e.source AS example_source, evis.visible,
				eval.student_evaluation, eval.teacher_evaluation, eval.additionalinfo, evis.courseid, s.id as scheduleid,
				e.externalurl, e.externaltask, e.description
			FROM {block_exacompschedule} s
			JOIN {block_exacompexamples} e ON e.id = s.exampleid
			JOIN {".block_exacomp::DB_EXAMPVISIBILITY."} evis ON evis.exampleid= e.id AND evis.studentid=0 AND evis.visible = 1 AND evis.courseid=?
			LEFT JOIN {block_exacompexameval} eval ON eval.exampleid = s.exampleid AND eval.studentid = s.studentid
			WHERE s.studentid = ? AND (
				-- innerhalb end und start
				(s.start > ? AND s.end < ?)
			)
			GROUP BY s.id -- because a bug somewhere causes duplicate rows
			ORDER BY e.title";
	return $DB->get_records_sql($sql,array($courseid, $studentid, $start, $end));
}

function block_exacomp_get_examples_for_start_end_all_courses($studentid, $start, $end){
	$courses = block_exacomp_get_courseids();
	$examples = array();
	foreach($courses as $course){
		$course_examples = block_exacomp_get_examples_for_start_end($course, $studentid, $start, $end);
		foreach($course_examples as $example){
			if(!array_key_exists($example->scheduleid, $examples))
				$examples[$example->scheduleid] = $example;
		}
	}

	return $examples;
}
function block_exacomp_get_json_examples($examples, $mind_eval = true){
	global $OUTPUT, $DB, $CFG, $USER, $PAGE;
	$output = block_exacomp_get_renderer();

	$array = array();
	foreach($examples as $example){
		$example_array = array();
		$example_array['id'] = $example->scheduleid;
		$example_array['title'] = $example->title;
		$example_array['start'] = $example->start;
		$example_array['end'] = $example->end;
		$example_array['exampleid'] = $example->exampleid;
		if($mind_eval){
			$example_array['student_evaluation'] = $example->student_evaluation;
			$example_array['teacher_evaluation'] = $example->teacher_evaluation;
			$example_array['additionalinfo'] = $example->additionalinfo;

			$example_array['student_evaluation_title'] = \block_exacomp\global_config::get_student_scheme_item_title($example->student_evaluation);
			$example_array['teacher_evaluation_title'] = \block_exacomp\global_config::get_scheme_item_title($example->teacher_evaluation);
		}
		if(isset($example->state))
			$example_array['state'] = $example->state;

		$example_array['studentid'] = $example->studentid;
		$example_array['courseid'] = $example->courseid;
		$example_array['scheduleid'] = $example->scheduleid;
		$img = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/assoc_icon.png'), 'alt'=>get_string("competence_associations", "block_exacomp"), 'height'=>16, 'width'=>16));

		$example_array['assoc_url'] = html_writer::link(
				new moodle_url('/blocks/exacomp/competence_associations.php',array("courseid"=>$example->courseid,"exampleid"=>$example->exampleid, "editmode"=>0)),
				 $img, array("target" => "_blank", "onclick" => "window.open(this.href,this.target,'width=880,height=660, scrollbars=yes'); return false;"));

		if($url = block_exacomp_get_file_url($example, 'example_solution'))
			$example_array['solution'] = html_writer::link($url, $OUTPUT->pix_icon("e/fullpage", get_string('solution','block_exacomp')) ,array("target" => "_blank"));
		if (block_exacomp_exaportexists ()) {
			if ($USER->id == $example->studentid) {
				$itemExists = block_exacomp_get_current_item_for_example($USER->id, $example->exampleid);

				$example_array ['submission_url'] = html_writer::link(
						new moodle_url('/blocks/exacomp/example_submission.php',array("courseid"=>$example->courseid,"exampleid"=>$example->exampleid)),
						html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/' . ((!$itemExists) ? 'manual_item.png' : 'reload.png')), 'alt'=>get_string("submission", "block_exacomp"))),
						array("target" => "_blank", "onclick" => "window.open(this.href,this.target,'width=880,height=660, scrollbars=yes'); return false;"));
			} else {
				$url = block_exacomp_get_viewurl_for_example ( $example->studentid, $example->exampleid );
				if ($url)
					$example_array ['submission_url'] = html_writer::link ( $url, html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/manual_item.png'), 'alt'=>get_string("submission", "block_exacomp"))), array (
							"target" => "_blank",
							"onclick" => "window.open(this.href,this.target,'width=880,height=660, scrollbars=yes'); return false;"
					) );
			}
		}
		if ($url = block_exacomp_get_file_url((object)array('id' => $example->exampleid), 'example_task')) {
			$example_array['task'] = html_writer::link($url, $output->print_preview_icon(),array("target" => "_blank"));
		}
		elseif(isset($example->externalurl)){
			$example_array['externalurl'] = html_writer::link(str_replace('&amp;','&',$example->externalurl), $output->print_preview_icon(),array("target" => "_blank"));
		}elseif(isset($example->externaltask)) {
			$example_array['externaltask'] = html_writer::link(str_replace('&amp;','&',$example->externaltask), $output->print_preview_icon(),array("target" => "_blank"));
		}

		$course_info = $DB->get_record('course', array('id'=>$example->courseid));
		$example_array['courseinfo'] = $course_info->shortname;

		$array[] = $example_array;
	}

	return $array;
}
function block_exacomp_build_json_time_slots($date = null){

	$units = (get_config("exacomp","scheduleunits")) ? get_config("exacomp","scheduleunits") : 8;
	$interval = (get_config("exacomp","scheduleinterval")) ? get_config("exacomp","scheduleinterval") : 50;
	$time =  (get_config("exacomp","schedulebegin")) ? get_config("exacomp","schedulebegin") : "07:45";

	list($h,$m) = explode(":",$time);
	$secTime = $h * 3600 + $m * 60;

	$slots = array();

	/*
	 * Split every unit into 4 pieces
	 */
	for($i=0; $i < $units * 4; $i++) {

		$entry = array();

		//only write at the begin of every unit
		if($i%4 == 0)
			$entry['name'] = ($i/4 + 1) . '. Einheit';
		else
			$entry['name'] = '';

		$entry['start'] = block_exacomp_parse_seconds_to_timestring($secTime);
		if ($date) {
			$entry['start_time'] = $date + $secTime;
		}
		//calculate end of current time frame
		$secTime += (($interval / 4) * 60);

		$entry['end'] = block_exacomp_parse_seconds_to_timestring($secTime);
		if ($date) {
			$entry['end_time'] = $date + $secTime;
		}

		$slots[] = $entry;
	}

	return $slots;
}
function block_exacomp_parse_seconds_to_timestring($secTime) {
	$hours = floor($secTime / 3600);
	$mins = floor(($secTime - ($hours*3600)) / 60);
	return sprintf('%02d', $hours) . ":" . sprintf('%02d', $mins) ;
}
function block_exacomp_get_dakora_state_for_example($courseid, $exampleid, $studentid){
	global $DB;
	//state 0 = never used in weekly schedule, no evaluation
	//state 1 = planned to work with example -> example is in pool, but no
	//state 2 = example is in work -> in calendar
	//state 3 = submission for example / example closed (for submission no file upload etc is necessary) -> closed
	//state 4 = evaluated -> only from teacher exacomp evaluation nE
	//state 5 = evaluated -> only from teacher exacomp evaluation > nE
	//TODO state 9 = locked time

	$example = $DB->get_record(block_exacomp::DB_EXAMPLES, array('id'=>$exampleid));
		if($example->blocking_event)
			return block_exacomp::EXAMPLE_STATE_LOCKED_TIME;

	$comp = $DB->get_record(block_exacomp::DB_EXAMPLEEVAL, array('courseid'=>$courseid, 'exampleid'=>$exampleid, 'studentid'=>$studentid));

	if($comp && $comp->teacher_evaluation !== null){
		if($comp->teacher_evaluation == 0)
			return block_exacomp::EXAMPLE_STATE_EVALUATED_NEGATIV;

		return block_exacomp::EXAMPLE_STATE_EVALUATED_POSITIV;
	}

	$sql = "select * FROM {block_exacompitemexample} ie
			JOIN {block_exaportitem} i ON i.id = ie.itemid
			WHERE ie.exampleid = ? AND i.userid = ?";

	$items_examp = $DB->get_records_sql($sql,array($exampleid, $studentid));

	if($items_examp)
		return block_exacomp::EXAMPLE_STATE_SUBMITTED;

	$schedule = $DB->get_records(block_exacomp::DB_SCHEDULE, array('courseid'=>$courseid, 'exampleid'=>$exampleid, 'studentid'=>$studentid));

	if($schedule){
		$in_work = false;
		foreach($schedule as $entry){
			if($entry->start>0 && $entry->end > 0) {
				$in_work = true;
			}
		}

		if($in_work)
			return block_exacomp::EXAMPLE_STATE_IN_CALENDAR;
		else
			return block_exacomp::EXAMPLE_STATE_IN_POOL;
	}

	return block_exacomp::EXAMPLE_STATE_NOT_SET;
}
function block_exacomp_in_pre_planing_storage($exampleid, $creatorid, $courseid){
	global $DB;

	if($DB->get_record(block_exacomp::DB_SCHEDULE, array('exampleid'=>$exampleid, 'creatorid'=>$creatorid, 'courseid'=>$courseid, 'studentid'=>0)))
		return true;

	return false;
}
function block_exacomp_has_items_pre_planning_storage($creatorid, $courseid){
	global $DB;

	return $DB->get_records(block_exacomp::DB_SCHEDULE, array('creatorid'=>$creatorid, 'courseid'=>$courseid, 'studentid'=>0));
}
function block_exacomp_get_pre_planning_storage($creatorid, $courseid){
	global $DB;

	$sql = "select s.*,
				e.title, e.id as exampleid, e.source AS example_source, evis.visible,
				evis.courseid, s.id as scheduleid
			FROM {".block_exacomp::DB_SCHEDULE."} s
			JOIN {".block_exacomp::DB_EXAMPLES."} e ON e.id = s.exampleid
			JOIN {".block_exacomp::DB_EXAMPVISIBILITY."} evis ON evis.exampleid= e.id AND evis.studentid=0 AND evis.visible = 1 AND evis.courseid=?
			WHERE s.creatorid = ? AND s.studentid=0 AND (
				-- noch nicht auf einen tag geleg
				(s.start IS null OR s.start=0)
			)
			ORDER BY s.id";

	return $DB->get_records_sql($sql,array($courseid, $creatorid));
}
function block_exacomp_get_student_pool_examples($students, $courseid){
	global $DB;

	foreach($students as $student){
		$student->pool_examples = block_exacomp_get_examples_for_pool($student->id, $courseid);
	}
	return $students;
}
function block_exacomp_example_up($exampleid, $descrid) {
	return block_exacomp_example_order($exampleid, $descrid, "<");
}
function block_exacomp_example_down($exampleid, $descrid) {
	return block_exacomp_example_order($exampleid, $descrid, ">");
}
function block_exacomp_example_order($exampleid, $descrid, $operator = "<") {
	global $DB, $USER, $COURSE;

	$example = $DB->get_record(block_exacomp::DB_EXAMPLES,array('id' => $exampleid));
	if(!$example || !$DB->record_exists(block_exacomp::DB_DESCEXAMP, array('exampid' => $exampleid,'descrid' => $descrid)))
		return false;

	$desc_examp = $DB->get_record(block_exacomp::DB_DESCEXAMP, array('exampid' => $exampleid,'descrid' => $descrid));
	$example->descsorting = $desc_examp->sorting;

	if(block_exacomp_is_admin($COURSE->id) || (isset($example->creatorid) && $example->creatorid == $USER->id)) {
		$sql = 'SELECT e.*, de.sorting as descsorting FROM {block_exacompexamples} e
			JOIN {block_exacompdescrexamp_mm} de ON de.exampid = e.id
			WHERE de.sorting ' . ((strcmp($operator,"<") == 0) ? "<" : ">") . ' ? AND de.descrid = ?
			ORDER BY de.sorting ' . ((strcmp($operator,"<") == 0) ? "DESC" : "ASC") . '
			LIMIT 1';

		$switchWith = $DB->get_record_sql($sql,array($example->descsorting, $descrid));

		if($switchWith) {
			$oldSorting = ($example->descsorting) ? $example->descsorting : 0;

			$example->descsorting = ($switchWith->descsorting) ? $switchWith->descsorting : 0;
			$switchWith->descsorting = $oldSorting;

			$desc_examp->sorting = $example->descsorting;
			$DB->update_record(block_exacomp::DB_DESCEXAMP, $desc_examp);

			$desc_examp = $DB->get_record(block_exacomp::DB_DESCEXAMP, array('exampid' => $switchWith->id,'descrid' => $descrid));
			$desc_examp->sorting = $switchWith->descsorting;
			$DB->update_record(block_exacomp::DB_DESCEXAMP, $desc_examp);

			return true;
		}
	}
	return false;
}
function block_exacomp_empty_pre_planning_storage($creatorid, $courseid){
	global $DB;

	$DB->delete_records(block_exacomp::DB_SCHEDULE, array('creatorid'=>$creatorid, 'courseid'=>$courseid, 'studentid'=>0));
}
function block_exacomp_get_current_item_for_example($userid, $exampleid) {
	global $DB;

	$sql = 'SELECT i.*, ie.status, ie.teachervalue, ie.studentvalue FROM {block_exacompexamples} e
			JOIN {block_exacompitemexample} ie ON ie.exampleid = e.id
			JOIN {block_exaportitem} i ON ie.itemid = i.id
			WHERE e.id = ?
			AND i.userid = ?
			ORDER BY ie.timecreated DESC
			LIMIT 1';

	return $DB->get_record_sql($sql,array($exampleid, $userid));
}
/**
 * keeps selected studentid in the session
 */
function block_exacomp_get_studentid($isTeacher) {
	if(!$isTeacher)
		return g::$USER->id;

	$studentid = optional_param('studentid', BLOCK_EXACOMP_DEFAULT_STUDENT, PARAM_INT);

	if($studentid == BLOCK_EXACOMP_DEFAULT_STUDENT) {
		if(isset($_SESSION['studentid-'.g::$COURSE->id]))
			$studentid = $_SESSION['studentid-'.g::$COURSE->id];
		else
			$studentid = BLOCK_EXACOMP_SHOW_ALL_STUDENTS;
	} else {
		$_SESSION['studentid-'.g::$COURSE->id] = $studentid;
	}
	return $studentid;
}

function block_exacomp_calc_example_stat_for_profile($courseid, $descriptor, $student, $scheme, $niveautitle){
	$global_scheme = get_config('exacomp', 'adminscheme');
	$global_scheme_values = array();

	if($global_scheme == 1){
		$global_scheme_values = array('nE', 'G', 'M', 'E');
	}else if($global_scheme == 2){
		$global_scheme_values = array('nE', 'A', 'B', 'C');
	}else if($global_scheme == 3){
		$global_scheme_values = array('nE', '*', '**', '***');
	}else{
		$global_scheme_values = array('0', '1', '2', '3');
	}
	list($total, $gradings, $notEvaluated, $inWork,$totalGrade, $notInWork) = block_exacomp_get_example_statistic_for_descriptor($courseid, $descriptor->id, $student->id);

	$string = "[";
	$object = array();
	$object_data = new stdClass();
	$object_data->data = array();
	$object_data->data["niveau"] = $niveautitle;
	$object_data->data["count"] = $notInWork;
	$object_data->name = ' oB';
	$object[] = $object_data;

	//$string .= "{data:[{niveau:'".$niveautitle."',count:".$notInWork."}],name:' nB'},";
	$string .= "{data:[{niveau:'".$niveautitle."',count:".$notEvaluated."}],name:' oB'},";

	 $i = 0;
	foreach($gradings as $grading){
		$object_data = new stdClass();
		$object_data->data = array();
		$object_data->data["niveau"] = $niveautitle;
		$object_data->data["count"] = $grading;
		$object_data->name = (($global_scheme==0)?$i:$global_scheme_values[$i]);
		$object[] = $object_data;

		$string .= "{data:[{niveau:'".$niveautitle."',count:".$grading."}],name:' ".(($global_scheme==0)?$i:$global_scheme_values[$i])."'},";
		$i++;
	}

	$string = substr($string, 0, strlen($string)-1);
	$string .= "]";
	$return = new stdClass();
	$return->data = $string;
	$return->dataobject = $object;
	$return->total = $total;
	$return->inWork = $inWork;
	return $return;
}
function block_exacomp_get_message_icon($userid) {
	global $DB, $CFG, $COURSE;

	if($userid != BLOCK_EXACOMP_SHOW_ALL_STUDENTS) {
		require_once($CFG->dirroot . '/message/lib.php');

		$userto = $DB->get_record('user', array('id' => $userid));

		message_messenger_requirejs();
		$url = new moodle_url('message/index.php', array('id' => $userto->id));
		$attributes = message_messenger_sendmessage_link_params($userto);

		return html_writer::link($url, html_writer::tag('button',html_writer::img(new moodle_url('/blocks/exacomp/pix/envelope.png'), get_string('message','message'),array('title' => fullname($userto)))), $attributes);
	} else {
		$attributes = array(
			'exa-type' => 'iframe-popup',
			'href'=>new moodle_url('message_to_course.php',array('courseid'=>$COURSE->id)),
			'exa-width' => '340px',
			'exa-height' => '340px',
		);
		return html_writer::tag('button',
				html_writer::img(new moodle_url('/blocks/exacomp/pix/envelope.png'), get_string('message','message'),array('title' => get_string('messagetocourse','block_exacomp'))),
				$attributes);
	}
}
function block_exacomp_send_notification($notificationtype, $userfrom, $userto, $subject, $message, $context, $contexturl) {
	global $CFG, $DB;

	if(!get_config('exacomp','notifications'))
		return;

	// do not send too many notifications. therefore check if user has got same notification within the last 5 minutes
	/*if($DB->get_records_select('message_read', "useridfrom = ? AND useridto = ? AND contexturlname = ? AND timecreated > ?",
		array('useridfrom' => $userfrom->id, 'useridto' => $userto->id, 'contexturlname' => $context, (time()-5*60))))
		return;
	*/
	require_once($CFG->dirroot . '/message/lib.php');

	$eventdata = new stdClass ();
	$eventdata->modulename = 'block_exacomp';
	$eventdata->userfrom = $userfrom;
	$eventdata->userto = $userto;
	$eventdata->subject = $subject;
	$eventdata->fullmessageformat = FORMAT_HTML;
	$eventdata->fullmessagehtml = $message;
	$eventdata->fullmessage = $message;
	$eventdata->smallmessage = $subject;

	$eventdata->name = $notificationtype;
	$eventdata->component = 'block_exacomp';
	$eventdata->notification = 0;
	$eventdata->contexturl = $contexturl;
	$eventdata->contexturlname = $context;

	message_send ( $eventdata );
}
function block_exacomp_send_submission_notification($userfrom, $userto, $example, $date, $time) {
	global $CFG,$USER;

	$subject = get_string('notification_submission_subject','block_exacomp',array('student' => fullname($userfrom), 'example' => $example->title));

	$viewurl = block_exacomp_get_viewurl_for_example($userfrom->id,$example->id);
	$message = get_string('notification_submission_body','block_exacomp',array('student' => fullname($userfrom), 'example' => $example->title, 'date' => $date, 'time' => $time, 'viewurl' => $viewurl));
	$context = get_string('notification_submission_context','block_exacomp');

	block_exacomp_send_notification("submission", $userfrom, $userto, $subject, $message, $context, $viewurl);
}
function block_exacomp_notify_all_teachers_about_submission($courseid, $exampleid, $timecreated) {
	global $USER, $DB;

	$teachers = block_exacomp_get_teachers_by_course($courseid);
	if($teachers) {
		foreach($teachers as $teacher) {
			block_exacomp_send_submission_notification($USER, $teacher, $DB->get_record(block_exacomp::DB_EXAMPLES,array('id'=>$exampleid)), date("D, d.m.Y",$timecreated), date("H:s",$timecreated));
		}
	}
}
function block_exacomp_send_self_assessment_notification($userfrom, $userto, $courseid) {
	global $CFG,$USER;

	$course = get_course($courseid);

	$subject = get_string('notification_self_assessment_subject','block_exacomp',array('course' => $course->shortname));
	$message = get_string('notification_self_assessment_body','block_exacomp',array('course' => $course->fullname, 'student' => fullname($userfrom)));
	$context = get_string('notification_self_assessment_context','block_exacomp');

	$viewurl = new moodle_url('/blocks/exacomp/assign_competencies.php',array('courseid' => $courseid));

	block_exacomp_send_notification("self_assessment", $userfrom, $userto, $subject, $message, $context, $viewurl);
}
function block_exacomp_notify_all_teachers_about_self_assessment($courseid) {
	global $USER, $DB;

	$teachers = block_exacomp_get_teachers_by_course($courseid);
	if($teachers) {
		foreach($teachers as $teacher) {
			block_exacomp_send_self_assessment_notification($USER, $teacher, $courseid);
		}
	}
}
function block_exacomp_send_grading_notification($userfrom, $userto, $courseid) {
	global $CFG,$USER;

	$course = get_course($courseid);

	$subject = get_string('notification_grading_subject','block_exacomp',array('course' => $course->shortname));
	$message = get_string('notification_grading_body','block_exacomp',array('course' => $course->fullname, 'teacher' => fullname($userfrom)));
	$context = get_string('notification_grading_context','block_exacomp');

	$viewurl = new moodle_url('/blocks/exacomp/assign_competencies.php',array('courseid' => $courseid));

	block_exacomp_send_notification("grading", $userfrom, $userto, $subject, $message, $context, $viewurl);
}
function block_exacomp_notify_students_about_grading($courseid, $students) {
	global $USER, $DB;

	if($students) {
		foreach($students as $student) {
			block_exacomp_send_grading_notification($USER, $DB->get_record('user', array('id' => $student)), $courseid);
		}
	}
}
function block_exacomp_send_weekly_schedule_notification($userfrom, $userto, $courseid, $exampleid) {
	global $CFG,$USER,$DB;

	$course = get_course($courseid);
	$example = $DB->get_record(block_exacomp::DB_EXAMPLES,array('id' => $exampleid));
	$subject = get_string('notification_weekly_schedule_subject','block_exacomp');
	$message = get_string('notification_weekly_schedule_body','block_exacomp',array('course' => $course->fullname, 'teacher' => fullname($userfrom), 'example' => $example->title));
	$context = get_string('notification_weekly_schedule_context','block_exacomp');

	$viewurl = new moodle_url('/blocks/exacomp/weekly_schedule.php',array('courseid' => $courseid));

	block_exacomp_send_notification("weekly_schedule", $userfrom, $userto, $subject, $message, $context, $viewurl);
}
function block_exacomp_send_example_comment_notification($userfrom, $userto, $courseid, $exampleid) {
	global $CFG,$USER,$DB;

	$course = get_course($courseid);
	$example = $DB->get_record(block_exacomp::DB_EXAMPLES,array('id' => $exampleid));
	$subject = get_string('notification_example_comment_subject','block_exacomp');
	$message = get_string('notification_example_comment_body','block_exacomp',array('course' => $course->fullname, 'teacher' => fullname($userfrom), 'example' => $example->title));
	$context = get_string('notification_example_comment_context','block_exacomp');

	$viewurl = block_exacomp_get_viewurl_for_example($userto->id,$example->id);

	block_exacomp_send_notification("comment", $userfrom, $userto, $subject, $message, $context, $viewurl);
}

/**
 *
 * @param int $cmid
 * @return cm_info
 */
function block_exacomp_get_cm_from_cmid($cmid) {
	try {
		// get_course_and_cm_from_cmid() throws moodle_exception if cm not found
		list($course, $cm) = get_course_and_cm_from_cmid($cmid);
		return $cm;
	} catch (moodle_exception $e) {
		return null;
	}
}

function block_exacomp_save_additional_grading_for_descriptor($courseid, $descriptorid, $studentid, $additionalinfo){
	global $DB, $USER;

	$record = $DB->get_record(block_exacomp::DB_COMPETENCIES, array('courseid'=>$courseid, 'compid'=>$descriptorid, 'userid'=>$studentid, 'comptype'=>block_exacomp::TYPE_DESCRIPTOR, 'role'=>block_exacomp::ROLE_TEACHER));
	if($record){
		$record->additionalinfo = $additionalinfo;
		$DB->update_record(block_exacomp::DB_COMPETENCIES, $record);
	}else{
		$insert = new stdClass();
		$insert->compid = $descriptorid;
		$insert->userid = $studentid;
		$insert->courseid = $courseid;
		$insert->comptype = block_exacomp::TYPE_DESCRIPTOR;
		$insert->additionalinfo = $additionalinfo;
		$insert->role = block_exacomp::ROLE_TEACHER;
		$insert->reviewerid = $USER->id;
		$DB->insert_record(block_exacomp::DB_COMPETENCIES, $insert);
	}
}

function block_exacomp_save_additional_grading_for_example($courseid, $exampleid, $studentid, $additionalinfo) {
	global $DB, $USER;

	if($additionalinfo == -1)
		$additionalinfo = null;

	$record = $DB->get_record ( block_exacomp::DB_EXAMPLEEVAL, array (
			'courseid' => $courseid,
			'exampleid' => $exampleid,
			'studentid' => $studentid
	) );
	if ($record) {
		$record->additionalinfo = $additionalinfo;
		$DB->update_record ( block_exacomp::DB_EXAMPLEEVAL, $record );
	} else {
		$insert = new stdClass ();
		$insert->exampleid = $exampleid;
		$insert->studentid = $studentid;
		$insert->courseid = $courseid;
		$insert->teacher_reviewerid = $USER->id;
		$insert->additionalinfo = $additionalinfo;
		$DB->insert_record ( block_exacomp::DB_EXAMPLEEVAL, $insert );
	}

	$item = block_exacomp_get_current_item_for_example ( $studentid, $exampleid );
	if ($item) {
		$itemexample = $DB->get_record ( 'block_exacompitemexample', array (
				'exampleid' => $exampleid,
				'itemid' => $item->id
		) );

		$itemexample->teachervalue = $additionalinfo;
		$itemexample->datemodified = time ();

		$DB->update_record ( 'block_exacompitemexample', $itemexample );
	}
}
function block_exacomp_course_has_examples($courseid){
	global $DB;

	$sql = "SELECT COUNT(*)
		FROM {".block_exacomp::DB_EXAMPLES."} ex
		JOIN {".block_exacomp::DB_DESCEXAMP."} dex ON ex.id = dex.exampid
		JOIN {".block_exacomp::DB_DESCTOPICS."} det ON dex.descrid = det.descrid
		JOIN {".block_exacomp::DB_COURSETOPICS."} ct ON det.topicid = ct.topicid
		WHERE ct.courseid = ?";

	return (bool)$DB->get_field_sql($sql, array($courseid));
}
function block_exacomp_send_message_to_course($courseid, $message) {
	global $USER;

	require_capability('moodle/site:sendmessage', context_system::instance());
	block_exacomp_require_teacher($courseid);

	$students = block_exacomp_get_students_by_course($courseid);

	foreach($students as $student) {
		if (empty($student->id) || isguestuser($student->id) || $student->id == $USER->id) {
			continue;
		}

		$messageid = message_post_message($USER, $student, $message, FORMAT_MOODLE);

		if (!$messageid) {
			throw new moodle_exception('errorwhilesendingmessage', 'core_message');
		}

	}
}

function block_exacomp_create_blocking_event($courseid, $title, $creatorid){
	global $DB;

	$example = new stdClass();
	$example->title = $title;
	$example->creatorid = $creatorid;
	$example->blocking_event = 1;

	$exampleid = $DB->insert_record(block_exacomp::DB_EXAMPLES, $example);

	$schedule = new stdClass();
	$schedule->studentid = 0;
	$schedule->exampleid = $exampleid;
	$schedule->creatorid = $creatorid;
	$schedule->courseid = $courseid;

	$scheduleid = $DB->insert_record(block_exacomp::DB_SCHEDULE, $schedule);

	$visibility = new stdClass();
	$visibility->courseid = $courseid;
	$visibility->exampleid = $exampleid;
	$visibility->studentid = 0;
	$visibility->visible = 1;

	$vibilityid = $DB->insert_record(block_exacomp::DB_EXAMPVISIBILITY, $visibility);
}

}

namespace block_exacomp {
	class global_config {
		static function get_scheme_item_title($id) {
			$items = static::get_scheme_items();
			if (!empty($items[$id])) {
				return $items[$id];
			} else {
				return null;
			}
		}

		static function get_scheme_items() {
			$global_scheme = static::get_scheme_id();

			if($global_scheme == 1){
				$global_scheme_values = array('nE', 'G', 'M', 'E');
			}else if($global_scheme == 2){
				$global_scheme_values = array('nE', 'A', 'B', 'C');
			}else if($global_scheme == 3){
				$global_scheme_values = array('nE', '*', '**', '***');
			}else{
				$global_scheme_values = array('0', '1', '2', '3');
			}

			return $global_scheme_values;
		}

		static function get_student_scheme_item_title($id) {
			$items = static::get_student_scheme_items();
			if (!empty($items[$id])) {
				return $items[$id];
			} else {
				return null;
			}
		}

		static function get_student_scheme_items() {
			$global_scheme = static::get_scheme_id();

			if (!$global_scheme) {
				return array('0', '1', '2', '3');
			} else {
				return array('', '*', '**', '***');
			}
		}

		static function get_scheme_id() {
			return get_config('exacomp', 'adminscheme');
		}
	}
}