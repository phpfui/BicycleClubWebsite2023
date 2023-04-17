<?php

namespace App\Record;

/**
 * @inheritDoc
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\Slide> $SlideChildren
 */
class SlideShow extends \App\Record\Definition\SlideShow
	{
	protected static array $virtualFields = [
		'SlideChildren' => [\PHPFUI\ORM\Children::class, \App\Table\Slide::class, 'sequence'],
	];

	public function allSettings() : array
		{
		return \json_decode($this->settings ?: '[]', true);
		}
	}
