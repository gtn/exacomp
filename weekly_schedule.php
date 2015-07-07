<?php

require_once dirname(__FILE__)."/inc.php";

require_login();

$monday = optional_param('monday', time(), PARAM_INT);
$monday = block_exacomp_add_days($monday, 1 - date('N', $monday));

$lastMonday = block_exacomp_add_days($monday, -7);
$nextMonday = block_exacomp_add_days($monday, +7);

// CHECK TEACHER
$isTeacher = 0; // todo: change .... has_capability('block/exacomp:teacher', $context);
$studentid = $isTeacher ? optional_param("studentid", 0, PARAM_INT) : $USER->id;

function block_exacomp_add_days($date, $days) {
    return mktime(0,0,0,date('m', $date), date('d', $date)+$days, date('Y', $date));
}

if (optional_param('action', '', PARAM_TEXT) == 'save') {

    function block_exacomp_clean_array_param($array, $param, $type) {
        if (!isset($array[$param]))
            return null;
        else
            return clean_param($array[$param], $type);
    }
    
    function block_exacomp_clean_array_array($array, $param) {
        if (!isset($array[$param]) || !is_array($array[$param]))
            return array();
        else
            return $array[$param];
    }
    
    function block_exacomp_weekly_schedule_clean_items($items) {
        $result = array();

        if (!is_array($items)) return $result;
        
        foreach ($items as $item) {
            if (!is_array($item)) continue;
            
            $result[] = (object)array(
                'id' => block_exacomp_clean_array_param($item, 'id', PARAM_INT),
                'student_evaluation' => block_exacomp_clean_array_param($item, 'student_evaluation', PARAM_INT),
                'teacher_evaluation' => block_exacomp_clean_array_param($item, 'teacher_evaluation', PARAM_INT)
            );
        }
        
        return $result;
    }
    
    $items = block_exacomp_weekly_schedule_clean_items(block_exacomp_clean_array_array($_REQUEST, 'items'));
    $trash = block_exacomp_weekly_schedule_clean_items(block_exacomp_clean_array_array($_REQUEST, 'trash'));
    
    $days = array();
    foreach (block_exacomp_clean_array_array($_REQUEST, 'days') as $date=>$dayItems) {
        $days[clean_param($date, PARAM_INT)] = block_exacomp_weekly_schedule_clean_items($dayItems);
    }

    // trash
    foreach ($trash as $item) {
        // items loeschen
		$DB->delete_records('block_exacompschedule', array('studentid'=>$studentid, 'id'=>$item->id));
        
        // todo: evaluation loeschen?
    }
    
    // day items auf einen tag verschieben
    foreach ($days as $day => $dayItems) {
        foreach ($dayItems as $item) {
            // day speichern
            $DB->execute('UPDATE {block_exacompschedule} SET
                timecreated = ?
            WHERE id=? AND studentid=?', array($day, $item->id, $studentid));
        
            // evaluation speichern
            if ($isTeacher) {
                $DB->execute('UPDATE {block_exacompexameval} SET
                    teach_evaluation = ?
                WHERE id=? AND studentid=?', array($item->teach_evaluation, $item->id, $studentid));
            } else {
                $DB->execute('UPDATE {block_exacompexameval} SET
                    student_evaluation = ?
                WHERE id=? AND studentid=?', array($item->student_evaluation, $item->id, $studentid));
            }
        }
    }
  
    // items
    foreach ($items as $item) {
        // datum loeschen
        $DB->execute('UPDATE {block_exacompschedule} SET
            timecreated = NULL
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



$PAGE->set_url('/blocks/exacomp/weekly_schedule.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('pluginname', 'block_exacomp'));

block_exacomp_init_js_css();
$PAGE->requires->jquery_plugin('ui');

echo $OUTPUT->header();

$sql = "select s.*, e.title, eval.student_evaluation, eval.teacher_evaluation
		FROM {block_exacompschedule} s 
		JOIN {block_exacompexamples} e ON e.id = s.exampleid 
		LEFT JOIN {block_exacompexameval} eval ON eval.exampleid = s.exampleid AND eval.studentid = s.studentid
		WHERE s.studentid = ? AND (
            -- noch nicht auf einen tag geleg
            (s.timecreated IS null OR s.timecreated=0)
            -- oder auf einen tag der vorwoche gelegt und noch nicht evaluiert
            OR (s.timecreated < $monday AND (eval.teacher_evaluation IS NULL OR eval.teacher_evaluation=0))
        )
        ORDER BY e.title";
$items = $DB->get_records_sql($sql,array($studentid));

function block_exacomp_weekly_schedule_print_items($items) {
	echo '<div class="items">';
    foreach ($items as $item) {
        echo '<div class="item" id="item-'.$item->id.'">';
        echo    '<div class="header">'.$item->title.'</div>';
		echo    '<div class="buttons">';
		echo        '<label>S <input type="checkbox" class="student_evaluation" '.($item->student_evaluation?'checked="checked"':'').' /></label>';
		echo       	'<label>L <input type="checkbox" class="teacher_evaluation" '.($item->teacher_evaluation?'checked="checked"':'').' /></label>';
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
			<a href="weekly_schedule.php?monday=<?=$lastMonday?>">&lt; vorige</a>
            &nbsp;&nbsp;&nbsp;&nbsp;
            Kalenderwoche <?php echo date('Y/W', block_exacomp_add_days($monday, 6) /* 1.1. kann der sonntag sein, dann ist die woche die 1te woche! */); ?>
            &nbsp;&nbsp;&nbsp;&nbsp;
			<a href="weekly_schedule.php?monday=<?=$nextMonday?>">nächste &gt;</a>
		</div>
		<div id="days">
            <?php
                for ($i = 0; $i < 5; $i++) {
                    $day = block_exacomp_add_days($monday, $i);

                    echo '<div class="day" id="day-'.$day.'">';
                    echo '<div class="header">'.date('l, d.m.', $day).'</div>';
                    
                    $sql = "select s.*, e.title, eval.student_evaluation, eval.teacher_evaluation
                            FROM {block_exacompschedule} s 
                            JOIN {block_exacompexamples} e ON e.id = s.exampleid 
                            LEFT JOIN {block_exacompexameval} eval ON eval.exampleid = s.exampleid AND eval.studentid = s.studentid
                            WHERE s.studentid = ? AND s.timecreated = ?";
                    $items = $DB->get_records_sql($sql,array($studentid, $day));
                    block_exacomp_weekly_schedule_print_items($items);
                    echo '</div>';
                }
            ?>
		</div>
	</div>
</div>
<?php

echo $OUTPUT->footer();