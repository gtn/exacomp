<?php

function block_exacomp_xml_insert_edulevel($value,$source) {
	global $DB;
	$new_value = new stdClass();
	$new_value->uid = (int)$value->uid;
	$new_value->title = (string)$value->title;
	$new_value->sorting = (int)$value->sorting;
	unset($value);
	$value = $new_value;
	
	$edulevel = $DB->get_record('block_exacompedulevels', array("sourceid" => (int)$value->uid,"source"=>$source));
	if ($edulevel) {
		//update
		$edulevel->title = $value->title;
		$edulevel->sorting = $value->sorting;
		$DB->update_record('block_exacompedulevels', $edulevel);
	} else {
		$value->source = $source;
		$value->sourceid = $value->uid;
		$DB->insert_record('block_exacompedulevels', $value);
	}

}

function block_exacomp_xml_insert_schooltyp($value,$source,$userlst) {
	global $DB;
	$newvalue=new stdClass();
	$newvalue->title=(string)$value->title;
	$newvalue->sourceid=(int)$value->uid;
	$newvalue->source=$source;
	$newvalue->sorting = (int)$value->sorting;
	$newvalue->isoez = (int)$value->isoez;
	$newvalue->elid = (int)$value->elid;
	$newvalue->uid = (int)$value->uid;
	unset($value);
	$value = $newvalue;
	$edulevel = $DB->get_record('block_exacompedulevels', array("sourceid"=> (int)$value->elid,"source"=>$source));
	if (!empty($edulevel->id)) $elid=$edulevel->id;
	else $elid=0;
	$newvalue->elid=$elid;
	$schooltype = $DB->get_record('block_exacompschooltypes', array("sourceid" => (int)$value->uid,"source"=>$source));
	if ($schooltype) {
		//update
		$schooltype->title = $value->title;
		$schooltype->elid = $elid;
		$schooltype->sorting = $value->sorting;
		$schooltype->isoez = $value->isoez;

		$DB->update_record('block_exacompschooltypes', $schooltype);

	} else {
		$value->elid = $elid;
		$value->source = $source;
		$DB->insert_record('block_exacompsubjects', $value);
		
		$sql='INSERT INTO {block_exacompschooltypes} (sorting,title,elid,isoez,sourceid,source) VALUES('.$value->sorting.',\''.$value->title.'\','.$elid.','.$value->isoez.','.$value->uid.','.$source.')';
		$DB->Execute($sql);
	}
	if ($userlst!="0"){
			
		/*gibts diese kategorie bei irgendeinem user? wenn ja, update, wenn nein insert
		  hats sich jemand die kategorie gelöscht, wird sie nicht neu angelegt, sonst bevormundung*/
		//$daten=New stdClass;$daten->pid=0;$daten->name=$value->title;
		if ($cats = $DB->get_records_sql('SELECT * FROM {block_exaportcate} WHERE source=? AND sourceid=? AND userid IN ('.$userlst.')',array($source,(int)$value->uid))){
			//$sql='Update {block_exaportcate} SET pid="0", name="'.$value->title.'" WHERE userid IN ('.$userlst.')' AND source='.source.' AND sourceid='..';
			//$DB->Execute($sql);
		}else{
			//insert
		}
		
		
		  
		/*if ($cat1 = $DB->get_records('block_exaportcate', array("sourceid"=> (int)$value->uid,"source"=>$source))){
			foreach($cat1 as $category){
				$cat1ids.=",".$category->id;
			}
		}
		else $cat1ids=0;*/
	}
}

