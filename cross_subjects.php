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

use block_exacomp\cross_subject;

require __DIR__ . '/inc.php';

$courseid = required_param('courseid', PARAM_INT);
$showevaluation = optional_param('showevaluation', true, PARAM_BOOL);
$group = optional_param('group', 0, PARAM_INT);
$style = optional_param('style', 0, PARAM_INT);

// CHECK TEACHER
block_exacomp_require_login($courseid);
$isTeacher = block_exacomp_is_teacher();

$studentid = block_exacomp_get_studentid();
$editmode = optional_param('editmode', 0, PARAM_BOOL);

$crosssubjid = optional_param('crosssubjid', 0, PARAM_INT);
$action = optional_param('action', '', PARAM_TEXT);

/* PAGE IDENTIFIER - MUST BE CHANGED. Please use string identifier from lang file */
$page_identifier = 'tab_cross_subjects';

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/cross_subjects.php', [
    'courseid' => $courseid,
    'showevaluation' => $showevaluation,
    'studentid' => $studentid,
    'editmode' => $editmode,
    'crosssubjid' => $crosssubjid,
]);

$PAGE->set_heading(block_exacomp_get_string('blocktitle'));
$PAGE->set_title(block_exacomp_get_string($page_identifier));

$scheme = block_exacomp_get_assessment_theme_scheme($courseid);

$output = block_exacomp_get_renderer();

// build breadcrumbs navigation
block_exacomp_build_breadcrum_navigation($courseid);

$output->requires()->js('/blocks/exacomp/javascript/jquery.inputmask.bundle.js', true);
$output->requires()->js('/blocks/exacomp/javascript/competence_tree_common.js', true);
$output->requires()->css('/blocks/exacomp/css/competence_tree_common.css');
$PAGE->requires->js('/blocks/exacomp/javascript/fullcalendar/moment.min.js', true);
$PAGE->requires->js('/blocks/exacomp/javascript/jquery.daterangepicker.min.js', true);
$PAGE->requires->css('/blocks/exacomp/css/daterangepicker.min.css', true);

// $output->requires()->css('/blocks/exacomp/css/example_tree_crosssubjects.css');

if ($action == 'share') {
    $cross_subject = cross_subject::get($crosssubjid, MUST_EXIST);

    $cross_subject->require_capability(BLOCK_EXACOMP_CAP_MODIFY);

    if (optional_param('save', '', PARAM_TEXT)) {
        require_sesskey();
        $share_all = optional_param('share_all', false, PARAM_BOOL);
        $studentids = block_exacomp\param::optional_array('studentids', PARAM_INT);

        $cross_subject->update(['shared' => $share_all]);

        $DB->delete_records(BLOCK_EXACOMP_DB_CROSSSTUD, array('crosssubjid' => $crosssubjid));
        foreach ($studentids as $studentid) {
            $DB->insert_record(BLOCK_EXACOMP_DB_CROSSSTUD, ['crosssubjid' => $crosssubjid, 'studentid' => $studentid]);
        }

        echo $output->popup_close_and_reload();
        exit;
    }

    $PAGE->set_url('/blocks/exacomp/cross_subjects.php', array('courseid' => $courseid, 'action' => $action, 'crosssubjid' => $crosssubjid, 'sesskey' => sesskey()));
    $PAGE->set_heading(block_exacomp_get_string('blocktitle'));
    $PAGE->set_pagelayout('embedded');

    $output = block_exacomp_get_renderer();
    echo $output->header_v2();

    $students = block_exacomp_get_students_by_course($courseid);
    if (!$students) {
        echo block_exacomp_get_string('nostudents');
        echo $output->footer();
        exit;
    }

    $assigned_students = $DB->get_records_menu(BLOCK_EXACOMP_DB_CROSSSTUD, array('crosssubjid' => $crosssubjid), '', 'studentid,crosssubjid');
    $shared = $cross_subject->shared;

    echo '<form method="post" id="share">';
    echo '<input type="hidden" name="save" value="save" />';
    echo '<input type="hidden" name="sesskey" value=' . sesskey() . ' />';
    echo html_writer::checkbox('share_all', 'share_all', $shared, '');
    echo block_exacomp_get_string('share_crosssub_with_all', null, $cross_subject->title);
    echo html_writer::empty_tag('br') . html_writer::empty_tag('br');

    echo block_exacomp_get_string('share_crosssub_with_students', null, $cross_subject->title) . html_writer::empty_tag('br');

    foreach ($students as $student) {
        echo html_writer::checkbox('studentids[]', $student->id, isset($assigned_students[$student->id]), $student->firstname . " " . $student->lastname, $shared ? ['disabled' => true] : []);
        echo html_writer::empty_tag('br');
    }

    echo html_writer::empty_tag('br');
    echo html_writer::tag("input", '', array("type" => "submit", "value" => block_exacomp_get_string('save_selection')));
    echo '</form>';

    echo $output->footer();
    exit;
}

