<?php

namespace App\Record;

/**
 * @inheritDoc
 */
class AdditionalEmail extends \App\Record\Definition\AdditionalEmail
	{
	public function clean() : static
		{
		$this->cleanEmail('email');

		return $this;
		}
	}
