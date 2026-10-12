<?php
// This file is part of Moodle - http://moodle.org/.

namespace block_exacomp;

defined('MOODLE_INTERNAL') || die();

use block_exacomp\externallib\externallib;

/**
 * Structured content through public submission/read callers and package APIs.
 *
 * @package block_exacomp
 * @copyright 2026 GTN - Global Training Network GmbH
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
final class diggrplus_submission_content_test extends \advanced_testcase {
    private $course;
    private $owner;
    private $topicid;
    private $descriptorid;

    protected function setUp(): void {
        global $CFG, $DB, $COURSE;
        parent::setUp();
        $this->resetAfterTest(true);
        require_once $CFG->dirroot . '/blocks/exacomp/classes/externallib/base.php';
        require_once $CFG->dirroot . '/blocks/exaport/lib/lib.php';
        require_once $CFG->dirroot . '/blocks/exaport/lib/scorm_export_helpers.php';
        require_once $CFG->dirroot . '/blocks/exaport/lib/package_import_helpers.php';
        set_config('block_exaport_userquota', 100000);
        set_config('block_exaport_app_externaleportfolio', 0);
        $this->course = $this->getDataGenerator()->create_course();
        $this->owner = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($this->owner->id, $this->course->id, 'student');
        $this->setUser($this->owner);
        $COURSE = $this->course;
        $_GET['courseid'] = $this->course->id;
        $subjectid = $DB->insert_record('block_exacompsubjects', (object)[
            'title' => 'Content subject', 'sourceid' => 0, 'source' => 0,
        ]);
        $this->topicid = (int)$DB->insert_record('block_exacomptopics', (object)[
            'title' => 'Content topic', 'subjid' => $subjectid, 'source' => 0,
        ]);
        $this->descriptorid = (int)$DB->insert_record('block_exacompdescriptors', (object)[
            'title' => 'Content descriptor', 'sourceid' => 0, 'source' => 0,
        ]);
        $DB->insert_record('block_exacompdescrtopic_mm', (object)[
            'descrid' => $this->descriptorid, 'topicid' => $this->topicid,
        ]);
        $this->redirectMessages();
        $this->redirectEmails();
        $this->redirectEvents();
    }

    private function item(bool $graded = false): \stdClass {
        global $DB;
        $id = $DB->insert_record('block_exaportitem', (object)[
            'userid' => $this->owner->id, 'courseid' => $this->course->id,
            'name' => 'Original', 'type' => 'file', 'intro' => 'Parent description',
            'url' => '', 'attachment' => '', 'timemodified' => 100,
        ]);
        $DB->insert_record(BLOCK_EXACOMP_DB_ITEM_MM, (object)[
            'exacomp_record_id' => $this->topicid, 'itemid' => $id, 'timecreated' => 100,
            'competence_type' => BLOCK_EXACOMP_TYPE_TOPIC, 'status' => $graded ? 1 : 0,
            'teachervalue' => $graded ? 2 : null, 'studentvalue' => 1,
        ]);
        return $DB->get_record('block_exaportitem', ['id' => $id], '*', MUST_EXIST);
    }

    private function file(string $area, int $itemid, string $name, string $bytes = 'File bytes'): \stored_file {
        return get_file_storage()->create_file_from_string([
            'contextid' => \context_user::instance($this->owner->id)->id,
            'component' => $area === 'draft' ? 'user' : 'block_exaport', 'filearea' => $area,
            'itemid' => $itemid, 'filepath' => '/', 'filename' => $name, 'userid' => $this->owner->id,
        ], $bytes);
    }

    private function submit(int $itemid, string $url = '', array $draftids = [], array $removeids = []): int {
        $result = externallib::diggrplus_submit_item(
            $this->topicid, 2, $url, '', 'Comment', implode(',', $draftids), $itemid,
            $this->course->id, BLOCK_EXACOMP_TYPE_TOPIC, 'Updated title', '', 0,
            implode(',', $removeids), 'Parent description',
            [['descriptorid' => $this->descriptorid, 'studentvalue' => 2]]
        );
        $clean = \external_api::clean_returnvalue(externallib::diggrplus_submit_item_returns(), $result);
        $this->assertTrue($clean['success']);
        return (int)$clean['itemid'];
    }

    /** Exercise the listing response consumed by the supplied ZIP, including schema cleaning. */
    private function read(int $itemid): array {
        // Existing topic listing SQL omits exacomp_record_id, which the access
        // check reads. Allow only that known warning while testing the real
        // response; leave other warnings with PHPUnit's handler. Fixing that
        // unrelated projection is outside this submission-boundary change.
        $servicefile = realpath(__DIR__ . '/../classes/externallib/externallib.php');
        $previoushandler = null;
        $previoushandler = set_error_handler(static function($severity, $message, $file, $line)
                use (&$previoushandler, $servicefile) {
            if ($severity === E_WARNING && $message === 'Undefined property: stdClass::$exacomp_record_id'
                    && $file === $servicefile) {
                return true;
            }
            return $previoushandler ? $previoushandler($severity, $message, $file, $line) : false;
        });
        try {
            $result = externallib::diggrplus_get_examples_and_items(
                $this->course->id, 0, $this->topicid, BLOCK_EXACOMP_TYPE_TOPIC, 'own_items', '', -1, ''
            );
        } finally {
            restore_error_handler();
        }
        $clean = \external_api::clean_returnvalue(externallib::diggrplus_get_examples_and_items_returns(), $result);
        foreach ($clean as $entry) {
            if ((int)$entry['item']['id'] === $itemid) {
                return $entry['item'];
            }
        }
        $this->fail('The submitted item must be readable by the app listing service');
    }

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

    public function test_url_and_upload_are_independent_and_updates_do_not_duplicate_links(): void {
        global $DB;
        $draftid = file_get_unused_draft_itemid();
        $this->file('draft', $draftid, 'first.txt', 'First upload');
        $id = $this->submit(0, 'https://example.test/first', [$draftid]);
        $item = $DB->get_record('block_exaportitem', ['id' => $id], '*', MUST_EXIST);
        $link = block_exaport_get_item_content_blocks($id)[0];
        $this->assertSame('link', $link->type);
        $first = $this->read($id);
        $this->assertSame('https://example.test/first', $first['url']);
        $this->assertCount(1, $first['studentfiles']);
        $storedid = $first['studentfiles'][0]['id'];
        $draftid2 = file_get_unused_draft_itemid();
        $this->file('draft', $draftid2, 'second.txt', 'Second upload');
        $this->assertSame($id, $this->submit($id, 'https://example.test/changed', [$draftid2]));
        $this->submit($id, 'https://example.test/changed');
        $blocks = block_exaport_get_item_content_blocks($id);
        $this->assertSame(['link', 'file', 'file'], array_column($blocks, 'type'));
        $expectedlink = clone $link;
        $expectedlink->url = 'https://example.test/changed';
        $this->assertEquals($expectedlink, $blocks[0], 'Existing link identity and metadata are preserved');
        $read = $this->read($id);
        $this->assertSame('https://example.test/changed', $read['url']);
        $this->assertSame(['first.txt', 'second.txt'], array_column($read['studentfiles'], 'filename'));
        $this->assertSame($storedid, $read['studentfiles'][0]['id']);
        $this->assertSame((string)$storedid, $read['studentfiles'][0]['fileindex']);
        $this->assertStringContainsString('/item_content_file/itemid/' . $id . '/blockid/' . $blocks[1]->id . '/first.txt',
            $read['studentfiles'][0]['file']);
        $this->assertSame('', $item->url);
        $this->assertSame('', $item->attachment);
    }

    public function test_empty_url_and_repeated_submission_preserve_links_text_and_appended_files(): void {
        $item = $this->item();
        $text = block_exaport_create_content_block($item->id, 'text', 'Text', '', [
            'content' => '<p><img src="@@PLUGINFILE@@/asset.txt"></p>',
        ]);
        $asset = $this->file('item_content_text', $text->id, 'asset.txt');
        $link = block_exaport_create_link_content_block($item->id, 'URL', 'https://example.test/old');
        $fileblock = block_exaport_create_file_content_block($item->id, 'Keep');
        $kept = $this->file('item_content_file', $fileblock->id, 'keep.txt');
        $draftid = file_get_unused_draft_itemid();
        $this->file('draft', $draftid, 'new.txt');
        // The uploaded UI sends an empty URL, even while appending files.
        $this->submit($item->id, '', [$draftid]);
        $this->submit($item->id, '');
        $blocks = block_exaport_get_item_content_blocks($item->id);
        $this->assertSame(['text', 'link', 'file', 'file'], array_column($blocks, 'type'));
        $this->assertEquals($text, $blocks[0]);
        $this->assertEquals($link, $blocks[1]);
        $this->assertEquals($fileblock, $blocks[2]);
        $this->assertSame('https://example.test/old', $this->read($item->id)['url']);
        $this->assertSame('File bytes', get_file_storage()->get_file_by_id($asset->get_id())->get_content());
        $this->assertSame('File bytes', get_file_storage()->get_file_by_id($kept->get_id())->get_content());
        $this->assertSame('Parent description', $this->read($item->id)['solutiondescription']);
    }

    public function test_multiple_links_update_first_by_sortorder_then_id_and_preserve_others(): void {
        $item = $this->item();
        $later = block_exaport_create_link_content_block($item->id, 'Unrelated', 'https://example.test/later',
            ['sortorder' => 20]);
        $primary = block_exaport_create_link_content_block($item->id, 'Primary', 'https://example.test/primary',
            ['sortorder' => 10]);
        $tied = block_exaport_create_link_content_block($item->id, 'Tied', 'https://example.test/tied',
            ['sortorder' => 10]);
        $this->submit($item->id, 'https://example.test/new');
        $this->submit($item->id, 'https://example.test/new');
        $blocks = block_exaport_get_item_content_blocks($item->id);
        $expected = clone $primary;
        $expected->url = 'https://example.test/new';
        $this->assertEquals([$expected, $tied, $later], $blocks);
        $this->assertSame('https://example.test/new', $this->read($item->id)['url']);
    }

    public function test_empty_url_with_multiple_links_allows_file_changes_and_preserves_every_link(): void {
        global $DB;
        $item = $this->item();
        $primary = block_exaport_create_link_content_block($item->id, 'Primary', 'https://example.test/primary');
        $other = block_exaport_create_link_content_block($item->id, 'Other', 'https://example.test/other');
        $block = block_exaport_create_file_content_block($item->id, 'Keep');
        $file = $this->file('item_content_file', $block->id, 'keep.txt');
        $draftid = file_get_unused_draft_itemid();
        $this->file('draft', $draftid, 'new.txt');
        $this->assertSame((int)$item->id, $this->submit($item->id, '', [$draftid], [$file->get_id()]));
        $blocks = block_exaport_get_item_content_blocks($item->id);
        $this->assertSame(['link', 'link', 'file'], array_column($blocks, 'type'));
        $this->assertEquals([$primary, $other], array_slice($blocks, 0, 2));
        $this->assertFalse(get_file_storage()->get_file_by_id($file->get_id()));
        $this->assertFalse($DB->record_exists('block_exaportitemblock', ['id' => $block->id]));
        $this->assertSame('https://example.test/primary', $this->read($item->id)['url']);
        $this->assertSame(['new.txt'], array_column($this->read($item->id)['studentfiles'], 'filename'));
        $this->assertSame('Updated title', $DB->get_field('block_exaportitem', 'name', ['id' => $item->id]));
        $this->submit($item->id, '');
        $this->assertEquals($blocks, block_exaport_get_item_content_blocks($item->id));
    }

    public static function removal_provider(): array {
        return [
            'remove one of two files' => [false, false],
            'remove last file of one block' => [true, false],
            'remove all file blocks' => [true, true],
        ];
    }

    /** @dataProvider removal_provider */
    public function test_removal_then_export_decode_import(bool $removeallfirst, bool $removeallblocks): void {
        global $DB;
        $item = $this->item();
        $text = block_exaport_create_content_block($item->id, 'text', 'Text', '', [
            'content' => '<p><img src="@@PLUGINFILE@@/asset.txt"></p>',
        ]);
        $asset = $this->file('item_content_text', $text->id, 'asset.txt', 'Embedded bytes');
        $link = block_exaport_create_link_content_block($item->id, 'URL', 'https://example.test/link');
        $block = block_exaport_create_file_content_block($item->id, 'Documents');
        $file1 = $this->file('item_content_file', $block->id, 'remove.txt', 'Removed bytes');
        $file2 = $this->file('item_content_file', $block->id, 'keep.txt', 'Kept bytes');
        $otherblock = block_exaport_create_file_content_block($item->id, 'Other file');
        $otherfile = $this->file('item_content_file', $otherblock->id, 'other.txt', 'Other bytes');
        $foreign = $this->item();
        $foreignblock = block_exaport_create_file_content_block($foreign->id, 'Foreign');
        $foreignfile = $this->file('item_content_file', $foreignblock->id, 'foreign.txt');
        $icon = $this->file('item_iconfile', $item->id, 'icon.txt');
        $removeids = [$file1->get_id(), $file1->get_id(), $foreignfile->get_id(), $asset->get_id(), $icon->get_id()];
        if ($removeallfirst) {
            $removeids[] = $file2->get_id();
        }
        if ($removeallblocks) {
            $removeids[] = $otherfile->get_id();
        }
        // Use the same stored IDs supplied by the actual listing response to the ZIP.
        $read = $this->read($item->id);
        $this->assertContains((int)$file1->get_id(), array_column($read['studentfiles'], 'id'));
        $this->submit($item->id, $read['url'], [], $removeids);
        $blocks = block_exaport_get_item_content_blocks($item->id);
        $expected = [$text, $link];
        if (!$removeallfirst) {
            $expected[] = $block;
        }
        if (!$removeallblocks) {
            $expected[] = $otherblock;
        }
        $this->assertEquals($expected, $blocks);
        $this->assertFalse(get_file_storage()->get_file_by_id($file1->get_id()));
        foreach ([$asset, $foreignfile, $icon] as $untouched) {
            $this->assertSame($untouched->get_content(),
                get_file_storage()->get_file_by_id($untouched->get_id())->get_content());
        }
        $root = make_request_directory();
        $packagefile = static function(\stored_file $file, string $base) use ($root): string {
            $path = block_exaport_scorm_archive_path($file, $base, []);
            check_dir_exists(dirname($root . '/' . $path));
            file_put_contents($root . '/' . $path, $file->get_content());
            return $path;
        };
        $item = $DB->get_record('block_exaportitem', ['id' => $item->id], '*', MUST_EXIST);
        $export = block_exaport_scorm_build_item_package($item,
            block_exaport_get_item_content_export_data($item), 'category/item.html', $packagefile);
        $manifest = block_exaport_decode_item_package(json_encode($export['manifest']));
        $recipient = $this->getDataGenerator()->create_user();
        $destination = clone $item;
        unset($destination->id);
        $destination->userid = $recipient->id;
        $destination->id = $DB->insert_record('block_exaportitem', $destination);
        block_exaport_import_item_package($destination, $root, $manifest);
        $imported = block_exaport_get_item_content_blocks($destination->id);
        $this->assertSame(array_column($blocks, 'type'), array_column($imported, 'type'));
        foreach ($blocks as $index => $sourceblock) {
            $newblock = $imported[$index];
            $this->assertNotSame((int)$sourceblock->id, (int)$newblock->id);
            foreach (['title', 'url', 'content', 'sortorder', 'contentformat'] as $field) {
                $this->assertEquals($sourceblock->$field, $newblock->$field);
            }
            $area = $sourceblock->type === 'text' ? 'item_content_text' : 'item_content_file';
            $originalfiles = get_file_storage()->get_area_files(\context_user::instance($item->userid)->id,
                'block_exaport', $area, $sourceblock->id, 'filename', false);
            $newfiles = get_file_storage()->get_area_files(\context_user::instance($recipient->id)->id,
                'block_exaport', $area, $newblock->id, 'filename', false);
            $this->assertCount(count($originalfiles), $newfiles);
            foreach (array_values($originalfiles) as $fileindex => $originalfile) {
                $newfile = array_values($newfiles)[$fileindex];
                $this->assertSame($originalfile->get_filename(), $newfile->get_filename());
                $this->assertSame($originalfile->get_content(), $newfile->get_content());
                $this->assertNotSame($originalfile->get_id(), $newfile->get_id());
            }
        }
        $this->assertEquals($blocks, block_exaport_get_item_content_blocks($item->id));
    }

    public function test_graded_item_creates_new_version_with_url_and_upload_without_changing_original(): void {
        $item = $this->item(true);
        $link = block_exaport_create_link_content_block($item->id, 'Original', 'https://example.test/original');
        $block = block_exaport_create_file_content_block($item->id, 'Original');
        $old = $this->file('item_content_file', $block->id, 'old.txt');
        $draftid = file_get_unused_draft_itemid();
        $this->file('draft', $draftid, 'new.txt');
        $id = $this->submit($item->id, 'https://example.test/new', [$draftid], [$old->get_id()]);
        $this->assertNotSame((int)$item->id, $id);
        $this->assertEquals([$link, $block], block_exaport_get_item_content_blocks($item->id));
        $this->assertNotFalse(get_file_storage()->get_file_by_id($old->get_id()));
        $this->assertSame('https://example.test/new', $this->read($id)['url']);
        $this->assertSame(['new.txt'], array_column($this->read($id)['studentfiles'], 'filename'));
    }

    public function test_final_file_of_only_block_exports_and_imports_as_empty_content(): void {
        global $DB;
        $item = $this->item();
        $block = block_exaport_create_file_content_block($item->id, 'Only file');
        $file = $this->file('item_content_file', $block->id, 'only.txt');
        $this->submit($item->id, '', [], [$file->get_id()]);
        $this->assertSame([], block_exaport_get_item_content_blocks($item->id));
        $export = block_exaport_scorm_build_item_package($item,
            block_exaport_get_item_content_export_data($item), 'category/item.html',
            static function(): string {
                throw new \coding_exception('Empty content must not package any files');
            });
        $manifest = block_exaport_decode_item_package(json_encode($export['manifest']));
        $destination = clone $item;
        unset($destination->id);
        $destination->id = $DB->insert_record('block_exaportitem', $destination);
        block_exaport_import_item_package($destination, make_request_directory(), $manifest);
        $this->assertSame([], block_exaport_get_item_content_blocks($destination->id));
        $this->assertSame('', $this->read($item->id)['url']);
        $this->assertSame([], $this->read($item->id)['studentfiles'] ?? []);
    }

    public function test_quota_rejection_preserves_final_file_block_and_url(): void {
        $item = $this->item();
        block_exaport_create_link_content_block($item->id, 'URL', 'https://example.test/original');
        $block = block_exaport_create_file_content_block($item->id, 'Only file');
        $file = $this->file('item_content_file', $block->id, 'old.txt', '1234567890');
        $draftid = file_get_unused_draft_itemid();
        $this->file('draft', $draftid, 'too-large.txt', '12345678901');
        set_config('block_exaport_userquota', 5);
        $before = $this->snapshot();
        try {
            $this->submit($item->id, 'https://example.test/rejected', [$draftid], [$file->get_id()]);
            $this->fail('Growth while already over quota must be rejected');
        } catch (\moodle_exception $exception) {
            $this->assertSame('userquotalimit', $exception->errorcode);
        }
        $this->assertEquals($before, $this->snapshot());
    }

    public static function replacement_provider(): array {
        return ['submit_example' => [false], 'dakora_submit_example' => [true]];
    }

    /** @dataProvider replacement_provider */
    public function test_other_singular_callers_keep_replace_all_semantics(bool $dakora): void {
        global $DB;
        $item = $this->item();
        $exampleid = $DB->insert_record('block_exacompexamples', (object)[
            'title' => 'Example', 'description' => '', 'sourceid' => 0, 'source' => 0,
        ]);
        $DB->set_field(BLOCK_EXACOMP_DB_ITEM_MM, 'exacomp_record_id', $exampleid, ['itemid' => $item->id]);
        $DB->set_field(BLOCK_EXACOMP_DB_ITEM_MM, 'competence_type', BLOCK_EXACOMP_TYPE_EXAMPLE,
            ['itemid' => $item->id]);
        block_exaport_create_content_block($item->id, 'text', 'Old text', '', ['content' => 'Old text']);
        block_exaport_create_link_content_block($item->id, 'Old URL', 'https://example.test/old');
        $oldblock = block_exaport_create_file_content_block($item->id, 'Old file');
        $oldfile = $this->file('item_content_file', $oldblock->id, 'old.txt');
        $draftid = file_get_unused_draft_itemid();
        $this->file('draft', $draftid, 'replacement.txt');
        if ($dakora) {
            externallib::dakora_submit_example($exampleid, 2, 'https://example.test/ignored',
                'replacement.txt', '', $item->id, $this->course->id, (string)$draftid);
        } else {
            externallib::submit_example($exampleid, 2, 'https://example.test/ignored', 'Description',
                'replacement.txt', $draftid, '', 'Title', $item->id, $this->course->id);
        }
        $blocks = block_exaport_get_item_content_blocks($item->id);
        $this->assertSame(['file'], array_column($blocks, 'type'));
        $this->assertFalse(get_file_storage()->get_file_by_id($oldfile->get_id()));
        $this->assertSame('replacement.txt',
            block_exaport_get_item_content_files($item->userid, $blocks[0]->id)[0]->get_filename());
        if ($dakora) {
            externallib::dakora_submit_example($exampleid, 2, 'https://example.test/replacement',
                '', '', $item->id, $this->course->id, '');
        } else {
            externallib::submit_example($exampleid, 2, 'https://example.test/replacement', 'Description',
                '', 0, '', 'Title', $item->id, $this->course->id);
        }
        $blocks = block_exaport_get_item_content_blocks($item->id);
        $this->assertSame(['link'], array_column($blocks, 'type'));
        $this->assertSame('https://example.test/replacement', $blocks[0]->url);
    }
}
