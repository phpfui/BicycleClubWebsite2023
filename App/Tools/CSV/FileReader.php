<?php

namespace App\Tools\CSV;

/**
 * A simple CSV reader based on a file containing CSV data
 *
 * @inheritDoc
 */
class FileReader extends Reader
	{
	public function __construct(private readonly string $fileName, bool $headerRow = true, string $separator = ',', string $enclosure = '"', string $escape = '\\')
		{
		parent::__construct(null, $headerRow, $separator, $enclosure, $escape);
		}

	protected function open() : static
		{
		if (\file_exists($this->fileName))
			{
			if ($this->stream)
				{
				\fclose($this->stream);
				$this->stream = null;
				}
			$this->stream = @\fopen($this->fileName, 'r');
			}

		return $this;
		}
	}
