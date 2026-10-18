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

namespace block_exacomp;

defined('MOODLE_INTERNAL') || die();

/**
 * Calendar parser compatibility with documented file and inline-content calls.
 *
 * @package block_exacomp
 * @copyright 2026 GTN - Global Training Network GmbH
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class calfileparser_test extends \basic_testcase {
    protected function setUp(): void {
        parent::setUp();
        require_once(__DIR__ . '/../lib/calFileParser/CalFileParser.php');
    }

    public function test_constructor_sets_array_output_without_deprecations(): void {
        $deprecations = [];
        set_error_handler(static function($severity, $message) use (&$deprecations) {
            $deprecations[] = $message;
            return true;
        }, E_DEPRECATED);
        try {
            $parser = new \CalFileParser();
        } finally {
            restore_error_handler();
        }
        $this->assertSame('array', $parser->get_output());
        $this->assertSame([], $deprecations);
    }

    public function test_parse_file_with_one_argument_returns_calendar_event(): void {
        $parser = new \CalFileParser();
        $parser->set_timezone('UTC');
        $events = $parser->parse(__DIR__ . '/fixtures/compatibility.ics');

        $this->assertCount(1, $events);
        $this->assertSame('Review fixture', $events[0]['SUMMARY']);
        $this->assertSame('2026-10-18T10:00:00+00:00', $events[0]['DTSTART']->format('c'));
        $this->assertSame('2026-10-18T11:00:00+00:00', $events[0]['DTEND']->format('c'));
    }

    public function test_parse_inline_content_keeps_three_argument_usage(): void {
        $parser = new \CalFileParser();
        $parser->set_timezone('UTC');
        $events = $parser->parse('', null, file_get_contents(__DIR__ . '/fixtures/compatibility.ics'));

        $this->assertCount(1, $events);
        $this->assertSame('Review fixture', $events[0]['SUMMARY']);
        $this->assertSame('2026-10-18T10:00:00+00:00', $events[0]['DTSTART']->format('c'));
        $this->assertSame('2026-10-18T11:00:00+00:00', $events[0]['DTEND']->format('c'));
    }
}
