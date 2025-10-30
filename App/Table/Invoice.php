<?php

namespace App\Table;

class Invoice extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\Invoice::class;

	/**
	 * @param array<string,array<int>|string> $parameters
	 */
	public function find(array $parameters) : \PHPFUI\ORM\DataObjectCursor
		{
		$sql = 'select distinct i.*,COALESCE(m.email,c.email) as email,COALESCE(m.firstName, c.firstName) as firstName, COALESCE(m.lastName, c.lastName) as lastName ' .
			'from invoice i left join invoiceItem ii on i.invoiceId=ii.invoiceId ' .
			'left outer join member m on i.memberId=m.memberId ' .
			'left outer join customer c on (0-i.memberId)=c.customerId ';
		$fields = $this->getFields();
		$fields['text'] = '';
		$input = [];
		$and = 'where ';

		foreach ($parameters as $fieldName => $value)
			{
			$field = $fieldName;
			$underscore = \strpos($field, '_');

			if ($underscore)
				{
				$field = \substr($field, 0, $underscore);
				}

			if ('text' == $field)
				{
				$itemFields = ['title', 'description', 'detailLine'];

				$sql .= $and . '(ii.title like ? or ii.description like ?  or ii.detailLine like ?)';
				$input[] = "%{$value}%";
				$input[] = "%{$value}%";
				$input[] = "%{$value}%";
				$and = ' and ';
				}
			elseif ('status' == $field)
				{
				switch ($value)
					{
					case 'S':
						$sql .= $and . 'i.fullfillmentDate>"1000-01-01"';
						$and = ' and ';

						break;

					case 'N':
						$sql .= $and . 'i.fullfillmentDate is null';
						$and = ' and ';

						break;

					case 'U':
						$sql .= $and . 'i.paymentDate is null';
						$and = ' and ';

						break;
					}
				}
			elseif ('name' == $field)
				{
				$sql .= $and . '(m.firstName like ? or m.lastname like ? or c.firstName like ? or c.lastname like ?)';
				$input[] = "%{$value}%";
				$input[] = "%{$value}%";
				$input[] = "%{$value}%";
				$input[] = "%{$value}%";
				$and = ' and ';
				}
			elseif (isset($fields[$field]))
				{
				if (\is_array($value))
					{
					if (\count($value))
						{
						foreach ($value as &$int)
							{
							$int = (int)$int;
							}
						$sql .= $and . 'i.' . $field . ' in (' . \implode(',', $value) . ')';
						$and = ' and ';
						}
					}
				elseif (! empty($value))
					{
					$type = $fields[$field]->phpType;

					switch ($type)
						{
						case 'int':
							if ($underscore)
								{
								$value = \App\Tools\Date::fromString($value);

								if ($value)
									{
									$operator = \strpos($fieldName, 'from') ? '>=?' : '<=?';
									$sql .= $and . 'i.' . $field . $operator;
									$input[] = $value;
									$and = ' and ';
									}
								}
							else
								{
								$value = (int)$value;

								if ($value)
									{
									$sql .= $and . 'i.' . $field . '=?';
									$input[] = $value;
									$and = ' and ';
									}
								}

							break;

						case 'string':
							$value = \htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');

							$table = 'i.';
							$itemFields = [$field];

							foreach ($itemFields as $field)
								{
								$sql .= $and . $table . $field . ' like ?';
								$input[] = "%{$value}%";
								$and = ' and ';
								}

							break;
						}
					}
				}
			}

		if (! empty($parameters['sort']))
			{
			if (isset($fields[$parameters['sort']]))
				{
				$sql .= ' order by i.' . $parameters['sort'];
				}
			elseif ('lastName' == $parameters['sort'])
				{
				$sql .= ' order by m.lastName';
				}

			if ('D' == $parameters['orderby'])
				{
				$sql .= ' desc';
				}
			}
		$sql .= ' limit 50';

		return \PHPFUI\ORM::getDataObjectCursor($sql, $input);
		}

	/**
	 * @param array<int> $types
	 */
	public static function getByDateType(string $startDate, string $endDate, array $types = []) : \PHPFUI\ORM\DataObjectCursor
		{
		$sql = 'select * from invoice where orderDate>=? and orderDate<=? and paymentDate>"1000-01-01"';
		$input = [$startDate, $endDate, ];

		if ($types)
			{
			$sql .= ' and invoiceId in (select invoiceId from invoiceItem where invoiceItem.invoiceId=invoice.invoiceId and type in (' . \implode(',', $types) . '))';
			}

		return \PHPFUI\ORM::getDataObjectCursor($sql, $input);
		}

	public static function getPaidByDate(int $shipped, string $startDate = '', string $endDate = '', int $points = 0) : \PHPFUI\ORM\ArrayCursor
		{
		$sql = self::getSelectedFields() ;
		$sql .= 'where i.paymentDate>"1000-01-01"';

		if ($shipped)
			{
			$sql .= ' and i.fullfillmentDate';
			$sql .= 1 == $shipped ? '>"1000-01-01"' : ' is null';
			}

		if ($points)
			{
			$sql .= ' and i.pointsUsed';
			$sql .= 2 == $points ? '>"1000-01-01"' : ' is null';
			}
		$input = [];

		if ($startDate)
			{
			$input[] = $startDate;
			$sql .= ' and i.orderDate>=?';
			}

		if ($endDate)
			{
			$input[] = $endDate;
			$sql .= ' and i.orderDate<=?';
			}
		$sql .= ' order by i.invoiceId';

		return \PHPFUI\ORM::getArrayCursor($sql, $input);
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\Invoice>
	 */
	public static function getTaxes(string $startDate, string $endDate) : \PHPFUI\ORM\RecordCursor
		{
		$sql = 'SELECT * FROM invoice where orderDate >= ? and orderDate <= ? and totalTax>0 and paymentDate>"1000-01-01"';

		return \PHPFUI\ORM::getRecordCursor(new \App\Record\Invoice(), $sql, [$startDate, $endDate, ]);
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\Invoice>
	 */
	public static function getUnpaidBefore(string $date) : \PHPFUI\ORM\RecordCursor
		{
		$sql = 'select * from invoice where orderDate < ? and paymentDate is null';

		return \PHPFUI\ORM::getRecordCursor(new \App\Record\Invoice(), $sql, [$date]);
		}

	/**
	 * @param array<string> $dates
	 *
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\Invoice>
	 */
	public function getUnpaidOn(array $dates) : \PHPFUI\ORM\RecordCursor
		{
		$condition = new \PHPFUI\ORM\Condition();//'paymentDate', null, new \PHPFUI\ORM\Operator\IsNull());
		$condition->and('orderDate', $dates, new \PHPFUI\ORM\Operator\In());
		$this->setWhere($condition);

		return $this->getRecordCursor();
		}

	public static function pointsUsed(string $start, string $end, string $sort) : \PHPFUI\ORM\ArrayCursor
		{
		$sql = 'select m.*,i.* from invoice i left join member m on m.memberId=i.memberId where i.pointsUsed > 0';
		$input = [];

		if ($start)
			{
			$sql .= ' and i.orderDate>=?';
			$input[] = $start;
			}

		if ($end)
			{
			$sql .= ' and i.orderDate<=?';
			$input[] = $end;
			}
		$sql .= ' order by ' . $sort;

		return \PHPFUI\ORM::getArrayCursor($sql, $input);
		}

	public function setCompletedForMember(int $memberId) : static
		{
		$this->addJoin('member');
		$condition = new \PHPFUI\ORM\Condition('paymentDate', '1000-01-01', new \PHPFUI\ORM\Operator\GreaterThan());
		$condition->and('invoice.memberId', $memberId);
		$this->setWhere($condition);
		$this->setOrderBy('orderDate', 'desc');

		return $this;
		}

	public function setUnpaidForMember(int $memberId) : static
		{
		$this->addJoin('member');
		$condition = new \PHPFUI\ORM\Condition('paymentDate', null, new \PHPFUI\ORM\Operator\IsNull());
		$condition->and('invoice.memberId', $memberId);
		$this->setWhere($condition);
		$this->setOrderBy('orderDate', 'desc');

		return $this;
		}

	public function setUnrecordedChecks() : static
		{
		$condition = new \PHPFUI\ORM\Condition('paymentDate', null, new \PHPFUI\ORM\Operator\IsNull());
		$condition->and('paidByCheck', 1);
		$this->setWhere($condition);

		return $this;
		}

	public function setUnshippedInvoices() : static
		{
		$this->addJoin('member');
		$condition = new \PHPFUI\ORM\Condition('paymentDate', '1000-01-01', new \PHPFUI\ORM\Operator\GreaterThan());
		$condition->and('fullfillmentDate', null, new \PHPFUI\ORM\Operator\IsNull());
		$this->setWhere($condition);
		$this->setOrderBy('orderDate', 'desc');

		return $this;
		}

	private static function getSelectedFields() : string
		{
		return 'select i.*,m.firstName,m.lastName,m.email,concat(m.firstName," ",m.lastName) name from invoice i left join member m on i.memberId=m.memberId ';
		}
	}
