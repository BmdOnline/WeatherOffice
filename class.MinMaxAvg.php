<?PHP
////////////////////////////////////////////////////
//
// WeatherOffice
//
// http://www.sourceforge.net/projects/weatheroffice
//
// Copyright (C) 03/2014
//	Bernhard Heibler,
//	Mathias Zuckermann
//	
//
// See COPYING for license info
//
////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//
// Class MinMaxAvg handles Extrema Computations and Handling
//
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class MinMaxAvg
{
	public static function getTableColumns()
	{
		$cols = array("temp_in", "temp_out", "rel_hum_in", "rel_hum_out", "windspeed", "wind_angle", "rain_total", "rel_pressure");
		return $cols;
	}

	public static function getOperations()
	{
		$ops = array("min", "max", "avg");
		return $ops;
	}
	
	public static function createDbTable()
	{
		$cols = MinMaxAvg::getTableColumns();
		$ops = MinMaxAvg::getOperations();
	
		$i=0;
		$columnCreate = "";
		
		foreach ($cols as $col)
		{
			foreach ($ops as $op)
			{
				$precision="3,1";
				
				if($col == "rel_pressure")
					$precision = "4,0";

				if($col == "wind_angle")
					$precision = "4,1";	
					
				if($col == "rain_total")
					$precision = "5,1";

				if($i++ > 0)
					$columnCreate = $columnCreate . ",";
			
				$columnCreate = $columnCreate . "${col}_$op decimal($precision) DEFAULT NULL\n";
			}
		}
		
		$query = "CREATE TABLE IF NOT EXISTS `MinMaxAvg` (" .
						 "`timestamp` bigint(14) DEFAULT '0'," .
						 "`type` char(10) NOT NULL DEFAULT '0'," .
						 $columnCreate .
						 ", PRIMARY KEY (`timestamp`) " .
						 ") ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		//echo "$query";				 
						 
		$result = mysql_query($query) or die ("Query Failed<br>Query:<font color=red>$query</font><br>Error:" . mysql_error());	
	}

	private function updateDbTableDay($startDay)
	{
		$Type = 'DAY';
		
		$tStampStart=1;
		$tStampLenght=8;
		$tRainStart=1;
		$tRainLenght=8;

		if($startDay > 0)
			$startTS= (int) ($startDay . "000000");
		else
			$startTS=0;
		
		echo "Updating Table MinMaxAvg Entries $Type<br>\n";

		$queryFields = "";
		$targetCols = "";
		
		$cols = MinMaxAvg::getTableColumns();
		
		$i=0;
		foreach ($cols as $col)
		{
			if($col != "rain_total")
			{
				if($i > 0)
				{
					$queryFields = $queryFields . ", ";
					$targetCols  = $targetCols  . ", ";
				}

				$queryFields = $queryFields . "ROUND(AVG($col),1) AS ${col}_avg, ";
				$targetCols  = $targetCols  . "${col}_avg,";
				$queryFields = $queryFields . "ROUND(MIN($col),1) AS ${col}_min, ";
				$targetCols  = $targetCols  . "${col}_min,";
				$queryFields = $queryFields . "ROUND(MAX($col),1) AS ${col}_max ";
				$targetCols  = $targetCols  . "${col}_max ";
			
				$i++;
			}
		}
			
		$query = "REPLACE INTO MinMaxAvg(timestamp, type, $targetCols) "
							.	"SELECT substr(timestamp, $tStampStart, $tStampLenght) as timestamp, \"${Type}\" as Type ,"
							. " $queryFields FROM weather WHERE timestamp > $startTS GROUP BY substr(timestamp, $tStampStart, $tStampLenght)";													
		//echo $query;

		mysql_query($query) or die ("Query Failed<br>Query:<font color=red>$query</font><br>Error:" . mysql_error());


		//echo "Calculating Rain for $Type...<br>\n";

		// Special Handling for Rain Required
		

		$query = "UPDATE MinMaxAvg AS tar ".
						 "INNER JOIN ".
						 "(SELECT SUBSTR(YYYYDD,$tStampStart, $tStampLenght) AS timestamp, ".
						 "ROUND(AVG(rainfall),1) AS rain_total_avg, ".
						 "ROUND(MIN(rainfall),1) AS rain_total_min, ".
						 "ROUND(MAX(rainfall),1) AS rain_total_max ".
						 "FROM ((SELECT MAX(rain_total)- MIN(rain_total) AS rainfall, ". 
						 "substr(timestamp,$tRainStart, $tRainLenght) AS YYYYDD " .
						 "FROM weather WHERE timestamp > $startTS GROUP BY substr(timestamp,$tRainStart, $tRainLenght) ) AS T1) " .
						 "GROUP BY SUBSTR(YYYYDD,$tStampStart, $tStampLenght))" .
						 " AS sor ON tar.timestamp = sor.timestamp ".
						 "SET ".
						 "tar.rain_total_avg=sor.rain_total_avg, ".
						 "tar.rain_total_min=sor.rain_total_min, ".
						 "tar.rain_total_max=sor.rain_total_max ";
		
		mysql_query($query) or die ("Query Failed<br>Query:<font color=red>$query</font><br>Error:" . mysql_error());					
	}
	
	function updateDbTableType($Type, $startDay)
	{
		$tStampStart=0;
		$tStampLenght=0;

		$tRainStart=0;
		$tRainLenght=0;
		
		$dataSource="";

		switch($Type)
		{
			case 'DAY';
				MinMaxAvg::updateDbTableDay($startDay);
				return;
				break;
			case 'YEARMONTH';
				$tStampStart=1;
				$tStampLenght=6;
				$tRainStart=1;
				$tRainLenght=8;
				$dataSource="MinMaxAvg WHERE type='DAY'";
				break;
			case 'MONTH';
				$tStampStart=5;
				$tStampLenght=2;
				$tRainStart=1;
				$tRainLenght=6;
				$dataSource="MinMaxAvg WHERE type='YEARMONTH'";
				break;				
			case 'YEAR';
				$tStampStart=1;
				$tStampLenght=4;
				$tRainStart=1;
				$tRainLenght=4;			
				$dataSource="MinMaxAvg WHERE type='YEARMONTH'";
				break;
			default;
				echo "Error: generateMinMaxEntries Type $Type is not supported\n";
				return;
		}

		echo "Updating Table MinMaxAvg Entries $Type<br>\n";

		$queryFields = "";
		$targetCols = "";
		
		$cols = MinMaxAvg::getTableColumns();
		
		$i=0;
		foreach ($cols as $col)
		{
				if($i > 0)
				{
					$queryFields = $queryFields . ", ";
					$targetCols  = $targetCols  . ", ";
				}

				if($col == "rain_total" && $Type != 'MONTH')
				{
					$queryFields = $queryFields . "ROUND(SUM(${col}_avg),1) AS ${col}_avg, ";	
					$queryFields = $queryFields . "ROUND(SUM(${col}_min),1) AS ${col}_min, ";
					$queryFields = $queryFields . "ROUND(SUM(${col}_max),1) AS ${col}_max ";
				}
				else
				{
					$queryFields = $queryFields . "ROUND(AVG(${col}_avg),1) AS ${col}_avg, ";	
					$queryFields = $queryFields . "ROUND(MIN(${col}_min),1) AS ${col}_min, ";
					$queryFields = $queryFields . "ROUND(MAX(${col}_max),1) AS ${col}_max ";
				}
				
				$targetCols  = $targetCols  . "${col}_avg,";
				$targetCols  = $targetCols  . "${col}_min,";
				$targetCols  = $targetCols  . "${col}_max ";
				
				$i++;
			
		}
			
		$query = "REPLACE INTO MinMaxAvg(timestamp, type, $targetCols) "
							.	"SELECT substr(timestamp, $tStampStart, $tStampLenght) as timestamp, \"${Type}\" as Type ,"
							. " $queryFields FROM $dataSource GROUP BY substr(timestamp, $tStampStart, $tStampLenght)";													
		//echo $query;

		mysql_query($query) or die ("Query Failed<br>Query:<font color=red>$query</font><br>Error:" . mysql_error());
	
	}
	
	function updateDbTables($incremental)
	{
	
		if(TableExists("MinMaxAvg") == false)
		{
			echo "Table MinMaxAvg doesn't exist !<br>";
			echo "I will create it for you this will take some time. Please be patient ...<br>";
			flush();
			MinMaxAvg::createDbTable();
			$incremental = false;
		}
		
		if($incremental)
		{
			// Check when last entry in MinMaxAvg Table was stored		
			$lastEntryCal=MinMaxAvg::getLastEntryTs("DAY");
			getStopYearAndMonth($lyear, $lmonth, $lday);
			$lastEntryData= (int) ($lyear . $lmonth . $lday);
			
			if($lastEntryData == $lastEntryCal)
			{		
				//echo "MinMaxAvg Table is up to date nothing to do\n";
				return;
			}			
			$startDay=$lastEntryCal;
		}
		else
		{
			$startDay=0;
		}
		
		MinMaxAvg::updateDbTableType("DAY", $startDay);
		flush();
		MinMaxAvg::updateDbTableType("YEARMONTH", $startDay);
		flush();
		MinMaxAvg::updateDbTableType("MONTH", $startDay);
		flush();
		MinMaxAvg::updateDbTableType("YEAR", $startDay);
		flush();
	}
	
	public function getLastEntryTs($type)
	{
		$query = "SELECT MAX(timestamp) AS timestamp FROM MinMaxAvg WHERE type='$type'";
		$result = mysql_query($query) or die ("Query Failed<br>Query:<font color=red>$query</font><br>Error:" . mysql_error());	
	
		if($row = mysql_fetch_array($result, MYSQL_ASSOC))
		{	
			return $row['timestamp'];
		}

		return 0;
	}
	
	public function getRows($filter, $column)
	{
		$query = "SELECT ${column} FROM MinMaxAvg WHERE type='$filter'";
		$result = mysql_query($query) or die ("Query Failed<br>Query:<font color=red>$query</font><br>Error:" . mysql_error());	
	
		while($row = mysql_fetch_array($result, MYSQL_ASSOC))
		{	
			$rows[] = $row[$column];
		}

		return $rows;
	}
	
	public function getExtremValues($filter)
	{
		$cols['temp_out_min'] = array('min');
		$cols['temp_out_max'] = array('max');
		$cols['temp_out_avg'] = array('min','max');
		$cols['rel_pressure_max'] = array('max');
		$cols['rel_pressure_min'] = array('min');		
		$cols['rain_total_max'] = array('max');
		$cols['rain_total_min'] = array('min');		
		
		foreach ($cols as $column => $operations)
		{
			foreach($operations as $op)
			{
				$query = "SELECT timestamp, ${column}  FROM MinMaxAvg WHERE type='$filter' AND ${column}="
							 . "(SELECT $op(${column}) FROM MinMaxAvg WHERE type='$filter');";	
				$result = mysql_query($query) or die ("Query Failed<br>Query:<font color=red>$query</font><br>Error:" . mysql_error());		  
					
				if($row = mysql_fetch_array($result, MYSQL_ASSOC));
				{		
				
					$st["$column"]["$op"]=$row[$column];
					$st["$column"]["${op}Date"]=$row['timestamp'];
					/*
					foreach ($row as $key => $value)
					{
						echo "$key $value ";
					}
					echo " $op <br>";
					*/
				}
			}
					
			
		}
		
		return $st;
	}
	
	public function getStatArray($Type, $year, $month, $day)
	{
		$timeStamp = 0;
		switch($Type)
		{
			case 'DAY';
			  $timeStamp=sprintf("%04d%02d%02d", $year, $month, $day);
				break;
			case 'YEARMONTH';
			  $timeStamp=sprintf("%04d%02d", $year, $month);
				break;
			case 'MONTH';
				$timeStamp=sprintf("%02d", $month);
				break;				
			case 'YEAR';
				$timeStamp=sprintf("%024", $year);
				break;
			default;
				echo "Error: getStatArray Type $Type is not supported\n";
				return;
		}
		
		//echo "TS $Type $timeStamp<br>";
		
		$query = "SELECT *  FROM MinMaxAvg WHERE type='$Type' AND timestamp='$timeStamp'";
		
		$result = mysql_query($query) or die ("Query Failed<br>Query:<font color=red>$query</font><br>Error:" . mysql_error());		  
					
		if($row = mysql_fetch_array($result, MYSQL_ASSOC))
		{
			$cols = MinMaxAvg::getTableColumns();
			$ops  = MinMaxAvg::getOperations();
			
			foreach ($cols as $col)
			{	
				foreach($ops as $op)
				{			
						$col_name="${col}_${op}";						
						$st[$col][$op]=$row[$col_name];
				}
			}
			
			return $st;
		}
		
		return NULL;
	}
}
?>