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

/**
 * Event observer for block_exacomp.
 */
class block_exacomp_observer
{

    /**
     * Observer for \core\event\course_created event.
     *
     * @param \core\event\course_created $event
     * @return void
     */
    public static function course_created(\core\event\course_created $event)
    {
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
     * @param \core\event\course_module_completion_updated $event
     * @return void
     */
    public static function course_module_completion_updated(\core\event\course_module_completion_updated $event)
    {
        global $CFG, $DB, $USER;
        // If this course does not use moodle activities all queries can just be skipped entirely. Also the global admin setting for using autotest must be set.
        if (get_config('exacomp', 'autotest') && block_exacomp_get_settings_by_course($event->courseid)->uses_activities) {
            $topics = array();
            $descriptors = array();
            $examples = array();

            // check if this activity is related or assigned to an exacomp activity. If not, nothing has to be done.
            // relating activities to exacomp competencies creates examples which have an activityid field
            // assigning activities to exacomp competencies creates entries in BLOCK_EXACOMP_DB_COMPETENCE_ACTIVITY

            // if the old method is active, there can be assigned topics and descriptors
            if(block_exacomp_use_old_activities_method()){
                // get all assigned topics and descriptors for this activity
                // contextinstanceid is the coursemoduleid
                $descriptors = $DB->get_records(BLOCK_EXACOMP_DB_COMPETENCE_ACTIVITY,array('activityid' => $event->contextinstanceid, 'comptype' => BLOCK_EXACOMP_TYPE_DESCRIPTOR), null, 'compid');
                $topics = $DB->get_records(BLOCK_EXACOMP_DB_COMPETENCE_ACTIVITY,array('activityid' => $event->contextinstanceid, 'comptype' => BLOCK_EXACOMP_TYPE_TOPIC), null, 'compid');


            }

            // the new method is always active: there can be examples with this activityid
            $examples = $DB->get_records(BLOCK_EXACOMP_DB_EXAMPLES,array('activityid' => $event->contextinstanceid, 'courseid' => $event->courseid), '', 'id');

            // now grade those topics, descriptors and examples
            // TODO: Problem: The userid is the id of the student that marked it as done... the student is not allowed to grade himself ---> do it as admin, like in cron?
            block_exacomp_assign_competences($event->courseid, $event->userid, $topics, $descriptors, $examples, null, null, null, BLOCK_EXACOMP_ROLE_SYSTEM);
            //block_exacomp_assign_competences($event->courseid, $event->userid, $topics, $descriptors, null, true, $maxGrade, $studentGradeResult); TODO: quizzes can have more specific grading

            echo "asdf";


            // For this activity: 1. check if the related example should be visible,
            // 2. check if the activity is completed and set the corresponding competence or example as gained (depending on assign/relate)
            // 3. NO NEED to update any timestamps in the autotestassign table, since it will never be checked anyways (this field is depreacted when not using tasks anymore but events instead)



        }
        return true;
    }

}
