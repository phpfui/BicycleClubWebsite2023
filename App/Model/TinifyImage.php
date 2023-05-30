<?php

namespace App\Model;

class TinifyImage extends \App\Model\File
	{
	/**
	 * Construct a TinifyImage and set the library key
	 *
	 * @param string $type of file to process
	 */
	public function __construct(string $type)
		{
		$settingTable = new \App\Table\Setting();
		\Tinify\Tinify::setKey($settingTable->value('TinifyKey'));
		parent::__construct($type);
		}

	/**
	 * load, detect orientation, rotate and save if needed
	 *
	 * @param string $filename path to image
	 *
	 * @return bool true on changed
	 */
	public function autoRotate(string $filename) : bool
		{
		$type = $this->getImageType($filename);
		$img = $this->openImageFromAny($filename, $type);
		$exif = [];
		if (file_exists($filename))
			{
			$exif = @\exif_read_data($filename);
			}
		$changed = false;

		if ($img && $exif && isset($exif['Orientation']))
			{
			$ort = $exif['Orientation'];

			if (6 == $ort || 5 == $ort)
				{
				$img = \imagerotate($img, 270, 0);
				$changed = true;
				}
			elseif (3 == $ort || 4 == $ort)
				{
				$img = \imagerotate($img, 180, 0);
				$changed = true;
				}
			elseif (8 == $ort || 7 == $ort)
				{
				$img = \imagerotate($img, 90, 0);
				$changed = true;
				}

			if (5 == $ort || 4 == $ort || 7 == $ort)
				{
				\imageflip($img, IMG_FLIP_HORIZONTAL);
				$changed = true;
				}
			}

		if ($changed)
			{
			$changed = $this->saveImageFromAny($filename, $img, $type);
			}

		return $changed;
		}

	public function download(string | int $name, string $extension, string $downloadName = '') : string
		{
		$file = $this->get($name . $extension);

		if (\file_exists($file) && ($data = \file_get_contents($file)))
			{
			$extension = \str_replace('.', '', $extension);

			$type = 'image/';

			if (\str_contains($extension, 'pdf'))
				{
				$type = 'application/pdf';
				$extension = '';
				}
			elseif ('jpg' == \strtolower($extension))
				{
				$extension = 'jpeg';
				}
			$type .= $extension;
			\header('Content-type: ' . $type);
			\header('Content-Disposition: inline; filename="' . $downloadName . '"');
			echo $data;

			return '';
			}

		return $file;
		}

	/**
	 * get the type of an image file
	 *
	 * @param string $filepath to image
	 *
	 * @return int (0 = invalid, 1 = gif, 2 = jpg, 3 = png, 6 = bmp)
	 */
	public function getImageType(string $filepath) : int
		{
		$type = @\exif_imagetype($filepath);
		$allowedTypes = [
			IMAGETYPE_GIF,
			IMAGETYPE_JPEG,
			IMAGETYPE_PNG,
			IMAGETYPE_BMP,
			IMAGETYPE_WBMP,
			IMAGETYPE_WEBP,
		];

		if (! \in_array($type, $allowedTypes))
			{
			return 0;
			}

		return $type;
		}

	/**
	 * 	 * Returns useful info about the image like the GPS coordinates as an array.
	 *
	 * @return (float|string)[]
	 *
	 * @psalm-return array{taken?: string, latitude?: float, longitude?: float}
	 */
	public function getInformation(int $name, string $extension) : array
		{
		$returnValue = [];
		$filePath = $this->get((string)$name . $extension);
		$exif = [];
		if (file_exists($filePath))
			{
			$exif = @\exif_read_data($filePath);
			}

		if (! $exif)
			{
			$exif = [];
			}

		$timeField = $exif['DateTimeOriginal'] ?? $exif['DateTime'] ?? $exif['GPSDateStamp'] ?? null;

		if ($timeField)
			{
			$returnValue['taken'] = \date('Y-m-d H:i:s', \strtotime((string)$timeField));
			}

		$latitude = $this->getLatitude($exif);

		if (null === $latitude)
			{
			return $returnValue;
			}

		$longitude = $this->getLongitude($exif);

		if (null === $longitude)
			{
			return $returnValue;
			}
		$returnValue['latitude'] = $latitude;
		$returnValue['longitude'] = $longitude;

		return $returnValue;
		}

	/**
	 * is image type supported
	 *
	 * @param string $filepath to image
	 */
	public function isSupportedImageType(string $filepath) : bool
		{
		return \in_array($this->getImageType($filepath), [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP, ]);
		}

	/**
	 * 	 * Open image depending on type
	 * 	 *
	 *
	 * @param string $filepath to file
	 * @param int $type of image to open
	 *
	 * @return ?\GdImage resource to open image or false
	 */
	public function openImageFromAny(string $filepath, int $type) : ?\GdImage
		{
		$img = null;

		$img = match ($type) {
	  1 => @\imagecreatefromgif($filepath),
	  2 => @\imagecreatefromjpeg($filepath),
	  3 => @\imagecreatefrompng($filepath),
	  6 => @\imagecreatefrombmp($filepath),
	  default => $img,
  };

		return $img;
		}

	/**
	 * 	 * Default processFile that simply scales image to the right
	 * 	 * resolution for the web.
	 * 	 *
	 *
	 * @param string | int $file name to be scaled
	 *
	 */
	public function processFile(string | int $file) : string
		{
		// if not supported format, just return, we are done
		if (! $this->isSupportedImageType($file))
			{
			return '';
			}

		try
			{
			$toFile = $file . '.tinify';
			\App\Tools\File::unlink($toFile);
			$source = \Tinify\Source::fromFile($file);
			$source->toFile($toFile);
			\App\Tools\File::unlink($file);
			\rename($toFile, $file);
			}
		catch (\Throwable $e)
			{
			$this->error = $e->getMessage();
			// just return whatever was uploaded
			}

		return '';
		}

	/**
	 * Resize image to maximum width and height.  Will maximize the
	 * image while keeping the correct proportions.
	 *
	 * @param string $file name to be scaled
	 * @param int $width to resize to
	 * @param int $height to resize to
	 *
	 */
	public function resizeTo(string $file, int $width, int $height) : void
		{
		$toFile = $file . '.tinify';
		\App\Tools\File::unlink($toFile);
		$result = \getimagesize($file);
		$imageWidth = $result[0];
		$imageHeight = $result[1];

		// if both smaller, or not supported format, just return, we are done
		if (! $this->isSupportedImageType($file) || ($imageWidth <= $width && $imageHeight <= $height))
			{
			return;
			}

		$widthScaling = $this->computeScaling($imageWidth, $width);
		$heightScaling = $this->computeScaling($imageHeight, $height);

		try
			{
			$source = \Tinify\Source::fromFile($file);

			if ($widthScaling < $heightScaling)
				{
				$resized = $source->resize(['method' => 'scale', 'width' => $width]);
				}
			else
				{
				$resized = $source->resize(['method' => 'scale', 'height' => $height]);
				}
			$resized->toFile($toFile);
			}
		catch (\Throwable $e)
			{
			$this->error = $e->getMessage();
			}

		\App\Tools\File::unlink($file);
		\rename($toFile, $file);
		}

	/**
	 * Resize image to maximum height
	 *
	 * @param string $file name to be scaled
	 * @param int $height to resize to
	 *
	 */
	public function resizeToHeight(string $file, int $height) : void
		{
		$toFile = $file . '.tinify';
		\App\Tools\File::unlink($toFile);

		// if not supported format, just return, we are done
		if (! $this->isSupportedImageType($file))
			{
			return;
			}

		try
			{
			$source = \Tinify\Source::fromFile($file);
			$resized = $source->resize(['method' => 'scale', 'height' => $height]);
			$resized->toFile($toFile);
			}
		catch (\Throwable $e)
			{
			$this->error = $e->getMessage();
			}

		\App\Tools\File::unlink($file);
		\rename($toFile, $file);
		}

	/**
	 * Resize image to maximum width
	 *
	 * @param string $file name to be scaled
	 * @param int $width to resize to
	 *
	 */
	public function resizeToWidth(string $file, int $width) : void
		{
		$toFile = $file . '.tinify';
		\App\Tools\File::unlink($toFile);

		// if not supported format, just return, we are done
		if (! $this->isSupportedImageType($file))
			{
			return;
			}

		try
			{
			$source = \Tinify\Source::fromFile($file);
			$resized = $source->resize(['method' => 'scale', 'width' => $width]);
			$resized->toFile($toFile);
			}
		catch (\Throwable $e)
			{
			$this->error = $e->getMessage();
			}

		\App\Tools\File::unlink($file);
		\rename($toFile, $file);
		}

	/**
	 * Rotate image specified number of degrees.  Note that 90 will
	 * rotate to the left and 270 will rotate to the right
	 *
	 * @param string $file name including full path
	 * @param float $degrees to rotate (0-360)
	 *
	 */
	public function rotate(string $file, float $degrees) : void
		{
		$type = $this->getImageType($file);
		$img = $this->openImageFromAny($file, $type);

		if ($img)
			{
			$img = \imagerotate($img, $degrees, 0);
			$this->saveImageFromAny($file, $img, $type);
			}
		}

	/**
	 * Rotate image to left
	 *
	 * @param string $file name including full path
	 *
	 */
	public function rotateLeft(string $file) : void
		{
		$this->rotate($file, 90.0);
		}

	/**
	 * Rotate image to right
	 *
	 * @param string $file name including full path
	 *
	 */
	public function rotateRight(string $file) : void
		{
		$this->rotate($file, 270.0);
		}

	/**
	 * 	 * Save image depending on type and close resource
	 * 	 *
	 *
	 * @param string $filepath to file
	 * @param \GdImage $img to save
	 * @param int $type of file (jpg, png)
	 *
	 * @return bool true on success
	 */
	public function saveImageFromAny(string $filepath, \GdImage $img, int $type) : bool
		{
		$returnValue = false;

		switch ($type)
			{
			case 1:
				$returnValue = @\imagegif($img, $filepath);

				break;

			case 2:
				$returnValue = @\imagejpeg($img, $filepath, 94);

				break;

			case 3:
				$returnValue = @\imagepng($img, $filepath, 0);

				break;

			case 6:
				$returnValue = @\imagewbmp($img, $filepath);

				break;
			}
		@\imagedestroy($img);

		return $returnValue;
		}

	/**
	 * Compute scaling and return compresssion if we need to reduce
	 *
	 * @param int $imageSize current image measurement
	 * @param int $size we want
	 *
	 * @return float scaled percent
	 */
	private function computeScaling(int $imageSize, int $size) : float
		{
		return $size / $imageSize;
		}

	private function getLatitude(array $exif) : ?float
		{
		if (isset($exif['GPSLatitude'], $exif['GPSLatitudeRef']))
			{
			return $this->gps($exif['GPSLatitude'], $exif['GPSLatitudeRef']);
			}

		return null;
		}

	private function getLongitude(array $exif) : ?float
		{
		if (isset($exif['GPSLongitude'], $exif['GPSLongitudeRef']))
			{
			return $this->gps($exif['GPSLongitude'], $exif['GPSLongitudeRef']);
			}

		return null;
		}

	private function gps(array $coordinate, string $hemisphere) : float
		{
		for ($i = 0; $i < 3; $i++)
			{
			$part = \explode('/', (string)$coordinate[$i]);

			if (1 == \count($part))
				{
				$coordinate[$i] = $part[0];
				}
			elseif (2 == \count($part))
				{
				$coordinate[$i] = (float)($part[0]) / (float)($part[1]);
				}
			else
				{
				$coordinate[$i] = 0;
				}
			}
		[$degrees, $minutes, $seconds] = $coordinate;
		$sign = ('W' == $hemisphere || 'S' == $hemisphere) ? -1 : 1;

		return $sign * ($degrees + $minutes / 60 + $seconds / 3600);
		}
	}
