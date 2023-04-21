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

// delete it!!!!
//set_time_limit(10);
//ini_set('display_errors', 1);
//error_reporting(E_ALL);
//deleted it!!!!

require __DIR__ . '/inc.php';

$courseid = required_param('courseid', PARAM_INT);
$showevaluation = optional_param("showevaluation", true, PARAM_BOOL);
$group = optional_param('group', 0, PARAM_INT);

$editmode = optional_param('editmode', 0, PARAM_BOOL);
$subjectid = optional_param('subjectid', 0, PARAM_INT);

$topicid = optional_param('topicid', 0, PARAM_INT);
$niveauid = optional_param('niveauid', BLOCK_EXACOMP_SHOW_ALL_NIVEAUS, PARAM_INT);

block_exacomp_require_login($courseid);

$slicestudentlist = false;
if (get_config('exacomp', 'disable_js_assign_competencies')) {
    $columngroupnumber = optional_param('colgroupid', 0, PARAM_INT);
    if ($columngroupnumber > -1) { // -1 - show all!
        $slicestudentlist = true;
        $slicestartposition = $columngroupnumber * BLOCK_EXACOMP_STUDENTS_PER_COLUMN;
    }
}

// CHECK TEACHER
$isTeacher = block_exacomp_is_teacher();
if (!$isTeacher) {
    $editmode = 0;
}
$isEditingTeacher = block_exacomp_is_editingteacher($courseid, $USER->id);

$studentid = block_exacomp_get_studentid();
if ($studentid == 0) {
    $studentid = BLOCK_EXACOMP_SHOW_ALL_STUDENTS;
}

$selectedStudentid = $studentid;

if ($editmode) {
    $selectedStudentid = $studentid;
    $studentid = BLOCK_EXACOMP_SHOW_ALL_STUDENTS;
}

$page_identifier = 'tab_competence_overview';

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/assign_competencies.php', [
    'courseid' => $courseid,
    'showevaluation' => $showevaluation,
    'studentid' => $selectedStudentid,
    'editmode' => $editmode,
    'niveauid' => $niveauid,
    'subjectid' => $subjectid,
    'topicid' => $topicid,
]);
$PAGE->set_heading(block_exacomp_get_string('blocktitle'));
$PAGE->set_title(block_exacomp_get_string($page_identifier));

// build breadcrumbs navigation
block_exacomp_build_breadcrum_navigation($courseid);

/**
 * @var block_exacomp_renderer
 */
$output = block_exacomp_get_renderer();
$output->requires()->js('/blocks/exacomp/javascript/jquery.inputmask.bundle.js', true);
$output->requires()->js('/blocks/exacomp/javascript/competence_tree_common.js', true);
$output->requires()->css('/blocks/exacomp/css/competence_tree_common.css');
$PAGE->requires->js('/blocks/exacomp/javascript/fullcalendar/moment.min.js', true);
$PAGE->requires->js('/blocks/exacomp/javascript/jquery.daterangepicker.min.js', true);
$PAGE->requires->css('/blocks/exacomp/css/daterangepicker.min.css', true);
$output->editmode = $editmode;

$activities = block_exacomp_get_activities_by_course($courseid);
$course_settings = block_exacomp_get_settings_by_course($courseid);

if ($course_settings->uses_activities && !$activities && !$course_settings->show_all_descriptors) {
    echo $output->header_v2($page_identifier);
    echo $output->no_activities_warning($isTeacher);
    echo $output->footer();
    exit;
}

$ret = block_exacomp_init_overview_data($courseid, $subjectid, $topicid, $niveauid, $editmode, $isTeacher, ($isTeacher ? 0 : $USER->id), ($isTeacher) ? false : true, @$course_settings->hideglobalsubjects);

if (!$ret) {
    print_error('not configured');
}
list($courseSubjects, $courseTopics, $niveaus, $selectedSubject, $selectedTopic, $selectedNiveau) = $ret;

// IF TEACHER SHOW ALL COURSE STUDENTS, IF NOT ONLY CURRENT USER
if ($isTeacher) {
    //if ($slicestudentlist) {
    //    $limitfrom = $slicestartposition + 1; // sql from
    //    $limitnum = BLOCK_EXACOMP_STUDENTS_PER_COLUMN;
    //} else {
    $limitfrom = '';
    $limitnum = '';
    //}
    $students = $allCourseStudents = block_exacomp_get_students_by_course($courseid, $limitfrom, $limitnum);
} else {
    $students = $allCourseStudents = array($USER->id => $USER);
}

