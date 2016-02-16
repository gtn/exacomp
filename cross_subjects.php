<?php

require_once __DIR__."/inc.php";

$courseid = required_param('courseid', PARAM_INT);
$showevaluation = optional_param("showevaluation", true, PARAM_BOOL);
$group = optional_param('group', 0, PARAM_INT);
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_simplehtml', $courseid);
}
$context = context_course::instance($courseid);

// CHECK TEACHER
require_login($course);
$isTeacher = block_exacomp_is_teacher($context);

$studentid = block_exacomp_get_studentid($isTeacher) ;
$editmode = optional_param('editmode', 0, PARAM_BOOL);

$crosssubjid = optional_param('crosssubjid', 0, PARAM_INT);
$action = optional_param('action', '', PARAM_TEXT);

/* PAGE IDENTIFIER - MUST BE CHANGED. Please use string identifier from lang file */
$page_identifier = 'tab_cross_subjects';

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/cross_subjects.php', [
	'courseid' => $courseid,
	'showevaluation' => $showevaluation,
	'studentid' => $studentid,
	'editmode' => $editmode,
	'crosssubjid' => $crosssubjid,
]);

$PAGE->set_heading(get_string('pluginname', 'block_exacomp'));
$PAGE->set_title(get_string($page_identifier, 'block_exacomp'));

/* @var $output block_exacomp_renderer */
$output = block_exacomp_get_renderer();

// build breadcrumbs navigation
block_exacomp_build_breadcrum_navigation($courseid);

$output->requires()->js('/blocks/exacomp/javascript/jquery.inputmask.bundle.js', true);
$output->requires()->js('/blocks/exacomp/javascript/competence_tree_common.js', true);
$output->requires()->css('/blocks/exacomp/css/competence_tree_common.css');

if ($action == 'share') {
	$cross_subject = \block_exacomp\cross_subject::get($crosssubjid, MUST_EXIST);

	$cross_subject->require_capability(block_exacomp\CAP_MODIFY);

	if (optional_param('save', '', PARAM_TEXT)) {
		$share_all = optional_param('share_all', false, PARAM_BOOL);
		$studentids = block_exacomp\param::optional_array('studentids', PARAM_INT);

		$cross_subject->update(['shared' => $share_all]);

		$DB->delete_records(\block_exacomp\DB_CROSSSTUD, array('crosssubjid'=>$crosssubjid));
		foreach($studentids as $studentid) {
			$DB->insert_record(\block_exacomp\DB_CROSSSTUD, ['crosssubjid'=>$crosssubjid, 'studentid'=>$studentid]);
		}

		echo $output->popup_close_and_reload();
		exit;
	}

	$PAGE->set_url('/blocks/exacomp/cross_subjects.php', array('courseid' => $courseid, 'action' => $action, 'crosssubjid' => $crosssubjid));
	$PAGE->set_heading(get_string('pluginname', 'block_exacomp'));
	$PAGE->set_pagelayout('embedded');

	$output = block_exacomp_get_renderer();
	echo $output->header_v2();

	$students = block_exacomp_get_students_by_course($courseid);
	if(!$students) {
		echo get_string('nostudents','block_exacomp');
		echo $output->footer();
		exit;
	}

	$assigned_students = $DB->get_records_menu(\block_exacomp\DB_CROSSSTUD, array('crosssubjid'=>$crosssubjid),'','studentid,crosssubjid');
	$shared = $cross_subject->shared;

	echo '<form method="post" id="share">';
	echo '<input type="hidden" name="save" value="save" />';
	echo html_writer::checkbox('share_all', 'share_all', $shared, '');
	echo get_string('share_crosssub_with_all', 'block_exacomp', $cross_subject->title);
	echo html_writer::empty_tag('br').html_writer::empty_tag('br');

	echo get_string('share_crosssub_with_students','block_exacomp',$cross_subject->title).html_writer::empty_tag('br');

	foreach($students as $student) {
		echo html_writer::checkbox('studentids[]',$student->id,isset($assigned_students[$student->id]),$student->firstname." ".$student->lastname, $shared?['disabled'=>true]:[]);
		echo html_writer::empty_tag('br');
	}

	echo html_writer::empty_tag('br');
	echo html_writer::tag("input", '', array("type"=>"submit","value"=>get_string('save_selection', 'block_exacomp')));
	echo '</form>';

	echo $output->footer();
	exit;
}

if ($isTeacher && optional_param('save', '', PARAM_TEXT)) {

}
// IF DELETE > 0 DELTE CUSTOM EXAMPLE
/*
if(($delete = optional_param("delete", 0, PARAM_INT)) > 0 && $isTeacher)
	block_exacomp_delete_custom_example($delete);
*/

$activities = block_exacomp_get_activities_by_course($courseid);
$course_settings = block_exacomp_get_settings_by_course($courseid);

