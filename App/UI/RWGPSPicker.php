<?php

namespace App\UI;

class RWGPSPicker
	{
	private readonly \App\Table\RWGPS $RWGPSTable;

	public function __construct(private readonly \PHPFUI\Page $page, private readonly string $fieldName, private readonly string $label = '', private readonly \App\Record\RWGPS $initial = new \App\Record\RWGPS())
		{
		$this->RWGPSTable = new \App\Table\RWGPS();
		}

	/**
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
			$condition = new \PHPFUI\ORM\Condition();

			foreach ($names as $name)
				{
				$condition->or(new \PHPFUI\ORM\Condition('title', "%{$name}%", new \PHPFUI\ORM\Operator\Like()));
				$condition->or(new \PHPFUI\ORM\Condition('csv', "%{$name}%", new \PHPFUI\ORM\Operator\Like()));
				$condition->or(new \PHPFUI\ORM\Condition('town', "%{$name}%", new \PHPFUI\ORM\Operator\Like()));
				}
			$this->RWGPSTable->setWhere($condition);

			foreach ($this->RWGPSTable->getRecordCursor() as $RWGPS)
				{
				$returnValue[] = ['value' => $this->getText($RWGPS), 'data' => $RWGPS->RWGPSId];
				}
			}

		return ['suggestions' => $returnValue];
		}

	public function getEditControl() : \PHPFUI\Input\AutoComplete
		{
		$value = $this->getText($this->initial);
		$control = new \PHPFUI\Input\AutoComplete($this->page, $this->callback(...), 'text', $this->fieldName, $this->label, $value);
		$hidden = $control->getHiddenField();
		$hidden->setValue((string)($this->initial->RWGPSId ?? 0));
		$control->setNoFreeForm();

		return $control;
		}

	private function getText(\App\Record\RWGPS $RWGPS) : string
		{
		if ($RWGPS->empty())
			{
			return '';
			}

		return ($RWGPS->title ?? 'No Name') . ' - ' . \number_format($RWGPS->miles ?? 0, 1) . ' Miles';
		}
	}
