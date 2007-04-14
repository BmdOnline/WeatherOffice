<?php
////////////////////////////////////////////////////
//
// WeatherOffice
//
// http://www.sourceforge.net/projects/weatheroffice
//
////////////////////////////////////////////////////
	include("jpgraphSetup.php");
	
	$begin =  $_REQUEST["begin"];
	$end =    $_REQUEST["end"];
	$col =   $_REQUEST["col"];
	$titleStr =   $_REQUEST["title"];
	$unit =  $_REQUEST["unit"];
	$type = $_REQUEST["type"];
	
	$day     = substr($begin, 6, 2);
	$month = substr($begin, 4, 2);
	
	$year    = substr($begin, 0, 4);

	$query = "select $col, rec_time, rec_date from weather where timestamp >= $begin and timestamp <= $end order by timestamp";
	$result = mysql_query($query) or die ("oneValue Abfrage fehlgeschlagen<br>Query:<font color=red>$query</font><br>Error:" . mysql_error());
	$num = mysql_num_rows($result);

	$graph = new Graph(900, 300, "auto");
	$graph->SetMargin(50,10,10,90);
	$graph->SetScale("datlin");
	
	$factor=(integer) ($num/500); // 500 Werte maximal
	if($factor == 0)
		$factor = 1;
		
	switch($type)
	{
		case "day":
			$title = $titleStr . " am " . $day . "." . $month . "." . $year;
			$graph ->xaxis->scale-> SetDateFormat( 'H:i');
			break;

		case "24":
			$title = "24h " . $titleStr . " am " . $day . "." . $month . "." . $year;
			$graph ->xaxis->scale-> SetDateFormat( 'H:i');
			break;

		case "week":
			$title = $titleStr . " in der Woche vom " . $day . "." . $month . "." . $year;
			$graph ->xaxis->scale-> SetDateFormat( 'd.m. H:i');
			break;
			
		case "month":
			$title = $titleStr . " " .  monthName($month, $text) . " " . $year;
			$graph ->xaxis->scale-> SetDateFormat( 'd.m. H:i');
			break;
			
		case "free":
			$title = $titleStr . " im Zeitraum vom " . $day . "." . $month . "." . $year;
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
			$recTime = $row["rec_time"];
			$recDate  = $row["rec_date"];
		
			$xdata[$idx] = mktime(substr($recTime, 0, 2), substr($recTime, 3, 2), substr($recTime, 6, 2),
				substr($recDate, 5, 2), substr($recDate, 8, 2), substr($recDate, 0, 4));
			$ydata[$idx] = $row[$col];
		
			// convert windspeed from m/s to km/h
			if($col == "windspeed")
				$ydata[$idx] *= 3.6;
			
			$idx++;
		}
		$i++;
	}
	
	mysql_free_result($result);
	mysql_close();
	
	$lineplot=new LinePlot($ydata, $xdata);
	$lineplot->SetColor("blue");
	$lineplot->SetWeight(2);
	$graph->Add($lineplot);
	$graph->SetShadow();
	$graph->Stroke();
?>
