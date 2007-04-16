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
	echo "<html>";
	echo "<head>";
	echo "<meta http-equiv=\"content-type\" content=\"text/html;charset=iso-8859-1\">";
	echo "<meta http-equiv=\"Refresh\" CONTENT=\"120\">";  
	echo "<title>Weather Webcam</title>";
	echo "<link rel=\"stylesheet\" href=\"woffice.css\">";
	echo "</head>";
	echo "<body bgcolor=\"#d6e5ca\" marginheight=\"25\" marginwidth=\"20\" topmargin=\"25\" leftmargin=\"0\">";

	include("weatherDataInclude.php");
	
	if($webcamType == "image")
	{
		echo "<img src=\"$weatherWebcamUrl\">";
	}
	else
	{
		echo "<hr><a href=$weatherWebcamUrl target=\"main\">Webcam</a>";
	}
	
?>

	</body>
</head>
