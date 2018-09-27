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
	$col1 =   $_REQUEST["col1"];
	$col2 =   $_REQUEST["col2"];
	$titleStr =   $_REQUEST["title"];
	$unit =   $_REQUEST["unit"];
	$type =   $_REQUEST["type"];

	$day     = substr($begin, 6, 2);
	$month   = substr($begin, 4, 2);
	$year    = substr($begin, 0, 4);

	$query = "select $col1, $col2, rec_time, rec_date from weather where timestamp >= $begin and timestamp <= $end";
	$result = $link->query($query);
	if (!$result) {
		printf("Query Failed.<br>Query:<font color=red>$query</font><br>Error: %s\n", $link->error);
		exit();
	}
	$num = $result->num_rows;

	$margin = 40;

	$graph = new PolarGraph($PolarWidth, $PolarHeight);
	$graph->SetMargin($margin, $margin, $margin, $margin);
	$graph->SetScale('lin');
	$graph->SetMarginColor($MarginColor);
	$graph->SetFrame(true,$FrameColor,1);
	$graph->SetBox(false);
	$factor=(integer) ($num/500); // 500 Werte maximal
	if($factor == 0)
		$factor = 1;

	switch($type)
	{
		case "day":
			$title = $titleStr . " " . $text['at'] . " " . $day . "." . $month . "." . $year;
			break;

		case "24":
			$title = "24h " . $titleStr . " " . $text['at'] . " " . $day . "." . $month . "." . $year;
			break;

		case "week":
			$title = $titleStr . " " . $text['in_the_week_from'] . " " . $day . "." . $month . "." . $year;
			break;

		case "month":
			$title = $titleStr . " " .  monthName($month, $text) . " " . $year;
			break;

		case "free":
			$title = $titleStr . " " . $text['in_the_period_from'] . " " . $day . "." . $month . "." . $year;
			break;

		default:
			$title = $titleStr;

	}

	$graph->title->Set(encodeStringForGraph($title));
	$graph->title->SetColor($LegendColor);


	$data = array();

	$idx = 0;
	$i   = 0;

	while($row = $result->fetch_assoc())
	{
		if(($i % $factor) == 0)
		{
			$recTime = $row["rec_time"];
			$recDate = $row["rec_date"];

			// we rotate the graph since we want north up
			$data[$idx] = 90 - $row[$col1] ;
			$idx++;
			$data[$idx] = $row[$col2];

			if($col2 == "windspeed")
			  $data[$idx] *= 3.6;

			$idx++;
		}
		$i++;
	}

	$totalNumValues = $idx/2;

	$result->free();
	$link->close();


	$p = new PolarPlot($data);
	$graph->Add($p);
	$p->mark->SetType(MARK_FILLEDCIRCLE);
	$p->SetWeight(0);
	$p->mark->SetCallback("PlaceMarkCallback");


	// Add Directions

	$tNorth = new Text(encodeStringForGraph($text['north_char']));
	$graph->Add($tNorth);
	$tNorth->Center(0,$PolarWidth, $margin - 15);
	$tNorth->SetFont(FF_FONT2, FS_BOLD, 12);
	$tNorth->SetColor($PolarDirColors[0]);

	$tEast = new Text(encodeStringForGraph($text['east_char']));
	$graph->Add($tEast);
	$tEast->Center($PolarWidth-$margin,$PolarWidth - $margin/2, $PolarHeight/2 - $margin/4);
	$tEast->SetFont(FF_FONT2, FS_BOLD, 12);
	$tEast->SetColor($PolarDirColors[1]);

	$tSouth = new Text(encodeStringForGraph($text['south_char']));
	$graph->Add($tSouth);
	$tSouth->Center(0,$PolarWidth, $PolarHeight - $margin);
	$tSouth->SetFont(FF_FONT2, FS_BOLD, 12);
	$tSouth->SetColor($PolarDirColors[2]);

	$tWest = new Text(encodeStringForGraph($text['west_char']));
	$graph->Add($tWest);
	$tWest->Center($margin/2,$margin, $PolarHeight/2 - $margin/4);
	$tWest->SetFont(FF_FONT2, FS_BOLD, 12);
	$tWest->SetColor($PolarDirColors[3]);

	$graph->axis->ShowAngleLabel(false);
	$graph->SetShadow();
	$graph->Stroke();
?>
