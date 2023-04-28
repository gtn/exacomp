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

namespace block_exacomp\task;

use core\task\scheduled_task;
use block_exacomp\data;

defined('MOODLE_INTERNAL') || die();

require_once __DIR__ . '/../../inc.php';


class clear_last_weeks_schedule extends scheduled_task {
    public function get_name() {
        return block_exacomp_trans(['en:Clear all unfinished tasks from last weeks schedule and put them into the planning storage again.', 'de:Alle unbearbeiteten Aufgaben im Wochenplan der letzten Woche entfernen und wieder in den Planungsspeicher schieben.']);
    }

    public function execute() {
        try {
            mtrace("Exabis Competence Grid: clear_last_weeks_schedule task is running:");
            block_exacomp_clear_last_weeks_schedule();
            mtrace("clear_last_weeks_schedule done");
        } catch (moodle_exception $e) {
            mtrace("clear_last_weeks_schedule tables: " . $e->getMessage());
        }
    }
}




