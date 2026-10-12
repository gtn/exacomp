<?php
// This file is part of Moodle - http://moodle.org/.

namespace block_exacomp;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once $CFG->dirroot . '/blocks/exaport/lib/lib.php';

use block_exacomp\externallib\externallib;

/**
 * Quota enforcement through the actual DiggrPlus item submission caller.
 *
 * @package block_exacomp
 * @copyright 2026 GTN - Global Training Network GmbH
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
final class diggrplus_submission_quota_test extends \advanced_testcase {
    private $course;
    private $owner;
    private $topicid;
    private $descriptorid;

    protected function setUp(): void {
        global $CFG, $DB, $COURSE;
        parent::setUp();
        $this->resetAfterTest(true);
        // Moodle 5.2 requires isolated tests for Exacomp's legacy external_api bootstrap.
        require_once $CFG->dirroot . '/blocks/exacomp/classes/externallib/base.php';
        set_config('block_exaport_userquota', 100);
        $this->course = $this->getDataGenerator()->create_course();
        $this->owner = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($this->owner->id, $this->course->id, 'student');
        $this->setUser($this->owner);
        $COURSE = $this->course;
        // The existing sharing helper reads the course from the HTTP request, as REST supplies it.
        $_GET['courseid'] = $this->course->id;
        $subjectid = $DB->insert_record('block_exacompsubjects', (object)[
            'title' => 'Quota subject', 'sourceid' => 0, 'source' => 0,
        ]);
        $this->topicid = (int)$DB->insert_record('block_exacomptopics', (object)[
            'title' => 'Quota topic', 'subjid' => $subjectid, 'source' => 0,
        ]);
        $this->descriptorid = (int)$DB->insert_record('block_exacompdescriptors', (object)[
            'title' => 'Quota descriptor', 'sourceid' => 0, 'source' => 0,
        ]);
        $DB->insert_record('block_exacompdescrtopic_mm', (object)[
            'descrid' => $this->descriptorid, 'topicid' => $this->topicid,
        ]);
    }

    /** Create a parent item, optionally marked as graded to force a new version. */
    private function item(bool $graded = false, ?int $ownerid = null): \stdClass {
        global $DB;
        $itemid = $DB->insert_record('block_exaportitem', (object)[
            'userid' => $ownerid ?? $this->owner->id, 'courseid' => $this->course->id,
            'name' => 'Original item', 'type' => 'note', 'intro' => 'Original description',
            'url' => '', 'attachment' => '', 'timemodified' => 100,
        ]);
        $DB->insert_record(BLOCK_EXACOMP_DB_ITEM_MM, (object)[
            'exacomp_record_id' => $this->topicid, 'itemid' => $itemid, 'timecreated' => 100,
            'competence_type' => BLOCK_EXACOMP_TYPE_TOPIC, 'status' => $graded ? 1 : 0,
            'teachervalue' => $graded ? 2 : null, 'studentvalue' => 1,
        ]);
        return $DB->get_record('block_exaportitem', ['id' => $itemid], '*', MUST_EXIST);
    }

    /** Create real bytes in an owner context; metadata does not determine quota ownership. */
    private function file(int $ownerid, string $component, string $area, int $itemid,
            string $name, int $size): \stored_file {
        return get_file_storage()->create_file_from_string([
            'contextid' => \context_user::instance($ownerid)->id, 'component' => $component,
            'filearea' => $area, 'itemid' => $itemid, 'filepath' => '/',
            'filename' => $name, 'userid' => $this->owner->id,
        ], str_repeat(substr($name, 0, 1), $size));
    }

    /** Snapshot all plugin state, including evaluations, relations, views and stored/draft files. */
    private function snapshot(): array {
        global $DB;
        $result = ['files' => $DB->get_records('files', null, 'id')];
        foreach ($DB->get_tables() as $table) {
            if (strpos($table, 'block_exaport') === 0 || strpos($table, 'block_exacomp') === 0) {
                $result[$table] = $DB->get_records($table, null, 'id');
            }
        }
        return $result;
    }

    /** Call the public endpoint with grading/comment/collaborator inputs too. */
    private function submit(int $itemid, array $draftids, array $removeids, int $submit = 0): array {
        return externallib::diggrplus_submit_item(
            $this->topicid, 2, '', '', 'New comment', implode(',', $draftids), $itemid,
            $this->course->id, BLOCK_EXACOMP_TYPE_TOPIC, 'New title', (string)$this->owner->id,
            $submit, implode(',', $removeids), 'New description',
            [['descriptorid' => $this->descriptorid, 'studentvalue' => 2]]
        );
    }

    public static function quota_provider(): array {
        // Other Exaport areas use 50 bytes in every case. Null means a new parent item.
        return [
            'new below limit' => [null, [40], [], true, false, false],
            'new exact limit' => [null, [50], [], true, false, false],
            'new over limit' => [null, [51], [], false, false, false],
            'all draft files are charged' => [null, [20, 31], [], false, false, false],
            'retained files not counted twice' => [['old.txt' => 40], [], [], true, false, false],
            'append at limit' => [['old.txt' => 40], [10], [], true, false, false],
            'append over limit' => [['old.txt' => 40], [11], [], false, false, false],
            'removal plus addition exact' => [
                ['keep.txt' => 20, 'remove.txt' => 30], [30], ['remove.txt'], true, false, false,
            ],
            'removal plus addition over' => [
                ['keep.txt' => 20, 'remove.txt' => 30], [31], ['remove.txt'], false, false, false,
            ],
            'same-name replacement' => [['old.txt' => 40], [50], ['old.txt'], true, false, false],
            'duplicate removals credited once' => [
                ['old.txt' => 40], [51], ['old.txt', 'old.txt'], false, false, false,
            ],
            'other-item removal cannot free quota' => [
                ['old.txt' => 40], [11], ['foreign'], false, false, false,
            ],
            'already over unchanged' => [['old.txt' => 60], [], [], true, false, false],
            'already over growth' => [['old.txt' => 60], [1], [], false, false, false],
            'already over equal replacement' => [['old.txt' => 60], [60], ['old.txt'], true, false, false],
            'already over reducing replacement' => [['old.txt' => 60], [59], ['old.txt'], true, false, false],
            'already over removal only' => [['old.txt' => 60], [], ['old.txt'], true, false, false],
            'version cannot credit original removal' => [['old.txt' => 40], [11], ['old.txt'], false, true, false],
            'version at limit keeps original files' => [['old.txt' => 40], [10], ['old.txt'], true, true, false],
            'repeated draft IDs count each copy' => [null, [30], [], false, false, true],
        ];
    }

    /** @dataProvider quota_provider */
    public function test_actual_caller_enforces_quota_before_side_effects(?array $existing, array $sizes,
            array $removenames, bool $accepted, bool $graded, bool $repeatdraft): void {
        global $DB;
        $otheritem = $this->item();
        $otherblock = block_exaport_create_file_content_block((int)$otheritem->id, 'Other content');
        $otherfile = $this->file($this->owner->id, 'block_exaport', 'item_content_file', $otherblock->id, 'other.txt', 20);
        $this->file($this->owner->id, 'block_exaport', 'item_content_text', 9001, 'embedded.txt', 10);
        $this->file($this->owner->id, 'block_exaport', 'item_file', $otheritem->id, 'legacy.txt', 10);
        $this->file($this->owner->id, 'block_exaport', 'item_iconfile', $otheritem->id, 'icon.txt', 10);
        $this->file($this->owner->id, 'user', 'private', 0, 'private.txt', 1000);
        $otherowner = $this->getDataGenerator()->create_user();
        $this->file($otherowner->id, 'block_exaport', 'item_file', 9002, 'other-owner.txt', 1000);
        $item = $existing !== null ? $this->item($graded) : null;
        $originalfiles = [];
        foreach ($existing ?? [] as $name => $size) {
            $block = block_exaport_create_file_content_block((int)$item->id, $name);
            $originalfiles[$name] = $this->file($this->owner->id, 'block_exaport', 'item_content_file', $block->id, $name, $size);
        }
        $draftids = [];
        $newnames = [];
        foreach ($sizes as $index => $size) {
            $draftid = file_get_unused_draft_itemid();
            $name = $removenames === ['old.txt'] ? 'old.txt' : 'new-' . $index . '.txt';
            $this->file($this->owner->id, 'user', 'draft', $draftid, $name, $size);
            $draftids[] = $draftid;
            $newnames[] = $name;
        }
        if ($repeatdraft) {
            $draftids[] = $draftids[0];
        }
        $removeids = [];
        foreach ($removenames as $name) {
            $removeids[] = $name === 'foreign' ? $otherfile->get_id() : $originalfiles[$name]->get_id();
        }
        // Enrol a teacher so rejection must precede any submission notification as well.
        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($teacher->id, $this->course->id, 'editingteacher');
        $before = $this->snapshot();
        $messages = $this->redirectMessages();
        $emails = $this->redirectEmails();
        $events = $this->redirectEvents();
        if (!$accepted) {
            try {
                $this->submit((int)($item->id ?? 0), $draftids, $removeids, 1);
                $this->fail('The actual endpoint must reject before changing submission state');
            } catch (\moodle_exception $exception) {
                $this->assertSame('userquotalimit', $exception->errorcode);
            }
            $this->assertEquals($before, $this->snapshot(), 'No items, blocks, files, grades, relations or sharing changed');
            $this->assertSame([], $messages->get_messages());
            $this->assertSame([], $emails->get_messages());
            $this->assertSame([], $events->get_events());
            return;
        }
        $result = $this->submit((int)($item->id ?? 0), $draftids, $removeids);
        $this->assertTrue($result['success']);
        $destination = $DB->get_record('block_exaportitem', ['id' => $result['itemid']], '*', MUST_EXIST);
        $this->assertSame('New title', $destination->name);
        $this->assertSame('New description', $destination->intro);
        $this->assertSame((int)$this->owner->id, (int)$destination->userid);
        if ($item) {
            if ($graded) {
                $this->assertNotSame((int)$item->id, (int)$destination->id);
                $this->assertEquals($item, $DB->get_record('block_exaportitem', ['id' => $item->id]));
            } else {
                $this->assertSame((int)$item->id, (int)$destination->id);
            }
        }
        $expected = $graded || !$item ? [] : array_diff_key($existing, array_flip($removenames));
        $actual = [];
        foreach (block_exaport_get_item_content_blocks((int)$destination->id) as $block) {
            foreach (block_exaport_get_item_content_files((int)$destination->userid, (int)$block->id) as $file) {
                $actual[$file->get_filename()] = $file->get_content();
            }
        }
        foreach ($expected as $name => $size) {
            $expected[$name] = str_repeat(substr($name, 0, 1), $size);
        }
        foreach ($sizes as $index => $size) {
            $expected[$newnames[$index]] = str_repeat(substr($newnames[$index], 0, 1), $size);
        }
        ksort($actual);
        ksort($expected);
        $this->assertSame($expected, $actual);
        foreach ($originalfiles as $name => $file) {
            $retained = get_file_storage()->get_file_by_id($file->get_id());
            if ($graded || !in_array($name, $removenames)) {
                $this->assertNotFalse($retained, 'Retained file IDs survive appends/versioned submissions');
                $this->assertSame($file->get_content(), $retained->get_content());
            } else {
                $this->assertFalse($retained);
            }
        }
    }
}
