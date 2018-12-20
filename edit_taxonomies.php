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
require_once $CFG->dirroot.'/lib/datalib.php';

$courseid = required_param('courseid', PARAM_INT);
$action = optional_param('action', "", PARAM_ALPHA);

if (! $course = $DB->get_record ( 'course', array ('id' => $courseid) )) {
	print_error ( 'invalidcourse', 'block_simplehtml', $courseid );
}

require_login($course);

$context = context_system::instance();
block_exacomp_require_admin($context);

$check = block_exacomp\data::has_data();
if (!$check) {
	redirect (new moodle_url('/blocks/exacomp/import.php', array('courseid' => $courseid)));
}

$page_identifier = 'tab_admin_taxonomies';

$PAGE->set_url('/blocks/exacomp/edit_taxonomies.php', array('courseid' => $courseid));
$PAGE->set_heading(block_exacomp_get_string('blocktitle'));
$PAGE->set_title(block_exacomp_get_string($page_identifier));

// Build breadcrumbs navigation.
block_exacomp_build_breadcrum_navigation($courseid);

// data save
if (isset($action)) {
    switch ($action) {
        case 'save':
            // save existing taxonomies
            if (isset($_POST['data'])) {
                $data = $_POST['data'];
                foreach ($data as $id => $taxonomytitle) {
                    $newtitle = trim($taxonomytitle);
                    if ($id > 0 && $newtitle) {
                        $DB->execute('UPDATE {'.BLOCK_EXACOMP_DB_TAXONOMIES.'} SET title =? WHERE id = ?', array($newtitle, $id));
                    }
                }
            }
            // add new taxonomies
            if (isset($_POST['datanew'])) {
                $data = $_POST['datanew'];
                foreach ($data as $id => $taxonomytitle) {
                    $newtitle = trim($taxonomytitle);
                    if ($newtitle) {
                        $sqlmaxsorting = "SELECT MAX(sorting) as sorting FROM {".BLOCK_EXACOMP_DB_TAXONOMIES."} WHERE source = ?";
                        $max_sorting = $DB->get_record_sql($sqlmaxsorting, array(BLOCK_EXACOMP_DATA_SOURCE_CUSTOM));
                        $sorting = intval($max_sorting->sorting) + 1;
                        $DB->insert_record(BLOCK_EXACOMP_DB_TAXONOMIES, (object)array(
                                'title' => $newtitle,
                                'sourceid' => 0,
                                'source' => BLOCK_EXACOMP_DATA_SOURCE_CUSTOM,
                                'parentid' => 0,
                                'sorting' => $sorting));
                    }
                }
            }
            redirect($CFG->wwwroot.'/blocks/exacomp/edit_taxonomies.php?courseid='.$courseid);
            die;
            break;
        case 'delete':
            $taxtodelete = required_param('taxid', PARAM_INT);
            $DB->delete_records(BLOCK_EXACOMP_DB_TAXONOMIES, ['id' => $taxtodelete]);
            redirect($CFG->wwwroot.'/blocks/exacomp/edit_taxonomies.php?courseid='.$courseid, block_exacomp_get_string('taxonomy_was_deleted'), null, 'info');
            die;
            break;
        case 'sorting':
            $taxtomove = required_param('taxid', PARAM_INT);
            $direction = optional_param('dir', 'down', PARAM_ALPHA);
            $taxonomies = block_exacomp_get_taxonomies(BLOCK_EXACOMP_DATA_SOURCE_CUSTOM);
            $neightbids = array(0, 0);
            $neightbsortings = array(0, 0);
            $savenext = false;
            $originsorting = 0;
            foreach ($taxonomies as $taxonomy) {
                if ($taxonomy->id == $taxtomove) {
                    $savenext = true;
                    $originsorting = $taxonomy->sorting;
                } else if ($savenext) {
                    // next item
                    $neightbids[1] = $taxonomy->id;
                    $neightbsortings[1] = $taxonomy->sorting;
                    break;
                } else {
                    // element before
                    $neightbids[0] = $taxonomy->id;
                    $neightbsortings[0] = $taxonomy->sorting;
                }
            }
            switch ($direction) {
                case 'down':
                    if ($neightbids[1] > 0) {
                        $DB->execute('UPDATE {'.BLOCK_EXACOMP_DB_TAXONOMIES.'} SET sorting = ? WHERE id = ?', array($neightbsortings[1], $taxtomove));
                        $DB->execute('UPDATE {'.BLOCK_EXACOMP_DB_TAXONOMIES.'} SET sorting = ? WHERE id = ?', array($originsorting, $neightbids[1]));
                    }
                    break;
                case 'up':
                    if ($neightbids[0] > 0) {
                        $DB->execute('UPDATE {'.BLOCK_EXACOMP_DB_TAXONOMIES.'} SET sorting = ? WHERE id = ?', array($neightbsortings[0], $taxtomove));
                        $DB->execute('UPDATE {'.BLOCK_EXACOMP_DB_TAXONOMIES.'} SET sorting = ? WHERE id = ?', array($originsorting, $neightbids[0]));
                    }
                    break;
            }
            redirect($CFG->wwwroot.'/blocks/exacomp/edit_taxonomies.php?courseid='.$courseid);
            die;
            break;
    }
}

// Build tab navigation & print header.
$output = block_exacomp_get_renderer();
echo $output->header($context, $courseid, 'tab_admin_settings');
echo $OUTPUT->tabtree(block_exacomp_build_navigation_tabs_admin_settings($courseid), $page_identifier);

/* CONTENT REGION */
if (block_exacomp_is_skillsmanagement()) {
	echo $output->notification(block_exacomp_trans('en:This settings is not available in skillsmanagement mode!'));
	echo $output->footer();
	exit;
}

// taxonomies from this Moodle
echo $output->edit_taxonomies($courseid);
// taxonomies from import
echo $output->imported_taxonomies($courseid);

/* END CONTENT REGION */
echo $output->footer();
