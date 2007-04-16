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
include("weatherInclude.php");
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">
<html>
<head>
<?php
echo "<title>Weather Office Home - $STATION_NAME</title>";
?>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=ISO-8859-1">
<META HTTP-EQUIV="Refresh" CONTENT="300">  
<link rel="stylesheet" href="woffice.css">
<link rel="microsummary" type="application/x.microsummary+xml" href="summary.php">
</head>

<frameset rows="*,46" cols="*">
   <frameset rows="*" cols="220, *">
      <frameset rows="160, *" cols="1*">
         <frame name="logo" scrolling="no"     marginwidth="0" marginheight="0" src="logo.html" noresize frameborder="0">
         <frame name="ctrl" scrolling="auto"   marginwidth="2" marginheight="0" src="weather.php"  noresize frameborder="0">
      </frameset>

      <frameset rows="78, *" cols="1*">
         <frame name="header" scrolling="no"   marginwidth="10" marginheight="14" src="header.php?lang=<?= $language ?>" noresize frameborder="0">
         <frame name="main" scrolling="auto" marginwidth="10" marginheight="14" src="main.php?lang=<?= $language ?>" noresize frameborder="0">
      </frameset>
   </frameset>

   <frameset rows="*" cols="*">
      <frame name="footer" scrolling="no"   marginwidth="1" marginheight="1" src="footer.php" noresize frameborder="0">
   </frameset>
   <noframes>
       <body>
         <p>You need a browser that supports frame to veiw this page.</p>
      </body>
   </noframes>
</frameset>
<body>
</html>
