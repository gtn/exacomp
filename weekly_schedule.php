<?php

require_once dirname(__FILE__)."/inc.php";

require_login();

$monday = optional_param('monday', time(), PARAM_INT);
$monday = add_days($monday, 1 - date('N', $monday));

$lastMonday = add_days($monday, -7);
$nextMonday = add_days($monday, +7);

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
                's' => block_exacomp_clean_array_param($item, 's', PARAM_INT),
                'l' => block_exacomp_clean_array_param($item, 'l', PARAM_INT)
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

    // TODO: trash items loeschen
    // TODO: day items auf einen tag verschieben
    // TODO: items datum loeschen
    
    die('ok');
}



$PAGE->set_url('/blocks/exacomp/weekly_schedule.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('pluginname', 'block_exacomp'));

block_exacomp_init_js_css();
$PAGE->requires->jquery_plugin('ui');

echo $OUTPUT->header();

function add_days($date, $days) {
    return mktime(0,0,0,date('m', $date), date('d', $date)+$days, date('Y', $date));
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
			<div class="items">
				<div class="item" id="item-1">item 1</div>
				<div class="item" id="item-2">item 2</div>
				<div class="item" id="item-3">item 3</div>
			</div>
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
            Kalenderwoche <?php echo date('Y/W', add_days($monday, 6) /* 1.1. kann der sonntag sein, dann ist die woche die 1te woche! */); ?>
            &nbsp;&nbsp;&nbsp;&nbsp;
			<a href="weekly_schedule.php?monday=<?=$nextMonday?>">nächste &gt;</a>
		</div>
		<div id="days">
			<div class="day" id="day-<?php echo add_days($monday, 0); ?>">
				<div class="header">Montag, <?php echo date('d.m.', add_days($monday, 0)); ?></div>
				<div class="items"></div>
			</div>
			<div class="day" id="day-<?php echo add_days($monday, 1); ?>">
				<div class="header">Dienstag, <?php echo date('d.m.', add_days($monday, 1)); ?></div>
				<div class="items">
					<div class="item" id="item-4">item 4</div>
					<div class="item" id="item-5">item 5</div>
				</div>
			</div>
			<div class="day" id="day-<?php echo add_days($monday, 2); ?>">
				<div class="header">Mittwoch, <?php echo date('d.m.', add_days($monday, 2)); ?></div>
				<div class="items"></div>
			</div>
			<div class="day" id="day-<?php echo add_days($monday, 3); ?>">
				<div class="header">Donnerstag, <?php echo date('d.m.', add_days($monday, 3)); ?></div>
				<div class="items"></div>
			</div>
			<div class="day" id="day-<?php echo add_days($monday, 4); ?>">
				<div class="header">Freitag, <?php echo date('d.m.', add_days($monday, 4)); ?></div>
				<div class="items"></div>
			</div>
		</div>
	</div>
</div>
<?php

$sql = "select s.*, e.title, eval.student_evaluation, eval.teacher_evaluation
		FROM {block_exacompschedule} s 
		JOIN {block_exacompexamples} e ON e.id = s.exampleid 
		LEFT JOIN {block_exacompexameval} eval ON eval.exampleid = s.exampleid AND eval.studentid = s.studentid
		WHERE s.studentid = ? AND s.timecreated IS null";
$studentid = 3;
$items = $DB->get_records_sql($sql,array($studentid));
var_dump($items);
echo $OUTPUT->footer();