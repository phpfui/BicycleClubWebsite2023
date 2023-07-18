<?php

namespace App\Table;

class AuditTrail extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\AuditTrail::class;

	/**
	 * @param array<string,string> $input
	 */
	public static function log(string $sql, array $input = []) : void
		{
		$auditTrail = new \App\Record\AuditTrail();
		$auditTrail->setFrom(['memberId' => \App\Model\Session::signedInMemberId(),
			'statement' => $sql, 'input' => \json_encode($input, JSON_THROW_ON_ERROR), 'additional' => \App\Tools\Logger::get()->formatTrace(\debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS))]);
		$auditTrail->insert();
		}

	public static function purge(int $time) : void
		{
		$sql = 'delete from auditTrail where time < ?';
		\PHPFUI\ORM::execute($sql, [\date('Y-m-d H:i:s', $time)]);
		}
	}
