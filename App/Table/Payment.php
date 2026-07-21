<?php

namespace App\Table;

class Payment extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\Payment::class;

	/**
	 * @param array<int> $paymentTypes
	 *
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\Payment>
	 */
	public function getByDate(string $startDate, string $endDate, array $paymentTypes = [], bool $userOnly = false) : \PHPFUI\ORM\RecordCursor
		{
		$condition = new \PHPFUI\ORM\Condition('dateReceived', $startDate, new \PHPFUI\ORM\Operator\GreaterThanEqual());
		$condition->and('dateReceived', $endDate, new \PHPFUI\ORM\Operator\LessThanEqual());

		if ($paymentTypes)
			{
			$condition->and('paymentType', $paymentTypes, new \PHPFUI\ORM\Operator\In());
			}

		if ($userOnly)
			{
			$condition->and('enteringMemberNumber', \App\Model\Session::getSignedInMemberId());
			}

		$this->setWhere($condition);
		$this->setOrderBy('dateReceived')->addOrderBy('paymentDated');

		return $this->getRecordCursor();
		}

	/**
	 * @return array<string>
	 */
	public static function getPaymentTypes() : array
		{
		return ['Cash',
			'Check',
			'Money Order',
			'PayPal',
			'Stripe', ];
		}
	}
