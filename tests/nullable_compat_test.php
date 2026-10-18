<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Regression tests for the explicitly nullable signatures (PHP 8.4 deprecation fix).
 *
 * Pins the behaviour for omitted and null arguments, so the type declarations
 * can change without altering what callers get back.
 *
 * @package    block_exacomp
 * @copyright  2026 gtn gmbh
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_exacomp;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/blocks/exacomp/lib/lib.php');

final class nullable_compat_test extends \advanced_testcase {

    public function test_url_create_without_params_has_no_query(): void {
        $url = \block_exacomp\common\url::create('/blocks/exacomp/view.php');
        $this->assertStringNotContainsString('?', $url->out(false));
    }

    public function test_url_params_null_returns_current_params(): void {
        $url = \block_exacomp\common\url::create('/x.php', ['a' => 1]);
        $this->assertEquals(['a' => 1], $url->params(null));
    }

    public function test_url_copy_null_keeps_params(): void {
        $url = \block_exacomp\common\url::create('/x.php', ['a' => 1]);
        $this->assertEquals(['a' => 1], $url->copy(null)->params());
    }

    public function test_url_copy_null_value_removes_param(): void {
        $url = \block_exacomp\common\url::create('/x.php', ['a' => 1, 'b' => 2]);
        $this->assertEquals(['a' => 1], $url->copy(['b' => null])->params());
    }

    public function test_array_filter_recursive_without_callback(): void {
        $input = ['a' => 0, 'b' => 1, 'c' => ['d' => null, 'e' => 2]];
        $this->assertEquals(['b' => 1, 'c' => ['e' => 2]], block_exacomp_array_filter_recursive($input));
    }

    public function test_array_filter_recursive_with_callback(): void {
        $input = ['a' => 0, 'b' => 1, 'c' => ['d' => 5, 'e' => 2]];
        $callback = static fn($v) => $v !== 1;
        $this->assertEquals(['a' => 0, 'c' => ['d' => 5, 'e' => 2]], block_exacomp_array_filter_recursive($input, $callback));
    }

    public function test_update_record_with_null_conditions_updates_by_id(): void {
        global $DB;
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();

        $this->assertTrue(common\db::update_record('user', (object)[
            'id' => $user->id, 'firstname' => 'Updated',
        ], null));
        $this->assertSame('Updated', $DB->get_field('user', 'firstname', ['id' => $user->id]));
    }

    public function test_subject_queries_accept_omitted_and_null_conditions(): void {
        global $DB;
        $this->resetAfterTest();
        $id = $DB->insert_record('block_exacompsubjects', (object)[
            'title' => 'Compatibility subject', 'sourceid' => 123,
        ]);
        $sql = 'SELECT * FROM {block_exacompsubjects}';

        $this->assertSame('Compatibility subject', subject::get_records()[$id]->title);
        $this->assertSame('Compatibility subject', subject::get_records(null)[$id]->title);
        $this->assertSame('Compatibility subject', subject::get_objects()[$id]->title);
        $this->assertSame('Compatibility subject', subject::get_objects(null)[$id]->title);
        $this->assertSame('Compatibility subject', subject::get_records_sql($sql)[$id]->title);
        $this->assertSame('Compatibility subject', subject::get_records_sql($sql, null)[$id]->title);
        $this->assertSame('Compatibility subject', subject::get_objects_sql($sql)[$id]->title);
        $this->assertSame('Compatibility subject', subject::get_objects_sql($sql, null)[$id]->title);
    }

    public function test_subject_factories_accept_null_database_layer(): void {
        $data = (object)['id' => 123, 'sourceid' => 123, 'title' => 'Compatibility subject'];

        $this->assertSame('Compatibility subject', (new subject($data, null))->title);
        $this->assertSame('Compatibility subject', subject::create($data, null)->title);
        $this->assertSame('Compatibility subject', subject::create_objects([$data], [], null)[123]->title);
    }

    // The legacy Moodle external API requires an isolated PHPUnit process.
    #[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
    #[\PHPUnit\Framework\Attributes\PreserveGlobalState(false)]
    public function test_webservice_return_preserves_subject_data_with_null_cycle_guard(): void {
        global $CFG;
        require_once($CFG->dirroot . '/blocks/exacomp/classes/externallib/base.php');
        $value = ['subject' => subject::create(['id' => 123, 'sourceid' => 123, 'title' => 'Compatibility subject'])];

        $default = externallib\base::prepare_ws_return($value);
        $explicit = externallib\base::prepare_ws_return($value, null);

        $this->assertInstanceOf(\stdClass::class, $explicit['subject']);
        $this->assertSame(123, $explicit['subject']->id);
        $this->assertSame('Compatibility subject', $explicit['subject']->title);
        $this->assertEquals($default, $explicit);
    }

    // The legacy Moodle external API requires an isolated PHPUnit process.
    #[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
    #[\PHPUnit\Framework\Attributes\PreserveGlobalState(false)]
    public function test_null_experience_level_preserves_course_settings(): void {
        global $CFG;
        $this->resetAfterTest();
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        require_once($CFG->dirroot . '/blocks/exacomp/classes/externallib/base.php');
        require_once($CFG->dirroot . '/blocks/exacomp/classes/externallib/externallib.php');
        externallib\externallib::dakoraplus_save_coursesettings($course->id, 'expert');

        foreach ([[$course->id], [$course->id, null]] as $args) {
            $result = externallib\externallib::dakoraplus_save_coursesettings(...$args);
            $result = \external_api::clean_returnvalue(
                externallib\externallib::dakoraplus_save_coursesettings_returns(), $result);
            $this->assertSame(['success' => true], $result);
            $this->assertSame('expert', block_exacomp_get_settings_by_course($course->id)->experience_level);
        }
    }
}
