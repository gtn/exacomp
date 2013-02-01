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


require_once dirname(__FILE__) . '/inc.php';
require_once dirname(__FILE__) . '/lib/div.php';
require_once($CFG->dirroot . "/lib/datalib.php");
require_once dirname(__FILE__) . '/lib/xmllib.php';

global $COURSE, $CFG, $OUTPUT;
$content = "";

$courseid = optional_param('courseid', 0, PARAM_INT);
$action = optional_param('action', "", PARAM_ALPHA);

require_login($courseid);

$context = get_context_instance(CONTEXT_SYSTEM);

require_capability('block/exacomp:admin', $context);

$check = block_exacomp_xml_check_import();
if(!$check)
    block_exacomp_xml_do_import();

//Falls Formular abgesendet, speichern
if (isset($action) && $action == 'save') {
    $values = $_POST['data'];
    block_exacomp_set_mdltype($values);
    $url = $CFG->wwwroot;
    //header('Location: ' . $url);
    $headertext=get_string("save", "block_exacomp");
}else{
	$headertext=get_string("explainconfig", "block_exacomp");
}

$PAGE->set_url('/blocks/exacomp/edit_config.php?courseid=1');
block_exacomp_print_header("admin", "admintabschooltype");


echo "<div class='block_excomp_center'>";

echo $OUTPUT->box(text_to_html($headertext));

$content.='<form action="edit_config.php?courseid=' . $courseid . '&action=save" method="post">';
$content .= '<table>';

$eigenest=false;
$levels = block_exacomp_get_edulevels();
foreach ($levels as $level) {
    $types = block_exacomp_get_schooltypes($level->id);
    if($level->source>1 && $eigenest==false){
    	$content .= '<tr class="heading r0"> <td class="category catlevel1" colspan="2"><h2>' . get_string('specificcontent', 'block_exacomp') . '</h2></td></tr>';
    	$eigenest=true;
    }
    $content .= '<tr> <td colspan="2"><b>' . $level->title . '</b></td></tr>';
    foreach ($types as $type) {
        if (block_exacomp_get_moodletypes($type->id))
            $content .= '<tr><td>' . $type->title . '</td><td><input type="checkbox" name="data[' . $type->id . ']" value="' . $type->id . '" checked="checked" /></td></tr>';
        else
            $content .= '<tr><td>' . $type->title . '</td><td><input type="checkbox" name="data[' . $type->id . ']" value="' . $type->id . '" /></td></tr>';
    }
}
$content .= '</table>';
$content.='<div><input type="submit" value="' . get_string('auswahl_speichern', 'block_exacomp') . '" /></div>';

$content.='</form>';

echo $OUTPUT->box($content);

echo '</div>';

echo '</div>'; //exabis_competences_block

echo $OUTPUT->footer();