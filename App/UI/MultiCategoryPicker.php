<?php

namespace App\UI;

class MultiCategoryPicker extends \PHPFUI\Input\MultiSelect
	{
	/**
	 * @param array<int> $selected
	 */
	public function __construct(string $fieldName = 'categories', string $label = '', array $selected = [])
		{
		parent::__construct($fieldName, $label);
		$this->selectAll();
		$this->setColumns(2);
		$this->addOption('All', '0', \in_array(0, $selected));
		$categoryTable = new \App\Table\Category();

		foreach ($categoryTable->getRecordCursor() as $category)
			{
			$this->addOption($category->label(), $category->categoryId, \in_array($category->categoryId, $selected));
			}
		}
	}
