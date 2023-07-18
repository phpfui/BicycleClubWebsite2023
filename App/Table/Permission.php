<?php

namespace App\Table;

class Permission extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\Permission::class;

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\Permission>
	 */
	public function getAllPermissionGroups() : \PHPFUI\ORM\RecordCursor
		{
		$this->setWhere(new \PHPFUI\ORM\Condition('permissionId', 100000, new \PHPFUI\ORM\Operator\LessThan()));
		$this->setOrderBy('name');

		return $this->getRecordCursor();
		}

	public function getAllPermissions(string $column = 'name', string $sort = 'a', int $page = 0, int $limit = 0) : \PHPFUI\ORM\ArrayCursor
		{
		$sort = 'd' == $sort ? 'desc' : '';
		$column .= 'menu' == $column ? " {$sort},name {$sort}" : " {$sort}";
		$sql = "select * from permission where permissionId > 100000 order by {$column}";

		if ($limit)
			{
			$start = $page * $limit;
			$sql .= " limit {$start},{$limit}";
			}

		return \PHPFUI\ORM::getArrayCursor($sql);
		}

	public function getAllPermissionsCount() : int
		{
		$sql = 'select count(*) from permission where permissionId > 100000';

		return (int)\PHPFUI\ORM::getValue($sql);
		}

	public function getMembersWithPermissionGroup(string $name) : ?\PHPFUI\ORM\RecordCursor
		{
		$settingTable = new \App\Table\Setting();
		$permission = $settingTable->getStandardPermissionGroup($name);

		if (! $permission->name)
			{
			return null;
			}

		$id = $permission->permissionId;
		$sql = 'select * from member left join userPermission on member.memberId=userPermission.memberId where permissionGroup=? and revoked=0';

		return \PHPFUI\ORM::getRecordCursor(new \App\Record\Member(), $sql, [$id]);
		}

	public function getNextGroupId() : int
		{
		$sql = 'select permissionId from permission where permissionId < 100000 order by permissionId desc limit 1';

		return (int)(\PHPFUI\ORM::getValue($sql)) + 1;
		}

	public function getNextPermissionId() : int
		{
		$sql = 'select permissionId from permission where permissionId > 100000 order by permissionId desc limit 1';

		$retVal = (int)(\PHPFUI\ORM::getValue($sql));

		if ($retVal < 100000)
			{
			$retVal = 100000;
			}

		return $retVal + 1;
		}

	public function rename(string $currentName, string $newName) : static
		{
		$this->setWhere(new \PHPFUI\ORM\Condition('name', $currentName));
		$this->update(['name' => $newName]);
		$this->setWhere();

		return $this;
		}
	}
