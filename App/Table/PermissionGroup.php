<?php

namespace App\Table;

class PermissionGroup extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\PermissionGroup::class;

	public function getGroupPermissions(int $groupId) : \PHPFUI\ORM\DataObjectCursor
		{
		$this->setJoin('permission');
		$this->setWhere(new \PHPFUI\ORM\Condition('groupId', $groupId));
		$this->setOrderBy('menu');
		$this->addOrderBy('name');

		return $this->getDataObjectCursor();
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\PermissionGroup>
	 */
	public function getPermissionsForGroup(int $groupId) : \PHPFUI\ORM\RecordCursor
		{
		$this->setOrderBy('permissionId', 'desc');
		$this->setWhere(new \PHPFUI\ORM\Condition('groupId', $groupId));

		return $this->getRecordCursor();
		}
	}
