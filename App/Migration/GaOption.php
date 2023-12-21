<?php

namespace App\Migration;

// hack the record classes to skip references to future properties for this migration
class GaOption extends \App\Record\GaOption
	{
	public function __construct(int|array|null|string|\PHPFUI\ORM\DataObject $parameter = null)
		{
		parent::__construct($parameter);
		unset(static::$fields['csvField']);
		}

	public function clean() : static
		{
		return $this;
		}
	}
