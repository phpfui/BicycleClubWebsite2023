<?php

namespace App\Record;

/**
 * @inheritDoc
 *
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\PollResponse> $PollResponseChildren
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\Payment> $PaymentChildren
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\Member> $MemberChildren
 */
class Membership extends \App\Record\Definition\Membership
	{
	/** @var array<string, array<string>> */
	protected static array $virtualFields = [
		'MemberChildren' => [\PHPFUI\ORM\Children::class, \App\Table\Member::class],
		'PaymentChildren' => [\PHPFUI\ORM\Children::class, \App\Table\Payment::class],
		'PollResponseChildren' => [\PHPFUI\ORM\Children::class, \App\Table\PollResponse::class],
	];

	public function clean() : static
		{
		$this->cleanProperName('address');
		$this->cleanProperName('town');
		$this->cleanUpperCase('state');
		$this->cleanPhone('zip', '\\-');

		return $this;
		}
	}
