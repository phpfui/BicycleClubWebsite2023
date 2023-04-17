<?php

// set the server name which determains which db to use
$_SERVER['SERVER_NAME'] = $argv[1] ?? 'localhost';

include __DIR__ . '/../common.php';

echo "Loaded settings file {$dbSettings->getLoadedFileName()}\n";

$fileModel = new \App\Model\ForumAttachmentFiles();

foreach ($fileModel->getAll() as $originalFile)
	{
	$newName = $file = $originalFile;

	if ($index = \strpos($file, '_size'))
		{
		$newName = \substr($file, 0, $index);
		$file = $newName;
		}

	if ($index = \strpos($file, '_'))
		{
		$newName = \substr($file, 0, $index);
		$file = $newName;
		}

	if ($index = \strpos($file, 'size'))
		{
		$newName = \substr($file, 0, $index);
		$file = $newName;
		}

	if (! \strpos($file, '.'))
		{
		$newName = '';

		for ($i = 0; $i < \strlen($file); ++$i)
			{
			$char = $file[$i];

			if (\is_numeric($char))
				{
				$newName .= $char;
				}
			else
				{
				$newName .= '.';
				$newName .= \substr($file, $i);

				break;
				}
			}
		$file = $newName;
		}

	if ($newName != $originalFile)
		{
		$fileModel->rename($originalFile, $newName);
		}
	echo $newName . '<br>';
	}
