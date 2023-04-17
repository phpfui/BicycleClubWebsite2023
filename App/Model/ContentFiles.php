<?php

namespace App\Model;

class ContentFiles extends \App\Model\TinifyImage
	{
	public function __construct()
		{
		parent::__construct('images/content');
		}

	public function processFile(string | int $file) : string
		{
		$this->resizeToWidth($file, 1000);

		return '';
		}
	}
