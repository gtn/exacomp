<?php
// This file is part of Moodle - http://moodle.org/.

namespace block_exacomp;

use block_exacomp\local\deferred_events;
use block_exacomp\local\migration_pause;

defined('MOODLE_INTERNAL') || die();

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 * @covers \block_exacomp\local\deferred_events
 * @covers \block_exacomp\local\migration_pause
 */
#[\PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses]
#[\PHPUnit\Framework\Attributes\PreserveGlobalState(false)]
final class migration_pause_test extends \advanced_testcase {
    public function test_other_moodle_activity_is_deferred_and_replayed_before_unpausing(): void {
        global $CFG, $DB, $USER;
        $this->resetAfterTest();
        if (!class_exists('\\block_exaport\\local\\item_content_migration')) {
            $this->markTestSkipped('Optional Exaport is not installed.');
        }
        require_once($CFG->dirroot . '/blocks/exacomp/classes/observer.php');
        $this->setAdminUser();
        $actorid = $USER->id;
        $course = $this->getDataGenerator()->create_course();
        set_config('addblock_to_new_course', 'side-pre', 'exacomp');
        set_config('item_content_migration_pending', 1, 'block_exaport');
        $event = \core\event\course_created::create([
            'other' => ['fullname' => $course->fullname],
            'objectid' => $course->id, 'context' => \context_course::instance($course->id), 'userid' => $actorid,
        ]);
        $event->add_record_snapshot('course', $course);
        \block_exacomp_observer::course_created($event);
        $this->assertTrue(migration_pause::is_pending());
        $this->assertSame(1, $DB->count_records('block_exacompdeferredevent'));
        $this->assertFalse($DB->record_exists('block_instances', [
            'blockname' => 'exacomp', 'parentcontextid' => \context_course::instance($course->id)->id,
        ]));
        $called = false;
        $this->assertFalse(deferred_events::finish(static function() use (&$called): void {
            $called = true;
        }));
        $this->assertFalse($called);
        $this->setGuestUser();
        $this->assertTrue(deferred_events::drain(1));
        $this->assertSame(1, (int)$USER->id);
        $this->assertFalse(deferred_events::is_replaying());
        $this->assertFalse($DB->record_exists('block_exacompdeferredevent', []));
        $this->assertSame(1, $DB->count_records('block_instances', [
            'blockname' => 'exacomp', 'parentcontextid' => \context_course::instance($course->id)->id,
        ]));
        $this->assertTrue(migration_pause::is_pending());
        $this->assertTrue(deferred_events::finish(static function(): void {
            unset_config('item_content_migration_pending', 'block_exaport');
        }));
        $this->assertFalse(migration_pause::is_pending());
        $this->assertTrue(deferred_events::drain(1));
        $this->assertSame(1, $DB->count_records('block_instances', [
            'blockname' => 'exacomp', 'parentcontextid' => \context_course::instance($course->id)->id,
        ]));
    }

    public function test_pending_block_renders_a_translated_list_notice(): void {
        global $CFG;
        $this->resetAfterTest();
        if (!class_exists('\\block_exaport\\local\\item_content_migration')) {
            $this->markTestSkipped('Optional Exaport is not installed.');
        }
        require_once($CFG->dirroot . '/blocks/exacomp/block_exacomp.php');
        set_config('item_content_migration_pending', 1, 'block_exaport');
        $content = (new \block_exacomp())->get_content();
        $this->assertCount(1, $content->items);
        $this->assertSame([], $content->icons);
        $this->assertSame('', $content->footer);
        $this->assertSame(get_string('itemcontentmigrationpending', 'block_exacomp'), $content->items[0]);
        $this->assertStringNotContainsString('[[', $content->items[0]);
    }

