<?php

namespace App\Table;

class GaEvent extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\GaEvent::class;

	public function getCurrentEvents() : iterable
		{
		$this->setWhere(new \PHPFUI\ORM\Condition('eventDate', \App\Tools\Date::todayString(), new \PHPFUI\ORM\Operator\GreaterThanEqual()));

		return $this->getRecordCursor();
		}

	public static function getEvents(array $events) : iterable
		{
		$sql = 'select * from gaEvent where gaEventId in (';
		$ids = [];

		foreach ($events as $gaEventId => $value)
			{
			if ($value)
				{
				$ids[] = (int)$gaEventId;
				}
			}

		if (! $ids)
			{
			$ids[] = 0;
			}
		$sql .= \implode(',', $ids);
		$sql = $sql . ') order by eventDate desc;';

		return \PHPFUI\ORM::getArrayCursor($sql);
		}
	}
