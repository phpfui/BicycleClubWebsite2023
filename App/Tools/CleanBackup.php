<?php

namespace App\Tools;

class CleanBackup
	{
	private $backupHandle;

	private $targetHandle;

	public function __construct(string $backupPath, string $targetPath)
		{
		$this->backupHandle = \fopen($backupPath, 'r');

		if (! $this->backupHandle)
			{
			echo "Can't open {$backupPath} for reading\n";

			exit;
			}

		$this->targetHandle = \fopen($targetPath, 'w');

		if (! $this->targetHandle)
			{
			echo "Can't open {$targetPath} for writing\n";

			exit;
			}
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
