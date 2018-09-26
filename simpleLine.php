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
// English Translation Steve Chamberlin
//
// See COPYING for license info
//
////////////////////////////////////////////////////
	include("jpgraphSetup.php");
	include("jpgraph_scatter.php");


	$totalNumValues = 0;
	$plotMarkNum = 0;

	function PlaceMarkCallback($aVal) {
	    global $PlotThickness, $LineColors;
	    global $plotMarkNum, $totalNumValues;
	    $plotMarkNum++;
	    $idx=100*$plotMarkNum/$totalNumValues;

 	    return array($PlotThickness,$LineColors[0],rainbowColor($idx));

	}

	$begin =  $_REQUEST["begin"];
	$end =    $_REQUEST["end"];
	$col =   $_REQUEST["col"];
	$titleStr =   $_REQUEST["title"];
	$unit =  $_REQUEST["unit"];
	$type = $_REQUEST["type"];

	if (ISSET($_REQUEST["gradient"]))
		$gradient=$_REQUEST["gradient"];
	else
		$gradient=0;

	$day     = substr($begin, 6, 2);
	$month = substr($begin, 4, 2);

	$year    = substr($begin, 0, 4);

	$addSensor = false;
	if(substr($col, 0, 2) == "as")
	{
		$addSensor = true;
		$addSensorNum = substr($col, 2, 1); // Hack: bis stringende abfragen
		$query = "select value,timestamp from additionalvalues where timestamp >= $begin and timestamp <= $end and id = $addSensorNum order by timestamp";
	}
	else
	{
		$query = "select $col, rec_time, rec_date from weather where timestamp >= $begin and timestamp <= $end order by timestamp";
	}

	$result = $link->query($query);
	if (!$result) {
		printf("Query Failed.<br>Query:<font color=red>$query</font><br>Error: %s\n", $link->error);
		exit();
	}
	$num = $result->num_rows;

	$graph = new Graph(850, 400);
	$graph->SetMargin(50,50,10,90);
	$graph->SetMarginColor($MarginColor);
	$graph->SetScale("datlin");

	$factor=(integer) ($num/500); // 500 Werte maximal
	if($factor == 0)
		$factor = 1;

	switch($type)
	{
		case "day":
			$title = $titleStr . " " . $text['at'] . " " . $day . "." . $month . "." . $year;
			$graph->xaxis->scale->SetDateFormat('H:i');
			// Force labels to only be displayed every 60 minutes
			$graph->xaxis->scale->ticks->Set(60*60);
			$graph->xaxis->SetLabelAngle(90);
			break;

		case "24":
			$title = "24h " . $titleStr . " " . $text['at'] . " " . $day . "." . $month . "." . $year;
			$graph->xaxis->scale->SetDateFormat('H:i');
			// Force labels to only be displayed every 60 minutes
			$graph->xaxis->scale->ticks->Set(60*60);
			$graph->xaxis->SetLabelAngle(90);
			break;

		case "week":
			$title = $titleStr . " " . $text['in_the_week_from'] . " " . $day . "." . $month . "." . $year;
			$graph ->xaxis->scale-> SetDateFormat( "d.m\nH:i");
			// Force labels to only be displayed every 12 hours
			$graph->xaxis->scale->ticks->Set(12*60*60);
			$graph->xaxis->SetLabelAngle(0);
			break;

		case "month":
			$title = $titleStr . " " .  monthName($month, $text) . " " . $year;
			$graph ->xaxis->scale-> SetDateFormat("d.m\nH:i");
			// Force labels to only be displayed every 2 days
			$graph->xaxis->scale->ticks->Set(2*24*60*60);
			$graph->xaxis->SetLabelAngle(0);
			break;

		case "free":
			$title = $titleStr . " " . $text['in_the_period_from'] . " " . $day . "." . $month . "." . $year;
			$graph->xaxis->scale->SetDateFormat("d.m H:i");
			// Force labels to only be displayed every 2 days
			//$graph->xaxis->scale->ticks->Set(2*24*60*60);
			$graph->xaxis->SetLabelAngle(90);
			break;

		default:
			$title = $titleStr;
			$graph->xaxis->scale->SetDateFormat('H:i');
			$graph->xaxis->SetLabelAngle(90);
	}

	$graph->title->Set(encodeStringForGraph($title));
	$graph->yaxis->SetColor($YAxisColors[0]);
	$graph->yaxis->title->Set(encodeStringForGraph($unit));
	$graph->SetTickDensity(TICKD_SPARSE);

	$graph->xaxis->SetPos('min');

	$xdata = array();
	$ydata = array();
	$start = time();

	$idx = 0;
	$i   = 0;

	$lastValue = -1;

	while($row = $result->fetch_assoc())
	{
		if(($i % $factor) == 0)
		{
			if($addSensor == true)
			{
				$ts = $row["timestamp"];
				$recTime = substr($ts, 8,2).":".substr($ts,10,2).":".substr($ts,12,2);
				$recDate  = substr($ts, 0, 4)."-".substr($ts,4,2)."-".substr($ts,6,2);
			}
			else
			{
				$recTime = $row["rec_time"];
				$recDate  = $row["rec_date"];
			}

			$xdata[$idx] = mktime(substr($recTime, 0, 2), substr($recTime, 3, 2), substr($recTime, 6, 2),
				substr($recDate, 5, 2), substr($recDate, 8, 2), substr($recDate, 0, 4));

			if($addSensor == true)
			{
				$ydata[$idx] = $row["value"];
			}
			else if($gradient == 1)
			{
				if($i == 0)
					$ydata[$idx] = 0;
				else
					$ydata[$idx] = $row[$col] - $lastValue;

				$lastValue = $row[$col];
			}
			else
			{
				$ydata[$idx] = $row[$col];
			}
			// convert windspeed from m/s to km/h
			if($col == "windspeed")
				$ydata[$idx] *= 3.6;

			$idx++;
		}
		$i++;
	}

	$totalNumValues = $idx;

    $result->free();
    $link->close();

	if($col == "wind_angle" || $col == "windspeed")
	{
	  if($col == "windspeed")	  
		$scatplot= new LinePlot($ydata, $xdata);
	  else
		$scatplot= new ScatterPlot($ydata, $xdata);
	  $graph->Add($scatplot);
	  $scatplot->SetColor($LineColors[0]);
	  if ($LineFillColors[0]) $scatplot->SetFillColor($LineFillColors[0]);
  	  $scatplot->mark->SetCallback("PlaceMarkCallback");
   	  $scatplot->mark->SetType(MARK_FILLEDCIRCLE);
	}
	else
	{
	  $lineplot=new LinePlot($ydata, $xdata);
	  $graph->Add($lineplot);
	  $lineplot->SetColor($LineColors[0]);
	  if ($LineFillColors[0]) $lineplot->SetFillColor($LineFillColors[0]);
	  $lineplot->SetWeight($LineThickness);
	}

	$graph->SetShadow();
	$graph->Stroke();

?>
