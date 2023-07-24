<?php

namespace App\Table;

class UserPermission extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\UserPermission::class;

	public static function addPermissionToUser(int $memberId, int $permission) : bool
		{
		if (! $memberId || ! $permission)
			{
			return false;
			}

		$key = [$memberId, $permission, ];
		\PHPFUI\ORM::execute('insert into userPermission (memberId, permissionGroup) VALUES (?,?) on duplicate key update revoked=0', $key);

		return true;
		}

	public static function deletePermissionsForMember(int $number) : void
		{
		\PHPFUI\ORM::execute('delete from userPermission where memberId=?', [$number]);
		}

	public static function deletePermissionsForMembership(int $membershipId) : void
		{
		$sql = 'delete from userPermission where memberId in (select memberId from member where membershipId=?)';
		\PHPFUI\ORM::execute($sql, [$membershipId]);
		}

	public static function forMember(int $memberId) : \PHPFUI\ORM\DataObjectCursor
		{
		$sql = 'select * from userPermission u,permission p where u.memberId=? and u.permissionGroup=p.permissionId order by p.menu,p.name';

		return \PHPFUI\ORM::getDataObjectCursor($sql, [$memberId]);
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\UserPermission>
	 */
	public static function getPermissionsForUser(int $memberId) : \PHPFUI\ORM\RecordCursor
		{
		return \PHPFUI\ORM::getRecordCursor(new \App\Record\UserPermission(), 'select u.* from userPermission u left join permission p on p.permissionId=u.permissionGroup where u.memberId=?', [$memberId]);
		}

	public static function removePermissionFromUser(int $memberId, int $permission) : bool
		{
		return \PHPFUI\ORM::execute(
			'delete from userPermission where memberId=? and permissionGroup=?',
			[$memberId, $permission, ]
		);
		}

	public static function revokePermissionForUser(int $memberId, int $permission) : bool
		{
		if (! $memberId || ! $permission)
			{
			return false;
			}

		$key = [$memberId, $permission, 1];

		return \PHPFUI\ORM::execute('insert into userPermission (memberId, permissionGroup, revoked) VALUES (?,?,?) on duplicate key update revoked=1', $key);
		}
	}
