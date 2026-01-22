<?php

namespace App\View\Volunteer;

class Event
	{
	private readonly \App\Table\JobEvent $jobEventTable;

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->jobEventTable = new \App\Table\JobEvent();
		$this->processRequest();
		}

	public function add() : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();
		$add = new \PHPFUI\Button('Add Event');
		$this->addEventModal($add)->showOnPageLoad();
		$container->add($add);

		return $container;
		}

	public function edit(\App\Record\JobEvent $jobEvent) : \App\UI\ErrorFormSaver
		{
		$jobEventId = $jobEvent->jobEventId;
		$submit = new \PHPFUI\Submit('Save');
		$form = $this->getForm($jobEvent, $submit);

		if ($form->save())
			{
			return $form;
			}

		$form->addAsFirst(new \App\View\Volunteer\Menu($jobEvent, 'Event'));
		$form->addAsFirst(new \PHPFUI\SubHeader($jobEvent->name));

		$jobTable = new \App\Table\Job();
		$jobTable->setWhere(new \PHPFUI\ORM\Condition('jobEventId', $jobEventId));

		if (! \count($jobTable))
			{
			$callout = new \PHPFUI\Callout('alert');
			$link = new \PHPFUI\Link("/Volunteer/jobs/{$jobEventId}", 'add jobs to this event', false);
			$callout->add("You need to {$link}.");
			$form->add($callout);
			}

		$buttonGroup = new \App\UI\CancelButtonGroup();
		$buttonGroup->addButton($submit);
		$form->add($buttonGroup);

		return $form;
		}

	public function list(\App\Table\JobEvent $jobEventTable) : \PHPFUI\Container
		{
		$recordId = 'jobEventId';
		$table = new \App\UI\ContinuousScrollTable($this->page, $jobEventTable);
		$table->setSortColumn('date');
		$table->setSortDirection('d');
		$table->setRecordId($recordId);
		$delete = new \PHPFUI\AJAX('deleteJobEventDate', 'Permanently delete this event and all related data?');
		$delete->addFunction('success', '$("#' . $recordId . '-"+data.response).css("background-color","red").hide("fast").remove()');
		$this->page->addJavaScript($delete->getPageJS());

		$headers = ['name', 'date'];
		$table->setSearchColumns($headers)->setSortableColumns($headers);
		$headers[] = 'copy';
		$headers[] = 'del';
		$table->setHeaders($headers);

		$that = $this;

		$table->addCustomColumn('copy', static function(array $event) use ($that) : \PHPFUI\FAIcon
			{
			$copyIcon = new \PHPFUI\FAIcon('far', 'clone', '#');
			$that->addCopyEventModal($copyIcon, $event);

			return $copyIcon;
			});
		$table->addCustomColumn('del', static fn (array $event) : \PHPFUI\FAIcon => new \PHPFUI\FAIcon('far', 'trash-alt', '#')->addAttribute('onclick', $delete->execute([$recordId => $event[$recordId]])));
		$table->addCustomColumn('name', static fn (array $event) : \PHPFUI\Link => new \PHPFUI\Link('/Volunteer/edit/' . $event[$recordId], $event['name'], false));

		$container = new \PHPFUI\Container();
		$container->add($table);
		$add = new \PHPFUI\Button('Add Event');
		$add->addClass('success');
		$this->addEventModal($add);
		$container->add($add);

		return $container;
		}

	protected function processRequest() : void
		{
		if (\App\Model\Session::checkCSRF() && isset($_POST['copy'], $_POST['action']) && 'Copy' == $_POST['action'])
			{
			$this->jobEventTable->copy(new \App\Record\JobEvent((int)$_POST['copy']), $_POST['name'], $_POST['date']);
			$this->page->redirect();
			}
		elseif (\App\Model\Session::checkCSRF() && isset($_POST['action']))
			{
			switch ($_POST['action'])
				{
				case 'deleteJobEventDate':

					$jobEvent = new \App\Record\JobEvent((int)$_POST['jobEventId']);
					$jobEvent->delete();
					$this->page->setResponse($_POST['jobEventId']);

					break;


				case 'Add':

					$jobEvent = new \App\Record\JobEvent();
					$jobEvent->setFrom($_POST);
					$id = $jobEvent->insert();
					$this->page->redirect("/Volunteer/edit/{$id}");

					break;


				default:

					$this->page->redirect();

				}
			}
		}

	/**
	 * @param array<string,mixed> $row
	 */
	private function addCopyEventModal(\PHPFUI\HTML5Element $modalLink, array $row) : void
		{
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$modal->addClass('large');
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$fieldSet = new \PHPFUI\FieldSet('Copy Event');
		$fieldSet->add(new \PHPFUI\Input\Hidden('copy', $row['jobEventId']));
		$name = new \PHPFUI\Input\Text('name', 'Event Name', $row['name']);
		$name->setRequired()->setToolTip('This is the name volunteers will see, so make it clear and descriptive');
		$fieldSet->add($name);

		$eventDate = new \PHPFUI\Input\Date($this->page, 'date', 'Event Date');
		$eventDate->setRequired()->setToolTip('Date of the event');
		$fieldSet->add($eventDate);
		$form->add($fieldSet);
		$submit = new \PHPFUI\Submit('Copy', 'action');
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);
		}

	private function addEventModal(\PHPFUI\HTML5Element $modalLink) : \PHPFUI\Reveal
		{
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$modal->addClass('large');

		$form = $this->getForm(new \App\Record\JobEvent());
		$form->setAreYouSure(false);

		$submit = new \PHPFUI\Submit('Add', 'action');
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);

		return $modal;
		}

	private function getForm(\App\Record\JobEvent $jobEvent, ?\PHPFUI\Submit $submit = null) : \App\UI\ErrorFormSaver
		{
		$form = new \App\UI\ErrorFormSaver($this->page, $jobEvent, $submit);
		$fieldSet = new \PHPFUI\FieldSet('Event Information');
		$fieldSet->add(new \PHPFUI\Input\Hidden('jobEventId', (string)$jobEvent->jobEventId));
		$name = new \PHPFUI\Input\Text('name', 'Event Name', $jobEvent->name);
		$name->setRequired()->setToolTip('This is the name volunteers will see, so make it clear and descriptive');
		$fieldSet->add($name);

		$member = new \App\Record\Member($jobEvent->organizer);
		$memberPicker = new \App\UI\MemberPicker($this->page, new \App\Model\MemberPickerNoSave('Volunteer Organizer'), 'organizer', $member->toArray());
		$organizer = $memberPicker->getEditControl();
		$organizer->setTooltip('The person who is in charge of all volunteer coordination');
		$fieldSet->add($organizer);

		$eventDate = new \PHPFUI\Input\Date($this->page, 'date', 'Event Date', $jobEvent->date);
		$eventDate->setRequired()->setToolTip('Date of the event');
		$cutoffDate = new \PHPFUI\Input\Date($this->page, 'cutoffDate', 'Volunteer Cut Off Date', $jobEvent->cutoffDate);
		$cutoffDate->setToolTip('Last date volunteers can sign up to volunteer for the event');
		$fieldSet->add(new \PHPFUI\MultiColumn($eventDate, $cutoffDate));
		$form->add($fieldSet);

		return $form;
		}
	}
