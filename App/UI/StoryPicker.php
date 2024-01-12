<?php

namespace App\UI;

class StoryPicker
	{
	private readonly \App\Table\Story $storyTable;

	public function __construct(private readonly \PHPFUI\Page $page, private readonly string $fieldName, private readonly string $label = '', private readonly \App\Record\Story $initial = new \App\Record\Story())
		{
		$this->storyTable = new \App\Table\Story();
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
			$condition = new \PHPFUI\ORM\Condition();

			foreach ($names as $name)
				{
				$condition->or(new \PHPFUI\ORM\Condition('headline', "%{$name}%", new \PHPFUI\ORM\Operator\Like()));
				$condition->or(new \PHPFUI\ORM\Condition('author', "%{$name}%", new \PHPFUI\ORM\Operator\Like()));
				$condition->or(new \PHPFUI\ORM\Condition('lastEdited', "%{$name}%", new \PHPFUI\ORM\Operator\Like()));
				}
			$this->storyTable->setWhere($condition);

			foreach ($this->storyTable->getRecordCursor() as $story)
				{
				$returnValue[] = ['value' => $this->getText($story), 'data' => $story->storyId];
				}
			}

		return ['suggestions' => $returnValue];
		}

	public function getEditControl() : \PHPFUI\Input\AutoComplete
		{
		$value = $this->getText($this->initial);
		$control = new \PHPFUI\Input\AutoComplete($this->page, $this->callback(...), 'text', $this->fieldName, $this->label, $value);
		$hidden = $control->getHiddenField();
		$hidden->setValue((string)($this->initial->storyId ?? ''));
		$control->setNoFreeForm();

		return $control;
		}

	private function getText(\App\Record\Story $story) : string
		{
		if ($story->empty())
			{
			return '';
			}

		return $story->headline . ' - ' . $story->author . ' - ' . $story->lastEdited;
		}
	}
