<?php

namespace App\Record;

/**
 * @inheritDoc
 *
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\PollAnswer> $PollAnswerChildren
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\PollResponse> $PollResponseChildren
 */
class Poll extends \App\Record\Definition\Poll
	{
	/** @var array<string, array<string>> */
	protected static array $virtualFields = [
		'PollAnswerChildren' => [\PHPFUI\ORM\Children::class, \App\Table\PollAnswer::class],
		'PollResponseChildren' => [\PHPFUI\ORM\Children::class, \App\Table\PollResponse::class],
	];

	public function delete() : bool
		{
		$condition = new \PHPFUI\ORM\Condition('pollId', $this->pollId);

		$pollResponseTable = new \App\Table\PollResponse();
		$pollResponseTable->setWhere($condition);
		$pollResponseTable->delete();

		return parent::delete();
		}
	}
