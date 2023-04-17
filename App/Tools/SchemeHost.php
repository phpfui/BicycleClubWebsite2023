<?php

namespace App\Tools;

trait SchemeHost
	{
	/**
	 * Return just server host name with scheme
	 */
	public function getSchemeHost() : string
		{
		return $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
		}
	}
