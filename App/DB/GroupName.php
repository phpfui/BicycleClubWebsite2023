<?php

namespace App\DB;

class GroupName extends \PHPFUI\ORM\VirtualField
	{
	/**
	 * @param array<string> $parameters
	 */
	public function getValue(array $parameters) : string
		{
		$permission = new \App\Record\Permission($this->currentRecord->groupId);	// @phpstan-ignore-line

		return $permission->name;
		}
	}
