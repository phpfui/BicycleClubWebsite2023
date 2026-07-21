<?php

namespace App\Table;

class SigninSheetRide extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\SigninSheetRide::class;

	public function rides(int $signinSheetId) : \PHPFUI\ORM\DataObjectCursor
		{
		$this->setJoin('ride');
		$this->setWhere(new \PHPFUI\ORM\Condition('signinSheetId', $signinSheetId));
		$this->setOrderBy('rideDate', 'desc');

		return $this->getDataObjectCursor();
		}
	}
