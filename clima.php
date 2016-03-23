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

include("weatherInclude.php");
include("class.MinMaxAvg.php");
include("class.climatetable.php");

function climaTable()
{
	global $text;
	global $STATION_NAME, $STATION_LAT,  $STATION_LON;
	
	echo "<h2>Klimatafel</h2>";

	# Objekt für Klimatafel anlegen
	$objClimateTable = new ClimateTable();


	$arrAvgTemp=MinMaxAvg::getRows('MONTH','temp_out_avg');
	$arrMaxTemp=MinMaxAvg::getRows('MONTH','temp_out_max');
	$arrMinTemp=MinMaxAvg::getRows('MONTH','temp_out_min');


	$query= "SELECT substr(DAY,5,2)AS MONTH,".
					"ROUND(AVG(DAY_TEMP_MAX),1) AS MEAN_TEMP_MAX,".
					"ROUND(AVG(DAY_TEMP_MIN),1) AS MEAN_TEMP_MIN ".
					"FROM (SELECT substr(timestamp, 1,8) as DAY, ".
					"MAX(temp_out_max) AS DAY_TEMP_MAX, ".
					"MIN(temp_out_min) AS DAY_TEMP_MIN  ".
					"FROM MinMaxAvg WHERE Type='DAY' GROUP BY substr(timestamp, 1,8) )AS T1 group by substr(DAY,5,2)";

	$result = mysql_query($query) or die ("oneValue Abfrage fehlgeschlagen<br>Query:<font color=red>$query</font><br>Error:" . mysql_error());
	while($row = mysql_fetch_array($result, MYSQL_ASSOC))
	{	
		$arrMeanMaxTemp[] = $row['MEAN_TEMP_MAX'];
		$arrMeanMinTemp[] = $row['MEAN_TEMP_MIN'];
	}

	
	getStartYearAndMonth($firstYear, $firstMonth, $firstDay);
	getStopYearAndMonth($lastYear, $lastMonth, $lastDay);
	
	# Titelzeile festlegen
	$objClimateTable->setTitle("Monatliche Durchschnittstemperaturen und -niederschl&auml;ge ($firstYear - $lastYear)");
	# Name der Station/ des Messortes festlegen (optional)
	$objClimateTable->setStationName($STATION_NAME);
	# Geografische Koordinaten der Station/ des Messortes festlegen (optional)
	$objClimateTable->setStationPlace("Lat: $STATION_LAT Lon: $STATION_LON");
	$objClimateTable->addRow("TEMP_AVG","Mittlere Temperatur (&deg;C)",$arrAvgTemp);
	$objClimateTable->addRow("MEAN_TEMP_MAX","Mittlere H&ouml;chsttemperatur (&deg;C)",$arrMeanMaxTemp);
	$objClimateTable->addRow("MEAN_TEMP_MIN","Mittlere Tiefsttemperatur (&deg;C)",$arrMeanMinTemp);
	$objClimateTable->addRow("TEMP_MAX","Historischer H&ouml;chstwert (&deg;C)",$arrMaxTemp);
	$objClimateTable->addRow("TEMP_MIN","Historischer Tiefstwert (&deg;C)",$arrMinTemp);
	
	if(isDisplayEnabled(DISPLAY_RAIN_INFO))
	{
		$arrAvgRainfall=MinMaxAvg::getRows('MONTH','rain_total_avg');

		$query = "SELECT SUBSTR(YYYYDD,5,2) AS MONTH, ".
						"ROUND(AVG(RAINDAYS)) AS RAINDAYS_AVG ".
						"FROM (SELECT COUNT(DAY)AS RAINDAYS, SUBSTR(DAY,1,6) AS YYYYDD " .
						"FROM (	SELECT DAY, RAINFALL FROM (select substr(timestamp,1,8) AS DAY, type AS Type, rain_total_max AS rainfall ".
						"FROM MinMaxAvg group by substr(timestamp,1,8)) AS T1 WHERE Type='DAY' and rainfall > 0) AS T2 ".
						"GROUP BY SUBSTR(DAY,1,6)) AS T3 GROUP BY SUBSTR(YYYYDD,5,2) ORDER BY SUBSTR(YYYYDD,5,2);";

		$result = mysql_query($query) or die ("oneValue Abfrage fehlgeschlagen<br>Query:<font color=red>$query</font><br>Error:" . mysql_error());

		while($row = mysql_fetch_array($result, MYSQL_ASSOC))
		{	
			$arrAvgRaindays[intval($row['MONTH'])-1] = $row['RAINDAYS_AVG'];
		}
		
		$objClimateTable->addRow("RAINFALL_AVG","Mittlerer Niederschlag (mm)",$arrAvgRainfall);
		$objClimateTable->addRow("RAINDAYS_AVG","Regentage (d)",$arrAvgRaindays);	
	}
	
	$objClimateTable->getTable();

}

