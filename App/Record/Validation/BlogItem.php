<?php

namespace App\Record\Validation;

class BlogItem extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'ranking' => ['required', 'integer'],
	];

	public function __construct(\App\Record\BlogItem $record)
		{
		parent::__construct($record);
		}
	}
