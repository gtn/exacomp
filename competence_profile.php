<?php
require_once dirname(__FILE__) . '/inc.php';
require_once dirname(__FILE__) . '/lib/div.php';
require_once dirname(__FILE__) . '/lib/radargraph.php';
require_once($CFG->dirroot . "/lib/datalib.php");

global $COURSE, $CFG, $OUTPUT, $USER,$DB;

$content = "";
$courseid = required_param('courseid', PARAM_INT);
$view = optional_param('view', 'html', PARAM_TEXT);
require_login($courseid);

$context = get_context_instance(CONTEXT_COURSE, $courseid);
require_capability('block/exacomp:student', $context);

$url = '/blocks/exacomp/competence_profile.php?courseid=' . $courseid;
$PAGE->set_url($url);
$url = $CFG->wwwroot . $url;

$imgdir = make_upload_directory("exacomp/temp/userpic/{$USER->id}");

$fs = get_file_storage();
$context = $DB->get_record("context",array("contextlevel"=>30,"instanceid"=>$USER->id));
$files = $fs->get_area_files($context->id, 'user', 'icon', 0, '', false);
$file = reset($files);
unset($files);
//copy file
if($file) {
	$newfile=$imgdir.$file->get_filename();
	$file->copy_content_to($newfile);
}

$css = "styles_print.css";
$profile = block_exacomp_read_profile_template($view);
$profile2=$profile;
$profile = str_replace ( '###TITLE###', get_string('competence_profile','block_exacomp'), $profile);
$profile = str_replace ( '###INFOTEXT###', get_string('infotext','block_exacomp'), $profile);
$profile = str_replace ( '###EXAPORTINFOTEXT###', get_string('exaportinfotext','block_exacomp'), $profile);
$profile = str_replace ( '###EXASTUDINFOTEXT###', get_string('exastudinfotext','block_exacomp'), $profile);
$profile = str_replace ( '###NAMETRANSLATION###', get_string('name','block_exacomp'), $profile);
$profile = str_replace ( '###CITYTRANSLATION###', get_string('city','block_exacomp'), $profile);

$profile = str_replace ( '###CSSFILE###', $css, $profile );
$profile = str_replace ( '###NAME###', $USER->firstname. ' ' .$USER->lastname, $profile );
$profile = str_replace ( '###CITY###', $USER->city, $profile );

$profilesettings = ($DB->count_records("block_exacompprofilesettings",array("userid"=>$USER->id))) ? true : false;
$course_desriptors = block_exacomp_get_descriptors_of_all_courses();

$cssclass= '';
$total=0;
$total_achieved=0;
$coursesummary="";
foreach ($course_desriptors as $coures_descriptor) {
	$descriptors = $coures_descriptor->descriptors;
	if(!$descriptors)
		continue;

	if($profilesettings && !block_exacomp_check_profile_settings($USER->id,"exacomp",$coures_descriptor->id))
		continue;
	$coursesummary.='<tr class="'.$cssclass.'"><td>'.$coures_descriptor->fullname.'</td><td>'.count($coures_descriptor->descriptors).'</td><td>'.count(block_exacomp_get_usercompetences($USER->id, 1, $coures_descriptor->id)).'</td></tr>';
	$total = $total + count($coures_descriptor->descriptors);
	$total_achieved = $total_achieved + count(block_exacomp_get_usercompetences($USER->id, 1, $coures_descriptor->id));
	$cssclass = block_exacomp_switch_css($cssclass);
}
if($coursesummary != '') {
	$profile = str_replace ( '###EXACOMP###', block_exacomp_read_exacomp_template(), $profile);
	$profile = str_replace ( '###EXACOMPINFOTEXT###', get_string('exacompinfotext','block_exacomp'), $profile);
	$profile = str_replace ( '###COURSE###', get_string('course','block_exacomp'), $profile);
	$profile = str_replace ( '###TOTAL###', get_string('gesamt','block_exacomp'), $profile);
	$profile = str_replace ( '###ACHIEVED###', get_string('erreicht','block_exacomp'), $profile);
	$profile = str_replace ( '###EXACOMP_COURSESUMMARY###', $coursesummary, $profile);
	$profile = str_replace ( '###EXACOMP_TOTALAMOUNT###', $total, $profile);
	$profile = str_replace ( '###EXACOMP_TOTALREACHED###', $total_achieved, $profile);
} else
	$profile = str_replace ( '###EXACOMP###','',$profile);

$profile = str_replace ( '###EXACOMP_TABLES###', block_exacomp_get_competence_tables($course_desriptors), $profile);

