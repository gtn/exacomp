<?php
/*
 * copyright exabis
 */

require __DIR__.'/inc.php';
require_once('lib/lib.php');
require_once('classes/data.php');

$courseid = required_param('courseid', PARAM_INT);
$schooltypes = explode(',', required_param('schooltypes', PARAM_TAGLIST));
$xmlname = required_param('xmlname', PARAM_URL);
$lang = optional_param('lang', 'en', PARAM_TEXT);

global $DB;

$data = file_get_contents($xmlname);
$success = block_exacomp\data_importer::do_import_string($data);
foreach($schooltypes as &$schooltype)
	$schooltype = $DB->get_field('block_exacompschooltypes','id',array('sourceid'=>$schooltype));

if($lang == "en") {
	$schooltypes[] = 1; //Social Competencies and Personal Competencies
	$schooltypes[] = 2;
}
else {
	$schooltypes[] = 3; //Soziale Kompetenzen, Personale Kompetenzen
	$schooltypes[] = 4;
}

block_exacomp_set_mdltype($schooltypes,$courseid);

$subjects = block_exacomp_get_subjects_for_schooltype($courseid);
$coursetopics = array();
foreach($subjects as $subject) {
	$topics = block_exacomp_get_all_topics($subject->id);
	foreach($topics as $topic)
		$coursetopics[] = $topic->id;
}
block_exacomp_set_coursetopics($courseid,$coursetopics);
