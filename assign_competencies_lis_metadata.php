<table class="exabis_comp_info">
		<tr>
			<td><span class="exabis_comp_top_small">Lehrkraft</span> <b><?php 
			$context = get_context_instance(CONTEXT_COURSE, $courseid);
			$teachers = get_users_by_capability($context, 'block/exacomp:teacher', 'u.id, u.firstname, u.lastname');
			if ($teachers) {
				echo fullname(reset($teachers));
			}
			?> </b>
			</td>
			<td><span class="exabis_comp_top_small">Klasse</span> <b><?php echo $COURSE->shortname; ?>
			</b>
			</td>
			<td><span class="exabis_comp_top_small">Schuljahr</span> <b>13/14</b>
			</td>
			<td><span class="exabis_comp_top_small">Fach</span> <b><?php $schooltype = 	$DB->get_field("block_exacompschooltypes", "title", array("id"=>$selected_subject->stid));
			echo $schooltype;?> </b>
			</td>
			<td><span class="exabis_comp_top_small">Kompetenzbereich/Leitidee</span>
				<b><?php echo $selected_subject->number . " - " . $selected_subject->title; ?>
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
			<td><span class="exabis_comp_top_small">Teilkompetenz</span> <span
				class="exabis_comp_top_header"><?php echo $selected_topic->title; ?>
			</span>
			</td>
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
			<td rowspan="2" class="comp_grey_97"><b>Was du schon können solltest:</b>
				<p>
					<?php echo $selected_topic->requirement;?>
				</p>
			</td>
			<?php } ?>
		</tr>
		<tr>
			<td class="comp_grey_97">
				<div class="exabis_comp_top_indentation_nr">A:</div>
				<div class="exabis_comp_top_indentation">
					<?php echo $selected_topic->ataxonomie; ?>
				</div>
			</td>

		</tr>
		<tr>
			<td class="comp_grey_90">
				<div class="exabis_comp_top_indentation_nr">B:</div>
				<div class="exabis_comp_top_indentation">
					<?php echo $selected_topic->btaxonomie; ?>
				</div>
			</td>
			<?php if ($role == "student") { ?>
			<td class="comp_grey_97"><b>Wofür du das brauchst:</b>
				<p>
					<?php echo $selected_topic->benefit; ?>
				</p>
			</td>
			<?php } ?>
		</tr>
		<tr>
			<td class="comp_grey_83">
				<div class="exabis_comp_top_indentation_nr">C:</div>
				<div class="exabis_comp_top_indentation">
					<?php echo $selected_topic->ctaxonomie; ?>
				</div>
			</td>
			<?php if ($role == "student") { ?>
			<td class="comp_grey_97"><b>Wie du dein Können prüfen kannst:</b>
				<p>
					<?php echo $selected_topic->knowledgecheck; ?>
				</p>
			</td>
			<?php } ?>
		</tr>
	</table>
	<br />