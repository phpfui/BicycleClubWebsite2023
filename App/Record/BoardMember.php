<?php

namespace App\Record;

/**
 * @inheritDoc
 */
class BoardMember extends \App\Record\Definition\BoardMember
	{
	public function clean() : static
		{
		$this->cleanProperName('title');

		return $this;
		}
	}
