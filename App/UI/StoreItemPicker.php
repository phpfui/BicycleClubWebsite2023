<?php

namespace App\UI;

class StoreItemPicker
	{
	private readonly \App\Table\StoreItem $storeItemTable;

	public function __construct(private readonly \PHPFUI\Page $page, private readonly string $fieldName, private readonly string $label = '', private readonly \App\Record\StoreItem $initial = new \App\Record\StoreItem())
		{
		$this->storeItemTable = new \App\Table\StoreItem();
		}

	/**
	 * @param array<string,string> $parameters
	 *
	 * @return (mixed|string)[][][]
	 *
	 * @psalm-return array{suggestions: list<array{value: string, data: mixed}>}
	 */
	public function callback(array $parameters) : array
		{
		$returnValue = [];

		if (empty($parameters['save']))
			{
			$names = \explode(' ', (string)$parameters['AutoComplete']);
			$condition = new \PHPFUI\ORM\Condition('type', 0);

			foreach ($names as $name)
				{
				$orCondition = new \PHPFUI\ORM\Condition('title', "%{$name}%", new \PHPFUI\ORM\Operator\Like());
				$orCondition->or('description', "%{$name}%", new \PHPFUI\ORM\Operator\Like());
				$condition->and($orCondition);
				}
			$this->storeItemTable->setWhere($condition);

			foreach ($this->storeItemTable->getRecordCursor() as $storeItem)
				{
				$returnValue[] = ['value' => $this->getText($storeItem), 'data' => $storeItem->storeItemId];
				}
			}

		return ['suggestions' => $returnValue];
		}

	public function getEditControl() : \PHPFUI\Input\AutoComplete
		{
		$value = $this->getText($this->initial);
		$control = new \PHPFUI\Input\AutoComplete($this->page, $this->callback(...), 'text', $this->fieldName, $this->label, $value);
		$hidden = $control->getHiddenField();
		$hidden->setValue((string)($this->initial->storeItemId ?? 0));
		$control->setNoFreeForm();

		return $control;
		}

	private function getText(\App\Record\StoreItem $storeItem) : string
		{
		if ($storeItem->empty())
			{
			return '';
			}

		return $storeItem->title;
		}
	}
