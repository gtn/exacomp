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
if (file_exists($CFG->dirroot . "/lib/gradelib.php")){
		require_once($CFG->dirroot . "/lib/gradelib.php");
		if (function_exists("grade_get_grades")) $gradelib=true;
		else $gradelib=false;
}else{
	$gradelib=false;
}
global $COURSE, $CFG, $OUTPUT;
$spalten=5;
$zeilenanzahl=5;
$content = "";
$courseid = required_param('courseid', PARAM_INT);
$action = optional_param('action', "", PARAM_ALPHA);
$showevaluation = optional_param('showevaluation', "", PARAM_ALPHA);
$bewertungsdimensionen=block_exacomp_getbewertungsschema($courseid);
require_login($courseid);

$context = get_context_instance(CONTEXT_COURSE,$courseid);

require_capability('block/exacomp:teacher', $context);

$url = '/blocks/exacomp/edit_students.php?courseid=' . $courseid;
$PAGE->set_url($url);
$url = $CFG->wwwroot.$url;
block_exacomp_print_header("teacher", "teachertabassigncompetencesdetail");

if ($action == "save") {
    $values = array();
    foreach ($_POST['data'] as $key => $activitiest) {
        if (!empty($_POST['data'][$key])) {
            foreach ($_POST['data'][$key] as $key2 => $descs) {
            	
                foreach ($_POST['data'][$key][$key2] as $key3 => $wert) {
                    // Die Einträge in ein Array speichern
                    
                    if ($wert>0){ //wenn pulldown und wert 0, kein eintrag, wenn checkbox kein hackerl kommt er gar nicht hierhier
	                    $values[] = array('user' => $key3, 'desc' => $key2, 'activity' => $key,'wert'=> $wert);
	                  }
                }
            }
        }
    }
//print_r($values);
    block_exacomp_set_descusermm($values, $courseid, $USER->id, 1);
}
echo '<script type="text/javascript" src="lib/wz_tooltip.js"></script>';
echo "<div class='block_excomp_center'>";
if($showevaluation=='on')
    echo $OUTPUT->box(text_to_html(get_string("explainstudenteditingoff", "block_exacomp").'<a href="'.$url.'">'.get_string("hier", "block_exacomp").'</a>'));
else
    echo $OUTPUT->box(text_to_html(get_string("explainstudenteditingon", "block_exacomp").'<a href="'.$url.'&amp;showevaluation=on">'.get_string("hier", "block_exacomp").'</a>'));

$content.='<form action="edit_students.php?action=save&amp;courseid=' . $courseid . '" method="post">';
$zeile = "";

$activities = block_exacomp_get_assignments($courseid);
$context = get_context_instance(CONTEXT_COURSE, $courseid);
$students = get_role_users(5, $context);

if($showevaluation == 'on')
        $colspan=2;
    else
        $colspan=1;
