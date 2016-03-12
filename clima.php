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

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//
// MAIN
//
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////

include("weatherInclude.php");

echo "<h2>{$text['climagraph']} </h2>";

echo "<p><img src=\"climaGraph.php?title=${text['avg_temp']}&col=temp_out\">";

if(isDisplayEnabled(DISPLAY_RAIN_INFO))
{
	echo "<p><img src=\"climaGraph.php?title=${text['avg_prec']}&col=rain_total&unit=mm&avg=30\">";
}

mysql_close();
?>
