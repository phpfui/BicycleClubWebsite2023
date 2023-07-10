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
$videoTable = new \App\Table\Video();
$videoTable->setWhere(new \PHPFUI\ORM\Condition('fileName', '%.mp4', new \PHPFUI\ORM\Operator\NotLike()));

foreach ($videoTable->getRecordCursor() as $video)
	{
	$video->fileName = $video->fileName . '.mp4';
	$video->update();
	}
