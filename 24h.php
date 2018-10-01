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
// get24
//
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function get24($day, $month, $year, $hour, $minute, $showVal, $text)
{
	global $link;
	// Header
	$prev = getdate(strtotime("-1 day", mktime($hour, $minute, 0, $month, $day, $year)));
	$next   = getdate(strtotime("+1 day", mktime($hour, $minute, 0, $month, $day, $year)));
	
	$prevDay = $prev['mday'];
	$nextDay = $next['mday'];
	$prevMon = $prev['mon'];
	$nextMon = $next['mon'];
	$prevYear = $prev['year'];
	$nextYear = $next['year'];
	
	$begin = convertTimestamp($prevDay, $prevMon, $prevYear, $hour, $minute, 0);
	$end   = convertTimestamp($day, $month, $year, $hour, $minute, 59);
	
	$monthName = monthName($month, $text);
	$prevMonthName = monthName($prevMon, $text);
	$nextMonthName = monthName($nextMon, $text);
	
	echo "<a name=\"top\"></a>";
	echo "<center>";
	echo "{$text['go']} <a href=\"24h.php?showVal=$showVal&day=$prevDay&month=$prevMon&year=$prevYear&hour=$hour&minute=$minute\" target=\"main\">{$text['24_hours_back']}</a> {$text['or']} ";
	echo "<a href=\"24h.php?showVal=$showVal&day=$nextDay&month=$nextMon&year=$nextYear&hour=$hour&minute=$minute\" target=\"main\">{$text['24_hours_forward']}</a><hr>";
	echo "</center>";
	
	$query = "select * from weather where timestamp >= $begin and timestamp <= $end order by timestamp";
	$result = $link->query($query);
	if (!$result) {
		printf("Query Failed.<br>Query:<font color=red>$query</font><br>Error: %s\n", $link->error);
		exit();
	}
	$num = $result->num_rows;
	if ($num == 0)
	{
		getStartYearAndMonth($firstYear, $firstMonth, $firstDay);
		getStopYearAndMonth($lastYear, $lastMonth, $lastDay);
		//echo "Keine Daten f&uuml;r den $day.$month.$year gefunden. Daten sind ab dem $firstDay.$firstMonth.$firstYear verf&uuml;gbar.";
		echo "No data found for the $day.$month.$year. Data are available between the $firstDay.$firstMonth.$firstYear and the $lastDay.$lastMonth.$lastYear.";
		return $num;
	}
	
	// Statistics
	$stat=statArray($result, $num, $day, $begin, $end);
		
	if($minute < 10)
	{
		$minute = "0" . $minute;
	}

	echo "<h2>{$text['statistics']} {$text['for']} {$text['the_she']} {$text['24_hours']} {$text['from']} $prevDay.$prevMon.$prevYear $hour:$minute{$text['uhr']} {$text['to']} $day.$month.$year $hour:$minute{$text['uhr']}.</h2><p>";
	links($showVal, $text);
	
	// Graphs
	graphs("24", "{$text['graphs']} {$text['24_hours']} {$text['before_date']} $day.$month.$year, $hour:$minute{$text['uhr']}.", $begin, $end, $text);
	
	// Average Table Header
	echo "<a name=\"avg\"></a>";
	echo "<h3>{$text['avg_values']} {$text['for_date']} $day.$month.$year.</h3><p>";
	valueTable($stat, "avg", $day, $month, $year, $text);

	// min values Table Header
	echo "<a name=\"minimal\"></a>";
	echo "<hr><h3>{$text['min_values']} {$text['for_date']} $day.$month.$year.</h3><p>";	
	valueTimeDateTable($stat, "min", "minTime", "minDate", $text);

	// max values Table Header
	echo "<a name=\"maximal\"></a>";
	echo "<hr><h3>{$text['max_values']} {$text['for_date']} $day.$month.$year.</h3><p>";	
	valueTimeDateTable($stat, "max", "maxTime", "maxDate", $text);
	
	echo "<a name=\"all\"></a>";
	if($showVal == "true")
	{
		// All Values Table Header
		echo "<hr><h3>{$text['all_values']} {$text['for_date']} $day.$month.$year.</h3><p>";	
		tableHeader($text);
	
		// All Values Table
		printTableRows($result);
		tableFooter($text);
	}
	else
	{
		echo "<hr><a href=\"24h.php?showVal=true&day=$day&month=$month&year=$year&hour=$hour&minute=$minute#all\">{$text['show_all_values']}</a>";
	}
	
	echo "<center>";
	echo "{$text['go']} <a href=\"24h.php?showVal=$showVal&day=$prevDay&month=$prevMon&year=$prevYear&hour=$hour&minute=$minute\" target=\"main\">{$text['24_hours_back']}</a> {$text['or']} ";
	echo "<a href=\"24h.php?showVal=$showVal&day=$nextDay&month=$nextMon&year=$nextYear&hour=$hour&minute=$minute\" target=\"main\">{$text['24_hours_forward']}</a><hr>";
	echo "</center>";
	
 	$result->free();
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

$showVal = $_REQUEST["showVal"];
$day =   $_REQUEST["day"];
$month = $_REQUEST["month"]; 
$year =  $_REQUEST["year"];
$hour = $_REQUEST["hour"];
$minute = $_REQUEST["minute"];

if($day[0] == 0)
	$day = $day[1];

if($month[0] == 0)
	$month = $month[1];

get24($day, $month, $year, $hour, $minute, $showVal, $text);

$link->close();
?>
