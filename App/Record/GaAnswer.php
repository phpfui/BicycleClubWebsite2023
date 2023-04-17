<?php

namespace App\Record;

/**
 * @inheritDoc
 */
class GaAnswer extends \App\Record\Definition\GaAnswer
	{
	public function clean() : static
		{
		$this->cleanProperName('answer');

		return $this;
		}
	}
