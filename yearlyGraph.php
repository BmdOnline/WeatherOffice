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
	
	$dispyear =  $_REQUEST["year"];
	

	$graph = new Graph(800, 400, "auto",  86400);
	$graph->SetMargin(50,150,10,90);
	$graph->SetScale("datlin");
	$graph->SetY2Scale( "lin");
		
	$title = "{$text['yearly_overview_graph']} $dispyear";
	$graph ->xaxis->scale-> SetDateFormat( 'd.m.');
	
	$graph->title->Set($title);
	
	$graph->yaxis->SetColor("green");
	$graph->yaxis->title->Set("°C");
	$graph->y2axis->title->Set("mm");
	$graph->y2axis->SetColor("blue");
	$graph->xaxis->SetLabelAngle(90);
	$graph->xaxis->SetPos('min');
	
	$graph ->legend->Pos( 0.03,0.2,"right" ,"center");
	 
	$xdata = array();
	$ydata1 = array();
	$ydata2 = array();
	$ydata3 = array();	
	$ydata4 = array();
	
	/*** START DATA QUERY */
	
	$nextDay = getdate(strtotime("+ 0 sec", mktime(0, 0, 0, 1, 1, $dispyear)));
	$day   = $nextDay['mday'];
	$month = $nextDay['mon'];
	$year  = $nextDay['year'];
	
	$idx = 0;
		
	while($dispyear == $year )
	{
		$stat=MinMaxAvg::getStatArray('DAY', $year, $month, $day);
	   
	  if ($stat)
	  {
			$xdata[$idx] =  mktime(0, 0, 0, $nextDay['mon'], $nextDay['mday'], $nextDay['year']);
			$ydata1[$idx] = $stat["temp_out"]["min"];
			$ydata2[$idx] = $stat["temp_out"]["avg"];     
			$ydata3[$idx] = $stat["temp_out"]["max"];
			$ydata4[$idx] = $stat["rain_total"]['max']; // - $stat["rain_total"]['min'];
	     
      $idx++;
    }
	
	  $nextDay = getdate(strtotime("+1 day", mktime(0, 0, 0, $nextDay['mon'], $nextDay['mday'], $nextDay['year'])));
	  $day   = $nextDay['mday'];
		$month = $nextDay['mon'];
	  $year  = $nextDay['year'];
	   
	}
	
	$lineplot1=new LinePlot($ydata1, $xdata);
	$lineplot1->SetColor("yellow");
	$lineplot1->SetWeight($LineThickness);
	$lineplot1->SetLegend($text['min'] . " " . $text['temp']);
	$graph->Add($lineplot1);
	
	$lineplot2=new LinePlot($ydata2, $xdata);
	$lineplot2->SetColor("green");
	$lineplot2->SetWeight($LineThickness);
	$lineplot2->SetLegend($text['avg'] . " " . $text['temp']);
	$graph->Add($lineplot2);
	
	$lineplot3=new LinePlot($ydata3, $xdata);
	$lineplot3->SetColor("red");
	$lineplot3->SetWeight($LineThickness);
	$lineplot3->SetLegend($text['max'] . " " . $text['temp']);
	$graph->Add($lineplot3);
	
	$lineplot4=new LinePlot($ydata4, $xdata);
	$lineplot4->SetColor("blue");
	$lineplot4->SetWeight($LineThickness);
	$lineplot4->SetLegend($text['precipitation']);
	$graph->AddY2($lineplot4);	

	$graph->SetShadow();
	$graph->Stroke();
?>
