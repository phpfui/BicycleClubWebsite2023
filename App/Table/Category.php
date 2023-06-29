<?php

namespace App\Table;

class Category extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\Category::class;

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

	public function getDistributions() : iterable
		{
		$sql = 'select c.category,count(*) as count from category c ' .
			'left join memberCategory mc on mc.categoryId=c.categoryId ' .
			'left join member m on m.memberId=mc.memberId ' .
			'left join membership s on m.membershipId=s.membershipId ' .
			'where s.expires>=? group by c.category order by c.ordering';

		return \PHPFUI\ORM::getDataObjectCursor($sql, [\App\Tools\Date::todayString()]);
		}
	}
