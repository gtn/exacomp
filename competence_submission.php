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

require __DIR__.'/inc.php';
require_once __DIR__.'/competence_submission_form.php';

$courseid = required_param('courseid', PARAM_INT);
$compid = required_param('compid', PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_exacomp', $courseid);
}

//// error if example does not exist or was created by somebody else
//if (!$example = $DB->get_record('block_exacompexamples', array('id' => $compid))) {
//    print_error('invalidexample', 'block_exacomp', $compid);
//}

block_exacomp_require_login($course);

$context = context_course::instance($courseid);

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/competence_submission.php', array('courseid' => $courseid,'compid' => $compid));
$PAGE->set_heading(block_exacomp_get_string('blocktitle'));
$PAGE->set_pagelayout('embedded');

// build breadcrumbs navigation
$coursenode = $PAGE->navigation->find($courseid, navigation_node::TYPE_COURSE);
$blocknode = $coursenode->add(block_exacomp_get_string('blocktitle'));
$blocknode->make_active();

// build tab navigation & print header
$output = block_exacomp_get_renderer();
echo $output->header($context, $courseid, '', false);

/* CONTENT REGION */
require_once $CFG->dirroot . '/blocks/exaport/inc.php';

$isTeacher = block_exacomp_is_teacher();
//$visible_solution = block_exacomp_is_example_solution_visible($courseid, $compid, $USER->id);
$visible_solution = false;
$form = new block_exacomp_competence_submission_form($_SERVER['REQUEST_URI'],
    array(  'compid' => $compid,
        'isTeacher' => $isTeacher,
        'studentid' => $USER->id,
        'visible_solution' => $visible_solution));

if ($formdata = $form->get_data()) {

    $type = 'file';

    //store item in the right portfolio category
    $course_category = block_exaport_get_user_category($course->fullname, $USER->id);

    if(!$course_category) {
        $course_category = block_exaport_create_user_category($course->fullname, $USER->id,0, $course->id);
    }

    $subjecttitle = block_exacomp_get_subjecttitle_by_descriptor($compid);
    $subject_category = block_exaport_get_user_category($subjecttitle, $USER->id);

    if(!$subject_category) {
        $subject_category = block_exaport_create_user_category($subjecttitle, $USER->id, $course_category->id);
    }

    if(!empty($formdata->url))
        $formdata->url = (filter_var($formdata->url, FILTER_VALIDATE_URL) == TRUE) ? $formdata->url : "http://" . $formdata->url;

    $itemid = $DB->insert_record("block_exaportitem", array('userid'=>$USER->id,'name'=>$formdata->name,'url'=>$formdata->url,'intro'=>$formdata->intro,'type'=>$type,'timemodified'=>time(),'categoryid'=>$subject_category->id, 'courseid' => $courseid));
    //autogenerate a published view for the new item
    $compTitle = $DB->get_field('block_exacompdescriptors','title',array("id"=>$compid));

    $dbView = new stdClass();
    $dbView->userid = $USER->id;
    $dbView->name = $compTitle;
    $dbView->timemodified = time();
    $dbView->layout = 1;
    // generate view hash
    do {
        $hash = substr(md5(microtime()), 3, 8);
    } while ($DB->record_exists("block_exaportview", array("hash"=>$hash)));
    $dbView->hash = $hash;

    $dbView->id = $DB->insert_record('block_exaportview', $dbView);

    //share the view with teachers
    block_exaport_share_view_to_teachers($dbView->id, $courseid);

    // add item to view
    $DB->insert_record('block_exaportviewblock', array('viewid' => $dbView->id,' positionx'=>1, 'positiony'=>1, 'type'=>'item', 'itemid' => $itemid));

    if (isset($formdata->file)) {
        $filename = $form->get_new_filename('file');
        $context = context_user::instance($USER->id);
        try {
            $form->save_stored_file('file', $context->id, 'block_exaport', 'item_file', $itemid, '/', $filename, true);
        } catch (Exception $e) {
            //some problem with the file occured
        }
    }
    $timecreated = time();
    $DB->insert_record(BLOCK_EXACOMP_DB_ITEM_MM, array('exacomp_record_id' => $compid, 'itemid' => $itemid, 'timecreated' => $timecreated, 'status' => 0, 'competence_type' => BLOCK_EXACOMP_TYPE_DESCRIPTOR));

    block_exacomp_notify_all_teachers_about_submission($courseid, $compid, $timecreated);

    \block_exacomp\event\example_submitted::log(['objectid' => $compid, 'courseid' => $courseid]);

//    // add "activity" relations to competences: TODO: is this ok?
//    $competences = $DB->get_records('block_exacompdescrexamp_mm', ['exampid' => $compid]);
//    foreach ($competences as $comp) {
//        if ($comp->descrid) {
//            $DB->insert_record('block_exacompcompactiv_mm', array('compid' => $comp->descrid, 'comptype' => 0, 'eportfolioitem' => 1, 'activityid' => $itemid));
//        }
//    }

    echo $output->popup_close_and_reload();
    exit;
} else if($form->is_cancelled()) {
    echo $output->popup_close_and_reload();
    exit;
}

$form->display();

/* END CONTENT REGION */
echo $output->footer();
