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

require __DIR__ . '/inc.php';
require_once __DIR__ . '/example_upload_form.php';

$courseid = required_param('courseid', PARAM_INT);
$exampleid = optional_param('exampleid', 0, PARAM_INT);
$questionid = optional_param('questionid', 0, PARAM_INT);

block_exacomp_require_login($courseid);

if ($exampleid) {
    if (!$example = block_exacomp\example::get($exampleid)) {
        print_error('invalidexample', 'block_exacomp', $exampleid);
    }
    block_exacomp_require_item_capability(BLOCK_EXACOMP_CAP_MODIFY, $example);
} else {
    block_exacomp_require_capability(BLOCK_EXACOMP_CAP_ADD_EXAMPLE, $courseid);
    $example = null;
}

$context = context_course::instance($courseid);

$action = optional_param('action', 'add', PARAM_TEXT);

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/example_upload.php', array('courseid' => $courseid));
$PAGE->set_title(block_exacomp_get_string('blocktitle'));
$PAGE->set_pagelayout('embedded');

if ($action == 'serve') {
    print_error('this function is not available anymore');
}

if ($action == 'delete') {
    require_sesskey();
    if (!$example) {
        print_error('invalidexample', 'block_exacomp', $exampleid);
    }
    $returnurl = new moodle_url(required_param('returnurl', PARAM_LOCALURL));

    block_exacomp_delete_custom_example($example);

    redirect($returnurl);
}

// build breadcrumbs navigation
$coursenode = $PAGE->navigation->find($courseid, navigation_node::TYPE_COURSE);
$blocknode = $coursenode->add(block_exacomp_get_string('blocktitle'));
$blocknode->make_active();

// build tab navigation & print header
$output = block_exacomp_get_renderer();
echo $output->header($context, $courseid, '', false);
/* CONTENT REGION */

block_exacomp_require_teacher($context);
$descrid = optional_param('descrid', -1, PARAM_INT);
$topicid = optional_param('topicid', -1, PARAM_INT);
$crosssubjid = optional_param('crosssubjid', -1, PARAM_INT);

// if($descid == -1 && $topicid == -1 && $crosssubjid != -1){ //add to a crosssubject

// }else if($descrid != -1 && $topicid != -1){
$taxonomies = $DB->get_records_menu("block_exacomptaxonomies", null, "", "id, title");
$topicsub = $DB->get_record("block_exacomptopics", array("id" => $topicid));
$topics = $DB->get_records("block_exacomptopics", array("subjid" => @$topicsub->subjid), null, 'id, title');

$example_descriptors = array();
if ($exampleid > 0) {
    $example_descriptors = $DB->get_records(BLOCK_EXACOMP_DB_DESCEXAMP, array('exampid' => $exampleid), '', 'descrid');
}
if (!$questionid) {
    $tree = block_exacomp_build_example_association_tree($courseid, $example_descriptors, $exampleid, $descrid);
} else {
    //adjustet example_descripotors to question_descriptors
    $question_descriptors = $DB->get_records(BLOCK_EXACOMP_DB_DESCRIPTOR_QUESTION, array('questid' => $questionid), '', 'descrid');
    $tree = block_exacomp_build_example_association_tree($courseid, $question_descriptors, $exampleid, $descrid);
}
$csettings = block_exacomp_get_settings_by_course($courseid);
$example_activities = array();

if ($csettings->uses_activities) {
    $example_activities = block_exacomp_list_possible_activities_for_example($COURSE->id);
}

