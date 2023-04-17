<?php

namespace App\Table;

class CueSheetVersion extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\CueSheetVersion::class;

	public function allRidesWithRWGPSLinks() : iterable
		{
		$sql = 'select * from cueSheetVersion where link like "%ridewithgps%" or link like "%rwgps%"';

		return \PHPFUI\ORM::getRecordCursor($this->instance, $sql);
		}

	public function setDateDescCursor(\App\Record\CueSheet $cueSheet, int $limit = 0) : static
		{
		$this->setWhere(new \PHPFUI\ORM\Condition('cueSheetId', $cueSheet->cueSheetId));
		$this->addOrderBy('dateAdded', 'desc');

		if ($limit)
			{
			$this->setLimit($limit);
			}

		return $this;
		}
	}
