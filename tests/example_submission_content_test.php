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

    public function test_submission_without_file_or_url_creates_item_with_description(): void {
        global $DB;

        $description = 'Description-only example submission';
        $selection = \block_exacomp\local\example_submission_content::select($this->user->id, 0, '');
        $item = $this->create_item($description);
        \block_exacomp\local\example_submission_content::store($item, $item->name, $selection);

        $saveditem = $DB->get_record('block_exaportitem', ['id' => $item->id], '*', MUST_EXIST);
        $this->assertSame($description, $saveditem->intro);
        $this->assertSame([], $this->get_blocks($item->id));
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
    private function create_item(string $intro = ''): \stdClass {
        global $DB;

        $itemid = $DB->insert_record('block_exaportitem', [
            'userid' => $this->user->id,
            'name' => 'Example submission',
            'url' => '',
            'attachment' => '',
            'intro' => $intro,
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
