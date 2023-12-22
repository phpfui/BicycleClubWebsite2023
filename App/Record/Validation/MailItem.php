<?php

namespace App\Record\Validation;

class MailItem extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'body' => ['maxlength'],
		'domain' => ['maxlength'],
		'fromEmail' => ['maxlength', 'email'],
		'fromName' => ['maxlength'],
		'html' => ['integer'],
		'memberId' => ['integer'],
		'paused' => ['integer'],
		'replyTo' => ['maxlength', 'email'],
		'replyToName' => ['maxlength'],
		'title' => ['maxlength'],
	];

	public function __construct(\App\Record\MailItem $record)
		{
		parent::__construct($record);
		}
	}
