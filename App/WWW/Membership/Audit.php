<?php

namespace App\WWW\Membership;

class Audit extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	private readonly \App\View\Member $memberView;

	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		parent::__construct($controller);
		$this->memberView = new \App\View\Member($this->page);
		}

	public function abandoned() : void
		{
		if ($this->page->addHeader('Abandoned Member Signups'))
			{
			$memberTable = new \App\Table\Member()->abandoned();
			$this->page->addPageContent($this->memberView->showWithDelete($memberTable, 'No Abandoned Members'));
			}
		}

	public function badExpirations() : void
		{
		if ($this->page->addHeader('Memberships with Bad Expirations'))
			{
			$membershipTable = new \App\Table\Membership()->badExpirations();
			$this->page->addPageContent($this->memberView->showWithDelete($membershipTable, 'No members with bad expirations'));
			}
		}

	public function missingNames() : void
		{
		if ($this->page->addHeader('Memberships with Missing Names'))
			{
			$memberTable = new \App\Table\Member()->missingNames();
			$this->page->addPageContent($this->memberView->showWithDelete($memberTable, 'All members have names'));
			}
		}

	public function noMembers() : void
		{
		if ($this->page->addHeader('Memberships with No Members'))
			{
			$membershipTable = new \App\Table\Membership()->noMembers();
			$this->page->addPageContent($this->memberView->showWithDelete($membershipTable, 'All memberships have members'));
			}
		}

	public function noPayments() : void
		{
		if ($this->page->addHeader('Memberships with No Payments'))
			{
			$membershipTable = new \App\Table\Membership()->noPayments();
			$this->page->addPageContent($this->memberView->showWithDelete($membershipTable, 'Everyone has paid'));
			}
		}

	public function noPermissions() : void
		{
		if ($this->page->addHeader('Memberships with No Permissions'))
			{
			$memberTable = new \App\Table\Member()->noPermissions();
			$this->page->addPageContent($this->memberView->showWithDelete($memberTable, 'All members have permissions'));
			}
		}
	}
