<?php

namespace App\Model;

class ProfileImages extends \App\Model\ThumbnailImageFiles
	{
	public function __construct(array $item = [])
		{
		parent::__construct('../files/profiles', 'memberId', $item);
		}

	public function crop() : void
		{
		// create an image manager instance with favored driver
		$manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
		$path = $this->getPhotoFilePath();
		$member = $this->getItem();
		$image = $manager->read($path);
		$image->crop($member['profileWidth'], $member['profileHeight'], $member['profileX'], $member['profileY']);
		$image->save($this->getCropPath());
//		// saving it twice works?
//		$image->save($this->getCropPath());
		}

	public function cropExists() : bool
		{
		return $this->exists($this->getCropPath());
		}

	public function delete(string | int $id = '') : void
		{
		\App\Tools\File::unlink($this->getPhotoFilePath());
		\App\Tools\File::unlink($this->getCropPath());
		}

	public function exists(string $path) : bool
		{
		return \file_exists($path) && @\exif_imagetype($path);
		}

	public function getCropImg() : ?\PHPFUI\Image
		{
		if (! $this->exists($this->getCropPath()))
			{
			return null;
			}
		$member = $this->getItem();

		$path = '/Membership/image/' . $this->getKey() . '/1';

		return new \PHPFUI\Image($path, $member['firstName'] . ' ' . $member['lastName']);
		}

	public function getCropPath() : string
		{
		return $this->getPhotoFilePath('crop');
		}

	public function getImg() : ?\PHPFUI\Image
		{
		if (! $this->exists($this->getPhotoFilePath()))
			{
			return null;
			}
		$member = $this->getItem();

		$path = '/Membership/image/' . $this->getKey();

		return new \PHPFUI\Image($path, $member['firstName'] . ' ' . $member['lastName']);
		}

	public function processFile(string | int $file) : string
		{
		// Don't tinify master photo
		return '';
		}
	}
