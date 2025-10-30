<?php

namespace App\WWW\Store;

class Configuration extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		parent::__construct($controller);
		}

	public function abandonEmail() : void
		{
		if ($this->page->addHeader('Abandon Cart Email'))
			{
			$editor = new \App\View\Email\Settings($this->page, 'abandonCart', new \App\Model\Email\AbandonCart());
			$this->page->addPageContent($editor);
			}
		}

	public function settings() : void
		{
		if ($this->page->addHeader('Store Settings'))
			{
			$storeView = new \App\View\Store($this->page);
			$this->page->addPageContent($storeView->configuration());
			}
		}
	}
