<?php
// v 1.00 - 14.03.2004 - initial version

include("weatherInclude.php");


$width   = 1000;
$PIX_PER_YEAR=15;

$marginL = 50;
$marginR = 50;
$marginT = 30;
$marginB = 50;

if (isset($_REQUEST["width"]))
   $width = $_REQUEST["width"];

$begin 		=  getRequest("begin");
$end 			=  getRequest("end");
$col 			=  getRequest("col");
$titleStr =  getRequest("title");
$unit 		=  getRequest("unit");
$type 		=  getRequest("type");
		
$day     = substr($begin, 6, 2);
$month 	 = substr($begin, 4, 2);
$year    = substr($begin, 0, 4);
$startDate = new DateTime("$day.$month.$year");

$day     = substr($end, 6, 2);
$month 	 = substr($end, 4, 2);
$year    = substr($end, 0, 4);
$endDate = new DateTime("$day.$month.$year");

$interval = $startDate->diff($endDate);

if($interval->days > 10)
{
	$weeks = $interval->days / 7;
	//$height = $weeks * $rowHeight;
}

$firstYear=0;
$lastYear=0;
$dummyMonth=0;
$dummyDay=0;
getStartYearAndMonth($firstYear, $dummyMonth, $dummyDay);
getStopYearAndMonth($lastYear, $dummyMonth, $dummyDay);

//$query = "select timestamp, $col from MinMaxAvg where type='DAY' and timestamp >= $begin and timestamp <= $end order by timestamp";
$query = "select timestamp, $col from MinMaxAvg where type='DAY' order by timestamp";
$result = mysql_query($query) or die ("oneValue Abfrage fehlgeschlagen<br>Query:<font color=red>$query</font><br>Error:" . mysql_error());
$num = mysql_num_rows($result);   

$height  = $marginT + $marginB + 2+($PIX_PER_YEAR * (($lastYear-$firstYear)+1));

Header("Content-type: image/PNG");

$img = imagecreate($width,$height);

// Allocate Colors
$cBack = ImageColorAllocate($img,200,200,200);
$cCanvas   = ImageColorAllocate($img,255,255,255); 
//$cBack = ImageColorAllocate($img,113,148,45);
$cBorder = ImageColorAllocate($img,218,218,218);  
//$cText   = ImageColorAllocate($img,50,50,50);  
$cText   = ImageColorAllocate($img,0,0,0); 
$cTextB   = ImageColorAllocate($img,0,0,0); 
$cScale   = ImageColorAllocate($img,50,50,50);  

$cStatus = array();



$NUMCOLORS=60;

if($col == "rain_total_max")
{
	$i=0;
	while($i < $NUMCOLORS)
	{
		$colVal=255-(255*$i)/$NUMCOLORS;
		$cStatus[$i] = ImageColorAllocate($img,$colVal,$colVal,255);
		$i++;
	}
}
else if($col == "windspeed_max")
{
	$i=0;
	while($i < $NUMCOLORS)
	{
		$colVal=255-(255*$i)/$NUMCOLORS;
		$cStatus[$i] = ImageColorAllocate($img,255,$colVal,$colVal);
		$i++;
	}
}
else
{
	$i=0;
	while($i < $NUMCOLORS)
	{
		$cStatus[$i] = ImageColorAllocate($img,((255*$i)/$NUMCOLORS),0, 255-((255*$i)/$NUMCOLORS));
		$i++;
	}
}



//$fill0 = ImageColorAllocate($img,220,220,255);
//$fill = ImageColorAllocate($img,44,81,150);
//$transparent= imagecolortransparent($img,$back);

$plotX1 = $marginL;
$plotY1 = $marginT;
$plotX2 = $width - $marginR;
$plotY2 = $height - $marginB;
$plotDX = $plotX2 - $plotX1-3;

