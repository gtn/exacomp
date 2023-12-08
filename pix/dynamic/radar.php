<?php
require __DIR__ . '/../../inc.php';

global $DB, $OUTPUT, $PAGE, $USER;

$courseid = required_param('courseid', PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_simplehtml', $courseid);
}

$context = context_course::instance($courseid);
$PAGE->set_context($context);

// CHECK TEACHER
$isTeacher = block_exacomp_is_teacher($context);

$graphAction = required_param('graphAction', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);
$subjectid = required_param('subjectid', PARAM_INT);
$studentid = required_param('studentid', PARAM_INT);
$student = $DB->get_record('user', array('id' => $studentid));

$height = optional_param('height', 600, PARAM_INT);
$width = optional_param('width', 300, PARAM_INT);

/* pChart library inclusions */
$pathToPChart = __DIR__ . '/../../vendor/pChart/';
require $pathToPChart . 'class/pData.class.php';
require $pathToPChart . 'class/pDraw.class.php';
require $pathToPChart . 'class/pRadar.class.php';
require $pathToPChart . 'class/pImage.class.php';

$fontSize = 10;
$AxisRotation = 0;
$segments = 5;
$segmentHeight = 20;

switch ($graphAction) {
    case 'competenceProfileRadar':
        $topics = block_exacomp_get_topics_for_radar_graph($courseid, $studentid, $subjectid);
        $radarData = new stdClass();
        $radarData->labels = (array)array_map(function($t) {
            return $t->title;
        }, $topics);
        $radarData->datasets = [
            '0' => (object)[
                'label' => get_string("studentcomp", "block_exacomp"),
                'data' => array_values(array_map(function($a) {
                    return round($a->student, 2);
                }, $topics)),
                'palette' => array('R' => 249, 'G' => 178, 'B' => 51),
            ],
            '1' => (object)[
                'label' => get_string("teachercomp", "block_exacomp"),
                'data' => array_values(array_map(function($a) {
                    return round($a->teacher, 2);
                }, $topics)),
                'palette' => array('R' => 72, 'G' => 165, 'B' => 63),
            ],
        ];
        $AxisRotation = -90;
        $segments = 4;
        $segmentHeight = 25;

        break;
}


/* Set the default font properties */
$fontName = $pathToPChart . 'fonts/verdana.ttf';
$fontName = $pathToPChart . 'fonts/SourceSansPro-Regular.ttf';

$MyData = new pData();
foreach ($radarData->datasets as $dataKey => $dataset) {
    $MyData->addPoints($dataset->data, 'data' . $dataKey);
    if ($dataset->label) {
        $MyData->setSerieDescription('data' . $dataKey, $dataset->label);
    }
    if ($dataset->palette) {
        $MyData->setPalette('data' . $dataKey, $dataset->palette);
    }
}

/* Create the X serie */
$MyData->addPoints($radarData->labels, 'Labels');
$MyData->setAbscissa('Labels');

/* Create the pChart object */
$myPicture = new pImage($width, $height, $MyData);


/* Define general drawing parameters */
$myPicture->setFontProperties(array(
    'FontName' => $fontName,
    'FontSize' => $fontSize,
    'R' => 40,
    'G' => 40,
    'B' => 40));

/* Create the radar object */
$SplitChart = new pRadar();

/* Draw the 1st radar chart */
$myPicture->setGraphArea(10, 10, $width - 10, $height - 45);
$Options = array(
    'Layout' => RADAR_LAYOUT_STAR,
    'LabelPos' => RADAR_LABELS_HORIZONTAL,
    'DrawPoly' => true,
    'PolyAlpha' => 30,
    'AxisAlpha' => 20,
    'AxisRotation' => $AxisRotation,
    'Segments' => $segments,
    'SegmentHeight' => $segmentHeight,
    'PointRadius' => 2,
    'TicksLength' => 0,
    //'WriteValues' => true,
    //'WriteValuesInBubble' => false,
    //'ValuePadding' => 15,
);
$SplitChart->drawRadar($myPicture, $MyData, $Options);

/* Write down the legend */
$fontSizeLegend = 14;
$myPicture->setFontProperties(array(
    'FontName' => $fontName,
    'FontSize' => $fontSizeLegend));
$myPicture->drawLegend(10, $height - 25, array(
    'Style' => LEGEND_BOX,
    'Mode' => LEGEND_HORIZONTAL,
    'BoxWidth' => 10,
    'BoxHeight' => 10,
    'R' => 255,
    'G' => 255,
    'B' => 255));

/* Render the picture */
/* Render the picture (choose the best way) */
//$myPicture->autoOutput('111.png');
//$myPicture->Render("drawradar.png");
$myPicture->stroke();
