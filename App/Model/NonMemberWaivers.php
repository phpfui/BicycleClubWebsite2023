<?php

namespace App\Model;

class NonMemberWaivers extends \App\Model\File
	{
	public function __construct()
		{
	  parent::__construct('../files/nonMemberWaivers');
		}

	public function url(string $filename) : string
		{
		return '/Leaders/downloadWaiver/' . \substr($filename, 0, \strrpos($filename, '.'));
		}
	}
