<?php

// set the server name which determains which db to use
$_SERVER['SERVER_NAME'] = $argv[1] ?? 'localhost';

include 'common.php';

echo "Loaded settings file {$dbSettings->getLoadedFileName()}\n";

//$parser = new \ZBateson\MailMimeParser\MailMimeParser();
//
//$handle = fopen('c:\\download\\Image testing.eml', 'r');
//$message = $parser->parse($handle, false);
//foreach ($message->getAllParts() as $part)
//	{
//	if ('image/png' == $part->getContentType())
//		{
//		print_r($part->getAllHeaders());
//		}
//	}
//

foreach (\PHPFUI\ORM\Table::getAllTables() as $table)
	{
	echo "{$table->getTableName()}: {$table->count()}\n";
	}


$ride = new \App\Record\Ride(23058);

$model = new \App\Model\Ride();

$calendar = $model->getCalendarObject($ride);

file_put_contents('ride.ics', $calendar->export());
