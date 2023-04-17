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

	public static function deletePermissionsForMembership($number) : void
		{
		$sql = 'delete from userPermission where memberId in (select memberId from member where membershipId=?)';
		\PHPFUI\ORM::execute($sql, [$number]);
		}

	public static function forMember($memberId) : iterable
		{
		$sql = 'select * from userPermission u,permission p where u.memberId=? and u.permissionGroup=p.permissionId order by p.menu,p.name';

		return \PHPFUI\ORM::getDataObjectCursor($sql, [$memberId]);
		}

	/**
	 * @return array<array> array of userPermissons with name and revoked
	 */
	public static function getPermissionsForUser($memberId) : iterable
		{
		return \PHPFUI\ORM::getRows('select * from userPermission u left join permission p on p.permissionId=u.permissionGroup where u.memberId=?', [$memberId]);
		}

	public static function removePermissionFromUser(int $memberId, int $permission) : bool
		{
		\PHPFUI\ORM::execute(
			'delete from userPermission where memberId=? and permissionGroup=?',
			[$memberId, $permission, ]
		);

		return true;
		}

	public static function revokePermissionForUser(int $memberId, int $permission) : bool
		{
		if (! $memberId || ! $permission)
			{
			return false;
			}

		$key = [$memberId, $permission, 1];
		\PHPFUI\ORM::execute('insert into userPermission (memberId, permissionGroup, revoked) VALUES (?,?,?) on duplicate key update revoked=1', $key);

		return true;
		}
	}
