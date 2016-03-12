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

function printSpecialDays($stat, $text)
{
		$dayFieldList = array('zeroMaxDays','zeroMinDays','summerDays','heatDays','tropicalNights');
	  $shownItems = 0;
	    
	  foreach($dayFieldList as $dayField)
	  { 
			if($stat["temp_out"][$dayField] > 0)
			{
				if($shownItems == 0)
					printf("%s ",$text['There was']);
				else 
					printf(",<br>");
				
				printf("<b>%d %s</b> (%s) (%s)", $stat["temp_out"][$dayField], $text[$dayField], $text[$dayField .'Desc'],  $stat["temp_out"][$dayField. 'Text']);
												
				$shownItems++;
			}
	  }
	  
	  if($shownItems > 0)
			printf(".<br>");	 
}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//
// getDay
//
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function getMonth($month, $year, $showVal, $text, $lng)
{
	$begin = convertTimestamp(1, $month, $year, 0, 0, 0);
	$end   = convertTimestamp(31, $month, $year, 23, 59, 59);
	
	// Header
	$prev = getdate(strtotime("-1 month", mktime(0, 0, 0, $month, 1, $year)));
	$next   = getdate(strtotime("+1 month", mktime(0, 0, 0, $month, 1, $year)));
	
	$prevMon = $prev['mon'];
	$nextMon = $next['mon'];
	$prevYear = $prev['year'];
	$nextYear = $next['year'];
	
	$monthName = monthName($month, $text);
	$prevMonthName = monthName($prevMon, $text);
	$nextMonthName = monthName($nextMon, $text);
	
	echo "<a name=\"top\"></a>";
	 
	echo "<center>";
	echo "{$text['go_to']}: <a href=\"monthly.php?showVal=$showVal&yearMonth=$prevYear$prevMon\" target=\"main\">$prevMonthName $prevYear</a> {$text['or']} ";
	echo "<a href=\"monthly.php?showVal=$showVal&yearMonth=$nextYear$nextMon\" target=\"main\">$nextMonthName $nextYear</a><hr>";
	echo "</center>";
	
	$query = "select * from weather where timestamp >= $begin and timestamp <= $end order by timestamp";
	$result = mysql_query($query) or die ("oneValue Abfrage fehlgeschlagen<br>Query:<font color=red>$query</font><br>Error:" . mysql_error());
	$num = mysql_num_rows($result);
	if ($num == 0)
	{
		getStartYearAndMonth($firstYear, $firstMonth, $firstDay);
		echo "Keine Daten f&uuml;r den $day.$month.$year gefunden. Daten sind ab dem $firstDay.$firstMonth.$firstYear verf&uuml;gbar.";
		return $num;
	}
	
	// Statistics
	$stat=statArray($result, $num, 1, $begin, $end);
		
	echo "<h2>{$text['monthly_overview']} {$text['for']} $monthName $year.</h2>";
	$today = getdate();
	$tomorrow = getdate(strtotime("+1 day", mktime(0, 0, 0, $today['mon'], $today['mday'], $today['year'])));
	if($today['year'] == $year && $today['mon'] == $month && $tomorrow['mon'] == $month)
	{
	   printf("<h3><font color=\"red\">Vorl&auml;ufig bis zum %d.%d.%d.</font></h3>", $today['mday'], $today['mon'], $today['year']);
	}
	
	links($showVal, $text);
	
	// Gasteiger Text
	$mittel = longTermAverage($month);
	$avgTemp = $stat["temp_out"]['avg'];
	$avgTempDiff = abs($avgTemp -  $mittel['temp']);
	if($avgTemp >= $mittel['temp'])
	{
		$wcTxt = $text['warmer'];
	}
	else
	{
		$wcTxt = $text['colder'];
	}
	
	$rainMon = $stat["rain_total"]['max'] - $stat["rain_total"]['min'];
	$rainPct = $rainMon/$mittel['rain']*100;
	
	$windAvg = $stat["wind_angle"]['avg'];
	$windAvgTxt = windDir($windAvg, $text);
	
	// Language dependent text
  if($lng == "de")
  {
		// german
	  printf("<p>Die <b>mittlere Temperatur</b> im %s hat <b>%2.2f &deg;C</b> betragen.<br>", $monthName, $avgTemp);
	  printf("Damit war der Monat <b> um %2.2f &deg;C %s </b> als im langj&auml;hrigen Mittel von %2.2f &deg;C ", $avgTempDiff, $wcTxt, $mittel['temp']);
	  printf("<a href=\"http://www.dwd.de/de/FundE/Klima/KLIS/daten/online/nat/index_mittelwerte.htm\" target=\"_blank\">(mehr dazu)</a><br>");
	  printf("Die <b>h&ouml;chste Temperatur</b> wurde am <b>%s.%s</b> um <b>%s</b> mit <b>%2.2f &deg;C</b> gemessen.<br>",
	    	dayLink(substr($stat["temp_out"]['maxDate'], 8, 2), $month, $year), substr($stat["temp_out"]['maxDate'], 5, 2), substr($stat["temp_out"]['maxTime'], 0, 5), $stat["temp_out"]['max']);
	  printf("Die <b>niedrigste Temperatur</b> trat am <b>%s.%s</b> um <b>%s</b> mit <b>%2.2f &deg;C</b> auf.<br>",
			dayLink(substr($stat["temp_out"]['minDate'], 8, 2), $month, $year), substr($stat["temp_out"]['minDate'], 5, 2), substr($stat["temp_out"]['minTime'], 0, 5), $stat["temp_out"]['min']);
		printf("Der <b>w&auml;rmste Tag</b> war der <b>%s.%s</b> mit einer Durchschnittstemperatur von <b>%2.2f &deg;C</b>.<br>",
			dayLink(substr($stat["temp_out"]['maxDayAvgDate'], 8, 2), $month, $year), substr($stat["temp_out"]['maxDayAvgDate'], 5, 2), $stat["temp_out"]['maxDayAvg']);
		printf("Der <b>k&auml;lteste Tag</b> war der <b>%s.%s</b> mit durchschnittlich <b>%2.2f &deg;C</b>.<br>",
			dayLink(substr($stat["temp_out"]['minDayAvgDate'], 8, 2), $month, $year), substr($stat["temp_out"]['minDayAvgDate'], 5, 2), $stat["temp_out"]['minDayAvg']);
	 
	  printSpecialDays($stat, $text);
				  
		printf("<p>Die <b>Taupunktstemperatur</b> lag im Monatsdurchschnitt bei <b>%2.2f &deg;C</b>.<br>", $stat["dewpoint"]['avg']);		
		printf("Der <b>h&ouml;chste Taupunkt</b> wurde am <b>%s.%s</b> um <b>%s</b> mit <b>%2.2f &deg;C</b> erreicht.<br>",
			dayLink(substr($stat["dewpoint"]['maxDate'], 8, 2), $month, $year), substr($stat["dewpoint"]['maxDate'], 5, 2),  substr($stat["dewpoint"]['maxTime'], 0, 5), $stat["dewpoint"]['max']);
		printf("Der <b>niedrigste Taupunkt</b> war am <b>%s.%s</b> um <b>%s</b> mit <b>%2.2f &deg;C</b> zu verzeichnen.<br>",
			dayLink(substr($stat["dewpoint"]['minDate'], 8, 2), $month, $year), substr($stat["dewpoint"]['minDate'], 5, 2),  substr($stat["dewpoint"]['minTime'], 0, 5), $stat["dewpoint"]['min']);
	
		if(isDisplayEnabled(DISPLAY_RAIN_INFO))
		{
			printf("<p>Es fielen <b>%2.2f mm Niederschlag</b>. Das waren <b>%2.2f %%</b> des langj&auml;hrigen Mittels (<b>%s mm</b>).<br>", $rainMon, $rainPct, $mittel['rain']);
			printf("Der <b>niederschlagsreichste Tag</b> war der <b>%s.%s</b> mit <b>%2.2f mm</b>.<br>",
			dayLink(substr($stat["rain_total"]['maxDayDiffDate'], 8, 2), $month, $year), substr($stat["rain_total"]['maxDayDiffDate'], 5, 2), $stat["rain_total"]['maxDayDiff']);
			printf("<b>Niederschlagsfrei</b> waren <b>%d Tage</b> (%s).<br>", $stat["rain_total"]['zeroDiffValDays'], $stat["rain_total"]['zeroDiffValText']);
		}
		
		if(isDisplayEnabled(DISPLAY_WIND_INFO))
		{
			printf("<p>Der <b>mittlere Wind</b> im <b>%s</b> hat <b>%2.2f km/h (%s)</b>
				betragen.<br>", $monthName, $stat["windspeed"]['avg']*3.6,	beaufort($stat['windspeed']['avg'], $lng));
			printf("Der <b>st&auml;rkste Wind</b> war am <b>%s.%s</b> um <b>%s</b> mit <b>%2.2f km/h (%s)</b> zu verzeichnen.<br>",
				dayLink(substr($stat["windspeed"]['maxDate'], 8, 2), $month, $year),
				substr($stat["windspeed"]['maxDate'], 5, 2), 
				substr($stat["windspeed"]['maxTime'], 0, 5), $stat["windspeed"]['max']*3.6, beaufort($stat['windspeed']['max'], $lng));
			printf("Der <b>windigste Tag</b> war der <b>%s.%s</b> mit einer durchschnittlichen Windgeschwindigkeit von <b>%2.2f km/h (%s)</b>.<br>",
				dayLink(substr($stat["windspeed"]['maxDayAvgDate'], 8, 2), $month, $year),
				substr($stat["windspeed"]['maxDayAvgDate'], 5, 2), $stat["windspeed"]['maxDayAvg']*3.6, beaufort($stat['windspeed']['maxDayAvg'],$lng));
			printf("Am <b>wenigsten Wind</b> wehte am <b>%s.%s</b> mit durchschnittlich <b>%2.2f km/h (%s)</b>.<br>",
				dayLink(substr($stat["windspeed"]['minDayAvgDate'], 8, 2), $month, $year),
				substr($stat["windspeed"]['minDayAvgDate'], 5, 2), $stat["windspeed"]['minDayAvg']*3.6, beaufort($stat['windspeed']['minDayAvg'],$lng));
			printf("Der Wind kam vorherrschend aus <b>%s (%2.2f)</b>.<br>", $windAvgTxt, $windAvg);
		}
		if(isDisplayEnabled(DISPLAY_PRES_INFO))
		{
			printf("<p>Der <b>mittlere auf NN reduzierte Luftdruck</b> lag bei <b>%2.2f hPa</b>.<br>", $stat["rel_pressure"]['avg']);
			printf("Der <b>h&ouml;chste Luftdruck</b> wurde am <b>%s.%s</b> um <b>%s</b> mit <b>%2.2f hPa</b> gemessen.<br>",
				dayLink(substr($stat["rel_pressure"]['maxDate'], 8, 2), $month, $year),
				dayLink(substr($stat["rel_pressure"]['maxDate'], 5, 2), $month, $year),
				substr($stat["rel_pressure"]['maxTime'], 0, 5),
				$stat["rel_pressure"]['max']);
			printf("Der <b>niedrigste Luftdruck</b> kam am <b>%s.%s</b> um <b>%s</b> mit <b>%2.2f hPa</b> vor.<br>",
				dayLink(substr($stat["rel_pressure"]['minDate'], 8, 2), $month, $year),
				dayLink(substr($stat["rel_pressure"]['minDate'], 5, 2), $month, $year),
				substr($stat["rel_pressure"]['minTime'], 0, 5),
				$stat["rel_pressure"]['min']);
		}	
		printf("");
	}
	else
	{ 
		// English version
		printf("<p>The <b>average temperature</b> was <b>%2.2f &deg;C</b> in %s.<br>", $avgTemp, $monthName);
		printf("This means it was <b> by %2.2f &deg;C %s </b> then in the long-time average of %2.2f &deg;C ", $avgTempDiff, $wcTxt, $mittel['temp']);
		printf("<a href=\"http://www.dwd.de/de/FundE/Klima/KLIS/daten/online/nat/index_mittelwerte.htm\" target=\"_blank\">(read more)</a><br>");
		printf("The <b>highest temperature</b> was measured on <b>%s.%s</b> at <b>%s</b> with <b>%2.2f &deg;C</b>.<br>",
			dayLink(substr($stat["temp_out"]['maxDate'], 8, 2), $month, $year), substr($stat["temp_out"]['maxDate'], 5, 2), substr($stat["temp_out"]['maxTime'], 0, 5), $stat["temp_out"]['max']);
		printf("The <b>lowest temperature</b> appeared on <b>%s.%s</b> at <b>%s</b> and was <b>%2.2f &deg;C</b>.<br>",
			dayLink(substr($stat["temp_out"]['minDate'], 8, 2), $month, $year), substr($stat["temp_out"]['minDate'], 5, 2), substr($stat["temp_out"]['minTime'], 0, 5), $stat["temp_out"]['min']);
		printf("The <b>warmest day</b> has been the <b>%s.%s</b> with an average temperature of <b>%2.2f &deg;C</b>.<br>",
			dayLink(substr($stat["temp_out"]['maxDayAvgDate'], 8, 2), $month, $year), substr($stat["temp_out"]['maxDayAvgDate'], 5, 2), $stat["temp_out"]['maxDayAvg']);
		printf("The <b>coldest day</b> was the <b>%s.%s</b> with an average of <b>%2.2f &deg;C</b>.<br>",
			dayLink(substr($stat["temp_out"]['minDayAvgDate'], 8, 2), $month, $year), substr($stat["temp_out"]['minDayAvgDate'], 5, 2), $stat["temp_out"]['minDayAvg']);
	 
		printSpecialDays($stat, $text);
	 			
		printf("<p>The <b>dewpoint</b> was in the average of the month at <b>%2.2f &deg;C</b>.<br>", $stat["dewpoint"]['avg']);		
		printf("The <b>highest dewpoint</b> was reached on <b>%s.%s</b> at <b>%s</b> with <b>%2.2f &deg;C</b>.<br>",
			dayLink(substr($stat["dewpoint"]['maxDate'], 8, 2), $month, $year), substr($stat["dewpoint"]['maxDate'], 5, 2),  substr($stat["dewpoint"]['maxTime'], 0, 5), $stat["dewpoint"]['max']);
		printf("The <b>lowest dewpoint</b> was measured on <b>%s.%s</b> at <b>%s</b> with <b>%2.2f &deg;C</b>.<br>",
			dayLink(substr($stat["dewpoint"]['minDate'], 8, 2), $month, $year), substr($stat["dewpoint"]['minDate'], 5, 2),  substr($stat["dewpoint"]['minTime'], 0, 5), $stat["dewpoint"]['min']);
	
		if(isDisplayEnabled(DISPLAY_RAIN_INFO))
		{
			printf("<p>There was a <b>precipitation of %2.2f mm</b>, <b>%2.2f %%</b> of the long-time average of <b>%s mm</b>.<br>", $rainMon, $rainPct, $mittel['rain']);
			printf("The day with the <b>most precipitation</b> was <b>%s.%s</b> with <b>%2.2f mm</b>.<br>",
			dayLink(substr($stat["rain_total"]['maxDayDiffDate'], 8, 2), $month, $year), substr($stat["rain_total"]['maxDayDiffDate'], 5, 2), $stat["rain_total"]['maxDayDiff']);
			printf("<b>No precipitation</b> happened on <b>%d day(s)</b> (%s).<br>", $stat["rain_total"]['zeroDiffValDays'], $stat["rain_total"]['zeroDiffValText']);
		}
		
		if(isDisplayEnabled(DISPLAY_WIND_INFO))
		{
			printf("<p>The <b>average windspeed</b> in <b>%s</b> was <b>%2.2f km/h (%s)</b>.<br>", $monthName,
				$stat["windspeed"]['avg']*3.6, beaufort($stat['windspeed']['avg'], $lng));
			printf("The <b>strongest wind</b> was measured on <b>%s.%s</b> at <b>%s</b> with <b>%2.2f km/h (%s)</b>.<br>",
				dayLink(substr($stat["windspeed"]['maxDate'], 8, 2), $month, $year),
				substr($stat["windspeed"]['maxDate'], 5, 2), 
				substr($stat["windspeed"]['maxTime'], 0, 5), $stat["windspeed"]['max']*3.6, beaufort($stat['windspeed']['max'], $lng));
			printf("The <b>windiest day</b> was <b>%s.%s</b> with an average windspeed of <b>%2.2f km/h (%s)</b>.<br>",
				dayLink(substr($stat["windspeed"]['maxDayAvgDate'], 8, 2), $month, $year),
				substr($stat["windspeed"]['maxDayAvgDate'], 5, 2), $stat["windspeed"]['maxDayAvg']*3.6, beaufort($stat['windspeed']['maxDayAvg'],$lng));
			printf("The day with the <b>least wind</b> was <b>%s.%s</b> with an average of <b>%2.2f km/h (%s)</b>.<br>",
				dayLink(substr($stat["windspeed"]['minDayAvgDate'], 8, 2), $month, $year),
				substr($stat["windspeed"]['minDayAvgDate'], 5, 2), $stat["windspeed"]['minDayAvg']*3.6, beaufort($stat['windspeed']['minDayAvg'],$lng));
			printf("The wind came most of the time from <b>%s (%2.2f)</b>.<br>", $windAvgTxt, $windAvg);
		}
		
		if(isDisplayEnabled(DISPLAY_PRES_INFO))
		{
			printf("<p>The <b>average, to normal zero reduced airpressure</b> was at <b>%2.2f hPa</b>.<br>", $stat["rel_pressure"]['avg']);
			printf("The <b>highst airpressure</b> was measured on <b>%s.%s</b> at <b>%s</b> with <b>%2.2f hPa</b>.<br>",
				dayLink(substr($stat["rel_pressure"]['maxDate'], 8, 2), $month, $year),
				dayLink(substr($stat["rel_pressure"]['maxDate'], 5, 2), $month, $year),
				substr($stat["rel_pressure"]['maxTime'], 0, 5),
				$stat["rel_pressure"]['max']);
			printf("The <b>lowest airpressure</b> appeared on <b>%s.%s</b> at <b>%s</b> with <b>%2.2f hPa</b>.<br>",
				dayLink(substr($stat["rel_pressure"]['minDate'], 8, 2), $month, $year),
				dayLink(substr($stat["rel_pressure"]['minDate'], 5, 2), $month, $year),
				substr($stat["rel_pressure"]['minTime'], 0, 5),
				$stat["rel_pressure"]['min']);
		}
		
		printf("");;
	}
			
	// graphs
	graphs("month", "{$text['graphs']} {$text['for']} $monthName $year.", $begin, $end, $text);

	// Average Table	
	echo "<a name=\"avg\"></a>";
	echo "<h3>{$text['avg_values']} {$text['for']} $monthName $year.</h3><p>";
	valueTable($stat, "avg", "--", "--", "--", $text);
	
	// min values Table
	echo "<a name=\"minimal\"></a>";
	echo "<hr><h3>{$text['min_values']} {$text['for']} $monthName $year.</h3><p>";	
	valueTimeDateTable($stat, "min", "minTime", "minDate", $text);
	
	// max values Table Header
	echo "<a name=\"maximal\"></a>";
	echo "<hr><h3>{$text['max_values']} {$text['for']} $monthName $year.</h3><p>";	
	valueTimeDateTable($stat, "max", "maxTime", "maxDate", $text);
	
	echo "<a name=\"all\"></a>";
	if ($showVal == "true")
	{
		// All Values Table Header
	
		echo "<hr><h3>{$text['all_values']} {$text['for']} $monthName $year.</h3><p>";	
		tableHeader($text);
	
		// All Values Table
		printTableRows($result);
		tableFooter($text);
	}
	else
	{
		echo "<hr><a href=\"monthly.php?showVal=true&yearMonth=$year$month#all\">{$text['show_all_values']}</a>";
	}
	
	echo "<hr><center>";
	echo "{$text['go_to']}: <a href=\"monthly.php?showVal=$showVal&yearMonth=$prevYear$prevMon\" target=\"main\">$prevMonthName $prevYear</a> {$text['or']} ";
	echo "<a href=\"monthly.php?showVal=$showVal&yearMonth=$nextYear$nextMon\" target=\"main\">$nextMonthName $nextYear</a><hr>";
	echo "</center>";
	
 	mysql_free_result($result);
 	mysql_close();
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

$yearMonth = $_REQUEST["yearMonth"];
$showVal = $_REQUEST["showVal"];
$lng = $_REQUEST["lng"];

$year = substr($yearMonth, 0, 4);
$month  = substr($yearMonth, 4, 2);

getMonth($month, $year, $showVal, $text, $lng);

?>
