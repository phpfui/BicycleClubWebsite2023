<?php

namespace App\View\Store;

class StoreItemOption extends \PHPFUI\Input\Select
	{
	public function __construct(\App\Record\StoreOption $storeOption, string $value = '')
		{
		parent::__construct('storeOption[' . $storeOption->optionName . ']', $storeOption->optionName);

		foreach ($storeOption->getOptions() as $option)
			{
			$this->addOption($option, $option, $option == $value);
			}
		}
	}
