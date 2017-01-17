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

use block_exacomp\globals as g;

/**
 * logic copied from webservice/pluginfile.php
 */

/**
 * AJAX_SCRIPT - exception will be converted into JSON
 */
define('AJAX_SCRIPT', true);

/**
 * NO_MOODLE_COOKIES - we don't want any cookie
 */
define('NO_MOODLE_COOKIES', true);


require __DIR__.'/inc.php';
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->dirroot . '/webservice/lib.php');

// Allow CORS requests.
header('Access-Control-Allow-Origin: *');

//authenticate the user
$wstoken = required_param('wstoken', PARAM_ALPHANUM);
$function = required_param('wsfunction', PARAM_ALPHANUMEXT);
$webservicelib = new \webservice();
$authenticationinfo = $webservicelib->authenticate_user($wstoken);

// check if it is a exacomp token
if ($authenticationinfo['service']->name != 'exacompservices') {
	throw new moodle_exception('not an exacomp webservice token');
}

class simple_service {
	static function dakora_print_schedule() {
		$courseid = required_param('courseid', PARAM_INT);

		if (!$course = g::$DB->get_record('course', array('id' => $courseid))) {
			print_error('invalidcourse', 'block_simplehtml', $courseid);
		}

		require_login($course);

		$context = \context_course::instance($courseid);

		// CHECK TEACHER
		$isTeacher = block_exacomp_is_teacher($context);

		$studentid = block_exacomp_get_studentid() ;

		/* CONTENT REGION */
		if($isTeacher){
			$coursestudents = block_exacomp_get_students_by_course($courseid);
			if($studentid <= 0) {
				$student = null;
			}else{
				//check permission for viewing students profile
				if(!array_key_exists($studentid, $coursestudents))
					print_error("nopermissions","","","Show student profile");

				$student = g::$DB->get_record('user',array('id' => $studentid));
			}
		} else {
			$student = g::$USER;
		}

		if (!$student) {
			print_error("student not found");
		}

		printer::weekly_schedule($course, $student, optional_param('interval', 'week', PARAM_TEXT));

		// die;
	}

	static function get_examples_as_tree() {
		$courseid = required_param('courseid', PARAM_INT);
		$q = trim(optional_param('q', '', PARAM_RAW));

		if (!$course = g::$DB->get_record('course', array('id' => $courseid))) {
			print_error('invalidcourse', 'block_simplehtml', $courseid);
		}

		require_login($course);

		$subjects = search_competence_grid_as_tree($courseid, $q);

		return static::json_items($subjects, BLOCK_EXACOMP_DB_SUBJECTS);
	}

	static function get_examples_as_list() {
		$courseid = required_param('courseid', PARAM_INT);
		$q = trim(optional_param('q', '', PARAM_RAW));

		if (!$course = g::$DB->get_record('course', array('id' => $courseid))) {
			print_error('invalidcourse', 'block_simplehtml', $courseid);
		}

		require_login($course);

		$examples = search_competence_grid_as_example_list($courseid, $q);

		return static::json_items($examples, BLOCK_EXACOMP_DB_EXAMPLES);
	}

	private static function json_items($items, $by) {
		$results = [];

		foreach ($items as $item) {
			if ($item instanceof subject) {
				$results[$item->id] = (object)[
					'id' => $item->id,
					'title' => $item->title,
					'topics' => static::json_items($item->topics, $by),
				];
			}
			elseif ($item instanceof topic) {
				$results[$item->id] = (object)[
					'id' => $item->id,
					'numbering' => $item->get_numbering(),
					'title' => $item->title,
					'descriptors' => static::json_items($item->descriptors, $by),
				];
			}
			elseif ($item instanceof descriptor) {
				$results[$item->id] = (object)[
					'id' => $item->id,
					'numbering' => $item->get_numbering(),
					'title' => $item->title,
					'children' => static::json_items($item->children, $by),
				];
				if ($by == BLOCK_EXACOMP_DB_SUBJECTS) {
					$results[$item->id]->examples = static::json_items($item->examples, $by);
				}
			}
			elseif ($item instanceof example) {
				$results[$item->id] = (object)[
					'id' => $item->id,
					'title' => $item->title,
				];
				if ($by == BLOCK_EXACOMP_DB_EXAMPLES) {
					// for example list
					$results[$item->id]->subjects = static::json_items($item->subjects, $by);
				}
			}
			else {
				throw new \coding_exception('wrong object type '.get_class($item));
			}
		}

		return $results;
	}
}

if (is_callable(['\block_exacomp\simple_service', $function])) {
	$ret = simple_service::$function();

	// pretty print if available (since php 5.4.0)
	echo defined('JSON_PRETTY_PRINT') ? json_encode($ret, JSON_PRETTY_PRINT) : json_encode($ret);
} else {
	throw new \moodle_exception("wsfunction '$function' not found");
}