if ($descrid != -1) {
    $form = new block_exacomp_example_upload_form($_SERVER['REQUEST_URI'],
        array("descrid" => $descrid,
            "taxonomies" => $taxonomies,
            "tree" => $tree,
            "topicid" => $topicid,
            "exampleid" => $exampleid,
            "isTeacherexample" => ($example ? $example->is_teacherexample : 0),
            "uses_activities" => $csettings->uses_activities,
            "activities" => $example_activities));
} else if ($crosssubjid != -1) {
    $form = new block_exacomp_example_upload_form($_SERVER['REQUEST_URI'],
        array("crosssubjid" => $crosssubjid,
            "exampleid" => $exampleid,
            "isTeacherexample" => ($example ? $example->is_teacherexample : 0),
            "uses_activities" => $csettings->uses_activities,
            "activities" => $example_activities));
} else if ($questionid) {

    $form = new block_exacomp_example_upload_form($_SERVER['REQUEST_URI'],
        array("questionid" => $questionid,
            "tree" => $tree));
}

if ($formdata = $form->get_data()) {
    require_sesskey();
    if (!$questionid) {
        $example_icons = array(); // it is possible to have different icons for different fields
        $newExample = new stdClass();
        $newExample->title = $formdata->title;
        $newExample->description = $formdata->description;
        $newExample->timeframe = $formdata->timeframe;
        $newExample->creatorid = $USER->id;
        if (!empty($formdata->externalurl)) {
            $newExample->externalurl = (filter_var($formdata->externalurl, FILTER_VALIDATE_URL) == true) ? $formdata->externalurl : "http://" . $formdata->externalurl;
        } else {
            $newExample->externalurl = null;
        }
        if ($formdata->exampleid == 0) { // for new examples/ not for updated
            $newExample->source = BLOCK_EXACOMP_EXAMPLE_SOURCE_TEACHER;
        }
        if ($formdata->isTeacherexample) {
            $newExample->is_teacherexample = 1;
        } else {
            $newExample->is_teacherexample = 0;
        }

        $newExample->externaltask = '';
        if (!empty($formdata->assignment)) {
            if ($module = get_coursemodule_from_id(null, $formdata->assignment)) {
                // externaltask
                $newExample->externaltask = block_exacomp_get_activityurl($module)->out(false);
                // get icon path for activity and save it to database
                $mod_info = get_fast_modinfo($courseid);
                if (array_key_exists($module->id, $mod_info->cms)) {
                    $cm = $mod_info->cms[$module->id];
                    $example_icons['externaltask'] = $cm->get_icon_url()->out(false);
                    if ($cm->name) {
                        $newExample->activitytitle = $cm->name;
                    }
                }
                // activityid
                $newExample->activityid = $module->id;
                // courseid
                $newExample->courseid = $module->course;
                // activitylink
                $activitylink = block_exacomp_get_activityurl($module)->out(false);
                $activitylink = str_replace($CFG->wwwroot . '/', '', $activitylink);
                $newExample->activitylink = $activitylink;
            }
        }
        if (count($example_icons)) {
            $newExample->example_icon = serialize($example_icons);
        } else {
            $newExample->example_icon = '';
        }

        if (!get_config('exacomp', 'example_upload_global')) {
            // courseid HAS to be set because the admin setting says so. If there is no $courseid ==> error
            if ($COURSE->id != 0) {
                if ($newExample->courseid == Null || $newExample->courseid == 0) {
                    $newExample->courseid = $COURSE->id;
                }
            } else {
                throw new invalid_parameter_exception ('Courseid can not be empty, because of example_upload_global setting set to false.');
            }
        }

        if ($formdata->exampleid == 0) {
            // insert new example
            $newExample->id = $DB->insert_record(BLOCK_EXACOMP_DB_EXAMPLES, $newExample);
            $newExample->sorting = $newExample->id;
            $DB->update_record(BLOCK_EXACOMP_DB_EXAMPLES, $newExample);
        } else {
            // update example
            $newExample->id = $formdata->exampleid;
            $DB->update_record(BLOCK_EXACOMP_DB_EXAMPLES, $newExample);
            $DB->delete_records(BLOCK_EXACOMP_DB_DESCEXAMP, array('exampid' => $newExample->id));
        }

        //insert taxid in exampletax_mm
        $DB->delete_records(BLOCK_EXACOMP_DB_EXAMPTAX, ['exampleid' => $newExample->id]);
        if (!empty($formdata->taxid)) {
            foreach ($formdata->taxid as $tax => $taxid) {
                $DB->insert_record(BLOCK_EXACOMP_DB_EXAMPTAX, [
                    'exampleid' => $newExample->id,
                    'taxid' => $taxid,
                ]);
            }
        }
        // or create a new taxonomy from example form
        $newTax = trim(optional_param('newtaxonomy', '', PARAM_RAW));
        if ($newTax != '') {
            $newTaxonomy = new stdClass();
            $newTaxonomy->title = $newTax;
            $newTaxonomy->parentid = 0;
            $newTaxonomy->sorting = $DB->get_field(BLOCK_EXACOMP_DB_TAXONOMIES, 'MAX(sorting)', array()) + 1;
            $newTaxonomy->source = BLOCK_EXACOMP_EXAMPLE_SOURCE_TEACHER;
            $newTaxonomy->sourceid = 0;
            $newTaxonomy->id = $DB->insert_record(BLOCK_EXACOMP_DB_TAXONOMIES, $newTaxonomy);
            $DB->insert_record(BLOCK_EXACOMP_DB_EXAMPTAX, [
                'exampleid' => $newExample->id,
                'taxid' => $newTaxonomy->id,
            ]);
        }
        //add descriptor association
        $descriptors = block_exacomp\param::optional_array('descriptor', array(PARAM_INT => PARAM_INT));
        if ($descriptors) {
            foreach ($descriptors as $descriptorid) {
                $desc_examp = $DB->get_record(BLOCK_EXACOMP_DB_DESCEXAMP, array('descrid' => $descriptorid, 'exampid' => $newExample->id));
                if (!$desc_examp) {
                    $sql = "SELECT MAX(sorting) as sorting FROM {" . BLOCK_EXACOMP_DB_DESCEXAMP . "} WHERE descrid=?";
                    $max_sorting = $DB->get_record_sql($sql, array($descriptorid));
                    $sorting = intval($max_sorting->sorting) + 1;
                    $insert = new stdClass();
                    $insert->descrid = $descriptorid;
                    $insert->exampid = $newExample->id;
                    $insert->sorting = $sorting;

                    $DB->insert_record(BLOCK_EXACOMP_DB_DESCEXAMP, $insert);
                }
                //block_exacomp_globals::$DB->insert_or_update_record(BLOCK_EXACOMP_DB_DESCEXAMP, array('descrid'=>$descriptorid, 'exampid'=>$newExample->id));
            }
        } else if ($crosssubjid != -1) {
            $insert = new stdClass();
            $insert->descrid = null;
            $insert->id_foreign = $crosssubjid;
            $insert->table_foreign = "cross";
            $insert->exampid = $newExample->id;
            //$insert->sorting = $sorting;
            $DB->insert_record(BLOCK_EXACOMP_DB_DESCEXAMP, $insert);
        }

        // other courses
        $otherCourseids = block_exacomp_get_courseids_by_example($newExample->id);
        // add myself (should be in there anyway)
        if (!in_array($courseid, $otherCourseids)) {
            $otherCourseids[] = $courseid;
        }

        foreach ($otherCourseids as $otherCourseid) {
            //add visibility if not exists
            if (!$DB->get_record(BLOCK_EXACOMP_DB_EXAMPVISIBILITY, array('courseid' => $otherCourseid, 'exampleid' => $newExample->id, 'studentid' => 0))) {
                $DB->insert_record(BLOCK_EXACOMP_DB_EXAMPVISIBILITY, array('courseid' => $otherCourseid, 'exampleid' => $newExample->id, 'studentid' => 0, 'visible' => 1));
            }
            if (!$DB->get_record(BLOCK_EXACOMP_DB_SOLUTIONVISIBILITY, array('courseid' => $otherCourseid, 'exampleid' => $newExample->id, 'studentid' => 0))) {
                $DB->insert_record(BLOCK_EXACOMP_DB_SOLUTIONVISIBILITY, array('courseid' => $otherCourseid, 'exampleid' => $newExample->id, 'studentid' => 0, 'visible' => 1));
            }
        }

        block_exacomp_settstamp();

        // save file
        file_save_draft_area_files($formdata->files, context_system::instance()->id, 'block_exacomp', 'example_task',
            $newExample->id, array('subdirs' => 0, 'maxfiles' => 2));
        file_save_draft_area_files($formdata->solution, context_system::instance()->id, 'block_exacomp', 'example_solution',
            $newExample->id, array('subdirs' => 0, 'maxfiles' => 1));
        file_save_draft_area_files($formdata->completefile, context_system::instance()->id, 'block_exacomp', 'example_completefile',
            $newExample->id, array('subdirs' => 0, 'maxfiles' => 1));
    } else {
        require_sesskey();
        //add descriptor association
        $descriptors = block_exacomp\param::optional_array('descriptor', array(PARAM_INT => PARAM_INT));
        $descrids = array();
        if ($descriptors) {
            $records = $DB->get_records(BLOCK_EXACOMP_DB_DESCRIPTOR_QUESTION, array('questid' => $questionid));
            foreach ($records as $record) {
                $descrids[] = $record->descrid;
            }
            foreach ($descrids as $discrid) {
                if (!in_array($discrid, $descriptors)) {
                    $DB->delete_records(BLOCK_EXACOMP_DB_DESCRIPTOR_QUESTION, array('descrid' => $discrid, 'questid' => $questionid));
                }
            }
            foreach ($descriptors as $descriptorid) {
                $desc_quest = $DB->get_record(BLOCK_EXACOMP_DB_DESCRIPTOR_QUESTION, array('descrid' => $descriptorid, 'questid' => $questionid));
                if (!$desc_quest) {
                    $insert->descrid = $descriptorid;
                    $insert->questid = $questionid;

                    $DB->insert_record(BLOCK_EXACOMP_DB_DESCRIPTOR_QUESTION, $insert);
                }
            }
        }
    }

    echo $output->popup_close_and_reload();
    exit;
} else if ($form->is_cancelled()) {
    echo $output->popup_close_and_reload();
    exit;
}

