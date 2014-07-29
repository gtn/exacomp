<table class="exabis_comp_info">
	<tr>
		<td><span class="exabis_comp_top_small">Fach</span> <b><?php $schooltype = 	$DB->get_field("block_exacompschooltypes", "title", array("id"=>$selectedSubject->stid));
		echo $schooltype;?> </b></td>
		<td><span class="exabis_comp_top_small">Kompetenzbereich/Leitidee</span>
			<b><?php echo $selectedSubject->numb . " - " . $selectedSubject->title; ?>
		</b></td>
		<td><span class="exabis_comp_top_small">Kompetenz</span><b> <?php echo $selectedTopic->title; ?>
		</b></td>

		<td><span class="exabis_comp_top_small">Lernfortschritt</span> <b><?php 
		$cat = $DB->get_record("block_exacompcategories",array("id"=>$selectedTopic->catid,"lvl"=>4));
		echo $cat->title; ?> </b></td>
		<td><span class="exabis_comp_top_small">Lernwegliste</span> <b><?php 
		echo substr($schooltype, 0,1).$selectedSubject->numb.".".$cat->sourceid;
		?> </b></td>
	</tr>

</table>
<table class="exabis_comp_top">
	<tr>
		<td class="comp_grey_97"><b>Anleitung</b>
			<p>Hier können Sie für Ihre Lerngruppen / Klasse vermerken, welche
				Lernmaterialien bearbeitet und welche Lernnachweise erbracht wurden.
				Darüber hinaus können Sie das Erreichen der Teilkompetenzen
				eintragen. Je nach Konzept der Schule kann die Bearbeitung des
				Lernmaterials / das Erreichen einer Teilkompetenz durch Kreuz
				markiert oder die Qualität der Bearbeitung / der Kompetenzerreichung
				gekennzeichnet werden. Keinenfalls müssen die Schülerinnen und
				Schüler alle Materialien bearbeiten. Wenn eine (Teil-)kompetenz
				bereits vorliegt, kann das hier eingetragen werden. Die Schülerinnen
				und Schüler müssen dann keine zugehörigen Lernmaterialien
				bearbeiten.</p>
		</td>
	</tr>
</table>
<br />
