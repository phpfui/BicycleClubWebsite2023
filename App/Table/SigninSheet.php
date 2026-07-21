<?php

namespace App\Table;

class SigninSheet extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\SigninSheet::class;

	public function getForDateRange(string $startDate, string $endDate) : \PHPFUI\ORM\DataObjectCursor
		{
		$this->setSelect('signinSheet.memberId');
		$this->addSelect('signinSheetRide.rideId');
		$this->setDistinct();
		$this->setJoin('signinSheetRide');
		$condition = new \PHPFUI\ORM\Condition('dateAdded', $startDate, new \PHPFUI\ORM\Operator\GreaterThanEqual());
		$condition->and('dateAdded', $endDate, new \PHPFUI\ORM\Operator\LessThanEqual());
		$this->setWhere($condition);

		return $this->getDataObjectCursor();
		}

	public function getForMemberDate(int $memberId, string $startDate, string $endDate) : \PHPFUI\ORM\DataObjectCursor
		{
		$condition = new \PHPFUI\ORM\Condition('pending', 0, new \PHPFUI\ORM\Operator\NotEqual());
		$condition->and('memberId', $memberId);
		$condition->and('dateAdded', $startDate, new \PHPFUI\ORM\Operator\GreaterThanEqual());
		$condition->and('dateAdded', $endDate, new \PHPFUI\ORM\Operator\LessThanEqual());
		$this->setWhere($condition);
		$this->setOrderBy('dateAdded', 'desc');

		return $this->getDataObjectCursor();
		}

	/**
	 * @param array<string,string> $parameters
	 */
	public function search(array $parameters) : bool
		{
		$condition = $this->getWhereCondition();
		$this->addJoin('signinSheetRide', 'signinSheetId');
		$this->addJoin('ride', new \PHPFUI\ORM\Condition('ride.rideId', new \PHPFUI\ORM\Field('signinSheetRide.rideId')));
		$returnValue = false;

		if (! empty($parameters['MemberName']))
			{
			$condition->and('signinSheet.memberId', $parameters['MemberName']);
			$returnValue = true;
			}

		if (! empty($parameters['ride_title']))
			{
			$condition->and('ride.title', '%' . $parameters['ride_title'] . '%', new \PHPFUI\ORM\Operator\Like());
			$returnValue = true;
			}

		if (! empty($parameters['addedEnd']))
			{
			$condition->and('signinSheet.dateAdded', $parameters['addedEnd'], new \PHPFUI\ORM\Operator\LessThanEqual());
			$returnValue = true;
			}

		if (! empty($parameters['addedStart']))
			{
			$condition->and('signinSheet.dateAdded', $parameters['addedStart'], new \PHPFUI\ORM\Operator\GreaterThanEqual());
			$returnValue = true;
			}

		if (! empty($parameters['rideDateEnd']))
			{
			$condition->and('ride.rideDate', $parameters['rideDateEnd'], new \PHPFUI\ORM\Operator\LessThanEqual());
			$returnValue = true;
			}

		if (! empty($parameters['rideDateStart']))
			{
			$condition->and('ride.rideDate', $parameters['rideDateStart'], new \PHPFUI\ORM\Operator\GreaterThanEqual());
			$returnValue = true;
			}

		return $returnValue;
		}
	}
