<?php

namespace App\Model;

class Calendar
	{
	/**
	 * @var string[]
	 *
	 * @psalm-var array{eventDate: string, title: string, distances: string, startTime: string, location: string, publicContact: string}
	 */
	private array $columns = ['eventDate' => 'Date',
		'title' => 'Name',
		'distances' => 'Distances',
		'startTime' => 'Start',
		'location' => 'Location',
		'publicContact' => 'Contact', ];

	public function approve(\App\Record\Calendar $calendar) : void
		{
		$calendar->pending = 0;
		$calendar->update();
		$email = new \App\Model\Email('acceptCalendar', new \App\Model\Email\Calendar($calendar));
		$email->setFromMember(\App\Model\Session::getSignedInMember());
		$this->sendEmail($email, $calendar);
		}

	public function getColumns() : array
		{
		return $this->columns;
		}

	public function getData(array $request = []) : \App\Table\Calendar
		{
		$calendarTable = new \App\Table\Calendar();

		$sort = 'eventDate';

		if (isset($request['d']))
			{
			$order = ' desc';
			}
		else
			{
			$order = '';
			}
		$calendarTable->addOrderBy($sort, $order);
		$condition = new \PHPFUI\ORM\Condition('eventDate', \App\Tools\Date::todayString(), new \PHPFUI\ORM\Operator\GreaterThanEqual());
		$condition->and('pending', 0);
		$calendarTable->setWhere($condition);

		return $calendarTable;
		}

	/**
	 * @return (mixed|string)[]
	 *
	 * @psalm-return array<array-key, mixed|string>
	 */
	public function getHeaders(array $request = [], int $panel = 0, array $additional = []) : array
		{
		if ($panel)
			{
			$panel = "&p={$panel}";
			}
		else
			{
			$panel = '';
			}

		if (isset($request['s']))
			{
			$sort = $request['s'];
			}
		else
			{
			$sort = 'eventDate';
			}
		$headers = [];

		foreach ($this->columns as $field => $headerText)
			{
			$link = "?s={$field}";
			$icon = '';

			if ($field == $sort)
				{
				if (empty($request['d']))
					{
					$icon = new \PHPFUI\FAIcon('fas', 'arrow-up');
					$link .= '&d=1';
					}
				else
					{
					$icon = new \PHPFUI\FAIcon('fas', 'arrow-down');
					}
				}
			$headers[$field] = "<a href='{$link}{$panel}'>{$headerText}{$icon}</a>";
			}

		return \array_merge($headers, $additional);
		}

	/**
	 * @return string[]
	 *
	 * @psalm-return array{1: string, 2: string, 3: string, 4: string, 5?: string}
	 */
	public function getTabs() : array
		{
		$types = [1 => 'Tour',
			2 => 'Charity',
			3 => 'Race',
			4 => 'Cycling Related', ];

		if (\App\Model\Session::isSignedIn())
			{
			$settingTable = new \App\Table\Setting();
			$types[5] = $settingTable->value('clubAbbrev') . ' Only';
			}

		return $types;
		}

	public function thankYouNote(\App\Record\Calendar $calendar) : void
		{
		$calendar->update();
		$email = new \App\Model\Email('thankYouCalendar', new \App\Model\Email\Calendar($calendar));
		$this->sendEmail($email, $calendar);
		}

	public function reject(\App\Record\Calendar $calendar, string $message) : void
		{
		$calendar->pending = 2;
		$calendar->update();
		$email = new \App\Model\Email('rejectCalendar', new \App\Model\Email\Calendar($calendar, $message));
		$email->setFromMember(\App\Model\Session::getSignedInMember());
		$this->sendEmail($email, $calendar);
		}

	private function sendEmail(\App\Model\Email $email, \App\Record\Calendar $calendar) : static
		{
		$send = false;

		if (\filter_var($calendar->privateEmail, FILTER_VALIDATE_EMAIL))
			{
			$send = true;
			$email->addTo($calendar->privateEmail, $calendar->privateContact);
			}

		if (\filter_var($calendar->publicEmail, FILTER_VALIDATE_EMAIL))
			{
			$send = true;
			$email->addTo($calendar->publicEmail, $calendar->publicContact);
			}

		if ($send)
			{
			$email->send();
			}

		return $this;
		}
	}
