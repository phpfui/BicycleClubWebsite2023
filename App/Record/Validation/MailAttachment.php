<?php

namespace App\Record\Validation;

class MailAttachment extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'mailItemId' => ['required', 'integer'],
		'prettyName' => ['maxlength'],
	];

	public function __construct(\App\Record\MailAttachment $record)
		{
		parent::__construct($record);
		}
	}
