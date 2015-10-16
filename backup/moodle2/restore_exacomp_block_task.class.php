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

            var_dump(restore_dbops::get_backup_ids_record($this->get_restoreid(), 'question', 4));
            var_dump(restore_dbops::get_backup_ids_record($this->get_restoreid(), 'course_module', 4));
            var_dump(restore_dbops::get_backup_ids_record($this->get_restoreid(), 'course_module', 234));
            var_dump(restore_dbops::get_backup_ids_record($this->get_restoreid(), 'course_moduleref', 4));
            exit;
        
        if (!empty($GLOBALS['block_exacomp_imported_activities'])) {
            $modinfo = get_fast_modinfo($this->get_courseid());
            
            $modulesByName = array();
            $modulesBySectionAndName = array();
            foreach ($modinfo->get_cms() as $module) {
                $modulesByName[$module->name] = $module;
                $modulesBySectionAndName[$module->sectionnum.'-'.$module->name] = $module;
            }
            
            foreach ($GLOBALS['block_exacomp_imported_activities'] as $activity) {
                
                if (isset($modulesBySectionAndName[$activity->sectionnum.'-'.$activity->activitytitle])) {
                    $cm = $modulesBySectionAndName[$activity->sectionnum.'-'.$activity->activitytitle];
                } else if (isset($modulesByName[$activity->activitytitle])) {
                    $cm = $modulesByName[$activity->activitytitle];
                } else {
                    // activity not found, delete it
                    $cm = null;
                    $DB->delete_records("block_exacompcompactiv_mm", array('id' => $activity->id));
                }
                
                if ($cm) {
                    // activity found
                    $activity->activityid = $cm->id;
                    $activity->activitytitle = $cm->name;
                    $DB->update_record("block_exacompcompactiv_mm", $activity);
                }
            }
        }
    }
}