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

require __DIR__.'/inc.php';

global $DB, $OUTPUT, $PAGE;

$courseid = required_param('courseid', PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_simplehtml', $courseid);
}

block_exacomp_require_login($course);

$context = context_course::instance($courseid);

/* PAGE IDENTIFIER - MUST BE CHANGED. Please use string identifier from lang file */
$page_identifier = 'tab_badges';

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/my_badges.php', array('courseid' => $courseid));
$PAGE->set_heading(block_exacomp_get_string('blocktitle'));
$PAGE->set_title(block_exacomp_get_string($page_identifier));

// build breadcrumbs navigation
block_exacomp_build_breadcrum_navigation($courseid);

// build tab navigation & print header
$output = block_exacomp_get_renderer();
echo $output->header($context,$courseid, $page_identifier);

/* CONTENT REGION */

block_exacomp_award_badges($courseid, $USER->id);
$badges = block_exacomp_get_user_badges($courseid, $USER->id);


echo $output->my_badges($badges);

/* END CONTENT REGION */

echo $output->footer();
