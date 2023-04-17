<?php

namespace App\Model;

interface PermissionsInterface
	{
	public function addPermission(string $permission, string $menu) : int;

	public function addPermissionToUser(int $user, string $permission) : bool;

	public function deletePermissionString(string $permission) : int;

	public function getPermissionId(string $name) : int;

	public function getPermissionsForGroup($group, array $permissions = []) : array;

	public function getPermissionsForUser(int $memberId) : array;

	public function isAuthorized(string $permission, string $menu = '') : bool;

	public function removePermissionFromUser(int $memberId, string $permission) : bool;

	public function revokePermissionForUser(int $memberId, string $permission) : bool;

	public function isSuperUser() : bool;

	public function generatePermissionLoader() : void;

	public function addGroup() : \App\Record\Permission;

	public function saveMember(array $parameters) : void;

	public function saveGroup(array $parameters) : void;

	public function deleteGroup(\App\Record\Permission $permission) : static;

	public function deletePermission(\App\Record\Permission $permission) : static;
	}
