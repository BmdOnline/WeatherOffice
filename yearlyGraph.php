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


	$graph = new Graph($GraphWidth, $GraphHeight, "auto",  86400);
	$graph->SetMargin(50,50,10,90);
	$graph->SetScale("datlin");
	$graph->SetY2Scale( "lin");
	$graph->SetMarginColor($MarginColor);
	$graph->SetFrame(true,$FrameColor,1);

	$title = "{$text['yearly_overview_graph']} $dispyear";
	$graph->xaxis->scale->SetDateFormat('d.m');

	$graph->title->Set(encodeStringForGraph($title));
	$graph->title->SetColor($LegendColor);

	$graph->yaxis->SetColor($YAxisColors[0]);
	$graph->yaxis->title->SetMargin(0);
	$graph->yaxis->title->SetColor($LegendColor);
	$graph->yaxis->title->Set(encodeStringForGraph("°C"));
	$graph->y2axis->SetColor($YAxisColors[1]);
	$graph->y2axis->title->SetMargin(10);
	$graph->y2axis->title->SetColor($LegendColor);
	$graph->y2axis->title->Set(encodeStringForGraph("mm"));
	$graph->SetTickDensity(TICKD_SPARSE);
	$graph->xaxis->SetLabelAngle(90);
	$graph->xaxis->SetPos('min');

	$graph->legend->SetColor($LegendColor);
	$graph->legend->SetFillColor($LegendFillColor);
	$graph->legend->SetPos( 0.03,0.95,"right" ,"bottom");
	$graph->legend->SetColumns(4);

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
	$graph->Add($lineplot1);
	$lineplot1->SetColor($YearlyColors[0]);
	if ($YearlyFillColors[0]) $lineplot1->SetFillColor($YearlyFillColors[0]);
	$lineplot1->SetWeight($LineThickness);
	$lineplot1->SetLegend(encodeStringForGraph($text['min'] . " " . $text['temp']));

	$lineplot2=new LinePlot($ydata2, $xdata);
	$graph->Add($lineplot2);
	$lineplot2->SetColor($YearlyColors[1]);
	if ($YearlyFillColors[1]) $lineplot2->SetFillColor($YearlyFillColors[1]);
	$lineplot2->SetWeight($LineThickness);
	$lineplot2->SetLegend(encodeStringForGraph($text['avg'] . " " . $text['temp']));

	$lineplot3=new LinePlot($ydata3, $xdata);
	$graph->Add($lineplot3);
	$lineplot3->SetColor($YearlyColors[2]);
	if ($YearlyFillColors[2]) $lineplot3->SetFillColor($YearlyFillColors[2]);
	$lineplot3->SetWeight($LineThickness);
	$lineplot3->SetLegend(encodeStringForGraph($text['max'] . " " . $text['temp']));

	$lineplot4=new LinePlot($ydata4, $xdata);
	$graph->AddY2($lineplot4);
	$lineplot4->SetColor($YearlyColors[3]);
	if ($YearlyFillColors[3]) $lineplot4->SetFillColor($YearlyFillColors[3]);
	$lineplot4->SetWeight($LineThickness);
	$lineplot4->SetLegend(encodeStringForGraph($text['precipitation']));

	$graph->SetShadow();
	$graph->Stroke();
?>
