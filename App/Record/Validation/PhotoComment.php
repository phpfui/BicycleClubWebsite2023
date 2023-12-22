<?php

namespace App\Record\Validation;

class PhotoComment extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'memberId' => ['required', 'integer'],
		'photoComment' => ['required', 'maxlength'],
		'photoId' => ['required', 'integer'],
		'timestamp' => ['required', 'datetime'],
	];

	public function __construct(\App\Record\PhotoComment $record)
		{
		parent::__construct($record);
		}
	}
