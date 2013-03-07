<?php

require_once($CFG->dirroot . '/blocks/exacomp/backup/moodle2/restore_exacomp_stepslib.php'); // We have structure steps

class restore_exacomp_block_task extends restore_block_task {
 
    protected function define_my_settings() {
    }

    protected function define_my_steps() {
        // rss_client has one structure step
        $this->add_step(new restore_exacomp_block_structure_step('exacomp_structure', 'exacomp.xml'));
    }

    public function get_fileareas() {
        return array(); // No associated fileareas
    }

    public function get_configdata_encoded_attributes() {
        return array(); // No special handling of configdata
    }

    static public function define_decode_contents() {
        return array();
    }

    static public function define_decode_rules() {
        return array();
    }
    
    public function after_restore() {
    	global $DB;
    	$course = $DB->get_record("course",array("id"=>$this->get_courseid()));
    	$activities = $DB->get_records_sql("SELECT * FROM {block_exacompdescractiv_mm} WHERE " . $DB->sql_compare_text('coursetitle') . " = ?",array($course->shortname));
    	    	
    	foreach($activities as $activity) {
    	
    		$sql = 'SELECT * FROM {assignment} WHERE course = ? AND ' . $DB->sql_compare_text('name') . ' = ?';
        	$source_activity = $DB->get_record_sql($sql,array($this->get_courseid(),$activity->activitytitle));
        	
        	$activityid = $DB->get_record('course_modules',array("module"=>1,"instance"=>$source_activity->id));
        	$activity->activityid = $activityid->id;
        	
        	$DB->update_record("block_exacompdescractiv_mm",$activity);
    	
    	}
    	
	}
 
}