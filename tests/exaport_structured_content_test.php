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

use block_exacomp\externallib\externallib;

/**
 * Focused regression tests for Exaport's structured item-content boundary.
 *
 * End-to-end storage, URL authorisation and external response validation are
 * covered when this suite is run in Moodle with the matching Exaport version.
 *
 * @covers \block_exacomp\externallib\externallib
 */
final class exaport_structured_content_test extends \advanced_testcase {

    /** Invoke the small schema adapter while keeping it private in production. */
    private function student_files(array $content): array {
        $method = new \ReflectionMethod(externallib::class, 'exaport_student_files');
        $method->setAccessible(true);
        return $method->invoke(null, $content);
    }

    public function test_structured_files_keep_serializer_order_and_urls(): void {
        $files = $this->student_files(['files' => [
            ['id' => 8, 'filename' => 'b.txt', 'mimetype' => 'text/plain',
                'url' => 'https://moodle.test/webservice/pluginfile.php/3/block_exaport/item_content_file/41/8/b.txt?token=a%2Bb'],
            ['id' => 12, 'filename' => 'a.png', 'mimetype' => 'image/png', 'isimage' => true,
                'url' => 'https://moodle.test/webservice/pluginfile.php/3/block_exaport/item_content_file/41/12/a.png?token=a%2Bb'],
        ]]);

        $this->assertSame([8, 12], array_column($files, 'id'));
        $this->assertSame(['b.txt', 'a.png'], array_column($files, 'filename'));
        $this->assertStringContainsString('/webservice/pluginfile.php/', $files[0]['file']);
        $this->assertStringContainsString('/item_content_file/41/8/', $files[0]['file']);
        $this->assertStringContainsString('token=a%2Bb', $files[0]['file']);
        $this->assertStringNotContainsString('portfoliofile.php', $files[0]['file']);
    }

    public function test_empty_structured_projection_has_no_legacy_fallback(): void {
        $this->assertSame([], $this->student_files([]));
        $this->assertSame([], $this->student_files(['files' => []]));
    }

    public function test_active_code_does_not_use_legacy_item_content_apis(): void {
        $source = file_get_contents(__DIR__ . '/../classes/externallib/externallib.php');
        $source = preg_replace('~/\\*.*?\\*/|//[^\\n]*~s', '', $source);

        $this->assertStringNotContainsString("'filearea' => 'item_file'", $source);
        $this->assertStringNotContainsString('block_exaport_get_item_single_file(', $source);
        $this->assertStringNotContainsString('block_exaport_get_item_files(', $source);
        $this->assertStringNotContainsString('block_exaport_get_files(', $source);
        $this->assertStringNotContainsString('block_exaport_file_remove(', $source);
    }
}
