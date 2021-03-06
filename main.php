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
include "weatherInclude.php";

function testComp($compWoffice)
{
	echo "$compWoffice";
	//Die Session initialisieren
	$ch = curl_init($compWoffice . "/main.php");
	$fp = fopen("/tmp/compWoffice.txt", "w");

	//Session Optionen setzen
	curl_setopt($ch, CURLOPT_FILE, $fp);
	curl_setopt($ch, CURLOPT_HEADER, 0);

	//Ausf�hren der Aktionen
	curl_exec($ch);

	//Session beenden
	curl_close($ch);
	fclose($fp);

	$fp = fopen("/tmp/compWoffice.txt", "r");
	fclose($fp);
}

	echo "<html>";
	echo "<head>";
	echo "<meta http-equiv=\"content-type\" content=\"text/html;charset=iso-8859-1\">";
	echo "<meta http-equiv=\"Refresh\" CONTENT=\"120\">";
	echo "<title>Weather</title>";
	echo "<link rel=\"stylesheet\" href=\"woffice.css\">";
	echo "</head>";
	echo "<body bgcolor=\"#d6e5ca\" marginheight=\"25\" marginwidth=\"20\" topmargin=\"25\" leftmargin=\"0\">";

	$timestamp = $database->getWeatherLastDate();
	if (!$timestamp) {
		exit();
	}

    $values = $database->getWeatherFromDate($timestamp);

	$day     = substr($timestamp, 6, 2);
	$month = substr($timestamp, 4, 2);
	$year    = substr($timestamp, 0, 4);
	$hour    = substr($timestamp, 8, 2);
	$minute = substr($timestamp, 10, 2);
	$second = substr($timestamp, 12, 4);

	$windkmh = $values['windspeed']*3.6;
	$bftTxt = beaufort($values['windspeed'], $lang);

	$diff = tendency($timestamp);

	$hrmin = hourMinute($hour, $minute, $text);

	$comfTxt = comfortText($values['temp_in'], $values['rel_hum_in'], $text);

	//testComp($COMP_WOFFICE);

	echo "<h2>{$text['current_values']} {$text['for_date']} $day.$month.$year in <span id=\"location\">$STATION_NAME</span>,
{$text['measured_at']} $hrmin</h2>";

	// Temperature and Humidity

	$absHum=absoluteHumidity($values['temp_out'],$values['rel_hum_out']);

	echo "<Table border=\"1\" valign=\"center\">";
	echo "<tr>";
	echo "<td colspan=\"2\"><b>{$text['temperature']} {$text['and']} {$text['humidity']}</b></td>";
	echo "<td colspan=\"2\"><b>{$text['tendency']}</b></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td>{$text['temperature']}</td>";
	echo "<td><span id=\"tempout\">$values[temp_out] &deg;C</span></td>";
	displayTendency($diff['temp_out'],"&deg;C/h", $text);
	echo "</tr>";
	echo "<tr>";
	echo "<td>{$text['humidity']}</td>";
	echo "<td>$values[rel_hum_out] % ($absHum g/m&sup3;)</td>";
	displayTendency($diff['rel_hum_out'],"%%/h", $text);
	echo "</tr>";

	echo "<tr>";
	echo "<td>{$text['dewpoint']}</td>";
	echo "<td>$values[dewpoint] &deg;C</td>";
	displayTendency($diff['dewpoint'],"&deg;C/h", $text);
	echo "</tr>";

	if($values['temp_out'] >= 27)
	{
		$heatIdx = heatIndex($values['temp_out'], $values['rel_hum_out'], $text);
		echo "<tr>";
		echo "<td colspan=\"1\">{$text['heat_index']}</td>";
		echo "<td colspan=\"3\">$heatIdx</td>";
		echo "</tr>";
	}

	// Wind
	if(isDisplayEnabled(DISPLAY_WIND_INFO))
	{
		echo "<tr>";
		echo "<td colspan=\"4\"><b>{$text['wind']}</b></td>";
		echo "</tr>";
		echo "<tr>";
		echo "<td>{$text['speed']}</td>";
		echo "<td>$windkmh km/h ($bftTxt) ($values[windspeed] m/s) {$text['from']} $values[wind_direction] ($values[wind_angle]) </td>";
				displayTendency($diff['windspeed'],"km/h", $text);
		echo "</tr>";
		echo "<tr>";
		echo "<td>{$text['windchill']}</td>";
		echo "<td>$values[wind_chill] &deg;C</td>";
				displayTendency($diff['wind_chill'],"&deg;C/h", $text);
		echo "</tr>";
  }
	// Air Pressure
	if(isDisplayEnabled(DISPLAY_PRES_INFO))
	{
		echo "<tr>";
		echo "<td colspan=\"4\"><b>{$text['pressure']}</b></td>";
		echo "</tr>";
		echo "<tr>";
		echo "<td>{$text['nn_pressure']}</td>";
		echo "<td>$values[rel_pressure] hPa</td>";
				displayTendency($diff['rel_pressure'],"hPa/h", $text);
		echo "</tr>";
		echo "<tr>";
		echo "<td>{$text['tendency']}</td>";
		$tendName = tendencyName($values['tendency'], $text);
		$foreName = forecastName($values['forecast'], $text);
		echo "<td>$tendName ({$text['forecast']}: $foreName)";
		echo "</td><td colspan=\"2\"><center>";
		forecastSymbol($values['forecast']);
		echo "</center></td></tr>";
	}
	// Precipitation
	if(isDisplayEnabled(DISPLAY_RAIN_INFO))
	{
		echo "<tr>";
		echo "<td colspan=\"4\"><b>{$text['precipitation']}</b></td>";
		echo "</tr>";
		echo "<tr>";
		echo "<td>{$text['last_hour']}</td>";
		echo "<td colspan=\"3\">$values[rain_1h] mm</td>";
		echo "</tr>";
		echo "<tr>";
		echo "<td>{$text['today']}</td>";
		echo "<td colspan=\"3\">$values[rain_24h] mm</td>";
		echo "</tr>";
		echo "<td>{$text['last_24_hours']}</td>";
		echo "<td colspan=\"3\">$diff[rain_last24] mm</td>";
		echo "</tr>";
		echo "<tr>";
		echo "<td>{$text['overall']}</td>";
		echo "<td colspan=\"3\">$values[rain_total] mm</td>";
		echo "</tr>";
	}

	if(phpMajorVersion()  < 5)
	{
	 require_once('sunriseSunset.php');
	 }

	 $prevTime = strtotime("-1 day", mktime($hour, $minute, $second, $month, $day, $year));
	 $sunRiseYesterday = date_sunrise($prevTime, SUNFUNCS_RET_STRING, $STATION_LAT, $STATION_LON, 90+5/6, date("O")/100);
	 $sunDownYesterday = date_sunset($prevTime, SUNFUNCS_RET_STRING, $STATION_LAT, $STATION_LON, 90+5/6, date("O")/100);

	  $sunRise = date_sunrise(time(), SUNFUNCS_RET_STRING, $STATION_LAT, $STATION_LON, 90+5/6, date("O")/100);
	  $sunDown = date_sunset(time(), SUNFUNCS_RET_STRING, $STATION_LAT, $STATION_LON, 90+5/6, date("O")/100);

	  echo "<tr>";
	  echo "<td colspan=\"4\"><b>{$text['sun_position']}</b></td>";
	  echo "</tr>";
	  echo "<tr>";
  	  echo "<td>{$text['sun_rise']}</td>";
	  echo "<td colspan=\"1\">$sunRise{$text['uhr']}</td>";
	  echo "<td colspan=\"2\">{$text['yesterday']} $sunRiseYesterday{$text['uhr']}</td>";
	  echo "</tr>";
	  echo "<tr>";
	  echo "<td>{$text['sun_down']}</td>";
	  echo "<td colspan=\"1\">$sunDown{$text['uhr']}</td>";
	  echo "<td colspan=\"2\">{$text['yesterday']} $sunDownYesterday{$text['uhr']}</td>";
	  echo "</tr>";


	// Indoor
	if(isDisplayEnabled(DISPLAY_ROOM_INFO))
	{
		$absHum=absoluteHumidity($values['temp_in'],$values['rel_hum_in']);

		echo "<tr>";
		echo "<td colspan=\"4\"><b>{$text['indoor']}</b></td>";
		echo "</tr>";
		echo "<tr>";
		echo "<td>{$text['temperature']}</td>";
		echo "<td colspan=\"1\">$values[temp_in] &deg;C</td>";
		displayTendency($diff['temp_in'],"&deg;C/h", $text);
		echo "</tr>";
		echo "<tr>";
		echo "<td>{$text['humidity']}</td>";
		echo "<td colspan=\"1\">$values[rel_hum_in] %   ($absHum g/m&sup3;)</td>";
		displayTendency($diff['rel_hum_in'],"%%/h", $text);
		echo "</tr>";
		echo "<tr>";
		echo "<td>{$text['comfort']}</td>";
		echo "<td colspan=\"3\">$comfTxt</td>";
		echo "</tr>";


	}

	if($values['temp_in'] >= 27)
	{
		$heatIdxIn = heatIndex($values['temp_in'], $values['rel_hum_in'], $text);
		echo "<tr>";
		echo "<td colspan=\"1\">{$text['heat_index']}</td>";
		echo "<td colspan=\"3\">$heatIdxIn</td>";
		echo "</tr>";
	}

	// Additional Sensors
	if($database->haveSensors(true))
	{
		echo "<tr>";
		echo "<td colspan=\"4\"><b>{$text['additionalSensors']}</b></td>";
		echo "</tr>";
		echo "<tr>";

		$database->listSensors(true);
		while($datarow = $database->getNextRow())
		{
			$name=$datarow['name'];
			$filename=$datarow['filename'];
			$linenumber=$datarow['linenumber'];
			$unit=$datarow['unit'];
			$value=GetCurrentSensorValue($filename, $linenumber);

			echo "<tr>";
			echo "<td colspan=\"1\">$name</td>";
			echo "<td colspan=\"3\">$value $unit</td>";
			echo "</tr>";
		}
		$database->free();
	}

	// End
	echo "</table>";

	$database->close();
?>

	</body>
</html>
