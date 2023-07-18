<?php

namespace App\WWW\Store;

class Invoice extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	protected int $customerId;

	private readonly \App\Model\Customer $customerModel;

	private readonly \App\Model\Invoice $invoiceModel;

	private readonly \App\View\Invoice $invoiceView;

	private readonly \App\View\Store $storeView;

	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		parent::__construct($controller);
		$this->invoiceView = new \App\View\Invoice($this->page);
		$this->storeView = new \App\View\Store($this->page);
		$this->invoiceModel = new \App\Model\Invoice();

		if (\App\Model\Session::checkCSRF())
			{
			if (isset($_POST['markAsShipped']))
				{
				if (\is_array($_POST['ship']))
					{
					foreach ($_POST['ship'] as $invoiceId => $ship)
						{
						if ($ship)
							{
							$this->invoiceModel->markAsShipped(new \App\Record\Invoice($invoiceId));
							}
						}
					}
				$this->page->redirect();
				}
			}
		$this->customerModel = new \App\Model\Customer();
		$this->customerId = $this->customerModel->getNumber();
		}

	public function create() : void
		{
		if ($this->page->addHeader('Create Invoice'))
			{
			$view = new \App\View\Invoice\Edit($this->page);
			$cartItems = \App\Model\Session::getCartItems();
			$this->page->addPageContent($view->create($cartItems));
			}
		}

	public function download(\App\Record\Invoice $invoice) : void
		{
		if (! $invoice->empty())
			{
			if ($this->page->isAuthorized('Download Invoice') || $invoice['memberId'] == $this->customerId)
				{
				$pdf = $this->invoiceModel->generatePDF($invoice);
				$pdf->Output($this->invoiceModel->getFileName($invoice), 'I');
				}
			}
		}

	public function find() : void
		{
		if ($this->page->addHeader('Find Invoice'))
			{
			$view = new \App\View\Invoice\Search($this->page);
			$this->page->addPageContent($view);
			}
		}

	public function myUnpaid() : void
		{
		$this->page->setPublic($this->customerId < 0);

		if ($this->page->addHeader('My Unpaid Invoices'))
			{
			$invoiceTable = new \App\Table\Invoice();
			$invoiceTable->setUnpaidForMember($this->customerId);
			$this->page->addPageContent($this->invoiceView->show($invoiceTable, 'You have no unpaid invoices'));
			}
		}

	public function pay(\App\Record\Invoice $invoice) : void
		{
		if ($invoice->unpaidBalance() <= 0.0)
			{
			$this->page->redirect('/Store/paid/' . $invoice->invoiceId);

			return;
			}

		if ($this->page->addHeader('Add Invoice Payment'))
			{
			$invoiceView = new \App\View\Invoice($this->page);
			$invoice->paymentDate = \App\Tools\Date::todayString();
			$this->page->addPageContent($invoiceView->markPaid($invoice));
			}
		}

	public function report() : void
		{
		if ($this->page->addHeader('Invoice Report'))
			{
			if (isset($_POST['submit']) && \App\Model\Session::checkCSRF())
				{
				$report = new \App\Report\InvoiceReport();
				$report->download($_POST);
				$this->page->done();
				}
			else
				{
				$form = new \PHPFUI\Form($this->page);
				$this->storeView->getInvoiceRequest($form);
				$form->addAttribute('target', '_blank');
				$form->add(new \PHPFUI\Submit('Generate Report'));
				$this->page->addPageContent($form);
				}
			}
		}

	public function unshipped() : void
		{
		if ($this->page->addHeader('Unshipped Invoices'))
			{
			$invoiceTable = new \App\Table\Invoice();
			$invoiceTable->setUnshippedInvoices();

			$form = new \PHPFUI\Form($this->page);
			$form->setAreYouSure(false);
			$buttonGroup = new \App\UI\CancelButtonGroup();

			if (\count($invoiceTable))
				{
				$pullList = new \PHPFUI\Button('Print Pull List', '/Store/pullList');
				$pullList->addAttribute('target', '_blank');
				$buttonGroup->addButton($pullList);
				$pullList = new \PHPFUI\Button('Print Ride Pull List');
				$pullList->addClass('warning');
				$this->addRidePullListModal($pullList);
				$buttonGroup->addButton($pullList);
				$markAsShipped = new \PHPFUI\Submit('Mark As Shipped', 'markAsShipped');
				$markAsShipped->addClass('secondary');
				$buttonGroup->addButton($markAsShipped);
				$form->add($buttonGroup);
				}

			$form->add($this->invoiceView->show($invoiceTable, 'All invoices have been shipped'));

			if (\count($buttonGroup))
				{
				$form->add($buttonGroup);
				}
			$this->page->addPageContent($form);
			}
		}

	private function addRidePullListModal(\PHPFUI\HTML5Element $modalLink) : \PHPFUI\Reveal
		{
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$form = new \PHPFUI\Form($this->page);
		$form->setAttribute('method', 'get');
		$form->addAttribute('action', '/Store/ridePullList');

		$form->setAreYouSure(false);
		$form->add(new \PHPFUI\Input\Date($this->page, 'rideDate', 'Date of Rides'));
		$submit = new \PHPFUI\Submit('Print Ride Pull List');
		$modal->closeOnClick($submit);
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);

		return $modal;
		}
	}
