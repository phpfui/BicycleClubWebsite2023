<?php

namespace App\Model;

class Restore
	{
	/**
	 * @var array<string>
	 */
	private array $errorMessages = [];

	public function __construct(private readonly string $fileName)
		{
		}

	/**
	 * @return array<string>
	 */
	public function getErrors() : array
		{
		return $this->errorMessages;
		}

	public function run() : bool
		{
		if (! \file_exists($this->fileName))
			{
			$this->errorMessages[] = "File not found: {$this->fileName}";

			return false;
			}

		$sql = '';
		$lines = \file($this->fileName);

		foreach ($lines as $line)
			{

			// Ignoring comments from the SQL script
			if (\str_starts_with((string)$line, '--') || '' == $line)
				{
				continue;
				}

			$sql .= $line;

			if (\str_ends_with(\trim((string)$line), ';'))
				{
				\PHPFUI\ORM::execute($sql);
				$lastError = \PHPFUI\ORM::getLastError();

				if ($lastError)
					{
					$this->errorMessages[] = $lastError . ' SQL: ' . $sql;
					}
				$sql = '';
				}
			} // end foreach

		return ! \count($this->errorMessages);
		}
	}
