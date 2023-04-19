<?php

namespace App\View;

class Permissions
	{
	private readonly \App\Model\PermissionsInterface $permissionModel;

	private readonly \App\Table\Permission $permissionTable;

	private readonly \App\Table\UserPermission $userPermissionTable;

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->permissionTable = new \App\Table\Permission();
		$this->permissionModel = $this->page->getPermissions();
		$this->userPermissionTable = new \App\Table\UserPermission();
		$this->processAJAXRequest();
		}

	public function editMember(int $memberId) : \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit();
		$form = new \PHPFUI\Form($this->page, $submit);

		if ($form->isMyCallback())
			{
			$this->permissionModel->saveMember($_POST);
			$this->page->setResponse('Saved');
			}
		else
			{
			$form->add(new \PHPFUI\Input\Hidden('memberId', (string)$memberId));
			$permissions = $this->page->getPermissions();
			$result = $this->permissionTable->getAll();

			if (! $permissions->isAuthorized('Super User'))
				{
				$userPermissions = $permissions->getPermissionsForUser(\App\Model\Session::signedInMemberId());
				$newResult = [];

				foreach ($result as $permission)
					{
					if (isset($userPermissions[$permission['permissionId']]))
						{
						$newResult[] = $permission;
						}
					}
				$result = $newResult;
				}
			$allPermissions = $allGroups = [];

			foreach ($result as $permission)
				{
				$id = $permission['permissionId'];

				if ($id >= 100000)
					{
					$allPermissions[$id] = $permission;
					}
				else
					{
					$allGroups[$id] = $permission;
					}
				}
			$permissions = $this->userPermissionTable->getPermissionsForUser($memberId);
			$notAdditional = $allPermissions;
			$notInRevoked = $allPermissions;
			$notInGroup = $allGroups;
			$additional = $inRevoked = $inGroup = [];

			foreach ($permissions as $permission)
				{
				$permissionId = $permission['permissionGroup'];

				if ($permission['revoked'])
					{
					$inRevoked[] = $permission;
					unset($notInRevoked[$permissionId]);
					}
				elseif ($permissionId < 100000)
					{
					$inGroup[] = $permission;
					unset($notInGroup[$permissionId]);
					}
				else
					{
					$additional[] = $permission;
					unset($notAdditional[$permissionId]);
					}
				}
			$tabs = new \PHPFUI\Tabs();
			$index = 'permissionId';
			$callback = $this->getGroupName(...);
			$sortCallback = $this->permissionSort(...);
			\usort($notInGroup, $sortCallback);
			\usort($inGroup, $sortCallback);
			$groupToFromList = new \PHPFUI\ToFromList($this->page, 'groups', $inGroup, $notInGroup, $index, $callback);
			$groupToFromList->setInName('Groups');
			$groupToFromList->setOutName('Available');
			$tabs->addTab('Groups', $groupToFromList, true);
			\usort($additional, $sortCallback);
			\usort($notAdditional, $sortCallback);
			$allowedToFromList = new \PHPFUI\AccordionToFromList(
				$this->page,
				'additionalIds',
				$this->groupByMenu($additional),
				$this->groupByMenu($notAdditional),
				$index,
				$callback
			);
			$allowedToFromList->setInName('Allowed');
			$allowedToFromList->setOutName('Available');
			$tabs->addTab('Additional', $allowedToFromList);
			\usort($inRevoked, $sortCallback);
			\usort($notInRevoked, $sortCallback);
			$revokedToFromList = new \PHPFUI\AccordionToFromList(
				$this->page,
				'revokedIds',
				$this->groupByMenu($inRevoked),
				$this->groupByMenu($notInRevoked),
				$index,
				$callback
			);
			$revokedToFromList->setInName('Revoked');
			$revokedToFromList->setOutName('Available');
			$tabs->addTab('Revoked', $revokedToFromList);
			$form->add($tabs);
			$buttonGroup = new \PHPFUI\ButtonGroup();
			$buttonGroup->addButton($submit);
			$form->add($buttonGroup);
			}

		return $form;
		}

	public function editPermissionGroup(\App\Record\Permission $name = new \App\Record\Permission()) : \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit();
		$form = new \PHPFUI\Form($this->page, $submit);

		$readOnly = $name->system && ! $this->page->getPermissions()->isSuperUser();

		if (! $readOnly && $form->isMyCallback())
			{
			if ($name->loaded())
				{
				$this->permissionModel->saveGroup($_POST);
				}
			$this->page->setResponse('Saved');
			}
		else
			{
			if ($name->empty())
				{
				$form->add(new \PHPFUI\SubHeader('Group not found'));

				return $form;
				}

			$groupId = new \PHPFUI\Input\Hidden('groupId', (string)$name->permissionId);
			$form->add($groupId);

			if ($readOnly)
				{
				$groupName = new \App\UI\Display('Name', $name->name);
				}
			else
				{
				$groupName = new \PHPFUI\Input\Text('name', 'Permission Group Name', $name->name);
				$groupName->setRequired()->setToolTip("Provide a descriptive name that describes that users with this group can do (Example: 'Membership Chair' or 'Content Editor')");
				}
			$multiColumn = new \PHPFUI\MultiColumn($groupName);

			if ($this->page->getPermissions()->isSuperUser())
				{
				$system = new \PHPFUI\Input\CheckBoxBoolean('system', 'System Updated', (bool)$name->system);
				$system->setToolTip('Should be ckecked if this is a system updated permission group');
				$multiColumn->add($system);
				}
			elseif ($readOnly)
				{
				$multiColumn->add(new \App\UI\Display('Read Only', 'True'));
				}
			$form->add($multiColumn);
			$permissions = $this->permissionTable->getAllPermissions('menu');
			$groupPermissions = $this->page->getPermissions()->getPermissionsForGroup($name->permissionId);
			$inRevokedGroup = $notInRevokedGroup = $inGroup = $notInGroup = [];

			foreach ($permissions as $permission)
				{
				$permissionId = $permission['permissionId'];

				if (isset($groupPermissions[$permissionId]) && ! $groupPermissions[$permissionId])
					{
					$revoked = true;
					$inRevokedGroup[] = $permission;
					}
				else
					{
					$revoked = false;
					$notInRevokedGroup[] = $permission;
					}

				if (! $revoked && ! empty($groupPermissions[$permissionId]))
					{
					$inGroup[] = $permission;
					}
				else
					{
					$notInGroup[] = $permission;
					}
				}
			$callback = $this->getGroupName(...);
			$index = 'permissionId';
			$allowedToFromList = new \PHPFUI\AccordionToFromList(
				$this->page,
				'permissionId',
				$this->groupByMenu($inGroup),
				$this->groupByMenu($notInGroup),
				$index,
				$callback
			);

			if ($readOnly)
				{
				$allowedToFromList->setReadOnly();
				}

			$allowedToFromList->setInName('Allowed');
			$allowedToFromList->setOutName('Available');
			$tabs = new \PHPFUI\Tabs();
			$tabs->addTab('Allowed', $allowedToFromList, true);
			$revokedToFromList = new \PHPFUI\AccordionToFromList(
				$this->page,
				'revokedIds',
				$this->groupByMenu($inRevokedGroup),
				$this->groupByMenu($notInRevokedGroup),
				$index,
				$callback
			);

			if ($readOnly)
				{
				$revokedToFromList->setReadOnly();
				}

			$revokedToFromList->setInName('Revoked');
			$revokedToFromList->setOutName('Available');
			$tabs->addTab('Revoked', $revokedToFromList);
			$form->add($tabs);

			if (! $readOnly)
				{
				$buttonGroup = new \PHPFUI\ButtonGroup();
				$buttonGroup->addButton($submit);
				$form->add($buttonGroup);
				}
			}

		return $form;
		}

	public function getAllGroups() : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$this->permissionTable->setWhere(new \PHPFUI\ORM\Condition('permissionId', 100000, new \PHPFUI\ORM\Operator\LessThan()));
		$this->permissionTable->setOrderBy('name');

		$searchHeaders = ['name' => 'Permission Group', 'system' => 'System Updated'];
		$normalHeaders = ['members' => 'Members',
			'edit' => 'Edit',
			'del' => 'Del', ];

		$view = new \App\UI\ContinuousScrollTable($this->page, $this->permissionTable);
		$deleter = new \App\Model\DeleteRecord($this->page, $view, $this->permissionTable, 'Are you sure you want to permanently delete this permission group?');
		$view->addCustomColumn('del', $deleter->columnCallback(...));
		new \App\Model\EditIcon($view, $this->permissionTable, '/Admin/groupEdit/');
		$view->addCustomColumn('members', static fn (array $permission) => new \PHPFUI\FAIcon('fas', 'users', '/Admin/groupMembers/' . $permission['permissionId']));
