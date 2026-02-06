<?php

namespace App\View;

class Password
	{
	private readonly \App\Model\Member $memberModel;

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->memberModel = new \App\Model\Member();
		}

	public function password(\App\Record\Member $member) : \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit('Save', 'changePassword');
		$form = new \PHPFUI\Form($this->page);

		if ($form->isMyCallback($submit))
			{
			\App\Model\Session::setFlash('post', $_POST);
			$errors = \App\Model\PasswordPolicy::validate($_POST['password']);

			if ($errors)
				{
				\App\Model\Session::setFlash('alert', $errors);
				$this->page->redirect();
				}
			elseif ($this->page->isAuthorized('Reset Any Password') || \App\Model\PasswordPolicy::verifyPassword($_POST['current'], $member))
				{
				$member->password = \App\Model\PasswordPolicy::hashPassword($_POST['password']);
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
			$passwordPolicy = new \App\Model\PasswordPolicy();
			$errors = $passwordPolicy->validate($_POST['password'] ?? '');

			if (isset($_POST['confirm'], $_POST['password']) && $_POST['confirm'] === $_POST['password'] && ! $errors) // @mago-expect lint:no-insecure-comparison
				{
				$member->password = \App\Model\PasswordPolicy::hashPassword($_POST['password']);
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
	}
