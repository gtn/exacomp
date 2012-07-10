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

//echo CONTEXT_USER."-----";
$context = get_context_instance(CONTEXT_COURSE, $courseid);


//eigen berechtigung für exabis_competences, weil die moodle rollen nicht genau passen, zb
//bei moodle dürfen übergeordnete rollen alles der untergeordneten, dass soll hier nicht sein

if (has_capability('block/exacomp:student', $context)) {
    $introle = 0;
    $role = "student";
}
if (has_capability('block/exacomp:teacher', $context)) {
    $role = "teacher";
    $introle = 1;
}

$url = '/blocks/exacomp/assign_competences.php?courseid=' . $courseid;
$PAGE->set_url($url);
$url = $CFG->wwwroot . $url;
$identifier = "teachertabassigncompetences";
if ($role == "student")
    $identifier = "studenttabcompetences";

block_exacomp_print_header($role, $identifier);

if ($action == "save") {
    $values = array();

    foreach ($_POST['data'] as $key => $desc) {
        if (!empty($_POST['data'][$key])) {
            foreach ($_POST['data'][$key] as $key2 => $wert) {
                // Die Einträge in ein Array speichern
                if ($wert>0){//wenn pulldown und wert 0, kein eintrag, wenn checkbox kein hackerl kommt er gar nicht hierhier
	                $values[] = array('user' => $key2, 'desc' => $key, 'wert' => $wert);
	              }
            }
        }
    }
    block_exacomp_set_descuser($values, $courseid, $USER->id, $introle);
}
echo '<script type="text/javascript" src="lib/wz_tooltip.js"></script>';


echo "<div class='block_excomp_center'>";
if ($role == "teacher") {
    if ($showevaluation == 'on')
        echo $OUTPUT->box(text_to_html(get_string("explainassignoff", "block_exacomp") . '<a href="' . $url . '">'.get_string("hier", "block_exacomp").'.</a>'));
    else
        echo $OUTPUT->box(text_to_html(get_string("explainassignon", "block_exacomp") . '<a href="' . $url . '&amp;showevaluation=on">'.get_string("hier", "block_exacomp").'.</a>'));
}
else {
    if ($showevaluation == 'on')
        echo $OUTPUT->box(text_to_html(get_string("explainassignoffstudent", "block_exacomp") . '<a href="' . $url . '">'.get_string("hier", "block_exacomp").'.</a>'));
    else
        echo $OUTPUT->box(text_to_html(get_string("explainassignonstudent", "block_exacomp") . '<a href="' . $url . '&amp;showevaluation=on">'.get_string("hier", "block_exacomp").'.</a>'));
}
$content.='<form action="assign_competences.php?action=save&amp;courseid=' . $courseid . '" method="post">';
$zeile = "";

$descriptors = block_exacomp_get_descriptors_by_course($courseid);

$context = get_context_instance(CONTEXT_COURSE, $courseid);
if ($role == "teacher"){
		// spalte mit schuelern(=teilnehmer), 5=teilnehmer
    $students = get_role_users(5, $context);
}else{
		//spalte nur teilnehmer selber
    $students = array($USER);
}
if ($showevaluation == 'on')
    $colspan = 2;
else
    $colspan=1;

