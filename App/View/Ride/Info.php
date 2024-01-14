<?php

namespace App\View\Ride;

class Info
	{
	private \App\View\Leader $leader;

	private \App\Table\Pace $paceTable;

	private readonly \App\View\StartLocation $startLocationView;

	public function __construct(private readonly \PHPFUI\Page $page)
		{
		$this->paceTable = new \App\Table\Pace();
		$this->startLocationView = new \App\View\StartLocation($this->page);
		$this->leader = new \App\View\Leader($this->page);
		}

	public function getRideInfo(\App\Record\Ride $ride, string $rwgpsMenu = '') : \PHPFUI\FieldSet
		{
		$fieldSet = new \PHPFUI\FieldSet('Ride Information');

		if ($ride->dateAdded)
			{
			$fieldSet->add(new \App\UI\Display('Date / Time Added', \date('n/j/Y g:i a', \strtotime($ride->dateAdded))));
			}

		if ($ride->releasePrinted)
			{
			$fieldSet->add(new \App\UI\Display('Sign In Sheet Printed', \date('n/j/Y g:i a', \strtotime($ride->releasePrinted))));
			}

		if ($ride->pointsAwarded)
			{
			$fieldSet->add(new \App\UI\Display('Volunteer Points Credited', $ride->pointsAwarded));
			}

		if ($ride->rideDate)
			{
			$fieldSet->add(new \App\UI\Display('Date', \App\Tools\Date::formatString('l, F j, Y', $ride->rideDate)));
			}

		if ($ride->startTime)
			{
			$fieldSet->add(new \App\UI\Display('Time', \App\Tools\TimeHelper::toSmallTime($ride->startTime)));
			$model = new \App\Model\Ride();
			$seconds = $model->computeDuration($ride);
			$start = \App\Tools\TimeHelper::fromString($ride->startTime);
			$end = (int)($start + ($seconds / 60));
			$fieldSet->add(new \App\UI\Display('Estimated End Time', \App\Tools\TimeHelper::toString($end)));
			}

		if ($ride->title)
			{
			$fieldSet->add(new \App\UI\Display('Title', $ride->title));
			}
		$fieldSet->add(new \App\UI\Display('Category', $this->paceTable->getPace($ride->paceId)));

		if ($ride->targetPace)
			{
			$fieldSet->add(new \App\UI\Display('Target Pace', $ride->targetPace));
			}

		if ($ride->averagePace > 0)
			{
			$fieldSet->add(new \App\UI\Display('Average Pace', $ride->averagePace));
			}

		if ($ride->mileage)
			{
			$fieldSet->add(new \App\UI\Display('Distance', $ride->mileage));
			}

		if ($ride->regrouping)
			{
			$fieldSet->add(new \App\UI\Display('Regrouping Policy', $ride->regrouping));
			}

		if ($ride->elevation)
			{
			$fieldSet->add(new \App\UI\Display('Elevation Gain', $ride->elevation . ' feet'));

			if ($ride->mileage)
				{
				$fieldSet->add(new \App\UI\Display('Feet Per Mile', \number_format($ride->elevation / (float)$ride->mileage, 1)));
				}
			}

		if ($ride->startLocationId)
			{
			$fieldSet->add(new \App\UI\Display('Start Location', $this->startLocationView->getLocationPicker($ride->startLocation)));
			}

		if ($ride->RWGPSId)
			{
			$link = new \PHPFUI\Link("/RWGPS/detail/{$ride->RWGPSId}", \PHPFUI\TextHelper::unhtmlentities($ride->RWGPS->title) . ' - ' . $ride->RWGPSId, false);
			$link->addAttribute('target', '_blank');
			$fieldSet->add(new \App\UI\Display('RWGPS Detail', $link));
			}

		if ($ride->cueSheetId)
			{
			$cueSheetView = new \App\View\CueSheet($this->page);
			$fieldSet->add(new \App\UI\Display('Cue Sheet', $ride->cueSheet->getFullNameLink()));
			}

		if ($ride->memberId)
			{
			$member = $ride->member;
			$fieldSet->add(new \App\UI\Display('Leader', $this->leader->getStatsLink($member)));

			if (! empty($member->cellPhone))
				{
				$fieldSet->add(new \App\UI\Display('Leader Cell', \PHPFUI\Link::phone($member->cellPhone)));
				}
			}

		foreach (\App\Table\AssistantLeader::getForRide($ride) as $assistant)
			{
			$fieldSet->add(new \App\UI\Display('Assistant Leader', $this->leader->getStatsLink($assistant)));
			}

		if ($ride->description)
			{
			$row = new \PHPFUI\GridX();
			$row->add('<label><strong>Description</strong></label>');
			$fieldSet->add($row);
			$fieldSet->add($ride->description);
			}

		$fieldSet->add($rwgpsMenu);

		if ($ride->rideStatus)
			{
			$fieldSet->add(new \App\UI\Display('Ride Status', \App\Table\Ride::getStatusValues()[$ride->rideStatus]));
			}

		if ($ride->numberOfRiders)
			{
			$fieldSet->add(new \App\UI\Display('Number Of Riders', $ride->numberOfRiders));
			}

		if ($ride->accident)
			{
			$fieldSet->add(new \App\UI\Display('Reported Crash', 'Yes'));
			}

		return $fieldSet;
		}

	public function getRideInfoEmail(\App\Record\Ride $ride) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();
		$style = new \PHPFUI\HTML5Element('style');
		$style->add('table tr:nth-child(odd) {background-color: #f2f2f2;} table td,table th {padding: .5em;} table tr td:first-child {font-weight: bold;}');
		$container->add($style);
		$fieldSet = new \PHPFUI\FieldSet('Ride Information');
		$container->add($fieldSet);
		$table = new \PHPFUI\Table();

		if ($ride->dateAdded)
			{
			$table->addRow(['Date / Time Added', \date('n/j/Y g:i a', \strtotime($ride->dateAdded))]);
			}

		if ($ride->releasePrinted)
			{
			$table->addRow(['Sign In Sheet Printed', \date('n/j/Y g:i a', \strtotime($ride->releasePrinted))]);
			}

		if ($ride->pointsAwarded)
			{
			$table->addRow(['Volunteer Points Credited', $ride->pointsAwarded]);
			}

		if ($ride->rideDate)
			{
			$table->addRow(['Date', \App\Tools\Date::formatString('l, F j, Y', $ride->rideDate)]);
			}

		if ($ride->startTime)
			{
			$table->addRow(['Time', \App\Tools\TimeHelper::toSmallTime($ride->startTime)]);
			$model = new \App\Model\Ride();
			$seconds = $model->computeDuration($ride);
			$start = \App\Tools\TimeHelper::fromString($ride->startTime);
			$end = (int)($start + ($seconds / 60));
			$table->addRow(['Estimated End Time', \App\Tools\TimeHelper::toString($end)]);
			}

		if ($ride->title)
			{
			$table->addRow(['Title', $ride->title]);
			}
		$table->addRow(['Category', $this->paceTable->getPace($ride->paceId)]);

		if ($ride->targetPace)
			{
			$table->addRow(['Target Pace', $ride->targetPace]);
			}

		if ($ride->averagePace > 0)
			{
			$table->addRow(['Average Pace', $ride->averagePace]);
			}

		if ($ride->mileage)
			{
			$table->addRow(['Distance', $ride->mileage]);
			}

		if ($ride->regrouping)
			{
			$table->addRow(['Regrouping Policy', $ride->regrouping]);
			}

		if ($ride->elevation)
			{
			$table->addRow(['Elevation Gain', $ride->elevation . ' feet']);

			if ($ride->mileage)
				{
				$table->addRow(['Feet Per Mile', \number_format($ride->elevation / (float)$ride->mileage, 1)]);
				}
			}

		if ($ride->startLocationId)
			{
			$table->addRow(['Start Location', $ride->startLocation->name]);
			}

		if ($ride->cueSheetId)
			{
			$table->addRow(['Cue Sheet', $ride->cueSheet->getFullNameLink()]);
			}

		if ($ride->memberId)
			{
			$member = $ride->member;
			$table->addRow(['Leader', $member->fullName()]);

			if (! empty($member->cellPhone))
				{
				$table->addRow(['Leader Cell', \PHPFUI\Link::phone($member->cellPhone)]);
				}
			}

		foreach (\App\Table\AssistantLeader::getForRide($ride) as $assistant)
			{
			$table->addRow(['Assistant Leader', $assistant->member->fullName()]);
			}

		if ($ride->description)
			{
			$table->addRow(['Description', $ride->description]);
			}

		$table->addRow(['RWGPS', $ride->RWGPS->routeLink()]);

		if ($ride->rideStatus)
			{
			$table->addRow(['Ride Status', \App\Table\Ride::getStatusValues()[$ride->rideStatus]]);
			}

		$fieldSet->add($table);

		return $container;
		}
	}
