<?php

include 'commonbase.php';

$file = 'files/gearCalculator/tires.csv';
$csvReader = new \App\Tools\CSV\FileReader($file);

$tires = [];

foreach ($csvReader as $row)
	{
	[$width, $size] = \explode('-', $row['ISO']);
	$tires[$size . '-' . $width] = $row;
	}

\ksort($tires);

$csvWriter = new \App\Tools\CSV\FileWriter($file, false);

foreach ($tires as $tire)
	{
	$csvWriter->outputRow($tire);
	}
