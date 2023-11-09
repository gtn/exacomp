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

use block_exacomp\globals as g;
use block_exacomp_permission_exception;
use invalid_parameter_exception;

defined('MOODLE_INTERNAL') || die();

require_once __DIR__ . '/../../inc.php';
require_once $CFG->libdir . '/externallib.php';

class base extends \external_api {
    static function require_can_access_course($courseid, $allcrosssubjects = 0) {
        global $USER;
        if ($courseid > 0) {
            $courseIds = [$courseid];
        } else if ($allcrosssubjects) { // check all cources where I am a teacher
            $courseIds = block_exacomp_get_courses_of_teacher($USER->id); // TODO: looks like this function is not working with userid!
        } else {
            $courseIds = [-1111]; // wrong crosssubject id, for secure
        }
        foreach ($courseIds as $courseid) {
            $course = g::$DB->get_record('course', ['id' => $courseid]);
            if (!$course) {
                throw new invalid_parameter_exception ('Course not found');
            }
            if (!can_access_course($course)) {
                throw new invalid_parameter_exception ('Not allowed to access this course');
            }
        }
    }

    protected static function require_can_access_user($userid) {
        // can view myself
        if ($userid == g::$USER->id) {
            return;
        }

        // check external trainers
        $isTrainer = g::$DB->get_record(BLOCK_EXACOMP_DB_EXTERNAL_TRAINERS, array(
            'trainerid' => g::$USER->id,
            'studentid' => $userid,
        ));
        if ($isTrainer) {
            return;
        }

        // check course teacher
        require_once g::$CFG->dirroot . '/lib/enrollib.php';
        $courses = enrol_get_users_courses(g::$USER->id, true);
        foreach ($courses as $course) {
            if (block_exacomp_is_teacher($course->id)) {
                $users = get_enrolled_users(block_exacomp_get_context_from_courseid($course->id));
                if (isset($users[$userid])) {
                    // ok
                    return;
                }
            }
        }

        throw new invalid_parameter_exception ('Not allowed to view other user');
    }

    /**
     * Used to check if current user is allowed to view the user(student) $userid
     *
     * @param int $courseid
     * @param int|object $userid
     * @throws invalid_parameter_exception
     */
    public static function require_can_access_course_user($courseid, $userid) {
        if ($courseid) {
            // because in webservice block_exacomp_get_descriptors_for_example $courseid = 0

            $course = g::$DB->get_record('course', ['id' => $courseid]);
            if (!$course) {
                throw new invalid_parameter_exception ('Course not found');
            }

            if (!can_access_course($course)) {
                throw new block_exacomp_permission_exception('Not allowed to access this course');
            }
        }

        // can view myself
        if ($userid == g::$USER->id) {
            return;
        }

        // teacher can view other users
        if (block_exacomp_is_teacher($courseid)) {
            if ($userid == 0) {
                return;
            }
            $users = get_enrolled_users(block_exacomp_get_context_from_courseid($courseid));
            if (isset($users[$userid])) {
                return;
            }
        }

        throw new block_exacomp_permission_exception('Not allowed to view other user');
    }

    /**
     * Used to check if current user is allowed to view the user(student) $userid but RETURNS TRUE-FALSE INSTEAD OF EXCEPTION
     *
     * @param int $courseid
     * @param int|object $userid
     */
    public static function can_access_course_user($courseid, $userid) {
        if ($courseid) {
            // because in webservice block_exacomp_get_descriptors_for_example $courseid = 0

            $course = g::$DB->get_record('course', ['id' => $courseid]);
            if (!$course) {
                throw new invalid_parameter_exception ('Course not found');
            }

            if (!can_access_course($course)) {
                return false;
            }
        }

        // can view myself
        if ($userid == g::$USER->id) {
            return true;
        }

        // teacher can view other users
        if (block_exacomp_is_teacher($courseid)) {
            if ($userid == 0) {
                return true;
            }
            $users = get_enrolled_users(block_exacomp_get_context_from_courseid($courseid));
            if (isset($users[$userid])) {
                return true;
            }
        }

        return false;
    }

    public static function custom_htmltrim($string) {
        //$string = strip_tags($string);
        $string = nl2br($string);
        $remove = array("\n", "\r\n", "\r", "<p>", "</p>", "<h1>", "</h1>", "<br>", "<br />", "<br/>", "<sup>", "</sup>");
        $string = str_replace($remove, ' ', $string); // new lines to space
        $string = preg_replace('!\s+!', ' ', $string); // multiple spaces to single
        $string = fix_utf8($string);
        // here is possible &nbsp;, but also are possible umlauts...
        $string = strtr($string, array_flip(get_html_translation_table(HTML_ENTITIES, ENT_QUOTES)));
        $string = trim($string, chr(0xC2) . chr(0xA0));
        return $string;
    }
}