if ($descriptors) {
    $content.='<div class="ec_td_mo_auto">
		<table id="comps" class="compstable flexible boxaligncenter generaltable">
		<thead>
		<tr class="heading r0">
		<td  colspan="' . (count($students) * $colspan + 1) . '" scope="col"><h2>' . $COURSE->fullname . '</h2></td></tr>
		<tr>';
		
    if ($role == "teacher") {
        $content.='<td class="ec_minwidth ec_activitylist_item"></td>';
        foreach ($students as $student) {
            $content.='<td class="ec_tableheadwidth" colspan="' . $colspan . '">' . $student->lastname . ' ' . $student->firstname . '</td>';
        }
    } else {
        $content.='<td class="ec_activitylist_item" colspan="' . (count($students) * $colspan + 1) . '">' . get_string("compevaluation", "block_exacomp") . '</td>';
    }
    $content.="</tr></thead>";

    if ($showevaluation == 'on') {
        $content.='<tr><td></td>';
        for ($i = 0; $i < count($students); $i++)
            if($role=="teacher")   
                $content.='<td>'.get_string("schueler_short", "block_exacomp").'</td><td>'.get_string("lehrer_short", "block_exacomp").'</td>';
            else
                $content.='<td>'.get_string("lehrer_short", "block_exacomp").'</td><td>'.get_string("schueler_short", "block_exacomp").'</td>';
        $content.='</tr>';
    }

    $trclass = "even";
    foreach ($descriptors as $descriptor) {
        $tempzeile = "";

        if ($trclass == "even") {
            $trclass = "odd";
            $bgcolor = ' style="background-color:#efefef" ';
        } else {
            $trclass = "even";
            $bgcolor = ' style="background-color:#ffffff" ';
        }
        $tempzeile.='<tr class="r2 ' . $trclass . '" ' . $bgcolor . '><td class="ec_minwidth ec_activitylist_item">' . $descriptor->title . '<input type="hidden" value="' . $descriptor->id . '" name="ec_descriptor[' . $descriptor->id . ']" /></td>';
        foreach ($students as $student) {
            
						if ($bewertungsdimensionen==1){ 
							if ($showevaluation == "on"){
                	$tempzeile.='<td onmouseover="Tip(\'###evalteacher' . $descriptor->id . '_' . $student->id . '###\')" onmouseout="UnTip()" class="ec_td_mo"><input type="checkbox" value="1" name="evaluation[' . $descriptor->id . '][' . $student->id . ']" checked="###checkedevaluation' . $descriptor->id . '_' . $student->id . '###" disabled="disabled" /></td>';
               }
							$tempzeile.='<td class="ec_td_mo"><input type="checkbox" value="1" name="data[' . $descriptor->id . '][' . $student->id . ']" checked="###checked' . $descriptor->id . '_' . $student->id . '###" /></td>';
        		}else {
	        			if ($showevaluation == "on"){
	                	$tempzeile.='<td onmouseover="Tip(\'###evalteacher' . $descriptor->id . '_' . $student->id . '###\')" onmouseout="UnTip()" class="ec_td_mo">';
	                	$tempzeile.='###checkedevaluation' . $descriptor->id . '_' . $student->id . '###</td>';
	               }
        				$tempzeile.='<td class="ec_td_mo"><select name="data[' . $descriptor->id . '][' . $student->id . ']">';
        					for ($i=0;$i<=$bewertungsdimensionen;$i++){
        						$tempzeile.='<option value="'.$i.'" selected="###selected' . $descriptor->id . '_' . $student->id . '_'.$i.'###">'.$i.'</option>';
        					}
        					$tempzeile.='</select></td>';
        				}
            
        }
        $competences = block_exacomp_get_competences_by_descriptor($descriptor->id, $courseid, $introle);
        foreach ($competences as $competence) {
          if ($bewertungsdimensionen==1){
            $tempzeile = str_replace('###checked' . $competence->descid . '_' . $competence->userid . '###', 'checked', $tempzeile);
          }else{
          	$tempzeile = str_replace('###selected' . $competence->descid . '_' . $competence->userid . '_'.$competence->wert.'###', 'selected', $tempzeile);
          }
        }
        $tempzeile = preg_replace('/checked="###checked([0-9_])+###"/', '', $tempzeile);
        $tempzeile = preg_replace('/selected="###selected([0-9_])+###"/', '', $tempzeile); //nicht gewählte aktivitäten-descriptorenpaare, checked=... löschen
        $evaluations = block_exacomp_get_competences_by_descriptor($descriptor->id, $courseid, ($introle + 1) % 2);
        foreach ($evaluations as $evaluation) {
        	if ($bewertungsdimensionen==1){
            $tempzeile = str_replace('###checkedevaluation' . $evaluation->descid . '_' . $evaluation->userid . '###', 'checked', $tempzeile);
          }else{
          	$tempzeile = str_replace('###checkedevaluation' . $evaluation->descid . '_' . $evaluation->userid . '###', $evaluation->wert, $tempzeile);
          }
            
            $tempzeile=str_replace('###evalteacher' . $evaluation->descid . '_' . $evaluation->userid . '###',get_string('assessedby','block_exacomp').$evaluation->lastname.' '.$evaluation->firstname,$tempzeile);
        }
        $tempzeile = preg_replace('/checked="###checkedevaluation([0-9_])+###"/', '', $tempzeile);
        $tempzeile = preg_replace('/###checkedevaluation([0-9_])+###/', '', $tempzeile);
        $tempzeile = preg_replace('/###evalteacher([0-9_])+###/', get_string("keine_beurteilung", "block_exacomp"), $tempzeile);
        $tempzeile.='</tr>';

        //Aktivität-Zeile
        if ($trclass == "even") {
            $trclass = "odd";
            $bgcolor = ' style="background-color:#efefef" ';
        } else {
            $trclass = "even";
            $bgcolor = ' style="background-color:#ffffff" ';
        }

        $acticon = block_exacomp_get_activity_icon($descriptor->id);
		$exicon = block_exacomp_get_examplelink($descriptor->id);
	
        $tempzeile.='<tr class="r2 ' . $trclass . '" ' . $bgcolor . '><td>
        <a onmouseover="Tip(\'' . $acticon->text . '\')" onmouseout="UnTip()">' . $acticon->icon . '</a> '.$exicon.'</td>';

				 
        $activities = block_exacomp_get_activities($descriptor->id, $courseid);
        
        foreach ($students as $student) {
            $stdicon = block_exacomp_get_student_icon($activities, $student);
            
            $tempzeile .= '<td colspan="' . $colspan . '"><a onmouseover="Tip(\'' . $stdicon->text . '\')" onmouseout="UnTip()">' . $stdicon->icon . '</a>';
        		//gibt es zugeordnete artefakte in exabis_eportfolio
            if (block_exacomp_exaportexists()){
	            $stdicon = block_exacomp_get_portfolio_icon($student, $descriptor->id);
	            if(isset($stdicon)) {
								if($role=="student")
									$url = 'href="'.$CFG->wwwroot.'/blocks/exaport/view_items.php?courseid='.$courseid.'"';
								else
									$url = 'href="'.$CFG->wwwroot.'/blocks/exaport/shared_views.php?courseid='.$courseid.'"';
								$tempzeile .= '<a '.$url.' onmouseover="Tip(\'' . $stdicon->text . '\')" onmouseout="UnTip()">' . $stdicon->icon . '</a>';
	            }
	          }
            $tempzeile .= '</td>';
        }
	
        $tempzeile.='</tr>';

        $content .= $tempzeile;
        $tempzeile = "";
    }
		$content.='<tr><td id="tdsubmit" colspan="'.(count($students) * $colspan + 1).'"><input type="submit" value="' . get_string('auswahl_speichern', 'block_exacomp') . '" /></td></tr>';
    $content.="</table></div>";
    
    $content.='</form>';
} else {
    $content.=$OUTPUT->box(text_to_html(get_string("explainno_comps", "block_exacomp")));
}

$content.='
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js"></script>
<script type="text/javascript" src="lib/jquery.tablescroll.js"></script>

<script>
/*<![CDATA[*/

jQuery(document).ready(function($)
{
	$("#comps").tableScroll({height:500});
});

/*]]>*/
</script>';
echo $content;
echo "</div>";
echo '</div>'; //exabis_competences_block
echo $OUTPUT->footer();