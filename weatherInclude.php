<?php
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
// 29.11.05 - created this header

	 // Define Some Constants that can be used in Config File

   define('DISPLAY_ROOM_INFO', 0x1);
   define('DISPLAY_WIND_INFO', 0x2);
   define('DISPLAY_RAIN_INFO', 0x4);
   define('DISPLAY_PRES_INFO', 0x8);

   $ConfDisplay = 0xffffffff;

   function SetDisplayValues($newValue)
   {
			global $ConfDisplay;
			$ConfDisplay = $newValue;
   }

   include("weatherDataInclude.php");

   // Version
   $WeatherOfficeVersion="1.1.5-dev";

   // Graph parameters
   $GraphWidth=850;
   $GraphHeight=300;
   $PolarWidth=400;
   $PolarHeight=425;

   $LineThickness=1.0;
   $PlotThickness=3.0;

   $LineColors=array("blue", "green", "red");
   $LineFillColors=array(null, null, null);
   $YearlyColors=array("yellow", "green", "red", "blue");
   $YearlyFillColors=array(null, null, null, null);
   $PolarDirColors=array("red", "black", "green", "black");
   $XAxisColors="black";
   $YAxisColors=array("blue", "red");
   $LegendColor="black";
   $LegendFillColor="white";
   $MarginColor="gray";
   $FrameColor="gray:0.25";

   // Retreive Language
   $gl=array();

   $gl = get_languages('data');
   $language=$gl[0][1];

   if($language != "de" && $language != "en" && $language != "fr")
   {
	   $language = "en";
	   $lang="en";
   }
   else
     $lang=$language;

   require_once 'language.php';
   require_once 'class.Database.php';


   //
   // connect to the database
   //
   $database = new DATABASE($weatherDatabaseHost, $weatherDatabaseUser, $weatherDatabasePW, $weatherDatabase);
   if (!$database->connected()) die();

   date_default_timezone_set($TimeZone);
   $now = time();

  function isDisplayEnabled($item)
	{
		global $ConfDisplay;

		if($item & $ConfDisplay)
			return true;
		else
			return false;
	}

 function phpMajorVersion()
 {
 	$version = explode('.', phpversion());
 	return (int)$version[0];
 }

function hourMinute($hour, $minute, $text)
{
	$str = "$hour:$minute{$text['uhr']}";
	return $str;
}

function celsius($tempF)
{
	return ((($tempF-32)*5)/9);
}

function fahrenheit($tempC)
{
	return ((($tempC*9)/5)+32);
}

function absoluteHumidity($temp, $relHum)
{
	$absHum = 0;
	$temp_k = $temp + 273.15;
	$gas_const = 8314.3;
	$mol_steam = 18.016;

	if ($temp >= 0 )
  {
    $a = 7.5;
    $b = 237.3;
  }
  else
  {
    $a = 7.6;
		$b = 240.7;
  }

	$sdd_1 = (($a * $temp)/($b + $temp));
	$sdd = 6.1078 * pow(10,$sdd_1);
	$dd = $relHum/100 * $sdd;

	$dd1 = ($dd / 6.1078);

	$dewPoint = log10($dd1);
	$absHum = 100000 * $mol_steam/$gas_const * $dd / $temp_k;

	$absHum = (round($absHum * 10)) / 10;

	return $absHum;
}

function heatIndex($temp, $hum, $text)
{
	if($temp < 27)
	{
		return $text['heat_index_not_relevant_temp'];
	}

	if($hum < 40)
	{
		return $text['heat_index_not_relevant_hum'];
	}

	$tempF = fahrenheit($temp);
	$tempF2 = pow($tempF, 2);
	$hum2 = pow($hum, 2);

	// formel aus der wiki (google "wiki hitzeindex")
	$hiF = -42.379 + 2.04901523*$tempF + 10.1433127*$hum + -0.22475541*$tempF*$hum + -0.00683783*$tempF2 +
		-0.05481717*$hum2 + 0.00122874*$tempF2*$hum + 0.00085282*$tempF*$hum2 + -0.00000199*$tempF2*$hum2;

	$hiD = celsius($hiF);

	if($hiD >= 54)
	{
		$hTxt = "<b>${text['extream_danger']}</b>";
	}
	else if ($hiD >= 41 && $hiD < 54)
	{
		$hTxt = "<b>${text['danger']}</b>";
	}
	else if($hiD >=32 && $hiD < 41)
	{
		$hTxt = "<b>${text['extream_caution']}</b>";
 	}
	else if($hiD >27 && $hiD< 32)
	{
		$hTxt = "<b>${text['caution']}</b>";
	}
	return (sprintf("%2.2f C - %s", $hiD, $hTxt));
}

function windDir($degree, $text)
{
	if(($degree >= 0 && $degree < 22.5) || ($degree >= 337.5 && $degree <=360))
	{
		return $text['north'];
	}
	else if($degree >= 22.5 && $degree < 67.5)
	{
		return $text['northeast'];
	}
	else if($degree >= 67.5 && $degree < 112.5)
	{
		return $text['east'];
	}
	else if($degree >= 112.5 && $degree < 157.5)
	{
		return $text['southeast'];
	}
	else if($degree >= 157.5 && $degree < 202.5)
	{
		return $text['south'];
	}
	else if($degree >= 202.5 && $degree < 247.5)
	{
		return $text['southwest'];
	}
	else if($degree >= 247.5 && $degree < 292.5)
	{
		return $text['west'];
	}
	else if($degree >= 292.5 && $degree < 337.5)
	{
		return $text['northwest'];
	}
	else
	{
		return $text['unknown'];
	}
}

