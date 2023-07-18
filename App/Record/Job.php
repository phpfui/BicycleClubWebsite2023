<?php

namespace App\Record;

/**
 * @inheritDoc
 *
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\JobShift> $JobShiftChildren
 */
class Job extends \App\Record\Definition\Job
	{
	/** @var array<string, array<string>> */
	protected static array $virtualFields = [
		'JobShiftChildren' => [\PHPFUI\ORM\Children::class, \App\Table\JobShift::class],
	];

	public function clean() : static
		{
		$this->cleanProperName('location');
		$this->cleanProperName('title');

		return $this;
		}
	}
