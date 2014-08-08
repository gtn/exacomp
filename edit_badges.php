<?php

/* * *************************************************************
 *  Copyright notice
*
*  (c) 2014 exabis internet solutions <info@exabis.at>
*  All rights reserved
*
*  You can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  This module is based on the Collaborative Moodle Modules from
*  NCSA Education Division (http://www.ncsa.uiuc.edu)
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
* ************************************************************* */

require_once dirname(__FILE__)."/inc.php";

global $DB, $OUTPUT, $PAGE;

$courseid = required_param('courseid', PARAM_INT);
$badgeid = optional_param('badgeid', 0, PARAM_INT);
$action = optional_param('action', "", PARAM_ALPHA);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_simplehtml', $courseid);
}

require_login($course);

$context = context_course::instance($courseid);

/* PAGE IDENTIFIER - MUST BE CHANGED. Please use string identifier from lang file */
$page_identifier = 'tab_teacher_settings_badges';

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/edit_badges.php', array('courseid' => $courseid));
$PAGE->set_heading(get_string('pluginname', 'block_exacomp'));
$PAGE->set_title(get_string($page_identifier, 'block_exacomp'));

block_exacomp_init_js_css();

// build breadcrumbs navigation
$coursenode = $PAGE->navigation->find($courseid, navigation_node::TYPE_COURSE);
$blocknode = $coursenode->add(get_string('pluginname','block_exacomp'));
$pagenode = $blocknode->add(get_string($page_identifier,'block_exacomp'), $PAGE->url);
$pagenode->make_active();

// build tab navigation & print header
echo $OUTPUT->header();
echo $OUTPUT->tabtree(block_exacomp_build_navigation_tabs($context,$courseid), $page_identifier);

if (!block_exacomp_moodle_badges_enabled()) {
	error("Badges library not found, please upgrade your Moodle to 2.5");
	exit;
}


/* CONTENT REGION */
$output = $PAGE->get_renderer('block_exacomp');
if ($badgeid && $badge = $DB->get_record('badge', array('id' => $badgeid))) {
	if ($action == 'save') {
		$DB->delete_records('block_exacompdescbadge_mm', array("badgeid" => $badgeid));
		if (!empty($_POST['descriptors'])){
			foreach ($_POST['descriptors'] as $value=>$tmp) {
				$DB->insert_record('block_exacompdescbadge_mm', array("badgeid" => $badgeid, "descid" => intval($value)));
			}
		}
	}else{
		$tree = block_exacomp_get_competence_tree($courseid);
		$badge->descriptors = block_exacomp_get_badge_descriptors($badge->id);
		echo $output->print_edit_badges($tree, $badge);
		echo $OUTPUT->footer();
		return;
	}
 }
 

$badges = badges_get_badges(BADGE_TYPE_COURSE, $courseid); 

if (!$badges) {
	echo $OUTPUT->box(text_to_html(get_string("no_badges_yet", "block_exacomp")));
	echo $OUTPUT->footer();
	return;
}

block_exacomp_award_badges($courseid);

foreach ($badges as $badge) {
	$descriptors = block_exacomp_get_badge_descriptors($badge->id);
	$descriptors = $DB->get_records_sql('
		SELECT d.*
		FROM {block_exacompdescriptors} d
		JOIN {block_exacompdescbadge_mm} db ON d.id=db.descid AND db.badgeid=?
	', array($badge->id));

	$context = context_course::instance($badge->courseid);
	echo $output->print_badge($badge, $descriptors, $context);
}


/* END CONTENT REGION */

echo $OUTPUT->footer();

?>