<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
global $USER;

require __DIR__ . '/inc.php';

$start = optional_param('start', 0, PARAM_INT);
$end = optional_param('end', 0, PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);
$topicid = required_param('topicid', PARAM_INT);
$userid = required_param('userid', PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_exacomp', $courseid);
}

// error if example does not exist or was created by somebody else
if (!$topic = $DB->get_record(BLOCK_EXACOMP_DB_TOPICS, array('id' => $topicid))) {
    print_error('invalidtopic', 'block_exacomp', $topicid);
}

block_exacomp_require_login($course);

$context = context_course::instance($courseid);

if ($userid != $USER->id) {
    block_exacomp_require_teacher($courseid);
}

if (!block_exacomp_use_eval_niveau($courseid)) {
    print_error('invalidevalniveau', 'block_exacomp');
}

/* PAGE URL - MUST BE CHANGED */
$PAGE->set_url('/blocks/exacomp/3dchart.php', array('courseid' => $courseid));
$PAGE->set_heading(block_exacomp_get_string('blocktitle'));
$PAGE->set_pagelayout('embedded');

// build breadcrumbs navigation
block_exacomp_build_breadcrum_navigation($courseid);

$exacomp_graph = (object)[
    'data' => [],
    'options' => (object)[],
];

$graph_options =& $exacomp_graph->options;
$graph_data =& $exacomp_graph->data;

/*
$graph_options->xMin = 0;
$graph_options->xMax = 10;
$graph_options->yMin = 0;
$graph_options->yMax = 10;
$graph_options->zMin = 0;
$graph_options->zMax = 10;
*/

$evaluation = block_exacomp_get_descriptor_statistic_for_topic($courseid, $topicid, $userid, $start, $end)['average_descriptor_evaluations'];
$graph_options->xLabels = array_map(function($label) {
    // remove LFS at the beginning
    return preg_replace('!^' . preg_quote(block_exacomp_get_string('niveau_short'), '!') . '!', '', $label);
}, array_keys(['' => ''] + $evaluation));
$graph_options->xLabel = block_exacomp_get_string('niveau_short');
$xlabels_long = array_keys(['' => ''] + $evaluation);

if (block_exacomp_get_assessment_comp_diffLevel($courseid)) {
    $evalniveau_titles = \block_exacomp\global_config::get_evalniveaus(true, $courseid);
} else {
    $evalniveau_titles = array('' => '', '0' => block_exacomp_get_string('teacherevaluation_short'));
}
$graph_options->yLabels = array_values($evalniveau_titles) + ['2'];
$y_id_to_index = array_combine(array_keys($evalniveau_titles), array_keys($graph_options->yLabels));
$ylabels_long = $graph_options->yLabels;

// add student's evaluation
end($graph_options->yLabels);
$student_value_index = key($graph_options->yLabels) + 1;
$graph_options->yLabels[$student_value_index] = block_exacomp_get_string('selfevaluation_short');
$ylabels_long[$student_value_index] = block_exacomp_get_string('selfevaluation');

// php <5.6.0 has no filter key function
function block_exacomp_array_filter_keys($arr, $cb) {
    return array_intersect_key($arr, array_flip(array_filter(array_keys($arr), $cb)));
}

$value_titles = block_exacomp_array_filter_keys(\block_exacomp\global_config::get_teacher_eval_items($courseid, true), function($k) {
    return $k >= 0;
});
$value_titles_long = block_exacomp_array_filter_keys(\block_exacomp\global_config::get_teacher_eval_items($courseid, false), function($k) {
    return $k >= 0;
});
$value_titles_self_assessment = \block_exacomp\global_config::get_student_eval_items(true, BLOCK_EXACOMP_TYPE_TOPIC, null, $courseid);

$graph_options->zLabels = array_fill(0, count($value_titles), '');

$graph_options->yColors = [
    $student_value_index => [
        'fill' => 'RGB(255,197,57)',
    ],
];

