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

$courseid = required_param('courseid', PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_simplehtml', $courseid);
}

require_login($course);

$course_context = context_course::instance($courseid);

require_capability('block/exacomp:admin', context_system::instance());

$action = required_param('action', PARAM_ALPHANUMEXT);

$output = block_exacomp_get_renderer();

\block_exacomp\data::prepare();

function block_exacomp_require_secret() {
	global $PAGE, $courseid;

	if (!get_config('exacomp', 'export_password')) {
		// no secret needed
		return '';
	}

	$secret = optional_param('secret', 0, PARAM_TEXT);

	if ($secret) {
		return $secret;
	}

	$secret = block_exacomp_random_password();

	$output = block_exacomp_get_renderer();

	/* PAGE IDENTIFIER - MUST BE CHANGED. Please use string identifier from lang file */
	$page_identifier = 'tab_admin_import';

	/* PAGE URL - MUST BE CHANGED */
	$PAGE->set_url('/blocks/exacomp/export.php', array('courseid' => $courseid));
	$PAGE->set_heading(block_exacomp_get_string('blocktitle'));
	$PAGE->set_title(block_exacomp_get_string($page_identifier));

	// build breadcrumbs navigation
	$coursenode = $PAGE->navigation->find($courseid, navigation_node::TYPE_COURSE);
	$blocknode = $coursenode->add(block_exacomp_get_string('blocktitle'));
	$pagenode = $blocknode->add(block_exacomp_get_string($page_identifier), $PAGE->url);
	$pagenode->make_active();

	echo $output->header(context_system::instance(), $courseid, 'tab_admin_settings');
	echo $output->tabtree(block_exacomp_build_navigation_tabs_admin_settings($courseid), $page_identifier);

	echo block_exacomp_get_string('export_password_message', null, $secret);
	echo '<br/><br/>';

	// add all other post parameters, eg. descriptors[], subjects[], topics[]
	$flatten_params = function($params, $level = 0) use (&$flatten_params) {
		$ret = [];
		foreach ($params as $key=>$value) {
			$key = $level > 0 ? '['.$key.']' : $key;
			if (is_array($value)) {
				foreach ($flatten_params($value, $level+1) as $subKey=>$value) {
					$ret[$key.$subKey] = $value;
				}
			} else {
				$ret[$key] = $value;
			}
		}
		return $ret;
	};

	$other_params = '';
	foreach ($flatten_params($_POST) as $key => $value) {
		$other_params .= '<input type="hidden" name="'.$key.'" value="'.$value.'" />';
	}

	echo '<form method="post">
		'.$other_params.'
		<input type="hidden" name="secret" value="'.$secret.'" />
		<input type="submit" class="btn btn-primary" value="'.block_exacomp_get_string('next').'" />
	</form>';

	echo $output->footer();
	exit;
}

if ($action == 'export_all') {
	$secret = block_exacomp_require_secret();

	block_exacomp\data_exporter::do_export($secret);
} else if ($action == 'export_selected') {
	$secret = block_exacomp_require_secret();
	$descriptors = block_exacomp\param::optional_array('descriptors', array(PARAM_INT=>PARAM_INT));

	block_exacomp\data_exporter::do_export($secret, $descriptors);
} else if ($action == 'select') {
	
	/* PAGE IDENTIFIER - MUST BE CHANGED. Please use string identifier from lang file */
	$page_identifier = 'tab_admin_import';
	
	/* PAGE URL - MUST BE CHANGED */
	$PAGE->set_url('/blocks/exacomp/export.php', array('courseid' => $courseid));
	$PAGE->set_heading(block_exacomp_get_string('blocktitle'));
	$PAGE->set_title(block_exacomp_get_string($page_identifier));
	
	// build breadcrumbs navigation
	$coursenode = $PAGE->navigation->find($courseid, navigation_node::TYPE_COURSE);
	$blocknode = $coursenode->add(block_exacomp_get_string('blocktitle'));
	$pagenode = $blocknode->add(block_exacomp_get_string($page_identifier), $PAGE->url);
	$pagenode->make_active();
	
	echo $output->header(context_system::instance(), $courseid, 'tab_admin_settings');
	echo $OUTPUT->tabtree(block_exacomp_build_navigation_tabs_admin_settings($courseid), $page_identifier);
	
	echo $output->descriptor_selection_export();
	
	echo $output->footer();
} else {
	print_error("wrong action '$action'");
}
