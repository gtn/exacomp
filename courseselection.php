<?php

/* * *************************************************************
 *  Copyright notice
 *
 *  (c) 2011 exabis internet solutions <info@exabis.at>
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


require_once dirname(__FILE__) . '/inc.php';
require_once dirname(__FILE__) . '/lib/div.php';
require_once($CFG->dirroot . "/lib/datalib.php");

global $COURSE, $CFG, $OUTPUT;
$content = "";

$courseid = required_param('courseid', PARAM_INT);
$courseid = (isset($courseid)) ? $courseid : $COURSE->id;
$action = optional_param('action', "", PARAM_ALPHAEXT);

require_login($courseid);

$context = get_context_instance(CONTEXT_COURSE, $courseid);

require_capability('block/exacomp:teacher', $context);
//Falls Formular abgesendet, speichern


if ($action == 'save_coursetopics') {
    block_exacomp_set_coursetopics($courseid, $_POST['data']);
    $action="";
}

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
	set_descr_for_assignment("Abgabe: Migration - Tabellenkalulatonsdatei, Präsentation",array(693,694,695,696));	*/

}
$PAGE->set_url('/blocks/exacomp/courseselection.php?courseid=' . $courseid);

block_exacomp_print_header("teacher", "teachertabselection");


echo "<div class='block_excomp_center'>";


if (empty($action)) {
    $subjects = block_exacomp_get_subjects();

    if (!$subjects) {
        echo $OUTPUT->box(text_to_html(get_string("explainnomoodle_config", "block_exacomp")));
    } else {
        echo $OUTPUT->box(text_to_html(get_string("explainconfigcourse_subjects", "block_exacomp")));
        $content.='<form action="courseselection.php?courseid=' . $courseid . '&amp;action=detail" method="post">';
        $content .= '<table>';
		$specific=false;
		
		$schooltype = "";
		$schooltypes = $DB->get_records_menu("block_exacompschooltypes",null,null,"id, title");
        foreach ($subjects as $subject) {
			if($schooltype != $schooltypes[$subject->stid]) {
				$schooltype = $schooltypes[$subject->stid];
				$content .= '<tr> <td colspan="2"><b>' . $schooltype . '</b></td></tr>';
				
			}
        	if($subject->source!=1 && !$specific){
        		$specific=true;
        		$content .= '<tr> <td colspan="2"><h2>' . get_string("specificsubject","block_exacomp") . '</h2></td></tr>';
        	}
            if (block_exacomp_check_subject_by_course($subject->id, $courseid))
                $content .= '<tr><td>' . $subject->title . '</td><td><input type="checkbox" name="data[' . $subject->id . ']" value="' . $subject->id . '" checked="checked" /></td></tr>';
            else
                $content .= '<tr><td>' . $subject->title . '</td><td><input type="checkbox" name="data[' . $subject->id . ']" value="' . $subject->id . '" /></td></tr>';
        }
        $content.='<tr><td colspan="2"><input type="submit" value="' . get_string('auswahl_speichern', 'block_exacomp') . '" /></td></tr>';
        $content .= '</table>';
        

        $content.='</form>';
    }
}
else if ($action == 'detail') {

    if (!empty($_POST["data"])){
    	if (function_exists("clean_param_array")) $subids=clean_param_array($_POST["data"],PARAM_ALPHANUMEXT,true);
    	else $subids=optional_param('data', '', PARAM_ALPHANUMEXT);
    }
    
    if (!empty($_POST["data"])) {
        $subjects = block_exacomp_get_subjects_by_id($subids);
        $content.='<form name="topics" action="courseselection.php?courseid=' . $courseid . '&action=save_coursetopics" method="post">';
        $content .= '<table>';
        $specific=false;
        foreach ($subjects as $subject) {
        	if($subject->source!=1 && !$specific){
        		$specific=true;
        		$content .= '<tr> <td colspan="2"><h2>' . get_string("specificsubject","block_exacomp") . '</h2></td></tr>';
        	}
			
            $content .= '<tr> <td colspan="2"><b>' . $subject->title . '</b></td></tr>';

			function block_exacomp_print_levels($level, $topics) {
				$content = '';
				
				foreach ($topics as $topic) {
					$content .= '<tr><td style="padding-left: '.(25*$level).'px">' . $topic->title . '</td>';
					if (empty($topic->subs)) {
						$content .= '<td><input type="checkbox" alt="Topic" name="data[' . $topic->id . ']" value="' . $topic->id . '" '.($topic->checked?'checked="checked"':'').' /></td>';
					}
					$content .= '</tr>';
					if (!empty($topic->subs)) {
						$content .= block_exacomp_print_levels($level+1, $topic->subs);
					}
				}
				
				return $content;
			}
			$topics = block_exacomp_get_competence_tree_for_subject($courseid, $subject->id);
			$content .= block_exacomp_print_levels(0, $topics);
        }
        $content.='<tr><td colspan="2"><input type="submit" value="' . get_string('auswahl_speichern', 'block_exacomp') . '" /></td></tr>';
        $content .= '</table>';
        

        $content.='</form>';
    } else {
        $content.=get_string('keineauswahl', 'block_exacomp');
        block_exacomp_reset_coursetopics($courseid);
    }
}
if($content)
    echo $OUTPUT->box($content);

echo '</div>';
echo '</div>'; //exabis_competences_block
echo $OUTPUT->footer();