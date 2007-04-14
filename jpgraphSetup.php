<?php
////////////////////////////////////////////////////
//
// WeatherOffice
//
// http://www.sourceforge.net/projects/weatheroffice
//
////////////////////////////////////////////////////

function phpnum()
{
   $version = explode('.',phpversion());
   return (int) $version[0];
}

if(phpnum() == 5)
  $jpgraphPath="./jpgraph_php5";
else
  $jpgraphPath="./jpgraph_php4";
        
$oldIncludePath=get_cfg_var('include_path');
$newIncludePath=$jpgraphPath . PATH_SEPARATOR . $oldIncludePath;
ini_set('include_path',$newIncludePath);

include("jpgraph.php");
include("jpgraph_line.php");
include("jpgraph_date.php");
include("jpgraph_polar.php");
include ("weatherInclude.php");

?>
