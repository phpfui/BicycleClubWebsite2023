<?php

namespace App\View\Volunteer;

class Points
	{
	public function __construct(private readonly \App\View\Page $page)
		{
		}

	public function display(\App\Record\Member $member, int $year) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		if ($member->empty())
			{
			return $container;
			}

		$container->add(new \PHPFUI\SubHeader($member->firstName . ' ' . $member->lastName));

		$container->add(new \App\UI\Display('Available Volunteer Points', $member->volunteerPoints ?? 0));

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

			if (\count($items))
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

	public function listHistory(\App\Table\PointHistory $pointsTable) : string
		{
		$container = new \PHPFUI\Container();

		$table = new \App\UI\ContinuousScrollTable($this->page, $pointsTable);

		// get the parameter we know we are interested in

		$sortableHeaders = ['time' => 'Time', 'volunteerPoints' => 'Leader Points', 'oldLeaderPoints' => 'Pre Edit Points', ];
		$normalHeaders = ['member' => 'Member', 'editorId' => 'Editor'];
		$table->addCustomColumn('member', static fn (array $row) : string => new \App\Record\Member($row['memberId'])->fullName());
		$table->addCustomColumn('editorId', static function(array $row) : string {if (empty($row['editorId'])) { return 'System';} $member = new \App\Record\Member($row['editorId']);

return $member->fullName();});
		$table->setSortableColumns(\array_keys($sortableHeaders))->setHeaders($normalHeaders + $sortableHeaders)->setSearchColumns($sortableHeaders);
		$container->add($table);

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
			$this->setSearch($pointHistoryTable, $_GET);
			$output = $this->listHistory($pointHistoryTable);

			$output .= $button;
			}
		else
			{
			$modal->showOnPageLoad();
			}

		return $button . $output;
		}

	/**
	 * @param array<string,string> $parameters
	 */
	protected function getSearchModal(\PHPFUI\HTML5Element $modalLink, array $parameters) : \PHPFUI\Reveal
		{
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$form->setAttribute('method', 'get');
		$fieldSet = new \PHPFUI\FieldSet('Enter criteria to search');

		$memberPicker = new \App\UI\MemberPicker($this->page, new \App\Model\MemberPickerNoSave('Member Name'), 'memberId');
		$fieldSet->add($memberPicker->getEditControl());

		$memberEditorPicker = new \App\UI\MemberPicker($this->page, new \App\Model\MemberPickerNoSave('Editor Name'), 'editorId');
		$fieldSet->add($memberEditorPicker->getEditControl());

		$from = new \PHPFUI\Input\Date($this->page, 'time_min', 'From Date', $parameters['time_min'] ?? '');
		$to = new \PHPFUI\Input\Date($this->page, 'time_max', 'To Date', $parameters['time_max'] ?? '');
		$fieldSet->add(new \PHPFUI\MultiColumn($from, $to));

		$form->add($fieldSet);
		$submit = new \PHPFUI\Submit('Search');
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);

		return $modal;
		}

	/**
	 * @param array<string,string> $parameters
	 */
	protected function setSearch(\App\Table\PointHistory $pointHistoryTable, array $parameters) : static
		{
		$condition = new \PHPFUI\ORM\Condition();

		if (! empty($parameters['memberId']))
			{
			$condition->and('memberId', $parameters['memberId']);
			}

		if (! empty($parameters['editorId']))
			{
			$condition->and('editorId', $parameters['editorId']);
			}

		if (! empty($parameters['time_min']))
			{
			$condition->and('time', $parameters['time_min'], new \PHPFUI\ORM\Operator\GreaterThanEqual());
			}

		if (! empty($parameters['time_max']))
			{
			$condition->and('time', $parameters['time_max'], new \PHPFUI\ORM\Operator\LessThanEqual());
			}

		$pointHistoryTable->setWhere($condition);
		$pointHistoryTable->setOrderBy('time');

		return $this;
		}

	/**
	 * @param array<string,string> $category
	 */
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

	/**
	 * @param array<string,string> $category
	 */
	private function listDates(\PHPFUI\ORM\DataObjectCursor $items, array $category) : \PHPFUI\Table
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
	}
