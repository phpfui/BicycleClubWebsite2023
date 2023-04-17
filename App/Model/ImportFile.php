<?php

namespace App\Model;

class ImportFile extends \App\Model\File
	{
	protected array $mimeTypes = [
		'.csv' => 'text/csv',
		'.tsv' => 'text/csv',
	];

	public function __construct(private readonly string $fileName)
		{
		parent::__construct('../import');
		}

	public function getFileName() : string
		{
		return $this->getPath() . '/' . $this->fileName;
		}

	public function processFile(string | int $path) : string
		{
		\rename($path, $this->getFileName());

		return '';
		}
	}
