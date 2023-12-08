<?php

/* pChart library inclusions */
$pathToPChart = __DIR__ . '/../../vendor/pChart/';
require $pathToPChart . 'class/pData.class.php';
require $pathToPChart . 'class/pDraw.class.php';
require $pathToPChart . 'class/pPie.class.php';
require $pathToPChart . 'class/pImage.class.php';

$height = array_key_exists('height', $_GET) && $_GET['height'] ? $_GET['height'] : 50;
$width = array_key_exists('width', $_GET) && $_GET['width'] ? $_GET['width'] : 50;
$evalValue = array_key_exists('evalValue', $_GET) && $_GET['evalValue'] ? $_GET['evalValue'] : 0;
$evalMax = array_key_exists('evalMax', $_GET) && $_GET['evalMax'] ? $_GET['evalMax'] : 0;
$niveauTitle = array_key_exists('niveauTitle', $_GET) && $_GET['niveauTitle'] ? $_GET['niveauTitle'] : '';
$diffLevel = array_key_exists('diffLevel', $_GET) && $_GET['diffLevel'] ? $_GET['diffLevel'] : '';
$stringValue = array_key_exists('stringValue', $_GET) && $_GET['stringValue'] ? $_GET['stringValue'] : '';
$stringValueShort = array_key_exists('stringValueShort', $_GET) && $_GET['stringValueShort'] ? $_GET['stringValueShort'] : '';
$assessmentType = array_key_exists('assessmentType', $_GET) && $_GET['assessmentType'] ? intval($_GET['assessmentType']) : 0;

/* Create and populate the pData object */
$segments = array();
//$segments = array(10,20,30,40,50,60,70,80,90,100);
$maxCircleAlpha = 100;
$minCircleAlpha = 50;
$circleAlpha = $maxCircleAlpha;

function nullPicture($gray = false) {
    global $centerText, $fontSize, $circleColor, $circleTextColor, $drawCheckMarkNo, $circleAlpha;
    //$centerText[0] = 'X';
    //$fontSize = $fontSize * 0.75;
    //$circleColor = array('R' => 172, 'G' => 183, 'B' => 182);
    //$circleTextColor = array('R' => 225, 'G' => 225, 'B' => 225);
    if ($gray) {
        $circleColor = array('R' => 172, 'G' => 183, 'B' => 182, 'Alpha' => $circleAlpha);
    }
    $drawCheckMarkNo = true;
}

;

function plusPicture() {
    global $centerText, $fontSize, $circleColor, $circleTextColor, $drawCheckMarkYes;
    //$centerText[0] = 'V';
    //$fontSize = $fontSize * 0.75;
    $drawCheckMarkYes = true;
}

;

function changeCircleAlpha($value) {
    global $circleColor, $maxCircleAlpha, $minCircleAlpha, $evalMax;
    if ($value >= $evalMax) {
        $circleColor['Alpha'] = 100;
    } else {
        $k1 = ($maxCircleAlpha - $minCircleAlpha) / $evalMax;
        $result = round($k1 * $value + $minCircleAlpha);
        if ($result < $minCircleAlpha) {
            $circleColor['Alpha'] = $minCircleAlpha;
        } else {
            $circleColor['Alpha'] = $result;
        }
    }
}

