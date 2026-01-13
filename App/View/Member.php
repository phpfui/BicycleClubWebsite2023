<?php

namespace App\View;

class Member
	{
	private string $addMemberButtonText = 'Add Member';

	private int $formCount = 0;

	private readonly bool $leader;

	private readonly \App\Model\Member $memberModel;

	private readonly \App\Model\ProfileImages $profileModel;

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->memberModel = new \App\Model\Member();
		$this->profileModel = new \App\Model\ProfileImages();
		$this->leader = $this->page->isAuthorized('Ride Leader');
		$this->processRequest();
		}

	public function crop(\App\Record\Member $member) : \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit('Save Crop');
		$form = new \PHPFUI\Form($this->page, $submit);

		if ($form->isMyCallback())
			{
			$member->profileX = (int)$_POST['profileX'];
			$member->profileY = (int)$_POST['profileY'];
			$member->profileWidth = (int)$_POST['profileWidth'];
			$member->profileHeight = (int)$_POST['profileHeight'];
			$message = 'Saved';
			$color = 'Lime';

			if ($member->profileWidth < 100)
				{
				$message = 'Photo width must be 100 or greater';
				$color = 'red';
				}
			elseif ($member->profileHeight < 100)
				{
				$message = 'Photo height must be 100 or greater';
				$color = 'red';
				}
			else
				{
				$member->update();
				$this->profileModel->update($member->toArray());
				$this->profileModel->crop();
				}

			$this->page->setResponse($message, $color);
			}
		else
			{
			$this->profileModel->update($member->toArray());
			$image = $this->profileModel->getImg();
			$cropper = new \App\View\Cropper($this->page, $image);
			$cropper->setOption('preserveAspectRatio', true);
			$cropper->setOption('minSize', [200, 200]);
			$cropper->setXField(new \PHPFUI\Input\Hidden('profileX', (string)$member->profileX));
			$cropper->setYField(new \PHPFUI\Input\Hidden('profileY', (string)$member->profileY));
			$cropper->setWidthField(new \PHPFUI\Input\Hidden('profileWidth', (string)$member->profileWidth));
			$cropper->setHeightField(new \PHPFUI\Input\Hidden('profileHeight', (string)$member->profileHeight));

			$form->add($cropper->editor());

			$buttonGroup = new \PHPFUI\ButtonGroup();
			$buttonGroup->addButton($submit);
			$link = '/Membership/edit/' . $member->memberId;

			if ($member->memberId == \App\Model\Session::signedInMemberId())
				{
				$link = '/Membership/myInfo';
				}

			$cancel = new \PHPFUI\Button('Back', $link . '?tab=Photo');
			$cancel->addClass('hollow')->addClass('secondary');
			$buttonGroup->addButton($cancel);

			$form->add($buttonGroup);
			}

		return $form;
		}

	public function edit(\App\Record\Member $member, bool $openAddressTab = false) : string
		{
		if (! $member->loaded())
			{
			return new \PHPFUI\SubHeader('Member not found');
			}

		return $this->editMembership($member->membership, $member, $openAddressTab);
		}

	public function editMembership(\App\Record\Membership $membership, \App\Record\Member $member = new \App\Record\Member(), bool $openAddressTab = true) : string
		{
		$tabs = new \PHPFUI\Tabs();

		if ($membership->loaded())
			{
			$members = \App\Table\Member::membersInMembership($membership->membershipId);

			foreach ($members as $mem)
				{
				$tabs->addTab($mem->firstName, $this->getEditForm($mem), ! $openAddressTab && $mem->memberId == $member->memberId);
				}
			$tabs->addTab('Address', $this->editMembershipForm($membership, $members), $openAddressTab);
			$tabs->addTab('Payments', $this->editPayments($membership));
			}
		else
			{
			// just return the membership form, nothing to tab just yet
			return $this->editMembershipForm();
			}

		$tabSection = $tabs->getTabs();
		$tabSection->setAttribute('data-deep-link', 'true');
		$tabSection->setAttribute('data-update-history', 'true');
		$tabSection->setAttribute('data-deep-link-smudge', 'false');

		return $tabSection . $tabs->getContent();
		}

	public function editPayments(\App\Record\Membership $membership) : \PHPFUI\Container
		{
		$view = new \App\View\Payments($this->page);

		return $view->show($membership->PaymentChildren);
		}

	public function getAddMemberModalButton(\App\Record\Membership $membership) : \PHPFUI\Button
		{
		$addMemberButton = new \PHPFUI\Button($this->addMemberButtonText);
		$addMemberButton->addClass('success');
		$modal = new \PHPFUI\Reveal($this->page, $addMemberButton);
		$modal->addClass('large');
		$modal->add(new \PHPFUI\Header($this->addMemberButtonText, 3));
		$modalForm = new \PHPFUI\Form($this->page)->setAreYouSure(false);
		$modalForm->setAreYouSure(false);
		$modalForm->add(new \PHPFUI\Input\Hidden('membershipId', (string)$membership->membershipId));
		$member = new \App\Record\Member();
		$modalForm->add($this->getMemberSettings($member));
		$email = new \App\UI\UniqueEmail($this->page, $member, 'email', 'Email Address');
		$email->setRequired();
		$modalForm->add($email);
		$modalForm->add($this->getNewsletterSetting($member));
		$modalForm->add($this->getRideSettings($member));
		$modalForm->add($this->getPrivacySettings($member));
		$modalForm->add(new \PHPFUI\FormError());
		$modalForm->add(new \PHPFUI\Submit($this->addMemberButtonText, 'action'));
		$modal->add($modalForm);

		return $addMemberButton;
		}

	public function getAddress(\App\Record\Membership $member, bool $requireAllAddressFields = true) : \PHPFUI\FieldSet
		{
		$requireAllAddressFields = true;
		$fieldSet = new \PHPFUI\FieldSet('Address');
		$address = new \PHPFUI\Input\Text('address', 'Street Address', $member->address);
		$address->setRequired($requireAllAddressFields);
		$fieldSet->add($address);
		$town = new \PHPFUI\Input\Text('town', 'Town', $member->town);
		$town->setRequired($requireAllAddressFields);
		$fieldSet->add($town);
		$multiColumn = new \PHPFUI\MultiColumn();
		$state = new \App\UI\State($this->page, 'state', 'State', $member->state ?? '');
		$state->setRequired($requireAllAddressFields);
		$multiColumn->add($state);
		$zip = new \PHPFUI\Input\Zip($this->page, 'zip', 'Zip Code', $member->zip ?? '');
		$zip->setRequired($requireAllAddressFields);
		$multiColumn->add($zip);
		$fieldSet->add($multiColumn);

		if ($this->page->isAuthorized('Edit Affiliation'))
			{
			$affiliation = new \PHPFUI\Input\Text('affiliation', 'Affiliation', $member->affiliation);
			$affiliation->setToolTip('If this membership is from a bike shop, or other activity, state it here.');
			$fieldSet->add($affiliation);
			}

		return $fieldSet;
		}

	public function getEditForm(\App\Record\Member $member) : \PHPFUI\Tabs
		{
		$id = $member->memberId;
		$tabs = new \PHPFUI\Tabs();

		$parameters = $this->page->getQueryParameters();
		$parameters['tab'] ??= 'General';

		$tabContent = [];
		$tabContent['General'] = $this->getMemberSettings($member);
		$tabContent['Email'] = $this->getEmails($member);
		$tabContent['Notifications'] = $this->getRideSettings($member) . $this->getNewsletterSetting($member);
		$tabContent['Privacy'] = $this->getPrivacySettings($member);

		if ($this->page->isAuthorized('Member Admin Tab'))
			{
			$tabContent['Admin'] = $this->getAdminSettings($member);
			}

		foreach ($tabContent as $tabName => $content)
			{
			$buttonGroup = new \PHPFUI\ButtonGroup();
			$wrapped = $this->wrapForm($id, $content, $buttonGroup);

			if ('General' == $tabName)
				{
				$waiverButton = new \PHPFUI\Button('Download Waiver', '/Membership/waiver/' . $member->memberId);
				$waiverButton->addClass('info');
				$buttonGroup->addButton($waiverButton);
				}
			$tabs->addTab($tabName, $wrapped, $parameters['tab'] == $tabName);
			}

		if ($this->page->isAuthorized('Member Photo'))
			{
			$tabName = 'Photo';
			$tabs->addTab($tabName, $this->getPhoto($member), $parameters['tab'] == $tabName);
			}

		return $tabs;
		}

	/**
	 * @param array<string,string> $member
	 */
	public function getImageIcon(array $member) : ?\PHPFUI\FAIcon
		{
		$this->profileModel->update($member);

		if (! $this->profileModel->cropExists())
			{
			return null;
			}
		$icon = new \PHPFUI\FAIcon('fas', 'portrait');
		$icon->deleteClass('fa-2x');
		$reveal = new \PHPFUI\Reveal($this->page, $icon);
		$reveal->add($this->profileModel->getCropImg());
		$reveal->add('<br><br>');
		$reveal->add($reveal->getCloseButton('Close'));

		return $icon;
		}

	public function getMemberStatus(\App\Record\Membership $membership) : \PHPFUI\FieldSet
		{
		$fieldSet = new \PHPFUI\FieldSet('Membership Information');

		if ($this->page->isAuthorized('Edit Membership Dates'))
			{
			$multiColumn = new \PHPFUI\MultiColumn();
			$multiColumn->add(new \PHPFUI\Input\Date($this->page, 'joined', 'Member Since', $membership->joined));
			$expires = $membership->expires ?? \App\Tools\Date::toString(\App\Tools\Date::endOfMonth(\App\Tools\Date::today()));
			$expires = new \PHPFUI\Input\MonthYear($this->page, 'expires', 'Membership Expires', $expires);
			$expires->setDay(0); // end of month
			$multiColumn->add($expires);
			$fieldSet->add($multiColumn);
			}
		else
			{
			$fieldSet->add(new \App\UI\Display('Member Since', \App\Tools\Date::formatString('F Y', $membership->joined)));
			$fieldSet->add(new \App\UI\Display('Member Expires', \App\Tools\Date::formatString('F Y', $membership->expires)));
			}
		$lastRenewed = $membership->lastRenewed ? \App\Tools\Date::formatString('F Y', $membership->lastRenewed) : 'New Membership';
		$fieldSet->add(new \App\UI\Display('Last Renewed', $lastRenewed));

//		if (! empty($member->lastLogin))
//			{
//			$fieldSet->add(new \App\UI\Display('Last Login', \date('n/j/Y \a\\t g:i a', \strtotime($member->lastLogin))));
//			}

		return $fieldSet;
		}

	public function getNewsletterSetting(\App\Record\Member $member) : \PHPFUI\FieldSet
		{
		$fieldSet = new \PHPFUI\FieldSet('Newsletter and Notification Email Settings');
		$announcements = new \PHPFUI\Input\CheckBoxBoolean('emailAnnouncements', 'Club Announcements', (bool)$member->emailAnnouncements);
		$announcements->setToolTip('You will receive club announcements as soon as they are sent, sometimes several a week');
		$journal = new \PHPFUI\Input\CheckBoxBoolean('journal', 'Weekly Journal', (bool)$member->journal);
		$journal->setToolTip('All annoucements are mailed every Thursday morning. Last minute announcements can be missed if the journal comes out after the event.');
		$fieldSet->add(new \PHPFUI\MultiColumn($announcements, $journal));
		$newsletter = new \PHPFUI\Input\CheckBoxBoolean('emailNewsletter', 'Receive Newsletter', (bool)$member->emailNewsletter);
		$newsletter->setToolTip('Club newsletters are sent periodically and can contain different information from other club communications above.');
		$rideComments = new \PHPFUI\Input\CheckBoxBoolean('rideComments', 'Receive ride comment emails', (bool)$member->rideComments);
		$rideComments->setToolTip('Check if you want to default to receiving ride comments. You can turn ride comment emails on or off on a per ride basis.');
		$fieldSet->add(new \PHPFUI\MultiColumn($newsletter, $rideComments));

		return $fieldSet;
		}

	public function getPrivacySettings(\App\Record\Member $member) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$toolTip = new \PHPFUI\ToolTip('Don’t show the following in the Membership Directory', 'Your personal information may still be shown to ride leaders if you sign up for a ride.');
		$fieldSet = new \PHPFUI\FieldSet($toolTip);
		$recentLink = new \PHPFUI\Link('/Membership/recent', 'Recent Sign Ins', false);
		$fields = ['showNothing' => 'My info',
			'showNoStreet' => 'Street',
			'showNoTown' => 'Town, zip',
			'showNoPhone' => 'Phone',
			'showNoRideSignup' => ['Don’t Show My Name in the Ride Signup list', 'Your name will not be shown in the ride signup list, but the ride leader will see your name'],
			'showNoSignin' => "Don’t Show My Name in the {$recentLink} list",
		];
		$multiColumn = new \PHPFUI\MultiColumn();

		$offset = 0;

		foreach ($fields as $field => $title)
			{
			$toolTip = '';

			if (\is_array($title))
				{
				$toolTip = $title[1];
				$title = $title[0];
				}
			$field = new \PHPFUI\Input\CheckBoxBoolean($field, $title, (bool)$member[$field]);

			if ($toolTip)
				{
				$field->setToolTip($toolTip);
				}
			$multiColumn->add($field);

			if (! (++$offset % 4))
				{
				$fieldSet->add($multiColumn);
				$multiColumn = new \PHPFUI\MultiColumn();
				}
			}

		$this->profileModel->update($member->toArray());

		if ($this->profileModel->cropExists())
			{
			$field = new \PHPFUI\Input\CheckBoxBoolean('showNoSocialMedia', 'Don’t post my image on social media', (bool)$member->showNoSocialMedia);
			$multiColumn->add($field);
			}
		else
			{
			$multiColumn->add('To set Social Media preference, you must upload a profile photo');
			}

		$fieldSet->add($multiColumn);

		$container->add($fieldSet);

		$container->add($this->getGeoLocationSelect($member));

		return $container;
		}

	public function getRideSettings(\App\Record\Member $member) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$categories = \App\Table\MemberCategory::getRideCategoriesForMember($member->memberId);
		$picker = new \App\UI\MultiCategoryPicker('categories', 'Ride Category Interests', $categories);
		$picker->setToolTip('You must specify your categories interests below to receive ride reminder emails');

		$toolTip = new \PHPFUI\ToolTip('Ride Reminder Settings', 'You sign up for Ride Reminder emails here.');
		$fieldSet = new \PHPFUI\FieldSet($toolTip);
		$fieldSet->add($picker);

		$journal = new \PHPFUI\Input\Number('rideJournal', 'Ride Journal Days In Advance', $member->rideJournal);
		$journal->addAttribute('min', (string)0)->addAttribute('max', (string)9);
		$journal->setToolTip('Number of days in advance to receive an email of the upcoming rides in your category. Zero for off.');
		$newRides = new \PHPFUI\Input\CheckBoxBoolean('newRideEmail', 'New Ride Posted Email', (bool)$member->newRideEmail);
		$newRides->setToolTip('Get an email when a ride is added in your categories');
		$filter = new \PHPFUI\Input\CheckBoxBoolean('rideScheduleFilter', 'Display Ride Schedule Filter', $this->page->isAuthorized('Ride Schedule Filter'));
		$filter->setToolTip('The filter will default to the categories selected above');
		$row = new \PHPFUI\MultiColumn($newRides, $journal);
		$fieldSet->add($row);
		$fieldSet->add($filter);
		$fieldSet->add(new \PHPFUI\Input\Hidden('updateCategories', '1'));	// need to set so model will update
		$container->add($fieldSet);

		return $container;
		}

	public function htmlList(\PHPFUI\ORM\DataObjectCursor $members) : string
		{
		$output = '';

		if (! \count($members))
			{
			return '';
			}

		foreach ($members as $member)
			{
			if ($member->showNothing)
				{
				continue;
				}
			$memberRecord = new \App\Record\Member($member);
			$name = $memberRecord->fullName();

			$town = $member->showNoTown ? '' : ' - ' . $member->town;
			$output .= "{$name} {$town}<br>";
			}

		return $output;
		}

	public function listMembersWithRides(\PHPFUI\ORM\DataObjectCursor $members) : string | \PHPFUI\Table
		{
		if (! \count($members))
			{
			return '';
			}
		$canEdit = $this->page->isAuthorized('Edit Member');
		$showAll = $this->page->isAuthorized('Show All Members');
		$table = new \PHPFUI\Table();
		$table->setHeaders(['Name', 'Town', 'Joined', 'Rides', 'email']);

		foreach ($members as $member)
			{
			$memberRecord = new \App\Record\Member($member);
			$name = $memberRecord->fullName();

			if ($member->showNothing && ! $showAll)
				{
				continue;
				}

			if ($canEdit)
				{
				$name = \PHPFUI\Link::localUrl('/Membership/edit/' . $member->memberId, $name);
				}
			$town = $member->showNoTown ? '' : $member->town;
			$table->addRow([
				'Name' => $name,
				'Town' => $town,
				'email' => new \PHPFUI\FAIcon('far', 'envelope', "/Membership/email/{$member->memberId}"),
				'Joined' => $member->joined,
				'Rides' => $member->rides,
			]);
			}

		return $table;
		}

	public function notifications(\App\Record\Member $member) : \PHPFUI\UnorderedList | \PHPFUI\SubHeader
		{
		if (! $member->loaded())
			{
			return new \PHPFUI\SubHeader('Member not found');
			}

		$ul = new \PHPFUI\UnorderedList();
		$emailLink = new \PHPFUI\Link('#', 'Update Emails', false);
		$emailReveal = new \PHPFUI\Reveal($this->page, $emailLink);
		$emailButtonGroup = new \PHPFUI\ButtonGroup();
		$emailReveal->add($this->wrapForm($member->memberId, $this->getEmails($member) . $this->getNewsletterSetting($member), $emailButtonGroup));
		$emailButtonGroup->addButton($this->getCancel());
		$ul->addItem(new \PHPFUI\ListItem($emailLink));

		$cellLink = new \PHPFUI\Link('#', 'Update Cell Phone', false);
		$cellButtonGroup = new \PHPFUI\ButtonGroup();
		$cellReveal = new \PHPFUI\Reveal($this->page, $cellLink);
		$cellReveal->add($this->wrapForm($member->memberId, $this->getCellSettings($member), $cellButtonGroup));
		$cellButtonGroup->addButton($this->getCancel());
		$ul->addItem(new \PHPFUI\ListItem($cellLink));

		$rideSettingsLink = new \PHPFUI\Link('#', 'Ride Reminder Settings', false);
		$rideSettingButtonGroup = new \PHPFUI\ButtonGroup();
		$rideSettingsReveal = new \PHPFUI\Reveal($this->page, $rideSettingsLink);
		$rideSettingsReveal->add($this->wrapForm($member->memberId, $this->getRideSettings($member), $rideSettingButtonGroup));
		$rideSettingButtonGroup->addButton($this->getCancel());
		$ul->addItem(new \PHPFUI\ListItem($rideSettingsLink));

		$forums = 'My Forums';

		if ($this->page->isAuthorized($forums))
			{
			$forumLink = new \PHPFUI\Link('/Forums/my', $forums, false);
			$ul->addItem(new \PHPFUI\ListItem($forumLink));
			}

		return $ul;
		}

	public function password(\App\Record\Member $member) : \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit('Save', 'changePassword');
		$form = new \PHPFUI\Form($this->page);

		if ($form->isMyCallback($submit))
			{
			\App\Model\Session::setFlash('post', $_POST);
			$errors = $this->memberModel->validatePassword($_POST['password']);

			if ($errors)
				{
				\App\Model\Session::setFlash('alert', $errors);
				$this->page->redirect();
				}
			elseif ($this->page->isAuthorized('Reset Any Password') || $this->memberModel->verifyPassword($_POST['current'], $member))
				{
				$member->password = $this->memberModel->hashPassword($_POST['password']);
				$member->passwordReset = $member->passwordResetExpires = null;
				$member->update();
				\App\Model\Session::destroy();
				\session_start();
				\App\Model\Session::registerMember($member);
				\App\Model\Session::setFlash('success', 'Password Changed');
				$this->page->redirect('/Home');
				}
			else
				{
				\App\Model\Session::setFlash('alert', 'Invalid Current Password');
				$this->page->redirect();
				}
			}
		else
			{
			$post = \App\Model\Session::getFlash('post');
			$column = new \PHPFUI\Cell(12);
			$current = new \PHPFUI\Input\PasswordEye('current', 'Current Password', $post['current'] ?? '');
			$current->setRequired();
			$current->setToolTip('You need to enter your current password as an extra precaution against fraud');
			$column->add($current);
			$passwordPolicy = new \App\View\Admin\PasswordPolicy($this->page);
			$column->add($passwordPolicy->list());
			$newPassword = $passwordPolicy->getValidatedPassword('password', 'New Password', $post['password'] ?? '');
			$newPassword->setRequired();
			$column->add($newPassword);
			$confirm = new \PHPFUI\Input\PasswordEye('confirm', 'Confirm Password', $post['confirm'] ?? '');
			$confirm->addAttribute('data-equalto', $newPassword->getId());
			$confirm->addErrorMessage('Must be the same as the new password.');
			$confirm->setRequired();
			$confirm->setToolTip('You must enter the same password twice to make sure it is correct');
			$column->add($confirm);
			$form->add($column);
			$form->setAreYouSure(false);
			$form->add($submit);
			}

		return $form;
		}

	public function passwordNew(\App\Record\Member $member) : \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit('Save Password and Sign In', 'changePassword');
		$form = new \PHPFUI\Form($this->page);

		if ($form->isMyCallback($submit))
			{
			$passwordValidator = new \App\Model\PasswordPolicy();
			$errors = $passwordValidator->validate($_POST['password'] ?? '');

			if (isset($_POST['confirm'], $_POST['password']) && $_POST['confirm'] == $_POST['password'] && ! $errors)
				{
				$member->password = $this->memberModel->hashPassword($_POST['password']);
				$member->passwordReset = $member->passwordResetExpires = null;
				$member->update();
				$this->memberModel->signInMember($member->email, $member->password);
				\App\Model\Session::setFlash('success', 'Password Reset');
				$this->page->redirect('/Home');
				}
			else
				{
				\App\Model\Session::setFlash('alert', $errors);
				$this->page->redirect();
				}
			}
		else
			{
			$passwordPolicy = new \App\View\Admin\PasswordPolicy($this->page);
			$form->add($passwordPolicy->list());
			$current = $passwordPolicy->getValidatedPassword('password', 'New Password');
			$current->setRequired();
			$form->add($current);
			$confirm = new \PHPFUI\Input\PasswordEye('confirm', 'Confirm Password');
			$confirm->addAttribute('data-equalto', $current->getId());
			$confirm->addErrorMessage('Must be the same as the new password.');
			$confirm->setRequired();
			$confirm->setToolTip('You must enter the same password twice to make sure it is correct');
			$form->setAreYouSure(false);
			$form->add($confirm);
			$form->add($submit);
			}

		return $form;
		}

	public function show(\PHPFUI\ORM\DataObjectCursor $members, string $noMembers = 'No members found') : string
		{
		if (! \count($members))
			{
			return new \PHPFUI\Header($noMembers, 5);
			}
		$delete = new \PHPFUI\AJAX('deleteMember', 'Permanently delete this member?');
		$delete->addFunction('success', '$("#member-"+data.response).css("background-color","red").hide("fast").remove()');
		$columns = [];
		$columns[] = $this->makeColumn(4, 'memberName', 'Name');
		$columns[] = $this->makeColumn(3, 'address', 'Address', 'showNoStreet');
		$columns[] = $this->makeColumn(3, 'town', 'Town', 'showNoTown');
		$columns[] = $this->makeColumn(2, 'category', 'Categories');
		$row = new \PHPFUI\GridX();

		foreach ($columns as $column)
			{
			$field = new \PHPFUI\Cell((int)$column['column']);
			$field->add($column['name']);
			$row->add($field);
			}
		$header = $row;
		$canEdit = $this->page->isAuthorized('Edit Member');
		$canDelete = $this->page->isAuthorized('Delete Member');
		$showAll = $this->page->isAuthorized('Show All Members');
		$this->page->addJavaScript($delete->getPageJS());
		$accordion = new \App\UI\Accordion();
		$emailMember = $this->page->isAuthorized('Email Member');

		foreach ($members as $memberObject)
			{
			$memberArray = $memberObject->toArray();

			if (empty($memberArray['memberName']))
				{
				$memberArray['memberName'] = $memberArray['firstName'] . ' ' . $memberArray['lastName'];
				}
			$member = new \App\Record\Member($memberArray);

			if (! $canEdit && $member->showNothing && ! $showAll)
				{
				continue;
				}
			$id = isset($memberArray['memberId']) && $member->memberId ? $member->memberId : -($memberObject->membershipId ?? 0);
			$memberArray['category'] = \App\Table\MemberCategory::getRideCategoryStringForMember($id);
			$row = new \PHPFUI\GridX();

			foreach ($columns as $column)
				{
				$field = new \PHPFUI\Cell((int)$column['column']);

				if ($column['no'] && ($memberArray[$column['no']] ?? false))
					{
					$field->add('&nbsp;');  // user does not want this field shown
					}
				else
					{
					$field->add($memberArray[$column['field']] ?? '');
					}
				$row->add($field);
				}
			// add a row of details, add stuff and keep track of column left
			$detail = [];

			$image = $this->getImageIcon($member->toArray());

			if ($image)
				{
				$image->addClass('fa-2x');
				$detail[] = $image;
				}

			// email
			if ($emailMember && $id > 0)
				{
				$detail[] = new \PHPFUI\FAIcon('far', 'envelope', "/Membership/email/{$id}");
				}

			// phone
			if (! empty($member->phone) && ($canEdit || $showAll || ! $member->showNoPhone))
				{
				$icon = new \PHPFUI\FAIcon('fas', 'phone', 'tel:' . $member->phone);
				$icon->setToolTip($member->phone);
				$detail[] = $icon;
				}

			// cell
			if (! empty($member->cellPhone) && ($canEdit || $showAll || ! $member->showNoPhone))
				{
				$icon = new \PHPFUI\FAIcon('fas', 'mobile-alt', 'tel:' . $member->cellPhone);
				$icon->setToolTip($member->cellPhone);
				$detail[] = $icon;
				}

			// emergency contact
			// emergency phone
			if ((! empty($member->emergencyContact) || ! empty($member->emergencyPhone)) && ($this->leader || ! $member->showNoPhone))
				{
				$icon = new \PHPFUI\FAIcon('fas', 'phone-square', 'tel:' . $member->emergencyPhone);
				$icon->addClass('warning');
				$detail[] = new \PHPFUI\ToolTip($icon, 'Emergency contact: ' . $member->emergencyContact);
				}

			// edit
			if ($canEdit)
				{
				if ($id > 0)
					{
					$detail[] = new \PHPFUI\FAIcon('far', 'edit', "/Membership/edit/{$id}");
					}
				elseif ($id <= 0)
					{
					$detail[] = new \PHPFUI\FAIcon('far', 'edit', "/Membership/editMembership/{$memberObject->membershipId}");
					}
				}

			// delete
			if (($canDelete || $member->membershipId == \App\Model\Session::signedInMembershipId()) && $id != \App\Model\Session::signedInMemberId())
				{
				$icon = new \PHPFUI\FAIcon('far', 'trash-alt', '#');
				$icon->addAttribute('onclick', $delete->execute(['memberId' => $id]));
				$detail[] = $icon;
				}

			$detailRow = $this->splitIcons($detail);
			$accordion->addTab($row, $detailRow, false)->setId("member-{$id}");
			}

		return $header . $accordion;
		}

	private function addEmailModal(\PHPFUI\HTML5Element $modalLink, \App\Record\Member $member) : void
		{
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$modal->addClass('medium');
		$modalForm = new \PHPFUI\Form($this->page);
		$modalForm->setAreYouSure(false);
		$email = new \PHPFUI\Input\Email('email', 'Additional Forum Email');
		$modalForm->add($email);
		$modalForm->add(new \PHPFUI\Input\Hidden('memberId', (string)$member->memberId));
		$modalForm->add(new \PHPFUI\Submit('Add Email', 'action'));
		$modal->add($modalForm);
		}

	private function editMembershipForm(\App\Record\Membership $membership = new \App\Record\Membership(), ?\PHPFUI\ORM\RecordCursor $members = null) : \PHPFUI\Form
		{

		if ($membership->loaded())
			{
			$submit = new \PHPFUI\Submit('Save', "submit-M{$membership->membershipId}");
			$form = new \PHPFUI\Form($this->page, $submit);
			}
		else
			{
			$submit = new \PHPFUI\Submit('Add', 'submit-M0');
			$form = new \PHPFUI\Form($this->page);
			}
		$canAddPayment = $this->page->isAuthorized('Add Payment') && $membership->membershipId;

		if ($form->isMyCallback())
			{
			$post = $_POST;
			$post['membershipId'] = $membership->membershipId;

			if (! $this->page->isAuthorized('Edit Affiliation'))
				{
				unset($post['affiliation']);
				}

			if (isset($post['stateText']) && empty($post['state']))
				{
				$post['state'] = \App\UI\State::getAbbrevation($post['stateText']);
				}

			if (! $this->page->isAuthorized('Edit Membership Dates'))
				{
				unset($post['joined'], $post['expires'], $post['pending'], $post['lastRenewed']);
				}

			if ($canAddPayment)
				{
				if (! empty($post['paymentNumber']) && ! empty($post['paymentDate']) && ! empty($post['paymentAmount']))
					{
					$payment = new \App\Record\Payment();
					$payment->paymentType = (int)$post['paymentType'];
					$payment->amount = (float)$post['paymentAmount'];
					$payment->membership = $membership;
					$payment->dateReceived = \date('Y-m-d');
					$payment->paymentNumber = $post['paymentNumber'];
					$payment->paymentDated = $post['paymentDate'];
					$payment->enteringMemberNumber = \App\Model\Session::signedInMemberId();
					$payment->insert();
					$post['lastRenewed'] = \date('Y-m-d');
					$post['pending'] = 0;
					}
				}
			else
				{
				unset($post['allowedMembers']);
				}
			$membership->setFrom($post);
			$membership->update();
			$this->page->setResponse('Saved');
			}
		else
			{
			if ($membership->membershipId)
				{
				$form->add($this->getMemberStatus($membership));
				}
			$form->add($this->getAddress($membership, false));

			if ($canAddPayment)
				{
				$form->add($this->getPayments($membership));
				}
			$buttonGroup = new \PHPFUI\ButtonGroup();
			$buttonGroup->addButton($submit);

			if ($membership->loaded() && ($membership->allowedMembers < 1 || (null === $members ? 0 : \count($members)) < $membership->allowedMembers))
				{
				$addMemberButton = $this->getAddMemberModalButton($membership);
				$form->saveOnClick($addMemberButton);
				$buttonGroup->addButton($addMemberButton);
				}
			$form->add($buttonGroup);
			}

		return $form;
		}

	private function getAdminSettings(\App\Record\Member $member) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$container->add(new \App\UI\Display('Last Login', $member->lastLogin ?? ''));
		$container->add(new \App\UI\Display('Accepted Waiver', $member->acceptedWaiver ?? ''));
		$multiColumn = new \PHPFUI\MultiColumn();

		if ($this->page->isAuthorized('Edit Volunteer Points'))
			{
			$points = new \PHPFUI\Input\Number('volunteerPoints', 'Volunteer Points', $member->volunteerPoints ?? 0);
			$points->addAttribute('max', (string)9999)->addAttribute('min', (string)0);
			$points->setToolTip('You can change the number of volunteer points if you need to override the system calculation');
			$multiColumn->add($points);
			}

		if ($this->page->isAuthorized('Edit Member Deceased'))
			{
			$deceased = new \PHPFUI\Input\CheckBoxBoolean('deceased', 'Deceased', (bool)$member->deceased);
			$deceased->setToolTip("You can mark this member as deceased so they won't show up in searches, but still be associated with rides and cuesheets.");
			$multiColumn->add('<br>' . $deceased);
			}

		if (\count($multiColumn))
			{
			$container->add($multiColumn);
			}

		$buttonGroup = new \PHPFUI\ButtonGroup();

		if ($this->page->isAuthorized('Edit Member Permissions'))
			{
			$buttonGroup->addButton(new \PHPFUI\Button('Permissions', '/Membership/permissionEdit/' . $member->memberId));
			}

		if ($this->page->isAuthorized('Reset Any Password'))
			{
			$reset = new \PHPFUI\Button('Change Password', '/Membership/password/' . $member->memberId);
			$reset->addClass('warning');
			$buttonGroup->addButton($reset);
			}

		if ($this->page->isAuthorized('Reset Password EMail'))
			{
			$textModel = new \App\Model\SMS();

			if ($textModel->enabled())
				{
				$reset = new \PHPFUI\DropDownButton('Reset Password');
				$reset->addLink('/Membership/passwordReset/' . $member->memberId, 'By Email');
				$reset->addLink('/Membership/passwordReset/' . $member->memberId . '/1', 'By Text');
				}
			else
				{
				$reset = new \PHPFUI\Button('Reset Password', '/Membership/passwordReset/' . $member->memberId);
				}
			$reset->addClass('alert');
			$buttonGroup->addButton($reset);
			}

		if ($this->page->isAuthorized('Email Single Newsletter'))
			{
			$newsletter = new \PHPFUI\Button('Email Newsletter', '/Membership/newsletter/' . $member->memberId);
			$newsletter->addClass('info');
			$buttonGroup->addButton($newsletter);
			}

		if ($this->page->isAuthorized('Login As Other User'))
			{
			$loginAs = new \PHPFUI\Button('Login As', '/Home/loginAs/' . $member->memberId);
			$loginAs->addClass('success');
			$buttonGroup->addButton($loginAs);
			}

		if ($this->page->isAuthorized('Ride Attendance'))
			{
			$rideAttendance = new \PHPFUI\Button('Ride Attendance', '/Rides/attendance/' . $member->memberId);
			$rideAttendance->addClass('secondary');
			$buttonGroup->addButton($rideAttendance);
			}

		if (\count($buttonGroup))
			{
			$buttonGroup->addClass('small');
			$container->add($buttonGroup);
			}

		return $container;
		}

	private function getCancel() : \PHPFUI\Cancel
		{
		$cancel = new \PHPFUI\Cancel('Close');
		$cancel->addClass('hollow')->addClass('alert');

		return $cancel;
		}

	private function getCellSettings(\App\Record\Member $member) : \PHPFUI\FieldSet
		{
		$fieldSet = new \PHPFUI\FieldSet('Cell Phone Settings');
		$cellPhone = new \App\UI\TelUSA($this->page, 'cellPhone', 'Cell Phone', $member->cellPhone);
		$fieldSet->add($cellPhone);
		$allowTexting = new \PHPFUI\Input\CheckBoxBoolean('allowTexting', 'Enable club texts', (bool)$member->allowTexting);
		$allowTexting->setToolTip('Members can send texts to other members via the website, or a ride.  Uncheck to opt out of club texts.');
		$fieldSet->add($allowTexting);
		$fieldSet->add($this->getGeoLocationSelect($member));

		return $fieldSet;
		}

	private function getEmails(\App\Record\Member $member) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$primaryFieldSet = new \PHPFUI\FieldSet('Your Primary (Log In) Email Address');
		$email = new \App\UI\UniqueEmail($this->page, $member, 'email', 'Email Address', $member->email);
		$email->setRequired();
		$primaryFieldSet->add($email);
		$container->add($primaryFieldSet);

		$memberFieldSet = new \PHPFUI\FieldSet('Additional Forum Emails');
		$memberFieldSet->add('These email addresses are not your primary contact email, but can be used to reply from in our Forums.');
		$table = new \PHPFUI\Table();
		$table->setRecordId($id = 'additionalEmailId');
		$delete = new \PHPFUI\AJAX('deleteEmail', 'Are you sure you want to delete this email address?');
		$delete->addFunction('success', '$("#' . $id . '-"+data.response).css("background-color","red").hide("fast").remove()');
		$this->page->addJavaScript($delete->getPageJS());
		$additionalEmailTable = new \App\Table\AdditionalEmail();
		$additionalEmailTable->setWhere(new \PHPFUI\ORM\Condition('memberId', $member->memberId));
		$table->setHeaders(['email', 'Verified', 'Del']);

		foreach ($additionalEmailTable->getRecordCursor() as $additionalEmail)
			{
			$email = $additionalEmail->toArray();
			$email['Verified'] = $additionalEmail->verified ? 'Verified' : "<a href='/Membership/verifyEmail/{$email['email']}'>Verify</a>";
			$icon = new \PHPFUI\FAIcon('far', 'trash-alt', '#');
			$icon->addAttribute('onclick', $delete->execute([$id => $additionalEmail->additionalEmailId]));
			$email['Del'] = $icon;
			$table->addRow($email);
			}
		$memberFieldSet->add($table);

		$addButton = new \PHPFUI\Button('Add email');
		$memberFieldSet->add($addButton);
		$this->addEmailModal($addButton, $member);
		$container->add($memberFieldSet);

		return $container;
		}

	private function getGeoLocationSelect(\App\Record\Member $member) : \PHPFUI\HTML5Element
		{
		return new \App\UI\GeoLocate('geoLocate', $member->geoLocate);
		}

	private function getMemberSettings(\App\Record\Member $member) : \PHPFUI\Container
		{
		$memberFieldSet = new \PHPFUI\Container();

		$link = new \PHPFUI\Link("/Volunteer/myPoints/{$member->memberId}", 'Available Volunteer Points', false);
		$points = new \App\UI\Display($link, $member->volunteerPoints ?? 0);
		$points->setToolTip('You can redeem these point in the store.  Volunteer points will automatically be applied to eligible items on checkout.');
		$memberFieldSet->add($points);

		$firstName = new \PHPFUI\Input\Text('firstName', 'First Name', $member->firstName);
		$firstName->setRequired();
		$lastName = new \PHPFUI\Input\Text('lastName', 'Last Name', $member->lastName);
		$lastName->setRequired();
		$memberFieldSet->add(new \PHPFUI\MultiColumn($firstName, $lastName));
		$phone = new \App\UI\TelUSA($this->page, 'phone', 'Phone', $member->phone);
		$cellPhone = new \App\UI\TelUSA($this->page, 'cellPhone', 'Cell Phone', $member->cellPhone);
		$cellPhone->setRequired();
		$memberFieldSet->add(new \PHPFUI\MultiColumn($phone, $cellPhone));
		$emergencyContact = new \PHPFUI\Input\Text('emergencyContact', 'Emergency Contact Name', $member->emergencyContact);
		$emergencyPhone = new \App\UI\TelUSA($this->page, 'emergencyPhone', 'Emergency Contact Phone', $member->emergencyPhone);
		$memberFieldSet->add(new \PHPFUI\MultiColumn($emergencyContact, $emergencyPhone));

		return $memberFieldSet;
		}

	private function getPayments(\App\Record\Membership $membership) : \PHPFUI\FieldSet
		{
		$fieldSet = new \PHPFUI\FieldSet('Payment Information');
		$multiColumn = new \PHPFUI\MultiColumn();
		$paymentType = new \PHPFUI\Input\Select('paymentType', 'Payment Type');

		foreach (\App\Table\Payment::getPaymentTypes() as $index => $type)
			{
			$paymentType->addOption($type, $index, 1 == $index);
			}
		$multiColumn->add($paymentType);
		$checkNumber = new \PHPFUI\Input\Text('paymentNumber', 'Payment Number');
		$multiColumn->add($checkNumber);
		$checkDate = new \PHPFUI\Input\Date($this->page, 'paymentDate', 'Payment Date');
		$multiColumn->add($checkDate);
		$checkAmount = new \PHPFUI\Input\Number('paymentAmount', 'Payment Amount');
		$checkAmount->setToolTip('Whole numbers are assumed to be dollars and no cents.');
		$multiColumn->add($checkAmount);
		$fieldSet->add($multiColumn);
		$multiColumn = new \PHPFUI\MultiColumn();

		if ($membership->allowedMembers < 0)
			{
			$membership->allowedMembers = 0;
			}
		$allowedMembers = new \PHPFUI\Input\Number('allowedMembers', 'Number of members allowed on this membership', $membership->allowedMembers);
		$allowedMembers->addAttribute('max', (string)99)->addAttribute('min', (string)0);
		$allowedMembers->setToolTip('Each membership can be allowed a different number of members, enter 0 for unlimited');
		$multiColumn->add($allowedMembers);
		$pending = new \PHPFUI\Input\CheckBoxBoolean('pending', 'Pending membership (not yet paid)', (bool)$membership->pending);
		$multiColumn->add('<br>' . $pending);
		$fieldSet->add($multiColumn);

		return $fieldSet;
		}

	private function getPhoto(\App\Record\Member $member) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$equalizer = new \PHPFUI\Equalizer();

		$this->profileModel->update($member->toArray());
		$photo = $this->profileModel->getCropImg();
		$pleaseCrop = '';

		if (! $photo)
			{
			$pleaseCrop = new \PHPFUI\HTML5Element('span');
			$pleaseCrop->addClass('float-left');
			$pleaseCrop->add('<b> <- Please crop</b>');
			$photo = $this->profileModel->getImg();
			}

		$currentFieldSet = new \PHPFUI\FieldSet('Current Photo');
		// always get the ID so it does not change if a photo has been updated
		$currentFieldSetId = $currentFieldSet->getId();

		if ($photo)
			{
			$delete = new \PHPFUI\AJAX('deleteImage', 'Delete this photo?');
			$delete->addFunction('success', '$("#"+data.response).css("background-color","red").hide("fast").remove()');
			$this->page->addJavaScript($delete->getPageJS());
			$currentFieldSet->add($photo);

			$div = new \PHPFUI\HTML5Element('div');
			$div->addClass('clearfix');
			$cropIcon = new \PHPFUI\FAIcon('fas', 'crop');
			$cropIcon->setToolTip('Crop this photo');
			$cropLink = new \PHPFUI\Link('/Membership/crop/' . $member->memberId, $cropIcon, false);
			$cropLink->addClass('float-left');
			$div->add($cropLink);
			$div->add($pleaseCrop);
			$deleteIcon = new \PHPFUI\FAIcon('far', 'trash-alt');
			$deleteLink = new \PHPFUI\Link(null, $deleteIcon);
			$deleteLink->addAttribute('onclick', $delete->execute(['memberId' => $member->memberId,  'deleteId' => '"' . $currentFieldSetId . '"']));
			$deleteLink->addClass('float-right');
			$div->add($deleteLink);

			if ($pleaseCrop)
				{
				$alert = new \PHPFUI\Callout('alert');
				$alert->add('You must crop the photo before it will be shown.');
				$currentFieldSet->add($alert);
				}

			$currentFieldSet->add($div);
			}
		else
			{
			$settingTable = new \App\Table\Setting();
			$fileName = $settingTable->value('missingProfile');

			if ($fileName)
				{
				$file = new \App\Model\ImageFiles();
				$currentFieldSet->add($file->getImg($fileName));
				}
			}

		$equalizer->addColumn($currentFieldSet, 'medium-6');

		$uploadFieldSet = new \PHPFUI\FieldSet('Upload New');
		$uploadName = 'Upload';
		$uploadValue = 'upload' . $member->memberId;
		$fileField = 'image' . $member->memberId;
		$form = new \PHPFUI\Form($this->page);

		if (($_POST[$uploadValue] ?? '') == $uploadName && \App\Model\Session::checkCSRF())
			{
			$allowedFiles = ['.jpg' => 'image/jpeg',
				'.jpeg' => 'image/jpeg',
				'.gif' => 'image/gif',
				'.png' => 'image/png', ];
			$this->profileModel->upload($member->memberId, $fileField, $_FILES, $allowedFiles);
			$error = $this->profileModel->getLastError();

			if ($error)
				{
				\App\Model\Session::setFlash('alert', $error);
				}
			else
				{
				\App\Model\Session::setFlash('success', 'Add a profile photo!');
				}
			$extension = $this->profileModel->getExtension();

			if ($extension)
				{
				$member->extension = $extension;
				$member->profileWidth = $member->profileHeight = 0;
				$member->update();
				}

			$this->page->redirect('', 'tab=Photo');
			}
		else
			{
			$file = new \PHPFUI\Input\File($this->page, $fileField, 'Select Image to Upload (.jpg, .png, .gif only)');
			$file->setAllowedExtensions(['png', 'jpg', 'jpeg', 'gif']);
			$file->setToolTip('Photo to be uploaded should be clear and high quality.');
			$form->add($file);
			$form->add(new \PHPFUI\Submit($uploadName, $uploadValue));
			}

		$uploadFieldSet->add($form);

		$equalizer->addColumn($uploadFieldSet, 'medium-6');
		$container->add($equalizer);

		return $container;
		}

	/**
	 * @return string[]
	 */
	private function makeColumn(string | int $column, string $field, string $name, string $restriction = '') : array
		{
		return ['column' => $column,
			'field' => $field,
			'name' => $name,
			'no' => $restriction, ];
		}

	private function processRequest() : void
		{
		if (\App\Model\Session::checkCSRF())
			{
			$post = $_POST;

			if (isset($post['action']))
				{
				switch ($post['action'])
					{
					case 'deleteImage':
						$memberId = (int)($post['memberId'] ?? 0);

						if ($memberId)
							{
							$member = new \App\Record\Member($memberId);
							$this->profileModel->update($member->toArray());
							$member->profileWidth = 0;
							$member->profileHeight = 0;
							$member->extension = '';
							$member->update();
							$this->profileModel->delete((string)$memberId);
							}
						$this->page->setResponse($post['deleteId']);

						break;

					case 'deleteEmail':
						$key = 'additionalEmailId';
						$additionalEmail = new \App\Record\AdditionalEmail($post[$key]);
						$additionalEmail->delete();
						$this->page->setResponse($post[$key]);

						break;

					case 'Add Email':
						$post['verified'] = 0;
						$email = \App\Model\Member::cleanEmail($post['email'] ?? '');

						if (! empty($post['memberId']) && \filter_var($email, FILTER_VALIDATE_EMAIL))
							{
							$member = new \App\Record\Member(['email' => $email]);

							if ($member->empty())
								{
								$post['email'] = $email;
								$additionalEmail = new \App\Record\AdditionalEmail();
								$additionalEmail->setFrom($post);
								$additionalEmail->insertOrIgnore();
								}
							}
						$this->page->redirect();

						break;

					case 'deleteMember':
						$memberId = (int)$post['memberId'];

						if ($memberId > 0)
							{
							$member = $this->memberModel->get($memberId);

							if ($member && ($this->page->isAuthorized('Delete Member') || $member['membershipId'] == \App\Model\Session::signedInMembershipId())
									&& $member['memberId'] != \App\Model\Session::signedInMemberId())
								{
								$this->memberModel->delete($memberId);
								}
							else
								{
								$memberId = 0;
								}
							}
						else
							{
							$membership = new \App\Record\Membership(-$memberId);
							$membership->delete();
							}
						$this->page->setResponse((string)$memberId);

						break;

					case $this->addMemberButtonText:
						$this->memberModel->addMemberToMembership($post);
						$this->page->redirect();

						break;
					}
				}
			elseif (isset($post['submit-M0']))
				{
				unset($post['expires']);
				$post['pending'] = 0;
				$post['joined'] = \date('Y-m-d');
				unset($post['lastRenewed']);
				$membership = new \App\Record\Membership();

				if (isset($post['stateText']) && empty($post['state']))
					{
					$post['state'] = \App\UI\State::getAbbrevation($post['stateText']);
					}
				$membership->setFrom($post);
				$id = $membership->insert();
				$this->page->redirect("/Membership/editMembership/{$id}");
				}
			}
		}

	/**
	 * @param array<mixed> $detail
	 */
	private function splitIcons(array $detail) : \PHPFUI\Container
		{
		$retVal = new \PHPFUI\Container();

		$count = \count($detail);

		if (! $count)
			{
			return $retVal;
			}

		if ($count > 6)
			{
			// split icons
			++$count;
			$half = (int)($count / 2);
			$first = \array_slice($detail, 0, $half);
			$retVal->add($this->splitIcons($first));
			$detail = \array_slice($detail, $half);
			$count = \count($detail);
			}

		$allocatedColumns = [12,
			6,
			4,
			3,
			2,
			2,
		];
		$columnSize = $allocatedColumns[$count - 1];
		$detailRow = new \PHPFUI\GridX();

		foreach ($detail as $icon)
			{
			$col = new \PHPFUI\Cell($columnSize);
			$col->add($icon);
			$detailRow->add($col);
			}
		$retVal->add($detailRow);

		return $retVal;
		}

	private function wrapForm(int $memberId, string $content, ?\PHPFUI\ButtonGroup $buttonGroup = null) : \PHPFUI\Form
		{
		++$this->formCount;
		$submit = new \PHPFUI\Submit('Save', "submit-{$memberId}-{$this->formCount}");
		$form = new \PHPFUI\Form($this->page, $submit);

		if ($form->isMyCallback())
			{
			$post = $_POST;
			$post['memberId'] = $memberId;
			unset($post['membershipId'], $post['lastLogin'], $post['password'], $post['pending'], $post['pendingLeader'], $post['acceptedWaiver']);

			if (! $this->page->isAuthorized('Edit Volunteer Points'))
				{
				unset($post['volunteerPoints']);
				}

			if (! $this->page->isAuthorized('Edit Member Deceased'))
				{
				unset($post['deceased']);
				}

			if (! $this->page->isAuthorized('Member Admin Tab'))
				{
				unset($post['expires'], $post['subscriptionId'], $post['allowedMembers'], $post['joined'], $post['lastRenewed'], $post['renews']);
				}

			$this->memberModel->saveFromPost($post);
			$this->page->setResponse('Saved');
			}
		else
			{
			$form->add($content);

			if (! $buttonGroup)
				{
				$buttonGroup = new \PHPFUI\ButtonGroup();
				}
			$buttonGroup->addButton($submit);
			$form->add($buttonGroup);
			}

		return $form;
		}
	}
