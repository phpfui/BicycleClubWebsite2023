<?php

namespace App\Tools\CSV;

/**
 * \CSV\Writer: a simple class to output a CSV file given data in an array
 *
 * Features:
 *  - User specified separator (default: comma)
 *  - Auto header row generation
 *  - Sparse array output where emtpy columns are empty in the output
 */
abstract class Writer
	{
	private bool $headerRow = true;

	/** @var array<string> */
	private array $rowColumns = [];

	/**
	 * Make a Writer.
	 *
	 * @param resource $outputStream
	 */
	public function __construct(private $outputStream, private readonly string $separator, private readonly string $enclosure, private readonly string $escape, private readonly string $eol)
		{
		}

	/**
	 * if set, the first row output will also add a header row.  Headers will be from keys of first row added, or headers specified by setRowColumns
	 */
	public function addHeaderRow(bool $headerRow = true) : static
		{
		$this->headerRow = $headerRow;

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
			\fputcsv($this->outputStream, \array_keys($outputRow), $this->separator, $this->enclosure, $this->escape, $this->eol);
			}

		\fputcsv($this->outputStream, $outputRow, $this->separator, $this->enclosure, $this->escape, $this->eol);

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
