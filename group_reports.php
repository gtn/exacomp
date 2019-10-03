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

require __DIR__.'/inc.php';
require_once __DIR__."/../../config.php"; // path to Moodle's config.php
require_once __DIR__.'/wsdatalib.php';

$courseid = required_param('courseid', PARAM_INT);


if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_simplehtml', $courseid);
}


//To log in from Dakora:
$wstoken = optional_param('wstoken', null, PARAM_RAW);

require_once($CFG->dirroot.'/webservice/lib.php');

$authenticationinfo = null;
if ($wstoken) {
    $webservicelib = new \webservice();
    $authenticationinfo = $webservicelib->authenticate_user($wstoken);
}
 require_login($course);

$useprofoundness = block_exacomp_get_settings_by_course($courseid)->useprofoundness;

$reportType = optional_param('reportType', 'general', PARAM_ALPHANUM);
if ($reportType == 'profoundness' && !$useprofoundness) {
    print_error('This function is disabled!');
}

$page_identifier = 'tab_teacher_report_'.$reportType;

$action = optional_param('action', '', PARAM_TEXT);
$isDocx = (bool)optional_param('formatDocx', false, PARAM_RAW);
$isPdf = (bool)optional_param('formatPdf', false, PARAM_RAW);

$isTemplateDeleting = (bool)optional_param('deleteTemplate', false, PARAM_RAW);
block_exacomp_save_report_settings($courseid, $isTemplateDeleting);


$output = block_exacomp_get_renderer();


if (optional_param('print', false, PARAM_BOOL)) {
    $output->print = true;
    $wsDataHandler = new block_exacomp_ws_datahandler($wstoken);
    $filter = $wsDataHandler->getParam('report_filter');
} else {
    //geht hier rein
    $filter = block_exacomp_group_reports_get_filter($reportType);
}

    // before all output

    if ($action == 'search') {
        $output->print = true;

        switch ($reportType) {
            case 'general':
                if ($isPdf) {
                    block_exacomp_group_reports_result($filter, $isPdf);
                }
                break;
            case 'annex':
                if ($isDocx || $isPdf) {
                    block_exacomp_group_reports_annex_result($filter);
                }
                break;
            case 'profoundness':
                if (/*$isDocx || */$isPdf) {
                    block_exacomp_group_reports_profoundness_result($filter);
                }
                break;
        }
    }
    $PAGE->set_url('/blocks/exacomp/group_reports.php', array('courseid' => $courseid, 'reportType' => $reportType));

    $output = block_exacomp_get_renderer();

    $PAGE->requires->js('/blocks/exacomp/javascript/fullcalendar/moment.min.js', true);
    $PAGE->requires->js('/blocks/exacomp/javascript/jquery.daterangepicker.min.js', true);
    $PAGE->requires->css('/blocks/exacomp/css/daterangepicker.min.css', true);

    echo $output->header_v2('tab_group_reports');

    $extra = '<input type="hidden" name="action" value="search"/>';

        ?>
    	<style>
    		.block h2 {
    			font-size: 130%;
    			margin: 0;
    			padding: 5px;
    			line-height: 100%;
    		}

    		.block h3 {
    			font-size: 110%;
    			margin: 0;
    			padding: 5px;
    			line-height: 100%;
    		}

    		.block h3 * {
    			font-weight: bold;
    		}

    		label {
    			margin: 0;
    			padding: 0;
    			display: inline;
    		}

    		.filter-group .filter-group-body {
    			display: none;
    		}
    		.filter-group.visible .filter-group-body {
    			display: block;
    		}
    		.filter-group-body > div {
    			padding: 0 0 8px 25px;
    		}

    		.range-inputs {
    			display: none;
    		}

    		.filter-title {
    			display: inline-block;
    			width: 140px;
    		}
    	</style>
    	<script>
    			function update() {
    				$(':checkbox.filter-group-checkbox').each(function () {
    					if ($(this).is(':checked')) {
    						$(this).closest('.filter-group').addClass('visible');
    					} else {
    						$(this).closest('.filter-group').removeClass('visible');
    					}
    				});
    			}

    			$(document).on('change', ':checkbox.filter-group-checkbox', update);
    			$(update);
    	</script>
    <?php
        $settings_subtree = array();
        $settings_subtree[] = new tabobject('tab_teacher_report_general', new moodle_url('/blocks/exacomp/group_reports.php', array('courseid' => $courseid, 'reportType'=>'general')), block_exacomp_get_string("tab_teacher_report_general"), null, true);
        $settings_subtree[] = new tabobject('tab_teacher_report_annex', new moodle_url('/blocks/exacomp/group_reports.php', array('courseid' => $courseid, 'reportType' => 'annex')), block_exacomp_get_string("tab_teacher_report_annex"), null, true);
        if ($useprofoundness) {
            $settings_subtree[] = new tabobject('tab_teacher_report_profoundness', new moodle_url('/blocks/exacomp/group_reports.php', array('courseid' => $courseid, 'reportType' => 'profoundness')),
                    block_exacomp_get_string("tab_teacher_report_profoundness"), null, true);
        }

        echo $OUTPUT->tabtree($settings_subtree, $page_identifier);

    ?>
    	<div class="block">
            <?php
            echo '<h2>'.block_exacomp_get_string('display_settings').'</h2>';
            switch ($reportType) {
                case 'annex':
                    echo $output->group_report_annex_filters('exacomp', $filter, '', $extra, $courseid);
                    break;
                case 'profoundness':
                    echo $output->group_report_profoundness_filters('exacomp', $filter, '', $extra, $courseid);
                    break;
                default:
                    echo $output->group_report_filters('exacomp', $filter, '', $extra, $courseid);
            }
            ?>
    	</div>
    <?php

    if ($action == 'search' && !$isTemplateDeleting) {
        echo html_writer::tag('h2', block_exacomp_get_string('result'));
//        const BLOCK_EXACOMP_TYPE_DESCRIPTOR = 0;
//        const BLOCK_EXACOMP_TYPE_TOPIC = 1;
//        const BLOCK_EXACOMP_TYPE_CROSSSUB = 2;
//        const BLOCK_EXACOMP_TYPE_SUBJECT = 3;
//        const BLOCK_EXACOMP_TYPE_EXAMPLE = 4;
//        const BLOCK_EXACOMP_TYPE_DESCRIPTOR_PARENT = 1001;
//        const BLOCK_EXACOMP_TYPE_DESCRIPTOR_CHILD = 1002;
        $filterlogictext = "Filterlogic: <br>";

        if($filter[BLOCK_EXACOMP_TYPE_SUBJECT]["visible"]){
            $filterlogictext .= "All Subjects which pass following filters: ";
            $filterlogictext = create_filterlogic_text(BLOCK_EXACOMP_TYPE_SUBJECT,$filter,$filterlogictext);
        }

        if($filter[BLOCK_EXACOMP_TYPE_TOPIC]["visible"]){
            $filterlogictext .= "<br>All topics of subjects that have not been filtered AND pass following filters: ";
            $filterlogictext = create_filterlogic_text(BLOCK_EXACOMP_TYPE_TOPIC,$filter,$filterlogictext);
        }

        if($filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR_PARENT]["visible"]){
            $filterlogictext .= "<br>All parent descriptors of topics that have not been filtered AND pass following filters: ";
            $filterlogictext = create_filterlogic_text(BLOCK_EXACOMP_TYPE_DESCRIPTOR_PARENT,$filter,$filterlogictext);
        }

        if($filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR_CHILD]["visible"]){
            $filterlogictext .= "<br>All child descriptors of parent descriptors that have not been filtered AND pass following filters: ";
            $filterlogictext = create_filterlogic_text(BLOCK_EXACOMP_TYPE_DESCRIPTOR_CHILD,$filter,$filterlogictext);
        }

        if($filter[BLOCK_EXACOMP_TYPE_DESCRIPTOR_EXAMPLE]["visible"]){
            $filterlogictext .= "<br>All examples of subjects, topics and descriptors that have not been filtered AND pass following filters: ";
            $filterlogictext = create_filterlogic_text(BLOCK_EXACOMP_TYPE_EXAMPLE,$filter,$filterlogictext);
        }

        echo html_writer::tag('p', $filterlogictext);


        switch ($reportType) {
            case 'annex':
                block_exacomp_group_reports_annex_result($filter);
                break;
            case 'profoundness':
                block_exacomp_group_reports_profoundness_result($filter);
                break;
            default:
                block_exacomp_group_reports_result($filter, $isPdf);
        }
    }


    echo $output->footer();

