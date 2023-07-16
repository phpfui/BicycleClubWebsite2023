<?php

namespace App\Record;

/**
 * @inheritDoc
 */
class Calendar extends \App\Record\Definition\Calendar
	{
	public function clean() : static
		{
		$this->description = \App\Tools\TextHelper::cleanUserHtml($this->description);
		$this->cleanProperName('title');
		$this->cleanProperName('location');
		$this->cleanProperName('privateContact');
		$this->cleanProperName('publicContact');
		$this->cleanUpperCase('state');

		return $this;
		}
	}
