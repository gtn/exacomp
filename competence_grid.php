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

require_login($courseid);

$context = get_context_instance(CONTEXT_COURSE, $courseid);
require_capability('block/exacomp:teacher', $context);

$url = '/blocks/exacomp/competence_grid.php?courseid=' . $courseid;
$PAGE->set_url($url);
$PAGE->requires->css('/blocks/exacomp/styles.css');

block_exacomp_print_header("teacher", "teachertabcompetencegrid");

$subjects = $DB->get_records_sql_menu('
		SELECT s.id, s.title
		FROM {block_exacompsubjects} s
		JOIN {block_exacomptopics} t ON t.subjid = s.id
		JOIN {block_exacompcoutopi_mm} ct ON ct.topicid = t.id AND ct.courseid = ?

		GROUP BY s.id
		ORDER BY s.title
		', array($courseid));

// as default use the first subject from the dropdown list
if($subjects && $subjectid == 0)
	$subjectid = key($subjects);

echo get_string("choosesubject","block_exacomp");
echo html_writer::select($subjects, 'exacomp_competence_grid_select_subject',array($subjectid),null,
		array("onchange"=>"document.location.href='".$CFG->wwwroot.$url."&subjectid='+this.value;"));

?>
<div id="exabis_competences_block">
	<?
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
	
	?>
	</div>