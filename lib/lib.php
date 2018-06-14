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

defined('MOODLE_INTERNAL') || die();

use block_exacomp\globals as g;
use Super\Cache;

require_once __DIR__.'/common.php';
require_once __DIR__.'/classes.php';
require_once __DIR__.'/../block_exacomp.php';
require_once($CFG->libdir.'/badgeslib.php');
require_once($CFG->dirroot.'/badges/lib/awardlib.php');


/**
 * DATABSE TABLE NAMES
 */
const BLOCK_EXACOMP_DB_SKILLS = 'block_exacompskills';
const BLOCK_EXACOMP_DB_NIVEAUS = 'block_exacompniveaus';
const BLOCK_EXACOMP_DB_TAXONOMIES = 'block_exacomptaxonomies';
const BLOCK_EXACOMP_DB_EXAMPLES = 'block_exacompexamples';
const BLOCK_EXACOMP_DB_EXAMPLEEVAL = 'block_exacompexameval';
const BLOCK_EXACOMP_DB_DESCRIPTORS = 'block_exacompdescriptors';
const BLOCK_EXACOMP_DB_DESCEXAMP = 'block_exacompdescrexamp_mm';
const BLOCK_EXACOMP_DB_EDULEVELS = 'block_exacompedulevels';
const BLOCK_EXACOMP_DB_SCHOOLTYPES = 'block_exacompschooltypes';
const BLOCK_EXACOMP_DB_SUBJECTS = 'block_exacompsubjects';
const BLOCK_EXACOMP_DB_TOPICS = 'block_exacomptopics';
const BLOCK_EXACOMP_DB_COURSETOPICS = 'block_exacompcoutopi_mm';
const BLOCK_EXACOMP_DB_DESCTOPICS = 'block_exacompdescrtopic_mm';
const BLOCK_EXACOMP_DB_CATEGORIES = 'block_exacompcategories';
const BLOCK_EXACOMP_DB_COMPETENCE_ACTIVITY = 'block_exacompcompactiv_mm';
const BLOCK_EXACOMP_DB_COMPETENCES = 'block_exacompcompuser';
const BLOCK_EXACOMP_DB_COMPETENCE_USER_MM = 'block_exacompcompuser_mm';
const BLOCK_EXACOMP_DB_SETTINGS = 'block_exacompsettings';
const BLOCK_EXACOMP_DB_MDLTYPES = 'block_exacompmdltype_mm';
const BLOCK_EXACOMP_DB_DESCBADGE = 'block_exacompdescbadge_mm';
const BLOCK_EXACOMP_DB_PROFILESETTINGS = 'block_exacompprofilesettings';
const BLOCK_EXACOMP_DB_CROSSSUBJECTS = 'block_exacompcrosssubjects';
const BLOCK_EXACOMP_DB_DESCCROSS = 'block_exacompdescrcross_mm';
const BLOCK_EXACOMP_DB_CROSSSTUD = 'block_exacompcrossstud_mm';
const BLOCK_EXACOMP_DB_DESCVISIBILITY = 'block_exacompdescrvisibility';
const BLOCK_EXACOMP_DB_DESCCAT = 'block_exacompdescrcat_mm';
const BLOCK_EXACOMP_DB_EXAMPTAX = 'block_exacompexampletax_mm';
const BLOCK_EXACOMP_DB_DATASOURCES = 'block_exacompdatasources';
const BLOCK_EXACOMP_DB_SCHEDULE = 'block_exacompschedule';
const BLOCK_EXACOMP_DB_EXAMPVISIBILITY = 'block_exacompexampvisibility';
const BLOCK_EXACOMP_DB_ITEMEXAMPLE = 'block_exacompitemexample';
const BLOCK_EXACOMP_DB_SUBJECT_NIVEAU_MM = 'block_exacompsubjniveau_mm';
const BLOCK_EXACOMP_DB_EXTERNAL_TRAINERS = 'block_exacompexternaltrainer';
const BLOCK_EXACOMP_DB_EVALUATION_NIVEAU = 'block_exacompeval_niveau';
const BLOCK_EXACOMP_DB_TOPICVISIBILITY = 'block_exacomptopicvisibility';
const BLOCK_EXACOMP_DB_SOLUTIONVISIBILITY = 'block_exacompsolutvisibility';
const BLOCK_EXACOMP_DB_AUTOTESTASSIGN = 'block_exacompautotestassign';

/**
 * PLUGIN ROLES
 */
const BLOCK_EXACOMP_ROLE_TEACHER = 1;
const BLOCK_EXACOMP_ROLE_STUDENT = 0;

const BLOCK_EXACOMP_WS_ROLE_TEACHER = 1;
const BLOCK_EXACOMP_WS_ROLE_STUDENT = 2;

/**
 * COMPETENCE TYPES
 */
const BLOCK_EXACOMP_TYPE_DESCRIPTOR = 0;
const BLOCK_EXACOMP_TYPE_TOPIC = 1;
const BLOCK_EXACOMP_TYPE_CROSSSUB = 2;
const BLOCK_EXACOMP_TYPE_SUBJECT = 3;
const BLOCK_EXACOMP_TYPE_EXAMPLE = 4;
const BLOCK_EXACOMP_TYPE_DESCRIPTOR_PARENT = 1001;
const BLOCK_EXACOMP_TYPE_DESCRIPTOR_CHILD = 1002;

const BLOCK_EXACOMP_SETTINGS_MAX_SCHEME = 10;
const BLOCK_EXACOMP_DATA_SOURCE_CUSTOM = 3;
const BLOCK_EXACOMP_EXAMPLE_SOURCE_TEACHER = 3;
const BLOCK_EXACOMP_EXAMPLE_SOURCE_USER = 4;

const BLOCK_EXACOMP_IMPORT_SOURCE_DEFAULT = 1;
const BLOCK_EXACOMP_IMPORT_SOURCE_SPECIFIC = 2;

const BLOCK_EXACOMP_CUSTOM_CREATED_DESCRIPTOR = 3;

const BLOCK_EXACOMP_EXAMPLE_STATE_NOT_SET = 0; // never used in weekly schedule, no evaluation
const BLOCK_EXACOMP_EXAMPLE_STATE_IN_POOL = 1; // planned to work with example -> example is in pool
const BLOCK_EXACOMP_EXAMPLE_STATE_IN_CALENDAR = 2; // example is in work -> in calendar
const BLOCK_EXACOMP_EXAMPLE_STATE_SUBMITTED = 3; //state 3 = submission for example / example closed (for submission no file upload etc is necessary) -> closed
const BLOCK_EXACOMP_EXAMPLE_STATE_EVALUATED_NEGATIV = 4; // evaluated -> only from teacher-> exacomp evaluation nE
const BLOCK_EXACOMP_EXAMPLE_STATE_EVALUATED_POSITIV = 5; //evaluated -> only from teacher -> exacomp evaluation > nE
const BLOCK_EXACOMP_EXAMPLE_STATE_LOCKED_TIME = 9; //handled like example entry on calendar, but represent locked time

const BLOCK_EXACOMP_STUDENTS_PER_COLUMN = 3;
const BLOCK_EXACOMP_SHOW_ALL_TOPICS = -1;
const BLOCK_EXACOMP_SHOW_ALL_NIVEAUS = 99999999;

const BLOCK_EXACOMP_CAP_ADD_EXAMPLE = 'add_example';
const BLOCK_EXACOMP_CAP_VIEW = 'view';
const BLOCK_EXACOMP_CAP_MODIFY = 'modify';
const BLOCK_EXACOMP_CAP_DELETE = 'delete';
const BLOCK_EXACOMP_CAP_SORTING = 'sorting';

const BLOCK_EXACOMP_SHOW_ALL_TAXONOMIES = 100000000;
const BLOCK_EXACOMP_SHOW_ALL_STUDENTS = -1;
const BLOCK_EXACOMP_DEFAULT_STUDENT = -5;

const BLOCK_EXACOMP_REPORT1 = 1;
const BLOCK_EXACOMP_REPORT2 = 2;
const BLOCK_EXACOMP_REPORT3 = 3;

const BLOCK_EXACOMP_EVAL_INPUT_TACHER_EVALUATION = 'teacherevaluation';
const BLOCK_EXACOMP_EVAL_INPUT_STUDENT_EVALUATION = 'studentevaluation';
const BLOCK_EXACOMP_EVAL_INPUT_ADDITIONALINFO = 'additionalinfo';
const BLOCK_EXACOMP_EVAL_INPUT_EVALNIVEAUID = 'evalniveauid';

/**
 * access configuration setting via functions
 */
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
function block_exacomp_init_js_css() {
	global $PAGE, $CFG;

	// only allowed to be called once
	static $js_inited = false;
	if ($js_inited) {
		return;
	}
	$js_inited = true;

	// js/css for whole block
	$PAGE->requires->css('/blocks/exacomp/css/styles.css');
	$PAGE->requires->jquery();
	$PAGE->requires->jquery_plugin('ui');
	$PAGE->requires->jquery_plugin('ui-css');
	$PAGE->requires->js("/blocks/exacomp/javascript/simpletreemenu/simpletreemenu.js", true);
	$PAGE->requires->css("/blocks/exacomp/javascript/simpletreemenu/simpletree.css", true);
	$PAGE->requires->js("/blocks/exacomp/javascript/jquery.disablescroll.js", true);
	$PAGE->requires->js('/blocks/exacomp/javascript/exacomp.js', true);
	$PAGE->requires->js('/blocks/exacomp/javascript/ajax.js', true);

	// Strings can be used in JavaScript: M.util.block_exacomp_get_string(identifier, component)
	$PAGE->requires->strings_for_js([
		'show', 'hide', 'all',
	], 'moodle');
	$PAGE->requires->strings_for_js([
	    'override_notice1', 'override_notice2','unload_notice', 'example_sorting_notice', 'delete_unconnected_examples',
		'value_too_large', 'value_too_low', 'value_not_allowed', 'hide_solution', 'show_solution', 'weekly_schedule',
		'pre_planning_storage', 'weekly_schedule_disabled', 'pre_planning_storage_disabled',
		'add_example_for_all_students_to_schedule_confirmation', 'seperatordaterange', 'selfevaluation',
	    'topic_3dchart_empty', 'columnselect', 'n1.unit', 'n2.unit', 'n3.unit', 'n4.unit', 'n5.unit', 'n6.unit', 'n7.unit',
	    'n8.unit', 'n9.unit', 'n10.unit',
	], 'block_exacomp');

	// page specific js/css
	$scriptName = preg_replace('!\.[^\.]+$!', '', basename($_SERVER['PHP_SELF']));
	if (file_exists($CFG->dirroot.'/blocks/exacomp/css/'.$scriptName.'.css')) {
		$PAGE->requires->css('/blocks/exacomp/css/'.$scriptName.'.css');
	}
	if (file_exists($CFG->dirroot.'/blocks/exacomp/javascript/'.$scriptName.'.js')) {
		$PAGE->requires->js('/blocks/exacomp/javascript/'.$scriptName.'.js', true);
	}
}

function block_exacomp_init_js_weekly_schedule() {
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

function block_exacomp_is_editingteacher($context = null, $userid = null) {
	$context = block_exacomp_get_context_from_courseid($context);

	return has_capability('block/exacomp:teacher', $context, $userid) && has_capability('block/exacomp:editingteacher', $context, $userid);
}

function block_exacomp_is_teacher_in_any_course() {
	$courses = block_exacomp_get_courseids();

	foreach ($courses as $course) {
		if (block_exacomp_is_teacher($course)) {
			return true;
		}
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

function block_exacomp_use_eval_niveau() {
	$evaluation_niveau = block_exacomp_evaluation_niveau_type();

	return $evaluation_niveau >= 1 && $evaluation_niveau <= 3;
}

function block_exacomp_evaluation_niveau_type() {
	return get_config('exacomp', 'adminscheme');
}

function block_exacomp_additional_grading() {
	return get_config('exacomp', 'additional_grading');
}

function block_exacomp_get_timetable_entries() {
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
 * Gets all subjects that are in use in a particular course.
 *
 * @param int $courseid
 * @param bool $showalldescriptors default false, show only comps with activities
 * @return array $subjects
 */
function block_exacomp_get_subjects_by_course($courseid, $showalldescriptors = false) {
	if (!$showalldescriptors) {
		$showalldescriptors = block_exacomp_get_settings_by_course($courseid)->show_all_descriptors;
	}

	$sql = '
		SELECT DISTINCT s.id, s.titleshort, s.title, s.stid, s.infolink, s.description, s.source, s.sourceid, s.sorting, s.author
		FROM {'.BLOCK_EXACOMP_DB_SUBJECTS.'} s
		JOIN {'.BLOCK_EXACOMP_DB_TOPICS.'} t ON t.subjid = s.id
		JOIN {'.BLOCK_EXACOMP_DB_COURSETOPICS.'} ct ON ct.topicid = t.id AND ct.courseid = ?
		'.($showalldescriptors ? '' : '
			-- only show active ones
			JOIN {'.BLOCK_EXACOMP_DB_DESCTOPICS.'} topmm ON topmm.topicid=t.id
			JOIN {'.BLOCK_EXACOMP_DB_DESCRIPTORS.'} d ON topmm.descrid=d.id
			JOIN {'.BLOCK_EXACOMP_DB_COMPETENCE_ACTIVITY.'} ca ON ((d.id=ca.compid AND ca.comptype = '.BLOCK_EXACOMP_TYPE_DESCRIPTOR.') OR (t.id=ca.compid AND ca.comptype = '.BLOCK_EXACOMP_TYPE_TOPIC.'))
				AND ca.activityid IN ('.block_exacomp_get_allowed_course_modules_for_course_for_select($courseid).')
			').'
		ORDER BY s.title
			';

	$subjects = block_exacomp\subject::get_objects_sql($sql, array($courseid));

	return block_exacomp_sort_items($subjects, BLOCK_EXACOMP_DB_SUBJECTS);
}

/**
 * Gets all available subjects
 */
function block_exacomp_get_all_subjects() {
	global $DB;

	return $DB->get_records(BLOCK_EXACOMP_DB_SUBJECTS, array(), '', 'id, title, source, sourceid, author');
}

/**
 * This method is only used in the LIS version
 * @param int $courseid
 */
function block_exacomp_get_schooltypes_by_course($courseid) {
	global $DB;

	return $DB->get_records_sql('
			SELECT DISTINCT s.id, s.title, s.source, s.sourceid, s.sorting
			FROM {'.BLOCK_EXACOMP_DB_SCHOOLTYPES.'} s
			JOIN {'.BLOCK_EXACOMP_DB_MDLTYPES.'} m ON m.stid = s.id AND m.courseid = ?
			ORDER BY s.sorting, s.title
			', array($courseid));
}

/**
 *
 * This function is used for courseselection.php
 * -only subject according to selected schooltypes are returned
 * @param int $courseid
 */
function block_exacomp_get_subjects_for_schooltype($courseid, $schooltypeid = 0) {
	$sql = 'SELECT s.* FROM {'.BLOCK_EXACOMP_DB_SUBJECTS.'} s
	JOIN {'.BLOCK_EXACOMP_DB_MDLTYPES.'} type ON s.stid = type.stid
	WHERE type.courseid=?';

	if ($schooltypeid > 0) {
		$sql .= ' AND type.stid = ?';
	}

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

	if ($courseid == 0) {
		$sql = 'SELECT s.id, s.title, s.author
		FROM {'.BLOCK_EXACOMP_DB_SUBJECTS.'} s
		JOIN {'.BLOCK_EXACOMP_DB_TOPICS.'} t ON t.subjid = s.id
		GROUP BY s.id, s.title, s.stid
		ORDER BY s.stid, s.sorting, s.title
		';

		return $DB->get_records_sql($sql);
	} else if ($subjectid != null) {
		$sql = 'SELECT s.id, s.title, s.author
		FROM {'.BLOCK_EXACOMP_DB_SUBJECTS.'} s
		JOIN {'.BLOCK_EXACOMP_DB_TOPICS.'} t ON t.subjid = s.id
		WHERE s.id = ?
		GROUP BY s.id, s.title, s.stid
		ORDER BY s.stid, s.sorting, s.title';

		return $DB->get_records_sql($sql, $subjectid);
	}

	$subjects = $DB->get_records_sql('
			SELECT DISTINCT s.id, s.title, s.stid, s.sorting
			FROM {'.BLOCK_EXACOMP_DB_SUBJECTS.'} s
			JOIN {'.BLOCK_EXACOMP_DB_TOPICS.'} t ON t.subjid = s.id
			JOIN {'.BLOCK_EXACOMP_DB_COURSETOPICS.'} ct ON ct.topicid = t.id AND ct.courseid = ?
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

	$descriptors = block_exacomp_get_descriptors_by_example($exampleid);
	foreach ($descriptors as $descriptor) {

		$full = $DB->get_record(BLOCK_EXACOMP_DB_DESCRIPTORS, array("id" => $descriptor->id));
		$sql = "select s.* FROM {".BLOCK_EXACOMP_DB_SUBJECTS."} s, {".BLOCK_EXACOMP_DB_DESCTOPICS."} dt, {".BLOCK_EXACOMP_DB_TOPICS."} t
		WHERE dt.descrid = ? AND t.id = dt.topicid AND t.subjid = s.id";

		$subject = $DB->get_record_sql($sql, array($full->parentid), IGNORE_MULTIPLE);
		if ($subject) {
			return $subject->title;
		}
	}
}

/**
 * returns all topics from a course
 * @param int $courseid
 */
function block_exacomp_get_topics_by_course($courseid, $showalldescriptors = false, $showonlyvisible = false) {
	return block_exacomp_get_topics_by_subject($courseid, 0, $showalldescriptors, $showonlyvisible);
}

/**
 * Gets all topics from a particular subject
 * @param int $courseid
 * @param int $subjectid
 */
function block_exacomp_get_topics_by_subject($courseid, $subjectid = 0, $showalldescriptors = false, $showonlyvisible = false) {
	global $DB;
	if (!$courseid) {
		$showonlyvisible = false;
	}

	if (!$showalldescriptors) {
		$showalldescriptors = block_exacomp_get_settings_by_course($courseid)->show_all_descriptors;
	}

	$sql = '
		SELECT DISTINCT t.id, t.title, t.sorting, t.subjid, t.description, t.numb, t.source, t.sourceid, tvis.visible as visible, s.source AS subj_source, s.sorting AS subj_sorting, s.title AS subj_title
		FROM {'.BLOCK_EXACOMP_DB_TOPICS.'} t
		JOIN {'.BLOCK_EXACOMP_DB_COURSETOPICS.'} ct ON ct.topicid = t.id AND ct.courseid = ? '.(($subjectid > 0) ? 'AND t.subjid = ? ' : '').'
		JOIN {'.BLOCK_EXACOMP_DB_SUBJECTS.'} s ON t.subjid=s.id -- join subject here, to make sure only topics with existing subject are loaded
		-- left join, because courseid=0 has no topicvisibility!
		JOIN {'.BLOCK_EXACOMP_DB_TOPICVISIBILITY.'} tvis ON tvis.topicid=t.id AND tvis.studentid=0 AND tvis.courseid=ct.courseid'
		.($showonlyvisible ? ' AND tvis.visible = 1 ' : '')
		.($showalldescriptors ? '' : '
			-- only show active ones
			JOIN {'.BLOCK_EXACOMP_DB_DESCTOPICS.'} topmm ON topmm.topicid=t.id
			JOIN {'.BLOCK_EXACOMP_DB_DESCRIPTORS.'} d ON topmm.descrid=d.id
			JOIN {'.BLOCK_EXACOMP_DB_COMPETENCE_ACTIVITY.'} da ON ((d.id=da.compid AND da.comptype = '.BLOCK_EXACOMP_TYPE_DESCRIPTOR.') OR (t.id=da.compid AND da.comptype = '.BLOCK_EXACOMP_TYPE_TOPIC.'))
				AND da.activityid IN ('.block_exacomp_get_allowed_course_modules_for_course_for_select($courseid).')
		');

	//GROUP By funktioniert nur mit allen feldern im select, aber nicht mit strings
	$params = array($courseid);
	if ($subjectid > 0) {
		$params[] = $subjectid;
	}

	$topics = $DB->get_records_sql($sql, $params);

	return block_exacomp_sort_items($topics, ['subj_' => BLOCK_EXACOMP_DB_SUBJECTS, '' => BLOCK_EXACOMP_DB_TOPICS]);
}

/**
 * receives a list of items and returns them sorted
 * @param unknown $items can be array of different types of items, like topics, subjects...
 * @param unknown $sortings associated array with sorting options
 * @throws \block_exacomp\moodle_exception
 * @return unknown sorted items
 */
function block_exacomp_sort_items(&$items, $sortings) {
	$sortings = (array)$sortings;

	uasort($items, function($a, $b) use ($sortings) {
		foreach ($sortings as $prefix => $sorting) {
			if (is_int($prefix)) {
				$prefix = '';
			}

			if ($sorting == BLOCK_EXACOMP_DB_SUBJECTS) {
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
				if ($a->{$prefix.'source'} != BLOCK_EXACOMP_DATA_SOURCE_CUSTOM && $b->{$prefix.'source'} == BLOCK_EXACOMP_DATA_SOURCE_CUSTOM) {
					return -1;
				}
				if ($a->{$prefix.'source'} == BLOCK_EXACOMP_DATA_SOURCE_CUSTOM && $b->{$prefix.'source'} != BLOCK_EXACOMP_DATA_SOURCE_CUSTOM) {
					return 1;
				}

				if ($a->{$prefix.'sorting'} < $b->{$prefix.'sorting'}) {
					return -1;
				}
				if ($a->{$prefix.'sorting'} > $b->{$prefix.'sorting'}) {
					return 1;
				}

				if ($a->{$prefix.'title'} != $b->{$prefix.'title'}) {
					return strcmp($a->{$prefix.'title'}, $b->{$prefix.'title'});
				}
			} elseif ($sorting == BLOCK_EXACOMP_DB_TOPICS) {
				if (!property_exists($a, $prefix.'sorting') || !property_exists($b, $prefix.'sorting')) {
					throw new \block_exacomp\moodle_exception('col not found: '.$prefix.'sorting');
				}
				if (!property_exists($a, $prefix.'numb') || !property_exists($b, $prefix.'numb')) {
					throw new \block_exacomp\moodle_exception('col not found: '.$prefix.'numb');
				}
				if (!property_exists($a, $prefix.'title') || !property_exists($b, $prefix.'title')) {
					throw new \block_exacomp\moodle_exception('col not found: '.$prefix.'title');
				}

				if ($a->{$prefix.'numb'} < $b->{$prefix.'numb'}) {
					return -1;
				}
				if ($a->{$prefix.'numb'} > $b->{$prefix.'numb'}) {
					return 1;
				}

				if ($a->{$prefix.'sorting'} < $b->{$prefix.'sorting'}) {
					return -1;
				}
				if ($a->{$prefix.'sorting'} > $b->{$prefix.'sorting'}) {
					return 1;
				}

				if ($a->{$prefix.'title'} != $b->{$prefix.'title'}) {
					return strcmp($a->{$prefix.'title'}, $b->{$prefix.'title'});
				}
			} elseif ($sorting == BLOCK_EXACOMP_DB_DESCRIPTORS) {
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
						if ($a->{$prefix.'source'} == BLOCK_EXACOMP_DATA_SOURCE_CUSTOM) {
							return 1;
						}
						if ($b->{$prefix.'source'} == BLOCK_EXACOMP_DATA_SOURCE_CUSTOM) {
							return -1;
						}
					}
				}

				if ($a->{$prefix.'sorting'} < $b->{$prefix.'sorting'}) {
					return -1;
				}
				if ($a->{$prefix.'sorting'} > $b->{$prefix.'sorting'}) {
					return 1;
				}

				// last by title
				if ($a->{$prefix.'title'} != $b->{$prefix.'title'}) {
					return strcmp($a->{$prefix.'title'}, $b->{$prefix.'title'});
				}
			} elseif ($sorting == BLOCK_EXACOMP_DB_NIVEAUS) {
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

					if ($a->{$prefix.'sorting'} < $b->{$prefix.'sorting'}) {
						return -1;
					}
					if ($a->{$prefix.'sorting'} > $b->{$prefix.'sorting'}) {
						return 1;
					}
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
			FROM {'.BLOCK_EXACOMP_DB_SUBJECTS.'} s
			JOIN {'.BLOCK_EXACOMP_DB_TOPICS.'} t ON t.subjid = s.id
			'.($subjectid == null ? '' : '
					-- only show active ones
					WHERE s.id = ?
					').'
			', array($subjectid));

	return block_exacomp_sort_items($topics, ['subj_' => BLOCK_EXACOMP_DB_SUBJECTS, '' => BLOCK_EXACOMP_DB_TOPICS]);
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

	if (!block_exacomp_get_settings_by_course($courseid)->uses_activities) {
		return true;
	}

	$cms = get_course_mods($courseid);

	foreach ($cms as $cm) {
		if ($DB->record_exists(BLOCK_EXACOMP_DB_COMPETENCE_ACTIVITY, array("compid" => $compid, "comptype" => $comptype, "activityid" => $cm->id))) {
			return true;
		}
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

	block_exacomp_require_item_capability(BLOCK_EXACOMP_CAP_DELETE, $example);

	$DB->delete_records(BLOCK_EXACOMP_DB_EXAMPLES, array('id' => $example->id));
	$DB->delete_records(BLOCK_EXACOMP_DB_DESCEXAMP, array('exampid' => $example->id));
	$DB->delete_records(BLOCK_EXACOMP_DB_EXAMPLEEVAL, array('exampleid' => $example->id));

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

	if ($evalniveauid !== null && $evalniveauid < 1) {
		$evalniveauid = null;
	}

	// TODO: block_exacomp_external::require_teacher_permission($courseid, $userid);
	if ($role == BLOCK_EXACOMP_ROLE_STUDENT && $userid != $USER->id) {
		return -1;
	}
	if ($role == BLOCK_EXACOMP_ROLE_TEACHER) {
		block_exacomp_require_teacher($courseid);
	}

	block_exacomp_set_comp_eval($courseid, $role, $userid, $comptype, $compid, [
		'value' => $value,
		'evalniveauid' => $evalniveauid,
		'reviewerid' => $USER->id,
	]);

	if ($role == BLOCK_EXACOMP_ROLE_TEACHER) {
		block_exacomp_send_grading_notification($USER, $DB->get_record('user', array('id' => $userid)), $courseid);
	} else {
		block_exacomp_notify_all_teachers_about_self_assessment($courseid);
	}

	\block_exacomp\event\competence_assigned::log(['objecttable' => ($comptype == BLOCK_EXACOMP_TYPE_DESCRIPTOR) ? 'block_exacompdescriptors' : 'block_exacomptopics', 'objectid' => $compid, 'courseid' => $courseid, 'relateduserid' => $userid]);

	return 1;
}

function block_exacomp_set_user_example($userid, $exampleid, $courseid, $role, $value = null, $evalniveauid = null) {
	global $USER;

	$updateEvaluation = new stdClass();
	if ($evalniveauid < 1) {
		$evalniveauid = null;
	}

	if ($role == BLOCK_EXACOMP_ROLE_TEACHER) {
		block_exacomp_require_teacher($courseid);

		$data = [
			'value' => $value,
			'reviewerid' => $USER->id,
			'timestamp' => time(),
			'evalniveauid' => $evalniveauid,
			'resubmission' => ($value >= 0) ? false : true,
		];
	} elseif ($userid != $USER->id) {
		// student can only assess himself
		return;
	} else {
		$data = [
			'timestamp' => time(),
			'value' => $value,
		];
	}

	block_exacomp_set_comp_eval($courseid, $role, $userid, BLOCK_EXACOMP_TYPE_EXAMPLE, $exampleid, $data);

	if ($role == BLOCK_EXACOMP_ROLE_TEACHER) {
		\block_exacomp\event\competence_assigned::log(['objectid' => $exampleid, 'courseid' => $courseid, 'relateduserid' => $userid]);
	}
}

/**
 * call this function to allow resubmission of example submission for student
 * @param unknown $userid
 * @param unknown $exampleid
 * @param unknown $courseid
 * @return boolean
 */
function block_exacomp_allow_resubmission($userid, $exampleid, $courseid) {
	global $DB;

	block_exacomp_require_teacher($courseid);

	$exameval = $DB->get_record(BLOCK_EXACOMP_DB_EXAMPLEEVAL, array('courseid' => $courseid, 'studentid' => $userid, 'exampleid' => $exampleid));
	if ($exameval) {
		$exameval->resubmission = 1;
		$DB->update_record(BLOCK_EXACOMP_DB_EXAMPLEEVAL, $exameval);

		return block_exacomp_get_string('allow_resubmission_info');
	}

	return false;
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
				if (is_array($evaluations)) {
					if (isset($evaluations['teacher'])) {
						$value = intval($evaluations['teacher']);
					} else {
						$value = intval($evaluations['student']);
					}
				}
				$values[] = array('user' => intval($studentidKey), 'compid' => intval($compidKey), 'value' => $value);
			}
		}
	}
	if (!$subjectid) {
		block_exacomp_reset_comp_data($courseid, $role, $comptype, (($role == BLOCK_EXACOMP_ROLE_STUDENT)) ? $USER->id : false, $topicid);
	} else {
		$studentid = ($role == BLOCK_EXACOMP_ROLE_STUDENT) ? $USER->id : required_param('studentid', PARAM_INT);
		block_exacomp_reset_comp_data_for_subject($courseid, $role, $comptype, $studentid, $subjectid);
	}
	foreach ($values as $value) {
		block_exacomp_set_user_competence($value['user'], $value['compid'], $comptype, $courseid, $role, $value['value']);
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

	$DB->delete_records_select('block_exacompcompuser', $select, array($courseid, $role, $comptype, $userid, $subjectid));
}

/**
 * Gets settings for the current course
 * @param int $courseid
 */
function block_exacomp_get_settings_by_course($courseid = 0) {
	if (!$courseid) {
		$courseid = g::$COURSE->id;
	}

	$settings = g::$DB->get_record(BLOCK_EXACOMP_DB_SETTINGS, array("courseid" => $courseid));

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
	if (empty($settings->nostudents)) {
		$settings->nostudents = 0;
	}
	$settings->work_with_students = !$settings->nostudents;

	if (!isset($settings->uses_activities)) {
		$settings->uses_activities = 0;
	}
	if (!isset($settings->show_all_examples)) {
		$settings->show_all_examples = block_exacomp_is_skillsmanagement() ? 1 : 0;
	}
	if (!$settings->uses_activities) {
		$settings->show_all_descriptors = 1;
	}
	if (!isset($settings->show_all_descriptors)) {
		$settings->show_all_descriptors = 1;
	}
	if (isset($settings->filteredtaxonomies)) {
		$settings->filteredtaxonomies = json_decode($settings->filteredtaxonomies, true);
	} else {
		$settings->filteredtaxonomies = array(BLOCK_EXACOMP_SHOW_ALL_TAXONOMIES);
	}

	return $settings;
}


/**
 *
 * returns all descriptors
 * @param $courseid if course id =0 all possible descriptors are returned
 */
function block_exacomp_get_descriptors($courseid = 0, $showalldescriptors = false, $subjectid = 0, $showallexamples = true, $filteredtaxonomies = array(BLOCK_EXACOMP_SHOW_ALL_TAXONOMIES), $showonlyvisible = false, $include_childs = true) {
	if (!$courseid) {
		$showalldescriptors = true;
		$showonlyvisible = false;
	}
	if (!$showalldescriptors) {
		$showalldescriptors = block_exacomp_get_settings_by_course($courseid)->show_all_descriptors;
	}


	$sql = '
		SELECT DISTINCT desctopmm.id as u_id, d.id as id, d.title, d.source, d.niveauid, t.id AS topicid, d.profoundness, d.parentid, n.sorting AS niveau_sorting, n.title AS niveau_title, dvis.visible as visible, desctopmm.sorting
		FROM {'.BLOCK_EXACOMP_DB_TOPICS.'} t
		'.(($courseid > 0) ? ' JOIN {'.BLOCK_EXACOMP_DB_COURSETOPICS.'} topmm ON topmm.topicid=t.id AND topmm.courseid=? '.(($subjectid > 0) ? ' AND t.subjid = '.$subjectid.' ' : '') : '').'
		JOIN {'.BLOCK_EXACOMP_DB_DESCTOPICS.'} desctopmm ON desctopmm.topicid=t.id
		JOIN {'.BLOCK_EXACOMP_DB_DESCRIPTORS.'} d ON desctopmm.descrid=d.id AND d.parentid=0
		-- left join, because courseid=0 has no descvisibility!
		LEFT JOIN {'.BLOCK_EXACOMP_DB_DESCVISIBILITY.'} dvis ON dvis.descrid=d.id AND dvis.studentid=0 AND dvis.courseid=?
		'.($showonlyvisible ? ' AND dvis.visible = 1 ' : '').'
		LEFT JOIN {'.BLOCK_EXACOMP_DB_NIVEAUS.'} n ON d.niveauid = n.id
		'.($showalldescriptors ? '' : '
			JOIN {'.BLOCK_EXACOMP_DB_COMPETENCE_ACTIVITY.'} ca ON d.id=ca.compid AND ca.comptype='.BLOCK_EXACOMP_TYPE_DESCRIPTOR.'
				AND ca.activityid IN ('.block_exacomp_get_allowed_course_modules_for_course_for_select($courseid).')
		');

	$descriptors = block_exacomp\descriptor::get_objects_sql($sql, array($courseid, $courseid, $courseid, $courseid));

	foreach ($descriptors as $descriptor) {
		if ($include_childs) {
			$descriptor = block_exacomp_get_examples_for_descriptor($descriptor, $filteredtaxonomies, $showallexamples, $courseid);
			$descriptor->children = block_exacomp_get_child_descriptors($descriptor, $courseid, $showalldescriptors, $filteredtaxonomies, $showallexamples, true, $showonlyvisible);
			$descriptor->categories = block_exacomp_get_categories_for_descriptor($descriptor);
		}
		/*
		if ($include_childs === true || (is_array($include_childs) && in_array(BLOCK_EXACOMP_DB_EXAMPLES, $include_childs))) {
			//get examples
			$descriptor = block_exacomp_get_examples_for_descriptor($descriptor, $filteredtaxonomies, $showallexamples, $courseid);
		}

		if ($include_childs === true || (is_array($include_childs) && in_array(BLOCK_EXACOMP_DB_DESCRIPTORS, $include_childs))) {
		   //check for child-descriptors
			$descriptor->children = block_exacomp_get_child_descriptors($descriptor,$courseid, $showalldescriptors, $filteredtaxonomies, $showallexamples, true, $showonlyvisible);
		}

		if (!is_array($include_childs) || in_array(BLOCK_EXACOMP_DB_CATEGORIES, $include_childs)) {
			$descriptor->categories = block_exacomp_get_categories_for_descriptor($descriptor);
		}
		*/
	}

	return block_exacomp_sort_items($descriptors, ['niveau_' => BLOCK_EXACOMP_DB_NIVEAUS, BLOCK_EXACOMP_DB_DESCRIPTORS]);
}

/**
 * return categories for specific descriptor (e.g. G, M, E for LIS data)
 * @param unknown $descriptor
 * @return unknown
 */
function block_exacomp_get_categories_for_descriptor($descriptor) {
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
		FROM {".BLOCK_EXACOMP_DB_CATEGORIES."} c
		JOIN {".BLOCK_EXACOMP_DB_DESCCAT."} dc ON dc.catid=c.id
		WHERE dc.descrid=?
		ORDER BY c.sorting
	", array($descriptor->id));

	return $categories;
}

/**
 * return child descriptors for parentdescriptor
 * @param unknown $parent - parentdescriptor
 * @param unknown $courseid
 * @param string $showalldescriptors - or only activated in course
 * @param array $filteredtaxonomies - return only those with chosen taxonomies
 * @param string $showallexamples - or exclude external
 * @param string $mindvisibility - return visible value
 * @param string $showonlyvisible - return only visible
 * @return unknown
 */
function block_exacomp_get_child_descriptors($parent, $courseid, $unusedShowalldescriptors = false, $filteredtaxonomies = array(BLOCK_EXACOMP_SHOW_ALL_TAXONOMIES), $showallexamples = true, $mindvisibility = true, $showonlyvisible = false) {
	global $DB;

	// old:
	// $DB->record_exists(BLOCK_EXACOMP_DB_DESCRIPTORS, array("parentid" => $parent->id))

	static $descparents = null;
	if ($descparents === null) {
		$descparents = $DB->get_records_menu(BLOCK_EXACOMP_DB_DESCRIPTORS, null, null, 'distinct parentid,parentid AS tmp');
	}

	if (!isset($descparents[$parent->id])) {
		return [];
	}

	if (!$courseid) {
		$showonlyvisible = false;
		$mindvisibility = false;
	}

	$sql = 'SELECT d.id, d.title, d.niveauid, d.source, '.$parent->topicid.' as topicid, d.profoundness, d.parentid, '.
		($mindvisibility ? 'dvis.visible as visible, ' : '').' d.sorting
			FROM {'.BLOCK_EXACOMP_DB_DESCRIPTORS.'} d '
		.($mindvisibility ? 'JOIN {'.BLOCK_EXACOMP_DB_DESCVISIBILITY.'} dvis ON dvis.descrid=d.id AND dvis.courseid=? AND dvis.studentid=0 '
			.($showonlyvisible ? 'AND dvis.visible=1 ' : '') : '');

	$sql .= ' WHERE d.parentid = ?';

	$params = array();
	if ($mindvisibility) {
		$params[] = $courseid;
	}

	$params[] = $parent->id;
	$descriptors = block_exacomp\descriptor::get_objects_sql($sql, $params);

	foreach ($descriptors as $descriptor) {
		$descriptor = block_exacomp_get_examples_for_descriptor($descriptor, $filteredtaxonomies, $showallexamples, $courseid);
		$descriptor->children = block_exacomp_get_child_descriptors($descriptor, $courseid, null /* unused */, $filteredtaxonomies);
		$descriptor->categories = block_exacomp_get_categories_for_descriptor($descriptor);
	}

	return block_exacomp_sort_items($descriptors, BLOCK_EXACOMP_DB_DESCRIPTORS);
}

/**
 * return descriptor with examples
 * @param unknown $descriptor - is returned again
 * @param array $filteredtaxonomies - only chosen taxonomies
 * @param string $showallexamples - exclude external or not
 * @param unknown $courseid
 * @param string $mind_visibility - return visibie field
 * @param string $showonlyvisible - return only visible
 * @return unknown
 */
function block_exacomp_get_examples_for_descriptor($descriptor, $filteredtaxonomies = array(BLOCK_EXACOMP_SHOW_ALL_TAXONOMIES), $showallexamples = true, $courseid = null, $mind_visibility = true, $showonlyvisible = false) {
	global $COURSE;

	if ($courseid == null) {
		$courseid = $COURSE->id;
	}

	$examples = \block_exacomp\example::get_objects_sql(
		"SELECT DISTINCT de.id as deid, e.id, e.title, e.externalurl, e.source, e.sourceid,
			e.externalsolution, e.externaltask, e.completefile, e.description, e.creatorid, e.iseditable, e.tips, e.timeframe, e.author
			, de.sorting
			FROM {".BLOCK_EXACOMP_DB_EXAMPLES."} e
			JOIN {".BLOCK_EXACOMP_DB_DESCEXAMP."} de ON e.id=de.exampid AND de.descrid=?"
		." WHERE "
		." e.source != ".BLOCK_EXACOMP_EXAMPLE_SOURCE_USER." AND "
		.($showallexamples ? " 1=1 " : " e.creatorid > 0")
		." ORDER BY de.sorting"
		, array($descriptor->id, $courseid, $courseid));

	// old
	if ($mind_visibility || $showonlyvisible) {
		foreach ($examples as $example) {
			$example->visible = block_exacomp_is_example_visible($courseid, $example, 0);
			$example->solution_visible = block_exacomp_is_example_solution_visible($courseid, $example, 0);

			if ($showonlyvisible && !$example->visible) {
				unset($examples[$example->id]);
			}
		}
	}

	foreach ($examples as $example) {
		$example->descriptor = $descriptor;
		$example->taxonomies = block_exacomp_get_taxonomies_by_example($example);

		$taxtitle = "";
		foreach ($example->taxonomies as $taxonomy) {
			$taxtitle .= $taxonomy->title.", ";
		}

		$taxtitle = substr($taxtitle, 0, strlen($taxtitle) - 1);
		$example->tax = $taxtitle;
	}
	$filtered_examples = array();
	if (!in_array(BLOCK_EXACOMP_SHOW_ALL_TAXONOMIES, $filteredtaxonomies)) {
		$filtered_taxonomies = implode(",", $filteredtaxonomies);

		foreach ($examples as $example) {
			if ($example->taxonomies) {
				foreach ($example->taxonomies as $taxonomy) {
					if (in_array($taxonomy->id, $filteredtaxonomies)) {
						if (!array_key_exists($example->id, $filtered_examples)) {
							$filtered_examples[$example->id] = $example;
						}
						continue;
					}
				}
			}
		}
	} else {
		$filtered_examples = $examples;
	}

	$descriptor->examples = array();
	foreach ($filtered_examples as $example) {
		$descriptor->examples[$example->id] = $example;
	}

	return $descriptor;
}

/**
 * get taxonomies for a certain example
 * @param unknown $example
 */
function block_exacomp_get_taxonomies_by_example($example) {
	global $DB;

	return $DB->get_records_sql("
		SELECT tax.*
		FROM {".BLOCK_EXACOMP_DB_TAXONOMIES."} tax
		JOIN {".BLOCK_EXACOMP_DB_EXAMPTAX."} et ON tax.id = et.taxid
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
function block_exacomp_get_descriptors_by_topic($courseid, $topicid, $showalldescriptors = false, $mind_visibility = false, $showonlyvisible = true) {
	global $DB;

	if (!$showalldescriptors) {
		$showalldescriptors = block_exacomp_get_settings_by_course($courseid)->show_all_descriptors;
	}

	$sql = '
		SELECT DISTINCT d.id, desctopmm.id as u_id, d.title, d.niveauid, t.id AS topicid, d.requirement, d.knowledgecheck, d.benefit, d.sorting, d.parentid, n.title as cattitle
		FROM {'.BLOCK_EXACOMP_DB_TOPICS.'} t JOIN {'.BLOCK_EXACOMP_DB_COURSETOPICS.'} topmm ON topmm.topicid=t.id AND topmm.courseid=? '.(($topicid > 0) ? ' AND t.id = '.$topicid.' ' : '').'
		JOIN {'.BLOCK_EXACOMP_DB_DESCTOPICS.'} desctopmm ON desctopmm.topicid=t.id
		JOIN {'.BLOCK_EXACOMP_DB_DESCRIPTORS.'} d ON desctopmm.descrid=d.id AND d.parentid=0
		LEFT JOIN {'.BLOCK_EXACOMP_DB_NIVEAUS.'} n ON n.id = d.niveauid '
		.($mind_visibility ? 'JOIN {'.BLOCK_EXACOMP_DB_DESCVISIBILITY.'} dvis ON dvis.descrid=d.id AND dvis.studentid=0 AND dvis.courseid=? '
			.($showonlyvisible ? 'AND dvis.visible = 1 ' : '') : '')
		.($showalldescriptors ? '' : '
			JOIN {'.BLOCK_EXACOMP_DB_COMPETENCE_ACTIVITY.'} ca ON d.id=ca.compid AND ca.comptype='.BLOCK_EXACOMP_TYPE_DESCRIPTOR.'
				AND ca.activityid IN ('.block_exacomp_get_allowed_course_modules_for_course_for_select($courseid).')
		').'
		ORDER BY d.sorting';

	$descriptors = $DB->get_records_sql($sql, array($courseid, $courseid, $courseid));

	/*
	foreach($descriptors as $descriptor){
		$descriptor->categories = block_exacomp_get_categories_for_descriptor($descriptor);
	}
	*/

	return $descriptors;
}

/**
 * Returns descriptors for a given subject
 * @param int $subjectid
 * @param bool $niveaus default false, if true only descriptors with neveaus are returned
 * @return multitype:
 */
function block_exacomp_get_descriptors_by_subject($subjectid, $niveaus = true) {
	global $DB;

	$sql = "SELECT d.*, dt.topicid, t.title as topic_title FROM {".BLOCK_EXACOMP_DB_DESCRIPTORS."} d, {".BLOCK_EXACOMP_DB_DESCTOPICS."} dt, {".BLOCK_EXACOMP_DB_TOPICS."} t
	WHERE d.id=dt.descrid AND d.parentid =0 AND dt.topicid IN (SELECT id FROM {".BLOCK_EXACOMP_DB_TOPICS."} WHERE subjid=?)";
	if ($niveaus) {
		$sql .= " AND d.niveauid > 0";
	}
	$sql .= " AND dt.topicid = t.id order by d.skillid, dt.topicid, d.niveauid";

	return $DB->get_records_sql($sql, array($subjectid));
}

/**
 * get descriptors associated with example
 * @param unknown $exampleid
 */
function block_exacomp_get_descriptors_by_example($exampleid) {
	global $DB;

	return $DB->get_records_sql("
		SELECT d.*, de.id AS descexampid
		FROM {".BLOCK_EXACOMP_DB_DESCRIPTORS."} d
		JOIN {".BLOCK_EXACOMP_DB_DESCEXAMP."} de ON de.descrid=d.id
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
function block_exacomp_get_competence_tree($courseid = 0, $subjectid = null, $topicid = null, $showalldescriptors = false, $niveauid = null, $showallexamples = true, $filteredtaxonomies = array(BLOCK_EXACOMP_SHOW_ALL_TAXONOMIES), $calledfromoverview = false, $calledfromactivities = false, $showonlyvisible = false, $without_descriptors = false, $showonlyvisibletopics = false, $include_childs = true) {
	global $DB;

	if (!$showalldescriptors) {
		$showalldescriptors = block_exacomp_get_settings_by_course($courseid)->show_all_descriptors;
	}

	$selectedTopic = null;
	if ($topicid && $calledfromoverview) {
		$selectedTopic = $DB->get_record(BLOCK_EXACOMP_DB_TOPICS, array('id' => $topicid));
	}

	// 1. GET SUBJECTS
	if ($courseid == 0) {
		$allSubjects = block_exacomp_get_all_subjects();
	} elseif ($subjectid) {
		$allSubjects = array($subjectid => \block_exacomp\subject::get($subjectid));
	} else {
		$allSubjects = block_exacomp_get_subjects_by_course($courseid, $showalldescriptors);
	}

	// 2. GET TOPICS
	$allTopics = block_exacomp_get_all_topics($subjectid, $showonlyvisible);
	if ($courseid > 0) {
		if ((!$calledfromoverview && !$calledfromactivities) || !$selectedTopic) {
			$courseTopics = block_exacomp_get_topics_by_subject($courseid, $subjectid, $showalldescriptors, $showonlyvisibletopics);
		} elseif (isset($selectedTopic)) {
			$courseTopics = \block_exacomp\topic::get($selectedTopic->id);
			if (!$courseTopics) {
				$courseTopics = array();
			} else {
				$courseTopics = array($courseTopics->id => $courseTopics);
			}
		}
	}

	// 3. GET DESCRIPTORS
	if ($without_descriptors) {
		$allDescriptors = array();
	} else {
		$allDescriptors = block_exacomp_get_descriptors($courseid, $showalldescriptors, 0, $showallexamples, $filteredtaxonomies, $showonlyvisible, $include_childs);
	}

	foreach ($allDescriptors as $descriptor) {

		if ($niveauid != BLOCK_EXACOMP_SHOW_ALL_NIVEAUS && $calledfromoverview) {
			if ($descriptor->niveauid != $niveauid) {
				continue;
			}
		}

		// get descriptor topic
		if (empty($allTopics[$descriptor->topicid])) {
			continue;
		}
		$topic = $allTopics[$descriptor->topicid];
		$topic->descriptors[$descriptor->id] = $descriptor;
	}

	$subjects = array();

	foreach ($allSubjects as $subject) {
		$subject->topics = [];
	}

	foreach ($allTopics as $topic) {
		//topic must be coursetopic if courseid <> 0
		if ($courseid > 0 && !array_key_exists($topic->id, $courseTopics)) {
			continue;
		}

		// find subject
		if (empty($allSubjects[$topic->subjid])) {
			continue;
		}
		$subject = $allSubjects[$topic->subjid];
		if (!isset($topic->descriptors)) {
			$topic->descriptors = array();
		}
		$topic = block_exacomp\topic::create($topic);

		// found: add it to the subject result
		$subject->topics[$topic->id] = $topic;
		$subjects[$subject->id] = $subject;
	}

	// sort topics
	foreach ($subjects as $subject) {
		block_exacomp_sort_items($subject->topics, BLOCK_EXACOMP_DB_TOPICS);
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
function block_exacomp_init_overview_data($courseid, $subjectid, $topicid, $niveauid, $editmode, $isTeacher = true, $studentid = 0, $showonlyvisible = false) {
	$courseTopics = block_exacomp_get_topics_by_course($courseid, false, $showonlyvisible ? (($isTeacher) ? false : true) : false);
	$courseSubjects = block_exacomp_get_subjects_by_course($courseid);

	$topic = new \stdClass();
	$topic->id = $topicid;

	$selectedSubject = null;
	$selectedTopic = null;
	if ($subjectid) {
		if (!empty($courseSubjects[$subjectid])) {
			$selectedSubject = $courseSubjects[$subjectid];

			$topics = block_exacomp_get_topics_by_subject($courseid, $selectedSubject->id, false, ($showonlyvisible ? (($isTeacher) ? false : true) : false));
			if ($topicid == BLOCK_EXACOMP_SHOW_ALL_TOPICS) {
				// no $selectedTopic
			} elseif ($topicid && isset($topics[$topicid]) && block_exacomp_is_topic_visible($courseid, $topic, $studentid)) {
				$selectedTopic = $topics[$topicid];
			} else {
				// select first visible topic
				foreach ($topics as $tmpTopic) {
					if (block_exacomp_is_topic_visible($courseid, $tmpTopic, $studentid)) {
						$selectedTopic = $tmpTopic;
						break;
					}
				}
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

		$topics = block_exacomp_get_topics_by_subject($courseid, $selectedSubject->id, false, ($showonlyvisible ? (($isTeacher) ? false : true) : false));
		// select first visible topic
		foreach ($topics as $tmpTopic) {
			if (block_exacomp_is_topic_visible($courseid, $tmpTopic, $studentid)) {
				$selectedTopic = $tmpTopic;
				break;
			}
		}
	}

	// load all descriptors first (needed for teacher)
	if ($editmode) {
		$descriptors = block_exacomp_get_descriptors_by_topic($courseid, $selectedTopic ? $selectedTopic->id : null, true, false, false);
	} else {
		$descriptors = block_exacomp_get_descriptors_by_topic($courseid, $selectedTopic ? $selectedTopic->id : null, false, true, true);
	}

	if (!$selectedTopic) {
		// $descriptors contains all descriptors for this course, so filter it for just descriptors of selected subject
		foreach ($descriptors as $key => $descriptor) {
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
	$niveaus = g::$DB->get_records_list(BLOCK_EXACOMP_DB_NIVEAUS, 'id', $niveau_ids, 'sorting');
	$niveaus = \block_exacomp\niveau::create_objects($niveaus);

	$defaultNiveau = block_exacomp\niveau::create();
	$defaultNiveau->id = BLOCK_EXACOMP_SHOW_ALL_NIVEAUS;
	$defaultNiveau->title = block_exacomp_get_string('allniveaus');
	$defaultNiveau->source = 0;

	$niveaus = array($defaultNiveau->id => $defaultNiveau) + $niveaus;

	if (isset($niveaus[$niveauid])) {
		$selectedNiveau = $niveaus[$niveauid];
	} else {
		// default: show all
		$selectedNiveau = reset($niveaus);
	}

	// add topics to subjects
	foreach ($courseSubjects as $subject) {
		$subject->topics = [];
	}
	foreach ($courseTopics as $topic) {
		$courseSubjects[$topic->subjid]->topics[$topic->id] = $topic;
	}

	return array($courseSubjects, $courseTopics, $niveaus, $selectedSubject, $selectedTopic, $selectedNiveau);
}

/**
 *
 * Returns all students enroled to a particular course
 * @param unknown_type $courseid
 */
function block_exacomp_get_students_by_course($courseid) {
	$context = context_course::instance($courseid);

	$students = get_users_by_capability($context, 'block/exacomp:student', '', 'lastname,firstname');

	// TODO ggf user mit exacomp:teacher hier filtern?
	return $students;
}

/**
 *
 * Returns all teacher enroled to a course
 * @param unknown_type $courseid
 */
function block_exacomp_get_teachers_by_course($courseid) {
	$context = context_course::instance($courseid);

	return get_enrolled_users($context, 'block/exacomp:teacher');
}

/**
 * Returns all the import information for a particular user in the given course about his competencies, topics and example evaluation values
 *
 * It returns user objects in the following format
 *         $user
 *             ->competencies
 *                 ->teacher[competenceid] = competence value
 *                 ->student[competenceid] = competence value
 *             ->topics
 *                 ->teacher
 *                 ->student
 *
 * @param sdtClass $user
 * @param int $courseid
 * @return stdClass $ser
 */
function block_exacomp_get_user_information_by_course($user, $courseid, $onlycomps = false) {
	// get student competencies
	$user = block_exacomp_get_user_competences_by_course($user, $courseid);
	// get student topics
	$user = block_exacomp_get_user_topics_by_course($user, $courseid);
	// get student crosssubs
	$user = block_exacomp_get_user_crosssubs_by_course($user, $courseid);
	// get student subjects
	$user = block_exacomp_get_user_subjects_by_course($user, $courseid);

	if (!$onlycomps) {
		// get student exampl
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
	$user->crosssubs->teacher = $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_TEACHER, "comptype" => BLOCK_EXACOMP_TYPE_CROSSSUB), '', 'compid as id, value');
	$user->crosssubs->student = $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_STUDENT, "comptype" => BLOCK_EXACOMP_TYPE_CROSSSUB), '', 'compid as id, value');
	$user->crosssubs->timestamp_teacher = $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_TEACHER, "comptype" => BLOCK_EXACOMP_TYPE_CROSSSUB), '', 'compid as id, timestamp');
	$user->crosssubs->timestamp_student = $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_STUDENT, "comptype" => BLOCK_EXACOMP_TYPE_CROSSSUB), '', 'compid as id, timestamp');
	$user->crosssubs->teacher_additional_grading = $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_TEACHER, "comptype" => BLOCK_EXACOMP_TYPE_CROSSSUB), '', 'compid as id, additionalinfo');
	$user->crosssubs->niveau = $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_TEACHER, "comptype" => BLOCK_EXACOMP_TYPE_CROSSSUB), '', 'compid as id, evalniveauid');

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
	$user->competencies->teacher = $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_TEACHER, "comptype" => BLOCK_EXACOMP_TYPE_DESCRIPTOR), '', 'compid as id, value');
	$user->competencies->student = $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_STUDENT, "comptype" => BLOCK_EXACOMP_TYPE_DESCRIPTOR), '', 'compid as id, value');
	$user->competencies->timestamp_teacher = $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_TEACHER, "comptype" => BLOCK_EXACOMP_TYPE_DESCRIPTOR), '', 'compid as id, timestamp');
	$user->competencies->timestamp_student = $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_STUDENT, "comptype" => BLOCK_EXACOMP_TYPE_DESCRIPTOR), '', 'compid as id, timestamp');
	$user->competencies->teacher_additional_grading = $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_TEACHER, "comptype" => BLOCK_EXACOMP_TYPE_DESCRIPTOR), '', 'compid as id, additionalinfo');
	$user->competencies->niveau = $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_TEACHER, "comptype" => BLOCK_EXACOMP_TYPE_DESCRIPTOR), '', 'compid as id, evalniveauid');

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
	$user->topics->teacher = $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_TEACHER, "comptype" => BLOCK_EXACOMP_TYPE_TOPIC), '', 'compid as id, value');
	$user->topics->student = $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_STUDENT, "comptype" => BLOCK_EXACOMP_TYPE_TOPIC), '', 'compid as id, value');
	$user->topics->timestamp_teacher = $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_TEACHER, "comptype" => BLOCK_EXACOMP_TYPE_TOPIC), '', 'compid as id, timestamp');
	$user->topics->timestamp_student = $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_STUDENT, "comptype" => BLOCK_EXACOMP_TYPE_TOPIC), '', 'compid as id, timestamp');
	$user->topics->teacher_additional_grading = $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_TEACHER, "comptype" => BLOCK_EXACOMP_TYPE_TOPIC), '', 'compid as id, additionalinfo');
	$user->topics->niveau = $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_TEACHER, "comptype" => BLOCK_EXACOMP_TYPE_TOPIC), '', 'compid as id, evalniveauid');

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
	$user->subjects->teacher = $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_TEACHER, "comptype" => BLOCK_EXACOMP_TYPE_SUBJECT), '', 'compid as id, value');
	$user->subjects->student = $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_STUDENT, "comptype" => BLOCK_EXACOMP_TYPE_SUBJECT), '', 'compid as id, value');
	$user->subjects->timestamp_teacher = $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_TEACHER, "comptype" => BLOCK_EXACOMP_TYPE_SUBJECT), '', 'compid as id, timestamp');
	$user->subjects->timestamp_student = $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_STUDENT, "comptype" => BLOCK_EXACOMP_TYPE_SUBJECT), '', 'compid as id, timestamp');
	$user->subjects->teacher_additional_grading = $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_TEACHER, "comptype" => BLOCK_EXACOMP_TYPE_SUBJECT), '', 'compid as id, additionalinfo');
	$user->subjects->niveau = $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_TEACHER, "comptype" => BLOCK_EXACOMP_TYPE_SUBJECT), '', 'compid as id, evalniveauid');

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
	$examples->teacher = g::$DB->get_records_menu(BLOCK_EXACOMP_DB_EXAMPLEEVAL, array("courseid" => $courseid, "studentid" => $user->id), '', 'exampleid as id, teacher_evaluation as value');
	$examples->student = g::$DB->get_records_menu(BLOCK_EXACOMP_DB_EXAMPLEEVAL, array("courseid" => $courseid, "studentid" => $user->id), '', 'exampleid as id, student_evaluation as value');
	$examples->niveau = g::$DB->get_records_menu(BLOCK_EXACOMP_DB_EXAMPLEEVAL, array("courseid" => $courseid, "studentid" => $user->id), '', 'exampleid as id, evalniveauid');
	$examples->timestamp_teacher = g::$DB->get_records_menu(BLOCK_EXACOMP_DB_EXAMPLEEVAL, array("courseid" => $courseid, "studentid" => $user->id), '', 'exampleid as id, timestamp_teacher as timestamp');
	$examples->timestamp_student = g::$DB->get_records_menu(BLOCK_EXACOMP_DB_EXAMPLEEVAL, array("courseid" => $courseid, "studentid" => $user->id), '', 'exampleid as id, timestamp_student as timestamp');

	return $examples;
}

