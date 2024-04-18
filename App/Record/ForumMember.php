<?php

namespace App\Record;

/**
 * @inheritDoc
 * @property \App\Enum\Forum\SubscriptionType $emailType
 */
class ForumMember extends \App\Record\Definition\ForumMember
	{
	/** @var array<string, array<string>> */
	protected static array $virtualFields = [
		'emailType' => [\PHPFUI\ORM\Enum::class, \App\Enum\Forum\SubscriptionType::class],
	];

	}
