<?php

namespace App\Record;

/**
 * @inheritDoc
 */
class ReservationPerson extends \App\Record\Definition\ReservationPerson
	{
	public function clean() : static
		{
		$this->cleanEmail('email');
		$this->cleanProperName('lastName');
		$this->cleanProperName('firstName');

		return $this;
		}
	}
