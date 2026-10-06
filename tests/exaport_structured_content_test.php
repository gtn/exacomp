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
        // Web services must never delete all item content, only blocks they own.
        $this->assertStringNotContainsString('block_exaport_delete_item_content(', $source);
        $this->assertStringContainsString('block_exaport_delete_item_content_block($item, $block)', $source);
        $this->assertStringContainsString('block_exaport_delete_item(', $source);
    }

    public function test_browser_submission_is_atomic_and_keeps_parent_content_empty(): void {
        $source = file_get_contents(__DIR__ . '/../example_submission.php');

        $this->assertStringContainsString("'url' => '', 'attachment' => ''", $source);
        $this->assertStringContainsString('example_submission_content::select(', $source);
        $this->assertStringContainsString('example_submission_content::store(', $source);
        $this->assertStringContainsString('$DB->start_delegated_transaction()', $source);
        $this->assertStringContainsString('$transaction->allow_commit()', $source);
        $this->assertLessThan(
            strpos($source, 'block_exacomp_notify_all_teachers_about_submission('),
            strpos($source, '$transaction->allow_commit()')
        );
    }
}
