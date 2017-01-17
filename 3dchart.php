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
global $USER;

require __DIR__.'/inc.php';

$courseid = required_param('courseid', PARAM_INT);
$topicid = required_param('topicid', PARAM_INT);
$userid = required_param('userid', PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_exacomp', $courseid);
}

// error if example does not exist or was created by somebody else
if (!$topic = $DB->get_record(BLOCK_EXACOMP_DB_TOPICS, array('id' => $topicid))) {
	print_error('invalidtopic', 'block_exacomp', $topicid);
}

require_login($course);

$context = context_course::instance($courseid);

if($userid != $USER->id)
	block_exacomp_require_teacher($courseid);

if(!block_exacomp_use_eval_niveau())
	print_error('invalidevalniveau', 'block_exacomp');
	
	
//	$scheme_items = \block_exacomp\global_config::get_value_titles($courseid);
//	$evaluationniveau_items = \block_exacomp\global_config::get_evalniveaus();
/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/3dchart.php', array('courseid' => $courseid));
$PAGE->set_heading(block_exacomp_get_string('blocktitle', 'block_exacomp'));
$PAGE->set_pagelayout('embedded');

// build breadcrumbs navigation
block_exacomp_build_breadcrum_navigation($courseid);

$output = block_exacomp_get_renderer();
$output->requires()->js('/blocks/exacomp/javascript/vis.js', true);
// build tab navigation & print header
echo $output->header($context, $courseid, "", false);

/* CONTENT REGION */
echo html_writer::div(null, null, array('id' => 'mygraph'));

/* END CONTENT REGION */
echo $output->footer();