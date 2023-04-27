<?php

namespace App\Record;

/**
 * @inheritDoc
 */
class SystemEmail extends \App\Record\Definition\SystemEmail
	{
	public function clean() : static
		{
		$this->cleanEmail('mailbox');
		$this->cleanEmail('email');
		$this->cleanProperName('name');

		return $this;
		}
	}
