<?php

require_once __DIR__.'/inc.php';
require_once $CFG->dirroot.'/lib/tcpdf/tcpdf.php';

$courseid = required_param('courseid', PARAM_INT);
$monday = optional_param('time', time(), PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse', 'block_simplehtml', $courseid);
}

require_login($courseid);

$context = context_course::instance($courseid);

// CHECK TEACHER
$isTeacher = block_exacomp_is_teacher($context);

$studentid = block_exacomp_get_studentid($isTeacher) ;
if($studentid == BLOCK_EXACOMP_SHOW_ALL_STUDENTS)
    $studentid = 0;


/* CONTENT REGION */
if($isTeacher){
    $coursestudents = block_exacomp_get_students_by_course($courseid);

    //check permission for viewing students profile
    if(!array_key_exists($studentid, $coursestudents))
        print_error("nopermissions","","","Show student profile");
}

$student = $DB->get_record('user',array('id' => $studentid));

$monday = block_exacomp_add_days($monday, 1 - date('N', $monday));

$day_cnt = 5;
$days = [];

function generate_day($day, $studentid) {
    $day->title = strftime('%a %d.%m.', $day->time);

    $examples = block_exacomp_get_examples_for_start_end_all_courses($studentid, $day->time, block_exacomp_add_days($day->time, 1)-1);
    
    foreach($examples as $example){
        $example->state = block_exacomp_get_dakora_state_for_example($example->courseid, $example->exampleid, $studentid);
        
        // find start slot
        for ($i = 0; $i < count($day->slots); $i++) {
            if ($day->slots[$i]->start_time >= $example->start) {
                $example->start_slot = $i;
                $example->end_slot = $i;
                break;
            }
        }
        
        // find end slot
        for ($i = $example->start_slot; $i < count($day->slots); $i++) {
            if ($day->slots[$i]->start_time >= $example->end) {
                break;
            }
            $example->end_slot = $i;
        }
        
        $example->rowspan = $example->end_slot - $example->start_slot + 1;
        
    }

    // first sort by start time, then by duration (same as fullcalendar)
    usort($examples, function($a, $b) {
        return 
            (
                $a->start <> $b->start
                ? $a->start > $b->start
                : $a->rowspan < $b->rowspan
            ) ? 1 : -1;
    });
    
    // init empty columns
    foreach ($day->slots as $slot) {
        $slot->cols = array_fill(0, 1000, false);
    }
    
    $day->colspan = 1; // the max colspan for this day
    foreach ($examples as $example) {
        for ($col_i = 0; $col_i < 1000; $col_i++) {
            // find if the event can be inserted into this column (all cells are free)
            $ok = true;
            for ($slot_i = $example->start_slot; $slot_i <= $example->end_slot; $slot_i++) {
                if ($day->slots[$slot_i]->cols[$col_i]) {
                    $ok = false;
                    break;
                }
            }
            
            // yes, can be inserted here
            if ($ok) {
                for ($slot_i = $example->start_slot; $slot_i <= $example->end_slot; $slot_i++) {
                    $day->slots[$slot_i]->cols[$col_i] = true;
                }
                
                $day->slots[$example->start_slot]->cols[$col_i] = $example;
                $day->colspan = max($day->colspan, $col_i+1);
                break;
            }
            
            // no -> continue to next column
        }
    }
}

for ($i = 0; $i < $day_cnt; $i++) {
    // build the day object
    $time = block_exacomp_add_days($monday, $i);
    $days[$time] = (object)[
        'time' => $time,
        'slots' => array_map(function($x){return (object)$x; }, block_exacomp_build_json_time_slots($time))
    ];
    
    // load the events and columns for this day
    generate_day($days[$time], $studentid);
}




class PDF extends TCPDF {
}

// Instanciation of inherited class
$pdf = new PDF('L');
// $pdf->AliasNbPages();
$pdf->SetPrintHeader(false);
$pdf->SetPrintFooter(false);

$pdf->AddPage();
$pdf->SetFont('helvetica', '', 9);

$tbl  = '
    <style>
        .event-default {
            color: #fff;
            background-color: #026cc5;
        }
        .state4 {
            color: #fff;
            background-color: rgb(24, 164, 6);
            
        }
        
        .state3 {
            color: #fff;
            background-color: rgb(189, 189, 189);
        }
        .different-course {
            color: #fff;
            background-color: #acbcca;
        }
    table td {
        text-align: center;
        height: 14px;
    }
    </style>
        
    <table><tr>
        <td style="font-size: 10px;" align="left">Kurs: '.$course->fullname.'</td>
        <td style="font-size: 10px;" align="right">Kursteilnehmer: '.fullname($student).'</td>
    </tr></table>
    &nbsp;<br />
    <table border="0.1" style="padding: 1px">';
$tbl .= '<tr><td></td>';
foreach ($days as $day) {
    $tbl .= '<td colspan="'.$day->colspan.'">'.$day->title.'</td>';
}
$tbl .= '</tr>';

$color_i = 0;
foreach (block_exacomp_build_json_time_slots() as $slot_i=>$slot) {
    $tbl .= '<tr nobr="true"';
    if ($slot['name']) {
        $color_i++;
    }
    if ($color_i % 2) $tbl .= ' style="background-color:#EEEEEE;"';
    $tbl .= '><td>'.$slot["name"].'</td>';
    foreach ($days as $day) {
        for ($col_i = 0; $col_i < $day->colspan; $col_i++) {
            $example = $day->slots[$slot_i]->cols[$col_i];
            if (is_object($example)) {
                
                if ($example->courseid != $courseid)
                    $class = 'different-course';
                elseif ($example->state == 3)
                    $class = 'state3';
                elseif ($example->state == 4)
                    $class = 'state4';
                else
                    $class = 'event-default';
                
                
                
                $tbl .= '<td rowspan="'.$example->rowspan.'" class="'.$class.'">';
                $tbl .= $example->title;
                $tbl .= '</td>';
            } else if (!$example) {
                $tbl .= '<td></td>';
            }
        }
    }
    $tbl .= '</tr>';
}
$tbl .= '</table>';

$pdf->writeHTML($tbl, true, false, false, false, '');

$pdf->Output();
