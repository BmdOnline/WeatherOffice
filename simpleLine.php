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
	    global $plotMarkNum, $totalNumValues;
	    $plotMarkNum++;
	    $idx=100*$plotMarkNum/$totalNumValues;

 	    return array(3,"",rainbowColor($idx));

	} 

	$begin =  $_REQUEST["begin"];
	$end =    $_REQUEST["end"];
	$col =   $_REQUEST["col"];
	$titleStr =   $_REQUEST["title"];
	$unit =  $_REQUEST["unit"];
	$type = $_REQUEST["type"];
	
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

	$result = mysql_query($query) or die ("oneValue Abfrage fehlgeschlagen<br>Query:<font color=red>$query</font><br>Error:" . mysql_error());
	$num = mysql_num_rows($result);

	$graph = new Graph(900, 300);
	$graph->SetMargin(50,10,10,90);
	$graph->SetScale("datlin");
	
	$factor=(integer) ($num/500); // 500 Werte maximal
	if($factor == 0)
		$factor = 1;
		
	switch($type)
	{
		case "day":
			$title = $titleStr . " " . $text['at'] . " " . $day . "." . $month . "." . $year;
			$graph ->xaxis->scale-> SetDateFormat( 'H:i');
			break;

		case "24":
			$title = "24h " . $titleStr . " " . $text['at'] . " " . $day . "." . $month . "." . $year;
			$graph ->xaxis->scale-> SetDateFormat( 'H:i');
			break;

		case "week":
			$title = $titleStr . " " . $text['in_the_week_from'] . " " . $day . "." . $month . "." . $year;
			$graph ->xaxis->scale-> SetDateFormat( 'd.m. H:i');
			break;
			
		case "month":
			$title = $titleStr . " " .  monthName($month, $text) . " " . $year;
			$graph ->xaxis->scale-> SetDateFormat( 'd.m. H:i');
			break;
			
		case "free":
			$title = $titleStr . " " . $text['in_the_period_from'] . " " . $day . "." . $month . "." . $year;
			$graph ->xaxis->scale-> SetDateFormat( 'd.m. H:i');
			break;
			
		default:
			$title = $titleStr;
			$graph ->xaxis->scale-> SetDateFormat( 'H:i');
	}
	
	$graph->title->Set($title);
	$graph->yaxis->SetColor("blue");
	$graph->yaxis->title->Set($unit);
	
	$graph->xaxis->SetLabelAngle(90);
	$graph->xaxis->SetPos('min');
	
	$xdata = array();
	$ydata = array();
	$start = time();
	
	$idx = 0;
	$i   = 0;
	while($row = mysql_fetch_array($result, MYSQL_ASSOC))
	{
		if(($i % $factor) == 0)
		{
			if($addSensor == true)
			{
				$ts = $row["timestamp"];
				$recTime = substr($ts, 8,2).":".substr($ts,10,2).":".substr($ts,12,2);
				$recDate  = substr($ts, 0, 4)."-".substr($ts,4,2)."-".substr($ts,6,2);
				touch("/tmp/recTime_$recTime");
				touch("/tmp/recDate_$recDate");
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

	mysql_free_result($result);
	mysql_close();
	
	if($col == "wind_angle" || $col == "windspeed")
	{
	  $scatplot= new ScatterPlot($ydata, $xdata);	  
	  $scatplot->SetColor("blue");
  	  $scatplot->mark->SetCallback("PlaceMarkCallback");
   	  $scatplot->mark->SetType(MARK_FILLEDCIRCLE);
	  
	  if($col == "windspeed")
	     $scatplot->SetImpuls();
	  $graph->Add($scatplot);
	}
	else
	{
	  $lineplot=new LinePlot($ydata, $xdata);	
	  $lineplot->SetColor("blue");
	  $lineplot->SetWeight($LineThickness);
	  $graph->Add($lineplot);
	}
	
	$graph->SetShadow();
	$graph->Stroke();

?>