function beaufort($windspeed, $lang)
{
    global $text;

	$bftLim = array(0.5, 2.1, 3.6, 5.7, 8.2, 11.3, 14.4,
							 17.5, 21.1, 24.7, 28.8, 32.9);

	$bft = 0;
	while ($bft < count($bftLim) && $windspeed >= $bftLim[$bft])
	{
		$bft++;
	}

	$txt = $bft . " bft - " . $text['beaufort'][$bft];
	return $txt;
}

function getRequest($key)
{
	global $_REQUEST;

	if (isset($_REQUEST[$key]))
		return $_REQUEST[$key];
	else
		return "";
}

function getTableColumns()
{
	$cols = array("temp_in");

	if(isDisplayEnabled(DISPLAY_ROOM_INFO))
	{
		array_push($cols,"temp_out");
	}
	array_push($cols,"dewpoint");

	if(isDisplayEnabled(DISPLAY_ROOM_INFO))
	{
		array_push($cols,"rel_hum_in");
	}

	array_push($cols,"rel_hum_out");

	if(isDisplayEnabled(DISPLAY_WIND_INFO))
	{
		array_push($cols,"windspeed", "wind_angle","wind_chill");
	}

	if(isDisplayEnabled(DISPLAY_PRES_INFO))
	{
		array_push($cols,"rel_pressure");
	}

	if(isDisplayEnabled(DISPLAY_RAIN_INFO))
	{
		array_push($cols, "rain_1h", "rain_24h", "rain_total");
	}

	return $cols;
}

function valueTable($stat, $value, $day, $month, $year, $text)
{
	tableHeader($text);
	echo "<tr>";
	printf("<td>$day.$month.$year</td>");
	printf("<td>--:--:--</td>");

	$cols = getTableColumns();

	foreach($cols as $column)
	{
		printf("<td>%2.2f</td>", $stat[$column][$value]);
	}

	echo "</tr>";
	tableFooter($text);
}

function valueTimeDateTable($stat, $value, $valueTime, $valueDate, $text)
{
	$cols = getTableColumns();

	tableHeader($text);
	echo "<tr>";
	printf("<td></td>");
	printf("<td></td>");

	foreach($cols as $column)
	{
		printf("<td>%2.2f</td>", $stat[$column][$value]);
	}

	echo "</tr>";
	echo "<tr>";
	printf("<td></td>");
	printf("<td>{$text['time']}</td>");

	foreach($cols as $column)
	{
		printf("<td>%s</td>", $stat[$column][$valueTime]);
	}

	echo "</tr>";
	echo "<tr>";

	printf("<td>{$text['date']}</td>");
	printf("<td></td>");
	foreach($cols as $column)
	{
		printf("<td>%s</td>", $stat[$column][$valueDate]);
	}
	echo "</tr>";
	tableFooter($text);
}

function timeDiff($time1, $time2, $text)
{
	printf("<p>Dauer fr %s: %d Sekunden.<br>", $text, $time2['sec']-$time1['sec']);
}

function dayLink($day, $month, $year)
{
	if(strlen($day)<2)
	{
		$day = "0" . $day;
	}
	return("<a href=daily.php?day=$day&month=$month&year=$year&showVal=false target=\"main\">$day</a>");
}

function dateLink($tag)
{
	$day = substr($tag, 6, 2);
	$month = substr($tag, 4, 2);
  $year = substr($tag, 0, 4);

	if(strlen($day)<2)
	{
		$day = "0" . $day;
	}
	return("<a href=daily.php?day=$day&month=$month&year=$year&showVal=false target=\"main\">$day.$month.$year</a>");
}

function monthLink($tag)
{
	global $text;

	$month = (int) substr($tag, 4, 2);
  $year = substr($tag, 0, 4);

  $monthText=monthName($month, $text);

	return("<a href=monthly.php?yearMonth=$year$month&showVal=false target=\"main\">$monthText $year</a>");
}


