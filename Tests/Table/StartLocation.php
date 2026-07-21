<?php

namespace Tests\Table;

class StartLocation extends \PHPFUI\ORM\Table
{
	protected static string $className = '\\' . \App\Record\StartLocation::class;

	/**
	 * @param array<string,mixed> $where
	 */
	public function getAll(array $where = []) : \PHPFUI\ORM\DataObjectCursor
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

	public function getByName(string $name) : \PHPFUI\ORM\DataObjectCursor
		{
		$sql = 'select * from startLocation where name like ?';

		return \PHPFUI\ORM::getDataObjectCursor($sql, ["%{$name}%"]);
		}
	}
