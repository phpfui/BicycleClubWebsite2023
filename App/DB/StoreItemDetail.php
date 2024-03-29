<?php

namespace App\DB;

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
		// @phpstan-ignore-next-line
		$condition = new \PHPFUI\ORM\Condition('storeItemId', $this->currentRecord->storeItemId);
		// @phpstan-ignore-next-line
		$condition->and(new \PHPFUI\ORM\Condition('storeItemDetailId', $this->currentRecord->storeItemDetailId));
		$condition->and(new \PHPFUI\ORM\Condition('storeItemDetailId', operator:new \PHPFUI\ORM\Operator\IsNotNull()));
		$table->setWhere($condition);

		return $table->getRecordCursor();
		}
	}
