<?php

namespace App\Model;

class PhotoFiles extends \App\Model\TinifyImage
	{
	public function __construct()
		{
		parent::__construct('../files/photos');
		}

	public function isSupportedImageType(string $filepath) : bool
		{
		// don't use Tinify for photo albums
		return false;
		}

	public function processFile(string | int $file) : string
		{
		$this->autoRotate($file);

		return '';
		}
	}
