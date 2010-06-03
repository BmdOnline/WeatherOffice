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
	$_SERVER["HTTP_ACCEPT_LANGUAGE"]="de";
	
	include("weatherInclude.php");
	
	$query = "select max(timestamp) from weather";
	$result = mysql_query($query) or die ("Abfrage fehlgeschlagen<br>Query:<font color=red>$query</font><br>Error:" . mysql_error());
	$timestamp = mysql_result($result, 0);
	mysql_free_result($result);
	
	$query = "select * from weather where timestamp=$timestamp";
	$result = mysql_query($query) or die ("Abfrage fehlgeschlagen<br>Query:<font color=red>$query</font><br>Error:" . mysql_error());
	$values = mysql_fetch_array($result);
	mysql_free_result($result);
	
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
	
	echo "msgbox  title=\"Wetter in $STATION_NAME $day.$month.$year $hour:$minute\" msg=\"";
	echo "Temperatur$tab$values[temp_out]C $tab2 Innen: $values[temp_in]C~n";
	echo "Feuchtigkeit$tab$values[rel_hum_out]% $tab2 Innen: $values[rel_hum_in]%~n";
	echo "Taupunkt$tab$values[dewpoint]C~n";
	echo "Windgeschw.$tab$windkmh km/h ($bftTxt)~n";
	echo "Windrichtung$tab$values[wind_direction] ($values[wind_angle])~n";
	echo "Windkuehle$tab$values[wind_chill] C~n";
	echo "Luftdruck$tab$values[rel_pressure] hPa  $tendName->$foreName~n";
	echo "Regen 1h$tab$values[rain_1h] mm~n";
	echo "Regen 24h$tab$values[rain_24h] mm~n";
	echo "Regen Jahr$tab$values[rain_total] mm";	
	echo "\" timeout=\"180\" select=\"OK\"";
	
	mysql_close();
?>

