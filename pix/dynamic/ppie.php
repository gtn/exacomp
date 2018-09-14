<?php

/* pChart library inclusions */
$pathToPChart = __DIR__.'/../../vendor/pChart/';
require $pathToPChart.'class/pData.class.php';
require $pathToPChart.'class/pDraw.class.php';
require $pathToPChart.'class/pPie.class.php';
require $pathToPChart.'class/pImage.class.php';

$height = $_GET['height'] ? $_GET['height'] : 50;
$width = $_GET['width'] ? $_GET['width'] : 50;
$value = $_GET['value'] ? $_GET['value'] : 0;
$maxValue = $_GET['valueMax'] ? $_GET['valueMax'] : 0;
$title = $_GET['title'] ? $_GET['title'] : '';

/* Create and populate the pData object */
$segments = array();
$labels = array();
$colors = array();
for ($i = 0; $i < $maxValue; $i++) {
    $segments[] = 100 / $maxValue;
    $labels[] = $i;
}
if ($value < 0) {
    for ($i = 0; $i < $maxValue;  $i++) {
        $colors[] = array('R' => 255, 'G' => 255, 'B' => 255);// '#ffffff';
    }
} elseif ($value == 0) {
    for ($i = 0; $i < $maxValue; $i++){
        $colors[] = array('R' => 221, 'G' => 0, 'B' => 26);// '#dd001a';
    }

} elseif ($value > 0) {
    for ($i = 0; $i < $value; $i++) {
        $colors[] = array('R' => 10, 'G' => 221, 'B' => 26);// '#0add1a';
    }
    for($i = $value; $i < $maxValue; $i++) {
        $colors[] = array('R' => 255, 'G' => 255, 'B' => 255);// '#ffffff';
    }
}

$MyData = new pData();   
$MyData->addPoints($segments, "segments");
//$MyData->setPalette('segments', $colors);

//$MyData->setSerieDescription("segments", "segments");

/* Define the absissa serie */
$MyData->addPoints($labels, "Labels");
$MyData->setAbscissa("Labels");

/* Create the pChart object */
$myPicture = new pImage($width, $height, $MyData, true);
//$myPicture->setGraphArea(20,20,40,40);

/* Draw a solid background */
//$Settings = array("R"=>170, "G"=>183, "B"=>87, "Dash"=>1, "DashR"=>190, "DashG"=>203, "DashB"=>107);
//$myPicture->drawFilledRectangle(0,0,300,300,$Settings);

/* Overlay with a gradient */
//$Settings = array("StartR"=>219, "StartG"=>231, "StartB"=>139, "EndR"=>1, "EndG"=>138, "EndB"=>68, "Alpha"=>50);
//$myPicture->drawGradientArea(0,0,300,260,DIRECTION_VERTICAL,$Settings);
//$myPicture->drawGradientArea(0,0,300,20,DIRECTION_VERTICAL,array("StartR"=>0,"StartG"=>0,"StartB"=>0,"EndR"=>50,"EndG"=>50,"EndB"=>50,"Alpha"=>100));

/* Add a border to the picture */
//$myPicture->drawRectangle(0,0,299,259,array("R"=>0,"G"=>0,"B"=>0));

/* Write the picture title */ 
//$myPicture->setFontProperties(array("FontName" => $pathToPChart.'fonts/Silkscreen.ttf', "FontSize"=>6));
//$myPicture->drawText(10, 13, "pPie - Draw 2D ring charts",array("R"=>255,"G"=>255,"B"=>255));

/* Set the default font properties */ 
$myPicture->setFontProperties(array("FontName" => $pathToPChart.'fonts/Verdana.ttf', "FontSize"=>10, "R"=>80, "G"=>80, "B"=>80));

/* Enable shadow computing */ 
//$myPicture->setShadow(TRUE,array("X"=>2,"Y"=>2,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>50));

/* Create the pPie object */ 
$PieChart = new pPie($myPicture, $MyData);
foreach ($colors as $key => $color) {
    $PieChart->setSliceColor($key, $color);
}

/* Draw an AA pie chart */
$xCenter = floor($width / 2);
$yCenter = floor($height / 2);
$radius = ceil(0.8 * min($xCenter, $yCenter));
$innerRadius = ceil($radius / 2);
$PieChart->draw2DRing(
        $xCenter,
        $yCenter,
        array(
            'Radius' => $radius,
            'InnerRadius' => $innerRadius, // pPie.class.php has a bug with this. need to de changed if pChart is updating
            "WriteValues" => false,
            "ValueR" => 255,
            "ValueG" => 255,
            "ValueB" => 255,
            "Border" => true,
            'BorderR' => 175,
            'BorderG' => 175,
            'BorderB' => 175
        ));

$myPicture->drawText(
        $xCenter,
        $yCenter,
        $title,
        array('FontSize' => $innerRadius-3, "R" => 0, "G" => 0, "B"=>0 , "Align" => TEXT_ALIGN_MIDDLEMIDDLE)
);

/* Write the legend box */ 
//$myPicture->setShadow(false);
//$PieChart->drawPieLegend(15,40,array("Alpha"=>20));

/* Render the picture (choose the best way) */
//$myPicture->autoOutput('111.png');
$myPicture->stroke();
