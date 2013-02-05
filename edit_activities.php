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

global $COURSE, $CFG, $OUTPUT,$DB;

if(strcmp("mysql",$CFG->dbtype)==0){
	$sql5="SET @@group_concat_max_len = 5012";

	$DB->execute($sql5);
}
$content = "";
$courseid = required_param('courseid', PARAM_INT);
$action = optional_param('action', "", PARAM_ALPHA);

require_login($courseid);

$context = get_context_instance(CONTEXT_COURSE, $courseid);

require_capability('block/exacomp:teacher', $context);

$PAGE->set_url('/blocks/exacomp/edit_activities.php?courseid=' . $courseid);

block_exacomp_print_header("teacher", "teachertabassignactivities");
echo '<script type="text/javascript" src="lib/wz_tooltip.js"></script>';
echo "<div class='block_excomp_center'>";

$content.='<form action="edit_activities.php?action=save&amp;courseid=' . $courseid . '" method="post">';

if ($courseid > 0) {
    if ($action == "save") {
    	if(!empty($_POST['ec_activity'])){
        foreach ($_POST['ec_activity'] as $key => $activitiest) {
            $wert = "";
            if (!empty($_POST['data'][$key])) {

                foreach ($_POST['data'][$key] as $key2 => $descs) {
                    $wert.="," . $key2;
                    $wert = preg_replace("/^,/", "", $wert);
                }
            } else {
                $wert = "";
            }
            //echo $wert."--".$key."<br>";
						block_exacomp_set_descractivitymm($wert, $key);
        }
      }
        $modsetting_arr=array();
        if (!empty($_POST['block_exacomp_activitysetting'])){
	        foreach ($_POST['block_exacomp_activitysetting'] as $ks=>$vs){
	            $modsetting_arr["activities"][]=$vs;
	        };
	      }
            $modsetting="";
						if ($modsetting = $DB->get_record("block_exacompsettings", array("course"=>$courseid))){
							$modsetting->activities=serialize($modsetting_arr);
							$DB->update_record('block_exacompsettings', $modsetting);
						}else{
							$modsettingi=array("course" => $courseid,"grading"=>"1","activities"=>serialize($modsetting_arr));
							$DB->insert_record('block_exacompsettings',$modsettingi);
				}   
        echo $OUTPUT->box(text_to_html(get_string("activitysuccess", "block_exacomp")));
    }
    $zeile = "";
  $shownactivities=array();
	$modules = block_exacomp_get_modules();
    $colspan = (count($modules) + 1);
    echo $OUTPUT->box(text_to_html(get_string("explaineditactivities_subjects", "block_exacomp")));

    //Inhalt nur zeigen falls Aktivitäten vorhanden sind
    if ($modules) {
        $content.='<div class="grade-report-grader">
		<table id="comps" class="compstable flexible boxaligncenter generaltable">
		<tr class="heading r0">
		<td class="category catlevel1" scope="col"><h2>' . $COURSE->fullname . '</h2></td>
		<td class="category catlevel1 bottom" colspan="###colspanminus1###" scope="col"><a href="#colsettings">'.get_string('spalten_setting','block_exacomp').'</a></td>
		</tr>
		<tr><td></td>';
				if($modsetting = $DB->get_record("block_exacompsettings", array("course"=>$courseid))){
					$modhide=unserialize($modsetting->activities);
				}
				if(empty($modhide)) $modhide=array();
				if (!array_key_exists("activities",$modhide)) $modhide["activities"]=array();
        foreach ($modules as $mod) {
        	if(!$mod->visible){
        		$colspan=($colspan-1);continue;
        	}
        	$module = $activity = block_exacomp_get_coursemodule($mod);

        	//Skip Nachrichtenforum
        	if($module->name == get_string('namenews','mod_forum')){
        		$colspan=($colspan-1);continue;
        	}
        	$shownactivities[$module->id]["name"]=$module->name;
        	if(in_array($mod->id,$modhide["activities"])){
        		$shownactivities[$module->id]["selected"]=1;
        		$colspan=($colspan-1);continue;
        	}else{
        		$shownactivities[$module->id]["selected"]=0;
        	}
        	
        	$content.='<td class="ec_tableheadwidth"><a href="' . block_exacomp_get_activityurl($module). '">' . $module->name . '</a><input type="hidden" value="' . $module->id . '" name="ec_activity[' . $module->id . ']" /></td>';
        	$zeile.='<td><input type="checkbox" name="data[' . $module->id . '][###descid###]" checked="###checked' . $module->id . '_###descid######" /></td>';
        	
        }
        $content.="</tr>";
        $descriptors = block_exacomp_get_descritors_list($courseid);
        $trclass = "even";
        $topic = "";
        $subject = "";

        foreach ($descriptors as $descriptor) {
            if ($trclass == "even") {
                $trclass = "odd";
                $bgcolor = ' style="background-color:#efefef" ';
            } else {
                $trclass = "even";
                $bgcolor = ' style="background-color:#ffffff" ';
            }
            if ($subject !== $descriptor->subject) {
                $subject = $descriptor->subject;
                $content .= '<tr class="ec_heading"><td colspan="###colspannormal###"><h4>' . $subject . '</h4></td></tr>';
            }
            if ($topic !== $descriptor->topic) {
                $topic = $descriptor->topic;
                $content .= '<tr class="ec_heading"><td colspan="###colspannormal###"><b>' . $topic . '</b></td></tr>';
            }
            $activitiesr = block_exacomp_get_activities($descriptor->id); //alle gewählten aktivitäten eines descriptors, zum sparen von abfragen
            $zeiletemp = str_replace("###descid###", "" . $descriptor->id, $zeile);
						$exicon = block_exacomp_get_examplelink($descriptor->id);
			
            foreach ($activitiesr as $activietyr) {
                $zeiletemp = str_replace('###checked' . $activietyr->id . '_' . $descriptor->id . '###', 'checked', $zeiletemp);
            }
            $zeiletemp = preg_replace('/checked="###checked([0-9_])+###"/', '', $zeiletemp); //nicht gewählte aktivitäten-descriptorenpaare, checked=... löschen
            $content.='<tr class="r2 ' . $trclass . '" ' . $bgcolor . '><td class="ec_minwidth">' . $descriptor->title . '<input type="hidden" value="' . $descriptor->id . '" name="ec_descr[' . $descriptor->id . ']" /></td>' . $zeiletemp . '</tr>';
        }
        $content.='<tr><td id="tdsubmit" colspan="###colspannormal###"><input type="submit" value="' . get_string('auswahl_speichern', 'block_exacomp') . '" /></td></tr>';
        $content.="</table></div>";
        
        $content.='<div id="colsettings">';
        
        $content.='<table id="comps" class="compstable flexible boxaligncenter generaltable">
				<tr class="heading r0" >
				<td class="category catlevel1" colspan="2" scope="col"><h2>' . get_string('hide_activities', 'block_exacomp') . '</h2></td>
				</tr><tr><td>';
        
        if (!empty($shownactivities)){
					if (count($shownactivities)<10) $ssize=count($shownactivities)+1;
					else $ssize=11;
					$content.='<select size="'.$ssize.'" name="block_exacomp_activitysetting[]" multiple="multiple">';
						$content.='<option value="-1">  </option>';
						foreach($shownactivities as $k=>$v){
							$content.='<option value="'.$k.'"';
							if ($v["selected"]==1) $content.=' selected="selected"';
							$content.='>'.$v["name"].'</option>';
						}
					$content.='</select></td><td>';
				}
				$content.='<input type="submit" value="' . get_string('hide_activities_save', 'block_exacomp') . '" /></td></tr><tr><td colspan="2">';
				$content.=get_string('hide_activities_descr', 'block_exacomp') . '</td></tr></table>';
				$content.='</div>';

        $content.='</form>';
        $content=str_replace("###colspannormal###",$colspan,$content);
        $content=str_replace("###colspanminus1###",($colspan-1),$content);
    } else {
        echo $OUTPUT->box(text_to_html(get_string("explainno_subjects", "block_exacomp")));
    }
}

$content.="";
echo $content;
echo "</div>";
echo '</div>'; //exabis_competences_block
echo $OUTPUT->footer();
