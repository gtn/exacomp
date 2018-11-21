<?php
// This file is part of Exabis Competence Grid
//
// (c) 2016 GTN - Global Training Network GmbH <office@gtn-solutions.com>
//
// Exabis Competence Grid is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This script is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You can find the GNU General Public License at <http://www.gnu.org/licenses/>.
//
// This copyright notice MUST APPEAR in all copies of the script!

namespace block_exacomp\task;

defined('MOODLE_INTERNAL') || die();

require_once __DIR__.'/../../inc.php';
class import extends \core\task\scheduled_task {
	public function get_name() {
		return block_exacomp_trans(['en:Import Data', 'de:Daten Importieren']);
	}

	public function execute() {
		$xmlserverurl = get_config('exacomp', 'xmlserverurl');

		mtrace('Exabis Competence Grid: import task is running.');

		//import xml with provided server url
		if (!$xmlserverurl) {
			mtrace('nothing to import');
		}

		try {
			\block_exacomp\data::prepare();

			if (\block_exacomp\data_importer::do_import_url($xmlserverurl, null, BLOCK_EXACOMP_IMPORT_SOURCE_DEFAULT, false, -1)) {
				mtrace("import done");
				block_exacomp_settstamp();
			} else {
				mtrace("import failed: unknown error");
			}
		} catch (\block_exacomp\moodle_exception $e) {
			mtrace("import failed: ".$e->getMessage());
		}

		block_exacomp_perform_auto_test();

		return true;
	}
}