//Add the local groups
$groups = ($isTeacher) ? groups_get_all_groups($courseid) : array();
if ($course_settings->nostudents) {
    $allCourseStudents = array();
}

//var_dump($editmode);
//die;
$competence_tree = block_exacomp_get_competence_tree($courseid,
    $selectedSubject ? $selectedSubject->id : null,
    $selectedTopic ? $selectedTopic->id : null,
    false,
    $selectedNiveau ? $selectedNiveau->id : null,
    true,
    $course_settings->filteredtaxonomies,
    true,
    false,
    false,
    false,
    ($isTeacher) ? false : true,
    false,
    null,
    $editmode);

// skip all niveaus that are empty for the selected topic if not in editmode
if ($topicid && $topicid != -1) {
    $used_niveaus = $competence_tree[$selectedSubject->id]->topics[$topicid]->used_niveaus;
    foreach ($niveaus as $k => $niveau) {
        if (!$editmode) {
            if (!isset($used_niveaus[$niveau->id]) && $niveau->id != BLOCK_EXACOMP_SHOW_ALL_NIVEAUS) {
                if ($selectedNiveau->id == $niveau->id) {
                    $selectedNiveau = $niveaus[BLOCK_EXACOMP_SHOW_ALL_NIVEAUS]; // if e.g. niveau with id 6 is clicked, then the topic is switched and niveau 6 does not exist ==> go to "show all"
                    $PAGE->set_url('/blocks/exacomp/assign_competencies.php', [
                        'courseid' => $courseid,
                        'showevaluation' => $showevaluation,
                        'studentid' => $selectedStudentid,
                        'editmode' => $editmode,
                        'niveauid' => $selectedNiveau->id,
                        'subjectid' => $subjectid,
                        'topicid' => $topicid,
                    ]);
                    redirect($PAGE->url);
                }
                unset($niveaus[$k]);
            }
        }
    }
}

$scheme = block_exacomp_get_grading_scheme($courseid);
$colselector = "";
if ($isTeacher) {    //mind nostudents setting
    if ($studentid == BLOCK_EXACOMP_SHOW_ALL_STUDENTS && $editmode == 0 && $course_settings->nostudents != 1) {
        $colselector = $output->students_column_selector(count($allCourseStudents), 'assign_competencies');
        // slice students list if need
        if ($slicestudentlist) {
            if (count($students) < ($columngroupnumber * BLOCK_EXACOMP_STUDENTS_PER_COLUMN)) {
                $slicestartposition = 0;
            }
            $students = array_slice($students, $slicestartposition, BLOCK_EXACOMP_STUDENTS_PER_COLUMN);
        }
    } else if (!$studentid || $course_settings->nostudents == 1 || ($studentid == BLOCK_EXACOMP_SHOW_ALL_STUDENTS && $editmode = 1)) {
        $students = array();
    } else if ($studentid < -1) {
        //MAYBE CHANGE WORDING   studentId is actually student or localgroup id.... if it is a localgroup, the value is negative and the groupid can be caluclated as follows:
        //((-1)*dropdownvalue)-1   the -1 is used for ALL_STUDENTS, this is why i calculate it like this    RW
        $groupid = (-1) * $studentid - 1;
        // 	    $students = groups_get_members($groupid);
        $students = block_exacomp_groups_get_members($courseid, $groupid);
        // slice students list if need
        $colselector = $output->students_column_selector(count($students), 'assign_competencies');
        if ($slicestudentlist) {
            if (count($students) < ($columngroupnumber * BLOCK_EXACOMP_STUDENTS_PER_COLUMN)) {
                $slicestartposition = 0;
            }
            $students = array_slice($students, $slicestartposition, BLOCK_EXACOMP_STUDENTS_PER_COLUMN);
        }
    } else {
        $students = !empty($students[$studentid]) ? array($students[$studentid]) : $students;
    }
}

if (sizeof($students) == 1) {
    // if only one student is selected: update the example visibilities for the case that e.g. an activity has been set to available=true because of e.g. the date.
    block_exacomp_update_related_examples_visibilities_for_single_student($courseid, reset($students)->id); //reset gets the first element (not $student[0] because it is associative)
}

