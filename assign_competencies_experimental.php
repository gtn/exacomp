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
		$gradelib = function_exists("grade_get_grades");
}else{
	$gradelib=false;
}

$courseid = required_param('courseid', PARAM_INT);
require_login($courseid);

$schueler_gruppierung_breite=5;
$zeilenanzahl=5;
$content = "";
$action = optional_param('action', "", PARAM_ALPHA);
$showevaluation = optional_param('showevaluation', "", PARAM_ALPHA);
$bewertungsdimensionen=block_exacomp_getbewertungsschema($courseid);

$context = get_context_instance(CONTEXT_COURSE, $courseid);


//eigen berechtigung für exabis_competences, weil die moodle rollen nicht genau passen, zb
//bei moodle dürfen übergeordnete rollen alles der untergeordneten, dass soll hier nicht sein

if (has_capability('block/exacomp:student', $context)) {
	$introle = 0;
	$role = "student";
}
if (has_capability('block/exacomp:teacher', $context)) {
	$introle = 1;
	$role = "teacher";
}

$url = '/blocks/exacomp/assign_competencies_experimental.php?courseid=' . $courseid;
$PAGE->set_url($url);
$url = $CFG->wwwroot . $url;
$identifier = "teachertabassigncompetences";
if ($role == "student")
	$identifier = "studenttabcompetences";

$PAGE->requires->css('/blocks/exacomp/css/assign_competencies.css');
$PAGE->requires->js('/blocks/exacomp/javascript/jquery.js', true);
$PAGE->requires->js('/blocks/exacomp/javascript/exacomp.js', true);

block_exacomp_print_header($role, $identifier);

if ($action == "save" && isset($_POST['btn_submit'])) {
	$values = array();
  if (!empty($_POST['data'])){
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
	}
	block_exacomp_set_descuser($values, $courseid, $USER->id, $introle);
}

$context = get_context_instance(CONTEXT_COURSE, $courseid);
if ($role == "teacher"){
	// spalte mit schuelern(=teilnehmer), 5=teilnehmer
	$students = get_role_users(5, $context);
	$students = array_merge($students, $students, $students);
	$students = array_merge($students, $students, $students);
}else{
	//spalte nur teilnehmer selber
	$students = array($USER);
}

$descriptors = block_exacomp_get_descriptors_by_course($courseid);

?>
<div class="exabis_competencies_lis">

<div class="exabis_comp_select">
Fach auswählen:
<select class="start-searchbox-select" name="gemeinde">
<option value=""></option>
<option value="">Mathematik</option>
<option value="">Deutsch</option>
<option value="">Englisch</option>
</select>

Kompetenzbereich/Leitidee auswählen:
<select class="start-searchbox-select" name="gemeinde">
<option value=""></option>
<option value="">4-Messen</option>
<option value="">4-Messen</option>
<option value="">4-Messen Blindtext</option>
</select>
</div>

<table class="exabis_comp_info">
	<tr>
		<td><span class="exabis_comp_top_small">Lehrkraft</span>
			<b>LH</b>
		</td>
		<td><span class="exabis_comp_top_small">Klasse</span>
			<b>4B</b>
		</td>
		<td><span class="exabis_comp_top_small">Schuljahr</span>
			<b>13/14</b>
		</td>
		<td><span class="exabis_comp_top_small">Fach</span>
			<b>Mathematik</b>
		</td>
		<td><span class="exabis_comp_top_small">Kompetenzbereich/Leitidee</span>
			<b>4 - Messen</b>
		</td>
		<td><span class="exabis_comp_top_small">Lernfortschritt</span>
			<b>LF 6</b>
		</td>
		<td><span class="exabis_comp_top_small">Lernwegliste</span>
			<b>M4.6</b>
		</td>
	</tr>
	
</table>




