<?php

namespace App\View;

class Categories
	{
	private readonly \App\Table\Category $categoryTable;

	public function __construct(private readonly \PHPFUI\Page $page, private readonly ?\PHPFUI\Button $backButton = null)
		{
		$this->categoryTable = new \App\Table\Category();
		}

	public function edit() : \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit();
		$form = new \PHPFUI\Form($this->page, $submit);

		if ($form->isMyCallback())
			{
			$i = 0;
			$data = $_POST;
			$memberDefault = $data['memberDefault'] ?? 1;
			$data['memberDefault'] = [];

			foreach ($data['ordering'] ?? [] as $key => $value)
				{
				$data['memberDefault'][$key] = $key == $memberDefault;
				$data['ordering'][$key] = ++$i;
				}

			$this->categoryTable->updateFromTable($data);
			$this->page->setResponse('Saved');
			}
		elseif (isset($_POST['action']) && \App\Model\Session::checkCSRF())
			{
			switch ($_POST['action'])
				{
				case 'Add':

					$category = new \App\Record\Category();
					$category->setFrom($_POST);
					$category->insert();

					break;

				}
			$this->page->redirect();
			}
		else
			{
			$table = new \PHPFUI\OrderableTable($this->page);
			$table->setRecordId($pk = $this->categoryTable->getPrimaryKeys()[0]);
			$textarea = new \PHPFUI\Input\TextArea('descriptionText', 'Category Description');
			$popupEditor = new \PHPFUI\PopupInput($this->page, $textarea);
			'?csrf=' . \App\Model\Session::csrf();

			$count = $total = \count($this->categoryTable);
			$headers = ['category' => 'Cat',
				'minSpeed' => 'Min <wbr>Speed',
				'maxSpeed' => 'Max <wbr>Speed',
				'memberDefault' => 'Default',
				'description' => 'Desc',
				'pace' => 'Pace',
			];

			if ($total > 1)
				{
				$headers['delete'] = 'Del';
				}
			$table->setHeaders($headers);

			$add = null;

			if ($count < 10)
				{
				$add = new \PHPFUI\Button('Add Category');
				$add->addClass('info');
				$form->saveOnClick($add);
				$this->addCategoryModal($add);
				}

			foreach ($this->categoryTable->getRecordCursor() as $category)
				{
				$id = $category->categoryId;
				$row = $category->toArray();
				$key = new \PHPFUI\Input\Hidden("categoryId[{$id}]", $id);
				$ordering = new \PHPFUI\Input\Hidden("ordering[{$id}]", $id);
				$cat = new \PHPFUI\Input\Text("category[{$id}]", '', $category->category);
				$cat->addAttribute('maxlength', (string)20);
				$row['category'] = $cat . $key . $ordering;
				$minspeed = new \PHPFUI\Input\Number("minSpeed[{$id}]", '', $category->minSpeed);
				$minspeed->addAttribute('min', (string)0)->addAttribute('max', (string)25)->addAttribute('step', (string)0.1)->addAttribute('style', 'width:5em;');
				$row['minSpeed'] = $minspeed;
				$maxspeed = new \PHPFUI\Input\Number("maxSpeed[{$id}]", '', $category->maxSpeed);
				$maxspeed->addAttribute('min', (string)0)->addAttribute('max', (string)25)->addAttribute('step', (string)0.1)->addAttribute('style', 'width:5em;');
				$row['maxSpeed'] = $maxspeed;
				$description = new \PHPFUI\Input\Hidden("description[{$id}]", $category->description);
				$icon = new \PHPFUI\FAIcon('far', 'edit', '#');
				$icon->setAttribute('onclick', $popupEditor->getLoadJS($description));
				$row['description'] = $icon . $description;
				$row['delete'] = new \PHPFUI\FAIcon('far', 'trash-alt', "/Leaders/deleteCategory/{$id}");
				$row['pace'] = new \PHPFUI\FAIcon('fas', 'asterisk', "/Leaders/pace/{$id}");
				$radio = new \PHPFUI\Input\Radio('memberDefault', '', $id);
				$radio->setChecked($category->memberDefault);
				$row['memberDefault'] = $radio;
				$table->addRow($row);
				}
			$form->add($table);
			$buttonGroup = new \PHPFUI\ButtonGroup();
			$buttonGroup->addButton($submit);

			if ($add)
				{
				$buttonGroup->addButton($add);
				}

			if ($this->backButton)
				{
				$buttonGroup->addButton($this->backButton);
				}
			$form->add($buttonGroup);
			}

		return $form;
		}

	public static function getCategoryLabel(\App\Record\Category $category) : string
		{
		$label = $category->category ?? 'All';

		if ((int)($category->minSpeed) && (int)($category->maxSpeed))
			{
			$label .= " ({$category->minSpeed}-{$category->maxSpeed})";
			}
		elseif ($category->minSpeed)
			{
			$label .= " ({$category->minSpeed}+)";
			}
		elseif ($category->maxSpeed)
			{
			$label .= " (<{$category->maxSpeed})";
			}

		return $label;
		}

	public function getCategoryPicker(string $fieldName = 'category', string $label = '', int $selected = 0) : \PHPFUI\Input\Select
		{
		$select = new \PHPFUI\Input\Select($fieldName, $label);
		$select->addOption('All', (string)0, 0 == $selected);

		foreach ($this->categoryTable->getRecordCursor() as $category)
			{
			$select->addOption(static::getCategoryLabel($category), $category->categoryId, $category->categoryId == $selected);
			}

		return $select;
		}

	/**
	 * @param array<int> $selected
	 */
	public function getMultiCategoryPicker(string $fieldName = 'categories', string $label = '', array $selected = []) : \PHPFUI\Input\MultiSelect
		{
		$select = new \PHPFUI\Input\MultiSelect($fieldName, $label);
		$select->selectAll();
		$select->setColumns(2);
		$select->addOption('All', (string)0, \in_array(0, $selected));

		foreach ($this->categoryTable->getRecordCursor() as $category)
			{
			$select->addOption(static::getCategoryLabel($category), $category->categoryId, \in_array($category->categoryId, $selected));
			}

		return $select;
		}

	public static function getPaceLabel(\App\Record\Pace $pace) : string
		{
		$label = $pace->pace;

		if ((int)($pace->minSpeed) && (int)($pace->maxSpeed))
			{
			$label .= " ({$pace->minSpeed}-{$pace->maxSpeed})";
			}
		elseif ($pace->minSpeed)
			{
			$label .= " ({$pace->minSpeed}+)";
			}
		elseif ($pace->maxSpeed)
			{
			$label .= " (<{$pace->maxSpeed})";
			}

		return $label;
		}

	private function addCategoryModal(\PHPFUI\HTML5Element $modalLink) : void
		{
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$modal->addClass('large');
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$fieldSet = new \PHPFUI\FieldSet('New Category Information');
		$table = new \PHPFUI\Table();
		$table->setHeaders(['category' => 'Cat',
			'minSpeed' => 'Min <wbr>Speed',
			'maxSpeed' => 'Max <wbr>Speed', ]);
		$cat = new \PHPFUI\Input\Text('category');
		$cat->addAttribute('style', 'width:3em;');
		$category = ['category' => $cat];
		$minspeed = new \PHPFUI\Input\Number('minSpeed');
		$minspeed->addAttribute('min', (string)0)->addAttribute('max', (string)25)->addAttribute('step', (string)0.1)->addAttribute('style', 'width:5em;');
		$maxspeed = new \PHPFUI\Input\Number('maxSpeed');
		$maxspeed->addAttribute('min', (string)0)->addAttribute('max', (string)25)->addAttribute('step', (string)0.1)->addAttribute('style', 'width:5em;');
		$table->addRow($category);
		$fieldSet->add($table);
		$form->add($fieldSet);
		$submit = new \PHPFUI\Submit('Add', 'action');
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);
		}
	}
