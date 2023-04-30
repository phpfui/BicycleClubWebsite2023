<?php

namespace App\View\System;

class API
	{
	private readonly array $tables;

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->tables = \PHPFUI\ORM\Table::getAllTables();
		}

	public function edit(\App\Record\OauthUser $user = new \App\Record\OauthUser()) : \PHPFUI\Form
		{
		if ($user->oauthUserId)
			{
			$submit = new \PHPFUI\Submit();
			$form = new \PHPFUI\Form($this->page, $submit);

			if ($form->isMyCallback($submit))
				{
				$user->setFrom($_POST);
				$user->update();
				$this->page->setResponse('Saved');

				return $form;
				}
			elseif (\App\Model\Session::checkCSRF() && ($_POST['submit'] ?? '') == 'Change Password')
				{
				$user->setPassword($_POST['password']);
				$user->update();

				$this->page->redirect();
				}
			}
		else
			{
			$submit = new \PHPFUI\Submit('Add User');
			$form = new \PHPFUI\Form($this->page);

			if (\App\Model\Session::checkCSRF() && ($_POST['submit'] ?? '') == $submit->getText())
				{
				$user->setFrom($_POST);
				$user->oauthUserId = 0;
				$user->insert();

				$this->page->redirect('/System/API/edit/' . $user->oauthUserId);

				return $form;
				}
			}

		if (! $user->password)
			{
			$callout = new \PHPFUI\Callout('alert');
			$callout->add('You must set a password for this user.');
			$form->add($callout);
			}

		$fieldSet = new \PHPFUI\FieldSet('User Info');
		$fieldSet->add(new \App\UI\Display('Last Login', $user->lastLogin ?? 'None'));
		$fieldSet->add(new \App\UI\Display('Has Password', $user->password ? 'Yes' : 'No'));

		$memberPicker = new \App\UI\MemberPicker($this->page, new \App\Model\MemberPickerNoSave('Member Associated with this API User'), 'memberId', $user->member->toArray());
		$fieldSet->add($memberPicker->getEditControl());

		$userName = new \PHPFUI\Input\Text('userName', 'User Name', $user->userName);
		$userName->setRequired()->setToolTip('This is the user name the person will use to sign in with to the API. It can be any text or email address. It is a good idea to make it descriptive so you know what it is being used for');
		$fieldSet->add($userName);

		if ($user->oauthUserId)
			{
			$changePasswordButton = new \PHPFUI\Button('Set Password');
			$this->addPasswordReveal($user, $changePasswordButton);

			$buttonGroup = new \PHPFUI\ButtonGroup();

			if ($user->password)
				{
				$changePasswordButton->addClass('warning');
				}
			else
				{
				$changePasswordButton->addClass('success');
				}
			$buttonGroup->addButton($changePasswordButton);

			$editPermissionsButton = new \PHPFUI\Button('Permissions', '/System/API/permissions/' . $user->oauthUserId);
			$editPermissionsButton->addClass('info');
			$buttonGroup->addButton($editPermissionsButton);

			$fieldSet->add($buttonGroup);
			}

		$form->add($fieldSet);

		$buttonGroup = new \PHPFUI\ButtonGroup();
		$buttonGroup->addButton($submit);
		$backButton = new \PHPFUI\Button('API Users', '/System/API/users');
		$backButton->addClass('hollow secondary');
		$buttonGroup->addButton($backButton);

		$form->add($buttonGroup);

		return $form;

		}

	public function editPermissions(\App\Record\OauthUser $user) : \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit();
		$form = new \PHPFUI\Form($this->page, $submit);

		if ($form->isMyCallback($submit))
			{
			$post = $_POST;
			unset($post['csrf'], $post['submit']);
			$user->setPermissions($post);
			$user->update();
			$this->page->setResponse('Saved');

			return $form;
			}
		$data = $user->getPermissions();
		$member = $user->member;

		$form->add(new \PHPFUI\SubHeader('API Permissions for ' . $member->fullName()));
		$table = new \PHPFUI\Table();
		$crud = ['POST' => 'Create', 'GET' => 'Read', 'PUT' => 'Update', 'DELETE' => 'Delete'];
		$table->setHeaders(\array_merge(['Table'], \array_values($crud)));

		$row = [];
		$row['Table'] = '<b>Select entire column</b>';

		foreach ($crud as $column)
			{
			$row[$column] = new \App\UI\CheckAll('.' . $column, '');
			}
		$table->addRow($row);

		foreach ($this->tables as $tableObject)
			{
			$row = [];
			$name = $tableObject->getTableName();
			$row['Table'] = \ucfirst((string)$name);

			foreach ($crud as $type => $column)
				{
				$cb = new \PHPFUI\Input\CheckBox($name . '[' . $type . ']', '', 1);

				if (isset($data[$name][$type]))
					{
					$cb->addAttribute('checked');
					}
				$cb->addClass($column);
				$row[$column] = $cb;
				}
			$table->addRow($row);
			}

		$buttonGroup = new \PHPFUI\ButtonGroup();
		$buttonGroup->addButton($submit);
		$editMemberButton = new \PHPFUI\Button('Edit User', '/System/API/edit/' . $user->oauthUserId);
		$editMemberButton->addClass('info');
		$buttonGroup->addButton($editMemberButton);

		$backButton = new \PHPFUI\Button('API Users', '/System/API/users');
		$backButton->addClass('hollow secondary');
		$buttonGroup->addButton($backButton);

		$form->add($buttonGroup);
		$form->add($table);

		return $form;
		}

	public function list(\App\Table\OauthUser $table) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		if (\count($table))
			{
			$view = new \App\UI\ContinuousScrollTable($this->page, $table);
			$deleter = new \App\Model\DeleteRecord($this->page, $view, $table, 'Are you sure you want to permanently delete this user?');
			$view->addCustomColumn('del', $deleter->columnCallback(...));
			$view->addCustomColumn('member', static function(array $row) {$member = new \App\Record\Member($row['memberId']);

				return $member->fullName();});
			$view->addCustomColumn('edit', static fn (array $row) => new \PHPFUI\FAIcon('fas', 'edit', '/System/API/edit/' . $row['oauthUserId']));
			$view->addCustomColumn('passwordSet', static fn (array $row) => $row['password'] ? 'Yes' : 'No');
			$view->addCustomColumn('permissions', static fn (array $row) => new \PHPFUI\FAIcon('fas', 'check-square', '/System/API/permissions/' . $row['oauthUserId']));
			$headers = ['userName', 'lastLogin'];
			$view->setSearchColumns($headers)->setHeaders(\array_merge($headers, ['member', 'passwordSet', 'edit', 'permissions', 'del']))->setSortableColumns($headers);
			$container->add($view);
			}
		else
			{
			$container->add(new \PHPFUI\SubHeader('No Users Found'));
			}

		return $container;
		}

	private function addPasswordReveal(\App\Record\OauthUser $user, \PHPFUI\HTML5Element $button) : void
		{
		$modal = new \PHPFUI\Reveal($this->page, $button);
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$fieldSet = new \PHPFUI\FieldSet('Change Password');
		$fieldSet->add(new \PHPFUI\Input\Hidden('oauthUserId', (string)$user->oauthUserId));

		$current = new \PHPFUI\Input\PasswordEye('password', 'New Password');
		$current->setRequired();
		$current->setToolTip('The password should be 8 characters long, have letters, numbers and punctuation');
		$fieldSet->add($current);
		$confirm = new \PHPFUI\Input\PasswordEye('confirm', 'Confirm Password');
		$confirm->addAttribute('data-equalto', $current->getId());
		$confirm->addErrorMessage('Must be the same as the new password.');
		$confirm->setRequired();
		$confirm->setToolTip('You must enter the same password twice to make sure it is correct');
		$fieldSet->add($confirm);
		$form->add($fieldSet);
		$form->add(new \PHPFUI\Submit('Change Password'));
		$modal->add($form);
		}
	}
