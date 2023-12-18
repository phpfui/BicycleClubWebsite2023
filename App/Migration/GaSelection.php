<?php

namespace App\Migration;

// hack the record classes to skip references to future properties for this migration
class GaSelection extends \App\Record\GaSelection
	{
	public function clean() : static
		{
		return $this;
		}

	// we need to remove this for the conversion only
	public static function unsetCSVValue() : void
		{
		unset(static::$fields['csvValue']);
		}
	}
