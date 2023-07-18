<?php

namespace App\Table;

class PaypalRefund extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\PaypalRefund::class;

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\PaypalRefund>
	 */
	public function getPendingRefunds() : \PHPFUI\ORM\RecordCursor
		{
		$refundedCondition = new \PHPFUI\ORM\Condition('refundedDate', null, new \PHPFUI\ORM\Operator\IsNull());
		$refundedCondition->or('refundedDate', null, new \PHPFUI\ORM\Operator\IsNull());

		$condition = new \PHPFUI\ORM\Condition('response', '');
		$condition->and($refundedCondition);

		$this->setWhere($condition);

		return $this->getRecordCursor();
		}
	}
