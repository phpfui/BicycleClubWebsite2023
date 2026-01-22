<?php

namespace App\Tools\CSV;

/**
 * A simple CSV reader based on an already open stream
 *
 * @inheritDoc
 */
class StreamReader extends Reader
	{
	/**
	 * @param ?resource $stream
	 */
	public function __construct(protected $stream, bool $headerRow = true, string $separator = ',', string $enclosure = '"', string $escape = '\\') // @mago-expect lint:parameter-type
		{
		parent::__construct($stream, $headerRow, $separator, $enclosure, $escape);
		$this->rewind();
		}
	}