//		$view->addCustomColumn('system', static function(array $permission) { return $permission['system'] ? 'Yes' : 'No';});

		$view->setHeaders(\array_merge($searchHeaders, $normalHeaders));
		$view->setSearchColumns($searchHeaders);
		$view->setSortableColumns(\array_keys($searchHeaders));

		if ($this->page->isAuthorized('Add Permission Group'))
			{
			$add = new \PHPFUI\Button('Add Permission Group', '/Admin/addGroup');
			$container->add($add);
			$container->add($view);
			$container->add($add);
			}
		else
			{
			$container->add($view);
			}

		return $container;
		}

	public function getAllPermissions() : \App\UI\ContinuousScrollTable
		{
		$permissionTable = new \App\Table\Permission();
		$view = new \App\UI\ContinuousScrollTable($this->page, $permissionTable);
		$view->addCustomColumn('members', static fn (array $row) => new \PHPFUI\FAIcon('fas', 'users', '/Admin/permissionMembers/' . $row['permissionId']));
		$headers = ['name' => 'Permission Name', 'menu' => 'Menu', 'members' => 'Members'];

		if ($this->page->isAuthorized('Delete Permission'))
			{
			$deleter = new \App\Model\DeleteRecord($this->page, $view, $permissionTable, 'Permanently delete this permission? It will come back if in use, but will not be assigned to anyone.');
			$view->addCustomColumn('del', $deleter->columnCallback(...));
			$headers['del'] = 'Delete';
			}
		$view->setSearchColumns(['name', 'menu'])->setHeaders($headers)->setSortableColumns(['name', 'menu']);

		return $view;
		}

	public function getGroupName(string $fieldName, string $index, $permission, string $type) : string
		{
		if (! \is_array($permission))
			{
			$permissionName = new \App\Record\Permission($permission);
			$permission = $permissionName->toArray();
			}
		$menu = $permission['menu'] ?? '';

		if (\strlen((string)$menu))
			{
			$menu = "<b>{$menu}</b> - ";
			}

		if ('in' == $type)
			{
			$type = '';
			}
		$hidden = new \PHPFUI\Input\Hidden($type . $fieldName . '[]', $permission[$index] ?? 0);

		return $hidden . $menu . ($permission['name'] ?? '');
		}

	public function membersWithPermission(\App\Record\Permission $permission) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		if (\App\Model\Session::checkCSRF())
			{
			if ('Add' == ($_POST['submit'] ?? '') && ! empty($_POST['memberId']))
				{
				\App\Table\UserPermission::addPermissionToUser($_POST['memberId'], $permission->permissionId);
				$this->page->redirect();
				}
			elseif ('deleteMember' == ($_POST['action'] ?? '') && ! empty($_POST['permissionGroup']))
				{
				$userPermission = new \App\Record\UserPermission();
				$userPermission->setFrom($_POST);
				$userPermission->delete();
				$this->page->setResponse($_POST['memberId']);

				return $container;
				}
			}

		if ($permission->loaded())
			{
			$memberTable = new \App\Table\Member();

			$memberTable->setMembersWithPermissionId($permission->permissionId);

			$headers = ['firstName', 'lastName'];

			$view = new \App\UI\ContinuousScrollTable($this->page, $memberTable);
			new \App\Model\EditIcon($view, $memberTable, '/Membership/permissionEdit/');

			$view->setSearchColumns($headers)->setHeaders(\array_merge($headers, ['edit', 'remove']))->setSortableColumns($headers);

			$functionName = 'deleteMember';
			$view->setRecordId('memberId');
			$delete = new \PHPFUI\AJAX('deleteMember');
			$delete->addFunction('success', "$('#memberId-'+data.response).css('background-color','red').hide('fast')");
			$this->page->addJavaScript($delete->getPageJS());
			$view->addCustomColumn('remove', static function(array $member) use ($delete, $permission)
				{
				$trash = new \PHPFUI\FAIcon('far', 'trash-alt', '#');
				$trash->addAttribute('onclick', $delete->execute(['memberId' => $member['memberId'], 'permissionGroup' => $permission->permissionId]));

				return $trash;
				});

			$add = new \PHPFUI\Button('Add Member With This Permission');
			$this->getAddMemberModal($add);
			$container->add($add);

			$container->add($view);
			}
		else
			{
			$container->add(new \PHPFUI\SubHeader('Permission Not Found'));
			}

		return $container;
		}

	public function permissionSort(array $lhs, array $rhs) : int
		{
		if (! $returnValue = \strcmp($lhs['menu'] ?? '', $rhs['menu'] ?? ''))
			{
			$returnValue = \strcmp($lhs['name'] ?? '', $rhs['name'] ?? '');
			}

		return $returnValue;
		}

	protected function processAJAXRequest() : void
		{
		if (\App\Model\Session::checkCSRF() && isset($_POST['action']))
			{
			switch ($_POST['action'])
				{
				case 'deleteGroup':

					$this->permissionModel->deleteGroup(new \App\Record\Permission((int)$_POST['permissionId']));
					$this->page->setResponse($_POST['permissionId']);

					break;


				case 'deletePermission':

					$this->permissionModel->deletePermission(new \App\Record\Permission((int)$_POST['permissionId']));
					$this->page->setResponse($_POST['permissionId']);

					break;


				case 'Add':

					$permission = $this->permissionModel->addGroup();
					$this->page->redirect('/Admin/groupEdit/' . $permission->permissionId);

					break;

				}
			}
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

	private function groupByMenu(array $permissions) : array
		{
		$grouped = [];

		foreach ($permissions as $permission)
			{
			$grouped[$permission['menu'] ?: 'Global'][] = $permission;
			}

		return $grouped;
		}
	}
