<?php

namespace App\Record;

/**
 * @inheritDoc
 *
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\VolunteerJobShift> $VolunteerJobShiftChildren
 */
class JobShift extends \App\Record\Definition\JobShift
	{
	protected static array $virtualFields = [
		'VolunteerJobShiftChildren' => [\PHPFUI\ORM\Children::class, \App\Table\VolunteerJobShift::class],
	];
	}
