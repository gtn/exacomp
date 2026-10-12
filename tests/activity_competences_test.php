<?php
// This file is part of Moodle - http://moodle.org/
// Licensed under the GNU GPL v3 or later.
namespace block_exacomp;

use block_exacomp\externallib\activity_competences as service;

defined('MOODLE_INTERNAL') || die();

/**
 * Contract and authorization tests for safe activity competence additions.
 * @package block_exacomp
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3 or later
 */
// Exacomp's existing course/module callbacks load legacy external classes.
// Isolate the fixtures so those aliases cannot affect other Moodle tests.
#[\PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses]
#[\PHPUnit\Framework\Attributes\PreserveGlobalState(false)]
#[\PHPUnit\Framework\Attributes\Group('block_exacomp')]
final class activity_competences_test extends \advanced_testcase {
    private $course;
    private $activity;
    private $exampleid;
    private $root;
    private $child;
    private $grandchild;

    protected function setUp(): void {
        global $DB;
        parent::setUp();
        $this->resetAfterTest();
        $this->course = $this->getDataGenerator()->create_course();
        $this->activity = $this->getDataGenerator()->create_module('page', ['course' => $this->course->id]);
        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($teacher->id, $this->course->id, 'editingteacher');
        $this->setUser($teacher);
        $topic = $DB->insert_record('block_exacomptopics', ['title' => 'Configured topic']);
        $DB->insert_record('block_exacompcoutopi_mm', ['courseid' => $this->course->id, 'topicid' => $topic]);
        $this->root = $DB->insert_record('block_exacompdescriptors', ['title' => 'Root']);
        $this->child = $DB->insert_record('block_exacompdescriptors', ['title' => 'Child', 'parentid' => $this->root]);
        $level = $DB->insert_record('block_exacompniveaus', ['title' => 'Level 1']);
        $DB->set_field('block_exacompdescriptors', 'niveauid', $level, ['id' => $this->child]);
        $this->grandchild = $DB->insert_record('block_exacompdescriptors', ['title' => 'Grandchild', 'parentid' => $this->child]);
        $DB->insert_record('block_exacompdescrtopic_mm', ['descrid' => $this->root, 'topicid' => $topic]);
        // A different creator must not hide the material or prevent a course teacher adding links.
        $creator = $this->getDataGenerator()->create_user();
        $this->exampleid = $DB->insert_record('block_exacompexamples', [
            'title' => 'Unlinked material', 'description' => 'Keep me', 'source' => 2,
            'creatorid' => $creator->id, 'timestamp' => 12345, 'externalurl' => 'https://example.test',
            'activityid' => $this->activity->cmid, 'courseid' => $this->course->id,
        ]);
        $DB->insert_record('block_exacompexampvisibility', [
            'courseid' => $this->course->id, 'exampleid' => $this->exampleid, 'studentid' => 0, 'visible' => 0,
        ]);
    }

    private function clean($method, $result) {
        return service::clean_returnvalue(service::{$method . '_returns'}(), $result);
    }

    private function inspect() {
        return $this->clean('get_activity_competences', service::get_activity_competences(
            $this->course->id, $this->activity->cmid));
    }

    private function add($ids, $confirm = true) {
        return $this->clean('add_activity_competences', service::add_activity_competences(
            $this->course->id, $this->activity->cmid, $this->exampleid, $ids, $confirm));
    }

    public function test_complete_course_tree_without_material_or_visibility_filters(): void {
        global $DB;
        $DB->insert_record('block_exacompdescrvisibility', [
            'courseid' => $this->course->id, 'descrid' => $this->grandchild, 'studentid' => 0, 'visible' => 0,
        ]);
        $result = $this->clean('get_course_competences', service::get_course_competences($this->course->id));
        $this->assertEquals([$this->root, $this->child, $this->grandchild], array_column($result, 'id'));
        $this->assertEquals($this->child, $result[2]['parentid']);
        $this->assertSame('Level 1', json_decode($result[1]['niveau'])->title);
        $this->assertSame('unique', $this->inspect()['status']);
    }