// to have a useful z value (height) we want the maximum of the teacher evaluation and the maximum of the student evaluation to both result in the same height
// if a teacher can evaluate with e.g. 10 different values, and the student only with 3, the 10th and the 3rd should result in the same height
// the height = (z-max / number of values) * the value. Teacher and student values are not necessarily counted the same way (e.g. the teacher can have 3 values, the max being 2, the student can have 3 values, the max being 3)
// ==> count the possible values. This is already done with $graph_options->zLabels = array_fill(0, count($value_titles), '');
// ==> the only problem that remains is that the student values are taken as is, and not scaled. The z-values are based on get_teacher_eval_items
// the student values are based on get_student_eval_items
// find the $scalefactor for the student values using count($value_titles_self_assessment);
$scalefactor = (count($graph_options->zLabels)-1) / (count($value_titles_self_assessment)-1); // -1 because we start counting at 0, and therefore the highest value is then e.g. 6, not 7, even though there are 7 values

// student values are from 1, to x. 0 meaning no value.
// teacher values are, depending on the setting, sometimes from 0 to x. -1 stands for no value.

$x = 1;
foreach ($evaluation as $e) {

    if ($e->studentvalue > 0) {
        $data_value = (object)[
            'x' => $x,
            'y' => $student_value_index,
            'z' => $e->studentvalue * $scalefactor,
            'label' => $xlabels_long[$x] . ' / ' . block_exacomp_get_string('selfevaluation') . ': <b>' . $value_titles_self_assessment[$e->studentvalue] . '</b>',
        ];
        $graph_data["{$data_value->x}-{$data_value->y}-{$data_value->z}"] = $data_value;
    }
    if ($e->teachervalues) {
        foreach ($e->teachervalues as $evkey => $tvalue) {
            // $e->teachervalues is an array with key = evaniveauid and value is the teacher evaluation
            if ($tvalue >= 0 && isset($y_id_to_index[$evkey])) {
                $data_value = (object)[
                    'x' => $x,
                    'y' => $y_id_to_index[$evkey],
                    'z' => $tvalue,
                    'label' => @$xlabels_long[$x] . ' / ' . @$ylabels_long[$y_id_to_index[$evkey]] . ': <b>' .
                        $e->teachervaluetitles[$evkey] . '</b>',
                ];
                $graph_data["{$data_value->x}-{$data_value->y}-{$data_value->z}"] = $data_value;
            }
            /*if ($e->teachervalue >= 0 && isset($y_id_to_index[$e->evalniveau])) {
                $data_value = (object) [
                        'x' => $x,
                        'y' => $y_id_to_index[$e->evalniveau],
                        'z' => $e->teachervalue,
                        'label' => @$xlabels_long[$x].' / '.@$ylabels_long[$y_id_to_index[$e->evalniveau]].': <b>'.
                                $e->teachervaluetitle.'</b>',
                ];
                $graph_data["{$data_value->x}-{$data_value->y}-{$data_value->z}"] = $data_value;
            }*/
        }
    }
    /*
                var title = evalniveau_titles_by_index[point.y] ? evalniveau_titles_by_index[point.y].title : '' || '';
                var value
                if (evalniveau_titles_by_index[point.y].id == student_value_id) {
                    value = exacomp_data.value_titles_self_assessment[point.z];
                } else {
                    value = exacomp_data.value_titles_long[Object.keys(exacomp_data.value_titles)[point.z]];
                }
                return title + ' <b>' + value + '</b>';
    */
    $x++;
}

$output = block_exacomp_get_renderer();
$output->requires()->js('/blocks/exacomp/javascript/vis.js', true);

// build tab navigation & print header
$PAGE->set_pagelayout('embedded');
echo $output->header_v2();

echo '<script> exacomp_graph = ' . json_encode($exacomp_graph) . '; </script>';

/* CONTENT REGION */
if (!$exacomp_graph->data) {
    echo block_exacomp_get_string('topic_3dchart_empty');
} else {
    echo html_writer::div(null, null, array('id' => 'mygraph'));
}

/* END CONTENT REGION */
echo $output->footer();
