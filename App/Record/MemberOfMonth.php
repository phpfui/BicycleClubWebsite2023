<?php

namespace App\Record;

/**
 * @inheritDoc
 */
class MemberOfMonth extends \App\Record\Definition\MemberOfMonth
	{
	public function clean() : static
		{
		$this->bio = \App\Tools\TextHelper::cleanUserHtml($this->bio);

		return $this;
		}
	}