function displayExtremas()
{
	global $text;

	echo "<h2>Extremas </h2>";

	$stat=MinMaxAvg::getExtremValues('DAY');

	printf("Die <b>h&ouml;chste Temperatur</b> wurde am <b>%s</b> mit <b>%2.2f &deg;C</b> gemessen.<br>",
					dateLink($stat['temp_out_max']['maxDate']), $stat["temp_out_max"]['max']);
	printf("Die <b>niedrigste Temperatur</b> trat am <b>%s</b> mit <b>%2.2f &deg;C</b> auf.<br><br>",	    	
					dateLink($stat['temp_out_min']['minDate']), $stat["temp_out_min"]['min']);
	printf("Der <b>w&auml;rmste Tag</b> war der <b>%s</b> mit einer Durchschnittstemperatur von <b>%2.2f &deg;C</b>.<br>",				
					dateLink($stat['temp_out_avg']['maxDate']), $stat["temp_out_avg"]['max']);
	printf("Der <b>k&auml;lteste Tag</b> war der <b>%s</b> mit durchschnittlich <b>%2.2f &deg;C</b>.<br><br>",				
					dateLink($stat['temp_out_avg']['minDate']), $stat["temp_out_avg"]['min']);

	printf("Der <b>h&ouml;chste Luftdruck</b> wurde am <b>%s</b> mit <b>%2.2f hPa</b> gemessen.<br>",
					dateLink($stat['rel_pressure_max']['maxDate']), $stat["rel_pressure_max"]['max']);		
	printf("Der <b>niedrigste Luftdruck</b> kam am <b>%s</b> mit <b>%2.2f hPa</b> vor.<br><br>",				
					dateLink($stat['rel_pressure_min']['minDate']), $stat["rel_pressure_min"]['min']);		
																
	$statMonth=MinMaxAvg::getExtremValues('YEARMONTH');

	printf("Der <b>w&auml;rmste Monat</b> war der <b>%s</b> mit einer Durchschnittstemperatur von <b>%2.2f &deg;C</b>.<br>",				
					monthLink($statMonth['temp_out_avg']['maxDate']), $statMonth["temp_out_avg"]['max']);

	printf("Der <b>k&auml;lteste Monat</b> war der <b>%s</b> mit durchschnittlich <b>%2.2f &deg;C</b>.<br><br>",				
					monthLink($statMonth['temp_out_avg']['minDate']), $statMonth["temp_out_avg"]['min']);

	if(isDisplayEnabled(DISPLAY_RAIN_INFO))
	{
		printf("Der <b>niederschlagsreichste Tag</b> war der <b>%s</b> mit <b>%2.2f mm</b>.<br>",					
						dateLink($stat['rain_total_max']['maxDate']), $stat["rain_total_max"]['max']);	
						
		printf("Der <b>niederschlagsreichste Monat</b> war der <b>%s</b> mit <b>%2.2f mm</b>.<br>",					
						monthLink($statMonth['rain_total_max']['maxDate']), $statMonth["rain_total_max"]['max']);						
						
		printf("Der <b>trockenste Monat</b> war der <b>%s</b> mit <b>%2.2f mm</b>.<br><br>",					
						monthLink($statMonth['rain_total_min']['minDate']), $statMonth["rain_total_min"]['min']);								
	}
}

function climaGraphs()
{
	global $text;

	echo "<h2>{$text['climagraph']} </h2>";

	echo "<p><img src=\"climaGraph2D.php?title=${text['avg_temp']}&col=temp_out_avg\">";

	if(isDisplayEnabled(DISPLAY_RAIN_INFO))
	{
		echo "<p><img src=\"climaGraph2D.php?title=${text['precipation']}&col=rain_total_max\">";
	}
}

MinMaxAvg::updateDbTables(true); // Incremental update of DB Tables
climaTable();
displayExtremas();
climaGraphs();

mysql_close();
?>
</html>
