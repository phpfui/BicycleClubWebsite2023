<?php

namespace App\Tools;

/**
 * \PHPFUI\Tools\CSVWriter: a simple class to output a CSV file given data in an array
 *
 * Features:
 *  - Automatically output to browser download by default
 *  - Option to generate file output
 *  - User specified delimiter (default: comma)
 *  - Auto header row generation
 *  - Sparse array output where emtpy columns are empty in the output
 */
class CSVWriter
	{
	private bool $headerRow = false;

	private $out = null;

	private array $rowColumns = [];

	/**
	 * Make a CSVWriter.
	 *
	 * @param string $filename to be output. If $download is true then this is the name the user will see.  Avoid OS specific filespec conventions. If $download is false, then you can specify a directory or other OS specific filespec.
	 */
	public function __construct(string $filename, private readonly string $delimiter = ',', private readonly bool $download = true)
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
		}

	public function __destruct()
		{
		\fclose($this->out);

		if ($this->download)
			{
			exit;
			}
		}

	/**
	 * if set, the first row output will also add a header row.  Headers will be from keys of first row added, or headers specified by setRowColumns
	 */
	public function addHeaderRow(bool $headerRow = true) : static
		{
		$this->headerRow = true;

		return $this;
		}

	/**
	 * Output a single row.  Array members must be compatible with string output.
	 *
	 * @param array<string | int, mixed> $row */
	public function outputRow(array $row) : static
		{
		if ($this->rowColumns)
			{
			$outputRow = [];

			foreach ($this->rowColumns as $column)
				{
				$outputRow[$column] = $row[$column] ?? '';
				}
			}
		else
			{
			$outputRow = $row;
			}

		if ($this->headerRow)
			{
			$this->headerRow = false;
			\fputcsv($this->out, \array_keys($outputRow), $this->delimiter);
			}

		\fputcsv($this->out, $outputRow, $this->delimiter);

		return $this;
		}

	/**
	 * Set the row column names and order. Setting this allows for sparse array output.
	 *
	 * @param array<string> $columns
	 */
	public function setRowColumns(array $columns) : static
		{
		$this->rowColumns = $columns;

		return $this;
		}
	}
