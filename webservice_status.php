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

require __DIR__.'/inc.php';

$courseid = required_param ( 'courseid', PARAM_INT );

require_login($courseid);
block_exacomp_require_admin();

$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('blocktitle', 'block_exacomp'));
$PAGE->set_title(get_string('blocktitle', 'block_exacomp'));

$PAGE->set_url('/blocks/exacomp/webservice_status.php');


$output = $PAGE->get_renderer ( 'block_exacomp' );
echo $output->header_v2('tab_admin_settings');
echo $output->tabtree(block_exacomp_build_navigation_tabs_admin_settings($courseid), 'tab_webservice_status');

echo block_exacomp\trans([
	'de:Die folgenden Schritte sind notwendig um Exabis Competencies Webservices zu aktiviert:',
	'en:Please follow the steps below to enable Exabis Competencies webservices:',
]);

$brtag = html_writer::empty_tag('br');

// echo $OUTPUT->heading(get_string('onesystemcontrolling', 'webservice'), 3, 'main');
$table = new html_table();
$table->head = array(get_string('step', 'webservice'), get_string('status'),
	get_string('description'));
$table->colclasses = array('leftalign step', 'leftalign status', 'leftalign description');
$table->id = 'onesystemcontrol';
$table->attributes['class'] = 'admintable wsoverview generaltable';
$table->data = array();

/// 1. Enable Web Services
$row = array();
$url = new moodle_url("/admin/search.php?query=enablewebservices");
$row[0] = "1. " . html_writer::tag('a', get_string('enablews', 'webservice'),
				array('href' => $url));
$status = html_writer::tag('span', get_string('no'), array('class' => 'statuscritical'));
if ($CFG->enablewebservices) {
	$status = html_writer::tag('span', get_string('ok'), array('class' => 'statusok'));
}
$row[1] = $status;
$row[2] = get_string('enablewsdescription', 'webservice');
$table->data[] = $row;

/// 2. Enable protocols
$row = array();
$url = new moodle_url("/admin/settings.php?section=webserviceprotocols");
$row[0] = "2. " . html_writer::tag('a', get_string('enableprotocols', 'webservice'),
				array('href' => $url));
//retrieve activated protocol
$active_protocols = empty($CFG->webserviceprotocols) ?
		array() : explode(',', $CFG->webserviceprotocols);
$status = "";
if (!empty($active_protocols)) {
	foreach ($active_protocols as $protocol) {
		$status .= $protocol . $brtag;
	}
}
if (!in_array('rest', $active_protocols)) {
	$status = html_writer::tag('span', 'REST Protocol not enabled', array('class' => 'statuscritical')).$brtag.$status;
} else {
	$status = html_writer::tag('span', get_string('ok'), array('class' => 'statusok')).$brtag.$status;
}

$row[1] = $status;
$row[2] = get_string('enableprotocolsdescription', 'webservice');
$table->data[] = $row;

/// 3. Enable Web Services for Mobile Devices
$row = array();
$url = new moodle_url("/admin/search.php?query=enablemobilewebservice");
$row[0] = "3. " . html_writer::tag('a', get_string('enablemobilewebservice', 'admin'),
				array('href' => $url));
if ($CFG->enablemobilewebservice) {
	$status = html_writer::tag('span', get_string('ok'), array('class' => 'statusok'));
} else {
	$status = html_writer::tag('span', get_string('no'), array('class' => 'statuscritical'));
}

$row[1] = $status;
$enablemobiledocurl = new moodle_url(get_docs_url('Enable_mobile_web_services'));
$enablemobiledoclink = html_writer::link($enablemobiledocurl, new lang_string('documentation'));
$default = is_https() ? 1 : 0;
$row[2] = new lang_string('configenablemobilewebservice', 'admin', $enablemobiledoclink);
$table->data[] = $row;

/// 4. Webservice Roles
$row = array();
$url = new moodle_url("/admin/roles/manage.php");
$row[0] = "4. " . html_writer::tag('a', 'Roles with webservice access',
				array('href' => $url));
$wsroles = get_roles_with_capability('moodle/webservice:createtoken');
// get rolename in local language
$wsroles = role_fix_names($wsroles, context_system::instance(), ROLENAME_ORIGINAL);
if ($wsroles) {
	$status = html_writer::tag('span', get_string('ok'), array('class' => 'statusok'));
	foreach ($wsroles as $role) {
		$status .= $brtag.$role->localname;
	}
} else {
	$status = html_writer::tag('span', 'Permissions not set', array('class' => 'statuscritical'));
}

$row[1] = $status;
$row[2] = nl2br('Grant additional permission to the role "authenticated user" at: Site administration/Users/Permissions/Define roles
4.1 Select Authenticated User
4.2 Click on "Edit"
4.3 Filter for createtoken
4.4 Allow moodle/webservice:createtoken');
$table->data[] = $row;


/// 5. Checks
$status = '';
//set shortname for external service exacompservices
$exacomp_service = $DB->get_record('external_services', array('name'=>'exacompservices'));
if (!$exacomp_service) {
	$status .= html_writer::tag('span', 'Exacompservice not found', array('class' => 'statuscritical'));
}
// not needed anymore
/*
	$exacomp_service->shortname = 'exacompservices';
	$DB->update_record('external_services', $exacomp_service);
*/

$exaport_service = $DB->get_record('external_services', array('name'=>'exaportservices'));
if (!$exaport_service) {
	$status .= html_writer::tag('span', 'Exaportservice not found', array('class' => 'statuscritical'));
}
// not needed anymore
/*
	$exaport_service->shortname = 'exaportservices';
	$DB->update_record('external_services', $exaport_service);
*/

if (empty($status)) {
	$status = html_writer::tag('span', get_string('ok'), array('class' => 'statusok'));
}

$row = array();
$row[0] = "5. Webservice checks";
$row[1] = $status;
$row[2] = '';
$table->data[] = $row;


if (get_config('exacomp', 'external_trainer_assign')) {
	$count = $DB->count_records('block_exacompexternaltrainer');
	if ($count) {
		$status = html_writer::tag('span', get_string('ok'), array('class' => 'statusok'));
	} else {
		$status = html_writer::tag('span', 'No external trainers assigned', array('class' => 'statuscritical'));
	}
	// checks for elove app
	$row = array();
	$url = new moodle_url("/blocks/exacomp/externaltrainers.php?courseid=".$courseid);
	$row[0] = "6. " . html_writer::tag('a', get_string('block_exacomp_external_trainer_assign', 'block_exacomp'),
					array('href' => $url));

	$row[1] = $status;
	$row[2] = '';
	$table->data[] = $row;
}

echo html_writer::table($table);

echo $output->footer();

