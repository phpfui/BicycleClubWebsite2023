<?php

namespace App\Model;

class GABannerFile extends \App\Model\TinifyImage
	{
	public function __construct()
		{
		parent::__construct('images/GA');
		}

	/**
	 * @param array<string,mixed> $banner
	 */
	public static function getBanner(array $banner) : string
		{
		return "<img alt='{$banner['description']}' src='/images/GA/{$banner['GABannerName']}'>";
		}
	}