function block_exacomp_xml_insert_subject($value,$source) {
	global $DB;
	$data=array();
	$data["title"]=strval($value->title);
	$data["titleshort"]=strval($value->titleshort);
	$data["sourceid"]=intval($value->uid);
	$data["source"]=$source;
	$data["sorting"]=intval($value->sorting);
	if ($schooltype = $DB->get_record('block_exacompschooltypes', array("sourceid"=>intval($value->stid),"source"=>$source))) $stid=$schooltype->id;
	else $stid=0;
	$data["stid"]=$stid;
	$uid=intval($value->uid);
	if($subject = $DB->get_record('block_exacompsubjects', array("sourceid" =>$uid,"source"=>$source))){
		$data["id"]=$subject->id;
		$DB->update_record('block_exacompsubjects', $data);
		/*if ($exaport==true){
			$cats = $DB->get_record('block_exaportcate', array("sourceid"=> (int)$value->uid,"source"=>$source));
			$data=stdClass();
			$data->name=$value->title;
			foreach ($cats as $cat){
				$data->id=$cat->id;
				$DB->update_record('block_exaportcate', $data);
			}
		}*/
	} else {
		$DB->insert_record('block_exacompsubjects', $data);
		
		
		/*$sql='INSERT INTO {block_exacompsubjects} (sorting,title,titleshort,stid,sourceid,source) VALUES('.$value->sorting.',\''.($value->title).'\',\''.($value->uid).'\','.$stid.','.$value->uid.','.$source.')';
		echo $sql;
		$DB->Execute($sql);
		$datei="";
		show_sql_error($sql,$zeile="",$datei);*/
		/*if ($exaport==true){
			$sql='INSERT INTO {block_exaportcate} (sorting,title,titleshort,stid,sourceid,source) VALUES('.$value->sorting.',\''.$value->title.'\',\''.$value->titleshort.'\','.$stid.','.$value->uid.','.$source.')';
			$DB->Execute($sql);
		}*/
	}
}
function block_exacomp_xml_insert_skill($value,$source) {

	global $DB;
	/*$skill = $DB->get_record('block_exacompskills', array("sourceid" => (int)$value->uid,"source"=>$source));
	if ($skill) {
		//update
		$skill->title = $value->title;
		$skill->sorting = $value->sorting;
		$DB->update_record('block_exacompskills', $skill);
		
	} else {*/
		$sql='INSERT INTO {block_exacompskills} (sorting,title) VALUES('.$value->sorting.',\''.$value->title.'\')';
		$DB->Execute($sql);
	//}
}

function block_exacomp_xml_insert_taxonomie($value,$source) {
	global $DB;


	/*
	 * ID aus XML mit sourceID der Datenbank vergleichen.
	* Falls gefunden, update
	* Falls nicht, insert
	*
	*/
		global $DB;


	/*
	 * ID aus XML mit sourceID der Datenbank vergleichen.
	* Falls gefunden, update
	* Falls nicht, insert
	*
	*/
	
	$new_value = new stdClass();
	$new_value->uid = (int)$value->uid;
	$new_value->title = (string)$value->title;
	$new_value->sorting = (int)$value->sorting;
	$new_value->parent_tax = (int)$value->parent_tax;
	unset($value);
	$value = $new_value;
	
	$tax = $DB->get_record('block_exacomptaxonomies', array("sourceid" => $value->uid));
	if ($tax) {
		//update
		$tax->title = $value->title;
		$DB->update_record('block_exacompexamples', $tax);
	} else {
		//insert
		$value->sourceid = $value->uid;
		$value->parentid = $value->parent_tax;

		$sql='INSERT INTO {block_exacomptaxonomies} (id,sorting,title,sourceid) VALUES('.$value->uid.','.$value->sorting.',\''.$value->title.'\','.$value->uid.')';
		$DB->Execute($sql);

	}
	/*new version mit source
	$new_value = new stdClass();
	$new_value->uid = (int)$value->uid;
	$new_value->title = (string)$value->title;
	$new_value->sorting = (int)$value->sorting;
	$new_value->parent_tax = (int)$value->parent_tax;
	unset($value);
	$value = $new_value;
	
	$parenttax = $DB->get_record('block_exacomptaxonomies', array("sourceid" => $value->uid,"source"=>$source));
	if (!empty($parenttax->id)) $ptax=$parenttax->id;
	else $ptax=0;
	
	$tax = $DB->get_record('block_exacomptaxonomies', array("sourceid" => $value->parent_tax,"source"=>$source));
	if ($tax) {
		//update
		$tax->title = $value->title;
		$tax->parentid =$ptax;
		$DB->update_record('block_exacompexamples', $tax);
	} else {
		//insert
		$value->sourceid = $value->uid;
		$value->parentid = $value->parent_tax;

		$sql='INSERT INTO {block_exacomptaxonomies} (sorting,title,sourceid,source,parentid) VALUES('.$value->sorting.',\''.$value->title.'\','.$value->uid.','.$source.','.$value->parent_tax.')';
		$DB->Execute($sql);

	}*/
}

