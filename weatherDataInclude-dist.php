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
?>
