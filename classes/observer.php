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

use core\event\course_created;
use core\event\course_module_completion_updated;
use core\event\course_module_updated;

defined('MOODLE_INTERNAL') || die();
require_once __DIR__ . '/../inc.php'; // otherwise the course_module_completion_updated does not have access to the exacomp functions in some cases

/**
 * Event observer for block_exacomp.
 */
class block_exacomp_observer {

    /**
     * Observer for \core\event\course_created event.
     *
     * @param course_created $event
     * @return void
     */
    public static function course_created(course_created $event) {
        global $CFG, $DB;

        $course = $event->get_record_snapshot('course', $event->objectid);
        $addto = get_config('exacomp', 'addblock_to_new_course');
        if ($addto) {
            // check main CFG from config.php - the moodle is able to create the block by self
            if ((isset($CFG->defaultblocks_override) && strpos($CFG->defaultblocks_override, 'exacomp') !== false)
                || (isset($CFG->defaultblocks) && strpos($CFG->defaultblocks, 'exacomp') !== false)
                || (isset($CFG->{'defaultblocks_' . $course->format}) && strpos($CFG->{'defaultblocks_' . $course->format}, 'exacomp') !== false)
            ) {
                return true;
            }
            // Check to see if this block is already on the default /my page.
            // another checking
            //$page = new moodle_page();
            //$page->set_context(context_system::instance());
            /*$criteria = array(
                    'blockname' => 'exacomp',
                    'parentcontextid' => $page->context->id,
                    'pagetypepattern' => 'my-index',
                    'subpagepattern' => $systempage->id,
            );*/

            //if (!$DB->record_exists('block_instances', $criteria)) {
            // Add the block to the default /my.

            $page = new moodle_page();
            $page->set_context(context_course::instance($course->id));
            $page->blocks->add_region($addto, false);
            $page->blocks->add_block('exacomp', $addto, 0, false, 'course-view-*', null);
            //}
        }
    }

    /**
     * Observer for \core\event\course_module_completion_updated event.
     *
     * @param course_module_completion_updated $event
     * @return void
     */
    public static function course_module_completion_updated(course_module_completion_updated $event) {
        global $CFG, $DB, $USER;

        if (block_exacomp_is_teacher($event->courseid, $USER->id)) {
            $admingrading = false;
        } else {
            $admingrading = true; // if the student triggers this event, the grading should be done by the admin
        }

        // If this course does not use moodle activities all queries can just be skipped entirely. Also the global admin setting for using autotest must be set.
        if (get_config('exacomp', 'autotest') && block_exacomp_get_settings_by_course($event->courseid)->uses_activities) {
            $topics = array();
            $descriptors = array();
            $examples = array();

            // check if this activity is related or assigned to an exacomp activity. If not, nothing has to be done.
            // relating activities to exacomp competencies creates examples which have an activityid field
            // assigning activities to exacomp competencies creates entries in BLOCK_EXACOMP_DB_COMPETENCE_ACTIVITY

            // if the old method is active, there can be assigned topics and descriptors
            if (block_exacomp_use_old_activities_method()) {
                // get all assigned topics and descriptors for this activity
                // contextinstanceid is the coursemoduleid
                $descriptors = $DB->get_records(BLOCK_EXACOMP_DB_COMPETENCE_ACTIVITY, array('activityid' => $event->contextinstanceid, 'comptype' => BLOCK_EXACOMP_TYPE_DESCRIPTOR), null, 'compid');
                $topics = $DB->get_records(BLOCK_EXACOMP_DB_COMPETENCE_ACTIVITY, array('activityid' => $event->contextinstanceid, 'comptype' => BLOCK_EXACOMP_TYPE_TOPIC), null, 'compid');
            }
            // the new method is always active: there can be examples with this activityid
            $examples = $DB->get_records(BLOCK_EXACOMP_DB_EXAMPLES, array('activityid' => $event->contextinstanceid, 'courseid' => $event->courseid), '', 'id');

            // now grade those topics, descriptors and examples
            $userealvalue = false;
            $maxgrade = null;
            $studentgraderesult = null;
            // get completion info for the activity
            $activity_completion = $event->get_record_snapshot('course_modules_completion', $event->objectid);
            // get the module, then check if it is a quiz. If it is a quiz, get the quiz-grading, if not, just grade with max value.
            $quiz_module = $DB->get_record('course_modules', array('id' => $activity_completion->coursemoduleid, 'module' => 17)); // 17 is the id of the module "quiz"
            if ($quiz_module) {
                $quiz = $DB->get_record('quiz', array('id' => $quiz_module->instance));
                $quiz_grade = $DB->get_record('quiz_grades', array('quiz' => $quiz->id, 'userid' => $event->relateduserid));
                if ($quiz_grade) {
                    $userealvalue = true;
                    $maxgrade = $quiz->grade;
                    $studentgraderesult = $quiz_grade->grade;
                }
            }
            // TODO if needed some day: the same can be done for anything else with a grade... e.g. assignments can have grades ==> get assignment, get the grade, set maxgrad and studengraderesult and userealvalue
            if ($activity_completion && ($activity_completion->completionstate == COMPLETION_COMPLETE || $activity_completion->completionstate == COMPLETION_COMPLETE_PASS)) {
                block_exacomp_assign_competences($event->courseid, $event->relateduserid, $topics, $descriptors, $examples, $userealvalue, $maxgrade, $studentgraderesult, $admingrading);
                // $event->relateduserid is the id of the student that is graded. $event->userid is the id of the user that triggered the event
            }
            block_exacomp_update_related_examples_visibilities_for_single_student($event->courseid, $event->relateduserid); // update the visibilities
            // The visibilities are instantly updated if a user e.g. solves a series of assignments that depend on each other.
            // If the dependency is per date, then the visibilities have to be updated at other places, e.g. when loading examples or in a task for the cronjob
        }
        return true;
    }