function block_exacomp_xml_insert_topic($value,$source) {
	global $DB;
	/*
	 * ID aus XML mit sourceID der Datenbank vergleichen.
	* Falls gefunden, update
	* Falls nicht, insert
	*
	*/

	$new_value = new stdClass();
	$new_value->subjid = (int)$value->subjid;
	$new_value->uid = (int)$value->uid;
	$new_value->title = (string)$value->title;
	$new_value->titleshort = (string)$value->titleshort;
	$new_value->sorting = (int)$value->sorting;
	$new_value->description = (string)$value->description;
	unset($value);
	$value = $new_value;
	
	// Subject ID wird benštigt, durch sourceid holen
	//echo (int)$value->subjid." ".$source;
	$subject = $DB->get_record('block_exacompsubjects', array("sourceid"=> (int)$value->subjid,"source"=>$source));
	if (!empty($subject->id)) $subj=$subject->id;
	else $subj=0;
	$topic = $DB->get_record('block_exacomptopics', array("sourceid" => (int)$value->uid,"source"=>$source));

	if ($topic) {
		//update
		$topic->title = $value->title;
		$topic->titleshort = $value->titleshort;
		$topic->subjid = $subj;
		$topic->sorting = $value->sorting;
		$topic->description = $value->description;
		$DB->update_record('block_exacomptopics', $topic);
	} else {
		//insert
		$value->sourceid = $value->uid;
		$value->subjid = $subj;
		$value->source = $source;
		$DB->insert_record('block_exacomptopics', $new_value);
	}
}

function block_exacomp_xml_insert_descriptor($value,$source) {
	global $DB;
	/*
	 * ID aus XML mit sourceID der Datenbank vergleichen.
	* Falls gefunden, update
	* Falls nicht, insert
	*
	*/
	$new_value = new stdClass();
	$new_value->uid = (int)$value->uid;
	$new_value->title = (string)$value->title;
	$new_value->niveauid = (int)$value->niveauid;
	$new_value->sorting = (int)$value->sorting;
	$new_value->skillid = (int)$value->skillid;
	$new_value->parent_id = (int)$value->parent_id;
	
	unset($value);
	$value = $new_value;
	
	$desc = $DB->get_record('block_exacompdescriptors', array("sourceid" => $value->uid,"source"=>$source));
	if ($desc) {
		//update
		$desc->title = (string)$value->title;
		$desc->skillid = (int)$value->skillid;
		$desc->niveauid = (int)$value->niveauid;
		$desc->sorting = (int)$value->sorting;

		$DB->update_record('block_exacompdescriptors', $desc);
	} else {
		//insert
		$value->sourceid = $value->uid;
		$value->source = $source;
		$DB->insert_record('block_exacompdescriptors', $value);
	}
}

function block_exacomp_xml_insert_example($value,$source) {
	global $DB;
	/*
	 * ID aus XML mit sourceID der Datenbank vergleichen.
	* Falls gefunden, update
	* Falls nicht, insert
	*
	*/
	$new_value = new stdClass();
	$new_value->uid = (int)$value->uid;
	$new_value->title = (string)$value->title;
		$new_value->titleshort = (string)$value->titleshort;
	$new_value->task = (string)$value->task;
	$new_value->solution = (string)$value->solution;
	$new_value->attachement = (string)$value->attachement;
	$new_value->completefile = (string)$value->completefile;
	$new_value->description = (string)$value->description;
	$new_value->taxid = (int)$value->taxid;
	$new_value->timeframe = (string)$value->timeframe;
	$new_value->ressources = (string)$value->ressources;
	$new_value->tips = (string)$value->tips;
	$new_value->externalurl = (string)$value->externalurl;
	$new_value->externalsolution = (string)$value->externalsolution;
	$new_value->externaltask = (string)$value->externaltask;
	$new_value->sorting = (int)$value->sorting;
	$new_value->lang = (int)$value->lang;
	$new_value->iseditable = (int)$value->iseditable;
	$new_value->source = $source;
	unset($value);
	$value = $new_value;
	$example = $DB->get_record('block_exacompexamples', array("sourceid" => (int)$value->uid,"source"=>$source));
	if ($example) {
		//update
		$example->title = (string)$value->title;
		$example->titleshort = (string)$value->titleshort;
		$example->task = (string)$value->task;
		$example->solution = (string)$value->solution;
		$example->attachement = (string)$value->attachement;
		$example->completefile = (string)$value->completefile;
		$example->description = (string)$value->description;
		$example->taxid = (int)$value->taxid;
		$example->timeframe = (string)$value->timeframe;
		$example->ressources = (string)$value->ressources;
		$example->tips = (string)$value->tips;
		$example->externalurl = (string)$value->externalurl;
		$example->externalsolution = (string)$value->externalsolution;
		$example->externaltask = (string)$value->externaltask;
		$example->sorting = (int)$value->sorting;
		$example->lang = (int)$value->lang;
		$example->iseditable = (int)$value->iseditable;
		$DB->update_record('block_exacompexamples', $example);
	} else {
		//insert
		$value->sourceid = $value->uid;
		$value->source = $source;
		$DB->insert_record('block_exacompexamples', $value);
	}
}

