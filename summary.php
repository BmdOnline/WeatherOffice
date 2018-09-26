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
	header("Content-type: text/xml");

	// Microsummary for Firefox 2.0 Bookmarks http://wiki.mozilla.org/Microsummaries

	echo"<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
	
	include("weatherInclude.php");
	
	$query = "select max(timestamp) from weather";
	$result = $link->query($query);
	if (!$result) {
        	printf("Query Failed.<br>Query:<font color=red>$query</font><br>Error: %s\n", $link->error);
	        exit();
	}
	$datarow = $result->fetch_array();
	$timestamp = $datarow[0];
 	$result->free();
	
	$query = "select * from weather where timestamp=$timestamp";
	$result = $link->query($query);
	if (!$result) {
        	printf("Query Failed.<br>Query:<font color=red>$query</font><br>Error: %s\n", $link->error);
	        exit();
	}
	$values = $result->fetch_array();

	$day     = substr($timestamp, 6, 2);
	$month = substr($timestamp, 4, 2);
	$year    = substr($timestamp, 0, 4);
	$hour    = substr($timestamp, 8, 2);
	$minute = substr($timestamp, 10, 2);
	$second = substr($timestamp, 12, 4);

	$windkmh = $values['windspeed']*3.6;

 	$result->free();


	echo"<generator xmlns=\"http://www.mozilla.org/microsummaries/0.1\" name=\"Weather Info\">";
	echo"<template><transform xmlns=\"http://www.w3.org/1999/XSL/Transform\" version=\"1.0\"><output method=\"text\"/><template match=\"/\">";
	echo"<text>$STATION_NAME: $values[temp_out]C $windkmh km/h - $hour:$minute</text>";
	echo"</template></transform></template>";
        echo "</generator>";
 	$link->close();

      
?>
	  
      
