<?php
namespace block_exacomp\event;

defined('MOODLE_INTERNAL') || die();

abstract class base extends \block_exacomp\event {
	static function log(array $data) {
		// check if logging is enabled and then trigger the event
		if (!get_config('exacomp','logging')) {
			return null;
		}
		
		return parent::log($data);
	}
}