function block_exacomp_xml_truncate($tablename) {
	global $DB;
	$DB->delete_records($tablename);
}

/* this function deletes all categories if there are no subcategories
i.e. if there are no topics to a subject, the subject can be deleted*/
function block_exacomp_deleteIfNoSubcategories($parenttable,$subtable,$subforeignfield,$source) {
	global $DB;
	$sql='SELECT * FROM {'.$parenttable.'} pt WHERE source=? AND id NOT IN(Select '.$subforeignfield.' FROM {'.$subtable.'} WHERE source=? AND '.$subforeignfield.'=pt.id)';
	$todelets = $DB->get_records_sql($sql,array($source,$source));
	foreach ($todelets as $todelete) {
		//echo "delete ".$parenttable." id=".$todelete->id."<br>";
		$DB->delete_records($parenttable, array("id" => $todelete->id));
	}
}
function block_exacomp_xml_get_topics($source) {
	global $DB;
	return $DB->get_records('block_exacomptopics',array("source"=>$source));
}

function block_exacomp_xml_get_descriptors($source) {
	global $DB;
	return $DB->get_records('block_exacompdescriptors',array("source"=>$source));
}

function block_exacomp_xml_get_examples($source) {
	global $DB;
	return $DB->get_records('block_exacompexamples',array("source"=>$source));
}

function block_exacomp_xml_find_unused($values, $xml, $tablename) {
	global $DB;
	$founds = array();
	foreach ($values as $value) {
		$occur = false;
		foreach ($xml->table as $table) {
			$name = $table->attributes()->name;
			if ($name == $tablename) {
				if ($table->uid == $value->sourceid)
					$occur = true;
			}
		}
		// if !occur && source == zentraler Server
		if (!$occur)
			$founds[] = $value->sourceid;
	}
	return $founds;
}

function block_exacomp_xml_delete_unused_topics($founds,$source) {
	global $DB;
	foreach ($founds as $found) {
		$query = "SELECT * FROM {block_exacomptopics} t, {block_exacompdescrtopic_mm} dt WHERE dt.topicid = t.id and t.sourceid = " . $found . " and t.source = ".$source;
		$occur = $DB->get_records_sql($query);
		if (!$occur)
			$DB->delete_records('block_exacomptopics', array("sourceid" => $found));
	}
}

function block_exacomp_xml_delete_unused_descriptors($founds,$source) {
	global $DB;
	foreach ($founds as $found) {
		$query = "SELECT * FROM {block_exacompdescriptors} d, {block_exacompdescrtopic_mm} dt WHERE dt.descrid = d.id and d.sourceid = " . $found . " and d.source = ".$source;
		$occur = $DB->get_records_sql($query);
		if (!$occur)
			$DB->delete_records('block_exacompdescriptors', array("sourceid" => $found));
	}
}

function block_exacomp_xml_delete_unused_examples($founds,$source) {
	global $DB;
	foreach ($founds as $found) {
		$query = "SELECT * FROM {block_exacompexamples} e, {block_exacompdescrexamp_mm} de WHERE de.exampid = e.id and e.sourceid = " . $found . " and e.source = ".$source;
		$occur = $DB->get_records_sql($query);
		if (!$occur)
			$DB->delete_records('block_exacompexamples', array("sourceid" => $found));
	}
}

