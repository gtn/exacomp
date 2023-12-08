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

require __DIR__ . '/../inc.php';
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

function moodle_restore($data, $courseid, $userdoingrestore) {
    global $DB, $CFG;
    if (!is_siteadmin()) {
        die('No Admin!');
    }

    $transaction = $DB->start_delegated_transaction();

    try {
        // $data: the name of the folder in CFG->backuptempdir
        // $courseid: destination course of this restore
        // Restore backup into course.
        $controller = new restore_controller($data, $courseid, backup::INTERACTIVE_NO, backup::MODE_SAMESITE, $userdoingrestore, backup::TARGET_NEW_COURSE);
        //    $user = $controller->get_plan()->get_setting('users')->get_value();
        $controller->get_plan()->get_setting('users')->set_value(0); // 2021.09.16 this should be the setting "Include enrolled users"

        //    $controller->get_plan()->get_setting('enrolments')->set_value(backup::ENROL_ALWAYS);
        $controller->execute_precheck();

        $controller->execute_plan();
    } catch (\Exception $e) {
        throw new moodle_exception('something wrong with importing of activity: ' . $data);
    }

    // Commit.
    $transaction->allow_commit();
}
