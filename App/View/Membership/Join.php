<?php

namespace App\View\Membership;

class Join
	{
	private ?\App\UI\Captcha $captcha = null;

	private readonly \App\Model\MembershipDues $duesModel;

	private string $forgotPassword = 'ForgotPassword';

	private readonly \App\Model\Member $memberModel;

	private readonly \App\View\Member $memberView;

	private readonly \App\Table\Setting $settingTable;

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->memberModel = new \App\Model\Member();
		$this->memberModel->setStoreItemIdType(\App\Model\Member::MEMBERSHIP_JOIN);
		$this->memberView = new \App\View\Member($page);
		$this->settingTable = new \App\Table\Setting();
		$this->duesModel = new \App\Model\MembershipDues();

		if ('127.0.0.1' != $_SERVER['SERVER_ADDR'] && '::1' != $_SERVER['SERVER_ADDR'])
			{
			$this->captcha = new \App\UI\Captcha($this->page);
			}
		}

	public function getEmail() : \PHPFUI\HTML5Element
		{
		$post = \App\Model\Session::getFlash('post');
		$member = new \App\Record\Member();

		if ($post)
			{
			$member->setFrom($post);
			}

		$container = $this->getHeader('Thanks for your interest in ' . $this->settingTable->value('clubName'));
		$form = new \PHPFUI\Form($this->page);

		$form->setAreYouSure(false);

		if (isset($_POST['submit']) && 'Join' == $_POST['submit'] && (! $this->captcha || $this->captcha->isValid()))
			{
			\App\Model\Session::setFlash('post', $_POST);
			$fullName = $_POST['firstName'] . ' ' . $_POST['lastName'];

			// is the password valid and matching?
			if ($_POST['password'] !== $_POST['confirm']) // @mago-expect lint:no-insecure-comparison
				{
				\App\Model\Session::setFlash('alert', "Passwords don't match");
				$this->page->redirect();

				return $container;
				}

			$passwordPolicy = new \App\Model\PasswordPolicy();

			if ($errors = $passwordPolicy->validate($_POST['password']))
				{
				\App\Model\Session::setFlash('alert', $errors);
				$this->page->redirect();

				return $container;
				}

			// is it a bot entering the same name twice?
			if (\strtolower($_POST['lastName']) == \strtolower($_POST['firstName']))
				{
				\App\Model\Session::setFlash('alert', 'Please enter different names');
				$this->page->redirect();

				return $container;
				}

			$parts = \explode(' ', $fullName);
			$error = false;

			foreach ($parts as $part)
				{
				if (\filter_var($part, FILTER_VALIDATE_URL))
					{
					$error = true;

					break;
					}
				}

			if (! $error)
				{
				if (empty($_POST['email']) || ! \filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
					{
					\App\Model\Session::setFlash('alert', 'Please enter a valid email');
					$this->page->redirect();

					return $container;
					}
				elseif ($this->memberModel->emailIsUnused($_POST['email']))
					{
					$id = $this->memberModel->add($_POST);
					$member->read($id);

					if (! empty($_GET['a']))
						{
						$member = new \App\Record\Member($id);
						$today = \App\Tools\Date::today();
						$year = (int)\App\Tools\Date::year($today);

						if (1 != \App\Tools\Date::month($today))
							{
							++$year;
							}
						$membership = $member->membership;
						$membership->affiliation = $_GET['a'];
						$membership->expires = \App\Tools\Date::makeString($year, 1, 30);
						$membership->update();
						}
					$this->page->redirect("/Membership/verify/{$id}");
					$this->page->done();

					return $container;
					}

				$here = new \PHPFUI\Link('/Home', 'here', false);
				\App\Model\Session::setFlash('alert', "The email address is already in use. You can sign in or reset your password {$here} if you forgot it, even if you have not officially joined yet.");
				$_POST[$this->forgotPassword] = true;
				$this->page->redirect();

				return $container;
				}
			}

		if (! $this->page->isDone())
			{
			$container->add($this->page->getFlashMessages());
			$fieldSet = new \PHPFUI\FieldSet('Required Information');
			$email = new \PHPFUI\Input\Email('email', 'Your email', $member->email);
			$email->setRequired()->setToolTip('The club is run on email so we need your email address or you can\'t really participate.');
			$fieldSet->add($email);
			$firstName = new \PHPFUI\Input\Text('firstName', 'First Name', $member->firstName);
			$firstName->setRequired()->setToolTip('We want to know your name, so we know who is coming on the rides.');
			$fieldSet->add($firstName);
			$lastName = new \PHPFUI\Input\Text('lastName', 'Last Name', $member->firstName);
			$lastName->setRequired()->setToolTip('We want to know your name, so we know who is coming on the rides.');
			$fieldSet->add($lastName);

			$passwordPolicy = new \App\View\Admin\PasswordPolicy($this->page);
			$fieldSet->add($passwordPolicy->list());
			$current = $passwordPolicy->getValidatedPassword('password', 'Password', $member->password);
			$current->setRequired();
			$fieldSet->add($current);
			$confirm = $passwordPolicy->getValidatedPassword('confirm', 'Confirm Password', $member->password);
			$confirm->addAttribute('data-equalto', $current->getId());
			$confirm->addErrorMessage('Passwords must match.');
			$confirm->setRequired();
			$confirm->setToolTip('You must enter the same password twice to make sure it is correct');
			$fieldSet->add($confirm);

			if (empty($_GET['a']))
				{
				$questions = \json_decode($this->settingTable->value('NewMemberQuestions'), true);

				if (\is_array($questions))
					{
					$affiliation = new \PHPFUI\Input\Select('affiliation', 'How did you hear about us?');
					$affiliation->addOption('Please select', '');

					foreach ($questions as $question)
						{
						$affiliation->addOption($question);
						}
					$fieldSet->add($affiliation);
					}
				}
			$form->add($fieldSet);
			$form->add($this->captcha);

			$buttonGroup = new \PHPFUI\ButtonGroup();
			$joinButton = new \PHPFUI\Submit('Join');
			$joinButton->addClass('success');
			$buttonGroup->addButton($joinButton);

			if ($post[$this->forgotPassword] ?? false)
				{
				$signIn = new \PHPFUI\Button('Sign In', '/Home');
				$buttonGroup->addButton($signIn);
				$forgot = new \PHPFUI\Submit('Forgot My Password', $this->forgotPassword);
				$forgot->addClass('alert');
				$buttonGroup->addButton($forgot);
				}
			$form->add($buttonGroup);

			$container->add($form);
			}

		return $container;
		}

	public function process(\App\Record\Member $member, int $code = 0, string $discountCode = '') : string | \PHPFUI\HTML5Element
		{
		$website = $this->page->getSchemeHost();

		if ($member->verifiedEmail <= 1)
			{
			$this->page->setPublic();
			}

		$verifyCode = $this->memberModel->getVerifyCode($member->password);

		if (\App\Model\Session::checkCSRF() && isset($_POST['submit']))
			{
			switch ($_POST['submit'])
				{
				case 'Continue':
				case 'Confirm and Pay':
					if ($verifyCode == $code || $member->verifiedEmail >= 2)
						{
						$member->verifiedEmail += 1;
						}
					$_POST['membershipId'] = $member->membershipId;
					$_POST['memberId'] = $member->memberId;
					$_POST['verifiedEmail'] = $member->verifiedEmail;
					unset($_POST['expires'], $_POST['subscriptionId'], $_POST['lastRenewed'], $_POST['renews']);
					$_POST['pending'] = 1;
					$this->memberModel->saveFromPost($_POST, false);
					$this->page->redirect();

					break;

				case 'Join':
					if (! empty($_POST['bikeShopId']))
						{
						$membership = $member->membership;
						$bikeShop = new \App\Record\BikeShop($_POST['bikeShopId']);
						$membership->affiliation = $bikeShop->fullName();
						$membership->update();
						\App\Model\Session::unregisterMember();
						\App\Model\Session::registerMember($member);
						\App\Model\Session::setFlash('success', 'Welcome to the ' . $this->page->value('clubName'));

						$this->page->redirect('/Home');

						return '';
						}

					break;

				case 'Back':
					if ($member->verifiedEmail > 2)
						{
						$member->verifiedEmail -= 1;
						}
					$member->update();

					break;
				}
			}

		return match ($member->verifiedEmail) {
			0 => $this->explainLogin($member),
			1 => $this->confirmEmail($member, $code),
			2 => $this->getAddress($member),
			3 => $this->getContactInfo($member),
			4 => $this->getNotifications($member),
			5 => $this->getPrivacy($member),
			6 => $this->getMembers($member, $discountCode),
			7 => $this->getPayPal($member, $discountCode),
			default => '',
			};
		}

	private function confirmEmail(\App\Record\Member $member, int $code) : \PHPFUI\HTML5Element
		{
		$container = new \PHPFUI\HTML5Element('div');

		if ($member->password)
			{
			$verifyCode = $this->memberModel->getVerifyCode($member->password);


			if ($verifyCode == $code)
				{
				$member->verifiedEmail = 2;
				$member->update();
				$permissions = $this->page->getPermissions();
				$permissions->addPermissionToUser($member->memberId, 'Pending Member');
				// fake signing in the user so they can use the renew code
				$_SESSION['userPermissions'] = $permissions->getPermissionsForUser($member->memberId);
				\App\Model\Session::registerMember($member);
				$this->page->redirect();

				return $container;
				}
			}

		if (\App\Model\Session::checkCSRF())
			{
			if (isset($_POST['action']) && 'resendEmail' == $_POST['action'])
				{
				$this->memberModel->sendVerifyEmail($member);
				$this->page->setResponse((string)$member->memberId);

				return $container;
				}
			}

		if (! $member->password)
			{
			$submit = new \PHPFUI\Submit('Set Password');
			$form = new \PHPFUI\Form($this->page);

			if ($form->isMyCallback($submit))
				{
				// is the password valid and matching?
				if ($_POST['password'] !== $_POST['confirm']) // @mago-expect lint:no-insecure-comparison
					{
					\App\Model\Session::setFlash('alert', "Passwords don't match");
					$this->page->redirect();

					return $container;
					}

				$passwordPolicy = new \App\Model\PasswordPolicy();

				if ($errors = $passwordPolicy->validate($_POST['password']))
					{
					\App\Model\Session::setFlash('alert', $errors);
					$this->page->redirect();

					return $container;
					}
				$member->password = $this->memberModel->hashPassword($_POST['password']);
				$member->verifiedEmail = 0;
				$member->update();
				$this->memberModel->sendVerifyEmail($member);
				$this->page->redirect();

				return $container;
				}

			$container = $this->getHeader('Your Password Is Empty');
			$fieldSet = new \PHPFUI\FieldSet('You need to enter a password');
			$passwordPolicy = new \App\View\Admin\PasswordPolicy($this->page);
			$fieldSet->add($passwordPolicy->list());
			$current = $passwordPolicy->getValidatedPassword('password', 'Password', $member->password);
			$current->setRequired();
			$fieldSet->add($current);
			$confirm = $passwordPolicy->getValidatedPassword('confirm', 'Confirm Password', $member->password);
			$confirm->addAttribute('data-equalto', $current->getId());
			$confirm->addErrorMessage('Passwords must match.');
			$confirm->setRequired();
			$confirm->setToolTip('You must enter the same password twice to make sure it is correct');
			$fieldSet->add($confirm);
			$form->add($fieldSet);
			$form->add($submit);
			$form->setAreYouSure(false);
			}
		else
			{
			$alert = new \App\UI\Alert('The verification code was incorrect');
			$alert->addClass('alert');

			$container = $this->getHeader('Please Verify Your Email');
			$resendEmail = new \PHPFUI\AJAX('resendEmail');
			$resendEmail->addFunction('success', '$("#"+"' . $alert->getId() . '").html("Please check your inbox for a new email.")');
			$this->page->addJavaScript($resendEmail->getPageJS());
			$form = new \PHPFUI\Form($this->page);

			$form->add($alert);
			$resend = new \PHPFUI\Button('Resend email', '#');
			$resend->addAttribute('onclick', $resendEmail->execute(['memberId' => $member->memberId]));
			$form->add($resend);
			}
		$container->add($form);

		return $container;
		}

	private function explainLogin(\App\Record\Member $member) : \PHPFUI\HTML5Element
		{
		$output = $this->getHeader('First Time Log In');
		$member->verifiedEmail = 1;
		$member->update();

		return $output;
		}

	private function getAddress(\App\Record\Member $member) : \PHPFUI\HTML5Element
		{
		$output = $this->getHeader('Your Address');
		$form = new \PHPFUI\Form($this->page);
		$form->add($this->page->getFlashMessages());
		$form->add($this->memberView->getAddress($member->membership, true));
		$form->add($this->getButtonGroup($member->verifiedEmail));
		$output->add($form);

		return $output;
		}

	private function getButtonGroup(int $step) : \PHPFUI\ButtonGroup
		{
		$buttonGroup = new \PHPFUI\ButtonGroup();

		if ($step > 2)
			{
			$backButton = new \PHPFUI\Submit('Back');
			$backButton->addClass('secondary');
			$buttonGroup->addButton($backButton);
			}

		if ($step < 9)
			{
			$submit = new \PHPFUI\Submit('Continue');
			$submit->addClass('success');
			}
		else
			{
			$submit = new \PHPFUI\Submit('Join');
			$submit->addClass('success');
			}
		$buttonGroup->addButton($submit);

		return $buttonGroup;
		}

	private function getContactInfo(\App\Record\Member $member) : \PHPFUI\HTML5Element
		{
		$output = $this->getHeader('Your Contact Information');
		$form = new \PHPFUI\Form($this->page);
		$form->add($this->page->getFlashMessages());

		$cellPhone = new \App\UI\TelUSA($this->page, 'cellPhone', 'Cell Phone', $member->cellPhone);
		$form->add($cellPhone);
		$emergencyContact = new \PHPFUI\Input\Text('emergencyContact', 'Emergency Contact Name', $member->emergencyContact);
		$form->add($emergencyContact);
		$emergencyPhone = new \App\UI\TelUSA($this->page, 'emergencyPhone', 'Emergency Contact Phone', $member->emergencyPhone);
		$form->add($emergencyPhone);
		$form->add($this->getButtonGroup($member->verifiedEmail));
		$output->add($form);

		return $output;
		}

	private function getHeader(string $title) : \PHPFUI\HTML5Element
		{
		$output = new \PHPFUI\HTML5Element('div');

		if (! isset($_POST[$this->forgotPassword]))
			{
			$output->add(new \PHPFUI\Header('Join the ' . $this->settingTable->value('clubName')));
			$output->add(new \PHPFUI\Header($title, 3));
			$content = new \App\View\Content($this->page);
			$html = $content->getDisplayCategoryHTML($title);
			$output->add($html);
			}

		return $output;
		}

	private function getMembers(\App\Record\Member $member, string $discountCode) : \PHPFUI\Container
		{
		$membership = $member->membership;

		$freeText = $this->page->value('FreeMembershipQR');

		if ($membership->affiliation && $freeText && \str_contains($membership->affiliation, $freeText))
			{
			$membership->pending = 0;
			$membership->lastRenewed = null;
			$membership->joined = \App\Tools\Date::todayString();
			$year = (int)\date('Y');
			$membership->expires = "{$year}-12-31";
			$membership->allowedMembers = 1;
			$membership->update();

			$normalMember = $this->settingTable->getStandardPermissionGroup('Normal Member');
			$pendingMember = $this->settingTable->getStandardPermissionGroup('Pending Member');

			\App\Table\UserPermission::addPermissionToUser($member->memberId, $normalMember->permissionId);
			\App\Table\UserPermission::removePermissionFromUser($member->memberId, $pendingMember->permissionId);
			$member->verifiedEmail = 9;
			$member->update();

			$container = new \PHPFUI\Container();
			$form = new \PHPFUI\Form($this->page);
			$fieldSet = new \PHPFUI\FieldSet('Almost Done');
			$callout = new \PHPFUI\Callout('info');
			$callout->add('One last thing:');
			$fieldSet->add($callout);
			$bikeShopPicker = new \App\UI\BikeShopPicker('bikeShopId', 'Please select the bike shop where you bought your new bike');
			$fieldSet->add($bikeShopPicker);
			$form->add($fieldSet);
			$form->add($this->getButtonGroup($member->verifiedEmail));
			$container->add($form);

			return $container;
			}

		$members = \App\Table\Member::membersInMembership($member->membershipId);
		$renewView = new \App\View\Membership\Renew($this->page, $member->membership, $this->memberView, \App\Model\Member::MEMBERSHIP_JOIN);
		$container = $renewView->renew($discountCode);
		$allowedMembers = (int)$this->duesModel->MaxMembersOnMembership;

		if (! $allowedMembers || \count($members) < $allowedMembers)
			{
			$container->addAsFirst(new \PHPFUI\Header('Add Additional Members', 4));
			$this->memberView->getAddMemberModalButton($member->membership);
			}
		$container->addAsFirst($this->page->getFlashMessages());
		$container->addAsFirst($this->getHeader('Confirm Amount'));


		return $container;
		}

	private function getNotifications(\App\Record\Member $member) : \PHPFUI\HTML5Element
		{
		$output = $this->getHeader('Customize Your Notifications Settings');
		$form = new \PHPFUI\Form($this->page);
		$form->add($this->memberView->getNewsletterSetting($member));
		$form->add($this->memberView->getRideSettings($member));
		$form->add($this->getButtonGroup($member->verifiedEmail));
		$output->add($form);

		return $output;
		}

	private function getPayPal(\App\Record\Member $member, string $discountCode) : \PHPFUI\HTML5Element
		{
		$output = new \PHPFUI\HTML5Element('div');

		$membership = $member->membership;

		if ($membership->expires > \App\Tools\Date::todayString())
			{
			$membership->pending = 0;
			$membership->lastRenewed = null;
			$membership->joined = \App\Tools\Date::todayString();
			$membership->update();

			// set all members to have normal member privledge
			$memberTable = new \App\Table\Member();
			$memberTable->setWhere(new \PHPFUI\ORM\Condition('membershipId', $member->membershipId));

			$permissionModel = $this->page->getPermissions();

			foreach ($memberTable->getRecordCursor() as $memberRecord)
				{
				$permissionModel->addPermissionToUser($memberRecord->memberId, 'Normal Member');
				$permissionModel->removePermissionFromUser($memberRecord->memberId, 'Pending Member');
				}
			\App\Model\Session::unregisterMember();
			\App\Model\Session::registerMember($member);
			$this->page->redirect('/Home');

			return $output;
			}

		$output = $this->getHeader('Pay With PayPal');

		$renewView = new \App\View\Membership\Renew($this->page, $member->membership, $this->memberView, \App\Model\Member::MEMBERSHIP_JOIN);
		$output->add($renewView->checkout($member, $discountCode));

		return $output;
		}

	private function getPrivacy(\App\Record\Member $member) : \PHPFUI\HTML5Element
		{
		$output = $this->getHeader('Customize Your Privacy Settings');
		$form = new \PHPFUI\Form($this->page);
		$form->add($this->memberView->getPrivacySettings($member));
		$form->add($this->getButtonGroup($member->verifiedEmail));
		$output->add($form);

		return $output;
		}
	}
