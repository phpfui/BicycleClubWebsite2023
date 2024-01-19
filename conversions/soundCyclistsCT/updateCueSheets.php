<?php

// set the server name which determains which db to use
$_SERVER['SERVER_NAME'] = $argv[1] ?? 'localhost';

include __DIR__ . '/../../common.php';

echo "Loaded settings file {$dbSettings->getLoadedFileName()}\n";

function getMember(string $fullName) : ?\App\Record\Member
	{
	$names = \explode(' ', $fullName);

	$memberTable = new \App\Table\Member();

	if (\count($names) >= 2)
		{
		$condition = new \PHPFUI\ORM\Condition('firstName', $names[0]);
		$condition->and(new \PHPFUI\ORM\Condition('lastName', $names[1]));
		$memberTable->setWhere($condition);

		return $memberTable->getRecordCursor()->current();
		}

	return null;
	}

function makeDate(string $mdyFormat) : ?string
	{
	if (! \str_contains($mdyFormat, '/'))
		{
		return '2023-01-19';
		}

	[$month, $day, $year] = \explode('/', $mdyFormat);

	$retVal = \App\Tools\Date::makeString($year, $month, $day);

	if ('2099-12-31' < $retVal)
		{
		$retVal = '2099-12-31';
		}

	return $retVal;
	}

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

$iterator = new \DirectoryIterator(__DIR__ . '/zoho/files_for_ridelibrary');

$files = [];

foreach ($iterator as $item)
	{
	if (! $item->isDir())
		{
		$files[$item->getFilename()] = $item->getPathname();
		}
	}

// Ride Name
// Start location
// Town
// Distance
// Terrain
// Description
// Ride last led on
// PDF
// Word
// Excel
// GPS
// RidewithGPS Url
// Modified By
// Out Of Area
// Last Revised Date

$csvReader = \getReader('Ride Library RC.csv');

$fields = ['PDF', 'Excel', 'Word', 'GPS'];

$model = new \App\Model\CueSheetFiles();
$cueSheetPath = $model->getPath();

$terrains = [
	'' => 0,
	'Flat' => 1,
	'Flat/ Rolling' => 2,
	'Rolling' => 3,
	'Rolling/ Hilly' => 4,
	'Hilly' => 5,
];

$cueSheetVersionTable = new \App\Table\CueSheetVersion();

$count = 0;
$notFound = 0;

foreach ($csvReader as $rideImport)
	{
	$data = ['name' => $rideImport['Ride Name']];
	$cueSheet = new \App\Record\CueSheet($data);

	if (! $cueSheet->loaded())
		{
		++$notFound;
		$dom = new \voku\helper\HtmlDomParser($rideImport['RidewithGPS Url']);

		$RWGPS = null;

		foreach ($dom->find('a') as $node)
			{
			if (false !== \strpos($node->href, 'ridewithgps'))
				{
				$RWGPS = \App\Model\RideWithGPS::getRWGPSFromLink($node->href);
				}
			}
		unset($dom);
		$startLocationName = \trim($rideImport['Start location']);
		$startLocation = new \App\Record\StartLocation(['name' => $startLocationName]);
		$startLocation->town = $rideImport['Town'];
		$startLocation->insertOrUpdate();

		if ($RWGPS)
			{
			$cueSheet->RWGPS = $RWGPS;
			$RWGPS->startLocation = $startLocation;
			$RWGPS->town = $rideImport['Town'];
			$RWGPS->update();
			}
		// add cue sheets
		$cueSheet->name = $rideImport['Ride Name'];
		$cueSheet->mileage = (float)$rideImport['Distance'];
		$cueSheet->startLocation = $startLocation;
		$cueSheet->pending = 0;
		$member = \getMember($rideImport['Modified By']);

		if ($member)
			{
			$cueSheet->member = $member;
			}
		$cueSheet->dateAdded = \makeDate($rideImport['Last Revised Date']);
		$cueSheet->terrain = $terrains[$rideImport['Terrain']];
		$existingSheet = new \App\Record\CueSheet();

		if (! $existingSheet->read(['name' => $cueSheet->name, 'mileage' => $cueSheet->mileage, 'startLocationId' => $cueSheet->startLocationId]))
			{
			$cueSheet->insert();
			}
		else
			{
			$cueSheet = $existingSheet;
			$cueSheetVersionTable->setWhere(new \PHPFUI\ORM\Condition('cueSheetId', $cueSheet->cueSheetId));
			$cueSheetVersionTable->delete();
			}

		foreach ($fields as $fileField)
			{
			\addCueSheetFile($cueSheet, $rideImport[$fileField], $cueSheetPath);
			}
		}
	else
		{
		if (! $cueSheet->description)
			{
			$cueSheet->description = $rideImport['Description'];
			$cueSheet->update();
			}
		++$count;
		}
	}

echo "Found {$count}, not found {$notFound}\n";

function addCueSheetFile(\App\Record\CueSheet $cueSheet, string $file, string $path) : void
	{
	global $files;

	$file = \trim($file);

	if (empty($file))
		{
		return;
		}
	$parts = \explode('_', $file);
	$prefix = \array_shift($parts);

	$fileName = \implode('_', $parts);

	if (! isset($files[$fileName]))
		{
		echo "FILE NOT FOUND {$fileName}\n";

		return;
		}
	$importFile = $files[$fileName];

	$parts = \explode('.', $fileName);
	$extension = '.' . \array_pop($parts);

	$cueSheetVersion = new \App\Record\CueSheetVersion();
	$cueSheetVersion->cueSheet = $cueSheet;
	$cueSheetVersion->memberId = $cueSheet->memberId;
	$cueSheetVersion->dateAdded = $cueSheet->dateAdded;
	$cueSheetVersion->extension = $extension;
	$cueSheetVersion->insert();

	$destination = $path . $cueSheetVersion->cueSheetVersionId . $cueSheetVersion->extension;
	\copy($importFile, $destination);
	}
