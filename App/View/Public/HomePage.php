<?php

namespace App\View\Public;

class HomePage extends \App\View\Page implements \PHPFUI\Interfaces\NanoClass
	{
	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		parent::__construct($controller); // @phpstan-ignore argument.type
		$this->setPublic();
		$this->addBanners();
		$content = new \App\View\Content($this);
		$this->addPageContent($content->getDisplayCategoryHTML('Main Page'));
		}
	}
