<?php

namespace App\View\Setup;

class SuperUsers extends \PHPFUI\Container
	{
	public function __construct(private readonly \PHPFUI\Page $page)
		{
		}

	public function addUsers(\App\View\Setup\WizardBar $wizardBar) : \PHPFUI\Form
		{
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$form->add(new \PHPFUI\Header('Add Super Users', 4));
		$form->add($wizardBar);

		if (! empty($_GET['remove']))
			{
			\App\Table\UserPermission::removePermissionFromUser($_GET['remove'], 1);
			$this->page->redirect();

			return $form;
			}
		elseif (! empty($_POST['memberId']))
			{
			$memberId = (int)$_POST['memberId'];
			\App\Table\UserPermission::addPermissionToUser($memberId, 1);
			$member = new \App\Record\Member($memberId);
			$member->verifiedEmail = 10;
			$member->update();
			\PHPFUI\Session::setFlash('success', 'Super User added');
			$this->page->redirect();

			return $form;
			}
		$callout = new \PHPFUI\Callout('info');
		$callout->add('<b>Super Users</b> are god like users that always have all rights. ');
		$callout->add('Every website needs super users to make sure things go smoothly and fix any issues that may arise in the normal course of things. ');
		$callout->add('Super users can also delete all data so they should only be trusted people. ');
		$callout->add('You will need to add at least one super user (<b>HINT:</b> Yourself!)');
		$form->add($callout);

		$memberTable = new \App\Table\Member();
		$memberTable->getMembersWithPermissionId(1);

		$superUserCount = \count($memberTable);

		if (! $superUserCount)
			{
			$callout = new \PHPFUI\Callout('alert');
			$callout->add('No <b>Super Users</b> are defined.');
			$form->add($callout);
			}
		else
			{
			$view = new \App\UI\ContinuousScrollTable($this->page, $memberTable);
			$page = $this->page;
			$view->addCustomColumn('del', static fn (array $user) : \PHPFUI\FAIcon => new \PHPFUI\FAIcon('far', 'trash-alt', $page->getBaseURL() . '?remove=' . $user['memberId']));

			$headers = ['firstName', 'lastName', ];
			$view->setSearchColumns($headers)->setHeaders(\array_merge($headers, ['del']))->setSortableColumns($headers);

			$form->add($view);
			}

		$addUser = new \PHPFUI\Button('Add a Super User');
		$this->getAddMemberModal($addUser);

		$wizardBar->nextAllowed($superUserCount > 0);
		$wizardBar->addButton($addUser);

		return $form;
		}

	public function emailPasswordResets(\App\View\Setup\WizardBar $wizardBar) : \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit('Send Emails');
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$form->add(new \PHPFUI\Header('Email Password Resets', 4));
		$form->add($wizardBar);

		if (! empty($_POST['email']))
			{
			$memberModel = new \App\Model\Member();
			$count = 0;

			foreach ($_POST['email'] as $memberId => $checked)
				{
				$member = new \App\Record\Member($memberId);
				\App\Model\PasswordPolicy::resetPassword($member->email);
				++$count;
				}
			\PHPFUI\Session::setFlash('success', $count . ' Super Users emailed password resets');
			$this->page->redirect();

			return $form;
			}
		$callout = new \PHPFUI\Callout('info');
		$callout->add('You will need to reset passwords for your super users.  Check the users you want to email password resets to. ');
		$callout->add('Users that are not emailed a password reset may not know they need to reset their password');
		$form->add($callout);

		$memberTable = new \App\Table\Member();
		$memberTable->getMembersWithPermissionId(1);

		$superUserCount = \count($memberTable);

		if (! $superUserCount)
			{
			$callout = new \PHPFUI\Callout('alert');
			$callout->add('No <b>Super Users</b> are defined.');
			$form->add($callout);
			}
		else
			{
			$view = new \App\UI\ContinuousScrollTable($this->page, $memberTable);
			$view->addCustomColumn('email', static fn (array $user) : \PHPFUI\Input\CheckBox => new \PHPFUI\Input\CheckBox("email[{$user['memberId']}]")->addClass('checkAll'));
			$checkAll = new \App\UI\CheckAll('.checkAll');
			$headers = ['firstName', 'lastName', 'email' => $checkAll];
			$view->setSearchColumns($headers)->setHeaders($headers);
			unset($headers['email']);
			$view->setSortableColumns($headers);

			$form->add($view);
			}
		$wizardBar->addButton($submit);

		return $form;
		}

	private function getAddMemberModal(\PHPFUI\HTML5Element $add) : void
		{
		$modal = new \PHPFUI\Reveal($this->page, $add);
		$modalForm = new \PHPFUI\Form($this->page);
		$modalForm->setAreYouSure(false);
		$fieldSet = new \PHPFUI\FieldSet('Member to Add (type first or last name)');
		$memberPicker = new \App\UI\MemberPicker($this->page, new \App\Model\MemberPickerNoSave('Enter Member Name'), 'memberId');
		$fieldSet->add($memberPicker->getEditControl());
		$modalForm->add($fieldSet);
		$modalForm->add(new \PHPFUI\Submit('Add'));
		$modal->add($modalForm);
		}
	}
