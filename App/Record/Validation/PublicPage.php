<?php

namespace App\Record\Validation;

class PublicPage extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'banner' => ['integer'],
		'blog' => ['integer'],
		'blogAfter' => ['maxlength'],
		'footerMenu' => ['required', 'integer'],
		'header' => ['integer'],
		'homePageNotification' => ['required', 'integer'],
		'method' => ['maxlength'],
		'name' => ['maxlength'],
		'publicMenu' => ['required', 'integer'],
		'sequence' => ['integer'],
		'url' => ['maxlength', 'starts_with:/', 'unique'],
	];

	public function __construct(\App\Record\PublicPage $record)
		{
		parent::__construct($record);
		}
	}