function statArray($num, $day, $startTime, $stopTime)
{
	global $database;
	$minimalCacheRows = 200;

	if($num > $minimalCacheRows)
	{
	  // huge rown number have a look into the cache
	  $database->store("cache");
	  $database->createTableCache();

	  $database->getCacheValues($startTime, $stopTime, $num, $day);
	  $cacheNum = $database->getRowsCount();
	  if($cacheNum > 0)
	  {
	   $database->seekRow(0);
	   $value = $database->getNextRow();
   	   $database->restore("cache");
	   $st=unserialize($value["value"]);

	   // update access time

   	   $database->updateCacheTime($startTime, $stopTime, $num, $day);
	   return $st;
	  } else {
   	   $database->restore("cache");
	  }
	}


	$cols = array("temp_in", "temp_out", "dewpoint", "rel_hum_in", "rel_hum_out", "windspeed", "wind_angle",
			"wind_chill", "rel_pressure", "rain_1h", "rain_24h", "rain_total");


	$database->seekRow(0);
	$row = $database->getNextRow();

	foreach ($cols as $column)
	{
		$sum[$column]       	= 0;
		$min[$column]       	= 999999;
		$max[$column]      	= -999999;
		$dayAvgMin[$column]	= 9999999;
		$dayAvgMax[$column] 	= -9999999;
		$daySum[$column]    	= 0;
		$dayNum[$column]    	= 0;
		$curDay[$column]        = $day;
		$prevDate[$column]      = "--.--.----";
		$prevVal[$column] 	= $row[$column];
		$dayAvgMaxDate[$column]	= "--.--.----";
		$dayAvgMinDate[$column] = "--.--.----";
		$dayDiffMaxDate[$column] = "--.--.----";
		$dayDiff[$column] 	= 0;
		$dayMax[$column] 	= -9999999;
		$dayMin[$column] 	= 99999999;

		$dayDiffMax[$column] 		= 0;
		$dayBeginVal[$column] 		= $row[$column];
		$zeroDiffValDays[$column] 	= 0;
		$zeroDiffValText[$column]	= "";
		$zeroMaxDays[$column] 		= 0;
		$zeroMaxDaysText[$column]	= "";
		$zeroMinDays[$column]		= 0;
		$zeroMinDaysText[$column]	= "";
		$summerDays[$column] 		= 0;
		$summerDaysText[$column]	= "";
		$heatDays[$column] 		= 0;
		$heatDaysText[$column]		= "";
		$tropicalNights[$column] 	= 0;
		$tropicalNightsText[$column]	= "";
	}

	$currentRow = 0;
	$database->seekRow(0);
	while($row = $database->getNextRow())
	{
		$currentRow++;

		foreach ($cols as $column)
		{
			$value[$column] = $row[$column];

			//printf("<b>Row $i: </b>Value: $value<br>");

			// Neuer Tag ?
			$rowDay[$column] = substr($row["timestamp"], 6, 2);
			$rowMonth[$column] = substr($row["timestamp"], 4, 2);
			$rowYear[$column] = substr($row["timestamp"], 0, 4);
			//echo "rowDay $rowDay, curDay $curDay<br>";
			if($rowDay[$column] != $curDay[$column] || $currentRow == $num)
			{
				// neuer Tag !!!
				// Oder aber die letzte Row passiert am ersten Tag des Monats

				// Berechne Durchschnitt, min und max speichern

				if($dayNum[$column] != 0)
					$dayAvg[$column] = $daySum[$column]/$dayNum[$column];
				else
					$dayAvg[$column] = 0;

				$dayNum[$column] = 1; // Erster Wert vom neuen Tag
				$daySum[$column] = $value[$column];

				$dayEndVal[$column] = $prevVal[$column];

				/*
				if($column == "rain_total")
				{
					printf("New Day: prevVal %s - dayDiff=dayEndVal:%s-dayBeginVal:%s<br>", $prevVal[$column], $dayEndVal[$column], $dayBeginVal[$column]);
				}
				*/

				$dayDiff[$column] = $dayEndVal[$column] - $dayBeginVal[$column];

				// Niederschlagsfreie Tage
				if($dayDiff[$column] == 0)
				{
					$zeroDiffValDays[$column]++;
					$zeroDiffValText[$column] = $zeroDiffValText[$column] . dayLink($curDay[$column], $rowMonth[$column], $rowYear[$column]) . ",";
				}

//				$curDay[$column] = $rowDay[$column];

				// Besondere Tage, abhaengig von der Temperatur
				if($column == "temp_out")
				{
					// Eistage (hoechsttemp. hoechstens 0
					if($dayMax[$column] <= 0)
					{
						$zeroMaxDays[$column]++;
						$zeroMaxDaysText[$column] = $zeroMaxDaysText[$column] . dayLink($rowDay[$column], $rowMonth[$column], $rowYear[$column]) . ",";
					}

					// Frosttage: Tiefsttemp. hoechstens o
					if($dayMin[$column] <= 0)
					{
						$zeroMinDays[$column]++;
						$zeroMinDaysText[$column] = $zeroMinDaysText[$column] . dayLink($rowDay[$column], $rowMonth[$column], $rowYear[$column]) . ",";
					}

					// Sommertage: Hoechsttemp. mind. 25
					if($dayMax[$column] >= 25)
					{
						$summerDays[$column]++;
						$summerDaysText[$column] = $summerDaysText[$column] . dayLink($rowDay[$column], $rowMonth[$column], $rowYear[$column]) . ",";
					}

					// Hitzetage: Hoechsttemp. mind. 30
					if($dayMax[$column] >= 30)
					{
						$heatDays[$column]++;
						$heatDaysText[$column] = $heatDaysText[$column] . dayLink($curDay[$column], $rowMonth[$column], $rowYear[$column]) . ",";
					}

					// Tropennaechte: Tiefsttemp. mind. 20
					if($dayMin[$column] >= 20)
					{
						$tropicalNights[$column]++;
						$tropicalNightsText[$column] = $tropicalNightsText[$column] . dayLink($rowDay[$column], $rowMonth[$column], $rowYear[$column]) . ",";
					}
				}

				$curDay[$column] = $rowDay[$column];

				$dayBeginVal[$column] = $value[$column];

				if($dayAvg[$column] > $dayAvgMax[$column])
				{
					$dayAvgMax[$column] = $dayAvg[$column];
					$dayAvgMaxDate[$column] = $prevDate[$column];
				}

				if($dayAvg[$column] < $dayAvgMin[$column])
				{
					$dayAvgMin[$column] = $dayAvg[$column];
					$dayAvgMinDate[$column] = $prevDate[$column];
				}

				if($dayDiff[$column] > $dayDiffMax[$column])
				{
					/*
					if($column == "rain_total")
					{
						printf("%s: dayDiff[col]: %s dayDiffMax[col]:%s<br>", $curDay[$column], $dayDiff[$column], $dayDiffMax[$column]);
					}
					*/

					$dayDiffMax[$column] = $dayDiff[$column];
					$dayDiffMaxDate[$column] = $prevDate[$column];
				}

				// Zurcksetzen der max werte fr den neuen Tag
				$dayMax[$column] = -9999999;
				$dayMin[$column] = 99999999;
			}
			else
			{
				// Kein neuer Tag
				$daySum[$column] += $value[$column];
				if($value[$column] > $dayMax[$column])
				{
					$dayMax[$column] = $value[$column];
				}
				if($value[$column] < $dayMin[$column])
				{
					$dayMin[$column] = $value[$column];
				}

				$dayNum[$column]++;
			}

			$sum[$column] += $value[$column];
			if($value[$column] < $min[$column])
			{
				$min[$column] = $value[$column];
				$minTime[$column] = $row["rec_time"];
				$minDate[$column] = $row["rec_date"];
			}
			if($value[$column] > $max[$column])
			{
				$max[$column] = $value[$column];
				$maxTime[$column] = $row["rec_time"];
				$maxDate[$column] = $row["rec_date"];
			}
			$prevDate[$column] = $row["rec_date"];
			$prevVal[$column]  = $value[$column];
		}
	}

	foreach ($cols as $column)
	{
		$st[$column]['sum'] = $sum[$column];
		$st[$column]['avg'] = $sum[$column]/$num;
		$st[$column]['minTime'] = $minTime[$column];
		$st[$column]['minDate'] = $minDate[$column];
		$st[$column]['min'] = $min[$column];
		$st[$column]['maxTime'] = $maxTime[$column];
		$st[$column]['maxDate'] = $maxDate[$column];
		$st[$column]['max'] = $max[$column];
		$st[$column]['maxDayAvg'] = $dayAvgMax[$column];
		$st[$column]['maxDayAvgDate'] = $dayAvgMaxDate[$column];
		$st[$column]['minDayAvg'] = $dayAvgMin[$column];
		$st[$column]['minDayAvgDate'] = $dayAvgMinDate[$column];
		$st[$column]['maxDayDiff'] = $dayDiffMax[$column];
		$st[$column]['maxDayDiffDate'] = $dayDiffMaxDate[$column];
		$st[$column]['zeroDiffValDays'] = $zeroDiffValDays[$column];
		$st[$column]['zeroDiffValText'] = substr($zeroDiffValText[$column], 0, strlen($zeroDiffValText[$column])-1);
		$st[$column]['zeroMaxDays'] = $zeroMaxDays[$column];
		$st[$column]['zeroMaxDaysText'] = substr($zeroMaxDaysText[$column], 0, strlen($zeroMaxDaysText[$column])-1);
		$st[$column]['zeroMinDays'] = $zeroMinDays[$column];
		$st[$column]['zeroMinDaysText'] = substr($zeroMinDaysText[$column], 0, strlen($zeroMinDaysText[$column])-1);
		$st[$column]['heatDays'] = $heatDays[$column];
		$st[$column]['heatDaysText'] = substr($heatDaysText[$column], 0, strlen($heatDaysText[$column])-1);
		$st[$column]['summerDays'] = $summerDays[$column];
		$st[$column]['summerDaysText'] = substr($summerDaysText[$column], 0, strlen($summerDaysText[$column])-1);
		$st[$column]['tropicalNights'] = $tropicalNights[$column];
		$st[$column]['tropicalNightsText'] = substr($tropicalNightsText[$column], 0, strlen($tropicalNightsText[$column])-1);
	}


	if($num > $minimalCacheRows) {
  	  $database->storeCacheValues($startTime, $stopTime, $num, $day, $st);
	}

	return $st;
}