if ($exampleid > 0) {
    $example->descriptors = $DB->get_fieldset_select(BLOCK_EXACOMP_DB_DESCEXAMP, 'descrid', 'exampid = ?', array($exampleid));

    $draftitemid = file_get_submitted_draft_itemid('files');
    file_prepare_draft_area($draftitemid, context_system::instance()->id, 'block_exacomp', 'example_task', $exampleid,
        array('subdirs' => 0, 'maxfiles' => 2));
    $example->files = $draftitemid;

    $draftitemid = file_get_submitted_draft_itemid('solution');
    file_prepare_draft_area($draftitemid, context_system::instance()->id, 'block_exacomp', 'example_solution', $exampleid,
        array('subdirs' => 0, 'maxfiles' => 1));
    $example->solution = $draftitemid;

    $draftitemid = file_get_submitted_draft_itemid('completefile');
    file_prepare_draft_area($draftitemid, context_system::instance()->id, 'block_exacomp', 'example_completefile', $exampleid,
        array('subdirs' => 0, 'maxfiles' => 1));
    $example->completefile = $draftitemid;

    // currently externaltask can only hold a module url
    // read the id from the url and assign it to the form
    // TODO: later add a modid field or so
    /*if ($example->externaltask && preg_match('![^a-z]id=(?<id>[0-9]+)!', $example->externaltask, $matches)) {
        $example->assignment = $matches['id'];
    }*/
    $example->assignment = $example->activityid;
    $form->set_data($example);
}
//}

$form->display();

/* END CONTENT REGION */
echo $output->footer();
