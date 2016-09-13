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

namespace block_exacomp {

defined('MOODLE_INTERNAL') || die();

require_once __DIR__.'/common.php';
require_once __DIR__.'/classes.php';
require_once __DIR__.'/../block_exacomp.php';

if (block_exacomp_moodle_badges_enabled()) {
	require_once($CFG->libdir . '/badgeslib.php');
	require_once($CFG->dirroot . '/badges/lib/awardlib.php');
}

/**
 * DATABSE TABLE NAMES
 */
const DB_SKILLS = 'block_exacompskills';
const DB_NIVEAUS = 'block_exacompniveaus';
const DB_TAXONOMIES = 'block_exacomptaxonomies';
const DB_EXAMPLES = 'block_exacompexamples';
const DB_EXAMPLEEVAL = 'block_exacompexameval';
const DB_DESCRIPTORS = 'block_exacompdescriptors';
const DB_DESCEXAMP = 'block_exacompdescrexamp_mm';
const DB_EDULEVELS = 'block_exacompedulevels';
const DB_SCHOOLTYPES = 'block_exacompschooltypes';
const DB_SUBJECTS = 'block_exacompsubjects';
const DB_TOPICS = 'block_exacomptopics';
const DB_COURSETOPICS = 'block_exacompcoutopi_mm';
const DB_DESCTOPICS = 'block_exacompdescrtopic_mm';
const DB_CATEGORIES = 'block_exacompcategories';
const DB_COMPETENCE_ACTIVITY = 'block_exacompcompactiv_mm';
const DB_COMPETENCES = 'block_exacompcompuser';
const DB_COMPETENCE_USER_MM = 'block_exacompcompuser_mm';
const DB_SETTINGS = 'block_exacompsettings';
const DB_MDLTYPES = 'block_exacompmdltype_mm';
const DB_DESCBADGE = 'block_exacompdescbadge_mm';
const DB_PROFILESETTINGS = 'block_exacompprofilesettings';
const DB_CROSSSUBJECTS = 'block_exacompcrosssubjects';
const DB_DESCCROSS = 'block_exacompdescrcross_mm';
const DB_CROSSSTUD = 'block_exacompcrossstud_mm';
const DB_DESCVISIBILITY = 'block_exacompdescrvisibility';
const DB_DESCCAT = 'block_exacompdescrcat_mm';
const DB_EXAMPTAX = 'block_exacompexampletax_mm';
const DB_DATASOURCES = 'block_exacompdatasources';
const DB_SCHEDULE = 'block_exacompschedule';
const DB_EXAMPVISIBILITY = 'block_exacompexampvisibility';
const DB_ITEMEXAMPLE = 'block_exacompitemexample';
const DB_SUBJECT_NIVEAU_MM = 'block_exacompsubjniveau_mm';
const DB_EXTERNAL_TRAINERS = 'block_exacompexternaltrainer';
const DB_EVALUATION_NIVEAU = 'block_exacompeval_niveau';
const DB_TOPICVISIBILITY = 'block_exacomptopicvisibility';
const DB_SOLUTIONVISIBILITY = 'block_exacompsolutvisibility';

/**
 * PLUGIN ROLES
 */
const ROLE_TEACHER = 1;
const ROLE_STUDENT = 0;

const WS_ROLE_TEACHER = 1;
const WS_ROLE_STUDENT = 2;

/**
 * COMPETENCE TYPES
 */
const TYPE_DESCRIPTOR = 0;
const TYPE_TOPIC = 1;
const TYPE_CROSSSUB = 2;
const TYPE_SUBJECT = 3;

const SETTINGS_MAX_SCHEME = 10;
const DATA_SOURCE_CUSTOM = 3;
const EXAMPLE_SOURCE_TEACHER = 3;
const EXAMPLE_SOURCE_USER = 4;

const IMPORT_SOURCE_DEFAULT = 1;
const IMPORT_SOURCE_SPECIFIC = 2;

const CUSTOM_CREATED_DESCRIPTOR = 3;

const EXAMPLE_STATE_NOT_SET = 0; // never used in weekly schedule, no evaluation
const EXAMPLE_STATE_IN_POOL = 1; // planned to work with example -> example is in pool
const EXAMPLE_STATE_IN_CALENDAR = 2; // example is in work -> in calendar
const EXAMPLE_STATE_SUBMITTED = 3; //state 3 = submission for example / example closed (for submission no file upload etc is necessary) -> closed
const EXAMPLE_STATE_EVALUATED_NEGATIV = 4; // evaluated -> only from teacher-> exacomp evaluation nE
const EXAMPLE_STATE_EVALUATED_POSITIV = 5; //evaluated -> only from teacher -> exacomp evaluation > nE
const EXAMPLE_STATE_LOCKED_TIME = 9; //handled like example entry on calender, but represent locked time

const STUDENTS_PER_COLUMN = 3;
const SHOW_ALL_TOPICS = -1;
const SHOW_ALL_NIVEAUS = 99999999;

const CAP_ADD_EXAMPLE = 'add_example';
const CAP_VIEW = 'view';
const CAP_MODIFY = 'modify';
const CAP_DELETE = 'delete';
const CAP_SORTING = 'sorting';
}

namespace {

use block_exacomp\globals as g;

/**
 * COMPETENCE TYPES
 */
define('TYPE_DESCRIPTOR', 0);
define('TYPE_TOPIC', 1);
define('TYPE_CROSSSUB', 2);

$usebadges = get_config('exacomp', 'usebadges');
$xmlserverurl = get_config('exacomp', 'xmlserverurl');
$autotest = get_config('exacomp', 'autotest');
$testlimit = get_config('exacomp', 'testlimit');
$specificimport = get_config('exacomp','enableteacherimport');
$notifications = get_config('exacomp','notifications');

define("SHOW_ALL_TAXONOMIES",100000000);
define("BLOCK_EXACOMP_SHOW_ALL_STUDENTS", -1);
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
	$PAGE->requires->js("/blocks/exacomp/javascript/simpletreemenu/simpletreemenu.js", true);
	$PAGE->requires->css("/blocks/exacomp/javascript/simpletreemenu/simpletree.css", true);
	$PAGE->requires->js('/blocks/exacomp/javascript/exacomp.js', true);
	$PAGE->requires->js('/blocks/exacomp/javascript/ajax.js', true);

	// Strings can be used in JavaScript: M.util.get_string(identifier, component)
	$PAGE->requires->strings_for_js([
		'show', 'hide' //, 'selectall', 'deselectall'
	], 'moodle');
	$PAGE->requires->strings_for_js([
        'override_notice', 'unload_notice', 'example_sorting_notice', 'delete_unconnected_examples', 'value_too_large', 'value_too_low', 'value_not_allowed', 'hide_solution', 'show_solution', 'weekly_schedule', 'pre_planning_storage', 'weekly_schedule_disabled', 'pre_planning_storage_disabled', 'add_example_for_all_students_to_schedule_confirmation', 'seperatordaterange'
	], 'block_exacomp');
	
	// page specific js/css
	$scriptName = preg_replace('!\.[^\.]+$!', '', basename($_SERVER['PHP_SELF']));
	if (file_exists($CFG->dirroot.'/blocks/exacomp/css/'.$scriptName.'.css'))
		$PAGE->requires->css('/blocks/exacomp/css/'.$scriptName.'.css');
	if (file_exists($CFG->dirroot.'/blocks/exacomp/javascript/'.$scriptName.'.js'))
		$PAGE->requires->js('/blocks/exacomp/javascript/'.$scriptName.'.js', true);
}
function block_exacomp_init_js_weekly_schedule(){
	global $PAGE;
	
	$PAGE->requires->jquery_plugin('ui');
	$PAGE->requires->jquery_plugin('ui-css');
	
	$PAGE->requires->css('/blocks/exacomp/javascript/fullcalendar/fullcalendar.css');
	$PAGE->requires->js('/blocks/exacomp/javascript/fullcalendar/moment.min.js', true);
	$PAGE->requires->js('/blocks/exacomp/javascript/fullcalendar/fullcalendar.js', true);
	$PAGE->requires->js('/blocks/exacomp/javascript/fullcalendar/lang-all.js', true);
	$PAGE->requires->js('/blocks/exacomp/javascript/fullcalendar/jquery.ui.touch.js');
}

function block_exacomp_get_context_from_courseid($courseid) {
	if ($courseid instanceof context) {
		// already context
		return $courseid;
	} else if (is_numeric($courseid)) { // don't use is_int, because eg. moodle $COURSE->id is a string!
		return context_course::instance($courseid);
	} else if ($courseid === null) {
		return context_course::instance(g::$COURSE->id);
	} else {
		throw new \moodle_exception('wrong courseid type '.gettype($courseid));
	}
}
/**
 * 
 * @param courseid or context $context
 */
function block_exacomp_is_teacher($context = null, $userid = null) {
	$context = block_exacomp_get_context_from_courseid($context);
	return has_capability('block/exacomp:teacher', $context, $userid);
}

