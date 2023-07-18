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
	public static function getByDate(string $startDate, string $endDate, array $paymentTypes = [], bool $userOnly = false) : \PHPFUI\ORM\RecordCursor
		{
		$sql = 'SELECT * FROM payment where dateReceived>=? and dateReceived<=?';
		$input = [$startDate,
			$endDate, ];

		if ($paymentTypes)
			{
			$sql .= ' and paymentType in (' . \implode(',', $paymentTypes) . ')';
			}

		if ($userOnly)
			{
			$sql .= ' and enteringMemberNumber=?';
			$input[] = \App\Model\Session::signedInMemberId();
			}
		$sql .= ' order by dateReceived,paymentDated';

		return \PHPFUI\ORM::getRecordCursor(new \App\Record\Payment(), $sql, $input);
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
