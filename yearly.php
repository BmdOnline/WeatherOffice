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
	echo "<td colspan=\"3\"><b>{$text['precipitation']} mm</b></td>";			
	echo "<td colspan=\"2\"><b>{$text['wind']} km/h </b></td>";
	echo "<td colspan=\"1\"><b>{$text['wind']}</b></td>";	
	echo "<td colspan=\"3\"><b>{$text['hum_out']} % </b></td>";
	echo "<td colspan=\"3\"><b>{$text['pressure']} hPa </b></td>";
	echo "<td></td>";				
	echo "<td colspan=\"3\"><b>{$text['temp_in']} &deg;C </b></td>";
	echo "<td colspan=\"3\"><b>{$text['hum_in']} % </b></td>";
	echo "</tr>";
	
	echo "<tr>";
	echo "<td></td>";
	echo "<td><b>{$text['min']}</b></td>";	
	echo "<td><b>{$text['max']}</b></td>";
	echo "<td><b>{$text['avg']}</b></td>";
	echo "<td colspan=\"2\"><b>{$text['long_avg']}</b></td>";	

	echo "<td><b>{$text['total']}</b></td>";
	echo "<td colspan=\"2\"><b>{$text['long_avg']}</b></td>";

	echo "<td><b>{$text['avg']}</b></td>";		
	echo "<td><b>{$text['max']}</b></td>";

	echo "<td><b>{$text['dir']}</b></td>";
	
	echo "<td><b>{$text['min']}</b></td>";
	echo "<td><b>{$text['avg']}</b></td>";		
	echo "<td><b>{$text['max']}</b></td>";

	echo "<td><b>{$text['min']}</b></td>";
	echo "<td><b>{$text['avg']}</b></td>";		
	echo "<td><b>{$text['max']}</b></td>";

	echo "<td></td>";				
	
	echo "<td><b>Min</b></td>";
	echo "<td><b>Avg</b></td>";		
	echo "<td><b>Max</b></td>";
	
	echo "<td><b>Min</b></td>";
	echo "<td><b>Avg</b></td>";		
	echo "<td><b>Max</b></td>";
				
	echo "</tr>";
	
	while($dispyear == $year )
	{
	
	   $begin = convertTimestamp($day, $month, $year, 0, 0, 0);
   	   $end   = convertTimestamp(31, $month, $year, 23, 59, 59);
	
	   $query = "select * from weather where timestamp >= $begin and timestamp <= $end order by timestamp";
 	   $result = mysql_query($query) or die ("oneValue Abfrage fehlgeschlagen<br>Query:<font color=red>$query</font><br>Error:" . mysql_error());
 	   $num = mysql_num_rows($result);
	   
	   if ($num > 0)
	   {
	     $stat=statArray($result, $num, $day, $begin, $end);

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
	     
	     $avgRain = $mittel['rain'];
	     $devRain = $stat["rain_total"]['max'] - $stat["rain_total"]['min'] - $avgRain;

	     printf ("<td>%.1f </td>",$stat["rain_total"]['max'] - $stat["rain_total"]['min']);


	     if ($devRain > 0)
		     printf ("<td><span style=\"color: rgb(255, 0, 0);\">+%.1f</span></td>", $devRain);
	     else if ($devRain < 0)
	     	     printf ("<td><span style=\"color: rgb(0, 0, 255);\">%.1f</span></td>", $devRain);
	     else
			printf ("<td>%.1f</td>", $devRain);

	     printf ("<td>%.1f</td>", $avgRain);
	     
           printf ("<td>%.1f </td>",$stat["windspeed"]["avg"]* 3.6);	     
	     printf ("<td>%.1f </td>",$stat["windspeed"]["max"]* 3.6);
	     
	     printf ("<td>%.1f </td>",$stat["wind_angle"]["avg"]);	     
	
	     printf ("<td>%.1f </td>",$stat["rel_hum_out"]["min"]);
	     printf ("<td>%.1f </td>",$stat["rel_hum_out"]["avg"]);	     
	     printf ("<td>%.1f </td>",$stat["rel_hum_out"]["max"]);	

	     printf ("<td>%.1f </td>",$stat["rel_pressure"]["min"]);
	     printf ("<td>%.1f </td>",$stat["rel_pressure"]["avg"]);	     
	     printf ("<td>%.1f </td>",$stat["rel_pressure"]["max"]);	

	     echo "<td></td>";
	     
	     printf ("<td>%.1f </td>",$stat["temp_in"]["min"]);
	     printf ("<td>%.1f </td>",$stat["temp_in"]["avg"]);	     
	     printf ("<td>%.1f </td>",$stat["temp_in"]["max"]);	 
	     
             printf ("<td>%.1f </td>",$stat["rel_hum_in"]["min"]);
	     printf ("<td>%.1f </td>",$stat["rel_hum_in"]["avg"]);	     
	     printf ("<td>%.1f </td>",$stat["rel_hum_in"]["max"]);	
    
	     	     
	     echo "</tr>";
	   }
	
	   $nextDay = getdate(strtotime("+1 month", mktime(0, 0, 0, $nextDay['mon'], $nextDay['mday'], $nextDay['year'])));
	   $day   = $nextDay['mday'];
   	   $month = $nextDay['mon'];
	   $year  = $nextDay['year'];
	   
   	   mysql_free_result($result);

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

$year =  $_REQUEST["year"];

getYear($year, $text);

mysql_close();
?>
