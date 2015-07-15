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

$de = false;
$lang = current_language();
if(isset($lang) && substr( $lang, 0, 2) === 'de'){
	$de = true;
}

if($de)
	define('DEMO_XML_PATH', 'https://raw.githubusercontent.com/gtn/edustandards/master/demo/deutsch/exacomp_data.xml');
else 
	define('DEMO_XML_PATH', 'https://raw.githubusercontent.com/gtn/edustandards/master/demo/english/exacomp_data.xml');

global $DB, $OUTPUT, $PAGE;

$courseid = required_param('courseid', PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_simplehtml', $courseid);
}

require_login($course);

$context = context_system::instance();
$course_context = context_course::instance($courseid);

/* PAGE IDENTIFIER - MUST BE CHANGED. Please use string identifier from lang file */
$page_identifier = 'tab_admin_import';

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/import.php', array('courseid' => $courseid));
$PAGE->set_heading(get_string('pluginname', 'block_exacomp'));
$PAGE->set_title(get_string($page_identifier, 'block_exacomp'));

block_exacomp_init_js_css();

$isAdmin = has_capability('block/exacomp:admin', $context);
require_capability('block/exacomp:teacher', $course_context);

$action = optional_param('action', "", PARAM_ALPHA);
$importoption = optional_param('importoption', "", PARAM_ALPHA);
$importtype = optional_param('importtype', '', PARAM_TEXT);
$action = optional_param('action', '', PARAM_TEXT);

$mform = new block_exacomp_generalxml_upload_form();

$importSuccess = false;
/*
if($isAdmin && $importtype && $data = $mform->get_file_content('file')){
	if(strcmp($importtype, 'demo')!=0)
		print_r($data);
	//$importSuccess = block_exacomp_xml_do_import($data, (($importtype == 'normal') ? IMPORT_SOURCE_NORMAL : IMPORT_SOURCE_SPECIFIC));
}*/

if((strcmp($importtype,'custom') == 0) && $data = $mform->get_file_content('file')) {
	$importSuccess = block_exacomp_xml_do_import($data, IMPORT_SOURCE_SPECIFIC);
} elseif($isAdmin && (strcmp($importtype, 'demo') != 0) && $data = $mform->get_file_content('file')) {
	$importSuccess = block_exacomp_xml_do_import($data, IMPORT_SOURCE_NORMAL);
} elseif($isAdmin && $importtype && strcmp($importtype, 'demo')==0){
	//do demo import	
	$xml = file_get_contents(DEMO_XML_PATH);
	if($xml) {
		if(block_exacomp_xml_do_import($xml,1,1)) {
			$importSuccess = true;
			block_exacomp_settstamp();
		}
	}
}
// build breadcrumbs navigation
$coursenode = $PAGE->navigation->find($courseid, navigation_node::TYPE_COURSE);
$blocknode = $coursenode->add(get_string('pluginname','block_exacomp'));
$pagenode = $blocknode->add(get_string($page_identifier,'block_exacomp'), $PAGE->url);
$pagenode->make_active();

// build tab navigation & print header
echo $OUTPUT->header();
echo '<div id="exacomp">';
echo $OUTPUT->tabtree(block_exacomp_build_navigation_tabs($course_context,$courseid), $page_identifier);
/* CONTENT REGION */

