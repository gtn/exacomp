<?php   
 // data
 require_once '../inc.php';
require_once '../lib/div.php';
require_once($CFG->dirroot . "/lib/datalib.php");

global $COURSE, $CFG, $OUTPUT, $USER;

$par=$_GET["par"];
$varname="graphval".$par;
$topics2=unserialize($USER->$varname);
$fullname=$USER->lastname;

		/*
echo "<pre>";
	print_r($USER->graphval);
	echo  "</pre>";	die;
$topics2=unserialize($USER->graphval[$par]);
*/
//$topics2=unserialize($par);
	/*	echo "<pre>";
	print_r($topics2);
	echo  "</pre>";	die;*/
/*
$topics[1]["title"]=$USER->lastname;
$topics[1]["person"]=80;
$topics[1]["all"]=20;
$topics[2]["title"]=$USER->franz;
$topics[2]["person"]=90;
$topics[2]["all"]=100;
$topics[3]["title"]="3. Kern";
$topics[3]["person"]=60;
$topics[3]["all"]=89;
$topics[4]["title"]="4. Bewerten und anwenden";
$topics[4]["person"]=67;
$topics[4]["all"]=85;
		echo "<pre>";
	print_r($topics);
	echo  "</pre>";*/



draw_graph ($topics2,$fullname);

function draw_graph($data,$fullname){
$count_titles = 0;
$lenght_labels_for_graph = 20;
$str_text="";
$n=1;
foreach ($data as  $val) {

	$val["title"]=preg_replace("/^([0-9])+\./","",$val["title"]);
	if (strlen($val["title"])>$lenght_labels_for_graph)
		$arr_labels[] = substr($n.". ".$val["title"], 0,  $lenght_labels_for_graph).". . .";
	else 
		$arr_labels[] = $n.". ".$val["title"];
		

	
		$arr_text_titles[] = $n.". ".$val["title"];
	$arr_val_all[] = $val["all"];
	$arr_val_person[] = $val["person"];
	$count_titles++;
	$n++;
	};

 /* pChart library inclusions */
 include("class/pData.class.php");
 include("class/pDraw.class.php");
 include("class/pRadar.class.php");
 include("class/pImage.class.php");

 /* Create and populate the pData object */
 $MyData = new pData();   
 $MyData->addPoints($arr_val_person,"ScoreA");  
 $MyData->addPoints($arr_val_all,"ScoreB"); 
 $MyData->setSerieDescription("ScoreA",$fullname);
 $MyData->setPalette("ScoreA",array("R"=>75,"G"=>85,"B"=>217));
 $MyData->setSerieDescription("ScoreB","Gesamt");

 /* Define the absissa serie */
 $MyData->addPoints($arr_labels, "Labels");
 $MyData->setAbscissa("Labels");

 /* Create the pChart object */
  $width_image = 390;
 $height_image = 265 + ($count_titles*20);
 
 $myPicture = new pImage($width_image,$height_image,$MyData);

 /* Draw a solid background */
 //$Settings = array("R"=>179, "G"=>217, "B"=>91, "Dash"=>1, "DashR"=>199, "DashG"=>237, "DashB"=>111);
 //$myPicture->drawFilledRectangle(0,0,300,230,$Settings);

 /* Overlay some gradient areas */
 //$Settings = array("StartR"=>194, "StartG"=>231, "StartB"=>44, "EndR"=>43, "EndG"=>107, "EndB"=>58, "Alpha"=>50);
 //$myPicture->drawGradientArea(0,0,300,230,DIRECTION_VERTICAL,$Settings);

 /* Add a border to the picture */
 //$myPicture->drawRectangle(0,0,299,229,array("R"=>0,"G"=>0,"B"=>0));

 /* Set the default font properties */ 
 $myPicture->setFontProperties(array("FontName"=>"fonts/GeosansLight.ttf","FontSize"=>10,"R"=>0,"G"=>0,"B"=>0));

 /* Enable shadow computing */ 
 $myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));

 /* Create the pRadar object */ 
 $SplitChart = new pRadar();

 /* Draw a radar chart */ 
 $myPicture->setGraphArea(10,10,225,225);
 //$Options = array("Layout"=>RADAR_LAYOUT_STAR,"BackgroundGradient"=>array("StartR"=>255,"StartG"=>255,"StartB"=>255,"StartAlpha"=>100,"EndR"=>207,"EndG"=>227,"EndB"=>125,"EndAlpha"=>50), "FontName"=>"fonts/pf_arma_five.ttf","FontSize"=>6,"LabelPadding"=>5);
 $Options = array("Layout"=>RADAR_LAYOUT_STAR, "FontName"=>"fonts/pf_arma_five.ttf","FontSize"=>6); 
 $SplitChart->drawRadar($myPicture,$MyData,$Options);

 /* Write the chart legend */ 
 //$myPicture->setFontProperties(array("FontName"=>"fonts/GeosansLight.ttf","FontSize"=>12));
 //$myPicture->drawLegend(205,195,array("Style"=>LEGEND_BOX,"Mode"=>LEGEND_HORIZONTAL));/**/
$myPicture->setFontProperties(array("FontName"=>"fonts/GeosansLight.ttf","FontSize"=>12));
 $myPicture->drawLegend(10,245,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL));/**/
 
 
 /* Write some text */
 $myPicture->setFontProperties(array("FontName"=>"fonts/GeosansLight.ttf","FontSize"=>6));
 $TextSettings = array("R"=>0,"G"=>0,"B"=>0,"Angle"=>0,"FontSize"=>9, "Align"=>TEXT_ALIGN_BOTTOMLEFT);
 foreach ($arr_text_titles as $text){
	$str_text .= $text."\r\n";
}
 $myPicture->drawText(10,$height_image-20,$str_text,$TextSettings);


 /* Render the picture (choose the best way) */
 $myPicture->autoOutput("radar.png");
}
?>