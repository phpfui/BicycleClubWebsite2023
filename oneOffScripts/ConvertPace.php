<?php

// set the server name which determains which db to use
$_SERVER['SERVER_NAME'] = $argv[1] ?? 'localhost';

include __DIR__ . '/../common.php';

echo "Loaded settings file {$dbSettings->getLoadedFileName()}\n";

class NewCategory extends \App\CRUD\Category
	{
	public function __construct()
		{
		parent::__construct();
		$this->autoIncrement = false;
		$this->primaryKey = '';
		}
	}

class NewPace extends \App\CRUD\Pace
	{
	public function __construct()
		{
		parent::__construct();
		$this->autoIncrement = false;
		$this->primaryKey = '';
		}
	}

$newCategories = [1 => 'A', 'B', 'C', 'D', 'G', 'O', 'M', 'V'];

$newPaces = [
	'A21' => 'A+',
	'A20' => 'A+',
	'A19' => 'A',
	'A18' => 'A-',
	'A17' => 'A-',
	'B18' => 'B+',
	'B17' => 'B+',
	'B16' => 'B',
	'B15' => 'B',
	'B14' => 'B-',
	'C15' => 'C+',
	'C14' => 'C+',
	'C13' => 'C',
	'C12' => 'C',
	'C11' => 'C-',
	'D11' => 'D+',
	'D10' => 'D',
	'D9' => 'D-',
	'G13' => 'HP/G+',
	'G12' => 'HP/G',
	'G11' => 'HP/G',
	'G10' => 'HP/G-',
	'G9' => 'HP/G-',
	'M' => 'M',
	'O' => 'O',
	'VAll' => 'V',
	'V21' => 'V',
	'V20' => 'V',
	'V19' => 'V',
	'V18' => 'V',
	'V17' => 'V',
	'V16' => 'V',
	'V15' => 'V',
	'V14' => 'V',
	'V13' => 'V',
	'V12' => 'V',
	'V11' => 'V',
	'V10' => 'V',
];

$paceTable = new \App\CRUD\Pace();
$categoryTable = new \App\CRUD\Category();

\PHPFUI\ORM::execute('delete from pace');
\PHPFUI\ORM::execute('delete from category');

$newCatTable = new NewCategory();

foreach ($newCategories as $categoryId => $category)
	{
	$ordering = $categoryId;
	$minSpeed = 9999;
	$maxSpeed = 0;

	foreach ($newPaces as $pace => $oldPace)
		{
		if ($category != $pace[0])
			{
			continue;
			}
		$speed = (int)\substr($pace, 1);

		if ($speed)
			{
			$minSpeed = \min($minSpeed, $speed);
			$maxSpeed = \max($maxSpeed, $speed);
			}
		}
	$memberDefault = 'C' == $category;

	if (9999 == $minSpeed)
		{
		$minSpeed = $maxSpeed = ' ';
		}
	$description = '';
	$fields = ['categoryId' => $categoryId, 'category' => $category, 'minSpeed' => $minSpeed, 'maxSpeed' => $maxSpeed, 'ordering' => $ordering, 'memberDefault' => $memberDefault, 'description' => $description, ];
	$newCatTable->insert($fields);
	}

foreach ($categoryTable->getAll() as $oldCategory)
	{
	$oldCategoryId = $oldCategory['categoryId'];

	foreach ($newCategories as $newCategoryId => $category)
		{
		if ($category == $oldCategory['category'][0])
			{
			\PHPFUI\ORM::execute('update memberCategory set categoryId=? where categoryId=?', [$newCategoryId + 100, $oldCategoryId]);

			break;
			}
		}
	}
\PHPFUI\ORM::execute('update memberCategory set categoryId=categoryId-100 where categoryId > 0');

$paces = [];
$newPaceTable = new NewPace();
$paceId = 1;
$fields = ['paceId' => $paceId,
	'categoryId' => 0,
	'pace' => 'All',
	'minSpeed' => '',
	'maxSpeed' => '',
	'maxRiders' => 0,
	'ordering' => 0,
];
$newPaceTable->insert($fields);
$paces[$paceId] = $fields;

foreach ($newPaces as $pace => $oldPace)
	{
	++$paceId;
	$speed = (float)\substr($pace, 1);

	if ($speed)
		{
		$minSpeed = $speed - 0.4;
		$maxSpeed = $speed + 0.5;
		}
	else
		{
		$minSpeed = $maxSpeed = ' ';
		}
	$ordering = $paceId * 100;
	$category = $newCatTable->read(['category' => $pace[0]]);
	$categoryId = $category['categoryId'] ?? 0;
	$maxRiders = 0;
	$fields = ['paceId' => $paceId, 'categoryId' => $categoryId, 'pace' => $pace, 'minSpeed' => $minSpeed, 'maxSpeed' => $maxSpeed, 'maxRiders' => $maxRiders, 'ordering' => $ordering, ];
	$paces[$pace] = $fields;
	$newPaceTable->insert($fields);
	}

$rideTable = new \App\CRUD\Ride();
$rides = $rideTable->readMultiple();

foreach ($rides as $ride)
	{
	$paceId = 0;
	$pace = $paceTable->getPace($ride['paceId']);
	$category = $pace[0];

	if ('H' == $category)
		{
		$category = 'G';
		}

	if ($ride['averagePace'] > 1 && $ride['numberOfRiders'] > 1)
		{
		$speed = (int)((float)$ride['averagePace'] + 0.4);
		$newPace = $category . $speed;

		foreach ($paces as $pace => $paceInfo)
			{
			if ($pace == $newPace)
				{
				$paceId = $paceInfo['paceId'];

				break;
				}
			}

		if (! $paceId)
			{
			$speed = \number_format((float)$ride['averagePace'], 1);

			foreach ($paces as $paceInfo)
				{
				if ($speed <= $paceInfo['maxSpeed'])
					{
					$paceId = $paceInfo['paceId'];
					$newPace = $paceInfo['pace'];
					}
				}
			}

		if (! $paceId)
			{
			$paceId = 1;
			$newPace = 'All';
			}
		}
	else
		{
		foreach ($newPaces as $newPace => $oldPace)
			{
			if ($oldPace == $pace)
				{
				$paceId = $paces[$newPace]['paceId'];

				break;
				}
			}
		}
	echo "{$pace} {$ride['averagePace']} => {$newPace}\n";

	$ride['paceId'] = $paceId;
	$rideTable->update($ride);
	}
