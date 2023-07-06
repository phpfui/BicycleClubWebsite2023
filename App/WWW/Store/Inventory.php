<?php

namespace App\WWW\Store;

class Inventory extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	private readonly \App\View\Store $storeView;

	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		parent::__construct($controller);
		$this->storeView = new \App\View\Store($this->page);
		}

	public function manage() : void
		{
		if ($this->page->addHeader('Manage Inventory'))
			{
			$storeItemTable = new \App\Table\StoreItem();
			$this->page->addPageContent($this->storeView->showInventory($storeItemTable));
			}
		}

	public function report() : void
		{
		if ($this->page->addHeader('Inventory Report'))
			{
			if (isset($_POST['submit']) && \App\Model\Session::checkCSRF())
				{
				$report = new \App\Report\Inventory();
				$report->download($_POST);
				$this->page->done();
				}
			else
				{
				$this->page->addPageContent($this->storeView->getInventoryRequest());
				}
			}
		}
	}
