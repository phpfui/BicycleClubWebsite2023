<?php

namespace App\WWW\Store;

class Options extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	private readonly \App\View\Store\Options $view;

	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		parent::__construct($controller);
		$this->view = new \App\View\Store\Options($this->page);
		}

	public function add() : void
		{
		if ($this->page->addHeader('Add Store Option'))
			{
			$this->page->addPageContent($this->view->edit(new \App\Record\StoreOption()));
			}
		}

	public function list() : void
		{
		if ($this->page->addHeader('Store Options'))
			{
			$this->page->addPageContent($this->view->show());
			$this->page->addPageContent(new \App\UI\CancelButtonGroup(new \PHPFUI\Button('Add Store Option', '/Store/Options/add')));
			}
		}

	public function edit(\App\Record\StoreOption $storeOption = new \App\Record\StoreOption()) : void
		{
		if ($this->page->addHeader('Edit Store Option'))
			{
			$this->page->addPageContent($this->view->edit($storeOption));
			}
		}
	}
