<?php

namespace App\Record\Validation;

class ForumMember extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'emailType' => ['integer'],
	];

	public function __construct(\App\Record\ForumMember $record)
		{
		parent::__construct($record);
		}
	}
