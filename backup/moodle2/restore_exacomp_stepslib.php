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
 			$course = $DB->get_record("course",array("id"=>$this->get_courseid()));

        	foreach ($data->exacomp['activities']['descractiv_mm'] as $descractiv_mm) {
			
				$descractiv_mm = (object)$descractiv_mm;
			
				$source_desc = $DB->get_record('block_exacompdescriptors',array("sourceid"=>$descractiv_mm->descrid));
				$descractiv_mm->descrid = $source_desc->id;
        
				// temporary activityid, will be overwritten in restore_exacomp_block_task.class.php::after_restore()
				$descractiv_mm->activityid = -12345;
				$descractiv_mm->coursetitle = $course->shortname;
			
				$newitemid = $DB->insert_record('block_exacompdescractiv_mm', $descractiv_mm);
			}
		}

    }
}