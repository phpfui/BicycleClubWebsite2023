<?php

namespace App\Record\Validation;

class PointHistory extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'volunteerPoints' => ['integer'],
		'memberId' => ['required', 'integer'],
		'editorId' => ['integer'],
		'oldLeaderPoints' => ['integer'],
		'time' => ['required', 'datetime'],
	];

	public function __construct(\App\Record\PointHistory $record)
		{
		parent::__construct($record);
		}
	}