function graphs($type, $title, $begin, $end, $text)
{
    global $database;

	echo "<a name=\"graph\"</a>";
	echo "<hr><h3>$title</h3><p>";

	echo "<p><img src=\"tripleLine.php?begin=$begin&end=$end&col1=temp_out&col2=dewpoint&col3=rel_hum_out&title=${text['temperature']}/${text['dewpoint']}/${text['humidity']}&unit1=%B0C&unit2=%B0C&unit3=%25&type=$type\">";

	if(isDisplayEnabled(DISPLAY_ROOM_INFO))
	{
		echo "<p><img src=\"tripleLine.php?begin=$begin&end=$end&col1=temp_out&col2=temp_in&col3=rel_hum_in&title=${text['outside_temperature']}/${text['inside_temperature']}/${text['inside_humidity']}&unit1=%B0C&unit2=%B0C&unit3=%25&type=$type\">";
	}

	if(isDisplayEnabled(DISPLAY_PRES_INFO))
	{
		echo "<p><img src=\"simpleLine.php?begin=$begin&end=$end&col=rel_pressure&title=${text['pressure']}&unit=hPa&type=$type\">";
	}

	if(isDisplayEnabled(DISPLAY_WIND_INFO))
	{
		echo "<p><img src=\"simpleLine.php?begin=$begin&end=$end&col=windspeed&title=${text['windspeed']}&unit=km/h&type=$type\">";
		echo "<p><img src=\"simpleLine.php?begin=$begin&end=$end&col=wind_angle&title=${text['winddir']}&unit=%B0&type=$type\">";
		echo "<p><img src=\"polar.php?begin=$begin&end=$end&col1=wind_angle&col2=windspeed&title=${text['winddist']}&unit=km/h&type=$type\">";
	}

	if(isDisplayEnabled(DISPLAY_RAIN_INFO))
	{
		echo "<p><img src=\"simpleLine.php?begin=$begin&end=$end&col=rain_1h&title=${text['precipitation']} 1h&unit=mm&type=$type\">";
		echo "<p><img src=\"simpleLine.php?begin=$begin&end=$end&col=rain_24h&title=${text['precipitation']} 24h&unit=mm&type=$type\">";
		echo "<p><img src=\"simpleLine.php?begin=$begin&end=$end&col=rain_total&title=${text['precipitation']} ${text['total']} &unit=mm&type=$type\">";
	}

	// Additional Sensors
	$database->store("sensors"); // store previous queries
	if($database->haveSensors(true))
	{
		$database->listSensors(true);
		while($datarow = $database->getNextRow())
		{
			$id=$datarow['id'];
			$name=$datarow['name'];
			$filename=$datarow['filename'];
			$linenumber=$datarow['linenumber'];
			$unit=$datarow['unit'];

			echo "<p><img src=\"simpleLine.php?begin=$begin&end=$end&col=as$id&title=$name&unit=$unit&type=$type\">";
		}
	}
	$database->restore("sensors"); // restore previous queries

	echo "<br><a href=\"#top\">{$text['to_top']}</a>";
	echo "<hr>";
}


