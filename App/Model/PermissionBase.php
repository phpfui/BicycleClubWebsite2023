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
	 *
	 * @return array<int> array indexed by permissionId with a value enabled (int)
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
			'Event Coordinator' => 'Used to determine who can be listed to run an event',
			'Assistant Event Coordinator' => 'Used to determine who can be listed to run an event',
			'Normal Member' => 'Assigned to all new members',
			'Pending Member' => 'Assigned to members who have not paid yet',
			'Ride Coordinator' => 'Assistants to the Rides Chair',
			'Ride Leader' => 'Used for identifying ride leaders',
			'Super User' => 'God users',
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

	public function memberHasPermission(\App\Record\Member $member, \App\Record\Permission $permission) : bool
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

	/**
	 * @param array<string,string|array<int>> $parameters
	 */
	public function saveGroup(array $parameters) : void
		{
		}

	/**
	 * @param array<string,string|array<int>> $parameters
	 */
	public function saveMember(array $parameters) : void
		{
		}
	}