    //    /**
    //     * Observer for \core\event\course_module_created event.
    //     *
    //     * @param \core\event\course_module_created $event
    //     * @return void
    //     */
    //    public static function course_module_created(\core\event\course_module_created $event)
    //    {
    //        $students = \block_exacomp\permissions::get_course_students($event->courseid);
    //        $activities = block_exacomp_get_all_associated_activities_by_course($event->courseid);
    //        foreach($students as $student){
    //            block_exacomp_update_related_examples_visibilities($activities, $event->courseid, $student->id);
    //        }
    //        return true;
    //    }
    // This observer is not needed, since when creating an activity it cannot yet be related or assigned to any exacomp competence

    /**
     * Observer for \core\event\course_module_updated event.
     *
     * @param course_module_updated $event
     * @return void
     */
    public static function course_module_updated(course_module_updated $event) {
        global $DB;
        $event->other['name']; // gives the "name" field of e.g. assign or quiz table entry
        block_exacomp_check_relatedactivitydata($event->objectid, $event->other['name']);
        // this was done in block_exacomp_coursemodule_edit_post_actions() and  block_exacomp_override_webservice_execution before

        $students = \block_exacomp\permissions::get_course_students($event->courseid);
        $activity = $event->get_record_snapshot('course_modules', $event->objectid);
        $activity->activityid = $activity->id;
        // get the examples.. the assigned descriptors and topics do not matter for the visibility update
        $activity->examples = $DB->get_records(BLOCK_EXACOMP_DB_EXAMPLES, array('activityid' => $activity->id, 'courseid' => $event->courseid), '', 'id');
        foreach ($students as $student) {
            block_exacomp_update_related_examples_visibilities(array($activity), $event->courseid, $student->id);
        }
        return true;
    }

    /**
     * Observer for \core\event\course_module_deleted event.
     *
     * @param \core\event\course_module_deleted $event
     * @return void
     */
    public static function course_module_deleted(\core\event\course_module_deleted $event) {
        // This is often not triggered instantly, but for example in the next cron.
        block_exacomp_checkfordelete_relatedactivity($event->objectid);
        return true;
    }
    // this is has been done in block_exacomp_pre_course_module_delete() before.


