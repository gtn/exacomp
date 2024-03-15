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

namespace block_exacomp\externallib;

defined('MOODLE_INTERNAL') || die();

require_once $CFG->dirroot . '/mod/assign/locallib.php';
require_once $CFG->dirroot . '/mod/assign/submission/file/locallib.php';
require_once $CFG->dirroot . '/lib/filelib.php';

use block_enrolcode_lib;
use block_exacomp\cross_subject;
use block_exacomp\db_record;
use block_exacomp\descriptor;
use block_exacomp\event\example_commented;
use block_exacomp\event\example_submitted;
use block_exacomp\example;
use block_exacomp\global_config;
use block_exacomp\globals as g;
use block_exacomp\topic;
use block_exacomp_permission_exception;
use block_exaport\api;
use context_course;
use context_module;
use context_system;
use context_user;
use core_plugin_manager;
use Exception;
use external_files;
use external_format_value;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_util;
use external_value;
use invalid_parameter_exception;
use moodle_exception;
use moodle_url;
use stdClass;
use user_picture;

class externallib extends base {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_courses_parameters() {
        return new external_function_parameters(array(
            'userid' => new external_value(PARAM_INT, 'id of user', VALUE_DEFAULT),
        ));
    }

    /**
     * Get courses with exacomp block instances.
     * get courses
     *
     * @ws-type-read
     * @return array of user courses
     */
    public static function get_courses($userid = null) {
        global $CFG, $DB, $USER;
        require_once("$CFG->dirroot/lib/enrollib.php");

        static::validate_parameters(static::get_courses_parameters(), array(
            'userid' => $userid,
        ));

        if (!$userid) {
            $userid = $USER->id;
        }

        static::require_can_access_user($userid);

        $mycourses = enrol_get_users_courses($userid, true);
        $courses = array();

        $time = time();

        foreach ($mycourses as $mycourse) {
            if ($mycourse->visible == 0 || isset($mycourse->enddate) && isset($mycourse->enddate) && $mycourse->enddate < $time && $mycourse->enddate != 0) { //enddate is a smaller number than today ==> NOT visible, since it is over already
                continue;
            }

            $context = context_course::instance($mycourse->id);
            if ($DB->record_exists("block_instances", array(
                "blockname" => "exacomp",
                "parentcontextid" => $context->id,
            ))
            ) {

                if (block_exacomp_is_teacher($mycourse->id, $userid)) {
                    $exarole = BLOCK_EXACOMP_WS_ROLE_TEACHER;

                    $teachercanedit = block_exacomp_is_editingteacher($mycourse->id, $userid);
                } else {
                    $exarole = BLOCK_EXACOMP_ROLE_STUDENT;
                    $teachercanedit = false;
                }

                // $cache = \cache::make('block_exacomp', 'course_topics_configured');
                //
                // if ($val = $cache->get($mycourse->id)) {
                //     $course_topics_configured = $val == 'set';
                // } else {
                $course_topics_configured = !!block_exacomp_get_topics_by_subject($mycourse->id, null, true);
                //     $cache->set($mycourse->id, $course_topics_configured ? 'set' : 'notset');
                // }

                $course = array(
                    "courseid" => $mycourse->id,
                    "fullname" => $mycourse->fullname,
                    "shortname" => $mycourse->shortname,
                    "assessment_config" => $DB->get_field('block_exacompsettings', 'assessmentconfiguration', ['courseid' => $mycourse->id]),
                    "exarole" => $exarole,
                    'course_topics_configured' => $course_topics_configured,
                    "teachercanedit" => $teachercanedit,
                );
                $courses[] = $course;
            }
        }

        return $courses;
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function get_courses_returns() {
        return new external_multiple_structure(new external_single_structure(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'fullname' => new external_value(PARAM_TEXT, 'fullname of course'),
            'shortname' => new external_value(PARAM_RAW, 'shortname of course'),
            'exarole' => new external_value(PARAM_INT, '1=trainer, 2=student'),
            'teachercanedit' => new external_value(PARAM_BOOL),
            'course_topics_configured' => new external_value(PARAM_BOOL, 'only available for teachers (used in diggr+)', VALUE_OPTIONAL),
            'assessment_config' => new external_value(PARAM_RAW, 'which course specific assessment_config is used'),
        )));
    }

    /*
	 * Returns description of method parameters
	 * @return external_function_parameters
	 */
    public static function get_examples_for_subject_parameters() {
        return new external_function_parameters(array(
            'subjectid' => new external_value(PARAM_INT, 'id of subject'),
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'userid' => new external_value(PARAM_INT, 'id of user'),
        ));
    }

    /**
     * Get examples for subtopic
     * Get examples
     *
     * @ws-type-read
     * @param int subjectid
     * @return array of examples
     */
    public static function get_examples_for_subject($subjectid, $courseid, $userid) {
        global $DB, $USER;

        if (empty ($subjectid) || empty ($courseid)) {
            throw new invalid_parameter_exception ('Parameter can not be empty');
        }

        static::validate_parameters(static::get_examples_for_subject_parameters(), array(
            'subjectid' => $subjectid,
            'courseid' => $courseid,
            'userid' => $userid,
        ));

        if ($userid == 0) {
            $userid = $USER->id;
        }

        static::require_can_access_user($userid);

        $structure = array();

        $topics = block_exacomp_get_topics_by_subject($courseid, $subjectid);
        foreach ($topics as $topic) {
            if (!array_key_exists($topic->id, $structure)) {
                $structure[$topic->id] = new stdClass ();
                $structure[$topic->id]->topicid = $topic->id;
                $structure[$topic->id]->title = static::custom_htmltrim($topic->title);
                $structure[$topic->id]->requireaction = false;
                $structure[$topic->id]->examples = array();
                $structure[$topic->id]->quizes = array();
            }
            $descriptors = block_exacomp_get_descriptors_by_topic($courseid, $topic->id, false, true);

            foreach ($descriptors as $descriptor) {
                $examples = $DB->get_records_sql("SELECT de.id as deid, e.id, e.title, e.externalurl,
						e.externalsolution, e.externaltask, e.completefile, e.description, e.source, e.creatorid
						FROM {" . BLOCK_EXACOMP_DB_EXAMPLES . "} e
						JOIN {" . BLOCK_EXACOMP_DB_DESCEXAMP . "} de ON e.id=de.exampid AND de.descrid=?
						ORDER BY de.sorting
						", array(
                    $descriptor->id,
                ));

                foreach ($examples as $example) {
                    if ($example->source == BLOCK_EXACOMP_EXAMPLE_SOURCE_USER && $example->creatorid != $userid) {
                        // skip non user examples
                        continue;
                    }

                    // TODO: is this dead code?
                    /*
					$taxonomies = block_exacomp_get_taxonomies_by_example($example);
					if(!empty($taxonomies)){
						$taxonomy = reset($taxonomies);

						$example->taxid = $taxonomy->id;
						$example->tax = $taxonomy->title;
					}else{
						$example->taxid = null;
						$example->tax = "";
					}
					*/

                    if (!array_key_exists($example->id, $structure[$topic->id]->examples)) {
                        $structure[$topic->id]->examples[$example->id] = new stdClass ();
                        $structure[$topic->id]->examples[$example->id]->exampleid = $example->id;
                        $structure[$topic->id]->examples[$example->id]->example_title = static::custom_htmltrim($example->title);
                        $structure[$topic->id]->examples[$example->id]->example_creatorid = $example->creatorid;
                        $items_examp = $DB->get_records(BLOCK_EXACOMP_DB_ITEM_MM, array(
                            'exacomp_record_id' => $example->id,
                        ));
                        $items = array();
                        foreach ($items_examp as $item_examp) {
                            $item_db = $DB->get_record('block_exaportitem', array(
                                'id' => $item_examp->itemid,
                            ));
                            if ($item_db->userid == $userid) {
                                $items[] = $item_examp;
                            }
                        }
                        if (!empty ($items)) {
                            // check for current
                            $current_timestamp = 0;
                            foreach ($items as $item) {
                                if ($item->timecreated > $current_timestamp) {
                                    $structure[$topic->id]->examples[$example->id]->example_item = $item->itemid;
                                    $structure[$topic->id]->examples[$example->id]->example_status = $item->status;

                                    if ($item->status == 0) {
                                        $structure[$topic->id]->requireaction = true;
                                    }
                                }
                            }
                        } else {
                            $structure[$topic->id]->examples[$example->id]->example_item = -1;
                            $structure[$topic->id]->examples[$example->id]->example_status = -1;
                        }
                    }
                }

                // Quiz webservices are available from Moodle 3.1 onwards
                global $CFG;
                if ($CFG->version >= 2016052300) {
                    $quizes = $DB->get_records_sql("SELECT q.id, q.name, q.grade
							FROM {" . BLOCK_EXACOMP_DB_EXAMPLES . "} ex
                            JOIN {" . BLOCK_EXACOMP_DB_DESCEXAMP . "} dex ON ex.id = dex.exampid
							JOIN {course_modules} cm ON ex.activityid = cm.id
							JOIN {modules} m ON cm.module = m.id
							JOIN {quiz} q ON cm.instance = q.id
							WHERE m.name = 'quiz' AND dex.descrid = ?
							", array(
                            $descriptor->id,
                        )
                    );

                    foreach ($quizes as $quiz) {
                        $cm = get_coursemodule_from_instance('quiz', $quiz->id);
                        if (!$cm->visible) {
                            continue;
                        }

                        if (!array_key_exists($quiz->id, $structure[$topic->id]->quizes)) {
                            $structure[$topic->id]->quizes[$quiz->id] = new stdClass ();
                            $structure[$topic->id]->quizes[$quiz->id]->quizid = $quiz->id;
                            $structure[$topic->id]->quizes[$quiz->id]->quiz_title = static::custom_htmltrim($quiz->name);
                            $structure[$topic->id]->quizes[$quiz->id]->quiz_grade = $quiz->grade;
                        }
                    }
                }
            }
        }

        return $structure;
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function get_examples_for_subject_returns() {
        return new external_multiple_structure(new external_single_structure(array(
            'topicid' => new external_value(PARAM_INT, 'id of topic'),
            'title' => new external_value(PARAM_TEXT, 'title of topic'),
            'requireaction' => new external_value(PARAM_BOOL, 'trainer action required or not'),
            'examples' => new external_multiple_structure(new external_single_structure(array(
                'exampleid' => new external_value(PARAM_INT, 'id of example'),
                'example_title' => new external_value(PARAM_TEXT, 'title of example'),
                'example_item' => new external_value(PARAM_INT, 'current item id'),
                'example_status' => new external_value(PARAM_INT, 'status of current item'),
                'example_creatorid' => new external_value(PARAM_INT, 'creator of example'),
            ))),
            'quizes' => new external_multiple_structure(new external_single_structure(array(
                'quizid' => new external_value(PARAM_INT, 'id of quiz'),
                'quiz_title' => new external_value(PARAM_TEXT, 'title of quiz'),
                'quiz_grade' => new external_value(PARAM_FLOAT, 'sum grade of quiz'),

            )), 'quiz data', VALUE_OPTIONAL),
        )));
    }

    /*
	 * Returns description of method parameters
	 * @return external_function_parameters
	 */
    public static function get_examples_for_subject_with_lfs_infos_parameters() {
        return new external_function_parameters(array(
            'subjectid' => new external_value(PARAM_INT, 'id of subject'),
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'userid' => new external_value(PARAM_INT, 'id of user'),
        ));
    }

    /**
     * Get examples for subtopic
     * Get examples
     *
     * @ws-type-read
     * @param int subjectid
     * @return array of examples
     */
    public static function get_examples_for_subject_with_lfs_infos($subjectid, $courseid, $userid) {
        global $DB, $USER;

        if (empty ($subjectid) || empty ($courseid)) {
            throw new invalid_parameter_exception ('Parameter can not be empty');
        }

        static::validate_parameters(static::get_examples_for_subject_with_lfs_infos_parameters(), array(
            'subjectid' => $subjectid,
            'courseid' => $courseid,
            'userid' => $userid,
        ));

        if ($userid == 0) {
            $userid = $USER->id;
        }

        static::require_can_access_user($userid);

        $coursesettings = block_exacomp_get_settings_by_course($course['courseid']);
        $cm_mm = block_exacomp_get_course_module_association($course['courseid']);

        $structure = array();

        $topics = block_exacomp_get_topics_by_subject($courseid, $subjectid);
        foreach ($topics as $topic) {
            $topic_total_competencies = 0;

            if (!array_key_exists($topic->id, $structure)) {
                $structure[$topic->id] = new stdClass ();
                $structure[$topic->id]->topicid = $topic->id;
                $structure[$topic->id]->title = static::custom_htmltrim($topic->title);
                $structure[$topic->id]->requireaction = false;
                $structure[$topic->id]->totalCompetencies = $topic_total_competencies;
                $structure[$topic->id]->examples = array();
                $structure[$topic->id]->quizes = array();
            }

            $descriptors = block_exacomp_get_descriptors_by_topic($courseid, $topic->id, false, true);

            foreach ($descriptors as $descriptor) {
                if ($coursesettings->show_all_descriptors || ($coursesettings->uses_activities && isset ($cm_mm->competencies[$descriptor->id]))) {
                    $topic_total_competencies++;
                }

                $examples = $DB->get_records_sql("SELECT de.id as deid, e.id, e.title, e.externalurl,
						e.externalsolution, e.externaltask, e.completefile, e.description, e.source, e.creatorid
						FROM {" . BLOCK_EXACOMP_DB_EXAMPLES . "} e
						JOIN {" . BLOCK_EXACOMP_DB_DESCEXAMP . "} de ON e.id=de.exampid AND de.descrid=?
						ORDER BY de.sorting
						", array(
                    $descriptor->id,
                ));

                foreach ($examples as $example) {
                    if ($example->source == BLOCK_EXACOMP_EXAMPLE_SOURCE_USER && $example->creatorid != $userid) {
                        // skip non user examples
                        continue;
                    }

                    // TODO: is this dead code?
                    /*
	                 $taxonomies = block_exacomp_get_taxonomies_by_example($example);
	                 if(!empty($taxonomies)){
	                 $taxonomy = reset($taxonomies);

	                 $example->taxid = $taxonomy->id;
	                 $example->tax = $taxonomy->title;
	                 }else{
	                 $example->taxid = null;
	                 $example->tax = "";
	                 }
	                 */

                    if (!array_key_exists($example->id, $structure[$topic->id]->examples)) {
                        $structure[$topic->id]->examples[$example->id] = new stdClass ();
                        $structure[$topic->id]->examples[$example->id]->exampleid = $example->id;
                        $structure[$topic->id]->examples[$example->id]->numbering = block_exacomp_get_descriptor_numbering($descriptor);
                        $structure[$topic->id]->examples[$example->id]->example_title = static::custom_htmltrim($example->title);
                        $structure[$topic->id]->examples[$example->id]->example_creatorid = $example->creatorid;
                        $items_examp = $DB->get_records(BLOCK_EXACOMP_DB_ITEM_MM, array(
                            'exacomp_record_id' => $example->id,
                        ));
                        $items = array();
                        foreach ($items_examp as $item_examp) {
                            $item_db = $DB->get_record('block_exaportitem', array(
                                'id' => $item_examp->itemid,
                            ));
                            if ($item_db->userid == $userid) {
                                $items[] = $item_examp;
                            }
                        }
                        if (!empty ($items)) {
                            // check for current
                            $current_timestamp = 0;
                            foreach ($items as $item) {
                                if ($item->timecreated > $current_timestamp) {
                                    $structure[$topic->id]->examples[$example->id]->example_item = $item->itemid;
                                    $structure[$topic->id]->examples[$example->id]->example_status = $item->status;

                                    if ($item->status == 0) {
                                        $structure[$topic->id]->requireaction = true;
                                    }
                                }
                            }
                        } else {
                            $structure[$topic->id]->examples[$example->id]->example_item = -1;
                            $structure[$topic->id]->examples[$example->id]->example_status = -1;
                        }
                    }
                }

                // Quiz webservices are available from Moodle 3.1 onwards
                global $CFG;
                if ($CFG->version >= 2016052300) {
                    $quizes = $DB->get_records_sql("SELECT q.id, q.name, q.grade
							FROM {" . BLOCK_EXACOMP_DB_COMPETENCE_ACTIVITY . "} ca
							JOIN {course_modules} cm ON ca.activityid = cm.id
							JOIN {modules} m ON cm.module = m.id
							JOIN {quiz} q ON cm.instance = q.id
							WHERE m.name = 'quiz' AND ca.compid = ? AND ca.comptype = ?
							", array(
                            $descriptor->id, BLOCK_EXACOMP_TYPE_DESCRIPTOR,
                        )
                    );

                    foreach ($quizes as $quiz) {
                        $cm = get_coursemodule_from_instance('quiz', $quiz->id);
                        if (!$cm->visible) {
                            continue;
                        }

                        if (!array_key_exists($quiz->id, $structure[$topic->id]->quizes)) {
                            $structure[$topic->id]->quizes[$quiz->id] = new stdClass ();
                            $structure[$topic->id]->quizes[$quiz->id]->quizid = $quiz->id;
                            $structure[$topic->id]->quizes[$quiz->id]->quiz_title = static::custom_htmltrim($quiz->name);
                            $structure[$topic->id]->quizes[$quiz->id]->quiz_grade = $quiz->grade;
                        }
                    }
                }
            }
            $structure[$topic->id]->totalCompetencies = $topic_total_competencies;
        }

        return $structure;
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function get_examples_for_subject_with_lfs_infos_returns() {
        return new external_multiple_structure(new external_single_structure(array(
            'topicid' => new external_value(PARAM_INT, 'id of topic'),
            'title' => new external_value(PARAM_TEXT, 'title of topic'),
            'requireaction' => new external_value(PARAM_BOOL, 'trainer action required or not'),
            'totalCompetencies' => new external_value(PARAM_INT, 'amount of total competencies of this topic'),
            'examples' => new external_multiple_structure(new external_single_structure(array(
                'exampleid' => new external_value(PARAM_INT, 'id of example'),
                'numbering' => new external_value(PARAM_TEXT, 'descriptor numbering'),
                'example_title' => new external_value(PARAM_TEXT, 'title of example'),
                'example_item' => new external_value(PARAM_INT, 'current item id'),
                'example_status' => new external_value(PARAM_INT, 'status of current item'),
                'example_creatorid' => new external_value(PARAM_INT, 'creator of example'),
            ))),
            'quizes' => new external_multiple_structure(new external_single_structure(array(
                'quizid' => new external_value(PARAM_INT, 'id of quiz'),
                'quiz_title' => new external_value(PARAM_TEXT, 'title of quiz'),
                'quiz_grade' => new external_value(PARAM_FLOAT, 'sum grade of quiz'),

            )), 'quiz data', VALUE_OPTIONAL),
        )));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_example_by_id_parameters() {
        return new external_function_parameters(array(
            'exampleid' => new external_value(PARAM_INT, 'id of example'),
        ));
    }

    /**
     * Get example
     * Get example
     *
     * @ws-type-read
     * @param $exampleid
     * @return example
     * @throws invalid_parameter_exception
     */
    public static function get_example_by_id($exampleid, $courseid = null) {
        global $DB;

        if (empty ($exampleid)) {
            throw new invalid_parameter_exception ('Parameter can not be empty');
        }

        static::validate_parameters(static::get_example_by_id_parameters(), array(
            'exampleid' => $exampleid,
        ));

        if (!$courseid) {
            $courseid = static::find_courseid_for_example($exampleid);
        }
        static::require_can_access_example($exampleid, $courseid);

        $example = $DB->get_record(BLOCK_EXACOMP_DB_EXAMPLES, array(
            'id' => $exampleid,
        ));

        $example = static::block_excomp_get_example_details($example, $courseid);

        // rip out all html tags and other content if html is used
        if (strpos($example->description, "<!doctype html>") !== false) {
            $example->description = "";
        } else {
            $example->description = static::custom_htmltrim($example->description);
            $example->description = strip_tags($example->description);
        }

        return $example;
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function get_example_by_id_returns() {
        return new external_single_structure(array(
            'title' => new external_value(PARAM_TEXT, 'title of example'),
            'description' => new external_value(PARAM_TEXT, 'description of example'),
            'taskfileurl' => new external_value(PARAM_TEXT, 'task fileurl'),
            'taskfilenames' => new external_value(PARAM_TEXT, 'task filename'),
            'externalurl' => new external_value(PARAM_TEXT, 'externalurl of example'),
            'task' => new external_value(PARAM_TEXT, '@deprecated'),
            'solution' => new external_value(PARAM_TEXT, 'solution(url/description) of example'),
            //'timeframe' => new external_value(PARAM_INT, 'timeframe in minutes'),
            'timeframe' => new external_value(PARAM_TEXT, 'timeframe as string'), // like in Dakora?
            'hassubmissions' => new external_value(PARAM_BOOL, 'true if example has already submissions'),
            'quiz' => new external_single_structure(array(
                'quizid' => new external_value(PARAM_INT, 'id of quiz'),
                'quiz_title' => new external_value(PARAM_TEXT, 'title of quiz'),
                'quiz_grade' => new external_value(PARAM_FLOAT, 'sum grade of quiz'),
            )),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_descriptors_for_example_parameters() {
        return new external_function_parameters(array(
            'exampleid' => new external_value(PARAM_INT, 'id of example'),
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'userid' => new external_value(PARAM_INT, 'id of user'),
        ));
    }

    /**
     * Get desciptors for example
     * Get descriptors for example
     *
     * @ws-type-read
     * @param $exampleid
     * @param $courseid
     * @param $userid
     * @return array list of descriptors
     * @throws invalid_parameter_exception
     */
    public static function get_descriptors_for_example($exampleid, $courseid, $userid) {
        global $USER;

        if ($userid == 0) {
            $userid = $USER->id;
        }

        static::validate_parameters(static::get_descriptors_for_example_parameters(), array(
            'exampleid' => $exampleid,
            'courseid' => $courseid,
            'userid' => $userid,
        ));

        static::require_can_access_user($userid);
        if ($courseid > 0) {
            static::require_can_access_example($exampleid, $courseid);
        }

        return static::_get_descriptors_for_example($exampleid, $courseid, $userid);
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function get_descriptors_for_example_returns() {
        return new external_multiple_structure(new external_single_structure(array(
            'descriptorid' => new external_value(PARAM_INT, 'id of descriptor'),
            'title' => new external_value(PARAM_TEXT, 'title of descriptor'),
            'evaluation' => new external_value(PARAM_INT, 'evaluation of descriptor'),
        )));
    }

    protected static function _get_descriptors_for_example($exampleid, $courseid, $userid) {
        global $DB, $USER;

        $descriptors_exam_mm = $DB->get_records(BLOCK_EXACOMP_DB_DESCEXAMP, array(
            'exampid' => $exampleid,
        ));
        //this would get all descriptors that are generally linked to that example, but would not take into account, that some topics may not be added to the competence grid for this course

        $sql = "SELECT dex.*
            FROM {" . BLOCK_EXACOMP_DB_DESCEXAMP . "} dex
            JOIN {" . BLOCK_EXACOMP_DB_DESCTOPICS . "} det ON dex.descrid = det.descrid
            JOIN {" . BLOCK_EXACOMP_DB_COURSETOPICS . "} ct ON det.topicid = ct.topicid
            WHERE dex.exampid = ?
            AND ct.courseid = ?";

        $descriptors_exam_mm = $DB->get_records_sql($sql, [$exampleid, $courseid]);

        $descriptors = array();
        foreach ($descriptors_exam_mm as $descriptor_mm) {

            $descriptors[$descriptor_mm->descrid] = $DB->get_record(BLOCK_EXACOMP_DB_DESCRIPTORS, array(
                'id' => $descriptor_mm->descrid,
            ));

            $descriptors[$descriptor_mm->descrid]->title = static::custom_htmltrim(strip_tags($descriptors[$descriptor_mm->descrid]->title));

            $isTeacher = $DB->record_exists('block_exacompexternaltrainer', array('trainerid' => $USER->id, 'studentid' => $userid));
            $grading = BLOCK_EXACOMP_ROLE_TEACHER;
            if (block_exacomp_is_elove_student_self_assessment_enabled() && !$isTeacher) {
                $grading = BLOCK_EXACOMP_ROLE_STUDENT;
            }
            $eval = block_exacomp_get_comp_eval($courseid, $grading, $userid, BLOCK_EXACOMP_TYPE_DESCRIPTOR, $descriptor_mm->descrid);
            if ($eval && $eval->value !== null) {
                $descriptors[$descriptor_mm->descrid]->evaluation = $eval->value;
            } else {
                $descriptors[$descriptor_mm->descrid]->evaluation = 0;
            }

            $selected_categories = $DB->get_records(BLOCK_EXACOMP_DB_DESCCAT, array("descrid" => $descriptor_mm->descrid), "", "catid");
            if ($selected_categories) {
                $categoryTitlesRes = $DB->get_records_sql('SELECT c.title, c.title as tmp
	                                                        FROM {' . BLOCK_EXACOMP_DB_CATEGORIES . '} c
	                                                        WHERE c.id IN (' . implode(',', array_keys($selected_categories)) . ')');
                $descCategories = '. ' . get_string('dakora_niveau_after_descriptor_title', 'block_exacomp') . ': ' . implode(', ', array_keys($categoryTitlesRes));
                $descriptors[$descriptor_mm->descrid]->title = rtrim($descriptors[$descriptor_mm->descrid]->title, '.');
                $descriptors[$descriptor_mm->descrid]->title .= $descCategories;
            }

            $descriptors[$descriptor_mm->descrid]->descriptorid = $descriptor_mm->descrid;
        }

        return $descriptors;
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_descriptors_for_quiz_parameters() {
        return new external_function_parameters(array(
            'quizid' => new external_value(PARAM_INT, 'id of quiz'),
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'userid' => new external_value(PARAM_INT, 'id of user'),
        ));
    }

    /**
     * Get desciptors for quiz
     * Get descriptors for quiz
     *
     * @ws-type-read
     * @param $quizid
     * @param $courseid
     * @param $userid
     * @return array list of descriptors
     * @throws invalid_parameter_exception
     */
    public static function get_descriptors_for_quiz($quizid, $courseid, $userid) {
        global $DB, $USER;

        if ($userid == 0) {
            $userid = $USER->id;
        }

        static::validate_parameters(static::get_descriptors_for_quiz_parameters(), array(
            'quizid' => $quizid,
            'courseid' => $courseid,
            'userid' => $userid,
        ));

        static::require_can_access_user($userid);
        $cm = get_coursemodule_from_instance('quiz', $quizid);
        if (!$cm->visible) {
            throw new invalid_parameter_exception('no access to the requested quiz.');
        }

        $descriptors_quiz_mm = $DB->get_records(BLOCK_EXACOMP_DB_COMPETENCE_ACTIVITY, array(
            'activityid' => $cm->id,
            'comptype' => BLOCK_EXACOMP_TYPE_DESCRIPTOR,
        ));

        $descriptors = array();
        foreach ($descriptors_quiz_mm as $descriptor_mm) {
            $descriptors[$descriptor_mm->compid] = $DB->get_record(BLOCK_EXACOMP_DB_DESCRIPTORS, array(
                'id' => $descriptor_mm->compid,
            ));

            $isTeacher = $DB->record_exists('block_exacompexternaltrainer', array('trainerid' => $USER->id, 'studentid' => $userid));
            $grading = BLOCK_EXACOMP_ROLE_TEACHER;
            if (block_exacomp_is_elove_student_self_assessment_enabled() && !$isTeacher) {
                $grading = BLOCK_EXACOMP_ROLE_STUDENT;
            }
            $eval = block_exacomp_get_comp_eval($courseid, $grading, $userid, BLOCK_EXACOMP_TYPE_DESCRIPTOR, $descriptor_mm->compid);
            if ($eval && $eval->value !== null) {
                $descriptors[$descriptor_mm->compid]->evaluation = $eval->value;
            } else {
                $descriptors[$descriptor_mm->compid]->evaluation = 0;
            }
            $descriptors[$descriptor_mm->compid]->descriptorid = $descriptor_mm->compid;
        }

        return $descriptors;
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function get_descriptors_for_quiz_returns() {
        return new external_multiple_structure(new external_single_structure(array(
            'descriptorid' => new external_value(PARAM_INT, 'id of descriptor'),
            'title' => new external_value(PARAM_TEXT, 'title of descriptor'),
            'evaluation' => new external_value(PARAM_INT, 'evaluation of descriptor'),
        )));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_user_role_parameters() {
        return new external_function_parameters(array());
    }

    /**
     * Get role for user: 1=trainer 2=student
     *
     * @ws-type-read
     * @elove (2016-05-09: only used in elove)
     * return 1 for trainer
     * 2 for student
     * 0 if false
     *
     * @return array
     */
    public static function get_user_role() {
        global $DB, $USER;

        static::validate_parameters(static::get_user_role_parameters(), array());

        $firstLoginDiggr = block_exacomp_get_custom_profile_field_value($USER->id, "diwipassapp_login");
        if (!$firstLoginDiggr) {
            $firstLoginDiggr = 0;
        }
        if ($firstLoginDiggr != 1) {
            block_exacomp_set_custom_profile_field_value($USER->id, "diwipassapp_login", 1);
        }

        $trainer = $DB->get_records(BLOCK_EXACOMP_DB_EXTERNAL_TRAINERS, array(
            'trainerid' => $USER->id,
        ));
        if ($trainer) {
            return (object)[
                "role" => BLOCK_EXACOMP_WS_ROLE_TEACHER, "diwipassapp_login" => $firstLoginDiggr,
            ];
        }

        $student = $DB->get_records(BLOCK_EXACOMP_DB_EXTERNAL_TRAINERS, array(
            'studentid' => $USER->id,
        ));

        $courses = static::get_courses($USER->id);
        foreach ($courses as $course) {
            $context = context_course::instance($course["courseid"]);
            $isTeacher = block_exacomp_is_teacher($context);
            if ($isTeacher) {
                return (object)["role" => BLOCK_EXACOMP_WS_ROLE_TEACHER, "diwipassapp_login" => $firstLoginDiggr];
            }
        }

        return (object)["role" => BLOCK_EXACOMP_WS_ROLE_STUDENT, "diwipassapp_login" => $firstLoginDiggr];

        //        // neither student or trainer depricated
        //        return (object)[
        //            "role" => 0,
        //        ];
    }

    /**
     * Returns description of method return values
     *
     * @return external_multiple_structure
     */
    public static function get_user_role_returns() {
        return new external_function_parameters(array(
            'role' => new external_value(PARAM_INT, '1=trainer, 2=student'),
            'diwipassapp_login' => new external_value(PARAM_INT, '0=first, 1=not first'),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function diggr_get_user_role_parameters() {
        return new external_function_parameters(array());
    }

    /**
     * Get role for user: 1=trainer 2=student
     *
     * @ws-type-read
     * @diggr (2019-08-02: only used in diggr)
     * return 1 for trainer or teacher
     * 2 for student
     * 0 if false
     *
     * @return array
     */
    public static function diggr_get_user_role() {
        global $DB, $USER;

        static::validate_parameters(static::diggr_get_user_role_parameters(), array());

        $firstLoginDiggr = block_exacomp_get_custom_profile_field_value($USER->id, "diwipassapp_login");
        if (!$firstLoginDiggr) {
            $firstLoginDiggr = 0;
        }
        if ($firstLoginDiggr != 1) {
            block_exacomp_set_custom_profile_field_value($USER->id, "diwipassapp_login", 1);
        }

        $trainer = $DB->get_records(BLOCK_EXACOMP_DB_EXTERNAL_TRAINERS, array(
            'trainerid' => $USER->id,
        ));
        if ($trainer) {
            return (object)[
                "role" => BLOCK_EXACOMP_WS_ROLE_TEACHER, "diwipassapp_login" => $firstLoginDiggr,
            ];
        }

        $student = $DB->get_records(BLOCK_EXACOMP_DB_EXTERNAL_TRAINERS, array(
            'studentid' => $USER->id,
        ));

        if ($student) {
            return (object)[
                "role" => BLOCK_EXACOMP_WS_ROLE_STUDENT, "diwipassapp_login" => $firstLoginDiggr,
            ];
        }

        //Added this for dgb: if the teacher is a teacher of the DGB course, it is fine.
        //In ELOVE the teacher would have to be an EXTERNALTRAINER, in DGB, being an externltrainer OR being teacher in the DGB course suffices
        //$isTeacher = block_exacomp_is_teacher(get_config('auth_dgb', 'courseid'), $USER->id);
        $courses = static::get_courses($USER->id);
        foreach ($courses as $course) {
            $context = context_course::instance($course["courseid"]);
            $isTeacher = block_exacomp_is_teacher($context);
            if ($isTeacher) {
                return (object)[
                    "role" => BLOCK_EXACOMP_WS_ROLE_TEACHER, "diwipassapp_login" => $firstLoginDiggr,
                ];
            }
        }
        return (object)[
            "role" => BLOCK_EXACOMP_WS_ROLE_STUDENT, "diwipassapp_login" => $firstLoginDiggr,
        ];

        // neither student or trainer or teacher (depricated)
        //       return (object)[
        //           "role" => 0,
        //       ];
    }

    /**
     * Returns description of method return values
     *
     * @return external_multiple_structure
     */
    public static function diggr_get_user_role_returns() {
        return new external_function_parameters(array(
            'role' => new external_value(PARAM_INT, '1=trainer, 2=student'),
            'diwipassapp_login' => new external_value(PARAM_INT, '0=first, 1=not first'),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_external_trainer_students_parameters() {
        return new external_function_parameters(array());
    }

    /**
     * Get external trainer's students
     * Get all students for an external trainer
     *
     * @ws-type-read
     * @return array all items available
     */
    public static function get_external_trainer_students() {
        global $DB, $USER;

        $students = $DB->get_records(BLOCK_EXACOMP_DB_EXTERNAL_TRAINERS, array(
            'trainerid' => $USER->id,
        ));
        $returndata = array();
        $cohorts = $DB->get_records('cohort');

        foreach ($students as $student) {
            $studentObject = $DB->get_record('user', array(
                'id' => $student->studentid,
            ));
            $returndataObject = new stdClass ();
            $returndataObject->name = fullname($studentObject);
            $returndataObject->userid = $student->studentid;
            $return_cohorts = array();

            $user_cohorts = $DB->get_records('cohort_members', array('userid' => $student->studentid));
            foreach ($user_cohorts as $user_cohort) {
                if (!isset($cohorts[$user_cohort->cohortid])) {
                    continue;
                }

                $currentCohort = new stdClass ();
                $currentCohort->cohortid = $user_cohort->cohortid;
                $currentCohort->name = $cohorts[$user_cohort->cohortid]->name;

                $return_cohorts[] = $currentCohort;
            }
            $returndataObject->cohorts = $return_cohorts;

            $returndataObject->requireaction = false;
            $user_subjects = static::get_subjects_for_user($student->studentid);
            foreach ($user_subjects as $user_subject) {
                if ($user_subject->requireaction) {
                    $returndataObject->requireaction = true;
                }
            }

            static::get_user_list_info($returndataObject, 'get_external_trainer_students');

            $returndata[] = $returndataObject;
        }

        return $returndata;
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function get_external_trainer_students_returns() {
        return new external_multiple_structure(new external_single_structure(array(
            'userid' => new external_value(PARAM_INT, 'id of user'),
            'name' => new external_value(PARAM_TEXT, 'name of user'),
            'cohorts' => new external_multiple_structure(new external_single_structure(array(
                'cohortid' => new external_value(PARAM_INT, 'id of cohort'),
                'name' => new external_value(PARAM_TEXT, 'title of cohort'),
            ))),
            'requireaction' => new external_value(PARAM_BOOL, 'trainer action required or not'),
            'examples' => new external_single_structure(array(
                'total' => new external_value(PARAM_INT),
                'submitted' => new external_value(PARAM_INT),
                'reached' => new external_value(PARAM_INT),
            )),
        )));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_subjects_for_user_parameters() {
        return new external_function_parameters(array(
            'userid' => new external_value(PARAM_INT, 'id of user'),
        ));
    }

    /**
     * Get Subjects
     * get subjects from one user for all his courses
     *
     * @ws-type-read
     * @return array of user courses
     */
    public static function get_subjects_for_user($userid) {
        global $CFG, $USER;
        require_once("$CFG->dirroot/lib/enrollib.php");

        static::validate_parameters(static::get_subjects_for_user_parameters(), array(
            'userid' => $userid,
        ));

        if ($userid == 0) {
            $userid = $USER->id;
        }

        static::require_can_access_user($userid);

        $courses = static::get_courses($userid);

        $require_actions = static::get_requireaction_subjects($userid);

        $subjects_res = array();
        foreach ($courses as $course) {
            $subjects = block_exacomp_get_subjects_by_course($course["courseid"]);

            foreach ($subjects as $subject) {
                if (!array_key_exists($subject->id, $subjects_res)) {
                    $elem = new stdClass ();
                    $elem->subjectid = $subject->id;
                    $elem->title = static::custom_htmltrim($subject->title);
                    $elem->courseid = $course["courseid"];
                    $elem->requireaction = array_key_exists($subject->id, $require_actions);
                    $subjects_res[] = $elem;
                }
            }
        }

        return $subjects_res;
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function get_subjects_for_user_returns() {
        return new external_multiple_structure(new external_single_structure(array(
            'subjectid' => new external_value(PARAM_INT, 'id of subject'),
            'title' => new external_value(PARAM_TEXT, 'title of subject'),
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'requireaction' => new external_value(PARAM_BOOL, 'whether example in this subject has been edited or not by the selected student'),
        )));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function diggrplus_get_subjects_and_topics_for_user_parameters() {
        return new external_function_parameters(array(
            'userid' => new external_value(PARAM_INT, 'id of user'),
            'courseid' => new external_value(PARAM_INT, 'id of course. This is used for teachers.', VALUE_DEFAULT, -1),
            'showonlywithexamples' => new external_value(PARAM_BOOL, 'id of course. This is used for teachers.', VALUE_DEFAULT, true),
        ));
    }

    /**
     * Get Subjects
     * get subjects from one user for all his courses or for one specific course.
     *
     * @ws-type-read
     * @return array of user courses
     */
    public static function diggrplus_get_subjects_and_topics_for_user($userid, $courseid, $showonlywithexamples) {
        global $CFG, $USER, $DB;

        static::validate_parameters(static::diggrplus_get_subjects_and_topics_for_user_parameters(), array(
            'userid' => $userid,
            'courseid' => $courseid,
            'showonlywithexamples' => $showonlywithexamples,
        ));

        if (!$userid) {
            $userid = $USER->id;
        }
        static::require_can_access_user($userid);

        $structure = array();

        if ($courseid != -1) {
            $courses = static::get_courses($userid);
            $courses = array_filter($courses, function($course) use ($courseid) {
                return $course["courseid"] == $courseid;
            });
        } else {
            $courses = static::get_courses($userid); // this is better than enrol_get_users_courses($userid);, because it checks for existance of exabis Blocks as well as for visibility
        }

        if ($showonlywithexamples) {
            $topicIdsWithExamples = $DB->get_records_sql_menu("SELECT DISTINCT dt.topicid, dt.topicid AS tmp
			FROM {" . BLOCK_EXACOMP_DB_EXAMPLES . "} e
			JOIN {" . BLOCK_EXACOMP_DB_DESCEXAMP . "} de ON e.id=de.exampid
			JOIN {" . BLOCK_EXACOMP_DB_DESCTOPICS . "} dt ON dt.descrid=de.descrid
			-- WHERE e.creatorid=X
		", [
                // TODO: auch auf user abfragen!
                //$userid,
            ]);
        }

        foreach ($courses as $course) {
            $tree = block_exacomp_get_competence_tree($course["courseid"], null, null, false, null, true, null, false, false, true, true, true);

            foreach ($tree as $subject) {
                $elem_sub = new stdClass ();
                $elem_sub->id = $subject->id;
                $elem_sub->title = static::custom_htmltrim(strip_tags($subject->title));
                $elem_sub->courseid = $course['courseid'];
                $elem_sub->courseshortname = $course['shortname'];
                $elem_sub->coursefullname = $course['fullname'];
                $elem_sub->topics = array();
                foreach ($subject->topics as $topic) {
                    if ($showonlywithexamples) {
                        if (!$topicIdsWithExamples[$topic->id]) {
                            continue;
                        }
                    }
                    $elem_topic = new stdClass ();
                    $elem_topic->id = $topic->id;
                    $elem_topic->title = static::custom_htmltrim(strip_tags($topic->title));
                    $elem_topic->descriptors = array();
                    foreach ($topic->descriptors as $descriptor) {
                        $elem_desc = new stdClass ();
                        $elem_desc->descriptorid = $descriptor->id;
                        $elem_desc->descriptortitle = $descriptor->title;
                        $elem_topic->descriptors[] = $elem_desc;
                    }
                    $elem_sub->topics[] = $elem_topic;
                }

                if (!empty($elem_sub->topics)) {
                    $structure[] = $elem_sub;
                }
            }
        }

        return $structure;
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function diggrplus_get_subjects_and_topics_for_user_returns() {
        return new external_multiple_structure(new external_single_structure(array(
            'id' => new external_value(PARAM_INT, 'id of subject'),
            'title' => new external_value(PARAM_TEXT, 'title of subject'),
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'courseshortname' => new external_value(PARAM_TEXT, 'courseshortname'),
            'coursefullname' => new external_value(PARAM_TEXT, 'coursefullname'),
            'topics' => new external_multiple_structure(new external_single_structure(array(
                'id' => new external_value(PARAM_INT, 'id of example'),
                'title' => new external_value(PARAM_TEXT, 'title of example'),
                'descriptors' => new external_multiple_structure(new external_single_structure(array(
                    'descriptorid' => new external_value(PARAM_INT, 'id of example'),
                    'descriptortitle' => new external_value(PARAM_TEXT, 'title of example'),
                ))),
            ))),
        )));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function diggrplus_get_niveaus_for_subject_parameters() {
        return new external_function_parameters(array(
            'subjectid' => new external_value(PARAM_INT, 'id of subject'),
        ));
    }

    /**
     * Get Subjects
     * get subjects from one user for all his courses
     *
     * @ws-type-read
     * @return array of user courses
     */
    public static function diggrplus_get_niveaus_for_subject($subjectid) {
        global $DB;

        static::validate_parameters(static::diggrplus_get_niveaus_for_subject_parameters(), array(
            'subjectid' => $subjectid,
        ));

        $niveaus = $DB->get_records_sql("SELECT DISTINCT n.id as niveauid, n.title as niveautitle
			FROM {" . BLOCK_EXACOMP_DB_NIVEAUS . "} n
			JOIN {" . BLOCK_EXACOMP_DB_DESCRIPTORS . "} descr ON descr.niveauid = n.id
			JOIN {" . BLOCK_EXACOMP_DB_DESCTOPICS . "} desctop ON desctop.descrid = descr.id
			JOIN {" . BLOCK_EXACOMP_DB_TOPICS . "} topic ON topic.id = desctop.topicid
			JOIN {" . BLOCK_EXACOMP_DB_SUBJECTS . "} subj ON topic.subjid = subj.id
			WHERE subj.id=? ORDER BY niveauid
		", [
            $subjectid,
        ]);

        return $niveaus;
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function diggrplus_get_niveaus_for_subject_returns() {
        return new external_multiple_structure(new external_single_structure(array(
            'niveauid' => new external_value(PARAM_INT, 'id of subject'),
            'niveautitle' => new external_value(PARAM_TEXT, 'title of subject'),
        )));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function delete_item_parameters() {
        return new external_function_parameters(array(
            'itemid' => new external_value(PARAM_INT, 'id of item'),
        ));
    }

    /**
     * delete a submitted and wrong item
     * Deletes one user item if it is not graded already
     *
     * @ws-type-write
     * @param int $itemid
     */
    public static function delete_item($itemid) {
        global $CFG, $DB, $USER;

        // TODO: check exaport available
        // TODO: check allowd to delete

        $item = $DB->get_record('block_exaportitem', array('id' => $itemid, 'userid' => $USER->id));
        if ($item) {
            //check if the item is already graded
            $itemexample = $DB->get_record_sql("SELECT id, exacomp_record_id, itemid, status, MAX(timecreated) from {" . BLOCK_EXACOMP_DB_ITEM_MM . "} ie WHERE itemid = ?", array($itemid));
            if ($itemexample->status == 0) {
                //delete item and all associated content
                $DB->delete_records(BLOCK_EXACOMP_DB_ITEM_MM, array('id' => $itemexample->id));
                $DB->delete_records('block_exaportitem', array('id' => $itemid));
                if ($item->type == 'file') {
                    require_once $CFG->dirroot . '/blocks/exaport/inc.php';
                    block_exaport_file_remove($item);
                }

                $DB->delete_records('block_exaportitemcomm', array('itemid' => $itemid));
                $DB->delete_records('block_exaportviewblock', array('itemid' => $itemid));

                return array("success" => true);
            }
            throw new invalid_parameter_exception ('Not allowed; already graded');
        }

        throw new invalid_parameter_exception ('Not allowed');
    }

    /**
     * Returns desription of method return values
     *
     * @return external_single_structure
     */
    public static function delete_item_returns() {
        return new external_single_structure(array(
            'success' => new external_value(PARAM_BOOL, 'status'),
        ));
    }

    /**
     * This method is used for eLove
     *
     * @return external_function_parameters
     */
    public static function set_competence_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'descriptorid' => new external_value(PARAM_INT, 'id of descriptor'),
            'value' => new external_value(PARAM_INT, 'evaluation value'),
        ));
    }

    /**
     * Set a student evaluation for a particular competence
     * Set student evaluation
     *
     * @ws-type-write
     * @param int courseid
     * @param int descriptorid
     * @param int value
     */
    public static function set_competence($courseid, $descriptorid, $value) {
        global $DB, $USER;

        if (empty ($courseid) || empty ($descriptorid) || !isset ($value)) {
            throw new invalid_parameter_exception ('Parameter can not be empty');
        }

        static::validate_parameters(static::set_competence_parameters(), array(
            'courseid' => $courseid,
            'descriptorid' => $descriptorid,
            'value' => $value,
        ));

        static::require_can_access_course($courseid);

        $transaction = $DB->start_delegated_transaction(); // If an exception is thrown in the below code, all DB queries in this code will be rollback.

        $DB->delete_records('block_exacompcompuser', array(
            "userid" => $USER->id,
            "role" => 0,
            "compid" => $descriptorid,
            "courseid" => $courseid,
            "comptype" => BLOCK_EXACOMP_TYPE_DESCRIPTOR,
        ));
        if ($value > 0) {
            $DB->insert_record('block_exacompcompuser', array(
                "userid" => $USER->id,
                "role" => 0,
                "compid" => $descriptorid,
                "courseid" => $courseid,
                "comptype" => BLOCK_EXACOMP_TYPE_DESCRIPTOR,
                "reviewerid" => $USER->id,
                "value" => $value,
            ));
        }

        $transaction->allow_commit();

        return array(
            "success" => true,
        );
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function set_competence_returns() {
        return new external_single_structure(array(
            'success' => new external_value(PARAM_BOOL, 'status of success, either true (1) or false (0)'),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_item_for_example_parameters() {
        return new external_function_parameters(array(
            'userid' => new external_value(PARAM_INT, 'id of user'),
            'itemid' => new external_value(PARAM_INT, 'id of item'),
        ));
    }

    /**
     * Get Item
     * get subjects from one user for all his courses
     *
     * @ws-type-read
     * @return array of user courses
     */
    public static function get_item_for_example($userid, $itemid) {
        global $CFG, $DB, $USER;

        if ($userid == 0) {
            $userid = $USER->id;
        }

        static::validate_parameters(static::get_item_for_example_parameters(), array(
            'userid' => $userid,
            'itemid' => $itemid,
        ));

        static::require_can_access_user($userid);
        // TODO: can access item? can user access all items of that user

        $conditions = array(
            "id" => $itemid,
            "userid" => $userid,
        );
        $item = $DB->get_record("block_exaportitem", $conditions, 'id,userid,type,name,intro,url,courseid', MUST_EXIST);
        $itemexample = $DB->get_record(BLOCK_EXACOMP_DB_ITEM_MM, array(
            "itemid" => $itemid,
        ));

        if (!$itemexample) {
            throw new invalid_parameter_exception ('Item not found');
        }

        $courseid = static::find_courseid_for_example($itemexample->exacomp_record_id);
        static::require_can_access_example($itemexample->exacomp_record_id, $courseid);

        $item->file = "";
        $item->isimage = false;
        $item->filename = "";
        $item->effort = strip_tags($item->intro);
        $item->teachervalue = isset ($itemexample->teachervalue) ? $itemexample->teachervalue : 0;
        $item->studentvalue = isset ($itemexample->studentvalue) ? $itemexample->studentvalue : 0;
        $item->status = isset ($itemexample->status) ? $itemexample->status : 0;

        if ($item->type == 'file') {
            // TODO: move code into exaport\api
            require_once $CFG->dirroot . '/blocks/exaport/inc.php';

            $item->userid = $userid;
            if ($file = block_exaport_get_item_single_file($item)) {
                $item->file = ("{$CFG->wwwroot}/blocks/exaport/portfoliofile.php?access=portfolio/id/" . $userid . "&itemid=" . $itemid . "&wstoken=" . static::wstoken());
                $item->isimage = $file->is_valid_image();
                $item->filename = $file->get_filename();
            }
        }

        $item->studentcomment = '';
        $item->teachercomment = '';

        $itemcomments = api::get_item_comments($itemid);

        // teacher comment: last comment from any teacher in the course the item was submited
        foreach ($itemcomments as $itemcomment) {
            if (!$item->studentcomment && $userid == $itemcomment->userid) {
                $item->studentcomment = $itemcomment->entry;
            } else if (!$item->teachercomment) {
                if ($item->courseid && block_exacomp_is_teacher($item->courseid, $itemcomment->userid)) {
                    // dakora / exacomp teacher
                    $item->teachercomment = $itemcomment->entry;
                } else if (block_exacomp_is_external_trainer_for_student($itemcomment->userid, $item->userid)) {
                    // elove teacher
                    $item->teachercomment = $itemcomment->entry;
                }
            }
        }

        return $item;
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function get_item_for_example_returns() {
        return new external_single_structure(array(
            'id' => new external_value(PARAM_INT, 'id of item'),
            'name' => new external_value(PARAM_TEXT, 'title of item'),
            'type' => new external_value(PARAM_TEXT, 'type of item ENUM(note,file,link)'),
            'url' => new external_value(PARAM_TEXT, 'url'),
            'effort' => new external_value(PARAM_RAW, 'description of the effort'),
            'filename' => new external_value(PARAM_TEXT, 'title of item'),
            'file' => new external_value(PARAM_URL, 'file url'),
            'isimage' => new external_value(PARAM_BOOL, 'true if file is image'),
            'status' => new external_value(PARAM_INT, 'status of the submission'),
            'teachervalue' => new external_value(PARAM_INT, 'teacher grading'),
            'studentvalue' => new external_value(PARAM_INT, 'student grading'),
            'teachercomment' => new external_value(PARAM_TEXT, 'teacher comment'),
            'studentcomment' => new external_value(PARAM_TEXT, 'student comment'),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_competencies_for_upload_parameters() {
        return new external_function_parameters(array(
            'userid' => new external_value(PARAM_INT, 'id of user'),
        ));
    }

    /**
     * Get competencetree
     * Get all available competencies
     *
     * @ws-type-read
     * @param int subjectid
     * @return array of examples
     */
    public static function get_competencies_for_upload($userid) {
        global $USER;

        static::validate_parameters(static::get_competencies_for_upload_parameters(), array(
            'userid' => $userid,
        ));

        if (!$userid) {
            $userid = $USER->id;
        }
        static::require_can_access_user($userid);

        $structure = array();

        $courses = static::get_courses($userid);

        foreach ($courses as $course) {
            $tree = block_exacomp_get_competence_tree($course["courseid"]);

            foreach ($tree as $subject) {
                $elem_sub = new stdClass ();
                $elem_sub->subjectid = $subject->id;
                $elem_sub->subjecttitle = $subject->title;
                $elem_sub->topics = array();
                foreach ($subject->topics as $topic) {
                    $elem_topic = new stdClass ();
                    $elem_topic->topicid = $topic->id;
                    $elem_topic->topictitle = $topic->title;
                    $elem_topic->descriptors = array();
                    foreach ($topic->descriptors as $descriptor) {
                        $elem_desc = new stdClass ();
                        $elem_desc->descriptorid = $descriptor->id;
                        $elem_desc->descriptortitle = $descriptor->title;
                        $elem_topic->descriptors[] = $elem_desc;
                    }
                    $elem_sub->topics[] = $elem_topic;
                }
                $structure[] = $elem_sub;
            }
        }

        return $structure;
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function get_competencies_for_upload_returns() {
        return new external_multiple_structure(new external_single_structure(array(
            'subjectid' => new external_value(PARAM_INT, 'id of topic'),
            'subjecttitle' => new external_value(PARAM_TEXT, 'title of topic'),
            'topics' => new external_multiple_structure(new external_single_structure(array(
                'topicid' => new external_value(PARAM_INT, 'id of example'),
                'topictitle' => new external_value(PARAM_TEXT, 'title of example'),
                'descriptors' => new external_multiple_structure(new external_single_structure(array(
                    'descriptorid' => new external_value(PARAM_INT, 'id of example'),
                    'descriptortitle' => new external_value(PARAM_TEXT, 'title of example'),
                ))),
            ))),
        )));
    }

    /**
     * Returns description of method parameters
     * submit example for elove and diggr
     *
     * @return external_function_parameters
     */
    public static function submit_example_parameters() {
        return new external_function_parameters(array(
            'exampleid' => new external_value(PARAM_INT, 'exampleid'),
            'studentvalue' => new external_value(PARAM_INT, 'studentvalue'),
            'url' => new external_value(PARAM_URL, 'url'),
            'effort' => new external_value(PARAM_TEXT, 'effort'),
            'filename' => new external_value(PARAM_TEXT, 'filename, used to look up file and create a new one in the exaport file area'),
            'fileitemid' => new external_value(PARAM_INT, 'fileitemid, used to look up file and create a new one in the exaport file area'),
            'studentcomment' => new external_value(PARAM_TEXT, 'studentcomment'),
            'title' => new external_value(PARAM_TEXT, 'title'),
            'itemid' => new external_value(PARAM_INT, 'itemid'),
            'courseid' => new external_value(PARAM_INT, 'courseid'),
        ));
    }

    /**
     * Submit example
     * submit example for elove and diggr
     * Add item
     *
     * @ws-type-read
     * @param $exampleid
     * @param $studentvalue
     * @param $url
     * @param $effort
     * @param $filename
     * @param $studentcomment
     * @param $title
     * @param int $itemid
     * @param int $courseid
     * @return array of course subjects
     * @throws invalid_parameter_exception
     */
    public static function submit_example($exampleid, $studentvalue, $url, $effort, $filename, $fileitemid = 0, $studentcomment = null, $title = null, $itemid = 0, $courseid = 0) {
        global $CFG, $DB, $USER;

        static::validate_parameters(static::submit_example_parameters(),
            array('title' => $title, 'exampleid' => $exampleid, 'url' => $url, 'effort' => $effort, 'filename' => $filename, 'fileitemid' => $fileitemid, 'studentcomment' => $studentcomment, 'studentvalue' => $studentvalue,
                'itemid' => $itemid, 'courseid' => $courseid));

        if ($CFG->block_exaport_app_externaleportfolio) {
            // export to Mahara
            // TODO: besser als function call, nicht als include!
            if ($filename != '') {
                if ((include $CFG->dirroot . '/blocks/exacomp/upload_externalportfolio.php') == true) {
                    if ($maharaexport_success) {
                        $url = $result_querystring; // link to Mahara from upload_externalportfolio.php
                        // Type of item is 'url' if all OK;
                        $type = 'url';
                    }
                }
            }
        }
        if (!isset($type)) {
            $type = ($filename != '') ? 'file' : 'url';
        }

        //insert: if itemid == 0 OR status != 0
        $insert = true;
        if ($itemid != 0) {
            $itemexample = $DB->get_record(BLOCK_EXACOMP_DB_ITEM_MM, array('itemid' => $itemid));
            if ($itemexample->status == 0) {
                $insert = false;
            }
        }
        require_once $CFG->dirroot . '/blocks/exaport/inc.php';

        if ($insert) {
            //store item in eLOVE portfolio category
            $elove_category = block_exaport_get_user_category("eLOVE", $USER->id);

            if (!$elove_category) {
                $elove_category = block_exaport_create_user_category("eLOVE", $USER->id);
            }

            $exampletitle = $DB->get_field('block_exacompexamples', 'title', array('id' => $exampleid));
            $subjecttitle = block_exacomp_get_subjecttitle_by_example($exampleid);
            $subject_category = block_exaport_get_user_category($subjecttitle, $USER->id);

            if (!$subject_category) {
                $subject_category = block_exaport_create_user_category($subjecttitle, $USER->id, $elove_category->id);
            }

            $itemid = $DB->insert_record("block_exaportitem", array('userid' => $USER->id, 'name' => $exampletitle, 'url' => $url, 'intro' => $effort, 'type' => $type, 'timemodified' => time(), 'categoryid' => $subject_category->id));
            //autogenerate a published view for the new item
            $dbView = new stdClass();
            $dbView->userid = $USER->id;
            $dbView->name = $exampletitle;
            $dbView->timemodified = time();
            $dbView->layout = 1;
            // generate view hash
            do {
                $hash = substr(md5(microtime()), 3, 8);
            } while ($DB->record_exists("block_exaportview", array("hash" => $hash)));
            $dbView->hash = $hash;

            $dbView->id = $DB->insert_record('block_exaportview', $dbView);

            //share the view with teachers
            block_exaport_share_view_to_teachers($dbView->id);

            //add item to view
            $DB->insert_record('block_exaportviewblock', array('viewid' => $dbView->id, 'positionx' => 1, 'positiony' => 1, 'type' => 'item', 'itemid' => $itemid));

            //add the example competencies to the item, so that it is displayed in the exacomp moodle block
            $comps = $DB->get_records(BLOCK_EXACOMP_DB_DESCEXAMP, array('exampid' => $exampleid));

            foreach ($comps as $comp) {
                $DB->insert_record('block_exacompcompactiv_mm', array('compid' => $comp->descrid, 'comptype' => 0, 'eportfolioitem' => 1, 'activityid' => $itemid));
            }
        } else {
            $item = $DB->get_record('block_exaportitem', array('id' => $itemid));
            $item->name = $title;
            if ($url != '') {
                $item->url = $url;
            }
            $item->intro = $effort;
            $item->timemodified = time();

            if ($type == 'file') {
                block_exaport_file_remove($DB->get_record("block_exaportitem", array("id" => $itemid)));
            }

            $DB->update_record('block_exaportitem', $item);
        }

        //if a file is added we need to copy the file from the user/draft filearea to block_exaport/item_file with the itemid from above
        if ($type == "file") {
            $context = context_user::instance($USER->id);
            $fs = get_file_storage();
            try {
                $old = $fs->get_file($context->id, "user", "draft", $fileitemid, "/", $filename);

                if ($old) {
                    $file_record = array('contextid' => $context->id, 'component' => 'block_exaport', 'filearea' => 'item_file',
                        'itemid' => $itemid, 'filepath' => '/', 'filename' => $old->get_filename(),
                        'timecreated' => time(), 'timemodified' => time());
                    $fs->create_file_from_storedfile($file_record, $old->get_id());

                    $old->delete();
                }
            } catch (Exception $e) {
                //some problem with the file occured
            }
        }

        if ($insert) {
            $DB->insert_record(BLOCK_EXACOMP_DB_ITEM_MM, array('exacomp_record_id' => $exampleid, 'itemid' => $itemid, 'timecreated' => time(), 'status' => 0, 'studentvalue' => $studentvalue));
            if ($studentcomment != '') {
                $DB->insert_record('block_exaportitemcomm', array('itemid' => $itemid, 'userid' => $USER->id, 'entry' => $studentcomment, 'timemodified' => time()));
            }
        } else {
            $itemexample->timemodified = time();
            $itemexample->studentvalue = $studentvalue;
            $DB->update_record(BLOCK_EXACOMP_DB_ITEM_MM, $itemexample);
            if ($studentcomment != '') {
                $DB->delete_records('block_exaportitemcomm', array('itemid' => $itemid, 'userid' => $USER->id));
                $DB->insert_record('block_exaportitemcomm', array('itemid' => $itemid, 'userid' => $USER->id, 'entry' => $studentcomment, 'timemodified' => time()));
            }
        }
        // studentvalue has to be stored in exameval
        block_exacomp_set_user_example($USER->id, $exampleid, $courseid, BLOCK_EXACOMP_ROLE_STUDENT, $studentvalue);

        return array("success" => true, "itemid" => $itemid);
    }

    /**
     * Returns desription of method return values
     *submit example for elove and diggr
     *
     * @return external_single_structure
     */
    public static function submit_example_returns() {
        return new external_single_structure(array(
            'success' => new external_value(PARAM_BOOL, 'status'),
            'itemid' => new external_value(PARAM_INT, 'itemid'),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function create_or_update_example_parameters() {
        return new external_function_parameters(array(
            'exampleid' => new external_value(PARAM_INT, 'id of the example that is to be updated', VALUE_DEFAULT, -1),
            'name' => new external_value(PARAM_TEXT, 'title of example'),
            'description' => new external_value(PARAM_TEXT, 'description of example'),
            'timeframe' => new external_value(PARAM_TEXT, 'description of example', VALUE_DEFAULT, ''),
            'externalurl' => new external_value(PARAM_TEXT, '', VALUE_DEFAULT, 'wwww'),
            'comps' => new external_value(PARAM_TEXT, 'list of competencies, seperated by comma, or "freemat" if freematerial should be created', VALUE_DEFAULT, '0'),
            'fileitemids' => new external_value(PARAM_TEXT, 'fileitemids separated by comma', VALUE_DEFAULT, ''),
            'solutionfileitemid' => new external_value(PARAM_TEXT, 'fileitemid', VALUE_DEFAULT, ''),
            'taxonomies' => new external_value(PARAM_TEXT, 'list of taxonomies', VALUE_DEFAULT, ''),
            'newtaxonomy' => new external_value(PARAM_TEXT, 'new taxonomy to be created', VALUE_DEFAULT, ''),
            'courseid' => new external_value(PARAM_INT, 'courseid', VALUE_DEFAULT, 0),
            'filename' => new external_value(PARAM_TEXT, 'deprecated (old code for maybe elove?) filename, used to look up file and create a new one in the exaport file area', VALUE_DEFAULT, ''),
            'crosssubjectid' => new external_value(PARAM_INT, 'id of the crosssubject if it is a crosssubjectfile', VALUE_DEFAULT, -1),
            'activityid' => new external_value(PARAM_INT, 'id of related activity', VALUE_DEFAULT, 0),
            'is_teacherexample' => new external_value(PARAM_INT, 'is a teacher example?', VALUE_DEFAULT, 0),
        ));
    }

    /**
     * Create an example or update it
     * create example
     *
     * @ws-type-write
     *
     * @return array
     */
    public static function create_or_update_example($exampleid, $name, $description, $timeframe = '', $externalurl = null, $comps = null, $fileitemids = '', $solutionfileitemid = '', $taxonomies = '', $newtaxonomy = '', $courseid = 0,
        $filename = null,
        $crosssubjectid = -1, $activityid = 0, $is_teacherexample = 0) {
        if (empty ($name)) {
            throw new invalid_parameter_exception ('Parameter can not be empty');
        }

        static::validate_parameters(static::create_or_update_example_parameters(), array(
            'exampleid' => $exampleid,
            'name' => $name,
            'description' => $description,
            'timeframe' => $timeframe,
            'externalurl' => $externalurl,
            'comps' => $comps,
            'fileitemids' => $fileitemids,
            'solutionfileitemid' => $solutionfileitemid,
            'taxonomies' => $taxonomies,
            'newtaxonomy' => $newtaxonomy,
            'courseid' => $courseid,
            'filename' => $filename,
            'crosssubjectid' => $crosssubjectid,
            'activityid' => $activityid,
            'is_teacherexample' => $is_teacherexample,
        ));

        if (!get_config('exacomp', 'example_upload_global')) {
            // courseid HAS to be set because the admin setting says so. If there is no $courseid ==> error
            if ($courseid != 0) {
                $onlyForThisCourse = true;
            } else {
                throw new invalid_parameter_exception ('Parameter courseid can not be empty, because of example_upload_global setting set to false.');
            }
        }

        return self::create_or_update_example_common($exampleid, $name, $description, $timeframe, $externalurl, $comps, $fileitemids, $solutionfileitemid, $taxonomies, $newtaxonomy, $courseid, $filename, $crosssubjectid, $activityid,
            $is_teacherexample, 0, true, $onlyForThisCourse);
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function create_or_update_example_returns() {
        return new external_single_structure(array(
            'exampleid' => new external_value(PARAM_INT, 'id of created example'),
            'newtaxonomy' => new external_single_structure(array(
                'id' => new external_value(PARAM_INT, 'amount of total competencies'),
                'source' => new external_value(PARAM_TEXT, 'amount of reached competencies'),
                'title' => new external_value(PARAM_TEXT, 'amount of reached competencies'),
            )),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function diggrplus_create_or_update_example_parameters() {
        return new external_function_parameters(array(
            'exampleid' => new external_value(PARAM_INT, 'id of the example that is to be updated', VALUE_DEFAULT, -1),
            'name' => new external_value(PARAM_TEXT, 'title of example', VALUE_DEFAULT, ''),
            'description' => new external_value(PARAM_TEXT, 'description of example', VALUE_DEFAULT, ''),
            'timeframe' => new external_value(PARAM_TEXT, 'description of example', VALUE_DEFAULT, ''),
            'externalurl' => new external_value(PARAM_TEXT, '', VALUE_DEFAULT, 'wwww'),
            'comps' => new external_value(PARAM_TEXT, 'list of descriptorids, seperated by comma, or "freemat" if freematerial should be created', VALUE_DEFAULT, '0'),
            'taxonomies' => new external_value(PARAM_TEXT, 'list of taxonomies (comma seperated)', VALUE_DEFAULT, ''),
            'newtaxonomy' => new external_value(PARAM_TEXT, 'new taxonomy to be created', VALUE_DEFAULT, ''),
            'courseid' => new external_value(PARAM_INT, 'courseid', VALUE_DEFAULT, 0),
            'crosssubjectid' => new external_value(PARAM_INT, 'id of the crosssubject if it is a crosssubjectfile', VALUE_DEFAULT, -1),
            'fileitemids' => new external_value(PARAM_TEXT, 'fileitemids separated by comma, used to look up file and create a new one in the exaport file area', VALUE_DEFAULT, ''),
            'removefiles' => new external_value(PARAM_TEXT, 'fileindizes/pathnamehashes of the files that should be removed, separated by comma', VALUE_DEFAULT, ''),
            'solutionfileitemid' => new external_value(PARAM_TEXT, 'fileitemid for the solutionfile', VALUE_DEFAULT, ''),
            'activityid' => new external_value(PARAM_INT, 'id of related activity', VALUE_DEFAULT, 0),
            'is_teacherexample' => new external_value(PARAM_INT, 'is a teacher example?', VALUE_DEFAULT, 0),
        ));
    }

    /**
     * Create an example or update it
     * create example
     *
     * @ws-type-write
     *
     * @return array
     */
    public static function diggrplus_create_or_update_example($exampleid = -1, $name = null, $description = null, $timeframe = '', $externalurl = 'www', $comps = '0', $taxonomies = '', $newtaxonomy = '', $courseid = 0, $crosssubjectid = -1,
        $fileitemids = '', $removefiles = '', $solutionfileitemid = '', $activityid = 0, $is_teacherexample = 0) {
        global $COURSE; //TODO: calling this function with courseid=3... but $COURSE->id is 1. Why?

        static::validate_parameters(static::diggrplus_create_or_update_example_parameters(), array(
            'exampleid' => $exampleid,
            'name' => $name,
            'description' => $description,
            'timeframe' => $timeframe,
            'externalurl' => $externalurl,
            'comps' => $comps,
            'solutionfileitemid' => $solutionfileitemid,
            'taxonomies' => $taxonomies,
            'newtaxonomy' => $newtaxonomy,
            'courseid' => $courseid,
            'crosssubjectid' => $crosssubjectid,
            'fileitemids' => $fileitemids,
            'removefiles' => $removefiles,
            'solutionfileitemid' => $solutionfileitemid,
            'activityid' => $activityid,
            'is_teacherexample' => $is_teacherexample,
        ));

        if (!get_config('exacomp', 'example_upload_global') && !$courseid) {
            // courseid HAS to be set because the admin setting says so. If there is no $courseid ==> error
            throw new invalid_parameter_exception ('Parameter courseid can not be empty, because of example_upload_global setting set to false.');
        }
        $onlyForThisCourse = !!$courseid;

        $example = self::create_or_update_example_common($exampleid, $name, $description, $timeframe, $externalurl, $comps, $fileitemids, $solutionfileitemid, $taxonomies, $newtaxonomy, $courseid, null, $crosssubjectid, $activityid,
            $is_teacherexample, $removefiles, null, $onlyForThisCourse);

        return array("success" => true, "exampleid" => $example["exampleid"]);
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function diggrplus_create_or_update_example_returns() {
        return new external_single_structure(array(
            'exampleid' => new external_value(PARAM_INT, 'id of created example'),
            'success' => new external_value(PARAM_BOOL, 'status'),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function diggrplus_grade_descriptor_parameters() {
        return new external_function_parameters(array(
            'descriptorid' => new external_value(PARAM_INT, 'id of descriptor'),
            'grading' => new external_value(PARAM_INT, 'grade for this descriptor'),
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'userid' => new external_value(PARAM_INT, 'id of user, if 0 current user'),
            'role' => new external_value(PARAM_INT, 'user role (0 == student, 1 == teacher)'),
            'subjectid' => new external_value(PARAM_INT, 'subjectid', VALUE_DEFAULT, -1),
        ));
    }

    /**
     * Grade a descriptor
     *
     * @ws-type-write
     * @param $descriptorid
     * @param $grading
     * @param $courseid
     * @param $userid
     * @param $role
     * @param $subjectid
     * @return array
     * @throws invalid_parameter_exception
     *
     */
    public static function diggrplus_grade_descriptor($descriptorid, $grading, $courseid, $userid, $role, $subjectid) {
        global $DB, $USER;

        if (empty ($descriptorid) || empty ($grading)) {
            throw new invalid_parameter_exception ('Parameter can not be empty');
        }

        static::validate_parameters(static::diggrplus_grade_descriptor_parameters(), array(
            'descriptorid' => $descriptorid,
            'grading' => $grading,
            'courseid' => $courseid,
            'userid' => $userid,
            'role' => $role,
            'subjectid' => $subjectid,
        ));

        if ($userid == 0 && $role == BLOCK_EXACOMP_ROLE_STUDENT) {
            $userid = $USER->id;
        } else {
            if ($userid == 0) {
                throw new invalid_parameter_exception ('Userid can not be 0 for teacher grading');
            }
        }

        static::require_can_access_course_user($courseid, $userid);

        $customdata = ['block' => 'exacomp', 'app' => 'diggrplus', 'courseid' => $courseid, 'descriptorid' => $descriptorid, 'userid' => $USER->id];
        block_exacomp_set_user_competence($userid, $descriptorid, BLOCK_EXACOMP_TYPE_DESCRIPTOR, $courseid, $role, $grading, null, $subjectid, true, [
            'notification_customdata' => $customdata,
        ]);

        return array(
            "success" => true,
        );
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function diggrplus_grade_descriptor_returns() {
        return new external_single_structure(array(
            'success' => new external_value(PARAM_BOOL, 'true if grading was successful'),
        ));
    }

    /**
     * @return external_function_parameters
     */
    public static function diggrplus_grade_element_parameters() {
        return new external_function_parameters(array(
            'elementid' => new external_value(PARAM_INT, 'id of element'),
            'type' => new external_value(PARAM_TEXT, 'example, descriptor, topic'),
            'grading' => new external_value(PARAM_INT, 'grade for this element'),
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'userid' => new external_value(PARAM_INT, 'id of user, if 0 current user'),
            'role' => new external_value(PARAM_INT, 'user role (0 == student, 1 == teacher)'),
        ));
    }

    /**
     * Grade a element
     *
     * @ws-type-write
     * @param $descriptorid
     * @param $grading
     * @param $courseid
     * @param $userid
     * @param $role
     * @param $subjectid
     * @return array
     * @throws invalid_parameter_exception
     * @deprecated on 2023-05-29 -> can be deleted later
     */
    public static function diggrplus_grade_element($elementid, $type, $grading, $courseid, $userid, $role) {
        global $USER;

        if (empty ($elementid) || is_null($grading)) {
            throw new invalid_parameter_exception ('Parameter can not be empty');
        }

        if ($type == 'topic') {
            $comptype = BLOCK_EXACOMP_TYPE_TOPIC;
        } else if ($type == 'descriptor') {
            $comptype = BLOCK_EXACOMP_TYPE_DESCRIPTOR;
        } else if ($type == 'example') {
            $comptype = BLOCK_EXACOMP_TYPE_EXAMPLE;
        } else {
            throw new invalid_parameter_exception("type '$type' not supported");
        }

        return static::diggrplus_grade_competency($elementid, $comptype, $grading, $courseid, $userid, $role);
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function diggrplus_grade_element_returns() {
        return static::diggrplus_grade_competency_returns();
    }

    /**
     * @return external_function_parameters
     */
    public static function diggrplus_grade_competency_parameters() {
        return new external_function_parameters(array(
            'compid' => new external_value(PARAM_INT, 'competency id'),
            'comptype' => new external_value(PARAM_INT, 'competency type'),
            'grading' => new external_value(PARAM_INT, 'grade for this element'),
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'userid' => new external_value(PARAM_INT, 'id of user, if 0 current user'),
            'role' => new external_value(PARAM_INT, 'user role (0 == student, 1 == teacher)'),
        ));
    }

    /**
     * Grade a element
     *
     * @ws-type-write
     * @param $descriptorid
     * @param $grading
     * @param $courseid
     * @param $userid
     * @param $role
     * @param $subjectid
     * @return array
     * @throws invalid_parameter_exception
     *
     */
    public static function diggrplus_grade_competency($compid, $comptype, $grading, $courseid, $userid, $role) {
        global $DB, $USER;

        static::validate_parameters(static::diggrplus_grade_competency_parameters(), array(
            'compid' => $compid,
            'comptype' => $comptype,
            'grading' => $grading,
            'courseid' => $courseid,
            'userid' => $userid,
            'role' => $role,
        ));

        if ($userid == 0 && $role == BLOCK_EXACOMP_ROLE_STUDENT) {
            $userid = $USER->id;
        } else {
            if ($userid == 0) {
                throw new invalid_parameter_exception ('Userid can not be 0 for teacher grading');
            }
        }

        static::require_can_access_course_user($courseid, $userid);

        if ($comptype == BLOCK_EXACOMP_TYPE_DESCRIPTOR) {
            $customdata = ['block' => 'exacomp', 'app' => 'diggrplus', 'courseid' => $courseid, 'descriptorid' => $compid, 'userid' => $USER->id];
        } else {
            $customdata = [];
        }

        block_exacomp_set_user_competence($userid, $compid, $comptype, $courseid, $role, $grading, null, -1, true, [
            'notification_customdata' => $customdata,
        ]);

        if ($role == BLOCK_EXACOMP_ROLE_STUDENT && $comptype == BLOCK_EXACOMP_TYPE_EXAMPLE) {
            $item = static::get_example_item($USER->id, $compid);
            if ($item) {
                $studentvalue = $grading;
                $itemid = $item->id;

                // example bewertung auch in item_mm speichern
                $item_comp_mm = $DB->get_record(BLOCK_EXACOMP_DB_ITEM_MM, array('itemid' => $itemid));

                if (!$item_comp_mm) {
                    $DB->insert_record(BLOCK_EXACOMP_DB_ITEM_MM, array('exacomp_record_id' => $compid, 'itemid' => $itemid, 'timecreated' => time(), 'status' => 0, 'competence_type' => $comptype, 'studentvalue' => $studentvalue));
                } else {
                    $item_comp_mm->datemodified = time();
                    $item_comp_mm->studentvalue = $studentvalue; // TODO: -1 is not good, solve it differently
                    $DB->update_record(BLOCK_EXACOMP_DB_ITEM_MM, $item_comp_mm);
                }
            } else {
                // TODO: what to do if there is no item yet?!?
            }
        }

        return array(
            "success" => true,
        );
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function diggrplus_grade_competency_returns() {
        return new external_single_structure(array(
            'success' => new external_value(PARAM_BOOL, 'true if grading was successful'),
        ));
    }

    /**
     * @return external_function_parameters
     */
    public static function diggrplus_get_all_competency_gradings_parameters() {
        return new external_function_parameters(array(
            'compid' => new external_value(PARAM_INT, 'competence id'),
            'comptype' => new external_value(PARAM_INT, 'type of competence: descriptor, topic, subject'),
            'userid' => new external_value(PARAM_INT, ''),
        ));
    }

    /**
     * Get all gradings in all courses
     *
     * @ws-type-write
     */
    public static function diggrplus_get_all_competency_gradings($compid, $comptype, $userid) {
        static::validate_parameters(static::diggrplus_get_all_competency_gradings_parameters(), array(
            'compid' => $compid,
            'comptype' => $comptype,
            'userid' => $userid,
        ));

        if (!block_exacomp_is_teacher_in_any_course()) {
            throw new \moodle_exception('not a teacher');
        }

        if ($comptype == BLOCK_EXACOMP_TYPE_EXAMPLE) {
            throw new \moodle_exception('example needs a different logic');
        }

        $evals = g::$DB->get_records(BLOCK_EXACOMP_DB_COMPETENCES, ['compid' => $compid, 'comptype' => $comptype, 'userid' => $userid, 'role' => BLOCK_EXACOMP_ROLE_TEACHER], 'timestamp DESC');

        foreach ($evals as $key => $eval) {
            $user = g::$DB->get_record('user', ['id' => $eval->reviewerid]);
            $course = g::$DB->get_record('course', ['id' => $eval->courseid]);

            if ($user) {
                $userpicture = new user_picture($user);
                $userpicture->size = 1; // Size f1.

                $reviewer = (object)[
                    'userid' => $user->id,
                    'fullname' => fullname($user),
                    'profileimageurl' => $userpicture->get_url(g::$PAGE)->out(false),
                ];

            } else {
                $reviewer = null;
            }

            $evals[$key] = [
                'id' => $eval->id,
                'reviewer' => $reviewer,
                'courseid' => $course ? $course->id : 0,
                'coursefullname' => $course ? $course->fullname : '',
                'grading' => $eval->value,
                'timestamp' => $eval->timestamp,
            ];
        }

        return $evals;
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function diggrplus_get_all_competency_gradings_returns() {
        return new external_multiple_structure(new external_single_structure(array(
            'id' => new external_value(PARAM_INT, 'id of grading'),
            'reviewer' => new external_single_structure(array(
                'userid' => new external_value(PARAM_INT, ''),
                'fullname' => new external_value(PARAM_TEXT, ''),
                'profileimageurl' => new external_value(PARAM_TEXT, ''),
            ), 'reviewing teacher', VALUE_OPTIONAL),
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'coursefullname' => new external_value(PARAM_TEXT, 'id of course'),
            'grading' => new external_value(PARAM_INT, 'grade for this element'),
            'timestamp' => new external_value(PARAM_INT, 'timemodified'),
        )));
    }

    public static function diggrplus_msteams_import_students_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course to import to'),
            'access_token' => new external_value(PARAM_TEXT, 'msteams access token'),
            'teamid' => new external_value(PARAM_TEXT, 'uuid of msteams team to import from'),
        ));
    }

    /**
     * @ws-type-write
     */
    public static function diggrplus_msteams_import_students($courseid, $access_token, $teamid) {
        global $DB, $USER, $CFG;

        require_once $CFG->dirroot . '/lib/enrollib.php';
        require_once $CFG->dirroot . '/user/lib.php';

        static::validate_parameters(static::diggrplus_msteams_import_students_parameters(), array(
            'courseid' => $courseid,
            'access_token' => $access_token,
            'teamid' => $teamid,
        ));

        $access_token_parts = explode('.', $access_token);
        $tenantId = json_decode(base64_decode($access_token_parts[1]))->tid;

        block_exacomp_require_teacher($courseid);

        $o365_request = function($path) use ($access_token) {
            $ch = curl_init('https://graph.microsoft.com/v1.0/' . $path);

            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $access_token,
            ]);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

            $result = curl_exec($ch);
            $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            curl_close($ch);

            if ($status_code != 200) {
                throw new moodle_exception('request failed, status_code: ' . $status_code);
            }

            $result = json_decode($result);

            return $result;
        };

        $result = $o365_request('groups/' . $teamid . '/members');
        $resultOwners = $o365_request('groups/' . $teamid . '/owners');

        if (!is_array($result->value)) {
            throw new moodle_exception('result is not array');
        }
        if (!is_array($resultOwners->value)) {
            throw new moodle_exception('resultOwners is not array');
        }

        $ownerIds = array_map(function($o) {
            return $o->id;
        }, $resultOwners->value);

        $importedCount = 0;

        foreach ($result->value as $teamsUser) {
            $email = $teamsUser->userPrincipalName;
            // uppercase email addresses on hak-steyr
            $email = strtolower($email);

            $user = null;

            if (!get_config('exacomp', 'sso_create_users')) {
                // new logic mit o365 user verknüpfen
                // ggf ist der user schon verknüpft und wir nehmen diesen
                $usermap = $DB->get_record('block_exacomp_usermap', ['provider' => 'o365', 'tenant_id' => $tenantId, 'remoteuserid' => $email]);
                if ($usermap) {
                    $user = $DB->get_record('user', ['id' => $usermap->userid]);
                }
            }

            if (!$user) {
                $user = $DB->get_record('user', ['email' => $email]);
            }
            if (!$user) {
                // create the user
                $user = array(
                    'username' => $email,
                    'password' => generate_password(20),
                    'firstname' => $teamsUser->givenName,
                    'lastname' => $teamsUser->surname,
                    'description' => 'diggr-plus: imported from msteams',
                    'email' => $email,
                    'suspended' => 0,
                    'mnethostid' => $CFG->mnet_localhost_id,
                    'confirmed' => 1,
                );

                $userid = user_create_user($user);
            } else {
                $userid = $user->id;

                $context = context_course::instance($courseid);
                if (is_enrolled($context, $user)) {
                    continue;
                }
            }

            // enrol the user
            $enrol = enrol_get_plugin("manual"); //enrolment = manual
            $instances = enrol_get_instances($courseid, true);
            $manualinstance = null;
            foreach ($instances as $instance) {
                if ($instance->enrol == "manual") {
                    $manualinstance = $instance;
                    break;
                }
            }

            if (in_array($teamsUser->id, $ownerIds)) {
                $roleid = 3; // "editingteacher" role
            } else {
                $roleid = 5; //The roleid of "student" is 5 in mdl_role table
            }

            $enrol->enrol_user($manualinstance, $userid, $roleid);

            $importedCount++;
        }

        return array(
            "total_count" => count($result->value),
            "imported_count" => $importedCount,
        );
    }

    /**
     * @return external_multiple_structure
     */
    public static function diggrplus_msteams_import_students_returns() {
        return new external_single_structure(array(
            'total_count' => new external_value(PARAM_INT, 'number of users in the team'),
            'imported_count' => new external_value(PARAM_INT, 'number of newly imported users'),
        ));
    }

    public static function diggrplus_msteams_get_access_token_parameters() {
        return new external_function_parameters(array(
            'tenantid' => new external_value(PARAM_TEXT),
            'authentication_token' => new external_value(PARAM_TEXT, 'msteams authentication token'),
        ));
    }

    /**
     * @ws-type-write
     */
    public static function diggrplus_msteams_get_access_token($tenantid, $authentication_token) {
        global $DB, $USER, $CFG;

        static::validate_parameters(static::diggrplus_msteams_get_access_token_parameters(), array(
            'tenantid' => $tenantid,
            'authentication_token' => $authentication_token,
        ));

        $ch = curl_init('https://login.microsoftonline.com/' . $tenantid . '/oauth2/v2.0/token');
        curl_setopt($ch, CURLOPT_POST, 1);

        $client_id = get_config("exacomp", 'msteams_client_id');
        $client_secret = get_config("exacomp", 'msteams_client_secret');
        if (!$client_id) {
            throw new moodle_exception('client_id not set');
        }
        if (!$client_secret) {
            throw new moodle_exception('client_secret not set');
        }

        $postdata = "grant_type=urn:ietf:params:oauth:grant-type:jwt-bearer" .
            "&client_id=" . $client_id .
            "&client_secret=" . $client_secret .
            "&scope=https://graph.microsoft.com/groupmember.read.all" .
            "&requested_token_use=on_behalf_of" .
            "&assertion=" . $authentication_token;

        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: application/json',
        ]);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

        $result = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($status_code != 200) {
            throw new moodle_exception('request failed, status_code: ' . $status_code);
        }

        $result = json_decode($result);
        if (!$result->access_token) {
            throw new moodle_exception('got empty result');
        }

        $access_token = $result->access_token;

        return array(
            "access_token" => $access_token,
        );
    }

    /**
     * @return external_multiple_structure
     */
    public static function diggrplus_msteams_get_access_token_returns() {
        return new external_single_structure(array(
            'access_token' => new external_value(PARAM_TEXT),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function grade_item_parameters() {
        return new external_function_parameters(array(
            'userid' => new external_value(PARAM_INT, 'id of user'),
            'value' => new external_value(PARAM_INT, 'value for grading'),
            'status' => new external_value(PARAM_INT, 'status'),
            'comment' => new external_value(PARAM_TEXT, 'comment of grading'),
            'itemid' => new external_value(PARAM_INT, 'id of item'),
            'comps' => new external_value(PARAM_TEXT, 'comps for example - positive grading'),
            'courseid' => new external_value(PARAM_INT, 'if of course'),
        ));
    }

    /**
     * Grade an item
     * grade an item
     *
     * @ws-type-write
     * @param $userid
     * @param $value
     * @param $status
     * @param $comment
     * @param $itemid
     * @param $comps
     * @param $courseid
     * @return array
     * @throws invalid_parameter_exception
     *
     */
    public static function grade_item($userid, $value, $status, $comment, $itemid, $comps, $courseid) {
        global $DB, $USER;

        if (empty ($userid) || empty ($value) || empty ($comment) || empty ($itemid) || empty ($courseid)) {
            throw new invalid_parameter_exception ('Parameter can not be empty');
        }

        static::validate_parameters(static::grade_item_parameters(), array(
            'userid' => $userid,
            'value' => $value,
            'status' => $status,
            'comment' => $comment,
            'itemid' => $itemid,
            'comps' => $comps,
            'courseid' => $courseid,
        ));

        if (!$userid) {
            $userid = $USER->id;
        }
        static::require_can_access_user($userid);

        // insert into block_exacompitem_mm
        $update = $DB->get_record(BLOCK_EXACOMP_DB_ITEM_MM, array(
            'itemid' => $itemid,
        ));

        $exampleid = $update->exacomp_record_id;

        $update->itemid = $itemid;
        $update->datemodified = time();
        $update->teachervalue = $value;
        $update->status = $status;

        $DB->update_record(BLOCK_EXACOMP_DB_ITEM_MM, $update);
        // if the grading is good, tick the example in exacomp
        $exameval = $DB->get_record('block_exacompexameval', array(
            'exampleid' => $exampleid,
            'courseid' => $courseid,
            'studentid' => $userid,
        ));
        if ($exameval) {
            $exameval->teacher_evaluation = 1;
            $DB->update_record('block_exacompexameval', $exameval);
        } else {
            $DB->insert_record('block_exacompexameval', array(
                'exampleid' => $exampleid,
                'courseid' => $courseid,
                'studentid' => $userid,
                'teacher_evaluation' => 1,
            ));
        }

        $insert = new stdClass ();
        $insert->itemid = $itemid;
        $insert->userid = $USER->id;
        $insert->entry = $comment;
        $insert->timemodified = time();

        $DB->delete_records('block_exaportitemcomm', array(
            'itemid' => $itemid,
            'userid' => $USER->id,
        ));
        $DB->insert_record('block_exaportitemcomm', $insert);

        // get all available descriptors and unset them who are not received via web service
        $descriptors_exam_mm = $DB->get_records(BLOCK_EXACOMP_DB_DESCEXAMP, array(
            'exampid' => $exampleid,
        ));

        $descriptors = explode(',', $comps);

        $unset_descriptors = array();
        foreach ($descriptors_exam_mm as $descr_examp) {
            if (!in_array($descr_examp->descrid, $descriptors)) {
                $unset_descriptors[] = $descr_examp->descrid;
            }
        }

        // set positive graded competencies
        foreach ($descriptors as $descriptor) {
            if ($descriptor != 0) {
                $entry = block_exacomp_get_comp_eval($courseid, BLOCK_EXACOMP_ROLE_TEACHER, $userid, BLOCK_EXACOMP_TYPE_DESCRIPTOR, $descriptor);

                if ($entry) {
                    $entry->reviewerid = $USER->id;
                    $entry->value = 1;
                    $entry->timestamp = time();
                    $DB->update_record(BLOCK_EXACOMP_DB_COMPETENCES, $entry);
                } else {
                    $insert = new stdClass ();
                    $insert->userid = $userid;
                    $insert->compid = $descriptor;
                    $insert->reviewerid = $USER->id;
                    $insert->role = BLOCK_EXACOMP_ROLE_TEACHER;
                    $insert->courseid = $courseid;
                    $insert->value = 1;
                    $insert->timestamp = time();

                    $DB->insert_record(BLOCK_EXACOMP_DB_COMPETENCES, $insert);
                }
            }
        }

        // set negative graded competencies
        foreach ($unset_descriptors as $descriptor) {
            $entry = block_exacomp_get_comp_eval($courseid, BLOCK_EXACOMP_ROLE_TEACHER, $userid, BLOCK_EXACOMP_TYPE_DESCRIPTOR, $descriptor);

            if ($entry) {
                $entry->reviewerid = $USER->id;
                $entry->value = 0;
                $entry->timestamp = time();
                $DB->update_record(BLOCK_EXACOMP_DB_COMPETENCES, $entry);
            } else {
                $insert = new stdClass ();
                $insert->userid = $userid;
                $insert->compid = $descriptor;
                $insert->reviewerid = $USER->id;
                $insert->role = BLOCK_EXACOMP_ROLE_TEACHER;
                $insert->courseid = $courseid;
                $insert->value = 0;
                $insert->timestamp = time();

                $DB->insert_record(BLOCK_EXACOMP_DB_COMPETENCES, $insert);
            }
        }

        return array(
            "success" => true,
        );
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function grade_item_returns() {
        return new external_single_structure(array(
            'success' => new external_value(PARAM_BOOL, 'true if grading was successful'),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_user_examples_parameters() {
        return new external_function_parameters(array());
    }

    /**
     * get examples created by a specific user
     * grade an item
     *
     * @ws-type-read
     * @return array
     * @throws invalid_parameter_exception
     */
    public static function get_user_examples() {
        global $DB, $USER;

        static::validate_parameters(static::get_user_examples_parameters(), array());

        $subjects = static::get_subjects_for_user($USER->id);

        $examples = array();
        foreach ($subjects as $subject) {
            $topics = static::get_examples_for_subject($subject->subjectid, $subject->courseid, 0);
            foreach ($topics as $topic) {
                foreach ($topic->examples as $example) {
                    if ($example->example_creatorid == $USER->id) {
                        $elem = new stdClass ();
                        $elem->exampleid = $example->exampleid;
                        $elem->exampletitle = static::custom_htmltrim($example->example_title);
                        $elem->exampletopicid = $topic->topicid;
                        $items_examp = $DB->get_records(BLOCK_EXACOMP_DB_ITEM_MM, array(
                            'exacomp_record_id' => $example->exampleid,
                        ));
                        $items = array();
                        foreach ($items_examp as $item_examp) {
                            $item_db = $DB->get_record('block_exaportitem', array(
                                'id' => $item_examp->itemid,
                            ));
                            if ($item_db->userid == $USER->id) {
                                $items[] = $item_examp;
                            }
                        }
                        if (!empty ($items)) {
                            // check for current
                            $current_timestamp = 0;
                            foreach ($items as $item) {
                                if ($item->timecreated > $current_timestamp) {
                                    $elem->example_status = $item->status;
                                }
                            }
                        } else {
                            $elem->example_status = -1;
                        }

                        $examples[$example->exampleid] = $elem;
                    }
                }
            }
        }

        return $examples;
        // return array();
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function get_user_examples_returns() {
        return new external_multiple_structure(new external_single_structure(array(
            'exampleid' => new external_value(PARAM_INT, 'id of example'),
            'exampletitle' => new external_value(PARAM_TEXT, 'title of example'),
            'example_status' => new external_value(PARAM_INT, 'status of example'),
            'exampletopicid' => new external_value(PARAM_INT, 'topic id where example belongs to'),
        )));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_user_profile_parameters() {
        return new external_function_parameters(array(
            'userid' => new external_value(PARAM_INT, 'id of user'),
        ));
    }

    /**
     * get a list of courses with their competencies
     *
     * @ws-type-read
     * @return array of user courses
     */
    public static function get_user_profile($userid) {
        global $CFG, $DB, $USER;
        require_once($CFG->dirroot . "/lib/enrollib.php");

        static::validate_parameters(static::get_user_profile_parameters(), array(
            'userid' => $userid,
        ));

        if (!$userid) {
            $userid = $USER->id;
        }
        static::require_can_access_user($userid);

        $user = $DB->get_record('user', array(
            'id' => $userid,
        ));

        $grading = (block_exacomp_is_elove_student_self_assessment_enabled()) ? "student" : "teacher";

        // test:
        /*
		$data = (object)[];
		$walker = function($item) use (&$walker, &$data) {
			$subs = $item->get_subs();
			array_walk($subs, $walker);

			if ($item instanceof \block_exacomp\descriptor) {
				$data->descriptors[$item->id] = $item->id;
				foreach ($item->examples as $example) {
					$data->examples[$example->id] = $example->id;
				}
			} else {
				// $item->achieved = null;
			}
		};

		$tree = block_exacomp\db_layer_all_user_courses::create($userid)->get_subjects();

		array_walk($tree, $walker);
		var_dump($data);
		*/

        // total data
        $total_competencies = 0;
        $total_examples = array();
        $total_user_competencies = 0;
        $total_user_examples = array();

        $courses = static::get_courses($userid);

        $subjects_res = array();
        foreach ($courses as $course) {

            $subjects = block_exacomp_get_subjects_by_course($course["courseid"]);
            $coursesettings = block_exacomp_get_settings_by_course($course['courseid']);
            $user = block_exacomp_get_user_information_by_course($user, $course['courseid']);
            $cm_mm = block_exacomp_get_course_module_association($course['courseid']);

            foreach ($subjects as $subject) {
                $subject_total_competencies = 0;
                $subject_total_examples = 0;
                $subject_reached_competencies = 0;
                $subject_reached_examples = 0;
                $subject_topics = array();

                $topics = block_exacomp_get_topics_by_subject($course['courseid'], $subject->id);
                foreach ($topics as $topic) {
                    $topic_total_competencies = 0;
                    $topic_total_examples = 0;
                    $topic_reached_competencies = 0;
                    $topic_reached_examples = 0;

                    // topics zählen wir vorerst nicht, weil get_user_profile für elove ist
                    //if ($coursesettings->show_all_descriptors || ($coursesettings->uses_activities && isset ( $cm_mm->topics[$topic->id] )))
                    //	$topic_total_competencies ++;

                    //if (isset($user->topics->{$grading}[$topic->id])) {
                    //	$topic_reached_competencies++;
                    //}

                    $descriptors = block_exacomp_get_descriptors_by_topic($course['courseid'], $topic->id, false, true);
                    foreach ($descriptors as $descriptor) {
                        if ($coursesettings->show_all_descriptors || ($coursesettings->uses_activities && isset ($cm_mm->competencies[$descriptor->id]))) {
                            $topic_total_competencies++;
                        }

                        if (isset($user->competencies->{$grading}[$descriptor->id])) {
                            $topic_reached_competencies++;
                        }

                        $examples = $DB->get_records_sql("SELECT de.id as deid, e.id, e.title, e.externalurl,
						e.externalsolution, e.externaltask, e.completefile, e.description, e.creatorid, e.source
						FROM {" . BLOCK_EXACOMP_DB_EXAMPLES . "} e
						JOIN {" . BLOCK_EXACOMP_DB_DESCEXAMP . "} de ON e.id=de.exampid AND de.descrid=? ", array(
                            $descriptor->id,
                        ));

                        foreach ($examples as $example) {
                            if ($example->source == BLOCK_EXACOMP_EXAMPLE_SOURCE_USER) {
                                // ignore source=user for now
                                continue;
                            }

                            $taxonomies = block_exacomp_get_taxonomies_by_example($example);
                            if (!empty($taxonomies)) {
                                $taxonomy = reset($taxonomies);

                                $example->taxid = $taxonomy->id;
                                $example->tax = static::custom_htmltrim($taxonomy->title);
                            } else {
                                $example->taxid = null;
                                $example->tax = "";
                            }
                            if (!in_array($example->id, $total_examples)) {
                                $total_examples[] = $example->id;
                                $topic_total_examples++;

                                // CHECK FOR USER EXAMPLES
                                $sql = 'select * from {' . BLOCK_EXACOMP_DB_ITEM_MM . '} ie
										JOIN {block_exaportitem} i ON i.id = ie.itemid
										WHERE ie.exacomp_record_id = ? AND i.userid=? AND ie.status=2';
                                if ($DB->get_records_sql($sql, array(
                                    $example->id,
                                    $userid,
                                ))
                                ) {
                                    $total_user_examples[] = $example->id;
                                    $topic_reached_examples++;
                                }
                            }
                        }
                    }
                    $subject_total_competencies += $topic_total_competencies;
                    $subject_reached_competencies += $topic_reached_competencies;
                    $subject_total_examples += $topic_total_examples;
                    $subject_reached_examples += $topic_reached_examples;

                    if (!array_key_exists($topic->id, $subject_topics)) {
                        $elem = new stdClass ();
                        $elem->title = static::custom_htmltrim($topic->title);
                        $elem->competencies = array(
                            "total" => $topic_total_competencies,
                            "reached" => $topic_reached_competencies,
                        );
                        $elem->examples = array(
                            "total" => $topic_total_examples,
                            "reached" => $topic_reached_examples,
                        );
                        $subject_topics[] = $elem;
                    }
                }

                if (!array_key_exists($subject->id, $subjects_res)) {
                    $elem = new stdClass ();
                    $elem->title = static::custom_htmltrim($subject->title);
                    $elem->competencies = array(
                        "total" => $subject_total_competencies,
                        "reached" => $subject_reached_competencies,
                    );
                    $elem->examples = array(
                        "total" => $subject_total_examples,
                        "reached" => $subject_reached_examples,
                    );
                    $elem->topics = $subject_topics;

                    $subjects_res[] = $elem;
                }

                $total_competencies += $subject_total_competencies;
                $total_user_competencies += $subject_reached_competencies;
            }
        }

        $defaultdata = array();
        $defaultdata['user'] = array(
            "competencies" => array(
                "total" => $total_competencies,
                "reached" => $total_user_competencies,
            ),
            "examples" => array(
                "total" => count($total_examples),
                "reached" => count($total_user_examples),
            ),
        );
        $defaultdata['subjects'] = array();

        foreach ($subjects_res as $subject_res) {
            $cursubject = array(
                "title" => static::custom_htmltrim($subject_res->title),
                "data" => array(
                    "competencies" => $subject_res->competencies,
                    "examples" => $subject_res->examples,
                ),
                "topics" => array(),
            );

            foreach ($subject_res->topics as $topic) {
                $cursubject["topics"][] = array(
                    "title" => static::custom_htmltrim($topic->title),
                    "data" => array(
                        "competencies" => $topic->competencies,
                        "examples" => $topic->examples,
                    ),
                );
            }

            $defaultdata['subjects'][] = $cursubject;
        }

        /*
		print_r($total_examples);
		print_r($defaultdata);
		exit;
		*/

        return $defaultdata;
    }

    private static function get_user_list_info($user, $type) {
        $userid = $user->userid;

        $all_examples_reached = g::$DB->get_records_sql_menu("
			select distinct ie.exacomp_record_id, ie.exacomp_record_id as tmp from {" . BLOCK_EXACOMP_DB_ITEM_MM . "} ie
			JOIN {block_exaportitem} i ON i.id = ie.itemid
			WHERE i.userid=? AND ie.status=2
		", [$userid]);

        $all_examples_submitted = g::$DB->get_records_sql_menu("
			select distinct ie.exacomp_record_id, ie.exacomp_record_id as tmp from {" . BLOCK_EXACOMP_DB_ITEM_MM . "} ie
			JOIN {block_exaportitem} i ON i.id = ie.itemid
			WHERE i.userid=?
		", [$userid]);

        $all_examples = [];

        // old, slow
        /*
		// $data = (object)[];

		// find all_user_examples
		$walker = function($item) use (&$walker, &$all_examples) {
			if ($item instanceof \block_exacomp\descriptor) {
				foreach ($item->examples as $example) {
					$all_examples[$example->id] = $example->id;
				}

				// skip child descriptors for now, so it matches the old code in get_user_profile()
				return;
			}

			array_walk($item->get_subs(), $walker);
		};
		$tree = block_exacomp\db_layer_all_user_courses::create($userid)->get_subjects();
		array_walk($tree, $walker);
		*/

        // new, faster
        $courseids = array_keys(enrol_get_all_users_courses($user->userid));

        if ($courseids) {
            $sql = "SELECT ex.id, ex.id AS tmp
				FROM {" . BLOCK_EXACOMP_DB_EXAMPLES . "} ex
				WHERE ex.id IN (
					SELECT dex.exampid
					FROM {" . BLOCK_EXACOMP_DB_DESCEXAMP . "} dex
					JOIN {" . BLOCK_EXACOMP_DB_DESCTOPICS . "} det ON dex.descrid = det.descrid
					JOIN {" . BLOCK_EXACOMP_DB_COURSETOPICS . "} ct ON det.topicid = ct.topicid
					JOIN {" . BLOCK_EXACOMP_DB_DESCRIPTORS . "} d ON d.id = dex.descrid
					WHERE ct.courseid IN (" . join(',', $courseids) . ")
					AND d.parentid = 0 -- ignore child descriptors
				)
				AND ex.source != " . BLOCK_EXACOMP_EXAMPLE_SOURCE_USER . "
			";

            $all_examples = g::$DB->get_records_sql_menu($sql);
        }

        $examples_submitted = array_intersect_key($all_examples_submitted, $all_examples);
        $examples_reached = array_intersect_key($all_examples_reached, $all_examples);

        /*
		var_dump([
			'examples_total' => $all_examples,
			'examples_submitted' => $examples_submitted,
			'examples_reached' => $examples_reached,
		]);
		sort($all_examples);
		var_dump(join(',', $all_examples));
		/* */

        $user->examples = [
            'total' => count($all_examples),
            'submitted' => count($examples_submitted),
            'reached' => count($examples_reached),
        ];
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function get_user_profile_returns() {
        return new external_single_structure(array(
            'user' => new external_single_structure(array(
                'competencies' => new external_single_structure(array(
                    'total' => new external_value(PARAM_INT, 'amount of total competencies'),
                    'reached' => new external_value(PARAM_INT, 'amount of reached competencies'),
                )),
                'examples' => new external_single_structure(array(
                    'total' => new external_value(PARAM_INT, 'amount of total competencies'),
                    'reached' => new external_value(PARAM_INT, 'amount of reached competencies'),
                )),
            )),
            'subjects' => new external_multiple_structure(new external_single_structure(array(
                'title' => new external_value(PARAM_TEXT, 'subject title'),
                'data' => new external_single_structure(array(
                    'competencies' => new external_single_structure(array(
                        'total' => new external_value(PARAM_INT, 'amount of total competencies'),
                        'reached' => new external_value(PARAM_INT, 'amount of reached competencies'),
                    )),
                    'examples' => new external_single_structure(array(
                        'total' => new external_value(PARAM_INT, 'amount of total competencies'),
                        'reached' => new external_value(PARAM_INT, 'amount of reached competencies'),
                    )),
                )),
                'topics' => new external_multiple_structure(new external_single_structure(array(
                    'title' => new external_value(PARAM_TEXT, 'topic title'),
                    'data' => new external_single_structure(array(
                        'competencies' => new external_single_structure(array(
                            'total' => new external_value(PARAM_INT, 'amount of total competencies'),
                            'reached' => new external_value(PARAM_INT, 'amount of reached competencies'),
                        )),
                        'examples' => new external_single_structure(array(
                            'total' => new external_value(PARAM_INT, 'amount of total competencies'),
                            'reached' => new external_value(PARAM_INT, 'amount of reached competencies'),
                        )),
                    )),
                ))),
            ))),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function update_example_parameters() {
        return new external_function_parameters(array(
            'exampleid' => new external_value(PARAM_INT, 'id of example'),
            'name' => new external_value(PARAM_TEXT, 'title of example'),
            'description' => new external_value(PARAM_TEXT, 'description of example'),
            'externalurl' => new external_value(PARAM_TEXT, ''),
            'comps' => new external_value(PARAM_TEXT, 'list of competencies, seperated by comma'),
            'filename' => new external_value(PARAM_TEXT, 'filename, used to look up file and create a new one in the exaport file area'),
            'fileitemid' => new external_value(PARAM_INT, 'fileitemid'),
        ));
    }

    /**
     * update an example
     *
     * @ws-type-write
     * @param $exampleid
     * @param $name
     * @param $description
     * @param $externalurl
     * @param $comps
     * @param $filename
     * @param int $fileitemid
     * @return array
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     */
    public static function update_example($exampleid, $name, $description, $externalurl, $comps, $filename, $fileitemid = 0) {
        global $CFG, $DB, $USER;

        if (empty ($exampleid) || empty ($name)) {
            throw new invalid_parameter_exception ('Parameter can not be empty');
        }

        static::validate_parameters(static::update_example_parameters(), array(
            'exampleid' => $exampleid,
            'name' => $name,
            'description' => $description,
            'externalurl' => $externalurl,
            'comps' => $comps,
            'filename' => $filename,
            'fileitemid' => $fileitemid,
        ));

        $example = example::get($exampleid);

        block_exacomp_require_item_capability(BLOCK_EXACOMP_CAP_MODIFY, $example);

        $type = ($filename != '') ? 'file' : 'url';
        if ($type == 'file') {
            $context = context_user::instance($USER->id);
            $fs = get_file_storage();

            if (!$file = $fs->get_file($context->id, 'user', 'draft', $fileitemid, '/', $filename)) {
                throw new moodle_exception('file not found');
            }

            $fs->delete_area_files(context_system::instance()->id, 'block_exacomp', 'example_task', $example->id);
            $fs->create_file_from_storedfile(array(
                'contextid' => context_system::instance()->id,
                'component' => 'block_exacomp',
                'filearea' => 'example_task',
                'itemid' => $example->id,
            ), $file);

            $file->delete();
        }

        // insert into examples and example_desc
        $example->title = $name;
        $example->description = $description;
        $example->externalurl = $externalurl;

        $DB->update_record(BLOCK_EXACOMP_DB_EXAMPLES, $example);

        if (!empty ($comps)) {
            $DB->delete_records(BLOCK_EXACOMP_DB_DESCEXAMP, array(
                'exampid' => $exampleid,
            ));

            $descriptors = explode(',', $comps);
            foreach ($descriptors as $descriptor) {
                $insert = new stdClass ();
                $insert->exampid = $exampleid;
                $insert->descrid = $descriptor;
                $DB->insert_record(BLOCK_EXACOMP_DB_DESCEXAMP, $insert);
            }
        }

        return array(
            "success" => true,
        );
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function update_example_returns() {
        return new external_single_structure(array(
            'success' => new external_value(PARAM_BOOL, 'true if successful'),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function delete_example_parameters() {
        return new external_function_parameters(array(
            'exampleid' => new external_value(PARAM_INT, 'id of example'),
        ));
    }

    /**
     * delete a custom item
     * delete example
     *
     * @ws-type-write
     * @param * @return
     *
     */
    public static function delete_example($exampleid) {
        global $DB, $USER;

        static::validate_parameters(static::delete_example_parameters(), array(
            'exampleid' => $exampleid,
        ));

        $example = $DB->get_record('block_exacompexamples', array('id' => $exampleid, 'creatorid' => $USER->id));
        if (!$example) {
            throw new invalid_parameter_exception ('Parameter can not be empty');
        }

        // also checks for permissions
        block_exacomp_delete_custom_example($exampleid);

        $items = $DB->get_records(BLOCK_EXACOMP_DB_ITEM_MM, array('exacomp_record_id' => $exampleid));
        foreach ($items as $item) {
            $DB->delete_records(BLOCK_EXACOMP_DB_ITEM_MM, array('id' => $item->id));
            static::delete_item($item->itemid);
        }

        return array(
            "success" => true,
        );
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function delete_example_returns() {
        return new external_single_structure(array(
            'success' => new external_value(PARAM_BOOL, 'true if successful'),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_competencies_by_topic_parameters() {
        return new external_function_parameters(array(
            'userid' => new external_value(PARAM_INT, 'id of user'),
            'topicid' => new external_value(PARAM_INT, 'id of topic'),
        ));
    }

    /**
     * get competencies for a specific topic
     * Get all available competencies
     *
     * @ws-type-read
     * @param int subjectid
     * @return array of examples
     */
    public static function get_competencies_by_topic($userid, $topicid) {
        global $USER;

        static::validate_parameters(static::get_competencies_by_topic_parameters(), array(
            'userid' => $userid,
            'topicid' => $topicid,
        ));

        if (!$userid || $userid == 0) {
            $userid = $USER->id;
        }

        static::require_can_access_user($userid);

        $structure = array();

        $courses = static::get_courses($userid);

        foreach ($courses as $course) {
            $tree = block_exacomp_get_competence_tree($course["courseid"]);
            foreach ($tree as $subject) {
                foreach ($subject->topics as $topic) {
                    if ($topicid == 0 || ($topicid != 0 && $topic->id == $topicid)) {
                        foreach ($topic->descriptors as $descriptor) {
                            $elem_desc = new stdClass ();
                            $elem_desc->descriptorid = $descriptor->id;
                            $elem_desc->descriptortitle = static::custom_htmltrim($descriptor->title);
                            $structure[] = $elem_desc;
                        }
                    }
                }
            }
        }

        return $structure;
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function get_competencies_by_topic_returns() {
        return new external_multiple_structure(new external_single_structure(array(
            'descriptorid' => new external_value(PARAM_INT, 'id of example'),
            'descriptortitle' => new external_value(PARAM_TEXT, 'title of example'),
        )));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     *
     */
    public static function dakora_set_competence_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'userid' => new external_value(PARAM_INT, 'id of user, if 0 current user'),
            'compid' => new external_value(PARAM_INT, 'competence id'),
            'comptype' => new external_value(PARAM_INT, 'type of competence: descriptor, topic, subject'),
            'role' => new external_value(PARAM_INT, 'user role (0 == student, 1 == teacher)'),
            'value' => new external_value(PARAM_INT, 'evaluation value, only set for TK (0 to 3)'),
            'additionalinfo' => new external_value(PARAM_FLOAT, 'decimal between 1 and 6'),
            'evalniveauid' => new external_value(PARAM_INT, 'evaluation niveau (-1, 1, 2, 3)'),
            'subjectid' => new external_value(PARAM_INT, 'subjectid', VALUE_DEFAULT, -1),
        ));
    }

    /**
     * set competence for student
     * Set a competence for a user
     *
     * @ws-type-write
     * @param int courseid
     *            int userid
     *            int compid
     *            int role
     *            int value
     * @return success
     */
    public static function dakora_set_competence($courseid, $userid, $compid, $comptype, $role, $value, $additionalinfo, $evalniveauid, $subjectid) {
        global $USER, $DB;
        static::validate_parameters(static::dakora_set_competence_parameters(), array(
            'courseid' => $courseid,
            'userid' => $userid,
            'compid' => $compid,
            'comptype' => $comptype,
            'role' => $role,
            'value' => $value,
            'additionalinfo' => $additionalinfo,
            'evalniveauid' => $evalniveauid,
            'subjectid' => $subjectid,
        ));

        if ($userid == 0 && $role == BLOCK_EXACOMP_ROLE_STUDENT) {
            $userid = $USER->id;
        } else {
            if ($userid == 0) {
                throw new invalid_parameter_exception ('Userid can not be 0 for teacher grading');
            }
        }

        static::require_can_access_course_user($courseid, $userid);

        $parent = true;
        if ($comptype == BLOCK_EXACOMP_TYPE_DESCRIPTOR) {
            $descriptor = $DB->get_record(BLOCK_EXACOMP_DB_DESCRIPTORS, array('id' => $compid));
            if ($descriptor && $descriptor->parentid > 0) {
                $parent = false;
            }
        }

        $mapping = true;
        //if($parent && block_exacomp_get_assessment_comp_scheme()!=1){
        if ($parent && block_exacomp_additional_grading($comptype, $courseid) != BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE) {
            $mapping = false;
        }
        //if(!$parent && block_exacomp_get_assessment_childcomp_scheme()!=1){
        // dakora app sends always DESCRIPTOR types. So we need to check $parent variable
        if ($comptype == BLOCK_EXACOMP_TYPE_DESCRIPTOR) {
            if (!$parent && block_exacomp_additional_grading(BLOCK_EXACOMP_TYPE_DESCRIPTOR_CHILD, $courseid) != BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE) {
                $mapping = false;
            }
        }

        $customdata = ['block' => 'exacomp', 'app' => 'dakora', 'courseid' => $courseid, 'descriptorid' => $compid, 'userid' => $userid];

        if ($mapping && $role == BLOCK_EXACOMP_ROLE_TEACHER) { // grade ==> mapping needed, save mapped value and save additionalinfo
            //check if teacher, because the student sends the selfevaluationvalue in $value, not in $additinalinfo
            $value = global_config::get_additionalinfo_value_mapping($additionalinfo);
            if (block_exacomp_set_user_competence($userid, $compid, $comptype, $courseid, $role, $value, $evalniveauid, $subjectid, false, [
                    'notification_customdata' => $customdata,
                ]) < 0) {
                throw new invalid_parameter_exception ('Not allowed');
            }
            block_exacomp_save_additional_grading_for_comp($courseid, $compid, $userid, $additionalinfo, $comptype);
        } else {    // not grade ==> no mapping needed, just save the adittionalinfo into value
            if (block_exacomp_set_user_competence($userid, $compid, $comptype, $courseid, $role, $value, $evalniveauid, $subjectid, true, [
                    'notification_customdata' => $customdata,
                ]) < 0) {
                throw new invalid_parameter_exception ('Not allowed');
            }
        }

        return ['success' => true];
        // TODO: should we also return the changed values?
        // + (array)block_exacomp_get_comp_eval($courseid, $role, $userid, $comptype, $compid);
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function dakora_set_competence_returns() {
        return new external_single_structure(array(
            'success' => new external_value(PARAM_BOOL, 'status of success, either true (1) or false (0)'),
            /*
			'value' => new external_value(PARAM_INT, 'evaluation value, only set for TK (0 to 3)'),
			'additionalinfo' => new external_value(PARAM_FLOAT, 'decimal between 1 and 6'),
			'evalniveauid' => new external_value(PARAM_INT, 'evaluation niveau (-1, 1, 2, 3)'),
			*/
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function dakora_get_courses_parameters() {
        return static::get_courses_parameters();
    }

    /**
     * get courses for user for dakora app
     * get courses
     *
     * @ws-type-read
     * @return array of user courses
     */
    public static function dakora_get_courses($userid = null) {
        return static::get_courses($userid);
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function dakora_get_courses_returns() {
        return static::get_courses_returns();
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function dakora_get_topics_by_course_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'userid' => new external_value(PARAM_INT, 'id of user, 0 for current user', VALUE_DEFAULT, 0),
            'forall' => new external_value(PARAM_BOOL, 'for all users = true, for one user = false', VALUE_DEFAULT, 0),
        ));
    }

    /**
     * get topics for course for dakora app associated with examples
     * get courses
     *
     * @ws-type-read
     * @return array of user courses
     */
    public static function dakora_get_topics_by_course($courseid, $userid, $forall) {
        global $USER;

        static::validate_parameters(static::dakora_get_topics_by_course_parameters(), array(
            'courseid' => $courseid,
            'userid' => $userid,
            'forall' => $forall,
        ));

        if ($userid == 0 && $forall == false) {
            $userid = $USER->id;
        }

        return static::dakora_get_topics_by_course_common($courseid, true, $userid);
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function dakora_get_topics_by_course_returns() {
        return new external_multiple_structure(new external_single_structure(array(
            'topicid' => new external_value(PARAM_INT, 'id of topic'),
            'topictitle' => new external_value(PARAM_TEXT, 'title of topic'),
            'numbering' => new external_value(PARAM_TEXT, 'numbering for topic'),
            'subjectid' => new external_value(PARAM_INT, 'id of subject'),
            'subjecttitle' => new external_value(PARAM_TEXT, 'title of subject'),
            'visible' => new external_value(PARAM_INT, 'visibility of topic in current context'),
            'used' => new external_value(PARAM_INT, 'used in current context'),
        )));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function dakora_get_all_topics_by_course_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'userid' => new external_value(PARAM_INT, 'id of user, 0 for current user', VALUE_DEFAULT, 0),
            'forall' => new external_value(PARAM_BOOL, 'for all users = true, for one user = false', VALUE_DEFAULT, 0),
            'groupid' => new external_value(PARAM_INT, 'id of user, 0 for current user', VALUE_DEFAULT),
        ));
    }

    /**
     * get topics for course for dakora app
     * get courses
     *
     * @ws-type-read
     * @return array of user courses
     */
    public static function dakora_get_all_topics_by_course($courseid, $userid, $forall, $groupid = -1) {
        global $USER;

        static::validate_parameters(static::dakora_get_all_topics_by_course_parameters(), array(
            'courseid' => $courseid,
            'userid' => $userid,
            'forall' => $forall,
            'groupid' => $groupid,
        ));

        if ($userid == 0 && $forall == false) {
            $userid = $USER->id;
        }

        if ($userid != 0) {
            block_exacomp_update_related_examples_visibilities_for_single_student($courseid, $userid);
        }

        $return = new stdClass();
        $return->topics = static::dakora_get_topics_by_course_common($courseid, false, $userid, $groupid);
        $return->activitylist = static::return_key_value(block_exacomp_list_possible_activities_for_example($courseid));

        return $return;
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function dakora_get_all_topics_by_course_returns() {
        return new external_single_structure(array(
            'topics' => new external_multiple_structure(new external_single_structure(array(
                'topicid' => new external_value(PARAM_INT, 'id of topic'),
                'topictitle' => new external_value(PARAM_TEXT, 'title of topic'),
                'topicdescription' => new external_value(PARAM_RAW, 'description of topic'),
                'numbering' => new external_value(PARAM_TEXT, 'numbering for topic'),
                'subjectid' => new external_value(PARAM_INT, 'id of subject'),
                'subjecttitle' => new external_value(PARAM_TEXT, 'title of subject'),
                'visible' => new external_value(PARAM_INT, 'visibility of topic in current context'),
                'used' => new external_value(PARAM_INT, 'used in current context'),
                // 'gradingisold' => new external_value(PARAM_BOOL, 'true when there are childdescriptors with newer gradings than the parentdescriptor'),
            ))),
            'activitylist' => static::key_value_returns(PARAM_INT, PARAM_TEXT, 'possible activities list. needed for new example form'),
        ));
    }

    /*
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
    public static function dakora_get_descriptors_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'topicid' => new external_value(PARAM_INT, 'id of topic'),
            'userid' => new external_value(PARAM_INT, 'id of user, 0 for current user'),
            'forall' => new external_value(PARAM_BOOL, 'for all users = true, for one user = false'),
        ));
    }

    /**
     * get descriptors for topic for dakora app associated with examples
     * get descriptors for one topic, considering the visibility
     *
     * @ws-type-read
     * @return array of user courses
     */
    public static function dakora_get_descriptors($courseid, $topicid, $userid, $forall) {
        global $USER;
        static::validate_parameters(static::dakora_get_descriptors_parameters(), array(
            'courseid' => $courseid,
            'topicid' => $topicid,
            'userid' => $userid,
            'forall' => $forall,
        ));

        if ($userid == 0 && $forall == false) {
            $userid = $USER->id;
        }

        return static::dakora_get_descriptors_common($courseid, $topicid, $userid, $forall, true);
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function dakora_get_descriptors_returns() {
        return new external_multiple_structure(new external_single_structure(array(
            'descriptorid' => new external_value(PARAM_INT, 'id of descriptor'),
            'descriptortitle' => new external_value(PARAM_TEXT, 'title of descriptor'),
            'numbering' => new external_value(PARAM_TEXT, 'numbering for descriptor'),
            'niveautitle' => new external_value(PARAM_TEXT, 'title of niveau'),
            'niveaudescription' => new external_value(PARAM_TEXT, 'description of niveau'),
            'niveauid' => new external_value(PARAM_INT, 'id of niveau'),
            'visible' => new external_value(PARAM_INT, 'visibility of topic in current context'),
            'niveauvisible' => new external_value(PARAM_BOOL, 'if niveau is visible'),
            'used' => new external_value(PARAM_INT, 'used in current context'),
            'niveausort' => new external_value(PARAM_INT, 'sorting for ids'),
        )));
    }

    /*
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 */
    public static function dakora_get_all_descriptors_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'topicid' => new external_value(PARAM_INT, 'id of topic'),
            'userid' => new external_value(PARAM_INT, 'id of user, 0 for current user'),
            'forall' => new external_value(PARAM_BOOL, 'for all users = true, for one user = false'),
            'editmode' => new external_value(PARAM_BOOL, 'when editmode is active, descriptors fo hidden niveaus should be loaded', VALUE_DEFAULT, 0),
        ));
    }

    /**
     * get descriptors for topic for dakora app
     * get courses
     *
     * @ws-type-read
     * @return array of user courses
     */
    public static function dakora_get_all_descriptors($courseid, $topicid, $userid, $forall, $editmode) {
        global $USER;
        static::validate_parameters(static::dakora_get_all_descriptors_parameters(), array(
            'courseid' => $courseid,
            'topicid' => $topicid,
            'userid' => $userid,
            'forall' => $forall,
            'editmode' => $editmode,
        ));

        if ($userid == 0 && $forall == false) {
            $userid = $USER->id;
        }

        return static::dakora_get_descriptors_common($courseid, $topicid, $userid, $forall, false, $editmode, false);
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function dakora_get_all_descriptors_returns() {
        return new external_multiple_structure(new external_single_structure(array(
            'descriptorid' => new external_value(PARAM_INT, 'id of descriptor'),
            'descriptortitle' => new external_value(PARAM_TEXT, 'title of descriptor'),
            'numbering' => new external_value(PARAM_TEXT, 'numbering for descriptor'),
            'niveautitle' => new external_value(PARAM_TEXT, 'title of niveau'),
            'niveauid' => new external_value(PARAM_INT, 'id of niveau'),
            'niveaudescription' => new external_value(PARAM_TEXT, 'description of niveau'),
            'visible' => new external_value(PARAM_INT, 'visibility of topic in current context'),
            'used' => new external_value(PARAM_INT, 'used in current context'),
            'gradingisold' => new external_value(PARAM_BOOL, 'true when there are newer gradings in the childcompetences', false),
            'niveauvisible' => new external_value(PARAM_BOOL, 'if niveau is visible'),
            'niveausort' => new external_value(PARAM_INT, 'sorting for ids', false),
        )));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function dakora_get_descriptor_children_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'descriptorid' => new external_value(PARAM_INT, 'id of parent descriptor'),
            'userid' => new external_value(PARAM_INT, 'id of user, 0 for current user'),
            'forall' => new external_value(PARAM_BOOL, 'for all users = true, for one user = false'),
        ));
    }

    /**
     * get children (childdescriptor and examples) for descriptor for dakora app (only childs associated with examples)
     * get courses
     *
     * @ws-type-read
     * @return array of user courses
     */
    public static function dakora_get_descriptor_children($courseid, $descriptorid, $userid, $forall) {
        global $USER;
        static::validate_parameters(static::dakora_get_descriptor_children_parameters(), array(
            'courseid' => $courseid,
            'descriptorid' => $descriptorid,
            'userid' => $userid,
            'forall' => $forall,
        ));

        if ($userid == 0 && !$forall) {
            $userid = $USER->id;
        }

        $return = static::get_descriptor_children($courseid, $descriptorid, $userid, $forall);

        return $return;
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function dakora_get_descriptor_children_returns() {
        return new external_single_structure(array(
            'children' => new external_multiple_structure(new external_single_structure(array(
                'descriptorid' => new external_value(PARAM_INT, 'id of child'),
                'descriptortitle' => new external_value(PARAM_TEXT, 'title of child'),
                'numbering' => new external_value(PARAM_TEXT, 'numbering for child'),
                'teacherevaluation' => new external_value(PARAM_INT, 'grading of child'),
                'evalniveauid' => new external_value(PARAM_INT, 'evaluation niveau id'),
                'timestampteacher' => new external_value(PARAM_INT, 'timestamp of teacher evaluation'),
                'studentevaluation' => new external_value(PARAM_INT, 'self evaluation of child'),
                'timestampstudent' => new external_value(PARAM_INT, 'timestamp of student evaluation'),
                'examplestotal' => new external_value(PARAM_INT, 'total number of material'),
                'examplesvisible' => new external_value(PARAM_INT, 'visible number of material'),
                'examplesinwork' => new external_value(PARAM_INT, 'edited number of material'),
                'visible' => new external_value(PARAM_INT, 'visibility of child'),
                'used' => new external_value(PARAM_INT, 'used in current context'),
            ))),
            'examples' => new external_multiple_structure(new external_single_structure(array(
                'exampleid' => new external_value(PARAM_INT, 'id of example'),
                'exampletitle' => new external_value(PARAM_TEXT, 'title of example'),
                'examplestate' => new external_value(PARAM_INT, 'state of example, always 0 if for all students'),
                'visible' => new external_value(PARAM_INT, 'visibility of example'),
                'used' => new external_value(PARAM_INT, 'used in current context'),
            ))),
            'examplestotal' => new external_value(PARAM_INT, 'number of total examples'),
            'examplesvisible' => new external_value(PARAM_INT, 'number of visible examples'),
            'examplesinwork' => new external_value(PARAM_INT, 'number of examples in work'),
        ));
    }

    public static function dakora_get_examples_for_descriptor_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'descriptorid' => new external_value(PARAM_INT, 'id of parent descriptor'),
            'userid' => new external_value(PARAM_INT, 'id of user, if 0 current user'),
            'forall' => new external_value(PARAM_BOOL, 'if all users = true, only one user = false'),
        ));
    }

    /**
     * get examples for descriptor for dakora app
     *
     * @ws-type-read
     * @param $courseid
     * @param $descriptorid
     * @param $userid
     * @param $forall
     * @return array
     */
    public static function dakora_get_examples_for_descriptor($courseid, $descriptorid, $userid, $forall) {
        global $USER;
        static::validate_parameters(static::dakora_get_examples_for_descriptor_parameters(), array(
            'courseid' => $courseid,
            'descriptorid' => $descriptorid,
            'userid' => $userid,
            'forall' => $forall,
        ));

        if ($userid == 0 && !$forall) {
            $userid = $USER->id;
        }

        return static::dakora_get_examples_for_descriptor_common($courseid, $descriptorid, $userid, $forall, 0);
    }

    public static function dakora_get_examples_for_descriptor_returns() {
        return new external_multiple_structure(new external_single_structure(array(
            'exampleid' => new external_value(PARAM_INT, 'id of descriptor'),
            'exampletitle' => new external_value(PARAM_TEXT, 'title of descriptor'),
            'examplestate' => new external_value(PARAM_INT, 'state of example, always 0 if for all students'),
            'visible' => new external_value(PARAM_INT, 'visibility of example in current context'),
            'used' => new external_value(PARAM_INT, 'used in current context'),
        )));
    }

    public static function dakora_get_examples_for_descriptor_with_grading_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'descriptorid' => new external_value(PARAM_INT, 'id of parent descriptor'),
            'userid' => new external_value(PARAM_INT, 'id of user, if 0 current user'),
            'forall' => new external_value(PARAM_BOOL, 'if all users = true, only one user = false'),
        ));
    }

    /**
     * get examples for descriptor with additional grading information
     *
     * @ws-type-read
     * @param $courseid
     * @param $descriptorid
     * @param $userid
     * @param $forall
     * @return array
     */
    public static function dakora_get_examples_for_descriptor_with_grading($courseid, $descriptorid, $userid, $forall) {
        global $USER;
        static::validate_parameters(static::dakora_get_examples_for_descriptor_with_grading_parameters(), array(
            'courseid' => $courseid,
            'descriptorid' => $descriptorid,
            'userid' => $userid,
            'forall' => $forall,
        ));

        if ($userid == 0 && !$forall) {
            $userid = $USER->id;
        }

        return static::dakora_get_examples_for_descriptor_common($courseid, $descriptorid, $userid, $forall, 0);
    }

    public static function dakora_get_examples_for_descriptor_with_grading_returns() {
        return new external_multiple_structure(new external_single_structure(array(
            'exampleid' => new external_value(PARAM_INT, 'id of descriptor'),
            'exampletitle' => new external_value(PARAM_TEXT, 'title of descriptor'),
            'examplestate' => new external_value(PARAM_INT, 'state of example, always 0 if for all students'),
            'visible' => new external_value(PARAM_INT, 'visibility of example in current context'),
            'used' => new external_value(PARAM_INT, 'used in current context'),
            'teacherevaluation' => new external_value(PARAM_INT, 'example evaluation of teacher'),
            'studentevaluation' => new external_value(PARAM_INT, 'example evaluation of student'),
            'evalniveauid' => new external_value(PARAM_INT, 'evaluation niveau id'),
            'timestampteacher' => new external_value(PARAM_INT, 'timestamp for teacher evaluation'),
            'timestampstudent' => new external_value(PARAM_INT, 'timestamp for student evaluation'),
        )));
    }

    public static function dakora_get_examples_for_descriptor_for_crosssubject_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'descriptorid' => new external_value(PARAM_INT, 'id of parent descriptor'),
            'userid' => new external_value(PARAM_INT, 'id of user, if 0 current user'),
            'forall' => new external_value(PARAM_BOOL, 'if all users = true, only one user = false'),
            'crosssubjid' => new external_value(PARAM_INT, 'id of crosssubject'),
        ));
    }

    /**
     * get examples for descriptor for dakora app
     *
     * @ws-type-read
     * @param $courseid
     * @param $descriptorid
     * @param $userid
     * @param $forall
     * @param $crosssubjid
     * @return array
     */
    public static function dakora_get_examples_for_descriptor_for_crosssubject($courseid, $descriptorid, $userid, $forall, $crosssubjid) {
        global $USER;
        static::validate_parameters(static::dakora_get_examples_for_descriptor_for_crosssubject_parameters(), array(
            'courseid' => $courseid,
            'descriptorid' => $descriptorid,
            'userid' => $userid,
            'forall' => $forall,
            'crosssubjid' => $crosssubjid,
        ));

        if ($userid == 0 && !$forall) {
            $userid = $USER->id;
        }

        return static::dakora_get_examples_for_descriptor_common($courseid, $descriptorid, $userid, $forall, $crosssubjid);
    }

    public static function dakora_get_examples_for_descriptor_for_crosssubject_returns() {
        return new external_multiple_structure(new external_single_structure(array(
            'exampleid' => new external_value(PARAM_INT, 'id of descriptor'),
            'exampletitle' => new external_value(PARAM_TEXT, 'title of descriptor'),
            'examplestate' => new external_value(PARAM_INT, 'state of example, always 0 if for all students'),
            'visible' => new external_value(PARAM_INT, 'visibility of example in current context'),
            'used' => new external_value(PARAM_INT, 'used in current context'),
        )));
    }

    public static function dakora_get_examples_for_descriptor_for_crosssubject_with_grading_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'descriptorid' => new external_value(PARAM_INT, 'id of parent descriptor'),
            'userid' => new external_value(PARAM_INT, 'id of user, if 0 current user'),
            'forall' => new external_value(PARAM_BOOL, 'if all users = true, only one user = false'),
            'crosssubjid' => new external_value(PARAM_INT, 'id of crosssubject'),
        ));
    }

    /**
     * get examples for descriptor with additional grading information
     *
     * @ws-type-read
     * @param $courseid
     * @param $descriptorid
     * @param $userid
     * @param $forall
     * @param $crosssubjid
     * @return array
     */
    public static function dakora_get_examples_for_descriptor_for_crosssubject_with_grading($courseid, $descriptorid, $userid, $forall, $crosssubjid) {
        global $USER;
        static::validate_parameters(static::dakora_get_examples_for_descriptor_for_crosssubject_with_grading_parameters(), array(
            'courseid' => $courseid,
            'descriptorid' => $descriptorid,
            'userid' => $userid,
            'forall' => $forall,
            'crosssubjid' => $crosssubjid,
        ));

        if ($userid == 0 && !$forall) {
            $userid = $USER->id;
        }

        return static::dakora_get_examples_for_descriptor_for_crosssubject($courseid, $descriptorid, $userid, $forall, $crosssubjid);
    }

    public static function dakora_get_examples_for_descriptor_for_crosssubject_with_grading_returns() {
        return new external_multiple_structure(new external_single_structure(array(
            'exampleid' => new external_value(PARAM_INT, 'id of descriptor'),
            'exampletitle' => new external_value(PARAM_TEXT, 'title of descriptor'),
            'examplestate' => new external_value(PARAM_INT, 'state of example, always 0 if for all students'),
            'visible' => new external_value(PARAM_INT, 'visibility of example in current context'),
            'used' => new external_value(PARAM_INT, 'used in current context'),
            'teacherevaluation' => new external_value(PARAM_INT, 'example evaluation of teacher'),
            'studentevaluation' => new external_value(PARAM_INT, 'example evaluation of student'),
            'evalniveauid' => new external_value(PARAM_INT, 'evaluation niveau id'),
            'timestampteacher' => new external_value(PARAM_INT, 'timestamp for teacher evaluation'),
            'timestampstudent' => new external_value(PARAM_INT, 'timestamp for student evaluation'),
        )));
    }

    public static function dakora_get_example_overview_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'exampleid' => new external_value(PARAM_INT, 'id of example'),
            'userid' => new external_value(PARAM_INT, 'id of user, if 0 current user'),
        ));
    }

    /**
     * get example overview for dakora app
     *
     * @ws-type-read
     * @param $courseid
     * @param $exampleid
     * @param $userid
     * @return example
     */
    public static function dakora_get_example_overview($courseid, $exampleid, $userid) {
        static::validate_parameters(static::dakora_get_example_overview_parameters(), array(
            'courseid' => $courseid,
            'exampleid' => $exampleid,
            'userid' => $userid,
        ));

        $isTeacher = block_exacomp_is_teacher($courseid);
        if (!$isTeacher) {
            $userid = g::$USER->id;
        }

        $example = static::get_example_by_id($exampleid);

        //Taxonomies:
        $taxonomies = '';
        $taxids = '';
        foreach (block_exacomp_get_taxonomies_by_example($example->id) as $tax) {
            if ($taxonomies == '') { //first run, no ","
                $taxonomies .= static::custom_htmltrim($tax->title);
                $taxids .= $tax->id;
            } else {
                $taxonomies .= ',' . static::custom_htmltrim($tax->title);
                $taxids .= ',' . $tax->id;
            }
        }
        $example->exampletaxonomies = $taxonomies;
        $example->exampletaxids = $taxids;

        $solution_visible = block_exacomp_is_example_solution_visible($courseid, $example, $userid);
        $example->solution_visible = $solution_visible;

        // remove solution if not visible for student
        if (!$isTeacher && !$solution_visible) {
            $example->solution = "";
        }
        $example->title = static::custom_htmltrim(strip_tags($example->title));

        //        $example->taskfilecount = block_exacomp_get_number_of_files($example, 'example_task');

        return $example;
    }

    public static function dakora_get_example_overview_returns() {
        return new external_single_structure(array(
            'title' => new external_value(PARAM_TEXT, 'title of example'),
            'description' => new external_value(PARAM_TEXT, 'description of example'),
            'taskfileurl' => new external_value(PARAM_TEXT, 'task fileurl'),
            'taskfilenames' => new external_value(PARAM_TEXT, 'task filename'),
            'solutionfilename' => new external_value(PARAM_TEXT, 'task filename', VALUE_OPTIONAL),
            'externalurl' => new external_value(PARAM_TEXT, 'externalurl of example'),
            'externaltask' => new external_value(PARAM_TEXT, 'url of associated module'),
            'externaltask_embedded' => new external_value(PARAM_TEXT, 'url of associated module, link to embedded view in exacomp', VALUE_OPTIONAL),
            'task' => new external_value(PARAM_TEXT, '@deprecated'),
            'taskfilecount' => new external_value(PARAM_TEXT, 'number of files for the task'),
            'solution' => new external_value(PARAM_TEXT, 'solution(url/description) of example'),
            'timeframe' => new external_value(PARAM_TEXT, 'timeframe as string'),  //timeframe in minutes?? not anymore, it can be "4 hours" as well for example
            'hassubmissions' => new external_value(PARAM_BOOL, 'true if example has already submissions'),
            'solution_visible' => new external_value(PARAM_BOOL, 'visibility for example solution in current context'),
            'exampletaxonomies' => new external_value(PARAM_TEXT, 'taxonomies seperated by comma', VALUE_OPTIONAL),
            'exampletaxids' => new external_value(PARAM_TEXT, 'taxids seperated by comma', VALUE_OPTIONAL),
            'is_teacherexample' => new external_value(PARAM_BOOL, 'is teacher example?', VALUE_OPTIONAL),
        ));
    }

    public static function diggrplus_get_example_overview_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'exampleid' => new external_value(PARAM_INT, 'id of example'),
            'userid' => new external_value(PARAM_INT, 'id of user, if 0 current user'),
        ));
    }

    /**
     * get example overview for dakora app
     *
     * @ws-type-read
     * @param $courseid
     * @param $exampleid
     * @param $userid
     * @return example
     */
    public static function diggrplus_get_example_overview($courseid, $exampleid, $userid) {
        static::validate_parameters(static::diggrplus_get_example_overview_parameters(), array(
            'courseid' => $courseid,
            'exampleid' => $exampleid,
            'userid' => $userid,
        ));

        global $DB;

        $isTeacher = block_exacomp_is_teacher($courseid);
        if (!$isTeacher) {
            $userid = g::$USER->id;
        }

        $example = static::get_example_by_id($exampleid, $courseid);

        //Taxonomies:
        $taxonomies = '';
        $taxids = '';
        foreach (block_exacomp_get_taxonomies_by_example($example->id) as $tax) {
            if ($taxonomies == '') { //first run, no ","
                $taxonomies .= static::custom_htmltrim($tax->title);
                $taxids .= $tax->id;
            } else {
                $taxonomies .= ',' . static::custom_htmltrim($tax->title);
                $taxids .= ',' . $tax->id;
            }
        }
        $example->exampletaxonomies = $taxonomies;
        $example->exampletaxids = $taxids;

        $solution_visible = block_exacomp_is_example_solution_visible($courseid, $example, $userid);
        $example->solution_visible = $solution_visible;

        // remove solution if not visible for student
        if (!$isTeacher && !$solution_visible) {
            $example->solution = "";
        }
        $example->title = static::custom_htmltrim(strip_tags($example->title));

        $example->visible = block_exacomp_is_example_visible($courseid, $exampleid, $userid);
        //        $example->taskfilecount = block_exacomp_get_number_of_files($example, 'example_task');

        $example->annotation = $DB->get_field(BLOCK_EXACOMP_DB_EXAMPLE_ANNOTATION, 'annotationtext', array('exampleid' => $exampleid, 'courseid' => $courseid));

        return $example;
    }

    public static function diggrplus_get_example_overview_returns() {
        return new external_single_structure(array(
            'id' => new external_value(PARAM_INT, 'id of example'),
            'visible' => new external_value(PARAM_BOOL, 'visibility of example'),
            'title' => new external_value(PARAM_TEXT, 'title of example'),
            'description' => new external_value(PARAM_TEXT, 'description of example'),
            'solutionfilename' => new external_value(PARAM_TEXT, 'task filename', VALUE_OPTIONAL),
            'externalurl' => new external_value(PARAM_TEXT, 'externalurl of example'),
            'externaltask' => new external_value(PARAM_TEXT, 'url of associated module'),
            'externaltask_embedded' => new external_value(PARAM_TEXT, 'url of associated module, link to embedded view in exacomp', VALUE_OPTIONAL),
            'solution' => new external_value(PARAM_TEXT, 'solution(url/description) of example'),
            'timeframe' => new external_value(PARAM_TEXT, 'timeframe as string'),  //timeframe in minutes?? not anymore, it can be "4 hours" as well for example
            'hassubmissions' => new external_value(PARAM_BOOL, 'true if example has already submissions'),
            'solution_visible' => new external_value(PARAM_BOOL, 'visibility for example solution in current context'),
            'exampletaxonomies' => new external_value(PARAM_TEXT, 'taxonomies seperated by comma', VALUE_OPTIONAL),
            'exampletaxids' => new external_value(PARAM_TEXT, 'taxids seperated by comma', VALUE_OPTIONAL),
            'is_teacherexample' => new external_value(PARAM_BOOL, 'is teacher example?', VALUE_OPTIONAL),
            'creatorid' => new external_value(PARAM_INT, 'creatorid'),
            'annotation' => new external_value(PARAM_TEXT, 'annotation by the teacher for this example in this course'),
            'taskfiles' => new external_multiple_structure(new external_single_structure(array(
                'name' => new external_value(PARAM_TEXT, 'title of taskfile'),
                'url' => new external_value(PARAM_URL, 'file url'),
                'type' => new external_value(PARAM_TEXT, 'mime type for file'),
                //                    'fileindex' => new external_value(PARAM_TEXT, 'fileindex, used for deleting this file')
            )), 'taskfiles of the example', VALUE_OPTIONAL),
            'completefile' => new external_value(PARAM_TEXT, 'completefile (url/description) of example', VALUE_OPTIONAL),
            'completefilefilename' => new external_value(PARAM_TEXT, 'completefile filename', VALUE_OPTIONAL),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function dakora_add_example_to_learning_calendar_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'exampleid' => new external_value(PARAM_INT, 'id of example'),
            'creatorid' => new external_value(PARAM_INT, 'id of creator'),
            'userid' => new external_value(PARAM_INT, 'id of user, if 0 current user'),
            'forall' => new external_value(PARAM_BOOL, 'for all users = true, for one user = false'),
            'groupid' => new external_value(PARAM_INT, 'id of group', VALUE_DEFAULT),
        ));
    }

    /**
     * add example to learning calendar for dakora
     * get courses
     *
     * @ws-type-write
     * @return array of user courses
     */
    public static function dakora_add_example_to_learning_calendar($courseid, $exampleid, $creatorid, $userid, $forall, $groupid = 0) {
        global $DB, $USER;
        static::validate_parameters(static::dakora_add_example_to_learning_calendar_parameters(), array(
            'courseid' => $courseid,
            'exampleid' => $exampleid,
            'creatorid' => $creatorid,
            'userid' => $userid,
            'forall' => $forall,
            'groupid' => $groupid,
        ));

        if ($creatorid == 0) {
            $creatorid = $USER->id;
        }

        //Deprecated.. the with userid=0 it gets added to the planning storage of the teacher
        // 		if ($userid == 0 && !$forall) {
        // 			$userid = $USER->id;
        // 		}
        if (block_exacomp_is_student($courseid)) {
            $userid = $USER->id;
            $source = 'S';
        } else {
            $source = 'T';
        }

        static::require_can_access_course_user($courseid, $creatorid);
        static::require_can_access_course_user($courseid, $userid);
        static::require_can_access_example($exampleid, $courseid);

        //$example = $DB->get_record(BLOCK_EXACOMP_DB_EXAMPLES, array('id' => $exampleid));
        $scheduleid = 0;
        $customdata = ['block' => 'exacomp', 'app' => 'dakora', 'type' => 'add_example_to_learning_calendar', 'exampleid' => $exampleid];
        if ($forall) {
            $source = 'C';
            $students = block_exacomp_get_students_by_course($courseid);
            //Add to all the students
            foreach ($students as $student) {
                if (block_exacomp_is_example_visible($courseid, $exampleid, $student->id)) {
                    block_exacomp_add_example_to_schedule($student->id, $exampleid, $creatorid, $courseid, null, null, -1, -1, $source, null, null, $customdata);
                }
            }
        } else {
            if ($groupid != 0) { //add for group
                $students = block_exacomp_groups_get_members($courseid, $groupid);
                foreach ($students as $student) {
                    if (block_exacomp_is_example_visible($courseid, $exampleid, $student->id)) {
                        block_exacomp_add_example_to_schedule($student->id, $exampleid, $creatorid, $courseid, null, null, -1, -1, $source, null, null, $customdata);
                    }
                }
            } else { // add for single student
                if (block_exacomp_is_example_visible($courseid, $exampleid, $userid)) {
                    $scheduleid = block_exacomp_add_example_to_schedule($userid, $exampleid, $creatorid, $courseid, null, null, -1, -1, $source, null, null, $customdata);
                }
            }
        }

        return array(
            "scheduleid" => $scheduleid,
            "success" => true,
        );
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function dakora_add_example_to_learning_calendar_returns() {
        return new external_single_structure(array(
            'scheduleid' => new external_value(PARAM_INT, 'id of the single added example'),
            'success' => new external_value(PARAM_BOOL, 'status of success, either true (1) or false (0)'),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function dakora_get_descriptors_for_example_parameters() {
        return new external_function_parameters(array(
            'exampleid' => new external_value(PARAM_INT, 'id of example'),
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'userid' => new external_value(PARAM_INT, 'id of user'),
            'forall' => new external_value(PARAM_BOOL, 'for all users = true, for one user = false'),
        ));
    }

    /**
     * get descriptors where example is associated
     * Get descriptors for example
     *
     * @ws-type-read
     * @param int exampleid
     * @return list of descriptors
     */
    public static function dakora_get_descriptors_for_example($exampleid, $courseid, $userid, $forall) {
        global $DB, $USER;

        static::validate_parameters(static::dakora_get_descriptors_for_example_parameters(), array(
            'exampleid' => $exampleid,
            'courseid' => $courseid,
            'userid' => $userid,
            'forall' => $forall,
        ));

        if ($userid == 0 && !$forall) {
            $userid = $USER->id;
        }

        static::require_can_access_course_user($courseid, $userid);

        $non_visibilities = $DB->get_fieldset_select(BLOCK_EXACOMP_DB_DESCVISIBILITY, 'descrid', 'courseid=? AND studentid=? AND visible=0', array($courseid, 0));

        if (!$forall) {
            $non_visibilities_student = $DB->get_fieldset_select(BLOCK_EXACOMP_DB_DESCVISIBILITY, 'descrid', 'courseid=? AND studentid=? AND visible=0', array($courseid, $userid));
        }

        $descriptors = static::_get_descriptors_for_example($exampleid, $courseid, $userid);

        $final_descriptors = array();
        foreach ($descriptors as $descriptor) {
            //to make sure everything has a value
            $descriptor->reviewername = null;
            $descriptor->reviewerid = null;
            $descriptor->id = $descriptor->descriptorid;
            $descriptor->evalniveauid = null;
            $descriptor_topic_mm = $DB->get_record(BLOCK_EXACOMP_DB_DESCTOPICS, array('descrid' => $descriptor->id), '*', IGNORE_MULTIPLE); // get only one topic relation
            $descriptor->topicid = $descriptor_topic_mm->topicid;

            $topic = topic::get($descriptor->topicid);
            if (block_exacomp_is_topic_visible($courseid, $topic, $userid)) {
                $descriptor->numbering = block_exacomp_get_descriptor_numbering($descriptor);
                $descriptor->child = (($parentid = $DB->get_field(BLOCK_EXACOMP_DB_DESCRIPTORS, 'parentid', array('id' => $descriptor->id))) > 0) ? 1 : 0;
                $descriptor->parentid = $parentid;
                //new 16.05.2019 rw:
                //$descriptor->teacherevaluation = $descriptor->evaluation; //redundant? getting block_exacomp_get_comp_eval anyway
                $descriptor->teacherevaluation = -1; // never graded / SZ 07.04.2020
                $descriptor->studentevaluation = -1;
                $descriptor->timestampstudent = 0;
                if (!$forall) {
                    if ($grading = block_exacomp_get_comp_eval($courseid, BLOCK_EXACOMP_ROLE_STUDENT, $userid, BLOCK_EXACOMP_TYPE_DESCRIPTOR, $descriptor->id)) {
                        $descriptor->studentevaluation = ($grading->value !== null) ? $grading->value : -1;
                        $descriptor->timestampstudent = $grading->timestamp;
                    }
                }

                if (!$forall) {
                    if ($grading = block_exacomp_get_comp_eval($courseid, BLOCK_EXACOMP_ROLE_TEACHER, $userid, BLOCK_EXACOMP_TYPE_DESCRIPTOR, $descriptor->id)) {
                        $descriptor->teacherevaluation = ($grading->value !== null) ? $grading->value : -1;
                        $descriptor->additionalinfo = $grading->additionalinfo;
                        $descriptor->evalniveauid = $grading->evalniveauid;
                        $descriptor->timestampteacher = $grading->timestamp;
                        $descriptor->reviewerid = $grading->reviewerid;

                        //Reviewername finden
                        $reviewerid = $grading->reviewerid;
                        $reviewerTeacherFirstname = $DB->get_field('user', 'firstname', array('id' => $reviewerid));
                        $reviewerTeacherLastname = $DB->get_field('user', 'lastname', array('id' => $reviewerid));
                        $reviewerTeacherUsername = $DB->get_field('user', 'username', array('id' => $reviewerid));
                        if ($reviewerTeacherFirstname != null && $reviewerTeacherLastname != null) {
                            $reviewername = $reviewerTeacherFirstname . ' ' . $reviewerTeacherLastname;
                        } else {
                            $reviewername = $reviewerTeacherUsername;
                        }
                        $descriptor->reviewername = $reviewername;
                    }
                }

                if (!$forall) {
                    $descriptor->gradingisold = block_exacomp_is_descriptor_grading_old($descriptor->id, $userid);
                } else {
                    $descriptor->gradingisold = false;
                }

                if (!in_array($descriptor->descriptorid, $non_visibilities) && ((!$forall && !in_array($descriptor->descriptorid, $non_visibilities_student)) || $forall)) {
                    $final_descriptors[] = $descriptor;
                }
            }
        }

        return $final_descriptors;

        //

        //
        //
        //
        //        $descriptor_return->numbering = block_exacomp_get_descriptor_numbering($descriptor);
        //
        //
        //        $descriptor_return->niveautitle = "";
        //        $descriptor_return->niveauid = 0;
        //        if ($descriptor->niveauid) {
        //            $niveau = $DB->get_record(BLOCK_EXACOMP_DB_NIVEAUS, array('id' => $descriptor->niveauid));
        //            $descriptor_return->niveautitle = $niveau->title;
        //            $descriptor_return->niveauid = $niveau->id;
        //        }
        //
        //
        //        $childsandexamples = static::get_descriptor_children($courseid, $descriptorid, $userid, $forall, $crosssubjid, true);
        //
        //        $descriptor_return->children = $childsandexamples->children;
        //
        //        // summary for children gradings
        //        $grading_scheme = block_exacomp_get_grading_scheme($courseid) + 1;
        //
        //        $number_evalniveaus = 1;
        //        if (block_exacomp_use_eval_niveau()) {
        //            $number_evalniveaus = 4;
        //        }
        //
        //        $children_teacherevaluation = array();
        //        for ($i = 0; $i < $number_evalniveaus; $i++) {
        //            $children_teacherevaluation[$i] = array_fill(0, $grading_scheme, 0);
        //        }
        //
        //        $children_studentevaluation = array_fill(0, $grading_scheme, 0);
        //
        //        foreach ($childsandexamples->children as $child) {
        //            if ($child->teacherevaluation > -1) {
        //                $children_teacherevaluation[($child->evalniveauid > 0) ? $child->evalniveauid : 0][$child->teacherevaluation]++;
        //            }
        //            if ($child->studentevaluation > -1) {
        //                $children_studentevaluation[$child->studentevaluation]++;
        //            }
        //        }
        //
        //        $childrengradings = new stdClass();
        //        $childrengradings->teacher = array();
        //        $childrengradings->student = array();
        //
        //
        //        foreach ($children_teacherevaluation as $niveauid => $gradings) {
        //            foreach ($gradings as $key => $grading) {
        //                $childrengradings->teacher[] = array('evalniveauid' => $niveauid, 'value' => $key, 'sum' => $grading);
        //            }
        //
        //        }
        //        foreach ($children_studentevaluation as $key => $value) {
        //            $childrengradings->student[$key] = array('sum' => $value);
        //        }
        //        $descriptor_return->childrengradings = $childrengradings;
        //
        //        // summary for example gradings
        //        $descriptor_return->examples = $childsandexamples->examples;
        //
        //        $examples_teacherevaluation = array();
        //        for ($i = 0; $i < $number_evalniveaus; $i++) {
        //            $examples_teacherevaluation[$i] = array_fill(0, $grading_scheme, 0);
        //        }
        //
        //        $examples_studentevaluation = array_fill(0, $grading_scheme, 0);
        //
        //        foreach ($childsandexamples->examples as $example) {
        //            if ($example->teacherevaluation > -1) {
        //                $examples_teacherevaluation[($example->evalniveauid > 0) ? $example->evalniveauid : 0][$example->teacherevaluation]++;
        //            }
        //            if ($example->studentevaluation > -1) {
        //                $examples_studentevaluation[$example->studentevaluation]++;
        //            }
        //
        //            $example->id = $example->exampleid;
        //            $solution_visible = block_exacomp_is_example_solution_visible($courseid, $example, $userid);
        //            $example->solution_visible = $solution_visible;
        //        }
        //        $examplegradings = new stdClass();
        //        $examplegradings->teacher = array();
        //        $examplegradings->student = array();
        //
        //        foreach ($examples_teacherevaluation as $niveauid => $gradings) {
        //            foreach ($gradings as $key => $grading) {
        //                $examplegradings->teacher[] = array('evalniveauid' => $niveauid, 'value' => $key, 'sum' => $grading);
        //            }
        //
        //        }
        //
        //        foreach ($examples_studentevaluation as $key => $value) {
        //            $examplegradings->student[$key] = array('sum' => $value);
        //        }
        //        $descriptor_return->examplegradings = $examplegradings;
        //        // example statistics
        //        $descriptor_return->examplestotal = $childsandexamples->examplestotal;
        //        $descriptor_return->examplesvisible = $childsandexamples->examplesvisible;
        //        $descriptor_return->examplesinwork = $childsandexamples->examplesinwork;
        //        $descriptor_return->examplesedited = $childsandexamples->examplesedited;
        //
        //        $descriptor_return->hasmaterial = true;
        //        if (empty($childsandexamples->examples)) {
        //            $descriptor_return->hasmaterial = false;
        //        }
        //
        //        $descriptor_return->visible = (block_exacomp_is_descriptor_visible($courseid, $descriptor, $userid)) ? 1 : 0;
        //        $descriptor_return->used = (block_exacomp_descriptor_used($courseid, $descriptor, $userid)) ? 1 : 0;
        //

        //
        //        return $descriptor_return;
    }

    /*
	 * descriptorid
	 * teacherevaluation
	 * numbering
	 * parentid
	 * title
	 * topicid
	 * studentevaluation
	 * gradingisold
	 * niveauid
	 *
	 *
	 *
	 *
	 *
	 */

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function dakora_get_descriptors_for_example_returns() {
        return new external_multiple_structure(new external_single_structure(array(
            'descriptorid' => new external_value(PARAM_INT, 'id of descriptor'),
            'title' => new external_value(PARAM_TEXT, 'title of descriptor'),
            'teacherevaluation' => new external_value(PARAM_INT, 'evaluation of descriptor'),
            'studentevaluation' => new external_value(PARAM_INT, 'student evaluation of descriptor'),
            'evalniveauid' => new external_value(PARAM_INT, 'evaluation niveau id'),
            'niveauid' => new external_value(PARAM_INT, 'id of niveau'),
            //            'niveautitle' => new external_value(PARAM_TEXT, 'title of niveau'),
            'additionalinfo' => new external_value(PARAM_FLOAT, 'additional grading for descriptor'),
            'topicid' => new external_value(PARAM_INT, 'id of topic'),
            'numbering' => new external_value(PARAM_TEXT, 'descriptor numbering'),
            'child' => new external_value(PARAM_BOOL, 'true: child, false: parent'),
            'parentid' => new external_value(PARAM_INT, 'parentid if child, 0 otherwise'),
            'gradingisold' => new external_value(PARAM_BOOL, 'true when there are newer gradings in the childcompetences', false),
            'reviewerid' => new external_value(PARAM_INT, 'id of reviewer'),
            'reviewername' => new external_value(PARAM_TEXT, 'name of reviewer'),
        )));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function dakora_get_example_grading_parameters() {
        return new external_function_parameters(array(
            'exampleid' => new external_value(PARAM_INT, 'id of example'),
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'userid' => new external_value(PARAM_INT, 'id of user, if 0 current user'),
        ));
    }

    /**
     * get student and teacher evaluation for example
     * Get example grading for user
     *
     * @ws-type-read
     * @param int exampleid
     *            int courseid
     *            int userid
     * @return list of descriptors
     */
    public static function dakora_get_example_grading($exampleid, $courseid, $userid) {
        global $DB, $USER;

        static::validate_parameters(static::dakora_get_example_grading_parameters(), array(
            'exampleid' => $exampleid,
            'courseid' => $courseid,
            'userid' => $userid,
        ));

        if ($userid == 0) {
            $userid = $USER->id;
        }

        static::require_can_access_course_user($courseid, $userid);
        static::require_can_access_example($exampleid, $courseid);

        $student = $DB->get_record('user', array(
            'id' => $userid,
        ));

        $student->examples = block_exacomp_get_user_examples_by_course($student, $courseid);

        $teacherevaluation = -1;
        if (isset($student->examples->teacher[$exampleid])) {
            $teacherevaluation = $student->examples->teacher[$exampleid];
        }

        $studentevaluation = -1;
        if (isset($student->examples->student[$exampleid])) {
            $studentevaluation = $student->examples->student[$exampleid];
        }

        $evalniveauid = null;
        if (isset($student->examples->niveau[$exampleid])) {
            $evalniveauid = $student->examples->niveau[$exampleid];
        }

        return array(
            'teacherevaluation' => $teacherevaluation,
            'studentevaluation' => $studentevaluation,
            'evalniveauid' => $evalniveauid,
        );
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function dakora_get_example_grading_returns() {
        return new external_single_structure(array(
            'teacherevaluation' => new external_value(PARAM_INT, 'teacher evaluation for student and example'),
            'studentevaluation' => new external_value(PARAM_INT, 'self evaluation for example'),
            'evalniveauid' => new external_value(PARAM_INT, 'evaluation niveau id'),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function dakora_get_topic_grading_parameters() {
        return new external_function_parameters(array(
            'topicid' => new external_value(PARAM_INT, 'id of topic'),
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'userid' => new external_value(PARAM_INT, 'id of user, if 0 current user'),
        ));
    }

    /**
     * get student and teacher evaluation for topic
     * Get topic grading for user
     *
     * @ws-type-read
     * @param int topicid
     *            int courseid
     *            int userid
     * @return grading
     */
    public static function dakora_get_topic_grading($topicid, $courseid, $userid) {
        global $DB, $USER;

        static::validate_parameters(static::dakora_get_topic_grading_parameters(), array(
            'topicid' => $topicid,
            'courseid' => $courseid,
            'userid' => $userid,
        ));

        if ($userid == 0) {
            $userid = $USER->id;
        }

        static::require_can_access_course_user($courseid, $userid);

        $student = $DB->get_record('user', array(
            'id' => $userid,
        ));

        $student = block_exacomp_get_user_topics_by_course($student, $courseid);

        $teacherevaluation = -1;
        if (isset($student->topics->teacher[$topicid])) {
            $teacherevaluation = $student->topics->teacher[$topicid];
        }

        $additionalinfo = -1;
        if (isset($student->topics->teacher_additional_grading[$topicid])) {
            $additionalinfo = $student->topics->teacher_additional_grading[$topicid];
        }

        $studentevaluation = -1;
        if (isset($student->topics->student[$topicid])) {
            $studentevaluation = $student->topics->student[$topicid];
        }

        $evalniveauid = -1;
        if (isset($student->topics->niveau[$topicid])) {
            $evalniveauid = $student->topics->niveau[$topicid];
        }

        $timestampteacher = 0;
        if (isset($student->topics->timestamp_teacher[$topicid])) {
            $timestampteacher = $student->topics->timestamp_teacher[$topicid];
        }

        $timestampstudent = 0;
        if (isset($student->topics->timestamp_student[$topicid])) {
            $timestampstudent = $student->topics->timestamp_student[$topicid];
        }

        return array(
            'teacherevaluation' => $teacherevaluation,
            'additionalinfo' => $additionalinfo,
            'studentevaluation' => $studentevaluation,
            'evalniveauid' => $evalniveauid,
            'timestampteacher' => $timestampteacher,
            'timestampstudent' => $timestampstudent,
        );
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function dakora_get_topic_grading_returns() {
        return new external_single_structure(array(
            'teacherevaluation' => new external_value(PARAM_INT, 'teacher evaluation for student and topic'),
            'additionalinfo' => new external_value(PARAM_FLOAT, 'teacher additional info for student and topic'),
            'studentevaluation' => new external_value(PARAM_INT, 'self evaluation for topic'),
            'evalniveauid' => new external_value(PARAM_INT, 'evaluation niveau id'),
            'timestampteacher' => new external_value(PARAM_INT, 'timestamp for teacher evaluation'),
            'timestampstudent' => new external_value(PARAM_INT, 'timestamp for student evaluation'),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function dakora_get_subject_grading_parameters() {
        return new external_function_parameters(array(
            'subjectid' => new external_value(PARAM_INT, 'id of subject'),
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'userid' => new external_value(PARAM_INT, 'id of user, if 0 current user'),
        ));
    }

    /**
     * get student and teacher evaluation for subject
     * Get subject grading for user
     *
     * @ws-type-read
     * @param int subjectid
     *            int courseid
     *            int userid
     * @return grading
     */
    public static function dakora_get_subject_grading($subjectid, $courseid, $userid) {
        global $DB, $USER;

        static::validate_parameters(static::dakora_get_subject_grading_parameters(), array(
            'subjectid' => $subjectid,
            'courseid' => $courseid,
            'userid' => $userid,
        ));

        if ($userid == 0) {
            $userid = $USER->id;
        }

        static::require_can_access_course_user($courseid, $userid);

        $student = $DB->get_record('user', array(
            'id' => $userid,
        ));

        $student = block_exacomp_get_user_subjects_by_course($student, $courseid);

        $teacherevaluation = -1;
        if (isset($student->subjects->teacher[$subjectid])) {
            $teacherevaluation = $student->subjects->teacher[$subjectid];
        }

        $additionalinfo = -1;
        if (isset($student->subjects->teacher_additional_grading[$subjectid])) {
            $additionalinfo = $student->subjects->teacher_additional_grading[$subjectid];
        }

        $studentevaluation = -1;
        if (isset($student->subjects->student[$subjectid])) {
            $studentevaluation = $student->subjects->student[$subjectid];
        }

        $evalniveauid = -1;
        if (isset($student->subjects->niveau[$subjectid])) {
            $evalniveauid = $student->subjects->niveaus[$subjectid];
        }

        $timestampteacher = 0;
        if (isset($student->subjects->timestamp_teacher[$subjectid])) {
            $timestampteacher = $student->subjects->timestamp_teacher[$subjectid];
        }

        $timestampstudent = 0;
        if (isset($student->subjects->timestamp_student[$subjectid])) {
            $timestampstudent = $student->timestamp_student[$subjectid];
        }

        return array(
            'teacherevaluation' => $teacherevaluation,
            'additionalinfo' => $additionalinfo,
            'studentevaluation' => $studentevaluation,
            'evalniveauid' => $evalniveauid,
            'timestampteacher' => $timestampteacher,
            'timestampstudent' => $timestampstudent,
        );
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function dakora_get_subject_grading_returns() {
        return new external_single_structure(array(
            'teacherevaluation' => new external_value(PARAM_INT, 'teacher evaluation for student and subject'),
            'additionalinfo' => new external_value(PARAM_FLOAT, 'teacher additional info for student and subject'),
            'studentevaluation' => new external_value(PARAM_INT, 'self evaluation for subject'),
            'evalniveauid' => new external_value(PARAM_INT, 'evaluation niveau id'),
            'timestampteacher' => new external_value(PARAM_INT, 'timestamp for teacher evaluation'),
            'timestampstudent' => new external_value(PARAM_INT, 'timestamp for student evaluation'),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function dakora_get_user_role_parameters() {
        return new external_function_parameters(array());
    }

    /**
     * get user role 1= trainer, 2= student
     * return 1 for trainer
     * 2 for student
     * 0 if false
     *
     * @ws-type-read
     * @return array role
     */
    public static function dakora_get_user_role() {
        global $USER;

        static::validate_parameters(static::dakora_get_user_role_parameters(), array());

        $courses = static::get_courses($USER->id);

        foreach ($courses as $course) {
            $context = context_course::instance($course["courseid"]);

            $isTeacher = block_exacomp_is_teacher($context);

            if ($isTeacher) {
                return (object)["role" => BLOCK_EXACOMP_WS_ROLE_TEACHER];
            }
        }

        return (object)["role" => BLOCK_EXACOMP_WS_ROLE_STUDENT];
    }

    /**
     * Returns description of method return values
     *
     * @return external_multiple_structure
     */
    public static function dakora_get_user_role_returns() {
        return new external_function_parameters(array(
            'role' => new external_value(PARAM_INT, '1=trainer, 2=student'),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function dakora_get_students_and_groups_for_course_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
        ));
    }

    /**
     * get list of students for course
     *
     * @ws-type-read
     * @param $courseid
     * @return array
     */
    public static function dakora_get_students_and_groups_for_course($courseid) {
        global $PAGE, $OUTPUT;
        static::validate_parameters(static::dakora_get_students_and_groups_for_course_parameters(), array(
            'courseid' => $courseid,
        ));

        static::require_can_access_course($courseid);
        // TODO: only for teacher? fjungwirth: not necessary, students can also see other course participants in Moodle

        $studentsAndGroups = array();

        //TODO: check if the groups are needed, some schools don't user this RW
        //a setting from exacomp or a parameter from dakora... setting from exacomp preferred
        //Add groups as well:
        $groups = groups_get_all_groups($courseid);
        $studentsAndGroups['groups'] = $groups;
        foreach ($groups as $group) {
            $picture = get_group_picture_url($group, $courseid);
            if ($picture != null) {
                $picture->size = 50;
                $group->picture = $picture->out();
            } else {
                $group->picture = $OUTPUT->pix_url('i/group', '')->out();
            }

        }

        $students = block_exacomp_get_students_by_course($courseid);

        foreach ($students as $student) {
            $student->studentid = $student->id;
            $student->imagealt = '';
            $picture = new user_picture($student);
            $picture->size = 50;
            $student->profilepicture = $picture->get_url($PAGE)->out();
        }
        $studentsAndGroups['students'] = $students;

        return $studentsAndGroups;
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function dakora_get_students_and_groups_for_course_returns() {
        return new external_single_structure(array(
            'students' => new external_multiple_structure(new external_single_structure(array(
                'studentid' => new external_value(PARAM_INT, 'id of student'),
                'firstname' => new external_value(PARAM_TEXT, 'firstname of student'),
                'lastname' => new external_value(PARAM_TEXT, 'lastname of student'),
                'profilepicture' => new external_value(PARAM_TEXT, 'link to  profile picture'),
            ))),
            'groups' => new external_multiple_structure(new external_single_structure(array(
                'id' => new external_value(PARAM_INT, 'id of group'),
                'name' => new external_value(PARAM_TEXT, 'name of group'),
                'picture' => new external_value(PARAM_TEXT, 'link to  picture'),
            )))));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function dakora_get_students_for_teacher_parameters() {
        return new external_function_parameters(array(
            'userid' => new external_value(PARAM_INT, 'id of user'),
        ));
    }

    /**
     * get list of students that are enrolled in any course of a teacher
     *
     * @ws-type-read
     * @param userid
     * @return array
     */
    public static function dakora_get_students_for_teacher($userid) {
        global $PAGE;
        static::validate_parameters(static::dakora_get_students_for_teacher_parameters(), array(
            'userid' => $userid,
        ));

        $students = array();
        $courses = block_exacomp_get_exacomp_courses($userid);

        foreach ($courses as $course) {
            $students = ($students + block_exacomp_get_students_by_course($course->id));
        }

        return $students;

    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function dakora_get_students_for_teacher_returns() {
        return new external_multiple_structure(new external_single_structure(array(
            'id' => new external_value(PARAM_INT, 'id of student'),
            'firstname' => new external_value(PARAM_TEXT, 'firstname of student'),
            'lastname' => new external_value(PARAM_TEXT, 'lastname of student'),
        )));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function dakora_get_teachers_for_student_parameters() {
        return new external_function_parameters(array(
            'userid' => new external_value(PARAM_INT, 'id of user'),
        ));
    }

    /**
     * get list of teachers in any course of the student
     *
     * @ws-type-read
     * @param userid
     * @return array
     */
    public static function dakora_get_teachers_for_student($userid) {
        global $PAGE;
        static::validate_parameters(static::dakora_get_teachers_for_student_parameters(), array(
            'userid' => $userid,
        ));

        $teachers = array();
        $courses = block_exacomp_get_exacomp_courses($userid);

        foreach ($courses as $course) {
            $teachers = ($teachers + block_exacomp_get_teachers_by_course($course->id));
        }

        return $teachers;

    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function dakora_get_teachers_for_student_returns() {
        return new external_multiple_structure(new external_single_structure(array(
            'id' => new external_value(PARAM_INT, 'id of teacher'),
            'firstname' => new external_value(PARAM_TEXT, 'firstname of teacher'),
            'lastname' => new external_value(PARAM_TEXT, 'lastname of teacher'),
        )));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function dakora_get_examples_pool_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'userid' => new external_value(PARAM_INT, 'id of user, if 0 current user'),
        ));
    }

    /**
     * get list of examples for weekly schedule pool
     * Get examples for pool
     *
     * @ws-type-read
     * @param int courseid
     *            int userid
     * @return list of descriptors
     */
    public static function dakora_get_examples_pool($courseid, $userid) {
        global $USER, $DB;

        static::validate_parameters(static::dakora_get_examples_pool_parameters(), array(
            'courseid' => $courseid,
            'userid' => $userid,
        ));

        if ($userid == 0 && !block_exacomp_is_teacher($courseid)) {
            $userid = $USER->id;
        }

        $isTeacher = block_exacomp_is_teacher_in_any_course();

        static::require_can_access_course_user($courseid, $userid);

        $examples = block_exacomp_get_examples_for_pool($userid, $courseid);

        foreach ($examples as $exampleKey => $example) {
            if (!$isTeacher) {
                if ($example->is_teacherexample) {
                    unset($examples[$exampleKey]);
                    continue;
                }
            }

            // it seems like dakora_get_examples_pool_returns has problems with the example titles which contain html tags, so:
            $example->title = static::custom_htmltrim(strip_tags($example->title));
            // 		    //Taxonomies:
            $taxonomies = '';
            $taxids = '';
            foreach (block_exacomp_get_taxonomies_by_example($example->exampleid) as $tax) {
                if ($taxonomies == '') { //first run, no ","
                    $taxonomies .= static::custom_htmltrim($tax->title);
                    $taxids .= $tax->id;
                } else {
                    $taxonomies .= ',' . static::custom_htmltrim($tax->title);
                    $taxids .= ',' . $tax->id;
                }
            }
            $example->exampletaxonomies = $taxonomies;
            $example->exampletaxids = $taxids;
            // TODO: remove exampletaxonomies and exampletaxids after testing, if no app uses this webservice in that way. Taxonomies are now sent in an object
            $example->taxonomies = block_exacomp_get_taxonomies_by_example($example->exampleid);

            $example->state = block_exacomp_get_dakora_state_for_example($example->courseid, $example->exampleid, $userid);

            $example_course = $DB->get_record('course', array('id' => $example->courseid));
            $example->courseshortname = $example_course->shortname;
            $example->coursefullname = $example_course->fullname;

            // add the item information to the examples

            // this provides minimal information, and is what we need for now
            $item = block_exacomp_get_current_item_for_example($userid, $example->exampleid);
            $example->itemstatus = block_exacomp_get_human_readable_item_status($item ? $item->status : null);

            // set lastmodifiedbyid, if not yet set
            $example->lastmodifiedbyid = $example->lastmodifiedbyid ?: $example->creatorid;

            // this would include a lot of information, but still be an overkill
            //$items = block_exacomp_get_items_for_competence($userid, $example->exampleid, BLOCK_EXACOMP_TYPE_EXAMPLE);
            //if($items){
            //    // it is ordered by timecreated ==> get the newest one. Currently it is an array
            //    $example->item = reset($items); // reset returns the first element
            //}

            // this would include all the information we need, but be an overkill and less performant
            ////$example->item = block_exacomp_get_current_item_for_example($userid, $example->exampleid); this is a very old function. does not provide enough information
            //$items = static::diggrplus_get_examples_and_items($courseid, $userid, $example->exampleid, BLOCK_EXACOMP_TYPE_EXAMPLE);
            //if($items){
            //    // it is ordered by timecreated ==> get the newest one. Currently it is an array
            //    $example->item = reset($items); // reset returns the first element
            //}
        }


        return $examples;
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function dakora_get_examples_pool_returns() {
        return new external_multiple_structure(new external_single_structure(array(
            'exampleid' => new external_value(PARAM_INT, 'id of example'),
            'title' => new external_value(PARAM_TEXT, 'title of example'),
            'student_evaluation' => new external_value(PARAM_INT, 'self evaluation of student'),
            'teacher_evaluation' => new external_value(PARAM_INT, 'evaluation of teacher'),
            'evalniveauid' => new external_value(PARAM_INT, 'evaluation niveau id'),
            'courseid' => new external_value(PARAM_INT, 'example course'),
            'state' => new external_value(PARAM_INT, 'state of example'),
            'scheduleid' => new external_value(PARAM_INT, 'id in schedule context'),
            'creatorid' => new external_value(PARAM_INT, 'example added to pool by userid'),
            'lastmodifiedbyid' => new external_value(PARAM_INT, 'example pool state last edited by userid'),
            'courseshortname' => new external_value(PARAM_TEXT, 'shortname of example course'),
            'coursefullname' => new external_value(PARAM_TEXT, 'full name of example course'),
            'exampletaxonomies' => new external_value(PARAM_TEXT, 'taxonomies seperated by comma', VALUE_OPTIONAL),
            'exampletaxids' => new external_value(PARAM_TEXT, 'taxids seperated by comma', VALUE_OPTIONAL),
            'source' => new external_value(PARAM_TEXT, 'tag where the material comes from', VALUE_OPTIONAL),
            'timeframe' => new external_value(PARAM_TEXT, 'timeframe, suggested time'),
            'itemstatus' => new external_value(PARAM_TEXT, 'status of the item as text ENUM(new, inprogress, submitted, completed)'),
            'is_overdue' => new external_value(PARAM_BOOL),
            'taxonomies' => new external_multiple_structure(new external_single_structure([
                'id' => new external_value(PARAM_INT, 'id'),
                'title' => new external_value(PARAM_TEXT, 'name'),
                'source' => new external_value(PARAM_TEXT, 'source'),
            ]), 'values'),
        )));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function dakora_get_examples_trash_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'userid' => new external_value(PARAM_INT, 'id of user, if 0 current user'),
        ));
    }

    /**
     * get examples for trash bin
     * Get examples for trash
     *
     * @ws-type-read
     * @param int courseid
     *            int userid
     * @return list of descriptors
     */
    public static function dakora_get_examples_trash($courseid, $userid) {
        global $USER, $DB;

        static::validate_parameters(static::dakora_get_examples_trash_parameters(), array(
            'courseid' => $courseid,
            'userid' => $userid,
        ));

        if ($userid == 0 && !block_exacomp_is_teacher($courseid)) {
            $userid = $USER->id;
        }

        static::require_can_access_course_user($courseid, $userid);

        $examples = block_exacomp_get_examples_for_trash($userid, $courseid);

        foreach ($examples as $example) {
            $example->title = static::custom_htmltrim(strip_tags($example->title));
            $example->state = block_exacomp_get_dakora_state_for_example($example->courseid, $example->exampleid, $userid);

            $example_course = $DB->get_record('course', array('id' => $example->courseid));
            $example->courseshortname = $example_course->shortname;
            $example->coursefullname = $example_course->fullname;
        }

        return $examples;
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function dakora_get_examples_trash_returns() {
        return new external_multiple_structure(new external_single_structure(array(
            'exampleid' => new external_value(PARAM_INT, 'id of example'),
            'title' => new external_value(PARAM_TEXT, 'title of example'),
            'student_evaluation' => new external_value(PARAM_INT, 'self evaluation of student'),
            'teacher_evaluation' => new external_value(PARAM_INT, 'evaluation of teacher'),
            'evalniveauid' => new external_value(PARAM_INT, 'evaluation niveau id'),
            'courseid' => new external_value(PARAM_INT, 'example course'),
            'state' => new external_value(PARAM_INT, 'state of example'),
            'scheduleid' => new external_value(PARAM_INT, 'id in schedule context'),
            'courseshortname' => new external_value(PARAM_TEXT, 'shortname of example course'),
            'coursefullname' => new external_value(PARAM_TEXT, 'full name of example course'),
            'source' => new external_value(PARAM_TEXT, 'tag where the material comes from', VALUE_OPTIONAL),
        )));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function dakora_set_example_time_slot_parameters() {
        return new external_function_parameters(array(
            'scheduleid' => new external_value(PARAM_INT, 'id in schedule context'),
            'start' => new external_value(PARAM_INT, 'start timestamp'),
            'end' => new external_value(PARAM_INT, 'end timestamp'),
            'deleted' => new external_value(PARAM_INT, 'delete item'),
        ));
    }

    /**
     * set start and end time for example
     * set example time slot
     *
     * @ws-type-write
     * @param int courseid
     *            int exampleid
     *            int userid
     *            int start
     *            int end
     * @return list of descriptors
     */
    public static function dakora_set_example_time_slot($scheduleid, $start, $end, $deleted) {
        global $DB;
        static::validate_parameters(static::dakora_set_example_time_slot_parameters(), array(
            'scheduleid' => $scheduleid,
            'start' => $start,
            'end' => $end,
            'deleted' => $deleted,
        ));

        // TODO: check example
        $entry = block_exacomp_set_example_start_end($scheduleid, $start, $end, $deleted);

        //Get the example in order to get the suggested timeframe
        //        static::require_can_access_example($entry->exampleid, $entry-courseid)   not needed since it is sure I can access it already
        $example = $DB->get_record(BLOCK_EXACOMP_DB_EXAMPLES, array(
            'id' => $entry->exampleid,
        ));

        //1h30 or 01h30 or 1:30 or 01:30 is allowed format
        if (strpos($example->timeframe, 'h')) {
            $time = explode('h', $example->timeframe);
        } else if (strpos($example->timeframe, ':')) {
            $time = explode(':', $example->timeframe);
        } else {
            return array(
                "timeremaining" => '0',
                'timeplanned' => '0',
                'timesuggested' => '0',
                "success" => true,
            );
        }

        $timeSeconds = $time[0] * 60 * 60 + $time[1] * 60;
        $remainingtime = $timeSeconds;
        $timeplanned = 0;
        //Get the other scheduled instances of this example
        $schedule = g::$DB->get_records(BLOCK_EXACOMP_DB_SCHEDULE, ['exampleid' => $entry->exampleid]);
        foreach ($schedule as $scheduledmaterials) {
            $remainingtime -= ($scheduledmaterials->endtime - $scheduledmaterials->start);
            $timeplanned += ($scheduledmaterials->endtime - $scheduledmaterials->start);
        }
        if ($remainingtime > 0) {
            $remaininghours = floor($remainingtime / 3600);
            $remainingminutes = ($remainingtime % 3600) / 60;
            $remainingtime = $remaininghours . 'h' . $remainingminutes . 'min';
        } else if ($remainingtime < 0) {
            $remaininghours = ceil($remainingtime / 3600);
            $remainingminutes = -1 * (($remainingtime % 3600) / 60); //time -1 to make it positive ==>    remaining time =    -4h30min e.g.
            $remainingtime = $remaininghours . 'h' . $remainingminutes . 'min';
        }

        $plannedhours = floor($timeplanned / 3600);
        $plannminutes = ($timeplanned % 3600) / 60;
        $timeplanned = $plannedhours . 'h' . $plannminutes . 'min';

        return array(
            "timeremaining" => $remainingtime,
            "timeplanned" => $timeplanned,
            "timesuggested" => $example->timeframe,
            "success" => true,
        );
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function dakora_set_example_time_slot_returns() {
        return new external_single_structure(array(
            'timeremaining' => new external_value(PARAM_TEXT, 'time planned minus timeframe = timeremaining'),
            'timeplanned' => new external_value(PARAM_TEXT, 'time planned '),
            'timesuggested' => new external_value(PARAM_TEXT, 'timeframe'),
            'success' => new external_value(PARAM_BOOL, 'status of success, either true (1) or false (0)'),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function dakora_remove_example_from_schedule_parameters() {
        return new external_function_parameters(array(
            'scheduleid' => new external_value(PARAM_INT, 'id of schedule entry'),
        ));
    }

    /**
     * remove example from weekly schedule
     * remove example from time slot
     *
     * @ws-type-write
     * @param * @return list of descriptors
     */
    public static function dakora_remove_example_from_schedule($scheduleid) {

        static::validate_parameters(static::dakora_remove_example_from_schedule_parameters(), array(
            'scheduleid' => $scheduleid,
        ));

        // fjungwirth: permissions are checked in lib.php
        block_exacomp_remove_example_from_schedule($scheduleid);

        return array(
            "success" => true,
        );

    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function dakora_remove_example_from_schedule_returns() {
        return new external_single_structure(array(
            'success' => new external_value(PARAM_BOOL, 'status of success, either true (1) or false (0)'),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function dakora_add_examples_to_schedule_for_all_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
        ));
    }

    /**
     * add examples to the schedules of all course students
     * remove example from time slot
     *
     * @ws-type-write
     * @param * @return list of descriptors
     */
    public static function dakora_add_examples_to_schedule_for_all($courseid) {

        static::validate_parameters(static::dakora_add_examples_to_schedule_for_all_parameters(), array(
            'courseid' => $courseid,
        ));

        // permissions are checked in lib.php
        block_exacomp_add_examples_to_schedule_for_all($courseid);

        return array(
            "success" => true,
        );

    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function dakora_add_examples_to_schedule_for_all_returns() {
        return new external_single_structure(array(
            'success' => new external_value(PARAM_BOOL, 'status of success, either true (1) or false (0)'),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function dakora_get_examples_for_time_slot_parameters() {
        return new external_function_parameters(array(
            'userid' => new external_value(PARAM_INT, 'id of user, if 0 current user'),
            'start' => new external_value(PARAM_INT, 'start timestamp'),
            'end' => new external_value(PARAM_INT, 'end timestamp'),
        ));
    }

    /**
     * get examples for a special start to end period (e.g. day)
     * Get examples for time slot
     *
     * @ws-type-read
     * @param int userid
     *            int start
     *            int end
     * @return list of descriptors
     */
    public static function dakora_get_examples_for_time_slot($userid, $start, $end) {
        global $USER, $DB;

        static::validate_parameters(static::dakora_get_examples_for_time_slot_parameters(), array(
            'userid' => $userid,
            'start' => $start,
            'end' => $end,
        ));

        $isTeacher = block_exacomp_is_teacher_in_any_course();

        if ($userid == 0 && !$isTeacher) {
            $userid = $USER->id;
        }

        if ($userid != 0) {
            static::require_can_access_user($userid);
        }

        $examples = block_exacomp_get_examples_for_start_end_all_courses($userid, $start, $end);

        foreach ($examples as $exampleKey => $example) {
            // filter by is_teacherexample (only for teachers)
            // TODO: may move this code to block_exacomp_get_examples_for_start_end_all_courses()? (is this needed to other example lists?)
            if (!$isTeacher) {
                if ($example->is_teacherexample) {
                    unset($examples[$exampleKey]);
                    continue;
                }
            }

            $example->title = static::custom_htmltrim(strip_tags($example->title));
            $example->end = $example->endtime;  //because field was renamed to endtime in exacomp, not in dakora
            // 		    //Taxonomies:
            $taxonomies = '';
            $taxids = '';
            foreach (block_exacomp_get_taxonomies_by_example($example->exampleid) as $tax) {
                if ($taxonomies == '') { //first run, no ","
                    $taxonomies .= static::custom_htmltrim($tax->title);
                    $taxids .= $tax->id;
                } else {
                    $taxonomies .= ',' . static::custom_htmltrim($tax->title);
                    $taxids .= ',' . $tax->id;
                }
            }
            $example->exampletaxonomies = $taxonomies;
            $example->exampletaxids = $taxids;
            // TODO: remove exampletaxonomies and exampletaxids after testing, if no app uses this webservice in that way. Taxonomies are now sent in an object
            $example->taxonomies = block_exacomp_get_taxonomies_by_example($example->exampleid);

            $example->state = block_exacomp_get_dakora_state_for_example($example->courseid, $example->exampleid, $userid);
            $example_course = $DB->get_record('course', array(
                'id' => $example->courseid,
            ));

            $example->courseshortname = $example_course->shortname;
            $example->coursefullname = $example_course->fullname;

            //Check if editable or locked
            //For blocking events if student: flag if this is my own event (can edit) or one the teacher gave to me. Teacher can edit anyways
            $example->editable = true;
            if (($example->state > 3 && $example->state < 9)) {
                $example->editable = false;
            } else if ($example->state == 9) {
                if ($USER->id == $example->creatorid) {
                    $example->editable = true;
                } else {
                    $example->editable = false;
                }
            }


            // add the item information to the examples
            // this provides minimal information, and is what we need for now
            $item = block_exacomp_get_current_item_for_example($userid, $example->exampleid);
            $example->itemstatus = block_exacomp_get_human_readable_item_status($item ? $item->status : null);

            // set lastmodifiedbyid, if not yet set
            $example->lastmodifiedbyid = $example->lastmodifiedbyid ?: $example->creatorid;
        }


        return $examples;
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function dakora_get_examples_for_time_slot_returns() {
        return new external_multiple_structure(new external_single_structure(array(
            'exampleid' => new external_value(PARAM_INT, 'id of example'),
            'title' => new external_value(PARAM_TEXT, 'title of example'),
            'start' => new external_value(PARAM_INT, 'start of event'),
            'end' => new external_value(PARAM_INT, 'end of event'),
            'timeframe' => new external_value(PARAM_TEXT, 'timeframe'),
            'student_evaluation' => new external_value(PARAM_INT, 'self evaluation of student'),
            'teacher_evaluation' => new external_value(PARAM_INT, 'evaluation of teacher'),
            'evalniveauid' => new external_value(PARAM_INT, 'evaluation niveau id'),
            'courseid' => new external_value(PARAM_INT, 'example course'),
            'state' => new external_value(PARAM_INT, 'state of example'),
            'scheduleid' => new external_value(PARAM_INT, 'id in schedule context'),
            'creatorid' => new external_value(PARAM_INT, 'example added to pool by userid'),
            'lastmodifiedbyid' => new external_value(PARAM_INT, 'example pool state last edited by userid'),
            'addedtoschedulebyid' => new external_value(PARAM_INT, 'example added to plan by userid (may be 0, if added outside of dakora!)'),
            'courseshortname' => new external_value(PARAM_TEXT, 'shortname of example course'),
            'coursefullname' => new external_value(PARAM_TEXT, 'full name of example course'),
            'exampletaxonomies' => new external_value(PARAM_TEXT, 'taxonomies seperated by comma', VALUE_OPTIONAL),
            'exampletaxids' => new external_value(PARAM_TEXT, 'taxids seperated by comma', VALUE_OPTIONAL),
            'taxonomies' => new external_multiple_structure(new external_single_structure([
                'id' => new external_value(PARAM_INT, 'id'),
                'title' => new external_value(PARAM_TEXT, 'name'),
                'source' => new external_value(PARAM_TEXT, 'source'),
            ]), 'values'),
            'source' => new external_value(PARAM_TEXT, 'tag where the material comes from', VALUE_OPTIONAL),
            'schedule_marker' => new external_value(PARAM_TEXT, 'tag for the marker on the material in the weekly schedule', VALUE_OPTIONAL),
            'editable' => new external_value(PARAM_BOOL, 'for blocking events: show if editable (special for dakora?)'),
            'itemstatus' => new external_value(PARAM_TEXT, 'status of the item as text ENUM(new, inprogress, submitted, completed)'),
        )));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function dakora_get_cross_subjects_by_course_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'userid' => new external_value(PARAM_INT, 'id of user, if 0 current user'),
            'forall' => new external_value(PARAM_BOOL, 'for all users = true, for one user = false'),
            'allcrosssubjects' => new external_value(PARAM_BOOL, 'for all allcross subjects = true (no course selected)', VALUE_DEFAULT, 0),
        ));
    }

    /**
     * get cross subjects for an user in course context (allways all crosssubjs, even if not associated)
     * Get cross subjects
     *
     * @ws-type-read
     * @param int courseid
     * @param int userid
     * @param int $allcrosssubjects
     * @return array list of crosssubjects
     */
    public static function dakora_get_cross_subjects_by_course($courseid, $userid, $forall, $allcrosssubjects) {
        global $USER, $DB;

        static::validate_parameters(static::dakora_get_cross_subjects_by_course_parameters(), array(
            'courseid' => $courseid,
            'userid' => $userid,
            'forall' => $forall,
            'allcrosssubjects' => $allcrosssubjects,
        ));

        if ($userid == 0 && !$forall) {
            $userid = $USER->id;
        }

        //		$cross_subjects_all = block_exacomp_get_cross_subjects_by_course($courseid);
        if ($allcrosssubjects) {
            $cross_subjects = block_exacomp_get_crossubjects_by_teacher($USER->id);
        } else {
            $cross_subjects = block_exacomp_get_cross_subjects_by_course($courseid, $userid);
        }

        if ($forall) {
            static::require_can_access_course($courseid, $allcrosssubjects);
        } else if ($allcrosssubjects) {
            foreach ($cross_subjects as $cross_subject) {
                static::require_can_access_course_user($cross_subject->courseid, $userid);
            }
        } else {
            static::require_can_access_course_user($courseid, $userid);
        }

        $cross_subjects_visible = $cross_subjects;
        $all_cross_subjects = $cross_subjects;

        //if for all return only common cross subjects
        if ($forall) {
            $cross_subjects_visible = array();
            $students = [];
            if ($courseid > 0) {
                // get students for selected course. If we have $allcrossubjects - list of students will be changed to list of crossubject course in foreach
                $students = block_exacomp_get_students_by_course($courseid);
            }
            foreach ($cross_subjects as $cross_subject) {
                if ($cross_subject->shared == 1) {
                    $cross_subjects_visible[$cross_subject->id] = $cross_subject;
                } else {
                    $shared_for_all = true;
                    $cross_sub_students = $DB->get_fieldset_select(BLOCK_EXACOMP_DB_CROSSSTUD, 'studentid', 'crosssubjid=?', array($cross_subject->id));
                    if ($allcrosssubjects) {
                        $students = [];
                        // use courseid from crosssubject
                        if ($cross_subject->courseid) { // TODO: if cross_subject has not courseid - is this shared for all?
                            $students = block_exacomp_get_students_by_course($cross_subject->courseid);
                        }
                    }
                    foreach ($students as $student) {
                        if (!in_array($student->id, $cross_sub_students)) {
                            $shared_for_all = false;
                            break;
                        }
                    }

                    if ($shared_for_all) {
                        $cross_subjects_visible[$cross_subject->id] = $cross_subject;
                    }
                }
            }
        }

        //        $all_cross_subjects = block_exacomp_get_cross_subjects_by_course($courseid);
        foreach ($all_cross_subjects as $cross_subject) {
            $cross_subject->visible = 0;
            if (array_key_exists($cross_subject->id, $cross_subjects_visible)) {
                $cross_subject->visible = 1;
            }

            $cross_subject->examples = block_exacomp_get_examples_for_crosssubject($cross_subject->id);
            $cross_subject->hasmaterial = true;
            if (empty($cross_subject->examples)) {
                $cross_subject->hasmaterial = false;
            }

            $example_non_visibilities = $DB->get_fieldset_select(BLOCK_EXACOMP_DB_EXAMPVISIBILITY, 'exampleid', 'courseid=? AND studentid=? AND visible=0', array($cross_subject->courseid, 0));
            if (!$forall) {
                $example_non_visibilities_student = $DB->get_fieldset_select(BLOCK_EXACOMP_DB_EXAMPVISIBILITY, 'exampleid', 'courseid=? AND studentid=? AND visible=0', array($cross_subject->courseid, $userid));
            }

            $examples_return = array();
            foreach ($cross_subject->examples as $example) {
                $example_return = new stdClass();
                $example_return->exampleid = $example->id;
                $example_return->exampletitle = static::custom_htmltrim($example->title);
                $example_return->examplestate = ($forall) ? 0 : block_exacomp_get_dakora_state_for_example($cross_subject->courseid, $example->id, $userid);

                if ($forall) {
                    $example_return->teacherevaluation = -1;
                    $example_return->studentevaluation = -1;
                    $example_return->evalniveauid = null;
                    $example_return->timestampteacher = 0;
                    $example_return->timestampstudent = 0;
                    $example_return->solution_visible = 0;
                } else {
                    $evaluation = (object)static::_get_example_information($cross_subject->courseid, $userid, $example->id);
                    $example_return->teacherevaluation = $evaluation->teachervalue;
                    $example_return->studentevaluation = $evaluation->studentvalue;
                    $example_return->evalniveauid = $evaluation->evalniveauid;
                    $example_return->timestampteacher = $evaluation->timestampteacher;
                    $example_return->timestampstudent = $evaluation->timestampstudent;
                    $example_return->solution_visible = block_exacomp_is_example_solution_visible($cross_subject->courseid, $example, $userid);
                }
                $example_return->visible = ((!in_array($example->id, $example_non_visibilities)) && ((!$forall && !in_array($example->id, $example_non_visibilities_student)) || $forall)) ? 1 : 0;
                $example_return->used = (block_exacomp_example_used($cross_subject->courseid, $example, $userid)) ? 1 : 0;
                if (!array_key_exists($example->id, $examples_return)) {
                    $examples_return[$example->id] = $example_return;
                }
            }

            $cross_subject->examples = $examples_return;
        }

        if (!$forall && $userid) {
            foreach ($all_cross_subjects as $cross_subject) {
                static::add_comp_eval($cross_subject, $cross_subject->courseid, $userid);
            }
        } else {
            foreach ($all_cross_subjects as $cross_subject) {
                static::add_empty_comp_eval($cross_subject);
            }
        }

        return $all_cross_subjects;
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function dakora_get_cross_subjects_by_course_returns() {
        return new external_multiple_structure(new external_single_structure([
                'id' => new external_value(PARAM_INT, 'id of cross subject'),
                'title' => new external_value(PARAM_TEXT, 'title of cross subject'),
                'description' => new external_value(PARAM_TEXT, 'description of cross subject'),
                'subjectid' => new external_value(PARAM_INT, 'subject id, cross subject is associated with'),
                'visible' => new external_value(PARAM_INT, 'visibility of crosssubject for selected student'),
                'groupcategory' => new external_value(PARAM_TEXT, 'name of groupcategory'),
                'hasmaterial' => new external_value(PARAM_BOOL, 'true or false if crosssubject has material'),
                'examples' => new external_multiple_structure(new external_single_structure(array(
                    'exampleid' => new external_value(PARAM_INT, 'id of example'),
                    'exampletitle' => new external_value(PARAM_TEXT, 'title of example'),
                    'examplestate' => new external_value(PARAM_INT, 'state of example, always 0 if for all students'),
                    'visible' => new external_value(PARAM_INT, 'visibility of example in current context'),
                    'used' => new external_value(PARAM_INT, 'used in current context'),
                    'teacherevaluation' => new external_value(PARAM_INT, 'example evaluation of teacher'),
                    'studentevaluation' => new external_value(PARAM_INT, 'example evaluation of student'),
                    'timestampteacher' => new external_value(PARAM_INT, 'timestamp of teacher evaluation'),
                    'timestampstudent' => new external_value(PARAM_INT, 'timestamp of student evaluation'),
                    'evalniveauid' => new external_value(PARAM_INT, 'evaluation niveau id'),
                    'solution_visible' => new external_value(PARAM_BOOL, 'visibility for example solution in current context'),
                ))),
            ] + static::comp_eval_returns()));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function dakora_get_descriptors_by_cross_subject_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'crosssubjid' => new external_value(PARAM_INT, 'id of cross subject'),
            'userid' => new external_value(PARAM_INT, 'id of user, if 0 current user'),
            'forall' => new external_value(PARAM_BOOL, 'for all users = true, for one user = false'),
        ));
    }

    /**
     * get descriptors for a cross subject associated with examples
     * Get cross subjects
     *
     * @ws-type-read
     * @param int courseid
     *            int crosssubjid
     *            int userid
     *            boolean forall
     * @return list of descriptors
     */
    public static function dakora_get_descriptors_by_cross_subject($courseid, $crosssubjid, $userid, $forall) {
        global $USER;
        static::validate_parameters(static::dakora_get_descriptors_by_cross_subject_parameters(), array(
            'courseid' => $courseid,
            'crosssubjid' => $crosssubjid,
            'userid' => $userid,
            'forall' => $forall,
        ));

        if ($userid == 0 && $forall == false) {
            $userid = $USER->id;
        }

        static::require_can_access_course_user($courseid, $userid);

        return static::dakora_get_descriptors_by_cross_subject_common($courseid, $crosssubjid, $userid, $forall, true);
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function dakora_get_descriptors_by_cross_subject_returns() {
        return new external_multiple_structure(new external_single_structure(array(
            'descriptorid' => new external_value(PARAM_INT, 'id of descriptor'),
            'descriptortitle' => new external_value(PARAM_TEXT, 'title of descriptor'),
            'numbering' => new external_value(PARAM_TEXT, 'numbering for descriptor'),
            'niveautitle' => new external_value(PARAM_TEXT, 'title of nivaue'),
            'niveauid' => new external_value(PARAM_INT, 'id of niveau'),
            'visible' => new external_value(PARAM_INT, 'visibility of example in current context'),
            'used' => new external_value(PARAM_INT, 'used in current context'),
        )));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function dakora_get_all_descriptors_by_cross_subject_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'crosssubjid' => new external_value(PARAM_INT, 'id of cross subject'),
            'userid' => new external_value(PARAM_INT, 'id of user, if 0 current user'),
            'forall' => new external_value(PARAM_BOOL, 'for all users = true, for one user = false'),
        ));
    }

    /**
     * get descriptors for a cross subject
     * Get cross subjects
     *
     * @ws-type-read
     * @param int courseid
     *            int crosssubjid
     *            int userid
     *            boolean forall
     * @return list of descriptors
     */
    public static function dakora_get_all_descriptors_by_cross_subject($courseid, $crosssubjid, $userid, $forall) {
        global $USER;
        static::validate_parameters(static::dakora_get_all_descriptors_by_cross_subject_parameters(), array(
            'courseid' => $courseid,
            'crosssubjid' => $crosssubjid,
            'userid' => $userid,
            'forall' => $forall,
        ));

        if ($userid == 0 && $forall == false) {
            $userid = $USER->id;
        }

        static::require_can_access_course_user($courseid, $userid);

        return static::dakora_get_descriptors_by_cross_subject_common($courseid, $crosssubjid, $userid, $forall, false);
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function dakora_get_all_descriptors_by_cross_subject_returns() {
        return new external_multiple_structure(new external_single_structure(array(
            'descriptorid' => new external_value(PARAM_INT, 'id of descriptor'),
            'descriptortitle' => new external_value(PARAM_TEXT, 'title of descriptor'),
            'numbering' => new external_value(PARAM_TEXT, 'numbering for descriptor'),
            'niveautitle' => new external_value(PARAM_TEXT, 'title of nivaue'),
            'niveauid' => new external_value(PARAM_INT, 'id of niveau'),
            'visible' => new external_value(PARAM_INT, 'visibility of example in current context'),
            'used' => new external_value(PARAM_INT, 'used in current context'),
        )));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function dakora_get_descriptor_children_for_cross_subject_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'descriptorid' => new external_value(PARAM_INT, 'id of parent descriptor'),
            'userid' => new external_value(PARAM_INT, 'id of user, 0 for current user'),
            'forall' => new external_value(PARAM_BOOL, 'for all users = true, for one user = false'),
            'crosssubjid' => new external_value(PARAM_INT, 'id of cross subject'),
        ));
    }

    /**
     * get children in context of cross subject, associated with examples
     * get children for descriptor in cross subject context
     *
     * @ws-type-read
     * @return array of user courses
     */
    public static function dakora_get_descriptor_children_for_cross_subject($courseid, $descriptorid, $userid, $forall, $crosssubjid) {
        global $DB, $USER;
        static::validate_parameters(static::dakora_get_descriptor_children_for_cross_subject_parameters(), array(
            'courseid' => $courseid,
            'descriptorid' => $descriptorid,
            'userid' => $userid,
            'forall' => $forall,
            'crosssubjid' => $crosssubjid,
        ));

        if ($userid == 0 && !$forall) {
            $userid = $USER->id;
        }

        static::require_can_access_course_user($courseid, $userid);

        return static::get_descriptor_children($courseid, $descriptorid, $userid, $forall, $crosssubjid);

    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function dakora_get_descriptor_children_for_cross_subject_returns() {
        return new external_single_structure(array(
            'children' => new external_multiple_structure(new external_single_structure(array(
                'descriptorid' => new external_value(PARAM_INT, 'id of child'),
                'descriptortitle' => new external_value(PARAM_TEXT, 'title of child'),
                'numbering' => new external_value(PARAM_TEXT, 'numbering for child'),
                'teacherevaluation' => new external_value(PARAM_INT, 'grading of children'),
                'evalniveauid' => new external_value(PARAM_INT, 'evaluation niveau id'),
                'timestampteacher' => new external_value(PARAM_INT, 'timestamp of teacher evaluation'),
                'studentevaluation' => new external_value(PARAM_INT, 'self evaluation of children'),
                'timestampstudent' => new external_value(PARAM_INT, 'timestamp of student evaluation'),
                'examplestotal' => new external_value(PARAM_INT, 'total number of material'),
                'examplesvisible' => new external_value(PARAM_INT, 'visible number of material'),
                'examplesinwork' => new external_value(PARAM_INT, 'edited number of material'),
                'visible' => new external_value(PARAM_INT, 'visibility of child in current context'),
                'used' => new external_value(PARAM_INT, 'used in current context'),
            ))),
            'examples' => new external_multiple_structure(new external_single_structure(array(
                'exampleid' => new external_value(PARAM_INT, 'id of example'),
                'exampletitle' => new external_value(PARAM_TEXT, 'title of example'),
                'examplestate' => new external_value(PARAM_INT, 'state of example, always 0 if for all students'),
                'visible' => new external_value(PARAM_INT, 'visibility of example in current context'),
                'used' => new external_value(PARAM_INT, 'used in current context'),
            ))),
            'examplestotal' => new external_value(PARAM_INT, 'number of total examples'),
            'examplesvisible' => new external_value(PARAM_INT, 'number of visible examples'),
            'examplesinwork' => new external_value(PARAM_INT, 'number of examples in work'),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function dakora_get_all_descriptor_children_for_cross_subject_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'descriptorid' => new external_value(PARAM_INT, 'id of parent descriptor'),
            'userid' => new external_value(PARAM_INT, 'id of user, 0 for current user'),
            'forall' => new external_value(PARAM_BOOL, 'for all users = true, for one user = false'),
            'crosssubjid' => new external_value(PARAM_INT, 'id of cross subject'),
        ));
    }

    /**
     * get children in context of cross subject
     * get children for descriptor in cross subject context
     *
     * @ws-type-read
     * @return array of user courses
     */
    public static function dakora_get_all_descriptor_children_for_cross_subject($courseid, $descriptorid, $userid, $forall, $crosssubjid) {
        global $USER;
        static::validate_parameters(static::dakora_get_all_descriptor_children_for_cross_subject_parameters(), array(
            'courseid' => $courseid,
            'descriptorid' => $descriptorid,
            'userid' => $userid,
            'forall' => $forall,
            'crosssubjid' => $crosssubjid,
        ));

        if ($userid == 0 && !$forall) {
            $userid = $USER->id;
        }

        static::require_can_access_course_user($courseid, $userid);

        return static::get_descriptor_children($courseid, $descriptorid, $userid, $forall, $crosssubjid, true);

    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function dakora_get_all_descriptor_children_for_cross_subject_returns() {
        return new external_single_structure(array(
            'children' => new external_multiple_structure(new external_single_structure(array(
                'descriptorid' => new external_value(PARAM_INT, 'id of child'),
                'descriptortitle' => new external_value(PARAM_TEXT, 'title of child'),
                'numbering' => new external_value(PARAM_TEXT, 'numbering for child'),
                'teacherevaluation' => new external_value(PARAM_INT, 'grading of children'),
                'evalniveauid' => new external_value(PARAM_INT, 'evaluation niveau id'),
                'timestampteacher' => new external_value(PARAM_INT, 'timestamp of teacher evaluation'),
                'studentevaluation' => new external_value(PARAM_INT, 'self evaluation of children'),
                'timestampstudent' => new external_value(PARAM_INT, 'timestamp of student evaluation'),
                'hasmaterial' => new external_value(PARAM_BOOL, 'true or false if child has materials'),
                'examplestotal' => new external_value(PARAM_INT, 'total number of material'),
                'examplesvisible' => new external_value(PARAM_INT, 'visible number of material'),
                'examplesinwork' => new external_value(PARAM_INT, 'edited number of material'),
                'visible' => new external_value(PARAM_INT, 'visibility of children in current context'),
                'used' => new external_value(PARAM_INT, 'used in current context'),
            ))),
            'examples' => new external_multiple_structure(new external_single_structure(array(
                'exampleid' => new external_value(PARAM_INT, 'id of example'),
                'exampletitle' => new external_value(PARAM_TEXT, 'title of example'),
                'examplestate' => new external_value(PARAM_INT, 'state of example, always 0 if for all students'),
                'visible' => new external_value(PARAM_INT, 'visibility of example in current context'),
                'used' => new external_value(PARAM_INT, 'used in current context'),
            ))),
            'examplestotal' => new external_value(PARAM_INT, 'number of total examples'),
            'examplesvisible' => new external_value(PARAM_INT, 'number of visible examples'),
            'examplesinwork' => new external_value(PARAM_INT, 'number of examples in work'),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function dakora_get_schedule_config_parameters() {
        return new external_function_parameters(array());
    }

    /**
     * get configuration options for schedule units
     * get children for descriptor in cross subject context
     *
     * @ws-type-read
     * @return array of user courses
     */
    public static function dakora_get_schedule_config() {
        $units = (get_config("exacomp", "scheduleunits")) ? get_config("exacomp", "scheduleunits") : 8;
        $interval = (get_config("exacomp", "scheduleinterval")) ? get_config("exacomp", "scheduleinterval") : 50;
        $time = (get_config("exacomp", "schedulebegin")) ? get_config("exacomp", "schedulebegin") : "07:45";

        $periods = block_exacomp_get_timetable_entries();
        $periods_return = array();
        foreach ($periods as $period) {
            $period_return = new stdClass();
            $period_return->title = $period;
            $periods_return[] = $period_return;
        }
        return array("units" => $units, "interval" => $interval, "begin" => $time, "periods" => $periods_return);
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function dakora_get_schedule_config_returns() {
        return new external_single_structure(array(
            'units' => new external_value(PARAM_INT, 'number of units per day'),
            'interval' => new external_value(PARAM_TEXT, 'duration of unit in minutes'),
            'begin' => new external_value(PARAM_TEXT, 'begin time for the first unit, format hh:mm'),
            'periods' => new external_multiple_structure(new external_single_structure(array(
                'title' => new external_value(PARAM_TEXT, 'id of example'),
            ))),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     *
     */
    public static function dakora_get_pre_planning_storage_examples_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
        ));
    }

    /**
     * get examples for pre planning storage
     * get pre planning storage examples for current teacher
     *
     * @ws-type-read
     * @param int courseid
     * @return examples
     */
    public static function dakora_get_pre_planning_storage_examples($courseid) {
        global $USER;
        static::validate_parameters(static::dakora_get_pre_planning_storage_examples_parameters(), array(
            'courseid' => $courseid,
        ));

        $creatorid = $USER->id;
        static::require_can_access_course($courseid);

        $examples = block_exacomp_get_pre_planning_storage($creatorid, $courseid);

        foreach ($examples as $example) {
            $example->state = block_exacomp_get_dakora_state_for_example($example->courseid, $example->exampleid, $creatorid);
        }

        return $examples;
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function dakora_get_pre_planning_storage_examples_returns() {
        return new external_multiple_structure(new external_single_structure(array(
            'exampleid' => new external_value(PARAM_INT, 'id of example'),
            'title' => new external_value(PARAM_TEXT, 'title of example'),
            'courseid' => new external_value(PARAM_INT, 'example course'),
            'state' => new external_value(PARAM_INT, 'state of example'),
            'scheduleid' => new external_value(PARAM_INT, 'id in schedule context'),
        )));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     *
     */
    public static function dakora_get_pre_planning_storage_students_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
        ));
    }

    /**
     * get students for pre planning storage
     * get pre planning storage students for current teacher
     *
     * @ws-type-read
     * @param int courseid
     * @return examples
     */
    public static function dakora_get_pre_planning_storage_students($courseid) {
        global $USER;
        static::validate_parameters(static::dakora_get_pre_planning_storage_students_parameters(), array(
            'courseid' => $courseid,
        ));

        block_exacomp_require_teacher($courseid);

        $creatorid = $USER->id;

        $examples = array();
        $schedules = block_exacomp_get_pre_planning_storage($creatorid, $courseid);
        foreach ($schedules as $schedule) {
            if (!in_array($schedule->exampleid, $examples)) {
                $examples[] = $schedule->exampleid;
            }
        }

        $students = block_exacomp_get_students_by_course($courseid);
        $students = block_exacomp_get_student_pool_examples($students, $courseid);

        foreach ($students as $student) {
            $student_has_examples = false;
            foreach ($student->pool_examples as $example) {
                if (in_array($example->exampleid, $examples)) {
                    $student_has_examples = true;
                }
            }
            $student->studentid = $student->id;
            $student->has_examples = $student_has_examples;
        }

        return $students;
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function dakora_get_pre_planning_storage_students_returns() {
        return new external_multiple_structure(new external_single_structure(array(
            'studentid' => new external_value(PARAM_INT, 'id of student'),
            'firstname' => new external_value(PARAM_TEXT, 'firstname of student'),
            'lastname' => new external_value(PARAM_TEXT, 'lastname of student'),
            'has_examples' => new external_value(PARAM_BOOL, 'already has examples from current pre planning storage'),
        )));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     *
     */
    public static function dakora_get_pre_planning_storage_groups_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
        ));
    }

    /**
     * get students for pre planning storage
     * get pre planning storage students for current teacher
     *
     * @ws-type-read
     * @param int courseid
     * @return examples
     */
    public static function dakora_get_pre_planning_storage_groups($courseid) {
        global $USER;
        static::validate_parameters(static::dakora_get_pre_planning_storage_groups_parameters(), array(
            'courseid' => $courseid,
        ));

        block_exacomp_require_teacher($courseid);

        $creatorid = $USER->id;

        $examples = array();
        $schedules = block_exacomp_get_pre_planning_storage($creatorid, $courseid);

        foreach ($schedules as $schedule) {
            if (!in_array($schedule->exampleid, $examples)) {
                $examples[] = $schedule->exampleid;
            }
        }

        $groups = groups_get_all_groups($courseid);

        foreach ($groups as $group) {
            $group->has_examples = true;
            $students = block_exacomp_groups_get_members($courseid, $groupid);
            $students = block_exacomp_get_student_pool_examples($students, $courseid);

            foreach ($students as $student) {
                foreach ($student->pool_examples as $example) {
                    if (in_array($example->exampleid, $examples)) {
                        $student->has_examples = true;
                    }
                }
                if (!$student->has_examples) { //if one of the students does not have an example, the group as a whole is not marked with "has_examples"
                    $group->has_examples = false;
                }
            }
        }
        return $groups;
    }

    public static function dakora_get_pre_planning_storage_groups_returns() {
        return new external_multiple_structure(new external_single_structure(array(
            'id' => new external_value(PARAM_INT, 'id of group'),
            'name' => new external_value(PARAM_TEXT, 'name of group'),
            'has_examples' => new external_value(PARAM_BOOL, 'already has examples from current pre planning storage'),
        )));
    }




    //RW CHANGES IN PROGRESS:
    // 	$return = array();
    // 	$return[0]=$students;
    // 	$return['testvalue']=10;
    // 	return $return;
    // }

    // /**
    //  * Returns desription of method return values
    //  *
    //  * @return external_multiple_structure
    //  */
    // public static function dakora_get_pre_planning_storage_students_returns() {
    //     return new external_single_structure(array(
    //         new external_multiple_structure(new external_single_structure(array(
    //             'studentid' => new external_value(PARAM_INT, 'id of student'),
    //             'firstname' => new external_value(PARAM_TEXT, 'firstname of student'),
    //             'lastname' => new external_value(PARAM_TEXT, 'lastname of student'),
    //             'has_examples' => new external_value(PARAM_BOOL, 'already has examples from current pre planning storage'),
    //         ))),
    //         'testvalue' => new external_value(PARAM_INT, 'some testvalue)'),
    //     ));
    // }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     *
     */
    public static function dakora_has_items_in_pre_planning_storage_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
        ));
    }

    /**
     * return 0 if no items, 1 otherwise
     * get pre planning storage students for current teacher
     *
     * @ws-type-read
     * @param int courseid
     * @return examples
     */
    public static function dakora_has_items_in_pre_planning_storage($courseid) {
        global $USER;
        static::validate_parameters(static::dakora_has_items_in_pre_planning_storage_parameters(), array(
            'courseid' => $courseid,
        ));

        $creatorid = $USER->id;

        static::require_can_access_course($courseid);

        $items = false;
        if (block_exacomp_has_items_pre_planning_storage($creatorid, $courseid)) {
            $items = true;
        }

        return array(
            "success" => $items,
        );
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function dakora_has_items_in_pre_planning_storage_returns() {
        return new external_single_structure(array(
            'success' => new external_value(PARAM_BOOL, 'status of success, either true (1) or false (0)'),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     *
     */
    public static function dakora_empty_pre_planning_storage_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
        ));
    }

    /**
     * delte all items from current pre planning storage
     * empty pre planning storage for current teacher
     *
     * @ws-type-write
     * @param int courseid
     * @return examples
     */
    public static function dakora_empty_pre_planning_storage($courseid) {
        static::validate_parameters(static::dakora_empty_pre_planning_storage_parameters(), array(
            'courseid' => $courseid,
        ));

        static::require_can_access_course($courseid);

        block_exacomp_empty_pre_planning_storage($courseid);

        return array(
            "success" => true,
        );
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function dakora_empty_pre_planning_storage_returns() {
        return new external_single_structure(array(
            'success' => new external_value(PARAM_BOOL, 'status of success, either true (1) or false (0)'),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     *
     */
    public static function dakora_add_example_to_pre_planning_storage_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'exampleid' => new external_value(PARAM_INT, 'id of example'),
        ));
    }

    /**
     * add example to current pre planning storage
     * add example to current pre planning storage
     *
     * @ws-type-write
     * @param int courseid
     * @return array
     */
    public static function dakora_add_example_to_pre_planning_storage($courseid, $exampleid) {
        global $USER;
        static::validate_parameters(static::dakora_add_example_to_pre_planning_storage_parameters(), array(
            'courseid' => $courseid,
            'exampleid' => $exampleid,
        ));

        $creatorid = $USER->id;

        static::require_can_access_course($courseid);
        static::require_can_access_example($exampleid, $courseid);

        block_exacomp_add_example_to_schedule(0, $exampleid, $creatorid, $courseid);
        //block_exacomp_add_example_to_schedule(0, $exampleid, $creatorid, $courseid);

        return array(
            "success" => true,
        );
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function dakora_add_example_to_pre_planning_storage_returns() {
        return new external_single_structure(array(
            'success' => new external_value(PARAM_BOOL, 'status of success'),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     *
     */
    public static function dakora_add_examples_to_students_schedule_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'examples' => new external_value(PARAM_TEXT, 'json array of examples'),
            'students' => new external_value(PARAM_TEXT, 'json array of students'),
            'groups' => new external_value(PARAM_TEXT, 'json array of groups', VALUE_DEFAULT, ''),
        ));
    }

    /**
     * add examples from current pre planning storage to students weekly schedule
     * add example to current pre planning storage
     *
     * @ws-type-write
     * @param int courseid
     * @return examples
     */
    public static function dakora_add_examples_to_students_schedule($courseid, $examples, $students, $groups) {
        global $USER;
        static::validate_parameters(static::dakora_add_examples_to_students_schedule_parameters(), array(
            'courseid' => $courseid,
            'examples' => $examples,
            'students' => $students,
            'groups' => $groups,
        ));

        static::require_can_access_course_user($courseid, $USER->id);

        $creatorid = $USER->id;

        // TODO: input parameter prüfen? \block_exacomp\param::json()?
        $examples = json_decode($examples);
        $students = json_decode($students);
        $groups = json_decode($groups);

        foreach ($examples as $example) {
            foreach ($groups as $groupid) {
                $groupmembers = block_exacomp_groups_get_members($courseid, $groupid);
                foreach ($groupmembers as $member) {
                    block_exacomp_add_example_to_schedule($member->id, $example, $creatorid, $courseid);
                }
            }
            foreach ($students as $student) {
                block_exacomp_add_example_to_schedule($student, $example, $creatorid, $courseid, null, null, -1, -1, 'C');
            }
        }

        return array(
            "success" => true,
        );
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function dakora_add_examples_to_students_schedule_returns() {
        return new external_single_structure(array(
            'success' => new external_value(PARAM_BOOL, 'status of success, either true (1) or false (0)'),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     *
     */
    public static function dakora_add_examples_to_selected_students_schedule_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'students' => new external_value(PARAM_TEXT, 'json array of students'),
            'groups' => new external_value(PARAM_TEXT, 'json array of groups', VALUE_DEFAULT, ''),
            'distributionid' => new external_value(PARAM_TEXT, 'distribution id. used for undo button', VALUE_DEFAULT, null),
        ));
    }

    /**
     * add examples from current pre planning storage to students weekly schedule
     * add example to current pre planning storage
     *
     * @ws-type-write
     * @param mixed $courseid
     * @param string $students
     * @param string $groups
     * @param integer $distributionid
     * @return examples
     */
    public static function dakora_add_examples_to_selected_students_schedule($courseid, $students, $groups, $distributionid) {
        global $USER;
        static::validate_parameters(static::dakora_add_examples_to_selected_students_schedule_parameters(), array(
            'courseid' => $courseid,
            'students' => $students,
            'groups' => $groups,
            'distributionid' => $distributionid,
        ));

        static::require_can_access_course_user($courseid, $USER->id);

        $creatorid = $USER->id;

        // TODO: input parameter prüfen? \block_exacomp\param::json()?
        $students = json_decode($students);
        $groups = json_decode($groups);

        $distributionid = (int)$distributionid;

        foreach ($groups as $group) {
            block_exacomp_add_examples_to_schedule_for_group($courseid, $group, $distributionid);
        }
        block_exacomp_add_examples_to_schedule_for_students($courseid, $students, $distributionid);

        return array(
            "success" => true,
        );
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function dakora_add_examples_to_selected_students_schedule_returns() {
        return new external_single_structure(array(
            'success' => new external_value(PARAM_BOOL, 'status of success, either true (1) or false (0)'),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @deprecated Delete after dakora app changed
     */
    public static function dakora_delete_examples_from_schedule_parameters() {
        return self::dakora_undo_examples_from_schedule_parameters();

    }

    /**
     * remove example from weekly schedule by teacherid and distribution id
     * used for 'undo' button
     *
     * @ws-type-write
     * @param integer $teacherid
     * @param integer $distributionid
     * @return list of descriptors
     * @deprecated Delete after dakora app changed
     */
    public static function dakora_delete_examples_from_schedule($teacherid, $distributionid) {
        return self::dakora_undo_examples_from_schedule($teacherid, $distributionid);
    }

    /**
     * Returns desription of method return values
     *
     * @return external_single_structure
     * @deprecated Delete after dakora app changed
     */
    public static function dakora_delete_examples_from_schedule_returns() {
        return self::dakora_undo_examples_from_schedule_returns();
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function dakora_undo_examples_from_schedule_parameters() {
        return new external_function_parameters(array(
            'teacherid' => new external_value(PARAM_INT, 'id of teacher'),
            'distributionid' => new external_value(PARAM_INT, 'distribution id'),
        ));
    }

    /**
     * remove example from weekly schedule by teacherid and distribution id
     * used for 'undo' button
     *
     * @ws-type-write
     * @param integer $teacherid
     * @param integer $distributionid
     * @return list of descriptors
     */
    public static function dakora_undo_examples_from_schedule($teacherid, $distributionid) {
        global $DB;

        static::validate_parameters(static::dakora_undo_examples_from_schedule_parameters(), array(
            'teacherid' => $teacherid,
            'distributionid' => $distributionid,
        ));

        // get schedules for deleting
        $toDelete = $DB->get_records_sql('SELECT *
                FROM {' . BLOCK_EXACOMP_DB_SCHEDULE . '}
                WHERE creatorid = ?
                      AND studentid > 0
                      AND distributionid = ?',
            [$teacherid, $distributionid]
        );
        $returnToTeacherStorage = [];
        foreach ($toDelete as $scheduleid => $entry) {
            if (!array_key_exists($entry->exampleid, $returnToTeacherStorage)) {
                $returnToTeacherStorage[$entry->exampleid] = $entry;
            }
            // permissions are checked in lib.php
            block_exacomp_remove_example_from_schedule($scheduleid);
        }
        // return examples to teacher storage
        foreach ($returnToTeacherStorage as $exampleId => $example) {
            $courseid = $example->courseid;
            // student = 0, source = 0, distributionid = 0 !
            block_exacomp_add_example_to_schedule(0, $example->exampleid, g::$USER->id, $courseid, $example->start, $example->endtime, $example->ethema_ismain, $example->ethema_issubcategory, 'T', false, null);
        }

        return array(
            "success" => true,
        );

    }

    /**
     * Returns desription of method return values
     *
     * @return external_single_structure
     */
    public static function dakora_undo_examples_from_schedule_returns() {
        return new external_single_structure(array(
            'success' => new external_value(PARAM_BOOL, 'status of success, either true (1) or false (0)'),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function dakora_submit_example_parameters() {
        return new external_function_parameters(array(
            'exampleid' => new external_value(PARAM_INT, 'exampleid'),
            'studentvalue' => new external_value(PARAM_INT, 'studentvalue for grading', VALUE_DEFAULT, -1),
            'url' => new external_value(PARAM_URL, 'url'),
            //			'filename' => new external_value(PARAM_TEXT, 'filename, used to look up file and create a new one in the exaport file area'),
            'filenames' => new external_value(PARAM_TEXT, 'filenames, separated by comma, used to look up files and create a new ones in the exaport file area'),
            'studentcomment' => new external_value(PARAM_TEXT, 'studentcomment'),
            //'value' => new external_value(PARAM_INT, 'value of the grading', VALUE_DEFAULT, -1),
            'itemid' => new external_value(PARAM_INT, 'itemid (<=0 for insert, >0 for update)'),
            'courseid' => new external_value(PARAM_INT, 'courseid'),
            //			'fileitemid' => new external_value(PARAM_INT, 'fileitemid'),
            'fileitemids' => new external_value(PARAM_TEXT, 'fileitemids separated by comma'),
        ));
    }

    /**
     * submit example solution
     * Add student submission to example.
     *
     * @ws-type-write
     * @param int itemid (0 for new, >0 for existing)
     * @return array of course subjects
     */
    public static function dakora_submit_example($exampleid, $studentvalue = null, $url = null, $filenames = null, $studentcomment = null, $itemid = 0, $courseid = 0, $fileitemids = '') {
        global $CFG, $DB, $USER;
        static::validate_parameters(static::dakora_submit_example_parameters(),
            array('exampleid' => $exampleid, 'url' => $url, 'filenames' => $filenames, 'studentcomment' => $studentcomment, 'studentvalue' => $studentvalue, 'itemid' => $itemid, 'courseid' => $courseid, 'fileitemids' => $fileitemids));

        if (!isset($type)) {
            $type = ($filenames != '') ? 'file' : 'url';
        }

        static::require_can_access_course($courseid);

        //insert: if itemid == 0 OR status != 0
        $insert = true;
        if ($itemid > 0) {
            $itemexample = $DB->get_record(BLOCK_EXACOMP_DB_ITEM_MM, array('itemid' => $itemid));
            if ($itemexample && ($itemexample->teachervalue == null || $itemexample->status == 0)) {
                $insert = false;
            }
        }
        require_once $CFG->dirroot . '/blocks/exaport/inc.php';

        if ($insert) {
            //store item in the right portfolio category
            $course = get_course($courseid);
            $course_category = block_exaport_get_user_category($course->fullname, $USER->id);

            if (!$course_category) {
                $course_category = block_exaport_create_user_category($course->fullname, $USER->id); //create new category for portfoliofiles
            }

            $example = $DB->get_record('block_exacompexamples', array('id' => $exampleid), 'title, blocking_event');
            $exampletitle = $example->title;
            if ($example->blocking_event == 2) { //if freematerial, create the category with name "freematerials"
                $subjecttitle = get_string('freematerials', 'block_exacomp');
            } else {
                $subjecttitle = block_exacomp_get_subjecttitle_by_example($exampleid);
            }
            $subject_category = block_exaport_get_user_category($subjecttitle, $USER->id);
            if (!$subject_category) {
                $subject_category = block_exaport_create_user_category($subjecttitle, $USER->id, $course_category->id);
            }

            $itemid = $DB->insert_record("block_exaportitem",
                array('userid' => $USER->id, 'name' => $exampletitle, 'intro' => '', 'url' => $url, 'type' => $type, 'timemodified' => time(), 'categoryid' => $subject_category->id, 'teachervalue' => null, 'studentvalue' => null,
                    'courseid' => $courseid));
            //autogenerate a published view for the new item
            $dbView = new stdClass();
            $dbView->userid = $USER->id;
            $dbView->name = $exampletitle;
            $dbView->timemodified = time();
            $dbView->layout = 1;
            // generate view hash
            do {
                $hash = substr(md5(microtime()), 3, 8);
            } while ($DB->record_exists("block_exaportview", array("hash" => $hash)));
            $dbView->hash = $hash;

            $dbView->id = $DB->insert_record('block_exaportview', $dbView);

            //share the view with teachers
            block_exaport_share_view_to_teachers($dbView->id);

            //add item to view
            $DB->insert_record('block_exaportviewblock', array('viewid' => $dbView->id, 'positionx' => 1, 'positiony' => 1, 'type' => 'item', 'itemid' => $itemid));

        } else {
            $item = $DB->get_record('block_exaportitem', array('id' => $itemid));

            $item->url = $url;
            $item->timemodified = time();

            if ($type == 'file') {
                block_exaport_file_remove($DB->get_record("block_exaportitem", array("id" => $itemid)));
            }

            $DB->update_record('block_exaportitem', $item);
        }

        //if a file is added we need to copy the file from the user/private filearea to block_exaport/item_file with the itemid from above
        if ($type == "file") {
            $context = context_user::instance($USER->id);
            $fs = get_file_storage();
            try {
                $fileitemids = explode(',', $fileitemids);
                $filenames = explode(',', $filenames);

                if ($fileitemids) {
                    $i = 0; //for getting the names
                    foreach ($fileitemids as $fileitemid) {
                        $filename = $filenames[$i];
                        $i++;
                        $old = $fs->get_file($context->id, "user", "draft", $fileitemid, "/", $filename);
                        if ($old) {
                            $file_record = array('contextid' => $context->id, 'component' => 'block_exaport', 'filearea' => 'item_file',
                                'itemid' => $itemid, 'filepath' => '/', 'filename' => $old->get_filename(),
                                'timecreated' => time(), 'timemodified' => time());
                            $fs->create_file_from_storedfile($file_record, $old->get_id());
                            $old->delete();
                        }
                    }
                }
            } catch (Exception $e) {
                //some problem with the file occured
            }
        }

        if ($insert) {
            $DB->insert_record(BLOCK_EXACOMP_DB_ITEM_MM, array('exacomp_record_id' => $exampleid, 'itemid' => $itemid, 'timecreated' => time(), 'status' => 0));
            if ($studentcomment != '') {
                $DB->insert_record('block_exaportitemcomm', array('itemid' => $itemid, 'userid' => $USER->id, 'entry' => $studentcomment, 'timemodified' => time()));
            }
        } else {
            $itemexample->timemodified = time();
            $itemexample->studentvalue = $studentvalue;
            $DB->update_record(BLOCK_EXACOMP_DB_ITEM_MM, $itemexample);
            //$DB->delete_records('block_exaportitemcomm', array('itemid' => $itemid, 'userid' => $USER->id));   //DO NOT DELETE OLD COMMENTS, instead, only show newest
            if ($studentcomment != '') {
                $DB->insert_record('block_exaportitemcomm', array('itemid' => $itemid, 'userid' => $USER->id, 'entry' => $studentcomment, 'timemodified' => time()));
            }
        }

        block_exacomp_set_user_example($USER->id, $exampleid, $courseid, BLOCK_EXACOMP_ROLE_STUDENT, $studentvalue);
        $customdata = ['block' => 'exacomp', 'app' => 'dakora', 'type' => 'submit_example', 'itemid' => $itemid, 'itemuserid' => $item->userid, 'exampleid' => $exampleid];
        block_exacomp_notify_all_teachers_about_submission($courseid, $exampleid, time(), $studentcomment, $customdata);
        example_submitted::log(['objectid' => $exampleid, 'courseid' => $courseid]);

        return array("success" => true, "itemid" => $itemid);

    }

    /**
     * Returns desription of method return values
     *
     * @return external_single_structure
     */
    public static function dakora_submit_example_returns() {
        return new external_single_structure(array(
            'success' => new external_value(PARAM_BOOL, 'status'),
            'itemid' => new external_value(PARAM_INT, 'itemid'),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function diggrplus_submit_item_parameters() {
        return new external_function_parameters(array(
            'compid' => new external_value(PARAM_INT, 'id of topic/example'),
            'studentvalue' => new external_value(PARAM_INT, 'studentvalue for grading', VALUE_DEFAULT, -1), // if example --> grading also possible
            'url' => new external_value(PARAM_URL, 'url'),
            'filenames' => new external_value(PARAM_TEXT, 'filenames, separated by comma, used to look up files and create a new ones in the exaport file area'),
            'studentcomment' => new external_value(PARAM_TEXT, 'studentcomment'),
            'fileitemids' => new external_value(PARAM_TEXT, 'fileitemids separated by comma, used to look up file and create a new one in the exaport file area'),
            'itemid' => new external_value(PARAM_INT, 'itemid (0 for insert, >0 for update)'),
            'courseid' => new external_value(PARAM_INT, 'courseid'),
            'comptype' => new external_value(PARAM_INT, 'comptype (example, topic, descriptor)'),
            'itemtitle' => new external_value(PARAM_TEXT, 'name of the item (for examples, the exampletitle is fitting, but for topics, using the topic would not be very useful', VALUE_DEFAULT, ''),
            'collabuserids' => new external_value(PARAM_TEXT, 'userids of collaborators separated by comma', VALUE_DEFAULT, ''),
            'submit' => new external_value(PARAM_INT, '1 for submitting definitely (submitted), 0 for only creating/updating the item (inprogress)', VALUE_DEFAULT, 0),
            'removefiles' => new external_value(PARAM_TEXT, 'fileindizes/pathnamehashes of the files that should be removed, separated by comma'),
            'solutiondescription' => new external_value(PARAM_TEXT, 'description of what the student has done'),
            'descriptorgradings' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'descriptorid' => new external_value(PARAM_INT, 'id of descriptor'),
                        'studentvalue' => new external_value(PARAM_INT, 'studentvalue of descriptorgrading'),
                    )
                ), 'descriptors and gradingds', VALUE_OPTIONAL
            ),
        ));
    }

    /**
     * Add studentsubmission  (exaportitem) to topic, descriptor or example
     *
     * @ws-type-write
     * @param int itemid (0 for new, >0 for existing)
     * @return array of course subjects
     */
    public static function diggrplus_submit_item($compid, $studentvalue, $url, $filenames, $studentcomment, $fileitemids = '', $itemid = 0, $courseid = 0, $comptype = BLOCK_EXACOMP_TYPE_EXAMPLE, $itemtitle = '', $collabuserids = '',
        $submit = 0, $removefiles = '', $solutiondescription = '', $descriptorgradings = []) {
        global $CFG, $DB, $USER;
        static::validate_parameters(static::diggrplus_submit_item_parameters(),
            array('compid' => $compid, 'studentvalue' => $studentvalue, 'url' => $url, 'filenames' => $filenames, 'fileitemids' => $fileitemids, 'studentcomment' => $studentcomment,
                'itemid' => $itemid, 'courseid' => $courseid, 'comptype' => $comptype, 'itemtitle' => $itemtitle, 'collabuserids' => $collabuserids, 'submit' => $submit, 'removefiles' => $removefiles,
                'solutiondescription' => $solutiondescription, 'descriptorgradings' => $descriptorgradings));

        // TODO: is URL type needed in diggrplus? what exactly does it do?  For now: always set to "file"
        //        if (!isset($type)) {
        //            $type = ($filenames != '') ? 'file' : 'url';
        //        };
        $type = 'file';

        static::require_can_access_course($courseid);

        //insert: if itemid == 0 OR status != 0
        $insert = true;
        if ($itemid > 0) {
            $item_comp_mm = $DB->get_record(BLOCK_EXACOMP_DB_ITEM_MM, array('itemid' => $itemid));
            if ($item_comp_mm && ($item_comp_mm->teachervalue == null || $item_comp_mm->status == 0)) {
                $insert = false;
            }
        }
        require_once $CFG->dirroot . '/blocks/exaport/inc.php';

        $customdata = ['block' => 'exacomp', 'app' => 'diggrplus', 'courseid' => $courseid, 'itemid' => $itemid, 'itemuserid' => $USER->id];
        foreach ($descriptorgradings as $descriptorgrading) {
            block_exacomp_set_user_competence($USER->id, $descriptorgrading["descriptorid"], BLOCK_EXACOMP_TYPE_DESCRIPTOR, $courseid, BLOCK_EXACOMP_ROLE_STUDENT, $descriptorgrading["studentvalue"], null, -1, true, [
                'notification_customdata' => $customdata,
            ]);
        }

        // remove files specifically marked for deletion by user:
        // for deleting a file that already exists, itemid cannot be used, but pathnamehash. "get_file()" actually gets the pathnamehash and uses this to get the file
        // use get_file_by_hash() instead, for deleting already existing files.
        // TODO: could this be used to remove files this user doesn't have access to? HACKABLE
        // solution: get itemid -> get item -> check if this user is the creator of this item -> only then allow deletion
        if ($removefiles) {
            $fs = get_file_storage();
            $removefiles = explode(',', $removefiles);
            $context = context_user::instance($USER->id);
            $files = $fs->get_area_files($context->id, "block_exaport", "item_file", $itemid, "", false);

            foreach ($files as $file) {
                if (in_array($file->get_id(), $removefiles)) {
                    $file->delete();
                }
            }
        }

        if ($insert) {
            //store item in the right portfolio category
            $course = get_course($courseid);
            $course_category = block_exaport_get_user_category($course->fullname, $USER->id);

            if (!$course_category) {
                $course_category = block_exaport_create_user_category($course->fullname, $USER->id); //create new category for portfoliofiles
            }

            switch ($comptype) {
                case BLOCK_EXACOMP_TYPE_TOPIC:
                    $subjecttitle = block_exacomp_get_subjecttitle_by_topic($compid);

                    $comptitle = $itemtitle ? $itemtitle : $DB->get_field('block_exacomptopics', 'title', array("id" => $compid));

                    break;
                case BLOCK_EXACOMP_TYPE_DESCRIPTOR:
                    $subjecttitle = block_exacomp_get_subjecttitle_by_descriptor($compid);

                    $comptitle = $itemtitle ? $itemtitle : $DB->get_field('block_exacompdescriptors', 'title', array("id" => $compid));
                    break;
                case BLOCK_EXACOMP_TYPE_EXAMPLE:
                    $example = $DB->get_record('block_exacompexamples', array('id' => $compid), 'title, blocking_event');
                    $comptitle = $itemtitle ? $itemtitle : $example->title;
                    if ($example->blocking_event == 2) { //if freematerial, create the category with name "freematerials"
                        $subjecttitle = get_string('freematerials', 'block_exacomp');
                    } else {
                        $subjecttitle = block_exacomp_get_subjecttitle_by_example($compid);
                    }
                    break;
            }

            $subject_category = block_exaport_get_user_category($subjecttitle, $USER->id);
            if (!$subject_category) {
                $subject_category = block_exaport_create_user_category($subjecttitle, $USER->id, $course_category->id);
            }

            $itemid = $DB->insert_record("block_exaportitem",
                array('userid' => $USER->id, 'name' => $comptitle, 'intro' => $solutiondescription, 'url' => $url, 'type' => $type, 'timemodified' => time(), 'categoryid' => $subject_category->id, 'courseid' => $courseid));
            //autogenerate a published view for the new item
            $dbView = new stdClass();
            $dbView->userid = $USER->id;
            $dbView->name = $comptitle;
            $dbView->timemodified = time();
            $dbView->layout = 1;
            // generate view hash
            do {
                $hash = substr(md5(microtime()), 3, 8);
            } while ($DB->record_exists("block_exaportview", array("hash" => $hash)));
            $dbView->hash = $hash;

            $dbView->id = $DB->insert_record('block_exaportview', $dbView);

            //share the view with teachers
            block_exaport_share_view_to_teachers($dbView->id);

            //add item to view
            $DB->insert_record('block_exaportviewblock', array('viewid' => $dbView->id, 'positionx' => 1, 'positiony' => 1, 'type' => 'item', 'itemid' => $itemid));

        } else {
            $item = $DB->get_record('block_exaportitem', array('id' => $itemid));

            $item->name = $itemtitle;
            $item->url = $url;
            $item->timemodified = time();
            $item->type = $type;
            $item->intro = $solutiondescription;

            // This would overwrite, which we do not want in diggrplus
            //            if ($type == 'file') {
            //                block_exaport_file_remove($DB->get_record("block_exaportitem", array("id" => $itemid)));
            //            }

            $DB->update_record('block_exaportitem', $item);
        }

        //if a file is added we need to copy the file from the user/private filearea to block_exaport/item_file with the itemid from above
        if ($type == "file" && $fileitemids) {
            $context = context_user::instance($USER->id);
            $fs = get_file_storage();
            $fileitemids = explode(',', $fileitemids);

            if ($fileitemids) {
                foreach ($fileitemids as $file_i => $fileitemid) {
                    $old = current($fs->get_area_files($context->id, "user", "draft", $fileitemid, "", false));
                    if ($old) {
                        $file_record = array('contextid' => $context->id, 'component' => 'block_exaport', 'filearea' => 'item_file',
                            'itemid' => $itemid, 'filepath' => '/', 'filename' => $old->get_filename(),
                            'timecreated' => time(), 'timemodified' => time());

                        try {
                            $fs->create_file_from_storedfile($file_record, $old);
                        } catch (\stored_file_creation_exception $e) {
                            // error while saving the file, maybe the name already exists?

                            // try again with different name
                            $file_record['filename'] = preg_replace('!(\.[^\.]+)$!', ' - Kopie$1', $file_record['filename']);
                            $fs->create_file_from_storedfile($file_record, $old);
                        }
                        $old->delete();
                    }
                }
            }
        }

        //calculate status of item: 0 means no submit, 1 means student has submitted, 2 means there exists a teachervalue and the item is completed
        // status=submit since the teacher cannot have graded an item, that has not been submitted by a student before.
        // after a teacher has graded an item, the item cannot be submitted by the student anymore
        if ($insert) {
            $DB->insert_record(BLOCK_EXACOMP_DB_ITEM_MM, array('exacomp_record_id' => $compid, 'itemid' => $itemid, 'timecreated' => time(), 'status' => $submit, 'competence_type' => $comptype, 'studentvalue' => $studentvalue));
            if ($studentcomment != '') {
                $DB->insert_record('block_exaportitemcomm', array('itemid' => $itemid, 'userid' => $USER->id, 'entry' => $studentcomment, 'timemodified' => time()));
            }
        } else {
            $item_comp_mm->datemodified = time();
            $item_comp_mm->studentvalue = $studentvalue; // TODO: -1 is not good, solve it differently
            $item_comp_mm->status = $submit;
            $DB->update_record(BLOCK_EXACOMP_DB_ITEM_MM, $item_comp_mm);
            //$DB->delete_records('block_exaportitemcomm', array('itemid' => $itemid, 'userid' => $USER->id));   //DO NOT DELETE OLD COMMENTS, instead, only show newest
            if ($studentcomment != '') {
                $DB->insert_record('block_exaportitemcomm', array('itemid' => $itemid, 'userid' => $USER->id, 'entry' => $studentcomment, 'timemodified' => time()));
            }
        }

        // also store it in BLOCK_EXACOMP_DB_COMPETENCES
        block_exacomp_set_comp_eval($courseid, BLOCK_EXACOMP_ROLE_STUDENT, $USER->id, $comptype, $compid, [
            'value' => $studentvalue,
            'evalniveauid' => null,
            'reviewerid' => $USER->id,
            'timestamp' => time(),
        ]);


        if ($submit) {
            example_submitted::log(['objectid' => $compid, 'courseid' => $courseid]);

            $customdata = ['block' => 'exacomp', 'app' => 'diggrplus', 'type' => 'submit_item', 'courseid' => $courseid, 'itemid' => $itemid, 'itemuserid' => $USER->id];
            if ($comptype == BLOCK_EXACOMP_TYPE_EXAMPLE) {
                $customdata['exampleid'] = $compid;
                $example = $DB->get_record('block_exacompexamples', array('id' => $compid), 'title, blocking_event');
                $subject = block_exacomp_get_string('notification_submission_subject_noSiteName', null, ['student' => fullname($USER), 'example' => $example->title]);
                // $subject .= "\n\r".$studentcomment;
            } else {
                $item = $DB->get_record('block_exaportitem', array('id' => $itemid));
                $subject = block_exacomp_trans([
                    'de:{$a->student} hat ein freies Lernmaterial "{$a->example}" eingereicht',
                    'en:{$a->student} submitted a solution for "{$a->example}"',
                ], ['student' => fullname($USER), 'example' => $item->name]);
            }
            $notificationContext = block_exacomp_get_string('notification_submission_context');

            $teachers = block_exacomp_get_teachers_by_course($courseid);
            foreach ($teachers as $teacher) {
                block_exacomp_send_notification("submission", $USER, $teacher, $subject, '', $notificationContext, '', false, 0, $customdata);
            }
        }

        // add "activity" relations to competences: TODO: is this ok?
        // only do this if it is not done already for this activityid and compid
        $activityRelations = $DB->get_records('block_exacompcompactiv_mm', array('compid' => $compid, 'comptype' => $comptype, 'eportfolioitem' => 1, 'activityid' => $itemid));
        if (!$activityRelations) {
            $DB->insert_record('block_exacompcompactiv_mm', array('compid' => $compid, 'comptype' => $comptype, 'eportfolioitem' => 1, 'activityid' => $itemid));
        }

        // update collabuserids
        // first delete all, then add again   //could check if anything changed first, but I am not sure if it would bring any benefit
        $DB->delete_records(BLOCK_EXACOMP_DB_ITEM_COLLABORATOR_MM, array('itemid' => $itemid));
        if ($collabuserids) {
            $collabuserids = explode(',', $collabuserids);
            // disabled for now: why add yourself?
            // if (!in_array($USER->id, $collabuserids)) {
            // 	// add yourself as well
            // 	$collabuserids[] = $USER->id;
            // }

            foreach ($collabuserids as $collabuserid) {
                $DB->insert_record(BLOCK_EXACOMP_DB_ITEM_COLLABORATOR_MM, array('userid' => $collabuserid, 'itemid' => $itemid));
            }
        }

        return array("success" => true, "itemid" => $itemid);
    }

    /**
     * Returns desription of method return values
     *
     * @return external_single_structure
     */
    public static function diggrplus_submit_item_returns() {
        return new external_single_structure(array(
            'success' => new external_value(PARAM_BOOL, 'status'),
            'itemid' => new external_value(PARAM_INT, 'itemid'),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function diggrplus_submit_item_comment_parameters() {
        return new external_function_parameters(array(
            'itemid' => new external_value(PARAM_INT, 'id of item'),
            'comment' => new external_value(PARAM_TEXT, 'comment text'),
            'fileitemid' => new external_value(PARAM_TEXT, '', VALUE_DEFAULT, ''),
        ));
    }

    /**
     * Add studentsubmission  (exaportitem) to topic, descriptor or example
     *
     * @ws-type-write
     * @param int itemid (0 for new, >0 for existing)
     * @return array of course subjects
     */
    public static function diggrplus_submit_item_comment($itemid, $comment, $fileitemid) {
        global $DB, $USER;
        static::validate_parameters(static::diggrplus_submit_item_comment_parameters(),
            array(
                'itemid' => $itemid,
                'comment' => $comment,
                'fileitemid' => $fileitemid,
            ));

        $item = $DB->get_record('block_exaportitem', array('id' => $itemid), '*', MUST_EXIST);

        // Prüfung, ob schüler/lehrer/collaborators auch auf diesen item kommentieren dürfen
        $teachers = block_exacomp_get_teachers_by_course($item->courseid);
        $teacherIds = array_map(function($teacher) {
            return $teacher->id;
        }, $teachers);

        $collaboratorIds = $DB->get_records_menu(BLOCK_EXACOMP_DB_ITEM_COLLABORATOR_MM, array('itemid' => $itemid), '', 'id, userid');

        $allowed_users = array_unique(array_merge(
            [$item->userid], // owner
            $teacherIds,
            $collaboratorIds
        ));

        if (!in_array($USER->id, $allowed_users)) {
            throw new invalid_parameter_exception('not allowed to comment on item');
        }

        if ($fileitemid) {
            $context = context_user::instance($USER->id);
            $fs = get_file_storage();
            $file = reset($fs->get_area_files($context->id, 'user', 'draft', $fileitemid, null, false));
            if (!$file) {
                throw new moodle_exception('file not found');
            }
        } else {
            $file = null;
        }

        $commentid = $DB->insert_record('block_exaportitemcomm', array('itemid' => $itemid, 'userid' => $USER->id, 'entry' => $comment, 'timemodified' => time()));

        // send notification to all other users about comment
        $customdata = ['block' => 'exacomp', 'app' => 'diggrplus', 'type' => 'item_comment', 'itemid' => $itemid, 'itemuserid' => $item->userid, 'comment' => $comment];
        $subject = block_exacomp_trans([
            'de:{$a->fullname} hat einen Kommentar bei "{$a->example}" erfasst',
            'en:{$a->fullname} has commented on "{$a->example}"',
        ], ['fullname' => fullname($USER), 'example' => $item->name]);
        $notificationContext = block_exacomp_get_string('notification_submission_context');

        foreach ($allowed_users as $user_id) {
            if ($user_id == $USER->id) {
                // don't send to myself
                continue;
            }
            block_exacomp_send_notification("comment", $USER, $user_id, $subject, '', $notificationContext, '', false, $item->courseid, $customdata);
        }

        if ($file) {
            $fs->create_file_from_storedfile(array(
                'contextid' => context_system::instance()->id,
                'component' => 'block_exaport',
                'filearea' => 'item_comment_file',
                'itemid' => $commentid,
            ), $file);
            $file->delete();
        }

        return array("success" => true, "itemid" => $itemid);
    }

    /**
     * Returns desription of method return values
     *
     * @return external_single_structure
     */
    public static function diggrplus_submit_item_comment_returns() {
        return new external_single_structure(array(
            'success' => new external_value(PARAM_BOOL, 'status'),
            'itemid' => new external_value(PARAM_INT, 'itemid'),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function diggrplus_get_item_comments_parameters() {
        return new external_function_parameters(array(
            'itemid' => new external_value(PARAM_INT, 'id of item'),
        ));
    }

    /**
     * Add studentsubmission  (exaportitem) to topic, descriptor or example
     *
     * @ws-type-write
     * @param int itemid (0 for new, >0 for existing)
     * @return array of course subjects
     */
    public static function diggrplus_get_item_comments($itemid) {
        global $CFG, $DB, $USER;
        static::validate_parameters(static::diggrplus_get_item_comments_parameters(),
            array(
                'itemid' => $itemid,
            ));

        $item = $DB->get_record('block_exaportitem', array('id' => $itemid), '*', MUST_EXIST);

        // Prüfung, ob schüler/lehrer/collaborators auch auf diesen item kommentieren dürfen
        $teachers = block_exacomp_get_teachers_by_course($item->courseid);
        $teacherIds = array_map(function($teacher) {
            return $teacher->id;
        }, $teachers);

        $collaboratorIds = $DB->get_records_menu(BLOCK_EXACOMP_DB_ITEM_COLLABORATOR_MM, array('itemid' => $itemid), '', 'id, userid');

        $allowed_users = array_unique(array_merge(
            [$item->userid], // owner
            $teacherIds,
            $collaboratorIds
        ));

        if (!in_array($USER->id, $allowed_users)) {
            throw new invalid_parameter_exception('not allowed to comment on item');
        }

        require_once $CFG->dirroot . '/blocks/exaport/inc.php';
        $itemcomments = api::get_item_comments($itemid);
        $users = [];
        foreach ($itemcomments as $comment) {
            if ($users[$comment->userid] == null) {
                $users[$comment->userid] = $DB->get_record('user', array('id' => $comment->userid));
            }
            $comment->fullname = $users[$comment->userid]->firstname . ' ' . $users[$comment->userid]->lastname;
            $comment->comment = $comment->entry;

            $userpicture = new user_picture($users[$comment->userid]);
            $userpicture->size = 1; // Size f1.
            $comment->profileimageurl = $userpicture->get_url(g::$PAGE)->out(false);

            if (empty($comment->file)) {
                // empty value not allowed in webservice response
                unset($comment->file);
            } else {
                $file = $comment->file;
                // access ist über die gesharte view
                $access = block_exacomp_get_access_for_shared_view_for_item($item, $USER->id);
                if ($access) {
                    $fileurl = (new moodle_url("/blocks/exaport/portfoliofile.php", [
                        'access' => $access,
                        'itemid' => $item->id,
                        'commentid' => $comment->id,
                        'wstoken' => static::wstoken(),
                    ]))->out(false);
                } else {
                    $fileurl = '';
                }

                $comment->file = [
                    'file' => $fileurl,
                    'mimetype' => $file->get_mimetype(),
                    'filename' => $file->get_filename(),
                ];
            }
        }

        return $itemcomments;
    }

    /**
     * Returns desription of method return values
     *
     * @return external_single_structure
     */
    public static function diggrplus_get_item_comments_returns() {
        return new external_multiple_structure(new external_single_structure(array(
            'id' => new external_value(PARAM_INT, 'commentid'),
            'userid' => new external_value(PARAM_INT, 'userid'),
            'fullname' => new external_value(PARAM_TEXT, 'fullname of user'),
            'comment' => new external_value(PARAM_TEXT, 'commenttext'),
            'timemodified' => new external_value(PARAM_INT, 'timemodified'),
            'profileimageurl' => new external_value(PARAM_TEXT, ''),
            'file' => new external_single_structure(array(
                'filename' => new external_value(PARAM_TEXT, 'filename'),
                'file' => new external_value(PARAM_URL, 'file url'),
                'mimetype' => new external_value(PARAM_TEXT, 'mime type for file'),
            ), "", VALUE_OPTIONAL),
        )));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function diggrplus_get_examples_and_items_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'courseid. Used if a topic is selected as filter', VALUE_DEFAULT, -1),
            'userid' => new external_value(PARAM_INT, 'id of user'),
            'compid' => new external_value(PARAM_INT, 'id of subject(3)/topic(1)/descriptor(0)/example(4)   if <= 0 then show all items for user'),
            'comptype' => new external_value(PARAM_INT, 'Type of competence: subject/topic/descriptor/example      if <= 0 then show all items for user'),
            'type' => new external_value(PARAM_TEXT, 'examples, own_items or empty', VALUE_DEFAULT, ""),
            'search' => new external_value(PARAM_TEXT, 'search string', VALUE_DEFAULT, ""),
            'niveauid' => new external_value(PARAM_INT, 'niveauid normally stands for LFS1, LFS2, etc.', VALUE_DEFAULT, -1),
            'status' => new external_value(PARAM_TEXT, 'new, inprogress, submitted, completed.  acts as a filter', VALUE_DEFAULT, ""),
        ));
    }

    /**
     * Get Items
     * get all items AND examples for a competence
     * they will be returned in one array, even though their fields may vary, but it makes ordering according to filters easier for the backend
     *
     * @ws-type-read
     * @return array of items
     */
    public static function diggrplus_get_examples_and_items($courseid = -1, $userid = null, $compid = null, $comptype = null, $type = "", $search = "", $niveauid = -1, $status = "") {
        global $DB, $USER;

        if ($userid == 0) {
            $userid = $USER->id;
        }

        static::validate_parameters(static::diggrplus_get_examples_and_items_parameters(), array(
            'courseid' => $courseid,
            'userid' => $userid,
            'compid' => $compid,
            'comptype' => $comptype,
            'type' => $type,
            'search' => $search,
            'niveauid' => $niveauid,
            'status' => $status,
        ));

        static::require_can_access_user($userid);

        // bei compid=0, haben wir keinen comptype
        if ($compid <= 0) {
            $comptype = -1;
            $compid = -1;
        }

        if ($niveauid <= 0) {
            $niveauid = -1;
        }

        $examplesAndItems = array();

        if (($type == "own_items" || $type == "") && $status != "new") { // own items can never be "new" since new means there is an example without an item
            $items = block_exacomp_get_items_for_competence($userid, $compid, $comptype, $search, $niveauid, $status);

            foreach ($items as $item) {
                static::require_can_access_comp($item->exacomp_record_id, 0, $comptype);
                //TODO: what should be checked? I think there are no restrictions YET. But for free work that has not been assigned, there will have to be some "sumbmission" or "show to teacher" button
                // ==> Then the access can be checked RW
                static::block_exacomp_get_item_details($item, $userid, static::wstoken()); //this adds file and commentinformation
            }

            $examplesAndItems = array_merge($examplesAndItems, array_map(function($item) {
                $objDeeper = new stdClass();
                $objDeeper->courseid = $item->courseid;
                $objDeeper->item = $item;
                $objDeeper->subjecttitle = $item->subjecttitle;
                $objDeeper->subjectid = $item->subjectid;
                $objDeeper->topictitle = $item->topictitle ? static::custom_htmltrim($item->topictitle) : "";
                $objDeeper->topicid = $item->topicid ? $item->topicid : 0;
                $objDeeper->niveautitle = "";
                $objDeeper->niveauid = 0;
                $objDeeper->timemodified = $item->timemodified;
                return $objDeeper;
            }, $items));
        }

        if ($type == "examples" || $type == "") {
            // Now examples. If the comptype is not an example itself
            if ($comptype != BLOCK_EXACOMP_TYPE_EXAMPLE) {
                // TODO: how do we check if the user is a teacher? It is not oriented on courses
                //            $isTeacher = false;
                $examples = static::block_exacomp_get_examples_for_competence_and_user($userid, $compid, $comptype, static::wstoken(), $search, $niveauid, $status, $courseid);
                $examplesAndItems = array_merge($examplesAndItems, $examples);
            }

            if ($status == "new") {
                // if filtered by "new" then only examples without items should be shown
                // with an item it is "in Arbeit", "Abgegeben" or "Abgeschlossen"
                foreach ($examplesAndItems as $key => $exampleAndItem) {
                    if ($exampleAndItem->item) {
                        unset($examplesAndItems[$key]);
                    }
                }

                // Filter ob die Aufgabe dem Schüler schon einmal zugeteilt wurden. (d.h. bereits im Schüler Planungsspeicher oder wurde vom Lehrer in den Wochenplan gelegt), bzw. er selbst in den Planungsspeicher/Wochenplan gelegt hat.
                // filter only examples, which are in the calendar
                $sql = "SELECT DISTINCT exampleid, exampleid AS tmp FROM {block_exacompschedule} WHERE deleted=0 AND studentid=?";
                $visibleExamples = $DB->get_records_sql_menu($sql, [$userid]);
                foreach ($examplesAndItems as $key => $exampleAndItem) {
                    if (empty($visibleExamples[$exampleAndItem->example->id])) {
                        unset($examplesAndItems[$key]);
                    }
                }
            }
        }


        // TODO: we can actually forget about examplegradings, right?
        foreach ($examplesAndItems as $exampleAndItem) {
            $exampleAndItem->status = block_exacomp_get_human_readable_item_status($exampleAndItem->item ? $exampleAndItem->item->status : null);

            if ($exampleAndItem->item) {
                $student = g::$DB->get_record('user', array(
                    'id' => $exampleAndItem->item->userid,
                ));

                if ($student) {
                    $userpicture = new user_picture($student);
                    $userpicture->size = 1; // Size f1.

                    $exampleAndItem->item->owner = (object)[
                        'userid' => $student->id,
                        'fullname' => fullname($student),
                        'profileimageurl' => $userpicture->get_url(g::$PAGE)->out(false),
                    ];
                }

                $exampleAndItem->item->solutiondescription = $exampleAndItem->item->intro;
            }
        }

        //Filter by status and use different sortings depending on status
        if ($status == "inprogress" || $status == "submitted" || $status == "completed") {
            foreach ($examplesAndItems as $key => $exampleAndItem) {
                if ($exampleAndItem->status != $status) {
                    unset($examplesAndItems[$key]);
                }
            }
            usort($examplesAndItems, function($a, $b) {
                return strcmp($b->timemodified, $a->timemodified);
            });
        }
        //if status is "new" it is already sorted correctly and has been filtered before
        //if stauts is "" then it is already sorted correctly and no filters are applied
        return $examplesAndItems;
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function diggrplus_get_examples_and_items_returns() {
        return new external_multiple_structure(new external_single_structure(array(
            'courseid' => new external_value(PARAM_INT, ''),
            'status' => new external_value(PARAM_TEXT, 'ENUM(new, inprogress, submitted, completed)'),
            'subjectid' => new external_value(PARAM_INT, 'id of subject'),
            'subjecttitle' => new external_value(PARAM_TEXT, 'title of subject'),
            'topicid' => new external_value(PARAM_INT, 'id of topic'),
            'topictitle' => new external_value(PARAM_TEXT, 'title of topic'),

            'niveautitle' => new external_value(PARAM_TEXT, 'title of niveau'),
            'niveauid' => new external_value(PARAM_INT, 'id of niveau'),

            'timemodified' => new external_value(PARAM_INT, 'time the item was last modified --> not gradings, but only changes to the item (files, comments, name, collaborators)'),

            'example' => new external_single_structure(array(
                'id' => new external_value(PARAM_INT, 'id of example'),
                'title' => new external_value(PARAM_TEXT, 'title of example'),
                'description' => new external_value(PARAM_TEXT, 'description of example'),
                'annotation' => new external_value(PARAM_TEXT, 'annotation by the teacher for this example in this course'),
                //                'taskfileurl' => new external_value(PARAM_TEXT, 'task fileurl'),
                //                'taskfilenames' => new external_value(PARAM_TEXT, 'task filename'),
                'solutionfilename' => new external_value(PARAM_TEXT, 'task filename'),
                'externalurl' => new external_value(PARAM_TEXT, 'externalurl of example'),
                'externaltask' => new external_value(PARAM_TEXT, 'url of associated module'),
                'externaltask_embedded' => new external_value(PARAM_TEXT, 'url of associated module, link to embedded view in exacomp', VALUE_OPTIONAL),
                //                'taskfilecount' => new external_value(PARAM_TEXT, 'number of files for the task'),
                'solution' => new external_value(PARAM_TEXT, 'solution(url/description) of example'),
                'timeframe' => new external_value(PARAM_TEXT, 'timeframe as string'),  //timeframe in minutes?? not anymore, it can be "4 hours" as well for example
                'hassubmissions' => new external_value(PARAM_BOOL, 'true if example has already submissions'),
                'solution_visible' => new external_value(PARAM_BOOL, 'visibility for example solution in current context'),
                //                'exampletaxonomies' => new external_value(PARAM_TEXT, 'taxonomies seperated by comma'),
                //                'exampletaxids' => new external_value(PARAM_TEXT, 'taxids seperated by comma'),

                'taskfiles' => new external_multiple_structure(new external_single_structure(array(
                    'name' => new external_value(PARAM_TEXT, 'title of taskfile'),
                    'url' => new external_value(PARAM_URL, 'file url'),
                    'type' => new external_value(PARAM_TEXT, 'mime type for file'),
                    //                    'fileindex' => new external_value(PARAM_TEXT, 'fileindex, used for deleting this file')
                )), 'taskfiles of the example', VALUE_OPTIONAL),

                'teacher_evaluation' => new external_value(PARAM_INT, 'teacher_evaluation'),
                'student_evaluation' => new external_value(PARAM_INT, 'student_evaluation'),
            ), 'example information', VALUE_OPTIONAL),
            'item' => new external_single_structure(array(
                'id' => new external_value(PARAM_INT, 'id of item '),
                'name' => new external_value(PARAM_TEXT, 'title of item'),
                'solutiondescription' => new external_value(PARAM_TEXT, 'description of item', VALUE_OPTIONAL),
                'type' => new external_value(PARAM_TEXT, 'type of item ENUM(note,file,link)', VALUE_OPTIONAL),
                'url' => new external_value(PARAM_TEXT, 'url', VALUE_OPTIONAL),
                'effort' => new external_value(PARAM_RAW, 'description of the effort', VALUE_OPTIONAL),
                //                'status' => new external_value(PARAM_INT, 'status of the submission', VALUE_OPTIONAL),
                'teachervalue' => new external_value(PARAM_INT, 'teacher grading', VALUE_OPTIONAL),
                'studentvalue' => new external_value(PARAM_INT, 'student grading', VALUE_OPTIONAL),
                'teachercomment' => new external_value(PARAM_TEXT, 'teacher comment', VALUE_OPTIONAL),
                'studentcomment' => new external_value(PARAM_TEXT, 'student comment', VALUE_OPTIONAL),
                'owner' => new external_single_structure(array(
                    'userid' => new external_value(PARAM_INT, ''),
                    'fullname' => new external_value(PARAM_TEXT, ''),
                    'profileimageurl' => new external_value(PARAM_TEXT, ''),
                )),
                'studentfiles' => new external_multiple_structure(new external_single_structure(array(
                    'id' => new external_value(PARAM_INT, 'id'),
                    'filename' => new external_value(PARAM_TEXT, 'filename'),
                    'file' => new external_value(PARAM_URL, 'file url'),
                    'mimetype' => new external_value(PARAM_TEXT, 'mime type for file'),
                    'fileindex' => new external_value(PARAM_TEXT, 'fileindex, used for deleting this file'),
                )), "files of the student's submission", VALUE_OPTIONAL),
                'collaborators' => new external_multiple_structure(new external_single_structure(array(
                    'userid' => new external_value(PARAM_INT, 'userid of collaborator'),
                    'fullname' => new external_value(PARAM_TEXT, ''),
                    'profileimageurl' => new external_value(PARAM_TEXT, ''),
                )), 'collaborators', VALUE_OPTIONAL),
            ), 'item information', VALUE_OPTIONAL),
        )));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function diggrplus_get_teacher_examples_and_items_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, ''),
            'studentid' => new external_value(PARAM_INT, ''),
            'compid' => new external_value(PARAM_INT, 'id of topic/descriptor/example   if <= 0 then show all items for user'),
            'comptype' => new external_value(PARAM_INT, 'Type of competence: topic/descriptor/example      if <= 0 then show all items for user'),
            'type' => new external_value(PARAM_TEXT, 'examples, own_items or empty', VALUE_DEFAULT, ""),
            'search' => new external_value(PARAM_TEXT, 'search string', VALUE_DEFAULT, ''),
            'niveauid' => new external_value(PARAM_INT, 'niveauid normally stands for LFS1, LFS2, etc.', VALUE_DEFAULT, -1),
            'status' => new external_value(PARAM_TEXT, 'new, inprogress, submitted, completed.  acts as a filter', VALUE_DEFAULT, ""),
        ));
    }

    /**
     * Get Items
     * get all items AND examples for a competence
     * they will be returned in one array, even though their fields may vary, but it makes ordering according to filters easier for the backend
     *
     * @ws-type-read
     * @return array of items
     */
    public static function diggrplus_get_teacher_examples_and_items($courseid, $studentid, $compid, $comptype, $type = "", $search = "", $niveauid = -1, $status = "") {
        global $DB, $USER;

        static::validate_parameters(static::diggrplus_get_teacher_examples_and_items_parameters(), array(
            'courseid' => $courseid,
            'studentid' => $studentid,
            'compid' => $compid,
            'comptype' => $comptype,
            'type' => $type,
            'search' => $search,
            'niveauid' => $niveauid,
            'status' => $status,
        ));

        block_exacomp_require_teacher($courseid);

        $teacherid = $USER->id;

        // bei compid=0, haben wir keinen comptype
        if ($compid <= 0) {
            $comptype = -1;
            $compid = -1;
        }

        if ($niveauid <= 0) {
            $niveauid = -1;
        }

        if ($courseid) {
            $courses = static::get_courses();
            $courses = array_filter($courses, function($course) use ($courseid) {
                return $course['courseid'] == $courseid;
            });
        } else {
            $courses = static::get_courses();
        }

        $students = [];
        foreach ($courses as $course) {
            $course = (object)$course;
            $courseStudents = block_exacomp_get_students_by_course($course->courseid);
            foreach ($courseStudents as $student) {
                if ($studentid && $student->id != $studentid) {
                    // don't add to students array
                    continue;
                }
                $students[$student->id] = $student;
            }
        }

        $niveauid = 0;
        $niveautitle = '';
        if ($comptype == BLOCK_EXACOMP_TYPE_EXAMPLE) {
            // for a single example, also read the niveau information, which is used later to fill the object
            $niveau_info = current($DB->get_records_sql("SELECT DISTINCT n.id as niveauid, n.title as niveautitle
                FROM {" . BLOCK_EXACOMP_DB_NIVEAUS . "} n
                JOIN {" . BLOCK_EXACOMP_DB_DESCRIPTORS . "} descr ON descr.niveauid = n.id
                JOIN {" . BLOCK_EXACOMP_DB_DESCEXAMP . "} dex ON dex.descrid = descr.id
                WHERE dex.exampid=? ORDER BY niveauid", [$compid]));
            if ($niveau_info) {
                $niveauid = $niveau_info->niveauid;
                $niveautitle = $niveau_info->niveautitle;
            }
        }

        $examplesAndItems = array();

        foreach ($students as $student) {
            $userid = $student->id;

            $studentExamplesAndItems = [];

            if (($type == "own_items" || $type == "") && $status != "new") {
                $items = block_exacomp_get_items_for_competence($userid, $compid, $comptype, $search, $niveauid, $status, $courseid);

                foreach ($items as $item) {
                    // no check needed here, also doesn't work correctly with comptype=example
                    // static::require_can_access_comp($item->exacomp_record_id, 0, $comptype);
                    //TODO: what should be checked? I think there are no restrictions YET. But for free work that has not been assigned, there will have to be some "sumbmission" or "show to teacher" button
                    // ==> Then the access can be checked RW
                    static::block_exacomp_get_item_details($item, $userid, static::wstoken());
                }

                $studentExamplesAndItems = array_merge($studentExamplesAndItems, array_map(function($item) use ($niveauid, $niveautitle) {
                    $objDeeper = new stdClass();
                    $objDeeper->courseid = $item->courseid;
                    $objDeeper->item = $item;
                    $objDeeper->subjecttitle = $item->subjecttitle;
                    $objDeeper->subjectid = $item->subjectid;
                    $objDeeper->topictitle = $item->topictitle ? $item->topictitle : "";
                    $objDeeper->topicid = $item->topicid ? $item->topicid : 0;
                    $objDeeper->niveautitle = $niveautitle;
                    $objDeeper->niveauid = $niveauid;
                    $objDeeper->timemodified = $item->timemodified;
                    return $objDeeper;
                }, $items));
            }

            if ($type == "examples" || $type == "") {
                // Now examples. If the comptype is not an example itself
                if ($comptype != BLOCK_EXACOMP_TYPE_EXAMPLE) {
                    // TODO: how do we check if the user is a teacher? It is not oriented on courses
                    //            $isTeacher = false;
                    $examples = static::block_exacomp_get_examples_for_competence_and_user($userid, $compid, $comptype, static::wstoken(), $search, $niveauid, $status, $courseid);
                    $studentExamplesAndItems = array_merge($studentExamplesAndItems, $examples);
                }
            }

            foreach ($studentExamplesAndItems as $studentExampleAndItem) {
                if ($studentExampleAndItem->item) {
                    $userpicture = new user_picture($student);
                    $userpicture->size = 1; // Size f1.

                    $studentExampleAndItem->item->owner = (object)[
                        'userid' => $student->id,
                        'fullname' => fullname($student),
                        'profileimageurl' => $userpicture->get_url(g::$PAGE)->out(false),
                    ];

                    $studentExampleAndItem->item->solutiondescription = $studentExampleAndItem->item->intro;
                }
            }

            $examplesAndItems = array_merge($examplesAndItems, $studentExamplesAndItems);
        }

        // array_unique with SORT_REGULAR compares using "==", not "===". It compares the properties, not for object identity. We want to compare the properties --> good
        // also tested: it goes deep, it e.g. compared the item->timemodified.. if those are not ==, the whole thing is not ==
        $examplesAndItems = array_unique($examplesAndItems, SORT_REGULAR);
        foreach ($examplesAndItems as $key => $exampleAndItem) {
            if ($exampleAndItem->item) {
                if ($status == "new") { // if filtered by "new" then only examples without items should be shown
                    unset($examplesAndItems[$key]);
                } else {
                    $exampleAndItem->status = block_exacomp_get_human_readable_item_status($exampleAndItem->item->status);
                }
            } else { //no item but the object exists ==> there must be an example, no condition needed
                $exampleAndItem->status = "new";
            }
        }

        //Filter by status and use different sortings depending on status
        if ($status == "inprogress" || $status == "submitted" || $status == "completed") {
            foreach ($examplesAndItems as $key => $exampleAndItem) {
                if ($exampleAndItem->status != $status) {
                    unset($examplesAndItems[$key]);
                }
            }
            usort($examplesAndItems, function($a, $b) {
                return strcmp($b->timemodified, $a->timemodified);
            });
        }

        return $examplesAndItems;
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function diggrplus_get_teacher_examples_and_items_returns() {
        return new external_multiple_structure(new external_single_structure(array(
            'courseid' => new external_value(PARAM_INT, ''),
            'status' => new external_value(PARAM_TEXT, 'ENUM(new, inprogress, submitted, completed)'),
            'subjectid' => new external_value(PARAM_INT, 'id of subject'),
            'subjecttitle' => new external_value(PARAM_TEXT, 'title of subject'),
            'topicid' => new external_value(PARAM_INT, 'id of topic'),
            'topictitle' => new external_value(PARAM_TEXT, 'title of topic'),

            'niveautitle' => new external_value(PARAM_TEXT, 'title of niveau'),
            'niveauid' => new external_value(PARAM_INT, 'id of niveau'),

            'timemodified' => new external_value(PARAM_INT, 'time the item was last modified --> not gradings, but only changes to the item (files, comments, name, collaborators)'),

            'example' => new external_single_structure(array(
                'id' => new external_value(PARAM_INT, 'id of example'),
                'title' => new external_value(PARAM_TEXT, 'title of example'),
                'description' => new external_value(PARAM_TEXT, 'description of example'),
                'annotation' => new external_value(PARAM_TEXT, 'annotation by the teacher for this example in this course'),
                //                'taskfileurl' => new external_value(PARAM_TEXT, 'task fileurl'),
                //                'taskfilenames' => new external_value(PARAM_TEXT, 'task filename'),
                'solutionfilename' => new external_value(PARAM_TEXT, 'task filename'),
                'externalurl' => new external_value(PARAM_TEXT, 'externalurl of example'),
                'externaltask' => new external_value(PARAM_TEXT, 'url of associated module'),
                'externaltask_embedded' => new external_value(PARAM_TEXT, 'url of associated module, link to embedded view in exacomp', VALUE_OPTIONAL),
                //                'taskfilecount' => new external_value(PARAM_TEXT, 'number of files for the task'),
                'solution' => new external_value(PARAM_TEXT, 'solution(url/description) of example'),
                'timeframe' => new external_value(PARAM_TEXT, 'timeframe as string'),  //timeframe in minutes?? not anymore, it can be "4 hours" as well for example
                'hassubmissions' => new external_value(PARAM_BOOL, 'true if example has already submissions'),
                'solution_visible' => new external_value(PARAM_BOOL, 'visibility for example solution in current context'),
                //                'exampletaxonomies' => new external_value(PARAM_TEXT, 'taxonomies seperated by comma'),
                //                'exampletaxids' => new external_value(PARAM_TEXT, 'taxids seperated by comma'),

                'taskfiles' => new external_multiple_structure(new external_single_structure(array(
                    'name' => new external_value(PARAM_TEXT, 'title of taskfile'),
                    'url' => new external_value(PARAM_URL, 'file url'),
                    'type' => new external_value(PARAM_TEXT, 'mime type for file'),
                    //                    'fileindex' => new external_value(PARAM_TEXT, 'fileindex, used for deleting this file')
                )), 'taskfiles of the example', VALUE_OPTIONAL),

                'teacher_evaluation' => new external_value(PARAM_INT, 'teacher_evaluation'),
                'student_evaluation' => new external_value(PARAM_INT, 'student_evaluation'),
            ), 'example information', VALUE_OPTIONAL),
            'item' => new external_single_structure(array(
                'id' => new external_value(PARAM_INT, 'id of item '),
                'name' => new external_value(PARAM_TEXT, 'title of item'),
                'solutiondescription' => new external_value(PARAM_TEXT, 'description of item', VALUE_OPTIONAL),
                'type' => new external_value(PARAM_TEXT, 'type of item ENUM(note,file,link)', VALUE_OPTIONAL),
                'url' => new external_value(PARAM_TEXT, 'url', VALUE_OPTIONAL),
                'effort' => new external_value(PARAM_RAW, 'description of the effort', VALUE_OPTIONAL),
                //                'status' => new external_value(PARAM_INT, 'status of the submission', VALUE_OPTIONAL),
                'teachervalue' => new external_value(PARAM_INT, 'teacher grading', VALUE_OPTIONAL),
                'studentvalue' => new external_value(PARAM_INT, 'student grading', VALUE_OPTIONAL),
                'teachercomment' => new external_value(PARAM_TEXT, 'teacher comment', VALUE_OPTIONAL),
                'studentcomment' => new external_value(PARAM_TEXT, 'student comment', VALUE_OPTIONAL),
                'owner' => new external_single_structure(array(
                    'userid' => new external_value(PARAM_INT, ''),
                    'fullname' => new external_value(PARAM_TEXT, ''),
                    'profileimageurl' => new external_value(PARAM_TEXT, ''),
                )),
                'studentfiles' => new external_multiple_structure(new external_single_structure(array(
                    'id' => new external_value(PARAM_INT, 'id'),
                    'filename' => new external_value(PARAM_TEXT, 'filename'),
                    'file' => new external_value(PARAM_URL, 'file url'),
                    'mimetype' => new external_value(PARAM_TEXT, 'mime type for file'),
                    'fileindex' => new external_value(PARAM_TEXT, 'fileindex, used for deleting this file'),
                )), "files of the student's submission", VALUE_OPTIONAL),
                'collaborators' => new external_multiple_structure(new external_single_structure(array(
                    'userid' => new external_value(PARAM_INT, 'userid of collaborator'),
                    'fullname' => new external_value(PARAM_TEXT, ''),
                    'profileimageurl' => new external_value(PARAM_TEXT, ''),
                )), 'collaborators', VALUE_OPTIONAL),
            ), 'item information', VALUE_OPTIONAL),
        )));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function diggrplus_get_all_subjects_for_course_as_tree_parameters() {
        return new external_function_parameters(array(
            'userid' => new external_value(PARAM_INT, 'id of user'),
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'subjectid' => new external_value(PARAM_INT, 'id of subject, if you only want one specific subject', VALUE_DEFAULT, null),
            // name $select is borrowed from ms graph api
            '$select' => new external_value(PARAM_TEXT, 'select extra fields', VALUE_DEFAULT, null),
        ));
    }

    /**
     * Get Subjects
     * get subjects from one user for one course
     *
     * @ws-type-read
     * @return array of user courses
     */
    public static function diggrplus_get_all_subjects_for_course_as_tree($userid, $courseid, $subjectid = null, $select = null) {
        global $USER, $DB;

        static::validate_parameters(static::diggrplus_get_all_subjects_for_course_as_tree_parameters(), array(
            'userid' => $userid,
            'courseid' => $courseid,
            'subjectid' => $subjectid,
            '$select' => $select,
        ));

        if (!$userid) {
            $userid = $USER->id;
        }
        static::require_can_access_user($userid);

        $structure = array();

        $course = get_course($courseid);

        $tree = block_exacomp_get_competence_tree($courseid, $subjectid, null, true, null, true, null, false, false, false, false, false);

        $getExampleStatus = function($example) use ($userid) {
            if (!$userid) {
                // only available when selecting one student
                return '';
            }

            $item = block_exacomp_get_current_item_for_example($userid, $example->id);
            return block_exacomp_get_human_readable_item_status($item ? $item->status : null);
        };

        $student = $DB->get_record('user', [
            'id' => $userid,
        ]);
        $student = block_exacomp_get_user_information_by_course($student, $courseid);

        $isglobal = block_exacomp_get_settings_by_course($courseid)->isglobal && block_exacomp_is_dakora_teacher($USER->id);

        if (!$select) {
            $add_extra_fields = function($comptype, $obj, $retObj) {
            };
        } else {
            $select = explode(',', $select);

            if ($diff = array_diff($select, ['teacherevaluationcount'])) {
                throw new \moodle_exception('unknown $select: ' . join(',', $diff));
            }
            $selectFields = array_flip($select);
            $add_extra_fields = function($comptype, $obj, $retObj, $subject) use ($selectFields, $userid, $isglobal) {
                if (isset($selectFields['teacherevaluationcount'])) {
                    if ($comptype != BLOCK_EXACOMP_TYPE_SUBJECT && $isglobal && $subject->isglobal) {
                        $compid = $obj->id;
                        $retObj->teacherevaluationcount = g::$DB->get_field(BLOCK_EXACOMP_DB_COMPETENCES, 'COUNT(*)', ['compid' => $compid, 'comptype' => $comptype, 'userid' => $userid, 'role' => BLOCK_EXACOMP_ROLE_TEACHER]);
                    }
                }
            };
        }

        foreach ($tree as $subject) {
            $elem_sub = new stdClass ();
            $elem_sub->id = $subject->id;
            $elem_sub->used_niveaus = $subject->used_niveaus;
            $elem_sub->title = static::custom_htmltrim(strip_tags($subject->title));
            $elem_sub->courseid = $courseid;
            $elem_sub->courseshortname = $course->shortname;
            $elem_sub->coursefullname = $course->fullname;
            $elem_sub->topics = array();
            $add_extra_fields(BLOCK_EXACOMP_TYPE_SUBJECT, $subject, $elem_sub, $subject);
            foreach ($subject->topics as $topic) {
                $elem_topic = new stdClass ();
                $elem_topic->id = $topic->id;
                $elem_topic->title = static::custom_htmltrim(strip_tags($topic->title));
                $elem_topic->descriptors = array();
                $elem_topic->visible = block_exacomp_is_topic_visible($courseid, $topic, $userid);
                $elem_topic->used = block_exacomp_is_topic_used($courseid, $topic, $userid);
                $elem_topic->teacherevaluation = $student->topics->teacher[$topic->id];
                $elem_topic->studentevaluation = $student->topics->student[$topic->id];
                $add_extra_fields(BLOCK_EXACOMP_TYPE_TOPIC, $topic, $elem_topic, $subject);
                foreach ($topic->descriptors as $descriptor) {
                    $elem_desc = new stdClass ();
                    $elem_desc->id = $descriptor->id;
                    $elem_desc->niveauid = $descriptor->niveauid;
                    $elem_desc->title = static::custom_htmltrim(strip_tags($descriptor->title));
                    $elem_desc->childdescriptors = array();
                    $elem_desc->visible = block_exacomp_is_descriptor_visible($courseid, $descriptor, $userid, false);
                    $elem_desc->used = block_exacomp_descriptor_used($courseid, $descriptor, $userid);
                    $elem_desc->teacherevaluation = $student->competencies->teacher[$descriptor->id];
                    $elem_desc->studentevaluation = $student->competencies->student[$descriptor->id];
                    $add_extra_fields(BLOCK_EXACOMP_TYPE_DESCRIPTOR, $descriptor, $elem_desc, $subject);
                    foreach ($descriptor->children as $child) {
                        $elem_child = new stdClass ();
                        $elem_child->id = $child->id;
                        $elem_child->niveauid = $child->niveauid;
                        $elem_child->title = static::custom_htmltrim(strip_tags($child->title));
                        $elem_child->examples = array();
                        $elem_child->visible = block_exacomp_is_descriptor_visible($courseid, $child, $userid, false);
                        $elem_child->used = block_exacomp_descriptor_used($courseid, $child, $userid);
                        $elem_child->teacherevaluation = $student->competencies->teacher[$child->id];
                        $elem_child->studentevaluation = $student->competencies->student[$child->id];
                        $add_extra_fields(BLOCK_EXACOMP_TYPE_DESCRIPTOR, $child, $elem_child, $subject);
                        foreach ($child->examples as $example) {
                            $elem_example = new stdClass ();
                            $elem_example->id = $example->id;
                            $elem_example->title = $example->title;
                            $elem_example->creatorid = $example->creatorid;
                            $elem_example->visible = $example->visible;
                            $elem_example->status = $getExampleStatus($example);
                            $elem_example->teacherevaluation = $student->examples->teacher[$example->id];
                            $elem_example->studentevaluation = $student->examples->student[$example->id];
                            $add_extra_fields(BLOCK_EXACOMP_TYPE_EXAMPLE, $example, $elem_example, $subject);
                            //                            $elem_example->used = $example->used;
                            $elem_child->examples[] = $elem_example;
                        }
                        $elem_desc->childdescriptors[] = $elem_child;
                    }
                    $elem_desc->examples = array();
                    foreach ($descriptor->examples as $example) {
                        $elem_example = new stdClass ();
                        $elem_example->id = $example->id;
                        $elem_example->title = $example->title;
                        $elem_example->creatorid = $example->creatorid;
                        $elem_example->visible = $example->visible;
                        $elem_example->status = $getExampleStatus($example);
                        $elem_example->teacherevaluation = $student->examples->teacher[$example->id];
                        $elem_example->studentevaluation = $student->examples->student[$example->id];
                        $elem_example->taxonomies = $example->taxonomies;
                        $add_extra_fields(BLOCK_EXACOMP_TYPE_EXAMPLE, $example, $elem_example, $subject);
                        //                        $elem_example->used = $example->used;
                        $elem_desc->examples[] = $elem_example;

                    }
                    $elem_topic->descriptors[] = $elem_desc;
                }
                $elem_sub->topics[] = $elem_topic;
            }
            if (!empty($elem_sub->topics)) {
                $structure[] = $elem_sub;
            }
        }

        return $structure;
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function diggrplus_get_all_subjects_for_course_as_tree_returns() {
        return new external_multiple_structure(new external_single_structure(array(
            'id' => new external_value(PARAM_INT, 'id of subject'),
            'title' => new external_value(PARAM_TEXT, 'title of subject'),
            'used_niveaus' => new external_multiple_structure(new external_single_structure(array(
                'id' => new external_value(PARAM_TEXT, 'id of niveau'),
                'title' => new external_value(PARAM_TEXT, 'title of niveau'),
            ))),
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'courseshortname' => new external_value(PARAM_TEXT, 'courseshortname'),
            'coursefullname' => new external_value(PARAM_TEXT, 'coursefullname'),
            'topics' => new external_multiple_structure(new external_single_structure(array(
                'id' => new external_value(PARAM_INT, 'id of topic'),
                'title' => new external_value(PARAM_TEXT, 'title of topic'),
                'visible' => new external_value(PARAM_BOOL, 'visibility of topic in current context '),
                'used' => new external_value(PARAM_BOOL, 'if topic is used'),
                'teacherevaluation' => new external_value(PARAM_INT, '', VALUE_DEFAULT, null),
                'studentevaluation' => new external_value(PARAM_INT, '', VALUE_DEFAULT, null),
                'teacherevaluationcount' => new external_value(PARAM_INT, '', VALUE_OPTIONAL, null),
                'descriptors' => new external_multiple_structure(new external_single_structure(array(
                    'id' => new external_value(PARAM_INT, 'id of descriptor'),
                    'niveauid' => new external_value(PARAM_INT, 'id of the niveau (column) of this descriptor'),
                    'title' => new external_value(PARAM_TEXT, 'title of descriptor'),
                    'visible' => new external_value(PARAM_BOOL, 'visibility of descriptor in current context '),
                    'used' => new external_value(PARAM_BOOL, 'if descriptor is used'),
                    'teacherevaluation' => new external_value(PARAM_INT, '', VALUE_DEFAULT, null),
                    'studentevaluation' => new external_value(PARAM_INT, '', VALUE_DEFAULT, null),
                    'teacherevaluationcount' => new external_value(PARAM_INT, '', VALUE_OPTIONAL, null),
                    'childdescriptors' => new external_multiple_structure(new external_single_structure(array(
                        'id' => new external_value(PARAM_INT, 'id of example'),
                        'niveauid' => new external_value(PARAM_INT, 'id of the niveau (column) of this descriptor'),
                        'title' => new external_value(PARAM_TEXT, 'title of example'),
                        'visible' => new external_value(PARAM_BOOL, 'visibility of descriptor in current context '),
                        'used' => new external_value(PARAM_BOOL, 'if descriptor is used'),
                        'teacherevaluation' => new external_value(PARAM_INT, '', VALUE_DEFAULT, null),
                        'studentevaluation' => new external_value(PARAM_INT, '', VALUE_DEFAULT, null),
                        'teacherevaluationcount' => new external_value(PARAM_INT, '', VALUE_OPTIONAL, null),
                        'examples' => new external_multiple_structure(new external_single_structure(array(
                            'id' => new external_value(PARAM_INT, 'id of example'),
                            'title' => new external_value(PARAM_TEXT, 'title of example'),
                            'creatorid' => new external_value(PARAM_INT, 'creator of this example'),
                            'visible' => new external_value(PARAM_BOOL, 'visibility of example in current context '),
                            'status' => new external_value(PARAM_TEXT, 'ENUM(new, inprogress, submitted, completed)'),
                            'teacherevaluation' => new external_value(PARAM_INT, '', VALUE_DEFAULT, null),
                            'studentevaluation' => new external_value(PARAM_INT, '', VALUE_DEFAULT, null),
                            'teacherevaluationcount' => new external_value(PARAM_INT, '', VALUE_OPTIONAL, null),
                        ))),
                    ))),
                    'examples' => new external_multiple_structure(new external_single_structure(array(
                        'id' => new external_value(PARAM_INT, 'id of example'),
                        'title' => new external_value(PARAM_TEXT, 'title of example'),
                        'creatorid' => new external_value(PARAM_INT, 'creator of this example'),
                        'visible' => new external_value(PARAM_BOOL, 'visibility of example in current context '),
                        'status' => new external_value(PARAM_TEXT, 'ENUM(new, inprogress, submitted, completed)'),
                        'teacherevaluation' => new external_value(PARAM_INT, '', VALUE_DEFAULT, null),
                        'studentevaluation' => new external_value(PARAM_INT, '', VALUE_DEFAULT, null),
                        'teacherevaluationcount' => new external_value(PARAM_INT, '', VALUE_OPTIONAL, null),
                        'taxonomies' => new external_multiple_structure(new external_single_structure([
                            'id' => new external_value(PARAM_INT, 'id'),
                            'title' => new external_value(PARAM_TEXT, 'name'),
                            'source' => new external_value(PARAM_TEXT, 'source'),
                        ]), 'values'),
                    ))),
                ))),
            ))),
        )));
    }






































    //    /**
    //     * Returns description of method parameters
    //     *
    //     * @return external_function_parameters
    //     */
    //    public static function diggrplus_get_all_examples_for_course_parameters() {
    //        return new external_function_parameters(array(
    //            'courseid' => new external_value(PARAM_INT, ''),
    //            'userid' => new external_value(PARAM_INT, ''),
    //        ));
    //    }
    //
    //    /**
    //     * Get Items
    //     * get all items AND examples for a competence
    //     * they will be returned in one array, even though their fields may vary, but it makes ordering according to filters easier for the backend
    //     * @ws-type-read
    //     * @return array of items
    //     */
    //    public static function diggrplus_get_all_examples_for_course($courseid, $userid, $search="") {
    //        global $USER;
    //
    //        static::validate_parameters(static::diggrplus_get_all_examples_for_course_parameters(), array(
    //            'courseid' => $courseid,
    //            'userid' => $userid,
    //        ));
    //
    //        // TODO: check if is teacher
    //
    //        $examples = block_exacomp_get_examples_by_course($courseid, true);
    //        foreach($examples as $example){
    //            static::block_excomp_get_example_details($example, $courseid);
    //        }
    //
    //        //block_exacomp_get_examples_for_competence_and_user is not well suited for this
    ////        //get all subjects of course
    ////        $subjects = block_exacomp_get_subjects_by_course($courseid);
    ////
    ////        //get all examples of these subjects
    ////        $examples = array();
    ////        foreach($subjects as $subject){
    ////            $courseExamples = static::block_exacomp_get_examples_for_competence_and_user($userid, $subject->id, BLOCK_EXACOMP_TYPE_SUBJECT, static::wstoken(), $search, -1, "", $courseid);
    ////            $examples = array_merge($courseExamples, $examples);
    ////        }
    //
    //        return $examples;
    //    }
    //
    //
    //
    //
    //    /**
    //     * Returns desription of method return values
    //     *
    //     * @return external_multiple_structure
    //     */
    //    public static function diggrplus_get_all_examples_for_course_returns() {
    //        return new external_multiple_structure(new external_single_structure(array(
    //
    //            'courseid' => new external_value(PARAM_INT, ''),
    //
    //            'subjectid' => new external_value(PARAM_INT, 'id of subject'),
    //            'subjecttitle' => new external_value(PARAM_TEXT, 'title of subject'),
    //            'topicid' => new external_value(PARAM_INT, 'id of topic'),
    //            'topictitle' => new external_value(PARAM_TEXT, 'title of topic'),
    //
    //            'niveautitle' => new external_value(PARAM_TEXT, 'title of niveau'),
    //            'niveauid' => new external_value(PARAM_INT, 'id of niveau'),
    //
    //
    //            'id' => new external_value(PARAM_INT, 'id of example'),
    //                'title' => new external_value(PARAM_TEXT, 'title of example'),
    //                'description' => new external_value(PARAM_TEXT, 'description of example'),
    ////                'taskfileurl' => new external_value(PARAM_TEXT, 'task fileurl'),
    ////                'taskfilenames' => new external_value(PARAM_TEXT, 'task filename'),
    //                'solution' => new external_value(PARAM_TEXT, 'task filename'),
    //                'externalurl' => new external_value(PARAM_TEXT, 'externalurl of example'),
    //                'externaltask' => new external_value(PARAM_TEXT, 'url of associated module'),
    ////                'taskfilecount' => new external_value(PARAM_TEXT, 'number of files for the task'),
    //                'solution' => new external_value(PARAM_TEXT, 'solution(url/description) of example'),
    //                'timeframe' => new external_value(PARAM_TEXT, 'timeframe as string'),  //timeframe in minutes?? not anymore, it can be "4 hours" as well for example
    //                'solution_visible' => new external_value(PARAM_BOOL, 'visibility for example solution in current context'),
    ////                'exampletaxonomies' => new external_value(PARAM_TEXT, 'taxonomies seperated by comma'),
    ////                'exampletaxids' => new external_value(PARAM_TEXT, 'taxids seperated by comma'),
    //
    //                'taskfiles' => new external_multiple_structure(new external_single_structure(array(
    //                    'name' => new external_value(PARAM_TEXT, 'title of taskfile'),
    //                    'url' => new external_value(PARAM_URL, 'file url'),
    //                    'type' => new external_value(PARAM_TEXT, 'mime type for file'),
    ////                    'fileindex' => new external_value(PARAM_TEXT, 'fileindex, used for deleting this file')
    //                )), 'taskfiles of the example', VALUE_OPTIONAL),
    //
    //            )));
    //    }
    //

    public static function diggrplus_get_user_info_parameters() {
        return new external_function_parameters(array());
    }

    /**
     * @ws-type-read
     *
     * @return array
     */
    public static function diggrplus_get_user_info() {
        global $DB, $USER;

        static::validate_parameters(static::diggrplus_get_user_info_parameters(), array());

        $role = block_exacomp_is_teacher_in_any_course() ? 'teacher' : 'student';

        return (object)[
            "role" => $role,
        ];
    }

    public static function diggrplus_get_user_info_returns() {
        return new external_function_parameters(array(
            'role' => new external_value(PARAM_TEXT),
        ));
    }

    public static function diggrplus_request_external_file_parameters() {
        return new external_function_parameters(array(
            'url' => new external_value(PARAM_URL, ''),
        ));
    }

    /**
     * Load a file from an external Domain to prevent CORS when loading directly in the App
     *
     * @ws-type-read
     */
    public static function diggrplus_request_external_file($url) {
        global $USER;

        static::validate_parameters(static::diggrplus_request_external_file_parameters(), array(
            'url' => $url,
        ));

        header("Content-Type: image");
        $content = file_get_contents($url);

        if (!$content) {
            send_file_not_found();
            // throw new \Exception('clound\'t load content');
        }

        header('Access-Control-Allow-Origin: *');
        send_file($content, basename($url), null, 0, true);
        exit;
    }

    public static function diggrplus_request_external_file_returns() {
        return new external_value(PARAM_FILE, '');
    }

    public static function diggrplus_grade_item_parameters() {
        return new external_function_parameters(array(
            'itemid' => new external_value(PARAM_INT, ''),
            'teachervalue' => new external_value(PARAM_INT, 'teacher grading of the item, -1 if none (leads to status "submitted" instead of "completed"', VALUE_DEFAULT, -1),
            // 'userid' => new external_value(PARAM_INT, 'id of student that should be graded'),
            // 'value' => new external_value(PARAM_INT, 'value for grading'),
            // 'status' => new external_value(PARAM_INT, 'status'),
            // 'comment' => new external_value(PARAM_TEXT, 'comment of grading', VALUE_OPTIONAL),
            // 'comps' => new external_value(PARAM_TEXT, 'comps for example - positive grading'),
            'descriptorgradings' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'descriptorid' => new external_value(PARAM_INT, 'id of descriptor'),
                        'teachervalue' => new external_value(PARAM_INT, 'teachervalue of descriptorgrading'),
                    )
                ), 'descriptors and gradingds', VALUE_OPTIONAL
            ),
        ));
    }

    /**
     * teacher grades and item in diggrplus
     *
     * @ws-type-write
     */
    public static function diggrplus_grade_item($itemid, $teachervalue = -1, $descriptorgradings = []) {
        global $DB, $USER;

        // if (empty ($userid) || empty ($value) || empty ($comment) || empty ($itemid) || empty ($courseid)) {
        // 	throw new invalid_parameter_exception ('Parameter can not be empty');
        // }

        static::validate_parameters(static::diggrplus_grade_item_parameters(), array(
            'itemid' => $itemid,
            'teachervalue' => $teachervalue,
            // 'userid' => $userid,
            // 'value' => $value,
            // 'status' => $status,
            // 'comment' => $comment,
            // 'comps' => $comps,
            // 'courseid' => $courseid,
            'descriptorgradings' => $descriptorgradings,
        ));

        $item = $DB->get_record('block_exaportitem', ['id' => $itemid], '*', MUST_EXIST);
        static::require_can_access_user($item->userid);

        // insert into block_exacompitem_mm
        $update = $DB->get_record(BLOCK_EXACOMP_DB_ITEM_MM, array(
            'itemid' => $itemid,
        ));

        //		$exampleid = $update->exacomp_record_id;

        $update->datemodified = time();
        $update->teachervalue = $teachervalue;

        if ($teachervalue != -1) {
            $update->status = 2; //student has submitted, teacher has graded ==> the item is completed
        } else {
            $update->status = 1; //change status back to "submitted"
        }

        $DB->update_record(BLOCK_EXACOMP_DB_ITEM_MM, $update);

        // Descriptorgradings TODO takes a long time
        $customdata = ['block' => 'exacomp', 'app' => 'diggrplus', 'courseid' => $item->courseid, 'itemid' => $itemid, 'itemuserid' => $USER->id];
        foreach ($descriptorgradings as $descriptorgrading) {
            block_exacomp_set_user_competence($item->userid, $descriptorgrading["descriptorid"], BLOCK_EXACOMP_TYPE_DESCRIPTOR, $item->courseid, BLOCK_EXACOMP_ROLE_TEACHER, $descriptorgrading["teachervalue"], null, -1, true, [
                'notification_customdata' => $customdata,
            ]);
        }

        // notification TODO takes a long time
        $customdata = ['block' => 'exacomp', 'app' => 'diggrplus', 'type' => 'grade_item', 'itemid' => $itemid, 'itemuserid' => $item->userid];
        $subject = block_exacomp_trans([
            'de:{$a->teacher} hat dein Beispiel "{$a->example}" als erledigt markiert',
            'en:{$a->teacher} has checked your solution "{$a->example}" as completed',
        ], ['teacher' => fullname($USER), 'example' => $item->name]);
        $notificationContext = block_exacomp_get_string('notification_submission_context');
        block_exacomp_send_notification("grading", $USER, $item->userid, $subject, '', $notificationContext, '', false, 0, $customdata);

        // if the grading is good, tick the example in exacomp TODO: NOT FOR DIGGRPLUS   examples are NOT graded, the grade is saved with the item ==> not compatible with dakora
        //		$exameval = $DB->get_record('block_exacompexameval', array(
        //			'exampleid' => $exampleid,
        //			'courseid' => $courseid,
        //			'studentid' => $userid,
        //		));
        //		if ($exameval) {
        //			$exameval->teacher_evaluation = 1;
        //			$DB->update_record('block_exacompexameval', $exameval);
        //		} else {
        //			$DB->insert_record('block_exacompexameval', array(
        //				'exampleid' => $exampleid,
        //				'courseid' => $courseid,
        //				'studentid' => $userid,
        //				'teacher_evaluation' => 1,
        //			));
        //		}
        //

        // Compared to grade_item() the descriptorgradings and comments are NOT done here, but the comments are done in a separate webservice
        // while the descriptors are done with block_exacomp_set_upser_competence

        return array(
            "success" => true,
        );
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function diggrplus_grade_item_returns() {
        return new external_single_structure(array(
            'success' => new external_value(PARAM_BOOL, 'true if grading was successful'),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function diggrplus_get_competence_profile_statistic_parameters() {
        return new external_function_parameters(array(
            'userid' => new external_value(PARAM_INT, '', VALUE_DEFAULT, 0),
            'courseid' => new external_value(PARAM_INT, '', VALUE_DEFAULT, 0),
        ));
    }

    /**
     * Get competence statistic for profile
     *
     * @ws-type-read
     */
    public static function diggrplus_get_competence_profile_statistic($userid = 0, $courseid = 0) {
        global $USER, $DB;

        static::validate_parameters(static::diggrplus_get_competence_profile_statistic_parameters(), array(
            'userid' => $userid,
            'courseid' => $courseid,
        ));

        if ($userid == 0) {
            $userid = $USER->id;
        }

        $courses = enrol_get_users_courses($userid);
        if ($courseid == 0) { //get all courses of the user
            //for each course check if I have access to the course, but don't use "require_can_access_course_user" since it should just skip, and not throw exeption
            foreach ($courses as $key => $course) {
                if (!static::can_access_course_user($course->id, $userid)) {
                    unset($courses[$key]);
                }
            }
        } else {//get only this specific course
            $courses = array($courses[$courseid]); //make array, so the rest of the code continues to work the same way as for multiple courses
        }

        $courseCondition = "(";
        foreach ($courses as $course) {
            $courseCondition .= $course->id . ", ";
        }
        $courseCondition = substr($courseCondition, 0, -2); //remove last ", "
        $courseCondition .= " )";

        //get all items: for now all items of topics, since other free_items do not exist in diggrplus
        $sql = 'SELECT i.id, i.name, ie.status, ie.teachervalue, ie.studentvalue, i.courseid
              FROM {block_exacomptopics} d
                JOIN {' . BLOCK_EXACOMP_DB_ITEM_MM . '} ie ON ie.exacomp_record_id = d.id
                JOIN {block_exaportitem} i ON ie.itemid = i.id
              WHERE i.userid = ?
                AND ie.competence_type = ' . BLOCK_EXACOMP_TYPE_TOPIC . '
                AND i.courseid IN ' . $courseCondition;
        $own_items = $DB->get_records_sql($sql, array($userid));

        $completed_items = 0;

        foreach ($own_items as $item) {
            if ($item->status == BLOCK_EXACOMP_ITEM_STATUS_COMPLETED && $item->teachervalue && $item->teachervalue > 0) { // free item that is submitted and has grade
                $completed_items++;
            }
        }

        // Until here: completed free items
        // From here: tree with gradings and finding out which and how many competencies are gained
        $competencies_gained = 0;
        $descriptorcount = 0;
        $examples = []; // if an example is in more than one descriptor, it will get overwritten => this is why a simple count++ would not work.
        $structure = array();

        foreach ($courses as $course) {
            //showallexamples filters out those, who have not creatorid => those who were imported
            $tree = block_exacomp_get_competence_tree($course->id, null, null, false, null, true, null, false, false, true, false, true);
            $students = block_exacomp_get_students_by_course($course->id);
            $student = $students[$userid]; // TODO: check if you are allowed to get this information. Student1 should not see results for student2
            block_exacomp_get_user_information_by_course($student, $course->id);
            foreach ($tree as $subject) {
                $elem_sub = new stdClass ();
                $elem_sub->id = $subject->id;
                $elem_sub->title = static::custom_htmltrim($subject->title);
                $elem_sub->courseid = $course->id;
                $elem_sub->courseshortname = $course->shortname;
                $elem_sub->coursefullname = $course->fullname;
                $elem_sub->teacherevaluation = $student->subjects->teacher[$subject->id];
                $elem_sub->studentevaluation = $student->subjects->student[$subject->id];
                $elem_sub->topics = array();
                foreach ($subject->topics as $topic) {
                    $elem_topic = new stdClass ();
                    $elem_topic->id = $topic->id;
                    $elem_topic->title = static::custom_htmltrim($topic->title);
                    $elem_topic->descriptors = array();
                    $elem_topic->teacherevaluation = $student->topics->teacher[$topic->id];
                    $elem_topic->studentevaluation = $student->topics->student[$topic->id];
                    //                $elem_topic->visible = block_exacomp_is_topic_visible($courseid, $topic, $userid);
                    //                $elem_topic->used = block_exacomp_is_topic_used($courseid, $topic, $userid);
                    foreach ($topic->descriptors as $descriptor) {
                        if (!$descriptor->visible) {
                            continue;
                        }
                        $descriptorcount++;
                        $elem_desc = new stdClass ();
                        $elem_desc->id = $descriptor->id;
                        $elem_desc->title = static::custom_htmltrim($descriptor->title);
                        $elem_desc->childdescriptors = array();
                        $elem_desc->teacherevaluation = $student->competencies->teacher[$descriptor->id];
                        $elem_desc->studentevaluation = $student->competencies->student[$descriptor->id];
                        //                    $elem_desc->visible = block_exacomp_is_descriptor_visible($courseid, $descriptor, $userid, false);
                        //                    $elem_desc->used = block_exacomp_descriptor_used($courseid, $descriptor, $userid);
                        // Childdescriptors are ignored in Diggplus --> not anymore RW 02.07.2021
                        foreach ($descriptor->children as $child) {
                            if (!$child->visible) {
                                continue;
                            }
                            $descriptorcount++; //TODO: show the child descriptors in the frontend!
                            $elem_child = new stdClass ();
                            $elem_child->id = $child->id;
                            $elem_child->title = static::custom_htmltrim($child->title);
                            $elem_child->teacherevaluation = $student->competencies->teacher[$child->id];
                            $elem_child->studentevaluation = $student->competencies->student[$child->id];
                            //                        $elem_child->visible = block_exacomp_is_descriptor_visible($courseid, $child, $userid, false);
                            //                        $elem_child->used = block_exacomp_descriptor_used($courseid, $child, $userid);
                            $elem_desc->childdescriptors[] = $elem_child;

                            //check all examples of this descriptor. If every example has a solved item ==> mark competence as gained in the bar graph. Or if there is a specific positive grading.
                            if ($elem_child->teacherevaluation) {
                                //                                if(!block_exacomp_value_is_negative_by_assessment($elem_child->teacherevaluation, BLOCK_EXACOMP_TYPE_DESCRIPTOR_CHILD)){
                                //                                    $competencies_gained++;
                                //                                }
                                //TODO: this is only a quickfix because grading is not generic yet
                                if ($elem_child->teacherevaluation > 0) {
                                    $competencies_gained++;
                                }
                            } else if ($child->examples) {
                                $gained = true;
                                foreach ($child->examples as $example) {
                                    //                                    if(!$example->visible){ // TODO: why are the childexamples even returned, but the parent examples not if they are invisible?
                                    //                                        continue;
                                    //                                    }
                                    $item = current(block_exacomp_get_items_for_competence($userid, $example->id, BLOCK_EXACOMP_TYPE_EXAMPLE));
                                    if ($item && $item->status == BLOCK_EXACOMP_ITEM_STATUS_COMPLETED && $item->teachervalue && $item->teachervalue > 0) {
                                        continue;
                                    } else {
                                        $gained = false;
                                    }
                                }
                                if ($gained) {
                                    $competencies_gained++;
                                }
                            }
                            //                            foreach ($child->examples as $ex){  //that was a fix because the visibility did not work in the tree function
                            //                                if(block_exacomp_is_example_visible($ex->courseid, $ex, $userid)){
                            //                                    $examples[$ex->id] = $ex;
                            //                                }
                            //                            }
                            $examples += $child->examples;
                        }
                        $elem_topic->descriptors[] = $elem_desc;

                        //check all examples of this descriptor. If every example has a solved item ==> mark competence as gained in the bar graph. Or if there is a specific positive grading.
                        if ($elem_desc->teacherevaluation) {
                            //                            if(!block_exacomp_value_is_negative_by_assessment($elem_desc->teacherevaluation, BLOCK_EXACOMP_TYPE_DESCRIPTOR_PARENT)){
                            //                                $competencies_gained++;
                            //                            }
                            //TODO: this is only a quickfix, since grading is not yet generic
                            if ($elem_desc->teacherevaluation > 0) {
                                $competencies_gained++;
                            }
                        } else if ($descriptor->examples) {
                            $gained = true;
                            foreach ($descriptor->examples as $example) {
                                $item = current(block_exacomp_get_items_for_competence($userid, $example->id, BLOCK_EXACOMP_TYPE_EXAMPLE));
                                if ($item && $item->status == BLOCK_EXACOMP_ITEM_STATUS_COMPLETED && $item->teachervalue && $item->teachervalue > 0) {
                                    continue;
                                } else {
                                    $gained = false;
                                }
                            }
                            if ($gained) {
                                $competencies_gained++;
                            }
                        }
                        //                        foreach ($descriptor->examples as $ex){  // that was a fix because the visibility did not work in the tree function
                        //                            if(block_exacomp_is_example_visible($ex->courseid, $ex, $userid)){
                        //                                $examples[$ex->id] = $ex;
                        //                            }
                        //                        }
                        $examples += $descriptor->examples;
                    }
                    $elem_sub->topics[] = $elem_topic;
                }
                if (!empty($elem_sub->topics)) {
                    $structure[] = $elem_sub;
                }
            }
        }

        // TODO: maybe I can do this while going through the tree already
        foreach ($examples as $example) {
            $item = current(block_exacomp_get_items_for_competence($userid, $example->id, BLOCK_EXACOMP_TYPE_EXAMPLE));
            if ($item && $item->status == BLOCK_EXACOMP_ITEM_STATUS_COMPLETED && $item->teachervalue && $item->teachervalue > 0) {
                $completed_items++;
            }
        }

        $statistics_return = [
            'items_and_examples_total' => count($own_items) + count($examples),
            'items_and_examples_completed' => $completed_items,
            'competencies_total' => $descriptorcount,
            'competencies_gained' => $competencies_gained,
            'competencetree' => $structure,
        ];

        return $statistics_return;
    }

    public static function diggrplus_get_competence_profile_statistic_returns() {
        return new external_single_structure(array(
            'items_and_examples_total' => new external_value(PARAM_INT, 'number of free items + examples'),
            'items_and_examples_completed' => new external_value(PARAM_INT, 'number of solved items, those items can be free or related to an example'),
            'competencies_total' => new external_value(PARAM_INT, ''),
            'competencies_gained' => new external_value(PARAM_INT, ''),
            'competencetree' => new external_multiple_structure(new external_single_structure(array(
                'id' => new external_value(PARAM_INT, 'id of subject'),
                'title' => new external_value(PARAM_TEXT, 'title of subject'),
                'courseid' => new external_value(PARAM_INT, 'id of course'),
                'courseshortname' => new external_value(PARAM_TEXT, 'courseshortname'),
                'coursefullname' => new external_value(PARAM_TEXT, 'coursefullname'),
                'teacherevaluation' => new external_value(PARAM_INT, 'teacher evaluation of subject'),
                'studentevaluation' => new external_value(PARAM_INT, 'student evaluation of subject'),
                'topics' => new external_multiple_structure(new external_single_structure(array(
                    'id' => new external_value(PARAM_INT, 'id of example'),
                    'title' => new external_value(PARAM_TEXT, 'title of topic'),
                    'teacherevaluation' => new external_value(PARAM_INT, 'teacher evaluation of topic'),
                    'studentevaluation' => new external_value(PARAM_INT, 'student evaluation of topic'),
                    'descriptors' => new external_multiple_structure(new external_single_structure(array(
                        'id' => new external_value(PARAM_INT, 'id of example'),
                        'title' => new external_value(PARAM_TEXT, 'title of descriptor'),
                        'teacherevaluation' => new external_value(PARAM_INT, 'teacher evaluation of descriptor'),
                        'studentevaluation' => new external_value(PARAM_INT, 'student evaluation of descriptor'),
                        'childdescriptors' => new external_multiple_structure(new external_single_structure(array(
                            'id' => new external_value(PARAM_INT, 'id of example'),
                            'title' => new external_value(PARAM_TEXT, 'title of example'),
                            'teacherevaluation' => new external_value(PARAM_INT, 'teacher evaluation of childdescriptor'),
                            'studentevaluation' => new external_value(PARAM_INT, 'student evaluation of childdescriptor'),
                        ))),
                    ))),
                ))),
            ))),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function diggrplus_get_descriptors_for_example_parameters() {
        return new external_function_parameters(array(
            'exampleid' => new external_value(PARAM_INT, 'id of example'),
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'userid' => new external_value(PARAM_INT, 'id of user'),
            'forall' => new external_value(PARAM_BOOL, 'for all users = true, for one user = false'),
            'mindvisibility' => new external_value(PARAM_BOOL, 'if a teacher wants to see the descriptors of an example in a hidden descriptor: set this to FALSE'),
        ));
    }

    /**
     * get descriptors where example is associated
     * Get descriptors for example
     *
     * @ws-type-read
     * @return list of descriptors
     */
    public static function diggrplus_get_descriptors_for_example($exampleid, $courseid, $userid, $forall, $mindvisibility) {
        global $DB, $USER;

        static::validate_parameters(static::diggrplus_get_descriptors_for_example_parameters(), array(
            'exampleid' => $exampleid,
            'courseid' => $courseid,
            'userid' => $userid,
            'forall' => $forall,
            'mindvisibility' => $mindvisibility,
        ));

        if ($userid == 0 && !$forall) {
            $userid = $USER->id;
        }

        static::require_can_access_course_user($courseid, $userid);

        if ($mindvisibility) {
            $non_visibilities = $DB->get_fieldset_select(BLOCK_EXACOMP_DB_DESCVISIBILITY, 'descrid', 'courseid=? AND studentid=? AND visible=0', array($courseid, 0));
        }

        if (!$forall) {
            $non_visibilities_student = $DB->get_fieldset_select(BLOCK_EXACOMP_DB_DESCVISIBILITY, 'descrid', 'courseid=? AND studentid=? AND visible=0', array($courseid, $userid));
        }

        $descriptors = static::_get_descriptors_for_example($exampleid, $courseid, $userid);

        $final_descriptors = array();
        foreach ($descriptors as $descriptor) {
            //to make sure everything has a value
            $descriptor->reviewername = null;
            $descriptor->reviewerid = null;
            $descriptor->id = $descriptor->descriptorid;
            $descriptor->evalniveauid = null;
            $descriptor_topic_mm = $DB->get_record(BLOCK_EXACOMP_DB_DESCTOPICS, array('descrid' => $descriptor->id), '*', IGNORE_MULTIPLE); // get only one topic relation
            $descriptor->topicid = $descriptor_topic_mm->topicid;

            $topic = topic::get($descriptor->topicid);
            if (block_exacomp_is_topic_visible($courseid, $topic, $userid) || !$mindvisibility) {
                $descriptor->numbering = block_exacomp_get_descriptor_numbering($descriptor);
                $descriptor->child = (($parentid = $DB->get_field(BLOCK_EXACOMP_DB_DESCRIPTORS, 'parentid', array('id' => $descriptor->id))) > 0) ? 1 : 0;
                $descriptor->parentid = $parentid;
                //new 16.05.2019 rw:
                //$descriptor->teacherevaluation = $descriptor->evaluation; //redundant? getting block_exacomp_get_comp_eval anyway
                $descriptor->teacherevaluation = -1; // never graded / SZ 07.04.2020
                $descriptor->studentevaluation = -1;
                $descriptor->timestampstudent = 0;
                if (!$forall) {
                    if ($grading = block_exacomp_get_comp_eval($courseid, BLOCK_EXACOMP_ROLE_STUDENT, $userid, BLOCK_EXACOMP_TYPE_DESCRIPTOR, $descriptor->id)) {
                        $descriptor->studentevaluation = ($grading->value !== null) ? $grading->value : -1;
                        $descriptor->timestampstudent = $grading->timestamp;
                    }
                }

                if (!$forall) {
                    if ($grading = block_exacomp_get_comp_eval($courseid, BLOCK_EXACOMP_ROLE_TEACHER, $userid, BLOCK_EXACOMP_TYPE_DESCRIPTOR, $descriptor->id)) {
                        $descriptor->teacherevaluation = ($grading->value !== null) ? $grading->value : -1;
                        $descriptor->additionalinfo = $grading->additionalinfo;
                        $descriptor->evalniveauid = $grading->evalniveauid;
                        $descriptor->timestampteacher = $grading->timestamp;
                        $descriptor->reviewerid = $grading->reviewerid;

                        //Reviewername finden
                        $reviewerid = $grading->reviewerid;
                        $reviewerTeacherFirstname = $DB->get_field('user', 'firstname', array('id' => $reviewerid));
                        $reviewerTeacherLastname = $DB->get_field('user', 'lastname', array('id' => $reviewerid));
                        $reviewerTeacherUsername = $DB->get_field('user', 'username', array('id' => $reviewerid));
                        if ($reviewerTeacherFirstname != null && $reviewerTeacherLastname != null) {
                            $reviewername = $reviewerTeacherFirstname . ' ' . $reviewerTeacherLastname;
                        } else {
                            $reviewername = $reviewerTeacherUsername;
                        }
                        $descriptor->reviewername = $reviewername;
                    }
                }

                if (!$forall) {
                    $descriptor->gradingisold = block_exacomp_is_descriptor_grading_old($descriptor->id, $userid);
                } else {
                    $descriptor->gradingisold = false;
                }

                if (!in_array($descriptor->descriptorid, $non_visibilities ?: []) && ((!$forall && !in_array($descriptor->descriptorid, $non_visibilities_student ?: [])) || $forall)) {
                    $final_descriptors[] = $descriptor;
                }
            }
        }

        return $final_descriptors;
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function diggrplus_get_descriptors_for_example_returns() {
        return new external_multiple_structure(new external_single_structure(array(
            'descriptorid' => new external_value(PARAM_INT, 'id of descriptor'),
            'title' => new external_value(PARAM_TEXT, 'title of descriptor'),
            'teacherevaluation' => new external_value(PARAM_INT, 'evaluation of descriptor'),
            'studentevaluation' => new external_value(PARAM_INT, 'student evaluation of descriptor'),
            'evalniveauid' => new external_value(PARAM_INT, 'evaluation niveau id'),
            'niveauid' => new external_value(PARAM_INT, 'id of niveau'),
            //            'niveautitle' => new external_value(PARAM_TEXT, 'title of niveau'),
            'additionalinfo' => new external_value(PARAM_FLOAT, 'additional grading for descriptor'),
            'topicid' => new external_value(PARAM_INT, 'id of topic'),
            'numbering' => new external_value(PARAM_TEXT, 'descriptor numbering'),
            'child' => new external_value(PARAM_BOOL, 'true: child, false: parent'),
            'parentid' => new external_value(PARAM_INT, 'parentid if child, 0 otherwise'),
            'gradingisold' => new external_value(PARAM_BOOL, 'true when there are newer gradings in the childcompetences', false),
            'reviewerid' => new external_value(PARAM_INT, 'id of reviewer'),
            'reviewername' => new external_value(PARAM_TEXT, 'name of reviewer'),
        )));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function dakora_grade_example_parameters() {
        return new external_function_parameters(array(
            'userid' => new external_value(PARAM_INT, 'userid'),
            'courseid' => new external_value(PARAM_INT, 'courseid'),
            'exampleid' => new external_value(PARAM_INT, 'exampleid'),
            'examplevalue' => new external_value(PARAM_INT, 'examplevalue'),
            'additionalinfo' => new external_value(PARAM_FLOAT, 'additionalInfo'),
            'exampleevalniveauid' => new external_value(PARAM_INT, 'example evaluation niveau id'),
            'itemid' => new external_value(PARAM_INT, 'itemid', VALUE_DEFAULT, -1),
            'comment' => new external_value(PARAM_TEXT, 'comment', VALUE_DEFAULT, ''),
            'url' => new external_value(PARAM_URL, 'url', VALUE_DEFAULT, ''),
            'filename' => new external_value(PARAM_TEXT, 'filename, used to look up file and create a new one in the exaport comment file area', VALUE_DEFAULT, ''),
            'fileitemid' => new external_value(PARAM_TEXT, 'fileitemid', VALUE_DEFAULT, ''),
        ));
    }

    /**
     * grade example solution
     * Add student submission to example.
     *
     * @ws-type-write
     * @param int $itemid (0 for new, >0 for existing)
     * @return array of course subjects
     */
    public static function dakora_grade_example($userid, $courseid, $exampleid, $examplevalue, $additionalInfo, $exampleevalniveauid, $itemid, $comment, $url, $filename, $fileitemid) {
        global $CFG, $DB, $USER;
        static::validate_parameters(static::dakora_grade_example_parameters(), array('userid' => $userid, 'courseid' => $courseid, 'exampleid' => $exampleid, 'examplevalue' => $examplevalue,
            'additionalinfo' => $additionalInfo, 'exampleevalniveauid' => $exampleevalniveauid, 'itemid' => $itemid, 'comment' => $comment, 'url' => $url, 'filename' => $filename, 'fileitemid' => $fileitemid));
        if ($userid == 0) {
            $role = BLOCK_EXACOMP_ROLE_STUDENT; // wann?
            $userid = $USER->id;
        } else {
            $role = BLOCK_EXACOMP_ROLE_TEACHER;
        }

        require_once $CFG->dirroot . '/blocks/exaport/inc.php';
        static::require_can_access_course($courseid);

        static::require_can_access_course_user($courseid, $userid);
        static::require_can_access_example($exampleid, $courseid);
        block_exacomp_set_user_example(($userid == 0) ? $USER->id : $userid, $exampleid, $courseid, $role, $examplevalue, $exampleevalniveauid, $additionalInfo);
        if ($role == BLOCK_EXACOMP_ROLE_TEACHER) {
            $example = (object)array(
                'userid' => $userid,
                'exampleid' => $exampleid,
                'value' => $examplevalue,
                'niveauid' => $exampleevalniveauid,
            );
            $examples = array($example);
            block_exacomp_etheme_autograde_examples_tree($courseid, $examples);
        }
        if ($itemid > 0 && $userid > 0) {    //So the student will never reach this part, either the itemid is null, or submit example is used
            $itemexample = $DB->get_record(BLOCK_EXACOMP_DB_ITEM_MM, array('exacomp_record_id' => $exampleid, 'itemid' => $itemid));
            if (!$itemexample) {
                throw new invalid_parameter_exception("Wrong itemid given");
            }
            $itemexample->datemodified = time();
            $itemexample->status = 1;

            $DB->update_record(BLOCK_EXACOMP_DB_ITEM_MM, $itemexample);
            if ($comment || $filename != '') {
                // 	            $oldComment = $DB->get_record('block_exaportitemcomm', array('itemid' => $itemid, 'userid' => $USER->id));
                // 	            if($oldComment){
                // 	                $oldComment->itemid = $itemid;
                // 	                $oldComment->userid = $USER->id;
                // 	                $oldComment->entry = $comment;
                // 	                $oldComment->timemodified = time();
                // 	                $DB->update_record('block_exaportitemcomm', $oldComment);
                // 	                $commentid = $oldComment->id;

                // 	                //delete the old file if new one is uploaded
                // 	                if($filename != ''){
                // 	                    $DB->delete_records('files', array('itemid' => $commentid, 'userid' => $USER->id, 'filearea' => 'item_comment_file', 'component' => 'block_exaport'));
                // 	                }
                // 	            }else{
                $insert = new stdClass ();
                $insert->itemid = $itemid;
                $insert->userid = $USER->id;
                $insert->entry = $comment;
                $insert->timemodified = time();
                $commentid = $DB->insert_record('block_exaportitemcomm', $insert, true);
                // 	            }

                $customdata = ['block' => 'exacomp', 'app' => 'dakora', 'type' => 'grade_example', 'itemid' => $itemid, 'itemuserid' => $insert->userid];
                block_exacomp_send_example_comment_notification($USER, $DB->get_record('user', array('id' => $userid)), $courseid, $exampleid, $comment, $customdata);
                example_commented::log(['objectid' => $exampleid, 'courseid' => $courseid]);

                if ($filename != '') {
                    $context = context_user::instance($USER->id);
                    $fs = get_file_storage();
                    try {
                        $old = $fs->get_file($context->id, "user", "draft", $fileitemid, "/", $filename);
                        if ($old) {
                            //TODO!!!!   contextid = 1 ?? immer??
                            $file_record = array('contextid' => 1, 'component' => 'block_exaport', 'filearea' => 'item_comment_file',
                                'itemid' => $commentid, 'filepath' => '/', 'filename' => $old->get_filename(),
                                'timecreated' => time(), 'timemodified' => time());
                            $fs->create_file_from_storedfile($file_record, $old->get_id());
                            $old->delete();
                        }
                    } catch (Exception $e) {
                        throw new invalid_parameter_exception("some problem with the file occured");
                        //some problem with the file occured
                    }
                }
            }
        }
        return array("success" => true, "exampleid" => $exampleid);
    }

    /**
     * Returns desription of method return values
     *
     * @return external_single_structure
     */
    public static function dakora_grade_example_returns() {
        return new external_single_structure(array(
            'success' => new external_value(PARAM_BOOL, 'status'),
            'exampleid' => new external_value(PARAM_INT, 'exampleid'),
        ));
    }

    public static function dakora_get_descriptors_details_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'courseid'),
            'descriptorids' => new external_value(PARAM_TEXT, 'list of descriptors, seperated by comma'),
            'userid' => new external_value(PARAM_INT, 'userid'),
            'forall' => new external_value(PARAM_BOOL, 'forall'),
            'crosssubjid' => new external_value(PARAM_INT, 'crosssubjid'),
        ));
    }

    /**
     * get descriptor details incl. grading and children for many descriptors
     *
     * @ws-type-read
     * @param $courseid
     * @param $descriptorid
     * @param $userid
     * @param $forall
     * @param $crosssubjid
     * @return stdClass
     */
    public static function dakora_get_descriptors_details($courseid, $descriptorids, $userid, $forall, $crosssubjid) {
        global $DB, $USER;
        static::validate_parameters(static::dakora_get_descriptors_details_parameters(),
            array('courseid' => $courseid, 'descriptorids' => $descriptorids, 'userid' => $userid, 'forall' => $forall, 'crosssubjid' => $crosssubjid));

        if (!$forall && $userid == 0) {
            $userid = $USER->id;
        }

        static::require_can_access_course_user($courseid, $userid);

        //get an arry of descriptorids
        $descriptors = explode(',', $descriptorids);
        $descriptors_return = array();
        $counter = 0;
        foreach ($descriptors as $descriptor) {
            $descriptors_return[$counter] = static::get_descriptor_details_private($courseid, $descriptor, $userid, $forall, $crosssubjid);
            $counter++;
        }

        //$descriptors_return = static::get_descriptor_details_private($courseid, $descriptorids, $userid, $forall, $crosssubjid);
        return $descriptors_return;
        // 	    return array("success" => true, "itemid" => 3);
    }

    public static function dakora_get_descriptors_details_returns() {
        return new external_multiple_structure(new external_single_structure(array(
            'reviewerid' => new external_value(PARAM_INT, 'id of reviewer'),
            'reviewername' => new external_value(PARAM_TEXT, 'name of reviewer'),
            'descriptorid' => new external_value(PARAM_INT, 'id of descriptor'),
            'parentid' => new external_value(PARAM_INT, 'id of parent of descriptor'),
            'descriptortitle' => new external_value(PARAM_TEXT, 'title of descriptor'),
            'teacherevaluation' => new external_value(PARAM_INT, 'teacher evaluation of descriptor'),
            'studentevaluation' => new external_value(PARAM_INT, 'student evaluation of descriptor'),
            'teacherevaluation' => new external_value(PARAM_INT, 'teacher evaluation of descriptor'),
            'additionalinfo' => new external_value(PARAM_FLOAT, 'additional grading for descriptor'),
            'timestampteacher' => new external_value(PARAM_INT, 'timestamp for teacher evaluation'),
            'timestampstudent' => new external_value(PARAM_INT, 'timestamp for student evaluation'),
            'evalniveauid' => new external_value(PARAM_INT, 'evaluation niveau id'),
            'numbering' => new external_value(PARAM_TEXT, 'numbering'),
            'categories' => new external_value(PARAM_TEXT, 'descriptor categories seperated by comma', VALUE_OPTIONAL),
            'niveauid' => new external_value(PARAM_INT, 'id of niveau'),
            'niveautitle' => new external_value(PARAM_TEXT, 'title of niveau'),
            'gradingisold' => new external_value(PARAM_BOOL, 'true when there are newer gradings in the childcompetences', false),
            'globalgradings' => new external_value(PARAM_RAW, 'Globalgradings as text', VALUE_OPTIONAL),
            'gradinghistory' => new external_value(PARAM_RAW, 'Gradinghistory as text', VALUE_OPTIONAL),
            'hasmaterial' => new external_value(PARAM_BOOL, 'true or false if descriptor has material'),
            'children' => new external_multiple_structure(new external_single_structure(array(
                'reviewerid' => new external_value(PARAM_INT, 'id of reviewer'),
                'reviewername' => new external_value(PARAM_TEXT, 'name of reviewer'),
                'descriptorid' => new external_value(PARAM_INT, 'id of descriptor'),
                'parentid' => new external_value(PARAM_INT, 'id of parent of descriptor'),
                'descriptortitle' => new external_value(PARAM_TEXT, 'title of descriptor'),
                'teacherevaluation' => new external_value(PARAM_INT, 'teacher evaluation of descriptor'),
                'additionalinfo' => new external_value(PARAM_FLOAT, 'additional grading for descriptor'),
                'evalniveauid' => new external_value(PARAM_INT, 'evaluation niveau id'),
                'timestampteacher' => new external_value(PARAM_INT, 'timestamp of teacher evaluation'),
                'studentevaluation' => new external_value(PARAM_INT, 'student evaluation of descriptor'),
                'timestampstudent' => new external_value(PARAM_INT, 'timestamp of student evaluation'),
                'numbering' => new external_value(PARAM_TEXT, 'numbering'),
                'globalgradings' => new external_value(PARAM_RAW, 'Globalgradings as text', VALUE_OPTIONAL),
                'gradinghistory' => new external_value(PARAM_RAW, 'Gradinghistory as text', VALUE_OPTIONAL),
                'hasmaterial' => new external_value(PARAM_BOOL, 'true or false if descriptor has material'),
                'examples' => new external_multiple_structure(new external_single_structure(array(
                    'exampleid' => new external_value(PARAM_INT, 'id of example'),
                    'exampletitle' => new external_value(PARAM_TEXT, 'title of example'),
                    'examplestate' => new external_value(PARAM_INT, 'state of example, always 0 if for all students'),
                    'visible' => new external_value(PARAM_INT, 'visibility of example in current context'),
                    'used' => new external_value(PARAM_INT, 'used in current context'),
                    'teacherevaluation' => new external_value(PARAM_INT, 'example evaluation of teacher'),
                    'studentevaluation' => new external_value(PARAM_INT, 'example evaluation of student'),
                    'timestampteacher' => new external_value(PARAM_INT, 'timestamp of teacher evaluation'),
                    'timestampstudent' => new external_value(PARAM_INT, 'timestamp of student evaluation'),
                    'evalniveauid' => new external_value(PARAM_INT, 'evaluation niveau id'),
                    'solution_visible' => new external_value(PARAM_BOOL, 'visibility for example solution in current context'),
                    'exampletaxonomies' => new external_value(PARAM_TEXT, 'taxonomies seperated by comma', VALUE_OPTIONAL),
                    'exampletaxids' => new external_value(PARAM_TEXT, 'taxids seperated by comma', VALUE_OPTIONAL),
                    'examplecreatorid' => new external_value(PARAM_INT, 'id of the creator of this example'),
                    'additionalinfo' => new external_value(PARAM_FLOAT, 'additional grading', VALUE_OPTIONAL),
                    'resubmission' => new external_value(PARAM_BOOL, 'resubmission is allowed/not allowed', VALUE_OPTIONAL),
                    'is_teacherexample' => new external_value(PARAM_BOOL, 'is a teacher example?', VALUE_OPTIONAL),
                ))),
                'examplestotal' => new external_value(PARAM_INT, 'total number of material'),
                'examplesvisible' => new external_value(PARAM_INT, 'visible number of material'),
                'examplesinwork' => new external_value(PARAM_INT, 'number of material in work'),
                'visible' => new external_value(PARAM_INT, 'visibility of children in current context'),
                'used' => new external_value(PARAM_INT, 'used in current context'),
                'examplesedited' => new external_value(PARAM_INT, 'number of edited material'),
                'examplegradings' => new external_single_structure(array(
                    'teacher' => new external_multiple_structure(new external_single_structure(array(
                        'evalniveauid' => new external_value(PARAM_INT, 'niveau id to according number', 0),
                        'value' => new external_value(PARAM_INT, 'grading value', 0),
                        'sum' => new external_value(PARAM_INT, 'number of gradings'),
                    ))),
                    'student' => new external_multiple_structure(new external_single_structure(array(
                        'sum' => new external_value(PARAM_INT, 'number of gradings'),
                    ))),
                )),
            ))),
            'childrengradings' => new external_single_structure(array(
                'teacher' => new external_multiple_structure(new external_single_structure(array(
                    'evalniveauid' => new external_value(PARAM_INT, 'niveau id to according number', 0),
                    'value' => new external_value(PARAM_INT, 'grading value', 0),
                    'sum' => new external_value(PARAM_INT, 'number of gradings'),
                ))),
                'student' => new external_multiple_structure(new external_single_structure(array(
                    'sum' => new external_value(PARAM_INT, 'number of gradings'),
                ))),
            )),
            'examples' => new external_multiple_structure(new external_single_structure(array(
                'exampleid' => new external_value(PARAM_INT, 'id of example'),
                'exampletitle' => new external_value(PARAM_TEXT, 'title of example'),
                'examplestate' => new external_value(PARAM_INT, 'state of example, always 0 if for all students'),
                'visible' => new external_value(PARAM_INT, 'visibility of example in current context'),
                'used' => new external_value(PARAM_INT, 'used in current context'),
                'teacherevaluation' => new external_value(PARAM_INT, 'example evaluation of teacher'),
                'studentevaluation' => new external_value(PARAM_INT, 'example evaluation of student'),
                'evalniveauid' => new external_value(PARAM_INT, 'evaluation niveau id'),
                'timestampteacher' => new external_value(PARAM_INT, 'timestamp of teacher evaluation'),
                'timestampstudent' => new external_value(PARAM_INT, 'timestamp of student evaluation'),
                'solution_visible' => new external_value(PARAM_BOOL, 'visibility for example solution in current context'),
                'exampletaxonomies' => new external_value(PARAM_TEXT, 'taxonomies seperated by comma', VALUE_OPTIONAL),
                'exampletaxids' => new external_value(PARAM_TEXT, 'taxids seperated by comma', VALUE_OPTIONAL),
                'examplecreatorid' => new external_value(PARAM_INT, 'id of the creator of this example'),
                'additionalinfo' => new external_value(PARAM_FLOAT, 'additional grading', VALUE_OPTIONAL),
                'resubmission' => new external_value(PARAM_BOOL, 'resubmission is allowed/not allowed', VALUE_OPTIONAL),
                'is_teacherexample' => new external_value(PARAM_BOOL, 'is a teacher example?', VALUE_OPTIONAL),
            ))),
            'examplestotal' => new external_value(PARAM_INT, 'total number of material'),
            'examplesvisible' => new external_value(PARAM_INT, 'visible number of material'),
            'examplesinwork' => new external_value(PARAM_INT, 'number of material in work'),
            'examplesedited' => new external_value(PARAM_INT, 'number of edited material'),
            'examplegradings' => new external_single_structure(array(
                'teacher' => new external_multiple_structure(new external_single_structure(array(
                    'evalniveauid' => new external_value(PARAM_INT, 'niveau id to according number', 0),
                    'value' => new external_value(PARAM_INT, 'grading value', 0),
                    'sum' => new external_value(PARAM_INT, 'number of gradings'),
                ))),
                'student' => new external_multiple_structure(new external_single_structure(array(
                    'sum' => new external_value(PARAM_INT, 'number of gradings'),
                ))),
            )),
            'visible' => new external_value(PARAM_INT, 'visibility of example in current context'),
            'used' => new external_value(PARAM_INT, 'used in current context'),
        )));
    }

    public static function dakora_get_descriptor_details_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'courseid'),
            'descriptorid' => new external_value(PARAM_INT, 'descriptorid'),
            'userid' => new external_value(PARAM_INT, 'userid'),
            'forall' => new external_value(PARAM_BOOL, 'forall'),
            'crosssubjid' => new external_value(PARAM_INT, 'crosssubjid'),
        ));
    }

    /**
     * get descriptor details incl. grading and children
     *
     * @ws-type-read
     * @param $courseid
     * @param $descriptorid
     * @param $userid
     * @param $forall
     * @param $crosssubjid
     * @return stdClass
     */
    public static function dakora_get_descriptor_details($courseid, $descriptorid, $userid, $forall, $crosssubjid) {
        global $DB, $USER, $CFG;
        static::validate_parameters(static::dakora_get_descriptor_details_parameters(),
            array('courseid' => $courseid, 'descriptorid' => $descriptorid, 'userid' => $userid, 'forall' => $forall, 'crosssubjid' => $crosssubjid));

        if (!$forall && $userid == 0) {
            $userid = $USER->id;
        }

        static::require_can_access_course_user($courseid, $userid);

        $descriptor_return = static::get_descriptor_details_private($courseid, $descriptorid, $userid, $forall, $crosssubjid);
        //$descriptor_return->activitylist = static::return_key_value(block_exacomp_list_possible_activities_for_example($courseid));

        return $descriptor_return;
    }

    public static function dakora_get_descriptor_details_returns() {
        return new external_single_structure(array(
            'reviewerid' => new external_value(PARAM_INT, 'id of reviewer'),
            'reviewername' => new external_value(PARAM_TEXT, 'name of reviewer'),
            'descriptorid' => new external_value(PARAM_INT, 'id of descriptor'),
            'parentid' => new external_value(PARAM_INT, 'id of parent of descriptor'),
            'descriptortitle' => new external_value(PARAM_TEXT, 'title of descriptor'),
            'teacherevaluation' => new external_value(PARAM_INT, 'teacher evaluation of descriptor'),
            'studentevaluation' => new external_value(PARAM_INT, 'student evaluation of descriptor'),
            'teacherevaluation' => new external_value(PARAM_INT, 'teacher evaluation of descriptor'),
            'additionalinfo' => new external_value(PARAM_FLOAT, 'additional grading for descriptor'),
            'timestampteacher' => new external_value(PARAM_INT, 'timestamp for teacher evaluation'),
            'timestampstudent' => new external_value(PARAM_INT, 'timestamp for student evaluation'),
            'evalniveauid' => new external_value(PARAM_INT, 'evaluation niveau id'),
            'numbering' => new external_value(PARAM_TEXT, 'numbering'),
            'categories' => new external_value(PARAM_TEXT, 'descriptor categories seperated by comma', VALUE_OPTIONAL),
            'niveauid' => new external_value(PARAM_INT, 'id of niveau'),
            'niveautitle' => new external_value(PARAM_TEXT, 'title of niveau'),
            'gradingisold' => new external_value(PARAM_BOOL, 'true when there are newer gradings in the childcompetences', false),
            'hasmaterial' => new external_value(PARAM_BOOL, 'true or false if descriptor has material'),
            'children' => new external_multiple_structure(new external_single_structure(array(
                'reviewerid' => new external_value(PARAM_INT, 'id of reviewer'),
                'reviewername' => new external_value(PARAM_TEXT, 'name of reviewer'),
                'descriptorid' => new external_value(PARAM_INT, 'id of descriptor'),
                'parentid' => new external_value(PARAM_INT, 'id of parent of descriptor'),
                'descriptortitle' => new external_value(PARAM_TEXT, 'title of descriptor'),
                'teacherevaluation' => new external_value(PARAM_INT, 'teacher evaluation of descriptor'),
                'additionalinfo' => new external_value(PARAM_FLOAT, 'additional grading for descriptor'),
                'evalniveauid' => new external_value(PARAM_INT, 'evaluation niveau id'),
                'timestampteacher' => new external_value(PARAM_INT, 'timestamp of teacher evaluation'),
                'studentevaluation' => new external_value(PARAM_INT, 'student evaluation of descriptor'),
                'timestampstudent' => new external_value(PARAM_INT, 'timestamp of student evaluation'),
                'numbering' => new external_value(PARAM_TEXT, 'numbering'),
                'hasmaterial' => new external_value(PARAM_BOOL, 'true or false if descriptor has material'),
                'globalgradings' => new external_value(PARAM_RAW, 'Globalgradings as text', VALUE_OPTIONAL),
                'gradinghistory' => new external_value(PARAM_RAW, 'Gradinghistory as text', VALUE_OPTIONAL),
                'examples' => new external_multiple_structure(new external_single_structure(array(
                    'exampleid' => new external_value(PARAM_INT, 'id of example'),
                    'exampletitle' => new external_value(PARAM_RAW, 'title of example'),
                    'examplestate' => new external_value(PARAM_INT, 'state of example, always 0 if for all students'),
                    'visible' => new external_value(PARAM_INT, 'visibility of example in current context'),
                    'used' => new external_value(PARAM_INT, 'used in current context'),
                    'teacherevaluation' => new external_value(PARAM_INT, 'example evaluation of teacher'),
                    'studentevaluation' => new external_value(PARAM_INT, 'example evaluation of student'),
                    'timestampteacher' => new external_value(PARAM_INT, 'timestamp of teacher evaluation'),
                    'timestampstudent' => new external_value(PARAM_INT, 'timestamp of student evaluation'),
                    'evalniveauid' => new external_value(PARAM_INT, 'evaluation niveau id'),
                    'solution_visible' => new external_value(PARAM_BOOL, 'visibility for example solution in current context'),
                    'exampletaxonomies' => new external_value(PARAM_TEXT, 'taxonomies seperated by comma', VALUE_OPTIONAL),
                    'exampletaxids' => new external_value(PARAM_TEXT, 'taxids seperated by comma', VALUE_OPTIONAL),
                    'examplecreatorid' => new external_value(PARAM_INT, 'id of the creator of this example'),
                    'additionalinfo' => new external_value(PARAM_FLOAT, 'additional grading', VALUE_OPTIONAL),
                    'resubmission' => new external_value(PARAM_BOOL, 'resubmission is allowed/not allowed', VALUE_OPTIONAL),
                    'is_teacherexample' => new external_value(PARAM_BOOL, 'is a teacher example?', VALUE_OPTIONAL),
                ))),
                'examplestotal' => new external_value(PARAM_INT, 'total number of material'),
                'examplesvisible' => new external_value(PARAM_INT, 'visible number of material'),
                'examplesinwork' => new external_value(PARAM_INT, 'number of material in work'),
                'visible' => new external_value(PARAM_INT, 'visibility of children in current context'),
                'used' => new external_value(PARAM_INT, 'used in current context'),
                'examplesedited' => new external_value(PARAM_INT, 'number of edited material'),
                'examplegradings' => new external_single_structure(array(
                    'teacher' => new external_multiple_structure(new external_single_structure(array(
                        'evalniveauid' => new external_value(PARAM_INT, 'niveau id to according number', 0),
                        'value' => new external_value(PARAM_INT, 'grading value', 0),
                        'sum' => new external_value(PARAM_INT, 'number of gradings'),
                    ))),
                    'student' => new external_multiple_structure(new external_single_structure(array(
                        'sum' => new external_value(PARAM_INT, 'number of gradings'),
                    ))),
                )),
            ))),
            'childrengradings' => new external_single_structure(array(
                'teacher' => new external_multiple_structure(new external_single_structure(array(
                    'evalniveauid' => new external_value(PARAM_INT, 'niveau id to according number', 0),
                    'value' => new external_value(PARAM_INT, 'grading value', 0),
                    'sum' => new external_value(PARAM_INT, 'number of gradings'),
                ))),
                'student' => new external_multiple_structure(new external_single_structure(array(
                    'sum' => new external_value(PARAM_INT, 'number of gradings'),
                ))),
            )),
            'examples' => new external_multiple_structure(new external_single_structure(array(
                'exampleid' => new external_value(PARAM_INT, 'id of example'),
                'exampletitle' => new external_value(PARAM_RAW, 'title of example'),
                'examplestate' => new external_value(PARAM_INT, 'state of example, always 0 if for all students'),
                'visible' => new external_value(PARAM_INT, 'visibility of example in current context'),
                'used' => new external_value(PARAM_INT, 'used in current context'),
                'teacherevaluation' => new external_value(PARAM_INT, 'example evaluation of teacher'),
                'studentevaluation' => new external_value(PARAM_INT, 'example evaluation of student'),
                'evalniveauid' => new external_value(PARAM_INT, 'evaluation niveau id'),
                'timestampteacher' => new external_value(PARAM_INT, 'timestamp of teacher evaluation'),
                'timestampstudent' => new external_value(PARAM_INT, 'timestamp of student evaluation'),
                'solution_visible' => new external_value(PARAM_BOOL, 'visibility for example solution in current context'),
                'exampletaxonomies' => new external_value(PARAM_TEXT, 'taxonomies seperated by comma', VALUE_OPTIONAL),
                'exampletaxids' => new external_value(PARAM_TEXT, 'taxids seperated by comma', VALUE_OPTIONAL),
                'examplecreatorid' => new external_value(PARAM_INT, 'id of the creator of this example'),
                'additionalinfo' => new external_value(PARAM_FLOAT, 'additional grading', VALUE_OPTIONAL),
                'resubmission' => new external_value(PARAM_BOOL, 'resubmission is allowed/not allowed', VALUE_OPTIONAL),
                'is_teacherexample' => new external_value(PARAM_BOOL, 'is a teacher example?', VALUE_OPTIONAL),
            ))),
            'examplestotal' => new external_value(PARAM_INT, 'total number of material'),
            'examplesvisible' => new external_value(PARAM_INT, 'visible number of material'),
            'examplesinwork' => new external_value(PARAM_INT, 'number of material in work'),
            'examplesedited' => new external_value(PARAM_INT, 'number of edited material'),
            'examplegradings' => new external_single_structure(array(
                'teacher' => new external_multiple_structure(new external_single_structure(array(
                    'evalniveauid' => new external_value(PARAM_INT, 'niveau id to according number', 0),
                    'value' => new external_value(PARAM_INT, 'grading value', 0),
                    'sum' => new external_value(PARAM_INT, 'number of gradings'),
                ))),
                'student' => new external_multiple_structure(new external_single_structure(array(
                    'sum' => new external_value(PARAM_INT, 'number of gradings'),
                ))),
            )),
            'visible' => new external_value(PARAM_INT, 'visibility of example in current context'),
            'used' => new external_value(PARAM_INT, 'used in current context'),
            'globalgradings' => new external_value(PARAM_RAW, 'Globalgradings as text', VALUE_OPTIONAL),
            'gradinghistory' => new external_value(PARAM_RAW, 'Gradinghistory as text', VALUE_OPTIONAL),
            //'activitylist' => static::key_value_returns(PARAM_INT, PARAM_TEXT, 'possible activities list. needed for new example form'),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function dakora_get_example_information_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'userid' => new external_value(PARAM_INT, 'id of user'),
            'exampleid' => new external_value(PARAM_INT, 'id of example'),
        ));
    }

    /**
     * get information and submission for example
     * get example with all submission details and gradings
     *
     * @ws-type-read
     * @return
     */
    public static function dakora_get_example_information($courseid, $userid, $exampleid) {
        global $USER;
        if ($userid == 0) {
            $userid = $USER->id;
        }

        static::validate_parameters(static::dakora_get_example_information_parameters(), array(
            'courseid' => $courseid,
            'userid' => $userid,
            'exampleid' => $exampleid,
        ));

        static::require_can_access_course_user($courseid, $userid);
        static::require_can_access_example($exampleid, $courseid);

        return static::_get_example_information($courseid, $userid, $exampleid);
    }

    /**
     * Returns description of method return values
     *
     * @return external_multiple_structure
     */
    public static function dakora_get_example_information_returns() {
        return new external_single_structure(array(
            'itemid' => new external_value(PARAM_INT, 'id of item'),
            'status' => new external_value(PARAM_INT, 'status of the submission (-1 == no submission; 0 == not graded; 1 == graded'),
            'name' => new external_value(PARAM_TEXT, 'title of item'),
            'type' => new external_value(PARAM_TEXT, 'type of item ENUM(note,file,link)'),
            'url' => new external_value(PARAM_TEXT, 'url'),
            'teachervalue' => new external_value(PARAM_INT, 'teacher grading'),
            'teacherevaluation' => new external_value(PARAM_INT, 'teacher grading (double of teachervalue?)'),
            'studentvalue' => new external_value(PARAM_INT, 'student grading'),
            'evalniveauid' => new external_value(PARAM_INT, 'evaluation niveau id'),
            'timestampteacher' => new external_value(PARAM_INT, 'timestamp for teacher evaluation'),
            'timestampstudent' => new external_value(PARAM_INT, 'timestamp for student evaluation'),
            'teachercomment' => new external_value(PARAM_TEXT, 'teacher comment'),
            'teacherfile' => new external_single_structure(array(
                'filename' => new external_value(PARAM_TEXT, 'title of item'),
                'file' => new external_value(PARAM_URL, 'file url'),
                'mimetype' => new external_value(PARAM_TEXT, 'mime type for file'),
                'fileindex' => new external_value(PARAM_TEXT, 'mime type for file'),
            ), '', VALUE_OPTIONAL),
            'studentcomment' => new external_value(PARAM_TEXT, 'student comment'),
            'teacheritemvalue' => new external_value(PARAM_INT, 'item teacher grading'),
            'resubmission' => new external_value(PARAM_BOOL, 'resubmission is allowed/not allowed'),
            'additionalinfo' => new external_value(PARAM_FLOAT, 'additional grading'),
            'studentfiles' => new external_multiple_structure(new external_single_structure(array(
                'filename' => new external_value(PARAM_TEXT, 'title of item'),
                'file' => new external_value(PARAM_URL, 'file url'),
                'mimetype' => new external_value(PARAM_TEXT, 'mime type for file'),
                'fileindex' => new external_value(PARAM_TEXT, 'mime type for file'),
            ))),
            'activityid' => new external_value(PARAM_INT, 'activityid'),
            'activitytitle' => new external_value(PARAM_TEXT, 'activity title', VALUE_OPTIONAL),
            'activitytype' => new external_value(PARAM_TEXT, 'activity type - key for activity icons in Dakora', VALUE_OPTIONAL),
        ));
    }

    protected static function _get_example_information($courseid, $userid, $exampleid) {
        global $CFG, $DB;
        $example = $DB->get_record(BLOCK_EXACOMP_DB_EXAMPLES, array('id' => $exampleid));
        if (!$example) {
            throw new invalid_parameter_exception ('Example does not exist');
        }
        $itemInformation = block_exacomp_get_current_item_for_example($userid, $exampleid);
        $exampleEvaluation = $DB->get_record(BLOCK_EXACOMP_DB_EXAMPLEEVAL, array("studentid" => $userid, "courseid" => $courseid, "exampleid" => $exampleid));
        //throw new invalid_parameter_exception (print_r($exampleEvaluation, true));
        $data = array();
        $filedata = array();
        $studentfiles = array();
        if ($itemInformation) {
            //item exists
            $data['itemid'] = $itemInformation->id;
            $data['file'] = "";
            $data['isimage'] = false;
            $data['filename'] = "";
            $data['mimetype'] = "";
            $data['teachervalue'] = isset ($exampleEvaluation->teacher_evaluation) ? $exampleEvaluation->teacher_evaluation : -1;
            $data['teacherevaluation'] = $data['teachervalue'];
            $data['studentvalue'] = isset ($exampleEvaluation->student_evaluation) ? $exampleEvaluation->student_evaluation : -1;
            $data['evalniveauid'] = isset ($exampleEvaluation->evalniveauid) ? $exampleEvaluation->evalniveauid : null;
            $data['timestampteacher'] = isset ($exampleEvaluation->timestamp_teacher) ? $exampleEvaluation->timestamp_teacher : 0;
            $data['timestampstudent'] = isset ($exampleEvaluation->timestamp_student) ? $exampleEvaluation->timestamp_student : 0;
            $data['status'] = isset ($itemInformation->status) ? $itemInformation->status : -1;
            $data['name'] = $itemInformation->name;
            $data['type'] = $itemInformation->type;
            $data['url'] = $itemInformation->url;
            $data['teacheritemvalue'] = isset ($itemInformation->teachervalue) ? $itemInformation->teachervalue : -1;
            //$data['additionalinfo'] = isset ($itemInformation->additionalinfo) ? $itemInformation->additionalinfo : -1;
            $data['additionalinfo'] = isset ($exampleEvaluation->additionalinfo) ? $exampleEvaluation->additionalinfo : -1;
            $data['studentfiles'] = $studentfiles;

            require_once $CFG->dirroot . '/blocks/exaport/inc.php';
            if ($files = block_exaport_get_item_files($itemInformation)) {
                /*
	             * $fileurl = (string)new moodle_url("/blocks/exaport/portfoliofile.php", [
	             * 'userid' => $userid,
	             * 'itemid' => $itemInformation->id,
	             * 'wstoken' => static::wstoken(),
	             * ]);
	             */
                // TODO: moodle_url contains encoding errors which lead to problems in dakora
                foreach ($files as $fileindex => $file) {
                    if ($file != null) {
                        $fileurl = $CFG->wwwroot . "/blocks/exaport/portfoliofile.php?" . "userid=" . $userid . "&itemid=" . $itemInformation->id . "&wstoken=" . static::wstoken();
                        $filedata['file'] = $fileurl;
                        $filedata['mimetype'] = $file->get_mimetype();
                        $filedata['filename'] = $file->get_filename();
                        $filedata['fileindex'] = $fileindex;
                        $studentfiles[] = $filedata;
                    }
                }
                $data['studentfiles'] = $studentfiles;
            }
            $data['studentcomment'] = '';
            $data['teachercomment'] = '';
            //$data['teacherfile'] = [];
            $itemcomments = api::get_item_comments($itemInformation->id);
            $timemodified_compare = 0; //used for finding the most recent comment to display it in Dakora
            $timemodified_compareTeacher = 0;
            foreach ($itemcomments as $itemcomment) {
                if ($userid == $itemcomment->userid) {
                    if ($itemcomment->timemodified > $timemodified_compare) {
                        $data['studentcomment'] = $itemcomment->entry;
                        $timemodified_compare = $itemcomment->timemodified;
                    }
                } else if (true) { // TODO: check if is teacher?
                    if ($itemcomment->timemodified > $timemodified_compareTeacher) {
                        $data['teachercomment'] = $itemcomment->entry;
                        if ($itemcomment->file) { //the most recent file is being kept, so if there is a newer comment without a file, the last file is still shown
                            $tFile = $itemcomment->file;
                            $fileurl = (string)new moodle_url("/blocks/exaport/portfoliofile.php", [
                                'userid' => $userid,
                                'itemid' => $itemInformation->id,
                                'commentid' => $itemcomment->id,
                                'wstoken' => static::wstoken(),
                            ]);
                            $teacherfile = [
                                'file' => $fileurl,
                                'mimetype' => $tFile->get_mimetype(),
                                'filename' => $tFile->get_filename(),
                                'fileindex' => $tFile->get_contenthash(),
                            ];
                            $data['teacherfile'] = $teacherfile;
                        }
                        $timemodified_compareTeacher = $itemcomment->timemodified;
                    }

                }
            }
        } else {
            //no item and therefore no submission exists
            $data['itemid'] = 0;
            $data['status'] = -1;
            $data['name'] = "";
            $data['file'] = "";
            $data['filename'] = "";
            $data['url'] = "";
            $data['type'] = "";
            $data['mimetype'] = "";
            $data['teachercomment'] = "";
            $data['studentcomment'] = "";
            //$data['teacherfile'] = [];
            $data['teachervalue'] = isset ($exampleEvaluation->teacher_evaluation) ? $exampleEvaluation->teacher_evaluation : -1;
            $data['teacherevaluation'] = $data['teachervalue'];
            $data['studentvalue'] = isset ($exampleEvaluation->student_evaluation) ? $exampleEvaluation->student_evaluation : -1;
            $data['evalniveauid'] = isset ($exampleEvaluation->evalniveauid) ? $exampleEvaluation->evalniveauid : null;
            $data['timestampteacher'] = isset ($exampleEvaluation->timestamp_teacher) ? $exampleEvaluation->timestamp_teacher : 0;
            $data['timestampstudent'] = isset ($exampleEvaluation->timestamp_student) ? $exampleEvaluation->timestamp_student : 0;
            $data['teacheritemvalue'] = isset ($itemInformation->teachervalue) ? $itemInformation->teachervalue : -1;
            $data['additionalinfo'] = isset ($exampleEvaluation->additionalinfo) ? $exampleEvaluation->additionalinfo : -1;
            $data['filecount'] = 0;
            $data['studentfiles'] = $studentfiles;
        }
        if (!$exampleEvaluation || $exampleEvaluation->resubmission) {
            $data['resubmission'] = true;
        } else {
            $data['resubmission'] = false;
        }
        // add activity data
        $data['activityid'] = ($example && @$example->activityid ? $example->activityid : 0);
        $data['activitytitle'] = ($example && @$example->activitytitle ? $example->activitytitle : '');
        $modname = null;
        if ($example->activityid) {
            if ($module = get_coursemodule_from_id(null, $example->activityid)) {
                // get type of activity
                $mod_info = get_fast_modinfo($courseid);
                if (array_key_exists($module->id, $mod_info->cms)) {
                    $cm = $mod_info->cms[$module->id];
                    $modname = $cm->modname;
                    if (!$data['activitytitle'] && $cm->name) {
                        $data['activitytitle'] = $cm->name;
                    }
                }
            }
        }
        $data['activitytype'] = $modname;

        return $data;
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function dakora_get_user_information_parameters() {
        return new external_function_parameters(array());
    }

    /**
     * get information about current user
     * get example with all submission details and gradings
     *
     * @ws-type-read
     * @return
     */
    public static function dakora_get_user_information() {
        global $CFG, $USER;
        require_once($CFG->dirroot . "/user/lib.php");

        $data = user_get_user_details_courses($USER);
        $data['exarole'] = static::dakora_get_user_role()->role;
        unset($data['enrolledcourses']);
        unset($data['preferences']);

        return $data;
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function dakora_get_user_information_returns() {
        return new external_single_structure(array(
            'id' => new external_value(PARAM_INT, 'ID of the user'),
            'username' => new external_value(PARAM_RAW, 'The username', VALUE_OPTIONAL),
            'firstname' => new external_value(PARAM_NOTAGS, 'The first name(s) of the user', VALUE_OPTIONAL),
            'lastname' => new external_value(PARAM_NOTAGS, 'The family name of the user', VALUE_OPTIONAL),
            'fullname' => new external_value(PARAM_NOTAGS, 'The fullname of the user'),
            'email' => new external_value(PARAM_TEXT, 'An email address - allow email as root@localhost', VALUE_OPTIONAL),
            'firstaccess' => new external_value(PARAM_INT, 'first access to the site (0 if never)', VALUE_OPTIONAL),
            'lastaccess' => new external_value(PARAM_INT, 'last access to the site (0 if never)', VALUE_OPTIONAL),
            'auth' => new external_value(PARAM_PLUGIN, 'Auth plugins include manual, ldap, imap, etc', VALUE_OPTIONAL),
            'confirmed' => new external_value(PARAM_INT, 'Active user: 1 if confirmed, 0 otherwise', VALUE_OPTIONAL),
            'lang' => new external_value(PARAM_SAFEDIR, 'Language code such as "en", must exist on server', VALUE_OPTIONAL),
            'url' => new external_value(PARAM_URL, 'URL of the user', VALUE_OPTIONAL),
            'profileimageurlsmall' => new external_value(PARAM_URL, 'User image profile URL - small version'),
            'profileimageurl' => new external_value(PARAM_URL, 'User image profile URL - big version'),
            'exarole' => new external_value(PARAM_INT, '1=trainer, 2=student'),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function dakora_get_lang_information_parameters() {
        return new external_function_parameters(array(
            'lang' => new external_value(PARAM_TEXT, 'language'),
        ));
    }

    /**
     * Returns lang information from exacomp
     *
     * @ws-type-read
     * @return
     */
    public static function dakora_get_lang_information($lang) {
        global $DB;

        static::validate_parameters(static::dakora_get_lang_information_parameters(), array(
            'lang' => $lang,
        ));

        $output = $DB->get_records_sql('SELECT strings.stringid, strings.master
                    FROM {tool_customlang} strings
                    JOIN {tool_customlang_components} components ON components.id = strings.componentid
                    WHERE components.name = "block_exacomp"
                        AND strings.lang = ?
			            AND strings.stringid LIKE "dakora_%"
			  ', array($lang));
        return $output;

    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function dakora_get_lang_information_returns() {
        return new external_multiple_structure(new external_single_structure(array(
                'stringid' => new external_value(PARAM_TEXT, 'key for the lang string', VALUE_REQUIRED),
                'master' => new external_value(PARAM_TEXT, 'lang string in the chosen language', VALUE_REQUIRED),
            )
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function dakora_create_blocking_event_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'title' => new external_value(PARAM_TEXT, 'title of new blocking event'),
            'description' => new external_value(PARAM_TEXT, 'description of new blocking event'),
            'timeframe' => new external_value(PARAM_TEXT, 'timeframe'),
            'externalurl' => new external_value(PARAM_URL, 'external url'),
            'userid' => new external_value(PARAM_INT, 'id of user'),
            'preplanningstorage' => new external_value(PARAM_BOOL, 'in pre planning storage or for specific student'),
        ));
    }

    /**
     * create a blocking event
     * Create a new blocking event
     *
     * @ws-type-write
     */
    public static function dakora_create_blocking_event($courseid, $title, $description, $timeframe, $externalurl, $userid, $preplanningstorage) {
        global $USER;

        static::validate_parameters(static::dakora_create_blocking_event_parameters(), array(
            'courseid' => $courseid,
            'title' => $title,
            'description' => $description,
            'timeframe' => $timeframe,
            'externalurl' => $externalurl,
            'userid' => $userid,
            'preplanningstorage' => $preplanningstorage));

        if ($userid == 0 && !$preplanningstorage && !block_exacomp_is_teacher($courseid)) {
            $userid = $USER->id;
        }

        static::require_can_access_course_user($courseid, $userid);

        $scheduleid = block_exacomp_create_blocking_event($courseid, $title, $description, $timeframe, $externalurl, $USER->id, $userid);

        return array("scheduleid" => $scheduleid);
    }

    /**
     * Returns desription of method return values
     *
     * @return external_single_structure
     */
    public static function dakora_create_blocking_event_returns() {
        return new external_single_structure(array(
            'scheduleid' => new external_value(PARAM_INT, 'scheduleid'),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function dakora_get_examples_by_descriptor_and_grading_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'userid' => new external_value(PARAM_INT, 'id of user'),
            'descriptorid' => new external_value(PARAM_TEXT, 'id of descriptor'),
            'grading' => new external_value(PARAM_INT, 'grading value'),
        ));
    }

    /**
     * returns examples for given descriptor and grading
     * Create a new blocking event
     *
     * @ws-type-read
     */
    public static function dakora_get_examples_by_descriptor_and_grading($courseid, $userid, $descriptorid, $grading) {
        global $USER;

        static::validate_parameters(static::dakora_get_examples_by_descriptor_and_grading_parameters(), array('courseid' => $courseid,
            'userid' => $userid, 'descriptorid' => $descriptorid, 'grading' => $grading));

        if ($userid == 0) {
            $userid = $USER->id;
        }

        static::require_can_access_course_user($courseid, $userid);

        $grading = $grading - 1;

        $childsandexamples = static::get_descriptor_children($courseid, $descriptorid, $userid, 0);

        $examples_return = array();

        //parent descriptor
        $examples = static::dakora_get_examples_for_descriptor_with_grading($courseid, $descriptorid, $userid, false);

        foreach ($examples as $example) {
            $example->title = static::custom_htmltrim(strip_tags($example->title));
            if ($example->teacherevaluation == $grading) {
                if (!array_key_exists($example->exampleid, $examples_return)) {
                    $examples_return[$example->exampleid] = $example;
                }
            }
        }

        foreach ($childsandexamples->children as $child) {
            $examples = static::dakora_get_examples_for_descriptor_with_grading($courseid, $child->descriptorid, $userid, false);

            foreach ($examples as $example) {
                $example->title = static::custom_htmltrim(strip_tags($example->title));
                if ($example->teacherevaluation == $grading && $example->visible == 1) {
                    if (!array_key_exists($example->exampleid, $examples_return)) {
                        $examples_return[$example->exampleid] = $example;
                    }
                }
            }
        }

        return $examples_return;
    }

    /**
     * Returns desription of method return values
     *
     * @return external_single_structure
     */
    public static function dakora_get_examples_by_descriptor_and_grading_returns() {
        return new external_multiple_structure(new external_single_structure(array(
            'exampleid' => new external_value(PARAM_INT, 'id of topic'),
            'exampletitle' => new external_value(PARAM_TEXT, 'title of topic'),
        )));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function dakora_allow_example_resubmission_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'userid' => new external_value(PARAM_INT, 'id of user'),
            'exampleid' => new external_value(PARAM_INT, 'id of example'),
        ));
    }

    /**
     * allow student to resubmit example
     * Create a new blocking event
     *
     * @ws-type-read
     */
    public static function dakora_allow_example_resubmission($courseid, $userid, $exampleid) {
        global $USER;

        static::validate_parameters(static::dakora_allow_example_resubmission_parameters(), array('courseid' => $courseid,
            'userid' => $userid, 'exampleid' => $exampleid));

        static::require_can_access_course_user($courseid, $userid);

        block_exacomp_allow_resubmission($userid, $exampleid, $courseid);

        return array('success' => true);

    }

    /**
     * Returns desription of method return values
     *
     * @return external_single_structure
     */
    public static function dakora_allow_example_resubmission_returns() {
        return new external_single_structure(array(
            'success' => new external_value(PARAM_BOOL, 'status of success, either true (1) or false (0)'),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function dakora_get_competence_grid_for_profile_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'userid' => new external_value(PARAM_INT, 'id of user'),
            'subjectid' => new external_value(PARAM_INT, 'id of subject', VALUE_DEFAULT, -1),
            'crosssubjid' => new external_value(PARAM_INT, 'id of crosssubject', VALUE_DEFAULT, -1),
        ));
    }

    /**
     * get grid for profile
     *Get competence grid for profile
     *
     * @ws-type-read
     */
    public static function dakora_get_competence_grid_for_profile($courseid, $userid, $subjectid = -1, $crosssubjid = -1) {
        global $USER;

        static::validate_parameters(static::dakora_get_competence_grid_for_profile_parameters(), array('courseid' => $courseid,
            'userid' => $userid, 'subjectid' => $subjectid, 'crosssubjid' => $crosssubjid));

        if ($userid == 0) {
            $userid = $USER->id;
        }

        static::require_can_access_course_user($courseid, $userid);
        //$subjects = block_exacomp_get_subjects_by_course($courseid);

        if ($subjectid != -1) {
            $subjectinfo = array(
                'teacher' => array(
                    'gridgradings' => array(block_exacomp_get_competence_profile_grid_for_ws($courseid, $userid, $subjectid, BLOCK_EXACOMP_ROLE_TEACHER)),
                ),
                'student' => array(
                    'gridgradings' => array(block_exacomp_get_competence_profile_grid_for_ws($courseid, $userid, $subjectid, BLOCK_EXACOMP_ROLE_STUDENT)),
                ),
                'globalcompetences' => array(),
            );
        } else if ($crosssubjid != -1) {
            $subjectinfo = array(
                'teacher' => array(
                    'crosssubjgrading' => array(),
                    'gridgradings' => array(),
                ),
                'student' => array(
                    'crosssubjgrading' => array(),
                    'gridgradings' => array(),
                ),
                'globalcompetences' => array(),
            );

            $crosssubject = block_exacomp_get_crosssubject_by_id($crosssubjid);
            $subjects = block_exacomp_get_subjects_for_cross_subject($crosssubjid);
            $crosssubj_teachergrading = block_exacomp_get_comp_eval($crosssubject->courseid, BLOCK_EXACOMP_ROLE_TEACHER, $userid, BLOCK_EXACOMP_TYPE_CROSSSUB, $crosssubject->id);
            $crosssubj_studentgrading = block_exacomp_get_comp_eval($crosssubject->courseid, BLOCK_EXACOMP_ROLE_STUDENT, $userid, BLOCK_EXACOMP_TYPE_CROSSSUB, $crosssubject->id);

            if ($crosssubj_teachergrading) {
                $subjectinfo['teacher']['crosssubjgrading'] = $crosssubj_teachergrading;
            }
            if ($crosssubj_studentgrading) {
                $subjectinfo['student']['crosssubjgrading'] = $crosssubj_studentgrading;
            }

            foreach ($subjects as $id => $subj) {
                $subjectinfo['teacher']['gridgradings'][] = block_exacomp_get_competence_profile_grid_for_ws($courseid, $userid, $subj->id, BLOCK_EXACOMP_ROLE_TEACHER, null, $crosssubject);
                $subjectinfo['student']['gridgradings'][] = block_exacomp_get_competence_profile_grid_for_ws($courseid, $userid, $subj->id, BLOCK_EXACOMP_ROLE_STUDENT, null, $crosssubject);
            }
        }

        // global values
        $possible_courses = block_exacomp_get_exacomp_courses($userid);
        $user_courses = array();
        foreach ($possible_courses as $course) {
            $user_courses[$course->id] = $course;
        }
        // go across courses and subjects to get all statistic
        foreach ($user_courses as $cid => $course) {
            $competence_tree = block_exacomp_get_competence_tree($cid, null, null, false, null, true,
                array(BLOCK_EXACOMP_SHOW_ALL_TAXONOMIES), false, false, false, false, false, false);
            foreach ($competence_tree as $subject) {
                $tmp1 = block_exacomp_get_competence_profile_grid_for_ws($cid, $userid, $subject->id, BLOCK_EXACOMP_ROLE_TEACHER);
            }
        }
        $globalTableData = block_exacomp_get_competence_profile_grid_for_ws(null, $userid, null, BLOCK_EXACOMP_ROLE_TEACHER);
        $newSubjectData = block_exacomp_new_subject_data_for_competence_profile($globalTableData, $courseid);
        if (count($newSubjectData)) {
            foreach ($newSubjectData as $sId => $subjectData) {
                $subjectinfo['globalcompetences'][] = block_exacomp_get_competence_profile_grid_for_ws(
                    null,
                    $userid,
                    $sId,
                    BLOCK_EXACOMP_ROLE_TEACHER,
                    array($globalTableData[$sId]['table_rows'],
                        $globalTableData[$sId]['table_header'],
                        $subjectData,
                    ));
            }
        }

        // for testing in old app
        //$subjectinfo = block_exacomp_get_competence_profile_grid_for_ws($courseid, $userid, $subjectid, BLOCK_EXACOMP_ROLE_STUDENT);
        //      var_dump($subjectinfo['teacher'][0]->rows[1]);
        //        var_dump($subjectinfo);
        //        die;

        return $subjectinfo;
    }

    /**
     * Returns desription of method return values
     *
     * @return external_single_structure
     */
    public static function dakora_get_competence_grid_for_profile_returns() {
        $table_structure = array(
            'title' => new external_value(PARAM_TEXT, 'title of table', VALUE_DEFAULT, ""),
            'rows' => new external_multiple_structure(new external_single_structure(array(
                'columns' => new external_multiple_structure(new external_single_structure(array(
                    'text' => new external_value(PARAM_TEXT, 'cell text', VALUE_DEFAULT, ""),
                    'evaluation' => new external_value(PARAM_FLOAT, 'evaluation', VALUE_DEFAULT, -1),
                    //'evaluation' => new external_value(PARAM_TEXT, 'evaluation', VALUE_DEFAULT, '-1'),
                    'evaluation_text' => new external_value(PARAM_TEXT, 'evaluation text', VALUE_DEFAULT, ""),
                    'evaluation_mapped' => new external_value(PARAM_INT, 'mapped evaluation', VALUE_DEFAULT, -1),
                    'evalniveauid' => new external_value(PARAM_INT, 'evaluation niveau id', VALUE_DEFAULT, 0),
                    'show' => new external_value(PARAM_BOOL, 'show cell', VALUE_DEFAULT, true),
                    'visible' => new external_value(PARAM_BOOL, 'cell visibility', VALUE_DEFAULT, true),
                    'topicid' => new external_value(PARAM_INT, 'topic id', VALUE_DEFAULT, 0),
                    'span' => new external_value(PARAM_INT, 'colspan'),
                    'timestamp' => new external_value(PARAM_INT, 'evaluation timestamp, 0 if not set', VALUE_DEFAULT, 0),
                    'gradingisold' => new external_value(PARAM_BOOL, 'true when there are childdescriptors with newer gradings than the parentdescriptor', false),
                ))),
            ))),
        );
        return new external_single_structure(array(
                'teacher' => new external_single_structure(array(
                    'crosssubjgrading' => new external_single_structure(array(
                        'value' => new external_value(PARAM_FLOAT, 'evaluation', VALUE_OPTIONAL),
                        'additionalinfo' => new external_value(PARAM_FLOAT, 'evaluation', VALUE_OPTIONAL),
                        'evalniveauid' => new external_value(PARAM_FLOAT, 'evaluation', VALUE_OPTIONAL),
                    ), null, VALUE_OPTIONAL),
                    'gridgradings' => new external_multiple_structure(new external_single_structure($table_structure)),
                )),
                'student' => new external_single_structure(array(
                    'crosssubjgrading' => new external_single_structure(array(
                        'value' => new external_value(PARAM_FLOAT, 'evaluation', VALUE_OPTIONAL),
                        'additionalinfo' => new external_value(PARAM_FLOAT, 'evaluation', VALUE_OPTIONAL),
                        'evalniveauid' => new external_value(PARAM_FLOAT, 'evaluation', VALUE_OPTIONAL),
                    ), null, VALUE_OPTIONAL),
                    'gridgradings' => new external_multiple_structure(new external_single_structure($table_structure)),
                )),
                'globalcompetences' => new external_multiple_structure(new external_single_structure($table_structure), '', VALUE_DEFAULT, array()),
            )
        );
        // for testing in old app
        //return new external_single_structure($table_structure);
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function dakora_get_competence_profile_statistic_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'userid' => new external_value(PARAM_INT, 'id of user'),
            'subjectid' => new external_value(PARAM_INT, 'id of subject'),
            'start_timestamp' => new external_value(PARAM_INT, 'start timestamp for evaluation range'),
            'end_timestamp' => new external_value(PARAM_INT, 'end timestamp for evaluation range'),
        ));
    }

    /**
     * get statistic in user and subject context
     *Get competence statistic for profile
     *
     * @ws-type-read
     */
    public static function dakora_get_competence_profile_statistic($courseid, $userid, $subjectid, $start_timestamp, $end_timestamp) {
        global $USER;

        static::validate_parameters(static::dakora_get_competence_profile_statistic_parameters(), array('courseid' => $courseid,
            'userid' => $userid, 'subjectid' => $subjectid, 'start_timestamp' => $start_timestamp, 'end_timestamp' => $end_timestamp));

        if ($userid == 0) {
            $userid = $USER->id;
        }

        static::require_can_access_course_user($courseid, $userid);

        $statistics = block_exacomp_get_evaluation_statistic_for_subject($courseid, $subjectid, $userid, $start_timestamp, $end_timestamp, true);

        $statistics_return = array();
        foreach ($statistics as $key => $statistic) {
            $return = array();
            foreach ($statistic as $niveauid => $niveaustat) {
                //if niveaus are used, send all gradings with niveaus and all without(niveauid -1)
                //if niveause are NOT used, return only the gradings without niveaus (with niveauid -1)

                //                if(block_exacomp_get_assessment_comp_diffLevel()==0) { //if no niveaus are allowed but because of the old settings a niveau has been set for this competence: act like there is no niveau
                //                    $niveauid = -1;
                //                }
                $niveau = new stdClass();
                $niveau->id = (int)$niveauid; // quick bugfix: when "points" is set in the plugin settings, the last niveaus is "".. this would lead to an error since int is expected
                $evaluations = array();
                foreach ($niveaustat as $evalvalue => $sum) {
                    $eval = new stdClass();
                    if (!($evalvalue === "")) { //when the grading has existed but is reset to none, there is "" saved... DONT include these
                        switch (block_exacomp_get_assessment_comp_scheme($courseid)) {
                            case BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE:
                                $eval->value = round($evalvalue, 0, PHP_ROUND_HALF_DOWN);
                                break;
                            default:
                                $eval->value = round($evalvalue);
                        }
                        //$eval->value = $evalvalue;
                        $eval->sum = $sum;
                        $evaluations[] = $eval;
                    }

                }
                $niveau->evaluations = $evaluations;

                $return[$niveauid] = $niveau;
            }
            $statistics_return[$key]["niveaus"] = $return;
            //throw new invalid_parameter_exception (print_r($return));
        }

        $statistics_return['descriptor_evaluations']['descriptorsToGain'] = $statistics["descriptorsToGain"];
        return $statistics_return;
    }

    /**
     * Returns desription of method return values
     *
     * @return external_single_structure
     */
    // 	public static function dakora_get_competence_profile_statistic_returns() {
    // 	    return new external_single_structure(array(
    //             'niveaus' => new external_multiple_structure(new external_single_structure(array(
    //                 'id' => new external_value(PARAM_INT, 'evalniveauid'),
    //                 'evaluations' => new external_multiple_structure(new external_single_structure(array(
    //                     'value' => new external_value(PARAM_INT, 'value of evaluation'),
    //                     'sum' => new external_value(PARAM_INT, 'sum of evaluations of current gradings'),
    //                 ))),
    //             ))),
    // 	    ));
    // 	}
    public static function dakora_get_competence_profile_statistic_returns() {
        return new external_single_structure(array(
            'descriptor_evaluations' => new external_single_structure(array(
                'niveaus' => new external_multiple_structure(new external_single_structure(array(
                    'id' => new external_value(PARAM_INT, 'evalniveauid'),
                    'evaluations' => new external_multiple_structure(new external_single_structure(array(
                        'value' => new external_value(PARAM_INT, 'value of evaluation'),
                        'sum' => new external_value(PARAM_INT, 'sum of evaluations of current gradings'),
                    ))),
                ))),
                'descriptorsToGain' => new external_value(PARAM_INT, 'maximum number of descripotrs/competencies one can gain'),
            )),
            // 			'child_evaluations' => new external_single_structure(array(
            // 				'niveaus' => new external_multiple_structure(new external_single_structure(array(
            // 					'id' => new external_value(PARAM_INT, 'evalniveauid'),
            // 					'evaluations' => new external_multiple_structure(new external_single_structure(array(
            // 						'value' => new external_value(PARAM_INT, 'value of evaluation'),
            // 						'sum' => new external_value(PARAM_INT, 'sum of evaluations of current gradings'),
            // 					))),
            // 				))),
            // 			)),
            // 			'example_evaluations' => new external_single_structure(array(
            // 				'niveaus' => new external_multiple_structure(new external_single_structure(array(
            // 					'id' => new external_value(PARAM_INT, 'evalniveauid'),
            // 					'evaluations' => new external_multiple_structure(new external_single_structure(array(
            // 						'value' => new external_value(PARAM_INT, 'value of evaluation'),
            // 						'sum' => new external_value(PARAM_INT, 'sum of evaluations of current gradings'),
            // 					))),
            // 				))),
            // 			)),
        ));
    }



    // 	/**
    // 	 * Returns description of method parameters
    // 	 *
    // 	 * @return external_function_parameters
    // 	 */
    // 	public static function dakora_get_competence_profile_statistic_parameters() {
    // 		return new external_function_parameters(array(
    // 			'courseid' => new external_value(PARAM_INT, 'id of course'),
    // 			'userid' => new external_value(PARAM_INT, 'id of user'),
    // 			'subjectid' => new external_value(PARAM_INT, 'id of subject'),
    // 			'start_timestamp' => new external_value(PARAM_INT, 'start timestamp for evaluation range'),
    // 			'end_timestamp' => new external_value(PARAM_INT, 'end timestamp for evaluation range'),
    // 		));
    // 	}

    // 	/**
    // 	 * get statistic in user and subject context
    // 	 *Get competence statistic for profile
    // 	 *
    // 	 * @ws-type-read
    // 	 */
    // 	public static function dakora_get_competence_profile_statistic($courseid, $userid, $subjectid, $start_timestamp, $end_timestamp) {
    // 		global $USER;

    // 		static::validate_parameters(static::dakora_get_competence_profile_statistic_parameters(), array('courseid' => $courseid,
    // 			'userid' => $userid, 'subjectid' => $subjectid, 'start_timestamp' => $start_timestamp, 'end_timestamp' => $end_timestamp));

    // 		if ($userid == 0) {
    // 			$userid = $USER->id;
    // 		}

    // 		static::require_can_access_course_user($courseid, $userid);

    // 		$statistics = block_exacomp_get_evaluation_statistic_for_subject($courseid, $subjectid, $userid, $start_timestamp, $end_timestamp);

    // 		$statistics_return = array();

    // 		foreach ($statistics as $key => $statistic) {
    // 			$return = array();
    // 			foreach ($statistic as $niveauid => $niveaustat) {
    // 				$niveau = new stdClass();
    // 				$niveau->id = $niveauid;

    // 				$evaluations = array();
    // 				foreach ($niveaustat as $evalvalue => $sum) {
    // 					$eval = new stdClass();
    // 					$eval->value = $evalvalue;
    // 					$eval->sum = $sum;
    // 					$evaluations[] = $eval;
    // 				}
    // 				$niveau->evaluations = $evaluations;

    // 				$return[] = $niveau;
    // 			}
    // 			$statistics_return[$key]["niveaus"] = $return;
    // 		}

    // 		return $statistics_return;
    // 	}

    // 	/**
    // 	 * Returns desription of method return values
    // 	 *
    // 	 * @return external_single_structure
    // 	 */
    // 	public static function dakora_get_competence_profile_statistic_returns() {
    // 		return new external_single_structure(array(
    // 			'descriptor_evaluations' => new external_single_structure(array(
    // 				'niveaus' => new external_multiple_structure(new external_single_structure(array(
    // 					'id' => new external_value(PARAM_INT, 'evalniveauid'),
    // 					'evaluations' => new external_multiple_structure(new external_single_structure(array(
    // 						'value' => new external_value(PARAM_INT, 'value of evaluation'),
    // 						'sum' => new external_value(PARAM_INT, 'sum of evaluations of current gradings'),
    // 					))),
    // 				))),
    // 			)),
    // 			'child_evaluations' => new external_single_structure(array(
    // 				'niveaus' => new external_multiple_structure(new external_single_structure(array(
    // 					'id' => new external_value(PARAM_INT, 'evalniveauid'),
    // 					'evaluations' => new external_multiple_structure(new external_single_structure(array(
    // 						'value' => new external_value(PARAM_INT, 'value of evaluation'),
    // 						'sum' => new external_value(PARAM_INT, 'sum of evaluations of current gradings'),
    // 					))),
    // 				))),
    // 			)),
    // 			'example_evaluations' => new external_single_structure(array(
    // 				'niveaus' => new external_multiple_structure(new external_single_structure(array(
    // 					'id' => new external_value(PARAM_INT, 'evalniveauid'),
    // 					'evaluations' => new external_multiple_structure(new external_single_structure(array(
    // 						'value' => new external_value(PARAM_INT, 'value of evaluation'),
    // 						'sum' => new external_value(PARAM_INT, 'sum of evaluations of current gradings'),
    // 					))),
    // 				))),
    // 			)),
    // 		));
    // 	}

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function dakora_get_competence_profile_comparison_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'userid' => new external_value(PARAM_INT, 'id of user'),
            'topicid' => new external_value(PARAM_INT, 'id of subject'),
        ));
    }

    /**
     * get list for student and teacher comparison
     *Get competence comparison for profile
     *
     * @ws-type-read
     */
    public static function dakora_get_competence_profile_comparison($courseid, $userid, $topicid) {
        global $USER;

        static::validate_parameters(static::dakora_get_competence_profile_comparison_parameters(), array('courseid' => $courseid,
            'userid' => $userid, 'topicid' => $topicid));

        if ($userid == 0) {
            $userid = $USER->id;
        }

        static::require_can_access_course_user($courseid, $userid);

        $students = block_exacomp_get_students_by_course($courseid);
        $student = $students[$userid];

        $student = block_exacomp_get_user_information_by_course($student, $courseid);
        $descriptors = block_exacomp_get_descriptors_by_topic($courseid, $topicid);

        $comparison = new stdClass();
        $comparison->descriptors = array();

        $use_evalniveau = block_exacomp_use_eval_niveau($courseid);

        foreach ($descriptors as $descriptor) {
            $descriptor->numbering = block_exacomp_get_descriptor_numbering($descriptor);
            $descriptor->descriptorid = $descriptor->id;
            $descriptor->teacherevaluation = ((isset($student->competencies->teacher[$descriptor->id])) ? $student->competencies->teacher[$descriptor->id] : -1);
            $descriptor->additionalinfo = ((isset($student->competencies->teacher_additional_grading[$descriptor->id])) ? $student->competencies->teacher_additional_grading[$descriptor->id] : -1);
            $descriptor->evalniveauid = ($use_evalniveau) ? ((isset($student->competencies->niveau[$descriptor->id])) ? $student->competencies->niveau[$descriptor->id] : -1) : 0;
            $descriptor->timestampteacher = ((isset($student->competencies->timestamp_teacher[$descriptor->id])) ? $student->competencies->timestamp_teacher[$descriptor->id] : 0);
            $descriptor->studentevaluation = ((isset($student->competencies->student[$descriptor->id])) ? $student->competencies->student[$descriptor->id] : -1);
            $descriptor->timestampstudent = ((isset($student->competencies->timestamp_student[$descriptor->id])) ? $student->competencies->timestamp_student[$descriptor->id] : 0);
            $descriptor->examples = [];

            //$descriptor->niveauid;

            $descriptor->subs = array();

            $edited = false;
            $inwork = false;
            $notinwork = false;

            $examples = block_exacomp_get_visible_own_and_child_examples_for_descriptor($courseid, $descriptor->id, $student->id);

            foreach ($examples as $example) {
                $sub = new stdClass();
                //cannot be 9 -> no blocking events here
                if ($example->state > BLOCK_EXACOMP_EXAMPLE_STATE_SUBMITTED && !$edited) {
                    $sub->example = false;
                    $sub->title = 'Bearbeitet Lernmaterialien';
                    $descriptor->subs[] = $sub;
                    $edited = true;
                } else if ($example->state > BLOCK_EXACOMP_EXAMPLE_STATE_NOT_SET && $example->state < BLOCK_EXACOMP_EXAMPLE_STATE_SUBMITTED && !$inwork) {
                    $sub->example = false;
                    $sub->title = 'Lernmaterialien in Arbeit';
                    $descriptor->subs[] = $sub;
                    $inwork = true;
                } else if ($example->state == BLOCK_EXACOMP_EXAMPLE_STATE_NOT_SET && !$notinwork) {
                    $sub->example = false;
                    $sub->title = 'Unbearbeitete Lernmaterialien';
                    $descriptor->subs[] = $sub;
                    $notinwork = true;
                }

                $sub = $example;
                $sub->exampleid = $example->id;
                $sub->example = true;
                $sub->teacherevaluation = ((isset($student->examples->teacher[$example->id])) ? $student->examples->teacher[$example->id] : -1);
                $sub->evalniveauid = ($use_evalniveau) ? ((isset($student->examples->niveau[$example->id])) ? $student->examples->niveau[$example->id] : -1) : 0;
                $sub->timestampteacher = ((isset($student->examples->timestamp_teacher[$example->id])) ? $student->examples->timestamp_teacher[$example->id] : 0);
                $sub->studentevaluation = ((isset($student->examples->student[$example->id])) ? $student->examples->student[$example->id] : -1);
                $sub->timestampstudent = ((isset($student->examples->timestamp_student[$example->id])) ? $student->examples->timestamp_student[$example->id] : 0);
                $descriptor->examples[] = $sub;
            }

            $comparison->descriptors[] = $descriptor;
        }

        return $comparison;
    }

    /**
     * Returns desription of method return values
     *
     * @return external_single_structure
     */
    public static function dakora_get_competence_profile_comparison_returns() {
        return new external_single_structure(array(
            'descriptors' => new external_multiple_structure(new external_single_structure(array(
                'descriptorid' => new external_value(PARAM_INT, 'descriptorid'),
                'title' => new external_value(PARAM_TEXT, 'title of descriptor'),
                'numbering' => new external_value(PARAM_TEXT, 'descriptor numbering'),
                'teacherevaluation' => new external_value(PARAM_INT, 'teacher evaluation'),
                'additionalinfo' => new external_value(PARAM_FLOAT, 'additional grading of descriptor'),
                'evalniveauid' => new external_value(PARAM_INT, 'teacher evaluation niveau id'),
                'niveauid' => new external_value(PARAM_INT, 'niveau id (ger: lfs)'),
                'timestampteacher' => new external_value(PARAM_INT, 'timestamp for teacher descriptor evaluation'),
                'studentevaluation' => new external_value(PARAM_INT, 'student evaluation'),
                'timestampstudent' => new external_value(PARAM_INT, 'timestamp for student descriptor evaluation'),
                'examples' => new external_multiple_structure(new external_single_structure(array(
                    'example' => new external_value(PARAM_BOOL, 'indicates if sub is example or grouping statement'),
                    'exampleid' => new external_value(PARAM_INT, 'id of example', VALUE_DEFAULT, 0),
                    'title' => new external_value(PARAM_TEXT, 'title of sub'),
                    'teacherevaluation' => new external_value(PARAM_INT, 'teacher evaluation', VALUE_DEFAULT, -1),
                    'evalniveauid' => new external_value(PARAM_INT, 'teacher evaluation niveau id', VALUE_DEFAULT, -1),
                    'timestampteacher' => new external_value(PARAM_INT, 'timestamp for teacher example evaluation', VALUE_DEFAULT, 0),
                    'studentevaluation' => new external_value(PARAM_INT, 'student evaluation', VALUE_DEFAULT, -1),
                    'timestampstudent' => new external_value(PARAM_INT, 'timestamp for student example evaluation', VALUE_DEFAULT, 0),
                ))),
            ))),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function dakora_get_competence_profile_topic_statistic_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'userid' => new external_value(PARAM_INT, 'id of user'),
            'topicid' => new external_value(PARAM_INT, 'id of subject'),
            'start_timestamp' => new external_value(PARAM_INT, 'start timestamp for evaluation range'),
            'end_timestamp' => new external_value(PARAM_INT, 'end timestamp for evaluation range'),
        ));
    }

    /**
     * get data for 3D graph
     *Get competence statistic for topic in profile for 3D graph
     *
     * @ws-type-read
     */
    public static function dakora_get_competence_profile_topic_statistic($courseid, $userid, $topicid, $start_timestamp, $end_timestamp) {
        global $USER;

        static::validate_parameters(static::dakora_get_competence_profile_topic_statistic_parameters(), array('courseid' => $courseid,
            'userid' => $userid, 'topicid' => $topicid, 'start_timestamp' => $start_timestamp, 'end_timestamp' => $end_timestamp));

        if ($userid == 0) {
            $userid = $USER->id;
        }

        static::require_can_access_course_user($courseid, $userid);

        $statistics = block_exacomp_get_descriptor_statistic_for_topic($courseid, $topicid, $userid, $start_timestamp, $end_timestamp);

        $statistics_return = array();

        foreach ($statistics as $key => $statistic) {
            $return = array();
            foreach ($statistic as $niveautitle => $niveaustat) {
                $niveau = new stdClass();
                $niveau->title = static::custom_htmltrim($niveautitle);
                $niveau->teacherevaluation = $niveaustat->teachervalue;
                $niveau->evalniveauid = $niveaustat->evalniveau;
                $niveau->studentevaluation = $niveaustat->studentvalue;

                $return[] = $niveau;
            }
            $statistics_return[$key]["niveaus"] = $return;
        }

        return $statistics_return;
    }

    /**
     * Returns desription of method return values
     *
     * @return external_single_structure
     */
    public static function dakora_get_competence_profile_topic_statistic_returns() {
        return new external_single_structure(array(
            'descriptor_evaluation' => new external_single_structure(array(
                'niveaus' => new external_multiple_structure(new external_single_structure(array(
                    'title' => new external_value(PARAM_TEXT, 'evalniveauid'),
                    'teacherevaluation' => new external_value(PARAM_INT, 'evaluation value of current lfs'),
                    'evalniveauid' => new external_value(PARAM_INT, 'evaluation niveau id'),
                    'studentevaluation' => new external_value(PARAM_INT, 'student evaluation'),
                ))),
            )),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function is_elove_student_self_assessment_enabled_parameters() {
        return new external_function_parameters(array());
    }

    /**
     * check the corresponding config setting
     *
     * @ws-type-read
     * @return boolean
     */
    public static function is_elove_student_self_assessment_enabled() {
        global $DB, $USER;
        static::validate_parameters(static::is_elove_student_self_assessment_enabled_parameters(), array());

        return array('enabled' => block_exacomp_is_elove_student_self_assessment_enabled());
    }

    /**
     * Returns description of method return values
     *
     * @return external_multiple_structure
     */
    public static function is_elove_student_self_assessment_enabled_returns() {
        return new external_function_parameters(array(
            'enabled' => new external_value(PARAM_BOOL, ''),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function dakora_get_site_policies_parameters() {
        return new external_function_parameters(array());
    }

    /**
     *
     * @ws-type-read
     * @return boolean
     */
    public static function dakora_get_site_policies() {
        global $DB, $USER;
        static::validate_parameters(static::dakora_get_site_policies_parameters(), array());

        $policies = g::$DB->get_records("tool_policy_versions", array());

        $policies = \tool_policy\api::list_current_versions(\tool_policy\policy_version::AUDIENCE_LOGGEDIN);

        // During the signup, show compulsory policies only.
        foreach ($policies as $ix => $policyversion) {
            if ($policyversion->optional == \tool_policy\policy_version::AGREEMENT_OPTIONAL) {
                unset($policies[$ix]);
            }
        }
        $policies = array_values($policies);

        //filter out already agreed policies
        $lang = current_language();
        foreach ($policies as $k => $policy) {
            // Check if this policy version has been agreed or not.
            $versionagreed = false;
            $versiondeclined = false;
            $acceptances = \tool_policy\api::get_user_acceptances($USER->id);
            $policy->versionacceptance = \tool_policy\api::get_user_version_acceptance($USER->id, $policy->id, $acceptances);
            if (!empty($policy->versionacceptance)) {
                // The policy version has ever been replied to before. Check if status = 1 to know if still is accepted.
                if ($policy->versionacceptance->status) {
                    $versionagreed = true;
                } else {
                    $versiondeclined = true;
                }
                if ($versionagreed) {
                    if ($policy->versionacceptance->lang != $lang) {
                        // Add a message because this version has been accepted in a different language than the current one.
                        $policy->versionlangsagreed = get_string('policyversionacceptedinotherlang', 'tool_policy');
                    }
                    $usermodified = $policy->versionacceptance->usermodified;
                }
            }
            if ($versionagreed) {
                unset($policies[$k]);
            }

            $policy->summary = strip_tags($policy->summary);
            $policy->content = strip_tags($policy->content);
        }

        //        var_dump($policies);
        //        die;

        //        return array(
        //            'name' => $policies->name,
        //            'summary' => $policies->summary,
        //            'content' => $policies->content,
        //        );
        return $policies;
    }

    /**
     * Returns description of method return values
     *
     * @return external_multiple_structure
     */
    public static function dakora_get_site_policies_returns() {
        return new external_multiple_structure(new external_single_structure(array(
            'name' => new external_value(PARAM_TEXT, 'name'),
            'summary' => new external_value(PARAM_TEXT, 'summary'),
            'content' => new external_value(PARAM_TEXT, 'content'),
        )));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function dakora_get_example_h5p_activity_results_parameters() {
        return new external_function_parameters(array(
            'exampleid' => new external_value(PARAM_INT, 'id of the example that is connected to an activity'),
        ));
    }

    /**
     *
     * @ws-type-read
     * @return boolean
     */
    public static function dakora_get_example_h5p_activity_results($exampleid) {
        global $DB, $USER, $CFG;
        require_once $CFG->dirroot . '/mod/hvp/locallib.php';
        static::validate_parameters(static::dakora_get_example_h5p_activity_results_parameters(), array("exampleid" => $exampleid));

        // get the related activity
        $example = $DB->get_record(BLOCK_EXACOMP_DB_EXAMPLES, array(
            'id' => $exampleid,
        ));

        $results = [];

        // if hvp: get  /mod/hvp/review.php?id=hvpid&user=$USER->id
        // hvp --> the plugin that is mostly used
        if (strpos($example->externaltask, "/mod/hvp/view.php")) {
            if (!$cm = get_coursemodule_from_id('hvp', $example->activityid)) { // here the coursemodule aka activityid is needed
                print_error('invalidcoursemodule');
            }
            if (!$course = $DB->get_record('course', ['id' => $cm->course])) {
                print_error('coursemisconf');
            }

            $id = $cm->instance; // NOT the activityid, but the hvp-id of that activity.. this is the "instanceid" of the coursemodule
            $userid = (int)$USER->id; // (int) is IMPORTANT. the permissions check further down uses a ===, so it has to be int, not string

            require_login($course, false, $cm);

            // Check permission.
            $context = \context_module::instance($cm->id);
            hvp_require_view_results_permission($userid, $context, $cm->id);

            // Load H5P Content.
            $xapiresults = $DB->get_records_sql("
                SELECT x.*, i.grademax
                FROM {hvp_xapi_results} x
                JOIN {grade_items} i ON i.iteminstance = x.content_id
                WHERE x.user_id = ?
                AND x.content_id = ?
                AND i.itemtype = 'mod'
                AND i.itemmodule = 'hvp'", [$userid, $id]
            );
            if (!$xapiresults) {
                echo "norresultssubmitted";
            }

            $totalrawscore = null;
            $totalmaxscore = null;
            $totalscaledscore = null;
            $scaledscoreperscore = null;

            // Assemble our question tree.
            $basequestion = null;

            // Find base question. This is the compount of all questions of this single hvp.
            foreach ($xapiresults as $question) {
                if ($question->parent_id === null) {
                    // This is the root of our tree.
                    $basequestion = $question;

                    if (isset($question->raw_score) && isset($question->grademax) && isset($question->max_score)) {
                        $scaledscoreperscore = $question->max_score ? ($question->grademax / $question->max_score) : 0;
                        $question->score_scale = round($scaledscoreperscore, 2);
                        $totalrawscore = $question->raw_score;
                        $totalmaxscore = $question->max_score;
                        if ($question->max_score && $question->raw_score === $question->max_score) {
                            $totalscaledscore = round($question->grademax, 2);
                        } else {
                            $totalscaledscore = round($question->score_scale * $question->raw_score, 2);
                        }
                    }
                    break;
                }
            }

            // This could give more detailed results, maybe needed later.
            //foreach ($xapiresults as $question) {
            //    if ($question->parent_id === null) {
            //        // Already processed.
            //        continue;
            //    } else if (isset($xapiresults[$question->parent_id])) {
            //        // Add to parent.
            //        $xapiresults[$question->parent_id]->children[] = $question;
            //    }
            //
            //    // Set scores.
            //    if (!isset($question->raw_score)) {
            //        $question->raw_score = 0;
            //    }
            //    if (isset($question->raw_score) && isset($question->grademax) && isset($question->max_score)) {
            //        $question->scaled_score_per_score = $scaledscoreperscore;
            //        $question->parent_max_score = $totalmaxscore;
            //        $question->score_scale = round($question->raw_score * $scaledscoreperscore, 2);
            //    }
            //
            //    // Set score labels.
            //    $question->score_label            = get_string('reportingscorelabel', 'hvp');
            //    $question->scaled_score_label     = get_string('reportingscaledscorelabel', 'hvp');
            //    $question->score_delimiter        = get_string('reportingscoredelimiter', 'hvp');
            //    $question->scaled_score_delimiter = get_string('reportingscaledscoredelimiter', 'hvp');
            //    $question->questions_remaining_label = get_string('reportingquestionsremaininglabel', 'hvp');
            //}

            $current_result = [];
            $current_result["raw_score"] = $totalrawscore;
            $current_result["max_score"] = $totalmaxscore;
            $resultpage_url = new moodle_url("/mod/hvp/review.php", ["id" => $cm->instance, "user" => $userid]);

            // TODO: what to return for multiple questions

            $results = array(
                'current_result' => $current_result,
                'resultpage_url' => $resultpage_url->out(false),
            );

            //$fileurl = (string)new moodle_url("/blocks/exaport/portfoliofile.php", [
            //                            'userid' => $userid,
            //                            'itemid' => $item->id,
            //                            'commentid' => $itemcomment->id,
            //                            'wstoken' => static::wstoken(),
            //                        ]);
        } else if (strpos($example->externaltask, "/mod/h5pactivity/view.php")) {
            // if h5p: get /mod/h5pactivity/report.php?a=1&userid=5
            // todo.. but this is mostly not used
            throw new moodle_exception("H5P is not implemented yet. Only works for hvp interactive content files");
        } else {
            throw new moodle_exception("Not a hvp file. This example is not linked to a hvp-activity.");
        }

        return $results;
    }

    /**
     * Returns description of method return values
     *
     * @return external_multiple_structure
     */
    public static function dakora_get_example_h5p_activity_results_returns() {
        return new external_single_structure(array(
            'current_result' => new external_single_structure(array(
                'raw_score' => new external_value(PARAM_INT, 'current score of the student on this hvp'),
                'max_score' => new external_value(PARAM_INT, 'maximum score you can get on this hvp'),
            ), "current result. The interactive content hvp module does not store a history of results"),
            //'results' => new external_value(PARAM_TEXT, 'summary'),
            'resultpage_url' => new external_value(PARAM_TEXT, 'content'),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function diggr_create_cohort_parameters() {
        return new external_function_parameters(array(
            'name' => new external_value(PARAM_RAW, 'cohort name'),
            'skz' => new external_value(PARAM_RAW, 'school number'),
        ));
    }

    /**
     * Create one or more cohorts
     *
     * @param array $cohorts
     *            An array of cohorts to create.
     * @return array An array of arrays
     * @since Moodle 2.5
     *
     * @ws-type-write
     */
    public static function diggr_create_cohort($name, $skz) {
        global $DB, $USER;

        $parameters = static::validate_parameters(static::diggr_create_cohort_parameters(), array(
            'name' => $name,
            'skz' => $skz,
        ));

        $isTeacher = block_exacomp_is_teacher(get_config('auth_dgb', 'courseid'), $USER->id); //this seems to be a problem sometimes
        // block_exacomp_is_teacher_in_any_course
        //        var_dump(get_config('auth_dgb','courseid'));
        //        die;

        if ($isTeacher) {
            do {
                $nps = "";
                for ($i = 0; $i < 6; $i++) {
                    $nps .= chr((mt_rand(1, 36) <= 26) ? mt_rand(97, 122) : mt_rand(48, 57));
                }
            } while ($DB->get_field('block_exacompcohortcode', 'id', array('cohortcode' => $nps)));

            $cohortcode_return = array();

            $DB->insert_record('cohort', array(
                "contextid" => get_config('auth_dgb', 'contextid'),
                "name" => $skz . '' . $name,
                "descriptionformat" => 1,
                "timecreated" => time(),
                "timemodified" => time(),
            ));
            $cohortid = $DB->get_field('cohort', 'MAX(id)', array(
                'name' => $skz . '' . $name,
            ));
            $DB->insert_record('block_exacompcohortcode', array(
                "cohortid" => $cohortid,
                "cohortcode" => $nps,
                "skz" => $skz,
                "trainerid" => $USER->id,
            ));

            $cohortcode_return['cohortcode'] = $nps;
            return $cohortcode_return;
        }
        return false;

    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 2.5
     */
    public static function diggr_create_cohort_returns() {
        return new external_single_structure(
            array(
                'cohortcode' => new external_value(PARAM_RAW, 'cohortcode'),
            )
        );
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function diggr_get_students_of_cohort_parameters() {
        return new external_function_parameters(array(
            'cohortid' => new external_value(PARAM_RAW, 'cohort id'),
        ));
    }

    /**
     * Create one or more cohorts
     *
     * @param array $cohorts
     *            An array of cohorts to create.
     * @return array An array of arrays
     * @since Moodle 2.5
     *
     * @ws-type-read
     */
    public static function diggr_get_students_of_cohort($cohortid) {
        global $DB;

        $parameters = static::validate_parameters(static::diggr_get_students_of_cohort_parameters(), array(
            'cohortid' => $cohortid,
        ));

        $returnStudents = array();
        $returndata = new stdClass ();

        $students = $DB->get_records('cohort_members', array('cohortid' => $cohortid));
        foreach ($students as $student) {
            $studentObject = $DB->get_record('user', array(
                'id' => $student->userid,
            ));
            $returndataObject = new stdClass ();
            $returndataObject->userid = $student->userid;
            $returndataObject->name = $studentObject->username;

            $returnStudents[] = $returndataObject;
        }

        $returndata->cohortcode = $DB->get_field('block_exacompcohortcode', 'cohortcode', array(
            "cohortid" => $cohortid,
        ));

        $returndata->cohortid = $cohortid;
        $returndata->students = $returnStudents;

        return $returndata;
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 2.5
     */
    public static function diggr_get_students_of_cohort_returns() {
        return new external_single_structure(array(
            'cohortid' => new external_value(PARAM_INT, 'id of cohort'),
            'cohortcode' => new external_value(PARAM_TEXT, 'code of cohort'),
            'students' => new external_multiple_structure(new external_single_structure(array(
                'userid' => new external_value(PARAM_INT, 'id of student'),
                'name' => new external_value(PARAM_TEXT, 'name of student'),
            ))),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function diggr_get_cohorts_of_trainer_parameters() {
        return new external_function_parameters(array());
    }

    /**
     * Create one or more cohorts
     *
     * @param array $cohorts
     *            An array of cohorts to create.
     * @return array An array of arrays
     * @since Moodle 2.5
     *
     * @ws-type-read
     */
    public static function diggr_get_cohorts_of_trainer() {
        global $DB, $USER;

        $parameters = static::validate_parameters(static::diggr_get_cohorts_of_trainer_parameters(), array());

        $returndata = array();
        $cohorts = array();

        $dbCohorts = $DB->get_records('block_exacompcohortcode', array('trainerid' => $USER->id));
        foreach ($dbCohorts as $cohort) {
            $cohortname = $DB->get_field('cohort', 'name', array(
                'id' => $cohort->cohortid,
            ));
            $returndataObject = new stdClass ();

            $returndataObject->name = $cohortname;
            $returndataObject->cohortid = $cohort->cohortid;
            $returndataObject->cohortcode = $cohort->cohortcode;
            $cohorts[] = $returndataObject;
        }

        $returndata["cohorts"] = $cohorts;
        return $returndata;
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 2.5
     */
    public static function diggr_get_cohorts_of_trainer_returns() {
        return new external_single_structure(array(
            'cohorts' => new external_multiple_structure(new external_single_structure(array(
                'cohortid' => new external_value(PARAM_INT, 'id of cohort'),
                'name' => new external_value(PARAM_TEXT, 'name of user'),
                'cohortcode' => new external_value(PARAM_TEXT, 'code of cohort'),
            )))));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function dakora_get_evaluation_config_parameters() {
        return new external_function_parameters(array());
    }

    /**
     * get evaluation configuration
     * get admin evaluation configurations
     *
     * @deprecated use dakora_get_config instead         actually getting called a lot but not used RW
     * @ws-type-read
     */
    public static function dakora_get_evaluation_config() {
        // TODO: fjungwirth: What if scheme > 4 is selected in a course? WS & Dakora need to be adapted to that I think

        static::validate_parameters(static::dakora_get_evaluation_config_parameters(), array());

        //$confiiig=get_config(global_config::get_evalniveaus(true));
        //echo('asdf');

        return array('use_evalniveau' => block_exacomp_use_eval_niveau(), // TODO: courseid?
            // 			'evalniveautype' => block_exacomp_evaluation_niveau_type(),
            'evalniveaus' => global_config::get_evalniveaus(true),
            'values' => global_config::get_teacher_eval_items(),
        );
    }

    /**
     * Returns description of method return values
     *
     * @return external_multiple_structure
     */
    public static function dakora_get_evaluation_config_returns() {
        return new external_single_structure(array(
            'use_evalniveau' => new external_value(PARAM_BOOL, 'use evaluation niveaus'),
            // 			'evalniveautype' => new external_value(PARAM_INT, 'same as adminscheme before: 1: GME, 2: ABC, 3: */**/***'),
            'evalniveaus' => new external_single_structure(array(
                1 => new external_value(PARAM_TEXT, 'evaluation title for id = 1', VALUE_OPTIONAL),
                2 => new external_value(PARAM_TEXT, 'evaluation title for id = 2', VALUE_OPTIONAL),
                3 => new external_value(PARAM_TEXT, 'evaluation title for id = 3', VALUE_OPTIONAL))),
            'values' => new external_single_structure(array(
                0 => new external_value(PARAM_TEXT, 'value title for id = 0', VALUE_DEFAULT, "0"),
                1 => new external_value(PARAM_TEXT, 'value title for id = 1', VALUE_DEFAULT, "1"),
                2 => new external_value(PARAM_TEXT, 'value title for id = 2', VALUE_DEFAULT, "2"),
                3 => new external_value(PARAM_TEXT, 'value title for id = 3', VALUE_DEFAULT, "3"))),
        ));
    }

    public static function dakora_get_config_parameters() {
        return new external_function_parameters(array());
    }

    /**
     * Returns description of method return values
     *
     * @return external_multiple_structure
     */
    public static function dakora_get_config_returns() {
        return new external_single_structure(array(
            'points_limit' => new external_value(PARAM_INT, 'points_limit'),
            'grade_limit' => new external_value(PARAM_INT, 'grade_limit'),
            'points_negative_threshold' => new external_value(PARAM_INT, 'points_negative_threshold. Values below this value are negative'),
            'grade_negative_threshold' => new external_value(PARAM_INT, 'grade_negative_threshold. Values below this value are negative'),
            'verbal_negative_threshold' => new external_value(PARAM_INT, 'grade_negative_threshold. Values below this value are negative'),
            //'diffLevel_options' => static::key_value_returns(PARAM_INT, PARAM_TEXT, 'diffLevel_options'),
            //'verbose_options' => static::key_value_returns(PARAM_INT, PARAM_TEXT, 'verbose_options'),
            'example_scheme' => new external_value(PARAM_INT, 'example_scheme'),
            'example_diffLevel' => new external_value(PARAM_BOOL, 'example_diffLevel'),
            'example_SelfEval' => new external_value(PARAM_BOOL, 'example_SelfEval'),
            'childcomp_scheme' => new external_value(PARAM_INT, 'childcomp_scheme'),
            'childcomp_diffLevel' => new external_value(PARAM_BOOL, 'childcomp_diffLevel'),
            'childcomp_SelfEval' => new external_value(PARAM_BOOL, 'childcomp_SelfEval'),
            'comp_scheme' => new external_value(PARAM_INT, 'comp_scheme'),
            'comp_diffLevel' => new external_value(PARAM_BOOL, 'comp_diffLevel'),
            'comp_SelfEval' => new external_value(PARAM_BOOL, 'comp_SelfEval'),
            'topic_scheme' => new external_value(PARAM_INT, 'topic_scheme'),
            'topic_diffLevel' => new external_value(PARAM_BOOL, 'topic_diffLevel'),
            'topic_SelfEval' => new external_value(PARAM_BOOL, 'topic_SelfEval'),
            'subject_scheme' => new external_value(PARAM_INT, 'subject_scheme'),
            'subject_diffLevel' => new external_value(PARAM_BOOL, 'subject_diffLevel'),
            'subject_SelfEval' => new external_value(PARAM_BOOL, 'subject_SelfEval'),
            'theme_scheme' => new external_value(PARAM_INT, 'theme_scheme'),
            'theme_diffLevel' => new external_value(PARAM_BOOL, 'theme_diffLevel'),
            'theme_SelfEval' => new external_value(PARAM_BOOL, 'theme_SelfEval'),
            'use_evalniveau' => new external_value(PARAM_BOOL, 'use evaluation niveaus'),
            // 			'evalniveautype' => new external_value(PARAM_INT, 'same as adminscheme before: 1: GME, 2: ABC, 3: */**/***'),
            'evalniveaus' => static::key_value_returns(PARAM_INT, PARAM_TEXT, 'evaluation titles'),
            'teacherevalitems' => static::key_value_returns(PARAM_INT, PARAM_TEXT, 'values'),
            'teacherevalitems_short' => static::key_value_returns(PARAM_INT, PARAM_TEXT, 'values'),
            'studentevalitems' => static::key_value_returns(PARAM_INT, PARAM_TEXT, 'values'),
            'studentevalitems_short' => static::key_value_returns(PARAM_INT, PARAM_TEXT, 'values'),
            'studentevalitems_examples' => static::key_value_returns(PARAM_INT, PARAM_TEXT, 'values'),
            'studentevalitems_examples_short' => static::key_value_returns(PARAM_INT, PARAM_TEXT, 'values'),
            'gradingperiods' => new external_multiple_structure(new external_single_structure([
                'id' => new external_value(PARAM_INT, 'id'),
                'description' => new external_value(PARAM_TEXT, 'name'),
                'starttime' => new external_value(PARAM_INT, 'active from'),
                'endtime' => new external_value(PARAM_INT, 'active to'),
            ]), 'grading periods from exastud'),
            'taxonomies' => new external_multiple_structure(new external_single_structure([
                'id' => new external_value(PARAM_INT, 'id'),
                'title' => new external_value(PARAM_TEXT, 'name'),
                'source' => new external_value(PARAM_TEXT, 'source'),
            ]), 'values'),
            'version' => new external_value(PARAM_FLOAT, 'exacomp version number in YYYYMMDDXX format'),
            'moodleversion' => new external_value(PARAM_FLOAT, 'moodle version number in YYYYMMDDXX format'),
            'release' => new external_value(PARAM_TEXT, 'plugin release x.x.x format'),
            'exaportactive' => new external_value(PARAM_BOOL, 'flag if exaportfolio should be active'),// Returns JSON content.
            'customlanguagefile' => new external_value(PARAM_TEXT, 'customlanguagefiel'), // Returns JSON content.
            'timeout' => new external_value(PARAM_INT, 'a timeout timer'),
            'show_overview' => new external_value(PARAM_BOOL, 'flag if "show overview" is active'),
            'show_eportfolio' => new external_value(PARAM_BOOL, 'flag if "show ePortfolio" is active'),
            'categories' => new external_multiple_structure(new external_single_structure([
                'id' => new external_value(PARAM_INT, 'id'),
                'title' => new external_value(PARAM_TEXT, 'name'),
                'source' => new external_value(PARAM_TEXT, 'source'),
            ]), 'values'),
            'assessment_verbose_lowerisbetter' => new external_value(PARAM_BOOL, 'flag if "The lower the Assessment, the better" is active'),
        ));
    }

    /**
     *
     * @ws-type-read
     * @return array
     */
    public static function dakora_get_config() {
        global $CFG, $USER;
        static::validate_parameters(static::dakora_get_evaluation_config_parameters(), array());

        $info = core_plugin_manager::instance()->get_plugin_info('block_exacomp');

        $gradingperiods = block_exacomp_is_exastud_installed() ? \block_exastud\api::get_periods() : [];

        $exaportactive = true;

        if (static::get_user_role_common($USER->id)->role == BLOCK_EXACOMP_WS_ROLE_STUDENT) {
            $exaportactive = block_exacomp_is_block_used_by_student("exaport", $USER->id);
        }

        return array(
            'points_limit' => block_exacomp_get_assessment_points_limit(),
            'grade_limit' => block_exacomp_get_assessment_grade_limit(),
            'points_negative_threshold' => block_exacomp_get_assessment_points_negative_threshold(),
            'grade_negative_threshold' => block_exacomp_get_assessment_grade_negative_threshold(),
            'verbal_negative_threshold' => block_exacomp_get_assessment_verbose_negative_threshold(),
            //'diffLevel_options' => static::return_key_value(global_config::get_diffLevel_options(true)),
            //'verbose_options' => static::return_key_value(global_config::get_verbose_options()),
            'example_scheme' => block_exacomp_get_assessment_example_scheme(),
            'example_diffLevel' => block_exacomp_get_assessment_example_diffLevel(),
            'example_SelfEval' => block_exacomp_get_assessment_example_SelfEval(),
            'childcomp_scheme' => block_exacomp_get_assessment_childcomp_scheme(),
            'childcomp_diffLevel' => block_exacomp_get_assessment_childcomp_diffLevel(),
            'childcomp_SelfEval' => block_exacomp_get_assessment_childcomp_SelfEval(),
            'comp_scheme' => block_exacomp_get_assessment_comp_scheme(),
            'comp_diffLevel' => block_exacomp_get_assessment_comp_diffLevel(),
            'comp_SelfEval' => block_exacomp_get_assessment_comp_SelfEval(),
            'topic_scheme' => block_exacomp_get_assessment_topic_scheme(),
            'topic_diffLevel' => block_exacomp_get_assessment_topic_diffLevel(),
            'topic_SelfEval' => block_exacomp_get_assessment_topic_SelfEval(),
            'subject_scheme' => block_exacomp_get_assessment_subject_scheme(),
            'subject_diffLevel' => block_exacomp_get_assessment_subject_diffLevel(),
            'subject_SelfEval' => block_exacomp_get_assessment_subject_SelfEval(),
            'theme_scheme' => block_exacomp_get_assessment_theme_scheme(),
            'theme_diffLevel' => block_exacomp_get_assessment_theme_diffLevel(),
            'theme_SelfEval' => block_exacomp_get_assessment_theme_SelfEval(),
            'use_evalniveau' => block_exacomp_use_eval_niveau(),
            // 			'evalniveautype' => block_exacomp_evaluation_niveau_type(),
            'evalniveaus' => static::return_key_value(global_config::get_evalniveaus(true)),
            'teacherevalitems' => static::return_key_value(global_config::get_teacher_eval_items(0, null, BLOCK_EXACOMP_ASSESSMENT_TYPE_VERBOSE)),
            'teacherevalitems_short' => static::return_key_value(global_config::get_teacher_eval_items(0, true, BLOCK_EXACOMP_ASSESSMENT_TYPE_VERBOSE)),
            'studentevalitems' => static::return_key_value(global_config::get_student_eval_items(true)),
            'studentevalitems_short' => static::return_key_value(global_config::get_student_eval_items(true, null, true)),
            'studentevalitems_examples' => static::return_key_value(global_config::get_student_eval_items(true, BLOCK_EXACOMP_TYPE_EXAMPLE)),
            'studentevalitems_examples_short' => static::return_key_value(global_config::get_student_eval_items(true, BLOCK_EXACOMP_TYPE_EXAMPLE, true)),
            'gradingperiods' => $gradingperiods,
            'taxonomies' => g::$DB->get_records(BLOCK_EXACOMP_DB_TAXONOMIES, null, 'source', 'id, title, source'),
            'version' => $info->versiondb,
            'moodleversion' => $CFG->version,
            'release' => $info->release,
            'exaportactive' => $exaportactive,
            'customlanguagefile' => block_exacomp_get_config_dakora_language_file(true), // Returns JSON content.
            'timeout' => block_exacomp_get_config_dakora_timeout(),
            'show_overview' => block_exacomp_get_config_dakora_show_overview(),
            'show_eportfolio' => block_exacomp_get_config_dakora_show_eportfolio(),
            'categories' => g::$DB->get_records(BLOCK_EXACOMP_DB_CATEGORIES, null, 'source', 'id, title, source'),
            'assessment_verbose_lowerisbetter' => block_exacomp_get_config_assessment_verbose_lowerisbetter(),
        );
    }

    public static function dakora_get_courseconfigs_parameters() {
        return new external_function_parameters(array());
    }

    /**
     *
     * @ws-type-read
     * @return array
     */
    public static function dakora_get_courseconfigs() {
        global $CFG, $USER, $DB;
        static::validate_parameters(static::dakora_get_courseconfigs_parameters(), array());

        $info = core_plugin_manager::instance()->get_plugin_info('block_exacomp');

        $gradingperiods = block_exacomp_is_exastud_installed() ? \block_exastud\api::get_periods() : [];

        $exaportactive = true;

        if (static::get_user_role_common($USER->id)->role == BLOCK_EXACOMP_WS_ROLE_STUDENT) {
            $exaportactive = block_exacomp_is_block_used_by_student("exaport", $USER->id);
        }

        // Get which configuration is used for which course

        $courses = static::get_courses($USER->id);
        //        $courses_assoc = array();
        foreach ($courses as $key => $course) {
            //            $courses_assoc[$course["courseid"]] = $course;
            //            $courses_assoc[$course["courseid"]]["assessment_config"] = $DB->get_field('block_exacompsettings', 'assessmentconfiguration', ['courseid' => $course["courseid"]]);
            //            $courses[$key]["assessment_config"] = $DB->get_field('block_exacompsettings', 'assessmentconfiguration', ['courseid' => $course["courseid"]]); // already done in get_courses
            if ($courses[$key]["assessment_config"] == null) {
                $courses[$key]["assessment_config"] = 0;
            }
        }

        $assessment_configurations = block_exacomp_get_assessment_configurations();

        $configs = array();
        $configs[] = array(
            'id' => 0,
            'name' => '',
            'points_limit' => block_exacomp_get_assessment_points_limit(),
            'grade_limit' => block_exacomp_get_assessment_grade_limit(),
            'points_negative_threshold' => block_exacomp_get_assessment_points_negative_threshold(),
            'grade_negative_threshold' => block_exacomp_get_assessment_grade_negative_threshold(),
            'verbal_negative_threshold' => block_exacomp_get_assessment_verbose_negative_threshold(),
            //'diffLevel_options' => static::return_key_value(global_config::get_diffLevel_options(true)),
            //'verbose_options' => static::return_key_value(global_config::get_verbose_options()),
            'example_scheme' => block_exacomp_get_assessment_example_scheme(),
            'example_diffLevel' => block_exacomp_get_assessment_example_diffLevel(),
            'example_SelfEval' => block_exacomp_get_assessment_example_SelfEval(),
            'childcomp_scheme' => block_exacomp_get_assessment_childcomp_scheme(),
            'childcomp_diffLevel' => block_exacomp_get_assessment_childcomp_diffLevel(),
            'childcomp_SelfEval' => block_exacomp_get_assessment_childcomp_SelfEval(),
            'comp_scheme' => block_exacomp_get_assessment_comp_scheme(),
            'comp_diffLevel' => block_exacomp_get_assessment_comp_diffLevel(),
            'comp_SelfEval' => block_exacomp_get_assessment_comp_SelfEval(),
            'topic_scheme' => block_exacomp_get_assessment_topic_scheme(),
            'topic_diffLevel' => block_exacomp_get_assessment_topic_diffLevel(),
            'topic_SelfEval' => block_exacomp_get_assessment_topic_SelfEval(),
            'subject_scheme' => block_exacomp_get_assessment_subject_scheme(),
            'subject_diffLevel' => block_exacomp_get_assessment_subject_diffLevel(),
            'subject_SelfEval' => block_exacomp_get_assessment_subject_SelfEval(),
            'theme_scheme' => block_exacomp_get_assessment_theme_scheme(),
            'theme_diffLevel' => block_exacomp_get_assessment_theme_diffLevel(),
            'theme_SelfEval' => block_exacomp_get_assessment_theme_SelfEval(),
            'use_evalniveau' => block_exacomp_use_eval_niveau(),
            // 			'evalniveautype' => block_exacomp_evaluation_niveau_type(),
            'evalniveaus' => static::return_key_value(global_config::get_evalniveaus(true)),
            'teacherevalitems' => static::return_key_value(global_config::get_teacher_eval_items(0, null, BLOCK_EXACOMP_ASSESSMENT_TYPE_VERBOSE)),
            'teacherevalitems_short' => static::return_key_value(global_config::get_teacher_eval_items(0, true, BLOCK_EXACOMP_ASSESSMENT_TYPE_VERBOSE)),
            'studentevalitems' => static::return_key_value(global_config::get_student_eval_items(true)),
            'studentevalitems_short' => static::return_key_value(global_config::get_student_eval_items(true, null, true)),
            'studentevalitems_examples' => static::return_key_value(global_config::get_student_eval_items(true, BLOCK_EXACOMP_TYPE_EXAMPLE)),
            'studentevalitems_examples_short' => static::return_key_value(global_config::get_student_eval_items(true, BLOCK_EXACOMP_TYPE_EXAMPLE, true)),
            'gradingperiods' => $gradingperiods,
            'taxonomies' => g::$DB->get_records(BLOCK_EXACOMP_DB_TAXONOMIES, null, 'source', 'id, title, source'),
            'version' => $info->versiondb,
            'moodleversion' => $CFG->version,
            'release' => $info->release,
            'exaportactive' => $exaportactive,
            'customlanguagefile' => block_exacomp_get_config_dakora_language_file(true), // Returns JSON content.
            'timeout' => block_exacomp_get_config_dakora_timeout(),
            'show_overview' => block_exacomp_get_config_dakora_show_overview(),
            'show_eportfolio' => block_exacomp_get_config_dakora_show_eportfolio(),
            'categories' => g::$DB->get_records(BLOCK_EXACOMP_DB_CATEGORIES, null, 'source', 'id, title, source'),
            'assessment_verbose_lowerisbetter' => block_exacomp_get_config_assessment_verbose_lowerisbetter(),
        );

        foreach ($assessment_configurations as $id => $configuration) {
            $configs[] = array(
                'id' => $id, // array is indexed by id
                'name' => $configuration['name'],
                'points_limit' => $configuration["assessment_points_limit"],
                'grade_limit' => $configuration["assessment_grade_limit"],
                'points_negative_threshold' => $configuration["assessment_points_negativ"],
                'grade_negative_threshold' => $configuration["assessment_grade_negativ"],
                'verbal_negative_threshold' => $configuration["assessment_verbose_negative"],
                //'diffLevel_options' => static::return_key_value(global_config::get_diffLevel_options(true)),
                //'verbose_options' => static::return_key_value(global_config::get_verbose_options()),
                'example_scheme' => $configuration["assessment_example_scheme"],
                'example_diffLevel' => $configuration["assessment_example_diffLevel"],
                'example_SelfEval' => $configuration["assessment_example_SelfEval"],
                'childcomp_scheme' => $configuration["assessment_childcomp_scheme"],
                'childcomp_diffLevel' => $configuration["assessment_childcomp_diffLevel"],
                'childcomp_SelfEval' => $configuration["assessment_childcomp_SelfEval"],
                'comp_scheme' => $configuration["assessment_comp_scheme"],
                'comp_diffLevel' => $configuration["assessment_comp_diffLevel"],
                'comp_SelfEval' => $configuration["assessment_comp_SelfEval"],
                'topic_scheme' => $configuration["assessment_topic_scheme"],
                'topic_diffLevel' => $configuration["assessment_topic_diffLevel"],
                'topic_SelfEval' => $configuration["assessment_topic_SelfEval"],
                'subject_scheme' => $configuration["assessment_subject_scheme"],
                'subject_diffLevel' => $configuration["assessment_subject_diffLevel"],
                'subject_SelfEval' => $configuration["assessment_subject_SelfEval"],
                'theme_scheme' => $configuration["assessment_theme_scheme"],
                'theme_diffLevel' => $configuration["assessment_theme_diffLevel"],
                'theme_SelfEval' => $configuration["assessment_theme_SelfEval"],
                'use_evalniveau' => $configuration["assessment_diffLevel_options"] != '',
                // 			'evalniveautype' => block_exacomp_evaluation_niveau_type(),
                'evalniveaus' => static::get_evalniveaus_from_config($configuration),
                'teacherevalitems' => static::get_teacher_eval_items_from_config($configuration),
                'teacherevalitems_short' => static::get_teacher_eval_items_short_from_config($configuration),
                'studentevalitems' => static::return_key_value(global_config::get_student_eval_items(true)),
                //                'studentevalitems' => static::get_student_eval_items_from_config($configuration),
                'studentevalitems_short' => static::return_key_value(global_config::get_student_eval_items(true, null, true)),
                'studentevalitems_examples' => static::return_key_value(global_config::get_student_eval_items(true, BLOCK_EXACOMP_TYPE_EXAMPLE)),
                'studentevalitems_examples_short' => static::return_key_value(global_config::get_student_eval_items(true, BLOCK_EXACOMP_TYPE_EXAMPLE, true)),
                'gradingperiods' => $gradingperiods,
                'taxonomies' => g::$DB->get_records(BLOCK_EXACOMP_DB_TAXONOMIES, null, 'source', 'id, title, source'),
                'version' => $info->versiondb,
                'moodleversion' => $CFG->version,
                'release' => $info->release,
                'exaportactive' => $exaportactive,
                'customlanguagefile' => block_exacomp_get_config_dakora_language_file(true), // Returns JSON content.
                'timeout' => block_exacomp_get_config_dakora_timeout(),
                'show_overview' => block_exacomp_get_config_dakora_show_overview(),
                'show_eportfolio' => block_exacomp_get_config_dakora_show_eportfolio(),
                'categories' => g::$DB->get_records(BLOCK_EXACOMP_DB_CATEGORIES, null, 'source', 'id, title, source'),
                'assessment_verbose_lowerisbetter' => block_exacomp_get_config_assessment_verbose_lowerisbetter(),
            );
        }

        $ret = array();
        $ret["courses"] = $courses;
        $ret["configs"] = $configs;
        return $ret;
    }

    //    private static function get_student_eval_items_from_config ($configuration){
    //
    //    }

    private static function get_teacher_eval_items_short_from_config($configuration) {
        $jsondata = $configuration["assessment_verbose_options_short"];

        $copyofdata = $jsondata;
        $configdata = json_decode($jsondata, true);
        if (json_last_error() && $copyofdata != '') { // if old data is not json
            $configdata['de'] = $copyofdata;
        }
        $language = current_language();
        if ($language && array_key_exists($language, $configdata) && $configdata[$language] != '') {
            $evalitems = $configdata[$language];
        } else {
            $evalitems = $configdata['de'];
        }

        $evalitems = array_map('trim', explode(',', $evalitems));
        $no_grading = array(-1 => '');
        $evalitems = $no_grading + $evalitems;
        return static::return_key_value($evalitems);
    }

    private static function get_teacher_eval_items_from_config($configuration) {
        $jsondata = $configuration["assessment_verbose_options"];

        $copyofdata = $jsondata;
        $configdata = json_decode($jsondata, true);
        if (json_last_error() && $copyofdata != '') { // if old data is not json
            $configdata['de'] = $copyofdata;
        }
        $language = current_language();
        if ($language && array_key_exists($language, $configdata) && $configdata[$language] != '') {
            $evalitems = $configdata[$language];
        } else {
            $evalitems = $configdata['de'];
        }

        $evalitems = array_map('trim', explode(',', $evalitems));
        $no_grading = array(-1 => '');
        $evalitems = $no_grading + $evalitems;
        return static::return_key_value($evalitems);
    }

    private static function get_evalniveaus_from_config($configuration) {
        $evalniveaus = preg_split("/[\s*,\s*]*,+[\s*,\s*]*/", $configuration["assessment_diffLevel_options"]);
        $evalniveaus = array_combine(range(1, count($evalniveaus)), array_values($evalniveaus));
        $evalniveaus = [0 => ''] + $evalniveaus;
        return static::return_key_value($evalniveaus);
    }

    /**
     * Returns description of method return values
     *
     * @return external_multiple_structure
     */
    public static function dakora_get_courseconfigs_returns() {
        return new external_single_structure(array(
            'courses' => new external_multiple_structure(new external_single_structure(array(
                'courseid' => new external_value(PARAM_INT, 'id of course'),
                'fullname' => new external_value(PARAM_TEXT, 'fullname of course'),
                'assessment_config' => new external_value(PARAM_RAW, 'which course specific assessment_config is used'),
            ))),
            'configs' => new external_multiple_structure(new external_single_structure(array(
                'id' => new external_value(PARAM_INT, ''),
                'name' => new external_value(PARAM_TEXT, ''),
                'points_limit' => new external_value(PARAM_INT, 'points_limit'),
                'grade_limit' => new external_value(PARAM_INT, 'grade_limit'),
                'points_negative_threshold' => new external_value(PARAM_INT, 'points_negative_threshold. Values below this value are negative'),
                'grade_negative_threshold' => new external_value(PARAM_INT, 'grade_negative_threshold. Values below this value are negative'),
                'verbal_negative_threshold' => new external_value(PARAM_INT, 'grade_negative_threshold. Values below this value are negative'),
                //'diffLevel_options' => static::key_value_returns(PARAM_INT, PARAM_TEXT, 'diffLevel_options'),
                //'verbose_options' => static::key_value_returns(PARAM_INT, PARAM_TEXT, 'verbose_options'),
                'example_scheme' => new external_value(PARAM_INT, 'example_scheme'),
                'example_diffLevel' => new external_value(PARAM_BOOL, 'example_diffLevel'),
                'example_SelfEval' => new external_value(PARAM_BOOL, 'example_SelfEval'),
                'childcomp_scheme' => new external_value(PARAM_INT, 'childcomp_scheme'),
                'childcomp_diffLevel' => new external_value(PARAM_BOOL, 'childcomp_diffLevel'),
                'childcomp_SelfEval' => new external_value(PARAM_BOOL, 'childcomp_SelfEval'),
                'comp_scheme' => new external_value(PARAM_INT, 'comp_scheme'),
                'comp_diffLevel' => new external_value(PARAM_BOOL, 'comp_diffLevel'),
                'comp_SelfEval' => new external_value(PARAM_BOOL, 'comp_SelfEval'),
                'topic_scheme' => new external_value(PARAM_INT, 'topic_scheme'),
                'topic_diffLevel' => new external_value(PARAM_BOOL, 'topic_diffLevel'),
                'topic_SelfEval' => new external_value(PARAM_BOOL, 'topic_SelfEval'),
                'subject_scheme' => new external_value(PARAM_INT, 'subject_scheme'),
                'subject_diffLevel' => new external_value(PARAM_BOOL, 'subject_diffLevel'),
                'subject_SelfEval' => new external_value(PARAM_BOOL, 'subject_SelfEval'),
                'theme_scheme' => new external_value(PARAM_INT, 'theme_scheme'),
                'theme_diffLevel' => new external_value(PARAM_BOOL, 'theme_diffLevel'),
                'theme_SelfEval' => new external_value(PARAM_BOOL, 'theme_SelfEval'),
                'use_evalniveau' => new external_value(PARAM_BOOL, 'use evaluation niveaus'),
                // 			'evalniveautype' => new external_value(PARAM_INT, 'same as adminscheme before: 1: GME, 2: ABC, 3: */**/***'),
                'evalniveaus' => static::key_value_returns(PARAM_INT, PARAM_TEXT, 'evaluation titles'),
                'teacherevalitems' => static::key_value_returns(PARAM_INT, PARAM_TEXT, 'values'),
                'teacherevalitems_short' => static::key_value_returns(PARAM_INT, PARAM_TEXT, 'values'),
                'studentevalitems' => static::key_value_returns(PARAM_INT, PARAM_TEXT, 'values'),
                'studentevalitems_short' => static::key_value_returns(PARAM_INT, PARAM_TEXT, 'values'),
                'studentevalitems_examples' => static::key_value_returns(PARAM_INT, PARAM_TEXT, 'values'),
                'studentevalitems_examples_short' => static::key_value_returns(PARAM_INT, PARAM_TEXT, 'values'),
                'gradingperiods' => new external_multiple_structure(new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'id'),
                    'description' => new external_value(PARAM_TEXT, 'name'),
                    'starttime' => new external_value(PARAM_INT, 'active from'),
                    'endtime' => new external_value(PARAM_INT, 'active to'),
                ]), 'grading periods from exastud'),
                'taxonomies' => new external_multiple_structure(new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'id'),
                    'title' => new external_value(PARAM_TEXT, 'name'),
                    'source' => new external_value(PARAM_TEXT, 'source'),
                ]), 'values'),
                'version' => new external_value(PARAM_FLOAT, 'exacomp version number in YYYYMMDDXX format'),
                'moodleversion' => new external_value(PARAM_FLOAT, 'moodle version number in YYYYMMDDXX format'),
                'release' => new external_value(PARAM_TEXT, 'plugin release x.x.x format'),
                'exaportactive' => new external_value(PARAM_BOOL, 'flag if exaportfolio should be active'),// Returns JSON content.
                'customlanguagefile' => new external_value(PARAM_TEXT, 'customlanguagefiel'), // Returns JSON content.
                'timeout' => new external_value(PARAM_INT, 'a timeout timer'),
                'show_overview' => new external_value(PARAM_BOOL, 'flag if "show overview" is active'),
                'show_eportfolio' => new external_value(PARAM_BOOL, 'flag if "show ePortfolio" is active'),
                'categories' => new external_multiple_structure(new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'id'),
                    'title' => new external_value(PARAM_TEXT, 'name'),
                    'source' => new external_value(PARAM_TEXT, 'source'),
                ]), 'values'),
                'assessment_verbose_lowerisbetter' => new external_value(PARAM_BOOL, 'flag if "The lower the Assessment, the better" is active'),
            ))),
        ));
    }

    /** Returns the user role of this user
     *
     * @param $userid
     * @return object
     */
    private static function get_user_role_common($userid) {
        $courses = static::get_courses($userid);
        foreach ($courses as $course) {
            $context = context_course::instance($course["courseid"]);
            $isTeacher = block_exacomp_is_teacher($context);
            if ($isTeacher) {
                return (object)["role" => BLOCK_EXACOMP_WS_ROLE_TEACHER];
            }
        }
        return (object)["role" => BLOCK_EXACOMP_WS_ROLE_STUDENT];
    }

    public static function login_parameters() {
        return new external_function_parameters(array(
            'app' => new external_value(PARAM_INT, 'app accessing this service (eg. dakora)'),
            'app_version' => new external_value(PARAM_INT, 'version of the app (eg. 4.6.0)'),
            'services' => new external_value(PARAM_INT, 'wanted webservice tokens (eg. exacomp,exaport)', VALUE_DEFAULT, 'moodle_mobile_app,exacompservices'),
        ));
    }

    /**
     * Returns description of method return values
     */
    public static function login_returns() {
        return new external_single_structure([
            'user' => static::dakora_get_user_information_returns(),
            'exacompcourses' => static::dakora_get_courses_returns(),
            //			'config' => static::dakora_get_config_returns(),
            'courseconfigs' => static::dakora_get_courseconfigs_returns(),
            'tokens' => new external_multiple_structure(new external_single_structure([
                'service' => new external_value(PARAM_TEXT, 'name of service'),
                'token' => new external_value(PARAM_TEXT, 'token of the service'),
            ]), 'requested tokens'),
        ]);
    }

    /**
     * webservice called through token.php
     *
     * @ws-type-read
     * @return array
     */
    public static function login() {
        return [
            'user' => static::dakora_get_user_information(),
            'exacompcourses' => static::dakora_get_courses(),
            //			'config' => static::dakora_get_config(), everything is included in courseconfigs
            'courseconfigs' => static::dakora_get_courseconfigs(),
            'tokens' => [],
        ];
    }

    public static function dakora_set_descriptor_visibility_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'descriptorid' => new external_value(PARAM_INT, 'id of descriptor'),
            'userid' => new external_value(PARAM_INT, 'id of user, 0 for current user'),
            'forall' => new external_value(PARAM_BOOL, 'for all users = true, for one user = false'),
            'visible' => new external_value(PARAM_BOOL, 'visibility for descriptor in current context'),
        ));
    }

    /**
     * set visibility for descriptor
     *
     * @ws-type-write
     * @param $courseid
     * @param $descriptorid
     * @param $userid
     * @param $forall
     * @param $visible
     * @return array
     */
    public static function dakora_set_descriptor_visibility($courseid, $descriptorid, $userid, $forall, $visible) {
        global $USER;
        static::validate_parameters(static::dakora_set_descriptor_visibility_parameters(), array(
            'courseid' => $courseid,
            'descriptorid' => $descriptorid,
            'userid' => $userid,
            'forall' => $forall,
            'visible' => $visible,
        ));

        if ($userid == 0 && !$forall) {
            $userid = $USER->id;
        }

        static::require_can_access_course_user($courseid, $userid);

        block_exacomp_set_descriptor_visibility($descriptorid, $courseid, $visible, $userid);

        return array('success' => true);
    }

    public static function dakora_set_descriptor_visibility_returns() {
        return new external_single_structure(array(
            'success' => new external_value(PARAM_BOOL, 'status of success, either true (1) or false (0)'),
        ));
    }

    public static function dakora_set_example_visibility_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'exampleid' => new external_value(PARAM_INT, 'id of example'),
            'userid' => new external_value(PARAM_INT, 'id of user, 0 for current user'),
            'forall' => new external_value(PARAM_BOOL, 'for all users = true, for one user = false'),
            'visible' => new external_value(PARAM_BOOL, 'visibility for example in current context'),
        ));
    }

    /**
     * set visibility for example
     *
     * @ws-type-write
     * @param $courseid
     * @param $exampleid
     * @param $userid
     * @param $forall
     * @param $visible
     * @return array
     */
    public static function dakora_set_example_visibility($courseid, $exampleid, $userid, $forall, $visible) {
        global $USER;
        static::validate_parameters(static::dakora_set_example_visibility_parameters(), array(
            'courseid' => $courseid,
            'exampleid' => $exampleid,
            'userid' => $userid,
            'forall' => $forall,
            'visible' => $visible,
        ));

        if ($userid == 0 && !$forall) {
            $userid = $USER->id;
        }

        static::require_can_access_course_user($courseid, $userid);

        block_exacomp_set_example_visibility($exampleid, $courseid, $visible, $userid);

        return array('success' => true);
    }

    public static function dakora_set_example_visibility_returns() {
        return new external_single_structure(array(
            'success' => new external_value(PARAM_BOOL, 'status of success, either true (1) or false (0)'),
        ));
    }

    public static function dakora_set_topic_visibility_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'topicid' => new external_value(PARAM_INT, 'id of topic'),
            'userid' => new external_value(PARAM_INT, 'id of user, 0 for current user'),
            'forall' => new external_value(PARAM_BOOL, 'for all users = true, for one user = false'),
            'visible' => new external_value(PARAM_BOOL, 'visibility for topic in current context'),
            'groupid' => new external_value(PARAM_INT, 'id of group', VALUE_DEFAULT, -1),
        ));
    }

    /**
     * set visibility for topic
     *
     * @ws-type-write
     * @param $courseid
     * @param $topicid
     * @param $userid
     * @param $forall
     * @param $visible
     * @return array
     */
    public static function dakora_set_topic_visibility($courseid, $topicid, $userid, $forall, $visible, $groupid = -1) {
        global $USER;
        static::validate_parameters(static::dakora_set_topic_visibility_parameters(), array(
            'courseid' => $courseid,
            'topicid' => $topicid,
            'userid' => $userid,
            'forall' => $forall,
            'visible' => $visible,
            'groupid' => $groupid,
        ));

        if ($userid == 0 && !$forall) {
            $userid = $USER->id;
        }

        static::require_can_access_course_user($courseid, $userid);

        if ($groupid != -1) {
            block_exacomp_set_topic_visibility_for_group($topicid, $courseid, $visible, $groupid);
        } else {
            block_exacomp_set_topic_visibility($topicid, $courseid, $visible, $userid);
        }

        return array('success' => true);
    }

    public static function dakora_set_topic_visibility_returns() {
        return new external_single_structure(array(
            'success' => new external_value(PARAM_BOOL, 'status of success, either true (1) or false (0)'),
        ));
    }

    public static function dakora_set_niveau_visibility_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'topicid' => new external_value(PARAM_INT, 'id of topic'),
            'userid' => new external_value(PARAM_INT, 'id of user, 0 for current user'),
            'forall' => new external_value(PARAM_BOOL, 'for all users = true, for one user = false'),
            'visible' => new external_value(PARAM_BOOL, 'visibility for topic in current context'),
            'groupid' => new external_value(PARAM_INT, 'id of group', VALUE_DEFAULT, -1),
            'niveauid' => new external_value(PARAM_INT, 'id of the descriptorniveau'),
        ));
    }

    /**
     * set visibility for topic
     *
     * @ws-type-write
     * @param $courseid
     * @param $topicid
     * @param $userid
     * @param $forall
     * @param $visible
     * @return array
     */
    public static function dakora_set_niveau_visibility($courseid, $topicid, $userid, $forall, $visible, $groupid = -1, $niveauid = null) {
        global $USER;
        static::validate_parameters(static::dakora_set_niveau_visibility_parameters(), array(
            'courseid' => $courseid,
            'topicid' => $topicid,
            'userid' => $userid,
            'forall' => $forall,
            'visible' => $visible,
            'groupid' => $groupid,
            'niveauid' => $niveauid,
        ));

        if ($userid == 0 && !$forall) {
            $userid = $USER->id;
        }

        static::require_can_access_course_user($courseid, $userid);

        //        if($groupid != -1){
        //            block_exacomp_set_topic_visibility_for_group($topicid, $courseid, $visible, $groupid);
        //        }else{
        block_exacomp_set_niveau_visibility($topicid, $courseid, $visible, $userid, $niveauid);
        //        }

        return array('success' => true);
    }

    public static function dakora_set_niveau_visibility_returns() {
        return new external_single_structure(array(
            'success' => new external_value(PARAM_BOOL, 'status of success, either true (1) or false (0)'),
        ));
    }

    public static function dakora_set_example_solution_visibility_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'exampleid' => new external_value(PARAM_INT, 'id of example'),
            'userid' => new external_value(PARAM_INT, 'id of user, 0 for current user'),
            'forall' => new external_value(PARAM_BOOL, 'for all users = true, for one user = false'),
            'visible' => new external_value(PARAM_BOOL, 'visibility for example in current context'),
        ));
    }

    /**
     * set visibility for example solutions
     *
     * @ws-type-write
     * @param $courseid
     * @param $exampleid
     * @param $userid
     * @param $forall
     * @param $visible
     * @return array
     */
    public static function dakora_set_example_solution_visibility($courseid, $exampleid, $userid, $forall, $visible) {
        global $USER;
        static::validate_parameters(static::dakora_set_example_solution_visibility_parameters(), array(
            'courseid' => $courseid,
            'exampleid' => $exampleid,
            'userid' => $userid,
            'forall' => $forall,
            'visible' => $visible,
        ));

        if ($userid == 0 && !$forall) {
            $userid = $USER->id;
        }

        static::require_can_access_course_user($courseid, $userid);

        block_exacomp_set_example_solution_visibility($exampleid, $courseid, $visible, $userid);

        return array('success' => true);
    }

    public static function dakora_set_example_solution_visibility_returns() {
        return new external_single_structure(array(
            'success' => new external_value(PARAM_BOOL, 'status of success, either true (1) or false (0)'),
        ));
    }

    public static function dakora_create_cross_subject_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'title' => new external_value(PARAM_TEXT, 'title of crosssubject'),
            'description' => new external_value(PARAM_TEXT, 'description of crosssubject'),
            'subjectid' => new external_value(PARAM_INT, 'id of subject crosssubject is assigned to'),
            'draftid' => new external_value(PARAM_INT, 'id of draft', VALUE_DEFAULT, 0),
            'groupcategory' => new external_value(PARAM_TEXT, 'name of groupcategory', VALUE_DEFAULT, ""),
        ));
    }

    public static function diggrplus_set_descriptor_visibility_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'ids' => new external_value(PARAM_TEXT, 'list of descriptorids, seperated by comma'),
            'userid' => new external_value(PARAM_INT, 'id of user, 0 for current user'),
            'forall' => new external_value(PARAM_BOOL, 'for all users = true, for one user = false'),
            'visible' => new external_value(PARAM_BOOL, 'visibility for descriptor in current context'),
        ));
    }

    /**
     * set visibility for descriptor
     *
     * @ws-type-write
     * @param $courseid
     * @param $descriptorid
     * @param $userid
     * @param $forall
     * @param $visible
     * @return array
     */
    public static function diggrplus_set_descriptor_visibility($courseid, $ids, $userid, $forall, $visible) {
        global $USER;
        static::validate_parameters(static::diggrplus_set_descriptor_visibility_parameters(), array(
            'courseid' => $courseid,
            'ids' => $ids,
            'userid' => $userid,
            'forall' => $forall,
            'visible' => $visible,
        ));

        if ($userid == 0 && !$forall) {
            $userid = $USER->id;
        }

        static::require_can_access_course_user($courseid, $userid);

        $descriptorids = explode(',', $ids);

        foreach ($descriptorids as $descriptorid) {
            block_exacomp_set_descriptor_visibility($descriptorid, $courseid, $visible, $userid);
        }

        return array('success' => true);
    }

    public static function diggrplus_set_descriptor_visibility_returns() {
        return new external_single_structure(array(
            'success' => new external_value(PARAM_BOOL, 'status of success, either true (1) or false (0)'),
        ));
    }

    public static function diggrplus_set_example_visibility_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'ids' => new external_value(PARAM_TEXT, 'list of exampleids, seperated by comma'),
            'userid' => new external_value(PARAM_INT, 'id of user, 0 for current user'),
            'forall' => new external_value(PARAM_BOOL, 'for all users = true, for one user = false'),
            'visible' => new external_value(PARAM_BOOL, 'visibility for example in current context'),
        ));
    }

    /**
     * set visibility for example
     *
     * @ws-type-write
     * @param $courseid
     * @param $id
     * @param $userid
     * @param $forall
     * @param $visible
     * @return array
     */
    public static function diggrplus_set_example_visibility($courseid, $ids, $userid, $forall, $visible) {
        global $USER;
        static::validate_parameters(static::diggrplus_set_example_visibility_parameters(), array(
            'courseid' => $courseid,
            'ids' => $ids,
            'userid' => $userid,
            'forall' => $forall,
            'visible' => $visible,
        ));

        if ($userid == 0 && !$forall) {
            $userid = $USER->id;
        }

        static::require_can_access_course_user($courseid, $userid);

        $exampleids = explode(',', $ids);

        foreach ($exampleids as $exampleid) {
            block_exacomp_set_example_visibility($exampleid, $courseid, $visible, $userid);
        }

        return array('success' => true);
    }

    public static function diggrplus_set_example_visibility_returns() {
        return new external_single_structure(array(
            'success' => new external_value(PARAM_BOOL, 'status of success, either true (1) or false (0)'),
        ));
    }

    public static function diggrplus_set_topic_visibility_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'ids' => new external_value(PARAM_TEXT, 'list of topicids, seperated by comma'),
            'userid' => new external_value(PARAM_INT, 'id of user, 0 for current user'),
            'forall' => new external_value(PARAM_BOOL, 'for all users = true, for one user = false'),
            'visible' => new external_value(PARAM_BOOL, 'visibility for topic in current context'),
            'groupid' => new external_value(PARAM_INT, 'id of group', VALUE_DEFAULT, -1),
        ));
    }

    /**
     * set visibility for topic
     *
     * @ws-type-write
     * @param $courseid
     * @param $topicid
     * @param $userid
     * @param $forall
     * @param $visible
     * @return array
     */
    public static function diggrplus_set_topic_visibility($courseid, $ids, $userid, $forall, $visible, $groupid = -1) {
        global $USER;
        static::validate_parameters(static::diggrplus_set_topic_visibility_parameters(), array(
            'courseid' => $courseid,
            'ids' => $ids,
            'userid' => $userid,
            'forall' => $forall,
            'visible' => $visible,
            'groupid' => $groupid,
        ));

        if ($userid == 0 && !$forall) {
            $userid = $USER->id;
        }

        static::require_can_access_course_user($courseid, $userid);

        $topicids = explode(',', $ids);

        if ($groupid != -1) {
            foreach ($topicids as $topicid) {
                block_exacomp_set_topic_visibility_for_group($topicid, $courseid, $visible, $groupid);
            }
        } else {
            foreach ($topicids as $topicid) {
                block_exacomp_set_topic_visibility($topicid, $courseid, $visible, $userid);
            }
        }
        return array('success' => true);
    }

    public static function diggrplus_set_topic_visibility_returns() {
        return new external_single_structure(array(
            'success' => new external_value(PARAM_BOOL, 'status of success, either true (1) or false (0)'),
        ));
    }

    /**
     * create new crosssubject
     *
     * @ws-type-write
     * @param $courseid
     * @param $title
     * @param $description
     * @param $subjectid
     * @param $draftid
     * @return array
     */
    public static function dakora_create_cross_subject($courseid, $title, $description, $subjectid, $draftid, $groupcategory) {
        global $USER;
        static::validate_parameters(static::dakora_create_cross_subject_parameters(), array(
            'courseid' => $courseid,
            'title' => $title,
            'description' => $description,
            'subjectid' => $subjectid,
            'draftid' => $draftid,
            'groupcategory' => $groupcategory,
        ));

        $userid = $USER->id;

        static::require_can_access_course($courseid);

        block_exacomp_require_teacher($courseid);

        if ($draftid > 0) {
            $crosssubjid = block_exacomp_save_drafts_to_course(array($draftid), $courseid);
            block_exacomp_edit_crosssub($crosssubjid, $title, $description, $subjectid, $groupcategory);
        } else {
            block_exacomp_create_crosssub($courseid, $title, $description, $userid, $subjectid, $groupcategory);
        }

        return array('success' => true);
    }

    public static function dakora_create_cross_subject_returns() {
        return new external_single_structure(array(
            'success' => new external_value(PARAM_BOOL, 'status of success, either true (1) or false (0)'),
        ));
    }

    public static function dakora_delete_cross_subject_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'crosssubjid' => new external_value(PARAM_INT, 'id of crosssubject'),
        ));
    }

    /**
     * delete cross subject
     *
     * @ws-type-write
     * @param $courseid
     * @param $crosssubjid
     * @return array
     */
    public static function dakora_delete_cross_subject($courseid, $crosssubjid) {
        global $USER;
        static::validate_parameters(static::dakora_delete_cross_subject_parameters(), array(
            'courseid' => $courseid,
            'crosssubjid' => $crosssubjid,
        ));

        $userid = $USER->id;

        static::require_can_access_course($courseid);

        block_exacomp_require_teacher($courseid);

        $return = block_exacomp_delete_crosssub($crosssubjid);

        return array('success' => $return);
    }

    public static function dakora_delete_cross_subject_returns() {
        return new external_single_structure(array(
            'success' => new external_value(PARAM_BOOL, 'status of success, either true (1) or false (0)'),
        ));
    }

    public static function dakora_edit_cross_subject_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'crosssubjid' => new external_value(PARAM_INT, 'id of crosssubject'),
            'title' => new external_value(PARAM_TEXT, 'title of crosssubject'),
            'description' => new external_value(PARAM_TEXT, 'description of crosssubject'),
            'subjectid' => new external_value(PARAM_INT, 'id of subject crosssubject is assigned to'),
            'groupcategory' => new external_value(PARAM_TEXT, 'name of groupcategory', VALUE_DEFAULT, ""),
        ));
    }

    /**
     * edit existing crosssubject
     *
     * @ws-type-write
     * @param $courseid
     * @param $crosssubjid
     * @param $title
     * @param $description
     * @param $subjectid
     * @return array
     */
    public static function dakora_edit_cross_subject($courseid, $crosssubjid, $title, $description, $subjectid, $groupcategory = "") {
        global $USER;
        static::validate_parameters(static::dakora_edit_cross_subject_parameters(), array(
            'courseid' => $courseid,
            'crosssubjid' => $crosssubjid,
            'title' => $title,
            'description' => $description,
            'subjectid' => $subjectid,
            'groupcategory' => $groupcategory,
        ));

        $userid = $USER->id;

        static::require_can_access_course($courseid);
        block_exacomp_require_teacher($courseid);

        block_exacomp_edit_crosssub($crosssubjid, $title, $description, $subjectid, $groupcategory);

        return array('success' => true);
    }

    public static function dakora_edit_cross_subject_returns() {
        return new external_single_structure(array(
            'success' => new external_value(PARAM_BOOL, 'status of success, either true (1) or false (0)'),
        ));
    }

    public static function dakora_get_cross_subject_drafts_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
        ));
    }

    /**
     * get available drafts
     *
     * @ws-type-read
     * @param $courseid
     * @return cross_subject[]
     */
    public static function dakora_get_cross_subject_drafts($courseid) {
        global $USER;
        static::validate_parameters(static::dakora_get_cross_subject_drafts_parameters(), array('courseid' => $courseid));

        $userid = $USER->id;

        block_exacomp_require_teacher($courseid);

        return block_exacomp_get_cross_subjects_drafts();
    }

    public static function dakora_get_cross_subject_drafts_returns() {
        return new external_multiple_structure(new external_single_structure(array(
            'id' => new external_value(PARAM_INT, 'id of crosssubjet draft'),
            'title' => new external_value(PARAM_TEXT, 'title of draft'),
            'description' => new external_value(PARAM_TEXT, 'description of draft'),
        )));
    }

    public static function dakora_get_subjects_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
        ));
    }

    /**
     * get subjects
     *
     * @ws-type-read
     * @param $courseid
     * @return array
     */
    public static function dakora_get_subjects($courseid) {
        global $USER;
        static::validate_parameters(static::dakora_get_subjects_parameters(), array('courseid' => $courseid));

        $userid = $USER->id;

        static::require_can_access_course($courseid);
        block_exacomp_require_teacher($courseid);

        $subjects = array();
        $default_sub = new stdClass();
        $default_sub->id = 0;
        $default_sub->title = block_exacomp_get_string('nocrosssubsub');
        $subjects[] = $default_sub;

        $subjects = array_merge($subjects, block_exacomp_get_subjects_by_course($courseid));

        return $subjects;
    }

    public static function dakora_get_subjects_returns() {
        return new external_multiple_structure(new external_single_structure(array(
            'id' => new external_value(PARAM_INT, 'id of subject'),
            'title' => new external_value(PARAM_TEXT, 'title of subject'),
        )));
    }

    public static function dakora_get_students_for_cross_subject_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'crosssubjid' => new external_value(PARAM_INT, 'id of crossssubj'),
        ));
    }

    /**
     * get_students_for_crosssubject
     *
     * @ws-type-read
     * @param $courseid
     * @param $crosssubjid
     * @return stdClass
     */
    public static function dakora_get_students_for_cross_subject($courseid, $crosssubjid) {
        global $DB;
        static::validate_parameters(static::dakora_get_students_for_cross_subject_parameters(), array(
            'courseid' => $courseid,
            'crosssubjid' => $crosssubjid,
        ));

        static::require_can_access_course($courseid);
        block_exacomp_require_teacher($courseid);

        $crosssub = $DB->get_record(BLOCK_EXACOMP_DB_CROSSSUBJECTS, array('id' => $crosssubjid));
        $students = block_exacomp_get_students_for_crosssubject($courseid, $crosssub);

        $coursestudents = static::dakora_get_students_and_groups_for_course($courseid);
        foreach ($coursestudents as $student) {
            if (array_key_exists($student->id, $students)) {
                $student->visible = 1;
            } else {
                $student->visible = 0;
            }
        }

        $return = new stdClass();
        $return->students = $coursestudents;
        $return->visible_forall = $crosssub->shared;

        return $return;
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function dakora_get_students_for_cross_subject_returns() {
        return new external_single_structure(array(
            'students' => new external_multiple_structure(new external_single_structure(array(
                'id' => new external_value(PARAM_INT, 'id of student'),
                'firstname' => new external_value(PARAM_TEXT, 'firstname of student'),
                'lastname' => new external_value(PARAM_TEXT, 'lastname of student'),
                'visible' => new external_value(PARAM_INT, 'visibility of crosssubject to student'),
            ))),
            'visible_forall' => new external_value(PARAM_INT, 'visibility of crosssubject to all students'),
        ));
    }

    public static function dakora_set_cross_subject_student_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'crosssubjid' => new external_value(PARAM_INT, 'id of crosssubject'),
            'userid' => new external_value(PARAM_TEXT, 'title of crosssubject'),
            'forall' => new external_value(PARAM_INT, '0 or 1'),
            'value' => new external_value(PARAM_INT, 'value 0 or 1'),
        ));
    }

    /**
     * set visibility for crosssubject and student
     *
     * @ws-type-write
     * @param $courseid
     * @param $crosssubjid
     * @param $userid
     * @param $forall
     * @param $value
     * @return array
     */
    public static function dakora_set_cross_subject_student($courseid, $crosssubjid, $userid, $forall, $value) {
        global $USER, $DB;
        static::validate_parameters(static::dakora_set_cross_subject_student_parameters(), array(
            'courseid' => $courseid,
            'crosssubjid' => $crosssubjid,
            'userid' => $userid,
            'forall' => $forall,
            'value' => $value,
        ));

        if ($userid == 0 && !$forall) {
            $userid = $USER->id;
        }

        static::require_can_access_course($courseid);
        block_exacomp_require_teacher($courseid);

        if ($userid == 0) {
            $crosssub = $DB->get_record(BLOCK_EXACOMP_DB_CROSSSUBJECTS, array('id' => $crosssubjid));
            if ($crosssub->shared != $value) {
                $crosssub->shared = $value;
                $DB->update_record(BLOCK_EXACOMP_DB_CROSSSUBJECTS, $crosssub);
            }

            $DB->delete_records(BLOCK_EXACOMP_DB_CROSSSTUD, array('crosssubjid' => $crosssubjid));

            return array('success' => true);
        }

        if (block_exacomp_student_crosssubj($crosssubjid, $userid) && $value == 0) {
            $DB->delete_records(BLOCK_EXACOMP_DB_CROSSSTUD, array('crosssubjid' => $crosssubjid, 'studentid' => $userid));

            return array('success' => true);
        }

        if (!block_exacomp_student_crosssubj($crosssubjid, $userid) && $value == 1) {
            $insert = new stdClass();
            $insert->studentid = $userid;
            $insert->crosssubjid = $crosssubjid;
            $DB->insert_record(BLOCK_EXACOMP_DB_CROSSSTUD, $insert);

            return array('success' => true);
        }

        if (block_exacomp_student_crosssubj($crosssubjid, $userid) && $value == 1 || !block_exacomp_student_crosssubj($crosssubjid, $userid) && $value == 0) {
            return array('success' => true);
        }

        return array('success' => false);
    }

    public static function dakora_set_cross_subject_student_returns() {
        return new external_single_structure(array(
            'success' => new external_value(PARAM_BOOL, 'status of success, either true (1) or false (0)'),
        ));
    }

    public static function dakora_set_cross_subject_descriptor_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'crosssubjid' => new external_value(PARAM_INT, 'id of crosssubject'),
            'descriptorid' => new external_value(PARAM_TEXT, 'title of crosssubject'),
            'value' => new external_value(PARAM_INT, 'value 0 or 1'),
        ));
    }

    /**
     * set descriptor crosssubject association
     *
     * @ws-type-write
     * @param $courseid
     * @param $crosssubjid
     * @param $descriptorid
     * @param $value
     * @return array
     */
    public static function dakora_set_cross_subject_descriptor($courseid, $crosssubjid, $descriptorid, $value) {
        global $USER, $DB;
        static::validate_parameters(static::dakora_set_cross_subject_descriptor_parameters(), array(
            'courseid' => $courseid,
            'crosssubjid' => $crosssubjid,
            'descriptorid' => $descriptorid,
            'value' => $value,
        ));

        static::require_can_access_course($courseid);
        block_exacomp_require_teacher($courseid);

        if ($value == 1) {
            block_exacomp_set_cross_subject_descriptor($crosssubjid, $descriptorid);

            return array('success' => true);
        }

        if ($value == 0) {
            block_exacomp_unset_cross_subject_descriptor($crosssubjid, $descriptorid);

            return array('success' => true);
        }

        return array('success' => false);
    }

    public static function dakora_set_cross_subject_descriptor_returns() {
        return new external_single_structure(array(
            'success' => new external_value(PARAM_BOOL, 'status of success, either true (1) or false (0)'),
        ));
    }

    public static function dakora_dismiss_oldgrading_warning_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'descriptorid' => new external_value(PARAM_INT, 'id of descriptor'),
            'studentid' => new external_value(PARAM_INT, 'id of student'),
        ));
    }

    /**
     * set descriptor crosssubject association
     *
     * @ws-type-write
     * @param $courseid
     * @param $descriptorid
     * @param $studentid
     * @return array
     */
    public static function dakora_dismiss_oldgrading_warning($courseid, $descriptorid, $studentid) {
        global $USER, $DB;

        static::validate_parameters(static::dakora_dismiss_oldgrading_warning_parameters(), array(
            'courseid' => $courseid,
            'descriptorid' => $descriptorid,
            'studentid' => $studentid,
        ));

        static::require_can_access_course($courseid);
        block_exacomp_require_teacher($courseid);

        //block_exacomp_set_descriptor_grading_timestamp($courseid,$descriptorid,$studentid);
        block_exacomp_unset_descriptor_gradingisold($courseid, $descriptorid, $studentid);

        return array('success' => true);
    }

    public static function dakora_dismiss_oldgrading_warning_returns() {
        return new external_single_structure(array(
            'success' => new external_value(PARAM_BOOL, 'status of success, either true (1) or false (0)'),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function dakora_send_message_parameters() {
        return new external_function_parameters(array(
            'messagetext' => new external_value(PARAM_TEXT, 'text of message'),
            'userfrom' => new external_value(PARAM_INT, 'id of user that sends the message'),
            'userto' => new external_value(PARAM_INT, 'id of user message is sent to'),
        ));
    }

    /**
     * send message
     *
     * @ws-type-write
     * @return
     */
    public static function dakora_send_message($messagetext, $userfrom, $userto) {
        global $USER;
        if ($userfrom == 0) {
            $userfrom = $USER->id;
        }

        static::validate_parameters(static::dakora_send_message_parameters(), array(
            'messagetext' => $messagetext,
            'userfrom' => $userfrom,
            'userto' => $userto,
        ));

        $timecreated = time();
        block_exacomp_send_message($userfrom, $userto, $messagetext, date("D, d.m.Y", $timecreated), date("H:s", $timecreated), true);

        return array('success' => true);
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function dakora_send_message_returns() {
        return new external_single_structure(array(
            'success' => new external_value(PARAM_BOOL, 'status'),
        ));
    }

    /**
     * get all examples associated with any subject, topic or descriptor in any course for user
     * if compid or comptype are -1, get all examples for all courses
     *
     * @param int $userid
     * @param bool $compid
     * @param bool $comptype
     * @param int $courseid ---> use if you want to reduce the results to only the selected course. Otherwise, all courses are used.
     */
    private static function block_exacomp_get_examples_for_competence_and_user($userid = null, $compid = -1, $comptype = -1, $wstoken = null, $search = "", $niveauid = -1, $status = "", $courseid = -1) {
        global $DB;
        // Maybe better performance with join on user_enrolments table?
        //    if ($isTeacher) {
        //        $courses = block_exacomp_get_courses_of_teacher($userid);
        //    } else {
        //        $courses = block_exacomp_get_courses_of_student($userid);
        //    }

        // TODO: To avoid code duplication I used many existing functions. But this is by far not optimal for performance. Should I change this to sql-queries?
        $examples = array();
        if ($compid == -1 || $comptype == -1) { // return ALL examples.. no niveauid filter possible
            // TODO: checks so a student cannot hack this and view another student's items

            // Only use the course the teacher is interested in (e.g. a student could be in 5 courses, but the teacher only wants to see the results of the student in his course
            if ($courseid != -1) {
                $courses = static::get_courses($userid);
                $courses = array_filter($courses, function($course) use ($courseid) {
                    return $course["courseid"] == $courseid;
                });
            } else {
                $courses = static::get_courses($userid); // this is better than enrol_get_users_courses($userid);, because it checks for existance of exabis Blocks as well as for visibility
            }

            foreach ($courses as $course) {
                //                if($course->visible){
                $courseExamples = block_exacomp_get_examples_by_course($course["courseid"], true, $search, true, $userid); // TODO: duplicates?
                foreach ($courseExamples as $example) {
                    static::block_excomp_get_example_details($example, $course["courseid"], false);
                    // checkQuiz = false since we never use it in diggprlus (SO FAR!)
                }
                $examples += $courseExamples;
                //                }
            }
        } else if ($comptype == BLOCK_EXACOMP_TYPE_SUBJECT) {
            //Get ALL examples, then only use the ones of the correct subject.
            /* Special Case: Same Subject is used in two courses
             * one courses uses 3 topics, the other course 5 topics
             * examples of all 5 topics will be returned, making this function unsuitable for getting examples of a course
             * ==> use $courseid
             */
            $courses = static::get_courses($userid); // this is better, because it checks for existance of exabis Blocks as well as for visibility

            if ($courseid != -1) {
                $courses = array_filter($courses, function($course) use ($courseid) {
                    return $course["courseid"] == $courseid;
                });
            }

            foreach ($courses as $course) {
                $courseExamples = block_exacomp_get_examples_by_course($course["courseid"], true, $search, true, $userid); // TODO: duplicates?
                foreach ($courseExamples as $key => $example) {
                    $exampleSubjects = block_exacomp_get_subjects_by_example($example->id);
                    if (!in_array($compid, $exampleSubjects)) {
                        //                    if($compid != $example->subjectid){ // more than one subjectid ==> different check!
                        unset($courseExamples[$key]);
                    } else {
                        if ($niveauid != -1) {
                            $exampleNiveaus = block_exacomp_get_niveaus_by_example($example->id);
                            if (!in_array($niveauid, $exampleNiveaus)) {
                                unset($courseExamples[$key]);
                                continue;
                            }
                        }
                        static::block_excomp_get_example_details($example, $course["courseid"], false);
                    }
                }

                $examples += $courseExamples;
            }

            //This is a solution that "works", but is very slow. Slower than finding ALL examples of a user
            //            //Get all topics in this subject, then get all information of topics
            //
            //            //get the course in order to be able to use next functions
            //            $courseids = block_exacomp_get_courseids_by_subject($compid); // subject can be in more than one, I just need any course for the next function --> room for optimization! TODO
            //            $subject = block_exacomp_get_subject_by_subjectid($compid);
            //
            //            $topics = block_exacomp_get_topics_by_subject($courseids[0],$compid);
            //
            //
            //            foreach($topics as $topic){
            //                $descriptors = block_exacomp_get_descriptors_by_topic($courseids[0], $topic->id); // this only gets parents
            //
            //                foreach($descriptors as $descriptor){
            //                    $childdescriptors = block_exacomp_get_child_descriptors($descriptor,$courseids[0]);
            //                    // niveauid and cattitle of the PARENT descriptor objects contain the LFS information --> add that information to the childdescriptors as well
            //                    foreach($childdescriptors as $child){
            //                        $child->niveauid = $descriptor->niveauid;
            //                        $child->cattitle = $descriptor->cattitle;
            //                    }
            //                    $descriptors += $childdescriptors;
            //                }
            //
            //                foreach($descriptors as $descriptor){
            //                    $descriptorWithExamples = block_exacomp_get_examples_for_descriptor($descriptor->id,null,true,$courseids[0], null, null, null, $search);
            //                    // niveauid and cattitle of the descriptor objects contain the LFS information --> add that information to the example
            //                    foreach($descriptorWithExamples->examples as $example){
            //                        $example = static::block_excomp_get_example_details($example, $example->courseid);
            //                        $example->subjecttitle = $subject->title;
            //                        $example->subjectid = $subject->id;
            //                        $example->topictitle = $topic->title;
            //                        $example->topicid = $topic->id;
            //                        $example->niveauid = $descriptor->niveauid;
            //                        $example->niveautitle = $descriptor->cattitle;
            //                    }
            //                    $examples += $descriptorWithExamples->examples;
            //                }
            //
            //            }
        } else if ($comptype == BLOCK_EXACOMP_TYPE_TOPIC) {
            // get topic and subject information:
            $sql = 'SELECT topic.title as topictitle, subj.title as subjecttitle, topic.id as topicid, subj.id as subjectid
                  FROM {block_exacomptopics} topic
                    JOIN {block_exacompsubjects} subj ON topic.subjid = subj.id
                  WHERE topic.id = ?';
            $information = $DB->get_record_sql($sql, array($compid));

            if ($courseid == -1) {
                $courseids = block_exacomp_get_courseids_by_topic($compid); // topic can be in more than one course, use one of those courses, since it does not matter for the descriptors
                // only use courseids where this user is enrolled, since it DOES matter for the examples
                //there can be examples in one course, but not in the other, even though it is the same subject
                $usercourses = array_keys(enrol_get_all_users_courses($userid));
                $courseids = array_filter($courseids, function($courseid) use ($usercourses) {
                    return in_array($courseid, $usercourses);
                });
                $courseid = $courseids[array_key_first($courseids)];
                // TODO: $courseids should now only contain ONE course. Otherwise, this means, that 1 student is in 2 courses that have the SAME Subject ---> Problem, but should never occur
            }
            $descriptors = block_exacomp_get_descriptors_by_topic($courseid, $compid, false, true, true); // this only gets parents

            //Ignore childdescriptors for diggrplus   not anymore 02.07.2021
            foreach ($descriptors as $descriptor) {
                $childdescriptors = block_exacomp_get_child_descriptors($descriptor, $courseid, false, null, true, true, true);
                // niveauid and cattitle of the PARENT descriptor objects contain the LFS information --> add that information to the childdescriptors as well
                foreach ($childdescriptors as $child) {
                    $child->niveauid = $descriptor->niveauid;
                    $child->cattitle = $descriptor->cattitle;
                }
                $descriptors += $childdescriptors;
            }

            foreach ($descriptors as $key => $descriptor) {
                if ($niveauid != -1) {
                    if ($descriptor->niveauid != $niveauid) {
                        unset($descriptors, $key);
                        continue;
                    }
                }
                $descriptorWithExamples = block_exacomp_get_examples_for_descriptor($descriptor->id, null, true, $courseid, true, true, null, $search);
                // niveauid and cattitle of the descriptor objects contain the LFS information --> add that information to the example
                foreach ($descriptorWithExamples->examples as $example) {
                    $example = static::block_excomp_get_example_details($example, $example->courseid, false);
                    unset($example->descriptor); // this information is not needed and leads to problem when sorting, because it loops example->descriptor->example->etc
                    $example->subjecttitle = $information->subjecttitle;
                    $example->subjectid = $information->subjectid;
                    $example->topictitle = $information->topictitle;
                    $example->topicid = $information->topicid;
                    $example->niveauid = $descriptor->niveauid;
                    $example->niveautitle = $descriptor->cattitle;
                }
                $examples += $descriptorWithExamples->examples;
            }
        } else if ($comptype == BLOCK_EXACOMP_TYPE_DESCRIPTOR) {
            $courseids = block_exacomp_get_courseids_by_descriptor($compid); // descriptor can be in more than one, I just need any course for the next function --> room for optimization!
            $descriptorWithExamples = block_exacomp_get_examples_for_descriptor($compid, null, true, $courseids[0], true, true, null, $search);
            $examples = $descriptorWithExamples->examples;

            // get topic and subject information:
            $sql = 'SELECT topic.title as topictitle, subj.title as subjecttitle, topic.id as topicid, subj.id as subjectid
                  FROM {block_exacompdescriptors} d
                    JOIN {block_exacompdescrtopic_mm} desctop ON desctop.descrid = d.id
                    JOIN {block_exacomptopics} topic ON topic.id = desctop.topicid
                    JOIN {block_exacompsubjects} subj ON topic.subjid = subj.id
                  WHERE d.id = ?';
            $information = $DB->get_record_sql($sql, array($compid));

            foreach ($examples as $example) {
                $example = static::block_excomp_get_example_details($example, $example->courseid, false); // TODO: for now use this to avoid code duplication. But maybe for performace use custom function
                $example->subjecttitle = $information->subjecttitle;
                $example->subjectid = $information->subjectid;
                $example->topictitle = $information->topictitle;
                $example->topicid = $information->topicid;
                //                $example->niveauid = $information->niveauid;
                //                $example->niveautitle = $information->niveautitle;
            }
        }

        //remove examples that are not visible
        foreach ($examples as $key => $example) {
            if (!block_exacomp_is_example_visible($example->courseid, $example, $userid)) {
                unset($examples[$key]);
            }
        }

        // TODO: most of the time is lost in this mapping
        // add one layer of depth to structure and add items to example. Also get more information for the items (e.g. files)
        $examplesAndItems = array_map(function($example) use ($userid, $wstoken, $DB, $comptype, $courseid) {
            $objDeeper = new stdClass();

            // if ANY student has submitted anything to this example: check for every student
            // if no submission has ever been made: don't bother to even check
            // enormous speedup for new installations. Slower, the more submissions there are.
            if ($example->hassubmissions) {
                $item = current(block_exacomp_get_items_for_competence($userid, $example->id, BLOCK_EXACOMP_TYPE_EXAMPLE, "", -1, "", $courseid)); //there will be only one item ==> current(); TODO: This takes up most of the time
            }

            if ($item) {
                $item = static::block_exacomp_get_item_details($item, $userid, $wstoken); // TODO: is this needed? much information is already there
                $objDeeper->item = $item;
                $objDeeper->timemodified = $item->timemodified;
            } else {
                $objDeeper->timemodified = "0"; // timemodified set to a very long time ago, for sorting
            }

            // Fixing HTML-Tag error in return value for webservices
            // In some very olf Epop competence grids, there is HTML in the description. If this is the case --> just delete it
            if (strpos($example->description, "<!doctype html>") !== false) {
                $example->description = "";
            } else {
                $example->description = static::custom_htmltrim($example->description);
                $example->description = strip_tags($example->description);
                $example->title = static::custom_htmltrim($example->title);
                $example->title = strip_tags($example->title);
            }

            // Adding annotationinformation    TODO: Again: What IF the user has the same subject in two different courses.. which courseid to take?
            // check if it is already there (done for "all" and "subject" so save computation time
            if (!property_exists($example, "annotation")) {
                $example->annotation = $DB->get_field(BLOCK_EXACOMP_DB_EXAMPLE_ANNOTATION, 'annotationtext', array('exampleid' => $example->id, 'courseid' => $courseid));
            }

            // Adding the evaluation information if it did not get queried before when getting the examples
            // right now this is the case if "topic" is selected. For "all" and "subject" the evaluation is queried before ==> faster
            if (!(property_exists($example, "teacher_evaluation") || property_exists($example, "student_evaluation"))) {
                $exampleEvaluation = $DB->get_record(BLOCK_EXACOMP_DB_EXAMPLEEVAL, array("studentid" => $userid, "courseid" => $example->courseid, "exampleid" => $example->id), "teacher_evaluation, student_evaluation");
                $example->teacher_evaluation = $exampleEvaluation->teacher_evaluation;
                $example->student_evaluation = $exampleEvaluation->student_evaluation;
            }

            $objDeeper->courseid = $example->courseid;
            $objDeeper->example = $example;
            $objDeeper->subjecttitle = $example->subjecttitle;
            $objDeeper->subjectid = $example->subjectid;
            $objDeeper->topictitle = static::custom_htmltrim($example->topictitle);
            $objDeeper->topicid = $example->topicid;
            if ($comptype == BLOCK_EXACOMP_TYPE_DESCRIPTOR) {
                $objDeeper->niveauid = -1;
                $objDeeper->niveautitle = "";
            } else {
                $objDeeper->niveauid = $example->niveauid;
                $objDeeper->niveautitle = $example->niveautitle;
            }
            return $objDeeper;
        }, $examples);

        return $examplesAndItems;
    }

    private static function startsWith($string, $startString) {
        $len = strlen($startString);
        return (substr($string, 0, $len) === $startString);
    }

    // for diggrplus webservices and get_example_by_id (which is used in diggr?)
    private static function block_excomp_get_example_details($example, $courseid, $checkQuiz = true) {
        global $DB;

        if ($checkQuiz) {
            //da jetzt pr�fen ob Quiz pr�fen
            $quizDB = $DB->get_records_sql("SELECT q.id, q.name, q.grade
							FROM {" . BLOCK_EXACOMP_DB_EXAMPLES . "} ca
							JOIN {course_modules} cm ON ca.activityid = cm.id
							JOIN {modules} m ON cm.module = m.id
							JOIN {quiz} q ON cm.instance = q.id
							WHERE m.name = 'quiz' AND  ca.id = ?
							", array(
                    $example->id,
                )
            );

            $example->quiz = new stdClass ();
            foreach ($quizDB as $quiz) {
                $example->quiz->quizid = $quiz->id;
                $example->quiz->quiz_title = $quiz->name;
                $example->quiz->quiz_grade = $quiz->grade;
            }
            if ($example->quiz->quizid == null) {
                $example->quiz->quizid = -1;
                $example->quiz->quiz_title = " ";
                $example->quiz->quiz_grade = 0.0;
            }
        }

        // The data that is queried here is cached for example for diggrplus.
        // get_teacher_examples_and_items uses this function for every student for every example
        // the examples should only be queried once. If the example is already known, the $cachedExampleDatas are used.
        static $cachedExampleDatas = array();
        if (isset($cachedExampleDatas[$courseid][$example->id])) {
            $exampleData = $cachedExampleDatas[$courseid][$example->id];
        } else {
            $exampleData = $cachedExampleDatas[$courseid][$example->id] = (object)[];

            $exampleData->hassubmissions = !!$DB->get_records(BLOCK_EXACOMP_DB_ITEM_MM, array('exacomp_record_id' => $example->id));

            //New solution: filenameS instead of filename... keep both for compatibilty for now   RW
            // Newer solution: an array of "task" objects: taskfiles. These object contain all the information: the url is extended for the position value, so this does not have to be done in Dakora
            // To not break Dakora, the old system of taskfileurl + taskfilenames + taskfilecount will be kept
            $exampleData->taskfiles = [];

            $exampleData->taskfilecount = block_exacomp_get_number_of_files($example, 'example_task');
            $exampleData->taskfilenames = "";
            $exampleData->taskfileurl = "";
            for ($i = 0; $i < $exampleData->taskfilecount; $i++) {
                if ($file = block_exacomp_get_file($example, 'example_task', $i)) {
                    $exampleData->taskfileurl = static::get_webservice_url_for_file($file, $courseid)->out(false);
                    $exampleData->taskfilenames .= $file->get_filename() . ',';

                    //new solution for the taskfiles array
                    $exampleData->taskfiles[$i] = new stdClass();
                    $exampleData->taskfiles[$i]->url = $exampleData->taskfileurl = static::get_webservice_url_for_file($file, $courseid, $i)->out(false);
                    $exampleData->taskfiles[$i]->name = $file->get_filename();
                    $exampleData->taskfiles[$i]->type = $file->get_mimetype();
                } else {
                    $exampleData->taskfileurl = "";
                    $exampleData->taskfilenames = "";
                }
            }

            //		if ($file = block_exacomp_get_file($example, 'example_task')) {
            //			$example->taskfileurl = static::get_webservice_url_for_file($file, $courseid)->out(false);
            //            $example->taskfilename = $file->get_filename();
            //		} else {
            //			$example->taskfileurl = null;
            //			$example->taskfilename = null;
            //		}

            // fall back to old fields
            // check if it is an h5pactivity: if it cointains "/mod/h5pactivity/view.php" then it is ==> overwrite with our link to the exacomp h5pactivity view link
            //if(str_contains($example->externaltask, "/mod/h5pactivity/view.php")){ TODO works only for PHP8
            //    echo "asdf";
            //}
            // h5p
            if (strpos($example->externaltask, "/mod/h5pactivity/view.php")) {
                $exampleData->externaltask_embedded = str_replace("mod/h5pactivity/view.php", "blocks/exacomp/mod_h5p_embedded.php", $example->externaltask);
            }
            // hvp --> the plugin that is mostly used
            if (strpos($example->externaltask, "/mod/hvp/view.php")) {
                $exampleData->externaltask_embedded = str_replace("mod/hvp/view.php", "mod/hvp/embed.php", $example->externaltask);
            }

            $exampleData->externalurl = $example->externalurl;
            $exampleData->externaltask = $example->externaltask;
            $exampleData->task = $example->task;

            if (!$exampleData->externalurl && $exampleData->task) {
                $exampleData->externalurl = $exampleData->task;
                $exampleData->task = "";
            }

            if ($exampleData->externaltask) {
                $exampleData->externaltask = static::format_url($exampleData->externaltask);
            }

            if ($exampleData->externalurl) {
                $exampleData->externalurl = static::format_url($exampleData->externalurl);
            }

            if ($exampleData->externaltask_embedded) {
                $exampleData->externaltask_embedded = static::format_url($exampleData->externaltask_embedded);
            }

            // TODO: task field still needed in exacomp?
            if (!$exampleData->task) {
                $exampleData->task = $exampleData->taskfileurl;
            }
            if (!$exampleData->task) {
                $exampleData->task = $exampleData->externalurl;
            }

            $exampleData->solution = "";
            $exampleData->solutionfilename = "";
            $exampleData->solution_visible = 0;
            $solution = block_exacomp_get_file($example, 'example_solution');

            if ($solution) {
                $exampleData->solution = (string)static::get_webservice_url_for_file($solution, $courseid)->out(false);
                $exampleData->solutionfilename = $solution->get_filename();
            } else if ($example->externalsolution) {
                $exampleData->solution = $example->externalsolution;
            }

            // $example->description = strip_tags($example->description);
            // $example->description = static::custom_htmltrim($example->description);

            // complete file
            $completefile = block_exacomp_get_file($example, 'example_completefile');
            if ($completefile) {
                $exampleData->completefile = (string)static::get_webservice_url_for_file($completefile, $courseid)->out(false);
                $exampleData->completefilefilename = $completefile->get_filename();
            }

        }

        foreach ($exampleData as $key => $value) {
            $example->{$key} = $value;
        }
        //        $example = (object)array_merge((array)$example, (array)$exampleData);

        return $example;
    }

    // Used for diggrplus
    private static function block_exacomp_get_item_details($item, $userid, $wstoken) {
        global $CFG;

        $item->file = "";
        $item->isimage = false;
        $item->filename = "";
        $item->effort = strip_tags($item->intro);
        $item->teachervalue = isset ($item->teachervalue) ? $item->teachervalue : 0;
        $item->studentvalue = isset ($item->studentvalue) ? $item->studentvalue : 0;
        $item->status = isset ($item->status) ? $item->status : 0;

        if ($item->type == 'file') {

            // Stattdessen: block_exaport_get_item_files ??    Im Dakora webservice wird das verwendet.

            // TODO: move code into exaport\api
            require_once $CFG->dirroot . '/blocks/exaport/inc.php';

            $item->userid = $userid;
            // dont' use block_exaport_get_item_files, because this can also return only one file!
            if ($files = block_exaport_get_files($item, 'item_file')) {
                $studentfiles = [];
                foreach ($files as $fileindex => $file) {
                    $fileurl = $CFG->wwwroot . "/blocks/exaport/portfoliofile.php?" . "userid=" . $userid . "&itemid=" . $item->id . "&wstoken=" . $wstoken . "&inst=" . $fileindex .
                        // used only that file links are unique
                        '&contenthash=' . $file->get_contenthash();
                    $filedata['id'] = $file->get_id();
                    $filedata['file'] = $fileurl;
                    $filedata['mimetype'] = $file->get_mimetype();
                    $filedata['filename'] = $file->get_filename();
                    $filedata['isimage'] = $file->is_valid_image();
                    $filedata['fileindex'] = $fileindex;
                    $studentfiles[] = $filedata;
                }
                $item->studentfiles = $studentfiles;
            }
        }

        $item->studentcomment = '';
        $item->teachercomment = '';

        $itemcomments = api::get_item_comments($item->id);
        $timemodified_compare = 0; //used for finding the most recent comment to display it in Dakora
        $timemodified_compareTeacher = 0;

        // teacher comment: last comment from any teacher in the course the item was submited
        // TODO: maybe this is also deprecated, and the code from the Dakora Webservices (dakora_get_example_information)
        // would be better. E.g. submitting files as a teacher is possible in Dakora, not in Diggr and not in Diggrplus for now

        //        foreach ($itemcomments as $itemcomment) {
        //            if ($userid == $itemcomment->userid && $itemcomment->timemodified > $timemodified_compare) { //Studentcomment
        //                $item->studentcomment = $itemcomment->entry;
        //                $timemodified_compare = $itemcomment->timemodified;
        //            } else { // teachercomment   // } elseif (true) { // TODO: check if is teacher? should not matter, everyone is allowed read teachercomment
        //                if ($item->courseid && block_exacomp_is_teacher($item->courseid, $itemcomment->userid)) {
        //                    // dakora / exacomp teacher
        //                    $item->teachercomment = $itemcomment->entry;
        //                } elseif (block_exacomp_is_external_trainer_for_student($itemcomment->userid, $item->userid)) {
        //                    // elove teacher
        //                    $item->teachercomment = $itemcomment->entry;
        //                }
        //            }
        //        }

        // TODO: there is no block_exacomp_is_external_trainer_for_student() check, but is this needed for diggrplus or did we get rid of this externalteacher mechanic?
        foreach ($itemcomments as $itemcomment) {
            if ($userid == $itemcomment->userid) {
                if ($itemcomment->timemodified > $timemodified_compare) {
                    $item->studentcomment = $itemcomment->entry;
                    $timemodified_compare = $itemcomment->timemodified;
                }
            } else if (true) { // TODO: check if is teacher?
                if ($itemcomment->timemodified > $timemodified_compareTeacher) {
                    $item->teachercomment = $itemcomment->entry;
                    // TODO: files for teachercomment allowed in diggrplus?

                    //                    if ($itemcomment->file) { //the most recent file is being kept, so if there is a newer comment without a file, the last file is still shown
                    //                        $tFile = $itemcomment->file;
                    //                        $fileurl = (string)new moodle_url("/blocks/exaport/portfoliofile.php", [
                    //                            'userid' => $userid,
                    //                            'itemid' => $item->id,
                    //                            'commentid' => $itemcomment->id,
                    //                            'wstoken' => static::wstoken(),
                    //                        ]);
                    //                        $teacherfile = [
                    //                            'file' => $fileurl,
                    //                            'mimetype' => $tFile->get_mimetype(),
                    //                            'filename' => $tFile->get_filename(),
                    //                            'fileindex' => $tFile->get_contenthash(),
                    //                        ];
                    //                        $item->teacherfile = $teacherfile;
                    //                    }
                    $timemodified_compareTeacher = $itemcomment->timemodified;
                }
            }
        }

        return $item;
    }

    /**
     * helper function to use same code for 2 ws
     */
    private static function get_descriptor_details_private($courseid, $descriptorid, $userid, $forall, $crosssubjid) {
        global $DB, $USER;
        //copied from old get_descriptor_details so i can use it in get_descriptor_details and get_descriptors_details
        $descriptor = $DB->get_record(BLOCK_EXACOMP_DB_DESCRIPTORS, array('id' => $descriptorid));
        $descriptor_topic_mm = $DB->get_record(BLOCK_EXACOMP_DB_DESCTOPICS, array('descrid' => $descriptor->id));
        $descriptor->topicid = $descriptor_topic_mm->topicid;

        $descriptor_return = new stdClass();
        $descriptor_return->descriptorid = $descriptorid;
        $descriptor_return->parentid = $descriptor->parentid;
        $selected_categories = $DB->get_records(BLOCK_EXACOMP_DB_DESCCAT, array("descrid" => $descriptorid), "", "catid");
        if ($selected_categories) {
            $descriptor_return->categories = implode(',', array_keys($selected_categories));
        } else {
            $descriptor_return->categories = '';
        }
        if ($selected_categories) {
            $categoryTitlesRes = $DB->get_records_sql('SELECT c.title, c.title as tmp
	                                                        FROM {' . BLOCK_EXACOMP_DB_CATEGORIES . '} c
	                                                        WHERE c.id IN (' . implode(',', array_keys($selected_categories)) . ')');
            $descCategories = '. ' . get_string('dakora_niveau_after_descriptor_title', 'block_exacomp') . ': ' . implode(', ', array_keys($categoryTitlesRes));
            $descriptor->title = rtrim($descriptor->title, '.');
        } else {
            $descCategories = '';
        }
        $descriptor_return->descriptortitle = static::custom_htmltrim($descriptor->title) . $descCategories;
        $descriptor_return->teacherevaluation = -1;
        $descriptor_return->additionalinfo = null;
        $descriptor_return->evalniveauid = null;
        $descriptor_return->timestampteacher = 0;
        $descriptor_return->reviewerid = 0;
        $descriptor_return->reviewername = null;

        if (!$forall) {
            if ($grading = block_exacomp_get_comp_eval($courseid, BLOCK_EXACOMP_ROLE_TEACHER, $userid, BLOCK_EXACOMP_TYPE_DESCRIPTOR, $descriptorid)) {
                $descriptor_return->teacherevaluation = ($grading->value !== null) ? $grading->value : -1;
                $descriptor_return->additionalinfo = $grading->additionalinfo;
                $descriptor_return->evalniveauid = $grading->evalniveauid;
                $descriptor_return->timestampteacher = $grading->timestamp;
                $descriptor_return->reviewerid = $grading->reviewerid;

                if (block_exacomp_is_teacher($courseid)) {
                    $descriptor_return->gradinghistory = $grading->gradinghistory;
                }

                //Reviewername finden
                $reviewerid = $grading->reviewerid;
                $reviewerTeacherFirstname = $DB->get_field('user', 'firstname', array('id' => $reviewerid));
                $reviewerTeacherLastname = $DB->get_field('user', 'lastname', array('id' => $reviewerid));
                $reviewerTeacherUsername = $DB->get_field('user', 'username', array('id' => $reviewerid));
                if ($reviewerTeacherFirstname != null && $reviewerTeacherLastname != null) {
                    $reviewername = $reviewerTeacherFirstname . ' ' . $reviewerTeacherLastname;
                } else {
                    $reviewername = $reviewerTeacherUsername;
                }
                $descriptor_return->reviewername = $reviewername;
            }

            /*this is probably very ineffective because it has to be done for every descriptor*/
            if (block_exacomp_is_dakora_teacher() && block_exacomp_get_settings_by_course($courseid)->isglobal) {
                $descriptor_return->globalgradings = block_exacomp_get_globalgradings_single($descriptorid, $userid, BLOCK_EXACOMP_TYPE_DESCRIPTOR);
            }

        }

        $descriptor_return->studentevaluation = -1;
        $descriptor_return->timestampstudent = 0;
        if (!$forall) {
            if ($grading = block_exacomp_get_comp_eval($courseid, BLOCK_EXACOMP_ROLE_STUDENT, $userid, BLOCK_EXACOMP_TYPE_DESCRIPTOR, $descriptorid)) {
                $descriptor_return->studentevaluation = ($grading->value !== null) ? $grading->value : -1;
                $descriptor_return->timestampstudent = $grading->timestamp;
            }

        }

        $descriptor_return->numbering = block_exacomp_get_descriptor_numbering($descriptor);

        $descriptor_return->niveautitle = "";
        $descriptor_return->niveauid = 0;
        if ($descriptor->niveauid) {
            $niveau = $DB->get_record(BLOCK_EXACOMP_DB_NIVEAUS, array('id' => $descriptor->niveauid));
            $descriptor_return->niveautitle = static::custom_htmltrim($niveau->title);
            $descriptor_return->niveauid = $niveau->id;
        }

        $childsandexamples = static::get_descriptor_children($courseid, $descriptorid, $userid, $forall, $crosssubjid, true);

        $descriptor_return->children = $childsandexamples->children;

        // summary for children gradings
        $grading_scheme = block_exacomp_get_grading_scheme($courseid) + 1;

        $number_evalniveaus = 1;
        if (block_exacomp_use_eval_niveau($courseid)) {
            $number_evalniveaus = 4;
        }

        $children_teacherevaluation = array();
        for ($i = 0; $i < $number_evalniveaus; $i++) {
            $children_teacherevaluation[$i] = array_fill(0, $grading_scheme, 0);
        }

        $children_studentevaluation = array_fill(0, $grading_scheme, 0);

        foreach ($childsandexamples->children as $child) {
            if ($child->teacherevaluation > -1) {
                $children_teacherevaluation[($child->evalniveauid > 0) ? $child->evalniveauid : 0][$child->teacherevaluation]++;
            }
            if ($child->studentevaluation > -1) {
                $children_studentevaluation[$child->studentevaluation]++;
            }
        }

        $childrengradings = new stdClass();
        $childrengradings->teacher = array();
        $childrengradings->student = array();

        foreach ($children_teacherevaluation as $niveauid => $gradings) {
            foreach ($gradings as $key => $grading) {
                $childrengradings->teacher[] = array('evalniveauid' => $niveauid, 'value' => $key, 'sum' => $grading);
            }

        }
        foreach ($children_studentevaluation as $key => $value) {
            $childrengradings->student[$key] = array('sum' => $value);
        }
        $descriptor_return->childrengradings = $childrengradings;

        // summary for example gradings
        $descriptor_return->examples = $childsandexamples->examples;

        $examples_teacherevaluation = array();
        for ($i = 0; $i < $number_evalniveaus; $i++) {
            $examples_teacherevaluation[$i] = array_fill(0, $grading_scheme, 0);
        }

        $examples_studentevaluation = array_fill(0, $grading_scheme, 0);

        foreach ($childsandexamples->examples as $example) {
            if ($example->teacherevaluation > -1) {
                $examples_teacherevaluation[($example->evalniveauid > 0) ? $example->evalniveauid : 0][$example->teacherevaluation]++;
            }
            if ($example->studentevaluation > -1) {
                $examples_studentevaluation[$example->studentevaluation]++;
            }

            $example->id = $example->exampleid;
            $solution_visible = block_exacomp_is_example_solution_visible($courseid, $example, $userid);
            $example->solution_visible = $solution_visible;

        }
        $examplegradings = new stdClass();
        $examplegradings->teacher = array();
        $examplegradings->student = array();

        foreach ($examples_teacherevaluation as $niveauid => $gradings) {
            foreach ($gradings as $key => $grading) {
                $examplegradings->teacher[] = array('evalniveauid' => $niveauid, 'value' => $key, 'sum' => $grading);
            }

        }

        foreach ($examples_studentevaluation as $key => $value) {
            $examplegradings->student[$key] = array('sum' => $value);
        }
        $descriptor_return->examplegradings = $examplegradings;
        // example statistics
        $descriptor_return->examplestotal = $childsandexamples->examplestotal;
        $descriptor_return->examplesvisible = $childsandexamples->examplesvisible;
        $descriptor_return->examplesinwork = $childsandexamples->examplesinwork;
        $descriptor_return->examplesedited = $childsandexamples->examplesedited;

        $descriptor_return->hasmaterial = true;
        if (empty($childsandexamples->examples)) {
            $descriptor_return->hasmaterial = false;
        }

        $descriptor_return->visible = (block_exacomp_is_descriptor_visible($courseid, $descriptor, $userid)) ? 1 : 0;
        $descriptor_return->used = (block_exacomp_descriptor_used($courseid, $descriptor, $userid)) ? 1 : 0;

        if (!$forall) {
            $descriptor_return->gradingisold = block_exacomp_is_descriptor_grading_old($descriptor->id, $userid);
        } else {
            $descriptor_return->gradingisold = false;
        }

        return $descriptor_return;
    }

    /**
     * helper function to use same code for 2 ws
     */
    private static function get_descriptor_children($courseid, $descriptorid, $userid, $forall, $crosssubjid = 0, $show_all = false) {
        global $DB, $USER;

        if ($forall) {
            static::require_can_access_course($courseid);
        } else {
            static::require_can_access_course_user($courseid, $userid);
        }

        //		$coursesettings = block_exacomp_get_settings_by_course($courseid); //never used

        $showexamples = true;

        $parent_descriptor = $DB->get_record(BLOCK_EXACOMP_DB_DESCRIPTORS, array('id' => $descriptorid));
        $descriptor_topic_mm = $DB->get_record(BLOCK_EXACOMP_DB_DESCTOPICS, array('descrid' => $parent_descriptor->id));
        $parent_descriptor->topicid = $descriptor_topic_mm->topicid;

        $children = block_exacomp_get_child_descriptors($parent_descriptor, $courseid, false, array(BLOCK_EXACOMP_SHOW_ALL_TAXONOMIES), true, true);

        $non_visibilities = $DB->get_fieldset_select(BLOCK_EXACOMP_DB_DESCVISIBILITY, 'descrid', 'courseid=? AND studentid=? AND visible=0', array($courseid, 0));

        if ($crosssubjid > 0) {
            $crossdesc = $DB->get_fieldset_select(BLOCK_EXACOMP_DB_DESCCROSS, 'descrid', 'crosssubjid=?', array($crosssubjid));
        } else {
            $crossdesc = [];
        }

        if (!$forall) {
            $non_visibilities_student = $DB->get_fieldset_select(BLOCK_EXACOMP_DB_DESCVISIBILITY, 'descrid', 'courseid=? AND studentid=? AND visible=0', array($courseid, $userid));
        } else {
            $non_visibilities_student = [];
        }
        $children_return = array();
        foreach ($children as $child) {
            if ($child->examples || $show_all) {
                $child_return = static::dakora_get_descriptor_details($courseid, $child->id, $userid, $forall, $crosssubjid);

                $child_return->visible = (!in_array($child->id, $non_visibilities) && ((!$forall && !in_array($child->id, $non_visibilities_student)) || $forall)) ? 1 : 0;
                $child_return->used = (block_exacomp_descriptor_used($courseid, $child, $userid)) ? 1 : 0;
                //if(!in_array($child->id, $non_visibilities) && ((!$forall && !in_array($child->id, $non_visibilities_student))||$forall)){
                if ($crosssubjid == 0 || in_array($child->id, $crossdesc) || in_array($descriptorid, $crossdesc)) {
                    $children_return[] = $child_return;
                }
                //}
            }
        }

        $examples_return = array();

        if ($crosssubjid == 0 || in_array($parent_descriptor->id, $crossdesc)) {
            $parent_descriptor = block_exacomp_get_examples_for_descriptor($parent_descriptor, array(BLOCK_EXACOMP_SHOW_ALL_TAXONOMIES), $showexamples, $courseid);
            $example_non_visibilities = $DB->get_fieldset_select(BLOCK_EXACOMP_DB_EXAMPVISIBILITY, 'exampleid', 'courseid=? AND studentid=? AND visible=0', array($courseid, 0));
            if (!$forall) {
                $example_non_visibilities_student = $DB->get_fieldset_select(BLOCK_EXACOMP_DB_EXAMPVISIBILITY, 'exampleid', 'courseid=? AND studentid=? AND visible=0', array($courseid, $userid));
            } else {
                $example_non_visibilities_student = [];
            }

            foreach ($parent_descriptor->examples as $example) {
                //			    $example_return->tax = "A"; //cannot be exacuted
                $example_return = new stdClass();
                $example_return->exampleid = $example->id;
                $example_return->exampletitle = static::custom_htmltrim($example->title);
                $example_return->examplestate = ($forall) ? 0 : block_exacomp_get_dakora_state_for_example($courseid, $example->id, $userid);
                $example_return->visible = ((!in_array($example->id, $example_non_visibilities)) && ((!$forall && !in_array($example->id, $example_non_visibilities_student)) || $forall)) ? 1 : 0;
                $example_return->used = (block_exacomp_example_used($courseid, $example, $userid)) ? 1 : 0;
                if (!array_key_exists($example->id, $examples_return)) {
                    $examples_return[$example->id] = $example_return;
                }
            }
        }

        $return = new stdClass();
        $return->children = $children_return;
        //$return->examples = $examples_return;
        $return->examples = static::dakora_get_examples_for_descriptor_common($courseid, $descriptorid, $userid, $forall, 0);

        $descriptor_example_statistic = static::get_descriptor_example_statistic($courseid, $userid, $descriptorid, $forall, $crosssubjid);

        $return->examplestotal = $descriptor_example_statistic->total;
        $return->examplesvisible = $descriptor_example_statistic->visible;
        $return->examplesinwork = $descriptor_example_statistic->inwork;
        $return->examplesedited = $descriptor_example_statistic->edited;

        return $return;
    }

    private static function dakora_get_topics_by_course_common($courseid, $only_associated, $userid = 0, $groupid = -1) {

        static::require_can_access_course($courseid);

        //TODO if added for 1 student -> mind visibility for this student
        $tree = block_exacomp_build_example_association_tree($courseid, array(), 0, 0, true);

        //TODO, finish this to distinguish between groups, students and forall
        if ($groupid != -1) {
            $userid = 0;
        }

        $topics_return = array();
        foreach ($tree as $subject) {
            foreach ($subject->topics as $topic) {
                if (!$only_associated || ($only_associated && $topic->associated == 1)) {
                    $topic_return = new stdClass();
                    $topic_return->topicid = $topic->id;
                    $topic_return->topictitle = static::custom_htmltrim($topic->title);
                    $topic_return->topicdescription = ($topic->description) ? $topic->description : null;
                    $topic_return->numbering = block_exacomp_get_topic_numbering($topic->id);
                    $topic_return->subjectid = $subject->id;
                    $topic_return->subjecttitle = static::custom_htmltrim($subject->title);
                    // 					if($groupid != -1){//for group //TODO, finish this to distinguish between groups, students and forall
                    // 					    $topic_return->visible = (block_exacomp_is_topic_visible_for_group($courseid, $topic, $userid)) ? 1 : 0;
                    // 					    $topic_return->used = (block_exacomp_is_topic_used_for_group($courseid, $topic, $userid)) ? 1 : 0;
                    // 					}else{//for user or forall
                    // 					    $topic_return->visible = (block_exacomp_is_topic_visible($courseid, $topic, $userid)) ? 1 : 0;
                    // 					    $topic_return->used = (block_exacomp_is_topic_used($courseid, $topic, $userid)) ? 1 : 0;
                    // 					}
                    $topic_return->visible = (block_exacomp_is_topic_visible($courseid, $topic, $userid)) ? 1 : 0;
                    $topic_return->used = (block_exacomp_is_topic_used($courseid, $topic, $userid)) ? 1 : 0;
                    $topics_return[] = $topic_return;
                }
            }
        }

        return $topics_return;
    }

    private static function dakora_get_descriptors_common($courseid, $topicid, $userid, $forall, $only_associated, $editmode = false, $showonlyvisible = true) {
        global $DB, $USER;

        if ($forall) {
            static::require_can_access_course($courseid);
        } else {
            static::require_can_access_course_user($courseid, $userid);
        }

        $tree = block_exacomp_build_example_association_tree($courseid, array(), 0, 0, true, $editmode, $showonlyvisible);

        $non_visibilities = $DB->get_fieldset_select(BLOCK_EXACOMP_DB_DESCVISIBILITY, 'descrid', 'courseid=? AND studentid=? AND visible=0', array($courseid, 0));

        if (!$forall) {
            $non_visibilities_student = $DB->get_fieldset_select(BLOCK_EXACOMP_DB_DESCVISIBILITY, 'descrid', 'courseid=? AND studentid=? AND visible=0', array($courseid, $userid));
        }

        $descriptors_return = array();
        foreach ($tree as $subject) {
            foreach ($subject->topics as $topic) {
                if ($topic->id == $topicid) {
                    foreach ($topic->descriptors as $descriptor) {
                        // ignore this descriptor if show_teacherdescriptors_global is disabled and creatorid is not the current user
                        if (!get_config('exacomp', 'show_teacherdescriptors_global') && isset($descriptor->descriptor_creatorid) && $descriptor->descriptor_creatorid != $USER->id) {
                            continue;
                        }
                        if (!$only_associated || ($only_associated && $descriptor->associated == 1)) {
                            $descriptor_return = new stdClass();
                            $descriptor_return->descriptorid = $descriptor->id;
                            $descriptor_return->descriptortitle = static::custom_htmltrim($descriptor->title);
                            $descriptor_return->numbering = block_exacomp_get_descriptor_numbering($descriptor);
                            $descriptor_return->niveaudescription = "";
                            $descriptor_return->niveautitle = "";
                            //							$descriptor_return->niveausort = null;
                            $descriptor_return->niveauid = 0;
                            $descriptor_return->niveauvisible = 0;

                            if ($descriptor->niveauid) {
                                // $niveau = $DB->get_record(BLOCK_EXACOMP_DB_NIVEAUS, array('id' => $descriptor->niveauid));
                                $descriptor_return->niveautitle = static::custom_htmltrim($descriptor->niveau_title);
                                // $descriptor_return->niveausort = $niveau->numb;//.','.$niveau->sorting;//static::custom_htmltrim($niveau->title);
                                $descriptor_return->niveausort = $descriptor->niveau_sorting;
                                // 2023.04.21 Use sorting instead of numb.
                                // Numb is used rarely and is written in Komet. E.g. "M1.1, M1.2" etc. Sorting is the actual sorting of the elements in Komet
                                // So sorting is more relevant. It should not happen, that numb is different from sorting, but it DOES happent hat numb is not used ==> that is a problem
                                // sorting always exists ==> use sorting. We did not use a combination of sorting and numb, because it would create a string and caus problems.
                                $descriptor_return->niveauid = $descriptor->niveauid;

                                //								var_dump($descriptor->niveauid);
                                //								var_dump($niveau->id);
                                //								die;
                                $descriptor_return->niveauvisible = block_exacomp_is_niveau_visible($courseid, $topicid, $userid, $descriptor->niveauid);

                                $niveau = $DB->get_record('block_exacompsubjniveau_mm', array('subjectid' => $subject->id, 'niveauid' => $descriptor->niveauid));
                                $descriptor_return->niveaudescription = $niveau->subtitle;
                            }
                            $descriptor_return->visible = (!in_array($descriptor->id, $non_visibilities) && ((!$forall && !in_array($descriptor->id, $non_visibilities_student)) || $forall)) ? 1 : 0;
                            $descriptor_return->used = (block_exacomp_descriptor_used($courseid, $descriptor, $userid)) ? 1 : 0;
                            //if(!in_array($descriptor->id, $non_visibilities) && ((!$forall && !in_array($descriptor->id, $non_visibilities_student))||$forall))
                            if (!$forall) {
                                $descriptor_return->gradingisold = block_exacomp_is_descriptor_grading_old($descriptor->id, $userid);
                            } else {
                                $descriptor_return->gradingisold = false;
                            }
                            $descriptors_return[] = $descriptor_return;
                        }
                    }
                }
            }
        }

        // TODO RW IS THIS IMPORTANT?   It creates a problem... it sorts by numbering ALPHABETICALLY
        // M1.1-1
        // M1.1-12
        // M1.1-2
        // commented it out for now since it is already sorted correctly before the line "usort...."
        //		usort($descriptors_return, "static::cmp_niveausort");

        return $descriptors_return;
    }

    //TODO: RW    only_associated maybe has the wrong meaning now... onlyAssignedChildren=true returns only the children that have been directly assigned
    private static function dakora_get_descriptors_by_cross_subject_common($courseid, $crosssubjid, $userid, $forall, $only_associated = false) {
        global $DB;

        $descriptors = block_exacomp_get_descriptors_for_cross_subject($courseid, $crosssubjid, $only_associated);

        $non_visibilities = $DB->get_fieldset_select(BLOCK_EXACOMP_DB_DESCVISIBILITY, 'descrid', 'courseid=? AND studentid=? AND visible=0', array($courseid, 0));
        $non_topic_visibilities = $DB->get_fieldset_select(BLOCK_EXACOMP_DB_TOPICVISIBILITY, 'topicid', 'courseid=? AND studentid=? AND visible=0 AND niveauid IS NULL', array($courseid, 0));

        if (!$forall) {
            $non_visibilities_student = $DB->get_fieldset_select(BLOCK_EXACOMP_DB_DESCVISIBILITY, 'descrid', 'courseid=? AND studentid=? AND visible=0', array($courseid, $userid));
            $non_topic_visibilities_student = $DB->get_fieldset_select(BLOCK_EXACOMP_DB_TOPICVISIBILITY, 'topicid', 'courseid=? AND studentid=? AND visible=0 AND niveauid IS NULL', array($courseid, $userid));
        } else {
            $non_visibilities_student = [];
            $non_topic_visibilities = [];
        }

        $descriptors_return = array();
        foreach ($descriptors as $descriptor) {
            //TODO
            if ($only_associated) {
                $has_visible_examples = false;
                $has_children_with_visible_examples = false;

                $example_non_visibilities = $DB->get_fieldset_select(BLOCK_EXACOMP_DB_EXAMPVISIBILITY, 'exampleid', 'courseid=? AND studentid=? AND visible=0', array($courseid, 0));
                if (!$forall) {
                    $example_non_visibilities_student = $DB->get_fieldset_select(BLOCK_EXACOMP_DB_EXAMPVISIBILITY, 'exampleid', 'courseid=? AND studentid=? AND visible=0', array($courseid, $userid));
                }

                if (isset($descriptor->examples)) {    //descriptor has examples
                    foreach ($descriptor->examples as $example) {
                        if (!in_array($example->id, $example_non_visibilities) && ((!$forall && !in_array($example->id, $example_non_visibilities_student)) || $forall)) {
                            $has_visible_examples = true;
                        }    //descriptor has visible examples

                    }
                }

                if (isset($descriptor->children)) {
                    foreach ($descriptor->children as $child) {
                        if ((!in_array($child->id, $non_visibilities) && ((!$forall && !in_array($child->id, $non_visibilities_student)) || $forall))) { //child is visible
                            if (isset($child->examples)) {    //descriptor has children
                                foreach ($child->examples as $example) {
                                    if (!in_array($example->id, $example_non_visibilities) && ((!$forall && !in_array($example->id, $example_non_visibilities_student)) || $forall)) {
                                        $has_children_with_visible_examples = true;
                                    }    //descriptor has children with visible examples
                                }
                            }
                        }
                    }
                }

                if ($has_visible_examples || $has_children_with_visible_examples) {
                    $descriptor_return = new stdClass();
                    $descriptor_return->descriptorid = $descriptor->id;
                    $descriptor_return->descriptortitle = static::custom_htmltrim($descriptor->title);
                    $descriptor_return->numbering = block_exacomp_get_descriptor_numbering($descriptor);
                    $descriptor_return->niveautitle = "";
                    $descriptor_return->niveauid = 0;

                    $visibility = 0;
                    if (!in_array($descriptor->topicid, $non_topic_visibilities) && ((!$forall && !in_array($descriptor->topicid, $non_topic_visibilities_student)) || $forall)) {
                        if (!in_array($descriptor->id, $non_visibilities) && ((!$forall && !in_array($descriptor->id, $non_visibilities_student)) || $forall))    //descriptor is visibile
                        {
                            $visibility = 1;
                        }
                    }

                    $descriptor_return->visible = $visibility;
                    $descriptor_return->used = (block_exacomp_descriptor_used($courseid, $descriptor, $userid)) ? 1 : 0;
                    if ($descriptor->niveauid) {
                        $niveau = $DB->get_record(BLOCK_EXACOMP_DB_NIVEAUS, array('id' => $descriptor->niveauid));
                        $descriptor_return->niveautitle = substr(block_exacomp_get_descriptor_numbering($descriptor), 0, 3) . ": " . static::custom_htmltrim($niveau->title);
                        $descriptor_return->niveausort = block_exacomp_get_descriptor_numbering($descriptor);
                        $descriptor_return->niveauid = $niveau->id;
                    }
                    $descriptors_return[] = $descriptor_return;
                }
            } else {
                $descriptor_return = new stdClass();
                $descriptor_return->descriptorid = $descriptor->id;
                $descriptor_return->descriptortitle = static::custom_htmltrim($descriptor->title);
                $descriptor_return->numbering = block_exacomp_get_descriptor_numbering($descriptor);
                $descriptor_return->niveautitle = "";
                $descriptor_return->niveauid = 0;

                $visibility = 0;
                if (!in_array($descriptor->topicid, $non_topic_visibilities) && ((!$forall && !in_array($descriptor->topicid, $non_topic_visibilities_student)) || $forall)) {
                    if (!in_array($descriptor->id, $non_visibilities) && ((!$forall && !in_array($descriptor->id, $non_visibilities_student)) || $forall))    //descriptor is visibile
                    {
                        $visibility = 1;
                    }
                }

                $descriptor_return->visible = $visibility;
                $descriptor_return->used = (block_exacomp_descriptor_used($courseid, $descriptor, $userid)) ? 1 : 0;

                if ($descriptor->niveauid) {
                    $niveau = $DB->get_record(BLOCK_EXACOMP_DB_NIVEAUS, array('id' => $descriptor->niveauid));
                    $descriptor_return->niveautitle = substr(block_exacomp_get_descriptor_numbering($descriptor), 0, 3) . ": " . static::custom_htmltrim($niveau->title);
                    $descriptor_return->niveausort = block_exacomp_get_descriptor_numbering($descriptor);
                    $descriptor_return->niveauid = $niveau->id;
                }
                $descriptors_return[] = $descriptor_return;
            }
        }

        #sort crosssub entries
        usort($descriptors_return, "static::cmp_niveausort");

        return $descriptors_return;
    }

    private static function cmp_niveausort($a, $b) {
        return strcmp($a->niveausort, $b->niveausort);
    }

    private static function dakora_get_examples_for_descriptor_common($courseid, $descriptorid, $userid, $forall, $crosssubjid = 0) {
        global $DB;

        if ($forall) {
            static::require_can_access_course($courseid);
        } else {
            static::require_can_access_course_user($courseid, $userid);
        }

        $descriptor = $DB->get_record(BLOCK_EXACOMP_DB_DESCRIPTORS, array('id' => $descriptorid));
        $coursesettings = block_exacomp_get_settings_by_course($courseid);

        $showexamples = true;

        if ($crosssubjid > 0) {
            $cross_subject_descriptors = block_exacomp_get_descriptors_for_cross_subject($courseid, $crosssubjid);
            if (!array_key_exists($descriptor->id, $cross_subject_descriptors)) {
                return array();
            }
        }

        // get the topicid
        // note: parent and child descriptors are always associated with the topic
        $descriptor_topic_mm = $DB->get_record(BLOCK_EXACOMP_DB_DESCTOPICS, array('descrid' => $descriptor->id));
        $descriptor->topicid = $descriptor_topic_mm->topicid;
        $descriptor = block_exacomp_get_examples_for_descriptor($descriptor, array(BLOCK_EXACOMP_SHOW_ALL_TAXONOMIES), $showexamples, $courseid);

        $example_non_visibilities = $DB->get_fieldset_select(BLOCK_EXACOMP_DB_EXAMPVISIBILITY, 'exampleid', 'courseid=? AND studentid=? AND visible=0', array($courseid, 0));
        if (!$forall) {
            $example_non_visibilities_student = $DB->get_fieldset_select(BLOCK_EXACOMP_DB_EXAMPVISIBILITY, 'exampleid', 'courseid=? AND studentid=? AND visible=0', array($courseid, $userid));
        }

        $examples_return = array();
        foreach ($descriptor->examples as $example) {
            $example->title = static::custom_htmltrim($example->title);
            $example_return = new stdClass();
            $example_return->exampleid = $example->id;
            $example_return->exampletitle = static::custom_htmltrim(strip_tags($example->title));
            $example_return->examplestate = ($forall) ? 0 : block_exacomp_get_dakora_state_for_example($courseid, $example->id, $userid);
            //taxonomies and taxids: RW
            $taxonomies = '';
            $taxids = '';
            foreach ($example->taxonomies as $tax) {
                if ($taxonomies == '') { //first run, no ","
                    $taxonomies .= static::custom_htmltrim($tax->title);
                    $taxids .= $tax->id;
                } else {
                    $taxonomies .= ',' . static::custom_htmltrim($tax->title);
                    $taxids .= ',' . $tax->id;
                }
            }
            $example_return->exampletaxonomies = $taxonomies;
            $example_return->exampletaxids = $taxids;
            $example_return->examplecreatorid = $example->creatorid;

            if ($forall) {
                $example_return->teacherevaluation = -1;
                $example_return->studentevaluation = -1;
                $example_return->evalniveauid = null;
                $example_return->timestampteacher = 0;
                $example_return->timestampstudent = 0;
            } else {
                $evaluation = (object)static::_get_example_information($courseid, $userid, $example->id);
                $example_return->teacherevaluation = $evaluation->teachervalue;
                $example_return->studentevaluation = $evaluation->studentvalue;
                $example_return->evalniveauid = $evaluation->evalniveauid;
                $example_return->timestampteacher = $evaluation->timestampteacher;
                $example_return->timestampstudent = $evaluation->timestampstudent;
                $example_return->additionalinfo = isset($evaluation->additionalinfo) ? $evaluation->additionalinfo : -1;
                if (!$evaluation || $evaluation->resubmission) {
                    $example_return->resubmission = true;
                } else {
                    $example_return->resubmission = false;
                }
            }

            $example_return->visible = ((!in_array($example->id, $example_non_visibilities)) && ((!$forall && !in_array($example->id, $example_non_visibilities_student)) || $forall)) ? 1 : 0;
            $example_return->used = (block_exacomp_example_used($courseid, $example, $userid)) ? 1 : 0;
            if (!array_key_exists($example->id, $examples_return)) {
                $examples_return[$example->id] = $example_return;
            }
        }

        return $examples_return;
    }

    private static function create_or_update_example_common($exampleid, $name, $description, $timeframe = '', $externalurl = null, $comps = null, $fileitemids = '', $solutionfileitemid = '', $taxonomies = '', $newtaxonomy = '',
        $courseid = 0, $filename = null,
        $crosssubjectid = -1, $activityid = 0, $is_teacherexample = 0, $removefiles = 0, $visible = true, $onlyForThisCourse = false) {
        global $DB, $USER, $CFG, $COURSE;

        $COURSE->id = $courseid; // TODO: copied this from  update_descriptor_category.. why is the CONTEXT wrong?

        //Update material that already exists
        if ($exampleid != -1) {
            $example = example::get($exampleid);
            block_exacomp_require_item_capability(BLOCK_EXACOMP_CAP_MODIFY, $example);
        } else {
            //new material
            $example = new stdClass ();
        }

        if ($name) {
            $example->title = $name;
        }
        if ($description) {
            $example->description = $description;
        }
        $example->timeframe = $timeframe;
        $example->externalurl = $externalurl;
        $example->creatorid = $USER->id;
        $example->timestamp = time();
        if ($courseid) {
            // dakora ab 2017-09-19 übergibt auch die courseid
            $example->source = block_exacomp_is_teacher($courseid)
                ? BLOCK_EXACOMP_EXAMPLE_SOURCE_TEACHER
                : BLOCK_EXACOMP_EXAMPLE_SOURCE_USER;
        } else {
            // bei elove wird keine courseid übergeben
            // elove logik: dakora_get_user_role() kann nicht verwendet werden
            $example->source = static::get_user_role()->role == BLOCK_EXACOMP_WS_ROLE_TEACHER
                ? BLOCK_EXACOMP_EXAMPLE_SOURCE_TEACHER
                : BLOCK_EXACOMP_EXAMPLE_SOURCE_USER;
        }

        //add blockingevent tag, since it is a free_element that should be handled as a blocking event in many instances. Maybe add filed "free element" or check in a different way in the require_can_access_example
        //the require_can_access_example checks if a student has access, and they have access to their blocking and free elements ==> set blocking_event flag
        if ($comps == "freemat" && $crosssubjectid == -1) {
            $example->blocking_event = 2;
            $example->source = BLOCK_EXACOMP_EXAMPLE_SOURCE_USER_FREE_ELEMENT;
        }
        $example->activityid = $activityid;
        $example_icons = array();
        if ($exampleid != -1) {
            $ex = $DB->get_record(BLOCK_EXACOMP_DB_EXAMPLES, ['id' => $exampleid]);
            if ($ex->example_icon) {
                $example_icons = unserialize($ex->example_icon);
            }
        }
        if ($activityid) {
            if ($module = get_coursemodule_from_id(null, $activityid)) {
                // externaltask
                $example->externaltask = block_exacomp_get_activityurl($module)->out(false);
                // get icon path for activity and save it to database
                $mod_info = get_fast_modinfo($courseid);
                if (array_key_exists($module->id, $mod_info->cms)) {
                    $cm = $mod_info->cms[$module->id];
                    $example_icons['externaltask'] = $cm->get_icon_url()->out(false);
                }
                // activitylink
                $activitylink = block_exacomp_get_activityurl($module)->out(false);
                $activitylink = str_replace($CFG->wwwroot . '/', '', $activitylink);
                $example->activitylink = $activitylink;
            }
            $example->activitylink = '';
            $example->activitytitle = '';
            $example->courseid = $courseid;
        } else {
            $example->activitylink = '';
            $example->activitytitle = '';
            if ($onlyForThisCourse) { // the example will only exist in this course. This functionality is needed in diggrplus
                $example->courseid = $courseid;
            } else {
                $example->courseid = 0;
            }
            if (array_key_exists('externaltask', $example_icons)) {
                unset($example_icons['externaltask']);
            }
        }

        if (count($example_icons)) {
            $example->example_icon = serialize($example_icons);
        } else {
            $example->example_icon = '';
        }

        $example->is_teacherexample = $is_teacherexample;

        if ($exampleid != -1) {
            $DB->update_record(BLOCK_EXACOMP_DB_EXAMPLES, $example);
            $id = $exampleid;
        } else {
            $example->id = $id = $DB->insert_record(BLOCK_EXACOMP_DB_EXAMPLES, $example);
        }

        //in order for the free material to be accessible: code is mainly the same as block_exacomp_create_blocking_event
        if ($comps == "freemat" && $crosssubjectid == -1) {
            $schedule = new stdClass();
            $schedule->studentid = $USER->id;
            $schedule->exampleid = $example->id;
            $schedule->creatorid = $USER->id;
            $schedule->courseid = $courseid;
            $scheduleid = $DB->insert_record(BLOCK_EXACOMP_DB_SCHEDULE, $schedule);

            $record = $DB->get_records(BLOCK_EXACOMP_DB_EXAMPVISIBILITY, array('courseid' => $courseid, 'exampleid' => $exampleid, 'studentid' => 0, 'visible' => 1));
            if (!$record) {
                $visibility = new stdClass();
                $visibility->courseid = $courseid;
                $visibility->exampleid = $exampleid;
                $visibility->studentid = 0;
                $visibility->visible = 1;
                $visibilityid = $DB->insert_record(BLOCK_EXACOMP_DB_EXAMPVISIBILITY, $visibility);
            }
        }

        if ($fileitemids != '') {
            if ($exampleid != -1) {
                //if there already exists an example: remove either all files or only the ones explicitly stated (dakora uses "remove all" right now 20210204, diggprlus uses explizit remove
                if ($removefiles != '') {
                    // remove files specifically marked for deletion by user:
                    // for deleting a file that already exists, itemid cannot be used, but pathnamehash. "get_file()" actually gets the pathnamehash and uses this to get the file
                    // use get_file_by_hash() instead, for deleting already existing files.
                    // could this be used to remove files this user doesn't have access to? HACKABLE TODO
                    // solution: get itemid. this itemid is the exampleid in this case
                    $fs = get_file_storage();
                    $removefiles = explode(',', $removefiles);
                    foreach ($removefiles as $removefile) {
                        $file = $fs->get_file_by_id($removefile);
                        if ($file) {
                            if ($file->get_itemid() == $exampleid) { // only delete file of current example. Protection if something goes really wrong or this webservice is used maliciously
                                $file->delete();
                            }
                        }
                    }
                } else {
                    // TODO: this should be removed, maybe was needed in old dakora/diggr

                    //Delete old files
                    $context = context_user::instance($USER->id);
                    $fs = get_file_storage();
                    $fs->delete_area_files(context_system::instance()->id, 'block_exacomp', 'example_task', $example->id);
                }
            }

            $fileitemids = explode(',', $fileitemids);
            foreach ($fileitemids as $fileitemid) {
                $context = context_user::instance($USER->id);
                $fs = get_file_storage();

                if ($filename) {
                    // TODO: filename sollte nicht mehr notwendig sein, das ist alter code?
                    $file = $fs->get_file($context->id, 'user', 'draft', $fileitemid, '/', $filename);
                } else {
                    $file = reset($fs->get_area_files($context->id, 'user', 'draft', $fileitemid, null, false));
                }
                if (!$file) {
                    throw new moodle_exception('file not found');
                }

                $fs->create_file_from_storedfile(array(
                    'contextid' => context_system::instance()->id,
                    'component' => 'block_exacomp',
                    'filearea' => 'example_task',
                    'itemid' => $example->id,
                ), $file);
                $file->delete();
            }
        }

        if ($solutionfileitemid != '') {
            $context = context_user::instance($USER->id);
            $fs = get_file_storage();

            if ($exampleid != -1) {
                $fs->delete_area_files(context_system::instance()->id, 'block_exacomp', 'example_solution', $example->id);
            }

            $file = reset($fs->get_area_files($context->id, 'user', 'draft', $solutionfileitemid, null, false));
            if (!$file) {
                throw new moodle_exception('solution file not found');
            }

            $fs->create_file_from_storedfile([
                'contextid' => context_system::instance()->id,
                'component' => 'block_exacomp',
                'filearea' => 'example_solution',
                'itemid' => $example->id,
            ], $file);

            $file->delete();
        }

        if ($crosssubjectid != -1) {
            $insert = new stdClass ();
            $insert->exampid = $id;
            $insert->id_foreign = $crosssubjectid;
            $insert->table_foreign = 'cross';
            $DB->insert_record(BLOCK_EXACOMP_DB_DESCEXAMP, $insert);

            //vorerst notlösung:
            $insert = new stdClass();
            $insert->courseid = $courseid;
            $insert->exampleid = $id;
            $insert->studentid = 0;
            $insert->visible = 1;
            $DB->insert_record(BLOCK_EXACOMP_DB_EXAMPVISIBILITY, $insert);
            // 		    //visibility entries for this example in course where descriptors are associated
            // 		    $courseids = block_exacomp_get_courseids_by_descriptor($descriptor);
            // 		    foreach ($courseids as $courseid) {
            // 		        $insert = new stdClass();
            // 		        $insert->courseid = $courseid;
            // 		        $insert->exampleid = $id;
            // 		        $insert->studentid = 0;
            // 		        $insert->visible = 1;
            // 		        $DB->insert_record(BLOCK_EXACOMP_DB_EXAMPVISIBILITY, $insert);
            // 		    }
        } else if ($comps != "freemat" && $comps != '0') { //descriptors of course, and NOT edit but create
            $descriptors = explode(',', $comps);
            foreach ($descriptors as $descriptor) {
                $insert = new stdClass ();
                $insert->exampid = $id;
                $insert->descrid = $descriptor;
                $DB->insert_record(BLOCK_EXACOMP_DB_DESCEXAMP, $insert);

                //visibility entries for this example in course where descriptors are associated
                // TODO: do we want this? or should it only change in the current course? --> Solved in diggrplus by using diggrplus_annotate_example
                $courseids = block_exacomp_get_courseids_by_descriptor($descriptor);
                foreach ($courseids as $courseid) {
                    $insert = new stdClass();
                    $insert->courseid = $courseid;
                    $insert->exampleid = $id;
                    $insert->studentid = 0;
                    $insert->visible = 1;
                    g::$DB->insert_or_update_record(BLOCK_EXACOMP_DB_EXAMPVISIBILITY, $insert);
                }
            }
        } else if ($comps == "freemat") {
            //Free material, not linked to a "real" competence
            $insert = new stdClass ();
            $insert->exampid = $id;
            $insert->table_foreign = 'free_material';
            $insert->descrid = '-1';
            $DB->insert_record(BLOCK_EXACOMP_DB_DESCEXAMP, $insert);

            $insert = new stdClass();
            $insert->courseid = $courseid;
            $insert->exampleid = $id;
            $insert->studentid = 0;
            $insert->visible = 1;
            $DB->insert_record(BLOCK_EXACOMP_DB_EXAMPVISIBILITY, $insert);
        }
        //if not crosssubjectid, not comps, and not freemat then don't update the associations... comps should be ="0"

        //clear the taxonomies
        if ($exampleid != -1) {
            $DB->delete_records(BLOCK_EXACOMP_DB_EXAMPTAX, [
                'exampleid' => $id,
            ]);
        }
        $taxonomies = trim($taxonomies) ? explode(',', trim($taxonomies)) : [];
        foreach ($taxonomies as $taxid) {
            $DB->insert_record(BLOCK_EXACOMP_DB_EXAMPTAX, [
                'exampleid' => $id,
                'taxid' => $taxid,
            ]);
        }

        //and create and add the new taxonomy if it exists
        $newTax = $newtaxonomy;
        if ($newTax != '') {
            $newTaxonomy = new stdClass();
            $newTaxonomy->title = $newTax;
            $newTaxonomy->parentid = 0;
            $newTaxonomy->sorting = $DB->get_field(BLOCK_EXACOMP_DB_TAXONOMIES, 'MAX(sorting)', array()) + 1;
            $newTaxonomy->source = BLOCK_EXACOMP_EXAMPLE_SOURCE_TEACHER;
            $newTaxonomy->sourceid = 0;
            $newTaxonomy->id = $DB->insert_record(BLOCK_EXACOMP_DB_TAXONOMIES, $newTaxonomy);
            $DB->insert_record(BLOCK_EXACOMP_DB_EXAMPTAX, [
                'exampleid' => $id,
                'taxid' => $newTaxonomy->id,
            ]);
        }

        block_exacomp_set_example_visibility($id, $courseid, true, BLOCK_EXACOMP_SHOW_ALL_STUDENTS);

        return array(
            "exampleid" => $id,
            "newtaxonomy" => array(
                "id" => $newTaxonomy->id,
                "source" => $newTaxonomy->source,
                "title" => $newTaxonomy->title,
            ),
        );
    }

    //TODO this is not true for newest version
    private static function get_descriptor_example_statistic($courseid, $userid, $descriptorid, $forall, $crosssubjid) {
        $return = new stdClass();
        $return->total = 0;
        $return->visible = 0;
        $return->inwork = 0;
        $number_students = 1;

        if ($forall) {
            static::require_can_access_course($courseid);
            $students = block_exacomp_get_students_by_course($courseid);
            $number_students = count($students);
        } else {
            static::require_can_access_course_user($courseid, $userid);
        }

        list($total, $gradings, $notevaluated, $inwork, $totalgrade, $notinwork, $hidden, $edited, $evaluated, $visible) = block_exacomp_get_example_statistic_for_descriptor($courseid, $descriptorid, $userid, $crosssubjid);

        $return->total = $total;
        $return->visible = $visible;
        $return->inwork = $inwork;
        $return->edited = $edited;

        return $return;
    }

    /**
     * Returns all subjects for the given user where trainer action is required.
     * Trainer action is required as soon as there are ungraded submissions.
     *
     * @param int $userid
     */
    private static function get_requireaction_subjects($userid) {
        global $DB;

        $require_actions = $DB->get_records_sql('SELECT DISTINCT s.id
 			FROM {block_exacompsubjects} s
			JOIN {block_exacomptopics} t ON t.subjid = s.id
			JOIN {block_exacompdescrtopic_mm} td ON td.topicid = t.id
			JOIN {block_exacompdescriptors} d ON td.descrid = d.id
			JOIN {' . BLOCK_EXACOMP_DB_DESCEXAMP . '} de ON de.descrid = d.id
			JOIN {block_exacompexamples} e ON de.exampid = e.id
			JOIN {' . BLOCK_EXACOMP_DB_ITEM_MM . '} ie ON ie.exacomp_record_id = e.id
			JOIN {block_exaportitem} i ON i.id = ie.itemid
			WHERE ie.status = 0 AND i.userid = ?', array($userid));

        return $require_actions;
    }

    private static function find_courseid_for_example($exampleid) {
        // go through all courses
        // and all subjects
        // and all examples
        // and try to find it
        $courses_ws = static::get_courses(g::$USER->id);

        $courses = array();
        foreach ($courses_ws as $course) {
            $courses[$course['courseid']] = new stdClass();
            $courses[$course['courseid']]->id = $course['courseid'];
        }

        //check if user is external trainer, if he is add courses where external_student is enrolled
        // check external trainers
        $external_trainer_entries = g::$DB->get_records(BLOCK_EXACOMP_DB_EXTERNAL_TRAINERS, array(
            'trainerid' => g::$USER->id,
        ));

        foreach ($external_trainer_entries as $ext_tr_entry) {
            $courses_user = static::get_courses($ext_tr_entry->studentid);

            foreach ($courses_user as $course) {
                if (!array_key_exists($course['courseid'], $courses)) {
                    $courses[$course['courseid']] = new stdClass();
                    $courses[$course['courseid']]->id = $course['courseid'];
                }
            }
        }

        foreach ($courses as $course) {
            try {
                static::require_can_access_example($exampleid, $course->id);

                return $course->id;
            } catch (block_exacomp_permission_exception $e) {
                // try other course
            }
        }
    }



    //    private static function find_courseid_for_comp($compid, $comptype) {
    //        // go through all courses
    //        // and all subjects
    //        // and all examples
    //        // and try to find it
    //        $courses_ws = static::get_courses(g::$USER->id);
    //
    //        $courses = array();
    //        foreach ($courses_ws as $course) {
    //            $courses[$course['courseid']] = new stdClass();
    //            $courses[$course['courseid']]->id = $course['courseid'];
    //        }
    //
    //        //check if user is external trainer, if he is add courses where external_student is enrolled
    //        // check external trainers
    //        $external_trainer_entries = g::$DB->get_records(BLOCK_EXACOMP_DB_EXTERNAL_TRAINERS, array(
    //            'trainerid' => g::$USER->id,
    //        ));
    //
    //        foreach ($external_trainer_entries as $ext_tr_entry) {
    //            $courses_user = static::get_courses($ext_tr_entry->studentid);
    //
    //            foreach ($courses_user as $course) {
    //                if (!array_key_exists($course['courseid'], $courses)) {
    //                    $courses[$course['courseid']] = new stdClass();
    //                    $courses[$course['courseid']]->id = $course['courseid'];
    //                }
    //            }
    //        }
    //
    //        foreach ($courses as $course) {
    //            try {
    //                static::require_can_access_comp($compid, $course->id);
    //                return $course->id;
    //            } catch (block_exacomp_permission_exception $e) {
    //                // try other course
    //            }
    //        }
    //    }

    /**
     * @param $exampleid
     * @param int $courseid if courseid=0, then we don't know the course and have to search all
     *                        TODO: if courseid is set, then just search that course
     * @return object the data of the found example
     * @throws block_exacomp_permission_exception
     */
    private static function require_can_access_example($exampleid, $courseid) {
        $example = \block_exacomp\example::get($exampleid);
        if (!$example) {
            throw new block_exacomp_permission_exception("Example '$exampleid' not found");
        }

        if ($example->blocking_event == 1) {
            $schedule = g::$DB->get_record(BLOCK_EXACOMP_DB_SCHEDULE, ['exampleid' => $exampleid]);
            if (!$schedule) {
                throw new block_exacomp_permission_exception("Example '$exampleid' not found #2");
            }

            if ($schedule->studentid == g::$USER->id) {
                // ok: student example
                return;
            } else if ($schedule->creatorid == g::$USER->id) {
                // ok: created by this student / teacher
                return;
            } else if (block_exacomp_is_teacher($courseid)) {
                $students = block_exacomp_get_students_by_course($courseid);
                if (isset($students[$schedule->studentid])) {
                    // blocking event from a student in course
                    return;
                }
            }

            throw new block_exacomp_permission_exception("Example '$exampleid' in course '$courseid' not allowed");
            //        } else if($example->blocking_event == 2){   maybe check differently for free examples
        } else {
            $examples = block_exacomp_get_examples_by_course($courseid);
            $examples_crosssubj = block_exacomp_get_crosssubject_examples_by_course($courseid);
            $found = false;

            if (isset($examples[$exampleid]) || isset($examples_crosssubj[$exampleid])) {
                // ok: is course example
                $found = true;
            } else {
                // try to find it in a cross-subject
                $cross_subjects = block_exacomp_get_cross_subjects_by_course($courseid);
                if ($cross_subjects) {
                    $found = call_user_func(function() use ($cross_subjects, $courseid, $exampleid) {
                        foreach ($cross_subjects as $cross_subject) {
                            $descriptors = block_exacomp_get_descriptors_for_cross_subject($courseid, $cross_subject);

                            foreach ($descriptors as $descriptor) {
                                if (!empty($descriptor->examples[$exampleid])) {
                                    return true;
                                }

                                foreach ($descriptor->children as $descriptor) {
                                    if (!empty($descriptor->examples[$exampleid])) {
                                        return true;
                                    }
                                }
                            }
                        }

                        return false;
                    });
                }
            }

            // try to find it in free materials
            if (!$found && $example->blocking_event == 2) {
                $sql = 'SELECT * '
                    . 'FROM {' . BLOCK_EXACOMP_DB_DESCRIPTORS . '} '
                    . 'WHERE id = -1';

                $descriptors = descriptor::get_objects_sql($sql);

                $descriptor = array_pop($descriptors); //there will only be this single descriptor in the return array

                $descriptor = block_exacomp_get_examples_for_descriptor($descriptor, array(BLOCK_EXACOMP_SHOW_ALL_TAXONOMIES), true, $courseid, false);
                //$examples = block_exacomp_get_examples_for_pool($userid, $courseid);

                $examples = $descriptor->examples;

                if (isset($examples[$exampleid])) {
                    $found = true;
                }
            }

            if (!$found) {
                throw new block_exacomp_permission_exception("Example '$exampleid' not found #3");
            }

            // can be viewed by user, or by whole course
            if (block_exacomp_is_teacher($courseid) ||
                (block_exacomp_is_student($courseid) && block_exacomp_is_example_visible($courseid, $example, g::$USER->id))
            ) {
                return;
            }

            throw new block_exacomp_permission_exception("Example '$exampleid' in course '$courseid' not allowed");
        }
    }

    /**
     * @param $compid
     * @param int $courseid if courseid=0, then we don't know the course and have to search all
     * @param int $comptype
     * @return object the data of the found example
     * @throws block_exacomp_permission_exception
     */
    private static function require_can_access_comp($compid, $courseid = 0, $comptype = null) {
        switch ($comptype) {
            case BLOCK_EXACOMP_TYPE_TOPIC:
                // TODO: What should be checked? RW
                break;
            case BLOCK_EXACOMP_TYPE_DESCRIPTOR:

                break;
            case BLOCK_EXACOMP_TYPE_EXAMPLE:
                static::require_can_access_example($compid, $courseid);
                break;
        }
    }

    private static function wstoken() {
        return optional_param('wstoken', null, PARAM_ALPHANUM);
    }

    private static function get_webservice_url_for_file($file, $context = null, $position = -1) {
        $context = block_exacomp_get_context_from_courseid($context);

        $url = moodle_url::make_webservice_pluginfile_url($context->id, $file->get_component(), $file->get_filearea(),
            $file->get_itemid(), $file->get_filepath(), $file->get_filename());

        $url->param('token', static::wstoken());

        if ($position != -1) {
            $url->param('position', $position);
            //            $url = $url."&position=".$position;
        }

        return $url;
    }

    private static function format_url($url) {
        $url_no_protocol = preg_replace('!^.*://!', '', $url);
        $www_root_no_protocol = preg_replace('!^.*://!', '', g::$CFG->wwwroot);

        if (strpos($url_no_protocol, $www_root_no_protocol) === 0) {
            // is local moodle url

            // add http:// or https:// (if required)
            // $url = g::$CFG->wwwroot.substr($url_no_protocol, strlen($www_root_no_protocol));

            // make local url = relative to moodle
            $url = substr($url_no_protocol, strlen($www_root_no_protocol));

            // link to url.php, which loggs the user in first
            $url = (new moodle_url('/blocks/exacomp/login.php', [
                'wstoken' => static::wstoken(),
                'url' => $url,
            ]))->out(false);
        } else if (!preg_replace('!^.*://!', '', $url)) {
            $url = 'http://' . $url;
        }

        return $url;
    }

    protected static function key_value_returns($typeKey, $typeValue) {
        $nameKey = 'id';
        $nameValue = 'name';

        return new external_multiple_structure(
            new external_single_structure(array(
                $nameKey => new external_value($typeKey, $nameKey),
                $nameValue => new external_value($typeValue, $nameValue),
            )));
    }

    protected static function return_key_value($values) {
        $nameKey = 'id';
        $nameValue = 'name';
        $return = [];

        foreach ($values as $key => $value) {
            $return[] = [$nameKey => (int)$key, $nameValue => $value];
        }

        return $return;
    }

    /**
     * Returns the default eval value fields for a competence for both teacher and studen
     *
     * @return external_value[]
     */
    protected static function comp_eval_returns() {
        return [
            'additionalinfo' => new external_value(PARAM_FLOAT, 'additional grading'),
            'teacherevaluation' => new external_value(PARAM_INT, 'grading of child', VALUE_OPTIONAL),
            'evalniveauid' => new external_value(PARAM_INT, 'evaluation niveau id', VALUE_OPTIONAL),
            'timestampteacher' => new external_value(PARAM_INT, 'timestamp of teacher evaluation', VALUE_OPTIONAL),
            'studentevaluation' => new external_value(PARAM_INT, 'self evaluation of child', VALUE_OPTIONAL),
            'timestampstudent' => new external_value(PARAM_INT, 'timestamp of student evaluation', VALUE_OPTIONAL),
        ];
    }

    /**
     * @param db_record $item
     * @param $courseid
     * @param $studentid
     */
    protected static function add_comp_eval($item, $courseid, $studentid) {
        $eval = block_exacomp_get_comp_eval_merged($courseid, $studentid, $item);

        $item->teacherevaluation = ($eval->teacherevaluation !== null) ? $eval->teacherevaluation : -1;
        $item->studentevaluation = $eval->studentevaluation;
        $item->evalniveauid = $eval->evalniveauid;
        $item->additionalinfo = $eval->additionalinfo;
        $item->timestampteacher = $eval->timestampteacher;
        $item->timestampstudent = $eval->timestampstudent;
    }

    protected static function add_empty_comp_eval($item) {
        $item->teacherevaluation = null;
        $item->studentevaluation = null;
        $item->evalniveauid = null;
        $item->additionalinfo = null;
        $item->timestampteacher = null;
        $item->timestampstudent = null;
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function update_descriptor_category_parameters() {
        return new external_function_parameters(array(
            'descriptorid' => new external_value(PARAM_INT, 'id of descriptor', VALUE_REQUIRED),
            'categories' => new external_value(PARAM_TEXT, 'list of categories', VALUE_DEFAULT, ''),
            'newcategory' => new external_value(PARAM_RAW, 'new category title', VALUE_DEFAULT, ''),
            'courseid' => new external_value(PARAM_INT, 'courseid', VALUE_DEFAULT, 0),
        ));
    }

    /**
     * update an descriptor category
     *
     * @ws-type-write
     * @param integer $descriptorid
     * @param string $categories
     * @param string $newcategory
     * @param integer $courseid
     * @return array
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     */
    public static function update_descriptor_category($descriptorid, $categories = '', $newcategory = '', $courseid = 0) {
        global $CFG, $DB, $USER, $COURSE;

        if (empty($descriptorid)) {
            throw new invalid_parameter_exception ('Parameter descriptorid can not be empty');
        }

        static::validate_parameters(static::update_descriptor_category_parameters(), array(
            'descriptorid' => $descriptorid,
            'categories' => $categories,
            'newcategory' => $newcategory,
            'courseid' => $courseid,
        ));

        $descriptor = descriptor::get($descriptorid);

        if ($courseid = optional_param('courseid', 0, PARAM_INT)) {
            $COURSE->id = $courseid; // TODO: another way?
        }

        block_exacomp_require_item_capability(BLOCK_EXACOMP_CAP_MODIFY, $descriptor);
        $newCategoryReturn = null;

        if ($descriptorid > 0) {
            $newCat = trim($newcategory);
            if ($newCat != '') {
                $newCategory = new stdClass();
                $newCategory->title = $newCat;
                $newCategory->parentid = 0;
                $newCategory->sorting = $DB->get_field(BLOCK_EXACOMP_DB_CATEGORIES, 'MAX(sorting)', array()) + 1;
                $newCategory->source = 0;
                $newCategory->sourceid = 0;
                $newCategory->lvl = 5;
                $newCategory->id = $DB->insert_record(BLOCK_EXACOMP_DB_CATEGORIES, $newCategory);
                // new category will be added for descriptor.
                if ($categories) {
                    $categories .= ',';
                }
                $categories .= $newCategory->id;
                $newCategoryReturn = (object)[
                    'id' => $newCategory->id,
                    'title' => $newCategory->title,
                    'source' => $newCategory->source,
                ];
            }

            // Clear the existing categories.
            $DB->delete_records(BLOCK_EXACOMP_DB_DESCCAT, ['descrid' => $descriptorid]);
            // Insert new list.
            $categories = trim($categories) ? explode(',', trim($categories)) : [];
            if ($categories && count($categories) > 0) {
                foreach ($categories as $catid) {
                    $DB->insert_record(BLOCK_EXACOMP_DB_DESCCAT, [
                        'descrid' => $descriptorid,
                        'catid' => $catid,
                    ]);
                }
            }

        }

        $return = array(
            "success" => true,
        );

        if ($newCategoryReturn) {
            $return['newCategory'] = $newCategoryReturn;
        }

        return $return;
    }

    /**
     * Returns desription of method return values
     *
     * @return external_single_structure
     */
    public static function update_descriptor_category_returns() {
        return new external_single_structure(array(
            'success' => new external_value(PARAM_BOOL, 'true if successful'),
            'newCategory' => new external_single_structure(array(
                'id' => new external_value(PARAM_INT, 'id of new category'),
                'title' => new external_value(PARAM_TEXT, 'title of new category'),
                'source' => new external_value(PARAM_INT, 'cource of new category'),

            ), 'data of new category', VALUE_OPTIONAL),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_url_preview_parameters() {
        return new external_function_parameters(array(
            'url' => new external_value(PARAM_TEXT, 'url to fetch preview', VALUE_REQUIRED),
        ));
    }

    /**
     * gets title description and image of website
     *
     * @ws-type-read
     * @param string $url
     * @return array
     */
    public static function get_url_preview($url) {
        static::validate_parameters(static::get_url_preview_parameters(), array(
            'url' => $url,
        ));

        // disable errors on invalid html
        libxml_use_internal_errors(true);

        $dom = new DOMDocument;
        try {
            //            $dom->loadHTMLFile('https://www.nachrichten.at/oberoesterreich/oberoesterreicher-knackt-lotto-jackpot;art4,3257353');
            $dom->loadHTMLFile($url);

        } catch (Exception $e) {
        }

        if ($dom->documentElement) {

            $title = null;
            $imageUrl = null;
            $description = null;

            $metaElements = $dom->getElementsByTagName('meta');

            foreach ($metaElements as $metaElement) {
                $name = $metaElement->getAttribute("name") ?: $metaElement->getAttribute("property");
                $content = $metaElement->getAttribute("content");

                if ($name == "og:title") {
                    $title = $content;
                }

                if ($name == "description" || $name == "og:description") {
                    $description = $content;
                }

                if ($name == "og:image") {
                    $imageUrl = $content;
                }
            }

            if (empty($title)) {
                $titleElements = $dom->getElementsByTagName('title');
                $title = $titleElements->length ? utf8_decode($titleElements->item(0)->textContent) : null;
            }
            //
            //            echo $title;
            //            echo "\r\n" . $description;
            //            echo "\r\n" . $imageUrl;

            $return = array(
                "title" => $title,
                "description" => $description,
                "imageurl" => $imageUrl,
            );

            return $return;
        }
    }

    /**
     * Returns desription of method return values
     *
     * @return external_single_structure
     */
    public static function get_url_preview_returns() {
        return new external_single_structure(array(
            "title" => new external_value(PARAM_TEXT, 'true if successful'),
            "description" => new external_value(PARAM_TEXT, 'true if successful'),
            "imageurl" => new external_value(PARAM_TEXT, 'true if successful'),
        ));
    }

    /**
     * Returns competence overview
     *
     * @return external_function_parameters
     *
     */
    public static function dakora_competencegrid_overview_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, 'id of course'),
            'userid' => new external_value(PARAM_INT, 'id of user, if 0 current user'),
            'subjectid' => new external_value(PARAM_INT, 'subject id'),
            'forall' => new external_value(PARAM_INT, 'for all?', VALUE_DEFAULT, 0),
        ));
    }

    /**
     * view competence overview
     *
     * @ws-type-read
     * @param integer $courseid
     * @param integer $userid
     * @param integer $subjectid
     * @param integer $forall
     * @return array
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     */
    public static function dakora_competencegrid_overview($courseid = 0, $userid = 0, $subjectid = 0, $forall = 0) {
        global $USER;

        if (empty($courseid)) {
            throw new invalid_parameter_exception ('Parameter courseid can not be empty');
        }
        if (empty($subjectid)) {
            throw new invalid_parameter_exception ('Parameter subjectid can not be empty');
        }

        static::validate_parameters(static::dakora_competencegrid_overview_parameters(), array(
            'courseid' => $courseid,
            'subjectid' => $courseid,
            'userid' => $courseid,
            'forall' => $forall,
        ));

        $isTeacher = block_exacomp_is_teacher($courseid, $USER->id);
        if (!($userid > 0) && !$isTeacher) {
            // overview for student (self view)
            $userid = $USER->id;
        }
        // if $forall - display overview for all users
        if ($forall) {
            $userid = 0;
        }

        $output = block_exacomp_get_renderer();

        list($niveaus, $skills, $subjects, $data, $selection) = block_exacomp_init_competence_grid_data($courseid,
            $subjectid,
            $userid,
            (@block_exacomp_get_settings_by_course($courseid)->show_all_examples != 0 || $isTeacher),
            block_exacomp_get_settings_by_course($courseid)->filteredtaxonomies);

        $overview = $output->competence_grid($niveaus, $skills, $subjects, $data, $selection, $courseid, $userid, $subjectid, 'dakora');

        return ['overview' => $overview];
    }

    /**
     * Returns html table with competence overview
     *
     * @return external_single_structure
     */
    public static function dakora_competencegrid_overview_returns() {
        return new external_single_structure(array(
            'overview' => new external_value(PARAM_RAW, 'result html'),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function dakora_delete_custom_example_parameters() {
        return new external_function_parameters(array(
            'exampleid' => new external_value(PARAM_INT, 'id of example'),
        ));
    }

    /**
     * delete example
     *
     * @ws-type-write
     * @param integer $exampleid
     * @return
     *
     */
    public static function dakora_delete_custom_example($exampleid) {
        global $DB, $USER;

        static::validate_parameters(static::dakora_delete_custom_example_parameters(), array(
            'exampleid' => $exampleid,
        ));

        // only self-created!
        $example = $DB->get_record(BLOCK_EXACOMP_DB_EXAMPLES, array('id' => $exampleid, 'creatorid' => $USER->id));
        if (!$example) {
            throw new invalid_parameter_exception ('Can not delete this example!');
        }

        block_exacomp_delete_custom_example($exampleid);

        return array(
            "success" => true,
        );
    }

    /**
     * Returns desription of method return values
     *
     * @return external_single_structure
     */
    public static function dakora_delete_custom_example_returns() {
        return new external_single_structure(array(
            'success' => new external_value(PARAM_BOOL, 'true if successful'),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function diggrplus_delete_custom_example_parameters() {
        return new external_function_parameters(array(
            'exampleid' => new external_value(PARAM_INT, 'id of example'),
        ));
    }

    /**
     * delete example
     *
     * @ws-type-write
     * @param integer $exampleid
     * @return
     *
     */
    public static function diggrplus_delete_custom_example($exampleid) {
        global $DB, $USER;

        static::validate_parameters(static::diggrplus_delete_custom_example_parameters(), array(
            'exampleid' => $exampleid,
        ));

        // only self-created!
        $example = $DB->get_record(BLOCK_EXACOMP_DB_EXAMPLES, array('id' => $exampleid, 'creatorid' => $USER->id));
        if (!$example) {
            throw new invalid_parameter_exception ('Can not delete this example!');
        }

        block_exacomp_delete_custom_example($exampleid);

        return array(
            "success" => true,
        );
    }

    /**
     * Returns desription of method return values
     *
     * @return external_single_structure
     */
    public static function diggrplus_delete_custom_example_returns() {
        return new external_single_structure(array(
            'success' => new external_value(PARAM_BOOL, 'true if successful'),
        ));
    }

    public static function diggrplus_get_course_schooltype_tree_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT),
        ));
    }

    /**
     * @ws-type-read
     */
    public static function diggrplus_get_course_schooltype_tree($courseid) {
        [
            'courseid' => $courseid,
        ] = static::validate_parameters(static::diggrplus_get_course_schooltype_tree_parameters(), [
            'courseid' => $courseid,
        ]);

        block_exacomp_require_teacher($courseid);

        $schooltypes = block_exacomp_build_schooltype_tree_for_courseselection(0);
        $active_topics = block_exacomp_get_topics_by_subject($courseid, 0, true);

        foreach ($schooltypes as $schooltypeKey => $schooltype) {
            foreach ($schooltype->subjects as $subjectKey => $subject) {
                if ($subject->disabled || $schooltype->disabled) {
                    // nur anzeigen, wenn topics davon ausgewählt wurden
                    $activeCount = count(array_filter($subject->topics, function($topic) use ($active_topics) {
                        return !empty($active_topics[$topic->id]);
                    }));
                    if ($activeCount == 0) {
                        // remove subject (filter it out)
                        unset($schooltype->subjects[$subjectKey]);
                        continue;
                    }
                }

                foreach ($subject->topics as $topic) {
                    // some topics have html in the title, and moodle does not allow this?!?
                    $topic->title = strip_tags($topic->title);

                    $topic->active = !empty($active_topics[$topic->id]);
                }
            }

            if (!$schooltype->subjects) {
                // remove schooltype, if no subjects are available (all were filtered before)
                unset($schooltypes[$schooltypeKey]);
            }
        }

        return ['schooltypes' => $schooltypes];
    }

    public static function diggrplus_get_course_schooltype_tree_returns() {
        return new external_single_structure(array(
            'schooltypes' => new external_multiple_structure(new external_single_structure(array(
                'id' => new external_value(PARAM_INT),
                'title' => new external_value(PARAM_TEXT, 'schooltype title'),
                'subjects' => new external_multiple_structure(new external_single_structure(array(
                    'id' => new external_value(PARAM_INT),
                    'title' => new external_value(PARAM_TEXT, 'subject title'),
                    'topics' => new external_multiple_structure(new external_single_structure(array(
                        'id' => new external_value(PARAM_INT),
                        'title' => new external_value(PARAM_TEXT, 'topic title'),
                        'active' => new external_value(PARAM_BOOL),
                    ))),
                ))),
            ))),
        ));
    }

    public static function diggrplus_set_active_course_topics_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT),
            'topicids' => new external_multiple_structure(
                new external_value(PARAM_INT), 'topicid optional so it can be empty', VALUE_DEFAULT, []
            ),
            'hide_new_examples' => new external_value(PARAM_BOOL),
        ));
    }

    /**
     * @ws-type-write
     */
    public static function diggrplus_set_active_course_topics($courseid, $topicids = [], $hide_new_examples = null) {
        static::validate_parameters(static::diggrplus_set_active_course_topics_parameters(), array(
            'courseid' => $courseid,
            'topicids' => $topicids,
            'hide_new_examples' => $hide_new_examples,
        ));

        global $DB;

        block_exacomp_require_teacher($courseid);

        $oldTopicIds = $DB->get_records_menu(BLOCK_EXACOMP_DB_COURSETOPICS, array("courseid" => $courseid), '', 'id, topicid');

        block_exacomp_set_coursetopics($courseid, $topicids, true);

        if ($hide_new_examples) {
            $newTopicIds = $DB->get_records_menu(BLOCK_EXACOMP_DB_COURSETOPICS, array("courseid" => $courseid), '', 'id, topicid');
            $addedTopicIds = array_diff($newTopicIds, $oldTopicIds);

            $examples = block_exacomp_get_examples_by_course($courseid, true, '', false);
            foreach ($examples as $example) {
                if (in_array($example->topicid, $addedTopicIds)) {
                    // is an example from a newly activated topic
                    block_exacomp_set_example_visibility($example->id, $courseid, false, 0);
                }
            }
        }

        // invalidate cache
        // $cache = \cache::make('block_exacomp', 'course_topics_configured');
        // $cache->delete($courseid);

        return array("success" => true);
    }

    public static function diggrplus_set_active_course_topics_returns() {
        return new external_single_structure(array(
            'success' => new external_value(PARAM_BOOL, 'status'),
        ));
    }

    public static function diggrplus_get_config_parameters() {
        return new external_function_parameters(array());
    }

    /**
     *
     * @ws-type-read
     * @return array
     */
    public static function diggrplus_get_config() {
        global $CFG;
        static::validate_parameters(static::diggrplus_get_config_parameters(), array());

        $info_block_exacomp = core_plugin_manager::instance()->get_plugin_info('block_exacomp');
        $info_block_enrolcode = core_plugin_manager::instance()->get_plugin_info('block_enrolcode');
        $msteams_client_id = get_config("exacomp", 'msteams_client_id');

        $plugin_names = ['block_exacomp', 'mod_hvp'];
        $plugins = [];
        foreach ($plugin_names as $plugin_name) {
            $info = core_plugin_manager::instance()->get_plugin_info($plugin_name);

            $plugins[] = [
                'name' => $plugin_name,
                'versiondb' => $info->versiondb,
            ];
        }

        return array(
            'exacompversion' => $info_block_exacomp->versiondb,
            'moodleversion' => $CFG->version,
            'msteams_import_enabled' => !!trim($msteams_client_id),
            'msteams_azure_app_client_id' => $msteams_client_id,
            'enrolcode_enabled' => !!$info_block_enrolcode,
            'example_upload_global' => get_config('exacomp', 'example_upload_global'),
            'plugins' => $plugins,
        );
    }

    /**
     * Returns description of method return values
     *
     * @return external_single_structure
     */
    public static function diggrplus_get_config_returns() {
        return new external_single_structure(array(
            'exacompversion' => new external_value(PARAM_FLOAT, 'exacomp version number in YYYYMMDDXX format'),
            'moodleversion' => new external_value(PARAM_FLOAT, 'moodle version number in YYYYMMDDXX format'),
            'msteams_import_enabled' => new external_value(PARAM_BOOL, ''),
            'msteams_azure_app_client_id' => new external_value(PARAM_TEXT, ''),
            'enrolcode_enabled' => new external_value(PARAM_BOOL, ''),
            'example_upload_global' => new external_value(PARAM_BOOL, ''),
            'plugins' => new external_multiple_structure(new external_single_structure(array(
                'name' => new external_value(PARAM_TEXT),
                'versiondb' => new external_value(PARAM_FLOAT, '', VALUE_OPTIONAL),
            ))),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function diggrplus_annotate_example_parameters() {
        return new external_function_parameters(array(
            'exampleid' => new external_value(PARAM_INT, 'id of the example that is to be updated', VALUE_DEFAULT, -1),
            'courseid' => new external_value(PARAM_INT, 'courseid', VALUE_DEFAULT, 0),
            'annotationtext' => new external_value(PARAM_TEXT, 'title of example', VALUE_DEFAULT, ''),
            //            'visible' =>  new external_value(PARAM_BOOL, 'is the example visible for all or not?'),
        ));
    }

    /**
     * Create an example or update it
     * create example
     *
     * @ws-type-write
     *
     * @return array
     */
    public static function diggrplus_annotate_example($exampleid, $courseid, $annotationtext) {
        static::validate_parameters(static::diggrplus_annotate_example_parameters(), array(
            'exampleid' => $exampleid,
            'courseid' => $courseid,
            'annotationtext' => $annotationtext,
            //            'visible' => $visible
        ));
        global $DB;
        block_exacomp_require_teacher($courseid);

        $annotationtext = trim($annotationtext);

        $exampleannotation = $DB->get_record(BLOCK_EXACOMP_DB_EXAMPLE_ANNOTATION, array('exampleid' => $exampleid, 'courseid' => $courseid));
        if ($exampleannotation) {
            $exampleannotation->annotationtext = $annotationtext;
            $DB->update_record(BLOCK_EXACOMP_DB_EXAMPLE_ANNOTATION, $exampleannotation);
        } else {
            $exampleannotation = new stdClass();
            $exampleannotation->courseid = $courseid;
            $exampleannotation->exampleid = $exampleid;
            $exampleannotation->annotationtext = $annotationtext;
            $DB->insert_record(BLOCK_EXACOMP_DB_EXAMPLE_ANNOTATION, $exampleannotation);
        }

        // Set Visibility in this course
        //        $insert = new stdClass();
        //        $insert->courseid = $courseid;
        //        $insert->exampleid = $exampleid;
        //        $insert->studentid = 0; // forall
        //        $insert->visible = $visible;
        //        g::$DB->insert_or_update_record(BLOCK_EXACOMP_DB_EXAMPVISIBILITY, $insert);

        return array("success" => true);
    }

    /**
     * Returns desription of method return values
     *
     * @return external_single_structure
     */
    public static function diggrplus_annotate_example_returns() {
        return new external_single_structure(array(
            'success' => new external_value(PARAM_BOOL, 'status'),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function diggrplus_get_student_enrolcode_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, '', VALUE_REQUIRED),
        ));
    }

    /**
     * get active code for student enrollment
     *
     * @ws-type-read
     *
     * @return array
     */
    public static function diggrplus_get_student_enrolcode($courseid) {
        static::validate_parameters(static::diggrplus_get_student_enrolcode_parameters(), array(
            'courseid' => $courseid,
        ));
        global $DB;

        block_exacomp_require_teacher($courseid);

        // get latest code which is still valid
        $oldcodes = $DB->get_records_sql(
            "SELECT * FROM {block_enrolcode} WHERE courseid=? AND roleid=? AND maturity>=? ORDER BY maturity DESC",
            array($courseid, block_exacomp_get_student_roleid(), time())
        );
        $lastCode = current($oldcodes);

        return array("code" => @$lastCode->code, 'valid_until' => @$lastCode->maturity);
    }

    /**
     * Returns desription of method return values
     *
     * @return external_single_structure
     */
    public static function diggrplus_get_student_enrolcode_returns() {
        return new external_single_structure(array(
            'code' => new external_value(PARAM_TEXT, ''),
            'valid_until' => new external_value(PARAM_INT, ''),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function diggrplus_create_student_enrolcode_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, '', VALUE_REQUIRED),
        ));
    }

    /**
     * Create new enrolcode and delete old ones
     *
     * @ws-type-write
     *
     * @return array
     */
    public static function diggrplus_create_student_enrolcode($courseid) {
        static::validate_parameters(static::diggrplus_create_student_enrolcode_parameters(), array(
            'courseid' => $courseid,
        ));

        global $DB, $CFG;

        block_exacomp_require_teacher($courseid);

        $roleid = block_exacomp_get_student_roleid();
        $maturity = time() + 60 * 60 * 24 * 7;

        // delete old codes
        $DB->delete_records('block_enrolcode', array('roleid' => $roleid, 'courseid' => $courseid));

        // create new code
        require_once $CFG->dirroot . '/blocks/enrolcode/locallib.php';
        $code = block_enrolcode_lib::create_code($courseid, $roleid, 0, 1, $maturity, 0, 0);

        return array("code" => $code, 'valid_until' => $maturity);
    }

    /**
     * Returns desription of method return values
     *
     * @return external_single_structure
     */
    public static function diggrplus_create_student_enrolcode_returns() {
        return new external_single_structure(array(
            'code' => new external_value(PARAM_TEXT, ''),
            'valid_until' => new external_value(PARAM_INT, ''),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function diggrplus_enrol_by_enrolcode_parameters() {
        return new external_function_parameters(array(
            'code' => new external_value(PARAM_TEXT, '', VALUE_REQUIRED),
        ));
    }

    /**
     * Use a QR-Code to enrol
     *
     * @ws-type-write
     *
     * @return array
     */
    public static function diggrplus_enrol_by_enrolcode($code) {
        static::validate_parameters(static::diggrplus_enrol_by_enrolcode_parameters(), array(
            'code' => $code,
        ));
        global $CFG;

        require_once $CFG->dirroot . '/blocks/enrolcode/locallib.php';
        $courseid = block_enrolcode_lib::enrol_by_code($code);

        if (!$courseid) {
            return ['success' => false];
        }

        return array("success" => true);
    }

    /**
     * Returns desription of method return values
     *
     * @return external_single_structure
     */
    public static function diggrplus_enrol_by_enrolcode_returns() {
        return new external_single_structure(array(
            'success' => new external_value(PARAM_BOOL, 'status'),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function diwipass_get_sections_with_materials_parameters() {
        return new external_function_parameters(array());
    }

    /**
     * Get urls and resources per section for every course of current user
     *
     * @ws-type-write
     *
     * @return array
     */
    public static function diwipass_get_sections_with_materials() {
        static::validate_parameters(static::diwipass_get_sections_with_materials_parameters(), array());
        global $USER;

        //        block_exacomp_require_teacher($courseid); TODO: only for teacher?
        //        $quizzes = mod_quiz_external::get_quizzes_by_courses(); // dont get the quizzes, but the sections
        require_login(); // TODO: useless check?

        // Get courses, then for each course get sections with materials
        //        $courses = static::get_courses($USER->id);
        $courses = enrol_get_my_courses();

        foreach ($courses as $key => $course) {
            $modinfo = get_fast_modinfo($course->id);
            $sections = $modinfo->get_section_info_all();
            $courses[$key]->name = $course->fullname;
            //            $urls = mod_url_external::get_urls_by_courses(array($course->id));
            // get urls without caring about visibility:
            $urls = get_all_instances_in_courses("url", array($course->id => $course), $USER->id, true);
            $returnedurls = array();
            foreach ($urls as $url) {
                $context = context_module::instance($url->coursemodule);
                // Entry to return.
                $url->name = external_format_string($url->name, $context->id);

                $options = array('noclean' => true);
                list($url->intro, $url->introformat) =
                    external_format_text($url->intro, $url->introformat, $context->id, 'mod_url', 'intro', null, $options);
                $url->introfiles = external_util::get_area_files($context->id, 'mod_url', 'intro', false, false);

                $returnedurls[] = $url;
            }
            $urls = $returnedurls;

            //            $resources = mod_resource_external::get_resources_by_courses(array($course->id));
            // get resources without caring about visibility:
            $resources = get_all_instances_in_courses("resource", array($course->id => $course), $USER->id, true);
            $returnedresources = array();
            foreach ($resources as $resource) {
                $context = context_module::instance($resource->coursemodule);
                // Entry to return.
                $resource->name = external_format_string($resource->name, $context->id);
                $options = array('noclean' => true);
                list($resource->intro, $resource->introformat) =
                    external_format_text($resource->intro, $resource->introformat, $context->id, 'mod_resource', 'intro', null,
                        $options);
                $resource->introfiles = external_util::get_area_files($context->id, 'mod_resource', 'intro', false, false);
                $resource->contentfiles = external_util::get_area_files($context->id, 'mod_resource', 'content');

                $returnedresources[] = $resource;
            }
            $resources = $returnedresources;

            foreach ($sections as $sectionkey => $section) {
                $sections[$sectionkey]->urls = array();
                $sections[$sectionkey]->resources = array();
                $sections[$sectionkey]->name = $section->name; // Otherwise it will not be returned since the value is stored in _name, not in name... ->name is a "getter" not a field
            }
            // distribute the urls and resources to the correct sections
            foreach ($urls as $url) {
                $sections[$url->section]->urls[] = $url;
            }
            foreach ($resources as $resource) {
                foreach ($resource->contentfiles as $contentfilekey => $contentfile) {
                    $resource->contentfiles[$contentfilekey]["fileurl"] = str_replace("webservice/pluginfile", "blocks/exacomp/pluginfile_resource", $contentfile["fileurl"]);
                    // this is done to use this custom pluginfile.php specifically for diggr. The difference is: It allows opening resources that are still hidden in moodle.
                }
                $sections[$resource->section]->resources[] = $resource;
            }
            $courses[$key]->sections = $sections;
        }

        return array("courses" => $courses);
    }

    /**
     * Returns desription of method return values
     *
     * @return external_single_structure
     */
    public static function diwipass_get_sections_with_materials_returns() {
        return new external_single_structure(array(
            'courses' => new external_multiple_structure(new external_single_structure(array(
                'name' => new external_value(PARAM_TEXT, 'course name'),
                'sections' => new external_multiple_structure(new external_single_structure(array(
                    'name' => new external_value(PARAM_TEXT, 'section name', VALUE_DEFAULT, "sectionname missing"),
                    'resources' => new external_multiple_structure(new external_single_structure(array(
                        'id' => new external_value(PARAM_INT, 'Module id'),
                        'coursemodule' => new external_value(PARAM_INT, 'Course module id'),
                        'course' => new external_value(PARAM_INT, 'Course id'),
                        'name' => new external_value(PARAM_RAW, 'Page name'),
                        'intro' => new external_value(PARAM_RAW, 'Summary'),
                        'introformat' => new external_format_value('intro', 'Summary format'),
                        'introfiles' => new external_files('Files in the introduction text'),
                        'contentfiles' => new external_files('Files in the content'),
                    ))),
                    'urls' => new external_multiple_structure(new external_single_structure(array(
                        'id' => new external_value(PARAM_INT, 'Module id'),
                        'coursemodule' => new external_value(PARAM_INT, 'Course module id'),
                        'course' => new external_value(PARAM_INT, 'Course id'),
                        'name' => new external_value(PARAM_RAW, 'URL name'),
                        'intro' => new external_value(PARAM_RAW, 'Summary'),
                        'introformat' => new external_format_value('intro', 'Summary format'),
                        'introfiles' => new external_files('Files in the introduction text'),
                        'externalurl' => new external_value(PARAM_RAW_TRIMMED, 'External URL'),
                        'display' => new external_value(PARAM_INT, 'How to display the url'),
                        'displayoptions' => new external_value(PARAM_RAW, 'Display options (width, height)'),
                        'parameters' => new external_value(PARAM_RAW, 'Parameters to append to the URL'),
                        'timemodified' => new external_value(PARAM_INT, 'Last time the url was modified'),
                        'section' => new external_value(PARAM_INT, 'Course section id'),
                        'visible' => new external_value(PARAM_INT, 'Module visibility'),
                        'groupmode' => new external_value(PARAM_INT, 'Group mode'),
                        'groupingid' => new external_value(PARAM_INT, 'Grouping id'),
                    ))),
                ))),
            ))),
        ));
    }

    protected static function get_example_item($userid, $exampleid) {
        global $DB;

        $sql = 'SELECT i.*, ie.status, ie.teachervalue, ie.studentvalue
              FROM {block_exacompexamples} d
                JOIN {' . BLOCK_EXACOMP_DB_ITEM_MM . '} ie ON ie.exacomp_record_id = d.id
                JOIN {block_exaportitem} i ON ie.itemid = i.id
              WHERE i.userid = :userid
                AND d.id = :compid
                AND ie.competence_type = :comptype
              ORDER BY ie.timecreated DESC';
        $params["userid"] = $userid;
        $params["compid"] = $exampleid;
        $params["comptype"] = BLOCK_EXACOMP_TYPE_EXAMPLE;
        $item = current($DB->get_records_sql($sql, $params));

        return $item;
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function dakoraplus_get_example_and_item_parameters() {
        return new external_function_parameters(array(
            'exampleid' => new external_value(PARAM_INT, ''),
            'courseid' => new external_value(PARAM_INT, ''),
        ));
    }

    /**
     * @ws-type-read
     * @return array of items
     */
    public static function dakoraplus_get_example_and_item($exampleid, $courseid) {
        global $USER, $DB;

        static::validate_parameters(static::dakoraplus_get_example_and_item_parameters(), array(
            'exampleid' => $exampleid,
            'courseid' => $courseid,
        ));

        $studentid = $USER->id;

        $example = static::get_example_by_id($exampleid, $courseid);

        $item = current(block_exacomp_get_items_for_competence($studentid, $example->id, BLOCK_EXACOMP_TYPE_EXAMPLE));
        if (!$item) {
            // hack daniel, freie materialien haben keine deskriptoren, darum liefert block_exacomp_get_items_for_competence keine werte
            $item = static::get_example_item($studentid, $example->id);
        }
        if ($item) {
            static::block_exacomp_get_item_details($item, $studentid, static::wstoken());
        }

        $exampleAndItem = new stdClass();
        $exampleAndItem->courseid = $item->courseid ?: $courseid;
        $exampleAndItem->example = $example;
        if ($item) {
            // get info from item
            $exampleAndItem->item = $item;
            $exampleAndItem->subjecttitle = $item->subjecttitle;
            $exampleAndItem->subjectid = $item->subjectid;
            $exampleAndItem->topictitle = $item->topictitle ? $item->topictitle : "";
            $exampleAndItem->topicid = $item->topicid ? $item->topicid : 0;
        } else {
            // get info from example-descriptor-topic-subject-relationship
            $result = current($DB->get_records_sql("SELECT DISTINCT topic.title as topictitle, topic.id as topicid, subj.title as subjecttitle, subj.id as subjectid
            FROM {" . BLOCK_EXACOMP_DB_DESCEXAMP . "} dex
            JOIN {" . BLOCK_EXACOMP_DB_DESCTOPICS . "} det ON dex.descrid = det.descrid
            JOIN {" . BLOCK_EXACOMP_DB_COURSETOPICS . "} ct ON det.topicid = ct.topicid
            JOIN {" . BLOCK_EXACOMP_DB_TOPICS . "} topic ON ct.topicid = topic.id
            JOIN {" . BLOCK_EXACOMP_DB_SUBJECTS . "} subj ON topic.subjid = subj.id
            JOIN {" . BLOCK_EXACOMP_DB_DESCRIPTORS . "} d ON det.descrid=d.id
            WHERE ct.courseid = :courseid AND dex.exampid = :exampleid", ['courseid' => $courseid, 'exampleid' => $exampleid], 0, 1));

            if ($result) {
                $exampleAndItem->subjecttitle = $result->subjecttitle;
                $exampleAndItem->subjectid = $result->subjectid;
                $exampleAndItem->topictitle = $result->topictitle;
                $exampleAndItem->topicid = $result->topicid;
            } else {
                $exampleAndItem->subjecttitle = '';
                $exampleAndItem->subjectid = 0;
                $exampleAndItem->topictitle = "";
                $exampleAndItem->topicid = 0;
            }
        }

        $niveauid = 0;
        $niveautitle = '';
        // for a single example, also read the niveau information, which is used later to fill the object
        $niveau_info = current($DB->get_records_sql("SELECT DISTINCT n.id as niveauid, n.title as niveautitle
            FROM {" . BLOCK_EXACOMP_DB_NIVEAUS . "} n
            JOIN {" . BLOCK_EXACOMP_DB_DESCRIPTORS . "} descr ON descr.niveauid = n.id
            JOIN {" . BLOCK_EXACOMP_DB_DESCEXAMP . "} dex ON dex.descrid = descr.id
            WHERE dex.exampid=? ORDER BY niveauid", [$exampleid]));
        if ($niveau_info) {
            $niveauid = $niveau_info->niveauid;
            $niveautitle = $niveau_info->niveautitle;
        }

        $exampleAndItem->niveauid = $niveauid;
        $exampleAndItem->niveautitle = $niveautitle;
        $exampleAndItem->timemodified = $item->timemodified;

        if ($exampleAndItem->example) {
            $example = $exampleAndItem->example;

            if (!property_exists($example, "annotation")) {
                $example->annotation = $DB->get_field(BLOCK_EXACOMP_DB_EXAMPLE_ANNOTATION, 'annotationtext', array('exampleid' => $example->id, 'courseid' => $courseid));
            }

            if (!(property_exists($example, "teacher_evaluation") || property_exists($example, "student_evaluation"))) {
                $exampleEvaluation = $DB->get_record(BLOCK_EXACOMP_DB_EXAMPLEEVAL, array("studentid" => $studentid, "courseid" => $courseid, "exampleid" => $example->id), "teacher_evaluation, student_evaluation");
                $example->teacher_evaluation = $exampleEvaluation->teacher_evaluation;
                $example->student_evaluation = $exampleEvaluation->student_evaluation;
            }
        }

        $exampleAndItem->status = block_exacomp_get_human_readable_item_status($exampleAndItem->item ? $exampleAndItem->item->status : null);

        return $exampleAndItem;
    }

    /**
     * Returns desription of method return values
     *
     * @return external_single_structure
     */
    public static function dakoraplus_get_example_and_item_returns() {
        return new external_single_structure(array(
            'courseid' => new external_value(PARAM_INT, ''),
            'status' => new external_value(PARAM_TEXT, 'ENUM(new, inprogress, submitted, completed)'),
            'subjectid' => new external_value(PARAM_INT, 'id of subject'),
            'subjecttitle' => new external_value(PARAM_TEXT, 'title of subject'),
            'topicid' => new external_value(PARAM_INT, 'id of topic'),
            'topictitle' => new external_value(PARAM_TEXT, 'title of topic'),

            'niveautitle' => new external_value(PARAM_TEXT, 'title of niveau'),
            'niveauid' => new external_value(PARAM_INT, 'id of niveau'),

            'timemodified' => new external_value(PARAM_INT, 'time the item was last modified --> not gradings, but only changes to the item (files, comments, name, collaborators)'),

            'example' => new external_single_structure(array(
                'id' => new external_value(PARAM_INT, 'id of example'),
                'title' => new external_value(PARAM_TEXT, 'title of example'),
                'description' => new external_value(PARAM_TEXT, 'description of example'),
                'annotation' => new external_value(PARAM_TEXT, 'annotation by the teacher for this example in this course'),
                //                'taskfileurl' => new external_value(PARAM_TEXT, 'task fileurl'),
                //                'taskfilenames' => new external_value(PARAM_TEXT, 'task filename'),
                'solutionfilename' => new external_value(PARAM_TEXT, 'task filename'),
                'externalurl' => new external_value(PARAM_TEXT, 'externalurl of example'),
                'externaltask' => new external_value(PARAM_TEXT, 'url of associated module'),
                //                'taskfilecount' => new external_value(PARAM_TEXT, 'number of files for the task'),
                'solution' => new external_value(PARAM_TEXT, 'solution(url/description) of example'),
                'timeframe' => new external_value(PARAM_TEXT, 'timeframe as string'),  //timeframe in minutes?? not anymore, it can be "4 hours" as well for example
                'hassubmissions' => new external_value(PARAM_BOOL, 'true if example has already submissions'),
                'solution_visible' => new external_value(PARAM_BOOL, 'visibility for example solution in current context'),
                //                'exampletaxonomies' => new external_value(PARAM_TEXT, 'taxonomies seperated by comma'),
                //                'exampletaxids' => new external_value(PARAM_TEXT, 'taxids seperated by comma'),

                'taskfiles' => new external_multiple_structure(new external_single_structure(array(
                    'name' => new external_value(PARAM_TEXT, 'title of taskfile'),
                    'url' => new external_value(PARAM_URL, 'file url'),
                    'type' => new external_value(PARAM_TEXT, 'mime type for file'),
                    //                    'fileindex' => new external_value(PARAM_TEXT, 'fileindex, used for deleting this file')
                )), 'taskfiles of the example', VALUE_OPTIONAL),

                'teacher_evaluation' => new external_value(PARAM_INT, 'teacher_evaluation', VALUE_OPTIONAL),
                'student_evaluation' => new external_value(PARAM_INT, 'student_evaluation', VALUE_OPTIONAL),
            ), 'example information', VALUE_OPTIONAL),
            'item' => new external_single_structure(array(
                'id' => new external_value(PARAM_INT, 'id of item '),
                'name' => new external_value(PARAM_TEXT, 'title of item'),
                'solutiondescription' => new external_value(PARAM_TEXT, 'description of item', VALUE_OPTIONAL),
                'type' => new external_value(PARAM_TEXT, 'type of item ENUM(note,file,link)', VALUE_OPTIONAL),
                'url' => new external_value(PARAM_TEXT, 'url', VALUE_OPTIONAL),
                'effort' => new external_value(PARAM_RAW, 'description of the effort', VALUE_OPTIONAL),
                //                'status' => new external_value(PARAM_INT, 'status of the submission', VALUE_OPTIONAL),
                'teachervalue' => new external_value(PARAM_INT, 'teacher grading', VALUE_OPTIONAL),
                'studentvalue' => new external_value(PARAM_INT, 'student grading', VALUE_OPTIONAL),
                'teachercomment' => new external_value(PARAM_TEXT, 'teacher comment', VALUE_OPTIONAL),
                'studentcomment' => new external_value(PARAM_TEXT, 'student comment', VALUE_OPTIONAL),
                // 'owner' => new external_single_structure(array(
                //     'userid' => new external_value(PARAM_INT, ''),
                //     'fullname' => new external_value(PARAM_TEXT, ''),
                //     'profileimageurl' => new external_value(PARAM_TEXT, ''),
                // )),
                'studentfiles' => new external_multiple_structure(new external_single_structure(array(
                    'id' => new external_value(PARAM_INT, 'id'),
                    'filename' => new external_value(PARAM_TEXT, 'filename'),
                    'file' => new external_value(PARAM_URL, 'file url'),
                    'mimetype' => new external_value(PARAM_TEXT, 'mime type for file'),
                    'fileindex' => new external_value(PARAM_TEXT, 'fileindex, used for deleting this file'),
                )), "files of the student's submission", VALUE_OPTIONAL),
                'collaborators' => new external_multiple_structure(new external_single_structure(array(
                    'userid' => new external_value(PARAM_INT, 'userid of collaborator'),
                    'fullname' => new external_value(PARAM_TEXT, ''),
                    'profileimageurl' => new external_value(PARAM_TEXT, ''),
                )), 'collaborators', VALUE_OPTIONAL),
            ), 'item information', VALUE_OPTIONAL),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function dakoraplus_get_teacher_example_and_item_parameters() {
        return new external_function_parameters(array(
            'exampleid' => new external_value(PARAM_INT, ''),
            'courseid' => new external_value(PARAM_INT, ''),
            'studentid' => new external_value(PARAM_INT, ''),
        ));
    }

    /**
     * @ws-type-read
     * @return item
     */
    public static function dakoraplus_get_teacher_example_and_item($exampleid, $courseid, $studentid) {
        global $USER, $DB;

        static::validate_parameters(static::dakoraplus_get_teacher_example_and_item_parameters(), array(
            'exampleid' => $exampleid,
            'courseid' => $courseid,
            'studentid' => $studentid,
        ));

        block_exacomp_require_teacher($courseid);

        $teacherid = $USER->id;

        $example = static::get_example_by_id($exampleid, $courseid);

        $item = current(block_exacomp_get_items_for_competence($studentid, $example->id, BLOCK_EXACOMP_TYPE_EXAMPLE));
        if (!$item) {
            // hack daniel, freie materialien haben keine deskriptoren, darum liefert block_exacomp_get_items_for_competence keine werte
            $item = static::get_example_item($studentid, $example->id);
        }
        if ($item) {
            static::block_exacomp_get_item_details($item, $studentid, static::wstoken());
        }

        $exampleAndItem = new stdClass();
        $exampleAndItem->courseid = $item->courseid ?: $courseid;
        $exampleAndItem->example = $example;
        if ($item) {
            // get info from item
            $exampleAndItem->item = $item;
            $exampleAndItem->subjecttitle = $item->subjecttitle;
            $exampleAndItem->subjectid = $item->subjectid;
            $exampleAndItem->topictitle = $item->topictitle ? $item->topictitle : "";
            $exampleAndItem->topicid = $item->topicid ? $item->topicid : 0;
        } else {
            // get info from example-descriptor-topic-subject-relationship
            $result = current($DB->get_records_sql("SELECT DISTINCT topic.title as topictitle, topic.id as topicid, subj.title as subjecttitle, subj.id as subjectid
            FROM {" . BLOCK_EXACOMP_DB_DESCEXAMP . "} dex
            JOIN {" . BLOCK_EXACOMP_DB_DESCTOPICS . "} det ON dex.descrid = det.descrid
            JOIN {" . BLOCK_EXACOMP_DB_COURSETOPICS . "} ct ON det.topicid = ct.topicid
            JOIN {" . BLOCK_EXACOMP_DB_TOPICS . "} topic ON ct.topicid = topic.id
            JOIN {" . BLOCK_EXACOMP_DB_SUBJECTS . "} subj ON topic.subjid = subj.id
            JOIN {" . BLOCK_EXACOMP_DB_DESCRIPTORS . "} d ON det.descrid=d.id
            WHERE ct.courseid = :courseid AND dex.exampid = :exampleid", ['courseid' => $courseid, 'exampleid' => $exampleid], 0, 1));

            if ($result) {
                $exampleAndItem->subjecttitle = $result->subjecttitle;
                $exampleAndItem->subjectid = $result->subjectid;
                $exampleAndItem->topictitle = $result->topictitle;
                $exampleAndItem->topicid = $result->topicid;
            } else {
                $exampleAndItem->subjecttitle = '';
                $exampleAndItem->subjectid = 0;
                $exampleAndItem->topictitle = "";
                $exampleAndItem->topicid = 0;
            }
        }

        $niveauid = 0;
        $niveautitle = '';
        // for a single example, also read the niveau information, which is used later to fill the object
        $niveau_info = current($DB->get_records_sql("SELECT DISTINCT n.id as niveauid, n.title as niveautitle
            FROM {" . BLOCK_EXACOMP_DB_NIVEAUS . "} n
            JOIN {" . BLOCK_EXACOMP_DB_DESCRIPTORS . "} descr ON descr.niveauid = n.id
            JOIN {" . BLOCK_EXACOMP_DB_DESCEXAMP . "} dex ON dex.descrid = descr.id
            WHERE dex.exampid=? ORDER BY niveauid", [$exampleid]));
        if ($niveau_info) {
            $niveauid = $niveau_info->niveauid;
            $niveautitle = $niveau_info->niveautitle;
        }

        $exampleAndItem->niveauid = $niveauid;
        $exampleAndItem->niveautitle = $niveautitle;
        $exampleAndItem->timemodified = $item->timemodified;

        if ($exampleAndItem->item) {
            $student = g::$DB->get_record('user', array(
                'id' => $exampleAndItem->item->userid,
            ));

            if ($student) {
                $userpicture = new user_picture($student);
                $userpicture->size = 1; // Size f1.

                $exampleAndItem->item->owner = (object)[
                    'userid' => $student->id,
                    'fullname' => fullname($student),
                    'profileimageurl' => $userpicture->get_url(g::$PAGE)->out(false),
                ];
            }
        }

        if ($exampleAndItem->example) {
            $example = $exampleAndItem->example;

            if (!property_exists($example, "annotation")) {
                $example->annotation = $DB->get_field(BLOCK_EXACOMP_DB_EXAMPLE_ANNOTATION, 'annotationtext', array('exampleid' => $example->id, 'courseid' => $courseid));
            }

            if (!(property_exists($example, "teacher_evaluation") || property_exists($example, "student_evaluation"))) {
                $exampleEvaluation = $DB->get_record(BLOCK_EXACOMP_DB_EXAMPLEEVAL, array("studentid" => $studentid, "courseid" => $courseid, "exampleid" => $example->id), "teacher_evaluation, student_evaluation");
                $example->teacher_evaluation = $exampleEvaluation->teacher_evaluation;
                $example->student_evaluation = $exampleEvaluation->student_evaluation;
            }
        }

        $exampleAndItem->status = block_exacomp_get_human_readable_item_status($exampleAndItem->item ? $exampleAndItem->item->status : null);

        return $exampleAndItem;
    }

    /**
     * Returns desription of method return values
     *
     * @return external_single_structure
     */
    public static function dakoraplus_get_teacher_example_and_item_returns() {
        return new external_single_structure(array(
            'courseid' => new external_value(PARAM_INT, ''),
            'status' => new external_value(PARAM_TEXT, 'ENUM(new, inprogress, submitted, completed)'),
            'subjectid' => new external_value(PARAM_INT, 'id of subject'),
            'subjecttitle' => new external_value(PARAM_TEXT, 'title of subject'),
            'topicid' => new external_value(PARAM_INT, 'id of topic'),
            'topictitle' => new external_value(PARAM_TEXT, 'title of topic'),

            'niveautitle' => new external_value(PARAM_TEXT, 'title of niveau'),
            'niveauid' => new external_value(PARAM_INT, 'id of niveau'),

            'timemodified' => new external_value(PARAM_INT, 'time the item was last modified --> not gradings, but only changes to the item (files, comments, name, collaborators)'),

            'example' => new external_single_structure(array(
                'id' => new external_value(PARAM_INT, 'id of example'),
                'title' => new external_value(PARAM_TEXT, 'title of example'),
                'description' => new external_value(PARAM_TEXT, 'description of example'),
                'annotation' => new external_value(PARAM_TEXT, 'annotation by the teacher for this example in this course'),
                //                'taskfileurl' => new external_value(PARAM_TEXT, 'task fileurl'),
                //                'taskfilenames' => new external_value(PARAM_TEXT, 'task filename'),
                'solutionfilename' => new external_value(PARAM_TEXT, 'task filename'),
                'externalurl' => new external_value(PARAM_TEXT, 'externalurl of example'),
                'externaltask' => new external_value(PARAM_TEXT, 'url of associated module'),
                //                'taskfilecount' => new external_value(PARAM_TEXT, 'number of files for the task'),
                'solution' => new external_value(PARAM_TEXT, 'solution(url/description) of example'),
                'timeframe' => new external_value(PARAM_TEXT, 'timeframe as string'),  //timeframe in minutes?? not anymore, it can be "4 hours" as well for example
                'hassubmissions' => new external_value(PARAM_BOOL, 'true if example has already submissions'),
                'solution_visible' => new external_value(PARAM_BOOL, 'visibility for example solution in current context'),
                //                'exampletaxonomies' => new external_value(PARAM_TEXT, 'taxonomies seperated by comma'),
                //                'exampletaxids' => new external_value(PARAM_TEXT, 'taxids seperated by comma'),

                'taskfiles' => new external_multiple_structure(new external_single_structure(array(
                    'name' => new external_value(PARAM_TEXT, 'title of taskfile'),
                    'url' => new external_value(PARAM_URL, 'file url'),
                    'type' => new external_value(PARAM_TEXT, 'mime type for file'),
                    //                    'fileindex' => new external_value(PARAM_TEXT, 'fileindex, used for deleting this file')
                )), 'taskfiles of the example', VALUE_OPTIONAL),

                'teacher_evaluation' => new external_value(PARAM_INT, 'teacher_evaluation', VALUE_OPTIONAL),
                'student_evaluation' => new external_value(PARAM_INT, 'student_evaluation', VALUE_OPTIONAL),
            ), 'example information', VALUE_OPTIONAL),
            'item' => new external_single_structure(array(
                'id' => new external_value(PARAM_INT, 'id of item '),
                'name' => new external_value(PARAM_TEXT, 'title of item'),
                'solutiondescription' => new external_value(PARAM_TEXT, 'description of item', VALUE_OPTIONAL),
                'type' => new external_value(PARAM_TEXT, 'type of item ENUM(note,file,link)', VALUE_OPTIONAL),
                'url' => new external_value(PARAM_TEXT, 'url', VALUE_OPTIONAL),
                'effort' => new external_value(PARAM_RAW, 'description of the effort', VALUE_OPTIONAL),
                //                'status' => new external_value(PARAM_INT, 'status of the submission', VALUE_OPTIONAL),
                'teachervalue' => new external_value(PARAM_INT, 'teacher grading', VALUE_OPTIONAL),
                'studentvalue' => new external_value(PARAM_INT, 'student grading', VALUE_OPTIONAL),
                'teachercomment' => new external_value(PARAM_TEXT, 'teacher comment', VALUE_OPTIONAL),
                'studentcomment' => new external_value(PARAM_TEXT, 'student comment', VALUE_OPTIONAL),
                'owner' => new external_single_structure(array(
                    'userid' => new external_value(PARAM_INT, ''),
                    'fullname' => new external_value(PARAM_TEXT, ''),
                    'profileimageurl' => new external_value(PARAM_TEXT, ''),
                ), '', VALUE_OPTIONAL),
                'studentfiles' => new external_multiple_structure(new external_single_structure(array(
                    'id' => new external_value(PARAM_INT, 'id'),
                    'filename' => new external_value(PARAM_TEXT, 'filename'),
                    'file' => new external_value(PARAM_URL, 'file url'),
                    'mimetype' => new external_value(PARAM_TEXT, 'mime type for file'),
                    'fileindex' => new external_value(PARAM_TEXT, 'fileindex, used for deleting this file'),
                )), "files of the student's submission", VALUE_OPTIONAL),
                'collaborators' => new external_multiple_structure(new external_single_structure(array(
                    'userid' => new external_value(PARAM_INT, 'userid of collaborator'),
                    'fullname' => new external_value(PARAM_TEXT, ''),
                    'profileimageurl' => new external_value(PARAM_TEXT, ''),
                )), 'collaborators', VALUE_OPTIONAL),
            ), 'item information', VALUE_OPTIONAL),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function dakoraplus_save_coursesettings_parameters() {
        return new external_function_parameters(array(
            'courseid' => new external_value(PARAM_INT, ''),
            'assessment_config' => new external_value(PARAM_INT, '', VALUE_DEFAULT),
        ));
    }

    /**
     * @ws-type-write
     * @return success
     */
    public static function dakoraplus_save_coursesettings($courseid, $assessment_config = null) {
        static::validate_parameters(static::dakoraplus_save_coursesettings_parameters(), array(
            'courseid' => $courseid,
            'assessment_config' => $assessment_config,
        ));

        block_exacomp_require_teacher($courseid);

        if ($assessment_config !== null) {
            if ($assessment_config) {
                // check if is available
                $assessment_configurations = block_exacomp_get_assessment_configurations();
                if (empty($assessment_configurations[$assessment_config])) {
                    throw new invalid_parameter_exception ("assessment config with id '{$assessment_config}' not found");
                }
            }

            $settings = block_exacomp_get_settings_by_course($courseid);
            $settings->assessmentconfiguration = $assessment_config;
            $settings->filteredtaxonomies = json_encode($settings->filteredtaxonomies); // TODO: why like this? Is this done at every location? Then why not in the function.. copied from edit_course.php
            block_exacomp_save_coursesettings($courseid, $settings);
        }

        return array("success" => true);
    }

    /**
     * Returns desription of method return values
     *
     * @return external_single_structure
     */
    public static function dakoraplus_save_coursesettings_returns() {
        return new external_single_structure(array(
            'success' => new external_value(PARAM_BOOL, 'status'),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function dakoraplus_get_learning_diary_parameters() {
        return new external_function_parameters(array());
    }

    /**
     * @ws-type-read
     * @return array
     */
    public static function dakoraplus_get_learning_diary() {
        global $USER, $DB;

        $category = $DB->get_record('block_exaportcate', ['userid' => $USER->id, 'name' => 'Lerntagebuch'], '*', IGNORE_MULTIPLE);
        if (!$category) {
            return [];
        }

        $items = $DB->get_records('block_exaportitem', ['userid' => $USER->id, 'type' => 'note', 'categoryid' => $category->id], 'timemodified DESC');

        array_walk($items, function($item) {
            $item->title = $item->name;
            $item->text = $item->intro;
        });

        return $items;
    }

    /**
     * Returns desription of method return values
     *
     * @return external_multiple_structure
     */
    public static function dakoraplus_get_learning_diary_returns() {
        return new external_multiple_structure(
            new external_single_structure(array(
                'id' => new external_value(PARAM_INT),
                'timecreated' => new external_value(PARAM_INT),
                'timemodified' => new external_value(PARAM_INT),
                'title' => new external_value(PARAM_TEXT),
                'text' => new external_value(PARAM_TEXT),
            ))
        );
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function dakoraplus_save_learning_diary_entry_parameters() {
        return new external_function_parameters(array(
            'id' => new external_value(PARAM_INT, '', VALUE_DEFAULT, 0),
            'title' => new external_value(PARAM_TEXT),
            'text' => new external_value(PARAM_TEXT),
        ));
    }

    /**
     * @ws-type-write
     * @return success
     */
    public static function dakoraplus_save_learning_diary_entry($id, $title, $text) {
        global $DB, $USER;

        static::validate_parameters(static::dakoraplus_save_learning_diary_entry_parameters(), array(
            'id' => $id,
            'title' => $title,
            'text' => $text,
        ));

        $category = $DB->get_record('block_exaportcate', ['userid' => $USER->id, 'name' => 'Lerntagebuch'], '*', IGNORE_MULTIPLE);
        if (!$category) {
            $category = new stdClass();
            $category->name = "Lerntagebuch";
            $category->description = "Erstellt in DakoraPlus";
            $category->userid = $USER->id;

            $category->id = $DB->insert_record('block_exaportcate', $category);
        }

        $newItem = new stdClass();
        $newItem->userid = $USER->id;
        $newItem->name = $title ?: date('Y-m-d');
        $newItem->intro = $text;
        $newItem->categoryid = $category->id;
        $newItem->type = 'note';
        $newItem->timemodified = time();

        if ($id) {
            $oldItem = $DB->get_record('block_exaportitem', ['userid' => $USER->id, 'id' => $id, 'type' => 'note', 'categoryid' => $category->id]);
            // check if is owner
            if (!$oldItem) {
                throw new invalid_parameter_exception ("learning_diary entry not found or not allowed");
            }

            $newItem->id = $id;
            $DB->update_record("block_exaportitem", $newItem);
        } else {
            $newItem->timecreated = time();

            $DB->insert_record('block_exaportitem', $newItem);
        }

        return array("success" => true);
    }

    /**
     * Returns desription of method return values
     *
     * @return external_single_structure
     */
    public static function dakoraplus_save_learning_diary_entry_returns() {
        return new external_single_structure(array(
            'success' => new external_value(PARAM_BOOL, 'status'),
        ));
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_lang_parameters() {
        return new external_function_parameters(array(
            'lang' => new external_value(PARAM_TEXT),
            'app' => new external_value(PARAM_TEXT),
        ));
    }

    /**
     * Get language definitions in json format for diggr-plus and dakora-plus apps
     *
     * @ws-type-read
     * @return success
     */
    public static function get_lang($lang, $app) {
        global $DB, $USER;

        static::validate_parameters(static::get_lang_parameters(), array(
            'lang' => $lang,
            'app' => $app,
        ));

        header("Content-Type: application/json");
        header('Access-Control-Allow-Origin: *');

        if (!in_array($app, ['diggr-plus', 'dakora-plus', 'setapp'])) {
            $data = ['error' => "app {$app} not allowed"];
        } else if (!preg_match('!^[a-z]+$!', $lang)) {
            $data = ['error' => "lang {$lang} not allowed"];
        } else {
            $langFile = __DIR__ . "/lang/{$lang}/{$app}.json";
            if (!file_exists($langFile)) {
                $data = ['info' => 'no lang data found'];
            } else {
                $data = json_decode(file_get_contents($langFile));
            }
        }

        echo json_encode($data);
        exit;
    }

    /**
     * Returns desription of method return values
     *
     * @return external_single_structure
     */
    public static function get_lang_returns() {
        return new external_single_structure(array(
            'string_id' => new external_value(PARAM_TEXT, 'translation'),
        ));
    }
}
