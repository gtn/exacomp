<?php

require_once dirname(__FILE__)."/inc.php";

$courseid = required_param ( 'courseid', PARAM_INT );
if (! $course = $DB->get_record ( 'course', array (
		'id' => $courseid 
) )) {
	print_error ( 'invalidcourse', 'block_simplehtml', $courseid );
}

require_login ( $course );
$context = context_course::instance ( $courseid );
$isTeacher = has_capability( 'block/exacomp:teacher', $context);
$studentid = $isTeacher ? optional_param("studentid", 0, PARAM_INT) : $USER->id;

$week = optional_param('week', time(), PARAM_INT);
$week = block_exacomp_add_days($week, 1 - date('N', $week));

$lastWeek = block_exacomp_add_days($week, -7);
$nextWeek = block_exacomp_add_days($week, +7);

$my_url = new moodle_url('/blocks/exacomp/weekly_schedule.php',
        array('courseid'=>$courseid, 'week'=>$week) + ($isTeacher ? array('studentid'=>$studentid) : array())
    );

if (optional_param('action', '', PARAM_TEXT) == 'save') {

    require_sesskey();
    
    if (!$studentid) die('no studentid');

    $itemsDefinition = array(
        PARAM_INT => array(
            'id' => PARAM_INT,
            'student_evaluation' => PARAM_BOOL,
            'teacher_evaluation' => PARAM_BOOL
        )
    );
    $items = block_exacomp_optional_param_array('items', $itemsDefinition);
    $trash = block_exacomp_optional_param_array('trash', $itemsDefinition);
    
    $days = block_exacomp_optional_param_array('days', array(
        PARAM_INT => $itemsDefinition
    ));

    // trash
    foreach ($trash as $item) {
        // items loeschen
		$DB->delete_records('block_exacompschedule', array('studentid'=>$studentid, 'id'=>$item->id));
        
        // todo: evaluation loeschen?
    }
    
    // day items auf einen tag verschieben
    foreach ($days as $day => $dayItems) {
        $i = 0;
        foreach ($dayItems as $item) {
            $schedule = $DB->get_record('block_exacompschedule', array('id'=>$item->id, 'studentid'=>$studentid));
            if (!$schedule) {
                // ignore error
                continue;
            }
            
            // day speichern
            $DB->update_record('block_exacompschedule', array('id'=>$schedule->id, 'sorting' => ++$i, 'day'=>$day));
        
            // evaluation speichern
            if ($isTeacher) {
                $updates = array('teacher_evaluation'=>$item->teacher_evaluation);
            } else {
                $updates = array('student_evaluation'=>$item->student_evaluation);
            }
            $where = array(
                'exampleid' => $schedule->exampleid,
                'courseid' => $courseid,
                'studentid' => $studentid 
            );
            $exameval = $DB->get_record('block_exacompexameval', $where);
            
            if ($exameval) {
                $DB->update_record('block_exacompexameval', array('id'=>$exameval->id) + $updates);
            } else {
                $DB->insert_record('block_exacompexameval', $where + $updates);
            }
        }
    }
  
    // items
    foreach ($items as $item) {
        // datum loeschen
        $DB->execute('UPDATE {block_exacompschedule} SET
            day = NULL
        WHERE id=? AND studentid=?', array($item->id, $studentid));
        
        // todo: evaluation loeschen?
        /*
        if ($isTeacher) {
            $DB->execute('UPDATE {block_exacompexameval} SET
                teach_evaluation = 0
            WHERE id=? AND studentid=?', array($item->teach_evaluation, $item->id, $studentid));
        } else {
            $DB->execute('UPDATE {block_exacompexameval} SET
                student_evaluation = 0
            WHERE id=? AND studentid=?', array($item->student_evaluation, $item->id, $studentid));
        }
        */
    }
    
    die('ok');
}



$PAGE->set_url($my_url);
$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('pluginname', 'block_exacomp'));
$PAGE->set_title(get_string("tab_learning_agenda", 'block_exacomp'));

block_exacomp_init_js_css();
$PAGE->requires->jquery_plugin('ui');


echo $OUTPUT->header();
echo $OUTPUT->tabtree(block_exacomp_build_navigation_tabs($context,$courseid), "tab_learning_agenda");
echo '<div id="exacomp">';

