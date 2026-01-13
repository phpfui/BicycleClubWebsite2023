<?php

namespace App\View\Finance;

class Tax
	{
	private \App\Record\CartItem $cartItem;

	private \App\Record\Member $member;

	private \App\Record\Membership $membership;

	private \App\Record\StoreItem $storeItem;

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->member = \App\Model\Session::signedInMemberRecord();
		$this->membership = $this->member->membership;
		$cartItemTable = new \App\Table\CartItem();
		$cartItemTable->addJoin('storeItem');
		$condition = new \PHPFUI\ORM\Condition('memberId', $this->member->memberId);
		$condition->and('taxable', 1);
		$cartItemTable->setWhere($condition);
		$this->cartItem = $cartItemTable->getRecordCursor()->current();
		$this->storeItem = $this->cartItem->storeItem;
		}

	public function edit(\App\Record\Ziptax $zipTax) : \App\UI\ErrorFormSaver
		{
		if ($zipTax->loaded())
			{
			$submit = new \PHPFUI\Submit('Save');
			}
		else
			{
			$submit = null;
			}

		$form = new \App\UI\ErrorFormSaver($this->page, $zipTax, $submit);

		if ($form->save())
			{
			return $form;
			}
		elseif (! $submit)
			{
			$submit = new \PHPFUI\Submit('Add');

			if (($_POST['submit'] ?? '') == $submit->getText())
				{
				$zipTax->setFrom($_POST);
				$errors = $zipTax->validate();

				if ($errors)
					{
					\App\Model\Session::setFlash('alert', 'Please correct the fields highlighted');
					}
				else
					{
					$zipTax->insert();
					\App\Model\Session::setFlash('success', 'Tax Rate added');
					}
				$this->page->redirect();
				}
			}

		$fieldSet = new \PHPFUI\FieldSet('Required Fields');

		$zipCode = new \PHPFUI\Input\Text('zip_code', 'Zip Code', $zipTax->zip_code);
		$fieldSet->add($zipCode->setRequired()->setAttribute('maxlength', '32'));

		$rate = new \PHPFUI\Input\Number('zip_tax_rate', '% Tax Rate', \number_format($zipTax->zip_tax_rate ?? 0.0, 4));
		$fieldSet->add($rate->setRequired());
		$form->add($fieldSet);

		$fieldSet = new \PHPFUI\FieldSet('Informational Fields');

		$town = new \PHPFUI\Input\Text('zipcounty', 'Town', $zipTax->zipcounty);
		$fieldSet->add($town->setAttribute('maxlength', '32'));

		$state = new \PHPFUI\Input\Text('zipstate', 'State', $zipTax->zipstate);
		$fieldSet->add($state->setAttribute('maxlength', '32'));
		$form->add($fieldSet);

		$buttonGroup = new \PHPFUI\ButtonGroup();
		$buttonGroup->addButton($submit);
		$backButton = new \PHPFUI\Button('Back', '/Finance/editTaxTable');
		$backButton->addClass('secondary');
		$buttonGroup->addButton($backButton);

		$form->add($buttonGroup);

		return $form;
		}

	public function getTaxCalculation() : \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit('Save');
		$form = new \PHPFUI\Form($this->page, $submit);

		$settingTable = new \App\Table\Setting();
		$salesTaxFormulaField = 'salesTaxFormula';
		$salesTaxFormula = $settingTable->value($salesTaxFormulaField);

		if (isset($this->page->getQueryParameters()['test']))
			{
			try
				{
				if (! $this->storeItem->loaded())
					{
					\App\Model\Session::setFlash('alert', 'You have no taxable items in your shopping cart. Please add a taxable item.');
					$this->page->redirect();

					return $form;
					}
				$taxCalculator = new \App\Model\TaxCalculator();
				$tax = $taxCalculator->compute($this->cartItem->toArray(), (float)$this->member->volunteerPoints);
				\App\Model\Session::setFlash('success', 'Your forumula has no syntax errors. Computed tax: <b>$' . \number_format($tax, 2) . '</b>');
				}
			catch (\Exception $e)
				{
				$parts = \explode('\\', \get_class($e));
				$className = \PHPFUI\TextHelper::capitalSplit(\str_replace('Exception', '', \array_pop($parts)));
				\App\Model\Session::setFlash('alert', $className . ': ' . $e->getMessage());
				}
			$this->page->redirect();
			}

		if ($form->isMyCallback())
			{
			$settingTable->save($salesTaxFormulaField, $_POST[$salesTaxFormulaField]);
			$this->page->setResponse('Saved');

			return $form;
			}

		$tabs = new \PHPFUI\Tabs();

		$editLink = new \PHPFUI\Link('/Finance/editTaxTable', 'here.', false);
		$importLink = new \PHPFUI\Link('/Finance/importTaxTable', 'import them.', false);

		$instructions = 'For every item marked taxable in the user\'s cart, a sales tax amount needes to be computed. On the tabs above are variables that you can use to compute the tax.';
		$instructions .= '<br><br>In addition you can use <b>$taxRate</b> percentage as returned from the tax tables by zip code. You can edit the tax tables ' . $editLink . ' Or ' . $importLink;
		$fieldSet = new \PHPFUI\FieldSet('Example Generic Sales Tax Example');
		$callout = new \PHPFUI\Callout('info');
		$callout->add('$price * $quantity * $taxRate / 100.00');
		$fieldSet->add($callout);
		$instructions .= $fieldSet;

		$fieldSet = new \PHPFUI\FieldSet('Example with Volunteer Points Subtracted');
		$callout = new \PHPFUI\Callout('info');
		$callout->add('$price * $quantity * $taxRate / 100.0-$payByPoints * $volunteerPoints');
		$fieldSet->add($callout);
		$instructions .= $fieldSet;

		$fieldSet = new \PHPFUI\FieldSet('Example New York State Sales Tax Example');
		$fieldSet->add('In NY, clothing under $110 is exempt from 4% state sales tax but still subject to local taxes.');
		$nysCallout = new \PHPFUI\Callout('info');
		$nysCallout->add('$price * $quantity * if($state == "NY" && $price < 110.0 && $clothing == 1,$taxRate - 4.0, $taxRate) / 100.00');
		$fieldSet->add($nysCallout);
		$instructions .= $fieldSet;

		$tabs->addTab('Instructions', $instructions, true);
		$this->addTab($this->member, $tabs);
		$this->addTab($this->membership, $tabs);
		$this->addTab($this->cartItem, $tabs);
		$this->addTab($this->storeItem, $tabs);
		$form->add($tabs);

		$salesTaxFormulaInput = new \PHPFUI\Input\Text($salesTaxFormulaField, 'Sales Tax Formula', $salesTaxFormula);
		$form->add($salesTaxFormulaInput);
		$buttonGroup = new \PHPFUI\ButtonGroup();
		$buttonGroup->add($submit);
		$testButton = new \PHPFUI\Button('Test', $this->page->getBaseURL() . '?test');
		$form->saveOnClick($testButton);
		$buttonGroup->add($testButton->addClass('warning'));
		$form->add($buttonGroup);

		return $form;
		}

	public function show() : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$zipTaxTable = new \App\Table\Ziptax();

		$searchableHeaders = ['zip_code' => 'Zip Code', 'zip_tax_rate' => 'Tax Rate', 'zipcounty' => 'Town', 'zipstate' => 'State'];
		$countHeaders = ['Edit'];

		if ($this->page->isAuthorized('Delete Zip Tax'))
			{
			$countHeaders[] = 'Del';
			}

		$view = new \App\UI\ContinuousScrollTable($this->page, $zipTaxTable);

		$deleter = new \App\Model\DeleteRecord($this->page, $view, $zipTaxTable, 'Are you sure you want to permanently delete this zip tax rate?');
		$view->addCustomColumn('Del', $deleter->columnCallback(...));
		$view->addCustomColumn('Edit', static function(array $zipTax)
			{
			return new \PHPFUI\FAIcon('far', 'edit', '/Finance/editZiptax/' . $zipTax['zip_id']);
			});

		$view->setSearchColumns(\array_keys($searchableHeaders));
		$view->setSortableColumns(\array_keys($searchableHeaders));
		$view->setHeaders(\array_merge($searchableHeaders, $countHeaders));

		$container->add(new \PHPFUI\Button('Add Zip Tax', '/Finance/editZiptax/0'));

		$container->add($view);

		return $container;
		}

	/**
	 * @param array<string,mixed> $fields
	 */
	private function addArrayTab(string $name, array $fields, \PHPFUI\Tabs $tabs) : \PHPFUI\Tabs
		{
		unset($fields['password']);
		\ksort($fields);

		$table = new \PHPFUI\Table();
		$rows = [];
		$numColumns = 3;
		$column = 0;
		$lines = (int)(\count($fields) + 1) / $numColumns;
		$line = 0;

		foreach ($fields as $field => $value)
			{
			$rows[$line++][$column] = '<b>$' . $field . '</b> ' . $value;

			if ($line >= $lines)
				{
				++$column;
				$line = 0;
				}
			}

		while ($line < $lines)
			{
			$rows[$line++][$column] = '&nbsp;';
			}

		foreach ($rows as $row)
			{
			$table->addRow($row);
			}
		$tabs->addTab($name, $table);

		return $tabs;
		}

	private function addTab(\PHPFUI\ORM\Record $record, \PHPFUI\Tabs $tabs) : \PHPFUI\Tabs
		{
		return $this->addArrayTab(\PHPFUI\TextHelper::capitalSplit($record->getTableName()), $record->toArray(), $tabs);
		}
	}