if($course_settings->uses_activities && !$activities && !$course_settings->show_all_descriptors) {
	echo $output->header($context, $courseid, 'tab_cross_subjects');
	echo $output->print_no_activities_warning($isTeacher);
	echo $output->footer();
	exit;
}

$cross_subject = $crosssubjid ? \block_exacomp\cross_subject::get($crosssubjid, MUST_EXIST) : null;

if ($action == 'descriptor_selector') {
	$cross_subject->require_capability(block_exacomp\CAP_MODIFY);

	if (optional_param('save', '', PARAM_TEXT)) {
		$descriptors = block_exacomp\param::optional_array('descriptors', PARAM_INT);
		$old_descriptors = $DB->get_records_menu(\block_exacomp\DB_DESCCROSS, array('crosssubjid'=>$crosssubjid), null, 'descrid, descrid AS tmp');

		foreach ($descriptors as $descriptorid) {
			block_exacomp_set_cross_subject_descriptor($crosssubjid, $descriptorid);
			unset($old_descriptors[$descriptorid]);
		}

		foreach ($old_descriptors as $descriptorid) {
			block_exacomp_unset_cross_subject_descriptor($crosssubjid, $descriptorid);
		}

		echo $output->popup_close_and_reload();
		exit;
	}

	$PAGE->set_pagelayout('embedded');
	echo $output->header_v2();

	$active_descriptors = $DB->get_records_menu(block_exacomp\DB_DESCCROSS, ['crosssubjid'=>$crosssubjid], null, 'id, descrid');

	$print_tree = function($items, $level = 0) use (&$print_tree, $active_descriptors) {
		if (!$items) return '';

		$output = '';
		if ($level == 0) {
			$output .= '<div class="exabis_competencies_lis"><table class="exabis_comp_comp rg2 rg2-reopen-checked rg2-check_uncheck_parents_children">';
		}

		foreach ($items as $item) {
			$output .= '<tr class="'.($item instanceof block_exacomp\descriptor ? '' : 'highlight').' rg2-level-'.$level.'">';

			if ($item instanceof block_exacomp\subject) {
				$output .= '<td class="rg2-arrow rg2-indent" colspan="2"><div>';
			} else {
				if (block_exacomp_is_numbering_enabled()) {
					$output .= '<td class="row-numbering">'.$item->get_numbering().'</td>';
				}

				$output .= '<td class="rg2-arrow rg2-indent"><div>';

				if ($item instanceof block_exacomp\descriptor) {
					if (in_array($item->id, $active_descriptors)) {
						$checked = 'checked="checked"';
					} else {
						$checked = '';
					}
					$output .= '<input type="checkbox" name="descriptors[]" ' . $checked . ' value="' . $item->id . '">';
				} elseif ($item instanceof block_exacomp\topic) {
					// needed to allow selection of whole topic
					// $output .= '<input type="checkbox" name="topic_tmp">';
				}
			}

			$output .= $item->title.'</div></td>';

			$output .= '</tr>';

			$output .= $print_tree($item->get_subs(), $level+1);
		}

		if ($level == 0) {
			$output .= '</table></div>';
		}

		return $output;
	};

	$subjects = block_exacomp\db_layer_course::create($courseid)->get_subjects();

	echo '<form method="post">';
	echo $print_tree($subjects);
	echo '<input type="submit" name="save" value="'.block_exacomp\get_string('add_descriptors_to_crosssub').'" />';
	echo '</form>';

	echo $output->footer();
	exit;
}

if ($isTeacher && optional_param('save', '', PARAM_TEXT)) {
	if ($cross_subject) {
		$cross_subject->require_capability(block_exacomp\CAP_MODIFY);
	} else {
		// add
		block_exacomp_require_teacher();
	}

	$data = [
		'subjectid' => required_param('subjectid', PARAM_INT),
		'title' => required_param('title', PARAM_TEXT),
		'description' => required_param('description', PARAM_TEXT)
	];

	if ($cross_subject) {
		$cross_subject->update($data);
		redirect($PAGE->url);
	} else {
		$cross_subject = block_exacomp\cross_subject::create($data);
		$cross_subject->courseid = $courseid;
		$cross_subject->insert();

		$url = $PAGE->url;
		$url->param('crosssubjid', $cross_subject->id);
		$url->param('editmode', 1);
		redirect($url);
	}
	exit;
}
if ($isTeacher && $action == 'use_draft') {
	$cross_subject->require_capability(block_exacomp\CAP_VIEW);

	$new_id = block_exacomp_save_drafts_to_course([$cross_subject->id], $COURSE->id);
	redirect(new moodle_url('/blocks/exacomp/cross_subjects.php', array('courseid'=>$courseid, 'crosssubjid'=>$new_id, 'editmode'=>1)));
}
if ($isTeacher && $action == 'save_as_draft') {
	$cross_subject->require_capability(block_exacomp\CAP_MODIFY);

	$new_id = block_exacomp_save_drafts_to_course([$cross_subject->id], 0);
	redirect(new moodle_url('/blocks/exacomp/cross_subjects_overview.php', array('courseid'=>$courseid)));
}

