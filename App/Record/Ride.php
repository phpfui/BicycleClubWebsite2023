<?php

namespace App\Record;

/**
 * @inheritDoc
 *
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\SigninSheetRide> $SigninSheetRideChildren
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\RideSignup> $RideSignupChildren
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\RideIncentive> $RideIncentiveChildren
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\RideComment> $RideCommentChildren
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\AssistantLeader> $AssistantLeaderChildren
 */
class Ride extends \App\Record\Definition\Ride
	{
	protected static array $virtualFields = [
		'AssistantLeaderChildren' => [\PHPFUI\ORM\Children::class, \App\Table\AssistantLeader::class],
		'RideCommentChildren' => [\PHPFUI\ORM\Children::class, \App\Table\RideComment::class],
		'RideIncentiveChildren' => [\PHPFUI\ORM\Children::class, \App\Table\RideIncentive::class],
		'RideSignupChildren' => [\PHPFUI\ORM\Children::class, \App\Table\RideSignup::class],
		'SigninSheetRideChildren' => [\PHPFUI\ORM\Children::class, \App\Table\SigninSheetRide::class],
	];

	public function clean() : static
		{
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
