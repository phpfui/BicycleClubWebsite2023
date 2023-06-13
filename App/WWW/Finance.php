<?php

namespace App\WWW;

class Finance extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	private readonly \App\View\Finance $view;

	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		parent::__construct($controller);
		$this->view = new \App\View\Finance($this->page);
		}

	public function checksNotReceived() : void
		{
		if ($this->page->addHeader('Unreceived Checks'))
			{
			$invoiceView = new \App\View\Invoice($this->page);
			$invoiceTable = new \App\Table\Invoice();
			$invoiceTable->setUnrecordedChecks();
			$this->page->addPageContent($invoiceView->show($invoiceTable, 'No unreceived checks', 'Received'));
			}
		}

	public function checksReceived() : void
		{
		if ($this->page->addHeader('Print Checks Received'))
			{
			if (isset($_POST['submit']) && \App\Model\Session::checkCSRF())
				{
				$report = new \App\Report\Finance();
				$report->downloadChecksReceived($_POST);
				$this->page->done();
				}
			else
				{
				$this->page->addPageContent($this->view->getChecksRequest());
				}
			}
		}

	public function importTaxTable() : void
		{
		if ($this->page->addHeader('Import Tax Table'))
			{
			$this->page->addPageContent($this->view->ImportTaxTable());
			}
		}

	public function invoice() : void
		{
		if ($this->page->addHeader('Invoice Summary'))
			{
			if (isset($_POST['csv']) && \App\Model\Session::checkCSRF())
				{
				$report = new \App\Report\Finance();
				$report->downloadInvoiceSummary($_POST);
				$this->page->done();
				}
			else
				{
				$this->page->addPageContent($this->view->getStoreRequest(false));
				}
			}
		}

	public function maintenance() : void
		{
		if ($this->page->addHeader('Check Maintenance'))
			{
			$this->page->addPageContent($this->view->maintenance());
			}
		}

	public function markPaid(\App\Record\Invoice $invoice = new \App\Record\Invoice()) : void
		{
		if ($this->page->addHeader('Mark Invoice Paid'))
			{
			$invoiceView = new \App\View\Invoice($this->page);
			$this->page->addPageContent($invoiceView->markPaid($invoice));
			}
		}

	public function missingInvoices() : void
		{
		if ($this->page->addHeader('Missing Invoices'))
			{
			if (isset($_FILES['file']) && \App\Model\Session::checkCSRF())
				{
				$csvReader = new \App\Tools\CSVReader($_FILES['file']['tmp_name']);
				$model = new \App\Model\Invoice();
				$missingInvoices = $model->findMissingInvoices($csvReader);
				$this->page->addPageContent($this->view->ShowMissingInvoices($missingInvoices));
				}
			$this->page->addPageContent($this->view->RequestMissingInvoices());
			}
		}

	public function payPal() : void
		{
		if ($this->page->addHeader('PayPal Settings'))
			{
			$this->page->addPageContent($this->view->PayPalSettings());
			}
		}

	public function payPalTerms() : void
		{
		if ($this->page->addHeader('PayPal Terms and Conditions'))
			{
			$this->page->addPageContent(new \App\View\SettingEditor($this->page, 'PayPalTerm'));
			}
		}

	public function store() : void
		{
		if ($this->page->addHeader('Store Payment Summary'))
			{
			if (isset($_POST['csv']) && \App\Model\Session::checkCSRF())
				{
				$report = new \App\Report\Finance();
				$report->downloadPaymentSummary($_POST);
				$this->page->done();
				}
			else
				{
				$this->page->addPageContent($this->view->getStoreRequest());
				}
			}
		}

	public function tax() : void
		{
		if ($this->page->addHeader('Taxes Collected'))
			{
			if (isset($_POST['csv']) && \App\Model\Session::checkCSRF())
				{
				$report = new \App\Report\Finance();
				$report->downloadTaxesCollected($_POST);
				$this->page->done();
				}
			else
				{
				$this->page->addPageContent($this->view->getTaxRequest());
				}
			}
		}
	}
