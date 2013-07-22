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
require_capability('block/exacomp:student', $context);

$url = '/blocks/exacomp/learningagenda.php?courseid=' . $courseid;
$PAGE->set_url($url);
$PAGE->requires->css('/blocks/exacomp/styles.css');

block_exacomp_print_header("student", "studenttabcompetencesagenda");

$sql="SELECT concat( descr.id, '_', exameval.id ) AS id, subj.title AS subject, examp.title AS example, 
exameval.starttime as start, exameval.endtime as end, exameval.student_evaluation as evaluate, exameval.teacher_evaluation as tevaluate, descr.title
FROM {block_exacompsubjects} subj
INNER JOIN {block_exacomptopics} top ON top.subjid = subj.id
INNER JOIN {block_exacompdescrtopic_mm} tmm ON tmm.topicid = top.id
INNER JOIN {block_exacompdescriptors} descr ON descr.id = tmm.descrid
INNER JOIN {block_exacompdescrexamp_mm} emm ON emm.descrid = descr.id
INNER JOIN {block_exacompexamples} examp ON examp.id = emm.exampid
INNER JOIN {block_exacompexameval} exameval ON exameval.exampleid = emm.exampid
WHERE exameval.studentid =".$USER->id;

$rows = $DB->get_records_sql($sql);

$results = array();	
foreach($rows as $row){
	if(isset($row->start))
		$results[] = $row;
}

$act = mktime(0,0,0,1,1,date("Y"));
$wtag = date('w',$act)-1;

$days = array('MO','DI','MI','DO','FR');
$wochentage = array();
for($i = 0; $i <5; $i++){
   $wtage = array(0,-1,-2,-3, -4);
   $tage  = (29)*7 + $wtage[$wtag];
   $ts= mktime(0,0,0,1,1+$tage+$i,2013);
   $datum = date($ts);
   $wochentage[get_string($days[$i], 'block_exacomp')] = $datum;
} 
$data = array();
foreach($wochentage as $wochentag=>$tag){
	foreach($results as $result){
		if($result->start <= $tag && ($result->end >= $tag || !isset($result->end))){
			$example = new stdClass();
			$example->title = $result->example;
			$example->desc = $result->title;
			$example->evaluate = $result->evaluate;
			if(isset($result->tevaluate)){
				$example->tevaluate = $result->tevaluate;
			}else{
				$example->tevaluate = 0;
			}
			$data[$wochentag][$result->subject][] = $example;
		}
	}
}
?>
<div id="exabis_competences_block">
	<?
	$output = $PAGE->get_renderer('block_exacomp');
	echo $output->render_learning_agenda($data, $wochentage);
	?>
</div>