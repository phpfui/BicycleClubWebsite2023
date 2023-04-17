<?php

namespace App\View\Volunteer;

class AssignedShifts implements \Stringable
	{
	private readonly \App\Table\JobShift $jobShiftTable;

	private readonly \App\Table\VolunteerJobShift $volunteerJobShiftTable;

	public function __construct(private readonly \App\View\Page $page, private readonly \App\Record\Job $job)
		{
		$this->volunteerJobShiftTable = new \App\Table\VolunteerJobShift();
		$this->jobShiftTable = new \App\Table\JobShift();
		$this->processAJAXRequest();
		}

	public function __toString() : string
		{
		$output = '';
		$submit = new \PHPFUI\Submit();
		$form = new \PHPFUI\Form($this->page, $submit);

		if ($form->isMyCallback())
			{
			$this->volunteerJobShiftTable->updateFromTable($_POST);
			$this->page->setResponse('Saved');
			}
		else
			{
			$form->add(new \PHPFUI\Input\Hidden('jobId', (string)$this->job->jobId));

			if ($this->job->empty())
				{
				$this->page->redirect('/Volunteer/events');
				}
			$jobEvent = $this->job->jobEvent;
			$form->add(new \PHPFUI\SubHeader($jobEvent->name));
			$menu = new \App\View\Volunteer\Menu($jobEvent, 'Jobs');
			$form->add($menu);
			$subMenu = new \App\View\Volunteer\JobSubMenu($this->job, 'Volunteers');
			$form->add($subMenu);
			$form->add(new \PHPFUI\Header($this->job->title, 4));

			$shifts = $this->jobShiftTable->getJobShifts($this->job->jobId);

			$add = new \PHPFUI\Button('Add Volunteer');
			$add->addClass('success');
			$this->addVolunteerModal($add, $this->job, $shifts);

			$volunteers = $this->volunteerJobShiftTable->getVolunteers($this->job->jobId);
			$form->saveOnClick($add);
			$delete = new \PHPFUI\AJAX('deleteVolunteer', 'Delete this volunteer?');
			$delete->addFunction('success', '$("#volunteerJobShiftId-"+data.response).css("background-color","red").hide("slow").remove();');
			$this->page->addJavaScript($delete->getPageJS());

			$table = new \PHPFUI\Table();
			$table->setRecordId('volunteerJobShiftId');
			$table->addHeader('name', 'Volunteer');
			$table->addHeader('jobShiftId', 'Shift');
			$table->addHeader('shiftLeader', 'Shift Leader');
			$table->addHeader('delete', 'Delete');
			$editVolunteer = $this->page->isAuthorized('Edit Volunteers');

			foreach ($volunteers as $volunteerObject)
				{
				$volunteer = $volunteerObject->toArray();
				$id = $volunteer['volunteerJobShiftId'];
				$pk = new \PHPFUI\Input\Hidden("volunteerJobShiftId[{$id}]", $id);
				$memberId = new \PHPFUI\Input\Hidden("memberId[{$id}]", $volunteerObject->memberId);
				$fullName = $volunteer['firstName'] . ' ' . $volunteer['lastName'];

				if ($editVolunteer)
					{
					$fullName = "<a href='/Volunteer/signup/{$this->job->jobId}/{$volunteer['memberId']}'>{$fullName}</a>";
					}
				$volunteer['name'] = $fullName . $pk . $memberId;
				$select = new \PHPFUI\Input\Select("jobShiftId[{$id}]");

				foreach ($shifts as $shift)
					{
					$select->addOption(self::displayShiftTimes($shift), $shift['jobShiftId'], $shift['jobShiftId'] == $volunteer['jobShiftId']);
					}
				$volunteer['jobShiftId'] = $select;
				$volunteer['shiftLeader'] = new \PHPFUI\Input\CheckBoxBoolean("shiftLeader[{$id}]", 'Leader', $volunteer['shiftLeader']);
				$icon = new \PHPFUI\FAIcon('far', 'trash-alt', '#');
				$icon->addAttribute('onclick', $delete->execute(['volunteerJobShiftId' => $id]));
				$volunteer['delete'] = $icon;
				$table->addRow($volunteer);

				if (! empty($volunteer['notes']))
					{
					$table->addRow(['name' => $volunteer['notes']], [4]);
					}
				}
			$form->add($table);

			$buttonGroup = new \App\UI\CancelButtonGroup();

			if (\count($volunteers))
				{
				$buttonGroup->addButton($submit);
				}
			$buttonGroup->addButton($add);
			$form->add($buttonGroup);
			$output = $form;
			}

		return (string)$output;
		}

	public static function displayShiftTimes(\PHPFUI\ORM\DataObject $shift) : string
		{
		return \App\Tools\TimeHelper::toSmallTime($shift['startTime']) . ' - ' . \App\Tools\TimeHelper::toSmallTime($shift['endTime']);
		}

	public function showVolunteers() : string
		{
		$fieldSet = new \PHPFUI\FieldSet('Currently Signed Up');
		$shifts = $this->jobShiftTable->getJobShifts($this->job->jobId);
		$formattedShifts = [];

		foreach ($shifts as $shift)
			{
			$formattedShifts[$shift->jobShiftId] = self::displayShiftTimes($shift);
			}
		$volunteers = $this->volunteerJobShiftTable->getVolunteersByShift($this->job);
		$table = new \PHPFUI\Table();
		$table->addHeader('name', 'Volunteer');
		$table->addHeader('shift', 'Shift');
		$table->addHeader('shiftLeader', 'Shift Leader');
		$jobShiftId = 0;
		$people = [];

		$volunteer = null;

		foreach ($volunteers as $volunteerObject)
			{
			$volunteer = $volunteerObject->toArray();

			if ($volunteer['shiftLeader'])
				{
				$volunteer['name'] = $volunteer['firstName'] . ' ' . $volunteer['lastName'];
				$volunteer['shift'] = $formattedShifts[$volunteer['jobShiftId']];
				$volunteer['shiftLeader'] = new \PHPFUI\FAIcon('fas', 'star');
				$table->addRow($volunteer);
				}
			else
				{
				if ($jobShiftId != $volunteer['jobShiftId'])
					{
					if ($people)
						{
						$volunteer['name'] = new \PHPFUI\ToolTip('Volunteers', \implode(' / ', $people));
						$people = [];
						$volunteer['shift'] = $formattedShifts[$jobShiftId];
						$volunteer['shiftLeader'] = '';
						$table->addRow($volunteer);
						}
					}
				$people[] = $volunteer['firstName'] . ' ' . $volunteer['lastName'];
				$jobShiftId = $volunteer['jobShiftId'];
				}
			}

		if ($people && isset($formattedShifts[$jobShiftId]))
			{
			$volunteer['name'] = new \PHPFUI\ToolTip('Volunteers', \implode(' / ', $people));
			$volunteer['shift'] = $formattedShifts[$jobShiftId];
			$volunteer['shiftLeader'] = '';
			$table->addRow($volunteer);
			}
		$fieldSet->add($table);

		return $fieldSet;
		}

	protected function processAJAXRequest() : void
		{
		if (\App\Model\Session::checkCSRF() && isset($_POST['action']))
			{
			switch ($_POST['action'])
				{
				case 'deleteVolunteer':

					$volunteerJobShift = new \App\Record\VolunteerJobShift((int)$_POST['volunteerJobShiftId']);
					$volunteerJobShift->delete();
					$this->page->setResponse($_POST['volunteerJobShiftId']);

					break;


				case 'Add Volunteer':

					$volunteerJobShift = new \App\Record\VolunteerJobShift();
					$volunteerJobShift->setFrom($_POST);
					$volunteerJobShift->insert();
					$this->page->redirect();

					break;


				default:

					$this->page->redirect();

				}
			}
		}

	private function addVolunteerModal(\PHPFUI\HTML5Element $modalLink, \App\Record\Job $job, \PHPFUI\ORM\RecordCursor $cursor) : void
		{
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$modal->addClass('large');
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$fieldSet = new \PHPFUI\FieldSet('Add a volunteer for ' . $job->title);
		$fieldSet->add(new \PHPFUI\Input\Hidden('jobId', (string)$job->jobId));
		$fieldSet->add(new \PHPFUI\Input\Hidden('jobShiftId', (string)($cursor->current()->jobShiftId)));
		$fieldSet->add(new \PHPFUI\Input\Hidden('shiftLeader', '0'));
		$memberPicker = new \App\UI\MemberPicker($this->page, new \App\Model\MemberPickerNoSave('Volunteer Name'), 'memberId');
		$fieldSet->add($memberPicker->getEditControl());
		$form->add($fieldSet);
		$form->add($modal->getButtonAndCancel(new \PHPFUI\Submit('Add Volunteer', 'action')));
		$modal->add($form);
		}
	}
