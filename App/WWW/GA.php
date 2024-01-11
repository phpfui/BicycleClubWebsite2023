<?php

namespace App\WWW;

class GA extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	public function download() : void
		{
		if ($this->page->addHeader('Download Registrants'))
			{
			if (isset($_POST['submit']) && \App\Model\Session::checkCSRF())
				{
				$csvWriter = new \App\Tools\CSV\FileWriter('GARegistrants.csv');
				$csvWriter->addHeaderRow();

				$events = [];

				foreach ($_POST['gaEventId'] as $gaEventId => $active)
					{
					if ($active)
						{
						$events[] = $gaEventId;
						}
					}

				if (empty($events))
					{
					\App\Model\Session::setFlash('alert', 'You must select at least one event');
					$this->page->redirect();

					return;
					}

				$gaRiderTable = new \App\Table\GaRider();
				$riders = $gaRiderTable->getForEvents($events);

				foreach ($riders as $rider)
					{
					$csvWriter->outputRow($this->processRider($rider));
					}
				}
			else
				{
				$form = new \PHPFUI\Form($this->page);
				$form->setAreYouSure(false);
				$form->add(new \App\View\GA\EventPicker($this->page, \App\View\GA\EventPicker::MULTIPLE, 'Select Events'));
				$form->add(new \PHPFUI\Submit('Download'));
				$this->page->addPageContent($form);
				}
			}
		}

	public function edit(\App\Record\GaEvent $event = new \App\Record\GaEvent()) : void
		{
		$header = $event->loaded() ? 'Edit GA Event' : 'Add GA Event';

		if ($this->page->addHeader($header))
			{
			$view = new \App\View\GA\EventEdit($this->page);
			$this->page->addPageContent($view->Edit($event));
			}
		}

	public function editRider(\App\Record\GaRider $rider = new \App\Record\GaRider()) : void
		{
		if ($this->page->addHeader($rider->loaded() ? 'Edit Rider' : 'Add Registration'))
			{
			$view = new \App\View\GA\Rider($this->page);
			$this->page->addPageContent($view->edit($rider));
			}
		}

	public function email() : void
		{
		if ($this->page->addHeader($label = 'Email Registrants'))
			{
			$this->page->addPageContent(new \App\View\GA\Email($this->page));
			}
		}

	public function find() : void
		{
		if ($this->page->addHeader('Find Registrants'))
			{
			$view = new \App\View\GA\Rider($this->page);
			$gaRidersTable = new \App\Table\GaRider();
			$this->page->addPageContent($view->show($gaRidersTable));
			}
		}

	public function labels() : void
		{
		if ($this->page->addHeader($label = 'Mailing Labels'))
			{
			if (isset($_POST['submit']) && \App\Model\Session::checkCSRF())
				{
				$report = new \App\Report\GALabels($_POST['gaEventId'], $_POST['label']);

				if (empty($report->getEvents()))
					{
					\App\Model\Session::setFlash('alert', 'You must select at least one event');
					$this->page->redirect();

					return;
					}
				$report->generate();
				$this->page->done();
				}
			else
				{
				$form = new \PHPFUI\Form($this->page);
				$form->setAreYouSure(false);
				$form->add(new \App\View\GA\EventPicker($this->page, \App\View\GA\EventPicker::MULTIPLE, 'Select Events To Include In Labels'));
				$form->add(new \App\UI\LabelStock());
				$form->add(new \PHPFUI\Submit('Download ' . $label));
				$this->page->addPageContent($form);
				}
			}
		}

	public function landingPageEditor(string $item = '') : void
		{
		$item = \in_array($item, ['Header', 'Footer', 'Global', 'Tab', 'Reorder']) ? $item : '';
		$page = "Landing Page {$item} Editor";
		$page = \str_replace('  ', ' ', $page);

		if ($this->page->addHeader($page))
			{
			$view = new \App\View\GA\LandingEditor($this->page);
			$this->page->addPageContent($view->menu($item));
			}
		}

	public function manage() : void
		{
		if ($this->page->addHeader('Manage Dates'))
			{
			$this->page->addPageContent((new \PHPFUI\Button('Add Event', '/GA/edit/0'))->addClass('success'));
			$this->page->addPageContent(new \App\View\GA\EventPicker($this->page, \App\View\GA\EventPicker::TABLE, 'Existing Events', '/GA/edit'));
			}
		}

	public function register() : void
		{
		$this->page->setPublic();
		$gaEventTable = new \App\Table\GaEvent();
		$gaEventTable->setOrderBy('eventDate', 'desc');
		$events = $gaEventTable->getRecordCursor();

		$today = \App\Tools\Date::todayString();
		$settingTable = new \App\Table\Setting();
		$clubAbbrev = $settingTable->value('clubAbbrev');
		$hr = '';
		$activeEvents = 0;

		foreach ($events as $event)
			{
			if (! $event->allowShopping)
				{
				$this->page->setShowMenus(false);
				}

			if ($today <= $event->eventDate)
				{
				++$activeEvents;
				$this->page->addPageContent($hr);
				$hr = '<hr>';
				$this->page->addPageContent(new \PHPFUI\Header($event->title));
				$this->page->addPageContent(\App\Tools\TextHelper::unhtmlentities($event->description));
				$model = new \App\Model\GeneralAdmission();
				$spotsLeft = $event->maxRegistrants - $model->totalRegistrants($event);
				$message = '';

				if ($spotsLeft > 0)
					{
					if ($event->showPreregistration && $spotsLeft > 0)
						{
						$message = "Only {$spotsLeft} registrations left. Register Today!";
						}
					}
				else
					{
					$message = 'This event is sold out!';
					}

				if ($message)
					{
					$row = new \PHPFUI\GridX();
					$row->add("<strong>{$message}</strong>");
					$this->page->addPageContent($row);
					$row = new \PHPFUI\GridX();
					$row->add('&nbsp;');
					$this->page->addPageContent($row);
					}

				if ($spotsLeft > 0)
					{
					$buttonGroup = new \PHPFUI\ButtonGroup();

					if (\App\Model\Session::isSignedIn())
						{
						$buttonGroup->addButton(new \PHPFUI\Button('Continue', '/GA/signUpMember'));
						}
					else
						{
						$this->page->addPageContent(new \PHPFUI\SubHeader('Select a category to register under'));
						$buttonGroup->addButton(new \PHPFUI\Button('General Public', '/GA/signUp'));
						$buttonGroup->addButton(new \PHPFUI\Button($clubAbbrev . ' Members', '/GA/signUpMember'));
						}
					$this->page->addPageContent($buttonGroup);
					}
				}
			}

		if (! $activeEvents)
			{
			$this->page->addPageContent(new \PHPFUI\SubHeader('There are no upcoming events'));
			}
		}

	public function signIn(\App\Record\GaEvent $event = new \App\Record\GaEvent()) : void
		{
		if ($this->page->addHeader($label = 'Registration Sheets'))
			{
			if (! $event->loaded())
				{
				$this->page->addPageContent(new \PHPFUI\SubHeader('Event Not Found'));
				}
			elseif (isset($_POST['submit']) && \App\Model\Session::checkCSRF())
				{
				$report = new \App\Report\GASignInSheets($event, (int)$_POST['type'], (int)$_POST['tagNumber']);
				$report->generate();
				$this->page->done();
				}
			else
				{
				$form = new \PHPFUI\Form($this->page);
				$form->setAreYouSure(false);
				$form->add(new \PHPFUI\SubHeader($event->title));
				$radio = new \PHPFUI\Input\RadioGroup('type', 'Download Type', (string)1);
				$radio->setToolTip('You can download the riders as one list, paged by the first letter of the last name, or in CSV format');
				$radio->addButton('Continous', (string)0);
				$radio->addButton('Paged On Last Name', (string)1);
				$radio->addButton('CSV', (string)2);
				$form->add(new \PHPFUI\MultiColumn($radio, new \PHPFUI\Input\Number('tagNumber', 'Starting Tag Number (zero for no numbers)', 1)));
				$form->add(new \PHPFUI\Submit('Download ' . $label));
				$this->page->addPageContent($form);
				}
			}
		}

	public function signs(\App\Record\GaEvent $event = new \App\Record\GaEvent()) : void
		{
		if ($this->page->addHeader($label = 'Preregistration Signs'))
			{
			if ($event->loaded())
				{
				if (isset($_POST['submit']) && \App\Model\Session::checkCSRF())
					{
					$report = new \App\Report\GARegistationSigns($event, (int)$_POST['count']);
					$report->generate();
					$this->page->done();
					}
				else
					{
					$form = new \PHPFUI\Form($this->page);
					$form->setAreYouSure(false);
					$form->add(new \PHPFUI\SubHeader($event->title));
					$fieldSet = new \PHPFUI\FieldSet('Enter the number of registrations signs needed');
					$number = new \PHPFUI\Input\Number('count', 'Number of signs', 5);
					$number->addAttribute('max', (string)99);
					$number->setRequired();
					$fieldSet->add($number);
					$form->add($fieldSet);
					$form->add(new \PHPFUI\Submit('Download ' . $label));
					$this->page->addPageContent($form);
					}
				}
			else
				{
				$this->page->addPageContent(new \App\View\GA\EventPicker($this->page, \App\View\GA\EventPicker::LINK, 'Click A GA Event for ' . $label, '/GA/signs'));
				}
			}
		}

	public function signUp(\App\Record\GaEvent $event = new \App\Record\GaEvent()) : void
		{
		$this->page->setPublic();
		$this->page->setShowMenus((bool)$event->allowShopping);
		$this->signUpCommon($event);
		}

	public function signUpMember(\App\Record\GaEvent $event = new \App\Record\GaEvent()) : void
		{
		if ($this->page->addHeader('Sign Up For ' . $event->title, 'GA Sign Up'))
			{
			$this->signUpCommon($event, false);
			}
		}

	public function statistics(\App\Record\GaEvent $event = new \App\Record\GaEvent()) : void
		{
		if ($this->page->addHeader('Statistics'))
			{
			if (! $event->loaded())
				{
				$this->page->addPageContent(new \PHPFUI\SubHeader('Event not found'));
				}
			else
				{
				$this->page->addPageContent(new \PHPFUI\SubHeader($event->eventDate . ' / ' . $event->title));
				$this->page->addPageContent(new \App\View\GA\Statistics($this->page, $event));
				}
			}
		}

	public function tabEditor(int $tab = 1) : void
		{
		if ($this->page->addHeader('Landing Page Tab Editor'))
			{
			$settingTable = new \App\Table\Setting();
			$tabs = \json_decode($settingTable->value('GATabs'), true);
			$container = new \PHPFUI\Container();

			if (isset($tabs[$tab]))
				{
				$container->add(new \PHPFUI\SubHeader('Edit Tab ' . $tabs[$tab]));
				$container->add(new \App\View\SettingEditor($this->page, 'GATab' . $tab, true));
				}
			else
				{
				$container->add(new \PHPFUI\SubHeader("Tab {$tab} not found"));
				}
			$this->page->addPageContent($container);
			}
		}

	public function unsubscribe(\App\Record\GaRider $rider = new \App\Record\GaRider(), string $email = '') : void
		{
		$this->page->setPublic();
		$unsubscribe = new \App\View\Unsubscribe($this->page, 'General Admission emails', $rider, $email);
		$this->page->addPageContent($unsubscribe);
		}

	public function updateRider(\App\Record\GaRider $rider = new \App\Record\GaRider()) : void
		{
		$this->page->setPublic();
		$this->page->setShowMenus((bool)$rider->gaEvent->allowShopping);

		if ($this->page->isAuthorized('Edit Rider') || $rider->customerId == \abs($_SESSION['customerNumber'] ?? 0))
			{
			$this->page->addPageContent(new \PHPFUI\Header('Update Rider'));
			$view = new \App\View\GA\Rider($this->page);
			$this->page->addPageContent($view->edit($rider, onSaveUrl: '/GA/signUp/' . $rider->gaEventId));
			}
		}

	/**
	 * @return array<string,string>
	 */
	private function processRider(\App\Record\GaRider $rider) : array
		{
		$row = $rider->toArray();

		foreach ($row as $key => $field)
			{
			$row[$key] = \App\Tools\TextHelper::unhtmlentities($field);
			}

		return $row;
		}

	private function signUpCommon(\App\Record\GaEvent $event = new \App\Record\GaEvent(), bool $showHeader = true) : void
		{
		if ($event->loaded())
			{
			if ($showHeader)
				{
				$this->page->addHeader('Sign Up For ' . $event->title, 'GA Sign Up');
				}
			$today = \App\Tools\Date::todayString();
			$model = new \App\Model\GeneralAdmission();
			$datePrice = $model->getLastRegistrationDateRecord($event);

			if ($event->maxRegistrants < $model->totalRegistrants($event))
				{
				$this->page->addPageContent(new \PHPFUI\SubHeader('This event is sold out!'));
				}
			elseif ($today > $event->lastRegistrationDate)
				{
				$this->page->addPageContent(new \PHPFUI\SubHeader('Preregistration has ended for this event.'));

				if ($event->eventDate >= $today)
					{
					$this->page->addPageContent(new \PHPFUI\SubHeader('But day of registration is open!'));
					$this->page->addPageContent('<p>Day of registration is $' . $datePrice->price . '. We accept all major credit cards.</p><p>');
					$this->page->addPageContent($event->description);
					}
				else
					{
					$this->page->addPageContent('Hope to see you next year.');
					}
				}
			else
				{
				$this->page->addPageContent(new \App\View\GA\Register($this->page, $event));
				}
			}
		else
			{
			$eventPicker = new \App\View\GA\EventPicker($this->page);
			$this->page->addPageContent($eventPicker->publicEvents());
			}
		}
	}
