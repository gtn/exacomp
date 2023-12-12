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

use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\PdfParser;
use setasign\Fpdi\PdfParser\StreamReader;
use setasign\Fpdi\PdfReader\PageBoundaries;
use setasign\Fpdi\PdfReader\PdfReader;

defined('MOODLE_INTERNAL') || die();

require_once __DIR__ . '/inc.php';

function block_exacomp_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {

    //  Check the contextlevel is as expected - if your plugin is a block, this becomes CONTEXT_BLOCK, etc.
    if ($context->contextlevel != CONTEXT_COURSE) {
        return false;
    }

    // Make sure the user is logged in and has access to the module (plugins that are not course modules should leave out the 'cm' part).
    block_exacomp_require_login($course, true, $cm);

    // Check the relevant capabilities - these may vary depending on the filearea being accessed.

    // Leave this line out if you set the itemid to null in make_pluginfile_url (set $itemid to 0 instead).
    $itemid = array_shift($args); // The first item in the $args array.

    // Extract the filename / filepath from the $args array.
    $filename = array_pop($args); // The last item in the $args array.

    //get the position of the file (if more files exist) via param in the link
    $position = optional_param('position', 0, PARAM_INT);

    if ($filearea == 'example_task') {
        $example = block_exacomp\example::get($itemid);
        if (!$example) {
            throw new block_exacomp_permission_exception('file not found');
        }
        $example->require_capability(BLOCK_EXACOMP_CAP_VIEW);

        $file = block_exacomp_get_file($example, $filearea, $position);
        if (!$file) {
            return false;
        }

        //		$options['filename'] = $filename;
        $options['filename'] = $file->get_filename(); //overwrite the filename that has been sent in the URL with the actual filename
    } else if ($filearea == 'example_solution') {
        // actually all users are allowed to see the solution
        /*
        if (!block_exacomp_is_teacher($context)) {
            return false;
        }
        */

        $example = block_exacomp\example::get($itemid);
        if (!$example) {
            throw new block_exacomp_permission_exception('file not found');
        }
        $example->require_capability(BLOCK_EXACOMP_CAP_VIEW);

        $file = block_exacomp_get_file($example, $filearea);
        if (!$file) {
            return false;
        }

        //		$options['filename'] = $filename;
        $options['filename'] = $file->get_filename(); //overwrite the filename that has been sent in the URL with the actual filename
    } else if ($filearea == 'example_completefile') {
        // actually all users are allowed to see the completefile
        $example = block_exacomp\example::get($itemid);
        if (!$example) {
            throw new block_exacomp_permission_exception('file not found');
        }
        $example->require_capability(BLOCK_EXACOMP_CAP_VIEW);
        $file = block_exacomp_get_file($example, $filearea);
        if (!$file) {
            return false;
        }
        $options['filename'] = $file->get_filename(); //overwrite the filename that has been sent in the URL with the actual filename
    } else {
        // wrong filearea
        return false;
    }

    /*
    // Use the itemid to retrieve any relevant data records and perform any security checks to see if the
    // user really does have access to the file in question.

    if (!$args) {
        $filepath = '/'; // $args is empty => the path is '/'
    } else {
        $filepath = '/'.implode('/', $args).'/'; // $args contains elements of the filepath
    }

    // Retrieve the file from the Files API.
    $fs = get_file_storage();
    $file = $fs->get_file(context_system::instance()->id, 'block_exacomp', $filearea, $itemid, $filepath, $filename);

    if (!$file) {
        echo context_system::instance()->id.", $filearea, $itemid, $filepath, $filename";
        return false; // The file does not exist.
    }
    */

    // We can now send the file back to the browser - in this case with a cache lifetime of 1 day and no filtering.
    // From Moodle 2.3, use send_stored_file instead.

    $as_pdf = optional_param('as_pdf', false, PARAM_BOOL);
    if ($as_pdf) {
        \block_exacomp\api::send_stored_file_as_pdf($file, $forcedownload, $options);
    }

    // $as_image = optional_param('as_image', false, PARAM_TEXT);
    // if ($as_image) {
    //     send_stored_file_as_image($file, $forcedownload, $options);
    // }

    send_stored_file($file, 0, 0, $forcedownload, $options);
    exit;
}

/*
function send_stored_file_as_image(\stored_file $file, $forcedownload, $options) {
    $info = $file->get_imageinfo();
    if ($info) {
        // already a image
        send_stored_file($file, null, 0, $forcedownload, $options);
        exit;
    }

    if ($file->get_mimetype() == 'application/pdf') {
        // convert to image

        $converter = new \core_files\converter();
        var_dump($converter->can_convert_storedfile_to($file, 'jpg'));
        $conversion = $converter->start_conversion($file, 'jpg');
        var_dump($conversion);
        exit;
    }

    send_header_404();
    die('can not convert to image');
}
*/


function is_exacomp_active_in_course() {
    global $COURSE, $PAGE, $CFG;

    $page = new moodle_page();
    $page->set_url('/course/view.php', array('id' => $COURSE->id));
    $page->set_pagelayout('course');
    $page->set_course($COURSE);

    $blockmanager = $page->blocks;

    $blockmanager->load_blocks(true);

    foreach ($blockmanager->get_regions() as $region) {
        foreach ($blockmanager->get_blocks_for_region($region) as $block) {
            $instance = $block->instance;
            if ($instance->blockname == "exacomp") {
                return true;
            }
        }
    }
    return false;
}

/**
 * Inject the exacomp element into all moodle module settings forms.
 *
 * @param moodleform $formwrapper The moodle quickforms wrapper object.
 * @param MoodleQuickForm $mform The actual form object (required to modify the form).
 */
