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
//
// Mimimized Main Page to be displayed on a
// Dreambox Digital SAT-Receiver using Tuxweather
//
////////////////////////////////////////////////////
	include("weatherInclude.php");

	$_SERVER["HTTP_ACCEPT_LANGUAGE"]=$lang;

	$timestamp = $database->getWeatherLastDate();
 	$database->free();

	$values = $database->getWeatherFromDate($timestamp);
 	$database->free();

	$day     = substr($timestamp, 6, 2);
	$month = substr($timestamp, 4, 2);
	$year    = substr($timestamp, 0, 4);
	$hour    = substr($timestamp, 8, 2);
	$minute = substr($timestamp, 10, 2);
	$second = substr($timestamp, 12, 4);

	$windkmh = $values['windspeed']*3.6;
	$bftTxt = beaufort($values['windspeed'], $lang);

	$heatIdx = heatIndex($values['temp_out'], $values['rel_hum_out'], $text);
	$heatIdxIn = heatIndex($values['temp_in'], $values['rel_hum_in'], $text);

	$diff = tendency($timestamp);

	$comfTxt = comfortText($values['temp_in'], $values['rel_hum_in'], $text);
	$tendName = tendencyName($values['tendency'], $text);
	$foreName = forecastName($values['forecast'], $text);

 	$database->close();

	echo "$values[temp_out]";
?>