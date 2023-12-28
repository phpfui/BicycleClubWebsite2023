<?php

namespace App\UI;

class GeoLocate extends \PHPFUI\Input\Select
	{
	public function __construct(string $name, int $value)
		{
		parent::__construct($name, 'Default to sending your geo location when sending ride texts or comments via the web site. Disable setting will not include the location option.');
		$this->addOption('Default Off', '0', 0 == $value);
		$this->addOption('Default On', '1', 1 == $value);
		$this->addOption('Disabled', '2', 2 == $value);
		}
	}
