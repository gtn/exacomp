<?php
// This file is part of Moodle - http://moodle.org/.

/**
 * Tests for structured Exaport item content.
 *
 * @package block_exacomp
 * @copyright 2026 GTN - Global Training Network GmbH
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_exacomp;

defined('MOODLE_INTERNAL') || die();

/**
 * Focused regression tests for Exaport's structured item-content boundary.
 *
 * End-to-end storage, URL authorisation and external response validation are
 * covered when this suite is run in Moodle with the matching Exaport version.
 */
final class exaport_structured_content_test extends \advanced_testcase {

    private function require_exaport_content_api(): void {
        global $CFG;

        if (!is_file($CFG->dirroot . '/blocks/exaport/inc.php')) {
            $this->markTestSkipped('Exaport is not installed.');
        }
        require_once $CFG->dirroot . '/blocks/exaport/lib/lib.php';
        require_once $CFG->dirroot . '/blocks/exaport/inc.php';
        foreach ([
            'block_exaport_get_item_content_blocks',
            'block_exaport_get_item_content_files',
            'block_exaport_get_item_content_webservice_data',
            'block_exaport_create_link_content_block',
            'block_exaport_delete_item_content',
            'block_exaport_import_stored_file_into_content_block',
        ] as $function) {
            if (!function_exists($function)) {
                $this->markTestSkipped('The installed Exaport version lacks structured-content APIs.');
            }
        }
    }

    private function invoke_private_externallib_method(string $method, array $arguments) {
        global $CFG;

        require_once __DIR__ . '/../classes/externallib/externallib.php';

        $reflection = new \ReflectionMethod(\block_exacomp\externallib\externallib::class, $method);
        $reflection->setAccessible(true);
        return $reflection->invokeArgs(null, $arguments);
    }

    public function test_active_code_does_not_use_legacy_item_content_apis(): void {
        $sources = [
            file_get_contents(__DIR__ . '/../example_submission.php'),
            file_get_contents(__DIR__ . '/../classes/externallib/externallib.php'),
        ];
        $source = preg_replace('~/\\*.*?\\*/|//[^\\n]*~s', '', implode("\n", $sources));

        $this->assertStringNotContainsString("'filearea' => 'item_file'", $source);
        $this->assertStringNotContainsString('block_exaport_get_item_single_file(', $source);
        $this->assertStringNotContainsString('block_exaport_get_item_files(', $source);
        $this->assertStringNotContainsString('block_exaport_get_files(', $source);
        $this->assertStringNotContainsString('block_exaport_file_remove(', $source);
        $this->assertStringContainsString('block_exaport_import_stored_file_into_content_block(', $source);
        $this->assertStringContainsString('block_exaport_create_link_content_block(', $source);
        $this->assertStringContainsString('block_exaport_delete_item(', $source);
    }

    public function test_browser_submission_is_atomic_and_keeps_parent_content_empty(): void {
        $source = file_get_contents(__DIR__ . '/../example_submission.php');

        $this->assertStringContainsString("'url' => '', 'attachment' => ''", $source);
        $this->assertStringContainsString('$DB->start_delegated_transaction()', $source);
        $this->assertStringContainsString('$transaction->allow_commit()', $source);
        $this->assertLessThan(
            strpos($source, 'block_exacomp_notify_all_teachers_about_submission('),
            strpos($source, '$transaction->allow_commit()')
        );
    }

