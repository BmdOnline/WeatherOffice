<?php
////////////////////////////////////////////////////
//
// WeatherOffice
//
// http://www.sourceforge.net/projects/weatheroffice
//
// Copyright (C) 03/2014
//	Lars Hinrichsen
//	
//
// See COPYING for license info
//
////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//
// Generates a HTML table for Climate data
// Look & Feel is like climate tables in Wikipedia.
//
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	class ClimateTable{

		private $arrParameters;
		
		
		/**
		 * Constructor
		 *
		 * @access 		public
		 * @return 		\ClimateTable
		 */
		public function ClimateTable(){
			
		}

		/**
		 * Set the name of the weather station for the table header
		 *
		 * @param 		string     $strStationname       Name of the station
		 * @access 		public
		 * @return 		void
		 */
		public function setStationname($strStationname){
			$this->arrParameters['STATIONNAME'] =  $strStationname;
		}
		
		/**
		 * Allow a title to be set
		 *
		 * @param 		string     $strCaption       title of the diagram
		 * @access 		public
		 * @return 		void
		 */
		public function setTitle($strTitle){
			$this->arrParameters['TITLE'] =  $strTitle;
		}
		
		/**
		 * Set the geographical position of the weather station
		 *
		 * @param 		string     $strGeoPosition       Should be structured like "N 51° 12.345 E 006° 12.345"		 
		 * @access 		public
		 * @return 		void
		 */
		public function setStationPlace($strStationPlace){
			$this->arrParameters['STATIONPLACE'] =  $strStationPlace;
		}
		
		/**
		 * Add a row with values
		 *
		 * @param 		const		TEMP_MIN, TEMP_MAX, TEMP_MIN, RAINFALL_AVG, RAINFALL_MIN,RAINFALL_MAX
		 * @param 		string     	$strRowCaption       Any text to be the first column in this row
		 * @param 		array     	$arrTemperature      12 fields with numeric values
		 * @access 		public
		 * @return 		void
		 */
		public function addRow($strType, $strRowCaption, $arrTemperature){
			
			$this->arrParameters[$strType] =  array("CAPTION" => $strRowCaption, 
											"VALUES" =>$arrTemperature);
		}
		
		
		/**
		 * Calculate the background color for temperature
		 *
		 * @param 		double     	$dblValue       Temperature to be converted to color
		 * @access 		private
		 * @return 		void
		 */
		private function getBGCOLORTemp($dblValue){
						
			$strBGCOLOR = "#8AB0FF";	# blue

			if($dblValue >= -15)
			{
				$strBGCOLOR =  "#B9D3FF"; # lighter blue
			}

			if($dblValue >= -10)
			{
				$strBGCOLOR =  "#CFE8FF"; # light blue
			}
				
			if($dblValue >= -5)
			{
				$strBGCOLOR =  "#FFFFFF"; # white
			}

			if($dblValue >= 5)
			{
				$strBGCOLOR =  "#FFFF99"; # light yellow
			}

			if($dblValue >= 10)
			{
				$strBGCOLOR =  "#FFCC66"; # light orange
			}

			if($dblValue >= 15)
			{
				$strBGCOLOR =  "#FFA500"; # orange
			}
				
			if($dblValue >= 20)
			{
				$strBGCOLOR =  "#FF6347"; # lightred
			}			
			
			if($dblValue >= 25)
			{
				$strBGCOLOR =  "#FF6347"; # red
			}

			if($dblValue >= 30)
			{
				$strBGCOLOR =  "#FF4040"; # darkred
			}
				
			return $strBGCOLOR;
			
		}
				
		/**
		 * Generate a table row for temperature values
		 *
		 * @param 		string		$caption		caption of the row (e.g. "Average temperature in °C")
		 * @param 		array     	$arrValues      array with 12 numeric temperature values
		 * @access 		private
		 * @return 		void
		 */
		private function getRowTemperature($caption, $arrValues){

			echo "<tr align=\"center\"><td style=\"height:20px; text-align:left; white-space:nowrap;\">";
			echo $caption . "</td>";
			$sum=0;
			for($i = 0; $i<12;$i++) 
			{ 		
				echo "<td style=\"background: " . $this->getBGCOLORTemp($arrValues[$i]) . "\">". $arrValues[$i] . "</td>";
				$sum=$sum+$arrValues[$i];
			}
			echo "<td style=\"border-left: 6px solid #E5E5E5; border-right: 3px solid #E5E5E5; font-size: 110%;\"><b>&empty;</b></td>";
			echo "<td style=\"background: " . $this->getBGCOLORTemp($sum/12) . ";width:45px;\"><b>". round($sum/12,1) . "</b></td>";
			echo "</tr>";
		}
		
		/**
		 * Calculate the background color for raindays
		 *
		 * @param 		double     	$dblValue       Number of raindays to be converted to color
		 * @access 		private
		 * @return 		void
		 */
		private function getBGCOLORRaindays($dblValue){
		
			$strBGCOLOR = "#EED8AE";	# light peach
		
			if($dblValue >= 2 )
			{
				$strBGCOLOR =  "#FFF8DC"; # lighter peach
			}
		
			if($dblValue >= 3)
			{
				$strBGCOLOR =  "#FFFFFF"; # white
			}
		
			if($dblValue >= 4)
			{
				$strBGCOLOR =  "#CFE8FF"; # blued white
			}
		
			if($dblValue >= 5)
			{
				$strBGCOLOR =  "#FFFF99"; # lighter blue
			}
		
			if($dblValue >= 7)
			{
				$strBGCOLOR =  "#B9D3FF"; # light blue
			}
		
			if($dblValue >= 8)
			{
				$strBGCOLOR =  "#8AB0FF"; # blue
			}
		
			if($dblValue >= 9)
			{
				$strBGCOLOR =  "#6495ED"; # darker blue
			}
				
			if($dblValue >= 10)
			{
				$strBGCOLOR =  "#4169E1"; # dark blue
			}

			if($dblValue >= 12)
			{
				$strBGCOLOR =  "#828BD9"; # light violet
			}

			if($dblValue >= 13)
			{
				$strBGCOLOR =  "#607CD2"; # violet
			}

		
			return $strBGCOLOR;
				
		}
		
		/**
		 * Generate a table row for rainfall values
		 *
		 * @param 		string		$caption		caption of the row (e.g. "Average temperature in °C")
		 * @param 		array     	$arrValues      array with 12 numeric rainfall values
		 * @access 		private
		 * @return 		void
		 */
		private function getRowRainfall($caption, $arrValues){
		
			echo "<tr align=\"center\"><td style=\"height:20px; text-align:left; white-space:nowrap;\">";
			echo $caption . "</td>";
			$sum = 0;
			for($i = 0; $i<12;$i++)
			{
				echo "<td style=\"background: " . $this->getBGCOLORRainfall($arrValues[$i]) . "\">". $arrValues[$i] . "</td>";
				$sum=$sum+$arrValues[$i];
			}
					echo "<td style=\"border-left: 6px solid #E5E5E5; border-right: 3px solid #E5E5E5; font-size: 110%;\"><b>&sum;</b></td>";
			echo "<td style=\"background: " . $this->getBGCOLORRainfall($sum/12) . ";width:45px;\"><b>". $sum . "</b></td>";
			echo "</tr>";
		}
		

		/**
		 * Generate a table row for rainday values
		 *
		 * @param 		string		$caption		caption of the row (e.g. "Average temperature in °C")
		 * @param 		array     	$arrValues      array with 12 numeric number of raindays per month values
		 * @access 		private
		 * @return 		void
		 */
		private function getRowRaindays($caption, $arrValues){
		
			echo "<tr align=\"center\"><td style=\"height:20px; text-align:left; white-space:nowrap;\">";
			echo $caption . "</td>";
			$sum = 0;
			for($i = 0; $i<12;$i++)
			{
				echo "<td style=\"background: " . $this->getBGCOLORRaindays($arrValues[$i]) . "\">". $arrValues[$i] . "</td>";
				$sum=$sum+$arrValues[$i];
			}
					echo "<td style=\"border-left: 6px solid #E5E5E5; border-right: 3px solid #E5E5E5; font-size: 110%;\"><b>&sum;</b></td>";
			echo "<td style=\"background: " . $this->getBGCOLORRaindays($sum/12) . ";width:45px;\"><b>". $sum . "</b></td>";
			echo "</tr>";
		}
		
		/**
		 * Calculate the background color for rainfall
		 *
		 * @param 		double     	$dblValue       amount of rain to be converted to color
		 * @access 		private
		 * @return 		void
		 */
		private function getBGCOLORRainfall($dblValue){
		
			$strBGCOLOR = "#EED8AE";	# light peach
		
			if($dblValue >= 10 )
			{
				$strBGCOLOR =  "#FFF8DC"; # lighter peach
			}
		
			if($dblValue >= 20)
			{
				$strBGCOLOR =  "#FFFFFF"; # white
			}
		
			if($dblValue >= 30)
			{
				$strBGCOLOR =  "#F0F8FF"; # blued white
			}
		
			if($dblValue >= 40)
			{
				$strBGCOLOR =  "#CFE8FF"; # lighter blue
			}
		
			if($dblValue >= 50)
			{
				$strBGCOLOR =  "#B9D3FF"; # light blue
			}
		
			if($dblValue >= 60)
			{
				$strBGCOLOR =  "#8AB0FF"; # blue
			}
		
			if($dblValue >= 70)
			{
				$strBGCOLOR =  "#6495ED"; # darker blue
			}
		
			if($dblValue >= 80)
			{
				$strBGCOLOR =  "#607CD2"; # dark blue
			}
		
			return $strBGCOLOR;
		
		}
		
		private function getMonthNames(){
			
			echo "<tr align=\"center\"><td style=\"height:15px\"></td>";
			echo "<td style=\"width:45px\">Jan</td>";
			echo "<td style=\"width:45px\">Feb</td>";
			echo "<td style=\"width:45px\">M&auml;r</td>";
			echo "<td style=\"width:45px\">Apr</td>";
			echo "<td style=\"width:45px\">Mai</td>";
			echo "<td style=\"width:45px\">Jun</td>";
			echo "<td style=\"width:45px\">Jul</td>";
			echo "<td style=\"width:45px\">Aug</td>";
			echo "<td style=\"width:45px\">Sep</td>";
			echo "<td style=\"width:45px\">Okt</td>";
			echo "<td style=\"width:45px\">Nov</td>";
			echo "<td style=\"width:45px\">Dez</td>";
			echo "</tr>";
		}
		
		
		/**
		 * Send the climate table to the stdout via echo
		 * @access 		public
		 * @return 		void
		 */
		public function getTable(){
			
			echo "<div style=\"font-size:95%; text-align:center; margin-bottom:5px;\">";			
			echo "<table style=\"border: 5px solid #E5E5E5; font-size:95%; background:#E5E5E5; margin-bottom:10px;\" border=\"0\" cellpadding=\"1\" cellspacing=\"1\">";
			echo "<tr align=\"center\"><td colspan=\"13\" height=\"5\"><b>" . $this->arrParameters["TITLE"];
			if ($this->arrParameters["STATIONNAME"] != NULL) 
			{
				echo "<BR>" . $this->arrParameters["STATIONNAME"];
			}
			
			if ($this->arrParameters["STATIONPLACE"] != NULL)
			{
				echo " (" . $this->arrParameters["STATIONPLACE"] . ")";
			}
				
			echo "</b></td></tr>";
			
			#echo "<tbody><tr align=\"center\"><td style=\"height:15px\">";
			#echo "<tbody>";
			
			$this->getMonthNames();
			if($this->arrParameters["TEMP_AVG"] != NULL) 
			{		
				$this->getRowTemperature($this->arrParameters["TEMP_AVG"]["CAPTION"],$this->arrParameters["TEMP_AVG"]["VALUES"]);
				echo "<tr align=\"center\"><td colspan=\"13\" height=\"5\"></td></tr>"; # horizontal spacer
			}
			if($this->arrParameters["MEAN_TEMP_MAX"] != NULL) 
			{				
				$this->getRowTemperature($this->arrParameters["MEAN_TEMP_MAX"]["CAPTION"],$this->arrParameters["MEAN_TEMP_MAX"]["VALUES"]);
			}
			if($this->arrParameters["MEAN_TEMP_MIN"] != NULL) 
			{				
				$this->getRowTemperature($this->arrParameters["MEAN_TEMP_MIN"]["CAPTION"],$this->arrParameters["MEAN_TEMP_MIN"]["VALUES"]);
			}
			if($this->arrParameters["TEMP_MAX"] != NULL) 
			{				
				$this->getRowTemperature($this->arrParameters["TEMP_MAX"]["CAPTION"],$this->arrParameters["TEMP_MAX"]["VALUES"]);
			}
			if($this->arrParameters["TEMP_MIN"] != NULL) 
			{				
				$this->getRowTemperature($this->arrParameters["TEMP_MIN"]["CAPTION"],$this->arrParameters["TEMP_MIN"]["VALUES"]);
			}
			echo "<tr align=\"center\"><td colspan=\"13\" height=\"5\"></td></tr>"; # horizontal spacer
			if($this->arrParameters["RAINFALL_AVG"] != NULL) 
			{				
				$this->getRowRainfall($this->arrParameters["RAINFALL_AVG"]["CAPTION"],$this->arrParameters["RAINFALL_AVG"]["VALUES"]);
			}
			if($this->arrParameters["RAINDAYS_AVG"] != NULL)
			{
				$this->getRowRaindays($this->arrParameters["RAINDAYS_AVG"]["CAPTION"],$this->arrParameters["RAINDAYS_AVG"]["VALUES"]);
			}
			echo "</tbody></table></div>\n";
				
		}
	}

?>