function block_exacomp_is_teacher_in_any_course() {
	$courses = block_exacomp_get_courseids();

	foreach($courses as $course) {
		if(block_exacomp_is_teacher($course))
			return true;
	}

	return false;
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
function block_exacomp_is_elove_student_self_assessment_enabled() {
	return get_config('exacomp', 'elove_student_self_assessment');
}
function block_exacomp_use_eval_niveau(){
	return get_config('exacomp', 'use_eval_niveau');
}

function block_exacomp_evaluation_niveau_type(){
	return (get_config('exacomp', 'adminscheme')) ? get_config('exacomp', 'adminscheme') : 0;
}

function block_exacomp_additional_grading(){
	return get_config('exacomp', 'additional_grading');
}

function block_exacomp_get_timetable_entries(){
	$content = get_config('exacomp', 'periods');
	return explode("\n", $content);
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
 * @deprecated use subject::get(id) instead
 */
function block_exacomp_get_subject_by_id($subjectid) {
	global $DB;
	return $DB->get_record(\block_exacomp\DB_SUBJECTS,array("id" => $subjectid),'id, title, titleshort, source, author');
}
/**
 * Gets all subjects that are in use in a particular course.
 *
 * @param int $courseid
 * @param bool $showalldescriptors default false, show only comps with activities
 * @return array $subjects
 */
function block_exacomp_get_subjects_by_course($courseid, $showalldescriptors = false) {
	if(!$showalldescriptors)
		$showalldescriptors = block_exacomp_get_settings_by_course($courseid)->show_all_descriptors;

	$sql = '
	SELECT DISTINCT s.id, s.titleshort, s.title, s.stid, s.infolink, s.description, s.source, s.sorting, s.author
	FROM {'.\block_exacomp\DB_SUBJECTS.'} s
	JOIN {'.\block_exacomp\DB_TOPICS.'} t ON t.subjid = s.id
	JOIN {'.\block_exacomp\DB_COURSETOPICS.'} ct ON ct.topicid = t.id AND ct.courseid = ?
	'.($showalldescriptors ? '' : '
			-- only show active ones
			JOIN {'.\block_exacomp\DB_DESCTOPICS.'} topmm ON topmm.topicid=t.id
			JOIN {'.\block_exacomp\DB_DESCRIPTORS.'} d ON topmm.descrid=d.id
			JOIN {'.\block_exacomp\DB_COMPETENCE_ACTIVITY.'} ca ON (d.id=ca.compid AND ca.comptype = '.TYPE_DESCRIPTOR.') OR (t.id=ca.compid AND ca.comptype = '.TYPE_TOPIC.')
			JOIN {course_modules} a ON ca.activityid=a.id AND a.course=ct.courseid
			').'
	ORDER BY s.title
			';

	$subjects = block_exacomp\subject::get_objects_sql($sql, array($courseid));

	return block_exacomp_sort_items($subjects, \block_exacomp\DB_SUBJECTS);
}
/**
 * Gets all available subjects
 */
function block_exacomp_get_all_subjects() {
	global $DB;
	return $DB->get_records(\block_exacomp\DB_SUBJECTS,array(),'','id, title, source, sourceid, author');
}
/**
 * This method is only used in the LIS version
 * @param int $courseid
 */
function block_exacomp_get_schooltypes_by_course($courseid) {
	global $DB;
	return $DB->get_records_sql('
			SELECT DISTINCT s.id, s.title, s.source, s.sourceid, s.sorting
			FROM {'.\block_exacomp\DB_SCHOOLTYPES.'} s
			JOIN {'.\block_exacomp\DB_MDLTYPES.'} m ON m.stid = s.id AND m.courseid = ?
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
	$sql = 'SELECT s.* FROM {'.\block_exacomp\DB_SUBJECTS.'} s
	JOIN {'.\block_exacomp\DB_MDLTYPES.'} type ON s.stid = type.stid
	WHERE type.courseid=?';

	if($schooltypeid > 0)
		$sql .= ' AND type.stid = ?';

	return \block_exacomp\subject::get_objects_sql($sql, [$courseid, $schooltypeid]);
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
		$sql = 'SELECT s.id, s.title, s.author
		FROM {'.\block_exacomp\DB_SUBJECTS.'} s
		JOIN {'.\block_exacomp\DB_TOPICS.'} t ON t.subjid = s.id
		GROUP BY s.id, s.title, s.stid
		ORDER BY s.stid, s.sorting, s.title
		';

		return $DB->get_records_sql($sql);
	} else if($subjectid != null) {
		$sql = 'SELECT s.id, s.title, s.author
		FROM {'.\block_exacomp\DB_SUBJECTS.'} s
		JOIN {'.\block_exacomp\DB_TOPICS.'} t ON t.subjid = s.id
		WHERE s.id = ?
		GROUP BY s.id, s.title, s.stid
		ORDER BY s.stid, s.sorting, s.title';

		return $DB->get_records_sql($sql,$subjectid);
	}

	$subjects = $DB->get_records_sql('
			SELECT DISTINCT s.id, s.title, s.stid, s.sorting
			FROM {'.\block_exacomp\DB_SUBJECTS.'} s
			JOIN {'.\block_exacomp\DB_TOPICS.'} t ON t.subjid = s.id
			JOIN {'.\block_exacomp\DB_COURSETOPICS.'} ct ON ct.topicid = t.id AND ct.courseid = ?
			'.(block_exacomp_get_settings_by_course($courseid)->show_all_descriptors ? '' : '
					-- only show active ones
					JOIN {'.\block_exacomp\DB_DESCTOPICS.'} topmm ON topmm.topicid=t.id
					JOIN {'.\block_exacomp\DB_DESCRIPTORS.'} d ON topmm.descrid=d.id
					JOIN {'.\block_exacomp\DB_COMPETENCE_ACTIVITY.'} ca ON (d.id=ca.compid AND ca.comptype = '.TYPE_DESCRIPTOR.') OR (t.id=ca.compid AND ca.comptype = '.TYPE_TOPIC.')
					JOIN {course_modules} a ON ca.activityid=a.id AND a.course=ct.courseid
					').'
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

		$full = $DB->get_record(\block_exacomp\DB_DESCRIPTORS, array("id" => $descriptor->descrid));
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
function block_exacomp_get_topics_by_course($courseid,$showalldescriptors = false, $showonlyvisible=false) {
	return block_exacomp_get_topics_by_subject($courseid,0,$showalldescriptors, $showonlyvisible);
}
/**
 * Gets all topics from a particular subject
 * @param int $courseid
 * @param int $subjectid
 */
function block_exacomp_get_topics_by_subject($courseid, $subjectid = 0, $showalldescriptors = false, $showonlyvisible=false) {
	global $DB;
	if(!$courseid)
		$showonlyvisible = false;
	
	if(!$showalldescriptors)
		$showalldescriptors = block_exacomp_get_settings_by_course($courseid)->show_all_descriptors;

	$sql = 'SELECT DISTINCT t.id, t.title, t.sorting, t.subjid, t.description, t.numb, t.source, tvis.visible as visible, s.source AS subj_source, s.sorting AS subj_sorting, s.title AS subj_title
	FROM {'.\block_exacomp\DB_TOPICS.'} t
	JOIN {'.\block_exacomp\DB_COURSETOPICS.'} ct ON ct.topicid = t.id AND ct.courseid = ? '.(($subjectid > 0) ? 'AND t.subjid = ? ': '').'
	JOIN {'.\block_exacomp\DB_SUBJECTS.'} s ON t.subjid=s.id -- join subject here, to make sure only topics with existing subject are loaded
	-- left join, because courseid=0 has no topicvisibility!
	JOIN {'.\block_exacomp\DB_TOPICVISIBILITY.'} tvis ON tvis.topicid=t.id AND tvis.studentid=0 AND tvis.courseid=?'
	.($showonlyvisible?' AND tvis.visible = 1 ':'')
	.($showalldescriptors ? '' : '
			-- only show active ones
			JOIN {'.\block_exacomp\DB_DESCTOPICS.'} topmm ON topmm.topicid=t.id
			JOIN {'.\block_exacomp\DB_DESCRIPTORS.'} d ON topmm.descrid=d.id
			JOIN {'.\block_exacomp\DB_COMPETENCE_ACTIVITY.'} da ON (d.id=da.compid AND da.comptype = '.TYPE_DESCRIPTOR.') OR (t.id=da.compid AND da.comptype = '.TYPE_TOPIC.')
			JOIN {course_modules} a ON da.activityid=a.id AND a.course=ct.courseid
			').'
			';
	
	//GROUP By funktioniert nur mit allen feldern im select, aber nicht mit strings
	$params = array($courseid);
	if($subjectid>0)
		$params[] = $subjectid;
	$params[] = $courseid;
	
	$topics = $DB->get_records_sql($sql,$params);

	return block_exacomp_sort_items($topics, ['subj_' => \block_exacomp\DB_SUBJECTS, '' => \block_exacomp\DB_TOPICS]);
}

function block_exacomp_sort_items(&$items, $sortings) {
	$sortings = (array)$sortings;

	uasort($items, function($a, $b) use ($sortings) {
		foreach ($sortings as $prefix => $sorting) {
			if (is_int($prefix)) $prefix = '';

			if ($sorting == \block_exacomp\DB_SUBJECTS) {
				if (!property_exists($a, $prefix.'source') || !property_exists($b, $prefix.'source')) {
					throw new \block_exacomp\moodle_exception('col not found: '.$prefix.'source');
				}
				if (!property_exists($a, $prefix.'sorting') || !property_exists($b, $prefix.'sorting')) {
					throw new \block_exacomp\moodle_exception('col not found: '.$prefix.'sorting');
				}
				if (!property_exists($a, $prefix.'title') || !property_exists($b, $prefix.'title')) {
					throw new \block_exacomp\moodle_exception('col not found: '.$prefix.'title');
				}

				// sort subjects
				// first imported, then generated
				if ($a->{$prefix.'source'} != \block_exacomp\DATA_SOURCE_CUSTOM && $b->{$prefix.'source'} == \block_exacomp\DATA_SOURCE_CUSTOM)
					return -1;
				if ($a->{$prefix.'source'} == \block_exacomp\DATA_SOURCE_CUSTOM && $b->{$prefix.'source'} != \block_exacomp\DATA_SOURCE_CUSTOM)
					return 1;

				if ($a->{$prefix.'sorting'} < $b->{$prefix.'sorting'})
					return -1;
				if ($a->{$prefix.'sorting'} > $b->{$prefix.'sorting'})
					return 1;

				if ($a->{$prefix.'title'} != $b->{$prefix.'title'}) {
					return strcmp($a->{$prefix.'title'}, $b->{$prefix.'title'});
				}
			} elseif ($sorting == \block_exacomp\DB_TOPICS) {
				if (!property_exists($a, $prefix.'sorting') || !property_exists($b, $prefix.'sorting')) {
					throw new \block_exacomp\moodle_exception('col not found: '.$prefix.'sorting');
				}
				if (!property_exists($a, $prefix.'numb') || !property_exists($b, $prefix.'numb')) {
					throw new \block_exacomp\moodle_exception('col not found: '.$prefix.'numb');
				}
				if (!property_exists($a, $prefix.'title') || !property_exists($b, $prefix.'title')) {
					throw new \block_exacomp\moodle_exception('col not found: '.$prefix.'title');
				}

				if ($a->{$prefix.'numb'} < $b->{$prefix.'numb'})
					return -1;
				if ($a->{$prefix.'numb'} > $b->{$prefix.'numb'})
					return 1;

				if ($a->{$prefix.'sorting'} < $b->{$prefix.'sorting'})
					return -1;
				if ($a->{$prefix.'sorting'} > $b->{$prefix.'sorting'})
					return 1;

				if ($a->{$prefix.'title'} != $b->{$prefix.'title'}) {
					return strcmp($a->{$prefix.'title'}, $b->{$prefix.'title'});
				}
			} elseif ($sorting == \block_exacomp\DB_DESCRIPTORS) {
				if (!property_exists($a, $prefix.'sorting') || !property_exists($b, $prefix.'sorting')) {
					throw new \block_exacomp\moodle_exception('col not found: '.$prefix.'sorting');
				}
				if (!property_exists($a, $prefix.'title') || !property_exists($b, $prefix.'title')) {
					throw new \block_exacomp\moodle_exception('col not found: '.$prefix.'title');
				}

				if (!property_exists($a, $prefix.'source') || !property_exists($b, $prefix.'source')) {
					debugging('block_exacomp_sort_items() descriptors need a source', DEBUG_DEVELOPER);
				} else {
					if ($a->{$prefix.'source'} != $b->{$prefix.'source'}) {
						if ($a->{$prefix.'source'} == \block_exacomp\DATA_SOURCE_CUSTOM) {
							return 1;
						}
						if ($b->{$prefix.'source'} == \block_exacomp\DATA_SOURCE_CUSTOM) {
							return -1;
						}
					}
				}

				if ($a->{$prefix.'sorting'} < $b->{$prefix.'sorting'})
					return -1;
				if ($a->{$prefix.'sorting'} > $b->{$prefix.'sorting'})
					return 1;

				// last by title
				if ($a->{$prefix.'title'} != $b->{$prefix.'title'}) {
					return strcmp($a->{$prefix.'title'}, $b->{$prefix.'title'});
				}
			} elseif ($sorting == \block_exacomp\DB_NIVEAUS) {
				if (!property_exists($a, $prefix.'sorting') || !property_exists($b, $prefix.'sorting')) {
					throw new \block_exacomp\moodle_exception('col not found: '.$prefix.'sorting');
				}
				if (!property_exists($a, $prefix.'title') || !property_exists($b, $prefix.'title')) {
					throw new \block_exacomp\moodle_exception('col not found: '.$prefix.'title');
				}

				if ($a->{$prefix.'sorting'} != $b->{$prefix.'sorting'}) {
					// move descriptors without niveau.sorting (which actually probably means they have no niveau) to the end
					if (!$a->{$prefix.'sorting'}) {
						return 1;
					}
					if (!$b->{$prefix.'sorting'}) {
						return -1;
					}

					if ($a->{$prefix.'sorting'} < $b->{$prefix.'sorting'})
						return -1;
					if ($a->{$prefix.'sorting'} > $b->{$prefix.'sorting'})
						return 1;
				}

				if ($a->{$prefix.'title'} != $b->{$prefix.'title'}) {
					return strcmp($a->{$prefix.'title'}, $b->{$prefix.'title'});
				}
			} else {
				throw new \block_exacomp\moodle_exception('sorting type not found: '.$sorting);
			}
		}
	});

	return $items;
}


/**
 * Gets all topics
 */
function block_exacomp_get_all_topics($subjectid = null, $showonlyvisible = false) {
	global $DB;
	
	$topics = $DB->get_records_sql('
			SELECT t.id, t.sorting, t.numb, t.title, t.parentid, t.subjid, s.source AS subj_source, s.sorting AS subj_sorting, s.title AS subj_title
			FROM {'.\block_exacomp\DB_SUBJECTS.'} s
			JOIN {'.\block_exacomp\DB_TOPICS.'} t ON t.subjid = s.id
			'.($subjectid == null ? '' : '
					-- only show active ones
					WHERE s.id = ?
					').'
			', array($subjectid));

	return block_exacomp_sort_items($topics, ['subj_' => \block_exacomp\DB_SUBJECTS, '' => \block_exacomp\DB_TOPICS]);
}
/**
 *
 * Gets topic with particular id
 * @param  $topicid
 * @deprecated use topic::get(id) instead
 */
function block_exacomp_get_topic_by_id($topicid) {
	global $DB;

	$topic = $DB->get_record_sql('
			SELECT t.id, t.title, t.parentid, t.subjid, t.numb
			FROM {'.\block_exacomp\DB_TOPICS.'} t
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
		if($DB->record_exists(\block_exacomp\DB_COMPETENCE_ACTIVITY, array("compid"=>$compid,"comptype"=>$comptype,"activityid"=>$cm->id)))
			return true;
	}

	return false;
}

/**
 * Deletes an uploaded example and all it's database/filesystem entries
 * @param int $delete exampleid
 */
function block_exacomp_delete_custom_example($example_object_or_id) {
	global $DB;

	$example = block_exacomp\example::get($example_object_or_id);
	if (!$example) {
		throw new \moodle_exception('Example not found');
	}

	block_exacomp\require_item_capability(block_exacomp\CAP_DELETE, $example);

	$DB->delete_records(\block_exacomp\DB_EXAMPLES, array('id' => $example->id));
	$DB->delete_records(\block_exacomp\DB_DESCEXAMP, array('exampid' => $example->id));
	$DB->delete_records(\block_exacomp\DB_EXAMPLEEVAL, array('exampleid' => $example->id));

	$fs = get_file_storage();
	$fs->delete_area_files(\context_system::instance()->id, 'block_exacomp', 'example_task', $example->id);
	$fs->delete_area_files(\context_system::instance()->id, 'block_exacomp', 'example_solution', $example->id);
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
 * @param int $evalniveauid
 */
function block_exacomp_set_user_competence($userid, $compid, $comptype, $courseid, $role, $value, $evalniveauid = null) {
	global $DB, $USER;

	if($evalniveauid !== null && $evalniveauid < 1)
		$evalniveauid = null;
	
	// TODO: block_exacomp_external::require_teacher_permission($courseid, $userid);
	if($role == \block_exacomp\ROLE_STUDENT && $userid != $USER->id)
		return -1;
	if($role == \block_exacomp\ROLE_TEACHER)
		block_exacomp_require_teacher($courseid);

	$id = -1;

	if($record = block_exacomp\get_comp_eval($courseid, $role, $userid, $comptype, $compid)) {
		$record->value = ($value != -1) ? $value : null;
		$record->timestamp = time();
		$record->reviewerid = $USER->id;
		$record->evalniveauid = $evalniveauid;
		$DB->update_record(\block_exacomp\DB_COMPETENCES, $record);
		$id = $record->id;
	} else {
		$id = $DB->insert_record(\block_exacomp\DB_COMPETENCES, array("userid" => $userid, "compid" => $compid, "comptype" => $comptype, "courseid" => $courseid, "role" => $role, "value" => $value, "reviewerid" => $USER->id, "timestamp" => time(), "evalniveauid" => $evalniveauid));
	}

	if($role == \block_exacomp\ROLE_TEACHER)
		block_exacomp_send_grading_notification($USER, $DB->get_record('user',array('id'=>$userid)), $courseid);
	else
		block_exacomp_notify_all_teachers_about_self_assessment($courseid);

	\block_exacomp\event\competence_assigned::log(['objecttable' => ($comptype == \block_exacomp\TYPE_DESCRIPTOR) ? 'block_exacompdescriptors' : 'block_exacomptopics', 'objectid' => $compid, 'courseid' => $courseid, 'relateduserid' => $userid]);

	return $id;
}

function block_exacomp_set_user_example($userid, $exampleid, $courseid, $role, $value = null, $evalniveauid = null) {
	global $DB, $USER;

	$updateEvaluation = new stdClass();
	if($evalniveauid !== null && $evalniveauid < 1)
		$evalniveauid = null;

	if ($role == \block_exacomp\ROLE_TEACHER) {
		block_exacomp_require_teacher($courseid);
		$updateEvaluation->teacher_evaluation = ($value != -1) ? $value : null;
		$updateEvaluation->teacher_reviewerid = $USER->id;
		$updateEvaluation->timestamp_teacher = time();
		$updateEvaluation->evalniveauid = $evalniveauid;
		$updateEvaluation->resubmission = ($value != -1) ? false : true;
	} else {
		if ($userid != $USER->id)
			// student can only assess himself
			return;

			$updateEvaluation->timestamp_student = time();
			if($value !== null)
				$updateEvaluation->student_evaluation = ($value != -1) ? $value : null;
	}
	if($record = $DB->get_record(\block_exacomp\DB_EXAMPLEEVAL,array("studentid" => $userid, "courseid" => $courseid, "exampleid" => $exampleid))) {
		//if teacher keep studenteval
		if($role == \block_exacomp\ROLE_TEACHER) {
			$record->teacher_evaluation = $updateEvaluation->teacher_evaluation;
			$record->teacher_reviewerid = $updateEvaluation->teacher_reviewerid;
			$record->timestamp_teacher = $updateEvaluation->timestamp_teacher;
			$record->evalniveauid = $updateEvaluation->evalniveauid;
			$record->resubmission = $updateEvaluation->resubmission;

			$DB->update_record(\block_exacomp\DB_EXAMPLEEVAL,$record);
		} else {
			//if student keep teachereval
			$updateEvaluation->teacher_evaluation = $record->teacher_evaluation;
			$updateEvaluation->teacher_reviewerid = $record->teacher_reviewerid;
			$updateEvaluation->id = $record->id;
			$DB->update_record(\block_exacomp\DB_EXAMPLEEVAL,$updateEvaluation);
		}
		return $record->id;
	}
	else {
		$updateEvaluation->courseid = $courseid;
		$updateEvaluation->exampleid = $exampleid;
		$updateEvaluation->studentid = $userid;

		return $DB->insert_record(\block_exacomp\DB_EXAMPLEEVAL, $updateEvaluation);
	}

	// TODO: unreachable statement?!?
	if($role == \block_exacomp\ROLE_TEACHER)
		\block_exacomp\event\competence_assigned::log(['objectid' => $exampleid, 'courseid' => $courseid, 'relateduserid' => $userid]);
}
function block_exacomp_allow_resubmission($userid, $exampleid, $courseid) {
	global $DB;

	block_exacomp_require_teacher($courseid);

	$exameval = $DB->get_record(\block_exacomp\DB_EXAMPLEEVAL, array('courseid'=>$courseid,'studentid'=>$userid,'exampleid'=>$exampleid));
	if($exameval) {
		$exameval->resubmission = 1;
		$DB->update_record(\block_exacomp\DB_EXAMPLEEVAL, $exameval);
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

	if($record = $DB->get_record(\block_exacomp\DB_COMPETENCE_USER_MM, array("userid" => $userid, "compid" => $compid, "comptype" => $comptype, 'activityid'=>$activityid, "role" => $role))) {
		$record->value = $value;
		$record->timestamp = time();
		$DB->update_record(\block_exacomp\DB_COMPETENCE_USER_MM, $record);
	} else {
		$DB->insert_record(\block_exacomp\DB_COMPETENCE_USER_MM, array("userid" => $userid, "compid" => $compid, "comptype" => $comptype, 'activityid'=>$activityid, "role" => $role, "value" => $value, "reviewerid" => $USER->id, "timestamp" => time()));
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
function block_exacomp_save_competences($data, $courseid, $role, $comptype, $topicid = null, $subjectid = false) {
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
		block_exacomp_reset_comp_data($courseid, $role, $comptype, (($role == \block_exacomp\ROLE_STUDENT)) ? $USER->id : false, $topicid);
	else {
		$studentid = ($role == \block_exacomp\ROLE_STUDENT) ? $USER->id : required_param('studentid', PARAM_INT);
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
function block_exacomp_save_competences_activities_detail($data, $courseid, $role, $comptype) {
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
	block_exacomp_reset_comp_activity_data($courseid, $role, $comptype, (($role == \block_exacomp\ROLE_STUDENT)) ? $USER->id : false, $activityid);

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

	if($role == \block_exacomp\ROLE_TEACHER){
		foreach($activities as $activity)
			$DB->delete_records(\block_exacomp\DB_COMPETENCE_USER_MM, array("activityid" => $activity->id, "role" => $role, "comptype" => $comptype));
	}else{
		foreach($activities as $activity)
			$DB->delete_records(\block_exacomp\DB_COMPETENCE_USER_MM, array("activityid" => $activity->id, "role" => $role,  "comptype" => $comptype, "userid"=>$userid));
	}
}

/**
 * Delete timestamp for exampleid
 */
/*
function block_exacomp_delete_timefield($exampleid, $deletestart, $deleteent){
	global $DB, $USER;

	$updateid = $DB->get_field(\block_exacomp\DB_EXAMPLEEVAL, 'id', array('exampleid'=>$exampleid, 'studentid'=>$USER->id));
	$update = new stdClass();
	$update->id = $updateid;
	if($deletestart==1)
		$update->starttime = null;
	elseif($deleteend==1)
	$update->endtime = null;

	$DB->update_record(\block_exacomp\DB_EXAMPLEEVAL, $update);
}
*/

/**
 * Gets settings for the current course
 * @param int $courseid
 */
function block_exacomp_get_settings_by_course($courseid = 0) {
	if (!$courseid)
		$courseid = g::$COURSE->id;

	$settings = g::$DB->get_record(block_exacomp\DB_SETTINGS, array("courseid" => $courseid));

	if (!$settings) {
		$settings = new stdClass;
	}

	// actually this is a global setting now
	$settings->useprofoundness = get_config('exacomp', 'useprofoundness');

	/*if ($settings->useprofoundness) {
		$settings->grading = 2;
	} else*/
	if (empty($settings->grading)) {
		$settings->grading = 1;
	}
	if (empty($settings->nostudents)) $settings->nostudents = 0;
	$settings->work_with_students = !$settings->nostudents;

	if (!isset($settings->uses_activities)) $settings->uses_activities = block_exacomp_is_skillsmanagement() ? 0 : 1;
	if (!isset($settings->show_all_examples)) $settings->show_all_examples = block_exacomp_is_skillsmanagement() ? 1 : 0;
	if (!isset($settings->usedetailpage)) $settings->usedetailpage = 0;
	if (!$settings->uses_activities) {
		$settings->show_all_descriptors = 1;
	} elseif (!isset($settings->show_all_descriptors)) {
		$settings->show_all_descriptors = 0;
	}
	if (isset($settings->filteredtaxonomies)) {
		$settings->filteredtaxonomies = json_decode($settings->filteredtaxonomies,true);
	} else {
		$settings->filteredtaxonomies = array(SHOW_ALL_TAXONOMIES);
	}

	return $settings;
}

function block_exacomp_is_skillsmanagement() {
	return !empty(g::$CFG->is_skillsmanagement);
}
function block_exacomp_is_topicgrading_enabled() {
	return get_config('exacomp', 'usetopicgrading');
}
function block_exacomp_is_subjectgrading_enabled() {
	return get_config('exacomp', 'usesubjectgrading');
}
function block_exacomp_is_numbering_enabled() {
	return get_config('exacomp', 'usenumbering');
}
function block_exacomp_is_niveautitle_for_profile_enabled() {
	return get_config('exacomp', 'useniveautitleinprofile');
}

/**
 * Returns a list of descriptors from a particular course
 *
 * @param $courseid
 * @param $onlywithactivitys - to select only descriptors assigned to activities
 */
// not used anymore
/*
function block_exacomp_get_descritors_list($courseid, $onlywithactivitys = 0) {
	global $DB;

	$query = 'SELECT t.id as topdescrid, d.id,d.title,tp.title as topic_title,tp.id as topicid, s.title as subject,s.id as
	subjectid,d.niveauid
	FROM {'.\block_exacomp\DB_DESCRIPTORS.'} d,
	{'.\block_exacomp\DB_COURSETOPICS.'} c,
	{'.\block_exacomp\DB_DESCTOPICS.'} t,
	{'.\block_exacomp\DB_TOPICS.'} tp,
	{'.\block_exacomp\DB_SUBJECTS.'} s
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
*/
/**
 *
 * returns all descriptors
 * @param $courseid if course id =0 all possible descriptors are returned
 */
function block_exacomp_get_descriptors($courseid = 0, $showalldescriptors = false, $subjectid = 0, $showallexamples = true, $filteredtaxonomies = array(SHOW_ALL_TAXONOMIES), $showonlyvisible=false, $include_childs = true) {
	if (!$courseid) {
		$showalldescriptors = true;
		$showonlyvisible = false;
	}
	if(!$showalldescriptors)
		$showalldescriptors = block_exacomp_get_settings_by_course($courseid)->show_all_descriptors;


	$sql = 'SELECT DISTINCT desctopmm.id as u_id, d.id as id, d.title, d.source, d.niveauid, t.id AS topicid, d.profoundness, d.parentid, n.sorting AS niveau_sorting, n.title AS niveau_title, dvis.visible as visible, desctopmm.sorting '
	.' FROM {'.\block_exacomp\DB_TOPICS.'} t '
	.(($courseid>0)?' JOIN {'.\block_exacomp\DB_COURSETOPICS.'} topmm ON topmm.topicid=t.id AND topmm.courseid=? ' . (($subjectid > 0) ? ' AND t.subjid = '.$subjectid.' ' : '') :'')
	.' JOIN {'.\block_exacomp\DB_DESCTOPICS.'} desctopmm ON desctopmm.topicid=t.id '
	.' JOIN {'.\block_exacomp\DB_DESCRIPTORS.'} d ON desctopmm.descrid=d.id AND d.parentid=0 '
	.' -- left join, because courseid=0 has no descvisibility!
		LEFT JOIN {'.\block_exacomp\DB_DESCVISIBILITY.'} dvis ON dvis.descrid=d.id AND dvis.studentid=0 AND dvis.courseid=?'
	.($showonlyvisible?' AND dvis.visible = 1 ':'')
	.' LEFT JOIN {'.\block_exacomp\DB_NIVEAUS.'} n ON d.niveauid = n.id '
	.($showalldescriptors ? '' : '
			JOIN {'.\block_exacomp\DB_COMPETENCE_ACTIVITY.'} da ON d.id=da.compid AND da.comptype='.TYPE_DESCRIPTOR.'
			JOIN {course_modules} a ON da.activityid=a.id '.(($courseid>0)?'AND a.course=?':''));

	$descriptors = block_exacomp\descriptor::get_objects_sql($sql, array($courseid, $courseid, $courseid, $courseid));

	foreach($descriptors as $descriptor) {
		if($include_childs){
			//get examples
			$descriptor = block_exacomp_get_examples_for_descriptor($descriptor, $filteredtaxonomies, $showallexamples, $courseid);
			   //check for child-descriptors
			$descriptor->children = block_exacomp_get_child_descriptors($descriptor,$courseid, $showalldescriptors, $filteredtaxonomies, $showallexamples, true, $showonlyvisible);
		}
		//get categories
		$descriptor->categories = block_exacomp_get_categories_for_descriptor($descriptor);
	}

	return block_exacomp_sort_items($descriptors, ['niveau_' => \block_exacomp\DB_NIVEAUS, \block_exacomp\DB_DESCRIPTORS]);
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
		FROM {".\block_exacomp\DB_CATEGORIES."} c
		JOIN {".\block_exacomp\DB_DESCCAT."} dc ON dc.catid=c.id
		WHERE dc.descrid=?
		ORDER BY c.sorting
	", array($descriptor->id));

	return $categories;
}
function block_exacomp_get_child_descriptors($parent, $courseid, $showalldescriptors = false, $filteredtaxonomies = array(SHOW_ALL_TAXONOMIES), $showallexamples = true, $mindvisibility = true, $showonlyvisible=false ) {
	global $DB;

	if(!$DB->record_exists(\block_exacomp\DB_DESCRIPTORS, array("parentid" => $parent->id))) {
		return array();
	}

	if (!$courseid) {
		$showalldescriptors = true;
		$showonlyvisible = false;
		$mindvisibility = false;
	}
	if(!$showalldescriptors)
		$showalldescriptors = block_exacomp_get_settings_by_course($courseid)->show_all_descriptors;

	$sql = 'SELECT d.id, d.title, d.niveauid, d.source, '.$parent->topicid.' as topicid, d.profoundness, d.parentid, '.
			($mindvisibility?'dvis.visible as visible, ':'').' d.sorting
			FROM {'.\block_exacomp\DB_DESCRIPTORS.'} d '
			.($mindvisibility ? 'JOIN {'.\block_exacomp\DB_DESCVISIBILITY.'} dvis ON dvis.descrid=d.id AND dvis.courseid=? AND dvis.studentid=0 '
			.($showonlyvisible? 'AND dvis.visible=1 ':'') : '');

	/* activity association only for parent descriptors
			.($showalldescriptors ? '' : '
				JOIN {'.\block_exacomp\DB_COMPETENCE_ACTIVITY.'} da ON d.id=da.compid AND da.comptype='.TYPE_DESCRIPTOR.'
				JOIN {course_modules} a ON da.activityid=a.id '.(($courseid>0)?'AND a.course=?':''));
	*/
	$sql .= ' WHERE d.parentid = ?';

	$params = array();
	if($mindvisibility)
		$params[] = $courseid;

	$params[]= $parent->id;
	//$descriptors = $DB->get_records_sql($sql, ($showalldescriptors) ? array($parent->id) : array($courseid,$parent->id));
	$descriptors = block_exacomp\descriptor::get_objects_sql($sql, $params);

	foreach($descriptors as $descriptor) {
		$descriptor = block_exacomp_get_examples_for_descriptor($descriptor, $filteredtaxonomies, $showallexamples, $courseid);
		$descriptor->children = block_exacomp_get_child_descriptors($descriptor, $courseid,$showalldescriptors,$filteredtaxonomies);
		$descriptor->categories = block_exacomp_get_categories_for_descriptor($descriptor);
	}

	return block_exacomp_sort_items($descriptors, \block_exacomp\DB_DESCRIPTORS);
}

function block_exacomp_get_examples_for_descriptor($descriptor, $filteredtaxonomies = array(SHOW_ALL_TAXONOMIES),$showallexamples = true, $courseid = null, $mind_visibility=true, $showonlyvisible = false ) {
	global $COURSE;

	if($courseid == null)
		$courseid = $COURSE->id;

	$examples = \block_exacomp\example::get_objects_sql(
			"SELECT DISTINCT de.id as deid, e.id, e.title, e.externalurl, e.source, ".
                ($mind_visibility?"evis.visible,esvis.visible as solution_visible, ":"")."
				e.externalsolution, e.externaltask, e.completefile, e.description, e.creatorid, e.iseditable, e.tips, e.timeframe, e.author
				, de.sorting
				FROM {" . \block_exacomp\DB_EXAMPLES . "} e
				JOIN {" . \block_exacomp\DB_DESCEXAMP . "} de ON e.id=de.exampid AND de.descrid=?"
			.($mind_visibility?' JOIN {'.\block_exacomp\DB_EXAMPVISIBILITY.'} evis ON evis.exampleid= e.id AND evis.studentid=0 AND evis.courseid=? '
			.($showonlyvisible?' AND evis.visible = 1 ':''). ' JOIN {'.\block_exacomp\DB_SOLUTIONVISIBILITY.'} esvis ON esvis.exampleid= e.id AND esvis.studentid=0 AND esvis.courseid=? ' :'')
			. " WHERE "
			. " e.source != " . \block_exacomp\EXAMPLE_SOURCE_USER . " AND "
			. (($showallexamples) ? " 1=1 " : " e.creatorid > 0")
			. " ORDER BY de.sorting"
			, array($descriptor->id, $courseid, $courseid));

	foreach($examples as $example){
		$example->descriptor = $descriptor;
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
			if($example->taxonomies){
				foreach($example->taxonomies as $taxonomy){
					if(in_array($taxonomy->id, $filteredtaxonomies)){
						if(!array_key_exists($example->id, $filtered_examples))
							$filtered_examples[$example->id] = $example;
						continue;
					}
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
		FROM {".\block_exacomp\DB_TAXONOMIES."} tax
		JOIN {".\block_exacomp\DB_EXAMPTAX."} et ON tax.id = et.taxid
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

	$sql = '(SELECT DISTINCT d.id, desctopmm.id as u_id, d.title, d.niveauid, t.id AS topicid, d.requirement, d.knowledgecheck, d.benefit, d.sorting, d.parentid, n.title as cattitle '
	.'FROM {'.\block_exacomp\DB_TOPICS.'} t JOIN {'.\block_exacomp\DB_COURSETOPICS.'} topmm ON topmm.topicid=t.id AND topmm.courseid=? ' . (($topicid > 0) ? ' AND t.id = '.$topicid.' ' : '')
	.'JOIN {'.\block_exacomp\DB_DESCTOPICS.'} desctopmm ON desctopmm.topicid=t.id '
	.'JOIN {'.\block_exacomp\DB_DESCRIPTORS.'} d ON desctopmm.descrid=d.id AND d.parentid=0 '
	. 'LEFT JOIN {'.\block_exacomp\DB_NIVEAUS.'} n ON n.id = d.niveauid '
	.($mind_visibility?'JOIN {'.\block_exacomp\DB_DESCVISIBILITY.'} dvis ON dvis.descrid=d.id AND dvis.studentid=0 AND dvis.courseid=? '
	.($showonlyvisible?'AND dvis.visible = 1 ':''):'')
	.($showalldescriptors ? '' : '
			JOIN {'.\block_exacomp\DB_COMPETENCE_ACTIVITY.'} da ON d.id=da.compid AND da.comptype='.TYPE_DESCRIPTOR.'
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

	$sql = "SELECT d.*, dt.topicid, t.title as topic_title FROM {".\block_exacomp\DB_DESCRIPTORS."} d, {".\block_exacomp\DB_DESCTOPICS."} dt, {".\block_exacomp\DB_TOPICS."} t
	WHERE d.id=dt.descrid AND d.parentid =0 AND dt.topicid IN (SELECT id FROM {".\block_exacomp\DB_TOPICS."} WHERE subjid=?)";
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
		FROM {".\block_exacomp\DB_DESCRIPTORS."} d
		JOIN {".\block_exacomp\DB_DESCEXAMP."} de ON de.descrid=d.id
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
function block_exacomp_get_competence_tree($courseid = 0, $subjectid = null, $topicid = null, $showalldescriptors = false, $niveauid = null, $showallexamples = true, $filteredtaxonomies = array(SHOW_ALL_TAXONOMIES), $calledfromoverview = false, $calledfromactivities = false, $showonlyvisible=false, $without_descriptors=false, $showonlyvisibletopics = false, $include_childs = true) {
	global $DB;

	if(!$showalldescriptors)
		$showalldescriptors = block_exacomp_get_settings_by_course($courseid)->show_all_descriptors;

	$selectedTopic = null;
	if($topicid && $calledfromoverview) {
		$selectedTopic = $DB->get_record(\block_exacomp\DB_TOPICS,array('id'=>$topicid));
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
	$allTopics = block_exacomp_get_all_topics($subjectid, $showonlyvisible);
	if($courseid > 0) {
		if((!$calledfromoverview && !$calledfromactivities) || !$selectedTopic) {
			$courseTopics = block_exacomp_get_topics_by_subject($courseid, $subjectid, false, $showonlyvisibletopics);
		}
		elseif(isset($selectedTopic)) {
			$courseTopics = \block_exacomp\topic::get($selectedTopic->id);
			if (!$courseTopics) {
				$courseTopics = array();
			} else {
				$courseTopics = array($courseTopics->id => $courseTopics);
			}
		}
	}

	// 3. GET DESCRIPTORS
	if($without_descriptors)
		$allDescriptors = array();
	else
		$allDescriptors = block_exacomp_get_descriptors($courseid, $showalldescriptors,0,$showallexamples, $filteredtaxonomies, $showonlyvisible, $include_childs);

	foreach ($allDescriptors as $descriptor) {

		if($niveauid != block_exacomp\SHOW_ALL_NIVEAUS && $calledfromoverview)
			if($descriptor->niveauid != $niveauid)
				continue;

		// get descriptor topic
		if (empty($allTopics[$descriptor->topicid])) continue;
		$topic = $allTopics[$descriptor->topicid];
		$topic->descriptors[$descriptor->id] = $descriptor;
	}

	$subjects = array();

	foreach ($allSubjects as $subject) {
		$subject->topics = [];
	}

	foreach ($allTopics as $topic) {
		//topic must be coursetopic if courseid <> 0
		if ($courseid>0 && !array_key_exists($topic->id, $courseTopics))
			continue;

		// find subject
		if (empty($allSubjects[$topic->subjid])) {
			continue;
		}
		$subject = $allSubjects[$topic->subjid];
		if(!isset($topic->descriptors))
			$topic->descriptors = array();
		$topic = block_exacomp\topic::create($topic);

		// found: add it to the subject result
		$subject->topics[$topic->id] = $topic;
		$subjects[$subject->id] = $subject;
	}

	// sort topics
	foreach ($subjects as $subject) {
		block_exacomp_sort_items($subject->topics, \block_exacomp\DB_TOPICS);

		// sort descriptors in topics
		// not needed, because block_exacomp_get_descriptor() already sorts
		/*
		foreach ($subject->topics as $topic) {
			block_exacomp_sort_items($topic->descriptors, \block_exacomp\DB_DESCRIPTORS);
		}
		*/
	}


	return block_exacomp\subject::create_objects($subjects);
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
function block_exacomp_init_overview_data($courseid, $subjectid, $topicid, $niveauid, $editmode, $isTeacher=true, $studentid=0, $showonlyvisible=false) {
	$courseTopics = block_exacomp_get_topics_by_course($courseid, false, $showonlyvisible?(($isTeacher)?false:true):false);
	$courseSubjects = block_exacomp_get_subjects_by_course($courseid);

	$topic = new \stdClass();
	$topic->id = $topicid;
	
	$selectedSubject = null;
	$selectedTopic = null;
	if ($subjectid) {
		if (!empty($courseSubjects[$subjectid])) {
			$selectedSubject = $courseSubjects[$subjectid];

			$topics = block_exacomp_get_topics_by_subject($courseid, $selectedSubject->id, false, ($showonlyvisible?(($isTeacher)?false:true):false));
			if ($topicid == block_exacomp\SHOW_ALL_TOPICS) {
				// no $selectedTopic
			} elseif ($topicid && isset($topics[$topicid]) && block_exacomp_is_topic_visible($courseid, $topic, $studentid)) {
				$selectedTopic = $topics[$topicid];
			} else {
				// select first visible
				$visible_topics = block_exacomp_get_topic_visibilities_for_course_and_user($courseid, $studentid);
				$selectedTopic = $topics[reset($visible_topics)->id];
			}
		}
	}
	if (!$selectedSubject && $topicid) {
		if (isset($courseTopics[$topicid]) && block_exacomp_is_topic_visible($courseid, $topic, $studentid)) {
			$selectedTopic = $courseTopics[$topicid];
			$selectedSubject = $courseSubjects[$selectedTopic->subjid];
		}
	}
	if (!$selectedSubject) {
		// select the first subject
		$selectedSubject = reset($courseSubjects);
		$topics = block_exacomp_get_topics_by_subject($courseid, $selectedSubject->id, false, ($showonlyvisible?(($isTeacher)?false:true):false));
		$visible_topics = block_exacomp_get_topic_visibilities_for_course_and_user($courseid, $studentid);
		$selectedTopic = $topics[reset($visible_topics)->id];
	}

	// load all descriptors first (needed for teacher)
	if ($editmode) {
		$descriptors = block_exacomp_get_descriptors_by_topic ( $courseid, $selectedTopic ? $selectedTopic->id : null, true, false, false);
	} else {
		$descriptors = block_exacomp_get_descriptors_by_topic ( $courseid, $selectedTopic ? $selectedTopic->id : null, false, true, true);
	}

	if (!$selectedTopic) {
		// $descriptors contains all descriptors for this course, so filter it for just descriptors of selected subject
		foreach ($descriptors as $key=>$descriptor) {
			if (isset($courseTopics[$descriptor->topicid]) && ($courseTopics[$descriptor->topicid]->subjid == $selectedSubject->id)) {
				// OK
			} else {
				unset($descriptors[$key]);
			}
		}
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
	$niveaus = g::$DB->get_records_list(\block_exacomp\DB_NIVEAUS, 'id', $niveau_ids, 'sorting');
	$niveaus = \block_exacomp\niveau::create_objects($niveaus);

	$defaultNiveau = block_exacomp\niveau::create();
	$defaultNiveau->id = block_exacomp\SHOW_ALL_NIVEAUS;
	$defaultNiveau->title = block_exacomp\get_string('allniveaus');

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
	$user = block_exacomp_get_user_competences_by_course($user, $courseid);
	// get student topics
	$user = block_exacomp_get_user_topics_by_course($user, $courseid);
	// get student crosssubs
	$user = block_exacomp_get_user_crosssubs_by_course($user, $courseid);
	// get student subjects
	$user = block_exacomp_get_user_subjects_by_course($user, $courseid); 

	if(!$onlycomps){
		// get student examples
		$user->examples = block_exacomp_get_user_examples_by_course($user, $courseid);
		$activities = block_exacomp_get_activities_by_course($courseid);
		// get student activities topics
		$user = block_exacomp_get_user_activities_topics_by_course($user, $courseid, $activities);
		// get student activities competencies
		$user = block_exacomp_get_user_activities_competences_by_course($user, $courseid, $activities);
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
	$user->crosssubs->teacher = $DB->get_records_menu(\block_exacomp\DB_COMPETENCES,array("courseid" => $courseid, "userid" => $user->id, "role" => \block_exacomp\ROLE_TEACHER, "comptype" => TYPE_CROSSSUB),'','compid as id, value');
	$user->crosssubs->student = $DB->get_records_menu(\block_exacomp\DB_COMPETENCES,array("courseid" => $courseid, "userid" => $user->id, "role" => \block_exacomp\ROLE_STUDENT, "comptype" => TYPE_CROSSSUB),'','compid as id, value');
	$user->crosssubs->timestamp_teacher = $DB->get_records_menu(\block_exacomp\DB_COMPETENCES,array("courseid" => $courseid, "userid" => $user->id, "role" => \block_exacomp\ROLE_TEACHER, "comptype" => TYPE_CROSSSUB),'','compid as id, timestamp');
	$user->crosssubs->timestamp_student = $DB->get_records_menu(\block_exacomp\DB_COMPETENCES,array("courseid" => $courseid, "userid" => $user->id, "role" => \block_exacomp\ROLE_STUDENT, "comptype" => TYPE_CROSSSUB),'','compid as id, timestamp');
	$user->crosssubs->teacher_additional_grading = $DB->get_records_menu(\block_exacomp\DB_COMPETENCES,array("courseid" => $courseid, "userid" => $user->id, "role" => \block_exacomp\ROLE_TEACHER, "comptype" => TYPE_CROSSSUB),'','compid as id, additionalinfo');
	$user->crosssubs->niveau = $DB->get_records_menu(\block_exacomp\DB_COMPETENCES, array("courseid"=>$courseid, "userid"=>$user->id, "role" =>\block_exacomp\ROLE_TEACHER, "comptype" => TYPE_CROSSSUB),'', 'compid as id, evalniveauid');
	return $user;
}
/**
 * This method returns all user competencies for a particular user in the given course

 * @param stdClass $user
 * @param int $courseid
 * @return stdClass user
 */
function block_exacomp_get_user_competences_by_course($user, $courseid) {
	global $DB;

	$user->competencies = new stdClass();
	$user->competencies->teacher = $DB->get_records_menu(\block_exacomp\DB_COMPETENCES,array("courseid" => $courseid, "userid" => $user->id, "role" => \block_exacomp\ROLE_TEACHER, "comptype" => TYPE_DESCRIPTOR),'','compid as id, value');
	$user->competencies->student = $DB->get_records_menu(\block_exacomp\DB_COMPETENCES,array("courseid" => $courseid, "userid" => $user->id, "role" => \block_exacomp\ROLE_STUDENT, "comptype" => TYPE_DESCRIPTOR),'','compid as id, value');
	$user->competencies->timestamp_teacher = $DB->get_records_menu(\block_exacomp\DB_COMPETENCES,array("courseid" => $courseid, "userid" => $user->id, "role" => \block_exacomp\ROLE_TEACHER, "comptype" => TYPE_DESCRIPTOR),'','compid as id, timestamp');
	$user->competencies->timestamp_student = $DB->get_records_menu(\block_exacomp\DB_COMPETENCES,array("courseid" => $courseid, "userid" => $user->id, "role" => \block_exacomp\ROLE_STUDENT, "comptype" => TYPE_DESCRIPTOR),'','compid as id, timestamp');
	$user->competencies->teacher_additional_grading = $DB->get_records_menu(\block_exacomp\DB_COMPETENCES,array("courseid" => $courseid, "userid" => $user->id, "role" => \block_exacomp\ROLE_TEACHER, "comptype" => TYPE_DESCRIPTOR),'','compid as id, additionalinfo');
	$user->competencies->niveau = $DB->get_records_menu(\block_exacomp\DB_COMPETENCES, array("courseid"=>$courseid, "userid"=>$user->id, "role" =>\block_exacomp\ROLE_TEACHER, "comptype" => TYPE_DESCRIPTOR),'', 'compid as id, evalniveauid');
	
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
	$user->topics->teacher = $DB->get_records_menu(\block_exacomp\DB_COMPETENCES,array("courseid" => $courseid, "userid" => $user->id, "role" => \block_exacomp\ROLE_TEACHER, "comptype" => TYPE_TOPIC),'','compid as id, value');
	$user->topics->student = $DB->get_records_menu(\block_exacomp\DB_COMPETENCES,array("courseid" => $courseid, "userid" => $user->id, "role" => \block_exacomp\ROLE_STUDENT, "comptype" => TYPE_TOPIC),'','compid as id, value');
	$user->topics->timestamp_teacher = $DB->get_records_menu(\block_exacomp\DB_COMPETENCES,array("courseid" => $courseid, "userid" => $user->id, "role" => \block_exacomp\ROLE_TEACHER, "comptype" => TYPE_TOPIC),'','compid as id, timestamp');
	$user->topics->timestamp_student = $DB->get_records_menu(\block_exacomp\DB_COMPETENCES,array("courseid" => $courseid, "userid" => $user->id, "role" => \block_exacomp\ROLE_STUDENT, "comptype" => TYPE_TOPIC),'','compid as id, timestamp');
	$user->topics->teacher_additional_grading = $DB->get_records_menu(\block_exacomp\DB_COMPETENCES,array("courseid" => $courseid, "userid" => $user->id, "role" => \block_exacomp\ROLE_TEACHER, "comptype" => TYPE_TOPIC),'','compid as id, additionalinfo');
	$user->topics->niveau = $DB->get_records_menu(\block_exacomp\DB_COMPETENCES, array("courseid"=>$courseid, "userid"=>$user->id, "role" =>\block_exacomp\ROLE_TEACHER, "comptype" => TYPE_TOPIC),'', 'compid as id, evalniveauid');
	
	return $user;
}

/**
 *  This method returns all user subjects for a particular user in the given course
 *
 * @param stdClass $user
 * @param int $courseid
 * @return stdClass $user
 */
function block_exacomp_get_user_subjects_by_course($user, $courseid) {
	global $DB;

	$user->subjects = new stdClass();
	$user->subjects->teacher = $DB->get_records_menu(\block_exacomp\DB_COMPETENCES,array("courseid" => $courseid, "userid" => $user->id, "role" => \block_exacomp\ROLE_TEACHER, "comptype" => \block_exacomp\TYPE_SUBJECT),'','compid as id, value');
	$user->subjects->student = $DB->get_records_menu(\block_exacomp\DB_COMPETENCES,array("courseid" => $courseid, "userid" => $user->id, "role" => \block_exacomp\ROLE_STUDENT, "comptype" => \block_exacomp\TYPE_SUBJECT),'','compid as id, value');
	$user->subjects->timestamp_teacher = $DB->get_records_menu(\block_exacomp\DB_COMPETENCES,array("courseid" => $courseid, "userid" => $user->id, "role" => \block_exacomp\ROLE_TEACHER, "comptype" => \block_exacomp\TYPE_SUBJECT),'','compid as id, timestamp');
	$user->subjects->timestamp_student = $DB->get_records_menu(\block_exacomp\DB_COMPETENCES,array("courseid" => $courseid, "userid" => $user->id, "role" => \block_exacomp\ROLE_STUDENT, "comptype" => \block_exacomp\TYPE_SUBJECT),'','compid as id, timestamp');
	$user->subjects->teacher_additional_grading = $DB->get_records_menu(\block_exacomp\DB_COMPETENCES,array("courseid" => $courseid, "userid" => $user->id, "role" => \block_exacomp\ROLE_TEACHER, "comptype" => \block_exacomp\TYPE_SUBJECT),'','compid as id, additionalinfo');
	$user->subjects->niveau = $DB->get_records_menu(\block_exacomp\DB_COMPETENCES, array("courseid"=>$courseid, "userid"=>$user->id, "role" =>\block_exacomp\ROLE_TEACHER, "comptype" => \block_exacomp\TYPE_SUBJECT),'', 'compid as id, evalniveauid');

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
	$examples->teacher = g::$DB->get_records_menu(\block_exacomp\DB_EXAMPLEEVAL,array("courseid" => $courseid, "studentid" => $user->id),'','exampleid as id, teacher_evaluation as value');
	$examples->student = g::$DB->get_records_menu(\block_exacomp\DB_EXAMPLEEVAL,array("courseid" => $courseid, "studentid" => $user->id),'','exampleid as id, student_evaluation as value');
	$examples->niveau = g::$DB->get_records_menu(\block_exacomp\DB_EXAMPLEEVAL, array("courseid"=>$courseid, "studentid"=>$user->id),'', 'exampleid as id, evalniveauid');
	$examples->timestamp_teacher = g::$DB->get_records_menu(\block_exacomp\DB_EXAMPLEEVAL,array("courseid" => $courseid, "studentid" => $user->id),'','exampleid as id, timestamp_teacher as timestamp');
	$examples->timestamp_student = g::$DB->get_records_menu(\block_exacomp\DB_EXAMPLEEVAL,array("courseid" => $courseid, "studentid" => $user->id),'','exampleid as id, timestamp_student as timestamp');
	
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

		$user->activities_topics->activities[$activity->id]->teacher += $DB->get_records_menu(\block_exacomp\DB_COMPETENCE_USER_MM,array("activityid" => $activity->id, "userid" => $user->id, "role" => \block_exacomp\ROLE_TEACHER, "comptype" => TYPE_TOPIC),'','compid as id, value');
		$user->activities_topics->activities[$activity->id]->student += $DB->get_records_menu(\block_exacomp\DB_COMPETENCE_USER_MM,array("activityid" => $activity->id, "userid" => $user->id, "role" => \block_exacomp\ROLE_STUDENT, "comptype" => TYPE_TOPIC),'','compid as id, value');
	}

	return $user;
}
/**
 *  This method returns all competencies for the detailed view for a given user
 *
 * @param object $user
 * @param int $courseid
 */
function block_exacomp_get_user_activities_competences_by_course($user, $courseid, $activities){
	global $DB;

	$user->activities_competencies = new stdClass();
	$user->activities_competencies->activities = array();

	foreach($activities as $activity){
		$user->activities_competencies->activities[$activity->id] = new stdClass();

		$user->activities_competencies->activities[$activity->id]->teacher = array();
		$user->activities_competencies->activities[$activity->id]->student = array();
		$user->activities_competencies->activities[$activity->id]->teacher += $DB->get_records_menu(\block_exacomp\DB_COMPETENCE_USER_MM,array("activityid" => $activity->id, "userid" => $user->id, "role" => \block_exacomp\ROLE_TEACHER, "comptype" => TYPE_DESCRIPTOR, "eportfolioitem"=>0),'','compid as id, value');
		$user->activities_competencies->activities[$activity->id]->student += $DB->get_records_menu(\block_exacomp\DB_COMPETENCE_USER_MM,array("activityid" => $activity->id, "userid" => $user->id, "role" => \block_exacomp\ROLE_STUDENT, "comptype" => TYPE_DESCRIPTOR, "eportfolioitem"=>0),'','compid as id, value');
	}

	return $user;
}
function block_exacomp_build_navigation_tabs_settings($courseid){
	global $usebadges;
	$courseSettings = block_exacomp_get_settings_by_course($courseid);
	$settings_subtree = array();

	$settings_subtree[] = new tabobject('tab_teacher_settings_configuration', new moodle_url('/blocks/exacomp/edit_course.php', array('courseid'=>$courseid)), get_string("tab_teacher_settings_configuration", "block_exacomp"), null, true);
	$settings_subtree[] = new tabobject('tab_teacher_settings_selection', new moodle_url('/blocks/exacomp/courseselection.php', array('courseid'=>$courseid)), get_string("tab_teacher_settings_selection", "block_exacomp"), null, true);

	if (block_exacomp_is_activated($courseid))
		if ($courseSettings->uses_activities)
			$settings_subtree[] = new tabobject('tab_teacher_settings_assignactivities', new moodle_url('/blocks/exacomp/edit_activities.php', array('courseid'=>$courseid)), get_string("tab_teacher_settings_assignactivities", "block_exacomp"), null, true);

	if (block_exacomp_moodle_badges_enabled() && $usebadges) {
		$settings_subtree[] = new tabobject('tab_teacher_settings_badges', new moodle_url('/blocks/exacomp/edit_badges.php', array('courseid'=>$courseid)), get_string("tab_teacher_settings_badges", "block_exacomp"), null, true);
	}

	return $settings_subtree;
}
function block_exacomp_build_navigation_tabs_admin_settings($courseid){
	$checkImport = block_exacomp\data::has_data();

	$settings_subtree = array();

	if ($checkImport && has_capability('block/exacomp:admin', context_system::instance()))
		$settings_subtree[] = new tabobject('tab_admin_configuration', new moodle_url('/blocks/exacomp/edit_config.php',array("courseid"=>$courseid)),get_string('tab_admin_configuration','block_exacomp'), null, true);

	$settings_subtree[] = new tabobject('tab_admin_import', new moodle_url('/blocks/exacomp/import.php', array('courseid'=>$courseid)), get_string("tab_admin_import", "block_exacomp"), null, true);

	if (get_config('exacomp','external_trainer_assign') && has_capability('block/exacomp:assignstudents', context_system::instance())) {
		$settings_subtree[] = new tabobject('tab_external_trainer_assign', new moodle_url('/blocks/exacomp/externaltrainers.php', array('courseid'=>$courseid)), get_string("block_exacomp_external_trainer_assign", "block_exacomp"), null, true);
	}
	$settings_subtree[] = new tabobject('tab_webservice_status', new moodle_url('/blocks/exacomp/webservice_status.php', array('courseid'=>$courseid)), block_exacomp\trans(['de:Webservice Status', 'en:Check Webservices']), null, true);

	return $settings_subtree;
}
function block_exacomp_build_navigation_tabs_profile($context,$courseid){
	if (block_exacomp_is_teacher($context))
		return array();

	$profile_subtree = array();

	$profile_subtree[] = new tabobject('tab_competence_profile_profile', new moodle_url('/blocks/exacomp/competence_profile.php', array("courseid"=>$courseid)), get_string('tab_competence_profile_profile', 'block_exacomp'), null, true);
	$profile_subtree[] = new tabobject('tab_competence_profile_settings', new moodle_url('/blocks/exacomp/competence_profile_settings.php', array("courseid"=>$courseid)), get_string('tab_competence_profile_settings', 'block_exacomp'), null, true);
	return $profile_subtree;
}
/*function block_exacomp_build_navigation_tabs_cross_subjects($context,$courseid){
	if (!block_exacomp_is_teacher($context))
		return array();

	$crosssubs = block_exacomp_get_cross_subjects_by_course($courseid);

	$profile_subtree = array();

	$profile_subtree[] = new tabobject('tab_cross_subjects_overview', new moodle_url('/blocks/exacomp/cross_subjects_overview.php', array("courseid"=>$courseid)), get_string('tab_cross_subjects_overview', 'block_exacomp'), null, true);
	if($crosssubs)
		$profile_subtree[] = new tabobject('tab_cross_subjects_course', new moodle_url('/blocks/exacomp/cross_subjects.php', array("courseid"=>$courseid)), get_string('tab_cross_subjects_course', 'block_exacomp'), null, true);
	return $profile_subtree;
}*/
/**
 * Build navigtion tabs, depending on role and version
 *
 * @param object $context
 * @param int $courseid
 */
function block_exacomp_build_navigation_tabs($context,$courseid) {
	global $USER;

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

	$has_data = \block_exacomp\data::has_data();

	$rows = array();

	$isTeacher = block_exacomp_is_teacher($context) && $courseid != 1;
	$isStudent = has_capability('block/exacomp:student', $context) && $courseid != 1 && !has_capability('block/exacomp:admin', $context);
	$isTeacherOrStudent = $isTeacher || $isStudent;

	if($checkConfig && $has_data){	//Modul wurde konfiguriert
		if ($isTeacherOrStudent && block_exacomp_is_activated($courseid)) {
			//Kompetenzraster
			$rows[] = new tabobject('tab_competence_grid', new moodle_url('/blocks/exacomp/competence_grid.php',array("courseid"=>$courseid)),get_string('tab_competence_grid','block_exacomp'), null, true);
		}
		if ($isTeacherOrStudent && $ready_for_use) {
			//Kompetenzüberblick
			$rows[] = new tabobject('tab_competence_overview', new moodle_url('/blocks/exacomp/assign_competencies.php',array("courseid"=>$courseid)),get_string('tab_competence_overview','block_exacomp'), null, true);

			if ($isTeacher || (block_exacomp_cross_subjects_exists() && block_exacomp_get_cross_subjects_by_course($courseid, $USER->id))) {
				// Cross subjects: always for teacher and for students if it there are cross subjects
				$rows[] = new tabobject('tab_cross_subjects', new moodle_url('/blocks/exacomp/cross_subjects_overview.php', array("courseid"=>$courseid)), get_string('tab_cross_subjects', 'block_exacomp'), null, true);
			}

			if (!$courseSettings->nostudents) {
				//Kompetenz-Detailansicht nur wenn mit Aktivitäten gearbeitet wird
				if ($courseSettings->uses_activities && $courseSettings->usedetailpage) {
					$rows[] = new tabobject('tab_competence_details', new moodle_url('/blocks/exacomp/competence_detail.php',array("courseid"=>$courseid)),get_string('tab_competence_details','block_exacomp'), null, true);
				}

				//Kompetenzprofil
				$rows[] = new tabobject('tab_competence_profile_profile', new moodle_url('/blocks/exacomp/competence_profile.php', array("courseid"=>$courseid)), get_string('tab_competence_profile',  'block_exacomp'), null, true);
			}

			if (!$courseSettings->nostudents) {
				//Beispiel-Aufgaben
				$rows[] = new tabobject('tab_examples', new moodle_url('/blocks/exacomp/view_examples.php',array("courseid"=>$courseid)),get_string('tab_examples','block_exacomp'), null, true);

				//Lernagenda
				//$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/learningagenda.php', array('courseid'=>$courseid)), get_string('tab_learning_agenda', 'block_exacomp'), array('title'=>get_string('tab_learning_agenda', 'block_exacomp')));
				//$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/subject.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
			}

			if (!$courseSettings->nostudents) {
				//Wochenplan
				$rows[] = new tabobject('tab_weekly_schedule', new moodle_url('/blocks/exacomp/weekly_schedule.php',array("courseid"=>$courseid)),get_string('tab_weekly_schedule','block_exacomp'), null, true);
			}

			if ($isTeacher && !$courseSettings->nostudents) {
				if ($courseSettings->useprofoundness) {
					$rows[] = new tabobject('tab_profoundness', new moodle_url('/blocks/exacomp/profoundness.php',array("courseid"=>$courseid)),get_string('tab_profoundness','block_exacomp'), null, true);
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
			$rows[] = new tabobject('tab_teacher_settings', new moodle_url('/blocks/exacomp/edit_course.php',array("courseid"=>$courseid)),get_string('tab_teacher_settings','block_exacomp'), null, true);
		}
	}

	//if has_data && checkSubjects -> Modul wurde konfiguriert
	//else nur admin sieht block und hat nur den link Modulkonfiguration
	if (is_siteadmin() || (has_capability('block/exacomp:admin', $globalcontext) && !block_exacomp_is_skillsmanagement())) {
		//Admin sieht immer Modulkonfiguration
		//Wenn Import schon erledigt, weiterleitung zu edit_config, ansonsten import.
		if($has_data){
			$rows[] = new tabobject('tab_admin_settings', new moodle_url('/blocks/exacomp/edit_config.php',array("courseid"=>$courseid)),get_string('tab_admin_settings','block_exacomp'), null, true);
		}
	}

	if ($de && !block_exacomp_is_skillsmanagement()) {
		//Hilfe
		$rows[] = new tabobject('tab_help', new moodle_url('/blocks/exacomp/help.php', array("courseid"=>$courseid)), get_string('tab_help', 'block_exacomp'), null, true);
	}

	return $rows;
}

function block_exacomp_build_breadcrum_navigation($courseid) {
	global $PAGE;
	$coursenode = $PAGE->navigation->find($courseid, navigation_node::TYPE_COURSE);
	$blocknode = $coursenode->add(get_string('blocktitle','block_exacomp'));
	$blocknode->make_active();
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
	return $DB->get_records(\block_exacomp\DB_EDULEVELS,null,'source');
}
/**
 *
 * Get schooltypes for particular education level
 * @param unknown_type $edulevel
 */
function block_exacomp_get_schooltypes($edulevel) {
	global $DB;

	return $DB->get_records(\block_exacomp\DB_SCHOOLTYPES, array("elid" => $edulevel));
}
/**
 * Gets a subject's schooltype title
 *
 * @param object $subject
 * @return Ambigous <mixed, boolean>
 */
function block_exacomp_get_schooltype_title_by_subject($subject){
	global $DB;
	$subject = $DB->get_record(\block_exacomp\DB_SUBJECTS, array('id'=>$subject->id));
	if ($subject) return $DB->get_field(\block_exacomp\DB_SCHOOLTYPES, "title", array("id"=>$subject->stid));

}
/**
 * Get a schooltype by subject
 *
 * @param unknown_type $subject
 */
function block_exacomp_get_schooltype_by_subject($subject){
	global $DB;
	return $DB->get_record(\block_exacomp\DB_SCHOOLTYPES, array("id"=>$subject->stid));
}
/**
 * Gets a topic's category
 *
 * @param object $topic
 */
function block_exacomp_get_category($topic){
	global $DB;
	if(isset($topic->catid))
		return $DB->get_record(\block_exacomp\DB_CATEGORIES,array("id"=>$topic->catid));
}
/**
 * Gets assigned schooltypes for particular courseid
 *
 * @param int $typeid
 * @param int $courseid
 */
function block_exacomp_get_mdltypes($typeid, $courseid = 0) {
	global $DB;

	return $DB->get_record(\block_exacomp\DB_MDLTYPES, array("stid" => $typeid, "courseid" => $courseid));
}
/**
 *
 * Assign a schooltype to a course
 * @param unknown_type $values
 * @param unknown_type $courseid
 */
function block_exacomp_set_mdltype($values, $courseid = 0) {
	global $DB;

	$DB->delete_records(\block_exacomp\DB_MDLTYPES,array("courseid"=>$courseid));
	foreach ($values as $value) {
		$DB->insert_record(\block_exacomp\DB_MDLTYPES, array("stid" => intval($value),"courseid" => $courseid));
	}

	block_exacomp_clean_course_topics($values, $courseid);
}

function block_exacomp_clean_course_topics($values, $courseid){
	global $DB;

	if($courseid == 0)
		// TODO: ist das korrekt so? sollte man nicht courseid=0 auslesen?
		$coutopics = $DB->get_records(\block_exacomp\DB_COURSETOPICS);
	else
		$coutopics = $DB->get_records(\block_exacomp\DB_COURSETOPICS, array('courseid'=>$courseid));

	foreach($coutopics as $coutopic){
		$sql = 'SELECT s.stid FROM {'.\block_exacomp\DB_TOPICS.'} t
			JOIN {'.\block_exacomp\DB_SUBJECTS.'} s ON t.subjid=s.id
			WHERE t.id=?';

		$schooltype = $DB->get_record_sql($sql, array($coutopic->topicid));

		if($schooltype && !array_key_exists($schooltype->stid, $values)){
			$DB->delete_records(\block_exacomp\DB_COURSETOPICS, array('id'=>$coutopic->id));
		}
	}
}
/**
 * check if configuration is already finished
 * configuration is finished if schooltype is selected for course(LIS)/moodle(normal)
 */
function block_exacomp_is_configured($courseid=0){
	global $DB;
	return $DB->get_records(\block_exacomp\DB_MDLTYPES, array("courseid"=>$courseid));
}
/**
 *
 * Check if moodle version is supporting badges
 */
function block_exacomp_moodle_badges_enabled() {
	// moodle has badges since version 2.5
	return true;
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

	$DB->delete_records(\block_exacomp\DB_SETTINGS, array("courseid" => $courseid));

	if ($settings->grading > \block_exacomp\SETTINGS_MAX_SCHEME) $settings->grading = \block_exacomp\SETTINGS_MAX_SCHEME;

	//adapt old evaluation to new scheme
	//update compcompuser && compcompuser_mm && exameval
	if($old_course_settings->grading != $settings->grading){
		//block_exacompcompuser
		$records = $DB->get_records(\block_exacomp\DB_COMPETENCES, array('courseid'=>$courseid));
		foreach($records as $record){
			//if value is set and greater than zero->adapt to new scheme
			if(isset($record->value) && $record->value > 0){
				//calculate old percentage and apply it to new scheme
				$percent_old = $record->value / $old_course_settings->grading;
				$value_new = round($settings->grading * $percent_old);

				$update = new stdClass();
				$update->id = $record->id;
				$update->value = $value_new;
				$DB->update_record(\block_exacomp\DB_COMPETENCES, $update);

			}
		}

		//block_exacompcompuser_mm
		$records = $DB->get_records_sql('
			SELECT comp.id, comp.value
			FROM {'.\block_exacomp\DB_COMPETENCE_USER_MM.'} comp
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
				$DB->update_record(\block_exacomp\DB_COMPETENCE_USER_MM, $update);
			}
		}

		//block_exacompexampeval
		$records = $DB->get_records(\block_exacomp\DB_EXAMPLEEVAL, array('courseid'=>$courseid));
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
				$DB->update_record(\block_exacomp\DB_EXAMPLEEVAL, $update);
		}

	}

	$settings->courseid = $courseid;
	$settings->tstamp = time();

	$DB->insert_record(\block_exacomp\DB_SETTINGS, $settings);
}
/**
 *
 * Check if there are already topics assigned to a course
 * @param int $courseid
 */
function block_exacomp_is_activated($courseid) {
	global $DB;

	return $DB->get_records(\block_exacomp\DB_COURSETOPICS, array("courseid" => $courseid));
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
	$activities_assigned_to_any_course = $DB->get_records(\block_exacomp\DB_COMPETENCE_ACTIVITY, array('eportfolioitem'=>0));
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
 * @param int $courseid
 */
function block_exacomp_get_grading_scheme($courseid) {
	$settings = block_exacomp_get_settings_by_course($courseid);
	return $settings->grading;
}
/**
 *
 * Builds topic title to print
 * @param stdClass $topic
 */
function block_exacomp_get_output_fields($topic) {
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
				FROM {'.\block_exacomp\DB_DESCRIPTORS.'} d
				JOIN {'.\block_exacomp\DB_DESCBADGE.'} db ON d.id=db.descid AND db.badgeid=?
				', array($badge->id));

		// no descriptors selected?
		if (empty($descriptors)) continue;
		foreach ($users as $user) {
			if ($badge->is_issued($user->id)) {
				// skip, already issued
				continue;
			}

			$usercompetences_all = block_exacomp_get_user_competences_by_course($user, $courseid);
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
				FROM {'.\block_exacomp\DB_DESCRIPTORS.'} d
				JOIN {'.\block_exacomp\DB_DESCBADGE.'} db ON d.id=db.descid AND db.badgeid=?
				', array($badge->id));

		// no descriptors selected?
		if (empty($descriptors)) continue;

		$badge->descriptorStatus = array();

		$user = $DB->get_record('user', array('id'=>$userid));
		$usercompetences_all = block_exacomp_get_user_competences_by_course($user, $courseid);
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
 * Gets all descriptors assigned to a badge
 * @param unknown_type $badgeid
 */
function block_exacomp_get_badge_descriptors($badgeid){
	global $DB;
	return $DB->get_records_sql('
			SELECT d.*
			FROM {'.\block_exacomp\DB_DESCRIPTORS.'} d
			JOIN {'.\block_exacomp\DB_DESCBADGE.'} db ON d.id=db.descid AND db.badgeid=?
			', array($badgeid));
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
			FROM {'.\block_exacomp\DB_COMPETENCE_ACTIVITY.'} mm
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
			FROM {'.\block_exacomp\DB_COMPETENCE_ACTIVITY.'} mm
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

	$DB->delete_records(\block_exacomp\DB_COURSETOPICS, array("courseid" => $courseid));

	block_exacomp_update_topic_visibilities($courseid, $topicids);
	
	$descriptors = array();
	$examples = array();
	foreach ($topicids as $topicid) {
		$DB->insert_record(\block_exacomp\DB_COURSETOPICS, array("courseid" => $courseid, "topicid" => $topicid));

		//insert descriptors in block_exacompdescrvisibility
		$descriptors_topic = block_exacomp_get_descriptors_by_topic($courseid, $topicid, true);
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

	// TODO: maybe move this whole part to block_exacomp\data::normalize_database() or better a new normalize_course($courseid);

	//delete unconnected examples
	//add blocking events to examples which are not deleted
	$blocking_events = $DB->get_records(\block_exacomp\DB_EXAMPLES, array('blocking_event'=>1));

	foreach($blocking_events as $event){
		$examples[$event->id] = $event;
	}

	$where = $examples ? join(',', array_keys($examples)) : '-1';
	$DB->execute("DELETE FROM {".\block_exacomp\DB_SCHEDULE."} WHERE courseid = ? AND exampleid NOT IN($where)", array($courseid));
}

/**
 *
 * given descriptor list is visible in cour
 * @param unknown_type $courseid
 * @param unknown_type $descriptors
 */
function block_exacomp_update_descriptor_visibilities($courseid, $descriptors){
	global $DB;

	$visibilities = $DB->get_fieldset_select(\block_exacomp\DB_DESCVISIBILITY,'descrid', 'courseid=? AND studentid=0', array($courseid));

	//get all cross subject descriptors - to support cross-course subjects descriptor visibility must be kept
	$cross_subjects = $DB->get_records(\block_exacomp\DB_CROSSSUBJECTS, array('courseid'=>$courseid));
	$cross_subjects_descriptors = array();

	foreach($cross_subjects as $crosssub){
		$cross_subject_descriptors = $DB->get_fieldset_select(\block_exacomp\DB_DESCCROSS, 'descrid', 'crosssubjid=?', array($crosssub->id));
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
			$DB->insert_record(\block_exacomp\DB_DESCVISIBILITY, array("courseid"=>$courseid, "descrid"=>$descriptor->id, "studentid"=>0, "visible"=>1));
		}

		$descriptor->children = block_exacomp_get_child_descriptors($descriptor, $courseid, true, array(SHOW_ALL_TAXONOMIES), true, false);

		foreach($descriptor->children as $childdescriptor){
			if(!in_array($childdescriptor->id, $visibilities)) {
				$visibilities[] = $childdescriptor->id;
				$DB->insert_record(\block_exacomp\DB_DESCVISIBILITY, array("courseid"=>$courseid, "descrid"=>$childdescriptor->id, "studentid"=>0, "visible"=>1));
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
				$DB->delete_records(\block_exacomp\DB_DESCVISIBILITY, array("courseid"=>$courseid, "descrid"=>$visible));
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

	$visibilities = $DB->get_fieldset_select(\block_exacomp\DB_EXAMPVISIBILITY,'exampleid', 'courseid=? AND studentid=0', array($courseid));

	//get all cross subject examples - to support cross-course subjects exampels visibility must be kept
	$cross_subjects = $DB->get_records(\block_exacomp\DB_CROSSSUBJECTS, array('courseid'=>$courseid));
	$cross_subject_examples = array();

	foreach($cross_subjects as $crosssub){
		$cross_subject_descriptors = $DB->get_fieldset_select(\block_exacomp\DB_DESCCROSS, 'descrid', 'crosssubjid=?', array($crosssub->id));
		foreach($cross_subject_descriptors as $descriptor){
			$descriptor = $DB->get_record(\block_exacomp\DB_DESCRIPTORS, array('id'=>$descriptor));
			$descriptor = block_exacomp_get_examples_for_descriptor($descriptor, array(SHOW_ALL_TAXONOMIES), true, $courseid, false);
			foreach($descriptor->examples as $example)
				if(!in_array($example->id, $cross_subject_examples))
					$cross_subject_examples[] = $example->id;

			if($descriptor->parentid == 0){
				$descriptor_topic_mm = $DB->get_record(\block_exacomp\DB_DESCTOPICS, array('descrid'=>$descriptor->id));
				if($descriptor_topic_mm) {
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
	}

	$finalexamples = $examples;
	//manage visibility, do not delete user visibility, but delete unused entries
	foreach($examples as $example){
		//new example in table
		if(!in_array($example->id, $visibilities)) {
			$visibilities[] = $example->id;
			$DB->insert_record(\block_exacomp\DB_EXAMPVISIBILITY, array("courseid"=>$courseid, "exampleid"=>$example->id, "studentid"=>0, "visible"=>1));
			$DB->insert_record(\block_exacomp\DB_SOLUTIONVISIBILITY, array("courseid"=>$courseid, "exampleid"=>$example->id, "studentid"=>0, "visible"=>1));
		}
	}

	foreach($visibilities as $visible){
		//delete ununsed descriptors for course and for special students
		if(!array_key_exists($visible, $finalexamples)){
			//check if used in cross-subjects --> then it must still be visible
			if(!in_array($visible, $cross_subject_examples)) {
                $DB->delete_records(\block_exacomp\DB_EXAMPVISIBILITY, array("courseid"=>$courseid, "exampleid"=>$visible));
                $DB->delete_records(\block_exacomp\DB_SOLUTIONVISIBILITY, array("courseid"=>$courseid, "exampleid"=>$visible));
            }
		}
	}
}

function block_exacomp_update_topic_visibilities($courseid, $topicids){
	global $DB;
	
	$visibilities = $DB->get_fieldset_select(\block_exacomp\DB_TOPICVISIBILITY,'topicid', 'courseid=? AND studentid=0', array($courseid));
	
	//manage visibility, do not delete user visibility, but delete unused entries
	foreach($topicids as $topicid){
		//new descriptors in table
		if(!in_array($topicid, $visibilities)) {
			$visibilities[] = $topicid;
			$DB->insert_record(\block_exacomp\DB_TOPICVISIBILITY, array("courseid"=>$courseid, "topicid"=>$topicid, "studentid"=>0, "visible"=>1));
		}
	}
	
	foreach($visibilities as $visible){
		//delete ununsed descriptors for course and for special students
		if(!in_array($visible, $topicids)){
			//check if used in cross-subjects --> then it must still be visible
			$DB->delete_records(\block_exacomp\DB_TOPICVISIBILITY, array("courseid"=>$courseid, "topicid"=>$visible));
		}
	}
	
}

//TODO this can be done easier
/*function block_exacomp_get_active_topics($tree, $courseid){
	$active_topics = block_exacomp_get_topics_by_course($courseid);
	foreach($tree as $subject){
		block_exacomp_get_active_topics_rec($subject->topics, $active_topics);
	}
	return $tree;
}*/
//TODO this can be done easier
/*function block_exacomp_get_active_topics_rec($subs, $active_topics){
	foreach($subs as $topic){
		if(isset($active_topics[$topic->id])){
			$topic->checked = true;
		}else{
			$topic->checked = false;
		}
		/*
		if(!empty($topic->subs)){
			block_exacomp_get_active_topics_rec($topic->subs, $topics);
		}
		*//*
	}
}*/
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
		$test->descriptors = $DB->get_records(\block_exacomp\DB_COMPETENCE_ACTIVITY, array('activityid'=>$test->activityid, 'comptype'=>TYPE_DESCRIPTOR), null, 'compid');
		$test->topics = $DB->get_records(\block_exacomp\DB_COMPETENCE_ACTIVITY, array('activityid'=>$test->activityid, 'comptype'=>TYPE_TOPIC), null, 'compid');
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
function block_exacomp_save_competences_activities($data, $courseid, $comptype) {
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

	$DB->delete_records(\block_exacomp\DB_COMPETENCE_ACTIVITY, array("activityid" => $activityid, "compid" => $compid, "comptype"=>$comptype, "eportfolioitem"=>0));
	$DB->insert_record(\block_exacomp\DB_COMPETENCE_ACTIVITY, array("activityid" => $activityid, "compid" => $compid, "comptype"=>$comptype, "coursetitle"=>$COURSE->shortname, 'activitytitle'=>$instance->name));
}
/**
 *
 * Delete competence, activity associations
 */
function block_exacomp_delete_competences_activities(){
	global $COURSE, $DB;

	$cmodules = $DB->get_records('course_modules', array('course'=>$COURSE->id));

	foreach($cmodules as $cm){
		$DB->delete_records(\block_exacomp\DB_COMPETENCE_ACTIVITY, array('activityid'=>$cm->id, 'eportfolioitem'=>0));
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
	FROM {'.\block_exacomp\DB_COMPETENCE_ACTIVITY.'} mm
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
	$query = 'SELECT DISTINCT mm.activityid as id, mm.activitytitle as title FROM {'.\block_exacomp\DB_COMPETENCE_ACTIVITY.'} mm
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
			$competencies = array("studentcomps"=>$DB->get_records(\block_exacomp\DB_COMPETENCES,array("role"=>\block_exacomp\ROLE_STUDENT,"courseid"=>$courseid,"userid"=>$studentid,"comptype"=>TYPE_DESCRIPTOR),"","compid,userid,reviewerid,value"),
					"teachercomps"=>$DB->get_records(\block_exacomp\DB_COMPETENCES,array("role"=>\block_exacomp\ROLE_TEACHER,"courseid"=>$courseid,"userid"=>$studentid,"comptype"=>TYPE_DESCRIPTOR),"","compid,userid,reviewerid,value,evalniveauid"));

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
					FROM {" . \block_exacomp\DB_EXAMPLES . "} e
					JOIN {" . \block_exacomp\DB_DESCEXAMP . "} de ON e.id=de.exampid
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
			$topics[$descriptor->topicid] = $descriptor->topic_title;
		}

		$selection = $DB->get_records(\block_exacomp\DB_COURSETOPICS,array('courseid'=>$courseid),'','topicid');

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
/*function block_exacomp_get_examples_LIS_student($subjects){
	$examples = array();
	foreach($subjects as $subject){
		block_exacomp_get_examples_LIS_student_topics($subject->topics, $examples);
	}
	return $examples;

}*/
/**
 *
 * Helper function to extract examples from subject tree for LIS student view
 * @param unknown_type $subs
 * @param unknown_type $examples
 */
/*function block_exacomp_get_examples_LIS_student_topics($topics, &$examples){
	foreach($topics as $topic){
		/*
		if(isset($topic->subs))
			block_exacomp_get_examples_LIS_student_topics($subs, $examples);
		*//*

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
}*/
function block_exacomp_extract_niveaus($subject_tree){
	$niveaus = array();

	foreach($subject_tree as $subject){
		block_exacomp_extract_niveaus_topics($subject->topics, $niveaus);
	}
	return $niveaus;
}
function block_exacomp_extract_niveaus_topics($subs, &$niveaus){
	global $DB;
	foreach ($subs as $topic){
		/*
		if(isset($topic->subs))
			block_exacomp_extract_niveaus_topics($topic->subs, $niveaus);
		*/

		if(isset($topic->descriptors)){
			foreach($topic->descriptors as $descriptor){
				if($descriptor->niveauid > 0){
					if(!isset($niveaus[$descriptor->niveauid]))
						$niveaus[$descriptor->niveauid] = $DB->get_record(\block_exacomp\DB_NIVEAUS, array('id'=>$descriptor->niveauid));
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
			$subject_has_niveaus = block_exacomp_filter_niveaus_topics($subject->topics, $niveaus);

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
	// $sub_topics_have_niveaus = false;
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
		/*
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
		*/
		if (!$topic_has_niveaus)
			unset($subs[$topic->id]);
	}
	return $sub_has_niveaus;
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
function block_exacomp_get_exacomp_courses($userid) {
	$all_exacomp_courses = block_exacomp_get_courseids();
	$user_courses = [];

	//get all courses where exacomp is installed
	foreach ($all_exacomp_courses as $course) {
		$context = context_course::instance($course);

		// only active courses where user is enrolled
		if(is_enrolled($context, $userid, '', true) && has_capability('block/exacomp:use', $context, $userid)){
			$user_courses[$course] = g::$DB->get_record('course', array('id'=>$course));
		}
	}

	return $user_courses;
}

function block_exacomp_get_teacher_courses($userid) {
	$courses = block_exacomp_get_exacomp_courses($userid);
	foreach ($courses as $key=>$course) {
		if (!block_exacomp_is_teacher(context_course::instance($course->id))) {
			// unset($courses[$key]);
		}
	}
	return $courses;
}

function block_exacomp_exaportexists(){
	global $DB;
	return $DB->get_record('block',array('name'=>'exaport'));
}
function block_exacomp_exastudexists(){
	return class_exists('\block_exastud\api') && \block_exastud\api::active();
}


function block_exacomp_get_profile_settings($userid = 0){
	global $USER, $DB;

	if($userid == 0)
		$userid = $USER->id;

	$profile_settings = new stdClass();

	$profile_settings->exacomp = array();
	$exacomp_settings = $DB->get_records(\block_exacomp\DB_PROFILESETTINGS, array('block'=>'exacomp', 'userid'=>$userid));
	foreach($exacomp_settings as $setting){
		$profile_settings->exacomp[$setting->itemid] = $setting;
	}

	return $profile_settings;
}

function block_exacomp_reset_profile_settings($userid){
	global $DB;
	$DB->delete_records(\block_exacomp\DB_PROFILESETTINGS, array('userid'=>$userid));
}

function block_exacomp_set_profile_settings($userid, $courses){
	global $DB;

	block_exacomp_reset_profile_settings($userid);

	//save courses
	foreach($courses as $course){
		$insert = new stdClass();
		$insert->block = 'exacomp';
		$insert->itemid = $course;
		$insert->feedback = '';
		$insert->userid = $userid;

		$DB->insert_record(\block_exacomp\DB_PROFILESETTINGS, $insert);
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

		$DB->insert_record(\block_exacomp\DB_PROFILESETTINGS, $insert);
	}
}
function block_exacomp_check_profile_config($userid){
	global $DB;

	return $DB->get_records(\block_exacomp\DB_PROFILESETTINGS, array('userid'=>$userid));
}
function block_exacomp_set_tipp($compid, $user, $type, $scheme){
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
function block_exacomp_build_schooltype_tree_for_courseselection($limit_courseid){
	$schooltypes = block_exacomp_get_schooltypes_by_course($limit_courseid);

	foreach($schooltypes as $schooltype){
		$schooltype->subjects = block_exacomp_get_subjects_for_schooltype($limit_courseid, $schooltype->id);
	}

	return $schooltypes;
}
/**
 * This function is used for ePop, to test for the latest db update.
 * It is used after every xml import and every example upload.
 */
function block_exacomp_settstamp(){
	global $DB;

	$modsetting = $DB->get_record('block_exacompsettings', array('courseid'=>0,'activities'=>'importxml'));
	if ($modsetting){
		$modsetting->tstamp = time();
		$DB->update_record('block_exacompsettings', $modsetting);
	}else{
		$DB->insert_record('block_exacompsettings',array("courseid" => 0,"grading"=>"0","activities"=>"importxml","tstamp"=>time()));
	}
}
/**
 * This function checkes for finished quizes that are associated with competences and automatically gains them if the
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
							if(block_exacomp_additional_grading()){
								block_exacomp_save_additional_grading_for_descriptor($courseid, $descriptor->compid, $student->id, 
								\block_exacomp\global_config::get_value_additionalinfo_mapping($grading_scheme), $comptype = 0);
							}
						
							block_exacomp_set_user_competence($student->id, $descriptor->compid,
									0, $courseid, \block_exacomp\ROLE_TEACHER, $grading_scheme);
							mtrace("set competence ".$descriptor->compid." for user ".$student->id.'<br>');
						}
					}
					if(isset($test->topics)){
						foreach($test->topics as $topic){
							if(block_exacomp_additional_grading() && block_exacomp_is_topicgrading_enabled()){
								block_exacomp_save_additional_grading_for_descriptor($courseid, $descriptor->compid, $student->id, 
								\block_exacomp\global_config::get_value_additionalinfo_mapping($grading_scheme), $comptype = 1);
							}
							
							block_exacomp_set_user_competence($student->id, $topic->compid,
									1, $courseid, \block_exacomp\ROLE_TEACHER, $grading_scheme);
							mtrace("set topic competence ".$topic->compid." for user ".$student->id.'<br>');

						}
					}
				}
			}
		}
	}
	return true;
}
/**
 *
 * check if there are already evaluations available
 * @param unknown_type $courseid
 */
function block_exacomp_check_user_evaluation_exists($courseid){
	$students = block_exacomp_get_students_by_course($courseid);
	foreach($students as $student){
		$info =  block_exacomp_get_user_competences_by_course($student, $courseid);

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

/**
 * @return block_exacomp\cross_subject[]
 */
function block_exacomp_get_cross_subjects_drafts(){
	return block_exacomp\cross_subject::get_objects(array('courseid'=>0));
}
/**
 *
 * save the given drafts to course
 * @param array $drafts_to_save
 * @param int $courseid
 */
function block_exacomp_save_drafts_to_course($drafts_to_save, $courseid){
	//TODO test TOPICVISIBILITY
	global $DB, $USER;
	$redirect_crosssubjid = 0;
	foreach($drafts_to_save as $draftid){
		$draft = $DB->get_record(\block_exacomp\DB_CROSSSUBJECTS, array('id'=>$draftid));
		$draft->courseid = $courseid;
		$draft->creatorid = $USER->id;
		$draft->sourceid = 0;
		$draft->source = \block_exacomp\IMPORT_SOURCE_SPECIFIC;
		$crosssubjid = $DB->insert_record(\block_exacomp\DB_CROSSSUBJECTS, $draft);

		if($redirect_crosssubjid == 0) $redirect_crosssubjid = $crosssubjid;

		//assign competencies
		$comps = $DB->get_records(\block_exacomp\DB_DESCCROSS, array('crosssubjid'=>$draftid));
		foreach($comps as $comp){
			$insert = new stdClass();
			$insert->descrid = $comp->descrid;
			$insert->crosssubjid = $crosssubjid;
			$DB->insert_record(\block_exacomp\DB_DESCCROSS, $insert);

			//cross course subjects -> insert in visibility table if not existing
			$visibility = $DB->get_record(\block_exacomp\DB_DESCVISIBILITY, array('courseid'=>$courseid, 'descrid'=>$comp->descrid, 'studentid'=>0));
			if(!$visibility){
				$insert = new stdClass();
				$insert->courseid = $courseid;
				$insert->descrid = $comp->descrid;
				$insert->studentid=0;
				$insert->visible = 1;
				$DB->insert_record(\block_exacomp\DB_DESCVISIBILITY, $insert);
			}

			//check if descriptor has parent and if parent is visible in course
			$descriptor = $DB->get_record(\block_exacomp\DB_DESCRIPTORS, array('id'=>$comp->descrid));
			if($descriptor->parentid != 0){ //has parent
					$parent_visibility = $DB->get_record(\block_exacomp\DB_DESCVISIBILITY, array('courseid'=>$courseid, 'descrid'=>$descriptor->parentid, 'studentid'=>0));
					if(!$parent_visibility){ //not visible insert in table
						$insert = new stdClass();
						$insert->courseid = $courseid;
						$insert->descrid = $descriptor->parentid;
						$insert->studentid=0;
						$insert->visible = 1;
						$DB->insert_record(\block_exacomp\DB_DESCVISIBILITY, $insert);
					}
			}
		}
	}
	return $redirect_crosssubjid;
}

function block_exacomp_create_crosssub($courseid, $title, $description, $creatorid, $subjectid=0){
	global $DB;
	
	$insert = new stdClass();
	$insert->title = $title;
	$insert->description = $description;
	$insert->courseid = $courseid;
	$insert->creatorid = $creatorid;
	$insert->subjectid = $subjectid;
	$insert->sourceid = 0;
	$insert->source = \block_exacomp\IMPORT_SOURCE_SPECIFIC;
	return $DB->insert_record(\block_exacomp\DB_CROSSSUBJECTS, $insert);
}

function block_exacomp_edit_crosssub($crosssubjid, $title, $description, $subjectid){
	global $DB;
	
	$crosssubj = $DB->get_record(\block_exacomp\DB_CROSSSUBJECTS, array('id'=>$crosssubjid));
	$crosssubj->title = $title;
	$crosssubj->description = $description;
	$crosssubj->subjectid = $subjectid;
	return $DB->update_record(\block_exacomp\DB_CROSSSUBJECTS, $crosssubj);
}
function block_exacomp_delete_crosssub($crosssubjid){
	global $DB;
	//delete student association if crosssubject is deleted
	$DB->delete_records(\block_exacomp\DB_CROSSSTUD, array('crosssubjid'=>$crosssubjid));
	return $DB->delete_records(\block_exacomp\DB_CROSSSUBJECTS, array('id'=>$crosssubjid));
}
function block_exacomp_delete_crosssubject_drafts($drafts_to_delete){
	global $DB;
	foreach($drafts_to_delete as $draftid){
		$DB->delete_records(\block_exacomp\DB_CROSSSUBJECTS, array('id'=>$draftid));
	}
}

function block_exacomp_get_crosssubjects(){
	return g::$DB->get_records(\block_exacomp\DB_CROSSSUBJECTS);
}

/**
 * @param $courseid
 * @param null $studentid
 * @return block_exacomp\cross_subject[]
 */
function block_exacomp_get_cross_subjects_by_course($courseid, $studentid=null){
	$crosssubs = block_exacomp\cross_subject::get_objects(['courseid'=>$courseid], 'title');

	if (!$studentid) {
		return $crosssubs;
	}

	// also check for student permissions
	$crosssubs_shared = array();
	foreach($crosssubs as $crosssubj){
		if ($crosssubj->shared == 1 || block_exacomp_student_crosssubj($crosssubj->id, $studentid))
			$crosssubs_shared[$crosssubj->id] = $crosssubj;
	}
	return $crosssubs_shared;
}
function block_exacomp_student_crosssubj($crosssubjid, $studentid){
	global $DB;
	return $DB->get_records(\block_exacomp\DB_CROSSSTUD, array('crosssubjid'=>$crosssubjid, 'studentid'=>$studentid));
}

/**
 * Gets an associative array that is used to display the whole hierarchie of subjects, topics and competencies within a course
 *
 * @param int $courseid
 * @param int $subjectid
 * @return associative_array
 */
function block_exacomp_get_competence_tree_for_cross_subject($courseid, $crosssubjid, $showalldescriptors = false, $showallexamples = true, $filteredtaxonomies = array(SHOW_ALL_TAXONOMIES), $studentid = 0, $showonlyvisibletopics = false) {
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

	foreach ($allSubjects as $subject) {
		$subject->topics = [];
	}

	foreach ($allTopics as $topic) {
		//topic must be coursetopic if courseid <> 0
		if($courseid > 0 && !array_key_exists($topic->id, $courseTopics))
			continue;

		// find subject
		if (empty($allSubjects[$topic->subjid])) {
			continue;
		}
		
		if($showonlyvisibletopics && !block_exacomp_is_topic_visible($courseid, $topic, $studentid)){
			continue;
		}
		$subject = $allSubjects[$topic->subjid];

		// found: add it to the subject result
		$subject->topics[$topic->id] = $topic;
		$subjects[$subject->id] = $subject;
	}
	return block_exacomp\subject::create_objects($subjects);
}

function block_exacomp_get_descriptors_for_cross_subject($courseid, $crosssubjid, $showalldescriptors = false){
	global $DB;
	$comps = $DB->get_records(\block_exacomp\DB_DESCCROSS, array('crosssubjid'=>$crosssubjid),'','descrid,crosssubjid');

	if(!$comps) return array();

	$show_childs = array();
	$WHERE = "";
	foreach($comps as $comp){
		$cross_descr = $DB->get_record(\block_exacomp\DB_DESCRIPTORS,array('id'=>$comp->descrid));

		$WHERE .= (($cross_descr->parentid == 0)?$cross_descr->id:$cross_descr->parentid).',';

		if($cross_descr->parentid == 0) //parent deskriptor -> show all childs
			$show_childs[$cross_descr->id] = true;
	}
	$WHERE = substr($WHERE, 0, strlen($WHERE)-1);

	if(!$showalldescriptors)
		$showalldescriptors = block_exacomp_get_settings_by_course($courseid)->show_all_descriptors;

	$sql = '(SELECT DISTINCT desctopmm.id as u_id, d.id as id, d.source, d.title, d.niveauid, t.id AS topicid, d.profoundness, d.sorting, d.parentid, dvis.visible as visible, tvis.visible as tvisible, n.sorting as niveau '
	.'FROM {'.\block_exacomp\DB_TOPICS.'} t '
	.'JOIN {'.\block_exacomp\DB_DESCTOPICS.'} desctopmm ON desctopmm.topicid=t.id '
	.'JOIN {'.\block_exacomp\DB_DESCRIPTORS.'} d ON desctopmm.descrid=d.id AND d.parentid = 0 '
	.'JOIN {'.\block_exacomp\DB_DESCVISIBILITY.'} dvis ON dvis.descrid = d.id AND dvis.studentid=0 AND dvis.courseid=? '
	.'JOIN {'.\block_exacomp\DB_TOPICVISIBILITY.'} tvis ON tvis.topicid = t.id AND tvis.studentid=0 AND tvis.courseid=? '
	.'LEFT JOIN {'.\block_exacomp\DB_NIVEAUS.'} n ON n.id = d.niveauid '
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
		$topic = $DB->get_record(\block_exacomp\DB_TOPICS, array('id'=>$descriptor->topicid));
		if(!array_key_exists($topic->id, $topics))
			$topics[$topic->id] = $topic;
	}

	return $topics;
}
function block_exacomp_cross_subjects_exists(){
	global $DB;
	$dbman = $DB->get_manager();
	$table = new xmldb_table(\block_exacomp\DB_CROSSSUBJECTS);
	return $dbman->table_exists($table);
}
function block_exacomp_set_cross_subject_descriptor($crosssubjid,$descrid) {
	global $DB, $COURSE;
	$record = $DB->get_record(\block_exacomp\DB_DESCCROSS,array('crosssubjid'=>$crosssubjid,'descrid'=>$descrid));
	if(!$record)
		$DB->insert_record(\block_exacomp\DB_DESCCROSS,array('crosssubjid'=>$crosssubjid,'descrid'=>$descrid));

	//insert visibility if cross course
	$cross_subject = $DB->get_record(\block_exacomp\DB_CROSSSUBJECTS, array('id'=>$crosssubjid));
	$visibility = $DB->get_record(\block_exacomp\DB_DESCVISIBILITY, array('courseid'=>$cross_subject->courseid, 'descrid'=>$descrid, 'studentid'=>0));
	if(!$visibility){
		$insert = new stdClass();
		$insert->courseid = $cross_subject->courseid;
		$insert->descrid = $descrid;
		$insert->studentid = 0;
		$insert->visible = 1;
		$DB->insert_record(\block_exacomp\DB_DESCVISIBILITY, $insert);
	}

	$descriptor = $DB->get_record(\block_exacomp\DB_DESCRIPTORS, array('id'=>$descrid));

	if($descriptor->parentid == 0){	//insert children into visibility table
		//get topicid
		$descriptor_topic_mm = $DB->get_record(\block_exacomp\DB_DESCTOPICS, array('descrid'=>$descriptor->id));
		$descriptor->topicid = $descriptor_topic_mm->topicid;

		$children = block_exacomp_get_child_descriptors($descriptor, $COURSE->id);

		foreach($children as $child){
			$visibility = $DB->get_record(\block_exacomp\DB_DESCVISIBILITY, array('courseid'=>$cross_subject->courseid, 'descrid'=>$child->id, 'studentid'=>0));
			if(!$visibility){
				$insert = new stdClass();
				$insert->courseid = $cross_subject->courseid;
				$insert->descrid = $child->id;
				$insert->studentid = 0;
				$insert->visible = 1;
				$DB->insert_record(\block_exacomp\DB_DESCVISIBILITY, $insert);

				//insert example visibility if not existent
				$child = block_exacomp_get_examples_for_descriptor($child, array(SHOW_ALL_TAXONOMIES), true, $COURSE->id);
				foreach($child->examples as $example){
					$record = $DB->get_records(\block_exacomp\DB_EXAMPVISIBILITY, array('courseid'=>$cross_subject->courseid, 'exampleid'=>$example->id, 'studentid'=>0));
					if(!$record){
						$insert = new stdClass();
						$insert->courseid = $cross_subject->courseid;
						$insert->exampleid = $example->id;
						$insert->studentid = 0;
						$insert->visible = 1;
						$DB->insert_record(\block_exacomp\DB_EXAMPVISIBILITY, $insert);
					}
				}
			}
		}
	}
	else{ //insert parent into visibility table
		$visibility = $DB->get_record(\block_exacomp\DB_DESCVISIBILITY, array('courseid'=>$cross_subject->courseid, 'descrid'=>$descriptor->parentid, 'studentid'=>0));
		if(!$visibility){
			$insert = new stdClass();
			$insert->courseid = $cross_subject->courseid;
			$insert->descrid = $descriptor->parentid;
			$insert->studentid = 0;
			$insert->visible = 1;
			$DB->insert_record(\block_exacomp\DB_DESCVISIBILITY, $insert);
		}
	}

	//example visibility
	$descriptor = block_exacomp_get_examples_for_descriptor($descriptor, array(SHOW_ALL_TAXONOMIES), true, $COURSE->id);

	foreach($descriptor->examples as $example){
		$record = $DB->get_records(\block_exacomp\DB_EXAMPVISIBILITY, array('courseid'=>$cross_subject->courseid, 'exampleid'=>$example->id,'studentid'=>0));
		if(!$record){
			$insert = new stdClass();
			$insert->courseid = $cross_subject->courseid;
			$insert->exampleid = $example->id;
			$insert->studentid = 0;
			$insert->visible = 1;

			$DB->insert_record(\block_exacomp\DB_EXAMPVISIBILITY, $insert);
		}
	}
}

function block_exacomp_unset_cross_subject_descriptor($crosssubjid, $descrid){
	global $DB, $COURSE;
	$record = $DB->get_record(\block_exacomp\DB_DESCCROSS,array('crosssubjid'=>$crosssubjid,'descrid'=>$descrid));
	if($record)
		$DB->delete_records(\block_exacomp\DB_DESCCROSS,array('crosssubjid'=>$crosssubjid,'descrid'=>$descrid));

	//delete visibility of non course descriptors, not connected to another course crosssubject
	$cross_subject = $DB->get_record(\block_exacomp\DB_CROSSSUBJECTS, array('id'=>$crosssubjid));
	$cross_courseid = $cross_subject->courseid;

	if($cross_courseid != $COURSE->id){	//not current course
		$course_descriptors = block_exacomp_get_descriptors($cross_courseid);

		if(!array_key_exists($descrid, $course_descriptors)){	// no course descriptor -> cross course
			$descriptor_crosssubs_mm = $DB->get_records(\block_exacomp\DB_DESCCROSS, array('descrid'=>$descrid));
			$course_cross_subjects = block_exacomp_get_cross_subjects_by_course($cross_courseid);

			$used_in_other_crosssub = false;
			foreach($descriptor_crosssubs_mm as $entry){
				if($entry->crosssubjid != $cross_subject->id){
					if(array_key_exists($entry->crosssubjid, $course_cross_subjects))
						$used_in_other_crosssub = true;
				}
			}

			if(!$used_in_other_crosssub){ // delete visibility if not used in other cross subject in this course
				$DB->delete_records(\block_exacomp\DB_DESCVISIBILITY, array('descrid'=>$descrid, 'courseid'=>$cross_courseid, 'studentid'=>0));
			}
		}
	}
}
function block_exacomp_get_descr_topic_sorting($topicid, $descid){
	global $DB;
	$record = $DB->get_record(\block_exacomp\DB_DESCTOPICS, array('descrid'=>$descid, 'topicid'=>$topicid));
	return ($record) ? $record->sorting : 0;
}
function block_exacomp_set_descriptor_visibility($descrid, $courseid, $visible, $studentid){
	global $DB;
	if($studentid == BLOCK_EXACOMP_SHOW_ALL_STUDENTS || $studentid ==0){//if visibility changed for all: delete individual settings
		$studentid = 0;
		$sql = "DELETE FROM {".\block_exacomp\DB_DESCVISIBILITY."} WHERE descrid = ? AND courseid = ? and studentid <> 0";

		$DB->execute($sql, array($descrid, $courseid));
	}
	g::$DB->insert_or_update_record(\block_exacomp\DB_DESCVISIBILITY,
		['visible'=>$visible],
		['descrid'=>$descrid, 'courseid'=>$courseid, 'studentid'=>$studentid]
	);
	
	block_exacomp_update_visibility_cache($courseid);
}
function block_exacomp_set_example_visibility($exampleid, $courseid, $visible, $studentid){
	if($studentid == BLOCK_EXACOMP_SHOW_ALL_STUDENTS || $studentid == 0){//if visibility changed for all: delete individual settings
		$studentid = 0;
		$sql = "DELETE FROM {".\block_exacomp\DB_EXAMPVISIBILITY."} WHERE exampleid = ? AND courseid = ? and studentid <> 0";
		g::$DB->execute($sql, array($exampleid, $courseid));
	}

	g::$DB->insert_or_update_record(\block_exacomp\DB_EXAMPVISIBILITY,
		['visible'=>$visible],
		['exampleid'=>$exampleid, 'courseid'=>$courseid, 'studentid'=>$studentid]
	);
	
	block_exacomp_update_visibility_cache($courseid);
}
function block_exacomp_set_example_solution_visibility($exampleid, $courseid, $visible, $studentid){
	if($studentid == BLOCK_EXACOMP_SHOW_ALL_STUDENTS || $studentid == 0){//if visibility changed for all: delete individual settings
		$studentid = 0;
		$sql = "DELETE FROM {".\block_exacomp\DB_SOLUTIONVISIBILITY."} WHERE exampleid = ? AND courseid = ? and studentid <> 0";
		g::$DB->execute($sql, array($exampleid, $courseid));
	}

	g::$DB->insert_or_update_record(\block_exacomp\DB_SOLUTIONVISIBILITY,
		['visible'=>$visible],
		['exampleid'=>$exampleid, 'courseid'=>$courseid, 'studentid'=>$studentid]
	);
}
function block_exacomp_set_topic_visibility($topicid, $courseid, $visible, $studentid){
	global $DB;
	if($studentid == BLOCK_EXACOMP_SHOW_ALL_STUDENTS || $studentid ==0){//if visibility changed for all: delete individual settings
		$studentid = 0;
		$sql = "DELETE FROM {".\block_exacomp\DB_TOPICVISIBILITY."} WHERE topicid = ? AND courseid = ? and studentid <> 0";

		$DB->execute($sql, array($topicid, $courseid));
	}
	g::$DB->insert_or_update_record(\block_exacomp\DB_TOPICVISIBILITY,
			['visible'=>$visible],
			['topicid'=>$topicid, 'courseid'=>$courseid, 'studentid'=>$studentid]
			);
	block_exacomp_update_visibility_cache($courseid);
}
function block_exacomp_topic_used($courseid, $topic, $studentid){
	global $DB;
	if($studentid == 0){
		$sql = "SELECT * FROM {".\block_exacomp\DB_COMPETENCES."} WHERE courseid = ? AND compid = ? AND comptype=? AND ( value>=0 OR additionalinfo IS NOT NULL)";
		$records = $DB->get_records_sql($sql, array($courseid, $topic->id, TYPE_TOPIC));
		if($records) return true;
	}else{
		$sql = "SELECT * FROM {".\block_exacomp\DB_COMPETENCES."} WHERE courseid = ? AND compid = ? AND comptype=? AND userid = ? AND ( value>=0 OR additionalinfo IS NOT NULL)";
		$records = $DB->get_records_sql($sql, array($courseid, $topic->id, TYPE_TOPIC, $studentid));
		if($records) return true;
	}
	
	$descriptors = block_exacomp_get_descriptors_by_topic($courseid, $topic->id);
	foreach($descriptors as $descriptor){
		$descriptor->children = block_exacomp_get_child_descriptors($descriptor, $courseid);
		if(block_exacomp_descriptor_used($courseid, $descriptor, $studentid))
			return true;
	}
	
	return false;
}
function block_exacomp_descriptor_used($courseid, $descriptor, $studentid){
	global $DB;
	//if studentid == 0 used = true, if no evaluation (teacher OR student) for this descriptor for any student in this course
	//								 if no evaluation/submission for the examples of this descriptor

	//if studentid != 0 used = true, if any assignment (teacher OR student) for this descriptor for THIS student in this course
	//								 if no evaluation/submission for the examples of this descriptor
	
	if(!isset($descriptor->examples))
		$descriptor = block_exacomp_get_examples_for_descriptor($descriptor);
	
	if($studentid == 0){
		$sql = "SELECT * FROM {".\block_exacomp\DB_COMPETENCES."} WHERE courseid = ? AND compid = ? AND comptype=? AND ( value>=0 OR additionalinfo IS NOT NULL)";
		$records = $DB->get_records_sql($sql, array($courseid, $descriptor->id, TYPE_DESCRIPTOR));
		if($records) return true;
	}else{
		$sql = "SELECT * FROM {".\block_exacomp\DB_COMPETENCES."} WHERE courseid = ? AND compid = ? AND comptype=? AND userid = ? AND ( value>=0 OR additionalinfo IS NOT NULL)";
		$records = $DB->get_records_sql($sql, array($courseid, $descriptor->id, TYPE_DESCRIPTOR, $studentid));
		if($records) return true;
	}
	
	if(isset($descriptor->children)){
		//check child used
		foreach($descriptor->children as $child){
			if(block_exacomp_descriptor_used($courseid, $child, $studentid))
				return true;
		}
	}
	
	if($descriptor->examples){
		foreach($descriptor->examples as $example){
			if(block_exacomp_example_used($courseid, $example, $studentid))
				return true;
		}
	}
	
	return false;
}

function block_exacomp_example_used($courseid, $example, $studentid){
	global $DB;
	//if studentid == 0 used = true, if no evaluation/submission for this example
	//if studentid != 0 used = true, if no evaluation/submission for this examples for this student
	
	if($studentid <= 0){ // any self or teacher evaluation
		$sql = "SELECT * FROM {".\block_exacomp\DB_EXAMPLEEVAL."} WHERE courseid = ? AND exampleid = ? AND teacher_evaluation>= 0";
		$records = $DB->get_records_sql($sql, array($courseid, $example->id));
		if($records)
			return true;
		
		$sql = "SELECT * FROM {".\block_exacomp\DB_EXAMPLEEVAL."} WHERE courseid = ? AND exampleid = ? AND student_evaluation>= 0";
		$records = $DB->get_records_sql($sql, array($courseid, $example->id));
		if($records) return true;
		
		//on any weekly schedule? -> yes: used
		$onSchedule = $DB->record_exists(\block_exacomp\DB_SCHEDULE, array('courseid'=>$courseid, 'exampleid' => $example->id));
		if($onSchedule)
			return true;
			
		//any submission made?
		if(block_exacomp_exaportexists()){
			$sql = "SELECT * FROM {".\block_exacomp\DB_ITEMEXAMPLE."} ie JOIN {".'block_exaportitem'."} i ON ie.itemid = i.id ".
					"WHERE ie.exampleid = ? AND i.courseid = ?";
			$records = $DB->get_records_sql($sql, array($example->id, $courseid));
			if($records)
				return true;
		}
	}else{ // any self or teacher evaluation for this student
		$sql = "SELECT * FROM {".\block_exacomp\DB_EXAMPLEEVAL."} WHERE courseid = ? AND exampleid = ? AND studentid=? AND teacher_evaluation>=0";
		$records = $DB->get_records_sql($sql, array($courseid, $example->id, $studentid));
		if($records) return true;
		
		$sql = "SELECT * FROM {".\block_exacomp\DB_EXAMPLEEVAL."} WHERE courseid = ? AND exampleid = ? AND studentid = ? AND student_evaluation>=0";
		$records = $DB->get_records_sql($sql, array($courseid, $example->id, $studentid));
		if($records) return true;
		
		//on students weekly schedule? -> yes: used
		$onSchedule = $DB->record_exists(\block_exacomp\DB_SCHEDULE, array('studentid'=>$studentid, 'courseid'=>$courseid, 'exampleid' => $example->id));
		if($onSchedule)
			return true;
		
		//or on pre planning storage
		$onSchedule = $DB->record_exists(\block_exacomp\DB_SCHEDULE, array('studentid'=>0, 'courseid'=>$courseid, 'exampleid' => $example->id));
		if($onSchedule)
			return true;
		
		//submission made?
		if(block_exacomp_exaportexists()){
			$sql = "SELECT * FROM {".\block_exacomp\DB_ITEMEXAMPLE."} ie JOIN {".'block_exaportitem'."} i ON ie.itemid = i.id ".
					"WHERE ie.exampleid = ? AND i.userid = ? AND i.courseid = ?";
			$records = $DB->get_records_sql($sql, array($example->id, $studentid, $courseid));
			if($records)
				return true;
		}
	}
	
	return false;
}
function block_exacomp_get_students_for_crosssubject($courseid, $crosssub){
	global $DB;
	$course_students = block_exacomp_get_students_by_course($courseid);
	if($crosssub->shared)
		return $course_students;

	$students = array();
	$assigned_students = $DB->get_records_menu(\block_exacomp\DB_CROSSSTUD,array('crosssubjid'=>$crosssub->id),'','studentid,crosssubjid');
	foreach($course_students as $student){
		if(isset($assigned_students[$student->id]))
			$students[$student->id] = $student;
	}
	return $students;
}

/**
 * get the url to view an example, the example is from $studentid, $viewerid wants to view it
 * @param $studentid
 * @param $viewerid
 * @param $exampleid
 * @return null|string
 */
function block_exacomp_get_viewurl_for_example($studentid, $viewerid, $exampleid) {
	global $CFG, $DB;

	if (!block_exacomp_exaportexists()) {
		return null;
	}

	$item = block_exacomp_get_current_item_for_example($studentid, $exampleid);

	if(!$item)
		return null;

	if ($studentid == $viewerid) {
		// view my own item
		$access = "portfolio/id/".$studentid."&itemid=".$item->id;
	} else {
		// view sb elses item, find a suitable view
		$sql = "SELECT viewblock.*
			FROM {block_exaportviewblock} viewblock
			JOIN {block_exaportviewshar} viewshar ON viewshar.viewid=viewblock.viewid
			WHERE viewblock.type='item' AND viewblock.itemid=? AND viewshar.userid=?";
		$view = $DB->get_record_sql($sql, [ $item->id, $viewerid ]);
		if(!$view)
			return null;

		$access = "view/id/".$studentid."-".$view->viewid."&itemid=".$item->id;
	}

	return $CFG->wwwroot.'/blocks/exaport/shared_item.php?access='.$access;
}
function block_exacomp_get_gridurl_for_example($courseid, $studentid, $exampleid) {
	global $CFG, $DB;
	
	$example_descriptors = $DB->get_records(\block_exacomp\DB_DESCEXAMP,array('exampid'=>$exampleid),'','descrid');
	$tree = block_exacomp_build_example_association_tree($courseid, $example_descriptors, $exampleid);
	$topic = (reset(reset($tree)->topics));
	return $CFG->wwwroot.'/blocks/exacomp/assign_competencies.php?courseid='.$courseid . '&studentid='.$studentid . '&subjectid='.$topic->subjid . '&topicid='.$topic->id;
}
function block_exacomp_add_example_to_schedule($studentid,$exampleid,$creatorid,$courseid,$start=null,$end=null) {
	global $USER, $DB;

	$timecreated = $timemodified = time();

	$DB->insert_record(\block_exacomp\DB_SCHEDULE, array('studentid' => $studentid, 'exampleid' => $exampleid, 'courseid' => $courseid,'creatorid' => $creatorid, 'timecreated' => $timecreated, 'timemodified' => $timemodified, 'start' => $start,'end' => $end));
	//only send a notification if a teacher adds an example for a student and not for pre planning storage
	if($creatorid != $studentid && $studentid >0)
		block_exacomp_send_weekly_schedule_notification($USER,$DB->get_record('user', array('id' => $studentid)), $courseid, $exampleid);

	\block_exacomp\event\example_added::log(['objectid' => $exampleid, 'courseid' => $courseid, 'relateduserid' => $studentid]);

	return true;
}
function block_exacomp_add_examples_to_schedule_for_all($courseid) {
	// Check Permission
	block_exacomp_require_teacher($courseid);
	// Get all examples to add:
	//    -> studentid 0: on teachers schedule
	$examples = g::$DB->get_records_select(block_exacomp\DB_SCHEDULE, "studentid = 0 AND courseid = ? AND start IS NOT NULL AND end IS NOT NULL AND deleted = 0", array($courseid));
	
	// Get all students for the given course
	$students = block_exacomp_get_students_by_course($courseid);
	// Add examples for all users
	foreach($examples as $example)
		foreach($students as $student)
			block_exacomp_add_example_to_schedule($student->id, $example->exampleid, g::$USER->id, $courseid, $example->start, $example->end);

			// Delete records from the teacher's schedule
			g::$DB->delete_records_list(block_exacomp\DB_SCHEDULE, 'id', array_keys($examples));
			return true;
}
function block_exacomp_add_days($date, $days) {
	return mktime(0,0,0,date('m', $date), date('d', $date)+$days, date('Y', $date));
}

function block_exacomp_build_example_association_tree($courseid, $example_descriptors = array(), $exampleid=0, $descriptorid = 0, $showallexamples=false){
	//get all subjects, topics, descriptors and examples
	$tree = block_exacomp_get_competence_tree($courseid, null, null, false, block_exacomp\SHOW_ALL_NIVEAUS, true, block_exacomp_get_settings_by_course($courseid)->filteredtaxonomies, false, false, true);

	// unset all descriptors, topics and subjects that do not contain the example descriptors
	foreach($tree as $skey => $subject) {
		$subject->associated = 0;
		foreach ( $subject->topics as $tkey => $topic ) {
			$topic->associated = 0;
			if(isset($topic->descriptors)) {
				foreach ( $topic->descriptors as $dkey => $descriptor ) {

					$descriptor = block_exacomp_check_child_descriptors($descriptor, $example_descriptors, $exampleid, $descriptorid, $showallexamples);

					if ($descriptor->associated)
						$topic->associated = 1;
				}
			}

			if ($topic->associated)
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

	$colspan = count($niveaus)-1;

	foreach($niveaus as $id => $niveau) {
		if((isset($niveau->title))?in_array($niveau->title, $spanningNiveaus):in_array($niveau, $spanningNiveaus)) {
			$colspan--;
		}
	}

	return $colspan;
}
function block_exacomp_is_topic_visible($courseid, $topic, $studentid){
	global $DB;
	
	// $studentid could be BLOCK_EXACOMP_SHOW_ALL_STUDENTS
	if ($studentid <= 0) {
		$studentid = 0;
	}
	
	$visibilities = block_exacomp_get_visibility_cache($courseid);
	
	return array_key_exists($topic->id, $visibilities->topic_visibilities[$studentid]);
}
				
function block_exacomp_is_descriptor_visible($courseid, $descriptor, $studentid, $checkParent = false) {
	global $DB;

	// $studentid could be BLOCK_EXACOMP_SHOW_ALL_STUDENTS
	if ($studentid <= 0) {
		$studentid = 0;
	}

	$visibilities = block_exacomp_get_visibility_cache($courseid);
	
	return array_key_exists($descriptor->id, $visibilities->descriptor_visibilites[$studentid]);
}
function block_exacomp_is_example_visible($courseid, $example, $studentid, $checkParent = false){
	global $DB;

	// $studentid could be BLOCK_EXACOMP_SHOW_ALL_STUDENTS
	if ($studentid <= 0) {
		$studentid = 0;
	}

	$visibilities = block_exacomp_get_visibility_cache($courseid);
	
	return array_key_exists($example->id, $visibilities->example_visibilities[$studentid]);
}
function block_exacomp_is_example_solution_visible($courseid, $example, $studentid){
	global $DB;

	// $studentid could be BLOCK_EXACOMP_SHOW_ALL_STUDENTS
	if ($studentid <= 0) {
		$studentid = 0;
	}

	$visibilities = block_exacomp_get_visibility_cache($courseid);
	
	return array_key_exists($example->id, $visibilities->solution_visibilities[$studentid]);
}

function block_exacomp_get_visible_css($visible, $role) {
	$visible_css = '';
	if(!$visible)
		($role == \block_exacomp\ROLE_TEACHER) ? $visible_css = ' rg2-locked' : $visible_css = ' hidden';

	return $visible_css;
}

	function block_exacomp_get_descriptor_numbering($descriptor) {
		if (!block_exacomp_is_numbering_enabled()) {
			return '';
		}

		$id = $descriptor->id; // saved for later

		static $numberingCache = [];

		if (!isset($numberingCache[$id])) {
			// build cache

			if (isset($descriptor->topic) && $descriptor->topic instanceof \block_exacomp\topic) {
				$topic = $descriptor->topic;
			} else {
				$topic = \block_exacomp\topic::get($descriptor->topicid);
			}

			$topicNumbering = $topic->get_numbering();
			if (!$topicNumbering) {
				// no numbering
				foreach ($topic->descriptors as $descriptor) {
					$numberingCache[$descriptor->id] = '';

					foreach ($descriptor->children as $descriptor) {
						$numberingCache[$descriptor->id] = '';
					}
				}
			} else {
				// get niveaus and descriptor counts
				$niveaus = g::$DB->get_records(block_exacomp\DB_NIVEAUS);
				foreach ($topic->descriptors as $descriptor) {
					if (isset($niveaus[$descriptor->niveauid])) {
						@$niveaus[$descriptor->niveauid]->descriptor_cnt++;
					}
				}

				$descriptorMainNumber = 0;
				foreach ($topic->descriptors as $descriptor) {
					if (!isset($niveaus[$descriptor->niveauid])) {
						// descriptor without niveau
						$descriptorMainNumber++;
						$descriptorNumber = $descriptorMainNumber;
					} elseif ($niveaus[$descriptor->niveauid]->descriptor_cnt > 1) {
						// make niveaus with multiple descriptors in the format of "{$niveau->numb}-{$i}"
						$descriptorMainNumber = $niveaus[$descriptor->niveauid]->numb;
						@$niveaus[$descriptor->niveauid]->descriptor_i++;
						$descriptorNumber = $niveaus[$descriptor->niveauid]->numb.'-'.$niveaus[$descriptor->niveauid]->descriptor_i;
					} else {
						$descriptorMainNumber = $niveaus[$descriptor->niveauid]->numb;
						$descriptorNumber = $niveaus[$descriptor->niveauid]->numb;
					}

					$numberingCache[$descriptor->id] = $topicNumbering ? $topicNumbering.'.'.$descriptorNumber : '';

					foreach (array_values($descriptor->children) as $j => $descriptor) {
						$numberingCache[$descriptor->id] = $topicNumbering ? $topicNumbering.'.'.$descriptorNumber.'.'.($j + 1) : '';
					}
				}
			}
		}

		return isset($numberingCache[$id]) ? $numberingCache[$id] : 'not found #v96900';
	}

	/**
	 *
	 * @param id|block_exacomp\topic $topic
	 * @return string
	 */
	function block_exacomp_get_topic_numbering($topic) {
		if (!block_exacomp_is_numbering_enabled()) {
			return '';
		}

		// make sure it is a topic object
		$topic = \block_exacomp\topic::to_object($topic);
		if (!$topic) {
			throw new moodle_exception('topic not found');
		}

		$subject = $topic->get_subject();
		if ($subject && !empty($subject->titleshort) && !empty($topic->numb)) {
			return $subject->titleshort.'.'.$topic->numb;
		} else {
			return '';
		}
	}

function block_exacomp_get_course_cross_subjects_drafts_sorted_by_subjects(){
	$subjects = block_exacomp_get_subjects_by_course(g::$COURSE->id);

	$default_subject = new stdClass();
	$default_subject->id = 0;
	$default_subject->title = get_string('nocrosssubsub', 'block_exacomp');

	// insert default subject at the front
	array_unshift($subjects, $default_subject);

	foreach($subjects as $key=>$subject){
		$subject->cross_subject_drafts = block_exacomp\cross_subject::get_objects(array('subjectid'=>$subject->id, 'courseid'=>0), 'title');
		if (!$subject->cross_subject_drafts) {
			unset($subjects[$key]);
		}
	}

	return $subjects;
}

function block_exacomp_get_cross_subjects_grouped_by_subjects(){
	global $DB;

	$subjects = block_exacomp_get_subjects();

	$default_subject = new stdClass();
	$default_subject->id = 0;
	$default_subject->title = get_string('nocrosssubsub', 'block_exacomp');

	$subjects[0] = $default_subject;

	foreach($subjects as $subject){
		$subject->crosssubjects = $DB->get_records_sql('
			SELECT *
			FROM {'.\block_exacomp\DB_CROSSSUBJECTS.'}
			WHERE subjectid=? AND courseid>0', [$subject->id]);

		if (!$subject->crosssubjects) {
			// ignore this subject
			unset($subjects[$subject->id]);
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

	$children = $DB->get_records(\block_exacomp\DB_DESCRIPTORS,array("parentid" => $descriptorid));

	foreach($children as $child) {
		$child_crosssubjects = block_exacomp_get_cross_subjects_for_descriptor($courseid, $child->id);
		$crosssubjects += $child_crosssubjects;
	}

	return $crosssubjects;
}

function block_exacomp_get_cross_subject_descriptors($crosssubjid) {
	global $DB;
	$sql = "SELECT d.* from {".\block_exacomp\DB_DESCRIPTORS."} d
			JOIN {".\block_exacomp\DB_DESCCROSS."} dc ON dc.descrid = d.id
			WHERE dc.crosssubjid = ?";
	$descriptors = $DB->get_records_sql($sql, array("crosssubjid" => $crosssubjid));

	return $descriptors;
}

function block_exacomp_delete_custom_descriptor($descriptorid){
	global $DB;

	//delete descriptor evaluation
	$DB->delete_records(\block_exacomp\DB_COMPETENCES, array('compid'=>$descriptorid, 'comptype'=>TYPE_DESCRIPTOR));

	//delete crosssubject association
	$DB->delete_records(\block_exacomp\DB_DESCCROSS, array('descrid'=>$descriptorid));

	//delete descriptor
	$DB->delete_records(\block_exacomp\DB_DESCRIPTORS, array('id'=>$descriptorid));

}
function block_exacomp_get_cross_subject_examples($crosssubjid) {
	global $DB;
	$sql = "SELECT e.* from {".\block_exacomp\DB_EXAMPLES."} e
			JOIN {".\block_exacomp\DB_DESCEXAMP."} de ON de.exampid = e.id
			JOIN {".\block_exacomp\DB_DESCRIPTORS."} d ON de.descrid = d.id
			JOIN {".\block_exacomp\DB_DESCCROSS."} dc ON dc.descrid = d.id
			WHERE dc.crosssubjid = ?";
	return $DB->get_records_sql($sql, array("crosssubjid" => $crosssubjid));
}

/**
 * returns statistic according to new grading possibilities, only examples directly associated are minded
 * @param unknown $courseid
 * @param unknown $descrid
 * @param unknown $studentid
 * @param number $crosssubjid
 * @return number[]|number[][]
 */
function block_exacomp_get_example_statistic_for_descriptor_refact($courseid, $descrid, $studentid, $crosssubjid = 0) {
	global $DB;

	//get descriptor from id
	$descriptor = $DB->get_record(\block_exacomp\DB_DESCRIPTORS,array("id" => $descrid));
	//get examples for descriptor
	$descriptor = block_exacomp_get_examples_for_descriptor($descriptor, array(SHOW_ALL_TAXONOMIES), true, $courseid);

	//check if descriptor is associated if crosssubject is given - if not examples are not included in crosssubject
	$crosssubjdescriptos = array();
	if($crosssubjid > 0)
		$crosssubjdescriptos = block_exacomp_get_descriptors_for_cross_subject($courseid, $crosssubjid);

	if($studentid > 0){
		$students = block_exacomp_get_students_by_course($courseid);
		$student = $students[$studentid];
		$student = block_exacomp_get_user_information_by_course($student, $courseid);
	}
	//define values to be returned
	$total = 0; //total number of examples associated with descriptor
	$hidden = 0; //number of examples visible for student
	$visible = 0; //number of examples visible to user

	$inwork = 0; //number of examples in work (on schedule or pool)
	$notinwork = 0; //number of examples not in work (not on schedule or pool)
	$edited = 0; //number of examples were a submission or evaluation exists

	$evaluated = 0;
	$notevaluated = 0; //number of examples not evaluated

	$gradings = array(); //array[niveauid][value][number of examples evaluated with this value and niveau]
	//create grading statistic
	$scheme_items = \block_exacomp\global_config::get_value_titles(block_exacomp_get_grading_scheme($courseid));
	$evaluationniveau_items = \block_exacomp\global_config::get_evalniveaus();

	if(block_exacomp_use_eval_niveau())
		foreach($evaluationniveau_items as $niveaukey => $niveauitem){
			$gradings[$niveaukey] = array();
			foreach($scheme_items as $schemekey => $schemetitle){
				$gradings[$niveaukey][$schemekey] = 0;
			}
		}
	else 
		foreach($scheme_items as $key => $title){
			$gradings[$key] = 0;
		}
	
	$totalgrade = 0; //TODO still needed?

	//calculate statistic
	if($crosssubjid == 0 || array_key_exists($descriptor->id, $crosssubjdescriptos)){
		$total = count($descriptor->examples);

		foreach($descriptor->examples as $example){
			//count visible examples for this student
			if(block_exacomp_is_example_visible($courseid, $example, $studentid))
				$visible++;

				//check if inwork
				$schedule = $DB->record_exists(\block_exacomp\DB_SCHEDULE, array('courseid'=>$courseid, 'exampleid'=>$example->id, 'studentid'=>$studentid));
				if($schedule)
					$inwork++;
						
				if($studentid > 0){ //no meaningful numbers if studentid > 0
					//submission made?
					$submission_exists = false;
					if(block_exacomp_exaportexists()){
						$sql = "SELECT * FROM {".\block_exacomp\DB_ITEMEXAMPLE."} ie JOIN {".'block_exaportitem'."} i ON ie.itemid = i.id ".
								"WHERE ie.exampleid = ? AND i.userid = ? AND i.courseid = ?";
						$records = $DB->get_records_sql($sql, array($example->id, $studentid, $courseid));
						if($records)
							$submission_exists = true;
					}
						
					$teacher_eval_exists = false;
					$sql = "SELECT * FROM {".\block_exacomp\DB_EXAMPLEEVAL."} WHERE courseid = ? AND exampleid = ? AND studentid=? AND teacher_evaluation>=0";
					$records = $DB->get_records_sql($sql, array($courseid, $example->id, $studentid));
					if($records) $teacher_eval_exists = true;
						
					$student_eval_exists = false;
					$sql = "SELECT * FROM {".\block_exacomp\DB_EXAMPLEEVAL."} WHERE courseid = ? AND exampleid = ? AND studentid = ? AND student_evaluation>=0";
					$records = $DB->get_records_sql($sql, array($courseid, $example->id, $studentid));
					if($records) $student_eval_exists = true;
						
					if($submission_exists || $teacher_eval_exists || $student_eval_exists)
						$edited++;
							
					if($teacher_eval_exists || $student_eval_exists)
						$evaluated++;
							
						//create grading statistic
						if(block_exacomp_use_eval_niveau()){
							if(isset($student->examples->teacher[$example->id]) && isset($student->examples->niveau[$example->id]))
								$gradings[$student->examples->niveau[$example->id]][$student->examples->teacher[$example->id]]++;
						}else{ 
							if(isset($student->examples->teacher[$example->id]))
								$gradings[$student->examples->teacher[$example->id]]++;
						}
				}
		}
			
		$hidden = $total - $visible;
		$notinwork = $total - $inwork;
		$notevaluated = $total - $evaluated;

	}

	$statistic = array($total, $gradings, $notevaluated, $inwork, $totalgrade, $notinwork, $hidden, $edited, $evaluated, $visible);

	return $statistic;
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
 * @param array|object $item database item
 * @param string $type
 * @return moodle_url
 */
function block_exacomp_get_file_url($item, $type, $context=null) {
	// get from filestorage
	$file = block_exacomp_get_file($item, $type);

	if (!$file) return null;

	return block_exacomp_get_url_for_file($file, $context);
}

/**
 * @param stored_file $file
 * @return moodle_url
 */
function block_exacomp_get_url_for_file($file, $context=null) {
	$context = block_exacomp_get_context_from_courseid($context);

	$url = moodle_url::make_pluginfile_url($context->id, $file->get_component(), $file->get_filearea(),
		$file->get_itemid(), $file->get_filepath(), $file->get_filename());

	return $url;
}

function block_exacomp_get_examples_for_pool($studentid, $courseid){
	global $DB;

	 if (date('w', time()) == 1)
		 $beginning_of_week = strtotime('Today',time());
	 else
		 $beginning_of_week = strtotime('last Monday',time());

	$sql = "select s.*,
				e.title, e.id as exampleid, e.source AS example_source, evis.visible,
				eval.student_evaluation, eval.teacher_evaluation, eval.evalniveauid, evis.courseid, s.id as scheduleid,
				e.externalurl, e.externaltask, e.description
			FROM {block_exacompschedule} s
			JOIN {block_exacompexamples} e ON e.id = s.exampleid
			JOIN {".\block_exacomp\DB_EXAMPVISIBILITY."} evis ON evis.exampleid= e.id AND evis.studentid=0 AND evis.visible = 1 AND evis.courseid=?
			LEFT JOIN {block_exacompexameval} eval ON eval.exampleid = s.exampleid AND eval.studentid = s.studentid AND eval.courseid = s.courseid
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
				eval.student_evaluation, eval.teacher_evaluation, eval.evalniveauid, evis.courseid, s.id as scheduleid
			FROM {block_exacompschedule} s
			JOIN {block_exacompexamples} e ON e.id = s.exampleid
			JOIN {".\block_exacomp\DB_EXAMPVISIBILITY."} evis ON evis.exampleid= e.id AND evis.studentid=0 AND evis.visible = 1 AND evis.courseid=?
			LEFT JOIN {block_exacompexameval} eval ON eval.exampleid = s.exampleid AND eval.studentid = s.studentid
			WHERE s.studentid = ? AND s.deleted = 1 AND (
				-- noch nicht auf einen tag geleg
				(s.start IS null OR s.start=0)
			)
			ORDER BY s.id";

	return $DB->get_records_sql($sql,array($courseid, $studentid));
}
function block_exacomp_set_example_start_end($scheduleid, $start, $end, $deleted = 0){
	global $DB, $USER;

	$entry = $DB->get_record(\block_exacomp\DB_SCHEDULE, array('id'=>$scheduleid));
	$entry->start = $start;
	$entry->end = $end;
	$entry->deleted = $deleted;

	if($entry->studentid != $USER->id)
		block_exacomp_require_teacher($entry->courseid);

	if ($DB instanceof pgsql_native_moodle_database) {
		// HACK: because moodle doesn't quote pgsql identifiers and pgsql doesn't allow end as column name
		$DB->execute('UPDATE {'.\block_exacomp\DB_SCHEDULE.'} SET "end"=? WHERE id=?', [$entry->end, $entry->id]);
		unset($entry->end);
	}

	$DB->update_record(\block_exacomp\DB_SCHEDULE, $entry);
}
function block_exacomp_copy_example_from_schedule($scheduleid){
	global $DB, $USER;

	$entry = $DB->get_record(\block_exacomp\DB_SCHEDULE, array('id' => $scheduleid));
	if($entry->studentid != $USER->id)
		block_exacomp_require_teacher($entry->courseid);

	unset($entry->id);
	unset($entry->start);
	unset($entry->end);
	
	$DB->insert_record(\block_exacomp\DB_SCHEDULE, $entry);
}
function block_exacomp_remove_example_from_schedule($scheduleid){
	global $DB, $USER;

	$entry = $DB->get_record(\block_exacomp\DB_SCHEDULE, array('id' => $scheduleid));
	
	if($entry->studentid != $USER->id)
		block_exacomp_require_teacher($entry->courseid);

	$DB->delete_records(\block_exacomp\DB_SCHEDULE, array('id'=>$scheduleid));
}

function block_exacomp_get_examples_for_start_end($courseid, $studentid, $start, $end){
	global $DB;

	$sql = "select s.*,
				e.title, e.id as exampleid, e.source AS example_source, evis.visible,
				eval.student_evaluation, eval.teacher_evaluation, eval.evalniveauid, s.courseid, s.id as scheduleid,
				e.externalurl, e.externaltask, e.description, evalniveau.title as niveau
			FROM {block_exacompschedule} s
			JOIN {block_exacompexamples} e ON e.id = s.exampleid
			JOIN {".\block_exacomp\DB_EXAMPVISIBILITY."} evis ON evis.exampleid= e.id AND evis.studentid=0 AND evis.visible = 1 AND evis.courseid=?
			LEFT JOIN {block_exacompexameval} eval ON eval.exampleid = s.exampleid AND eval.studentid = s.studentid 
			LEFT JOIN {block_exacompeval_niveau} evalniveau ON evalniveau.id = eval.evalniveauid
			WHERE s.studentid = ? AND s.courseid = ? AND (
				-- innerhalb end und start
				(s.start > ? AND s.end < ?)
			)
			-- GROUP BY s.id -- because a bug somewhere causes duplicate rows
			ORDER BY e.title";
	return $DB->get_records_sql($sql,array($courseid, $studentid, $courseid, $start, $end));
}

function block_exacomp_get_examples_for_start_end_all_courses($studentid, $start, $end){
	if($studentid < 0)
		$studentid = 0;
	
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
		$example_array['niveau'] = isset($example->niveau) ? $example->niveau : null;
		$example_array['description'] = isset($example->description) ? $example->description:"";
		
		
		if($mind_eval){
			$example_array['student_evaluation'] = $example->student_evaluation;
			$example_array['teacher_evaluation'] = $example->teacher_evaluation;
			
			$student_title = \block_exacomp\global_config::get_student_value_title_by_id($example->student_evaluation);
			$teacher_title = \block_exacomp\global_config::get_value_title_by_id($example->teacher_evaluation);
			
			$example_array['student_evaluation_title'] = (strcmp($student_title, ' ')==0)?'-':$student_title;
			$example_array['teacher_evaluation_title'] = (strcmp($teacher_title, ' ')==0)?'-':$teacher_title;
		}
		if(isset($example->state))
			$example_array['state'] = $example->state;

		$example_array['studentid'] = $example->studentid;
		$example_array['courseid'] = $example->courseid;
		$example_array['scheduleid'] = $example->scheduleid;
		$example_array['copy_url'] = $output->local_pix_icon("copy_example.png", get_string('copy'));

		$img = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/assoc_icon.png'), 'alt'=>get_string("competence_associations", "block_exacomp"), 'title'=>get_string("competence_associations", "block_exacomp"), 'height'=>16, 'width'=>16));

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
						html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/' . ((!$itemExists) ? 'manual_item.png' : 'reload.png')), 'alt'=>get_string("submission", "block_exacomp"), 'title'=>get_string("submission", "block_exacomp"))),
						array("target" => "_blank", "onclick" => "window.open(this.href,this.target,'width=880,height=660, scrollbars=yes'); return false;"));
			} else {
				$url = block_exacomp_get_viewurl_for_example ( $example->studentid, $USER->id, $example->exampleid );
				if ($url)
					$example_array ['submission_url'] = html_writer::link ( $url, html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/manual_item.png'), 'alt'=>get_string("submission", "block_exacomp"), 'title'=>get_string("submission", "block_exacomp"))), array (
							"target" => "_blank",
							"onclick" => "window.open(this.href,this.target,'width=880,height=660, scrollbars=yes'); return false;"
					) );
			}
		}
		if ($url = block_exacomp_get_file_url((object)array('id' => $example->exampleid), 'example_task')) {
			$example_array['task'] = html_writer::link($url, $output->preview_icon(),array("target" => "_blank"));
		}
		elseif(isset($example->externalurl)){
			$example_array['externalurl'] = html_writer::link(str_replace('&amp;','&',$example->externalurl), $output->preview_icon(),array("target" => "_blank"));
		}elseif(isset($example->externaltask)) {
			$example_array['externaltask'] = html_writer::link(str_replace('&amp;','&',$example->externaltask), $output->preview_icon(),array("target" => "_blank"));
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

	$timeentries = block_exacomp_get_timetable_entries();
	/*
	 * Split every unit into 4 pieces
	 */
	for($i=0; $i < $units * 4; $i++) {

		$entry = array();

		//only write at the begin of every unit
		if($i%4 == 0){
			$entry['name'] = ($i/4 + 1) . '. Einheit';
			$entry['time'] = (isset($timeentries[$i/4]))?$timeentries[$i/4]:'';
		}
		else{
			$entry['name'] = '';
			$entry['time'] = '';
		}
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

	$example = $DB->get_record(\block_exacomp\DB_EXAMPLES, array('id'=>$exampleid));
		if($example->blocking_event)
			return \block_exacomp\EXAMPLE_STATE_LOCKED_TIME;

	$comp = $DB->get_record(\block_exacomp\DB_EXAMPLEEVAL, array('courseid'=>$courseid, 'exampleid'=>$exampleid, 'studentid'=>$studentid));

	if($comp && $comp->teacher_evaluation !== null){
		if($comp->teacher_evaluation == 0)
			return \block_exacomp\EXAMPLE_STATE_EVALUATED_NEGATIV;

		return \block_exacomp\EXAMPLE_STATE_EVALUATED_POSITIV;
	}

	if(block_exacomp_exaportexists()) {
		$sql = "select * FROM {block_exacompitemexample} ie
				JOIN {block_exaportitem} i ON i.id = ie.itemid
				WHERE ie.exampleid = ? AND i.userid = ?";
	
		$items_examp = $DB->get_records_sql($sql,array($exampleid, $studentid));
	
		if($items_examp || ($comp && $comp->student_evaluation !== null && $comp->student_evaluation > 0))
			return \block_exacomp\EXAMPLE_STATE_SUBMITTED;
	}
	
	$schedule = $DB->get_records(\block_exacomp\DB_SCHEDULE, array('courseid'=>$courseid, 'exampleid'=>$exampleid, 'studentid'=>$studentid));

	if($schedule){
		$in_work = false;
		foreach($schedule as $entry){
			if($entry->start>0 && $entry->end > 0) {
				$in_work = true;
			}
		}

		if($in_work)
			return \block_exacomp\EXAMPLE_STATE_IN_CALENDAR;
		else
			return \block_exacomp\EXAMPLE_STATE_IN_POOL;
	}

	return \block_exacomp\EXAMPLE_STATE_NOT_SET;
}
function block_exacomp_in_pre_planing_storage($exampleid, $creatorid, $courseid){
	global $DB;

	if($DB->get_record(\block_exacomp\DB_SCHEDULE, array('exampleid'=>$exampleid, 'creatorid'=>$creatorid, 'courseid'=>$courseid, 'studentid'=>0)))
		return true;

	return false;
}
function block_exacomp_has_items_pre_planning_storage($creatorid, $courseid){
	global $DB;

	return $DB->get_records(\block_exacomp\DB_SCHEDULE, array('creatorid'=>$creatorid, 'courseid'=>$courseid, 'studentid'=>0));
}
function block_exacomp_get_pre_planning_storage($creatorid, $courseid){
	global $DB;

	$sql = "select s.*,
				e.title, e.id as exampleid, e.source AS example_source, evis.visible,
				evis.courseid, s.id as scheduleid
			FROM {".\block_exacomp\DB_SCHEDULE."} s
			JOIN {".\block_exacomp\DB_EXAMPLES."} e ON e.id = s.exampleid
			JOIN {".\block_exacomp\DB_EXAMPVISIBILITY."} evis ON evis.exampleid= e.id AND evis.studentid=0 AND evis.visible = 1 AND evis.courseid=?
			WHERE s.creatorid = ? AND s.studentid=0 AND (
				-- noch nicht auf einen tag geleg
				(s.start IS null OR s.start=0)
			)
			ORDER BY s.id";

	return $DB->get_records_sql($sql,array($courseid, $creatorid));
}
function block_exacomp_get_student_pool_examples($students, $courseid){
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

	$example = \block_exacomp\example::get($exampleid);
	if(!$example || !$DB->record_exists(\block_exacomp\DB_DESCEXAMP, array('exampid' => $exampleid,'descrid' => $descrid)))
		return false;

	$desc_examp = $DB->get_record(\block_exacomp\DB_DESCEXAMP, array('exampid' => $exampleid,'descrid' => $descrid));
	$example->descsorting = $desc_examp->sorting;

	if (\block_exacomp\require_item_capability(\block_exacomp\CAP_SORTING, $example)) {
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
			$DB->update_record(\block_exacomp\DB_DESCEXAMP, $desc_examp);

			$desc_examp = $DB->get_record(\block_exacomp\DB_DESCEXAMP, array('exampid' => $switchWith->id,'descrid' => $descrid));
			$desc_examp->sorting = $switchWith->descsorting;
			$DB->update_record(\block_exacomp\DB_DESCEXAMP, $desc_examp);

			return true;
		}
	}
	return false;
}
function block_exacomp_empty_pre_planning_storage($courseid){
	global $DB;

	$DB->delete_records(\block_exacomp\DB_SCHEDULE, array('courseid'=>$courseid, 'studentid'=>0));
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
function block_exacomp_get_studentid() {
	if(!block_exacomp_is_teacher())
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
function block_exacomp_get_message_icon($userid) {
	global $DB, $CFG, $COURSE;

	if($userid != BLOCK_EXACOMP_SHOW_ALL_STUDENTS) {
		require_once($CFG->dirroot . '/message/lib.php');

		$userto = $DB->get_record('user', array('id' => $userid));

		if (!$userto) {
			return;
		}

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
	if($DB->get_records_select('message_read', "useridfrom = ? AND useridto = ? AND contexturlname = ? AND timecreated > ?",
		array('useridfrom' => $userfrom->id, 'useridto' => $userto->id, 'contexturlname' => $context, (time()-5*60))))
		return;
	
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
function block_exacomp_send_submission_notification($userfrom, $userto, $example, $date, $time, $courseid) {
	global $CFG,$USER,$SITE;

	$subject = get_string('notification_submission_subject','block_exacomp',array('site'=>$SITE->fullname, 'student' => fullname($userfrom), 'example' => $example->title));

	$gridurl = block_exacomp_get_gridurl_for_example($courseid, $userto->id, $example->id);
	
	$message = get_string('notification_submission_body','block_exacomp',array('student' => fullname($userfrom), 'example' => $example->title, 'date' => $date, 'time' => $time, 'viewurl' => $gridurl, 'receiver'=>fullname($userto), 'site'=>$SITE->fullname));
	$context = get_string('notification_submission_context','block_exacomp');

	block_exacomp_send_notification("submission", $userfrom, $userto, $subject, $message, $context, $gridurl);
}
function block_exacomp_notify_all_teachers_about_submission($courseid, $exampleid, $timecreated) {
	global $USER, $DB;

	$teachers = block_exacomp_get_teachers_by_course($courseid);
	if($teachers) {
		foreach($teachers as $teacher) {
			block_exacomp_send_submission_notification($USER, $teacher, $DB->get_record(\block_exacomp\DB_EXAMPLES,array('id'=>$exampleid)), date("D, d.m.Y",$timecreated), date("H:s",$timecreated), $courseid);
		}
	}
}
function block_exacomp_send_self_assessment_notification($userfrom, $userto, $courseid) {
	global $CFG,$USER, $SITE;

	$course = get_course($courseid);

	$subject = get_string('notification_self_assessment_subject','block_exacomp',array('site'=>$SITE->fullname,'course' => $course->shortname));
	$message = get_string('notification_self_assessment_body','block_exacomp',array('course' => $course->fullname, 'student' => fullname($userfrom), 'receiver'=>fullname($userto), 'site'=>$SITE->fullname));
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
	global $CFG,$USER, $SITE;

	$course = get_course($courseid);

	$subject = get_string('notification_grading_subject','block_exacomp',array('site'=>$SITE->fullname,'course' => $course->shortname));
	$message = get_string('notification_grading_body','block_exacomp',array('course' => $course->fullname, 'teacher' => fullname($userfrom), 'receiver'=>fullname($userto), 'site'=>$SITE->fullname));
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
	global $CFG,$USER,$DB, $SITE;

	$course = get_course($courseid);
	$example = $DB->get_record(\block_exacomp\DB_EXAMPLES,array('id' => $exampleid));
	$subject = get_string('notification_weekly_schedule_subject','block_exacomp', array('site'=>$SITE->fullname));
	$message = get_string('notification_weekly_schedule_body','block_exacomp',array('course' => $course->fullname, 'teacher' => fullname($userfrom), 'receiver'=>fullname($userto), 'site'=>$SITE->fullname));
	$context = get_string('notification_weekly_schedule_context','block_exacomp');

	$viewurl = new moodle_url('/blocks/exacomp/weekly_schedule.php',array('courseid' => $courseid));

	block_exacomp_send_notification("weekly_schedule", $userfrom, $userto, $subject, $message, $context, $viewurl);
}
function block_exacomp_send_example_comment_notification($userfrom, $userto, $courseid, $exampleid) {
	global $CFG,$USER,$DB, $SITE;

	$course = get_course($courseid);
	$example = $DB->get_record(\block_exacomp\DB_EXAMPLES,array('id' => $exampleid));
	$subject = get_string('notification_example_comment_subject','block_exacomp', array('example' => $example->title, 'site'=>$SITE->fullname));
	$message = get_string('notification_example_comment_body','block_exacomp',array('course' => $course->fullname, 'teacher' => fullname($userfrom), 'example' => $example->title, 'receiver'=>fullname($userto), 'site'=>$SITE->fullname));
	$context = get_string('notification_example_comment_context','block_exacomp');

	$viewurl = block_exacomp_get_viewurl_for_example($userto->id, $userto->id, $example->id);

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

function block_exacomp_save_additional_grading_for_descriptor($courseid, $descriptorid, $studentid, $additionalinfo, $comptype = \block_exacomp\TYPE_DESCRIPTOR){
	global $DB, $USER;

	if($additionalinfo > 6.0)
		$additionalinfo = 6.0;
	elseif($additionalinfo < 1.0 && $additionalinfo != "")
		$additionalinfo = 1.0;
	
	$value = block_exacomp\global_config::get_additionalinfo_value_mapping($additionalinfo);
	$record = block_exacomp\get_comp_eval($courseid, \block_exacomp\ROLE_TEACHER, $studentid, $comptype, $descriptorid);
	
	// force additional info to be stored with a dot as decimal mark
	$additionalinfo = str_replace(",", ".", $additionalinfo);
	
	if($additionalinfo == '' || empty($additionalinfo))
		$additionalinfo = NULL;
	
	if($record){
		$record->additionalinfo = $additionalinfo;
		$record->value = $value;
		$DB->update_record(\block_exacomp\DB_COMPETENCES, $record);
	}else{
		$insert = new stdClass();
		$insert->compid = $descriptorid;
		$insert->userid = $studentid;
		$insert->courseid = $courseid;
		$insert->comptype = $comptype;
		$insert->additionalinfo = $additionalinfo;
		$insert->role = \block_exacomp\ROLE_TEACHER;
		$insert->reviewerid = $USER->id;
		$insert->value = $value;
		$insert->timestamp = time();
		$DB->insert_record(\block_exacomp\DB_COMPETENCES, $insert);
	}
}

function block_exacomp_save_additional_grading_for_example($courseid, $exampleid, $studentid, $additionalinfo) {
	global $DB, $USER;

	if($additionalinfo == -1)
		$additionalinfo = null;

	$record = $DB->get_record ( \block_exacomp\DB_EXAMPLEEVAL, array (
			'courseid' => $courseid,
			'exampleid' => $exampleid,
			'studentid' => $studentid
	) );
	if ($record) {
		$record->additionalinfo = $additionalinfo;
		$DB->update_record ( \block_exacomp\DB_EXAMPLEEVAL, $record );
	} else {
		$insert = new stdClass ();
		$insert->exampleid = $exampleid;
		$insert->studentid = $studentid;
		$insert->courseid = $courseid;
		$insert->teacher_reviewerid = $USER->id;
		$insert->additionalinfo = $additionalinfo;
		$DB->insert_record ( \block_exacomp\DB_EXAMPLEEVAL, $insert );
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
function block_exacomp_get_examples_by_course($courseid){
	$sql = "SELECT ex.*
		FROM {".\block_exacomp\DB_EXAMPLES."} ex
		WHERE ex.id IN (
			SELECT dex.exampid
			FROM {".\block_exacomp\DB_DESCEXAMP."} dex
			JOIN {".\block_exacomp\DB_DESCTOPICS."} det ON dex.descrid = det.descrid
			JOIN {".\block_exacomp\DB_COURSETOPICS."} ct ON det.topicid = ct.topicid
			WHERE ct.courseid = ?
		)";

	return g::$DB->get_records_sql($sql, array($courseid));
}
function block_exacomp_course_has_examples($courseid){
	$sql = "SELECT COUNT(*)
		FROM {".\block_exacomp\DB_EXAMPLES."} ex
		JOIN {".\block_exacomp\DB_DESCEXAMP."} dex ON ex.id = dex.exampid
		JOIN {".\block_exacomp\DB_DESCTOPICS."} det ON dex.descrid = det.descrid
		JOIN {".\block_exacomp\DB_COURSETOPICS."} ct ON det.topicid = ct.topicid
		WHERE ct.courseid = ?";

	return (bool)g::$DB->get_field_sql($sql, array($courseid));
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

function block_exacomp_create_blocking_event($courseid, $title, $creatorid, $studentid){
	global $DB;

	$example = new stdClass();
	$example->title = $title;
	$example->creatorid = $creatorid;
	$example->blocking_event = 1;

	$exampleid = $DB->insert_record(\block_exacomp\DB_EXAMPLES, $example);

	$schedule = new stdClass();
	$schedule->studentid = $studentid;
	$schedule->exampleid = $exampleid;
	$schedule->creatorid = $creatorid;
	$schedule->courseid = $courseid;

	$scheduleid = $DB->insert_record(\block_exacomp\DB_SCHEDULE, $schedule);

	$record = $DB->get_records(\block_exacomp\DB_EXAMPVISIBILITY, array('courseid'=>$courseid, 'exampleid'=>$exampleid, 'studentid'=>0, 'visible'=>1));
	if(!$record){
		$visibility = new stdClass();
		$visibility->courseid = $courseid;
		$visibility->exampleid = $exampleid;
		$visibility->studentid = 0;
		$visibility->visible = 1;

		$vibilityid = $DB->insert_record(\block_exacomp\DB_EXAMPVISIBILITY, $visibility);
	}
}

function block_exacomp_check_student_example_permission($courseid, $exampleid, $studentid){
	global $DB;
	
	return $DB->record_exists(\block_exacomp\DB_EXAMPVISIBILITY, array('courseid'=>$courseid, 'exampleid'=>$exampleid, 'studentid'=>$studentid, 'visible'=>1));
}

function block_exacomp_get_courseids_by_descriptor($descriptorid){
	$sql = 'SELECT ct.courseid
		FROM {'.\block_exacomp\DB_COURSETOPICS.'} ct 
		JOIN {'.\block_exacomp\DB_DESCTOPICS.'} dt ON ct.topicid = dt.topicid  
		WHERE dt.descrid = ?';
	
	return g::$DB->get_records_sql($sql, array($descriptorid));
}
/**
* get evaluation images for competence profile for teacher
* according to course scheme and admin scheme
**/
function block_exacomp_get_html_for_niveau_eval($evaluation){
	$evaluation_niveau_type = block_exacomp_evaluation_niveau_type();
	if($evaluation_niveau_type == 0)
		return;

	if($evaluation_niveau_type == 0)
		return;

	//predefined pictures 
		$grey_1_src = '/blocks/exacomp/pix/compprof_rating_teacher_grey_1_'.$evaluation_niveau_type.'.png';
		$grey_2_src = '/blocks/exacomp/pix/compprof_rating_teacher_grey_2_'.$evaluation_niveau_type.'.png';
		$grey_3_src = '/blocks/exacomp/pix/compprof_rating_teacher_grey_3_'.$evaluation_niveau_type.'.png';
		$one_src = '/blocks/exacomp/pix/compprof_rating_teacher_1_'.$evaluation_niveau_type.'.png';
		$two_src = '/blocks/exacomp/pix/compprof_rating_teacher_2_'.$evaluation_niveau_type.'.png';
		$three_src = '/blocks/exacomp/pix/compprof_rating_teacher_3_'.$evaluation_niveau_type.'.png';
		
		$image1 = $grey_1_src;
		$image2 = $grey_2_src;
		$image3 = $grey_3_src;
		if($evaluation > -1){
			if($evaluation == 1){
				$image1 = $one_src;
			}if($evaluation == 2){
				$image2 = $two_src;
			}if($evaluation == 3){
				$image3 = $three_src;
			}
		}
		
		return html_writer::empty_tag('img', array('src'=>new moodle_url($image1), 'width'=>'25', 'height'=>'25')).
				html_writer::empty_tag('img', array('src'=>new moodle_url($image2), 'width'=>'25', 'height'=>'25')).
				html_writer::empty_tag('img', array('src'=>new moodle_url($image3), 'width'=>'25', 'height'=>'25'));
}

/**
* get evaluation images for competence profile for students
* allways use starts so far, according to scheme
**/
function block_exacomp_get_html_for_student_eval($evaluation, $scheme){
	
	if(block_exacomp_additional_grading()){
		$image1 = '/blocks/exacomp/pix/compprof_rating_student_grey_1.png';
		$image2 = '/blocks/exacomp/pix/compprof_rating_student_grey_2.png';
		$image3 = '/blocks/exacomp/pix/compprof_rating_student_grey_3.png';
		$one_src = '/blocks/exacomp/pix/compprof_rating_student_1.png';
		$two_src = '/blocks/exacomp/pix/compprof_rating_student_2.png';
		$three_src = '/blocks/exacomp/pix/compprof_rating_student_3.png';
		
		if($evaluation > -1){
			if($evaluation == 1){
				$image1 = $one_src;
			}if($evaluation == 2){
				$image2 = $two_src;
			}if($evaluation == 3){
				$image3 = $three_src;
			}
		}
		
		return html_writer::empty_tag('img', array('src'=>new moodle_url($image1), 'width'=>'25', 'height'=>'25')).
				html_writer::empty_tag('img', array('src'=>new moodle_url($image2), 'width'=>'25', 'height'=>'25')).
				html_writer::empty_tag('img', array('src'=>new moodle_url($image3), 'width'=>'25', 'height'=>'25'));
	}
	
	$images = array();
	for($i=0;$i<$scheme;$i++){
		if($evaluation > -1 && $i <= $evaluation){
			$images[] = '/blocks/exacomp/pix/compprof_rating_student.png';
		}else{
			$images[] = '/blocks/exacomp/pix/compprof_rating_student_grey.png';
		}
	}

	$return = "";
	foreach($images as $image){
		$return .= html_writer::empty_tag('img', array('src'=>new moodle_url($image), 'width'=>'25', 'height'=>'25'));
	}
	
	return $return;
	
}

/**
 * get evaluation images for competence profile for students
 * allways use starts so far, according to scheme
 **/
function block_exacomp_get_html_for_teacher_eval($evaluation, $scheme){
	$images = array();

	if($evaluation == 0)
		$images[] = '/blocks/exacomp/pix/compprof_rating_teacher_0_1.png';
	else 
		$images[] = '/blocks/exacomp/pix/compprof_rating_teacher_grey_0_1.png';
	
	for($i=1;$i<=$scheme;$i++){
		if($evaluation > 0 && $i <= $evaluation){
			$images[] = '/blocks/exacomp/pix/compprof_rating_teacher.png';
		}else{
			$images[] = '/blocks/exacomp/pix/compprof_rating_teacher_grey.png';
		}
	}

	$return = "";
	foreach($images as $image){
		$return .= html_writer::empty_tag('img', array('src'=>new moodle_url($image), 'width'=>'25', 'height'=>'25'));
	}

	return $return;

}

function block_exacomp_get_grid_for_competence_profile($courseid, $studentid, $subjectid){
	global $DB;
	list($course_subjects, $table_column, $table_header, $selectedSubject, $selectedTopic, $selectedNiveau) = block_exacomp_init_overview_data($courseid, $subjectid, -1, 0, false, block_exacomp_is_teacher(), $studentid);
	
	$user = $DB->get_record('user', array('id'=>$studentid));
	$user = block_exacomp_get_user_information_by_course($user, $courseid);
	
	$competence_tree = block_exacomp_get_competence_tree($courseid, $subjectid, null, false, null, true, array(SHOW_ALL_TAXONOMIES), false, false, false, false, false, false);
	$table_content = new stdClass();
	$table_content->content = array();
		
	$scheme_items = \block_exacomp\global_config::get_value_titles(block_exacomp_get_grading_scheme($courseid));
	$evaluationniveau_items = \block_exacomp\global_config::get_evalniveaus();
	foreach($competence_tree as $subject){
		foreach($subject->topics as $topic){
			$table_content->content[$topic->id] = new stdClass();
			$table_content->content[$topic->id]->niveaus = array();
			$table_content->content[$topic->id]->span = 0;
			foreach($topic->descriptors as $descriptor){
				
				$evaluation = block_exacomp\get_comp_eval($courseid, \block_exacomp\ROLE_TEACHER, $studentid, \block_exacomp\TYPE_DESCRIPTOR, $descriptor->id);
				
				$niveau = $DB->get_record(\block_exacomp\DB_NIVEAUS, array('id'=>$descriptor->niveauid));
				if($niveau){
					$table_content->content[$topic->id]->niveaus[$niveau->title] = new stdClass();
					$table_content->content[$topic->id]->niveaus[$niveau->title]->evalniveau = ($evaluation)?
						((block_exacomp_use_eval_niveau())?
								(($evaluation->evalniveauid)?$evaluationniveau_items[$evaluation->evalniveauid].' ':'')
						:''):'';
						
					$table_content->content[$topic->id]->niveaus[$niveau->title]->evalniveauid = ($evaluation)?
						((block_exacomp_use_eval_niveau())?
								(($evaluation->evalniveauid)?$evaluation->evalniveauid:0)
						:0):0;
					
					$table_content->content[$topic->id]->niveaus[$niveau->title]->eval = ($evaluation) ? (((block_exacomp_additional_grading())?
								(($evaluation->additionalinfo)?$evaluation->additionalinfo:'')
						:$scheme_items[$evaluation->value])):'';
					
					$table_content->content[$topic->id]->niveaus[$niveau->title]->show = true;
					
					$table_content->content[$topic->id]->niveaus[$niveau->title]->visible = block_exacomp_is_descriptor_visible($courseid, $descriptor, $studentid);
					
					if($niveau->span == 1)
						$table_content->content[$topic->id]->span = 1;
				}
			}
			
			$table_content->content[$topic->id]->topic_evalniveau = 
						((block_exacomp_use_eval_niveau())?
								((isset($user->topics->niveau[$topic->id]))
										?$evaluationniveau_items[$user->topics->niveau[$topic->id]].' ':'')
						:'');
						
			$table_content->content[$topic->id]->topic_evalniveauid = 
						((block_exacomp_use_eval_niveau())?
								((isset($user->topics->niveau[$topic->id]))
										?$user->topics->niveau[$topic->id]:0)
						:0);
			
			$table_content->content[$topic->id]->topic_eval = 
						((block_exacomp_additional_grading())?
								((isset($user->topics->teacher_additional_grading[$topic->id]))
										?$user->topics->teacher_additional_grading[$topic->id]:'')
						:((isset($user->topics->teacher[$topic->id]))
								?$scheme_items[$user->topics->teacher[$topic->id]]:''));
			
			$table_content->content[$topic->id]->visible = block_exacomp_is_topic_visible($courseid, $topic, $studentid);
			
			$table_content->content[$topic->id]->topic_id = $topic->id;
		}
		$table_content->subject_evalniveau = 
					((block_exacomp_use_eval_niveau())?
								((isset($user->subjects->niveau[$subject->id]))
										?$evaluationniveau_items[$user->subjects->niveau[$subject->id]].' ':'')
						:'');
						
		$table_content->subject_evalniveauid = ((block_exacomp_use_eval_niveau())?
								((isset($user->subjects->niveau[$subject->id]))
										?$user->subjects->niveau[$subject->id]:0)
						:0);
						
		$table_content->subject_eval = ((block_exacomp_additional_grading())?
								((isset($user->subjects->teacher_additional_grading[$subject->id]))
										?$user->subjects->teacher_additional_grading[$subject->id]:'')
						:((isset($user->subjects->teacher[$subject->id]))
								?$scheme_items[$user->subjects->teacher[$subject->id]]:''));
		
		$table_content->subject_title = $subject->title;
	}
	
	foreach($table_header as $key => $niveau){
		if(isset($niveau->span) && $niveau->span == 1)
			unset($table_header[$key]);
		
		elseif($niveau->id != \block_exacomp\SHOW_ALL_NIVEAUS)
			foreach($table_content->content as $row){
				if($row->span != 1){
					if(!array_key_exists($niveau->title, $row->niveaus)){
						$row->niveaus[$niveau->title] = new stdClass();
						$row->niveaus[$niveau->title]->eval = '';
						$row->niveaus[$niveau->title]->evalniveau = '';
						$row->niveaus[$niveau->title]->evalniveauid = 0;
						$row->niveaus[$niveau->title]->show = false;
						$row->niveaus[$niveau->title]->visible = true;
					}
				}
			}
	}
	
	foreach($table_content->content as $row){
		#sort crosssub entries
		ksort($row->niveaus);
	}
	
	return array($course_subjects, $table_column, $table_header, $table_content);
}

function block_exacomp_get_competence_profile_grid_for_ws($courseid, $userid, $subjectid){
	global $DB;
	list($course_subjects, $table_rows, $table_header, $table_content) = block_exacomp_get_grid_for_competence_profile($courseid, $userid, $subjectid);

	$spanning_niveaus = $DB->get_records(\block_exacomp\DB_NIVEAUS,array('span' => 1));
	//calculate the col span for spanning niveaus
	$spanning_colspan = block_exacomp_calculate_spanning_niveau_colspan($table_header, $spanning_niveaus);
	
	$table = new stdClass();
	$table->rows = array();
	
	$header_row = new stdClass();
	$header_row->columns = array();
	
	$header_row->columns[0] = new stdClass();
	$header_row->columns[0]->text = $table_content->subject_title;
	$header_row->columns[0]->span = 0;
	
	$current_idx = 1;
	foreach($table_header as $element){
		if($element->id != \block_exacomp\SHOW_ALL_NIVEAUS){
			$header_row->columns[$current_idx] = new stdClass();
			$header_row->columns[$current_idx]->text = $element->title;
			$header_row->columns[$current_idx]->span = 0;
			$current_idx++;
		}
	}
	
	if(block_exacomp_is_topicgrading_enabled()){
		$topic_eval_header = new stdClass();
		$topic_eval_header->text = get_string('total', 'block_exacomp');
		$topic_eval_header->span = 0;
		$header_row->columns[$current_idx] = $topic_eval_header;
	}
	
	$table->rows[] = $header_row;

	
	foreach($table_content->content as $topic => $rowcontent ){
		$topic_visibility_check = new stdClass();
		$topic_visibility_check->id = $rowcontent->topic_id;
		$content_row = new stdClass();
		$content_row->columns = array();
		
		$content_row->columns[0] = new stdClass();
		$content_row->columns[0]->text = block_exacomp_get_topic_numbering($topic) . " " . $table_rows[$topic]->title;
		$content_row->columns[0]->span = 0;
		$content_row->columns[0]->visible = $rowcontent->visible;
		
		$current_idx = 1;
		foreach($rowcontent->niveaus as $niveau => $element){
			$content_row->columns[$current_idx] = new stdClass();
			$content_row->columns[$current_idx]->evaluation = ( empty($element->eval) || strlen(trim($element->eval)) == 0 )?-1:$element->eval;
			$content_row->columns[$current_idx]->evalniveauid = $element->evalniveauid;
			$content_row->columns[$current_idx]->show = $element->show;
			$content_row->columns[$current_idx]->visible = ((!$element->visible || !$rowcontent->visible)?false:true);
			$content_row->columns[$current_idx]->evaluation_mapped = \block_exacomp\global_config::get_additionalinfo_value_mapping($element->eval);
			
			if(array_key_exists($niveau, $spanning_niveaus)){
				$content_row->columns[$current_idx]->span = $spanning_colspan;
			}else{
				$content_row->columns[$current_idx]->span = 0;
			}	 
			$current_idx++;
		}
		
		if(block_exacomp_is_topicgrading_enabled()){
			$topic_eval = new stdClass();
			$topic_eval->evaluation_text = \block_exacomp\global_config::get_value_title_by_id(\block_exacomp\global_config::get_additionalinfo_value_mapping($rowcontent->topic_eval));
			$topic_eval->evaluation = empty($rowcontent->topic_eval)?-1:$rowcontent->topic_eval;
			$topic_eval->evaluation_mapped = \block_exacomp\global_config::get_additionalinfo_value_mapping($rowcontent->topic_eval);
			$topic_eval->evalniveauid = $rowcontent->topic_evalniveauid;
			$topic_eval->topicid = $rowcontent->topic_id;
			$topic_eval->span = 0;
			$topic_eval->visible = $rowcontent->visible;
			$content_row->columns[$current_idx] = $topic_eval;
		}
		
		$table->rows[] = $content_row;
	}
	
	if(block_exacomp_is_subjectgrading_enabled()){
		$content_row = new stdClass();
		$content_row->columns = array();
		
		$content_row->columns[0] = new stdClass();
		$content_row->columns[0]->text = get_string('total', 'block_exacomp');
		$content_row->columns[0]->span = count($table_header);
	
		$content_row->columns[1] = new stdClass();
		$content_row->columns[1]->evaluation = empty($table_content->subject_eval)?-1:$table_content->subject_eval;
		$content_row->columns[1]->evaluation_text = \block_exacomp\global_config::get_value_title_by_id(\block_exacomp\global_config::get_additionalinfo_value_mapping($table_content->subject_eval));
		$content_row->columns[1]->evaluation_mapped = \block_exacomp\global_config::get_additionalinfo_value_mapping($table_content->subject_eval);
		$content_row->columns[1]->evalniveauid = $table_content->subject_evalniveauid;
		$content_row->columns[1]->span = 0;
		
		$table->rows[] = $content_row;
	}
	
	return $table;
}

function block_exacomp_map_value_to_grading($course){
	global $DB;
	
	$mapping =  \block_exacomp\global_config::get_values_additionalinfo_mapping();
	
	//TOPIC, SUBJECT, CROSSSUBJECT, DESCRIPTOR
	$select = 'courseid = ? AND role = ?'; //is put into the where clause
	$params = array($course, \block_exacomp\ROLE_TEACHER);
	$results = $DB->get_records_select(\block_exacomp\DB_COMPETENCES, $select, $params);
	
	foreach($results as $result){
		if(!$result->additionalinfo && $result->value > -1){
			if($result->comptype == \block_exacomp\TYPE_DESCRIPTOR)
				$descriptor = $DB->get_record(\block_exacomp\DB_DESCRIPTORS, array('id'=>$result->compid));
			
			if($result->comptype != \block_exacomp\TYPE_DESCRIPTOR || $descriptor->parentid == 0){
				$result->additionalinfo = @$mapping[$result->value];
				$DB->update_record(\block_exacomp\DB_COMPETENCES, $result);
			}
		}
	}
}

/**
 * return all visible descriptors for a subject in course and user context with only one sql query
 * parts of this query dealing with the visibility could replace is_descriptor_visible
 * 
 * @param int $courseid
 * @param int $subjectid
 * @param int $userid
 * @param string $parent true for parent, false for child descriptors
 * 
 * @return: {{id, title}, {...}}
 */
function block_exacomp_get_visible_descriptors_for_subject($courseid, $subjectid, $userid=0, $parent=true){
	global $DB;
	
	$sql = "SELECT DISTINCT d.id, d.title FROM {".\block_exacomp\DB_DESCRIPTORS."} d
		LEFT JOIN {".\block_exacomp\DB_DESCTOPICS."} dt ON d.id = dt.descrid
		LEFT JOIN {".\block_exacomp\DB_COURSETOPICS."} ct ON dt.topicid = ct.topicid
		LEFT JOIN {".\block_exacomp\DB_DESCVISIBILITY."} dv ON d.id = dv.descrid AND dv.courseid = ct.courseid
		LEFT JOIN {".\block_exacomp\DB_TOPICVISIBILITY."} tv ON dt.topicid = tv.topicid AND tv.courseid = ct.courseid
		LEFT JOIN {".\block_exacomp\DB_TOPICS."} t ON ct.topicid = t.id
		WHERE ct.courseid = ? AND t.subjid = ? AND 
				
		".(($parent)?"d.parentid = 0":"d.parentid!=0")."
						
		AND ((dv.visible = 1 AND dv.studentid = 0 AND NOT EXISTS 
		  (SELECT *
		   FROM {".\block_exacomp\DB_DESCVISIBILITY."} dvsub
		   WHERE dvsub.descrid = dv.descrid AND dvsub.courseid = dv.courseid AND dvsub.visible = 0 AND dvsub.studentid = ?)) 
		   OR (dv.visible = 1 AND dv.studentid = ? AND NOT EXISTS 
		  (SELECT *
		   FROM {".\block_exacomp\DB_DESCVISIBILITY."} dvsub
		   WHERE dvsub.descrid = dv.descrid AND dvsub.courseid = dv.courseid AND dvsub.visible = 0 AND dvsub.studentid = 0)))
		   
		AND ((tv.visible = 1 AND tv.studentid = 0 AND NOT EXISTS 
		  (SELECT *
		   FROM {".\block_exacomp\DB_TOPICVISIBILITY."} tvsub
		   WHERE tvsub.topicid = tv.topicid AND tvsub.courseid = tv.courseid AND tvsub.visible = 0 AND tvsub.studentid = ?)) 
		   OR (tv.visible = 1 AND tv.studentid = ? AND NOT EXISTS 
		  (SELECT *
		   FROM {".\block_exacomp\DB_TOPICVISIBILITY."} tvsub
		   WHERE tvsub.topicid = tv.topicid AND tvsub.courseid = tv.courseid AND tvsub.visible = 0 AND tvsub.studentid = 0)))";
			
	$params = array($courseid, $subjectid, $userid, $userid, $userid, $userid);
	
	return $DB->get_records_sql($sql, $params);
}

/**
 * return all visible examples for a subject in course and user context with only one sql query
 * parts of this query dealing with the visibility could replace is_example_visible
 * 
 * @param int $courseid
 * @param int $subjectid
 * @param number $userid
 * 
 * @return {{id, title}, {...}}
 */
function block_exacomp_get_visible_examples_for_subject($courseid, $subjectid, $userid=0){
	global $DB;

	$sql = "SELECT DISTINCT e.id, e.title FROM {".\block_exacomp\DB_EXAMPLES."} e
		LEFT JOIN {".\block_exacomp\DB_DESCEXAMP."} de ON e.id = de.exampid
		LEFT JOIN {".\block_exacomp\DB_DESCTOPICS."} dt ON de.descrid = dt.descrid
		LEFT JOIN {".\block_exacomp\DB_COURSETOPICS."} ct ON dt.topicid = ct.topicid
		LEFT JOIN {".\block_exacomp\DB_EXAMPVISIBILITY."} ev ON e.id = ev.exampleid AND ev.courseid = ct.courseid
		LEFT JOIN {".\block_exacomp\DB_DESCVISIBILITY."} dv ON de.descrid = dv.descrid AND dv.courseid = ct.courseid
		LEFT JOIN {".\block_exacomp\DB_TOPICVISIBILITY."} tv ON dt.topicid = tv.topicid AND tv.courseid = ct.courseid
		LEFT JOIN {".\block_exacomp\DB_TOPICS."} t ON ct.topicid = t.id
		
		WHERE ct.courseid = ? AND t.subjid = ?
		
		AND ((ev.visible = 1 AND ev.studentid = 0 AND NOT EXISTS 
		  (SELECT *
		   FROM {".\block_exacomp\DB_EXAMPVISIBILITY."} evsub
		   WHERE evsub.exampleid = ev.exampleid AND evsub.courseid = ev.courseid AND evsub.visible = 0 AND evsub.studentid = ?)) 
		   OR (ev.visible = 1 AND ev.studentid = ? AND NOT EXISTS 
		  (SELECT *
		   FROM {".\block_exacomp\DB_EXAMPVISIBILITY."} evsub
		   WHERE evsub.exampleid = ev.exampleid AND evsub.courseid = ev.courseid AND evsub.visible = 0 AND evsub.studentid = 0)))
		
		AND ((dv.visible = 1 AND dv.studentid = 0 AND NOT EXISTS
		  (SELECT *
		   FROM {".\block_exacomp\DB_DESCVISIBILITY."} dvsub
		   WHERE dvsub.descrid = dv.descrid AND dvsub.courseid = dv.courseid AND dvsub.visible = 0 AND dvsub.studentid = ?))
		   OR (dv.visible = 1 AND dv.studentid = ? AND NOT EXISTS
		  (SELECT *
		   FROM {".\block_exacomp\DB_DESCVISIBILITY."} dvsub
		   WHERE dvsub.descrid = dv.descrid AND dvsub.courseid = dv.courseid AND dvsub.visible = 0 AND dvsub.studentid = 0)))
		 
		AND ((tv.visible = 1 AND tv.studentid = 0 AND NOT EXISTS
		  (SELECT *
		   FROM {".\block_exacomp\DB_TOPICVISIBILITY."} tvsub
		   WHERE tvsub.topicid = tv.topicid AND tvsub.courseid = tv.courseid AND tvsub.visible = 0 AND tvsub.studentid = ?))
		   OR (tv.visible = 1 AND tv.studentid = ? AND NOT EXISTS
		  (SELECT *
		   FROM {".\block_exacomp\DB_TOPICVISIBILITY."} tvsub
		   WHERE tvsub.topicid = tv.topicid AND tvsub.courseid = tv.courseid AND tvsub.visible = 0 AND tvsub.studentid = 0)))";

	$params = array($courseid, $subjectid, $userid, $userid, $userid, $userid, $userid, $userid);

	return $DB->get_records_sql($sql, $params);
}
/**
 * get evaluation statistics for a user in course and subject context for descriptor, childdescriptor and examples
 * within given timeframe (if start and end = 0, all available data is used)
 * global use of evaluation_niveau is minded here
 * 
 * @param int $courseid
 * @param int $subjectid
 * @param int $userid - not working for userid = 0 : no user_information available
 * @return array("descriptor_evaluation", "child_evaluation", "example_evaluation") 
 * this is representing the resulting matrix, use of evaluation niveaus is minded here 
 */
function block_exacomp_get_evaluation_statistic_for_subject($courseid, $subjectid, $userid, $start_timestamp = 0, $end_timestamp = 0){
	global $DB;
	
	$user = $DB->get_record("user", array("id"=>$userid));

	$user = block_exacomp_get_user_information_by_course($user, $courseid);

	//TODO: is visibility hier fürn hugo? Bewertungen kann es eh nur für sichtbare geben ...
	$descriptors = block_exacomp_get_visible_descriptors_for_subject($courseid, $subjectid, $userid);
	$child_descriptors = block_exacomp_get_visible_descriptors_for_subject($courseid, $subjectid, $userid, false);
	$examples = block_exacomp_get_visible_examples_for_subject($courseid, $subjectid, $userid);
	
	$descriptorgradings = array(); //array[niveauid][value][number of examples evaluated with this value and niveau]
	$childgradings = array();
	$examplegradings = array();
	
	//create grading statistic
	$scheme_items = \block_exacomp\global_config::get_value_titles(block_exacomp_get_grading_scheme($courseid));
	$evaluationniveau_items = (block_exacomp_use_eval_niveau())?\block_exacomp\global_config::get_evalniveaus():array('-1'=>'');
	
	foreach($evaluationniveau_items as $niveaukey => $niveauitem){
		$descriptorgradings[$niveaukey] = array();
		$childgradings[$niveaukey] = array();
		$examplegradings[$niveaukey] = array();
		
		foreach($scheme_items as $schemekey => $schemetitle){
			if($schemekey > -1){
				$descriptorgradings[$niveaukey][$schemekey] = 0;
				$childgradings[$niveaukey][$schemekey] = 0;
				$examplegradings[$niveaukey][$schemekey] = 0;
			}
		}
	}

	foreach($descriptors as $descriptor){
		//check if grading is within timeframe
		if(isset($user->competencies->timestamp_teacher[$descriptor->id]) && 
				($start_timestamp == 0 || $user->competencies->timestamp_teacher[$descriptor->id] >= $start_timestamp) && 
				($end_timestamp == 0 || $user->competencies->timestamp_teacher[$descriptor->id] <= $endtimestamp)){
			
			//check if niveau is given in evaluation, if not -1
			$niveaukey = (block_exacomp_use_eval_niveau() && isset($user->competencies->niveau[$descriptor->id]))?$user->competencies->niveau[$descriptor->id]:-1;
			if(isset($user->competencies->teacher[$descriptor->id]) && $user->competencies->teacher[$descriptor->id]>-1) //increase counter in statistic
				$descriptorgradings[$niveaukey][$user->competencies->teacher[$descriptor->id]]++;
		}
	}
	
	foreach($child_descriptors as $child){
		//check if grading is within timeframe
		if(isset($user->competencies->timestamp_teacher[$child->id]) &&
				($start_timestamp == 0 || $user->competencies->timestamp_teacher[$child->id] >= $start_timestamp) &&
				($end_timestamp == 0 || $user->competencies->timestamp_teacher[$child->id] <= $endtimestamp)){
		
			//check if niveau is given in evaluation, if not -1
			$niveaukey = (block_exacomp_use_eval_niveau() && isset($user->competencies->niveau[$child->id]))?$user->competencies->niveau[$child->id]:-1;
			if(isset($user->competencies->teacher[$child->id]) && $user->competencies->teacher[$child->id]>-1) //increase counter in statistic
				$childgradings[$niveaukey][$user->competencies->teacher[$child->id]]++;
		}
	}
	
	foreach($examples as $example){
		//create grading statistic for example
		if(isset($user->examples->timestamp_teacher[$example->id]) &&
				($start_timestamp == 0 || $user->examples->timestamp_teacher[$example->id] >= $start_timestamp) &&
				($end_timestamp == 0 || $user->examples->timestamp_teacher[$example->id] <= $endtimestamp)){

			//check if niveau is given in evaluation, if not -1
			$niveaukey = (block_exacomp_use_eval_niveau() && isset($user->examples->niveau[$example->id]))?$user->examples->niveau[$example->id]:-1;
			if(isset($user->examples->teacher[$example->id]) && $user->examples->teacher[$example->id]>-1) //increase counter in statistic
				$examplegradings[$niveaukey][$user->examples->teacher[$example->id]]++;
		}
		
	}
	
	return array("descriptor_evaluations" => $descriptorgradings, "child_evaluations"=>$childgradings, "example_evaluations" => $examplegradings);	
}

/**
 * get evaluation statistics for a user in course and subject context for descriptor, childdescriptor and examples
 * global use of evaluation_niveau is minded here
 *
 * @param int $courseid
 * @param int $topic
 * @param int $userid - not working for userid = 0 : no user_information available
 * @return descriptor_evaluation_list this is a list of niveautitles of all evaluated descriptors with according evaluation value and evaluation niveau
 */
function block_exacomp_get_descriptor_statistic_for_topic($courseid, $topicid, $userid, $start_timestamp = 0, $end_timestamp = 0){
	global $DB;

	$user = $DB->get_record("user", array("id"=>$userid));

	$user = block_exacomp_get_user_information_by_course($user, $courseid);
	$descriptors = block_exacomp_get_descriptors_by_topic($courseid, $topicid);
	
	#sort crosssub entries
	usort($descriptors, "block_exacomp_cmp_niveausort");
	
	$descriptorgradings = array(); //array[niveauid][value][number of examples evaluated with this value and niveau]
	
	foreach($descriptors as $descriptor){
		$teacher_eval_within_timeframe = (isset($user->competencies->timestamp_teacher[$descriptor->id]) &&
				($start_timestamp == 0 || $user->competencies->timestamp_teacher[$descriptor->id] >= $start_timestamp) &&
				($end_timestamp == 0 || $user->competencies->timestamp_teacher[$descriptor->id] <= $end_timestamp));
		$student_eval_within_timeframe = (isset($user->competencies->timestamp_student[$descriptor->id]) &&
				($start_timestamp == 0 || $user->competencies->timestamp_student[$descriptor->id] >= $start_timestamp) &&
				($end_timestamp == 0 || $user->competencies->timestamp_student[$descriptor->id] <= $end_timestamp));
		
		$descriptorgradings[$descriptor->cattitle] = new stdClass();
		$descriptorgradings[$descriptor->cattitle]->teachervalue = ((isset($user->competencies->teacher[$descriptor->id]) && $teacher_eval_within_timeframe)? $user->competencies->teacher[$descriptor->id]:-1);
		$descriptorgradings[$descriptor->cattitle]->evalniveau = ((isset($user->competencies->niveau[$descriptor->id]) && $teacher_eval_within_timeframe)? $user->competencies->niveau[$descriptor->id]:-1);	
		$descriptorgradings[$descriptor->cattitle]->studentvalue = ((isset($user->competencies->student[$descriptor->id]) && $student_eval_within_timeframe) ? $user->competencies->student[$descriptor->id]:-1);
	}

	return array("descriptor_evaluation" => $descriptorgradings);
}

function block_exacomp_get_visible_own_and_child_examples_for_descriptor($courseid, $descriptorid, $userid){
	global $DB;
	$sql = 'SELECT DISTINCT e.id, e.title, e.sorting FROM {'.\block_exacomp\DB_EXAMPLES.'} e 
		JOIN {'.\block_exacomp\DB_DESCEXAMP.'} de ON de.exampid = e.id
		JOIN {'.\block_exacomp\DB_DESCRIPTORS.'} d ON de.descrid = d.id
		LEFT JOIN {'.\block_exacomp\DB_EXAMPVISIBILITY.'} ev ON e.id = ev.exampleid AND ev.courseid = ?
		WHERE e.blocking_event = 0 AND d.id IN (
				SELECT dsub.id FROM {'.\block_exacomp\DB_DESCRIPTORS.'} dsub 
                LEFT JOIN {'.\block_exacomp\DB_DESCVISIBILITY.'} dv ON dsub.id = dv.descrid AND dv.courseid = ? 
                WHERE dsub.id = ? OR dsub.parentid = ?
                AND ((dv.visible = 1 AND dv.studentid = 0 AND NOT EXISTS (
                		SELECT * FROM {'.\block_exacomp\DB_DESCVISIBILITY.'} dvsub
		  				WHERE dvsub.descrid = dv.descrid AND dvsub.courseid = dv.courseid AND dvsub.visible = 0 AND dvsub.studentid = ?))
		  		OR (dv.visible = 1 AND dv.studentid = ? AND NOT EXISTS (
                		SELECT * FROM {'.\block_exacomp\DB_DESCVISIBILITY.'} dvsub
		  			 	WHERE dvsub.descrid = dv.descrid AND dvsub.courseid = dv.courseid AND dvsub.visible = 0 AND dvsub.studentid = 0)))
		)
 		AND ((ev.visible = 1 AND ev.studentid = 0 AND NOT EXISTS (
              	SELECT * FROM {'.\block_exacomp\DB_EXAMPVISIBILITY.'} evsub
   				WHERE evsub.exampleid = ev.exampleid AND evsub.courseid = ev.courseid AND evsub.visible = 0 AND evsub.studentid = ?)) 
   		OR (ev.visible = 1 AND ev.studentid = ? AND NOT EXISTS (
              	SELECT * FROM {'.\block_exacomp\DB_EXAMPVISIBILITY.'} evsub
  				WHERE evsub.exampleid = ev.exampleid AND evsub.courseid = ev.courseid AND evsub.visible = 0 AND evsub.studentid =0)))';
	
	$examples = $DB->get_records_sql($sql, array($courseid, $courseid, $descriptorid, $descriptorid, $userid, $userid, $userid, $userid));
	
	foreach($examples as $example){
		$example->state = block_exacomp_get_dakora_state_for_example($courseid, $example->id, $userid);
	}
	
	usort($examples, "block_exacomp_cmp_statetitlesort");
	return $examples;
}

/**
 * return all visible examples for a course and user context with only one sql query
 *
 * @param int $courseid
 * @param number $userid
 *
 * @return {{id}, {...}}
 */
function block_exacomp_get_example_visibilities_for_course_and_user($courseid, $userid = 0){
	global $DB;

	$sql = "SELECT DISTINCT e.id FROM {".\block_exacomp\DB_EXAMPLES."} e
		LEFT JOIN {".\block_exacomp\DB_DESCEXAMP."} de ON e.id = de.exampid
		LEFT JOIN {".\block_exacomp\DB_DESCTOPICS."} dt ON de.descrid = dt.descrid
		LEFT JOIN {".\block_exacomp\DB_COURSETOPICS."} ct ON dt.topicid = ct.topicid
		LEFT JOIN {".\block_exacomp\DB_EXAMPVISIBILITY."} ev ON e.id = ev.exampleid AND ev.courseid = ct.courseid
		LEFT JOIN {".\block_exacomp\DB_DESCVISIBILITY."} dv ON de.descrid = dv.descrid AND dv.courseid = ct.courseid
		LEFT JOIN {".\block_exacomp\DB_TOPICVISIBILITY."} tv ON dt.topicid = tv.topicid AND tv.courseid = ct.courseid
		LEFT JOIN {".\block_exacomp\DB_TOPICS."} t ON ct.topicid = t.id

		WHERE ct.courseid = ? 

		AND ((ev.visible = 1 AND ev.studentid = 0 AND NOT EXISTS
		  (SELECT *
		   FROM {".\block_exacomp\DB_EXAMPVISIBILITY."} evsub
		   WHERE evsub.exampleid = ev.exampleid AND evsub.courseid = ev.courseid AND evsub.visible = 0 AND evsub.studentid = ?))
		   OR (ev.visible = 1 AND ev.studentid = ? AND NOT EXISTS
		  (SELECT *
		   FROM {".\block_exacomp\DB_EXAMPVISIBILITY."} evsub
		   WHERE evsub.exampleid = ev.exampleid AND evsub.courseid = ev.courseid AND evsub.visible = 0 AND evsub.studentid = 0)))

		AND ((dv.visible = 1 AND dv.studentid = 0 AND NOT EXISTS
		  (SELECT *
		   FROM {".\block_exacomp\DB_DESCVISIBILITY."} dvsub
		   WHERE dvsub.descrid = dv.descrid AND dvsub.courseid = dv.courseid AND dvsub.visible = 0 AND dvsub.studentid = ?))
		   OR (dv.visible = 1 AND dv.studentid = ? AND NOT EXISTS
		  (SELECT *
		   FROM {".\block_exacomp\DB_DESCVISIBILITY."} dvsub
		   WHERE dvsub.descrid = dv.descrid AND dvsub.courseid = dv.courseid AND dvsub.visible = 0 AND dvsub.studentid = 0)))
		
		AND ((tv.visible = 1 AND tv.studentid = 0 AND NOT EXISTS
		  (SELECT *
		   FROM {".\block_exacomp\DB_TOPICVISIBILITY."} tvsub
		   WHERE tvsub.topicid = tv.topicid AND tvsub.courseid = tv.courseid AND tvsub.visible = 0 AND tvsub.studentid = ?))
		   OR (tv.visible = 1 AND tv.studentid = ? AND NOT EXISTS
		  (SELECT *
		   FROM {".\block_exacomp\DB_TOPICVISIBILITY."} tvsub
		   WHERE tvsub.topicid = tv.topicid AND tvsub.courseid = tv.courseid AND tvsub.visible = 0 AND tvsub.studentid = 0)))";

	$params = array($courseid, $userid, $userid, $userid, $userid, $userid, $userid);

	return $DB->get_records_sql($sql, $params);
}
/**
 * get all visible descriptors for all course students -> improved performance
 * @param unknown $courseid
 * @return {[userid]=>[{id}, {id},...]}
 */
function block_exacomp_get_example_visibilities_for_course($courseid){
	$user_visibilites = array();
	$students = block_exacomp_get_students_by_course($courseid);

	foreach($students as $student){
		$user_visibilites[$student->id] = block_exacomp_get_example_visibilities_for_course_and_user($courseid, $student->id);
	}
	$user_visibilites[0] = block_exacomp_get_example_visibilities_for_course_and_user($courseid);
	return $user_visibilites;
}

/**
 * return all visible descriptors (parent & child) for a course and user context with only one sql query
 * 
 * @param int $courseid
 * @param int $userid if userid == 0 -> only visibility for all is minded, not user related:
 * 						 used in assign_competencies when all sutdents are selected
 *
 * @return: {{id}, {...}}
 */
function block_exacomp_get_descriptor_visibilities_for_course_and_user($courseid, $userid = 0){
	global $DB;

	$sql = "SELECT DISTINCT d.id FROM {".\block_exacomp\DB_DESCRIPTORS."} d
		LEFT JOIN {".\block_exacomp\DB_DESCTOPICS."} dt ON d.id = dt.descrid
		LEFT JOIN {".\block_exacomp\DB_COURSETOPICS."} ct ON dt.topicid = ct.topicid
		LEFT JOIN {".\block_exacomp\DB_DESCVISIBILITY."} dv ON d.id = dv.descrid AND dv.courseid = ct.courseid
		LEFT JOIN {".\block_exacomp\DB_TOPICVISIBILITY."} tv ON dt.topicid = tv.topicid AND tv.courseid = ct.courseid
		LEFT JOIN {".\block_exacomp\DB_TOPICS."} t ON ct.topicid = t.id
		WHERE ct.courseid = ? 

		AND ((dv.visible = 1 AND dv.studentid = 0 AND NOT EXISTS
		  (SELECT *
		   FROM {".\block_exacomp\DB_DESCVISIBILITY."} dvsub
		   WHERE dvsub.descrid = dv.descrid AND dvsub.courseid = dv.courseid AND dvsub.visible = 0 AND dvsub.studentid = ?))
		   OR (dv.visible = 1 AND dv.studentid = ? AND NOT EXISTS
		  (SELECT *
		   FROM {".\block_exacomp\DB_DESCVISIBILITY."} dvsub
		   WHERE dvsub.descrid = dv.descrid AND dvsub.courseid = dv.courseid AND dvsub.visible = 0 AND dvsub.studentid = 0)))
		 
		AND ((tv.visible = 1 AND tv.studentid = 0 AND NOT EXISTS
		  (SELECT *
		   FROM {".\block_exacomp\DB_TOPICVISIBILITY."} tvsub
		   WHERE tvsub.topicid = tv.topicid AND tvsub.courseid = tv.courseid AND tvsub.visible = 0 AND tvsub.studentid = ?))
		   OR (tv.visible = 1 AND tv.studentid = ? AND NOT EXISTS
		  (SELECT *
		   FROM {".\block_exacomp\DB_TOPICVISIBILITY."} tvsub
		   WHERE tvsub.topicid = tv.topicid AND tvsub.courseid = tv.courseid AND tvsub.visible = 0 AND tvsub.studentid = 0)))";
		
	$params = array($courseid, $userid, $userid, $userid, $userid);

	return $DB->get_records_sql($sql, $params);
}

/**
 * get all visible descriptors for all course students -> improved performance
 * @param unknown $courseid
 * @return {[userid]=>[{id}, {id},...]}
 */
function block_exacomp_get_descriptor_visibilities_for_course($courseid){
	$user_visibilites = array();
	$students = block_exacomp_get_students_by_course($courseid);
	
	foreach($students as $student){
		$user_visibilites[$student->id] = block_exacomp_get_descriptor_visibilities_for_course_and_user($courseid, $student->id);
	}
	$user_visibilites[0] = block_exacomp_get_descriptor_visibilities_for_course_and_user($courseid);
	return $user_visibilites;
}

/**
 * return all visible topics for a course and user context with only one sql query
 *
 * @param int $courseid
 * @param int $userid
 *
 * @return: {{id}, {...}}
 */
function block_exacomp_get_topic_visibilities_for_course_and_user($courseid, $userid = 0){
	global $DB;
	
	$sql = "SELECT DISTINCT t.id FROM {".\block_exacomp\DB_TOPICS."} t
		LEFT JOIN {".\block_exacomp\DB_COURSETOPICS."} ct ON t.id = ct.topicid
		LEFT JOIN {".\block_exacomp\DB_TOPICVISIBILITY."} tv ON t.id = tv.topicid AND tv.courseid = ct.courseid
		WHERE ct.courseid = ? 

		AND ((tv.visible = 1 AND tv.studentid = 0 AND NOT EXISTS 
   			(SELECT * FROM {".\block_exacomp\DB_TOPICVISIBILITY."} tvsub
			WHERE tvsub.topicid = tv.topicid AND tvsub.courseid = tv.courseid AND tvsub.visible = 0 AND tvsub.studentid = ?)) 
		OR (tv.visible = 1 AND tv.studentid = ? AND NOT EXISTS 
			(SELECT * FROM {".\block_exacomp\DB_TOPICVISIBILITY."} tvsub
			WHERE tvsub.topicid = tv.topicid AND tvsub.courseid = tv.courseid AND tvsub.visible = 0 AND tvsub.studentid = 0)))";

	$params = array($courseid, $userid, $userid);

	return $DB->get_records_sql($sql, $params);
}

/**
 * get all visible topic for all course students -> improved performance
 * @param unknown $courseid
 * @return {[userid]=>[{id}, {id},...]}
 */
function block_exacomp_get_topic_visibilities_for_course($courseid){
	$user_visibilites = array();
	$students = block_exacomp_get_students_by_course($courseid);

	foreach($students as $student){
		$user_visibilites[$student->id] = block_exacomp_get_topic_visibilities_for_course_and_user($courseid, $student->id);
	}

	$user_visibilites[0] = block_exacomp_get_topic_visibilities_for_course_and_user($courseid);
	return $user_visibilites;
}
/**
 * returnes a list of examples whose solutions are visibile in course and user context
 * @param unknown $courseid
 * @param number $userid
 */
function block_exacomp_get_solution_visibilities_for_course_and_user($courseid, $userid = 0){
	global $DB;
	
	$sql = "SELECT DISTINCT e.id FROM {".\block_exacomp\DB_EXAMPLES."} e 
		LEFT JOIN {".\block_exacomp\DB_DESCEXAMP."} de ON e.id = de.exampid 
		LEFT JOIN {".\block_exacomp\DB_DESCTOPICS."} dt ON de.descrid = dt.descrid 
		LEFT JOIN {".\block_exacomp\DB_COURSETOPICS."} ct ON dt.topicid = ct.topicid 
		LEFT JOIN {".\block_exacomp\DB_SOLUTIONVISIBILITY."} sv ON e.id = sv.exampleid AND sv.courseid = ct.courseid 
		WHERE ct.courseid = ? 
		AND ((sv.visible = 1 AND sv.studentid = 0 AND NOT EXISTS (
			SELECT * FROM {".\block_exacomp\DB_SOLUTIONVISIBILITY."} svsub 
			WHERE svsub.exampleid = sv.exampleid AND svsub.courseid = sv.courseid AND svsub.visible = 0 AND svsub.studentid = ?)) 
		OR (sv.visible = 1 AND sv.studentid = ? AND NOT EXISTS (
			SELECT * FROM {".\block_exacomp\DB_SOLUTIONVISIBILITY."} svsub WHERE svsub.exampleid = sv.exampleid AND svsub.courseid = sv.courseid AND svsub.visible = 0 AND svsub.studentid = 0)))";
	
	$params = array($courseid, $userid, $userid);
	
	return $DB->get_records_sql($sql, $params);
}

/**
 * returns a list of examples whose solutions are visibile for each user in course
 * userid = 0 individual visibility not minded
 * @param unknown $courseid
 * @return 
 */
function block_exacomp_get_solution_visibilities_for_course($courseid){
	$user_visibilites = array();
	$students = block_exacomp_get_students_by_course($courseid);
	
	foreach($students as $student){
		$user_visibilites[$student->id] = block_exacomp_get_solution_visibilities_for_course_and_user($courseid, $student->id);
	}
	
	$user_visibilites[0] = block_exacomp_get_solution_visibilities_for_course_and_user($courseid);
	return $user_visibilites;
}
function block_exacomp_get_visibility_object($courseid){
	$visibilites = new stdClass();
	$visibilites->courseid = $courseid;
	$visibilites->example_visibilities = block_exacomp_get_example_visibilities_for_course($courseid);
	$visibilites->descriptor_visibilites = block_exacomp_get_descriptor_visibilities_for_course($courseid);
	$visibilites->topic_visibilities = block_exacomp_get_topic_visibilities_for_course($courseid);
	$visibilites->solution_visibilities = block_exacomp_get_solution_visibilities_for_course($courseid);
	return $visibilites;
}
function block_exacomp_get_visibility_cache($courseid){
	// Get a cache instance
	$cache = cache::make('block_exacomp', 'visibility_cache');
	
	$visibilites = $cache->get('visibilities');
	
	if(!$visibilites || $visibilites->courseid != $courseid){
		$result = $cache->set('visibilities', block_exacomp_get_visibility_object($courseid));
		$visibilites = $cache->get('visibilities');
	}
	
	return $visibilites;
}

function block_exacomp_update_visibility_cache($courseid){
	$cache = cache::make('block_exacomp', 'visibility_cache');
	return $cache->set('visibilities', block_exacomp_get_visibility_object($courseid));
}

function block_exacomp_cmp_statetitlesort($a, $b){
	if($a->state == $b->state)
		return $a->sorting > $b->sorting;
	
	return $a->state < $b->state;
}
//TODO duplicate function in external lib, remove function in externallib
function block_exacomp_cmp_niveausort($a, $b){
	return strcmp($a->cattitle, $b->cattitle);
}

}

namespace block_exacomp {

	use block_exacomp\globals as g;

	function is_external_trainer_for_student($trainerid, $studentid) {
		return g::$DB->get_record(DB_EXTERNAL_TRAINERS, [
			'trainerid' => $trainerid,
			'studentid' => $studentid,
		]);
	}

	function is_external_trainer($trainerid) {
		return g::$DB->get_record(DB_EXTERNAL_TRAINERS, [
			'trainerid' => $trainerid,
		]);
	}

	/**
	 * Returns subject grade and evaluation niveau for one user
	 *
	 * @param int $userid
	 * @param int $subjectid
	 * @param int $courseid
	 * @return object
	 */
	function get_user_subject_evaluation($userid, $subjectid, $courseid) {
		// don't do teacher check here
		// block_exacomp_require_teacher ( $courseid );

		return g::$DB->get_record_sql("
			SELECT cu.additionalinfo, en.title as niveau
			FROM {".DB_COMPETENCES."} as cu
			LEFT JOIN {".DB_EVALUATION_NIVEAU."} en ON cu.evalniveauid = en.id
			WHERE cu.userid = ? AND cu.courseid = ? AND cu.compid = ? AND cu.role = ?", [
			$userid,
			$courseid,
			$subjectid,
			ROLE_TEACHER,
		]);
	}

	class global_config {
		
		/**
		 * Returns all values used for examples and child-descriptors
		 */
		static function get_value_titles($courseid = 0, $short = false) {
			// if additional_grading is set, use global value scheme
			// TODO: use language strings for the titles
			
			if (block_exacomp_additional_grading()) {
				if($short)
					return array(
					 	-1 => 'oA',
						0 => 'nE',
						1 => 'tE',
						2 => 'üE',
						3 => 'vE'
					);
					
				return array (
						- 1 => 'ohne Angabe',
						0 => 'nicht erreicht',
						1 => 'teilweise',
						2 => 'überwiegend',
						3 => 'vollständig' 
				);
			} 
			// else use value scheme set in the course
			else {
				// TODO: add settings to g::$COURSE?
				$course_grading = block_exacomp_get_settings_by_course(($courseid==0)?g::$COURSE->id:$courseid)->grading;
				
				$values = array(-1 => ' ');
				$values += range(0, $course_grading);
				
				return $values;
			}
		}
	
		/**
		 * Returns title for one value
		 * @param id $id
		 */
		static function get_value_title_by_id($id) {
			if(!$id) return ' ';
			return static::get_value_titles()[$id];
		}
		
		/**
		 * Returns all values used for examples and child-descriptors
		 */
		static function get_student_value_titles() {
				
			// if additional_grading is set, use global value scheme
			if (block_exacomp_additional_grading()) {
				return array (
						- 1 => ' ',
						1 => ':-(',
						2 => ':-|',
						3 => ':-)'
				);
			}
			// else use value scheme set in the course
			else {
				// TODO: add settings to g::$COURSE?
				$course_grading = block_exacomp_get_settings_by_course(g::$COURSE->id)->grading;
				
				$values = array(-1 => ' ');
				$values += range(1, $course_grading);
				
				return $values;
			}
		}
		
		/**
		 * Returns title for one value
		 * @param id $id
		 */
		static function get_student_value_title_by_id($id) {
			if(!$id) return ' ';
			return static::get_student_value_titles()[$id];
		}
		
		/**
		 * Returns all evaluation niveaus, specified by the admin
		 */
		static function get_evalniveaus() {
			$values = array(-1 => ' ');
			$values += g::$DB->get_records_menu(DB_EVALUATION_NIVEAU,null,'','id,title');
			
			return $values;
		}
		
		/**
		 * Returns title for one evaluation niveau
		 * @param id $id
		 */
		static function get_evalniveau_title_by_id($id) {
			return static::get_evalniveaus()[$id];
		}
		
		/**
		 * Maps gradings (1.0 - 6.0) to 0-3 values
		 * 
		 * @param double $additionalinfo
		 */
		static function get_additionalinfo_value_mapping($additionalinfo){
			if($additionalinfo == "")
				return -1;
				
			$mapping = array(6.0, 4.8, 3.5, 2.2);
			
			foreach($mapping as $k => $v) {
				if($additionalinfo > $v)
					break;
				$value = $k;
			}
			
			return $value;
		}
		
		/**
		 * Maps 0-3 values to gradings (1.0 - 6.0)
		 *
		 * @param int $value
		 */
		static function get_value_additionalinfo_mapping($value){
			if(!$value || $value == "")
				return -1;
		
			$mapping = array(6.0, 4.8, 3.5, 2.2);
				
			return $mapping[$value];
		}
		
		/**
		 * return range of gradings to value mapping
		 * @param int $value
		 */
		static function get_values_additionalinfo_mapping(){
			return array(6.0, 4.8, 3.5, 2.2);
		}
	}

	/**
	 * gibt die gefundenen ergebnisse als liste zurück
	 */
	/*
	function search_competence_grid_list($courseid, $q) {
		$subjects = \block_exacomp\db_layer_course::create($courseid)->get_subjects();

		$queryItems = preg_split('![\s,]+!', trim($q));
		foreach ($queryItems as &$q) {
			$q = \core_text::strtolower($q);
		}
		unset($q);

		$searchResults = (object)[
			'subjects' => [],
			'topics' => [],
			'descriptors' => [],
			'examples' => [],
		];
		$find = function($object) use ($queryItems) {
			foreach ($queryItems as $q) {
				$found = false;
				// for now, just search all fields for the search string
				foreach ($object->get_data() as $value) {
					if (is_array($value) || is_object($value)) continue;

					if (\core_text::strpos(\core_text::strtolower($value), $q) !== false) {
						$found = true;
						break;
					}
				}
				if (!$found) {
					return false;
				}
			}

			return true;
		};

		$find_example = function($example) use ($searchResults, $find) {
			if ($find($example)) {
				$searchResults->examples[$example->id] = $example;
			}
		};
		$find_descriptor = function($descriptor) use ($searchResults, $find, &$find_descriptor, $find_example) {
			if ($find($descriptor)) {
				$searchResults->descriptors[$descriptor->id] = $descriptor;
			}

			array_walk($descriptor->examples, $find_example);

			array_walk($descriptor->children, $find_descriptor);
		};
		$find_topic = function($topic) use ($searchResults, $find, $find_descriptor) {
			if ($find($topic)) {
				$searchResults->topics[$topic->id] = $topic;
			}

			array_walk($topic->descriptors, $find_descriptor);
		};
		$find_subject = function($subject) use ($searchResults, $find, $find_topic) {
			if ($find($subject)) {
				$searchResults->subjects[$subject->id] = $subject;
			}

			array_walk($subject->topics, $find_topic);
		};

		array_walk($subjects, $find_subject);

		$searchResults = (object)array_filter((array)$searchResults, function($tmp) { return !empty($tmp); });
		return $searchResults;
	}
	*/

	/**
	 * searches the competence grid of one course and returns only the found items
	 * @param $courseid
	 * @param $q
	 * @return array
	 */
	function search_competence_grid_as_tree($courseid, $q) {
		$subjects = db_layer_course::create($courseid)->get_subjects();

		if (!trim($q)) {
			$queryItems = null;
		} else {
			$queryItems = preg_split('![\s,]+!', trim($q));
			foreach ($queryItems as &$q) {
				$q = \core_text::strtolower($q);
			}
			unset($q);
		}

		$find = function($object) use ($queryItems) {
			if (!$queryItems) {
				// no filter, so got found!
				return true;
			}

			foreach ($queryItems as $q) {
				$found = false;
				// for now, just search all fields for the search string
				foreach ($object->get_data() as $value) {
					if (is_array($value) || is_object($value)) continue;

					if (\core_text::strpos(\core_text::strtolower($value), $q) !== false) {
						$found = true;
						break;
					}
				}
				if (!$found) {
					return false;
				}
			}

			return true;
		};

		$filter_not_empty = function(&$items) use (&$filter_not_empty) {
 			$items = array_filter($items, function($item) use ($filter_not_empty) {
				if ($item instanceof subject) {
					$filter_not_empty($item->topics);
					return !!$item->topics;
				}
				if ($item instanceof topic) {
					$filter_not_empty($item->descriptors);
					return !!$item->descriptors;
				}
				if ($item instanceof descriptor) {
					$filter_not_empty($item->children);

					return $item->examples || $item->children;
				}
			});
		};

		$filter = function(&$items) use ($find, &$filter, $filter_not_empty) {
 			$items = array_filter($items, function($item) use ($find, $filter, $filter_not_empty) {
				if ($item instanceof subject) {
					if ($find($item)) {
						$filter_not_empty($item->topics);
					} else {
						$filter($item->topics);
					}
					return !!$item->topics;
				}
				if ($item instanceof topic) {
					if ($find($item)) {
						$filter_not_empty($item->descriptors);
					} else {
						$filter($item->descriptors);
					}

					return !!$item->descriptors;
				}
				if ($item instanceof descriptor) {
					if ($find($item)) {
						$filter_not_empty($item->children);
						return true;
					} else {
						$filter($item->examples);
						$filter($item->children);
						return $item->examples || $item->children;
					}
				}
				if ($item instanceof example) {
					return $find($item);
				}
			});
		};

		if ($queryItems) {
			$filter($subjects);
		} else {
			$filter_not_empty($subjects);
		}

		return $subjects;
	}

	function search_competence_grid_as_example_list($courseid, $q) {
		$examples = [];
		$data = (object)[];
		$get_examples = function($items) use (&$get_examples, &$examples, &$data) {
 			array_walk($items, function($item) use (&$get_examples, &$examples, &$data) {
				if ($item instanceof subject) {
					$data->subject = $item;
					$get_examples($item->topics);
				}
				if ($item instanceof topic) {
					$data->topic = $item;
					$get_examples($item->descriptors);
				}
				if ($item instanceof descriptor) {
					$data->descriptors[] = $item;
					$get_examples($item->children);
					$get_examples($item->examples);
					array_pop($data->descriptors);
				}
				if ($item instanceof example) {
					if (empty($examples[$item->id])) {
						$examples[$item->id] = $item;
						$item->subjects = [];
					}
					$parent = $examples[$item->id];

					if (empty($parent->subjects[$data->subject->id])) {
						$parent->subjects[$data->subject->id] = clone $data->subject;
						$parent->subjects[$data->subject->id]->topics = [ ];
					}
					$parent = $parent->subjects[$data->subject->id];

					if (empty($parent->topics[$data->topic->id])) {
						$parent->topics[$data->topic->id] = clone $data->topic;
						$parent->topics[$data->topic->id]->descriptors = [];
					}
					$parent = $parent->topics[$data->topic->id];

					if (empty($parent->descriptors[$data->descriptors[0]->id])) {
						$parent->descriptors[$data->descriptors[0]->id] = clone $data->descriptors[0];
						$parent->descriptors[$data->descriptors[0]->id]->children = [];
					}
					$parent = $parent->descriptors[$data->descriptors[0]->id];

					if (!empty($data->descriptors[1]) && empty($parent->children[$data->descriptors[1]->id])) {
						$parent->children[$data->descriptors[1]->id] = clone $data->descriptors[1];
					}
				}
			});
		};

		$subjects = search_competence_grid_as_tree($courseid, $q);

		$get_examples($subjects);

		return $examples;
	}

	function get_comp_eval($courseid, $role, $userid, $comptype, $compid) {
		return g::$DB->get_record(\block_exacomp\DB_COMPETENCES, array('courseid'=>$courseid, 'userid'=>$userid, 'compid'=>$compid, 'comptype'=>$comptype, 'role'=>$role));
	}

	function get_comp_eval_value($courseid, $role, $userid, $comptype, $compid) {
		return g::$DB->get_field(\block_exacomp\DB_COMPETENCES, 'value', array('courseid'=>$courseid, 'userid'=>$userid, 'compid'=>$compid, 'comptype'=>$comptype, 'role'=>$role));
	}

	function get_select_niveau_items() {
		$values = array(''=>array(''=>''));
		$niveaus = niveau::get_objects(null, 'sorting');
		foreach ($niveaus as $niveau) {
			$sourceName = block_exacomp_get_renderer()->source_info($niveau->source);
			if (!isset($values[$sourceName])) $values[$sourceName] = [];
			$values[$sourceName][$niveau->id] = $niveau->title;
		}
		ksort($values);

		return $values;
	}

	class permission_exception extends moodle_exception {
		function __construct($errorcode = 'Not allowed', $module='', $link='', $a=NULL, $debuginfo=null) {
			return parent::__construct($errorcode, $module, $link, $a, $debuginfo);
		}
	}

	function has_capability($cap, $data) {
		if ($cap == CAP_ADD_EXAMPLE) {
			$courseid = $data;
			if (!block_exacomp_is_teacher($courseid)) {
				return false;
			}
		} else {
			throw new \coding_exception("Capability $cap not found");
		}

		return true;
	}

	function require_capability($cap, $data) {
		if (!has_capability($cap, $data)) {
			throw new permission_exception();
		}
	}

	function require_item_capability($cap, $item) {
		if ($item instanceof example && in_array($cap, [CAP_MODIFY, CAP_DELETE, CAP_SORTING])) {
			if ($item->creatorid == g::$USER->id) {
				// User is creator
				return true;
			}

			if (!block_exacomp_is_teacher()) {
				throw new permission_exception('User is no teacher');
			}

			// find example in course
			$examples = block_exacomp_get_examples_by_course(g::$COURSE->id);
			if (!isset($examples[$item->id])) {
				throw new permission_exception('Not a course example');
			}
		} elseif ($item instanceof example && in_array($cap, [CAP_VIEW])) {
			if (!block_exacomp_is_student() && !block_exacomp_is_teacher()) {
				throw new permission_exception('User is no teacher or student');
			}

			// find descriptor in course
			$examples = block_exacomp_get_examples_by_course(g::$COURSE->id);
			if (!isset($examples[$item->id])) {
				throw new permission_exception('Not a course example');
			}

			// TODO: check visibility?
		} elseif ($item instanceof subject && in_array($cap, [CAP_MODIFY, CAP_DELETE])) {
			if (!block_exacomp_is_teacher(g::$COURSE->id)) {
				throw new permission_exception('User is no teacher');
			}

			$subjects = block_exacomp_get_subjects(g::$COURSE->id);
			if (!isset($subjects[$item->id])) {
				throw new permission_exception('No course subject');
			}

			if ($item->source != DATA_SOURCE_CUSTOM) {
				throw new permission_exception('Not a custom subject');
			}
		} elseif ($item instanceof topic && in_array($cap, [CAP_MODIFY, CAP_DELETE])) {
			if (!block_exacomp_is_teacher(g::$COURSE->id)) {
				throw new permission_exception('User is no teacher');
			}

			$topics = block_exacomp_get_topics_by_course(g::$COURSE->id);
			if (!isset($topics[$item->id])) {
				throw new permission_exception('No course topic');
			}


			if ($item->source != DATA_SOURCE_CUSTOM) {
				throw new permission_exception('Not a custom topic');
			}
		} elseif ($item instanceof descriptor && in_array($cap, [CAP_MODIFY, CAP_DELETE])) {
			if (!block_exacomp_is_teacher(g::$COURSE->id)) {
				throw new permission_exception('User is no teacher');
			}

			// find descriptor in course
			$descriptors = block_exacomp_get_descriptors(g::$COURSE->id);
			$found = false;
			foreach ($descriptors as $descriptor) {
				if ($descriptor->id == $item->id) {
					$found = true;
					break;
				}
				foreach ($descriptor->children as $descriptor) {
					if ($descriptor->id == $item->id) {
						$found = true;
						break;
					}
				}
				if ($found) break;
			}
			if (!$found) {
				throw new permission_exception('No course descriptor');
			}

			if ($item->source != DATA_SOURCE_CUSTOM) {
				throw new permission_exception('Not a custom descriptor');
			}
		} elseif ($item instanceof cross_subject && in_array($cap, [CAP_MODIFY, CAP_DELETE])) {
			if (block_exacomp_is_admin()) return true;

			if ($item->is_draft()) {
				// draft
				if (!block_exacomp_is_teacher(g::$COURSE->id)) {
					throw new permission_exception('User is no teacher');
				}

				if ($item->creatorid != g::$USER->id) {
					throw new permission_exception('No permission');
				}
			} else {
				if (!block_exacomp_is_teacher($item->courseid)) {
					throw new permission_exception('User is no teacher');
				}
			}
		} elseif ($item instanceof cross_subject && in_array($cap, [CAP_VIEW])) {
			if ($item->has_capability(CAP_MODIFY)) return true;

			if ($item->is_draft() && block_exacomp_is_teacher()) {
				// teachers can view all drafts
				return true;
			}

			// it's a student
			if ($item->is_draft() || $item->courseid != g::$COURSE->id) {
				throw new permission_exception('No permission');
			}
			if (!$item->shared && !block_exacomp_student_crosssubj($item->id, g::$USER->id)) {
				throw new permission_exception('No permission');
			}
		} else {
			throw new \coding_exception("Capability $cap for item ".print_r($item, true)." not found");
		}

		return true;
	}

	function has_item_capability($cap, $item) {
		try {
			require_item_capability($cap, $item);
			return true;
		} catch (permission_exception $e) {
			return false;
		}
	}
}

