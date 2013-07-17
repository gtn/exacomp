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
$subjectid = optional_param('subjectid', isset($SESSION->block_exacomp_last_subjectid) ? (int)$SESSION->block_exacomp_last_subjectid : 0, PARAM_INT);
$topicid = optional_param('topicid', isset($SESSION->block_exacomp_last_topicid) ? (int)$SESSION->block_exacomp_last_topicid : 0, PARAM_INT);
 
$showevaluation = optional_param('showevaluation', "", PARAM_ALPHA);
$bewertungsdimensionen=block_exacomp_getbewertungsschema($courseid);

$context = get_context_instance(CONTEXT_COURSE, $courseid);

$courseSettings = block_exacomp_coursesettings();

//eigen berechtigung für exabis_competences, weil die moodle rollen nicht genau passen, zb
//bei moodle dürfen übergeordnete rollen alles der untergeordneten, dass soll hier nicht sein

if (has_capability('block/exacomp:teacher', $context)) {
	$introle = 1;
	$role = "teacher";
} elseif (has_capability('block/exacomp:student', $context)) {
	$introle = 0;
	$role = "student";
}

$url = '/blocks/exacomp/assign_competencies.php?courseid=' . $courseid;
$PAGE->set_url($url);
$url = $CFG->wwwroot . $url;
$identifier = "teachertabassigncompetences";
if ($role == "student")
	$identifier = "studenttabcompetences";

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

	function block_exacomp_set_dataexamples($courseid, $role) {
		global $DB, $USER;
		
		if (!empty($_POST['dataexamples'])){
			$oldData = $DB->get_records_sql("
				SELECT CONCAT(deu.descrexamp_mm_id,'_',deu.studentid) AS tmp, id
				FROM {block_exacompexameval} deu
				WHERE deu.courseid=?
			", array($courseid));

			foreach ($_POST['dataexamples'] as $deid => $students) {
				foreach ($students as $studentid => $values) {
				
					$updateEvaluation = new stdClass;
					if ($role == "teacher") {
						$updateEvaluation->teacher_evaluation = $values['teacher_evaluation'];
						$updateEvaluation->teacher_reviewerid = $USER->id;
					} else {
						if ($studentid != $USER->id)
							// student can only assess himself
							continue;
						
						if(strcmp($values['starttime'], "")!=0){
							$date = explode("/", $values['starttime']);
							$date = new DateTime($date[2].'-'.$date[0].'-'.$date[1]);
							$starttime = $date->getTimestamp();
						}else{
							$starttime = NULL;
						}
						
						if(strcmp($values['endtime'], "")!=0){
							$date = explode("/", $values['endtime']);
							$date = new DateTime($date[2].'-'.$date[0].'-'.$date[1]);
							$endtime = $date->getTimestamp();
						}else{
							$endtime = NULL;
						}
						
						$updateEvaluation->student_evaluation = $values['student_evaluation'];
						$updateEvaluation->starttime = $starttime;
						$updateEvaluation->endtime = $endtime;
						$updateEvaluation->studypartner = $values['studypartner'];
					}
					if (isset($oldData[$deid.'_'.$studentid])) {
						$updateEvaluation->id = $oldData[$deid.'_'.$studentid]->id;

						$DB->update_record('block_exacompexameval', $updateEvaluation);
					} else {
						$updateEvaluation->courseid = $courseid;
						$updateEvaluation->descrexamp_mm_id = $deid;
						$updateEvaluation->studentid = $studentid;

						$DB->insert_record('block_exacompexameval', $updateEvaluation);
					}
				}
			}
		}
	}
	block_exacomp_set_dataexamples($courseid, $role);
}

$context = get_context_instance(CONTEXT_COURSE, $courseid);
if ($role == "teacher"){
	// spalte mit schuelern(=teilnehmer), 5=teilnehmer
	$students = get_role_users(5, $context);
	// $students = array_merge($students, $students, $students);
	// $students = array_merge($students, $students, $students);
}else{
	//spalte nur teilnehmer selber
	$students = array($USER);
}

// read all subjects in this course
$topics = null;
$selected_subject = null;
$selected_topic = null;
$descriptors = null;

