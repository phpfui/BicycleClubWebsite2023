<?php

namespace App\Tools\CSV;

/**
 * Base class for a simple CSV reader supporting files, string or streams as input
 *
 * Emulates an array of records (arrays), implimented as an Iterator, so can be used in a foreach statements.
 *
 * - If your CSV has headers (the default), then the keys of the returned array will be the header values.
 * - You can also specify a different field separator, for example ("\t") for tabs.
 * - Use rewind to reset to the top of the file.
 * - The header record is NEVER returned as a record.  The first iteration will be the first record in the file, excluding the header record if specified.
 * - An empty stream will return -1 as the current index.
 *
 * @implements \Iterator<array<string, string>>
 */
abstract class Reader implements \Iterator
	{
	/** @var array<string, string> */
	private array $current = [];

	/** @var array<string> */
	private array $headers = [];

	private int $index = 0;

	/**
	 * @param ?resource $stream
	 */
	public function __construct(protected $stream, private readonly bool $headerRow, private readonly string $separator, private string $enclosure, private string $escape)
		{
		$this->rewind();
		}

	/** @return array<string, string> */
	public function current() : array
		{
		return $this->current;
		}

	/**
	 * @return int index of the record in the CSV stream. Zero is index of first data record. -1 means the CSV stream is empty
	 */
	public function key() : int
		{
		return $this->index;
		}

	/**
	 * Load the next record in the stream
	 */
	public function next() : void
		{
		$this->current = [];

		if ($this->stream)
			{
			$array = \fgetcsv($this->stream, 0, $this->separator, $this->enclosure, $this->escape);

			if ($array)
				{
				++$this->index;

				if ($this->headers)
					{
					foreach ($this->headers as $index => $header)
						{
						if (isset($array[$index]))
							{
							$this->current[$header] = $array[$index];
							}
						else
							{
							break;
							}
						}
					}
				else
					{
					$this->current = $array;
					}
				}
			}
		}

	/**
	 * rewind the reader to the first record
	 */
	public function rewind() : void
		{
		$this->index = -1;
		$this->open();

		if ($this->stream)
			{
			\rewind($this->stream);

			if ($this->headerRow)
				{
				$this->headers = \fgetcsv($this->stream, 0, $this->separator, $this->enclosure, $this->escape);
				}
			}
		$this->next();
		}

	/**
	 * You can specify headers if your file does not include them.  The headers will be used as the key in the returned associative array for each record.
	 *
	 * @param array<string> $headers of strings
	 */
	public function setHeaders(array $headers) : static
		{
		$this->headers = $headers;

		return $this;
		}

	public function valid() : bool
		{
		return $this->current && $this->stream;
		}

	/**
	 * Override the open method to support your data type
	 */
	protected function open() : static
		{
		return $this;
		}
	}
