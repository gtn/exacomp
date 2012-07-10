<?php

function block_exacomp_xml_insert_edulevel($value) {
    global $DB;
    $sql='INSERT INTO {block_exacompedulevels} (id,sorting,title) VALUES('.$value->uid.','.$value->sorting.',"'.$value->title.'")';
  
    $DB->Execute($sql);

}

function block_exacomp_xml_insert_schooltyp($value) {
    global $DB;
    
    $value->id = $value->uid;

    $sql='INSERT INTO {block_exacompschooltypes} (id,sorting,title,elid,isoez) VALUES('.$value->uid.','.$value->sorting.',"'.$value->title.'",'.$value->elid.','.$value->isoez.')';
    $DB->Execute($sql);
            
}

function block_exacomp_xml_insert_subject($value) {
//    global $DB;
//    $DB->insert_record('block_exacompsubjects', $value);

    global $DB;
    /*
     * ID aus XML mit sourceID der Datenbank vergleichen.
     * Falls gefunden, update
     * Falls nicht, insert
     *
     */
    $subject = $DB->get_record('block_exacompsubjects', array("sourceid" => $value->uid));
    if ($subject) {
        //update
        $subject->title = $value->title;
        $subject->stid = $value->stid;

        $DB->update_record('block_exacompsubjects', $subject);
    } else {
        //insert
        
        $sql='INSERT INTO {block_exacompsubjects} (id,sorting,title,stid,sourceid) VALUES('.$value->uid.','.$value->sorting.',"'.$value->title.'",'.$value->stid.','.$value->uid.')';
    		$DB->Execute($sql);
    }
}

function block_exacomp_xml_insert_skill($value) {
    global $DB;
    $sql='INSERT INTO {block_exacompskills} (id,sorting,title) VALUES('.$value->uid.','.$value->sorting.',"'.$value->title.'")';
   	$DB->Execute($sql);

}

function block_exacomp_xml_insert_taxonomie($value) {
    global $DB;
    
	
	    /*
     * ID aus XML mit sourceID der Datenbank vergleichen.
     * Falls gefunden, update
     * Falls nicht, insert
     *
     */
    $tax = $DB->get_record('block_exacomptaxonomies', array("sourceid" => $value->uid));
    if ($tax) {
        //update
        $tax->title = $value->title;
				
				
				
				
		    $DB->update_record('block_exacompexamples', $tax);
    } else {
        //insert
        $value->sourceid = $value->uid;
				$value->parentid = $value->parent_tax;
				
				$sql='INSERT INTO {block_exacomptaxonomies} (id,sorting,title,sourceid) VALUES('.$value->uid.','.$value->sorting.',"'.$value->title.'",'.$value->uid.')';
    		$DB->Execute($sql);
    		
    }
}

function block_exacomp_xml_insert_topic($value) {
    global $DB;
    /*
     * ID aus XML mit sourceID der Datenbank vergleichen.
     * Falls gefunden, update
     * Falls nicht, insert
     *
     */

    // Subject ID wird benötigt, durch sourceid holen
    $subject = $DB->get_record('block_exacompsubjects', array("sourceid"=> $value->subjid));
		if (!empty($subject->id)) $subj=$subject->id;
		else $subj=0;
    $topic = $DB->get_record('block_exacomptopics', array("sourceid" => $value->uid));

    if ($topic) {
        //update
        $topic->title = $value->title;
        $topic->subjid = $subj;
				$topic->sorting = $value->sorting;
				$topic->description = $value->description;
        $DB->update_record('block_exacomptopics', $topic);
    } else {
        //insert
        $value->sourceid = $value->uid;
        $value->subjid = $subj;
        $DB->insert_record('block_exacomptopics', $value);
    }
}

function block_exacomp_xml_insert_descriptor($value) {
    global $DB;
    /*
     * ID aus XML mit sourceID der Datenbank vergleichen.
     * Falls gefunden, update
     * Falls nicht, insert
     *
     */

    $desc = $DB->get_record('block_exacompdescriptors', array("sourceid" => $value->uid));
    if ($desc) {
        //update
        $desc->title = $value->title;
        $desc->skillid = $value->skillid;
        $desc->niveauid = $value->niveauid;
        $desc->sorting = $value->sorting;

        $DB->update_record('block_exacompdescriptors', $desc);
    } else {
        //insert
        $value->sourceid = $value->uid;
        $DB->insert_record('block_exacompdescriptors', $value);
    }
}

