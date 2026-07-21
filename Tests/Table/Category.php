<?php

namespace Tests\Table;

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
		$sql = 'select c.category,count(*) as count from category c ' .
			'left join memberCategory mc on mc.categoryId=c.categoryId ' .
			'left join member m on m.memberId=mc.memberId ' .
			'left join membership s on m.membershipId=s.membershipId ' .
			'where s.expires>=? group by c.category order by c.ordering';

		return \PHPFUI\ORM::getDataObjectCursor($sql, [\App\Tools\Date::todayString()]);
		}
	}