<table class="exabis_comp_top">
	<tr>
		<td>
			<span class="exabis_comp_top_small">Teilkompetenz</span>
			<span class="exabis_comp_top_header">Ich kann Rauminhalt und Oberflächeninhalte von Quadern berechnen und mit Volumenmaßen umgehen</span>
		</td>
		<td rowspan="4" class="comp_grey_97">
			<b>Anleitung</b>
			<p>
				Hier können Sie für Ihre Lerngruppen / Klasse vermerken, welche Lernmaterialien bearbeitet und welche Lernnachweise erbracht wurden. Darüber hinaus können Sie das Erreichen der Teilkompetenzen eintragen. Je nach Konzept der Schule kann die Bearbeitung des Lernmaterials / das Erreichen einer Teilkompetenz durch Kreuz markiert oder die Qualität der Bearbeitung / der Kompetenzerreichung gekennzeichnet werden. Keinenfalls müssen die Schülerinnen und Schüler alle Materialien bearbeiten. Wenn eine (Teil-)kompetenz bereits vorliegt, kann das hier eingetragen werden. Die Schülerinnen und Schüler müssen dann keine zugehörigen Lernmaterialien bearbeiten.
			</p>
		</td>
	</tr>
	<tr>
		<td class="comp_grey_97">
		<div class="exabis_comp_top_indentation_nr">A:</div>
			<div class="exabis_comp_top_indentation">Ich kann das Volumen von Quadern berechnen. Ich kann Raum- und Hohlmaße in benachbarte Einheiten umwandeln. Ich kann das Volumen von Körpern durch Ausfüllen bestimmen.</div>
		</td>
		
	</tr>
	<tr>
		<td class="comp_grey_90">
			<div class="exabis_comp_top_indentation_nr">B:</div>
			<div class="exabis_comp_top_indentation">Ich kann den Oberflächeninhalt von Quadern ermitteln. Ich kann Oberflächeninhalt und Volumen von realen, quaderförmigen Gegenständen durch Messen und Berechnen ermitteln.</div>
		</td>
		
	</tr>
	<tr>
		<td class="comp_grey_83">
			<div class="exabis_comp_top_indentation_nr">C:</div>
			<div class="exabis_comp_top_indentation">Ich kann Raummaße in Einheiten umwandeln, die der Sachsituation angemessen sind. Ich kann Volumen und Oberflächeninhalt von zusammengesetzten Körpern berechnen.</div>
		</td>
		
	</tr>
</table>
<br />
<div class="exabis_comp_top_legend">
<img src="pix/list_12x11.png" alt="Aktivitäten" /> Aktivitäten - <img src="pix/folder_fill_12x12.png" alt="ePortfolio" /> ePortfolio - <img src="pix/x_11x11.png" alt="Leer" /> noch keine Aufgaben zu diesem Deskriptor abgegeben und keinen Test durchgeführt
</div>

