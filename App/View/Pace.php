<?php

namespace App\View;

class Pace
	{
	private readonly \App\Table\Pace $paceTable;

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->paceTable = new \App\Table\Pace();
		}

	public function edit(\App\Record\Category $category) : \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit();
		$form = new \PHPFUI\Form($this->page, $submit);
		$add = false;

		if (empty($category->category))
			{
			$category->category = 'All';
			}
		$form->add("<h3>Edit Paces for Category {$category->category}</h3>");
		$form->add(new \PHPFUI\Input\Hidden('categoryId', (string)$category->categoryId));

		if ($form->isMyCallback())
			{
			$i = 0;

			foreach ($_POST['ordering'] ?? [] as $key => $value)
				{
				$_POST['ordering'][$key] = ++$i;
				}
			$this->paceTable->updateFromTable($_POST);
			$this->paceTable->reorderPace($_POST['categoryId']);
			$this->page->setResponse('Saved');
			}
		elseif (isset($_GET['action']) && \App\Model\Session::checkCSRF())
			{
			switch ($_GET['action'])
				{
				case 'delete':
					$pace = new \App\Record\Pace((int)$_POST['paceId']);
					$pace->delete();

					break;

				}
			$this->page->redirect();
			}
		elseif (isset($_POST['action']) && \App\Model\Session::checkCSRF())
			{
			if ('Add' == $_POST['action'])
				{
				$pace = new \App\Record\Pace();
				$pace->setFrom($_POST);
				$pace->insert();
				}
			$this->page->redirect();
			}
		else
			{
			$table = new \PHPFUI\OrderableTable($this->page);
			$table->setRecordId($pk = \array_key_first($this->paceTable->getPrimaryKeys()));
			$paces = $this->paceTable->getPaceOrder($category->categoryId);
			$url = '?csrf=' . \App\Model\Session::csrf();
			$count = $total = \count($paces);

			$add = new \PHPFUI\Button('Add Pace');
			$add->addClass('info');
			$form->saveOnClick($add);
			$this->addPaceModal($add, $category);
			$headers = [
				'pace' => 'Pace',
				'minSpeed' => 'Min Speed',
				'maxSpeed' => 'Max Speed',
				'maxRiders' => 'Max Riders',
			];

			if ($total > 1)
				{
				$headers['delete'] = 'Del';
				}
			$table->setHeaders($headers);
			$first = true;

			foreach ($paces as $pace)
				{
				$row = $pace->toArray();
				$id = $row[$pk];
				$key = new \PHPFUI\Input\Hidden("{$pk}[{$id}]", $id);
				$ordering = new \PHPFUI\Input\Hidden("ordering[{$id}]", $id);
				$rowName = new \PHPFUI\Input\Text("pace[{$id}]", '', $row['pace']);
				$rowName->addAttribute('maxlength', (string)5);
				$row['pace'] = $rowName . $key . $ordering;
				$minSpeed = new \PHPFUI\Input\Number("minSpeed[{$id}]", '', $row['minSpeed']);
				$minSpeed->addAttribute('min', (string)0)->addAttribute('max', (string)25)->addAttribute('step', (string)0.1);
				$row['minSpeed'] = $minSpeed;
				$maxSpeed = new \PHPFUI\Input\Number("maxSpeed[{$id}]", '', $row['maxSpeed']);
				$maxSpeed->addAttribute('min', (string)0)->addAttribute('max', (string)25)->addAttribute('step', (string)0.1);
				$row['maxSpeed'] = $maxSpeed;

				$maxRiders = new \PHPFUI\Input\Number("maxRiders[{$id}]", '', $row['maxRiders']);
				$maxRiders->addAttribute('min', (string)-1)->addAttribute('max', (string)99)->addAttribute('step', (string)1);
				$row['maxRiders'] = $maxRiders;

				if ($total > 1)
					{
					$row['delete'] = new \PHPFUI\FAIcon('far', 'trash-alt', '/Leaders/deletePace/' . $id);
					}
				$table->addRow($row);
				}
			$form->add($table);
			$buttonGroup = new \App\UI\CancelButtonGroup();
			$buttonGroup->addButton($submit);

			// @phpstan-ignore-next-line
			if ($add && $category->loaded())
				{
				$buttonGroup->addButton($add);
				}
			$editCategories = new \PHPFUI\Button('Edit Categories', '/Leaders/categories');
			$editCategories->addClass('secondary');
			$buttonGroup->addButton($editCategories);
			$form->add($buttonGroup);
			}

		return $form;
		}

	public function outputMovePace(\PHPFUI\Button $backButton) : string | \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit('Move Pace');
		$submit->addClass('alert');
		$form = new \PHPFUI\Form($this->page, $submit);

		if ($form->isMyCallback())
			{
			$this->paceTable->movePace($_POST['pace'], $_POST['category']);
			$this->page->setResponse('Moved');

			return '';
			}
		$pacePicker = new \App\View\PacePicker('pace', 'Category', 'Choose a Pace to move');
		$form->add($pacePicker);
		$categoryView = new \App\View\Categories($this->page, $backButton);
		$catPicker = $categoryView->getCategoryPicker('category', 'Select a Category to move it into');
		$form->add($catPicker);
		$buttonGroup = new \App\UI\CancelButtonGroup();
		$buttonGroup->addButton($submit);
		$buttonGroup->addButton($backButton);
		$form->add($buttonGroup);

		return $form;
		}

	private function addPaceModal(\PHPFUI\HTML5Element $modalLink, \App\Record\Category $category) : void
		{
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$modal->addClass('small');
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$form->add(new \PHPFUI\Input\Hidden('categoryId', (string)$category->categoryId));
		$fieldSet = new \PHPFUI\FieldSet('New Pace Information');
		$paceName = new \PHPFUI\Input\Text('pace', 'Pace');
		$paceName->addAttribute('maxlength', (string)5);
		$paceName->addAttribute('style', 'width:5em;');
		$minSpeed = new \PHPFUI\Input\Number('minSpeed', 'Min Speed');
		$minSpeed->addAttribute('min', (string)0)->addAttribute('max', (string)25)->addAttribute('step', (string)0.1)->addAttribute('style', 'width:5em;');
		$maxSpeed = new \PHPFUI\Input\Number('maxSpeed', 'Max Speed');
		$maxSpeed->addAttribute('min', (string)0)->addAttribute('max', (string)25)->addAttribute('step', (string)0.1)->addAttribute('style', 'width:5em;');
		$maxRiders = new \PHPFUI\Input\Number('maxRiders', 'Max Riders');
		$maxRiders->addAttribute('min', (string)-1)->addAttribute('max', (string)99)->addAttribute('step', (string)1)->addAttribute('style', 'width:5em;');

		$fieldSet->add(new \PHPFUI\MultiColumn($paceName, $minSpeed, $maxSpeed, $maxRiders));
		$form->add($fieldSet);
		$submit = new \PHPFUI\Submit('Add', 'action');
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);
		}
	}
