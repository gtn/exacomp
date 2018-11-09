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

require __DIR__ . '/../inc.php';
require_once ($CFG->dirroot . '/backup/util/includes/restore_includes.php');

function moodle_restore($data, $courseid, $userdoingrestore)
{
    if (! is_siteadmin()) {
        die('No Admin!');
    }

    // $data: the name of the folder in CFG->backuptempdir
    // $courseid: destination course of this restore
    // Restore backup into course.
    $controller = new restore_controller($data, $courseid, backup::INTERACTIVE_NO, backup::MODE_IMPORT, $userdoingrestore, backup::TARGET_CURRENT_ADDING);
    $controller->execute_precheck();

    $controller->execute_plan();

//     var_dump($courseid);

    // Commit.
 //   $transaction->allow_commit();
}
