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

	$tab="~T205";
	$tab2="~T380";

 	$database->close();

	echo "msgbox  title=\"${text['weatherstation_in']} $STATION_NAME $day.$month.$year $hour:$minute\" msg=\"";
	echo "${text['temperature']}$tab$values[temp_out]C $tab2 ${text['indoor']}: $values[temp_in]C~n";
	echo "${text['humidity']}$tab$values[rel_hum_out]% $tab2 ${text['indoor']}: $values[rel_hum_in]%~n";
	echo "${text['dewpoint']}$tab$values[dewpoint]C~n";
	echo "${text['windspeed']}$tab$windkmh km/h ($bftTxt)~n";
	echo "${text['winddir']}$tab$values[wind_direction] ($values[wind_angle])~n";
	echo "${text['windchill']}$tab$values[wind_chill] C~n";
	echo "${text['pressure']}$tab$values[rel_pressure] hPa  $tendName->$foreName~n";
	echo "${text['rain1h_table']}$tab$values[rain_1h] mm~n";
	echo "${text['rain24h_table']}$tab$values[rain_24h] mm~n";
	echo "${text['rainoverall_table']}$tab$values[rain_total] mm";
	echo "\" timeout=\"180\" select=\"OK\"";
	?>