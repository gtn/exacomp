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

global $DB, $OUTPUT, $PAGE, $version;

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

// build tab navigation & print header
echo $OUTPUT->header();
echo $OUTPUT->tabtree(block_exacomp_build_navigation_tabs($context,$courseid), $page_identifier);

/* CONTENT REGION */

if ($action == 'save') {
    block_exacomp_set_coursetopics($courseid, (isset($_POST['data'])?$_POST['data']:array()));
    $action="";
}

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

$tree = block_exacomp_get_competence_tree();

$courseid_temp = $courseid;
if(!$version) $courseid_temp = 0;

$topics = block_exacomp_get_topics_by_subject($courseid, 0, true);

$subjects = block_exacomp_get_subjects_for_schooltype($courseid_temp);
$output = $PAGE->get_renderer('block_exacomp');
echo $output->print_courseselection($tree, $subjects, $topics);

/* END CONTENT REGION */

echo $OUTPUT->footer();

?>