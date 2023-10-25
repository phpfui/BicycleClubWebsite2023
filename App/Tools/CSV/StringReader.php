<?php

namespace App\Tools\CSV;

/**
 * A simple CSV reader based on CSV data in a string
 *
 * @inheritDoc
 */
class StringReader extends Reader
	{
	public function __construct(private readonly string $data, bool $headerRow = true, string $separator = ',', string $enclosure = '"', string $escape = '\\')
		{
		parent::__construct(null, $headerRow, $separator, $enclosure, $escape);
		}

	protected function open() : static
		{
		if ($this->stream)
			{
			\fclose($this->stream);
			}
		$this->stream = \fopen('php://memory', 'r+');
		\fwrite($this->stream, $this->data);

		return $this;
		}
	}
