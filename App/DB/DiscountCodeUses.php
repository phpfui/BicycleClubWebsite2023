<?php

namespace App\DB;

/**
 * @property \App\Record\DiscountCode $currentRecord
 */
class DiscountCodeUses extends \PHPFUI\ORM\VirtualField
	{
	/**
	 * @param array<mixed> $parameters
	 *
	 */
	public function getValue(array $parameters) : int
		{
		$table = new \App\Table\Invoice();
		$condition = new \PHPFUI\ORM\Condition('discountCodeId', $this->currentRecord->discountCodeId);
		$condition->andNot('paymentDate', null);
		$table->setWhere($condition);

		return $table->getRecordCursor()->count();
		}
	}
