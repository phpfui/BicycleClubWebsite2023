<?php

namespace App\Record\Validation;

class ForumMember extends \PHPFUI\ORM\Validator
	{
	public function __construct(\App\Record\ForumMember $record)
		{
		parent::__construct($record);
		}
	}
