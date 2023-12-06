<?php

namespace App\WWW\Membership;

class Maintenance extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	private readonly \App\View\Membership $membershipView;

	private readonly \App\Table\Member $memberTable;

	private readonly \App\View\Member $memberView;

	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		parent::__construct($controller);
		$this->membershipView = new \App\View\Membership($this->page);
		$this->memberView = new \App\View\Member($this->page);
		$this->memberTable = new \App\Table\Member();
		}

	public function addMembership() : void
		{
		if ($this->page->addHeader('Add New Membership'))
			{
			$this->page->addPageContent($this->memberView->editMembership(new \App\Record\Membership(), new \App\Record\Member()));
			}
		}

	public function audit(string $type = '') : void
		{
		if ($this->page->addHeader('Membership Audit'))
			{
			switch ($type)
				{
				case 'noPayments':

					$members = $this->memberTable->noPayments();
					$this->page->addSubHeader('No Payments');
					$this->page->addPageContent($this->memberView->show($members, 'Everyone has paid'));

					break;


				case 'noMembers':

					$members = $this->memberTable->noMembers();
					$this->page->addSubHeader('No Members');
					$this->page->addPageContent($this->memberView->show($members, 'All memberships have members'));

					break;


				case 'badExpirations':

					$members = $this->memberTable->badExpirations();
					$this->page->addSubHeader('Bad Expiration Dates');
					$this->page->addPageContent($this->memberView->show($members, 'No members with bad expirations'));

					break;


				case 'missingNames':

					$members = $this->memberTable->missingNames();
					$this->page->addSubHeader('Missing Names');
					$this->page->addPageContent($this->memberView->show($members, 'All members have names'));

					break;


				case 'noPermissions':

					$members = $this->memberTable->noPermissions();
					$this->page->addSubHeader('No Permissions');
					$this->page->addPageContent($this->memberView->show($members, 'All members have permissions'));

					break;


				default:

					$landing = new \App\UI\LandingPage($this->page);
					$landing->addLink('/Membership/Maintenance/audit/noPayments', 'Memberships with No Payments');
					$landing->addLink('/Membership/Maintenance/audit/noMembers', 'Memberships with No Members');
					$landing->addLink('/Membership/Maintenance/audit/badExpirations', 'Memberships with Bad Expirations (payment, but expiration not updated)');
					$landing->addLink('/Membership/Maintenance/audit/missingNames', 'Memberships with Missing Names');
					$landing->addLink('/Membership/Maintenance/audit/noPermissions', 'Memberships with No Permissions');
					$this->page->addPageContent($landing);

				}
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
			$members = $this->memberTable->getPendingMembers(\App\Tools\Date::todayString());
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
			$this->page->addPageContent($this->membershipView->updateSubscriptions());
			}
		}
	}
