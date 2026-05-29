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

use block_exacomp\globals as g;
use block_exacomp\import_exception;
use block_exacomp\permissions;
use Super\Cache;

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/classes.php';
require_once __DIR__ . '/../block_exacomp.php';
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->libdir . '/badgeslib.php');
// Backwards compatibility: In Moodle 5.2, awardlib.php was removed and its functions
// (process_manual_award, process_manual_revoke) were moved into the
// \core_badges\award_manager class (see MDL-83902). On Moodle < 5.2 we still need
// to include the old file because the global functions do not exist otherwise.
if (file_exists($CFG->dirroot . '/badges/lib/awardlib.php')) {
    require_once($CFG->dirroot . '/badges/lib/awardlib.php');
}
require_once($CFG->dirroot . '/cohort/lib.php');
require_once __DIR__ . '/setapp.php';
