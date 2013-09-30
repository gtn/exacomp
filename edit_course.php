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

global $COURSE, $CFG, $OUTPUT;
$content = "";

$courseid = required_param('courseid', PARAM_INT);
$courseid = (isset($courseid)) ? $courseid : $COURSE->id;
$action = optional_param('action', "", PARAM_ALPHAEXT);

require_login($courseid);

$context = get_context_instance(CONTEXT_COURSE, $courseid);

require_capability('block/exacomp:teacher', $context);
//Falls Formular abgesendet, speichern


if ($action == 'save_coursesettings') {
	$settings = new stdClass;
	$settings->grading = optional_param('grading', 1, PARAM_INT);
	
	if($settings->grading == 0)
		$settings->grading = 1;
	
	$settings->uses_activities = optional_param('uses_activities', "", PARAM_INT);
	$settings->show_all_descriptors = optional_param('show_all_descriptors', "", PARAM_INT);
	
	block_exacomp_save_coursesettings($courseid, $settings);
		
} 

$PAGE->set_url('/blocks/exacomp/edit_course.php?courseid=' . $courseid);

block_exacomp_print_header("teacher", "teachertabconfig");

echo "<div class='block_excomp_center'>";

if ($action == 'save_coursesettings')
    echo get_string("save", "block_exacomp");

	echo $OUTPUT->box(text_to_html(get_string("explain_bewertungsschema", "block_exacomp")));
	
	$courseSettings = block_exacomp_coursesettings();
	
	echo $OUTPUT->box_start();
	?>
	<form action="edit_course.php?courseid=<?php echo $courseid; ?>&action=save_coursesettings" method="post">
		<?php echo get_string('bewertungsschema', 'block_exacomp'); ?>:
		<input type="text" size="2" name="grading" value="<?php echo block_exacomp_getbewertungsschema($courseid); ?>" /><br />
		
		<input type="checkbox" value="1" name="uses_activities" <?php if (!empty($courseSettings->uses_activities)) echo 'checked="checked"'; ?> />
		<?php echo get_string('uses_activities', 'block_exacomp'); ?><br />
		
		<input type="checkbox" value="1" name="show_all_descriptors" <?php if (!empty($courseSettings->show_all_descriptors)) echo 'checked="checked"'; ?> />
		<?php echo get_string('show_all_descriptors', 'block_exacomp'); ?><br />
		<input type="submit" value="<?php echo get_string('save', 'admin'); ?>" />
	</form>
	<?php
	echo $OUTPUT->box_end();

echo '</div>';
echo '</div>'; //exabis_competences_block
echo $OUTPUT->footer();