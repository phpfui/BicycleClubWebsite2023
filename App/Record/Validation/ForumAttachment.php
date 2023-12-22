<?php

namespace App\Record\Validation;

class ForumAttachment extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'fileName' => ['maxlength'],
		'forumId' => ['integer'],
		'forumMessageId' => ['integer'],
	];

	public function __construct(\App\Record\ForumAttachment $record)
		{
		parent::__construct($record);
		}
	}
