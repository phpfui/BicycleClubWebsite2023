<?php

namespace App\Table;

class Category extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\Category::class;

	/**
	 * @var array<int,array<string,string>>
	 */
	private array $categories = [];

	public function __construct()
		{
		parent::__construct();
		$sql = 'select * from category order by ordering';

		foreach (\PHPFUI\ORM::getRows($sql) as $row)
			{
			$this->categories[$row['categoryId']] = $row;
			}
		}

	public function changeCategory(int $from, int $to) : void
		{
		\App\Table\MemberCategory::changeCategory($from, $to);
		$paceTable = new \App\Table\Pace();
		$fromPaces = $paceTable->getPaceOrder($from);
		$toPaces = $paceTable->getPaceOrder($to);
		$toPaceCount = \count($toPaces);

		foreach ($fromPaces as $index => $pace)
			{
			$i = $index;

			if ($i >= $toPaceCount)
				{
				$i = $toPaceCount - 1;
				}
			$toPace = $toPaces[$i];
			\App\Table\Ride::changePace($pace['paceId'], $toPace['paceId']);
			}

		$categoryKey = new \PHPFUI\ORM\Condition('categoryId', $from);
		$paceTable->setWhere($categoryKey);
		$paceTable->delete();
		$this->setWhere($categoryKey);
		$this->delete();
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\Category>
	 */
	public function getAllCategories() : \PHPFUI\ORM\RecordCursor
		{
		$this->setOrderBy('ordering');

		return $this->getRecordCursor();
		}

	public function getCategoryForId(int $categoryId) : string
		{
		return $this->categories[$categoryId]['category'] ?? 'All';
		}

	/**
	 * @return array<int>
	 */
	public function getDefaults() : array
		{
		$defaults = [];

		foreach ($this->getAllCategories() as $category)
			{
			if (! empty($category->memberDefault))
				{
				$defaults[] = $category->categoryId;
				}
			}

		return $defaults;
		}

	public function getDistributions() : \PHPFUI\ORM\DataObjectCursor
		{
		$this->setSelect('category');
		$this->addSelect(new \PHPFUI\ORM\Literal('count(*)'), 'count');
		$this->setJoin('memberCategory');
		$this->addJoin('member', new \PHPFUI\ORM\Condition('member.memberId', new \PHPFUI\ORM\Field('memberCategory.memberId')));
		$this->addJoin('membership', new \PHPFUI\ORM\Condition('member.membershipId', new \PHPFUI\ORM\Field('membership.membershipId')));

		$this->setWhere(new \PHPFUI\ORM\Condition('expires', \App\Tools\Date::todayString(), new \PHPFUI\ORM\Operator\GreaterThanEqual()));
		$this->setGroupBy('category');
		$this->setOrderBy('ordering');

		return $this->getDataObjectCursor();
		}
	}
