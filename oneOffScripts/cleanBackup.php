<?php

include __DIR__ . '/../commonbase.php';

class cleanBackup
	{
	public function __construct(private $backupHandle, private $targetHandle)
		{
		}

	public function run() : void
		{
		while (($line = \fgets($this->backupHandle)) !== false)
			{
			\fwrite($this->targetHandle, $this->processLine($line));
			}
		}

	private function replaceOption(string $option, string $replacement, string $line) : string
		{
		$start = \stripos($line, $option);

		if (false === $start)
			{
			return $line;
			}

		$lineEnd = \strlen($line);

		if (\strlen($replacement))
			{
			$start += \strlen($option);
			$end = $start;
			}
		else
			{
			$end = $start + \strlen($option);
			}

		while ($end < $lineEnd && ' ' != $line[$end] && ';' != $line[$end] && ',' != $line[$end])
			{
			++$end;
			}

		return \substr($line, 0, $start) . $replacement . \substr($line, $end);
		}

	private function processLine(string $line) : string
		{
		static $options = ['CHARSET=' => 'UTF8MB4', 'COLLATE ' => '', 'COLLATE=' => 'utf8mb4_general_ci', 'DEFINER=' => 'CURRENT_USER', ];

		foreach ($options as $option => $replacement)
			{
			$line = $this->replaceOption($option, $replacement, $line);
			}

		return $line;
		}
	}

echo "Clean up MySQL backup to correct char sets and collation\n\n";

if (3 != \count($argv))
	{
	echo "Incorrect number of parameters, two required\n\n";
	echo "Syntax: cleanBackup.php backup.sql newFile.sql\n";

	exit;
	}

\array_shift($argv);
$backupPath = \array_shift($argv);
$targetPath = \array_shift($argv);

if (! \file_exists($backupPath))
	{
	echo "File {$backupPath} was not found\n";

	exit;
	}

if (\file_exists($targetPath))
	{
	echo "File {$targetPath} already exists\n";

//	exit;
	}

$backupHandle = \fopen($backupPath, 'r');

if (! $backupHandle)
	{
	echo "Can't open {$backupPath} for reading\n";

	exit;
	}

$targetHandle = \fopen($targetPath, 'w');

if (! $targetHandle)
	{
	echo "Can't open {$targetPath} for writing\n";

	exit;
	}

$cleaner = new cleanBackup($backupHandle, $targetHandle);
$cleaner->run();