<?php
if ($descriptors) {
	?>
<div class="ec_td_mo_auto"><?php
	echo spaltenbrowser_v2(count($students),$schueler_gruppierung_breite);
?>

<form action="assign_competencies_experimental.php?action=save&courseid=<?php echo $courseid; ?>" method="post">
<table class="exabis_comp_comp">
	<thead>
		<tr>
			<td colspan="2"><b>Teilkompetenzen und Lernmaterialien</b></td>
			<?php
				$columnCnt = 0;
				foreach ($students as $student) {
					echo '<td class="exabis_comp_top_studentcol colgroup colgroup-'.floor($columnCnt++/$schueler_gruppierung_breite).'">'.fullname($student).'</td>';
				}
			?>
		</tr>
	</thead>
	<?php
		$rowgroup = 0;
        foreach ($descriptors as $descriptor) {
			$rowgroup++;
			/*
			if ($subject !== $descriptor->subject) {
                $subject = $descriptor->subject;
                $content .= '<tr class="ec_heading"><td colspan="###colspannormal###"><h4>' . $subject . '</h4></td></tr>';
            }
            if ($topic !== $descriptor->topic) {
                $topic = $descriptor->topic;
                $content .= '<tr class="ec_heading"><td colspan="###colspannormal###"><b>' . $topic . '</b></td></tr>';
            }
			*/

			?>
			<tr class="exabis_comp_teilcomp rowgroup-header rowgroup-<?php echo $rowgroup; ?>">
			<td>A01</td>
			<td class="rowgroup-arrow"><div><?php echo $descriptor->title; ?></div></td>
			<?php
				$columnCnt = 0;
				foreach ($students as $student) {
					echo '<td class="colgroup colgroup-'.floor($columnCnt++/$schueler_gruppierung_breite).'"><input type="checkbox" value="1" name="data[' . $descriptor->id . '][' . $student->id . ']" checked="checked"><img src="pix/folder_fill_12x12.png" alt="ePortfolio" /><img src="pix/list_12x11.png" alt="Aktivitäten" /></td>';
				}
			?>
			</tr>
			<tr class="exabis_comp_aufgabe rowgroup-content rowgroup-<?php echo $rowgroup; ?>">
				<td></td>
				<td>LM1.1</td>
				<?php
					$columnCnt = 0;
					foreach ($students as $student) {
						echo '<td class="colgroup colgroup-'.floor($columnCnt++/$schueler_gruppierung_breite).'">
							<input type="checkbox" value="1" name="datatodo[' . $descriptor->id . '][' . $student->id . ']" checked="checked">
						</td>';
					}
				?>
			</tr>
			<tr class="exabis_comp_aufgabe rowgroup-content rowgroup-<?php echo $rowgroup; ?>"">
				<td></td>
				<td>LM1.2</td>
				<?php
					$columnCnt = 0;
					foreach ($students as $student) {
						echo '<td class="colgroup colgroup-'.floor($columnCnt++/$schueler_gruppierung_breite).'">
							<input type="checkbox" value="1" name="datatodo[' . $descriptor->id . '][' . $student->id . ']" checked="checked">
						</td>';
					}
				?>
			</tr>
				<?php
		}
	?>
		<tr class="exabis_comp_teilcomp">
			<td>A02</td>
			<td><b><img src="pix/pfeil_show.png" class="exabis_comp_teilcomp_pfeil" />&nbsp;<a href="#">Ich kann das Volumen von Quadern berechnen</a></b></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"><img src="pix/x_11x11.png" alt="Leer" /></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"><img src="pix/x_11x11.png" alt="Leer" /></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"><img src="pix/folder_fill_12x12.png" alt="ePortfolio" /><img src="pix/list_12x11.png" alt="Aktivitäten" /></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"><img src="pix/x_11x11.png" alt="Leer" /></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"><img src="pix/x_11x11.png" alt="Leer" /></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"><img src="pix/folder_fill_12x12.png" alt="ePortfolio" /></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"><img src="pix/x_11x11.png" alt="Leer" /></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"><img src="pix/x_11x11.png" alt="Leer" /></td>
		</tr>
		<tr class="exabis_comp_teilcomp">
			<td>A03</td>
			<td><b><img src="pix/pfeil_hide.png" class="exabis_comp_teilcomp_pfeil" />&nbsp;<a href="#">Ich kann Raum- und Hohlmaße in benachbarte Einheiten umrechnen</a></b></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"><img src="pix/x_11x11.png" alt="Leer" /></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"><img src="pix/x_11x11.png" alt="Leer" /></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"><img src="pix/x_11x11.png" alt="Leer" /></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"><img src="pix/x_11x11.png" alt="Leer" /></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"><img src="pix/x_11x11.png" alt="Leer" /></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"><img src="pix/x_11x11.png" alt="Leer" /></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"><img src="pix/x_11x11.png" alt="Leer" /></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"><img src="pix/x_11x11.png" alt="Leer" /></td>
		</tr>
	
		<tr class="exabis_comp_aufgabe">
			<td></td>
			<td>LM1.1</td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"></td>
		</tr>
		<tr class="exabis_comp_aufgabe">
			<td></td>
			<td>LM1.2</td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"></td>
		</tr>
		<tr  class="exabis_comp_aufgabe">
			<td></td>
			<td>LM1.2</td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"></td>
		</tr>
		<tr  class="exabis_comp_aufgabe">
			<td></td>
			<td>LM1.3</td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"></td>
		</tr>
		<tr  class="exabis_comp_aufgabe">
			<td></td>
			<td>LM1.4</td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"></td>
		</tr>
		
		<tr class="exabis_comp_teilcomp">
			<td>A04</td>
			<td><b><img src="pix/pfeil_show.png" class="exabis_comp_teilcomp_pfeil" />&nbsp;<a href="#">Ich kann den Oberflächeninhalt von Quadern ermitteln</a></b></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"><img src="pix/x_11x11.png" alt="Leer" /></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"><img src="pix/x_11x11.png" alt="Leer" /></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"><img src="pix/x_11x11.png" alt="Leer" /></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"><img src="pix/x_11x11.png" alt="Leer" /></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"><img src="pix/x_11x11.png" alt="Leer" /></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"><img src="pix/x_11x11.png" alt="Leer" /></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"><img src="pix/x_11x11.png" alt="Leer" /></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"><img src="pix/x_11x11.png" alt="Leer" /></td>
		</tr>
		<tr class="exabis_comp_teilcomp">
			<td>A05</td>
			<td><b><img src="pix/pfeil_show.png" class="exabis_comp_teilcomp_pfeil" />&nbsp;<a href="#">Ich kann den Oberflächeninhalt von realen quaderförmigen Gegenständen durch Messen und Berechnen ermitteln</a></b></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"><img src="pix/x_11x11.png" alt="Leer" /></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"><img src="pix/x_11x11.png" alt="Leer" /></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"><img src="pix/x_11x11.png" alt="Leer" /></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"><img src="pix/x_11x11.png" alt="Leer" /></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"><img src="pix/x_11x11.png" alt="Leer" /></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"><img src="pix/x_11x11.png" alt="Leer" /></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"><img src="pix/x_11x11.png" alt="Leer" /></td>
			<td><input type="checkbox" value="1" name="data[33][3679]" checked="checked"><img src="pix/x_11x11.png" alt="Leer" /></td>
		</tr>

	</thead>
</table>
</form>
	<?php
} else {
	$content.=$OUTPUT->box(text_to_html(get_string("explainno_comps", "block_exacomp")));
}

