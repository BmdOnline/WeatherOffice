<?php

//////////////////////////////////////////
//
// Edit this file to meet your environment
//
//////////////////////////////////////////

// Database Name
   $weatherDatabase    = "open2300";
// Database Host
   $weatherDatabaseHost= "127.0.0.1";
// Database User
   $weatherDatabaseUser= "mySqlUser";
// Password of database user   
   $weatherDatabasePW  = "mySqlUserPassword";
   
// Start year and month of data in the database
   $startYear=2005;
   $startMonth=11;
   
// Your Weather station name (city)   
   $STATION_NAME = "Hinterdupfing";

// Lattitude and Longitude of the weather station in decimal degrees
   $STATION_LAT = 48.93;
   $STATION_LON = 12.13;

   // 
   // You can add a webcam of your area to the menu in the left frame
   //
   // Webcam Type "page" or "image"
   //
   $webcamType = "image";
   $weatherWebcamUrl ="http://zucki.homeip.net/snap.jpg";
    
  
// This function contains some web links shown on the bottom of the left frame   
function weatherWebLinks()
{
	echo "<hr>";
	echo "<a href=\"http://weather.homeip.net\" target=\"_blank\">Wetterstation Regensburg</a><br>";
	echo "<a href=\"http://wetter.homeuix.net\" target=\"_blank\">Wetterstation Neubeuern</a><br>";
}

function longTermAverage($month)
{
	// This function contains an array of long therm averages for temperature and rain for your area
	// Example Values are for Regensburg. Excelsheets containing values for Germany can be 
	// obtained from DWD (Deutscher Wetterdienst, German Weather Service):
	// http://www.dwd.de/de/FundE/Klima/KLIS/daten/online/nat/index_mittelwerte.htm
	$averageTemp = array(-2.1, -0.4, 3.6, 8.1, 12.9, 16.2, 17.9, 17.2, 13.7, 8.4, 2.9, -0.6);
	$averageRain   = array(44, 38, 38, 45, 58, 76, 70, 72, 49, 45, 50, 51);
	$average['temp'] = $averageTemp[$month -1];
	$average['rain'] = $averageRain[$month -1];
	return $average;
}
?>
