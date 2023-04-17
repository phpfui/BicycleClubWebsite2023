<?php

namespace App\View\Volunteer;

class JobSubMenu extends \PHPFUI\Menu
	{
	public function __construct(\App\Record\Job $job, private readonly string $active)
		{
		parent::__construct();
		$this->addMenuItem(new \PHPFUI\MenuItem('Job Details', "/Volunteer/jobEdit/{$job->jobId}"));
		$this->addMenuItem(new \PHPFUI\MenuItem('Shifts', "/Volunteer/editShift/{$job->jobId}"));
		$this->addMenuItem(new \PHPFUI\MenuItem('Volunteers', "/Volunteer/editVolunteers/{$job->jobId}"));
		$this->addMenuItem(new \PHPFUI\MenuItem('Email Volunteers', "/Volunteer/emailShift/{$job->jobId}"));
		}

	public function getStart() : string
		{
		$this->setActiveName($this->active);

		return parent::getStart();
		}
	}
