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

global $COURSE, $CFG, $OUTPUT, $USER;
$content = "";
$courseid = required_param('courseid', PARAM_INT);
$action = optional_param('action', "", PARAM_ALPHA);
$showevaluation = optional_param('showevaluation', "", PARAM_ALPHA);
$bewertungsdimensionen=block_exacomp_getbewertungsschema($courseid);
require_login($courseid);

$context = get_context_instance(CONTEXT_COURSE, $courseid);
require_capability('block/exacomp:student', $context);

$url = '/blocks/exacomp/evaluate_competences.php?courseid=' . $courseid;
$PAGE->set_url($url);
$url = $CFG->wwwroot . $url;
block_exacomp_print_header("student", "studenttabcompetencesdetail");

if ($action == "save") {
    $values = array();
    foreach ($_POST['data'] as $key => $activitiest) {
        if (!empty($_POST['data'][$key])) {
            foreach ($_POST['data'][$key] as $key2 => $descs) {
                foreach ($_POST['data'][$key][$key2] as $key3 => $wert) {
                    // Die Einträge in ein Array speichern
                     if ($wert>0){//wenn pulldown und wert 0, kein eintrag, wenn checkbox kein hackerl kommt er gar nicht hierhier
		                    $values[] = array('user' => $key3, 'desc' => $key2, 'activity' => $key,'wert' => $wert);
		                  }
                }
            }
        }
    }

    block_exacomp_set_descusermm($values, $courseid, $USER->id, 0);
}
echo "<div class='block_excomp_center'>";


if ($showevaluation == 'on')
    echo $OUTPUT->box(text_to_html(get_string("explainevaluationoff", "block_exacomp") . '<a href="' . $url . '">'.get_string("hier", "block_exacomp").'.</a>'));
else
    echo $OUTPUT->box(text_to_html(get_string("explainevaluationon", "block_exacomp") . '<a href="' . $url . '&amp;showevaluation=on">'.get_string("hier", "block_exacomp").'.</a>'));
$content.='<form action="evaluate_competences.php?action=save&amp;courseid=' . $courseid . '" method="post">';
$zeile = "";

$activities = block_exacomp_get_assignments($courseid);
$context = get_context_instance(CONTEXT_COURSE, $courseid);
$student = $USER;

if ($showevaluation == 'on')
    $colspan = 2;
else
    $colspan=1;

