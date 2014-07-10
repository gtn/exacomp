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
require_once dirname(__FILE__)."/lib/xmllib.php";

global $DB, $OUTPUT, $PAGE;

$courseid = required_param('courseid', PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_simplehtml', $courseid);
}

require_login($course);

$context = context_system::instance();

/* PAGE IDENTIFIER - MUST BE CHANGED. Please use string identifier from lang file */
$page_identifier = 'tab_admin_import';

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/import.php', array('courseid' => $courseid));
$PAGE->set_heading(get_string('pluginname', 'block_exacomp'));

block_exacomp_init_js_css();

// build breadcrumbs navigation
$coursenode = $PAGE->navigation->find($courseid, navigation_node::TYPE_COURSE);
$blocknode = $coursenode->add(get_string('pluginname','block_exacomp'));
$pagenode = $blocknode->add(get_string($page_identifier,'block_exacomp'), $PAGE->url);
$pagenode->make_active();

// build tab navigation & print header
echo $OUTPUT->header();
echo $OUTPUT->tabtree(block_exacomp_build_navigation_tabs($context,$courseid), $page_identifier);

/* CONTENT REGION */

$action = optional_param('action', "", PARAM_ALPHA);
$importoption = optional_param('importoption', "", PARAM_ALPHA);
$importtype = optional_param('importtype', '', PARAM_TEXT);

$isAdmin = has_capability('block/exacomp:admin', $context);
/* Admins are allowed to import data, or a special capability for custom imports */
if($isAdmin || block_exacomp_check_customupload()) {

	if($importtype) {
		
		$mform = new block_exacomp_generalxml_upload_form();
		if ($mform->is_cancelled()) {
			redirect($PAGE->url);
		} else {
			if ($data = $mform->get_file_content('file')) {
				if(block_exacomp_xml_do_import($data, (($importtype == 'normal') ? IMPORT_SOURCE_NORMAL : IMPORT_SOURCE_SPECIFIC))) {
					echo $OUTPUT->box(get_string("importsuccess", "block_exacomp"));
				}
				else {
					echo $OUTPUT->box(get_string("importfail", "block_exacomp"));
					$mform->display();
				}
			} else {
				echo $OUTPUT->box(get_string("importinfo", "block_exacomp"));
				echo $OUTPUT->box(get_string("importwebservice", "block_exacomp",new moodle_url("/admin/settings.php", array('section'=>'blocksettingexacomp'))));
		
				$mform->display();
			}
		}

	} else {

		if(block_exacomp_xml_check_import() && $isAdmin){
			echo $OUTPUT->box(get_string("importdone", "block_exacomp"));
			echo $OUTPUT->box(html_writer::link(new moodle_url('/blocks/exacomp/import.php', array('courseid'=>$courseid, 'importtype'=>'normal')), get_string('doimport_again', 'block_exacomp')));
		}
		else if($isAdmin) {
			echo $OUTPUT->box(get_string("importpending", "block_exacomp"));
			echo $OUTPUT->box(html_writer::link(new moodle_url('/blocks/exacomp/import.php', array('courseid'=>$courseid, 'importtype'=>'normal')), get_string('doimport', 'block_exacomp')));
		}

		if(block_exacomp_xml_check_import())
			echo $OUTPUT->box(html_writer::link(new moodle_url('/blocks/exacomp/import_own.php', array('courseid'=>$courseid)), get_string('doimport_own', 'block_exacomp')));

		if(isset($import)) {
			if($import)
				echo $OUTPUT->box(get_string("importsuccess", "block_exacomp"));
			else
				echo $OUTPUT->box(get_string("importfail", "block_exacomp"));

		}
	}
} else require_capability('block/exacomp:admin', $context);

/* END CONTENT REGION */

echo $OUTPUT->footer();

?>