<?php

namespace App\Table;

class InvoiceItem extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\InvoiceItem::class;

	public static function findItems(int $invoiceId, string $restrict, string $exclude, string $text) : \PHPFUI\ORM\DataObjectCursor
		{
		$sql = 'select * from invoiceItem where type=0 and invoiceId=?';
		$input = [$invoiceId];

		if (! empty($restrict))
			{
			$in = \explode(',', $restrict);

			foreach ($in as &$i)
				{
				$i = (int)$i;
				}
			$sql .= ' and storeItemId in (' . \implode(',', $in) . ')';
			}

		if (! empty($exclude))
			{
			$out = \explode(',', $exclude);

			foreach ($out as &$i)
				{
				$i = (int)$i;
				}
			$sql .= ' and storeItemId not in (' . \implode(',', $out) . ')';
			}

		if (! empty($text))
			{
			$sql .= ' and (title like ? or description like ? or detailLine like ?)';
			$search = "%{$text}%";
			$input[] = $search;
			$input[] = $search;
			$input[] = $search;
			}

		return \PHPFUI\ORM::getDataObjectCursor($sql, $input);
		}

	/**
	 * @param array<int> $types
	 */
	public static function getByDateType(string $startDate, string $endDate, array $types = []) : \PHPFUI\ORM\DataObjectCursor
		{
		$sql = 'select * from invoiceItem ii left join invoice i on i.invoiceId=ii.invoiceId where i.orderDate>=? and i.orderDate<=? and i.paymentDate>"1000-01-01"';
		$input = [$startDate, $endDate, ];

		if ($types)
			{
			$sql .= ' and ii.type in (' . \implode(',', $types) . ')';
			}

		return \PHPFUI\ORM::getDataObjectCursor($sql, $input);
		}

	public static function getUnshippedItems() : \PHPFUI\ORM\ArrayCursor
		{
		$sql = 'select * from invoice i,invoiceItem ii where i.fullfillmentDate is null and i.paymentDate > "1000-01-01" and i.invoiceId = ii.invoiceId';

		return \PHPFUI\ORM::getArrayCursor($sql);
		}
	}
