<?php

namespace App\DB;

/**
 * @property \App\Record\CartItem $currentRecord
 */
class StoreItemDetail extends \PHPFUI\ORM\VirtualField
	{
	/**
	 * @param array<mixed> $parameters
	 *
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\StoreItemDetail>
	 */
	public function getValue(array $parameters) : \PHPFUI\ORM\RecordCursor
		{
		$table = new \App\Table\StoreItemDetail();
		$condition = new \PHPFUI\ORM\Condition('storeItemId', $this->currentRecord->storeItemId);
		$condition->and(new \PHPFUI\ORM\Condition('storeItemDetailId', $this->currentRecord->storeItemDetailId));
		$condition->and(new \PHPFUI\ORM\Condition('storeItemDetailId', operator:new \PHPFUI\ORM\Operator\IsNotNull()));
		$table->setWhere($condition);

		return $table->getRecordCursor();
		}
	}
