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
// addSensor
//
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////


//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//
// MAIN
//
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////
//
// Data in weather (as stored by mysql2300)
//
// timestamp: uniqe bigint(14) in format YYYYMMDDhhmmss
//
//////////////////////////////////////////////////////////////////////
include("weatherInclude.php");

function GetNextSensorID()
{
	$query="SELECT max(id) FROM additionalsensors;";
	$result = mysql_query($query) or die ("Abfrage fehlgeschlagen<br>Query:<font color=red>$query</font><br>Error:" . mysql_error());
	$sensorID = mysql_result($result, 0) + 1;
	mysql_free_result($result);
	
	return $sensorID;
}

$table = "additionalsensors";

if(TableExists($table))
{

	$addSensor = $_REQUEST["addSensor"];

	if($addSensor == "false")
	{

		// Sensorentabelle
		echo "<hr><table border=\"1\"><tr>";
		echo "<th>" . $text['sensors']['id'] . "</th>";
		echo "<th>" . $text['sensors']['name'] . "</th>";
		echo "<th>" . $text['sensors']['filename'] . "</th>";
		echo "<th>" . $text['sensors']['linenumber'] . "</th>";
		echo "<th>" . $text['sensors']['value'] . "</th>";
		echo "<th>" . $text['sensors']['unit'] . "</th>";
		echo "<th>" . $text['sensors']['active'] . "</th></tr>";

			
		$result = SqlQuery("select * from $table ORDER BY id", false);
		$cnt=mysql_num_rows($result);
		for($i=0; $i<$cnt; $i++)
		{
			$id=mysql_result($result, $i, 'id');
			$name=mysql_result($result, $i, 'name');
			$filename=mysql_result($result, $i, 'filename');
			$linenumber=mysql_result($result, $i, 'linenumber');
			$unit=mysql_result($result, $i, 'unit');
			$active=mysql_result($result, $i, 'Active');
			$value=GetCurrentSensorValue($filename, $linenumber);
		
			echo "<tr>";
			echo "<td>$id</td>";
			echo "<td>$name</td>";
			echo "<td>$filename</td>";
			echo "<td>$linenumber</td>";
			echo "<td>$value</td>";
			echo "<td>$unit</td>";
			echo "<td>$active</td>";
			echo "</tr>";
		}
		mysql_free_result($result);
		
		echo "</table>";
		
		
		// New Sensor
		echo "<p><hr><b>" . $text['sensors']['new'] . ":</b></p>";
		echo "<form action = \"additionalSensors.php\" method=\"post\" target=\"main\">";

		echo " " . $text['sensors']['name'] . ": ";
		printf( "<input type = \"text\" size=\"15\" maxlenght=\"30\" name=\"name\" value=\"\">");

		echo " " . $text['sensors']['filename'] . ": ";
		printf( "<input type = \"text\" size=\"20\" maxlenght=\"255\" name=\"filename\" value=\"\">");

		echo " " . $text['sensors']['linenumber'] . ": ";
		printf( "<input type = \"text\" size=\"3\" name=\"linenumber\" value=\"\">");

		echo " " . $text['sensors']['unit'] . ": ";
		printf( "<input type = \"text\" size=\"7\"  maxlenght=\"20\" name=\"unit\" value=\"\">");

		echo " <input type = \"submit\" value = \"" . $text['sensors']['add'] . "\">";

		echo "<input type = \"hidden\" name=\"addSensor\" value=\"true\">";
		echo "</form>";
	}
	else
	{
		$name = $_REQUEST["name"];
		$filename = $_REQUEST["filename"];
		$linenumber = $_REQUEST["linenumber"];
		$unit = $_REQUEST["unit"];

		$newSensorId = GetNextSensorID();
		
		SqlQuery("INSERT INTO $table Values($newSensorId,
							\"$name\", 				
							\"$filename\", 
							\"$linenumber\",
							\"$unit\",1);", false);

		printf($text['sensors']['added'] . "<br>", $name);
	}
}
else
{
	printf($text['sensors']['no_table'] . "<br>", $table);
}

mysql_close();
?>
