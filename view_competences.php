<?php

require_once dirname(__FILE__) . '/inc.php';
require_once dirname(__FILE__) . '/lib/div.php';
require_once($CFG->dirroot . "/lib/datalib.php");

global $COURSE, $CFG, $OUTPUT, $USER,$DB;
$sql5="SET @@group_concat_max_len = 5012";

$DB->execute($sql5);


$content = "";
$courseid = required_param('courseid', PARAM_INT);
require_login($courseid);

$context = get_context_instance(CONTEXT_COURSE, $courseid);
require_capability('block/exacomp:student', $context);

$url = '/blocks/exacomp/view_competences.php?courseid=' . $courseid;
$PAGE->set_url($url);
$url = $CFG->wwwroot . $url;
$identifier = "studenttabcompetencesoverview";
block_exacomp_print_header("student", $identifier);
echo '<script type="text/javascript" src="lib/wz_tooltip.js"></script>';


$content.= $OUTPUT->box(text_to_html(get_string("explaincompetencesoverview", "block_exacomp")));
$content.='<div class="grade-report-grader">';

$course_desriptors = block_exacomp_get_descriptors_of_all_courses();

$content.='<table id="comps" class="compstable flexible boxaligncenter generaltable">';
$content.='<tr class="heading r0"><td><h3>'.get_string("course", "block_exacomp").'</h3></td><td><h3>'.get_string("gesamt", "block_exacomp").'</h3></td><td><h3>'.get_string("erreicht", "block_exacomp").'</h3></td><td></td></tr>';
$total=0;
$total_achieved=0;
$total_avg=0;
$countstudents=0;
$countstudentsges=0;$gesamtpossible=0;
foreach ($course_desriptors as $coures_descriptor) {
	if(!$coures_descriptor->descriptors)
		continue;
	
	$grading=1;
	if($coures_descriptor->id) $grading=getgrading($coures_descriptor->id);
	
	$conttemp = get_context_instance(CONTEXT_COURSE, $coures_descriptor->id);
	$students = get_role_users(5, $conttemp);
	$countstudents=count($students);
 	$countstudentsges=$countstudentsges+$countstudents;
	$content.='<tr><td>'.$coures_descriptor->fullname.'</td><td>'.count($coures_descriptor->descriptors).'</td><td>'.count(block_exacomp_get_usercompetences($USER->id, 1, $coures_descriptor->id)).'</td><td>'.block_exacomp_get_ladebalken($coures_descriptor->id, $USER->id, count($coures_descriptor->descriptors),null,$grading,0,$countstudents).'</td></tr>';
	$total = $total + count($coures_descriptor->descriptors);
	$total_avg = $total_avg + block_exacomp_get_average_course_competences($coures_descriptor->id,$grading)->a;
	$total_achieved = $total_achieved + count(block_exacomp_get_usercompetences($USER->id, 1, $coures_descriptor->id));
	$gesamtpossible=$gesamtpossible+($countstudents*count($coures_descriptor->descriptors));
}
//echo $coures_descriptor->id."=id   ".$USER->id."=userid    ".$total."=total    ".$total_achieved."=total_achieved dann 1   ".$total_avg."=total_avg   ".$countstudentsges."=countstudentsges   ".$gesamtpossible."=gesamtpossible";
$content.='<tr><td><b>Total</b></td><td>'.$total.'</td><td>'.$total_achieved.'</td><td>'.block_exacomp_get_ladebalken($coures_descriptor->id, $USER->id, $total,$total_achieved,1,$total_avg,$countstudentsges,$gesamtpossible).'</td></tr></table>';
foreach ($course_desriptors as $coures_descriptor) {
	$descriptors = $coures_descriptor->descriptors;
	if ($descriptors) {

		$content.='<table class="compstable flexible boxaligncenter generaltable"><tr class="heading r0">
		<td class="category catlevel1"  scope="col"><h2>' . $coures_descriptor->fullname .'</h2></td>
		<td>L</td>
		<td>S</td></tr>';

		$trclass = "even";
		$topic = "";
		foreach ($descriptors as $descriptor) {
			if ($trclass == "even") {
				$trclass = "odd";
				$bgcolor = ' style="background-color:#efefef" ';
			} else {
				$trclass = "even";
				$bgcolor = ' style="background-color:#ffffff" ';
			}

			if ($topic !== $descriptor->topic) {
				$topic = $descriptor->topic;
				$content .= '<tr><td colspan="3"><b>' . $topic . '</b></td></tr>';
			}

			$content .= '<tr class="r2 ' . $trclass . '" ' . $bgcolor . '><td class="ec_minwidth">' . $descriptor->title . '</td>
			<td>###icon' . $coures_descriptor->id."_".$descriptor->id . '###</td>
			<td>###studenticon' . $coures_descriptor->id."_".$descriptor->id . '###</td></tr>';
		}
		$content .= '</table>';
	}
}
foreach ($course_desriptors as $coures_descriptor) {
	$descriptors = $coures_descriptor->descriptors;
	if ($descriptors) {
		$teacher_competences = block_exacomp_get_usercompetences($USER->id,1,$coures_descriptor->id);
		foreach ($teacher_competences as $teacher_competence) {
			if (!empty($teacher_competence))
				$content = str_replace('###icon' . $coures_descriptor->id."_".$teacher_competence->id . '###', '<img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/accept.png" height="16" width="16" alt="Reached Competence" />', $content);
		}
		$content = preg_replace('/###icon'.$coures_descriptor->id."_".'([0-9])+###/', '<img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/cancel.png" height="16" width="16" alt="'.get_string("not_met", "block_exacomp").'" />', $content);
		
		$user_competences = block_exacomp_get_usercompetences($USER->id, 0,$coures_descriptor->id);
		foreach ($user_competences as $user_competence) {
			if (!empty($user_competence))
				$content = str_replace('###studenticon' . $coures_descriptor->id."_".$user_competence->id . '###', '<img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/accept.png" height="16" width="16" alt="'.get_string("not_met", "block_exacomp").'" />', $content);
		}
		$content = preg_replace('/###studenticon'.$coures_descriptor->id."_".'([0-9])+###/', '<img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/cancel.png" height="16" width="16" alt="'.get_string("not_met", "block_exacomp").'" />', $content);
	}
}
//Portfolio Kompetenzen
if (block_exacomp_exaportexists()){
	$descriptors = block_exacomp_check_portfolio_competences($USER->id);
	if ($descriptors) {
		$content.='	<table class="compstable flexible boxaligncenter generaltable"><tr class="heading r0">
		<td class="category catlevel1"  scope="col"><h2 class="eP">exabis ePortfolio</h2></td>
		<td>L</td>
		<td>S</td></tr>';
		$name="";
		foreach ($descriptors as $descriptor) {
			if ($trclass == "even") {
				$trclass = "odd";
				$bgcolor = ' style="background-color:#efefef" ';
			} else {
				$trclass = "even";
				$bgcolor = ' style="background-color:#ffffff" ';
			}

			if ($name !== $descriptor->name) {
				$name = $descriptor->name;
				$content .= '<tr><td colspan="3"><b>' . $name . '</b></td></tr>';
			}

			$content .= '<tr class="r2 ' . $trclass . '" ' . $bgcolor . '><td class="ec_minwidth">' . $descriptor->title . '</td>';

			$icon = block_exacomp_check_teacher_assign($descriptor,$USER->id,1,false,2000);
			$content .= '<td>'.$icon.'</td>';
			$icon = block_exacomp_check_student_assign($descriptor,$USER->id,false,2000);
			$content .= '<td>'.$icon.'</td></tr>';
		}
	}
}
$content.="</table></div>";
echo $content;
echo '</div>'; //exabis_competences_block
echo $OUTPUT->footer();
?>
