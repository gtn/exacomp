<?php

function block_exacomp_create_radargraph(){
	global $USER,$CFG;
	$content="";
$course_desriptors = block_exacomp_get_descriptors_of_all_courses(1);
$content.='<table style="empty-cells:hide;margin:0 auto;">';

foreach ($course_desriptors as $coures_descriptor) {
	if(!$coures_descriptor->descriptors)
		continue;
	$topics=array();
	$subjects=array();
	$content.='<tr><td colspan="2">'.$coures_descriptor->fullname.'</td></tr>';
	$numberStudents = intval(count(get_enrolled_users(get_context_instance(CONTEXT_COURSE, $coures_descriptor->id))));
	$numberStudents=$numberStudents-1; //den user selber abziehen, das sind jetzt die anderen studenten

	foreach ($coures_descriptor->descriptors as $descr){

		if (empty($topics[$descr->subjectid][$descr->topicid]["amount"])){
			$topics[$descr->subjectid][$descr->topicid]["amount"]=0;
			$topics[$descr->subjectid][$descr->topicid]["title"]=$descr->topic;
			$topics[$descr->subjectid][$descr->topicid]["descr"]="0";
			$subjects[$descr->subjectid]["title"]=$descr->subject;
		}
		$topics[$descr->subjectid][$descr->topicid]["amount"]++;
		$topics[$descr->subjectid][$descr->topicid]["descr"]=$topics[$descr->subjectid][$descr->topicid]["descr"].",".$descr->id;

	}

	$h=1;

	foreach ($topics as $skey=>$sval){
		foreach ($sval as $tkey=>$tval){
			$gradings=block_exacomp_get_usercompetences_topics($USER->id, 1, $coures_descriptor->id,0,$tval["descr"]);
			if ($tval["amount"]>0) $topics[$skey][$tkey]["person"]=ceil((($gradings->p/$tval["amount"])*100));
			else $topics[$skey][$tkey]["person"]=0;
			//$topics[$tkey]["personvalue"]=$gradings->p;
			if ($numberStudents>0) $topics[$skey][$tkey]["all"]=ceil(($gradings->a/($numberStudents*$tval["amount"]))*100);
			else $topics[$skey][$tkey]["all"]=0;
			//$topics[$tkey]["allvalue"]=$gradings->a;
			//$topics[$tkey]["allamount"]=$numberStudents;
			unset ($topics[$skey][$tkey]["amount"]);unset ($topics[$skey][$tkey]["descr"]);
				
		}
	}

	foreach($topics as $skey=>$sval){
		if(count($sval)>2){
			$varname="graphval".$skey;
			$USER->$varname=serialize($sval);
	
	
			if($h==1) {
				$content.='<tr>';
			}
			$content.='<td>'.$subjects[$skey]["title"];
			$content.='<br><img src=\''.$CFG->wwwroot.'/blocks/exacomp/phpgraph/radar.php?par='.$skey.'\'>';
			$content.='</td>';
			$h++;
			if($h==3) {
				$content.='</tr>';$h=1;
			}
			//unset($USER->$varname);
		}
	}


}
return $content;
}

?>