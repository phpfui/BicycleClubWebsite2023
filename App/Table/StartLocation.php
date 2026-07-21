<?php

namespace App\Table;

class StartLocation extends \PHPFUI\ORM\Table
{
	protected static string $className = '\\' . \App\Record\StartLocation::class;

	/**
	 * @param array<string,mixed> $where
	 */
	public function getAll(array $where = []) : \PHPFUI\ORM\DataObjectCursor
		{
		$condition = new \PHPFUI\ORM\Condition();

		foreach ($where as $field => $value)
			{
			$condition->and($field, $value);
			}
		$this->setWhere($condition);
		$this->setOrderBy('name');

		return $this->getDataObjectCursor();
		}

	public function getByName(string $name) : \PHPFUI\ORM\DataObjectCursor
		{
		$this->setWhere(new \PHPFUI\ORM\Condition('name', "%{$name}%", new \PHPFUI\ORM\Operator\Like()));

		return $this->getDataObjectCursor();
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
		$sql = 'update RWGPS set startLocationId=:to where startLocationId=:from';
		\PHPFUI\ORM::execute($sql, $input);
		$sql = 'update ride set startLocationId=:to where startLocationId=:from';
		\PHPFUI\ORM::execute($sql, $input);

		$location = new \App\Record\StartLocation($from);
		$location->delete();
		}
	}
