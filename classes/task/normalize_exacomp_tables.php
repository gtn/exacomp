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


class normalize_exacomp_tables extends scheduled_task {
    public function get_name() {
        return block_exacomp_trans(['en:Normalize exacomp tables', 'de:Exacomp Tabellen normalisieren']);
    }

    public function execute() {
        data::normalize_database();
    }
}