/**
 *  This method returns all topics for the detailed view for a given user
 *
 * @param object $user
 * @param int $courseid
 */
function block_exacomp_get_user_activities_topics_by_course($user, $courseid, $activities) {
	global $DB;

	$user->activities_topics = new stdClass();
	$user->activities_topics->activities = array();

	foreach ($activities as $activity) {
		$user->activities_topics->activities[$activity->id] = new stdClass();

		$user->activities_topics->activities[$activity->id]->teacher = array();
		$user->activities_topics->activities[$activity->id]->student = array();

		$user->activities_topics->activities[$activity->id]->teacher += $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCE_USER_MM, array("activityid" => $activity->id, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_TEACHER, "comptype" => BLOCK_EXACOMP_TYPE_TOPIC), '', 'compid as id, value');
		$user->activities_topics->activities[$activity->id]->student += $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCE_USER_MM, array("activityid" => $activity->id, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_STUDENT, "comptype" => BLOCK_EXACOMP_TYPE_TOPIC), '', 'compid as id, value');
	}

	return $user;
}

/**
 *  This method returns all competencies for the detailed view for a given user
 *
 * @param object $user
 * @param int $courseid
 */
function block_exacomp_get_user_activities_competences_by_course($user, $courseid, $activities) {
	global $DB;

	$user->activities_competencies = new stdClass();
	$user->activities_competencies->activities = array();

	foreach ($activities as $activity) {
		$user->activities_competencies->activities[$activity->id] = new stdClass();

		$user->activities_competencies->activities[$activity->id]->teacher = array();
		$user->activities_competencies->activities[$activity->id]->student = array();
		$user->activities_competencies->activities[$activity->id]->teacher += $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCE_USER_MM, array("activityid" => $activity->id, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_TEACHER, "comptype" => BLOCK_EXACOMP_TYPE_DESCRIPTOR, "eportfolioitem" => 0), '', 'compid as id, value');
		$user->activities_competencies->activities[$activity->id]->student += $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCE_USER_MM, array("activityid" => $activity->id, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_STUDENT, "comptype" => BLOCK_EXACOMP_TYPE_DESCRIPTOR, "eportfolioitem" => 0), '', 'compid as id, value');
	}

	return $user;
}

/**
 * build navigation tabs for coursesettings
 * @param unknown $courseid
 * @return \block_exacomp\tabobject[]
 */
function block_exacomp_build_navigation_tabs_settings($courseid) {
	$usebadges = get_config('exacomp', 'usebadges');
	$courseSettings = block_exacomp_get_settings_by_course($courseid);
	$settings_subtree = array();

	$settings_subtree[] = new tabobject('tab_teacher_settings_configuration', new moodle_url('/blocks/exacomp/edit_course.php', array('courseid' => $courseid)), block_exacomp_get_string("tab_teacher_settings_configuration"), null, true);
	$settings_subtree[] = new tabobject('tab_teacher_settings_selection', new moodle_url('/blocks/exacomp/courseselection.php', array('courseid' => $courseid)), block_exacomp_get_string("tab_teacher_settings_selection"), null, true);

	if (block_exacomp_is_activated($courseid)) {
		if ($courseSettings->uses_activities) {
			$settings_subtree[] = new tabobject('tab_teacher_settings_assignactivities', new moodle_url('/blocks/exacomp/edit_activities.php', array('courseid' => $courseid)), block_exacomp_get_string("tab_teacher_settings_assignactivities"), null, true);
		}
	}

	if ($usebadges) {
		$settings_subtree[] = new tabobject('tab_teacher_settings_badges', new moodle_url('/blocks/exacomp/edit_badges.php', array('courseid' => $courseid)), block_exacomp_get_string("tab_teacher_settings_badges"), null, true);
	}

	return $settings_subtree;
}

/**
 * build navigation tabs for admin settings (Import, Webservice..)
 * @param unknown $courseid
 * @return \block_exacomp\tabobject[]
 */
function block_exacomp_build_navigation_tabs_admin_settings($courseid) {
	$checkImport = block_exacomp\data::has_data();

	$settings_subtree = array();

	if ($checkImport && has_capability('block/exacomp:admin', context_system::instance())) {
		$settings_subtree[] = new tabobject('tab_admin_configuration', new moodle_url('/blocks/exacomp/edit_config.php', array("courseid" => $courseid)), block_exacomp_get_string('tab_admin_configuration'), null, true);
	}

	$settings_subtree[] = new tabobject('tab_admin_import', new moodle_url('/blocks/exacomp/import.php', array('courseid' => $courseid)), block_exacomp_get_string("tab_admin_import"), null, true);

	if (get_config('exacomp', 'external_trainer_assign') && has_capability('block/exacomp:assignstudents', context_system::instance())) {
		$settings_subtree[] = new tabobject('tab_external_trainer_assign', new moodle_url('/blocks/exacomp/externaltrainers.php', array('courseid' => $courseid)), block_exacomp_get_string("block_exacomp_external_trainer_assign"), null, true);
	}
	$settings_subtree[] = new tabobject('tab_webservice_status', new moodle_url('/blocks/exacomp/webservice_status.php', array('courseid' => $courseid)), block_exacomp_trans(['de:Webservice Status', 'en:Check Webservices']), null, true);

	return $settings_subtree;
}

/**
 * build navigation tab for student profile
 * @param unknown $context
 * @param unknown $courseid
 * @return \block_exacomp\tabobject[]
 */
function block_exacomp_build_navigation_tabs_profile($context, $courseid) {
	if (block_exacomp_is_teacher($context)) {
		return array();
	}

	$profile_subtree = array();

	$profile_subtree[] = new tabobject('tab_competence_profile_profile', new moodle_url('/blocks/exacomp/competence_profile.php', array("courseid" => $courseid)), block_exacomp_get_string('tab_competence_profile_profile'), null, true);
	$profile_subtree[] = new tabobject('tab_competence_profile_settings', new moodle_url('/blocks/exacomp/competence_profile_settings.php', array("courseid" => $courseid)), block_exacomp_get_string('tab_competence_profile_settings'), null, true);

	return $profile_subtree;
}

/**
 * Build navigtion tabs, depending on role and version
 *
 * @param object $context
 * @param int $courseid
 */
function block_exacomp_build_navigation_tabs($context, $courseid) {
	global $USER;

	$globalcontext = context_system::instance();

	$courseSettings = block_exacomp_get_settings_by_course($courseid);
	$ready_for_use = block_exacomp_is_ready_for_use($courseid);

	$de = false;
	$lang = current_language();
	if (isset($lang) && substr($lang, 0, 2) === 'de') {
		$de = true;
	}
	if (block_exacomp_is_skillsmanagement()) {
		$checkConfig = block_exacomp_is_configured($courseid);
	} else {
		$checkConfig = block_exacomp_is_configured();
	}

	$has_data = \block_exacomp\data::has_data();

	$rows = array();

	$isTeacher = block_exacomp_is_teacher($context) && $courseid != 1;
	$isStudent = has_capability('block/exacomp:student', $context) && $courseid != 1 && !has_capability('block/exacomp:admin', $context);
	$isTeacherOrStudent = $isTeacher || $isStudent;

	if ($checkConfig && $has_data) {    //Modul wurde konfiguriert
		if ($isTeacherOrStudent && block_exacomp_is_activated($courseid)) {
			// moved into competence profile !
			// $rows[] = new tabobject('tab_competence_grid', new moodle_url('/blocks/exacomp/competence_grid.php',array("courseid"=>$courseid)),block_exacomp_get_string('tab_competence_grid'), null, true);
		}
		if ($isTeacherOrStudent && $ready_for_use) {
			//Kompetenzüberblick
			$rows[] = new tabobject('tab_competence_overview', new moodle_url('/blocks/exacomp/assign_competencies.php', array("courseid" => $courseid)), block_exacomp_get_string('tab_competence_overview'), null, true);

			if ($isTeacher || block_exacomp_get_cross_subjects_by_course($courseid, $USER->id)) {
				// Cross subjects: always for teacher and for students if it there are cross subjects
				$rows[] = new tabobject('tab_cross_subjects', new moodle_url('/blocks/exacomp/cross_subjects_overview.php', array("courseid" => $courseid)), block_exacomp_get_string('tab_cross_subjects'), null, true);
			}

			if (!$courseSettings->nostudents) {
				//Kompetenzprofil
				$rows[] = new tabobject('tab_competence_profile_profile', new moodle_url('/blocks/exacomp/competence_profile.php', array("courseid" => $courseid)), block_exacomp_get_string('tab_competence_profile'), null, true);
			}

			if (!$courseSettings->nostudents) {
				//Beispiel-Aufgaben
				$rows[] = new tabobject('tab_examples', new moodle_url('/blocks/exacomp/view_examples.php', array("courseid" => $courseid)), block_exacomp_get_string('tab_examples'), null, true);
			}

			if (!$courseSettings->nostudents) {
				//Wochenplan
				$rows[] = new tabobject('tab_weekly_schedule', new moodle_url('/blocks/exacomp/weekly_schedule.php', array("courseid" => $courseid)), block_exacomp_get_string('tab_weekly_schedule'), null, true);
			}

			if ($isTeacher && !$courseSettings->nostudents) {
				if ($courseSettings->useprofoundness) {
					$rows[] = new tabobject('tab_profoundness', new moodle_url('/blocks/exacomp/profoundness.php', array("courseid" => $courseid)), block_exacomp_get_string('tab_profoundness'), null, true);
				}

				//Meine Auszeichnungen
				//if ($usebadges) {
				//$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/my_badges.php', array('courseid'=>$courseid)), block_exacomp_get_string('tab_badges'), array('title'=>block_exacomp_get_string('tab_badges')));
				//$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/pix/i/badge.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
				//}
			}
		}

		if ($isTeacher) {
			//Einstellungen
			$rows[] = new tabobject('tab_teacher_settings', new moodle_url('/blocks/exacomp/edit_course.php', array("courseid" => $courseid)), block_exacomp_get_string('tab_teacher_settings'), null, true);
			$rows[] = new tabobject('tab_group_reports', new moodle_url('/blocks/exacomp/group_reports.php', array("courseid" => $courseid)), block_exacomp_get_string('tab_group_reports'), null, true);
		}
	}

	//if has_data && checkSubjects -> Modul wurde konfiguriert
	//else nur admin sieht block und hat nur den link Modulkonfiguration
	if (is_siteadmin() || (has_capability('block/exacomp:admin', $globalcontext) && !block_exacomp_is_skillsmanagement())) {
		//Admin sieht immer Modulkonfiguration
		//Wenn Import schon erledigt, weiterleitung zu edit_config, ansonsten import.
		if ($has_data) {
			$rows[] = new tabobject('tab_admin_settings', new moodle_url('/blocks/exacomp/edit_config.php', array("courseid" => $courseid)), block_exacomp_get_string('tab_admin_settings'), null, true);
		}
	}

	/*
	if ($de) {
		//Hilfe
		$rows[] = new tabobject('tab_help', new moodle_url('/blocks/exacomp/help.php', array("courseid"=>$courseid)), block_exacomp_get_string('tab_help'), null, true);
	}*/

	return $rows;
}

/**
 * build breadcrum
 * @param unknown $courseid
 */
function block_exacomp_build_breadcrum_navigation($courseid) {
	global $PAGE;
	$PAGE->navbar->add(block_exacomp_get_string('blocktitle'));
}

/**
 * Check if school specific import is enabled
 */
function block_exacomp_check_customupload() {
	return get_config('exacomp', 'enableteacherimport');
}

/**
 *
 * Get available education levels
 */
function block_exacomp_get_edulevels() {
	global $DB;

	return $DB->get_records(BLOCK_EXACOMP_DB_EDULEVELS, null, 'source');
}

/**
 *
 * Get schooltypes for particular education level
 * @param unknown_type $edulevel
 */
function block_exacomp_get_schooltypes($edulevel) {
	global $DB;

	return $DB->get_records(BLOCK_EXACOMP_DB_SCHOOLTYPES, array("elid" => $edulevel));
}

/**
 * Gets a subject's schooltype title
 *
 * @param object $subject
 * @return Ambigous <mixed, boolean>
 */
function block_exacomp_get_schooltype_title_by_subject($subject) {
	global $DB;
	$subject = $DB->get_record(BLOCK_EXACOMP_DB_SUBJECTS, array('id' => $subject->id));
	if ($subject) {
		return $DB->get_field(BLOCK_EXACOMP_DB_SCHOOLTYPES, "title", array("id" => $subject->stid));
	}

}

/**
 * Get a schooltype by subject
 *
 * @param unknown_type $subject
 */
function block_exacomp_get_schooltype_by_subject($subject) {
	global $DB;

	return $DB->get_record(BLOCK_EXACOMP_DB_SCHOOLTYPES, array("id" => $subject->stid));
}

/**
 * Gets a topic's category
 *
 * @param object $topic
 */
function block_exacomp_get_category($topic) {
	global $DB;
	if (isset($topic->catid)) {
		return $DB->get_record(BLOCK_EXACOMP_DB_CATEGORIES, array("id" => $topic->catid));
	}
}

/**
 * Gets assigned schooltypes for particular courseid
 *
 * @param int $typeid
 * @param int $courseid
 */
function block_exacomp_get_mdltypes($typeid, $courseid = 0) {
	global $DB;

	return $DB->get_record(BLOCK_EXACOMP_DB_MDLTYPES, array("stid" => $typeid, "courseid" => $courseid));
}

/**
 *
 * Assign a schooltype to a course
 * @param unknown_type $values
 * @param unknown_type $courseid
 */
function block_exacomp_set_mdltype($values, $courseid = 0) {
	global $DB;

	$DB->delete_records(BLOCK_EXACOMP_DB_MDLTYPES, array("courseid" => $courseid));
	foreach ($values as $value) {
		$DB->insert_record(BLOCK_EXACOMP_DB_MDLTYPES, array("stid" => intval($value), "courseid" => $courseid));
	}

	block_exacomp_clean_course_topics($values, $courseid);
}

/**
 * called when schooltype is changed, remove old topics
 * @param unknown $values
 * @param unknown $courseid
 */