$subjects = $DB->get_records_sql('
	SELECT s.id, s.title, s.stid, s.number
	FROM {block_exacompsubjects} s
	JOIN {block_exacomptopics} t ON t.subjid = s.id
	JOIN {block_exacompcoutopi_mm} ct ON ct.topicid = t.id AND ct.courseid = ?
	'.($courseSettings->show_all_descriptors ? '' : '
		-- only show active ones
		JOIN {block_exacompdescrtopic_mm} topmm ON topmm.topicid=t.id
		JOIN {block_exacompdescriptors} d ON topmm.descrid=d.id
		JOIN {block_exacompdescractiv_mm} da ON d.id=da.descrid
		JOIN {course_modules} a ON da.activityid=a.id AND a.course=ct.courseid
	').'
	GROUP BY s.id
	ORDER BY s.title
', array($courseid));

if (isset($subjects[$subjectid])) {
	$selected_subject = $subjects[$subjectid];
} elseif ($subjects) {
	$selected_subject = reset($subjects);
}

if ($selected_subject) {
	$SESSION->block_exacomp_last_subjectid = $selected_subject->id;

	$topics = $DB->get_records_sql('
		SELECT t.id, t.title, t.cat, t.ataxonomie, t.btaxonomie, t.ctaxonomie
		FROM {block_exacomptopics} t
		JOIN {block_exacompcoutopi_mm} ct ON ct.topicid = t.id AND t.subjid = ? AND ct.courseid = ?
		'.($courseSettings->show_all_descriptors ? '' : '
			-- only show active ones
			JOIN {block_exacompdescrtopic_mm} topmm ON topmm.topicid=t.id
			JOIN {block_exacompdescriptors} d ON topmm.descrid=d.id
			JOIN {block_exacompdescractiv_mm} da ON d.id=da.descrid
			JOIN {course_modules} a ON da.activityid=a.id AND a.course=ct.courseid
		').'
		GROUP BY t.id
		ORDER BY t.title
	', array($selected_subject->id, $courseid));
	
	if (isset($topics[$topicid])) {
		$selected_topic = $topics[$topicid];
	} elseif ($topics) {
		$selected_topic = reset($topics);
	}
	
	if ($selected_topic) {
		$SESSION->block_exacomp_last_topicid = $selected_topic->id;
		
		$descriptors = $DB->get_records_sql('
			SELECT d.id, d.title
			FROM {block_exacompdescriptors} d
			JOIN {block_exacompdescrtopic_mm} topmm ON topmm.descrid=d.id AND topmm.topicid=?
			'.($courseSettings->show_all_descriptors ? '' : '
				-- only show active ones
				JOIN {block_exacompdescractiv_mm} da ON d.id=da.descrid
				JOIN {course_modules} a ON da.activityid=a.id AND a.course='.$courseid.'
			').'
			GROUP BY d.id
			ORDER BY d.sorting
		', array($selected_topic->id));
	}
}

if ($showevaluation == 'on')
	$studentColspan = 2;
else
	$studentColspan = 1;

?>
<div class="exabis_competencies_lis">

<div class="exabis_comp_select">
Fach auswählen:
<select class="start-searchbox-select" onchange="document.location.href='<?php echo $url; ?>&subjectid='+this.value;">
<?php foreach ($subjects as $subject) {
	echo '<option value="'.$subject->id.'"'.($subject->id==$selected_subject->id?' selected="selected"':'').'>'.$subject->title.'</option>';
} ?>
</select>

<?php if ($topics): ?>
Kompetenzbereich/Leitidee auswählen:
<select class="start-searchbox-select" onchange="document.location.href='<?php echo $url; ?>&subjectid=<?php echo $selected_subject->id; ?>&topicid='+this.value;">
	<?php foreach ($topics as $topic) {
		echo '<option value="'.$topic->id.'"'.($topic->id==$selected_topic->id?' selected="selected"':'').'>'.$topic->title.'</option>';
	} ?>
</select>
<?php endif; ?>
</div>

<?php if ($selected_topic) { 

$url .= '&subjectid='.$selected_subject->id.'&topicid='.$selected_topic->id;

if ($role == "teacher") {
	if ($showevaluation)
		echo $OUTPUT->box(text_to_html(get_string("explainassignoff", "block_exacomp") . '<a href="' . $url . '">'.get_string("hier", "block_exacomp").'.</a>'));
	else
		echo $OUTPUT->box(text_to_html(get_string("explainassignon", "block_exacomp") . '<a href="' . $url . '&amp;showevaluation=on">'.get_string("hier", "block_exacomp").'.</a>'));
}
else {
	if ($showevaluation)
		echo $OUTPUT->box(text_to_html(get_string("explainassignoffstudent", "block_exacomp") . '<a href="' . $url . '">'.get_string("hier", "block_exacomp").'.</a>'));
	else
		echo $OUTPUT->box(text_to_html(get_string("explainassignonstudent", "block_exacomp") . '<a href="' . $url . '&amp;showevaluation=on">'.get_string("hier", "block_exacomp").'.</a>'));
}




if (get_config("exacomp","alternativedatamodel")) {
	?>

	<table class="exabis_comp_info">
		<tr>
			<td><span class="exabis_comp_top_small">Lehrkraft</span>
				<b><?php 
				$context = get_context_instance(CONTEXT_COURSE, $courseid);
				$teachers = get_users_by_capability($context, 'block/exacomp:teacher', 'u.id, u.firstname, u.lastname');
				if ($teachers) {
					echo fullname(reset($teachers));
				}
				?></b>
			</td>
			<td><span class="exabis_comp_top_small">Klasse</span>
				<b><?php echo $COURSE->shortname; ?></b>
			</td>
			<td><span class="exabis_comp_top_small">Schuljahr</span>
				<b>13/14</b>
			</td>
			<td><span class="exabis_comp_top_small">Fach</span>
				<b><?php $schooltype = 	$DB->get_field("block_exacompschooltypes", "title", array("id"=>$selected_subject->stid));
				echo $schooltype;?></b>
			</td>
			<td><span class="exabis_comp_top_small">Kompetenzbereich/Leitidee</span>
				<b><?php echo $selected_subject->number . " - " . $selected_subject->title; ?></b>
			</td>
			<td><span class="exabis_comp_top_small">Lernfortschritt</span>
				<b>LF <?php echo $selected_topic->cat; ?></b>
			</td>
			<td><span class="exabis_comp_top_small">Lernwegliste</span>
				<b><?php 
					echo substr($schooltype, 0,1).$selected_subject->number.".".$selected_topic->cat;				
				?></b>
			</td>
		</tr>
		
	</table>
	<table class="exabis_comp_top">
		<tr>
			<td>
				<span class="exabis_comp_top_small">Teilkompetenz</span>
				<span class="exabis_comp_top_header"><?php echo $selected_topic->title; ?></span>
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
				<div class="exabis_comp_top_indentation"><?php echo $selected_topic->ataxonomie; ?></div>
			</td>
			
		</tr>
		<tr>
			<td class="comp_grey_90">
				<div class="exabis_comp_top_indentation_nr">B:</div>
				<div class="exabis_comp_top_indentation"><?php echo $selected_topic->btaxonomie; ?></div>
			</td>
			
		</tr>
		<tr>
			<td class="comp_grey_83">
				<div class="exabis_comp_top_indentation_nr">C:</div>
				<div class="exabis_comp_top_indentation"><?php echo $selected_topic->ctaxonomie; ?></div>
			</td>
			
		</tr>
	</table>
	<br />
		<?php
}
?>
<div class="exabis_comp_top_legend">
<img src="pix/list_12x11.png" alt="Aktivitäten" /> Aktivitäten - <img src="pix/folder_fill_12x12.png" alt="ePortfolio" /> ePortfolio - <img src="pix/x_11x11.png" alt="Leer" /> noch keine Aufgaben zu diesem Deskriptor abgegeben und keinen Test durchgeführt
</div>

<?php
if ($descriptors) {
	?>
<div class="ec_td_mo_auto"><?php
	echo spaltenbrowser(count($students),$schueler_gruppierung_breite);
?>

<form action="assign_competencies.php?action=save&courseid=<?php echo $courseid; ?>&subjectid=<?php  echo $selected_subject->id; ?>&topicid=<?php echo $selected_topic->id; ?>" method="post">
<table class="exabis_comp_comp">
	<?php
		$rowgroup = 0;
        foreach ($descriptors as $descriptor) {
			$rowgroup++;
			
			if (($rowgroup % 6) == 1) {
				?>
				<tr>
					<td colspan="2"><b>Teilkompetenzen und Lernmaterialien</b></td>
					<?php
						$columnCnt = 0;
						foreach ($students as $student) {
							echo '<td class="exabis_comp_top_studentcol colgroup colgroup-'.floor($columnCnt++/$schueler_gruppierung_breite).'" colspan="' . $studentColspan . '">'.fullname($student).'</td>';
						}
					?>
				</tr>
				<?php

				if ($showevaluation) {
					echo '<tr><td colspan="2"></td>';
					for ($i = 0; $i < count($students); $i++){
						$extra = ' class="colgroup colgroup-'.floor($i/$schueler_gruppierung_breite).'"';
						if($role=="teacher")
							echo '<td'.$extra.'>'.get_string("schueler_short", "block_exacomp").'</td><td'.$extra.'>'.get_string("lehrer_short", "block_exacomp").'</td>';
						else
							echo '<td'.$extra.'>'.get_string("lehrer_short", "block_exacomp").'</td><td'.$extra.'>'.get_string("schueler_short", "block_exacomp").'</td>';
					}
					echo '</tr>';
				}
			}
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

			$examples = $DB->get_records_sql(
				"SELECT de.id as deid, e.id, e.title, tax.title as tax, e.task, e.externalurl,
				e.externalsolution, e.externaltask, e.solution, e.completefile, e.description, e.taxid, e.attachement
				FROM {block_exacompexamples} e
				JOIN {block_exacompdescrexamp_mm} de ON e.id=de.exampid AND de.descrid=?
				LEFT JOIN {block_exacomptaxonomies} tax ON e.taxid=tax.id
				ORDER BY tax.title", array($descriptor->id));

			$competences = block_exacomp_get_competences_by_descriptor($descriptor->id, $courseid, $introle);
			if ($showevaluation) {
				$evaluations = block_exacomp_get_competences_by_descriptor($descriptor->id, $courseid, ($introle + 1) % 2);
			}

			if (preg_match('!^([^\s]*[0-9][^\s]*+)\s+(.*)$!iu', $descriptor->title, $matches)) {
				$output_id = $matches[1];
				$output_title = $matches[2];
			} else {
				$output_id = '';
				$output_title = $descriptor->title;
			}
			?>
			<tr class="exabis_comp_teilcomp <?php if ($examples): ?>rowgroup-header rowgroup-<?php echo $rowgroup; ?><?php endif; ?>">
			<td><?php echo $output_id; ?></td>
			<td class="rowgroup-arrow"><div><?php echo $output_title; ?></div></td>
			<?php
				$columnCnt = 0;
				$activities = block_exacomp_get_activities($descriptor->id, $courseid);
				foreach ($students as $student) {
					
					if ($showevaluation) {
						if (isset($evaluations[$student->id])&&$evaluations[$student->id]->wert) {
							$evaluation = $evaluations[$student->id];
							if ($evaluation->wert) {
								echo '<td class="exabis-tooltip colgroup colgroup-'.floor($columnCnt++/$schueler_gruppierung_breite).'" title="'.s(get_string('assessedby','block_exacomp').fullname($evaluation)).'">';
								if ($bewertungsdimensionen==1) {
									echo '<input type="checkbox" disabled="disabled" '.($evaluation->wert?' checked="checked"':'').' />';
								} else {
									echo '<select disabled="disabled"><option>'.$evaluation->wert.'</option></select>';
								}
							} else {
								echo '<td class="colgroup colgroup-'.floor($columnCnt++/$schueler_gruppierung_breite).'">';
							}
							echo '</td>';
						} else {
							echo '<td class="colgroup colgroup-'.floor($columnCnt++/$schueler_gruppierung_breite).'"></td>';
						}
					}

					echo '<td class="colgroup colgroup-'.floor($columnCnt++/$schueler_gruppierung_breite).'">';

					if ($bewertungsdimensionen==1) {
						echo '<input type="checkbox" value="1" name="data[' . $descriptor->id . '][' . $student->id . ']"'.(isset($competences[$student->id])&&$competences[$student->id]->wert?' checked="checked"':'').' />';
					} else {
						echo '<select name="data[' . $descriptor->id . '][' . $student->id . ']">';
						for ($i=0; $i<=$bewertungsdimensionen; $i++) {
							echo '<option value="'.$i.'"'.(isset($competences[$student->id])&&$competences[$student->id]->wert==$i?' selected="selected"':'').'>'.$i.'</option>';
						}
						echo '</select>';
					}

					$hasIcons = false;
					if ($stdicon = block_exacomp_get_student_icon($activities, $student,$courseid,$gradelib)) {
						echo '<span title="'.s($stdicon->text).'" class="exabis-tooltip">' . $stdicon->icon . '</span>';
						$hasIcons = true;
					}
					if (block_exacomp_exaportexists()) {
						if ($stdicon = block_exacomp_get_portfolio_icon($student, $descriptor->id)) {
							if($role=="student")
								$url = 'href="'.$CFG->wwwroot.'/blocks/exaport/view_items.php?courseid='.$courseid.'"';
							elseif ($stdicon->submitted) {
								$url = 'href="'.$CFG->wwwroot.'/blocks/exaport/shared_views.php?courseid='.$courseid.'&desc='.$descriptor->id.'&u='.$student->id.'"';
							} else {
								$url = '';
							}
							
							if ($url) {
								echo '<a '.$url.' title="'.s($stdicon->text).'" class="exabis-tooltip">' . $stdicon->icon . '</a>';
							} else {
								echo '<span title="'.s($stdicon->text).'" class="exabis-tooltip">' . $stdicon->icon . '</span>';
							}
							$hasIcons = true;
						}
					}
					
					if (!$hasIcons) {
						echo '<span title="'.s('todo').'" class="exabis-tooltip"><img src="pix/x_11x11.png" /></span>';
					}
					
					// echo '<img src="pix/folder_fill_12x12.png" alt="ePortfolio" title="asdf" class="exabis-tooltip" /><img src="pix/list_12x11.png" alt="Aktivitäten" />';
					echo '</td>';
				}
			?>
			</tr>
			<?php foreach ($examples as $example) {

				$examplesEvaluationData = $DB->get_records_sql("
					SELECT deu.studentid, u.firstname, u.lastname, deu.*
					FROM {block_exacompexameval} deu
					LEFT JOIN {user} u ON u.id=deu.".($role == "teacher"?'studentid':'teacher_reviewerid')."
					WHERE deu.courseid=? AND deu.descrexamp_mm_id=?
				", array($courseid, $example->deid));
			
				?>
			<tr class="exabis_comp_aufgabe rowgroup-content rowgroup-<?php echo $rowgroup; ?>"">
				<td></td>
				<td><?php echo $example->title; ?></td>
				<?php
					$columnCnt = 0;
					foreach ($students as $student) {

						if ($showevaluation) {
							$evaluationWert = null;
							$evaluationTooltip = null;
							if (isset($examplesEvaluationData[$student->id])) {
								$evaluation = $examplesEvaluationData[$student->id];
								if ($role == "teacher") {
									$evaluationWert = $evaluation->student_evaluation;
									$evaluationTooltip = $evaluation->starttime ? $evaluation->starttime.' - '.$evaluation->endtime : '';
								} else {
									$evaluationWert = $evaluation->teacher_evaluation;
									$evaluationTooltip = get_string('assessedby','block_exacomp').fullname($evaluation);
								}
							}
							
							if ($evaluationWert) {
								echo '<td class="exabis-tooltip colgroup colgroup-'.floor($columnCnt++/$schueler_gruppierung_breite).'" title="'.s($evaluationTooltip).'">';
								
								if ($bewertungsdimensionen==1) {
									echo '<input type="checkbox" disabled="disabled" '.($evaluationWert?' checked="checked"':'').' />';
								} else {
									echo '<select disabled="disabled"><option>'.$evaluationWert.'</option></select>';
								}
							} else {
								echo '<td class="colgroup colgroup-'.floor($columnCnt++/$schueler_gruppierung_breite).'">';
							}
							echo '</td>';
						}

						echo '<td class="colgroup colgroup-'.floor($columnCnt++/$schueler_gruppierung_breite).'">';

						if ($role == "teacher") {
							if ($bewertungsdimensionen==1) {
								echo '<input type="hidden" value="0" name="dataexamples[' . $example->deid . '][' . $student->id . '][teacher_evaluation]" />';
								echo '<input type="checkbox" value="1" name="dataexamples[' . $example->deid . '][' . $student->id . '][teacher_evaluation]"'.
									(isset($examplesEvaluationData[$student->id])&&$examplesEvaluationData[$student->id]->teacher_evaluation?' checked="checked"':'').' />';
							} else {
								echo '<select name="dataexamples[' . $example->deid . '][' . $student->id . '][teacher_evaluation]">';
								for ($i=0; $i<=$bewertungsdimensionen; $i++) {
									echo '<option value="'.$i.'"'.(isset($examplesEvaluationData[$student->id])&&$examplesEvaluationData[$student->id]->teacher_evaluation==$i?' selected="selected"':'').'>'.$i.'</option>';
								}
								echo '</select>';
							}
						} else {
							echo 'Aufgabe erledigt: ';
							if ($bewertungsdimensionen==1) {
								echo '<input type="hidden" value="0" name="dataexamples[' . $example->deid . '][' . $student->id . '][student_evaluation]" />';
								echo '<input type="checkbox" value="1" name="dataexamples[' . $example->deid . '][' . $student->id . '][student_evaluation]"'.
									(isset($examplesEvaluationData[$student->id])&&$examplesEvaluationData[$student->id]->student_evaluation?' checked="checked"':'').' />';
							} else {
								echo '<select name="dataexamples[' . $example->deid . '][' . $student->id . '][student_evaluation]">';
								for ($i=0; $i<=$bewertungsdimensionen; $i++) {
									echo '<option value="'.$i.'"'.(isset($examplesEvaluationData[$student->id])&&$examplesEvaluationData[$student->id]->student_evaluation==$i?' selected="selected"':'').'>'.$i.'</option>';
								}
								echo '</select>';
							}
							
							$studypartner = isset($examplesEvaluationData[$student->id]) ? $examplesEvaluationData[$student->id]->studypartner : '';

							echo ' <select name="dataexamples[' . $example->deid . '][' . $student->id . '][studypartner]">
								<option value="self"'.($studypartner=='self'?' selected="selected"':'').'>selbst</option>
								<option value="studypartner"'.($studypartner=='studypartner'?' selected="selected"':'').'>Lernpartner</option>
								<option value="studygroup"'.($studypartner=='studygroup'?' selected="selected"':'').'>Lerngruppe</option>
								<option value="teacher"'.($studypartner=='teacher'?' selected="selected"':'').'>Lehrkraft</option>
								</select><br/>
								von <input class="datepicker" type="text" name="dataexamples[' . $example->deid . '][' . $student->id . '][starttime]" value="'.
									(isset($examplesEvaluationData[$student->id]->starttime)?date("m/d/Y",$examplesEvaluationData[$student->id]->starttime):'').'" readonly/>
								bis <input class="datepicker" type="text" name="dataexamples[' . $example->deid . '][' . $student->id . '][endtime]" value="'.
									(isset($examplesEvaluationData[$student->id]->endtime)?date("m/d/Y",$examplesEvaluationData[$student->id]->endtime):'').'" readonly/>
							';
						}
						echo '</td>';
					}
				?>
			</tr>
			<?php }
		}
	?>
	</thead>
</table>
<input name="btn_submit" type="submit" value="<?php echo get_string('auswahl_speichern', 'block_exacomp'); ?>" />
</form>

	<?php

} else {
	echo $OUTPUT->box(text_to_html(get_string("explainno_comps", "block_exacomp")));
}
}

echo $OUTPUT->footer();
