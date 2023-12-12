<?php

namespace App\UI;

class WebFont extends \PHPFUI\Input\Select
	{
	public function __construct(string $name = 'font', string $label = 'Font')
		{
		parent::__construct($name, $label);

		$this->addPrivateOption('Arial (sans-serif)');
		$this->addPrivateOption('Verdana (sans-serif)');
		$this->addPrivateOption('Tahoma (sans-serif)');
		$this->addPrivateOption('Trebuchet MS (sans-serif)');
		$this->addPrivateOption('Times New Roman (serif)');
		$this->addPrivateOption('Georgia (serif)');
		$this->addPrivateOption('Garamond (serif)');
		$this->addPrivateOption('Courier New (monospace)');
		$this->addPrivateOption('Brush Script MT (cursive)');
		}

	private function addPrivateOption(string $name) : void
		{
		$font = \substr($name, 0, \strpos($name, ' ('));
		$this->addOption($name, $font);
		}
	}
