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

$courseid = required_param('courseid', PARAM_INT);
require_login($courseid);

$context = get_context_instance(CONTEXT_COURSE, $courseid);

//eigen berechtigung für exabis_competences, weil die moodle rollen nicht genau passen, zb
//bei moodle dürfen übergeordnete rollen alles der untergeordneten, dass soll hier nicht sein

if (has_capability('block/exacomp:teacher', $context)) {
	$introle = 1;
	$role = "teacher";
} elseif (has_capability('block/exacomp:student', $context)) {
	$introle = 0;
	$role = "student";
}

$url = '/blocks/exacomp/all_reached_competencies.php?courseid=' . $courseid;
$PAGE->set_url($url);
$url = $CFG->wwwroot . $url;

block_exacomp_print_header($role, 'studenttabcompetencesoverview', 'all_reached_for_all_courses');

$students = array($USER);
$levels = block_exacomp_get_competence_tree_all_reached_competencies_for_user();

?>
<div class="exabis_competencies_lis">

	<div class="exabis_comp_top_legend">

		<?php

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

				echo '<td class="reached-info">';

				$checkboxname = ($version) ? "dataexamples" : "data";

				if ($descriptor->reached)
					echo '<img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/accept.png" height="16" width="16" alt="Reached Competence" />';

				echo '</td>';
			}
			?>
		</tr>
		<?php 

		foreach($examples as $example) {

			//skip custom examples from other courses
			if( (!array_key_exists($example->creatorid, $teachers)) && isset($example->creatorid))
				continue;

			$examplepadding = (get_config('exacomp','alternativedatamodel')) ? ($level-1)*35 : $examplepadding = ($level-1)*20+35;
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
			/*
			if(!get_config('exacomp','alternativedatamodel')) {
				$columnCnt = 0;
				foreach($students as $student)
					echo '<td class="exabis-tooltip></td>';
			} else {
				$examplesEvaluationData = $DB->get_records_sql("
						SELECT deu.studentid, u.firstname, u.lastname, deu.*
						FROM {block_exacompexameval} deu
						LEFT JOIN {user} u ON u.id=deu.".($role == "teacher"?'studentid':'teacher_reviewerid')."
						WHERE deu.courseid=? AND deu.exampleid=?
						", array($courseid, $example->id));
					
				$columnCnt = 0;
				foreach ($students as $student) {

					echo '<td>';

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
			*/
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
				echo '<td class="exabis_comp_top_studentcol">'.fullname($student).'</td>';
			}
			?>
		</tr>
		<?php

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
					$rowgroup_class .= ' highlight';
				}
				if(!get_config('exacomp','alternativedatamodel')) {
					?>
		<tr class="exabis_comp_teilcomp <?php echo $this_rowgroup_class; ?>">
			<td><?php echo $output_id; ?></td>
			<td class="rowgroup-arrow" style="padding-left: <?php echo ($level-1)*20+12; ?>px"><div
					class="desctitle">
					<?php echo $output_title; ?>
				</div></td>
			<?php
			$columnCnt = 0;
			foreach ($students as $student) {
				echo "<td></td>";
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

				<table class="exabis_comp_comp">
					<?php
					$rowgroup = 0;
					$data = (object)array(
							'rowgroup' => 0,
							'courseid' => $courseid,
							'students' => $students,
							'role' => $role,
							'gradelib' => $gradelib
					);
					block_exacomp_print_levels(0, $levels, $data);

					?>
				</table>
		</div>
		<?php
		}
		else {
			echo $OUTPUT->box(text_to_html(get_string("explainno_comps", "block_exacomp")));
		}
		
	?>
	</div>
</div>
<?php 

echo $OUTPUT->footer();
