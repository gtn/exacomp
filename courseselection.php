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

global $DB, $OUTPUT, $PAGE, $version,$skillmanagement;

$courseid = required_param('courseid', PARAM_INT);
$action = optional_param('action', "", PARAM_ALPHAEXT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_simplehtml', $courseid);
}

require_login($course);

$context = context_course::instance($courseid);

/* PAGE IDENTIFIER - MUST BE CHANGED. Please use string identifier from lang file */
$page_identifier = 'tab_teacher_settings_selection';

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/courseselection.php', array('courseid' => $courseid));
$PAGE->set_heading(get_string('pluginname', 'block_exacomp'));
$PAGE->set_title(get_string($page_identifier, 'block_exacomp'));

block_exacomp_init_js_css();

// build breadcrumbs navigation
$coursenode = $PAGE->navigation->find($courseid, navigation_node::TYPE_COURSE);
$blocknode = $coursenode->add(get_string('pluginname','block_exacomp'));
$pagenode = $blocknode->add(get_string($page_identifier,'block_exacomp'), $PAGE->url);
$pagenode->make_active();

$headertext = "";

if(!$version)
	$img = new moodle_url('/blocks/exacomp/pix/two.png');
else 
 	$img = new moodle_url('/blocks/exacomp/pix/three.png');
	 	
if ($action == 'save') {
    block_exacomp_set_coursetopics($courseid, (isset($_POST['data'])?$_POST['data']:array()));
    $action="";
    
    if(!isset($_POST['data']))
    	$headertext = get_string('tick_some', 'block_exacomp');
    else{
	    $course_settings = block_exacomp_get_settings_by_course($courseid);
	    if($course_settings->uses_activities){
		    if (block_exacomp_is_activated($courseid))
		    $headertext=get_string("save_success", "block_exacomp") .html_writer::empty_tag('br')
		    	.html_writer::empty_tag('img', array('src'=>$img, 'alt'=>'', 'width'=>'60px', 'height'=>'60px'))				
				. html_writer::link(new moodle_url('edit_activities.php', array('courseid'=>$courseid)), get_string('next_step', 'block_exacomp'));
	    }else{
	    	 $headertext=get_string("save_success", "block_exacomp") .html_writer::empty_tag('br')
	    		.html_writer::empty_tag('img', array('src'=>$img, 'alt'=>'', 'width'=>'60px', 'height'=>'60px')).get_string('completed_config', 'block_exacomp');
	
	   		 $students = block_exacomp_get_students_by_course($courseid);
	   		 if(empty($students))
				$headertext .= html_writer::empty_tag('br')
					.html_writer::link(new moodle_url('/enrol/users.php', array('id'=>$courseid)), get_string('optional_step', 'block_exacomp'));
	    }
    }
}else{
	$headertext = html_writer::empty_tag('img', array('src'=>$img, 'alt'=>'', 'width'=>'60px', 'height'=>'60px')).get_string('teacher_second_configuration_step', 'block_exacomp');
}

// build tab navigation & print header
$output = $PAGE->get_renderer('block_exacomp');
echo $OUTPUT->header();
echo $output->print_wrapperdivstart();
echo $OUTPUT->tabtree(block_exacomp_build_navigation_tabs($context,$courseid), 'tab_teacher_settings');
echo $OUTPUT->tabtree(block_exacomp_build_navigation_tabs_settings($courseid), $page_identifier);
/* CONTENT REGION */
/*
if($action == 'digicomps') {
	$values=array("15"=>15,"20"=>20,"17"=>17,"18"=>18,"21"=>21,"22"=>22,"23"=>23,"25"=>25,"112"=>112,"113"=>113,);
	block_exacomp_set_coursetopics($courseid, $values);
	/*
	set_descr_for_assignment("Chat - Wie würdest du dich verhalten?",array(73,684));
	set_descr_for_assignment("Chat - Wo wohnt Susi?",array(73,684));
	set_descr_for_assignment("Aufgabe 1 - Bewegungsdiagramme bitte hier abgeben",array(696,695,693,));
	set_descr_for_assignment("Aufgabe 2 - Bewegungsdiagramm - Textdatei und Präsentation bitte hier abgeben",array(700,698,699));
	set_descr_for_assignment("Aufgabe 3 - Bewegungsdiagramm - Präsentation bitte hier abgeben",array(700,698,699,693,696));
	set_descr_for_assignment("Das zusammengeräumte Haus bitte hier gezippt abgeben!",array(715,716,717));
	set_descr_for_assignment("Mensch - Maschine - Schnittstelle Präsentation hier abgeben",array(701,703,700,698,699));
	set_descr_for_assignment("Abagabe zu: Praktisches Beispiel - Eingabesteuerug",array(703));
	set_descr_for_assignment("Energiekosten - Tabellenkalulationsblatt - Lösung bitte hier abgeben",array(693,694,695,696,686,684));
	set_descr_for_assignment("Einladung - Datei bitte hier abgeben",array(700,698,699));
	set_descr_for_assignment("Handy - Lösung bitte hier eingeben!",array(677));
	set_descr_for_assignment("Zoo Salzburg - Ergebnis bitte hier abgeben",array(699,700,688,689,691,692,686));
	set_descr_for_assignment("Interview bitte hier abgeben",array(697));
	set_descr_for_assignment("Informationen Lehrberuf - Lösung bitte hier abgeben",array(699,700,688,689,691,692));
	set_descr_for_assignment("Abgabe: Migration - Tabellenkalulatonsdatei, Präsentation",array(693,694,695,696));

 */

$courseid_temp = $courseid;
if(!$version && !$skillmanagement) $courseid_temp = 0;

$schooltypes = block_exacomp_build_schooltype_tree($courseid_temp);

$topics = block_exacomp_get_topics_by_subject($courseid, 0, true);


echo $output->print_courseselection($schooltypes, $topics, $headertext);

/* END CONTENT REGION */
echo $output->print_wrapperdivend();
echo $OUTPUT->footer();

?>