<?php

// set the server name which determains which db to use
$_SERVER['SERVER_NAME'] = $argv[1] ?? 'localhost';

include __DIR__ . '/../../common.php';

echo "Loaded settings file {$dbSettings->getLoadedFileName()}\n";

function getReader(string $fileName) : \App\Tools\CSV\FileReader
	{
	$fileName = PROJECT_ROOT . '/conversions/soundCyclistsCT/zoho/tables/' . $fileName;

	if (! \file_exists($fileName))
		{
		echo "File {$fileName} was not found\n";

		exit;
		}

	return new \App\Tools\CSV\FileReader($fileName);
	}

$csvReader = \getReader('Ride Library RL.csv');

// Ride Name
// Start location
// Start Town
// Miles
// Terrain
// PDF
// Excel
// Word
// Ride last led on
// Revised Date
// Description
// GPS (Garmin .gpx)
// Google Map

$count = 0;
$notFound = 0;
foreach ($csvReader as $rideImport)
	{
	$data = ['name' => $rideImport['Ride Name']];
	$cueSheet = new \App\Record\CueSheet($data);
	if (! $cueSheet->loaded())
		{
		++$notFound;
		}
	else
		{
		$cueSheet->description = $rideImport['Description'];
		$cueSheet->update();
		++$count;
		}
	}

echo "Found $count, not found $notFound\n";
