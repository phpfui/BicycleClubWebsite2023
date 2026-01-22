<?php

namespace App\WWW;

class Calendar extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	protected \PHPFUI\Button $backButton;

	private readonly string $calendarName;

	private readonly \App\View\Calendar $view;

	public function __construct(\PHPFUI\Interfaces\NanoController $controller)
		{
		parent::__construct($controller);
		$this->view = new \App\View\Calendar($this->page);
		$this->backButton = new \PHPFUI\Button('Calendar Configuration', '/Calendar/configure');
		$this->backButton->addClass('secondary');
		$settingTable = new \App\Table\Setting();
		$this->calendarName = $settingTable->value('calendarName');
		}

	public function acceptEmail() : void
		{
		if ($this->calendarName && $this->page->addHeader('Accept Calendar Email'))
			{
			$this->editEmail('acceptCalendar');
			}
		}

	public function addEvent() : void
		{
		if ($this->calendarName)
			{
			$this->page->setPublic();
			$this->page->addHeader('Add Calendar Event');
			$this->page->addPageContent($this->view->edit(new \App\Record\Calendar()));
			}
		}

	public function approve(\App\Record\Calendar $calendar = new \App\Record\Calendar()) : void
		{
		if ($this->calendarName && $this->page->addHeader('Approve Calendar Entries'))
			{
			if ($calendar->loaded())
				{
				if ($calendar->pending)
					{
					\App\Model\Session::setFlash('success', "Calendar Event {$calendar->title} has been approved");
					$model = new \App\Model\Calendar();
					$model->approve($calendar);
					}
				else
					{
					$this->page->addPageContent('<h3>Calendar Event has already been approved</h3>');
					}
				}
			else
				{
				$this->page->addPageContent('<h3>Calendar Event not found</h3>');
				}
			$this->page->redirect('/Calendar/pending', '', 2);
			}
		}

	public function configure() : void
		{
		if ($this->calendarName && $this->page->addHeader($header = 'Calendar Configuration'))
			{
			$landing = $this->page->mainMenu->getLandingPage($this->page, '/Calendar/configure', $header);
			$this->page->addPageContent($landing);
			}
		}

	public function coordinator() : void
		{
		if ($this->calendarName && $this->page->addHeader($name = 'Calendar Coordinator'))
			{
			$view = new \App\View\Coordinators($this->page);
			$this->page->addPageContent($view->getEmail($name));
			}
		}

	public function delete(\App\Record\Calendar $calendar = new \App\Record\Calendar()) : void
		{
		if ($this->calendarName && $this->page->addHeader('Approve Calendar Entries'))
			{
			if ($calendar->loaded())
				{
				\App\Model\Session::setFlash('success', "Calendar Event {$calendar->title} has been deleted");
				$calendar->delete();
				}
			else
				{
				$this->page->addPageContent('<h3>Calendar Event not found</h3>');
				}
			$this->page->redirect('/Calendar/pending', '', 2);
			}
		}

	public function deny(\App\Record\Calendar $calendar = new \App\Record\Calendar()) : void
		{
		if ($this->calendarName && $this->page->addHeader('Reject Calender Event'))
			{
			if ($calendar->loaded())
				{
				$this->page->addPageContent($this->view->reject($calendar));
				}
			else
				{
				\App\Model\Session::setFlash('success', "Calendar Event {$calendar->title} has been rejected");
				$this->page->redirect('/Calendar/pending', '', 2);
				}
			}
		}

	public function edit(\App\Record\Calendar $calendar = new \App\Record\Calendar(), string $sha1 = '') : void
		{

		if (! $this->calendarName || $calendar->empty())
			{
			$this->page->addPageContent('<h3>Calendar Event not found</h3>');

			return;
			}

		if ($sha1 && $calendar->privateEmail && $calendar->privateContact && \sha1($calendar->privateEmail . $calendar->privateContact) == $sha1)
			{
			$this->page->setPublic();
			}

		if ($this->page->addHeader('Edit Calendar Event'))
			{
			$this->page->addPageContent($this->view->edit($calendar, $this->page->isPublic()));
			}
		}

	public function events() : void
		{
		if ($title = $this->calendarName)
			{
			$this->page->setPublic();
			$this->page->addHeader($title);
			$this->page->addPageContent(new \PHPFUI\Button('Add An Event', '/Calendar/addEvent'));
			$this->page->addPageContent('&nbsp;');
			$content = new \App\View\Content($this->page);
			$this->page->addPageContent($content->getDisplayCategoryHTML($title));
			$model = new \App\Model\Calendar();
			$this->page->addPageContent($this->view->showCalendar($model->getCalendarEntries($_GET), $_GET));
			}
		}

	public function notes() : void
		{
		if ($this->calendarName && $this->page->addHeader($title = 'Calendar Notes'))
			{
			$content = new \App\View\Content($this->page);
			$this->page->addPageContent($content->getDisplayCategoryHTML($title));
			}
		}

	public function pending() : void
		{
		if ($this->calendarName && $this->page->addHeader('Pending Calendar Events'))
			{
			$calendarTable = new \App\Table\Calendar();
			$calendarTable->setWhere(new \PHPFUI\ORM\Condition('pending', 1));
			$this->page->addPageContent($this->view->showCalendar($calendarTable));
			}
		}

	public function rejected() : void
		{
		if ($this->calendarName && $this->page->addHeader('Rejected Calendar Events'))
			{
			$calendarTable = new \App\Table\Calendar();
			$calendarTable->setWhere(new \PHPFUI\ORM\Condition('pending', 2));
			$this->page->addPageContent($this->view->showCalendar($calendarTable));
			}
		}

	public function rejectEmail() : void
		{
		if ($this->calendarName && $this->page->addHeader('Reject Calendar Email'))
			{
			$this->editEmail('rejectCalendar');
			}
		}

	public function thankYou(\App\Record\Calendar $calendar = new \App\Record\Calendar()) : void
		{
		if ($this->calendarName && $calendar->loaded() && 1 == $calendar->pending)
			{
			$this->page->setPublic();
			$this->page->addHeader('Thank You for submitting an event');
			$this->page->addSubHeader('We will send you an email when it is approved');
			$model = new \App\Model\Calendar();
			$model->thankYouNote($calendar);
			}
		}

	public function thankYouEmail() : void
		{
		if ($this->calendarName && $this->page->addHeader('Thank You Calendar Email'))
			{
			$this->editEmail('thankYouCalendar');
			}
		}

	private function editEmail(string $setting) : void
		{
		$editor = new \App\View\Email\Settings($this->page, $setting, new \App\Model\Email\Calendar());
		$editor->addButton($this->backButton);
		$this->page->addPageContent($editor);
		}
	}