$activities = block_exacomp_get_activities_by_course($courseid);
$course_settings = block_exacomp_get_settings_by_course($courseid);

if ($course_settings->uses_activities && !$activities && !$course_settings->show_all_descriptors) {
    echo $output->header_v2('tab_cross_subjects');
    echo $output->no_activities_warning($isTeacher);
    echo $output->footer();
    exit;
}

$cross_subject = $crosssubjid ? cross_subject::get($crosssubjid, MUST_EXIST) : null;

if (optional_param('print', false, PARAM_BOOL)) {
    $output->print = true;
    if ($cross_subject) {
        $html_tables = array();
        if ($isTeacher) {

            $students = (!$cross_subject->is_draft() && $course_settings->nostudents != 1) ? block_exacomp_get_students_for_crosssubject($courseid, $cross_subject) : array();

            if (!$students) {
                $selectedStudentid = 0;
                $studentid = 0;
            } else if (isset($students[$studentid])) {
                $selectedStudentid = $studentid;
            } else {
                $selectedStudentid = 0;
                $studentid = BLOCK_EXACOMP_SHOW_ALL_STUDENTS;
            }
        } else {
            $students = array($USER);
            $selectedStudentid = $USER->id;
            $studentid = $USER->id;
        }

        foreach ($students as $student) {
            $student = block_exacomp_get_user_information_by_course($student, $courseid);
        }
        $subjects = block_exacomp_get_competence_tree_for_cross_subject($courseid,
            $cross_subject,
            $isTeacher, //!($course_settings->show_all_examples == 0 && !$isTeacher),
            $course_settings->filteredtaxonomies,
            ($studentid > 0 && !$isTeacher) ? $studentid : 0,
            ($isTeacher) ? false : true);

        if ($subjects) {
            //$html_pdf = $output->overview_legend($isTeacher);
            $html_pdf = $output->overview_metadata_cross_subjects($cross_subject, false);

            $competence_overview = $output->competence_overview($subjects,
                $courseid,
                $students,
                $showevaluation,
                $isTeacher ? BLOCK_EXACOMP_ROLE_TEACHER : BLOCK_EXACOMP_ROLE_STUDENT,
                $scheme,
                false,
                $cross_subject->id);

            $html_pdf .= $competence_overview;
            $html_tables[] = $html_pdf;
        }
        block_exacomp\printer::crossubj_overview($cross_subject, $subjects, $students, '', $html_tables);
    }
}

