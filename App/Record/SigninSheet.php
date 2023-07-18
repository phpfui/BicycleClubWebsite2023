<?php

namespace App\Record;

/**
 * @inheritDoc
 *
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\SigninSheetRide> $SigninSheetRideChildren
 */
class SigninSheet extends \App\Record\Definition\SigninSheet
	{
	/** @var array<string, array<string>> */
	protected static array $virtualFields = [
		'SigninSheetRideChildren' => [\PHPFUI\ORM\Children::class, \App\Table\SigninSheetRide::class],
	];
	}
