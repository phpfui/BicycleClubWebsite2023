<?php

namespace App\Record;

/**
 * @inheritDoc
 *
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\GaRider> $GaRiderChildren
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\GaRide> $GaRideChildren
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\GaPriceDate> $GaPriceDateChildren
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\GaIncentive> $GaIncentiveChildren
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\GaAnswer> $GaAnswerChildren
 */
class GaEvent extends \App\Record\Definition\GaEvent
	{
	protected static array $virtualFields = [
		'GaAnswerChildren' => [\PHPFUI\ORM\Children::class, \App\Table\GaAnswer::class],
		'GaIncentiveChildren' => [\PHPFUI\ORM\Children::class, \App\Table\GaIncentive::class],
		'GaPriceDateChildren' => [\PHPFUI\ORM\Children::class, \App\Table\GaPriceDate::class],
		'GaRideChildren' => [\PHPFUI\ORM\Children::class, \App\Table\GaRide::class],
		'GaRiderChildren' => [\PHPFUI\ORM\Children::class, \App\Table\GaRider::class],
	];

	public function clean() : static
		{
		$this->description = \App\Tools\TextHelper::cleanUserHtml($this->description);
		$this->signupMessage = \App\Tools\TextHelper::cleanUserHtml($this->signupMessage);

		return $this;
		}
	}
