<?php

require_once __DIR__."/inc.php";

$courseid = required_param('courseid', PARAM_INT);

require_login($courseid);

/* PAGE IDENTIFIER - MUST BE CHANGED. Please use string identifier from lang file */
$page_identifier = 'tab_cross_subjects_overview';

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/cross_subjects_overview.php', array('courseid' => $courseid));
$PAGE->set_heading(get_string('pluginname', 'block_exacomp'));
$PAGE->set_title(get_string($page_identifier, 'block_exacomp'));

// build breadcrumbs navigation
block_exacomp_build_breadcrum_navigation($courseid);

$output = block_exacomp_get_renderer();

//SAVE DATA
/*
if (($action = optional_param("action", "", PARAM_TEXT) ) == "save") {
 	if(isset($_POST['delete_crosssubs']) && isset($_POST['draft'])){
		$drafts_to_delete = $_POST['draft'];
		block_exacomp_delete_crosssubject_drafts($drafts_to_delete);
	}
	else if(isset($_POST['draft'])){
		$drafts_to_save = $_POST['draft'];
		//if more than one draft added redirect to first selected
		$current_id = block_exacomp_save_drafts_to_course($drafts_to_save, $courseid);
		redirect(new moodle_url('/blocks/exacomp/cross_subjects.php', array('courseid'=>$courseid, 'crosssubjid'=>$current_id)));
	}
	else if(isset($_POST['new_crosssub_overview'])){
		redirect(new moodle_url('/blocks/exacomp/cross_subjects.php', array('courseid'=>$courseid, 'crosssubjid'=>0, 'new'=>1)));
	}
}
*/

// build tab navigation & print header
echo $output->header_v2('tab_cross_subjects');

