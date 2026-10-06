<?php
// This file is part of Moodle - http://moodle.org/.

namespace block_exacomp;

use block_exacomp\externallib\externallib;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once $CFG->dirroot . '/blocks/exacomp/lib/lib.php';
require_once $CFG->dirroot . '/blocks/exaport/lib/lib.php';

/**
 * Tests ownership checks, validation and atomicity of the update branches of
 * submit_example, dakora_submit_example and diggrplus_submit_item.
 *
 * @package block_exacomp
 * @copyright 2026 GTN - Global Training Network GmbH
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @group block_exacomp
 */
final class submission_update_test extends \advanced_testcase {

    /** @var \stdClass */
    private $course;

    /** @var \stdClass */
    private $owner;

    /** @var \stdClass */
    private $other;

    /** @var int */
    private $exampleid;

    protected function setUp(): void {
        global $DB;

        parent::setUp();
        $this->resetAfterTest(true);
        $this->course = $this->getDataGenerator()->create_course();
        $this->owner = $this->getDataGenerator()->create_user();
        $this->other = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($this->owner->id, $this->course->id, 'student');
        $this->getDataGenerator()->enrol_user($this->other->id, $this->course->id, 'student');
        $this->exampleid = $DB->insert_record('block_exacompexamples', ['title' => 'Example']);
    }

    /** The services which update an existing item, as [name, caller]. */
    public static function services_provider(): array {
        return [
            'submit_example' => ['submit_example'],
            'dakora_submit_example' => ['dakora_submit_example'],
            'diggrplus_submit_item' => ['diggrplus_submit_item'],
        ];
    }

    /**
     * @dataProvider services_provider
     */
    public function test_other_users_item_is_rejected_and_unchanged(string $service): void {
        $item = $this->create_item($this->owner);
        $before = $this->snapshot($item);

        $this->setUser($this->other);
        $draftitemid = $this->create_draft_file($this->other, 'attack.txt', 'attack');
        try {
            $this->call($service, $item->id, ['draftitemid' => $draftitemid, 'filename' => 'attack.txt',
                'removefiles' => (string)$before['files'][0]['id']]);
            $this->fail('Updating the item of another user must throw.');
        } catch (\block_exacomp_permission_exception $e) {
            $this->assertStringContainsString((string)$item->id, $e->getMessage());
        }

        $this->assertSame($before, $this->snapshot($item));
    }

    /**
     * @dataProvider services_provider
     */
    public function test_missing_draft_file_throws_and_keeps_content(string $service): void {
        $item = $this->create_item($this->owner);
        $before = $this->snapshot($item);

        $this->setUser($this->owner);
        // A draft area which does not exist.
        try {
            $this->call($service, $item->id, ['draftitemid' => 987654321, 'filename' => 'missing.txt']);
            $this->fail('A missing draft file must throw.');
        } catch (\invalid_parameter_exception $e) {
            $this->assertSame($before, $this->snapshot($item));
        }

        if ($service === 'diggrplus_submit_item') {
            // DiggrPlus has no file names: every file of an existing draft area is used.
            return;
        }

        // An existing draft area, but without the requested file.
        $draftitemid = $this->create_draft_file($this->owner, 'present.txt', 'present');
        try {
            $this->call($service, $item->id, ['draftitemid' => $draftitemid, 'filename' => 'missing.txt']);
            $this->fail('A missing draft file must throw.');
        } catch (\invalid_parameter_exception $e) {
            $this->assertSame($before, $this->snapshot($item));
        }
    }

    /**
     * @dataProvider services_provider
     */
    public function test_failure_during_update_rolls_back(string $service): void {
        global $DB;

        $item = $this->create_item($this->owner);
        $before = $this->snapshot($item);

        $this->setUser($this->owner);
        // The URL is too long for the content block column, so adding the new content fails
        // after the item was updated and (depending on the service) old content was removed.
        $url = 'https://example.test/' . str_repeat('a', 300);
        try {
            $this->call($service, $item->id, ['url' => $url, 'removefiles' => (string)$before['files'][0]['id']]);
            $this->fail('Adding the new content must fail.');
        } catch (\dml_exception $e) {
            $this->assertFalse($DB->is_transaction_started(), 'The failed update must not leave a transaction open.');
        }

        $this->assertSame($before, $this->snapshot($item));
    }

    public function test_dakora_resubmission_by_owner_replaces_content(): void {
        $item = $this->create_item($this->owner);
        $this->setUser($this->owner);
        $draftitemid = $this->create_draft_file($this->owner, 'new.txt', 'new content');

        $this->call('dakora_submit_example', $item->id, ['draftitemid' => $draftitemid, 'filename' => 'new.txt']);

        $snapshot = $this->snapshot($item);
        $this->assertCount(1, $snapshot['blocks']);
        $this->assertSame('file', $snapshot['blocks'][0]['type']);
        $this->assertSame([['filename' => 'new.txt', 'content' => 'new content']],
            array_map(static function($file) {
                return ['filename' => $file['filename'], 'content' => $file['content']];
            }, $snapshot['files']));
    }

    public function test_submit_example_resubmission_by_owner_replaces_content(): void {
        $item = $this->create_item($this->owner);
        $this->setUser($this->owner);

        $this->call('submit_example', $item->id, ['url' => 'https://example.test/new']);

        $snapshot = $this->snapshot($item);
        $this->assertCount(1, $snapshot['blocks']);
        $this->assertSame('link', $snapshot['blocks'][0]['type']);
        $this->assertSame('https://example.test/new', $snapshot['blocks'][0]['url']);
        $this->assertSame([], $snapshot['files']);
    }

