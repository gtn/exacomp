<?php
// This file is part of Exabis Competence Grid
//
// (c) 2019 GTN - Global Training Network GmbH <office@gtn-solutions.com>
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

namespace block_exacomp\privacy;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\approved_userlist;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy Subsystem for block_exacomp implementing null_provider.
 */
class provider implements
        \core_privacy\local\metadata\provider,
        \core_privacy\local\request\core_userlist_provider,
        \core_privacy\local\request\plugin\provider {

    public static function get_metadata(collection $collection) : collection {
        // block_exacompcompuser
        $collection->add_database_table('block_exacompcompuser', [
                'userid' => 'privacy:metadata:block_exacompcompuser:userid',
                'compid' => 'privacy:metadata:block_exacompcompuser:compid',
                'reviewerid' => 'privacy:metadata:block_exacompcompuser:reviewerid',
                'role' => 'privacy:metadata:block_exacompcompuser:role',
                'courseid' => 'privacy:metadata:block_exacompcompuser:courseid',
                'value' => 'privacy:metadata:block_exacompcompuser:value',
                'comptype' => 'privacy:metadata:block_exacompcompuser:comptype',
                'timestamp' => 'privacy:metadata:block_exacompcompuser:timestamp',
                'additionalinfo' => 'privacy:metadata:block_exacompcompuser:additionalinfo',
                'evalniveauid' => 'privacy:metadata:block_exacompcompuser:evalniveauid',
                'gradingisold ' => 'privacy:metadata:block_exacompcompuser:gradingisold',
        ], 'privacy:metadata:block_exacompcompuser');

        // block_exacompexameval
        $collection->add_database_table('block_exacompexameval', [
                'exampleid' => 'privacy:metadata:block_exacompexameval:exampleid',
                'courseid' => 'privacy:metadata:block_exacompexameval:courseid',
                'studentid' => 'privacy:metadata:block_exacompexameval:studentid',
                'teacher_evaluation' => 'privacy:metadata:block_exacompexameval:teacher_evaluation',
                'additionalinfo' => 'privacy:metadata:block_exacompexameval:additionalinfo',
                'teacher_reviewerid' => 'privacy:metadata:block_exacompexameval:teacher_reviewerid',
                'timestamp_teacher' => 'privacy:metadata:block_exacompexameval:timestamp_teacher',
                'student_evaluation' => 'privacy:metadata:block_exacompexameval:student_evaluation',
                'timestamp_student' => 'privacy:metadata:block_exacompexameval:timestamp_student',
                'evalniveauid' => 'privacy:metadata:block_exacompexameval:evalniveauid',
        ], 'privacy:metadata:block_exacompexameval');

        // block_exacompcmassign
        $collection->add_database_table('block_exacompcmassign', [
                'coursemoduleid' => 'privacy:metadata:block_exacompcmassign:coursemoduleid',
                'userid' => 'privacy:metadata:block_exacompcmassign:userid',
                'timemodified' => 'privacy:metadata:block_exacompcmassign:timemodified',
                'relateddata' => 'privacy:metadata:block_exacompcmassign:relateddata',
        ], 'privacy:metadata:block_exacompcmassign');

        // block_exacompcompuser_mm
        // TODO: any adding to this table. Only reading. Is it used yet?

        // block_exacompcrossstud_mm
        $collection->add_database_table('block_exacompcrossstud_mm', [
                'crosssubjid' => 'privacy:metadata:block_exacompcrossstud_mm:crosssubjid',
                'studentid' => 'privacy:metadata:block_exacompcrossstud_mm:studentid',
        ], 'privacy:metadata:block_exacompcrossstud_mm');

        // block_exacompdescrvisibility
        $collection->add_database_table('block_exacompdescrvisibility', [
                'courseid' => 'privacy:metadata:block_exacompdescrvisibility:courseid',
                'descrid' => 'privacy:metadata:block_exacompdescrvisibility:descrid',
                'studentid' => 'privacy:metadata:block_exacompdescrvisibility:studentid',
                'visible' => 'privacy:metadata:block_exacompdescrvisibility:visible',
        ], 'privacy:metadata:block_exacompdescrvisibility');

        // block_exacompexampvisibility
        $collection->add_database_table('block_exacompexampvisibility', [
                'courseid' => 'privacy:metadata:block_exacompexampvisibility:courseid',
                'exampleid' => 'privacy:metadata:block_exacompexampvisibility:exampleid',
                'studentid' => 'privacy:metadata:block_exacompexampvisibility:studentid',
                'visible' => 'privacy:metadata:block_exacompexampvisibility:visible',
        ], 'privacy:metadata:block_exacompexampvisibility');

        // block_exacompexternaltrainer
        $collection->add_database_table('block_exacompexternaltrainer', [
                'trainerid' => 'privacy:metadata:block_exacompexternaltrainer:trainerid',
                'studentid' => 'privacy:metadata:block_exacompexternaltrainer:studentid',
        ], 'privacy:metadata:block_exacompexternaltrainer');

        // block_exacompprofilesettings
        // now only for courses. But in the future is possible for other things
        $collection->add_database_table('block_exacompprofilesettings', [
                'itemid' => 'privacy:metadata:block_exacompprofilesettings:itemid',
                'userid' => 'privacy:metadata:block_exacompprofilesettings:userid',
        ], 'privacy:metadata:block_exacompprofilesettings');

        // block_exacompschedule
        $collection->add_database_table('block_exacompschedule', [
                'studentid' => 'privacy:metadata:block_exacompschedule:studentid',
                'exampleid' => 'privacy:metadata:block_exacompschedule:exampleid',
                'creatorid' => 'privacy:metadata:block_exacompschedule:creatorid',
                'timecreated' => 'privacy:metadata:block_exacompschedule:timecreated',
                'timemodified' => 'privacy:metadata:block_exacompschedule:timemodified',
                'courseid' => 'privacy:metadata:block_exacompschedule:courseid',
                'sorting' => 'privacy:metadata:block_exacompschedule:sorting',
                'start' => 'privacy:metadata:block_exacompschedule:start',
                'end' => 'privacy:metadata:block_exacompschedule:end',
                'deleted' => 'privacy:metadata:block_exacompschedule:deleted',
        ], 'privacy:metadata:block_exacompschedule');

        // block_exacompsolutvisibility
        $collection->add_database_table('block_exacompsolutvisibility', [
                'courseid' => 'privacy:metadata:block_exacompsolutvisibility:courseid',
                'exampleid' => 'privacy:metadata:block_exacompsolutvisibility:exampleid',
                'studentid' => 'privacy:metadata:block_exacompsolutvisibility:studentid',
                'visible' => 'privacy:metadata:block_exacompsolutvisibility:visible',
        ], 'privacy:metadata:block_exacompsolutvisibility');

        // block_exacomptopicvisibility
        $collection->add_database_table('block_exacomptopicvisibility', [
                'courseid' => 'privacy:metadata:block_exacomptopicvisibility:courseid',
                'topicid' => 'privacy:metadata:block_exacomptopicvisibility:topicid',
                'studentid' => 'privacy:metadata:block_exacomptopicvisibility:studentid',
                'visible' => 'privacy:metadata:block_exacomptopicvisibility:visible',
        ], 'privacy:metadata:block_exacomptopicvisibility');

        // block_exacompwsdata
        $collection->add_database_table('block_exacompwsdata', [
                'token' => 'privacy:metadata:block_exacompwsdata:token',
                'userid' => 'privacy:metadata:block_exacompwsdata:userid',
                'data' => 'privacy:metadata:block_exacompwsdata:data',
        ], 'privacy:metadata:block_exacompwsdata');

        return $collection;
    }

    public static function get_contexts_for_userid(int $userid): \core_privacy\local\request\contextlist {
        $contextlist = new \core_privacy\local\request\contextlist();

        $contextlist->add_user_context($userid);
        
        $sql = 'SELECT c.id
                    FROM {context} c
                        INNER JOIN {block_instances} bi ON bi.blockname = ? AND bi.parentcontextid = c.id AND c.contextlevel = ?               
                        INNER JOIN {block_exacompcompuser} ccu ON ccu.courseid = c.instanceid                        
                    WHERE ccu.userid = ? 
                          OR ccu.reviewerid = ?
        ';

        $params = ['exacomp', CONTEXT_COURSE, $userid, $userid];

        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Get the list of users within a specific context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        global $DB;
        $context = $userlist->get_context();
        if (get_class($context) != 'context_course') {
            return;
        }
        $courseid = $context->instanceid;
        if ($courseid) {
            $params = ['courseid' => $courseid];

            $sql = "SELECT userid as userid FROM {block_exacompcompuser} WHERE courseid = :courseid "; // for students
            $userlist->add_from_sql('userid', $sql, $params);
            $sql = "SELECT reviewerid as userid FROM {block_exacompcompuser} WHERE courseid = :courseid "; // for reviewers
            $userlist->add_from_sql('userid', $sql, $params);
            $sql = "SELECT studentid as userid FROM {block_exacompdescrvisibility} WHERE courseid = :courseid ";
            $userlist->add_from_sql('userid', $sql, $params);
            $sql = "SELECT studentid as userid FROM {block_exacompexameval} WHERE courseid = :courseid "; // for students
            $userlist->add_from_sql('userid', $sql, $params);
            $sql = "SELECT teacher_reviewerid as userid FROM {block_exacompexameval} WHERE courseid = :courseid "; // for reviewers
            $userlist->add_from_sql('userid', $sql, $params);
            $sql = "SELECT studentid as userid FROM {block_exacompexampvisibility} WHERE courseid = :courseid ";
            $userlist->add_from_sql('userid', $sql, $params);
        }
    }

    public static function export_user_data(\core_privacy\local\request\approved_contextlist $contextlist) {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/blocks/exacomp/lib/lib.php');
        require_once($CFG->dirroot . '/blocks/exacomp/lib/classes.php');
        if (empty($contextlist->count())) {
            //return;
        }
        $user = $contextlist->get_user();

        // got only context_cources
        $exacompcoursescontexts = $contextlist->get_contexts();
        foreach ($exacompcoursescontexts as $k => $context) {
            if (get_class($context) != 'context_course') {
                unset($exacompcoursescontexts[$k]);
            }
        }

        // block_exacompcompuser
        // block_exacompexameval
        // get user's grades (reviews from teachers)
        foreach ($exacompcoursescontexts as $context) {
            $courseid = $context->instanceid;
            $grades = array();
            $tree = block_exacomp_get_competence_tree($courseid);
            //echo "<pre>debug:<strong>provider.php:92</strong>\r\n"; print_r($tree); echo '</pre>'; // !!!!!!!!!! delete it
            foreach ($tree as $subject) {
                if (!array_key_exists($subject->id, $grades)) {
                    $grades[$subject->id] = array();
                }
                $grades[$subject->id]['title'] = $subject->title;
                $grades[$subject->id]['titleshort'] = $subject->titleshort;
                $grades[$subject->id]['infolink'] = $subject->infolink;
                $grades[$subject->id]['description'] = $subject->description;
                $grades[$subject->id]['author'] = $subject->author;
                $assessment = block_exacomp_get_user_assesment_wordings($user->id, $subject->id, BLOCK_EXACOMP_TYPE_SUBJECT, $courseid);
                $grades[$subject->id]['assessment_grade'] = $assessment->grade;
                $grades[$subject->id]['assessment_niveau'] = $assessment->niveau;
                $grades[$subject->id]['assessment_selfgrade'] = $assessment->self_grade;
                $grades[$subject->id]['topics'] = array();
                foreach ($subject->topics as $topic) {
                    if (!array_key_exists($topic->id, $grades[$subject->id]['topics'])) {
                        $grades[$subject->id]['topics'][$topic->id] = array();
                    }
                    $grades[$subject->id]['topics'][$topic->id]['title'] = $topic->title;
                    $grades[$subject->id]['topics'][$topic->id]['description'] = $topic->description;
                    $assessment = block_exacomp_get_user_assesment_wordings($user->id, $topic->id, BLOCK_EXACOMP_TYPE_TOPIC, $courseid);
                    $grades[$subject->id]['topics'][$topic->id]['assessment_grade'] = $assessment->grade;
                    $grades[$subject->id]['topics'][$topic->id]['assessment_niveau'] = $assessment->niveau;
                    $grades[$subject->id]['topics'][$topic->id]['assessment_selfgrade'] = $assessment->self_grade;
                    $grades[$subject->id]['topics'][$topic->id]['descriptors'] = array();
                    foreach ($topic->descriptors as $descriptor) {
                        $assessment = block_exacomp_get_user_assesment_wordings($user->id, $descriptor->id, BLOCK_EXACOMP_TYPE_DESCRIPTOR, $courseid);
                        if (!array_key_exists($descriptor->id, $grades[$subject->id]['topics'][$topic->id]['descriptors'])) {
                            $grades[$subject->id]['topics'][$topic->id]['descriptors'][$descriptor->id] = array();
                        }
                        $grades[$subject->id]['topics'][$topic->id]['descriptors'][$descriptor->id]['title'] = $descriptor->title;
                        $grades[$subject->id]['topics'][$topic->id]['descriptors'][$descriptor->id]['niveautitle'] = $descriptor->niveau_title;
                        $grades[$subject->id]['topics'][$topic->id]['descriptors'][$descriptor->id]['assessment_grade'] = $assessment->grade;
                        $grades[$subject->id]['topics'][$topic->id]['descriptors'][$descriptor->id]['assessment_niveau'] = $assessment->niveau;
                        $grades[$subject->id]['topics'][$topic->id]['descriptors'][$descriptor->id]['assessment_selfgrade'] = $assessment->self_grade;
                        $grades[$subject->id]['topics'][$topic->id]['descriptors'][$descriptor->id]['examples'] = array();
                        foreach ($descriptor->examples as $example) {
                            $assessment = block_exacomp_get_user_assesment_wordings($user->id, $example->id, BLOCK_EXACOMP_TYPE_EXAMPLE, $courseid);
                            if ($assessment) {
                                if (!array_key_exists($example->id,
                                        $grades[$subject->id]['topics'][$topic->id]['descriptors'][$descriptor->id]['examples'])) {
                                    $grades[$subject->id]['topics'][$topic->id]['descriptors'][$descriptor->id]['examples'][$example->id] =
                                            array();
                                }
                                $grades[$subject->id]['topics'][$topic->id]['descriptors'][$descriptor->id]['examples'][$example->id]['assessment_grade'] =
                                        $assessment->grade;
                                $grades[$subject->id]['topics'][$topic->id]['descriptors'][$descriptor->id]['examples'][$example->id]['assessment_niveau'] =
                                        $assessment->niveau;
                                $grades[$subject->id]['topics'][$topic->id]['descriptors'][$descriptor->id]['examples'][$example->id]['assessment_selfgrade'] =
                                        $assessment->self_grade;
                                $grades[$subject->id]['topics'][$topic->id]['descriptors'][$descriptor->id]['examples'][$example->id]['title'] =
                                        $example->title;
                                $grades[$subject->id]['topics'][$topic->id]['descriptors'][$descriptor->id]['examples'][$example->id]['description'] =
                                        $example->description;
                                $grades[$subject->id]['topics'][$topic->id]['descriptors'][$descriptor->id]['examples'][$example->id]['externalurl'] =
                                        $example->externalurl;
                                $grades[$subject->id]['topics'][$topic->id]['descriptors'][$descriptor->id]['examples'][$example->id]['externalsolution'] =
                                        $example->externalsolution;
                                $grades[$subject->id]['topics'][$topic->id]['descriptors'][$descriptor->id]['examples'][$example->id]['externaltask'] =
                                        $example->externaltask;
                                $grades[$subject->id]['topics'][$topic->id]['descriptors'][$descriptor->id]['examples'][$example->id]['author'] =
                                        $example->author;

                                $grades[$subject->id]['topics'][$topic->id]['descriptors'][$descriptor->id]['examples'][$example->id] =
                                        array_filter($grades[$subject->id]['topics'][$topic->id]['descriptors'][$descriptor->id]['examples'][$example->id]);
                            }
                        }
                        // TODO: subdescriptors?
                        $grades[$subject->id]['topics'][$topic->id]['descriptors'][$descriptor->id] = array_filter($grades[$subject->id]['topics'][$topic->id]['descriptors'][$descriptor->id]);
                    }
                    $grades[$subject->id]['topics'][$topic->id] = array_filter($grades[$subject->id]['topics'][$topic->id]);
                }
                $grades[$subject->id] = array_filter($grades[$subject->id]);
            }
            if (count($grades)) {
                $grades = array('competences_overview' => $grades);
                //$context = \context_course::instance($courseid);
                $contextdata = \core_privacy\local\request\helper::get_context_data($context, $user);
                $contextdata = (object) array_merge((array) $contextdata, $grades);
                $writer = \core_privacy\local\request\writer::with_context($context);
                $writer->export_data(['Exacomp/gradings'], $contextdata);
            }
        }

        // get user's grades (reviews AS a teacher)
        // does not kept real data of reviewd student. Only values. Is it correct?
        foreach ($exacompcoursescontexts as $context) {
            $courseid = $context->instanceid;
            $grades = array();
            $tree = block_exacomp_get_competence_tree($courseid);
            //echo "<pre>debug:<strong>provider.php:92</strong>\r\n"; print_r($tree); echo '</pre>'; // !!!!!!!!!! delete it
            foreach ($tree as $subject) {
                if (!array_key_exists($subject->id, $grades)) {
                    $grades[$subject->id] = array();
                }
                $grades[$subject->id]['title'] = $subject->title;
                $grades[$subject->id]['titleshort'] = $subject->titleshort;
                $grades[$subject->id]['infolink'] = $subject->infolink;
                $grades[$subject->id]['description'] = $subject->description;
                $grades[$subject->id]['author'] = $subject->author;
                $assessments = block_exacomp_get_teacher_assesment_wordings_array($user->id, $subject->id, BLOCK_EXACOMP_TYPE_SUBJECT, $courseid);
                $grades[$subject->id]['my_assessments'] = $assessments;
                $grades[$subject->id]['topics'] = array();
                foreach ($subject->topics as $topic) {
                    if (!array_key_exists($topic->id, $grades[$subject->id]['topics'])) {
                        $grades[$subject->id]['topics'][$topic->id] = array();
                    }
                    $grades[$subject->id]['topics'][$topic->id]['title'] = $topic->title;
                    $grades[$subject->id]['topics'][$topic->id]['description'] = $topic->description;
                    $assessments = block_exacomp_get_teacher_assesment_wordings_array($user->id, $topic->id, BLOCK_EXACOMP_TYPE_TOPIC, $courseid);
                    $grades[$subject->id]['topics'][$topic->id]['my_assessments'] = $assessments;
                    $grades[$subject->id]['topics'][$topic->id]['descriptors'] = array();
                    foreach ($topic->descriptors as $descriptor) {
                        $assessments = block_exacomp_get_teacher_assesment_wordings_array($user->id, $descriptor->id, BLOCK_EXACOMP_TYPE_DESCRIPTOR, $courseid);
                        if (!array_key_exists($descriptor->id, $grades[$subject->id]['topics'][$topic->id]['descriptors'])) {
                            $grades[$subject->id]['topics'][$topic->id]['descriptors'][$descriptor->id] = array();
                        }
                        $grades[$subject->id]['topics'][$topic->id]['descriptors'][$descriptor->id]['title'] = $descriptor->title;
                        $grades[$subject->id]['topics'][$topic->id]['descriptors'][$descriptor->id]['niveautitle'] = $descriptor->niveau_title;
                        $grades[$subject->id]['topics'][$topic->id]['descriptors'][$descriptor->id]['my_assessment'] = $assessments;
                        $grades[$subject->id]['topics'][$topic->id]['descriptors'][$descriptor->id]['examples'] = array();
                        foreach ($descriptor->examples as $example) {
                            $assessments = block_exacomp_get_teacher_assesment_wordings_array($user->id, $example->id, BLOCK_EXACOMP_TYPE_EXAMPLE, $courseid);
                            if ($assessments) {
                                if (!array_key_exists($example->id,
                                        $grades[$subject->id]['topics'][$topic->id]['descriptors'][$descriptor->id]['examples'])) {
                                    $grades[$subject->id]['topics'][$topic->id]['descriptors'][$descriptor->id]['examples'][$example->id] =
                                            array();
                                }
                                $grades[$subject->id]['topics'][$topic->id]['descriptors'][$descriptor->id]['examples'][$example->id]['my_assessment'] = $assessments;
                                $grades[$subject->id]['topics'][$topic->id]['descriptors'][$descriptor->id]['examples'][$example->id]['title'] = $example->title;
                                $grades[$subject->id]['topics'][$topic->id]['descriptors'][$descriptor->id]['examples'][$example->id]['description'] = $example->description;
                                $grades[$subject->id]['topics'][$topic->id]['descriptors'][$descriptor->id]['examples'][$example->id]['externalurl'] = $example->externalurl;
                                $grades[$subject->id]['topics'][$topic->id]['descriptors'][$descriptor->id]['examples'][$example->id]['externalsolution'] = $example->externalsolution;
                                $grades[$subject->id]['topics'][$topic->id]['descriptors'][$descriptor->id]['examples'][$example->id]['externaltask'] = $example->externaltask;
                                $grades[$subject->id]['topics'][$topic->id]['descriptors'][$descriptor->id]['examples'][$example->id]['author'] = $example->author;

                                $grades[$subject->id]['topics'][$topic->id]['descriptors'][$descriptor->id]['examples'][$example->id] =
                                        array_filter($grades[$subject->id]['topics'][$topic->id]['descriptors'][$descriptor->id]['examples'][$example->id]);
                            }
                        }
                        // TODO: subdescriptors?
                        $grades[$subject->id]['topics'][$topic->id]['descriptors'][$descriptor->id] = array_filter($grades[$subject->id]['topics'][$topic->id]['descriptors'][$descriptor->id]);
                    }
                    $grades[$subject->id]['topics'][$topic->id] = array_filter($grades[$subject->id]['topics'][$topic->id]);
                }
                $grades[$subject->id] = array_filter($grades[$subject->id]);
            }
            if (count($grades)) {
                $grades = array('competences_reviews' => $grades);
                //$context = \context_course::instance($courseid);
                $contextdata = \core_privacy\local\request\helper::get_context_data($context, $user);
                $contextdata = (object) array_merge((array) $contextdata, $grades);
                $writer = \core_privacy\local\request\writer::with_context($context);
                $writer->export_data(['Exacomp/reviews'], $contextdata);
            }

        }

        // block_exacompcmassign
        // does not need to export, because this data used only for comparing old<->new data
        // real data is exporting with quiz plugin

        // block_exacompcrossstud_mm
        // crossubjects related to students
        foreach ($exacompcoursescontexts as $context) {
            $courseid = $context->instanceid;
            $crosssubjectsData = array();
            $crosssubjects = block_exacomp_get_cross_subjects_by_course($courseid, $user->id);
            foreach ($crosssubjects as $cross_subject) {
                $crosssubjectsData[$cross_subject->id] = array();
                $crosssubjectsData[$cross_subject->id]['title'] = $cross_subject->title;
                $crosssubjectsData[$cross_subject->id]['description'] = $cross_subject->description;
                $crosssubjectsData[$cross_subject->id]['subjects'] = array();
                $subjects = block_exacomp_get_competence_tree_for_cross_subject($courseid, $cross_subject, true, null, $user->id);
                foreach ($subjects as $subject) {
                    $crosssubjectsData[$cross_subject->id]['subjects'][] = $subject->title;
                }
                $assessment = block_exacomp_get_user_assesment_wordings($user->id, $cross_subject->id, BLOCK_EXACOMP_TYPE_CROSSSUB, $courseid);
                $crosssubjectsData[$cross_subject->id]['assessment_grade'] = $assessment->grade;
                $crosssubjectsData[$cross_subject->id]['assessment_niveau'] = $assessment->niveau;
                $crosssubjectsData[$cross_subject->id]['assessment_selfgrade'] = $assessment->self_grade;
                $crosssubjectsData[$cross_subject->id] = array_filter($crosssubjectsData[$cross_subject->id]);
                // all other data is in the subject/topic/... data (look above).  Is it true?
            }

            if (count($crosssubjectsData)) {
                $crosssubjectsData = array('crossubjects_reviews' => $crosssubjectsData);
                //$context = \context_course::instance($courseid);
                $contextdata = \core_privacy\local\request\helper::get_context_data($context, $user);
                $contextdata = (object) array_merge((array) $contextdata, $crosssubjectsData);
                $writer = \core_privacy\local\request\writer::with_context($context);
                $writer->export_data(['Exacomp/crossubject gradings'], $contextdata);
            }
        }
        // crossubjects what I evaluate
        foreach ($exacompcoursescontexts as $context) {
            $courseid = $context->instanceid;
            $crosssubjectsData = array();
            $crosssubjects = $DB->get_fieldset_select('block_exacompcompuser',
                    'compid',
                    ' reviewerid = ? AND comptype = ? ',
                    [$user->id, BLOCK_EXACOMP_TYPE_CROSSSUB]
            );

            if ($crosssubjects) {
                $allcrossubjects = block_exacomp_get_crosssubjects();
                foreach ($crosssubjects as $crosssubjectid) {
                    if (!array_key_exists($crosssubjectid, $allcrossubjects)) {
                        continue;
                    }
                    $cross_subject = $allcrossubjects[$crosssubjectid];
                    $crosssubjectsData[$cross_subject->id] = array();
                    $crosssubjectsData[$cross_subject->id]['title'] = $cross_subject->title;
                    $crosssubjectsData[$cross_subject->id]['description'] = $cross_subject->description;
                    $subjects = block_exacomp_get_competence_tree_for_cross_subject($courseid, $cross_subject, true, null, $user->id);
                    $crosssubjectsData[$cross_subject->id]['subjects'] = array();
                    foreach ($subjects as $subject) {
                        $crosssubjectsData[$cross_subject->id]['subjects'][] = $subject->title;
                    }
                    $assessments = block_exacomp_get_teacher_assesment_wordings_array($user->id, $cross_subject->id,
                            BLOCK_EXACOMP_TYPE_CROSSSUB, $courseid);
                    $crosssubjectsData[$cross_subject->id]['my_assessment'] = $assessments;
                    $crosssubjectsData[$cross_subject->id] = array_filter($crosssubjectsData[$cross_subject->id]);
                    // all other data is in the subject/topic/... data (look above).  Is it true?
                }
            }

            if (count($crosssubjectsData)) {
                $crosssubjectsData = array('crossubjects_reviews' => $crosssubjectsData);
                //$context = \context_course::instance($courseid);
                $contextdata = \core_privacy\local\request\helper::get_context_data($context, $user);
                $contextdata = (object) array_merge((array) $contextdata, $crosssubjectsData);
                $writer = \core_privacy\local\request\writer::with_context($context);
                $writer->export_data(['Exacomp/crossubject reviews'], $contextdata);
            }
        }

        // block_exacompdescrvisibility
        // which descriptors are visible
        // select only competences, which has relation to the student
        // if the table record has studentid = 0 (for all?) -  does not export
        // So: export only data, which is not default for user
        foreach ($exacompcoursescontexts as $context) {
            $courseid = $context->instanceid;
            $descrvisiblesData = array();
            $descrhiddenData = array();
            $visibles = $DB->get_records_sql('SELECT d.title, dv.visible 
                    FROM {'.BLOCK_EXACOMP_DB_DESCVISIBILITY.'} dv
                        LEFT JOIN {'.BLOCK_EXACOMP_DB_DESCRIPTORS.'} d ON dv.descrid = d.id
                    WHERE dv.studentid = ?
                        AND dv.courseid = ?
                    ',
                    [$user->id, $courseid]
            );
            if ($visibles) {
                foreach ($visibles as $visible) {
                    if ($visible->visible) {
                        $descrvisiblesData[] = $visible->title;
                    } else {
                        $descrhiddenData[] = $visible->title;
                    }
                }
            }

            if (count($descrvisiblesData) || count($descrhiddenData)) {
                $descrvisibles = array('visible_competences' => $descrvisiblesData,
                                        'hidden_competences' => $descrhiddenData);
                //$context = \context_course::instance($courseid);
                $contextdata = \core_privacy\local\request\helper::get_context_data($context, $user);
                $contextdata = (object) array_merge((array) $contextdata, $descrvisibles);
                $writer = \core_privacy\local\request\writer::with_context($context);
                $writer->export_data(['Exacomp/visible competences/descriptors'], $contextdata);
            }
        }

        // block_exacompexampvisibility
        // which examples are visible
        // select only materials, which has relation to the student
        // if the table record has studentid = 0 (for all?) -  does not export
        // So: export only data, which is not default for user
        foreach ($exacompcoursescontexts as $context) {
            $courseid = $context->instanceid;
            $examvisiblesData = array();
            $examhiddenData = array();
            $visibles = $DB->get_records_sql('SELECT e.title, ev.visible 
                    FROM {'.BLOCK_EXACOMP_DB_EXAMPVISIBILITY.'} ev
                        LEFT JOIN {'.BLOCK_EXACOMP_DB_EXAMPLES.'} e ON ev.exampleid = e.id
                    WHERE ev.studentid = ?
                        AND ev.courseid = ?
                    ',
                    [$user->id, $courseid]
            );
            if ($visibles) {
                foreach ($visibles as $visible) {
                    if ($visible->visible) {
                        $examvisiblesData[] = $visible->title;
                    } else {
                        $examhiddenData[] = $visible->title;
                    }
                }
            }

            if (count($examvisiblesData) || count($examhiddenData)) {
                $examvisibles = array('visible_competences' => $examvisiblesData,
                                        'hidden_competences' => $examhiddenData);
                //$context = \context_course::instance($courseid);
                $contextdata = \core_privacy\local\request\helper::get_context_data($context, $user);
                $contextdata = (object) array_merge((array) $contextdata, $examvisibles);
                $writer = \core_privacy\local\request\writer::with_context($context);
                $writer->export_data(['Exacomp/visible competences/examples'], $contextdata);
            }
        }

        // block_exacompexternaltrainer
        // external trainers for student
        $context = \context_user::instance($user->id);
        $externaltrainersData = array();
        $externaltrainers = $DB->get_fieldset_sql('SELECT DISTINCT u.id  
                FROM {'.BLOCK_EXACOMP_DB_EXTERNAL_TRAINERS.'} et
                    LEFT JOIN {user} u ON et.trainerid = u.id
                WHERE et.studentid = ?                        
                ',
                [$user->id]
        );
        if ($externaltrainers) {
            foreach ($externaltrainers as $trainer) {
                $trainerobject = $DB->get_record('user', array('id' => $trainer));
                if ($trainerobject) {
                    $externaltrainersData[] = fullname($trainerobject);
                }
            }
        }
        if (count($externaltrainersData)) {
            $externaltrainersData = array('external_trainers' => $externaltrainersData);
            $contextdata = \core_privacy\local\request\helper::get_context_data($context, $user);
            $contextdata = (object) array_merge((array) $contextdata, $externaltrainersData);
            $writer = \core_privacy\local\request\writer::with_context($context);
            $writer->export_data(['Exacomp/external/trainers'], $contextdata);
        }
        // my external students
        $context = \context_user::instance($user->id);
        $externalstudentsData = array();
        $externalstudents = $DB->get_fieldset_sql('SELECT DISTINCT u.id  
                FROM {'.BLOCK_EXACOMP_DB_EXTERNAL_TRAINERS.'} et
                    LEFT JOIN {user} u ON et.studentid = u.id
                WHERE et.trainerid = ?                        
                ',
                [$user->id]
        );
        if ($externalstudents) {
            foreach ($externalstudents as $student) {
                $studentobject = $DB->get_record('user', array('id' => $student));
                if ($studentobject) {
                    $externalstudentsData[] = fullname($studentobject);
                }
            }
        }
        if (count($externalstudentsData)) {
            $externalstudentsData = array('external_students' => $externalstudentsData);
            $contextdata = \core_privacy\local\request\helper::get_context_data($context, $user);
            $contextdata = (object) array_merge((array) $contextdata, $externalstudentsData);
            $writer = \core_privacy\local\request\writer::with_context($context);
            $writer->export_data(['Exacomp/external/students'], $contextdata);
        }

        // block_exacompprofilesettings
        $context = \context_user::instance($user->id);
        $selectedCourcesData = array();
        $selectedCources = $DB->get_fieldset_sql('SELECT DISTINCT c.fullname   
                FROM {block_exacompprofilesettings} ps
                    LEFT JOIN {course} c ON ps.itemid = c.id AND ps.block = ?
                WHERE ps.userid = ?                        
                ',
                ['exacomp', $user->id]
        );
        if ($selectedCources) {
            foreach ($selectedCources as $courseTitle) {
                $selectedCourcesData[] = $courseTitle; // title: is it enough?
            }
        }
        if (count($selectedCourcesData)) {
            $selectedCourcesData = array('courses_for_profile' => $selectedCourcesData);
            $contextdata = \core_privacy\local\request\helper::get_context_data($context, $user);
            $contextdata = (object) array_merge((array) $contextdata, $selectedCourcesData);
            $writer = \core_privacy\local\request\writer::with_context($context);
            $writer->export_data(['Exacomp/Competence profile/courses'], $contextdata);
        }

        // block_exacompschedule
        // which examples were added to student's scheduler
        foreach ($exacompcoursescontexts as $context) {
            $courseid = $context->instanceid;
            $examplesData = array();
            $examples = $DB->get_records_sql(
                    'SELECT DISTINCT e.title as example_title, 
                                    s.creatorid as creator_id, 
                                    s.timecreated as timecreated,
                                    s.timemodified as timemodified,
                                    s.sorting as sorting,
                                    s.start as startts,
                                    s.end as endts                                                                           
                        FROM {'.BLOCK_EXACOMP_DB_SCHEDULE.'} s
                            LEFT JOIN {'.BLOCK_EXACOMP_DB_EXAMPLES.'} e ON e.id = s.exampleid                                                   
                        WHERE s.studentid = ?
                            AND s.courseid = ?
                            AND s.deleted = 0 
                        ORDER BY s.sorting ',
                            [$user->id, $courseid]
            );
            foreach ($examples as $example) {
                $creator = $DB->get_record('user', ['id' => $example->creator_id]);
                $examplesData[] = array_filter(array(
                    'scheduled_example' => $example->example_title,
                    'scheduled_author' => fullname($creator),
                    'scheduled_timecreated' => transform::datetime($example->timecreated),
                    'scheduled_timemodified' => transform::datetime($example->timemodified),
                    'scheduled_starttime' => ($example->startts ? transform::datetime($example->startts) : ''),
                    'scheduled_endtime' => ($example->endts ? transform::datetime($example->endts) : ''),
                    //'scheduled_sorting_position' => transform::datetime($example->timemodified),
                ));
            }

            if (count($examplesData)) {
                $examplesData = array('scheduled_examples' => $examplesData);
                //$context = \context_course::instance($courseid);
                $contextdata = \core_privacy\local\request\helper::get_context_data($context, $user);
                $contextdata = (object) array_merge((array) $contextdata, $examplesData);
                $writer = \core_privacy\local\request\writer::with_context($context);
                $writer->export_data(['Exacomp/scheduled examples'], $contextdata);
            }
        }
        // which examples were added from me
        foreach ($exacompcoursescontexts as $context) {
            $courseid = $context->instanceid;
            $examplesData = array();
            $examples = $DB->get_records_sql(
                    'SELECT DISTINCT e.title as example_title, 
                                    s.studentid as student_id,                                 
                                    s.timecreated as timecreated,
                                    s.timemodified as timemodified,
                                    s.sorting as sorting,
                                    s.start as startts,
                                    s.end as endts                                                                           
                        FROM {'.BLOCK_EXACOMP_DB_SCHEDULE.'} s
                            LEFT JOIN {'.BLOCK_EXACOMP_DB_EXAMPLES.'} e ON e.id = s.exampleid                                                   
                        WHERE s.creatorid = ?
                            AND s.courseid = ?
                            AND s.deleted = 0 
                        ORDER BY s.sorting ',
                            [$user->id, $courseid]
            );
            foreach ($examples as $example) {
                $student = $DB->get_record('user', ['id' => $example->student_id]);
                $examplesData[] = array_filter(array(
                    'scheduled_example' => $example->example_title,
                    //'scheduled_student' => fullname($student), // to add name of student?
                    'scheduled_timecreated' => transform::datetime($example->timecreated),
                    'scheduled_timemodified' => transform::datetime($example->timemodified),
                    'scheduled_starttime' => ($example->startts ? transform::datetime($example->startts) : ''),
                    'scheduled_endtime' => ($example->endts ? transform::datetime($example->endts) : ''),
                    // may be to add count of related students?
                    //'scheduled_sorting_position' => transform::datetime($example->timemodified),
                ));
            }

            if (count($examplesData)) {
                $examplesData = array('scheduled_examples' => $examplesData);
                //$context = \context_course::instance($courseid);
                $contextdata = \core_privacy\local\request\helper::get_context_data($context, $user);
                $contextdata = (object) array_merge((array) $contextdata, $examplesData);
                $writer = \core_privacy\local\request\writer::with_context($context);
                $writer->export_data(['Exacomp/my scheduled examples'], $contextdata);
            }
        }

        // block_exacompsolutvisibility
        // solutions visibility
        foreach ($exacompcoursescontexts as $context) {
            $courseid = $context->instanceid;
            $solutvisiblesData = array();
            $soluthiddenData = array();
            $visibles = $DB->get_records_sql('SELECT e.title, sol.visible 
                    FROM {'.BLOCK_EXACOMP_DB_SOLUTIONVISIBILITY.'} sol
                        LEFT JOIN {'.BLOCK_EXACOMP_DB_EXAMPLES.'} e ON sol.exampleid = e.id
                    WHERE sol.studentid = ?
                        AND sol.courseid = ?
                    ',
                    [$user->id, $courseid]
            );
            if ($visibles) {
                foreach ($visibles as $visible) {
                    if ($visible->visible) {
                        $solutvisiblesData[] = $visible->title;
                    } else {
                        $soluthiddenData[] = $visible->title;
                    }
                }
            }

            if (count($solutvisiblesData) || count($soluthiddenData)) {
                $solutvisiblesData = array('visible_solutions' => $solutvisiblesData,
                        'hidden_solutions' => $soluthiddenData);
                //$context = \context_course::instance($courseid);
                $contextdata = \core_privacy\local\request\helper::get_context_data($context, $user);
                $contextdata = (object) array_merge((array) $contextdata, $solutvisiblesData);
                $writer = \core_privacy\local\request\writer::with_context($context);
                $writer->export_data(['Exacomp/visible competences/solutions'], $contextdata);
            }
        }

        // block_exacomptopicvisibility
        foreach ($exacompcoursescontexts as $context) {
            $courseid = $context->instanceid;
            $topicvisiblesData = array();
            $topichiddenData = array();
            $visibles = $DB->get_records_sql('SELECT t.title, tv.visible 
                    FROM {'.BLOCK_EXACOMP_DB_TOPICVISIBILITY.'} tv
                        LEFT JOIN {'.BLOCK_EXACOMP_DB_TOPICS.'} t ON tv.topicid = t.id
                    WHERE tv.studentid = ?
                        AND tv.courseid = ?
                    ',
                    [$user->id, $courseid]
            );
            if ($visibles) {
                foreach ($visibles as $visible) {
                    if ($visible->visible) {
                        $topicvisiblesData[] = $visible->title;
                    } else {
                        $topichiddenData[] = $visible->title;
                    }
                }
            }

            if (count($topicvisiblesData) || count($topichiddenData)) {
                $topicsvisibles = array('visible_topics' => $topicvisiblesData,
                        'hidden_topics' => $topichiddenData);
                //$context = \context_course::instance($courseid);
                $contextdata = \core_privacy\local\request\helper::get_context_data($context, $user);
                $contextdata = (object) array_merge((array) $contextdata, $topicsvisibles);
                $writer = \core_privacy\local\request\writer::with_context($context);
                $writer->export_data(['Exacomp/visible competences/topics'], $contextdata);
            }
        }

        // block_exacompwsdata
        // does not need to export, because it is temporary data for working of webservices

    }

    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;
        if (get_class($context) != 'context_course') {
            return;
        }
        $courseid = $context->instanceid;
        if ($courseid) {
            $DB->delete_records('block_exacompcompuser', ['courseid' => $courseid]); // for students
            //$DB->delete_records('block_exacompcompuser', ['courseid' => $courseid]); // for teachers
            $DB->delete_records('block_exacompdescrvisibility', ['courseid' => $courseid]);
            $DB->delete_records('block_exacompexameval', ['courseid' => $courseid]); // for students
            //$DB->delete_records('block_exacompexameval', ['courseid' => $courseid]); // for teachers
            $DB->delete_records('block_exacompexampvisibility', ['courseid' => $courseid]);
        }
        return;
    }

    public static function delete_data_for_user(\core_privacy\local\request\approved_contextlist $contextlist) {
        global $DB;
        if (empty($contextlist->count())) {
            return;
        }
        $userid = $contextlist->get_user()->id;

        $exacompcoursescontexts = $contextlist->get_contexts();
        foreach ($exacompcoursescontexts as $k => $context) {
            if (get_class($context) != 'context_course') {
                unset($exacompcoursescontexts[$k]);
            }
        }
        foreach ($exacompcoursescontexts as $context) {
            $courseid = $context->instanceid;
            $DB->delete_records('block_exacompautotestassign', ['userid' => $userid]);
            // block_exacompcmassign
            // does not need to delete, because this data used only for comparing old<->new data
            // real data is deleting with quiz plugin
            //$DB->delete_records('block_exacompcmassign', ['userid' => $userid]);

            $DB->delete_records('block_exacompcompuser', ['userid' => $userid, 'courseid' => $courseid]); // for students
            $DB->delete_records('block_exacompcompuser', ['reviewerid' => $userid, 'courseid' => $courseid]); // for teachers
            // block_exacompcompuser_mm - looks like not used anymore
            //$DB->delete_records('block_exacompcompuser_mm', ['userid' => $userid]);
            $DB->delete_records('block_exacompcrossstud_mm', ['studentid' => $userid]);
            $DB->delete_records('block_exacompcrosssubjects', ['creatorid' => $userid]);
            $DB->delete_records('block_exacompdescrvisibility', ['studentid' => $userid, 'courseid' => $courseid]);
            $DB->delete_records('block_exacompexameval', ['studentid' => $userid, 'courseid' => $courseid]); // for students
            $DB->delete_records('block_exacompexameval', ['teacher_reviewerid' => $userid, 'courseid' => $courseid]); // for teachers
            $DB->delete_records('block_exacompexampvisibility', ['studentid' => $userid, 'courseid' => $courseid]);
            $DB->delete_records('block_exacompexternaltrainer', ['studentid' => $userid]); // for students
            $DB->delete_records('block_exacompexternaltrainer', ['trainerid' => $userid]); // for trainers
            $DB->delete_records('block_exacompprofilesettings', ['userid' => $userid]);
            $DB->delete_records('block_exacompschedule', ['studentid' => $userid]);
            $DB->delete_records('block_exacompschedule', ['creatorid' => $userid]);
            $DB->delete_records('block_exacompsolutvisibility', ['studentid' => $userid]);
            $DB->delete_records('block_exacomptopicvisibility', ['studentid' => $userid]);
            $DB->delete_records('block_exacompwsdata', ['userid' => $userid]);
        }
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;
        $context = $userlist->get_context();
        if (get_class($context) != 'context_course') {
            return;
        }
        $courseid = $context->instanceid;

        list($inSql, $inParams) = $DB->get_in_or_equal($userlist->get_userids(), SQL_PARAMS_NAMED);
        $params = $inParams;

        $select = " userid {$inSql}";
        $DB->delete_records_select('block_exacompautotestassign', $select, $params);
        $DB->delete_records_select('block_exacompwsdata', $select, $params);
        $DB->delete_records_select('block_exacompprofilesettings', $select, $params);

        $select = " studentid {$inSql}";
        $DB->delete_records_select('block_exacompcrossstud_mm', $select, $params);
        $DB->delete_records_select('block_exacompexternaltrainer', $select, $params);
        $DB->delete_records_select('block_exacompschedule', $select, $params);
        $DB->delete_records_select('block_exacompsolutvisibility', $select, $params);
        $DB->delete_records_select('block_exacomptopicvisibility', $select, $params);

        $select = " creatorid {$inSql}";
        $DB->delete_records_select('block_exacompschedule', $select, $params);
        $DB->delete_records_select('block_exacompcrosssubjects', $select, $params);

        $select = " trainerid {$inSql}";
        $DB->delete_records_select('block_exacompexternaltrainer', $select, $params);

        $params += ['courseid' => $courseid];

        $select = " studentid {$inSql} AND courseid = :courseid ";
        $DB->delete_records_select('block_exacompdescrvisibility', $select, $params);
        $DB->delete_records_select('block_exacompexameval', $select, $params);
        $DB->delete_records_select('block_exacompexampvisibility', $select, $params);

        $select = " userid {$inSql} AND courseid = :courseid ";
        $DB->delete_records_select('block_exacompcompuser', $select, $params);

        $select = " reviewerid {$inSql} AND courseid = :courseid ";
        $DB->delete_records_select('block_exacompcompuser', $select, $params);

        $select = " teacher_reviewerid {$inSql} AND courseid = :courseid ";
        $DB->delete_records_select('block_exacompexameval', $select, $params);

    }

    /**
     * Get the language string identifier with the component's language
     * file to explain why this plugin stores no data.
     *
     * @return  string
     */
    /*public static function get_reason() : string {
        return 'privacy:metadata';
    }*/


};

