<?php

namespace App\Tools\CSV;

/**
 * \PHPFUI\Tools\CSVWriter: a simple class to output a CSV file given data in an array
 *
 * Features:
 *  - User specified delimiter (default: comma)
 *  - Auto header row generation
 *  - Sparse array output where emtpy columns are empty in the output
 */
class FileWriter extends Writer
	{
	/**
	 * @var resource $out opened output file stream
	 */
	private $out;

	/**
	 * Make a \CSV\File\Writer.
	 *
	 * @param string $filename to be output. If $download is true then this is the name the user will see.  Avoid OS specific filespec conventions. If $download is false, then you can specify a directory or other OS specific filespec.
	 */
	public function __construct(string $filename, private readonly bool $download = true, string $separator = ',', string $enclosure = '"', string $escape = '\\', string $eol = "\n")

		{
		if ($this->download)
			{
			\header('Content-Type: application/csv');
			\header('Content-Disposition: inline; filename="' . $filename . '"');
			\header('Cache-Control: private, max-age=0, must-revalidate');
			\header('Pragma: public');
			$this->out = \fopen('php://output', 'w');
			}
		else
			{
			$this->out = \fopen($filename, 'w');
			}
		parent::__construct($this->out, $separator, $enclosure, $escape, $eol);
		}

	public function __destruct()
		{
		\fclose($this->out);

		if ($this->download)
			{
			exit;
			}
		}
	}
