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

global $COURSE, $CFG, $OUTPUT, $USER;

$courseid = required_param('courseid', PARAM_INT);
$subjectid = optional_param('subjectid', 0, PARAM_INT);
$mode = optional_param('mode', 'normal', PARAM_TEXT);

require_login($courseid);

$context = get_context_instance(CONTEXT_COURSE, $courseid);
require_capability('block/exacomp:teacher', $context);

$url = '/blocks/exacomp/competence_grid.php?courseid=' . $courseid;
$PAGE->set_url($url);
$PAGE->requires->css('/blocks/exacomp/styles.css');

block_exacomp_print_header("teacher", "teachertabcompetencegrid");

$version = get_config('exacomp', 'alternativedatamodel');

if(!$version) {
	$subjects = $DB->get_records_sql_menu('
			SELECT s.id, s.title
			FROM {block_exacompsubjects} s
			JOIN {block_exacomptopics} t ON t.subjid = s.id
			JOIN {block_exacompcoutopi_mm} ct ON ct.topicid = t.id AND ct.courseid = ?

			GROUP BY s.id
			ORDER BY s.title
			', array($courseid));
} else {
	$subjects = $DB->get_records_sql_menu('
			SELECT s.id, s.title
			FROM {block_exacompschooltypes} s
			JOIN {block_exacompmdltype_mm} m ON m.typeid = s.id AND m.courseid = ?
			GROUP BY s.id
			ORDER BY s.title
			', array($courseid));
}
// as default use the first subject from the dropdown list
if($subjects && $subjectid == 0)
	$subjectid = key($subjects);

if($mode == 'normal') {
	echo get_string("choosesubject","block_exacomp");
	echo html_writer::select($subjects, 'exacomp_competence_grid_select_subject',array($subjectid),null,
			array("onchange"=>"document.location.href='".$CFG->wwwroot.$url."&subjectid='+this.value;"));
}
?>
<div id="exabis_competences_block">
	<?php
	if($version && $mode == 'normal') {
		//niveaus = LF 1-6
		//skills = "Kompetenzbereiche"
		//topics = subjects
		$niveaus = array(1=>1,2=>2,3=>3,4=>4,5=>5,6=>6);
		$skills = array(1 => "Kompetenzbereiche");
		$subjects = $DB->get_records_menu('block_exacompsubjects',array("stid" => $subjectid),null,"id, title");
		$niveaus = $DB->get_records_menu('block_exacompcategories', array("lvl" => 4),null,"id,title");
		$data = array();

		// Arrange data in associative array for easier use
		foreach($subjects as $subjid => $subject) {
			$topics = $DB->get_records('block_exacomptopics',array("subjid"=>$subjid),"cat");
			foreach($topics as $topic) {
				if($topic->cat == 0) continue;
				$data[1][$subjid][$topic->cat][] = $topic;
			}
		}

		$selection = $DB->get_fieldset_select('block_exacompcoutopi_mm', 'topicid', ' courseid = ?',array($courseid));
		
		$output = $PAGE->get_renderer('block_exacomp');
		echo $output->render_competence_grid($niveaus, $skills, $subjects, $data, $selection, $courseid);
	// detail = 1 subject displayed with tax A, B, C info
	} else if($version && $mode == 'detail') {
		$topics = $DB->get_records('block_exacomptopics',array("subjid"=>$subjectid),"cat");
		$niveaus = $DB->get_records_menu('block_exacompcategories', array("lvl" => 4),null,"id,title");
		$selection = $DB->get_fieldset_select('block_exacompcoutopi_mm', 'topicid', ' courseid = ?',array($courseid));
		
		$output = $PAGE->get_renderer('block_exacomp');
		echo $output->render_tax_competence_grid($niveaus, $DB->get_record('block_exacompsubjects',array("id"=>$subjectid)),
				$topics, $selection, $courseid);
	// version for niveaus and normal exacomp usage
	} else {
		$output = $PAGE->get_renderer('block_exacomp');
		$niveaus = block_exacomp_get_niveaus_for_subject($subjectid);
		$skills = $DB->get_records_menu('block_exacompskills',null,null,"id, title");

		$descriptors = block_exacomp_get_descriptors_for_subject($subjectid);

		// Arrange data in associative array for easier use
		$topics = array();
		$data = array();
		foreach ($descriptors as $descriptor) {
			$data[$descriptor->skillid][$descriptor->topicid][$descriptor->niveauid][] = $descriptor;
			$topics[$descriptor->topicid] = $descriptor->topic;
		}

		unset($descriptors);

		echo $output->render_competence_grid($niveaus, $skills, $topics, $data);
	}
	?>
</div>
