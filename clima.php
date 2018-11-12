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
	global $database;
	global $text;
	global $STATION_NAME, $STATION_LAT,  $STATION_LON;

	echo "<h2>{$text['Climate Table']}</h2>";

	# Objekt für Klimatafel anlegen
	$objClimateTable = new ClimateTable();


	$arrAvgTemp=MinMaxAvg::getRows('MONTH','temp_out_avg');
	$arrMaxTemp=MinMaxAvg::getRows('MONTH','temp_out_max');
	$arrMinTemp=MinMaxAvg::getRows('MONTH','temp_out_min');


	$database->getMinMaxAvgMeanValues();
	$database->seekRow(0);
	while($row = $database->getNextRow())
	{
		$arrMeanMaxTemp[] = $row['MEAN_TEMP_MAX'];
		$arrMeanMinTemp[] = $row['MEAN_TEMP_MIN'];
	}


	getStartYearAndMonth($firstYear, $firstMonth, $firstDay);
	getStopYearAndMonth($lastYear, $lastMonth, $lastDay);

	# Titelzeile festlegen
	$objClimateTable->setTitle("{$text['Monthly']}  {$text['Temperatures']} {$text['and']}  {$text['Precipations']} ($firstYear - $lastYear)");
	# Name der Station/ des Messortes festlegen (optional)
	$objClimateTable->setStationName($STATION_NAME);
	# Geografische Koordinaten der Station/ des Messortes festlegen (optional)
	$objClimateTable->setStationPlace("Lat: $STATION_LAT Lon: $STATION_LON");
	$objClimateTable->addRow("TEMP_AVG","{$text['avg_temp']} (&deg;C)",$arrAvgTemp);
	$objClimateTable->addRow("MEAN_TEMP_MAX","{$text['Average']} {$text['Maximum Temperature']} (&deg;C)",$arrMeanMaxTemp);
	$objClimateTable->addRow("MEAN_TEMP_MIN","{$text['Average']} {$text['Minimum Temperature']} (&deg;C)",$arrMeanMinTemp);
	$objClimateTable->addRow("TEMP_MAX","{$text['Historic']} {$text['Peak Value']} (&deg;C)",$arrMaxTemp);
	$objClimateTable->addRow("TEMP_MIN","{$text['Historic']} {$text['Lowest Value']} (&deg;C)",$arrMinTemp);

	if(isDisplayEnabled(DISPLAY_RAIN_INFO))
	{
		$arrAvgRainfall=MinMaxAvg::getRows('MONTH','rain_total_avg');

		$database->getMinMaxAvgRainDaysValues();
		$database->seekRow(0);
		while($row = $database->getNextRow())
		{
			$arrAvgRaindays[intval($row['MONTH'])-1] = $row['RAINDAYS_AVG'];
		}

		$objClimateTable->addRow("RAINFALL_AVG","{$text['Average']} {$text['precipation']} (mm)",$arrAvgRainfall);
		$objClimateTable->addRow("RAINDAYS_AVG","{$text['Raindays']} (d)",$arrAvgRaindays);
	}

	$objClimateTable->getTable();

}

function displayExtremas()
{
	global $text, $lang;

	echo "<h2>${text['Extreme values']}</h2>";

	$stat=MinMaxAvg::getExtremValues('DAY');
	$statMonth=MinMaxAvg::getExtremValues('YEARMONTH');

	printf("<p>");
	printf($text['messages']['max_temp_short'],
					dateLink($stat['temp_out_max']['maxDate']), $stat["temp_out_max"]['max']);
	printf($text['messages']['min_temp_short'],
					dateLink($stat['temp_out_min']['minDate']), $stat["temp_out_min"]['min']);
	printf("</p>");

	printf("<p>");
	printf($text['messages']['max_temp_day'],
					dateLink($stat['temp_out_avg']['maxDate']), $stat["temp_out_avg"]['max']);
	printf($text['messages']['min_temp_day'],
					dateLink($stat['temp_out_avg']['minDate']), $stat["temp_out_avg"]['min']);
	printf("</p>");

	printf("<p>");
	printf($text['messages']['max_temp_month'],
					monthLink($statMonth['temp_out_avg']['maxDate']), $statMonth["temp_out_avg"]['max']);
	printf($text['messages']['min_temp_month'],
					monthLink($statMonth['temp_out_avg']['minDate']), $statMonth["temp_out_avg"]['min']);
	printf("</p>");

	if(isDisplayEnabled(DISPLAY_PRES_INFO))
	{
		printf("<p>");
		printf($text['messages']['max_pressure_short'],
						dateLink($stat['rel_pressure_max']['maxDate']), $stat["rel_pressure_max"]['max']);
		printf($text['messages']['min_pressure_short'],
						dateLink($stat['rel_pressure_min']['minDate']), $stat["rel_pressure_min"]['min']);
		printf("</p>");
	}


	if(isDisplayEnabled(DISPLAY_RAIN_INFO))
	{
		printf("<p>");
		printf($text['messages']['max_rain_day'],
						dateLink($stat['rain_total_max']['maxDate']), $stat["rain_total_max"]['max']);

		printf($text['messages']['max_rain_month'],
						monthLink($statMonth['rain_total_max']['maxDate']), $statMonth["rain_total_max"]['max']);

		printf($text['messages']['min_rain_month'],
						monthLink($statMonth['rain_total_min']['minDate']), $statMonth["rain_total_min"]['min']);
		printf("</p>");
	}
}

function climaGraphs()
{
	global $text;

	echo "<h2>{$text['climagraph']} </h2>";

	echo "<p><img src=\"climaGraph.php?title=${text['avg_temp']}&col=temp_out\">";
	echo "<p><img src=\"climaGraph.php?title=${text['avg_prec']}&col=rain_total&unit=mm&avg=30\">";

	echo "<p><img src=\"climaGraph2D.php?title=${text['avg_temp']}&col=temp_out_avg\">";

	if(isDisplayEnabled(DISPLAY_RAIN_INFO))
	{
		echo "<p><img src=\"climaGraph2D.php?title=${text['precipation']}&col=rain_total_max\">";
	}

	if(isDisplayEnabled(DISPLAY_PRES_INFO))
	{
		echo "<p><img src=\"climaGraph2D.php?title=${text['pressure']}&col=rel_pressure_avg\">";
	}

	if(isDisplayEnabled(DISPLAY_WIND_INFO))
	{
		echo "<p><img src=\"climaGraph2D.php?title=${text['wind']}&col=windspeed_max\">";
	}
}

MinMaxAvg::updateDbTables(true); // Incremental update of DB Tables
climaTable();
displayExtremas();
climaGraphs();

$database->close();
?>
</html>
