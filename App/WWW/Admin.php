<?php

namespace App\WWW;

class Admin extends \App\Common\WWW\Admin
	{
	public function bikeShopAreas() : void
		{
		if ($this->page->addHeader('Bike Shop Areas'))
			{
			$this->page->addPageContent(new \App\View\Admin\BikeShopAreas($this->page));
			}
		}

	public function bikeShopEdit(\App\Record\BikeShop $bikeShop = new \App\Record\BikeShop()) : void
		{
		if ($this->page->addHeader('Bike Shop Edit'))
			{
			$view = new \App\View\Admin\BikeShop($this->page);
			$this->page->addPageContent($view->edit($bikeShop));
			}
		}

	public function bikeShopList() : void
		{
		if ($this->page->addHeader('Bike Shop Maintenance'))
			{
			$view = new \App\View\Admin\BikeShop($this->page);
			$this->page->addPageContent($view->list());
			}
		}

	public function board() : void
		{
		if ($this->page->addHeader('Board Members'))
			{
			$view = new \App\View\Admin\Board($this->page);
			$this->page->addPageContent($view->editView());
			}
		}

	public function boardMember(\App\Record\BoardMember $member = new \App\Record\BoardMember()) : void
		{
		if ($this->page->addHeader('Edit Board Member'))
			{
			if ($member->loaded())
				{
				$view = new \App\View\Admin\Board($this->page);
				$this->page->addPageContent($view->editMember($member));
				}
			else
				{
				$this->page->addPageContent(new \PHPFUI\SubHeader('Member Not Found'));
				}
			}
		}

	public function roles() : void
		{
		if ($this->page->addHeader('Role Assignments'))
			{
			$assignmentView = new \App\View\Member\Assign($this->page);
			$this->page->addPageContent($assignmentView->getForm());
			}
		}
	}
