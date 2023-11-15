<?php

namespace App\View;

class Invoice
	{
	private readonly bool $cancel;

	private readonly bool $canShip;

	private float $invoiceTotal = 0.0;

	private string $type = '';

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->canShip = $this->page->isAuthorized('Unshipped Invoices');
		$this->cancel = $this->page->isAuthorized('Cancel Order');
		}

	public function markPaid(\App\Record\Invoice $invoice) : string | \PHPFUI\Form
		{
		if (! $invoice->empty())
			{
			if (\App\Model\Session::checkCSRF())
				{
				if ('Add Payment' == isset($_POST['submit']))
					{
					$payment = new \App\Record\Payment();
					$payment->invoiceId = $invoice->invoiceId;
					$payment->paymentType = (int)$_POST['paymentType'];
					$payment->amount = (float)$_POST['paymentAmount'];
					$payment->dateReceived = \App\Tools\Date::todayString();
					$payment->paymentNumber = $_POST['paymentNumber'];
					$payment->paymentDated = $_POST['paymentDated'];
					$payment->insert();
					$amount = 0;

					foreach ($invoice->PaymentChildren as $payment)
						{
						$amount += $payment->amount;
						}

					if ($amount >= $invoice->totalPrice)
						{
						$invoiceModel = new \App\Model\Invoice();
						$invoiceModel->markAsReceived($invoice);
						}
					$this->page->redirect();
					}
				}
			$form = new \PHPFUI\Form($this->page);
			$form->add($this->status($invoice));
			$form->add($this->payments($invoice));
			$fieldSet = new \PHPFUI\FieldSet('Payment Information');
			$multiColumn = new \PHPFUI\MultiColumn();
			$paymentType = new \PHPFUI\Input\Select('paymentType', 'Payment Type');

			foreach (\App\Table\Payment::getPaymentTypes() as $index => $type)
				{
				$paymentType->addOption($type, $index, 0 == $index);
				}
			$multiColumn->add($paymentType);
			$checkNumber = new \PHPFUI\Input\Text('paymentNumber', 'Payment Number');
			$multiColumn->add($checkNumber);
			$checkDate = new \PHPFUI\Input\Date($this->page, 'paymentDated', 'Payment Date', $invoice->paymentDate);
			$multiColumn->add($checkDate);
			$checkAmount = new \PHPFUI\Input\Number('paymentAmount', 'Payment Amount', $invoice->totalPrice);
			$checkAmount->setToolTip('Whole numbers are assumed to be dollars and no cents.');
			$multiColumn->add($checkAmount);
			$fieldSet->add($multiColumn);
			$form->add($fieldSet);
			$form->add(new \PHPFUI\Submit('Add Payment'));

			return $form;
			}

		return "Invoice #{$invoice->invoiceId} not found.";
		}

	public function show(\App\Table\Invoice $invoiceTable, string $noInvoices = 'No invoices found', string $type = 'Shipped') : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();
		$this->type = $type;

		if (! \count($invoiceTable))
			{
			$container->add(new \PHPFUI\SubHeader($noInvoices));

			return $container;
			}

		$customerModel = new \App\Model\Customer();
		$headers = ['invoiceId' => 'Invoice<br>#',
			'orderDate' => 'Order<br>Date',
			'paymentDate' => 'Paid On',
			'fullfillmentDate' => 'Shipped On',
			'totalPrice' => 'Total',
			'firstName' => 'First Name',
			'lastName' => 'Last Name',
		];

		$extraHeaders = [
			'view' => 'View',
		];

		if ($this->page->isAuthorized('Unshipped Invoices'))
			{
			$extraHeaders['label'] = 'Label';
			}
		$extraHeaders['cancel'] = 'Cancel';

		$view = new \App\UI\ContinuousScrollTable($this->page, $invoiceTable);

		$view->addCustomColumn('label', static fn (array $invoice) => new \PHPFUI\Link('/Store/mailingLabel/' . $invoice['invoiceId'], 'Print', false));
		$view->addCustomColumn('view', static fn (array $invoice) => new \PHPFUI\FAIcon('fas', 'file-download', '/Store/Invoice/download/' . $invoice['invoiceId']));
		$view->addCustomColumn('cancel', $this->getCancelColumn(...));
		$view->addCustomColumn('paymentDate', $this->getPaymentDate(...));
		$view->addCustomColumn('fullfillmentDate', $this->getFullfillmentDate(...));
		$view->addCustomColumn('firstName', static function(array $invoice) use ($customerModel)
			{
			$member = $customerModel->read((int)$invoice['memberId']);

			return $member->firstName;
			});
		$view->addCustomColumn('lastName', static function(array $invoice) use ($customerModel)
			{
			$member = $customerModel->read((int)$invoice['memberId']);

			return $member->lastName;
			});

		$view->setHeaders(\array_merge($headers, $extraHeaders));
		$view->setSearchColumns(\array_keys($headers));
		$view->setSortableColumns(\array_keys($headers));

		$container->add($view);

		return $container;
		}

	public function status(\App\Record\Invoice $invoice, bool $deleted = false) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();
		$fieldSet = new \PHPFUI\FieldSet('Invoice Status');
		$fieldSet->add(new \App\UI\Display('Invoice Id', $invoice->invoiceId));
		$fieldSet->add(new \App\UI\Display('Order Date', $invoice->orderDate));

		if ($invoice->paymentDate > '1000-01-01')
			{
			$fieldSet->add(new \App\UI\Display('Payment Date', $invoice->paymentDate));
			}

		if ($invoice->fullfillmentDate > '1000-01-01')
			{
			$fieldSet->add(new \App\UI\Display('Fullfillment  Date', $invoice->fullfillmentDate));
			}
		$fieldSet->add(new \App\UI\Display('Shipping', '$' . \number_format($invoice->totalShipping, 2)));
		$fieldSet->add(new \App\UI\Display('Tax', '$' . \number_format($invoice->totalTax, 2)));
		$fieldSet->add(new \App\UI\Display('Total', '$' . \number_format($invoice->totalPrice, 2)));

		if (! empty($invoice->pointsUsed))
			{
			$fieldSet->add(new \App\UI\Display('Volunteer Points Used', $invoice->pointsUsed));
			}
		$fieldSet->add(new \App\UI\Display('Total Paid', '$' . \number_format($invoice->paypalPaid, 2)));

		if ($invoice->paypaltx)
			{
			$fieldSet->add(new \App\UI\Display('PayPal Transaction Id', $invoice->paypaltx));
			}

		if ($invoice->instructions)
			{
			$fieldSet->add(new \App\UI\Display('Instructions', $invoice->instructions));
			}

		if (! $deleted)
			{
			$download = new \PHPFUI\FAIcon('fas', 'file-download', '/Store/Invoice/download/' . $invoice->invoiceId);
			$fieldSet->add(new \App\UI\Display('Download', $download));
			}
		$container->add($fieldSet);

		$fieldSet = new \PHPFUI\FieldSet('Invoice Details');
		$table = new \PHPFUI\Table();
		$table->setHeaders(['title' => 'Item', 'detailLine' => 'Detail', 'quantity' => 'Quantity',
			'price' => 'Price', 'shipping' => 'Shipping', 'tax' => 'Tax', ]);

		foreach ($invoice->InvoiceItemChildren as $item)
			{
			$row = $item->toArray();

			foreach (['price', 'shipping', 'tax'] as $index)
				{
				$row[$index] = '$' . $row[$index];
				}
			$table->addRow($row);
			}
		$fieldSet->add($table);
		$container->add($fieldSet);

		return $container;
		}

	/**
	 * @param array<string,mixed> $invoice
	 */
	private function getCancelColumn(array $invoice) : string
		{
		$this->invoiceTotal += $invoice['totalPrice'];

		if (! $this->getDate($invoice['paymentDate']) || $this->cancel)
			{
			$delete = new \PHPFUI\FAIcon('far', 'trash-alt', '/Store/cancelOrder/' . $invoice['invoiceId']);

			if ($invoice['paypalPaid'] > 0.0)
				{
				$delete->setConfirm('Cancel this order and issue a refund?');
				}
			else
				{
				$delete->setConfirm('Cancel this order?');
				}

			return $delete;
			}

		return '';
		}

	private function getDate(?string $date, string $default = '') : string
		{
		if (! $date || '1000-01-01' > $date)
			{
			return $default;
			}

		return $date;
		}

	/**
	 * @param array<string,string> $invoice
	 */
	private function getFullfillmentDate(array $invoice) : string
		{
		$shipDate = $this->getDate($invoice['fullfillmentDate'], 'Pending');

		if (! $this->getDate($invoice['paymentDate']) && $invoice['paidByCheck'])
			{
			$shipDate = new \PHPFUI\Link('/Finance/markPaid/' . $invoice['invoiceId'], 'Mark Paid', false);
			}
		elseif (! $this->getDate($invoice['fullfillmentDate']) && $this->canShip)
			{
			$shipDate = new \PHPFUI\Input\CheckBoxBoolean("ship[{$invoice['invoiceId']}]", 'Mark');
			$shipDate->setToolTip('Check to mark as ' . $this->type);
			}

		return $shipDate;
		}

	/**
	 * @param array<string,string> $invoice
	 */
	private function getPaymentDate(array $invoice) : string
		{
		return $this->getDate($invoice['paymentDate'], new \PHPFUI\Link('/Store/pay/' . $invoice['invoiceId'], 'Pay Now', false));
		}

	private function payments(\App\Record\Invoice $invoice) : \PHPFUI\FieldSet
		{
		$fieldSet = new \PHPFUI\FieldSet('Payments Received');
		$view = new \App\View\Payments($this->page);
		$fieldSet->add($view->show($invoice->PaymentChildren));

		return $fieldSet;
		}
	}
