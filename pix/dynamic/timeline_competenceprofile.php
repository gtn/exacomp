<?php
require __DIR__ . '/../../inc.php';

global $DB, $OUTPUT, $PAGE, $USER;

$courseid = required_param('courseid', PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_simplehtml', $courseid);
}

//block_exacomp_require_login($course);
$context = context_course::instance($courseid);
$PAGE->set_context($context);

// CHECK TEACHER
$isTeacher = block_exacomp_is_teacher($context);

//$studentid = block_exacomp_get_studentid() ;
$studentid = required_param('studentid', PARAM_INT);
$student = $DB->get_record('user', array('id' => $studentid));

/* pChart library inclusions */
$pathToPChart = __DIR__ . '/../../vendor/pChart/';

include($pathToPChart . 'class/pData.class.php');
include($pathToPChart . 'class/pDraw.class.php');
include($pathToPChart . 'class/pImage.class.php');

$height = optional_param('height', 300, PARAM_INT);
$width = optional_param('width', 600, PARAM_INT);

$renderer = $PAGE->get_renderer('block_exacomp');
$graphData = $renderer->timeline_graph($course, $student, true);

$max_timestamp = time();
$min_timestamp = strtotime('yesterday', time());

/*echo '<pre>';
print_r($graphData);
echo '</pre>';
exit;*/

$MyData = new pData();
$teacherSerie = block_exacomp_get_string("teacher");
$studentSerie = block_exacomp_get_string("student");
$totalSerie = block_exacomp_get_string("timeline_available");
$MyData->addPoints($graphData['teacher'], $teacherSerie);
$MyData->setPalette($teacherSerie, array("R" => 2, "G" => 166, "B" => 0)); // #02a600
$MyData->addPoints($graphData['student'], $studentSerie);
$MyData->setPalette($studentSerie, array("R" => 0, "G" => 117, "B" => 221)); // #0075dd
$MyData->addPoints($graphData['total'], $totalSerie);
$MyData->setPalette($totalSerie, array("R" => 170, "G" => 170, "B" => 170)); //
//$MyData->setSeriePicture("User","resources/serie1.png");
//$MyData->setSeriePicture("Group","resources/serie2.png");
$MyData->setSerieWeight($teacherSerie, 0.5);
$MyData->setSerieWeight($studentSerie, 0.5);
$MyData->setSerieWeight($totalSerie, 0.5);
//$MyData->setSerieTicks($teacherSerie,4);

//$MyData->setAxisName(0, "Hours");
$MyData->addPoints($graphData['labels'], 'Labels');
//$MyData->setSerieDescription("Labels","Months");
$MyData->setAbscissa("Labels");

/* Create the pChart object */
$myPicture = new pImage($width, $height, $MyData);

/* Draw the background */
//$Settings = array("R"=>170, "G"=>183, "B"=>87, "Dash"=>1, "DashR"=>190, "DashG"=>203, "DashB"=>107);
//$myPicture->drawFilledRectangle(0,0,700,230,$Settings);

/* Overlay with a gradient */
//$Settings = array("StartR"=>219, "StartG"=>231, "StartB"=>139, "EndR"=>1, "EndG"=>138, "EndB"=>68, "Alpha"=>50);
//$myPicture->drawGradientArea(0,0,700,230,DIRECTION_VERTICAL,$Settings);
//$myPicture->drawGradientArea(0,0,700,20,DIRECTION_VERTICAL,array("StartR"=>0,"StartG"=>0,"StartB"=>0,"EndR"=>50,"EndG"=>50,"EndB"=>50,"Alpha"=>80));

/* Add a border to the picture */
//$myPicture->drawRectangle(0,0,699,229,array("R"=>0,"G"=>0,"B"=>0));

/* Write the picture title */
$myPicture->setFontProperties(array("FontName" => $pathToPChart . 'fonts/Verdana.ttf', "FontSize" => 7));
//$myPicture->drawText(10,13,"drawPlotChart() - draw a plot chart",array("R"=>255,"G"=>255,"B"=>255));

/* Write the chart title */
//$myPicture->setFontProperties(array("FontName"=>$pathToPChart.'fonts/Forgotte.ttf',"FontSize"=>11));
//$myPicture->drawText(250,55,"Average time spent on projects",array("FontSize"=>20,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE));

/* Draw the scale and the 1st chart */
$myPicture->setGraphArea(20, 20, $width - 20, $height - 80);
//$myPicture->drawFilledRectangle(20, 20, $width - 20, $height - 20, array("R"=>255,"G"=>255,"B"=>255,"Surrounding"=>-200,"Alpha"=>10));
$myPicture->drawScale(array(
    'Mode' => SCALE_MODE_START0,
    'DrawSubTicks' => false,
    'GridTicks' => 0,
    'AxisR' => 175,
    'AxisG' => 175,
    'AxisB' => 175,
    'GridR' => 175,
    'GridG' => 175,
    'GridB' => 175,
    'TickR' => 175,
    'TickG' => 175,
    'TickB' => 175,
    'GridAlpha' => 25,
    'AxisAlpha' => 0,
    'TickAlpha' => 25,
    'LabelRotation' => 75,
    'XAxisTitleMargin' => 30,
));
//$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));
//$myPicture->setFontProperties(array("FontName"=>$pathToPChart.'fonts/pf_arma_five.ttf',"FontSize"=>6));
//$myPicture->drawSplineChart();
$myPicture->drawLineChart(array());
$myPicture->drawPlotChart(array(
    'DisplayValues' => true,
    'DisplayColor' => DISPLAY_AUTO,
    'PlotBorder' => true,
    'BorderR' => 255,
    'BorderG' => 255,
    'BorderB' => 255,
    'BorderAlpha' => 100,
    'BorderSize' => 1,
));

/* Draw the scale and the 2nd chart */
/*$myPicture->setGraphArea(500,60,670,190);
$myPicture->drawFilledRectangle(500,60,670,190,array("R"=>255,"G"=>255,"B"=>255,"Surrounding"=>-200,"Alpha"=>10));
$myPicture->drawScale(array("Pos"=>SCALE_POS_TOPBOTTOM,"DrawSubTicks"=>TRUE));
$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));
$myPicture->drawPlotChart();
$myPicture->setShadow(FALSE);*/

/* Write the chart legend */
$Config = array("FontR" => 0,
    "FontG" => 0,
    "FontB" => 0,
    "Margin" => 6,
    "Alpha" => 30,
    "BoxSize" => 5,
    "Style" => LEGEND_NOBORDER,
    "Mode" => LEGEND_HORIZONTAL,
    "FontName" => $pathToPChart . 'fonts/Verdana.ttf',
);
$myPicture->drawLegend(25, 10, $Config);
$myPicture->setShadow(false);

/* Render the picture (choose the best way) */
//$myPicture->autoOutput();
$myPicture->stroke();
