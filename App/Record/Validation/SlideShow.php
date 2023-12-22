<?php

namespace App\Record\Validation;

class SlideShow extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'active' => ['required', 'integer'],
		'added' => ['required', 'datetime'],
		'alignment' => ['maxlength'],
		'endDate' => ['date', 'gte_field:startDate'],
		'memberId' => ['integer', 'minvalue:0'],
		'name' => ['maxlength', 'required'],
		'startDate' => ['date', 'lte_field:endDate'],
		'updated' => ['datetime'],
		'width' => ['required', 'integer'],
	];

	public function __construct(\App\Record\SlideShow $record)
		{
		parent::__construct($record);
		}
	}
