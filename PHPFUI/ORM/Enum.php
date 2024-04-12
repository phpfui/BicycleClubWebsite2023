<?php

namespace PHPFUI\ORM;

class Enum extends \PHPFUI\ORM\VirtualField
	{
	/**
	 * @param array<mixed> $parameters optional
	 */
	public function getValue(array $parameters) : mixed
		{
		$enum = $parameters[0];

		return $enum::from($this->currentRecord->offsetGet($this->fieldName));
		}

	/**
	 * @param array<mixed> $parameters optional
	 */
	public function setValue(mixed $value, array $parameters) : void
		{
		$this->currentRecord->offsetSet($fieldName, $value->value);
		}
	}
