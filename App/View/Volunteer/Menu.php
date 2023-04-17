<?php

namespace App\View\Volunteer;

class Menu extends \PHPFUI\Menu
	{
	public function __construct(\App\Record\JobEvent $jobEvent, private readonly string $active)
		{
		parent::__construct();
		$this->addMenuItem(new \PHPFUI\MenuItem('All Events', '/Volunteer/events'));
		$this->addMenuItem(new \PHPFUI\MenuItem('Event', "/Volunteer/edit/{$jobEvent->jobEventId}"));
		$this->addMenuItem(new \PHPFUI\MenuItem('Email', "/Volunteer/emailAll/{$jobEvent->jobEventId}"));
		$this->addMenuItem(new \PHPFUI\MenuItem('Jobs', "/Volunteer/jobs/{$jobEvent->jobEventId}"));
		$this->addMenuItem(new \PHPFUI\MenuItem('Polls', "/Volunteer/polls/{$jobEvent->jobEventId}"));
		$this->addMenuItem(new \PHPFUI\MenuItem('Reports', "/Volunteer/reports/{$jobEvent->jobEventId}"));
		$this->addMenuItem(new \PHPFUI\MenuItem('Schedule', "/Volunteer/schedule/{$jobEvent->jobEventId}"));
		}

	public function getStart() : string
		{
		$this->setActiveName($this->active);

		return parent::getStart();
		}
	}
