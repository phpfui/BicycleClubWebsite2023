<?php

namespace App\Record\Validation;

class MailPiece extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'email' => ['maxlength', 'email'],
		'mailItemId' => ['required', 'integer'],
		'memberId' => ['integer'],
		'name' => ['maxlength'],
	];

	public function __construct(\App\Record\MailPiece $record)
		{
		parent::__construct($record);
		}
	}