    public function test_structured_submission_file_identity_and_removal_roundtrip(): void {
        global $DB;

        $this->require_exaport_content_api();
        $this->resetAfterTest();

        $owner = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $itemid = $DB->insert_record('block_exaportitem', (object)[
            'userid' => $owner->id,
            'name' => 'Structured submission',
            'intro' => '',
            'url' => '',
            'attachment' => '',
            'type' => 'file',
            'timemodified' => time(),
            'courseid' => $course->id,
        ]);
        $item = $DB->get_record('block_exaportitem', ['id' => $itemid], '*', MUST_EXIST);

        // A new submission creates a block; a later replace removes it before
        // importing files, while an in-progress update appends another block.
        $this->invoke_private_externallib_method('replace_exaport_item_content',
            [$item, 'https://example.test/old', [], true]);
        $this->assertCount(1, block_exaport_get_item_content_blocks($itemid));

        $context = \context_user::instance($owner->id);
        $fs = get_file_storage();
        $draftfiles = [];
        foreach ([
            ['first.png', file_get_contents(__DIR__ . '/../pix/file_32.png')],
            ['nested/second.txt', 'second file contents'],
        ] as [$path, $contents]) {
            $draftitemid = file_get_unused_draft_itemid($owner->id);
            $filepath = dirname($path) === '.' ? '/' : '/' . dirname($path) . '/';
            $filename = basename($path);
            $draftfiles[] = $fs->create_file_from_string([
                'contextid' => $context->id,
                'component' => 'user',
                'filearea' => 'draft',
                'itemid' => $draftitemid,
                'filepath' => $filepath,
                'filename' => $filename,
                'userid' => $owner->id,
            ], $contents);
        }

        $this->invoke_private_externallib_method('replace_exaport_item_content',
            [$item, '', [$draftfiles[0]], true]);
        $this->invoke_private_externallib_method('replace_exaport_item_content',
            [$item, '', [$draftfiles[1]], false]);

        $blocks = block_exaport_get_item_content_blocks($itemid);
        $this->assertCount(2, $blocks);
        $files = [];
        foreach ($blocks as $block) {
            $this->assertSame('file', $block->type);
            $blockfiles = block_exaport_get_item_content_files($owner->id, $block->id);
            foreach ($blockfiles as $file) {
                $this->assertSame($block->id, $file->get_itemid());
                $this->assertSame('item_content_file', $file->get_filearea());
            }
            $files = array_merge($files, $blockfiles);
        }
        $this->assertCount(2, $files);
        $this->assertSame('/nested/', $files[1]->get_filepath());

        $content = block_exaport_get_item_content_webservice_data($item);
        $studentfiles = $this->invoke_private_externallib_method('exaport_student_files', [$item, $content]);
        $this->assertSame(array_map(static function($file) {
            return $file->get_id();
        }, $files), array_column($studentfiles, 'id'));
        $this->assertSame($files[0]->is_valid_image(), $studentfiles[0]['isimage']);

        $itemresponse = [
            'id' => $itemid,
            'name' => $item->name,
            'owner' => [
                'userid' => $owner->id,
                'fullname' => fullname($owner),
                'profileimageurl' => '',
            ],
            'studentfiles' => array_map(static function($file) {
                unset($file['isimage']);
                return $file;
            }, $studentfiles),
        ];
        $validated = \external_api::clean_returnvalue(
            \block_exacomp\externallib\externallib::diggrplus_get_examples_and_items_returns(),
            [[
                'courseid' => $course->id,
                'status' => 'inprogress',
                'subjectid' => 1,
                'subjecttitle' => 'Subject',
                'topicid' => 1,
                'topictitle' => 'Topic',
                'niveautitle' => 'Level',
                'niveauid' => 1,
                'timemodified' => time(),
                'item' => $itemresponse,
            ]]
        );
        $this->assertSame($studentfiles[0]['id'], $validated[0]['item']['studentfiles'][0]['id']);

        $this->invoke_private_externallib_method('remove_exaport_item_files', [$item, [(string)$studentfiles[0]['id']]]);
        $reloaded = block_exaport_get_item_content_webservice_data($item);
        $remaining = $this->invoke_private_externallib_method('exaport_student_files', [$item, $reloaded]);
        $this->assertCount(1, $remaining);
        $this->assertSame($studentfiles[1]['id'], $remaining[0]['id']);
    }
}
