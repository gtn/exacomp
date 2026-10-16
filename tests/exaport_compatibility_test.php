<?php
// This file is part of Moodle - http://moodle.org/.

namespace block_exacomp;

use block_exacomp\externallib\externallib;
use block_exacomp\local\example_submission_content;
use block_exacomp\local\exaport_compatibility;

defined('MOODLE_INTERNAL') || die();

/**
 * Incompatible optional integration calls must leave stored data unchanged.
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
final class exaport_compatibility_test extends \advanced_testcase {
    /** Exercise public callers with an old Exaport, including reads of empty results. */
    public function test_old_exaport_blocks_calls_without_changing_records_or_files(): void {
        global $CFG, $DB, $USER;
        $this->resetAfterTest(true);
        $this->setAdminUser();
        require_once($CFG->dirroot . '/blocks/exacomp/classes/externallib/base.php');

        $itemid = $DB->insert_record('block_exaportitem', (object)[
            'userid' => $USER->id, 'name' => 'Keep submission', 'type' => 'file', 'url' => '',
            'intro' => 'Keep description', 'timemodified' => 123,
        ]);
        $draft = get_file_storage()->create_file_from_string([
            'contextid' => \context_user::instance($USER->id)->id, 'component' => 'user',
            'filearea' => 'draft', 'itemid' => 123, 'filepath' => '/', 'filename' => 'keep.txt',
        ], 'Keep these bytes');
        // The earlier structured-content release must also be rejected.
        set_config('version', 2026101502, 'block_exaport');

        $tables = ['block_exaportitem', 'block_exaportitemcate', 'block_exaportview',
            'block_exaportviewblock', 'block_exaportitemcomm', BLOCK_EXACOMP_DB_ITEM_MM, 'files'];
        $before = [];
        foreach ($tables as $table) {
            $before[$table] = $DB->get_records($table, null, 'id');
        }
        $calls = [
            ['submit_example', [1, 1, 'https://example.test/', '', '', 123, '', '', $itemid, 1]],
            ['dakora_submit_example', [1, 1, '', 'keep.txt', '', $itemid, 1, '123']],
            ['diggrplus_submit_item', [1, 1, '', 'keep.txt', '', '123', $itemid, 1]],
            ['delete_item', [$itemid]],
            ['get_item_for_example', [$USER->id, $itemid]],
            ['dakora_get_example_information', [1, $USER->id, 1]],
            ['diggrplus_get_examples_and_items', []],
            ['diggrplus_get_teacher_examples_and_items', [1, $USER->id, 1, 4]],
            ['dakoraplus_get_example_and_item', [1, 1, $itemid]],
            ['dakoraplus_get_teacher_example_and_item', [1, 1, $USER->id]],
        ];
        foreach ($calls as [$method, $arguments]) {
            $this->assert_update_required(static function() use ($method, $arguments): void {
                externallib::$method(...$arguments);
            });
        }
        $this->assert_update_required(static function() use ($USER): void {
            example_submission_content::select($USER->id, 123, '');
        });
        $this->assert_update_required(static function() use ($itemid, $USER, $draft): void {
            example_submission_content::store((object)['id' => $itemid, 'userid' => $USER->id],
                'Keep submission', (object)['url' => 'https://example.test/', 'draftfiles' => [$draft]]);
        });
        foreach ($tables as $table) {
            $this->assertEquals($before[$table], $DB->get_records($table, null, 'id'), $table);
        }
        $this->assertSame('Keep these bytes', $draft->get_content());
    }

    /** Exacomp itself may be installed when Exaport is absent. */
    public function test_missing_exaport_gives_an_actionable_integration_error(): void {
        $this->resetAfterTest(true);
        unset_config('version', 'block_exaport');
        $this->assert_update_required(static function(): void {
            exaport_compatibility::require_available();
        });
    }

    /** An installed version is insufficient when Exaport's code has been removed. */
    public function test_missing_exaport_code_is_rejected(): void {
        global $CFG;
        $this->resetAfterTest(true);
        class_exists(exaport_compatibility::class);
        get_string('exaportupdaterequired', 'block_exacomp');
        set_config('version', exaport_compatibility::MIN_VERSION, 'block_exaport');
        $directory = make_request_directory();
        $dirroot = $CFG->dirroot;
        try {
            $CFG->dirroot = $directory;
            $this->assert_update_required(static function(): void {
                exaport_compatibility::require_available();
            });
        } finally {
            $CFG->dirroot = $dirroot;
        }
    }

    /** The matching migrated version is usable. */
    public function test_compatible_exaport_is_accepted(): void {
        $this->resetAfterTest(true);
        set_config('version', exaport_compatibility::MIN_VERSION, 'block_exaport');
        exaport_compatibility::require_available();
        $this->assertTrue(function_exists('block_exaport_get_item_content_webservice_data'));
    }

    /** Check both the stable error code and the administrator's update instruction. */
    private function assert_update_required(callable $call): void {
        try {
            $call();
            $this->fail('An incompatible Exaport integration call was accepted');
        } catch (\moodle_exception $exception) {
            $this->assertSame('exaportupdaterequired', $exception->errorcode);
            $this->assertStringContainsString('install or update Exaport', $exception->getMessage());
        }
    }
}