function links($showVal, $text)
{
	echo "<a href=\"#graph\">{$text['graphs']}</a> ";
	echo "<a href=\"#avg\">{$text['avg_values']}</a> ";
	echo "<a href=\"#minimal\">{$text['min_values']}</a> ";
	echo "<a href=\"#maximal\">{$text['max_values']}</a> ";
	echo "<a href=\"#all\">{$text['all_values']}</a><p>";
}

function monthName($month, $text)
{
	switch ($month)
	{
		case 1: return "{$text['january']}";
		case 2: return "{$text['february']}";
		case 3: return "{$text['march']}";
		case 4: return "{$text['april']}";
		case 5: return "{$text['may']}";
		case 6: return "{$text['june']}";
		case 7: return "{$text['july']}";
		case 8: return "{$text['august']}";
		case 9: return "{$text['september']}";
		case 10: return "{$text['october']}";
		case 11: return "{$text['november']}";
		case 12: return "{$text['december']}";
	}
}

function encodeStringForGraph($text)
{
	return html_entity_decode($text, ENT_COMPAT, 'ISO-8859-1');
}

function tendencyName($tend, $text)
{
	switch($tend)
	{
		case "Falling": return $text['falling'];
		case "Rising": return $text['rising'];
		case "Steady": return $text['steady'];

		default: return $tend;
	}
}

function forecastName($fore, $text)
{
	switch($fore)
	{
		case "Cloudy": return $text['cloudy'];
		case "Sunny": return $text['sunny'];
		case "Rainy": return $text['rainy'];

		default: return $fore;
	}
}

function forecastSymbol($fore)
{
	echo "<IMG SRC=\"images/$fore.png\" ALT=\"$fore\">";
}


function convertTimestamp($day, $month, $year, $hour, $minute, $second)
{
	$timestamp = $year;
	if($month < 10)
		$timestamp = $timestamp . "0" . $month;
	else
		$timestamp = $timestamp . $month;

	if($day < 10)
		$timestamp = $timestamp . "0" . $day;
	else
		$timestamp = $timestamp . $day;

	if($hour < 10)
		$timestamp = $timestamp . "0" . $hour;
	else
		$timestamp = $timestamp . $hour;

	if($minute < 10)
		$timestamp = $timestamp . "0" . $minute;
	else
		$timestamp = $timestamp . $minute;

	if($second < 10)
		$timestamp = $timestamp . "0" . $second;
	else
		$timestamp = $timestamp . $second;

	return (string)$timestamp;
}

function tableHeader($text)
{
	echo "<Table border=\"1\">";
	echo "<tr>";
	echo "<td><b>{$text['date']}</b></td>";
	echo "<td><b>{$text['time']}</b></td>";
	if(isDisplayEnabled(DISPLAY_ROOM_INFO))
	{
		echo "<td><b>{$text['temp_in_table']}</b></td>";
	}

	echo "<td><b>{$text['temp_out_table']}</b></td>";
	echo "<td><b>{$text['dew_table']}</b></td>";

	if(isDisplayEnabled(DISPLAY_ROOM_INFO))
	{
		echo "<td><b>{$text['hum_in_table']}</b></td>";
	}

	echo "<td><b>{$text['hum_out_table']}</b></td>";

	if(isDisplayEnabled(DISPLAY_WIND_INFO))
	{
		echo "<td><b>{$text['windspeed_table']}</b></td>";
		echo "<td><b>{$text['windangle_table']}</b></td>";
		echo "<td><b>{$text['windchill_table']}</b></td>";
	}

	if(isDisplayEnabled(DISPLAY_PRES_INFO))
	{
		echo "<td><b>{$text['airpressure_table']}</b></td>";
	}

	if(isDisplayEnabled(DISPLAY_RAIN_INFO))
	{
		echo "<td><b>{$text['rain1h_table']}</b></td>";
		echo "<td><b>{$text['rain24h_table']}</b></td>";
		echo "<td><b>{$text['rainoverall_table']}</b></td>";
	}

	echo "</tr>";
}

function tableFooter($text)
{
	echo "</table><p>";
	echo "<a href=\"#top\">{$text['to_top']}</a>";
}


function printTableRows($database)
{
	$database->seekRow(0);
	while($row = $database->getNextRow())
	{
		printf("<tr><td>%s</td><td>%s</td>", $row["rec_date"],$row["rec_time"]);

		if(isDisplayEnabled(DISPLAY_ROOM_INFO))
		{
			printf("<td>%2.2f</td>",$row["temp_in"]);
		}

		printf("<td>%2.2f</td><td>%2.2f</td>",$row["temp_out"],$row["dewpoint"]);

		if(isDisplayEnabled(DISPLAY_ROOM_INFO))
		{
			printf("<td>%2.2f</td>",$row["rel_hum_in"]);
		}

		printf("<td>%2.2f</td>",$row["rel_hum_out"]);

		if(isDisplayEnabled(DISPLAY_WIND_INFO))
		{
			printf("<td>%2.2f</td><td>%2.2f</td><td>%2.2f</td>",$row["windspeed"],$row["wind_angle"], $row["wind_chill"]);
		}

		if(isDisplayEnabled(DISPLAY_PRES_INFO))
		{
			printf("<td>%2.2f</td>",$row["rel_pressure"]);
		}

		if(isDisplayEnabled(DISPLAY_RAIN_INFO))
		{
			printf("<td>%2.2f</td><td>%2.2f</td><td>%2.2f</td></tr>",	$row["rain_1h"],$row["rain_24h"],	$row["rain_total"]);
		}

	}
}


