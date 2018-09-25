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
require_once($CFG->libdir.'/filelib.php');
require_once($CFG->dirroot.'/webservice/lib.php');

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

class block_exacomp_simple_service {
	/**
	 * used own webservice, because moodle does not support returning files from webservices
	 */
	static function dakora_print_schedule() {
		$course = static::require_courseid();

		// CHECK TEACHER
		$isTeacher = block_exacomp_is_teacher($course->id);

		$studentid = block_exacomp_get_studentid();

		/* CONTENT REGION */
		if ($isTeacher) {
			$coursestudents = block_exacomp_get_students_by_course($course->id);
			if ($studentid <= 0) {
				$student = null;
			} else {
				//check permission for viewing students profile
				if (!array_key_exists($studentid, $coursestudents)) {
					print_error("nopermissions", "", "", "Show student profile");
				}

				$student = g::$DB->get_record('user', array('id' => $studentid));
			}
		} else {
			$student = g::$USER;
		}

		if (!$student) {
			print_error("student not found");
		}

		\block_exacomp\printer::weekly_schedule($course, $student, optional_param('interval', 'week', PARAM_TEXT));

		// die;
	}
	
	static function dakora_print_competence_grid() {
	    $course = static::require_courseid();
	    
	    $courseid = required_param('courseid', PARAM_INT);
	    $showevaluation = optional_param("showevaluation", true, PARAM_BOOL);
	    $group = optional_param('group', 0, PARAM_INT);
	    
	    $editmode = optional_param('editmode', 0, PARAM_BOOL);
	    $subjectid = optional_param('subjectid', 0, PARAM_INT);

	    $topicid = optional_param('topicid', 0, PARAM_INT);
	    $niveauid = optional_param('niveauid', BLOCK_EXACOMP_SHOW_ALL_NIVEAUS, PARAM_INT);

// 	    var_dump($courseid,$showevaluation,$group,$editmode,$subjectid,$topicid,$niveauid);
// 	    die();
	    
	    // CHECK TEACHER
	    $isTeacher = block_exacomp_is_teacher($courseid);
	    if(!$isTeacher) $editmode = 0;
	    $isEditingTeacher = block_exacomp_is_editingteacher($courseid,$USER->id);
	    
	    $studentid = block_exacomp_get_studentid();

	    if($studentid == 0){
	        $studentid = BLOCK_EXACOMP_SHOW_ALL_STUDENTS;
	    }
	        
        $selectedStudentid = $studentid;
        
        if($editmode) {
            $selectedStudentid = $studentid;
            $studentid = BLOCK_EXACOMP_SHOW_ALL_STUDENTS;
        }  
	    
	    $ret = block_exacomp_init_overview_data($courseid, $subjectid, $topicid, $niveauid, false , $isTeacher, ($isTeacher?0:$USER->id), ($isTeacher)?false:true);
	    if (!$ret) {
	        print_error('not configured');
	    }
	    list($courseSubjects, $courseTopics, $niveaus, $selectedSubject, $selectedTopic, $selectedNiveau) = $ret;
 
	    $output = block_exacomp_get_renderer();
	    
	    // IF TEACHER SHOW ALL COURSE STUDENTS, IF NOT ONLY CURRENT USER
	    $students = $allCourseStudents = ($isTeacher) ? block_exacomp_get_students_by_course($courseid) : array($USER->id => $USER);
	    if($course_settings->nostudents) $allCourseStudents = array();
	    
	    $course_settings = block_exacomp_get_settings_by_course($courseid);
	    $isTeacher = true;
	    $competence_tree = block_exacomp_get_competence_tree($courseid,
	                                                       $selectedSubject? $selectedSubject->id : null,
	                                                       $selectedTopic? $selectedTopic->id : null,
	                                                       false,
	                                                       $selectedNiveau? $selectedNiveau->id : null,
	                                                       ($course_settings->show_all_examples != 0 || $isTeacher),
                                                	       $course_settings->filteredtaxonomies,
                                                	       true,
                                                	       false,
                                                	       false,
                                                	       false, 
                                                	       ($isTeacher) ? false : true, 
                                                	       false);   
	    
	    $scheme = block_exacomp_get_grading_scheme($courseid);
	    $colselector="";
	    if ($isTeacher) {	//mind nostudents setting
	       
	        
	        if ($studentid == BLOCK_EXACOMP_SHOW_ALL_STUDENTS && $editmode == 0 && $course_settings->nostudents != 1) {
	            $colselector = $output->students_column_selector(count($allCourseStudents));
	        } elseif (!$studentid || $course_settings->nostudents == 1 || ($studentid == BLOCK_EXACOMP_SHOW_ALL_STUDENTS && $editmode = 1)) {
	            $students = array();
	        } else {
	            $students = !empty($students[$studentid]) ? array($students[$studentid]) : $students;
	        }
	    }
	   
	    
	    
	    foreach ($students as $student) {
	        block_exacomp_get_user_information_by_course($student, $courseid);
	    }
	    
	    $output->print = true;
	    $html_tables = [];
	    
	    if ($group == -1) {
	        // all students, do nothing
	    } else {
	        // get the students on this group
	        $students = array_slice($students, $group * BLOCK_EXACOMP_STUDENTS_PER_COLUMN, BLOCK_EXACOMP_STUDENTS_PER_COLUMN, true);
	    }
	    
	    
	   
	    
	    // TODO: print column information for print
	    
	    
	    
	    // loop through all pages (eg. when all students should be printed)
	    for ($group_i = 0; $group_i < count($students); $group_i += BLOCK_EXACOMP_STUDENTS_PER_COLUMN) {
	        $students_to_print = array_slice($students, $group_i, BLOCK_EXACOMP_STUDENTS_PER_COLUMN, true);
	        
	        $html_header = $output->overview_metadata($selectedSubject->title, $selectedTopic, null, $selectedNiveau);

	        $html_tables[] = $output->competence_overview($competence_tree,
	            $courseid,
	            $students_to_print,
	            $showevaluation,
	            $isTeacher ? BLOCK_EXACOMP_ROLE_TEACHER : BLOCK_EXACOMP_ROLE_STUDENT,
	            $scheme,
	            $selectedNiveau->id != BLOCK_EXACOMP_SHOW_ALL_NIVEAUS,
	            0,
	            $isEditingTeacher);
	    }

	    \block_exacomp\printer::competence_overview($selectedSubject, $selectedTopic, $selectedNiveau, null, $html_header, $html_tables);

	}

