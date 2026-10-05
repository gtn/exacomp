<?php
// This file is part of Moodle - http://moodle.org/.

namespace block_exacomp;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once $CFG->dirroot . '/blocks/exaport/lib/lib.php';

/**
 * Tests the structured content written by Exacomp example submissions.
 *
 * @package block_exacomp
 * @copyright 2026 GTN - Global Training Network GmbH
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class example_submission_content_test extends \advanced_testcase {

    /** @var \stdClass */
    private $course;

    /** @var \stdClass */
    private $user;

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
        $this->course = $this->getDataGenerator()->create_course();
        $this->user = $this->getDataGenerator()->create_user();
        $this->setUser($this->user);
    }

    public function test_file_submission_stores_file_in_structured_content(): void {
        $item = $this->create_item();
        $draftitemid = $this->create_draft_file('submission.txt', 'file submission');

        $selection = \block_exacomp\local\example_submission_content::select($this->user->id, $draftitemid, '');
        \block_exacomp\local\example_submission_content::store($item, $item->name, $selection);

        $blocks = $this->get_blocks($item->id);
        $this->assertCount(1, $blocks);
        $this->assertSame('file', reset($blocks)->type);

        $files = $this->get_content_files(reset($blocks)->id);
        $this->assertCount(1, $files);
        $this->assertSame('submission.txt', $files[0]->get_filename());
        $this->assertSame('file submission', $files[0]->get_content());
    }

    public function test_submission_without_file_or_url_is_rejected_without_persisting_item(): void {
        global $DB;

        $before = [
            'items' => $DB->count_records('block_exaportitem'),
            'blocks' => $DB->count_records('block_exaportitemblock'),
            'itemcategories' => $DB->count_records('block_exaportitemcate'),
            'exampleitems' => $DB->count_records('block_exacompitem_mm'),
            'views' => $DB->count_records('block_exaportview'),
            'viewblocks' => $DB->count_records('block_exaportviewblock'),
        ];

        $rejected = false;
        try {
            \block_exacomp\local\example_submission_content::select($this->user->id, 0, '');
        } catch (\moodle_exception $exception) {
            $rejected = true;
        }

        $this->assertTrue($rejected, 'A submission without a file or URL must be rejected.');
        $this->assertSame($before['items'], $DB->count_records('block_exaportitem'));
        $this->assertSame($before['blocks'], $DB->count_records('block_exaportitemblock'));
        $this->assertSame($before['itemcategories'], $DB->count_records('block_exaportitemcate'));
        $this->assertSame($before['exampleitems'], $DB->count_records('block_exacompitem_mm'));
        $this->assertSame($before['views'], $DB->count_records('block_exaportview'));
        $this->assertSame($before['viewblocks'], $DB->count_records('block_exaportviewblock'));
    }

    public function test_file_and_url_submission_keeps_both_inputs(): void {
        $item = $this->create_item();
        $draftitemid = $this->create_draft_file('combined.txt', 'combined submission');
        $url = 'https://example.test/submission';

        $selection = \block_exacomp\local\example_submission_content::select(
            $this->user->id, $draftitemid, $url);
        \block_exacomp\local\example_submission_content::store($item, $item->name, $selection);

        $blocks = $this->get_blocks($item->id);
        $this->assertCount(2, $blocks, 'A combined submission must create both a link and a file block.');
        $types = array_map(static function($block) {
            return $block->type;
        }, array_values($blocks));
        $this->assertContains('link', $types);
        $this->assertContains('file', $types);
        $this->assertStringContainsString($url, json_encode(
            \block_exaport_get_item_content_webservice_data($item), JSON_UNESCAPED_SLASHES));

        $fileblocks = array_values(array_filter($blocks, static function($block) {
            return $block->type === 'file';
        }));
        $fileblock = reset($fileblocks);
        $files = $this->get_content_files($fileblock->id);
        $this->assertCount(1, $files);
        $this->assertSame('combined.txt', $files[0]->get_filename());
    }

    public function test_url_only_submission_stores_structured_link_block(): void {
        $item = $this->create_item();
        $url = 'https://example.test/link-only';

        $selection = \block_exacomp\local\example_submission_content::select($this->user->id, 0, $url);
        \block_exacomp\local\example_submission_content::store($item, $item->name, $selection);

        $blocks = $this->get_blocks($item->id);
        $this->assertCount(1, $blocks);
        $this->assertSame('link', reset($blocks)->type);
        $this->assertStringContainsString($url, json_encode(
            \block_exaport_get_item_content_webservice_data($item), JSON_UNESCAPED_SLASHES));
        $this->assertSame([], $this->get_content_files(reset($blocks)->id));
    }

    /**
     * Create the minimal Exaport parent item used by its structured-content API.
     *
     * @return \stdClass
     */
    private function create_item(): \stdClass {
        global $DB;

        $itemid = $DB->insert_record('block_exaportitem', [
            'userid' => $this->user->id,
            'name' => 'Example submission',
            'url' => '',
            'attachment' => '',
            'intro' => '',
            'type' => 'file',
            'timemodified' => time(),
            'courseid' => $this->course->id,
        ]);
        return $DB->get_record('block_exaportitem', ['id' => $itemid], '*', MUST_EXIST);
    }

    /**
     * Add a draft file for the current user.
     *
     * @param string $filename
     * @param string $content
     * @return int
     */
    private function create_draft_file(string $filename, string $content): int {
        $draftitemid = \file_get_unused_draft_itemid();
        $usercontext = \context_user::instance($this->user->id);
        \get_file_storage()->create_file_from_string([
            'contextid' => $usercontext->id,
            'component' => 'user',
            'filearea' => 'draft',
            'itemid' => $draftitemid,
            'filepath' => '/',
            'filename' => $filename,
        ], $content);
        return $draftitemid;
    }

    /**
     * @param int $itemid
     * @return \stdClass[]
     */
    private function get_blocks(int $itemid): array {
        global $DB;

        return array_values($DB->get_records('block_exaportitemblock', ['itemid' => $itemid], 'id ASC'));
    }

    /**
     * @param int $blockid
     * @return \stored_file[]
     */
    private function get_content_files(int $blockid): array {
        $context = \context_user::instance($this->user->id);
        return array_values(\get_file_storage()->get_area_files(
            $context->id, 'block_exaport', 'item_content_file', $blockid, 'filename ASC', false));
    }
}