ImageFilledRectangle($img,0,0,$width-1,$height-1,$cBack);
ImageRectangle($img,0,0,$width-1,$height-1,$cScale);
ImageFilledRectangle($img, $plotX1, $plotY1, $plotX2,$plotY2, $cCanvas);
ImageRectangle($img, $plotX1, $plotY1, $plotX2,$plotY2, $cScale);

$textWidth = imagefontwidth(4) * strlen($titleStr);
imagestring ( $img , 4, ($width-$textWidth)/2.0, 10,  $titleStr, $cText);

/** Draw Scale */

$scaleFirstDay = mktime(12, 0, 0, 1, 1, 1970);
$scaleLastDay  = mktime(12, 0, 0, 12, 31, 1970);

$deltaX = $scaleLastDay - $scaleFirstDay;
$firstX = $scaleFirstDay;

$actScale = $scaleFirstDay;

while($actScale <= $scaleLastDay)
{
	$dayOfMonth=date ("d", $actScale );
	
	if($dayOfMonth == 1 or $dayOfMonth == 15)
	{
		$X1 = ((($actScale-$firstX)/$deltaX)*$plotDX)+$plotX1;
		$X2 = ((($actScale-$firstX)/$deltaX)*$plotDX)+$plotX1;
		$Y1 = $plotY2+1;
		$Y2 = $plotY2+5;
			
		ImageLine($img, $X1, $Y1, $X2,$Y2, $cScale);
	
		//if($dayOfMonth == 1)
		{
			$lText = date ("d.m", $actScale );
			imagestringup ( $img , 2, $X1-5, $Y2+35,  $lText, $cText);
			//imagettftext ( $img , 8, 90 , $X1+4, $Y2+35, $cText , $fontFile , $lText);
		}
	}
	$actScale += 24*60*60; //+ One day
}

$year=$firstYear;

while($year <= $lastYear)
{
	$X1 = $plotX1 - 35;
	$Y1 = $plotY1 + ($year - $firstYear) * $PIX_PER_YEAR;

	$lText = $year;
	imagestring ( $img , 2, $X1, $Y1,  $lText, $cText);	
	$year++;
}

// Draw Values

$lastX2 = 0;
$firstYear=0;

while($row = mysql_fetch_array($result, MYSQL_ASSOC))
{
	$recDate  = $row["timestamp"];
	$year =  (int)substr($recDate, 0, 4);
	$month = substr($recDate, 4, 2);
	$day =  substr($recDate,  6, 2);
	
	$actX = mktime(12, 0, 0, $month, $day, 1970);
	$actValue = $row[$col];		
	
	$X1=((($actX-$firstX)/$deltaX)*$plotDX)+$plotX1+1;
	$X2=((($actX-$firstX)/$deltaX)*$plotDX)+$plotX1+2;
	
	if(($X1-$lastX2) < 2 and  ($X1-$lastX2) > 0)// Fill gaps
	{
		$X1=$lastX2;
	}
		
	if($firstYear == 0)
		$firstYear = $year;
		

	$Y1=$plotY1 + ($year - $firstYear) * $PIX_PER_YEAR +1;
	$Y2=$Y1 + $PIX_PER_YEAR;
	//$Y2=$plotY2-1;
	
	if($col == "rain_total_max")
		$colorIdx = (int) $actValue;
	else if($col == "windspeed_max")
		$colorIdx = (int) $actValue;
	else if($col == "rel_pressure_avg")
		$colorIdx = (int) ($actValue - 990.0);		
	else
		$colorIdx = (int)($actValue + 25);
	
	if($colorIdx >= $NUMCOLORS)
		$colorIdx = $NUMCOLORS-1;
	if($colorIdx < 0)
		$colorIdx = 0;
	
	ImageFilledRectangle($img, $X1, $Y1, $X2,$Y2, $cStatus[$colorIdx]);
	
	$lastX2 = $X2;
	
}
   
imagePNG($img);
imagedestroy($img);

?>
