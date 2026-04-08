<?php

namespace App\Record;

/**
 * @inheritDoc
 */
class CueSheetVersion extends \App\Record\Definition\CueSheetVersion
	{

	public function clean() : static
		{
		$this->dateAdded ??= \App\Tools\Date::todayString();

		return $this;
		}

	}
