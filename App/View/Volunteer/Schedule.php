<?php

namespace App\View\Volunteer;

class Schedule
	{
	public function __construct(private readonly \App\View\Page $page, protected \App\Record\JobEvent $jobEvent)
		{
		}

	public function schedule() : \PHPFUI\Container
		{
		$output = new \PHPFUI\Container();

		if (\App\Model\Session::checkCSRF() && isset($_POST['action']) && 'TVE' == $_POST['action'])
			{
			$model = new \App\Model\Volunteer();
			$model->toggleEvent(new \App\Record\VolunteerJobShift((int)$_POST['volunteerJobShiftId']));
			$this->page->setRawResponse('true');

			return $output;
			}

		if (! $this->jobEvent->empty())
			{
			$output->add(new \PHPFUI\SubHeader($this->jobEvent->name));
			$output->add(new \App\View\Volunteer\Menu($this->jobEvent, 'Schedule'));
			$volunteerJobShiftTable = new \App\Table\VolunteerJobShift();
			$volunteerInfo = $volunteerJobShiftTable->getVolunteerSchedule($this->jobEvent);
			$headers = ['time' => 'Time', 'title' => 'Job', 'name' => 'Volunteer', 'shiftLeader' => 'Leader', 'worked' => 'Worked', ];
			$table = new \PHPFUI\Table();
			$table->setHeaders($headers);
			$ajax = new \PHPFUI\AJAX('TVE');
			$lastDate = '';
			$accordion = 0;

			foreach ($volunteerInfo as $volunteer)
				{
				if ($lastDate && $lastDate != $volunteer['date'])
					{
					if (! $accordion)
						{
						$accordion = new \App\UI\Accordion();
						}
					$accordion->addTab($lastDate, $table);
					$table = new \PHPFUI\Table();
					$table->setHeaders($headers);
					}

				if ($lastDate != $volunteer['date'])
					{
					$lastDate = $volunteer['date'];
					}
				$volunteer['time'] = \App\Tools\TimeHelper::toSmallTime($volunteer['startTime']) . '-' . \App\Tools\TimeHelper::toSmallTime($volunteer['endTime']);
				$name = $volunteer['firstName'] . ' ' . $volunteer['lastName'];
				$volunteer['name'] = "<a href='/Membership/show/{$volunteer['memberId']}'>{$name}</a>";
				$volunteer['shiftLeader'] = $volunteer['shiftLeader'] ? new \PHPFUI\FAIcon('fas', 'star') : '';
				$volunteer['worked'] = new \PHPFUI\Input\CheckBoxBoolean('worked[]', '', (bool)$volunteer['worked']);
				$volunteer['worked']->addAttribute('onchange', $ajax->execute(['volunteerJobShiftId' => $volunteer['volunteerJobShiftId']]));
				$table->addRow($volunteer);

				if ($volunteer['notes'])
					{
					$table->addRow(['time' => '', 'title' => '', 'name' => $volunteer['notes'], 'shiftLeader' => '', 'worked' => '', ], [1, 1, 3]);
					}
				}
			$this->page->addJavaScript($ajax->getPageJS());

			if ($accordion)
				{
				$accordion->addTab($lastDate, $table);
				$output->add($accordion);
				}
			else
				{
				$output->add($table);
				}
			}
		else
			{
			$this->page->redirect('/Volunteer/events');
			}

		return $output;
		}
	}
