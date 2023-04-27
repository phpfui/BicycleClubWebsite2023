<?php

namespace App\Record;

/**
 * @inheritDoc
 */
class BoardMember extends \App\Record\Definition\BoardMember
	{
	public function clean() : static
		{
		$this->description = \App\Tools\TextHelper::cleanUserHtml($this->description);
		$this->cleanProperName('title');

		return $this;
		}
	}
