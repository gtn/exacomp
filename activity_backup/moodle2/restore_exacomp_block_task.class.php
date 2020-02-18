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

		// this part needs to run after all activites have been added
		if (!empty($GLOBALS['activexamples'])) {
		    $idrecord = restore_dbops::get_backup_ids_record($this->get_restoreid(), 'course_module', end($GLOBALS['activexamples'][0]));
			if ($idrecord && ($cm = block_exacomp_get_cm_from_cmid($idrecord->newitemid))) {
				// activity found
			    array_push($GLOBALS['activexamples'][1], $cm->id);
			} else {
			    array_push($GLOBALS['activexamples'][1], -1);
			}
		}
	}
}