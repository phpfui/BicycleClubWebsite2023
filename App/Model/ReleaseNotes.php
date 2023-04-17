<?php

namespace App\Model;

class ReleaseNotes extends \App\Model\File
	{
	public function __construct()
		{
		parent::__construct('../files/releaseNotes');
		}
	}
