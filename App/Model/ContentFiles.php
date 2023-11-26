<?php

namespace App\Model;

class ContentFiles extends \App\Model\TinifyImage
	{
	public function __construct()
		{
		parent::__construct('images/content');
		}
	}
