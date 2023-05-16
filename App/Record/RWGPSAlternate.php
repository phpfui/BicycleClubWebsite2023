<?php

namespace App\Record;

class RWGPSAlternate extends \App\Record\Definition\RWGPSAlternate
	{
	protected static array $virtualFields = [
		'alternateRoutes' => [\PHPFUI\ORM\RelatedRecord::class, \App\Table\RWGPS::class],
	];
	}
