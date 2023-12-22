<?php

namespace App\Record\Validation;

class AssistantLeader extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
	];

	public function __construct(\App\Record\AssistantLeader $record)
		{
		parent::__construct($record);
		}
	}