if ($activities) {
    $content.='<div style="text-align:left;">
		<table id="comps" class="compstable flexible boxaligncenter generaltable">
		<tr class="heading r0">
		<td  colspan="' . ($colspan + 1) . '" scope="col"><span><b>' . $COURSE->fullname . '</b></span></td></tr>
		<tr>';

    $content.='<td class="ec_activitylist_item" colspan="' . ($colspan + 1) . '">' . get_string("compdetailevaluation", "block_exacomp") . '<input type="hidden" value="' . $student->id . '" name="ec_student[' . $student->id . ']" /></td>';
// foreach ($descriptors as $descriptor) {
//        $content.='<td>' . $descriptor->title . '<input type="hidden" value="' . $descriptor->id . '" name="ec_activity[' . $activity->id . ']_descriptor[' . $descriptor->id . ']" /></td>';
// $zeile.='<td><input type="checkbox" name="data[###activityid###][###descriptorid###][' . $student->id . ']" checked="###checked###activityid_###descritptorid###_' . $student->id . '###" /></td>';

    $content.="</tr>";
    if ($showevaluation == 'on') {
        $content.='<tr><td></td>';
        $content.='<td>L</td><td>S</td>';
        $content.='</tr>';
    }
    $trclass = "even";
    foreach ($activities as $activitymod) {
    	
    	$mod = $DB->get_record('modules',array("id"=>$activitymod->module));
    	$activity = get_coursemodule_from_id($mod->name, $activitymod->id);
        
        if ($trclass == "even") {
            $trclass = "odd";
            $bgcolor = ' style="background-color:#efefef" ';
        } else {
            $trclass = "even";
            $bgcolor = ' style="background-color:#ffffff" ';
        }

        $content.='<tr class="r2 ' . $trclass . '" ' . $bgcolor . '><td colspan="' . ($colspan + 1) . '" class="ec_activitylist_item"><a href="' . block_exacomp_get_activityurl($activity,true) .'">' . $activity->name . '</a><input type="hidden" value="' . $activitymod->id . '" name="ec_activity[' . $activitymod->id . ']" /></td></tr>';
        $zeile = "";
        $tempzeile = "";

        $descriptors = block_exacomp_get_descriptors($activitymod->id, $courseid);
        foreach ($descriptors as $descriptor) {
            if ($trclass == "even") {
                $trclass = "odd";
                $bgcolor = ' style="background-color:#efefef" ';
            } else {
                $trclass = "even";
                $bgcolor = ' style="background-color:#ffffff" ';
            }
            $tempzeile.='<tr class="r2 ' . $trclass . '" ' . $bgcolor . '><td class="ec_minwidth">' . $descriptor->title . '<input type="hidden" value="' . $descriptor->id . '" name="ec_activity[' . $activity->id . ']_descriptor[' . $descriptor->id . ']" /></td>';
            //Checkbox für jeden Schüler generieren
            


            if ($bewertungsdimensionen==1){ 
            	if ($showevaluation == 'on')
              {$tempzeile.='<td ###checkedcomp' . $activity->id . '_' . $descriptor->id . '_' . $student->id . '###><input type="checkbox" name="teacher[' . $activity->id . '][' . $descriptor->id . '][' . $student->id . ']" checked="###checkedteacher' . $activity->id . '_' . $descriptor->id . '_' . $student->id . '###" disabled="disabled" /></td>';}
              $tempzeile.='<td ###checkedcomp' . $activity->id . '_' . $descriptor->id . '_' . $student->id . '###>';
            	$tempzeile.='<input type="checkbox" value="1" name="data[' . $activity->id . '][' . $descriptor->id . '][' . $student->id . ']" checked="###checked' . $activity->id . '_' . $descriptor->id . '_' . $student->id . '###" /></td>';
            }else {
            	if ($showevaluation == 'on')
              	{$tempzeile.='<td ###checkedcomp' . $activity->id . '_' . $descriptor->id . '_' . $student->id . '###>###checkedteacher' . $activity->id . '_' . $descriptor->id . '_' . $student->id . '###</td>';}
            	 $tempzeile.='<td ###checkedcomp' . $activity->id . '_' . $descriptor->id . '_' . $student->id . '###>';
        				$tempzeile.='<select name="data[' . $activity->id . '][' . $descriptor->id . '][' . $student->id . ']">';
        				for ($i=0;$i<=$bewertungsdimensionen;$i++){
        					$tempzeile.='<option value="'.$i.'" selected="###selected' . $activity->id . '_' . $descriptor->id . '_' . $student->id . '_'.$i.'###">'.$i.'</option>';
        				}
        				$tempzeile.='</select></td>';
        		}
            
            $tempzeile .= '</tr>';

            //Checken welche Kompetenzen der Schüler erworben hat
            $competences = block_exacomp_get_competences($descriptor->id, $courseid, 0);
            $evaluations = block_exacomp_get_competences($descriptor->id, $courseid, 1);
            $genericcomps = block_exacomp_get_genericcompetences($descriptor->id, $courseid, 0,$bewertungsdimensionen);
            
            foreach ($competences as $competence) {
            	
		          if ($bewertungsdimensionen==1){
		            $tempzeile = str_replace('###checked' . $competence->activityid . '_' . $competence->descid . '_' . $competence->userid . '###', 'checked', $tempzeile);
		          }else{
		          	$tempzeile = str_replace('###selected' . $competence->activityid . '_' . $competence->descid . '_' . $competence->userid . '_'.$competence->wert.'###', 'selected', $tempzeile);
		          }
            }
            foreach ($evaluations as $evaluation) {
            	if ($bewertungsdimensionen==1){
                $tempzeile = str_replace('###checkedteacher' . $evaluation->activityid . '_' . $evaluation->descid . '_' . $evaluation->userid . '###', 'checked', $tempzeile);
            	}else{
            		 $tempzeile = str_replace('###checkedteacher' . $evaluation->activityid . '_' . $evaluation->descid . '_' . $evaluation->userid . '###', $evaluation->wert, $tempzeile);
            	}
            }
            foreach ($genericcomps as $gernericcomp) {
                $tempzeile = str_replace('###checkedcomp' . $activity->id . '_' . $gernericcomp->descid . '_' . $gernericcomp->userid . '###', 'class="competenceok ec_firstcol"', $tempzeile);
            }
            $tempzeile = preg_replace('/checked="###checked([0-9_])+###"/', '', $tempzeile); //nicht gewählte aktivitäten-descriptorenpaare, checked=... löschen
           	$tempzeile = preg_replace('/selected="###selected([0-9_])+###"/', '', $tempzeile); //nicht gewählte aktivitäten-descriptorenpaare, checked=... löschen
            $tempzeile = preg_replace('/checked="###checkedteacher([0-9_])+###"/', '', $tempzeile);
            $tempzeile = preg_replace('/###checkedteacher([0-9_])+###/', '', $tempzeile);
            $tempzeile = preg_replace('/###checkedcomp([0-9_])+###/', 'class="ec_firstcol"', $tempzeile);

            $zeile .= $tempzeile;
            $tempzeile = "";
        }
        $content .= $zeile;
    }
    $content.='<tr><td colspan="'.($colspan + 1).'"><input id="tdsubmit" type="submit" value="' . get_string('auswahl_speichern', 'block_exacomp') . '" /></td></tr>';
    $content.="</table></div>";
    
    $content.='</form>';
} else {
    $content.=$OUTPUT->box(text_to_html(get_string("explainno_comps", "block_exacomp")));
}
echo $content;
echo "</div>";
echo '</div>'; //exabis_competences_block
echo $OUTPUT->footer();