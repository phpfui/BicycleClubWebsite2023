<?php

namespace App\Record;

/**
 * @inheritDoc
 *
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\VolunteerPollResponse> $VolunteerPollResponseChildren
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\VolunteerPollAnswer> $VolunteerPollAnswerChildren
 */
class VolunteerPoll extends \App\Record\Definition\VolunteerPoll
	{
	/** @var array<string, array<string>> */
	protected static array $virtualFields = [
		'VolunteerPollAnswerChildren' => [\PHPFUI\ORM\Children::class, \App\Table\VolunteerPollAnswer::class, 'ordering'],
		'VolunteerPollResponseChildren' => [\PHPFUI\ORM\Children::class, \App\Table\VolunteerPollResponse::class],
	];
	}
