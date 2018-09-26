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
	
include ("jpgraphSetup.php");
	
	function getLegend($col)
	{
		global $text;
		
		switch($col)
        	{
			case 'temp_out':
				return( $text['outside_temperature'] );
				break;

			case 'dewpoint':
				return( $text['dewpoint'] );
				break;

			case 'temp_in':
				return( $text['inside_temperature']);
				break;
				
			case 'wind_chill':
				return( $text['windchill'] );
				break;

			case 'rel_hum_in':
				return( $text['inside_humidity'] );
				break;

			case 'rel_hum_out':
				return( $text['outside_humidity'] );
				break;
				

			default:
				return( $text['undefined'] );
	        }
	}

	$begin =  $_REQUEST["begin"];
	$end =    $_REQUEST["end"];
	$col1 =   $_REQUEST["col1"];
	$col2 =   $_REQUEST["col2"];
	$col3 =   $_REQUEST["col3"];
	$titleStr =   $_REQUEST["title"];
	$unit1 =  $_REQUEST["unit1"];
	$unit2 =  $_REQUEST["unit2"];
	$unit3 =  $_REQUEST["unit3"];
	$type = $_REQUEST["type"];
	
	$day     = substr($begin, 6, 2);
	$month = substr($begin, 4, 2);
	$year    = substr($begin, 0, 4);

	$query = "select $col1, $col2, $col3, rec_time, rec_date from weather where timestamp >= $begin and timestamp <= $end order by timestamp";
	$result = $link->query($query);
	if (!$result) {
		printf("Query Failed.<br>Query:<font color=red>$query</font><br>Error: %s\n", $link->error);
		exit();
	}
	$num = $result->num_rows;

	$graph = new Graph(1080, 500);
	$graph->SetMargin(50,190,10,90);
	$graph->SetScale("datlin");
	$graph->SetY2Scale( "lin");
	
	$factor=(integer) ($num/500); // 500 Werte maximal
	if($factor == 0)
		$factor = 1;
		
	switch($type)
	{
		case "day":
			$title = $titleStr . " " . $text['at'] . " " . $day . "." . $month . "." . $year;
			$graph->xaxis->scale->SetDateFormat( 'H:i');
			break;
			
		case "24":
			$title = "24h " . $titleStr . " " . $text['at'] . " " . $day . "." . $month . "." . $year;
			$graph->xaxis->scale->SetDateFormat( 'H:i');
			break;

		case "week":
			$title = $titleStr . " " . $text['in_the_week_from'] . " " . $day . "." . $month . "." . $year;
			$graph->xaxis->scale->SetDateFormat( 'd.m. H:i');
			break;
			
		case "month":
			$title = $titleStr . " " .  monthName($month, $text) . " " . $year;
			$graph->xaxis->scale->SetDateFormat( 'd.m. H:i');
			break;
			
		case "free":
			$title = $titleStr . " " . $text['in_the_period_from'] . " " . $day . "." . $month . "." . $year;
			$graph->xaxis->scale->SetDateFormat( 'd.m. H:i');
			break;
			
		default:
			$title = $titleStr;
			$graph->xaxis->scale->SetDateFormat( 'H:i');
	}
	
	$graph->title->Set(encodeStringForGraph($title));
	
	$graph->yaxis->SetColor("blue");
	$graph->yaxis->title->Set(encodeStringForGraph($unit1));
	$graph->y2axis->title->Set(encodeStringForGraph($unit3));
	$graph->y2axis->SetColor("red");
	$graph->xaxis->SetLabelAngle(90);
	$graph->xaxis->SetPos('min');
	
	$graph->legend->Pos( 0.03,0.2,"right" ,"center");
	$graph->legend->SetColumns(1);
	 
	$xdata = array();
	$ydata = array();
	$start = time();
	
	$idx = 0;
	$i   = 0;
	while($row = $result->fetch_assoc())
	{
		if(($i % $factor) == 0)
		{
			$recTime = $row["rec_time"];
			$recDate = $row["rec_date"];
		
			$xdata[$idx] = mktime(substr($recTime, 0, 2), substr($recTime, 3, 2), substr($recTime, 6, 2),
				substr($recDate, 5, 2), substr($recDate, 8, 2), substr($recDate, 0, 4));
			$ydata1[$idx] = $row[$col1];
			$ydata2[$idx] = $row[$col2];
			$ydata3[$idx] = $row[$col3];
			$idx++;
		}
		$i++;
	}
	
	$result->free();
	$link->close();
	
	$lineplot1=new LinePlot($ydata1, $xdata);
	$graph->Add($lineplot1);
	$lineplot1->SetColor("blue");
	$lineplot1->SetWeight($LineThickness);
	$lineplot1->SetLegend(encodeStringForGraph(getLegend($col1)));
	
	$lineplot2=new LinePlot($ydata2, $xdata);
	$graph->Add($lineplot2);
	$lineplot2->SetColor("green");
	$lineplot2->SetWeight($LineThickness);
	$lineplot2->SetLegend(encodeStringForGraph(getLegend($col2)));
	
	$lineplot3=new LinePlot($ydata3, $xdata);
	$graph->AddY2($lineplot3);
	$lineplot3->SetColor("red");
	$lineplot3->SetWeight($LineThickness);
	$lineplot3->SetLegend(encodeStringForGraph(getLegend($col3)));
	
	$graph->SetShadow();
	$graph->Stroke();
?>
