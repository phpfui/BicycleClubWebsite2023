<?php

include __DIR__ . '/../../../commonbase.php';

$directoryPath = PROJECT_ROOT . '/Tests/Functional/requests';

$directory = new \DirectoryIterator($directoryPath);

$rows = [];

foreach ($directory as $file)
	{
	if ($file->isFile() && $file->isReadable())
		{
		$path = $file->getPathname();

		if (\stripos($path, '.csv'))
			{
			$reader = new \App\Tools\CSVReader($path);

			foreach ($reader as $row)
				{
				if (empty($row['missing']))
					{
					$type = 'POST' == $row['REQUEST_METHOD'] ? '_post' : '_get';
					$key = $row['REQUEST_METHOD'] . $row['REQUEST_URI'] . $row[$type];
					$rows[$key] = $row;
					}
				}
			unset($reader);
			}
		}
	}

$writer = new \App\Tools\CSVWriter($directoryPath . '/HttpRequest.csv', ',', false);
$writer->addHeaderRow();

foreach ($rows as $row)
	{
	$writer->outputRow($row);
	}
