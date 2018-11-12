<?php
////////////////////////////////////////////////////
//
// WeatherOffice
//
// http://www.sourceforge.net/projects/weatheroffice
//
// Copyright (C) 04/2007 Mathias Zuckermann &
//			 Bernhard Heibler
//
// See COPYING for license info
//
////////////////////////////////////////////////////
	include("jpgraphSetup.php");
	include("class.MinMaxAvg.php");

	if(array_key_exists("col",$_REQUEST))
	   $col = $_REQUEST["col"];
	else
	   $col = "temp_out";

	if(array_key_exists("avg",$_REQUEST))
	   $numAvg = $_REQUEST["avg"];
	else
	   $numAvg = 10;

	if(array_key_exists("title",$_REQUEST))
	   $title = $_REQUEST["title"];
	else
	   $title = $text['avg_temp'];

	if(array_key_exists("unit",$_REQUEST))
	   $unit = $_REQUEST["unit"];
	else
	   $unit = "°C";

	$graph = new Graph($GraphWidth, $GraphHeight, "auto", 86400);

	$timestamp = $database->getMinMaxAvgLastDate('DAY');
	$maxYear    = substr($timestamp, 0, 4);

	$graph->SetMargin(50,100,10,90);
	$graph->SetScale("datlin");
	//$graph->SetY2Scale( "lin");
	$graph->SetMarginColor($MarginColor);
	$graph->SetFrame(true,$FrameColor,1);

	$graph->xaxis->scale->SetDateFormat('d.m');

	$graph->title->Set(encodeStringForGraph($title));

	$graph->yaxis->SetColor($YAxisColors[0]);
	$graph->yaxis->title->Set(encodeStringForGraph($unit));
	//$graph->y2axis->title->Set(encodeStringForGraph("mm"));
	//$graph->y2axis->SetColor($YAxisColors[1]);
	$graph->xaxis->SetLabelAngle(90);
	$graph->xaxis->SetPos('min');
	$graph->xaxis->SetColor($XAxisColors);

	$graph->legend->SetPos( 0.03,0.4,"right" ,"center");
	$graph->legend->SetColumns(1);

	$xdata = array();
	$ydata = array();
	$ydata1 = array();
	$ydata2 = array();
	$ydata3 = array();
	$ydata4 = array();



	/*** START DATA QUERY */

	getStartYearAndMonth($firstYear, $firstMonth, $firstDay);

	$nextDay = getdate(strtotime("+ 0 sec", mktime(0, 0, 0, 1, $firstMonth, $firstYear)));
	$day   = $nextDay['mday'];
	$month = $nextDay['mon'];
	$year  = $nextDay['year'];

	$idx  = 0;
	$idx2 = 0;
	$avg = 0;
	$yearNum = -1;
	$lastYear = 0;

	while($year <= $maxYear+1)
	{
	   if($year != $lastYear)
	   {
		$yearNum++;
		$ydata[$yearNum] = array();
		$xdata[$yearNum] = array();
		$idx2 = 0;
		$lastYear = $year;
	   }


	   $begin = convertTimestamp($day, $month, $year, 0, 0, 0);
   	   $end   = convertTimestamp($day, $month, $year, 23, 59, 59);

	   $stat=MinMaxAvg::getStatArray('DAY', $year, $month, $day);
	   if ($stat) {
	      if($col == "rain_total")
		     $ydata2[$idx] = $stat["rain_total"]["max"];// - $stat["rain_total"]["min"];
	       else
	     	 $ydata2[$idx] = $stat[$col]["avg"];

 	     $xdata[$yearNum][$idx2] =  mktime(0, 0, 0, $nextDay['mon'], $nextDay['mday'], 0);

	     if($idx < $numAvg)
	     {
		  $avg += $ydata2[$idx];
		  $ydata[$yearNum][$idx2] =  $avg / ($idx+2);
	     }
	     else
	     {
              $ydata[$yearNum][$idx2] =  $avg / $numAvg;

	   	  $avg += $ydata2[$idx];
		  $avg -= $ydata2[$idx - $numAvg];
	     }

	     $idx++;
	     $idx2++;
	   }

	   $nextDay = getdate(strtotime("+1 day", mktime(0, 0, 0, $nextDay['mon'], $nextDay['mday'], $nextDay['year'])));
	   $day   = $nextDay['mday'];
   	   $month = $nextDay['mon'];
	   $year  = $nextDay['year'];
	}

	$num = 0;
	$lineplot = array();


	$gColors = array();
	$gColors[0] = "maroon";
	$gColors[1] = "red";
	$gColors[2] = "purple";
	$gColors[3] = "magenta";
	$gColors[4] = "green";
	$gColors[5] = "paleturquoise";
	$gColors[6] = "olivedrab";
	$gColors[7] = "yellow";
	$gColors[8] = "gold";
	$gColors[9] = "blue";
	$gColors[10] = "teal";
	$gColors[11] = "aqua";
	$gColors[12] = "orange";

	while($num < $yearNum)
	{
		$lineplot[$num]=new LinePlot($ydata[$num], $xdata[$num]);
		$graph->Add($lineplot[$num]);
		$lineplot[$num]->SetColor($gColors[$num % sizeof($gColors)]);
		$lineplot[$num]->SetWeight($LineThickness);
		$lineplot[$num]->SetLegend(encodeStringForGraph($firstYear + $num));
		$num++;
	}

	$graph->SetShadow();
	$graph->Stroke();
?>

