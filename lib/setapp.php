<?php

defined('MOODLE_INTERNAL') || die;

function block_exacomp_is_setapp_enabled() {
    return get_config('exacomp', 'setapp_enabled');
}

function block_exacomp_require_setapp_enabled() {
    if (!block_exacomp_is_setapp_enabled()) {
        throw new Exception('diggr v not activated');
    }
}

function block_exacomp_is_diggrv_student($user) {
    return ($user->description == "diggrv" && preg_match('!^diggrv-!', $user->username));
}

function block_exacomp_diggrv_create_first_course() {
    global $DB, $USER, $CFG;

    require_once("$CFG->dirroot/course/lib.php");

    if (block_exacomp_get_teacher_courses($USER->id)) {
        // already has one course
        return;
    }

    // course_category exists?
    $course_category = $DB->get_record('course_categories', ['idnumber' => 'diggrv']);
    // alternative:
    // core_course_category::get_all();

    if (!$course_category) {
        $course_category = core_course_category::create([
            'name' => 'Diggr V',
            'parent' => 0,
            'idnumber' => 'diggrv',
            'description' => 'Automatically created',
        ]);
    }

    $course = new stdClass();
    $course->shortname = 'Klasse - ' . fullname($USER) . ' ' . date('d.m.Y H:i'); // has to be unique!
    $course->fullname = 'Klasse - ' . fullname($USER);
    $course->summary = '';

    $course->idnumber = 'diggrv-' . round((microtime(true) - 1600000000) * 1000);
    // $course->format = $courseconfig->format;
    $course->visible = 1;
    // $course->newsitems = $courseconfig->newsitems;
    // $course->showgrades = $courseconfig->showgrades;
    // $course->showreports = $courseconfig->showreports;
    // $course->maxbytes = $courseconfig->maxbytes;
    // $course->groupmode = $courseconfig->groupmode;
    // $course->groupmodeforce = $courseconfig->groupmodeforce;
    // $course->enablecompletion = $courseconfig->enablecompletion;
    // Insert default names for teachers/students, from the current language.

    $course->category = $course_category->id;

    $course->startdate = time();
    // Choose a sort order that puts us at the start of the list!
    $course->sortorder = 0;

    $course = create_course($course);

    $userid = $USER->id;
    $courseid = $course->id;

    // enrol the student
    $enrol = enrol_get_plugin("manual"); //enrolment = manual
    $instances = enrol_get_instances($courseid, true);
    $manualinstance = null;
    foreach ($instances as $instance) {
        if ($instance->enrol == "manual") {
            $manualinstance = $instance;
            break;
        }
    }

    $enrol->enrol_user($manualinstance, $userid, 3); //The roleid of "editingteacher" is 3 in mdl_role table

    // add exacomp block, so the course is visible in diggr-plus
    $now = time();
    $parentcontextid = context_course::instance($courseid)->id;
    $blockinstance = [
        'blockname' => 'exacomp',
        'parentcontextid' => $parentcontextid,
        'showinsubcontexts' => false,
        'pagetypepattern' => 'course-view-*',
        'subpagepattern' => null,
        'defaultregion' => 'side-pre',
        'defaultweight' => 0,
        'configdata' => '',
        'timecreated' => $now,
        'timemodified' => $now,
    ];
    $DB->insert_record('block_instances', $blockinstance);
    // alternative:
    // add_block_at_end_of_default_region

    // only allow manual enrolment method
    // status=1 = disabled, strange but true
    $DB->execute("UPDATE {enrol} SET status=1 WHERE enrol<>'manual' AND courseid=?", [$courseid]);
}

/**
 * @param $schoolcode
 * @throws coding_exception
 * @throws dml_exception
 * @throws moodle_exception
 * Used for creating a course as a teacher. The teacher provides a name for the class and a schoolcode (Schulkennzahl)
 * This schoolcode is checked for existance and the class/course is created and the teacher is assigned to it as a teacher
 */
function block_exacomp_diggrv_create_course($coursename, $schoolcode) {
    global $DB, $USER, $CFG;

    require_once("$CFG->dirroot/course/lib.php");

    // course_category exists?
    $course_category = $DB->get_record('course_categories', ['idnumber' => 'diggrv']);
    // alternative:
    // core_course_category::get_all();

    if (!$course_category) {
        $course_category = core_course_category::create([
            'name' => 'Diggr V',
            'parent' => 0,
            'idnumber' => 'diggrv',
            'description' => 'Created by diggrv teachers',
        ]);
    }

    $course = new stdClass();
    $course->shortname = $coursename . ' ' . date('d.m.Y H:i'); // has to be unique!
    $course->fullname = $coursename;
    $course->summary = '';

    $course->idnumber = 'diggrv-' . round((microtime(true) - 1600000000) * 1000);
    // $course->format = $courseconfig->format;
    $course->visible = 1;
    // $course->newsitems = $courseconfig->newsitems;
    // $course->showgrades = $courseconfig->showgrades;
    // $course->showreports = $courseconfig->showreports;
    // $course->maxbytes = $courseconfig->maxbytes;
    // $course->groupmode = $courseconfig->groupmode;
    // $course->groupmodeforce = $courseconfig->groupmodeforce;
    // $course->enablecompletion = $courseconfig->enablecompletion;
    // Insert default names for teachers/students, from the current language.

    $course->category = $course_category->id;

    $course->startdate = time();
    // Choose a sort order that puts us at the start of the list!
    $course->sortorder = 0;

    $course = create_course($course);

    $userid = $USER->id;
    $courseid = $course->id;

    // enrol the teacher
    $enrol = enrol_get_plugin("manual"); //enrolment = manual
    $instances = enrol_get_instances($courseid, true);
    $manualinstance = null;
    foreach ($instances as $instance) {
        if ($instance->enrol == "manual") {
            $manualinstance = $instance;
            break;
        }
    }

    $enrol->enrol_user($manualinstance, $userid, 3); //The roleid of "editingteacher" is 3 in mdl_role table

    // add exacomp block, so the course is visible in diggr-plus
    $now = time();
    $parentcontextid = context_course::instance($courseid)->id;
    $blockinstance = [
        'blockname' => 'exacomp',
        'parentcontextid' => $parentcontextid,
        'showinsubcontexts' => false,
        'pagetypepattern' => 'course-view-*',
        'subpagepattern' => null,
        'defaultregion' => 'side-pre',
        'defaultweight' => 0,
        'configdata' => '',
        'timecreated' => $now,
        'timemodified' => $now,
    ];
    $DB->insert_record('block_instances', $blockinstance);
    // alternative:
    // add_block_at_end_of_default_region

    // only allow manual enrolment method
    // status=1 = disabled, strange but true
    $DB->execute("UPDATE {enrol} SET status=1 WHERE enrol<>'manual' AND courseid=?", [$courseid]);
}