function block_exacomp_get_user_assesment_wordings($userid, $competenceid, $competencetype, $courseid, $forRole = BLOCK_EXACOMP_ROLE_STUDENT) {
    //$result = block_exacomp_get_user_assesment($userid, $competenceid, $competencetype, $courseid);
    /*if ($forRole == BLOCK_EXACOMP_ROLE_STUDENT) {
        $evaluation = block_exacomp_get_comp_eval($courseid, BLOCK_EXACOMP_ROLE_TEACHER, $userid, $competencetype, $competenceid);
    } elseif ($forRole == BLOCK_EXACOMP_ROLE_TEACHER) {
        $evaluation = block_exacomp_get_comp_eval($courseid, BLOCK_EXACOMP_ROLE_STUDENT, $userid, $competencetype, $competenceid);*/
    $teacher_evaluation = block_exacomp_get_comp_eval($courseid, BLOCK_EXACOMP_ROLE_TEACHER, $userid, $competencetype, $competenceid);
    $self_evaluation = block_exacomp_get_comp_eval($courseid, BLOCK_EXACOMP_ROLE_STUDENT, $userid, $competencetype, $competenceid);
    $value_titles_self_assessment = \block_exacomp\global_config::get_student_eval_items(false, $competencetype);
    $teacher_eval_items = \block_exacomp\global_config::get_teacher_eval_items($courseid, false, BLOCK_EXACOMP_ASSESSMENT_TYPE_VERBOSE);
    //}
    if ($teacher_evaluation || $self_evaluation) {
        $evaluation = new \stdClass();
        if (array_key_exists(@$self_evaluation->value, $value_titles_self_assessment)) {
            $evaluation->self_grade = $value_titles_self_assessment[$self_evaluation->value];
        } else {
            $evaluation->self_grade = null;
        }
        if (block_exacomp_get_assessment_diffLevel($competencetype)) {
            $evaluation->niveau = block_exacomp_get_assessment_diffLevel_verb($teacher_evaluation->evalniveauid);
        } else {
            $evaluation->niveau = mull;
        }
        if (block_exacomp_additional_grading($competencetype)) {
            $result_grade = '';
            switch (block_exacomp_additional_grading($competencetype)) {
                case BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE:
                    $result_grade = block_exacomp_format_eval_value($teacher_evaluation->additionalinfo);
                    break;
                case BLOCK_EXACOMP_ASSESSMENT_TYPE_VERBOSE:
                    $value = @$teacher_evaluation->value === null ? -1 : @$teacher_evaluation->value;
                    if (isset($teacher_eval_items[$value])) {
                        $result_grade = $teacher_eval_items[$value];
                    }
                    break;
                case BLOCK_EXACOMP_ASSESSMENT_TYPE_POINTS:
                    $result_grade = block_exacomp_format_eval_value($teacher_evaluation->value);
                    break;
                case BLOCK_EXACOMP_ASSESSMENT_TYPE_YESNO:
                    if ($teacher_evaluation->value > 0) {
                        $result_grade = block_exacomp_get_string('yes_no_Yes');
                    }
                    break;
            }
            $evaluation->grade = $result_grade;
        } else {
            $evaluation->grade = null;
        }
    } else {
        $evaluation = (object)array(
                'grade' => null,
                'niveau' => null,
                'self_grade' => null,
        );
    }
    return $evaluation;
}

