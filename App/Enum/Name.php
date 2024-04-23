<?php

namespace App\Enum;

trait Name
	{
	public function name() : string
		{
		return \ucwords(\strtolower(\str_replace('_', ' ', $this->name)));
		}
	}
