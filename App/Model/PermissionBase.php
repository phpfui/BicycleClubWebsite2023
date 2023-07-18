<?php

namespace App\Model;

class PermissionBase
	{
	public function addGroup() : \App\Record\Permission
		{
		return new \App\Record\Permission();
		}

	public function addPermission(string $permission, string $menu) : int
		{
		return 0;
		}

	public function addPermissionToUser(int $user, string $permission) : bool
		{
		return true;
		}

	public function deleteGroup(\App\Record\Permission $permission) : static
		{
		return $this;
		}

	public function deletePermission(\App\Record\Permission $permission) : static
		{
		return $this;
		}

	public function deletePermissionString(string $permission) : int
		{
		return 0;
		}

	public function generatePermissionLoader() : void
		{
		}

	public function getPermissionId(string $name) : int
		{
		return 0;
		}

	/**
	 * @param array<int,int> $permissions
	 */
	public function getPermissionsForGroup(string|int $group, array $permissions = []) : array
		{
		return [];
		}

	/**
	 * @return array<int,int>
	 */
	public function getPermissionsForUser(int $memberId) : array
		{
		return [];
		}

	/**
	 * @return array<string>
	 */
	public function getStandardGroups() : array
		{
		return [
			'Event Coordinator',
			'Normal Member',
			'Pending Member',
			'Ride Coordinator',
			'Ride Leader',
			'Super User',
		];
		}

	public function hasPermission(int $permission) : bool
		{
		return true;
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

	public function saveGroup(array $parameters) : void
		{
		}

	public function saveMember(array $parameters) : void
		{
		}
	}
