<?php


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

$levels = array();
$topics = $DB->get_records_sql('
	SELECT t.id, t.title, s.id AS sid, s.title AS stitle
	FROM {block_exacomptopics} t
	JOIN {block_exacompcoutopi_mm} topmm ON topmm.topicid=t.id AND topmm.courseid=?
	JOIN {block_exacompsubjects} s ON t.subjid = s.id
	WHERE t.parentid=0
	GROUP BY t.id
	ORDER BY s.id, t.sorting
	', array($courseid));

foreach ($topics as $topicRow) {
	$topic = (object)array(
		'id' => $topicRow->id,
		'title' => $topicRow->title,
		'type' => 'topic',
		'subs' => array()
	);

	block_exacomp_build_topic_tree($courseid, $courseSettings, $topic);
	
	if (!empty($topic->subs) || !empty($topic->descriptors)) {
		// only add this one if has subtopics or descriptors
		if (empty($levels[$topicRow->sid])) {
			$levels[$topicRow->sid] = (object)array(
				'id' => $topicRow->sid,
				'title' => $topicRow->stitle,
				'type' => 'subject',
				'subs' => array()
			);
		}

		$levels[$topicRow->sid]->subs[$topic->id] = $topic;
	}
}

function block_exacomp_build_topic_tree($courseid, $courseSettings, &$parentTopic) {
	global $DB;
	
	$parentTopic->descriptors = $DB->get_records_sql('
		SELECT d.id, d.title, "descriptor" AS type
		FROM {block_exacompdescriptors} d
		JOIN {block_exacompdescrtopic_mm} topmm ON topmm.descrid=d.id AND topmm.topicid=?
		'.($courseSettings->show_all_descriptors ? '' : '
				-- only show active ones
				JOIN {block_exacompdescractiv_mm} da ON d.id=da.descrid
				JOIN {course_modules} a ON da.activityid=a.id AND a.course='.$courseid.'
				').'
		GROUP BY d.id
		ORDER BY d.sorting
		', array($parentTopic->id));
	
	foreach ($parentTopic->descriptors as &$descriptor) {
		$descriptor->evaluationData = $DB->get_records_sql("
				SELECT deu.userid, u.firstname, u.lastname, deu.*, deu.wert as teacher_evaluation
				FROM {block_exacompdescuser} deu
				LEFT JOIN {user} u ON u.id=deu.userid
				WHERE deu.courseid=? AND deu.descid=? AND deu.role = 1
				", array($courseid, $descriptor->id));

		foreach($descriptor->evaluationData as $exaeval) {
			$exaeval->student_evaluation = $DB->get_field('block_exacompdescuser', 'wert', array("userid"=>$exaeval->userid,"descid"=>$exaeval->descid,"role"=>0,"courseid"=>$exaeval->courseid));
		}
	}
	/*
	if ($parentTopic->descriptors) {
		return;
	}
	*/

	$topics = $DB->get_records_sql('
		SELECT t.id, t.title
		FROM {block_exacomptopics} t
		WHERE t.parentid = ?
		ORDER BY t.sorting
		', array($parentTopic->id));
	
	foreach ($topics as $topic) {
		$topic = (object)array(
			'id' => $topic->id,
			'title' => $topic->title,
			'type' => 'topic',
			'subs' => array()
		);

		block_exacomp_build_topic_tree($courseid, $courseSettings, $topic);
		
		if (!empty($topic->subs) || !empty($topic->descriptors)) {
			// only add this one if has subtopics or descriptors
			$parentTopic->subs[$topic->id] = $topic;
		}
	}
}
//echo "<pre>";
//print_r($levels); exit;

if ($showevaluation == 'on')
	$studentColspan = 2;
else
	$studentColspan = 1;

?>
<div class="exabis_competencies_lis">

	<?php
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


		?>
	<div class="exabis_comp_top_legend">
		<img src="pix/list_12x11.png" alt="Aktivitäten" /> Aktivitäten - <img
			src="pix/folder_fill_12x12.png" alt="ePortfolio" /> ePortfolio - <img
			src="pix/x_11x11.png" alt="Leer" /> noch keine Aufgaben zu diesem
		Deskriptor abgegeben und keinen Test durchgeführt
	</div>

	<?php

function block_exacomp_print_level_descriptors($level, $subs, &$data) {
	extract((array)$data);

	$version = 0;
	
	foreach ($subs as $descriptor) {

		$activities = block_exacomp_get_activities($descriptor->id, $courseid);
		?>
		<tr
			class="exabis_comp_aufgabe <?php echo $data->rowgroup_class; ?>">
			<td></td>
			<td style="padding-left: <?php echo ($level-1)*20+12; ?>px">
				<p class="aufgabetext">
					<?php echo $descriptor->title; ?>
				</p> <?php 
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
							$evaluationWert = $evaluation->student_evaluation;
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
								(isset($descriptor->evaluationData[$student->id])&&$descriptor->evaluationData[$student->id]->student_evaluation?' checked="checked"':'').' />';
					} else {
						echo '<select name="'.$checkboxname.'[' . $descriptor->id . '][' . $student->id . '][student_evaluation]">';
						for ($i=0; $i<=$bewertungsdimensionen; $i++) {
							echo '<option value="'.$i.'"'.(isset($descriptor->evaluationData[$student->id])&&$descriptor->evaluationData[$student->id]->student_evaluation==$i?' selected="selected"':'').'>'.$i.'</option>';
						}
						echo '</select>';
					}

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
				echo '</td>';
			}
			?>
		</tr>
		<?php 
	}
}

function block_exacomp_print_levels($level, $subs, &$data) {
	if (empty($subs)) return;

	extract((array)$data);
	
	if ($level == 0) {
		foreach ($subs as $group) {
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
	
	$original_rowgroup_class = $data->rowgroup_class;
	
	foreach ($subs as $item) {
		if (preg_match('!^([^\s]*[0-9][^\s]*+)\s+(.*)$!iu', $item->title, $matches)) {
			$output_id = $matches[1];
			$output_title = $matches[2];
		} else {
			$output_id = '';
			$output_title = $item->title;
		}
		
		$hasSubs = !empty($item->subs) || !empty($item->descriptors);

		if ($hasSubs) {
			$data->rowgroup++;
			$rowgroup_class = 'rowgroup-header rowgroup-header-'.$data->rowgroup.' '.$original_rowgroup_class;
			$data->rowgroup_class = 'rowgroup-content rowgroup-content-'.$data->rowgroup.' '.$original_rowgroup_class;
		} else {
			$rowgroup_class = $original_rowgroup_class;
		}
		
		if ($level == 1) {
			$rowgroup_class .= ' highlight';
		}
		
		?>
		<tr
			class="exabis_comp_teilcomp <?php echo $rowgroup_class; ?>">
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

		if (!empty($item->descriptors)) {
			block_exacomp_print_level_descriptors($level+1, $item->descriptors, $data);
		}
		
		block_exacomp_print_levels($level+1, $item->subs, $data);
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
					'rowgroup_class' => '',
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

				
				return;
				foreach ($levels as $group) {
					$rowgroup++;

					?>
					<tr>
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
				}
	}
	else {
		echo $OUTPUT->box(text_to_html(get_string("explainno_comps", "block_exacomp")));
	}


?>
</div>
</div>
<?php 
