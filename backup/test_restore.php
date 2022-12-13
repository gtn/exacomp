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

if (!is_siteadmin()) {
    die('No Admin!');
}

// Transaction.
$transaction = $DB->start_delegated_transaction();

$files = glob(block_exacomp_get_backup_temp_directory() . '*');
$files = array_filter($files, 'is_dir');
usort($files, function($a, $b) {
    return filemtime($a) < filemtime($b);
});

if (!isset($files[0])) {
    die('backup not found');
}
echo "restoring last backup: " . $files[0] . "\n";

// Create new course.
$folder = basename($files[0]); // as found in: $CFG->dataroot . '/temp/backup/'
$categoryid = 1; // e.g. 1 == Miscellaneous
$userdoingrestore = 2; // e.g. 2 == admin
$courseid = restore_dbops::create_new_course('', '', $categoryid);

// Restore backup into course.
$controller = new restore_controller($folder, $courseid,
    backup::INTERACTIVE_NO, backup::MODE_SAMESITE, $userdoingrestore,
    backup::TARGET_NEW_COURSE);
$controller->execute_precheck();
$controller->execute_plan();

var_dump($courseid);

// Commit.
$transaction->allow_commit();

die('done');
