<?php

namespace App\Record;

/**
 * @inheritDoc
 */
class Customer extends \App\Record\Definition\Customer
	{
	public function fullName() : string
		{
		return \App\Tools\TextHelper::unhtmlentities(($this->current['firstName'] ?? '') . ' ' . ($this->current['lastName'] ?? ''));
		}

	public function clean() : static
		{
		$this->cleanEmail('email');
		$this->cleanProperName('lastName');
		$this->cleanProperName('firstName');
		$this->cleanProperName('address');
		$this->cleanProperName('town');
		$this->cleanUpperCase('state');
		$this->cleanPhone('zip', '\\-');

		return $this;
		}
	}
