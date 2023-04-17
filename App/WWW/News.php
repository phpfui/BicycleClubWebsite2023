<?php

namespace App\WWW;

class News extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	private readonly \App\View\Content $content;

	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		parent::__construct($controller);
		$this->content = new \App\View\Content($this->page);
		}

	public function board(int $year = 0) : void
		{
		if (! $year)
			{
			$year = (int)\App\Tools\Date::format('Y');
			}

		if ($this->page->addHeader('Board Minutes'))
			{
			$earliest = (int)\App\Table\Blog::getOldest('Board News');

			if ($earliest)
				{
				$subnav = new \App\UI\YearSubNav('/News/board', $year, $earliest);
				$this->page->addPageContent($subnav);
				$this->page->addPageContent($this->content->getDisplayCategoryHTML('Board News', 0, $year));
				}
			else
				{
				$this->page->addPageContent($this->content->getDisplayCategoryHTML('Board News'));
				$this->page->addPageContent(new \PHPFUI\SubHeader('No Minutes Found'));
				}
			}
		}

	public function latest() : void
		{
		if ($this->page->addHeader('Latest News'))
			{
			$this->page->addPageContent($this->content->getDisplayCategoryHTML('Latest News'));
			}
		}
	}
