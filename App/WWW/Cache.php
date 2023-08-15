<?php

namespace App\WWW;

class Cache extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	public function buster() : void
		{
		$this->page->getCacheBuster()->outputBustedPage($this->page->getBaseURL());
		}
	}
