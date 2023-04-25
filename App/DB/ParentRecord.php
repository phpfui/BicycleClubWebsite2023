<?php

namespace App\DB;

class ParentRecord extends \PHPFUI\ORM\VirtualField
	{
	public function getValue(array $parameters) : mixed
		{
		$class = \array_shift($parameters);

		return new $class($this->parentRecord[$this->fieldName . 'Id']);
		}
	}
