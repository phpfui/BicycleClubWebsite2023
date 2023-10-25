<?php

namespace App\Tools\CSV;

/**
 * \CSV\StringWriter
 *
 * uses __toString for output of CSV data
 */
class StringWriter extends Writer implements \Stringable
	{
	/**
	 * @var resource $out stream for output
	 */
	private $out;

	/**
	 * Create a \CSV\StringWriter
	 */
	public function __construct(string $separator = ',', string $enclosure = '"', string $escape = '\\', string $eol = "\n")
		{
		$this->out = \fopen('php://memory', 'w');
		parent::__construct($this->out, $separator, $enclosure, $escape, $eol);
		}

	public function __toString() : string
		{
		\rewind($this->out);

		return \stream_get_contents($this->out);
		}
	}
