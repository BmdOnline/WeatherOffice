<?php
////////////////////////////////////////////////////
//
// WeatherOffice
//
// http://www.sourceforge.net/projects/weatheroffice
//
////////////////////////////////////////////////////
	include("jpgraphSetup.php");
	
	$dispyear =  $_REQUEST["year"];
	

	$graph = new Graph(800, 400, "auto");
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
	
	   $begin = convertTimestamp($day, $month, $year, 0, 0, 0);
   	   $end   = convertTimestamp($day, $month, $year, 23, 59, 59);
	
	   $query = "select * from weather where timestamp >= $begin and timestamp <= $end order by timestamp";
 	   $result = mysql_query($query) or die ("oneValue Abfrage fehlgeschlagen<br>Query:<font color=red>$query</font><br>Error:" . mysql_error());
 	   $num = mysql_num_rows($result);
	   
	   if ($num > 0)
	   {
	     $stat=statArray($result, $num, $day, $begin, $end);

	     $xdata[$idx] =  mktime(0, 0, 0, $nextDay['mon'], $nextDay['mday'], $nextDay['year']);
	     
  	     $ydata1[$idx] = $stat["temp_out"]["min"];
	     $ydata2[$idx] = $stat["temp_out"]["avg"];     
	     $ydata3[$idx] = $stat["temp_out"]["max"];
	     $ydata4[$idx] = $stat["rain_total"]['max'] - $stat["rain_total"]['min'];
	     
     	     $idx++;
           }
	
	   $nextDay = getdate(strtotime("+1 day", mktime(0, 0, 0, $nextDay['mon'], $nextDay['mday'], $nextDay['year'])));
	   $day   = $nextDay['mday'];
   	   $month = $nextDay['mon'];
	   $year  = $nextDay['year'];
	   
   	   mysql_free_result($result);

	}
	
	$lineplot1=new LinePlot($ydata1, $xdata);
	$lineplot1->SetColor("yellow");
	$lineplot1->SetWeight(2);
	$lineplot1->SetLegend($text['min']);
	$graph->Add($lineplot1);
	
	$lineplot2=new LinePlot($ydata2, $xdata);
	$lineplot2->SetColor("green");
	$lineplot2->SetWeight(2);
	$lineplot2->SetLegend($text['avg']);
	$graph->Add($lineplot2);
	
	$lineplot3=new LinePlot($ydata3, $xdata);
	$lineplot3->SetColor("red");
	$lineplot3->SetWeight(2);
	$lineplot3->SetLegend($text['max']);
	$graph->Add($lineplot3);
	
	$lineplot4=new LinePlot($ydata4, $xdata);
	$lineplot4->SetColor("blue");
	$lineplot4->SetWeight(2);
	$lineplot4->SetLegend($text['precipitation']);
	$graph->AddY2($lineplot4);	

	$graph->SetShadow();
	$graph->Stroke();
?>
