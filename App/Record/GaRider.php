<?php

namespace App\Record;

/**
 * @inheritDoc
 */
class GaRider extends \App\Record\Definition\GaRider
	{
	public function clean() : static
		{
		$this->cleanEmail('email');
		$this->cleanProperName('lastName');
		$this->cleanProperName('firstName');
		$this->cleanProperName('address');
		$this->cleanProperName('town');
		$this->cleanProperName('state');
		$this->cleanProperName('contact');
		$this->cleanPhone('contactPhone');
		$this->cleanPhone('zip', '\\-');

		return $this;
		}
	}
