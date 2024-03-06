<?php

namespace App\DB;

class GroupName extends \PHPFUI\ORM\VirtualField
	{
	/**
	 * @param array<mixed> $parameters
	 *
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\StoreItemDetail>
	 */
	public function getValue(array $parameters) : \PHPFUI\ORM\RecordCursor
		{
		$permission = new \App\Record\Permission($this->currentRecord->groupId);

		return $permission->name;
		}
	}
