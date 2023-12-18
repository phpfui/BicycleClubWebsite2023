<?php

namespace App\Record;

/**
 * @inheritDoc
 */
class StartLocation extends \App\Record\Definition\StartLocation
	{
	public function clean() : static
		{
		$this->cleanProperName('address');
		$this->cleanProperName('town');
		$this->cleanProperName('addressExit');
		$this->cleanUpperCase('state');

		return $this;
		}
	}
