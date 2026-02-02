<?php

namespace App\WWW\Membership;

class Maintenance extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	private readonly \App\View\Member $memberView;

	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		parent::__construct($controller);
		$this->memberView = new \App\View\Member($this->page);
		}

	public function addMembership() : void
		{
		if ($this->page->addHeader('Add New Membership'))
			{
			$this->page->addPageContent($this->memberView->editMembership(new \App\Record\Membership(), new \App\Record\Member()));
			}
		}

	public function combineMembers() : void
		{
		if ($this->page->addHeader('Combine Members'))
			{
			$view = new \App\View\Member\Combine($this->page);
			$this->page->addPageContent($view->combine());
			}
		}

	public function combineMemberships() : void
		{
		if ($this->page->addHeader('Combine Memberships'))
			{
			$view = new \App\View\Membership\Combine($this->page);
			$this->page->addPageContent($view->combine());
			}
		}

	public function confirm() : void
		{
		if ($this->page->addHeader('Membership Confirm'))
			{
			$memberTable = new \App\Table\Member();
			$members = $memberTable->getPendingMembers(\App\Tools\Date::todayString());
			$this->page->addPageContent($this->memberView->show($members, 'No pending members found'));
			}
		}

	public function edit(\App\Record\Member $member = new \App\Record\Member()) : void
		{
		if ($this->page->addHeader('Edit Member') && $member->loaded())
			{
			$this->page->addPageContent($this->memberView->edit($member));
			}
		}

	public function extend() : void
		{
		if ($this->page->addHeader('Extend Memberships'))
			{
			$this->page->addPageContent(new \App\View\Membership\Extend($this->page));
			}
		}

	public function landingPage() : void
		{
		$this->page->landingPage('Membership Maintenance');
		}

	public function subscriptions() : void
		{
		if ($this->page->addHeader('Update Subscriptions'))
			{
			$membershipView = new \App\View\Membership($this->page);
			$this->page->addPageContent($membershipView->updateSubscriptions());
			}
		}
	}