/* Admins are allowed to import data, or a special capability for custom imports */
if($isAdmin || block_exacomp_check_customupload()) {

    if($action == 'delete') {
        block_exacomp_delete_custom_competencies();
        echo $OUTPUT->box(get_string("delete_success", "block_exacomp"));
    }
	if($importtype) {
		if(strcmp($importtype, 'normal')==0 || strcmp($importtype, 'custom')==0){
			if ($mform->is_cancelled()) {
				redirect($PAGE->url);
			} else {
				if ($data = $mform->get_file_content('file')) {
					if($importSuccess) {
							$string = get_string('next_step', 'block_exacomp');
							$url = 'edit_config.php';
						
						$html = get_string("importsuccess", "block_exacomp").html_writer::empty_tag('br');
						if($isAdmin)
							$html .= html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/one_admin.png'), 'alt'=>'', 'width'=>'60px', 'height'=>'60px'))
							.html_writer::link(new moodle_url($url, array('courseid'=>$courseid, 'fromimport'=>1)), $string);
						
						echo $OUTPUT->box($html);
					}
					else {
						echo $OUTPUT->box(get_string("importfail", "block_exacomp"));
						$mform->display();
					}
				} else {
					echo $OUTPUT->box(get_string("importinfo", "block_exacomp"));
					if($isAdmin) echo $OUTPUT->box(get_string("importwebservice", "block_exacomp",new moodle_url("/admin/settings.php", array('section'=>'blocksettingexacomp'))));
			
					$mform->display();
				}
			}
		}elseif(strcmp($importtype, 'demo')==0){
			if($importSuccess){
				if(!$version) $string = get_string('next_step', 'block_exacomp');
				else $string = get_string('next_step_teacher', 'block_exacomp');
							
				echo $OUTPUT->box(get_string("importsuccess", "block_exacomp").html_writer::empty_tag('br')
					.html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/one_admin.png'), 'alt'=>'', 'width'=>'60px', 'height'=>'60px'))
					.html_writer::link(new moodle_url('edit_config.php', array('courseid'=>$courseid, 'fromimport'=>1)), $string));
			}else{
				echo $OUTPUT->box(get_string("importfail", "block_exacomp"));
			}
		}
	} else {

		if(block_exacomp_xml_check_import() && $isAdmin){
			echo $OUTPUT->box(get_string("importdone", "block_exacomp"));
			echo $OUTPUT->box(html_writer::link(new moodle_url('/blocks/exacomp/import.php', array('courseid'=>$courseid, 'importtype'=>'normal')), get_string('doimport_again', 'block_exacomp')));
		}
		else if($isAdmin) {
			echo $OUTPUT->box(html_writer::empty_tag('img', array('src'=>new moodle_url('/blocks/exacomp/pix/one_admin.png'), 'alt'=>'', 'width'=>'60px', 'height'=>'60px')).get_string('first_configuration_step', 'block_exacomp'));
			echo $OUTPUT->box(get_string("importpending", "block_exacomp"));
			echo $OUTPUT->box(html_writer::link(new moodle_url('/blocks/exacomp/import.php', array('courseid'=>$courseid, 'importtype'=>'normal')), get_string('doimport', 'block_exacomp')));
			echo $OUTPUT->box(html_writer::link(new moodle_url('/blocks/exacomp/import.php', array('courseid'=>$courseid, 'importtype'=>'demo')), get_string('do_demo_import', 'block_exacomp')));
		}

		if(block_exacomp_xml_check_import())
			echo $OUTPUT->box(html_writer::link(new moodle_url('/blocks/exacomp/import.php', array('courseid'=>$courseid, 'importtype'=>'custom')), get_string('doimport_own', 'block_exacomp')));
		
		if(block_exacomp_xml_check_custom_import() && $isAdmin)
		    echo $OUTPUT->box(html_writer::link(new moodle_url('/blocks/exacomp/import.php', array('courseid'=>$courseid, 'action'=>'delete' )), get_string('delete_own', 'block_exacomp'), array( "onclick" => "return confirm('" . get_string('delete_own_confirm','block_exacomp') . "')")));
		
		if(isset($import)) {
			if($import)
				echo $OUTPUT->box(get_string("importsuccess", "block_exacomp"));
			else
				echo $OUTPUT->box(get_string("importfail", "block_exacomp"));

		}
	}
} else require_capability('block/exacomp:admin', $context);

/* END CONTENT REGION */
echo '</div>';
echo $OUTPUT->footer();

?>