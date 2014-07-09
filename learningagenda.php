<?php

/* * *************************************************************
 *  Copyright notice
*
*  (c) 2014 exabis internet solutions <info@exabis.at>
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

require_once dirname(__FILE__)."/inc.php";

global $DB, $OUTPUT, $PAGE;

$courseid = required_param('courseid', PARAM_INT);

/* ADDITIONAL PARAMS FOR LEARNING AGENDA */
$subjectid = optional_param('subjectid', 0, PARAM_INT);
$action = optional_param('action', 0, PARAM_INT);
$calendarinput = optional_param('calendarinput', 'none', PARAM_TEXT);
$studentid=optional_param('studentid', 0, PARAM_INT);
$print = optional_param('print', 0, PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_simplehtml', $courseid);
}

require_login($course);

$context = context_course::instance($courseid);

$page_identifier = 'tab_learning_agenda';

$PAGE->set_url('/blocks/exacomp/learningagenda.php', array('courseid' => $courseid));
$PAGE->set_heading(get_string('pluginname', 'block_exacomp'));

// build breadcrumbs navigation
$coursenode = $PAGE->navigation->find($courseid, navigation_node::TYPE_COURSE);
$blocknode = $coursenode->add(get_string('pluginname','block_exacomp'));
$pagenode = $blocknode->add(get_string($page_identifier,'block_exacomp'), $PAGE->url);
$pagenode->make_active();

/* ADDITIONAL FOR LEARNINGAGENDA
 * check if teacher or student, if teacher: display student selection
 */
if (has_capability('block/exacomp:teacher', $context)) {
	$role = "teacher";
	//lib function
	$stundentselect = block_exacomp_studentselector(get_role_users(5, $context),$studentid,$PAGE->url);
} else {
	$role = "student";
	$stundentselect="";
	$studentid=$USER->id;
}

/*ADDITIONAL FOR LEARNINGAGENDA
 * print header only if learningagenda is not viewed in print version
 */
if($print == 0){
	// build tab navigation & print header
	echo $OUTPUT->header();
	echo $OUTPUT->tabtree(block_exacomp_build_navigation_tabs($context,$courseid), $page_identifier);
}

/*ADDITIIONAL FOR LEARNINGAGENDA
 * calculate current week
 */
$week = $action * 604800;

/*ADDITIONAL FOR LEARNINGAGENDA
 * if date is selecte by calendar, timestamp is calculated 
 */
if(strcmp($calendarinput, 'none')!=0){
	$datecal =  explode ("-", $calendarinput);
	$timecal = mktime(0,0,0,$datecal[1],$datecal[2],$datecal[0]); 
}

/* CONTENT REGION */

$sql="SELECT concat( tmm.id, '_', emm.id ) AS id, subj.title AS subject, subj.number as subnumb, st.title AS schooltype, examp.title AS example, examp.task AS exampletask, examp.externalurl AS exampleurl, cat.title as cat,
exameval.starttime as start, exameval.endtime as end, exameval.student_evaluation as evaluate, exameval.teacher_evaluation as tevaluate, descr.title
FROM {block_exacompsubjects} subj
INNER JOIN {block_exacomptopics} top ON top.subjid = subj.id
INNER JOIN {block_exacompdescrtopic_mm} tmm ON tmm.topicid = top.id
INNER JOIN {block_exacompdescriptors} descr ON descr.id = tmm.descrid
INNER JOIN {block_exacompdescrexamp_mm} emm ON emm.descrid = descr.id
INNER JOIN {block_exacompexamples} examp ON examp.id = emm.exampid
INNER JOIN {block_exacompexameval} exameval ON exameval.exampleid = emm.exampid
LEFT JOIN {block_exacompcategories} cat ON top.catid = cat.id
LEFT JOIN {block_exacompschooltypes} st ON st.id = subj.stid 
WHERE exameval.studentid =?";

$rows = $DB->get_records_sql($sql, array($studentid));

$results = array();	
foreach($rows as $row){
	if(isset($row->start))
		$results[] = $row;
}

//if date is selected by calendar use this time 
if(isset($timecal)) $time = $timecal;
//if current date is used, add $week to browse through the weeks
else $time = time()+$week;

$days = array('MO','DI','MI','DO','FR');

//calculate dates of whole week from current day
$actday = date('w', $time);
if($actday == -1) $actday = 6;
$temps = array(-1+$actday,-2+$actday,-3+$actday,-4+$actday,-5+$actday);

$dates = array();
foreach($temps as $temp){
	$dates[] = getdate( $time-$temp*86400);
}

//convert the dates to timestamps
$weekdays = array();
for($i = 0; $i <5; $i++){
	$boxdate = mktime(0,0,0, $dates[$i]['mon'], $dates[$i]['mday'], $dates[$i]['year']);
	$weekdays[get_string($days[$i], 'block_exacomp')] = date($boxdate);
}
//in $weekdays all timestamps from actual week are stored now
//add subjects, examples and descriptors to each day
$data = array();
$example_count_week = 0;
$example_count = $example_count_week;
foreach($weekdays as $weekday=>$tag){
	foreach($results as $result){
		$example = new stdClass();
		
		if($result->start == $tag || !isset($result->end))	{
			$example_count_week++;
			$example->title = $result->example;
			$example->task = $result->exampletask;
			$example->externalurl = $result->exampleurl;
			$example->desc = $result->title;
			$example->evaluate = $result->evaluate;
			$example->numb = $result->subnumb;
			$example->schooltype = substr($result->schooltype,0,1);
			$example->cat = $result->cat;
			$example->enddate = $result->end;
			if(isset($result->tevaluate)){
				$example->tevaluate = $result->tevaluate;
			}else{
				$example->tevaluate = 0;
			}
			$data[$weekday][$result->subject][] = $example;
		}
		$data[$weekday]['date'] =  strftime("%d. %b %Y", $tag);
	}
	
	//check if examples available
	if($example_count_week == 0)
		$data[$weekday]["no example available"][] = new stdClass();
	
	$example_count += $example_count_week;
	$example_count_week = 0;	
}
$output = $PAGE->get_renderer('block_exacomp');	
if($print == 0){
	echo $output->form_week_learningagenda($stundentselect,$action, $studentid, $print,date('Y-m-d',$time));
	
	//check if any examples available
	if($example_count!=0)	
		echo $output->render_learning_agenda($data, $weekdays);
	else { //no examples
		if($studentid == 0) //no studentid -> choose student
			echo '<h2>'.get_string('schueler_waehlen', 'block_exacomp').'</h2>';
		else 
			echo '<h2>'.get_string('keine_lernagenden', 'block_exacomp').'</h2>';
	} 
		
}else{
	$user = $DB->get_record('user', array('id'=>$studentid)); 
	$content =  $output->print_view_learning_agenda($data, $user->firstname.' '.$user->lastname);
	
	require_once($CFG->dirroot.'/lib/tcpdf/tcpdf.php');
	try
	{	// create new PDF document
		$pdf = new TCPDF("P", "pt", "A4", true, 'UTF-8', false);
		$pdf->AddPage();
		$pdf->SetTitle('Lernagenda');
		$pdf->setCellHeightRatio(1.25);
		$pdf->writeHTML($content, true, false, true, false, '');
		$pdf->Output('Lernagenda.pdf', 'I');
	}
	catch(tcpdf_exception $e) {
		echo $e;
		exit;
	}
}
/* END CONTENT REGION */

echo $OUTPUT->footer();

?>






