<?php

namespace App\Record;

/**
 * @inheritDoc
 * @property \App\Enum\RideSignup\Attended $attended
 * @property \App\Enum\RideSignup\Status $status
 */
class RideSignup extends \App\Record\Definition\RideSignup
	{
	/** @var array<string, array<string>> */
	protected static array $virtualFields = [
		'attended' => [\PHPFUI\ORM\Enum::class, \App\Enum\RideSignup\Attended::class],
		'status' => [\PHPFUI\ORM\Enum::class, \App\Enum\RideSignup\Status::class],
	];
	}