function diffTime($timestamp, $diff)
{
	$day    = substr($timestamp, 6, 2);
	$month  = substr($timestamp, 4, 2);
	$year   = substr($timestamp, 0, 4);
	$hour   = substr($timestamp, 8, 2);
	$minute = substr($timestamp, 10, 2);
	$second = substr($timestamp, 12, 4);

  $newTime = getdate(strtotime($diff, mktime($hour, $minute, $second, $month, $day, $year)));

	$newtimestamp = convertTimestamp($newTime['mday'],$newTime['mon'],$newTime['year'],
	       $newTime['hours'],$newTime['minutes'],$newTime['seconds']);

	return $newtimestamp;
}

function diffTimestamps($t1, $t2)
{
	$timestamp=$t1;

	$day    = substr($timestamp, 6, 2);
	$month  = substr($timestamp, 4, 2);
	$year   = substr($timestamp, 0, 4);
	$hour   = substr($timestamp, 8, 2);
	$minute = substr($timestamp, 10, 2);
	$second = substr($timestamp, 12, 4);

	$time1 = mktime($hour, $minute, $second, $month, $day, $year);

	$timestamp=$t2;

	$day    = substr($timestamp, 6, 2);
	$month  = substr($timestamp, 4, 2);
	$year   = substr($timestamp, 0, 4);
	$hour   = substr($timestamp, 8, 2);
	$minute = substr($timestamp, 10, 2);
	$second = substr($timestamp, 12, 4);

	$time2 = mktime($hour, $minute, $second, $month, $day, $year);

	return $time2 - $time1;
}

function getStartYearAndMonth(&$year, &$month, &$day)
{
	global $database;

	$timestamp = $database->getWeatherFirstDate();
	if ($timestamp) {
		$day   = substr($timestamp, 6, 2);
		$month = substr($timestamp, 4, 2);
		$year  = substr($timestamp, 0, 4);
	}
}

function getStopYearAndMonth(&$year, &$month, &$day)
{
	global $database;

	$timestamp = $database->getWeatherLastDate();
	if ($timestamp) {
		$day   = substr($timestamp, 6, 2);
		$month = substr($timestamp, 4, 2);
		$year  = substr($timestamp, 0, 4);
	}
}

function tendency($timestamp)
{
	global $database;
	$starttime = diffTime($timestamp, "-65 minutes");
	$stoptime = diffTime($timestamp, "-55 minutes");


	$oldValues = $database->getWeatherFromPeriod($starttime, $stoptime, 1); // Only first result
	$curValues = $database->getWeatherFromDate($timestamp);

	if($oldValues)
	{
		//$diff = array_combine(array_keys($curValues), array_map(function ($x, $y) { return $y-$x; } , $oldValues, $curValues));
		$diff = array_combine(array_keys($curValues), array_map(function ($x, $y) { return is_numeric($x)?$y-$x:0; } , $oldValues, $curValues));

		$starttime = diffTime($timestamp, "-1450 minutes");
		$stoptime = diffTime($timestamp, "-1435 minutes");

		$oldValues = $database->getWeatherFromPeriod($starttime, $stoptime, 1); // Only first result

		$diff['rain_last24'] = $curValues['rain_total'] - $oldValues['rain_total'];
	}
	else
		$diff=0;

	return $diff;
}

function displayTendency($value, $unit, $text)
{

   if($value > 0.0)
   {
     printf ("<td>+%.1f  $unit</td>",$value);
     echo "<td><IMG SRC=\"images/up.png\" ALT=\"up\"></td>";
   }
   else if($value < 0.0)
   {
      printf ("<td>%.1f  $unit</td>",$value);
     echo "<td><IMG SRC=\"images/down.png\" ALT=\"down\"></td>";
   }
   else
     echo "<td>{$text['stable']}</td><td><IMG SRC=\"images/equal.png\" ALT=\"equal\"></td>";


}

/******************************************
this will return an array composed of a 4 item array for each language the os supports
1. full language abbreviation, like en-ca
2. primary language, like en
3. full language string, like English (Canada)
4. primary language string, like English
*******************************************/

// choice of redirection header or just getting language data
// to call this you only need to use the $feature parameter
function get_languages( $feature, $spare='' )
{
	// get the languages
	$a_languages = languages();
	$index = '';
	$complete = '';
	$found = false;// set to default value
	//prepare user language array
	$user_languages = array();

	//check to see if language is set
	if ( isset( $_SERVER["HTTP_ACCEPT_LANGUAGE"] ) )
	{
		//explode languages into array
		$languages = strtolower( $_SERVER["HTTP_ACCEPT_LANGUAGE"] );
		$languages = explode( ",", $languages );

		foreach ( $languages as $language_list )
		{
			// pull out the language, place languages into array of full and primary
			// string structure:
			$temp_array = array();
			// slice out the part before ; on first step, the part before - on second, place into array
			$temp_array[0] = substr( $language_list, 0, strcspn( $language_list, ';' ) );//full language
			$temp_array[1] = substr( $language_list, 0, 2 );// cut out primary language
			//place this array into main $user_languages language array
			$user_languages[] = $temp_array;
		}

		//start going through each one
		for ( $i = 0; $i < count( $user_languages ); $i++ )
		{
			foreach ( $a_languages as $index => $complete )
			{
				if ( $index == $user_languages[$i][0] )
				{
					// complete language, like english (canada)
					$user_languages[$i][2] = $complete;
					// extract working language, like english
					$user_languages[$i][3] = substr( $complete, 0, strcspn( $complete, ' (' ) );
				}
			}
		}
	}
	else// if no languages found
	{
		$user_languages[0] = array( '','','','' ); //return blank array.
	}

	// return parameters
	if ( $feature == 'data' )
	{
		return $user_languages;
	}

	// this is just a sample, replace target language and file names with your own.
	elseif ( $feature == 'header' )
	{
		switch ( $user_languages[0][1] )// get default primary language, the first one in array that is
		{
			case 'en':
				$location = 'english.php';
				$found = true;
				break;
			case 'sp':
				$location = 'spanish.php';
				$found = true;
				break;
			default:
				break;
		}
		if ( $found )
		{
			header("Location: $location");
		}
		else// make sure you have a default page to send them to
		{
			header("Location: default.php");
		}
	}
}

