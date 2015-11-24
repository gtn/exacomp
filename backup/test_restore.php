<?php
 
require_once('../inc.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
 
if (!is_siteadmin()) {
	die('Pls login first!');
}

// Transaction.
$transaction = $DB->start_delegated_transaction();
 
// Create new course.
$folder			 = '61ccef7ce9f223715890ee752aa30db3'; // as found in: $CFG->dataroot . '/temp/backup/' 
$categoryid		 = 1; // e.g. 1 == Miscellaneous
$userdoingrestore   = 2; // e.g. 2 == admin
$courseid		   = 52; // restore_dbops::create_new_course('', '', $categoryid);
echo $courseid.' ';
// Restore backup into course.
$controller = new restore_controller($folder, $courseid, 
		backup::INTERACTIVE_NO, backup::MODE_SAMESITE, $userdoingrestore,
		backup::TARGET_NEW_COURSE);
$controller->execute_precheck();
$controller->execute_plan();
 
// Commit.
$transaction->allow_commit();

die('done');