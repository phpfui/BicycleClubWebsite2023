<?php

namespace App\Model;

class BannerFiles extends \App\Model\TinifyImage
	{
	final public const int MIN_WIDTH = 1200;

	final public const int PROPORTION = 6;

	public function __construct()
		{
		parent::__construct('images/banners');
		}

	public static function getBanner(\App\Record\Banner $banner) : string
		{
		if ($banner->html)
			{
			$style = new \PHPFUI\HTML5Element('style');
			$style->add($banner->css);

			return \htmlspecialchars_decode($banner->html) . $style;
			}

		return "<img alt='{$banner->description}' src='/images/banners/{$banner->bannerId}{$banner->fileNameExt}'>";
		}

	public function processFile(string | int $file) : string
		{
		$result = \getimagesize($file);
		$imageWidth = (int)$result[0];
		$imageHeight = (int)$result[1];

		if ($imageWidth < \App\Model\BannerFiles::MIN_WIDTH)
			{
			$width = \App\Model\BannerFiles::MIN_WIDTH;

			return "Banner images must be at least {$width} pixels wide not {$imageWidth}";
			}

		$actual = $imageWidth / $imageHeight;
		$diff = $actual - (float)\App\Model\BannerFiles::PROPORTION;
		$actual = \number_format($actual, 2);
		$proportion = \App\Model\BannerFiles::PROPORTION;

		if ($diff > 0.05 || $diff < -0.05)
			{
			return "Banner proportions must be {$proportion}:1, not {$actual}:1";
			}

		$this->resizeToWidth($file, 1200);

		return '';
		}
	}
