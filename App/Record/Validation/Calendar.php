<?php

namespace App\Record\Validation;

class Calendar extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'description' => ['maxlength', 'required'],
		'distances' => ['maxlength'],
		'eventDate' => ['required', 'date'],
		'eventDays' => ['integer', 'required'],
		'eventType' => ['integer'],
		'location' => ['maxlength', 'required'],
		'pending' => ['required', 'integer'],
		'price' => ['integer'],
		'privateContact' => ['maxlength'],
		'privateEmail' => ['maxlength', 'email', 'required'],
		'publicContact' => ['maxlength'],
		'publicEmail' => ['maxlength', 'email'],
		'startTime' => ['time'],
		'state' => ['maxlength', 'required'],
		'title' => ['maxlength', 'required'],
		'webSite' => ['maxlength', 'website'],
	];

	public function __construct(\App\Record\Calendar $record)
		{
		parent::__construct($record);
		}
	}