// text in the center
$drawCheckMarkYes = false;
$drawCheckMarkNo = false;
$drawOuterGraph = false;
$centerText = array();
$fontSizes = array();
$circleColor = array('R' => 30, 'G' => 111, 'B' => 188, 'Alpha' => $circleAlpha);
$circleTextColor = array('R' => 255, 'G' => 255, 'B' => 255);
$fontSize = 24;
$xBuffer = 0; // different fonts can have different values
$outerGraphWidth = 7;
$outerMargin = 3;
$originalWidth = $width;
$originalHeight = $height;
switch ($assessmentType) {
    case 0: // none
        if ($diffLevel && $niveauTitle) {
            $centerText[0] = $niveauTitle;
            $xBuffer = 2;
        }
        break;
    case 1: // Grade
    case 3: // Points
        if (($assessmentType == 1 && (!$evalValue || $evalValue == -1))
            || $assessmentType == 3 && ($evalValue == -1 || (!$evalValue && $evalValue !== 0))
            || ($diffLevel && !$niveauTitle)) {
            nullPicture(true);
            break;
        }
        $centerText[0] = $evalValue;
        if (strlen($centerText[0]) > 2) { // for numbers greater than 100
            $fontSize = $fontSize * 0.85;
        }
        if ($diffLevel && $niveauTitle) {
            $centerText[0] = trim($niveauTitle);
            $centerText[1] = $evalValue;
            if (strlen($centerText[0]) || strlen($centerText[1]) > 2) { // for numbers greater than 100
                $fontSize = $fontSize * 0.75;
            }
        }
        if ($diffLevel) {
            $drawOuterGraph = true;
            $width = $width + (($outerMargin + $outerGraphWidth) * 2);
            $height = $height + (($outerMargin + $outerGraphWidth) * 2);
        }
        changeCircleAlpha($evalValue);

        /*        if (($assessmentType == 1 && !$evalValue)
                    || $assessmentType == 3 && ($evalValue == -1 || (!$evalValue && $evalValue !==0))) {
                    nullPicture(true);
                } else {
                    $drawOuterGraph = true;
                    $width = $width + (($outerMargin + $outerGraphWidth) * 2);
                    $height = $height + (($outerMargin + $outerGraphWidth) * 2);
                    $centerText[0] = $evalValue;
                    if (strlen($centerText[0]) > 2) { // for numbers greater than 100
                        $fontSize = $fontSize * 0.85;
                    }
                    if ($diffLevel && $niveauTitle) {
                        $centerText[0] = trim($niveauTitle);
                        $centerText[1] = $evalValue;
                        if (strlen($centerText[0]) || strlen($centerText[1]) > 2) { // for numbers greater than 100
                            $fontSize = $fontSize * 0.75;
                        }
                    }
                }*/
        break;
    case 2: // Verbose
        if ($diffLevel && $niveauTitle && $evalValue > -1) {
            $centerText[0] = trim($niveauTitle);
            $xBuffer = 2;
            $drawOuterGraph = true;
            $width = $width + (($outerMargin + $outerGraphWidth) * 2);
            $height = $height + (($outerMargin + $outerGraphWidth) * 2);
        } else if (!$diffLevel) {
            /*if ($stringValueShort) { // for app
                $centerText[0] = trim($stringValueShort);
                $fontSize = 16;
            } else*/
            if ($stringValue) {
                $string = explode('-', $stringValue);
                foreach ($string as $key => $word) {
                    $centerText[$key] = $word;
                    if (mb_strlen($word) >= 10) {
                        $fontSizes[] = 7;
                    } else {
                        $fontSizes[] = 8;
                    }
                }
                $fontSize = min($fontSizes);
            } else {
                nullPicture(true);
            }
        } else {
            nullPicture(true);
        }

        ////
        /*if ($niveauTitle) {
            if ($evalValue > 0 || $evalValue === 0) {
                $drawOuterGraph = true;
                $drawOuterValue = 100; // percents
                $centerText[0] = trim($niveauTitle);
                $xBuffer = 2;
                $width = $width + (($outerMargin + $outerGraphWidth) * 2);
                $height = $height + (($outerMargin + $outerGraphWidth) * 2);
            } else {
                //nullPicture();
                //$circleColor = array('R' => 172, 'G' => 183, 'B' => 182);
                $centerText[0] = trim($niveauTitle);
                $xBuffer = 2;
            }
        } else {
            if ($stringValueShort) {
                $centerText[0] = trim($stringValueShort);
                $fontSize = 16;
            } else if ($stringValue) {
                $string = explode('-', $stringValue);
                foreach ($string as $key => $word) {
                    $centerText[$key] = $word;
                    if (mb_strlen($word) >= 10) {
                        $fontSizes[] = 7;
                    } else {
                        $fontSizes[] = 8;
                    }
                }
                $fontSize = min($fontSizes);
            } else {
                nullPicture(true);
            }
        }*/
        break;
    case 4: // Yes/No
        if ($evalValue > 0) {
            plusPicture();
        } else if ($evalValue == -1) {
            nullPicture(true);
        } else {
            nullPicture();
        }
        break;
}


//$assessmentType==1 //Grade
//($evalMax-$evalValue)

$MyData = new pData();
$labels = array();
$colors = array();
$raysCount = 50;

for ($i = 0; $i < $raysCount; $i++) {
    $segments[$i] = 100 / $raysCount;
    $labels[$i] = $i;
    //$colors[$i] = array('R' => 140, 'G' => 182, 'B' => 196);
    $MyData->Palette[$i] = array('R' => 140, 'G' => 182, 'B' => 196, 'Alpha' => 100);
    if ($assessmentType == 1) { //grade
        if ($evalMax > 0) {
            if ($evalValue == 0) {//no grading: no circle
                $MyData->Palette[$i]['Alpha'] = 0;
            } else {
                if ($evalValue == $evalMax && $i <= 1) {
                    $MyData->Palette[$i]['Alpha'] = 100;
                } else if ($i >= (($evalMax - $evalValue) * 100 / ($evalMax - 1)) / (100 / $raysCount)) {
                    $MyData->Palette[$i]['Alpha'] = 0;
                }

            }
        } else {
            $MyData->Palette[$i]['Alpha'] = 0;
        }
    } else {
        if ($evalMax > 0) {
            if ($i >= ($evalValue * 100 / $evalMax) / (100 / $raysCount)) {
                $MyData->Palette[$i]['Alpha'] = 0;
            }
        } else {
            $MyData->Palette[$i]['Alpha'] = 0;
        }
    }

}

$MyData->addPoints($segments, "segments");
//for ($i = 0; $i < $raysCount; $i++) {
//    $MyData->setPalette('segments', $colors[$i]);
//}

//$MyData->setSerieDescription("segments", "segments");

