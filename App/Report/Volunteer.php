<?php

namespace App\Report;

class Volunteer extends \PDF_MC_Table
	{
	private readonly \App\Table\Member $memberTable;

	private string $reportName = '';

	/**
	 * @param array<string,string> $parameters
	 */
	public function __construct(private array $parameters)
		{
		$this->memberTable = new \App\Table\Member();
		$this->parameters = $parameters;
		parent::__construct();
		$this->SetDisplayMode('fullpage');
		$this->SetFont('Arial', '', 10);
		$this->setNoLines(true);
		$this->headerFontSize = 18;
		$this->SetAutoPageBreak(true, 2);
		}

	public function __destruct()
		{
		if (! $this->reportName)
			{
			$now = \App\Tools\Date::todayString();
			$this->reportName = "JobReport-{$now}.pdf";
			}

		$this->Output($this->reportName, 'I');
		}

	public function generate(\App\Record\JobEvent $jobEvent) : void
		{
		$this->pollReports();
		$this->volunteerReports($jobEvent);
		}

	public function generateVolunteerHistory() : void
		{
		$now = \App\Tools\Date::todayString();
		$this->reportName = "VolunteerHistoryReport-{$now}.pdf";

		$this->AddPage('P', 'Letter');
		$printed = \App\Tools\Date::todayString();

		$from = $this->parameters['start'];
		$to = $this->parameters['end'];

		$this->SetDocumentTitle("Volunteer History {$from} - {$to} Printed On {$printed}");
		$this->PrintHeader();

		$this->SetWidths([40, 25, 80, 60]);
		$this->SetHeader(['Volunteer', 'Date', 'Job', 'Event']);
		$this->PrintColumnHeaders();
		$volunteerJobShiftTable = new \App\Table\VolunteerJobShift();
		$volunteers = $volunteerJobShiftTable->getVolunteersForDates($this->parameters['start'], $this->parameters['end']);

		$lastName = '';

		foreach ($volunteers as $volunteer)
			{
			if ($volunteer['name'] == $lastName)
				{
				$volunteer['name'] = '';
				}
			else
				{
				// add a blank line if previous name
				if ($lastName)
					{
					$this->Row(['', '', '', '', ]);
					}
				$lastName = $volunteer['name'];
				}
			$this->Row([$volunteer['name'], $volunteer['date'], $volunteer['job'], $volunteer['event'], ]);
			}
		}

	public function pollReports() : void
		{
		$volunteerPollResponseTable = new \App\Table\VolunteerPollResponse();
		$volunteerPollAnswerTable = new \App\Table\VolunteerPollAnswer();
		$detailArray = [];

		foreach ($this->parameters as $key => $value)
			{
			$field = 'pollId-';
			$fieldLen = \strlen($field);

			if ($value && \strlen($key) > $fieldLen && 0 == \substr_compare($key, $field, 0, $fieldLen))
				{
				$pollId = \substr($key, $fieldLen);
				$detail = (int)($this->parameters["detail-{$pollId}"]);
				$counts = [];

				if ($detail)
					{
					$detailArray = [];
					}
				$poll = new \App\Record\VolunteerPoll($pollId);

				$volunteerPollResponseTable->setWhere(new \PHPFUI\ORM\Condition('volunteerPollId', $pollId));
				$responses = $volunteerPollResponseTable->getArrayCursor();

				foreach ($responses as $response)
					{
					if ($detail)
						{
						$member = new \App\Record\Member($response['memberId']);
						$detailArray["{$member->lastName},{$member->firstName}~{$member->memberId}"] = $response['answer'];
						}

					if (isset($counts[$response['answer']]))
						{
						++$counts[$response['answer']];
						}
					else
						{
						$counts[$response['answer']] = 1;
						}
					}
				$this->AddPage('P', 'Letter');
				$this->SetDocumentTitle('Volunteer Poll Results Printed On ' . \App\Tools\Date::todayString());
				$this->SetWidths([200]);
				$this->SetHeader(['Poll Question']);
				$this->SetAligns(['L']);
				$this->PrintHeader();
				$this->Row([$poll->question]);
				$this->Row(['']);
				$this->Row(['Results Summary']);
				$this->SetWidths([10, 20, 170, ]);
				$this->SetAligns(['L', 'C', 'L', ]);
				$this->setHeader(['', 'Count', 'Response', ]);
				$this->Row(['', 'Count', 'Response', ]);

				$volunteerPollAnswerTable->setWhere(new \PHPFUI\ORM\Condition('volunteerPollId', $pollId));
				$answers = $volunteerPollAnswerTable->getRecordCursor();

				foreach ($answers as $answer)
					{
					$value = 'None';

					if (isset($counts[$answer->volunteerPollAnswerId]))
						{
						$value = $counts[$answer->volunteerPollAnswerId];
						}
					$this->Row(['', $value, $answer->answer, ]);
					}

				if ($detail)
					{
					\ksort($detailArray);

					$headers = ['Last Name', 'First Name', 'Phone', 'Cell', 'Town', ];
					$this->setHeader($headers);

					foreach ($answers as $answer)
						{
						$this->SetWidths([200]);
						$this->Row(['']);
						$this->Row(['Volunteers responding ' . $answer->answer]);
						$this->SetWidths([40, 40, 30, 30, 40, 40, ]);
						$this->SetAligns(['L', 'L', 'L', 'L', 'L', ]);
						$this->Row($headers);

						foreach ($detailArray as $key => $value)
							{
							if ($value == $answer->volunteerPollAnswerId)
								{
								/** @noinspection PhpUnusedLocalVariableInspection */
								[$junk, $number] = \explode('~', $key);
								$member = $this->memberTable->getMembership((int)$number);
								$this->Row([$member['lastName'] ?? 'Unknown', $member['firstName'] ?? 'Unknown', $member['phone'] ?? 'Unknown', $member['cellPhone'] ?? 'Unknown', $member['town'] ?? 'Unknown', ]);
								}
							}
						}
					}
				}
			}
		$now = \App\Tools\Date::todayString();
		$this->reportName = "JobReport-{$now}.pdf";
		}

	public function volunteerReports(\App\Record\JobEvent $jobEvent) : void
		{
		$jobTable = new \App\Table\Job();
		$jobShiftTable = new \App\Table\JobShift();
		$volunteerJobShiftTable = new \App\Table\VolunteerJobShift();
		$details = (int)($this->parameters['details']);
		$needed = (int)($this->parameters['needed']);
		$current = (int)($this->parameters['current']);
		$multiple = (int)($this->parameters['multiple']);
		$this->SetHeader([]);

		$jobs = $jobTable->getJobs($jobEvent);

		if (\count($jobs) && ($details || $needed || $current || $multiple))
			{
			$newPage = true;
			$this->AddPage('P', 'Letter');
			$this->SetDocumentTitle('Volunteer Job Assignments Printed On ' . \App\Tools\Date::todayString());
			$this->PrintHeader();

			foreach ($jobs as $job)
				{
				if (! $multiple && ! $newPage)
					{
					$this->AddPage('P', 'Letter');
					}
				$newPage = false;
				$this->SetWidths([40,
					50,
					100, ]);
				$this->SetHeader(['Job Date',
					'Job Title',
					'Location', ]);
				$this->SetAligns(['L',
					'L',
					'L', ]);
				$this->PrintColumnHeaders();
				$date = \App\Tools\Date::formatString('l, n/j/Y', $job['date']);
				$this->Row([$date,
					$job['title'],
					$job['location'], ]);
				$this->Row(['']);

				if ($details)
					{
					$this->SetWidths([190]);
					$this->SetHeader(['Description']);
					$this->SetAligns(['L']);
					$this->PrintColumnHeaders();
					$this->Row([$job['description']]);
					$this->Row(['']);
					}
				$hours = [null => 'No Shift'];
				$this->SetWidths([20,
					20,
					20,
					20,
					25, ]);
				$this->SetHeader(['Start Time',
					'End Time',
					'Needed',
					'Taken',
					'Remaining', ]);
				$this->SetAligns(['C',
					'C',
					'C',
					'C',
					'C', ]);

				if ($needed)
					{
					$this->PrintColumnHeaders();
					}
				$jobShiftTable->setWhere(new \PHPFUI\ORM\Condition('jobId', $job['jobId']));

				$shifts = $jobShiftTable->getRecordCursor();

				foreach ($shifts as $shift)
					{
					$hours[$shift->jobShiftId] = \str_replace(' ', '', \App\Tools\TimeHelper::toSmallTime($shift->startTime) . '-' . \App\Tools\TimeHelper::toSmallTime($shift->endTime));
					$volunteerJobShiftTable->setWhere(new \PHPFUI\ORM\Condition('jobShiftId', $shift->jobShiftId));
					$taken = \count($volunteerJobShiftTable);

					if ($needed)
						{
						$this->Row([\App\Tools\TimeHelper::toSmallTime($shift->startTime),
							\App\Tools\TimeHelper::toSmallTime($shift->endTime),
							$shift->needed,
							$taken,
							$shift->needed - $taken, ]);
						}
					}

				if ($needed)
					{
					$this->Row(['']);
					}

				if ($current)
					{
					$volunteers = $volunteerJobShiftTable->getVolunteers($job['jobId']);

					if (\count($volunteers))
						{
						$this->SetWidths([35,
							40,
							53,
							25,
							25,
							30, ]);
						$this->SetHeader(['Shift',
							'Name',
							'Email',
							'Home Phone',
							'Cell Phone',
							'', ]);
						$this->SetAligns(['L',
							'L',
							'L',
							'L',
							'L',
							'L', ]);
						$this->PrintColumnHeaders();

						foreach ($volunteers as $shift)
							{
							$member = new \App\Record\Member($shift->memberId);
							$leader = $shift->shiftLeader > 0 ? 'Shift Leader' : '';
							$this->Row([$hours[$shift->jobShiftId],
								\App\Tools\TextHelper::unhtmlentities($member->firstName . ' ' . $member->lastName),
								$member->email,
								$member->phone,
								$member->cellPhone,
								$leader, ]);
							}
						}
					$this->Row(['']);
					}

				if ($multiple)
					{
					$this->SetWidths([195]);
					$this->SetHeader(['']);
					$this->SetAligns(['C']);
					$this->Row(['=================================================================================']);
					$this->Row(['']);
					}
				}
			}
		}
	}
