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

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_simplehtml', $courseid);
}

require_login($course);

$context = context_course::instance($courseid);

/* PAGE IDENTIFIER - MUST BE CHANGED. Please use string identifier from lang file */
$page_identifier = 'tab_skillmanagement';

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/skillmanagement.php', array('courseid' => $courseid));
$PAGE->set_heading(get_string('pluginname', 'block_exacomp'));

block_exacomp_init_js_css();

// build breadcrumbs navigation
$coursenode = $PAGE->navigation->find($courseid, navigation_node::TYPE_COURSE);
$blocknode = $coursenode->add(get_string('pluginname','block_exacomp'));
$pagenode = $blocknode->add(get_string($page_identifier,'block_exacomp'), $PAGE->url);
$pagenode->make_active();

// build tab navigation & print header
echo $OUTPUT->header();
//echo $OUTPUT->tabtree(block_exacomp_build_navigation_tabs($context,$courseid), $page_identifier);

/* CONTENT REGION */

$contents = html_writer::tag('p', 'Ihr Browser kann leider keine eingebetteten Frames anzeigen:
	Sie k&ouml;nnen die eingebettete Seite &uuml;ber den folgeden Verweis aufrufen: '
	.html_writer::link(new moodle_url('../../index.php?id=xmltool', array('courseid'=>$courseid, 'uname'=>$USER->username,
		'uhash'=>md5($USER->firstaccess))), get_string('tab_skillmanagement', 'block_exacomp')));
	
echo html_writer::tag('iframe', $contents, array('src'=>new moodle_url('../../../index.php?id=xmltool', 
	array('courseid'=>$courseid, 'uname'=>$USER->username, 'uhash'=>md5($USER->firstaccess))), 'width'=>"99%", 'height'=>500, 'name'=>'iXmlTool'));

/* END CONTENT REGION */

echo $OUTPUT->footer();

?>