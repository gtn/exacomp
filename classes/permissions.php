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

namespace block_exacomp;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/enrollib.php');

class permissions {
    public static function get_course_students(int $courseid): array {
        // Get all enrolled users in the course
        $context = \context_course::instance($courseid);
        $users = get_enrolled_users($context, 'block/exacomp:student', orderby: 'u.lastname, u.firstname');

        // filter all users, which are also a teacher in the course
        // then they are a teacher and not a student
        $users = array_filter($users, fn($user) => !has_capability('block/exacomp:teacher', $context, $user->id));

        return $users;
    }

    public static function get_course_teachers(int $courseid): array {
        $context = \context_course::instance($courseid);
        return get_enrolled_users($context, 'block/exacomp:teacher', orderby: 'u.lastname, u.firstname');
    }
}
