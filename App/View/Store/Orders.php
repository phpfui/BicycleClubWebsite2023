<?php

namespace App\View\Store;

class Orders
	{
	public function __construct(private readonly \App\View\Page $page)
		{
		if (\PHPFUI\Session::checkCSRF() && 'Print' == ($_POST['submit'] ?? ''))
			{
			$labels = new \PDF_Label($_POST['label']);
			$duplicateAddresses = [];
			$storeOrderTable = $this->getStoreOrderTable();
			$storeOrderTable->setLimit(0);
			$view = new \App\UI\ContinuousScrollTable($this->page, $storeOrderTable);

			foreach ($view->getArrayCursor() as $row)
				{
				if ('Stock' == $_POST['labelType'])
					{
					$text = "{$row['firstName']} {$row['lastName']}\n\n";
					$text .= "{$row['title']}\n";
					$text .= "{$row['optionsSelected']}\n\n";
					$text .= "Quantity: {$row['quantity']}";
					}
				else
					{
					$key = "{$row['address']}|{$row['town']}|{$row['zip']}";

					if (isset($duplicateAddresses[$key]))
						{
						continue;
						}
					$duplicateAddresses[$key] = true;
					$text = "{$row['firstName']} {$row['lastName']}\n";
					$text .= "{$row['address']}\n";
					$text .= "{$row['town']} {$row['state']} {$row['zip']}";
					}
				$labels->Add_PDF_Label($text);
				}
			$labels->output('store_order_' . $_POST['labelType'] . '_' . \App\Tools\Date::todayString() . '.pdf', 'D');

			exit;
			}
		}

	public function show() : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$storeOrderTable = $this->getStoreOrderTable();

		if (! \count($storeOrderTable))
			{
			$container->add(new \PHPFUI\SubHeader('No Store Orders found'));

			return $container;
			}
		$view = new \App\UI\ContinuousScrollTable($this->page, $storeOrderTable);

		$headers = ['title' => 'Item', 'optionsSelected' => 'Selected Options', 'quantity' => 'Quantity', 'invoiceId' => 'Invoice', 'firstName' => 'First Name', 'lastName' => 'Last Name', 'added' => 'Date Added'];

		$view->addCustomColumn('invoiceId', static function(array $storeOrder)
			{
			$link = new \PHPFUI\Link('/Store/Invoice/download/' . $storeOrder['invoiceId'], $storeOrder['invoiceId'], false);
			$link->addAttribute('target', '_blank');

			return $link;
			});
		$view->setSearchColumns($headers)->setSortableColumns(\array_keys($headers))->setHeaders($headers);
		$labelButton = new \PHPFUI\Button('Labels');
		$labelButton->addClass('secondary');
		$container->add($labelButton);
		$container->add($view);

		$reveal = new \PHPFUI\Reveal($this->page, $labelButton);
		$form = new \PHPFUI\Form($this->page);
		$fieldSet = new \PHPFUI\FieldSet('Select Label Types');
		$fieldSet->add(new \App\UI\LabelStock());
		$radioGroup = new \PHPFUI\Input\RadioGroup('labelType', 'Label Type', 'Stock');
		$radioGroup->addButton('Stock');
		$radioGroup->addButton('Address');
		$fieldSet->add($radioGroup);
		$form->add($fieldSet);
		$printButton = new \PHPFUI\Submit('Print');
		$reveal->closeOnClick($printButton);
		$form->add($reveal->getButtonAndCancel($printButton));
		$reveal->add($form);

		return $container;
		}

	private function getStoreOrderTable() : \App\Table\StoreOrder
		{
		$storeOrderTable = new \App\Table\StoreOrder();
		$storeOrderTable->addJoin('storeItem');
		$storeOrderTable->addJoin('member');
		$storeOrderTable->addJoin('membership', new \PHPFUI\ORM\Condition('member.membershipId', new \PHPFUI\ORM\Field('membership.membershipId')));

		return $storeOrderTable;
		}
	}
