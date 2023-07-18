<?php

namespace App\Table;

class GaEvent extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\GaEvent::class;

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\GaEvent>
	 */
	public function getCurrentEvents() : \PHPFUI\ORM\RecordCursor
		{
		$this->setWhere(new \PHPFUI\ORM\Condition('eventDate', \App\Tools\Date::todayString(), new \PHPFUI\ORM\Operator\GreaterThanEqual()));

		return $this->getRecordCursor();
		}
	}