function create_filterlogic_text($input_type,$filter,$filterlogictext) {
    $niveauValues = block_exacomp\global_config::get_evalniveaus();
    $first = true;
    if($filter[$input_type]["evalniveauid"]) {
        $filterlogictext .= "Niveau: ";
        foreach ($filter[$input_type]["evalniveauid"] as $niveauid) {
            if ($niveauid == 0) {
                $filterlogictext .= "not specified";
                $first = false;
            } else {
                if (!$first) {
                    $filterlogictext .= " OR ";
                }
                $filterlogictext .= $niveauValues[$niveauid] . " ";
                $first = false;
            }
        }
    }

    $gradingScheme = block_exacomp_additional_grading($input_type);
    $teacher_eval_items = \block_exacomp\global_config::get_teacher_eval_items(g::$COURSE->id, false, $gradingScheme);
    switch ($gradingScheme) {
        // Input fields for Grade|Points
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE:
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_POINTS:
            if($filter[$input_type]["teacherevaluation_from"] != null) {
                $filterlogictext .= " AND teacherevaluation from ";
                $filterlogictext .= $filter[$input_type]["teacherevaluation_from"];
                $filterlogictext .= "  to ";
                $filterlogictext .= $filter[$input_type]["teacherevaluation_to"];
            }
            break;
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_VERBOSE:
            if($filter[$input_type]["teacherevaluation"]) {
                $filterlogictext .= " AND ";
                $filterlogictext .= "teacherevaluation: ";
                $first = true;
                foreach($filter[$input_type]["teacherevaluation"] as $teachereval){
                    if($teachereval == -1){
                        $filterlogictext .= "not specified";
                        $first = false;
                    }else{
                        if(!$first){
                            $filterlogictext .= " OR ";
                        }
                        $filterlogictext .= $teacher_eval_items[$teachereval]." ";
                        $first = false;
                    }
                }
            }
            break;
        case BLOCK_EXACOMP_ASSESSMENT_TYPE_YESNO:
            //TODO
            break;
        default:
    }

    if($filter[$input_type]["studentevaluation"]) {
        $filterlogictext .= " AND ";
        $filterlogictext .= "studentevaluation: ";
        $scheme_values = \block_exacomp\global_config::get_student_eval_items(true, $input_type);
        $first = true;
        foreach($filter[$input_type]["studentevaluation"] as $studenteval){
            if($studenteval == 0){
                $filterlogictext .= "not specified";
                $first = false;
            }else{
                if(!$first){
                    $filterlogictext .= " OR ";
                }
                $filterlogictext .= $scheme_values[$studenteval]." ";
                $first = false;
            }
        }
    }

    return $filterlogictext;
}







/*
?>
<form method="post">
	<input type="text" name="q" value="<?php p($q); ?>" />
	<input type="submit" name="Suchen" />
</form>
<?php

if (!$q) {
	exit;
}
*/


