<?php

include __DIR__ . '/../../commonbase.php';

$csvReader = new \App\Tools\CSV\FileReader(__DIR__ . '/hubs.csv');
$csvWriter = new \App\Tools\CSVWriter(__DIR__ . '/normalizedHubs.csv', download:false);
$csvWriter->addHeaderRow();

foreach ($csvReader as $row)
	{
	$ratios = explode('-', $row['ratios']);
	foreach ($ratios as &$ratio)
		{
		$ratio = number_format((float)$ratio, 3);
		}
	$row['ratios'] = implode('-', $ratios);
	$csvWriter->outputRow($row);
	}
