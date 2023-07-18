<?php

namespace App\View\Volunteer;

class JobShifts
	{
	private readonly \App\Table\JobShift $jobShiftTable;

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->jobShiftTable = new \App\Table\JobShift();
		$this->processAJAXRequest();
		}

	public function addJobShiftModal(\PHPFUI\HTML5Element $modalLink, \App\Record\Job $job) : \PHPFUI\Reveal
		{
		$modal = new \PHPFUI\Reveal($this->page, $modalLink);
		$modal->addClass('large');
		$form = new \PHPFUI\Form($this->page);
		$form->setAreYouSure(false);
		$fieldSet = new \PHPFUI\FieldSet('Job Shift Information for ' . $job->title);
		$fieldSet->add(new \PHPFUI\Input\Hidden('jobId', (string)$job->jobId));
		$fieldSet->add(new \PHPFUI\Input\Hidden('jobShiftId'));
		$needed = new \PHPFUI\Input\Number('needed', 'Number of volunteers needed');
		$needed->addAttribute('max', (string)99);
		$needed->setRequired()->setToolTip('Number of volunteers needed for this shift');
		$fieldSet->add($needed);
		$startTime = new \PHPFUI\Input\Time($this->page, 'startTime', 'Start Time of Shift', '', 5);
		$startTime->setParentReveal($modal);
		$startTime->setRequired()->setToolTip('The time the volunteer needs to show up to do the job.');
		$fieldSet->add($startTime);
		$endTime = new \PHPFUI\Input\Time($this->page, 'endTime', 'End Time of Shift', '', 5);
		$endTime->setParentReveal($modal);
		$endTime->setRequired()->setToolTip('The time the volunteer can leave.');
		$fieldSet->add($endTime);
		$form->add($fieldSet);
		$submit = new \PHPFUI\Submit('Add', 'action');
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);

		return $modal;
		}

	public function output(\App\Record\Job $job) : string
		{
		$output = '';
		$submit = new \PHPFUI\Submit();
		$form = new \PHPFUI\Form($this->page, $submit);

		if ($form->isMyCallback())
			{
			$this->jobShiftTable->updateFromTable($_POST);
			$this->page->setResponse('Saved');
			}
		else
			{
			$add = new \PHPFUI\Button('Add New Shift');
			$add->addClass('success');
			$modal = $this->addJobShiftModal($add, $job);
			$jobEvent = $job->jobEvent;
			$form->add(new \PHPFUI\SubHeader($jobEvent->name));
			$form->add(new \App\View\Volunteer\Menu($jobEvent, 'Jobs'));
			$form->add(new \App\View\Volunteer\JobSubMenu($job, 'Shifts'));
			$form->add(new \PHPFUI\Header($job->title, 4));
			$shifts = $this->jobShiftTable->getJobShifts($job->jobId);
			$form->saveOnClick($add);
			$delete = new \PHPFUI\AJAX('deleteShift', 'Permanently delete this shift and all current volunteers?');
			$delete->addFunction('success', '$("#jobShiftId-"+data.response).css("background-color","red").hide("fast").remove();');
			$this->page->addJavaScript($delete->getPageJS());
			$form->add(new \PHPFUI\Input\Hidden('jobId', (string)$job->jobId));
			$table = new \PHPFUI\Table();
			$table->setRecordId('jobShiftId');
			$table->addHeader('startTime', 'Start Time');
			$table->addHeader('endTime', 'End Time');
			$table->addHeader('needed', 'Needed');
			$table->addHeader('delete', 'Del');

			if (! \count($shifts))
				{
				$modal->showOnPageLoad();
				}

			foreach ($shifts as $shiftRecord)
				{
				$shift = $shiftRecord->toArray();
				$id = $shiftRecord->jobShiftId;
				$hidden = new \PHPFUI\Input\Hidden("jobShiftId[{$id}]", (string)$shiftRecord->jobShiftId);
				$shift['startTime'] = new \PHPFUI\Input\Time($this->page, "startTime[{$id}]", '', $shiftRecord->startTime, 5);
				$shift['endTime'] = new \PHPFUI\Input\Time($this->page, "endTime[{$id}]", '', $shiftRecord->endTime, 5);
				$needed = new \PHPFUI\Input\Number("needed[{$id}]", '', $shiftRecord->needed);
				$needed->addAttribute('max', (string)99);
				$shift['needed'] = $needed;
				$icon = new \PHPFUI\FAIcon('far', 'trash-alt', '#');
				$icon->addAttribute('onclick', $delete->execute(['jobShiftId' => $id]));
				$shift['delete'] = $icon . $hidden;
				$table->addRow($shift);
				}
			$form->add($table);
			$buttonGroup = new \App\UI\CancelButtonGroup();

			if (\count($shifts))
				{
				$buttonGroup->addButton($submit);
				}
			$buttonGroup->addButton($add);
			$form->add($buttonGroup);
			$output = $form;
			}

		return $output;
		}

	public function showJobShiftsFor(\App\Record\Job $job, \App\Record\Member $member, bool $buttons = true) : string
		{
		$jobView = new \App\View\Volunteer\Jobs($this->page);
		$output = $jobView->showJob($job);
		$volunteerJobShiftTable = new \App\Table\VolunteerJobShift();
		$memberShifts = $volunteerJobShiftTable->getShiftsForMember($job, $member);
		$shifts = [];

		foreach ($memberShifts as $shift)
			{
			$shifts[$shift['jobShiftId']] = $shift;
			}
		$this->jobShiftTable->setWhere(new \PHPFUI\ORM\Condition('jobId', $job->jobId));
		$allShifts = $this->jobShiftTable->getArrayCursor();
		$fieldSet = new \PHPFUI\FieldSet('Your Shifts');
		$shiftLeader = false;

		foreach ($allShifts as $shift)
			{
			if (isset($shifts[$shift['jobShiftId']]))
				{
				$shift = $shifts[$shift['jobShiftId']];
				$shiftLeader += $shift['shiftLeader'];
				$row = new \PHPFUI\GridX();
				$row->add('<b>Start Time: </b>');
				$row->add(\App\Tools\TimeHelper::toSmallTime($shift['startTime']));
				$row->add(' - ');
				$row->add('<b>End Time: </b>');
				$row->add(\App\Tools\TimeHelper::toSmallTime($shift['endTime']));
				$fieldSet->add($row);
				}
			}

		if ($buttons)
			{
			$buttonGroup = new \App\UI\CancelButtonGroup();

			if ($shiftLeader)
				{
				$buttonGroup->addButton(new \PHPFUI\Button('Email Your Shift', "/Volunteer/emailShift/{$job->jobId}"));
				}
			$buttonGroup->addButton(new \PHPFUI\Button('Edit These Shifts', "/Volunteer/signup/{$job->jobId}"));
			}
		else
			{
			$buttonGroup = '';
			}

		return $output . $fieldSet . $buttonGroup;
		}

	protected function processAJAXRequest() : void
		{
		if (\App\Model\Session::checkCSRF() && isset($_POST['action']))
			{
			switch ($_POST['action'])
				{
				case 'deleteShift':

					$jobShift = new \App\Record\JobShift((int)$_POST['jobShiftId']);
					$jobShift->delete();
					$this->page->setResponse($_POST['jobShiftId']);

					break;


				case 'Add':

					$jobShift = new \App\Record\JobShift();
					$jobShift->setFrom($_POST);
					$jobShift->insert();
					$this->page->redirect();

					break;


				default:

					$this->page->redirect();

				}
			}
		}
	}