function block_exacomp_clean_course_topics($values, $courseid) {
	global $DB;

	if ($courseid == 0) //all topics for all courses
	{
		$coutopics = $DB->get_records(BLOCK_EXACOMP_DB_COURSETOPICS);
	} else {
		$coutopics = $DB->get_records(BLOCK_EXACOMP_DB_COURSETOPICS, array('courseid' => $courseid));
	}

	foreach ($coutopics as $coutopic) {
		$sql = 'SELECT s.stid FROM {'.BLOCK_EXACOMP_DB_TOPICS.'} t
			JOIN {'.BLOCK_EXACOMP_DB_SUBJECTS.'} s ON t.subjid=s.id
			WHERE t.id=?';

		$schooltype = $DB->get_record_sql($sql, array($coutopic->topicid));

		if ($schooltype && !array_key_exists($schooltype->stid, $values)) {
			$DB->delete_records(BLOCK_EXACOMP_DB_COURSETOPICS, array('id' => $coutopic->id));
		}
	}
}

/**
 * check if configuration is already finished
 * configuration is finished if schooltype is selected for course(LIS)/moodle(normal)
 */
function block_exacomp_is_configured($courseid = 0) {
	global $DB;

	return $DB->get_records(BLOCK_EXACOMP_DB_MDLTYPES, array("courseid" => $courseid));
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

	$DB->delete_records(BLOCK_EXACOMP_DB_SETTINGS, array("courseid" => $courseid));

	if ($settings->grading > BLOCK_EXACOMP_SETTINGS_MAX_SCHEME) {
		$settings->grading = BLOCK_EXACOMP_SETTINGS_MAX_SCHEME;
	}

	//adapt old evaluation to new scheme
	//update compcompuser && compcompuser_mm && exameval
	if ($old_course_settings->grading != $settings->grading) {
		//block_exacompcompuser
		$records = $DB->get_records(BLOCK_EXACOMP_DB_COMPETENCES, array('courseid' => $courseid));
		foreach ($records as $record) {
			//if value is set and greater than zero->adapt to new scheme
			if (isset($record->value) && $record->value > 0) {
				//calculate old percentage and apply it to new scheme
				$percent_old = $record->value / $old_course_settings->grading;
				$value_new = round($settings->grading * $percent_old);

				$update = new stdClass();
				$update->id = $record->id;
				$update->value = $value_new;
				$DB->update_record(BLOCK_EXACOMP_DB_COMPETENCES, $update);

			}
		}

		//block_exacompcompuser_mm
		$records = $DB->get_records_sql('
			SELECT comp.id, comp.value
			FROM {'.BLOCK_EXACOMP_DB_COMPETENCE_USER_MM.'} comp
			JOIN {course_modules} cm ON comp.activityid=cm.id
			WHERE cm.course=?', array($courseid));

		foreach ($records as $record) {
			if (isset($record->value) && $record->value > 0) {
				//calculate old percentage and apply it to new scheme
				$percent_old = $record->value / $old_course_settings->grading;
				$value_new = round($settings->grading * $percent_old);

				$update = new stdClass();
				$update->id = $record->id;
				$update->value = $value_new;
				$DB->update_record(BLOCK_EXACOMP_DB_COMPETENCE_USER_MM, $update);
			}
		}

		//block_exacompexampeval
		$records = $DB->get_records(BLOCK_EXACOMP_DB_EXAMPLEEVAL, array('courseid' => $courseid));
		foreach ($records as $record) {
			$update = new stdClass();
			$update->id = $record->id;

			$doteacherupdate = false;
			if (isset($record->teacher_evaluation) && $record->teacher_evaluation > 0) {
				//calculate old percentage and apply it to new scheme
				$percent_old = $record->teacher_evaluation / $old_course_settings->grading;
				$teachereval_new = round($settings->grading * $percent_old);

				$update->teacher_evaluation = $teachereval_new;
				$doteacherupdate = true;
			}
			$dostudentupdate = false;
			if (isset($record->student_evaluation) && $record->student_evaluation > 0) {
				//calculate old percentage and apply it to new scheme
				$percent_old = $record->student_evaluation / $old_course_settings->grading;
				$studenteval_new = round($settings->grading * $percent_old);

				$update->student_evaluation = $studenteval_new;
				$dostudentupdate = true;
			}

			if ($dostudentupdate || $doteacherupdate) {
				$DB->update_record(BLOCK_EXACOMP_DB_EXAMPLEEVAL, $update);
			}
		}

	}

	$settings->courseid = $courseid;
	$settings->tstamp = time();

	$DB->insert_record(BLOCK_EXACOMP_DB_SETTINGS, $settings);
}

/**
 *
 * Check if there are already topics assigned to a course
 * @param int $courseid
 */
function block_exacomp_is_activated($courseid) {
	global $DB;

	return $DB->get_records(BLOCK_EXACOMP_DB_COURSETOPICS, array("courseid" => $courseid));
}

/**
 *    Check is block is ready for use.
 *    It is ready if:
 *    1. block is activated and no activites are used in the course
 *    or
 *    2. block is activated, activities are used and associated
 *
 */
function block_exacomp_is_ready_for_use($courseid) {

	global $DB;
	$course_settings = block_exacomp_get_settings_by_course($courseid);
	$is_activated = block_exacomp_is_activated($courseid);

	//no topics selected
	if (!$is_activated) {
		return false;
	}

	return true;

	//topics selected
	//no activities->finish
	if (!$course_settings->uses_activities) {
		return true;
	}

	if ($course_settings->show_all_descriptors) {
		return true;
	}

	//work with activities
	$activities_assigned_to_any_course = $DB->get_records(BLOCK_EXACOMP_DB_COMPETENCE_ACTIVITY, array('eportfolioitem' => 0));
	//no activites assigned
	if (!$activities_assigned_to_any_course) {
		return false;
	}

	//activity assigned in given course
	foreach ($activities_assigned_to_any_course as $activity) {
		$module = $DB->get_record('course_modules', array('id' => $activity->activityid));
		if (isset($module->course) && $module->course == $courseid) {
			return true;
		}
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
function block_exacomp_award_badges($courseid, $userid = null) {
	global $DB, $USER;

	$users = get_enrolled_users(context_course::instance($courseid));
	if ($userid) {
		if (!isset($users[$userid])) {
			return;
		}

		// only award for this user
		$users = array(
			$userid => $users[$userid],
		);
	}
	$badges = badges_get_badges(BADGE_TYPE_COURSE, $courseid);

	foreach ($badges as $badge) {

		// badges, which can be issued to user: status=active, type=manual
		if (!$badge->is_active() || !$badge->has_manual_award_criteria()) {
			continue;
		}


		$descriptors = $DB->get_records_sql('
				SELECT d.*
				FROM {'.BLOCK_EXACOMP_DB_DESCRIPTORS.'} d
				JOIN {'.BLOCK_EXACOMP_DB_DESCBADGE.'} db ON d.id=db.descid AND db.badgeid=?
				', array($badge->id));

		// no descriptors selected?
		if (empty($descriptors)) {
			continue;
		}
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
			if (!$allFound) {
				continue;
			}

			// has all required competencies
			$acceptedroles = array_keys($badge->criteria[BADGE_CRITERIA_TYPE_MANUAL]->params);
			if (process_manual_award($user->id, $USER->id, $acceptedroles[0], $badge->id)) {
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

	if ($userid == null) {
		$userid = $USER->id;
	}

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
		'pending' => array(),
	);

	foreach ($badges as $badge) {

		// badges, which can be issued to user: status=active, type=manual
		if (!$badge->is_active() || !$badge->has_manual_award_criteria()) {
			continue;
		}

		$descriptors = $DB->get_records_sql('
				SELECT d.*
				FROM {'.BLOCK_EXACOMP_DB_DESCRIPTORS.'} d
				JOIN {'.BLOCK_EXACOMP_DB_DESCBADGE.'} db ON d.id=db.descid AND db.badgeid=?
				', array($badge->id));

		// no descriptors selected?
		if (empty($descriptors)) {
			continue;
		}

		$badge->descriptorStatus = array();

		$user = $DB->get_record('user', array('id' => $userid));
		$usercompetences_all = block_exacomp_get_user_competences_by_course($user, $courseid);
		$usercomptences = $usercompetences_all->competencies->teacher;
		//$usercompetences = block_exacomp_get_usercompetences($userid, $role=1, $courseid);

		foreach ($descriptors as $descriptor) {
			if (isset($usercompetences[$descriptor->id])) {
				$badge->descriptorStatus[] = html_writer::empty_tag('img', array('src' => new moodle_url('/blocks/exacomp/pix/accept.png'), 'style' => 'vertical-align:text-bottom;')).$descriptor->title;
			} else {
				$badge->descriptorStatus[] = html_writer::empty_tag('img', array('src' => new moodle_url('/blocks/exacomp/pix/cancel.png'), 'style' => 'vertical-align:text-bottom;')).$descriptor->title;
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
function block_exacomp_get_badge_descriptors($badgeid) {
	global $DB;

	return $DB->get_records_sql('
			SELECT d.*
			FROM {'.BLOCK_EXACOMP_DB_DESCRIPTORS.'} d
			JOIN {'.BLOCK_EXACOMP_DB_DESCBADGE.'} db ON d.id=db.descid AND db.badgeid=?
			', array($badgeid));
}

/**
 *
 * Gets supported modules for assigning activities
 * -- not needed any more: requirement all modules are supported
 */
/*function block_exacomp_get_supported_modules() {
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
}*/
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
	if (block_exacomp_get_settings_by_course($courseid)->uses_activities == 0) {
		return null;
	}

	global $DB;
	$records = $DB->get_records_sql('
			SELECT mm.id, compid, comptype, activityid
			FROM {'.BLOCK_EXACOMP_DB_COMPETENCE_ACTIVITY.'} mm
			JOIN {course_modules} m ON m.id = mm.activityid
			WHERE m.course = ? AND mm.eportfolioitem = 0
			ORDER BY comptype, compid', array($courseid));

	$mm = new stdClass();
	$mm->competencies = array();
	$mm->topics = array();

	foreach ($records as $record) {
		if ($record->comptype == BLOCK_EXACOMP_TYPE_DESCRIPTOR) {
			$mm->competencies[$record->compid][$record->activityid] = $record->activityid;
		} else {
			$mm->topics[$record->compid][$record->activityid] = $record->activityid;
		}
	}

	return $mm;
}

function block_exacomp_get_allowed_course_modules_for_course($courseid) {
	return \Super\Cache::staticCallback(__FUNCTION__, function($courseid) {
		// TODO: optimieren
		$modinfo = get_fast_modinfo($courseid);
		$modules = $modinfo->get_cms();

		$active_modules = [];
		foreach ($modules as $mod) {
			$module = block_exacomp_get_coursemodule($mod);

			//Skip Nachrichtenforum
			if (strcmp($module->name, block_exacomp_get_string('namenews', 'mod_forum')) == 0) {
				continue;
			}

			//skip News forum in any language, supported_modules[1] == forum
			$forum = g::$DB->get_record('modules', array('name' => 'forum'));
			if ($module->module == $forum->id) {
				$forum = g::$DB->get_record('forum', array('id' => $module->instance));
				if (strcmp($forum->type, 'news') == 0) {
					continue;
				}
			}

			$active_modules[$module->id] = $module;
		}

		return $active_modules;
	}, func_get_args());
}

function block_exacomp_get_allowed_course_modules_for_course_for_select($courseid) {
	$ids = array_keys(block_exacomp_get_allowed_course_modules_for_course($courseid));
	if (!$ids) {
		// always return at least one
		return '-9999';
	} else {
		return join(',', $ids);
	}
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

function block_exacomp_get_eportfolioitem_association($students) {
	global $DB, $COURSE, $USER;
	//$teachers = block_exacomp_get_teachers_by_course($COURSE->id);
	$result = array();
	foreach ($students as $student) {
		$eportfolioitems = $DB->get_records_sql('
			SELECT mm.id, compid, activityid, i.shareall, i.externaccess, i.name
			FROM {'.BLOCK_EXACOMP_DB_COMPETENCE_ACTIVITY.'} mm
			JOIN {block_exaportitem} i ON mm.activityid=i.id
			WHERE mm.eportfolioitem = 1 AND i.userid=?
			ORDER BY compid', array($student->id));

		$result[$student->id] = new stdClass();
		$result[$student->id]->competencies = array();

		foreach ($eportfolioitems as $item) {
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

			foreach ($shared_info as $info) {
				if ((isset($info->shareall) && $info->shareall > 0)) {
					$shared = true;
					$useextern = false;
					$hash = $info->hash;
					$viewid = $info->id;
					$owner = $info->owner;
					continue;
				}
				if (isset($info->externaccess) && $info->externaccess > 0) {
					$shared = true;
					$useextern = true;
					$hash = $info->hash;
					$viewid = $info->id;
					$owner = $info->owner;
					continue;
				}

			}
			if (!$shared) {
				foreach ($shared_info as $info) {
					if (isset($info->userid) && $USER->id == $info->userid) {
						$shared = true;
						$useextern = false;
						$hash = $info->hash;
						$viewid = $info->id;
						$owner = $info->owner;
						continue;
					}
				}
			}
			if (!isset($result[$student->id]->competencies[$item->compid])) {
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
 * @param array $associated_modules
 * @param stdClass $student
 *
 * @return stdClass $icon
 */
function block_exacomp_get_icon_for_user($associated_modules, $student) {
	global $CFG, $DB;
	require_once $CFG->libdir.'/gradelib.php';

	$found = false;

	$icon = new stdClass();
	$icon->text = block_exacomp_get_string('associated_activities', null, fullname($student));
	$icon->text .= '<div>';

	usort($associated_modules, function($a, $b) {
		return strcmp($a->get_formatted_name(), $b->get_formatted_name());
	});

	foreach ($associated_modules as $cm) {
		/* @var cm_info $cm */
		$hasSubmission = false;

		$gradeinfo = grade_get_grades($cm->course, "mod", $cm->modname, $cm->instance, $student->id);

		//check for assign
		$assign = $DB->get_record('modules', array('name' => 'assign'));
		if ($cm->module == $assign->id) {
			$hasSubmission = $DB->get_record('assign_submission', array('assignment' => $cm->instance, 'userid' => $student->id));
		}

		$icon->text .= '<div>';

		if (isset($gradeinfo->items[0]->grades[$student->id]->dategraded) || $hasSubmission) {
			$found = true;
			$icon->text .= html_writer::empty_tag("img", array("src" => "pix/list_12x11.png"));
		} else {
			$icon->text .= html_writer::empty_tag("img", array("src" => "pix/x_11x11.png"));
		}

		$icon->text .= ' ';

		$entry = s($cm->get_formatted_name());
		if (isset($gradeinfo->items[0]->grades[$student->id])) {
			$entry .= ', '.block_exacomp_get_string('grading').': '.$gradeinfo->items[0]->grades[$student->id]->str_long_grade;
		}

		if ($cm->modname == 'quiz' && block_exacomp_is_teacher()) {
			$url = new moodle_url('/mod/quiz/report.php?id='.$cm->id.'&mode=overview');
		} else {
			$url = new moodle_url('/mod/'.$cm->modname.'/view.php?id='.$cm->id);
		}
		$icon->text .= '<a href="'.$url.'">'.$entry.'</a>';

		$icon->text .= '</div>';
	}

	if ($found) {
		$icon->img = html_writer::empty_tag("img", array("src" => "pix/list_12x11.png", "alt" => block_exacomp_get_string("legend_activities")));
	} else {
		$icon->img = html_writer::empty_tag("img", array("src" => "pix/x_11x11.png", "alt" => block_exacomp_get_string("usernosubmission", null, fullname($student))));
	}

	return $icon;
}

function block_exacomp_normalize_course_visibilities($courseid) {
	$topicids = g::$DB->get_records_menu(BLOCK_EXACOMP_DB_COURSETOPICS, array("courseid" => $courseid), null, 'topicid, topicid AS tmp');

	$descriptors = array();
	$examples = array();

	foreach ($topicids as $topicid) {
		//insert descriptors in block_exacompdescrvisibility
		$descriptors_topic = block_exacomp_get_descriptors_by_topic($courseid, $topicid, true);
		foreach ($descriptors_topic as $descriptor) {
			$descriptors[$descriptor->id] = $descriptor;
		}
	}

	block_exacomp_update_descriptor_visibilities($courseid, $descriptors);

	foreach ($descriptors as $descriptor) {
		$descriptor = block_exacomp_get_examples_for_descriptor($descriptor, array(BLOCK_EXACOMP_SHOW_ALL_TAXONOMIES), true, $courseid, false);
		foreach ($descriptor->examples as $example) {
			$examples[$example->id] = $example;
		}

		$descriptor->children = block_exacomp_get_child_descriptors($descriptor, $courseid);
		foreach ($descriptor->children as $child) {
			$child = block_exacomp_get_examples_for_descriptor($child, array(BLOCK_EXACOMP_SHOW_ALL_TAXONOMIES), true, $courseid, false);
			foreach ($child->examples as $example) {
				$examples[$example->id] = $example;
			}
		}
	}

	block_exacomp_update_example_visibilities($courseid, $examples);

	//delete unconnected examples
	//add blocking events to examples which are not deleted
	$blocking_events = g::$DB->get_records(BLOCK_EXACOMP_DB_EXAMPLES, array('blocking_event' => 1));

	foreach ($blocking_events as $event) {
		$examples[$event->id] = $event;
	}

	$where = $examples ? join(',', array_keys($examples)) : '-1';
	g::$DB->execute("DELETE FROM {".BLOCK_EXACOMP_DB_SCHEDULE."} WHERE courseid = ? AND exampleid NOT IN($where)", array($courseid));
}

/**
 *
 * Assign topics to course
 * @param unknown_type $courseid
 * @param unknown_type $values
 */
function block_exacomp_set_coursetopics($courseid, $topicids) {
	global $DB;

	$DB->delete_records(BLOCK_EXACOMP_DB_COURSETOPICS, array("courseid" => $courseid));

	block_exacomp_update_topic_visibilities($courseid, $topicids);

	foreach ($topicids as $topicid) {
		$DB->insert_record(BLOCK_EXACOMP_DB_COURSETOPICS, array("courseid" => $courseid, "topicid" => $topicid));
	}

	block_exacomp_normalize_course_visibilities($courseid);
}

/**
 *
 * given descriptor list is visible in cour
 * @param unknown_type $courseid
 * @param unknown_type $descriptors
 */
function block_exacomp_update_descriptor_visibilities($courseid, $descriptors) {
	global $DB;

	$visibilities = $DB->get_fieldset_select(BLOCK_EXACOMP_DB_DESCVISIBILITY, 'descrid', 'courseid=? AND studentid=0', array($courseid));

	//get all cross subject descriptors - to support cross-course subjects descriptor visibility must be kept
	$cross_subjects = $DB->get_records(BLOCK_EXACOMP_DB_CROSSSUBJECTS, array('courseid' => $courseid));
	$cross_subjects_descriptors = array();

	foreach ($cross_subjects as $crosssub) {
		$cross_subject_descriptors = $DB->get_fieldset_select(BLOCK_EXACOMP_DB_DESCCROSS, 'descrid', 'crosssubjid=?', array($crosssub->id));
		foreach ($cross_subject_descriptors as $descriptor) {
			if (!in_array($descriptor, $cross_subjects_descriptors)) {
				$cross_subjects_descriptors[] = $descriptor;
			}
		}
	}

	$finaldescriptors = $descriptors;
	//manage visibility, do not delete user visibility, but delete unused entries
	foreach ($descriptors as $descriptor) {
		//new descriptors in table
		if (!in_array($descriptor->id, $visibilities)) {
			$visibilities[] = $descriptor->id;
			$DB->insert_record(BLOCK_EXACOMP_DB_DESCVISIBILITY, array("courseid" => $courseid, "descrid" => $descriptor->id, "studentid" => 0, "visible" => 1));
		}

		$descriptor->children = block_exacomp_get_child_descriptors($descriptor, $courseid, true, array(BLOCK_EXACOMP_SHOW_ALL_TAXONOMIES), true, false);

		foreach ($descriptor->children as $childdescriptor) {
			if (!in_array($childdescriptor->id, $visibilities)) {
				$visibilities[] = $childdescriptor->id;
				$DB->insert_record(BLOCK_EXACOMP_DB_DESCVISIBILITY, array("courseid" => $courseid, "descrid" => $childdescriptor->id, "studentid" => 0, "visible" => 1));
			}

			if (!array_key_exists($childdescriptor->id, $finaldescriptors)) {
				$finaldescriptors[$childdescriptor->id] = $childdescriptor;
			}
		}
	}

	foreach ($visibilities as $visible) {
		//delete ununsed descriptors for course and for special students
		if (!array_key_exists($visible, $finaldescriptors)) {
			//check if used in cross-subjects --> then it must still be visible
			if (!in_array($visible, $cross_subjects_descriptors)) {
				$DB->delete_records(BLOCK_EXACOMP_DB_DESCVISIBILITY, array("courseid" => $courseid, "descrid" => $visible));
			}
		}
	}
}

/**
 *
 * given example list is visible in cour
 * @param unknown_type $courseid
 * @param unknown_type $descriptors
 */
function block_exacomp_update_example_visibilities($courseid, $examples) {
	global $DB;

	$visibilities = $DB->get_fieldset_select(BLOCK_EXACOMP_DB_EXAMPVISIBILITY, 'exampleid', 'courseid=? AND studentid=0', array($courseid));

	//get all cross subject examples - to support cross-course subjects exampels visibility must be kept
	$cross_subjects = $DB->get_records(BLOCK_EXACOMP_DB_CROSSSUBJECTS, array('courseid' => $courseid));
	$cross_subject_examples = array();

	foreach ($cross_subjects as $crosssub) {
		$cross_subject_descriptors = $DB->get_fieldset_select(BLOCK_EXACOMP_DB_DESCCROSS, 'descrid', 'crosssubjid=?', array($crosssub->id));
		foreach ($cross_subject_descriptors as $descriptor) {
			$descriptor = $DB->get_record(BLOCK_EXACOMP_DB_DESCRIPTORS, array('id' => $descriptor));
			$descriptor = block_exacomp_get_examples_for_descriptor($descriptor, array(BLOCK_EXACOMP_SHOW_ALL_TAXONOMIES), true, $courseid, false);
			foreach ($descriptor->examples as $example) {
				if (!in_array($example->id, $cross_subject_examples)) {
					$cross_subject_examples[] = $example->id;
				}
			}

			if ($descriptor->parentid == 0) {
				$descriptor_topic_mm = $DB->get_record(BLOCK_EXACOMP_DB_DESCTOPICS, array('descrid' => $descriptor->id));
				if ($descriptor_topic_mm) {
					$descriptor->topicid = $descriptor_topic_mm->topicid;
					$descriptor->children = block_exacomp_get_child_descriptors($descriptor, $courseid);
					foreach ($descriptor->children as $child) {
						$child = block_exacomp_get_examples_for_descriptor($child, array(BLOCK_EXACOMP_SHOW_ALL_TAXONOMIES), true, $courseid, false);
						foreach ($child->examples as $example) {
							if (!in_array($example->id, $cross_subject_examples)) {
								$cross_subject_examples[] = $example->id;
							}
						}
					}
				}
			}
		}
	}

	$finalexamples = $examples;
	//manage visibility, do not delete user visibility, but delete unused entries
	foreach ($examples as $example) {
		//new example in table
		if (!in_array($example->id, $visibilities)) {
			$visibilities[] = $example->id;
			$DB->insert_record(BLOCK_EXACOMP_DB_EXAMPVISIBILITY, array("courseid" => $courseid, "exampleid" => $example->id, "studentid" => 0, "visible" => 1));
			$DB->insert_record(BLOCK_EXACOMP_DB_SOLUTIONVISIBILITY, array("courseid" => $courseid, "exampleid" => $example->id, "studentid" => 0, "visible" => 1));
		}
	}

	foreach ($visibilities as $visible) {
		//delete ununsed descriptors for course and for special students
		if (!array_key_exists($visible, $finalexamples)) {
			//check if used in cross-subjects --> then it must still be visible
			if (!in_array($visible, $cross_subject_examples)) {
				$DB->delete_records(BLOCK_EXACOMP_DB_EXAMPVISIBILITY, array("courseid" => $courseid, "exampleid" => $visible));
				$DB->delete_records(BLOCK_EXACOMP_DB_SOLUTIONVISIBILITY, array("courseid" => $courseid, "exampleid" => $visible));
			}
		}
	}
}

/**
 * given list is visible in course - make sure for each visible topic one entry in visibility table exists
 * (studentid = 0 and courseid = courseid)
 * @param unknown $courseid
 * @param unknown $topicids
 */
function block_exacomp_update_topic_visibilities($courseid, $topicids) {
	global $DB;

	$visibilities = $DB->get_fieldset_select(BLOCK_EXACOMP_DB_TOPICVISIBILITY, 'topicid', 'courseid=? AND studentid=0', array($courseid));

	//manage visibility, do not delete user visibility, but delete unused entries
	foreach ($topicids as $topicid) {
		//new descriptors in table
		if (!in_array($topicid, $visibilities)) {
			$visibilities[] = $topicid;
			$DB->insert_record(BLOCK_EXACOMP_DB_TOPICVISIBILITY, array("courseid" => $courseid, "topicid" => $topicid, "studentid" => 0, "visible" => 1));
		}
	}

	foreach ($visibilities as $visible) {
		//delete ununsed descriptors for course and for special students
		if (!in_array($visible, $topicids)) {
			//check if used in cross-subjects --> then it must still be visible
			$DB->delete_records(BLOCK_EXACOMP_DB_TOPICVISIBILITY, array("courseid" => $courseid, "topicid" => $visible));
		}
	}

}

/**
 *
 * Returns quizes assigned to course
 * @param unknown_type $courseid
 */
function block_exacomp_get_active_tests_by_course($courseid) {
	global $DB;

	$sql = "SELECT DISTINCT cm.instance as id, cm.id as activityid, q.grade FROM {block_exacompcompactiv_mm} activ "
		."JOIN {course_modules} cm ON cm.id = activ.activityid "
		."JOIN {modules} m ON m.id = cm.module "
		."JOIN {quiz} q ON cm.instance = q.id "
		."WHERE m.name='quiz' AND cm.course=?";

	$tests = $DB->get_records_sql($sql, array($courseid));

	foreach ($tests as $test) {
		$test->descriptors = $DB->get_records(BLOCK_EXACOMP_DB_COMPETENCE_ACTIVITY, array('activityid' => $test->activityid, 'comptype' => BLOCK_EXACOMP_TYPE_DESCRIPTOR), null, 'compid');
		$test->topics = $DB->get_records(BLOCK_EXACOMP_DB_COMPETENCE_ACTIVITY, array('activityid' => $test->activityid, 'comptype' => BLOCK_EXACOMP_TYPE_TOPIC), null, 'compid');
	}

	return $tests;
}

/**
 *
 * Returns all course ids where an instance of Exabis Competences is installed
 */
function block_exacomp_get_courseids() {
	$instances = g::$DB->get_records('block_instances', array('blockname' => 'exacomp'));

	$exabis_competences_courses = array();

	foreach ($instances as $instance) {
		$context = g::$DB->get_record('context', array('id' => $instance->parentcontextid, 'contextlevel' => CONTEXT_COURSE));
		if ($context) {
			$exabis_competences_courses[$context->instanceid] = $context->instanceid;
		}
	}

	return $exabis_competences_courses;
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
		if (is_enrolled($context, $userid, '', true) && has_capability('block/exacomp:use', $context, $userid)) {
			$user_courses[$course] = g::$DB->get_record('course', array('id' => $course));
		}
	}

	return $user_courses;
}

/**
 * get all courses where a given user is enrolled as teacher
 * @param unknown_type $userid
 */
function block_exacomp_get_teacher_courses($userid) {
	$courses = block_exacomp_get_exacomp_courses($userid);
	foreach ($courses as $key => $course) {
		if (!block_exacomp_is_teacher(context_course::instance($course->id))) {
			// unset($courses[$key]);
		}
	}

	return $courses;
}

/**
 *
 * Gets URL for particular activity
 * @param unknown_type $activity
 * @param unknown_type $student
 */
function block_exacomp_get_activityurl($activity, $student = false) {
	global $DB;

	$mod = $DB->get_record('modules', array("id" => $activity->module));

	if ($mod->name == "assignment" && !$student) {
		return new moodle_url('/mod/assignment/submissions.php', array('id' => $activity->id));
	} else {
		return new moodle_url('/mod/'.$mod->name.'/view.php', array('id' => $activity->id));
	}
}

/**
 *
 * Gets course module name for module
 * @param unknown_type $mod
 */
function block_exacomp_get_coursemodule($mod) {
	global $DB;
	$name = $DB->get_field('modules', 'name', array("id" => $mod->module));

	return get_coursemodule_from_id($name, $mod->id);
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
	if ($data != null) {
		foreach ($data as $cmoduleKey => $comps) {
			if (!empty($cmoduleKey)) {
				foreach ($comps as $compidKey => $empty) {
					//set activity
					block_exacomp_set_compactivity($cmoduleKey, $compidKey, $comptype);
				}
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

	$cmmod = $DB->get_record('course_modules', array("id" => $activityid));
	$modulename = $DB->get_record('modules', array("id" => $cmmod->module));
	$instance = get_coursemodule_from_id($modulename->name, $activityid);

	$DB->delete_records(BLOCK_EXACOMP_DB_COMPETENCE_ACTIVITY, array("activityid" => $activityid, "compid" => $compid, "comptype" => $comptype, "eportfolioitem" => 0));
	$DB->insert_record(BLOCK_EXACOMP_DB_COMPETENCE_ACTIVITY, array("activityid" => $activityid, "compid" => $compid, "comptype" => $comptype, "coursetitle" => $COURSE->shortname, 'activitytitle' => $instance->name));
}

/**
 *
 * Delete competence, activity associations
 */
function block_exacomp_delete_competences_activities() {
	global $COURSE, $DB;

	$cmodules = $DB->get_records('course_modules', array('course' => $COURSE->id));

	foreach ($cmodules as $cm) {
		$DB->delete_records(BLOCK_EXACOMP_DB_COMPETENCE_ACTIVITY, array('activityid' => $cm->id, 'eportfolioitem' => 0));
	}
}

/**
 * Get activity for particular competence
 * @param unknown_type $descid
 * @param unknown_type $courseid
 * @param unknown_type $descriptorassociation
 */
function block_exacomp_get_activities($compid, $courseid = null, $comptype = BLOCK_EXACOMP_TYPE_DESCRIPTOR) { //alle assignments die einem bestimmten descriptor zugeordnet sind
	global $CFG, $DB;
	$query = 'SELECT mm.id as uniqueid,a.id,ass.grade,a.instance
	FROM {'.BLOCK_EXACOMP_DB_COMPETENCE_ACTIVITY.'} mm
	INNER JOIN {course_modules} a ON a.id=mm.activityid
	LEFT JOIN {assign} ass ON ass.id=a.instance
	WHERE mm.compid=? AND mm.comptype = ?';

	$condition = array($compid, $comptype);

	if ($courseid) {
		$query .= " AND a.course=?";
		$condition = array($compid, $comptype, $courseid);
	}

	$activities = $DB->get_records_sql($query, $condition);
	if (!$activities) {
		$activities = array();
	}

	return $activities;
}

/**
 * Get activities avaiable in current course
 * @param unknown $courseid
 */
function block_exacomp_get_activities_by_course($courseid) {
	global $DB;
	$query = 'SELECT DISTINCT mm.activityid as id, mm.activitytitle as title FROM {'.BLOCK_EXACOMP_DB_COMPETENCE_ACTIVITY.'} mm
		INNER JOIN {course_modules} a ON a.id=mm.activityid
		WHERE a.course = ? AND mm.eportfolioitem=0';

	return $DB->get_records_sql($query, array($courseid));
}

/**
 * init data for competencegrid, shown in tab "Reports" or "Berichte"
 * @param unknown $courseid
 * @param unknown $subjectid
 * @param unknown $studentid
 * @param string $showallexamples
 * @param array $filteredtaxonomies
 * @return unknown[]|NULL[][]
 */
/*function block_exacomp_init_competence_grid_data($courseid, $subjectid, $studentid, $showallexamples = false, $filteredtaxonomies = array(SHOW_ALL_TAXONOMIES)) {
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
			$competencies = array("studentcomps"=>$DB->get_records(BLOCK_EXACOMP_DB_COMPETENCES,array("role"=>BLOCK_EXACOMP_ROLE_STUDENT,"courseid"=>$courseid,"userid"=>$studentid,"comptype"=>BLOCK_EXACOMP_TYPE_DESCRIPTOR),"","compid,userid,reviewerid,value"),
					"teachercomps"=>$DB->get_records(BLOCK_EXACOMP_DB_COMPETENCES,array("role"=>BLOCK_EXACOMP_ROLE_TEACHER,"courseid"=>$courseid,"userid"=>$studentid,"comptype"=>BLOCK_EXACOMP_TYPE_DESCRIPTOR),"","compid,userid,reviewerid,value,evalniveauid"));

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
					FROM {" . BLOCK_EXACOMP_DB_EXAMPLES . "} e
					JOIN {" . BLOCK_EXACOMP_DB_DESCEXAMP . "} de ON e.id=de.exampid
					WHERE de.descrid=?"
					. ($showallexamples ? "" : " AND e.creatorid > 0")
					, array($descriptor->id));

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

		$selection = $DB->get_records(BLOCK_EXACOMP_DB_COURSETOPICS,array('courseid'=>$courseid),'','topicid');

		return array($niveaus, $skills, $topics, $data, $selection);

}*/

/**
 * return all avaiable niveaus within one subject (LFS for LIS)
 * @param unknown $subjectid
 */
function block_exacomp_get_niveaus_for_subject($subjectid) {
	global $DB;
	//sql could be optimized
	$niveaus = "SELECT DISTINCT n.id as id, n.title, n.sorting 
			FROM {".BLOCK_EXACOMP_DB_DESCRIPTORS."} d, {".BLOCK_EXACOMP_DB_DESCTOPICS."} dt, {".BLOCK_EXACOMP_DB_NIVEAUS."} n
			WHERE d.id=dt.descrid AND dt.topicid IN 
				(SELECT id FROM {".BLOCK_EXACOMP_DB_TOPICS."} WHERE subjid=?)
			AND d.niveauid > 0 AND d.niveauid = n.id order by n.sorting, n.id";

	return $DB->get_records_sql_menu($niveaus, array($subjectid));
}

/**
 * get all avaiable niveaus within full competence tree
 * @param unknown $subject_tree
 */
function block_exacomp_extract_niveaus($subject_tree) {
	global $DB;
	$niveaus = array();

	foreach ($subject_tree as $subject) {
		foreach ($subject->topics as $topic) {
			if (isset($topic->descriptors)) {
				foreach ($topic->descriptors as $descriptor) {
					if ($descriptor->niveauid > 0) {
						if (!isset($niveaus[$descriptor->niveauid])) {
							$niveaus[$descriptor->niveauid] = $DB->get_record(BLOCK_EXACOMP_DB_NIVEAUS, array('id' => $descriptor->niveauid));
						}
					}
				}
			}
		}
	}

	return $niveaus;
}

/**
 *
 * Unsets every subject, topic, descriptor where descriptor niveauid is filtered
 * @param unknown_type $tree
 * @param unknown_type $niveaus
 */
function block_exacomp_filter_niveaus(&$tree, $niveaus) {
	if (!empty($niveaus) && !in_array(0, $niveaus)) {
		//go through tree and unset every subject, topic and descriptor where niveau is not in selected niveaus
		foreach ($tree as $subject) {
			//traverse recursively, because of possible topic-children
			$subject_has_niveaus = block_exacomp_filter_niveaus_topics($subject->topics, $niveaus);

			if (!$subject_has_niveaus) {
				unset($tree[$subject->id]);
			}
		}
	}
}

/**
 * helper function to traverse through tree recursively, because of endless topic children
 * and unset every node where descriptor doesn't fit to niveaus
 */
function block_exacomp_filter_niveaus_topics($subs, $niveaus) {
	$sub_has_niveaus = false;
	// $sub_topics_have_niveaus = false;
	foreach ($subs as $topic) {
		$topic_has_niveaus = false;
		if (isset($topic->descriptors)) {
			foreach ($topic->descriptors as $descriptor) {
				if (!in_array($descriptor->niveauid, $niveaus)) {
					unset($topic->descriptors[$descriptor->id]);
				} else {
					$sub_has_niveaus = true;
					$topic_has_niveaus = true;
				}
			}
		}

		if (!$topic_has_niveaus) {
			unset($subs[$topic->id]);
		}
	}

	return $sub_has_niveaus;
}

/**
 * delete data ouf of stated database tables
 */
function block_exacomp_truncate_all_data() {
	global $DB;

	$sql = "TRUNCATE {block_exacompcategories}";
	$DB->execute($sql);
	$sql = "TRUNCATE {block_exacompcompactiv_mm}";
	$DB->execute($sql);
	$sql = "TRUNCATE {block_exacompcompuser}";
	$DB->execute($sql);
	$sql = "TRUNCATE {block_exacompcompuser_mm}";
	$DB->execute($sql);
	$sql = "TRUNCATE {block_exacompcoutopi_mm}";
	$DB->execute($sql);
	$sql = "TRUNCATE {block_exacompdescbadge_mm}";
	$DB->execute($sql);
	$sql = "TRUNCATE {block_exacompdescrexamp_mm}";
	$DB->execute($sql);
	$sql = "TRUNCATE {block_exacompdescriptors}";
	$DB->execute($sql);
	$sql = "TRUNCATE {block_exacompdescrtopic_mm}";
	$DB->execute($sql);
	$sql = "TRUNCATE {block_exacompedulevels}";
	$DB->execute($sql);
	$sql = "TRUNCATE {block_exacompexameval}";
	$DB->execute($sql);
	$sql = "TRUNCATE {block_exacompexamples}";
	$DB->execute($sql);
	$sql = "TRUNCATE {block_exacompmdltype_mm}";
	$DB->execute($sql);
	$sql = "TRUNCATE {block_exacompniveaus}";
	$DB->execute($sql);
	$sql = "TRUNCATE {block_exacompprofilesettings}";
	$DB->execute($sql);
	$sql = "TRUNCATE {block_exacompschooltypes}";
	$DB->execute($sql);
	$sql = "TRUNCATE {block_exacompsettings}";
	$DB->execute($sql);
	$sql = "TRUNCATE {block_exacompskills}";
	$DB->execute($sql);
	$sql = "TRUNCATE {block_exacompsubjects}";
	$DB->execute($sql);
	$sql = "TRUNCATE {block_exacomptaxonomies}";
	$DB->execute($sql);
	$sql = "TRUNCATE {block_exacomptopics}";
	$DB->execute($sql);
	$sql = "TRUNCATE {block_exacompdatasources}";
	$DB->execute($sql);

	// TODO: tabellen block_exacompdescrvisibility, block_exacompitemexample, block_exacompschedule gehören auch gelöscht?
}

/**
 * checks is block Exabis Eportfolio is installed
 */
function block_exacomp_exaportexists() {
	global $DB;

	return !!$DB->get_record('block', array('name' => 'exaport'));
}

/**
 * checks if block Exabis Studentreview is installed
 * @return boolean
 */
function block_exacomp_is_exastud_installed() {
	return class_exists('\block_exastud\api') && \block_exastud\api::active();
}

function block_exacomp_get_exastud_periods_current_and_past_periods() {
	if (!block_exacomp_is_exastud_installed()) {
		return [];
	}

	if (!method_exists('block_exastud\api', 'get_periods')) {
		return [];
	}

	$periods = \block_exastud\api::get_periods();
	// filter only current/past
	$periods = array_filter($periods, function($p) {
		return $p->starttime < time();
	});

	return $periods;
}

/**
 * get settings for a user profile
 * @param number $userid
 * @return \block_exacomp\stdClass
 */
function block_exacomp_get_profile_settings($userid = 0) {
	global $USER, $DB;

	if ($userid == 0) {
		$userid = $USER->id;
	}

	$profile_settings = new stdClass();

	$profile_settings->exacomp = array();
	$exacomp_settings = $DB->get_records(BLOCK_EXACOMP_DB_PROFILESETTINGS, array('block' => 'exacomp', 'userid' => $userid));
	foreach ($exacomp_settings as $setting) {
		$profile_settings->exacomp[$setting->itemid] = $setting;
	}

	return $profile_settings;
}

/**
 * reset profile settings
 * @param unknown $userid
 */
function block_exacomp_reset_profile_settings($userid) {
	global $DB;
	$DB->delete_records(BLOCK_EXACOMP_DB_PROFILESETTINGS, array('userid' => $userid));
}

/**
 * set profile settings
 * at the moment only courses to be shown in profile can be stored
 * @param unknown $userid
 * @param unknown $courses
 */
function block_exacomp_set_profile_settings($userid, $courses) {
	global $DB;

	block_exacomp_reset_profile_settings($userid);

	//save courses
	foreach ($courses as $course) {
		$insert = new stdClass();
		$insert->block = 'exacomp';
		$insert->itemid = $course;
		$insert->feedback = '';
		$insert->userid = $userid;

		$DB->insert_record(BLOCK_EXACOMP_DB_PROFILESETTINGS, $insert);
	}
}

/**
 * initialize user profile settings
 * @param unknown $courses
 * @param unknown $userid
 */
function block_exacomp_init_profile($courses, $userid) {
	global $DB;

	$record = $DB->get_records(BLOCK_EXACOMP_DB_PROFILESETTINGS, array('userid' => $userid));
	if (!$record) {
		block_exacomp_reset_profile_settings($userid);
		foreach ($courses as $course) {
			$insert = new stdClass();
			$insert->block = 'exacomp';
			$insert->itemid = $course->id;
			$insert->feedback = '';
			$insert->userid = $userid;

			$DB->insert_record(BLOCK_EXACOMP_DB_PROFILESETTINGS, $insert);
		}
	}
}

/**
 * create tipp for competence overview
 * @param unknown $compid
 * @param unknown $user
 * @param unknown $type
 * @param unknown $scheme
 * @return boolean
 */
function block_exacomp_set_tipp($compid, $user, $type, $scheme) {
	//$user_information = block_exacomp_get_user_information_by_course($user, $COURSE->id);

	$show_tipp = false;
	foreach ($user->{$type}->activities as $activity) {
		if (isset($activity->teacher[$compid]) && $activity->teacher[$compid] >= ceil($scheme / 2)) {
			$show_tipp = true;
		}
	}

	return $show_tipp;
}

/**
 * return string for tipp in competence overview
 * @param unknown $compid
 * @param unknown $user
 * @param unknown $scheme
 * @param unknown $type
 * @param unknown $comptype
 * @return string
 */
function block_exacomp_get_tipp_string($compid, $user, $scheme, $type, $comptype) {
	global $COURSE;
	$activities = block_exacomp_get_activities($compid, $COURSE->id, $comptype);
	$user_information = block_exacomp_get_user_information_by_course($user, $COURSE->id);

	$gained = 0;
	$total = count($activities);

	foreach ($activities as $activity) {
		if (isset($user_information->{$type}->activities[$activity->id]->teacher[$compid])
			&& $user_information->{$type}->activities[$activity->id]->teacher[$compid] >= ceil($scheme / 2)
		) {
			$gained++;
		}
	}

	return block_exacomp_get_string('teacher_tipp_1').$total.block_exacomp_get_string('teacher_tipp_2').$gained.block_exacomp_get_string('teacher_tipp_3');
}

/**
 *
 * Gets tree with schooltype on highest level
 * @param unknown_type $courseid
 */
function block_exacomp_build_schooltype_tree_for_courseselection($limit_courseid) {
	$schooltypes = block_exacomp_get_schooltypes_by_course($limit_courseid);

	foreach ($schooltypes as $schooltype) {
		$schooltype->subjects = block_exacomp_get_subjects_for_schooltype($limit_courseid, $schooltype->id);
	}

	return $schooltypes;
}

/**
 * This function is used for ePop, to test for the latest db update.
 * It is used after every xml import and every example upload.
 */
function block_exacomp_settstamp() {
	global $DB;

	$modsetting = $DB->get_record('block_exacompsettings', array('courseid' => 0, 'activities' => 'importxml'));
	if ($modsetting) {
		$modsetting->tstamp = time();
		$DB->update_record('block_exacompsettings', $modsetting);
	} else {
		$DB->insert_record('block_exacompsettings', array("courseid" => 0, "grading" => "0", "activities" => "importxml", "tstamp" => time()));
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

	if (!$autotest) {
		return;
	}

	//for all courses where exacomp is used
	$courses = block_exacomp_get_courseids();

	foreach ($courses as $courseid) {
		//tests associated with competences
		//get all tests that are associated with competences
		$tests = block_exacomp_get_active_tests_by_course($courseid);
		$students = block_exacomp_get_students_by_course($courseid);

		$grading_scheme = block_exacomp_get_grading_scheme($courseid);

		//get student grading for each test
		foreach ($students as $student) {
			foreach ($tests as $test) {
				//get grading for each test and assign topics and descriptors
				$quiz = $DB->get_record('quiz_grades', array('quiz' => $test->id, 'userid' => $student->id));
				$quiz_assignment = $DB->get_record(BLOCK_EXACOMP_DB_AUTOTESTASSIGN, array('quiz' => $test->id, 'userid' => $student->id));

				// assign competencies if test is successfully completed AND test grade update since last auto assign
				if (isset($quiz->grade) && (floatval($test->grade) * (floatval($testlimit) / 100)) <= $quiz->grade && (!$quiz_assignment || $quiz_assignment->timemodified < $quiz->timemodified)) {
					//assign competences to student
					if (isset($test->descriptors)) {
						foreach ($test->descriptors as $descriptor) {
							if (block_exacomp_additional_grading()) {
								block_exacomp_save_additional_grading_for_comp($courseid, $descriptor->compid, $student->id,
									\block_exacomp\global_config::get_value_additionalinfo_mapping($grading_scheme), $comptype = 0);
							}

							block_exacomp_set_user_competence($student->id, $descriptor->compid,
								0, $courseid, BLOCK_EXACOMP_ROLE_TEACHER, $grading_scheme);
							mtrace("set competence ".$descriptor->compid." for user ".$student->id.'<br>');
						}
					}
					if (isset($test->topics)) {
						foreach ($test->topics as $topic) {
							if (block_exacomp_additional_grading() && block_exacomp_is_topicgrading_enabled()) {
								block_exacomp_save_additional_grading_for_comp($courseid, $descriptor->compid, $student->id,
									\block_exacomp\global_config::get_value_additionalinfo_mapping($grading_scheme), $comptype = 1);
							}

							block_exacomp_set_user_competence($student->id, $topic->compid,
								1, $courseid, BLOCK_EXACOMP_ROLE_TEACHER, $grading_scheme);
							mtrace("set topic competence ".$topic->compid." for user ".$student->id.'<br>');

						}
					}

					if (!$quiz_assignment) {
						$quiz_assignment = new \stdClass();
						$quiz_assignment->quiz = $test->id;
						$quiz_assignment->userid = $student->id;
						$quiz_assignment->timemodified = $quiz->timemodified;
						$DB->insert_record(BLOCK_EXACOMP_DB_AUTOTESTASSIGN, $quiz_assignment);
					} else {
						$quiz_assignment->timemodified = $quiz->timemodified;
						$DB->update_record(BLOCK_EXACOMP_DB_AUTOTESTASSIGN, $quiz_assignment);
					}
				}
			}
		}
	}

	return true;
}

function block_exacomp_get_gained_competences($course, $student) {

	$gained_competencies_teacher = [];
	$gained_competencies_student = [];

	$total_descriptors = 0;

	$dbLayer = \block_exacomp\db_layer_student::create($course->id, $student->id);
	$topics = $dbLayer->get_topics();
	$descriptors = $dbLayer->get_descriptor_parents();

	foreach ($descriptors as $descriptor) {
		if ($comp = block_exacomp_get_comp_eval_gained($course->id, BLOCK_EXACOMP_ROLE_TEACHER, $student->id, BLOCK_EXACOMP_TYPE_DESCRIPTOR, $descriptor->id)) {
			$gained_competencies_teacher[] = $comp;
		}
		if ($comp = block_exacomp_get_comp_eval_gained($course->id, BLOCK_EXACOMP_ROLE_STUDENT, $student->id, BLOCK_EXACOMP_TYPE_DESCRIPTOR, $descriptor->id)) {
			$gained_competencies_student[] = $comp;
		}
	}

	foreach ($topics as $topic) {
		if ($comp = block_exacomp_get_comp_eval_gained($course->id, BLOCK_EXACOMP_ROLE_TEACHER, $student->id, BLOCK_EXACOMP_TYPE_TOPIC, $topic->id)) {
			$gained_competencies_teacher[] = $comp;
		}
		if ($comp = block_exacomp_get_comp_eval_gained($course->id, BLOCK_EXACOMP_ROLE_STUDENT, $student->id, BLOCK_EXACOMP_TYPE_TOPIC, $topic->id)) {
			$gained_competencies_student[] = $comp;
		}
	}

	return [$gained_competencies_teacher, $gained_competencies_student, count($descriptors)];
}

/**
 *
 * check if there are already evaluations available
 * @param unknown_type $courseid
 */
function block_exacomp_check_user_evaluation_exists($courseid) {
	$students = block_exacomp_get_students_by_course($courseid);
	foreach ($students as $student) {
		$info = block_exacomp_get_user_competences_by_course($student, $courseid);

		if (!empty($info->competencies->teacher) || !empty($info->comptencies->student)) {
			return true;
		}
	}

	return false;
}

/**
 * build a schooltype -> subjects tree with given subjects
 * @param unknown_type $subjects
 * @return tree like:
 *  schooltype1
 *      - subject 1
 *      - subject 2
 *  schooltype2
 *      - subject 3
 */
function block_exacomp_get_schooltypetree_by_topics($subjects, $competencegrid = false) {
	$tree = array();
	foreach ($subjects as $subject) {
		if (!$competencegrid) {
			$schooltype = \block_exacomp\subject::get($subject->subjid);
		} else {
			$schooltype = block_exacomp_get_schooltype_by_subject($subject);
		}

		if (!array_key_exists($schooltype->id, $tree)) {
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
 * get all crosssubjects, submitted as drafts, available in all courses
 * @return block_exacomp\cross_subject[]
 */
function block_exacomp_get_cross_subjects_drafts() {
	return block_exacomp\cross_subject::get_objects(array('courseid' => 0));
}

/**
 *
 * save the given drafts to course
 * @param array $drafts_to_save
 * @param int $courseid
 */
function block_exacomp_save_drafts_to_course($drafts_to_save, $courseid) {
	//TODO test TOPICVISIBILITY
	global $DB, $USER;
	$redirect_crosssubjid = 0;
	foreach ($drafts_to_save as $draftid) {
		$draft = $DB->get_record(BLOCK_EXACOMP_DB_CROSSSUBJECTS, array('id' => $draftid));
		$draft->courseid = $courseid;
		$draft->creatorid = $USER->id;
		$draft->sourceid = 0;
		$draft->source = BLOCK_EXACOMP_IMPORT_SOURCE_SPECIFIC;
		$crosssubjid = $DB->insert_record(BLOCK_EXACOMP_DB_CROSSSUBJECTS, $draft);

		if ($redirect_crosssubjid == 0) {
			$redirect_crosssubjid = $crosssubjid;
		}

		//assign competencies
		$comps = $DB->get_records(BLOCK_EXACOMP_DB_DESCCROSS, array('crosssubjid' => $draftid));
		foreach ($comps as $comp) {
			$insert = new stdClass();
			$insert->descrid = $comp->descrid;
			$insert->crosssubjid = $crosssubjid;
			$DB->insert_record(BLOCK_EXACOMP_DB_DESCCROSS, $insert);

			//cross course subjects -> insert in visibility table if not existing
			$visibility = $DB->get_record(BLOCK_EXACOMP_DB_DESCVISIBILITY, array('courseid' => $courseid, 'descrid' => $comp->descrid, 'studentid' => 0));
			if (!$visibility) {
				$insert = new stdClass();
				$insert->courseid = $courseid;
				$insert->descrid = $comp->descrid;
				$insert->studentid = 0;
				$insert->visible = 1;
				$DB->insert_record(BLOCK_EXACOMP_DB_DESCVISIBILITY, $insert);
			}

			//check if descriptor has parent and if parent is visible in course
			$descriptor = $DB->get_record(BLOCK_EXACOMP_DB_DESCRIPTORS, array('id' => $comp->descrid));
			if ($descriptor->parentid != 0) { //has parent
				$parent_visibility = $DB->get_record(BLOCK_EXACOMP_DB_DESCVISIBILITY, array('courseid' => $courseid, 'descrid' => $descriptor->parentid, 'studentid' => 0));
				if (!$parent_visibility) { //not visible insert in table
					$insert = new stdClass();
					$insert->courseid = $courseid;
					$insert->descrid = $descriptor->parentid;
					$insert->studentid = 0;
					$insert->visible = 1;
					$DB->insert_record(BLOCK_EXACOMP_DB_DESCVISIBILITY, $insert);
				}
			}
		}
	}

	return $redirect_crosssubjid;
}

/**
 * create new crosssubject in course
 * @param unknown $courseid
 * @param unknown $title
 * @param unknown $description
 * @param unknown $creatorid
 * @param number $subjectid
 */
function block_exacomp_create_crosssub($courseid, $title, $description, $creatorid, $subjectid = 0) {
	global $DB;

	$insert = new stdClass();
	$insert->title = $title;
	$insert->description = $description;
	$insert->courseid = $courseid;
	$insert->creatorid = $creatorid;
	$insert->subjectid = $subjectid;
	$insert->sourceid = 0;
	$insert->source = BLOCK_EXACOMP_IMPORT_SOURCE_SPECIFIC;

	return $DB->insert_record(BLOCK_EXACOMP_DB_CROSSSUBJECTS, $insert);
}

/**
 * update title, description or subjectid of crosssubject
 * @param unknown $crosssubjid
 * @param unknown $title
 * @param unknown $description
 * @param unknown $subjectid
 */
function block_exacomp_edit_crosssub($crosssubjid, $title, $description, $subjectid) {
	global $DB;

	$crosssubj = $DB->get_record(BLOCK_EXACOMP_DB_CROSSSUBJECTS, array('id' => $crosssubjid));
	$crosssubj->title = $title;
	$crosssubj->description = $description;
	$crosssubj->subjectid = $subjectid;

	return $DB->update_record(BLOCK_EXACOMP_DB_CROSSSUBJECTS, $crosssubj);
}

/**
 * remove given crosssubject
 * @param unknown $crosssubjid
 */
function block_exacomp_delete_crosssub($crosssubjid) {
	global $DB;
	//delete student association if crosssubject is deleted
	$DB->delete_records(BLOCK_EXACOMP_DB_CROSSSTUD, array('crosssubjid' => $crosssubjid));

	return $DB->delete_records(BLOCK_EXACOMP_DB_CROSSSUBJECTS, array('id' => $crosssubjid));
}

/**
 * delete drafts for all users in all courses
 * @param unknown $drafts_to_delete
 */
function block_exacomp_delete_crosssubject_drafts($drafts_to_delete) {
	global $DB;
	foreach ($drafts_to_delete as $draftid) {
		$DB->delete_records(BLOCK_EXACOMP_DB_CROSSSUBJECTS, array('id' => $draftid));
	}
}

/**
 * returns all crosssubjects
 */
function block_exacomp_get_crosssubjects() {
	return g::$DB->get_records(BLOCK_EXACOMP_DB_CROSSSUBJECTS);
}

/**
 * @param $courseid
 * @param null $studentid
 * @return block_exacomp\cross_subject[]
 */
function block_exacomp_get_cross_subjects_by_course($courseid, $studentid = null) {
	$crosssubs = block_exacomp\cross_subject::get_objects(['courseid' => $courseid], 'title');

	if (!$studentid) {
		return $crosssubs;
	}

	// also check for student permissions
	$crosssubs_shared = array();
	foreach ($crosssubs as $crosssubj) {
		if ($crosssubj->shared == 1 || block_exacomp_student_crosssubj($crosssubj->id, $studentid)) {
			$crosssubs_shared[$crosssubj->id] = $crosssubj;
		}
	}

	return $crosssubs_shared;
}

/**
 * check crosssubject student association
 * @param unknown $crosssubjid
 * @param unknown $studentid
 */
function block_exacomp_student_crosssubj($crosssubjid, $studentid) {
	global $DB;

	return $DB->get_records(BLOCK_EXACOMP_DB_CROSSSTUD, array('crosssubjid' => $crosssubjid, 'studentid' => $studentid));
}

/**
 * Gets an associative array that is used to display the whole hierarchie of subjects, topics and competencies within a course
 *
 * @param int $courseid
 * @param int $subjectid
 * @return associative_array
 */
function block_exacomp_get_competence_tree_for_cross_subject($courseid, $cross_subject, $showallexamples = true, $filteredtaxonomies = array(BLOCK_EXACOMP_SHOW_ALL_TAXONOMIES), $studentid = 0, $showonlyvisibletopics = false) {
	// $showalldescriptors = block_exacomp_get_settings_by_course($courseid)->show_all_descriptors;

	$allTopics = block_exacomp_get_all_topics();
	$allSubjects = block_exacomp_get_subjects();

	$allDescriptors = block_exacomp_get_descriptors_for_cross_subject($courseid, $cross_subject, false);

	$subjects = [];
	$topics = [];

	foreach ($allTopics as $topic) {
		$topic->descriptors = [];
	}

	foreach ($allDescriptors as $descriptor) {
		// get descriptor topic
		if (empty($allTopics[$descriptor->topicid])) {
			continue;
		}
		$topic = $topics[$descriptor->topicid] = $allTopics[$descriptor->topicid];
		$topic->descriptors[$descriptor->id] = $descriptor;
	}

	foreach ($allSubjects as $subject) {
		$subject->topics = [];
	}

	foreach ($topics as $topic) {
		if (empty($allSubjects[$topic->subjid])) {
			continue;
		}

		$subject = $subjects[$topic->subjid] = $allSubjects[$topic->subjid];
		$subject->topics[$topic->id] = $topic;

		if ($showonlyvisibletopics && !block_exacomp_is_topic_visible($courseid, $topic, $studentid)) {
			continue;
		}

		// found: add it to the subject result
		$subjects[$subject->id] = $subject;
	}

	return block_exacomp\subject::create_objects($subjects);
}

function block_exacomp_get_descriptors_assigned_to_cross_subject($crosssubjid) {
	return g::$DB->get_records_sql("
		SELECT d.*
		FROM {".BLOCK_EXACOMP_DB_DESCRIPTORS."} d
		JOIN {".BLOCK_EXACOMP_DB_DESCCROSS."} dc ON d.id = dc.descrid
		WHERE dc.crosssubjid = ?
	", array($crosssubjid));
}

/**
 * get descriptors for crosssubject
 * @param unknown $courseid
 * @param unknown $crosssubjid
 * @param string $showalldescriptors
 * @return unknown
 */
function block_exacomp_get_descriptors_for_cross_subject($courseid, $cross_subject, $showalldescriptors = null) {
	global $DB;

	$crosssubjid = is_scalar($cross_subject) ? $cross_subject : $cross_subject->id;

	$assignedDescriptors = block_exacomp_get_descriptors_assigned_to_cross_subject($crosssubjid);
	if (!$assignedDescriptors) {
		return [];
	}

	$searchDescriptorIds = [];
	foreach ($assignedDescriptors as $descriptor) {
		$searchDescriptorIds[] = $descriptor->parentid ?: $descriptor->id;
	}

	/*
	$show_childs = array();
	foreach($comps as $comp){
		if($cross_descr->parentid == 0) //parent deskriptor -> show all childs
			$show_childs[$cross_descr->id] = true;
	}
	*/

	if ($showalldescriptors === null) {
		$showalldescriptors = block_exacomp_get_settings_by_course($courseid)->show_all_descriptors;
	}

	$sql = 'SELECT DISTINCT desctopmm.id as u_id, d.id as id, d.source, d.title, d.niveauid, t.id AS topicid, d.profoundness, d.sorting, d.parentid, n.sorting as niveau '
		.'FROM {'.BLOCK_EXACOMP_DB_TOPICS.'} t '
		.'JOIN {'.BLOCK_EXACOMP_DB_DESCTOPICS.'} desctopmm ON desctopmm.topicid=t.id '
		.'JOIN {'.BLOCK_EXACOMP_DB_DESCRIPTORS.'} d ON desctopmm.descrid=d.id AND d.parentid = 0 '
		.'LEFT JOIN {'.BLOCK_EXACOMP_DB_NIVEAUS.'} n ON n.id = d.niveauid '
		.'WHERE d.id IN('.join(',', $searchDescriptorIds).')';

	$descriptors = \block_exacomp\descriptor::get_objects_sql($sql);

	foreach ($descriptors as $descriptor) {
		if (isset($assignedDescriptors[$descriptor->id])) {
			// assigned, ok
		} else {
			// not assigned = nicht direkt ausgewählt => children checken
			foreach ($descriptor->children as $child_descriptor) {
				if (!isset($assignedDescriptors[$child_descriptor->id])) {
					unset($descriptor->children[$child_descriptor->id]);
				}
			}
		}
	}

	/*
	foreach($descriptors as &$descriptor) {
		//get examples
		if(array_key_exists($descriptor->id, $comps) || (isset($show_childs[$descriptor->id]) && $show_childs[$descriptor->id]))
			$descriptor = block_exacomp_get_examples_for_descriptor($descriptor,array(BLOCK_EXACOMP_SHOW_ALL_TAXONOMIES), true, $courseid);
		else $descriptor->examples = array();

		//check for child-descriptors
		$descriptor->children = block_exacomp_get_child_descriptors($descriptor,$courseid, $showalldescriptors);
		foreach($descriptor->children as $cid => $cvalue) {
			if(!array_key_exists($cid, $comps) && (!isset($show_childs[$descriptor->id])||!($show_childs[$descriptor->id])))
				unset($descriptor->children[$cid]);
		}
		$descriptor->categories = block_exacomp_get_categories_for_descriptor($descriptor);
	}
	*/

	return $descriptors;

}

function block_exacomp_get_subjects_for_cross_subject($cross_subject) {
	$crosssubjid = is_scalar($cross_subject) ? $cross_subject : $cross_subject->id;

	return \block_exacomp\subject::get_objects_sql("
		SELECT s.*
		FROM {".BLOCK_EXACOMP_DB_SUBJECTS."} s
		WHERE id IN (
			SELECT t.subjid
			FROM {".BLOCK_EXACOMP_DB_TOPICS."} t
			JOIN {".BLOCK_EXACOMP_DB_DESCTOPICS."} dt ON t.id = dt.topicid
			JOIN {".BLOCK_EXACOMP_DB_DESCRIPTORS."} d ON dt.descrid=d.id
			JOIN {".BLOCK_EXACOMP_DB_DESCCROSS."} dc ON d.id = dc.descrid
			WHERE dc.crosssubjid = ?
		);
	", [$crosssubjid]);
}

/**
 * associate descriptors with crosssubjects
 * @param unknown $crosssubjid
 * @param unknown $descrid
 */
function block_exacomp_set_cross_subject_descriptor($crosssubjid, $descrid) {
	global $DB, $COURSE;
	$record = $DB->get_record(BLOCK_EXACOMP_DB_DESCCROSS, array('crosssubjid' => $crosssubjid, 'descrid' => $descrid));
	if (!$record) {
		$DB->insert_record(BLOCK_EXACOMP_DB_DESCCROSS, array('crosssubjid' => $crosssubjid, 'descrid' => $descrid));
	}

	//insert visibility if cross course
	$cross_subject = $DB->get_record(BLOCK_EXACOMP_DB_CROSSSUBJECTS, array('id' => $crosssubjid));
	$visibility = $DB->get_record(BLOCK_EXACOMP_DB_DESCVISIBILITY, array('courseid' => $cross_subject->courseid, 'descrid' => $descrid, 'studentid' => 0));
	if (!$visibility) {
		$insert = new stdClass();
		$insert->courseid = $cross_subject->courseid;
		$insert->descrid = $descrid;
		$insert->studentid = 0;
		$insert->visible = 1;
		$DB->insert_record(BLOCK_EXACOMP_DB_DESCVISIBILITY, $insert);
	}

	$descriptor = $DB->get_record(BLOCK_EXACOMP_DB_DESCRIPTORS, array('id' => $descrid));

	if ($descriptor->parentid == 0) {    //insert children into visibility table
		//get topicid
		$descriptor_topic_mm = $DB->get_record(BLOCK_EXACOMP_DB_DESCTOPICS, array('descrid' => $descriptor->id));
		$descriptor->topicid = $descriptor_topic_mm->topicid;

		$children = block_exacomp_get_child_descriptors($descriptor, $COURSE->id);

		foreach ($children as $child) {
			$visibility = $DB->get_record(BLOCK_EXACOMP_DB_DESCVISIBILITY, array('courseid' => $cross_subject->courseid, 'descrid' => $child->id, 'studentid' => 0));
			if (!$visibility) {
				$insert = new stdClass();
				$insert->courseid = $cross_subject->courseid;
				$insert->descrid = $child->id;
				$insert->studentid = 0;
				$insert->visible = 1;
				$DB->insert_record(BLOCK_EXACOMP_DB_DESCVISIBILITY, $insert);

				//insert example visibility if not existent
				$child = block_exacomp_get_examples_for_descriptor($child, array(BLOCK_EXACOMP_SHOW_ALL_TAXONOMIES), true, $COURSE->id);
				foreach ($child->examples as $example) {
					$record = $DB->get_records(BLOCK_EXACOMP_DB_EXAMPVISIBILITY, array('courseid' => $cross_subject->courseid, 'exampleid' => $example->id, 'studentid' => 0));
					if (!$record) {
						$insert = new stdClass();
						$insert->courseid = $cross_subject->courseid;
						$insert->exampleid = $example->id;
						$insert->studentid = 0;
						$insert->visible = 1;
						$DB->insert_record(BLOCK_EXACOMP_DB_EXAMPVISIBILITY, $insert);
					}
				}
			}
		}
	} else { //insert parent into visibility table
		$visibility = $DB->get_record(BLOCK_EXACOMP_DB_DESCVISIBILITY, array('courseid' => $cross_subject->courseid, 'descrid' => $descriptor->parentid, 'studentid' => 0));
		if (!$visibility) {
			$insert = new stdClass();
			$insert->courseid = $cross_subject->courseid;
			$insert->descrid = $descriptor->parentid;
			$insert->studentid = 0;
			$insert->visible = 1;
			$DB->insert_record(BLOCK_EXACOMP_DB_DESCVISIBILITY, $insert);
		}
	}

	//example visibility
	$descriptor = block_exacomp_get_examples_for_descriptor($descriptor, array(BLOCK_EXACOMP_SHOW_ALL_TAXONOMIES), true, $COURSE->id);

	foreach ($descriptor->examples as $example) {
		$record = $DB->get_records(BLOCK_EXACOMP_DB_EXAMPVISIBILITY, array('courseid' => $cross_subject->courseid, 'exampleid' => $example->id, 'studentid' => 0));
		if (!$record) {
			$insert = new stdClass();
			$insert->courseid = $cross_subject->courseid;
			$insert->exampleid = $example->id;
			$insert->studentid = 0;
			$insert->visible = 1;

			$DB->insert_record(BLOCK_EXACOMP_DB_EXAMPVISIBILITY, $insert);
		}
	}
}

/**
 * unset crosssubject and descriptor association
 * @param unknown $crosssubjid
 * @param unknown $descrid
 */
function block_exacomp_unset_cross_subject_descriptor($crosssubjid, $descrid) {
	global $DB, $COURSE;
	$record = $DB->get_record(BLOCK_EXACOMP_DB_DESCCROSS, array('crosssubjid' => $crosssubjid, 'descrid' => $descrid));
	if ($record) {
		$DB->delete_records(BLOCK_EXACOMP_DB_DESCCROSS, array('crosssubjid' => $crosssubjid, 'descrid' => $descrid));
	}

	//delete visibility of non course descriptors, not connected to another course crosssubject
	$cross_subject = $DB->get_record(BLOCK_EXACOMP_DB_CROSSSUBJECTS, array('id' => $crosssubjid));
	$cross_courseid = $cross_subject->courseid;

	if ($cross_courseid != $COURSE->id) {    //not current course
		$course_descriptors = block_exacomp_get_descriptors($cross_courseid);

		if (!array_key_exists($descrid, $course_descriptors)) {    // no course descriptor -> cross course
			$descriptor_crosssubs_mm = $DB->get_records(BLOCK_EXACOMP_DB_DESCCROSS, array('descrid' => $descrid));
			$course_cross_subjects = block_exacomp_get_cross_subjects_by_course($cross_courseid);

			$used_in_other_crosssub = false;
			foreach ($descriptor_crosssubs_mm as $entry) {
				if ($entry->crosssubjid != $cross_subject->id) {
					if (array_key_exists($entry->crosssubjid, $course_cross_subjects)) {
						$used_in_other_crosssub = true;
					}
				}
			}

			if (!$used_in_other_crosssub) { // delete visibility if not used in other cross subject in this course
				$DB->delete_records(BLOCK_EXACOMP_DB_DESCVISIBILITY, array('descrid' => $descrid, 'courseid' => $cross_courseid, 'studentid' => 0));
			}
		}
	}
}

/**
 * change descriptor visibility, studentid = 0 : visibility settings for all students
 * @param unknown $descrid
 * @param unknown $courseid
 * @param unknown $visible
 * @param unknown $studentid
 */
function block_exacomp_set_descriptor_visibility($descrid, $courseid, $visible, $studentid) {
	global $DB;
	if ($studentid == BLOCK_EXACOMP_SHOW_ALL_STUDENTS || $studentid == 0) {//if visibility changed for all: delete individual settings
		$studentid = 0;
		$sql = "DELETE FROM {".BLOCK_EXACOMP_DB_DESCVISIBILITY."} WHERE descrid = ? AND courseid = ? and studentid <> 0";

		$DB->execute($sql, array($descrid, $courseid));
	}

	g::$DB->insert_or_update_record(BLOCK_EXACOMP_DB_DESCVISIBILITY,
		['visible' => $visible],
		['descrid' => $descrid, 'courseid' => $courseid, 'studentid' => $studentid]
	);

	block_exacomp_clear_visibility_cache($courseid);
}

/**
 * change example visibility, studentid = 0: visibility settings for all students
 * @param unknown $exampleid
 * @param unknown $courseid
 * @param unknown $visible
 * @param unknown $studentid
 */
function block_exacomp_set_example_visibility($exampleid, $courseid, $visible, $studentid) {
	if ($studentid == BLOCK_EXACOMP_SHOW_ALL_STUDENTS || $studentid == 0) {//if visibility changed for all: delete individual settings
		$studentid = 0;
		$sql = "DELETE FROM {".BLOCK_EXACOMP_DB_EXAMPVISIBILITY."} WHERE exampleid = ? AND courseid = ? and studentid <> 0";
		g::$DB->execute($sql, array($exampleid, $courseid));
	}

	g::$DB->insert_or_update_record(BLOCK_EXACOMP_DB_EXAMPVISIBILITY,
		['visible' => $visible],
		['exampleid' => $exampleid, 'courseid' => $courseid, 'studentid' => $studentid]
	);

	block_exacomp_clear_visibility_cache($courseid);
}

/**
 * change example solution visibility, studentid = 0: visibility settings for all students
 * @param unknown $exampleid
 * @param unknown $courseid
 * @param unknown $visible
 * @param unknown $studentid
 */
function block_exacomp_set_example_solution_visibility($exampleid, $courseid, $visible, $studentid) {
	if ($studentid == BLOCK_EXACOMP_SHOW_ALL_STUDENTS || $studentid == 0) {//if visibility changed for all: delete individual settings
		$studentid = 0;
		$sql = "DELETE FROM {".BLOCK_EXACOMP_DB_SOLUTIONVISIBILITY."} WHERE exampleid = ? AND courseid = ? and studentid <> 0";
		g::$DB->execute($sql, array($exampleid, $courseid));
	}

	g::$DB->insert_or_update_record(BLOCK_EXACOMP_DB_SOLUTIONVISIBILITY,
		['visible' => $visible],
		['exampleid' => $exampleid, 'courseid' => $courseid, 'studentid' => $studentid]
	);
}

/**
 * change topic visibility settings, studentid = 0: visibility settings for all students
 * @param unknown $topicid
 * @param unknown $courseid
 * @param unknown $visible
 * @param unknown $studentid
 */
function block_exacomp_set_topic_visibility($topicid, $courseid, $visible, $studentid) {
	global $DB;
	if ($studentid == BLOCK_EXACOMP_SHOW_ALL_STUDENTS || $studentid == 0) {//if visibility changed for all: delete individual settings
		$studentid = 0;
		$sql = "DELETE FROM {".BLOCK_EXACOMP_DB_TOPICVISIBILITY."} WHERE topicid = ? AND courseid = ? and studentid <> 0";

		$DB->execute($sql, array($topicid, $courseid));
	}
	g::$DB->insert_or_update_record(BLOCK_EXACOMP_DB_TOPICVISIBILITY,
		['visible' => $visible],
		['topicid' => $topicid, 'courseid' => $courseid, 'studentid' => $studentid]
	);
	block_exacomp_clear_visibility_cache($courseid);
}

/**
 * check if topic or any underlying (descriptor, example) is used
 * used for topic: student or teacher evaluation exists
 * @param unknown $courseid
 * @param unknown $topic
 * @param unknown $studentid
 * @return boolean
 */
function block_exacomp_is_topic_used($courseid, $topic, $studentid) {
	global $DB;
	if ($studentid == 0) {
		$sql = "SELECT * FROM {".BLOCK_EXACOMP_DB_COMPETENCES."} WHERE courseid = ? AND compid = ? AND comptype=? AND ( value>=0 OR additionalinfo IS NOT NULL)";
		$records = $DB->get_records_sql($sql, array($courseid, $topic->id, BLOCK_EXACOMP_TYPE_TOPIC));
		if ($records) {
			return true;
		}
	} else {
		$sql = "SELECT * FROM {".BLOCK_EXACOMP_DB_COMPETENCES."} WHERE courseid = ? AND compid = ? AND comptype=? AND userid = ? AND ( value>=0 OR additionalinfo IS NOT NULL)";
		$records = $DB->get_records_sql($sql, array($courseid, $topic->id, BLOCK_EXACOMP_TYPE_TOPIC, $studentid));
		if ($records) {
			return true;
		}
	}

	$descriptors = block_exacomp_get_descriptors_by_topic($courseid, $topic->id);
	foreach ($descriptors as $descriptor) {
		$descriptor->children = block_exacomp_get_child_descriptors($descriptor, $courseid);
		if (block_exacomp_descriptor_used($courseid, $descriptor, $studentid)) {
			return true;
		}
	}

	return false;
}

/**
 * check if descriptor or underlying object (childdescriptor, example) is used
 * descriptor used: teacher or student evaluation exists
 * @param unknown $courseid
 * @param unknown $descriptor
 * @param unknown $studentid
 * @return boolean
 */
function block_exacomp_descriptor_used($courseid, $descriptor, $studentid) {
	global $DB;
	//if studentid == 0 used = true, if no evaluation (teacher OR student) for this descriptor for any student in this course
	//								 if no evaluation/submission for the examples of this descriptor

	//if studentid != 0 used = true, if any assignment (teacher OR student) for this descriptor for THIS student in this course
	//								 if no evaluation/submission for the examples of this descriptor

	if (!isset($descriptor->examples)) {
		$descriptor = block_exacomp_get_examples_for_descriptor($descriptor);
	}

	if ($studentid == 0) {
		$sql = "SELECT * FROM {".BLOCK_EXACOMP_DB_COMPETENCES."} WHERE courseid = ? AND compid = ? AND comptype=? AND ( value>=0 OR additionalinfo IS NOT NULL)";
		$records = $DB->get_records_sql($sql, array($courseid, $descriptor->id, BLOCK_EXACOMP_TYPE_DESCRIPTOR));
		if ($records) {
			return true;
		}
	} else {
		$sql = "SELECT * FROM {".BLOCK_EXACOMP_DB_COMPETENCES."} WHERE courseid = ? AND compid = ? AND comptype=? AND userid = ? AND ( value>=0 OR additionalinfo IS NOT NULL)";
		$records = $DB->get_records_sql($sql, array($courseid, $descriptor->id, BLOCK_EXACOMP_TYPE_DESCRIPTOR, $studentid));
		if ($records) {
			return true;
		}
	}

	if (isset($descriptor->children)) {
		//check child used
		foreach ($descriptor->children as $child) {
			if (block_exacomp_descriptor_used($courseid, $child, $studentid)) {
				return true;
			}
		}
	}

	if ($descriptor->examples) {
		foreach ($descriptor->examples as $example) {
			if (block_exacomp_example_used($courseid, $example, $studentid)) {
				return true;
			}
		}
	}

	return false;
}

/**
 * check if example is used
 * example used: student or teacherevaluation exists, submission exists, example on weekly schedule or
 * on pre-planning storage,
 * @param unknown $courseid
 * @param unknown $example
 * @param unknown $studentid
 * @return boolean
 */
function block_exacomp_example_used($courseid, $example, $studentid) {
	global $DB;
	//if studentid == 0 used = true, if no evaluation/submission for this example
	//if studentid != 0 used = true, if no evaluation/submission for this examples for this student

	if ($studentid <= 0) { // any self or teacher evaluation
		$sql = "SELECT * FROM {".BLOCK_EXACOMP_DB_EXAMPLEEVAL."} WHERE courseid = ? AND exampleid = ? AND teacher_evaluation>= 0";
		$records = $DB->get_records_sql($sql, array($courseid, $example->id));
		if ($records) {
			return true;
		}

		$sql = "SELECT * FROM {".BLOCK_EXACOMP_DB_EXAMPLEEVAL."} WHERE courseid = ? AND exampleid = ? AND student_evaluation>= 0";
		$records = $DB->get_records_sql($sql, array($courseid, $example->id));
		if ($records) {
			return true;
		}

		//on any weekly schedule? -> yes: used
		$onSchedule = $DB->record_exists(BLOCK_EXACOMP_DB_SCHEDULE, array('courseid' => $courseid, 'exampleid' => $example->id));
		if ($onSchedule) {
			return true;
		}

		//any submission made?
		if (block_exacomp_exaportexists()) {
			$sql = "SELECT * FROM {".BLOCK_EXACOMP_DB_ITEMEXAMPLE."} ie JOIN {".'block_exaportitem'."} i ON ie.itemid = i.id ".
				"WHERE ie.exampleid = ? AND i.courseid = ?";
			$records = $DB->get_records_sql($sql, array($example->id, $courseid));
			if ($records) {
				return true;
			}
		}
	} else { // any self or teacher evaluation for this student
		$sql = "SELECT * FROM {".BLOCK_EXACOMP_DB_EXAMPLEEVAL."} WHERE courseid = ? AND exampleid = ? AND studentid=? AND teacher_evaluation>=0";
		$records = $DB->get_records_sql($sql, array($courseid, $example->id, $studentid));
		if ($records) {
			return true;
		}

		$sql = "SELECT * FROM {".BLOCK_EXACOMP_DB_EXAMPLEEVAL."} WHERE courseid = ? AND exampleid = ? AND studentid = ? AND student_evaluation>=0";
		$records = $DB->get_records_sql($sql, array($courseid, $example->id, $studentid));
		if ($records) {
			return true;
		}

		//on students weekly schedule? -> yes: used
		$onSchedule = $DB->record_exists(BLOCK_EXACOMP_DB_SCHEDULE, array('studentid' => $studentid, 'courseid' => $courseid, 'exampleid' => $example->id));
		if ($onSchedule) {
			return true;
		}

		//or on pre planning storage
		$onSchedule = $DB->record_exists(BLOCK_EXACOMP_DB_SCHEDULE, array('studentid' => 0, 'courseid' => $courseid, 'exampleid' => $example->id));
		if ($onSchedule) {
			return true;
		}

		//submission made?
		if (block_exacomp_exaportexists()) {
			$sql = "SELECT * FROM {".BLOCK_EXACOMP_DB_ITEMEXAMPLE."} ie JOIN {".'block_exaportitem'."} i ON ie.itemid = i.id ".
				"WHERE ie.exampleid = ? AND i.userid = ? AND i.courseid = ?";
			$records = $DB->get_records_sql($sql, array($example->id, $studentid, $courseid));
			if ($records) {
				return true;
			}
		}
	}

	return false;
}

/**
 * get all students associated with a crosssubject
 * @param unknown $courseid
 * @param unknown $crosssub
 * @return unknown|unknown[]
 */
function block_exacomp_get_students_for_crosssubject($courseid, $crosssub) {
	global $DB;
	$course_students = block_exacomp_get_students_by_course($courseid);
	if ($crosssub->shared) {
		return $course_students;
	}

	$students = array();
	$assigned_students = $DB->get_records_menu(BLOCK_EXACOMP_DB_CROSSSTUD, array('crosssubjid' => $crosssub->id), '', 'studentid,crosssubjid');
	foreach ($course_students as $student) {
		if (isset($assigned_students[$student->id])) {
			$students[$student->id] = $student;
		}
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

	if (!$item) {
		return null;
	}

	if ($studentid == $viewerid) {
		// view my own item
		$access = "portfolio/id/".$studentid."&itemid=".$item->id;
	} else {
		// view sb elses item, find a suitable view
		$sql = "SELECT viewblock.*
			FROM {block_exaportviewblock} viewblock
			JOIN {block_exaportviewshar} viewshar ON viewshar.viewid=viewblock.viewid
			WHERE viewblock.type='item' AND viewblock.itemid=? AND viewshar.userid=?";
		$view = $DB->get_record_sql($sql, [$item->id, $viewerid]);
		if (!$view) {
			return null;
		}

		$access = "view/id/".$studentid."-".$view->viewid."&itemid=".$item->id;
	}

	return $CFG->wwwroot.'/blocks/exaport/shared_item.php?access='.$access;
}

/**
 * get the url to enter the competence overview example belongs to
 * @param unknown $courseid
 * @param unknown $studentid
 * @param unknown $exampleid
 * @return string
 */
function block_exacomp_get_gridurl_for_example($courseid, $studentid, $exampleid) {
	global $CFG, $DB;

	$example_descriptors = $DB->get_records(BLOCK_EXACOMP_DB_DESCEXAMP, array('exampid' => $exampleid), '', 'descrid');
	$tree = block_exacomp_build_example_association_tree($courseid, $example_descriptors, $exampleid);
	$topic = (reset(reset($tree)->topics));

	return $CFG->wwwroot.'/blocks/exacomp/assign_competencies.php?courseid='.$courseid.'&studentid='.$studentid.'&subjectid='.$topic->subjid.'&topicid='.$topic->id;
}

/**
 * add example to students schedule, if start and end not set, example is added to planning storage
 * @param unknown $studentid
 * @param unknown $exampleid
 * @param unknown $creatorid
 * @param unknown $courseid
 * @param unknown $start
 * @param unknown $end
 * @return boolean
 */
function block_exacomp_add_example_to_schedule($studentid, $exampleid, $creatorid, $courseid, $start = null, $end = null, $is_pps) {
	global $USER, $DB;

	$timecreated = $timemodified = time();

	// prüfen, ob element schon zur gleichen zeit im wochenplan ist
	if ($DB->get_record(BLOCK_EXACOMP_DB_SCHEDULE, array('studentid' => $studentid, 'exampleid' => $exampleid, 'courseid' => $courseid, 'start' => $start))) {
		return true;
	}

	$DB->insert_record(BLOCK_EXACOMP_DB_SCHEDULE, array('studentid' => $studentid, 'exampleid' => $exampleid, 'courseid' => $courseid, 'creatorid' => $creatorid, 'timecreated' => $timecreated, 
	                                                    'timemodified' => $timemodified, 'start' => $start, 'end' => $end, 'deleted' => 0,'is_pps' => $is_pps));
	//only send a notification if a teacher adds an example for a student and not for pre planning storage
	if ($creatorid != $studentid && $studentid > 0) {
		block_exacomp_send_weekly_schedule_notification($USER, $DB->get_record('user', array('id' => $studentid)), $courseid, $exampleid);
	}

	\block_exacomp\event\example_added::log(['objectid' => $exampleid, 'courseid' => $courseid, 'relateduserid' => $studentid]);
	
	return true;
}

/**
 * add example to all planning storages for all students in course
 * @param unknown $courseid
 * @return boolean
 */
function block_exacomp_add_examples_to_schedule_for_all($courseid) {
	// Check Permission
	block_exacomp_require_teacher($courseid);
	// Get all examples to add:
	//    -> studentid 0: on teachers schedule
	$examples = g::$DB->get_records_select(BLOCK_EXACOMP_DB_SCHEDULE, "studentid = 0 AND courseid = ? AND start IS NOT NULL AND end IS NOT NULL AND deleted = 0", array($courseid));

	// Get all students for the given course
	$students = block_exacomp_get_students_by_course($courseid);
	// Add examples for all users
	foreach ($examples as $example) {
		foreach ($students as $student) {
		    if (block_exacomp_is_example_visible($courseid, $example->exampleid, $student->id)) {
		        block_exacomp_add_example_to_schedule($student->id, $example->exampleid, g::$USER->id, $courseid, $example->start, $example->end, 0);
		    }
		}
	}

	// Delete records from the teacher's schedule
	g::$DB->delete_records_list(BLOCK_EXACOMP_DB_SCHEDULE, 'id', array_keys($examples));

	return true;
}

/**
 * help function for printer
 * @param unknown $date
 * @param unknown $days
 * @return number
 */
function block_exacomp_add_days($date, $days) {
	return mktime(0, 0, 0, date('m', $date), date('d', $date) + $days, date('Y', $date));
}

/**
 * get tree where a flag for each object (from subject to child descriptor) indicates if an example is associated with
 * @param unknown $courseid
 * @param array $example_descriptors
 * @param number $exampleid
 * @param number $descriptorid
 * @param string $showallexamples
 * @return associative_array
 */
function block_exacomp_build_example_association_tree($courseid, $example_descriptors = array(), $exampleid = 0, $descriptorid = 0, $showallexamples = false) {
	//get all subjects, topics, descriptors and examples
	$tree = block_exacomp_get_competence_tree($courseid, null, null, false, BLOCK_EXACOMP_SHOW_ALL_NIVEAUS, true, block_exacomp_get_settings_by_course($courseid)->filteredtaxonomies, false, false, true);

	// unset all descriptors, topics and subjects that do not contain the example descriptors
	foreach ($tree as $skey => $subject) {
		$subject->associated = 0;
		foreach ($subject->topics as $tkey => $topic) {
			$topic->associated = 0;
			if (isset($topic->descriptors)) {
				foreach ($topic->descriptors as $dkey => $descriptor) {

					$descriptor = block_exacomp_check_child_descriptors($descriptor, $example_descriptors, $exampleid, $descriptorid, $showallexamples);

					if ($descriptor->associated) {
						$topic->associated = 1;
					}
				}
			}

			if ($topic->associated) {
				$subject->associated = 1;
			}
		}
	}

	return $tree;
}

/**
 * helper function for block_exacomp_build_example_association_tree
 * @param unknown $descriptor
 * @param unknown $example_descriptors
 * @param unknown $exampleid
 * @param number $descriptorid
 * @param string $showallexamples
 * @return unknown
 */
function block_exacomp_check_child_descriptors($descriptor, $example_descriptors, $exampleid, $descriptorid = 0, $showallexamples = false) {

	$descriptor->associated = 0;
	$descriptor->direct_associated = 0;

	if (array_key_exists($descriptor->id, $example_descriptors) || $descriptorid == $descriptor->id || ($showallexamples && !empty($descriptor->examples))) {
		$descriptor->associated = 1;
		$descriptor->direct_associated = 1;
	}

	//check descriptor examples
	foreach ($descriptor->examples as $ekey => $example) {
		$descriptor->examples[$ekey]->associated = 1;
		if ($example->id != $exampleid && !$showallexamples) {
			$descriptor->examples[$ekey]->associated = 0;
		}
	}

	//check children and their examples
	foreach ($descriptor->children as $ckey => $cvalue) {
		$keepDescriptor_child = false;
		if (array_key_exists($cvalue->id, $example_descriptors) || $descriptorid == $ckey || ($showallexamples && !empty($cvalue->examples))) {
			$keepDescriptor_child = true;
			$descriptor->associated = 1;
		}
		$descriptor->children[$ckey]->associated = 1;
		$descriptor->children[$ckey]->direct_associated = 1;
		if (!$keepDescriptor_child) {
			$descriptor->children[$ckey]->associated = 0;
			$descriptor->children[$ckey]->direct_associated = 0;
			continue;
		}
		foreach ($cvalue->examples as $ekey => $example) {
			$cvalue->examples[$ekey]->associated = 1;
			if ($example->id != $exampleid && !$showallexamples) {
				$cvalue->examples[$ekey]->associated = 0;
			}
		}
	}

	return $descriptor;
}

/**
 * descriptors can be across all niveaus, a colspan is needed to display tables correct
 * @param unknown $niveaus
 * @param unknown $spanningNiveaus
 * @return number
 */
function block_exacomp_calculate_spanning_niveau_colspan($niveaus, $spanningNiveaus) {

	$colspan = count($niveaus) - 1;

	foreach ($niveaus as $id => $niveau) {
		if ((isset($niveau->title)) ? in_array($niveau->title, $spanningNiveaus) : in_array($niveau, $spanningNiveaus)) {
			$colspan--;
		}
	}

	return $colspan;
}

/**
 * visibility for topic in course and user context
 * @param unknown $courseid
 * @param unknown $topic
 * @param unknown $studentid
 * @return boolean
 */
function block_exacomp_is_topic_visible($courseid, $topic, $studentid) {
	global $DB;

	// $studentid could be BLOCK_EXACOMP_SHOW_ALL_STUDENTS
	if ($studentid <= 0) {
		$studentid = 0;
	}

	$visibilities = block_exacomp_get_topic_visibilities_for_course_and_user($courseid, 0);
	if (isset($visibilities[$topic->id]) && !$visibilities[$topic->id]) {
		return false;
	}

	if ($studentid > 0) {
		// also check student if set
		$visibilities = block_exacomp_get_topic_visibilities_for_course_and_user($courseid, $studentid);
		if (isset($visibilities[$topic->id]) && !$visibilities[$topic->id]) {
			return false;
		}
	}

	return true;
}

/**
 * visibility for descriptor in course and user context
 * @param unknown $courseid
 * @param unknown $descriptor
 * @param unknown $studentid
 * @return boolean
 */
function block_exacomp_is_descriptor_visible($courseid, $descriptor, $studentid) {
	global $DB;

	// $studentid could be BLOCK_EXACOMP_SHOW_ALL_STUDENTS
	if ($studentid <= 0) {
		$studentid = 0;
	}

	if (($topic = \block_exacomp\topic::get($descriptor->topicid)) && !block_exacomp_is_topic_visible($courseid, $topic, $studentid)) {
		return false;
	}

	$visibilities = block_exacomp_get_descriptor_visibilities_for_course_and_user($courseid, 0);
	if (isset($visibilities[$descriptor->id]) && !$visibilities[$descriptor->id]) {
		return false;
	}

	if ($studentid > 0) {
		// also check student if set
		$visibilities = block_exacomp_get_descriptor_visibilities_for_course_and_user($courseid, $studentid);
		if (isset($visibilities[$descriptor->id]) && !$visibilities[$descriptor->id]) {
			return false;
		}
	}

	return true;
}

/**
 * visibility for example in course and user context
 * @param unknown $courseid
 * @param unknown $example
 * @param unknown $studentid
 * @return boolean
 */
function block_exacomp_is_example_visible($courseid, $exampleid, $studentid) {
	// $studentid could be BLOCK_EXACOMP_SHOW_ALL_STUDENTS
	if ($studentid <= 0) {
		$studentid = 0;
	}

	// TODO: also need check descriptor? then we also need to check crossdescriptors!
	/*
	if (($topic = \block_exacomp\topic::get($descriptor->topicid)) && !block_exacomp_is_topic_visible($courseid, $topic, $studentid)) {
		return false;
	}
	*/

	$visibilities = block_exacomp_get_example_visibilities_for_course_and_user($courseid, 0);
	if (isset($visibilities[$exampleid->id]) && !$visibilities[$exampleid->id]) {
		return false;
	}

	if ($studentid > 0) {
		// also check student if set
 		$visibilities = block_exacomp_get_example_visibilities_for_course_and_user($courseid, $studentid);
 		if (isset($visibilities[$exampleid->id]) && !$visibilities[$exampleid->id]) {
			return false;
		}
	}

	return true;
}

/**
 * visibility for example solution in course and user context
 * @param unknown $courseid
 * @param unknown $example
 * @param unknown $studentid
 * @return boolean
 */
function block_exacomp_is_example_solution_visible($courseid, $example, $studentid) {
	// $studentid could be BLOCK_EXACOMP_SHOW_ALL_STUDENTS
	if ($studentid <= 0) {
		$studentid = 0;
	}

	$visibilities = block_exacomp_get_solution_visibilities_for_course_and_user($courseid, 0);
	if (isset($visibilities[$example->id]) && !$visibilities[$example->id]) {
		return false;
	}

	if ($studentid > 0) {
		// also check student if set
		$visibilities = block_exacomp_get_solution_visibilities_for_course_and_user($courseid, $studentid);
		if (isset($visibilities[$example->id]) && !$visibilities[$example->id]) {
			return false;
		}
	}

	return true;
}

/**
 * different css classes for student and teacher
 * @param unknown $visible
 * @param unknown $role
 * @return string
 */
function block_exacomp_get_visible_css($visible, $role) {
	$visible_css = '';
	if (!$visible) {
		($role == BLOCK_EXACOMP_ROLE_TEACHER) ? $visible_css = ' rg2-locked' : $visible_css = ' hidden';
	}

	return $visible_css;
}

/**
 * get numbering for descriptor
 * @param unknown $descriptor
 * @return string
 */
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
		} elseif (!empty($descriptor->topicid)) {
			$topic = \block_exacomp\topic::get($descriptor->topicid);
		} else {
			throw new \Exception('topic not found');
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
			$niveaus = g::$DB->get_records(BLOCK_EXACOMP_DB_NIVEAUS);
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
 * get numbering for topic
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

/**
 * get crosssubjectdrafts sorted by subject
 */
function block_exacomp_get_course_cross_subjects_drafts_sorted_by_subjects() {
	$subjects = block_exacomp_get_subjects_by_course(g::$COURSE->id);

	$default_subject = new stdClass();
	$default_subject->id = 0;
	$default_subject->title = block_exacomp_get_string('nocrosssubsub');

	// insert default subject at the front
	array_unshift($subjects, $default_subject);

	foreach ($subjects as $key => $subject) {
		$subject->cross_subject_drafts = block_exacomp\cross_subject::get_objects(array('subjectid' => $subject->id, 'courseid' => 0), 'title');
		if (!$subject->cross_subject_drafts) {
			unset($subjects[$key]);
		}
	}

	return $subjects;
}

/**
 * get crosssubjectdrafts grouped by subject
 * @return unknown
 */
function block_exacomp_get_cross_subjects_grouped_by_subjects() {
	global $DB;

	$subjects = block_exacomp_get_subjects();

	$default_subject = new stdClass();
	$default_subject->id = 0;
	$default_subject->title = block_exacomp_get_string('nocrosssubsub');

	$subjects[0] = $default_subject;

	foreach ($subjects as $subject) {
		$subject->crosssubjects = $DB->get_records_sql('
			SELECT *
			FROM {'.BLOCK_EXACOMP_DB_CROSSSUBJECTS.'}
			WHERE subjectid=? AND courseid>0', [$subject->id]);

		if (!$subject->crosssubjects) {
			// ignore this subject
			unset($subjects[$subject->id]);
		}
	}

	return $subjects;
}

/**
 * delete descriptor created within Exabis Competencies in Moodle
 * @param unknown $descriptorid
 */
function block_exacomp_delete_custom_descriptor($descriptorid) {
	global $DB;

	//delete descriptor evaluation
	$DB->delete_records(BLOCK_EXACOMP_DB_COMPETENCES, array('compid' => $descriptorid, 'comptype' => BLOCK_EXACOMP_TYPE_DESCRIPTOR));

	//delete crosssubject association
	$DB->delete_records(BLOCK_EXACOMP_DB_DESCCROSS, array('descrid' => $descriptorid));

	//delete descriptor
	$DB->delete_records(BLOCK_EXACOMP_DB_DESCRIPTORS, array('id' => $descriptorid));

}

/**
 * returns statistic according to new grading possibilities, only examples directly associated are minded
 * @param unknown $courseid
 * @param unknown $descrid
 * @param unknown $studentid
 * @param number $crosssubjid
 * @return number[]|number[][]
 */
function block_exacomp_get_example_statistic_for_descriptor($courseid, $descrid, $studentid, $crosssubjid = 0) {
	global $DB;

	//get descriptor from id
	$descriptor = $DB->get_record(BLOCK_EXACOMP_DB_DESCRIPTORS, array("id" => $descrid));
	//get examples for descriptor
	$descriptor = block_exacomp_get_examples_for_descriptor($descriptor, array(BLOCK_EXACOMP_SHOW_ALL_TAXONOMIES), true, $courseid);

	//check if descriptor is associated if crosssubject is given - if not examples are not included in crosssubject
	$crosssubjdescriptos = array();
	if ($crosssubjid > 0) {
		$crosssubjdescriptos = block_exacomp_get_descriptors_for_cross_subject($courseid, $crosssubjid);
	}

	if ($studentid > 0) {
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
	$scheme_items = \block_exacomp\global_config::get_teacher_eval_items($courseid);
	$evaluationniveau_items = \block_exacomp\global_config::get_evalniveaus();

	if (block_exacomp_use_eval_niveau()) {
		foreach ($evaluationniveau_items as $niveaukey => $niveauitem) {
			$gradings[$niveaukey] = array();
			foreach ($scheme_items as $schemekey => $schemetitle) {
				$gradings[$niveaukey][$schemekey] = 0;
			}
		}
	} else {
		foreach ($scheme_items as $key => $title) {
			$gradings[$key] = 0;
		}
	}

	$totalgrade = 0;

	//calculate statistic
	if ($crosssubjid == 0 || array_key_exists($descriptor->id, $crosssubjdescriptos)) {
		$total = count($descriptor->examples);

		foreach ($descriptor->examples as $example) {
			//count visible examples for this student
			if (block_exacomp_is_example_visible($courseid, $example, $studentid)) {
				$visible++;
			}

			//check if inwork
			$schedule = $DB->record_exists(BLOCK_EXACOMP_DB_SCHEDULE, array('courseid' => $courseid, 'exampleid' => $example->id, 'studentid' => $studentid));
			if ($schedule) {
				$inwork++;
			}

			if ($studentid > 0) { //no meaningful numbers if studentid > 0
				//submission made?
				$submission_exists = false;
				if (block_exacomp_exaportexists()) {
					$sql = "SELECT * FROM {".BLOCK_EXACOMP_DB_ITEMEXAMPLE."} ie JOIN {".'block_exaportitem'."} i ON ie.itemid = i.id ".
						"WHERE ie.exampleid = ? AND i.userid = ? AND i.courseid = ?";
					$records = $DB->get_records_sql($sql, array($example->id, $studentid, $courseid));
					if ($records) {
						$submission_exists = true;
					}
				}

				$teacher_eval_exists = false;
				$sql = "SELECT * FROM {".BLOCK_EXACOMP_DB_EXAMPLEEVAL."} WHERE courseid = ? AND exampleid = ? AND studentid=? AND teacher_evaluation>=0";
				$records = $DB->get_records_sql($sql, array($courseid, $example->id, $studentid));
				if ($records) {
					$teacher_eval_exists = true;
				}

				$student_eval_exists = false;
				$sql = "SELECT * FROM {".BLOCK_EXACOMP_DB_EXAMPLEEVAL."} WHERE courseid = ? AND exampleid = ? AND studentid = ? AND student_evaluation>=0";
				$records = $DB->get_records_sql($sql, array($courseid, $example->id, $studentid));
				if ($records) {
					$student_eval_exists = true;
				}

				if ($submission_exists || $teacher_eval_exists || $student_eval_exists) {
					$edited++;
				}

				if ($teacher_eval_exists || $student_eval_exists) {
					$evaluated++;
				}

				//create grading statistic
				if (block_exacomp_use_eval_niveau()) {
					if (isset($student->examples->teacher[$example->id]) && isset($student->examples->niveau[$example->id])) {
						$gradings[$student->examples->niveau[$example->id]][$student->examples->teacher[$example->id]]++;
					}
				} else {
					if (isset($student->examples->teacher[$example->id])) {
						$gradings[$student->examples->teacher[$example->id]]++;
					}
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
function block_exacomp_get_file_url($item, $type, $context = null) {
	// get from filestorage
	$file = block_exacomp_get_file($item, $type);

	if (!$file) {
		return null;
	}

	return block_exacomp_get_url_for_file($file, $context);
}

/**
 * @param stored_file $file
 * @return moodle_url
 */
function block_exacomp_get_url_for_file($file, $context = null) {
	$context = block_exacomp_get_context_from_courseid($context);

	$url = moodle_url::make_pluginfile_url($context->id, $file->get_component(), $file->get_filearea(),
		$file->get_itemid(), $file->get_filepath(), $file->get_filename());

	return $url;
}

/**
 * get all examples for pool in student and course context
 * @param unknown $studentid
 * @param unknown $courseid
 */
function block_exacomp_get_examples_for_pool($studentid, $courseid) {
	global $DB;

	if (date('w', time()) == 1) {
		$beginning_of_week = strtotime('Today', time());
	} else {
		$beginning_of_week = strtotime('last Monday', time());
	}

	$sql = "select s.*,
				e.title, e.id as exampleid, e.source AS example_source, evis.visible,
				eval.student_evaluation, eval.teacher_evaluation, eval.evalniveauid, evis.courseid, s.id as scheduleid,
				e.externalurl, e.externaltask, e.description
			FROM {block_exacompschedule} s
			JOIN {block_exacompexamples} e ON e.id = s.exampleid
			JOIN {".BLOCK_EXACOMP_DB_EXAMPVISIBILITY."} evis ON evis.exampleid= e.id AND evis.studentid=0 AND evis.visible = 1 AND evis.courseid=?
			LEFT JOIN {block_exacompexameval} eval ON eval.exampleid = s.exampleid AND eval.studentid = s.studentid AND eval.courseid = s.courseid
			WHERE s.studentid = ? AND s.deleted = 0 AND s.is_pps = 0 AND (
				-- noch nicht auf einen tag geleg
				(s.start IS null OR s.start=0)
				-- oder auf einen tag der vorwoche gelegt und noch nicht evaluiert
				OR (s.start < ? AND (eval.teacher_evaluation IS NULL OR eval.teacher_evaluation=0))
			)
			ORDER BY s.id";

	return $DB->get_records_sql($sql, array($courseid, $studentid, $beginning_of_week));
}

/**
 * get all examples located in trash for student and course context
 * @param unknown $studentid
 * @param unknown $courseid
 */
function block_exacomp_get_examples_for_trash($studentid, $courseid) {
	global $DB;

	$sql = "select s.*,
				e.title, e.id as exampleid, e.source AS example_source, evis.visible,
				eval.student_evaluation, eval.teacher_evaluation, eval.evalniveauid, evis.courseid, s.id as scheduleid
			FROM {block_exacompschedule} s
			JOIN {block_exacompexamples} e ON e.id = s.exampleid
			JOIN {".BLOCK_EXACOMP_DB_EXAMPVISIBILITY."} evis ON evis.exampleid= e.id AND evis.studentid=0 AND evis.visible = 1 AND evis.courseid=?
			LEFT JOIN {block_exacompexameval} eval ON eval.exampleid = s.exampleid AND eval.studentid = s.studentid
			WHERE s.studentid = ? AND s.deleted = 1 AND (
				-- noch nicht auf einen tag geleg
				(s.start IS null OR s.start=0)
			)
			ORDER BY s.id";

	return $DB->get_records_sql($sql, array($courseid, $studentid));
}

/**
 * with start and end != 0 move example to learning calendar, otherwise if deleted = 0 to pool, or delted = 1 to trash
 * @param unknown $scheduleid
 * @param unknown $start
 * @param unknown $end
 * @param number $deleted
 */
function block_exacomp_set_example_start_end($scheduleid, $start, $end, $deleted = 0) {
	global $DB, $USER;

	$entry = $DB->get_record(BLOCK_EXACOMP_DB_SCHEDULE, array('id' => $scheduleid));
	$entry->start = $start;
	$entry->end = $end;
	$entry->deleted = $deleted;

	if ($entry->studentid != $USER->id) {
		block_exacomp_require_teacher($entry->courseid);
	}

	if ($DB instanceof pgsql_native_moodle_database) {
		// HACK: because moodle doesn't quote pgsql identifiers and pgsql doesn't allow end as column name
		$DB->execute('UPDATE {'.BLOCK_EXACOMP_DB_SCHEDULE.'} SET "end"=? WHERE id=?', [$entry->end, $entry->id]);
		unset($entry->end);
	}

	$DB->update_record(BLOCK_EXACOMP_DB_SCHEDULE, $entry);
}

/**
 * copy example from calendar to pool
 * @param unknown $scheduleid
 */
function block_exacomp_copy_example_from_schedule($scheduleid) {
	global $DB, $USER;

	$entry = $DB->get_record(BLOCK_EXACOMP_DB_SCHEDULE, array('id' => $scheduleid));
	if ($entry->studentid != $USER->id) {
		block_exacomp_require_teacher($entry->courseid);
	}

	unset($entry->id);
	unset($entry->start);
	unset($entry->end);

	$DB->insert_record(BLOCK_EXACOMP_DB_SCHEDULE, $entry);
}

/**
 * remove example from schedule
 * @param unknown $scheduleid
 */
function block_exacomp_remove_example_from_schedule($scheduleid) {
	global $DB, $USER;

	$entry = $DB->get_record(BLOCK_EXACOMP_DB_SCHEDULE, array('id' => $scheduleid));

	if ($entry->studentid != $USER->id) {
		block_exacomp_require_teacher($entry->courseid);
	}

	$DB->delete_records(BLOCK_EXACOMP_DB_SCHEDULE, array('id' => $scheduleid));
}

/**
 * get examples for calendar time slot
 * @param unknown $courseid
 * @param unknown $studentid
 * @param unknown $start
 * @param unknown $end
 */
function block_exacomp_get_examples_for_start_end($courseid, $studentid, $start, $end) {
	global $DB;

	$sql = "select s.*,
				e.title, e.id as exampleid, e.source AS example_source, evis.visible,
				eval.student_evaluation, eval.teacher_evaluation, eval.evalniveauid, s.courseid, s.id as scheduleid,
				e.externalurl, e.externaltask, e.description, evalniveau.title as niveau
			FROM {block_exacompschedule} s
			JOIN {block_exacompexamples} e ON e.id = s.exampleid
			JOIN {".BLOCK_EXACOMP_DB_EXAMPVISIBILITY."} evis ON evis.exampleid= e.id AND evis.studentid=0 AND evis.visible = 1 AND evis.courseid=?
			LEFT JOIN {block_exacompexameval} eval ON eval.exampleid = s.exampleid AND eval.studentid = s.studentid 
			LEFT JOIN {block_exacompeval_niveau} evalniveau ON evalniveau.id = eval.evalniveauid
			WHERE s.studentid = ? AND s.courseid = ? AND (
				-- innerhalb end und start
				(s.start > ? AND s.end < ?)
			)
			-- GROUP BY s.id -- because a bug somewhere causes duplicate rows
			ORDER BY e.title";

	return $DB->get_records_sql($sql, array($courseid, $studentid, $courseid, $start, $end));
}

/**
 * examples from all courses are shown in calendar
 * @param unknown $studentid
 * @param unknown $start
 * @param unknown $end
 * @return unknown[]
 */
function block_exacomp_get_examples_for_start_end_all_courses($studentid, $start, $end) {
	if ($studentid < 0) {
		$studentid = 0;
	}

	$courses = block_exacomp_get_courseids();
	$examples = array();
	foreach ($courses as $course) {
		$course_examples = block_exacomp_get_examples_for_start_end($course, $studentid, $start, $end);
		foreach ($course_examples as $example) {
			if (!array_key_exists($example->scheduleid, $examples)) {
				$examples[$example->scheduleid] = $example;
			}
		}
	}

	return $examples;
}

/**
 * needed for communication with fullcalendar
 * @param unknown $examples
 * @param string $mind_eval
 * @return NULL[][]|string[][]
 */
function block_exacomp_get_json_examples($examples, $mind_eval = true) {
	global $OUTPUT, $DB, $CFG, $USER, $PAGE;
	$output = block_exacomp_get_renderer();

	$array = array();
	foreach ($examples as $example) {
		$example_array = array();
		$example_array['id'] = $example->scheduleid;
		$example_array['title'] = $example->title;
		$example_array['start'] = $example->start;
		$example_array['end'] = $example->end;
		$example_array['exampleid'] = $example->exampleid;
		$example_array['niveau'] = isset($example->niveau) ? $example->niveau : null;
		$example_array['description'] = isset($example->description) ? $example->description : "";


		if ($mind_eval) {
			$example_array['student_evaluation'] = $example->student_evaluation;
			$example_array['teacher_evaluation'] = $example->teacher_evaluation;

			$student_title = \block_exacomp\global_config::get_student_eval_title_by_id($example->student_evaluation);
			$teacher_title = \block_exacomp\global_config::get_teacher_eval_title_by_id($example->teacher_evaluation);

			$example_array['student_evaluation_title'] = (strcmp($student_title, ' ') == 0) ? '-' : $student_title;
			$example_array['teacher_evaluation_title'] = (strcmp($teacher_title, ' ') == 0) ? '-' : $teacher_title;
		}
		if (isset($example->state)) {
			$example_array['state'] = $example->state;
		}

		$example_array['studentid'] = $example->studentid;
		$example_array['courseid'] = $example->courseid;
		$example_array['scheduleid'] = $example->scheduleid;
		$example_array['copy_url'] = $output->local_pix_icon("copy_example.png", block_exacomp_get_string('copy'));

		$img = html_writer::empty_tag('img', array('src' => new moodle_url('/blocks/exacomp/pix/assoc_icon.png'), 'alt' => block_exacomp_get_string("competence_associations"), 'title' => block_exacomp_get_string("competence_associations"), 'height' => 16, 'width' => 16));

		$example_array['assoc_url'] = html_writer::link(
			new moodle_url('/blocks/exacomp/competence_associations.php', array("courseid" => $example->courseid, "exampleid" => $example->exampleid, "editmode" => 0)),
			$img, array("target" => "_blank", "onclick" => "window.open(this.href,this.target,'width=880,height=660, scrollbars=yes'); return false;"));

		if ($url = block_exacomp_get_file_url($example, 'example_solution')) {
			$example_array['solution'] = html_writer::link($url, $OUTPUT->pix_icon("e/fullpage", block_exacomp_get_string('solution')), array("target" => "_blank"));
		}
		if (block_exacomp_exaportexists()) {
			if ($USER->id == $example->studentid) {
				$itemExists = block_exacomp_get_current_item_for_example($USER->id, $example->exampleid);

				$example_array['submission_url'] = html_writer::link(
					new moodle_url('/blocks/exacomp/example_submission.php', array("courseid" => $example->courseid, "exampleid" => $example->exampleid)),
					html_writer::empty_tag('img', array('src' => new moodle_url('/blocks/exacomp/pix/'.((!$itemExists) ? 'manual_item.png' : 'reload.png')), 'alt' => block_exacomp_get_string("submission"), 'title' => block_exacomp_get_string("submission"))),
					array("target" => "_blank", "onclick" => "window.open(this.href,this.target,'width=880,height=660, scrollbars=yes'); return false;"));
			} else {
				$url = block_exacomp_get_viewurl_for_example($example->studentid, $USER->id, $example->exampleid);
				if ($url) {
					$example_array['submission_url'] = html_writer::link($url, html_writer::empty_tag('img', array('src' => new moodle_url('/blocks/exacomp/pix/manual_item.png'), 'alt' => block_exacomp_get_string("submission"), 'title' => block_exacomp_get_string("submission"))), array(
						"target" => "_blank",
						"onclick" => "window.open(this.href,this.target,'width=880,height=660, scrollbars=yes'); return false;",
					));
				}
			}
		}
		if ($url = block_exacomp_get_file_url((object)array('id' => $example->exampleid), 'example_task')) {
			$example_array['task'] = html_writer::link($url, $output->preview_icon(), array("target" => "_blank"));
		} elseif (isset($example->externalurl)) {
			$example_array['externalurl'] = html_writer::link(str_replace('&amp;', '&', $example->externalurl), $output->preview_icon(), array("target" => "_blank"));
		} elseif (isset($example->externaltask)) {
			$example_array['externaltask'] = html_writer::link(str_replace('&amp;', '&', $example->externaltask), $output->preview_icon(), array("target" => "_blank"));
		}

		$course_info = $DB->get_record('course', array('id' => $example->courseid));
		$example_array['courseinfo'] = $course_info->shortname;

		$array[] = $example_array;
	}

	return $array;
}

/**
 * needed for communication with fullcalendar
 * @param unknown $date
 * @return string[][]|number[][]|unknown[][]
 */
function block_exacomp_build_json_time_slots($date = null) {

	$units = (get_config("exacomp", "scheduleunits")) ? get_config("exacomp", "scheduleunits") : 8;
	$interval = (get_config("exacomp", "scheduleinterval")) ? get_config("exacomp", "scheduleinterval") : 50;
	$time = (get_config("exacomp", "schedulebegin")) ? get_config("exacomp", "schedulebegin") : "07:45";

	list($h, $m) = explode(":", $time);
	$secTime = $h * 3600 + $m * 60;

	$slots = array();

	$timeentries = block_exacomp_get_timetable_entries();
	/*
	 * Split every unit into 4 pieces
	 */
	for ($i = 0; $i < $units * 4; $i++) {

		$entry = array();

		//only write at the begin of every unit
		if ($i % 4 == 0) {
			$entry['name'] = ($i / 4 + 1).'. Einheit';
			$entry['time'] = (isset($timeentries[$i / 4])) ? $timeentries[$i / 4] : '';
		} else {
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

/**
 * map timestamp to values readable for fullcalendar
 * @param unknown $secTime
 * @return string
 */
function block_exacomp_parse_seconds_to_timestring($secTime) {
	$hours = floor($secTime / 3600);
	$mins = floor(($secTime - ($hours * 3600)) / 60);

	return sprintf('%02d', $hours).":".sprintf('%02d', $mins);
}

/**
 * get state for example
 * @param unknown $courseid
 * @param unknown $exampleid
 * @param unknown $studentid
 * @return BLOCK_EXACOMP_EXAMPLE_STATE_LOCKED_TIME|BLOCK_EXACOMP_EXAMPLE_STATE_EVALUATED_NEGATIV|BLOCK_EXACOMP_EXAMPLE_STATE_EVALUATED_POSITIV|BLOCK_EXACOMP_EXAMPLE_STATE_SUBMITTED|BLOCK_EXACOMP_EXAMPLE_STATE_IN_CALENDAR|BLOCK_EXACOMP_EXAMPLE_STATE_IN_POOL|BLOCK_EXACOMP_EXAMPLE_STATE_NOT_SET
 */
function block_exacomp_get_dakora_state_for_example($courseid, $exampleid, $studentid) {
	global $DB;
	//state 0 = never used in weekly schedule, no evaluation
	//state 1 = planned to work with example -> example is in pool, but no
	//state 2 = example is in work -> in calendar
	//state 3 = submission for example / example closed (for submission no file upload etc is necessary) -> closed
	//state 4 = evaluated -> only from teacher exacomp evaluation nE
	//state 5 = evaluated -> only from teacher exacomp evaluation > nE
	//state 9 = locked time

	$example = $DB->get_record(BLOCK_EXACOMP_DB_EXAMPLES, array('id' => $exampleid));
	if ($example->blocking_event) {
		return BLOCK_EXACOMP_EXAMPLE_STATE_LOCKED_TIME;
	}

	$comp = $DB->get_record(BLOCK_EXACOMP_DB_EXAMPLEEVAL, array('courseid' => $courseid, 'exampleid' => $exampleid, 'studentid' => $studentid));

	if ($comp && !$comp->resubmission && $comp->teacher_evaluation !== null) {
		if ($comp->teacher_evaluation == 0) {
			return BLOCK_EXACOMP_EXAMPLE_STATE_EVALUATED_NEGATIV;
		}

		return BLOCK_EXACOMP_EXAMPLE_STATE_EVALUATED_POSITIV;
	}

	if (block_exacomp_exaportexists()) {
		$sql = "select * FROM {block_exacompitemexample} ie
				JOIN {block_exaportitem} i ON i.id = ie.itemid
				WHERE ie.exampleid = ? AND i.userid = ?";

		$items_examp = $DB->get_records_sql($sql, array($exampleid, $studentid));

		// $comp->student_evaluation can also be 0 = "O/A".
		// if student has an self-evaluation, and that self-evaluation was after the teacher evaluation (or teacher hasn't evaluated yet)
		// then the example is submitted
		if ($items_examp || ($comp && $comp->student_evaluation !== null && $comp->student_evaluation >= 0 && $comp->timestamp_student > $comp->timestamp_teacher)) {
			return BLOCK_EXACOMP_EXAMPLE_STATE_SUBMITTED;
		}
	}

	$schedule = $DB->get_records(BLOCK_EXACOMP_DB_SCHEDULE, array('courseid' => $courseid, 'exampleid' => $exampleid, 'studentid' => $studentid));

	if ($schedule) {
		$in_work = false;
		foreach ($schedule as $entry) {
			if ($entry->start > 0 && $entry->end > 0) {
				$in_work = true;
			}
		}

		if ($in_work) {
			return BLOCK_EXACOMP_EXAMPLE_STATE_IN_CALENDAR;
		} else {
			return BLOCK_EXACOMP_EXAMPLE_STATE_IN_POOL;
		}
	}

	return BLOCK_EXACOMP_EXAMPLE_STATE_NOT_SET;
}

/**
 * check if example already contained in pre planning storage
 * @param unknown $exampleid
 * @param unknown $creatorid
 * @param unknown $courseid
 * @return boolean
 */
function block_exacomp_in_pre_planing_storage($exampleid, $creatorid, $courseid) {
	global $DB;

	if ($DB->get_record(BLOCK_EXACOMP_DB_SCHEDULE, array('exampleid' => $exampleid, 'creatorid' => $creatorid, 'courseid' => $courseid, 'studentid' => 0))) {
		return true;
	}

	return false;
}

/**
 * check if pre planning storage contains any examples
 * @param unknown $creatorid
 * @param unknown $courseid
 */
function block_exacomp_has_items_pre_planning_storage($creatorid, $courseid) {
	global $DB;

	return $DB->get_records(BLOCK_EXACOMP_DB_SCHEDULE, array('creatorid' => $creatorid, 'courseid' => $courseid, 'studentid' => 0));
}

/**
 * return pre-planning storage
 * @param unknown $creatorid
 * @param unknown $courseid
 */
function block_exacomp_get_pre_planning_storage($creatorid, $courseid) {
	global $DB;

	$sql = "select s.*,
				e.title, e.id as exampleid, e.source AS example_source, evis.visible,
				evis.courseid, s.id as scheduleid
			FROM {".BLOCK_EXACOMP_DB_SCHEDULE."} s
			JOIN {".BLOCK_EXACOMP_DB_EXAMPLES."} e ON e.id = s.exampleid
			JOIN {".BLOCK_EXACOMP_DB_EXAMPVISIBILITY."} evis ON evis.exampleid= e.id AND evis.studentid=0 AND evis.visible = 1 AND evis.courseid=?
			WHERE s.creatorid = ? AND s.studentid=0 AND s.is_pps = 1 AND (
				-- noch nicht auf einen tag geleg
				(s.start IS null OR s.start=0)
			)
			ORDER BY s.id";

	return $DB->get_records_sql($sql, array($courseid, $creatorid));
}

/**
 * needed to mark if student has already any examples available in pre-planning storage in his/her pool
 * @param unknown $students
 * @param unknown $courseid
 * @return unknown
 */
function block_exacomp_get_student_pool_examples($students, $courseid) {
	foreach ($students as $student) {
		$student->pool_examples = block_exacomp_get_examples_for_pool($student->id, $courseid);
	}

	return $students;
}

/**
 * change example order: move up
 * @param unknown $exampleid
 * @param unknown $descrid
 * @return boolean
 */
function block_exacomp_example_up($exampleid, $descrid) {
	return block_exacomp_example_order($exampleid, $descrid, "<");
}

/**
 * change example order: move down
 * @param unknown $exampleid
 * @param unknown $descrid
 * @return boolean
 */
function block_exacomp_example_down($exampleid, $descrid) {
	return block_exacomp_example_order($exampleid, $descrid, ">");
}

/**
 * change example order in database to persist
 * @param unknown $exampleid
 * @param unknown $descrid
 * @param string $operator
 * @return boolean
 */
function block_exacomp_example_order($exampleid, $descrid, $operator = "<") {
	global $DB, $USER, $COURSE;

	$example = \block_exacomp\example::get($exampleid);
	if (!$example || !$DB->record_exists(BLOCK_EXACOMP_DB_DESCEXAMP, array('exampid' => $exampleid, 'descrid' => $descrid))) {
		return false;
	}

	$desc_examp = $DB->get_record(BLOCK_EXACOMP_DB_DESCEXAMP, array('exampid' => $exampleid, 'descrid' => $descrid));
	$example->descsorting = $desc_examp->sorting;

	if (block_exacomp_require_item_capability(BLOCK_EXACOMP_CAP_SORTING, $example)) {
		$sql = 'SELECT e.*, de.sorting as descsorting FROM {block_exacompexamples} e
			JOIN {block_exacompdescrexamp_mm} de ON de.exampid = e.id
			WHERE de.sorting '.((strcmp($operator, "<") == 0) ? "<" : ">").' ? AND de.descrid = ?
			ORDER BY de.sorting '.((strcmp($operator, "<") == 0) ? "DESC" : "ASC").'
			LIMIT 1';

		$switchWith = $DB->get_record_sql($sql, array($example->descsorting, $descrid));

		if ($switchWith) {
			$oldSorting = ($example->descsorting) ? $example->descsorting : 0;

			$example->descsorting = ($switchWith->descsorting) ? $switchWith->descsorting : 0;
			$switchWith->descsorting = $oldSorting;

			$desc_examp->sorting = $example->descsorting;
			$DB->update_record(BLOCK_EXACOMP_DB_DESCEXAMP, $desc_examp);

			$desc_examp = $DB->get_record(BLOCK_EXACOMP_DB_DESCEXAMP, array('exampid' => $switchWith->id, 'descrid' => $descrid));
			$desc_examp->sorting = $switchWith->descsorting;
			$DB->update_record(BLOCK_EXACOMP_DB_DESCEXAMP, $desc_examp);

			return true;
		}
	}

	return false;
}

/**
 * remove examples from pre-planning storage
 * @param unknown $courseid
 */
function block_exacomp_empty_pre_planning_storage($courseid) {
	global $DB;

	$DB->delete_records(BLOCK_EXACOMP_DB_SCHEDULE, array('courseid' => $courseid, 'studentid' => 0));
}

/**
 * get current exaport item for example -> this is example submission
 * @param unknown $userid
 * @param unknown $exampleid
 */
function block_exacomp_get_current_item_for_example($userid, $exampleid) {
	global $DB;

	$sql = 'SELECT i.*, ie.status, ie.teachervalue, ie.studentvalue FROM {block_exacompexamples} e
			JOIN {block_exacompitemexample} ie ON ie.exampleid = e.id
			JOIN {block_exaportitem} i ON ie.itemid = i.id
			WHERE e.id = ?
			AND i.userid = ?
			ORDER BY ie.timecreated DESC
			LIMIT 1';

	return $DB->get_record_sql($sql, array($exampleid, $userid));
}

/**
 * keeps selected studentid in the session
 */
function block_exacomp_get_studentid() {
	if (!block_exacomp_is_teacher()) {
		return g::$USER->id;
	}

	$studentid = optional_param('studentid', BLOCK_EXACOMP_DEFAULT_STUDENT, PARAM_INT);

	if ($studentid == BLOCK_EXACOMP_DEFAULT_STUDENT) {
		if (isset($_SESSION['studentid-'.g::$COURSE->id])) {
			$studentid = $_SESSION['studentid-'.g::$COURSE->id];
		} else {
			$studentid = BLOCK_EXACOMP_SHOW_ALL_STUDENTS;
		}
	} else {
		$_SESSION['studentid-'.g::$COURSE->id] = $studentid;
	}

	return $studentid;
}

/**
 * get message icon for communication between students and teacher
 * @param unknown $userid
 */
function block_exacomp_get_message_icon($userid) {
	global $DB, $CFG, $COURSE;

	if ($userid != BLOCK_EXACOMP_SHOW_ALL_STUDENTS) {
		require_once($CFG->dirroot.'/message/lib.php');

		$userto = $DB->get_record('user', array('id' => $userid));

		if (!$userto) {
			return;
		}

		if (function_exists('message_messenger_requirejs')) {
			// before moodle 3.3
			message_messenger_requirejs();
			$url = new moodle_url('message/index.php', array('id' => $userto->id));
			$attributes = message_messenger_sendmessage_link_params($userto);

			return html_writer::link($url, html_writer::tag('button', html_writer::img(new moodle_url('/blocks/exacomp/pix/envelope.png'), block_exacomp_get_string('message', 'message'), array('title' => fullname($userto)))), $attributes);
		} else {
			$url = new moodle_url('/message/index.php', array('id' => $userto->id));
			$attributes = ['target' => '_blank', 'title' => fullname($userto)];

			return html_writer::link($url, html_writer::img(new moodle_url('/blocks/exacomp/pix/envelope.png'), block_exacomp_get_string('message', 'message')), $attributes);
			$anchortagcontents = '<img class="iconsmall" src="'.$OUTPUT->pix_url('t/message').'" alt="'.get_string('messageselectadd').'" />';
			$anchorurl = new moodle_url('/message/index.php', array('id' => $user->id));
			$anchortag = html_writer::link($anchorurl, $anchortagcontents,
				array('title' => get_string('messageselectadd')));

			$this->content->text .= '<div class="message">'.$anchortag.'</div>';
		}
	} else {
		$attributes = array(
			'exa-type' => 'iframe-popup',
			'href' => new moodle_url('message_to_course.php', array('courseid' => $COURSE->id)),
			'exa-width' => '340px',
			'exa-height' => '340px',
		);

		return html_writer::tag('button',
			html_writer::img(new moodle_url('/blocks/exacomp/pix/envelope.png'), block_exacomp_get_string('message', 'message'), array('title' => block_exacomp_get_string('messagetocourse'))),
			$attributes);
	}
}

/**
 * send notification to user
 * @param unknown $notificationtype
 * @param unknown $userfrom
 * @param unknown $userto
 * @param unknown $subject
 * @param unknown $message
 * @param unknown $context
 * @param unknown $contexturl
 */
function block_exacomp_send_notification($notificationtype, $userfrom, $userto, $subject, $message, $context, $contexturl) {
	global $CFG, $DB;

	if (!get_config('exacomp', 'notifications')) {
		return;
	}

	// do not send too many notifications. therefore check if user has got same notification within the last 5 minutes
	if ($DB->get_records_select('message_read', "useridfrom = ? AND useridto = ? AND contexturlname = ? AND timecreated > ?",
		array('useridfrom' => $userfrom->id, 'useridto' => $userto->id, 'contexturlname' => $context, (time() - 5 * 60)))
	) {
		return;
	}

	require_once($CFG->dirroot.'/message/lib.php');

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

	message_send($eventdata);
}

/**
 * send specific notification: submission made
 * @param unknown $userfrom
 * @param unknown $userto
 * @param unknown $example
 * @param unknown $date
 * @param unknown $time
 * @param unknown $courseid
 */
function block_exacomp_send_submission_notification($userfrom, $userto, $example, $date, $time, $courseid) {
	global $CFG, $USER, $SITE;

	$subject = block_exacomp_get_string('notification_submission_subject', null, array('site' => $SITE->fullname, 'student' => fullname($userfrom), 'example' => $example->title));

	$gridurl = block_exacomp_get_gridurl_for_example($courseid, $userto->id, $example->id);

	$message = block_exacomp_get_string('notification_submission_body', null, array('student' => fullname($userfrom), 'example' => $example->title, 'date' => $date, 'time' => $time, 'viewurl' => $gridurl, 'receiver' => fullname($userto), 'site' => $SITE->fullname));
	$context = block_exacomp_get_string('notification_submission_context');

	block_exacomp_send_notification("submission", $userfrom, $userto, $subject, $message, $context, $gridurl);
}

/**
 * send specific notification to all course teachers: submission made
 * @param unknown $courseid
 * @param unknown $exampleid
 * @param unknown $timecreated
 */
function block_exacomp_notify_all_teachers_about_submission($courseid, $exampleid, $timecreated) {
	global $USER, $DB;

	$teachers = block_exacomp_get_teachers_by_course($courseid);
	if ($teachers) {
		foreach ($teachers as $teacher) {
			block_exacomp_send_submission_notification($USER, $teacher, $DB->get_record(BLOCK_EXACOMP_DB_EXAMPLES, array('id' => $exampleid)), date("D, d.m.Y", $timecreated), date("H:s", $timecreated), $courseid);
		}
	}
}

/**
 * send specific notification: new student evaluation available
 * @param unknown $userfrom
 * @param unknown $userto
 * @param unknown $courseid
 */
function block_exacomp_send_self_assessment_notification($userfrom, $userto, $courseid) {
	global $SITE;

	$course = get_course($courseid);

	$subject = block_exacomp_get_string('notification_self_assessment_subject', null, array('site' => $SITE->fullname, 'course' => $course->shortname));
	$message = block_exacomp_get_string('notification_self_assessment_body', null, array('course' => $course->fullname, 'student' => fullname($userfrom), 'receiver' => fullname($userto), 'site' => $SITE->fullname));
	$context = block_exacomp_get_string('notification_self_assessment_context');

	$viewurl = new moodle_url('/blocks/exacomp/assign_competencies.php', array('courseid' => $courseid));

	block_exacomp_send_notification("self_assessment", $userfrom, $userto, $subject, $message, $context, $viewurl);
}

/**
 * send specific notification to all course teachers: new student evaluation available
 * @param unknown $courseid
 */
function block_exacomp_notify_all_teachers_about_self_assessment($courseid) {
	global $USER, $DB;

	$teachers = block_exacomp_get_teachers_by_course($courseid);
	if ($teachers) {
		foreach ($teachers as $teacher) {
			block_exacomp_send_self_assessment_notification($USER, $teacher, $courseid);
		}
	}
}

/**
 * send specific notification: new evaluation available
 * @param unknown $userfrom
 * @param unknown $userto
 * @param unknown $courseid
 */
function block_exacomp_send_grading_notification($userfrom, $userto, $courseid) {
	global $CFG, $USER, $SITE;

	$course = get_course($courseid);

	$subject = block_exacomp_get_string('notification_grading_subject', null, array('site' => $SITE->fullname, 'course' => $course->shortname));
	$message = block_exacomp_get_string('notification_grading_body', null, array('course' => $course->fullname, 'teacher' => fullname($userfrom), 'receiver' => fullname($userto), 'site' => $SITE->fullname));
	$context = block_exacomp_get_string('notification_grading_context');

	$viewurl = new moodle_url('/blocks/exacomp/assign_competencies.php', array('courseid' => $courseid));

	block_exacomp_send_notification("grading", $userfrom, $userto, $subject, $message, $context, $viewurl);
}

/**
 * send specific notification to all course students: new evaluation available
 * @param unknown $courseid
 * @param unknown $students
 */
function block_exacomp_notify_students_about_grading($courseid, $students) {
	global $USER, $DB;

	if ($students) {
		foreach ($students as $student) {
			block_exacomp_send_grading_notification($USER, $DB->get_record('user', array('id' => $student)), $courseid);
		}
	}
}

/**
 * send specific notification: new example on weekly schedule
 * @param unknown $userfrom
 * @param unknown $userto
 * @param unknown $courseid
 * @param unknown $exampleid
 */
function block_exacomp_send_weekly_schedule_notification($userfrom, $userto, $courseid, $exampleid) {
	global $CFG, $USER, $DB, $SITE;

	$course = get_course($courseid);
	$example = $DB->get_record(BLOCK_EXACOMP_DB_EXAMPLES, array('id' => $exampleid));
	$subject = block_exacomp_get_string('notification_weekly_schedule_subject', null, array('site' => $SITE->fullname));
	$message = block_exacomp_get_string('notification_weekly_schedule_body', null, array('course' => $course->fullname, 'teacher' => fullname($userfrom), 'receiver' => fullname($userto), 'site' => $SITE->fullname));
	$context = block_exacomp_get_string('notification_weekly_schedule_context');

	$viewurl = new moodle_url('/blocks/exacomp/weekly_schedule.php', array('courseid' => $courseid));

	block_exacomp_send_notification("weekly_schedule", $userfrom, $userto, $subject, $message, $context, $viewurl);
}

/**
 * send specific notification: new example comment
 * @param unknown $userfrom
 * @param unknown $userto
 * @param unknown $courseid
 * @param unknown $exampleid
 */
function block_exacomp_send_example_comment_notification($userfrom, $userto, $courseid, $exampleid) {
	global $CFG, $USER, $DB, $SITE;

	$course = get_course($courseid);
	$example = $DB->get_record(BLOCK_EXACOMP_DB_EXAMPLES, array('id' => $exampleid));
	$subject = block_exacomp_get_string('notification_example_comment_subject', null, array('example' => $example->title, 'site' => $SITE->fullname));
	$message = block_exacomp_get_string('notification_example_comment_body', null, array('course' => $course->fullname, 'teacher' => fullname($userfrom), 'example' => $example->title, 'receiver' => fullname($userto), 'site' => $SITE->fullname));
	$context = block_exacomp_get_string('notification_example_comment_context');

	$viewurl = block_exacomp_get_viewurl_for_example($userto->id, $userto->id, $example->id);

	block_exacomp_send_notification("comment", $userfrom, $userto, $subject, $message, $context, $viewurl);
}

/**
 * only used in backup
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

/**
 * if additional grading is activated, save note for descriptor, topic and subject,
 * former used value field is still used, note is mapped to value
 * @param unknown $courseid
 * @param unknown $descriptorid
 * @param unknown $studentid
 * @param unknown $additionalinfo
 * @param unknown $comptype
 */
function block_exacomp_save_additional_grading_for_comp($courseid, $descriptorid, $studentid, $additionalinfo, $comptype = BLOCK_EXACOMP_TYPE_DESCRIPTOR) {
	global $DB, $USER;

	if (is_string($additionalinfo)) {
		// force additional info to be stored with a dot as decimal mark
		$additionalinfo = (float)str_replace(",", ".", $additionalinfo);
	}

	if ($additionalinfo > 6.0) {
		$additionalinfo = 6.0;
	} elseif ($additionalinfo > 0 && $additionalinfo < 1.0) {
		$additionalinfo = 1.0;
	} elseif ($additionalinfo <= 0) {
		$additionalinfo = null;
	}

	$value = block_exacomp\global_config::get_additionalinfo_value_mapping($additionalinfo);
	$record = block_exacomp_get_comp_eval($courseid, BLOCK_EXACOMP_ROLE_TEACHER, $studentid, $comptype, $descriptorid);


	if ($additionalinfo == '' || empty($additionalinfo)) {
		$additionalinfo = null;
	}

	if(block_exacomp_is_teacher($courseid)){
    	if ($record) {
    		// falls sich die bewertung geändert hat, timestamp neu setzen
    		if ($record->value != $value || $record->additionalinfo != $additionalinfo) {
    			$record->timestamp = time();
    		}
    
    		$record->reviewerid = $USER->id;
    		$record->additionalinfo = $additionalinfo;
    		$record->value = $value;
    
    		$DB->update_record(BLOCK_EXACOMP_DB_COMPETENCES, $record);
    	} else {
    		$insert = new stdClass();
    		$insert->compid = $descriptorid;
    		$insert->userid = $studentid;
    		$insert->courseid = $courseid;
    		$insert->comptype = $comptype;
    		$insert->role = BLOCK_EXACOMP_ROLE_TEACHER;
    		$insert->reviewerid = $USER->id;
    		$insert->timestamp = time();
    
    		$insert->additionalinfo = $additionalinfo;
    		$insert->value = $value;
    		$DB->insert_record(BLOCK_EXACOMP_DB_COMPETENCES, $insert);
    	}   
	}
}

/**
 * get all examples associated with any descriptors in this course
 * @param unknown $courseid
 */
function block_exacomp_get_examples_by_course($courseid) {
	$sql = "SELECT ex.*
		FROM {".BLOCK_EXACOMP_DB_EXAMPLES."} ex
		WHERE ex.id IN (
			SELECT dex.exampid
			FROM {".BLOCK_EXACOMP_DB_DESCEXAMP."} dex
			JOIN {".BLOCK_EXACOMP_DB_DESCTOPICS."} det ON dex.descrid = det.descrid
			JOIN {".BLOCK_EXACOMP_DB_COURSETOPICS."} ct ON det.topicid = ct.topicid
			WHERE ct.courseid = ?
		)";

	return g::$DB->get_records_sql($sql, array($courseid));
}

/**
 * check if any examples are available in course
 * needed for dropdown in weekly schedule
 * @param unknown $courseid
 * @return boolean
 */
function block_exacomp_course_has_examples($courseid) {
	$sql = "SELECT COUNT(*)
		FROM {".BLOCK_EXACOMP_DB_EXAMPLES."} ex
		JOIN {".BLOCK_EXACOMP_DB_DESCEXAMP."} dex ON ex.id = dex.exampid
		JOIN {".BLOCK_EXACOMP_DB_DESCTOPICS."} det ON dex.descrid = det.descrid
		JOIN {".BLOCK_EXACOMP_DB_COURSETOPICS."} ct ON det.topicid = ct.topicid
		WHERE ct.courseid = ?";

	return (bool)g::$DB->get_field_sql($sql, array($courseid));
}

/**
 * send message to all students in course
 * @param unknown $courseid
 * @param unknown $message
 * @throws moodle_exception
 */
function block_exacomp_send_message_to_course($courseid, $message) {
	global $USER;

	require_capability('moodle/site:sendmessage', context_system::instance());
	block_exacomp_require_teacher($courseid);

	$students = block_exacomp_get_students_by_course($courseid);

	foreach ($students as $student) {
		if (empty($student->id) || isguestuser($student->id) || $student->id == $USER->id) {
			continue;
		}

		$messageid = message_post_message($USER, $student, $message, FORMAT_MOODLE);

		if (!$messageid) {
			throw new moodle_exception('errorwhilesendingmessage', 'core_message');
		}

	}
}

/**
 * create an example only available on weekly schedule, to define occupied time slots, currently only for teachers
 * @param unknown $courseid
 * @param unknown $title
 * @param unknown $creatorid
 * @param unknown $studentid
 */
function block_exacomp_create_blocking_event($courseid, $title, $creatorid, $studentid) {
	global $DB;

	$example = new stdClass();
	$example->title = $title;
	$example->creatorid = $creatorid;
	$example->blocking_event = 1;

	$exampleid = $DB->insert_record(BLOCK_EXACOMP_DB_EXAMPLES, $example);

	$schedule = new stdClass();
	$schedule->studentid = $studentid;
	$schedule->exampleid = $exampleid;
	$schedule->creatorid = $creatorid;
	$schedule->courseid = $courseid;

	$scheduleid = $DB->insert_record(BLOCK_EXACOMP_DB_SCHEDULE, $schedule);

	$record = $DB->get_records(BLOCK_EXACOMP_DB_EXAMPVISIBILITY, array('courseid' => $courseid, 'exampleid' => $exampleid, 'studentid' => 0, 'visible' => 1));
	if (!$record) {
		$visibility = new stdClass();
		$visibility->courseid = $courseid;
		$visibility->exampleid = $exampleid;
		$visibility->studentid = 0;
		$visibility->visible = 1;

		$vibilityid = $DB->insert_record(BLOCK_EXACOMP_DB_EXAMPVISIBILITY, $visibility);
	}
}

/**
 * needed to create example over webservice API
 * @param unknown $descriptorid
 */
function block_exacomp_get_courseids_by_descriptor($descriptorid) {
	$sql = 'SELECT ct.courseid
		FROM {'.BLOCK_EXACOMP_DB_COURSETOPICS.'} ct 
		JOIN {'.BLOCK_EXACOMP_DB_DESCTOPICS.'} dt ON ct.topicid = dt.topicid  
		WHERE dt.descrid = ?';

	return g::$DB->get_fieldset_sql($sql, array($descriptorid));
}

function block_exacomp_get_courseids_by_example($exampleid) {
	$sql = 'SELECT ct.courseid
		FROM {'.BLOCK_EXACOMP_DB_COURSETOPICS.'} ct 
		JOIN {'.BLOCK_EXACOMP_DB_DESCTOPICS.'} dt ON ct.topicid = dt.topicid  
		JOIN {'.BLOCK_EXACOMP_DB_DESCEXAMP.'} dex ON dex.descrid = dt.descrid
		WHERE dex.exampid=?';

	return g::$DB->get_fieldset_sql($sql, array($exampleid));
}

/**
 * get evaluation images for competence profile for teacher
 * according to course scheme and admin scheme
 **/
function block_exacomp_get_html_for_niveau_eval($evaluation) {
	$evaluation_niveau_type = block_exacomp_evaluation_niveau_type();
	if ($evaluation_niveau_type == 0) {
		return;
	}

	if ($evaluation_niveau_type == 0) {
		return;
	}

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
	if ($evaluation > -1) {
		if ($evaluation == 1) {
			$image1 = $one_src;
		}
		if ($evaluation == 2) {
			$image2 = $two_src;
		}
		if ($evaluation == 3) {
			$image3 = $three_src;
		}
	}

	return html_writer::empty_tag('img', array('src' => new moodle_url($image1), 'width' => '25', 'height' => '25')).
		html_writer::empty_tag('img', array('src' => new moodle_url($image2), 'width' => '25', 'height' => '25')).
		html_writer::empty_tag('img', array('src' => new moodle_url($image3), 'width' => '25', 'height' => '25'));
}

/**
 * get data for grid table in profile, where topics are listed on horizontal axis, niveaus on vertical
 * and each cell represent descriptor evaluation
 * additionally topic and subject evaluation is also included
 * @param unknown $courseid
 * @param unknown $studentid
 * @param unknown $subjectid
 * @return array{[subject_title, subject_eval, subject_evalniveau, subject_evalniveauid, timestamp,
 *                  content {[topicid] => [topic_evalniveau, topic_evalniveauid, topic_eval, topicid, visible, timestamp, span,
 *                            niveaus {[niveautitle] => [evalniveau, evalniveauid, eval, visible, show, timestamp, span]}
 *                  ]}
 *           ]}
 * where xx_evalniveau is empty if block_exacomp_use_eval_niveau() = false
 *       xx_evalniveauid is -1 is not evaluated and 0 if block_exacomp_use_eval_niveau() = false
 *       xx_eval is additional grading (not mapped!) if block_exacomp_additional_grading() = true
 *               and value if block_exacomp_additional_grading() = false
 *       show = false, if niveau not used within current topic
 *       span = 1 or 0 inidication if niveau is across (übergreifend)
 */
function block_exacomp_get_grid_for_competence_profile($courseid, $studentid, $subjectid) {
	global $DB;
	list($course_subjects, $table_column, $table_header, $selectedSubject, $selectedTopic, $selectedNiveau) = block_exacomp_init_overview_data($courseid, $subjectid, -1, 0, false, block_exacomp_is_teacher(), $studentid);

	$user = $DB->get_record('user', array('id' => $studentid));
	$user = block_exacomp_get_user_information_by_course($user, $courseid);

	$subject = block_exacomp\db_layer_student::create($courseid)->get_subject($subjectid);
	if (!$subject) {
		return;
	}

	$table_content = new stdClass();
	$table_content->content = array();

	$use_evalniveau = block_exacomp_use_eval_niveau();
	$scheme_items = \block_exacomp\global_config::get_teacher_eval_items($courseid);
	$evaluationniveau_items = \block_exacomp\global_config::get_evalniveaus();
	foreach ($subject->topics as $topic) {
		// auswertung pro lfs
		$data = $table_content->content[$topic->id] = block_exacomp_get_grid_for_competence_profile_topic_data($courseid, $studentid, $topic);

		// gesamt für topic
		$data->topic_evalniveauid =
			(($use_evalniveau) ?
				((isset($user->topics->niveau[$topic->id]))
					? $user->topics->niveau[$topic->id] : -1)
				: 0);

		$data->topic_evalniveau = @$evaluationniveau_items[$data->topic_evalniveauid] ?: '';

		$data->topic_eval =
			((block_exacomp_additional_grading()) ?
				((isset($user->topics->teacher_additional_grading[$topic->id]))
					? $user->topics->teacher_additional_grading[$topic->id] : '')
				: ((isset($user->topics->teacher[$topic->id]))
					? $scheme_items[$user->topics->teacher[$topic->id]] : '-1'));

		$data->visible = block_exacomp_is_topic_visible($courseid, $topic, $studentid);
		$data->timestamp = ((isset($user->topics->timestamp_teacher[$topic->id])) ? $user->topics->timestamp_teacher[$topic->id] : 0);
		$data->topic_id = $topic->id;
	}

	$table_content->subject_evalniveau =
		(($use_evalniveau) ?
			((isset($user->subjects->niveau[$subject->id]))
				? @$evaluationniveau_items[$user->subjects->niveau[$subject->id]].' ' : '')
			: '');

	$table_content->subject_evalniveauid = (($use_evalniveau) ?
		((isset($user->subjects->niveau[$subject->id]))
			? $user->subjects->niveau[$subject->id] : -1)
		: 0);

	$table_content->subject_eval = ((block_exacomp_additional_grading()) ?
		((isset($user->subjects->teacher_additional_grading[$subject->id]))
			? $user->subjects->teacher_additional_grading[$subject->id] : '')
		: ((isset($user->subjects->teacher[$subject->id]))
			? $scheme_items[$user->subjects->teacher[$subject->id]] : ''));

	$table_content->timestamp = (isset($user->subjects->timestamp_teacher[$subject->id]))
		? $user->subjects->timestamp_teacher[$subject->id] : '';

	$table_content->subject_title = $subject->title;

	foreach ($table_header as $key => $niveau) {
		if (isset($niveau->span) && $niveau->span == 1) {
			unset($table_header[$key]);
		} elseif ($niveau->id != BLOCK_EXACOMP_SHOW_ALL_NIVEAUS) {
			foreach ($table_content->content as $row) {
				if ($row->span != 1) {
					if (!array_key_exists($niveau->title, $row->niveaus)) {
						$row->niveaus[$niveau->title] = new stdClass();
						$row->niveaus[$niveau->title]->eval = '';
						$row->niveaus[$niveau->title]->evalniveau = '';
						$row->niveaus[$niveau->title]->evalniveauid = ($use_evalniveau) ? -1 : 0;
						$row->niveaus[$niveau->title]->show = false;
						$row->niveaus[$niveau->title]->visible = true;
						$row->niveaus[$niveau->title]->timestamp = 0;
					}
				}
			}
		}
	}

	foreach ($table_content->content as $row) {
		#sort crosssub entries
		ksort($row->niveaus);
	}

	return array($course_subjects, $table_column, $table_header, $table_content);
}

/**
 * @param $courseid
 * @param $studentid
 * @param \block_exacomp\topic $topic
 * @return object
 */
function block_exacomp_get_grid_for_competence_profile_topic_data($courseid, $studentid, $topic) {
	$data = (object)[];
	$data->niveaus = array();
	$data->span = 0;

	$use_evalniveau = block_exacomp_use_eval_niveau();
	$evaluationniveau_items = \block_exacomp\global_config::get_evalniveaus();

	foreach ($topic->descriptors as $descriptor) {
		$evaluation = block_exacomp_get_comp_eval($courseid, BLOCK_EXACOMP_ROLE_TEACHER, $studentid, BLOCK_EXACOMP_TYPE_DESCRIPTOR, $descriptor->id);

		$niveau = \block_exacomp\niveau::get($descriptor->niveauid);
		if (!$niveau) {
			continue;
		}

		$data->niveaus[$niveau->title] = new stdClass();

		if ($use_evalniveau && $evaluation && $evaluation->evalniveauid) {
			$data->niveaus[$niveau->title]->evalniveau = @$evaluationniveau_items[$evaluation->evalniveauid];
			$data->niveaus[$niveau->title]->evalniveauid = $evaluation->evalniveauid ?: -1;
		} else {
			$data->niveaus[$niveau->title]->evalniveau = '';
			$data->niveaus[$niveau->title]->evalniveauid = -1;
		}

		// copy of block_exacomp_get_descriptor_statistic_for_topic()
		$data->niveaus[$niveau->title]->eval =
			(block_exacomp_additional_grading())
				? (($evaluation && $evaluation->additionalinfo) ? $evaluation->additionalinfo : '')
				: (($evaluation && $evaluation->value) ? $scheme_items[$evaluation->value] : -1);

		$data->niveaus[$niveau->title]->show = true;
		$data->niveaus[$niveau->title]->visible = block_exacomp_is_descriptor_visible($courseid, $descriptor, $studentid);
		$data->niveaus[$niveau->title]->timestamp = ((isset($evaluation->timestamp)) ? $evaluation->timestamp : 0);

		if ($niveau->span == 1) {
			$data->span = 1;
		}
	}

	return $data;
}

/**
 * format data to access via WS
 * @param unknown $courseid
 * @param unknown $userid
 * @param unknown $subjectid
 * @return \block_exacomp\stdClass
 * see ws dakora_get_competence_grid_for_profile for return value description
 */
function block_exacomp_get_competence_profile_grid_for_ws($courseid, $userid, $subjectid) {
	global $DB;
	list($course_subjects, $table_rows, $table_header, $table_content) = block_exacomp_get_grid_for_competence_profile($courseid, $userid, $subjectid);

	$spanning_niveaus = $DB->get_fieldset_select(BLOCK_EXACOMP_DB_NIVEAUS, 'title', 'span=?', array(
		1,
	));//calculate the col span for spanning niveaus
	$spanning_colspan = block_exacomp_calculate_spanning_niveau_colspan($table_header, $spanning_niveaus);

	$table = new stdClass();
	$table->rows = array();

	$header_row = new stdClass();
	$header_row->columns = array();

	$header_row->columns[0] = new stdClass();
	$header_row->columns[0]->text = $table_content->subject_title;
	$header_row->columns[0]->span = 0;

	$current_idx = 1;
	foreach ($table_header as $element) {
		if ($element->id != BLOCK_EXACOMP_SHOW_ALL_NIVEAUS) {
			$header_row->columns[$current_idx] = new stdClass();
			$header_row->columns[$current_idx]->text = $element->title;
			$header_row->columns[$current_idx]->span = 0;
			$current_idx++;
		}
	}

	if (block_exacomp_is_topicgrading_enabled()) {
		$topic_eval_header = new stdClass();
		$topic_eval_header->text = block_exacomp_get_string('total');
		$topic_eval_header->span = 0;
		$header_row->columns[$current_idx] = $topic_eval_header;
	}

	$table->rows[] = $header_row;


	foreach ($table_content->content as $topic => $rowcontent) {
		$topic_visibility_check = new stdClass();
		$topic_visibility_check->id = $rowcontent->topic_id;
		$content_row = new stdClass();
		$content_row->columns = array();

		$content_row->columns[0] = new stdClass();
		$content_row->columns[0]->text = block_exacomp_get_topic_numbering($topic)." ".$table_rows[$topic]->title;
		$content_row->columns[0]->span = 0;
		$content_row->columns[0]->visible = $rowcontent->visible;

		$current_idx = 1;
		foreach ($rowcontent->niveaus as $niveau => $element) {
			$content_row->columns[$current_idx] = new stdClass();
			$content_row->columns[$current_idx]->evaluation = (empty($element->eval) || strlen(trim($element->eval)) == 0) ? -1 : $element->eval;
			$content_row->columns[$current_idx]->evalniveauid = $element->evalniveauid;
			$content_row->columns[$current_idx]->show = $element->show;
			$content_row->columns[$current_idx]->visible = ((!$element->visible || !$rowcontent->visible) ? false : true);
			$content_row->columns[$current_idx]->evaluation_mapped = \block_exacomp\global_config::get_additionalinfo_value_mapping($element->eval);
			$content_row->columns[$current_idx]->timestamp = $element->timestamp;

			if (in_array($niveau, $spanning_niveaus)) {
				$content_row->columns[$current_idx]->span = $spanning_colspan;
			} else {
				$content_row->columns[$current_idx]->span = 0;
			}
			$current_idx++;
		}

		if (block_exacomp_is_topicgrading_enabled()) {
			$topic_eval = new stdClass();
			$topic_eval->evaluation_text = \block_exacomp\global_config::get_teacher_eval_title_by_id(\block_exacomp\global_config::get_additionalinfo_value_mapping($rowcontent->topic_eval));
			$topic_eval->evaluation = empty($rowcontent->topic_eval) ? -1 : $rowcontent->topic_eval;
			$topic_eval->evaluation_mapped = \block_exacomp\global_config::get_additionalinfo_value_mapping($rowcontent->topic_eval);
			$topic_eval->evalniveauid = $rowcontent->topic_evalniveauid;
			$topic_eval->topicid = $rowcontent->topic_id;
			$topic_eval->span = 0;
			$topic_eval->visible = $rowcontent->visible;
			$topic_eval->timestamp = $rowcontent->timestamp;
			$content_row->columns[$current_idx] = $topic_eval;
		}

		$table->rows[] = $content_row;
	}

	if (block_exacomp_is_subjectgrading_enabled()) {
		$content_row = new stdClass();
		$content_row->columns = array();

		$content_row->columns[0] = new stdClass();
		$content_row->columns[0]->text = block_exacomp_get_string('total');
		$content_row->columns[0]->span = count($table_header);

		$content_row->columns[1] = new stdClass();
		$content_row->columns[1]->evaluation = empty($table_content->subject_eval) ? -1 : $table_content->subject_eval;
		$content_row->columns[1]->evaluation_text = \block_exacomp\global_config::get_teacher_eval_title_by_id(\block_exacomp\global_config::get_additionalinfo_value_mapping($table_content->subject_eval));
		$content_row->columns[1]->evaluation_mapped = \block_exacomp\global_config::get_additionalinfo_value_mapping($table_content->subject_eval);
		$content_row->columns[1]->evalniveauid = $table_content->subject_evalniveauid;
		$content_row->columns[1]->span = 0;

		$table->rows[] = $content_row;
	}

	return $table;
}

/**
 * if additional grading is enabled, already existing evaluation for topic, subjects and descriptors are mapped to notes from 1 to 6
 * @param unknown $course
 */
function block_exacomp_map_value_to_grading($course) {
	global $DB;

	$mapping = \block_exacomp\global_config::get_values_additionalinfo_mapping();

	//TOPIC, SUBJECT, CROSSSUBJECT, DESCRIPTOR
	$select = 'courseid = ? AND role = ?'; //is put into the where clause
	$params = array($course, BLOCK_EXACOMP_ROLE_TEACHER);
	$results = $DB->get_records_select(BLOCK_EXACOMP_DB_COMPETENCES, $select, $params);

	foreach ($results as $result) {
		if (!$result->additionalinfo && $result->value > -1) {
			if ($result->comptype == BLOCK_EXACOMP_TYPE_DESCRIPTOR) {
				$descriptor = $DB->get_record(BLOCK_EXACOMP_DB_DESCRIPTORS, array('id' => $result->compid));
			}

			if ($result->comptype != BLOCK_EXACOMP_TYPE_DESCRIPTOR || $descriptor->parentid == 0) {
				$result->additionalinfo = @$mapping[$result->value];
				$DB->update_record(BLOCK_EXACOMP_DB_COMPETENCES, $result);
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
function block_exacomp_get_visible_descriptors_for_subject($courseid, $subjectid, $userid = 0, $parent = true) {
	global $DB;

	$sql = "SELECT DISTINCT d.id, d.title FROM {".BLOCK_EXACOMP_DB_DESCRIPTORS."} d
		LEFT JOIN {".BLOCK_EXACOMP_DB_DESCTOPICS."} dt ON d.id = dt.descrid
		LEFT JOIN {".BLOCK_EXACOMP_DB_COURSETOPICS."} ct ON dt.topicid = ct.topicid
		LEFT JOIN {".BLOCK_EXACOMP_DB_DESCVISIBILITY."} dv ON d.id = dv.descrid AND dv.courseid = ct.courseid
		LEFT JOIN {".BLOCK_EXACOMP_DB_TOPICVISIBILITY."} tv ON dt.topicid = tv.topicid AND tv.courseid = ct.courseid
		LEFT JOIN {".BLOCK_EXACOMP_DB_TOPICS."} t ON ct.topicid = t.id
		WHERE ct.courseid = ? AND t.subjid = ? AND 
				
		".(($parent) ? "d.parentid = 0" : "d.parentid!=0")."
						
		AND ((dv.visible = 1 AND dv.studentid = 0 AND NOT EXISTS 
		  (SELECT *
		   FROM {".BLOCK_EXACOMP_DB_DESCVISIBILITY."} dvsub
		   WHERE dvsub.descrid = dv.descrid AND dvsub.courseid = dv.courseid AND dvsub.visible = 0 AND dvsub.studentid = ?)) 
		   OR (dv.visible = 1 AND dv.studentid = ? AND NOT EXISTS 
		  (SELECT *
		   FROM {".BLOCK_EXACOMP_DB_DESCVISIBILITY."} dvsub
		   WHERE dvsub.descrid = dv.descrid AND dvsub.courseid = dv.courseid AND dvsub.visible = 0 AND dvsub.studentid = 0)))
		   
		AND ((tv.visible = 1 AND tv.studentid = 0 AND NOT EXISTS 
		  (SELECT *
		   FROM {".BLOCK_EXACOMP_DB_TOPICVISIBILITY."} tvsub
		   WHERE tvsub.topicid = tv.topicid AND tvsub.courseid = tv.courseid AND tvsub.visible = 0 AND tvsub.studentid = ?)) 
		   OR (tv.visible = 1 AND tv.studentid = ? AND NOT EXISTS 
		  (SELECT *
		   FROM {".BLOCK_EXACOMP_DB_TOPICVISIBILITY."} tvsub
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
function block_exacomp_get_visible_examples_for_subject($courseid, $subjectid, $userid = 0) {
	global $DB;

	$sql = "SELECT DISTINCT e.id, e.title FROM {".BLOCK_EXACOMP_DB_EXAMPLES."} e
		LEFT JOIN {".BLOCK_EXACOMP_DB_DESCEXAMP."} de ON e.id = de.exampid
		LEFT JOIN {".BLOCK_EXACOMP_DB_DESCTOPICS."} dt ON de.descrid = dt.descrid
		LEFT JOIN {".BLOCK_EXACOMP_DB_COURSETOPICS."} ct ON dt.topicid = ct.topicid
		LEFT JOIN {".BLOCK_EXACOMP_DB_EXAMPVISIBILITY."} ev ON e.id = ev.exampleid AND ev.courseid = ct.courseid
		LEFT JOIN {".BLOCK_EXACOMP_DB_DESCVISIBILITY."} dv ON de.descrid = dv.descrid AND dv.courseid = ct.courseid
		LEFT JOIN {".BLOCK_EXACOMP_DB_TOPICVISIBILITY."} tv ON dt.topicid = tv.topicid AND tv.courseid = ct.courseid
		LEFT JOIN {".BLOCK_EXACOMP_DB_TOPICS."} t ON ct.topicid = t.id
		
		WHERE ct.courseid = ? AND t.subjid = ?
		
		AND ((ev.visible = 1 AND ev.studentid = 0 AND NOT EXISTS 
		  (SELECT *
		   FROM {".BLOCK_EXACOMP_DB_EXAMPVISIBILITY."} evsub
		   WHERE evsub.exampleid = ev.exampleid AND evsub.courseid = ev.courseid AND evsub.visible = 0 AND evsub.studentid = ?)) 
		   OR (ev.visible = 1 AND ev.studentid = ? AND NOT EXISTS 
		  (SELECT *
		   FROM {".BLOCK_EXACOMP_DB_EXAMPVISIBILITY."} evsub
		   WHERE evsub.exampleid = ev.exampleid AND evsub.courseid = ev.courseid AND evsub.visible = 0 AND evsub.studentid = 0)))
		
		AND ((dv.visible = 1 AND dv.studentid = 0 AND NOT EXISTS
		  (SELECT *
		   FROM {".BLOCK_EXACOMP_DB_DESCVISIBILITY."} dvsub
		   WHERE dvsub.descrid = dv.descrid AND dvsub.courseid = dv.courseid AND dvsub.visible = 0 AND dvsub.studentid = ?))
		   OR (dv.visible = 1 AND dv.studentid = ? AND NOT EXISTS
		  (SELECT *
		   FROM {".BLOCK_EXACOMP_DB_DESCVISIBILITY."} dvsub
		   WHERE dvsub.descrid = dv.descrid AND dvsub.courseid = dv.courseid AND dvsub.visible = 0 AND dvsub.studentid = 0)))
		 
		AND ((tv.visible = 1 AND tv.studentid = 0 AND NOT EXISTS
		  (SELECT *
		   FROM {".BLOCK_EXACOMP_DB_TOPICVISIBILITY."} tvsub
		   WHERE tvsub.topicid = tv.topicid AND tvsub.courseid = tv.courseid AND tvsub.visible = 0 AND tvsub.studentid = ?))
		   OR (tv.visible = 1 AND tv.studentid = ? AND NOT EXISTS
		  (SELECT *
		   FROM {".BLOCK_EXACOMP_DB_TOPICVISIBILITY."} tvsub
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
 * @return array(["descriptor_evaluation"] => ["evalniveauid"] => [evalvalue] => sum
 *                 ["child_evaluation"] => ["evalniveauid"] => [evalvalue] => sum
 *                 ["example_evaluation"] => ["evalniveauid"] => [evalvalue] => sum
 *         )
 * this is representing the resulting matrix, use of evaluation niveaus is minded here
 * evalniveauid = 0 if block_exacomp_use_eval_niveau() = false, otherwise -1, 1, 2, 3
 * evalvalue is 0 to 3, this statistic is not display if block_exacomp_additional_grading() = false
 *
 */
function block_exacomp_get_evaluation_statistic_for_subject($courseid, $subjectid, $userid, $start_timestamp = 0, $end_timestamp = 0) {
	// TODO: is visibility hier fürn hugo? Bewertungen kann es eh nur für sichtbare geben ...
	$descriptors = block_exacomp_get_visible_descriptors_for_subject($courseid, $subjectid, $userid);
	$child_descriptors = block_exacomp_get_visible_descriptors_for_subject($courseid, $subjectid, $userid, false);
	$examples = block_exacomp_get_visible_examples_for_subject($courseid, $subjectid, $userid);

	$descriptorgradings = []; // array[niveauid][value][number of examples evaluated with this value and niveau]
	$childgradings = [];
	$examplegradings = [];

	// create grading statistic
	$scheme_items = \block_exacomp\global_config::get_teacher_eval_items(block_exacomp_get_grading_scheme($courseid));
	$evaluationniveau_items = block_exacomp_use_eval_niveau()
		? \block_exacomp\global_config::get_evalniveaus()
		: ['0' => ''];

	foreach ($evaluationniveau_items as $niveaukey => $niveauitem) {
		$descriptorgradings[$niveaukey] = [];
		$childgradings[$niveaukey] = [];
		$examplegradings[$niveaukey] = [];

		foreach ($scheme_items as $schemekey => $schemetitle) {
			if ($schemekey > -1) {
				$descriptorgradings[$niveaukey][$schemekey] = 0;
				$childgradings[$niveaukey][$schemekey] = 0;
				$examplegradings[$niveaukey][$schemekey] = 0;
			}
		}
	}

	foreach ($descriptors as $descriptor) {
		$eval = block_exacomp_get_comp_eval($courseid, BLOCK_EXACOMP_ROLE_TEACHER, $userid, BLOCK_EXACOMP_TYPE_DESCRIPTOR, $descriptor->id);

		// check if grading is within timeframe
		if ($eval && $eval->value !== null && $eval->timestamp >= $start_timestamp && ($end_timestamp == 0 || $eval->timestamp <= $end_timestamp)) {
			$niveaukey = block_exacomp_use_eval_niveau() ? $eval->evalniveauid : 0;

			// increase counter in statistic
			if (isset($descriptorgradings[$niveaukey][$eval->value])) {
				$descriptorgradings[$niveaukey][$eval->value]++;
			}
		}
	}

	foreach ($child_descriptors as $child) {
		$eval = block_exacomp_get_comp_eval($courseid, BLOCK_EXACOMP_ROLE_TEACHER, $userid, BLOCK_EXACOMP_TYPE_DESCRIPTOR, $child->id);

		// check if grading is within timeframe
		if ($eval && $eval->value !== null && $eval->timestamp >= $start_timestamp && ($end_timestamp == 0 || $eval->timestamp <= $end_timestamp)) {
			$niveaukey = block_exacomp_use_eval_niveau() ? $eval->evalniveauid : 0;

			// increase counter in statistic
			if (isset($childgradings[$niveaukey][$eval->value])) {
				$childgradings[$niveaukey][$eval->value]++;
			}
		}
	}

	foreach ($examples as $example) {
		$eval = block_exacomp_get_comp_eval($courseid, BLOCK_EXACOMP_ROLE_TEACHER, $userid, BLOCK_EXACOMP_TYPE_EXAMPLE, $example->id);

		// check if grading is within timeframe
		if ($eval && $eval->value !== null && $eval->timestamp >= $start_timestamp && ($end_timestamp == 0 || $eval->timestamp <= $end_timestamp)) {
			$niveaukey = block_exacomp_use_eval_niveau() ? $eval->evalniveauid : 0;

			// increase counter in statistic
			if (isset($examplegradings[$niveaukey][$eval->value])) {
				$examplegradings[$niveaukey][$eval->value]++;
			}
		}
	}

	return [
		"descriptor_evaluations" => $descriptorgradings,
		"child_evaluations" => $childgradings,
		"example_evaluations" => $examplegradings,
	];
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
function block_exacomp_get_descriptor_statistic_for_topic($courseid, $topicid, $studentid, $start_timestamp = 0, $end_timestamp = 0) {
	global $DB;

	if (!$end_timestamp) {
		// until now
		$end_timestamp = time();
	}

	$use_evalniveau = block_exacomp_use_eval_niveau();
	$evaluationniveau_items = \block_exacomp\global_config::get_evalniveaus();
	$user = $DB->get_record("user", array("id" => $studentid));

	$topic = \block_exacomp\topic::get($topicid);
	$topic->setDbLayer(\block_exacomp\db_layer_course::create($courseid));

	$descriptorgradings = array(); //array[niveauid][value][number of examples evaluated with this value and niveau]

	$user = block_exacomp_get_user_information_by_course($user, $courseid);

	foreach ($topic->descriptors as $descriptor) {
		$niveau = \block_exacomp\niveau::get($descriptor->niveauid);
		if (!$niveau) {
			continue;
		}

		$teacher_evaluation = block_exacomp_get_comp_eval($courseid, BLOCK_EXACOMP_ROLE_TEACHER, $studentid, BLOCK_EXACOMP_TYPE_DESCRIPTOR, $descriptor->id);
		$student_evaluation = block_exacomp_get_comp_eval($courseid, BLOCK_EXACOMP_ROLE_STUDENT, $studentid, BLOCK_EXACOMP_TYPE_DESCRIPTOR, $descriptor->id);

		$teacher_eval_within_timeframe = $teacher_evaluation && $teacher_evaluation->timestamp >= $start_timestamp && $teacher_evaluation->timestamp <= $end_timestamp;
		$student_eval_within_timeframe = $student_evaluation && $student_evaluation->timestamp >= $start_timestamp && $student_evaluation->timestamp <= $end_timestamp;

		$descriptorgradings[$niveau->title] = new stdClass();
		// copy of block_exacomp_get_grid_for_competence_profile_topic_data()
		$descriptorgradings[$niveau->title]->teachervalue =
			(block_exacomp_additional_grading())
				? (($teacher_evaluation && $teacher_evaluation->additionalinfo) ? \block_exacomp\global_config::get_additionalinfo_value_mapping($teacher_evaluation->additionalinfo) : '')
				: (($teacher_evaluation && $teacher_evaluation->value) ? $scheme_items[$teacher_evaluation->value] : -1);
		$descriptorgradings[$niveau->title]->evalniveau = ($use_evalniveau && $teacher_eval_within_timeframe ? $teacher_evaluation->evalniveauid : -1) ?: -1;
		$descriptorgradings[$niveau->title]->studentvalue = ($student_eval_within_timeframe ? $student_evaluation->value : -1) ?: -1;
	}

	return array("descriptor_evaluation" => $descriptorgradings);
}

/**
 * get all underlying examples for one descriptor, including those associated with child descriptors and sort them according to their state
 * @param unknown $courseid
 * @param unknown $descriptorid
 * @param unknown $userid
 * @return unknown
 */
function block_exacomp_get_visible_own_and_child_examples_for_descriptor($courseid, $descriptorid, $userid) {
	global $DB;
	$sql = 'SELECT DISTINCT e.id, e.title, e.sorting FROM {'.BLOCK_EXACOMP_DB_EXAMPLES.'} e 
		JOIN {'.BLOCK_EXACOMP_DB_DESCEXAMP.'} de ON de.exampid = e.id
		JOIN {'.BLOCK_EXACOMP_DB_DESCRIPTORS.'} d ON de.descrid = d.id
		LEFT JOIN {'.BLOCK_EXACOMP_DB_EXAMPVISIBILITY.'} ev ON e.id = ev.exampleid AND ev.courseid = ?
		WHERE e.blocking_event = 0 AND d.id IN (
				SELECT dsub.id FROM {'.BLOCK_EXACOMP_DB_DESCRIPTORS.'} dsub 
                LEFT JOIN {'.BLOCK_EXACOMP_DB_DESCVISIBILITY.'} dv ON dsub.id = dv.descrid AND dv.courseid = ? 
                WHERE dsub.id = ? OR dsub.parentid = ?
                AND ((dv.visible = 1 AND dv.studentid = 0 AND NOT EXISTS (
                		SELECT * FROM {'.BLOCK_EXACOMP_DB_DESCVISIBILITY.'} dvsub
		  				WHERE dvsub.descrid = dv.descrid AND dvsub.courseid = dv.courseid AND dvsub.visible = 0 AND dvsub.studentid = ?))
		  		OR (dv.visible = 1 AND dv.studentid = ? AND NOT EXISTS (
                		SELECT * FROM {'.BLOCK_EXACOMP_DB_DESCVISIBILITY.'} dvsub
		  			 	WHERE dvsub.descrid = dv.descrid AND dvsub.courseid = dv.courseid AND dvsub.visible = 0 AND dvsub.studentid = 0)))
		)
 		AND ((ev.visible = 1 AND ev.studentid = 0 AND NOT EXISTS (
              	SELECT * FROM {'.BLOCK_EXACOMP_DB_EXAMPVISIBILITY.'} evsub
   				WHERE evsub.exampleid = ev.exampleid AND evsub.courseid = ev.courseid AND evsub.visible = 0 AND evsub.studentid = ?)) 
   		OR (ev.visible = 1 AND ev.studentid = ? AND NOT EXISTS (
              	SELECT * FROM {'.BLOCK_EXACOMP_DB_EXAMPVISIBILITY.'} evsub
  				WHERE evsub.exampleid = ev.exampleid AND evsub.courseid = ev.courseid AND evsub.visible = 0 AND evsub.studentid =0)))';

	$examples = $DB->get_records_sql($sql, array($courseid, $courseid, $descriptorid, $descriptorid, $userid, $userid, $userid, $userid));

	foreach ($examples as $example) {
		$example->state = block_exacomp_get_dakora_state_for_example($courseid, $example->id, $userid);
	}

	usort($examples, function($a, $b) {
		if ($a->state == $b->state) {
			return $a->sorting > $b->sorting;
		}

		return $a->state < $b->state;
	});

	return $examples;
}

/**
 * get data for displaying a list of all underlying topics and descriptors and examples
 * (not childdescriptors, but all their examples withing descriptor) with teacher und student evaluation for a subject
 * @param unknown $courseid
 * @param unknown $subject
 * @param unknown $student
 * @return unknown[]|stdClass[]
 */
function block_exacomp_get_data_for_profile_comparison($courseid, $subject, $student) {
	$student = block_exacomp_get_user_information_by_course($student, $courseid);

	foreach ($subject->subs as $topic) {
		foreach ($topic->descriptors as $descriptor) {
			$descriptor->examples = block_exacomp_get_visible_own_and_child_examples_for_descriptor($courseid, $descriptor->id, $student->id);
		}
	}

	return array($student, $subject);
}

/**
 * return all visible examples for a course and user context with only one sql query
 *
 * @param int $courseid
 * @param number $userid
 *
 * @return {{id}, {...}}
 */
function block_exacomp_get_example_visibilities_for_course_and_user($courseid, $userid = 0) {
	return Cache::staticCallback(__FUNCTION__, function($courseid, $userid) {
		return g::$DB->get_records_sql_menu("
			SELECT DISTINCT ev.exampleid, ev.visible
			FROM {".BLOCK_EXACOMP_DB_EXAMPVISIBILITY."} ev
			WHERE ev.courseid=? AND ev.studentid=?
		", [$courseid, $userid]);
	}, func_get_args());
}

/**
 * return all visible descriptors (parent & child) for a course and user context with only one sql query
 *
 * @param int $courseid
 * @param int $userid if userid == 0 -> only visibility for all is minded, not user related:
 *                         used in assign_competencies when all sutdents are selected
 *
 * @return: {{id}, {...}}
 */
function block_exacomp_get_descriptor_visibilities_for_course_and_user($courseid, $userid) {
	return Cache::staticCallback([__FUNCTION__], function($courseid, $userid) {
		return g::$DB->get_records_sql_menu("
			SELECT DISTINCT dv.descrid, dv.visible
			FROM {".BLOCK_EXACOMP_DB_DESCVISIBILITY."} dv
			WHERE dv.courseid = ? AND dv.studentid = ?
		", [$courseid, $userid]);
	}, func_get_args());
}

/**
 * return all visible topics for a course and user context with only one sql query
 *
 * @param int $courseid
 * @param int $userid
 *
 * @return: {{id}, {...}}
 */
function block_exacomp_get_topic_visibilities_for_course_and_user($courseid, $userid = 0) {
	return Cache::staticCallback(__FUNCTION__, function($courseid, $userid) {
		return g::$DB->get_records_sql_menu("
			SELECT DISTINCT tv.topicid, tv.visible
			FROM {".BLOCK_EXACOMP_DB_TOPICVISIBILITY."} tv
			WHERE tv.courseid = ? AND tv.studentid = ?", [$courseid, $userid]);
	}, func_get_args());
}

/**
 * returnes a list of examples whose solutions are visibile in course and user context
 * @param unknown $courseid
 * @param number $userid
 */
function block_exacomp_get_solution_visibilities_for_course_and_user($courseid, $userid = 0) {
	return Cache::staticCallback(__FUNCTION__, function($courseid, $userid) {
		return g::$DB->get_records_sql_menu("
			SELECT DISTINCT sv.exampleid, sv.visible 
			FROM {".BLOCK_EXACOMP_DB_SOLUTIONVISIBILITY."} sv 
			WHERE sv.courseid = ? AND sv.studentid=? 
		", [$courseid, $userid]);
	}, func_get_args());
}

/**
 * clear visibility cache if any visibility of any object in course changes
 * @param unknown $courseid
 */
function block_exacomp_clear_visibility_cache($courseid) {
	// not needed anymore
}

/**
 * create tree for one example, similar like block_exacomp_build_example_association_tree()
 * but with improved performance
 */
function block_exacomp_build_example_parent_names($courseid, $exampleid) {
	$sql = "SELECT d.id as descrid, d.title as descrtitle, d.parentid as parentid, s.id as subjid, s.title as subjecttitle, t.id as topicid, t.title as topictitle, 
				e.id as exampleid, e.title as exampletitle 
			FROM {".BLOCK_EXACOMP_DB_SUBJECTS."} s 
			JOIN {".BLOCK_EXACOMP_DB_TOPICS."} t ON t.subjid = s.id
			JOIN {".BLOCK_EXACOMP_DB_COURSETOPICS."} ct ON t.id = ct.topicid
			JOIN {".BLOCK_EXACOMP_DB_DESCTOPICS."} dt ON dt.topicid = t.id
			JOIN {".BLOCK_EXACOMP_DB_DESCRIPTORS."} d ON d.id = dt.descrid
			JOIN {".BLOCK_EXACOMP_DB_DESCEXAMP."} de ON d.id = de.descrid
			JOIN {".BLOCK_EXACOMP_DB_EXAMPLES."} e ON e.id = de.exampid
			WHERE e.id = ? AND ct.courseid = ?";

	$records = iterator_to_array(g::$DB->get_recordset_sql($sql, array($exampleid, $courseid)));

	$flatTree = array();
	foreach ($records as $record) {
		$titles = [block_exacomp_get_topic_numbering(\block_exacomp\topic::get($record->topicid)).' '.$record->topictitle];

		//check if parent descriptor or child
		if ($record->parentid > 0) {    //child get parentdescriptor
			$parent_descriptor = \block_exacomp\descriptor::get($record->parentid);
			$parent_descriptor->topicid = $record->topicid;

			$titles[] = block_exacomp_get_descriptor_numbering($parent_descriptor).' '.$parent_descriptor->title;
		}

		$descriptor = \block_exacomp\descriptor::get($record->descrid);
		$descriptor->topicid = $record->topicid;
		$titles[] = block_exacomp_get_descriptor_numbering($descriptor).' '.$record->descrtitle;

		$flatTree[join(' | ', $titles)] = $titles;
	}

	return $flatTree;
}

function block_exacomp_any_examples_on_schedule($courseid) {
	global $DB;
	$schedule = $DB->get_records(BLOCK_EXACOMP_DB_SCHEDULE, array('courseid' => $courseid));
	if (!$schedule) {
		return false;
	}

	return true;
}

/**
 * used for eLove-App, checks if trainer is specified as external trainer for student
 * @param unknown $trainerid
 * @param unknown $studentid
 */
function block_exacomp_is_external_trainer_for_student($trainerid, $studentid) {
	return g::$DB->get_record(BLOCK_EXACOMP_DB_EXTERNAL_TRAINERS, [
		'trainerid' => $trainerid,
		'studentid' => $studentid,
	]);
}

/**
 * used for eLove-App, checks if trainer is external trainer for any student
 * @param unknown $trainerid
 */
function block_exacomp_is_external_trainer($trainerid) {
	return g::$DB->get_record(BLOCK_EXACOMP_DB_EXTERNAL_TRAINERS, [
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
function block_exacomp_get_user_subject_evaluation($userid, $subjectid, $courseid) {
	return g::$DB->get_record_sql("
		SELECT cu.additionalinfo, en.title as niveau
		FROM {".BLOCK_EXACOMP_DB_COMPETENCES."} as cu
		LEFT JOIN {".BLOCK_EXACOMP_DB_EVALUATION_NIVEAU."} en ON cu.evalniveauid = en.id
		WHERE cu.userid = ? AND cu.courseid = ? AND cu.compid = ? AND cu.role = ?", [
		$userid,
		$courseid,
		$subjectid,
		BLOCK_EXACOMP_ROLE_TEACHER,
	]);
}

/**
 * searches the competence grid of one course and returns only the found items
 * @param $courseid
 * @param $q
 * @return array
 */
function block_exacomp_search_competence_grid_as_tree($courseid, $q) {
	$subjects = \block_exacomp\db_layer_course::create($courseid)->get_subjects();

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
				if (is_array($value) || is_object($value)) {
					continue;
				}

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
			if ($item instanceof \block_exacomp\subject) {
				$filter_not_empty($item->topics);

				return !!$item->topics;
			}
			if ($item instanceof \block_exacomp\topic) {
				$filter_not_empty($item->descriptors);

				return !!$item->descriptors;
			}
			if ($item instanceof \block_exacomp\descriptor) {
				$filter_not_empty($item->children);

				return $item->examples || $item->children;
			}
		});
	};

	$filter = function(&$items) use ($find, &$filter, $filter_not_empty) {
		$items = array_filter($items, function($item) use ($find, $filter, $filter_not_empty) {
			if ($item instanceof \block_exacomp\subject) {
				if ($find($item)) {
					$filter_not_empty($item->topics);
				} else {
					$filter($item->topics);
				}

				return !!$item->topics;
			}
			if ($item instanceof \block_exacomp\topic) {
				if ($find($item)) {
					$filter_not_empty($item->descriptors);
				} else {
					$filter($item->descriptors);
				}

				return !!$item->descriptors;
			}
			if ($item instanceof \block_exacomp\descriptor) {
				if ($find($item)) {
					$filter_not_empty($item->children);

					return true;
				} else {
					$filter($item->examples);
					$filter($item->children);

					return $item->examples || $item->children;
				}
			}
			if ($item instanceof \block_exacomp\example) {
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

/**
 * searches the competence tree as example list
 * @param unknown $courseid
 * @param unknown $q
 * @return \block_exacomp\example[]
 */
function block_exacomp_search_competence_grid_as_example_list($courseid, $q) {
	$examples = [];
	$data = (object)[];
	$get_examples = function($items) use (&$get_examples, &$examples, &$data) {
		array_walk($items, function($item) use (&$get_examples, &$examples, &$data) {
			if ($item instanceof \block_exacomp\subject) {
				$data->subject = $item;
				$get_examples($item->topics);
			}
			if ($item instanceof \block_exacomp\topic) {
				$data->topic = $item;
				$get_examples($item->descriptors);
			}
			if ($item instanceof \block_exacomp\descriptor) {
				$data->descriptors[] = $item;
				$get_examples($item->children);
				$get_examples($item->examples);
				array_pop($data->descriptors);
			}
			if ($item instanceof \block_exacomp\example) {
				if (empty($examples[$item->id])) {
					$examples[$item->id] = $item;
					$item->subjects = [];
				}
				$parent = $examples[$item->id];

				if (empty($parent->subjects[$data->subject->id])) {
					$parent->subjects[$data->subject->id] = clone $data->subject;
					$parent->subjects[$data->subject->id]->topics = [];
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

	$subjects = block_exacomp_search_competence_grid_as_tree($courseid, $q);

	$get_examples($subjects);

	return $examples;
}

function block_exacomp_check_competence_data_is_gained($competence_data) {
	if (block_exacomp_additional_grading()) {
		$value = block_exacomp\global_config::get_additionalinfo_value_mapping($competence_data->additionalinfo);

		return $value >= 1;
	} else {
		return $competence_data->value >= 1;
	}
}

function block_exacomp_get_comp_eval_gained($courseid, $role, $userid, $comptype, $compid) {
	$eval = block_exacomp_get_comp_eval($courseid, $role, $userid, $comptype, $compid);

	return $eval && block_exacomp_check_competence_data_is_gained($eval) ? $eval : null;
}

/**
 * return evaluation for any type of competence: descriptor, subject, topic, crosssubject
 * @param unknown $courseid
 * @param unknown $role
 * @param unknown $studentid
 * @param unknown $comptype
 * @param unknown $compid
 * @return \block_exacomp\comp_eval
 */
function block_exacomp_get_comp_eval($courseid, $role, $studentid, $comptype, $compid) {

	// fallback for example style
	if ($comptype == BLOCK_EXACOMP_TYPE_EXAMPLE) {
		$eval = g::$DB->get_record(BLOCK_EXACOMP_DB_EXAMPLEEVAL, array("studentid" => $studentid, "courseid" => $courseid, "exampleid" => $compid));
		if (!$eval) {
			return null;
		}

		$data = [
			'id' => 'example:'.$eval->id,
			'courseid' => $eval->courseid,
			'userid' => $eval->studentid,
			'comptype' => BLOCK_EXACOMP_TYPE_EXAMPLE,
			'compid' => $eval->exampleid,
		];

		if ($role == BLOCK_EXACOMP_ROLE_TEACHER) {
			$data += [
				'value' => $eval->teacher_evaluation,
				'role' => BLOCK_EXACOMP_ROLE_TEACHER,
				'reviewerid' => $eval->teacher_reviewerid,
				'evalniveauid' => $eval->evalniveauid,
				'additionalinfo' => null,
				'timestamp' => $eval->timestamp_teacher,
				'resubmission' => $eval->resubmission,
			];
		} else {
			$data += [
				'value' => $eval->student_evaluation,
				'role' => BLOCK_EXACOMP_ROLE_STUDENT,
				'reviewerid' => $eval->studentid,
				'evalniveauid' => null,
				'additionalinfo' => null,
				'timestamp' => $eval->timestamp_student,
			];
		}

		// lastly add original data for debugging
		$data += [
			'@interal-original-data' => $eval,
		];

		return \block_exacomp\comp_eval::create($data);
	}

	return \block_exacomp\comp_eval::get(['courseid' => $courseid, 'userid' => $studentid, 'compid' => $compid, 'comptype' => $comptype, 'role' => $role]);
}

/**
 * get student and teacher evaluation
 * @param $courseid
 * @param $studentid
 * @param $comptype
 * @param $compid
 * @return \block_exacomp\comp_eval_merged
 */
function block_exacomp_get_comp_eval_merged($courseid, $studentid, $item) {
	return \block_exacomp\comp_eval_merged::get($courseid, $studentid, $item);
}

function block_exacomp_set_comp_eval($courseid, $role, $studentid, $comptype, $compid, $data) {
	$data = (array)$data;
	unset($data['courseid']);
	unset($data['role']);
	unset($data['userid']);
	unset($data['comptype']);
	unset($data['compid']);

	if ($comptype == BLOCK_EXACOMP_TYPE_EXAMPLE) {
		if ($role == BLOCK_EXACOMP_ROLE_TEACHER) {
			if (array_key_exists('reviewerid', $data)) {
				$data['teacher_reviewerid'] = $data['reviewerid'];
				unset($data['reviewerid']);
			}
			if (array_key_exists('timestamp', $data)) {
				$data['timestamp_teacher'] = $data['timestamp'];
				unset($data['timestamp']);
			}
			if (array_key_exists('value', $data)) {
				$data['teacher_evaluation'] = $data['value'];
				unset($data['value']);
			}

			if (isset($data['teacher_evaluation']) && $data['teacher_evaluation'] < 0) {
				// teacher:
				// 0 = nicht erreicht
				// null = nicht gesetzt
				// -1 = => auf null setzen
				$data['teacher_evaluation'] = null;
			}
			if (isset($data['evalniveauid']) && $data['evalniveauid'] <= 0) {
				$data['evalniveauid'] = null;
			}

			// resubmission: as is
		} else {
			if (array_key_exists('timestamp', $data)) {
				$data['timestamp_student'] = $data['timestamp'];
				unset($data['timestamp']);
			}
			if (array_key_exists('value', $data)) {
				$data['student_evaluation'] = $data['value'];
				unset($data['value']);
			}

			if (isset($data['student_evaluation']) && $data['student_evaluation'] < 0) {
				// teacher:
				// 0, null, -1 = nicht gesetzt => auf null setzen
				$data['student_evaluation'] = null;
			}

			unset($data['resubmission']);
			unset($data['evalniveauid']);
			unset($data['reviewerid']);
		}

		g::$DB->insert_or_update_record(BLOCK_EXACOMP_DB_EXAMPLEEVAL, $data, [
			'studentid' => $studentid,
			'courseid' => $courseid,
			'exampleid' => $compid,
		]);
	} else {
		if (isset($data['value'])) {
			if ($role == BLOCK_EXACOMP_ROLE_TEACHER) {
				if ($data['value'] < 0) {
					$data['value'] = null;
				}
			} else {
				if ($data['value'] <= 0) {
					$data['value'] = null;
				}
			}

			if (isset($data['evalniveauid']) && $data['evalniveauid'] <= 0) {
				$data['evalniveauid'] = null;
			}
		}

		if (!array_key_exists('timestamp', $data)) {
			$record = g::$DB->get_record(BLOCK_EXACOMP_DB_COMPETENCES, [
				'courseid' => $courseid,
				'userid' => $studentid,
				'comptype' => $comptype,
				'compid' => $compid,
				'role' => $role,
			]);

			if ($record) {
				$changed = false;
				if (array_key_exists('additionalinfo', $data) && $data['additionalinfo'] != $record->additionalinfo) {
					$changed = true;
				}
				if (array_key_exists('evalniveauid', $data) && $data['evalniveauid'] != $record->evalniveauid) {
					$changed = true;
				}
				if (array_key_exists('value', $data)) {
					// for value also check for null
					$new_value = $data['value'];
					$old_value = $record->value;
					if ($new_value !== null) {
						$new_value = (int)$new_value;
					}
					if ($old_value !== null) {
						$old_value = (int)$old_value;
					}
					if ($new_value !== $old_value) {
						$changed = true;
					}
				}

				if ($changed) {
					$data['timestamp'] = time();
				}
			} else {
				$data['timestamp'] = time();
			}
		}


		g::$DB->insert_or_update_record(BLOCK_EXACOMP_DB_COMPETENCES, $data, [
			'courseid' => $courseid,
			'userid' => $studentid,
			'comptype' => $comptype,
			'compid' => $compid,
			'role' => $role,
		]);
	}
}

/**
 * return evaluation value for any type of competence: descriptor, subject, topic, crosssubject
 * @param unknown $courseid
 * @param unknown $role
 * @param unknown $userid
 * @param unknown $comptype
 * @param unknown $compid
 */
/*
function block_exacomp_get_comp_eval_value($courseid, $role, $userid, $comptype, $compid) {
	return g::$DB->get_field(BLOCK_EXACOMP_DB_COMPETENCES, 'value', array('courseid' => $courseid, 'userid' => $userid, 'compid' => $compid, 'comptype' => $comptype, 'role' => $role));
}
*/

/**
 * return niveau items
 * @return string[][]
 */
function block_exacomp_get_select_niveau_items($blank = true) {
	$values = [];
	if ($blank) {
		$values[''] = ['' => ''];
	}
	$niveaus = \block_exacomp\niveau::get_objects(null, 'sorting');
	foreach ($niveaus as $niveau) {
		$sourceName = block_exacomp_get_renderer()->source_info($niveau->source);
		if (!isset($values[$sourceName])) {
			$values[$sourceName] = [];
		}
		$values[$sourceName][$niveau->id] = $niveau->title;
	}
	ksort($values);

	return $values;
}

class block_exacomp_permission_exception extends moodle_exception {
	function __construct($errorcode = 'Not allowed', $module = '', $link = '', $a = null, $debuginfo = null) {
		return parent::__construct($errorcode, $module, $link, $a, $debuginfo);
	}
}

/**
 * check if user has certain capability
 * @param unknown $cap
 * @param unknown $data
 * @throws \coding_exception
 * @return boolean
 */
function block_exacomp_has_capability($cap, $data) {
	if ($cap == BLOCK_EXACOMP_CAP_ADD_EXAMPLE) {
		$courseid = $data;
		if (!block_exacomp_is_teacher($courseid)) {
			return false;
		}
	} else {
		throw new \coding_exception("Capability $cap not found");
	}

	return true;
}

/**
 * require certain capability
 * @param unknown $cap
 * @param unknown $data
 * @throws block_exacomp_permission_exception
 */
function block_exacomp_require_capability($cap, $data) {
	if (!block_exacomp_has_capability($cap, $data)) {
		throw new block_exacomp_permission_exception();
	}
}

/**
 * require capability for certain item
 * @param unknown $cap
 * @param unknown $item
 * @throws block_exacomp_permission_exception
 * @throws \coding_exception
 * @return boolean
 */
function block_exacomp_require_item_capability($cap, $item) {
	if ($item instanceof \block_exacomp\example && in_array($cap, [BLOCK_EXACOMP_CAP_MODIFY, BLOCK_EXACOMP_CAP_DELETE, BLOCK_EXACOMP_CAP_SORTING])) {
		if ($item->creatorid == g::$USER->id) {
			// User is creator
			return true;
		}

		if (!block_exacomp_is_teacher()) {
			throw new block_exacomp_permission_exception('User is no teacher');
		}

		// find example in course
		$examples = block_exacomp_get_examples_by_course(g::$COURSE->id);
		if (!isset($examples[$item->id])) {
			throw new block_exacomp_permission_exception('Not a course example');
		}
	} elseif ($item instanceof \block_exacomp\example && in_array($cap, [BLOCK_EXACOMP_CAP_VIEW])) {
		if (!block_exacomp_is_student() && !block_exacomp_is_teacher()) {
			throw new block_exacomp_permission_exception('User is no teacher or student');
		}

		// find descriptor in course
		$examples = block_exacomp_get_examples_by_course(g::$COURSE->id);
		if (!isset($examples[$item->id])) {
			throw new block_exacomp_permission_exception('Not a course example');
		}

		// TODO: check visibility?
	} elseif ($item instanceof \block_exacomp\subject && in_array($cap, [BLOCK_EXACOMP_CAP_MODIFY, BLOCK_EXACOMP_CAP_DELETE])) {
		if (!block_exacomp_is_teacher(g::$COURSE->id)) {
			throw new block_exacomp_permission_exception('User is no teacher');
		}

		$subjects = block_exacomp_get_subjects(g::$COURSE->id);
		if (!isset($subjects[$item->id])) {
			throw new block_exacomp_permission_exception('No course subject');
		}

		if ($item->source != BLOCK_EXACOMP_DATA_SOURCE_CUSTOM) {
			throw new block_exacomp_permission_exception('Not a custom subject');
		}
	} elseif ($item instanceof \block_exacomp\topic && in_array($cap, [BLOCK_EXACOMP_CAP_MODIFY, BLOCK_EXACOMP_CAP_DELETE])) {
		if (!block_exacomp_is_teacher(g::$COURSE->id)) {
			throw new block_exacomp_permission_exception('User is no teacher');
		}

		$topics = block_exacomp_get_topics_by_course(g::$COURSE->id);
		if (!isset($topics[$item->id])) {
			throw new block_exacomp_permission_exception('No course topic');
		}


		if ($item->source != BLOCK_EXACOMP_DATA_SOURCE_CUSTOM) {
			throw new block_exacomp_permission_exception('Not a custom topic');
		}
	} elseif ($item instanceof \block_exacomp\descriptor && in_array($cap, [BLOCK_EXACOMP_CAP_MODIFY, BLOCK_EXACOMP_CAP_DELETE])) {
		if (!block_exacomp_is_teacher(g::$COURSE->id)) {
			throw new block_exacomp_permission_exception('User is no teacher');
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
			if ($found) {
				break;
			}
		}
		if (!$found) {
			throw new block_exacomp_permission_exception('No course descriptor');
		}

		if ($item->source != BLOCK_EXACOMP_DATA_SOURCE_CUSTOM) {
			throw new block_exacomp_permission_exception('Not a custom descriptor');
		}
	} elseif ($item instanceof \block_exacomp\cross_subject && in_array($cap, [BLOCK_EXACOMP_CAP_MODIFY, BLOCK_EXACOMP_CAP_DELETE])) {
		if (block_exacomp_is_admin()) {
			return true;
		}

		if ($item->is_draft()) {
			// draft
			if (!block_exacomp_is_teacher(g::$COURSE->id)) {
				throw new block_exacomp_permission_exception('User is no teacher');
			}

			if ($item->creatorid != g::$USER->id) {
				throw new block_exacomp_permission_exception('No permission');
			}
		} else {
			if (!block_exacomp_is_teacher($item->courseid)) {
				throw new block_exacomp_permission_exception('User is no teacher');
			}
		}
	} elseif ($item instanceof \block_exacomp\cross_subject && in_array($cap, [BLOCK_EXACOMP_CAP_VIEW])) {
		if ($item->has_capability(BLOCK_EXACOMP_CAP_MODIFY)) {
			return true;
		}

		if ($item->is_draft() && block_exacomp_is_teacher()) {
			// teachers can view all drafts
			return true;
		}

		// it's a student
		if ($item->is_draft() || $item->courseid != g::$COURSE->id) {
			throw new block_exacomp_permission_exception('No permission');
		}
		if (!$item->shared && !block_exacomp_student_crosssubj($item->id, g::$USER->id)) {
			throw new block_exacomp_permission_exception('No permission');
		}
	} else {
		throw new \coding_exception("Capability $cap for item ".print_r($item, true)." not found");
	}

	return true;
}

/**
 * check capability for certain item
 * @param unknown $cap
 * @param unknown $item
 * @return boolean
 */
function block_exacomp_has_item_capability($cap, $item) {
	try {
		block_exacomp_require_item_capability($cap, $item);

		return true;
	} catch (block_exacomp_permission_exception $e) {
		return false;
	}
}

function block_exacomp_get_db_table_from_type($type) {
	if ($type == BLOCK_EXACOMP_TYPE_SUBJECT) {
		return BLOCK_EXACOMP_DB_SUBJECTS;
	} elseif ($type == BLOCK_EXACOMP_TYPE_TOPIC) {
		return BLOCK_EXACOMP_DB_TOPICS;
	} elseif ($type == BLOCK_EXACOMP_TYPE_DESCRIPTOR) {
		return BLOCK_EXACOMP_DB_DESCRIPTORS;
	} elseif ($type == BLOCK_EXACOMP_TYPE_EXAMPLE) {
		return BLOCK_EXACOMP_DB_EXAMPLES;
	}
}

function block_exacomp_format_eval_value($value) {
	if ($value === null) {
		return '';
	}

	return format_float($value, 1, true, true);
}

function block_exacomp_group_reports_get_filter($reportType = 'general') {
    $filter = (array)@$_REQUEST['filter'];

    switch ($reportType) {
        case 'annex':
            //if (!$filter) {
            //default constants filter
            @$filter[BLOCK_EXACOMP_TYPE_SUBJECT]['visible'] = true;
            @$filter[BLOCK_EXACOMP_TYPE_TOPIC]['visible'] = true;
            @$filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR_PARENT]['visible'] = true;
            @$filter[BLOCK_EXACOMP_TYPE_SUBJECT]['active'] = true;
            @$filter[BLOCK_EXACOMP_TYPE_TOPIC]['active'] = true;
            @$filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR_PARENT]['active'] = true;
        //}
        //break;
        default:
            if (!$filter) {
                // default filter
                @$filter[BLOCK_EXACOMP_TYPE_SUBJECT]['visible'] = true;
                @$filter[BLOCK_EXACOMP_TYPE_TOPIC]['visible'] = true;
                @$filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR_PARENT]['visible'] = true;
            }

            // active means, we also have to loop over those items
            if (@$filter[BLOCK_EXACOMP_TYPE_EXAMPLE]['visible']) {
                @$filter[BLOCK_EXACOMP_TYPE_EXAMPLE]['active'] = true;
            }
            if (@$filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR_CHILD]['visible'] || @$filter[BLOCK_EXACOMP_TYPE_EXAMPLE]['active']) {
                @$filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR_CHILD]['active'] = true;
            }
            if (@$filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR_PARENT]['visible'] || @$filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR_CHILD]['active']) {
                @$filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR_PARENT]['active'] = true;
            }
            if (@$filter[BLOCK_EXACOMP_TYPE_TOPIC]['visible'] || @$filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR_PARENT]['active']) {
                @$filter[BLOCK_EXACOMP_TYPE_TOPIC]['active'] = true;
            }
            if (@$filter[BLOCK_EXACOMP_TYPE_SUBJECT]['visible'] || @$filter[BLOCK_EXACOMP_TYPE_TOPIC]['active']) {
                @$filter[BLOCK_EXACOMP_TYPE_SUBJECT]['active'] = true;
            }

            if (@$filter['type'] != 'student_counts') {
                $filter['type'] = 'students';
            }
    }

    return $filter;
}

function block_exacomp_tree_walk(&$items, $data, $callback) {
	$args = func_get_args();
	array_shift($args);
	array_shift($args);
	array_shift($args);

	foreach ($items as $key => $item) {
		$walk_subs = function() use ($item, $data, $callback) {
			$filter = $data['filter'];

			$args = func_get_args();

			if ($item instanceof \block_exacomp\subject && @$filter[BLOCK_EXACOMP_TYPE_TOPIC]['active']) {
				call_user_func_array('block_exacomp_tree_walk', array_merge([&$item->topics, $data, $callback], $args));
			}
			if ($item instanceof \block_exacomp\topic && @$filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR_PARENT]['active']) {
				call_user_func_array('block_exacomp_tree_walk', array_merge([&$item->descriptors, $data, $callback], $args));
			}
			if ($item instanceof \block_exacomp\descriptor && @$filter[BLOCK_EXACOMP_TYPE_EXAMPLE]['active']) {
				call_user_func_array('block_exacomp_tree_walk', array_merge([&$item->examples, $data, $callback], $args));
			}
			if ($item instanceof \block_exacomp\descriptor && @$filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR_CHILD]['active']) {
				call_user_func_array('block_exacomp_tree_walk', array_merge([&$item->children, $data, $callback], $args));
			}
		};

		$ret = call_user_func_array($callback, array_merge([$walk_subs, $item], $args));

		if ($ret === false) {
			unset($items[$key]);
		}
	}
}

function block_exacomp_group_reports_result($filter) {
	$courseid = g::$COURSE->id;
	$students = block_exacomp_get_students_by_course($courseid);

	if ($filter['type'] == 'students') {
		$has_output = false;
		
		if($filter['selectedStudent'] != 0){
		    $students=array($students[$filter['selectedStudent']]);
 		}
		foreach ($students as $student) {
			$studentid = $student->id;

			$subjects = \block_exacomp\db_layer_course::create($courseid)->get_subjects();
			block_exacomp_tree_walk($subjects, ['filter' => $filter], function($walk_subs, $item, $level = 0) use ($studentid, $courseid, $filter) {
				$eval = block_exacomp_get_comp_eval_merged($courseid, $studentid, $item);

				$item_type = $item::TYPE;
				if ($item_type == BLOCK_EXACOMP_TYPE_DESCRIPTOR) {
					$item_type = $level > 2 ? BLOCK_EXACOMP_TYPE_DESCRIPTOR_CHILD : BLOCK_EXACOMP_TYPE_DESCRIPTOR_PARENT;
				}

				$item_filter = (array)@$filter[$item_type];

				$item->visible = @$item_filter['visible'];

				if (!@$item_filter['active']) {
					return false;
				}

				if (@$item_filter[BLOCK_EXACOMP_EVAL_INPUT_EVALNIVEAUID]) {
					$value = @$eval->evalniveauid ?: 0;
					if (!in_array($value, $item_filter[BLOCK_EXACOMP_EVAL_INPUT_EVALNIVEAUID])) {
						/*
						$item->visible = false;
						return;
						*/
						return false;
					}
				}
				if (@$item_filter['additionalinfo_from']) {
					$value = @$eval->additionalinfo ?: 0;
					if ($value < str_replace(',', '.', $item_filter['additionalinfo_from'])) {
						return false;
					}
				}
				if (@$item_filter['additionalinfo_to']) {
					$value = @$eval->additionalinfo ?: 0;
					if ($value > str_replace(',', '.', $item_filter['additionalinfo_to'])) {
						return false;
					}
				}

				if (@$item_filter[BLOCK_EXACOMP_EVAL_INPUT_TACHER_EVALUATION]) {
					$value = @$eval->teacherevaluation === null ? -1 : @$eval->teacherevaluation;
					if (!in_array($value, $item_filter[BLOCK_EXACOMP_EVAL_INPUT_TACHER_EVALUATION])) {
						return false;
					}
				}
				if (@$item_filter[BLOCK_EXACOMP_EVAL_INPUT_STUDENT_EVALUATION]) {
					$value = @$eval->studentevaluation ?: 0;
					if (!in_array($value, $item_filter[BLOCK_EXACOMP_EVAL_INPUT_STUDENT_EVALUATION])) {
						return false;
					}
				}

				if (@$filter['time']['active'] && @$filter['time']['from'] && $eval->timestampteacher < @$filter['time']['from']) {
					$item->visible = false;
				}
				if (@$filter['time']['active'] && @$filter['time']['to'] && $eval->timestampteacher > @$filter['time']['to']) {
					$item->visible = false;
				}

				$walk_subs($level + 1);

				$filter_active = $item_filter;
				unset($filter_active['active']);
				unset($filter_active['visible']);
				$filter_active = array_filter($filter_active, function($value) { return !empty($value); });
				$filter_active = !!$filter_active;

				if (!$filter_active) {
					if ($item instanceof \block_exacomp\subject && !$item->topics) {
						return false;
					}
					if ($item instanceof \block_exacomp\topic && !$item->descriptors) {
						return false;
					}
					if ($item instanceof \block_exacomp\descriptor && !$item->children && !$item->examples) {
						return false;
					}
				}
			});


			ob_start();
			block_exacomp_tree_walk($subjects, ['filter' => $filter], function($walk_subs, $item, $level = 0) use ($studentid, $courseid, $filter) {
				$eval = block_exacomp_get_comp_eval_merged($courseid, $studentid, $item);

				if (!$item->visible) {
					// walk subs with same level
					$walk_subs($level);

					return;
				}

				
				//item_type is needed to distinguish between topics, parent descripors and child descriptors --> important for css-styling
				$item_type = $item::TYPE;
				if ($item_type == BLOCK_EXACOMP_TYPE_SUBJECT) {
				    echo '<tr class="exarep_subject_row">';
				}else if ($item_type == BLOCK_EXACOMP_TYPE_TOPIC) {
				    echo '<tr class="exarep_topic_row">';
				}else if($item_type == BLOCK_EXACOMP_TYPE_DESCRIPTOR && $level <= 2) {
				    echo '<tr class="exarep_descriptor_parent_row">';
				}else if($item_type == BLOCK_EXACOMP_TYPE_DESCRIPTOR && $level > 2) {
				    echo '<tr class="exarep_descriptor_child_row">';
				}else if($item_type == BLOCK_EXACOMP_TYPE_EXAMPLE) {
				    echo '<tr class="exarep_example_row">';
				}
				
				echo '<td class="exarep_descriptor" style="white-space: nowrap">'.$item->get_numbering();
				echo '<td class="exarep_descriptorText" style="padding-left: '.(5 + $level * 15).'px">'.$item->title;
				if (@$filter['time']['active']) {
					echo '<td class="timestamp">'.($eval->timestampteacher ? date('d.m.Y', $eval->timestampteacher) : '').'</td>';
				}
				echo '<td class="exarep_studentAssessment" style="padding: 0 10px;">'.$eval->get_student_value_title();
				echo '<td class="exarep_teacherAssessment" style="padding: 0 10px;">'.block_exacomp_format_eval_value($eval->additionalinfo);
				echo '<td class="exarep_exa_evaluation" style="padding: 0 10px;">'.$eval->get_teacher_value_title();
				echo '<td class="exarep_difficultyLevel" style="padding: 0 10px;">'.$eval->get_evalniveau_title();

				$walk_subs($level + 1);
			});
			$output = ob_get_clean();

			if ($output) {
				$has_output = true;

				echo '<h3>'.fullname($student).'</h3>';
				echo '<table class="report_table" border="1" width="100%">';
				echo '<thead><th style="width: 4%"></th><th style="width: 65%"></th>';
				if (@$filter['time']['active']) {
				    echo '<th>'.block_exacomp_get_string('assessment_date').'</th>';
				}
				//echo html_writer::tag('th',block_exacomp_get_string('output_current_assessments'),array('colspan' => "4"));
				echo '<th colspan="4">'.block_exacomp_get_string('output_current_assessments').'</th>';
				echo '<tr>';
                echo '<th class="heading"></th>';
                echo '<th class="heading"></th>';
                echo '<th class="heading" class="studentAssessment">'.block_exacomp_get_string('student_assessment').'</th>';
                echo '<th class="heading" class="teacherAssessment">'.block_exacomp_get_string('teacher_assessment').'</th>';
                echo '<th class="heading" class="exa_evaluation">'.block_exacomp_get_string('exa_evaluation').'</th>';
                echo '<th class="heading"class="difficultyLevel">'.block_exacomp_get_string('difficulty_group_report').'</th>';
                echo "<tbody>";
                echo $output;
				echo '</table>';
			}
		}

		if (!$has_output) {
			echo block_exacomp_get_string('no_entries_found');
		}
	}

	if ($filter['type'] == 'student_counts') {
		$subjects = \block_exacomp\db_layer_course::create($courseid)->get_subjects();

		echo '<table>';
		echo '<tr><th></th><th></th><th colspan="3">'.block_exacomp_get_string('number_of_found_students').' ('.count($students).')</th>';
		

		block_exacomp_tree_walk($subjects, ['filter' => $filter], function($walk_subs, $item, $level = 0) use ($courseid, $filter, $students) {

			$item_type = $item::TYPE;
			if ($item_type == BLOCK_EXACOMP_TYPE_DESCRIPTOR) {
				$item_type = $level > 2 ? BLOCK_EXACOMP_TYPE_DESCRIPTOR_CHILD : BLOCK_EXACOMP_TYPE_DESCRIPTOR_PARENT;
			}

			$item_filter = (array)@$filter[$item_type];

			$visible = @$item_filter['visible'];

			if ($visible) {
				$count = 0;
				foreach ($students as $student) {
					$studentid = $student->id;

					$eval = block_exacomp_get_comp_eval_merged($courseid, $studentid, $item);

					if (@$item_filter[BLOCK_EXACOMP_EVAL_INPUT_EVALNIVEAUID]) {
						$value = @$eval->evalniveauid ?: 0;
						if (!in_array($value, $item_filter[BLOCK_EXACOMP_EVAL_INPUT_EVALNIVEAUID])) {
							continue;
						}
					}
					if (@$item_filter['additionalinfo_from']) {
						$value = @$eval->additionalinfo ?: 0;
						if ($value < str_replace(',', '.', $item_filter['additionalinfo_from'])) {
							continue;
						}
					}
					if (@$item_filter['additionalinfo_to']) {
						$value = @$eval->additionalinfo ?: 0;
						if ($value > str_replace(',', '.', $item_filter['additionalinfo_to'])) {
							continue;
						}
					}

					if (@$item_filter[BLOCK_EXACOMP_EVAL_INPUT_TACHER_EVALUATION]) {
						$value = @$eval->teacherevaluation === null ? -1 : @$eval->teacherevaluation;
						if (!in_array($value, $item_filter[BLOCK_EXACOMP_EVAL_INPUT_TACHER_EVALUATION])) {
							continue;
						}
					}
					if (@$item_filter[BLOCK_EXACOMP_EVAL_INPUT_STUDENT_EVALUATION]) {
						$value = @$eval->studentevaluation ?: 0;
						if (!in_array($value, $item_filter[BLOCK_EXACOMP_EVAL_INPUT_STUDENT_EVALUATION])) {
							continue;
						}
					}

					if (@$filter['time']['active'] && @$filter['time']['from'] && $eval->timestampteacher < @$filter['time']['from']) {
						continue;
					}
					if (@$filter['time']['active'] && @$filter['time']['to'] && $eval->timestampteacher > @$filter['time']['to']) {
						continue;
					}

					$count++;
				}

				echo '<tr>';
				echo '<td style="white-space: nowrap">'.$item->get_numbering();
				echo '<td style="padding-left: '.(5 + $level * 15).'px">'.$item->title;
				echo '<td style="padding: 0 10px;">'.$count;
			}

			$walk_subs($level + 1);
		});

		echo '</table>';
	}
}

function block_exacomp_group_reports_annex_result($filter) {
    $courseid = g::$COURSE->id;
    $students = block_exacomp_get_students_by_course($courseid);

    //print_r($filter);
    $has_output = false;
    $isDocx = (bool)optional_param('formatDocx', false, PARAM_RAW);
    $dataRow = array();

    if ($filter['selectedStudent'] > 0){
        $students = array($students[$filter['selectedStudent']]);
    }
    foreach ($students as $student) {
        $studentid = $student->id;

        $subjects = \block_exacomp\db_layer_course::create($courseid)->get_subjects();
        block_exacomp_tree_walk($subjects, ['filter' => $filter], function($walk_subs, $item, $level = 0) use ($studentid, $courseid, $filter) {
            $eval = block_exacomp_get_comp_eval_merged($courseid, $studentid, $item);

            $item_type = $item::TYPE;
            if ($item_type == BLOCK_EXACOMP_TYPE_DESCRIPTOR) {
                $item_type = $level > 2 ? BLOCK_EXACOMP_TYPE_DESCRIPTOR_CHILD : BLOCK_EXACOMP_TYPE_DESCRIPTOR_PARENT;
            }

            $item_filter = (array)@$filter[$item_type];
            $item->visible = @$item_filter['visible'];

            if (!@$item_filter['active']) {
                return false;
            }

            $walk_subs($level + 1);

            $item->evaluation = $eval;
        });

        // count of columns
        $colCount = block_exacomp_get_grading_scheme($courseid);

        if ($isDocx) {
            $dataRow[$studentid] = array();
            $dataRow[$studentid]['studentData'] = $student;
            $dataRow[$studentid]['courseData'] = g::$COURSE;
            $dataRow[$studentid]['subjects'] = $subjects;
            /*block_exacomp_tree_walk($subjects, ['filter' => $filter], function($walk_subs, $item, $level = 0) use (
                    $studentid, $courseid, $filter, &$dataRow
            ) {
                $eval = block_exacomp_get_comp_eval_merged($courseid, $studentid, $item);

            });*/
        } else {
            echo '<hr>';
            echo '<h1>'.block_exacomp_get_string('tab_teacher_report_annex_title').'</h1>';
            echo '<h2>'.fullname($student).'</h2>';
            echo '<h3>'.g::$COURSE->fullname.'</h3>';

            $firstSubject = true;
            $has_subject_results = false;

            ob_start();
            block_exacomp_tree_walk($subjects, ['filter' => $filter], function($walk_subs, $item, $level = 0) use (
                    $studentid, $courseid, $filter, $colCount, &$firstSubject, &$has_subject_results
            ) {
                $eval = block_exacomp_get_comp_eval_merged($courseid, $studentid, $item);

                if (!$item->visible) {
                    // walk subs with same level
                    $walk_subs($level);
                    return;
                }

                //item_type is needed to distinguish between topics, parent descripors and child descriptors --> important for css-styling
                $item_type = $item::TYPE;
                $selectedEval = $eval->teacherevaluation;
                if ($selectedEval > 0 || $item_type == BLOCK_EXACOMP_TYPE_SUBJECT) {
                    switch ($item_type) {
                        case BLOCK_EXACOMP_TYPE_SUBJECT:
                            $has_subject_results = false;
                            // table wrapping with Subject title
                            if (!$firstSubject) {
                                echo "</tbody>";
                                echo '</table>';
                            } else {
                                $firstSubject = false;
                            }
                            echo '<br><h3>'.$item->title.'</h3>';
                            echo '<table class="report_table" border="1" width="100%" style="margin-bottom: 25px;">';
                            echo '<thead>';
                            echo '<tr>';
                            echo '<th class="heading">'.block_exacomp_get_string('descriptor').'</th>';
                            echo '<th class="heading">'.block_exacomp_get_string('taxonomy').'</th>';
                            for ($i = 0; $i <= $colCount; $i++) {
                                echo '<th class="heading">'.$i.'</th>';
                            }
                            echo '</tr></thead>';
                            echo "<tbody>";
                            break;
                        case BLOCK_EXACOMP_TYPE_TOPIC:
                            echo '<tr class="exarep_topic_row">';
                            break;
                        case BLOCK_EXACOMP_TYPE_DESCRIPTOR:
                            if ($level <= 2) {
                                echo '<tr class="exarep_descriptor_parent_row">';
                            } else if ($level > 2) {
                                echo '<tr class="exarep_descriptor_child_row">';
                            }
                            break;
                        case BLOCK_EXACOMP_TYPE_EXAMPLE:
                            echo '<tr class="exarep_example_row">';
                            break;
                    }

                    if ($item_type != BLOCK_EXACOMP_TYPE_SUBJECT) {
                        $has_subject_results = true;
                        echo '<td class="exarep_descriptorText" style="padding-left: '.(5 + $level * 15).'px">'.
                                $item->get_numbering().' '.$item->title.'</td>';
                        //echo '<pre>'.print_r($item,true).'</pre>';
                        echo '<td style="padding: 0 10px;">'.$eval->get_evalniveau_title().'</td>';
                        for ($i = 0; $i <= $colCount; $i++) {
                            echo '<td style="padding: 0 10px;">';
                            if ($selectedEval == $i) {
                                echo 'X';
                            }
                            echo '</td>';
                        }
                        echo '</tr>';
                    }
                }
                $walk_subs($level + 1);
            });
            echo "</tbody>";
            echo '</table>';
            if (!$has_subject_results) {
                echo block_exacomp_get_string('no_entries_found');
            }
        }

    }

    if ($isDocx) {
        \block_exacomp\printer::block_exacomp_generate_report_annex_docx($dataRow);
        exit;
    }

}


function block_exacomp_update_evaluation_niveau_tables() {
	$evaluation_niveau = block_exacomp_evaluation_niveau_type();

	if ($evaluation_niveau == 1) {
		$titles = array(1 => 'G', 2 => 'M', 3 => 'E', 101 => 'Z');
	} elseif ($evaluation_niveau == 2) {
		$titles = array(1 => 'A', 2 => 'B', 3 => 'C');
	} elseif ($evaluation_niveau == 3) {
		$titles = array(1 => '1', 2 => '2', 3 => '3');
	} else {
		return;
	}

	g::$DB->delete_records(BLOCK_EXACOMP_DB_EVALUATION_NIVEAU);

	//fill table
	foreach ($titles as $id => $title) {
		$entry = new stdClass();
		$entry->title = $title;
		$entry->id = $id;
		// to insert record with a specific id, use insert_record_raw and set $customsequence = true
		g::$DB->insert_record_raw(BLOCK_EXACOMP_DB_EVALUATION_NIVEAU, $entry, false, false, true);
	}
}
