<?php

namespace App\View\Store;

class DiscountCode
	{
	public function __construct(private readonly \App\View\Page $page)
		{
		$this->processRequest();
		}

	public function edit(\App\Record\DiscountCode $discountCode = new \App\Record\DiscountCode()) : \App\UI\ErrorFormSaver
		{
		if ($discountCode->discountCodeId)
			{
			$submit = new \PHPFUI\Submit();
			}
		else
			{
			$submit = new \PHPFUI\Submit('Add');
			}
		$form = new \App\UI\ErrorFormSaver($this->page, $discountCode, $submit);

		if ($form->save('/Store/DiscountCodes/list'))
			{
			return $form;
			}
		$form->add(new \PHPFUI\Input\Hidden('discountCodeId', (string)$discountCode->discountCodeId));
		$fieldSet = new \PHPFUI\FieldSet('Discount Code Information');

		$discountCodeField = new \PHPFUI\Input\Text('discountCode', 'Discount Code', $discountCode->discountCode);
		$discountCodeField->setToolTip('This is the code the user enters to receive the discount.  Case insensitive');
		$discountCodeField->setRequired();
		$fieldSet->add($discountCodeField);

		$description = new \PHPFUI\Input\Text('description', 'Description', $discountCode->description);
		$description->setToolTip('A note so you know what this discount code is for.');
		$fieldSet->add($description);

		$startDate = new \PHPFUI\Input\Date($this->page, 'startDate', 'Start Date', $discountCode->startDate);
		$startDate->setRequired();
		$startDate->setMinDate(\App\Tools\Date::todayString());
		$startDate->setToolTip('The discount code will be not be valid until this date.');
		$expires = new \PHPFUI\Input\Date($this->page, 'expirationDate', 'Expiration Date', $discountCode->expirationDate);
		$expires->setRequired();
		$expires->setToolTip('The discount code will be valid through this date.');
		$fieldSet->add(new \PHPFUI\MultiColumn($startDate, $expires));

		$validItems = new \PHPFUI\Input\Text('validItemNumbers', 'Valid Item Numbers', $discountCode->validItemNumbers);
		$validItems->setToolTip('You can limit the items a discount code can be applied to. Enter the item numbers here, comma separated.  If you use just the first number, all versions of the item will get the discount, or list both numbers to restrict the discount to a specific version of the item.');
		$fieldSet->add($validItems);

		$discountType = new \PHPFUI\Input\RadioGroupEnum('type', 'Discount Type', $discountCode->type);

		$discount = new \PHPFUI\Input\Number('discount', 'Discount Amount', \number_format($discountCode->discount ?? 0, 2));
		$discount->setToolTip('The amount of the discount in dollars and cents or percentage.');
		$discount->setRequired();

		$cashOnly = new \PHPFUI\Input\CheckBoxBoolean('cashOnly', 'Cash Discount Only', (bool)$discountCode->cashOnly);
		$cashOnly->setToolTip('Check to excude volunteer points from the discount');

		$fieldSet->add(new \PHPFUI\MultiColumn($discountType, $discount, $cashOnly));

		$uses = new \PHPFUI\Input\Number('maximumUses', 'Maximum Uses', $discountCode->maximumUses);
		$uses->setToolTip('The number of times this discount code can be used. Blank is unlimited.');
		$repeat = new \PHPFUI\Input\Number('repeatCount', 'Repeat Count', $discountCode->repeatCount);
		$repeat->setToolTip('The number of times it can be used on one transaction.');
		$fieldSet->add(new \PHPFUI\MultiColumn($uses, $repeat));

		$form->add($fieldSet);
		$buttonGroup = new \PHPFUI\ButtonGroup();
		$buttonGroup->addButton($submit);
		$cancel = new \PHPFUI\Button('Cancel', '/Store/DiscountCodes/list');
		$cancel->addClass('hollow')->addClass('alert');
		$buttonGroup->addButton($cancel);
		$form->add($buttonGroup);

		return $form;
		}

	public function show() : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$discountCodeTable = new \App\Table\DiscountCode();

		if (! \count($discountCodeTable))
			{
			$container->add(new \PHPFUI\SubHeader('No Discount Codes found'));

			return $container;
			}
		$discountCodeTable->setLimit(10);
		$view = new \App\UI\ContinuousScrollTable($this->page, $discountCodeTable);

		$headers = ['discountCode', 'startDate', 'expirationDate', 'discount', 'used' => 'Times Used', ];

		$deleter = new \App\Model\DeleteRecord($this->page, $view, $discountCodeTable, 'Are you sure you want to permanently delete this discound code?');
		$view->addCustomColumn('del', $deleter->columnCallback(...));
		$view->addCustomColumn('used', static function(array $discountArray)
			{
			$discountCode = new \App\Record\DiscountCode();
			$discountCode->setFrom($discountArray);

			return $discountCode->timesUsed;
			});
		$view->addCustomColumn('discountCode', static fn (array $discountCode) => new \PHPFUI\Link('/Store/DiscountCodes/edit/' . $discountCode['discountCodeId'], $discountCode['discountCode'], false));
		$view->setSearchColumns($headers)->setHeaders(\array_merge($headers, ['del']))->setSortableColumns($headers);
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
					case 'deleteDiscountCode':
						$discountCode = new \App\Record\DiscountCode($_POST['discountCodeId']);
						$discountCode->delete();
						$this->page->setResponse($_POST['discountCodeId']);

						break;
					}
				}
			}
		}
	}