function block_exacomp_xml_get_current_ids($table, $tablename, $source) {
	global $DB;
	$value = array();
	if ($tablename == "topic")
		$topic = $DB->get_record('block_exacomptopics', array("sourceid" => (int)$table->topicid,"source"=>$source), "id");
	else
		$example = $DB->get_record('block_exacompexamples', array("sourceid" => (int)$table->exampid,"source"=>$source), "id");

	if($table->source == 1)
		$source = 1;
	
	$descr = $DB->get_record('block_exacompdescriptors', array("sourceid" => (int)$table->descrid,"source"=>$source), "id");

	if (!empty($topic) && !empty($descr)) {
		$value['topicid'] = $topic->id;
		$value['descrid'] = $descr->id;
		return $value;
	}
	if (!empty($example) && !empty($descr)) {
		$value['exampid'] = $example->id;
		$value['descrid'] = $descr->id;
		return $value;
	}
}

function block_exacomp_xml_delete_descrtopicmm($source) {
	global $DB;
	$query = "SELECT dt.id FROM {block_exacomptopics} t, {block_exacompdescriptors} d, {block_exacompdescrtopic_mm} dt WHERE t.id=dt.topicid AND d.id=dt.descrid AND t.sourceid IS NOT NULL AND d.sourceid IS NOT NULL AND d.source = ? AND t.source = ?";
	$assigns = $DB->get_records_sql($query,array($source,$source));

	foreach ($assigns as $assign) {
		$DB->delete_records('block_exacompdescrtopic_mm', array("id" => $assign->id));
	}
}

function block_exacomp_xml_delete_descrexampmm($source) {
	global $DB;
	$query = "SELECT de.id FROM {block_exacompexamples} e, {block_exacompdescriptors} d, {block_exacompdescrexamp_mm} de WHERE e.id=de.exampid AND d.id=de.descrid AND e.sourceid IS NOT NULL AND d.sourceid IS NOT NULL AND d.source = ? AND e.source = ?";
	$assigns = $DB->get_records_sql($query,array($source,$source));

	foreach ($assigns as $assign) {
		$DB->delete_records('block_exacompdescrexamp_mm', array("id" => $assign->id));
	}
}

function block_exacomp_xml_insert_descrexampmm($descrexamples) {
	global $DB;

	foreach ($descrexamples as $descrexample) {
		$data = new stdClass();
		//echo $descrexample['descrid']."   ".$descrexample['exampid']."<hr>";
		$data->exampid = $descrexample['exampid'];
		$data->descrid = $descrexample['descrid'];
		$DB->insert_record('block_exacompdescrexamp_mm', $data);
	}
	//die;
}
function block_exacomp_xml_insert_descrtopicmm($descrtopics) {
	global $DB;

	foreach ($descrtopics as $descrtopic) {
		$data = new stdClass();

		$data->topicid = $descrtopic['topicid'];
		$data->descrid = $descrtopic['descrid'];
		$DB->insert_record('block_exacompdescrtopic_mm', $data);
	}
}

