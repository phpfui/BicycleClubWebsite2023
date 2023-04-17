<?php

namespace App\Model;

class AllPermissions implements \App\Model\PermissionsInterface
	{
	public function addPermission(string $permission, string $menu) : int
		{
		return 0;
		}

	public function addPermissionToUser(int $user, string $permission) : bool
		{
		return true;
		}

	public function deletePermissionString(string $permission) : int
		{
		return 0;
		}

	public function getPermissionId(string $name) : int
		{
		return 0;
		}

	/**
	 *
	 * @psalm-return array<empty, empty>
	 */
	public function getPermissionsForGroup($group, array $permissions = []) : array
		{
		return [];
		}

	/**
	 *
	 * @psalm-return array<empty, empty>
	 */
	public function getPermissionsForUser(int $memberId) : array
		{
		return [];
		}

	public function isAuthorized(string $permission, string $menu = '') : bool
		{
		return true;
		}

	public function isSuperUser() : bool
		{
		return true;
		}

	public function removePermissionFromUser(int $user, string $permission) : bool
		{
		return true;
		}

	public function revokePermissionForUser(int $user, string $permission) : bool
		{
		return true;
		}

	public function generatePermissionLoader() : void
		{
		}

	public function addGroup() : \App\Record\Permission
		{
		return new \App\Record\Permission();
		}

	public function saveMember(array $parameters) : void
		{
		}

	public function saveGroup(array $parameters) : void
		{
		}

	public function deleteGroup(\App\Record\Permission $permission) : static
		{
		return $this;
		}

	public function deletePermission(\App\Record\Permission $permission) : static
		{
		return $this;
		}
	}
