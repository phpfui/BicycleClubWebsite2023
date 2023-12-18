<?php

namespace App\Migration;

// hack the record classes to skip references to future properties for this migration
class GaOption extends \App\Record\GaOption
	{
	public function clean() : static
		{
		return $this;
		}

	// we need to remove this for the conversion only
	public static function unsetCSVField() : void
		{
		unset(static::$fields['csvField']);
		}
	}
