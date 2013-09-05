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

					if(is_array($wert)) {
						if(isset($wert['teacher_evaluation']))
							$wert = $wert['teacher_evaluation'];
						else
							$wert = $wert['student_evaluation'];
					}
					if ($wert>0){//wenn pulldown und wert 0, kein eintrag, wenn checkbox kein hackerl kommt er gar nicht hierhier
						$values[] =  array('user' => $key2, 'desc' => $key, 'wert' => $wert);
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
					SELECT CONCAT(deu.exampleid,'_',deu.studentid) AS tmp, id
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

						if (!empty($values['starttime'])) {
							$date = new DateTime($values['starttime']);
							$starttime = $date->getTimestamp();
						}else{
							$starttime = null;
						}

						if (!empty($values['endtime'])) {
							$date = new DateTime($values['endtime']);
							$endtime = $date->getTimestamp();
						}else{
							$endtime = null;
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
						$updateEvaluation->exampleid = $deid;
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
		ORDER BY s.stid, s.title
		', array($courseid));

if (isset($subjects[$subjectid])) {
	$selected_subject = $subjects[$subjectid];
} elseif ($subjects) {
	$selected_subject = reset($subjects);
}

$version = get_config('exacomp', 'alternativedatamodel');

if ($selected_subject && $version) {
	$SESSION->block_exacomp_last_subjectid = $selected_subject->id;

	$topics = $DB->get_records_sql('
			SELECT t.id, t.title, t.cat, t.ataxonomie, t.btaxonomie, t.ctaxonomie, t.requirement, t.benefit, t.knowledgecheck
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
			ORDER BY t.cat, t.title
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
else {
	$descriptors = $DB->get_records_sql('
			SELECT t.id, t.title, s.title as subject
			FROM {block_exacomptopics} t
			JOIN {block_exacompcoutopi_mm} topmm ON topmm.topicid=t.id AND topmm.courseid=?
			JOIN {block_exacompsubjects} s ON t.subjid = s.id
			GROUP BY t.id
			ORDER BY s.id, t.sorting
			', array($courseid));
}
if ($showevaluation == 'on')
	$studentColspan = 2;
else
	$studentColspan = 1;

?>
<div class="exabis_competencies_lis">

	<?php
	if($version) {
		?>

	<div class="exabis_comp_select">
		Fach auswählen: <select class="start-searchbox-select"
			onchange="document.location.href='<?php echo $url; ?>&subjectid='+this.value;">
			<?php foreach ($subjects as $subject) {
				echo '<option value="'.$subject->id.'"'.($subject->id==$selected_subject->id?' selected="selected"':'').'>'.$subject->title.'</option>';
} ?>
		</select>

		<?php if ($topics): ?>
		Kompetenzbereich/Leitidee auswählen: <select
			class="start-searchbox-select"
			onchange="document.location.href='<?php echo $url; ?>&subjectid=<?php echo $selected_subject->id; ?>&topicid='+this.value;">
			<?php foreach ($topics as $topic) {
				echo '<option value="'.$topic->id.'"'.($topic->id==$selected_topic->id?' selected="selected"':'').'>'.$topic->title.'</option>';
	} ?>
		</select>
		<?php endif; ?>
	</div>

	<?php }
	if ($selected_topic || !$version) {

		$url .= '&subjectid='.$subjectid.'&topicid='.$topicid;

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




		if ($version) {
			?>

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
	<?php
		}
		?>
	<div class="exabis_comp_top_legend">
		<img src="pix/list_12x11.png" alt="Aktivitäten" /> Aktivitäten - <img
			src="pix/folder_fill_12x12.png" alt="ePortfolio" /> ePortfolio - <img
			src="pix/x_11x11.png" alt="Leer" /> noch keine Aufgaben zu diesem
		Deskriptor abgegeben und keinen Test durchgeführt
	</div>

	<?php
	if ($descriptors) {
		?>
	<div class="ec_td_mo_auto">
		<?php
		echo spaltenbrowser(count($students),$schueler_gruppierung_breite);
		?>

		<form id="assign-competencies"
			action="assign_competencies.php?action=save&courseid=<?php echo $courseid; ?>&subjectid=<?php  echo $selected_subject->id; ?>&topicid=<?php echo $selected_topic->id; ?>"
			method="post">
			<input type="hidden" name="open_row_groups"
				value="<?php echo p(optional_param('open_row_groups', "", PARAM_TEXT)); ?>" />
			<table class="exabis_comp_comp">
				<?php
				$rowgroup = 0;
				$subjectTitle = ($version) ? "Teilkompetenzen und Lernmaterialien" : "";
				foreach ($descriptors as $descriptor) {
					$rowgroup++;

					if ((($rowgroup % 6) == 1 && $version) || (isset($descriptor->subject) && $descriptor->subject != $subjectTitle && !$version)) {
						if(!$version)
							$subjectTitle = $descriptor->subject;
						?>
				<tr>
					<td colspan="2"><b><?php echo $subjectTitle; ?> </b></td>
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

					// in the alternative data model we use examples on this level, in the normal case we use descriptors
					if($version) {
						$examples = $DB->get_records_sql(
								"SELECT de.id as deid, e.id, e.title, tax.title as tax, e.task, e.externalurl,
								e.externalsolution, e.externaltask, e.solution, e.completefile, e.description, e.taxid, e.attachement
								FROM {block_exacompexamples} e
								JOIN {block_exacompdescrexamp_mm} de ON e.id=de.exampid AND de.descrid=?
								LEFT JOIN {block_exacomptaxonomies} tax ON e.taxid=tax.id
								ORDER BY tax.title", array($descriptor->id));
					} else {
						$examples = $DB->get_records_sql('
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
								', array($descriptor->id));
					}

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
					$activities = block_exacomp_get_activities($descriptor->id, $courseid);
					?>
				<tr
					class="exabis_comp_teilcomp <?php if ($examples): ?>rowgroup-header rowgroup-<?php echo $rowgroup; ?><?php endif; ?>">
					<td><?php echo $output_id; ?></td>
					<td class="rowgroup-arrow"><div
							class="desctitle<?php if(count($activities)==0 && $courseSettings->uses_activities == 1 && $version) echo "grey";?>">
							<?php echo $output_title; ?>
						</div></td>
					<?php
					$columnCnt = 0;
					foreach ($students as $student) {

						if(!$version) {
							echo "<td colspan='".$studentColspan."'></td>";
							continue;
						}

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

					if($version) {
						$examplesEvaluationData = $DB->get_records_sql("
								SELECT deu.studentid, u.firstname, u.lastname, deu.*
								FROM {block_exacompexameval} deu
								LEFT JOIN {user} u ON u.id=deu.".($role == "teacher"?'studentid':'teacher_reviewerid')."
								WHERE deu.courseid=? AND deu.exampleid=?
								", array($courseid, $example->id));
					}
					else {
						$examplesEvaluationData = $DB->get_records_sql("
								SELECT deu.userid, u.firstname, u.lastname, deu.*, deu.wert as teacher_evaluation
								FROM {block_exacompdescuser} deu
								LEFT JOIN {user} u ON u.id=deu.userid
								WHERE deu.courseid=? AND deu.descid=? AND deu.role = 1
								", array($courseid, $example->id));

						foreach($examplesEvaluationData as $exaeval) {
							$exaeval->student_evaluation = $DB->get_field('block_exacompdescuser', 'wert', array("userid"=>$exaeval->userid,"descid"=>$exaeval->descid,"role"=>0,"courseid"=>$exaeval->courseid));
						}

						$activities = block_exacomp_get_activities($example->id, $courseid);

					}
					?>
				<tr
					class="exabis_comp_aufgabe rowgroup-content rowgroup-<?php echo $rowgroup; ?>"">
					<td></td>
					<td>
						<p class="aufgabetext">
							<?php echo $example->title; ?>
						</p> <?php 
						if($role == "student" && $version) {
							$img = '<img src="pix/examples_and_tasks.png" height="16" width="23" alt="Example" />';
							if($example->task)
								echo "<a target='_blank' href='".$example->task."'>".$img."</a>";
							if($example->externalurl)
								echo "<a target='_blank' href='".$example->externalurl."'>".$img."</a>";
						}
						?>
					</td>
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
									$evaluationTooltip = isset($evaluation->starttime) ? $evaluation->starttime.' - '.$evaluation->endtime : '';
								} else {
									$evaluationWert = $evaluation->teacher_evaluation;
									if($version)
										$evaluationTooltip = get_string('assessedby','block_exacomp').fullname($evaluation);
									else
										$evaluationTooltip = get_string('assessedby','block_exacomp').fullname($DB->get_record('user',array('id'=>$evaluation->reviewerid)));
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

						$checkboxname = ($version) ? "dataexamples" : "data";

						if ($role == "teacher") {
							if ($bewertungsdimensionen==1) {
								echo '<input type="hidden" value="0" name="'.$checkboxname.'[' . $example->id . '][' . $student->id . '][teacher_evaluation]" />';
								echo '<input type="checkbox" value="1" name="'.$checkboxname.'[' . $example->id . '][' . $student->id . '][teacher_evaluation]"'.
										(isset($examplesEvaluationData[$student->id])&&$examplesEvaluationData[$student->id]->teacher_evaluation?' checked="checked"':'').' />';
							} else {
								echo '<select name="'.$checkboxname.'[' . $example->id . '][' . $student->id . '][teacher_evaluation]">';
								for ($i=0; $i<=$bewertungsdimensionen; $i++) {
									echo '<option value="'.$i.'"'.(isset($examplesEvaluationData[$student->id])&&$examplesEvaluationData[$student->id]->teacher_evaluation==$i?' selected="selected"':'').'>'.$i.'</option>';
								}
								echo '</select>';
							}
						} else {
							if($version)
								echo 'Aufgabe erledigt: ';
							
							if ($bewertungsdimensionen==1) {
								echo '<input type="hidden" value="0" name="'.$checkboxname.'[' . $example->id . '][' . $student->id . '][student_evaluation]" />';
								echo '<input type="checkbox" value="1" name="'.$checkboxname.'[' . $example->id . '][' . $student->id . '][student_evaluation]"'.
										(isset($examplesEvaluationData[$student->id])&&$examplesEvaluationData[$student->id]->student_evaluation?' checked="checked"':'').' />';
							} else {
								echo '<select name="'.$checkboxname.'[' . $example->id . '][' . $student->id . '][student_evaluation]">';
								for ($i=0; $i<=$bewertungsdimensionen; $i++) {
									echo '<option value="'.$i.'"'.(isset($examplesEvaluationData[$student->id])&&$examplesEvaluationData[$student->id]->student_evaluation==$i?' selected="selected"':'').'>'.$i.'</option>';
								}
								echo '</select>';
							}

							if($version) {
								$studypartner = isset($examplesEvaluationData[$student->id]) ? $examplesEvaluationData[$student->id]->studypartner : '';

								echo ' <select name="dataexamples[' . $example->id . '][' . $student->id . '][studypartner]">
								<option value="self"'.($studypartner=='self'?' selected="selected"':'').'>selbst</option>
								<option value="studypartner"'.($studypartner=='studypartner'?' selected="selected"':'').'>Lernpartner</option>
								<option value="studygroup"'.($studypartner=='studygroup'?' selected="selected"':'').'>Lerngruppe</option>
								<option value="teacher"'.($studypartner=='teacher'?' selected="selected"':'').'>Lehrkraft</option>
								</select><br/>
								von <input class="datepicker" type="text" name="dataexamples[' . $example->id . '][' . $student->id . '][starttime]" value="'.
								(isset($examplesEvaluationData[$student->id]->starttime)?date("Y-m-d",$examplesEvaluationData[$student->id]->starttime):'').'" readonly/>
								bis <input class="datepicker" type="text" name="dataexamples[' . $example->id . '][' . $student->id . '][endtime]" value="'.
								(isset($examplesEvaluationData[$student->id]->endtime)?date("Y-m-d",$examplesEvaluationData[$student->id]->endtime):'').'" readonly/>
								';
							}

						}

						if(!$version) {
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
							echo '</td>';
						}
					}
					?>
				</tr>
				<?php }
				}
				?>
				</thead>
			</table>
			<input name="btn_submit" type="submit"
				value="<?php echo get_string('auswahl_speichern', 'block_exacomp'); ?>" />
		</form>

		<?php

	} /*
	else if(!$version) {
	?>
	<div class="ec_td_mo_auto">
	<?php
	echo spaltenbrowser(count($students),$schueler_gruppierung_breite);
	?>

	<form id="assign-competencies"
	action="assign_competencies.php?action=save&courseid=<?php echo $courseid; ?>&subjectid=<?php  echo $selected_subject->id; ?>&topicid=<?php echo $selected_topic->id; ?>"
	method="post">
	<input type="hidden" name="open_row_groups"
	value="<?php echo p(optional_param('open_row_groups', "", PARAM_TEXT)); ?>" />
	<table class="exabis_comp_comp">
	<?php
	foreach ($subjects as $subject) {
	?>
	<tr>
	<td colspan="2"><b><?php echo $subject->title; ?></b></td>
	<?php
	$columnCnt = 0;
	foreach ($students as $student) {
	echo '<td class="exabis_comp_top_studentcol colgroup colgroup-'.floor($columnCnt++/$schueler_gruppierung_breite).'" colspan="' . $studentColspan . '">'.fullname($student).'</td>';
	}
	?>
	</tr>
	<?php
	}
	?>
	</table>
	</form>
	</div>
	</div>
	</div>
	<?php
	} */
	else {
		echo $OUTPUT->box(text_to_html(get_string("explainno_comps", "block_exacomp")));
	}
	}

	?>
	</div>
	</div>
	<?php 
	echo $OUTPUT->footer();
