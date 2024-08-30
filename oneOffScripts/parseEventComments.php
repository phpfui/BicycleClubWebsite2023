<?php

// set the server name which determains which db to use
$_SERVER['SERVER_NAME'] = $argv[1] ?? 'localhost';

include __DIR__ . '/../common.php';

echo "Loaded settings file {$dbSettings->getLoadedFileName()}\n";

$eventTable = new \App\Table\Event();
$eventTable->setOrderBy('eventId', 'desc');
$eventTable->setLimit(1);

$event = $eventTable->getRecordCursor()->current();

echo $event->title . "\n";

$reservationPersonTable = new \App\Table\ReservationPerson();
$reservationPersonTable->setWhere(new \PHPFUI\ORM\Condition('eventId', $event->eventId));

$personCursor = $reservationPersonTable->getRecordCursor();

$subWords = [
	'shroom' => 'mushroom',
	'vegetarian' => 'veggie',
	'sauage' => 'sausage',
	'bell' => 'pepper',
	'' => 'cheese',
	'plain' => 'cheese',
	'plan' => 'cheese',
	'veg' => 'veggie',
	'vege' => 'veggie',
	'vegtable' => 'veggie',
	'veggy' => 'veggie',
	'peperoni' => 'pepperoni',
];

$skipWords = [
	'the' => true,
	'mix' => true,
	'lover' => true,
	'thing' => true,
	'pizza' => true,
	'please' => true,
	'etc' => true,
	'also' => true,
	'good' => true,
	'all' => true,
	'off' => true,
	'on' => true,
	'no' => true,
	'fine' => true,
	'with' => true,
	'is' => true,
	'of' => true,
	'thank' => true,
	'just' => true,
	'you' => true,
	'these' => true,
	'or' => true,
	'and' => true,
	'andor' => true,
	'any' => true,
	'anything' => true,
	'but' => true,
	'whatever' => true,
	'i' => true,
	'none' => true,
];

$items = [];

foreach ($personCursor as $person)
	{
	$parts = \explode(' ', \preg_replace('/[^a-z]/', ' ', \strtolower($person->comments)));

	foreach ($parts as $item)
		{
		$item = \rtrim($item, 's');

		if (\array_key_exists($item, $skipWords))
			{
			continue;
			}

		$item = $subWords[$item] ?? $item;

		if (! \array_key_exists($item, $items))
			{
			$items[$item] = 0;
			}
		++$items[$item];
		}
	}

\arsort($items);
\print_r($items);