if (block_exacomp_is_teacher() || block_exacomp_is_admin()) {
	$course_crosssubs = block_exacomp_get_cross_subjects_by_course($courseid);

	$item_title_cell = new html_table_cell;
	$item_title_cell->attributes['width'] = '90%';

	echo '<table width="100%" cellpadding="10" cellspacing="0"><tr>';
	echo '<td width="33%" style="vertical-align: top;">';

	$table = new html_table();
	$tmp = new html_table_cell($output->pix_icon('i/group', '').' '.block_exacomp\trans('de:freigegebene Kursthemen'));
	$tmp->colspan = 2;
	$table->head = [$tmp];

	foreach($course_crosssubs as $crosssub){
		if (!$crosssub->is_shared()) continue;

		$title = clone $item_title_cell;
		$title->text = html_writer::link(new moodle_url('/blocks/exacomp/cross_subjects.php', array('courseid'=>$courseid, 'crosssubjid'=>$crosssub->id)), $crosssub->title);
		$table->data[] = [
			$title,
			html_writer::link(new moodle_url('/blocks/exacomp/cross_subjects.php', array('courseid'=>$courseid, 'crosssubjid'=>$crosssub->id, 'editmode'=>1)),$output->pix_icon("i/edit", get_string("edit")), array('class'=>'crosssub-icons')).
			html_writer::link('#', $output->pix_icon("i/enrolusers", block_exacomp\trans("de:Freigabe bearbeiten")), ['exa-type'=>'iframe-popup', 'exa-url'=>'cross_subjects.php?courseid='.$courseid.'&crosssubjid='.$crosssub->id.'&action=share'])
		];
		/*
		if($isTeacher){
			$content .= html_writer::link(new moodle_url('/blocks/exacomp/cross_subjects.php', array('courseid'=>$courseid, 'crosssubjid'=>$crosssub->id, 'editmode'=>1)),$this->pix_icon("i/edit", get_string("edit")), array('class'=>'crosssub-icons'));
			$content .= html_writer::link('', $this->pix_icon("t/delete", get_string("delete")), array("onclick" => "if( confirm('".get_string('confirm_delete', 'block_exacomp')."')) block_exacomp.delete_crosssubj(".$crosssub->id."); return false;"), array('class'=>'crosssub-icons'));
		}
		*/
	}

	if (!$table->data) {
		$table->data[] = [get_string('no_crosssubjs', 'block_exacomp')];
	}

	echo html_writer::table($table);

	echo '</td>';
	echo '<td width="33%" style="vertical-align: top;">';

	$table = new html_table();
	$tmp = new html_table_cell($output->pix_icon('i/manual_item', '').' '.block_exacomp\trans('de:vorhandene Kursthemen'));
	$tmp->colspan = 2;
	$table->head = [$tmp];

	foreach($course_crosssubs as $crosssub){
		if ($crosssub->is_shared()) continue;

		$title = clone $item_title_cell;
		$title->text = html_writer::link(new moodle_url('/blocks/exacomp/cross_subjects.php', array('courseid'=>$courseid, 'crosssubjid'=>$crosssub->id)), $crosssub->title);
		$table->data[] = [
			$title,
			html_writer::link(new moodle_url('/blocks/exacomp/cross_subjects.php', array('courseid'=>$courseid, 'crosssubjid'=>$crosssub->id, 'editmode'=>1)),$output->pix_icon("i/edit", get_string("edit"))).
			html_writer::link('#', $output->pix_icon("t/delete", get_string("delete")), array("onclick" => "if( confirm('".get_string('confirm_delete', 'block_exacomp')."')) block_exacomp.delete_crosssubj(".$crosssub->id."); return false;")).
			html_writer::link('#', $output->pix_icon("i/enrolusers", block_exacomp\trans("de:Freigeben")), ['exa-type'=>'iframe-popup', 'exa-url'=>'cross_subjects.php?courseid='.$courseid.'&crosssubjid='.$crosssub->id.'&action=share']).
			html_writer::link(new moodle_url('/blocks/exacomp/cross_subjects.php', array('courseid'=>$courseid, 'crosssubjid'=>$crosssub->id, 'action'=>'save_as_draft')), $output->pix_icon("i/repository", block_exacomp\trans("de:Kopie als Vorlage speichern")))
		];
	}

	if (!$table->data) {
		$table->data[] = [get_string('no_crosssubjs', 'block_exacomp')];
	}

	echo html_writer::table($table);

	echo html_writer::empty_tag('input', array('type'=>'button', 'value' => get_string('create_new_crosssub','block_exacomp'),
			 "onclick" => "document.location.href='".block_exacomp\url::create('/blocks/exacomp/cross_subjects.php',array('courseid' => $COURSE->id, 'crosssubjid'=>0))."'"));

	echo '</td>';
	echo '<td width="33%" style="vertical-align: top;">';

	$table = new html_table();
	$tmp = new html_table_cell($output->pix_icon('i/repository', '').' '.block_exacomp\trans('de:Themenvorlagen'));
	$tmp->colspan = 2;
	$table->head = [$tmp];

	foreach (block_exacomp_get_cross_subjects_drafts() as $crosssub) {
		$title = clone $item_title_cell;
		$title->text = html_writer::link(new moodle_url('/blocks/exacomp/cross_subjects.php', array('courseid'=>$courseid, 'crosssubjid'=>$crosssub->id)), $crosssub->title);
		$table->data[] = [
			$title,
			($crosssub->has_capability(block_exacomp\CAP_MODIFY) ? html_writer::link(new moodle_url('/blocks/exacomp/cross_subjects.php', array('courseid'=>$courseid, 'crosssubjid'=>$crosssub->id, 'editmode'=>1)),$output->pix_icon("i/edit", get_string("edit"))) : '').
			($crosssub->has_capability(block_exacomp\CAP_DELETE) ? html_writer::link('#', $output->pix_icon("t/delete", get_string("delete")), array("onclick" => "if( confirm('".get_string('confirm_delete', 'block_exacomp')."')) block_exacomp.delete_crosssubj(".$crosssub->id."); return false;")) : '').
			html_writer::link(new moodle_url('/blocks/exacomp/cross_subjects.php', array('courseid'=>$courseid, 'crosssubjid'=>$crosssub->id, 'action'=>'use_draft')), $output->pix_icon("i/manual_item", block_exacomp\trans("de:Vorlage verwenden")))
		];
	}

	if (!$table->data) {
		$table->data[] = [get_string('no_crosssubjs', 'block_exacomp')];
	}

	echo html_writer::table($table);

	echo '</td>';
	echo '</tr></table>';
} else {
	$course_crosssubs = block_exacomp_get_cross_subjects_by_course($courseid, $USER->id);
	echo $output->print_cross_subjects_list($course_crosssubs, $courseid, false);
}

/* END CONTENT REGION */
echo $output->footer();
