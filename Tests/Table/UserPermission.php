<?php

namespace Tests\Table;

class UserPermission extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\UserPermission::class;

	public static function getPermissionsForUser(int $memberId) : \PHPFUI\ORM\DataObjectCursor
		{
		$sql = 'select * from userPermission u,permission p where u.memberId=? and u.permissionGroup=p.permissionId order by p.menu,p.name';

		return \PHPFUI\ORM::getDataObjectCursor($sql, [$memberId]);
		}
	}
