<?php

namespace App\WWW;

class Privacy extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		parent::__construct($controller);
		$this->page->setPublic();
		$content = new \App\View\Content($this->page);
		$this->page->addPageContent($content->getDisplayCategoryHTML('Privacy Policy'));
		}
	}
