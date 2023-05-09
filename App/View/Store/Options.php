<?php

namespace App\View\Store;

class Options
	{
	public function __construct(private readonly \App\View\Page $page)
		{
		$this->processRequest();
		}

	public function checkForAdd() : bool
		{
		if (isset($_POST['submit']) && \App\Model\Session::checkCSRF())
			{
			$storeOptionId = (int)($_POST['storeOptionId'] ?? 0);

			switch ($_POST['submit'])
				{
				case 'Add Option':
					$storeOption = new \App\Record\StoreOption($storeOptionId);
					$values = $storeOption->getOptions();
					$values[] = $_POST['optionValue'] ?? '';
					$storeOption->setOptions($values);
					$storeOption->update();
					$this->page->redirect('/Store/Options/edit/' . $storeOptionId);
					$this->page->done();

					return true;

				case 'Add':
					$storeOption = new \App\Record\StoreOption();
					$storeOption->setFrom($_POST);
					$storeOptionId = $storeOption->insert();
					$this->page->redirect('/Store/Options/edit/' . $storeOptionId);
					$this->page->done();

					return true;
				}
			}

		return false;
		}

	public function edit(\App\Record\StoreOption $storeOption = new \App\Record\StoreOption()) : string | \PHPFUI\Form
		{
		if ($this->checkForAdd())
			{
			return '';
			}

		if ($storeOption->storeOptionId)
			{
			$submit = new \PHPFUI\Submit();
			$form = new \PHPFUI\Form($this->page, $submit);

			$addValue = new \PHPFUI\Button('Add Option Value');
			$reveal = new \PHPFUI\Reveal($this->page, $addValue);
			$revealForm = new \PHPFUI\Form($this->page);
			$revealForm->setAreYouSure(false);
			$fieldSet = new \PHPFUI\FieldSet('Value to add');
			$fieldSet->add(new \PHPFUI\Input\Hidden('storeOptionId', (string)$storeOption->storeOptionId));
			$fieldSet->add(new \PHPFUI\Input\Text('optionValue'));
			$fieldSet->add($reveal->getButtonAndCancel(new \PHPFUI\Submit('Add Option')));
			$revealForm->add($fieldSet);
			$reveal->add($revealForm);
			}
		else
			{
			$addValue = false;
			$submit = new \PHPFUI\Submit('Add');
			$form = new \PHPFUI\Form($this->page);
			}

		if ($form->isMyCallback())
			{
			unset($_POST['storeOptionId']);
			$storeOption->setFrom($_POST);
			$storeOption->setOptions($_POST['values'] ?? []);
			$storeOption->update();
			$this->page->setResponse('Saved');
			$this->page->done();

			return $form;
			}
		$form->add(new \PHPFUI\Input\Hidden('storeOptionId', (string)$storeOption->storeOptionId));
		$fieldSet = new \PHPFUI\FieldSet('Store Option Details');
		$optionNameField = new \PHPFUI\Input\Text('optionName', 'Option Name', $storeOption->optionName);
		$optionNameField->setAttribute('maxlength', '100');
		$optionNameField->setToolTip('This is the name of the option that the user will see.');
		$optionNameField->setRequired();
		$fieldSet->add($optionNameField);
		$form->add($fieldSet);

		if ($addValue)
			{
			$fieldSet = new \PHPFUI\FieldSet('Option Values');

			$table = new \PHPFUI\OrderableTable($this->page);
			$rowId = 'rowId';
			$table->setRecordId($rowId);
			$table->addHeader('name', 'Name');
			$table->addHeader('delete', 'Del');

			foreach ($storeOption->getOptions() as $index => $optionValue)
				{
				$row = [$rowId => $index];
				$row['name'] = new \PHPFUI\Input\Text('values[]', '', $optionValue);
				$trash = new \PHPFUI\FAIcon('far', 'trash-alt', '#');
				$trash->addAttribute('onclick', '$("#rowId-' . $index . '").css("background-color","red").hide("fast").remove();');
				$row['delete'] = $trash;
				$table->addRow($row);
				}
			$fieldSet->add($table);

			$fieldSet->add($addValue);
			$form->saveOnClick($addValue);
			$form->add($fieldSet);
			}

		$buttonGroup = new \PHPFUI\ButtonGroup();
		$buttonGroup->addButton($submit);
		$cancel = new \PHPFUI\Button('Cancel', '/Store/Options/list');
		$cancel->addClass('hollow')->addClass('alert');
		$buttonGroup->addButton($cancel);
		$form->add($buttonGroup);

		return $form;
		}

	public function show() : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$storeOptionTable = new \App\Table\StoreOption();

		if (! \count($storeOptionTable))
			{
			$container->add(new \PHPFUI\SubHeader('No Store Options found'));

			return $container;
			}
		$storeOptionTable->setLimit(10);
		$view = new \App\UI\ContinuousScrollTable($this->page, $storeOptionTable);

		$headers = ['optionName' => 'Name', 'optionValues' => 'Values'];

		$deleter = new \App\Model\DeleteRecord($this->page, $view, $storeOptionTable, 'Are you sure you want to permanently delete this store option?');
		$view->addCustomColumn('del', $deleter->columnCallback(...));
		$view->addCustomColumn('optionName', static fn (array $storeOption) => new \PHPFUI\Link('/Store/Options/edit/' . $storeOption['storeOptionId'], $storeOption['optionName'], false));
		$view->setSearchColumns($headers)->setSortableColumns(\array_keys($headers))->setHeaders(\array_merge($headers, ['del']));
		$container->add($view);

		return $container;
		}

	private function processRequest() : void
		{
		if (\App\Model\Session::checkCSRF())
			{
			if (isset($_POST['action']))
				{
				switch ($_POST['action'])
					{
					case 'deleteStoreOption':
						$storeOption = new \App\Record\StoreOption((int)$_POST['storeOptionId']);
						$storeOption->delete();
						$this->page->setResponse($_POST['storeOptionId']);

						break;
					}
				}
			}
		}
	}
