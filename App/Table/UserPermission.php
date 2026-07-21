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

	public function getPermissionsForUser(int $memberId) : \PHPFUI\ORM\DataObjectCursor
		{
		$this->setJoin('permission', new \PHPFUI\ORM\Condition('permissionId', new \PHPFUI\ORM\Literal('userPermission.permissionGroup')));
		$this->setWhere(new \PHPFUI\ORM\Condition('memberId', $memberId));
		$this->setOrderBy('menu');
		$this->addOrderBy('name');

		return $this->getDataObjectCursor();
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
