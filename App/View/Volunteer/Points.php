<?php

namespace App\View\Volunteer;

class Points
	{
	private readonly \App\Table\Member $memberTable;

	/**
	 * @var string[]
	 *
	 * @psalm-var array{0: string, 1: string, 2: string, 3: string}
	 */
	private array $hidden = ['p', 'l', 'c', 's'];

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->memberTable = new \App\Table\Member();
		}

	public function display(\App\Record\Member $member, int $year) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		if ($member->empty())
			{
			return $container;
			}

		$container->add(new \PHPFUI\SubHeader($member->firstName . ' ' . $member->lastName));

		$container->add(new \App\UI\Display('Available Volunteer Points', $member->leaderPoints ?? 0));

		$categories = [];
		$categories['Ride Leads'] = ['table' => \App\Table\Ride::class, 'date' => 'rideDate', 'name' => 'title'];
		$categories['Assistant Leads'] = ['table' => \App\Table\AssistantLeader::class, 'date' => 'rideDate', 'name' => 'title'];
		$categories['Volunteering'] = ['table' => \App\Table\VolunteerPoint::class, 'date' => 'date', 'name' => 'name'];
		$categories['Cue Sheets'] = ['table' => \App\Table\CueSheet::class, 'date' => 'dateAdded', 'name' => 'name'];
		$categories['Sign In Sheets'] = ['table' => \App\Table\SigninSheet::class, 'date' => 'dateAdded', 'name' => ''];

		$tabs = new \PHPFUI\Tabs();
		$volunteerPointStartDate = \App\Tools\Date::make(2019, 6, 17);
		$selected = true;

		$nowYear = (int)\App\Tools\Date::year(\App\Tools\Date::today());

		if ($year > $nowYear || $year < 2019)
			{
			$year = $nowYear;
			}
		$yearSubNav = new \App\UI\YearSubNav("/Volunteer/myPoints/{$member->memberId}/{$year}", $year, 2019, $nowYear);
		$container->add($yearSubNav);
		$startDate = \App\Tools\Date::toString(\max(\App\Tools\Date::make($year, 1, 1), $volunteerPointStartDate));
		$endDate = \App\Tools\Date::makeString($year, 12, 31);

		foreach ($categories as $name => $category)
			{
			$class = $category['table'];
			$items = $class::getForMemberDate($member->memberId, $startDate, $endDate);

			if ($items)
				{
				$tabs->addTab($name, $this->listDates($items, $category), $selected);
				$selected = false;
				}
			}

		if (\count($tabs))
			{
			$container->add(new \PHPFUI\Header('Categories qualifying for volunteer points', 5));
			$container->add($tabs);
			}
		else
			{
			$container->add(new \PHPFUI\Header('You have no activies that qualify for volunteer points', 5));
			}

		return $container;
		}

	public function listHistory(\PHPFUI\ORM\DataObjectCursor $points) : string
		{
		$container = new \PHPFUI\Container();

		$table = new \PHPFUI\SortableTable();

		// get the parameter we know we are interested in
		$parameters = $table->getParsedParameters();
		$p = (int)($parameters['p'] ?? 0);
		$limit = (int)($parameters['l'] ?? 20);
		$column = $parameters['c'] ?? 'time';
		$sort = $parameters['s'] ?? 'd';
		$total = 0;

		$sortableHeaders = ['time' => 'Time', 'leaderPoints' => 'Leader Points', 'oldLeaderPoints' => 'Pre Edit Points', ];
		$normalHeaders = ['member' => 'Member', 'memberIdEditor' => 'Editor'];
		$table->setSortableColumns(\array_keys($sortableHeaders))->setSortedColumnOrder($column, $sort)->setHeaders($normalHeaders + $sortableHeaders);

		foreach ($points as $point)
			{
			$row = $point->toArray();
			$total += $point->leaderPoints;
			$row['member'] = $this->memberTable->getName((int)$point->memberId);

			if (null == $point->memberIdEditor)
				{
				$point->oldLeaderPoints = null;
				}
			$row['memberIdEditor'] = $this->memberTable->getName((int)$point->memberIdEditor);
			$table->addRow($row);
			}
		$row = ['time' => '<b>Total</b>', 'leaderPoints' => "<b>{$total}</b>"];
		$table->addRow($row);
		// set page to magic value for replacement
		$parameters['p'] = 'PAGE';
		$url = $table->getBaseUrl() . '?' . \http_build_query($parameters);

		$lastPage = (int)((\count($points) - 1) / $limit) + 1;
		// Add the paginator to the bottom
		$paginator = new \PHPFUI\Pagination($p, $lastPage, $url);
		$paginator->center()->setFastForward(10)->setWindow(5);

		$container->add($table);

		$container->add($paginator);

		return "{$container}";
		}

	public function searchHistory() : string
		{
		$button = new \PHPFUI\Button('Search Points');
		$modal = $this->getSearchModal($button, $_GET);
		$output = '';
		$row = new \PHPFUI\GridX();
		$row->add('<br>');

		if ($_GET)
			{
			$pointHistoryTable = new \App\Table\PointHistory();

			$points = $pointHistoryTable->find($_GET);
			$output = $this->listHistory($points);

			if (\count($points))
			 {
			 $output .= $row . $button;
			 }
			}
		else
			{
			$modal->showOnPageLoad();
			}

		return $button . $output;
		}

	protected function getSearchModal(\PHPFUI\HTML5Element $modalLink, array $parameters) : \PHPFUI\Reveal
		{
		$this->setDefaults($parameters);
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$form->setAttribute('method', 'get');
		$fieldSet = new \PHPFUI\FieldSet('Enter criteria to search');

		$memberPicker = new \App\UI\MemberPicker($this->page, new \App\Model\MemberPickerNoSave('Member Name'), 'memberId');
		$fieldSet->add($memberPicker->getEditControl());

		$memberEditorPicker = new \App\UI\MemberPicker($this->page, new \App\Model\MemberPickerNoSave('Editor Name'), 'memberIdEditor');
		$fieldSet->add($memberEditorPicker->getEditControl());

		$from = new \PHPFUI\Input\Date($this->page, 'time_min', 'From Date', $parameters['time_min'] ?? '');
		$to = new \PHPFUI\Input\Date($this->page, 'time_max', 'To Date', $parameters['time_max'] ?? '');
		$fieldSet->add(new \PHPFUI\MultiColumn($from, $to));

		foreach ($this->hidden as $field)
			{
			if (isset($parameters[$field]))
				{
				$fieldSet->add(new \PHPFUI\Input\Hidden($field, $parameters[$field]));
				}
			}
		$form->add($fieldSet);
		$submit = new \PHPFUI\Submit('Search');
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);

		return $modal;
		}

	protected function setDefaults(array &$parameters) : void
		{
		$searchFields = [];
		$searchFields['l'] = 20;
		$searchFields['p'] = 0;

		foreach ($searchFields as $key => $value)
			{
			if (! isset($parameters[$key]))
				{
				$parameters[$key] = $value;
				}
			}
		$parameters['p'] = 0;
		}

	private function listDates(iterable $items, array $category) : \PHPFUI\Table
		{
		$table = new \PHPFUI\Table();
		$headers = ['date' => 'Date', 'name' => 'Name', 'credited' => 'Credited', 'info' => 'Info'];
		$table->setHeaders($headers);

		foreach ($items as $item)
			{
			$row = [];
			$row['date'] = $item[$category['date']];
			$row['name'] = $item[$category['name']];
			$row['credited'] = $item['pointsAwarded'] ? 'Yes' : 'No';
			$row['info'] = $this->getInfoReveal($item, $category);
			$table->addRow($row);
			}

		return $table;
		}

	private function getInfoReveal(\PHPFUI\ORM\DataObject $item, array $category) : \PHPFUI\HTML5Element
		{
		$opener = new \PHPFUI\FAIcon('far', 'question-circle');
		$reveal = new \PHPFUI\Reveal($this->page, $opener);
		$reveal->addClass('large');
		$div = new \PHPFUI\FieldSet('Details');
		$reveal->add($div);
		$reveal->add($reveal->getCloseButton());

		$parameters = ['table' => $category['table'], 'pointsAwarded' => $item['pointsAwarded'] ?? 0];

		foreach ($item->toArray() as $field => $value)
			{
			if (\str_ends_with($field, 'Id'))
				{
				$parameters[$field] = $value;
				}
			}

		$reveal->loadUrlOnOpen('/Volunteer/pointsDetail?' . \http_build_query($parameters), $div->getId());

		return $opener;
		}
	}
