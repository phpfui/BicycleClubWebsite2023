<?php

namespace App\Table;

class Pace extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\Pace::class;

	/** @var array<int, array<string,string>> */
	private array $paces = [];

	public function __construct()
		{
		parent::__construct();
		$paces = \PHPFUI\ORM::getRows('select * from pace order by ordering');

		foreach ($paces as $pace)
			{
			$this->paces[$pace['paceId']] = $pace;
			}
		}

	public function getCategoryIdFromPaceId(?int $paceId) : int
		{
		return $this->paces[$paceId]['categoryId'] ?? 0;
		}

	public function getPace(int $paceId) : string
		{
		return $this->paces[$paceId]['pace'] ?? 'All';
		}

	public function getPaceOrder(int $categoryId) : \PHPFUI\ORM\DataObjectCursor
		{
		$sql = 'select * from pace where categoryId=? order by ordering';

		return \PHPFUI\ORM::getDataObjectCursor($sql, [$categoryId]);
		}

	/** @return array<int, array<string,string>> */
	public function getPaces() : array
		{
		return $this->paces;
		}

	/**
	 * @param array<int> $categories
	 *
	 * @return array<int>
	 */
	public function getPacesForCategories(array $categories) : array
		{
		$paces = [];

		foreach ($this->paces as $pace)
			{
			if (\in_array($pace['categoryId'], $categories))
				{
				$paces[] = (int)$pace['paceId'];
				}
			}

		return $paces;
		}

	public function movePace(int $fromPace, int $toCategory) : void
		{
		if ($fromPace && $toCategory)
			{
			$pace = new \App\Record\Pace($fromPace);

			if ($pace->categoryId != $toCategory)
				{
				$newPace = new \App\Record\Pace($pace->paceId);
				$pace->delete();
				$pace->paceId = 0;
				$newPace->categoryId = $toCategory;
				$toPace = $newPace->insert();
				\App\Table\Ride::changePace($fromPace, $toPace);
				}
			}
		}

	public function reorderPace(int $categoryId) : bool
		{
		$sql = 'update pace left join category on category.categoryId=pace.categoryId set pace.ordering=(category.ordering*100)+pace.ordering where pace.categoryId=?';

		return \PHPFUI\ORM::execute($sql, [$categoryId]);
		}
	}
