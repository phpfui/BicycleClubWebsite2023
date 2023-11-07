<?php

namespace App\WWW;

class Membership extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	private readonly \App\View\Membership $membershipView;

	private readonly \App\Table\Member $memberTable;

	private readonly \App\View\Member $memberView;

	private readonly \App\Table\Setting $settingTable;

	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		parent::__construct($controller);
		$this->membershipView = new \App\View\Membership($this->page);
		$this->memberView = new \App\View\Member($this->page);
		$this->memberTable = new \App\Table\Member();
		$this->settingTable = new \App\Table\Setting();
		}

	public function card(string $command = 'print') : void
		{
		if ($this->page->isAuthorized('Membership Card'))
			{
			$command = \strtolower($command);
			$card = new \App\Report\MembershipCard();

			if ('all' == $command)
				{
				$members = $this->memberTable->membersInMembership(\App\Model\Session::signedInMembershipId());

				foreach ($members as $member)
					{
					$card->generate($member);
					}
				$this->page->done();
				}
			elseif ('my' == $command)
				{
				$card->generate(\App\Model\Session::signedInMemberRecord());
				$this->page->done();
				}
			elseif ('screen' == $command)
				{
				$member = $this->memberTable->getMembership(\App\Model\Session::signedInMemberId());

				$container = new \PHPFUI\HTML5Element('span');
				$container->addClass('text-center');
				$container->add(new \PHPFUI\Header('Membership Card', 1));
				$container->add(new \PHPFUI\Header('This is to confirm that', 5));
				$container->add(new \PHPFUI\Header($member['firstName'] . ' ' . $member['lastName'], 2));
				$container->add(new \PHPFUI\Header('is a member in good standing of the', 5));
				$file = new \App\Model\ImageFiles();
				$container->add($file->getImg($this->settingTable->value('clubLogo')));
				$container->add(new \PHPFUI\SubHeader($this->settingTable->value('clubName')));
				$expires = \App\Tools\Date::formatString('F Y', $member['expires']);
				$container->add(new \PHPFUI\Header("through {$expires}", 5));

				$this->page->addPageContent($container);
				}
			elseif ('print' == $command)
				{
				$this->page->addHeader('Print Membership Cards', 'Membership');
				$members = $this->memberTable->membersInMembership(\App\Model\Session::signedInMembershipId());
				$column = new \PHPFUI\Cell(12, 6, 4);
				$url = $this->page->getBaseURL();

				$buttonRow = new \PHPFUI\GridX();
				$button = new \PHPFUI\Button('Online', "{$url}/Screen");
				$buttonRow->add($button);
				$column->add($buttonRow);

				$i = 0;

				foreach ($members as $member)
					{
					$buttonRow = new \PHPFUI\GridX();
					$button = new \PHPFUI\Button('Card for ' . $member['firstName'] . ' ' . $member['lastName'], $url . "/{$i}");
					$buttonRow->add($button);
					$column->add($buttonRow);
					$i += 1;
					}

				if (\count($members) > 1)
					{
					$buttonRow = new \PHPFUI\GridX();
					$button = new \PHPFUI\Button('Cards for all of the above', $url . '/all');
					$buttonRow->add($button);
					$column->add($buttonRow);
					}
				$this->page->addPageContent($column);
				}
			else
				{
				$members = $this->memberTable->membersInMembership(\App\Model\Session::signedInMembershipId());
				$index = (int)$command;

				while (--$index > 0)
					{
					$members->next();
					}

				$card->generate($members->current());
				$this->page->done();
				}
			}
		}

	public function confirmEmail(string $emailHash = '') : void
		{
		if ($this->page->addHeader('Confirm Additional Email'))
			{
			$memberModel = new \App\Model\Member();

			if ($memberModel->confirmEmail($emailHash))
				{
				$alert = new \App\UI\Alert('Your email has been confirmed.');
				}
			else
				{
				$alert = new \App\UI\Alert('Invalid email');
				$alert->addClass('alert');
				}
			$this->page->addPageContent($alert);
			$this->page->addPageContent(new \PHPFUI\Button('My Info', '/Membership/myInfo'));
			}
		}

	public function crop(\App\Record\Member $member = new \App\Record\Member()) : void
		{
		if (! $member->loaded())
			{
			$this->page->addPageContent(new \PHPFUI\SubHeader('Member not found'));

			return;
			}

		$memberModel = new \App\Model\Member();

		if ($this->page->addHeader('Crop My Photo') && ($memberModel->memberInMembership($member) || $this->page->isAuthorized('Crop Member Photo')))
			{
			$this->page->addPageContent($this->memberView->crop($member));
			}
		}

	public function edit(\App\Record\Member $member = new \App\Record\Member()) : void
		{
		if ($this->page->addHeader('Edit Member') && $member->loaded())
			{
			$this->page->addPageContent($this->memberView->edit($member));
			}
		}

	public function editMembership(\App\Record\Membership $membership = new \App\Record\Membership()) : void
		{
		if ($membership->loaded() && $this->page->addHeader('Edit Membership'))
			{
			$this->page->addPageContent($this->memberView->editMembership($membership));
			}
		elseif ($this->page->addHeader('Add New Membership'))
			{
			$this->page->addPageContent($this->memberView->editMembership($membership, new \App\Record\Member()));
			}
		}

	public function email(\App\Record\Member $member = new \App\Record\Member()) : void
		{
		if ($this->page->addHeader('Email Member'))
			{
			if ($member->loaded() && $member->email)
				{
				$this->page->addPageContent(new \App\View\Email\Member($this->page, $member));
				}
			else
				{
				$this->page->addPageContent(new \PHPFUI\SubHeader('Member not found'));
				}
			}
		}

	public function emailAll() : void
		{
		if ($this->page->addHeader('Email All Members'))
			{
			$this->page->addPageContent(new \App\View\Email\Members($this->page));
			}
		}

	public function find() : void
		{
		if ($this->page->addHeader('Find Members'))
			{
			$this->page->addPageContent(new \App\View\Member\Search($this->page));
			}
		}

	public function forgotPassword() : void
		{
		$this->page->setPublic();
		$email = \App\Model\Session::getFlash('forgotPassword');
		$this->page->addHeader('Reset My Password');
		$view = new \App\View\Member\ResetPassword($this->page);
		$this->page->addPageContent($view->getEmail($email ?? ''));
		}

	public function image(\App\Record\Member $member = new \App\Record\Member(), int $cropped = 0) : void
		{
		if ($this->page->isAuthorized('Member Photo'))
			{
			if ($member->loaded())
				{
				$fileModel = new \App\Model\ProfileImages($member->toArray());
				$path = $cropped ? $fileModel->getCropPath() : $fileModel->getPhotoFilePath();

				if (false !== ($data = @\file_get_contents($path)))
					{
					$extension = \str_replace('.', '', $member->extension);

					if ('jpg' == $extension)
						{
						$extension = 'jpeg';
						}
					\header('Content-type: image/' . $extension);
					echo $data;
					}
				}
			}

		exit;
		}

	public function minor() : void
		{
		$report = new \App\Report\MemberWaiver();
		$report->generateMinorRelease();
		$report->Output('MinorRelease.pdf', 'I');
		$this->page->done();
		}

	public function mom(int $year = 0, \App\Record\MemberOfMonth $memberOfMonth = new \App\Record\MemberOfMonth()) : void
		{
		if ($this->page->addHeader('Member Of The Month'))
			{
			$MOMView = new \App\View\Member\OfMonth($this->page);
			$this->page->addPageContent($MOMView->navigate('/Membership/mom', $year, $memberOfMonth));
			}
		}

	public function momEdit(\App\Record\MemberOfMonth $memberOfMonth = new \App\Record\MemberOfMonth()) : void
		{
		$title = $memberOfMonth->loaded() ? 'Edit' : 'Add';

		if ($this->page->addHeader($title . ' Member Of The Month'))
			{
			$MOMView = new \App\View\Member\OfMonth($this->page);
			$this->page->addPageContent($MOMView->edit($memberOfMonth));
			}
		}

	public function myInfo() : void
		{
		$this->page->setRenewing();

		if ($this->page->addHeader('My Info'))
			{
			$this->page->addPageContent($this->memberView->edit(\App\Model\Session::signedInMemberRecord()));
			}
		}

	public function myNotifications() : void
		{
		if ($this->page->addHeader('My Notifications'))
			{
			$this->page->addPageContent($this->memberView->notifications(\App\Model\Session::signedInMemberRecord()));
			}
		}

	public function newMembers() : void
		{
		if ($this->page->addHeader('New Members'))
			{
			$today = \App\Tools\Date::today();
			$members = $this->memberTable->getNewMembers(\App\Tools\Date::toString($today - 180), \App\Tools\Date::todayString());
			$tabs = new \PHPFUI\Tabs();
			$tabs->addTab('Editable', $this->memberView->list($members), true);
			$tabs->addTab('Name - Town', $this->memberView->htmlList($members));
			$this->page->addPageContent($tabs);
			}
		}

	public function newsletter(\App\Record\Member $member = new \App\Record\Member()) : void
		{
		if ($this->page->addHeader('Email Single Newsletter'))
			{
			$newsletterTable = new \App\Table\Newsletter();
			$email = new \App\Tools\EMail();
			$newsletter = $newsletterTable->getLatest();
			$fileModel = new \App\Model\NewsletterFiles($newsletter);

			if ($member->loaded())
				{
				$abbrev = $this->settingTable->value('clubAbbrev');
				$name = $this->settingTable->value('newsletterName');
				$date = \App\Tools\Date::formatString('F Y', $newsletter->date);
				$email->setBody($this->settingTable->value('newsletter'));
				$title = "{$date} {$abbrev} {$name}";
				$email->setSubject($title);
				$email->setFromMember(\App\Model\Session::getSignedInMember());
				$email->addAttachment($fileModel->get($newsletter->newsletterId . '.pdf'), $fileModel->getPrettyFileName());
				$email->addToMember($member->toArray());
				$email->send();
				$this->page->addPageContent("The {$title} was sent to {$member->fullName()}");
				}
			else
				{
				$this->page->addPageContent(new \PHPFUI\SubHeader('Member not found'));
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

	public function password(\App\Record\Member $member = new \App\Record\Member()) : void
		{
		$this->page->setRenewing();

		if ($this->page->addHeader('Change My Password'))
			{
			if (! $member->loaded() || ! $this->page->isAuthorized('Reset Any Password'))
				{
				$member = \App\Model\Session::signedInMemberRecord();
				}
			$this->page->addPageContent($this->memberView->password($member));
			}
		}

	public function passwordNew(\App\Record\Member $member = new \App\Record\Member(), string $hash = '') : void
		{
		$this->page->setPublic();

		if ($member->loaded() && $member->passwordReset == $hash && $member->passwordResetExpires > \date('Y-m-d H:i:s'))
			{
			$this->page->addHeader('Reset My Password');
			$this->page->addPageContent(new \PHPFUI\SubHeader('Enter a new password'));
			$this->page->addPageContent($this->memberView->passwordNew($member));
			}
		else
			{
			$this->page->addPageContent(new \PHPFUI\Header('Link has expired'));
			}
		}

	public function passwordReset(\App\Record\Member $member = new \App\Record\Member(), int $text = 0) : void
		{
		if ($this->page->addHeader('Reset Password EMail'))
			{
			if ($member->loaded())
				{
				$memberModel = new \App\Model\Member();
				$memberModel->resetPassword($member->email, (bool)$text);
				$this->page->addPageContent("A password reset email was sent to {$member->fullName()}");
				}
			else
				{
				$this->page->addPageContent(new \PHPFUI\SubHeader('Member not found'));
				}
			}
		}

	public function permissionEdit(\App\Record\Member $member = new \App\Record\Member()) : void
		{
		if ($this->page->addHeader('Edit Member Permissions'))
			{
			if ($member->loaded())
				{
				$this->page->addSubHeader('for ' . $member->fullName());
				$view = new \App\View\Permissions($this->page);
				$this->page->addPageContent($view->editMember($member));
				}
			else
				{
				$this->page->addPageContent(new \PHPFUI\SubHeader('Member not found'));
				}
			}
		}

	public function recent() : void
		{
		if ($this->page->addHeader('Recent Sign Ins'))
			{
			$memberTable = new \App\Table\Member();
			$memberTable->addJoin('membership', 'membershipId');
			$memberTable->addOrderBy('lastLogin', 'DESC');
			$memberTable->getWhereCondition()->and('membership.expires', \App\Tools\Date::todayString(), new \PHPFUI\ORM\Operator\GreaterThanEqual());
			$memberTable->setLimit(50);
			$view = new \App\UI\ContinuousScrollTable($this->page, $memberTable);
			$headers = ['firstName', 'lastName', 'lastLogin' => 'Last Login', 'joined' => 'Joined'];
			$view->setSearchColumns(['firstName', 'lastName', ])->setHeaders($headers);

			$this->page->addPageContent($view);
			}
		}

	public function renew() : void
		{
		$this->page->setRenewing();

		if ($this->page->addHeader('Renew My Membership'))
			{
			if (\App\Model\Session::hasExpired())
				{
				$this->page->addPageContent(new \PHPFUI\Header('Your membership has lapsed. Renew NOW!', 3));
				}
			$renewView = new \App\View\Membership\Renew($this->page, \App\Model\Session::signedInMembershipRecord(), $this->memberView);
			$this->page->addPageContent($renewView->renew());
			}
		}

	public function renewCheckout() : void
		{
		$this->page->setRenewing();

		if (! \App\Model\Session::signedInMembershipId())
			{
			$this->page->redirect('/Home');

			return;
			}

		if ($this->page->addHeader('Pay With PayPal'))
			{
			$renewView = new \App\View\Membership\Renew($this->page, \App\Model\Session::signedInMembershipRecord(), $this->memberView);
			$this->page->addPageContent($renewView->checkout(\App\Model\Session::signedInMemberRecord()));
			}
		}

	public function roster(string $field = '', string $select = '', int $offset = 0) : void
		{
		if ($this->page->addHeader('Club Roster'))
			{
			$view = new \App\View\Member\Roster($this->page, '/Membership/roster');
			$this->page->addPageContent($view->show($field, $select, $offset));
			}
		}

	public function show(int $memberId = 0) : void
		{
		if ($this->page->addHeader('Show Member') && $memberId)
			{
			$view = new \App\View\Member($this->page);
			$this->page->addPageContent($view->show($this->memberTable->getMembershipCursor($memberId)));
			}
		}

	public function statistics() : void
		{
		if ($this->page->addHeader('Club Statistics'))
			{
			$this->page->addPageContent(new \App\View\Statistics(new \App\Model\Statistics()));
			}
		}

//	public function subscription() : void
//		{
//		$this->setRenewing();
//
//		if ($this->addHeader('Manage My Subscription'))
//			{
//			$view = new \App\View\Subscription($this, \App\Model\Session::signedInMemberId());
//			$this->addPageContent($view->subscribe());
//			}
//		}

	public function subscriptions() : void
		{
		if ($this->page->addHeader('Update Subscriptions'))
			{
			$this->page->addPageContent($this->membershipView->updateSubscriptions());
			}
		}

	public function text(\App\Record\Member $member = new \App\Record\Member()) : void
		{
		if ($this->page->addHeader('Text Member') && $member->loaded())
			{
			$view = new \App\View\Text($this->page);
			$this->page->addPageContent($view->textMember($member));
			}
		}

	public function thumbnail(\App\Record\Member $member = new \App\Record\Member()) : void
		{
		if ($this->page->isAuthorized('Member Photo Thumbnail'))
			{
			if ($member->loaded())
				{
				$fileModel = new \App\Model\ProfileImages($member->toArray());

				if (false !== ($data = @\file_get_contents($fileModel->getThumbFilePath())))
					{
					$extension = \str_replace('.', '', $member->extension);

					if ('jpg' == $extension)
						{
						$extension = 'jpeg';
						}
					\header('Content-type: image/' . $extension);
					echo $data;
					}
				}
			}

		exit;
		}

	public function unsubscribe(\App\Record\Member $member = new \App\Record\Member(), string $email = '') : void
		{
		$this->page->setPublic();
		$unsubscribe = new \App\View\Unsubscribe($this->page, 'Membership emails', $member, $email);
		$this->page->addPageContent($unsubscribe);
		}

	public function verify(\App\Record\Member $member = new \App\Record\Member(), int $code = 0) : void
		{
		if ($member->loaded())
			{
			$joinView = new \App\View\Membership\Join($this->page);
			$this->page->setPublic();
			$this->page->addPageContent($joinView->process($member, $code));
			}
		else
			{
			$this->page->redirect('/');
			}
		}

	public function verifyEmail(string $email = '') : void
		{
		if ($this->page->addHeader('Verify Additional Email'))
			{
			$memberModel = new \App\Model\Member();

			if ($memberModel->verifyEmail($email))
				{
				$alert = new \App\UI\Alert('Please check your email and click on the link to verify this email address.');
				}
			else
				{
				$alert = new \App\UI\Alert('We were unable to find that email address');
				$alert->addClass('alert');
				}
			$this->page->addPageContent($alert);
			$this->page->addPageContent(new \PHPFUI\Button('My Info', '/Membership/myInfo'));
			}
		}

	public function waiver(\App\Record\Member $member = new \App\Record\Member()) : void
		{
		if ($member->empty() || ($member->membershipId != \App\Model\Session::signedInMembershipId() && ! $this->page->isAuthorized('View Waiver')))
			{
			return;
			}
		$waiverModel = new \App\Model\MemberWaiver($member);

		if ($member->memberId == \App\Model\Session::signedInMemberId() && $this->page->isAuthorized('Waiver Exempt'))
			{
			$waiverModel->generate();
			}
		$waiverModel->downloadGenerated();
		}
	}
