<?php
require_once dirname(__FILE__) . '/inc.php';
require_once dirname(__FILE__) . '/lib/div.php';

global $COURSE, $CFG, $OUTPUT;
$courseid = required_param('courseid', PARAM_INT);
$courseid = (isset($courseid)) ? $courseid : $COURSE->id;
$action = optional_param('action', "", PARAM_ALPHA);

require_login($courseid);
$PAGE->set_url('/blocks/exacomp/competence_profile_settings.php?courseid=' . $courseid);
block_exacomp_print_header("student", "studenttabcompetenceprofile");

if($action == "detail") {
	if(optional_param('exacomp','',PARAM_TEXT) == '' && optional_param('exastud','',PARAM_TEXT)=='' && optional_param('exaport','',PARAM_TEXT)=='')
		$action="save";
}
if($action == "save") {
	//delete old user settings
	$DB->delete_records('block_exacompprofilesettings',array('userid'=>$USER->id));
	if(isset($_POST['exacomp']))
		foreach($_POST['exacomp'] as $course) {
			$DB->insert_record('block_exacompprofilesettings',array("block"=>"exacomp","itemid"=>$course,"userid"=>$USER->id,"feedback"=>0));
		}
	if(isset($_POST['exastud']))
		foreach($_POST['exastud'] as $period) {
			$DB->insert_record('block_exacompprofilesettings',array("block"=>"exastud","itemid"=>$period,"userid"=>$USER->id,"feedback"=>0));
		}
	if(isset($_POST['exaport']))
		foreach($_POST['exaport'] as $item) {
			$DB->insert_record('block_exacompprofilesettings',array("block"=>"exaport","itemid"=>$item,"userid"=>$USER->id,"feedback"=>0));
		}
	echo get_string('save','block_exacomp');
	unset($action);
}
if (empty($action)){
	echo $OUTPUT->box(text_to_html(get_string("explainprofilesettings", "block_exacomp")));
	$inhalt='<form action="competence_profile_settings.php?courseid=' . $courseid . '&amp;action=detail" method="post">';
	
	$checked = (block_exacomp_check_profile_settings($USER->id,"exacomp")) ? " checked " : "";
	$inhalt.='<p><h3><input '.$checked.'type="checkbox" name="exacomp" value="exacomp" /> Exabis Competencies</h3>';
	$inhalt.= get_string('explain_exacomp_profile_settings','block_exacomp');
	$inhalt.='</p>';
	
	if(block_exacomp_exastudexists()) {
		$checked = (block_exacomp_check_profile_settings($USER->id,"exastud")) ? " checked " : "";
		$inhalt.='<p><h3><input '.$checked.'type="checkbox" name="exastud" value="exastud" /> Exabis Student Review</h3>';
		$inhalt.= get_string('explain_exastud_profile_settings','block_exacomp');
		$inhalt.='</p>';
	}
	
	if(block_exacomp_exaportexists()) {
		$checked = (block_exacomp_check_profile_settings($USER->id,"exaport")) ? " checked " : "";
		$inhalt.='<p><h3><input '.$checked.'type="checkbox" name="exaport" value="exaport" /> Exabis ePortfolio</h3>';
		$inhalt.= get_string('explain_exaport_profile_settings','block_exacomp');
		$inhalt.='</p>';
	}
	$inhalt.='<input type="submit" value="' . get_string('auswahl_speichern', 'block_exacomp') . '" /></form>';
	echo $OUTPUT->box($inhalt);
} else if($action == "detail") {
	$inhalt = '<form action="competence_profile_settings.php?courseid=' . $courseid . '&amp;action=save" method="post">';
	
	if(optional_param('exacomp','',PARAM_TEXT) == 'exacomp') {
		$exacomp='<p><h3>Exabis Competencies</h3>';
		$exacomp.= get_string('explain_exacomp_profile_settings','block_exacomp');
		$exacomp.='</p>';
		
		$coursedescriptors = block_exacomp_get_descriptors_of_all_courses();
		foreach($coursedescriptors as $course) {
			if($course->descriptors) {
				$checked = (block_exacomp_check_profile_settings($USER->id,"exacomp",$course->id)) ? " checked " : "";
				$exacomp.='<p><input '.$checked.'type="checkbox" name="exacomp[]" value="'.$course->id.'" /> '.$course->fullname.'</p>';
			}
		}
		
		$inhalt.= $OUTPUT->box($exacomp);
	}
	
	if(block_exacomp_exastudexists() && optional_param('exastud','',PARAM_TEXT) == 'exastud') {
		$exastud='<p><h3>Exabis Student Review</h3>';
		$exastud.= get_string('explain_exastud_profile_settings','block_exacomp');
		$exastud.='</p>';
		$sql = "SELECT p.id,p.description FROM {block_exastudreview} r, {block_exastudperiod} p WHERE r.student_id = ? AND r.periods_id = p.id GROUP BY p.id";
		$periods = $DB->get_records_sql($sql,array("studentid"=>$USER->id));
		foreach($periods as $period) {
			$checked = (block_exacomp_check_profile_settings($USER->id,"exastud",$period->id)) ? " checked " : "";
			$exastud.='<p><input '.$checked.'type="checkbox" name="exastud[]" value="'.$period->id.'" /> '.$period->description.'</p>';
		}
	
		$inhalt.= $OUTPUT->box($exastud);
	}
	
	if(block_exacomp_exaportexists() && optional_param('exaport','',PARAM_TEXT) == 'exaport' && block_exacomp_check_portfolio_competences($USER->id)) {
		require_once($CFG->dirroot . "/blocks/exaport/lib/lib.php");
		$exaport='<p><h3>Exabis ePortfolio</h3>';
		$exaport.= get_string('explain_exaport_profile_settings','block_exacomp');
		$exaport.='</p>';
		
		$items = $DB->get_records('block_exaportitem',array("userid"=>$USER->id));
		foreach($items as $item) {
			if(!block_exaport_check_item_competences($item))
				continue;
			$checked = (block_exacomp_check_profile_settings($USER->id,"exaport",$item->id)) ? " checked " : "";
			$exaport.='<p><input '.$checked.' type="checkbox" name="exaport[]" value="'.$item->id.'" /> '.$item->name.'</p>';
		}
	
		$inhalt.= $OUTPUT->box($exaport);
	}
	$inhalt.='<input type="submit" value="' . get_string('auswahl_speichern', 'block_exacomp') . '" /></form>';
	echo $inhalt;
}