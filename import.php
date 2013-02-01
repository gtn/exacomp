<?php

/* * *************************************************************
 *  Copyright notice
 *
 *  (c) 2011 exabis internet solutions <info@exabis.at>
 *  All rights reserved
 *
 *  You can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This module is based on the Collaborative Moodle Modules from
 *  NCSA Education Division (http://www.ncsa.uiuc.edu)
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 * ************************************************************* */

ini_set('max_execution_time', 3000);
require_once dirname(__FILE__) . '/inc.php';
require_once dirname(__FILE__) . '/lib/div.php';
require_once dirname(__FILE__) . '/lib/xmllib.php';

global $COURSE, $CFG, $OUTPUT;
$content = "";

$courseid = optional_param('courseid', 0, PARAM_INT);
require_login($courseid);

$context = get_context_instance(CONTEXT_SYSTEM);
$action = optional_param('action', "", PARAM_ALPHA);

require_capability('block/exacomp:admin', $context);

$url = '/blocks/exacomp/import.php';
$PAGE->set_url($url);
$url = $CFG->wwwroot.$url;

if($action == "xml") {
	//global $exaport;
	//$exaport=has_exaport();
    $import = block_exacomp_xml_do_import();
}

block_exacomp_print_header("admin", "admintabimport");

echo "<div class='block_excomp_center'>";


$check = block_exacomp_xml_check_import();
if($check)
     echo $OUTPUT->box(text_to_html(get_string("importdone", "block_exacomp")));
 else
     echo $OUTPUT->box(text_to_html(get_string("importpending", "block_exacomp")));

echo $OUTPUT->box(text_to_html('<a href="'.$url.'?action=xml&courseid='.$courseid.'">'.get_string("doimport", "block_exacomp").'</a>'));
if($check)
	echo $OUTPUT->box(text_to_html('<a href="'.$CFG->wwwroot.'/blocks/exacomp/import_own.php?courseid='.$courseid.'">'.get_string("doimport_own", "block_exacomp").'</a>'));

if(isset($import)) {
    if($import)
        echo $OUTPUT->box(text_to_html(get_string("importsuccess", "block_exacomp")));
    else
        echo $OUTPUT->box(text_to_html(get_string("importfail", "block_exacomp")));

}

echo $content;
echo "</div>";
echo $OUTPUT->footer();
