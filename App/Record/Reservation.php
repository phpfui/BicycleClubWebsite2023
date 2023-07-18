<?php

namespace App\Record;

/**
 * @inheritDoc
 *
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\ReservationPerson> $ReservationPersonChildren
 */
class Reservation extends \App\Record\Definition\Reservation
	{
	/** @var array<string, array<string>> */
	protected static array $virtualFields = [
		'ReservationPersonChildren' => [\PHPFUI\ORM\Children::class, \App\Table\ReservationPerson::class],
	];

	public function delete() : bool
		{
		$payment = $this->payment;

		if ($payment->loaded())
			{
			$payment->delete();
			}

		return parent::delete();
		}
	}