function languages()
{
// pack abbreviation/language array
// important note: you must have the default language as the last item in each major language, after all the
// en-ca type entries, so en would be last in that case
	$a_languages = array(
	'af' => 'Afrikaans',
	'sq' => 'Albanian',
	'ar-dz' => 'Arabic (Algeria)',
	'ar-bh' => 'Arabic (Bahrain)',
	'ar-eg' => 'Arabic (Egypt)',
	'ar-iq' => 'Arabic (Iraq)',
	'ar-jo' => 'Arabic (Jordan)',
	'ar-kw' => 'Arabic (Kuwait)',
	'ar-lb' => 'Arabic (Lebanon)',
	'ar-ly' => 'Arabic (libya)',
	'ar-ma' => 'Arabic (Morocco)',
	'ar-om' => 'Arabic (Oman)',
	'ar-qa' => 'Arabic (Qatar)',
	'ar-sa' => 'Arabic (Saudi Arabia)',
	'ar-sy' => 'Arabic (Syria)',
	'ar-tn' => 'Arabic (Tunisia)',
	'ar-ae' => 'Arabic (U.A.E.)',
	'ar-ye' => 'Arabic (Yemen)',
	'ar' => 'Arabic',
	'hy' => 'Armenian',
	'as' => 'Assamese',
	'az' => 'Azeri',
	'eu' => 'Basque',
	'be' => 'Belarusian',
	'bn' => 'Bengali',
	'bg' => 'Bulgarian',
	'ca' => 'Catalan',
	'zh-cn' => 'Chinese (China)',
	'zh-hk' => 'Chinese (Hong Kong SAR)',
	'zh-mo' => 'Chinese (Macau SAR)',
	'zh-sg' => 'Chinese (Singapore)',
	'zh-tw' => 'Chinese (Taiwan)',
	'zh' => 'Chinese',
	'hr' => 'Croatian',
	'cs' => 'Czech',
	'da' => 'Danish',
	'div' => 'Divehi',
	'nl-be' => 'Dutch (Belgium)',
	'nl' => 'Dutch (Netherlands)',
	'en-au' => 'English (Australia)',
	'en-bz' => 'English (Belize)',
	'en-ca' => 'English (Canada)',
	'en-ie' => 'English (Ireland)',
	'en-jm' => 'English (Jamaica)',
	'en-nz' => 'English (New Zealand)',
	'en-ph' => 'English (Philippines)',
	'en-za' => 'English (South Africa)',
	'en-tt' => 'English (Trinidad)',
	'en-gb' => 'English (United Kingdom)',
	'en-us' => 'English (United States)',
	'en-zw' => 'English (Zimbabwe)',
	'en' => 'English',
	'us' => 'English (United States)',
	'et' => 'Estonian',
	'fo' => 'Faeroese',
	'fa' => 'Farsi',
	'fi' => 'Finnish',
	'fr-be' => 'French (Belgium)',
	'fr-ca' => 'French (Canada)',
	'fr-lu' => 'French (Luxembourg)',
	'fr-mc' => 'French (Monaco)',
	'fr-ch' => 'French (Switzerland)',
	'fr' => 'French (France)',
	'mk' => 'FYRO Macedonian',
	'gd' => 'Gaelic',
	'ka' => 'Georgian',
	'de-at' => 'German (Austria)',
	'de-li' => 'German (Liechtenstein)',
	'de-lu' => 'German (lexumbourg)',
	'de-ch' => 'German (Switzerland)',
	'de' => 'German (Germany)',
	'el' => 'Greek',
	'gu' => 'Gujarati',
	'he' => 'Hebrew',
	'hi' => 'Hindi',
	'hu' => 'Hungarian',
	'is' => 'Icelandic',
	'id' => 'Indonesian',
	'it-ch' => 'Italian (Switzerland)',
	'it' => 'Italian (Italy)',
	'ja' => 'Japanese',
	'kn' => 'Kannada',
	'kk' => 'Kazakh',
	'kok' => 'Konkani',
	'ko' => 'Korean',
	'kz' => 'Kyrgyz',
	'lv' => 'Latvian',
	'lt' => 'Lithuanian',
	'ms' => 'Malay',
	'ml' => 'Malayalam',
	'mt' => 'Maltese',
	'mr' => 'Marathi',
	'mn' => 'Mongolian (Cyrillic)',
	'ne' => 'Nepali (India)',
	'nb-no' => 'Norwegian (Bokmal)',
	'nn-no' => 'Norwegian (Nynorsk)',
	'no' => 'Norwegian (Bokmal)',
	'or' => 'Oriya',
	'pl' => 'Polish',
	'pt-br' => 'Portuguese (Brazil)',
	'pt' => 'Portuguese (Portugal)',
	'pa' => 'Punjabi',
	'rm' => 'Rhaeto-Romanic',
	'ro-md' => 'Romanian (Moldova)',
	'ro' => 'Romanian',
	'ru-md' => 'Russian (Moldova)',
	'ru' => 'Russian',
	'sa' => 'Sanskrit',
	'sr' => 'Serbian',
	'sk' => 'Slovak',
	'ls' => 'Slovenian',
	'sb' => 'Sorbian',
	'es-ar' => 'Spanish (Argentina)',
	'es-bo' => 'Spanish (Bolivia)',
	'es-cl' => 'Spanish (Chile)',
	'es-co' => 'Spanish (Colombia)',
	'es-cr' => 'Spanish (Costa Rica)',
	'es-do' => 'Spanish (Dominican Republic)',
	'es-ec' => 'Spanish (Ecuador)',
	'es-sv' => 'Spanish (El Salvador)',
	'es-gt' => 'Spanish (Guatemala)',
	'es-hn' => 'Spanish (Honduras)',
	'es-mx' => 'Spanish (Mexico)',
	'es-ni' => 'Spanish (Nicaragua)',
	'es-pa' => 'Spanish (Panama)',
	'es-py' => 'Spanish (Paraguay)',
	'es-pe' => 'Spanish (Peru)',
	'es-pr' => 'Spanish (Puerto Rico)',
	'es-us' => 'Spanish (United States)',
	'es-uy' => 'Spanish (Uruguay)',
	'es-ve' => 'Spanish (Venezuela)',
	'es' => 'Spanish (Traditional Sort)',
	'sx' => 'Sutu',
	'sw' => 'Swahili',
	'sv-fi' => 'Swedish (Finland)',
	'sv' => 'Swedish',
	'syr' => 'Syriac',
	'ta' => 'Tamil',
	'tt' => 'Tatar',
	'te' => 'Telugu',
	'th' => 'Thai',
	'ts' => 'Tsonga',
	'tn' => 'Tswana',
	'tr' => 'Turkish',
	'uk' => 'Ukrainian',
	'ur' => 'Urdu',
	'uz' => 'Uzbek',
	'vi' => 'Vietnamese',
	'xh' => 'Xhosa',
	'yi' => 'Yiddish',
	'zu' => 'Zulu' );

	return $a_languages;
}