function block_exacomp_xml_insert_example($value) {
    global $DB;
    /*
     * ID aus XML mit sourceID der Datenbank vergleichen.
     * Falls gefunden, update
     * Falls nicht, insert
     *
     */
    $example = $DB->get_record('block_exacompexamples', array("sourceid" => $value->uid));
    if ($example) {
        //update
        $example->title = $value->title;
        $example->task = $value->task;
        $example->solution = $value->solution;
        $example->attachement = $value->attachement;
        $example->completefile = $value->completefile;
        $example->description = $value->description;
        $example->taxid = $value->taxid;
        $example->timeframe = $value->timeframe;
        $example->ressources = $value->ressources;
        $example->tips = $value->tips;
        $example->externalurl = $value->externalurl;
        $example->externalsolution = $value->externalsolution;
        $example->externaltask = $value->externaltask;
        $example->sorting = $value->sorting;
        $DB->update_record('block_exacompexamples', $example);
    } else {
        //insert
        $value->sourceid = $value->uid;
        $DB->insert_record('block_exacompexamples', $value);
    }
}

function block_exacomp_xml_truncate($tablename) {
    global $DB;
    $DB->delete_records($tablename);
}

function block_exacomp_xml_get_topics() {
    global $DB;
    return $DB->get_records('block_exacomptopics');
}

function block_exacomp_xml_get_descriptors() {
    global $DB;
    return $DB->get_records('block_exacompdescriptors');
}

