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
// English Translation Steve Chamberlin
//
// See COPYING for license info
//
////////////////////////////////////////////////////

include ("jpgraphSetup.php");

	function getLegend($col)
	{
		global $text;

		switch($col)
        	{
			case 'temp_out':
				return( $text['outside_temperature'] );
				break;

			case 'dewpoint':
				return( $text['dewpoint'] );
				break;

			case 'temp_in':
				return( $text['inside_temperature']);
				break;

			case 'wind_chill':
				return( $text['windchill'] );
				break;

			case 'rel_hum_in':
				return( $text['inside_humidity'] );
				break;

			case 'rel_hum_out':
				return( $text['outside_humidity'] );
				break;


			default:
				return( $text['undefined'] );
	        }
	}

	$begin =  $_REQUEST["begin"];
	$end =    $_REQUEST["end"];
	$col1 =   $_REQUEST["col1"];
	$col2 =   $_REQUEST["col2"];
	$col3 =   $_REQUEST["col3"];
	$titleStr =   $_REQUEST["title"];
	$unit1 =  $_REQUEST["unit1"];
	$unit2 =  $_REQUEST["unit2"];
	$unit3 =  $_REQUEST["unit3"];
	$type = $_REQUEST["type"];

	$day     = substr($begin, 6, 2);
	$month = substr($begin, 4, 2);
	$year    = substr($begin, 0, 4);

	$fields = "$col1, $col2, $col3, rec_time, rec_date";
	$database->getWeatherFieldsFromPeriod($fields, $begin, $end);
	$num = $database->getRowsCount();
	if ($num>0) {
		$graph = new Graph($GraphWidth, $GraphHeight);
		$graph->SetMargin(50,50,10,90);
		$graph->SetScale("datlin");
		$graph->SetY2Scale("lin");
		$graph->SetMarginColor($MarginColor);
		$graph->SetFrame(true,$FrameColor,1);

		$factor=(integer) ($num/500); // 500 Werte maximal
		if($factor == 0)
			$factor = 1;

		switch($type)
		{
			case "day":
				$title = $titleStr . " " . $text['at'] . " " . $day . "." . $month . "." . $year;
				$graph->xaxis->scale->SetDateFormat('H:i');
				// Force labels to only be displayed every 60 minutes
				$graph->xaxis->scale->ticks->Set(60*60);
				$graph->xaxis->SetLabelAngle(90);
				break;

			case "24":
				$title = "24h " . $titleStr . " " . $text['at'] . " " . $day . "." . $month . "." . $year;
				$graph->xaxis->scale->SetDateFormat('H:i');
				// Force labels to only be displayed every 60 minutes
				$graph->xaxis->scale->ticks->Set(60*60);
				$graph->xaxis->SetLabelAngle(90);
				break;

			case "week":
				$title = $titleStr . " " . $text['in_the_week_from'] . " " . $day . "." . $month . "." . $year;
				$graph ->xaxis->scale->SetDateFormat( "d.m\nH:i");
				// Force labels to only be displayed every 12 hours
				$graph->xaxis->scale->ticks->Set(12*60*60);
				$graph->xaxis->SetLabelAngle(0);
				break;

			case "month":
				$title = $titleStr . " " .  monthName($month, $text) . " " . $year;
				$graph ->xaxis->scale->SetDateFormat("d.m\nH:i");
				// Force labels to only be displayed every 2 days
				$graph->xaxis->scale->ticks->Set(2*24*60*60);
				$graph->xaxis->SetLabelAngle(0);
				break;

			case "free":
				$title = $titleStr . " " . $text['in_the_period_from'] . " " . $day . "." . $month . "." . $year;
				$graph->xaxis->scale->SetDateFormat("d.m H:i");
				// Force labels to only be displayed every 2 days
				//$graph->xaxis->scale->ticks->Set(2*24*60*60);
				$graph->xaxis->SetLabelAngle(90);
				break;

			default:
				$title = $titleStr;
				$graph->xaxis->scale->SetDateFormat('H:i');
				$graph->xaxis->SetLabelAngle(90);
		}

		$graph->title->Set(encodeStringForGraph($title));
		$graph->title->SetColor($LegendColor);

		$graph->yaxis->SetColor($YAxisColors[0]);
		$graph->yaxis->title->SetMargin(0);
		$graph->yaxis->title->SetColor($LegendColor);
		$graph->yaxis->title->Set(encodeStringForGraph($unit1));
		$graph->y2axis->SetColor($YAxisColors[1]);
		$graph->y2axis->title->SetMargin(10);
		$graph->y2axis->title->SetColor($LegendColor);
		$graph->y2axis->title->Set(encodeStringForGraph($unit3));
		$graph->SetTickDensity(TICKD_SPARSE);
		$graph->xaxis->SetPos('min');
		$graph->xaxis->SetColor($XAxisColors);

		$graph->legend->SetColor($LegendColor);
		$graph->legend->SetFillColor($LegendFillColor);
		$graph->legend->SetPos( 0.03,0.95,"right" ,"bottom");
		$graph->legend->SetColumns(3);

		$xdata = array();
		$ydata = array();
		$start = time();

		$idx = 0;
		$i   = 0;
		$database->seekRow(0);
		while($row = $database->getNextRow())
		{
			if(($i % $factor) == 0)
			{
				$recTime = $row["rec_time"];
				$recDate = $row["rec_date"];

				$xdata[$idx] = mktime(substr($recTime, 0, 2), substr($recTime, 3, 2), substr($recTime, 6, 2),
					substr($recDate, 5, 2), substr($recDate, 8, 2), substr($recDate, 0, 4));
				$ydata1[$idx] = $row[$col1];
				$ydata2[$idx] = $row[$col2];
				$ydata3[$idx] = $row[$col3];
				$idx++;
			}
			$i++;
		}

		$database->free();
		$database->close();

		$lineplot1=new LinePlot($ydata1, $xdata);
		$graph->Add($lineplot1);
		$lineplot1->SetColor($LineColors[0]);
		if ($LineFillColors[0]) $lineplot1->SetFillColor($LineFillColors[0]);
		$lineplot1->SetWeight($LineThickness);
		$lineplot1->SetLegend(encodeStringForGraph(getLegend($col1)));

		$lineplot2=new LinePlot($ydata2, $xdata);
		$graph->Add($lineplot2);
		$lineplot2->SetColor($LineColors[1]);
		if ($LineFillColors[1]) $lineplot2->SetFillColor($LineFillColors[1]);
		$lineplot2->SetWeight($LineThickness);
		$lineplot2->SetLegend(encodeStringForGraph(getLegend($col2)));

		$lineplot3=new LinePlot($ydata3, $xdata);
		$graph->AddY2($lineplot3);
		$lineplot3->SetColor($LineColors[2]);
		if ($LineFillColors[2]) $lineplot3->SetFillColor($LineFillColors[2]);
		$lineplot3->SetWeight($LineThickness);
		$lineplot3->SetLegend(encodeStringForGraph(getLegend($col3)));

		$graph->SetShadow();
		$graph->Stroke();
	} else {
		// No data to draw
		// Create a transparent pixel
		header('Content-Type: image/png');
		$img = imagecreatetruecolor(1, 1);
		imagesavealpha($img, true);
		$color = imagecolorallocatealpha($img, 0, 0, 0, 127);
		imagefill($img, 0, 0, $color);
		imagepng($img);
		imagedestroy($img);
	}

?>
