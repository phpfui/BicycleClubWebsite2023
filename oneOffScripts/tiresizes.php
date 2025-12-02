<?php

include 'commonbase.php';

$headers = ['ISO', 'Imperial', 'Metric', 'American', 'Diameter', 'Width', 'Rim'];

$csvWriter = new \App\Tools\CSV\FileWriter('tiresizes.csv');

$resource = \fopen('tiresizes.txt', 'r');

while ($line = \fgets($resource))
	{
	$parts = \explode(' ', $line);
	$metric = $imperial = $american = '';
	$iso = $parts[0];
	$tire = \explode('-', $iso);
	$width = $tire[0] ?? '';
	$rim = $tire[1] ?? '';
	$diameter = \round((int)$parts[2] / 10 * 3.14159265358979);
	$label = $parts[1];
	$sizes = \explode('x', $label);
	$label = \str_replace('x', ' x ', $label);

	if ((int)$sizes[0] <= 35)
		{
		$american = $label;
		}
	elseif (\strpos($label, 'C'))
		{
		$metric = $label;
		}
	elseif (\strpos($label, '.'))
		{
		$imperial = $label;
		}
	elseif (\strpos($label, '/'))
		{
		$american = $label;
		}
	else
		{
		$american = $label;
		}

	$row = ['ISO' => $iso, 'Imperial' => $imperial, 'Metric' => $metric, 'American' => $american, 'Diameter' => $diameter, 'Width' => $width, 'Rim' => $rim, 'new' => 'new'];
	$csvWriter->outputRow($row);
	}

//
//45-622 700x45C 2242 224
//47-622 700x47C 2268 227
//54-622 29x2.1 2288 229 29
//56-622 29x2.2 2298 230
//60-622 29x2.3 2326 233
//ISO,Imperial,Metric,American,Diameter,Width,Rim
//47-203,,,"12 x 1.75",297,47,203
//47-305,,,"16 x 1.75 x 2",399,47,305
//32-340,,400A,,404,32,340
//37-340,,400 x 35A,,414,37,340
//35-349,,,"16 x 1.35",419,35,349
//37-349,"16 x 1 3/8",,"16 x 1.5",423,37,349
//28-355,,,"18 x 1 1/8",411,28,355
//4
//
