<?php

namespace App\UI;

class Font extends \PHPFUI\Input\Select
	{
	public function __construct($name = 'font', $label = 'Font')
		{
		parent::__construct($name, $label);

		$fonts = [];

		foreach (\glob(PROJECT_ROOT . '/font/*.php') as $file)
			{
			include $file;
			$fonts[\pathinfo((string)$file, PATHINFO_FILENAME)] = $name;
			}

		\asort($fonts);

		foreach ($fonts as $file => $font)
			{
			$this->addOption($font, $file);
			}
		}
	}