echo $OUTPUT->footer();
exit;

/*
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

//$content.=block_exacomp_create_radargraph();
*

$zeile = "";

$context = get_context_instance(CONTEXT_COURSE, $courseid);
if ($role == "teacher"){
	// spalte mit schuelern(=teilnehmer), 5=teilnehmer
	$students = get_role_users(5, $context);
}else{
	//spalte nur teilnehmer selber
	$students = array($USER);
}
*
if ($showevaluation == 'on')
	$colspan = 2;
else
	$colspan=1;
*/
if ($descriptors) {

	if ($showevaluation == 'on') {
		$content.='<tr><td></td>';
		$z=1;
		$p=1;
		for ($i = 0; $i < count($students); $i++){
			if($role=="teacher")
				$content.='<td class="zelle'.$p.'" >'.get_string("schueler_short", "block_exacomp").'</td><td class="zelle'.$p.'">'.get_string("lehrer_short", "block_exacomp").'</td>';
			else
				$content.='<td class="zelle'.$p.'" >'.get_string("lehrer_short", "block_exacomp").'</td><td class="zelle'.$p.'">'.get_string("schueler_short", "block_exacomp").'</td>';
			if ($z==$schueler_gruppierung_breite){
				$z=1;$p++;
			}
			else $z++;
		}
		$content.='</tr>';
	}

	$trclass = "even";
	$zeile=1;
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
		$z=1;
		$p=1;
		foreach ($students as $student) {

			if ($bewertungsdimensionen==1){
				if ($showevaluation == "on"){
					$tempzeile.='<td class="zelle'.$p.'" onmouseover="Tip(\'###evalteacher' . $descriptor->id . '_' . $student->id . '###\')" onmouseout="UnTip()" class="ec_td_mo"><input type="checkbox" value="1" name="evaluation[' . $descriptor->id . '][' . $student->id . ']" checked="###checkedevaluation' . $descriptor->id . '_' . $student->id . '###" disabled="disabled" /></td>';
				}
				$tempzeile.='<td class="zelle'.$p.'" class="ec_td_mo"><input type="checkbox" value="1" name="data[' . $descriptor->id . '][' . $student->id . ']" checked="###checked' . $descriptor->id . '_' . $student->id . '###" /></td>';
			}else {
				if ($showevaluation == "on"){
					$tempzeile.='<td class="zelle'.$p.'" onmouseover="Tip(\'###evalteacher' . $descriptor->id . '_' . $student->id . '###\')" onmouseout="UnTip()" class="ec_td_mo">';
					$tempzeile.='###checkedevaluation' . $descriptor->id . '_' . $student->id . '###</td>';
				}
				$tempzeile.='<td class="zelle'.$p.'" class="ec_td_mo"><select name="data[' . $descriptor->id . '][' . $student->id . ']">';
				for ($i=0;$i<=$bewertungsdimensionen;$i++){
					$tempzeile.='<option value="'.$i.'" selected="###selected' . $descriptor->id . '_' . $student->id . '_'.$i.'###">'.$i.'</option>';
				}
				$tempzeile.='</select></td>';
			}
			if ($z==$schueler_gruppierung_breite){
				$z=1;$p++;
			}
			else $z++;
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
		$z=1;
		$p=1;
		foreach ($students as $student) {
			$stdicon = block_exacomp_get_student_icon($activities, $student,$courseid,$gradelib);
			
			$tempzeile .= '<td class="zelle'.$p.'"  colspan="' . $colspan . '"><a onmouseover="Tip(\'' . $stdicon->text . '\')" onmouseout="UnTip()">' . $stdicon->icon . '</a>';
			//gibt es zugeordnete artefakte in exabis_eportfolio
			if (block_exacomp_exaportexists()){
				

				$stdicon = block_exacomp_get_portfolio_icon($student, $descriptor->id);
				
				if(isset($stdicon)) {
					
					if($role=="student")
						$url = 'href="'.$CFG->wwwroot.'/blocks/exaport/view_items.php?courseid='.$courseid.'"';
					else
						$url = 'href="'.$CFG->wwwroot.'/blocks/exaport/shared_views.php?courseid='.$courseid.'&desc='.$descriptor->id.'&u='.$student->id.'"';
					$tempzeile .= '<a '.$url.' onmouseover="Tip(\'' . $stdicon->text . '\')" onmouseout="UnTip()">' . $stdicon->icon . '</a>';
				}
			}
			$tempzeile .= '</td>';
			if ($z==$schueler_gruppierung_breite){
				$z=1;$p++;
			}
			else $z++;
		}

		$tempzeile.='</tr>';

		$content .= $tempzeile;
		if ($zeile==$zeilenanzahl && $role == "teacher"){
			if ($trclass == "even") {
				$trclass = "odd";
				$bgcolor = ' style="background-color:#efefef" ';
				$fontcolor = ' style="color:#6c6c6c" ';
			} else {
				$trclass = "even";
				$bgcolor = ' style="background-color:#ffffff" ';
				$fontcolor = ' style="color:#6c6c6c" ';
			}
			$content.='<tr class="'.$trclass.'" ' . $bgcolor . '><td></td>';
			$zi=1;
			$pi=1;
			foreach ($students as $student) {
				$content.='<td'.$fontcolor.' class="zelle'.$pi.'" colspan="' . $colspan . '">'.$student->lastname.'</td>';
				if ($zi==$schueler_gruppierung_breite){
					$zi=1;$pi++;
				}
				else $zi++;
			}
			$content.='</tr>';
			$zeile=0;
		}
		$tempzeile = "";
		$zeile++;
	}
	$content.='<tr><td id="tdsubmit" colspan="'.(count($students) * $colspan + 1).'"><input name="btn_submit" type="submit" value="' . get_string('auswahl_speichern', 'block_exacomp') . '" /></td></tr>';
	$content.="</table></div>";

	$content.='</form>';
} else {
	$content.=$OUTPUT->box(text_to_html(get_string("explainno_comps", "block_exacomp")));
}

echo $content;
echo "</div>";
echo '</div>'; //exabis_competences_block
$content='
<script type="text/javascript">

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