<?PHP
////////////////////////////////////////////////////
//
// WeatherOffice
//
// http://www.sourceforge.net/projects/weatheroffice
//
// Copyright (C) 03/2016
//	Bernhard Heibler,
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
	/**
		* Returns a list of columns from the source table that should be processed for Min Max Avg calculations
		*
		* @access 		public
		* @return 		String Array
		*/
	public static function getSrcTableColumns()
	{
		$cols = array("temp_in", "temp_out", "rel_hum_in", "rel_hum_out", "windspeed", "wind_angle", "rain_total", "rel_pressure");
		return $cols;
	}

	/**
		* Returns a list of operations that should be applied to the source table columns
		*
		* @access 		public
		* @return 		String Array
		*/
	public static function getOperations()
	{
		$ops = array("min", "max", "avg");
		return $ops;
	}

	/**
		* Create the MinMaxAvg Table in the database if it doesn't exist
		*
		* @access 		public
		* @return 		void
		*/
	public static function createDbTable()
	{
	    global $database;
	    $cols = MinMaxAvg::getSrcTableColumns();
	    $ops = MinMaxAvg::getOperations();

	    $database->createTableMinMaxAvg($cols, $ops);
	}

	/**
	* Calculate and update the Min Max Avg values for every day
	*
	* @access 		private
	* @param 		  int     $startDay       First day to process
	* @return 		void
	*/
	static function updateDbTableDay($startDay)
	{
	    global $database;
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

	    $cols = MinMaxAvg::getSrcTableColumns();

	    $database->calculateMinMaxAvg($cols, $Type, $tStampStart, $tStampLenght, $startTS);


	    //echo "Calculating Rain for $Type...<br>\n";

	    // Special Handling for Rain Required
	    $database->updateMinMaxAvg($tStampStart, $tStampLenght, $tRainStart, $tRainLenght, $startTS);
	}
	/**
	* Calculate and update the Min Max Avg values the given entry type
	*
	* @access 		public
	* @param 		  string     $Type           'DAY', 'MONTH' ...
	* @param 		  int     	 $startDay       First day to process
	* @return 		void
	*/
	static function updateDbTableType($Type, $startDay)
	{
		global $database;
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
			    $dataType="DAY";
			    break;
		    case 'MONTH';
			    $tStampStart=5;
			    $tStampLenght=2;
			    $tRainStart=1;
			    $tRainLenght=6;
			    $dataType="YEARMONTH";
			    break;
		    case 'YEAR';
			    $tStampStart=1;
			    $tStampLenght=4;
			    $tRainStart=1;
			    $tRainLenght=4;
			    $dataType="YEARMONTH";
			    break;
		    default;
			    echo "Error: generateMinMaxEntries Type $Type is not supported\n";
			    return;
		}

		echo "Updating Table MinMaxAvg Entries $Type<br>\n";

		$queryFields = "";
		$targetCols = "";

		$cols = MinMaxAvg::getSrcTableColumns();

		$database->aggregateMinMaxAvg($cols, $Type, $tStampStart, $tStampLenght, $dataType);

	}

	/**
	 * Update all MinMaxAvg Table entries. If incremental update is set checks what entries are missing
	 *
	 * @access 		public
	 * @param 		bool				 $incrumental		 fale -> Run a full update, true -> only update missing days
	 * @return 		void
	 */
	static function updateDbTables($incremental)
	{
		global $database;

		if(!$database->haveMinMaxAvg())
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
			$lastEntryCal=$database->getMinMaxAvgLastDate("DAY");
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


	/**
	 * Return all values of the given colum
	 *
	 * @access 		public
	 * @param 		string     $Type           'DAY', 'MONTH' ...
	 * @param 		string     $column         Name of column to return
	 * @return 		array of strings
	 */
	static public function getRows($filter, $column)
	{
		global $database;
		$database->getMinMaxAvgFieldsFromType($column, $filter);
		$database->seekRow(0);
		while($row = $database->getNextRow())
		{
			$rows[] = $row[$column];
		}
		$database->free();
		return $rows;
	}

	/**
	 * Return the extreme values for the given entry type
	 *
	 * @access 		public
	 * @param 		string     $filter         'DAY', 'MONTH' ...
	 * @return 		two dimensional associative array
	 */
	static public function getExtremValues($filter)
	{
		global $database;
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
				$row = $database->getMinMaxAvgExtremeValue($column, $op, $filter);
				if ($row)
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

	/**
	 * Return the values for the given entry
	 *
	 * @access 		public
	 * @param 		string     $Type         				'DAY', 'MONTH' ...
	 * @param			int				 $year, $month, $day	 selected entry
	 * @return 		two dimensional associative array
	 */
	static public function getStatArray($Type, $year, $month, $day)
	{
		global $database;
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
				$timeStamp=sprintf("%04d", $year);
				break;
			default;
				echo "Error: getStatArray Type $Type is not supported\n";
				return;
		}

		//echo "TS $Type $timeStamp<br>";

		$row = $database->getMinMaxAvgFromDate($Type, $timeStamp);
		$database->free();
		if($row)
		{
			$cols = MinMaxAvg::getSrcTableColumns();
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
