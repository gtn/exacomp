<?php
/*
 * copyright exabis
 */

namespace block_exacomp\event;
defined('MOODLE_INTERNAL') || die();

/**
 * @license	http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  gtn gmbh <office@gtn-solutions.com>
 */
class example_added extends base {

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
		return get_string('eventsexampleadded', 'block_exacomp');
	}

	/**
	 * Get description
	 * @return string
	 */
	public function get_description() {
		return "User {$this->userid} added the example {$this->objectid} in course {$this->courseid} to the weekly schedule of user {$this->relateduserid}";
	}

	/**
	 * Get URL related to the action
	 *
	 * @return \moodle_url
	 */
	public function get_url() {
		return new \moodle_url('/blocks/exacomp/weekly_schedule.php', array('courseid' => $this->courseid, 'studentid' => $this->relateduserid));
	}

}
