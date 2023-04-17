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
	protected static array $virtualFields = [
		'VolunteerPollAnswerChildren' => [\PHPFUI\ORM\Children::class, \App\Table\VolunteerPollAnswer::class],
		'VolunteerPollResponseChildren' => [\PHPFUI\ORM\Children::class, \App\Table\VolunteerPollResponse::class],
	];
	}