    //
    /**
     * Observer for \mod_quiz\event\attempt_submitted event.
     *
     * this can be used to update the competences of a student after a quiz has been submitted
     * it captures the automatically graded questions
     * if the question-descriptor relation is done AFTER the quiz has been submitted, the grades are not recalculated.
     *
     * @param \mod_quiz\event\attempt_submitted $event
     * @return void
     */
    public static function attempt_submitted(\mod_quiz\event\attempt_submitted $event) {
        global $DB, $USER;
        if (block_exacomp_has_topics_assigned($event->courseid)) {
            if (block_exacomp_is_teacher($event->courseid, $USER->id)) {
                $admingrading = false;
            } else {
                $admingrading = true; // if the student triggers this event, the grading should be done by the admin
            }

            // Get the quiz attempt ID from the event data.
            $attemptid = $event->objectid;

            // Fetch the quiz attempt record to get the 'uniqueid'.
            $quizattempt = $DB->get_record('quiz_attempts', array('id' => $attemptid), 'uniqueid, userid');

            if (!$quizattempt) {
                // If the quiz attempt is not found, return early.
                return;
            }

            // The uniqueid corresponds to the question usage id in question_attempts.
            $questionusageid = $quizattempt->uniqueid;

            // Fetch all question attempts related to this quiz attempt using the uniqueid.
            // $questionattempts = $DB->get_records('question_attempts', array('questionusageid' => $questionusageid));

            $sql = "SELECT attempts.id, attempts.questionid, attempts.maxmark, step.fraction, attempts.timemodified as timemodified
            FROM {question_attempts} attempts
            JOIN {question_attempt_steps} AS step ON attempts.id=step.questionattemptid
            WHERE (step.state = 'gradedright' OR step.state = 'gradedwrong')
            AND questionusageid = :questionusageid";
            $questionattempts = $DB->get_records_sql($sql, array('questionusageid' => $questionusageid));


            // Loop through each question attempt.
            foreach ($questionattempts as $questionattempt) {
                $questionid = $questionattempt->questionid;

                // For each question, fetch related descriptors from block_exacompdescrquest_mm.
                $descriptors = $DB->get_records('block_exacompdescrquest_mm', array('questid' => $questionid));

                // Process the descriptors as needed for grading or other purposes.
                foreach ($descriptors as $descriptor) {
                    $grading_scheme = block_exacomp_get_assessment_comp_scheme($descriptor->courseid);

                    if (block_exacomp_additional_grading(BLOCK_EXACOMP_TYPE_DESCRIPTOR, $descriptor->courseid)) {
                        block_exacomp_save_additional_grading_for_comp($descriptor->courseid, $descriptor->descrid, $quizattempt->userid,
                            block_exacomp_get_assessment_max_good_value($grading_scheme, true, $questionattempt->maxmark, $questionattempt->fraction, $descriptor->courseid), $comptype = BLOCK_EXACOMP_TYPE_DESCRIPTOR, -1, $admingrading);
                    }

                    block_exacomp_set_user_competence($quizattempt->userid, $descriptor->descrid, BLOCK_EXACOMP_TYPE_DESCRIPTOR, $descriptor->courseid, BLOCK_EXACOMP_ROLE_TEACHER,
                        block_exacomp_get_assessment_max_good_value($grading_scheme, true, $questionattempt->maxmark, $questionattempt->fraction, $descriptor->courseid), null, -1, true, [], $admingrading);
                    $descriptor->timemodified = $questionattempt->timemodified;
                    $DB->update_record("block_exacompdescrquest_mm", $descriptor);
                }
            }
        }
        return true;
    }

