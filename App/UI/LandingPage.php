<?php

namespace App\UI;

class LandingPage extends \PHPFUI\Menu
	{
	public function __construct(private readonly \App\View\Page $page)
		{
		$this->addClass('vertical');
		}

	public function addLink(string $link, string $header) : static
		{
		if ($this->page->isAuthorized($header))
			{
			$this->addMenuItem(new \PHPFUI\MenuItem($header, $link));
			}

		return $this;
		}

	protected function getStart() : string
		{
		$this->sort();

		return parent::getStart();
		}
	}
