<?php

namespace App\Table;

class PermissionGroup extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\PermissionGroup::class;

	public static function getGroupPermissions($group) : iterable
		{
		$sql = 'select * from permissionGroup g,permission n where g.groupId=? and g.permissionId=n.permissionId order by n.menu,n.name';

		return \PHPFUI\ORM::getDataObjectCursor($sql, [$group]);
		}

	public static function getGroupsWithPermission(int $id) : iterable
		{
		return \PHPFUI\ORM::getArrayCursor('select * from permissionGroup where permissionId=?', [$id]);
		}

	public static function getPermissionsForGroup($groupId) : iterable
		{
		return \PHPFUI\ORM::getArrayCursor('select * from permissionGroup where groupId=? order by permissionId desc', [$groupId]);
		}
	}
