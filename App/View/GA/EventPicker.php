<?php

namespace App\View\GA;

class EventPicker implements \Stringable
	{
	/**
	 * @var array<int,int>
	 */
	private array $selected = [];

	public function __construct(private readonly \App\View\Page $page, private \App\Enum\GeneralAdmission\EventPicker $type = \App\Enum\GeneralAdmission\EventPicker::MULTIPLE, private readonly string $title = 'Select GA Events', private readonly string $link = '')
		{
		if (\App\Model\Session::checkCSRF())
			{
			if (isset($_POST['action']))
				{
				switch ($_POST['action'])
					{
					case 'deleteEvent':
						$gaEvent = new \App\Record\GaEvent((int)$_POST['gaEventId']);
						$gaEvent->delete();
						$this->page->setResponse($_POST['gaEventId']);

						break;
					}
				}
			}
		}

	public function __toString() : string
		{
		$gaEventTable = new \App\Table\GaEvent();
		$gaEventTable->setOrderBy('eventDate', 'desc');
		$events = $gaEventTable->getRecordCursor();

		$fieldSet = new \PHPFUI\FieldSet($this->title);

		switch ($this->type)
			{
			case \App\Enum\GeneralAdmission\EventPicker::SINGLE_SELECT:

				$select = new \PHPFUI\Input\Select('gaEventId');

				foreach ($events as $event)
					{
					$select->addOption($event->eventDate . ' / ' . $event->title, $event->gaEventId, (bool)($this->selected[$event->gaEventId] ?? 0));
					}
				$fieldSet->add($select);

				break;

			case \App\Enum\GeneralAdmission\EventPicker::SINGLE:

				$radio = new \PHPFUI\Input\RadioGroup('gaEventId', '', (string)($this->selected[$events->current()->gaEventId ?? 0] ?? false));
				$radio->setSeparateRows();

				foreach ($events as $event)
					{
					$radio->addButton($event->eventDate . ' / ' . $event->title, $event->gaEventId);
					}
				$fieldSet->add($radio);

				break;

			case \App\Enum\GeneralAdmission\EventPicker::MULTIPLE:

				$multiColumn = new \PHPFUI\MultiColumn();

				foreach ($events as $event)
					{
					$cb = new \PHPFUI\Input\CheckBoxBoolean("gaEventId[{$event->gaEventId}]", $event->eventDate . ' / ' . $event->title, (bool)($this->selected[$event->gaEventId] ?? 0));
					$cb->setToolTip(\Soundasleep\Html2Text::convert($event->description ?? '', ['drop_links' => true, 'ignore_errors' => true]));
					$multiColumn->add($cb);

					if (\count($multiColumn) >= 2)
						{
						$fieldSet->add($multiColumn);
						$multiColumn = new \PHPFUI\MultiColumn();
						}
					}

				if (\count($multiColumn))
					{
					$fieldSet->add($multiColumn);
					}

				break;

			case \App\Enum\GeneralAdmission\EventPicker::LINK:

				$ul = new \PHPFUI\UnorderedList();

				foreach ($events as $event)
					{
					$title = $event->title . ' / ' . \App\Tools\Date::formatString('F j, Y', $event->eventDate);
					$ul->addItem(new \PHPFUI\ListItem("<a href='{$this->link}/{$event->gaEventId}'>{$title}</a>"));
					}
				$fieldSet->add($ul);

				break;

			case \App\Enum\GeneralAdmission\EventPicker::TABLE:

					$searchableHeaders = ['eventDate' => 'Date', 'title' => 'Title', ];
					$countHeaders = ['stats' => 'Stats', 'sheets' => 'Sign In Sheets', 'signs' => 'Pre Reg Signs', 'copy' => 'Copy', 'del' => 'Del'];

					$view = new \App\UI\ContinuousScrollTable($this->page, $gaEventTable);
					$view->setRecordId('gaEventId');

					$deleter = new \App\Model\DeleteRecord($this->page, $view, $gaEventTable, 'Permanently delete this event and all associated data?');
					$view->addCustomColumn('del', $deleter->columnCallback(...));
					$view->addCustomColumn('title', static fn (array $event) => (string)new \PHPFUI\Link('/GA/edit/' . $event['gaEventId'], $event['title'] ?? 'Missing', false));
					$view->addCustomColumn('stats', static fn (array $event) => (string)new \PHPFUI\FAIcon('fas', 'chart-bar', '/GA/statistics/' . $event['gaEventId']));
					$view->addCustomColumn('signs', static fn (array $event) => (string)new \PHPFUI\FAIcon('fas', 'sign-hanging', '/GA/signs/' . $event['gaEventId']));
					$view->addCustomColumn('sheets', static fn (array $event) => (string)new \PHPFUI\FAIcon('fas', 'file-signature', '/GA/signIn/' . $event['gaEventId']));
					$view->addCustomColumn('copy', static fn (array $event) => (string)new \PHPFUI\FAIcon('fas', 'copy', '/GA/copy/' . $event['gaEventId']));

					$view->setSearchColumns($searchableHeaders)->setSortableColumns(\array_keys($searchableHeaders));
					$view->setHeaders(\array_merge($searchableHeaders, $countHeaders));

				$fieldSet->add($view);

				break;
			}

		return (string)$fieldSet;
		}

	public function publicEvents(string $link = '/GA/signUp') : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();
		$gaEventTable = new \App\Table\GaEvent();
		$gaEventTable->setOrderBy('eventDate', 'desc');
		$events = $gaEventTable->getRecordCursor();

		$ul = new \PHPFUI\UnorderedList();
		$today = \App\Tools\Date::todayString();
		$count = 0;
		$gaEventId = 0;

		foreach ($events as $event)
			{
			if ($event->eventDate >= $today)
				{
				$gaEventId = $event->gaEventId;
				$title = $event->title . ' / ' . \App\Tools\Date::formatString('F j, Y', $event->eventDate);
				$ul->addItem(new \PHPFUI\ListItem("<a href='{$link}/{$event->gaEventId}'>{$title}</a>"));
				++$count;
				}
			else
				{
				break;
				}
			}

		if ($count > 1)
			{
			$fieldSet = new \PHPFUI\FieldSet('Pick An Event');
			$fieldSet->add($ul);
			$container->add($fieldSet);
			}
		elseif ($count)
			{
			$this->page->redirect($link . '/' . $gaEventId);
			}
		else
			{
			$container->add(new \PHPFUI\SubHeader('There are no upcoming events'));
			}

		return $container;
		}

	/**
	 * @param array<int>|int $selected
	 */
	public function setSelected(array | int $selected) : static
		{
		if (! \is_array($selected))
			{
			$selected = [(int)$selected => 1];
			}

		$this->selected = $selected;

		return $this;
		}
	}