foreach ($students as $student) {
    block_exacomp_get_user_information_by_course($student, $courseid);
}

if (optional_param('print', false, PARAM_BOOL)) {
    $output->print = true;
    $html_tables = [];

    if ($group == -1) {
        // all students, do nothing
    } else {
        // get the students on this group
        $students = array_slice($students, $group * BLOCK_EXACOMP_STUDENTS_PER_COLUMN, BLOCK_EXACOMP_STUDENTS_PER_COLUMN, true);
    }

    // TODO: print column information for print

    // loop through all pages (eg. when all students should be printed)
    for ($group_i = 0; $group_i < count($students); $group_i += BLOCK_EXACOMP_STUDENTS_PER_COLUMN) {
        $students_to_print = array_slice($students, $group_i, BLOCK_EXACOMP_STUDENTS_PER_COLUMN, true);

        $html_header = $output->overview_metadata($selectedSubject->title, $selectedTopic, null, $selectedNiveau);

        // $html .= "&nbsp;<br />";

        $competence_overview = $output->competence_overview($competence_tree,
            $courseid,
            $students_to_print,
            $showevaluation,
            $isTeacher ? BLOCK_EXACOMP_ROLE_TEACHER : BLOCK_EXACOMP_ROLE_STUDENT,
            $scheme,
            $selectedNiveau->id != BLOCK_EXACOMP_SHOW_ALL_NIVEAUS,
            0,
            $isEditingTeacher);

        $html_tables[] = $competence_overview;
    }

    block_exacomp\printer::competence_overview($selectedSubject, $selectedTopic, $selectedNiveau, null, $html_header, $html_tables);
}

echo $output->header_v2($page_identifier);
echo $colselector;
echo $output->competence_overview_form_start($selectedNiveau, $selectedTopic, $studentid, $editmode);

// dropdowns for subjects and topics and students -> if user is teacher and working with students
echo $output->overview_dropdowns('assign_competencies', $allCourseStudents, $selectedStudentid, $isTeacher, $isEditingTeacher, $groups);

echo '<div class="clearfix"></div>';

if ($selectedNiveau->id != BLOCK_EXACOMP_SHOW_ALL_NIVEAUS) {
    echo $output->overview_metadata($selectedSubject->title, $selectedTopic, null, $selectedNiveau);
    if ($isTeacher) {
        echo $output->overview_metadata_teacher($selectedTopic, $selectedNiveau);
    } else {
        $cm_mm = block_exacomp_get_course_module_association($courseid);
        $course_mods = get_fast_modinfo($courseid)->get_cms();

        $activities_student = array();
        if (isset($cm_mm->topics[$selectedNiveau->id])) {
            foreach ($cm_mm->topics[$selectedNiveau->id] as $cmid) {
                $activities_student[] = $course_mods[$cmid];
            }
        }
    }
}

echo html_writer::start_tag("div", array("id" => "exabis_competences_block"));
echo html_writer::start_tag("div", array("class" => "exabis_competencies_lis"));

echo html_writer::start_tag("div", array("class" => "gridlayout"));

$competence_overview = $output->competence_overview($competence_tree,
    $courseid,
    $students,
    $showevaluation,
    $isTeacher ? BLOCK_EXACOMP_ROLE_TEACHER : BLOCK_EXACOMP_ROLE_STUDENT,
    $scheme,
    ($selectedNiveau->id != BLOCK_EXACOMP_SHOW_ALL_NIVEAUS),
    0,
    $isEditingTeacher);

echo '<div class="gridlayout-left">';
echo $output->subjects_menu($courseSubjects, $selectedSubject, $selectedTopic, $students, $editmode);
echo '</div>';
echo '<div class="gridlayout-right">';

echo $output->niveaus_menu($niveaus, $selectedNiveau, $selectedTopic, $selectedSubject);

echo '<div class="clearfix"></div>';

if ($course_settings->nostudents != 1) {
    echo $output->overview_legend($isTeacher);
}
if ($course_settings->nostudents != 1 && $studentid) {
    echo $output->student_evaluation($showevaluation, $isTeacher, $selectedNiveau->id, $subjectid, $topicid, $studentid);
}

echo $competence_overview;

echo '</div>';
echo html_writer::end_tag("div");
echo html_writer::end_tag("div");
echo html_writer::end_tag("div");

/* END CONTENT REGION */

echo $output->footer();