function block_exacomp_get_teacher_assesment_wordings_array($teacherid, $competenceid, $competencetype, $courseid) {
    global $DB;
    $result = array();
    switch ($competencetype) {
        case BLOCK_EXACOMP_TYPE_EXAMPLE:
            $evaluations = $DB->get_records(BLOCK_EXACOMP_DB_EXAMPLEEVAL,
                    array('teacher_reviewerid' => $teacherid,
                            'courseid' => $courseid,
                            'exampleid' => $competenceid,
                            ));
            break;
        default:
            $evaluations = $DB->get_records(BLOCK_EXACOMP_DB_COMPETENCES,
                    array('reviewerid' => $teacherid,
                            'role' => BLOCK_EXACOMP_ROLE_TEACHER,
                            'courseid' => $courseid,
                            'compid' => $competenceid,
                            'comptype' => $competencetype));
    }
    if ($evaluations) {
        $teacher_eval_items = \block_exacomp\global_config::get_teacher_eval_items($courseid, false, BLOCK_EXACOMP_ASSESSMENT_TYPE_VERBOSE);
        foreach ($evaluations as $eval) {
            if ($competencetype == BLOCK_EXACOMP_TYPE_EXAMPLE) {
                $eval->value = $eval->teacher_evaluation;
            }
            $niveau = block_exacomp_get_assessment_diffLevel_verb($eval->evalniveauid);
            $result_grade = '';
            switch (block_exacomp_additional_grading($competencetype)) {
                case BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE:
                    $result_grade = block_exacomp_format_eval_value($eval->additionalinfo);
                    break;
                case BLOCK_EXACOMP_ASSESSMENT_TYPE_VERBOSE:
                    $value = @$eval->value === null ? -1 : @$eval->value;
                    if (isset($teacher_eval_items[$value])) {
                        $result_grade = $teacher_eval_items[$value];
                    }
                    break;
                case BLOCK_EXACOMP_ASSESSMENT_TYPE_POINTS:
                    $result_grade = block_exacomp_format_eval_value($eval->value);
                    break;
                case BLOCK_EXACOMP_ASSESSMENT_TYPE_YESNO:
                    if ($eval->value > 0) {
                        $result_grade = block_exacomp_get_string('yes_no_Yes');
                    }
                    break;
            }
            $result[] = $niveau.':'.$result_grade;
        }
    }
    return $result;
}