if($activities) {
$content.='<div>';
$content.='<div class="spaltenbrowser">';
	if (count($students)>$spalten) $content.=spaltenbrowser(count($students),$spalten);
$content.="</div>";
$content.='
		<table style="empty-cells:hide;border-left:1px solid #E3DFD4;margin-top:4px;" id="comps" class="compstable flexible boxaligncenter generaltable">
		<tr class="heading r0">
		<td id="headerwithcoursename" class="category catlevel1" colspan="' . (count($students)*$colspan + 1) . '" scope="col"><h2>' . $COURSE->fullname . '</h2></td></tr>
		<tr><td></td>';
	$z=1;
	$p=1;
foreach ($students as $student) {
	$content.='<td class="zelle'.$p.'" colspan="'.$colspan.'">' . $student->lastname . ' ' . $student->firstname . '<input type="hidden" value="' . $student->id . '" name="ec_student[' . $student->id . ']" /></td>';
	if ($z==$spalten){ $z=1;$p++;}
  else $z++;
}
$content.="</tr>";

if ($showevaluation == 'on') {
    $content.='<tr><td></td>';
    $z=1;
	$p=1;
    for ($i = 0; $i < count($students); $i++){
      $content.='<td class="zelle'.$p.'">'.get_string("schueler_short", "block_exacomp").'</td><td class="zelle'.$p.'">'.get_string("lehrer_short", "block_exacomp").'</td>';
      if ($z==$spalten){ $z=1;$p++;}
  		else $z++; 
    }
    $content.='</tr>';
}

$trclass = "even";
$zeilenr=0;
foreach ($activities as $activitymod) {
    $activity = block_exacomp_get_coursemodule($activitymod); 	
   			
    if ($trclass == "even") {
        $trclass = "odd";
        $bgcolor = ' style="background-color:#efefef" ';
    } else {
        $trclass = "even";
        $bgcolor = ' style="background-color:#ffffff" ';
    }
		//aufgabe=assignment, titel anzeigen und link 
    //$content.='<tr class="r2 ' . $trclass . '" ' . $bgcolor . '><td colspan="' . (count($students)*$colspan + 1) . '" class="ec_activitylist_item"><a href="' . $CFG->wwwroot . '/mod/assignment/submissions.php?id=' . ($activity->id + $courseid) . '&course='.$courseid.'">' . $activity->name . '</a><input type="hidden" value="' . $activity->id . '" name="ec_activity[' . $activity->id . ']" /></td></tr>';
    $content.='<tr class="r2 ' . $trclass . '" ' . $bgcolor . '><td colspan="' . (count($students)*$colspan + 1) . '" class="ec_activitylist_item"><a href="' . block_exacomp_get_activityurl($activitymod) . '&amp;course='.$courseid.'">' . $activity->name . '</a><input type="hidden" value="' . $activity->id . '" name="ec_activity[' . $activity->id . ']" /></td></tr>';
    $zeile = "";
    $tempzeile = "";

    $descriptors = block_exacomp_get_descriptors($activitymod->id,$courseid);
    
    foreach ($descriptors as $descriptor) {
        if ($trclass == "even") {
            $trclass = "odd";
            $bgcolor = ' style="background-color:#efefef" ';
        } else {
            $trclass = "even";
            $bgcolor = ' style="background-color:#ffffff" ';
        }
		
				$exicon = block_exacomp_get_examplelink($descriptor->id);
        $tempzeile.='<tr class="r2 ' . $trclass . '" ' . $bgcolor . '><td class="ec_minwidth">' . $descriptor->title . '' . $exicon . '<input type="hidden" value="' . $descriptor->id . '" name="ec_activity[' . $activity->id . ']_descriptor[' . $descriptor->id . ']" /></td>';
        //Checkbox für jeden Schüler generieren
         $z=1;
        $p=1;
        foreach ($students as $student) {
        		$gradeicon="";
            if ($activity->modname=="quiz"){
            	$quizresult=block_exacomp_getquizresult($gradelib,$activity->instance,$courseid,$student->id);
            	$gradeicon=$quizresult->icon;
				    }
				    		
							  
            		if ($bewertungsdimensionen==1){ 
            			if($showevaluation=='on')
                		{$tempzeile.='<td class="zelle'.$p.'" ###checkedcomp' . $activity->id . '_' . $descriptor->id . '_' . $student->id . '###><input type="checkbox" name="student[' . $activity->id . '][' . $descriptor->id . '][' . $student->id . ']" checked="###checkedstudent' . $activity->id . '_' . $descriptor->id . '_' . $student->id . '###" disabled /></td>';}
            			$tempzeile.='<td class="zelle'.$p.'" ###checkedcomp' . $activity->id . '_' . $descriptor->id . '_' . $student->id . '###><input value="1" type="checkbox" name="data[' . $activity->id . '][' . $descriptor->id . '][' . $student->id . ']" checked="###checked' . $activity->id . '_' . $descriptor->id . '_' . $student->id . '###" />'.$gradeicon.'</td>';
        				}else {
        					if($showevaluation=='on')
                		{$tempzeile.='<td class="zelle'.$p.'" ###checkedcomp' . $activity->id . '_' . $descriptor->id . '_' . $student->id . '###>###checkedstudent' . $activity->id . '_' . $descriptor->id . '_' . $student->id . '###</td>';}
        					$tempzeile.='<td class="zelle'.$p.'" ###checkedcomp' . $activity->id . '_' . $descriptor->id . '_' . $student->id . '###><select style="float:left;" name="data[' . $activity->id . '][' . $descriptor->id . '][' . $student->id . ']"  >';
        					for ($i=0;$i<=$bewertungsdimensionen;$i++){
        						$tempzeile.='<option value="'.$i.'" selected="###selected' . $activity->id . '_' . $descriptor->id . '_' . $student->id . '_'.$i.'###">'.$i.'</option>';
        					}
        					$tempzeile.='</select> '.$gradeicon;
        					$tempzeile.='</td>';
        				}
        				if ($z==$spalten){ $z=1;$p++;}
        				else $z++;
        }
        $tempzeile .= '</tr>';

        //Checken welche Kompetenzen der Schüler erworben hat
        $competences = block_exacomp_get_competences($descriptor->id, $courseid);
        $evaluations = block_exacomp_get_competences($descriptor->id, $courseid, 0);
        $genericcomps = block_exacomp_get_genericcompetences($descriptor->id, $courseid,1,$bewertungsdimensionen);
        foreach ($competences as $competence) {
        	if ($bewertungsdimensionen==1){
            $tempzeile = str_replace('###checked' . $competence->activityid . '_' . $competence->descid . '_' . $competence->userid . '###', 'checked', $tempzeile);
          }else{
          	$tempzeile = str_replace('###selected' . $competence->activityid . '_' . $competence->descid . '_' . $competence->userid . '_'.$competence->wert.'###', 'selected', $tempzeile);
          }
        }
        foreach ($evaluations as $evaluation) {
        	if ($bewertungsdimensionen==1){
            $tempzeile = str_replace('###checkedstudent' . $evaluation->activityid . '_' . $evaluation->descid . '_' . $evaluation->userid . '###', 'checked', $tempzeile);
          }else{
          	$tempzeile = str_replace('###checkedstudent' . $evaluation->activityid . '_' . $evaluation->descid . '_' . $evaluation->userid . '###', $evaluation->wert, $tempzeile);
          }
        }
        foreach ($genericcomps as $gernericcomp) {
            $tempzeile = str_replace('###checkedcomp' . $activity->id . '_' . $gernericcomp->descid . '_' . $gernericcomp->userid . '###', 'class="competenceok"', $tempzeile);
        }
        $tempzeile = preg_replace('/checked="###checked([0-9_])+###"/', '', $tempzeile); //nicht gewählte aktivitäten-descriptorenpaare, checked=... löschen
        $tempzeile = preg_replace('/selected="###selected([0-9_])+###"/', '', $tempzeile); //nicht gewählte aktivitäten-descriptorenpaare, checked=... löschen
        $tempzeile = preg_replace('/checked="###checkedstudent([0-9_])+###"/', '', $tempzeile);
        $tempzeile = preg_replace('/###checkedstudent([0-9_])+###/', '', $tempzeile);
        $tempzeile = preg_replace('/###checkedcomp([0-9_])+###/', '', $tempzeile);

        $zeile .= $tempzeile;
        $tempzeile = "";
        if ($zeilenr==$zeilenanzahl){
        	
        	if ($trclass == "even") {
            $trclass = "odd";
            $bgcolor = ' style="background-color:#efefef" ';
            $fontcolor = ' style="color:#6c6c6c" ';
	        } else {
	            $trclass = "even";
	            $bgcolor = ' style="background-color:#ffffff" ';
	            $fontcolor = ' style="color:#6c6c6c" ';
	        }
        	$zeile.='<tr class="'.$trclass.'" ' . $bgcolor . '><td></td>';
        	$zi=1;
        	$pi=1;
        	foreach ($students as $student) {
        		$zeile.='<td'.$fontcolor.' ';
        		if($showevaluation=='on') $zeile.=' colspan="2" ';
        		$zeile.='class="zelle'.$pi.'" >'.$student->lastname.' ' . $student->firstname.'</td>';
        		if ($zi==$spalten){ $zi=1;$pi++;}
        		else $zi++;
        	}
        	$zeile.='</tr>';
					$zeilenr=0;
				}
        $zeilenr++;
    }
    $content .= $zeile;
    
}
$content.='<tr><td id="tdsubmit" colspan="'.(count($students)*$colspan + 1).'"><input type="submit" value="' . get_string('auswahl_speichern', 'block_exacomp') . '" /></td></tr>';
$content.="</table></div>";

$content.='</form>';
} else {
    $content.=$OUTPUT->box(text_to_html(get_string("explainno_comps", "block_exacomp")));
}
echo $content;
echo "</div>";
echo '</div>'."\n"; //exabis_competences_block


$content='
<script language="JavaScript">

function hidezelle(nummer,bereiche){
for (z=1;z<=bereiche;z++){
	if (nummer==0) showcell("zelle" + z);
	else if (z==nummer) showcell("zelle" + nummer);
	else hidecell("zelle" + z);
}
if (nummer==0){}
else change_colspan(bereiche); 
}

function change_colspan(anzahl){
/*
document.getElementById("headerwithcoursename").setAttribute("colspan",(anzahl+2));
var elements = document.getElementsByTagName("*");
for(i = 0; i < elements.length; i++) {
if(elements[i].getAttribute("class") == "ec_activitylist_item") {
elements[i].setAttribute("colspan",(anzahl+2));
}}
*/
}
function hidecell(zelle) {

var elements = document.getElementsByTagName("*");
for(i = 0; i < elements.length; i++) {
if(elements[i].getAttribute("class") == zelle) {
elements[i].style.display = "none";
}}}

function showcell(zelle) {

var elements = document.getElementsByTagName("*");

for(i = 0; i < elements.length; i++) {

if(elements[i].getAttribute("class") == zelle) {
elements[i].style.display = "table-cell";

}}}
';
echo $content;
echo '</script>'."\n";
echo $OUTPUT->footer();