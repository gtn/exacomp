<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

use block_exacomp\cross_subject;
use block_exacomp\descriptor;
use block_exacomp\globals as g;
use block_exacomp\printer;
use block_exacomp\subject;
use block_exacomp\topic;

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

require __DIR__ . '/inc.php';
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->dirroot . '/webservice/lib.php');
require_once __DIR__ . "/../../config.php"; // path to Moodle's config.php
require_once __DIR__ . '/wsdatalib.php';

// Allow CORS requests.
header('Access-Control-Allow-Origin: *');

//authenticate the user
$wstoken = required_param('wstoken', PARAM_ALPHANUM);
$function = required_param('wsfunction', PARAM_ALPHANUMEXT);
$webservicelib = new webservice();
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

        printer::weekly_schedule($course, $student, optional_param('interval', 'week', PARAM_TEXT));

        // die;
    }

    static function dakora_print_crosssubject() {
        global $OUTPUT, $USER, $DB;
        $course = static::require_courseid();

        $output = block_exacomp_get_renderer();
        $output->print = true;

        $courseid = required_param('courseid', PARAM_INT);
        $crosssubjid = required_param('crosssubjectid', PARAM_INT);
        $showevaluation = optional_param("showevaluation", true, PARAM_BOOL);
        // $context = context_course::instance($courseid);
        $studentid = block_exacomp_get_studentid();
        //$page_identifier = 'tab_competence_profile_profile';
        $isTeacher = block_exacomp_is_teacher($courseid);

        $scheme = block_exacomp_get_assessment_theme_scheme($courseid);

        $activities = block_exacomp_get_activities_by_course($courseid);
        $course_settings = block_exacomp_get_settings_by_course($courseid);

        //$user_evaluation = block_exacomp_get_user_information_by_course($USER, $courseid); //so the $USER has more data   not usefull, rw

        if ($course_settings->uses_activities && !$activities && !$course_settings->show_all_descriptors) {
            echo $output->header_v2('tab_cross_subjects');
            echo $output->no_activities_warning($isTeacher);
            echo $output->footer();
            exit;
        }

        $cross_subject = $crosssubjid ? cross_subject::get($crosssubjid, MUST_EXIST) : null;

        if ($cross_subject) {
            $html_tables = array();
            if ($isTeacher) {
                $students = (!$cross_subject->is_draft() && $course_settings->nostudents != 1) ? block_exacomp_get_students_for_crosssubject($courseid, $cross_subject) : array();
                if (!$students) {
                    $selectedStudentid = 0;
                    $studentid = 0;
                } else if (isset($students[$studentid])) {
                    $selectedStudentid = $studentid;
                } else {
                    $selectedStudentid = 0;
                    $studentid = BLOCK_EXACOMP_SHOW_ALL_STUDENTS;
                }
            } else {
                $students = array($USER);
                $selectedStudentid = $USER->id;
                $studentid = $USER->id;
            }
            foreach ($students as $student) {
                $student = block_exacomp_get_user_information_by_course($student, $courseid);
            }
            $subjects = block_exacomp_get_competence_tree_for_cross_subject($courseid,
                $cross_subject,
                $isTeacher, //!($course_settings->show_all_examples == 0 && !$isTeacher),
                $course_settings->filteredtaxonomies,
                ($studentid > 0 && !$isTeacher) ? $studentid : 0,
                ($isTeacher) ? false : true);

            if ($subjects) {
                //$html_pdf = $output->overview_legend($isTeacher);
                $html_pdf = $output->overview_metadata_cross_subjects($cross_subject, false);

                $competence_overview = $output->competence_overview($subjects,
                    $courseid,
                    $students,
                    $showevaluation,
                    $isTeacher ? BLOCK_EXACOMP_ROLE_TEACHER : BLOCK_EXACOMP_ROLE_STUDENT,
                    $scheme,
                    false,
                    $cross_subject->id);
                $html_pdf .= $competence_overview;
                $html_tables[] = $html_pdf;
            }
            block_exacomp\printer::crossubj_overview($cross_subject, $subjects, $students, '', $html_tables);
        }
    }

    static function dakora_print_competence_profile() {
        global $OUTPUT, $USER, $DB;
        $course = static::require_courseid();

        $courseid = required_param('courseid', PARAM_INT);
        $context = context_course::instance($courseid);
        $studentid = block_exacomp_get_studentid();
        $page_identifier = 'tab_competence_profile_profile';
        $isTeacher = block_exacomp_is_teacher($courseid);
        $output = block_exacomp_get_renderer();

        if (!$isTeacher) {
            $studentid = $USER->id;
            $html_tables[] = $OUTPUT->tabtree(block_exacomp_build_navigation_tabs_profile($context, $courseid), $page_identifier);
        } else {
            $html_content = '';
            $html_header = '';
            $student = $DB->get_record('user', array('id' => $studentid));

            $possible_courses = block_exacomp_get_exacomp_courses($student);
            block_exacomp_init_profile($possible_courses, $student->id);
            $html_content .= $output->competence_profile_metadata($student);
            //$html_header .= $output->competence_profile_metadata($student); // TODO: ??
            $usebadges = get_config('exacomp', 'usebadges');
            $profile_settings = block_exacomp_get_profile_settings($studentid);
            $items = array();
            $user_courses = array();
            foreach ($possible_courses as $course) {
                if (isset($profile_settings->exacomp[$course->id])) {
                    $user_courses[$course->id] = $course;
                }
            }
            if (!empty($profile_settings->exacomp) || $profile_settings->showallcomps == 1) {
                $html_content .= html_writer::tag('h3', block_exacomp_get_string('my_comps'), array('class' => 'competence_profile_sectiontitle'));
                foreach ($user_courses as $course) {
                    // if selected
                    if (isset($profile_settings->exacomp[$course->id])) {
                        $html_content .= $output->competence_profile_course($course, $student, true, block_exacomp_get_grading_scheme($course->id));
                    }
                }
                // Überfachliche Kompetenzen
                // used last course from foreach! TODO: check it!
                $html_content .= $output->competence_profile_course($course, $student, true, block_exacomp_get_grading_scheme($course->id), true); //prints global values
                $html_tables[] = $html_content;
            }
        }

        block_exacomp\printer::competenceprofile_overview($studentid, $html_header, $html_tables);
    }

    static function dakora_print_competence_grid() {
        $course = static::require_courseid();

        $courseid = required_param('courseid', PARAM_INT);
        $showevaluation = optional_param("showevaluation", true, PARAM_BOOL);
        $group = optional_param('group', 0, PARAM_INT);

        $editmode = optional_param('editmode', 0, PARAM_BOOL);
        $subjectid = optional_param('subjectid', 0, PARAM_INT);

        $topicid = optional_param('topicid', BLOCK_EXACOMP_SHOW_ALL_TOPICS, PARAM_INT);
        if ($topicid == null) {
            $topicid = BLOCK_EXACOMP_SHOW_ALL_TOPICS;
        }

        $niveauid = optional_param('niveauid', BLOCK_EXACOMP_SHOW_ALL_NIVEAUS, PARAM_INT);

        $course_settings = block_exacomp_get_settings_by_course($courseid);

        // CHECK TEACHER
        $isTeacher = block_exacomp_is_teacher($courseid);

        if (!$isTeacher) {
            $editmode = 0;
        }
        $isEditingTeacher = block_exacomp_is_editingteacher($courseid, $USER->id);

        $studentid = block_exacomp_get_studentid();

        if ($studentid == 0) {
            $studentid = BLOCK_EXACOMP_SHOW_ALL_STUDENTS;
        }

        $selectedStudentid = $studentid;

        if ($editmode) {
            $selectedStudentid = $studentid;
            $studentid = BLOCK_EXACOMP_SHOW_ALL_STUDENTS;
        }

        $ret = block_exacomp_init_overview_data($courseid, $subjectid, $topicid, $niveauid, false, $isTeacher, ($isTeacher ? 0 : $USER->id), ($isTeacher) ? false : true, $course_settings->hideglobalsubjects);
        if (!$ret) {
            print_error('not configured');
        }
        list($courseSubjects, $courseTopics, $niveaus, $selectedSubject, $selectedTopic, $selectedNiveau) = $ret;

        $output = block_exacomp_get_renderer();

        // IF TEACHER SHOW ALL COURSE STUDENTS, IF NOT ONLY CURRENT USER
        $students = $allCourseStudents = ($isTeacher) ? block_exacomp_get_students_by_course($courseid) : array($USER->id => $USER);
        if ($course_settings->nostudents) {
            $allCourseStudents = array();
        }

        $course_settings = block_exacomp_get_settings_by_course($courseid);
        //$isTeacher = true; //???
        $competence_tree = block_exacomp_get_competence_tree($courseid,
            $selectedSubject ? $selectedSubject->id : null,
            $selectedTopic ? $selectedTopic->id : null,
            false,
            $selectedNiveau ? $selectedNiveau->id : null,
            true,
            $course_settings->filteredtaxonomies,
            true,
            false,
            false,
            false,
            ($isTeacher) ? false : true,
            false);
        $scheme = block_exacomp_get_grading_scheme($courseid);

        $colselector = "";
        if ($isTeacher) {    //mind nostudents setting
            if ($studentid == BLOCK_EXACOMP_SHOW_ALL_STUDENTS && $editmode == 0 && $course_settings->nostudents != 1) {
                $colselector = $output->students_column_selector(count($allCourseStudents));
            } else if (!$studentid || $course_settings->nostudents == 1 || ($studentid == BLOCK_EXACOMP_SHOW_ALL_STUDENTS && $editmode = 1)) {
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

        if ($group == 0) {
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

            $competence_overview = $output->competence_overview($competence_tree,
                $courseid,
                $students_to_print,
                $showevaluation,
                $isTeacher ? BLOCK_EXACOMP_ROLE_TEACHER : BLOCK_EXACOMP_ROLE_STUDENT,
                $scheme,
                $selectedNiveau->id != BLOCK_EXACOMP_SHOW_ALL_NIVEAUS,
                0,
                $isEditingTeacher);
            $html_tables[] = $competence_overview;
        }

        printer::competence_overview($selectedSubject, $selectedTopic, $selectedNiveau, null, $html_header, $html_tables);

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
        // Dakora has language settings, moodle has language settings.
        // To ensure that the reports are shown in the Dakora-language, the language this moodle user is temporarily force set
        $lang = required_param('lang', PARAM_TEXT);
        if (current_language() != $lang) {
            force_current_language($lang);
        }

        $course = static::require_courseid();

        $output = block_exacomp_get_renderer();
        $filter = block_exacomp_group_reports_get_filter();
        $wstoken = required_param('wstoken', PARAM_ALPHANUM);
        $action = $_SERVER['PHP_SELF'];

        $extra = '
			<input type="hidden" name="wstoken" value="' . $wstoken . '"/>
			<input type="hidden" name="wsfunction" value="' . 'group_reports_result' . '"/>
			<input type="hidden" name="courseid" value="' . $course->id . '"/>
		';

        $courseid = required_param('courseid', PARAM_INT);

        $isTeacher = block_exacomp_is_teacher($courseid);

        echo $output->group_report_filters('webservice', $filter, $action, $extra, $courseid, $isTeacher);
    }

    static function group_reports_result() {
        static::require_courseid();
        $wstoken = required_param('wstoken', PARAM_ALPHANUM);
        $filter = block_exacomp_group_reports_get_filter();
        $isPdf = optional_param('isPdf', false, PARAM_BOOL);

        // absolutely necessary to use $wstoken:
        $wsDataHandler = new block_exacomp_ws_datahandler($wstoken);
        // or in this way:
        //$wsDataHandler = new block_exacomp_ws_datahandler();
        //$wsDataHandler->setToken($wstoken);

        // example of saving data. It can be single param or array of params
        $wsDataHandler->setParam('report_filter', $filter);
        $wsDataHandler->setParam('isPdf', $isPdf);

        // example of reading params
        // 		$filtersFromSession = $wsDataHandler->getParam('report_filter');
        //  		print_r($filtersFromSession);
        // // 		var_dump($filtersFromSession);
        // 		die();

        $courseid = required_param('courseid', PARAM_INT);
        $isTeacher = block_exacomp_is_teacher($courseid);

        if ($isPdf) {
            block_exacomp_group_reports_result($filter, $isPdf, $isTeacher);
        } else {
            block_exacomp_group_reports_result($filter, false, $isTeacher);
        }

    }

    /**
     * @throws coding_exception
     * @deprecated ? - use dakora_competencegrid_overview from externallib.php
     */
    static function dakora_competencegrid_overview() {
        global $authenticationinfo;
        static::require_courseid();
        $wstoken = required_param('wstoken', PARAM_ALPHANUM);
        $subjectid = required_param('subjectid', PARAM_INT);
        $studentid = optional_param('studentid', false, PARAM_INT);

        $wsDataHandler = new block_exacomp_ws_datahandler($wstoken);

        $wsDataHandler->setParam('gridoverview_subjectid', $subjectid);
        $wsDataHandler->setParam('gridoverview_studentid', $studentid);

        $courseid = required_param('courseid', PARAM_INT);

        $isTeacher = block_exacomp_is_teacher($courseid, $authenticationinfo['user']->id);
        if (!($studentid > 0) && !$isTeacher) {
            // overview for student (self view)
            $studentid = $authenticationinfo['user']->id;
        }
        $output = block_exacomp_get_renderer();

        list($niveaus, $skills, $subjects, $data, $selection) = block_exacomp_init_competence_grid_data($courseid,
            $subjectid,
            $studentid,
            (@block_exacomp_get_settings_by_course($courseid)->show_all_examples != 0 || $isTeacher),
            block_exacomp_get_settings_by_course($courseid)->filteredtaxonomies);

        echo $output->competence_grid($niveaus, $skills, $subjects, $data, $selection, $courseid, $studentid, $subjectid, 'dakora');

    }

    private static function require_courseid() {
        $courseid = required_param('courseid', PARAM_INT);

        if (!$course = g::$DB->get_record('course', array('id' => $courseid))) {
            print_error('invalidcourse', 'block_simplehtml', $courseid);
        }

        block_exacomp_require_login($course);

        return $course;
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
            } else if ($item instanceof topic) {
                $results[$item->id] = (object)[
                    'id' => $item->id,
                    'numbering' => $item->get_numbering(),
                    'title' => $item->title,
                    'descriptors' => static::json_items($item->descriptors, $by),
                ];
            } else if ($item instanceof descriptor) {
                $results[$item->id] = (object)[
                    'id' => $item->id,
                    'numbering' => $item->get_numbering(true),
                    'title' => $item->title,
                    'children' => static::json_items($item->children, $by),
                    'niveauid' => $item->get_niveau()->id,
                ];
                if ($by == BLOCK_EXACOMP_DB_SUBJECTS) {
                    $results[$item->id]->examples = static::json_items($item->examples, $by);
                }
            } else if ($item instanceof \block_exacomp\example) {
                $results[$item->id] = (object)[
                    'id' => $item->id,
                    'title' => $item->title,
                ];
                if ($by == BLOCK_EXACOMP_DB_EXAMPLES) {
                    // for example list
                    $results[$item->id]->subjects = static::json_items($item->subjects, $by);
                }
            } else {
                throw new coding_exception('wrong object type ' . get_class($item));
            }
        }

        return $results;
    }

    /**
     * used own webservice, because moodle does not support returning files from webservices
     */
    static function diggr_set_cert_params() {
        $wstoken = required_param('wstoken', PARAM_ALPHANUM);
        $gradings = $_POST['gradings'];
        $username = $_POST['username'];

        // 	    $gradings = $gradings['gradings'];

        $wsDataHandler = new block_exacomp_ws_datahandler($wstoken);
        $wsDataHandler->setParam('gradings', $gradings);
        $wsDataHandler->setParam('username', $username);
    }

    static function diggr_create_certificate() {

        global $CFG;
        $wstoken = required_param('wstoken', PARAM_ALPHANUM);

        $wsDataHandler = new block_exacomp_ws_datahandler($wstoken);
        $gradings = $wsDataHandler->getParam('gradings');
        $username = $wsDataHandler->getParam('username');

        // Include the main TCPDF library (search for installation path).
        require_once $CFG->dirroot . '/lib/tcpdf/tcpdf.php';

        // create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT + 40, PDF_MARGIN_TOP + 15, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(0);
        $pdf->SetFooterMargin(0);

        // remove default footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
            require_once(dirname(__FILE__) . '/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        // ---------------------------------------------------------

        // set font
        $pdf->SetFont('helvetica', 'B', 38);
        $pdf->SetTextColor(0, 0, 0);
        // add a page
        $pdf->AddPage();

        // -- set new background ---

        // get the current page break margin
        $bMargin = $pdf->getBreakMargin();
        // get current auto-page-break mode
        $auto_page_break = $pdf->getAutoPageBreak();
        // disable auto-page-break
        $pdf->SetAutoPageBreak(false, 0);
        // set bacground image

        $img_file = $CFG->dirroot . '/blocks/exacomp/pix2/diwipass_zertifikat_logo.jpg';
        $pdf->Image($img_file, 0, 20, 190, 297, 'JPG', '', '', false, 300, '', false, false, 0);
        $diwilogo = $CFG->dirroot . '/blocks/exacomp/pix2/combined.PNG';
        $pdf->Image($diwilogo, 0, 225, 210, 50, 'PNG', '', '', false, 300, '', false, false, 0);
        // restore auto-page-break status
        $pdf->SetAutoPageBreak($auto_page_break, $bMargin);
        // set the starting point for the page content
        $pdf->setPageMark();

        $pdf->Write(0, "ZERTIFIKAT", '', 0, 'C', true, 0, false, false, 0);

        $pdf->SetTextColor(160, 0, 0);

        $pdf->Ln();
        $pdf->SetFont('helvetica', '', 25);
        $pdf->Write(0, $username, '', 0, 'C', true, 0, false, false, 0);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Ln();

        $pdf->Write(0, "hat am " . date("d.m.Y"), '', 0, 'C', true, 0, false, false, 0);
        $pdf->Write(0, "den Digitales-Wissen-Pass erfolgreich absolviert", '', 0, 'C', true, 0, false, false, 0);
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Ln();

        //$pdf->Write(0, $gradings[0]['name'] . "                                    " . $gradings[0]['score'], '', 0, 'L', true, 0, false, false, 0);
        //array_shift($gradings);
        $pdf->Ln();

        $html = '<span style="text-align:justify;">Für den Erwerb des Zertifikates wurde ein theoretischer und ein praktischer Nachweis in den Niveaustufen 1 bis 4 über folgende Kompetenzbereiche erbracht:</span>';
        $pdf->writeHTML($html, true, 0, true, true);
        $pdf->SetMargins(PDF_MARGIN_LEFT + 60, PDF_MARGIN_TOP + 10, PDF_MARGIN_RIGHT);
        $pdf->Ln();

        //       foreach($gradings as $grading){
        //           $pdf->Write(0, $grading['name'], '', 0, 'L', true, 0, false, false, 0);
        //           //$pdf->MultiCell(80, 0, $grading['name'], 0, 'L', 0, 0, 90, '', true);
        //           //$pdf->MultiCell(10, 0, "✓", 0, 'R', 0, 1, '', '', true);
        //           $pdf->Ln();
        //       }

        $pdf->Write(0, "Grundlagen und Zugang", '', 0, 'L', true, 0, false, false, 0);
        $pdf->Write(0, "Umgang mit Informationen und Daten", '', 0, 'L', true, 0, false, false, 0);
        $pdf->Write(0, "Kommunikation und Zusammenarbeit", '', 0, 'L', true, 0, false, false, 0);
        $pdf->Write(0, "Kreation digitaler Inhalte", '', 0, 'L', true, 0, false, false, 0);
        $pdf->Write(0, "Sicherheit", '', 0, 'L', true, 0, false, false, 0);

        $pdf->SetMargins(PDF_MARGIN_LEFT + 40, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Ln();

        $pdf->Write(0, "Folgendes Ergebnis wurde erzielt:", '', 0, 'L', true, 0, false, false, 0);
        $pdf->Ln();
        $pdf->MultiCell(80, 0, "Theorieteil", 0, 'L', 0, 0, 60, '', true);
        $pdf->MultiCell(30, 0, $gradings[1]['score'] . " %", 0, 'R', 0, 1, '', '', true);
        $pdf->MultiCell(80, 0, "Praxisteil", 0, 'L', 0, 0, 60, '', true);
        $pdf->MultiCell(30, 0, $gradings[0]['score'] . " %", 0, 'R', 0, 1, '', '', true);
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Ln();
        $html = '<span style="text-align:justify;">Es wird bestätigt, dass der/die Teilnehmer*in über elementare digitale Kompetenzen verfügt, die Voraussetzung für eine erfolgreiche Bewältigung von Beruf und Alltag sind.</span>';
        $pdf->writeHTML($html, true, 0, true, true);
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT + 10);
        $pdf->Ln();
        $pdf->Ln();

        // ---------------------------------------------------------

        //Close and output PDF document
        $pdf->Output('diwiPass.pdf', 'D');

    }

    static function diggr_create_bill() {

        global $CFG, $DB;
        $wstoken = required_param('wstoken', PARAM_ALPHANUM);

        $wsDataHandler = new block_exacomp_ws_datahandler($wstoken);
        $userid = $_POST['userid'];
        $firstname = $_POST['firstname'];
        $lastname = $_POST['lastname'];
        $email = $_POST['email'];
        $company = $_POST['company'];
        $uid = $_POST['uid'];
        $telephone = $_POST['telephone'];
        $street = $_POST['street'];
        $place = $_POST['place'];
        $codes = $_POST['codes'];
        $single_price = $_POST['single_price'];
        $total_price = $_POST['total_price'];

        //test
        /*
        $firstname = "Fabio";
        $lastname = "Pernegger";
        $email = "fpernegger@gtn-solutions.com";
        $company = "GTN-Solutions";
        $uid = "3";
        $telephone = "077843627543";
        $street = "Spittelwiese 23";
        $place = "Linz";
        $codes = "30";
        $single_price = "40";
        $total_price = "1200";
        */

        if ($DB->record_exists("block_exacompsettings", array("courseid" => -43))) {
            $orderid = $DB->get_record("block_exacompsettings", array("courseid" => -43));
            $DB->update_record("block_exacompsettings", array("id" => $orderid->id, "diwiordernumber" => $orderid->diwiordernumber + 1));
        } else {
            $DB->insert_record("block_exacompsettings", array("courseid" => -43)); // , "grading" => 0, "profoundness" => 0, "filteredtaxonomies" => "[100000000]"
        }

        // Include the main TCPDF library (search for installation path).
        require_once $CFG->dirroot . '/lib/tcpdf/tcpdf.php';

        // create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT + 10, PDF_MARGIN_TOP + 30, PDF_MARGIN_RIGHT + 10);
        $pdf->SetHeaderMargin(0);
        $pdf->SetFooterMargin(0);

        // remove default footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // ---------------------------------------------------------

        // set font
        $pdf->SetFont('helvetica', '', 25);
        $pdf->SetTextColor(0, 0, 0);
        // add a page
        $pdf->AddPage();

        // -- set new background ---

        // get the current page break margin
        $bMargin = $pdf->getBreakMargin();
        // get current auto-page-break mode
        $auto_page_break = $pdf->getAutoPageBreak();
        // disable auto-page-break
        $pdf->SetAutoPageBreak(false, 0);
        // set bacground image

        $img_file = $CFG->dirroot . '/blocks/exacomp/pix2/FrauenstiftungLogo.PNG';
        $pdf->Image($img_file, 30, 10, 160, 40, 'PNG', '', '', false, 300, '', false, false, 0);
        // restore auto-page-break status
        $pdf->SetAutoPageBreak($auto_page_break, $bMargin);
        // set the starting point for the page content
        $pdf->setPageMark();

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', '', 12);

        $pdf->Write(0, $company, '', 0, 'L', true, 0, false, false, 0);
        $pdf->Write(0, $firstname . " " . $lastname, '', 0, 'L', true, 0, false, false, 0);
        $pdf->Write(0, $street, '', 0, 'L', true, 0, false, false, 0);
        $pdf->Write(0, $place, '', 0, 'L', true, 0, false, false, 0);
        $pdf->Write(0, $email, '', 0, 'L', true, 0, false, false, 0);
        $pdf->Ln();
        $pdf->Ln();
        $pdf->Write(0, "Steyr, " . date("d.m.Y"), '', 0, 'R', true, 0, false, false, 0);
        $pdf->Ln();
        $pdf->Ln();
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Write(0, "Rechnung Nr. " . $orderid->diwiordernumber . "/ 96 /" . date("Y"), '', 0, 'L', true, 0, false, false, 0);
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Ln();
        $pdf->Ln();
        $pdf->Write(0, "DiWi-Pass: Zertifizierung digitaler Kompetenzen", '', 0, 'L', true, 0, false, false, 0);
        $pdf->Ln();
        $pdf->Ln();
        $pdf->Write(0, "Für Codes zur Teilnahme an der Zertifizierung verrechnen wir:", '', 0, 'L', true, 0, false, false, 0);
        $pdf->Ln();
        //$pdf->Write(0, $gradings[0]['name'] . "                                    " . $gradings[0]['score'], '', 0, 'L', true, 0, false, false, 0);
        //array_shift($gradings);
        $pdf->Ln();

        $html = <<<EOD
<table cellspacing="0" cellpadding="1" border="1" style="border-color:gray;">
    <tr>
        <td style = "text-align: center">Anzahl Codes</td>
        <td style = "text-align: center">Preis pro Code in €</td>
		<td style = "text-align: center">Gesamtbetrag in €</td>
    </tr>
	<tr>
        <td style = "text-align: center">{$codes}</td>
        <td style = "text-align: center">{$single_price}</td>
		<td style = "text-align: center">{$total_price}</td>
    </tr>
</table>
EOD;

        $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

        $pdf->Ln();
        $pdf->Write(0, "Im o.a. Betrag ist keine Mehrwertsteuer enthalten.", '', 0, 'L', true, 0, false, false, 0);
        $pdf->Ln();
        $pdf->Ln();
        $pdf->Write(0, "Vielen Dank für Ihre Bestellung. Nach Zahlungseingang mittels Banküberweisung erhalten Sie den/die Codes per email übermittelt.", '', 0, 'L', true, 0, false, false, 0);
        $pdf->Ln();
        $pdf->Ln();
        $pdf->Write(0, "Wir wünschen Ihnen viel Erfolg bei der Zertifizierung!", '', 0, 'C', true, 0, false, false, 0);
        $pdf->Ln();
        $pdf->Ln();
        $pdf->Ln();
        $pdf->Ln();
        $pdf->Ln();
        $pdf->Ln();
        $pdf->Write(0, "Überweisung des Betrages innerhalb 14 Tagen auf unser Konto:", '', 0, 'L', true, 0, false, false, 0);
        $pdf->Write(0, "Verein Frauenarbeit Steyr", '', 0, 'L', true, 0, false, false, 0);
        $pdf->Write(0, "Oberbank Steyr, BIC OBKLAT2L", '', 0, 'L', true, 0, false, false, 0);
        $pdf->Write(0, "IBAN AT64 1511 0009 1105 0151.", '', 0, 'L', true, 0, false, false, 0);

        // ---------------------------------------------------------

        //Close and output PDF document
        //$pdf->Output('diwiPass.pdf', 'D');
        $attachment = $pdf->Output('filename.pdf', 'S');

        global $CFG;
        require_once $CFG->dirroot . '/lib/phpmailer/moodle_phpmailer.php';

        $officemail = parse_ini_file($CFG->dirroot . "/blocks/exacomp/pix2/info.ini")["email"];
        $mailer = new moodle_phpmailer();

        $mailer->AddReplyTo($officemail, 'Reply To');
        $mailer->SetFrom($officemail, $officemail);
        $mailer->AddAddress($email, $email);
        $mailer->Subject = 'Diwipass Rechnungsbestätigung';
        $mailer->AddEmbeddedImage($CFG->dirroot . '/blocks/exacomp/pix2/frauenstiftungadresse.PNG', 'logo_1');
        $mailer->AddEmbeddedImage($CFG->dirroot . '/blocks/exacomp/pix2/diwipass.PNG', 'logo_2');
        $mailer->AltBody = "";
        $mailer->MsgHTML('
			<img src="cid:logo_2">
			<br>
			<b>Vielen Dank für Ihre Bestellung!</b>
			<p>Die Rechnung zur Bestellung finden Sie im Anhang.</p>
			<p> Nach Zahlungseingang mittels Banküberweisung erhalten Sie den/die Codes per E-Mail Übermittelt</p>.
            <p> Wir wünschen Ihnen viel Erfolg bei der diwipass-Zertifizierung!</p>
			<img src="cid:logo_1">');

        if ($attachment) {
            $mailer->AddStringAttachment($attachment, 'Rechnung.pdf');
        }
        $mailer->Send();


        $mailer = new moodle_phpmailer();

        $mailer->AddReplyTo($officemail, 'Reply To');
        $mailer->SetFrom($officemail, $officemail);
        $mailer->AddAddress($officemail, $officemail);
        $mailer->Subject = 'Diwipass Rechnungsbestätigung';
        $mailer->AddEmbeddedImage($CFG->dirroot . '/blocks/exacomp/pix2/frauenstiftungadresse.PNG', 'logo_1');
        $mailer->AddEmbeddedImage($CFG->dirroot . '/blocks/exacomp/pix2/diwipass.PNG', 'logo_2');
        $mailer->AltBody = "";
        $mailer->MsgHTML('
			<img src="cid:logo_2">
			<br>
			<b>Vielen Dank für Ihre Bestellung!</b>
			<p>Die Rechnung zur Bestellung finden Sie im Anhang.</p>
			<p> Nach Zahlungseingang mittels Banküberweisung erhalten Sie den/die Codes per E-Mail Übermittelt</p>.
            <p> Wir wünschen Ihnen viel Erfolg bei der diwipass-Zertifizierung!</p>
			<img src="cid:logo_1">');

        if ($attachment) {
            $mailer->AddStringAttachment($attachment, 'Rechnung.pdf');
        }
        $mailer->Send();

        //Close and output PDF document
        $pdf->Output('Rechnung.pdf', 'D');

    }

}


if (is_callable(['block_exacomp_simple_service', $function])) {
    ob_start();
    $ret = block_exacomp_simple_service::$function();
    $output = ob_get_clean();

    if ($ret === null) {
        header("Content-Type: text/html; charset=utf-8");
        echo $output . $ret;
    } else {
        // pretty print if available (since php 5.4.0)
        echo defined('JSON_PRETTY_PRINT') ? json_encode($ret, JSON_PRETTY_PRINT) : json_encode($ret);
    }
} else {
    throw new moodle_exception("wsfunction '$function' not found");
}
