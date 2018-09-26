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
function getFreeInput($beginDay, $beginMonth, $beginYear, $endDay, $endMonth, $endYear, $showVal, $text)
{
	global $link;
	$begin = convertTimestamp($beginDay, $beginMonth, $beginYear, 0, 0, 0);
	$end   = convertTimestamp($endDay, $endMonth, $endYear, 23, 59, 59);
	
	// Header
	echo "<a name=\"top\"></a>";

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
		echo "Keine Daten f&uuml;r den $day.$month.$year gefunden. Daten sind ab dem $firstDay.$firstMonth.$firstYear verf&uuml;gbar.";
		return $num;
	}
	
	// Statistics
	$stat=statArray($result, $num, $beginDay, $begin, $end);
		
	echo "<h2>{$text['statistics']} {$text['of']} $beginDay.$beginMonth.$beginYear {$text['to']} $endDay.$endMonth.$endYear.</h2><p>";
	links($showVal, $text);
	
	// graphs
	graphs("free", "{$text['graphs']} {$text['for_the_range_from']} $beginDay.$beginMonth.$beginYear {$text['to']} $endDay.$endMonth.$endYear.", $begin, $end, $text);
	
	// Average Table Header	
	echo "<a name=\"avg\"></a>";
	echo "<h3>{$text['avg_values']} {$text['for_the_range_from']} $beginDay.$beginMonth.$beginYear {$text['to']} $endDay.$endMonth.$endYear.</h3><p>";
	valueTable($stat, "avg", "--", "--", "--", $text);

	// min values Table Header
	echo "<a name=\"minimal\"></a>";
	echo "<hr><h3>{$text['min_values']} {$text['for_the_range_from']} $beginDay.$beginMonth.$beginYear {$text['to']} $endDay.$endMonth.$endYear.</h3><p>";	
	valueTimeDateTable($stat, "min", "minTime", "minDate", $text);

	// max values Table Header
	echo "<a name=\"maximal\"></a>";
	echo "<hr><h3>{$text['max_values']} {$text['for_the_range_from']} $beginDay.$beginMonth.$beginYear {$text['to']} $endDay.$endMonth.$endYear.</h3><p>";	
	valueTimeDateTable($stat, "max", "maxTime", "maxDate", $text);

	echo "<a name=\"all\"></a>";
	if($showVal == "true")
	{
		// All Values Table Header
		echo "<hr><h3>{$text['all_values']} {$text['for_the_range_from']} $beginDay.$beginMonth.$beginYear {$text['to']} $endDay.$endMonth.$endYear.</h3><p>";	
		tableHeader($text);
	
		// All Values Table
		printTableRows($result);
		tableFooter($text);
	}
	else
	{
		echo "<hr><a href=\"freeInput.php?showVal=true&beginDay=$beginDay&beginMonth=$beginMonth&beginYear=$beginYear&endDay=$endDay&endMonth=$endMonth&endYear=$endYear#all\">{$text['show_all_values']}</a>";
	}
	
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

$beginDay =   $_REQUEST["beginDay"];
$beginMonth = $_REQUEST["beginMonth"]; 
$beginYear =  $_REQUEST["beginYear"];
$endDay   =   $_REQUEST["endDay"];
$endMonth   = $_REQUEST["endMonth"]; 
$endYear   =  $_REQUEST["endYear"];
$showVal = $_REQUEST["showVal"];

getFreeInput($beginDay, $beginMonth, $beginYear, $endDay, $endMonth, $endYear, $showVal, $text);

$link->close();
?>
