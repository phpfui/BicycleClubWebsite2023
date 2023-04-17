<?php

namespace App\Model;

class CueSheetFiles extends \App\Model\File
	{
	public function __construct()
		{
		parent::__construct('../files/cuesheets');
		}
	}
