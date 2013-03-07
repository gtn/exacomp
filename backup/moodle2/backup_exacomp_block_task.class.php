<?php
 
require_once($CFG->dirroot . '/blocks/exacomp/backup/moodle2/backup_exacomp_stepslib.php'); // Because it exists (must)
require_once($CFG->dirroot . '/blocks/exacomp/backup/moodle2/backup_exacomp_settingslib.php'); // Because it exists (optional)

/**
 * choice backup task that provides all the settings and steps to perform one
 * complete backup of the activity
 */
class backup_exacomp_block_task extends backup_block_task {
 
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
        $this->add_step(new backup_exacomp_block_structure_step('exacomp_structure', 'exacomp.xml'));

    }
 
    /**
     * Code the transformations to perform in the activity in
     * order to get transportable (encoded) links
     */
    static public function encode_content_links($content) {
        return $content;
    }
    
    public function get_fileareas() {
    
    }
    public function get_configdata_encoded_attributes() {
    
    }
    
}
