<?php
/*
 * copyright exabis
 */

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
