<?php

namespace App\Record;

/**
 * @inheritDoc
 *
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\SigninSheetRide> $SigninSheetRideChildren
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\RideSignup> $rideSignups
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\RideIncentive> $RideIncentiveChildren
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\RideComment> $RideCommentChildren
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\AssistantLeader> $assistantLeaders
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\RideSignup> $confirmedRiders
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\RideSignup> $waitList
 */
class Ride extends \App\Record\Definition\Ride
	{
	/** @var array<string, array<string>> */
	protected static array $virtualFields = [
		'assistantLeaders' => [\PHPFUI\ORM\Children::class, \App\Table\AssistantLeader::class],
		'RideCommentChildren' => [\PHPFUI\ORM\Children::class, \App\Table\RideComment::class],
		'RideIncentiveChildren' => [\PHPFUI\ORM\Children::class, \App\Table\RideIncentive::class],
		'waitList' => [\App\DB\RideWaitList::class],
		'rideSignups' => [\PHPFUI\ORM\Children::class, \App\Table\RideSignup::class],
		'SigninSheetRideChildren' => [\PHPFUI\ORM\Children::class, \App\Table\SigninSheetRide::class],
		'confirmedRiders' => [\App\DB\ConfirmedRiders::class],
	];

	public function canClone() : bool
		{
		return $this->rideDate >= \App\Tools\Date::todayString() && $this->maxRiders && \count($this->waitList);
		}

	public function clean() : static
		{
		if (null === $this->title)
			{
			$this->title = '';
			}

		if ($this->averagePace)
			{
			$this->averagePace = \round($this->averagePace, 1);
			}

		if ($this->targetPace)
			{
			$this->targetPace = \round($this->targetPace, 1);
			}

		return $this;
		}
	}
