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

$version = get_config('exacomp', 'alternativedatamodel');
$courseSettings = block_exacomp_coursesettings();
// if alternatie data model & not using activities: show all descriptors
$course_desriptors = ($version && $courseSettings->uses_activities == 0) ? block_exacomp_get_descriptors_of_all_courses(0) : block_exacomp_get_descriptors_of_all_courses();

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

$levels = block_exacomp_get_competence_tree_for_all_courses();
$content .= '<div class="ec_td_mo_auto">';
$content .= '<div class="exabis_competencies_lis">';
$content .= '<br/><br/><table class="exabis_comp_comp">';

$rowgroup = 0;
$data = (object)array(
		'rowgroup' => 0,
		'courseid' => $courseid
);
block_exacomp_print_levels(0, $levels, $data, $content);


$content .= '</table></div>';
 
function block_exacomp_print_levels($level, $subs, &$data, &$content, $rowgroup_class = ''){
	
	if ($level == 0) {
		foreach ($subs as $group) {
			$content .=	'<tr class="highlight">';
			$content .= '<td colspan="2"><b>'.$group->title.'</b></td>';
			$content .= '<td>L</td>';
			$content .= '<td>S</td>';
			$content .= '</tr>';
	
			block_exacomp_print_levels($level+1, $group->subs, $data, $content);
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
	
		$hasSubs = !empty($item->subs) || !empty($item->descriptors);
	
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
	
		$content .= '<tr class="exabis_comp_teilcomp'.$this_rowgroup_class.'">';
		$content .= '<td>'.$output_id.'</td>';
		$content .= '<td class="rowgroup-arrow" style="padding-left:'.(($level-1)*20+12).'px"><div class="desctitle">'.$output_title.'</div></td>';	
		$content .= '<td></td>';
		$content .= '<td></td>';
		$content .= '</tr>';
			
		if (!empty($item->descriptors)) {
			block_exacomp_print_level_descriptors($level+1, $item->descriptors, $data, $content);
		}
		
		if (!empty($item->subs)) {
			block_exacomp_print_levels($level+1, $item->subs, $data, $content, $sub_rowgroup_class);
		}
	}
		
}

function block_exacomp_print_level_descriptors($level, $subs, &$data, &$content, $rowgroup_class = '') {
	global $CFG, $DB, $USER, $COURSE;
	extract((array)$data);
	
	$version = 0;
	foreach ($subs as $descriptor) {
		if(isset($descriptor->evaluationData[$USER->id])){
			
			$activities = block_exacomp_get_activities($descriptor->id, $courseid);
		
			$content .= '<tr class="exabis_comp_aufgabe'.$rowgroup_class.'">';
			$content .= '<td></td>';
			$content .= '<td style="padding-left:'.(($level-1)*20+12).'px">';
			$content .= '<p class="aufgabetext">'.$descriptor->title.'</p>';
			$content .= '</td>';
			$content .= '<td><img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/accept.png" height="16" width="16" alt="'.get_string("not_met", "block_exacomp").'" /></td>';
			if(isset ($descriptor->evaluationData[$USER->id]->student_evaluation))	
				$content .= '<td><img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/accept.png" height="16" width="16" alt="'.get_string("not_met", "block_exacomp").'" /></td>';
			else
				$content .= '<td><img src="' . $CFG->wwwroot . '/blocks/exacomp/pix/cancel.png" height="16" width="16" alt="'.get_string("not_met", "block_exacomp").'" /></td>';
			$content .= '</tr>';
		
		}		
	}
}

echo $content;

echo '</div></div>'; //exabis_competences_block
echo $OUTPUT->footer();
?>

