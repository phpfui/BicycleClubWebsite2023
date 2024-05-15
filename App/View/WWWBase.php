<?php

namespace App\View;

class WWWBase implements \Stringable
	{
	protected \App\View\Page $page;

	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		$this->page = new \App\View\Page($controller); // @phpstan-ignore argument.type
		}

	public function __toString() : string
		{
		return "{$this->page}";
		}

	public function landingPage() : void
		{
		$this->page->landingPage();
		}
	}