    public function test_registered_services_refuse_calls_before_business_logic(): void {
        global $DB;
        $this->resetAfterTest();
        if (!class_exists('\\block_exaport\\local\\item_content_migration')) {
            $this->markTestSkipped('Optional Exaport is not installed.');
        }
        set_config('item_content_migration_pending', 1, 'block_exaport');
        $functions = $DB->get_records('external_functions', ['component' => 'block_exacomp']);
        $this->assertNotEmpty($functions);
        foreach ($functions as $function) {
            $method = new \ReflectionMethod($function->classname, $function->methodname);
            $args = [];
            foreach ($method->getParameters() as $parameter) {
                $args[] = $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() :
                    match ((string)$parameter->getType()) {
                        'int' => 0, 'string' => '', 'array' => [], 'bool' => false, 'float' => 0.0, default => null,
                    };
            }
            try {
                $method->invokeArgs(null, $args);
                $this->fail('Pending migration allowed ' . $function->name);
            } catch (\moodle_exception $exception) {
                $this->assertSame('itemcontentmigrationpending', $exception->errorcode, $function->name);
            }
        }
        $this->assertSame(0, $DB->count_records('block_exaportitem'));
    }

    public function test_module_update_replays_title_and_student_visibility(): void {
        global $CFG, $DB;
        $this->resetAfterTest();
        $this->preventResetByRollback(); // Exercise the event's real commit/rollback boundary on PostgreSQL too.
        if (!class_exists('\\block_exaport\\local\\item_content_migration')) {
            $this->markTestSkipped('Optional Exaport is not installed.');
        }
        require_once($CFG->dirroot . '/blocks/exacomp/classes/observer.php');
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $activity = $this->getDataGenerator()->create_module('url', ['course' => $course->id]);
        $example = $DB->insert_record('block_exacompexamples', [
            'title' => 'Before', 'activitytitle' => 'Before', 'courseid' => $course->id,
            'activityid' => $activity->cmid, 'source' => 0,
        ]);
        set_config('item_content_migration_pending', 1, 'block_exaport');
        $event = \core\event\course_module_updated::create([
            'objectid' => $activity->cmid, 'context' => \context_module::instance($activity->cmid),
            'other' => ['modulename' => 'url', 'name' => 'Renamed', 'instanceid' => $activity->id],
        ]);
        $event->add_record_snapshot('course_modules', $DB->get_record('course_modules', ['id' => $activity->cmid]));
        \block_exacomp_observer::course_module_updated($event);
        $this->assertSame('Before', $DB->get_field('block_exacompexamples', 'title', ['id' => $example]));
        $journal = $DB->get_record('block_exacompdeferredevent', [], '*', MUST_EXIST);
        $payload = json_decode($journal->payload, true);
        $this->assertContains((int)$student->id, $payload['snapshots']['students']);
        // Reproduce the caught development defect after the observer's title UPDATE, before journal deletion.
        $broken = $payload;
        $broken['snapshots']['students'] = [['unexpected' => 'user object']];
        $DB->set_field('block_exacompdeferredevent', 'payload', json_encode($broken), ['id' => $journal->id]);
        try {
            deferred_events::drain(100);
            $this->fail('Expected the invalid replay input to fail.');
        } catch (\coding_exception $exception) {
            $this->assertStringContainsString('Deferred Exacomp event ' . $journal->id, $exception->getMessage());
        }
        $this->assertSame('Before', $DB->get_field('block_exacompexamples', 'title', ['id' => $example]));
        $this->assertTrue($DB->record_exists('block_exacompdeferredevent', ['id' => $journal->id]));
        $this->assertFalse(deferred_events::is_replaying());
        // Repair only the injected cause; leave migration state, identities and the existing journal row intact.
        $DB->set_field('block_exacompdeferredevent', 'payload', $journal->payload, ['id' => $journal->id]);
        $this->assertTrue(deferred_events::drain(100));
        $this->assertSame('Renamed', $DB->get_field('block_exacompexamples', 'title', ['id' => $example]));
        $this->assertFalse($DB->record_exists('block_exacompdeferredevent', []));
    }

