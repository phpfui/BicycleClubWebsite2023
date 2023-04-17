<?php

namespace App\Table;

class RideComment extends \PHPFUI\ORM\Table
	{
	final public const DELIVERY_BOTH = 3;

	final public const DELIVERY_EMAIL = 1;

	final public const DELIVERY_TEXT = 2;

	protected static string $className = '\\' . \App\Record\RideComment::class;
	}