if ($action == 'descriptor_selector') {
    $cross_subject->require_capability(BLOCK_EXACOMP_CAP_MODIFY);

    if (optional_param('save', '', PARAM_TEXT)) {
        require_sesskey();
        $descriptors = block_exacomp\param::optional_array('descriptors', PARAM_INT);
        $old_descriptors = $DB->get_records_menu(BLOCK_EXACOMP_DB_DESCCROSS, array('crosssubjid' => $crosssubjid), null, 'descrid, descrid AS tmp');

        foreach ($descriptors as $descriptorid) {
            block_exacomp_set_cross_subject_descriptor($crosssubjid, $descriptorid);
            unset($old_descriptors[$descriptorid]);
        }

        foreach ($old_descriptors as $descriptorid) {
            block_exacomp_unset_cross_subject_descriptor($crosssubjid, $descriptorid);
        }

        echo $output->popup_close_and_reload();
        exit;
    }

    $PAGE->set_pagelayout('embedded');
    echo $output->header_v2();

    $active_descriptors = $DB->get_records_menu(BLOCK_EXACOMP_DB_DESCCROSS, ['crosssubjid' => $crosssubjid], null, 'id, descrid');

    $print_tree = function($items, $level = 0) use (&$print_tree, $active_descriptors, $USER) {
        if (!$items) {
            return '';
        }

        $output = '';
        if ($level == 0) {
            $output .= '<div class="exabis_competencies_lis"><table class="exabis_comp_comp rg2 rg2-reopen-checked rg2-check_uncheck_parents_children">';
        }

        foreach ($items as $item) {
            // only for descriptors: settings parameter 'show_teacherdescriptors_global'
            if (in_array($level, [2, 3])) {
                if (!get_config('exacomp', 'show_teacherdescriptors_global') && isset($item->descriptor_creatorid) && $item->descriptor_creatorid != $USER->id) {
                    continue;
                }
            }

            $output .= '<tr class="' . ($item instanceof block_exacomp\descriptor ? '' : 'highlight') . ' rg2-level-' . $level . '">';

            if ($item instanceof block_exacomp\subject) {
                $output .= '<td class="rg2-arrow rg2-indent" colspan="2"><div>';
            } else {
                if (block_exacomp_is_numbering_enabled()) {
                    $output .= '<td class="row-numbering">' . $item->get_numbering() . '</td>';
                }

                $output .= '<td class="rg2-arrow rg2-indent"><div>';

                if ($item instanceof block_exacomp\descriptor) {
                    if (in_array($item->id, $active_descriptors)) {
                        $checked = 'checked="checked"';
                    } else {
                        $checked = '';
                    }
                    $output .= '<input type="checkbox" name="descriptors[]" ' . $checked . ' value="' . $item->id . '">';
                } else if ($item instanceof block_exacomp\topic) {
                    // needed to allow selection of whole topic
                    // $output .= '<input type="checkbox" name="topic_tmp">';
                }
            }

            $output .= $item->title . '</div></td>';

            $output .= '</tr>';

            $output .= $print_tree($item->get_subs(), $level + 1);
        }

        if ($level == 0) {
            $output .= '</table></div>';
        }

        return $output;
    };

    $subjects = block_exacomp\db_layer_course::create($courseid)->get_subjects();

    // andere subjects laden, die nicht im kurs sind, aber im cross_subject
    $cross_subject_subjects = block_exacomp_get_subjects_for_cross_subject($cross_subject);
    foreach ($cross_subject_subjects as $cross_subject_subject) {
        if (!isset($subjects[$cross_subject_subject->id])) {
            $subjects[$cross_subject_subject->id] = $cross_subject_subject;
        }
    }

    echo '<form method="post">';
    echo $print_tree($subjects);
    echo '<input type="hidden" name="sesskey" value=' . sesskey() . ' />';
    echo '<input type="submit" name="save" value="' . block_exacomp_get_string('add_descriptors_to_crosssub') . '" class="btn btn-primary"/>';
    echo '</form>';

    echo $output->footer();
    exit;
}

if ($isTeacher && optional_param('save', '', PARAM_TEXT)) {
    require_sesskey();
    if ($cross_subject) {
        $cross_subject->require_capability(BLOCK_EXACOMP_CAP_MODIFY);
    } else {
        // add
        block_exacomp_require_teacher();
    }

    $data = [
        'subjectid' => required_param('subjectid', PARAM_INT),
        'title' => required_param('title', PARAM_TEXT),
        'groupcategory' => required_param('groupcategory', PARAM_TEXT),
        'description' => required_param('description', PARAM_TEXT),
    ];

    if ($cross_subject) {
        $cross_subject->update($data);
        redirect($PAGE->url);
    } else {
        $cross_subject = block_exacomp\cross_subject::create($data);
        $cross_subject->courseid = $courseid;
        $cross_subject->insert();

        $url = $PAGE->url;
        $url->param('crosssubjid', $cross_subject->id);
        $url->param('editmode', 1);
        redirect($url);
    }
    exit;
}
if ($isTeacher && $action == 'use_draft') {
    require_sesskey();
    $cross_subject->require_capability(BLOCK_EXACOMP_CAP_VIEW);

    $new_id = block_exacomp_save_drafts_to_course([$cross_subject->id], $COURSE->id);
    redirect(new moodle_url('/blocks/exacomp/cross_subjects.php', array('courseid' => $courseid, 'crosssubjid' => $new_id, 'editmode' => 1)));
}
if ($isTeacher && $action == 'save_as_draft') {
    require_sesskey();
    $cross_subject->require_capability(BLOCK_EXACOMP_CAP_MODIFY);

    $new_id = block_exacomp_save_drafts_to_course([$cross_subject->id], 0);
    redirect(new moodle_url('/blocks/exacomp/cross_subjects_overview.php', array('courseid' => $courseid)));
}

