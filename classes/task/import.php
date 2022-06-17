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

use block_exacomp\data;
use block_exacomp\data_importer;
use block_exacomp\moodle_exception;
use core\task\scheduled_task;

defined('MOODLE_INTERNAL') || die();

require_once __DIR__ . '/../../inc.php';

class import extends scheduled_task {
    public function get_name() {
        //		return block_exacomp_trans(['en:Import Data', 'de:Daten Importieren']);
        return block_exacomp_get_string('data_imported_title');
    }

    public function execute() {
        $xmlserverurl = get_config('exacomp', 'xmlserverurl');

        mtrace('Exabis Competence Grid: import task is running.');

        //import xml with provided server url
        if (!$xmlserverurl) {
            mtrace('nothing to import');
        }

        try {
            data::prepare();
            if (data_importer::do_import_url($xmlserverurl, null, BLOCK_EXACOMP_IMPORT_SOURCE_DEFAULT, false, -1)) {
                mtrace("import done");
                block_exacomp_settstamp();
            } else {
                mtrace("import failed: unknown error");
            }
        } catch (moodle_exception $e) {
            mtrace("import failed: " . $e->getMessage());
        }
        return true;
    }
}
