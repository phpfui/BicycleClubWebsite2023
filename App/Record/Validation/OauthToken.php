<?php

namespace App\Record\Validation;

/**
 * Autogenerated. File will not be changed by oneOffScripts\generateValidators.php.  Delete and rerun if you want.
 */
class OauthToken extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, string[]> */
	public static array $validators = [
		'client' => ['maxlength'],
		'expires' => ['required', 'maxlength', 'datetime'],
		'oauthTokenId' => ['integer'],
		'oauthUserId' => ['integer'],
		'scopes' => ['maxlength'],
		'token' => ['maxlength'],
	];
	}
