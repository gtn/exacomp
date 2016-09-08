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

require_once __DIR__.'/../../inc.php';

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
