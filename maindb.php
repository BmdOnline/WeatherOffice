<?PHP
////////////////////////////////////////////////////
//
// WeatherOffice
//
// http://www.sourceforge.net/projects/weatheroffice
//
////////////////////////////////////////////////////
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
		
	$heatIdx = heatIndex($values['temp_out'], $values['rel_hum_out']);
	$heatIdxIn = heatIndex($values['temp_in'], $values['rel_hum_in']);

	$diff = tendency($timestamp);
	
	echo "Messwerte fuer den $day.$month.$year $hour:$minute in $STATION_NAME\n\n";
	
	// Temperatur und Feuchte
	echo "Temperatur        $values[temp_out]C  Innen: $values[temp_in]C\n";
	echo "Feuchtigkeit      $values[rel_hum_out]%   Innen: $values[rel_hum_in]%\n";
	echo "Taupunkt           $values[dewpoint]C\n";

	echo "\n";

	echo "Windgeschw.      $windkmh km/h ($bftTxt)\n";
	echo "Windrichtung     $values[wind_direction] ($values[wind_angle])\n";
	echo "Windkuehle        $values[wind_chill] C\n";

	echo "\n";

	echo "Luftdruck           $values[rel_pressure] hPa\n";
	
	$tendName = tendencyName($values['tendency'], $text);
	$foreName = forecastName($values['forecast'], $text);
	
	echo "Tendenz             $tendName -> $foreName\n";
	
	echo "\n";

	// Niederschlag
	echo "Regen Stunde    $values[rain_1h] mm\n";
	echo "Regen Heute      $values[rain_24h] mm\n";
	echo "Regen Gesamt   $values[rain_total] mm\n";
	
	mysql_close();
?>

