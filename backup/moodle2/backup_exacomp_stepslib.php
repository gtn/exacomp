<?php
 
require_once __DIR__."/../../lib/lib.php";
require_once __DIR__."/../../lib/xmllib.php";

/**
 * Define all the backup steps that will be used by the backup_choice_activity_task
 */
 
 /**
 * Define the complete choice structure for backup, with file and id annotations
 */     
class backup_exacomp_block_structure_step extends backup_block_structure_step {
 
    protected function define_structure() {
 
        global $DB;
        
        // To know if we are including userinfo
        // $userinfo = $this->get_setting_value('userinfo');
        
        // Define each element separated
 
         $exacomp = new backup_nested_element('exacomp', array('id'), null);

        $settings = new backup_nested_element('settings', array(), array('courseid','grading','tstamp','uses_activities','show_all_descriptors','show_all_examples','nostudents'
            // TODO: is this one still needed? always null
            ,'activities'
        ));
        $mdltypes = new backup_nested_element('mdltypes');
        $mdltype = new backup_nested_element('mdltype', array(), array('source', 'sourceid')); // NOTE: set source/sourceid as xml-values, not attributes. because moodle needs at least one xml-value!
        $topics = new backup_nested_element('topics');
        $topic = new backup_nested_element('topic', array(), array('source', 'sourceid'));
        $taxonomies = new backup_nested_element('taxonomies');
        $taxonomy= new backup_nested_element('taxonomy', array(), array('source', 'sourceid'));
        
        $activities = new backup_nested_element('activities');
        $compactiv_mm = new backup_nested_element('compactiv_mm', array(), array('comptype', 'compsource', 'compsourceid', 'activityid'));
        
        // Build the tree

        $exacomp->add_child($settings);
        $exacomp->add_child($mdltypes);
        $mdltypes->add_child($mdltype);
        $exacomp->add_child($topics);
        $topics->add_child($topic);
        $exacomp->add_child($taxonomies);
        $taxonomies->add_child($taxonomy);
        $exacomp->add_child($activities);
        $activities->add_child($compactiv_mm);
        
        // Define sources
        
        $exacomp->set_source_array(array((object)array('id' => $this->task->get_blockid())));
        
        $dbSchooltypes = $DB->get_records_sql("
                SELECT DISTINCT t.id, t.source, t.sourceid, 'dummy' as dummy
                FROM {block_exacompschooltypes} t
                JOIN {block_exacompmdltype_mm} mt ON t.id = mt.stid
                WHERE mt.courseid = ?",
                array($this->get_courseid()));
        $mdltype->set_source_array(block_exacomp_data_course_backup::assign_source_array($dbSchooltypes));
        
        $dbTopics = $DB->get_records_sql("
            SELECT DISTINCT t.id, t.source, t.sourceid, 'dummy' as dummy
            FROM {".block_exacomp::DB_TOPICS."} t
            JOIN {".block_exacomp::DB_COURSETOPICS."} ct ON t.id = ct.topicid
            WHERE ct.courseid = ?",
            array($this->get_courseid()));
        $topic->set_source_array(block_exacomp_data_course_backup::assign_source_array($dbTopics));
        
        $course_settings = block_exacomp_get_settings_by_course($this->get_courseid());
        if ($course_settings->filteredtaxonomies) {
            $dbTaxonomies = $DB->get_records_sql("
                SELECT DISTINCT t.id, t.source, t.sourceid, 'dummy' as dummy
                FROM {".block_exacomp::DB_TAXONOMIES."} t
                WHERE t.id IN (".join(',', $course_settings->filteredtaxonomies).")");
        } else {
            $dbTaxonomies = array();
        }
        $taxonomy->set_source_array(block_exacomp_data_course_backup::assign_source_array($dbTaxonomies));
        
        $settings->set_source_table('block_exacompsettings', array('courseid'=>backup::VAR_COURSEID));
        
        
        // backup descractiv_mm
        $dbActivities = $DB->get_recordset_sql("
                SELECT d.id as compid, d.source as compsource, d.sourceid as compsourceid, ca.activityid, ca.comptype
                FROM {block_exacompcompactiv_mm} ca
                JOIN {block_exacompdescriptors} d ON d.id=ca.compid AND ca.comptype = 0 AND ca.eportfolioitem = 0
                JOIN {course_modules} cm ON ca.activityid=cm.id AND cm.course = ?
                UNION
                SELECT d.id as compid, d.source as compsource, d.sourceid as compsourceid, ca.activityid, ca.comptype
                FROM {block_exacompcompactiv_mm} ca
                JOIN {block_exacomptopics} d ON d.id=ca.compid AND ca.comptype = 1 AND ca.eportfolioitem = 0
                JOIN {course_modules} cm ON ca.activityid=cm.id AND cm.course = ?
            ", array($this->get_courseid(), $this->get_courseid()));
        $dbActivities = iterator_to_array($dbActivities);
        $compactiv_mm->set_source_array(block_exacomp_data_course_backup::assign_source_array($dbActivities, 'comp'));

        // All the rest of elements only happen if we are including user info
        /*
        if ($userinfo) {
            // nothing for now
        }
        */

        // Define id annotations
        // actually this is not needed, because not allowed according to backup_helper::get_inforef_itemnames
        // $compactiv_mm->annotate_ids('course_module', 'activityid');
        
        // Define file annotations
        // $choice->annotate_files('mod_choice', 'intro', null); // This file area hasn't itemid

        // Return the root element (choice), wrapped into standard activity structure
        return $this->prepare_block_structure($exacomp);
    }
}