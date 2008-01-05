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

	if(array_key_exists("col",$_REQUEST))
	   $col = $_REQUEST["col"];
	else
	   $col = "temp_out";

	if(array_key_exists("avg",$_REQUEST))
	   $numAvg = $_REQUEST["avg"];
	else
	   $numAvg = 10;	   

	if(array_key_exists("title",$_REQUEST))
	   $title = $_REQUEST["title"];
	else
	   $title = $text['avg_temp'];

	if(array_key_exists("unit",$_REQUEST))
	   $unit = $_REQUEST["unit"];
	else
	   $unit = "°C";

	$graph = new Graph(800, 300, "auto", 86400);
	
	$query = "select max(timestamp) from weather";
	$result = mysql_query($query) or die ("Abfrage fehlgeschlagen<br>Query:<font color=red>$query</font><br>Error:" . mysql_error());
	$timestamp = mysql_result($result, 0);
	mysql_free_result($result);
	$maxYear    = substr($timestamp, 0, 4);

	$graph->SetMargin(50,150,10,90);
	$graph->SetScale("datlin");
	//$graph->SetY2Scale( "lin");
		
	$graph ->xaxis->scale-> SetDateFormat( 'd.m');
	
	$graph->title->Set($title);
	
	$graph->yaxis->SetColor("green");
	$graph->yaxis->title->Set($unit);
	//$graph->y2axis->title->Set("mm");
	//$graph->y2axis->SetColor("blue");
	$graph->xaxis->SetLabelAngle(90);
	$graph->xaxis->SetPos('min');
	
	$graph ->legend->Pos( 0.03,0.2,"right" ,"center");
	 
	$xdata = array();
	$ydata = array();
	$ydata1 = array();
	$ydata2 = array();
	$ydata3 = array();	
	$ydata4 = array();

	
	
	/*** START DATA QUERY */
	
	$nextDay = getdate(strtotime("+ 0 sec", mktime(0, 0, 0, 1, $startMonth, $startYear)));
	$day   = $nextDay['mday'];
	$month = $nextDay['mon'];
	$year  = $nextDay['year'];
	
	$idx  = 0;
	$idx2 = 0;
	$avg = 0;
	$yearNum = -1;
	$lastYear = 0;

	while($year <= $maxYear+1)
	{
	   if($year != $lastYear)
	   {
		$yearNum++;
		$ydata[$yearNum] = array();
		$xdata[$yearNum] = array();
		$idx2 = 0;
		$lastYear = $year;
	   }	


	   $begin = convertTimestamp($day, $month, $year, 0, 0, 0);
   	   $end   = convertTimestamp($day, $month, $year, 23, 59, 59);
	
	   $query = "select * from weather where timestamp >= $begin and timestamp <= $end order by timestamp";
 	   $result = mysql_query($query) or die ("oneValue Abfrage fehlgeschlagen<br>Query:<font color=red>$query</font><br>Error:" . mysql_error());
 	   $num = mysql_num_rows($result);
	   
	   if ($num > 0)
	   {
	     $stat=statArray($result, $num, $day, $begin, $end);

	      if($col == "rain_total")	
		     $ydata2[$idx] = $stat["rain_total"]["max"] - $stat["rain_total"]["min"];
	       else
	     	 $ydata2[$idx] = $stat[$col]["avg"];
	     
 	     $xdata[$yearNum][$idx2] =  mktime(0, 0, 0, $nextDay['mon'], $nextDay['mday'], 0);

	     if($idx < $numAvg)
	     {
		  $avg += $ydata2[$idx];
		  $ydata[$yearNum][$idx2] =  $avg / ($idx+2);
	     }
	     else
	     {
              $ydata[$yearNum][$idx2] =  $avg / $numAvg;
		   
	   	  $avg += $ydata2[$idx];
		  $avg -= $ydata2[$idx - $numAvg];	
	     }

     	     $idx++;
	     $idx2++;
         }
	
	   $nextDay = getdate(strtotime("+1 day", mktime(0, 0, 0, $nextDay['mon'], $nextDay['mday'], $nextDay['year'])));
	   $day   = $nextDay['mday'];
   	   $month = $nextDay['mon'];
	   $year  = $nextDay['year'];
	   
   	   mysql_free_result($result);

	}
	
	$num = 0;
	$lineplot = array();


	$gColors = array();
	$gColors[0] = "green";
	$gColors[1] = "blue";
	$gColors[2] = "cyan";
	$gColors[3] = "orange";
	

	while($num < $yearNum)
	{
		$lineplot[$num]=new LinePlot($ydata[$num], $xdata[$num]);
		$lineplot[$num]->SetColor($gColors[$num % 4]);
		$lineplot[$num]->SetWeight($LineThickness);
		$lineplot[$num]->SetLegend($startYear + $num);
		$graph->Add($lineplot[$num]);
		$num++;
	}
	
	$graph->SetShadow();
	$graph->Stroke();
?>
