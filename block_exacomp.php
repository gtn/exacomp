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

require_once __DIR__ . '/../moodleblock.class.php';
require_once __DIR__ . '/inc.php';

class block_exacomp extends block_list {

    function init() {
        $this->title = block_exacomp_get_string('blocktitle');
    }

    function specialization() {
        if ($this->isExaplanDashboardBlock()) {
            $this->title = block_exacomp_get_string('overview_examples_report_title');
        }
    }

    function applicable_formats() {
        // block can only be installed in courses
        $formats = array('all' => true,
            'mod' => false,
            'tag' => false,
            'my' => false);
        // allow to add block in the dashboard if 'exaplan' is installed
        if (block_exacomp_exaplanexists()) {
            $formats['my'] = true;
        }
        return $formats;
    }

    function hide_header() {
        if ($this->isExaplanDashboardBlock()) {
            //            return true; // used another title
        }
        return false;
    }

    function isExaplanDashboardBlock() {
        global $DB;
        $instanceId = $this->context->instanceid;
        $blockData = $DB->get_record('block_instances', ['id' => $instanceId], '*');
        if ($blockData->defaultregion == 'content' && $blockData->pagetypepattern == 'my-index') {
            return true;
        }
        return false;
    }

    function get_content() {
        global $CFG, $USER, $COURSE, $DB, $PAGE;

        if ($this->isExaplanDashboardBlock()) {
            // content for exaplan dashboard

            $content = '';
            $output = block_exacomp_get_renderer();
            $studentid = optional_param('studentid', BLOCK_EXACOMP_SHOW_ALL_STUDENTS, PARAM_INT);
            $courseid = optional_param('courseid', 0, PARAM_INT);

            // get all courses where the user is a teacher
            $teacherCourses = block_exacomp_get_courses_of_teacher($USER->id);
            // get all students from these courses
            $coursestudents = [];
            foreach ($teacherCourses as $cId) {
                $coursestudents = array_merge($coursestudents, block_exacomp_get_students_by_course($cId));
            }
            // sort by last name + first name
            usort($coursestudents, function($a, $b) {
                $aName = $a->lastname . ' ' . $a->firstname;
                $bName = $b->lastname . ' ' . $b->firstname;
                return strcmp($aName, $bName);
            });

            $content .= '<div style="padding-bottom: 15px;" id="reportExamples">';

            // student selector
            if (block_exacomp_is_teacher_in_any_course()) {
                $content .= '<form action="" method="get">';

                if ($studentid == BLOCK_EXACOMP_SHOW_ALL_STUDENTS) {
                    $content .= html_writer::tag("p", block_exacomp_get_string("select_student"));
                }
                $content .= block_exacomp_get_string('choosestudent');
                $content .= $output->studentselector($coursestudents, $studentid, null, null, ['name' => 'studentid', 'onChange' => 'this.form.submit()']);

                $content .= '</form>';
            } else {
                // TODO: block_exacomp_is_student() does not work - why?
                // get 'is a student' by his courses
                $studentCourses = enrol_get_users_courses($USER->id, true, '*');
                if (count($studentCourses) > 0) { // this is a student
                    $studentid = $USER->id;
                }
            }
            // dashboard of students data (tabs with courses)
            if ($studentid > 0) {

                $student = $DB->get_record('user', array('id' => $studentid));

                // by default show first course as selected
                $possible_courses = block_exacomp_get_exacomp_courses($student);
                if (!$courseid) {
                    $courseid = reset($possible_courses)->id;
                }

                $mod_info = get_fast_modinfo($courseid);

                // students data (evaluations of examples)
                $studentExampleEvaluations = block_exacomp_get_user_examples_by_course($student, $courseid);

                // main report content
                $content .= '<div>';

                // student's overview
                $content .= $output->competence_profile_metadata($student, 1);

                // tabs with course titles for selected student
                $content .= '<ul class="nav nav-tabs mb-3">';
                foreach ($possible_courses as $tempCourseId => $course) {
                    $active = ($course->id == $courseid ? 'active' : '');
                    $courseLink = new moodle_url('/my', array('studentid' => $studentid, 'courseid' => $course->id));
                    $content .= '<li class="nav-item"><a class="nav-link ' . $active . '" href="' . $courseLink . '#reportExamples">' . $course->fullname . '</a></li>';
                }
                $content .= '</ul>';

                $course = $possible_courses[$courseid];

                $examples = block_exacomp_get_examples_by_course($courseid);
                if (count($examples) > 0) {
                    // to order
                    $examples = \block_exacomp\example::get_objects_sql("
                            SELECT DISTINCT e.*
                              FROM {" . BLOCK_EXACOMP_DB_EXAMPLES . "} e
                              WHERE e.id IN (" . implode(',', array_keys($examples)) . ")
                              ORDER BY e.title ");
                }

                // set example 'finished' value
                foreach ($examples as $exInd => $example) {
                    $finished = COMPLETION_INCOMPLETE;
                    // first: use teacher evaluation
                    if (isset($studentExampleEvaluations->teacher[$example->id])) {
                        $finished = $studentExampleEvaluations->teacher[$example->id];
                    } else if (@$cm = $mod_info->cms[$example->activityid]) {
                        // second: use default sactivity completion method
                        $completionFunc = $cm->modname . '_get_completion_state';
                        if (function_exists($completionFunc)) {
                            $finished = $completionFunc($course, $cm, $studentid, 'not-defined'); // not-defined - if the activity has not conditions to get complete status
                        }
                    }
                    $examples[$exInd]->finished = $finished;
                }

                // add related activities (with old method)
                // then such activities will be used for creating virtual examples to get parent names for better view
                if (block_exacomp_use_old_activities_method()) {
                    $virtualExamples = [];
                    $activities = block_exacomp_get_activities_by_course($courseid, 'camm.*');

                    $i = 1;
                    foreach ($activities as $act) {
                        $module = get_coursemodule_from_id(null, $act->activityid);
                        $activitylink = block_exacomp_get_activityurl($module)->out(false);

                        $example_icons = null;
                        $finished = COMPLETION_INCOMPLETE;
                        if (@$cm = $mod_info->cms[$act->activityid]) {
                            // completion by default activity completion method
                            if ($cm->completion == COMPLETION_TRACKING_MANUAL) {
                                // if the student set up completion manually}
                                $completionData = $DB->get_record('course_modules_completion', array('coursemoduleid' => $cm->id, 'userid' => $studentid));
                                if ($completionData && $completionData->completionstate == COMPLETION_COMPLETE) {
                                    $finished = true;
                                }
                            } else {
                                // for automatic completion methods
                                $completionFunc = $cm->modname . '_get_completion_state';
                                if (function_exists($completionFunc)) {
                                    $finished = $completionFunc($course, $cm, $studentid, 'not-defined'); // not-defined - if the activity has not conditions to get complete status
                                }
                            }
                            $example_icons = $cm->get_icon_url()->out(false);
                            $example_icons = serialize(array('externaltask' => $example_icons));
                        }

                        $newExample = new \block_exacomp\example([
                            'id' => -$i,
                            'title' => $act->activitytitle,
                            'isVirtualExample' => true,
                            'levelOfVirtualParent' => $act->comptype,
                            'idOfVirtualParent' => $act->compid,
                            'externaltask' => $activitylink, // link to activity
                            'externalurl' => '',
                            'externalsolution' => '',
                            'example_icon' => $example_icons,
                            'finished' => $finished,
                        ]);
                        $examples[-$i] = $newExample;
                        $i++;
                    }

                }

                if ($studentid) {
                    $urlParams = ['courseid' => $courseid];
                    if (block_exacomp_is_teacher_in_any_course() && $studentid != $USER->id) { // 'studentid' is only for admins
                        $urlParams['studentid'] = $studentid;
                    }
                    $overviewLink = new moodle_url('/blocks/exacomp/competence_grid.php', $urlParams);
                    $content .= '<div class="pull-right"><a href="' . $overviewLink . '" class="btn btn-sm btn-info">Kompetenzbewertung</a> </div>';
                }

                // main report for selected student and selected course
                if (count($examples) > 0) {
                    $cont = $output->example_based_list_tree($examples, $courseid, $courseid, $studentid, true);
                    $content .= html_writer::div($cont, '', ['id' => 'exacomp_tabbed_course_' . $course->id]);
                } else {
                    $content .= html_writer::div('kein Lernmaterial hier', 'alert alert-warning', ['id' => 'exacomp_tabbed_course_' . $course->id]);
                }

                $content .= '</div>';
            }
            $content .= '</div>';


            $this->content = new stdClass();
            $this->content->icons = array();
            $this->content->items[] = $content;
            return $this->content;
        }

        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';

            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->footer = '';
        $this->content->icons = array();
        $this->content->items = array();

        // user/index.php expect course context, so get one if page has module context.
        $currentcontext = $this->page->context->get_course_context(false);
        $globalcontext = context_system::instance();

        if (empty($currentcontext)) {
            return $this->content;
        }

        $courseid = intval($COURSE->id);

        if (block_exacomp_is_skillsmanagement()) {
            $checkConfig = block_exacomp_is_configured($courseid);
        } else {
            $checkConfig = block_exacomp_is_configured();
        }

        //$has_data = block_exacomp\data::has_data();
        $has_data = true; // 22.03.2020 SZ

        $courseSettings = block_exacomp_get_settings_by_course($courseid);

        $ready_for_use = block_exacomp_is_ready_for_use($courseid);

        $de = false;
        $lang = current_language();
        if (isset($lang) && substr($lang, 0, 2) === 'de') {
            $de = true;
        }

        $isTeacher = block_exacomp_is_teacher($currentcontext) && $courseid != 1;
        $isStudent = has_capability('block/exacomp:student', $currentcontext) && $courseid != 1 && !has_capability('block/exacomp:admin', $currentcontext);
        $isTeacherOrStudent = $isTeacher || $isStudent;
        // $lis = block_exacomp_is_altversion();
        if ($checkConfig && $has_data) {    //Modul wurde konfiguriert

            if ($isTeacherOrStudent && $ready_for_use) {
                //Kompetenzüberblick
                $icon = '<img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/competencegrid.svg' . '" class="icon" alt="" />';
                $this->content->items[] = '<a title="' . block_exacomp_get_string('tab_competence_overview') . '" ' .
                    ' href="' . $CFG->wwwroot . '/blocks/exacomp/assign_competencies.php?courseid=' . $courseid . '">' .
                    $icon . block_exacomp_get_string('tab_competence_overview') . '</a>';

                if ($isTeacher || block_exacomp_get_cross_subjects_by_course($courseid, $USER->id)) {
                    // Cross subjects: always for teacher and for students if it there are cross subjects
                    $icon = '<img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/subjects.svg' . '" class="icon" alt="" />';
                    $this->content->items[] = '<a title="' . block_exacomp_get_string('tab_cross_subjects') . '" ' .
                        ' href="' . $CFG->wwwroot . '/blocks/exacomp/cross_subjects_overview.php?courseid=' . $courseid . '">' .
                        $icon . block_exacomp_get_string('tab_cross_subjects') . '</a>';
                }

                if (!$courseSettings->nostudents) {
                    //Kompetenzprofil
                    $icon = '<img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/competenceprofile.svg' . '" class="icon" alt="" />';
                    $this->content->items[] = '<a title="' . block_exacomp_get_string('tab_competence_profile') . '" ' .
                        ' href="' . $CFG->wwwroot . '/blocks/exacomp/competence_profile.php?courseid=' . $courseid . '">' .
                        $icon . block_exacomp_get_string('tab_competence_profile') . '</a>';
                }

                if (!$courseSettings->nostudents) {
                    //Beispiel-Aufgaben
                    $icon = '<img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/learningmaterials.svg' . '" class="icon" alt="" />';
                    $this->content->items[] = '<a title="' . block_exacomp_get_string('tab_examples') . '" ' .
                        ' href="' . $CFG->wwwroot . '/blocks/exacomp/view_examples.php?courseid=' . $courseid . '">' .
                        $icon . block_exacomp_get_string('tab_examples') . '</a>';

                    //Lernagenda
                    //$this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/learningagenda.php', array('courseid'=>$courseid)), block_exacomp_get_string('tab_learning_agenda'), array('title'=>block_exacomp_get_string('tab_learning_agenda')));
                    //$this->content->icons[] = html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/subject.png'), 'alt'=>"", 'height'=>16, 'width'=>23));
                }

                if (!$courseSettings->nostudents) {
                    //Wochenplan
                    $icon = '<img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/wochenplan.svg' . '" class="icon" alt="" />';
                    $this->content->items[] = '<a title="' . block_exacomp_get_string('tab_weekly_schedule') . '" ' .
                        ' href="' . $CFG->wwwroot . '/blocks/exacomp/weekly_schedule.php?courseid=' . $courseid . '">' .
                        $icon . block_exacomp_get_string('tab_weekly_schedule') . '</a>';
                }
                //Gruppenbericht
                $icon = '<img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/groupreports.svg' . '" class="icon" alt="" />';
                $this->content->items[] = '<a title="' . block_exacomp_get_string('reports') . '" ' .
                    ' href="' . $CFG->wwwroot . '/blocks/exacomp/group_reports.php?courseid=' . $courseid . '">' .
                    $icon . block_exacomp_get_string('reports') . '</a>';

                if ($isTeacher && !$courseSettings->nostudents) {
                    if ($courseSettings->useprofoundness) {
                        $icon = '<img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/basicextendedskills.svg' . '" class="icon" alt="" />';
                        $this->content->items[] = '<a title="' . block_exacomp_get_string('tab_profoundness') . '" ' .
                            ' href="' . $CFG->wwwroot . '/blocks/exacomp/profoundness.php?courseid=' . $courseid . '">' .
                            $icon . block_exacomp_get_string('tab_profoundness') . '</a>';
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
                $icon = '<img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/Course settings.svg' . '" class="icon" alt="" />';
                $this->content->items[] = '<a title="' . block_exacomp_get_string('tab_teacher_settings') . '" ' .
                    ' href="' . $CFG->wwwroot . '/blocks/exacomp/edit_course.php?courseid=' . $courseid . '">' .
                    $icon . block_exacomp_get_string('tab_teacher_settings') . '</a>';

                if (!$ready_for_use) {
                    if (!block_exacomp_is_disabled_create_grid()) {
                        $icon = '<img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/basicextendedskills.svg' . '" class="icon" alt="" />';
                        $this->content->items[] = '<a title="' . block_exacomp_get_string('tab_teacher_settings_new_subject') . '" ' .
                            ' href="' . $CFG->wwwroot . '/blocks/exacomp/subject.php?courseid=' . $courseid . '&embedded=false' . '">' .
                            $icon . block_exacomp_get_string('tab_teacher_settings_new_subject') . '</a>';
                    }
                }
                if (get_config('exacomp', 'external_trainer_assign')) {
                    $icon = '<img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/externaltrainer.svg' . '" class="icon" alt="" />';
                    $this->content->items[] = '<a title="' . block_exacomp_get_string('block_exacomp_external_trainer_assign') . '" ' .
                        ' href="' . $CFG->wwwroot . '/blocks/exacomp/externaltrainers.php?courseid=' . $courseid . '&sesskey=' . sesskey() . '">' .
                        $icon . block_exacomp_get_string('block_exacomp_external_trainer_assign') . '</a>';
                }
            }
            /*if ($de) {
                //Hilfe
                $this->content->items[] = html_writer::link(new moodle_url('/blocks/exacomp/help.php', array('courseid' => $courseid)), block_exacomp_get_string('tab_help'), array('title' => block_exacomp_get_string('tab_help')));
                $this->content->icons[] = html_writer::empty_tag('img', array('src' => new moodle_url('/blocks/exacomp/pix/info.png'), 'alt' => "", 'height' => 16, 'width' => 23));
            }*/
        } else {

            if ($isTeacher) {
                if (!$ready_for_use) {
                    if (!block_exacomp_is_disabled_create_grid()) {
                        $icon = '<img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/basicextendedskills.svg' . '" class="icon" alt="" />';
                        $this->content->items[] = '<a title="' . block_exacomp_get_string('tab_teacher_settings_new_subject') . '" ' .
                            ' href="' . $CFG->wwwroot . '/blocks/exacomp/subject.php?courseid=' . $courseid . '&embedded=false' . '">' .
                            $icon . block_exacomp_get_string('tab_teacher_settings_new_subject') . '</a>';
                    }
                }
            }
            if ($isTeacher && !has_capability('block/exacomp:admin', $globalcontext)) {
                $this->content->items[] = block_exacomp_get_string('admin_config_pending');
                //$this->content->icons[] = '';
            }
        }

        //if has_data && checkSubjects -> Modul wurde konfiguriert
        //else nur admin sieht block und hat nur den link Modulkonfiguration
        if (is_siteadmin() || (has_capability('block/exacomp:admin', $globalcontext) && !block_exacomp_is_skillsmanagement())) {
            //Admin sieht immer Modulkonfiguration
            //Wenn Import schon erledigt, weiterleitung zu edit_config, ansonsten import.
            if ($has_data) {
                $icon = '<img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/websitesettings.svg' . '" class="icon" alt="" />';
                $this->content->items[] = '<a title="' . block_exacomp_get_string('tab_admin_settings') . '" ' .
                    ' href="' . $CFG->wwwroot . '/blocks/exacomp/edit_config.php?courseid=' . $courseid . '">' .
                    $icon . block_exacomp_get_string('tab_admin_settings') . '</a>';
            }

            // always show import/export
            $icon = '<img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/importexport.svg' . '" class="icon" alt="" />';
            $this->content->items[] = '<a title="' . block_exacomp_get_string('tab_admin_import') . '" ' .
                ' href="' . $CFG->wwwroot . '/blocks/exacomp/import.php?courseid=' . $courseid . '">' .
                $icon . block_exacomp_get_string('tab_admin_import') . '</a>';
        }
        // link to dakora_url
        $dakoraUrl = trim(get_config('exacomp', 'dakora_url'));
        if ($dakoraUrl) {
            $icon = '<img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/dakora2.svg' . '" class="icon" alt="" />';

            $dakoraUrl = $CFG->wwwroot . '/blocks/exacomp/applogin.php?action=dakora_sso&sesskey=' . sesskey();
            $this->content->items[] = '<a target="_blank" title="' . block_exacomp_get_string('tab_admin_import') . '" ' .
                ' href="' . $dakoraUrl . '">' .
                $icon . block_exacomp_get_string('block_exacomp_link_to_dakora_app') . '</a>';
        }
        return $this->content;
    }

    public function instance_allow_multiple() {
        return false;
    }

    function has_config() {
        return true;
    }
}