    public function test_quiz_submission_keeps_original_grade_inputs_for_replay(): void {
        global $CFG, $DB;
        $this->resetAfterTest();
        if (!class_exists('\\block_exaport\\local\\item_content_migration')) {
            $this->markTestSkipped('Optional Exaport is not installed.');
        }
        require_once($CFG->dirroot . '/mod/quiz/locallib.php');
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_quiz');
        $quiz = $generator->create_test_quiz([['True/false', 1, 'truefalse']],
            ['course' => $course->id, 'preferredbehaviour' => 'immediatefeedback']);
        $this->setUser($student);
        $attempt = $generator->create_attempt($quiz->get_quizid(), $student->id);
        $subject = $DB->insert_record('block_exacompsubjects', ['title' => 'Journal subject', 'source' => 0, 'sourceid' => 0]);
        $topic = $DB->insert_record('block_exacomptopics', ['title' => 'Journal topic', 'subjid' => $subject, 'source' => 0, 'sourceid' => 0]);
        $descriptor = $DB->insert_record('block_exacompdescriptors', ['title' => 'Quiz competence', 'source' => 0, 'sourceid' => 0]);
        $DB->insert_record('block_exacompdescrtopic_mm', ['descrid' => $descriptor, 'topicid' => $topic]);
        $DB->insert_record('block_exacompcoutopi_mm', ['courseid' => $course->id, 'topicid' => $topic]);
        $questionid = $DB->get_field('question_attempts', 'questionid', ['questionusageid' => $attempt->uniqueid]);
        $DB->insert_record('block_exacompdescrquest_mm', [
            'questid' => $questionid, 'descrid' => $descriptor, 'courseid' => $course->id,
        ]);
        set_config('assessment_comp_scheme', BLOCK_EXACOMP_ASSESSMENT_TYPE_POINTS, 'exacomp');
        set_config('assessment_points_limit', 2, 'exacomp');
        set_config('assessment_points_negativ', 0, 'exacomp');
        set_config('item_content_migration_pending', 1, 'block_exaport');
        $generator->submit_responses($attempt->id, [1 => get_string('true', 'qtype_truefalse')], true, true);
        $journal = $DB->get_record('block_exacompdeferredevent', [], '*', MUST_EXIST);
        $payload = json_decode($journal->payload, true);
        $this->assertSame('\\mod_quiz\\event\\attempt_submitted', $payload['data']['eventname']);
        $this->assertSame((int)$student->id, $payload['actorid']);
        $this->assertTrue($payload['snapshots']['admingrading']);
        $questions = $payload['snapshots']['quizattempt']['questions'];
        $this->assertCount(1, $questions);
        $original = reset($questions);
        $expectedgrade = block_exacomp_get_assessment_max_good_value(BLOCK_EXACOMP_ASSESSMENT_TYPE_POINTS,
            true, $original['maxmark'], $original['fraction'], $course->id);
        $this->assertFalse($DB->record_exists('block_exacompcompuser', ['userid' => $student->id, 'compid' => $descriptor]));
        // Simulate a subsequent regrade: journalled inputs must remain the event-time inputs.
        $DB->set_field('question_attempt_steps', 'fraction', 0.125, ['questionattemptid' => $original['id']]);
        $again = json_decode($DB->get_field('block_exacompdeferredevent', 'payload', ['id' => $journal->id]), true);
        $this->assertSame($questions, $again['snapshots']['quizattempt']['questions']);
        $this->assertTrue(deferred_events::drain(100));
        $grade = $DB->get_record('block_exacompcompuser', [
            'userid' => $student->id, 'compid' => $descriptor, 'courseid' => $course->id,
            'comptype' => BLOCK_EXACOMP_TYPE_DESCRIPTOR, 'role' => BLOCK_EXACOMP_ROLE_TEACHER,
        ], '*', MUST_EXIST);
        $this->assertEquals($expectedgrade, $grade->value);
        $this->assertFalse($DB->record_exists('block_exacompdeferredevent', []));
        $this->assertTrue(migration_pause::is_pending());
    }
}