function comfortText($temp, $hum, $text)
{
	// Comfort: If Temperature/Humidity combo is in the area spanned by the x/y points, the temperature is comfy
	// Return value of contains: 0 means point is not in area
	$xpoints = array( 17.7, 22.3, 24.4, 18.6);
	$ypoints = array( 75, 63, 35, 38);
	$npoints = 4;
	if(contains($temp, $hum, $xpoints, $ypoints, $npoints) == 0)
	{
		// Sill comfortable in this area:
		$xpoints = array(16, 16.9, 20.5, 24.8, 27, 25.8, 19.9, 17);
		$ypoints = array(75, 87, 80, 62, 32, 19, 19.9, 38);
		$npoints = 8;
		if(contains($temp, $hum, $xpoints, $ypoints, $npoints) == 0)
		{
			// Awkward
			if($hum > 75)
			{
				$comfyText=$text['awkwardly_wet'];
			}
			else if($hum < 25)
			{
				$comfyText=$text['awkwardly_dry'];
			}
			else
			{
				$comfyText=$text['awkward'];
			}
		}
		else
		{
			// Still comfy
			$comfyText=$text['still_comfy'];
		}
	}
	else
	{
		// Comfy
		$comfyText=$text['comfy'];
	}
	return $comfyText;
}

function contains($x, $y, $xpoints, $ypoints, $npoints)
{
	$wn = 0;

	$x1 = $xpoints[$npoints - 1];
	$y1 = $ypoints[$npoints - 1];

	$x2 = $xpoints[0];
	$y2 = $ypoints[0];

	$startUeber = $y1 >= $y ? true : false;

	for ($i=1; $i< $npoints; $i++)
	{
		$endUeber = $y2 >= $y ? true : false;

		if ($startUeber != $endUeber)
		{
			if (($y2 - $y) * ($x2 - $x1) <= ($y2 - $y1) * ($x2 - $x))
			{
				if ($endUeber)
				{
					$wn++;
				}
			}
			else
			{
				if (!$endUeber)
				{
					$wn--;
				}
			}
		}

		$startUeber = $endUeber;

		$y1 = $y2;

		$x1 = $x2;

		$x2 = $xpoints[$i];

		$y2 = $ypoints[$i];
	}

	return ($wn);
}

function rainbowColor($idx)
{
	$alpha = "@0.25";
	if($idx < 25)
		return sprintf('#%02X%02X%02X%s"', 255,$idx*10.2,0,$alpha);
	else if($idx < 50)
		return sprintf('#%02X%02X%02X%s', 255-(($idx-25)*10.2),255,0,$alpha);
	else if($idx < 75)
		return sprintf('#%02X%02X%02X%s', 0,255-(($idx-50)*10.2),($idx-50)*10.2,$alpha);
	else if ($idx <= 100)
		return sprintf('#%02X%02X%02X%s', ($idx-75)*10.2,0,255,$alpha);
	else
		return sprintf('#%02X%02X%02X%s', 0,0,0,$alpha);
}

function GetCurrentSensorValue($filename, $linenumber)
{
    global $text;
	if(file_exists($filename) == false)
	{
		$value = $text['sensors']['unavailable'];
	}
	else
	{
		$value = $text['sensors']['available'];

		$handle = fopen($filename, "r");

		for($i=0; $i<$linenumber; $i++)
		{
			$value = fgets($handle);
		}

		fclose($handle);
	}

	return $value;
}

?>
