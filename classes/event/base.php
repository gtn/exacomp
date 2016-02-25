<?php
/*
 * copyright exabis
 */

namespace block_exacomp\event;

defined('MOODLE_INTERNAL') || die();

abstract class base extends \block_exacomp\event {
	static function log(array $data) {
		// check if logging is enabled and then trigger the event
		if (!get_config('exacomp','logging')) {
			return null;
		}
		
		// moodle doesn't allow objecttable parameter in $data
		$objecttable = null;
		if (!empty($data['objecttable'])) {
			$objecttable = $data['objecttable'];
			unset($data['objecttable']);
		}

		static::prepareData($data);

		$obj = static::create($data);

		// set objecttable here
		if ($objecttable) {
			$obj->data['objecttable'] = 'block_exacompdescriptors';
		}

		return $obj->trigger();
	}
}
