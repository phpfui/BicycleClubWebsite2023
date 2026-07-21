<?php

namespace App\Table;

class InvoiceItem extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\InvoiceItem::class;

	public function findItems(int $invoiceId, string $restrict, string $exclude, string $text) : \PHPFUI\ORM\DataObjectCursor
		{
		$condition = new \PHPFUI\ORM\Condition('type', 0);
		$condition->and('invoiceId', $invoiceId);

		if (! empty($restrict))
			{
			$in = \explode(',', $restrict);

			foreach ($in as &$i)
				{
				$i = (int)$i;
				}
			$condition->and('storeItemId', $in, new \PHPFUI\ORM\Operator\In());
			}

		if (! empty($exclude))
			{
			$out = \explode(',', $exclude);

			foreach ($out as &$i)
				{
				$i = (int)$i;
				}
			$condition->and('storeItemId', $out, new \PHPFUI\ORM\Operator\NotIn());
			}

		if (! empty($text))
			{
			$search = "%{$text}%";
			$orCondition = new \PHPFUI\ORM\Condition('title', $search, new \PHPFUI\ORM\Operator\Like());
			$orCondition->or('description', $search, new \PHPFUI\ORM\Operator\Like());
			$orCondition->or('detailLine', $search, new \PHPFUI\ORM\Operator\Like());
			$condition->and($orCondition);
			}
		$this->setWhere($condition);

		return $this->getDataObjectCursor();
		}

	/**
	 * @param array<int> $types
	 */
	public function getByDateType(string $startDate, string $endDate, array $types = []) : \PHPFUI\ORM\DataObjectCursor
		{
		$this->setJoin('invoice');
		$condition = new \PHPFUI\ORM\Condition('orderDate', $startDate, new \PHPFUI\ORM\Operator\GreaterThanEqual());
		$condition->and('orderDate', $endDate, new \PHPFUI\ORM\Operator\LessThanEqual());
		$condition->and('paymentDate', '1000-01-01', new \PHPFUI\ORM\Operator\GreaterThan());

		if ($types)
			{
			$condition->and('type', $types, new \PHPFUI\ORM\Operator\In());
			}
		$this->setWhere($condition);

		return $this->getDataObjectCursor();
		}

	public function getUnshippedItems() : \PHPFUI\ORM\ArrayCursor
		{
		$this->setJoin('invoice');
		$condition = new \PHPFUI\ORM\Condition('paymentDate', '1000-01-01', new \PHPFUI\ORM\Operator\GreaterThan());
		$condition->and('fullfillmentDate', null);
		$this->setWhere($condition);

		return $this->getArrayCursor();
		}
	}
