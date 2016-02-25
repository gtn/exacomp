<?php
// This file is part of Exabis Competencies
//
// (c) 2016 exabis internet solutions <info@exabis.at>
//
// Exabis Comeptencies is free software: you can redistribute it and/or modify
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

require_once($CFG->dirroot . '/blocks/exacomp/backup/moodle2/backup_exacomp_stepslib.php'); // Because it exists (must)
require_once($CFG->dirroot . '/blocks/exacomp/backup/moodle2/backup_exacomp_settingslib.php'); // Because it exists (optional)
 
/**
 * choice backup task that provides all the settings and steps to perform one
 * complete backup of the block
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
		// exacomp only has one structure step
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
		return array();
	}
	public function get_configdata_encoded_attributes() {
	
	}
}