function block_exacomp_xml_do_import($file = null, $source = 1) {
	
	global $DB,$CFG;
	$filename = 'xml/exacomp_data.xml';
	$schritt = optional_param('schr', 0, PARAM_INT);

	$edulevel = 0;
	$schooltyp = 0;
	$subject = 0;
	$topic = 0;
	$skill = 0;
	$tax = 0;
	$descrtopic = array();
	$descrexamp = array();

	if (file_exists($filename) || $file) {
		
		$xml = (!$file) ? simplexml_load_file($filename) : simplexml_load_string($file);
		if ($xml) {
			$STARTZEIT = time();

		
			$userlst=0;
			/*$exaport=has_exaport();
			if ($exaport==true){
				if ($users = $DB->get_records("block_exaportuser", array("oezinstall"=>1))){
					foreach ($users as $user){
						$userlst.=",".$user->id;
					}
					$userlst=preg_replace("/^,/","",$userlst);
				}
			}*/
			if (($schritt==0 && $source==1) || $source>1){
				foreach ($xml->table as $table) {
					$name = $table->attributes()->name;
	
					if ($name == "block_exacompedulevels") {
						if ($edulevel == 0) {
							//block_exacomp_xml_truncate($table->attributes()->name);
							$edulevel = 1;
						}
						block_exacomp_xml_insert_edulevel($table,$source);
					}
					if ($name == "block_exacompschooltypes") {
						/*if ($schooltyp == 0) {
							block_exacomp_xml_truncate($name);
							$schooltyp = 1;
						}*/
						block_exacomp_xml_insert_schooltyp($table,$source,$userlst);
					}
					if ($name == "block_exacompsubjects") {
						
						/*if ($subject == 0) {
							$DB->delete_records('block_exacompsubjects',array("source" => $source));
							$subject = 1;
						}*/
						block_exacomp_xml_insert_subject($table,$source);
					}
					if ($name == "block_exacompskills" && $source==1) {
						if ($skill == 0) {
							block_exacomp_xml_truncate($name);
							$skill = 1;
						}
						block_exacomp_xml_insert_skill($table,$source);
					}
					if ($name == "block_exacomptaxonomies" && $source==1) {
						if ($tax == 0) {
							block_exacomp_xml_truncate($name);
							$tax = 1;
						}
						block_exacomp_xml_insert_taxonomie($table,$source);
					}
					if ($name == "block_exacomptopics") {
						block_exacomp_xml_insert_topic($table,$source);
					}
					if ($name == "block_exacompdescriptors") {
						block_exacomp_xml_insert_descriptor($table,$source);
					}

					if ($name == "block_exacompexamples") {
						block_exacomp_xml_insert_example($table,$source);
					}
					if ($name == "block_exacompdescrtopic_mm") {
						$descrtopicmm = block_exacomp_xml_get_current_ids($table, "topic",$source);
						if (!empty($descrtopicmm['descrid']) && !empty($descrtopicmm['topicid']))
							$descrtopic[] = $descrtopicmm;
					}
					if ($name == "block_exacompdescrexamp_mm") {
						$descrexampmm = block_exacomp_xml_get_current_ids($table, "example",$source);
	
						if (!empty($descrexampmm['descrid']) && !empty($descrexampmm['exampid']))
							$descrexamp[] = $descrexampmm;
					}
				}

				block_exacomp_xml_delete_descrtopicmm($source); 
				block_exacomp_xml_insert_descrtopicmm($descrtopic);
	
				block_exacomp_xml_delete_descrexampmm($source); 
				block_exacomp_xml_insert_descrexampmm($descrexamp);

			
				if ($source==1){
					$courseid=optional_param('courseid', 0, PARAM_INT);
					redirect($CFG->wwwroot.'/blocks/exacomp/import.php?action=xml&courseid='.$courseid.'&schr=1');
					die;
				}
			}
			if (($schritt=="1" && $source=="1") || $source>1){
				$STARTZEIT = time();
			//block_exacomp_deleteIfNoSubcategories("block_exacomptopics","block_exacompdescrtopic_mm","topicid",$source);
				block_exacomp_deleteIfNoSubcategories("block_exacompsubjects","block_exacomptopics","subjid",$source);
				block_exacomp_deleteIfNoSubcategories("block_exacompschooltypes","block_exacompsubjects","stid",$source);
				block_exacomp_deleteIfNoSubcategories("block_exacompedulevels","block_exacompschooltypes","elid",$source);
	
				
				$topics = block_exacomp_xml_get_topics($source);
				$founds = block_exacomp_xml_find_unused($topics, $xml, "block_exacomptopics");
				block_exacomp_xml_delete_unused_topics($founds,$source);
	
				$descs = block_exacomp_xml_get_descriptors($source);
				$founds = block_exacomp_xml_find_unused($descs, $xml, "block_exacompdescriptors");
				block_exacomp_xml_delete_unused_descriptors($founds,$source);
	
				$examples = block_exacomp_xml_get_examples($source);
				$founds = block_exacomp_xml_find_unused($examples, $xml, "block_exacompexamples");
				block_exacomp_xml_delete_unused_examples($founds,$source);

			}
		}
		return true;
	}
	return false;
}

function block_exacomp_xml_check_import() {
	global $DB;
	$check = $DB->get_records('block_exacompdescriptors');
	if ($check)
		return true;
	else
		return false;
}
function has_exaport(){
	global $DB;
	$all_tables = $DB->get_tables();
	
	//achtung dossier aus exaport derzeit nicht eingebunden, bei aktivierung $exaport=false 6 zeilen weiter unten löschen;
	if (in_array("block_exaportview", $all_tables)) {
		$exaport=true;
	}else{
		$exaport=false;
	}
	return $exaport;
}
function show_sql_error($sql,$zeile="",$datei){
 	$err=mysql_error();
	if(!empty($err)){
		 echo "<p style='background-color:#f9cdcd;border:2px red solid;'>".$sql."<br>";
		 echo "SQL-Fehler: ".mysql_error(). " in ".$datei." Zeile ".$zeile."</p>";
	}
}
?>
