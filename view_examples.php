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

global $DB, $OUTPUT, $PAGE;

$outputContent = '';
$courseid = required_param('courseid', PARAM_INT);
$courseid_for_tree = $courseid;
$sort = optional_param('sort', "desc", PARAM_ALPHA);
$style = optional_param('style', 0, PARAM_INT);
$action = optional_param('action', '', PARAM_RAW);

if (!$course = $DB->get_record('course', array(
    'id' => $courseid,
))
) {
    print_error('invalidcourse', 'block_simplehtml', $courseid);
}

block_exacomp_require_login($course);

$context = context_course::instance($courseid);

// CHECK TEACHER
$isTeacher = block_exacomp_is_teacher($context);

$studentid = block_exacomp_get_studentid();

// save filtering (now in the course settings, as it was before)
$courseSettings = block_exacomp_get_settings_by_course($courseid);
if ($action == 'save_filtersettings') {
    $selectedTaxonomies = BLOCK_EXACOMP_SHOW_ALL_TAXONOMIES;
    if (isset($_POST['filteredtaxonomies'])) {
        $selectedTaxonomies = array_values(clean_param_array($_POST['filteredtaxonomies'], PARAM_TEXT));
    }
    if (isset($_POST['filterClear'])) {
        $selectedTaxonomies = BLOCK_EXACOMP_SHOW_ALL_TAXONOMIES;
    }
    $courseSettings->filteredtaxonomies = json_encode($selectedTaxonomies);
    block_exacomp_save_coursesettings($courseid, $courseSettings);
}

/* PAGE IDENTIFIER - MUST BE CHANGED. Please use string identifier from lang file */
$page_identifier = 'tab_examples';

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/view_examples.php', array(
    'courseid' => $courseid,
    'style' => $style,
));
$PAGE->set_heading(block_exacomp_get_string('blocktitle'));
$PAGE->set_title(block_exacomp_get_string($page_identifier));

// build breadcrumbs navigation
block_exacomp_build_breadcrum_navigation($courseid);

// build tab navigation & print header
$output = block_exacomp_get_renderer();

$isPrintView = false;
if (optional_param('print', false, PARAM_BOOL)) {
    $output->print = true;
    $isPrintView = true;
    $html_tables = array();
    $html_headers = array(); // TODO:
}

$outputContent .= $output->header_v2($page_identifier);

$outputContent .= $output->button_box('window.open(location.href+\'&print=1\');', '');

/*if ($show_all_examples != 0) {
	$courseid_for_tree = 0;
}*/

/* CONTENT REGION */
$courseSettings = block_exacomp_get_settings_by_course($courseid); // Reload settings
$outputContent .= $output->view_example_header($courseSettings, $style);

