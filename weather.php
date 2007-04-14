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
////////////////////////////////////////////////////




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

	$today = getdate();

	//
	// Webcam
	//
	if($webcamType == "image")
	{
		echo "<a href=\"webcam.php\" target=\"main\">Webcam</a>";
	}
	else
	{
		echo "<a href=\"$weatherWebcamUrl\" target=\"main\">Webcam</a>";
	}

	//
	// Current Values
	//
	echo "<hr><a href=\"main.php?lang={$language}   \" target=\"main\">{$text['current_values']}</a>";

	//
	// Last 24 Hours
	//
	echo "<p><hr><a href=\"24h.php?showVal=false&day={$today['mday']}&month={$today['mon']}&year={$today['year']}&hour={$today['hours']}&minute={$today['minutes']}\" target=\"main\">{$text['last_24_hours']}</a>";

	//
	// Daily Overview
	//
	echo "<hr><p><b>{$text['daily_overview']}</b></p>";
	echo "<form action = \"daily.php\" method=\"post\" target=\"main\">";
	echo "<select name=\"day\">";
	
	for($i=1; $i<=31; $i++)
	{
		if($i == $today['mday'])
		{
			echo "<option value=\"$i\" selected> $i";
		}
		else
		{
			echo "<option value=\"$i\"> $i";
		}
	}

	echo "</select>.";
	echo "<select name=\"month\">";

	for($i=1; $i<=12; $i++)
	{
		if($i == $today['mon'])
		{
			echo "<option value=\"$i\" selected> $i";
		}
		else
		{
			echo "<option value=\"$i\"> $i";
		}
	}

	echo "</select>.";
	echo "<select name=\"year\">";

	for($i=2005; $i<=2008; $i++)
	{
		if($i == $today['year'])
		{
			echo "<option value=\"$i\" selected> $i";
		}
		else
		{
			echo "<option value=\"$i\"> $i";
		}
	}

	echo "</select>";
	echo "<p><input type = \"submit\" value = \"OK\">";
	echo "<input type = \"hidden\" name=\"showVal\" value=\"false\">";
	echo "</form>";

	//
	// Weekly Overview
	//
	echo "<p><hr><a href=\"weekly.php?showVal=false&day={$today['mday']}&month={$today['mon']}&year={$today['year']}\" target=\"main\">{$text['weekly_overview']}</a>";
	
	// 
	// Monthly Overview
	//
	echo "<p><hr><b>{$text['monthly_overview']}</b></p>";
	echo "<form action = \"monthly.php\" method = \"post\" target=\"main\">";
	echo "<select name=\"yearMonth\">";

	$today = getdate();

	
	for($curYear=$startYear; $curYear <= $today['year']; $curYear++)
	{	
		for($curMonth=1; $curMonth <= 12; $curMonth ++)
		{
			$curMonthName=monthName($curMonth, $text);
			if(($curYear == $startYear && $curMonth < $startMonth) ||
			   ($curYear == $today['year'] && $curMonth > $today['mon']))
			{
				// Do nothing
			}
			else if($curMonth == $today['mon'] && $curYear == $today['year'])
			{
				echo "<option value=\"$curYear$curMonth\" selected> $curMonthName $curYear";
			}
			else
			{
				echo "<option value=\"$curYear$curMonth\"> $curMonthName $curYear";
			}
		}
	}
	
	echo "</select>";
	echo "<p><input type = \"submit\" value = \"OK\">";
	echo "<input type = \"hidden\" name=\"showVal\" value=\"false\">";
	echo "<input type = \"hidden\" name=\"lng\" value=\"$language\">";
	echo "</form>";
	
	// 
	// Yearly Overview
	//
	echo "<p><hr><b>{$text['yearly_overview']}</b></p>";
	echo "<form action = \"yearly.php\" method = \"post\" target=\"main\">";
	echo "<select name=\"year\">";

	$today = getdate();
	$startMonth = 11;
	$startYear = 2005;
	
	for($curYear=$startYear; $curYear <= $today['year']; $curYear++)
	{	
		if($curYear == $today['year'])
		{
			echo "<option value=\"$curYear\" selected>  $curYear";
		}
		else
		{
			echo "<option value=\"$curYear\"> $curYear";
		}
		
	}
	
	echo "</select>";
	echo "  <input type = \"submit\" value = \"OK\">";
	echo "<input type = \"hidden\" name=\"showVal\" value=\"false\">";
	echo "</form>";
	
	//
	// Range
	//
	echo "<p><hr><b>{$text['range']}</b></p>";
	echo "<form action = \"freeInput.php\" method=\"post\" target=\"main\">";
	echo "<select name=\"beginDay\">";
	
	for($i=1; $i<=31; $i++)
	{
		if($i == $today['mday'])
		{
			echo "<option value=\"$i\" selected> $i";
		}
		else
		{
			echo "<option value=\"$i\"> $i";
		}
	}

	echo "</select>.";
	echo "<select name=\"beginMonth\">";

	for($i=1; $i<=12; $i++)
	{
		if($i == $today['mon'])
		{
			echo "<option value=\"$i\" selected> $i";
		}
		else
		{
			echo "<option value=\"$i\"> $i";
		}
	}

	echo "</select>.";
	echo "<select name=\"beginYear\">";

	for($i=2005; $i<=2008; $i++)
	{
		if($i == $today['year'])
		{
			echo "<option value=\"$i\" selected> $i";
		}
		else
		{
			echo "<option value=\"$i\"> $i";
		}
	}

	echo "</select><p>";
	
		echo "<select name=\"endDay\">";
	
	for($i=1; $i<=31; $i++)
	{
		if($i == $today['mday'])
		{
			echo "<option value=\"$i\" selected> $i";
		}
		else
		{
			echo "<option value=\"$i\"> $i";
		}
	}

	echo "</select>.";
	echo "<select name=\"endMonth\">";

	for($i=1; $i<=12; $i++)
	{
		if($i == $today['mon'])
		{
			echo "<option value=\"$i\" selected> $i";
		}
		else
		{
			echo "<option value=\"$i\"> $i";
		}
	}

	echo "</select>.";
	echo "<select name=\"endYear\">";

	for($i=2005; $i<=2008; $i++)
	{
		if($i == $today['year'])
		{
			echo "<option value=\"$i\" selected> $i";
		}
		else
		{
			echo "<option value=\"$i\"> $i";
		}
	}

	echo "</select><p>";
	
	echo "<p><input type = \"submit\" value = \"OK\">";
	echo "<input type = \"hidden\" name=\"showVal\" value=\"false\">";
	echo "</form>";

	// Weblinks
	weatherWebLinks();
?>

</body>
</html>