	/**
	 * used own webservice, because moodle does not support indexed arrays (eg. [ 188 => object])
	 */
	static function get_examples_as_tree() {
		$course = static::require_courseid();
		$q = trim(optional_param('q', '', PARAM_RAW));

		$subjects = block_exacomp_search_competence_grid_as_tree($course->id, $q);

		return static::json_items($subjects, BLOCK_EXACOMP_DB_SUBJECTS);
	}

	/**
	 * used own webservice, because moodle does not support indexed arrays (eg. [ 188 => object])
	 */
	static function get_examples_as_list() {
		$course = static::require_courseid();
		$q = trim(optional_param('q', '', PARAM_RAW));

		$examples = block_exacomp_search_competence_grid_as_example_list($course->id, $q);

		return static::json_items($examples, BLOCK_EXACOMP_DB_EXAMPLES);
	}

	static function group_reports_form() {
		$course = static::require_courseid();

		$output = block_exacomp_get_renderer();
		$filter = block_exacomp_group_reports_get_filter();
		$wstoken = required_param('wstoken', PARAM_ALPHANUM);
		$action = $_SERVER['PHP_SELF'];

		$extra = '
			<input type="hidden" name="wstoken" value="'.$wstoken.'"/>
			<input type="hidden" name="wsfunction" value="'.'group_reports_result'.'"/>
			<input type="hidden" name="courseid" value="'.$course->id.'"/>
		';

		$courseid = required_param('courseid', PARAM_INT);
		echo $output->group_report_filters('webservice', $filter, $action, $extra, $courseid);
	}

	static function group_reports_result() {
		static::require_courseid();

		$filter = block_exacomp_group_reports_get_filter();

		block_exacomp_group_reports_result($filter);
	}
	
	

	private static function require_courseid() {
		$courseid = required_param('courseid', PARAM_INT);

		if (!$course = g::$DB->get_record('course', array('id' => $courseid))) {
			print_error('invalidcourse', 'block_simplehtml', $courseid);
		}

		require_login($course);

		return $course;
	}


	private static function json_items($items, $by) {
		$results = [];

		foreach ($items as $item) {
			if ($item instanceof \block_exacomp\subject) {
				$results[$item->id] = (object)[
					'id' => $item->id,
					'title' => $item->title,
					'topics' => static::json_items($item->topics, $by),
				];
			} elseif ($item instanceof \block_exacomp\topic) {
				$results[$item->id] = (object)[
					'id' => $item->id,
					'numbering' => $item->get_numbering(),
					'title' => $item->title,
					'descriptors' => static::json_items($item->descriptors, $by),
				];
			} elseif ($item instanceof \block_exacomp\descriptor) {
				$results[$item->id] = (object)[
					'id' => $item->id,
					'numbering' => $item->get_numbering(),
					'title' => $item->title,
					'children' => static::json_items($item->children, $by),
				];
				if ($by == BLOCK_EXACOMP_DB_SUBJECTS) {
					$results[$item->id]->examples = static::json_items($item->examples, $by);
				}
			} elseif ($item instanceof \block_exacomp\example) {
				$results[$item->id] = (object)[
					'id' => $item->id,
					'title' => $item->title,
				];
				if ($by == BLOCK_EXACOMP_DB_EXAMPLES) {
					// for example list
					$results[$item->id]->subjects = static::json_items($item->subjects, $by);
				}
			} else {
				throw new \coding_exception('wrong object type '.get_class($item));
			}
		}

		return $results;
	}
}

if (is_callable(['block_exacomp_simple_service', $function])) {
	ob_start();
	$ret = block_exacomp_simple_service::$function();
	$output = ob_get_clean();

	if ($ret === null) {
		header("Content-Type: text/html; charset=utf-8");
		echo $output.$ret;
	} else {
		// pretty print if available (since php 5.4.0)
		echo defined('JSON_PRETTY_PRINT') ? json_encode($ret, JSON_PRETTY_PRINT) : json_encode($ret);
	}
} else {
	throw new \moodle_exception("wsfunction '$function' not found");
}
