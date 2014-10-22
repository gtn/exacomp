<?php

class restore_exacomp_block_structure_step extends restore_structure_step {

	protected function define_structure() {

		$paths = array();

		$paths[] = new restore_path_element('block', '/block',true);
		$paths[] = new restore_path_element('exacomp', '/block/exacomp');
		$paths[] = new restore_path_element('settings', '/block/exacomp/settings');
		$paths[] = new restore_path_element('mdltype', '/block/exacomp/mdltypes/mdltype');
		$paths[] = new restore_path_element('topic', '/block/exacomp/topics/topic');
		$paths[] = new restore_path_element('compactiv_mm', '/block/exacomp/activities/compactiv_mm');

		// Return the paths wrapped into standard activity structure
		return $paths;
	}

	public function process_block($data) {
		global $DB;

		$data = (object)$data;

		if (isset($data->exacomp['settings'])) {
			$settings = $data->exacomp['settings'];
			$settings = reset($settings);

			$settings['courseid'] = $this->get_courseid();
			
			$db_settings = $DB->get_record('block_exacompsettings', array('courseid'=>$this->get_courseid()));
			if(!$db_settings)
				$DB->insert_record('block_exacompsettings',$settings);
		}

		if (isset($data->exacomp['mdltypes']['mdltype'])) {
			foreach ($data->exacomp['mdltypes']['mdltype'] as $mdltype) {
				$mdltype = (object)$mdltype;
				$source_type = $DB->get_record('block_exacompschooltypes',array("sourceid"=>$mdltype->id));
				if($source_type) {
					$mdltype->stid = $source_type->id;
					$mdltype->courseid = $this->get_courseid();

					$DB->insert_record('block_exacompmdltype_mm', $mdltype);
				}
			}
		}
		
		if (isset($data->exacomp['topics']['topic'])) {

			foreach($data->exacomp['topics']['topic'] as $topic) {
				$topic = (object)$topic;
				$topic->courseid = $this->get_courseid();

				$source_topic = $DB->get_record('block_exacomptopics',array("sourceid"=>$topic->id));
				if($source_topic) {
					$topic->topicid = $source_topic->id;

					// insert the record
					$newitemid = $DB->insert_record('block_exacompcoutopi_mm', $topic);
				}
			}
		}
		
		if (isset($data->exacomp['activities']['compactiv_mm'])) {
			$course = $DB->get_record("course",array("id"=>$this->get_courseid()));

			foreach ($data->exacomp['activities']['compactiv_mm'] as $descractiv_mm) {
					
				$descractiv_mm = (object)$descractiv_mm;
					
				if($descractiv_mm->comptype == 0) {
					$source_desc = $DB->get_record('block_exacompdescriptors',array("sourceid"=>$descractiv_mm->compid));
				} else {
					$source_desc = $DB->get_record('block_exacomptopics',array("sourceid"=>$descractiv_mm->compid));
				}
				$descractiv_mm->compid = $source_desc->id;
				// temporary activityid, will be overwritten in restore_exacomp_block_task.class.php::after_restore()
				$descractiv_mm->activityid = -12345;
				$descractiv_mm->coursetitle = $course->shortname;
					
				if($source_desc)
					$newitemid = $DB->insert_record('block_exacompcompactiv_mm', $descractiv_mm);
			}
		}
	}
}