function block_exacomp_xml_get_examples() {
    global $DB;
    return $DB->get_records('block_exacompexamples');
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

function block_exacomp_xml_delete_unused_topics($founds) {
    global $DB;
    foreach ($founds as $found) {
        $query = "SELECT * FROM {block_exacomptopics} t, {block_exacompdescrtopic_mm} dt WHERE dt.topicid = t.id and t.sourceid = " . $found;
        $occur = $DB->get_records_sql($query);
        if (!$occur)
            $DB->delete_records('block_exacomptopics', array("sourceid" => $found));
    }
}

function block_exacomp_xml_delete_unused_descriptors($founds) {
    global $DB;
    foreach ($founds as $found) {
        $query = "SELECT * FROM {block_exacompdescriptors} d, {block_exacompdescrtopic_mm} dt WHERE dt.descrid = d.id and d.sourceid = " . $found;
        $occur = $DB->get_records_sql($query);
        if (!$occur)
            $DB->delete_records('block_exacompdescriptors', array("sourceid" => $found));
    }
}

function block_exacomp_xml_delete_unused_examples($founds) {
    global $DB;
    foreach ($founds as $found) {
        $query = "SELECT * FROM {block_exacompexamples} e, {block_exacompdescrexamp_mm} de WHERE de.exampid = e.id and e.sourceid = " . $found;
        $occur = $DB->get_records_sql($query);
        if (!$occur)
            $DB->delete_records('block_exacompexamples', array("sourceid" => $found));
    }
}

function block_exacomp_xml_get_current_ids($table, $tablename) {
    global $DB;
    $value = array();
    if ($tablename == "topic")
        $topic = $DB->get_record('block_exacomptopics', array("sourceid" => $table->topicid), "id");
    else
        $example = $DB->get_record('block_exacompexamples', array("sourceid" => $table->exampid), "id");

    $descr = $DB->get_record('block_exacompdescriptors', array("sourceid" => $table->descrid), "id");

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

function block_exacomp_xml_delete_descrtopicmm() {
    global $DB;
    $query = "SELECT dt.id FROM {block_exacomptopics} t, {block_exacompdescriptors} d, {block_exacompdescrtopic_mm} dt WHERE t.id=dt.topicid AND d.id=dt.descrid AND t.sourceid IS NOT NULL AND d.sourceid IS NOT NULL";
    $assigns = $DB->get_records_sql($query);

    foreach ($assigns as $assign) {
        $DB->delete_records('block_exacompdescrtopic_mm', array("id" => $assign->id));
    }
}

function block_exacomp_xml_delete_descrexampmm() {
    global $DB;
    $query = "SELECT de.id FROM {block_exacompexamples} e, {block_exacompdescriptors} d, {block_exacompdescrexamp_mm} de WHERE e.id=de.exampid AND d.id=de.descrid AND e.sourceid IS NOT NULL AND d.sourceid IS NOT NULL";
    $assigns = $DB->get_records_sql($query);

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

function block_exacomp_xml_do_import($file = null) {
	global $DB;
    $filename = 'xml/exacomp_data.xml';

    if($file == "desp")
        $filename = '../digitales_esp/xml/exacomp_data.xml';

    $edulevel = 0;
    $schooltyp = 0;
    $subject = 0;
    $topic = 0;
    $skill = 0;
    $tax = 0;
    $descrtopic = array();
    $descrexamp = array();

    if (file_exists($filename)) {
        $xml = simplexml_load_file($filename);
        if ($xml) {
            foreach ($xml->table as $table) {
                $name = $table->attributes()->name;

                if ($name == "block_exacompedulevels") {
                    if ($edulevel == 0) {
                        block_exacomp_xml_truncate($table->attributes()->name);
                        $edulevel = 1;
                    }
                    block_exacomp_xml_insert_edulevel($table);
                }
                if ($name == "block_exacompschooltypes") {
                    if ($schooltyp == 0) {
                        block_exacomp_xml_truncate($name);
                        $schooltyp = 1;
                    }
                    block_exacomp_xml_insert_schooltyp($table);
                }
                if ($name == "block_exacompsubjects") {
                    if ($subject == 0) {
                        block_exacomp_xml_truncate($name);
                        $subject = 1;
                    }
                    block_exacomp_xml_insert_subject($table);
                }
                if ($name == "block_exacompskills") {
                    if ($skill == 0) {
                        block_exacomp_xml_truncate($name);
                        $skill = 1;
                    }
                    block_exacomp_xml_insert_skill($table);
                }
                if ($name == "block_exacomptaxonomies") {
                    if ($tax == 0) {
                        block_exacomp_xml_truncate($name);
                        $tax = 1;
                    }
                    block_exacomp_xml_insert_taxonomie($table);
                }
                if ($name == "block_exacomptopics") {
                    block_exacomp_xml_insert_topic($table);
                }
                if ($name == "block_exacompdescriptors") {
                    block_exacomp_xml_insert_descriptor($table);
                }
                if ($name == "block_exacompexamples") {
                    block_exacomp_xml_insert_example($table);
                }
                if ($name == "block_exacompdescrtopic_mm") {
                    $descrtopicmm = block_exacomp_xml_get_current_ids($table, "topic");
                    
                    if (!empty($descrtopicmm['descrid']) && !empty($descrtopicmm['topicid']))
                        $descrtopic[] = $descrtopicmm;
                }
                if ($name == "block_exacompdescrexamp_mm") {
                    $descrexampmm = block_exacomp_xml_get_current_ids($table, "example");
                    
                    if (!empty($descrexampmm['descrid']) && !empty($descrexampmm['exampid']))
                        $descrexamp[] = $descrexampmm;
                }
            }
            
            
            $topics = block_exacomp_xml_get_topics();
            $founds = block_exacomp_xml_find_unused($topics, $xml, "block_exacomptopics");
            block_exacomp_xml_delete_unused_topics($founds);

            $descs = block_exacomp_xml_get_descriptors();
            $founds = block_exacomp_xml_find_unused($descs, $xml, "block_exacompdescriptors");
            block_exacomp_xml_delete_unused_descriptors($founds);

            $examples = block_exacomp_xml_get_examples();
            $founds = block_exacomp_xml_find_unused($examples, $xml, "block_exacompexamples");
            block_exacomp_xml_delete_unused_examples($founds);

            block_exacomp_xml_delete_descrtopicmm();
            block_exacomp_xml_insert_descrtopicmm($descrtopic);

            block_exacomp_xml_delete_descrexampmm();
           
            block_exacomp_xml_insert_descrexampmm($descrexamp);
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

?>
