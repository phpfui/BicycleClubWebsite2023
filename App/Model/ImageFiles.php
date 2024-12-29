<?php

namespace App\Model;

class ImageFiles extends \App\Model\TinifyImage
	{
	public function __construct()
		{
		parent::__construct('../files/images');
		}

	public function getImg(string $file) : \PHPFUI\Image
		{
		$index = \strpos($file, '.');
		$base = 'Image';

		if (false !== $index)
			{
			$base = \substr($file, 0, $index);
			}

		$path = $this->get($file);

		if (! \file_exists($path))
			{
			return new \PHPFUI\Image('/images/missing.jpg', 'Image not found');
			}
		$data = \base64_encode(@\file_get_contents($path));

		return new \PHPFUI\Image("data:image/jpeg;base64,{$data}", $base);
		}

	public function url(string $filename) : string
		{
		return '';
		}
	}
