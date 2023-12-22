<?php

namespace App\Record\Validation;

class JournalItem extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'body' => ['maxlength'],
		'memberId' => ['integer'],
		'timeSent' => ['required', 'datetime'],
		'title' => ['maxlength'],
	];

	public function __construct(\App\Record\JournalItem $record)
		{
		parent::__construct($record);
		}
	}
