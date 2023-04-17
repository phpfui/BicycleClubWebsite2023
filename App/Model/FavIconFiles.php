<?php

namespace App\Model;

class FavIconFiles extends \App\Model\File
	{
	private array $errors = [];

	public function __construct()
		{
		parent::__construct('../files/favicons');
		}

	public function getErrors() : array
		{
		if ($this->getLastError())
			{
			$this->errors[] = $this->getLastError();
			}

		return $this->errors;
		}

	public function processFile(string | int $path) : string
		{
		$zip = new \ZipArchive();
		$res = $zip->open($path);

		if ($res)
			{
			$zip->extractTo($this->getPath());

			foreach (\glob($this->getPath() . '*.*') as $filename)
				{
				if (! \str_contains('.zip', (string)$filename))
					{
					$file = \strrchr((string)$filename, '/');
					\rename($filename, PUBLIC_ROOT . $file);
					}
				}
			$zip->close();
			}
		else
			{
			$this->errors[] = "Error opening file ({$path})";
			}

		return '';
		}
	}