    public function test_diggrplus_resubmission_by_owner_keeps_existing_blocks_and_adds_file(): void {
        $item = $this->create_item($this->owner);
        $before = $this->snapshot($item);
        $this->setUser($this->owner);
        $draftitemid = $this->create_draft_file($this->owner, 'added.txt', 'added content');

        $this->call('diggrplus_submit_item', $item->id, ['draftitemid' => $draftitemid]);

        $snapshot = $this->snapshot($item);
        $this->assertCount(count($before['blocks']) + 1, $snapshot['blocks']);
        $filenames = array_column($snapshot['files'], 'filename');
        $this->assertContains('existing.txt', $filenames);
        $this->assertContains('added.txt', $filenames);
        $this->assertSame($before['blocks'][0], $snapshot['blocks'][0]);
    }

    /**
     * Call one of the services to update $itemid as the current user.
     *
     * @param string $service
     * @param int $itemid
     * @param array $options draftitemid, filename, url, removefiles
     */
    private function call(string $service, int $itemid, array $options = []) {
        $draftitemid = $options['draftitemid'] ?? 0;
        $filename = $options['filename'] ?? '';
        $url = $options['url'] ?? '';
        $courseid = $this->course->id;

        switch ($service) {
            case 'submit_example':
                return externallib::submit_example($this->exampleid, 1, $url, 'effort', $draftitemid ? $filename : '',
                    $draftitemid, 'comment', 'New title', $itemid, $courseid);
            case 'dakora_submit_example':
                return externallib::dakora_submit_example($this->exampleid, 1, $url, $draftitemid ? $filename : '',
                    'comment', $itemid, $courseid, $draftitemid ? (string)$draftitemid : '');
            case 'diggrplus_submit_item':
                return externallib::diggrplus_submit_item($this->exampleid, 1, $url, '', 'comment',
                    $draftitemid ? (string)$draftitemid : '', $itemid, $courseid, BLOCK_EXACOMP_TYPE_EXAMPLE,
                    'New title', '', 0, $options['removefiles'] ?? '', 'New description');
        }
        throw new \coding_exception('Unknown service');
    }

    /**
     * Create an item of $user with a link block and a file block and its exacomp assignment.
     *
     * @param \stdClass $user
     * @return \stdClass
     */
    private function create_item(\stdClass $user): \stdClass {
        global $DB;

        $itemid = $DB->insert_record('block_exaportitem', [
            'userid' => $user->id,
            'name' => 'Original title',
            'url' => '',
            'attachment' => '',
            'intro' => 'Original intro',
            'type' => 'file',
            'timemodified' => 1000,
            'courseid' => $this->course->id,
        ]);
        $item = $DB->get_record('block_exaportitem', ['id' => $itemid], '*', MUST_EXIST);
        $DB->insert_record(BLOCK_EXACOMP_DB_ITEM_MM, [
            'exacomp_record_id' => $this->exampleid,
            'itemid' => $itemid,
            'timecreated' => 1000,
            'status' => 0,
        ]);

        block_exaport_create_link_content_block($itemid, 'Original title', 'https://example.test/original');
        $draft = $this->create_draft_file($user, 'existing.txt', 'existing content');
        $file = \get_file_storage()->get_area_files(\context_user::instance($user->id)->id, 'user', 'draft',
            $draft, 'id ASC', false);
        block_exaport_import_stored_file_into_content_block($item, reset($file));
        return $item;
    }

    /**
     * Add a draft file for $user.
     *
     * @param \stdClass $user
     * @param string $filename
     * @param string $content
     * @return int draft item id
     */
    private function create_draft_file(\stdClass $user, string $filename, string $content): int {
        // file_get_unused_draft_itemid() refuses guests and uses the current user.
        global $USER;
        $previoususer = $USER;
        $this->setUser($user);
        $draftitemid = \file_get_unused_draft_itemid();
        $this->setUser($previoususer);
        \get_file_storage()->create_file_from_string([
            'contextid' => \context_user::instance($user->id)->id,
            'component' => 'user',
            'filearea' => 'draft',
            'itemid' => $draftitemid,
            'filepath' => '/',
            'filename' => $filename,
        ], $content);
        return $draftitemid;
    }

    /**
     * The complete state of an item which the services may change.
     *
     * @param \stdClass $item
     * @return array
     */
    private function snapshot(\stdClass $item): array {
        global $DB;

        $record = $DB->get_record('block_exaportitem', ['id' => $item->id], '*', MUST_EXIST);
        $owner = \context_user::instance($item->userid);
        $fs = \get_file_storage();
        $blocks = [];
        $files = [];
        foreach ($DB->get_records('block_exaportitemblock', ['itemid' => $item->id], 'id ASC') as $block) {
            $blocks[] = (array)$block;
            foreach ($fs->get_area_files($owner->id, 'block_exaport', 'item_content_file', $block->id,
                'filename ASC', false) as $file) {
                $files[] = ['id' => $file->get_id(), 'blockid' => $block->id,
                    'filename' => $file->get_filename(), 'content' => $file->get_content()];
            }
        }
        $mm = $DB->get_records(BLOCK_EXACOMP_DB_ITEM_MM, ['itemid' => $item->id], 'id ASC');
        return [
            'item' => (array)$record,
            'blocks' => $blocks,
            'files' => $files,
            'assignments' => array_map(static function($row) {
                return (array)$row;
            }, array_values($mm)),
        ];
    }
}
