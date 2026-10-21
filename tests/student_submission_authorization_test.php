<?php
// This file is part of Moodle - http://moodle.org/.

namespace block_exacomp;

defined('MOODLE_INTERNAL') || die();

use block_exacomp\externallib\externallib;

/**
 * Authorization through the three student-submission service callers.
 *
 * @package block_exacomp
 * @copyright 2026 GTN - Global Training Network GmbH
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
final class student_submission_authorization_test extends \advanced_testcase {
    private $course;
    private $owner;
    private $exampleid;
    private $descriptorid;

    protected function setUp(): void {
        global $CFG, $DB, $COURSE;
        parent::setUp();
        $this->resetAfterTest(true);
        require_once $CFG->dirroot . '/blocks/exacomp/classes/externallib/base.php';
        require_once $CFG->dirroot . '/blocks/exaport/lib/lib.php';
        set_config('block_exaport_app_externaleportfolio', 0);
        set_config('block_exaport_userquota', 100000);
        $this->course = $this->getDataGenerator()->create_course();
        $this->owner = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($this->owner->id, $this->course->id, 'student');
        $this->setUser($this->owner);
        $COURSE = $this->course;
        $_GET['courseid'] = $this->course->id;
        $subjectid = $DB->insert_record('block_exacompsubjects', [
            'title' => 'Authorization subject', 'sourceid' => 0, 'source' => 0,
        ]);
        $topicid = $DB->insert_record('block_exacomptopics', [
            'title' => 'Authorization topic', 'subjid' => $subjectid, 'source' => 0,
        ]);
        $this->descriptorid = (int)$DB->insert_record('block_exacompdescriptors', [
            'title' => 'Authorization descriptor', 'sourceid' => 0, 'source' => 0,
        ]);
        $DB->insert_record('block_exacompdescrtopic_mm', ['descrid' => $this->descriptorid, 'topicid' => $topicid]);
        $this->exampleid = (int)$DB->insert_record('block_exacompexamples', [
            'title' => 'Authorization example', 'description' => '', 'sourceid' => 0, 'source' => 0,
        ]);
        $DB->insert_record('block_exacompdescrexamp_mm', ['descrid' => $this->descriptorid, 'exampid' => $this->exampleid]);
        $this->redirectMessages();
        $this->redirectEmails();
        $this->redirectEvents();
    }

    private function file(int $userid, string $area, int $itemid, string $name, string $bytes): \stored_file {
        return get_file_storage()->create_file_from_string([
            'contextid' => \context_user::instance($userid)->id,
            'component' => $area === 'draft' ? 'user' : 'block_exaport', 'filearea' => $area,
            'itemid' => $itemid, 'filepath' => '/', 'filename' => $name, 'userid' => $userid,
        ], $bytes);
    }

    private function item(bool $graded): \stdClass {
        global $DB;
        $id = $DB->insert_record('block_exaportitem', [
            'userid' => $this->owner->id, 'courseid' => $this->course->id,
            'name' => 'Owner submission', 'type' => 'file', 'intro' => 'Owner description',
            'url' => '', 'attachment' => '  historical-submission-id  ', 'timemodified' => 100,
        ]);
        $DB->insert_record(BLOCK_EXACOMP_DB_ITEM_MM, [
            'exacomp_record_id' => $this->exampleid, 'itemid' => $id, 'timecreated' => 100,
            'competence_type' => BLOCK_EXACOMP_TYPE_EXAMPLE, 'status' => $graded ? 1 : 0,
            'teachervalue' => $graded ? 2 : null, 'studentvalue' => 1,
        ]);
        block_exaport_create_link_content_block($id, 'Owner link', 'https://example.test/owner');
        $text = block_exaport_create_content_block($id, 'text', 'Owner text', '', [
            'content' => '<p>Owner text <img src="@@PLUGINFILE@@/embedded.txt"></p>',
        ]);
        $this->file($this->owner->id, 'item_content_text', $text->id, 'embedded.txt', 'Owner embedded bytes');
        $block = block_exaport_create_file_content_block($id, 'Owner file');
        $this->file($this->owner->id, 'item_content_file', $block->id, 'owner.txt', 'Owner file bytes');
        return $DB->get_record('block_exaportitem', ['id' => $id], '*', MUST_EXIST);
    }

    private function snapshot(): array {
        global $DB;
        $snapshot = [];
        foreach ($DB->get_tables() as $table) {
            if (strpos($table, 'block_exaport') === 0 || strpos($table, 'block_exacomp') === 0
                    || in_array($table, ['files', 'notifications', 'messages'], true)) {
                $snapshot[$table] = $DB->get_records($table, null, 'id');
            }
        }
        return $snapshot;
    }

    private function submit(string $service, int $itemid, int $draftid, int $removeid = 0): array {
        global $USER;
        if ($service === 'diggrplus') {
            return externallib::diggrplus_submit_item($this->exampleid, 2, 'https://example.test/submitted',
                'incoming.txt', 'Submission comment', (string)$draftid, $itemid, $this->course->id,
                BLOCK_EXACOMP_TYPE_EXAMPLE, 'Submitted title', (string)$USER->id, 1,
                $removeid ? (string)$removeid : '', 'Submitted description',
                [['descriptorid' => $this->descriptorid, 'studentvalue' => 2]]);
        }
        if ($service === 'dakora') {
            return externallib::dakora_submit_example($this->exampleid, 2, 'https://example.test/submitted',
                'incoming.txt', 'Submission comment', $itemid, $this->course->id, (string)$draftid);
        }
        return externallib::submit_example($this->exampleid, 2, 'https://example.test/submitted',
            'Submitted description', 'incoming.txt', $draftid, 'Submission comment', 'Submitted title',
            $itemid, $this->course->id);
    }

    public static function foreign_submission_provider(): array {
        $cases = [];
        foreach (['diggrplus', 'dakora', 'submit_example'] as $service) {
            foreach (['unrelated', 'collaborator', 'shared_view', 'teacher'] as $access) {
                foreach ([false, true] as $graded) {
                    $cases[$service . '-' . $access . '-' . ($graded ? 'graded' : 'ungraded')] = [$service, $access, $graded];
                }
            }
        }
        return $cases;
    }

    /** @dataProvider foreign_submission_provider */
    public function test_rejects_foreign_submission_without_side_effects(string $service, string $access, bool $graded): void {
        global $DB;
        $item = $this->item($graded);
        $actor = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($actor->id, $this->course->id,
            $access === 'teacher' ? 'editingteacher' : 'student');
        if ($access === 'collaborator') {
            $DB->insert_record(BLOCK_EXACOMP_DB_ITEM_COLLABORATOR_MM, ['itemid' => $item->id, 'userid' => $actor->id]);
        } else if ($access === 'shared_view') {
            $viewid = $DB->insert_record('block_exaportview', ['userid' => $this->owner->id, 'name' => 'Shared view']);
            $DB->insert_record('block_exaportviewblock', ['viewid' => $viewid, 'itemid' => $item->id, 'type' => 'item']);
            $DB->insert_record('block_exaportviewshar', ['viewid' => $viewid, 'userid' => $actor->id]);
        }
        $this->setUser($actor);
        $draftid = file_get_unused_draft_itemid();
        $draft = $this->file($actor->id, 'draft', $draftid, 'incoming.txt', 'Caller draft bytes');
        $ownerfile = block_exaport_get_item_structured_files($item)[0];
        $before = $this->snapshot();
        $error = null;
        try {
            $this->submit($service, $item->id, $draftid, $ownerfile->get_id());
        } catch (\invalid_parameter_exception $exception) {
            $error = $exception;
        }
        $this->assertInstanceOf(\invalid_parameter_exception::class, $error, 'The service must reject a foreign item');
        $this->assertEquals($before, $this->snapshot(), 'Rejection must precede every submission side effect');
        $this->assertSame('Owner file bytes', get_file_storage()->get_file_by_id($ownerfile->get_id())->get_content());
        $this->assertSame('Caller draft bytes', get_file_storage()->get_file_by_id($draft->get_id())->get_content());
    }

    public static function owner_submission_provider(): array {
        $cases = [];
        foreach (['diggrplus', 'dakora', 'submit_example'] as $service) {
            foreach (['new', 'update', 'version'] as $operation) {
                $cases[$service . '-' . $operation] = [$service, $operation];
            }
        }
        return $cases;
    }

    /** @dataProvider owner_submission_provider */
    public function test_owner_can_create_update_and_version(string $service, string $operation): void {
        global $DB;
        $original = $operation === 'new' ? null : $this->item($operation === 'version');
        $originalblocks = $original ? block_exaport_get_item_content_blocks($original->id) : [];
        $originalrelation = $original ? $DB->get_record(BLOCK_EXACOMP_DB_ITEM_MM, ['itemid' => $original->id]) : null;
        $originalfile = $original ? block_exaport_get_item_structured_files($original)[0] : null;
        $draftid = file_get_unused_draft_itemid();
        $this->file($this->owner->id, 'draft', $draftid, 'incoming.txt', 'Owner incoming bytes');
        $result = $this->submit($service, $original->id ?? 0, $draftid, $originalfile ? $originalfile->get_id() : 0);
        $this->assertTrue($result['success']);
        $saved = $DB->get_record('block_exaportitem', ['id' => $result['itemid']], '*', MUST_EXIST);
        $this->assertEquals($this->owner->id, $saved->userid);
        $files = block_exaport_get_item_structured_files($saved);
        $this->assertCount(1, $files);
        $this->assertSame('Owner incoming bytes', $files[0]->get_content());
        $this->assertEquals(\context_user::instance($this->owner->id)->id, $files[0]->get_contextid());
        if ($operation === 'update') {
            $this->assertEquals($original->id, $saved->id);
            $this->assertSame($original->attachment, $saved->attachment);
        } else if ($operation === 'version') {
            $this->assertSame('', $saved->attachment);
            $this->assertNotEquals($original->id, $saved->id);
            $this->assertEquals($original, $DB->get_record('block_exaportitem', ['id' => $original->id]));
            $this->assertEquals($originalblocks, block_exaport_get_item_content_blocks($original->id));
            $this->assertEquals($originalrelation, $DB->get_record(BLOCK_EXACOMP_DB_ITEM_MM, ['itemid' => $original->id]));
            $this->assertSame('Owner file bytes', get_file_storage()->get_file_by_id($originalfile->get_id())->get_content());
        } else {
            $this->assertSame('', $saved->attachment);
        }
    }

    public function test_collaborator_can_still_comment(): void {
        global $DB;
        $item = $this->item(false);
        $collaborator = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($collaborator->id, $this->course->id, 'student');
        $DB->insert_record(BLOCK_EXACOMP_DB_ITEM_COLLABORATOR_MM, ['itemid' => $item->id, 'userid' => $collaborator->id]);
        $this->setUser($collaborator);
        $result = externallib::diggrplus_submit_item_comment($item->id, 'Collaborator comment', 0);
        $this->assertTrue($result['success']);
        $comment = $DB->get_record('block_exaportitemcomm', [
            'itemid' => $item->id, 'userid' => $collaborator->id,
        ], '*', MUST_EXIST);
        $this->assertSame('Collaborator comment', $comment->entry);
    }

    public static function service_provider(): array {
        return ['diggrplus' => ['diggrplus'], 'dakora' => ['dakora'], 'submit_example' => ['submit_example']];
    }

    /** @dataProvider service_provider */
    public function test_missing_existing_item_is_rejected_without_creating_a_submission(string $service): void {
        $draftid = file_get_unused_draft_itemid();
        $this->file($this->owner->id, 'draft', $draftid, 'incoming.txt', 'Owner draft bytes');
        $before = $this->snapshot();
        $error = null;
        try {
            $this->submit($service, PHP_INT_MAX, $draftid);
        } catch (\invalid_parameter_exception $exception) {
            $error = $exception;
        }
        $this->assertInstanceOf(\invalid_parameter_exception::class, $error);
        $this->assertEquals($before, $this->snapshot());
    }
}
