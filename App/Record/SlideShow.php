<?php

namespace App\Record;

/**
 * @inheritDoc
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\Slide> $SlideChildren
 */
class SlideShow extends \App\Record\Definition\SlideShow
	{
	/** @var array<string, array<string>> */
	protected static array $virtualFields = [
		'SlideChildren' => [\PHPFUI\ORM\Children::class, \App\Table\Slide::class, 'sequence'],
	];

	/**
	 * @return array<string, mixed>
	 */
	public function allSettings() : array
		{
		return \json_decode($this->settings ?: '[]', true);
		}
	}
