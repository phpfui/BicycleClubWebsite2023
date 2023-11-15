<?php

namespace App\View;

class Payments
	{
	public function __construct(private readonly \App\View\Page $page)
		{
		$this->processAJAXRequest();
		}

	/**
	 * @param \PHPFUI\ORM\RecordCursor<\App\Record\Payment> $payments
	 */
	public function show(\PHPFUI\ORM\RecordCursor $payments, string $noPaymentMessage = 'No payments found') : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		if (! \count($payments))
			{
			$container->add(new \PHPFUI\SubHeader($noPaymentMessage));

			return $container;
			}

		$headers = ['paymentType' => 'Type',
			'paymentNumber' => 'Number',
			'amount' => 'Amount',
			'paymentDated' => 'Dated',
			'dateReceived' => 'Received',
			'invoiceId' => 'Invoice', ];
		$delete = null;

		if ($this->page->isAuthorized('Delete Payment'))
			{
			$headers['del'] = 'Del';
			$delete = new \PHPFUI\AJAX('deletePayment', 'Permanently delete this payment?');
			$delete->addFunction('success', '$("#paymentId-"+data.response).css("background-color","red").hide("fast").remove()');
			$this->page->addJavaScript($delete->getPageJS());
			}

		$total = 0;
		$paymentsByYear = [];

		foreach ($payments as $payment)
			{
			$total += $payment->amount;
			$year = (int)($payment->dateReceived);

			if (! isset($paymentsByYear[$year]))
				{
				$paymentsByYear[$year] = [];
				}
			$paymentsByYear[$year][] = clone $payment;
			}

		\krsort($paymentsByYear);
		$tabs = new \PHPFUI\Tabs();
		$active = true;

		foreach ($paymentsByYear as $year => $yearPayments)
			{
			$tabs->addTab((string)$year, $this->getPaymentTable($yearPayments, $headers, $delete), $active);
			$active = false;
			}
		$container->add($tabs);

		$total = \number_format($total, 2);
		$container->add(new \App\UI\Display('Grand Total of all Years', '<b>$' . $total . '</b>'));

		return $container;
		}

	protected function processAJAXRequest() : void
		{
		if (\App\Model\Session::checkCSRF() && isset($_POST['action']) && $this->page->isAuthorized('Delete Payment'))
			{
			switch ($_POST['action'])
				{
				case 'deletePayment':

					$payment = new \App\Record\Payment((int)$_POST['paymentId']);
					$payment->delete();
					$this->page->setResponse($_POST['paymentId']);

					break;

				}
			}
		}

	/**
	 * @param array<\App\Record\Payment> $payments
	 * @param array<string,string> $headers
	 */
	private function getPaymentTable(array $payments, array $headers, ?\PHPFUI\AJAX $delete) : \PHPFUI\Table
		{
		$table = new \PHPFUI\Table();
		$table->setRecordId('paymentId');
		$table->setHeaders($headers);
		$dollar = '$';
		$total = 0;

		$paymentTypes = \App\Table\Payment::getPaymentTypes();

		foreach ($payments as $payment)
			{
			$row = $payment->toArray();
			$total += $payment->amount;
			$row['amount'] = $dollar . $payment->amount;
			$row['paymentType'] = $paymentTypes[$payment->paymentType] ?? 'Unknown';
			$row['dateReceived'] = $payment->dateReceived;
			$row['paymentDated'] = $payment->paymentDated;

			if (! empty($payment->invoiceId))
				{
				$row['invoiceId'] = new \PHPFUI\FAIcon('fas', 'file-pdf', '/Store/Invoice/download/' . $payment->invoiceId);
				}
			else
				{
				$row['invoiceId'] = 'N/A';
				}

			if ($delete)
				{
				$icon = new \PHPFUI\FAIcon('far', 'trash-alt', '#');
				$icon->addAttribute('onclick', $delete->execute(['paymentId' => $payment->paymentId]));
				$row['del'] = $icon;
				}
			$table->addRow($row);
			}

		$total = \number_format($total, 2);
		$table->addRow(['paymentType' => '',
			'paymentNumber' => '<b>Total for Year</B>',
			'amount' => "<b>&dollar;{$total}</b>",
			'dateReceived' => '',
			'paymentDated' => '',
			'del' => '', ]);

		return $table;
		}
	}
