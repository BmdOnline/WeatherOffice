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
	include "weatherInclude.php";

	echo "<h1>Weather Office $WeatherOfficeVersion - {$text['weatherstation_in']} $STATION_NAME</h1>";
	echo "<p><b>Lat:</b> $STATION_LAT <b>Lon:</b> $STATION_LON</h2>"
?>
	</body>
</head>
