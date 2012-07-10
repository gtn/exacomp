<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 exabis internet solutions <info@exabis.at>
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
***************************************************************/


require_once dirname(__FILE__).'/inc.php';
require_once dirname(__FILE__).'/lib/div.php';

global $COURSE,$CFG;

require_once($CFG->dirroot."/mod/lesson/lib.php");
require_once($CFG->dirroot."/mod/lesson/lib.php");
require_once($CFG->dirroot."\lib\datalib.php");

//print_r($USER);



$navlinks = array();

	$navlinks[] = array('name' => get_string('exabis_competences','block_exacomp'), 'link' => FALSE, 'type' => 'misc');

	$navigation = build_navigation($navlinks);
	print_header_simple(get_string('exabis_competences','block_exacomp'), '', $navigation,'', '', true, '','');



echo "<div class='block_excomp_center'>";

$courses=get_my_courses($USER->id);
$lessons = get_all_instances_in_courses('lesson', $courses);
$query="SELECT * FROM {block_exacompdescriptors}";

$descriptors = get_records_sql($query);

if (!$descriptors) {
	$descriptors = array();
}

$content='<form action="view.php" name="lessondescriptor" method="post">';
$wert= optional_param('ec_lessons', 0, PARAM_INT);
$pd=create_pulldown_array($lessons,"ec_lessons","name",$wert,false,"");
$content.=$pd;

$wert="";
foreach ($_POST["ec_descriptors"] AS $key=>$value){
	$wert.=intval($value).",";
}
$pd=create_pulldown_array($descriptors,"ec_descriptors","title",$wert,false,"multiple");
$content.=$pd;
$content.='<input type="submit">';
$content.='</form>';
echo $content;
/*foreach ($lessons as $lesson){
	echo $lesson->id." ".$lesson->name."<br>";
  print_r ($lesson);
}
*/

echo "</div>";
echo $OUTPUT->footer();