/* Define the absissa serie */
$MyData->addPoints($labels, "Labels");
$MyData->setAbscissa("Labels");

/* Create the pChart object */
$myPicture = new pImage($width, $height, $MyData, true);

/* Set the default font properties */
$fontName = $pathToPChart . 'fonts/verdana.ttf';
$fontName = $pathToPChart . 'fonts/SourceSansPro-Regular.ttf';
$myPicture->setFontProperties(array("FontName" => $fontName, "FontSize" => 10, "R" => 255, "G" => 255, "B" => 255));

/* Enable shadow computing */
//$myPicture->setShadow(TRUE,array("X"=>2,"Y"=>2,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>50));

$xCenter = floor($width / 2);
$yCenter = floor($height / 2);
$radius = floor(1 * min(floor($originalWidth / 2), floor($originalHeight / 2))) - 1;
$innerRadius = $radius + $outerMargin;
if ($drawOuterGraph) {
    /* Create the pPie object */
    $PieChart = new pPie($myPicture, $MyData);
    //foreach ($colors as $key => $color) {
    //    $PieChart->setSliceColor($key, $color);
    //}
    $PieChart->draw2DRing(
        $xCenter,
        $yCenter,
        array(
            'OuterRadius' => $radius + $outerMargin + $outerGraphWidth,
            // original pPie.class.php has a bug with 'InnerRadius'. need to de changed if pChart is updating.
            // Fork for php7 does not have this bug, but has 'OuterRadius' instead of 'Radius' option
            'InnerRadius' => $radius + $outerMargin,
            "WriteValues" => false,
            'Precision' => 100,
            //"ValueR" => 255,
            //"ValueG" => 0,
            //"ValueB" => 0,
            "Border" => true,
            //'BorderR' => 0,
            //'BorderG' => 0,
            //'BorderB' => 0,
            //'BorderAlpha' => 0,
        ));
}

if (count($centerText) > 0 || $drawCheckMarkYes || $drawCheckMarkNo) {
    $myPicture->drawFilledCircle(
        $xCenter,
        $yCenter,
        $radius,
        array("R" => $circleColor['R'],
            "G" => $circleColor['G'],
            "B" => $circleColor['B'],
            'Alpha' => $circleColor['Alpha']));
}

if (count($centerText) > 0) {
    $middleX = array();
    $yTextBuffer = 0; // vertical correction if text has comma

    foreach ($centerText as $key => $text) {
        $centerText[$key] = str_replace('.', ',', $text);
        $txtSize = imagettfbbox((array_key_exists($key, $fontSizes) && $fontSizes[$key] ? $fontSizes[$key] : $fontSize), 0, $fontName, $text);
        $middleX[$key] = $xCenter - ($txtSize[2] / 2) - $xBuffer;
        if (strpos($text, ',') !== false) {
            $yTextBuffer = 3;
        }
    }

    $vertCorrection = 0;
    if (count($centerText) > 1) {
        $vertCorrection = $fontSize - ($fontSize * 0.4);
        //$vertCorrection = 12;
    }
    //echo $vertCorrection; exit;
    foreach ($centerText as $key => $text) {
        $myPicture->drawText(
            $middleX[$key],
            ($yCenter + $yTextBuffer) + ($key == 0 ? (0 - $vertCorrection) : $vertCorrection),
            $text,
            array('FontSize' => (array_key_exists($key, $fontSizes) && $fontSizes[$key] ? $fontSizes[$key] : $fontSize),
                "R" => $circleTextColor['R'],
                "G" => $circleTextColor['G'],
                "B" => $circleTextColor['B'],
                "Align" => TEXT_ALIGN_MIDDLELEFT,
                /*"DrawBox" => true*/)
        );
    }
} else {

}

if ($drawCheckMarkNo) {
    $offsets = $radius - ($radius * 0.72); // from center
    $lineOptions = array(
        'R' => 255,
        'G' => 255,
        'B' => 255,
        'Weight' => 1,
    );
    // draw \
    $myPicture->drawLine($xCenter - $offsets,
        $yCenter - $offsets,
        $xCenter + $offsets,
        $yCenter + $offsets,
        $lineOptions);
    // draw /
    $myPicture->drawLine($xCenter - $offsets,
        $yCenter + $offsets,
        $xCenter + $offsets,
        $yCenter - $offsets,
        $lineOptions);
}

if ($drawCheckMarkYes) {
    $offsets = $radius - ($radius * 0.7); // from center
    $lineOptions = array(
        'R' => 255,
        'G' => 255,
        'B' => 255,
        'Weight' => 1,
    );
    // draw \
    $myPicture->drawLine($xCenter - $offsets,
        $yCenter,
        $xCenter - ($offsets / 3),
        $yCenter + $offsets,
        $lineOptions);
    // draw /
    $myPicture->drawLine($xCenter - ($offsets / 3),
        $yCenter + $offsets,
        $xCenter + $offsets,
        $yCenter - $offsets,
        $lineOptions);
}


/* Render the picture (choose the best way) */
//$myPicture->autoOutput('111.png');
$myPicture->stroke();
