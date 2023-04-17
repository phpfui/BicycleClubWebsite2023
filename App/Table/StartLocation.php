<?php

namespace App\Table;

class StartLocation extends \PHPFUI\ORM\Table
{
	protected static string $className = '\\' . \App\Record\StartLocation::class;

	public function getAll(array $where = []) : iterable
		{
		$sql = 'select * from startLocation ';
		$data = [];

		if ($where)
			{
			$sql .= 'where ';
			$and = '';

			foreach ($where as $field => $value)
				{
				$sql .= $and . $field . '=?';
				$data[] = $value;
				$and = ' and ';
				}
			}
		$sql .= ' order by name';

		return \PHPFUI\ORM::getDataObjectCursor($sql, $data);
		}

	public function getByName(string $name) : iterable
		{
		$sql = 'select * from startLocation where name like ?';

		return \PHPFUI\ORM::getDataObjectCursor($sql, ["%{$name}%"]);
		}

	public function getStartsWith(string $char) : iterable
		{
		$sql = 'select * from startLocation where name regexp ? group by startLocationId order by name';

		return \PHPFUI\ORM::getDataObjectCursor($sql, ["^[{$char}]"]);
		}

	public function merge(int $from, int $to) : void
		{
		if ($from == $to)
			{
			return;
			}
		$input = ['from' => $from, 'to' => $to, ];
		$sql = 'update cueSheet set startLocationId=:to where startLocationId=:from';
		\PHPFUI\ORM::execute($sql, $input);
		$sql = 'update ride set startLocationId=:to where startLocationId=:from';
		\PHPFUI\ORM::execute($sql, $input);

		$location = new \App\Record\StartLocation($from);
		$location->delete();
		}
	}
