<?php
// This file is part of Exabis Competencies
//
// (c) 2016 GTN - Global Training Network GmbH <office@gtn-solutions.com>
//
// Exabis Competencies is free software: you can redistribute it and/or modify
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
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
 
if (!is_siteadmin()) {
	die('Pls login first!');
}

$course_id = 2; // Set this to one existing choice cmid in your dev site
$user_doing_the_backup   = 2; // Set this to the id of your admin accouun
 
$bc = new backup_controller(backup::TYPE_1COURSE, $course_id, backup::FORMAT_MOODLE,
							backup::INTERACTIVE_NO, backup::MODE_GENERAL, $user_doing_the_backup);
$bc->execute_plan();

die('done');
