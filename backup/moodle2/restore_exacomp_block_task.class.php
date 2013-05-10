<?php

require_once($CFG->dirroot . '/blocks/exacomp/backup/moodle2/restore_exacomp_stepslib.php'); // We have structure steps

class restore_exacomp_block_task extends restore_block_task {
 
  public function history_exists() {

        $fullpath = $this->get_taskbasepath();
        if (empty($fullpath)) {
            return false;
        }

        $fullpath = rtrim($fullpath, '/') . '/exacomp.xml';
        if (!file_exists($fullpath)) {
           return false;
        }
        return true;
    }

     protected function define_my_settings() {

        if (!$this->history_exists()) {
            return;
        } 
			}

    protected function define_my_steps() {
        // rss_client has one structure step
        if ($this->history_exists()) {
	        $this->add_step(new restore_exacomp_block_structure_step('exacomp_structure', 'exacomp.xml'));
	      }
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

		$modules = block_exacomp_get_modules($this->get_courseid());
    	$course = $DB->get_record("course",array("id"=>$this->get_courseid()));
		
		$modulesByName = array();
		foreach ($modules as $module) {
			$modulesByName[$module->name] = $module;
		}
		
    	$activities = $DB->get_records_sql("SELECT * FROM {block_exacompdescractiv_mm} WHERE activityid = ? AND " . $DB->sql_compare_text('coursetitle') . " = ?", array(-12345, $course->shortname));
    	    	
    	foreach($activities as $activity) {
			if (isset($modulesByName[$activity->activitytitle])) {
				// activity found
				$activity->activityid = $modulesByName[$activity->activitytitle]->id;
				$DB->update_record("block_exacompdescractiv_mm", $activity);
			} else {
				// activity not found, delete it
				$DB->delete_records("block_exacompdescractiv_mm", array('id' => $activity->id));
			}
    	}
	}
 
}