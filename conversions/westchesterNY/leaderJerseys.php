<?php

// set the server name which determains which db to use
$_SERVER['SERVER_NAME'] = $argv[1] ?? 'localhost';

include __DIR__ . '/../../common.php';

echo "Loaded settings file {$dbSettings->getLoadedFileName()}\n";

$csvReader = new \App\Tools\CSVReader('../leaders.csv');
$csvWriter = new \App\Tools\CSVWriter('../leaderLabels.csv');

$headers = ['First Name', 'Last Name', 'Street Address 1', 'City', 'State', 'Zip Code', 'Country',
	'Order ID', 'Shipping Carrier', 'Insurance Value', ];

$csvWriter->outputRow($headers);

$membersTable = new \App\CRUD\Members();
$row = \array_flip($headers);

foreach ($csvReader as $l)
	{
	$leaders = $membersTable->findByName([$l['firstName'], $l['lastName']]);

	foreach ($leaders as $leader)
		{
		$row['First Name'] = $leader['firstName'];
		$row['Last Name'] = $leader['lastName'];
		$row['Street Address 1'] = $leader['address'];
		$row['City'] = $leader['town'];
		$row['State'] = $leader['state'];
		$row['Zip Code'] = $leader['zip'];
		$row['Country'] = 'US';
		$row['Order ID'] = '';
		$row['Shipping Carrier'] = '';
		$row['Insurance Value'] = '';
		$csvWriter->outputRow($row);
		}
	}
