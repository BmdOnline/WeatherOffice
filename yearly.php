<html>
<head>
	<meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
	<title>Weather</title>
	<link rel="stylesheet" href="woffice.css">
	</head>
	<body bgcolor="#d6e5ca" marginheight="25" marginwidth="20" topmargin="25" leftmargin="0">
			
<?PHP
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
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//
// getDay
//
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function getYear($dispyear, $text)
{
	
	$nextDay = getdate(strtotime("+0 sec", mktime(0, 0, 0, 1, 1, $dispyear)));
	$day   = $nextDay['mday'];
	$month = $nextDay['mon'];
	$year  = $nextDay['year'];
	
	echo "<h2>{$text['yearly_overview']} {$text['for']} $year.</h2>";
	
	echo "<Table border=\"1\" spaceing=\"5\" >";
	echo "<tr>";
	echo "<td><b>{$text['date']}</b></td>";
	echo "<td colspan=\"5\"><b>{$text['temp_out']} &deg;C </b></td>";	
	
	if(isDisplayEnabled(DISPLAY_RAIN_INFO))
	{
		echo "<td colspan=\"3\"><b>{$text['precipitation']} mm</b></td>";			
	}
	
	if(isDisplayEnabled(DISPLAY_WIND_INFO))
	{
		echo "<td colspan=\"2\"><b>{$text['wind']} km/h </b></td>";
		echo "<td colspan=\"1\"><b>{$text['wind']}</b></td>";	
	}
	
	echo "<td colspan=\"3\"><b>{$text['hum_out']} % </b></td>";	
	
	if(isDisplayEnabled(DISPLAY_PRES_INFO))
	{
		echo "<td colspan=\"3\"><b>{$text['pressure']} hPa </b></td>";
	}
	
	if(isDisplayEnabled(DISPLAY_ROOM_INFO))
	{
		echo "<td></td>";				
		echo "<td colspan=\"3\"><b>{$text['temp_in']} &deg;C </b></td>";
		echo "<td colspan=\"3\"><b>{$text['hum_in']} % </b></td>";
	}
	
	echo "</tr>";
	
	echo "<tr>";
	echo "<td></td>";
	echo "<td><b>{$text['min']}</b></td>";	
	echo "<td><b>{$text['max']}</b></td>";
	echo "<td><b>{$text['avg']}</b></td>";
	echo "<td colspan=\"2\"><b>{$text['long_avg']}</b></td>";	

	if(isDisplayEnabled(DISPLAY_RAIN_INFO))
	{
		echo "<td><b>{$text['total']}</b></td>";
		echo "<td colspan=\"2\"><b>{$text['long_avg']}</b></td>";
	}
	
	if(isDisplayEnabled(DISPLAY_WIND_INFO))
	{
		echo "<td><b>{$text['avg']}</b></td>";		
		echo "<td><b>{$text['max']}</b></td>";
		echo "<td><b>{$text['dir']}</b></td>";
	}
	
	echo "<td><b>{$text['min']}</b></td>";
	echo "<td><b>{$text['avg']}</b></td>";		
	echo "<td><b>{$text['max']}</b></td>";

	if(isDisplayEnabled(DISPLAY_PRES_INFO))
	{
		echo "<td><b>{$text['min']}</b></td>";
		echo "<td><b>{$text['avg']}</b></td>";		
		echo "<td><b>{$text['max']}</b></td>";
	}
	
	if(isDisplayEnabled(DISPLAY_ROOM_INFO))
	{
		echo "<td></td>";				
		
		echo "<td><b>Min</b></td>";
		echo "<td><b>Avg</b></td>";		
		echo "<td><b>Max</b></td>";
		
		echo "<td><b>Min</b></td>";
		echo "<td><b>Avg</b></td>";		
		echo "<td><b>Max</b></td>";
	}			
	echo "</tr>";
	
	$sum['temp_out_avg'] = 0;
	$sum['temp_out_long_avg'] = 0;
	$sum['rain'] = 0;
	$sum['rain_long_avg'] = 0;	
	$numMonth=0;

	while($dispyear == $year )
	{
	
	   $begin = convertTimestamp($day, $month, $year, 0, 0, 0);
   	 $end   = convertTimestamp(31, $month, $year, 23, 59, 59);
	   
	   
	   $stat=MinMaxAvg::getStatArray('YEARMONTH', $year, $month, $day);
	   
	   if ($stat)
	   {
	     //$stat=statArray($result, $num, $day, $begin, $end);
	     $numMonth++;
	     echo "<tr>";
	     
  	   printf ("<td><a href=\"monthly.php?yearMonth=%d%d\">%s</a></td>",$year,$month,monthName($month, $text)); 
	     printf ("<td>%.1f </td>",$stat["temp_out"]["min"]);	     
	     printf ("<td>%.1f </td>",$stat["temp_out"]["max"]);
	     printf ("<td>%.1f </td>",$stat["temp_out"]["avg"]);


	     $mittel = longTermAverage($month);
  	     $avgTemp = $mittel['temp'];
	     $devTemp = $stat["temp_out"]["avg"]-$avgTemp;
	     if ($devTemp > 0)
		     printf ("<td><span style=\"color: rgb(255, 0, 0);\">+%.1f</span></td>", $devTemp);
	     else if ($devTemp < 0)
	     	     printf ("<td><span style=\"color: rgb(0, 0, 255);\">%.1f</span></td>", $devTemp);
	     else
			printf ("<td>%.1f</td>", $devTemp);

	     printf ("<td>%.1f</td>", $avgTemp);

	     $sum['temp_out_avg']      += $stat["temp_out"]["avg"];
	     $sum['temp_out_long_avg'] += $avgTemp;
	     
	     if(isDisplayEnabled(DISPLAY_RAIN_INFO))
			 {
					$avgRain = $mittel['rain'];
					$devRain = $stat["rain_total"]['max'] - $avgRain;

					printf ("<td>%.1f </td>",$stat["rain_total"]['max']);

					if ($devRain > 0)
						printf ("<td><span style=\"color: rgb(255, 0, 0);\">+%.1f</span></td>", $devRain);
					else if ($devRain < 0)
						printf ("<td><span style=\"color: rgb(0, 0, 255);\">%.1f</span></td>", $devRain);
					else
					printf ("<td>%.1f</td>", $devRain);

					printf ("<td>%.1f</td>", $avgRain);
					
					$sum['rain'] += $stat["rain_total"]['max'];
					$sum['rain_long_avg'] += $avgRain;	     
	     }
	     
	     if(isDisplayEnabled(DISPLAY_WIND_INFO))
			 {
				 printf ("<td>%.1f </td>",$stat["windspeed"]["avg"]* 3.6);	     
				 printf ("<td>%.1f </td>",$stat["windspeed"]["max"]* 3.6);	     
				 printf ("<td>%.1f </td>",$stat["wind_angle"]["avg"]);	  
	     }
	
	     printf ("<td>%.1f </td>",$stat["rel_hum_out"]["min"]);
	     printf ("<td>%.1f </td>",$stat["rel_hum_out"]["avg"]);	     
	     printf ("<td>%.1f </td>",$stat["rel_hum_out"]["max"]);	

	     if(isDisplayEnabled(DISPLAY_PRES_INFO))
			 {
				 printf ("<td>%.1f </td>",$stat["rel_pressure"]["min"]);
				 printf ("<td>%.1f </td>",$stat["rel_pressure"]["avg"]);	     
				 printf ("<td>%.1f </td>",$stat["rel_pressure"]["max"]);	
			 }

	     if(isDisplayEnabled(DISPLAY_ROOM_INFO))
			 {
				echo "<td></td>";
					
				printf ("<td>%.1f </td>",$stat["temp_in"]["min"]);
				printf ("<td>%.1f </td>",$stat["temp_in"]["avg"]);	     
				printf ("<td>%.1f </td>",$stat["temp_in"]["max"]);	 
					
				printf ("<td>%.1f </td>",$stat["rel_hum_in"]["min"]);
				printf ("<td>%.1f </td>",$stat["rel_hum_in"]["avg"]);	     
				printf ("<td>%.1f </td>",$stat["rel_hum_in"]["max"]);	
			 }
	     	     
	     echo "</tr>";
	   }
	
	   $nextDay = getdate(strtotime("+1 month", mktime(0, 0, 0, $nextDay['mon'], $nextDay['mday'], $nextDay['year'])));
	   $day   = $nextDay['mday'];
   	   $month = $nextDay['mon'];
	   $year  = $nextDay['year'];
	   

	}

	if($numMonth>0)
	{
		$temp_out_avg = $sum['temp_out_avg'] / $numMonth;
		$temp_out_long_avg = $sum['temp_out_long_avg'] / $numMonth;

		printf ("<tr></tr><tr><td>${text['total']}<td><td></td>");
		
		printf ("<td>%.1f </td>",$temp_out_avg);
		
	  $devTemp = $temp_out_avg-$temp_out_long_avg;
	  
	  if ($devTemp > 0)
		  printf ("<td><span style=\"color: rgb(255, 0, 0);\">+%.1f</span></td>", $devTemp);
	  else if ($devTemp < 0)
	    printf ("<td><span style=\"color: rgb(0, 0, 255);\">%.1f</span></td>", $devTemp);
	  else
			printf ("<td>%.1f</td>", $devTemp);
		
	  printf ("<td>%.1f</td>", $temp_out_long_avg);		
	        
	  if(isDisplayEnabled(DISPLAY_RAIN_INFO))
		{		
			$devRain = $sum['rain'] - $sum['rain_long_avg'];	
			
			printf ("<td>%.1f</td>", $sum['rain']);
					
			if ($devRain > 0)
				printf ("<td><span style=\"color: rgb(255, 0, 0);\">+%.1f</span></td>", $devRain);
			else if ($devRain < 0)
				printf ("<td><span style=\"color: rgb(0, 0, 255);\">%.1f</span></td>", $devRain);
			else
				printf ("<td>%.1f</td>", $devRain);

			printf ("<td>%.1f</td>", $sum['rain_long_avg']);
		}
		
		printf ("</tr>");

	}

	echo "</table>";
	
	echo "<p><img src=\"yearlyGraph.php?year=$dispyear&\">";

	
}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//
// MAIN
//
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////
//
// Data in weather (as stored by mysql2300)
//
// timestamp: uniqe bigint(14) in format YYYYMMDDhhmmss
//
//////////////////////////////////////////////////////////////////////
include("weatherInclude.php");
include("class.MinMaxAvg.php");

$year =  $_REQUEST["year"];

MinMaxAvg::updateDbTables(true); // Incremental update of DB Tables
getYear($year, $text);

mysql_close();
?>
