<?php

namespace App\View\Volunteer;

class Signup implements \Stringable
	{
	private readonly \App\Table\VolunteerJobShift $volunteerJobShiftTable;

	public function __construct(private readonly \App\View\Page $page, protected \App\Record\Job $job, protected \App\Record\Member $member)
		{
		if ($member->empty() || ! $page->isAuthorized('Edit Volunteers'))
			{
			$this->member = new \App\Record\Member(\App\Model\Session::signedInMemberId());
			}
		$this->volunteerJobShiftTable = new \App\Table\VolunteerJobShift();
		}

	public function __toString() : string
		{
		$output = '';

		$pickAJobUrl = '/Volunteer/pickAJob';

		if ($this->job->empty())
			{
			$this->page->redirect($pickAJobUrl);

			return $output;
			}

		if ($this->page->isAuthorized('Edit Volunteers'))
			{
			$output .= "<h4>For Volunteer {$this->member->firstName} {$this->member->lastName}</h4>";
			}
		$jobView = new \App\View\Volunteer\Jobs($this->page);
		$output .= $jobView->showJob($this->job);
		$shifts = new \App\View\Volunteer\AssignedShifts($this->page, $this->job);
		$output .= $shifts->showVolunteers();
		$submit = new \PHPFUI\Submit('Volunteer');
		$form = new \PHPFUI\Form($this->page, $submit);

		if ($form->isMyCallback())
			{
			\PHPFUI\ORM::beginTransaction();
			$data = ['jobId' => $this->job->jobId,
				'memberId' => $this->member->memberId, ];
			$shift = new \App\Record\VolunteerJobShift($data);
			$shiftTable = new \App\Table\VolunteerJobShift();
			$condition = new \PHPFUI\ORM\Condition('jobId', $this->job->jobId);
			$condition->and('memberId', $this->member->memberId);
			$shiftTable->setWhere($condition);
			$shiftTable->delete();
			$updatePoll = false;

			if (isset($_POST['jobShiftId']))
				{
				foreach ($_POST['jobShiftId'] as $jobShiftId => $checked)
					{
					$data['shiftLeader'] = $shift->shiftLeader;
					$data['jobShiftId'] = $jobShiftId;
					$data['notes'] = $_POST['notes'][$jobShiftId];

					if ($checked)
						{
						$volunteerJobShift = new \App\Record\VolunteerJobShift();
						$volunteerJobShift->setFrom($data);
						$volunteerJobShift->insert();
						}
					$updatePoll = true;
					}
				}

			foreach ($_POST as $field => $value)
				{
				if (\str_contains($field, 'poll-'))
					{
					/** @noinspection PhpUnusedLocalVariableInspection */
					[$junk, $volunteerPoll] = \explode('-', $field);

					$data = ['volunteerPollId' => $volunteerPoll,
						'memberId' => $this->member->memberId, ];

					$volunteerPollResponseTable = new \App\Table\VolunteerPollResponse();
					$condition = new \PHPFUI\ORM\Condition('volunteerPollId', $volunteerPoll);
					$condition->and(new \PHPFUI\ORM\Condition('memberId', $this->member->memberId));

					$volunteerPollResponseTable->setWhere($condition);
					$volunteerPollResponseTable->delete();

					if ($updatePoll)
						{
						$data['answer'] = $value;
						$volunteerPollResponse = new \App\Record\VolunteerPollResponse();
						$volunteerPollResponse->setFrom($data);
						$volunteerPollResponse->insert();
						}
					}
				}
			\PHPFUI\ORM::commit();
			$this->page->setResponse('Saved');
			}
		else
			{
			$fieldSet = new \PHPFUI\FieldSet('Select The Shifts You Want');
			$jobShiftTable = new \App\Table\JobShift();
			$shifts = $jobShiftTable->getAvailableJobShifts($this->job->jobId);
			$availableShifts = [];

			foreach ($shifts as $shift)
				{
				$availableShifts[$shift['jobShiftId']] = $shift;
				}
			$volunteerShifts = $this->volunteerJobShiftTable->getShiftsForMember($this->job, $this->member);
			$answers = [];

			foreach ($volunteerShifts as $shift)
				{
				$answers[$shift['jobShiftId']] = 1;
				$availableShifts[$shift['jobShiftId']] = $shift;
				}

			$hr = '';

			foreach ($availableShifts as $shift)
				{
				$fieldSet->add($hr);
				$hr = '<hr>';
				$id = $shift['jobShiftId'];
				$title = \App\View\Volunteer\AssignedShifts::displayShiftTimes($shift);
				$cb = new \PHPFUI\Input\CheckBoxBoolean("jobShiftId[{$id}]", $title, isset($answers[$id]));
				$fieldSet->add($cb);
				$notes = new \PHPFUI\Input\Text("notes[{$id}]", 'Specific notes to the organizer for ' . $title, $shift['notes'] ?? '');
				$notes->setAttribute('maxlen', (string)255);
				$fieldSet->add($notes);
				}
			$form->add($fieldSet);
			$volunteerPollTable = new \App\Table\VolunteerPoll();
			$polls = $volunteerPollTable->getPolls($this->job->jobEvent);

			if (\count($polls))
				{
				$fieldSet = new \PHPFUI\FieldSet('Please answer the following questions');
				$first = true;

				foreach ($polls as $poll)
					{
					if (! $first)
						{
						$row = new \PHPFUI\GridX();
						$row->add('<hr>');
						$fieldSet->add($row);
						}
					$first = false;
					$row = new \PHPFUI\GridX();
					$row->add($poll['question']);
					$fieldSet->add($row);
					$row = new \PHPFUI\GridX();
					$row->addClass('large-2 medium-4 columns');
					$volunteerPollAnswerTable = new \App\Table\VolunteerPollAnswer();
					$response = new \App\Record\VolunteerPollResponse(['memberId' => $this->member->memberId,
						'volunteerPollId' => $poll['volunteerPollId'], ]);
					$answers = $volunteerPollAnswerTable->getPollAnswers($poll['volunteerPollId']);
					$select = new \PHPFUI\Input\Select('poll-' . $poll['volunteerPollId']);
					$select->addOption('None', (string)0, 0 == $response->answer);

					foreach ($answers as $answer)
						{
						$select->addOption($answer['answer'], $answer['volunteerPollAnswerId'], $response->answer == $answer['volunteerPollAnswerId']);
						}
					$row->add($select);
					$fieldSet->add($row);
					}
				$form->add($fieldSet);
				}

			$buttonGroup = new \PHPFUI\ButtonGroup();
			$buttonGroup->addButton($submit);
			$cancel = new \PHPFUI\Button('Back To Jobs', $pickAJobUrl . '/' . $this->job->jobEventId);
			$cancel->addClass('hollow');
			$buttonGroup->addButton($cancel);

			$form->add($buttonGroup);

			$output .= $form;
			}

		return $output;
		}
	}
