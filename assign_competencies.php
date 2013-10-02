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


$courseid = required_param('courseid', PARAM_INT);
require_login($courseid);

$schueler_gruppierung_breite=5;
$zeilenanzahl=5;
$content = "";
$action = optional_param('action', "", PARAM_ALPHA);
$subjectid = optional_param('subjectid', isset($SESSION->block_exacomp_last_subjectid) ? (int)$SESSION->block_exacomp_last_subjectid : 0, PARAM_INT);
$topicid = optional_param('topicid', isset($SESSION->block_exacomp_last_topicid) ? (int)$SESSION->block_exacomp_last_topicid : 0, PARAM_INT);
$delete = optional_param('delete',0,PARAM_INT);

$showevaluation = optional_param('showevaluation', "", PARAM_ALPHA);
$bewertungsdimensionen=block_exacomp_getbewertungsschema($courseid);

$context = get_context_instance(CONTEXT_COURSE, $courseid);

$courseSettings = block_exacomp_coursesettings();
$alternativeDataVersion = get_config('exacomp', 'alternativedatamodel');

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

if($delete > 0 && $role == "teacher") {
	block_exacomp_delete_custom_example($delete);
}
if ($action == "save") {
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
	$students = get_role_users(5, $context);
}else{
	$students = array($USER);
}
$teachers = get_role_users(array(1,2,3,4), $context);
if($alternativeDataVersion) {
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


	if ($selected_subject) {
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
}

if ($showevaluation == 'on')
	$studentColspan = 2;
else
	$studentColspan = 1;

?>
<div class="exabis_competencies_lis">

	<?php

	if($alternativeDataVersion) {
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
	<?php 
	}

	if(($alternativeDataVersion && $selected_topic) || !$alternativeDataVersion) {
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


		if($alternativeDataVersion)
			include 'assign_competencies_lis_metadata.php';
		?>

	<div class="exabis_comp_top_legend">
		<div class="exabis_comp_top_legend">
			<img src="pix/list_12x11.png"
				alt=<?php echo get_string('activities', 'block_exacomp'); ?> />
			<?php echo get_string('activities', 'block_exacomp'); ?>
			- <img src="pix/folder_fill_12x12.png" alt="ePortfolio" /> ePortfolio
			- <img src="pix/x_11x11.png"
				alt="<?php echo get_string('noactivitiesyet', 'block_exacomp');?>" />
			<?php echo get_string('noactivitiesyet', 'block_exacomp');?>
			<?php if($role == "teacher") { ?>
			- <img src="pix/upload_12x12.png" alt="Upload" />
			<?php echo get_string('example_upload_header', 'block_exacomp');?>
			<?php } ?>
		</div>

		<?php

		if(!$alternativeDataVersion)
			$levels = block_exacomp_get_competence_tree_for_course($courseid);
		else
			$levels = block_exacomp_get_competence_tree_for_LIS($selected_topic->id);

		function block_exacomp_print_level_descriptors($level, $subs, &$data, $rowgroup_class = '') {
			global $CFG, $DB, $USER, $teachers;
			extract((array)$data);

			$version = 0;

			$url = '/blocks/exacomp/assign_competencies.php?courseid=' . $courseid;
			$url = $CFG->wwwroot . $url;

			foreach ($subs as $descriptor) {

				$activities = block_exacomp_get_activities($descriptor->id, $courseid);

				// in the alternative data model we use examples on this level, in the normal case we use descriptors
				$examples = $DB->get_records_sql(
						"SELECT de.id as deid, e.id, e.title, tax.title as tax, e.task, e.externalurl,
						e.externalsolution, e.externaltask, e.solution, e.completefile, e.description, e.taxid, e.attachement, e.creatorid
						FROM {block_exacompexamples} e
						JOIN {block_exacompdescrexamp_mm} de ON e.id=de.exampid AND de.descrid=?
						LEFT JOIN {block_exacomptaxonomies} tax ON e.taxid=tax.id
						ORDER BY tax.title", array($descriptor->id));

				if ($examples) {
					$data->rowgroup++;
					$this_rowgroup_class = 'rowgroup-header rowgroup-header-'.$data->rowgroup.' '.$rowgroup_class;
					$sub_rowgroup_class = 'rowgroup-content rowgroup-content-'.$data->rowgroup.' '.$rowgroup_class;
				} else {
					$this_rowgroup_class = $rowgroup_class;
				}

				$descrpadding = (get_config('exacomp','alternativedatamodel')) ? ($level-1)*20 :  ($level-2)*20+12;;

				if (preg_match('!^([^\s]*[0-9][^\s]*+)\s+(.*)$!iu', $descriptor->title, $matches)) {
					$output_id = $matches[1];
					$output_title = $matches[2];
				} else {
					$output_id = '';
					$output_title = $descriptor->title;
				}
				?>
		<tr class="exabis_comp_aufgabe <?php echo $this_rowgroup_class; ?>">
			<td><?php 
			if($role == "teacher") {
				?> <a
				href="example_upload.php?courseid=<?php echo $courseid;?>&descrid=<?php echo $descriptor->id;?>"
				target="_blank"
				onclick="window.open(this.href,this.target,'width=640,height=480'); return false;">
					<img src='pix/upload_12x12.png' />
			</a> <?php 
			}
			echo $output_id;
			?>
			</td>
			<td <?php if($examples) { ?>class="rowgroup-arrow" <?php } ?> style="padding-left: <?php echo $descrpadding; ?>px">
				<div class="aufgabetext">
					<?php echo $output_title; ?>
				</div> <?php 
				?>
			</td>
			<?php
			$columnCnt = 0;
			foreach ($students as $student) {

				if ($showevaluation) {
					$evaluationWert = null;
					$evaluationTooltip = null;
					if (isset($descriptor->evaluationData[$student->id])) {
						$evaluation = $descriptor->evaluationData[$student->id];
						if ($role == "teacher") {
							$evaluationWert = isset($evaluation->student_evaluation) ? $evaluation->student_evaluation : 0;
							$evaluationTooltip = isset($evaluation->starttime) ? $evaluation->starttime.' - '.$evaluation->endtime : '';
						} else {
							$evaluationWert = $evaluation->teacher_evaluation;
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
						echo '<input type="hidden" value="0" name="'.$checkboxname.'[' . $descriptor->id . '][' . $student->id . '][teacher_evaluation]" />';
						echo '<input type="checkbox" value="1" name="'.$checkboxname.'[' . $descriptor->id . '][' . $student->id . '][teacher_evaluation]"'.
								(isset($descriptor->evaluationData[$student->id])&&$descriptor->evaluationData[$student->id]->teacher_evaluation?' checked="checked"':'').' />';
					} else {
						echo '<select name="'.$checkboxname.'[' . $descriptor->id . '][' . $student->id . '][teacher_evaluation]">';
						for ($i=0; $i<=$bewertungsdimensionen; $i++) {
							echo '<option value="'.$i.'"'.(isset($descriptor->evaluationData[$student->id])&&$descriptor->evaluationData[$student->id]->teacher_evaluation==$i?' selected="selected"':'').'>'.$i.'</option>';
						}
						echo '</select>';
					}
				} else {
					if ($bewertungsdimensionen==1) {
						echo '<input type="hidden" value="0" name="'.$checkboxname.'[' . $descriptor->id . '][' . $student->id . '][student_evaluation]" />';
						echo '<input type="checkbox" value="1" name="'.$checkboxname.'[' . $descriptor->id . '][' . $student->id . '][student_evaluation]"'.
								(isset($descriptor->evaluationData[$student->id])&&isset($descriptor->evaluationData[$student->id]->student_evaluation) ?' checked="checked"':'').' />';
					} else {
						echo '<select name="'.$checkboxname.'[' . $descriptor->id . '][' . $student->id . '][student_evaluation]">';
						for ($i=0; $i<=$bewertungsdimensionen; $i++) {
							echo '<option value="'.$i.'"'.(isset($descriptor->evaluationData[$student->id])&&$descriptor->evaluationData[$student->id]->student_evaluation==$i?' selected="selected"':'').'>'.$i.'</option>';
						}
						echo '</select>';
					}

				}

				if ($stdicon = block_exacomp_get_student_icon($activities, $student,$courseid,$gradelib)) {
					if($stdicon->actSubOccured)
						echo '<span title="'.s($stdicon->text).'" class="exabis-tooltip">' . $stdicon->icon . '</span>';
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
					}
				}

				if(isset($descriptor->evaluationData[$student->id]->compalreadyreached))
					echo '<span title="'.s(get_string('compalreadyreached','block_exacomp')).'" class="exabis-tooltip"><img src="pix/info.png" /></span>';

				echo '</td>';
			}
			?>
		</tr>
		<?php 

		foreach($examples as $example) {
			
			if( isset($example->creatorid) && ($example->creatorid != $USER->id && !array_key_exists($example->creatorid, $teachers)))
				continue;

			$examplepadding = (get_config('exacomp','alternativedatamodel')) ? ($level-1)*35 : ($level-1)*20+35;
			?>
		<tr class="exabis_comp_aufgabe <?php echo $sub_rowgroup_class; ?>">
			<td></td>
			<td style="padding-left: <?php echo $examplepadding; ?>px">
				<p class="aufgabetext">
					<?php echo $example->title; 
					if(isset($example->creatorid) && $example->creatorid == $USER->id) {
						?>
					<a onclick="return confirm('Beispiel wirklich löschen?')"
						href="<?php echo $url.'&delete='.$example->id;?>"><img
						src="pix/x_11x11_redsmall.png" /> </a>
					<?php 
					}
					$img = '<img src="pix/i_11x11.png" alt="Beispiel" />';
					if($example->task)
						echo "<a target='_blank' href='".$example->task."'>".$img."</a>";
					if($example->externalurl)
						echo "<a target='_blank' href='".$example->externalurl."'>".$img."</a>";

					?>
				</p> <?php 
				?>
			</td>
			<?php 
			if(!get_config('exacomp','alternativedatamodel')) {
				$columnCnt = 0;
				foreach($students as $student)
					echo '<td class="exabis-tooltip colgroup colgroup-'.floor($columnCnt++/$schueler_gruppierung_breite).'"></td>';
			} else {
				$examplesEvaluationData = $DB->get_records_sql("
						SELECT deu.studentid, u.firstname, u.lastname, deu.*
						FROM {block_exacompexameval} deu
						LEFT JOIN {user} u ON u.id=deu.".($role == "teacher"?'studentid':'teacher_reviewerid')."
						WHERE deu.courseid=? AND deu.exampleid=?
						", array($courseid, $example->id));
					
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

					$checkboxname = "dataexamples";

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
			}
			?>
		</tr>
		<?php
		}
			}
		}

		function block_exacomp_print_levels($level, $subs, &$data, $rowgroup_class = '') {
			if (empty($subs)) return;

			extract((array)$data);

			if ($level == 0) {
				foreach ($subs as $group) {
					if(get_config('exacomp', 'alternativedatamodel'))
						$group->title = "Teilkompetenzen und Lernmaterialien";
					?>
		<tr class="highlight">
			<td colspan="2"><b><?php echo $group->title; ?> </b></td>
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
			
		block_exacomp_print_levels($level+1, $group->subs, $data);
				}

				return;
			}

			foreach ($subs as $item) {
				if (preg_match('!^([^\s]*[0-9][^\s]*+)\s+(.*)$!iu', $item->title, $matches)) {
					$output_id = $matches[1];
					$output_title = $matches[2];
				} else {
					$output_id = '';
					$output_title = $item->title;
				}

				$hasSubs = (!empty($item->subs) || !empty($item->descriptors) && !get_config('exacomp','alternativedatamodel'));

				if ($hasSubs) {
					$data->rowgroup++;
					$this_rowgroup_class = 'rowgroup-header rowgroup-header-'.$data->rowgroup.' '.$rowgroup_class;
					$sub_rowgroup_class = 'rowgroup-content rowgroup-content-'.$data->rowgroup.' '.$rowgroup_class;
				} else {
					$this_rowgroup_class = $rowgroup_class;
				}

				if ($level == 1) {
					//$rowgroup_class .= ' highlight';
				}
				if(!get_config('exacomp','alternativedatamodel')) {
					?>
		<tr class="exabis_comp_teilcomp <?php echo $this_rowgroup_class; ?> highlight">
			<td><?php echo $output_id; ?></td>
			<td class="rowgroup-arrow" style="padding-left: <?php echo ($level-1)*20+12; ?>px"><div
					class="desctitle">
					<?php echo $output_title; ?>
				</div></td>
			<?php
			$columnCnt = 0;
			foreach ($students as $student) {
				echo "<td class='colgroup colgroup-".floor($columnCnt++/$schueler_gruppierung_breite)."' colspan='".$studentColspan."'></td>";
			}
			?>
		</tr>
		<?php

				}
				if(!isset($sub_rowgroup_class))
					$sub_rowgroup_class = '';

				if (!empty($item->descriptors)) {
					block_exacomp_print_level_descriptors($level+1, $item->descriptors, $data, $sub_rowgroup_class);
				}

				if (!empty($item->subs)) {
					block_exacomp_print_levels($level+1, $item->subs, $data, $sub_rowgroup_class);
				}
			}
		}

		if ($levels) {

			?>
		<div class="ec_td_mo_auto">
			<?php
			echo spaltenbrowser(count($students),$schueler_gruppierung_breite);
			?>

			<form id="assign-competencies"
				action="assign_competencies.php?action=save&courseid=<?php echo $courseid; ?>"
				method="post">
				<input type="hidden" name="open_row_groups"
					value="<?php echo p(optional_param('open_row_groups', "", PARAM_TEXT)); ?>" />
				<table class="exabis_comp_comp">
					<?php
					$rowgroup = 0;
					$data = (object)array(
							'rowgroup' => 0,
							'courseid' => $courseid,
							'showevaluation' => $showevaluation,
							'students' => $students,
							'schueler_gruppierung_breite' => $schueler_gruppierung_breite,
							'studentColspan' => $studentColspan,
							'role' => $role,
							'bewertungsdimensionen' => $bewertungsdimensionen,
							'gradelib' => $gradelib
					);
					block_exacomp_print_levels(0, $levels, $data);

					?>
				</table>
				<input name="btn_submit" type="submit"
					value="<?php echo get_string('auswahl_speichern', 'block_exacomp'); ?>" />
			</form>
		</div>
		<?php
		}
		else {
			echo $OUTPUT->box(text_to_html(get_string("explainno_comps", "block_exacomp")));
		}
	}
	?>
	</div>
</div>
<?php 

echo $OUTPUT->footer();
