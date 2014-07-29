<table class="exabis_comp_info">
		<tr>
			<td><span class="exabis_comp_top_small">Fach</span> <b><?php $schooltype = 	$DB->get_field("block_exacompschooltypes", "title", array("id"=>$selected_subject->stid));
			echo $schooltype;?> </b>
			</td>
			<td><span class="exabis_comp_top_small">Kompetenzbereich/Leitidee</span>
				<b><?php echo $selected_subject->number . " - " . $selected_subject->title; ?>
			</b>
			</td>
			<td><span class="exabis_comp_top_small">Kompetenz</span><b>
				<?php echo $selected_topic->title; ?>
			</b>
			</td>
			
			<td><span class="exabis_comp_top_small">Lernfortschritt</span> <b><?php 
			$cat = $DB->get_record("block_exacompcategories",array("id"=>$selected_topic->cat,"lvl"=>4));
			echo $cat->title; ?> </b>
			</td>
			<td><span class="exabis_comp_top_small">Lernwegliste</span> <b><?php 
			echo substr($schooltype, 0,1).$selected_subject->number.".".$cat->sourceid;
			?> </b>
			</td>
		</tr>

	</table>
	<table class="exabis_comp_top">
		<tr>			
			<?php if ($role != "student") { ?>
			<td rowspan="4" class="comp_grey_97"><b>Anleitung</b>
				<p>Hier können Sie für Ihre Lerngruppen / Klasse vermerken, welche
					Lernmaterialien bearbeitet und welche Lernnachweise erbracht
					wurden. Darüber hinaus können Sie das Erreichen der Teilkompetenzen
					eintragen. Je nach Konzept der Schule kann die Bearbeitung des
					Lernmaterials / das Erreichen einer Teilkompetenz durch Kreuz
					markiert oder die Qualität der Bearbeitung / der
					Kompetenzerreichung gekennzeichnet werden. Keinenfalls müssen die
					Schülerinnen und Schüler alle Materialien bearbeiten. Wenn eine
					(Teil-)kompetenz bereits vorliegt, kann das hier eingetragen
					werden. Die Schülerinnen und Schüler müssen dann keine zugehörigen
					Lernmaterialien bearbeiten.</p>
			</td>
			<?php } else { ?>
			<td class="comp_grey_97"><b>Was du schon können solltest:</b>
				<p>
					<?php echo $selected_topic->requirement;?>
				</p>
			</td>
			<?php } ?>
		</tr>
		
		<tr>
			<?php if ($role == "student") { ?>
			<td class="comp_grey_97"><b>Wofür du das brauchst:</b>
				<p>
					<?php echo $selected_topic->benefit; ?>
				</p>
			</td>
			<?php } ?>
		</tr>
		<tr>
			<?php if ($role == "student") { ?>
			<td class="comp_grey_97"><b>Wie du dein Können prüfen kannst:</b>
				<p>
					<?php echo $selected_topic->knowledgecheck; ?>
				</p>
				<p>
					Ich habe diese Kompetenz erreicht:
					<?php $topicReached = $DB->get_record('block_exacomptopicuser',array("userid"=>$USER->id,"courseid"=>$courseid,"role"=>0,"subjid"=>$selected_subject->id,"topicid"=>$selected_topic->id));
					//if($showevaluation) {
						$topicReachedTeacher = $DB->get_record('block_exacomptopicuser', array("userid"=>$USER->id,"courseid"=>$courseid,"role"=>1,"subjid"=>$selected_subject->id,"topicid"=>$selected_topic->id));
						$schema=block_exacomp_getbewertungsschema($courseid);
						if(!isset($topicReachedTeacher->wert)) {
							$topicReachedTeacher = new stdClass();
							$topicReachedTeacher->wert = 0;
						}
						if(!isset($topicReached->wert)) {
							$topicReached = new stdClass();
							$topicReached->wert = 0;
						}
						
						if($schema == 1)
							echo "S: ".html_writer::checkbox("topiccomp", 1, $topicReached->wert >= ceil($schema/2))." Bestätigung L: ".html_writer::checkbox("topiccomp", 1, $topicReachedTeacher->wert >= ceil($schema/2), "", array("disabled"=>"disabled"));
						else {
							$options = array();
							for($i=0;$i<=$schema;$i++)
								$options[] = $i;
									
							echo "S: ".html_writer::checkbox("topiccomp", $schema, $topicReached->wert >= ceil($schema/2))." Bestätigung L: ". $topicReachedTeacher->wert;
								
							//echo "S: ". html_writer::select($options, "topiccomp", $topicReached->wert, false) ." Bestätigung L: ". $topicReachedTeacher->wert;
						}
							//}else{
						//echo html_writer::checkbox("topiccomp", 1, $topicReached);
					//}
					$activities = block_exacomp_get_activities($selected_topic->id, $courseid, 0);
					if ($stdicon = block_exacomp_get_student_icon($activities, $USER,$courseid,$gradelib,true)) {
								if($stdicon->actSubOccured)
									echo ' &nbsp;<span title="'.s($stdicon->text).'" class="exabis-tooltip">' . $stdicon->icon . '</span>';
					}
					?>
					<input type="hidden" name="topiccompid" value="<?php $selected_topic->id;?>" />
					<input type="hidden" name="subjectcompid" value="<?php echo $selected_subject->id;?>" />
				</p>
			</td>
			<?php } ?>
		</tr>
	</table>
	<br />