<?php

/**
 * Page to edit the question bank
 *
 * @package    moodlecore
 * @subpackage questionbank
 * @copyright  1999 onwards Martin Dougiamas {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\event\question_category_viewed;

require __DIR__ . '/inc.php';
require_once(__DIR__ . '/questiontodescriptor/exacomp_view.php');

global $DB, $CFG, $PAGE, $OUTPUT, $COURSE, $USER;

$PAGE->requires->jquery();
$PAGE->requires->js('/blocks/exacomp/javascript/exacomp.js', true);
$PAGE->requires->js('/blocks/exacomp/javascript/simpletreemenu/simpletreemenu.js', true);
$PAGE->requires->css('/blocks/exacomp/javascript/simpletreemenu/simpletree.css');
$PAGE->requires->css('/blocks/exacomp/css/colorbox.css');
$PAGE->requires->js('/blocks/exacomp/javascript/jquery.colorbox.js', true);

//require_once(__DIR__ . '/../config.php');
require_once($CFG->dirroot . '/question/editlib.php');

require_once __DIR__ . '/classes/data.php';

$courseid = required_param('courseid', PARAM_INT);
//$moduleid = required_param('moduleid', PARAM_INT);
$action = optional_param("action", "", PARAM_TEXT);
$descs = optional_param_array('desc', [], PARAM_INT);
$questid = optional_param('questid', '', PARAM_RAW);
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_simplehtml', $courseid);
}

if ($action == 'save') {
    require_sesskey();
    $DB->delete_records("block_exacompdescrquest_mm", array('questid' => $questid));
    foreach ($descs as $desc) {
        if (!$DB->record_exists("block_exacompdescrquest_mm", array('questid' => $questid, 'descrid' => $desc, 'courseid' => $courseid))) {
            $DB->insert_record("block_exacompdescrquest_mm", array('questid' => $questid, 'descrid' => $desc, 'courseid' => $courseid));
        }
    }
}

require_login($course);

$context = context_course::instance($courseid);
//$output = block_exacomp_get_renderer();

$page_identifier = 'tab_teacher_settings_questiontodescriptors';

list($thispageurl, $contexts, $cmid, $cm, $module, $pagevars) =
    question_edit_setup('questions', '/question/edit.php');

$slicemodulelist = false;
$columngroupnumber = null;
if (get_config('exacomp', 'disable_js_edit_activities')) {
    $columngroupnumber = optional_param('colgroupid', 0, PARAM_INT);
    if ($columngroupnumber > -1) { // -1 - show all!
        $slicemodulelist = true;
        $slicestartposition = $columngroupnumber * BLOCK_EXACOMP_MODULES_PER_COLUMN;
    }
}

$page_params = array('courseid' => $courseid);
if ($columngroupnumber !== null) {
    $page_params['colgroupid'] = $columngroupnumber;
}
/* PAGE URL - MUST BE CHANGED */
$url = new moodle_url('/blocks/exacomp/question_to_descriptors.php', $page_params);
if (($lastchanged = optional_param('lastchanged', 0, PARAM_INT)) !== 0) {
    $url->param('lastchanged', $lastchanged);
}


$cache = cache::make('block_exacomp', 'visibility_cache');
$result = $cache->set('comptree', block_exacomp_build_comp_tree());


// Create a question in the default category.

$cat = question_make_default_categories($contexts->all());

$questionbank = new core_question\local\bank\exacomp_view($contexts, $url, $COURSE, $cm);
ob_start();

$PAGE->set_url($url);
$PAGE->set_heading(block_exacomp_get_string('blocktitle'));
//$streditingquestions = get_string('editquestions', 'question');
//$PAGE->set_title(block_exacomp_get_string($streditingquestions));
$PAGE->set_title(block_exacomp_get_string($page_identifier));

block_exacomp_build_breadcrum_navigation($courseid);

// build tab navigation & print header
echo $OUTPUT->header($context, $courseid, 'tab_teacher_settings');
echo $OUTPUT->tabtree(block_exacomp_build_navigation_tabs_settings($courseid), $page_identifier);

//$context = $contexts->lowest();
//$streditingquestions = get_string('editquestions', 'question');
//$PAGE->set_title($streditingquestions);
//$PAGE->set_heading($COURSE->fullname);

// build breadcrumbs navigation

//// Print horizontal nav if needed.
//$renderer = $PAGE->get_renderer('core_question', 'bank');
//echo $renderer->extra_horizontal_navigation();

echo '<div class="questionbankwindow boxwidthwide boxaligncenter">';
$questionbank->display($pagevars, 'editq');
echo "</div>\n";

// Log the view of this category.
list($categoryid, $contextid) = explode(',', $pagevars['cat']);
$category = new stdClass();
$category->id = $categoryid;
$catcontext = context::instance_by_id($contextid);
$event = question_category_viewed::create_from_question_category_instance($category, $catcontext);
$event->trigger();

echo $OUTPUT->footer();
