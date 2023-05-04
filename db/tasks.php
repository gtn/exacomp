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

defined('MOODLE_INTERNAL') || die();

$tasks = array(
    array(
        'classname' => 'block_exacomp\task\import',
        'blocking' => 0,
        'minute' => '7',
        'hour' => '23',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*',
    ),
    array(
        'classname' => 'block_exacomp\task\import_additional',
        'blocking' => 0,
        'minute' => '30',
        'hour' => '1',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*',
    ),
    array(
        'classname' => 'block_exacomp\task\komettranslator_to_exacomp',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '*',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*',
    ),
    array(
        'classname' => 'block_exacomp\task\question_grading',
        'blocking' => 0,
        'minute' => '*',
        'hour' => '*',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*',
    ),
    array(
        'classname' => 'block_exacomp\task\normalize_exacomp_tables',
        'blocking' => 0,
        'minute' => '55',
        'hour' => '3',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*',
    ),
    array(
        'classname' => 'block_exacomp\task\clear_exacomp_weekly_schedule',
        'blocking' => 0,
        'minute' => '59',
        'hour' => '23',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '0',
    ),
);
