<?php

namespace App\Table;

class PermissionGroup extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\PermissionGroup::class;

	public static function getGroupPermissions(int $groupId) : \PHPFUI\ORM\DataObjectCursor
		{
		$sql = 'select * from permissionGroup g,permission n where g.groupId=? and g.permissionId=n.permissionId order by n.menu,n.name';

		return \PHPFUI\ORM::getDataObjectCursor($sql, [$groupId]);
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\PermissionGroup>
	 */
	public static function getPermissionsForGroup(int $groupId) : \PHPFUI\ORM\RecordCursor
		{
		return \PHPFUI\ORM::getRecordCursor(new \App\Record\PermissionGroup(), 'select * from permissionGroup where groupId=? order by permissionId desc', [$groupId]);
		}
	}
