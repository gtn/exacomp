<?php
// This file is part of Exabis Competence Grid
//
// (c) 2016 GTN - Global Training Network GmbH <office@gtn-solutions.com>
//
// Exabis Competence Grid is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This script is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You can find the GNU General Public License at <http://www.gnu.org/licenses/>.
//
// This copyright notice MUST APPEAR in all copies of the script!

require_once __DIR__."/../../inc.php";

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
		$userinfo = $this->get_setting_value('users');

		// Define each element separated

		\block_exacomp\data::prepare();

		$exacomp = new backup_nested_element('exacomp', array('id'), null);

		$settings = new backup_nested_element('settings', array(), array('courseid', 'grading', 'tstamp', 'uses_activities', 'show_all_descriptors', 'nostudents'
			// TODO: is this one still needed? always null
		, 'activities',
		));
		$mdltypes = new backup_nested_element('mdltypes');
		$mdltype = new backup_nested_element('mdltype', array(), array('source', 'sourceid')); // NOTE: set source/sourceid as xml-values, not attributes. because moodle needs at least one xml-value!
		$topics = new backup_nested_element('topics');
		$topic = new backup_nested_element('topic', array(), array('source', 'sourceid'));
		$taxonomies = new backup_nested_element('taxonomies');
		$taxonomy = new backup_nested_element('taxonomy', array(), array('source', 'sourceid'));

		$activities = new backup_nested_element('activities');
		$compactiv_mm = new backup_nested_element('compactiv_mm', array(), array('comptype', 'compsource', 'compsourceid', 'activityid'));

		$compcompusers = new backup_nested_element('evaluations');

		$compcompuser = new backup_nested_element('evaluation', [], [
			'userid', 'comptype', 'source', 'sourceid', 'role', 'reviewerid', 'timestamp',
			'value', 'additionalinfo', 'evalniveauid', 'resubmission',
		]);

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

		$exacomp->add_child($compcompusers);
		$compcompusers->add_child($compcompuser);

		// Define sources

		$exacomp->set_source_array(array((object)array('id' => $this->task->get_blockid())));

		$dbSchooltypes = $DB->get_records_sql("
				SELECT DISTINCT t.id, t.source, t.sourceid, 'dummy' as dummy
				FROM {block_exacompschooltypes} t
				JOIN {block_exacompmdltype_mm} mt ON t.id = mt.stid
				WHERE mt.courseid = ?",
			array($this->get_courseid()));
		$mdltype->set_source_array(block_exacomp\data_course_backup::assign_source_array($dbSchooltypes));

		$dbTopics = $DB->get_records_sql("
			SELECT DISTINCT t.id, t.source, t.sourceid, 'dummy' as dummy
			FROM {".BLOCK_EXACOMP_DB_TOPICS."} t
			JOIN {".BLOCK_EXACOMP_DB_COURSETOPICS."} ct ON t.id = ct.topicid
			WHERE ct.courseid = ?",
			array($this->get_courseid()));
		$topic->set_source_array(block_exacomp\data_course_backup::assign_source_array($dbTopics));

		$course_settings = block_exacomp_get_settings_by_course($this->get_courseid());
		if ($course_settings->filteredtaxonomies
                && is_array($course_settings->filteredtaxonomies)
                && count($course_settings->filteredtaxonomies) > 0) {
			$dbTaxonomies = $DB->get_records_sql("
				SELECT DISTINCT t.id, t.source, t.sourceid, 'dummy' as dummy
				FROM {".BLOCK_EXACOMP_DB_TAXONOMIES."} t
				WHERE t.id IN (".join(',', $course_settings->filteredtaxonomies).")");
		} else {
			$dbTaxonomies = array();
		}
		$taxonomy->set_source_array(block_exacomp\data_course_backup::assign_source_array($dbTaxonomies));

		$settings->set_source_table('block_exacompsettings', array('courseid' => backup::VAR_COURSEID));


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
		$dbActivities = iterator_to_array($dbActivities, false);
		$compactiv_mm->set_source_array(block_exacomp\data_course_backup::assign_source_array($dbActivities, 'comp'));

		// All the rest of elements only happen if we are including user info
		if ($userinfo) {
			// nothing for now
			$compcompuser->set_source_array(static::get_evaluations($this->get_courseid()));
		}

		// Define id annotations
		// actually this is not needed, because not allowed according to backup_helper::get_inforef_itemnames
		$compcompuser->annotate_ids('user', 'userid');
		$compcompuser->annotate_ids('user', 'reviewerid');

		// Define file annotations
		// $choice->annotate_files('mod_choice', 'intro', null); // This file area hasn't itemid

		// Return the root element (choice), wrapped into standard activity structure
		return $this->prepare_block_structure($exacomp);
	}

	static function get_evaluations($courseid) {
		global $DB;

		$students = block_exacomp_get_students_by_course($courseid);
		$tree = \block_exacomp\db_layer_course::create($courseid)->get_subjects();

		$dataSources = $DB->get_records_menu(BLOCK_EXACOMP_DB_DATASOURCES, null, null, 'id,source');

		$compcompuser = [];

		$walker = function($item) use (&$walker, $courseid, &$compcompuser, &$dataSources, &$students) {
			if ($item instanceof \block_exacomp\descriptor) {
				array_walk($item->examples, $walker);
			}

			array_walk($item->get_subs(), $walker);

			if ($item->source == BLOCK_EXACOMP_DATA_SOURCE_CUSTOM) {
				$source = get_config('exacomp', 'mysource');
				$sourceid = $item->id;
			} elseif (isset($dataSources[$item->source])) {
				$source = $dataSources[$item->source];
				$sourceid = $item->sourceid;
			} else {
				throw new \Exception("source {$item->source} not found");
			}

			$sourceData = [
				'source' => $source,
				'sourceid' => $sourceid,
			];

			foreach ($students as $student) {
				$evaluation = block_exacomp_get_comp_eval($courseid, BLOCK_EXACOMP_ROLE_TEACHER, $student->id, $item::TYPE, $item->id);
				if ($evaluation) {
					$compcompuser[] = (array)$evaluation + $sourceData;
				}

				$evaluation = block_exacomp_get_comp_eval($courseid, BLOCK_EXACOMP_ROLE_STUDENT, $student->id, $item::TYPE, $item->id);
				if ($evaluation) {
					$compcompuser[] = (array)$evaluation + $sourceData;
				}
			}
		};

		array_walk($tree, $walker);

		return $compcompuser;
	}
}
