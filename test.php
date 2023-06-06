<?php

include 'common.php';

$parser = new \ZBateson\MailMimeParser\MailMimeParser();

$handle = fopen('c:\\download\\Image testing.eml', 'r');
$message = $parser->parse($handle, false);
foreach ($message->getAllParts() as $part)
	{
	if ('image/png' == $part->getContentType())
		{
		print_r($part->getAllHeaders());
		}
	}

