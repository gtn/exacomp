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
require_once dirname(__FILE__) . '/lib/lib.php';
require_once($CFG->dirroot . "/lib/datalib.php");
require_once dirname(__FILE__) . '/lib/xmllib.php';

global $DB, $OUTPUT, $PAGE, $COURSE, $CFG;

$courseid = required_param('courseid', PARAM_INT);
$action = optional_param('action', "", PARAM_ALPHA);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_simplehtml', $courseid);
}

require_login($course);

$version = get_config('exacomp', 'alternativedatamodel');
if($version) {
	$context = context_course::instance($courseid);
	require_capability('block/exacomp:teacher', $context);
}
else {
	$context =context_system::instance();
	require_capability('block/exacomp:admin', $context);
}

$check = block_exacomp_xml_check_import();
if(!$check){
	redirect(new moodle_url('/blocks/exacomp/import.php', array('courseid'=>$courseid)));
}

/* PAGE IDENTIFIER - MUST BE CHANGED. Please use string identifier from lang file */
$page_identifier = 'tab_admin_configuration';

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/edit_config.php', array('courseid' => $courseid));
$PAGE->set_heading(get_string('pluginname', 'block_exacomp'));
$PAGE->set_title(get_string($page_identifier, 'block_exacomp'));

block_exacomp_init_js_css();

// build breadcrumbs navigation
$coursenode = $PAGE->navigation->find($courseid, navigation_node::TYPE_COURSE);
$blocknode = $coursenode->add(get_string('pluginname','block_exacomp'));
$pagenode = $blocknode->add(get_string($page_identifier,'block_exacomp'), $PAGE->url);
$pagenode->make_active();

//Falls Formular abgesendet, speichern
if (isset($action) && $action == 'save') {
	$values = isset($_POST['data']) ? $_POST['data'] : array();
	if($version)
		block_exacomp_set_mdltype($values,$courseid);
	else
		block_exacomp_set_mdltype($values);

	$headertext=get_string("save_success", "block_exacomp");
	//echo '<script>location.reload();</script>';
}else{
	$headertext=get_string("explainconfig", "block_exacomp");
}

// build tab navigation & print header
echo $OUTPUT->header();
echo $OUTPUT->tabtree(block_exacomp_build_navigation_tabs($context,$courseid), $page_identifier);

/* CONTENT REGION */




/* HTML CONTENT */

$data = new stdClass();
$data->headertext = $headertext;
$data->levels = array();
 
$levels = block_exacomp_get_edulevels();
foreach($levels as $level){
	$data->levels[$level->id] = new stdClass();
	$data->levels[$level->id]->level = $level;
	$data->levels[$level->id]->schooltypes = array();
	
	$types = block_exacomp_get_schooltypes($level->id);

	foreach($types as $type){
		$ticked = (!$version) ? block_exacomp_get_mdltypes($type->id) : block_exacomp_get_mdltypes($type->id,$courseid);
		
		$data->levels[$level->id]->schooltypes[$type->id] = new stdClass();
		$data->levels[$level->id]->schooltypes[$type->id]->schooltype = $type;
		$data->levels[$level->id]->schooltypes[$type->id]->ticked = $ticked;
	}
}

$output = $PAGE->get_renderer('block_exacomp');
	
echo $output->print_edit_config($data, $courseid);

/* END CONTENT REGION */

echo $OUTPUT->footer();

?>