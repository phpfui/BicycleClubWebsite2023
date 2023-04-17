<?php

namespace App\Model;

class RideImages extends \App\Model\TinifyImage
	{
	public function __construct()
		{
		parent::__construct('images/rides');
		}

	public function processFile(string | int $file) : string
		{
		$this->resizeToWidth($file, 1000);

		return '';
		}
	}
