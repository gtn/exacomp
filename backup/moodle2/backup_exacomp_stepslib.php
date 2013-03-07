<?php
 
/**
 * Define all the backup steps that will be used by the backup_choice_activity_task
 */
 
 /**
 * Define the complete choice structure for backup, with file and id annotations
 */     
class backup_exacomp_block_structure_step extends backup_block_structure_step {
 
    protected function define_structure() {
 
 
        // Define each element separated
        
        $exacomp = new backup_nested_element('exacomp', array('id'), null);
         
        $topics = new backup_nested_element('topics');
        $topic = new backup_nested_element('topic', array('id'), array('sourceid'));
        
        $activities = new backup_nested_element('activities');
        $descractiv_mm = new backup_nested_element('descractiv_mm', array('id'), array('descrid','activityid','activitytitle','coursetitle'));
 
        // Build the tree
        $exacomp->add_child($topics);
        $topics->add_child($topic);
        $exacomp->add_child($activities);
        $activities->add_child($descractiv_mm);
 
        // Define sources
        
        $exacomp->set_source_array(array((object)array('id' => $this->task->get_blockid())));

		$topic->set_source_sql('
            SELECT t.sourceid as id, t.sourceid
              FROM {block_exacomptopics} t, {block_exacompcoutopi_mm} ct
             WHERE t.id = ct.topicid AND ct.courseid = ?',
            array(backup::VAR_COURSEID));
 
 		$descractiv_mm->set_source_sql('
 			SELECT da.id, d.sourceid as descrid, da.activityid, da.activitytitle, da.coursetitle
 			  FROM {block_exacompdescractiv_mm} da, {block_exacompdescriptors} d, {course_modules} cm, {assignment} a
 			 WHERE d.id=da.descrid AND da.activitytype=1 AND da.activityid=cm.id AND cm.module=1 AND a.id=cm.instance AND a.course = ?
 			',
 			array(backup::VAR_COURSEID));
        // Define id annotations
 
        // Define file annotations
 
        // Return the root element (choice), wrapped into standard activity structure
        return $this->prepare_block_structure($exacomp);
 
    }
}