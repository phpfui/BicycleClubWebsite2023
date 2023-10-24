<?php

namespace App\WWW\Rides;

class Statistics extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	private \App\View\Rides $view;

	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		parent::__construct($controller);
		$this->view = new \App\View\Rides($this->page);
		}

	public function ride(int $year = 0) : void
		{
		if (! $year)
			{
			$year = \App\Tools\Date::format('Y');
			}

		if ($this->page->addHeader('Ride Statistics'))
			{
			$oldest = \App\Table\Ride::getOldest();
			$earliest = (int)\App\Tools\Date::formatString('Y', $oldest['rideDate'] ?? \App\Tools\Date::todayString());
			$subnav = new \App\UI\YearSubNav('/Rides/Statistics/ride', $year, $earliest);
			$this->page->addPageContent($subnav);
			$this->page->addPageContent($this->view->stats($year));
			}
		}

	public function riders() : void
		{
		if ($this->page->addHeader('Rider Statistics'))
			{
			$view = new \App\View\Ride\Statistics($this->page);
			$this->page->addPageContent($view->download());
			}
		}
	}