    public function test_dry_run_addition_and_repeat_preserve_every_existing_record(): void {
        global $DB;
        $outside = $DB->insert_record('block_exacompdescriptors', ['title' => 'Inactive topic descriptor']);
        $DB->insert_record('block_exacompdescrexamp_mm', ['exampid' => $this->exampleid, 'descrid' => $outside]);
        $DB->insert_record('block_exacompdescrexamp_mm', [
            'exampid' => $this->exampleid, 'table_foreign' => 'cross', 'id_foreign' => 42, 'sorting' => 17,
        ]);
        // Generic descriptor representation must count as an existing link, too.
        $DB->insert_record('block_exacompdescrexamp_mm', [
            'exampid' => $this->exampleid, 'table_foreign' => 'descr', 'id_foreign' => $this->child,
        ]);
        $DB->insert_record('block_exacompsolutvisibility', [
            'courseid' => $this->course->id, 'exampleid' => $this->exampleid, 'studentid' => 0, 'visible' => 0,
        ]);
        $file = get_file_storage()->create_file_from_string([
            'contextid' => \context_system::instance()->id, 'component' => 'block_exacomp',
            'filearea' => 'example_task', 'itemid' => $this->exampleid, 'filepath' => '/', 'filename' => 'keep.txt',
        ], 'Keep file contents');
        $before = $this->inspect();
        $this->assertCount(3, $before['materials'][0]['relations']);
        $plan = $this->clean('plan_activity_competences', service::plan_activity_competences(
            $this->course->id, $this->activity->cmid, $this->exampleid, [$this->child, $this->grandchild]));
        $this->assertEquals([$this->grandchild], $plan['missingids']);
        $this->assertEquals([$this->child], $plan['existingids']);
        $this->assertSame($before, $this->inspect());
        $result = $this->add([$this->child, $this->grandchild, $this->grandchild]);
        $this->assertEquals([$this->grandchild], $result['addedids']);
        $after = $result['state']['materials'][0];
        foreach (['metadata', 'visibility', 'solutionvisibility', 'files'] as $field) {
            $this->assertSame($before['materials'][0][$field], $after[$field]);
        }
        $this->assertSame($before['materials'][0]['relations'], array_slice($after['relations'], 0, 3));
        $this->assertSame('Keep file contents', $file->get_content());
        $repeat = $this->add([$this->child, $this->grandchild]);
        $this->assertSame([], $repeat['addedids']);
        $this->assertSame($result['state'], $repeat['state']);
        $this->assertFalse($DB->record_exists('block_exacompdescrexamp_mm', [
            'exampid' => $this->exampleid, 'descrid' => $this->root]));
    }

    public function test_invalid_descriptor_rejects_whole_batch(): void {
        global $DB;
        $outside = $DB->insert_record('block_exacompdescriptors', ['title' => 'Other course']);
        foreach ([0, -1, 99999999, $outside] as $invalid) {
            $before = $this->inspect();
            try {
                $this->add([$this->child, $invalid]);
                $this->fail('Invalid descriptor accepted');
            } catch (\invalid_parameter_exception $e) {
                $this->assertSame($before, $this->inspect());
            }
        }
    }

    public function test_missing_and_ambiguous_materials_block_writes(): void {
        global $DB;
        $duplicate = $DB->insert_record('block_exacompexamples', [
            'activityid' => $this->activity->cmid, 'courseid' => $this->course->id]);
        $this->assertSame('ambiguous', $this->inspect()['status']);
        try {
            $this->add([$this->child]);
            $this->fail('Ambiguous activity accepted');
        } catch (\invalid_parameter_exception $e) {
            $this->assertSame(0, $DB->count_records('block_exacompdescrexamp_mm'));
        }
        $DB->delete_records('block_exacompexamples', ['id' => $duplicate]);
        $DB->delete_records('block_exacompexamples', ['id' => $this->exampleid]);
        $this->assertSame('missing', $this->inspect()['status']);
        $this->expectException(\invalid_parameter_exception::class);
        $this->add([$this->child]);
    }

