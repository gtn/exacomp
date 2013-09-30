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
$PAGE->requires->css('/blocks/exacomp/css/assign_competencies.css');
block_exacomp_print_header("teacher", "teachertabassignactivities");
echo '<script type="text/javascript" src="lib/wz_tooltip.js"></script>';
echo "<div class='exabis_competencies_lis'>";

$content.='<form id="edit-activities" action="edit_activities.php?action=save&amp;courseid=' . $courseid . '" method="post">';

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
	            $modsetting_arr["activities"][]=clean_param($vs,PARAM_SEQUENCE);
	        };
	      }
	      
            $modsetting="";
						if ($modsetting = $DB->get_record("block_exacompsettings", array("course"=>$courseid))){
							$modsetting->activities=serialize($modsetting_arr);
							$DB->update_record('block_exacompsettings', $modsetting);
						}else{
							$curtime=time();
							$modsettingi=array("course" => $courseid,"grading"=>"1","activities"=>serialize($modsetting_arr),"tstamp"=>$curtime);
							$DB->insert_record('block_exacompsettings',$modsettingi);
				}   
        echo $OUTPUT->box(text_to_html(get_string("activitysuccess", "block_exacomp")));
    }
    
    if (!empty($_POST['block_exacomp_niveaufilter'])){
    	$niveau_arr=array();
    	$niveau_arr["niveau"]=array();
    	
    	foreach ($_POST['block_exacomp_niveaufilter'] as $ks=>$vs){
    		if($vs > 0)
    			$niveau_arr["niveau"][]=clean_param($vs,PARAM_SEQUENCE);
    	};
    }
    $zeile = "";
  $shownactivities=array();
	$modules = block_exacomp_get_modules($COURSE->id);
    $colspan = (count($modules) + 1);
    echo $OUTPUT->box(text_to_html(get_string("explaineditactivities_subjects", "block_exacomp")));

    //Inhalt nur zeigen falls Aktivitäten vorhanden sind
    if ($modules) {
        $content.='<div class="grade-report-grader">
		<table id="comps" class="exabis_comp_comp">
		<tr class="heading r0">
		<td class="category catlevel1" scope="col"><h2>' . $COURSE->fullname . '</h2></td>
		<td class="category catlevel1 bottom" colspan="###colspanminus1###" scope="col"><a href="#colsettings">'.get_string('spalten_setting','block_exacomp').'</a> &nbsp;&nbsp;<a href="#colsettings">'.get_string('niveau_filter','block_exacomp').'</a></td>
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
		
		function block_exacomp_print_levels($level, $subs, &$data, $rowgroup_class = '') {
			if (empty($subs)) return;

			extract((array)$data);
			
			if ($level == 0) {
				foreach ($subs as $group) {
					?>
					<tr class="ec_heading">
					<td colspan="###colspannormal###"><h4><?php echo $group->title; ?></h4></td>
					</tr>
					<?php

					block_exacomp_print_levels($level+1, $group->subs, $data);
				}
				
				return;
			}
			
			foreach ($subs as $item) {
				$hasSubs = !empty($item->subs) || !empty($item->descriptors);

				if ($hasSubs) {
					$data->rowgroup++;
					$this_rowgroup_class = 'rowgroup-header rowgroup-header-'.$data->rowgroup.' '.$rowgroup_class;
					$subs_rowgroup_class = 'rowgroup-content rowgroup-content-'.$data->rowgroup.' '.$rowgroup_class;
				} else {
					$this_rowgroup_class = $rowgroup_class;
				}
				
				?>
                <tr class="ec_heading <?php echo $this_rowgroup_class; ?>">
					<td class="rowgroup-arrow" style="padding-left: <?php echo ($level-1)*20+12; ?>px" colspan="###colspannormal###"><div><?php echo $item->title; ?></div></td></tr>
				</tr>
				<?php

				if (isset($item->subs))
					block_exacomp_print_levels($level+1, $item->subs, $data, $subs_rowgroup_class);

				if (!empty($item->descriptors)) {
					foreach ($item->descriptors as $descriptor) {
						$activitiesr = block_exacomp_get_activities($descriptor->id); //alle gewählten aktivitäten eines descriptors, zum sparen von abfragen
						$zeiletemp = str_replace("###descid###", "" . $descriptor->id, $data->zeile);
			
						foreach ($activitiesr as $activietyr) {
							$zeiletemp = str_replace('###checked' . $activietyr->id . '_' . $descriptor->id . '###', 'checked', $zeiletemp);
						}
						$zeiletemp = preg_replace('/checked="###checked([0-9_])+###"/', '', $zeiletemp); //nicht gewählte aktivitäten-descriptorenpaare, checked=... löschen
						echo '<tr class="r2 '.$subs_rowgroup_class.'">';
						echo '<td class="competencetitle" style="padding-left: '.(($level-1)*20+12).'px">' . $descriptor->title . '<input type="hidden" value="' . $descriptor->id . '" name="ec_descr[' . $descriptor->id . ']" /></td>' . $zeiletemp . '</tr>';
					}
					$data->descriptorlist .= ",".$descriptor->id;
				}
			}
		}
		
		$levels = block_exacomp_get_competence_tree_for_activity_selection($courseid,(isset($niveau_arr)) ? $niveau_arr["niveau"] : null);
		$data = (object)array(
			'rowgroup' => 0,
			'courseid' => $courseid,
			'zeile' => $zeile,
			'descriptorlist' => ''
		);
		ob_start();
		block_exacomp_print_levels(0, $levels, $data);
		$content .= ob_get_clean();

		$allDescriptors = $DB->get_fieldset_sql('
				SELECT d.id
				FROM {block_exacompsubjects} s
				JOIN {block_exacomptopics} t ON t.subjid = s.id
				JOIN {block_exacompcoutopi_mm} topmm ON topmm.topicid=t.id AND topmm.courseid=?
				JOIN {block_exacompdescrtopic_mm} desctopmm ON desctopmm.topicid=t.id
				JOIN {block_exacompdescriptors} d ON desctopmm.descrid=d.id
				', array($courseid));
		
		$descriptorlist = implode(",",$allDescriptors);
		
        $content.='<tr><td id="tdsubmit" colspan="###colspannormal###"><input type="submit" value="' . get_string('auswahl_speichern', 'block_exacomp') . '" /></td></tr>';
        $content.="</table></div>";
        
        $content.='<div id="colsettings">';
        
        $content.='<table class="settingtable"><tr><td><table id="block_exacomp_activitysettings" class="compstable flexible boxaligncenter generaltable">
				<tr class="heading r0" >
				<td class="category catlevel1" colspan="2" scope="col"><h2>' . get_string('hide_activities', 'block_exacomp') . '</h2></td>
				</tr><tr><td class="contentcell">';
        
        if (!empty($shownactivities)){
					if (count($shownactivities)<10) $ssize=count($shownactivities)+1;
					else $ssize=11;
					$ssize=11;
					$content.='<select size="'.$ssize.'" name="block_exacomp_activitysetting[]" multiple="multiple">';
						$content.='<option value="-1">  </option>';
						foreach($shownactivities as $k=>$v){
							$content.='<option value="'.$k.'"';
							if ($v["selected"]==1) $content.=' selected="selected"';
							$content.='>'.$v["name"].'</option>';
						}
					$content.='</select></td><td class="contentcell">';
				}
				$content.='<input type="submit" value="' . get_string('hide_activities_save', 'block_exacomp') . '" /></td></tr><tr><td colspan="2">';
				$content.=get_string('hide_activities_descr', 'block_exacomp') . '</td></tr></table></td>';
				$content.='<td>';
				$content.='<table id="block_exacomp_niveaufilter" class="compstable flexible boxaligncenter generaltable">
				<tr class="heading r0" >
				<td class="category catlevel1" colspan="2" scope="col"><h2>' . get_string('niveau_auswahl', 'block_exacomp') . '</h2></td>
				</tr><tr><td class="contentcell">';
				/*niveau selector */
				if (!empty($descriptorlist)){
					$sql="SELECT n.id,n.title FROM {block_exacompdescriptors} d INNER JOIN {block_exacompniveaus} n ON d.niveauid=n.id WHERE d.id IN (".$descriptorlist.") GROUP BY n.id,n.title ORDER BY n.title";
					if ($niveaus = $DB->get_records_sql($sql)){
						if (count($niveaus)<10) $ssize=count($niveaus)+1;
						else $ssize=11;
						$ssize=11;
						if(!isset($niveau_arr)) {
							$niveau_arr=array();
							$niveau_arr["niveau"]=array();
						}
						
						$content.='<select size="'.$ssize.'" name="block_exacomp_niveaufilter[]" multiple="multiple">';
							$content.='<option value="0">  </option>';
							foreach($niveaus as $niveau){
								$content.='<option value="'.$niveau->id.'"';
								if (in_array($niveau->id,$niveau_arr["niveau"])) $content.=' selected="selected"';
								$content.='>'.$niveau->title.'</option>';
							}
							$content.='</select>';
					}
				}
				$content.='</td><td class="contentcell">';
				$content.='<input type="submit" value="' . get_string('niveau_auswahl_save', 'block_exacomp') . '" /></td>';
				
				/*niveau selector end*/
				$content.='</tr><tr><td colspan="2">'.get_string('filter_niveaus_descr', 'block_exacomp').'</td></td></table>';
				$content.='</td></tr></table>';
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
