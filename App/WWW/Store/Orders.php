<?php

namespace App\WWW\Store;

class Orders extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	private readonly \App\View\Store\Orders $view;

	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		parent::__construct($controller);
		$this->view = new \App\View\Store\Orders($this->page);
		}

	public function list() : void
		{
		if ($this->page->addHeader('Store Orders'))
			{
			$this->page->addPageContent($this->view->show());
			}
		}
	}
