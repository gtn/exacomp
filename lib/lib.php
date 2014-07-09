<?php

function block_exacomp_build_navigation_tabs($context,$courseid) {
	
	if(has_capability('block/exacomp:admin', $context)) {
		$rows = array(
				new tabobject('tab_admin_import', new moodle_url('/blocks/exacomp/edit_config.php',array("courseid"=>$courseid)),get_string('tab_admin_import','block_exacomp')),
				new tabobject('tab_admin_configuration', new moodle_url('/blocks/exacomp/import.php',array("courseid"=>$courseid)),get_string('tab_admin_configuration','block_exacomp'))
		);
	}
	if (has_capability('block/exacomp:teacher', $context)) {
		$rows = array(
				new tabobject('tab_competence_overview', new moodle_url('/blocks/exacomp/assign_competencies.php',array("courseid"=>$courseid)),get_string('tab_competence_overview','block_exacomp')),
				new tabobject('tab_competence_details', new moodle_url('/blocks/exacomp/edit_students.php',array("courseid"=>$courseid)),get_string('tab_competence_details','block_exacomp')),
				new tabobject('tab_competence_grid', new moodle_url('/blocks/exacomp/competence_grid.php',array("courseid"=>$courseid)),get_string('tab_competence_grid','block_exacomp')),
				new tabobject('tab_examples', new moodle_url('/blocks/exacomp/view_examples.php',array("courseid"=>$courseid)),get_string('tab_examples','block_exacomp')),
				new tabobject('tab_learning_agenda', new moodle_url('/blocks/exacomp/learningagenda.php',array("courseid"=>$courseid)),get_string('tab_learning_agenda','block_exacomp')),
				new tabobject('tab_badges', new moodle_url('/blocks/exacomp/my_badges.php',array("courseid"=>$courseid)),get_string('tab_badges','block_exacomp')),
				new tabobject('tab_teacher_settings', new moodle_url('/blocks/exacomp/edit_course.php',array("courseid"=>$courseid)),get_string('tab_teacher_settings','block_exacomp'))
		);
	} elseif (has_capability('block/exacomp:student', $context)) {
		$rows = array(
				new tabobject('tab_competence_overview', new moodle_url('/blocks/exacomp/assign_competencies.php',array("courseid"=>$courseid)),get_string('tab_competence_overview','block_exacomp')),
				new tabobject('tab_competence_details', new moodle_url('/blocks/exacomp/edit_students.php',array("courseid"=>$courseid)),get_string('tab_competence_details','block_exacomp')),
				new tabobject('tab_student_all', new moodle_url('/blocks/exacomp/all_gained_competencies_course_based.php',array("courseid"=>$courseid)),get_string('tab_student_all','block_exacomp')),
				new tabobject('tab_competence_grid', new moodle_url('/blocks/exacomp/competence_grid.php',array("courseid"=>$courseid)),get_string('tab_competence_grid','block_exacomp')),
				new tabobject('tab_learning_agenda', new moodle_url('/blocks/exacomp/learningagenda.php',array("courseid"=>$courseid)),get_string('tab_learning_agenda','block_exacomp')),
				new tabobject('tab_badges', new moodle_url('/blocks/exacomp/my_badges.php',array("courseid"=>$courseid)),get_string('tab_badges','block_exacomp'))
		);
	}
	
	return $rows;
}

function block_exacomp_studentselector($students,$selected,$url){
	global $CFG;

	$studentsAssociativeArray = array();
	$studentsAssociativeArray[0]=get_string('no_student_selected', "block_exacomp");
	foreach($students as $student) {
		$studentsAssociativeArray[$student->id] = fullname($student);
	}
	return html_writer::select($studentsAssociativeArray, 'exacomp_competence_grid_select_student',$selected,true,
			array("onchange"=>"document.location.href='".$CFG->wwwroot.$url."&studentid='+this.value;"));
}