echo $output->header_v2($page_identifier);

//Delete timestamp (end|start) from example
/*
if($example_del = optional_param('exampleid', 0, PARAM_INT)){
	block_exacomp_delete_timefield($example_del, optional_param('deletestart', 0, PARAM_INT), optional_param('deleteend', 0, PARAM_INT));
}
*/

// TODO: wer schreibt alles uppercase?
// IF TEACHER SHOW ALL COURSE STUDENTS, IF NOT ONLY CURRENT USER
// TODO: logik hier kontrollieren
if ($isTeacher) {
    $students = ($cross_subject && !$cross_subject->is_draft() && $course_settings->nostudents != 1) ? block_exacomp_get_students_for_crosssubject($courseid, $cross_subject) : array();
    if (!$cross_subject) {
        $selectedStudentid = 0;
        $studentid = 0;
        $editmode = true;
    } else if ($editmode) {
        $selectedStudentid = 0;
        $studentid = 0;
    } else if (!$students) {
        if ($cross_subject && !$cross_subject->is_draft() && $course_settings->nostudents != 1) {
            echo html_writer::div(block_exacomp_get_string('share_crosssub_for_further_use'), "alert alert-warning");
        }
        // $editmode = true;
        $selectedStudentid = 0;
        $studentid = 0;
    } else if (isset($students[$studentid])) {
        $selectedStudentid = $studentid;
    } else {
        $selectedStudentid = 0;
        $studentid = BLOCK_EXACOMP_SHOW_ALL_STUDENTS;
    }
} else {
    $students = array($USER);
    $editmode = false;
    $selectedStudentid = $USER->id;
    $studentid = $USER->id;
}

if ($editmode) {
    block_exacomp_require_teacher();
    if ($cross_subject) {
        $cross_subject->require_capability(BLOCK_EXACOMP_CAP_MODIFY);
    }
} else {
    $cross_subject->require_capability(BLOCK_EXACOMP_CAP_VIEW);
}

$output->editmode = $editmode;

foreach ($students as $student) {
    $student = block_exacomp_get_user_information_by_course($student, $courseid);
}

if ($editmode) {
    echo html_writer::start_tag('form', array('id' => 'cross-subject-data', "action" => $PAGE->url->out(false, array('sesskey' => sesskey())), 'method' => 'post'));
    echo '<input type="hidden" name="save" value="save" />';
}

//schooltypes
/*$schooltypes = block_exacomp_get_schooltypes_by_course($courseid);

$schooltype_title = "";
foreach($schooltypes as $schooltype){
	$schooltype_title .= $schooltype->title . ", ";
}
$schooltype = substr($schooltype_title, 0, strlen($schooltype_title)-1);
*/
echo $output->overview_metadata_cross_subjects($cross_subject, $editmode);

//$scheme = block_exacomp_get_grading_scheme($courseid);

if (!$isTeacher) {
    $user_evaluation = block_exacomp_get_user_information_by_course($USER, $courseid);

    $cm_mm = block_exacomp_get_course_module_association($courseid);
    $course_mods = get_fast_modinfo($courseid)->get_cms();

    //TODO: test with activities
    /*$activities_student = array();
    if(isset($cm_mm->topics[$selectedTopic->id]))
        foreach($cm_mm->topics[$selectedTopic->id] as $cmid)
            $activities_student[] = $course_mods[$cmid];*/
}

echo $output->cross_subject_buttons($cross_subject, $students, $selectedStudentid, ($course_settings->nostudents != 1));

