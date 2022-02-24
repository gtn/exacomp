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

$courseid = required_param('courseid', PARAM_INT);
$badgeid = optional_param('badgeid', 0, PARAM_INT);
$action = optional_param('action', "", PARAM_ALPHA);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_simplehtml', $courseid);
}

block_exacomp_require_login($course);

$context = context_course::instance($courseid);

/* PAGE IDENTIFIER - MUST BE CHANGED. Please use string identifier from lang file */
$page_identifier = 'tab_teacher_settings_badges';

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/edit_badges.php', array('courseid' => $courseid));
$PAGE->set_heading(block_exacomp_get_string('blocktitle'));
$PAGE->set_title(block_exacomp_get_string($page_identifier));

// build breadcrumbs navigation
block_exacomp_build_breadcrum_navigation($courseid);

// build tab navigation & print header
$output = block_exacomp_get_renderer();
echo $output->header($context, $courseid, 'tab_teacher_settings');
echo $OUTPUT->tabtree(block_exacomp_build_navigation_tabs_settings($courseid), $page_identifier);

/* CONTENT REGION */

if ($badgeid && $badge = $DB->get_record('badge', array('id' => $badgeid))) {
    if ($action == 'save') {
        require_sesskey();
        $DB->delete_records('block_exacompdescbadge_mm', array("badgeid" => $badgeid));
        if (!empty($_POST['descriptors'])) {
            foreach ($_POST['descriptors'] as $value => $tmp) {
                $DB->insert_record('block_exacompdescbadge_mm', array("badgeid" => $badgeid, "descid" => intval($value)));
            }
        }
    } else {
        $tree = block_exacomp_get_competence_tree($courseid);
        $badge->descriptors = block_exacomp_get_badge_descriptors($badge->id);
        echo $output->edit_badges($tree, $badge);
        echo $OUTPUT->footer();
        return;
    }
}

$badges = badges_get_badges(BADGE_TYPE_COURSE, $courseid);

if (!$badges) {
    echo $OUTPUT->box(text_to_html(block_exacomp_get_string("no_badges_yet")));
    echo $OUTPUT->footer();
    return;
}

block_exacomp_award_badges($courseid);

foreach ($badges as $badge) {
    $descriptors = block_exacomp_get_badge_descriptors($badge->id);
    $descriptors = $DB->get_records_sql('
		SELECT d.*
		FROM {block_exacompdescriptors} d
		JOIN {block_exacompdescbadge_mm} db ON d.id=db.descid AND db.badgeid=?
	', array($badge->id));

    $context = context_course::instance($badge->courseid);
    echo $output->badge($badge, $descriptors, $context);
}

/* END CONTENT REGION */
echo $output->footer();
