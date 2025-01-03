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

// List of observers.
$observers = array(

    array(
        'eventname' => '\core\event\course_created',
        'callback' => 'block_exacomp_observer::course_created',
    ),

    array(
        'eventname' => '\core\event\course_module_completion_updated',
        'callback' => 'block_exacomp_observer::course_module_completion_updated',
    ),

    //    array(
    //        'eventname' => '\core\event\course_module_created',
    //        'callback' => 'block_exacomp_observer::course_module_created',
    //    ),

    array(
        'eventname' => '\core\event\course_module_updated',
        'callback' => 'block_exacomp_observer::course_module_updated',
    ),

    array(
        'eventname' => '\core\event\course_module_deleted',
        'callback' => 'block_exacomp_observer::course_module_deleted',
    ),

    // question graded testes
    // array(
    //     'eventname' => '\mod_quiz\event\attempt_regraded',
    //     'callback' => 'block_exacomp_observer::attempt_regraded',
    // ),
    array(
        'eventname' => '\mod_quiz\event\attempt_submitted',
        'callback' => 'block_exacomp_observer::attempt_submitted',
    ),
    // array(
    //     'eventname' => '\mod_quiz\event\question_manually_graded',
    //     'callback' => 'block_exacomp_observer::question_manually_graded',
    // ),

);
