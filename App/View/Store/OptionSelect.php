<?php

namespace App\View\Store;

class OptionSelect extends \PHPFUI\Input\Select
	{
	public function __construct(string $name, string $label = '', ?int $value = null)
		{
		parent::__construct($name, $label);

		$storeOptionTable = new \App\Table\StoreOption();
		$storeOptionTable->addOrderBy('optionName');
		$storeOptionTable->addOrderBy('optionValues');

		foreach ($storeOptionTable->getRecordCursor() as $option)
			{
			$this->addOption($option->optionName . ' - ' . $option->optionValues, $option->storeOptionId, $option->storeOptionId == $value);
			}
		}
	}
