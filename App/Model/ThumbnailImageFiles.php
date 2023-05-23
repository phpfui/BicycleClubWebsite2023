<?php

namespace App\Model;

class ThumbnailImageFiles extends \App\Model\TinifyImage
	{
	public function __construct(string $type, private string $index, protected array $item = [])
		{
		$this->item = $item;
		$this->index = $index;
		parent::__construct($type);
		$this->mimeTypes = [
			'.jpg' => 'image/jpeg',
			'.jpeg' => 'image/jpeg',
			'.png' => 'image/png',
			'.webp' => 'image/webp',
		];
		}

	/**
	 * creates a re-sized image
	 *
	 * @param int $maxDimension of resized image
	 */
	public function createThumb(int $maxDimension = 0) : bool
		{
		$maxDimension = (int)$maxDimension;

		if (! $maxDimension)
			{
			$maxDimension = 150;
			}
		$originalFilename = PUBLIC_ROOT . $this->getPhotoFileName();

		if (! $this->isSupportedImageType($originalFilename))
			{
			\App\Model\Session::setFlash('alert', 'Unsupported image type.');
			\App\Tools\Logger::get()->debug(__METHOD__ . ': Unsupported image type');

			return false;
			}

		$newFilename = PUBLIC_ROOT . $this->getThumbFileName();
		$settingTable = new \App\Table\Setting();
		\Tinify\Tinify::setKey($settingTable->value('TinifyKey'));
		$source = \Tinify\Source::fromFile($originalFilename);
		$resized = $source->resize(['method' => 'scale', 'height' => $maxDimension]);

		return false !== $resized->toFile($newFilename);
		}

	public function delete(string | int $id = '') : void
		{
		\App\Tools\File::unlink(PUBLIC_ROOT . $this->getPhotoFileName());
		\App\Tools\File::unlink(PUBLIC_ROOT . $this->getThumbFileName());
		}

	public function getItem() : array
		{
		return $this->item;
		}

	public function getKey() : string
		{
		return $this->item[$this->index] ?? '';
		}

	public function getPhotoFileName(string $directory = '') : string
		{
		if ($directory)
			{
			$directory .= '/';
			}

		return "/{$this->type}/" . $directory . $this->getBaseName($this->item[$this->index] ?? 0) . ($this->item['extension'] ?? '');
		}

	public function getPhotoFilePath(string $directory = '') : string
		{
		return PUBLIC_ROOT . $this->getPhotoFileName($directory);
		}

	public function getPhotoImg(string $altText = '') : \PHPFUI\Image
		{
		return new \PHPFUI\Image($this->verifyImage($this->getPhotoFileName()), $altText);
		}

	public function getThumbFileName() : string
		{
		return "/{$this->type}/thumbs/" . $this->getBaseName($this->item[$this->index] ?? '') . ($this->item['extension'] ?? '');
		}

	public function getThumbFilePath() : string
		{
		return PUBLIC_ROOT . $this->getThumbFileName();
		}

	public function getThumbnail() : \PHPFUI\Thumbnail
		{
		return new \PHPFUI\Thumbnail(new \PHPFUI\Image($this->getPhotoFileName()));
		}

	public function getThumbnailImg() : \PHPFUI\Image
		{
		return new \PHPFUI\Image($this->verifyImage($this->getThumbFileName()));
		}

	public function update(array $item) : void
		{
		$this->item = $item;
		}

	public function verifyImage(string $file) : string
		{
		if (! \is_file(PUBLIC_ROOT . $file))
			{
			$file = '/images/logo.png';
			}

		return $file;
		}
	}
