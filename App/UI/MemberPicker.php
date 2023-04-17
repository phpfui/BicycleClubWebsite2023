<?php

namespace App\UI;

class MemberPicker
	{
	private readonly string $name;

	public function __construct(private readonly \PHPFUI\Page $page, private readonly \App\Model\MemberPickerBase $model, private string $fieldName = '', array $initialMember = [])
		{
		$this->model->setMember($initialMember);
		$this->name = $model->getName();
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

			foreach ($this->model->findByName($names) as $row)
				{
				$returnValue[] = ['value' => $this->getText($row),
					'data' => $row['memberId'], ];
				}
			}
		else
			{
			$this->model->save($parameters['AutoComplete']);
			}

		return ['suggestions' => $returnValue];
		}

	public function getEditControl() : \PHPFUI\Input\AutoComplete
		{
		if (! $this->fieldName)
			{
			$this->fieldName = \str_replace(' ', '', $this->name);
			}
		$member = $this->model->getMember($this->name, false);
		$value = $this->getText($member);
		$control = new \PHPFUI\Input\AutoComplete($this->page, $this->callback(...), 'text', $this->fieldName, $this->name, $value);
		$hidden = $control->getHiddenField();
		$hidden->setValue($this->model->getMember('', true)['memberId'] ?? 0);
		$control->setNoFreeForm();

		return $control;
		}

	public function getText(array $member) : string
		{
		if (! isset($member['firstName']))
			{
			$member['firstName'] = '';
			}

		if (! isset($member['lastName']))
			{
			$member['lastName'] = '';
			}

		return $member['firstName'] . ' ' . $member['lastName'];
		}
	}
