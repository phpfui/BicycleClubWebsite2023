<?php

namespace App\View\Member;

class Roster
	{
	public function __construct(private readonly \App\View\Page $page, private readonly string $baseUrl = '')
		{
		}

	public function show(string $field = '', string $select = '', int $offset = 0) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();
		$limit = 50;
		$alpha = [];

		for ($i = 0; $i < 26; ++$i)
			{
			$char = \chr(\ord('A') + $i);
			$alpha[$char] = $char;
			}
		$categoryTable = new \App\Table\Category();
		$allCats = $categoryTable->getAllCategories();
		$categories = [];

		foreach ($allCats as $category)
			{
			$categories[$category['categoryId']] = $category['category'];
			}
		$membershipTable = new \App\Table\Membership();
		$membership = $membershipTable->getOldestMembership();

		$firstYear = (int)($membership->joined ?? \App\Tools\Date::todayString());
		$thisYear = (int)\App\Tools\Date::todayString();
		$years = [];
		$condition = '';

		for (; $firstYear <= $thisYear; ++$firstYear)
			{
			$years[$firstYear] = $firstYear;
			}

		$alphaCondition = static fn ($field, $char) : \PHPFUI\ORM\Condition => new \PHPFUI\ORM\Condition($field, $char . '%', new \PHPFUI\ORM\Operator\Like());

		$townCondition = static function($field, $char) : \PHPFUI\ORM\Condition
			{
			$condition = new \PHPFUI\ORM\Condition($field, $char . '%', new \PHPFUI\ORM\Operator\Like());

			return $condition->and(new \PHPFUI\ORM\Condition('member.showNoTown', 0));
			};

		$yearCondition = static function($field, $year) : \PHPFUI\ORM\Condition
			{
			$start = \App\Tools\Date::makeString((int)$year, 1, 1);
			$end = \App\Tools\Date::makeString((int)$year, 12, 31);

			$condition = new \PHPFUI\ORM\Condition($field, $start, new \PHPFUI\ORM\Operator\GreaterThanEqual());
			$condition->and(new \PHPFUI\ORM\Condition($field, $end, new \PHPFUI\ORM\Operator\LessThanEqual()));

			return $condition;
			};

		$categoryCondition = static function($field, $cat) : \PHPFUI\ORM\Condition
			{
			return new \PHPFUI\ORM\Condition($field, $cat);
//			return " left join memberCategory c on c.memberId=m.memberId where {$field}={$cat}";
			};

		$subnav = new \App\UI\SubNav();
		$sections = [
			'firstName' => ['name' => 'First Name', 'range' => $alpha, 'table' => 'member', 'condition' => $alphaCondition, ],
			'lastName' => ['name' => 'Last Name', 'range' => $alpha, 'table' => 'member', 'condition' => $alphaCondition, ],
			'categoryId' => ['name' => 'Category', 'range' => $categories, 'table' => 'memberCategory', 'condition' => $categoryCondition, ],
			'town' => ['name' => 'Town', 'range' => $alpha, 'table' => 'membership', 'condition' => $townCondition, ],
			'joined' => ['name' => 'Year Joined', 'range' => $years, 'table' => 'membership', 'condition' => $yearCondition, ],
		];
		$range = [];
		$table = '';
		$where = new \PHPFUI\ORM\Condition();

		foreach ($sections as $index => $attributes)
			{
			$selected = false;

			if ($field == $index)
				{
				$table = $attributes['table'];
				$range = $attributes['range'];
				$condition = $attributes['condition'];
				$selected = true;
				}
			$subnav->addTab("{$this->baseUrl}/{$index}", $attributes['name'], $selected);
			}
		$container->add($subnav);
		$selectField = "{$table}.{$field}";

		if ($range)
			{
			$rangeNav = new \App\UI\SubNav();

			if ('' == $select)
				{
				$table = '';
				}
			$where = $condition($selectField, $select);

			foreach ($range as $index => $name)
				{
				$rangeNav->addTab("{$this->baseUrl}/{$field}/{$index}", $name, $index == $select);
				}
			$container->add($rangeNav);
			}

		if ($table)
			{
			$memberTable = new \App\Table\Member();
			$memberTable->addJoin('membership');
			$memberTable->setFullJoinSelects();
			$memberTable->addSelect(new \PHPFUI\ORM\Literal('concat(member.firstName, " ", member.lastName)'), 'memberName');

			if ('categoryId' == $field)
				{
				$memberTable->addJoin('memberCategory', 'memberId');
				}

			$where->and(new \PHPFUI\ORM\Condition('member.showNothing', 0));
			$where->and(new \PHPFUI\ORM\Condition('member.deceased', 0));
			$where->and(new \PHPFUI\ORM\Condition('membership.pending', 0));
			$where->and(new \PHPFUI\ORM\Condition('membership.expires', \App\Tools\Date::todayString(), new \PHPFUI\ORM\Operator\GreaterThanEqual()));

			$memberTable->setLimit($limit)->setOffset($offset);
			$memberTable->setWhere($where);

			$members = $memberTable->getDataObjectCursor();
			$count = $members->total();

			if ($count > $limit)
				{
				$limitNav = new \App\UI\SubNav();

				for ($i = 0; $i < $count; $i += $limit)
					{
					$start = $i + 1;
					$end = $i + $limit;

					if ($end > $count)
						{
						$end = $count;
						}
					$limitNav->addTab("{$this->baseUrl}/{$field}/{$select}/{$i}", "{$start}-{$end}", $i == $offset);
					}
				$container->add($limitNav);
				}
			$view = new \App\View\Member($this->page);
			$container->add($view->show($members));
			}

		return $container;
		}

	public function report() : string
		{
		$form = new \PHPFUI\Form($this->page);

		if (\App\Model\Session::checkCSRF() && 'Download' == ($_POST['submit'] ?? ''))
			{
			$roster = new \App\Report\Roster();

			$roster->download($_POST);

			return $form;
			}

		$fieldSet = new \PHPFUI\FieldSet('Limit Roster By Dates');
		$startDate = new \PHPFUI\Input\Date($this->page, 'startDate', 'Joined');
		$startDate->setRequired();
		$endDate = new \PHPFUI\Input\Date($this->page, 'endDate', 'Lapsed');
		$endDate->setRequired();
		$fieldSet->add(new \PHPFUI\MultiColumn($startDate, $endDate));
		$form->add($fieldSet);
		$format = new \PHPFUI\Input\RadioGroup('format', 'Download File Type', 'PDF');
		$format->addButton('PDF');
		$format->addButton('CSV');
		$form->add($format);
		$form->add('<br>');
		$form->add(new \PHPFUI\Submit('Download'));

		return $form;
		}
	}