echo $output->header_v2($page_identifier);

//Delete timestamp (end|start) from example
/*
if($example_del = optional_param('exampleid', 0, PARAM_INT)){
	block_exacomp_delete_timefield($example_del, optional_param('deletestart', 0, PARAM_INT), optional_param('deleteend', 0, PARAM_INT));
}
*/

// TODO: wer schreibt alles uppercase?
// IF TEACHER SHOW ALL COURSE STUDENTS, IF NOT ONLY CURRENT USER
// TODO: logik hier kontrollieren
if ($isTeacher) {
	$students = ($cross_subject)?block_exacomp_get_students_for_crosssubject($courseid, $cross_subject):array();
	if (!$cross_subject) {
		$selectedStudentid = 0;
		$studentid = 0;
		$editmode = true;
	} elseif(!$students) {
		if ($cross_subject && !$cross_subject->is_draft())
			echo html_writer::div(get_string('share_crosssub_for_further_use','block_exacomp'),"alert alert-warning");
		// $editmode = true;
		$selectedStudentid = 0;
		$studentid = 0;
	} elseif($editmode) {
		$selectedStudentid = $studentid;
		$studentid = 0;
	} else {
		$selectedStudentid = $studentid;
	}
} else {
	$students = array($USER);
	$editmode = false;
	$selectedStudentid = $USER->id;
	$studentid = $USER->id;
}

if ($editmode) {
	block_exacomp_require_teacher();
	if ($cross_subject) {
		$cross_subject->require_capability(block_exacomp\CAP_MODIFY);
	}
} else {
	$cross_subject->require_capability(block_exacomp\CAP_VIEW);
}

$output->editmode = $editmode;

foreach($students as $student)
	$student = block_exacomp_get_user_information_by_course($student, $courseid);

if ($editmode) {
	echo html_writer::start_tag('form', array('id'=>'cross-subject-data', "action" => $PAGE->url, 'method'=>'post'));
	echo '<input type="hidden" name="save" value="save" />';
}

//schooltypes
/*$schooltypes = block_exacomp_get_schooltypes_by_course($courseid);

$schooltype_title = "";
foreach($schooltypes as $schooltype){
	$schooltype_title .= $schooltype->title . ", ";
}
$schooltype = substr($schooltype_title, 0, strlen($schooltype_title)-1);
*/
echo $output->print_overview_metadata_cross_subjects($cross_subject, $editmode);

$scheme = block_exacomp_get_grading_scheme($courseid);

if(!$isTeacher){
	$user_evaluation = block_exacomp_get_user_information_by_course($USER, $courseid);

	$cm_mm = block_exacomp_get_course_module_association($courseid);
	$course_mods = get_fast_modinfo($courseid)->get_cms();

	//TODO: test with activities
	/*$activities_student = array();
	if(isset($cm_mm->topics[$selectedTopic->id]))
		foreach($cm_mm->topics[$selectedTopic->id] as $cmid)
			$activities_student[] = $course_mods[$cmid];*/
}

echo $output->print_cross_subject_buttons($cross_subject, $students, $selectedStudentid);

$statistic = false;
if($isTeacher){
	if($studentid == BLOCK_EXACOMP_SHOW_ALL_STUDENTS){
		$showevaluation = false;
		echo $output->print_column_selector(count($students));
	}elseif ($studentid == 0)
		$students = array();
	elseif($studentid == BLOCK_EXACOMP_SHOW_STATISTIC)
		$statistic = true;
	else{
		$students = array($students[$studentid]);
		$showevaluation = true;
	}
}else{
	$showevaluation = true;
}

if ($editmode) {
	echo html_writer::end_tag('form');
}

if($cross_subject)
	echo $output->print_overview_legend($isTeacher);

$subjects = block_exacomp_get_competence_tree_for_cross_subject($courseid,(isset($cross_subject))?$cross_subject->id:null,false, !($course_settings->show_all_examples == 0 && !$isTeacher),$course_settings->filteredtaxonomies);

if ($cross_subject) {
	echo html_writer::start_tag('form', array('id'=>'assign-competencies', "action" => $PAGE->url, 'method'=>'post'));
	echo html_writer::start_tag("div", array("class"=>"exabis_competencies_lis"));
	echo $output->print_competence_overview($subjects, $courseid, $students, $showevaluation, $isTeacher ? \block_exacomp\ROLE_TEACHER : \block_exacomp\ROLE_STUDENT, $scheme, false, $cross_subject->id, $statistic);
	echo html_writer::end_tag("div");
	echo html_writer::end_tag('form');
}

/* END CONTENT REGION */
echo $output->footer();

