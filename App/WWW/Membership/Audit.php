<?php

namespace App\WWW\Membership;

class Audit extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	private readonly \App\Table\Member $memberTable;

	private readonly \App\View\Member $memberView;

	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		parent::__construct($controller);
		$this->memberView = new \App\View\Member($this->page);
		$this->memberTable = new \App\Table\Member();
		}

	public function abandoned() : void
		{
		if ($this->page->addHeader('Abandoned Member Signups'))
			{
			$members = $this->memberTable->abandoned();
			$this->page->addPageContent($this->memberView->show($members, 'No Abandoned Members'));
			}
		}

	public function badExpirations() : void
		{
		if ($this->page->addHeader('Memberships with Bad Expirations'))
			{
			$members = $this->memberTable->badExpirations();
			$this->page->addPageContent($this->memberView->show($members, 'No members with bad expirations'));
			}
		}

	public function missingNames() : void
		{
		if ($this->page->addHeader('Memberships with Missing Names'))
			{
			$members = $this->memberTable->missingNames();
			$this->page->addPageContent($this->memberView->show($members, 'All members have names'));
			}
		}

	public function noMembers() : void
		{
		if ($this->page->addHeader('Memberships with No Members'))
			{
			$members = $this->memberTable->noMembers();
			$this->page->addPageContent($this->memberView->show($members, 'All memberships have members'));
			}
		}

	public function noPayments() : void
		{
		if ($this->page->addHeader('Memberships with No Payments'))
			{
			$members = $this->memberTable->noPayments();
			$this->page->addPageContent($this->memberView->show($members, 'Everyone has paid'));
			}
		}

	public function noPermissions() : void
		{
		if ($this->page->addHeader('Memberships with No Permissions'))
			{
			$members = $this->memberTable->noPermissions();
			$this->page->addPageContent($this->memberView->show($members, 'All members have permissions'));
			}
		}
	}
