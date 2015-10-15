<?php

require_once($CFG->dirroot . '/blocks/exacomp/backup/moodle2/restore_exacomp_stepslib.php'); // We have structure steps

class restore_exacomp_block_task extends restore_block_task {

    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings() {
        // No particular settings for this activity
    }
	
    /**
     * Define (add) particular steps this activity can have
     */
    protected function define_my_steps() {
        // Choice only has one structure step
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


		// read all the modules except 'label', because this one is just text!
		$modules = $DB->get_records_sql("SELECT cm.*, m.name as modname
				FROM {modules} m, {course_modules} cm
				WHERE cm.course = ? AND cm.module = m.id AND m.name NOT IN ('label')",
				array($this->get_courseid()));

		foreach($modules as $module) {
			$module->name = $DB->get_field($module->modname, "name", array('id' => $module->instance));
		}

		$course = $DB->get_record("course",array("id"=>$this->get_courseid()));

		$modulesByName = array();
		foreach ($modules as $module) {
			$modulesByName[$module->name] = $module;
		}

		$activities = $DB->get_records_sql("SELECT * FROM {block_exacompcompactiv_mm} WHERE activityid = ? AND " . $DB->sql_compare_text('coursetitle') . " = ?", array(-12345, $course->shortname));

		foreach($activities as $activity) {
			if (isset($modulesByName[$activity->activitytitle])) {
				// activity found
				$activity->activityid = $modulesByName[$activity->activitytitle]->id;
				$DB->update_record("block_exacompcompactiv_mm", $activity);
			} else {
				// activity not found, delete it
				$DB->delete_records("block_exacompcompactiv_mm", array('id' => $activity->id));
			}
		}
	}

}