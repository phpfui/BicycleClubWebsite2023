<?php

namespace App\Model;

class Permission extends \App\Model\PermissionBase
	{
	/** @var array<string,int> */
	protected array $permissions = [];

	private int $count = 0;

	private readonly string $permissionLoaderFile;

	private bool $runPermissionLoader = false;

	private bool $setup = false;

	public function __construct()
		{
		$settings = new \App\Settings\DB();

		$this->setup = ($settings->setup ?? true);

		$this->permissionLoaderFile = PROJECT_ROOT . '/permissionLoader.php';

		if (\file_exists($this->permissionLoaderFile))
			{
			include $this->permissionLoaderFile;
			}
		else
			{
			$this->runPermissionLoader = true;
			}

		if (empty($_SESSION['userPermissions']) && ! empty($_SESSION['memberId']))
			{
			$_SESSION['userPermissions'] = $this->getPermissionsForUser($_SESSION['memberId']);
			}
		}

	public function __destruct()
		{
		if ($this->runPermissionLoader)
			{
			$this->generatePermissionLoader();
			}
		}

	public function addGroup() : \App\Record\Permission
		{
		$permissionTable = new \App\Table\Permission();
		$permission = new \App\Record\Permission();
		$permission->permissionId = $permissionTable->getNextGroupId();
		$permission->name = 'New Permission Group Name';
		$permission->menu = 'Permission Group';
		$permission->insert();

		return $permission;
		}

	public function addPermission(string $name, string $menu) : int
		{
		$this->runPermissionLoader = true;
		$permission = new \App\Record\Permission();
		$permission->setFrom(['menu' => $menu, 'name' => $name]);
		$id = $permission->insertOrUpdate();

		if ($id < 100000)
			{
			\PHPFUI\ORM::execute('update permission set permissionId=? where permissionId=?', [100000 + $id, $id]);
			$id = 100000 + $id;
			}

		return $id;
		}

	public function addPermissionToGroup(string $group, string $permissionName, string $menu) : bool
		{
		$permissionId = $this->getPermissionId($permissionName);

		if (! $permissionId)
			{
			$permission = new \App\Record\Permission();
			$permission->menu = $menu;
			$permission->name = $permissionName;
			$permissionId = $permission->insertOrUpdate();
			}

		$permissionGroup = new \App\Record\PermissionGroup();
		$settingTable = new \App\Table\Setting();
		$permissionGroup->groupId = $settingTable->getStandardPermissionGroup('Normal Member')->permissionId;
		$permissionGroup->permissionId = $permissionId;
		$permissionGroup->revoked = 0;

		return $permissionGroup->save();
		}

	public function addPermissionToUser(int $memberId, string $permissionName) : bool
		{
		$permission = $this->getPermissionId($permissionName);

		return \App\Table\UserPermission::addPermissionToUser($memberId, $permission);
		}

	public function deleteGroup(\App\Record\Permission $permission) : static
		{
		$userPermissionTable = new \App\Table\UserPermission();
		$userPermissionTable->setWhere(new \PHPFUI\ORM\Condition('permissionGroup', $permission->permissionId));
		$userPermissionTable->delete();

		$permissionGroupTable = new \App\Table\PermissionGroup();
		$permissionGroupTable->setWhere(new \PHPFUI\ORM\Condition('groupId', $permission->permissionId));
		$permissionGroupTable->delete();

		$permission->delete();

		return $this;
		}

	public function deletePermission(\App\Record\Permission $permission) : static
		{
		$userPermissionTable = new \App\Table\UserPermission();
		$userPermissionTable->setWhere(new \PHPFUI\ORM\Condition('permissionGroup', $permission->permissionId));
		$userPermissionTable->delete();

		$permissionGroupTable = new \App\Table\PermissionGroup();
		$permissionGroupTable->setWhere(new \PHPFUI\ORM\Condition('permissionId', $permission->permissionId));
		$permissionGroupTable->delete();

		$permission->delete();

		return $this;
		}

	public function deletePermissionString(string $permissionName) : int
		{
		$permission = new \App\Record\Permission(['name' => $permissionName]);
		$id = 0;

		if ($permission->loaded())
			{
			$this->deletePermission($permission)->deleteGroup($permission);
			$id = $permission->permissionId;
			}

		return $id;
		}

	public function generatePermissionLoader() : void
		{
		if ($this->setup)
			{
			return;
			}
		$newName = $this->permissionLoaderFile . '.temp';
		$handle = \fopen($newName, 'w');
		\fwrite($handle, "<?php\n");
		$permissions = [];

		$permissionTable = new \App\Table\Permission();

		foreach ($permissionTable->getRecordCursor() as $permission)
			{
			if (! isset($permissions[$permission->name]))
				{
				$permissions[$permission->name] = $permission->permissionId;
				}
			else
				{
				$permission->delete();
				}
			}

		\ksort($permissions);

		foreach ($permissions as $key => $value)
			{
			\fwrite($handle, '$this->permissions[\'' . \str_replace("'", "\\'", $key) . '\'] = ' . $value . ";\n");
			}
		\fclose($handle);
		\App\Tools\File::unlink($this->permissionLoaderFile);
		@\rename($newName, $this->permissionLoaderFile);
		}

	public function generateStandardPermissions() : void
		{
		$csvWriter = new \App\Tools\CSV\FileWriter(PROJECT_ROOT . '/files/standardPermissions.csv', download:false);
		$csvWriter->addHeaderRow();

		$permissionGroupTable = new \App\Table\PermissionGroup();
		$permissionGroupTable->addSelect('groupName.name', 'permissionGroup');
		$permissionGroupTable->addSelect('permission.name', 'permission');
		$permissionGroupTable->addSelect('revoked');
		$permissionGroupTable->addSelect('permission.menu', 'menu');
		$permissionGroupTable->addJoin('permission');
		$permissionGroupTable->addJoin('permission', new \PHPFUI\ORM\Condition(new \PHPFUI\ORM\Literal('groupName.permissionId'), new \PHPFUI\ORM\Literal('permissionGroup.groupId')), as:'groupName');
		$permissionGroupTable->setWhere(new \PHPFUI\ORM\Condition('groupName.system', 1));
		$permissionGroupTable->addOrderBy('groupName.name');
		$permissionGroupTable->addOrderBy('permission.name');

		foreach ($permissionGroupTable->getArrayCursor() as $row)
			{
			$csvWriter->outputRow($row);
			}
		}

	public function getLastQueryCount() : int
		{
		return $this->count;
		}

	public function getPermissionId(string $name) : int
		{
		return $this->permissions[$name] ?? 0;
		}

	/**
	 * @param array<int, int> $permissions array indexed by permissionId with a value enabled (int)
	 *
	 * @return array<int, int> array indexed by permissionId with a value enabled (int)
	 */
	public function getPermissionsForGroup(string|int $group, array $permissions = []) : array
		{
		if (! \is_numeric($group))
			{
			$id = $this->getPermissionId($group);
			}
		else
			{
			$id = $group;
			}

		if ($id < 100000)
			{
			$result = \App\Table\PermissionGroup::getPermissionsForGroup($id);

			foreach ($result as $permission)
				{
				if (! $permission->revoked)
					{
					if (! isset($permissions[$permission->permissionId]))
						{
						$permissions[$permission->permissionId] = 1;
						}
					}
				else
					{
					$permissions[$permission->permissionId] = 0;
					}
				}
			}
		$this->count = \count($permissions);

		return $permissions;
		}

	/**
	 * @return array<int, int> array indexed by permissionId with a value enabled (int)
	 */
	public function getPermissionsForUser(int $memberId) : array
		{
		$permissions = [];

		if ($memberId)
			{
			$result = \App\Table\UserPermission::getPermissionsForUser($memberId);

			foreach ($result as $permission)
				{
				if (! $permission->revoked)
					{
					if (! isset($permissions[$permission->permissionGroup]))
						{
						$permissions[$permission->permissionGroup] = 1;
						}
					}
				else
					{
					$permissions[$permission->permissionGroup] = 0;
					}

				if ($permission->permissionGroup < 100000)
					{
					$permissions = $this->getPermissionsForGroup($permission->permissionGroup, $permissions);
					}
				}
			}
		$this->count = \count($permissions);

		return $permissions;
		}

	public function hasPermission(int $permission) : bool
		{
		if ($this->isSuperUser())
			{
			return true;
			}

		return ! empty($_SESSION['userPermissions'][$permission]);
		}

	public function isAuthorized(string $permissionName, string $menu = '') : bool
		{
		// look up permission in array, will be case sensitive
		$id = $this->getPermissionId($permissionName);

		// not found, we should add it
		if (! $id)
			{
			$this->runPermissionLoader = true;
			$permission = new \App\Record\Permission(['name' => $permissionName]);

			if ($permission->empty())
				{
				// if not found, add it
				$id = $this->addPermission($permissionName, $menu);
				}
			else
				{
				// found, but was not found in array, so it has the wrong case, fix!
				$permission->name = $permissionName;
				$this->runPermissionLoader = true;
				$permission->update();
				}
			}

		return $this->hasPermission($id);
		}

	public function isSuperUser() : bool
		{
		return ! empty($_SESSION['userPermissions'][1]);
		}

	public function loadStandardPermissions() : void
		{
		$csvReader = new \App\Tools\CSV\FileReader(PROJECT_ROOT . '/files/standardPermissions.csv');

		$permission = new \App\Record\Permission();
		$permissionGroupName = new \App\Record\Permission();
		$permissionTable = new \App\Table\Permission();
		$permissionGroupTable = new \App\Table\PermissionGroup();
		$settingTable = new \App\Table\Setting();

		foreach ($csvReader as $row)
			{
			if ($permissionGroupName->name != $row['permissionGroup'])
				{
				$permissionGroupName = $settingTable->getStandardPermissionGroup($row['permissionGroup']);

				if (! $permissionGroupName)
					{
					$permissionGroupName = new \App\Record\Permission(['name' => $row['permissionGroup'], 'system' => 1]);
					}

				if (! $permissionGroupName->loaded())
					{
					$permissionGroupName->permissionId = $permissionTable->getNextGroupId();
					$permissionGroupName->name = $row['permissionGroup'];
					$permissionGroupName->system = 1;
					$permissionGroupName->insert();
					}
				else
					{
					$permissionGroupTable->setWhere(new \PHPFUI\ORM\Condition('groupId', $permissionGroupName->permissionId));
					$permissionGroupTable->delete();
					}
				}

			if ($permission->name != $row['permission'])
				{
				$permission->setEmpty();
				$permission->read(['name' => $row['permission']]);

				if (! $permission->loaded())
					{
					$permission->permissionId = $permissionTable->getNextPermissionId();
					$permission->name = $row['permission'];
					$permission->system = 0;
					$permission->menu = $row['menu'];
					}
				}
			$permissionGroup = new \App\Record\PermissionGroup();
			$permissionGroup->groupId = $permissionGroupName->permissionId;
			$permissionGroup->permission = $permission;
			$permissionGroup->revoked = (int)$row['revoked'];
			$permissionGroup->insertOrUpdate();
			}

		$this->generatePermissionLoader();
		}

	public function removePermissionFromUser(int $memberId, string $permissionName) : bool
		{
		$permission = $this->getPermissionId($permissionName);

		return \App\Table\UserPermission::removePermissionFromUser($memberId, $permission);
		}

	public function revokePermissionForUser(int $memberId, string $permissionName) : bool
		{
		$permission = $this->getPermissionId($permissionName);

		return \App\Table\UserPermission::revokePermissionForUser($memberId, $permission);
		}

	/**
	 * @param array<string,string|array<int>> $parameters
	 */
	public function saveGroup(array $parameters) : void
		{
		$group = ['groupId' => $parameters['groupId']];
		$permissionGroupTable = new \App\Table\PermissionGroup();
		$permissionGroupTable->setWhere(new \PHPFUI\ORM\Condition('groupId', $parameters['groupId']));
		$permissionGroupTable->delete();

		$revoked = [];

		foreach ($parameters['revokedIds'] ?? [] as $permissionId)
			{
			$revoked[$permissionId] = true;
			}

		foreach ($parameters['permissionId'] ?? [] as $permissionId)
			{
			$group['permissionId'] = $permissionId;
			$group['revoked'] = $revoked[$permissionId] ?? 0;
			$permissionGroup = new \App\Record\PermissionGroup();
			$permissionGroup->setFrom($group);
			$permissionGroup->insert();
			unset($permissionGroup);
			}

		foreach ($revoked as $permissionId => $junk)
			{
			$permissionId = (int)$permissionId;
			$group['permissionId'] = $permissionId;
			$group['revoked'] = 1;
			$permissionGroup = new \App\Record\PermissionGroup();
			$permissionGroup->setFrom($group);
			$permissionGroup->insert();
			unset($permissionGroup);
			}

		$parameters['permissionId'] = $parameters['groupId'];
		$parameters['menu'] = 'Permission Group';
		$permission = new \App\Record\Permission();
		$permission->setFrom($parameters);
		$permission->insertOrUpdate();
		}

	/**
	 * @param array<string,string|array<int>> $parameters
	 */
	public function saveMember(array $parameters) : void
		{
		$member = ['memberId' => $parameters['memberId']];
		$userPermissionTable = new \App\Table\UserPermission();
		$userPermissionTable->setWhere(new \PHPFUI\ORM\Condition('memberId', $parameters['memberId']));
		$userPermissionTable->delete();

		$revoked = [];

		foreach ($parameters['revokedIds'] ?? [] as $permissionId)
			{
			$permissionId = (int)$permissionId;
			$revoked[$permissionId] = true;
			}

		foreach ($parameters['groups'] ?? [] as $permissionId)
			{
			$permissionId = (int)$permissionId;
			$member['permissionGroup'] = $permissionId;
			$member['revoked'] = 0;
			$permission = new \App\Record\UserPermission();
			$permission->setFrom($member);
			$permission->insertOrUpdate();
			unset($permission);
			}

		foreach ($parameters['additionalIds'] ?? [] as $permissionId)
			{
			$permissionId = (int)$permissionId;
			$member['permissionGroup'] = $permissionId;
			$member['revoked'] = $revoked[$permissionId] ?? 0;
			$permission = new \App\Record\UserPermission();
			$permission->setFrom($member);
			$permission->insertOrUpdate();
			unset($permission);
			}

		foreach ($revoked as $permissionId => $junk)
			{
			$permissionId = (int)$permissionId;
			$member['permissionGroup'] = $permissionId;
			$member['revoked'] = 1;
			$permission = new \App\Record\UserPermission();
			$permission->setFrom($member);
			$permission->insertOrUpdate();
			unset($permission);
			}
		}
	}
