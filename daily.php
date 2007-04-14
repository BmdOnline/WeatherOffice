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
function getDay($day, $month, $year, $showVal, $text)
{
	$begin = convertTimestamp($day, $month, $year, 0, 0, 0);
	$end   = convertTimestamp($day, $month, $year, 23, 59, 59);
	
	// Header
	$prev = getdate(strtotime("-1 day", mktime(0, 0, 0, $month, $day, $year)));
	$next   = getdate(strtotime("+1 day", mktime(0, 0, 0, $month, $day, $year)));
	
	$prevDay = $prev['mday'];
	$nextDay = $next['mday'];
	$prevMon = $prev['mon'];
	$nextMon = $next['mon'];
	$prevYear = $prev['year'];
	$nextYear = $next['year'];
	
	$monthName = monthName($month, $text);
	$prevMonthName = monthName($prevMon, $text);
	$nextMonthName = monthName($nextMon, $text);
	
	echo "<a name=\"top\"></a>";
	echo "<center>";
	echo "{$text['go_to']}: <a href=\"daily.php?showVal=$showVal&day=$prevDay&month=$prevMon&year=$prevYear\" target=\"main\">$prevDay. $prevMonthName $prevYear</a> {$text['or']} ";
	echo "<a href=\"daily.php?showVal=$showVal&day=$nextDay&month=$nextMon&year=$nextYear\" target=\"main\">$nextDay. $nextMonthName $nextYear</a><hr>";
	echo "</center>";
	
	$query = "select * from weather where timestamp >= $begin and timestamp <= $end order by timestamp";
	$result = mysql_query($query) or die ("oneValue Abfrage fehlgeschlagen<br>Query:<font color=red>$query</font><br>Error:" . mysql_error());
	$num = mysql_num_rows($result);
	if ($num == 0)
	{
		echo "Keine Daten f&uuml;r den $day.$month.$year gefunden. Daten sind ab dem 28.11.2005 verf&uuml;gbar.";
		return $num;
	}
	
	// Statistics
	$stat=statArray($result, $num, $day, $begin, $end);
		
	echo "<h2>{$text['daily_overview']} {$text['for_date']} $day.$month.$year.</h2><p>";
	links($showVal, $text);
	
	// graps
	graphs("day", "{$text['graphs']} {$text['for_date']} $day.$month.$year.", $begin, $end, $text);
	
	// Average Table 	
	echo "<a name=\"avg\"></a>";
	echo "<h3>{$text['avg_values']} {$text['for_date']} $day.$month.$year.</h3><p>";
	valueTable($stat, "avg", $day, $month, $year, $text);

	// min values Table
	echo "<a name=\"minimal\"></a>";
	echo "<hr><h3>{$text['min_values']} {$text['for_date']} $day.$month.$year.</h3><p>";	
	valueTimeDateTable($stat, "min", "minTime", "minDate", $text);

	// max values Table
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
		echo "<hr><a href=\"daily.php?showVal=true&day=$day&month=$month&year=$year#all\">{$text['show_all_values']}</a>";
	}
	
	echo "<hr><center>";
	echo "{$text['go_to']}: <a href=\"daily.php?showVal=$showVal&day=$prevDay&month=$prevMon&year=$prevYear\" target=\"main\">$prevDay. $prevMonthName $prevYear</a> {$text['or']} ";
	echo "<a href=\"daily.php?showVal=$showVal&day=$nextDay&month=$nextMon&year=$nextYear\" target=\"main\">$nextDay. $nextMonthName $nextYear</a><hr>";
	echo "</center>";
	
 	mysql_free_result($result);
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

if($day[0] == 0)
	$day = $day[1];

if($month[0] == 0)
	$month = $month[1];

getDay($day, $month, $year, $showVal, $text);

mysql_close();
?>
