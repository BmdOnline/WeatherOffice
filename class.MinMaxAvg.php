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
		$cols = array("temp_out", "windspeed", "rel_pressure", "rain_total");
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

				if($col == "rain_total")
					$precision = "4,1";

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

	public function updateDbTable($Type)
	{
		$tStampStart=0;
		$tStampLenght=0;

		$tRainStart=0;
		$tRainLenght=0;

		switch($Type)
		{
			case 'DAY';
				$tStampStart=1;
				$tStampLenght=8;
				$tRainStart=1;
				$tRainLenght=8;
				break;
			case 'MONTH';
				$tStampStart=5;
				$tStampLenght=2;
				$tRainStart=1;
				$tRainLenght=6;
				break;
			case 'YEARMONTH';
				$tStampStart=1;
				$tStampLenght=6;
				$tRainStart=1;
				$tRainLenght=8;
				break;
			case 'YEAR';
				$tStampStart=1;
				$tStampLenght=4;
				$tRainStart=1;
				$tRainLenght=4;			
				break;
			default;
				echo "Error: generateMinMaxEntries Type $Type is not supported\n";
				return;
		}

		echo "Updating Table MinMaxAvg Entries $Type\n";

		$queryFields = "";
		$targetCols = "";
		
		$cols = MinMaxAvg::getTableColumns();
		
		$i=0;
		foreach ($cols as $col)
		{
			if($cols != "rain_total")
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
							. " $queryFields FROM weather GROUP BY substr(timestamp, $tStampStart, $tStampLenght)";													
		//echo $query;

		mysql_query($query) or die ("Query Failed<br>Query:<font color=red>$query</font><br>Error:" . mysql_error());


		echo "Calculating Rain for $Type...\n";

		// Special Handling for Rain Required
		

		$query = "UPDATE MinMaxAvg AS tar ".
						 "INNER JOIN ".
						 "(SELECT SUBSTR(YYYYDD,$tStampStart, $tStampLenght) AS timestamp, ".
						 "ROUND(AVG(rainfall),1) AS rain_total_avg, ".
						 "ROUND(MIN(rainfall),1) AS rain_total_min, ".
						 "ROUND(MAX(rainfall),1) AS rain_total_max ".
						 "FROM ((SELECT MAX(rain_total)- MIN(rain_total) AS rainfall, ". 
						 "substr(timestamp,$tRainStart, $tRainLenght) AS YYYYDD " .
						 "FROM weather GROUP BY substr(timestamp,$tRainStart, $tRainLenght) ) AS T1) " .
						 "GROUP BY SUBSTR(YYYYDD,$tStampStart, $tStampLenght))" .
						 " AS sor ON tar.timestamp = sor.timestamp ".
						 "SET ".
						 "tar.rain_total_avg=sor.rain_total_avg, ".
						 "tar.rain_total_min=sor.rain_total_min, ".
						 "tar.rain_total_max=sor.rain_total_max ";
		
		mysql_query($query) or die ("Query Failed<br>Query:<font color=red>$query</font><br>Error:" . mysql_error());						 
		
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

	public function getValues($filter)
	{
		$cols['temp_out_min'] = array('min');
		$cols['temp_out_max'] = array('max');
		$cols['temp_out_avg'] = array('min','max');
		$cols['rel_pressure_max'] = array('max');
		$cols['rel_pressure_min'] = array('min');		
		$cols['rain_total_avg'] = array('min','max');
		
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
}
?>
