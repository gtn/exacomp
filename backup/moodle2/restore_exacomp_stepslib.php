<?php

class restore_exacomp_block_structure_step extends restore_structure_step {
 
    protected function define_structure() {
 
        $paths = array();
 
 		$paths[] = new restore_path_element('block', '/block',true);
 		$paths[] = new restore_path_element('exacomp', '/block/exacomp');
        $paths[] = new restore_path_element('topic', '/block/exacomp/topics/topic');
 		$paths[] = new restore_path_element('descractiv_mm', '/block/exacomp/activities/descractiv_mm');

        // Return the paths wrapped into standard activity structure
		return $paths;
    }
    
    public function process_block($data) {
        global $DB;

        $data = (object)$data;

        if (isset($data->exacomp['topics']['topic'])) {
        
            foreach ($data->exacomp['topics']['topic'] as $topic) {
                $topic = (object)$topic;
                $topic->courseid = $this->get_courseid();
        
        		$source_topic = $DB->get_record('block_exacomptopics',array("sourceid"=>$topic->id));
        		$topic->topicid = $source_topic->id;
 
        		// insert the record
        		$newitemid = $DB->insert_record('block_exacompcoutopi_mm', $topic);
            }
        }
        
        if (isset($data->exacomp['activities']['descractiv_mm'])) {
        	foreach ($data->exacomp['activities']['descractiv_mm'] as $descractiv_mm) {
			
			$descractiv_mm = (object)$descractiv_mm;
			
			$source_desc = $DB->get_record('block_exacompdescriptors',array("sourceid"=>$descractiv_mm->descrid));
        	$descractiv_mm->descrid = $source_desc->id;
        
        /*	$sql = 'SELECT * FROM {assignment} WHERE course = ? AND ' . $DB->sql_compare_text('name') . ' = ?';
        	$source_activity = $DB->get_record_sql($sql,array($this->get_courseid(),$descractiv_mm->activitytitle));
        	
        	$activityid = $DB->get_record('course_modules',array("module"=>1,"instance"=>$source_activity->id));*/
 			$descractiv_mm->activityid = 012345;
 			$course = $DB->get_record("course",array("id"=>$this->get_courseid()));
 			$descractiv_mm->coursetitle = $course->shortname;
        	$newitemid = $DB->insert_record('block_exacompdescractiv_mm', $descractiv_mm);
			
			}
		}

    }
    
    public function process_exacomp($data) {}
     public function process_topic($data) {
        global $DB;
        $data = (object)$data;

        $data->courseid = $this->get_courseid();
        
        $source_topic = $DB->get_record('block_exacomptopics',array("sourceid"=>$data->sourceid));
        $data->topicid = $source_topic->id;
 
        // insert the record
        $newitemid = $DB->insert_record('block_exacompcoutopi_mm', $data);
        // immediately after inserting "activity" record, call this
        //$this->apply_activity_instance($newitemid);
    }
 
    public function process_descractiv_mm($data) {
        global $DB;
         
        $data = (object)$data;
        $oldid = $data->id;
 
        $source_desc = $DB->get_record('block_exacompdescriptors',array("sourceid"=>$data->sourceid));
        $data->descrid = $source_desc->id;
        
        $source_activity = $DB->get_record('assignment',array("course"=>$this->get_courseid(),"name"=>$data->activitytitle));
        $activityid = $DB->get_record('course_modules',array("module"=>1,"instance"=>$source_activity->id));
 		$data->activityid = $activityid->id;
 
        $newitemid = $DB->insert_record('block_exabcompdescractiv_mm', $data);
        //$this->set_mapping('choice_option', $oldid, $newitemid);
    }
 
}