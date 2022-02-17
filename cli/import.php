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
 * CLI import
 *
 * Notes:
 *   - this script is used to import xml data to exacomp
 *   - it is required to use the web server account when executing PHP CLI scripts
 *   - to run from windows: 1) change to directory of php.exe (i.e. xampp/php) 2) run in windows cmd: 'php.exe C:\xampp\htdocs\moodle\blocks\exacomp\cli\import.php C:\xampp\htdocs\moodle\data.xml'
 *
 */

use block_exacomp\data;
use block_exacomp\event\import_completed;

define('CLI_SCRIPT', true);

require(__DIR__ . '/../inc.php');
require_once("$CFG->libdir/clilib.php");

// Now get cli options.
list($options, $unrecognized) = cli_get_params(array('verbose' => true, 'help' => false), array('v' => 'verbose', 'h' => 'help'));

$file = array_pop($unrecognized);
if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(block_exacomp_get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help'] || !$file) {
    // Options:
    // -v, --verbose         Print verbose progress information
    // -h, --help            Print out this help

    $help =
        "Import cometence grid from a file

Example:
\$ sudo -u www-data /usr/bin/php exacomp/cli/import.php competence_grid.zip

";

    echo $help;
    die;
}

if (empty($options['verbose'])) {
    $trace = new null_progress_trace();
} else {
    $trace = new text_progress_trace();
}

if (!is_file($file)) {
    $trace->output("error: file '$file' not found");
    exit(1);
}

$trace->output("importing file '$file'...");

data::prepare();

try {
    $importSuccess = block_exacomp\data_importer::do_import_file($file, null, BLOCK_EXACOMP_IMPORT_SOURCE_DEFAULT);

    if ($importSuccess) {
        import_completed::log(['objectid' => 0]);

        $trace->output('file imported');
    } else {
        $trace->output('import error');
        exit(1);
    }
} catch (block_exacomp\import_exception $importException) {
    $trace->output('import error');

    throw $importException;
}
