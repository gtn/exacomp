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

require __DIR__.'/../inc.php';
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
 
if (!is_siteadmin()) {
	die('Pls login first!');
}

// Transaction.
$transaction = $DB->start_delegated_transaction();

$files = glob($CFG->dataroot . '/temp/backup/*');
$files = array_filter($files, 'is_dir');
usort($files, function($a, $b) {
	return filemtime($a) < filemtime($b);
});

// Create new course.
$folder             = basename($files[0]); // as found in: $CFG->dataroot . '/temp/backup/'
$categoryid         = 1; // e.g. 1 == Miscellaneous
$userdoingrestore   = 2; // e.g. 2 == admin
$courseid           = restore_dbops::create_new_course('', '', $categoryid);

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
