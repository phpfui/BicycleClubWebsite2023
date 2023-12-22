<?php

namespace App\Record\Validation;

class ForumMessage extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'forumId' => ['integer'],
		'htmlMessage' => ['maxlength'],
		'lastEdited' => ['datetime'],
		'lastEditorId' => ['integer'],
		'memberId' => ['required', 'integer'],
		'posted' => ['required', 'datetime'],
		'textMessage' => ['maxlength'],
		'title' => ['maxlength'],
	];

	public function __construct(\App\Record\ForumMessage $record)
		{
		parent::__construct($record);
		}
	}
