<?php

namespace App\View;

class Calendar
	{
	private readonly \App\Model\Calendar $model;

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->model = new \App\Model\Calendar();
		$this->processRequest();
		}

	public function edit(\App\Record\Calendar $calendar = new \App\Record\Calendar(), bool $publicEditor = false) : \App\UI\ErrorFormSaver
		{
		if ($calendar->loaded())
			{
			$submit = new \PHPFUI\Submit();
			$form = new \App\UI\ErrorFormSaver($this->page, $calendar, $submit);
			$id = $calendar->calendarId;
			}
		else
			{
			$submit = new \PHPFUI\Submit('Add');
			$form = new \App\UI\ErrorFormSaver($this->page, $calendar);
			$id = 0;
			}

		if ($form->save())
			{
			if ($publicEditor)
				{
				$calendar->pending = 1;
				$calendar->update();
				}

			return $form;
			}
		$fieldSet = new \PHPFUI\FieldSet('Required Fields');
		$title = new \PHPFUI\Input\Text('title', 'Event Title', $calendar['title']);
		$title->setToolTip('This is the name of the event, what it is generally known as. This will appear on the first page of the listing, so it should be clear.');
		$title->setRequired();
		$types = $this->model->getTabs();
		$select = new \PHPFUI\Input\Select('eventType', 'Event Type');
		$select->setRequired();

		foreach ($types as $key => $value)
			{
			$select->addOption($value, (string)$key, $calendar->eventType == $key);
			}
		$toolTip = new \PHPFUI\ToolTip($select, 'Please choose the appropriate event type from the following:<br><dl><dt><b>Tour</b></dt><dd>Tours are untimed and generally no mass start, and no prizes.
      </dd><dt><b>Charity</b></dt><dd>Fund raising would be the primary reason for this event.
      </dd><dt><b>Race</b></dt><dd>Races are timed events, often with mass starts and prizes for places.
      </dd><dt><b>Cycling Related</b></dt><dd>Any event where actual bicycling is not the primary focus, but it relates to cycling.</dd></dl>');
		$toolTip->addAttribute('data-allow-html', 'true');
		$fieldSet->add(new \PHPFUI\MultiColumn($title, $toolTip));
		$date = new \PHPFUI\Input\Date($this->page, 'eventDate', 'Event Date', $calendar->eventDate);
		$date->setMinDate(\App\Tools\Date::todayString());
		$date->setRequired();
		$date->setToolTip('This is the start date of the event. It is the first day of a multiday event. Your listing will be displayed on this date, but not after.');
		$calendar->eventDays = \max(1, $calendar->eventDays);
		$days = new \PHPFUI\Input\Number('eventDays', 'Number Of Days', $calendar->eventDays);
		$days->addAttribute('max', (string)99)->addAttribute('min', (string)0);
		$days->setRequired();
		$days->setToolTip('Normally one, this is the number of days the event runs for.');
		$fieldSet->add(new \PHPFUI\MultiColumn($date, $days));
		$location = new \PHPFUI\Input\Text('location', 'Start Location', $calendar->location);
		$location->setRequired();
		$location->setToolTip('This is where the event starts or is located, but do not include the state (see next question). It should be a town or other easily reconized location by the general public. It should not be used for directions, web addresses, or very specific locations. Towns and well known parks are good examples of start locations.');
		$select = new \App\UI\State($this->page, 'state', 'State', $calendar->state ?? '');
		$select->setRequired();
		$select->setToolTip('Please select a state.  You should not add it to the location.  This is so we can provide state by state listings if we want.');
		$fieldSet->add(new \PHPFUI\MultiColumn($location, $select));
		$description = new \PHPFUI\Input\TextArea('description', 'Description', $calendar->description);
		$description->setToolTip('This is a free form text field which should describe your event. You are free to put any information in this field.');
		$description->setRequired();
		$fieldSet->add($description);

		$privateEmail = new \PHPFUI\Input\Email('privateEmail', 'Private email address', $calendar->privateEmail);
		$privateEmail->setRequired();
		$privateEmail->setToolTip('This is your email address. It will not be made public. We will use it to contact you if we have questions about your event.');
		$fieldSet->add($privateEmail);

		$form->add($fieldSet);
		$callout = new \PHPFUI\Callout('info');
		$callout->add('If you provide a <b>Private Contact</b> name, you will get a link to edit your event once it has been posted. Edited events will have to be reapproved.');
		$form->add($callout);
		$fieldSet = new \PHPFUI\FieldSet('Optional Fields: (but recommended)');
		$distances = new \PHPFUI\Input\Text('distances', 'Distances', $calendar->distances);
		$distances->setToolTip('Most events have multiple distances you can ride. We allow you to list up to 6, but feel free to add additional route information in the description.');

		$price = new \PHPFUI\Input\Number('price', 'Event Cost', empty($calendar->price) ? '' : $calendar->price);
		$price->setToolTip('This is the fee for participating in the event. Leave it blank if it is free.');

		if (! $id)
			{
			$calendar->startTime = null;
			}
		$startTime = new \PHPFUI\Input\Time($this->page, 'startTime', 'Start Time', $calendar->startTime ?? '');
		$startTime->setToolTip('This is the time the event starts, or when it opens to the public. This is simply provided for organizational information only to make it easy for people to find in the description.');
		$fieldSet->add(new \PHPFUI\MultiColumn($distances, $price, $startTime));
		$webSite = new \PHPFUI\Input\Url('webSite', 'Web Site', $calendar->webSite);
		$webSite->setToolTip('We highly recommend you include a web site link. This allows people to find even more information about event. We will present it so users can simply click it and will be taken to the page you supply.');
		$publicEmail = new \PHPFUI\Input\Email('publicEmail', 'Public Email', $calendar->publicEmail);
		$publicEmail->setToolTip('This is the email address of the person you want contacted in your organization if one of our web site vistors has a question about your event. We do not publish your email addresses on the web so it can not be picked up by web crawlers and be a source of SPAM. We only allow a user to fill out a form and we send it to you directly without the user seeing your email address. If you respond to them, they will see your email address.');
		$fieldSet->add(new \PHPFUI\MultiColumn($webSite, $publicEmail));
		$publicContact = new \PHPFUI\Input\Text('publicContact', 'Public Contact', $calendar->publicContact);
		$publicContact->setToolTip('This should be a person\'s name from your organization. If it is not filled out, we will just refer to the Event Organizer.');
		$privateContact = new \PHPFUI\Input\Text('privateContact', 'Private Contact', $calendar->privateContact);
		$privateContact->setToolTip('This should be a person\'s name in your organization. We will not show this on the web site. We will only use it to contact you in case we have questions about your event.');
		$fieldSet->add(new \PHPFUI\MultiColumn($publicContact, $privateContact));
		$form->add($fieldSet);
		$buttonGroup = new \App\UI\CancelButtonGroup();
		$buttonGroup->addButton($submit);

		if ($this->page->isAuthorized('Approve Calendar Entries'))
			{
			$approve = new \PHPFUI\Button('Approve', "/Calendar/approve/{$id}");
			$approve->addClass('success');
			$buttonGroup->addButton($approve);
			$deny = new \PHPFUI\Button('Deny', "/Calendar/deny/{$id}");
			$deny->addClass('warning');
			$buttonGroup->addButton($deny);
			$delete = new \PHPFUI\Button('Delete', "/Calendar/delete/{$id}");
			$delete->setConfirm('Are you sure you want to delete this event?  It can not be undone.');
			$delete->addClass('alert');
			$buttonGroup->addButton($delete);
			}
		$form->add($buttonGroup);

		return $form;
		}

	public function reject(\App\Record\Calendar $calendar) : string
		{
		$form = new \PHPFUI\Form($this->page);
		$parts = $this->getCalendarItem($calendar);
		$calendarTable = new \App\Table\Calendar();

		$fields = ['title', 'description', 'distances', 'price', 'eventDate', 'startTime', 'eventDays', 'webSite', 'location', 'state', 'publicContact', 'publicEmail', 'privateContact', 'privateEmail', ];

		foreach ($fields as $field)
			{
			if (! empty($parts[$field]))
				{
				$translated = $calendarTable->translate($field);
				$form->add(new \App\UI\Display($translated, $parts[$field]));
				}
			}

		$message = new \PHPFUI\Input\TextArea('message', 'Details on why the event was rejected.');
		$message->setToolTip('Add any specific comments to the submitter here.  This will be added to the standard boilerplate for rejected events.');
		$message->setRequired();
		$form->add($message);
		$form->add(new \PHPFUI\Input\Hidden('calendarId', (string)$calendar->calendarId));

		$buttonGroup = new \PHPFUI\ButtonGroup();
		$buttonGroup->addButton(new \PHPFUI\Submit('Reject Event', 'action'));
		$cancel = new \App\UI\Cancel();
		$cancel->setLink('/Calendar/edit/' . $calendar->calendarId);
		$buttonGroup->addButton($cancel);
		$form->add($buttonGroup);

		return $form;
		}

	public function showCalendar(\App\Table\Calendar $calendarTable, array $parameters = [], array $showTabs = []) : \PHPFUI\Tabs | \PHPFUI\Table
		{
		$additionalHeaders = [];

		if ($this->page->isAuthorized('Edit Calendar Event'))
			{
			$additionalHeaders['edit'] = 'Edit';
			$spanCols = [7];
			}
		else
			{
			$spanCols = [6];
			}
		$panel = 0;
		$all = new \PHPFUI\Table();
		$all->setHeaders($this->model->getHeaders($parameters, $panel++, $additionalHeaders));
		$club = new \PHPFUI\Table();
		$club->setHeaders($this->model->getHeaders($parameters, $panel++, $additionalHeaders));
		$tour = new \PHPFUI\Table();
		$tour->setHeaders($this->model->getHeaders($parameters, $panel++, $additionalHeaders));
		$charity = new \PHPFUI\Table();
		$charity->setHeaders($this->model->getHeaders($parameters, $panel++, $additionalHeaders));
		$race = new \PHPFUI\Table();
		$race->setHeaders($this->model->getHeaders($parameters, $panel++, $additionalHeaders));
		$related = new \PHPFUI\Table();
		$related->setHeaders($this->model->getHeaders($parameters, $panel++, $additionalHeaders));

		foreach ($calendarTable->getRecordCursor() as $item)
			{
			$item->description = \str_replace("\n", '<br>', $item->description ?? '');
			$description = ['eventDate' => \App\Tools\TextHelper::addLinks($item->description)];
			$all->addRow($this->getCalendarItem($item));
			$all->addRow($description, $spanCols);

			switch ($item->eventType)
				{
				case 1:
					$tour->addRow($this->getCalendarItem($item));
					$tour->addRow($description, $spanCols);

					break;

				case 2:
					$charity->addRow($this->getCalendarItem($item));
					$charity->addRow($description, $spanCols);

					break;

				case 3:
					$race->addRow($this->getCalendarItem($item));
					$race->addRow($description, $spanCols);

					break;

				case 4:
					$related->addRow($this->getCalendarItem($item));
					$related->addRow($description, $spanCols);

					break;

				case 5:
					$club->addRow($this->getCalendarItem($item));
					$club->addRow($description, $spanCols);

					break;
				}
			}
		$tabs = new \PHPFUI\Tabs();

		if (! $showTabs)
			{
			$showTabs = \array_merge(['All'], $this->model->getTabs());
			}

		if (1 == \count($showTabs))
			{
			return $club;
			}
		$activeTab = $parameters['p'] ?? 0;
		$tabCount = 0;
		$tabs->addTab('All', $all, $tabCount++ == $activeTab);
		$settingTable = new \App\Table\Setting();
		$clubName = $settingTable->value('clubAbbrev') . ' Only';
		$tabs->addTab($clubName, $club, $tabCount++ == $activeTab);
		$tabs->addTab('Tour', $tour, $tabCount++ == $activeTab);
		$tabs->addTab('Charity', $charity, $tabCount++ == $activeTab);
		$tabs->addTab('Race', $race, $tabCount++ == $activeTab);
		$tabs->addTab('Cycling Related', $related, $tabCount++ == $activeTab);

		return $tabs;
		}

	protected function processRequest() : void
		{
		if (\App\Model\Session::checkCSRF())
			{
			if (isset($_POST['submit']) && 'Add' == $_POST['submit'])
				{
				$calendar = new \App\Record\Calendar();
				$calendar->setFrom($_POST);
				$id = $calendar->insert();
				$url = $this->page->getBaseURL();
				$pos = \strrpos($url, '/');

				if ($pos > 0)
					{
					$url = \substr($url, 0, $pos + 1);
					}

				if ($this->page->isAuthorized('Edit Calendar Event'))
					{
					$this->page->redirect($url . 'edit/' . $id);
					}
				else
					{
					$this->page->redirect('/Calendar/thankYou/' . $id);
					}
				}
			elseif (isset($_POST['action']))
				{
				switch ($_POST['action'])
					{
					case 'Reject Event':
						$this->model->reject(new \App\Record\Calendar((int)$_POST['calendarId']), $_POST['message']);
						$this->page->redirect('/Calendar/pending');

						break;
					}
				}
			}
		}

	/**
	 * @return (\PHPFUI\FAIcon|\PHPFUI\Link|mixed|string)[]
	 *
	 * @psalm-return array{startTime: string, eventDate: string, webSite: mixed, title: \PHPFUI\Link|mixed, location: string, publicEmail: mixed, publicContact: mixed|string, edit: \PHPFUI\FAIcon}
	 */
	private function getCalendarItem(\App\Record\Calendar $item) : array
		{
		$id = $item->calendarId;
		$row = $item->toArray();
		$row['startTime'] = \App\Tools\TimeHelper::toSmallTime($item->startTime ?? '');
		$date = $item->eventDate;

		if ($item->eventDays > 1)
			{
			$date .= '<br>' . \App\Tools\Date::toString(\App\Tools\Date::fromString($item->eventDate) + (int)$item->eventDays - 1);
			}
		$row['eventDate'] = $date;
		$title = $item->title;

		if ($item->webSite)
			{
			$title = new \PHPFUI\Link($item->webSite, $title);
			}
		$row['title'] = $title;
		$row['location'] .= ',' . $item->state;
		$contact = $item->publicContact;

		if (\filter_var($item->publicEmail, FILTER_VALIDATE_EMAIL))
			{
			$contact = "<a href='mailto:{$item->publicEmail}'>{$contact}</a>";
			}
		$row['publicContact'] = $contact;
		$row['edit'] = new \PHPFUI\FAIcon('far', 'edit', '/Calendar/edit/' . $id);

		return $row;
		}
	}