switch ($style) {
    case 0:
        $tree = block_exacomp_build_example_association_tree($courseid, array(), 0, 0, true);
        if ($isPrintView) {
            $html_tables[] = $output->competence_based_list_tree($tree, $isTeacher, false);
        } else {
            $outputContent .= $output->competence_based_list_tree($tree, $isTeacher, false);
        }

        //Crossubjects and crossubjectfiles
        //$crossubject_tree = block_exacomp_build_crossubject_example_tree($courseid, array(), 0, 0, true);

        /*
        $crossubjects = block_exacomp_get_cross_subjects_by_course($courseid);
        echo $output->print_crosssubjects_and_examples($crossubjects, $isTeacher, false);
        */
        break;
    case 1:
        //could be optimized together with block_exacomp_build_example_tree
        //non critical - only 1 additional query for whole loading process
        $examples = block_exacomp_get_examples_by_course($courseid);
        // for order and additional condition
        $examples = \block_exacomp\example::get_objects_sql("
            SELECT DISTINCT e.*
                FROM {" . BLOCK_EXACOMP_DB_EXAMPLES . "} e
                WHERE e.id IN (" . implode(',', array_keys($examples)) . ") "
            . (!$isTeacher ? " AND e.is_teacherexample = 0 " : "")
        );
        /*$examples = \block_exacomp\example::get_objects_sql("
            SELECT DISTINCT e.*
            FROM {" . BLOCK_EXACOMP_DB_COURSETOPICS . "} ct
            JOIN {" . BLOCK_EXACOMP_DB_DESCTOPICS . "} dt ON ct.topicid = dt.topicid
            JOIN {" . BLOCK_EXACOMP_DB_DESCEXAMP . "} de ON dt.descrid = de.descrid
            JOIN {" . BLOCK_EXACOMP_DB_EXAMPLES . "} e ON e.id = de.exampid
            WHERE ct.courseid = ?
            " . (!$isTeacher ? " AND e.is_teacherexample = 0 " : "") . "
            ORDER BY e.title
        ", [$courseid]);*/

        if (!$isTeacher) {
            $examples = array_filter($examples, function($example) use ($courseid, $studentid) {
                return block_exacomp_is_example_visible($courseid, $example, $studentid);
            });
        }

        if ($isPrintView) {
            $html_tables[] = $output->example_based_list_tree($examples);
        } else {
            $outputContent .= $output->example_based_list_tree($examples);
        }
        break;
    case 2:
        // get all crosssubjects or for student
        if (block_exacomp_is_teacher() || block_exacomp_is_admin()) {
            $crosssubs = block_exacomp_get_cross_subjects_by_course($courseid);
        } else {
            $crosssubs = block_exacomp_get_cross_subjects_by_course($courseid, $USER->id);
        }
        $outputContent .= html_writer::start_tag("table", array("class" => 'rg2'));
        foreach ($crosssubs as $cross) {

            $crossContent = '';
            //get files specifically for this cross:
            $examples = block_exacomp_get_examples_for_crosssubject($cross->id);
            //get files from competencies that are added to this cross:
            $examples += \block_exacomp\example::get_objects_sql("
                SELECT DISTINCT e.*
                FROM {" . BLOCK_EXACOMP_DB_DESCCROSS . "} dc
                    JOIN {" . BLOCK_EXACOMP_DB_DESCEXAMP . "} de ON dc.descrid = de.descrid
                    JOIN {" . BLOCK_EXACOMP_DB_EXAMPLES . "} e ON e.id = de.exampid
                WHERE dc.crosssubjid = ?
                ORDER BY e.title
            ", [$cross->id]);

            //get files from the childcompetencies of the competencies that are added
            //get descriptors and check if they are parents
            //if they are parent --> get the examples of their children
            $assoc_descriptors = block_exacomp_get_descriptors_for_cross_subject($courseid, $cross);

            foreach ($assoc_descriptors as $descriptor) {
                if ($descriptor->parentid == 0) {
                    $childdescriptors = block_exacomp_get_child_descriptors($descriptor, $courseid);
                    foreach ($childdescriptors as $childdescriptor) {
                        $examples = array_merge($examples, $childdescriptor->examples);
                    }
                }
            }

            if (!$isTeacher) {
                $examples = array_filter($examples, function($example) use ($courseid, $studentid) {
                    return block_exacomp_is_example_visible($courseid, $example, $studentid);
                });
            }
            $crossContent .= html_writer::start_tag("tr", array("class" => "rg2-level-0 rg2 rg2-header highlight"));
            $crossContent .= html_writer::start_tag("td", array("class" => "rg2-arrow rg2-indent"));
            $crossContent .= '<div>' . $cross->title . '</div>';
            $crossContent .= html_writer::end_tag("td");
            $crossContent .= html_writer::end_tag("tr");
            $crossContent .= html_writer::start_tag("tr", array("class" => "rg2-level-1 rg2"));
            $crossContent .= html_writer::start_tag("td", array("class" => "rg2-indent"));
            if ($isPrintView) {
                $html_headers[] = $cross->title;
                $html_tables[] = $output->cross_based_list_tree($examples, $cross->id);
            } else {
                $crossContent .= $output->cross_based_list_tree($examples, $cross->id);
            }
            $crossContent .= html_writer::end_tag("td");
            $crossContent .= html_writer::end_tag("tr");
            $outputContent .= $crossContent;
        }
        $outputContent .= html_writer::end_tag("table");
        break;
}

if ($isPrintView) {
    block_exacomp\printer::view_examples($html_headers, $html_tables, $style);
}

echo $outputContent;
/* END CONTENT REGION */
echo $output->footer();

