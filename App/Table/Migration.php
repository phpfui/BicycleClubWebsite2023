<?php

namespace App\Table;

class Migration extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\Migration::class;

	public function getHighest() : \App\Record\Migration
		{
		$sql = 'select * from migration order by migrationId desc limit 1';

		$record = new \App\Record\Migration();
		$record->loadFromSQL($sql);

		return $record;
		}

	public function paginate(int $page, int $perPage) : iterable
		{
		$offset = ($page - 1) * $perPage;
		$sql = "select * from migration order by migrationId desc limit {$offset} {$perPage}";

		return \PHPFUI\ORM::getArrayCursor($sql);
		}
	}
