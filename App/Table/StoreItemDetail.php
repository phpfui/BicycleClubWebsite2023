<?php

namespace App\Table;

class StoreItemDetail extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\StoreItemDetail::class;

	public function getAllStock(int $storeItemId, string $order = 'storeItemDetailId') : \PHPFUI\ORM\ArrayCursor
		{
		$this->setWhere(new \PHPFUI\ORM\Condition('storeItemId', $storeItemId));
		$this->setOrderBy($order);

		return $this->getArrayCursor();
		}

	public function getInStock(int $storeItemId, string $order = 'storeItemDetailId') : \PHPFUI\ORM\ArrayCursor
		{
		$condition = new \PHPFUI\ORM\Condition('storeItemId', $storeItemId);
		$condition->and('quantity', 0, new \PHPFUI\ORM\Operator\GreaterThan());
		$this->setWhere($condition);
		$this->setOrderBy($order);

		return $this->getArrayCursor();
		}

	public function getOutOfStock(int $storeItemId, string $order = 'storeItemDetailId') : \PHPFUI\ORM\ArrayCursor
		{
		$condition = new \PHPFUI\ORM\Condition('storeItemId', $storeItemId);
		$condition->and('quantity', 0, new \PHPFUI\ORM\Operator\LessThanEqual());
		$this->setWhere($condition);
		$this->setOrderBy($order);

		return $this->getArrayCursor();
		}
	}