    public function test_explicit_confirmation_is_required(): void {
        $this->expectException(\invalid_parameter_exception::class);
        $this->add([$this->child], false);
    }

    public function test_foreign_material_id_is_rejected(): void {
        $this->exampleid = 99999999;
        $this->expectException(\invalid_parameter_exception::class);
        $this->add([$this->child]);
    }

    public function test_inconsistent_material_course_is_visible_but_not_writable(): void {
        global $DB;
        $DB->set_field('block_exacompexamples', 'courseid', 0, ['id' => $this->exampleid]);
        $this->assertCount(1, $this->inspect()['materials']);
        $this->expectException(\invalid_parameter_exception::class);
        $this->add([$this->child]);
    }

    public function test_other_course_activity_is_rejected(): void {
        $other = $this->getDataGenerator()->create_course();
        $activity = $this->getDataGenerator()->create_module('page', ['course' => $other->id]);
        $this->expectException(\moodle_exception::class);
        service::get_activity_competences($this->course->id, $activity->cmid);
    }

    public function test_student_cannot_read_plan_or_write(): void {
        $student = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($student->id, $this->course->id, 'student');
        $this->setUser($student);
        $calls = [
            function() { service::get_course_competences($this->course->id); },
            function() { $this->inspect(); },
            function() { service::plan_activity_competences($this->course->id,
                $this->activity->cmid, $this->exampleid, [$this->child]); },
            function() { $this->add([$this->child]); },
        ];
        foreach ($calls as $call) {
            try {
                $call();
                $this->fail('Student authorized');
            } catch (\required_capability_exception $e) {
                $this->assertSame('nopermissions', $e->errorcode);
            }
        }
    }

    public function test_teacher_in_another_course_is_not_authorized(): void {
        $other = $this->getDataGenerator()->create_course();
        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($teacher->id, $other->id, 'editingteacher');
        $this->setUser($teacher);
        $this->expectException(\moodle_exception::class);
        $this->add([$this->child]);
    }

    public function test_token_context_restriction_is_enforced(): void {
        $other = $this->getDataGenerator()->create_course();
        service::set_context_restriction(\context_course::instance($other->id));
        try {
            $this->expectException(\core_external\restricted_context_exception::class);
            $this->inspect();
        } finally {
            service::set_context_restriction(\context_system::instance());
        }
    }

    public function test_invalid_course_and_activity_ids(): void {
        foreach ([[0, $this->activity->cmid], [99999999, $this->activity->cmid],
                [$this->course->id, 0], [$this->course->id, 99999999]] as $ids) {
            try {
                service::get_activity_competences($ids[0], $ids[1]);
                $this->fail('Invalid course/module accepted');
            } catch (\moodle_exception $e) {
                $this->assertNotEmpty($e->errorcode);
            }
        }
    }

    public function test_service_registration_and_types(): void {
        global $CFG;
        \block_exacomp\developer::developer_actions();
        require($CFG->dirroot . '/blocks/exacomp/db/services.php');
        require_once($CFG->libdir . '/upgradelib.php');
        external_update_descriptions('block_exacomp');
        foreach (['get_course_competences', 'get_activity_competences', 'plan_activity_competences',
                'add_activity_competences'] as $method) {
            $name = 'block_exacomp_' . $method;
            $this->assertContains($name, $services['exacompservices']['functions']);
            $this->assertSame($method === 'add_activity_competences' ? 'write' : 'read', $functions[$name]['type']);
            $this->assertSame('block/exacomp:teacher', $functions[$name]['capabilities']);
            $registered = service::external_function_info($name);
            $this->assertSame($method, $registered->methodname);
            $this->assertInstanceOf(\core_external\external_function_parameters::class, $registered->parameters_desc);
            $annotation = (new \ReflectionMethod(service::class, $method))->getDocComment();
            $this->assertStringContainsString('@ws-capabilities block/exacomp:teacher', $annotation);
            $this->assertStringContainsString('@ws-type-' . $functions[$name]['type'], $annotation);
        }
    }
}
