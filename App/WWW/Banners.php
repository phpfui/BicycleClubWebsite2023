<?php

namespace App\WWW;

class Banners extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	private readonly \App\Table\Banner $bannerTable;

	private readonly \App\View\Banner $view;

	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		parent::__construct($controller);
		$this->bannerTable = new \App\Table\Banner();
		$this->view = new \App\View\Banner($this->page);
		}

	public function active() : void
		{
		if ($this->page->addHeader('Active Banners'))
			{
			$today = \App\Tools\Date::todayString();
			$condition = new \PHPFUI\ORM\Condition('pending', 0);
			$condition->and(new \PHPFUI\ORM\Condition('endDate', $today, new \PHPFUI\ORM\Operator\GreaterThanEqual()));
			$condition->and(new \PHPFUI\ORM\Condition('startDate', $today, new \PHPFUI\ORM\Operator\LessThanEqual()));
			$this->bannerTable->setWhere($condition);
			$this->page->addPageContent($this->view->listBanners($this->bannerTable));
			}
		}

	public function addBanner() : void
		{
		if ($this->page->addHeader('Add Banner'))
			{
			$this->page->addPageContent($this->view->edit(new \App\Record\Banner()));
			}
		}

	public function allBanners() : void
		{
		if ($this->page->addHeader('All Banners'))
			{
			$this->page->addPageContent($this->view->listBanners($this->bannerTable));
			}
		}

	public function current() : void
		{
		if ($this->page->addHeader('Current Banners'))
			{
			$today = \App\Tools\Date::todayString();
			$condition = new \PHPFUI\ORM\Condition('endDate', $today, new \PHPFUI\ORM\Operator\GreaterThanEqual());
			$this->bannerTable->setWhere($condition);
			$this->page->addPageContent($this->view->listBanners($this->bannerTable));
			}
		}

	public function edit(\App\Record\Banner $banner = new \App\Record\Banner()) : void
		{
		if ($this->page->addHeader('Edit Banner'))
			{
			$this->page->addPageContent($this->view->edit($banner));
			}
		}

	public function past(int $year = 0) : void
		{
		if ($this->page->addHeader('Past Banners'))
			{
			$this->page->addPageContent($this->view->listBannersByYear($this->bannerTable, 'past', $year));
			}
		}

	public function pending() : void
		{
		if ($this->page->addHeader('Pending Banners'))
			{
			$condition = new \PHPFUI\ORM\Condition('pending', 0, new \PHPFUI\ORM\Operator\NotEqual());
			$this->bannerTable->setWhere($condition);
			$this->page->addPageContent($this->view->listBanners($this->bannerTable));
			}
		}

	public function settings() : void
		{
		if ($this->page->addHeader('Banner Settings'))
			{
			$this->page->addPageContent($this->view->settings());
			}
		}

	public function test(\App\Record\Banner $banner = new \App\Record\Banner()) : void
		{
		if ($this->page->isAuthorized('Test Banner'))
			{
			$this->page->addPageContent($this->view->test($banner));
			}
		}
	}
