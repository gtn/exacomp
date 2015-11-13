<?php

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
$webservicelib = new webservice();
$authenticationinfo = $webservicelib->authenticate_user($token);


if ($function == 'dakora_print_schedule') {
    $courseid = required_param('courseid', PARAM_INT);

    if (!$course = $DB->get_record('course', array('id' => $courseid))) {
        print_error('invalidcourse', 'block_simplehtml', $courseid);
    }

    require_login($course);

    $context = context_course::instance($courseid);

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
            
            $student = $DB->get_record('user',array('id' => $studentid));
        }
    } else {
        $student = $USER;
    }
    
    if (!$student) {
        print_error("student not found");
    }

    block_exacomp\printer::weekly_schedule($course, $student, optional_param('interval', 'week', PARAM_TEXT));
    die;
}

throw new moodle_exception("wsfunction $function not found");
