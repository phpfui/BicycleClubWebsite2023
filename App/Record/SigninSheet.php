<?php

namespace App\Record;

/**
 * @inheritDoc
 *
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\SigninSheetRide> $SigninSheetRideChildren
 */
class SigninSheet extends \App\Record\Definition\SigninSheet
	{
	protected static array $virtualFields = [
		'SigninSheetRideChildren' => [\PHPFUI\ORM\Children::class, \App\Table\SigninSheetRide::class],
	];
	}
