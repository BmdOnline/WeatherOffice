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

MinMaxAvg::updateDbTables(false); // Force a full update

$database->close();
?>