    // TODO: manually graded questions did not work with the cron, were not needed. This code below does also not work yet, but almost. ==> Finish if needed
    // // this can be used to update the competences of a student after a quiz has been submitted
    // // it captures the manually graded questions.
    // public static function question_manually_graded(\mod_quiz\event\question_manually_graded $event) {
    //     global $DB, $USER;
    //
    //     if (block_exacomp_is_teacher($event->courseid, $USER->id)) {
    //         $admingrading = false;
    //     } else {
    //         $admingrading = true; // if the student triggers this event, the grading should be done by the admin
    //     }
    //
    //     // Get the quiz attempt ID from the event data.
    //     $attemptid = $event->get_data()["other"]["attemptid"];
    //
    //     // Fetch the quiz attempt record to get the 'uniqueid'.
    //     $quizattempt = $DB->get_record('quiz_attempts', array('id' => $attemptid), 'uniqueid, userid');
    //
    //     // the teacher could grade an old attempt AFTER the newest attempt has been graded ==> get the newest attempt
    //     $sql = "SELECT *
    //     FROM {quiz_attempts}
    //     WHERE userid = :userid
    //     AND quiz = :quizid
    //     ORDER BY attempt DESC
    //     LIMIT 1";
    //     $params = array('userid' => $quizattempt->userid, 'quizid' => $event->get_data()["other"]["quizid"]);
    //     $quizattempt = $DB->get_record_sql($sql, $params);
    //
    //     if (!$quizattempt) {
    //         // If the quiz attempt is not found, return early.
    //         return;
    //     }
    //
    //     // The uniqueid corresponds to the question usage id in question_attempts.
    //     $questionusageid = $quizattempt->uniqueid;
    //
    //     // Fetch all question attempts related to this quiz attempt using the uniqueid.
    //     // $questionattempts = $DB->get_records('question_attempts', array('questionusageid' => $questionusageid));
    //
    //     // get the attempt and step information with the join, but only for this specific question
    //     $sql = "SELECT attempts.id, attempts.questionid, attempts.maxmark, step.fraction, attempts.timemodified as timemodified
    //         FROM {question_attempts} attempts
    //         JOIN {question_attempt_steps} AS step ON attempts.id=step.questionattemptid
    //         WHERE (step.state = 'mangrright' OR step.state = 'mangrwrong')
    //         AND questionusageid = :questionusageid // TODO: this does not work. The questionusageid is likely not the way to find the steps information
    //         AND attempts.questionid = :questionid";
    //     $questionattempts = $DB->get_records_sql($sql, array('questionusageid' => $questionusageid, 'questionid' => $event->get_data()["objectid"]));
    //
    //     // Loop through each question attempt... this will only be ONE question attempt, since the event is triggered for one question only.
    //     foreach ($questionattempts as $questionattempt) {
    //         $questionid = $questionattempt->questionid;
    //
    //         // For each question, fetch related descriptors from block_exacompdescrquest_mm.
    //         $descriptors = $DB->get_records('block_exacompdescrquest_mm', array('questid' => $questionid));
    //
    //         // Process the descriptors as needed for grading or other purposes.
    //         foreach ($descriptors as $descriptor) {
    //             $grading_scheme = block_exacomp_get_assessment_comp_scheme($descriptor->courseid);
    //
    //             if (block_exacomp_additional_grading(BLOCK_EXACOMP_TYPE_DESCRIPTOR, $descriptor->courseid)) {
    //                 block_exacomp_save_additional_grading_for_comp($descriptor->courseid, $descriptor->descrid, $quizattempt->userid,
    //                     block_exacomp_get_assessment_max_good_value($grading_scheme, true, $questionattempt->maxmark, $questionattempt->fraction, $descriptor->courseid), $comptype = BLOCK_EXACOMP_TYPE_DESCRIPTOR, -1, $admingrading);
    //             }
    //
    //             block_exacomp_set_user_competence($quizattempt->userid, $descriptor->descrid, BLOCK_EXACOMP_TYPE_DESCRIPTOR, $descriptor->courseid, BLOCK_EXACOMP_ROLE_TEACHER,
    //                 block_exacomp_get_assessment_max_good_value($grading_scheme, true, $questionattempt->maxmark, $questionattempt->fraction, $descriptor->courseid),null, -1, true, [], $admingrading);
    //             $descriptor->timemodified = $questionattempt->timemodified;
    //             $DB->update_record("block_exacompdescrquest_mm", $descriptor);
    //         }
    //     }
    //     return true;
    // }


}
