<?PHP
////////////////////////////////////////////////////
//
// WeatherOffice
//
// http://www.sourceforge.net/projects/weatheroffice
//
////////////////////////////////////////////////////
	header("Content-type: text/xml");

	// Microsummary for Firefox 2.0 Bookmarks http://wiki.mozilla.org/Microsummaries

	echo"<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
	
	include("weatherInclude.php");
	
	$query = "select max(timestamp) from weather";
	$result = mysql_query($query) or die ("Abfrage fehlgeschlagen<br>Query:<font color=red>$query</font><br>Error:" . mysql_error());
	$timestamp = mysql_result($result, 0);
	mysql_free_result($result);
	
	$query = "select * from weather where timestamp=$timestamp";
	$result = mysql_query($query) or die ("Abfrage fehlgeschlagen<br>Query:<font color=red>$query</font><br>Error:" . mysql_error());
	$values = mysql_fetch_array($result);

	$day     = substr($timestamp, 6, 2);
	$month = substr($timestamp, 4, 2);
	$year    = substr($timestamp, 0, 4);
	$hour    = substr($timestamp, 8, 2);
	$minute = substr($timestamp, 10, 2);
	$second = substr($timestamp, 12, 4);

	$windkmh = $values['windspeed']*3.6;

	mysql_free_result($result);


	echo"<generator xmlns=\"http://www.mozilla.org/microsummaries/0.1\" name=\"Weather Info\">";
	echo"<template><transform xmlns=\"http://www.w3.org/1999/XSL/Transform\" version=\"1.0\"><output method=\"text\"/><template match=\"/\">";
	echo"<text>$STATION_NAME: $values[temp_out]C $windkmh km/h - $hour:$minute</text>";
	echo"</template></transform></template>";
      echo "</generator>";
	mysql_close();

      
?>
	  
      