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

global $COURSE, $CFG, $OUTPUT, $USER;

$courseid = required_param('courseid', PARAM_INT);
$subjectid = optional_param('subjectid', 0, PARAM_INT);

require_login($courseid);

$context = get_context_instance(CONTEXT_COURSE, $courseid);
require_capability('block/exacomp:teacher', $context);

$url = '/blocks/exacomp/competence_grid.php?courseid=' . $courseid;
$PAGE->set_url($url);
$PAGE->requires->css('/blocks/exacomp/styles.css');

block_exacomp_print_header("teacher", "teachertabcompetencegrid");

$subjects = $DB->get_records_sql('
		SELECT s.id, s.title
		FROM {block_exacompsubjects} s
		JOIN {block_exacomptopics} t ON t.subjid = s.id
		JOIN {block_exacompcoutopi_mm} ct ON ct.topicid = t.id AND ct.courseid = ?

		GROUP BY s.id
		ORDER BY s.title
		', array($courseid));

if (isset($subjects[$subjectid])) {
	$selected_subject = $subjects[$subjectid];
} elseif ($subjects) {
	$selected_subject = reset($subjects);
}
?>
<div class="exabis_comp_select">
Fach auswählen:
<select class="start-searchbox-select" onchange="document.location.href='<?php echo $CFG->wwwroot.$url; ?>&subjectid='+this.value;">
<?php foreach ($subjects as $subject) {
	echo '<option value="'.$subject->id.'"'.($subject->id==$selected_subject->id?' selected="selected"':'').'>'.$subject->title.'</option>';
} ?>
</select>
<?
	echo html_writer::table(block_exacomp_get_competence_grid_for_subject($selected_subject->id));
