<?php

namespace App\Record;

/**
 * @inheritDoc
 *
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\JobShift> $JobShiftChildren
 */
class Job extends \App\Record\Definition\Job
	{
	protected static array $virtualFields = [
		'JobShiftChildren' => [\PHPFUI\ORM\Children::class, \App\Table\JobShift::class],
	];
	}
