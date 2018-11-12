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
// getMonth
//
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function getMonth($month, $year, $showVal, $text, $lng)
{
	global $database;
	// Header
	$prev = getdate(strtotime("-1 month", mktime(0, 0, 0, $month, 1, $year)));
	$next   = getdate(strtotime("+1 month", mktime(0, 0, 0, $month, 1, $year)));

	$prevMon = $prev['mon'];
	$nextMon = $next['mon'];
	$prevYear = $prev['year'];
	$nextYear = $next['year'];

	$begin = convertTimestamp(1, $month, $year, 0, 0, 0);
	$end   = convertTimestamp(31, $month, $year, 23, 59, 59);

	$monthName = monthName($month, $text);
	$prevMonthName = monthName($prevMon, $text);
	$nextMonthName = monthName($nextMon, $text);

	echo "<a name=\"top\"></a>";
	echo "<center>";
	echo "{$text['go_to']}: <a href=\"monthly.php?showVal=$showVal&yearMonth=$prevYear$prevMon&lng=$lng\" target=\"main\">$prevMonthName $prevYear</a> {$text['or']} ";
	echo "<a href=\"monthly.php?showVal=$showVal&yearMonth=$nextYear$nextMon&lng=$lng\" target=\"main\">$nextMonthName $nextYear</a><hr>";
	echo "</center>";

	$result = $database->getWeatherFromPeriod($begin, $end, false);
	$num = $database->getRowsCount();
	if ($num == 0)
	{
		getStartYearAndMonth($firstYear, $firstMonth, $firstDay);
		getStopYearAndMonth($lastYear, $lastMonth, $lastDay);
		printf($text['messages']['no_data_found_m'], "$month.$year", "$firstDay.$firstMonth.$firstYear", "$lastDay.$lastMonth.$lastYear");
		return $num;
	}

	// Statistics
	$stat=statArray($num, 1, $begin, $end);

	echo "<h2>{$text['monthly_overview']} {$text['for']} $monthName $year.</h2>";
	$today = getdate();
	$tomorrow = getdate(strtotime("+1 day", mktime(0, 0, 0, $today['mon'], $today['mday'], $today['year'])));
	if($today['year'] == $year && $today['mon'] == $month && $tomorrow['mon'] == $month)
	{
	   printf("<h3><font color=\"red\">".$text['partial_values']." %d.%d.%d.</font></h3>", $today['mday'], $today['mon'], $today['year']);
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
	printf("<p>");
	printf($text['messages']['avg_temp'], $avgTemp, $monthName);
	printf($text['messages']['avg_temp_diff'], $avgTempDiff, $wcTxt, $mittel['temp']);
	printf($text['messages']['max_temp'],
		dayLink(substr($stat["temp_out"]['maxDate'], 8, 2), $month, $year) . "." . substr($stat["temp_out"]['maxDate'], 5, 2), substr($stat["temp_out"]['maxTime'], 0, 5), $stat["temp_out"]['max']);
	printf($text['messages']['min_temp'],
		dayLink(substr($stat["temp_out"]['minDate'], 8, 2), $month, $year) . "." . substr($stat["temp_out"]['minDate'], 5, 2), substr($stat["temp_out"]['minTime'], 0, 5), $stat["temp_out"]['min']);
	printf($text['messages']['max_temp_day'],
		dayLink(substr($stat["temp_out"]['maxDayAvgDate'], 8, 2), $month, $year) . "." . substr($stat["temp_out"]['maxDayAvgDate'], 5, 2), $stat["temp_out"]['maxDayAvg']);
	printf($text['messages']['min_temp_day'],
		dayLink(substr($stat["temp_out"]['minDayAvgDate'], 8, 2), $month, $year) . "." . substr($stat["temp_out"]['minDayAvgDate'], 5, 2), $stat["temp_out"]['minDayAvg']);
	printf("</p>");

	printf("<p>");
	printSpecialDays($stat, $text);
	printf("</p>");

	printf("<p>");
	printf($text['messages']['avg_dewpoint'], $stat["dewpoint"]['avg']);
	printf($text['messages']['max_dewpoint'],
		dayLink(substr($stat["dewpoint"]['maxDate'], 8, 2), $month, $year) . "." . substr($stat["dewpoint"]['maxDate'], 5, 2),  substr($stat["dewpoint"]['maxTime'], 0, 5), $stat["dewpoint"]['max']);
	printf($text['messages']['min_dewpoint'],
		dayLink(substr($stat["dewpoint"]['minDate'], 8, 2), $month, $year) . "." . substr($stat["dewpoint"]['minDate'], 5, 2),  substr($stat["dewpoint"]['minTime'], 0, 5), $stat["dewpoint"]['min']);
	printf("</p>");

	if(isDisplayEnabled(DISPLAY_RAIN_INFO))
	{
		printf("<p>");
		printf($text['messages']['rain_month'], $rainMon, $rainPct, $mittel['rain']);
		printf($text['messages']['max_rain_day'],
			dayLink(substr($stat["rain_total"]['maxDayDiffDate'], 8, 2), $month, $year) . "." . substr($stat["rain_total"]['maxDayDiffDate'], 5, 2), $stat["rain_total"]['maxDayDiff']);
		printf($text['messages']['no_rain_days'], $stat["rain_total"]['zeroDiffValDays'], $stat["rain_total"]['zeroDiffValText']);
		printf("</p>");
	}

	if(isDisplayEnabled(DISPLAY_WIND_INFO))
	{
		printf("<p>");
		printf($text['messages']['avg_windspeed'], $monthName,
			$stat["windspeed"]['avg']*3.6, beaufort($stat['windspeed']['avg'], $lng));
		printf($text['messages']['max_windspeed'],
			dayLink(substr($stat["windspeed"]['maxDate'], 8, 2), $month, $year) . "." . substr($stat["windspeed"]['maxDate'], 5, 2),
			substr($stat["windspeed"]['maxTime'], 0, 5), $stat["windspeed"]['max']*3.6, beaufort($stat['windspeed']['max'], $lng));
		printf($text['messages']['max_windspeed_day'],
			dayLink(substr($stat["windspeed"]['maxDayAvgDate'], 8, 2), $month, $year) . "." . substr($stat["windspeed"]['maxDayAvgDate'], 5, 2),
			$stat["windspeed"]['maxDayAvg']*3.6, beaufort($stat['windspeed']['maxDayAvg'],$lng));
		printf($text['messages']['min_windspeed_day'],
			dayLink(substr($stat["windspeed"]['minDayAvgDate'], 8, 2), $month, $year) . "." . substr($stat["windspeed"]['minDayAvgDate'], 5, 2),
			$stat["windspeed"]['minDayAvg']*3.6, beaufort($stat['windspeed']['minDayAvg'],$lng));
		printf($text['messages']['avg_wind_dir'], $windAvgTxt, $windAvg);
		printf("</p>");
	}

	if(isDisplayEnabled(DISPLAY_PRES_INFO))
	{
		printf("<p>");
		printf($text['messages']['avg_pressure'], $stat["rel_pressure"]['avg']);
		printf($text['messages']['max_pressure'],
			dayLink(substr($stat["rel_pressure"]['maxDate'], 8, 2), $month, $year) . "." . substr($stat["rel_pressure"]['maxDate'], 5, 2),
			substr($stat["rel_pressure"]['maxTime'], 0, 5),
			$stat["rel_pressure"]['max']);
		printf($text['messages']['min_pressure'],
			dayLink(substr($stat["rel_pressure"]['minDate'], 8, 2), $month, $year) . "." . substr($stat["rel_pressure"]['minDate'], 5, 2),
			substr($stat["rel_pressure"]['minTime'], 0, 5),
			$stat["rel_pressure"]['min']);
		printf("</p>");
	}
	printf("");

	// Graphs
	graphs("month", "{$text['graphs']} {$text['for']} $monthName $year.", $begin, $end, $text);

	// Average Table Header
	echo "<a name=\"avg\"></a>";
	echo "<h3>{$text['avg_values']} {$text['for']} $monthName $year.</h3><p>";
	valueTable($stat, "avg", "--", "--", "--", $text);

	// min values Table Header
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
		printTableRows($database);
		tableFooter($text);
	}
	else
	{
		echo "<hr><a href=\"monthly.php?showVal=true&yearMonth=$year$month&lng=$lng#all\">{$text['show_all_values']}</a>";
	}

	echo "<hr><center>";
	echo "{$text['go_to']}: <a href=\"monthly.php?showVal=$showVal&yearMonth=$prevYear$prevMon&lng=$lng\" target=\"main\">$prevMonthName $prevYear</a> {$text['or']} ";
	echo "<a href=\"monthly.php?showVal=$showVal&yearMonth=$nextYear$nextMon&lng=$lng\" target=\"main\">$nextMonthName $nextYear</a><hr>";
	echo "</center>";

	$database->free();
	$database->close();
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
