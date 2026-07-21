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
		$this->setDistinct();
		$this->setSelect('invoice.*');
		$this->addSelect(new \PHPFUI\ORM\Literal('COALESCE(member.email, customer.email)'), 'email');
		$this->addSelect(new \PHPFUI\ORM\Literal('COALESCE(member.firstName, customer.firstName)'), 'firstName');
		$this->addSelect(new \PHPFUI\ORM\Literal('COALESCE(member.lastName, customer.lastName)'), 'lastName');

		$this->setJoin('invoiceItem');
		$this->addJoin('member', type:'left outer');
		$this->addJoin('customer', new \PHPFUI\ORM\Condition('customer.customerId', new \PHPFUI\ORM\Literal('(0-invoice.memberId)')), type:'left outer');


		$fields = $this->getFields();
		$fields['text'] = '';
		$input = [];
		$condition = new \PHPFUI\ORM\Condition();

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
				$titleCondition = new \PHPFUI\ORM\Condition();

				foreach (['title', 'description', 'detailLine'] as $recordField)
					{
					$titleCondition->or($recordField, "%{$value}%", new \PHPFUI\ORM\Operator\Like());
					}
				$condition->and($titleCondition);
				}
			elseif ('status' == $field)
				{
				switch ($value)
					{
					case 'S':
						$condition->and('fullfillmentDate', '1000-01-01', new \PHPFUI\ORM\Operator\GreaterThan());

						break;

					case 'N':
						$condition->and('fullfillmentDate', null);

						break;

					case 'U':
						$condition->and('paymentDate', null);

						break;
					}
				}
			elseif ('name' == $field)
				{
				$nameCondition = new \PHPFUI\ORM\Condition();

				foreach (['member.firstName', 'member.lastname', 'customer.firstname', 'customer.lastname'] as $recordField)
					{
					$nameCondition->or($recordField, "%{$value}%", new \PHPFUI\ORM\Operator\Like());
					}
				$condition->and($nameCondition);
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
						$condition->and('invoice.' . $field, $value, new \PHPFUI\ORM\Operator\In());
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
									$operator = \strpos($fieldName, 'from') ? new \PHPFUI\ORM\Operator\GreaterThanEqual() : new \PHPFUI\ORM\Operator\LessThanEqual();
									$condition->and('invoice.' . $field, $value, $operator);
									}
								}
							else
								{
								$value = (int)$value;

								if ($value)
									{
									$condition->and('invoice.' . $field, $value);
									}
								}

							break;

						case 'string':
							$value = \htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');

							$table = 'i.';
							$itemFields = [$field];

							foreach ($itemFields as $field)
								{
								$condition->and('invoice.' . $field, "%{$value}%", new \PHPFUI\ORM\Operator\Like());
								}

							break;
						}
					}
				}
			}

		if (! empty($parameters['sort']))
			{
			$desc = ('D' == $parameters['orderby']) ? 'desc' : 'asc';

			if (isset($fields[$parameters['sort']]))
				{
				$this->setOrderBy('invoice.' . $parameters['sort'], $desc);
				}
			elseif ('lastName' == $parameters['sort'])
				{
				$this->setOrderBy('member.' . $parameters['sort'], $desc);
				}
			}
		$this->setLimit(50);

		return $this->getDataObjectCursor();
		}

	/**
	 * @param array<int> $types
	 */
	public function getByDateType(string $startDate, string $endDate, array $types = []) : \PHPFUI\ORM\DataObjectCursor
		{
		if ($types)
			{
			$sql = ' and invoiceId in (select invoiceId from invoiceItem where invoiceItem.invoiceId=invoice.invoiceId and type in (' . \implode(',', $types) . '))';
			}

		$condition = new \PHPFUI\ORM\Condition('orderDate', $startDate, new \PHPFUI\ORM\Operator\GreaterThanEqual());
		$condition->and('orderDate', $endDate, new \PHPFUI\ORM\Operator\LessThanEqual());
		$condition->and('paymentDate', '1000-01-01', new \PHPFUI\ORM\Operator\GreaterThan());

		if ($types)
			{
			$invoiceItemTable = new \App\Table\InvoiceItem();
			$invoiceItemTable->setSelect('invoiceId');
			$iiCondition = new \PHPFUI\ORM\Condition('invoiceItem.invoiceId', new \PHPFUI\ORM\Literal('invoice.invoiceId'));
			$iiCondition->and('type', $types, new \PHPFUI\ORM\Operator\In());
			$invoiceItemTable->setWhere($iiCondition);

			$condition->and('invoiceId', $invoiceItemTable, new \PHPFUI\ORM\Operator\In());
			}
		$this->setWhere($condition);

		return $this->getDataObjectCursor();
		}

	public function getPaidByDate(int $shipped, string $startDate = '', string $endDate = '', int $points = 0) : \PHPFUI\ORM\ArrayCursor
		{
		$this->setSelect('invoice.*');
		$this->addSelect('member.firstName');
		$this->addSelect('member.lastName');
		$this->addSelect('member.email');
		$this->addSelect(new \PHPFUI\ORM\Literal('concat(member.firstName," ",member.lastName)'), 'name');
		$this->setJoin('member');

		$condition = new \PHPFUI\ORM\Condition('paymentDate', '1000-01-01', new \PHPFUI\ORM\Operator\GreaterThan());

		// $shipped == 0 'All Invoices';
		// $shipped == 1 'Shipped Invoices';
		// $shipped == 2 'Unshipped Invoices';

		if (1 == $shipped)
			{
			$condition->and('fullfillmentDate', '1000-01-01', new \PHPFUI\ORM\Operator\GreaterThan());
			}
		elseif (2 == $shipped)
			{
			$condition->and('fullfillmentDate', null);
			}

		// $points == 0 'Both'
		// $points == 1 'Paid Only'
		// $points == 2 'Volunteer'

		if (1 == $points)
			{
			$condition->and('pointsUsed', null);
			}
		elseif (2 == $points)
			{
			$condition->and('pointsUsed', 0, new \PHPFUI\ORM\Operator\GreaterThan());
			}

		if ($startDate)
			{
			$condition->and('orderDate', $startDate, new \PHPFUI\ORM\Operator\GreaterThanEqual());
			}

		if ($endDate)
			{
			$condition->and('orderDate', $endDate, new \PHPFUI\ORM\Operator\LessThanEqual());
			}

		$this->setWhere($condition);
		$this->setOrderBy('invoiceId');

		return $this->getArrayCursor();
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\Invoice>
	 */
	public function getTaxes(string $startDate, string $endDate) : \PHPFUI\ORM\RecordCursor
		{
		$condition = new \PHPFUI\ORM\Condition('orderDate', $startDate, new \PHPFUI\ORM\Operator\GreaterThanEqual());
		$condition->and('orderDate', $endDate, new \PHPFUI\ORM\Operator\LessThanEqual());
		$condition->and('totalTax', 0, new \PHPFUI\ORM\Operator\GreaterThan());
		$condition->and('paymentDate', '1000-01-01', new \PHPFUI\ORM\Operator\GreaterThan());
		$this->setWhere($condition);

		return $this->getRecordCursor();
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\Invoice>
	 */
	public function getUnpaidBefore(string $date) : \PHPFUI\ORM\RecordCursor
		{
		$condition = new \PHPFUI\ORM\Condition('orderDate', $date, new \PHPFUI\ORM\Operator\LessThan());
		$condition->and('paymentDate', null);
		$this->setWhere($condition);

		return $this->getRecordCursor();
		}

	/**
	 * @param array<string> $dates
	 *
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\Invoice>
	 */
	public function getUnpaidOn(array $dates) : \PHPFUI\ORM\RecordCursor
		{
		$condition = new \PHPFUI\ORM\Condition('paymentDate', null, new \PHPFUI\ORM\Operator\IsNull());
		$condition->and('orderDate', $dates, new \PHPFUI\ORM\Operator\In());
		$this->setWhere($condition);

		return $this->getRecordCursor();
		}

	public function pointsUsed(string $start, string $end) : \PHPFUI\ORM\ArrayCursor
		{
		$this->setSelect('member.*');
		$this->addSelect('invoice.*');
		$this->setJoin('member');
		$condition = new \PHPFUI\ORM\Condition('pointsUsed', 0, new \PHPFUI\ORM\Operator\GreaterThan());

		if ($start)
			{
			$condition->and('orderDate', $start, new \PHPFUI\ORM\Operator\GreaterThanEqual());
			}

		if ($end)
			{
			$condition->and('orderDate', $end, new \PHPFUI\ORM\Operator\LessThanEqual());
			}
		$this->setWhere($condition);
		$this->setOrderBy('member.lastName');
		$this->addOrderBy('member.firstName');

		return $this->getArrayCursor();
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
	}
