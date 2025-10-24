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
use block_exacomp\globals as g;
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
        global $DB;

        // set all importstate fields of the subjects of ALL sources to BLOCK_EXACOMP_SUBJECT_IMPORTING, meaning, it is currently importing and therefore in an unsure state
        $xmlserverurl = get_config('exacomp', 'xmlserverurl');
        $tasks = $DB->get_records(BLOCK_EXACOMP_DB_IMPORTTASKS, array('disabled' => 0)); // if an import task is just disabled, it will be handled as if it were removed.


        $delete_grids_missing_from_xmlserverurl = get_config('exacomp', 'delete_grids_missing_from_xmlserverurl') && ($xmlserverurl || !empty($tasks));
        if ($delete_grids_missing_from_xmlserverurl) {
            g::$DB->set_field(BLOCK_EXACOMP_DB_SUBJECTS, 'importstate', BLOCK_EXACOMP_SUBJECT_IMPORT_TASK_RUNNING);
        }
        $any_import_failed = false;

        // The original import tasks
        mtrace('Exabis Competence Grid: standard import task is running.');
        //import xml with provided server url
        if (!$xmlserverurl) {
            mtrace('nothing to import from the standard import url');
        }
        try {
            data::prepare();
            if (data_importer::do_import_url($xmlserverurl, null, BLOCK_EXACOMP_IMPORT_SOURCE_DEFAULT, false, -1, false)) {
                mtrace("import done");
                block_exacomp_settstamp();
            } else {
                mtrace("import failed: unknown error");
                $any_import_failed = true;
            }
        } catch (moodle_exception $e) {
            mtrace("import failed: " . $e->getMessage());
            $any_import_failed = true;
        }

        // The additional import task
        foreach ($tasks as $task) {
            mtrace('Exabis Competence Grid: import additional task is running.');
            //import xml with provided server url
            if (!$task->link) {
                mtrace('nothing to import from this task');
            }
            try {
                data::prepare();
                if (data_importer::do_import_url($task->link, null, BLOCK_EXACOMP_IMPORT_SOURCE_DEFAULT, false, $task->id)) {
                    mtrace("import done");
                    block_exacomp_settstamp();
                } else {
                    mtrace("import failed: unknown error");
                    $any_import_failed = true;
                }
            } catch (moodle_exception $e) {
                mtrace("import failed: " . $e->getMessage());
                $any_import_failed = true;
            }
        }

        // The deletion task
        // check if delete_grids_missing_from_xmlserverurl is set AND (if any url is in the xmlserverurl field, OR if $tasks is not empty)
        if ($delete_grids_missing_from_xmlserverurl) {
            mtrace('Exabis Competence Grid: delete_grids_missing_from_xmlserverurl  is running.');
            if ($any_import_failed) {
                mtrace("Synchronize did not run because an import failed");
            } else {
                // set all importstate fields of the subjects of ALL SOURCES to BLOCK_EXACOMP_SUBJECT_MISSING_FROM_IMPORT, if they are still set to BLOCK_EXACOMP_SUBJECT_IMPORTING after the imports are done
                // in the insert_edulevel function, the subjects are inserted and importstate is set to BLOCK_EXACOMP_SUBJECT_NOT_MISSING_FROM_IMPORT if the subject is in the xml
                // this leaves only the actually missing subjects set to BLOCK_EXACOMP_SUBJECT_MISSING_FROM_IMPORT
                g::$DB->set_field(BLOCK_EXACOMP_DB_SUBJECTS, 'importstate', BLOCK_EXACOMP_SUBJECT_MISSING_FROM_IMPORT, array('importstate' => BLOCK_EXACOMP_SUBJECT_IMPORT_TASK_RUNNING));
                // now: delete what needs to be deleted
                try {
                    if (block_exacomp_delete_grids_missing_from_komet_import()) {
                        mtrace("Synchronize done");
                    } else {
                        mtrace("Synchronize failed: unknown error");
                    }
                } catch (moodle_exception $e) {
                    mtrace("Synchronize failed: " . $e->getMessage());
                }
            }
        }
        return true;
    }
}
