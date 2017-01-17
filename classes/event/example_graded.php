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

namespace block_exacomp\event;
defined('MOODLE_INTERNAL') || die();

/**
 * @license	http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  gtn gmbh <office@gtn-solutions.com>
 */
class example_graded extends base {

	/**
	 * Init
	 * @return nothing
	 */
	protected function init() {
		$this->data['crud'] = 'u';
		$this->data['edulevel'] = self::LEVEL_TEACHING;
		$this->data['objecttable'] = 'block_exacompexamples';
	}

	/**
	 * Return localised event name.
	 *
	 * @return string
	 */
	public static function get_name() {
		return block_exacomp_get_string('eventsexamplegraded');
	}

	/**
	 * Get description
	 * @return string
	 */
	public function get_description() {
		return "User {$this->userid} graded the example {$this->objectid} for user {$this->relateduserid} in course {$this->courseid}";
	}

	/**
	 * Get URL related to the action
	 *
	 * @return \moodle_url
	 */
	public function get_url() {
		return new \moodle_url('/blocks/exacomp/assign_competencies.php', array('courseid' => $this->courseid));
	}

}
