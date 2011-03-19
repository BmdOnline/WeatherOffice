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

function LeadingZero($value)
{
	if($value < 10)
		return ("0" . $value);
	else
		return (string)$value;
}

function GetCurrentDate()
{
	$today = getdate();
	$mon = LeadingZero($today['mon']);
	$mday = LeadingZero($today['mday']);
	
	return($today['year'] . "-" . $mon . "-" . $mday);
}

function GetCurrentTimestamp()
{
	$today = getdate();
	return GetTimestamp($today);
}

function GetTimestamp($today)
{
	$mon = LeadingZero($today['mon']);
	$mday = LeadingZero($today['mday']);
	$hour = LeadingZero($today['hours']);
	$min = LeadingZero($today['minutes']);
	$sec = LeadingZero($today['seconds']);
	
	return($today['year'] . $mon . $mday . $hour . $min . $sec);
}

include("weatherInclude.php");

$ts = GetCurrentTimestamp();

$id=$argv[1];

$result = SqlQuery("select filename, linenumber from additionalsensors where id=\"$id\"", false);
$filename = mysql_result($result, 0, 'filename');
$linenumber = mysql_result($result, 0, 'linenumber');

mysql_free_result($result);

$value = GetCurrentSensorValue($filename, $linenumber);	
			
SqlQuery("INSERT INTO additionalvalues Values($id,
							\"$ts\", 				
							\"$value\");", false);				
?>