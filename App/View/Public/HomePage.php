<?php

namespace App\View\Public;

class HomePage extends \App\View\Page implements \PHPFUI\Interfaces\NanoClass
	{
	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		// @phpstan-ignore-next-line
		parent::__construct($controller);
		$this->setPublic();
		$this->addBanners();
		$content = new \App\View\Content($this);
		$this->addPageContent($content->getDisplayCategoryHTML('Main Page'));
		}
	}
