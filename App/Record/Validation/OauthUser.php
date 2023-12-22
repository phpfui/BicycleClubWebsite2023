<?php

namespace App\Record\Validation;

class OauthUser extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'lastLogin' => ['datetime'],
		'memberId' => ['required', 'integer'],
		'password' => ['maxlength'],
		'permissions' => ['maxlength'],
		'userName' => ['maxlength', 'required'],
	];

	public function __construct(\App\Record\OauthUser $record)
		{
		parent::__construct($record);
		}
	}
