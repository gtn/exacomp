<table class="exabis_comp_info">
		<tr>
			<td><span class="exabis_comp_top_small">Fach</span> <b><?php $schooltype = 	$DB->get_field("block_exacompschooltypes", "title", array("id"=>$selectedSubject->stid));
			echo $schooltype;?> </b>
			</td>
			<td><span class="exabis_comp_top_small">Kompetenzbereich/Leitidee</span>
				<b><?php echo $selectedSubject->numb . " - " . $selectedSubject->title; ?>
			</b>
			</td>
			<td><span class="exabis_comp_top_small">Kompetenz</span><b>
				<?php echo $selectedTopic->title; ?>
			</b>
			</td>
			
			<td><span class="exabis_comp_top_small">Lernfortschritt</span> <b><?php 
			$cat = $DB->get_record("block_exacompcategories",array("id"=>$selectedTopic->catid,"level"=>4));
			echo $cat->title; ?> </b>
			</td>
			<td><span class="exabis_comp_top_small">Lernwegliste</span> <b><?php 
			echo substr($schooltype, 0,1).$selectedSubject->numb.".".$cat->sourceid;
			?> </b>
			</td>
		</tr>

	</table>
	<table class="exabis_comp_top">
		<tr>			
			
			<td class="comp_grey_97"><b>Was du schon können solltest:</b>
				<p>
					<?php echo $selectedTopic->requirement;?>
				</p>
			</td>
		</tr>
		
		<tr>
			<?php if (!$isTeacher) { ?>
			<td class="comp_grey_97"><b>Wofür du das brauchst:</b>
				<p>
					<?php echo $selectedTopic->benefit; ?>
				</p>
			</td>
			<?php } ?>
		</tr>
		<tr>
			<?php if (!$isTeacher) { ?>
			<td class="comp_grey_97"><b>Wie du dein Können prüfen kannst:</b>
				<p>
					<?php echo $selectedTopic->knowledgecheck; ?>
				</p>
				<p>
					Ich habe diese Kompetenz erreicht:
					<?php $topicReached = $DB->get_record('block_exacompcompuser',array("comptype"=>1,"userid"=>$USER->id,"courseid"=>$courseid,"role"=>0,"compid"=>$selectedTopic->id));
					//if($showevaluation) {
						$topicReachedTeacher = $DB->get_record('block_exacompcompuser', array("comptype"=>1, "userid"=>$USER->id,"courseid"=>$courseid,"role"=>1,"compid"=>$selectedTopic->id));
						$schema=block_exacomp_get_grading_scheme($courseid);
						
						if(!isset($topicReachedTeacher->value)) {
							$topicReachedTeacher = new stdClass();
							$topicReachedTeacher->value = 0;
						}
						if(!isset($topicReached->value)) {
							$topicReached = new stdClass();
							$topicReached->value = 0;
						}
						
						if($schema == 1)
							echo "S: ".html_writer::checkbox("topiccomp", 1, $topicReached->value >= ceil($schema/2))." Bestätigung L: ".html_writer::checkbox("topiccomp", 1, $topicReachedTeacher->value >= ceil($schema/2), "", array("disabled"=>"disabled"));
						else {
							$options = array();
							for($i=0;$i<=$schema;$i++)
								$options[] = $i;
									
							echo "S: ".html_writer::checkbox("topiccomp", $schema, $topicReached->value >= ceil($schema/2))." Bestätigung L: ". $topicReachedTeacher->value;
								
					}
						
					$activities = block_exacomp_get_activities($selectedTopic->id, $courseid, 0);
					
					if ($stdicon = block_exacomp_get_icon_for_user($activities, $USER)) {
						echo ' &nbsp;<span title="'.s($stdicon->text).'" class="exabis-tooltip">' . $stdicon->img . '</span>';
					}
					?>
					<input type="hidden" name="topiccompid" value="<?php $selectedTopic->id;?>" />
					<input type="hidden" name="subjectcompid" value="<?php echo $selectedSubject->id;?>" />
				</p>
			</td>
			<?php } ?>
		</tr>
	</table>
	<br />