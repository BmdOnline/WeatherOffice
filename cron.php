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

MinMaxAvg::createDbTable();

MinMaxAvg::updateDbTable("DAY");
MinMaxAvg::updateDbTable("YEARMONTH");
MinMaxAvg::updateDbTable("MONTH");
MinMaxAvg::updateDbTable("YEAR");

mysql_close();
?>
