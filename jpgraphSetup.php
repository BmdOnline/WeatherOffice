<?php
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

function phpnum()
{
   $version = explode('.',phpversion());
   return (int) $version[0];
}

switch (phpnum()) {
    case 4:
        $jpgraphPath="./jpgraph_php4";
        break;
    case 5:
         $jpgraphPath="./jpgraph_php5";
        break;
    case 7:
        $jpgraphPath="./jpgraph_php72";
        break;
    default:
        $jpgraphPath="./jpgraph_php5";
}
        
$oldIncludePath=get_cfg_var('include_path');
$newIncludePath=$jpgraphPath . PATH_SEPARATOR . $oldIncludePath;
ini_set('include_path',$newIncludePath);

include("jpgraph.php");
include("jpgraph_line.php");
include("jpgraph_date.php");
include("jpgraph_polar.php");
include ("weatherInclude.php");

?>