if($isTeacher){
	$students = block_exacomp_get_students_by_course($courseid);

	if (!$students) {
        // TODO no students
        echo 'no students found';
        echo $OUTPUT->footer();
        exit;
    }

    echo html_writer::empty_tag("br");
	echo get_string("choosestudent", "block_exacomp");
    echo block_exacomp_studentselector($students, $studentid, $url);
    
    if (empty($students[$studentid])) {
        // empty id or wrongid, first select a student
        echo $OUTPUT->footer();
        exit;
    }
}

$sql = "select s.*, e.title, eval.student_evaluation, eval.teacher_evaluation
		FROM {block_exacompschedule} s 
		JOIN {block_exacompexamples} e ON e.id = s.exampleid 
		LEFT JOIN {block_exacompexameval} eval ON eval.exampleid = s.exampleid AND eval.studentid = s.studentid
		WHERE s.studentid = ? AND (
            -- noch nicht auf einen tag geleg
            (s.day IS null OR s.day=0)
            -- oder auf einen tag der vorwoche gelegt und noch nicht evaluiert
            OR (s.day < $week AND (eval.teacher_evaluation IS NULL OR eval.teacher_evaluation=0))
        )
        ORDER BY e.title";
$items = $DB->get_records_sql($sql,array($studentid));

function block_exacomp_weekly_schedule_print_items($items) {
    global $isTeacher;
    
	echo '<div class="items">';
    foreach ($items as $item) {
        echo '<div class="item" id="item-'.$item->id.'">';
        echo    '<div class="header">'.$item->title.'</div>';
		echo    '<div class="buttons">';
		echo        '<label>S: <input type="checkbox" class="student_evaluation" value="1" '.
                    ($isTeacher ? 'disabled="disabled"':'').
                    ($item->student_evaluation?'checked="checked"':'').' /></label>';
		echo       	'<label>L: <input type="checkbox" class="teacher_evaluation" value="1" '.
                    (!$isTeacher ? 'disabled="disabled"':'').
                    ($item->teacher_evaluation?'checked="checked"':'').' /></label>';
		echo    '</div>';
        echo '</div>';
    }
	echo '</div>';
}

?>

	<div id="main-content">
		<div class="column">
			<div id="save-button">
	            <input type="button" value="Speichern" style="width: 90%;" />
	        </div>
			<div id="items">
				<div class="header">items</div>
				<div class="empty">Keine Einträge</div>
	            <?php block_exacomp_weekly_schedule_print_items($items); ?>
			</div>
			<div id="trash">
				<div class="header">papierkorb</div>
				<div class="empty">Leer</div>
				<div class="items">
				</div>
			</div>
		</div>
		<div class="column">
			<div id="navi">
				<a href="<?php echo $my_url->out(true, array('week'=>$lastWeek)); ?>">&lt; vorige</a>
	            &nbsp;&nbsp;&nbsp;&nbsp;
	            Kalenderwoche <?php echo date('Y/W', block_exacomp_add_days($week, 6) /* 1.1. kann der sonntag sein, dann ist die woche die 1te woche! */); ?>
	            &nbsp;&nbsp;&nbsp;&nbsp;
				<a href="<?php echo $my_url->out(true, array('week'=>$nextWeek)); ?>">nächste &gt;</a>
			</div>
			<div id="days">
	            <?php
	                for ($i = 0; $i < 5; $i++) {
	                    $day = block_exacomp_add_days($week, $i);
	
	                    echo '<div class="day" id="day-'.$day.'">';
	                    echo '<div class="header">'.date('l, d.m.', $day).'</div>';
	                    
	                    $sql = "select s.*, e.title, eval.student_evaluation, eval.teacher_evaluation
	                            FROM {block_exacompschedule} s 
	                            JOIN {block_exacompexamples} e ON e.id = s.exampleid 
	                            LEFT JOIN {block_exacompexameval} eval ON eval.exampleid = s.exampleid AND eval.studentid = s.studentid
	                            WHERE s.studentid = ? AND s.day = ?
	                            ORDER BY s.sorting";
	                    $items = $DB->get_records_sql($sql,array($studentid, $day));
	                    block_exacomp_weekly_schedule_print_items($items);
	                    echo '</div>';
	                }
	            ?>
			</div>
		</div>
	</div>
</div>
<?php

echo $OUTPUT->footer();