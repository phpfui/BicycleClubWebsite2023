<?php

// set the server name which determains which db to use
$_SERVER['SERVER_NAME'] = $argv[1] ?? 'localhost';

include __DIR__ . '/../../common.php';

echo "Loaded settings file {$dbSettings->getLoadedFileName()}\n";

$member = new \App\Record\Member();
$member->read(['firstName' => 'Edmund', 'lastName' => 'Ryan']);

$rootFolderName = 'Board Meeting Minutes';
$rootFolder = new \App\Record\FileFolder();
$rootFolder->read(['fileFolder' => $rootFolderName]);

if (! $rootFolder->loaded())
	{
	$rootFolder->fileFolder = $rootFolderName;
	$rootFolder->parentId = 0;
	$rootFolder->insert();
	}

$iterator = new \DirectoryIterator(__DIR__ . '/zoho/board_minutes');

$monthNames = [];
$monthAbbrevs = [];
$monthsTrans = ['sept' => 'september', 'sept.' => 'september', 'annual' => 'december', 'septembert' => 'september'];

for ($i = 1; $i <= 12; ++$i)
	{
	$time = \strtotime('2020-' . $i . '-12 12:12:12');
	$monthNames[$i] = \strtolower(\date('F', $time));
	$monthAbbrevs[$i] = \strtolower(\date('M', $time));
	}

$folders = [];
$model = new \App\Model\FileFiles();

foreach ($iterator as $item)
	{
	if (! $item->isDir())
		{
		$fileName = \strtolower($item->getFilename());
		$parts = \explode('.', $fileName);
		$extension = \array_pop($parts);
		$file = \implode('_', $parts);
		$file = \str_replace(['.', ',', '-', ], '_', $file);
		$parts = \explode('_', $file);
		$final = [];

		foreach ($parts as $part)
			{
			if (! empty($part))
				{
				if (isset($monthsTrans[$part]))
					{
					$part = $monthsTrans[$part];
					}

				if ((int)$part > 0)
					{
					$final[] = $part;
					}
				elseif (\in_array($part, $monthNames))
					{
					$final[] = $part;
					}
				else
					{
					$key = \array_search($part, $monthAbbrevs);

					if ($key)
						{
						$final[] = $monthNames[$key];
						}
					}
				}

			if (3 == \count($final))
				{
				break;
				}
			}

		$year = '';
		$finalString = '';
		$allInts = true;

		foreach ($final as $part)
			{
			if (! (int)$part)
				{
				$allInts = false;
				}
			}

		if ($allInts)
			{
			$finalString = \implode('-', $final);
			}
		else
			{
			foreach ($final as $part)
				{
				if ((int)$part > 2000)
					{
					$year = $part;
					}
				else
					{
					$finalString .= $part . ' ';
					}
				}
			$finalString .= $year;
			}

		$time = \strtotime($finalString);
		echo $item->getFilename() . ' => ' . \date('Y-m-d', $time) . "\n";
		$year = \date('Y', $time);
		$fileFolder = new \App\Record\FileFolder();

		if (! $fileFolder->read(['parentId' => $rootFolder->fileFolderId, 'fileFolder' => $year]))
			{
			$fileFolder->parentId = $rootFolder->fileFolderId;
			$fileFolder->fileFolder = $year;
			}
		$file = new \App\Record\File();
		$file->fileFolder = $fileFolder;
		$file->member = $member;
		$file->extension = '.' . $extension;
		$file->file = 'Board Meeting Minutes';
		$file->fileName = \str_replace($file->extension, '', $item->getFilename());
		$file->insert();
		$destination = $model->getPath() . $file->fileId . $file->extension;
		\rename($item->getPathname(), $destination);
		}
	}
