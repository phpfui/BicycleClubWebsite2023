<?php

namespace App\DB;

class StoreItemDetail extends \PHPFUI\ORM\VirtualField
	{
	public function getValue(array $parameters) : mixed
		{
		$table = new \App\Table\StoreItemDetail();
		// @phpstan-ignore-next-line
		$condition = new \PHPFUI\ORM\Condition('storeItemId', $this->parentRecord->storeItemId);
		// @phpstan-ignore-next-line
		$condition->and(new \PHPFUI\ORM\Condition('storeItemDetailId', $this->parentRecord->storeItemDetailId));
		$condition->and(new \PHPFUI\ORM\Condition('storeItemDetailId', operator:new \PHPFUI\ORM\Operator\IsNotNull()));
		$table->setWhere($condition);

		return $table->getRecordCursor();
		}
	}
