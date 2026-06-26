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

/**
 * Upgrade helper functions for block_exacomp.
 *
 * Shared between db/upgrade.php and db/install.php so seed data is defined in one place.
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Seed the block_exacomp_assessment_cfgs table with default preconfigurations.
 *
 * Only inserts when the table is empty so that admin changes survive plugin upgrades.
 */
function block_exacomp_seed_assessment_configurations() {
    global $DB;

    if ($DB->count_records('block_exacomp_assessment_cfgs')) {
        return;
    }

    $records = array(
        array(
            'name' => 'Gemeinschaftsschule',
            'sortorder' => 1,
            'assessment_example_scheme' => 2,
            'assessment_example_diff_level' => 1,
            'assessment_example_self_eval' => 1,
            'assessment_childcomp_scheme' => 2,
            'assessment_childcomp_diff_level' => 1,
            'assessment_childcomp_self_eval' => 1,
            'assessment_comp_scheme' => 1,
            'assessment_comp_diff_level' => 1,
            'assessment_comp_self_eval' => 1,
            'assessment_topic_scheme' => 1,
            'assessment_topic_diff_level' => 1,
            'assessment_topic_self_eval' => 1,
            'assessment_subject_scheme' => 1,
            'assessment_subject_diff_level' => 1,
            'assessment_subject_self_eval' => 1,
            'assessment_theme_scheme' => 1,
            'assessment_theme_diff_level' => 1,
            'assessment_theme_self_eval' => 1,
            'assessment_points_limit' => 10,
            'assessment_points_negativ' => 4,
            'assessment_grade_limit' => 6,
            'assessment_grade_negativ' => 5,
            'assessment_grade_verbose' => 'sehr gut, gut, befriedigend, ausreichend, mangelhaft, ungenügend',
            'assessment_diff_level_options' => 'G,M,E,Z',
            'assessment_verbose_options' => 'nicht erreicht, teilweise erreicht, überwiegend erreicht, vollständig erreicht',
            'assessment_verbose_negative' => 0,
            'assessment_verbose_options_short' => 'ne, te, üe, ve',
            'assessment_verbose_lowerisbetter' => 0,
        ),
        array(
            'name' => 'berufliche Schulen',
            'sortorder' => 2,
            'assessment_example_scheme' => 3,
            'assessment_example_diff_level' => 0,
            'assessment_example_self_eval' => 1,
            'assessment_childcomp_scheme' => 3,
            'assessment_childcomp_diff_level' => 0,
            'assessment_childcomp_self_eval' => 1,
            'assessment_comp_scheme' => 3,
            'assessment_comp_diff_level' => 0,
            'assessment_comp_self_eval' => 1,
            'assessment_topic_scheme' => 3,
            'assessment_topic_diff_level' => 0,
            'assessment_topic_self_eval' => 1,
            'assessment_subject_scheme' => 3,
            'assessment_subject_diff_level' => 0,
            'assessment_subject_self_eval' => 1,
            'assessment_theme_scheme' => 3,
            'assessment_theme_diff_level' => 0,
            'assessment_theme_self_eval' => 1,
            'assessment_points_limit' => 10,
            'assessment_points_negativ' => 4,
            'assessment_grade_limit' => 6,
            'assessment_grade_negativ' => 5,
            'assessment_grade_verbose' => 'sehr gut, gut, befriedigend, ausreichend, mangelhaft, ungenügend',
            'assessment_diff_level_options' => 'A,B,C',
            'assessment_verbose_options' => 'nicht erreicht, teilweise erreicht, überwiegend erreicht, vollständig erreicht',
            'assessment_verbose_negative' => 0,
            'assessment_verbose_options_short' => 'ne, te, üe, ve',
            'assessment_verbose_lowerisbetter' => 0,
        ),
        array(
            'name' => '4.0 Skala',
            'sortorder' => 3,
            'assessment_example_scheme' => 2,
            'assessment_example_diff_level' => 1,
            'assessment_example_self_eval' => 1,
            'assessment_childcomp_scheme' => 2,
            'assessment_childcomp_diff_level' => 1,
            'assessment_childcomp_self_eval' => 1,
            'assessment_comp_scheme' => 2,
            'assessment_comp_diff_level' => 1,
            'assessment_comp_self_eval' => 1,
            'assessment_topic_scheme' => 2,
            'assessment_topic_diff_level' => 1,
            'assessment_topic_self_eval' => 1,
            'assessment_subject_scheme' => 2,
            'assessment_subject_diff_level' => 1,
            'assessment_subject_self_eval' => 1,
            'assessment_theme_scheme' => 2,
            'assessment_theme_diff_level' => 1,
            'assessment_theme_self_eval' => 1,
            'assessment_points_limit' => 10,
            'assessment_points_negativ' => 4,
            'assessment_grade_limit' => 6,
            'assessment_grade_negativ' => 5,
            'assessment_grade_verbose' => 'sehr gut, gut, befriedigend, ausreichend, mangelhaft, ungenügend',
            'assessment_diff_level_options' => 'G,M,E,Z',
            'assessment_verbose_options' => 'WESENTLICHE mit Hilfe erreicht, WESENTLICHE in Ansätzen erfüllt, WESENTLICHE teilweise erreicht, WESENTLICHE überwiegend erreicht, WESENTLICHE erreicht, WESENTLICHE übertroffen, WESENTLICHE weit übertroffen',
            'assessment_verbose_negative' => 0,
            'assessment_verbose_options_short' => 'mhe, iae, te, üe, e, ü, wü',
            'assessment_verbose_lowerisbetter' => 0,
        ),
        array(
            'name' => 'Dakora +',
            'sortorder' => 4,
            'assessment_example_scheme' => 2,
            'assessment_example_diff_level' => 0,
            'assessment_example_self_eval' => 1,
            'assessment_childcomp_scheme' => 2,
            'assessment_childcomp_diff_level' => 0,
            'assessment_childcomp_self_eval' => 1,
            'assessment_comp_scheme' => 2,
            'assessment_comp_diff_level' => 0,
            'assessment_comp_self_eval' => 1,
            'assessment_topic_scheme' => 2,
            'assessment_topic_diff_level' => 0,
            'assessment_topic_self_eval' => 1,
            'assessment_subject_scheme' => 2,
            'assessment_subject_diff_level' => 0,
            'assessment_subject_self_eval' => 1,
            'assessment_theme_scheme' => 2,
            'assessment_theme_diff_level' => 0,
            'assessment_theme_self_eval' => 1,
            'assessment_points_limit' => 10,
            'assessment_points_negativ' => 4,
            'assessment_grade_limit' => 6,
            'assessment_grade_negativ' => 5,
            'assessment_grade_verbose' => '😊,😐,🙁',
            'assessment_diff_level_options' => 'A,B,C',
            'assessment_verbose_options' => '😊,😐,🙁',
            'assessment_verbose_negative' => 2,
            'assessment_verbose_options_short' => '😊,😐,🙁',
            'assessment_verbose_lowerisbetter' => 1,
        ),
        array(
            'name' => 'Mix assessment',
            'sortorder' => 5,
            'assessment_example_scheme' => 0,
            'assessment_example_diff_level' => 0,
            'assessment_example_self_eval' => 0,
            'assessment_childcomp_scheme' => 3,
            'assessment_childcomp_diff_level' => 1,
            'assessment_childcomp_self_eval' => 1,
            'assessment_comp_scheme' => 3,
            'assessment_comp_diff_level' => 1,
            'assessment_comp_self_eval' => 1,
            'assessment_topic_scheme' => 0,
            'assessment_topic_diff_level' => 1,
            'assessment_topic_self_eval' => 1,
            'assessment_subject_scheme' => 0,
            'assessment_subject_diff_level' => 0,
            'assessment_subject_self_eval' => 0,
            'assessment_theme_scheme' => 0,
            'assessment_theme_diff_level' => 0,
            'assessment_theme_self_eval' => 0,
            'assessment_points_limit' => 2,
            'assessment_points_negativ' => 0,
            'assessment_grade_limit' => 2,
            'assessment_grade_negativ' => 0,
            'assessment_grade_verbose' => 'NA, ECA, A',
            'assessment_diff_level_options' => 'NA, ECA, A',
            'assessment_verbose_options' => 'NA, ECA, A',
            'assessment_verbose_negative' => 0,
            'assessment_verbose_options_short' => 'NA, ECA, A',
            'assessment_verbose_lowerisbetter' => 0,
        ),
    );

    foreach ($records as $record) {
        $DB->insert_record('block_exacomp_assessment_cfgs', (object)$record);
    }
}
