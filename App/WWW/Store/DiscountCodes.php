<?php

namespace App\WWW\Store;

class DiscountCodes extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	private readonly \App\View\Store\DiscountCode $view;

	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		parent::__construct($controller);
		$this->view = new \App\View\Store\DiscountCode($this->page);
		}

	public function add() : void
		{
		if ($this->page->addHeader('Add Discount Code'))
			{
			$this->page->addPageContent($this->view->edit(new \App\Record\DiscountCode()));
			}
		}

	public function list() : void
		{
		if ($this->page->addHeader('Discount Codes'))
			{
			$this->page->addPageContent($this->view->show());
			$this->page->addPageContent(new \App\UI\CancelButtonGroup(new \PHPFUI\Button('Add Discount Code', '/Store/DiscountCodes/add')));
			}
		}

	public function edit(\App\Record\DiscountCode $discountCode = new \App\Record\DiscountCode()) : void
		{
		if ($this->page->addHeader('Edit Discount Code'))
			{
			$this->page->addPageContent($this->view->edit($discountCode));
			}
		}
	}
