<?php
// This file is part of Moodle - http://moodle.org/.

namespace block_exacomp;

use block_exacomp\externallib\externallib;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once $CFG->dirroot . '/blocks/exacomp/lib/lib.php';
require_once $CFG->dirroot . '/blocks/exaport/lib/lib.php';

/**
 * Tests how the submission web services write Exaport content blocks.
 *
 * Covers resubmission without data loss (F1), the ownership check on update (F3)
 * and the link/file handling of diggrplus_submit_item (F4).
 *
 * @package block_exacomp
 * @copyright 2026 GTN - Global Training Network GmbH
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class exaport_submission_webservices_test extends \advanced_testcase {

    /** @var \stdClass */
    private $course;

    /** @var \stdClass */
    private $user;

    /** @var \stdClass */
    private $otheruser;

    /** @var \stdClass */
    private $example;

    protected function setUp(): void {
        global $CFG, $DB;

        parent::setUp();
        $this->resetAfterTest(true);
        $CFG->block_exaport_app_externaleportfolio = 0;

        $this->course = $this->getDataGenerator()->create_course();
        $this->user = $this->getDataGenerator()->create_user();
        $this->otheruser = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($this->user->id, $this->course->id, 'student');
        $this->getDataGenerator()->enrol_user($this->otheruser->id, $this->course->id, 'student');

        // A minimal subject > topic > descriptor > example chain, needed to create the portfolio category.
        $subjectid = $DB->insert_record(BLOCK_EXACOMP_DB_SUBJECTS, ['title' => 'Subject', 'sourceid' => 0]);
        $topicid = $DB->insert_record(BLOCK_EXACOMP_DB_TOPICS, ['title' => 'Topic', 'subjid' => $subjectid]);
        $descriptorid = $DB->insert_record(BLOCK_EXACOMP_DB_DESCRIPTORS, ['title' => 'Descriptor', 'parentid' => 0]);
        $DB->insert_record(BLOCK_EXACOMP_DB_DESCTOPICS, ['descrid' => $descriptorid, 'topicid' => $topicid]);
        $exampleid = $DB->insert_record(BLOCK_EXACOMP_DB_EXAMPLES, ['title' => 'Example', 'blocking_event' => 0]);
        $DB->insert_record(BLOCK_EXACOMP_DB_DESCEXAMP, ['descrid' => $descriptorid, 'exampid' => $exampleid, 'sorting' => 0]);
        $this->example = $DB->get_record(BLOCK_EXACOMP_DB_EXAMPLES, ['id' => $exampleid], '*', MUST_EXIST);

        $this->setUser($this->user);
    }

    /** @return string[][] */
    public static function singular_api_provider(): array {
        return [
            'submit_example' => ['submit_example'],
            'dakora_submit_example' => ['dakora_submit_example'],
        ];
    }

    /**
     * @dataProvider singular_api_provider
     */
    public function test_resubmission_url_then_file_keeps_url(string $api): void {
        $itemid = $this->submit($api, 0, 'https://example.test/first');
        $this->submit($api, $itemid, '', [$this->create_draft_file('new.txt', 'new')]);

        $this->assertSame(['link', 'file'], $this->block_types($itemid));
        $this->assertSame('https://example.test/first', $this->link_url($itemid));
        $this->assertSame(['new.txt'], $this->filenames($this->blocks($itemid, 'file')[0]->id));
    }

    /**
     * @dataProvider singular_api_provider
     */
    public function test_resubmission_file_then_url_keeps_files(string $api): void {
        $itemid = $this->submit($api, 0, '', [$this->create_draft_file('keep.txt', 'keep')]);
        $this->submit($api, $itemid, 'https://example.test/added');

        $this->assertSame(['file', 'link'], $this->block_types($itemid));
        $this->assertSame('https://example.test/added', $this->link_url($itemid));
        $this->assertSame(['keep.txt'], $this->filenames($this->blocks($itemid, 'file')[0]->id));
    }

    /**
     * @dataProvider singular_api_provider
     */
    public function test_resubmission_file_and_url_keeps_both(string $api): void {
        $itemid = $this->submit($api, 0, 'https://example.test/old', [$this->create_draft_file('old.txt', 'old')]);
        $this->assertSame(['link', 'file'], $this->block_types($itemid), 'New items list the link first.');

        $this->submit($api, $itemid, 'https://example.test/new', [$this->create_draft_file('new.txt', 'new')]);

        $this->assertSame(['link', 'file'], $this->block_types($itemid));
        $this->assertSame('https://example.test/new', $this->link_url($itemid));
        $this->assertSame(['new.txt'], $this->filenames($this->blocks($itemid, 'file')[0]->id));
        $this->assert_item_url_projection($itemid, 'https://example.test/new');
    }

    /**
     * @dataProvider singular_api_provider
     */
    public function test_repeated_url_resubmission_keeps_a_single_link_block(string $api): void {
        $itemid = $this->submit($api, 0, 'https://example.test/one');
        $this->submit($api, $itemid, 'https://example.test/two');
        $this->submit($api, $itemid, 'https://example.test/three');

        $this->assertSame(['link'], $this->block_types($itemid));
        $this->assert_item_url_projection($itemid, 'https://example.test/three');
    }

    /**
     * @dataProvider singular_api_provider
     */
    public function test_resubmission_keeps_text_block_and_extra_file_block(string $api): void {
        $itemid = $this->submit($api, 0, 'https://example.test/link', [$this->create_draft_file('first.txt', 'first')]);
        [$textblock, $extrablock] = $this->add_blocks_through_exaport_api($itemid);

        $this->submit($api, $itemid, 'https://example.test/changed', [$this->create_draft_file('second.txt', 'second')]);

        $blocks = $this->blocks($itemid);
        $this->assertSame(['link', 'file', 'text', 'file'], array_column($blocks, 'type'));
        $this->assertSame('https://example.test/changed', $this->link_url($itemid));
        $this->assertSame(['second.txt'], $this->filenames($blocks[1]->id));
        $this->assertEquals($textblock->id, $blocks[2]->id);
        $this->assertSame('<p>Text from the Exaport UI</p>', $blocks[2]->content);
        $this->assertEquals($extrablock->id, $blocks[3]->id);
        $this->assertSame(['extra.txt'], $this->filenames($extrablock->id));
    }

    /**
     * @dataProvider singular_api_provider
     */
    public function test_missing_draft_throws_and_leaves_item_unchanged(string $api): void {
        $itemid = $this->submit($api, 0, 'https://example.test/link', [$this->create_draft_file('first.txt', 'first')]);
        $snapshot = $this->snapshot($itemid);

        $unknowndraft = \file_get_unused_draft_itemid();
        try {
            $this->submit($api, $itemid, 'https://example.test/changed', [[$unknowndraft, 'missing.txt']]);
            $this->fail('A missing draft file must raise an error.');
        } catch (\moodle_exception $e) {
            $this->assertSame($snapshot, $this->snapshot($itemid));
        }

        // An existing draft area which does not contain the declared file.
        $draftitemid = $this->create_draft_file('other.txt', 'other');
        try {
            $this->submit($api, $itemid, '', [[$draftitemid, 'missing.txt']]);
            $this->fail('A missing draft file must raise an error.');
        } catch (\moodle_exception $e) {
            $this->assertSame($snapshot, $this->snapshot($itemid));
        }
    }

    public function test_dakora_missing_draft_area_without_filename_throws(): void {
        $itemid = $this->submit('dakora_submit_example', 0, '', [$this->create_draft_file('first.txt', 'first')]);
        $snapshot = $this->snapshot($itemid);

        try {
            externallib::dakora_submit_example($this->example->id, -1, '', '', '', $itemid, $this->course->id,
                (string)\file_get_unused_draft_itemid());
            $this->fail('An empty draft area must raise an error.');
        } catch (\moodle_exception $e) {
            $this->assertSame($snapshot, $this->snapshot($itemid));
        }
    }

    /**
     * @dataProvider singular_api_provider
     */
    public function test_foreign_item_is_rejected(string $api): void {
        $itemid = $this->submit($api, 0, 'https://example.test/private', [$this->create_draft_file('private.txt', 'p')]);
        $snapshot = $this->snapshot($itemid);

        $this->setUser($this->otheruser);
        $draftitemid = $this->create_draft_file('attack.txt', 'attack');
        try {
            $this->submit($api, $itemid, 'https://example.test/attack', [$draftitemid]);
            $this->fail('Updating another user\'s item must be rejected.');
        } catch (\moodle_exception $e) {
            $this->assertSame($snapshot, $this->snapshot($itemid));
        }
    }

    /**
     * An invalid second draft fails after the first one was valid: nothing may change.
     */
    public function test_dakora_failure_in_update_changes_nothing(): void {
        global $DB;

        $itemid = $this->submit('dakora_submit_example', 0, 'https://example.test/link',
            [$this->create_draft_file('first.txt', 'first')]);
        $snapshot = $this->snapshot($itemid);
        $name = $DB->get_field('block_exaportitem', 'name', ['id' => $itemid]);

        $validdraft = $this->create_draft_file('valid.txt', 'valid');
        $invaliddraft = \file_get_unused_draft_itemid();
        try {
            externallib::dakora_submit_example($this->example->id, -1, 'https://example.test/changed',
                'valid.txt,invalid.txt', 'comment', $itemid, $this->course->id, $validdraft . ',' . $invaliddraft);
            $this->fail('An invalid draft must raise an error.');
        } catch (\moodle_exception $e) {
            $this->assertSame($snapshot, $this->snapshot($itemid));
            $this->assertSame($name, $DB->get_field('block_exaportitem', 'name', ['id' => $itemid]));
            $this->assertFalse($DB->record_exists('block_exaportitemcomm', ['itemid' => $itemid, 'entry' => 'comment']));
        }
    }

    public function test_diggrplus_repeated_url_updates_keep_one_link_block(): void {
        $itemid = $this->submit_diggrplus(0, 'https://example.test/one');
        $this->assertSame(['link'], $this->block_types($itemid));

        $this->submit_diggrplus($itemid, 'https://example.test/two');
        $this->submit_diggrplus($itemid, 'https://example.test/three');

        $this->assertSame(['link'], $this->block_types($itemid));
        $this->assert_item_url_projection($itemid, 'https://example.test/three');

        // A null URL, as sent by the Diggr-plus app, does not touch the link.
        $this->submit_diggrplus($itemid, null);
        $this->assertSame(['link'], $this->block_types($itemid));
        $this->assert_item_url_projection($itemid, 'https://example.test/three');
    }

    public function test_diggrplus_insert_with_files_and_url_keeps_both(): void {
        $itemid = $this->submit_diggrplus(0, 'https://example.test/both', [$this->create_draft_file('both.txt', 'both')]);

        $this->assertSame(['link', 'file'], $this->block_types($itemid));
        $this->assert_item_url_projection($itemid, 'https://example.test/both');
        $this->assertSame(['both.txt'], $this->filenames($this->blocks($itemid, 'file')[0]->id));
    }

    public function test_diggrplus_update_appends_files_and_keeps_text_blocks(): void {
        $itemid = $this->submit_diggrplus(0, null, [$this->create_draft_file('first.txt', 'first')]);
        [$textblock] = $this->add_blocks_through_exaport_api($itemid);

        $this->submit_diggrplus($itemid, null, [$this->create_draft_file('second.txt', 'second')]);

        $files = [];
        foreach ($this->blocks($itemid, 'file') as $block) {
            $files = array_merge($files, $this->filenames($block->id));
        }
        sort($files);
        $this->assertSame(['extra.txt', 'first.txt', 'second.txt'], $files);
        $this->assertCount(1, $this->blocks($itemid, 'text'));
        $this->assertEquals($textblock->id, $this->blocks($itemid, 'text')[0]->id);
    }

    public function test_diggrplus_removing_last_file_deletes_empty_block(): void {
        global $DB;

        $itemid = $this->submit_diggrplus(0, 'https://example.test/link', [$this->create_draft_file('only.txt', 'only')]);
        $fileblock = $this->blocks($itemid, 'file')[0];
        $file = array_values(\block_exaport_get_item_content_files($this->user->id, $fileblock->id))[0];

        $this->submit_diggrplus($itemid, null, [], (string)$file->get_id());

        $this->assertSame(['link'], $this->block_types($itemid));
        $this->assertFalse($DB->record_exists('block_exaportitemblock', ['id' => $fileblock->id]));
        $this->assertSame([], \block_exaport_get_item_content_files($this->user->id, $fileblock->id));
    }

    public function test_diggrplus_foreign_item_is_rejected(): void {
        $itemid = $this->submit_diggrplus(0, 'https://example.test/private', [$this->create_draft_file('p.txt', 'p')]);
        $snapshot = $this->snapshot($itemid);
        $fileblock = $this->blocks($itemid, 'file')[0];
        $file = array_values(\block_exaport_get_item_content_files($this->user->id, $fileblock->id))[0];

        $this->setUser($this->otheruser);
        $draftitemid = $this->create_draft_file('attack.txt', 'attack');
        foreach ([
            [$draftitemid, 'https://example.test/attack', ''],
            [0, null, (string)$file->get_id()],
        ] as [$draft, $url, $remove]) {
            try {
                $this->submit_diggrplus($itemid, $url, $draft ? [$draft] : [], $remove);
                $this->fail('Updating another user\'s item must be rejected.');
            } catch (\moodle_exception $e) {
                $this->assertSame($snapshot, $this->snapshot($itemid));
            }
        }
    }

    public function test_diggrplus_failure_in_update_changes_nothing(): void {
        global $DB;

        $itemid = $this->submit_diggrplus(0, 'https://example.test/link', [$this->create_draft_file('first.txt', 'first')]);
        $snapshot = $this->snapshot($itemid);
        $file = array_values(\block_exaport_get_item_content_files($this->user->id, $this->blocks($itemid, 'file')[0]->id))[0];

        try {
            // The first draft is valid, the second does not exist; the file must not be removed either.
            $this->submit_diggrplus($itemid, 'https://example.test/changed',
                [$this->create_draft_file('valid.txt', 'valid'), \file_get_unused_draft_itemid()], (string)$file->get_id());
            $this->fail('An invalid draft must raise an error.');
        } catch (\moodle_exception $e) {
            $this->assertSame($snapshot, $this->snapshot($itemid));
            $this->assertSame('Title', $DB->get_field('block_exaportitem', 'name', ['id' => $itemid]));
        }
    }

    /**
     * Call submit_example or dakora_submit_example.
     *
     * @param string $api
     * @param int $itemid 0 to create a new item
     * @param string $url
     * @param array $files Draft item ids, or [draft item id, filename] pairs
     * @return int Item id
     */
    private function submit(string $api, int $itemid, string $url = '', array $files = []): int {
        $drafts = [];
        $names = [];
        foreach ($files as $file) {
            [$drafts[], $names[]] = is_array($file) ? $file : [$file, $this->draft_filename($file)];
        }

        if ($api === 'submit_example') {
            $result = externallib::submit_example($this->example->id, -1, $url, 'effort', $names ? $names[0] : '',
                $drafts ? $drafts[0] : 0, '', 'Title', $itemid, $this->course->id);
        } else {
            $result = externallib::dakora_submit_example($this->example->id, -1, $url, implode(',', $names), '',
                $itemid, $this->course->id, implode(',', $drafts));
        }
        $this->assertTrue($result['success']);
        return $result['itemid'];
    }

    /**
     * @param int $itemid 0 to create a new item
     * @param string|null $url
     * @param int[] $drafts
     * @param string $removefiles
     * @return int Item id
     */
    private function submit_diggrplus(int $itemid, ?string $url, array $drafts = [], string $removefiles = ''): int {
        $result = externallib::diggrplus_submit_item($this->example->id, -1, $url, '', '', implode(',', $drafts),
            $itemid, $this->course->id, BLOCK_EXACOMP_TYPE_EXAMPLE, 'Title', '', 0, $removefiles, '', []);
        $this->assertTrue($result['success']);
        return $result['itemid'];
    }

    /**
     * Add a text block and a second file block the way the Exaport UI does.
     *
     * @return \stdClass[] [text block, file block]
     */
    private function add_blocks_through_exaport_api(int $itemid): array {
        $textblock = \block_exaport_create_content_block($itemid, 'text', 'Notes', '',
            ['content' => '<p>Text from the Exaport UI</p>']);
        $fileblock = \block_exaport_create_file_content_block($itemid, 'Extra');
        \get_file_storage()->create_file_from_string([
            'contextid' => \context_user::instance($this->user->id)->id,
            'component' => 'block_exaport',
            'filearea' => 'item_content_file',
            'itemid' => $fileblock->id,
            'filepath' => '/',
            'filename' => 'extra.txt',
        ], 'extra');
        return [$textblock, $fileblock];
    }

    /** @var string[] Filenames of the drafts created by create_draft_file(), by draft item id. */
    private $draftnames = [];

    private function create_draft_file(string $filename, string $content): int {
        global $USER;

        $draftitemid = \file_get_unused_draft_itemid();
        \get_file_storage()->create_file_from_string([
            'contextid' => \context_user::instance($USER->id)->id,
            'component' => 'user',
            'filearea' => 'draft',
            'itemid' => $draftitemid,
            'filepath' => '/',
            'filename' => $filename,
        ], $content);
        $this->draftnames[$draftitemid] = $filename;
        return $draftitemid;
    }

    private function draft_filename(int $draftitemid): string {
        return $this->draftnames[$draftitemid];
    }

    /** @return \stdClass[] Blocks ordered like the Exaport readers (sortorder, id), optionally of one type. */
    private function blocks(int $itemid, ?string $type = null): array {
        global $DB;

        $blocks = array_values($DB->get_records('block_exaportitemblock', ['itemid' => $itemid], 'sortorder ASC, id ASC'));
        if ($type !== null) {
            $blocks = array_values(array_filter($blocks, static function($block) use ($type) {
                return $block->type === $type;
            }));
        }
        return $blocks;
    }

    private function block_types(int $itemid): array {
        return array_column($this->blocks($itemid), 'type');
    }

    private function link_url(int $itemid): string {
        $links = $this->blocks($itemid, 'link');
        $this->assertCount(1, $links);
        return $links[0]->url;
    }

    /** @return string[] */
    private function filenames(int $blockid): array {
        $names = [];
        foreach (\block_exaport_get_item_content_files((int)$this->user->id, $blockid) as $file) {
            $names[] = $file->get_filename();
        }
        return $names;
    }

    private function assert_item_url_projection(int $itemid, string $expected): void {
        global $DB;

        $item = $DB->get_record('block_exaportitem', ['id' => $itemid], '*', MUST_EXIST);
        $this->assertSame($expected, \block_exaport_get_item_content_webservice_data($item)['url']);
        $this->assertSame('', $item->url, 'The legacy url column stays empty.');
        $this->assertSame('', $item->attachment, 'The legacy attachment column stays empty.');
    }

    /** All content blocks (without timestamps) and the names and contents of their files. */
    private function snapshot(int $itemid): array {
        $snapshot = [];
        foreach ($this->blocks($itemid) as $block) {
            $files = [];
            foreach (\block_exaport_get_item_content_files((int)$this->user->id, $block->id) as $file) {
                $files[$file->get_filename()] = $file->get_content();
            }
            $snapshot[] = [
                'id' => $block->id,
                'type' => $block->type,
                'sortorder' => $block->sortorder,
                'title' => $block->title,
                'content' => $block->content,
                'url' => $block->url,
                'files' => $files,
            ];
        }
        return $snapshot;
    }
}
