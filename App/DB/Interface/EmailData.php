<?php

namespace App\DB\Interface;

interface EmailData
	{
	/** @return array<string, string> */
	public function toArray() : array;
	}
