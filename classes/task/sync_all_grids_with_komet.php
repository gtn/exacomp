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
/*
namespace block_exacomp\task;

use block_exacomp\data;
use block_exacomp\data_importer;
use block_exacomp\moodle_exception;
use core\task\scheduled_task;

defined('MOODLE_INTERNAL') || die();

require_once __DIR__ . '/../../inc.php';

class sync_all_grids_with_komet extends scheduled_task {
    public function get_name() {
        //		return block_exacomp_trans(['en:Import Data', 'de:Daten Importieren']);
        return block_exacomp_get_string('sync_all_grids_with_komet_description');
    }

    public function execute() {

        mtrace('Exabis Competence Grid: sync_all_grids_with_komet task is running.');

        try {
            if(get_config('exacomp', 'sync_all_grids_with_komet')){
                if (block_exacomp_delete_grids_missing_from_komet_import()) {
                    mtrace("Synchronize done");
                } else {
                    mtrace("Synchronize failed: unknown error");
                }
            }else {
                mtrace('sync_all_grids_with_komet is disabled');
                return true;
            }
        } catch (moodle_exception $e) {
            mtrace("Synchronize failed: " . $e->getMessage());
        }
        return true;
    }
}
*/
