<?php

namespace App\Record;

/**
 * @inheritDoc
 */
class BikeShop extends \App\Record\Definition\BikeShop
	{
	public function clean() : static
		{
		$this->cleanEmail('email');
		$this->cleanProperName('name');
		$this->cleanProperName('contact');
		$this->cleanProperName('address');
		$this->cleanProperName('town');
		$this->cleanUpperCase('state');
		$this->cleanPhone('zip', '\\-');
		$this->cleanPhone('phone');

		return $this;
		}

	public function fullName() : string
		{
		return $this->name . ' - ' . $this->town . ' - ' . $this->state;
		}
	}
