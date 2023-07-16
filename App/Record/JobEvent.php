<?php

namespace App\Record;

/**
 * @inheritDoc
 *
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\Job> $JobChildren
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\VolunteerPoint> $VolunteerPointChildren
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\VolunteerPoll> $VolunteerPollChildren
 */
class JobEvent extends \App\Record\Definition\JobEvent
	{
	protected static array $virtualFields = [
		'JobChildren' => [\PHPFUI\ORM\Children::class, \App\Table\Job::class],
		'VolunteerPointChildern' => [\PHPFUI\ORM\Children::class, \App\Table\VolunteerPoint::class],
		'VolunteerPollChildren' => [\PHPFUI\ORM\Children::class, \App\Table\VolunteerPoll::class],
	];

	public function clean() : static
		{
		$this->cleanEmail('email');
		$this->cleanProperName('name');

		return $this;
		}

	public function insert() : int
		{
		$this->fixDates();

		return parent::insert();
		}

	public function insertOrUpdate() : int
		{
		$this->fixDates();

		return parent::insertOrUpdate();
		}

	public function update() : bool
		{
		$this->fixDates();

		return parent::update();
		}

	private function fixDates() : void
		{
		if (empty($this->current['cutoffDate']) || $this->current['cutoffDate'] > ($this->current['date'] ?? ''))
			{
			$this->current['cutoffDate'] = $this->current['date'] ?? \App\Tools\Date::todayString();
			}
		}
	}
