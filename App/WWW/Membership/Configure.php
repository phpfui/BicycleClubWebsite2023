<?php

namespace App\WWW\Membership;

class Configure extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	private readonly \App\View\Membership $membershipView;

	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		parent::__construct($controller);
		$this->membershipView = new \App\View\Membership($this->page);
		}

	public function configure() : void
		{
		if ($this->page->addHeader('Membership Configuration'))
			{
			$this->page->addPageContent($this->membershipView->configure());
			}
		}

	public function csv() : void
		{
		$columns = null;

		if ($this->page->addHeader('Download CSV'))
			{
			if (isset($_POST['type']))
				{
				$membershipModel = new \App\Model\Membership();
				$csvWriter = new \App\Tools\CSV\FileWriter('members.csv');
				$membershipModel->export($csvWriter, $_POST['start'], $_POST['end'], $_POST['type']);
				}
			else
				{
				$this->page->addPageContent($this->membershipView->csvOptions());
				}
			}
		}

	public function dues() : void
		{
		if ($this->page->addHeader('Membership Dues'))
			{
			$duesView = new \App\View\Membership\Dues($this->page);

			$this->page->addPageContent($duesView->getForm());
			}
		}

	public function emails(string $email = '') : void
		{
		$emails = [
			'newsletter' => 'Newsletter',
			'Waiver' => 'Waiver Accepted',
			'newPasswordEmail' => 'New Password',
		];

		if (isset($emails[$email]))
			{
			if ($this->page->addHeader($emails[$email] . ' Email'))
				{
				$this->page->addPageContent(new \App\View\Email\Settings($this->page, $email, new \App\Model\Email\Membership()));
				}
			}
		else
			{
			if ($this->page->addHeader('Membership Emails'))
				{
				$landingPage = new \App\UI\LandingPage($this->page);

				foreach ($emails as $link => $header)
					{
					$landingPage->addLink("/Membership/emails/{$link}", "{$header} Email");
					}
				$landingPage->addLink('/Membership/notifications', 'Membership Notifications');
				$landingPage->sort();
				$this->page->addPageContent($landingPage);
				}
			}
		}

	public function notifications(int $id = -1, string $test = '') : void
		{
		if ($this->page->addHeader('Membership Notifications'))
			{
			$view = new \App\View\Membership\Notices($this->page);

			if (-1 != $id)
				{
				$notice = new \App\Record\MemberNotice($id);

				if ('test' === $test)
					{
					$email = new \App\Model\Email\Notice($notice, new \App\Model\Email\Member());
					$email->addToMember(\App\Model\Session::getSignedInMember());
					$email->send();
					\App\Model\Session::setFlash('success', 'Check your inbox for a test email.');
					$this->page->redirect('/Membership/notifications/' . $id);
					}
				else
					{
					$this->page->addPageContent($view->edit($notice));
					}
				}
			else
				{
				$this->page->addPageContent(new \PHPFUI\Button('Add Notification', '/Membership/notifications/0'));
				$this->page->addPageContent($view->list());
				}
			}
		}

	public function qrCodes() : void
		{
		if ($this->page->addHeader('Membership QR Codes'))
			{
			$view = new \App\View\QRCodes($this->page);
			$this->page->addPageContent($view->membership());
			}
		}

	public function rosterReport() : void
		{
		if ($this->page->addHeader('Roster Report'))
			{
			$view = new \App\View\Member\Roster($this->page);
			$this->page->addPageContent($view->report());
			}
		}
	}
