<?php

namespace App\UI;

class CueSheetPicker
	{
	private readonly \App\Table\CueSheet $cueSheetTable;

	public function __construct(private readonly \PHPFUI\Page $page, private readonly string $fieldName, private readonly string $label = '', private readonly \App\Record\CueSheet $initial = new \App\Record\CueSheet())
		{
		$this->cueSheetTable = new \App\Table\CueSheet();
		}

	/**
	 * @param array<string,string> $parameters
	 *
	 * @return (mixed|string)[][][]
	 */
	public function callback(array $parameters) : array
		{
		$returnValue = [];

		if (empty($parameters['save']))
			{
			$names = \explode(' ', (string)$parameters['AutoComplete']);
			$condition = new \PHPFUI\ORM\Condition();

			foreach ($names as $name)
				{
				$condition->or(new \PHPFUI\ORM\Condition('name', "%{$name}%", new \PHPFUI\ORM\Operator\Like()));
				}
			$this->cueSheetTable->setWhere($condition);

			foreach ($this->cueSheetTable->getRecordCursor() as $cueSheet)
				{
				$returnValue[] = ['value' => $this->getText($cueSheet), 'data' => $cueSheet->cueSheetId];
				}
			}

		return ['suggestions' => $returnValue];
		}

	public function getEditControl() : \PHPFUI\Input\AutoComplete
		{
		$value = $this->getText($this->initial);
		$control = new \PHPFUI\Input\AutoComplete($this->page, $this->callback(...), 'text', $this->fieldName, $this->label, $value);
		$hidden = $control->getHiddenField();
		$hidden->setValue((string)($this->initial->cueSheetId ?? 0));
		$control->setNoFreeForm();

		return $control;
		}

	private function getText(\App\Record\CueSheet $cueSheet) : string
		{
		if ($cueSheet->empty())
			{
			return '';
			}

		return $cueSheet->name . ' - ' . \number_format($cueSheet->mileage, 1) . ' Miles';
		}
	}
