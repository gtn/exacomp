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
 * Tests for block_exacomp_check_competence_data_is_gained() / block_exacomp_get_comp_eval_gained(),
 * used by the "Zeitlicher Ablauf des Kompetenzerwerbs" (timeline) graph
 * (block_exacomp_renderer::timeline_graph()).
 *
 * @package    block_exacomp
 * @copyright  2026 GTN - Global Training Network GmbH <office@gtn-solutions.com>
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/blocks/exacomp/lib/lib.php');

/**
 * @group block_exacomp
 */
class block_exacomp_comp_eval_gained_testcase extends advanced_testcase {

    /** @var stdClass */
    protected $course;

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
        $this->course = $this->getDataGenerator()->create_course();

        // make sure no course specific assessment preconfiguration is selected, so that all
        // scheme getters fall back to the global (site) config that we set per test
        global $DB;
        $DB->delete_records('block_exacompsettings', ['courseid' => $this->course->id]);
    }

    /**
     * Helper: build a fake comp_eval-like data object.
     */
    protected function make_eval($role, $comptype, $value, $additionalinfo = null) {
        return (object)[
            'value' => $value,
            'additionalinfo' => $additionalinfo,
            'role' => $role,
            'comptype' => $comptype,
        ];
    }

    public function test_teacher_points_scheme() {
        // "Mix assessment" style config: Points, highest = 2, fail (negativ) = 0.
        set_config('assessment_comp_scheme', BLOCK_EXACOMP_ASSESSMENT_TYPE_POINTS, 'exacomp');
        set_config('assessment_points_limit', 2, 'exacomp');
        set_config('assessment_points_negativ', 0, 'exacomp');

        $courseid = $this->course->id;

        $this->assertFalse(block_exacomp_check_competence_data_is_gained(
            $this->make_eval(BLOCK_EXACOMP_ROLE_TEACHER, BLOCK_EXACOMP_TYPE_DESCRIPTOR, 0), $courseid));
        $this->assertTrue(block_exacomp_check_competence_data_is_gained(
            $this->make_eval(BLOCK_EXACOMP_ROLE_TEACHER, BLOCK_EXACOMP_TYPE_DESCRIPTOR, 1), $courseid));
        $this->assertTrue(block_exacomp_check_competence_data_is_gained(
            $this->make_eval(BLOCK_EXACOMP_ROLE_TEACHER, BLOCK_EXACOMP_TYPE_DESCRIPTOR, 2), $courseid));
        // missing evaluation (NULL) must never be gained
        $this->assertFalse(block_exacomp_check_competence_data_is_gained(
            $this->make_eval(BLOCK_EXACOMP_ROLE_TEACHER, BLOCK_EXACOMP_TYPE_DESCRIPTOR, null), $courseid));
    }

    public function test_student_default_emoji_scale() {
        // default (non verbose) self assessment scale: 1 (worst) .. 3 (best), 0/NULL = not evaluated
        set_config('assessment_SelfEval_useVerbose', 0, 'exacomp');
        // scheme of the teacher scale must not influence the student scale/threshold at all
        set_config('assessment_comp_scheme', BLOCK_EXACOMP_ASSESSMENT_TYPE_POINTS, 'exacomp');
        set_config('assessment_points_limit', 2, 'exacomp');
        set_config('assessment_points_negativ', 0, 'exacomp');

        $courseid = $this->course->id;

        $this->assertFalse(block_exacomp_check_competence_data_is_gained(
            $this->make_eval(BLOCK_EXACOMP_ROLE_STUDENT, BLOCK_EXACOMP_TYPE_DESCRIPTOR, null), $courseid));
        $this->assertFalse(block_exacomp_check_competence_data_is_gained(
            $this->make_eval(BLOCK_EXACOMP_ROLE_STUDENT, BLOCK_EXACOMP_TYPE_DESCRIPTOR, 1), $courseid));
        $this->assertTrue(block_exacomp_check_competence_data_is_gained(
            $this->make_eval(BLOCK_EXACOMP_ROLE_STUDENT, BLOCK_EXACOMP_TYPE_DESCRIPTOR, 2), $courseid));
        $this->assertTrue(block_exacomp_check_competence_data_is_gained(
            $this->make_eval(BLOCK_EXACOMP_ROLE_STUDENT, BLOCK_EXACOMP_TYPE_DESCRIPTOR, 3), $courseid));
    }

    public function test_student_custom_verbose_scale_mix_assessment() {
        // "Mix assessment" self assessment style: 4 custom verbose values
        // (does not apply / rather not true / rather applies / true), stored as 1..4.
        // Only 0/NULL and the lowest option (1) are not gained.
        set_config('assessment_SelfEval_useVerbose', 1, 'exacomp');
        set_config('assessment_selfEvalVerbose_comp_long', 'does not apply; rather not true; rather applies; true', 'exacomp');

        $courseid = $this->course->id;

        $this->assertFalse(block_exacomp_check_competence_data_is_gained(
            $this->make_eval(BLOCK_EXACOMP_ROLE_STUDENT, BLOCK_EXACOMP_TYPE_DESCRIPTOR, null), $courseid));
        $this->assertFalse(block_exacomp_check_competence_data_is_gained(
            $this->make_eval(BLOCK_EXACOMP_ROLE_STUDENT, BLOCK_EXACOMP_TYPE_DESCRIPTOR, 1), $courseid));
        $this->assertTrue(block_exacomp_check_competence_data_is_gained(
            $this->make_eval(BLOCK_EXACOMP_ROLE_STUDENT, BLOCK_EXACOMP_TYPE_DESCRIPTOR, 2), $courseid));
        $this->assertTrue(block_exacomp_check_competence_data_is_gained(
            $this->make_eval(BLOCK_EXACOMP_ROLE_STUDENT, BLOCK_EXACOMP_TYPE_DESCRIPTOR, 3), $courseid));
        $this->assertTrue(block_exacomp_check_competence_data_is_gained(
            $this->make_eval(BLOCK_EXACOMP_ROLE_STUDENT, BLOCK_EXACOMP_TYPE_DESCRIPTOR, 4), $courseid));
    }

    public function test_descriptor_vs_topic_use_their_own_scheme() {
        // descriptor uses POINTS (fail = 0), topic uses GRADE (fail >= 5)
        set_config('assessment_comp_scheme', BLOCK_EXACOMP_ASSESSMENT_TYPE_POINTS, 'exacomp');
        set_config('assessment_points_limit', 2, 'exacomp');
        set_config('assessment_points_negativ', 0, 'exacomp');

        set_config('assessment_topic_scheme', BLOCK_EXACOMP_ASSESSMENT_TYPE_GRADE, 'exacomp');
        set_config('assessment_grade_limit', 6, 'exacomp');
        set_config('assessment_grade_negativ', 5, 'exacomp');

        $courseid = $this->course->id;

        // descriptor: value field, POINTS scheme
        $this->assertTrue(block_exacomp_check_competence_data_is_gained(
            $this->make_eval(BLOCK_EXACOMP_ROLE_TEACHER, BLOCK_EXACOMP_TYPE_DESCRIPTOR, 1), $courseid));

        // topic: additionalinfo field, GRADE scheme - "value" must be ignored here
        $this->assertTrue(block_exacomp_check_competence_data_is_gained(
            $this->make_eval(BLOCK_EXACOMP_ROLE_TEACHER, BLOCK_EXACOMP_TYPE_TOPIC, 0, 2), $courseid));
        $this->assertFalse(block_exacomp_check_competence_data_is_gained(
            $this->make_eval(BLOCK_EXACOMP_ROLE_TEACHER, BLOCK_EXACOMP_TYPE_TOPIC, 0, 5), $courseid));
        $this->assertFalse(block_exacomp_check_competence_data_is_gained(
            $this->make_eval(BLOCK_EXACOMP_ROLE_TEACHER, BLOCK_EXACOMP_TYPE_TOPIC, 0, null), $courseid));
    }

    public function test_verbose_scheme_lowerisbetter() {
        set_config('assessment_comp_scheme', BLOCK_EXACOMP_ASSESSMENT_TYPE_VERBOSE, 'exacomp');
        set_config('assessment_verbose_options', 'a, b, c, d', 'exacomp');
        set_config('assessment_verbose_negative', 2, 'exacomp');
        set_config('assessment_verbose_lowerisbetter', 1, 'exacomp'); // lowest index = best

        $courseid = $this->course->id;

        // lowerisbetter: values <= limit are considered "good"/negative-threshold not reached...
        // negative if value >= limit(2), so 0,1 not negative (gained), 2,3 negative (not gained)
        $this->assertTrue(block_exacomp_check_competence_data_is_gained(
            $this->make_eval(BLOCK_EXACOMP_ROLE_TEACHER, BLOCK_EXACOMP_TYPE_DESCRIPTOR, 0), $courseid));
        $this->assertTrue(block_exacomp_check_competence_data_is_gained(
            $this->make_eval(BLOCK_EXACOMP_ROLE_TEACHER, BLOCK_EXACOMP_TYPE_DESCRIPTOR, 1), $courseid));
        $this->assertFalse(block_exacomp_check_competence_data_is_gained(
            $this->make_eval(BLOCK_EXACOMP_ROLE_TEACHER, BLOCK_EXACOMP_TYPE_DESCRIPTOR, 2), $courseid));
    }

    public function test_yesno_scheme() {
        set_config('assessment_comp_scheme', BLOCK_EXACOMP_ASSESSMENT_TYPE_YESNO, 'exacomp');

        $courseid = $this->course->id;

        $this->assertFalse(block_exacomp_check_competence_data_is_gained(
            $this->make_eval(BLOCK_EXACOMP_ROLE_TEACHER, BLOCK_EXACOMP_TYPE_DESCRIPTOR, 0), $courseid));
        $this->assertTrue(block_exacomp_check_competence_data_is_gained(
            $this->make_eval(BLOCK_EXACOMP_ROLE_TEACHER, BLOCK_EXACOMP_TYPE_DESCRIPTOR, 1), $courseid));
    }

    public function test_null_vs_zero_is_not_evaluated() {
        set_config('assessment_comp_scheme', BLOCK_EXACOMP_ASSESSMENT_TYPE_POINTS, 'exacomp');
        set_config('assessment_points_limit', 2, 'exacomp');
        set_config('assessment_points_negativ', 0, 'exacomp');

        $courseid = $this->course->id;

        // A zero teacher value is not gained, whether it is stored directly or represented as NULL.
        $this->assertFalse(block_exacomp_check_competence_data_is_gained(
            $this->make_eval(BLOCK_EXACOMP_ROLE_TEACHER, BLOCK_EXACOMP_TYPE_DESCRIPTOR, 0), $courseid));
        $this->assertFalse(block_exacomp_check_competence_data_is_gained(
            $this->make_eval(BLOCK_EXACOMP_ROLE_TEACHER, BLOCK_EXACOMP_TYPE_DESCRIPTOR, null), $courseid));
    }

    /**
     * Full round trip through block_exacomp_set_comp_eval() / block_exacomp_get_comp_eval_gained(),
     * reproducing the "Mix assessment" scenario from the bug report:
     * Teacher: comp1=0, comp2=1, comp3=2 (Points, fail=0) => teacher count = 2
     * Student: comp1=0, comp2=1, comp3=2 (custom verbose 1..4) => student count = 2
     */
    public function test_set_and_get_comp_eval_gained_roundtrip() {
        set_config('assessment_comp_scheme', BLOCK_EXACOMP_ASSESSMENT_TYPE_POINTS, 'exacomp');
        set_config('assessment_points_limit', 2, 'exacomp');
        set_config('assessment_points_negativ', 0, 'exacomp');
        set_config('assessment_SelfEval_useVerbose', 1, 'exacomp');
        set_config('assessment_selfEvalVerbose_comp_long', 'does not apply; rather not true; rather applies; true', 'exacomp');

        $courseid = $this->course->id;
        $student = $this->getDataGenerator()->create_user();

        $descriptorids = [9001, 9002, 9003];
        $teachervalues = [0, 1, 2]; // zero remains a stored teacher value; negative values are cleared
        $studentvalues = [1, 2, 3]; // shifted by one compared to the (0-based) bug report example,
        // because the real value domain used by get_student_eval_items() is 1-based (0/NULL = not evaluated)

        foreach ($descriptorids as $i => $compid) {
            // savegradinghistory=false: keep this test focused on the gained-predicate, not on
            // the (unrelated) gradinghistory text rendering
            block_exacomp_set_comp_eval($courseid, BLOCK_EXACOMP_ROLE_TEACHER, $student->id, BLOCK_EXACOMP_TYPE_DESCRIPTOR, $compid, [
                'value' => $teachervalues[$i],
            ], false);
            block_exacomp_set_comp_eval($courseid, BLOCK_EXACOMP_ROLE_STUDENT, $student->id, BLOCK_EXACOMP_TYPE_DESCRIPTOR, $compid, [
                'value' => $studentvalues[$i],
            ], false);
        }

        $teachergained = 0;
        $studentgained = 0;
        foreach ($descriptorids as $compid) {
            if (block_exacomp_get_comp_eval_gained($courseid, BLOCK_EXACOMP_ROLE_TEACHER, $student->id, BLOCK_EXACOMP_TYPE_DESCRIPTOR, $compid)) {
                $teachergained++;
            }
            if (block_exacomp_get_comp_eval_gained($courseid, BLOCK_EXACOMP_ROLE_STUDENT, $student->id, BLOCK_EXACOMP_TYPE_DESCRIPTOR, $compid)) {
                $studentgained++;
            }
        }

        $this->assertEquals(2, $teachergained);
        $this->assertEquals(2, $studentgained);
    }

    public function test_missing_evaluations_are_not_gained() {
        set_config('assessment_comp_scheme', BLOCK_EXACOMP_ASSESSMENT_TYPE_POINTS, 'exacomp');
        set_config('assessment_points_limit', 2, 'exacomp');
        set_config('assessment_points_negativ', 0, 'exacomp');

        $courseid = $this->course->id;
        $student = $this->getDataGenerator()->create_user();

        $this->assertNull(block_exacomp_get_comp_eval_gained(
            $courseid, BLOCK_EXACOMP_ROLE_TEACHER, $student->id, BLOCK_EXACOMP_TYPE_DESCRIPTOR, 12345));
        $this->assertNull(block_exacomp_get_comp_eval_gained(
            $courseid, BLOCK_EXACOMP_ROLE_STUDENT, $student->id, BLOCK_EXACOMP_TYPE_DESCRIPTOR, 12345));
    }
}
