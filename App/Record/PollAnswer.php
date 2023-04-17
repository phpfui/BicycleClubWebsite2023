<?php

namespace App\Record;

/**
 * @inheritDoc
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\PollResponse> $PollResponseChildren
 */
class PollAnswer extends \App\Record\Definition\PollAnswer
	{
	protected static array $virtualFields = [
		'PollResponseChildren' => [\PHPFUI\ORM\Children::class, \App\Table\PollResponse::class],
	];

	public function clean() : static
		{
		$this->cleanProperName('answer');

		return $this;
		}
	}
