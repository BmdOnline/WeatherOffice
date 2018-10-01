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
// getWeek
//
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function getWeek($day, $month, $year, $showVal, $text)
{
	global $link;
	
	// Header
	$prevBegin = getdate(strtotime("-13 days", mktime(0, 0, 0, $month, $day, $year)));
	$prevEnd   = getdate(strtotime("-7 days", mktime(0, 0, 0, $month, $day, $year)));
	$nextBegin = getdate(strtotime("+1 day", mktime(0, 0, 0, $month, $day, $year)));
	$nextEnd   = getdate(strtotime("+7 days", mktime(0, 0, 0, $month, $day, $year)));
		
	$wbegin = getdate(strtotime("-6 days", mktime(0, 0, 0, $month, $day, $year)));
	$begin = convertTimestamp($wbegin['mday'], $wbegin['mon'], $wbegin['year'], 0, 0, 0);
	$end   = convertTimestamp($day, $month, $year, 23, 59, 59);
	echo "<a name=\"top\"></a>";
	echo "<center>";
	echo "{$text['go_to']}: <a href=\"weekly.php?showVal=$showVal&day={$prevEnd['mday']}&month={$prevEnd['mon']}&year={$prevEnd['year']}\" target=\"main\">{$prevBegin['mday']}.{$prevBegin['mon']}.{$prevBegin['year']} {$text['to']} {$prevEnd['mday']}.{$prevEnd['mon']}.{$prevEnd['year']}</a> {$text['or']} ";
	echo "         <a href=\"weekly.php?showVal=$showVal&day={$nextEnd['mday']}&month={$nextEnd['mon']}&year={$nextEnd['year']}\" target=\"main\">{$nextBegin['mday']}.{$nextBegin['mon']}.{$nextBegin['year']} {$text['to']} {$nextEnd['mday']}.{$nextEnd['mon']}.{$nextEnd['year']}</a><hr>";
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
	$stat=statArray($result, $num, $wbegin['mday'], $begin, $end);
		
	echo "<h2>{$text['weekly_overview']} {$text['for']} {$text['week_of']} {$wbegin['mday']}.{$wbegin['mon']}.{$wbegin['year']} {$text['to']} $day.$month.$year.</h2><p>";
	links($showVal, $text);
	
	// Graphs
	graphs("week", "{$text['graphs']} {$text['for']} {$text['week_of']} {$wbegin['mday']}.{$wbegin['mon']}.{$wbegin['year']} {$text['to']} $day.$month.$year.", $begin, $end, $text);
	
	// Average Table Header
	echo "<a name=\"avg\"></a>";
	echo "<h3>{$text['avg_values']} {$text['for']} {$text['week_of']} {$wbegin['mday']}.{$wbegin['mon']}.{$wbegin['year']} {$text['to']} $day.$month.$year.</h3><p>";
	valueTable($stat, "avg", "--", "--", "--", $text);

	// min values Table Header
	echo "<a name=\"minimal\"></a>";
	echo "<hr><h3>{$text['min_values']} {$text['for']} {$text['week_of']} {$wbegin['mday']}.{$wbegin['mon']}.{$wbegin['year']} {$text['to']} $day.$month.$year.</h3><p>";	
	valueTimeDateTable($stat, "min", "minTime", "minDate", $text);

	// max values Table Header
	echo "<a name=\"maximal\"></a>";
	echo "<hr><h3>{$text['max_values']} {$text['for']} {$text['week_of']} {$wbegin['mday']}.{$wbegin['mon']}.{$wbegin['year']} {$text['to']} $day.$month.$year.</h3><p>";	
	valueTimeDateTable($stat, "max", "maxTime", "maxDate", $text);
	
	echo "<a name=\"all\"></a>";
	if($showVal == "true")
	{
		// All Values Table Header
	
		echo "<hr><h3>{$text['all_values']} {$text['for']} {$text['week_of']} {$wbegin['mday']}.{$wbegin['mon']}.{$wbegin['year']} {$text['to']} $day.$month.$year.</h3><p>";	
		tableHeader($text);
	
		// All Values Table
		printTableRows($result);
		tableFooter($text);
	}
	else
	{
		echo "<hr><a href=\"weekly.php?showVal=true&day=$day&month=$month&year=$year#all\">{$text['show_all_values']}</a>";
	}
	
	echo "<hr><center>";
	echo "{$text['go_to']}: <a href=\"weekly.php?showVal=$showVal&day={$prevEnd['mday']}&month={$prevEnd['mon']}&year={$prevEnd['year']}\" target=\"main\">{$prevBegin['mday']}.{$prevBegin['mon']}.{$prevBegin['year']} {$text['to']} {$prevEnd['mday']}.{$prevEnd['mon']}.{$prevEnd['year']}</a> {$text['or']} ";
	echo "         <a href=\"weekly.php?showVal=$showVal&day={$nextEnd['mday']}&month={$nextEnd['mon']}&year={$nextEnd['year']}\" target=\"main\">{$nextBegin['mday']}.{$nextBegin['mon']}.{$nextBegin['year']} {$text['to']} {$nextEnd['mday']}.{$nextEnd['mon']}.{$nextEnd['year']}</a><hr>";
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
$day =     $_REQUEST["day"];
$month = $_REQUEST["month"]; 
$year =  $_REQUEST["year"];


getWeek($day, $month, $year, $showVal, $text);

$link->close();
?>
