<?php

namespace App\Record;

/**
 * @inheritDoc
 *
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\MemberCategory> $MemberCategoryChildren
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\MemberOfMonth> $MemberOfMonthChildren
 */
class Member extends \App\Record\Definition\Member
	{
	/** @var array<string, array<string>> */
	protected static array $virtualFields = [
		'MemberCategoryChildren' => [\PHPFUI\ORM\Children::class, \App\Table\MemberCategory::class],
		'MemberOfMonthChildren' => [\PHPFUI\ORM\Children::class, \App\Table\MemberOfMonth::class],
	];

	public function clean() : static
		{
		$this->cleanEmail('email');
		$this->email = \App\Model\Member::cleanEmail($this->email);
		$this->cleanProperName('lastName');
		$this->cleanProperName('firstName');
		$this->cleanProperName('emergencyContact');
		$this->cleanPhone('phone');
		$this->cleanPhone('cellPhone');
		$this->cleanPhone('emergencyPhone');

		return $this;
		}

	public function delete() : bool
		{
		$auditTrail = new \App\Record\AuditTrail();
		$auditTrail->memberId = \App\Model\Session::signedInMemberId();
		$auditTrail->additional = \print_r(\debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), true);
		$auditTrail->statement = 'Member deleted';
		$auditTrail->input = \print_r($this->toArray(), true);
		$auditTrail->insert();

		return parent::delete();
		}

	public function fullName() : string
		{
		return \App\Tools\TextHelper::unhtmlentities(($this->current['firstName'] ?? '') . ' ' . ($this->current['lastName'] ?? ''));
		}
	}
