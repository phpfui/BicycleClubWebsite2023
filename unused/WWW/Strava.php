<?php

namespace App\WWW;

class Strava extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	public function settings(string $parameter = '') : void
		{
		if ($this->page->addHeader('Strava Settings'))
			{
			$view = new \App\View\Strava($this->page);
			$this->page->addPageContent($view->editSettings($parameter));
			}
		}

	public function list() : void
		{
		if ($this->page->addHeader('Strava Routes'))
			{
			}
		}

	public function upcoming() : void
		{
		if ($this->page->addHeader('Upcoming Strava Routes'))
			{
			}
		}
	}
