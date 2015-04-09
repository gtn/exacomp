<?php
 
/**
 * Define all the backup steps that will be used by the backup_choice_activity_task
 */
 
 /**
 * Define the complete choice structure for backup, with file and id annotations
 */     
class backup_exacomp_block_structure_step extends backup_block_structure_step {
 
    protected function define_structure() {
 
    	global $DB;
    	
        // Define each element separated
 
 		$exacomp = new backup_nested_element('exacomp', array('id'), null);
         
        $settings = new backup_nested_element('settings',array(),array('courseid','grading','activities','tstamp','uses_activities','show_all_descriptors','show_all_examples','usedetailpage'));
        $mdltypes = new backup_nested_element('mdltypes');
        $mdltype = new backup_nested_element('mdltype', array('id'), array('sourceid'));
        $topics = new backup_nested_element('topics');
        $topic = new backup_nested_element('topic', array('id'), array('sourceid'));
        
        $activities = new backup_nested_element('activities');
		$compactiv_mm = new backup_nested_element('compactiv_mm', array('id'), array('compid','activityid','activitytitle','coursetitle','comptype'));
		
        // Build the tree
 
		$exacomp->add_child($settings);
		$exacomp->add_child($mdltypes);
		$mdltypes->add_child($mdltype);
		$exacomp->add_child($topics);
		$topics->add_child($topic);
		$exacomp->add_child($activities);
		$activities->add_child($compactiv_mm);
		
        // Define sources
		
		$exacomp->set_source_array(array((object)array('id' => $this->task->get_blockid())));
		
		$mdltype->set_source_sql('
				SELECT t.sourceid as id, t.sourceid
				FROM {block_exacompschooltypes} t, {block_exacompmdltype_mm} mt
				WHERE t.id = mt.stid AND mt.courseid = ?',
				array(backup::VAR_COURSEID));
		
		$topic->set_source_sql('
				SELECT t.sourceid as id, t.sourceid
				FROM {block_exacomptopics} t, {block_exacompcoutopi_mm} ct
				WHERE t.id = ct.topicid AND ct.courseid = ?',
				array(backup::VAR_COURSEID));
		
		$settings->set_source_table('block_exacompsettings', array('courseid'=>backup::VAR_COURSEID));
		
		// backup descractiv_mm
		/*$compactiv_mm_db = $DB->get_records_sql('
				(
				SELECT ca.id, d.sourceid as compid, ca.activityid, ca.activitytitle, ca.coursetitle, ca.comptype
				FROM {block_exacompcompactiv_mm} ca
				JOIN {block_exacompdescriptors} d ON d.id=ca.compid AND ca.comptype = 0 AND ca.eportfolioitem = 0 
				JOIN {course_modules} cm ON  ca.activityid=cm.id AND cm.course = ?
				)
				UNION
				(
				SELECT ca.id, d.sourceid as compid, ca.activityid, ca.comptype, ca.activitytitle, ca.coursetitle
				FROM {block_exacompcompactiv_mm} ca
				JOIN {block_exacomptopics} d ON d.id=ca.compid AND ca.comptype = 1 AND ca.eportfolioitem = 0 
				JOIN {course_modules} cm ON ca.activityid=cm.id AND cm.course = ?
				)
				', array(backup::VAR_COURSEID,backup::VAR_COURSEID));
		*/
		$compactiv_mm->set_source_sql('
				(
				SELECT ca.id, d.sourceid as compid, ca.activityid, ca.activitytitle, ca.coursetitle, ca.comptype
				FROM {block_exacompcompactiv_mm} ca
				JOIN {block_exacompdescriptors} d ON d.id=ca.compid AND ca.comptype = 0 AND ca.eportfolioitem = 0 
				JOIN {course_modules} cm ON  ca.activityid=cm.id AND cm.course = ?
				)
				UNION
				(
				SELECT ca.id, d.sourceid as compid, ca.activityid, ca.activitytitle, ca.coursetitle, ca.comptype
				FROM {block_exacompcompactiv_mm} ca
				JOIN {block_exacomptopics} d ON d.id=ca.compid AND ca.comptype = 1 AND ca.eportfolioitem = 0 
				JOIN {course_modules} cm ON ca.activityid=cm.id AND cm.course = ?
				)
				', array(backup::VAR_COURSEID,backup::VAR_COURSEID));
		
		// Return the root element (choice), wrapped into standard activity structure
		return $this->prepare_block_structure($exacomp);
    }
}