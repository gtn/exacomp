<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

defined('MOODLE_INTERNAL') || die();

use block_exacomp\globals as g;
use Super\Cache;

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/classes.php';
require_once __DIR__ . '/../block_exacomp.php';
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->libdir . '/badgeslib.php');
require_once($CFG->dirroot . '/badges/lib/awardlib.php');
require_once($CFG->dirroot . '/cohort/lib.php');
require_once __DIR__ . '/setapp.php';

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
const BLOCK_EXACOMP_DB_ITEM_MM = 'block_exacompitem_mm';
const BLOCK_EXACOMP_DB_SUBJECT_NIVEAU_MM = 'block_exacompsubjniveau_mm';
const BLOCK_EXACOMP_DB_EXTERNAL_TRAINERS = 'block_exacompexternaltrainer';
const BLOCK_EXACOMP_DB_EVALUATION_NIVEAU = 'block_exacompeval_niveau';
const BLOCK_EXACOMP_DB_TOPICVISIBILITY = 'block_exacomptopicvisibility';
const BLOCK_EXACOMP_DB_SOLUTIONVISIBILITY = 'block_exacompsolutvisibility';
const BLOCK_EXACOMP_DB_IMPORTTASKS = 'block_exacompimporttasks';
const BLOCK_EXACOMP_DB_GLOBALGRADINGS = 'block_exacompglobalgradings';
const BLOCK_EXACOMP_DB_DESCRIPTOR_QUESTION = 'block_exacompdescrquest_mm';
const BLOCK_EXACOMP_DB_ITEM_COLLABORATOR_MM = 'block_exacompitemcollab_mm';
const BLOCK_EXACOMP_DB_EXAMPLE_ANNOTATION = 'block_exacompexampannotation';

/**
 * PLUGIN ROLES
 */
const BLOCK_EXACOMP_ROLE_STUDENT = 0;
const BLOCK_EXACOMP_ROLE_TEACHER = 1;
const BLOCK_EXACOMP_ROLE_SYSTEM = 2; // used for automatic grading

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

const BLOCK_EXACOMP_GRADING_POSTIVE = 0;
const BLOCK_EXACOMP_GRADING_SOSO = 1;
const BLOCK_EXACOMP_GRADING_NEGATIVE = 2;

const BLOCK_EXACOMP_COURSE_POINT_LIMIT = 100;
const BLOCK_EXACOMP_DATA_SOURCE_CUSTOM = 3;
const BLOCK_EXACOMP_EXAMPLE_SOURCE_TEACHER = 3;
const BLOCK_EXACOMP_EXAMPLE_SOURCE_USER = 4;
const BLOCK_EXACOMP_EXAMPLE_SOURCE_USER_FREE_ELEMENT = 5;

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
const BLOCK_EXACOMP_MODULES_PER_COLUMN = 25;
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

const BLOCK_EXACOMP_EVAL_INPUT_TACHER_EVALUATION = 'teacherevaluation';
const BLOCK_EXACOMP_EVAL_INPUT_STUDENT_EVALUATION = 'studentevaluation';
const BLOCK_EXACOMP_EVAL_INPUT_ADDITIONALINFO = 'additionalinfo';
const BLOCK_EXACOMP_EVAL_INPUT_EVALNIVEAUID = 'evalniveauid';

const BLOCK_EXACOMP_ASSESSMENT_TYPE_NONE = 0;
const BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE = 1;
const BLOCK_EXACOMP_ASSESSMENT_TYPE_VERBOSE = 2;
const BLOCK_EXACOMP_ASSESSMENT_TYPE_POINTS = 3;
const BLOCK_EXACOMP_ASSESSMENT_TYPE_YESNO = 4;

const BLOCK_EXACOMP_IS_GLOBAL = 'isglobal';

// Item status for diggrplus
const BLOCK_EXACOMP_ITEM_STATUS_INPROGRESS = 0;
const BLOCK_EXACOMP_ITEM_STATUS_SUBMITTED = 1;
const BLOCK_EXACOMP_ITEM_STATUS_COMPLETED = 2;

// data for multiple using
$block_exacomp_topic_used_values = array();
$block_exacomp_descriptor_used_values = array();
$block_exacomp_example_used_values = array();

// course specific assessment configuration
$block_exacomp_assessment_configurations = array();

/**
 * get the assessemnt preconfigurations from the xml, but only load it once (global)
 *
 * @return array
 */
function block_exacomp_get_assessment_configurations() {
    global $DB, $block_exacomp_assessment_configurations;

    if (!empty($block_exacomp_assessment_configurations)) {
        return $block_exacomp_assessment_configurations;
    } else {
        $block_exacomp_assessment_configurations = block_exacomp_read_preconfigurations_xml();
    }
    return $block_exacomp_assessment_configurations;
}

/**
 * access configuration setting via functions
 */
function block_exacomp_is_skillsmanagement() {
    return !empty(g::$CFG->is_skillsmanagement);
}

function block_exacomp_is_topicgrading_enabled($courseid = 0) {
    global $DB, $COURSE;
    if ($courseid == 0) {
        $courseid = $COURSE->id;
    }
    $assessment_config = $DB->get_field('block_exacompsettings', 'assessmentconfiguration', ['courseid' => $courseid]);
    if ($assessment_config != 0) {
        $configurations = block_exacomp_get_assessment_configurations();
        $topicscheme = $configurations[$assessment_config]["assessment_topic_scheme"];
    } else {
        $topicscheme = get_config('exacomp', 'assessment_topic_scheme');
    }
    return ($topicscheme ? true : false);
}

function block_exacomp_is_subjectgrading_enabled($courseid = 0) {
    global $DB, $COURSE;
    if ($courseid == 0) {
        $courseid = $COURSE->id;
    }
    $assessment_config = $DB->get_field('block_exacompsettings', 'assessmentconfiguration', ['courseid' => $courseid]);
    if ($assessment_config != 0) {
        $configurations = block_exacomp_get_assessment_configurations();
        $subjectscheme = $configurations[$assessment_config]["assessment_subject_scheme"];
    } else {
        $subjectscheme = get_config('exacomp', 'assessment_subject_scheme');
    }

    return ($subjectscheme ? true : false);
}

function block_exacomp_is_numbering_enabled() {
    return get_config('exacomp', 'usenumbering');
}

/**
 * wrote own function, so eclipse knows which type the output renderer is
 *
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
function block_exacomp_init_js_css($courseid = 0) {
    global $PAGE, $CFG;

    // only allowed to be called once
    static $js_inited = false;
    if ($js_inited) {
        return;
    }
    $js_inited = true;

    // js/css for whole block
    $exacomp_config = array( //
        0 => array('grade_limit' => block_exacomp_get_assessment_grade_limit($courseid)),
    );
    $PAGE->requires->js_init_call('$E.set_exacomp_config', $exacomp_config);
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
        'override_notice1', 'override_notice2', 'unload_notice', 'example_sorting_notice', 'delete_unconnected_examples',
        'value_too_large', 'value_too_low', 'value_not_allowed', 'hide_solution', 'show_solution', 'weekly_schedule',
        'pre_planning_storage', 'weekly_schedule_disabled', 'pre_planning_storage_disabled',
        'add_example_for_all_students_to_schedule_confirmation', 'seperatordaterange', 'selfevaluation',
        'topic_3dchart_empty', 'columnselect', 'n1.unit', 'n2.unit', 'n3.unit', 'n4.unit', 'n5.unit', 'n6.unit', 'n7.unit',
        'n8.unit', 'n9.unit', 'n10.unit', 'save_changes_competence_evaluation', 'dismiss_gradingisold',
        'donotleave_page_message',
        'pre_planning_materials_assigned',
        'delete_ics_imports_confirmation',
        'import_ics_loading_time',
        'ics_provide_link_text',
    ],
        'block_exacomp'
    //['5' => sprintf("%.1f", block_exacomp_get_assessment_grade_limit())] // Important to keep array keys!!  5 => value_too_large. Disabled now. Using JS direct value
    );

    // page specific js/css
    $scriptName = preg_replace('!\.[^\.]+$!', '', basename($_SERVER['PHP_SELF']));
    if (file_exists($CFG->dirroot . '/blocks/exacomp/css/' . $scriptName . '.css')) {
        $PAGE->requires->css('/blocks/exacomp/css/' . $scriptName . '.css');
    }
    if (file_exists($CFG->dirroot . '/blocks/exacomp/javascript/' . $scriptName . '.js')) {
        $PAGE->requires->js('/blocks/exacomp/javascript/' . $scriptName . '.js', true);
    }
}

function block_exacomp_init_js_weekly_schedule() {
    global $PAGE;

    $PAGE->requires->jquery_plugin('ui');
    $PAGE->requires->jquery_plugin('ui-css');

    $PAGE->requires->css('/blocks/exacomp/javascript/fullcalendar/fullcalendar.css');
    $PAGE->requires->js('/blocks/exacomp/javascript/fullcalendar/moment.min.js', true);
    // fullcalendar.js has a few changes from origin: regarding to language wordings
    // This fullcalendar.js can broke working of Moodle JS merging
    // So - generate fullcalendar.min.js and use it
    //    $PAGE->requires->js('/blocks/exacomp/javascript/fullcalendar/fullcalendar.js', true);
    $PAGE->requires->js('/blocks/exacomp/javascript/fullcalendar/fullcalendar.min.js', true);
    $PAGE->requires->js('/blocks/exacomp/javascript/fullcalendar/locale-all.js', true);
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
        throw new \moodle_exception('wrong courseid type ' . gettype($courseid));
    }
}

/**
 *
 * @param courseid or context $context
 * @param userid $userid
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
    $courses = course_get_enrolled_courses_for_logged_in_user();

    foreach ($courses as $course) {
        if (block_exacomp_is_teacher($course->id)) {
            return true;
        }
    }

    return false;
}

function block_exacomp_get_teacher_courses($userid) {
    $courses = block_exacomp_get_exacomp_courses($userid);
    foreach ($courses as $key => $course) {
        if (!block_exacomp_is_teacher(context_course::instance($course->id), $userid)) {
            unset($courses[$key]);
        }
    }
    return $courses;
}

//Get all courses a teacher is enrolled
function block_exacomp_get_courses_of_teacher($userid) {
    $courses = block_exacomp_get_courseids();
    $teachersCourses = array();

    foreach ($courses as $course) {
        if (block_exacomp_is_teacher($course)) {
            $teachersCourses[] = $course;
        }
    }
    return $teachersCourses;
}

// Get all courses a student is enrolled
function block_exacomp_get_courses_of_student($userid) {
    $courses = block_exacomp_get_courseids();
    $studentCourses = array();

    //    var_dump($courses);

    foreach ($courses as $course) {
        if (block_exacomp_is_student($course)) {

            $studentCourses[] = $course;
        }
    }
    return $studentCourses;
}

/**
 *
 * @param courseid or context $context
 */
function block_exacomp_is_student($context = null) {
    $context = block_exacomp_get_context_from_courseid($context);

    //    echo has_capability('block/exacomp:teacher', $context);
    //    echo has_capability('block/exacomp:student', $context);

    // a teacher can not be a student in the same course   RW TODO, this leads to problems, check why
    //	return has_capability('block/exacomp:student', $context) && !has_capability('block/exacomp:teacher', $context);
    return has_capability('block/exacomp:student', $context);
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

function block_exacomp_use_old_activities_method() {
    if (get_config('exacomp', 'assign_activities_old_method')) {
        return true;
    }
    return false;
}

function block_exacomp_use_eval_niveau($courseid = 0) {
    $evaluation_niveau = block_exacomp_get_assessment_diffLevel_options($courseid);
    return $evaluation_niveau != '';
    //return $evaluation_niveau >= 1 && $evaluation_niveau <= 3;
}

function block_exacomp_get_assessment_any_diffLevel_exist($courseid = 0) {
    return (block_exacomp_get_assessment_subject_diffLevel($courseid) == 1
    || block_exacomp_get_assessment_topic_diffLevel($courseid) == 1
    || block_exacomp_get_assessment_comp_diffLevel($courseid) == 1
    || block_exacomp_get_assessment_childcomp_diffLevel($courseid) == 1
    || block_exacomp_get_assessment_example_diffLevel($courseid) == 1 ? true : false);
}

/**
 * @return mixed
 * @deprecated
 */
function block_exacomp_evaluation_niveau_type() {
    return get_config('exacomp', 'adminscheme');
}

/**
 * @return mixed
 * @var boolean $level
 */
function block_exacomp_additional_grading($level = null, $courseid = 0) {
    switch ($level) {
        case BLOCK_EXACOMP_TYPE_DESCRIPTOR:
        case BLOCK_EXACOMP_TYPE_DESCRIPTOR_PARENT:
            return block_exacomp_get_assessment_comp_scheme($courseid);
        case BLOCK_EXACOMP_TYPE_TOPIC:
            return block_exacomp_get_assessment_topic_scheme($courseid);
        case BLOCK_EXACOMP_TYPE_CROSSSUB:
            return block_exacomp_get_assessment_theme_scheme($courseid);
        case BLOCK_EXACOMP_TYPE_SUBJECT:
            return block_exacomp_get_assessment_subject_scheme($courseid);
        case BLOCK_EXACOMP_TYPE_EXAMPLE:
            return block_exacomp_get_assessment_example_scheme($courseid);
        case BLOCK_EXACOMP_TYPE_DESCRIPTOR_CHILD:
            return block_exacomp_get_assessment_childcomp_scheme($courseid);
        default:
            return false;
    }
    return false;
    //return get_config('exacomp', 'additional_grading');
}

/**
 * is there used at least one levele with using of needed type
 *
 * @return bool
 * @var int $type
 */
function block_exacomp_additional_grading_used_type($type, $courseid = 0) {
    $levels = array(BLOCK_EXACOMP_TYPE_DESCRIPTOR, BLOCK_EXACOMP_TYPE_TOPIC, BLOCK_EXACOMP_TYPE_CROSSSUB,
        BLOCK_EXACOMP_TYPE_SUBJECT, BLOCK_EXACOMP_TYPE_EXAMPLE, BLOCK_EXACOMP_TYPE_DESCRIPTOR_CHILD);
    foreach ($levels as $level) {
        if (block_exacomp_additional_grading($level, $courseid) == $type) {
            return true;
        }
    }
    return false;
}

// function block_exacomp_get_assessment_limits() {
//     $points_limit = get_config('exacomp', 'assessment_points_limit');
//     $grade_limit = get_config('exacomp', 'assessment_grade_limit');
//     return array($points_limit,$grad_limit);
// }

function block_exacomp_get_assessment_points_limit($onlyGlobal = true, $courseid = 0) {
    global $DB, $COURSE;

    // is this deprecated? RW 2021_07_20
    if (!$onlyGlobal) {
        // if we have courseid and we have at leat one level, which uses Points - we can use custom points limit for vourse
        $courseid = optional_param('courseid', 0, PARAM_INT);
        if (!$courseid) {
            $courseid = g::$COURSE->id;
        }
        $useprofoundness = get_config('exacomp', 'useprofoundness'); // if not selected - can be used custom limits
        if ($courseid && !$useprofoundness && block_exacomp_additional_grading_used_type(BLOCK_EXACOMP_ASSESSMENT_TYPE_POINTS, $courseid)) {
            $limitForCourse = $DB->get_field('block_exacompsettings', 'grading', ['courseid' => $courseid]);
            if ($limitForCourse && $limitForCourse > 1) { // if = 1 - please use Yes/No type
                return $limitForCourse;
            }
        }
    }

    if ($courseid == 0) {
        $courseid = $COURSE->id;
    }
    $assessment_config = $DB->get_field('block_exacompsettings', 'assessmentconfiguration', ['courseid' => $courseid]);
    if ($assessment_config != 0) {
        $configurations = block_exacomp_get_assessment_configurations();
        return (int)$configurations[$assessment_config]["assessment_points_limit"];
    } else {
        return (int)get_config('exacomp', 'assessment_points_limit');
    }
}

function block_exacomp_get_assessment_grade_limit($courseid = 0) {
    global $DB, $COURSE;

    //    static $value;
    //    if ($value !== null) {
    //        return $value;
    //    }

    if ($courseid == 0) {
        $courseid = $COURSE->id;
    }
    $assessment_config = $DB->get_field('block_exacompsettings', 'assessmentconfiguration', ['courseid' => $courseid]);
    if ($assessment_config != 0) {
        $configurations = block_exacomp_get_assessment_configurations();
        $value = $configurations[$assessment_config]["assessment_grade_limit"];
    } else {
        $value = get_config('exacomp', 'assessment_grade_limit');
    }

    return (int)$value;
    //return get_config('exacomp', 'assessment_grade_limit');
}

function block_exacomp_get_assessment_grade_verbose($getforlanguage = null, $courseid = 0) {
    //    static $value;
    //    if ($value !== null && array_key_exists($getforlanguage, $value)) {
    //        return $value[$getforlanguage];
    //    }
    $value = array();
    $value[$getforlanguage] = block_exacomp_get_translatable_parameter('assessment_grade_verbose', $getforlanguage, $courseid);
    return $value[$getforlanguage];
    //return block_exacomp_get_translatable_parameter('assessment_grade_verbose', $getforlanguage);
    //return get_config('exacomp', 'assessment_grade_verbose');
}

function block_exacomp_value_is_negative_by_assessment($value, $level, $withEqual = true, $courseid = 0) {
    $limit = block_exacomp_get_assessment_negative_threshold($level, $courseid);
    $scheme = block_exacomp_additional_grading($level, $courseid);
    switch ($scheme) {
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_NONE:
            return true; // TODO: always negative?
            break;
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE:
            if ($withEqual) {
                if ($value >= $limit) {
                    return true;
                }
            } else {
                if ($value > $limit) {
                    return true;
                }
            }
            break;
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_VERBOSE:
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_POINTS:
            if ($withEqual) {
                if ($value <= $limit) {
                    return true;
                }
            } else {
                if ($value < $limit) {
                    return true;
                }
            }
            break;
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_YESNO:
            if ($value <= 1) {
                return true; // TODO: is this ok condition?
            }
            break;
    }
    return false;
}

function block_exacomp_get_assessment_negative_threshold($level, $courseid = 0) {
    $type = block_exacomp_additional_grading($level, $courseid);
    switch ($type) {
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_NONE:
            return 0;
            break;
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE:
            return block_exacomp_get_assessment_grade_negative_threshold($courseid);
            break;
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_VERBOSE:
            return block_exacomp_get_assessment_verbose_negative_threshold($courseid);
            break;
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_POINTS:
            return block_exacomp_get_assessment_points_negative_threshold($courseid);
            break;
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_YESNO:
            return 1; // yes
            break;
    }
}

function block_exacomp_get_assessment_points_negative_threshold($courseid = 0) {
    global $DB, $COURSE;
    if ($courseid == 0) {
        $courseid = $COURSE->id;
    }
    $assessment_config = $DB->get_field('block_exacompsettings', 'assessmentconfiguration', ['courseid' => $courseid]);
    if ($assessment_config != 0) {
        $configurations = block_exacomp_get_assessment_configurations();
        return (int)$configurations[$assessment_config]["assessment_points_negativ"];
    } else {
        return (int)get_config('exacomp', 'assessment_points_negativ');
    }
}

function block_exacomp_get_assessment_grade_negative_threshold($courseid = 0) {
    global $DB, $COURSE;
    if ($courseid == 0) {
        $courseid = $COURSE->id;
    }
    $assessment_config = $DB->get_field('block_exacompsettings', 'assessmentconfiguration', ['courseid' => $courseid]);
    if ($assessment_config != 0) {
        $configurations = block_exacomp_get_assessment_configurations();
        return (int)$configurations[$assessment_config]["assessment_grade_negativ"];
    } else {
        return (int)get_config('exacomp', 'assessment_grade_negativ');
    }

}

function block_exacomp_get_assessment_verbose_negative_threshold($courseid = 0) {
    global $DB, $COURSE;
    if ($courseid == 0) {
        $courseid = $COURSE->id;
    }
    $assessment_config = $DB->get_field('block_exacompsettings', 'assessmentconfiguration', ['courseid' => $courseid]);
    if ($assessment_config != 0) {
        $configurations = block_exacomp_get_assessment_configurations();
        return (int)$configurations[$assessment_config]["assessment_verbose_negative"];
    } else {
        return (int)get_config('exacomp', 'assessment_verbose_negative');
    }

}

function block_exacomp_get_assessment_diffLevel_options($courseid = 0) {
    global $DB, $COURSE;

    //    static $value;
    //    if ($value !== null) {
    //        return $value;
    //    }

    if ($courseid == 0) {
        $courseid = $COURSE->id;
    }
    $assessment_config = $DB->get_field('block_exacompsettings', 'assessmentconfiguration', ['courseid' => $courseid]);
    if ($assessment_config != 0) {
        $configurations = block_exacomp_get_assessment_configurations();
        $value = $configurations[$assessment_config]["assessment_diffLevel_options"];
    } else {
        $value = trim(get_config('exacomp', 'assessment_diffLevel_options'));
    }

    return $value;
    //return trim(get_config('exacomp', 'assessment_diffLevel_options'));
}

function block_exacomp_get_assessment_diffLevel_options_splitted($courseid = 0) {
    // this static value is faster BUT: it breaks course specific settings on e.g. the competence profile
    //    static $value;
    //    if ($value !== null) {
    //        return $value;
    //    }
    $value = preg_split("/[\s*,\s*]*,+[\s*,\s*]*/", block_exacomp_get_assessment_diffLevel_options($courseid));
    // indexes - from 1: like ID in database
    $value = array_combine(range(1, count($value)), array_values($value));
    return $value;
    //return trim(get_config('exacomp', 'assessment_diffLevel_options'));
}

function block_exacomp_get_assessment_verbose_options($getforlanguage = null, $courseid = 0) {
    //    static $value;
    //    if ($value !== null && array_key_exists($getforlanguage, $value)) {
    //        return $value[$getforlanguage];
    //    }
    $value = array();
    $value[$getforlanguage] = block_exacomp_get_translatable_parameter('assessment_verbose_options', $getforlanguage, $courseid);
    return $value[$getforlanguage];
    //return block_exacomp_get_translatable_parameter('assessment_verbose_options', $getforlanguage);
}

function block_exacomp_get_assessment_verbose_options_short($getforlanguage = null, $courseid = 0) {
    //    static $value;
    //    if ($value !== null && array_key_exists($getforlanguage, $value)) {
    //        return $value[$getforlanguage];
    //    }
    $value = array();
    $value[$getforlanguage] = block_exacomp_get_translatable_parameter('assessment_verbose_options_short', $getforlanguage, $courseid);
    return $value[$getforlanguage];
    //return block_exacomp_get_translatable_parameter('assessment_verbose_options_short', $getforlanguage);
}

function block_exacomp_get_assessment_diffLevel_verb($value, $courseid = 0) {
    $difflevels = block_exacomp_get_assessment_diffLevel_options_splitted($courseid);
    // start from 1
    $difflevels = array_combine(range(1, count($difflevels)), array_values($difflevels));
    if (array_key_exists($value, $difflevels)) {
        return $difflevels[$value];
    }
    return null;
}

function block_exacomp_get_assessment_selfEval_verboses($level = 'example', $type = 'long', $getforlanguage = null, $courseid = 0) {
    //    static $value;
    if ($level != 'example') {
        $level = 'comp';
    }
    //    if ($value !== null && array_key_exists($getforlanguage.$level.$type, $value)) {
    //        return $value[$getforlanguage.$level.$type];
    //    }
    $value = array();
    $value[$getforlanguage . $level . $type] = block_exacomp_get_translatable_parameter('assessment_selfEvalVerbose_' . $level . '_' . $type, $getforlanguage, $courseid);
    return $value[$getforlanguage . $level . $type];
    //return block_exacomp_get_translatable_parameter('assessment_selfEvalVerbose_'.$level.'_'.$type, $getforlanguage);
}

function block_exacomp_get_assessment_example_scheme($courseid = 0) {
    global $DB, $COURSE;
    //    static $value;
    //    if ($value !== null) {
    //        return $value;
    //    }

    if ($courseid == 0) {
        $courseid = $COURSE->id;
    }
    $assessment_config = $DB->get_field('block_exacompsettings', 'assessmentconfiguration', ['courseid' => $courseid]);
    if ($assessment_config != 0) {
        $configurations = block_exacomp_get_assessment_configurations();
        $value = (int)$configurations[$assessment_config]["assessment_example_scheme"];
    } else {
        $value = (int)get_config('exacomp', 'assessment_example_scheme');
    }

    return $value;
    //return get_config('exacomp', 'assessment_example_scheme');
}

function block_exacomp_get_assessment_example_diffLevel($courseid = 0) {
    global $DB, $COURSE;
    //    static $value;
    //    if ($value !== null) {
    //        return $value;
    //    }

    if ($courseid == 0) {
        $courseid = $COURSE->id;
    }
    $assessment_config = $DB->get_field('block_exacompsettings', 'assessmentconfiguration', ['courseid' => $courseid]);
    if ($assessment_config != 0) {
        $configurations = block_exacomp_get_assessment_configurations();
        $value = $configurations[$assessment_config]["assessment_example_diffLevel"];
    } else {
        $value = get_config('exacomp', 'assessment_example_diffLevel');
    }
    return $value;
    //return get_config('exacomp', 'assessment_example_diffLevel');
}

function block_exacomp_get_assessment_example_SelfEval($courseid = 0) {
    global $DB, $COURSE;
    //    static $value;
    //    if ($value !== null) {
    //        return $value;
    //    }

    if ($courseid == 0) {
        $courseid = $COURSE->id;
    }
    $assessment_config = $DB->get_field('block_exacompsettings', 'assessmentconfiguration', ['courseid' => $courseid]);
    if ($assessment_config != 0) {
        $configurations = block_exacomp_get_assessment_configurations();
        $value = $configurations[$assessment_config]["assessment_example_SelfEval"];
    } else {
        $value = get_config('exacomp', 'assessment_example_SelfEval');
    }

    return $value;
    //return get_config('exacomp', 'assessment_example_SelfEval');
}

function block_exacomp_get_assessment_childcomp_scheme($courseid = 0) {
    global $DB, $COURSE;
    //    static $value;
    //    if ($value !== null) {
    //        return $value;
    //    }

    if ($courseid == 0) {
        $courseid = $COURSE->id;
    }
    $assessment_config = $DB->get_field('block_exacompsettings', 'assessmentconfiguration', ['courseid' => $courseid]);
    if ($assessment_config != 0) {
        $configurations = block_exacomp_get_assessment_configurations();
        $value = $configurations[$assessment_config]["assessment_childcomp_scheme"];
    } else {
        $value = get_config('exacomp', 'assessment_childcomp_scheme');
    }

    return (int)$value;
    //return get_config('exacomp', 'assessment_childcomp_scheme');
}

function block_exacomp_get_assessment_childcomp_diffLevel($courseid = 0) {
    global $DB, $COURSE;
    //    static $value;
    //    if ($value !== null) {
    //        return $value;
    //    }

    if ($courseid == 0) {
        $courseid = $COURSE->id;
    }
    $assessment_config = $DB->get_field('block_exacompsettings', 'assessmentconfiguration', ['courseid' => $courseid]);
    if ($assessment_config != 0) {
        $configurations = block_exacomp_get_assessment_configurations();
        $value = $configurations[$assessment_config]["assessment_childcomp_diffLevel"];
    } else {
        $value = get_config('exacomp', 'assessment_childcomp_diffLevel');
    }

    return $value;
    //return get_config('exacomp', 'assessment_childcomp_diffLevel');
}

function block_exacomp_get_assessment_childcomp_SelfEval($courseid = 0) {
    global $DB, $COURSE;
    //    static $value;
    //    if ($value !== null) {
    //        return $value;
    //    }

    if ($courseid == 0) {
        $courseid = $COURSE->id;
    }
    $assessment_config = $DB->get_field('block_exacompsettings', 'assessmentconfiguration', ['courseid' => $courseid]);
    if ($assessment_config != 0) {
        $configurations = block_exacomp_get_assessment_configurations();
        $value = $configurations[$assessment_config]["assessment_childcomp_SelfEval"];
    } else {
        $value = get_config('exacomp', 'assessment_childcomp_SelfEval');
    }

    return $value;
    //return get_config('exacomp', 'assessment_childcomp_SelfEval');
}

function block_exacomp_get_assessment_comp_scheme($courseid = 0) {
    global $DB, $COURSE;

    //    static $value;
    //    if ($value !== null) {
    //        return $value;
    //    }

    if ($courseid == 0) {
        $courseid = $COURSE->id;
    }
    $assessment_config = $DB->get_field('block_exacompsettings', 'assessmentconfiguration', ['courseid' => $courseid]);
    if ($assessment_config != 0) {
        $configurations = block_exacomp_get_assessment_configurations();
        $value = $configurations[$assessment_config]["assessment_comp_scheme"];
    } else {
        $value = get_config('exacomp', 'assessment_comp_scheme');
    }

    return (int)$value;
    //return get_config('exacomp', 'assessment_comp_scheme');
}

function block_exacomp_get_assessment_comp_diffLevel($courseid = 0) {
    global $DB, $COURSE;

    //    static $value;
    //    if ($value !== null) {
    //        return $value;
    //    }

    if ($courseid == 0) {
        $courseid = $COURSE->id;
    }
    $assessment_config = $DB->get_field('block_exacompsettings', 'assessmentconfiguration', ['courseid' => $courseid]);
    if ($assessment_config != 0) {
        $configurations = block_exacomp_get_assessment_configurations();
        $value = $configurations[$assessment_config]["assessment_comp_diffLevel"];
    } else {
        $value = get_config('exacomp', 'assessment_comp_diffLevel');
    }

    return $value;
    //return get_config('exacomp', 'assessment_comp_diffLevel');
}

function block_exacomp_get_assessment_comp_SelfEval($courseid = 0) {
    global $DB, $COURSE;

    //    static $value;
    //    if ($value !== null) {
    //        return $value;
    //    }

    if ($courseid == 0) {
        $courseid = $COURSE->id;
    }
    $assessment_config = $DB->get_field('block_exacompsettings', 'assessmentconfiguration', ['courseid' => $courseid]);
    if ($assessment_config != 0) {
        $configurations = block_exacomp_get_assessment_configurations();
        $value = $configurations[$assessment_config]["assessment_comp_SelfEval"];
    } else {
        $value = get_config('exacomp', 'assessment_comp_SelfEval');
    }

    return $value;
    //return get_config('exacomp', 'assessment_comp_SelfEval');
}

function block_exacomp_get_assessment_topic_scheme($courseid = 0) {
    global $DB, $COURSE;
    //    static $value;
    //    if ($value !== null) {
    //        return $value;
    //    }

    if ($courseid == 0) {
        $courseid = $COURSE->id;
    }
    $assessment_config = $DB->get_field('block_exacompsettings', 'assessmentconfiguration', ['courseid' => $courseid]);
    if ($assessment_config != 0) {
        $configurations = block_exacomp_get_assessment_configurations();
        $value = $configurations[$assessment_config]["assessment_topic_scheme"];
    } else {
        $value = get_config('exacomp', 'assessment_topic_scheme');
    }

    return (int)$value;
    //return get_config('exacomp', 'assessment_topic_scheme');
}

function block_exacomp_get_assessment_topic_diffLevel($courseid = 0) {
    global $DB, $COURSE;

    //    static $value;
    //    if ($value !== null) {
    //        return $value;
    //    }

    if ($courseid == 0) {
        $courseid = $COURSE->id;
    }
    $assessment_config = $DB->get_field('block_exacompsettings', 'assessmentconfiguration', ['courseid' => $courseid]);
    if ($assessment_config != 0) {
        $configurations = block_exacomp_get_assessment_configurations();
        $value = $configurations[$assessment_config]["assessment_topic_diffLevel"];
    } else {
        $value = get_config('exacomp', 'assessment_topic_diffLevel');
    }

    return $value;
    //return get_config('exacomp', 'assessment_topic_diffLevel');
}

function block_exacomp_get_assessment_topic_SelfEval($courseid = 0) {
    global $DB, $COURSE;

    //    static $value;
    //    if ($value !== null) {
    //        return $value;
    //    }

    if ($courseid == 0) {
        $courseid = $COURSE->id;
    }
    $assessment_config = $DB->get_field('block_exacompsettings', 'assessmentconfiguration', ['courseid' => $courseid]);
    if ($assessment_config != 0) {
        $configurations = block_exacomp_get_assessment_configurations();
        $value = $configurations[$assessment_config]["assessment_topic_SelfEval"];
    } else {
        $value = get_config('exacomp', 'assessment_topic_SelfEval');
    }

    return $value;
    //return get_config('exacomp', 'assessment_topic_SelfEval');
}

function block_exacomp_get_assessment_subject_scheme($courseid = 0) {
    global $DB, $COURSE;
    //    static $value;
    //    if ($value !== null) {
    //        return $value;
    //    }

    if ($courseid == 0) {
        $courseid = $COURSE->id;
    }
    $assessment_config = $DB->get_field('block_exacompsettings', 'assessmentconfiguration', ['courseid' => $courseid]);
    if ($assessment_config != 0) {
        $configurations = block_exacomp_get_assessment_configurations();
        $value = $configurations[$assessment_config]["assessment_subject_scheme"];
    } else {
        $value = get_config('exacomp', 'assessment_subject_scheme');
    }
    return (int)$value;
    //return get_config('exacomp', 'assessment_subject_scheme');
}

function block_exacomp_get_assessment_subject_diffLevel($courseid = 0) {
    global $DB, $COURSE;
    //    static $value;
    //    if ($value !== null) {
    //        return $value;
    //    }

    if ($courseid == 0) {
        $courseid = $COURSE->id;
    }
    $assessment_config = $DB->get_field('block_exacompsettings', 'assessmentconfiguration', ['courseid' => $courseid]);
    if ($assessment_config != 0) {
        $configurations = block_exacomp_get_assessment_configurations();
        $value = $configurations[$assessment_config]["assessment_subject_diffLevel"];
    } else {
        $value = get_config('exacomp', 'assessment_subject_diffLevel');
    }

    return $value;
    //return get_config('exacomp', 'assessment_subject_diffLevel');
}

function block_exacomp_get_assessment_subject_SelfEval($courseid = 0) {
    global $DB, $COURSE;
    //    static $value;
    //    if ($value !== null) {
    //        return $value;
    //    }

    if ($courseid == 0) {
        $courseid = $COURSE->id;
    }
    $assessment_config = $DB->get_field('block_exacompsettings', 'assessmentconfiguration', ['courseid' => $courseid]);
    if ($assessment_config != 0) {
        $configurations = block_exacomp_get_assessment_configurations();
        $value = $configurations[$assessment_config]["assessment_subject_SelfEval"];
    } else {
        $value = get_config('exacomp', 'assessment_subject_SelfEval');
    }

    return $value;
    //return get_config('exacomp', 'assessment_subject_SelfEval');
}

function block_exacomp_get_assessment_theme_scheme($courseid = 0) {
    global $DB, $COURSE;
    //    static $value;
    //    if ($value !== null) {
    //        return $value;
    //    }

    if ($courseid == 0) {
        $courseid = $COURSE->id;
    }
    $assessment_config = $DB->get_field('block_exacompsettings', 'assessmentconfiguration', ['courseid' => $courseid]);
    if ($assessment_config != 0) {
        $configurations = block_exacomp_get_assessment_configurations();
        $value = $configurations[$assessment_config]["assessment_theme_scheme"];
    } else {
        $value = get_config('exacomp', 'assessment_theme_scheme');
    }
    return (int)$value;
    //return get_config('exacomp', 'assessment_theme_scheme');
}

function block_exacomp_get_assessment_theme_diffLevel($courseid = 0) {
    global $DB, $COURSE;
    //    static $value;
    //    if ($value !== null) {
    //        return $value;
    //    }

    if ($courseid == 0) {
        $courseid = $COURSE->id;
    }
    $assessment_config = $DB->get_field('block_exacompsettings', 'assessmentconfiguration', ['courseid' => $courseid]);
    if ($assessment_config != 0) {
        $configurations = block_exacomp_get_assessment_configurations();
        $value = $configurations[$assessment_config]["assessment_theme_diffLevel"];
    } else {
        $value = get_config('exacomp', 'assessment_theme_diffLevel');
    }

    return $value;
    //return get_config('exacomp', 'assessment_theme_diffLevel');
}

function block_exacomp_get_assessment_theme_SelfEval($courseid = 0) {
    global $DB, $COURSE;
    //    static $value;
    //    if ($value !== null) {
    //        return $value;
    //    }

    if ($courseid == 0) {
        $courseid = $COURSE->id;
    }
    $assessment_config = $DB->get_field('block_exacompsettings', 'assessmentconfiguration', ['courseid' => $courseid]);
    if ($assessment_config != 0) {
        $configurations = block_exacomp_get_assessment_configurations();
        $value = $configurations[$assessment_config]["assessment_theme_SelfEval"];
    } else {
        $value = get_config('exacomp', 'assessment_theme_SelfEval');
    }

    return $value;
    //return get_config('exacomp', 'assessment_theme_SelfEval');
}

function block_exacomp_get_assessment_max_value($type, $courseid = 0) {
    $max = 0;
    switch ($type) {
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_NONE:
            break;
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE:
            $max = block_exacomp_get_assessment_grade_limit($courseid);
            break;
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_VERBOSE:
            $verboses = preg_split("/(\/|,) /", block_exacomp_get_assessment_verbose_options(null, $courseid));
            $max = count($verboses);
            if ($max > 0) {
                $max -= 1;  // because it is possible zero
            }
            break;
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_POINTS:
            $max = block_exacomp_get_assessment_points_limit(true, $courseid);
            break;
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_YESNO:
            $max = 1;
            break;
    }
    return $max;
}

function block_exacomp_get_assessment_max_good_value($type, $userrealvalue = false, $maxGrade = null, $studentGradeResult = null, $courseid = 0) { // for example 1 - good, 6 - bad
    $max = 0; // it is the best for used assessment type
    $real = 0; // it is regarding student real results (with percent of grading)
    if ($maxGrade > 0 && $studentGradeResult > 0) {
        $percent = $studentGradeResult / $maxGrade * 100;
    } else {
        $percent = 0;
    }
    switch ($type) {
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_NONE:
            break;
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE:
            $limit = block_exacomp_get_assessment_grade_limit($courseid);
            if ($percent > 0) {
                $k = ($limit - 1) / 100;
                $x = $limit - ($percent * $k);
                $real = round($x, 1); // TODO: is it possible to be float?;
            }
            $max = 1;
            break;
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_VERBOSE:
            $verboses = preg_split("/(\/|,) /", block_exacomp_get_assessment_verbose_options(null, $courseid));
            $max = count($verboses);
            if ($max > 0) {
                $max -= 1;  // because it is possible zero
            }
            if ($percent > 0) {
                $k = ($max) / 100;
                $x = ($percent * $k);
                $real = round($x);
            }
            break;
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_POINTS:
            $max = block_exacomp_get_assessment_points_limit(null, $courseid);
            if ($percent > 0) {
                $k = ($max) / 100;
                $x = ($percent * $k);
                $real = round($x);
            }
            break;
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_YESNO:
            if ($percent > 50) {
                $real = 1;
            } else {
                $real = 0;
            }
            $max = 1;
            break;
    }
    if ($userrealvalue) {
        return $real;
    } else {
        return $max;
    }
    //return $max;
}

function block_exacomp_get_assessment_max_value_by_level($level, $courseid = 0) {
    $type = block_exacomp_additional_grading($level, $courseid);
    return block_exacomp_get_assessment_max_value($type, $courseid);
}

function block_exacomp_get_assessment_diffLevel($level, $courseid = 0) {
    switch ($level) {
        case BLOCK_EXACOMP_TYPE_DESCRIPTOR:
        case BLOCK_EXACOMP_TYPE_DESCRIPTOR_PARENT:
            return block_exacomp_get_assessment_comp_diffLevel($courseid);
        case BLOCK_EXACOMP_TYPE_TOPIC:
            return block_exacomp_get_assessment_topic_diffLevel($courseid);
        case BLOCK_EXACOMP_TYPE_CROSSSUB:
            return block_exacomp_get_assessment_theme_diffLevel($courseid);
        case BLOCK_EXACOMP_TYPE_SUBJECT:
            return block_exacomp_get_assessment_subject_diffLevel($courseid);
        case BLOCK_EXACOMP_TYPE_EXAMPLE:
            return block_exacomp_get_assessment_example_diffLevel($courseid);
        case BLOCK_EXACOMP_TYPE_DESCRIPTOR_CHILD:
            return block_exacomp_get_assessment_childcomp_diffLevel($courseid);
        default:
            return false;
    }
    return false;
}

function block_exacomp_get_assessment_SelfEval($level, $courseid = 0) {
    switch ($level) {
        case BLOCK_EXACOMP_TYPE_DESCRIPTOR:
        case BLOCK_EXACOMP_TYPE_DESCRIPTOR_PARENT:
            return block_exacomp_get_assessment_comp_SelfEval($courseid);
        case BLOCK_EXACOMP_TYPE_TOPIC:
            return block_exacomp_get_assessment_topic_SelfEval($courseid);
        case BLOCK_EXACOMP_TYPE_CROSSSUB:
            return block_exacomp_get_assessment_theme_SelfEval($courseid);
        case BLOCK_EXACOMP_TYPE_SUBJECT:
            return block_exacomp_get_assessment_subject_SelfEval($courseid);
        case BLOCK_EXACOMP_TYPE_EXAMPLE:
            return block_exacomp_get_assessment_example_SelfEval($courseid);
        case BLOCK_EXACOMP_TYPE_DESCRIPTOR_CHILD:
            return block_exacomp_get_assessment_childcomp_SelfEval($courseid);
        default:
            return false;
    }
    return false;
}

function block_exacomp_get_translatable_parameter($parameter = '', $getforlanguage = null, $courseid = 0) {
    global $DB, $COURSE;
    if ($parameter == '') {
        return false;
    }
    // stored as json for different languages
    // de - default language

    if ($courseid == 0) {
        $courseid = $COURSE->id;
    }
    $assessment_config = $DB->get_field('block_exacompsettings', 'assessmentconfiguration', ['courseid' => $courseid]);
    if ($assessment_config != 0) {
        $configurations = block_exacomp_get_assessment_configurations();
        $jsondata = $configurations[$assessment_config][$parameter];
        // TODO: what if e.g. assessment_selfEvalVerbose_example_long is not set in the config, but assessment_grade_verbose is?
        // for now: if null use the adminsettings
        if ($jsondata == null) {
            $jsondata = get_config('exacomp', $parameter);
        }
    } else {
        $jsondata = get_config('exacomp', $parameter);
    }

    $copyofdata = $jsondata;
    $configdata = json_decode($jsondata, true);
    if (json_last_error() && $copyofdata != '') { // if old data is not json
        $configdata['de'] = $copyofdata;
    }
    if ($getforlanguage) {
        $language = $getforlanguage;
    } else {
        $language = current_language();
    }
    if ($language && is_array($configdata) && array_key_exists($language, $configdata) && $configdata[$language] != '') {
        return $configdata[$language];
    } else {
        return $configdata['de'];
    }
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
function block_exacomp_get_subjects_by_course($courseid, $showalldescriptors = false, $hideglobalsubjects = -1) {
    if (!$showalldescriptors) {
        $showalldescriptors = block_exacomp_get_settings_by_course($courseid)->show_all_descriptors;
    }

    $sql = '
		SELECT DISTINCT s.id, s.titleshort, s.title, s.stid, s.infolink, s.description, s.source, s.sourceid, s.sorting, s.author, s.editor, s.isglobal, s.class, s.is_editable
		FROM {' . BLOCK_EXACOMP_DB_SUBJECTS . '} s
		JOIN {' . BLOCK_EXACOMP_DB_TOPICS . '} t ON t.subjid = s.id
		JOIN {' . BLOCK_EXACOMP_DB_COURSETOPICS . '} ct ON ct.topicid = t.id AND ct.courseid = ?
		' . ($showalldescriptors ? '' : '
			-- only show active ones
			JOIN {' . BLOCK_EXACOMP_DB_DESCTOPICS . '} topmm ON topmm.topicid=t.id
			JOIN {' . BLOCK_EXACOMP_DB_DESCRIPTORS . '} d ON topmm.descrid=d.id
			JOIN {' . BLOCK_EXACOMP_DB_COMPETENCE_ACTIVITY . '} ca ON ((d.id=ca.compid AND ca.comptype = ' . BLOCK_EXACOMP_TYPE_DESCRIPTOR . ') OR (t.id=ca.compid AND ca.comptype = ' . BLOCK_EXACOMP_TYPE_TOPIC . '))
				AND ca.activityid IN (' . block_exacomp_get_allowed_course_modules_for_course_for_select($courseid) . ')
			') . '
		ORDER BY s.isglobal, s.title
			';

    $subjects = block_exacomp\subject::get_objects_sql($sql, array($courseid));

    //remove the subjects that are hidden because they are globalsubjects and the settings are set to hide them

    $coursesettings = block_exacomp_get_settings_by_course($courseid);
    $hideglobalsubjects = @$coursesettings->hideglobalsubjects;
    if ($hideglobalsubjects == 1) {
        foreach ($subjects as $key => $subject) {
            if ($subject->isglobal) {
                unset($subjects[$key]);
            }
        }
    }

    //return block_exacomp_sort_items($subjects, [BLOCK_EXACOMP_IS_GLOBAL, BLOCK_EXACOMP_DB_SUBJECTS]);
    return $subjects; // TODO: sorted in sql ?
}

/**
 */
function block_exacomp_get_subject_by_subjectid($subjectid) {
    global $DB;

    return $DB->get_record(BLOCK_EXACOMP_DB_SUBJECTS, array('id' => $subjectid));
}

/**
 * @param int $descriptorid
 */
function block_exacomp_get_subject_by_descriptorid($descriptorid) {
    global $DB;
    $topicid = $DB->get_record(BLOCK_EXACOMP_DB_DESCTOPICS, array('descrid' => $descriptorid), "topicid");
    if ($topicid) {
        $subjectid = $DB->get_record(BLOCK_EXACOMP_DB_TOPICS, array('id' => $topicid->topicid), "subjid");
        return $DB->get_record('block_exacompsubjects', array('id' => $subjectid->subjid));
    }
    return null; // Why it is possible?
}

/**
 */
function block_exacomp_get_subject_by_topicid($topicid) {
    global $DB;
    $subjectid = $DB->get_record(BLOCK_EXACOMP_DB_TOPICS, array('id' => $topicid), "subjid");
    return $DB->get_record('block_exacompsubjects', array('id' => $subjectid->subjid));
}

function block_exacomp_get_subject_by_example($exampleid) {
    global $DB;
    $resultSubject = null;
    $descriptors = block_exacomp_get_descriptors_by_example($exampleid);
    foreach ($descriptors as $descriptor) {
        $full = $DB->get_record(BLOCK_EXACOMP_DB_DESCRIPTORS, array("id" => $descriptor->id));
        $sql = "select s.*
                  FROM {" . BLOCK_EXACOMP_DB_SUBJECTS . "} s,
                        {" . BLOCK_EXACOMP_DB_DESCTOPICS . "} dt,
                        {" . BLOCK_EXACOMP_DB_TOPICS . "} t
		WHERE dt.descrid = ?
		        AND t.id = dt.topicid
		        AND t.subjid = s.id";

        if ($full->parentid == 0) {
            $subject = $DB->get_record_sql($sql, array($full->id), IGNORE_MULTIPLE);
        } else {
            $subject = $DB->get_record_sql($sql, array($full->parentid), IGNORE_MULTIPLE);
        }
        if ($subject) {
            $resultSubject = $subject;
            break;
        }
    }
    return $resultSubject;
}

// if the example is related to a few subjects
// @return array ids
function block_exacomp_get_subjects_by_example($exampleid) {
    global $DB;
    $resultSubjects = [];
    //    $descriptors = block_exacomp_get_descriptors_by_example($exampleid);
    $sql = ' SELECT DISTINCT s.id, s.id as tmp
                FROM {' . BLOCK_EXACOMP_DB_SUBJECTS . '} s
                    JOIN {' . BLOCK_EXACOMP_DB_TOPICS . '} t ON t.subjid = s.id
                    JOIN {' . BLOCK_EXACOMP_DB_DESCTOPICS . '} dtmm ON dtmm.topicid = t.id
                    JOIN {' . BLOCK_EXACOMP_DB_DESCEXAMP . '} demm ON demm.descrid = dtmm.descrid
                WHERE demm.exampid = ?
    ';
    $fullList = $DB->get_records_sql($sql, array($exampleid));
    foreach ($fullList as $s) {
        if (!in_array($s->id, $resultSubjects)) {
            $resultSubjects[] = $s->id;
        }
    }
    return $resultSubjects;
}

function block_exacomp_get_niveaus_by_example($exampleid) {
    global $DB;
    $niveaus = $DB->get_records_sql("
		SELECT DISTINCT d.niveauid
		FROM {" . BLOCK_EXACOMP_DB_DESCRIPTORS . "} d
		JOIN {" . BLOCK_EXACOMP_DB_DESCEXAMP . "} de ON de.descrid=d.id
		WHERE de.exampid = ?
	", [$exampleid]);
    $niveauArray = [];
    foreach ($niveaus as $niveau) {
        $niveauArray[] = $niveau->niveauid;
    }
    return $niveauArray;
}

/**
 * Gets all available subjects
 */
function block_exacomp_get_all_subjects() {
    global $DB;

    return $DB->get_records(BLOCK_EXACOMP_DB_SUBJECTS, array(), '', 'id, title, source, sourceid, author, isglobal, class');
}

/**
 * This method is only used in the LIS version
 *
 * @param int $courseid
 */
function block_exacomp_get_schooltypes_by_course($courseid) {
    global $DB;

    return $DB->get_records_sql('
			SELECT DISTINCT s.id, s.title, s.source, s.sourceid, s.sorting, s.disabled
			FROM {' . BLOCK_EXACOMP_DB_SCHOOLTYPES . '} s
			JOIN {' . BLOCK_EXACOMP_DB_MDLTYPES . '} m ON m.stid = s.id AND m.courseid = ?
			ORDER BY s.sorting, s.title
			', array($courseid));
}

/**
 *
 * This function is used for courseselection.php
 * -only subject according to selected schooltypes are returned
 *
 * @param int $courseid
 */
function block_exacomp_get_subjects_for_schooltype($courseid, $schooltypeid = 0) {
    $sql = 'SELECT s.*
                FROM {' . BLOCK_EXACOMP_DB_SUBJECTS . '} s
	                JOIN {' . BLOCK_EXACOMP_DB_MDLTYPES . '} type ON s.stid = type.stid
                WHERE type.courseid = ? ';
    // AND (s.disabled IS NULL OR s.disabled = 0) is needed conditions here, but we need to show hidden grids if here are already selected topics

    if ($schooltypeid > 0) {
        $sql .= ' AND type.stid = ? ';
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
        $sql = 'SELECT s.id, s.title, s.author, s.isglobal
		FROM {' . BLOCK_EXACOMP_DB_SUBJECTS . '} s
		JOIN {' . BLOCK_EXACOMP_DB_TOPICS . '} t ON t.subjid = s.id
		GROUP BY s.id, s.title, s.stid
		ORDER BY s.stid, s.sorting, s.title
		';

        return $DB->get_records_sql($sql);
    } else if ($subjectid != null) {
        $sql = 'SELECT s.id, s.title, s.author, s.isglobal
		FROM {' . BLOCK_EXACOMP_DB_SUBJECTS . '} s
		JOIN {' . BLOCK_EXACOMP_DB_TOPICS . '} t ON t.subjid = s.id
		WHERE s.id = ?
		GROUP BY s.id, s.title, s.stid
		ORDER BY s.stid, s.sorting, s.title';

        return $DB->get_records_sql($sql, $subjectid);
    }

    $subjects = $DB->get_records_sql('
			SELECT DISTINCT s.id, s.title, s.stid, s.sorting, s.isglobal
			FROM {' . BLOCK_EXACOMP_DB_SUBJECTS . '} s
			JOIN {' . BLOCK_EXACOMP_DB_TOPICS . '} t ON t.subjid = s.id
			JOIN {' . BLOCK_EXACOMP_DB_COURSETOPICS . '} ct ON ct.topicid = t.id AND ct.courseid = ?
			ORDER BY s.sorting, s.title
			', array($courseid));

    return $subjects;
}

/**
 * returns the subject an example belongs to
 *
 * @param int $exampleid
 */
function block_exacomp_get_subjecttitle_by_example($exampleid) {
    $subject = block_exacomp_get_subject_by_example($exampleid);
    if ($subject) {
        return $subject->title;
    }
    return null;
}

/**
 * returns the subject a descriptor belongs to
 *
 * @param int $compid
 */
function block_exacomp_get_subjecttitle_by_descriptor($compid) {
    $subject = block_exacomp_get_subject_by_descriptorid($compid);
    if ($subject) {
        return $subject->title;
    }
    return null;
}

/**
 * returns the subject a topic belongs to
 *
 * @param int $compid
 */
function block_exacomp_get_subjecttitle_by_topic($compid) {
    $subject = block_exacomp_get_subject_by_topicid($compid);
    if ($subject) {
        return $subject->title;
    }
    return null;
}

/**
 * returns all topics from a course
 *
 * @param int $courseid
 */
function block_exacomp_get_topics_by_course($courseid, $showalldescriptors = false, $showonlyvisible = false, $crosssubj = null) {
    return block_exacomp_get_topics_by_subject($courseid, 0, $showalldescriptors, $showonlyvisible, $crosssubj);
}

/**
 * Gets all topics from a particular subject
 *
 * @param int $courseid
 * @param int|array $subjectid
 * @param bool $showalldescriptors
 * @param bool $showonlyvisible
 * @return array
 */
function block_exacomp_get_topics_by_subject($courseid, $subjectid = 0, $showalldescriptors = false, $showonlyvisible = false, $crosssubj = null) {
    global $DB;
    if (!$courseid) {
        $showonlyvisible = false;
    }

    if (!$showalldescriptors) {
        $showalldescriptors = block_exacomp_get_settings_by_course($courseid)->show_all_descriptors;
    }

    $subjectSqlON = '';
    if (is_array($subjectid)) {
        $subjectSqlON = ' AND t.subjid IN (' . implode(',', $subjectid) . ') ';
    } else if ($subjectid > 0) {
        $subjectSqlON = ' AND t.subjid = ? ';
    }

    $sql = '
    SELECT DISTINCT t.id, t.title, t.sorting, t.subjid, t.description, t.numb, t.source, t.sourceid, t.span, tvis.visible as visible, s.source AS subj_source, s.sorting AS subj_sorting, s.title AS subj_title
    FROM {' . BLOCK_EXACOMP_DB_TOPICS . '} t
    JOIN {' . BLOCK_EXACOMP_DB_COURSETOPICS . '} ct ON ct.topicid = t.id AND ct.courseid = ? ' . $subjectSqlON . '
    JOIN {' . BLOCK_EXACOMP_DB_SUBJECTS . '} s ON t.subjid=s.id -- join subject here, to make sure only topics with existing subject are loaded
    -- left join, because courseid=0 has no topicvisibility!
    JOIN {' . BLOCK_EXACOMP_DB_TOPICVISIBILITY . '} tvis ON tvis.topicid=t.id AND tvis.studentid=0 AND tvis.courseid=ct.courseid AND tvis.niveauid IS NULL'
        . ($showonlyvisible ? ' AND tvis.visible = 1 ' : '')
        . ($showalldescriptors ? '' : '
        -- only show active ones
        JOIN {' . BLOCK_EXACOMP_DB_DESCTOPICS . '} topmm ON topmm.topicid=t.id
        JOIN {' . BLOCK_EXACOMP_DB_DESCRIPTORS . '} d ON topmm.descrid=d.id
        JOIN {' . BLOCK_EXACOMP_DB_COMPETENCE_ACTIVITY . '} da ON ((d.id=da.compid AND da.comptype = ' . BLOCK_EXACOMP_TYPE_DESCRIPTOR . ') OR (t.id=da.compid AND da.comptype = ' . BLOCK_EXACOMP_TYPE_TOPIC . '))
            AND da.activityid IN (' . block_exacomp_get_allowed_course_modules_for_course_for_select($courseid) . ')
    ');

    //GROUP By funktioniert nur mit allen feldern im select, aber nicht mit strings
    $params = array($courseid);
    if (!is_array($subjectid) && $subjectid > 0) {
        $params[] = $subjectid;
    }

    $topics = $DB->get_records_sql($sql, $params);

    //If crosssubject then only get those topics where a descriptor has been added
    if ($crosssubj) {
        $topics = block_exacomp_clear_topics_for_crosssubject($topics, $courseid, $crosssubj);
    }

    return block_exacomp_sort_items($topics, ['subj_' => BLOCK_EXACOMP_DB_SUBJECTS, '' => BLOCK_EXACOMP_DB_TOPICS]);
}

/**
 * get topics associated with descriptor
 *
 * @param integer $descriptorid
 */
function block_exacomp_get_topics_by_descriptor($descriptorid) {
    global $DB;
    return $DB->get_records_sql("
		SELECT t.*, t.id AS topicdescid
		  FROM {" . BLOCK_EXACOMP_DB_TOPICS . "} t
		    JOIN {" . BLOCK_EXACOMP_DB_DESCTOPICS . "} dt ON dt.topicid = t.id
		WHERE dt.descrid = ?
	", [$descriptorid]);
}

/**
 * receives a list of items and returns them sorted
 *
 * @param unknown $items can be array of different types of items, like topics, subjects...
 * @param unknown $sortings associated array with sorting options
 * @return unknown sorted items
 * @throws \block_exacomp\moodle_exception
 */
function block_exacomp_sort_items(&$items, $sortings) {
    $sortings = (array)$sortings;

    if (is_array($items)) {
        uasort($items, function($a, $b) use ($sortings) {
            foreach ($sortings as $prefix => $sorting) {
                if (is_int($prefix)) {
                    $prefix = '';
                }

                switch ($sorting) {
                    case BLOCK_EXACOMP_IS_GLOBAL:
                        if (!property_exists($a, $prefix . 'isglobal') || !property_exists($b, $prefix . 'isglobal')) {
                            throw new \block_exacomp\moodle_exception('col not found: ' . $prefix . 'isglobal');
                        }
                        return $a->{$prefix . 'isglobal'} < $b->{$prefix . 'isglobal'} ? -1 : 1;
                        break;
                    case BLOCK_EXACOMP_DB_SUBJECTS:
                        if (!property_exists($a, $prefix . 'source') || !property_exists($b, $prefix . 'source')) {
                            throw new \block_exacomp\moodle_exception('col not found: ' . $prefix . 'source');
                        }
                        if (!property_exists($a, $prefix . 'sorting') || !property_exists($b, $prefix . 'sorting')) {
                            throw new \block_exacomp\moodle_exception('col not found: ' . $prefix . 'sorting');
                        }
                        if (!property_exists($a, $prefix . 'title') || !property_exists($b, $prefix . 'title')) {
                            throw new \block_exacomp\moodle_exception('col not found: ' . $prefix . 'title');
                        }

                        // sort subjects
                        // first imported, then generated
                        if ($a->{$prefix . 'source'} != BLOCK_EXACOMP_DATA_SOURCE_CUSTOM && $b->{$prefix . 'source'} == BLOCK_EXACOMP_DATA_SOURCE_CUSTOM) {
                            return -1;
                        }
                        if ($a->{$prefix . 'source'} == BLOCK_EXACOMP_DATA_SOURCE_CUSTOM && $b->{$prefix . 'source'} != BLOCK_EXACOMP_DATA_SOURCE_CUSTOM) {
                            return 1;
                        }

                        if ($a->{$prefix . 'sorting'} < $b->{$prefix . 'sorting'}) {
                            return -1;
                        }
                        if ($a->{$prefix . 'sorting'} > $b->{$prefix . 'sorting'}) {
                            return 1;
                        }

                        if ($a->{$prefix . 'title'} != $b->{$prefix . 'title'}) {
                            return strcmp($a->{$prefix . 'title'}, $b->{$prefix . 'title'});
                        }
                        break;
                    case BLOCK_EXACOMP_DB_TOPICS:
                        if (!property_exists($a, $prefix . 'sorting') || !property_exists($b, $prefix . 'sorting')) {
                            throw new \block_exacomp\moodle_exception('col not found: ' . $prefix . 'sorting');
                        }
                        if (!property_exists($a, $prefix . 'numb') || !property_exists($b, $prefix . 'numb')) {
                            throw new \block_exacomp\moodle_exception('col not found: ' . $prefix . 'numb');
                        }
                        if (!property_exists($a, $prefix . 'title') || !property_exists($b, $prefix . 'title')) {
                            throw new \block_exacomp\moodle_exception('col not found: ' . $prefix . 'title');
                        }

                        if ($a->{$prefix . 'numb'} < $b->{$prefix . 'numb'}) {
                            return -1;
                        }
                        if ($a->{$prefix . 'numb'} > $b->{$prefix . 'numb'}) {
                            return 1;
                        }

                        if ($a->{$prefix . 'sorting'} < $b->{$prefix . 'sorting'}) {
                            return -1;
                        }
                        if ($a->{$prefix . 'sorting'} > $b->{$prefix . 'sorting'}) {
                            return 1;
                        }

                        if ($a->{$prefix . 'title'} != $b->{$prefix . 'title'}) {
                            return strcmp($a->{$prefix . 'title'}, $b->{$prefix . 'title'});
                        }
                        break;
                    case BLOCK_EXACOMP_DB_DESCRIPTORS:
                        if (!property_exists($a, $prefix . 'sorting') || !property_exists($b, $prefix . 'sorting')) {
                            throw new \block_exacomp\moodle_exception('col not found: ' . $prefix . 'sorting');
                        }
                        if (!property_exists($a, $prefix . 'title') || !property_exists($b, $prefix . 'title')) {
                            throw new \block_exacomp\moodle_exception('col not found: ' . $prefix . 'title');
                        }

                        if (!property_exists($a, $prefix . 'source') || !property_exists($b, $prefix . 'source')) {
                            debugging('block_exacomp_sort_items() descriptors need a source', DEBUG_DEVELOPER);
                        } else {
                            if ($a->{$prefix . 'source'} != $b->{$prefix . 'source'}) {
                                if ($a->{$prefix . 'source'} == BLOCK_EXACOMP_DATA_SOURCE_CUSTOM) {
                                    return 1;
                                }
                                if ($b->{$prefix . 'source'} == BLOCK_EXACOMP_DATA_SOURCE_CUSTOM) {
                                    return -1;
                                }
                            }
                        }

                        if ($a->{$prefix . 'sorting'} < $b->{$prefix . 'sorting'}) {
                            return -1;
                        }
                        if ($a->{$prefix . 'sorting'} > $b->{$prefix . 'sorting'}) {
                            return 1;
                        }

                        // last by title
                        if ($a->{$prefix . 'title'} != $b->{$prefix . 'title'}) {
                            return strcmp($a->{$prefix . 'title'}, $b->{$prefix . 'title'});
                        }
                        break;
                    case BLOCK_EXACOMP_DB_NIVEAUS:
                        if (!property_exists($a, $prefix . 'numb') || !property_exists($b, $prefix . 'numb')) {
                            throw new \block_exacomp\moodle_exception('col not found: ' . $prefix . 'numb');
                        }
                        if (!property_exists($a, $prefix . 'sorting') || !property_exists($b, $prefix . 'sorting')) {
                            throw new \block_exacomp\moodle_exception('col not found: ' . $prefix . 'sorting');
                        }
                        if (!property_exists($a, $prefix . 'title') || !property_exists($b, $prefix . 'title')) {
                            throw new \block_exacomp\moodle_exception('col not found: ' . $prefix . 'title');
                        }

                        if ($a->{$prefix . 'numb'} < $b->{$prefix . 'numb'}) {
                            return -1;
                        }
                        if ($a->{$prefix . 'numb'} > $b->{$prefix . 'numb'}) {
                            return 1;
                        }

                        if ($a->{$prefix . 'sorting'} !== $b->{$prefix . 'sorting'}) {
                            // move items without niveau.sorting (=null, which actually probably means they have no niveau) to the end
                            if ($a->{$prefix . 'sorting'} === null) {
                                return 1;
                            }
                            if ($b->{$prefix . 'sorting'} === null) {
                                return -1;
                            }

                            if ($a->{$prefix . 'sorting'} < $b->{$prefix . 'sorting'}) {
                                return -1;
                            }
                            if ($a->{$prefix . 'sorting'} > $b->{$prefix . 'sorting'}) {
                                return 1;
                            }
                        }

                        if ($a->{$prefix . 'title'} != $b->{$prefix . 'title'}) {
                            return strcmp($a->{$prefix . 'title'}, $b->{$prefix . 'title'});
                        }
                        break;
                    default:
                        throw new \block_exacomp\moodle_exception('sorting type not found: ' . $sorting);
                }
            }
        });
    }

    return $items;
}

/**
 * Gets all topics
 */
function block_exacomp_get_all_topics($subjectid = null, $showonlyvisible = false) {
    global $DB;
    if ($subjectid) {
        // if there is a subjectid, then for large databases, sorting with block_exacomp_sort_items is better for performance
        // $start = microtime(true);
        $sql = 'SELECT t.id, t.sorting, t.numb, t.title, t.description, t.parentid, t.subjid, s.source AS subj_source, s.sorting AS subj_sorting, s.title AS subj_title
			  FROM {' . BLOCK_EXACOMP_DB_SUBJECTS . '} s
			JOIN {' . BLOCK_EXACOMP_DB_TOPICS . '} t ON t.subjid = s.id ';
        if (is_array($subjectid) && count($subjectid) > 0) {
            $sql .= ' WHERE s.id IN (' . implode(',', $subjectid) . ') ';
        } else if ($subjectid !== null) {
            $sql .= ' WHERE s.id = ? ';
        }
        $topics = $DB->get_records_sql($sql, array($subjectid));
        $sorted_results = block_exacomp_sort_items($topics, ['subj_' => BLOCK_EXACOMP_DB_SUBJECTS, '' => BLOCK_EXACOMP_DB_TOPICS]);
        // echo "old sorting (block_exacomp_sort_items after sql):";
        // echo (microtime(true) - $start)*1000;
    } else {
        // if there is no subjectid, then sorting with ORDER BY is better for performance
        // $start = microtime(true);
        $sql = 'SELECT t.id, t.sorting, t.numb, t.title, t.description, t.parentid, t.subjid, s.source AS subj_source, s.sorting AS subj_sorting, s.title AS subj_title
			  FROM {' . BLOCK_EXACOMP_DB_SUBJECTS . '} s
			JOIN {' . BLOCK_EXACOMP_DB_TOPICS . '} t ON t.subjid = s.id ';
        $sql .= ' ORDER BY s.source, s.sorting, s.title, t.numb, t.sorting, t.title';
        $sorted_results = $DB->get_records_sql($sql, array($subjectid));
        // echo "new sorting (ORDER BY)";
        // echo (microtime(true) - $start)*1000;
    }
    return $sorted_results;
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
 *
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
    $DB->delete_records(BLOCK_EXACOMP_DB_ITEM_MM, array('exacomp_record_id' => $example->id, 'competence_type' => 4));

    $fs = get_file_storage();
    $fs->delete_area_files(\context_system::instance()->id, 'block_exacomp', 'example_task', $example->id);
    $fs->delete_area_files(\context_system::instance()->id, 'block_exacomp', 'example_solution', $example->id);
    $fs->delete_area_files(\context_system::instance()->id, 'block_exacomp', 'example_completefile', $example->id);
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
function block_exacomp_set_user_competence($userid, $compid, $comptype, $courseid, $role, $value, $evalniveauid = null, $subjectid = -1, $savegradinghistory = true, $options = [], $admingrading = false) {
    global $DB, $USER;

    if ($evalniveauid !== null && $evalniveauid < 1) {
        $evalniveauid = null;
    }

    // TODO: block_exacomp_external::require_teacher_permission($courseid, $userid);
    // TODO: scheduler task "autotest" ?
    if ($role == BLOCK_EXACOMP_ROLE_STUDENT && $userid != $USER->id) {
        return -1;
    }
    if ($role == BLOCK_EXACOMP_ROLE_TEACHER && !$admingrading) {
        block_exacomp_require_teacher($courseid);
        $revieweruser = $USER;
    } else {
        $revieweruser = get_admin();
    }

    block_exacomp_set_comp_eval($courseid, $role, $userid, $comptype, $compid, [
        'value' => $value,
        'evalniveauid' => $evalniveauid,
        'reviewerid' => $revieweruser->id,
        'timestamp' => time(),
    ], $savegradinghistory);

    if ($role == BLOCK_EXACOMP_ROLE_TEACHER) {
        block_exacomp_send_grading_notification($revieweruser, $DB->get_record('user', array('id' => $userid)), $courseid, $compid, $comptype, @$options['notification_customdata']);
        if ($subjectid == -1) {
            $subject = block_exacomp_get_subject_by_descriptorid($compid);
        } else {
            $subject = block_exacomp_get_subject_by_subjectid($subjectid);
        }

        if (@$subject->isglobal) {
            block_exacomp_update_globalgradings_text($compid, $userid, $comptype, $courseid);
        }
        //        block_exacomp_update_gradinghistory_text($compid,$userid,$courseid,$comptype);
    } else if ($role == BLOCK_EXACOMP_ROLE_STUDENT) {
        block_exacomp_notify_all_teachers_about_self_assessment($courseid, $compid, $comptype, $options['notification_customdata']);
    }
    $objecttable = '';
    switch ($comptype) {
        case BLOCK_EXACOMP_TYPE_DESCRIPTOR:
            $objecttable = 'block_exacompdescriptors';
            break;
        case BLOCK_EXACOMP_TYPE_TOPIC:
            $objecttable = 'block_exacomptopics';
            break;
        case BLOCK_EXACOMP_TYPE_EXAMPLE:
            $objecttable = 'block_exacompexamples';
            break;
    }

    \block_exacomp\event\competence_assigned::log(['objecttable' => $objecttable, 'objectid' => $compid, 'courseid' => $courseid, 'relateduserid' => $userid]);

    return 1;
}

function block_exacomp_set_user_example($userid, $exampleid, $courseid, $role, $value = null, $evalniveauid = null, $additionalinfo = null) {
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
            'additionalinfo' => $additionalinfo,
        ];
    } else if ($userid != $USER->id) {
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
 *
 * @param unknown $userid
 * @param unknown $exampleid
 * @param unknown $courseid
 * @return boolean
 */
function block_exacomp_allow_resubmission($userid, $exampleid, $courseid) {
    global $DB;

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
        block_exacomp_set_user_competence($value['user'], $value['compid'], $comptype, $courseid, $role, $value['value'], null, $subjectid);
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
function block_exacomp_reset_comp_data_for_subject($courseid, $role, $comptype, $userid, $subjectid) {
    global $DB;

    $select = " courseid = ? AND role = ? AND COMPTYPE = ? AND userid = ? AND
		(compid IN
		(SELECT t.id FROM {block_exacomptopics} t, {block_exacompsubjects} s
		WHERE t.subjid = s.id AND s.stid = ?))";

    $DB->delete_records_select('block_exacompcompuser', $select, array($courseid, $role, $comptype, $userid, $subjectid));
}

/**
 * Gets settings for the current course
 *
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
        $settings->grading = 0;
    }
    if (empty($settings->nostudents)) {
        $settings->nostudents = 0;
    }
    $settings->work_with_students = !$settings->nostudents;

    if (empty($settings->isglobal)) {
        $settings->isglobal = 0;
    }

    if (!isset($settings->uses_activities)) {
        $settings->uses_activities = 0;
    }
    if (!$settings->uses_activities) {
        $settings->show_all_descriptors = 1; //TODO is this a copy-paste error?
    }
    if (!isset($settings->show_all_descriptors)) {
        $settings->show_all_descriptors = 1;
    }
    if (isset($settings->filteredtaxonomies)) {
        $settings->filteredtaxonomies = json_decode($settings->filteredtaxonomies, true);
    } else {
        $settings->filteredtaxonomies = array(BLOCK_EXACOMP_SHOW_ALL_TAXONOMIES);
    }

    if (!isset($settings->assessmentconfiguration)) {
        $settings->assessmentconfiguration = 0;
    }

    return $settings;
}

/**
 *
 * returns all descriptors
 *
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
		SELECT DISTINCT desctopmm.id as u_id, d.id as id, d.title, d.source, d.niveauid, t.id AS topicid,
		                d.profoundness, d.parentid, n.sorting AS niveau_sorting, n.numb AS niveau_numb,
		                n.title AS niveau_title, dvis.visible as visible, d.author, d.editor,
		                d.sorting, d.creatorid as descriptor_creatorid
		FROM {' . BLOCK_EXACOMP_DB_TOPICS . '} t
		' . (($courseid > 0) ? ' JOIN {' . BLOCK_EXACOMP_DB_COURSETOPICS . '} topmm ON topmm.topicid=t.id AND topmm.courseid=? ' . (($subjectid > 0) ? ' AND t.subjid = ' . $subjectid . ' ' : '') : '') . '
		JOIN {' . BLOCK_EXACOMP_DB_DESCTOPICS . '} desctopmm ON desctopmm.topicid=t.id
		JOIN {' . BLOCK_EXACOMP_DB_DESCRIPTORS . '} d ON desctopmm.descrid=d.id AND d.parentid=0
		-- left join, because courseid=0 has no descvisibility!
		LEFT JOIN {' . BLOCK_EXACOMP_DB_DESCVISIBILITY . '} dvis ON dvis.descrid=d.id AND dvis.studentid=0 AND dvis.courseid=?
		' . ($showonlyvisible ? ' AND dvis.visible = 1 ' : '') . '
		LEFT JOIN {' . BLOCK_EXACOMP_DB_NIVEAUS . '} n ON d.niveauid = n.id
		' . ($showalldescriptors ? '' : '
			JOIN {' . BLOCK_EXACOMP_DB_COMPETENCE_ACTIVITY . '} ca ON d.id=ca.compid AND ca.comptype=' . BLOCK_EXACOMP_TYPE_DESCRIPTOR . '
				AND ca.activityid IN (' . block_exacomp_get_allowed_course_modules_for_course_for_select($courseid) . ')
		') . '
		';
    //ORDER BY t.sorting, n.numb, n.sorting, d.sorting

    $descriptors = block_exacomp\descriptor::get_objects_sql($sql, array($courseid, $courseid, $courseid, $courseid));

    //here a lot of time is lost rw
    foreach ($descriptors as $descriptor) {
        if ($include_childs) {
            $descriptor = block_exacomp_get_examples_for_descriptor($descriptor, $filteredtaxonomies, $showallexamples, $courseid, true, $showonlyvisible);
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

    return block_exacomp_sort_items($descriptors, ['niveau_' => BLOCK_EXACOMP_DB_NIVEAUS, '' => BLOCK_EXACOMP_DB_DESCRIPTORS]);
}

/**
 * return categories for specific descriptor (e.g. G, M, E for LIS data)
 *
 * @param unknown $descriptor
 * @return unknown
 */
function block_exacomp_get_categories_for_descriptor($descriptor) {
    global $DB;
    //im upgrade skript zugriff auf diese funktion obwohl die tabelle erst spï¿½ter akutalisiert wird
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
		FROM {" . BLOCK_EXACOMP_DB_CATEGORIES . "} c
		JOIN {" . BLOCK_EXACOMP_DB_DESCCAT . "} dc ON dc.catid=c.id
		WHERE dc.descrid=?
		ORDER BY c.sorting
	", array($descriptor->id));

    return $categories;
}

/**
 * return child descriptors for parentdescriptor
 *
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
    global $DB, $USER;

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

    $sql = 'SELECT d.id, d.title, d.niveauid, d.source, ' . $parent->topicid . ' as topicid, d.profoundness, d.parentid, ' .
        ($mindvisibility ? 'dvis.visible as visible, ' : '') . ' d.sorting, d.author, d.editor, d.creatorid as descriptor_creatorid
			FROM {' . BLOCK_EXACOMP_DB_DESCRIPTORS . '} d '
        . ($mindvisibility ? 'JOIN {' . BLOCK_EXACOMP_DB_DESCVISIBILITY . '} dvis ON dvis.descrid=d.id AND dvis.courseid=? AND dvis.studentid=0 '
            . ($showonlyvisible ? 'AND dvis.visible=1 ' : '') : '');

    $sql .= ' WHERE d.parentid = ?';

    $params = array();
    if ($mindvisibility) {
        $params[] = $courseid;
    }

    $params[] = $parent->id;
    $descriptors = block_exacomp\descriptor::get_objects_sql($sql, $params);

    foreach ($descriptors as $descriptor) {
        $descriptor = block_exacomp_get_examples_for_descriptor($descriptor, $filteredtaxonomies, $showallexamples, $courseid, $mindvisibility, $showonlyvisible);
        $descriptor->children = block_exacomp_get_child_descriptors($descriptor, $courseid, null /* unused */, $filteredtaxonomies, $showallexamples, $mindvisibility, $showonlyvisible);
        $descriptor->categories = block_exacomp_get_categories_for_descriptor($descriptor);
    }

    // filter descriptors if show_teacherdescriptors_global is disabled and creatorid is not the current user
    if (!get_config('exacomp', 'show_teacherdescriptors_global')) {
        $newDescriptors = [];
        foreach ($descriptors as $descriptor) {
            if (isset($descriptor->descriptor_creatorid) && $descriptor->descriptor_creatorid != $USER->id) {
                continue;
            }
            $newDescriptors[] = $descriptor;
        }
        $descriptors = $newDescriptors;
    }

    return block_exacomp_sort_items($descriptors, BLOCK_EXACOMP_DB_DESCRIPTORS);
}

/**
 * return descriptor with examples
 *
 * @param unknown $descriptor - is returned again
 * @param array $filteredtaxonomies - only chosen taxonomies
 * @param string $showallexamples - exclude external or not
 * @param unknown $courseid
 * @param string $mind_visibility - return visibie field
 * @param string $showonlyvisible - return only visible
 * @return unknown
 */
function block_exacomp_get_examples_for_descriptor($descriptor, $filteredtaxonomies = array(BLOCK_EXACOMP_SHOW_ALL_TAXONOMIES), $showallexamples = true, $courseid = null, $mind_visibility = true, $showonlyvisible = false,
    $freeelementdescriptor = false, $search = "") {
    global $COURSE, $USER;

    if (is_scalar($descriptor)) {
        $descriptorid = $descriptor;
        $descriptor = new stdClass();
        $descriptor->id = $descriptorid;
    }

    if ($courseid == null) {
        $courseid = $COURSE->id;
    }

    //    if($freeelementdescriptor){ //here the SOURCE does not matter. Normally the examples that are specific for a user would not be shown
    //        $examples = \block_exacomp\example::get_objects_sql(
    //            "SELECT DISTINCT de.id as deid, e.id, e.title, e.externalurl, e.source, e.sourceid, e.creatorid,
    //            e.externalsolution, e.externaltask, e.completefile, e.description, e.creatorid, e.iseditable, e.tips, e.timeframe, e.author,
    //            e.ethema_issubcategory, e.ethema_ismain, e.ethema_parent, e.ethema_important, e.example_icon,
    //            de.sorting
    //            FROM {".BLOCK_EXACOMP_DB_EXAMPLES."} e
    //            JOIN {".BLOCK_EXACOMP_DB_DESCEXAMP."} de ON e.id=de.exampid AND de.descrid=?"
    //            ." WHERE "
    //            .($showallexamples ? " 1=1 " : " e.creatorid > 0")
    //            ." ORDER BY de.sorting"
    //            , array($descriptor->id, $courseid, $courseid));
    //    }else{

    // TODO: check for example->courseid
    $examples = \block_exacomp\example::get_objects_sql(
        "SELECT DISTINCT de.id as deid, e.id, e.title, e.externalurl, e.source, e.sourceid, e.creatorid, e.task,
            e.externalsolution, e.externaltask, e.completefile, e.description, e.creatorid, e.iseditable, e.tips, e.timeframe, e.author, e.editor,
            e.ethema_issubcategory, e.ethema_ismain, e.ethema_parent, e.ethema_important, e.example_icon,
            de.sorting, e.courseid, e.activityid, e.activitylink, e.author_origin, e.is_teacherexample
            FROM {" . BLOCK_EXACOMP_DB_EXAMPLES . "} e
            JOIN {" . BLOCK_EXACOMP_DB_DESCEXAMP . "} de ON e.id=de.exampid AND de.descrid=:descriptorid"
        . " WHERE "
        . " e.source != " . BLOCK_EXACOMP_EXAMPLE_SOURCE_USER . " AND "
        . ($showallexamples ? " 1=1 " : " e.creatorid > 0")
        . (!block_exacomp_is_teacher() && !block_exacomp_is_teacher($courseid, $USER->id) /*for webservice*/ ? ' AND e.is_teacherexample = 0 ' : '')
        . " AND (e.title LIKE :searchtitle OR e.description LIKE :searchdescription)"
        . " AND (e.courseid = 0 OR e.courseid = :courseid OR e.courseid IS NULL)"
        . " ORDER BY de.sorting"
        , array("descriptorid" => $descriptor->id, "searchtitle" => "%" . $search . "%", "searchdescription" => "%" . $search . "%", "courseid" => $courseid));
    //    }

    // old
    if ($mind_visibility || $showonlyvisible) {
        foreach ($examples as $example) {
            $example->visible = block_exacomp_is_example_visible($courseid, $example, 0);
            $example->solution_visible = block_exacomp_is_example_solution_visible($courseid, $example, 0);

            if ($showonlyvisible && !$example->visible) {
                unset($examples[$example->id]);
            }

            if ($example->activityid > 0 && $example->courseid > 0 && $example->courseid != $courseid) {
                unset($examples[$example->id]);
            }
        }
    }

    foreach ($examples as $example) {
        $example->courseid = $courseid;
        $example->descriptor = $descriptor;
        $example->taxonomies = block_exacomp_get_taxonomies_by_example($example);

        $taxtitle = "";
        foreach ($example->taxonomies as $taxonomy) {
            $taxtitle .= $taxonomy->title . ", ";
        }

        $taxtitle = substr($taxtitle, 0, strlen($taxtitle) - 1);
        $example->tax = $taxtitle;
    }
    $filtered_examples = array();
    if ($filteredtaxonomies && is_array($filteredtaxonomies) &&
        !in_array(BLOCK_EXACOMP_SHOW_ALL_TAXONOMIES, $filteredtaxonomies)) {
        //        $filtered_taxonomies = implode(",", $filteredtaxonomies); //unused

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
 *
 * @param unknown $example
 */
function block_exacomp_get_taxonomies_by_example($example) {
    global $DB;

    $exampleid = is_scalar($example) ? $example : $example->id;

    return $DB->get_records_sql("
		SELECT tax.*
		FROM {" . BLOCK_EXACOMP_DB_TAXONOMIES . "} tax
		JOIN {" . BLOCK_EXACOMP_DB_EXAMPTAX . "} et ON tax.id = et.taxid
		WHERE et.exampleid = ?
		ORDER BY tax.sorting
	", array($exampleid));

}

/**
 * get taxonomies
 *
 * @param integer $sourceid
 * @return mixed
 */
function block_exacomp_get_taxonomies($sourceid = null) {
    global $DB;
    return $DB->get_records_sql("
		SELECT tax.*
		FROM {" . BLOCK_EXACOMP_DB_TAXONOMIES . "} tax
		" . ($sourceid ? " WHERE tax.source = ? " : "") . "
		ORDER BY tax.source, tax.sorting
	", array($sourceid));

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
		FROM {' . BLOCK_EXACOMP_DB_TOPICS . '} t
            JOIN {' . BLOCK_EXACOMP_DB_COURSETOPICS . '} topmm ON topmm.topicid=t.id AND topmm.courseid=? ' . (($topicid > 0) ? ' AND t.id = ' . $topicid . ' ' : '') . '
            JOIN {' . BLOCK_EXACOMP_DB_DESCTOPICS . '} desctopmm ON desctopmm.topicid=t.id
            JOIN {' . BLOCK_EXACOMP_DB_DESCRIPTORS . '} d ON desctopmm.descrid=d.id AND d.parentid=0
            LEFT JOIN {' . BLOCK_EXACOMP_DB_NIVEAUS . '} n ON n.id = d.niveauid '
        . ($mind_visibility ? 'JOIN {' . BLOCK_EXACOMP_DB_DESCVISIBILITY . '} dvis ON dvis.descrid=d.id AND dvis.studentid=0 AND dvis.courseid=? '
            . ($showonlyvisible ? 'AND dvis.visible = 1 ' : '') : '')
        . ($showalldescriptors ? '' : '
                JOIN {' . BLOCK_EXACOMP_DB_COMPETENCE_ACTIVITY . '} ca ON d.id=ca.compid AND ca.comptype=' . BLOCK_EXACOMP_TYPE_DESCRIPTOR . '
                    AND ca.activityid IN (' . block_exacomp_get_allowed_course_modules_for_course_for_select($courseid) . ')
            ') . '
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
 *
 * @param int $subjectid
 * @param bool $niveaus default false, if true only descriptors with neveaus are returned
 * @return multitype:
 */
function block_exacomp_get_descriptors_by_subject($subjectid, $niveaus = true) {
    global $DB;

    $sql = "SELECT d.*, dt.topicid, t.title as topic_title, t.sorting as topic_sorting
              FROM {" . BLOCK_EXACOMP_DB_DESCRIPTORS . "} d,
                    {" . BLOCK_EXACOMP_DB_DESCTOPICS . "} dt,
                    {" . BLOCK_EXACOMP_DB_TOPICS . "} t
	            WHERE d.id=dt.descrid
	                AND d.parentid =0
	                AND dt.topicid IN (
	                        SELECT id FROM {" . BLOCK_EXACOMP_DB_TOPICS . "} WHERE subjid=?
                    ) ";
    if ($niveaus) {
        $sql .= " AND d.niveauid > 0";
    }
    $sql .= " AND dt.topicid = t.id ";
    $sql .= ' ORDER BY t.sorting, t.title, d.sorting, d.title, d.skillid, dt.topicid, d.niveauid ';

    return $DB->get_records_sql($sql, array($subjectid));
}

/**
 * get descriptors associated with example
 *
 * @param unknown $exampleid
 */
function block_exacomp_get_descriptors_by_example($exampleid) {
    global $DB;

    return $DB->get_records_sql("
		SELECT DISTINCT d.*, d.id as dtemp
		FROM {" . BLOCK_EXACOMP_DB_DESCRIPTORS . "} d
		JOIN {" . BLOCK_EXACOMP_DB_DESCEXAMP . "} de ON de.descrid=d.id
		WHERE de.exampid = ?
	", [$exampleid]);
}

/**
 * get descriptors associated with niveau
 *
 * @param int $courseid
 * @param int $niveauid
 * @param int $topicid filter by topic
 * @return array
 */
function block_exacomp_get_descriptors_by_niveau($courseid, $niveauid, $topicid = 0) {
    global $DB;
    return $DB->get_records_sql('
		SELECT d.id, d.*
		FROM {' . BLOCK_EXACOMP_DB_TOPICS . '} t
            JOIN {' . BLOCK_EXACOMP_DB_COURSETOPICS . '} ctmm ON ctmm.topicid = t.id AND ctmm.courseid = ? ' . (($topicid > 0) ? ' AND t.id = ' . intval($topicid) . ' ' : '') . '
            JOIN {' . BLOCK_EXACOMP_DB_DESCTOPICS . '} dtmm ON dtmm.topicid = t.id
            JOIN {' . BLOCK_EXACOMP_DB_DESCRIPTORS . '} d ON dtmm.descrid = d.id
		WHERE d.niveauid = ?
	', [$courseid, $niveauid]);
}

/**
 * Gets an associative array that is used to display the whole hierarchie of subjects, topics and competencies within a course
 *
 * @param int $courseid
 * @param int $subjectid
 * @param int $topicid
 * @return \block_exacomp\subject[]
 */
function block_exacomp_get_competence_tree($courseid = 0, $subjectid = null, $topicid = null, $showalldescriptors = false, $niveauid = null, $showallexamples = true, $filteredtaxonomies = array(BLOCK_EXACOMP_SHOW_ALL_TAXONOMIES),
    $calledfromoverview = false,
    $calledfromactivities = false, $showonlyvisible = false, $without_descriptors = false, $showonlyvisibletopics = false, $include_childs = true, $filteredDescriptors = null, $editmode = false) {
    global $DB, $USER;

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
    } else if (is_array($subjectid)) {
        $allSubjects = [];
        foreach ($subjectid as $sid) {
            $allSubjects[$sid] = \block_exacomp\subject::get($sid);
        }
    } else if ($subjectid) {
        $allSubjects = array($subjectid => \block_exacomp\subject::get($subjectid));
    } else {
        $allSubjects = block_exacomp_get_subjects_by_course($courseid, $showalldescriptors);
    }

    // 2. GET TOPICS
    $allTopics = block_exacomp_get_all_topics($subjectid, $showonlyvisible);
    if ($courseid > 0) {
        if ((!$calledfromoverview && !$calledfromactivities) || !$selectedTopic) {
            $courseTopics = block_exacomp_get_topics_by_subject($courseid, $subjectid, $showalldescriptors, $showonlyvisibletopics);
        } else if (isset($selectedTopic)) {
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

    if ($filteredDescriptors && is_array($filteredDescriptors) && count($filteredDescriptors) > 0) {
        $allDescriptors = array_filter($allDescriptors,
            function($key) use ($filteredDescriptors) {
                return in_array($key, $filteredDescriptors);
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    // niveaus that are used in this topic... needed for not displaying empty niveaus in the exacomp competence grid
    foreach ($allDescriptors as $descriptor) {
        // get descriptor topic
        if (empty($allTopics[$descriptor->topicid])) {
            continue;
        }

        // if this descriptor is from other teacher and 'show_teacherdescriptors_global' is not enabled
        if (!get_config('exacomp', 'show_teacherdescriptors_global') && isset($descriptor->descriptor_creatorid) && $descriptor->descriptor_creatorid != $USER->id) {
            continue;
        }

        $topic = $allTopics[$descriptor->topicid];
        //if the niveau of this topic is invisible: skip
        if (!$editmode && !block_exacomp_is_niveau_visible($courseid, $topic, 0, $descriptor->niveauid)) {
            continue;
        }

        if (!property_exists($topic, "used_niveaus")) {
            $topic->used_niveaus = array();
        }
        if (!isset($topic->used_niveaus[$descriptor->niveauid])) {
            $topic->used_niveaus[$descriptor->niveauid] = (object)["id" => $descriptor->niveauid, "title" => $descriptor->niveau_title, "numb" => $descriptor->niveau_numb, "sorting" => $descriptor->niveau_sorting];
        }

        if ($niveauid != BLOCK_EXACOMP_SHOW_ALL_NIVEAUS && $calledfromoverview) {
            if ($descriptor->niveauid != $niveauid) {
                continue;
            }
        }

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
        if (!isset($topic->used_niveaus)) {
            $topic->used_niveaus = array();
        }
        $topic = block_exacomp\topic::create($topic);

        // add the used_niveaus of the topics of this subject to the subject.
        // the used_niveaus are needed for webservices e.g. diggrplus_get_all_subjects_for_course_as_tree() to not send the niveautitles with every single descriptor
        if (!property_exists($subject, "used_niveaus")) {
            $subject->used_niveaus = array();
        }
        foreach ($topic->used_niveaus as $niveauid => $niveautitle) {
            if (!isset($subject->used_niveaus[$niveauid])) {
                $subject->used_niveaus[$niveauid] = $niveautitle;
            }
        }

        // found: add it to the subject result
        $subject->topics[$topic->id] = $topic;
        $subjects[$subject->id] = $subject;
    }

    // sorting of subjects
    if (count($subjects) > 0) {
        $correctSorting = $DB->get_fieldset_sql('
                            SELECT s.id
                              FROM {' . BLOCK_EXACOMP_DB_SUBJECTS . '} s
                              WHERE s.id IN (' . implode(',', array_keys($subjects)) . ')
                              ORDER BY s.isglobal, s.title
                        ');
        $newSubjects = array();
        foreach ($correctSorting as $sKey) {
            if (array_key_exists($sKey, $subjects)) {
                $newSubjects[$sKey] = $subjects[$sKey];
            }
        }
        $subjects = $newSubjects;
    }

    // sort topics
    foreach ($subjects as $subject) {
        block_exacomp_sort_items($subject->topics, BLOCK_EXACOMP_DB_TOPICS);
        block_exacomp_sort_items($subject->used_niveaus, BLOCK_EXACOMP_DB_NIVEAUS);
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
function block_exacomp_init_overview_data($courseid, $subjectid, $topicid, $niveauid, $editmode, $isTeacher = true, $studentid = 0, $showonlyvisible = false, $hideglobalsubjects = false, $crosssubj = null) {

    $courseTopics = block_exacomp_get_topics_by_course($courseid, false, $showonlyvisible ? (($isTeacher) ? false : true) : false, $crosssubj);
    $courseSubjects = block_exacomp_get_subjects_by_course($courseid, false, $hideglobalsubjects);

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
            } else if ($topicid && isset($topics[$topicid]) && block_exacomp_is_topic_visible($courseid, $topic, $studentid)) {
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
        if ($selectedSubject) {
            $topics = block_exacomp_get_topics_by_subject($courseid, $selectedSubject->id, false,
                ($showonlyvisible ? (($isTeacher) ? false : true) : false));
            // select first visible topic
            foreach ($topics as $tmpTopic) {
                if (block_exacomp_is_topic_visible($courseid, $tmpTopic, $studentid)) {
                    $selectedTopic = $tmpTopic;
                    break;
                }
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
            if ($selectedSubject && isset($courseTopics[$descriptor->topicid]) && ($courseTopics[$descriptor->topicid]->subjid == $selectedSubject->id)) {
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

    //  This would only get the niveaus of one topic, but I to check all topics ==> new function to unly have 1 sql query
    // get niveau ids from descriptors
    //	$niveau_ids = array();
    //	foreach ($descriptors as $descriptor) {
    //		$niveau_ids[$descriptor->niveauid] = $descriptor->niveauid;
    //	}
    //
    //	// load niveaus from db
    //	$niveaus = g::$DB->get_records_list(BLOCK_EXACOMP_DB_NIVEAUS, 'id', $niveau_ids, 'numb, sorting');
    //	$niveaus = \block_exacomp\niveau::create_objects($niveaus);

    $niveaus = block_exacomp_get_niveaus_for_subject(($selectedSubject ? $selectedSubject->id : null));
    $niveaus = \block_exacomp\niveau::create_objects($niveaus);

    $defaultNiveau = block_exacomp\niveau::create();
    $defaultNiveau->id = BLOCK_EXACOMP_SHOW_ALL_NIVEAUS;
    $defaultNiveau->title = block_exacomp_get_string('allniveaus');
    $defaultNiveau->source = 0;

    $niveaus = array($defaultNiveau->id => $defaultNiveau) + $niveaus;

    foreach ($niveaus as $key => $niveau) {
        //	    if( $niveau->id != BLOCK_EXACOMP_SHOW_ALL_NIVEAUS && !block_exacomp_is_niveau_visible($courseid,$topic,$studentid,$niveau->id)){
        //            unset($niveaus[$key]);
        //        }
        if ($niveau->id != BLOCK_EXACOMP_SHOW_ALL_NIVEAUS) {
            $niveau->visible = block_exacomp_is_niveau_visible($courseid, $topic, $studentid, $niveau->id);
        }
    }

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
        if (!empty($courseSubjects) && array_key_exists($topic->subjid, $courseSubjects)) { //check if this subject is not removed already
            $courseSubjects[$topic->subjid]->topics[$topic->id] = $topic;
        }
    }

    return array($courseSubjects, $courseTopics, $niveaus, $selectedSubject, $selectedTopic, $selectedNiveau);
}

/**
 *
 * Returns all students enroled to a particular course
 *
 * @param unknown_type $courseid
 * @param unknown_type $limitfrom
 * @param unknown_type $limitnum
 */
function block_exacomp_get_students_by_course($courseid, $limitfrom = '', $limitnum = '') {
    $context = context_course::instance($courseid);

    $students = get_users_by_capability($context, 'block/exacomp:student', '', 'lastname,firstname', $limitfrom, $limitnum);

    // TODO ggf user mit exacomp:teacher hier filtern?
    return $students;
}

/**
 *
 * Returns all groups created in a particular course    actually not usefull, could use the moodle function directly RW
 *
 * @param unknown_type $courseid
 */
// function block_exacomp_get_groups_by_course($courseid) {

//     $groups = groups_get_all_groups($courseid);

//     return $groups;
// }

/**
 *
 * Returns all STUDENTS of this group... needed because teachers can also be in groups, but often you only need the students
 *
 * @param unknown_type $courseid
 * @param unknown_type $groupid
 */
function block_exacomp_groups_get_members($courseid, $groupid) {
    $students = groups_get_members($groupid); //with teachers
    foreach ($students as $student) {
        if (block_exacomp_is_teacher($courseid, $student->id)) {
            unset($students[$student->id]);
        }
    }
    return $students;
}

/**
 *
 * Returns all teacher enroled to a course
 *
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
 * @param bool $onlycomps
 * @return stdClass $user
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
 *
 * @param stdClass $user
 * @param int $courseid
 * @return stdClass user
 */
function block_exacomp_get_user_crosssubs_by_course($user, $courseid) {
    global $DB;
    $user->crosssubs = new stdClass();
    $user->crosssubs->teacher =
        $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_TEACHER, "comptype" => BLOCK_EXACOMP_TYPE_CROSSSUB), '', 'compid as id, value');
    $user->crosssubs->student =
        $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_STUDENT, "comptype" => BLOCK_EXACOMP_TYPE_CROSSSUB), '', 'compid as id, value');
    $user->crosssubs->timestamp_teacher =
        $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_TEACHER, "comptype" => BLOCK_EXACOMP_TYPE_CROSSSUB), '', 'compid as id, timestamp');
    $user->crosssubs->timestamp_student =
        $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_STUDENT, "comptype" => BLOCK_EXACOMP_TYPE_CROSSSUB), '', 'compid as id, timestamp');
    $user->crosssubs->teacher_additional_grading =
        $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_TEACHER, "comptype" => BLOCK_EXACOMP_TYPE_CROSSSUB), '', 'compid as id, additionalinfo');
    $user->crosssubs->niveau =
        $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_TEACHER, "comptype" => BLOCK_EXACOMP_TYPE_CROSSSUB), '', 'compid as id, evalniveauid');
    //    $user->competencies->globalgradings = $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_TEACHER, "comptype" => BLOCK_EXACOMP_TYPE_CROSSSUB), '', 'compid as id, globalgradings');
    return $user;
}

/**
 * This method returns all user competencies for a particular user in the given course
 *
 * @param stdClass $user
 * @param int $courseid
 * @return stdClass user
 */
function block_exacomp_get_user_competences_by_course($user, $courseid, $return_globalgradings = false) {
    global $DB;

    $user->competencies = new stdClass();
    $user->competencies->teacher =
        $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_TEACHER, "comptype" => BLOCK_EXACOMP_TYPE_DESCRIPTOR), '', 'compid as id, value');
    $user->competencies->student =
        $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_STUDENT, "comptype" => BLOCK_EXACOMP_TYPE_DESCRIPTOR), '', 'compid as id, value');
    $user->competencies->timestamp_teacher =
        $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_TEACHER, "comptype" => BLOCK_EXACOMP_TYPE_DESCRIPTOR), '', 'compid as id, timestamp');
    $user->competencies->timestamp_student =
        $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_STUDENT, "comptype" => BLOCK_EXACOMP_TYPE_DESCRIPTOR), '', 'compid as id, timestamp');
    $user->competencies->teacher_additional_grading =
        $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_TEACHER, "comptype" => BLOCK_EXACOMP_TYPE_DESCRIPTOR), '', 'compid as id, additionalinfo');
    $user->competencies->niveau =
        $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_TEACHER, "comptype" => BLOCK_EXACOMP_TYPE_DESCRIPTOR), '', 'compid as id, evalniveauid');
    $user->competencies->gradingisold =
        $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_TEACHER, "comptype" => BLOCK_EXACOMP_TYPE_DESCRIPTOR), '', 'compid as id, gradingisold');

    $user->competencies->globalgradings = $DB->get_records_menu(BLOCK_EXACOMP_DB_GLOBALGRADINGS, array("userid" => $user->id, "comptype" => BLOCK_EXACOMP_TYPE_DESCRIPTOR), '', 'compid as id, globalgradings');
    //$user->competencies->globalgradings = $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_TEACHER, "comptype" => BLOCK_EXACOMP_TYPE_DESCRIPTOR), '', 'compid as id, globalgradings');

    $user->competencies->gradinghistory =
        $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_TEACHER, "comptype" => BLOCK_EXACOMP_TYPE_DESCRIPTOR), '', 'compid as id, gradinghistory');

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
    $user->topics->teacher =
        $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_TEACHER, "comptype" => BLOCK_EXACOMP_TYPE_TOPIC), '', 'compid as id, value');
    $user->topics->student =
        $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_STUDENT, "comptype" => BLOCK_EXACOMP_TYPE_TOPIC), '', 'compid as id, value');
    $user->topics->timestamp_teacher =
        $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_TEACHER, "comptype" => BLOCK_EXACOMP_TYPE_TOPIC), '', 'compid as id, timestamp');
    $user->topics->timestamp_student =
        $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_STUDENT, "comptype" => BLOCK_EXACOMP_TYPE_TOPIC), '', 'compid as id, timestamp');
    $user->topics->teacher_additional_grading =
        $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_TEACHER, "comptype" => BLOCK_EXACOMP_TYPE_TOPIC), '', 'compid as id, additionalinfo');
    $user->topics->niveau =
        $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_TEACHER, "comptype" => BLOCK_EXACOMP_TYPE_TOPIC), '', 'compid as id, evalniveauid');
    $user->topics->gradingisold =
        $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_TEACHER, "comptype" => BLOCK_EXACOMP_TYPE_TOPIC), '', 'compid as id, gradingisold');
    //$user->topics->globalgradings = $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_TEACHER, "comptype" => BLOCK_EXACOMP_TYPE_TOPIC), '', 'compid as id, globalgradings');
    $user->topics->globalgradings = $DB->get_records_menu(BLOCK_EXACOMP_DB_GLOBALGRADINGS, array("userid" => $user->id, "comptype" => BLOCK_EXACOMP_TYPE_TOPIC), '', 'compid as id, globalgradings');
    $user->topics->gradinghistory =
        $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_TEACHER, "comptype" => BLOCK_EXACOMP_TYPE_TOPIC), '', 'compid as id, gradinghistory');

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
    $user->subjects->teacher =
        $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_TEACHER, "comptype" => BLOCK_EXACOMP_TYPE_SUBJECT), '', 'compid as id, value');
    $user->subjects->student =
        $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_STUDENT, "comptype" => BLOCK_EXACOMP_TYPE_SUBJECT), '', 'compid as id, value');
    $user->subjects->timestamp_teacher =
        $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_TEACHER, "comptype" => BLOCK_EXACOMP_TYPE_SUBJECT), '', 'compid as id, timestamp');
    $user->subjects->timestamp_student =
        $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_STUDENT, "comptype" => BLOCK_EXACOMP_TYPE_SUBJECT), '', 'compid as id, timestamp');
    $user->subjects->teacher_additional_grading =
        $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_TEACHER, "comptype" => BLOCK_EXACOMP_TYPE_SUBJECT), '', 'compid as id, additionalinfo');
    $user->subjects->student_additional_grading =
        $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_STUDENT, "comptype" => BLOCK_EXACOMP_TYPE_SUBJECT), '', 'compid as id, additionalinfo');
    $user->subjects->niveau =
        $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_TEACHER, "comptype" => BLOCK_EXACOMP_TYPE_SUBJECT), '', 'compid as id, evalniveauid');
    //$user->subjects->globalgradings = $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_TEACHER, "comptype" => BLOCK_EXACOMP_TYPE_SUBJECT), '', 'compid as id, globalgradings');
    $user->subjects->globalgradings = $DB->get_records_menu(BLOCK_EXACOMP_DB_GLOBALGRADINGS, array("userid" => $user->id, "comptype" => BLOCK_EXACOMP_TYPE_SUBJECT), '', 'compid as id, globalgradings');
    $user->subjects->gradinghistory =
        $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCES, array("courseid" => $courseid, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_TEACHER, "comptype" => BLOCK_EXACOMP_TYPE_SUBJECT), '', 'compid as id, gradinghistory');
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
    $examples->teacher_additional_grading = g::$DB->get_records_menu(BLOCK_EXACOMP_DB_EXAMPLEEVAL, array("courseid" => $courseid, "studentid" => $user->id), '', 'exampleid as id, additionalinfo');

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

        $user->activities_topics->activities[$activity->id]->teacher += $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCE_USER_MM,
            array("activityid" => $activity->id, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_TEACHER, "comptype" => BLOCK_EXACOMP_TYPE_TOPIC), '', 'compid as id, value');
        $user->activities_topics->activities[$activity->id]->student += $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCE_USER_MM,
            array("activityid" => $activity->id, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_STUDENT, "comptype" => BLOCK_EXACOMP_TYPE_TOPIC), '', 'compid as id, value');
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
        $user->activities_competencies->activities[$activity->id]->teacher += $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCE_USER_MM,
            array("activityid" => $activity->id, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_TEACHER, "comptype" => BLOCK_EXACOMP_TYPE_DESCRIPTOR, "eportfolioitem" => 0), '', 'compid as id, value');
        $user->activities_competencies->activities[$activity->id]->student += $DB->get_records_menu(BLOCK_EXACOMP_DB_COMPETENCE_USER_MM,
            array("activityid" => $activity->id, "userid" => $user->id, "role" => BLOCK_EXACOMP_ROLE_STUDENT, "comptype" => BLOCK_EXACOMP_TYPE_DESCRIPTOR, "eportfolioitem" => 0), '', 'compid as id, value');
    }

    return $user;
}

/**
 * build navigation tabs for coursesettings
 *
 * @param unknown $courseid
 * @return \block_exacomp\tabobject[]
 */
function block_exacomp_build_navigation_tabs_settings($courseid) {
    global $CFG;
    $usebadges = get_config('exacomp', 'usebadges');
    $courseSettings = block_exacomp_get_settings_by_course($courseid);
    $settings_subtree = array();
    $linkParams = array('courseid' => $courseid);
    // Edit course parameters submenu
    $settings_subtree[] = new tabobject('tab_teacher_settings_configuration', new moodle_url('/blocks/exacomp/edit_course.php', $linkParams), block_exacomp_get_string("tab_teacher_settings_configuration"), null, true);
    // Subject selection submenu
    $settings_subtree[] = new tabobject('tab_teacher_settings_selection', new moodle_url('/blocks/exacomp/courseselection.php', $linkParams), block_exacomp_get_string("tab_teacher_settings_selection"), null, true);
    // Activities submenu
    if (block_exacomp_is_activated($courseid)) {
        if ($courseSettings->uses_activities) {
            if (block_exacomp_use_old_activities_method()) {
                $settings_subtree[] = new tabobject('tab_teacher_settings_assignactivities', new moodle_url('/blocks/exacomp/edit_activities.php', $linkParams), block_exacomp_get_string("tab_teacher_settings_assignactivities"), null, true);
            } else {
                $settings_subtree[] =
                    new tabobject('tab_teacher_settings_activitiestodescriptors', new moodle_url('/blocks/exacomp/activities_to_descriptors.php', $linkParams), block_exacomp_get_string("tab_teacher_settings_activitiestodescriptors"), null,
                        true);
            }
            if (intval($CFG->version) >= 2022030300) {
                $settings_subtree[] =
                    new tabobject('tab_teacher_settings_questiontodescriptors', new moodle_url('/blocks/exacomp/question_to_descriptors.php', $linkParams), block_exacomp_get_string("tab_teacher_settings_questiontodescriptors"), null, true);
            }
        }
    }
    // Badges submenu
    if ($usebadges) {
        $settings_subtree[] = new tabobject('tab_teacher_settings_badges', new moodle_url('/blocks/exacomp/edit_badges.php', $linkParams), block_exacomp_get_string("tab_teacher_settings_badges"), null, true);
    }
    // Grading submenu
    $settings_subtree[] = new tabobject('tab_teacher_settings_course_assessment', new moodle_url('/blocks/exacomp/edit_course_assessment.php', $linkParams), block_exacomp_get_string("tab_teacher_settings_course_assessment"), null, true);

    return $settings_subtree;
}

/**
 * build navigation tabs for admin settings (Import, Webservice..)
 *
 * @param unknown $courseid
 * @return \block_exacomp\tabobject[]
 */
function block_exacomp_build_navigation_tabs_admin_settings($courseid) {
    $checkImport = block_exacomp\data::has_data();

    $settings_subtree = array();
    // Standards pre-selection submenu
    if ($checkImport && has_capability('block/exacomp:admin', context_system::instance())) {
        $settings_subtree[] = new tabobject('tab_admin_configuration', new moodle_url('/blocks/exacomp/edit_config.php', array("courseid" => $courseid)), block_exacomp_get_string('tab_admin_configuration'), null, true);
    }
    // Taxonomies submenu
    if (!block_exacomp_is_skillsmanagement()) {
        $settings_subtree[] = new tabobject('tab_admin_taxonomies',
            new moodle_url('/blocks/exacomp/edit_taxonomies.php', array('courseid' => $courseid)),
            block_exacomp_get_string("tab_teacher_settings_taxonomies") . '</a>' .
            block_exacomp_help_icon(block_exacomp_get_string('tab_teacher_settings_taxonomies'), block_exacomp_get_string('tab_teacher_settings_taxonomies_help'), true, 'move-into-sibling-link') . '<a>',
            block_exacomp_get_string("tab_teacher_settings_taxonomies"),
            true);
    }
    // Import submenu
    $settings_subtree[] = new tabobject('tab_admin_import', new moodle_url('/blocks/exacomp/import.php', array('courseid' => $courseid)), block_exacomp_get_string("tab_admin_import"), null, true);
    // "Assign external trainers" submenu
    if (get_config('exacomp', 'external_trainer_assign') && has_capability('block/exacomp:assignstudents', context_system::instance())) {
        $settings_subtree[] =
            new tabobject('tab_external_trainer_assign', new moodle_url('/blocks/exacomp/externaltrainers.php', array('courseid' => $courseid, 'sesskey' => sesskey())), block_exacomp_get_string("block_exacomp_external_trainer_assign"),
                null, true);
    }
    // Webservices submenu
    $settings_subtree[] = new tabobject('tab_webservice_status', new moodle_url('/blocks/exacomp/webservice_status.php', array('courseid' => $courseid)), block_exacomp_trans(['de:Webservice Status', 'en:Check Webservices']), null, true);

    return $settings_subtree;
}

/**
 * build navigation tab for student profile
 *
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
    //	$profile_subtree[] = new tabobject('tab_competence_profile_settings', new moodle_url('/blocks/exacomp/competence_profile_settings.php', array("courseid" => $courseid)), block_exacomp_get_string('tab_competence_profile_settings'), null, true);

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
            $rows[] = new tabobject('tab_competence_gridoverview', new moodle_url('/blocks/exacomp/competence_grid.php', array("courseid" => $courseid)), block_exacomp_get_string('tab_competence_gridoverview'), null, true);
        }
        if ($isTeacherOrStudent && $ready_for_use) {
            //KompetenzÃ¼berblick
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

            if (!$courseSettings->nostudents) {
                $rows[] = new tabobject('tab_group_reports', new moodle_url('/blocks/exacomp/group_reports.php', array("courseid" => $courseid)), block_exacomp_get_string('tab_group_reports'), null, true);
            }
        }

        if ($isTeacher) {
            //Einstellungen
            $rows[] = new tabobject('tab_teacher_settings', new moodle_url('/blocks/exacomp/edit_course.php', array("courseid" => $courseid)), block_exacomp_get_string('tab_teacher_settings'), null, true);
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
 *
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
 *
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
 *
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

function block_exacomp_set_schooltype_disabled($values) {
    global $DB;
    foreach ($DB->get_records(BLOCK_EXACOMP_DB_SCHOOLTYPES) as $rs) {
        // $DB->update_record(BLOCK_EXACOMP_DB_SCHOOLTYPES, array("id"=>$rs->id, "hidden"=>0));
        // if(in_array($rs->id, $values)){
        //     $DB->update_record(BLOCK_EXACOMP_DB_SCHOOLTYPES, array("id"=>$rs->id, "hidden"=>1));
        // }
        // if($values[$rs->id] != null) {
        //     $DB->update_record(BLOCK_EXACOMP_DB_SCHOOLTYPES, array("id"=>$rs->id, "hidden"=>$values[$rs->id]));
        // }

        if ($values[$rs->id] != null) { // if the value is not null, update the hidden value to whatever is in $values[$rs->id] which should be 1
            $DB->update_record(BLOCK_EXACOMP_DB_SCHOOLTYPES, array("id" => $rs->id, "disabled" => $values[$rs->id]));
        } else {
            // goes here if it is null or 0
            $DB->update_record(BLOCK_EXACOMP_DB_SCHOOLTYPES, array("id" => $rs->id, "disabled" => 0));
        }
    }
}

/**
 * called when schooltype is changed, remove old topics
 *
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
        $sql = 'SELECT s.stid FROM {' . BLOCK_EXACOMP_DB_TOPICS . '} t
			JOIN {' . BLOCK_EXACOMP_DB_SUBJECTS . '} s ON t.subjid=s.id
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
 *
 * @param unknown_type $courseid
 * @param unknown_type $settings
 */
function block_exacomp_save_coursesettings($courseid, $settings) {
    global $DB;

    $old_course_settings = block_exacomp_get_settings_by_course($courseid);
    $DB->delete_records(BLOCK_EXACOMP_DB_SETTINGS, array("courseid" => $courseid));

    /*	if ($settings->grading > block_exacomp_get_assessment_points_limit(true)) {
		$settings->grading = block_exacomp_get_assessment_points_limit(true);
	}*/
    if ($settings->grading > BLOCK_EXACOMP_COURSE_POINT_LIMIT) {
        $settings->grading = BLOCK_EXACOMP_COURSE_POINT_LIMIT;
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
			FROM {' . BLOCK_EXACOMP_DB_COMPETENCE_USER_MM . '} comp
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
 *
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
 *
 * @param int $courseid
 */
function block_exacomp_get_grading_scheme($courseid) {
    $settings = block_exacomp_get_settings_by_course($courseid);

    return $settings->grading;
}

/**
 *
 * Builds topic title to print
 *
 * @param stdClass $topic
 */
function block_exacomp_get_output_fields($topic) {
    $output_id = '';
    //$output_title = $topic->title;
    $output_title = nl2br($topic->title);
    $remove = array("\n", "\r\n", "\r", "<p>", "</p>", "<h1>", "</h1>", "<br>", "<br />", "<br/>");
    $output_title = str_replace($remove, ' ', $output_title); // new lines to space
    $output_title = preg_replace('!\s+!', ' ', $output_title); // multiple spaces to single
    $output_title = fix_utf8($output_title);
    //if (preg_match('!^([^\s]*[0-9][^\s]*+)\s+(.*)$!iu', $output_title, $matches)) {
    //    //$output_id = $matches[1];
    //    $output_id = '';
    //    $output_title = $matches[2];
    //}

    return array($output_id, $output_title);
}

/**
 *
 * Awards badges to user
 *
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
				FROM {' . BLOCK_EXACOMP_DB_DESCRIPTORS . '} d
				JOIN {' . BLOCK_EXACOMP_DB_DESCBADGE . '} db ON d.id=db.descid AND db.badgeid=?
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
 *
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
 *
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
				FROM {' . BLOCK_EXACOMP_DB_DESCRIPTORS . '} d
				JOIN {' . BLOCK_EXACOMP_DB_DESCBADGE . '} db ON d.id=db.descid AND db.badgeid=?
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
                $badge->descriptorStatus[] = html_writer::empty_tag('img', array('src' => new moodle_url('/blocks/exacomp/pix/accept.png'), 'style' => 'vertical-align:text-bottom;')) . $descriptor->title;
            } else {
                $badge->descriptorStatus[] = html_writer::empty_tag('img', array('src' => new moodle_url('/blocks/exacomp/pix/cancel.png'), 'style' => 'vertical-align:text-bottom;')) . $descriptor->title;
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
 *
 * @param unknown_type $badgeid
 */
function block_exacomp_get_badge_descriptors($badgeid) {
    global $DB;

    return $DB->get_records_sql('
			SELECT d.*
			FROM {' . BLOCK_EXACOMP_DB_DESCRIPTORS . '} d
			JOIN {' . BLOCK_EXACOMP_DB_DESCBADGE . '} db ON d.id=db.descid AND db.badgeid=?
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
			FROM {' . BLOCK_EXACOMP_DB_COMPETENCE_ACTIVITY . '} mm
			JOIN {course_modules} m ON m.id = mm.activityid
			WHERE m.course = ? AND mm.eportfolioitem = 0
			ORDER BY comptype, compid', array($courseid));

    // records by new method relation
    $sql = 'SELECT DISTINCT CONCAT_WS(\'-\', t.id, demm.descrid, t.activityid) as tempId, t.id, demm.descrid as compid, ' . BLOCK_EXACOMP_TYPE_DESCRIPTOR . ' as comptype, t.activityid as activityid
                FROM {' . BLOCK_EXACOMP_DB_EXAMPLES . '} t
                    JOIN {course_modules} cm ON cm.id = t.activityid
                    JOIN {modules} m ON m.id = cm.module
                    JOIN {' . BLOCK_EXACOMP_DB_DESCEXAMP . '} demm ON demm.exampid = t.id
                WHERE cm.course = ? ';
    $records_new = $DB->get_records_sql($sql, [$courseid]);

    $records += $records_new; // old method + new method

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

function block_exacomp_get_assigments_of_descrtopic($filter_descriptors) {
    global $DB;
    if ($filter_descriptors) {
        $records = $DB->get_records_sql('
            SELECT mm.id, compid, comptype, activityid, activitytitle
			FROM {' . BLOCK_EXACOMP_DB_COMPETENCE_ACTIVITY . '} mm
			JOIN {course_modules} m ON m.id = mm.activityid
			WHERE (compid IN (' . join(',', $filter_descriptors) . ') AND comptype = 0 )
            OR (compid IN (
            SELECT topicid
            FROM {' . BLOCK_EXACOMP_DB_DESCTOPICS . '}
            WHERE descrid IN (' . join(',', $filter_descriptors) . '))
            AND comptype = 1 )');
    } else {
        $records = $DB->get_records_sql('
            SELECT mm.id, compid, comptype, activityid, activitytitle
			FROM {' . BLOCK_EXACOMP_DB_COMPETENCE_ACTIVITY . '} mm
			JOIN {course_modules} m ON m.id = mm.activityid');
    }

    $mm = array();
    $ret = array();

    foreach ($records as $record) {
        $mm[$record->activityid][$record->comptype][$record->compid] = $record->compid;
        $ret[1][$record->activityid] = $record->activitytitle;
        //         if ($record->comptype == BLOCK_EXACOMP_TYPE_DESCRIPTOR) {
        //             $mm->competencies[$record->compid][$record->activityid] = $record->activityid;
        //         } else {
        //             $mm->topics[$record->compid][$record->activityid] = $record->activityid;
        //         }
    }
    $ret[0] = $mm;
    return $ret;
}

function block_exacomp_get_assigments_of_examples($filter_descriptors) {
    global $DB;
    if ($filter_descriptors) {
        $records = $DB->get_records_sql('
            SELECT mm.id, mm.exampid, descrid, activityid, activitytitle
			FROM {' . BLOCK_EXACOMP_DB_DESCEXAMP . '} mm
			JOIN {' . BLOCK_EXACOMP_DB_EXAMPLES . '} e ON e.id = mm.exampid
			LEFT JOIN {course_modules} cm ON cm.id = activityid
			WHERE (descrid IN (' . join(',', $filter_descriptors) . ') )
			    AND NOT activityid = 0
			    AND cm.course IS NOT NULL');
    } else {
        $records = $DB->get_records_sql('
            SELECT mm.id, mm.exampid, descrid, activityid, activitytitle
			FROM {' . BLOCK_EXACOMP_DB_DESCEXAMP . '} mm
			JOIN {' . BLOCK_EXACOMP_DB_EXAMPLES . '} e ON e.id = mm.exampid
			LEFT JOIN {course_modules} cm ON cm.id = activityid
            WHERE NOT activityid = 0
                  AND cm.course IS NOT NULL');
    }

    $mm = array();
    $ret = array();

    foreach ($records as $record) {
        $mm[$record->activityid][0][$record->descrid] = $record->descrid;
        $ret[1][$record->activityid] = $record->activitytitle;
        $ret[2][$record->activityid] = $record->exampid;
        //         if ($record->comptype == BLOCK_EXACOMP_TYPE_DESCRIPTOR) {
        //             $mm->competencies[$record->compid][$record->activityid] = $record->activityid;
        //         } else {
        //             $mm->topics[$record->compid][$record->activityid] = $record->activityid;
        //         }
    }
    $ret[0] = $mm;
    return $ret;
}

function get_all_courses_key_value() {
    global $DB;
    $records = $DB->get_records_sql('
        SELECT e.courseid, c.fullname
        FROM {block_exacompsettings} e
        JOIN {course} c ON e.courseid = c.id'
    );
    $ret = array();
    $i = 0;

    foreach ($records as $record) {
        $ret[$record->courseid] = $record->fullname;
        $i++;
    }

    return $ret;
}

function get_all_template_courses_key_value() {
    global $DB;
    $records = $DB->get_records_sql('
        SELECT e.courseid, c.fullname
        FROM {block_exacompsettings} e
        JOIN {course} c ON e.courseid = c.id
        WHERE e.istemplate = 1'
    );
    $ret = array();
    $i = 0;

    foreach ($records as $record) {
        $ret[$record->courseid] = $record->fullname;
        $i++;
    }

    return $ret;
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
            // Skip deleted.
            if ($module->deletioninprogress == 1) {
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
			    FROM {' . BLOCK_EXACOMP_DB_COMPETENCE_ACTIVITY . '} mm
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
				SELECT CONCAT_WS(\'_\', v.id, vs.userid, v.shareall, v.externaccess, vb.id) as uniqid, vs.userid, v.shareall, v.externaccess, v.id, v.hash, v.userid as owner
				  FROM {block_exaportviewblock} vb
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
 *
 * @param array $associated_modules
 * @param stdClass $student
 *
 * @return stdClass $icon
 */
function block_exacomp_get_icon_for_user($associated_modules, $student) {
    global $CFG, $DB;
    require_once $CFG->libdir . '/gradelib.php';

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
            // graded
            $found = true;
            // $icon->text .= html_writer::empty_tag("img", array("src" => "pix/list_12x11.png"));
            $icon->text .= html_writer::empty_tag("img", array("src" => "pix/ok_16x16.png"));
        } else {
            // $icon->text .= html_writer::empty_tag("img", array("src" => "pix/x_11x11.png"));
            $iconurl = $cm->get_icon_url();
            $icon->text .= html_writer::empty_tag('img', array('src' => $iconurl, 'class' => 'smallicon', 'alt' => ' ', 'width' => 16));
        }

        $icon->text .= ' ';

        $entry = s($cm->get_formatted_name());
        if (isset($gradeinfo->items[0]->grades[$student->id])) {
            $entry .= ', ' . block_exacomp_get_string('grading') . ': ' . $gradeinfo->items[0]->grades[$student->id]->str_long_grade;
        }

        if ($cm->modname == 'quiz' && block_exacomp_is_teacher()) {
            $url = new moodle_url('/mod/quiz/report.php?id=' . $cm->id . '&mode=overview');
        } else {
            $url = new moodle_url('/mod/' . $cm->modname . '/view.php?id=' . $cm->id);
        }
        $icon->text .= '<a href="' . $url . '">' . $entry . '</a>';

        $icon->text .= '</div>';
    }

    // Always needed icon?
    $icon->img = html_writer::empty_tag("img", array("src" => "pix/list_12x11.png", "alt" => block_exacomp_get_string("legend_activities")));
    /*if ($found) {
        $icon->img = html_writer::empty_tag("img", array("src" => "pix/list_12x11.png", "alt" => block_exacomp_get_string("legend_activities")));
    } else {
        $icon->img = html_writer::empty_tag("img", array("src" => "pix/x_11x11.png", "alt" => block_exacomp_get_string("usernosubmission", null, fullname($student))));
    }*/

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

    //delete unconnected examples
    //add blocking events and free materials to examples which are not deleted
    $blocking_events = g::$DB->get_records(BLOCK_EXACOMP_DB_EXAMPLES, array('blocking_event' => 1));
    $blocking_events = array_merge($blocking_events, g::$DB->get_records(BLOCK_EXACOMP_DB_EXAMPLES, array('blocking_event' => 2)));

    block_exacomp_update_example_visibilities($courseid, $examples, array_column($blocking_events, "id", "id"));

    foreach ($blocking_events as $event) {
        $examples[$event->id] = $event;
    }

    $where = $examples ? join(',', array_keys($examples)) : '-1';
    g::$DB->execute("DELETE FROM {" . BLOCK_EXACOMP_DB_SCHEDULE . "} WHERE courseid = ? AND exampleid NOT IN($where)", array($courseid));
}

/**
 *
 * Assign topics to course
 *
 * @param int $courseid
 * @param array $topicids
 * @param bool $fromWS
 * @return null
 */
function block_exacomp_set_coursetopics($courseid, $topicids, $fromWS = false) {
    global $DB;
    $topicsToDelete = null;
    if ($fromWS) { // will be deleted all related topics and will saved only current selected
        $DB->delete_records(BLOCK_EXACOMP_DB_COURSETOPICS, array("courseid" => $courseid));
    } else {
        // will be deleted only '-X' topics, but saved 'X'
        $topicsToAdd = $topicsToDelete = array();
        foreach ($topicids as $topicid) {
            $topicsToDelete[] = abs($topicid);
            if ($topicid > 0) {
                $topicsToAdd[] = $topicid;
            }
        }
        $topicids = $topicsToAdd; // what will be added
        if (count($topicsToDelete) > 0) { // what will be deleted (all from form)
            $DB->execute('DELETE
                            FROM {' . BLOCK_EXACOMP_DB_COURSETOPICS . '}
                            WHERE courseid = ?
                                AND topicid IN (' . implode(',', $topicsToDelete) . ') ',
                [$courseid]);
        }
    }

    block_exacomp_update_topic_visibilities($courseid, $topicids, $topicsToDelete);

    foreach ($topicids as $topicid) {
        $DB->insert_record(BLOCK_EXACOMP_DB_COURSETOPICS, array("courseid" => $courseid, "topicid" => $topicid));
    }

    block_exacomp_normalize_course_visibilities($courseid);
}

/**
 *
 * given descriptor list is visible in cour
 *
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
 *
 * @param unknown_type $courseid
 * @param unknown_type $descriptors
 */
function block_exacomp_update_example_visibilities($courseid, $examples, $blocking_events = array()) {
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
        if (!array_key_exists($visible, $finalexamples) && !array_key_exists($visible, $blocking_events)) {
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
 *
 * @param int $courseid
 * @param array $topicids
 * @param mixed $deleteOnly
 */
function block_exacomp_update_topic_visibilities($courseid, $topicids, $deleteOnly = null) {
    global $DB;

    $visibilities = $DB->get_fieldset_select(BLOCK_EXACOMP_DB_TOPICVISIBILITY, 'topicid', 'courseid=? AND studentid=0 AND niveauid IS NULL', array($courseid));

    //manage visibility, do not delete user visibility, but delete unused entries
    foreach ($topicids as $topicid) {
        //new descriptors in table
        if (!in_array($topicid, $visibilities)) {
            $visibilities[] = $topicid;
            $DB->insert_record(BLOCK_EXACOMP_DB_TOPICVISIBILITY, array("courseid" => $courseid, "topicid" => $topicid, "studentid" => 0, "visible" => 1));
        }
    }

    foreach ($visibilities as $visible) {
        // delete ununsed descriptors for course and for special students
        if ($deleteOnly !== null) {
            // used for nonWS
            if (!in_array($visible, $topicids) && in_array($visible, $deleteOnly)) {
                // check if used in cross-subjects --> then it must still be visible
                // and check it if it requested to delete
                $DB->delete_records(BLOCK_EXACOMP_DB_TOPICVISIBILITY, array("courseid" => $courseid, "topicid" => $visible, "niveauid" => null));
            }
        } else if (!in_array($visible, $topicids)) {
            //check if used in cross-subjects --> then it must still be visible
            $DB->delete_records(BLOCK_EXACOMP_DB_TOPICVISIBILITY, array("courseid" => $courseid, "topicid" => $visible, "niveauid" => null));
        }
    }

}

/**
 *
 * Returns quizes related or assigned to competencies in this course
 *
 * @param unknown_type $courseid
 */
function block_exacomp_get_active_tests_by_course($courseid) {
    global $DB;

    $testsForExamples = array();
    $testsForDescriptors = array();

    $sql = 'SELECT DISTINCT cm.instance as id, cm.id as activityid, q.grade
            FROM {' . BLOCK_EXACOMP_DB_EXAMPLES . '} e
              JOIN {course_modules} cm ON cm.id = e.activityid
              JOIN {modules} m ON m.id = cm.module
              JOIN {quiz} q ON cm.instance = q.id
            WHERE m.name = \'quiz\' AND cm.course = ? ';
    $testsForExamples = $DB->get_records_sql($sql, array($courseid));

    foreach ($testsForExamples as $test) {
        $test->examples = $DB->get_records(BLOCK_EXACOMP_DB_EXAMPLES, array('activityid' => $test->activityid, 'courseid' => $courseid), '', 'id');
    }

    if (block_exacomp_use_old_activities_method()) { //if not, use ONLY new method. but if old method is active, use BOTH
        $sql = "SELECT DISTINCT cm.instance as id, cm.id as activityid, q.grade
            FROM {block_exacompcompactiv_mm} activ
              JOIN {course_modules} cm ON cm.id = activ.activityid
              JOIN {modules} m ON m.id = cm.module
              JOIN {quiz} q ON cm.instance = q.id
            WHERE m.name = 'quiz' AND cm.course = ?";

        $testsForDescriptors = $DB->get_records_sql($sql, array($courseid));

        foreach ($testsForDescriptors as $test) {
            $test->descriptors = $DB->get_records(BLOCK_EXACOMP_DB_COMPETENCE_ACTIVITY,
                array('activityid' => $test->activityid, 'comptype' => BLOCK_EXACOMP_TYPE_DESCRIPTOR), null, 'compid');
            $test->topics = $DB->get_records(BLOCK_EXACOMP_DB_COMPETENCE_ACTIVITY,
                array('activityid' => $test->activityid, 'comptype' => BLOCK_EXACOMP_TYPE_TOPIC), null, 'compid');
        }

        //Before merging: find any activities which have been related as well as assigned
        //if both has happended: combine examples and descriptors into one, and remove the other. Otheriwse problems will occur later on (overwriting of timestamps)
        foreach ($testsForExamples as $testEx) {
            foreach ($testsForDescriptors as $key => $testDescr) {
                if ($testEx->activityid == $testDescr->activityid) {
                    $testEx->descriptors = $testDescr->descriptors;
                    $testEx->topics = $testDescr->topics;
                    unset($testsForDescriptors[$key]);
                }
            }
        }

        $tests = array_merge($testsForExamples, $testsForDescriptors);
    } else {
        $tests = $testsForExamples;
    }

    return $tests;
}

/**
 *
 * Returns activities related or assigned to competencies in this course
 *
 * @param unknown_type $courseid
 */
function block_exacomp_get_active_activities_by_course($courseid) {
    global $DB;

    $activitiesForExamples = array();
    $activitiesForDescriptors = array();

    $sql = 'SELECT DISTINCT cm.instance as id, cm.id as activityid
        FROM {' . BLOCK_EXACOMP_DB_EXAMPLES . '} e
          JOIN {course_modules} cm ON cm.id = e.activityid
          JOIN {modules} m ON m.id = cm.module
        WHERE NOT m.name = \'quiz\' AND cm.course = ? ';
    $activitiesForExamples = $DB->get_records_sql($sql, array($courseid));

    foreach ($activitiesForExamples as $activity) {
        $activity->examples = $DB->get_records(BLOCK_EXACOMP_DB_EXAMPLES, array('activityid' => $activity->activityid, 'courseid' => $courseid), '', 'id');
    }

    if (block_exacomp_use_old_activities_method()) { //if not, use ONLY new method. but if old method is active, use BOTH
        $sql = "SELECT cm.instance as id, cm.id as activityid
            FROM {block_exacompcompactiv_mm} activ
              JOIN {course_modules} cm ON cm.id = activ.activityid
              JOIN {modules} m ON m.id = cm.module
            WHERE NOT m.name = 'quiz' AND cm.course = ? ";

        $activitiesForDescriptors = $DB->get_records_sql($sql, array($courseid));

        foreach ($activitiesForDescriptors as $activity) {
            $activity->descriptors = $DB->get_records(BLOCK_EXACOMP_DB_COMPETENCE_ACTIVITY,
                array('activityid' => $activity->activityid, 'comptype' => BLOCK_EXACOMP_TYPE_DESCRIPTOR), null, 'compid');
            $activity->topics = $DB->get_records(BLOCK_EXACOMP_DB_COMPETENCE_ACTIVITY,
                array('activityid' => $activity->activityid, 'comptype' => BLOCK_EXACOMP_TYPE_TOPIC), null, 'compid');
        }

        //Before merging: find any activities which have been related as well as assigned
        //if both has happended: combine examples and descriptors into one, and remove the other. Otheriwse problems will occur later on (overwriting of timestamps)
        foreach ($activitiesForExamples as $actEx) {
            foreach ($activitiesForDescriptors as $key => $actDescr) {
                if ($actEx->activityid == $actDescr->activityid) {
                    $actEx->descriptors = $actDescr->descriptors;
                    $actEx->topics = $actDescr->topics;
                    unset($activitiesForDescriptors[$key]);
                }
            }
        }

        $activities = array_merge($activitiesForDescriptors, $activitiesForExamples);
    } else {
        $activities = $activitiesForExamples;
    }

    return $activities;
}

/**
 *
 * Returns all activities related or assigned to competencies in this course (also quizes)
 * This function is used for updating the visibilities
 *
 * @param unknown_type $courseid
 */
function block_exacomp_get_all_associated_activities_by_course($courseid) {
    global $DB;

    $sql = 'SELECT DISTINCT cm.id as activityid, cm.instance as id
        FROM {' . BLOCK_EXACOMP_DB_EXAMPLES . '} e
          JOIN {course_modules} cm ON cm.id = e.activityid
        WHERE cm.course = ? ';
    $activitiesForExamples = $DB->get_records_sql($sql, array($courseid));

    foreach ($activitiesForExamples as $activity) {
        $activity->examples = $DB->get_records(BLOCK_EXACOMP_DB_EXAMPLES, array('activityid' => $activity->activityid, 'courseid' => $courseid), '', 'id');
    }

    if (block_exacomp_use_old_activities_method()) { // if not, use ONLY new method. but if old method is active, use BOTH
        $sql = "SELECT DISTINCT concat(cm.instance, '-', cm.id) as uniqieId, cm.instance as id, cm.id as activityid
            FROM {block_exacompcompactiv_mm} activ
              JOIN {course_modules} cm ON cm.id = activ.activityid
            WHERE cm.course = ? ";

        $activitiesForDescriptorsAndTopics = $DB->get_records_sql($sql, array($courseid));

        foreach ($activitiesForDescriptorsAndTopics as $activity) {
            $activity->descriptors = $DB->get_records(BLOCK_EXACOMP_DB_COMPETENCE_ACTIVITY,
                array('activityid' => $activity->activityid, 'comptype' => BLOCK_EXACOMP_TYPE_DESCRIPTOR), null, 'compid');
            $activity->topics = $DB->get_records(BLOCK_EXACOMP_DB_COMPETENCE_ACTIVITY,
                array('activityid' => $activity->activityid, 'comptype' => BLOCK_EXACOMP_TYPE_TOPIC), null, 'compid');
        }

        //Before merging: find any activities which have been related as well as assigned
        //if both has happended: combine examples and descriptors into one, and remove the other. Otheriwse problems will occur later on (overwriting of timestamps)
        foreach ($activitiesForExamples as $actEx) {
            foreach ($activitiesForDescriptorsAndTopics as $key => $actDescr) {
                if ($actEx->activityid == $actDescr->activityid) {
                    $actEx->descriptors = $actDescr->descriptors;
                    $actEx->topics = $actDescr->topics;
                    unset($activitiesForDescriptorsAndTopics[$key]);
                }
            }
        }

        $activities = array_merge($activitiesForDescriptorsAndTopics, $activitiesForExamples);
    } else {
        $activities = $activitiesForExamples;
    }

    return $activities;
}

/**
 * Returns activities, which related to competences
 *
 * @param mixed $courseid
 * @param array $conditions
 * @return array
 */
function block_exacomp_get_related_activities($courseid, $conditions = array()) {
    global $DB;

    if (block_exacomp_use_old_activities_method()) {
        $tablename = BLOCK_EXACOMP_DB_COMPETENCE_ACTIVITY;
    } else {
        $tablename = BLOCK_EXACOMP_DB_EXAMPLES;
    }
    $sql = 'SELECT DISTINCT cm.*, m.name as modname
                FROM {' . $tablename . '} t
                    JOIN {course_modules} cm ON cm.id = t.activityid
                    JOIN {modules} m ON m.id = cm.module
                WHERE cm.course = ? ';
    $cond = array($courseid);

    if (@$conditions['availability']) {
        $sql .= ' AND availability IS NOT NULL ';
    }
    $acts = $DB->get_records_sql($sql, $cond);

    foreach ($acts as $activ) {
        if (block_exacomp_use_old_activities_method()) {
            $activ->descriptors = $DB->get_records(BLOCK_EXACOMP_DB_COMPETENCE_ACTIVITY,
                array('activityid' => $activ->id, 'comptype' => BLOCK_EXACOMP_TYPE_DESCRIPTOR), null, 'compid');
            $activ->topics = $DB->get_records(BLOCK_EXACOMP_DB_COMPETENCE_ACTIVITY,
                array('activityid' => $activ->id, 'comptype' => BLOCK_EXACOMP_TYPE_TOPIC), null, 'compid');
        } else {
            $activ->examples = $DB->get_records(BLOCK_EXACOMP_DB_EXAMPLES, array('activityid' => $activ->id, 'courseid' => $courseid), '', 'id');
        }
    }

    return $acts;
}

/**block_instances
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

/**block_instances
 *
 * Returns all course ids where an instance of Exabis Competences is installed
 */
function block_exacomp_get_course_names() {

    $instances = g::$DB->get_records('block_instances', array('blockname' => 'exacomp'));

    $exabis_competences_courses = array();

    foreach ($instances as $instance) {
        $context = g::$DB->get_record('context', array('id' => $instance->parentcontextid, 'contextlevel' => CONTEXT_COURSE));
        if ($context) {
            $exabis_competences_courses[$context->instanceid] = g::$DB->get_field('course', 'shortname', array('id' => $context->instanceid));
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
 * Gets URL for particular activity
 *
 * @param mixed $activity
 * @param boolean $student
 */
function block_exacomp_get_activityurl($activity, $student = false) {
    global $DB;

    $mod = $DB->get_record('modules', array("id" => $activity->module));

    if ($mod->name == "assignment" && !$student) {
        return new moodle_url('/mod/assignment/submissions.php', array('id' => $activity->id));
    } else {
        return new moodle_url('/mod/' . $mod->name . '/view.php', array('id' => $activity->id));
    }
}

/**
 *
 * Gets course module name for module
 *
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
 *
 * @param unknown_type $data
 * @param unknown_type $courseid
 * @param unknown_type $comptype
 */
function block_exacomp_save_competences_activities($data, $courseid, $comptype) {
    global $USER;
    if ($data != null) {
        foreach ($data as $cmoduleKey => $comps) {
            if (!empty($cmoduleKey)) {
                foreach ($comps as $compidKey => $val) {
                    //set activity
                    if ($val) {
                        block_exacomp_set_compactivity($cmoduleKey, $compidKey, $comptype);
                    }
                }
            }
        }
    }
}

/**
 *
 * Assign one competence to one activity
 *
 * @param integer $activityid
 * @param integer $compid
 * @param mixed $comptype
 * @param string $activitytitle
 */
function block_exacomp_set_compactivity($activityid, $compid, $comptype, $activitytitle = null) {
    global $DB, $COURSE;

    if ($activitytitle == null) {
        $cmmod = $DB->get_record('course_modules', array("id" => $activityid));
        $modulename = $DB->get_record('modules', array("id" => $cmmod->module));
        $instance = get_coursemodule_from_id($modulename->name, $activityid);
        $activitytitle = $instance->name;
    }
    $DB->delete_records(BLOCK_EXACOMP_DB_COMPETENCE_ACTIVITY, array("activityid" => $activityid, "compid" => $compid, "comptype" => $comptype, "eportfolioitem" => 0));
    $DB->insert_record(BLOCK_EXACOMP_DB_COMPETENCE_ACTIVITY, array("activityid" => $activityid, "compid" => $compid, "comptype" => $comptype, "coursetitle" => $COURSE->shortname, 'activitytitle' => $activitytitle));
}

/**
 *
 * Assign one example to one activity
 *
 * @param integer $activityid
 * @param integer $exampleid
 * @param string $activitytitle
 */
function block_exacomp_set_exampleactivity($activityid, $exampleid, $activitytitle = null) {
    global $DB, $COURSE, $CFG;

    if ($activitytitle == null) {
        $cmmod = $DB->get_record('course_modules', array("id" => $activityid));
        $modulename = $DB->get_record('modules', array("id" => $cmmod->module));
        $instance = get_coursemodule_from_id($modulename->name, $activityid);
        $activitytitle = $instance->name;
        $activityLink = block_exacomp_get_activityurl($instance)->out(false);
    } else {
        $link = $DB->get_field(BLOCK_EXACOMP_DB_EXAMPLES, 'activitylink', array('id' => $exampleid));
        $newLink = explode("=", $link);
        $activityLink = '';
        if ($newLink[0]) { // only if a link is not empty
            $activityLink = $newLink[0] . "=" . $activityid;
        }
    }
    $DB->update_record(BLOCK_EXACOMP_DB_EXAMPLES, array("id" => $exampleid, "activityid" => $activityid, "activitytitle" => $activitytitle, "activitylink" => $activityLink, "externaltask" => $activityLink));
}

/**
 *
 * Delete competence, activity associations
 */
function block_exacomp_delete_competences_activities($modulekey = null, $comptype = null) {
    global $COURSE, $DB;

    $where = array(
        'course' => $COURSE->id,
    );
    if ($modulekey) {
        $where['id'] = $modulekey;
    }
    $cmodules = $DB->get_records('course_modules', $where);

    $del_where = array(
        'eportfolioitem' => 0,
    );
    if ($comptype !== null) {
        $del_where['comptype'] = $comptype;
    }
    foreach ($cmodules as $cm) {
        $del_where['activityid'] = $cm->id;
        $DB->delete_records(BLOCK_EXACOMP_DB_COMPETENCE_ACTIVITY, $del_where);
    }
}

/**
 * Get activity for particular competence
 *
 * @param unknown_type $descid
 * @param unknown_type $courseid
 * @param unknown_type $descriptorassociation
 */
function block_exacomp_get_activities($compid, $courseid = null, $comptype = BLOCK_EXACOMP_TYPE_DESCRIPTOR) { //alle assignments die einem bestimmten descriptor zugeordnet sind
    global $CFG, $DB;
    $query = 'SELECT mm.id as uniqueid, a.id, ass.grade, a.instance
	            FROM {' . BLOCK_EXACOMP_DB_COMPETENCE_ACTIVITY . '} mm
	                INNER JOIN {course_modules} a ON a.id = mm.activityid
	                LEFT JOIN {assign} ass ON ass.id = a.instance
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
 *
 * @param unknown $courseid
 * @param string $fields
 */
function block_exacomp_get_activities_by_course($courseid, $fields = 'camm.activityid as id, camm.activitytitle as title') {
    global $DB;
    $query = 'SELECT DISTINCT ' . $fields . '
        FROM {' . BLOCK_EXACOMP_DB_COMPETENCE_ACTIVITY . '} camm
		    INNER JOIN {course_modules} cm ON cm.id = camm.activityid
		    LEFT JOIN {block_exacompdescriptors} d ON d.id = camm.compid
		    LEFT JOIN {block_exacomptopics} t ON t.id = camm.compid

		WHERE cm.course = ? AND camm.eportfolioitem = 0';

    return $DB->get_records_sql($query, array($courseid));
}

/**
 * Get activityname from example
 *
 * @param unknown $activityid
 */
function block_exacomp_get_activities_from_example($activityid) {
    global $DB;

    return $DB->get_field('block_exacompexamples', 'activitytitle', array('activityid' => $activityid));
}

/**
 * Get activity by id
 * This function returns all needed information about an activity
 *
 * @param unknown $activityid
 */
function block_exacomp_get_activitiy_by_id($activityid) {
    global $DB;

    $module = $DB->get_record('course_modules', array('id' => $activityid));
    $instance = $DB->get_field('modules', 'name', array('id' => $module->module));
    return $DB->get_record($instance, array('id' => $module->instance));
}

/**
 * @param array $descriptorsData
 * @param integer $courseid
 */
function block_exacomp_update_example_activity_relations($descriptorsData = array(), $courseid = 0) {
    global $DB, $CFG, $USER;
    foreach ($descriptorsData as $activityid => $descriptors) {
        $relatedDescriptors = array_filter($descriptors);
        $relatedDescriptors = array_keys($relatedDescriptors);
        if (!empty($relatedDescriptors)) { // if empty --> example would be created but not assigned to any descriptor ==> don't create the example
            block_exacomp_relate_example_to_activity($courseid, $activityid, $relatedDescriptors);
        }
    }
}

/**
 * @param integer $courseid
 * @param integer $activityid
 * @param array $descriptors
 */
function block_exacomp_relate_example_to_activity($courseid, $activityid, $descriptors = array(), $komettranslator = false) {
    global $DB, $CFG, $USER;
    static $mod_info = null;
    if ($mod_info === null) {
        $mod_info = get_fast_modinfo($courseid);
    }

    //2022.01.13 remove this functionality since it is not needed for now, and not tested enough. Apparently $DB->get_record("enrol", array("courseid" => $courseid, "enrol" => "guest")) can return multiple values in some cases
    //2021.11.04 RW: Add a check for the course, if the course is usable by guests, the example should NOT have a courseid and therefore exist globally in this subject, not only in this course.
    //    $enrol = $DB->get_record("enrol", array("courseid" => $courseid, "enrol" => "guest"));
    //    if($enrol){
    //        $courseOpenForGuests = !$enrol->status; // 0 stands for ENABLED ==> !.. the course is open for guests if status == 0
    //    }else{
    //        $courseOpenForGuests = false;
    //    }
    //    $courseOpenForGuests = !$DB->get_record("enrol", array("courseid" => $courseid, "enrol" => "guest"))->status; // 0 stands for ENABLED ==> ! .. leads to nullpointer exception in some cases

    //    if (count($descriptors)) { // if no any descriptor - no sense to insert the example (no relation to activity)... RW 2020.01.12 it DOES make sense, because otherwise you cannot delete the last connection
    //        var_dump("Activityid: ", $activityid);
    //        var_dump("descriptors: ", $descriptors);

    $existsRelatedExample =
        $DB->get_record(BLOCK_EXACOMP_DB_EXAMPLES, array('courseid' => $courseid, 'activityid' => $activityid), '*',
            IGNORE_MULTIPLE);
    if ($existsRelatedExample) {
        $exampleId = $existsRelatedExample->id;
    } else {
        $module = get_coursemodule_from_id(null, $activityid);
        $activitylink = block_exacomp_get_activityurl($module)->out(false);
        $activitylink = str_replace($CFG->wwwroot . '/', '', $activitylink);
        $externaltask = block_exacomp_get_activityurl($module)->out(false);
        $cm = $mod_info->cms[$activityid];
        if ($cm) {
            $example_icons = $cm->get_icon_url()->out(false);
        }
        if ($example_icons) {
            $example_icons = serialize(array('externaltask' => $example_icons));
        } else {
            $example_icons = null;
        }

        //        2022.01.13 remove this functionality since it is not needed for now, and not tested enough
        //        //2021.11.04 RW: Add a check for the course, if the course is usable by guests, the example should NOT have a courseid and therefore exist globally in this subject, not only in this course.
        //        if($komettranslator && $courseOpenForGuests){
        //            $newExample = (object) array(
        //                'title' => $module->name,
        //                'courseid' => 0,
        //                'activityid' => $activityid,
        //                'activitylink' => $activitylink,
        //                'activitytitle' => $module->name,
        //                'externaltask' => $externaltask,
        //                'creatorid' => $USER->id,
        //                'parentid' => 0,
        //                'example_icon' => $example_icons
        //            );
        //        }else{
        $newExample = (object)array(
            'title' => $module->name,
            'courseid' => $courseid,
            'activityid' => $activityid,
            'activitylink' => $activitylink,
            'activitytitle' => $module->name,
            'externaltask' => $externaltask,
            'creatorid' => $USER->id,
            'parentid' => 0,
            'example_icon' => $example_icons,
        );
        //        }

        $exampleId = $DB->insert_record(BLOCK_EXACOMP_DB_EXAMPLES, $newExample);
    }

    // Add visibility in course: this is needed for the planning storage and weekly schedule
    // other courses
    $otherCourseids = block_exacomp_get_courseids_by_example($exampleId);
    // add myself (should be in there anyway)
    if (!in_array($courseid, $otherCourseids)) {
        $otherCourseids[] = $courseid;
    }

    foreach ($otherCourseids as $otherCourseid) {
        //add visibility if not exists
        if (!$DB->get_record(BLOCK_EXACOMP_DB_EXAMPVISIBILITY, array('courseid' => $otherCourseid, 'exampleid' => $exampleId, 'studentid' => 0))) {
            $DB->insert_record(BLOCK_EXACOMP_DB_EXAMPVISIBILITY, array('courseid' => $otherCourseid, 'exampleid' => $exampleId, 'studentid' => 0, 'visible' => 1));
        }
        if (!$DB->get_record(BLOCK_EXACOMP_DB_SOLUTIONVISIBILITY, array('courseid' => $otherCourseid, 'exampleid' => $exampleId, 'studentid' => 0))) {
            $DB->insert_record(BLOCK_EXACOMP_DB_SOLUTIONVISIBILITY, array('courseid' => $otherCourseid, 'exampleid' => $exampleId, 'studentid' => 0, 'visible' => 1));
        }
    }

    // clean old relations to descriptors
    $DB->delete_records(BLOCK_EXACOMP_DB_DESCEXAMP, array('exampid' => $exampleId));
    // insert new relations to descriptors
    foreach ($descriptors as $descriptorid) {
        $newRelation = (object)array(
            'exampid' => $exampleId,
            'descrid' => $descriptorid,
        );
        $DB->insert_record(BLOCK_EXACOMP_DB_DESCEXAMP, $newRelation);
    }

}

;

/**
 * init data for competencegrid, shown in tab "Overview"
 *
 * @param unknown $courseid
 * @param unknown $subjectid
 * @param unknown $studentid
 * @param string $showallexamples
 * @param array $filteredtaxonomies
 * @return unknown[]|NULL[][]
 */
function block_exacomp_init_competence_grid_data($courseid, $subjectid, $studentid, $showallexamples = false, $filteredtaxonomies = array(SHOW_ALL_TAXONOMIES)) {
    global $DB;

    if ($studentid) {
        $cm_mm = block_exacomp_get_course_module_association($courseid);
        $course_mods = get_fast_modinfo($courseid)->get_cms();
    }

    $selection = array();

    $niveaus = block_exacomp_get_niveaus_for_subject($subjectid); // careful: changed this function on 23.07.2020... probably doesnt matter because this commented code is dead
    $niveaus = array_map(function($n) {
        return $n->title;
    }, $niveaus);
    $skills = $DB->get_records_menu('block_exacompskills', null, null, "id, title");
    $descriptors = block_exacomp_get_descriptors_by_subject($subjectid);

    //		$supported = block_exacomp_get_supported_modules();

    $data = array();
    if ($studentid > 0) {
        $competencies = array(
            "studentcomps" => $DB->get_records(BLOCK_EXACOMP_DB_COMPETENCES, array("role" => BLOCK_EXACOMP_ROLE_STUDENT, "courseid" => $courseid, "userid" => $studentid, "comptype" => BLOCK_EXACOMP_TYPE_DESCRIPTOR), "",
                "compid,userid,reviewerid,value"),
            "teachercomps" => $DB->get_records(BLOCK_EXACOMP_DB_COMPETENCES, array("role" => BLOCK_EXACOMP_ROLE_TEACHER, "courseid" => $courseid, "userid" => $studentid, "comptype" => BLOCK_EXACOMP_TYPE_DESCRIPTOR), "",
                "compid,userid,reviewerid,value,evalniveauid"));
    }
    // Arrange data in associative array for easier use
    $topics = array();
    $data = array();
    foreach ($descriptors as $descriptor) {
        if ($descriptor->parentid > 0) {
            continue;
        }
        if (!block_exacomp_is_niveau_visible($courseid, $descriptor->topicid, 0, $descriptor->niveauid)) {
            continue;
        }

        /*$descriptor->children = $DB->get_records('block_exacompdescriptors', array('parentid' => $descriptor->id));

        $examples = $DB->get_records_sql(
                "SELECT de.id as deid, e.id, e.title, e.externalurl,
                e.externalsolution, e.externaltask, e.completefile, e.description, e.creatorid
                FROM {" . BLOCK_EXACOMP_DB_EXAMPLES . "} e
                JOIN {" . BLOCK_EXACOMP_DB_DESCEXAMP . "} de ON e.id=de.exampid
                WHERE de.descrid=?"
                . ($showallexamples ? "" : " AND e.creatorid > 0")
                , array($descriptor->id));

        foreach ($examples as $example){
            $example->taxonomies = block_exacomp_get_taxonomies_by_example($example);

            $taxtitle = "";
            foreach($example->taxonomies as $taxonomy){
                $taxtitle .= $taxonomy->title.", ";
            }

            $taxtitle = substr($taxtitle, 0, strlen($taxtitle)-1);
            $example->tax = $taxtitle;
        }
        $filtered_examples = array();
        if (!in_array(BLOCK_EXACOMP_SHOW_ALL_TAXONOMIES, $filteredtaxonomies)){
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
        }*/

        if ($studentid > 0) {
            $descriptor->studentcomp = (array_key_exists($descriptor->id, $competencies['studentcomps'])) ? $competencies['studentcomps'][$descriptor->id]->value : false;
            $descriptor->teachercomp = (array_key_exists($descriptor->id, $competencies['teachercomps'])) ? $competencies['teachercomps'][$descriptor->id]->value : false;
            // ICONS
            if (isset($cm_mm->competencies[$descriptor->id])) {
                //get CM instances
                $cm_temp = array();
                foreach ($cm_mm->competencies[$descriptor->id] as $cmid) {
                    $cm_temp[] = $course_mods[$cmid];
                }

                $icon = block_exacomp_get_icon_for_user($cm_temp, $DB->get_record("user", array("id" => $studentid)), @$supported);
                $descriptor->icon = '<span title="' . $icon->text . '" class="exabis-tooltip">' . $icon->img . '</span>';
            }
        }
        $data[$descriptor->skillid][$descriptor->topicid][$descriptor->niveauid][] = $descriptor;
        $topics[$descriptor->topicid] = $descriptor->topic_title;
    }

    $selection = $DB->get_records(BLOCK_EXACOMP_DB_COURSETOPICS, array('courseid' => $courseid), '', 'topicid');

    return array($niveaus, $skills, $topics, $data, $selection);

}

/**
 * return all avaiable niveaus within one subject (LFS for LIS)
 *
 * @param unknown $subjectid
 */
function block_exacomp_get_niveaus_for_subject($subjectid) {
    global $DB;
    //sql could be optimized
    $niveaus = "SELECT DISTINCT n.id as id, n.title, n.sorting, n.*
			FROM {" . BLOCK_EXACOMP_DB_DESCRIPTORS . "} d, {" . BLOCK_EXACOMP_DB_DESCTOPICS . "} dt, {" . BLOCK_EXACOMP_DB_NIVEAUS . "} n
			WHERE d.id=dt.descrid AND dt.topicid IN
				(SELECT id FROM {" . BLOCK_EXACOMP_DB_TOPICS . "} WHERE subjid = ?)
			    AND d.niveauid > 0 AND d.niveauid = n.id AND d.parentid = 0
			ORDER BY n.numb, n.sorting, n.id";

    return $DB->get_records_sql($niveaus, array($subjectid));
    //before 23.07.2020:
    //       $niveaus = "SELECT DISTINCT n.id as id, n.title, n.sorting
    //			FROM {".BLOCK_EXACOMP_DB_DESCRIPTORS."} d, {".BLOCK_EXACOMP_DB_DESCTOPICS."} dt, {".BLOCK_EXACOMP_DB_NIVEAUS."} n
    //			WHERE d.id=dt.descrid AND dt.topicid IN
    //				(SELECT id FROM {".BLOCK_EXACOMP_DB_TOPICS."} WHERE subjid=?)
    //			AND d.niveauid > 0 AND d.niveauid = n.id order by n.sorting, n.id";
    //
    //    var_dump($DB->get_records_sql_menu($niveaus, array($subjectid)));
}

/**
 * get all avaiable niveaus within full competence tree
 *
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
 *
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

    // TODO: tabellen block_exacompdescrvisibility, block_exacompitem_mm, block_exacompschedule gehÃ¶ren auch gelÃ¶scht?
}

/**
 * checks is block Exabis Eportfolio is installed
 */
function block_exacomp_exaportexists() {
    global $DB;

    return !!$DB->get_record('block', array('name' => 'exaport'));
}

/**
 * checks is block Exabis Planning tool is installed
 */
function block_exacomp_exaplanexists() {
    global $DB;

    return !!$DB->get_record('block', array('name' => 'exaplan'));
}

/**
 * checks if block Exabis Studentreview is installed
 *
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
 *
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
 *
 * @param unknown $userid
 */
function block_exacomp_reset_profile_settings($userid) {
    global $DB;
    $DB->delete_records(BLOCK_EXACOMP_DB_PROFILESETTINGS, array('userid' => $userid));
}

/**
 * set profile settings
 * at the moment only courses to be shown in profile can be stored
 *
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
 *
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
 *
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
 *
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

    return block_exacomp_get_string('teacher_tipp_1') . $total . block_exacomp_get_string('teacher_tipp_2') . $gained . block_exacomp_get_string('teacher_tipp_3');
}

/**
 *
 * Gets tree with schooltype on highest level
 *
 * @param integer $limit_courseid
 * @param bool $onlyWithSubjects
 * @return array
 */
function block_exacomp_build_schooltype_tree_for_courseselection($limit_courseid, $onlyWithSubjects = false) {
    global $SESSION;
    $schooltypes = block_exacomp_get_schooltypes_by_course($limit_courseid);

    // filtering
    if (isset($SESSION->courseselection_filter)) {
        $filter = $SESSION->courseselection_filter;
    } else {
        $filter = array();
    }
    if (count($filter) && array_key_exists('schooltype', $filter) && count($filter['schooltype']) > 0) {
        $schooltypes = array_filter($schooltypes, function($st) use ($filter) {
            if (in_array($st->id, $filter['schooltype'])) {
                return true;
            }
            return false;
        });
    }

    foreach ($schooltypes as $k => $schooltype) {
        $schooltype->subjects = block_exacomp_get_subjects_for_schooltype($limit_courseid, $schooltype->id);
        if ($onlyWithSubjects && !$schooltype->subjects) {
            unset($schooltypes[$k]);
        }
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
 * @param $courseid
 * @param $studentid
 * This function updates the visibilities of all examples in this course that have been created by relating moodle activities to exacomp competencies.
 * It also puts the examples on the schedule of the student.
 */
function block_exacomp_update_related_examples_visibilities_for_single_student($courseid, $studentid) {
    global $DB;

    // If the automatic grading is not activated in the moodle settings all queries can just be skipped entirely
    $autotest = get_config('exacomp', 'autotest');
    if (!$autotest) {
        return;
    }
    // If this course does not use moodle activities all queries can just be skipped entirely
    if (!block_exacomp_get_settings_by_course($courseid)->uses_activities) {
        return;
    }

    //also get all activities (also quizes)
    $activities = block_exacomp_get_all_associated_activities_by_course($courseid);
    block_exacomp_update_related_examples_visibilities($activities, $courseid, $studentid);
}

/**
 * @param $activities
 * @param $courseid
 * @param $studentid
 * @param $cms_availability
 * This function is used for performance reasons. If the visibilities should be updated for several students, then the activities should only be loaded once.
 */
function block_exacomp_update_related_examples_visibilities($activities, $courseid, $studentid) {
    global $DB;
    //  --- in the old task: here do it foreach student. In this function: only for this student
    // TODO: just as in the moodle course/view.php, get the information on the availability of the modules
    $modinfo = get_fast_modinfo($courseid, $studentid);
    $cms_availability = $modinfo->cms;
    // for every activity: check if it should be visible or not, and if it is on the schedule
    foreach ($activities as $activity) {
        // get availability (visibility) info
        if (property_exists($activity, "examples")) {
            if ($cms_availability[$activity->activityid]->available && $cms_availability[$activity->activityid]->visible) {
                foreach ($activity->examples as $example) {
                    g::$DB->insert_or_update_record(BLOCK_EXACOMP_DB_EXAMPVISIBILITY,
                        ['visible' => 1],
                        ['exampleid' => $example->id, 'courseid' => $courseid, 'studentid' => $studentid]
                    );
                    // if not on schedule: add
                    if (!$DB->get_records(BLOCK_EXACOMP_DB_SCHEDULE, array('studentid' => $studentid, 'exampleid' => $example->id, 'courseid' => $courseid))) {
                        block_exacomp_add_example_to_schedule($studentid, $example->id, $studentid, $courseid, null, null, -1, -1, null, null, null, null);
                    }
                }
            } else {
                // Hide the related exacomp material if not yet hidden
                foreach ($activity->examples as $example) {
                    g::$DB->insert_or_update_record(BLOCK_EXACOMP_DB_EXAMPVISIBILITY,
                        ['visible' => 0],
                        ['exampleid' => $example->id, 'courseid' => $courseid, 'studentid' => $studentid]
                    );
                    // if already on schedule: remove
                    $DB->delete_records(BLOCK_EXACOMP_DB_SCHEDULE, array('studentid' => $studentid, 'exampleid' => $example->id, 'courseid' => $courseid, 'creatorid' => $studentid));
                }
            }
        }
    }
}

/**
 * This function checkes for finished quizes that are associated with competences and automatically gains them if the
 * coresponding setting is activated.
 * deprecated since 10.02.2022 version 2022021000
 */
function block_exacomp_perform_auto_test() {
    global $CFG, $DB;

    $autotest = get_config('exacomp', 'autotest');
    $testlimit = get_config('exacomp', 'testlimit');
    if (!$autotest) {
        return;
    }

    //for all courses where exacomp is used
    $courses = block_exacomp_get_courseids();

    foreach ($courses as $courseid) {
        // If this course does not use moodle activities all queries can just be skipped entirely
        if (!block_exacomp_get_settings_by_course($courseid)->uses_activities) {
            continue;
        }

        //also get all other activites, that are NOT tests/quizes ... assigned and related (if settings allow both)
        $otherActivities = block_exacomp_get_active_activities_by_course($courseid);

        // tests associated with competences
        // get all tests/quizes that are associated with competences
        $tests = block_exacomp_get_active_tests_by_course($courseid);
        $students = block_exacomp_get_students_by_course($courseid);

        $cms = block_exacomp_get_related_activities($courseid, ['availability' => true]);
        // get "related" activities gets the assigned ones? depends on the setting  ==> TODO: it depends on a GLOBAL setting and that could be a problem
        // if the old method is allowed, the new method does not work anymore for the specific cases where restriction is used

        $mod_info = get_fast_modinfo($courseid);
        //$grading_scheme = block_exacomp_get_grading_scheme($courseid);
        // get student grading for each test

        foreach ($students as $student) {
            $modinfo = get_fast_modinfo($courseid, $student->id);
            $cms_availability = $modinfo->cms;

            $changedquizes = array();
            foreach ($tests as $test) {
                // get availability (visibility) info
                $available = $cms_availability[$test->activityid]->available;
                if (is_array(@$test->examples)) {
                    if (!$available) {
                        // Hide the related exacomp material if not yet hidden
                        foreach ($test->examples as $example) {
                            g::$DB->insert_or_update_record(BLOCK_EXACOMP_DB_EXAMPVISIBILITY,
                                ['visible' => 0],
                                ['exampleid' => $example->id, 'courseid' => $courseid, 'studentid' => $student->id]
                            );
                            // if already on schedule: remove
                            $DB->delete_records(BLOCK_EXACOMP_DB_SCHEDULE, array('studentid' => $student->id, 'exampleid' => $example->id, 'courseid' => $courseid, 'creatorid' => $student->id));
                        }
                    } else {
                        foreach ($test->examples as $example) {
                            g::$DB->insert_or_update_record(BLOCK_EXACOMP_DB_EXAMPVISIBILITY,
                                ['visible' => 1],
                                ['exampleid' => $example->id, 'courseid' => $courseid, 'studentid' => $student->id]
                            );
                            // if not on schedule: add ( the check happens in the function)
                            block_exacomp_add_example_to_schedule($student->id, $example->id, $student->id, $courseid, null, null, -1, -1, null, null, null, null);
                        }
                    }
                }
            }

            // For every activity that is not a quiz: check if it should be visible
            foreach ($otherActivities as $activity) {
                // get availability (visibility) info
                $available = $cms_availability[$activity->activityid]->available;
                if (!$available) {
                    // Hide the related exacomp material if not yet hidden
                    foreach ($activity->examples as $example) {
                        g::$DB->insert_or_update_record(BLOCK_EXACOMP_DB_EXAMPVISIBILITY,
                            ['visible' => 0],
                            ['exampleid' => $example->id, 'courseid' => $courseid, 'studentid' => $student->id]
                        );
                        // if already on schedule: remove
                        $DB->delete_records(BLOCK_EXACOMP_DB_SCHEDULE, array('studentid' => $student->id, 'exampleid' => $example->id, 'courseid' => $courseid, 'creatorid' => $student->id));
                    }
                } else {
                    foreach ($activity->examples as $example) {
                        g::$DB->insert_or_update_record(BLOCK_EXACOMP_DB_EXAMPVISIBILITY,
                            ['visible' => 1],
                            ['exampleid' => $example->id, 'courseid' => $courseid, 'studentid' => $student->id]
                        );
                        // if not on schedule: add ( the check happens in the function)
                        block_exacomp_add_example_to_schedule($student->id, $example->id, $student->id, $courseid, null, null, -1, -1, null, null, null, null);
                    }
                }
            }

            // activities with restrict access   // TODO: why is this needed RW 2021.09.06
            //			if ($CFG->enableavailability && count($cms) > 0) {
            //                foreach ($cms as $cm) {
            //                    if ($cm->availability && block_exacomp_cmodule_is_autocompetence($cm->id)) {
            //                        /** course_modinfo $mod_info */
            //                        if (array_key_exists($cm->id, $mod_info->cms)) {
            //                            $modInfo = $mod_info->cms[$cm->id];
            //                        } else {
            //                            continue;
            //                        }
            //
            //                        $info = new \core_availability\info_module($modInfo);
            //                        $tree = new \core_availability\tree(json_decode($cm->availability));
            //                        $result = $tree->check_available(false, $info, true, $student->id); // TODO: here an error occurs in certain situations (using autotest and tick setting in the restricted access tab of an activity)
            //                        $information = $tree->get_result_information($info, $result);
            //                        if ($result->is_available() && !$information) { // the user have got access to this module
            //                            $relatedData = array();
            //                            $existing = $DB->get_record('block_exacompcmassign',
            //                                    [   'coursemoduleid' => $cm->id,
            //                                        'userid' => $student->id
            //                                    ], '*');
            //                            // the value will be changed if:
            //                            // - the timemodified of root activity will be changed
            //                            // - the timemodified at least one of child activities will be changed
            //                            // - the timemodified of fixed value for student<->activity is changed (now it is only quizes)
            //                            // is it ok?
            //
            //                            //$modIst = get_coursemodule_from_instance();
            //
            //                            // root activity timemodified
            //                            $rootTs = $DB->get_field_sql('SELECT DISTINCT t.timemodified as timemodified
            //                                                            FROM {'.$cm->modname.'} t
            //                                                            WHERE t.id = ? ', [$cm->instance]);
            //                            $relatedData['roottimemodified'] = $rootTs;
            //                            // availability settings
            //                            $relatedData['availability'] = json_decode($cm->availability);
            //                            // data of related activities
            //                            $relData = array();
            //                            $maxResults = 0;
            //                            $studentResults = 0;
            //                            foreach($relatedData['availability']->c as $relObj) {
            //                                $modparam = $DB->get_record_sql('SELECT DISTINCT cm.instance as modid, m.name as modname
            //                                                            FROM {course_modules} cm
            //                                                            JOIN {modules} m ON cm.module = m.id
            //                                                            WHERE cm.id = ? ', [$relObj->id]);
            //                                if ($modparam) {
            //                                    // the timemodified gets from last saved student answer or from DB if the user is not changed the grading
            //                                    if (!array_key_exists($modparam->modid, $changedquizes)) {
            //                                        $modts = $DB->get_field_sql('SELECT DISTINCT t.timemodified as timemodified
            //                                                            FROM {'.$modparam->modname.'} t
            //                                                            WHERE t.id = ? ', [$modparam->modid]);
            //                                    } else {
            //                                        $modts = $changedquizes[$modparam->modid];
            //                                    }
            //                                    $relObj->timemodified = $modts;
            //                                    $relatedData[$relObj->id] = array();
            //                                    $relatedData[$relObj->id]['timemodified'] = $relObj->timemodified;
            //                                    // for calculate average (now only for quizes): sum of max grades and sum of student results
            //                                    if ($modparam->modname == 'quiz') {
            //                                        $studentResult = $DB->get_field('quiz_grades', 'grade', array('quiz' => $modparam->modid, 'userid' => $student->id));
            //                                        if ($studentResult) {
            //                                            $studentResults += $studentResult;
            //                                        }
            //                                        $maxResult = $DB->get_field('quiz', 'grade', array('id' => $modparam->modid));
            //                                        if ($maxResult) {
            //                                            $maxResults += $maxResult;
            //                                        }
            //                                    }
            //                                }
            //                            }
            //                            $datatoDB = array();
            //                            $datatoDB['coursemoduleid'] = $cm->id;
            //                            $datatoDB['userid'] = $student->id;
            //                            $datatoDB['timemodified'] = $rootTs;
            //                            $datatoDB['relateddata'] = serialize($relatedData);
            //                            if (!$existing ||
            //                                    ($existing && unserialize($existing->relateddata) != unserialize($datatoDB['relateddata']))) {
            //                                // data was changed - save grading to competences!
            //                                if (block_exacomp_use_old_activities_method()) {
            //                                    block_exacomp_assign_competences($courseid, $student->id, $cm->topics, $cm->descriptors, null, true, $maxResults, $studentResults);
            //                                } else {
            //                                    block_exacomp_assign_competences($courseid, $student->id, null, null, $cm->examples, true, $maxResults, $studentResults);
            //                                }
            //                            } else {
            //                                // data was not changed. nothing to do
            //                            }
            //                            $DB->delete_records('block_exacompcmassign',
            //                                    ['coursemoduleid' => $cm->id, 'userid' => $student->id]);
            //                            $DB->insert_record('block_exacompcmassign', $datatoDB);
            //                        }
            //                    }
            //
            //                }
            //
            //            }
        }
    }

    return true;
}

function block_exacomp_assign_competences($courseid, $studentid, $topics, $descriptors, $examples, $userrealvalue = false, $maxGrade = null, $studentGradeResult = null, $admingrading = false) {
    if (isset($descriptors)) {
        $grading_scheme = block_exacomp_get_assessment_comp_scheme($courseid);
        foreach ($descriptors as $descriptor) {
            if (block_exacomp_additional_grading(BLOCK_EXACOMP_TYPE_DESCRIPTOR, $courseid)) {
                //block_exacomp_save_additional_grading_for_comp($courseid, $descriptor->compid, $studentid, \block_exacomp\global_config::get_value_additionalinfo_mapping($grading_scheme), $comptype = 0);
                block_exacomp_save_additional_grading_for_comp($courseid, $descriptor->compid, $studentid, block_exacomp_get_assessment_max_good_value($grading_scheme, $userrealvalue, $maxGrade, $studentGradeResult, $courseid),
                    BLOCK_EXACOMP_TYPE_DESCRIPTOR, -1, $admingrading);
            }
            //block_exacomp_set_user_competence($studentid, $descriptor->compid, 0, $courseid, BLOCK_EXACOMP_ROLE_TEACHER, $grading_scheme);
            block_exacomp_set_user_competence($studentid, $descriptor->compid, BLOCK_EXACOMP_TYPE_DESCRIPTOR, $courseid, BLOCK_EXACOMP_ROLE_TEACHER,
                block_exacomp_get_assessment_max_good_value($grading_scheme, $userrealvalue, $maxGrade, $studentGradeResult, $courseid), null, -1, true, [], $admingrading);
            //            mtrace("set competence ".$descriptor->compid." for user ".$studentid.'<br>');
        }
    }
    if (isset($topics)) {
        $grading_scheme = block_exacomp_get_assessment_topic_scheme($courseid);
        foreach ($topics as $topic) {
            if (block_exacomp_additional_grading(BLOCK_EXACOMP_TYPE_TOPIC, $courseid)) {
                //block_exacomp_save_additional_grading_for_comp($courseid, $topic->compid, $studentid, \block_exacomp\global_config::get_value_additionalinfo_mapping($grading_scheme), $comptype = 1);
                block_exacomp_save_additional_grading_for_comp($courseid, $topic->compid, $studentid, block_exacomp_get_assessment_max_good_value($grading_scheme, $userrealvalue, $maxGrade, $studentGradeResult, $courseid),
                    BLOCK_EXACOMP_TYPE_TOPIC, -1, $admingrading);
            }

            //block_exacomp_set_user_competence($studentid, $topic->compid, 1, $courseid, BLOCK_EXACOMP_ROLE_TEACHER, $grading_scheme);
            block_exacomp_set_user_competence($studentid, $topic->compid, BLOCK_EXACOMP_TYPE_TOPIC, $courseid, BLOCK_EXACOMP_ROLE_TEACHER,
                block_exacomp_get_assessment_max_good_value($grading_scheme, $userrealvalue, $maxGrade, $studentGradeResult, $courseid), null, -1, true, [], $admingrading);
            //            mtrace("set topic competence ".$topic->compid." for user ".$studentid.'<br>');

        }
    }
    if (isset($examples)) {
        $grading_scheme = block_exacomp_get_assessment_example_scheme($courseid);
        foreach ($examples as $example) {
            if (block_exacomp_additional_grading(BLOCK_EXACOMP_TYPE_EXAMPLE, $courseid)) {
                //block_exacomp_save_additional_grading_for_comp($courseid, $topic->compid, $studentid, \block_exacomp\global_config::get_value_additionalinfo_mapping($grading_scheme), $comptype = 1);
                block_exacomp_save_additional_grading_for_comp($courseid, $example->id, $studentid, block_exacomp_get_assessment_max_good_value($grading_scheme, $userrealvalue, $maxGrade, $studentGradeResult, $courseid),
                    BLOCK_EXACOMP_TYPE_EXAMPLE, -1, $admingrading);
            }

            //block_exacomp_set_user_competence($studentid, $topic->compid, 1, $courseid, BLOCK_EXACOMP_ROLE_TEACHER, $grading_scheme);
            block_exacomp_set_user_competence($studentid, $example->id, BLOCK_EXACOMP_TYPE_EXAMPLE, $courseid, BLOCK_EXACOMP_ROLE_TEACHER,
                block_exacomp_get_assessment_max_good_value($grading_scheme, $userrealvalue, $maxGrade, $studentGradeResult, $courseid), null, -1, true, [], $admingrading);

            block_exacomp_allow_resubmission($studentid, $example->id, $courseid); // 23.11.2021 this is a quickfix for allowing self grading of students if the example has been graded by auto_test

            //            mtrace("set example grading: ".$example->id." for user ".$studentid.'  '.block_exacomp_get_assessment_max_good_value($grading_scheme, $userrealvalue, $maxGrade, $studentGradeResult, $courseid).'<br>');
        }
    }
}

function block_exacomp_perform_question_grading() {
    global $DB;

    $question_array = array();
    $descquests = $DB->get_records("block_exacompdescrquest_mm");
    foreach ($descquests as $descquest) {
        if (!in_array($descquest->questid, $question_array)) {
            $question_array[] = $descquest->questid;
        }
    }

    $sql = "SELECT attempts.id, attempts.questionid, attempts.maxmark, step.fraction, step.userid, max(attempts.timemodified) as timemodified
            FROM {question_attempts} attempts
            JOIN {question_attempt_steps} AS step ON attempts.id=step.questionattemptid
            WHERE step.sequencenumber = 2
            GROUP BY questionid";

    $attempts = array_filter($DB->get_records_sql($sql), function($a) use ($question_array) {
        return in_array($a->questionid, $question_array);
    });

    foreach ($attempts as $attempt) {
        foreach ($descquests as $descquest) {
            if ($attempt->timemodified > $descquest->timemodified) {
                if ($attempt->questionid == $descquest->questid) {
                    if ($descquest->courseid != -1) {
                        $grading_scheme = block_exacomp_get_assessment_comp_scheme($descquest->courseid);

                        if (block_exacomp_additional_grading(BLOCK_EXACOMP_TYPE_DESCRIPTOR, $descquest->courseid)) {
                            block_exacomp_save_additional_grading_for_comp($descquest->courseid, $descquest->descrid, $attempt->userid,
                                block_exacomp_get_assessment_max_good_value($grading_scheme, true, $attempt->maxmark, $attempt->fraction, $descquest->courseid), $comptype = BLOCK_EXACOMP_TYPE_DESCRIPTOR);
                        }

                        block_exacomp_set_user_competence($attempt->userid, $descquest->descrid, BLOCK_EXACOMP_TYPE_DESCRIPTOR, $descquest->courseid, BLOCK_EXACOMP_ROLE_TEACHER,
                            block_exacomp_get_assessment_max_good_value($grading_scheme, true, $attempt->maxmark, $attempt->fraction, $descquest->courseid));
                        $descquest->timemodified = $attempt->timemodified;
                        $DB->update_record("block_exacompdescrquest_mm", $descquest);
                    }
                }
            }
        }
    }

}

function block_exacomp_get_gained_competences($course, $student, $subject = null, $crosssubj = null) {

    $gained_competencies_teacher = [];
    $gained_competencies_student = [];

    //TODO: this should be a bug, the descriptors of the WHOLE COURSE are used for a visualization of ONE SUBJECT... this cannot be right
    //    if($crosssubj){
    //        $descriptorOfCrosssubject = block_exacomp_get_descriptors_for_cross_subject($crosssubj->courseid,$crosssubj->id);
    //    }else{
    //        $dbLayer = \block_exacomp\db_layer_student::create($course->id, $student->id);
    //        $topics = $dbLayer->get_topics();
    //        $descriptors = $dbLayer->get_descriptor_parents();
    //    }

    if ($crosssubj) {
        $crosssubjcTree = block_exacomp_get_competence_tree_for_cross_subject($crosssubj->courseid, $crosssubj);
        $descriptors = array();
        $topics = $crosssubjcTree[$subject->id]->topics;
        foreach ($topics as $topic) {
            $descriptors = array_merge($descriptors, $topic->descriptors);
        }
        $courseid = $crosssubj->courseid;
    } else {
        $descriptors = block_exacomp_get_visible_descriptors_for_subject($course->id, $subject->id, $student->id);
        $topics = block_exacomp_get_topics_by_subject($course->id, $subject->id);
        $courseid = $course->id;
    }

    foreach ($descriptors as $descriptor) {
        if ($comp = block_exacomp_get_comp_eval_gained($courseid, BLOCK_EXACOMP_ROLE_TEACHER, $student->id, BLOCK_EXACOMP_TYPE_DESCRIPTOR, $descriptor->id)) {
            $gained_competencies_teacher[] = $comp;
        }
        if ($comp = block_exacomp_get_comp_eval_gained($courseid, BLOCK_EXACOMP_ROLE_STUDENT, $student->id, BLOCK_EXACOMP_TYPE_DESCRIPTOR, $descriptor->id)) {
            $gained_competencies_student[] = $comp;
        }
    }

    foreach ($topics as $topic) {
        if ($comp = block_exacomp_get_comp_eval_gained($courseid, BLOCK_EXACOMP_ROLE_TEACHER, $student->id, BLOCK_EXACOMP_TYPE_TOPIC, $topic->id)) {
            $gained_competencies_teacher[] = $comp;
        }
        if ($comp = block_exacomp_get_comp_eval_gained($courseid, BLOCK_EXACOMP_ROLE_STUDENT, $student->id, BLOCK_EXACOMP_TYPE_TOPIC, $topic->id)) {
            $gained_competencies_student[] = $comp;
        }
    }

    return [$gained_competencies_teacher, $gained_competencies_student, count($descriptors) + count($topics)];
}

/**
 *
 * check if there are already evaluations available
 *
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
 *
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
 *
 * @return block_exacomp\cross_subject[]
 */
function block_exacomp_get_cross_subjects_drafts() {
    return block_exacomp\cross_subject::get_objects(array('courseid' => 0));
}

/**
 *
 * save the given drafts to course
 *
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
 *
 * @param unknown $courseid
 * @param unknown $title
 * @param unknown $description
 * @param unknown $creatorid
 * @param number $subjectid
 */
function block_exacomp_create_crosssub($courseid, $title, $description, $creatorid, $subjectid = 0, $groupcategory = "") {
    global $DB;

    $insert = new stdClass();
    $insert->title = $title;
    $insert->description = $description;
    $insert->courseid = $courseid;
    $insert->creatorid = $creatorid;
    $insert->subjectid = $subjectid;
    $insert->sourceid = 0;
    $insert->source = BLOCK_EXACOMP_IMPORT_SOURCE_SPECIFIC;
    $insert->groupcategory = $groupcategory;

    return $DB->insert_record(BLOCK_EXACOMP_DB_CROSSSUBJECTS, $insert);
}

/**
 * update title, description or subjectid of crosssubject
 *
 * @param unknown $crosssubjid
 * @param unknown $title
 * @param unknown $description
 * @param unknown $subjectid
 */
function block_exacomp_edit_crosssub($crosssubjid, $title, $description, $subjectid, $groupcategory = "") {
    global $DB;

    $crosssubj = $DB->get_record(BLOCK_EXACOMP_DB_CROSSSUBJECTS, array('id' => $crosssubjid));
    $crosssubj->title = $title;
    $crosssubj->description = $description;
    $crosssubj->subjectid = $subjectid;
    $crosssubj->groupcategory = $groupcategory;

    return $DB->update_record(BLOCK_EXACOMP_DB_CROSSSUBJECTS, $crosssubj);
}

/**
 * remove given crosssubject
 *
 * @param unknown $crosssubjid
 */
function block_exacomp_delete_crosssub($crosssubjid) {
    global $DB;
    //delete examples that were created specifically only for this cross_subject
    block_exacomp_delete_examples_for_crosssubject($crosssubjid);

    // TODO: pruefen ob mein crosssubj?

    //delete student-crosssubject association
    $DB->delete_records(BLOCK_EXACOMP_DB_CROSSSTUD, array('crosssubjid' => $crosssubjid));

    //delete descriptor-crosssubject association
    $DB->delete_records(BLOCK_EXACOMP_DB_DESCCROSS, array('crosssubjid' => $crosssubjid));

    //delete crosssubject overall evaluations
    $DB->delete_records(BLOCK_EXACOMP_DB_COMPETENCES, array('compid' => $crosssubjid, 'comptype' => BLOCK_EXACOMP_TYPE_CROSSSUB));

    //delete crosssubject
    $DB->delete_records(BLOCK_EXACOMP_DB_CROSSSUBJECTS, array('id' => $crosssubjid));
}

/**
 * delete drafts for all users in all courses
 *
 * @param unknown $drafts_to_delete
 */
function block_exacomp_delete_crosssubject_drafts($drafts_to_delete) {
    global $DB;
    foreach ($drafts_to_delete as $draftid) {
        $DB->delete_records(BLOCK_EXACOMP_DB_DESCCROSS, array('crosssubjid' => $draftid));
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
 * returns the crosssubject with this id
 */
function block_exacomp_get_crosssubject_by_id($id) {
    return g::$DB->get_record(BLOCK_EXACOMP_DB_CROSSSUBJECTS, array('id' => $id));
}

/**
 * @param $courseid if = 0 - get all crossubjects
 * @param null $studentid
 * @return block_exacomp\cross_subject[]
 */
function block_exacomp_get_cross_subjects_by_course($courseid, $studentid = null) {
    if ($courseid > 0) {
        $crosssubs = block_exacomp\cross_subject::get_objects(['courseid' => $courseid], 'title');
    } else {
        $allCrosssubs = block_exacomp\cross_subject::get_objects(null, 'title'); // from all courses
        // filter by 'only my' (for courses where I am a teacher)
        $crosssubs = [];
        foreach ($allCrosssubs as $crosssub) {
            if (block_exacomp_is_teacher($crosssub->courseid)) {
                $crosssubs[] = $crosssub;
            }
        }
    }

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
 * @param null $userid
 * @return block_exacomp\cross_subject[]
 */
function block_exacomp_get_crossubjects_by_teacher($userid = null) {
    global $USER;
    if (!$userid) {
        $userid = $USER->id;
    }
    $allCrosssubs = block_exacomp\cross_subject::get_objects(null, 'title'); // get crossubjects from all courses
    // filter by 'user is a teacher'
    $crosssubs = [];
    foreach ($allCrosssubs as $crosssub) {
        if ($crosssub->courseid > 0) {
            if (block_exacomp_is_teacher($crosssub->courseid, $userid)) {
                $crosssubs[] = $crosssub;
            }
        }
    }

    return $crosssubs;
}

/**
 * check crosssubject student association
 *
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

    $allDescriptors = block_exacomp_get_descriptors_for_cross_subject($courseid, $cross_subject);

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
		FROM {" . BLOCK_EXACOMP_DB_DESCRIPTORS . "} d
		JOIN {" . BLOCK_EXACOMP_DB_DESCCROSS . "} dc ON d.id = dc.descrid
		WHERE dc.crosssubjid = ?
	", array($crosssubjid));
}

//function block_exacomp_get_topics_assigned_to_cross_subject($crosssubjid) {
//    $descriptors = block_exacomp_get_descriptors_assigned_to_cross_subject($crosssubjid);
//    $topics = array();
//    foreach ($descriptors as $descriptor){
//        if($descriptor->parentid == 0){
//            $topics[] = $descriptor;
//        }
//    }
//
//    foreach ($allDescriptors as $descriptor) {
//        // get descriptor topic
//        if (empty($allTopics[$descriptor->topicid])) {
//            continue;
//        }
//        $topic = $topics[$descriptor->topicid] = $allTopics[$descriptor->topicid];
//        $topic->descriptors[$descriptor->id] = $descriptor;
//    }
//
//    $topics = block_exacomp_get_all_topics();
//
//
//    return $topics;
//}

/**
 * @param $topics
 * @param $courseid
 * @param $crosssubj
 * @return mixed
 * removes all topics that are not used in this crosssubject
 * this is not perfect runtime-wise but there are so many places on where to get topics, that this way is easier
 */
function block_exacomp_clear_topics_for_crosssubject($topics, $courseid, $crosssubj) {
    $crosssubjid = is_scalar($crosssubj) ? $crosssubj : $crosssubj->id;

    $descriptors = block_exacomp_get_descriptors_for_cross_subject($courseid, $crosssubj);
    $topicsOfCrosssubj = array();
    foreach ($descriptors as $descriptor) {
        $topicsOfCrosssubj[$descriptor->topicid] = $descriptor->topicid;
    }

    if (is_array($topics)) {
        foreach ($topics as $key => $topic) {
            if (isset($topicsOfCrosssubj[$topic->id])) {
                //ok
            } else {
                unset($topics[$key]);
            }
        }
    }
    return $topics;
}

/**
 * get descriptors for crosssubject
 *
 * @param unknown $courseid
 * @param unknown $crosssubjid
 * @param string $showalldescriptors
 * @return unknown
 */
function block_exacomp_get_descriptors_for_cross_subject($courseid, $cross_subject, $onlyAssignedChildren = false) {
    global $DB;

    $crosssubjid = is_scalar($cross_subject) ? $cross_subject : $cross_subject->id;

    $assignedDescriptors = block_exacomp_get_descriptors_assigned_to_cross_subject($crosssubjid);

    //return only the assigned children, needed for statistics... otherwise return all assigned parents and parents of assigned children
    if ($onlyAssignedChildren) {
        foreach ($assignedDescriptors as $key => $assignedDescriptor) {
            if ($assignedDescriptor->parentid == 0) {
                unset($assignedDescriptors[$key]);
            }
        }
        return $assignedDescriptors;
    }

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

    $sql = 'SELECT DISTINCT desctopmm.id as u_id, d.id as id, d.source, d.title, d.niveauid, t.id AS topicid, d.profoundness, d.sorting, d.parentid, n.sorting as niveau '
        . 'FROM {' . BLOCK_EXACOMP_DB_TOPICS . '} t '
        . 'JOIN {' . BLOCK_EXACOMP_DB_DESCTOPICS . '} desctopmm ON desctopmm.topicid=t.id '
        . 'JOIN {' . BLOCK_EXACOMP_DB_DESCRIPTORS . '} d ON desctopmm.descrid=d.id AND d.parentid = 0 '
        . 'LEFT JOIN {' . BLOCK_EXACOMP_DB_NIVEAUS . '} n ON n.id = d.niveauid '
        . 'WHERE d.id IN(' . join(',', $searchDescriptorIds) . ')';

    $descriptors = \block_exacomp\descriptor::get_objects_sql($sql);

    foreach ($descriptors as $descriptor) {
        if (isset($assignedDescriptors[$descriptor->id])) {
            // assigned, ok
        } else {
            // not assigned = nicht direkt ausgewÃ¤hlt => children checken
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
		FROM {" . BLOCK_EXACOMP_DB_SUBJECTS . "} s
		WHERE id IN (
			SELECT t.subjid
			FROM {" . BLOCK_EXACOMP_DB_TOPICS . "} t
			JOIN {" . BLOCK_EXACOMP_DB_DESCTOPICS . "} dt ON t.id = dt.topicid
			JOIN {" . BLOCK_EXACOMP_DB_DESCRIPTORS . "} d ON dt.descrid=d.id
			JOIN {" . BLOCK_EXACOMP_DB_DESCCROSS . "} dc ON d.id = dc.descrid
			WHERE dc.crosssubjid = ?
		);
	", [$crosssubjid]);
}

/**
 * associate descriptors with crosssubjects
 *
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
 *
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
                //$DB->delete_records(BLOCK_EXACOMP_DB_DESCVISIBILITY, array('descrid' => $descrid, 'courseid' => $cross_courseid, 'studentid' => 0));
            }
        }
    }
}

/**
 * change descriptor visibility, studentid = 0 : visibility settings for all students
 *
 * @param unknown $descrid
 * @param unknown $courseid
 * @param unknown $visible
 * @param unknown $studentid
 */
function block_exacomp_set_descriptor_visibility($descrid, $courseid, $visible, $studentid) {
    global $DB;
    if ($studentid == BLOCK_EXACOMP_SHOW_ALL_STUDENTS || $studentid == 0) {//if visibility changed for all: delete individual settings
        $studentid = 0;
        $sql = "DELETE FROM {" . BLOCK_EXACOMP_DB_DESCVISIBILITY . "} WHERE descrid = ? AND courseid = ? and studentid <> 0";

        $DB->execute($sql, array($descrid, $courseid));
    }

    g::$DB->insert_or_update_record(BLOCK_EXACOMP_DB_DESCVISIBILITY,
        ['visible' => $visible],
        ['descrid' => $descrid, 'courseid' => $courseid, 'studentid' => $studentid]
    );
}

/**
 * change example visibility, studentid = 0: visibility settings for all students
 *
 * @param unknown $exampleid
 * @param unknown $courseid
 * @param unknown $visible
 * @param unknown $studentid
 */
function block_exacomp_set_example_visibility($exampleid, $courseid, $visible, $studentid) {
    if ($studentid == BLOCK_EXACOMP_SHOW_ALL_STUDENTS || $studentid == 0) {//if visibility changed for all: delete individual settings
        $studentid = 0;
        $sql = "DELETE FROM {" . BLOCK_EXACOMP_DB_EXAMPVISIBILITY . "} WHERE exampleid = ? AND courseid = ? and studentid <> 0";
        g::$DB->execute($sql, array($exampleid, $courseid));
    }

    g::$DB->insert_or_update_record(BLOCK_EXACOMP_DB_EXAMPVISIBILITY,
        ['visible' => $visible],
        ['exampleid' => $exampleid, 'courseid' => $courseid, 'studentid' => $studentid]
    );
}

/**
 * change example solution visibility, studentid = 0: visibility settings for all students
 *
 * @param unknown $exampleid
 * @param unknown $courseid
 * @param unknown $visible
 * @param unknown $studentid
 */
function block_exacomp_set_example_solution_visibility($exampleid, $courseid, $visible, $studentid) {
    if ($studentid == BLOCK_EXACOMP_SHOW_ALL_STUDENTS || $studentid == 0) {//if visibility changed for all: delete individual settings
        $studentid = 0;
        $sql = "DELETE FROM {" . BLOCK_EXACOMP_DB_SOLUTIONVISIBILITY . "} WHERE exampleid = ? AND courseid = ? and studentid <> 0";
        g::$DB->execute($sql, array($exampleid, $courseid));
    }

    g::$DB->insert_or_update_record(BLOCK_EXACOMP_DB_SOLUTIONVISIBILITY,
        ['visible' => $visible],
        ['exampleid' => $exampleid, 'courseid' => $courseid, 'studentid' => $studentid]
    );
}

/**
 * change topic visibility settings, studentid = 0: visibility settings for all students
 *
 * @param unknown $topicid
 * @param unknown $courseid
 * @param unknown $visible
 * @param unknown $studentid
 */
function block_exacomp_set_topic_visibility($topicid, $courseid, $visible, $studentid) {
    global $DB;
    if ($studentid == BLOCK_EXACOMP_SHOW_ALL_STUDENTS || $studentid == 0) {//if visibility changed for all: delete individual settings
        $studentid = 0;
        $sql = "DELETE FROM {" . BLOCK_EXACOMP_DB_TOPICVISIBILITY . "} WHERE topicid = ? AND courseid = ? and studentid <> 0 AND niveauid IS NULL";

        $DB->execute($sql, array($topicid, $courseid));
    }
    g::$DB->insert_or_update_record(BLOCK_EXACOMP_DB_TOPICVISIBILITY,
        ['visible' => $visible],
        ['topicid' => $topicid, 'courseid' => $courseid, 'studentid' => $studentid, 'niveauid' => null]
    );
}

/**
 * change topic visibility settings, studentid = 0: visibility settings for all students
 *
 * @param unknown $topicid
 * @param unknown $courseid
 * @param unknown $visible
 * @param unknown $studentid
 * @param unknown $niveauid
 *
 */
function block_exacomp_set_niveau_visibility($topicid, $courseid, $visible, $studentid, $niveauid) {
    global $DB;
    if ($studentid == BLOCK_EXACOMP_SHOW_ALL_STUDENTS || $studentid == 0) {//if visibility changed for all: delete individual settings
        $studentid = 0;
        $sql = "DELETE FROM {" . BLOCK_EXACOMP_DB_TOPICVISIBILITY . "} WHERE topicid = ? AND courseid = ? and studentid <> 0 AND niveauid = ?";

        $DB->execute($sql, array($topicid, $courseid, $niveauid));
    }
    g::$DB->insert_or_update_record(BLOCK_EXACOMP_DB_TOPICVISIBILITY,
        ['visible' => $visible],
        ['topicid' => $topicid, 'courseid' => $courseid, 'studentid' => $studentid, 'niveauid' => $niveauid]
    );
}

/**
 * check if topic or any underlying (descriptor, example) is used
 * used for topic: student or teacher evaluation exists
 *
 * @param unknown $courseid
 * @param unknown $topic
 * @param unknown $studentid
 * @return boolean
 */
function block_exacomp_is_topic_used($courseid, $topic, $studentid) {
    global $DB, $block_exacomp_topic_used_values;

    if (isset($block_exacomp_topic_used_values[$courseid][$studentid][$topic->id])) {
        return $block_exacomp_topic_used_values[$courseid][$studentid][$topic->id];
    }

    if ($studentid == 0) {
        $sql = "SELECT * FROM {" . BLOCK_EXACOMP_DB_COMPETENCES . "} WHERE courseid = ? AND compid = ? AND comptype=? AND ( value>=0 OR additionalinfo IS NOT NULL)";
        $records = $DB->get_records_sql($sql, array($courseid, $topic->id, BLOCK_EXACOMP_TYPE_TOPIC));
        if ($records) {
            $block_exacomp_topic_used_values[$courseid][$studentid][$topic->id] = true;
            return true;
        }
    } else {
        $sql = "SELECT * FROM {" . BLOCK_EXACOMP_DB_COMPETENCES . "} WHERE courseid = ? AND compid = ? AND comptype=? AND userid = ? AND ( value>=0 OR additionalinfo IS NOT NULL)";
        $records = $DB->get_records_sql($sql, array($courseid, $topic->id, BLOCK_EXACOMP_TYPE_TOPIC, $studentid));
        if ($records) {
            $block_exacomp_topic_used_values[$courseid][$studentid][$topic->id] = true;
            return true;
        }
    }

    $descriptors = block_exacomp_get_descriptors_by_topic($courseid, $topic->id);
    foreach ($descriptors as $descriptor) {
        $descriptor->children = block_exacomp_get_child_descriptors($descriptor, $courseid);
        if (block_exacomp_descriptor_used($courseid, $descriptor, $studentid)) {
            $block_exacomp_topic_used_values[$courseid][$studentid][$topic->id] = true;
            return true;
        }
    }

    $block_exacomp_topic_used_values[$courseid][$studentid][$topic->id] = false;
    return false;
}

/**
 * check if descriptor or underlying object (childdescriptor, example) is used
 * descriptor used: teacher or student evaluation exists
 *
 * @param unknown $courseid
 * @param unknown $descriptor
 * @param unknown $studentid
 * @return boolean
 */
function block_exacomp_descriptor_used($courseid, $descriptor, $studentid) {
    global $DB, $block_exacomp_descriptor_used_values;
    //if studentid == 0 used = true, if no evaluation (teacher OR student) for this descriptor for any student in this course
    //								 if no evaluation/submission for the examples of this descriptor

    //if studentid != 0 used = true, if any assignment (teacher OR student) for this descriptor for THIS student in this course
    //								 if no evaluation/submission for the examples of this descriptor

    if (isset($block_exacomp_descriptor_used_values[$courseid][$studentid][$descriptor->id])) {
        return $block_exacomp_descriptor_used_values[$courseid][$studentid][$descriptor->id];
    }

    if ($studentid == 0) {
        $sql = "SELECT * FROM {" . BLOCK_EXACOMP_DB_COMPETENCES . "} WHERE courseid = ? AND compid = ? AND comptype=? AND ( value>=0 OR additionalinfo IS NOT NULL)";
        $records = $DB->get_records_sql($sql, array($courseid, $descriptor->id, BLOCK_EXACOMP_TYPE_DESCRIPTOR));
        if ($records) {
            $block_exacomp_descriptor_used_values[$courseid][$studentid][$descriptor->id] = true;
            return true;
        }
    } else {
        $sql = "SELECT * FROM {" . BLOCK_EXACOMP_DB_COMPETENCES . "} WHERE courseid = ? AND compid = ? AND comptype=? AND userid = ? AND ( value>=0 OR additionalinfo IS NOT NULL)";
        $records = $DB->get_records_sql($sql, array($courseid, $descriptor->id, BLOCK_EXACOMP_TYPE_DESCRIPTOR, $studentid));
        if ($records) {
            $block_exacomp_descriptor_used_values[$courseid][$studentid][$descriptor->id] = true;
            return true;
        }
    }

    if (isset($descriptor->children)) {
        //check child used
        foreach ($descriptor->children as $child) {
            if (block_exacomp_descriptor_used($courseid, $child, $studentid)) {
                $block_exacomp_descriptor_used_values[$courseid][$studentid][$descriptor->id] = true;
                return true;
            }
        }
    }

    if (!isset($descriptor->examples)) {
        $descriptor = block_exacomp_get_examples_for_descriptor($descriptor);
    }

    if ($descriptor->examples) {
        foreach ($descriptor->examples as $example) {
            if (block_exacomp_example_used($courseid, $example, $studentid)) {
                $block_exacomp_descriptor_used_values[$courseid][$studentid][$descriptor->id] = true;
                return true;
            }
        }
    }

    $block_exacomp_descriptor_used_values[$courseid][$studentid][$descriptor->id] = false;
    return false;
}

/**
 * check if example is used
 * example used: student or teacherevaluation exists, submission exists, example on weekly schedule or
 * on pre-planning storage,
 *
 * @param unknown $courseid
 * @param unknown $example
 * @param unknown $studentid
 * @return boolean
 */
function block_exacomp_example_used($courseid, $example, $studentid) {
    global $DB, $block_exacomp_example_used_values;
    //if studentid == 0 used = true, if no evaluation/submission for this example
    //if studentid != 0 used = true, if no evaluation/submission for this examples for this student

    if (isset($block_exacomp_example_used_values[$courseid][$studentid][$example->id])) {
        return $block_exacomp_example_used_values[$courseid][$studentid][$example->id];
    }

    if ($studentid <= 0) { // any self or teacher evaluation
        $sql = "SELECT * FROM {" . BLOCK_EXACOMP_DB_EXAMPLEEVAL . "} WHERE courseid = ? AND exampleid = ? AND teacher_evaluation>= 0";
        $records = $DB->get_records_sql($sql, array($courseid, $example->id));
        if ($records) {
            $block_exacomp_example_used_values[$courseid][$studentid][$example->id] = true;
            return true;
        }

        $sql = "SELECT * FROM {" . BLOCK_EXACOMP_DB_EXAMPLEEVAL . "} WHERE courseid = ? AND exampleid = ? AND student_evaluation>= 0";
        $records = $DB->get_records_sql($sql, array($courseid, $example->id));
        if ($records) {
            $block_exacomp_example_used_values[$courseid][$studentid][$example->id] = true;
            return true;
        }

        //on any weekly schedule? -> yes: used
        $onSchedule = $DB->record_exists(BLOCK_EXACOMP_DB_SCHEDULE, array('courseid' => $courseid, 'exampleid' => $example->id));
        if ($onSchedule) {
            $block_exacomp_example_used_values[$courseid][$studentid][$example->id] = true;
            return true;
        }

        //any submission made?
        if (block_exacomp_exaportexists()) {
            $sql = "SELECT * FROM {" . BLOCK_EXACOMP_DB_ITEM_MM . "} ie JOIN {" . 'block_exaportitem' . "} i ON ie.itemid = i.id " .
                "WHERE ie.exacomp_record_id = ? AND i.courseid = ?";
            $records = $DB->get_records_sql($sql, array($example->id, $courseid));
            if ($records) {
                $block_exacomp_example_used_values[$courseid][$studentid][$example->id] = true;
                return true;
            }
        }
    } else { // any self or teacher evaluation for this student
        $sql = "SELECT * FROM {" . BLOCK_EXACOMP_DB_EXAMPLEEVAL . "} WHERE courseid = ? AND exampleid = ? AND studentid=? AND teacher_evaluation>=0";
        $records = $DB->get_records_sql($sql, array($courseid, $example->id, $studentid));
        if ($records) {
            $block_exacomp_example_used_values[$courseid][$studentid][$example->id] = true;
            return true;
        }

        $sql = "SELECT * FROM {" . BLOCK_EXACOMP_DB_EXAMPLEEVAL . "} WHERE courseid = ? AND exampleid = ? AND studentid = ? AND student_evaluation>=0";
        $records = $DB->get_records_sql($sql, array($courseid, $example->id, $studentid));
        if ($records) {
            $block_exacomp_example_used_values[$courseid][$studentid][$example->id] = true;
            return true;
        }

        //on students weekly schedule? -> yes: used
        $onSchedule = $DB->record_exists(BLOCK_EXACOMP_DB_SCHEDULE, array('studentid' => $studentid, 'courseid' => $courseid, 'exampleid' => $example->id));
        if ($onSchedule) {
            $block_exacomp_example_used_values[$courseid][$studentid][$example->id] = true;
            return true;
        }

        //or on pre planning storage
        $onSchedule = $DB->record_exists(BLOCK_EXACOMP_DB_SCHEDULE, array('studentid' => 0, 'courseid' => $courseid, 'exampleid' => $example->id));
        if ($onSchedule) {
            $block_exacomp_example_used_values[$courseid][$studentid][$example->id] = true;
            return true;
        }

        //submission made?
        if (block_exacomp_exaportexists()) {
            $sql = "SELECT * FROM {" . BLOCK_EXACOMP_DB_ITEM_MM . "} ie JOIN {" . 'block_exaportitem' . "} i ON ie.itemid = i.id " .
                "WHERE ie.exacomp_record_id = ? AND i.userid = ? AND i.courseid = ?";
            $records = $DB->get_records_sql($sql, array($example->id, $studentid, $courseid));
            if ($records) {
                $block_exacomp_example_used_values[$courseid][$studentid][$example->id] = true;
                return true;
            }
        }
    }

    $block_exacomp_example_used_values[$courseid][$studentid][$example->id] = false;
    return false;
}

/**
 * get all students associated with a crosssubject
 *
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
 *
 * @param $studentid
 * @param $viewerid
 * @param $exampleid
 * @return null|string
 */
function block_exacomp_get_viewurl_for_example($studentid, $viewerid, $exampleid, $courseid = null) {
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
        $access = "portfolio/id/" . $studentid . "&itemid=" . $item->id;
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

        $access = "view/id/" . $studentid . "-" . $view->viewid . "&itemid=" . $item->id;
    }

    return $CFG->wwwroot . '/blocks/exaport/shared_item.php?access=' . $access . '&exampleid=' . $exampleid . '&courseid=' . $courseid;
}

function block_exacomp_get_access_for_shared_view_for_item($item, $viewerid) {
    global $DB;

    $studentid = $item->userid;

    if ($studentid == $viewerid) {
        // view my own item
        $access = "portfolio/id/" . $studentid;
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

        $access = "view/id/" . $studentid . "-" . $view->viewid;
    }

    return $access;
}

/**
 * get the url to enter the competence overview example belongs to
 *
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

    return $CFG->wwwroot . '/blocks/exacomp/assign_competencies.php?courseid=' . $courseid . '&studentid=' . $studentid . '&subjectid=' . $topic->subjid . '&topicid=' . $topic->id . '&exampleid=' . $exampleid;
}

/**
 * function for testing import of ics to weekly_schedule
 */
function block_exacomp_import_ics_to_weekly_schedule($courseid, $studentid, $link, $creatorid, $filecontent) {
    $timeslots = block_exacomp_build_json_time_slots($date = null);
    $units = (get_config("exacomp", "scheduleunits")) ? get_config("exacomp", "scheduleunits") : 8;
    $interval = (get_config("exacomp", "scheduleinterval")) ? get_config("exacomp", "scheduleinterval") : 50;
    $time = (get_config("exacomp", "schedulebegin")) ? get_config("exacomp", "schedulebegin") : "07:45";

    //convert the timeslots to timestamps so i can compare them usefully
    $timeslottimestamps = array();
    foreach ($timeslots as $key => $timeslot) {
        $timeslottimestamps[] = DateTime::createFromFormat('H:i', $timeslot['start'])->getTimestamp();
        $timeslottimestamps[] = DateTime::createFromFormat('H:i', $timeslot['end'])->getTimestamp();
    }

    require __DIR__ . '/../lib/calFileParser/CalFileParser.php';
    $cal = new CalFileParser();
    $cal->set_timezone('Europe/Berlin');
    $icsData = $cal->parse($link, null, $filecontent);
    $start = $icsData[3]['DTSTART'];
    $end = $icsData[3]['DTEND'];

    $now = new DateTime();
    foreach ($icsData as $event) {
        //skip all events that happened before now:
        if ($event['DTSTART']->getTimestamp() < $now->getTimestamp()) {
            continue;
        }

        //Idea: map the time to the closest timeslot:
        //Get the hours and minutes of the timeslots, create date from it, do the same for timestamp
        //Compare the timestamps and find closest
        //update the timestamp with the closes hours and minutes
        $eventStartStamp = DateTime::createFromFormat('H:i', $event['DTSTART']->format('H:i'))->getTimestamp();
        $eventEndStamp = DateTime::createFromFormat('H:i', $event['DTEND']->format('H:i'))->getTimestamp();

        //START
        $smallestDifference = 999999999999;
        $keyOfBestFit = -1;
        foreach ($timeslottimestamps as $key => $timeslottimestamp) {
            $currdiff = abs($timeslottimestamp - $eventStartStamp);
            if ($currdiff == 0) {
                //nothing to do, since it fits a timestamp perfectly
                break;
            }
            if ($currdiff < $smallestDifference) {
                $keyOfBestFit = $key;
                $smallestDifference = $currdiff;
            }
        }

        if ($keyOfBestFit != -1) {
            //set the time of the event to the nearest hours and mins that are available from the timestamps
            //for this, I have to create a date from the dimestamp
            $hour = date('H', $timeslottimestamps[$keyOfBestFit]);
            $minute = date('i', $timeslottimestamps[$keyOfBestFit]);
            $event['DTSTART']->setTime($hour, $minute);
            //now i have to correct the date
        }

        //END
        $smallestDifference = 999999999999;
        $keyOfBestFit = -1;
        foreach ($timeslottimestamps as $key => $timeslottimestamp) {
            $currdiff = abs($timeslottimestamp - $eventEndStamp);
            if ($currdiff == 0) {
                //nothing to do, since it fits a timestamp perfectly
                break;
            }
            if ($currdiff < $smallestDifference) {
                $keyOfBestFit = $key;
                $smallestDifference = $currdiff;
            }
        }

        if ($keyOfBestFit != -1) {
            //set the time of the event to the nearest hours and mins that are available from the timestamps
            //for this, I have to create a date from the dimestamp
            $hour = date('H', $timeslottimestamps[$keyOfBestFit]);
            $minute = date('i', $timeslottimestamps[$keyOfBestFit]);

            //update the time, to a valid timeslot time
            $event['DTEND']->setTime($hour, $minute);
        }

        $timeStart = $event['DTSTART']->getTimestamp();
        $timeEnd = $event['DTEND']->getTimestamp();

        $blockingEventId = block_exacomp_create_background_event($courseid, $event["SUMMARY"], $creatorid, $studentid);
        block_exacomp_add_example_to_schedule($studentid, $blockingEventId, $creatorid, $courseid, $timeStart, $timeEnd, -1, -1, null, true);
    }
    return true;
}

/**
 * delete all ics_imports of this creator for this student in this course
 */
function block_exacomp_delete_imports_of_weekly_schedule($courseid, $studentid, $creatorid) {
    global $DB;
    // this JOIN is not working, why?
    //    DELETE FROM `mdl_block_exacompschedule` s
    //JOIN 'mdl_block_exacompexamples' e on e.id = s.exampleid AND e.blocking_event=3
    //solution without join:
    //    DELETE s FROM `mdl_block_exacompschedule` s, `mdl_block_exacompexamples` e
    //WHERE s.exampleid = e.id AND e.blocking_event = 3
    //    AND s.courseid = AND studentid = AND creatorid =

    //        $sql = "DELETE s,e,vis
    //        FROM {block_exacompschedule} s, {block_exacompexamples} e, {".BLOCK_EXACOMP_DB_EXAMPVISIBILITY."} vis
    //        WHERE e.id = s.exampleid AND s.exampleid = vis.exampleid AND s.courseid = vis.courseid
    //        AND s.studentid = ? AND s.courseid = ? AND s.creatorid = ?
    //        AND e.blocking_event = 3";

    $sql = "DELETE s
        FROM {block_exacompschedule} s, {block_exacompexamples} e
        WHERE e.id = s.exampleid
        AND s.studentid = ? AND s.courseid = ? AND s.creatorid = ?
        AND e.blocking_event = 3";

    $DB->execute($sql, array($studentid, $courseid, $creatorid));

    //Delete examples and visibility if they are not used in any place AND ARE ICS_IMPROTS of course
    //if a teacher imports into their own schedule, and then distributes and then deletes from ONE student --> the example still exists in other schedules
    //in every other case: the example is lost and should therefore be deleted
    //this query in word: delete every example and visibilityentry of the example if that example is an ics_imports AND never referenced in ANY schedule
    $sql = "DELETE e,v
        FROM {block_exacompexamples} e, {" . BLOCK_EXACOMP_DB_EXAMPVISIBILITY . "} v
        WHERE e.id = v.exampleid
        AND e.blocking_event = 3
        AND e.id NOT IN (
            SELECT s.exampleid FROM {block_exacompschedule} s
            WHERE s.courseid = ? AND s.creatorid = ?)";

    $DB->execute($sql, array($courseid, $creatorid));

}

/**
 * add example to students schedule, if start and end not set, example is added to planning storage
 *
 * @param unknown $studentid
 * @param unknown $exampleid
 * @param unknown $creatorid
 * @param unknown $courseid
 * @param unknown $start
 * @param unknown $end
 * @param int $ethema_ismain
 * @param int $ethema_issubcategory
 * @param char $source 'S' for student, 'T' for teacher individually, 'C' for central.. if teacher assigns many at one time
 * @return boolean
 */
function block_exacomp_add_example_to_schedule($studentid, $exampleid, $creatorid, $courseid, $start = null, $end = null, $ethema_ismain = -1, $ethema_issubcategory = -1, $source = null, $icsBackgroundEvent = false, $distributionid = null,
    $customdata = null) {
    global $USER, $DB;

    $timecreated = $timemodified = time();

    $returnvalue = true; // most of the time returning "true" is good enough, sometimes this will be overwritten by the scheduleid

    // prüfen, ob element schon zur gleichen zeit im wochenplan ist
    if ($studentid == 0) { // 2 teachers of same course should be able to add example in there planing scheduler, so creatorid have to be asked here. for student below this is not necessary
        if ($DB->get_record(BLOCK_EXACOMP_DB_SCHEDULE, array('studentid' => $studentid, 'creatorid' => $creatorid, 'exampleid' => $exampleid, 'courseid' => $courseid, 'start' => $start))) {
            return true;
        }
    } else {
        $scheduleentry = $DB->get_record(BLOCK_EXACOMP_DB_SCHEDULE, array('studentid' => $studentid, 'exampleid' => $exampleid, 'courseid' => $courseid, 'start' => $start));
        if ($scheduleentry) {
            return $scheduleentry->id;
        }
    }
    // if not given by the function call, find out the ethema parameter:
    if ($ethema_ismain == -1 && $ethema_issubcategory == -1) {
        $example = $DB->get_record(BLOCK_EXACOMP_DB_EXAMPLES, array('id' => $exampleid));
        $ethema_ismain = isset($example->ethema_ismain) ? $example->ethema_ismain : 0;
        $ethema_issubcategory = isset($example->ethema_issubcategory) ? $example->ethema_issubcategory : 0;
    }

    // check if it is an eThema parent... either main or subcategory
    if ($ethema_ismain) {
        $subcategoryexamples = block_exacomp_get_eThema_children($exampleid);
        foreach ($subcategoryexamples as $example) {
            if ($example->ethema_issubcategory) {
                block_exacomp_add_example_to_schedule($studentid, $example->id, $creatorid, $courseid, null, null, 0, 1, $source, false, $distributionid, $customdata);
            } else {
                block_exacomp_add_example_to_schedule($studentid, $example->id, $creatorid, $courseid, null, null, 0, 0, $source, false, $distributionid, $customdata);
            }
        }
    } else if ($ethema_issubcategory) {
        $childexamples = block_exacomp_get_eThema_children($exampleid);
        foreach ($childexamples as $example) {
            block_exacomp_add_example_to_schedule($studentid, $example->id, $creatorid, $courseid, null, null, 0, 0, $source, false, $distributionid, $customdata);
        }
    } else {
        $returnvalue = $DB->insert_record(BLOCK_EXACOMP_DB_SCHEDULE, array(
            'studentid' => $studentid,
            'exampleid' => $exampleid,
            'courseid' => $courseid,
            'creatorid' => $creatorid,
            'lastmodifiedbyid' => $creatorid,
            'addedtoschedulebyid' => $start ? $creatorid : 0,
            'timecreated' => $timecreated,
            'timemodified' => $timemodified,
            'start' => $start,
            'endtime' => $end,
            'deleted' => 0,
            'ethema_ismain' => $ethema_ismain,
            'ethema_issubcategory' => $ethema_issubcategory,
            'source' => $source,
            'distributionid' => $distributionid));

        //only send a notification if a teacher adds an example for a student and not for pre planning storage
        //also, don't send notifications for ics_imports
        if (!$icsBackgroundEvent) {
            if ($creatorid != $studentid && $studentid > 0) {
                block_exacomp_send_weekly_schedule_notification($USER, $DB->get_record('user', array('id' => $studentid)), $courseid, $exampleid, $customdata);
            }
        }
        \block_exacomp\event\example_added::log(['objectid' => $exampleid, 'courseid' => $courseid, 'relateduserid' => $studentid]);
    }
    return $returnvalue;
}

/**
 * get all subcategory examples and childexamples of this main or subcategory example
 *
 * @param exampleid
 * @return examples
 */
function block_exacomp_get_eThema_children($exampleid) {
    global $DB;
    return $DB->get_records(BLOCK_EXACOMP_DB_EXAMPLES, array('ethema_parent' => $exampleid));
}

/**
 * add example to all planning storages for all students in course
 *
 * @param unknown $courseid
 * @return boolean
 */
function block_exacomp_add_examples_to_schedule_for_all($courseid) {
    // Check Permission
    block_exacomp_require_teacher($courseid);
    // Get all examples to add:
    //    -> studentid 0: on teachers schedule
    $examples = g::$DB->get_records_select(BLOCK_EXACOMP_DB_SCHEDULE, "studentid = 0 AND courseid = ? AND start IS NOT NULL AND endtime IS NOT NULL AND deleted = 0", array($courseid));

    // Get all students for the given course
    $students = block_exacomp_get_students_by_course($courseid);
    // Add examples for all users
    foreach ($examples as $example) {
        foreach ($students as $student) {
            if (block_exacomp_is_example_visible($courseid, $example->exampleid, $student->id)) {
                block_exacomp_add_example_to_schedule($student->id, $example->exampleid, g::$USER->id, $courseid, $example->start, $example->endtime, $example->ethema_ismain, $example->ethema_issubcategory);
            }
        }
    }

    // Delete records from the teacher's schedule
    g::$DB->delete_records_list(BLOCK_EXACOMP_DB_SCHEDULE, 'id', array_keys($examples));

    return true;
}

/**
 * add example to all planning storages for all students of group
 *
 * @param mixed $courseid
 * @param integer $groupid
 * @param integer $distributionid
 * @return boolean
 */
function block_exacomp_add_examples_to_schedule_for_group($courseid, $groupid, $distributionid) {
    // Check Permission
    block_exacomp_require_teacher($courseid);
    // Get all examples to add:
    //    -> studentid 0: on teachers schedule
    $examples = g::$DB->get_records_select(BLOCK_EXACOMP_DB_SCHEDULE, "studentid = 0 AND courseid = ? AND start IS NOT NULL AND endtime IS NOT NULL AND deleted = 0", array($courseid));

    // Get all students for the given group
    $groupmembers = block_exacomp_groups_get_members($courseid, $groupid);

    // Add examples for all users of group
    foreach ($examples as $example) {
        foreach ($groupmembers as $student) {
            if (block_exacomp_is_example_visible($courseid, $example->exampleid, $student->id)) {
                block_exacomp_add_example_to_schedule($student->id, $example->exampleid, g::$USER->id, $courseid, $example->start, $example->endtime, $example->ethema_ismain, $example->ethema_issubcategory, 'C', false, $distributionid);
            }
        }
    }

    // Delete records from the teacher's schedule
    g::$DB->delete_records_list(BLOCK_EXACOMP_DB_SCHEDULE, 'id', array_keys($examples));

    return true;
}

/**
 * add example to all planning storages for students
 *
 * @param mixed $courseid
 * @param array $students
 * @param integer $distributionid
 * @return boolean
 */
function block_exacomp_add_examples_to_schedule_for_students($courseid, $students, $distributionid) {
    // Check Permission
    block_exacomp_require_teacher($courseid);
    // Get all examples to add:
    //    -> studentid 0: on teachers schedule
    $examples = g::$DB->get_records_select(BLOCK_EXACOMP_DB_SCHEDULE, "studentid = 0 AND courseid = ? AND start IS NOT NULL AND endtime IS NOT NULL AND deleted = 0", array($courseid));

    // Add examples for all users of group
    foreach ($examples as $example) {
        foreach ($students as $student) {
            if (block_exacomp_is_example_visible($courseid, $example->exampleid, $student->id)) {
                block_exacomp_add_example_to_schedule($student, $example->exampleid, g::$USER->id, $courseid, $example->start, $example->endtime, $example->ethema_ismain, $example->ethema_issubcategory, 'C', false, $distributionid);
            }
        }
    }

    // Delete records from the teacher's schedule
    g::$DB->delete_records_list(BLOCK_EXACOMP_DB_SCHEDULE, 'id', array_keys($examples));

    return true;
}

/**
 * help function for printer
 *
 * @param unknown $date
 * @param unknown $days
 * @return number
 */
function block_exacomp_add_days($date, $days) {
    return mktime(0, 0, 0, date('m', $date), date('d', $date) + $days, date('Y', $date));
}

/**
 * get tree where a flag for each object (from subject to child descriptor) indicates if an example is associated with
 *
 * @param unknown $courseid
 * @param array $associated_descriptors
 * @param number $exampleid
 * @param number $descriptorid
 * @param string $showallexamples
 * @return associative_array
 */
function block_exacomp_build_example_association_tree($courseid, $associated_descriptors = array(), $exampleid = 0, $descriptorid = 0, $showallexamples = false, $editmode = false, $showonlyvisible = true) {
    //get all subjects, topics, descriptors and examples
    $tree = block_exacomp_get_competence_tree($courseid, null, null, false, BLOCK_EXACOMP_SHOW_ALL_NIVEAUS, true, block_exacomp_get_settings_by_course($courseid)->filteredtaxonomies,
        false, false, $showonlyvisible, false, false, true, null, $editmode);

    // unset all descriptors, topics and subjects that do not contain the example descriptors
    foreach ($tree as $skey => $subject) {
        $subject->associated = 0;
        foreach ($subject->topics as $tkey => $topic) {
            $topic->associated = 0;
            if (isset($topic->descriptors)) {
                foreach ($topic->descriptors as $dkey => $descriptor) {

                    $descriptor = block_exacomp_check_child_descriptors($descriptor, $associated_descriptors, $exampleid, $descriptorid, $showallexamples);

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
 *
 * @param unknown $descriptor
 * @param unknown $associated_descriptors
 * @param unknown $exampleid
 * @param number $descriptorid
 * @param string $showallexamples
 * @return unknown
 */
function block_exacomp_check_child_descriptors($descriptor, $associated_descriptors, $exampleid, $descriptorid = 0, $showallexamples = false) {

    $descriptor->associated = 0;
    $descriptor->direct_associated = 0;

    if (array_key_exists($descriptor->id, $associated_descriptors) || $descriptorid == $descriptor->id || ($showallexamples && !empty($descriptor->examples))) {
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
        if (array_key_exists($cvalue->id, $associated_descriptors) || $descriptorid == $ckey || ($showallexamples && !empty($cvalue->examples))) {
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
 *
 * @param unknown $niveaus
 * @param unknown $spanningNiveaus
 * @return number
 */
function block_exacomp_calculate_spanning_niveau_colspan($niveaus, $spanningNiveaus) {

    if (!$niveaus) {
        return 0;
    }
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
 *
 * @param unknown $courseid
 * @param unknown $topic
 * @param unknown $studentid
 * @return boolean
 */
function block_exacomp_is_topic_visible($courseid, $topic, $studentid) {
    //global $DB;
    //return Cache::staticCallback(__FUNCTION__, function($courseid, $topic, $studentid) {
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
    //}, [$courseid, $topic->id, $studentid]);
}

/**
 * visibility for topic in course and group context
 *
 * @param unknown $courseid
 * @param unknown $topic
 * @param unknown $groupid
 * @return boolean
 */
function block_exacomp_is_topic_visible_for_group($courseid, $topic, $groupid) {
    global $DB;

    $groupmembers = block_exacomp_groups_get_members($courseid, $groupid);
    foreach ($groupmembers as $student) {
        $visibilities = block_exacomp_get_topic_visibilities_for_course_and_user($courseid, 0);
        if (isset($visibilities[$topic->id]) && !$visibilities[$topic->id]) {
            return false;
        }

        if ($student->id > 0) {
            // also check student if set
            $visibilities = block_exacomp_get_topic_visibilities_for_course_and_user($courseid, $student->id);
            if (isset($visibilities[$topic->id]) && !$visibilities[$topic->id]) {
                return false;
            }
        }
    }

    return true;
}

/**
 * visibility for topic in course and user context
 *
 * @param unknown $courseid
 * @param unknown $topic
 * @param unknown $studentid
 * @param unknown $niveauid
 * @return boolean
 */
function block_exacomp_is_niveau_visible($courseid, $topicid, $studentid, $niveauid) {
    $topicid = is_scalar($topicid) ? $topicid : $topicid->id;
    //global $DB;
    // $studentid could be BLOCK_EXACOMP_SHOW_ALL_STUDENTS
    if ($studentid <= 0) {
        $studentid = 0;
    }

    //    var_dump($courseid);
    //    var_dump($topic->id);
    //    var_dump($studentid);
    //    var_dump($niveauid);
    //    die;

    $visibilities = block_exacomp_get_niveau_visibilities_for_course_and_topic_and_user($courseid, 0, $topicid);
    //    var_dump($visibilities);
    //    die;
    if (isset($visibilities[$niveauid]) && !$visibilities[$niveauid]) {
        return false;
    }

    if ($studentid > 0) {
        // also check student if set
        $visibilities = block_exacomp_get_niveau_visibilities_for_course_and_topic_and_user($courseid, $studentid, $topicid);
        if (isset($visibilities[$niveauid]) && !$visibilities[$niveauid]) {
            return false;
        }
    }

    return true;
}

/**
 * visibility for descriptor in course and user context
 *
 * @param integer $courseid
 * @param unknown $descriptor
 * @param integer $studentid
 * @param bool $mindTopicVisibility
 * @return boolean
 */
function block_exacomp_is_descriptor_visible($courseid, $descriptor, $studentid, $mindTopicVisibility = true) {
    //global $DB;
    static $visibleDescriptors;

    if ($visibleDescriptors === null) {
        $visibleDescriptors = array();
    }

    if (isset($visibleDescriptors[$courseid][$descriptor->id][$studentid])) {
        return $visibleDescriptors[$courseid][$descriptor->id][$studentid];
    }

    // $studentid could be BLOCK_EXACOMP_SHOW_ALL_STUDENTS
    if ($studentid <= 0) {
        $studentid = 0;
    }

    if ($mindTopicVisibility &&
        ($topic = \block_exacomp\topic::get($descriptor->topicid)) &&
        !block_exacomp_is_topic_visible($courseid, $topic, $studentid)) {
        $visibleDescriptors[$courseid][$descriptor->id][$studentid] = false;
        return false;
    }

    // visibility --for-all--  more important that visibility of concrete user, so - check it first
    $visibilities = block_exacomp_get_descriptor_visibilities_for_course_and_user($courseid, 0);
    if (isset($visibilities[$descriptor->id]) && !$visibilities[$descriptor->id]) {
        $visibleDescriptors[$courseid][$descriptor->id][$studentid] = false;
        return false;
    }

    if ($studentid > 0) {
        // also check student if set
        $visibilities = block_exacomp_get_descriptor_visibilities_for_course_and_user($courseid, $studentid);
        if (isset($visibilities[$descriptor->id]) && !$visibilities[$descriptor->id]) {
            $visibleDescriptors[$courseid][$descriptor->id][$studentid] = false;
            return false;
        }
    }

    $visibleDescriptors[$courseid][$descriptor->id][$studentid] = true;
    return true;
}

/**
 * visibility for example in course and user context
 *
 * @param unknown $courseid
 * @param unknown $example or exampleid
 * @param unknown $studentid
 * @return boolean
 */
function block_exacomp_is_example_visible($courseid, $example, $studentid) {
    static $visibleExamples;

    if ($visibleExamples === null) {
        $visibleExamples = array();
    }

    $exampleid = is_scalar($example) ? $example : $example->id;

    // if the example has courseid and it is not equal $courseid --> So the example has been created in diggrplus only for this course... but why should it then even exist?
    // keep the check, but actually, the example should not even be here invisibly, because that will confuse the teachers who cannot change visibility but have this foreign example in their subject
    if ($example->courseid > 0 && $example->courseid != $courseid) {
        $visibleExamples[$courseid][$exampleid][$studentid] = false;
        return false;
    }

    if (isset($visibleExamples[$courseid][$exampleid][$studentid])) {
        return $visibleExamples[$courseid][$exampleid][$studentid];
    }

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
    if (isset($visibilities[$exampleid]) && !$visibilities[$exampleid]) {
        $visibleExamples[$courseid][$exampleid][$studentid] = false;
        return false;
    }

    if ($studentid > 0) {
        // also check student if set
        $visibilities = block_exacomp_get_example_visibilities_for_course_and_user($courseid, $studentid);
        if (isset($visibilities[$exampleid]) && !$visibilities[$exampleid]) {
            $visibleExamples[$courseid][$exampleid][$studentid] = false;
            return false;
        }
    }

    $visibleExamples[$courseid][$exampleid][$studentid] = true;
    return true;
}

///**
// * visibility for example for user in ANY course
// * @param unknown $example or exampleid
// * @param unknown $userid
// * @return boolean
// */
//function block_exacomp_is_example_visible_in_any_course($example, $userid) {
//    global $DB;
//    $exampleid = is_scalar($example) ? $example : $example->id;
//
//    var_dump($userid);
//
//    $userid = 0;
//
//    $visibles = $DB->get_records_sql("
//        SELECT DISTINCT ev.exampleid, ev.visible
//        FROM {".BLOCK_EXACOMP_DB_EXAMPVISIBILITY."} ev
//        WHERE ev.studentid=?
//    ", [$userid]);
//
//    var_dump($visibles);
//    die;
//
//    return true;
//}

/**
 * visibility for example solution in course and user context
 *
 * @param unknown $courseid
 * @param unknown $example or exampleid
 * @param unknown $studentid
 * @return boolean
 */
function block_exacomp_is_example_solution_visible($courseid, $example, $studentid) {
    static $visibleExampleSolutions;

    if ($visibleExampleSolutions === null) {
        $visibleExampleSolutions = array();
    }

    $exampleid = is_scalar($example) ? $example : $example->id;

    if (isset($visibleExampleSolutions[$courseid][$exampleid][$studentid])) {
        return $visibleExampleSolutions[$courseid][$exampleid][$studentid];
    }

    // $studentid could be BLOCK_EXACOMP_SHOW_ALL_STUDENTS
    if ($studentid <= 0) {
        $studentid = 0;
    }

    $visibilities = block_exacomp_get_solution_visibilities_for_course_and_user($courseid, 0);
    if (isset($visibilities[$exampleid]) && !$visibilities[$exampleid]) {
        $visibleExampleSolutions[$courseid][$exampleid][$studentid] = false;
        return false;
    }

    if ($studentid > 0) {
        // also check student if set
        $visibilities = block_exacomp_get_solution_visibilities_for_course_and_user($courseid, $studentid);
        if (isset($visibilities[$exampleid]) && !$visibilities[$exampleid]) {
            $visibleExampleSolutions[$courseid][$exampleid][$studentid] = false;
            return false;
        }
    }
    $visibleExampleSolutions[$courseid][$exampleid][$studentid] = true;
    return true;
}

/**
 * different css classes for student and teacher
 *
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
 *
 * @param unknown $descriptor
 * @param boolean $reloadTopic
 * @return string
 */
function block_exacomp_get_descriptor_numbering($descriptor, $reloadTopic = false) {
    if (!block_exacomp_is_numbering_enabled()) {
        return '';
    }

    $id = $descriptor->id; // saved for later

    static $numberingCache = [];

    if (!isset($numberingCache[$id])) {
        // if the descriptor is from search result tree - his topic has not full descriptors list (only founded)
        // so, we need to reload all descriptors for this topic
        if ($reloadTopic) {
            unset($descriptor->topic);
        }
        // build cache
        if (isset($descriptor->topic) && $descriptor->topic instanceof \block_exacomp\topic) {
            $topic = $descriptor->topic;
        } else if (!empty($descriptor->topicid)) {
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
                } else if ($niveaus[$descriptor->niveauid]->descriptor_cnt > 1) {
                    // make niveaus with multiple descriptors in the format of "{$niveau->numb}-{$i}"
                    $descriptorMainNumber = $niveaus[$descriptor->niveauid]->numb;
                    @$niveaus[$descriptor->niveauid]->descriptor_i++;
                    $descriptorNumber = $niveaus[$descriptor->niveauid]->numb . '-' . $niveaus[$descriptor->niveauid]->descriptor_i;
                } else {
                    $descriptorMainNumber = $niveaus[$descriptor->niveauid]->numb;
                    $descriptorNumber = $niveaus[$descriptor->niveauid]->numb;
                }

                $numberingCache[$descriptor->id] = $topicNumbering ? $topicNumbering . '.' . $descriptorNumber : '';

                foreach (array_values($descriptor->children) as $j => $descriptor) {
                    $numberingCache[$descriptor->id] = $topicNumbering ? $topicNumbering . '.' . $descriptorNumber . '.' . ($j + 1) : '';
                }
            }
        }
    }

    return isset($numberingCache[$id]) ? $numberingCache[$id] : 'not found #v96900';
}

/**
 * get numbering for topic
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
        return $subject->titleshort . '.' . $topic->numb;
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
        $subject->cross_subject_drafts = block_exacomp\cross_subject::get_objects(array('subjectid' => $subject->id, 'courseid' => 0), 'groupcategory, title');
        if (!$subject->cross_subject_drafts) {
            unset($subjects[$key]);
        }
    }

    return $subjects;
}

function block_exacomp_get_crosssubject_groupcategories($subjectid = -1) {
    global $DB;

    //    //get kategories of all subjects
    //    if($subjectid == -1){
    //        $groupcategories = $DB->get_records_sql('
    //			SELECT DISTINCT groupcategory
    //			FROM {'.BLOCK_EXACOMP_DB_CROSSSUBJECTS.'}
    //			WHERE courseid=0
    //            ORDER BY groupcategory');
    //    }else{
    //        $groupcategories = $DB->get_records_sql('
    //			SELECT DISTINCT groupcategory
    //			FROM {'.BLOCK_EXACOMP_DB_CROSSSUBJECTS.'}
    //			WHERE subjectid=? AND courseid=0
    //            ORDER BY groupcategory', [$subjectid]);
    //    }

    //get categories of all subjects --> fieldset instead of records, since records want distinct unique records
    if ($subjectid == -1) {
        $groupcategories = $DB->get_fieldset_sql('
			SELECT DISTINCT groupcategory
			FROM {' . BLOCK_EXACOMP_DB_CROSSSUBJECTS . '}
			WHERE courseid=0
            ORDER BY groupcategory');
    } else {
        $groupcategories = $DB->get_fieldset_sql('
			SELECT DISTINCT groupcategory
			FROM {' . BLOCK_EXACOMP_DB_CROSSSUBJECTS . '}
			WHERE subjectid=? AND courseid=0
            ORDER BY groupcategory', [$subjectid]);
    }

    return $groupcategories;
}

/**
 * get crosssubjectdrafts grouped by subject
 *
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
			FROM {' . BLOCK_EXACOMP_DB_CROSSSUBJECTS . '}
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
 *
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
 *
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
    $evaluationniveau_items = \block_exacomp\global_config::get_evalniveaus(null, $courseid);

    if (block_exacomp_use_eval_niveau($courseid)) {
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
                    $sql = "SELECT * FROM {" . BLOCK_EXACOMP_DB_ITEM_MM . "} ie JOIN {" . 'block_exaportitem' . "} i ON ie.itemid = i.id " .
                        "WHERE ie.exacomp_record_id = ? AND i.userid = ? AND i.courseid = ?";
                    $records = $DB->get_records_sql($sql, array($example->id, $studentid, $courseid));
                    if ($records) {
                        $submission_exists = true;
                    }
                }

                $teacher_eval_exists = false;
                $sql = "SELECT * FROM {" . BLOCK_EXACOMP_DB_EXAMPLEEVAL . "} WHERE courseid = ? AND exampleid = ? AND studentid=? AND teacher_evaluation>=0";
                $records = $DB->get_records_sql($sql, array($courseid, $example->id, $studentid));
                if ($records) {
                    $teacher_eval_exists = true;
                }

                $student_eval_exists = false;
                $sql = "SELECT * FROM {" . BLOCK_EXACOMP_DB_EXAMPLEEVAL . "} WHERE courseid = ? AND exampleid = ? AND studentid = ? AND student_evaluation>=0";
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
                if (block_exacomp_use_eval_niveau($courseid)) {
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
 * @param array|object $item database item
 * @param string $type
 * @return stored_file
 */
function block_exacomp_get_file($item, $type, $position = -1) {
    // this function reads the associated file from the moodle file storage

    $fs = get_file_storage();
    $files = $fs->get_area_files(context_system::instance()->id, 'block_exacomp', $type, $item->id, null, false);

    // return first file
    if ($position == -1) {
        return reset($files);
    } else {
        return array_shift(array_slice($files, $position, 1));
    }

}

/**
 * @param array|object $item database item
 * @param string $type
 * @return number of files this example has for an item
 */
function block_exacomp_get_number_of_files($item, $type) {
    // this function reads the associated file from the moodle file storage
    $fs = get_file_storage();
    $files = $fs->get_area_files(context_system::instance()->id, 'block_exacomp', $type, $item->id, null, false);
    return sizeof($files);
}

///**
// * @return stored_file
// * @param array|object $item database item
// * @param string $type
// */
//function block_exacomp_get_files($item, $type) {
//    // this function reads the associated file from the moodle file storage
//
//    $fs = get_file_storage();
//    $files = $fs->get_area_files(context_system::instance()->id, 'block_exacomp', $type, $item->id, null, false);
//
//    // return  files
//    return $files;
//}

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
 *
 * @param unknown $studentid
 * @param unknown $courseid
 */
function block_exacomp_get_examples_for_pool($studentid, $courseid) {
    global $DB, $USER;

    if (date('w', time()) == 1) {
        $beginning_of_week = strtotime('Today', time());
    } else {
        $beginning_of_week = strtotime('last Monday', time());
    }

    //if teacher: only show the examples that this teacher added to the plannning storage (creatorid)
    if ($studentid == 0) {
        $sql = "SELECT DISTINCT s.id, s.*,
				e.title, e.id as exampleid, e.source AS example_source, evis.visible, e.timeframe,
				eval.student_evaluation, eval.teacher_evaluation, eval.evalniveauid, evis.courseid, s.id as scheduleid,
				e.externalurl, e.externaltask, e.description, s.courseid as schedulecourseid,
				e.schedule_marker, e.activityid, e.is_teacherexample
			FROM {block_exacompschedule} s
			  JOIN {block_exacompexamples} e ON e.id = s.exampleid
			  JOIN {" . BLOCK_EXACOMP_DB_EXAMPVISIBILITY . "} evis ON evis.exampleid = e.id AND evis.studentid = 0 AND evis.visible = 1 AND evis.courseid = ?
			    LEFT JOIN {block_exacompexameval} eval ON eval.exampleid = s.exampleid AND eval.studentid = s.studentid AND eval.courseid = s.courseid
			WHERE s.studentid = ? AND s.deleted = 0 AND (
				-- noch nicht auf einen tag geleg
				(s.start IS null OR s.start=0)
				-- oder auf einen tag der vorwoche gelegt und noch nicht evaluiert
				OR (s.start < ? AND (eval.teacher_evaluation IS NULL OR eval.teacher_evaluation=0))
            ) AND s.creatorid = ?
			ORDER BY s.id";
        $entries = $DB->get_records_sql($sql, array($courseid, $studentid, $beginning_of_week, $USER->id));
    } else {
        block_exacomp_update_related_examples_visibilities_for_single_student($courseid, $studentid);
        $sql = "SELECT DISTINCT s.id, s.*,
				e.title, e.id as exampleid, e.source AS example_source, evis.visible, e.timeframe,
				eval.student_evaluation, eval.teacher_evaluation, eval.evalniveauid, evis.courseid, s.id as scheduleid,
				e.externalurl, e.externaltask, e.description, s.courseid as schedulecourseid,
				e.schedule_marker, e.activityid, e.is_teacherexample
			FROM {block_exacompschedule} s
			  JOIN {block_exacompexamples} e ON e.id = s.exampleid
			  JOIN {" . BLOCK_EXACOMP_DB_EXAMPVISIBILITY . "} evis ON evis.exampleid = e.id AND (evis.studentid = ? OR evis.studentid = 0) AND evis.visible = 1 AND evis.courseid = ?
			    LEFT JOIN {block_exacompexameval} eval ON eval.exampleid = s.exampleid AND eval.studentid = s.studentid AND eval.courseid = s.courseid
			WHERE s.studentid = ? AND s.deleted = 0 AND (
				-- noch nicht auf einen tag geleg
				(s.start IS null OR s.start=0)
				-- oder auf einen tag der vorwoche gelegt und noch nicht evaluiert
				OR (s.start < ? AND (eval.teacher_evaluation IS NULL OR eval.teacher_evaluation=0))
			)
			ORDER BY s.id";
        $entries = $DB->get_records_sql($sql, array($studentid, $courseid, $studentid, $beginning_of_week));
    }
    return $entries;
}

/**
 * get all examples located in trash for student and course context
 *
 * @param unknown $studentid
 * @param unknown $courseid
 */
function block_exacomp_get_examples_for_trash($studentid, $courseid) {
    global $DB, $USER;

    //if teacher: only show the examples that this teacher added to the plannning storage (creatorid)
    if ($studentid == 0) {
        $sql = "select s.*,
				e.title, e.id as exampleid, e.source AS example_source, evis.visible,
				eval.student_evaluation, eval.teacher_evaluation, eval.evalniveauid, evis.courseid, s.id as scheduleid, s.courseid as schedulecourseid
			FROM {block_exacompschedule} s
			JOIN {block_exacompexamples} e ON e.id = s.exampleid
			JOIN {" . BLOCK_EXACOMP_DB_EXAMPVISIBILITY . "} evis ON evis.exampleid= e.id AND evis.studentid=0 AND evis.visible = 1 AND evis.courseid=?
			LEFT JOIN {block_exacompexameval} eval ON eval.exampleid = s.exampleid AND eval.studentid = s.studentid
			WHERE s.studentid = ? AND s.deleted = 1 AND (
				-- noch nicht auf einen tag geleg
				(s.start IS null OR s.start=0)
			) AND s.creatorid = ?
			ORDER BY s.id";
        $entries = $DB->get_records_sql($sql, array($courseid, $studentid, $USER->id));
    } else {
        $sql = "select s.*,
				e.title, e.id as exampleid, e.source AS example_source, evis.visible,
				eval.student_evaluation, eval.teacher_evaluation, eval.evalniveauid, evis.courseid, s.id as scheduleid, s.courseid as schedulecourseid
			FROM {block_exacompschedule} s
			JOIN {block_exacompexamples} e ON e.id = s.exampleid
			JOIN {" . BLOCK_EXACOMP_DB_EXAMPVISIBILITY . "} evis ON evis.exampleid= e.id AND evis.studentid=0 AND evis.visible = 1 AND evis.courseid=?
			LEFT JOIN {block_exacompexameval} eval ON eval.exampleid = s.exampleid AND eval.studentid = s.studentid
			WHERE s.studentid = ? AND s.deleted = 1 AND (
				-- noch nicht auf einen tag geleg
				(s.start IS null OR s.start=0)
			)
			ORDER BY s.id";
        $entries = $DB->get_records_sql($sql, array($courseid, $studentid));
    }

    return $entries;
}

/**
 * with start and end != 0 move example to learning calendar, otherwise if deleted = 0 to pool, or delted = 1 to trash
 *
 * @param unknown $scheduleid
 * @param unknown $start
 * @param unknown $endtime
 * @param number $deleted
 */
function block_exacomp_set_example_start_end($scheduleid, $start, $endtime, $deleted = 0) {
    global $DB, $USER;

    $entry = $DB->get_record(BLOCK_EXACOMP_DB_SCHEDULE, array('id' => $scheduleid));
    $entry->lastmodifiedbyid = $USER->id;
    if (!$deleted && $start && !$entry->start) {
        // example was moved from planning storage to calendar
        $entry->addedtoschedulebyid = $USER->id;
    }
    $entry->start = $start;
    $entry->endtime = $endtime;
    $entry->deleted = $deleted;

    if ($entry->studentid != $USER->id) {
        //Permission denied error if wrong teacher tries to change this example
        block_exacomp_require_teacher($entry->courseid);
    }

    if ($DB instanceof pgsql_native_moodle_database) {
        // HACK: because moodle doesn't quote pgsql identifiers and pgsql doesn't allow end as column name
        $DB->execute('UPDATE {' . BLOCK_EXACOMP_DB_SCHEDULE . '} SET "endtime"=? WHERE id=?', [$entry->endtime, $entry->id]);
        unset($entry->endtime);
    }

    $DB->update_record(BLOCK_EXACOMP_DB_SCHEDULE, $entry);
    return $entry;
}

/**
 * copy example from calendar to pool
 *
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
    unset($entry->endtime);

    $DB->insert_record(BLOCK_EXACOMP_DB_SCHEDULE, $entry);
}

/**
 * remove example from schedule
 *
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
 *
 * @param unknown $courseid
 * @param unknown $studentid
 * @param unknown $start
 * @param unknown $end
 */
function block_exacomp_get_examples_for_start_end($courseid, $studentid, $start, $end) {
    global $DB, $USER;

    //if teacher: only show the examples that this teacher added to the plannning storage (creatorid)
    if ($studentid == 0) {
        $sql = "SELECT s.*,
				e.title, e.id as exampleid, e.source AS example_source, evis.visible,
				eval.student_evaluation, eval.teacher_evaluation, eval.evalniveauid, s.courseid, s.id as scheduleid,
				e.externalurl, e.externaltask, e.description, s.courseid as schedulecourseid,
				e.schedule_marker, e.timeframe as timeframe, e.is_teacherexample
				-- evalniveau.title as niveau,
			FROM {block_exacompschedule} s
			JOIN {block_exacompexamples} e ON e.id = s.exampleid
			JOIN {" . BLOCK_EXACOMP_DB_EXAMPVISIBILITY . "} evis ON evis.exampleid= e.id AND evis.studentid=0 AND evis.visible = 1 AND evis.courseid=?
			LEFT JOIN {block_exacompexameval} eval ON eval.exampleid = s.exampleid AND eval.studentid = s.studentid
			-- LEFT JOIN {block_exacompeval_niveau} evalniveau ON evalniveau.id = eval.evalniveauid -- moved to exacomp plugin settings
			WHERE s.studentid = ? AND s.courseid = ? AND (
				-- innerhalb end und start
				(s.start > ? AND s.endtime < ?)
			) AND s.creatorid = ?
			-- GROUP BY s.id -- because a bug somewhere causes duplicate rows
			ORDER BY e.title";
        $entries = $DB->get_records_sql($sql, array($courseid, $studentid, $courseid, $start, $end, $USER->id));
    } else {
        $sql = "SELECT s.*,
				e.title, e.id as exampleid, e.source AS example_source, evis.visible,
				eval.student_evaluation, eval.teacher_evaluation, eval.evalniveauid, s.courseid, s.id as scheduleid,
				e.externalurl, e.externaltask, e.description, s.courseid as schedulecourseid,
				e.schedule_marker, e.timeframe as timeframe, e.is_teacherexample
				-- evalniveau.title as niveau,
			FROM {block_exacompschedule} s
			JOIN {block_exacompexamples} e ON e.id = s.exampleid
			JOIN {" . BLOCK_EXACOMP_DB_EXAMPVISIBILITY . "} evis ON evis.exampleid= e.id AND evis.studentid=0 AND evis.visible = 1 AND evis.courseid=?
			LEFT JOIN {block_exacompexameval} eval ON eval.exampleid = s.exampleid AND eval.studentid = s.studentid
			-- LEFT JOIN {block_exacompeval_niveau} evalniveau ON evalniveau.id = eval.evalniveauid -- moved to exacomp plugin settings
			WHERE s.studentid = ? AND s.courseid = ? AND (
				-- innerhalb end und start
				(s.start > ? AND s.endtime < ?)
			)
			-- GROUP BY s.id -- because a bug somewhere causes duplicate rows
			ORDER BY e.title";
        $entries = $DB->get_records_sql($sql, array($courseid, $studentid, $courseid, $start, $end));
    }
    if (is_array($entries)) {
        $niveautitles = block_exacomp_get_assessment_diffLevel_options_splitted($courseid);
        foreach ($entries as $k => $entry) {
            if ($entry->evalniveauid && array_key_exists($entry->evalniveauid, $niveautitles)) {
                $entries[$k]->niveau = $niveautitles[$entry->evalniveauid];
            } else {
                $entries[$k]->niveau = '';
            }
        }
    }

    return $entries;
}

/**
 * examples from all courses are shown in calendar
 *
 * @param unknown $studentid
 * @param unknown $start
 * @param unknown $end
 * @return unknown[]
 */
function block_exacomp_get_examples_for_start_end_all_courses($studentid, $start, $end) {
    global $USER;
    if ($studentid < 0) {
        $studentid = 0;
    }

    $courses = block_exacomp_get_courseids();
    $examples = array();

    if ($studentid == 0) { //as teacher
        foreach ($courses as $course) {
            if (block_exacomp_is_teacher($course)) { //only show from courses where the teacher is a teacher
                $course_examples = block_exacomp_get_examples_for_start_end($course, $studentid, $start, $end);
                foreach ($course_examples as $example) {
                    if (!array_key_exists($example->scheduleid, $examples)) {
                        $examples[$example->scheduleid] = $example;
                    }
                }
            }
        }
    } else { //as student
        foreach ($courses as $course) {
            $course_examples = block_exacomp_get_examples_for_start_end($course, $studentid, $start, $end);
            foreach ($course_examples as $example) {
                if (!array_key_exists($example->scheduleid, $examples)) {
                    $examples[$example->scheduleid] = $example;
                }
            }
        }
    }

    return $examples;
}

/**
 * needed for communication with fullcalendar
 *
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
        $example_array['end'] = $example->endtime;
        $example_array['exampleid'] = $example->exampleid;
        $example_array['niveau'] = isset($example->niveau) ? $example->niveau : null;
        $example_array['description'] = isset($example->description) ? $example->description : "";
        $example_array['activityid'] = isset($example->activityid) ? $example->activityid : 0;

        if ($mind_eval) {
            $example_array['student_evaluation'] = $example->student_evaluation;
            $example_array['teacher_evaluation'] = $example->teacher_evaluation;

            $student_title = \block_exacomp\global_config::get_student_eval_title_by_id($example->student_evaluation, 'examples');
            $teacher_title = \block_exacomp\global_config::get_teacher_eval_title_by_id($example->teacher_evaluation);

            $example_array['student_evaluation_title'] = (strcmp($student_title, ' ') == 0) ? '-' : $student_title;
            $example_array['teacher_evaluation_title'] = (strcmp($teacher_title, ' ') == 0) ? '-' : $teacher_title;
        }
        if (isset($example->state)) {
            $example_array['state'] = $example->state;
        }
        if (isset($example->schedule_marker)) {
            $example_array['schedule_marker'] = $example->schedule_marker;
            $shorts = array(
                'structured' => 'SA',
                'free' => 'FA',
                'orientation' => 'O',
                'feedback' => 'F',
                'reflexion' => 'R',
            );
            if (array_key_exists($example->schedule_marker, $shorts)) {
                $example_array['schedule_marker_short'] = $shorts[$example->schedule_marker];
            }
        }

        $example_array['studentid'] = $example->studentid;
        $example_array['courseid'] = $example->courseid;
        $example_array['scheduleid'] = $example->scheduleid;
        $example_array['copy_url'] = $output->local_pix_icon("copy_example.png", block_exacomp_get_string('copy'));

        $img = html_writer::empty_tag('img',
            array('src' => new moodle_url('/blocks/exacomp/pix/assoc_icon.png'), 'alt' => block_exacomp_get_string("competence_associations"), 'title' => block_exacomp_get_string("competence_associations"), 'height' => 16, 'width' => 16));

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
                    html_writer::empty_tag('img',
                        array('src' => new moodle_url('/blocks/exacomp/pix/' . ((!$itemExists) ? 'manual_item.png' : 'reload.png')), 'alt' => block_exacomp_get_string("submission"), 'title' => block_exacomp_get_string("submission"))),
                    array("target" => "_blank", "onclick" => "window.open(this.href,this.target,'width=880,height=660, scrollbars=yes'); return false;"));
            } else {
                $url = block_exacomp_get_viewurl_for_example($example->studentid, $USER->id, $example->exampleid);
                if ($url) {
                    $example_array['submission_url'] = html_writer::link($url,
                        html_writer::empty_tag('img', array('src' => new moodle_url('/blocks/exacomp/pix/manual_item.png'), 'alt' => block_exacomp_get_string("submission"), 'title' => block_exacomp_get_string("submission"))), array(
                            "target" => "_blank",
                            "onclick" => "window.open(this.href,this.target,'width=880,height=660, scrollbars=yes'); return false;",
                        ));
                }
            }
        }
        if ($url = block_exacomp_get_file_url((object)array('id' => $example->exampleid), 'example_task')) {
            $example_array['task'] = html_writer::link($url, $output->preview_icon(), array("target" => "_blank"));
        } else if (isset($example->externalurl)) {
            $example_array['externalurl'] = html_writer::link(str_replace('&amp;', '&', $example->externalurl), $output->preview_icon(), array("target" => "_blank"));
        } else if (isset($example->externaltask)) {
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
 *
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

        // only write at the begin of every unit
        if ($i % 4 == 0) {
            //$entry['name'] = ($i / 4 + 1).'. Einheit';
            if (isset($timeentries[$i / 4])) {
                $entry['name'] = '--fromConfig--';
            } else {
                //$entry['name'] = '';
                $entry['name'] = block_exacomp_get_string('n' . ($i / 4 + 1) . '.unit');
            }
            //$entry['name'] = block_exacomp_get_string('n'.($i / 4 + 1).'.unit');
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
 *
 * @param unknown $secTime
 * @return string
 */
function block_exacomp_parse_seconds_to_timestring($secTime) {
    $hours = floor($secTime / 3600);
    $mins = floor(($secTime - ($hours * 3600)) / 60);

    return sprintf('%02d', $hours) . ":" . sprintf('%02d', $mins);
}

/**
 * get state for example
 *
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
    //state 10 = backgroundEvent

    $example = $DB->get_record(BLOCK_EXACOMP_DB_EXAMPLES, array('id' => $exampleid));
    if ($example->blocking_event == 1) {
        return BLOCK_EXACOMP_EXAMPLE_STATE_LOCKED_TIME;
    }

    //background event: should only be visible, nothing else... will be used for imported events from ics files from e.g. Webuntis
    if ($example->blocking_event == 3) {
        return 10;
    }

    $comp = $DB->get_record(BLOCK_EXACOMP_DB_EXAMPLEEVAL, array('courseid' => $courseid, 'exampleid' => $exampleid, 'studentid' => $studentid));
    $gradingScheme = block_exacomp_get_assessment_example_scheme($courseid);

    if ($comp && !$comp->resubmission && $comp->teacher_evaluation !== null) {
        if ($gradingScheme == BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE) { //the grading is in additionalinfo instead of teacher_evaluation
            if ($comp->additionalinfo == block_exacomp_get_assessment_max_value(BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE, $courseid)) {
                return BLOCK_EXACOMP_EXAMPLE_STATE_EVALUATED_NEGATIV;
            } else if ($comp->additionalinfo != -1 && $comp->additionalinfo != 0) {
                return BLOCK_EXACOMP_EXAMPLE_STATE_EVALUATED_POSITIV;
            }
        } else if ($gradingScheme == BLOCK_EXACOMP_ASSESSMENT_TYPE_POINTS) {
            if ($comp->teacher_evaluation < block_exacomp_get_assessment_max_value(BLOCK_EXACOMP_ASSESSMENT_TYPE_POINTS, $courseid) / 2) {
                return BLOCK_EXACOMP_EXAMPLE_STATE_EVALUATED_NEGATIV;
            }
            return BLOCK_EXACOMP_EXAMPLE_STATE_EVALUATED_POSITIV;
        } else {
            if ($comp->teacher_evaluation == 0) {
                return BLOCK_EXACOMP_EXAMPLE_STATE_EVALUATED_NEGATIV;
            }
            return BLOCK_EXACOMP_EXAMPLE_STATE_EVALUATED_POSITIV;
        }
    }

    if (block_exacomp_exaportexists()) {
        $sql = "select * FROM {" . BLOCK_EXACOMP_DB_ITEM_MM . "} ie
				JOIN {block_exaportitem} i ON i.id = ie.itemid
				WHERE ie.exacomp_record_id = ? AND i.userid = ?";

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
            if ($entry->start > 0 && $entry->endtime > 0) {
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
 *
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
 *
 * @param unknown $creatorid
 * @param unknown $courseid
 */
function block_exacomp_has_items_pre_planning_storage($creatorid, $courseid) {
    global $DB;

    return $DB->get_records(BLOCK_EXACOMP_DB_SCHEDULE, array('creatorid' => $creatorid, 'courseid' => $courseid, 'studentid' => 0));
}

/**
 * return pre-planning storage
 *
 * @param unknown $creatorid
 * @param unknown $courseid
 */
function block_exacomp_get_pre_planning_storage($creatorid, $courseid) {
    global $DB;

    $sql = "select s.*,
				e.title, e.id as exampleid, e.source AS example_source, evis.visible,
				evis.courseid, s.id as scheduleid
			FROM {" . BLOCK_EXACOMP_DB_SCHEDULE . "} s
			JOIN {" . BLOCK_EXACOMP_DB_EXAMPLES . "} e ON e.id = s.exampleid
			JOIN {" . BLOCK_EXACOMP_DB_EXAMPVISIBILITY . "} evis ON evis.exampleid= e.id AND evis.studentid=0 AND evis.visible = 1 AND evis.courseid=?
			WHERE s.creatorid = ? AND s.studentid=0 AND (
				-- noch nicht auf einen tag geleg
				(s.start IS null OR s.start=0)
			)
			ORDER BY s.id";

    return $DB->get_records_sql($sql, array($courseid, $creatorid));
}

/**
 * needed to mark if student has already any examples available in pre-planning storage in his/her pool
 *
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
 *
 * @param unknown $exampleid
 * @param unknown $descrid
 * @return boolean
 */
function block_exacomp_example_up($exampleid, $descrid) {
    return block_exacomp_example_order($exampleid, $descrid, "<");
}

/**
 * change example order: move down
 *
 * @param unknown $exampleid
 * @param unknown $descrid
 * @return boolean
 */
function block_exacomp_example_down($exampleid, $descrid) {
    return block_exacomp_example_order($exampleid, $descrid, ">");
}

/**
 * change example order in database to persist
 *
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
        $sql = "SELECT e.*, de.sorting as descsorting FROM {block_exacompexamples} e
			JOIN {" . BLOCK_EXACOMP_DB_DESCEXAMP . "} de ON de.exampid = e.id
			WHERE de.sorting " . ((strcmp($operator, '<') == 0) ? '<' : '>') . " ? AND de.descrid = ?
			ORDER BY de.sorting " . ((strcmp($operator, '<') == 0) ? 'DESC' : 'ASC') . "
			LIMIT 1";

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
 *
 * @param unknown $courseid
 */
function block_exacomp_empty_pre_planning_storage($courseid) {
    global $DB;

    $DB->delete_records(BLOCK_EXACOMP_DB_SCHEDULE, array('courseid' => $courseid, 'studentid' => 0));
}

/**
 * get current exaport item for example -> this is example submission
 *
 * @param unknown $userid
 * @param unknown $exampleid
 */
function block_exacomp_get_current_item_for_example($userid, $exampleid) {
    global $DB;

    $sql = 'SELECT i.*, ie.status, ie.teachervalue, ie.studentvalue
              FROM {block_exacompexamples} e
			    JOIN {' . BLOCK_EXACOMP_DB_ITEM_MM . '} ie ON ie.exacomp_record_id = e.id
			    JOIN {block_exaportitem} i ON ie.itemid = i.id
			  WHERE e.id = ?
			      AND i.userid = ?
			  ORDER BY ie.timecreated DESC
			  LIMIT 1';
    return $DB->get_record_sql($sql, array($exampleid, $userid));
}

/**
 * get exaport items for example/topic/descriptor
 *
 * @param unknown $userid
 * @param unknown $compid
 * @param unknown $comptype
 */
function block_exacomp_get_items_for_competence($userid, $compid = -1, $comptype = -1, $search = "", $niveauid = -1, $status = "", $courseid = -1) {
    global $DB;

    $courseidCondition = $courseid == -1 ? "" : "AND i.courseid = " . $courseid;

    $compidCondition = $compid == -1 ? "" : "AND d.id = :compid";
    $niveauCondition = $niveauid == -1 ? "" : "AND descr.niveauid = :niveauid";
    $niveauConditionD = $niveauid == -1 ? "" : "AND d.niveauid = :niveauid";

    switch ($status) {
        case "inprogress":
            $statusCondition = "AND ie.status = 0";
            break;
        case "submitted":
            $statusCondition = "AND ie.status = 1";
            break;
        case "completed":
            $statusCondition = "AND ie.status = 2";
            break;
        default:
            $statusCondition = "";
            break;
    }

    $params = [];

    // niveaucondition is needed now since we use descriptors as well, and descripotrs can have niveaus 04.11.2021
    // The joins do not really take up much of the time. The high amount of queries does. Maybe indizes could speed this up?
    // THe search could be made conditional
    switch ($comptype) {
        case BLOCK_EXACOMP_TYPE_EXAMPLE:
            $sql = 'SELECT i.*, ie.status, ie.teachervalue, ie.studentvalue, topic.title as topictitle, subj.title as subjecttitle, topic.id as topicid, subj.id as subjectid
              FROM {block_exacompexamples} d
                JOIN {' . BLOCK_EXACOMP_DB_ITEM_MM . '} ie ON ie.exacomp_record_id = d.id
                JOIN {block_exaportitem} i ON ie.itemid = i.id
                JOIN {block_exacompdescrexamp_mm} descexamp ON descexamp.exampid = d.id
                JOIN {block_exacompdescrtopic_mm} desctop ON desctop.descrid = descexamp.descrid
                JOIN {block_exacomptopics} topic ON topic.id = desctop.topicid
                JOIN {block_exacompsubjects} subj ON topic.subjid = subj.id
              WHERE i.userid = :userid
                ' . $compidCondition . '
                ' . $statusCondition . '
                ' . $courseidCondition . '
                AND ie.competence_type = :comptype
                AND (d.title LIKE :searchtitle OR d.description LIKE :searchdescription OR i.name LIKE :searchname OR i.intro LIKE :searchintro)
              ORDER BY ie.timecreated DESC';
            $params["userid"] = $userid;
            $params["compid"] = $compid;
            $params["comptype"] = $comptype;
            $params["searchtitle"] = "%" . $search . "%";
            $params["searchdescription"] = "%" . $search . "%";
            $params["searchname"] = "%" . $search . "%";
            $params["searchintro"] = "%" . $search . "%";
            $params["niveauid"] = $niveauid;
            break;
        case BLOCK_EXACOMP_TYPE_DESCRIPTOR:
            $sql = 'SELECT i.*, ie.status, ie.teachervalue, ie.studentvalue, topic.title as topictitle, subj.title as subjecttitle, topic.id as topicid, subj.id as subjectid
              FROM {block_exacompdescriptors} d
                JOIN {' . BLOCK_EXACOMP_DB_ITEM_MM . '} ie ON ie.exacomp_record_id = d.id
                JOIN {block_exaportitem} i ON ie.itemid = i.id
                JOIN {block_exacompdescrtopic_mm} desctop ON desctop.descrid = d.id
                JOIN {block_exacomptopics} topic ON topic.id = desctop.topicid
                JOIN {block_exacompsubjects} subj ON topic.subjid = subj.id
              WHERE i.userid = :userid
                ' . $compidCondition . '
                ' . $statusCondition . '
                ' . $courseidCondition . '
                ' . $niveauConditionD . '
                AND ie.competence_type = :comptype
                AND (i.name LIKE :searchname OR i.intro LIKE :searchintro)
              ORDER BY ie.timecreated DESC';
            $params["userid"] = $userid;
            $params["compid"] = $compid;
            $params["comptype"] = $comptype;
            $params["searchname"] = "%" . $search . "%";
            $params["searchintro"] = "%" . $search . "%";
            $params["niveauid"] = $niveauid;
            break;
        case BLOCK_EXACOMP_TYPE_TOPIC: // topics AND descriptors of those topics
            $sql = 'SELECT i.*, ie.status, ie.teachervalue, ie.studentvalue, d.title as topictitle, subj.title as subjecttitle, d.id as topicid, subj.id as subjectid
              FROM {block_exacomptopics} d

                JOIN {block_exacompdescrtopic_mm} desctop ON desctop.topicid = d.id
                JOIN {block_exacompdescriptors} descr ON descr.id = desctop.descrid

                JOIN {' . BLOCK_EXACOMP_DB_ITEM_MM . '} ie ON (ie.exacomp_record_id = d.id OR ie.exacomp_record_id = descr.id)
                JOIN {block_exaportitem} i ON ie.itemid = i.id
                JOIN {block_exacompsubjects} subj ON d.subjid = subj.id
              WHERE i.userid = :userid
                ' . $compidCondition . '
                ' . $statusCondition . '
                ' . $courseidCondition . '
                ' . $niveauCondition . '
                AND (ie.competence_type = :comptype OR ie.competence_type = ' . BLOCK_EXACOMP_TYPE_DESCRIPTOR . ')
                AND (i.name LIKE :searchname OR i.intro LIKE :searchintro)
              ORDER BY ie.timecreated DESC';
            $params["userid"] = $userid;
            $params["compid"] = $compid;
            $params["comptype"] = $comptype;
            $params["searchname"] = "%" . $search . "%";
            $params["searchintro"] = "%" . $search . "%";
            $params["niveauid"] = $niveauid;
            break;
        case -1: // same as for subject, since this contains ALL items
            $comptype = BLOCK_EXACOMP_TYPE_SUBJECT;
        case BLOCK_EXACOMP_TYPE_SUBJECT: // TODO: Only of subject, or also of topics beneath?  for now: also of topics beneath
            $sql = 'SELECT i.*, ie.status, ie.teachervalue, ie.studentvalue, topic.title as topictitle, d.title as subjecttitle, topic.id as topicid, d.id as subjectid
              FROM {block_exacompsubjects} d
                JOIN {block_exacomptopics} topic ON topic.subjid = d.id

                JOIN {block_exacompdescrtopic_mm} desctop ON desctop.topicid = topic.id
                JOIN {block_exacompdescriptors} descr ON descr.id = desctop.descrid

                JOIN {' . BLOCK_EXACOMP_DB_ITEM_MM . '} ie ON (ie.exacomp_record_id = d.id OR ie.exacomp_record_id = topic.id OR ie.exacomp_record_id = descr.id)
                JOIN {block_exaportitem} i ON ie.itemid = i.id
              WHERE i.userid = :userid
                ' . $compidCondition . '
                ' . $statusCondition . '
                ' . $courseidCondition . '
                ' . $niveauCondition . '
                AND (ie.competence_type = :comptype OR ie.competence_type = ' . BLOCK_EXACOMP_TYPE_TOPIC . ' OR ie.competence_type = ' . BLOCK_EXACOMP_TYPE_DESCRIPTOR . ')
                AND (i.name LIKE :searchname OR i.intro LIKE :searchintro)
              ORDER BY ie.timecreated DESC';
            $params["userid"] = $userid;
            $params["compid"] = $compid;
            $params["comptype"] = $comptype;
            $params["searchname"] = "%" . $search . "%";
            $params["searchintro"] = "%" . $search . "%";
            $params["niveauid"] = $niveauid;
            break;
    }

    // some parameters will not be needed, but this does not matter, since they have been named
    $items = $DB->get_records_sql($sql, $params);

    foreach ($items as $item) {
        $collaborators = $DB->get_records(BLOCK_EXACOMP_DB_ITEM_COLLABORATOR_MM, array('itemid' => $item->id));
        $item->collaborators = [];

        foreach ($collaborators as $collaborator) {
            $student = g::$DB->get_record('user', array(
                'id' => $collaborator->userid,
            ));
            if (!$student) {
                continue;
            }

            $userpicture = new user_picture($student);
            $userpicture->size = 1; // Size f1.

            $item->collaborators[] = (object)[
                'userid' => $student->id,
                'fullname' => fullname($student),
                'profileimageurl' => $userpicture->get_url(g::$PAGE)->out(false),
            ];
        }
    }

    return $items;
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
        if (isset($_SESSION['studentid-' . g::$COURSE->id])) {
            $studentid = $_SESSION['studentid-' . g::$COURSE->id];
        } else {
            $studentid = BLOCK_EXACOMP_SHOW_ALL_STUDENTS;
        }
    } else {
        $_SESSION['studentid-' . g::$COURSE->id] = $studentid;
    }

    return $studentid;
}

/**
 * get message icon for communication between students and teacher
 *
 * @param unknown $userid
 */
function block_exacomp_get_message_icon($userid) {
    global $DB, $CFG, $COURSE;

    if ($userid != BLOCK_EXACOMP_SHOW_ALL_STUDENTS) {
        require_once($CFG->dirroot . '/message/lib.php');

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
            $anchortagcontents = '<img class="iconsmall" src="' . $OUTPUT->pix_url('t/message') . '" alt="' . get_string('messageselectadd') . '" />';
            $anchorurl = new moodle_url('/message/index.php', array('id' => $user->id));
            $anchortag = html_writer::link($anchorurl, $anchortagcontents,
                array('title' => get_string('messageselectadd')));

            $this->content->text .= '<div class="message">' . $anchortag . '</div>';
        }
    } else {
        $attributes = array(
            'exa-type' => 'iframe-popup',
            'href' => new moodle_url('message_to_course.php', array('courseid' => $COURSE->id)),
            'exa-width' => '340px',
            'exa-height' => '340px',
            'class' => 'btn btn-default',
        );

        return html_writer::tag('button',
            html_writer::img(new moodle_url('/blocks/exacomp/pix/envelope.png'), block_exacomp_get_string('message', 'message'), array('title' => block_exacomp_get_string('messagetocourse'))),
            $attributes);
    }
}

/**
 * send notification to user
 *
 * @param string $notificationtype
 * @param object|int $userfrom
 * @param object|int $userto
 * @param string $subject
 * @param string $message
 * @param string $context
 * @param string $contexturl
 * @param bool $dakoramessage if true this comes from a webservice from dakora and should ignore the setting "exacomp | notifications" that allowes/dissalowes notifications
 * @param int $courseid required for message_sent
 */
function block_exacomp_send_notification($notificationtype, $userfrom, $userto, $subject, $message, $context, $contexturl = null, $dakoramessage = false, $courseid = 0, $customdata = null) {
    global $CFG, $DB;

    if (!get_config('exacomp', 'notifications') && !$dakoramessage) {
        return;
    }

    // do not send too many notifications. therefore check if user has got same notification within the last 5 minutes
    // 	if ($DB->get_records_select('message_read', "useridfrom = ? AND useridto = ? AND contexturl = ? AND fullmessage = ? AND timecreated > ?",
    // 	    array('useridfrom' => $userfrom->id, 'useridto' => $userto->id, 'contexturl' => $contexturl,'fullmessage' => $message, (time() - 5 * 60)))
    // 	) {
    // 		return;
    // 	}

    require_once($CFG->dirroot . '/message/lib.php');

    //if ((float)$CFG->version >= 2018120300){ //bigger than 3.6
    if ((float)$CFG->version >= 2016102700) { //bigger than 3.2dev (Build: 20161027)
        $eventdata = new core\message\message(); // works but is it inteded like that? RW TODO
    } else {
        $eventdata = new stdClass ();
    }

    $eventdata->modulename = 'block_exacomp';
    $eventdata->userfrom = $userfrom;
    $eventdata->userto = $userto;
    $eventdata->fullmessage = $message;
    $eventdata->name = $notificationtype;

    if ($notificationtype == "instantmessage") {
        //        if((float)$CFG->version < 2018051700){//if lower than version 3.5
        $eventdata->subject = $subject;
        //        }
        $eventdata->notification = 0;
        $eventdata->component = "moodle";
        $eventdata->fullmessageformat = 0;
        $eventdata->courseid = $courseid; //must be integer.. probably legacy... moodle always sends "1" in Moodle3.6
        $eventdata->smallmessage = $message;
    } else {
        $eventdata->subject = $subject;
        $eventdata->fullmessageformat = FORMAT_HTML;
        $eventdata->fullmessagehtml = $message;
        $eventdata->smallmessage = $subject;
        $eventdata->component = 'block_exacomp';
        $eventdata->notification = 1;
        $eventdata->contexturl = $contexturl;
        $eventdata->contexturlname = $context;
        $eventdata->courseid = $courseid;
        if ($customdata && $CFG->version >= 2019052000) { // version must be 3.7 or higher, otherwise this field does not yet exist
            $eventdata->customdata = $customdata;
        }
    }

    @message_send($eventdata);
}

/**
 * send message:
 *
 * @param unknown $userfrom
 * @param unknown $userto
 * @param unknown $messagetext
 * @param unknown $dakoramessage if true this comes from a webservice from dakora and should ignore the setting "exacomp | notifications" that allowes/dissalowes notifications
 */
function block_exacomp_send_message($userfrom, $userto, $messagetext, $date, $time, $dakoramessage = false) {
    global $CFG, $USER, $SITE;

    $subject = "new message from " + fullname($userfrom);
    $context = "message";

    block_exacomp_send_notification("instantmessage", $userfrom, $userto, $subject, $messagetext, $context, null, $dakoramessage);
}

/**
 * send specific notification: submission made
 *
 * @param unknown $userfrom
 * @param unknown $userto
 * @param unknown $example
 * @param unknown $date
 * @param unknown $time
 * @param unknown $courseid
 */
function block_exacomp_send_submission_notification($userfrom, $userto, $example, $date, $time, $courseid, $studentcomment, $customdata) {
    global $CFG, $USER, $SITE;

    $subject = block_exacomp_get_string('notification_submission_subject_noSiteName', null, array('student' => fullname($userfrom), 'example' => $example->title));
    $subject .= "\n\r" . $studentcomment;

    $gridurl = block_exacomp_get_gridurl_for_example($courseid, $userto->id, $example->id);

    $message = block_exacomp_get_string('notification_submission_body_noSiteName', null,
        array('student' => fullname($userfrom), 'example' => $example->title, 'date' => $date, 'time' => $time, 'viewurl' => $gridurl, 'receiver' => fullname($userto)));
    $context = block_exacomp_get_string('notification_submission_context');

    if ($CFG->version >= 2019052000) { //This is the version Number for Moodle 3.7.0
        if (!$customdata) {
            $customdata = [
                'exampleid' => $example->id,
            ];
        }
        block_exacomp_send_notification("submission", $userfrom, $userto, $subject, $message, $context, $gridurl, false, 0 /* kA wieso hier keine courseid --danielp */, $customdata);
    } else {
        block_exacomp_send_notification("submission", $userfrom, $userto, $subject, $message, $context, $gridurl, false, 0, $customdata /* kA wieso hier keine courseid --danielp */);
    }

}

/**
 * send specific notification to all course teachers: submission made
 *
 * @param unknown $courseid
 * @param unknown $exampleid
 * @param unknown $timecreated
 */
function block_exacomp_notify_all_teachers_about_submission($courseid, $exampleid, $timecreated, $studentcomment = ' ', $customdata = '') {
    global $USER, $DB;

    $teachers = block_exacomp_get_teachers_by_course($courseid);
    if ($teachers) {
        foreach ($teachers as $teacher) {
            block_exacomp_send_submission_notification($USER, $teacher, $DB->get_record(BLOCK_EXACOMP_DB_EXAMPLES, array('id' => $exampleid)),
                date("D, d.m.Y", $timecreated), date("H:s", $timecreated), $courseid, $studentcomment, $customdata);
        }
    }
}

/**
 * send specific notification: new student evaluation available
 *
 * @param unknown $userfrom
 * @param unknown $userto
 * @param unknown $courseid
 */
function block_exacomp_send_self_assessment_notification($userfrom, $userto, $courseid, $compid, $comptype, $customdata) {
    global $SITE, $DB, $USER;

    $course = get_course($courseid);
    $subject = block_exacomp_get_string('notification_self_assessment_subject_noSiteName', null, array('course' => $course->shortname));
    $message = block_exacomp_get_string('notification_self_assessment_body_noSiteName', null, array('course' => $course->fullname, 'student' => fullname($userfrom), 'receiver' => fullname($userto)));
    $context = block_exacomp_get_string('notification_self_assessment_context');

    //	$viewurl = new moodle_url('/blocks/exacomp/assign_competencies.php', array('courseid' => $courseid));

    if ($comptype == BLOCK_EXACOMP_TYPE_DESCRIPTOR) {
        $descriptor_topic_mm = $DB->get_record(BLOCK_EXACOMP_DB_DESCTOPICS, array('descrid' => $compid));
        $topicid = $descriptor_topic_mm->topicid;
        $viewurl = new moodle_url('/blocks/exacomp/assign_competencies.php', array('courseid' => $courseid, 'topicid' => $topicid, 'descriptorid' => $compid));
    } else if ($comptype == BLOCK_EXACOMP_TYPE_TOPIC) {
        $viewurl = new moodle_url('/blocks/exacomp/assign_competencies.php', array('courseid' => $courseid, 'topicid' => $compid));
    }

    block_exacomp_send_notification("self_assessment", $userfrom, $userto, $subject, $message, $context, $viewurl, false, 0, $customdata);
}

/**
 * send specific notification to all course teachers: new student evaluation available
 *
 * @param unknown $courseid
 * @param $compid
 * @param $comptype
 */
function block_exacomp_notify_all_teachers_about_self_assessment($courseid, $compid, $comptype, $customdata) {
    global $USER, $DB;

    $teachers = block_exacomp_get_teachers_by_course($courseid);
    if ($teachers) {
        foreach ($teachers as $teacher) {
            block_exacomp_send_self_assessment_notification($USER, $teacher, $courseid, $compid, $comptype, $customdata);
        }
    }
}

/**
 * send specific notification: new evaluation available
 *
 * @param unknown $userfrom
 * @param unknown $userto
 * @param unknown $courseid
 * @param $compid
 * @param $comptype
 * @throws dml_exception
 * @throws moodle_exception
 */
function block_exacomp_send_grading_notification($userfrom, $userto, $courseid, $compid, $comptype, $customdata) {
    global $CFG, $USER, $SITE, $DB;

    $course = get_course($courseid);

    $subject = block_exacomp_get_string('notification_grading_subject_noSiteName', null, array('course' => $course->shortname));
    $message = block_exacomp_get_string('notification_grading_body_noSiteName', null, array('course' => $course->fullname, 'teacher' => fullname($userfrom), 'receiver' => fullname($userto)));
    $context = block_exacomp_get_string('notification_grading_context');

    //$courseid, $topicid, $descriptorid
    $viewurl = null;
    if ($comptype == BLOCK_EXACOMP_TYPE_DESCRIPTOR) {
        $descriptor_topic_mm = $DB->get_record(BLOCK_EXACOMP_DB_DESCTOPICS, array('descrid' => $compid));
        $topicid = $descriptor_topic_mm->topicid;
        $viewurl = new moodle_url('/blocks/exacomp/assign_competencies.php', array('courseid' => $courseid, 'topicid' => $topicid, 'descriptorid' => $compid));
    } else if ($comptype == BLOCK_EXACOMP_TYPE_TOPIC) {
        $viewurl = new moodle_url('/blocks/exacomp/assign_competencies.php', array('courseid' => $courseid, 'topicid' => $compid));
    }

    block_exacomp_send_notification("grading", $userfrom, $userto, $subject, $message, $context, $viewurl, null, null, $customdata);
}

/**
 * send specific notification to all course students: new evaluation available
 *
 * @param unknown $courseid
 * @param unknown $students
 */
// TODO: is this ever used?
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
 *
 * @param unknown $userfrom
 * @param unknown $userto
 * @param unknown $courseid
 * @param unknown $exampleid
 */
function block_exacomp_send_weekly_schedule_notification($userfrom, $userto, $courseid, $exampleid, $customdata) {
    global $CFG, $USER, $DB, $SITE;

    $course = get_course($courseid);
    $example = $DB->get_record(BLOCK_EXACOMP_DB_EXAMPLES, array('id' => $exampleid));
    $subject = block_exacomp_get_string('notification_weekly_schedule_subject_noSiteName');
    $message = block_exacomp_get_string('notification_weekly_schedule_body_noSiteName', null, array('course' => $course->fullname, 'teacher' => fullname($userfrom), 'receiver' => fullname($userto)));
    $context = block_exacomp_get_string('notification_weekly_schedule_context');

    $viewurl = new moodle_url('/blocks/exacomp/weekly_schedule.php', array('courseid' => $courseid, 'exampleid' => $exampleid));
    //$viewurl = $CFG->wwwroot.'/blocks/exacomp/weekly_schedule.php?courseid='.$courseid.'&exampleid='.$exampleid;

    block_exacomp_send_notification("weekly_schedule", $userfrom, $userto, $subject, $message, $context, $viewurl, false, $courseid, $customdata);
}

/**
 * send specific notification: new example comment
 *
 * @param unknown $userfrom
 * @param unknown $userto
 * @param unknown $courseid
 * @param unknown $exampleid
 */
function block_exacomp_send_example_comment_notification($userfrom, $userto, $courseid, $exampleid, $comment, $customdata) {
    global $CFG, $USER, $DB, $SITE;

    $course = get_course($courseid);
    $example = $DB->get_record(BLOCK_EXACOMP_DB_EXAMPLES, array('id' => $exampleid));
    $subject = block_exacomp_get_string('notification_example_comment_subject_noSiteName', null, array('example' => $example->title));
    if ($comment != "false") {
        $subject .= "\n\r" . $comment;
    }
    $message = block_exacomp_get_string('notification_example_comment_body_noSiteName', null, array('course' => $course->fullname, 'teacher' => fullname($userfrom), 'example' => $example->title, 'receiver' => fullname($userto)));
    $context = block_exacomp_get_string('notification_example_comment_context');

    $viewurl = block_exacomp_get_viewurl_for_example($userto->id, $userto->id, $example->id, $courseid);

    block_exacomp_send_notification("comment", $userfrom, $userto, $subject, $message, $context, $viewurl, null, null, $customdata);
}

/**
 * only used in backup
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

/**
 * if additional grading is activated, save note for descriptor, topic and subject,
 * former used value field is still used, note is mapped to value
 *
 * @param unknown $courseid
 * @param unknown $descriptorid
 * @param unknown $studentid
 * @param unknown $additionalinfo
 * @param unknown $comptype
 */
function block_exacomp_save_additional_grading_for_comp($courseid, $descriptorid, $studentid, $additionalinfo, $comptype = BLOCK_EXACOMP_TYPE_DESCRIPTOR, $subjectid = -1, $admingrading = false) {
    global $DB, $USER;

    if (is_string($additionalinfo)) {
        // force additional info to be stored with a dot as decimal mark
        $additionalinfo = (float)str_replace(",", ".", $additionalinfo);
    }

    switch (block_exacomp_additional_grading($comptype, $courseid)) {
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_NONE:
            return true;
            break;
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE:
            $gradelimit = block_exacomp_get_assessment_grade_limit($courseid);
            if ($additionalinfo > $gradelimit) {
                $additionalinfo = $gradelimit;
            } else if ($additionalinfo > 0 && $additionalinfo < 1.0) {
                $additionalinfo = 1.0;
            } else if ($additionalinfo <= 0) {
                $additionalinfo = null;
            }
            break;
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_VERBOSE:
            $verboses = preg_split("/(\/|,) /", block_exacomp_get_assessment_verbose_options(null, $courseid));
            $max = count($verboses);
            if ($max > 0) {
                $max -= 1;  // because it is possible zero
            }
            if ($additionalinfo > $max) {
                $additionalinfo = $max;
            }
            break;
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_POINTS:
            $pointlimit = block_exacomp_get_assessment_points_limit($courseid);
            if ($additionalinfo > $pointlimit) {
                $additionalinfo = $pointlimit;
            }
            break;
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_YESNO:
            break;
    }
    $context = context_course::instance($courseid);
    $role = block_exacomp_is_teacher($context) ? BLOCK_EXACOMP_ROLE_TEACHER : BLOCK_EXACOMP_ROLE_STUDENT;
    $value = block_exacomp\global_config::get_additionalinfo_value_mapping($additionalinfo);
    $record = block_exacomp_get_comp_eval($courseid, $role, $studentid, $comptype, $descriptorid);

    if ($additionalinfo == '' || empty($additionalinfo)) {
        $additionalinfo = null;
    }
    if ($role == BLOCK_EXACOMP_ROLE_TEACHER) {
        if ($admingrading) {
            $adminuser = get_admin();
            $reviewerid = $adminuser->id;
            $reviewerfirstname = $adminuser->firstname;
            $reviewerlastname = $adminuser->lastname;
        } else {
            $reviewerid = $USER->id;
            $reviewerfirstname = $USER->firstname;
            $reviewerlastname = $USER->lastname;
        }

        if ($record) {
            if (property_exists($record, '@interal-original-data') && $realid = @$record->{'@interal-original-data'}->id) {
                $record->id = $realid;
            }
            $record->gradingisold = 0;
            // falls sich die bewertung geÃ¤ndert hat, timestamp neu setzen
            if ($record->value != $value || $record->additionalinfo != $additionalinfo) {
                $record->timestamp = time();
            }

            $record->reviewerid = $reviewerid;
            $record->additionalinfo = $additionalinfo;
            $record->value = $value;
            if (!property_exists($record, 'gradinghistory')) {
                $record->gradinghistory = '';
            }
            $record->gradinghistory .= $reviewerfirstname . " " . $reviewerlastname . " " . date("d.m.y G:i", $record->timestamp) . ": " . $value . "<br>";

            if ($comptype == BLOCK_EXACOMP_TYPE_EXAMPLE) {
                $DB->update_record(BLOCK_EXACOMP_DB_EXAMPLEEVAL, $record);
            } else {
                $DB->update_record(BLOCK_EXACOMP_DB_COMPETENCES, $record);
            }
        } else {
            $insert = new stdClass();
            $insert->gradingisold = 0;
            $insert->compid = $descriptorid;
            $insert->userid = $studentid;
            $insert->courseid = $courseid;
            $insert->comptype = $comptype;
            $insert->role = $role;
            $insert->reviewerid = $reviewerid;
            $insert->timestamp = time();
            $insert->gradinghistory = $reviewerfirstname . " " . $reviewerlastname . " " . date("d.m.y G:i", $insert->timestamp) . ": " . $value . "<br>";

            $insert->additionalinfo = $additionalinfo;
            $insert->value = $value;
            $DB->insert_record(BLOCK_EXACOMP_DB_COMPETENCES, $insert);
        }

        if ($role == BLOCK_EXACOMP_ROLE_TEACHER) {
            //set the gradingisold flag of the parentdescriptor(if there is one) to "1"
            block_exacomp_set_descriptor_gradingisold($courseid, $descriptorid, $studentid, $role);
            if ($subjectid == -1) {
                $subject = block_exacomp_get_subject_by_descriptorid($descriptorid);
            } else {
                $subject = block_exacomp_get_subject_by_subjectid($subjectid);
            }
            if (@$subject->isglobal) {
                block_exacomp_update_globalgradings_text($descriptorid, $studentid, $comptype, $courseid);
            }
            //            block_exacomp_update_gradinghistory_text($descriptorid,$studentid,$courseid,$comptype);
        }

    }
}

/**
 * get all examples associated with any descriptors in this course
 *
 * @param unknown $courseid
 */
function block_exacomp_get_examples_by_course($courseid, $withCompetenceInfo = false, $search = "", $mindvisibility = true, $userid = -1) {
    global $USER;
    if ($userid == -1) {
        $userid = $USER->id;
    }

    $params = [];

    if ($withCompetenceInfo) { // with topics and subjects and evaluation and annotation of example (for diggrplus)
        if ($mindvisibility) {
            // Visibility of Niveaus is NOT minded. But cannot be changed in diggrplus anyways, for which this function is made
            // Student specific visibility is also NOT minded, only global
            // changed 2023-10-10: mind the desc, topic and example visibility for the actual course, and not globally
            $sql = "SELECT DISTINCT ex.*, topic.title as topictitle, topic.id as topicid, subj.title as subjecttitle, subj.id as subjectid, ct.courseid as courseid, d.niveauid, n.title as niveautitle,
                        exameval.teacher_evaluation, exameval.student_evaluation, examannot.annotationtext as annotation
            FROM {" . BLOCK_EXACOMP_DB_EXAMPLES . "} ex
            JOIN {" . BLOCK_EXACOMP_DB_DESCEXAMP . "} dex ON dex.exampid = ex.id
            JOIN {" . BLOCK_EXACOMP_DB_DESCTOPICS . "} det ON dex.descrid = det.descrid
            JOIN {" . BLOCK_EXACOMP_DB_COURSETOPICS . "} ct ON det.topicid = ct.topicid
            JOIN {" . BLOCK_EXACOMP_DB_TOPICS . "} topic ON ct.topicid = topic.id
            JOIN {" . BLOCK_EXACOMP_DB_SUBJECTS . "} subj ON topic.subjid = subj.id

            JOIN {" . BLOCK_EXACOMP_DB_DESCRIPTORS . "} d ON det.descrid=d.id
            JOIN {" . BLOCK_EXACOMP_DB_NIVEAUS . "} n ON n.id = d.niveauid

            JOIN {" . BLOCK_EXACOMP_DB_DESCVISIBILITY . "} dvis ON d.id=dvis.descrid AND dvis.courseid=:courseiddvis
            JOIN {" . BLOCK_EXACOMP_DB_TOPICVISIBILITY . "} tvis ON topic.id=tvis.topicid AND tvis.niveauid IS NULL AND tvis.courseid=:courseidtvis
            JOIN {" . BLOCK_EXACOMP_DB_EXAMPVISIBILITY . "} evis ON ex.id=evis.exampleid AND evis.courseid=:courseidevis

            LEFT JOIN {" . BLOCK_EXACOMP_DB_EXAMPLEEVAL . "} exameval ON exameval.exampleid = ex.id AND exameval.courseid = :courseidexameval AND exameval.studentid = :userid
            LEFT JOIN {" . BLOCK_EXACOMP_DB_EXAMPLE_ANNOTATION . "} examannot ON examannot.exampleid = ex.id AND examannot.courseid = :courseidexamannot
            WHERE ct.courseid = :courseid
            AND dvis.visible = 1
            AND tvis.visible = 1
            AND evis.visible = 1
            AND (ex.courseid = 0 OR ex.courseid = :courseidexample OR ex.courseid IS NULL)"
                . (!block_exacomp_is_teacher() && !block_exacomp_is_teacher($courseid, $USER->id) /*for webservice*/ ? ' AND ex.is_teacherexample = 0 ' : '') . "
            AND (ex.title LIKE :searchtitle OR ex.description LIKE :searchdescription)
            ";

            $params = ['courseiddvis' => $courseid, 'courseidtvis' => $courseid, 'courseidevis' => $courseid, "courseidexameval" => $courseid, "courseidexamannot" => $courseid, "courseidexample" => $courseid];
        } else {
            $sql = "SELECT DISTINCT ex.*, topic.title as topictitle, topic.id as topicid, subj.title as subjecttitle, subj.id as subjectid, ct.courseid as courseid, d.niveauid, n.title as niveautitle
            FROM {" . BLOCK_EXACOMP_DB_EXAMPLES . "} ex
            JOIN {" . BLOCK_EXACOMP_DB_DESCEXAMP . "} dex ON dex.exampid = ex.id
            JOIN {" . BLOCK_EXACOMP_DB_DESCTOPICS . "} det ON dex.descrid = det.descrid
            JOIN {" . BLOCK_EXACOMP_DB_COURSETOPICS . "} ct ON det.topicid = ct.topicid
            JOIN {" . BLOCK_EXACOMP_DB_TOPICS . "} topic ON ct.topicid = topic.id
            JOIN {" . BLOCK_EXACOMP_DB_SUBJECTS . "} subj ON topic.subjid = subj.id

            JOIN {" . BLOCK_EXACOMP_DB_DESCRIPTORS . "} d ON det.descrid=d.id
            JOIN {" . BLOCK_EXACOMP_DB_NIVEAUS . "} n ON n.id = d.niveauid

            WHERE ct.courseid = :courseid
            AND (ex.title LIKE :searchtitle OR ex.description LIKE :searchdescription)"
                . (!block_exacomp_is_teacher() && !block_exacomp_is_teacher($courseid, $USER->id) /*for webservice*/ ? ' AND ex.is_teacherexample = 0 ' : '') . "
            ";
        }
    } else {
        $sql = "SELECT ex.*
		FROM {" . BLOCK_EXACOMP_DB_EXAMPLES . "} ex
		WHERE ex.id IN (
			SELECT dex.exampid
                FROM {" . BLOCK_EXACOMP_DB_DESCEXAMP . "} dex
                    JOIN {" . BLOCK_EXACOMP_DB_DESCTOPICS . "} det ON dex.descrid = det.descrid
                    JOIN {" . BLOCK_EXACOMP_DB_COURSETOPICS . "} ct ON det.topicid = ct.topicid
			WHERE ct.courseid = :courseid"
            . (!block_exacomp_is_teacher() && !block_exacomp_is_teacher($courseid, $USER->id) /*for webservice*/ ? ' AND ex.is_teacherexample = 0 ' : '') . "
		)
		/* for subdescriptors */
		OR ex.id IN (
			SELECT dex.exampid
                FROM {" . BLOCK_EXACOMP_DB_DESCRIPTORS . "} d
                    JOIN {" . BLOCK_EXACOMP_DB_DESCRIPTORS . "} d2 ON d2.parentid = d.id
                    JOIN {" . BLOCK_EXACOMP_DB_DESCEXAMP . "} dex ON dex.descrid = d2.id
                    JOIN {" . BLOCK_EXACOMP_DB_DESCTOPICS . "} det ON d.id = det.descrid /* topic relation by parent descriptor */
                    JOIN {" . BLOCK_EXACOMP_DB_COURSETOPICS . "} ct ON det.topicid = ct.topicid
			WHERE ct.courseid = :courseidsub"
            . (!block_exacomp_is_teacher() && !block_exacomp_is_teacher($courseid, $USER->id) /*for webservice*/ ? ' AND ex.is_teacherexample = 0 ' : '') . "
		)

		";
    }

    return g::$DB->get_records_sql($sql, array_merge(
        [
            "courseid" => $courseid, "courseidsub" => $courseid,
            "searchtitle" => "%" . $search . "%", "searchdescription" => "%" . $search . "%", "userid" => $userid,
        ],
        $params
    ));
}

function block_exacomp_get_crosssubject_examples_by_course($courseid) {
    $sql = "SELECT ex.*
		FROM {" . BLOCK_EXACOMP_DB_EXAMPLES . "} ex
		WHERE ex.id IN (
			SELECT dex.exampid
			FROM {" . BLOCK_EXACOMP_DB_DESCEXAMP . "} dex
			JOIN {" . BLOCK_EXACOMP_DB_CROSSSUBJECTS . "} crosssub ON dex.id_foreign = crosssub.id
			WHERE crosssub.courseid = ?
		)";
    return g::$DB->get_records_sql($sql, array($courseid));
}

/**
 * check if any examples are available in course
 * needed for dropdown in weekly schedule
 *
 * @param unknown $courseid
 * @return boolean
 */
function block_exacomp_course_has_examples($courseid) {
    $sql = "SELECT COUNT(*)
		FROM {" . BLOCK_EXACOMP_DB_EXAMPLES . "} ex
		JOIN {" . BLOCK_EXACOMP_DB_DESCEXAMP . "} dex ON ex.id = dex.exampid
		JOIN {" . BLOCK_EXACOMP_DB_DESCTOPICS . "} det ON dex.descrid = det.descrid
		JOIN {" . BLOCK_EXACOMP_DB_COURSETOPICS . "} ct ON det.topicid = ct.topicid
		WHERE ct.courseid = ?";
    if ((bool)g::$DB->get_field_sql($sql, array($courseid))) {
        return true;
    }
    // check subdescriptors
    $sql = "SELECT COUNT(*)
		FROM {" . BLOCK_EXACOMP_DB_EXAMPLES . "} ex
		JOIN {" . BLOCK_EXACOMP_DB_DESCEXAMP . "} dex ON ex.id = dex.exampid
		JOIN {" . BLOCK_EXACOMP_DB_DESCRIPTORS . "} d ON d.id = dex.descrid AND d.parentid > 0
		JOIN {" . BLOCK_EXACOMP_DB_DESCRIPTORS . "} d2 ON d2.id = d.parentid
		JOIN {" . BLOCK_EXACOMP_DB_DESCTOPICS . "} det ON det.descrid = d2.id
		JOIN {" . BLOCK_EXACOMP_DB_COURSETOPICS . "} ct ON det.topicid = ct.topicid
		WHERE ct.courseid = ?";
    return (bool)g::$DB->get_field_sql($sql, array($courseid));
}

/**
 * send message to all students in course
 *
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
 *
 * @param integer $courseid
 * @param string $title
 * @param string $description
 * @param string $timeframe
 * @param string $externalurl
 * @param integer $creatorid
 * @param integer $studentid
 */
function block_exacomp_create_blocking_event($courseid, $title, $description, $timeframe, $externalurl, $creatorid, $studentid) {
    global $DB;

    $example = new stdClass();
    $example->title = $title;
    $example->description = $description;
    $example->timeframe = $timeframe;
    $example->externalurl = $externalurl;
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

        $DB->insert_record(BLOCK_EXACOMP_DB_EXAMPVISIBILITY, $visibility);
    }

    return $scheduleid;

}

/**
 * create an event imported from an ICS file
 *
 * @param unknown $courseid
 * @param unknown $title
 * @param unknown $creatorid
 * @param unknown $studentid
 */
function block_exacomp_create_background_event($courseid, $title, $creatorid, $studentid) {
    global $DB;

    $example = new stdClass();
    $example->title = $title;
    $example->creatorid = $creatorid;
    $example->blocking_event = 3;

    $exampleid = $DB->insert_record(BLOCK_EXACOMP_DB_EXAMPLES, $example);

    $schedule = new stdClass();
    $schedule->studentid = $studentid;
    $schedule->exampleid = $exampleid;
    $schedule->creatorid = $creatorid;
    $schedule->courseid = $courseid;

    $record = $DB->get_records(BLOCK_EXACOMP_DB_EXAMPVISIBILITY, array('courseid' => $courseid, 'exampleid' => $exampleid, 'studentid' => 0, 'visible' => 1));
    if (!$record) {
        $visibility = new stdClass();
        $visibility->courseid = $courseid;
        $visibility->exampleid = $exampleid;
        $visibility->studentid = 0;
        $visibility->visible = 1;

        $vibilityid = $DB->insert_record(BLOCK_EXACOMP_DB_EXAMPVISIBILITY, $visibility);
    }

    return $exampleid;
}

/**
 * needed to create example over webservice API
 *
 * @param unknown $descriptorid
 */
function block_exacomp_get_courseids_by_descriptor($descriptorid) {
    $sql = 'SELECT ct.courseid
		FROM {' . BLOCK_EXACOMP_DB_COURSETOPICS . '} ct
		JOIN {' . BLOCK_EXACOMP_DB_DESCTOPICS . '} dt ON ct.topicid = dt.topicid
		WHERE dt.descrid = ?';

    return g::$DB->get_fieldset_sql($sql, array($descriptorid));
}

/**
 * needed to create example over webservice API
 *
 * @param unknown $topicid
 */
function block_exacomp_get_courseids_by_topic($topicid) {
    $sql = 'SELECT ct.courseid
		FROM {' . BLOCK_EXACOMP_DB_COURSETOPICS . '} ct
		WHERE ct.topicid = ?';

    return g::$DB->get_fieldset_sql($sql, array($topicid));
}

function block_exacomp_get_courseids_by_subject($subjid) {
    $sql = 'SELECT ct.courseid
		FROM {' . BLOCK_EXACOMP_DB_TOPICS . '} t
		JOIN {' . BLOCK_EXACOMP_DB_COURSETOPICS . '} ct ON ct.topicid = t.id
		WHERE t.subjid = ?';

    return g::$DB->get_fieldset_sql($sql, array($subjid));
}

function block_exacomp_get_courseids_by_example($exampleid) {
    $sql = 'SELECT ct.courseid
		FROM {' . BLOCK_EXACOMP_DB_COURSETOPICS . '} ct
		JOIN {' . BLOCK_EXACOMP_DB_DESCTOPICS . '} dt ON ct.topicid = dt.topicid
		JOIN {' . BLOCK_EXACOMP_DB_DESCEXAMP . '} dex ON dex.descrid = dt.descrid
		WHERE dex.exampid=?';
    $courseIds = g::$DB->get_fieldset_sql($sql, array($exampleid));
    // add subdescriptors
    $sql2 = 'SELECT ct.courseid
		FROM {' . BLOCK_EXACOMP_DB_COURSETOPICS . '} ct
		JOIN {' . BLOCK_EXACOMP_DB_DESCTOPICS . '} dt ON ct.topicid = dt.topicid
		JOIN {' . BLOCK_EXACOMP_DB_DESCRIPTORS . '} d ON dt.descrid = d.id AND d.parentid = 0
		JOIN {' . BLOCK_EXACOMP_DB_DESCRIPTORS . '} d2 ON d2.parentid = d.id
		JOIN {' . BLOCK_EXACOMP_DB_DESCEXAMP . '} dex ON dex.descrid = d2.id
		WHERE dex.exampid=?';
    if ($courseIdsSub = g::$DB->get_fieldset_sql($sql2, array($exampleid))) {
        $courseIds += $courseIdsSub;
    }
    return $courseIds;
}

/**
 * get evaluation images for competence profile for teacher
 * according to course scheme and admin scheme
 **/
function block_exacomp_get_html_for_niveau_eval($evaluation) {
    //$evaluation_niveau_type = block_exacomp_evaluation_niveau_type();
    $evaluation_niveau_types = block_exacomp_get_assessment_diffLevel_options();
    if (!$evaluation_niveau_types) {
        return;
    }
    $evaluation_niveau_types = explode(',', $evaluation_niveau_types);
    $evaluation_niveau_type = 0; //default
    if (array_key_exists($evaluation, $evaluation_niveau_types)) {
        $evaluation_niveau_type = $evaluation_niveau_types[$evaluation];
    }

    //predefined pictures
    $grey_1_src = '/blocks/exacomp/pix/compprof_rating_teacher_grey_1_' . $evaluation_niveau_type . '.png';
    $grey_2_src = '/blocks/exacomp/pix/compprof_rating_teacher_grey_2_' . $evaluation_niveau_type . '.png';
    $grey_3_src = '/blocks/exacomp/pix/compprof_rating_teacher_grey_3_' . $evaluation_niveau_type . '.png';
    $one_src = '/blocks/exacomp/pix/compprof_rating_teacher_1_' . $evaluation_niveau_type . '.png';
    $two_src = '/blocks/exacomp/pix/compprof_rating_teacher_2_' . $evaluation_niveau_type . '.png';
    $three_src = '/blocks/exacomp/pix/compprof_rating_teacher_3_' . $evaluation_niveau_type . '.png';

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

    return html_writer::empty_tag('img', array('src' => new moodle_url($image1), 'width' => '25', 'height' => '25')) .
        html_writer::empty_tag('img', array('src' => new moodle_url($image2), 'width' => '25', 'height' => '25')) .
        html_writer::empty_tag('img', array('src' => new moodle_url($image3), 'width' => '25', 'height' => '25'));
}

/**
 * get data for grid table in profile, where topics are listed on vertical axis, niveaus on horizontal
 * and each cell represent descriptor evaluation
 * additionally topic and subject evaluation is also included
 * if crosssubj is passed, then only the selection of topics that is included in this crosssubject should be returned
 *
 * @param unknown $courseid
 * @param unknown $studentid
 * @param unknown $subjectid
 * @param unknown $crosssubj
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
 *       span = 1 or 0 inidication if niveau is across (Ã¼bergreifend)
 */
function block_exacomp_get_grid_for_competence_profile($courseid, $studentid, $subjectid, $crosssubj = null) {
    global $DB;
    list($course_subjects, $table_column, $table_header, $selectedSubject, $selectedTopic, $selectedNiveau) =
        block_exacomp_init_overview_data($courseid, $subjectid, BLOCK_EXACOMP_SHOW_ALL_TOPICS, 0, false, block_exacomp_is_teacher(), $studentid, false, false, $crosssubj);

    $user = $DB->get_record('user', array('id' => $studentid));
    $user = block_exacomp_get_user_information_by_course($user, $courseid);

    $subject = block_exacomp\db_layer_student::create($courseid)->get_subject($subjectid);

    if (!$subject) {
        return;
    }
    if (!$subject->topics) {
        $subject->topics = [];
    }

    block_exacomp_sort_items($subject->topics, BLOCK_EXACOMP_DB_TOPICS);

    $table_content = new stdClass();
    $table_content->content = array();

    $use_evalniveau = block_exacomp_use_eval_niveau($courseid);
    $scheme_items = \block_exacomp\global_config::get_teacher_eval_items($courseid);
    $evaluationniveau_items = \block_exacomp\global_config::get_evalniveaus(null, $courseid);

    //If crosssubject then only get those topics where a descriptor has been added
    if ($crosssubj) {
        $subject->topics = block_exacomp_clear_topics_for_crosssubject($subject->topics, $courseid, $crosssubj);
    }

    if (is_array($subject->topics)) {
        foreach ($subject->topics as $topic) {
            // auswertung pro lfs
            $data = $table_content->content[$topic->id] = block_exacomp_get_grid_for_competence_profile_topic_data($courseid, $studentid, $topic, $crosssubj);

            // gesamt for topic
            $data->topic_evalniveauid =
                (($use_evalniveau) ?
                    ((isset($user->topics->niveau[$topic->id]))
                        ? $user->topics->niveau[$topic->id] : -1)
                    : 0);

            $data->topic_evalniveau = @$evaluationniveau_items[$data->topic_evalniveauid] ?: '';

            //auswirkung auf total im kompetenzprofil
            // 		$data->topic_eval =
            // 			((block_exacomp_additional_grading(BLOCK_EXACOMP_TYPE_TOPIC)) ?
            // 				((isset($user->topics->teacher_additional_grading[$topic->id]))
            // 					? $user->topics->teacher_additional_grading[$topic->id] : '')
            // 				: ((isset($user->topics->teacher[$topic->id]))
            // 					? $scheme_items[$user->topics->teacher[$topic->id]] : '-1'));
            $data->topic_eval =
                ((block_exacomp_additional_grading(BLOCK_EXACOMP_TYPE_TOPIC, $courseid) == BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE) ?
                    ((isset($user->topics->teacher_additional_grading[$topic->id]))
                        ? $user->topics->teacher_additional_grading[$topic->id] : '')
                    : ((isset($user->topics->teacher[$topic->id]))
                        ? $user->topics->teacher[$topic->id] : '-1')); // $scheme_items[$user->topics->teacher[$topic->id]] would deliver the text... float expected
            //$data->topic_eval = 2;

            $data->topic_selfeval = 123;

            $data->visible = block_exacomp_is_topic_visible($courseid, $topic, $studentid);
            $data->timestamp = ((isset($user->topics->timestamp_teacher[$topic->id])) ? $user->topics->timestamp_teacher[$topic->id] : 0);
            $data->topic_id = $topic->id;
        }
    }

    $table_content->subject_evalniveau =
        (($use_evalniveau) ?
            ((property_exists($subject, 'id') && isset($user->subjects->niveau[$subject->id]))
                ? @$evaluationniveau_items[$user->subjects->niveau[$subject->id]] . ' ' : '')
            : '');

    $table_content->subject_evalniveauid = (($use_evalniveau) ?
        ((property_exists($subject, 'id') && isset($user->subjects->niveau[$subject->id]))
            ? $user->subjects->niveau[$subject->id] : -1)
        : 0);

    $table_content->subject_eval = ((block_exacomp_additional_grading(BLOCK_EXACOMP_TYPE_SUBJECT, $courseid) == BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE) ?
        ((property_exists($subject, 'id') && isset($user->subjects->teacher_additional_grading[$subject->id]))
            ? $user->subjects->teacher_additional_grading[$subject->id] : '')
        : ((isset($user->subjects->teacher[$subject->id]))
            ? $user->subjects->teacher[$subject->id] : '')); //$scheme_items[$user->subjects->teacher[$subject->id]] wÃ¤re der String

    $table_content->timestamp = (property_exists($subject, 'id') && isset($user->subjects->timestamp_teacher[$subject->id]))
        ? $user->subjects->timestamp_teacher[$subject->id] : '';

    $table_content->subject_title = property_exists($subject, 'title') ? $subject->title : '';

    foreach ($table_header as $key => $niveau) {
        $niveaukey = /*$niveau->numb.'-'.$niveau->sorting.'-'.*/
            $niveau->title;
        //        $niveaukey = (property_exists($niveau, 'numb') ? $niveau->numb : 0).'-'.(property_exists($niveau, 'sorting') ? $niveau->sorting : 0).'-'.$niveau->title;
        if (isset($niveau->span) && $niveau->span == 1) {
            unset($table_header[$key]);
        } else if ($niveau->id != BLOCK_EXACOMP_SHOW_ALL_NIVEAUS) {
            foreach ($table_content->content as $row) {
                if ($row->span != 1) {
                    if (!array_key_exists($niveaukey, $row->niveaus)) { //Hier werden die Total bewertungen von den Topics Ã¼berschrieben!
                        $row->niveaus[$niveaukey] = new stdClass();
                        $row->niveaus[$niveaukey]->eval = '';
                        $row->niveaus[$niveaukey]->evalniveau = '';
                        $row->niveaus[$niveaukey]->evalniveauid = ($use_evalniveau) ? -1 : 0;
                        $row->niveaus[$niveaukey]->show = false;
                        $row->niveaus[$niveaukey]->visible = true;
                        $row->niveaus[$niveaukey]->timestamp = 0;
                    }
                }
            }
        }
    }

    // niveaus sorting: numb, sorting
    // sorted in previous function. TODO: right?
    // sorting by table header
    $headernames = array_map(function($n) {
        return $n->title;
    }, $table_header);
    unset($headernames[BLOCK_EXACOMP_SHOW_ALL_NIVEAUS]);
    foreach ($table_content->content as $row) {
        $row->niveaus = array_replace(array_flip($headernames), $row->niveaus);
        //		ksort($row->niveaus);
    }

    return array($course_subjects, $table_column, $table_header, $table_content);
}

/**
 * @param $courseid
 * @param $studentid
 * @param \block_exacomp\topic $topic
 * @return object
 */
function block_exacomp_get_grid_for_competence_profile_topic_data($courseid, $studentid, $topic, $crosssubj = null) {
    $data = (object)[];
    $data->niveaus = array();
    $data->span = @$topic->span ? $topic->span : 0;

    $use_evalniveau = block_exacomp_use_eval_niveau($courseid);
    $evaluationniveau_items = \block_exacomp\global_config::get_evalniveaus(null, $courseid);

    $niveausAvgsCalc = array();
    $evalniveauAvgsCalc = array();
    $niveausAvgsSelfCalc = array();

    if ($crosssubj) {
        $descriptorsOfCrosssubj = block_exacomp_get_descriptors_for_cross_subject($courseid, $crosssubj->id);
        $niveausAlreadyCheckedForShowing = array();
    }

    //$topic->descriptors are only PARENTdescriptors, which in turn have childdescriptors in the structure
    foreach ($topic->descriptors as $descriptor) {


        $evaluation = block_exacomp_get_comp_eval($courseid, BLOCK_EXACOMP_ROLE_TEACHER, $studentid, BLOCK_EXACOMP_TYPE_DESCRIPTOR, $descriptor->id);
        $student_evaluation = block_exacomp_get_comp_eval($courseid, BLOCK_EXACOMP_ROLE_STUDENT, $studentid, BLOCK_EXACOMP_TYPE_DESCRIPTOR, $descriptor->id);
        $niveau = \block_exacomp\niveau::get($descriptor->niveauid);
        if (!$niveau) {
            continue;
        }

        if (!key_exists($niveau->title, $niveausAvgsCalc)) {
            $niveausAvgsCalc[$niveau->title] = array();
            $evalniveauAvgsCalc[$niveau->title] = array();
            $niveausAvgsSelfCalc[$niveau->title] = array();
        }

        $data->niveaus[$niveau->title] = new stdClass();

        //dont display niveaus that are not used in this crosssubject
        $useEval = true;
        if ($crosssubj) {
            if (isset($descriptorsOfCrosssubj[$descriptor->id])) {
                //ok
            } else {
                $useEval = false;
            }
        }

        if ($use_evalniveau && $evaluation && $evaluation->evalniveauid) {
            $v = $evaluation->evalniveauid;
            if ($v > -1 && $useEval) { // add only evaluated values
                $evalniveauAvgsCalc[$niveau->title][] = $v;
            }
        }

        if ((block_exacomp_additional_grading(BLOCK_EXACOMP_TYPE_DESCRIPTOR, $courseid) == BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE)) {
            $v = (($evaluation && $evaluation->additionalinfo) ? $evaluation->additionalinfo : '');
            if ($v && $useEval) { // add only evaluated values
                $niveausAvgsCalc[$niveau->title][] = $v;
            }
        } else {
            $v = (($evaluation && $evaluation->value || ($evaluation && $evaluation->value == "0")) ? $evaluation->value : -1);
            if ($v > -1 && $useEval) { // add only evaluated values
                $niveausAvgsCalc[$niveau->title][] = $v;
            }
        };

        if (block_exacomp_get_assessment_comp_SelfEval($courseid) && $student_evaluation) {
            if ($student_evaluation->value && $useEval) { // add only evaluated values
                $niveausAvgsSelfCalc[$niveau->title][] = $student_evaluation->value;
            }
        }

        $data->niveaus[$niveau->title]->show = true;
        $data->niveaus[$niveau->title]->visible = block_exacomp_is_descriptor_visible($courseid, $descriptor, $studentid);
        $data->niveaus[$niveau->title]->timestamp = ((isset($evaluation->timestamp)) ? $evaluation->timestamp : 0);

        $data->niveaus[$niveau->title]->gradingisold = block_exacomp_is_descriptor_grading_old($descriptor->id, $studentid);
        if ($niveau->span == 1) { // deprecated, but needed for support old installations
            $data->span = 1;
        }

        // Hide niveaus that are not used in the crosssubject
        if ($crosssubj) {
            if (isset($descriptorsOfCrosssubj[$descriptor->id])) {
                //ok
                $niveausAlreadyCheckedForShowing[$niveau->title] = true;
            } else {
                if (!array_key_exists($niveau->title, $niveausAlreadyCheckedForShowing) || !$niveausAlreadyCheckedForShowing[$niveau->title]) {
                    $data->niveaus[$niveau->title]->show = false;
                }
            }
        }

    }

    // get averages for descriptors
    if (count($niveausAvgsCalc) > 0) {
        foreach ($niveausAvgsCalc as $nTitle => $nValues) {
            if (count($nValues) > 0) {
                $data->niveaus[$nTitle]->eval = round(array_sum($nValues) / count($nValues), 1);
            } else {
                $data->niveaus[$nTitle]->eval =
                    (block_exacomp_additional_grading(BLOCK_EXACOMP_TYPE_DESCRIPTOR, $courseid) == BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE) ? '' : '-1';
            }
        }
    }
    if (count($evalniveauAvgsCalc) > 0) {
        foreach ($evalniveauAvgsCalc as $nTitle => $nValues) {
            if (count($nValues) > 0) {
                $evalniveauid = round(array_sum($nValues) / count($nValues));
                $data->niveaus[$nTitle]->evalniveauid = $evalniveauid;
                $data->niveaus[$nTitle]->evalniveau = @$evaluationniveau_items[$evalniveauid];
            } else {
                $data->niveaus[$nTitle]->evalniveauid = -1;
                $data->niveaus[$nTitle]->evalniveau = '';
            }
        }
    }
    if (count($niveausAvgsSelfCalc) > 0) {
        foreach ($niveausAvgsSelfCalc as $nTitle => $nValues) {
            if (count($nValues) > 0) {
                $evalid = round(array_sum($nValues) / count($nValues));
                $data->niveaus[$nTitle]->self_evalid = $evalid;
                $data->niveaus[$nTitle]->self_eval = block_exacomp\global_config::get_student_eval_title_by_id($evalid, BLOCK_EXACOMP_TYPE_DESCRIPTOR, $courseid);
            } else {
                $data->niveaus[$nTitle]->self_evalid = -1;
                $data->niveaus[$nTitle]->self_eval = '';
            }
        }
    }

    return $data;
}

/**
 * format data to access via WS
 *
 * @param integer $courseid
 * @param integer $userid
 * @param integer $subjectid
 * @param integer $targetrole
 * @param array $custom_data
 * @return \block_exacomp\stdClass
 * see ws dakora_get_competence_grid_for_profile for return value description
 */
function block_exacomp_get_competence_profile_grid_for_ws($courseid, $userid, $subjectid, $targetrole = BLOCK_EXACOMP_ROLE_TEACHER, $custom_data = null, $crosssubj = null) {
    global $DB;
    static $subjectGenericData = null;
    if ($subjectGenericData === null) {
        $subjectGenericData = array();
    }
    if ($courseid !== null && $subjectid !== null) {
        $subjectData = $DB->get_record(BLOCK_EXACOMP_DB_SUBJECTS, ['id' => $subjectid]);
        list ($course_subjects, $table_rows, $table_header, $table_content) =
            block_exacomp_get_grid_for_competence_profile($courseid, $userid, $subjectid, $crosssubj);

        // aggregate all data to next generation of global report
        if (@$subjectData->isglobal) { // only isglobal?
            if (!array_key_exists($subjectid, $subjectGenericData)) {
                $subjectGenericData[$subjectid] = array(
                    'table_rows' => $table_rows,
                    'table_header' => $table_header,
                    'courses_table_content' => [],
                );
            }
            if (!array_key_exists($courseid, $subjectGenericData[$subjectid]['courses_table_content'])) {
                $subjectGenericData[$subjectid]['courses_table_content'][$courseid] = $table_content;
            }
        }
    } else if ($custom_data != null) {
        // use manual generated data (averages)
        list ($table_rows, $table_header, $table_content) = $custom_data;
    } else {
        // if no courseID - return ALL data of sybjects by courses
        return $subjectGenericData;
    }
    //list ($course_subjects, $table_rows, $table_header, $table_content) = block_exacomp_get_grid_for_competence_profile($courseid, $userid, $subjectid);

    $spanning_niveaus = $DB->get_fieldset_select(BLOCK_EXACOMP_DB_NIVEAUS, 'title', 'span=?', array(1));
    //calculate the col span for spanning niveaus
    $spanning_colspan = block_exacomp_calculate_spanning_niveau_colspan($table_header, $spanning_niveaus);

    $subjectData = $DB->get_record(BLOCK_EXACOMP_DB_SUBJECTS, ['id' => $subjectid]);

    $table = new stdClass();
    $table->title = $subjectData->title;
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

    if (block_exacomp_is_topicgrading_enabled($courseid)) {
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
        $content_row->columns[0]->text = block_exacomp_get_topic_numbering($topic) . " " . $table_rows[$topic]->title;
        $content_row->columns[0]->span = 0;
        $content_row->columns[0]->visible = $rowcontent->visible;

        $current_idx = 1;
        foreach ($rowcontent->niveaus as $niveau => $element) { //DESCRIPTORS
            $cellContent = new stdClass();

            //$grading = block_exacomp_get_comp_eval($courseid, BLOCK_EXACOMP_ROLE_TEACHER, $userid, BLOCK_EXACOMP_TYPE_DESCRIPTOR, $element->descriptorid);
            //$content_row->columns[$current_idx]->evaluation = ($grading->value !== null) ? $grading->value : -1;

            switch ($targetrole) {
                case BLOCK_EXACOMP_ROLE_TEACHER:
                    $cellContent->evaluation = (empty($element->eval) && $element->eval != '0') ? -1 : $element->eval;
                    $cellContent->evaluation_mapped = \block_exacomp\global_config::get_additionalinfo_value_mapping($element->eval);
                    $cellContent->evalniveauid = $element->evalniveauid;
                    break;
                case BLOCK_EXACOMP_ROLE_STUDENT:
                    $cellContent->evaluation_text = ($element->self_eval ? $element->self_eval : '');
                    $eval_val = ($element->self_evalid && $element->self_evalid > -1 ? $element->self_evalid : -1);
                    $cellContent->evaluation = $eval_val;
                    $cellContent->evaluation_mapped = $eval_val;
                    $cellContent->evalniveauid = -1;
                    break;
            }
            $cellContent->show = $element->show;
            $cellContent->visible = ((!$element->visible || !$rowcontent->visible) ? false : true);
            $cellContent->timestamp = (int)$element->timestamp;

            $cellContent->gradingisold = (bool)$element->gradingisold;

            if (in_array($niveau, $spanning_niveaus)) {
                $cellContent->span = $spanning_colspan;
            } else {
                $cellContent->span = 0;
            }
            $content_row->columns[$current_idx] = $cellContent;
            $current_idx++;
        }

        if (block_exacomp_is_topicgrading_enabled($courseid)) { //TOPICS
            $topic_eval = new stdClass();
            switch ($targetrole) {
                case BLOCK_EXACOMP_ROLE_TEACHER:
                    $topic_eval->evaluation_text = \block_exacomp\global_config::get_teacher_eval_title_by_id(\block_exacomp\global_config::get_additionalinfo_value_mapping($rowcontent->topic_eval));
                    $topic_eval->evaluation = (empty($rowcontent->topic_eval) && $rowcontent->topic_eval != '0') ? -1 : $rowcontent->topic_eval;
                    //$topic_eval->evaluation = (empty($rowcontent->topic_eval)|| strlen(trim($element->eval)) == 0) ? -1 : $rowcontent->topic_eval;   old
                    $topic_eval->evaluation_mapped = \block_exacomp\global_config::get_additionalinfo_value_mapping($rowcontent->topic_eval);
                    $topic_eval->evalniveauid = $rowcontent->topic_evalniveauid;
                    break;
                case BLOCK_EXACOMP_ROLE_STUDENT:
                    $student_evaluation = block_exacomp_get_comp_eval($courseid, BLOCK_EXACOMP_ROLE_STUDENT, $userid, BLOCK_EXACOMP_TYPE_TOPIC, $rowcontent->topic_id);
                    if (block_exacomp_get_assessment_topic_SelfEval($courseid) && $student_evaluation) {
                        $topic_eval->evaluation_text = $student_evaluation->get_value_title();
                        $topic_eval->evaluation = $student_evaluation->value;
                        $topic_eval->evaluation_mapped = $student_evaluation->value;
                    } else {
                        $topic_eval->evaluation_text = '';
                        $topic_eval->evaluation = -1;
                        $topic_eval->evaluation_mapped = -1;
                    }
                    $topic_eval->evalniveauid = -1;
                    break;
            }
            $topic_eval->topicid = $rowcontent->topic_id;
            $topic_eval->span = 0;
            $topic_eval->visible = $rowcontent->visible;
            $topic_eval->timestamp = (int)$rowcontent->timestamp;
            $content_row->columns[$current_idx] = $topic_eval;
        }

        $table->rows[] = $content_row;
    }

    if (block_exacomp_is_subjectgrading_enabled($courseid)) {       //SUBJECT
        $content_row = new stdClass();
        $content_row->columns = array();

        $content_row->columns[0] = new stdClass();
        $content_row->columns[0]->text = block_exacomp_get_string('total');
        $content_row->columns[0]->span = count($table_header);

        $content_row->columns[1] = new stdClass();
        switch ($targetrole) {
            case BLOCK_EXACOMP_ROLE_TEACHER:
                $content_row->columns[1]->evaluation = (empty($table_content->subject_eval) && $table_content->subject_eval != '0') ? -1 : $table_content->subject_eval;
                $content_row->columns[1]->evaluation_text = \block_exacomp\global_config::get_teacher_eval_title_by_id(\block_exacomp\global_config::get_additionalinfo_value_mapping($table_content->subject_eval));
                $content_row->columns[1]->evaluation_mapped = \block_exacomp\global_config::get_additionalinfo_value_mapping($table_content->subject_eval);
                $content_row->columns[1]->evalniveauid = $table_content->subject_evalniveauid;
                break;
            case BLOCK_EXACOMP_ROLE_STUDENT:
                $student_evaluation = block_exacomp_get_comp_eval($courseid, BLOCK_EXACOMP_ROLE_STUDENT, $userid, BLOCK_EXACOMP_TYPE_SUBJECT, $subjectid);
                if (block_exacomp_get_assessment_topic_SelfEval($courseid) && $student_evaluation) {
                    $content_row->columns[1]->evaluation_text = $student_evaluation->get_value_title();
                    $content_row->columns[1]->evaluation = $student_evaluation->value;
                    $content_row->columns[1]->evaluation_mapped = $student_evaluation->value;
                } else {
                    $content_row->columns[1]->evaluation_text = '';
                    $content_row->columns[1]->evaluation = -1;
                    $content_row->columns[1]->evaluation_mapped = -1;
                }
                $content_row->columns[1]->evalniveauid = -1;
                break;
        }
        $content_row->columns[1]->span = 0;

        // 		$content_row->columns[1] = new stdClass();
        // 		$content_row->columns[1]->evaluation = 1;
        // 		$content_row->columns[1]->evaluation_text = "NEINEINEIN";
        // 		$content_row->columns[1]->evaluation_mapped = \block_exacomp\global_config::get_additionalinfo_value_mapping($table_content->subject_eval);
        // 		$content_row->columns[1]->evalniveauid = $table_content->subject_evalniveauid;
        // 		$content_row->columns[1]->span = 0;

        $table->rows[] = $content_row;
    }

    return $table;
}

/**
 * if additional grading is enabled, already existing evaluation for topic, subjects and descriptors are mapped to notes from 1 to 6
 *
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

    $sql = "SELECT DISTINCT d.id, d.title FROM {" . BLOCK_EXACOMP_DB_DESCRIPTORS . "} d
		LEFT JOIN {" . BLOCK_EXACOMP_DB_DESCTOPICS . "} dt ON d.id = dt.descrid
		LEFT JOIN {" . BLOCK_EXACOMP_DB_COURSETOPICS . "} ct ON dt.topicid = ct.topicid
		LEFT JOIN {" . BLOCK_EXACOMP_DB_DESCVISIBILITY . "} dv ON d.id = dv.descrid AND dv.courseid = ct.courseid
		LEFT JOIN {" . BLOCK_EXACOMP_DB_TOPICVISIBILITY . "} tv ON dt.topicid = tv.topicid AND tv.courseid = ct.courseid AND tv.niveauid IS NULL
		LEFT JOIN {" . BLOCK_EXACOMP_DB_TOPICS . "} t ON ct.topicid = t.id
		WHERE ct.courseid = ? AND t.subjid = ? AND

		" . (($parent) ? "d.parentid = 0" : "d.parentid!=0") . "

		AND ((dv.visible = 1 AND dv.studentid = 0 AND NOT EXISTS
		  (SELECT *
		   FROM {" . BLOCK_EXACOMP_DB_DESCVISIBILITY . "} dvsub
		   WHERE dvsub.descrid = dv.descrid AND dvsub.courseid = dv.courseid AND dvsub.visible = 0 AND dvsub.studentid = ?))
		   OR (dv.visible = 1 AND dv.studentid = ? AND NOT EXISTS
		  (SELECT *
		   FROM {" . BLOCK_EXACOMP_DB_DESCVISIBILITY . "} dvsub
		   WHERE dvsub.descrid = dv.descrid AND dvsub.courseid = dv.courseid AND dvsub.visible = 0 AND dvsub.studentid = 0)))

		AND ((tv.visible = 1 AND tv.studentid = 0 AND NOT EXISTS
		  (SELECT *
		   FROM {" . BLOCK_EXACOMP_DB_TOPICVISIBILITY . "} tvsub
		   WHERE tvsub.topicid = tv.topicid AND tvsub.courseid = tv.courseid AND tvsub.visible = 0 AND tvsub.studentid = ? AND tvsub.niveauid IS NULL))
		   OR (tv.visible = 1 AND tv.studentid = ? AND NOT EXISTS
		  (SELECT *
		   FROM {" . BLOCK_EXACOMP_DB_TOPICVISIBILITY . "} tvsub
		   WHERE tvsub.topicid = tv.topicid AND tvsub.courseid = tv.courseid AND tvsub.visible = 0 AND tvsub.studentid = 0 AND tvsub.niveauid IS NULL)))";

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

    $sql = "SELECT DISTINCT e.id, e.title FROM {" . BLOCK_EXACOMP_DB_EXAMPLES . "} e
		LEFT JOIN {" . BLOCK_EXACOMP_DB_DESCEXAMP . "} de ON e.id = de.exampid
		LEFT JOIN {" . BLOCK_EXACOMP_DB_DESCTOPICS . "} dt ON de.descrid = dt.descrid
		LEFT JOIN {" . BLOCK_EXACOMP_DB_COURSETOPICS . "} ct ON dt.topicid = ct.topicid
		LEFT JOIN {" . BLOCK_EXACOMP_DB_EXAMPVISIBILITY . "} ev ON e.id = ev.exampleid AND ev.courseid = ct.courseid
		LEFT JOIN {" . BLOCK_EXACOMP_DB_DESCVISIBILITY . "} dv ON de.descrid = dv.descrid AND dv.courseid = ct.courseid
		LEFT JOIN {" . BLOCK_EXACOMP_DB_TOPICVISIBILITY . "} tv ON dt.topicid = tv.topicid AND tv.courseid = ct.courseid AND tv.niveauid IS NULL
		LEFT JOIN {" . BLOCK_EXACOMP_DB_TOPICS . "} t ON ct.topicid = t.id

		WHERE ct.courseid = ? AND t.subjid = ?

		AND ((ev.visible = 1 AND ev.studentid = 0 AND NOT EXISTS
		  (SELECT *
		   FROM {" . BLOCK_EXACOMP_DB_EXAMPVISIBILITY . "} evsub
		   WHERE evsub.exampleid = ev.exampleid AND evsub.courseid = ev.courseid AND evsub.visible = 0 AND evsub.studentid = ?))
		   OR (ev.visible = 1 AND ev.studentid = ? AND NOT EXISTS
		  (SELECT *
		   FROM {" . BLOCK_EXACOMP_DB_EXAMPVISIBILITY . "} evsub
		   WHERE evsub.exampleid = ev.exampleid AND evsub.courseid = ev.courseid AND evsub.visible = 0 AND evsub.studentid = 0)))

		AND ((dv.visible = 1 AND dv.studentid = 0 AND NOT EXISTS
		  (SELECT *
		   FROM {" . BLOCK_EXACOMP_DB_DESCVISIBILITY . "} dvsub
		   WHERE dvsub.descrid = dv.descrid AND dvsub.courseid = dv.courseid AND dvsub.visible = 0 AND dvsub.studentid = ?))
		   OR (dv.visible = 1 AND dv.studentid = ? AND NOT EXISTS
		  (SELECT *
		   FROM {" . BLOCK_EXACOMP_DB_DESCVISIBILITY . "} dvsub
		   WHERE dvsub.descrid = dv.descrid AND dvsub.courseid = dv.courseid AND dvsub.visible = 0 AND dvsub.studentid = 0)))

		AND ((tv.visible = 1 AND tv.studentid = 0 AND NOT EXISTS
		  (SELECT *
		   FROM {" . BLOCK_EXACOMP_DB_TOPICVISIBILITY . "} tvsub
		   WHERE tvsub.topicid = tv.topicid AND tvsub.courseid = tv.courseid AND tvsub.visible = 0 AND tvsub.studentid = ? AND tvsub.niveauid IS NULL))
		   OR (tv.visible = 1 AND tv.studentid = ? AND NOT EXISTS
		  (SELECT *
		   FROM {" . BLOCK_EXACOMP_DB_TOPICVISIBILITY . "} tvsub
		   WHERE tvsub.topicid = tv.topicid AND tvsub.courseid = tv.courseid AND tvsub.visible = 0 AND tvsub.studentid = 0 AND tvsub.niveauid IS NULL)))";

    $params = array($courseid, $subjectid, $userid, $userid, $userid, $userid, $userid, $userid);

    return $DB->get_records_sql($sql, $params);
}

// /**
//  * get evaluation statistics for a user in course and subject context for descriptor, childdescriptor and examples
//  * within given timeframe (if start and end = 0, all available data is used)
//  * global use of evaluation_niveau is minded here
//  *
//  * @param int $courseid
//  * @param int $subjectid
//  * @param int $userid - not working for userid = 0 : no user_information available
//  * @return array(["descriptor_evaluation"] => ["evalniveauid"] => [evalvalue] => sum
//  *                 ["child_evaluation"] => ["evalniveauid"] => [evalvalue] => sum
//  *                 ["example_evaluation"] => ["evalniveauid"] => [evalvalue] => sum
//  *         )
//  * this is representing the resulting matrix, use of evaluation niveaus is minded here
//  * evalniveauid = 0 if block_exacomp_use_eval_niveau() = false, otherwise -1, 1, 2, 3
//  * evalvalue is 0 to 3, this statistic is not display if block_exacomp_additional_grading() = false
//  *
//  *
//  *
//  *
//  * RW 2018:
//  * $onlyDescriptors for the webservice that only needs the descriptors (better performance)
//  */
// function block_exacomp_get_evaluation_statistic_for_subject($courseid, $subjectid, $userid, $start_timestamp = 0, $end_timestamp = 0, $onlyDescriptors = false) {
//     // TODO: is visibility hier fÃ¼rn hugo? Bewertungen kann es eh nur fÃ¼r sichtbare geben ...
//     $descriptors = block_exacomp_get_visible_descriptors_for_subject($courseid, $subjectid, $userid);
//     $child_descriptors = block_exacomp_get_visible_descriptors_for_subject($courseid, $subjectid, $userid, false);
//     $examples = block_exacomp_get_visible_examples_for_subject($courseid, $subjectid, $userid);

//     $descriptorgradings = []; // array[niveauid][value][number of examples evaluated with this value and niveau]
//     $childgradings = [];
//     $examplegradings = [];

//     // create grading statistic
//     $scheme_items = \block_exacomp\global_config::get_teacher_eval_items(block_exacomp_get_grading_scheme($courseid));
//     $evaluationniveau_items = block_exacomp_use_eval_niveau()
//     ? \block_exacomp\global_config::get_evalniveaus()
//     : ['0' => ''];

//     foreach ($evaluationniveau_items as $niveaukey => $niveauitem) {
//         $descriptorgradings[$niveaukey] = [];
//         $childgradings[$niveaukey] = [];
//         $examplegradings[$niveaukey] = [];

//         foreach ($scheme_items as $schemekey => $schemetitle) {
//             if ($schemekey > -1) {
//                 $descriptorgradings[$niveaukey][$schemekey] = 0;
//                 $childgradings[$niveaukey][$schemekey] = 0;
//                 $examplegradings[$niveaukey][$schemekey] = 0;
//             }
//         }
//     }

//     foreach ($descriptors as $descriptor) {
//         $eval = block_exacomp_get_comp_eval($courseid, BLOCK_EXACOMP_ROLE_TEACHER, $userid, BLOCK_EXACOMP_TYPE_DESCRIPTOR, $descriptor->id);

//         // check if grading is within timeframe
//         if ($eval && $eval->value !== null && $eval->timestamp >= $start_timestamp && ($end_timestamp == 0 || $eval->timestamp <= $end_timestamp)) {
//             $niveaukey = block_exacomp_use_eval_niveau() ? $eval->evalniveauid : 0;

//             // increase counter in statistic
//             if (isset($descriptorgradings[$niveaukey][$eval->value])) {
//                 $descriptorgradings[$niveaukey][$eval->value]++;
//             }
//         }
//     }

//     if(!$onlyDescriptors){
//         foreach ($child_descriptors as $child) {
//             $eval = block_exacomp_get_comp_eval($courseid, BLOCK_EXACOMP_ROLE_TEACHER, $userid, BLOCK_EXACOMP_TYPE_DESCRIPTOR, $child->id);

//             // check if grading is within timeframe
//             if ($eval && $eval->value !== null && $eval->timestamp >= $start_timestamp && ($end_timestamp == 0 || $eval->timestamp <= $end_timestamp)) {
//                 $niveaukey = block_exacomp_use_eval_niveau() ? $eval->evalniveauid : 0;

//                 // increase counter in statistic
//                 if (isset($childgradings[$niveaukey][$eval->value])) {
//                     $childgradings[$niveaukey][$eval->value]++;
//                 }
//             }
//         }
//     }

//     if(!$onlyDescriptors){
//         foreach ($examples as $example) {
//             $eval = block_exacomp_get_comp_eval($courseid, BLOCK_EXACOMP_ROLE_TEACHER, $userid, BLOCK_EXACOMP_TYPE_EXAMPLE, $example->id);

//             // check if grading is within timeframe
//             if ($eval && $eval->value !== null && $eval->timestamp >= $start_timestamp && ($end_timestamp == 0 || $eval->timestamp <= $end_timestamp)) {
//                 $niveaukey = block_exacomp_use_eval_niveau() ? $eval->evalniveauid : 0;

//                 // increase counter in statistic
//                 if (isset($examplegradings[$niveaukey][$eval->value])) {
//                     $examplegradings[$niveaukey][$eval->value]++;
//                 }
//             }
//         }
//     }

//     if(!$onlyDescriptors){
//         return [
//             "descriptor_evaluations" => $descriptorgradings,
//             "child_evaluations" => $childgradings,
//             "example_evaluations" => $examplegradings,
//         ];
//     }else{
//         return $descriptorgradings;
//     }
// }

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
 *
 *
 *
 * RW 2018:
 * $onlyDescriptors for the webservice that only needs the descriptors (better performance)
 */
function block_exacomp_get_evaluation_statistic_for_subject($courseid, $subjectid, $userid, $start_timestamp = 0, $end_timestamp = 0, $onlyDescriptors = false, $crosssubj = null) {
    // TODO: is visibility hier fÃ¼rn hugo? Bewertungen kann es eh nur fÃ¼r sichtbare geben ...
    if ($crosssubj) {
        $descriptors = block_exacomp_get_descriptors_for_cross_subject($courseid, $crosssubj->id);
        $child_descriptors = block_exacomp_get_descriptors_for_cross_subject($courseid, $crosssubj->id, true);
        $examples = block_exacomp_get_visible_examples_for_subject($courseid, $subjectid, $userid); // TODO: change for crosssubject
    } else {
        $descriptors = block_exacomp_get_visible_descriptors_for_subject($courseid, $subjectid, $userid);
        $child_descriptors = block_exacomp_get_visible_descriptors_for_subject($courseid, $subjectid, $userid, false);
        $examples = block_exacomp_get_visible_examples_for_subject($courseid, $subjectid, $userid);
    }
    $descriptorgradings = []; // array[niveauid][value][number of examples evaluated with this value and niveau]
    $childgradings = [];
    $examplegradings = [];

    $compAssessment = block_exacomp_get_assessment_comp_scheme($courseid); //variable for faster program RW

    // create grading statistic
    //$scheme_items = \block_exacomp\global_config::get_teacher_eval_items(block_exacomp_get_grading_scheme($courseid)); //deprecated/not generic? RW or just wrong?
    $schemeItems_descriptors = \block_exacomp\global_config::get_teacher_eval_items($courseid, false, $compAssessment);
    $schemeItems_examples = \block_exacomp\global_config::get_teacher_eval_items($courseid, false, block_exacomp_get_assessment_example_scheme($courseid));

    // 	switch (block_exacomp_get_assessment_comp_scheme()) {
    // 	    case BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE:
    // 	        $schemeItems_descriptors =
    // 	        break;
    // 	    case BLOCK_EXACOMP_ASSESSMENT_TYPE_VERBOSE:
    // 	        $titles = preg_split("/(\/|,) /", block_exacomp_get_assessment_verbose_options());
    // 	        echo $titles[$i];
    // 	        break;
    // 	    case BLOCK_EXACOMP_ASSESSMENT_TYPE_POINTS:
    // 	        echo $i;
    // 	        break;
    // 	    case BLOCK_EXACOMP_ASSESSMENT_TYPE_YESNO:
    // 	        echo $i == 1 ? block_exacomp_get_string('yes_no_Yes') : block_exacomp_get_string('yes_no_No');
    // 	        break;
    // 	}

    // 	 $schemeItems_descriptors = block_exacomp_additional_grading(BLOCK_EXACOMP_TYPE_DESCRIPTOR); //get the generic schemeItems RW

    $evaluationniveau_items = block_exacomp_use_eval_niveau($courseid)
        ? \block_exacomp\global_config::get_evalniveaus(null, $courseid) : [
            '-1' => '',
        ];

    // if diff levels are not active, but some gradings already have them ---> handle those gradings like there is no niveau
    if (block_exacomp_get_assessment_comp_diffLevel($courseid) == 1) {
        $evaluationniveau_items_comp = $evaluationniveau_items + ['-1' => ''];
    } else {
        $evaluationniveau_items_comp = ['-1' => ''];
    }
    if (block_exacomp_get_assessment_childcomp_diffLevel($courseid) == 1) {
        $evaluationniveau_items_childcomp = $evaluationniveau_items + ['-1' => ''];
    } else {
        $evaluationniveau_items_childcomp = ['-1' => ''];
    }
    if (block_exacomp_get_assessment_example_diffLevel($courseid) == 1) {
        $evaluationniveau_items_example = $evaluationniveau_items + ['-1' => ''];
    } else {
        $evaluationniveau_items_example = ['-1' => ''];
    }

    foreach ($evaluationniveau_items_comp as $niveaukey => $niveauitem) {
        $descriptorgradings[$niveaukey] = [];
        foreach ($schemeItems_descriptors as $schemekey => $schemetitle) {
            if ($schemekey > -1) {
                $descriptorgradings[$niveaukey][$schemekey] = 0;
            }
        }
    }

    foreach ($evaluationniveau_items_childcomp as $niveaukey => $niveauitem) {
        $childgradings[$niveaukey] = [];
        foreach ($schemeItems_descriptors as $schemekey => $schemetitle) {
            if ($schemekey > -1) {
                $childgradings[$niveaukey][$schemekey] = 0;
            }
        }
    }
    foreach ($evaluationniveau_items_example as $niveaukey => $niveauitem) {
        $examplegradings[$niveaukey] = [];
        foreach ($schemeItems_examples as $schemekey => $schemetitle) {
            if ($schemekey > -1) {
                $examplegradings[$niveaukey][$schemekey] = 0;
            }
        }
    }

    foreach ($descriptors as $descriptor) {
        $eval = block_exacomp_get_comp_eval($courseid, BLOCK_EXACOMP_ROLE_TEACHER, $userid, BLOCK_EXACOMP_TYPE_DESCRIPTOR, $descriptor->id);

        // check if grading is within timeframe
        if ($eval && ($eval->value || $eval->additionalinfo) !== null && $eval->timestamp >= $start_timestamp && ($end_timestamp == 0 || $eval->timestamp <= $end_timestamp)) {
            //$niveaukey = block_exacomp_use_eval_niveau() ? $eval->evalniveauid : 0;
            if (block_exacomp_get_assessment_comp_diffLevel($courseid)) {
                $niveaukey = $eval->evalniveauid ? $eval->evalniveauid : -1;
            } else {
                $niveaukey = -1;
            }
            // increase counter in statistic
            if ($compAssessment == BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE) { // additionalinfo nutzen, nicht value
                // it can be not integer. Make it:
                $indexVal = round($eval->additionalinfo, 0, PHP_ROUND_HALF_DOWN);
                if (isset($descriptorgradings[$niveaukey][$indexVal])) {
                    $descriptorgradings[$niveaukey][$indexVal]++;
                } else {
                    @$descriptorgradings[0][$indexVal]++;
                }
            } else { // POINTS or YESNO  or Verbose
                if (isset($descriptorgradings[$niveaukey][$eval->value])) {
                    $descriptorgradings[$niveaukey][$eval->value]++;
                } else {
                    @$descriptorgradings[0][$eval->value]++;
                }
            }
        }
    }
    // clean empty niveau if niveau is used and no any data in this row
    if (block_exacomp_get_assessment_comp_diffLevel($courseid)) {
        if (!count(array_filter($descriptorgradings[-1]))) {
            unset($descriptorgradings[-1]);
        }
    }

    if (!$onlyDescriptors) {
        foreach ($child_descriptors as $child) {
            $eval = block_exacomp_get_comp_eval($courseid, BLOCK_EXACOMP_ROLE_TEACHER, $userid, BLOCK_EXACOMP_TYPE_DESCRIPTOR, $child->id);
            // check if grading is within timeframe
            if ($eval && $eval->value !== null && $eval->timestamp >= $start_timestamp && ($end_timestamp == 0 || $eval->timestamp <= $end_timestamp)) {
                //$niveaukey = block_exacomp_use_eval_niveau() ? $eval->evalniveauid : 0;
                if (block_exacomp_get_assessment_childcomp_diffLevel($courseid)) {
                    $niveaukey = $eval->evalniveauid ? $eval->evalniveauid : -1;
                } else {
                    $niveaukey = -1;
                }

                // increase counter in statistic
                if (isset($childgradings[$niveaukey][$eval->value])) {
                    $childgradings[$niveaukey][$eval->value]++;
                }
            }
        }
        // clean empty niveau if niveau is used and no any data in this row
        if (block_exacomp_get_assessment_childcomp_diffLevel($courseid)) {
            if (!count(array_filter($childgradings[-1]))) {
                unset($childgradings[-1]);
            }
        }
    }

    if (!$onlyDescriptors) {
        foreach ($examples as $example) {
            $eval = block_exacomp_get_comp_eval($courseid, BLOCK_EXACOMP_ROLE_TEACHER, $userid, BLOCK_EXACOMP_TYPE_EXAMPLE, $example->id);
            // check if grading is within timeframe
            if ($eval && $eval->value !== null && $eval->timestamp >= $start_timestamp && ($end_timestamp == 0 || $eval->timestamp <= $end_timestamp)) {
                //$niveaukey = block_exacomp_use_eval_niveau() ? $eval->evalniveauid : 0;
                if (block_exacomp_get_assessment_example_diffLevel($courseid)) {
                    $niveaukey = $eval->evalniveauid ? $eval->evalniveauid : -1;
                } else {
                    $niveaukey = -1;
                }
                // increase counter in statistic
                if (isset($examplegradings[$niveaukey][$eval->value])) {
                    $examplegradings[$niveaukey][$eval->value]++;
                }
            }
        }
        // clean empty niveau if niveau is used and no any data in this row
        if (block_exacomp_get_assessment_example_diffLevel($courseid)) {
            if (!count(array_filter($examplegradings[-1]))) {
                //unset($examplegradings[-1]);
            }
        }
    }

    if (!$onlyDescriptors) {
        return [
            "descriptor_evaluations" => $descriptorgradings,
            "child_evaluations" => $childgradings,
            "example_evaluations" => $examplegradings,
        ];
    } else {
        return [
            "descriptor_evaluations" => $descriptorgradings,
            "descriptorsToGain" => count($descriptors),
        ];
    }
}

/**
 * get evaluation statistics for a user in course and subject context for descriptor, childdescriptor and examples
 * global use of evaluation_niveau is minded here
 *
 * @param int $courseid
 * @param int $topicid
 * @param int $studentid
 * @param int $start_timestamp
 * @param int $end_timestamp
 * @return array descriptor_evaluation_list this is a list of niveautitles of all evaluated descriptors with according evaluation value and evaluation niveau
 */
function block_exacomp_get_descriptor_statistic_for_topic($courseid, $topicid, $studentid, $start_timestamp = 0, $end_timestamp = 0) {
    global $DB;

    if (!$end_timestamp) {
        // until now
        $end_timestamp = time();
    }

    //$use_evalniveau = block_exacomp_use_eval_niveau();
    $use_evalniveau = block_exacomp_get_assessment_comp_diffLevel($courseid);
    if ($use_evalniveau) {
        $evaluationniveau_items = \block_exacomp\global_config::get_evalniveaus(true, $courseid);
    } else {
        $evaluationniveau_items = array(block_exacomp_get_string('teacherevaluation_short'));
    }
    $user = $DB->get_record("user", array("id" => $studentid));

    $topic = \block_exacomp\topic::get($topicid);
    $topic->setDbLayer(\block_exacomp\db_layer_course::create($courseid));

    $descriptorgradings = array(); //array[niveauid][value][number of examples evaluated with this value and niveau]

    //$user = block_exacomp_get_user_information_by_course($user, $courseid);
    $scheme_items = \block_exacomp\global_config::get_teacher_eval_items($courseid);
    $avgsum = array();
    $avgsumstudent = array();
    $averagedescriptorgradings = array();

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
        if (!array_key_exists($niveau->title, $averagedescriptorgradings)) {
            $averagedescriptorgradings[$niveau->title] = new stdClass();
            //$averagedescriptorgradings[$niveau->title]->evalniveaus = array();
            $averagedescriptorgradings[$niveau->title]->teachervalues = array();
            $averagedescriptorgradings[$niveau->title]->teachervaluetitles = array();
        }

        if (!array_key_exists($niveau->title, $avgsum)) {
            $avgsum[$niveau->title] = array();
        }
        if (!array_key_exists($niveau->title, $avgsumstudent)) {
            $avgsumstudent[$niveau->title] = array();
        }

        if ($use_evalniveau && $teacher_evaluation) {
            $evalniveauindex = $teacher_evaluation->evalniveauid;
        } else {
            $evalniveauindex = 0;
        }
        if ($teacher_evaluation) {
            //if (!array_key_exists($teacher_evaluation->evalniveauid, $averagedescriptorgradings[$niveau->title]->evalniveaus)) {
            //    $averagedescriptorgradings[$niveau->title]->evalniveaus[$teacher_evaluation->evalniveauid] = array();
            //}
            if (!array_key_exists($evalniveauindex, $avgsum[$niveau->title])) {
                $avgsum[$niveau->title][$evalniveauindex] = array();
            }
        }

        if (block_exacomp_additional_grading(BLOCK_EXACOMP_TYPE_DESCRIPTOR, $courseid) == BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE) {
            $descriptorgradings[$niveau->title]->teachervalue = (($teacher_evaluation && $teacher_evaluation->additionalinfo) ? \block_exacomp\global_config::get_additionalinfo_value_mapping($teacher_evaluation->additionalinfo) : '');
            $avgsum[$niveau->title][$evalniveauindex][] = (($teacher_evaluation && $teacher_evaluation->additionalinfo) ? $teacher_evaluation->additionalinfo : 0);
        } else {
            if ($teacher_evaluation) {
                $descriptorgradings[$niveau->title]->teachervalue = ($teacher_evaluation->value ? $scheme_items[$teacher_evaluation->value] : -1);
                $avgsum[$niveau->title][$evalniveauindex][] = ($teacher_evaluation->value ? $teacher_evaluation->value : 0);
            } else {
                $descriptorgradings[$niveau->title]->teachervalue = -1;
            }
        }
        if ($student_evaluation && $student_eval_within_timeframe) {
            $avgsumstudent[$niveau->title][] = ($student_evaluation->value ? $student_evaluation->value : -1);
        }
        $descriptorgradings[$niveau->title]->teachervaluetitle = $descriptorgradings[$niveau->title]->teachervalue;
        /*$descriptorgradings[$niveau->title]->teachervalue =
			(block_exacomp_additional_grading(BLOCK_EXACOMP_TYPE_DESCRIPTOR))
				? (($teacher_evaluation && $teacher_evaluation->additionalinfo) ? \block_exacomp\global_config::get_additionalinfo_value_mapping($teacher_evaluation->additionalinfo) : '')
				: (($teacher_evaluation && $teacher_evaluation->value) ? $scheme_items[$teacher_evaluation->value] : -1);*/
        $descriptorgradings[$niveau->title]->evalniveau = ($use_evalniveau && $teacher_eval_within_timeframe ? $teacher_evaluation->evalniveauid : -1) ?: -1;
        $descriptorgradings[$niveau->title]->studentvalue = ($student_eval_within_timeframe ? $student_evaluation->value : -1) ?: -1;
        //$averagedescriptorgradings[$niveau->title]->evalniveau = ($use_evalniveau && $teacher_eval_within_timeframe ? $teacher_evaluation->evalniveauid : -1) ?: -1;
        //$averagedescriptorgradings[$niveau->title]->studentvalue = ($student_eval_within_timeframe ? $student_evaluation->value : -1) ?: -1;
    }
    // get average for every niveau
    foreach ($avgsum as $ntitle => $evalniveaus) {
        foreach ($evalniveaus as $evalniveauid => $vals) {
            $average = round(array_sum($vals) / count($vals));
            if (block_exacomp_additional_grading(BLOCK_EXACOMP_TYPE_DESCRIPTOR, $courseid) == BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE) {
                $teachervalue = \block_exacomp\global_config::get_additionalinfo_value_mapping($average);
                $averagedescriptorgradings[$ntitle]->teachervalues[$evalniveauid] = $teachervalue;
                $averagedescriptorgradings[$ntitle]->teachervaluetitles[$evalniveauid] = $teachervalue;
            } else {
                $averagedescriptorgradings[$ntitle]->teachervalues[$evalniveauid] = $average;
                $averagedescriptorgradings[$ntitle]->teachervaluetitles[$evalniveauid] = $scheme_items[round($average)];
            }
        }
    }
    // get average for student values
    foreach ($avgsumstudent as $ntitle => $vals) {
        if (count($vals) > 0) {
            $average = round(array_sum($vals) / count($vals));
            $averagedescriptorgradings[$ntitle]->studentvalue = $average;
        } else {
            $averagedescriptorgradings[$ntitle]->studentvalue = -1;
        }
    }
    return array(
        'descriptor_evaluation' => $descriptorgradings,
        'average_descriptor_evaluations' => $averagedescriptorgradings, // we need to get average value for niveau (topic has many descriptors)
    );
}

/**
 * get all underlying examples for one descriptor, including those associated with child descriptors and sort them according to their state
 *
 * @param unknown $courseid
 * @param unknown $descriptorid
 * @param unknown $userid
 * @return unknown
 */
function block_exacomp_get_visible_own_and_child_examples_for_descriptor($courseid, $descriptorid, $userid) {
    global $DB;
    $sql = 'SELECT DISTINCT e.id, e.title, e.sorting FROM {' . BLOCK_EXACOMP_DB_EXAMPLES . '} e
		JOIN {' . BLOCK_EXACOMP_DB_DESCEXAMP . '} de ON de.exampid = e.id
		JOIN {' . BLOCK_EXACOMP_DB_DESCRIPTORS . '} d ON de.descrid = d.id
		LEFT JOIN {' . BLOCK_EXACOMP_DB_EXAMPVISIBILITY . '} ev ON e.id = ev.exampleid AND ev.courseid = ?
		WHERE e.blocking_event = 0 AND d.id IN (
				SELECT dsub.id FROM {' . BLOCK_EXACOMP_DB_DESCRIPTORS . '} dsub
                LEFT JOIN {' . BLOCK_EXACOMP_DB_DESCVISIBILITY . '} dv ON dsub.id = dv.descrid AND dv.courseid = ?
                WHERE dsub.id = ? OR dsub.parentid = ?
                AND ((dv.visible = 1 AND dv.studentid = 0 AND NOT EXISTS (
                		SELECT * FROM {' . BLOCK_EXACOMP_DB_DESCVISIBILITY . '} dvsub
		  				WHERE dvsub.descrid = dv.descrid AND dvsub.courseid = dv.courseid AND dvsub.visible = 0 AND dvsub.studentid = ?))
		  		OR (dv.visible = 1 AND dv.studentid = ? AND NOT EXISTS (
                		SELECT * FROM {' . BLOCK_EXACOMP_DB_DESCVISIBILITY . '} dvsub
		  			 	WHERE dvsub.descrid = dv.descrid AND dvsub.courseid = dv.courseid AND dvsub.visible = 0 AND dvsub.studentid = 0)))
		)
 		AND ((ev.visible = 1 AND ev.studentid = 0 AND NOT EXISTS (
              	SELECT * FROM {' . BLOCK_EXACOMP_DB_EXAMPVISIBILITY . '} evsub
   				WHERE evsub.exampleid = ev.exampleid AND evsub.courseid = ev.courseid AND evsub.visible = 0 AND evsub.studentid = ?))
   		OR (ev.visible = 1 AND ev.studentid = ? AND NOT EXISTS (
              	SELECT * FROM {' . BLOCK_EXACOMP_DB_EXAMPVISIBILITY . '} evsub
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
 *
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
			FROM {" . BLOCK_EXACOMP_DB_EXAMPVISIBILITY . "} ev
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
			FROM {" . BLOCK_EXACOMP_DB_DESCVISIBILITY . "} dv
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
			FROM {" . BLOCK_EXACOMP_DB_TOPICVISIBILITY . "} tv
			WHERE tv.courseid = ? AND tv.studentid = ? AND tv.niveauid IS NULL", [$courseid, $userid]);
    }, func_get_args());
}

/**
 * return all visible niveaus for a course and topic and user context with only one sql query
 *
 * @param int $courseid
 * @param int $userid
 * @param int $topicid
 *
 * @return: {{id}, {...}}
 */
function block_exacomp_get_niveau_visibilities_for_course_and_topic_and_user($courseid, $userid, $topicid) {
    return Cache::staticCallback(__FUNCTION__, function($courseid, $userid, $topicid) {
        return g::$DB->get_records_sql_menu("
			SELECT DISTINCT tv.niveauid, tv.visible
			FROM {" . BLOCK_EXACOMP_DB_TOPICVISIBILITY . "} tv
			WHERE tv.courseid = ? AND tv.studentid = ? AND tv.topicid = ? AND tv.niveauid IS NOT NULL", [$courseid, $userid, $topicid]);
    }, func_get_args());
}

/**
 * returnes a list of examples whose solutions are visibile in course and user context
 *
 * @param unknown $courseid
 * @param number $userid
 */
function block_exacomp_get_solution_visibilities_for_course_and_user($courseid, $userid = 0) {
    return Cache::staticCallback(__FUNCTION__, function($courseid, $userid) {
        return g::$DB->get_records_sql_menu("
			SELECT DISTINCT sv.exampleid, sv.visible
			FROM {" . BLOCK_EXACOMP_DB_SOLUTIONVISIBILITY . "} sv
			WHERE sv.courseid = ? AND sv.studentid=?
		", [$courseid, $userid]);
    }, func_get_args());
}

/**
 * create tree for one example, similar like block_exacomp_build_example_association_tree()
 * but with improved performance
 *
 * @param int $courseid
 * @param int $exampleid
 * @param \block_exacomp\example $exampleObj for using with  virtual example and moodle action relation (old method)
 */
function block_exacomp_build_example_parent_names($courseid, $exampleid, $exampleObj = null) {

    $sql = "SELECT d.id as descrid, d.title as descrtitle, d.parentid as parentid, s.id as subjid, s.title as subjecttitle, t.id as topicid, t.title as topictitle,
				e.id as exampleid, e.title as exampletitle
			FROM {" . BLOCK_EXACOMP_DB_SUBJECTS . "} s
			JOIN {" . BLOCK_EXACOMP_DB_TOPICS . "} t ON t.subjid = s.id
			JOIN {" . BLOCK_EXACOMP_DB_COURSETOPICS . "} ct ON t.id = ct.topicid
			JOIN {" . BLOCK_EXACOMP_DB_DESCTOPICS . "} dt ON dt.topicid = t.id
			JOIN {" . BLOCK_EXACOMP_DB_DESCRIPTORS . "} d ON d.id = dt.descrid
			JOIN {" . BLOCK_EXACOMP_DB_DESCEXAMP . "} de ON d.id = de.descrid
			JOIN {" . BLOCK_EXACOMP_DB_EXAMPLES . "} e ON e.id = de.exampid
			WHERE ct.courseid = ? AND e.id = ? ";
    $sqlParams = [$courseid, $exampleid];

    // use another $sql if it is a virtual example - for old realtion to moodle activity - we know only id of descriptor or topic
    if ($exampleObj && property_exists($exampleObj, 'isVirtualExample') && $exampleObj->isVirtualExample) {

        $select = " s.id as subjid, s.title as subjecttitle, t.id as topicid, t.title as topictitle ";
        $where = " ct.courseid = ? ";
        $sqlParams = [$courseid, $exampleObj->idOfVirtualParent];
        $from = " FROM {" . BLOCK_EXACOMP_DB_SUBJECTS . "} s
			        JOIN {" . BLOCK_EXACOMP_DB_TOPICS . "} t ON t.subjid = s.id
			        JOIN {" . BLOCK_EXACOMP_DB_COURSETOPICS . "} ct ON t.id = ct.topicid ";

        switch ($exampleObj->levelOfVirtualParent) {
            case BLOCK_EXACOMP_TYPE_DESCRIPTOR:
                $select .= ", d.id as descrid, d.title as descrtitle, d.parentid as parentid, 0 as exampleid, '' as exampletitle ";
                $from .= "  JOIN {" . BLOCK_EXACOMP_DB_DESCTOPICS . "} dt ON dt.topicid = t.id
			                JOIN {" . BLOCK_EXACOMP_DB_DESCRIPTORS . "} d ON d.id = dt.descrid ";
                $where .= " AND d.id = ? ";
                break;
            case BLOCK_EXACOMP_TYPE_TOPIC:
                $select .= ", 0 as descrid, '' as descrtitle, 0 as parentid, 0 as exampleid, '' as exampletitle ";
                $where .= " AND t.id = ? ";
                break;
        }

        $sql = " SELECT " . $select . " " . $from . " WHERE " . $where;

    }

    $records = iterator_to_array(g::$DB->get_recordset_sql($sql, $sqlParams));

    $flatTree = array();
    foreach ($records as $record) {
        $titles = [block_exacomp_get_topic_numbering(\block_exacomp\topic::get($record->topicid)) . ' ' . $record->topictitle];
        //check if parent descriptor or child
        if ($record->parentid > 0) {    //child get parentdescriptor
            $parent_descriptor = \block_exacomp\descriptor::get($record->parentid);
            $parent_descriptor->topicid = $record->topicid;

            $titles[] = block_exacomp_get_descriptor_numbering($parent_descriptor) . ' ' . $parent_descriptor->title;
        }

        if ($record->descrid > 0) {
            $descriptor = \block_exacomp\descriptor::get($record->descrid);
            $descriptor->topicid = $record->topicid;
            $titles[] = block_exacomp_get_descriptor_numbering($descriptor) . ' ' . $record->descrtitle;
        }

        $flatTree[join(' | ', $titles)] = $titles;
    }

    if ($records == null && $exampleid > 0) {
        $record = g::$DB->get_record_sql(
            "SELECT c.title
			  FROM {" . BLOCK_EXACOMP_DB_DESCEXAMP . "} e
              JOIN {" . BLOCK_EXACOMP_DB_CROSSSUBJECTS . "} c ON e.id_foreign=c.id
			  WHERE exampid = ?"
            , [
            $exampleid,
        ]);
        if ($record) {
            $flatTree = array();
            $titles[] = $record->title;
            $flatTree[join(' | ', $titles)] = $titles;
        }
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
 *
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
 *
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
 * @deprecated
 */
/*function block_exacomp_get_user_subject_evaluation($userid, $subjectid, $courseid) {
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
}*/

/**
 * searches the competence grid of one course and returns only the found items
 *
 * @param $courseid
 * @param $q
 * @return array
 */
function block_exacomp_search_competence_grid_as_tree($courseid, $q) {
    global $USER;
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

    $find = function($object) use ($queryItems, $USER) {
        if (!$queryItems) {
            // no filter, so got found!
            return true;
        }

        // settings parameter 'show_teacherdescriptors_global':
        if ($object instanceof \block_exacomp\descriptor) {
            if (!get_config('exacomp', 'show_teacherdescriptors_global') && isset($object->descriptor_creatorid) && $object->descriptor_creatorid != $USER->id) {
                return false;
            }
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
 *
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

function block_exacomp_check_competence_data_is_gained($competence_data, $courseid = 0) {
    if (block_exacomp_additional_grading(BLOCK_EXACOMP_TYPE_DESCRIPTOR, $courseid)) {
        $value = block_exacomp\global_config::get_additionalinfo_value_mapping($competence_data->additionalinfo);

        return $value >= 1;
    } else {
        return $competence_data->value >= 1;
    }
}

function block_exacomp_get_comp_eval_gained($courseid, $role, $userid, $comptype, $compid) {
    $eval = block_exacomp_get_comp_eval($courseid, $role, $userid, $comptype, $compid);

    return $eval && block_exacomp_check_competence_data_is_gained($eval, $courseid) ? $eval : null;
}

/**
 * return evaluation for any type of competence: descriptor, subject, topic, crosssubject
 *
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
            'id' => 'example:' . $eval->id, // TODO: 'example:' is needed yet?
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
 *
 * @param $courseid
 * @param $studentid
 * @param $comptype
 * @param $compid
 * @return \block_exacomp\comp_eval_merged
 */
function block_exacomp_get_comp_eval_merged($courseid, $studentid, $item) {
    return \block_exacomp\comp_eval_merged::get($courseid, $studentid, $item);
}

function block_exacomp_set_comp_eval($courseid, $role, $studentid, $comptype, $compid, $data, $savegradinghistory = true) {
    global $USER;
    $data = (array)$data;
    unset($data['courseid']);
    unset($data['role']);
    unset($data['userid']);
    unset($data['comptype']);
    unset($data['compid']);

    if ($comptype == BLOCK_EXACOMP_TYPE_EXAMPLE) {
        if ($role == BLOCK_EXACOMP_ROLE_TEACHER || $role == BLOCK_EXACOMP_ROLE_SYSTEM) {
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
            if ($role == BLOCK_EXACOMP_ROLE_TEACHER || $role == BLOCK_EXACOMP_ROLE_SYSTEM) {
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

            $scheme_values = \block_exacomp\global_config::get_teacher_eval_items(0, false, block_exacomp_additional_grading($comptype, $courseid));
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
                    //					if(!block_exacomp_additional_grading($comptype)) { //only add to history if not using additionalgrading or it will be a duplicate entry
                    if ($savegradinghistory) { //only add to history if not using additionalgrading or it will be a duplicate entry
                        if ($role == BLOCK_EXACOMP_ROLE_SYSTEM) {
                            $adminuser = get_admin();
                            $data['gradinghistory'] = $record->gradinghistory . $adminuser->firstname . " " . $adminuser->lastname . " " . date("d.m.y G:i", $data['timestamp']) . ": " . $scheme_values[$data['value']] . "<br>";
                        } else {
                            $data['gradinghistory'] = $record->gradinghistory . $USER->firstname . " " . $USER->lastname . " " . date("d.m.y G:i", $data['timestamp']) . ": " . $scheme_values[$data['value']] . "<br>";
                        }
                    }
                }
            } else {
                if ($savegradinghistory) {
                    $data['timestamp'] = time();
                    if ($role == BLOCK_EXACOMP_ROLE_SYSTEM) {
                        $adminuser = get_admin();
                        $data['gradinghistory'] = $adminuser->firstname . " " . $adminuser->lastname . " " . date("d.m.y G:i", $data['timestamp']) . ": " . $scheme_values[$data['value']] . "<br>";
                    } else {
                        $data['gradinghistory'] = $USER->firstname . " " . $USER->lastname . " " . date("d.m.y G:i", $data['timestamp']) . ": " . $scheme_values[$data['value']] . "<br>";
                    }

                }
            }
        }

        $data['gradingisold'] = 0;
        g::$DB->insert_or_update_record(BLOCK_EXACOMP_DB_COMPETENCES, $data, [
            'courseid' => $courseid,
            'userid' => $studentid,
            'comptype' => $comptype,
            'compid' => $compid,
            'role' => $role,
        ]);

        //set the gradingisold flag of the parentdescriptor(if there is one) to "1"
        block_exacomp_set_descriptor_gradingisold($courseid, $compid, $studentid, $role);
    }
}

/**
 * return evaluation value for any type of competence: descriptor, subject, topic, crosssubject
 *
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
 *
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
 *
 * @param unknown $cap
 * @param unknown $data
 * @return boolean
 * @throws \coding_exception
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
 *
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
 *
 * @param unknown $cap
 * @param unknown $item
 * @return boolean
 * @throws \coding_exception
 * @throws block_exacomp_permission_exception
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
            if (!isset($examples[$item->id])) {
                throw new block_exacomp_permission_exception('Not a course example!!!');
            }
        }
    } else if ($item instanceof \block_exacomp\example && in_array($cap, [BLOCK_EXACOMP_CAP_VIEW])) {
        if (!block_exacomp_is_student() && !block_exacomp_is_teacher()) {
            throw new block_exacomp_permission_exception('User is no teacher or student');
        }

        // only if it is not imported utem (custom)
        if ($item->source == BLOCK_EXACOMP_DATA_SOURCE_CUSTOM || $item->source === 0) {
            if ($item->creatorid == g::$USER->id || $item->creatorid === null) {
                // User is creator
                return true;
            }
        }

        // find descriptor in course
        $examples = block_exacomp_get_examples_by_course(g::$COURSE->id);
        if (!isset($examples[$item->id])) {
            $examples = block_exacomp_get_crosssubject_examples_by_course(g::$COURSE->id);
            if (!isset($examples[$item->id])) {
                if (!$item->blocking_event == 2) { //check if it is a free material
                    throw new block_exacomp_permission_exception('Not a course example.');
                }
            }
        }

        // TODO: check visibility?
    } else if ($item instanceof \block_exacomp\subject && in_array($cap, [BLOCK_EXACOMP_CAP_MODIFY, BLOCK_EXACOMP_CAP_DELETE])) {
        if (!block_exacomp_is_teacher(g::$COURSE->id)) {
            throw new block_exacomp_permission_exception('User is no teacher');
        }

        $subjects = block_exacomp_get_subjects(g::$COURSE->id);
        if (!isset($subjects[$item->id])) {
            throw new block_exacomp_permission_exception('No course subject');
        }

        if ($item->source != BLOCK_EXACOMP_DATA_SOURCE_CUSTOM && !$item->is_editable) {
            throw new block_exacomp_permission_exception('Not a custom subject');
        }
    } else if ($item instanceof \block_exacomp\topic && in_array($cap, [BLOCK_EXACOMP_CAP_MODIFY, BLOCK_EXACOMP_CAP_DELETE])) {
        if (!block_exacomp_is_teacher(g::$COURSE->id)) {
            throw new block_exacomp_permission_exception('User is no teacher');
        }

        // 		$topics = block_exacomp_get_topics_by_course(g::$COURSE->id);
        // 		if (!isset($topics[$item->id])) {
        // 			throw new block_exacomp_permission_exception('No course topic');
        // 		}

        if ($item->source != BLOCK_EXACOMP_DATA_SOURCE_CUSTOM) {
            // get the subject to check if this whole subject is editable and therefore the item as well
            $subject = $item->get_subject();
            if (!$subject->is_editable) {
                throw new block_exacomp_permission_exception('Not a custom topic');
            }
        }
    } else if ($item instanceof \block_exacomp\descriptor && in_array($cap, [BLOCK_EXACOMP_CAP_MODIFY, BLOCK_EXACOMP_CAP_DELETE])) {
        // only if it is not imported utem (custom)
        if ($item->source == BLOCK_EXACOMP_DATA_SOURCE_CUSTOM || $item->source === 0) {
            // is this a creator of custom element?
            if ($item->creatorid == g::$USER->id || $item->creatorid === null) {
                // User is creator
                return true;
            }
        }
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

        /*if ($item->source != BLOCK_EXACOMP_DATA_SOURCE_CUSTOM) {
			throw new block_exacomp_permission_exception('Not a custom descriptor');
		}*/
    } else if ($item instanceof \block_exacomp\cross_subject && in_array($cap, [BLOCK_EXACOMP_CAP_MODIFY, BLOCK_EXACOMP_CAP_DELETE])) {
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
    } else if ($item instanceof \block_exacomp\cross_subject && in_array($cap, [BLOCK_EXACOMP_CAP_VIEW])) {
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
    } else if ($item instanceof \block_exacomp\niveau && in_array($cap, [BLOCK_EXACOMP_CAP_MODIFY, BLOCK_EXACOMP_CAP_DELETE])) {
        if (!block_exacomp_is_teacher()) {
            throw new block_exacomp_permission_exception('User is no teacher');
        }
        // TODO: other checking?
    } else {
        throw new \coding_exception("Capability $cap for item " . print_r($item, true) . " not found");
    }

    return true;
}

/**
 * check capability for certain item
 *
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
    } else if ($type == BLOCK_EXACOMP_TYPE_TOPIC) {
        return BLOCK_EXACOMP_DB_TOPICS;
    } else if ($type == BLOCK_EXACOMP_TYPE_DESCRIPTOR) {
        return BLOCK_EXACOMP_DB_DESCRIPTORS;
    } else if ($type == BLOCK_EXACOMP_TYPE_EXAMPLE) {
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
            if (!$filter) {
                //default constants filter
                @$filter[BLOCK_EXACOMP_TYPE_SUBJECT]['visible'] = true;
                @$filter[BLOCK_EXACOMP_TYPE_TOPIC]['visible'] = true;
                @$filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR_PARENT]['visible'] = true;
                @$filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR_CHILD]['visible'] = true;
                //@$filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR]['visible'] = true;
                @$filter[BLOCK_EXACOMP_TYPE_SUBJECT]['active'] = true;
                @$filter[BLOCK_EXACOMP_TYPE_TOPIC]['active'] = true;
                @$filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR_PARENT]['active'] = true;
                @$filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR_CHILD]['active'] = true;
            }
        //break;
        default:
            if (!$filter) {
                // default filter
                @$filter[BLOCK_EXACOMP_TYPE_SUBJECT]['visible'] = true;
                @$filter[BLOCK_EXACOMP_TYPE_TOPIC]['visible'] = true;
                @$filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR_PARENT]['visible'] = true;
                //@$filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR]['visible'] = true;
            }

            if (@$filter['type'] != 'student_counts') {
                $filter['type'] = 'students';
            }
    }

    //    if (@$filter[BLOCK_EXACOMP_TYPE_EXAMPLE]['visible']) {
    //        @$filter[BLOCK_EXACOMP_TYPE_EXAMPLE]['active'] = true;
    //    }
    //
    //    if (@$filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR_CHILD]['visible']) {
    //        @$filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR_CHILD]['active'] = true;
    //    }
    //    if (@$filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR_PARENT]['visible']) {
    //        @$filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR_PARENT]['active'] = true;
    //    }
    //    //if (@$filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR]['visible'] || @$filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR_CHILD]['active']) {
    //    //    @$filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR]['active'] = true;
    //    //}
    //    if (@$filter[BLOCK_EXACOMP_TYPE_TOPIC]['visible'] ) {
    //        @$filter[BLOCK_EXACOMP_TYPE_TOPIC]['active'] = true;
    //    }
    //    if (@$filter[BLOCK_EXACOMP_TYPE_SUBJECT]['visible']) {
    //        @$filter[BLOCK_EXACOMP_TYPE_SUBJECT]['active'] = true;
    //    }

    // active means, we also have to loop over those items.... visible does not mean active, rework, 17.07.2019 RW
    if (@$filter[BLOCK_EXACOMP_TYPE_EXAMPLE]['visible']) {
        @$filter[BLOCK_EXACOMP_TYPE_EXAMPLE]['active'] = true;
    } else {
        @$filter[BLOCK_EXACOMP_TYPE_EXAMPLE] = array();
    }

    if (@$filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR_CHILD]['visible'] || @$filter[BLOCK_EXACOMP_TYPE_EXAMPLE]['active']) {
        if (!@$filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR_CHILD]['visible']) {
            //if it has only been set active because a lower level is active, then clear the settings (the settings are not visible so they should not have an effect)
            @$filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR_CHILD] = array();
        }
        @$filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR_CHILD]['active'] = true;
    }
    if (@$filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR_PARENT]['visible'] || @$filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR_CHILD]['active']) {
        if (!@$filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR_PARENT]['visible']) {
            //if it has only been set active because a lower level is active, then clear the settings (the settings are not visible so they should not have an effect)
            @$filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR_PARENT] = array();
        }
        @$filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR_PARENT]['active'] = true;
    }
    //if (@$filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR]['visible'] || @$filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR_CHILD]['active']) {
    //    @$filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR]['active'] = true;
    //}
    if (@$filter[BLOCK_EXACOMP_TYPE_TOPIC]['visible'] || @$filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR_PARENT]['active']) {
        if (!@$filter[BLOCK_EXACOMP_TYPE_TOPIC]['visible']) {
            //if it has only been set active because a lower level is active, then clear the settings (the settings are not visible so they should not have an effect)
            @$filter[BLOCK_EXACOMP_TYPE_TOPIC] = array();
        }
        @$filter[BLOCK_EXACOMP_TYPE_TOPIC]['active'] = true;
    }
    if (@$filter[BLOCK_EXACOMP_TYPE_SUBJECT]['visible'] || @$filter[BLOCK_EXACOMP_TYPE_TOPIC]['active']) {
        if (!@$filter[BLOCK_EXACOMP_TYPE_SUBJECT]['visible']) {
            //if it has only been set active because a lower level is active, then clear the settings (the settings are not visible so they should not have an effect)
            @$filter[BLOCK_EXACOMP_TYPE_SUBJECT] = array();
        }
        @$filter[BLOCK_EXACOMP_TYPE_SUBJECT]['active'] = true;
    }

    // removes all NULL, FALSE and Empty Strings but leaves 0 (zero) values
    $filter = block_exacomp_array_filter_recursive($filter, function($value) {
        return ($value !== null && $value !== false && $value !== '');
    });

    return $filter;
}

function block_exacomp_array_filter_recursive(array $array, callable $callback = null) {
    $array = is_callable($callback) ? array_filter($array, $callback) : array_filter($array);
    foreach ($array as &$value) {
        if (is_array($value)) {
            $value = call_user_func(__FUNCTION__, $value, $callback);
        }
    }
    return $array;
}

function block_exacomp_tree_walk(&$items, $data, $callback) {
    $args = func_get_args();
    array_shift($args);
    array_shift($args);
    array_shift($args);

    foreach ($items as $key => $item) {
        $walk_subs = function() use ($item, $data, $callback) { // does not enter this if timespan is set
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
        //something goes wrong here. The only difference is in the $callback
        //but if timespan is set, it just will not enter the function

        if ($ret === false) {
            unset($items[$key]);
        }
    }
}

function block_exacomp_group_reports_result($filter, $isPdf, $isTeacher) {

    $content = block_exacomp_group_reports_return_result($filter, $isPdf, $isTeacher);
    if ($isPdf) {
        \block_exacomp\printer::group_report($content);
    } else {
        echo $content;
    }
}

function block_exacomp_group_reports_return_result($filter, $isPdf, $isTeacher) {
    global $USER;
    $courseid = g::$COURSE->id;

    $coursestudents = block_exacomp_get_students_by_course($courseid);
    $students = array();

    if ($isTeacher) {
        $students = $coursestudents;
    } else {
        $students[$USER->id] = $coursestudents[$USER->id];
    }

    $html = '';

    if ($filter['type'] == 'students') {
        $has_output = false;

        if ($filter['selectedStudentOrGroup'] != 0) {
            if ($filter['selectedStudentOrGroup'] < -1 && $isTeacher) { //then it is a group, calculate encoded groupid by (-1)*selectedStudentOrGroup - 1
                $groupid = (-1) * $filter['selectedStudentOrGroup'] - 1;
                $students = block_exacomp_groups_get_members($courseid, $groupid);
            } else {
                $students = array($students[$filter['selectedStudentOrGroup']]);
            }
        }
        $i = 0;
        foreach ($students as $student) {
            $i++;
            $studentid = $student->id;

            $subjects = \block_exacomp\db_layer_course::create($courseid)->get_subjects();

            //            $filter['time'] = null;
            block_exacomp_tree_walk($subjects, ['filter' => $filter], function($walk_subs, $item, $level = 0) use ($studentid, $courseid, $filter) {
                $eval = block_exacomp_get_comp_eval_merged($courseid, $studentid, $item);
                $item_type = $item::TYPE;

                if ($item_type == BLOCK_EXACOMP_TYPE_DESCRIPTOR) {
                    $item_type = $level > 2 ? BLOCK_EXACOMP_TYPE_DESCRIPTOR_CHILD : BLOCK_EXACOMP_TYPE_DESCRIPTOR_PARENT;
                }
                $item_scheme = block_exacomp_additional_grading($item_type, $courseid); //this has to be done AFTER specifying the item type of course, otherwise always the scheme of the parent descriptor will be taken

                $item_filter = (array)@$filter[$item_type];

                $item->visible = @$item_filter['visible'];

                if (!@$item_filter['active']) {
                    return false;
                }

                $filter_result = block_exacomp_group_reports_annex_result_filter_rules($item_type, $item_scheme, $filter, $eval);

                //                var_dump($filter_result);
                //                var_dump(@$filter['time']);
                //                die;

                if (!$filter_result) {
                    return false;
                }

                if (@$filter['time']['active'] && @$filter['time']['from'] && $eval->timestampteacher < @$filter['time']['from']) {
                    $item->visible = false;
                }
                if (@$filter['time']['active'] && @$filter['time']['to'] && $eval->timestampteacher > @$filter['time']['to']) {
                    $item->visible = false;
                }

                $walk_subs($level + 1);

                //RW dont do all this so e.g. the subjects dont get removed just because they dont have any topics under them
                //				$filter_active = $item_filter;
                //				unset($filter_active['active']);
                //				unset($filter_active['visible']);
                //				$filter_active = array_filter($filter_active, function($value) { return !empty($value); });
                //				$filter_active = !!$filter_active;
                //
                //				if (!$filter_active) {
                //					if ($item instanceof \block_exacomp\subject && !$item->topics) {
                //						return false;
                //					}
                //					if ($item instanceof \block_exacomp\topic && !$item->descriptors) {
                //						return false;
                //					}
                //					if ($item instanceof \block_exacomp\descriptor && !$item->children && !$item->examples) {
                //						return false;
                //					}
                //				}
            });

            ob_start();
            block_exacomp_tree_walk($subjects, ['filter' => $filter], function($walk_subs, $item, $level = 0) use ($studentid, $courseid, $filter, $html, $isPdf) {
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
                } else if ($item_type == BLOCK_EXACOMP_TYPE_TOPIC) {
                    echo '<tr class="exarep_topic_row">';
                } else if ($item_type == BLOCK_EXACOMP_TYPE_DESCRIPTOR && $level <= 2) {
                    $item_type = BLOCK_EXACOMP_TYPE_DESCRIPTOR_PARENT; // ITEM_TYPE needs to be child or parent, not just Descriptor for block_exacomp_additional_grading to work
                    echo '<tr class="exarep_descriptor_parent_row">';
                } else if ($item_type == BLOCK_EXACOMP_TYPE_DESCRIPTOR && $level > 2) {
                    $item_type = BLOCK_EXACOMP_TYPE_DESCRIPTOR_CHILD;
                    echo '<tr class="exarep_descriptor_child_row">';
                } else if ($item_type == BLOCK_EXACOMP_TYPE_EXAMPLE) {
                    echo '<tr class="exarep_example_row">';
                }

                $item_scheme = block_exacomp_additional_grading($item_type, $courseid);

                echo '<td class="exarep_descriptor" width="4%" style="white-space: nowrap;">' . $item->get_numbering() . '</td>';
                echo '<td class="exarep_descriptorText" width="' . ($isPdf ? '50%' : '65%') . '" style="padding-left: ' . (5 + $level * 15) . 'px;">' .
                    ($isPdf ? str_repeat('&nbsp;&nbsp;&nbsp;', $level) : '') .
                    $item->title .
                    '</td>';

                if (@$filter['time']['active']) {
                    echo '<td width="5%" class="timestamp">' . ($eval->timestampteacher ? date('d.m.Y', $eval->timestampteacher) : '') . '</td>';
                    //$html .= '<td class="timestamp">'.($eval->timestampteacher ? date('d.m.Y', $eval->timestampteacher) : '').'</td>';
                }
                echo '<td class="exarep_studentAssessment" width="15%" style="padding: 0 10px;">' . $eval->get_student_value_title($item::TYPE) . '</td>';
                echo '<td class="exarep_teacherAssessment" width="15%" style="padding: 0 10px;">';
                switch ($item_scheme) {
                    case BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE:
                        echo block_exacomp_format_eval_value($eval->additionalinfo);
                        break;
                    case BLOCK_EXACOMP_ASSESSMENT_TYPE_POINTS:
                        echo block_exacomp_format_eval_value($eval->teacherevaluation);
                        break;
                    case BLOCK_EXACOMP_ASSESSMENT_TYPE_VERBOSE:
                        $value = @$eval->teacherevaluation === null ? -1 : @$eval->teacherevaluation;
                        $teacher_eval_items = \block_exacomp\global_config::get_teacher_eval_items($courseid, false, BLOCK_EXACOMP_ASSESSMENT_TYPE_VERBOSE);
                        if (isset($teacher_eval_items[$value])) {
                            echo $teacher_eval_items[$value];
                        }
                        break;
                    case BLOCK_EXACOMP_ASSESSMENT_TYPE_YESNO:
                        $value = @$eval->teacherevaluation === null ? -1 : @$eval->teacherevaluation;
                        if ($value > 0) {
                            echo 'V';
                        }
                        break;
                }
                echo '</td>';
                //				echo '<td class="exarep_exa_evaluation" width="10%" style="padding: 0 10px;">'.$eval->get_teacher_value_title().'</td>'; // remove? RW
                echo '<td class="exarep_difficultyLevel" width="10%" style="padding: 0 10px;">' . $eval->get_evalniveau_title() . '</td>';
                echo '</tr>';
                $walk_subs($level + 1);
            });
            $output = ob_get_clean();

            if ($output) {
                $has_output = true;

                // 				echo '<h3>'.fullname($student).'</h3>';
                // 				echo '<table class="report_table" border="1" width="100%">';
                // 				echo '<thead><th style="width: 4%"></th><th style="width: 65%"></th>';
                if ($i != 1) {
                    $html .= '<br pagebreak="true"/>';
                }
                $html .= '<h3>' . fullname($student) . '</h3>';
                $html .= '<table class="report_table" border="1" width="100%">';
                $html .= '<thead>';
                $html .= '<tr>';
                $html .= '<th width="4%" ></th>';
                $html .= '<th width="' . ($isPdf ? '50%' : '65%') . '" ></th>';
                if (@$filter['time']['active']) {
                    // 				    echo '<th>'.block_exacomp_get_string('assessment_date').'</th>';
                    $html .= '<th width="5%">' . block_exacomp_get_string('assessment_date') . '</th>';
                }
                //echo html_writer::tag('th',block_exacomp_get_string('output_current_assessments'),array('colspan' => "4"));
                // 				echo '<th colspan="4">'.block_exacomp_get_string('output_current_assessments').'</th>';
                // 				echo '<tr>';
                //                 echo '<th class="heading"></th>';
                //                 echo '<th class="heading"></th>';
                //                 echo '<th class="heading" class="studentAssessment">'.block_exacomp_get_string('student_assessment').'</th>';
                //                 echo '<th class="heading" class="teacherAssessment">'.block_exacomp_get_string('teacher_assessment').'</th>';
                //                 echo '<th class="heading" class="exa_evaluation">'.block_exacomp_get_string('exa_evaluation').'</th>';
                //                 echo '<th class="heading"class="difficultyLevel">'.block_exacomp_get_string('difficulty_group_report').'</th>';
                //                 echo "<tbody>";
                //                 echo $output;
                // 				echo '</table>';

                $html .= '<th width="40%" colspan="' . (@$filter['time']['active'] ? 5 : 4) . '">' . block_exacomp_get_string('output_current_assessments') . '</th>';
                $html .= '</tr>';
                $html .= '<tr>';
                $html .= '<th class="heading"></th>';
                $html .= '<th width="50%" class="heading"></th>';
                if (@$filter['time']['active']) {
                    $html .= '<th width="5%" class="heading"></th>';
                }
                $html .= '<th width="15%" class="heading studentAssessment">' . block_exacomp_get_string('student_assessment') . '</th>';
                $html .= '<th width="15%" class="heading teacherAssessment">' . block_exacomp_get_string('teacher_assessment') . '</th>';
                //				$html .= '<th width="10%" class="heading exa_evaluation">'.block_exacomp_get_string('exa_evaluation').'</th>'; //remove? RW
                $html .= '<th width="10%" class="heading difficultyLevel">' . block_exacomp_get_string('difficulty_group_report') . '</th>';
                $html .= '</tr>';
                $html .= '</thead>';
                $html .= "<tbody>";
                $html .= $output;
                $html .= "</tbody>";
                $html .= '</table>';
            }
        }

        if (!$has_output) {
            // 			echo block_exacomp_get_string('no_entries_found');
            $html .= block_exacomp_get_string('no_entries_found');
        }
    }

    if ($filter['type'] == 'student_counts') {
        $subjects = \block_exacomp\db_layer_course::create($courseid)->get_subjects();

        // 		echo '<table>';
        // 		echo '<tr><th></th><th></th><th colspan="3">'.block_exacomp_get_string('number_of_found_students').' ('.count($students).')</th>';
        $html .= '<table>';
        $html .= '<tr><th></th><th></th><th colspan="3">' . block_exacomp_get_string('number_of_found_students') . ' (' . count($students) . ')</th></tr>';
        //        echo '<table>';
        //        echo '<tr><th></th><th></th><th colspan="3">'.block_exacomp_get_string('number_of_found_students').' ('.count($students).')</th></tr>';

        ob_start();
        block_exacomp_tree_walk($subjects, ['filter' => $filter], function($walk_subs, $item, $level = 0) use ($courseid, $filter, $students, $html) {
            $item_type = $item::TYPE;
            $item_scheme = block_exacomp_additional_grading($item_type, $courseid);
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

                    $filter_result = block_exacomp_group_reports_annex_result_filter_rules($item_type, $item_scheme, $filter, $eval);
                    if (!$filter_result) {
                        continue;
                    }

                    $count++;
                }

                echo '<tr>';
                echo '<td style="white-space: nowrap">' . $item->get_numbering() . '</td>';
                echo '<td style="padding-left: ' . (5 + $level * 15) . 'px">' . $item->title . '</td>';
                echo '<td style="padding: 0 10px;">' . $count . '</td>';
                echo '</tr>';

                //                $html .= '<tr>';
                //                $html .= '<td style="white-space: nowrap">'.$item->get_numbering().'</td>';
                //                $html .= '<td style="padding-left: '.(5 + $level * 15).'px">'.$item->title.'</td>';
                //                $html .= '<td style="padding: 0 10px;">'.$count.'</td>';
                //                $html .= '</tr>';
            }

            $walk_subs($level + 1);
        });
        $output = ob_get_clean();
        if ($output) {
            $html .= $output;
        }

        //        echo '</table>';
        $html .= '</table>';

    }

    return $html;
}

/**
 * @param integer $item_type
 * @param integer $item_scheme
 * @param array $filter
 * @param $eval
 * @return bool
 */
function block_exacomp_group_reports_annex_result_filter_rules($item_type, $item_scheme, $filter, $eval) {
    $item_filter = (array)@$filter[$item_type];
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
    switch ($item_scheme) {
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE:
            $value = @$eval->additionalinfo ?: 0;
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_POINTS:
            if ($item_scheme != BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE) {
                $value = @$eval->teacherevaluation ?: 0;
            }
            if (@$item_filter['teacherevaluation_from']) {
                if ($value < str_replace(',', '.', $item_filter['teacherevaluation_from'])) {
                    return false;
                }
            }
            if (@$item_filter['teacherevaluation_to']) {
                if ($value > str_replace(',', '.', $item_filter['teacherevaluation_to'])) {
                    return false;
                }
            }
            break;
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_VERBOSE:
            $value = @$eval->teacherevaluation === null ? -1 : @$eval->teacherevaluation;
            $teacher_eval_items = \block_exacomp\global_config::get_teacher_eval_items(g::$COURSE->id, false, BLOCK_EXACOMP_ASSESSMENT_TYPE_VERBOSE);
            if (@$item_filter['teacherevaluation'] && is_array($item_filter['teacherevaluation']) && count($item_filter['teacherevaluation']) > 0) {
                if (!isset($teacher_eval_items[$value]) || !in_array($value, $item_filter[BLOCK_EXACOMP_EVAL_INPUT_TACHER_EVALUATION])) {
                    return false;
                }
            }
            break;
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_YESNO:
            //$value = $DB->get_record(BLOCK_EXACOMP_DB_COMPETENCES, array("compid" => $item->id, "role" => BLOCK_EXACOMP_ROLE_TEACHER, 'courseid' => $courseid, 'userid' => $studentid, 'comptype' => BLOCK_EXACOMP_TYPE_DESCRIPTOR));
            $value = @$eval->teacherevaluation === null ? -1 : @$eval->teacherevaluation;
            if (@$item_filter['teacherevaluation'] && is_array($item_filter['teacherevaluation']) && count($item_filter['teacherevaluation']) > 0) {
                if (!in_array($value, $item_filter[BLOCK_EXACOMP_EVAL_INPUT_TACHER_EVALUATION])) {
                    return false;
                }
            }
    }

    /*if (@$item_filter[BLOCK_EXACOMP_EVAL_INPUT_TACHER_EVALUATION]) {
        $value = @$eval->teacherevaluation === null ? -1 : @$eval->teacherevaluation;
        if (!in_array($value, $item_filter[BLOCK_EXACOMP_EVAL_INPUT_TACHER_EVALUATION])) {
            return false;
        }
    }*/

    if (@$item_filter[BLOCK_EXACOMP_EVAL_INPUT_STUDENT_EVALUATION]) {
        $value = @$eval->studentevaluation ?: 0;
        if (!in_array($value, $item_filter[BLOCK_EXACOMP_EVAL_INPUT_STUDENT_EVALUATION])) {
            return false;
        }
    }

    //    var_dump( $eval);

    if (@$filter['time']['active'] && @$filter['time']['from'] && $eval->timestampteacher != null && $eval->timestampteacher < @$filter['time']['from']) {
        return false;
    }
    if (@$filter['time']['active'] && @$filter['time']['to'] && $eval->timestampteacher != null && $eval->timestampteacher > @$filter['time']['to']) {
        return false;
    }

    return true;
}

function block_exacomp_group_reports_annex_result($filter) {
    $courseid = g::$COURSE->id;
    $students = block_exacomp_get_students_by_course($courseid);

    //print_r($filter);
    $has_output = false;
    $isDocx = (bool)optional_param('formatDocx', false, PARAM_RAW);
    $isPdf = (bool)optional_param('formatPdf', false, PARAM_RAW);
    $dataRow = array();

    if ($isPdf) {
        ob_start();
    }

    if ($filter['selectedStudentOrGroup'] != 0) {
        if ($filter['selectedStudentOrGroup'] < -1) { //then it is a group, calculate encoded groupid by (-1)*selectedStudentOrGroup - 1
            $groupid = (-1) * $filter['selectedStudentOrGroup'] - 1;
            $students = block_exacomp_groups_get_members($courseid, $groupid);
        } else {
            $students = array($students[$filter['selectedStudentOrGroup']]);
        }
    }
    $i = 0;
    foreach ($students as $student) {
        $i++;
        $studentid = $student->id;

        $subjects = \block_exacomp\db_layer_course::create($courseid)->get_subjects();
        block_exacomp_tree_walk($subjects, ['filter' => $filter], function($walk_subs, $item, $level = 0) use ($studentid, $courseid, $filter) {
            $eval = block_exacomp_get_comp_eval_merged($courseid, $studentid, $item);

            $item_type = $item::TYPE;
            $item_scheme = block_exacomp_additional_grading($item_type, $courseid);
            if ($item_type == BLOCK_EXACOMP_TYPE_DESCRIPTOR) {
                $item_type = $level > 2 ? BLOCK_EXACOMP_TYPE_DESCRIPTOR_CHILD : BLOCK_EXACOMP_TYPE_DESCRIPTOR_PARENT;
            }

            $item_filter = (array)@$filter[$item_type];
            $item->visible = @$item_filter['visible'];

            if (!@$item_filter['active']) {
                return false;
            }
            $filter_result = block_exacomp_group_reports_annex_result_filter_rules($item_type, $item_scheme, $filter, $eval);
            if (!$filter_result) {
                return false;
            }

            $item->evaluation = $eval;
            $walk_subs($level + 1);
        });

        //echo '<pre>';print_r($subjects); echo '<pre>';

        // count of columns
        //$colCount = block_exacomp_get_grading_scheme($courseid);
        $colCount = block_exacomp_get_report_columns_count_by_assessment($courseid);
        $startColumn = 0;

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

            if ($i != 1) {
                if ($isPdf) {
                    echo '<br pagebreak="true"/>';
                } else {
                    echo '<hr>';
                }
            }
            echo '<h1 class="toCenter">' . block_exacomp_get_string('tab_teacher_report_annex_title') . '</h1>';
            echo '<h2 class="toCenter">' . fullname($student) . '</h2>';
            echo '<h3 class="toCenter">' . g::$COURSE->fullname . '</h3>';

            $firstSubject = true;
            $has_subject_results = false;

            ob_start();

            block_exacomp_tree_walk($subjects, ['filter' => $filter], function($walk_subs, $item, $level = 0) use (
                $studentid, $courseid, $filter, $colCount, &$firstSubject, &$has_subject_results, $startColumn
            ) {
                $eval = block_exacomp_get_comp_eval_merged($courseid, $studentid, $item);

                $tableHeader = function(&$has_subject_results, &$firstSubject, $item, $colCount, $startColumn, $courseid) {
                    $has_subject_results = false;
                    // table wrapping with Subject title
                    if (!$firstSubject) {
                        echo "</tbody>";
                        echo '</table>';
                    } else {
                        $firstSubject = false;
                    }
                    echo '<br><h3>' . $item->title . '</h3>';
                    echo '<table class="report_table" border="1" width="100%" style="margin-bottom: 25px;">';
                    echo '<thead>';
                    echo '<tr>';
                    echo '<th class="heading">' . block_exacomp_get_string('descriptor') . '</th>';
                    echo '<th class="heading toCenter">' . block_exacomp_get_string('taxonomy') . '</th>';
                    // FIRST WAY: 1 column with value:
                    //echo '<th class="heading"></th>';
                    // SECOND WAY: 4 columns
                    //for ($i = 0; $i <= $colCount; $i++) {
                    //    echo '<th class="heading">'.$i.'</th>';
                    //}
                    // THIRD WAY: columns by selected grading system (grading for competences)
                    for ($i = $startColumn; $i < $colCount; $i++) {
                        echo '<th class="heading toCenter">';
                        switch (block_exacomp_get_assessment_comp_scheme($courseid)) {
                            case BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE:
                                echo $i;
                                break;
                            case BLOCK_EXACOMP_ASSESSMENT_TYPE_VERBOSE:
                                $titles = preg_split("/(\/|,) /", block_exacomp_get_assessment_verbose_options($courseid));
                                echo $titles[$i];
                                break;
                            case BLOCK_EXACOMP_ASSESSMENT_TYPE_POINTS:
                                echo $i;
                                break;
                            case BLOCK_EXACOMP_ASSESSMENT_TYPE_YESNO:
                                echo $i == 1 ? block_exacomp_get_string('yes_no_Yes') : block_exacomp_get_string('yes_no_No');
                                break;
                        }
                        echo '</th>';
                    }

                    echo '</tr></thead>';
                    echo "<tbody>";
                };

                //item_type is needed to distinguish between topics, parent descripors and child descriptors --> important for css-styling
                $item_type = $item::TYPE;
                $item_scheme = block_exacomp_additional_grading($item_type, $courseid);

                if (!$item->visible) {
                    if ($item_type == BLOCK_EXACOMP_TYPE_SUBJECT) {
                        $tableHeader($has_subject_results, $firstSubject, $item, $colCount, $startColumn);
                    }
                    // walk subs with same level
                    $walk_subs($level);
                    return;
                }
                $filter_result = block_exacomp_group_reports_annex_result_filter_rules(($item_type == BLOCK_EXACOMP_TYPE_DESCRIPTOR ? BLOCK_EXACOMP_TYPE_DESCRIPTOR_PARENT : $item_type), $item_scheme, $filter, $eval);
                if (!$filter_result) {
                    return false;
                }

                $selectedEval = block_exacomp_report_annex_get_selectedcolumn_by_assessment_type($item_scheme, $eval, $courseid);

                if ($selectedEval != '' || $item_type == BLOCK_EXACOMP_TYPE_SUBJECT) {
                    switch ($item_type) {
                        case BLOCK_EXACOMP_TYPE_SUBJECT:
                            $tableHeader($has_subject_results, $firstSubject, $item, $colCount, $startColumn);
                            echo '<tr class="exarep_subject_row">';
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

                    if (1 == 1 || $item_type != BLOCK_EXACOMP_TYPE_SUBJECT) {
                        $has_subject_results = true;
                        echo '<td class="exarep_descriptorText" style="padding-left: ' . (5 + $level * 15) . 'px">' .
                            $item->get_numbering() . ' ' . $item->title . '</td>';
                        //echo '<pre>'.print_r($item,true).'</pre>';
                        echo '<td class="toCenter" style="padding: 0 10px;">' . $eval->get_evalniveau_title() . '</td>';
                        // FIRST WAY: 1 column with value:
                        //echo '<td style="padding: 0 10px;">'.$selectedEval.'</td>';
                        // SECOND WAY: 4 columns
                        /*for ($i = 0; $i <= $colCount; $i++) {
                            echo '<td style="padding: 0 10px;">';
                            if ($selectedEval == $i) {
                                echo 'X';
                            }
                            echo '</td>';
                        }*/
                        // THIRD WAY: columns by selected grading system (grading for competences)
                        for ($i = $startColumn; $i < $colCount; $i++) {
                            echo '<td class="toCenter" style="padding: 0 10px;">';
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
            ob_end_flush();
            echo "</tbody>";
            echo '</table>';
            if (!$has_subject_results) {
                echo block_exacomp_get_string('no_entries_found');
            }
        }

    }

    if ($isPdf) {
        ob_end_flush();
        $html_content = ob_get_clean();
        \block_exacomp\printer::block_exacomp_generate_report_annex_pdf($html_content);
        exit;
    }

    if ($isDocx) {
        \block_exacomp\printer::block_exacomp_generate_report_annex_docx($courseid, $dataRow);
        exit;
    }

}

function block_exacomp_group_reports_profoundness_result($filter) {
    $courseid = g::$COURSE->id;
    $students = block_exacomp_get_students_by_course($courseid);
    foreach ($students as $student) {
        $student = block_exacomp_get_user_information_by_course($student, $courseid);
    }

    //print_r($filter);
    $has_output = false;
    $isDocx = (bool)optional_param('formatDocx', false, PARAM_RAW);
    $isPdf = (bool)optional_param('formatPdf', false, PARAM_RAW);
    $dataRow = array();

    if ($filter['selectedStudentOrGroup'] != 0) {
        if ($filter['selectedStudentOrGroup'] < -1) { //then it is a group, calculate encoded groupid by (-1)*selectedStudentOrGroup - 1
            $groupid = (-1) * $filter['selectedStudentOrGroup'] - 1;
            $students = block_exacomp_groups_get_members($courseid, $groupid);
        } else {
            $students = array($students[$filter['selectedStudentOrGroup']]);
        }
    }
    $i = 0;

    $subjects = block_exacomp_get_competence_tree($courseid, null, null, false, null, false);
    $output = block_exacomp_get_renderer();
    $reportcontent = '';

    $reportcontent .= '<style>
            .exabis_comp_comp, .exabis_comp_comp .highlight {
                color: #000000 !important;
            }
    </style>';

    foreach ($students as $student) {

        $studentstemp = array($student);

        if ($i != 0) {
            $reportcontent .= '<br pagebreak="true"/>';
        }

        $reportcontent .= '<h1 class="toCenter">' . block_exacomp_get_string('tab_teacher_report_profoundness_title') . '</h1>';
        $reportcontent .= '<h2 class="toCenter">' . fullname($student) . '</h2>';
        $reportcontent .= '<h3 class="toCenter">' . g::$COURSE->fullname . '</h3>';

        $reportcontent .= $output->profoundness($subjects, $courseid, $studentstemp, BLOCK_EXACOMP_ROLE_TEACHER, true);

        $i++;

    }

    if ($isPdf) {
        \block_exacomp\printer::block_exacomp_generate_report_profoundness_pdf($reportcontent);
        exit;
    }

    echo $reportcontent;

    /*    if ($isDocx) {
        \block_exacomp\printer::block_exacomp_generate_report_annex_docx($courseid, $dataRow);
        exit;
    }*/

}

function block_exacomp_formulaColumnByValue($maxColumn = 4, $minColumn = 1, $maxValue = 6, $minValue = 1, $value = 0) {
    // formula for additionalinfo (grading by value)
    // Y = aX + B
    // y = grading by 1-3 (as in the report columns)
    // x - grading from input field from competences overview: 1-6
    // a = d2/d1
    // b = 1 - a
    // d1 = maxValue(by X) - minValue (we have 1)
    // d2 = maxValue(by Y) - 1 (we have 3-1 = 2)
    $d1 = $maxValue - $minValue;
    $d2 = $maxColumn - $minColumn;
    if ($d1 > 0) {
        $a = $d2 / $d1;
    } else {
        $a = 1;
    }
    $b = 1 - $a; // TODO: check if the mins are not 1
    $result = ($a * $value) + $b;
    return round($result);
}

;

/**
 * returns count of columns in annex report. Regarding to grading systems
 * work with competence grading system. (TODO: is it ok?)
 *
 * @return int
 */
function block_exacomp_get_report_columns_count_by_assessment($courseid = 0) {
    switch (block_exacomp_get_assessment_comp_scheme($courseid)) {
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_NONE:
            $count = 0;
            break;
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE:
            // first column with ZERO!
            $count = block_exacomp_get_assessment_grade_limit($courseid);
            $count++;
            break;
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_VERBOSE:
            $conf = block_exacomp_get_assessment_verbose_options(null, $courseid);
            if ($conf == '') {
                $count = 0;
            } else {
                $titles = preg_split("/(\/|,) /", $conf);
                $count = count($titles);
            }
            break;
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_POINTS:
            $count = block_exacomp_get_assessment_points_limit(null, $courseid);
            $count++; // With zero
            break;
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_YESNO:
            $count = 2;
            break;
        default:
            $count = 4; // default (old) value
    }
    return $count;
}

function block_exacomp_report_annex_get_selectedcolumn_by_assessment_type($item_scheme, $eval, $courseid = 0) {
    $selectedEval = null;
    $column_count = block_exacomp_get_report_columns_count_by_assessment($courseid);
    switch ($item_scheme) {
        // FIRST way
        // we have ONE column and put value of evaluation, but not 'X' in the cells
        /*case BLOCK_EXACOMP_ASSESSMENT_TYPE_NONE:
            $selectedEval = '';
            break;
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE:
            if ($eval->additionalinfo >= 1) {
                $selectedEval = $eval->additionalinfo;
            } else {
                $selectedEval = 0;
            }
            break;
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_VERBOSE:
            $verboseTitles = array_map('trim', explode(',', block_exacomp_get_assessment_verbose_options()));
            if (isset($verboseTitles[$eval->teacherevaluation])) {
                $selectedEval = $verboseTitles[$eval->teacherevaluation];
            }
            break;
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_POINTS:
            if ($eval->teacherevaluation >= 1) {
                $selectedEval = $eval->teacherevaluation;
            } else {
                $selectedEval = 0;
            }
            break;
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_YESNO:
            if ($eval->teacherevaluation >= 1) {
                $selectedEval = 'X';
            } else {
                $selectedEval = '';
            }
            break;*/
        // SECOND way
        // if we have always 4 columns in the template - use formula to calculate where is 'X'
        /*case BLOCK_EXACOMP_ASSESSMENT_TYPE_NONE:
            $selectedEval = '';
            break;
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE:
            if ($eval->additionalinfo >= 1) {
                $selectedEval = block_exacomp_formulaColumnByValue($column_count, 1, block_exacomp_get_assessment_grade_limit(), 1, $eval->additionalinfo);
            } else {
                $selectedEval = 0;
            }
            break;
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_VERBOSE:
            $verboseTitles = array_map('trim', explode(',', block_exacomp_get_assessment_verbose_options()));
            $maxVerboses = count($verboseTitles) - 1;
            if (isset($verboseTitles[$eval->teacherevaluation])) {
                $selectedEval = block_exacomp_formulaColumnByValue($column_count, 1, $maxVerboses, 1, $eval->teacherevaluation);
            }
            break;
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_POINTS:
            $maxPoints = block_exacomp_get_assessment_points_limit();
            if ($eval->teacherevaluation >= 1) {
                $selectedEval = block_exacomp_formulaColumnByValue($column_count, 1, $maxPoints, 1, $eval->teacherevaluation);
            } else {
                $selectedEval = 0;
            }
            break;
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_YESNO:
            if ($eval->teacherevaluation >= 1) {
                $selectedEval = $column_count; // max column index
            } else {
                $selectedEval = 0;
            }
            break;*/
        // THIRD way
        // columns by selected grading system (grading for competences)
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_NONE:
            $selectedEval = '';
            break;
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE:
            if ($eval->additionalinfo >= 1) {
                $selectedEval = $eval->additionalinfo;
            } else {
                $selectedEval = 0;
            }
            break;
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_VERBOSE:
            $selectedEval = $eval->teacherevaluation;
            break;
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_POINTS:
            if ($eval->teacherevaluation >= 1) {
                $selectedEval = $eval->teacherevaluation;
            } else {
                $selectedEval = 0;
            }
            break;
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_YESNO:
            if ($eval->teacherevaluation >= 1) {
                $selectedEval = 1;
            } else {
                $selectedEval = 0;
            }
            break;
    }
    return $selectedEval;
}

function block_exacomp_save_report_settings($courseid, $delete = false) {
    $fs = get_file_storage();
    if ($delete) {
        $fs->delete_area_files($courseid, 'block_exacomp', 'report_annex', 0);
    }
    if (isset($_FILES["templateDocx"]) && trim($_FILES["templateDocx"]['name']) != '') {
        // Prepare file record object
        $fileinfo = array(
            'contextid' => $courseid,
            'component' => 'block_exacomp',
            'filearea' => 'report_annex',
            'itemid' => 0,
            'filepath' => '/',
            'filename' => $_FILES["templateDocx"]['name']);

        $fs->delete_area_files($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'], $fileinfo['itemid']);
        $fs->create_file_from_pathname($fileinfo, $_FILES["templateDocx"]['tmp_name']);
    }
}

function block_exacomp_update_evaluation_niveau_tables($data = '', $option_type = 'niveau') {
    if ($data != '') {
        //$titles = explode(",",$data);
        $titles = preg_split("/(\/|,)/", $data);
        // array from 1, not from 0
        array_unshift($titles, null);
        unset($titles[0]);
    } else {
        //$evaluation_niveau = block_exacomp_evaluation_niveau_type();
        $evaluation_niveau = block_exacomp_get_assessment_diffLevel_options();
        if ($evaluation_niveau == '') {
            return;
        }
        $titles = preg_split("/(\/|,) /", $evaluation_niveau);
        // array from 1, not from 0
        array_unshift($titles, null);
        unset($titles[0]);
        if ($titles[1] == $evaluation_niveau) {
            return;
        }
        /*if ($evaluation_niveau == 1) {
			$titles = array(1 => 'G', 2 => 'M', 3 => 'E', 101 => 'Z');
		} elseif ($evaluation_niveau == 2) {
			$titles = array(1 => 'A', 2 => 'B', 3 => 'C');
		} elseif ($evaluation_niveau == 3) {
			$titles = array(1 => '1', 2 => '2', 3 => '3');
		} else {
			return;
		}*/
    }

    //g::$DB->delete_records(BLOCK_EXACOMP_DB_EVALUATION_NIVEAU,array('option_type'=>$option_type));
    g::$DB->delete_records(BLOCK_EXACOMP_DB_EVALUATION_NIVEAU);

    //fill table
    foreach ($titles as $id => $title) {
        $entry = new stdClass();
        $entry->title = $title;
        $entry->id = $id;
        $entry->option_type = $option_type;
        // to insert record with a specific id, use insert_record_raw and set $customsequence = true
        g::$DB->insert_record(BLOCK_EXACOMP_DB_EVALUATION_NIVEAU, $entry);
    }

    // insert fake descriptor 'free_materials'
    if (g::$DB->record_exists(BLOCK_EXACOMP_DB_DESCRIPTORS, ['id' => -1])) {
        // update record is it exists already
        g::$DB->execute(' UPDATE {' . BLOCK_EXACOMP_DB_DESCRIPTORS . '}
                                                    SET source = ' . BLOCK_EXACOMP_CUSTOM_CREATED_DESCRIPTOR . '
                                                    WHERE id = -1 ');
    } else {
        // insert new
        g::$DB->insert_record_raw(BLOCK_EXACOMP_DB_DESCRIPTORS,
            ['id' => -1, 'title' => 'free_materials', 'source' => BLOCK_EXACOMP_CUSTOM_CREATED_DESCRIPTOR], true, false, true);
    }
}

/**
 * @return array
 */
function block_exacomp_read_preconfigurations_xml() {
    global $CFG;
    $xmlresult = array();
    $path = $CFG->dirroot . '/blocks/exacomp/';
    $namexml = $path . 'settings_preconfiguration.xml';
    $xmlcontent = file_get_contents($namexml);
    $xmlarray = (array)simplexml_load_string($xmlcontent);
    if ($xmlarray && is_array($xmlarray) && $xmlarray['configOption']) {
        if (!is_array($xmlarray['configOption'])) {
            $configs = array($xmlarray['configOption']);
        } else {
            $configs = $xmlarray['configOption'];
        }
        foreach ($configs as $config) {
            $data = (array)$config;
            if ($data['@attributes']['id'] > 0) {
                $key = $data['@attributes']['id'];
            } else {
                $key = max(array_keys($xmlresult)) + 1;
            }
            unset($data['@attributes']);
            $xmlresult[$key] = $data;
        }
    }
    return $xmlresult;
}

function block_exacomp_get_preconfigparameters_list() {
    $xmlpreconfig = block_exacomp_read_preconfigurations_xml();
    $preconfigparameters = array();
    foreach ($xmlpreconfig as $id => $config) {
        unset($config['name']);
        $preconfigparameters = array_unique(array_merge($preconfigparameters, array_keys($config)));
    }
    return $preconfigparameters;
}

function block_exacomp_get_custom_profile_field_value($userid, $fieldname) {
    return g::$DB->get_field_sql("SELECT uid.data
			FROM {user_info_data} uid
			JOIN {user_info_field} uif ON uif.id=uid.fieldid
			WHERE uif.shortname=? AND uid.userid=?
			", [$fieldname, $userid]);
}

function block_exacomp_get_date_of_birth_as_timestamp($userid) {
    $str = trim(block_exacomp_get_custom_profile_field_value($userid, 'dateofbirth'));
    if (!$str) {
        return null;
    }
    $parts = preg_split('![^0-9]+!', $str);
    if (count($parts) != 3) {
        // wrong format
        return null;
    }

    return mktime(0, 0, 0, $parts[1], $parts[0], $parts[2]);
}

function block_exacomp_get_date_of_birth($userid) {
    $timestamp = block_exacomp_get_date_of_birth_as_timestamp($userid);
    if (!$timestamp) {
        return null;
    }
    return date('d.m.Y', $timestamp);
}

function block_exacomp_set_custom_profile_field_value($userid, $fieldname, $value) {
    $fieldid = g::$DB->get_field_sql("SELECT uif.id
			FROM {user_info_field} uif
			WHERE uif.shortname = ?
			", [$fieldname]);
    if ($fieldid > 0) {
        $exists = g::$DB->get_record('user_info_data', ['userid' => $userid, 'fieldid' => $fieldid], '*', IGNORE_MULTIPLE);
        if ($exists) {
            $exists->data = $value;
            $updated = g::$DB->update_record('user_info_data', $exists);
        } else {
            $data = new stdClass();
            $data->userid = $userid;
            $data->fieldid = $fieldid;
            $data->data = $value;
            $inserted = g::$DB->insert_record('user_info_data', $data);
        }
        return true;
    }
    return false;
}

//  /**
//  * @param unknown $descriptorid
//  * @param unknown $studentid
//  * @return boolean    true if there exists a childcomp grading that is newer than the parentgrading
//  */
//  function block_exacomp_is_descriptor_grading_old($descriptorid, $studentid){

//      global $CFG, $DB;

//      $query = 'SELECT gradings.id, descriptors.parentid
//  	  FROM {'.BLOCK_EXACOMP_DB_COMPETENCES.'} gradings
//  	  JOIN {'.BLOCK_EXACOMP_DB_DESCRIPTORS.'} descriptors ON gradings.compid = descriptors.id
//       WHERE descriptors.parentid = ? AND gradings.userid = ?  AND gradings.timestamp >
//         ((SELECT MAX(timestamp)
//          FROM {'.BLOCK_EXACOMP_DB_COMPETENCES.'}
//          WHERE compid = ? AND userid = ?))';
//      //+30 sec weil innerhalb von 30 Sekunden die Ladezeit beim Hochladen sein kann??

//      $condition = array($descriptorid, $studentid, $descriptorid, $studentid);

//      $results = $DB->get_records_sql($query, $condition);

//      return $results != null;
//  }

/**
 * get all examples for a crosssubject
 *
 * @param unknown $crosssubjectid
 */
function block_exacomp_get_examples_for_crosssubject($crosssubjectid) {
    global $DB;

    $examples = \block_exacomp\example::get_objects_sql(
        "SELECT DISTINCT mm.id as deid, e.id, e.title, e.externalurl, e.source, e.sourceid,
			e.externalsolution, e.externaltask, e.completefile, e.description, e.creatorid, e.iseditable, e.tips, e.timeframe, e.author, e.courseid
			, mm.sorting, mm.id_foreign
			FROM {" . BLOCK_EXACOMP_DB_EXAMPLES . "} e
			JOIN {" . BLOCK_EXACOMP_DB_DESCEXAMP . "} mm ON e.id=mm.exampid AND mm.id_foreign=? AND mm.table_foreign='cross'"
        , array($crosssubjectid));

    return $examples;
}

/**
 * delete all examples that have been created specifically for a crosssubject
 *
 * @param unknown $crosssubjectid
 */
function block_exacomp_delete_examples_for_crosssubject($crosssubjectid) {
    global $DB;

    $examples = block_exacomp_get_examples_for_crosssubject($crosssubjectid);
    foreach ($examples as $example) {
        $DB->delete_records(BLOCK_EXACOMP_DB_EXAMPLES, array('id' => $example->id));
        $DB->delete_records(BLOCK_EXACOMP_DB_DESCEXAMP, array('exampid' => $example->id, 'id_foreign' => $crosssubjectid, 'table_foreign' => 'cross'));
    }

    return $examples;
}

//  /**
//   * return descriptor with examples
//   * @param unknown $descriptor - is returned again
//   * @param array $filteredtaxonomies - only chosen taxonomies
//   * @param string $showallexamples - exclude external or not
//   * @param unknown $courseid
//   * @param string $mind_visibility - return visibie field
//   * @param string $showonlyvisible - return only visible
//   * @return unknown
//   */
//  function block_exacomp_get_examples_for_descriptor($descriptor, $filteredtaxonomies = array(BLOCK_EXACOMP_SHOW_ALL_TAXONOMIES), $showallexamples = true, $courseid = null, $mind_visibility = true, $showonlyvisible = false) {

/**
 * unset descriptor gradingisold-attribute   (set it to false)
 *
 * @param unknown $courseid
 * @param unknown $descriptorid
 * @param unknown $studentid
 */
//  function block_exacomp_set_descriptor_grading_timestamp($courseid, $descriptorid, $studentid) {
//      $data = array();
//      $data["timestamp"]=time();

//      g::$DB->insert_or_update_record(BLOCK_EXACOMP_DB_COMPETENCES, $data, [
//          'courseid' => $courseid,
//          'userid' => $studentid,
//          'comptype' => BLOCK_EXACOMP_TYPE_DESCRIPTOR,
//          'compid' => $descriptorid,
//          'role' => BLOCK_EXACOMP_ROLE_TEACHER,
//      ]);
//  }

/**
 * set descriptor gradingisold-attribute   (set it to true)
 *
 * @param unknown $courseid
 * @param unknown $compid
 * @param unknown $studentid
 * @param unknown $role
 */
function block_exacomp_set_descriptor_gradingisold($courseid, $compid, $studentid, $role) {
    global $DB;

    //Find the id of the parent of the competence that has been changed
    $record = $DB->get_record_sql('
			SELECT parentid FROM {' . BLOCK_EXACOMP_DB_DESCRIPTORS . '} WHERE id=?
			', array($compid));

    $data = array();
    $data["gradingisold"] = 1;
    //set the gradingisoldflag of the parent
    g::$DB->update_record(BLOCK_EXACOMP_DB_COMPETENCES, $data, [
        'courseid' => $courseid,
        'userid' => $studentid,
        'comptype' => BLOCK_EXACOMP_TYPE_DESCRIPTOR,
        'compid' => @$record->parentid,
        'role' => $role,
    ]);
}

/**
 * unset descriptor gradingisold-attribute   (set it to false)
 *
 * @param unknown $courseid
 * @param unknown $compid
 * @param unknown $studentid
 */
function block_exacomp_unset_descriptor_gradingisold($courseid, $compid, $studentid) {
    global $DB;
    $data = array();
    $data["gradingisold"] = 0;
    //set the gradingisoldflag of the parent
    g::$DB->update_record(BLOCK_EXACOMP_DB_COMPETENCES, $data, [
        'courseid' => $courseid,
        'userid' => $studentid,
        'comptype' => BLOCK_EXACOMP_TYPE_DESCRIPTOR,
        'compid' => $compid,
        'role' => BLOCK_EXACOMP_ROLE_TEACHER,
    ]);
}

/**
 * should not be used if there is a faster way to check (in Competence grid the data already exists
 *
 * @param unknown $descriptorid
 * @param unknown $studentid
 * @return unknown
 */
function block_exacomp_is_descriptor_grading_old($descriptorid, $studentid) {
    global $CFG, $DB;

    $query = 'SELECT DISTINCT gradingisold
     	  FROM {' . BLOCK_EXACOMP_DB_COMPETENCES . '}
          WHERE compid = ? AND userid = ?';
    $condition = array($descriptorid, $studentid);

    $result = $DB->get_record_sql($query, $condition, IGNORE_MULTIPLE);
    if ($result) {
        return $result->gradingisold;
    } else {
        return false;
    }
}

/**
 * @param int $courseid
 * @param string $startlevel
 * @param int $parentid
 * @return bool
 */
function block_exacomp_delete_tree($courseid = 0, $startlevel = '', $parentid = 0) {
    global $DB;
    if (!$courseid) {
        $courseid = g::$COURSE->id;
    }
    $examplesToDelete = array();
    $descriptorsToDelete = array();
    $topicsToDelete = array();
    $subjectsToDelete = array();
    $niveausToDelete = array();
    if (!$parentid) {
        return false;
    }
    switch ($startlevel) {
        case 'subject':
            if ($startlevel == 'subject') {
                $subjectsToDelete[] = $parentid;
            }
            foreach ($subjectsToDelete as $subjectid) {
                $subjectObj = \block_exacomp\subject::get($subjectid);
                block_exacomp_require_item_capability(BLOCK_EXACOMP_CAP_DELETE, $subjectObj);
                $DB->delete_records(BLOCK_EXACOMP_DB_SUBJECT_NIVEAU_MM, array('subjectid' => $subjectid));
                $topicsToDelete = array_merge($topicsToDelete, array_keys(block_exacomp_get_topics_by_subject($courseid, $subjectid)));
                // get the topicObj with the subject, so the check if the topic is deletable works later on, when the subject is already deleted
                $subjectsForTopics = array();
                foreach ($topicsToDelete as $topic) {
                    $subjectsForTopics[$topic] = $subjectObj;
                }
                $subjectObj->delete();
            }
        case 'topic':
            if ($startlevel == 'topic') {
                $topicsToDelete[] = $parentid;
            }
            foreach ($topicsToDelete as $topicid) {
                $topicObj = \block_exacomp\topic::get($topicid);
                $topicObj->subject = $subjectsForTopics[$topicid];
                block_exacomp_require_item_capability(BLOCK_EXACOMP_CAP_DELETE, $topicObj);
                $DB->delete_records(BLOCK_EXACOMP_DB_COURSETOPICS, array('topicid' => $topicid, 'courseid' => $courseid));
                $DB->delete_records(BLOCK_EXACOMP_DB_DESCTOPICS, array('topicid' => $topicid));
                $DB->delete_records(BLOCK_EXACOMP_DB_TOPICVISIBILITY, array('topicid' => $topicid, 'courseid' => $courseid, 'niveauid' => null));
                $descriptorsToDelete = array_merge($descriptorsToDelete, block_exacomp_get_descriptors_by_topic($courseid, $topicid, true));
                $topicObj->delete();
            }
        case 'niveau':
            // only for niveau
            if ($startlevel == 'niveau') {
                $niveausToDelete [] = $parentid;
                foreach ($niveausToDelete as $niveauid) {
                    $niveauObj = \block_exacomp\niveau::get($niveauid);
                    block_exacomp_require_item_capability(BLOCK_EXACOMP_CAP_DELETE, $niveauObj);
                    $DB->delete_records(BLOCK_EXACOMP_DB_SUBJECT_NIVEAU_MM, array('niveauid' => $niveauid));
                    $descriptorsToDelete = array_merge($descriptorsToDelete, block_exacomp_get_descriptors_by_niveau($courseid, $niveauid));
                    $niveauObj->delete();
                }
            }
        case 'descriptor':
            if ($startlevel == 'descriptor') {
                $descriptorsToDelete[] = $parentid;
            }
            foreach ($descriptorsToDelete as $descriptor) {
                $descriptorObj = \block_exacomp\descriptor::get(intval($descriptor->id));
                block_exacomp_require_item_capability(BLOCK_EXACOMP_CAP_DELETE, $descriptorObj);
                $DB->delete_records(BLOCK_EXACOMP_DB_DESCBADGE, array('descid' => $descriptor->id));
                $DB->delete_records(BLOCK_EXACOMP_DB_DESCCAT, array('descrid' => $descriptor->id));
                $DB->delete_records(BLOCK_EXACOMP_DB_DESCCROSS, array('descrid' => $descriptor->id));
                $DB->delete_records(BLOCK_EXACOMP_DB_DESCEXAMP, array('descrid' => $descriptor->id));
                $DB->delete_records(BLOCK_EXACOMP_DB_DESCTOPICS, array('descrid' => $descriptor->id));
                $DB->delete_records(BLOCK_EXACOMP_DB_DESCVISIBILITY, array('descrid' => $descriptor->id, 'courseid' => $courseid));
                $tempDescr = block_exacomp_get_examples_for_descriptor($descriptorObj);
                if ($tempDescr && $tempDescr->examples) {
                    $examplesToDelete = array_merge($examplesToDelete, $tempDescr->examples);
                }
                $descriptorObj->delete();
            }
        case 'example':
            if ($startlevel == 'example') {
                $examplesToDelete[] = $parentid;
            }
            if (count($examplesToDelete) > 0) {
                foreach ($examplesToDelete as $exampleid) {
                    $exampleObj = \block_exacomp\example::get($exampleid);
                    block_exacomp_require_item_capability(BLOCK_EXACOMP_CAP_DELETE, $exampleObj);
                    $DB->delete_records(BLOCK_EXACOMP_DB_EXAMPTAX, array('exampleid' => $exampleid));
                    $DB->delete_records(BLOCK_EXACOMP_DB_ITEM_MM, array('exacomp_record_id' => $exampleid));
                    $DB->delete_records(BLOCK_EXACOMP_DB_EXAMPVISIBILITY, array('exampleid' => $exampleid, 'courseid' => $courseid));
                    $DB->delete_records(BLOCK_EXACOMP_DB_DESCEXAMP, array('exampleid' => $exampleid));
                    $DB->delete_records(BLOCK_EXACOMP_DB_SOLUTIONVISIBILITY, array('exampleid' => $exampleid));
                    $DB->delete_records(BLOCK_EXACOMP_DB_EXAMPLEEVAL, array('exampleid' => $exampleid));
                    $fs = get_file_storage();
                    $fs->delete_area_files(\context_system::instance()->id, 'block_exacomp', 'example_task', $exampleid);
                    $fs->delete_area_files(\context_system::instance()->id, 'block_exacomp', 'example_solution', $exampleid);
                    $fs->delete_area_files(\context_system::instance()->id, 'block_exacomp', 'example_completefile', $exampleid);
                    $exampleObj->delete();
                }
            }
            break;
    }
}

function block_exacomp_cmodule_is_autocompetence($cmid) {
    global $DB;
    return $DB->get_field('block_exacompcmsettings', 'value', ['name' => 'exacompUseAutoCompetences', 'coursemoduleid' => $cmid]);
}

/**
 * for eThema
 *
 * @param int $courseid
 * @param array $examples
 * @return bool
 */
function block_exacomp_etheme_autograde_examples_tree($courseid, $examples) {
    if (!get_config('exacomp', 'example_autograding')) {
        return true;
    }
    // for every USER:
    // 1. get changed examples (from ajax request)
    // 2. get all parent examples (these parents we need to look on needed recalculating of average value)
    // 3. get all childs for parents (from database): field ethema_important = 1
    // 4. calculate average value for parents
    // 5. the same process for 'subcategory' examples

    // get all changed example ids (different for every graded user)
    $examplechangedids = array();
    foreach ($examples as $example) {
        $userid = $example->userid;
        if (!array_key_exists($userid, $examplechangedids)) {
            $examplechangedids[$userid] = array();
        }
        $examplechangedids[$userid][] = $example->exampleid;
    }
    if (!(count($examplechangedids) > 0)) {
        return true;
    }

    $averagecalcualtingprocess = function($userid, $exampleids, $forsubcategory = false) use ($courseid) {
        $torecalculate = array();
        $toreturn = array();
        if (count($exampleids) > 0) {
            $psql = 'SELECT DISTINCT e2.*, ee.*
                        FROM {block_exacompexamples} e
                          JOIN {block_exacompexamples} e2 ON e2.ethema_parent = e.ethema_parent AND e2.ethema_important = 1
                          LEFT JOIN {block_exacompexameval} ee ON ee.courseid = ? AND ee.studentid = ? AND ee.exampleid = e2.id
                        WHERE e.id IN (' . implode(',', $exampleids) . ')
                            AND e.ethema_important = 1 '/*.($forsubcategory ? ' AND e.ethema_issubcategory = 1 ' : '') // do not need, because JOIN*/
            ;
            $allchilds = g::$DB->get_records_sql($psql, [$courseid, $userid]);
            foreach ($allchilds as $child) {
                if (!array_key_exists($child->ethema_parent, $torecalculate)) {
                    $torecalculate[$child->ethema_parent] = array();
                }
                $torecalculate[$child->ethema_parent][] = $child->teacher_evaluation;
            }
            // check on empty (not graded) child examples
            foreach (array_keys($torecalculate) as $parentid) {
                switch (block_exacomp_get_assessment_example_scheme($courseid)) {
                    case BLOCK_EXACOMP_ASSESSMENT_TYPE_VERBOSE:
                    case BLOCK_EXACOMP_ASSESSMENT_TYPE_POINTS:
                        // possible "0" value
                        $filtered_array = array_filter($torecalculate[$parentid], 'is_numeric');
                        break;
                    default:
                        $filtered_array = array_filter($torecalculate[$parentid]);
                }
                if (count($filtered_array) != count($torecalculate[$parentid])) {
                    // empty parent if it is not full graded (delete from database)
                    //block_exacomp_set_user_example($userid, $parentid, $courseid, BLOCK_EXACOMP_ROLE_TEACHER, '');
                    g::$DB->delete_records(BLOCK_EXACOMP_DB_EXAMPLEEVAL, [
                        'exampleid' => $parentid,
                        'courseid' => $courseid,
                        'studentid' => $userid,
                    ]);
                    $toreturn[] = $parentid; // for recalculate of main examples
                    unset($torecalculate[$parentid]);
                }
            }
            foreach ($torecalculate as $parentid => $values) {
                $averagevalue = block_exacomp_etheme_getaverage_example_grading($values);
                if ($averagevalue == -1) {
                    return true;
                }
                block_exacomp_set_user_example($userid, $parentid, $courseid, BLOCK_EXACOMP_ROLE_TEACHER, $averagevalue); // TODO: niveau?
            }
            $toreturn = array_merge(array_keys($torecalculate), $toreturn);
            return $toreturn; // return parents. for next checking of subcategory/main
        }
        return array();
    };

    $subcategories = array();
    // get parent ids and get all list of all child examples
    foreach ($examplechangedids as $userid => $exampleids) {
        $subs = $averagecalcualtingprocess($userid, $exampleids);
        if (count($subs) > 0) {
            if (!array_key_exists($userid, $subcategories)) {
                $subcategories[$userid] = array();
            }
            $subcategories[$userid] = array_merge($subcategories[$userid], $subs);
        }
    }
    // calculate average value of main example from child subcategory examples
    foreach ($subcategories as $userid => $exampleids) {
        $res2 = $averagecalcualtingprocess($userid, $exampleids, true);
    }

}

function block_exacomp_etheme_getaverage_example_grading($values) {
    $averagevalue = array_sum($values) / count($values);
    switch (block_exacomp_get_assessment_example_scheme()) {
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_NONE;
            return -1; // we do not need grade at all
            break;
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE:
            // round to bigger
            $averagevalue = ceil($averagevalue);
            break;
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_VERBOSE:
            // round
            $averagevalue = round($averagevalue);
            break;
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_POINTS:
            // round
            $averagevalue = round($averagevalue);
            break;
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_YESNO:
            // 2 yes, 1 no -> yes
            // 1 yes, 1 no -> yes
            // 1 yes, 2 no -> no
            if ($averagevalue < 0.5) {
                $averagevalue = 0;
            }
            break;
    }
    return $averagevalue;
}

function block_exacomp_is_autograding_example($exampleid) {
    // if the autograding is enabled
    if (!get_config('exacomp', 'example_autograding')) {
        return false;
    }
    // if example has at least one child example - it is autograded example
    $result = g::$DB->get_record(BLOCK_EXACOMP_DB_EXAMPLES, ['ethema_parent' => $exampleid], '*', IGNORE_MULTIPLE);
    return (bool)$result;
}

function block_exacomp_is_block_used_by_student($blockname, $studentid) {
    global $DB;

    $courseids = block_exacomp_get_courses_of_student($studentid);
    if (!$courseids) {
        return false;
    }

    $query = 'SELECT course.id as courseID, course.fullname, course.shortname, block_instances.blockname
	            FROM ({context} context
	                INNER JOIN {block_instances} block_instances  ON (context.id = block_instances.parentcontextid))
	                INNER JOIN {course} course                    ON (course.id = context.instanceid)
	            WHERE     (block_instances.blockname = ?)      AND (context.contextlevel = 50)
	                        AND course.id IN (' . join(',', $courseids) . ')'; //50 means that the instanceid stands for the courseid

    $condition = array($blockname);

    $coursesWithBlockActive = $DB->get_records_sql($query, $condition);

    if (!$coursesWithBlockActive) {
        return false;
    }
    return true;
}

/* Deprecated      here we saved all the data redundantly in the exacompcompuser table*/
//function block_exacomp_update_globalgradings_text($descriptorid,$studentid,$comptype){
//    global $DB;
//
//    $query = 'SELECT compuser.*, userr.firstname, userr.lastname
//                FROM {block_exacompcompuser} compuser
//                INNER JOIN `mdl_user` userr ON (compuser.reviewerid = userr.id)
//                WHERE  compuser.compid = ? AND compuser.userid = ? AND compuser.comptype = ?';
//
//    /*
//SELECT compuser.value, mdl_user.username
//FROM `mdl_block_exacompcompuser` compuser
//INNER JOIN `mdl_user` mdl_user ON (compuser.reviewerid = mdl_user.id)
//WHERE compuser.compid = 1 AND compuser.userid = 4 AND compuser.comptype = 1;
//     */
//
//    $records = $DB->get_records_sql($query, array($descriptorid,$studentid,$comptype));
//
//    $scheme_values = \block_exacomp\global_config::get_teacher_eval_items(0,false,block_exacomp_additional_grading($comptype));
//
//    $globalgradings_text = "";
//    foreach($records as $record){
//        $globalgradings_text .= $record->firstname." ".$record->lastname." ".date("d.m.y G:i",$record->timestamp).": ".$scheme_values[$record->value]."<br>";
//    }
//
//    foreach($records as $record){
//        $record->globalgradings = $globalgradings_text;
//        $DB->update_record("block_exacompcompuser", $record);
//    }
//
//    return $globalgradings_text;
//}

function block_exacomp_update_globalgradings_text($descriptorid, $studentid, $comptype, $courseid = 0) {
    global $DB;

    $query = 'SELECT compuser.*, userr.firstname, userr.lastname
                FROM {block_exacompcompuser} compuser
                INNER JOIN `mdl_user` userr ON (compuser.reviewerid = userr.id)
                WHERE  compuser.compid = ? AND compuser.userid = ? AND compuser.comptype = ?';

    /*
SELECT compuser.value, mdl_user.username
FROM `mdl_block_exacompcompuser` compuser
INNER JOIN `mdl_user` mdl_user ON (compuser.reviewerid = mdl_user.id)
WHERE compuser.compid = 1 AND compuser.userid = 4 AND compuser.comptype = 1;
     */

    $records = $DB->get_records_sql($query, array($descriptorid, $studentid, $comptype));

    $scheme_values = \block_exacomp\global_config::get_teacher_eval_items($courseid, false, block_exacomp_additional_grading($comptype, $courseid));

    $globalgradings_text = "";
    foreach ($records as $record) {
        $globalgradings_text .= $record->firstname . " " . $record->lastname . " " . date("d.m.y G:i", $record->timestamp) . ": " . $scheme_values[$record->value] . "<br>";
    }

    $globalgradingrecord = [
        'userid' => $studentid,
        'compid' => $descriptorid,
        'comptype' => $comptype,
        'globalgradings' => $globalgradings_text,
    ];

    g::$DB->insert_or_update_record(BLOCK_EXACOMP_DB_GLOBALGRADINGS, $globalgradingrecord, ['userid' => $studentid, 'compid' => $descriptorid, 'comptype' => $comptype]);

    return $globalgradings_text;
}

//deprecated, not needed anymore because we created a separate table now
//Get the globalgradings of this competence/topic/subject
//function block_exacomp_get_globalgradings_single($descriptorid,$studentid,$comptype){
//    global $DB;
//    $query = 'SELECT compuser.globalgradings
//                FROM {block_exacompcompuser} compuser
//                WHERE  compuser.compid = ? AND compuser.userid = ? AND compuser.comptype = ?';
//    $globalgradings_texts = $DB->get_records_sql($query, array($descriptorid,$studentid,$comptype));
//    //This often returns the same string multiple times... maybe there is a better way for performance but probably it is the best to query all and use the first
//    // solution in complexity O(1):
//    return array_pop($globalgradings_texts)->globalgradings;
//}

//needed in the webservices for descriptors
function block_exacomp_get_globalgradings_single($descriptorid, $studentid, $comptype) {
    global $DB;
    $query = 'SELECT globalgradings
                FROM {block_exacompglobalgradings}
                WHERE  compid = ? AND userid = ? AND comptype = ?';
    $globalgradings_text = $DB->get_record_sql($query, array($descriptorid, $studentid, $comptype));
    return $globalgradings_text->globalgradings;
}

function block_exacomp_get_dakora_teacher_cohort() {
    // get cohort or create cohort if not exists
    $cohort = g::$DB->get_record('cohort', ['contextid' => \context_system::instance()->id, 'idnumber' => 'block_exacomp_dakora_teachers']);

    $name = block_exacomp_get_string('dakora_teachers');
    $description = "Teachers that can see all globalgradings";

    if (!$cohort) {
        $cohort = (object)[
            'contextid' => \context_system::instance()->id,
            'idnumber' => 'block_exacomp_dakora_teachers',
            'name' => $name,
            'description' => $description,
            'visible' => 1,
            'component' => '', // should be block_exacomp, but then the admin can't change the group members anymore
        ];
        $cohort->id = cohort_add_cohort($cohort);
    } else {
        // keep name or description up to date
        if ($name != $cohort->name || $description != $cohort->description) {
            g::$DB->update_record('cohort', [
                'id' => $cohort->id,
                'name' => $name,
                'description' => $description,
            ]);
        }
    }

    return $cohort;
}

function block_exacomp_is_dakora_teacher($userid = null) {
    $cohort = block_exacomp_get_dakora_teacher_cohort();
    return cohort_is_member($cohort->id, $userid ? $userid : g::$USER->id);
}

// TODO: similar to block_exacomp_get_comp_eval() ?
// deprecated?
function block_exacomp_get_user_assesment($userid, $competenceid, $competencetype, $courseid) {
    $conditions = [
        'userid' => $userid,
        'compid' => $competenceid,
        'comptype' => $competencetype,
        'courseid' => $courseid,
    ];
    $result = g::$DB->get_record(BLOCK_EXACOMP_DB_COMPETENCES, $conditions, '*', IGNORE_MULTIPLE);
    if ($result) {
        $resultObj = (object)array(
            'grade' => @$result->additionalinfo,
            'niveau' => @$result->evalniveauid,
        );
        if (!block_exacomp_get_assessment_diffLevel($competencetype)) {
            $resultObj->niveau = null;
        }
        return $resultObj;
    }
    return null;
}

// do not needed? the icons are saved in database for performance
// used "block_exacomp_get_example_icon_simple" instead
function block_exacomp_get_example_icon_for_externals(&$renderer, $courseid, $externalUrl = '') {
    global $PAGE;
    $resulticontag = '';
    if ($externalUrl
        // && // check on current hostname????
        && preg_match('![\?\&]id=(?<id>[0-9]+)!', $externalUrl, $matches)) { // url has 'id=' parameter
        $moduleid = $matches['id'];
        if ($module = get_coursemodule_from_id(null, $moduleid)) {
            $mod_info = get_fast_modinfo($courseid);
            if (array_key_exists($moduleid, $mod_info->cms)) {
                $cm = $mod_info->cms[$moduleid];
                $iconurl = $cm->get_icon_url();
                $resulticontag = html_writer::empty_tag('img', array('src' => $iconurl,
                    'class' => 'smallicon', 'alt' => ' ', 'width' => 16)); // class 'activityicon' ?
            }
        }
    } else {
        // default icon
        $resulticontag = $renderer->local_pix_icon("globesearch.png", block_exacomp_get_string('preview'));
    }
    return $resulticontag;
}

function block_exacomp_get_example_icon_simple(&$renderer, $example, $forField = 'externaltask', $default = 'globesearch.png') {
    if ($example->example_icon) {
        $icons = unserialize($example->example_icon);
        if (array_key_exists($forField, $icons)) {
            $icontag = html_writer::empty_tag('img', array('src' => $icons[$forField],
                'class' => 'smallicon', 'alt' => ' ', 'width' => 16));
            return $icontag;
        } else {
            return $renderer->local_pix_icon($default, block_exacomp_get_string('preview'));
        }
    } else {
        return $renderer->local_pix_icon($default, block_exacomp_get_string('preview'));
    }
}

function block_exacomp_eduvidual_defaultSchooltypes() {
    global $DB;
    $eduvidalDefaults = array( // items are: $userSchoolType => array('title', 'sourceid')
        1 => ['title' => 'Volksschule', 'sourceid' => 7],
        2 => ['title' => 'Mittelschule', 'sourceid' => 10],
        3 => ['title' => 'Sonderschule', 'sourceid' => 7],
        4 => ['title' => 'Poly', 'sourceid' => 11],
        5 => ['title' => 'Berufsschule', 'sourceid' => 791],
        6 => ['title' => 'AHS', 'sourceid' => 5],
        7 => ['title' => 'HTL', 'sourceid' => 3],
        8 => ['title' => 'HAK', 'sourceid' => 1],
        9 => ['title' => 'HUM', 'sourceid' => 2],
    );
    foreach ($eduvidalDefaults as $dtkey => $dst) {
        $dstReal = $DB->get_record('block_exacompschooltypes', ['sourceid' => $dst['sourceid']], '*', IGNORE_MULTIPLE);
        if ($dstReal) {
            $eduvidalDefaults[$dtkey]['realId'] = $dstReal->id;
        } else {
            $eduvidalDefaults[$dtkey]['realId'] = null;
        }
    }
    return $eduvidalDefaults;
}

/** true if new version is good and we can work with this item, false if we must ignore this item
 *
 * @param $newVersion
 * @param $oldVersion
 * @param $rule rules: 1 - new >= old; 2 - new > old, 3 - new <= old, 4 - new < old ---> most used => 1
 * @return bool
 */
function block_exacomp_versions_compare($newVersion, $oldVersion, $rule = 1) {
    $stringVersionConvertToNumeric = function($stringValue) {
        // delete all dots, except first
        //$stringValue = str_replace('.', '', $stringValue);
        $parts = explode('.', $stringValue);
        array_walk($parts, function(&$p) {
            $p = abs((int)filter_var($p, FILTER_SANITIZE_NUMBER_INT));
        });
        // first number and mix of other
        $resultparts = array(array_shift($parts), implode('', $parts));
        $stringValue = implode('.', $resultparts);
        return $stringValue;
    };
    $newVersion = $stringVersionConvertToNumeric($newVersion);
    $oldVersion = $stringVersionConvertToNumeric($oldVersion);
    switch ($rule) {
        case 1:
            if ($newVersion >= $oldVersion) {
                return true;
            }
            break;
        case 2:
            if ($newVersion > $oldVersion) {
                return true;
            }
            break;
        case 3:
            if ($newVersion < $oldVersion) {
                return true;
            }
            break;
        case 4:
            if ($newVersion <= $oldVersion) {
                return true;
            }
            break;
    }
    return false;
}

function block_exacomp_get_config_dakora_language_file($returnContent = false) {
    if (get_config('exacomp', 'dakora_language_file')) {
        $fs = get_file_storage();
        $files = $fs->get_area_files(1, 'exacomp', 'exacomp_dakora_language_file', 0, '', false);
        $file = reset($files);
        if ($file) {
            if ($returnContent) {
                return $file->get_content();
            }
            return $file;
        }
    };
    return null;
}

function block_exacomp_get_config_assessment_verbose_lowerisbetter() {
    return get_config('exacomp', 'assessment_verbose_lowerisbetter');
}

function block_exacomp_get_config_dakora_timeout() {
    return (int)get_config('exacomp', 'dakora_timeout');
}

function block_exacomp_get_config_dakora_show_overview() {
    return get_config('exacomp', 'dakora_show_overview');
}

function block_exacomp_get_config_dakora_show_eportfolio() {
    return get_config('exacomp', 'dakora_show_eportfolio');
}

function block_exacomp_random_password($length = 12) {
    $alphabet = 'abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $pass = array(); //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < $length; $i++) {
        $n = random_int(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); //turn the array into a string
}

function block_exacomp_require_login($courseorid = null, $autologinguest = true, $cm = null, $setwantsurltome = true, $preventredirect = false) {
    require_login($courseorid, $autologinguest, $cm, $setwantsurltome, $preventredirect);

    if (class_exists('\block_exa2fa\api')) {
        if (method_exists(\block_exa2fa\api::class, 'check_user_a2fa_requirement')) {
            \block_exa2fa\api::check_user_a2fa_requirement('block_exacomp');
        } else {
            die('update block_exa2fa to last version!');
        }
    }
}

function block_exacomp_new_subject_data_for_competence_profile($subjectGenericData, $courseid = 0) {
    global $DB;
    $use_evalniveau = array(
        BLOCK_EXACOMP_TYPE_SUBJECT => block_exacomp_get_assessment_subject_diffLevel(),
        BLOCK_EXACOMP_TYPE_TOPIC => block_exacomp_get_assessment_topic_diffLevel(),
        BLOCK_EXACOMP_TYPE_DESCRIPTOR => block_exacomp_get_assessment_comp_diffLevel(),
    );
    $evaluationniveau_items = \block_exacomp\global_config::get_evalniveaus(null, $courseid);
    $newSubjectData = array();
    $avgSubjectsTmp = $avgTopicsTmp = $avgNiveausTmp = array(); //array('sum' => 0, 'count' => 0);
    $roundFunction = function($level, $value, $courseid) {
        $assessmentType = block_exacomp_additional_grading($level, $courseid);
        switch ($assessmentType) {
            case BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE:
                return round($value, 0, PHP_ROUND_HALF_DOWN);
                break;
            default:
                return round($value);
        }
        return $value;
    };
    // get values for every subjects by course for calculate averages in next step
    foreach ($subjectGenericData as $sId => $subjectData) {
        if (!array_key_exists($sId, $newSubjectData)) {
            $newSubjectData[$sId] = (object)array(
                'subject_evalniveau' => '',
                'subject_evalniveauid' => -1,
                'subject_eval' => -1,
                'timestamp' => '',
                'subject_title' => reset($subjectData['courses_table_content'])->subject_title,
                'content' => array(),
            );
        }
        foreach ($subjectData['courses_table_content'] as $cId => $courseContent) {
            foreach ($courseContent->content as $tId => $topicData) {
                if (!array_key_exists($tId, $newSubjectData[$sId]->content)) {
                    $newSubjectData[$sId]->content[$tId] = (object)array(
                        'niveaus' => array(),
                        'span' => 0,
                        'topic_evalniveauid' => 0,
                        'topic_evalniveau' => '',
                        'topic_eval' => -1,
                        'visible' => $topicData->visible,
                        'timestamp' => '',
                        'topic_id' => $tId,
                    );
                }
                if ($use_evalniveau[BLOCK_EXACOMP_TYPE_TOPIC] && @$topicData->topic_evalniveauid != '' && $topicData->topic_evalniveauid > 0) {
                    @$avgTopicsTmp[$tId]['evalniveau_sum'] += $topicData->topic_evalniveauid;
                    @$avgTopicsTmp[$tId]['evalniveau_count']++;
                }
                if ($topicData->topic_eval != '' && $topicData->topic_eval > -1) {
                    @$avgTopicsTmp[$tId]['sum'] += $topicData->topic_eval;
                    @$avgTopicsTmp[$tId]['count']++;
                }
                foreach ($topicData->niveaus as $niveauTitle => $niveauData) {
                    if (!array_key_exists($niveauTitle, $newSubjectData[$sId]->content[$tId]->niveaus)) {
                        $newSubjectData[$sId]->content[$tId]->niveaus[$niveauTitle] = (object)array(
                            'evalniveau' => '',
                            'evalniveauid' => -1, // block_exacomp_use_eval_niveau() ? -1 : 0 ???
                            'eval' => -1,
                            'show' => (isset($niveauData->show) ? $niveauData->show : false),
                            'visible' => (isset($niveauData->visible) ? $niveauData->visible : false),
                            'timestamp' => 0,
                            'gradingisold' => '',
                        );
                    }
                    if ($use_evalniveau[BLOCK_EXACOMP_TYPE_DESCRIPTOR] && @$niveauData->evalniveauid != '' && $niveauData->evalniveauid > 0) {
                        @$avgNiveausTmp[$tId . ':::' . $niveauTitle]['evalniveau_sum'] += $niveauData->evalniveauid;
                        @$avgNiveausTmp[$tId . ':::' . $niveauTitle]['evalniveau_count']++;
                    }
                    if (isset($niveauData->eval) && $niveauData->eval != '' && $niveauData->eval > -1) {
                        @$avgNiveausTmp[$tId . ':::' . $niveauTitle]['sum'] += $niveauData->eval;
                        @$avgNiveausTmp[$tId . ':::' . $niveauTitle]['count']++;
                    }
                }
            }

            if ($use_evalniveau[BLOCK_EXACOMP_TYPE_SUBJECT] && @$courseContent->subject_evalniveauid != '' && $courseContent->subject_evalniveauid > 0) {
                @$avgSubjectsTmp[$sId]['evalniveau_sum'] += $courseContent->subject_evalniveauid;
                @$avgSubjectsTmp[$sId]['evalniveau_count']++;
            }
            if (@$courseContent->subject_eval != '' && $courseContent->subject_eval > -1) {
                @$avgSubjectsTmp[$sId]['sum'] += $courseContent->subject_eval;
                @$avgSubjectsTmp[$sId]['count']++;
            }
        }
    }
    foreach ($subjectGenericData as $sId => $subjectData) {
        if (array_key_exists($sId, $avgSubjectsTmp)) {
            if (@$avgSubjectsTmp[$sId]['evalniveau_count'] > 0) {
                $new_evalniveau = $roundFunction(BLOCK_EXACOMP_TYPE_SUBJECT,
                    $avgSubjectsTmp[$sId]['evalniveau_sum'] / $avgSubjectsTmp[$sId]['evalniveau_count'], $courseid);
                $newSubjectData[$sId]->subject_evalniveau = @$evaluationniveau_items[$new_evalniveau] ?: '';
                $newSubjectData[$sId]->subject_evalniveauid = $new_evalniveau;
            }
            if (@$avgSubjectsTmp[$sId]['count'] > 0) {
                $newSubjectData[$sId]->subject_eval =
                    $roundFunction(BLOCK_EXACOMP_TYPE_SUBJECT, $avgSubjectsTmp[$sId]['sum'] / $avgSubjectsTmp[$sId]['count'], $courseid);
            }
        }
        foreach ($subjectData['courses_table_content'] as $cId => $courseContent) {
            foreach ($courseContent->content as $tId => $topicData) {
                foreach ($topicData->niveaus as $niveauTitle => $niveauData) {
                    if (array_key_exists($tId, $avgTopicsTmp)) {
                        if (@$avgTopicsTmp[$tId]['evalniveau_count'] > 0) {
                            $new_evalniveau = $roundFunction(BLOCK_EXACOMP_TYPE_TOPIC,
                                $avgTopicsTmp[$tId]['evalniveau_sum'] / $avgTopicsTmp[$tId]['evalniveau_count'], $courseid);
                            $newSubjectData[$sId]->content[$tId]->topic_evalniveau =
                                @$evaluationniveau_items[$new_evalniveau] ?: '';
                            $newSubjectData[$sId]->content[$tId]->topic_evalniveauid = $new_evalniveau;
                        }
                        if (@$avgTopicsTmp[$tId]['count'] > 0) {
                            $newSubjectData[$sId]->content[$tId]->topic_eval = $roundFunction(BLOCK_EXACOMP_TYPE_TOPIC,
                                $avgTopicsTmp[$tId]['sum'] / $avgTopicsTmp[$tId]['count'], $courseid);
                        }
                    }
                    foreach ($topicData->niveaus as $niveauTitle => $niveauData) {
                        if (array_key_exists($tId . ':::' . $niveauTitle, $avgNiveausTmp)) {
                            if (@$avgNiveausTmp[$tId . ':::' . $niveauTitle]['evalniveau_count'] > 0) {
                                $new_evalniveau = $roundFunction(BLOCK_EXACOMP_TYPE_DESCRIPTOR,
                                    $avgNiveausTmp[$tId . ':::' . $niveauTitle]['evalniveau_sum'] /
                                    $avgNiveausTmp[$tId . ':::' . $niveauTitle]['evalniveau_count'], $courseid);
                                $newSubjectData[$sId]->content[$tId]->niveaus[$niveauTitle]->evalniveau =
                                    @$evaluationniveau_items[$new_evalniveau] ?: '';
                                $newSubjectData[$sId]->content[$tId]->niveaus[$niveauTitle]->evalniveauid = $new_evalniveau;
                            }
                            if (@$avgNiveausTmp[$tId . ':::' . $niveauTitle]['count'] > 0) {
                                $newSubjectData[$sId]->content[$tId]->niveaus[$niveauTitle]->eval =
                                    $roundFunction(BLOCK_EXACOMP_TYPE_DESCRIPTOR,
                                        $avgNiveausTmp[$tId . ':::' . $niveauTitle]['sum'] /
                                        $avgNiveausTmp[$tId . ':::' . $niveauTitle]['count'], $courseid);
                            }
                        }
                    }
                }
            }
        }
    }
    return $newSubjectData;
}

function block_exacomp_list_possible_activities_for_example($courseid) {
    global $DB;
    $example_activities = array(
        0 => block_exacomp_get_string('none'),
    );

    $modinfo = get_fast_modinfo($courseid);
    $modules = $modinfo->get_cms();
    foreach ($modules as $mod) {
        $module = block_exacomp_get_coursemodule($mod);
        if ($module->deletioninprogress) {
            continue;
        }
        // Skip Nachrichtenforum
        if (strcmp($module->name, block_exacomp_get_string('namenews', 'mod_forum')) == 0) {
            continue;
        }
        $module_type = $DB->get_record('course_modules', array('id' => $module->id));
        $forum = $DB->get_record('modules', array('name' => 'forum'));
        // skip News forum in any language, supported_modules[1] == forum
        if ($module_type->module == $forum->id) {
            $forum = $DB->get_record('forum', array('id' => $module->instance));
            if (strcmp($forum->type, 'news') == 0) {
                continue;
            }
        }
        $example_activities[$module->id] = $module->name;
    }
    return $example_activities;
}

/**
 * $topics['topicid'] = $topic
 * $topic->student = 0-100 percentage
 * $topic->teacher = 0-100 percentage
 *
 * @return array $topics
 */
function block_exacomp_get_topics_for_radar_graph($courseid, $studentid, $subjectid = null) {
    global $DB;
    $scheme = block_exacomp_additional_grading(BLOCK_EXACOMP_TYPE_DESCRIPTOR, $courseid);
    $direction = ' >= '; // different for Points/Verb/Grade
    $valuefield = 'value';
    switch ($scheme) {
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_NONE:
            return array(); // no assessment - no data for graph
            break;
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE:
            $direction = ' < '; // highest value is worse
            $valuefield = 'additionalinfo'; // TODO: is this correct for GRADE type???? (also - only for teachers?)
            break;
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_VERBOSE:
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_POINTS:
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_YESNO:
            $direction = ' > '; // highest value is better
            break;
    }
    //$maxVal = block_exacomp_get_assessment_max_value_by_level(BLOCK_EXACOMP_TYPE_DESCRIPTOR);
    $negativeLimit = block_exacomp_get_assessment_negative_threshold(BLOCK_EXACOMP_TYPE_DESCRIPTOR, $courseid);

    $studentEvalItems = \block_exacomp\global_config::get_student_eval_items(null, null, null, $courseid);
    $selfLimit = ceil(max(array_keys($studentEvalItems)) / 2);
    if ($subjectid) {
        // for topics only from this subject
        $topics = block_exacomp_get_topics_by_subject($courseid, $subjectid);
    } else {
        // for all topics in course
        $topics = block_exacomp_get_topics_by_course($courseid);
    }

    foreach ($topics as $topic) {
        $totalDescr = block_exacomp_get_descriptors_by_topic($courseid, $topic->id, false, true);
        // for teacher
        $sql = 'SELECT c.id, c.userid, c.compid, c.role, c.courseid, c.value, c.comptype, c.timestamp
                    FROM {' . BLOCK_EXACOMP_DB_COMPETENCES . '} c, {' . BLOCK_EXACOMP_DB_DESCTOPICS . '} dt
                        LEFT JOIN {' . BLOCK_EXACOMP_DB_DESCRIPTORS . '} d ON dt.descrid = d.id
                    WHERE c.compid = dt.descrid
                        AND dt.topicid = ?
                        AND c.comptype = 0
                        AND c.role = ?
                        AND c.userid = ?
                        AND c.' . $valuefield . ' ' . $direction . ' ?
                        AND c.' . $valuefield . ' > -1
                        AND c.courseid = ?
                        AND d.parentid = 0'; // only parents?

        $competencies = $DB->get_records_sql($sql, array($topic->id, BLOCK_EXACOMP_ROLE_TEACHER, $studentid, $negativeLimit, $courseid));
        $topic->teacher = 0;
        if (count($totalDescr) > 0) {
            $topic->teacher = (count($competencies) / count($totalDescr)) * 100;
        }
        // for student
        $sql = 'SELECT c.id, c.userid, c.compid, c.role, c.courseid, c.value, c.comptype, c.timestamp
                    FROM {' . BLOCK_EXACOMP_DB_COMPETENCES . '} c, {' . BLOCK_EXACOMP_DB_DESCTOPICS . '} dt
                        LEFT JOIN {' . BLOCK_EXACOMP_DB_DESCRIPTORS . '} d ON dt.descrid = d.id
		            WHERE c.compid = dt.descrid
		                AND dt.topicid = ?
		                AND c.comptype = 0
		                AND c.role = ?
		                AND c.userid = ?
		                AND c.value >= ?
		                AND c.courseid = ?
		                AND d.parentid = 0';
        $competencies = $DB->get_records_sql($sql, array($topic->id, BLOCK_EXACOMP_ROLE_STUDENT, $studentid, $selfLimit, $courseid));
        $topic->student = 0;
        if (count($totalDescr) > 0) {
            $topic->student = (count($competencies) / count($totalDescr)) * 100;
        }
        $topic->title = trim(html_entity_decode($topic->title));
    }
    return $topics;
}

function block_exacomp_get_questions_of_quiz($moduleId) {
    global $DB;

    return $DB->get_records_sql('
			SELECT qst.id, qst.name
			FROM {course_modules} cm
			JOIN {quiz} q ON cm.instance = q.id
			JOIN {quiz_slots} qs ON q.id = qs.quizid
			JOIN {question} qst ON qst.id = qs.slot
			WHERE cm.id = ?
			', array($moduleId));
}

function block_exacomp_get__descriptor_of_question($questionid) {
    global $DB;

    return $DB->get_records_sql('
			SELECT dis.id, dis.title
			FROM {' . BLOCK_EXACOMP_DB_DESCRIPTOR_QUESTION . '} dq
			JOIN {' . BLOCK_EXACOMP_DB_DESCRIPTORS . '} dis ON dq.descrid = dis.id
			WHERE dq.questid = ?
			', array($questionid));
}

function block_exacomp_disable_core_competency() {
    set_config('enabled', 0, 'core_competency');
}

function block_exacomp_help_icon($title, $text, $isSpan = false, $addClass = '') {
    global $OUTPUT;
    $content = $OUTPUT->image_icon('help', $title);
    $tag = 'a';
    if ($isSpan) {
        $tag = 'span';
    }
    $content = '<' . $tag . ' class="btn btn-link p-0 ' . $addClass . '" role="button" data-container="body" data-toggle="popover" data-placement="right" data-content="<div class=&quot;no-overflow&quot;><p>' . $text .
        '</p></div> " data-html="true" tabindex="0" data-trigger="click hover focus">'
        . $content .
        '</' . $tag . '>';
    return $content;
}

function block_exacomp_get_backup_temp_directory() {
    global $CFG;
    static $path = null;
    if ($path === null) {
        if (@$CFG->backuptempdir && $CFG->backuptempdir !== "$CFG->tempdir/backup" && is_dir($CFG->backuptempdir)) {
            $tempdir = $CFG->backuptempdir;
        } else if (@$CFG->tempdir && $CFG->tempdir !== "$CFG->dataroot/temp" && is_dir($CFG->tempdir)) {
            $tempdir = $CFG->tempdir . '/backup/';
        } else {
            $tempdir = $CFG->dataroot . '/temp/backup/';
        }
        if (!is_dir($tempdir)) {
            mkdir($tempdir);
        }
        $tempdir = rtrim($tempdir, '/') . '/';
        $path = $tempdir;
    }
    return $path;
}

function block_exacomp_relate_komettranslator_to_exacomp() {
    global $DB;
    // if the komettranslator tool is not installed --> don't do anything
    if (!$DB->get_manager()->table_exists('local_komettranslator')) {
        return;
    }

    //find all activities which have competencies
    //for these competencies: find out if they are in local_komettranslator
    //if they are, find the related descriptorid
    //for each activityid call the function with the related descriptorids
    //done
    //next -> gradings

    //create_related_examples
    // create the examples based on the implicit relation that exists because of the moodlecomp to exacompdescriptor relation.

    //relate the modules(activities) to descriptors -> create examples
    //First, get all the activityids that are relevant: all activities that have any competency where the competency exists in local_komettranslator
    //DISCTINCT, since I only need to find all modules that have ANY relation. It does not matter how many competencies are linked.
    $modules = $DB->get_records_sql('
        SELECT DISTINCT modcomp.cmid as moduleid, cm.course as courseid
        FROM {competency_modulecomp} modcomp
        JOIN {local_komettranslator} komet ON komet.internalid = modcomp.competencyid
        JOIN {course_modules} cm ON cm.id = modcomp.cmid
        ');

    // We have the courseids, with the courseid we can check, if exacomp is active in this course
    // discard those where exacomp is not even active, since then they are not needed for sure
    // TODO: is this a good filter? Should this be the limiting factor? Or should it be more: e.g. the topic of the descriptors has to be activated?
    $modules = array_filter($modules, function($mod) {
        return !empty(block_exacomp_is_activated($mod->courseid));
    });


    //Now we have every RELEVANT module
    //for each module: get the competencies and thereby the descriptors
    foreach ($modules as $module) {
        $descriptors = $DB->get_records_sql('
        SELECT descr.id as descrid, modules.course as courseid
        FROM {competency_modulecomp} modcomp
        JOIN {course_modules} modules ON modules.id = modcomp.cmid
        JOIN {local_komettranslator}  komet ON komet.internalid = modcomp.competencyid
        JOIN {' . BLOCK_EXACOMP_DB_DESCRIPTORS . '} descr ON descr.sourceid = komet.itemid
        JOIN {' . BLOCK_EXACOMP_DB_DATASOURCES . '} datasrc ON (datasrc.id = descr.source AND datasrc.source = komet.sourceid)
        WHERE modcomp.cmid = ?
        ', array($module->moduleid));
        //get the courseid. One module can only be in one course => there will not be different courses, even though there can be different descriptors
        $courseid = array_values($descriptors)[0]->courseid;
        $descriptors = array_keys($descriptors); //the keys are the descriptorids, which is what I need
        if ($descriptors) {
            // TODO: Only do this, if the module is in a course where 1. exacomp is active and 2. the corresponding exacomp subject is active
            block_exacomp_relate_example_to_activity($courseid, $module->moduleid, $descriptors, true);
        }

        // TODO: the following works based on existing functions(good), but could be done more performant(bad)
        //        $courseids = [];
        //        foreach ($descriptors as $descriptor){
        //            $courseids = array_replace($courseids, block_exacomp_get_courseids_by_descriptor($descriptor)); //TODO: this does NOT work. The activity relation makes no sense in other courses
        //        }
        //        foreach ($courseids as $courseid){
        //            block_exacomp_relate_example_to_activity($courseid, $module->moduleid, $descriptors);
        //        }
    }

    //TODO: if a competency is REMOVED from a module the example should be removed as well...?

    //TODO: safe the time of the last update somewhere. I can then only walk through the NEW gradings and NEW relations => better performance

    //TODO: what about topics... actually, there are no examples for topics in eacomp. Grading will still be used

    //block_exacomp_grade_descriptors_by_related_moodlecomp
    //competency_usercomp contains the gradings. or competency_usercompcourse
    //TODO: should I check for type? To only allow descritpro and topicgradings? For not subjects are also found, but they are never graded afaik => don't query for better performance
    //get all graded competencies that are graded and exist in local_komettranslator and are thereby relevant
    $competencies = $DB->get_records_sql('
        SELECT usercompcourse.competencyid as compid
        FROM {competency_usercompcourse} usercompcourse
        JOIN {local_komettranslator} komet ON komet.internalid = usercompcourse.competencyid
        WHERE usercompcourse.proficiency IS NOT NULL
        ');

    foreach ($competencies as $competency) {
        //TODO: possibly leave out the JOIN on competency_usercompcourse since I could already get that info in the query above
        //JOIN {course_modules} cmod ON cmod.id = modcomp.cmid could be used to find the course => but there is a table competency_usercompCOURSE which solves this already
        $descriptorGradings = $DB->get_records_sql('
        SELECT descr.id as descrid, usercompcourse.courseid as courseid, usercompcourse.userid as userid, usercompcourse.proficiency as proficiency
        FROM {local_komettranslator} komet
        JOIN {' . BLOCK_EXACOMP_DB_DESCRIPTORS . '} descr ON descr.sourceid = komet.itemid
        JOIN {' . BLOCK_EXACOMP_DB_DATASOURCES . '} datasrc ON (datasrc.id = descr.source AND datasrc.source = komet.sourceid)
        JOIN {competency_usercompcourse} usercompcourse ON usercompcourse.competencyid = komet.internalid
        WHERE komet.internalid = ?
        ', array($competency->compid));

        $topicGradings = $DB->get_records_sql('
        SELECT topic.id as topicid, usercompcourse.courseid as courseid, usercompcourse.userid as userid, usercompcourse.proficiency as proficiency
        FROM {local_komettranslator} komet
        JOIN {' . BLOCK_EXACOMP_DB_TOPICS . '} topic ON topic.sourceid = komet.itemid
        JOIN {' . BLOCK_EXACOMP_DB_DATASOURCES . '} datasrc ON (datasrc.id = topic.source AND datasrc.source = komet.sourceid)
        JOIN {competency_usercompcourse} usercompcourse ON usercompcourse.competencyid = komet.internalid
        WHERE komet.internalid = ?
        ', array($competency->compid));

        //most of the time there will be only one descriptor/topic per id. But if there are different datasources there can be more than one time the same "itemid" in the local_komettranslator table
        foreach ($descriptorGradings as $grading) {
            //            block_exacomp_get_assessment_max_good_value($grading_scheme, $userrealvalue, $maxGrade, $studentGradeResult) --> FOR NOW: use Dichotom hardcoded => proficiency
            block_exacomp_set_user_competence($grading->userid, $grading->descrid, BLOCK_EXACOMP_TYPE_DESCRIPTOR, $grading->courseid, BLOCK_EXACOMP_ROLE_TEACHER, $grading->proficiency);
        }

        foreach ($topicGradings as $grading) {
            block_exacomp_set_user_competence($grading->userid, $grading->topicid, BLOCK_EXACOMP_TYPE_TOPIC, $grading->courseid, BLOCK_EXACOMP_ROLE_TEACHER, $grading->proficiency);
        }
    }
}

function block_exacomp_is_user_in_course($userid, $courseid) {
    $context = context_course::instance($courseid);

    // also check for exacomp course?
    // has_capability('block/exacomp:use', $context, $userid))
    return is_enrolled($context, $userid, '', true);
}

function block_exacomp_check_profile_fields() {

    $categoryid = g::$DB->get_field_sql("SELECT id FROM {user_info_category} ORDER BY sortorder LIMIT 1");
    if (!$categoryid) {
        $categoryid = g::$DB->insert_record('user_info_category', [
            'name' => block_exacomp_get_string('profiledefaultcategory', 'admin'),
            'sortorder' => 1,
        ]);
    }

    $sortorder = g::$DB->get_field_sql('SELECT MAX(sortorder) FROM {user_info_field} WHERE categoryid=?', [$categoryid]);

    $fields = [
        [
            'shortname' => 'ausserordentlich',
            'name' => block_exacomp_trans('de:Außerordentlich'),
            'description' => '',
            'datatype' => 'text',
            'categoryid' => $categoryid,
            'locked' => 1,
            'required' => 0,
            'visible' => 0,
            'param1' => 30,
            'param2' => 2048,
            'param3' => 0,
        ],
    ];

    foreach ($fields as $field) {
        $id = g::$DB->get_field('user_info_field', 'id', ['shortname' => $field['shortname']]);
        if ($id) {
            // don't update those:
            unset($field['name']);
            unset($field['description']);

            g::$DB->update_record('user_info_field', $field, ['id' => $id]);
        } else {
            $sortorder++;
            $field['sortorder'] = $sortorder;
            g::$DB->insert_record('user_info_field', $field);
        }
    }
}

function block_exacomp_build_comp_tree() {
    global $CFG, $USER, $COURSE, $DB;
    $content = '<form></form>';
    $content .= '<form id="treeform" method="post" ' .
        ' action="' . $CFG->wwwroot . '/blocks/exacomp/question_to_descriptors.php?courseid=' . $COURSE->id . '">';

    $printtree = function($items, $level = 0) use (&$printtree) {
        if (!$items) {
            return '';
        }

        $content = '';
        if ($level == 0) {
            $content .= '<ul id="comptree" class="treeview">';
        } else {
            $content .= '<ul>';
        }

        foreach ($items as $item) {
            if ($item instanceof \block_exacomp\descriptor) {
                $checked = 'checked="checked"';
            } else {
                $checked = '';
            }

            $content .= '<li>';
            if ($item instanceof \block_exacomp\descriptor) {
                $content .= '<input type="checkbox" name="desc[' . $item->id . ']" ' . $checked . ' value="' . $item->id . '">';
            }
            $content .= $item->title .
                ($item->achieved ? ' ' . g::$OUTPUT->pix_icon("i/badge",
                        'selected_competencies') : '') .
                $printtree($item->get_subs(), $level + 1) .
                '</li>';
        }

        $content .= '</ul>';

        return $content;
    };

    $comptree = \block_exacomp\api::get_comp_tree_for_exaport($USER->id);

    $content .= $printtree($comptree);
    $content .= '<input type="hidden" value="" name="questid">';
    $content .= '<input type="hidden" value="save" name="action">';
    $content .= '<input type="hidden" value=' . sesskey() . ' name="sesskey">';
    $content .= '<input type="submit" id="id_submitbutton" type="submit" value="' . get_string('savechanges') .
        '" name="submitbutton">';

    $content .= '</form>';

    return $content;
}

/**
 * Check related activities were changed/deleted - change example datas
 *
 * @param integer $cmid
 * @param string $newtitle
 * @return boolean
 */
function block_exacomp_check_relatedactivitydata($cmid, $newtitle) {
    global $DB, $CFG;
    //require_once $CFG->dirroot . '/blocks/exacomp/inc.php'; was needed when it was in exacomp/lib
    // 1. new method of relation - the relation is EXAMPLE
    $DB->execute('
        UPDATE {block_exacompexamples}
            SET title = ?,
              activitytitle = ?
            WHERE activityid = ?
              AND title != ?
              AND activitytitle != ?
        ', [$newtitle, $newtitle, $cmid, $newtitle, $newtitle]); // TODO: title is also changed or only activitytitle?
    // 2. old method - with MM table
    if (block_exacomp_use_old_activities_method()) {
        $DB->execute('
            UPDATE {block_exacompcompactiv_mm}
                SET activitytitle = ?
                WHERE activityid = ?
                  AND activitytitle != ?
            ', [$newtitle, $cmid, $newtitle]);
    }
    return true;
}


function block_exacomp_checkfordelete_relatedactivity($cmid) {
    global $DB, $CFG;
    //require_once $CFG->dirroot . '/blocks/exacomp/inc.php'; was needed when it was in exacomp/lib
    // 1. new method of relation - the relation is EXAMPLE
    // TODO: right now is deleted related example. May we need to stay the example, but change activity fields
    $DB->execute('
            DELETE FROM {block_exacompexamples}
                WHERE activityid = ?
            ', [$cmid]);
    // if we need to change activity fields only, not delete the example at all
    /* $DB->execute('
         UPDATE {block_exacompexamples}
             SET activityid = ?,
               activitytitle = ?,
               activitylink = ?,
               courseid = ?
             WHERE activityid = ?
         ', [0, '', '', 0, $cmid]);*/
    // 2. old method - with MM table
    if (block_exacomp_use_old_activities_method()) {
        $DB->execute('
            DELETE FROM {block_exacompcompactiv_mm}
                WHERE activityid = ?
            ', [$cmid]);
    }
}

function block_exacomp_fill_comp_tree($question, $comptree) {
    global $CFG, $USER, $COURSE, $DB;
    $activedescriptors = $DB->get_fieldset_select("block_exacompdescrquest_mm", 'descrid', 'questid = ' . $question->id);


    $dom = new DOMDocument;
    $dom->loadHTML(mb_convert_encoding($comptree, 'HTML-ENTITIES', "UTF-8"));
    $xpath = new DOMXPath($dom);
    $nodes = $xpath->query('//form[@id="treeform"]');
    foreach ($nodes as $node) {
        $node->setAttribute('id', 'treeform' . $question->id);
    }
    $nodes = $xpath->query('//ul[@id="comptree"]');
    foreach ($nodes as $node) {
        $node->setAttribute('id', 'comptree' . $question->id);
    }
    $nodes = $xpath->query('//input[@type="checkbox"]');
    foreach ($nodes as $node) {
        $node->removeAttribute('checked');
        $node->setAttribute('class', $node->getAttribute('value'));
        if (in_array(intval($node->getAttribute('value')), $activedescriptors)) {
            $node->setAttribute('checked', 'checked' . $question->id);
        }
    }
    $nodes = $xpath->query('//input[@name="questid"]');
    foreach ($nodes as $node) {
        $node->setAttribute('value', $question->id);
    }

    return $dom->saveHTML();
}

function block_exacomp_is_disabled_create_grid() {
    return get_config('exacomp', 'disable_create_grid');
}

function block_exacomp_get_student_roleid() {
    // TODO: change it to possibly read the roles of a course?
    // $context = \context_course::instance($courseid);
    // $roles = get_all_roles($context);
    // var_dump($roles);

    return 5;
}

/**
 * @param $statusId
 */
function block_exacomp_get_human_readable_item_status($statusId) {
    if ($statusId === null) {
        return "new";
    }

    switch ($statusId) {
        case BLOCK_EXACOMP_ITEM_STATUS_INPROGRESS:
            return "inprogress";
        case BLOCK_EXACOMP_ITEM_STATUS_SUBMITTED:
            return "submitted";
        case BLOCK_EXACOMP_ITEM_STATUS_COMPLETED:
            return "completed";
        default:
            return "errornostate";
    }
}

/**
 * @return int|null
 */
function block_exacomp_convert_human_readable_item_status(string $statusStr) {
    switch ($statusStr) {
        case "new":
            return null;
        case "inprogress":
            return BLOCK_EXACOMP_ITEM_STATUS_INPROGRESS;
        case "submitted":
            return BLOCK_EXACOMP_ITEM_STATUS_SUBMITTED;
        case "completed":
            return BLOCK_EXACOMP_ITEM_STATUS_COMPLETED;
        default:
            throw new \moodle_exception("unknown status '$statusStr'");
    }
}

function block_exacomp_clear_exacomp_weekly_schedule() {
    // get all entries in schedule, check if the start and end date are in the last week and there is no submission
    // if yes, remove entry from schedule and move it back to planungsspeicher
    // submission: for examples there must be an item in the exacompitem_mm table with the exampleid and an entry in the exaportitem table with the userid of the student
    global $DB;
    $lastweek = time() - 7 * 24 * 60 * 60;

    $sql = "UPDATE {" . BLOCK_EXACOMP_DB_SCHEDULE . "} schedule
    JOIN {" . BLOCK_EXACOMP_DB_EXAMPLES . "} ex ON ex.id = schedule.exampleid
    SET schedule.start = null, schedule.endtime = null, is_overdue = 1
    WHERE schedule.start > :lastweek1
    AND schedule.start < :currenttime1
    AND ex.blocking_event = 0
    AND schedule.id NOT IN (
        SELECT sched.id
        -- select table inside a subquery, because else we get a
        -- 'You can't specify target table 'schedule' for update in FROM clause'
        -- error
        FROM (SELECT id, exampleid, start FROM {" . BLOCK_EXACOMP_DB_SCHEDULE . "}) AS sched
        JOIN {" . BLOCK_EXACOMP_DB_ITEM_MM . "} item_mm ON sched.exampleid = item_mm.exacomp_record_id
        JOIN {block_exaportitem} item ON item_mm.itemid = item.id
        WHERE sched.start > :lastweek2
        AND sched.start < :currenttime2
    )";

    $params = array("lastweek1" => $lastweek, "lastweek2" => $lastweek, "currenttime1" => time(), "currenttime2" => time());
    $DB->execute($sql, $params);

    // now delete all entries in weekly schedule where the associated course does not exist anymore
    $sql = "DELETE FROM {" . BLOCK_EXACOMP_DB_SCHEDULE . "}
    WHERE courseid NOT IN (SELECT id FROM {course})";
    $DB->execute($sql);
}

