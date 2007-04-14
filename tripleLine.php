<?php
////////////////////////////////////////////////////
//
// WeatherOffice
//
// http://www.sourceforge.net/projects/weatheroffice
//
////////////////////////////////////////////////////
	
include ("jpgraphSetup.php");
	
	function getLegend($col)
	{
		switch($col)
        	{
			case 'temp_out':
				return("Aussentemperatur");
				break;

			case 'dewpoint':
				return("Taupunkt");
				break;

			case 'temp_in':
				return("Innentemperatur");
				break;

			default:
				return("Unbekannt");
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
	$result = mysql_query($query) or die ("oneValue Abfrage fehlgeschlagen<br>Query:<font color=red>$query</font><br>Error:" . mysql_error());
	$num = mysql_num_rows($result);

	$graph = new Graph(1080, 500, "auto");
	$graph->SetMargin(50,190,10,90);
	$graph->SetScale("datlin");
	$graph->SetY2Scale( "lin");
	
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
	$graph->yaxis->title->Set($unit1);
	$graph->y2axis->title->Set($unit3);
	$graph->y2axis->SetColor("red");
	$graph->xaxis->SetLabelAngle(90);
	$graph->xaxis->SetPos('min');
	
	$graph ->legend->Pos( 0.03,0.2,"right" ,"center");
	 
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
	
	mysql_free_result($result);
	mysql_close();
	
	$lineplot1=new LinePlot($ydata1, $xdata);
	$lineplot1->SetColor("blue");
	$lineplot1->SetWeight(2);
	$lineplot1->SetLegend(getLegend($col1));
	$graph->Add($lineplot1);
	
	$lineplot2=new LinePlot($ydata2, $xdata);
	$lineplot2->SetColor("green");
	$lineplot2->SetWeight(2);
	$lineplot2->SetLegend(getLegend($col2));

	$graph->Add($lineplot2);
	
	$lineplot3=new LinePlot($ydata3, $xdata);
	$lineplot3->SetColor("red");
	$lineplot3->SetWeight(2);
	$lineplot3->SetLegend("Luftfeuchte");
	$graph->AddY2($lineplot3);
	
	$graph->SetShadow();
	$graph->Stroke();
?>
