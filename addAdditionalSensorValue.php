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

$result = $database->getSensorFromId($id);
$database->free();

$active = $result['Active'];
if($active)
{
  $filename = $result['filename'];
  $linenumber = $result['linenumber'];

  $value = GetCurrentSensorValue($filename, $linenumber);
  $database->addSensorsValue($id, $ts, $value);
}
$database->close();

?>