//Portfolio Kompetenzen
if (block_exacomp_exaportexists()){
	require_once($CFG->dirroot . "/blocks/exaport/lib/lib.php");
	$exaport='';

	$descriptors = block_exacomp_check_portfolio_competences($USER->id);
	if ($descriptors) {
		$profile = str_replace ( '###EXAPORT_COMPS###', block_exacomp_get_portfolio_table($descriptors), $profile);
	}
	else
		$profile = str_replace ( '###EXAPORT_COMPS###', '', $profile);

	if($items = $DB->get_records('block_exaportitem',array("userid"=>$USER->id))) {
		$anycomp=false;
		foreach($items as $item) {
			if(!block_exaport_check_item_competences($item))
				continue;

			if($profilesettings && !block_exacomp_check_profile_settings($USER->id,"exaport",$item->id))
				continue;
				
			$anycomp=true;
			$cssclass = "printrowgrey";
			$exaport.='<table class="bordertable">';
			$exaport.='<tr class="printrowheading"><td width="100%" colspan="2" class="text"><h3>'.$item->name.'</h3></td></tr>';
			$exaport.='<tr><td class="legend">'.get_string('exaportintro','block_exacomp').'</td><td class="text">'.$item->intro.'</td></tr>';
			$exaport.='<tr class="printrowgrey"><td class="legend">'.get_string('exaportcategory','block_exacomp').'</td><td class="text">'.$DB->get_field('block_exaportcate', 'name', array('id'=>$item->categoryid)).'</td></tr>';
			$exaport.='<tr><td class="legend">'.get_string('exaporttype','block_exacomp').'</td><td class="text">'.$item->type.'</td></tr>';
			switch ($item->type) {
				case ("link"):
					$exaport.='<tr class="'.$cssclass.'"><td class="legend">'.get_string('url','block_exaport').'</td><td class="text">'.$item->url.'</td></tr>';
					$cssclass = block_exacomp_switch_css($cssclass);
					break;
				case ("file"):
					$context = $DB->get_record("context",array("contextlevel"=>30,"instanceid"=>$USER->id));
					$files = $fs->get_area_files($context->id, 'block_exaport', 'item_file',false,"id");
					$portfile = reset($files);
					unset($files);
					//copy file
					if($portfile) {
						$exaport.='<tr class="'.$cssclass.'"><td class="legend">'.get_string('exaportfilename','block_exacomp').'</td><td class="text">'.$portfile->get_filename().'</td></tr>';
						$cssclass = block_exacomp_switch_css($cssclass);
					}
					break;
			}
			$exaport.='<tr class="'.$cssclass.'"><td colspan="2">'.get_string('exaportinfo','block_exacomp').'</td></tr>';
			$cssclass = block_exacomp_switch_css($cssclass);
			$exaport.='<tr class="'.$cssclass.'"><td colspan="2"><ul>';
			$competencies = $DB->get_records('block_exacompdescractiv_mm',array("activitytype"=>2000,"activityid"=>$item->id));
			foreach($competencies as $competence)
				$exaport.='<li>'.$DB->get_field('block_exacompdescriptors','title', array("id"=>$competence->descrid)).'</li>';
			$exaport.='</ul></td></tr>';
			$exaport.='</table><p></p>';

		}
		if($anycomp) {
			$profile = str_replace ( '###EXAPORT###', block_exacomp_get_eportfolio_template(), $profile);
			$profile = str_replace ( '###EXAPORT###', $exaport, $profile);
		}
		else
			$profile = str_replace ( '###EXAPORT###', '', $profile);
	} else
		$profile = str_replace ( '###EXAPORT###', '', $profile);
}
else {
	$profile = str_replace ( '###EXAPORT_COMPS###', '', $profile);
	$profile = str_replace ( '###EXAPORT###', '', $profile);

}

if(block_exacomp_exastudexists()) {
	require_once($CFG->dirroot . "/blocks/exastud/lib/lib.php");
	if($reviews = block_exabis_student_review_get_review_periods($USER->id)) {
		$exastud='';
		foreach($reviews as $review) {
			if($profilesettings && !block_exacomp_check_profile_settings($USER->id,"exastud",$review->periods_id))
				continue;
			$exastud.=block_exacomp_get_student_report($USER->id, $review->periods_id);
		}
		if($exastud!='')
			$profile = str_replace ( '###EXASTUD###', block_exacomp_get_studentreview_template(), $profile);
		else
			$profile = str_replace ( '###EXASTUD###', '', $profile);

		$profile = str_replace ( '###EXASTUD###', $exastud, $profile);
	}
	else
		$profile = str_replace ( '###EXASTUD###', '', $profile);
} else
	$profile = str_replace ( '###EXASTUD###', '', $profile);

if($view == "print") {
	require_once($CFG->dirroot.'/lib/tcpdf/tcpdf.php');
	try
	{
		$profile = str_replace ( '###USERPIC###', '', $profile);

		// create new PDF document
		$pdf = new TCPDF("P", "pt", "A4", true, 'UTF-8', false);
		$pdf->SetTitle('Kompetenzprofil');
		$pdf->AddPage();
		$pdf->setCellHeightRatio(1.25);
		if($file) $pdf->Image($newfile,480,243, 75, 75);
		
		$pdf->writeHTML($profile, true, false, true, false, '');

		$pdf->Output('Kompetenzprofil.pdf', 'I');
		unlink($newfile);
	}
	catch(tcpdf_exception $e) {
		echo $e;
		exit;
	}
}
else {
	
	
	$identifier = "studenttabcompetenceprofile";
	$hdrtmp=block_exacomp_print_header("student", $identifier,null,true);
	$hdrers='
	<link rel="stylesheet" type="text/css" href="styles_print.css" />
	';
	$hdrtmp=str_replace("</head>",$hdrers.'</head>',$hdrtmp);
	echo $hdrtmp;
	$pdfurl = $CFG->wwwroot . "/blocks/exacomp/competence_profile.php?view=print&amp;courseid=".$courseid;
	$settingsurl = $CFG->wwwroot . "/blocks/exacomp/competence_profile_settings.php?courseid=".$courseid;

	echo $OUTPUT->box("<a href='".$pdfurl."'>".get_string('createpdf','block_exacomp')."</a>");
	echo $OUTPUT->box("<a href='".$settingsurl."'>".get_string('pdfsettings','block_exacomp')."</a>");
	//pdf-Trennelemente <hr> entfernen
	
	$profile = str_replace ( '<hr>', '', $profile);
	//Userpic einfügen
	$profile = str_replace ( '###USERPIC###', $OUTPUT->user_picture($USER,array("size"=>100)), $profile);
	echo $profile;
	$graph= block_exacomp_create_radargraph();
	echo $graph;
	echo '</div>';// id=exabis_competences_block opend in header
	echo $OUTPUT->footer();
}