function block_exacomp_coursemodule_standard_elements($formwrapper, $mform) {
    global $CFG, $COURSE, $DB, $PAGE;

    //$exacomp_active = is_exacomp_active_in_course(); // only inject if the block is active in this course
    //
    //    // enableavailability is a setting in mdl_config
    //    if (!empty($CFG->enableavailability) && $exacomp_active) {
    //        $cmid = optional_param('update', 0, PARAM_INT);
    //        $exacompUseAutoCompetencesVal = block_exacomp_cmodule_is_autocompetence($cmid);
    //        $mform->addElement('checkbox', 'exacompUseAutoCompetences', block_exacomp_get_string('module_used_availabilitycondition_competences'));
    //        $mform->setType('exacompUseAutoCompetences', PARAM_INT);
    //        if ($exacompUseAutoCompetencesVal) {
    //            $mform->setDefault('exacompUseAutoCompetences', true);
    //        }
    //        // sorting all elements - we need to add our element before 'Restrict access' element
    //        $allelements = $mform->_elementIndex;
    //        //$DB->delete_records('block_exacompcmsettings', ['name' => 'exacompUseAutoCompetnces']);
    //        $exacompElementInd = $allelements['exacompUseAutoCompetences'];
    //        $exacompElement = $mform->_elements[$exacompElementInd];
    //        unset($mform->_elements[$exacompElementInd]);
    //        if (array_key_exists('availabilityconditionsjson', $allelements)) {
    //            $avacondintionsElementInd = $allelements['availabilityconditionsjson'];
    //            // go insert
    //            array_splice($mform->_elements, $avacondintionsElementInd, 0, array($exacompElement)); // splice in at position 3
    //
    //            // reformat indexes
    //            foreach ($mform->_elements as $key => $el) {
    //                if ($el->_attributes && $el->_attributes['name']) {
    //                    $mform->_elementIndex[$el->_attributes['name']] = $key;
    //                }else if($el->_name){
    //                    $mform->_elementIndex[$el->_name] = $key;
    //                }
    //            }
    //        }
    //    }
    return;
}

// deprecated, it is now done in observer.php
///**
// * exabis field of the course module.
// *
// * @param stdClass $data Data from the form submission.
// * @param stdClass $course The course.
// * @return stdClass
// */
//function block_exacomp_coursemodule_edit_post_actions($data, $course) {
//    global $CFG, $DB;
//    //    if (!empty($CFG->enableavailability)) {
//    //        $DB->delete_records('block_exacompcmsettings', ['name' => 'exacompUseAutoCompetences']);
//    //        if (isset($data->exacompUseAutoCompetences)) {
//    //            $insert = new stdClass();
//    //            $insert->coursemoduleid = $data->coursemodule;
//    //            $insert->name = 'exacompUseAutoCompetences';
//    //            $insert->value = 1;
//    //            $DB->insert_record('block_exacompcmsettings', $insert);
//    //        }
//    //    }
//
//    block_exacomp_check_relatedactivitydata($data->coursemodule, $data->name);
//
//    return $data;
//}

// deprecated, it is now done in observer.php
///**
// * no any possibility to insert own hook for inplace_editable. So we need to make via top level hook
// *
// * @param stdClass $externalfunctioninfo
// * @param array $params
// * @return bool
// */
//function block_exacomp_override_webservice_execution($externalfunctioninfo, $params) {
//    if (
//        $externalfunctioninfo->name == 'core_update_inplace_editable'
//        && $externalfunctioninfo->classname == 'core_external'
//        && $externalfunctioninfo->methodname == 'update_inplace_editable'
//    ) {
//        $component = $params[0];
//        $itemtype = $params[1];
//        if ($component == 'core_course') {
//            if ($itemtype == 'activityname') {
//                block_exacomp_check_relatedactivitydata($params[2], $params[3]);
//            }
//        }
//    }
//    return false; // false - call original function!
//}

// deprecated, it is now done in observer.php
///**
// * delete relations: competence-activity
// *
// * @param stdClass $cm
// */
//function block_exacomp_pre_course_module_delete($cm) {
//
//    // Sometimes records will not delete immediately. It is if current Moodle installation has some plugin with enabled async deleting.
//    // In this case exacomp relations will be deleted during adhoc task
//    // for example - RECICLER BIN tool - it is ENABLED by default!
//    // also there can be other plugins, you can find them by search 'course_module_background_deletion_recommended' in the code
//    // TODO: may be it is possible to do with webservice hook?
//
//    block_exacomp_checkfordelete_relatedactivity($cm->id);
//
//    return true;
//}


// possibility to look into this way 'cm_info_dynamic'... now it is not success
//function block_exacomp_cm_info_dynamic() {

//}


/**
 * Inject the exacomp element into all moodle module settings forms.
 *
 * @param moodleform $formwrapper The moodle quickforms wrapper object.
 * @param MoodleQuickForm $mform The actual form object (required to modify the form).
 */
function block_exacomp_coursemodule_definition_after_data($formwrapper, $mform) {
    global $CFG, $COURSE, $DB, $PAGE;


    // add button if exaquest is active in this course
    if (is_exacomp_active_in_course() && $mform->_formName == 'mod_hvp_mod_form' && property_exists($formwrapper, 'dakoraplus')) {
        ?>
        <script type="text/javascript">
            function changeFormActionExacomp() {
                document.getElementsByClassName("mform")[0].action = "../exacomp/modedit.php";
            }
        </script>
        <?php
        //$mform->removeElement("tags");
        //$mform->removeElement("submitbutton2");
        // the submitbuttons are not there yet ==> cannot remove them here
        $mform->addElement('submit', 'savehvpactivity', get_string('save_hvp_activity', 'block_exacomp'), 'onClick="changeFormActionExacomp()"');
    }

    return;
}
