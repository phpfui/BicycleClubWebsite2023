<?php

namespace App\UI;

class PhotoPicker
	{
	private readonly \App\Table\PhotoFolder $photoFolderTable;

	private readonly \App\Table\Photo $photoTable;

	public function __construct(private readonly \PHPFUI\Page $page, private readonly string $fieldName, private readonly string $label = '', private readonly \App\Record\Photo $initial = new \App\Record\Photo())
		{
		$this->photoTable = new \App\Table\Photo();
		$this->photoFolderTable = new \App\Table\PhotoFolder();
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
		$returnValue[] = ['value' => '', 'data' => 'No Photo Selected'];

		if (empty($parameters['save']))
			{
			$names = \explode(' ', (string)$parameters['AutoComplete']);
			$condition = new \PHPFUI\ORM\Condition();

			foreach ($names as $name)
				{
				$condition->or(new \PHPFUI\ORM\Condition('photo', "%{$name}%", new \PHPFUI\ORM\Operator\Like()));
				}
			$this->photoTable->setWhere($condition);

			foreach ($this->photoTable->getRecordCursor() as $photo)
				{
				$returnValue[] = ['value' => \str_replace(['&quot;', '"'], '', $photo->photo), 'data' => $photo->photoId];
				}

			$condition = new \PHPFUI\ORM\Condition();

			foreach ($names as $name)
				{
				$condition->or(new \PHPFUI\ORM\Condition('photoFolder', "%{$name}%", new \PHPFUI\ORM\Operator\Like()));
				}
			$this->photoFolderTable->setWhere($condition);

			foreach ($this->photoFolderTable->getRecordCursor() as $folder)
				{
				foreach ($folder->PhotoChildren as $photo)
					{
					$returnValue[] = ['value' => \str_replace(['&quot;', '"'], '', $photo->photo), 'data' => $photo->photoId];
					}
				}
			}

		return ['suggestions' => $returnValue];
		}

	public function getEditControl() : \PHPFUI\Input\AutoComplete
		{
		$value = $this->initial->photo;
		$control = new \PHPFUI\Input\AutoComplete($this->page, $this->callback(...), 'text', $this->fieldName, $this->label, $value);
		$hidden = $control->getHiddenField();
		$hidden->setValue((string)($this->initial->photoId ?? 0));
		$control->setNoFreeForm();

		return $control;
		}
	}
