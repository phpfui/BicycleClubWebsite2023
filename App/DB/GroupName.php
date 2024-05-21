<?php

namespace App\DB;

/**
 * @property \App\Record\PermissionGroup $currentRecord
 */
class GroupName extends \PHPFUI\ORM\VirtualField
	{
	/**
	 * @param array<string> $parameters
	 */
	public function getValue(array $parameters) : string
		{
		$permission = new \App\Record\Permission($this->currentRecord->groupId);

		return $permission->name;
		}
	}
