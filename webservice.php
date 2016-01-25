<?php

namespace block_exacomp;

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


require_once(__DIR__.'/inc.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->dirroot . '/webservice/lib.php');

// Allow CORS requests.
header('Access-Control-Allow-Origin: *');

//authenticate the user
$token = required_param('wstoken', PARAM_ALPHANUM);
$function = required_param('wsfunction', PARAM_ALPHANUMEXT);
$webservicelib = new \webservice();
$authenticationinfo = $webservicelib->authenticate_user($token);

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

		$studentid = block_exacomp_get_studentid($isTeacher) ;

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

		return static::json_items($subjects, \block_exacomp::DB_SUBJECTS);
	}

	static function get_examples_as_list() {
		$courseid = required_param('courseid', PARAM_INT);
		$q = trim(optional_param('q', '', PARAM_RAW));

		if (!$course = g::$DB->get_record('course', array('id' => $courseid))) {
			print_error('invalidcourse', 'block_simplehtml', $courseid);
		}

		require_login($course);

		$examples = search_competence_grid_as_example_list($courseid, $q);

		return static::json_items($examples, \block_exacomp::DB_EXAMPLES);
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
				if ($by == \block_exacomp::DB_SUBJECTS) {
					$results[$item->id]->examples = static::json_items($item->examples, $by);
				}
			}
			elseif ($item instanceof example) {
				$results[$item->id] = (object)[
					'id' => $item->id,
					'title' => $item->title,
				];
				if ($by == \block_exacomp::DB_EXAMPLES) {
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
	exit;
} else {
	throw new \moodle_exception("wsfunction '$function' not found");
}

