<?php

include 'common.php';

$membershipTable = new \App\Table\Membership();
$membershipTable->addJoin('member', new \PHPFUI\ORM\Condition('member.membershipId', new \PHPFUI\ORM\Literal('membership.membershipId')));
$membershipTable->addJoin('invoice', new \PHPFUI\ORM\Condition('member.memberId', new \PHPFUI\ORM\Literal('invoice.memberId')));
$membershipTable->addJoin('invoiceItem', new \PHPFUI\ORM\Condition('invoice.invoiceId', new \PHPFUI\ORM\Literal('invoiceItem.invoiceId')));

$membershipTable->addSelect('membership.*');
$membershipTable->addSelect('member.*');
$expiresCondition = new \PHPFUI\ORM\Condition('membership.expires', '2023', new \PHPFUI\ORM\Operator\LessThan());
$expiresCondition->or(new \PHPFUI\ORM\Condition('membership.expires', null, new \PHPFUI\ORM\Operator\IsNull()));

$condition = new \PHPFUI\ORM\Condition('invoiceItem.title', '%membership%', new \PHPFUI\ORM\Operator\Like());
$condition->and($expiresCondition);
$condition->and(new \PHPFUI\ORM\Condition('invoice.orderDate', '2022-10-01', new \PHPFUI\ORM\Operator\GreaterThan()));
$condition->and(new \PHPFUI\ORM\Condition('invoice.paypaltx', null, new \PHPFUI\ORM\Operator\IsNotNull()));

$membershipTable->setWhere($condition);

try
	{
	foreach ($membershipTable->getArrayCursor() as $row)
		{
		$membership = new \App\Record\Membership($row['membershipId']);

		if ($membership->expires < '2023')
			{
			$membership->expires = '2023-10-31';
			}
		else
			{
			$parts = \explode('-', $membership->expires);
			$parts[0] = (string)(((int)$parts[0]) + 1);
			$membership->expires = \implode('-', $parts);
			}
		$membership->update();
		echo "update {$row['firstName']} {$row['lastName']} to {$membership->expires}\n";
		}
	}
catch (\Throwable $e)
	{
	\print_r($e);
	}
