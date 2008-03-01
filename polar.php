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
	    global $plotMarkNum, $totalNumValues;
	    $plotMarkNum++;
	    $idx=100*$plotMarkNum/$totalNumValues;
 	    return array(3,"",rainbowColor($idx));
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
	$result = mysql_query($query) or die ("oneValue Abfrage fehlgeschlagen<br>Query:<font color=red>$query</font><br>Error:" . mysql_error());
	$num = mysql_num_rows($result);

	$width  = 400;
	$height = 425;
	$margin = 40;

	$graph = new PolarGraph($width, $height);
	$graph->SetScale('lin');
	$graph->SetMargin($margin, $margin, $margin, $margin);

	
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
	
	$graph->title->Set($title);
		
	 
	$data = array();
	
	$idx = 0;
	$i   = 0;

	while($row = mysql_fetch_array($result, MYSQL_ASSOC))
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

	mysql_free_result($result);
	mysql_close();


	$p = new PolarPlot($data);
	$p->mark->SetType(MARK_FILLEDCIRCLE);
	$p->SetWeight(0);
	$p->mark->SetCallback("PlaceMarkCallback");
	

	// Add Directions

	$tNorth = new Text("N");
	$tNorth->Center(0,$width, $margin - 15);
	$tNorth->SetFont(FF_FONT2, FS_BOLD, 12);
	$tNorth->SetColor('red');
	$graph->Add($tNorth);

	$tEast = new Text($text['east_char']);
	$tEast->Center($width-$margin,$width - $margin/2, $height/2 - $margin/4);
	$tEast->SetFont(FF_FONT2, FS_BOLD, 12);
	$graph->Add($tEast);

	$tSouth = new Text("S");
	$tSouth->Center(0,$width, $height - $margin);
	$tSouth->SetFont(FF_FONT2, FS_BOLD, 12);
	$tSouth->SetColor('green');
	$graph->Add($tSouth);

	$tWest = new Text("W");
	$tWest->Center($margin/2,$margin, $height/2 - $margin/4);
	$tWest->SetFont(FF_FONT2, FS_BOLD, 12);
	$graph->Add($tWest);

	$graph->axis->ShowAngleLabel(false);
	$graph->SetMarginColor('gray');
	$graph->Add($p);
	$graph->SetShadow();
	$graph->Stroke();
?>
