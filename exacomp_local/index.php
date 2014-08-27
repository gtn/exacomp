<?php
// This file is part of the LFB-BW plugin for Moodle - http://moodle.org/
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

require_once(dirname(__FILE__).'/../../config.php');

$PAGE->set_url(new moodle_url('/local/exacomp_local/index.php'));
$site = get_site();
$pluginname = get_string('pluginname', 'local_exacomp_local');

$PAGE->set_pagelayout('admin');
$PAGE->set_context(context_system::instance());
$PAGE->set_title($site->shortname.': '.$pluginname);
$PAGE->set_heading($site->fullname);

require_login();

// Check permissions
if (!has_capability('local/exacomp_local:execute', context_system::instance())) {
    throw new moodle_exception('nopermissions', '', $PAGE->url->out(), get_string('exacomp_local:execute', 'local_exacomp_local'));
}

echo $OUTPUT->header();

//set shortname for external service exacompservices
$exacomp_service = $DB->get_record('external_services', array('name'=>'exacompservices'));
if($exacomp_service){
	$exacomp_service->shortname = 'exacompservices';
	$DB->update_record('external_services', $exacomp_service);
	echo get_string('if_enabled', 'local_exacomp_local');
}else{
	echo get_string('something_went_wrong', 'local_exacomp_local');
}
			

echo $OUTPUT->footer();