if ($isTeacher) {
    if ($studentid == BLOCK_EXACOMP_SHOW_ALL_STUDENTS) {
        //$showevaluation = false;   why?
        $showevaluation = true;
        echo $output->students_column_selector(count($students), 'cross_subjects');
    } else if ($studentid == 0) {
        $students = array();
    } else if (!empty($students[$studentid])) {
        $students = array($students[$studentid]);
        $showevaluation = true;
    }
} else {
    $showevaluation = true;
}

if ($editmode) {
    echo html_writer::end_tag('form');
}

if ($cross_subject) {
    $subjects = block_exacomp_get_competence_tree_for_cross_subject($courseid, $cross_subject, $isTeacher /*!($course_settings->show_all_examples == 0 && !$isTeacher)*/, $course_settings->filteredtaxonomies,
        ($studentid > 0 && !$isTeacher) ? $studentid : 0, ($isTeacher) ? false : true);
    if ($subjects) {
        if ($style == 0) {
            echo $output->overview_legend($isTeacher);
            echo html_writer::start_tag('form', array('id' => 'assign-competencies', "action" => $PAGE->url, 'method' => 'post'));
            echo html_writer::start_tag("div", array("class" => "exabis_competencies_lis"));
            $competence_overview = $output->competence_overview($subjects, $courseid, $students, $showevaluation, $isTeacher ? BLOCK_EXACOMP_ROLE_TEACHER : BLOCK_EXACOMP_ROLE_STUDENT, $scheme, false, $cross_subject->id);
            echo $competence_overview;
            echo html_writer::end_tag("div");
            echo html_writer::end_tag('form');
        } else if ($style == 1) {
            echo $output->overview_legend($isTeacher);
            echo html_writer::start_tag('form', array('id' => 'assign-competencies', "action" => $PAGE->url, 'method' => 'post'));
            echo html_writer::start_tag("div", array("class" => "exabis_competencies_lis"));
            echo $output->example_based_overview($subjects, $courseid, $students, $showevaluation, $isTeacher ? BLOCK_EXACOMP_ROLE_TEACHER : BLOCK_EXACOMP_ROLE_STUDENT, $scheme, false, $cross_subject->id);
            echo html_writer::end_tag("div");
            echo html_writer::end_tag('form');

            // 	        //could be optimized together with block_exacomp_build_example_tree
            // 	        //non critical - only 1 additional query for whole loading process
            // 	        $examples = \block_exacomp\example::get_objects_sql("
            //                 SELECT DISTINCT e.*
            //                 FROM {".BLOCK_EXACOMP_DB_COURSETOPICS."} ct
            //                 JOIN {".BLOCK_EXACOMP_DB_DESCTOPICS."} dt ON ct.topicid = dt.topicid
            //                 JOIN {".BLOCK_EXACOMP_DB_DESCEXAMP."} de ON dt.descrid = de.descrid
            //                 JOIN {".BLOCK_EXACOMP_DB_EXAMPLES."} e ON e.id = de.exampid
            //                 WHERE ct.courseid = ?
            //                 ORDER BY e.title
            //             ", [$courseid]);

            // 	        if (!$isTeacher) {
            // 	            $examples = array_filter($examples, function($example) use ($courseid, $studentid) {
            // 	                return block_exacomp_is_example_visible($courseid, $example, $studentid);
            // 	            });
            // 	        }

            //             echo $output->example_based_list_tree($examples);

            // 	        echo $output->overview_legend($isTeacher);
            // 	        echo html_writer::start_tag('form', array('id'=>'assign-competencies', "action" => $PAGE->url, 'method'=>'post'));
            // 	        echo html_writer::start_tag("div", array("class"=>"exabis_competencies_lis"));
            // 	        echo $output->competence_overview($subjects, $courseid, $students, $showevaluation, $isTeacher ? BLOCK_EXACOMP_ROLE_TEACHER : BLOCK_EXACOMP_ROLE_STUDENT, $scheme, false, $cross_subject->id);
            // 	        echo html_writer::end_tag("div");
            // 	        echo html_writer::end_tag('form');
        }

    } else {
        echo html_writer::div(
            block_exacomp_get_string('add_content_to_crosssub'),
            "alert alert-warning");
    }
}

/* END CONTENT REGION */
echo $output->footer();
